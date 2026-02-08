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
        Schema::create('fuel_credit_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fuel_sale_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('product_id')->constrained();
            $table->decimal('quantity', 10, 3);
            $table->decimal('rate', 10, 3);
            $table->decimal('total', 10, 2);
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'partially_paid', 'paid', 'overdue', 'cancelled'])->default('pending');
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('balance_due', 10, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->datetime('transaction_date');
            $table->string('invoice_number')->unique();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->softDeletes();
            $table->timestamps();
            
            $table->index('status');
            $table->index('invoice_number');
            $table->index('transaction_date');
            $table->index(['due_date', 'status']);
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
