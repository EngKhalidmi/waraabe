<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFuelCreditPaymentsTable extends Migration

{
    public function up()
    {
        Schema::create('credit_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fuel_credit_sale_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['Zaad', 'Edahab', 'Cash On Hand', 'Bank Account'])->default('Zaad');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->datetime('payment_date');
            $table->foreignId('received_by')->constrained('users');
            $table->softDeletes();
            $table->timestamps();
            
            $table->index('payment_date');
            $table->index('payment_method');
        });
    }

    public function down()
    {
        
    }
}