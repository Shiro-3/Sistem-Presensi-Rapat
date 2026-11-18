<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rapat_id')
                  ->constrained('rapats')
                  ->onDelete('cascade');
            $table->string('nama_lengkap');
            $table->string('nip');
            $table->string('jabatan');
            $table->string('unit_kerja');
            $table->longText('tanda_tangan');
            $table->dateTime('waktu_hadir');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensis');
    }
};