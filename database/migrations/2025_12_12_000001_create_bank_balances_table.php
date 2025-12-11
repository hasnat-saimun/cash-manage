<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('bank_balances')) {
            Schema::create('bank_balances', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('bank_account_id');
                $table->decimal('balance', 15, 2)->default(0);
                $table->timestamps();
            });
        }
        // Remove amount column from bank_accounts if exists
        if (Schema::hasTable('bank_accounts') && Schema::hasColumn('bank_accounts', 'amount')) {
            Schema::table('bank_accounts', function (Blueprint $table) {
                $table->dropColumn('amount');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('bank_balances')) {
            Schema::dropIfExists('bank_balances');
        }
        // Optionally restore amount column on rollback
        if (Schema::hasTable('bank_accounts') && !Schema::hasColumn('bank_accounts', 'amount')) {
            Schema::table('bank_accounts', function (Blueprint $table) {
                $table->decimal('amount', 15, 2)->default(0)->after('id');
            });
        }
    }
};
