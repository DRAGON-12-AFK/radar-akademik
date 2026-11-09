<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeringatanAkademik extends Model
{
    use HasFactory;

    protected $table = 'peringatan_akademik';

    protected $fillable = [
        'mahasiswa_id',
        'semester_id',
        'status_risiko',
        'skor_risiko',
        'indikator',
        'alasan',
        'rekomendasi',
        'dihitung_pada',
    ];

    protected function casts(): array
    {
        return [
            'skor_risiko' => 'decimal:2',
            'indikator' => 'array',
            'dihitung_pada' => 'datetime',
        ];
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }
}

