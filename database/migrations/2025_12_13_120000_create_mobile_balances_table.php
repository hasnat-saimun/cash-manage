<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_balances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id')->index();
            $table->date('date');
            $table->decimal('balance', 15, 2)->default(0);
            $table->decimal('rate_per_thousand', 8, 4)->default(0); // profit per 1000 units
            $table->decimal('profit', 15, 2)->default(0); // computed daily profit
            $table->timestamps();
            $table->unique(['business_id','date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_balances');
    }
};
