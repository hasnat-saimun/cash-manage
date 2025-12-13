<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id')->index();
            $table->string('provider')->nullable(); // e.g., bKash, Nagad
            $table->string('number');
            $table->timestamps();
            $table->unique(['business_id','number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_accounts');
    }
};
