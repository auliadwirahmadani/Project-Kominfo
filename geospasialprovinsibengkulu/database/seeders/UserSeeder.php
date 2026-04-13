<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil role_id berdasarkan nama role yang sudah ada di tabel roles
        $adminId      = DB::table('roles')->where('role_name', 'admin')->value('role_id');
        $produsenId   = DB::table('roles')->where('role_name', 'produsen')->value('role_id');
        $verifikatorId = DB::table('roles')->where('role_name', 'verifikator')->value('role_id');

        $users = [
            [
                'role_id'  => $adminId,
                'name'     => 'Admin',
                'email'    => 'admin@geoportal.id',
                'password' => Hash::make('admin123'),
                'status'   => 'aktif',
            ],
            [
                'role_id'  => $produsenId,
                'name'     => 'Produsen Data',
                'email'    => 'produsen@geoportal.id',
                'password' => Hash::make('produsen123'),
                'status'   => 'aktif',
            ],
            [
                'role_id'  => $verifikatorId,
                'name'     => 'Verifikator',
                'email'    => 'verifikator@geoportal.id',
                'password' => Hash::make('verifikator123'),
                'status'   => 'aktif',
            ],
        ];

        foreach ($users as $user) {
            // Cek dulu, kalau email sudah ada, skip
            $exists = DB::table('users')->where('email', $user['email'])->exists();
            if (!$exists) {
                DB::table('users')->insert($user);
            }
        }
    }
}
