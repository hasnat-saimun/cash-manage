<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $tables = [
            'sources',
            'transactions',
            'bank_manages',
            'bank_accounts',
            'bank_transactions',
            'client_creations',
            'configs',
        ];
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->foreignId('business_id')->nullable()->constrained('businesses')->cascadeOnDelete();
                });
            }
        }
    }

    public function down()
    {
        $tables = [
            'sources',
            'transactions',
            'bank_manages',
            'bank_accounts',
            'bank_transactions',
            'client_creations',
            'configs',
        ];
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropConstrainedForeignId('business_id');
                });
            }
        }
    }
};
