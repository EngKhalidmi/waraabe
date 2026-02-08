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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->text('item');
            $table->text('quantity');
            $table->text('unit_cost');
            $table->text('total_cost');
            $table->text('supplier');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('purchases');
    }
};
