<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->timestamps();
        });

        // Seed default categories matching previous bill types
        DB::table('categories')->insert([
            ['name' => 'Listrik', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Air', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Internet', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Gas', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Makanan', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Lainnya', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
