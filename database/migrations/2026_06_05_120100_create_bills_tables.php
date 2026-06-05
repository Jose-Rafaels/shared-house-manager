<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type');
            $table->unsignedBigInteger('amount');
            $table->date('due_date')->nullable();
            $table->date('billing_month');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('bill_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained()->cascadeOnDelete();
            $table->foreignId('housemate_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('share_amount');
            $table->timestamps();
            $table->unique(['bill_id', 'housemate_id']);
        });

        Schema::create('bill_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained()->cascadeOnDelete();
            $table->foreignId('housemate_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('amount');
            $table->date('payment_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_payments');
        Schema::dropIfExists('bill_participants');
        Schema::dropIfExists('bills');
    }
};
