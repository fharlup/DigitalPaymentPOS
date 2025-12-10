<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class LoginPage extends Component
{
    public $email = '';
    public $password = '';

    public function login()
    {
        // Validasi input
        $this->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Coba Login
        // Karena Admin & Kasir ada di tabel 'users', keduanya bisa login di sini
        if (Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
            session()->regenerate();
            return redirect()->route('kasir');
        }

        // Jika gagal
        $this->addError('email', 'Email atau password salah.');
    }

    public function render()
    {
        return view('livewire.login-page');
    }
}