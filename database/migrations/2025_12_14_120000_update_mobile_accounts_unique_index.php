<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('mobile_accounts')) {
            // Safely drop existing unique indexes if present
            try {
                $indexes = collect(DB::select("SHOW INDEX FROM `mobile_accounts`"));
                $hasBizNumberUnique = $indexes->first(function($ix){
                    return ($ix->Key_name ?? '') === 'mobile_accounts_business_id_number_unique';
                });
                if ($hasBizNumberUnique) {
                    DB::statement('ALTER TABLE `mobile_accounts` DROP INDEX `mobile_accounts_business_id_number_unique`');
                }
                // Fallback: drop any unique on (business_id, number)
                $hasComposite = $indexes->first(function($ix){
                    return ($ix->Column_name ?? '') === 'business_id' && ($ix->Non_unique ?? 1) == 0;
                });
                // No reliable way to match pair via SHOW INDEX rows without more logic; skip if named not found
            } catch (\Throwable $e) {}

            Schema::table('mobile_accounts', function (Blueprint $table) {
                // Ensure provider column exists; if not, add it nullable
                if (! Schema::hasColumn('mobile_accounts', 'provider')) {
                    $table->string('provider')->nullable()->after('number');
                }
            });

            // Add composite unique index including provider (outside closure to avoid doctrine limitations when dropping)
            try {
                DB::statement('ALTER TABLE `mobile_accounts` ADD UNIQUE `mobile_accounts_biz_number_provider_unique` (`business_id`, `number`, `provider`)');
            } catch (\Throwable $e) {
                // If it already exists, ignore
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('mobile_accounts')) {
            try {
                DB::statement('ALTER TABLE `mobile_accounts` DROP INDEX `mobile_accounts_biz_number_provider_unique`');
            } catch (\Throwable $e) {}
            try {
                DB::statement('ALTER TABLE `mobile_accounts` ADD UNIQUE `mobile_accounts_business_id_number_unique` (`business_id`, `number`)');
            } catch (\Throwable $e) {}
        }
    }
};
