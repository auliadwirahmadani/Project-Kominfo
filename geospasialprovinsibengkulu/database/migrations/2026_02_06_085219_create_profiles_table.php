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
        Schema::create('profiles', function (Blueprint $table) {
            $table->increments('profile_id');
            $table->integer('user_id')->nullable();
            $table->string('instansi', 150)->nullable();
            $table->text('alamat')->nullable();
            $table->string('no_hp', 20)->nullable();

            $table->foreign('user_id')
                  ->references('user_id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
