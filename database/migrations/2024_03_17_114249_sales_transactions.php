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
        Schema::create('sales_transactions', function (Blueprint $table) {
            $table->id();
            $table->text('customer_name');
            $table->text('phone');
            $table->float('sub_total');
            $table->float('discount');
            $table->float('net_price');
            $table->float('paid_amount');
            $table->float('discount');
            $table->float('balance');
            $table->float('paid_date');
            $table->float('payment_method');
            $table->timestamps();
      
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('sales_transactions');
    }
};
