<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            [
                'role_name' => 'admin',
            ],
            [
                'role_name' => 'produsen data',
            ],
            [
                'role_name' => 'verifikator',
            ],
            [
                'role_name' => 'pengunjung',
            ],
        ]);
    }
}
