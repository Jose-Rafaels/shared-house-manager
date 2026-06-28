<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chores', function (Blueprint $table) {
            $table->foreignId('assigned_to_member_id')->nullable()->after('description')->constrained('members')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('chores', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_to_member_id');
        });
    }
};
