<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

// Test 1: Cek user ada
$user = User::where('email', 'auliadwirahmadani81@gmail.com')->first();

if (!$user) {
    echo "❌ User tidak ditemukan di database!\n";
    exit(1);
}

echo "✅ User ditemukan:\n";
echo "   user_id  : " . $user->user_id . "\n";
echo "   name     : " . $user->name . "\n";
echo "   email    : " . $user->email . "\n";
echo "   role_id  : " . $user->role_id . "\n";
echo "   status   : " . $user->status . "\n";

// Test 2: Cek password cocok
$passwordMatch = Hash::check('dewahomophobic13', $user->password);
echo "\n" . ($passwordMatch ? "✅" : "❌") . " Password check: " . ($passwordMatch ? "COCOK" : "TIDAK COCOK") . "\n";

// Test 3: Cek relasi role
$role = $user->role;
if ($role) {
    echo "✅ Role ditemukan: " . $role->role_name . "\n";
} else {
    echo "❌ Role TIDAK ditemukan! role_id=" . $user->role_id . " tidak ada di tabel roles\n";
}

// Test 4: Cek Auth::attempt
$attempt = Auth::attempt(['email' => 'auliadwirahmadani81@gmail.com', 'password' => 'dewahomophobic13']);
echo "\n" . ($attempt ? "✅" : "❌") . " Auth::attempt: " . ($attempt ? "BERHASIL" : "GAGAL") . "\n";
