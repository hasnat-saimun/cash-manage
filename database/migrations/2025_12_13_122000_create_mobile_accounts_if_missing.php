<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('mobile_accounts')) {
            Schema::create('mobile_accounts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('business_id');
                $table->string('number', 30);
                $table->string('provider', 50)->nullable();
                $table->timestamps();
                $table->index(['business_id']);
            });
        }
    }

    public function down(): void
    {
        // Do not drop if table exists elsewhere; keep safe
        // Uncomment to drop on rollback (optional):
        // Schema::dropIfExists('mobile_accounts');
    }
};
