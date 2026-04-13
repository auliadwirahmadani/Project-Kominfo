<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('geospatial_layer', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('geospatial_id');
            // Jika Anda punya tabel users, bisa tambahkan foreign key constraint. Misalnya:
            // $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('geospatial_layer', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }
};
