<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chores', function (Blueprint $table) {
            $table->date('assigned_for_date')->nullable()->after('assigned_to_member_id');
        });
    }

    public function down(): void
    {
        Schema::table('chores', function (Blueprint $table) {
            $table->dropColumn('assigned_for_date');
        });
    }
};
