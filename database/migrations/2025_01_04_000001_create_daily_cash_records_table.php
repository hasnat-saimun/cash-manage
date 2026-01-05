<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('daily_cash_records', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('business_id')->unsigned();
            $table->date('date');
            $table->enum('type', ['debit', 'credit']); // debit = money out, credit = money in
            $table->decimal('amount', 15, 2);
            $table->string('description')->nullable();
            $table->string('reference_no')->nullable(); // for tracking purpose (cheque no, ref id, etc)
            $table->timestamps();
            
            // Indexes
            $table->index(['business_id', 'date']);
            $table->index('date');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_cash_records');
    }
};
