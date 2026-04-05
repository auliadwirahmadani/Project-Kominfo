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
        // Tentukan daftar kemungkinan nama tabel (jamak & tunggal)
        $tables = ['metadata_layers', 'metadata_layer'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    
                    // 1. Hapus kolom datetime yang lama jika masih ada
                    if (Schema::hasColumn($tableName, 'datetime')) {
                        $table->dropColumn('datetime');
                    }
                    
                    // 2. Tambahkan created_at HANYA JIKA belum ada
                    if (!Schema::hasColumn($tableName, 'created_at')) {
                        $table->timestamp('created_at')->nullable();
                    }

                    // 3. Tambahkan updated_at HANYA JIKA belum ada
                    if (!Schema::hasColumn($tableName, 'updated_at')) {
                        $table->timestamp('updated_at')->nullable();
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['metadata_layers', 'metadata_layer'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    
                    // Hapus created_at dan updated_at jika ada
                    if (Schema::hasColumn($tableName, 'created_at')) {
                        $table->dropColumn('created_at');
                    }
                    if (Schema::hasColumn($tableName, 'updated_at')) {
                        $table->dropColumn('updated_at');
                    }
                    
                    // Kembalikan kolom datetime jika belum ada
                    if (!Schema::hasColumn($tableName, 'datetime')) {
                        $table->dateTime('datetime')->nullable();
                    }
                });
            }
        }
    }
};