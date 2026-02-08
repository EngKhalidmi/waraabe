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
    Schema::create('fuel_sales', function (Blueprint $table) {
        $table->id();
        $table->date('date');
        $table->enum('shift', ['morning', 'evening']);
        $table->foreignId('salesman_id')->constrained('users'); // Reference users table
        $table->decimal('discount', 10, 2)->default(0);
        $table->decimal('net_total', 10, 2)->default(0);
        $table->decimal('cash_on_hand', 10, 2)->default(0);
        $table->decimal('balance', 10, 2)->default(0);
        $table->foreignId('created_by')->constrained('users');
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
