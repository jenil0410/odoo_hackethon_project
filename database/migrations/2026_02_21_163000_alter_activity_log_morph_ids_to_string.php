<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = config('activitylog.table_name', 'activity_log');
        $connection = config('activitylog.database_connection');
        $driver = Schema::connection($connection)->getConnection()->getDriverName();

        if (! Schema::connection($connection)->hasTable($table)) {
            return;
        }

        if ($driver === 'mysql') {
            DB::connection($connection)->statement("ALTER TABLE `{$table}` MODIFY `causer_id` VARCHAR(36) NULL");
            DB::connection($connection)->statement("ALTER TABLE `{$table}` MODIFY `subject_id` VARCHAR(36) NULL");
        }
    }

    public function down(): void
    {
        // no-op; converting back to integer is unsafe for existing UUID data
    }
};
