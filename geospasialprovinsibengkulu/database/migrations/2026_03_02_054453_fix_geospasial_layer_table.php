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
        // Kita cek tabel dengan nama 'geospatial_layer'
        if (Schema::hasTable('geospatial_layer')) {
            
            Schema::table('geospatial_layer', function (Blueprint $table) {
                
                // 1. Tambah kolom updated_at jika belum ada
                if (!Schema::hasColumn('geospatial_layer', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable()->after('created_at');
                }

                // 2. Tambah kolom user_id dan Foreign Key jika belum ada
                if (!Schema::hasColumn('geospatial_layer', 'user_id')) {
                    $table->unsignedBigInteger('user_id')->nullable()->after('category_id');
                    $table->foreign('user_id')
                          ->references('id')
                          ->on('users')
                          ->onDelete('cascade');
                }

                // 3. Hapus kolom 'id' jika ada
                if (Schema::hasColumn('geospatial_layer', 'id')) {
                    $table->dropColumn('id');
                }
            });

        } else {
            // Jika tabel tidak ditemukan, kita biarkan saja agar tidak error.
            // Tapi ini tandanya nama tabel di DB kamu BUKAN 'geospatial_layer'
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('geospatial_layer')) {
            Schema::table('geospatial_layer', function (Blueprint $table) {
                if (Schema::hasColumn('geospatial_layer', 'user_id')) {
                    $table->dropForeign(['user_id']);
                }
                $table->dropColumn(['updated_at', 'user_id']);
            });
        }
    }
};