<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $table = 'mahasiswas';

    protected $fillable = [
        'user_id',
        'dosen_wali_id',
        'nim',
        'nama_lengkap',
        'program_studi',
        'fakultas',
        'angkatan',
        'semester_berjalan',
        'status_akademik',
        'ipk',
        'total_sks',
        'persentase_kehadiran_total',
        'status_risiko',
        'share_token',
        'share_token_active',
    ];

    protected function casts(): array
    {
        return [
            'ipk' => 'decimal:2',
            'persentase_kehadiran_total' => 'decimal:2',
            'share_token_active' => 'boolean',
            'semester_berjalan' => 'integer',
            'total_sks' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function dosenWali(): BelongsTo
    {
        return $this->belongsTo(DosenWali::class, 'dosen_wali_id');
    }

    public function nilai(): HasMany
    {
        return $this->hasMany(Nilai::class, 'mahasiswa_id');
    }

    public function kehadiran(): HasMany
    {
        return $this->hasMany(Kehadiran::class, 'mahasiswa_id');
    }

    public function peringatanAkademik(): HasMany
    {
        return $this->hasMany(PeringatanAkademik::class, 'mahasiswa_id');
    }

    public function catatanPembimbingan(): HasMany
    {
        return $this->hasMany(CatatanPembimbingan::class, 'mahasiswa_id');
    }

    public function generateShareToken(): string
    {
        $this->share_token = Str::random(40);
        $this->share_token_active = true;
        $this->save();

        return $this->share_token;
    }

    public function revokeShareToken(): void
    {
        $this->share_token_active = false;
        $this->save();
    }
}

