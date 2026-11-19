<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rapats', function (Blueprint $table) {
            $table->tinyInteger('minta_foto')->default(0)->after('detail_lokasi');
        });

        Schema::table('absensis', function (Blueprint $table) {
            $table->longText('foto')->nullable()->after('tanda_tangan');
        });
    }

    public function down(): void
    {
        Schema::table('rapats', function (Blueprint $table) {
            $table->dropColumn('minta_foto');
        });

        Schema::table('absensis', function (Blueprint $table) {
            $table->dropColumn('foto');
        });
    }
};
