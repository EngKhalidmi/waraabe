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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::create('cash_account', function (Blueprint $table) {
            $table->id();
            $table->text('date');
            $table->text('account');
            $table->text('debit');
            $table->text('credit');
            $table->timestamps();
        });
    }
};
