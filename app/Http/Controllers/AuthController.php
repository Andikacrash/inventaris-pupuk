<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Hindari loop: session "intended" tidak boleh mengarah ke /login atau /register.
     */
    private function redirectAfterSuccessfulAuth(Request $request)
    {
        $url = $request->session()->get('url.intended');
        if (is_string($url)) {
            $path = parse_url($url, PHP_URL_PATH) ?? '';
            $path = rtrim($path, '/') ?: '/';
            if (str_ends_with($path, '/login') || str_ends_with($path, '/register')) {
                $request->session()->forget('url.intended');
            }
        }

        return redirect()->intended('/sales');
    }

    public function showLogin()
    {
        if (Auth::check()) {
            // Jangan pakai intended() di sini — bisa terjebak ke url.intended = /login
            return redirect('/sales');
        }

        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        $credentials['email'] = mb_strtolower(trim($credentials['email']));

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return $this->redirectAfterSuccessfulAuth($request);
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:kasir,admin'],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => mb_strtolower(trim($data['email'])),
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
        ]);

        return redirect()
            ->route('dashboard.index')
            ->with('success', 'Akun baru berhasil dibuat. Beritahu karyawan untuk masuk lewat halaman login dengan email ini.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
