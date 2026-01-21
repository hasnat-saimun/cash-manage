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
        Schema::create('transaction_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('business_id')->unsigned();
            $table->string('name')->unique(); // e.g., "Cash Deposit", "Cheque Payment"
            $table->string('type')->nullable(); // optional: 'debit', 'credit', or null for both
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes
            $table->index('business_id');
            $table->index('type');
            $table->index('is_active');
        });

        // Add foreign key to daily_cash_records
        Schema::table('daily_cash_records', function (Blueprint $table) {
            $table->bigInteger('transaction_detail_id')->unsigned()->nullable()->after('description');
            $table->foreign('transaction_detail_id')->references('id')->on('transaction_details')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_cash_records', function (Blueprint $table) {
            $table->dropForeign(['transaction_detail_id']);
            $table->dropColumn('transaction_detail_id');
        });
        
        Schema::dropIfExists('transaction_details');
    }
};
