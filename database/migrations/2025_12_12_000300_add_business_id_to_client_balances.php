<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('client_balances')) {
            Schema::table('client_balances', function (Blueprint $table) {
                if (!Schema::hasColumn('client_balances', 'business_id')) {
                    $table->foreignId('business_id')->nullable()->constrained('businesses')->cascadeOnDelete();
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('client_balances')) {
            Schema::table('client_balances', function (Blueprint $table) {
                if (Schema::hasColumn('client_balances', 'business_id')) {
                    $table->dropConstrainedForeignId('business_id');
                }
            });
        }
    }
};
