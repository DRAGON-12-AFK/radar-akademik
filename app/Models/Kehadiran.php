<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kehadiran extends Model
{
    use HasFactory;

    protected $table = 'kehadiran';

    protected $fillable = [
        'mahasiswa_id',
        'mata_kuliah_id',
        'semester_id',
        'total_pertemuan',
        'total_hadir',
        'total_izin',
        'total_sakit',
        'total_alpa',
        'persentase',
    ];

    protected function casts(): array
    {
        return [
            'total_pertemuan' => 'integer',
            'total_hadir' => 'integer',
            'total_izin' => 'integer',
            'total_sakit' => 'integer',
            'total_alpa' => 'integer',
            'persentase' => 'decimal:2',
        ];
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id');
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function hitungPersentase(): float
    {
        if ($this->total_pertemuan > 0) {
            $this->persentase = round(($this->total_hadir / $this->total_pertemuan) * 100, 2);
        } else {
            $this->persentase = 0.00;
        }

        return $this->persentase;
    }
}

