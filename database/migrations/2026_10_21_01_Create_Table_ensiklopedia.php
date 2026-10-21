<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ensiklopedia', function (Blueprint $table) {
            $table->uuid('id_ensiklopedia')->primary();
            $table->uuid('id_petani')->nullable(); // null = entri bawaan sistem (hanya baca)
            $table->string('judul');
            $table->string('kategori');
            $table->string('ringkasan', 300);
            $table->text('isi');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ensiklopedia');
    }
};