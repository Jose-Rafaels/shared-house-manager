<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shared_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('payer_housemate_id')->constrained('housemates')->restrictOnDelete();
            $table->unsignedBigInteger('amount');
            $table->date('expense_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('shared_expense_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shared_expense_id')->constrained()->cascadeOnDelete();
            $table->foreignId('housemate_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('share_amount');
            $table->timestamps();
            $table->unique(['shared_expense_id', 'housemate_id'], 'sep_shared_expense_housemate_unique');
        });

        Schema::create('debt_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shared_expense_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('debtor_housemate_id')->constrained('housemates')->restrictOnDelete();
            $table->foreignId('creditor_housemate_id')->constrained('housemates')->restrictOnDelete();
            $table->unsignedBigInteger('amount');
            $table->date('settled_on');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debt_settlements');
        Schema::dropIfExists('shared_expense_participants');
        Schema::dropIfExists('shared_expenses');
    }
};
