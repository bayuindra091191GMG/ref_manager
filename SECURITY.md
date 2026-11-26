# Security

## Report a vulnerability privately

Use **Report a vulnerability** under the repository's GitHub **Security** tab if private reporting is enabled. Otherwise, contact a maintainer through an established private channel. Do not open a public issue or pull request with vulnerability details.

Include the affected commit or version, the affected workflow and account role, the observed impact, and the minimum evidence needed to investigate. Use synthetic data and remove credentials, tokens, customer information, and payment records from logs or screenshots.

## Access boundaries

- Staff use the `admin` session guard and the `admin_users` provider. Protected mobile API routes use the Passport `user-api` guard. See [`config/auth.php`](config/auth.php) and the route registration in [`bootstrap/app.php`](bootstrap/app.php).
- Spatie permission checks apply where route groups declare them. Do not assume that a route is protected because its name starts with `admin.`, because the UI hides a menu item, or because another route in the same file checks a permission. Review middleware and branch or record ownership at the endpoint before exposing it. Keep maintenance and data-repair functionality off public ingress.
- Preserve request-forgery protection for browser writes. Keep payment callback verification separate from session authentication and check that a callback can only update its intended payment. Review duplicate delivery behavior before changing callbacks or retries.

## Secrets and sensitive data

- Never commit `.env` files, local setup notes, database dumps with real records, provider credentials, Passport private keys, or raw production logs. Use different keys and accounts for each environment. If a secret is exposed, revoke or rotate it; deleting a file in a later commit does not undo exposure.
- [`ApiLogger`](app/Http/Middleware/ApiLogger.php) records request and response payloads on routes that use it. Treat those logs as sensitive, restrict access and retention, and redact payloads before sharing them. New logging should not record passwords, bearer tokens, payment proofs, or personal data.
- The filesystem configuration marks some proof and receipt storage as public. Verify what the deployed web server exposes. Do not assume a file inherits the authorization of the page that linked to it; make an explicit access-control decision before serving sensitive uploads.
- Use HTTPS, disable production debug output, and limit access to the database, queue and cache services. Run `composer audit` and `npm audit` as part of dependency review. These checks do not cover JavaScript copied into `public/`; review vendored assets separately.

See [Operations](OPERATIONS.md) for release and recovery procedures.
