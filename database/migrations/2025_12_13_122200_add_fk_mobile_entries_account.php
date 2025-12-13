<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('mobile_entries') && Schema::hasTable('mobile_accounts')) {
            Schema::table('mobile_entries', function (Blueprint $table) {
                // add index to FK if not present
                if (!Schema::hasColumn('mobile_entries', 'mobile_account_id')) {
                    $table->unsignedBigInteger('mobile_account_id')->index()->after('id');
                }
                // create foreign key with cascade delete (guard re-creation)
                try {
                    $table->foreign('mobile_account_id')
                          ->references('id')->on('mobile_accounts')
                          ->onDelete('cascade');
                } catch (\Throwable $e) { /* ignore if FK exists */ }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('mobile_entries')) {
            try {
                Schema::table('mobile_entries', function (Blueprint $table) {
                    $table->dropForeign(['mobile_account_id']);
                });
            } catch (\Throwable $e) { /* ignore */ }
        }
    }
};
