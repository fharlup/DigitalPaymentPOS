<?php

namespace App\Models;

// Import class yang dibutuhkan
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

// Pastikan ada "implements FilamentUser"
class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    /**
     * PENTING: 'role' harus ada di sini agar bisa diisi data.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // <--- Pastikan ini ada!
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Logika Pintu Masuk Dashboard Admin (Filament)
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Hanya user dengan role 'admin' yang boleh masuk
        return $this->role === 'admin';
    }
}