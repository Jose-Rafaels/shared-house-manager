<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop old tables (order matters for foreign keys)
        Schema::dropIfExists('bill_payments');
        Schema::dropIfExists('bill_participants');
        Schema::dropIfExists('bills');
        Schema::dropIfExists('debt_settlements');
        Schema::dropIfExists('shared_expense_participants');
        Schema::dropIfExists('shared_expenses');

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payer_id')->constrained('members')->restrictOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->restrictOnDelete();
            $table->unsignedBigInteger('amount');
            $table->text('description')->nullable();
            $table->date('expense_date');
            $table->timestamps();
        });

        Schema::create('expense_splits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_id')->constrained('expenses')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('members')->restrictOnDelete();
            $table->unsignedBigInteger('amount_owed');
            $table->timestamps();

            $table->unique(['expense_id', 'member_id']);
        });

        Schema::create('settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_member_id')->constrained('members')->restrictOnDelete();
            $table->foreignId('to_member_id')->constrained('members')->restrictOnDelete();
            $table->unsignedBigInteger('amount');
            $table->date('settlement_date');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settlements');
        Schema::dropIfExists('expense_splits');
        Schema::dropIfExists('expenses');
    }
};
