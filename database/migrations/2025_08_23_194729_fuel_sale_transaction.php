<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('fuel_sale_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fuel_sale_id')->constrained()->onDelete('cascade');
            $table->string('dphase');
            $table->foreignId('product_id')->constrained();
            $table->decimal('previous_reading', 10, 3);
            $table->decimal('current_reading', 10, 3);
            $table->decimal('liters', 10, 3);
            $table->decimal('rate', 10, 3);
            $table->decimal('total', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
