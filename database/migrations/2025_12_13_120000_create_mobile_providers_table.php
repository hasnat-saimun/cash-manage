<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mobile_providers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('name', 50);
            $table->timestamps();
            $table->index(['business_id','name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_providers');
    }
};
