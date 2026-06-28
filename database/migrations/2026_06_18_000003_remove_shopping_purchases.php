<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('shopping_purchases');

        Schema::table('shopping_items', function (Blueprint $table) {
            $table->dropColumn('purchased_at');
            $table->boolean('is_purchased')->default(false)->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('shopping_items', function (Blueprint $table) {
            $table->dropColumn('is_purchased');
            $table->timestamp('purchased_at')->nullable()->after('added_by_member_id');
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
};
