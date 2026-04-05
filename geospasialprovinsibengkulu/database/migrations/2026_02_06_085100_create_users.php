<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {

            $table->increments('user_id');

            $table->unsignedBigInteger('role_id');

            $table->string('name', 100);
            $table->string('email', 100)->unique();
            $table->string('password', 255);

            $table->string('status', 20)->default('aktif');

            $table->rememberToken();
            $table->timestamps();

            // foreign key
            $table->foreign('role_id')
                  ->references('role_id')
                  ->on('roles')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};