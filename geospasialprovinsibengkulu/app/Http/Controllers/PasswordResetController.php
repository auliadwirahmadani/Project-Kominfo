<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\ResetPasswordMail;
use App\Models\User;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    /**
     * Tampilkan form Lupa Kata Sandi
     */
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Kirim link reset password ke email
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
        ]);

        $user = User::where('email', $request->email)->first();

        // Selalu tampilkan pesan sukses (untuk keamanan, tidak memberi tahu apakah email terdaftar)
        if (!$user) {
            return back()->with('status', 'Jika email tersebut terdaftar, link reset kata sandi telah dikirim.');
        }

        // Hapus token lama jika ada
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Buat token baru
        $token = Str::random(64);

        DB::table('password_reset_tokens')->insert([
            'email'      => $request->email,
            'token'      => Hash::make($token),
            'created_at' => Carbon::now(),
        ]);

        $resetUrl = route('password.reset.form', ['token' => $token, 'email' => urlencode($request->email)]);

        try {
            Mail::to($request->email)->send(new ResetPasswordMail($resetUrl, $user->name));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengirim email. Silakan coba beberapa saat lagi.')->withInput();
        }

        return back()->with('status', 'Link reset kata sandi telah dikirim ke email Anda. Silakan periksa kotak masuk atau folder spam.');
    }

    /**
     * Tampilkan form Reset Kata Sandi dari link email
     */
    public function showResetForm(Request $request)
    {
        $token = $request->query('token');
        $email = urldecode($request->query('email'));

        // Validasi token ada
        $record = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$record) {
            return redirect()->route('password.forgot')
                ->with('error', 'Link reset tidak valid atau sudah kadaluarsa.');
        }

        // Cek apakah token sudah expired (60 menit)
        $createdAt = Carbon::parse($record->created_at);
        if (Carbon::now()->diffInMinutes($createdAt) > 60) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return redirect()->route('password.forgot')
                ->with('error', 'Link reset sudah kadaluarsa. Silakan minta ulang.');
        }

        // Verifikasi token
        if (!Hash::check($token, $record->token)) {
            return redirect()->route('password.forgot')
                ->with('error', 'Link reset tidak valid.');
        }

        return view('auth.reset-password', compact('token', 'email'));
    }

    /**
     * Proses reset kata sandi baru
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'                 => 'required|email|exists:users,email',
            'token'                 => 'required',
            'password'              => 'required|min:8|confirmed',
            'password_confirmation' => 'required',
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.exists'      => 'Email tidak ditemukan.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min'      => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $email = $request->email;
        $token = $request->token;

        $record = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$record) {
            return back()->with('error', 'Link reset tidak valid atau sudah kadaluarsa.')->withInput();
        }

        // Cek expired
        $createdAt = Carbon::parse($record->created_at);
        if (Carbon::now()->diffInMinutes($createdAt) > 60) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return redirect()->route('password.forgot')
                ->with('error', 'Link reset sudah kadaluarsa. Silakan minta ulang.');
        }

        // Verifikasi token
        if (!Hash::check($token, $record->token)) {
            return back()->with('error', 'Link reset tidak valid.')->withInput();
        }

        // Update password
        User::where('email', $email)->update([
            'password' => Hash::make($request->password),
        ]);

        // Hapus token
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        return redirect()->route('login')
            ->with('success', 'Kata sandi berhasil diubah! Silakan masuk dengan kata sandi baru Anda.');
    }
}
