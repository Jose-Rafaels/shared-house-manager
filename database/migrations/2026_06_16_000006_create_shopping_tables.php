<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shopping_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('priority');
            $table->text('notes')->nullable();
            $table->foreignId('added_by_member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->timestamp('purchased_at')->nullable();
            $table->timestamps();
        });

        Schema::create('shopping_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shopping_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchased_by_member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->unsignedBigInteger('amount')->nullable();
            $table->date('purchased_on');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shopping_purchases');
        Schema::dropIfExists('shopping_items');
    }
};
