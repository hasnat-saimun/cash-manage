<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                if (! Schema::hasColumn('transactions', 'txnBalance')) {
                    $table->decimal('txnBalance', 15, 2)->nullable()->after('amount');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                if (Schema::hasColumn('transactions', 'txnBalance')) {
                    $table->dropColumn('txnBalance');
                }
            });
        }
    }
};
