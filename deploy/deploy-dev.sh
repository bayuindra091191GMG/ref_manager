#!/usr/bin/env bash
set -euo pipefail
umask 022

fail() {
    printf 'Deployment failed: %s\n' "$*" >&2
    exit 1
}

if (( $# != 7 )); then
    fail 'Usage: deploy-dev.sh <app-root> <site-url> <php-version> <database> <commit-sha> <archive-name> <archive-sha256>'
fi

app_root=$1
site_url=$2
php_version=$3
expected_database=$4
revision=$5
archive_name=$6
checksum=$7

[[ "$app_root" == /* && -d "$app_root" && ! -L "$app_root" ]] || fail 'Application root must be an existing absolute directory.'
(( EUID != 0 )) || fail 'Run as the site account, not root.'
[[ "$revision" =~ ^[0-9a-f]{40}$ && "$checksum" =~ ^[0-9a-f]{64}$ ]] || fail 'Invalid commit or archive checksum.'
[[ "$archive_name" =~ ^release-$revision-[0-9]+-[0-9]+\.tar\.gz$ ]] || fail 'Invalid incoming archive name.'

cd -- "$app_root"
[[ -s .env ]] || fail 'Provision the server .env before deploying.'
archive=".deploy/incoming/$archive_name"
[[ -f "$archive" && ! -L "$archive" ]] || fail 'Incoming archive missing or not a regular file.'
exec 9>.deploy/deploy.lock
flock -n 9 || fail 'Another deployment is running.'

[[ "$php_version" =~ ^8\.[3-9]$ ]] || fail 'Invalid PHP minor version.'
php_binary="/usr/bin/php$php_version"
[[ -x "$php_binary" ]] || fail "PHP binary is missing or not executable: $php_binary."
running_php=$("$php_binary" -r 'echo PHP_MAJOR_VERSION, ".", PHP_MINOR_VERSION;')
[[ "$running_php" == "$php_version" ]] || fail "Expected PHP $php_version at $php_binary; found $running_php."

actual_checksum=$(sha256sum "$archive")
[[ "${actual_checksum%% *}" == "$checksum" ]] || fail 'Archive checksum mismatch.'
LC_ALL=C tar -tvzf "$archive" | awk '
    substr($0, 1, 1) !~ /^[-d]$/ { invalid = 1 }
    END { exit invalid }
' || fail 'Archive must contain only regular files and directories.'

stage=$(mktemp -d .deploy/release.XXXXXXXX)
reopened=false
cleanup() {
    status=$?
    if (( status != 0 )); then
        if [[ "$reopened" == true ]]; then
            "$php_binary" artisan down --no-interaction || printf 'Could not restore maintenance mode. Check the site now.\n' >&2
        fi
        printf 'Deployment stopped (exit %s). Archive retained: %s\n' "$status" "$archive" >&2
    fi
    rm -rf -- "$stage"
}
trap cleanup EXIT
trap 'exit 129' HUP
trap 'exit 130' INT
trap 'exit 143' TERM

tar -xzf "$archive" -C "$stage" --no-same-owner --no-same-permissions
for path in artisan vendor/autoload.php public/index.php; do
    [[ -f "$stage/$path" ]] || fail "Release is missing $path."
done

mkdir -p storage/app/public storage/framework/{cache/data,sessions,views} storage/logs
if [[ -f artisan ]]; then
    "$php_binary" artisan down --no-interaction
fi

# Banners, coupons, and employee photos are uploaded into git-tracked directories
# under public/images. Protecting those paths keeps runtime uploads alive while
# still delivering files that exist in the release.
rsync -rt --checksum --delete --omit-dir-times \
    --exclude=/.env --exclude='/.env.*' --exclude=/.deploy --exclude=/.deploy-revision \
    --exclude=/.well-known --exclude=/public/.well-known --exclude=/storage --exclude=/public/storage \
    --filter='P /public/images/banners/***' \
    --filter='P /public/images/coupons/***' \
    --filter='P /public/images/employee_photos/***' \
    "$stage/" ./
mkdir -p bootstrap/cache
if [[ ! -e public/storage && ! -L public/storage ]]; then
    ln -s ../storage/app/public public/storage
fi
"$php_binary" artisan down --no-interaction

"$php_binary" -d display_errors=0 -d log_errors=0 -f /dev/stdin -- "$expected_database" <<'PHP'
<?php
try {
    require 'vendor/autoload.php';
    $app = require 'bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    $connection = $app->make('db')->connection();
    if ($connection->getDriverName() === 'mysql'
        && $connection->getDatabaseName() === $argv[1]
        && $connection->getPdo()->query('SELECT DATABASE()')->fetchColumn() === $argv[1]) {
        exit(0);
    }
} catch (Throwable) {
}
fwrite(STDERR, "Development database verification failed; no migrations were run.\n");
exit(1);
PHP

"$php_binary" artisan migrate --force --no-interaction
"$php_binary" artisan optimize --no-interaction

if [[ ! -f .deploy/ready ]]; then
    rm -f -- "$archive"
    printf 'Release staged in maintenance mode. Complete the runbook first-deployment steps before activation.\n'
    exit 0
fi

reopened=true
"$php_binary" artisan up --no-interaction
# This application has no /login route; the root path is the login page.
for path in /up /; do
    http_code=$(curl -fsS --max-time 20 --output /dev/null --write-out '%{http_code}' "$site_url$path")
    [[ "$http_code" == 200 ]] || fail "Health check $path returned HTTP $http_code; expected 200."
done

printf '%s\n' "$revision" > "$stage/.deploy-revision"
mv -f -- "$stage/.deploy-revision" .deploy-revision
reopened=false
rm -f -- "$archive"
printf 'Deployed %s\n' "$revision"
