<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('mobile_entries')) {
            Schema::create('mobile_entries', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('mobile_account_id');
                $table->date('date');
                $table->decimal('balance', 15, 2)->default(0);
                $table->decimal('rate_per_thousand', 15, 4)->default(0);
                $table->decimal('profit', 15, 2)->default(0);
                $table->timestamps();
                $table->index(['mobile_account_id','date']);
            });
        }
    }

    public function down(): void
    {
        // Safe rollback toggle
        // Schema::dropIfExists('mobile_entries');
    }
};
