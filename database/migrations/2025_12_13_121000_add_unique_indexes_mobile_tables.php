<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('mobile_providers')) {
            Schema::table('mobile_providers', function (Blueprint $table) {
                if (!Schema::hasColumn('mobile_providers', 'business_id')) {
                    $table->unsignedBigInteger('business_id')->index()->after('id');
                }
                if (!Schema::hasColumn('mobile_providers', 'name')) {
                    $table->string('name', 50)->after('business_id');
                }
            });
            // Add unique only if not already exists
            try {
                Schema::table('mobile_providers', function (Blueprint $table) {
                    $table->unique(['business_id','name'], 'mobile_providers_business_name_unique');
                });
            } catch (\Throwable $e) { /* ignore if exists */ }
        }

        if (Schema::hasTable('mobile_accounts')) {
            try {
                Schema::table('mobile_accounts', function (Blueprint $table) {
                    // allow same number if provider differs: use triple unique
                    $table->unique(['business_id','number','provider'], 'mobile_accounts_business_number_provider_unique');
                });
            } catch (\Throwable $e) { /* ignore if exists */ }
            // drop older unique if present
            try {
                Schema::table('mobile_accounts', function (Blueprint $table) {
                    $table->dropUnique('mobile_accounts_business_number_unique');
                });
            } catch (\Throwable $e) { /* ignore */ }
        }

        if (Schema::hasTable('mobile_entries')) {
            try {
                Schema::table('mobile_entries', function (Blueprint $table) {
                    $table->unique(['mobile_account_id','date'], 'mobile_entries_account_date_unique');
                });
            } catch (\Throwable $e) { /* ignore if exists */ }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('mobile_providers')) {
            try {
                Schema::table('mobile_providers', function (Blueprint $table) {
                    $table->dropUnique('mobile_providers_business_name_unique');
                });
            } catch (\Throwable $e) { /* ignore */ }
        }
        if (Schema::hasTable('mobile_accounts')) {
            try {
                Schema::table('mobile_accounts', function (Blueprint $table) {
                    $table->dropUnique('mobile_accounts_business_number_provider_unique');
                });
            } catch (\Throwable $e) { /* ignore */ }
        }
        if (Schema::hasTable('mobile_entries')) {
            try {
                Schema::table('mobile_entries', function (Blueprint $table) {
                    $table->dropUnique('mobile_entries_account_date_unique');
                });
            } catch (\Throwable $e) { /* ignore */ }
        }
    }
};
