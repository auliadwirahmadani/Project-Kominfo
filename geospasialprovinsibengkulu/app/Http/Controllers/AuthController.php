<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Tampilkan form login
    public function index()
    {
        return view('login');
    }

    // Proses login
    public function login(Request $request)
    {
        // 1. Validasi input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $credentials = $request->only('email', 'password');

        // 2. Cek kredensial
        if (Auth::attempt($credentials)) {
            
            $request->session()->regenerate();

            // Ambil role dan normalisasi
            $role = strtolower(trim(Auth::user()->role_name));

            // 3. Redirect berdasarkan role
            switch ($role) {
                case 'admin':
                    return redirect()->intended(route('admin.dashboard'));
                
                case 'produsen data':
                    return redirect()->intended(route('produsen.dashboard'));
                
                case 'verifikator':
                    return redirect()->intended(route('verifikator.dashboard'));
                
                default:
                    // Role tidak dikenali, logout dan tampilkan error
                    Auth::logout();
                    return back()->with('error', 'Role pengguna tidak dikenali.')->withInput();
            }
        }

        // 4. Jika gagal login
        return back()->with('error', 'Email atau password salah')->withInput();
    }

    // ✅ Logout - FIX: Redirect ke halaman geo (/)
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // ✅ Redirect ke halaman geo / root
        return redirect('/');
        // Atau jika ingin pakai named route:
        // return redirect()->route('geo');
    }
}