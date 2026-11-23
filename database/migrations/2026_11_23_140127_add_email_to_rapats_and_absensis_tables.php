<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rapats', function (Blueprint $table) {
            $table->tinyInteger('minta_email')->default(0)->after('minta_foto');
        });

        Schema::table('absensis', function (Blueprint $table) {
            $table->string('email')->nullable()->after('unit_kerja');
        });
    }

    public function down(): void
    {
        Schema::table('rapats', function (Blueprint $table) {
            $table->dropColumn('minta_email');
        });

        Schema::table('absensis', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }
};
