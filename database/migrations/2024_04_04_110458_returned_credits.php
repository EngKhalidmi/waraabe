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
        //
        Schema::create('returned_credits', function (Blueprint $table) {
            $table->id();
            $table->text('customer_name');
            $table->text('phone');
            $table->text('amount');
            $table->text('paid_amount');
            $table->text('balance');
            $table->text('date');
            $table->text('transaction_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('returned_credits');
    }
};
