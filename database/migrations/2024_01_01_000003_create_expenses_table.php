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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->decimal('amount', 10, 2);
            $table->string('category');
            $table->timestamp('date')->useCurrent();
            $table->string('paidBy');
            $table->enum('transactionType', ['expense', 'earning'])->default('expense');
            $table->timestamps();
            
            $table->index('date');
            $table->index('category');
            $table->index('paidBy');
            $table->index('transactionType');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};

