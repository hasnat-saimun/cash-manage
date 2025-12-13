<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('mobile_providers', function (Blueprint $table) {
            $table->unique(['business_id','name'], 'mobile_providers_business_name_unique');
        });
        Schema::table('mobile_accounts', function (Blueprint $table) {
            $table->unique(['business_id','number'], 'mobile_accounts_business_number_unique');
        });
        Schema::table('mobile_entries', function (Blueprint $table) {
            $table->unique(['mobile_account_id','date'], 'mobile_entries_account_date_unique');
        });
    }

    public function down(): void
    {
        Schema::table('mobile_providers', function (Blueprint $table) {
            $table->dropUnique('mobile_providers_business_name_unique');
        });
        Schema::table('mobile_accounts', function (Blueprint $table) {
            $table->dropUnique('mobile_accounts_business_number_unique');
        });
        Schema::table('mobile_entries', function (Blueprint $table) {
            $table->dropUnique('mobile_entries_account_date_unique');
        });
    }
};
