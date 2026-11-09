<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DosenWali extends Model
{
    use HasFactory;

    protected $table = 'dosen_walis';

    protected $fillable = [
        'user_id',
        'nidn',
        'nama_lengkap',
        'gelar',
        'email',
        'no_hp',
        'prodi',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function mahasiswas(): HasMany
    {
        return $this->hasMany(Mahasiswa::class, 'dosen_wali_id');
    }

    public function catatanPembimbingan(): HasMany
    {
        return $this->hasMany(CatatanPembimbingan::class, 'dosen_wali_id');
    }
}

