<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bank_accounts') && Schema::hasColumn('bank_accounts', 'opening_balance')) {
            Schema::table('bank_accounts', function (Blueprint $table) {
                $table->dropColumn('opning_balance');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('bank_accounts') && !Schema::hasColumn('bank_accounts', 'opening_balance')) {
            Schema::table('bank_accounts', function (Blueprint $table) {
                $table->decimal('opning_balance', 15, 2)->default(0)->after('id');
            });
        }
    }
};
