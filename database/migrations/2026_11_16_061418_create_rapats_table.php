<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rapats', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('judul_rapat');
            $table->string('penyelenggara');
            $table->date('tanggal_rapat');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->enum('tipe_lokasi', ['Daring', 'Luring', 'Daring & Luring']);
            $table->text('detail_lokasi');
            $table->enum('status', ['aktif', 'selesai'])->default('aktif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rapats');
    }
};