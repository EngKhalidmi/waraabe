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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->text('product_name');
            $table->text('quantity');
            $table->float('price');
            $table->float('total_price');
            $table->float('sales_transaction_id');
           
      
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('sales');
        
    }
};
