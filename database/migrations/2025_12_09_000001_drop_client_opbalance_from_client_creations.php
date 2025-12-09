<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('client_creations')) {
            Schema::table('client_creations', function (Blueprint $table) {
                if (Schema::hasColumn('client_creations', 'client_opBalance')) {
                    // Note: dropping columns may require doctrine/dbal
                    $table->dropColumn('client_opBalance');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('client_creations')) {
            Schema::table('client_creations', function (Blueprint $table) {
                if (! Schema::hasColumn('client_creations', 'client_opBalance')) {
                    // restore as decimal (adjust precision if your original type differs)
                    $table->decimal('client_opBalance', 15, 2)->default(0)->after('client_phone');
                }
            });
        }
    }
};
