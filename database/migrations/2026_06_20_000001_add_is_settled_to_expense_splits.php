<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expense_splits', function (Blueprint $table) {
            $table->boolean('is_settled')->default(false)->after('amount_owed');
            $table->index('is_settled');
        });
    }

    public function down(): void
    {
        Schema::table('expense_splits', function (Blueprint $table) {
            $table->dropIndex(['is_settled']);
            $table->dropColumn('is_settled');
        });
    }
};
