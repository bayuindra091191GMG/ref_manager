<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('admin_users')) {
            return;
        }
        if (DB::getDriverName() !== 'mysql') {
            throw new RuntimeException('The Ref Manager baseline schema requires the MySQL/MariaDB driver.');
        }

        $schemaPath = database_path('schema/mysql-schema.sql');

        if (!is_file($schemaPath)) {
            throw new RuntimeException("Schema dump not found at {$schemaPath}.");
        }

        DB::unprepared((string) file_get_contents($schemaPath));
    }

    public function down(): void
    {
        $connection = DB::connection();
        $schema = $connection->getDatabaseName();
        $tables = $connection->select(
            "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = 'BASE TABLE'",
            [$schema]
        );

        $connection->statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            foreach ($tables as $table) {
                if ($table->TABLE_NAME === 'migrations') {
                    continue;
                }

                $name = str_replace('`', '``', $table->TABLE_NAME);
                $connection->statement("DROP TABLE IF EXISTS `{$name}`");
            }
        } finally {
            $connection->statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }
};
