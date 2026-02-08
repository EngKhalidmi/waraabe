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
        Schema::create('package_list', function (Blueprint $table) {
            $table->id();
            $table->text('cartoon_no');
            $table->text('customer_name');
            $table->text('phone');
            $table->text('address');
            $table->text('token_no');
            $table->text('packager');
            $table->timestamps();
      
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('package_list');
    }
};
