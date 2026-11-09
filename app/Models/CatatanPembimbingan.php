<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatatanPembimbingan extends Model
{
    use HasFactory;

    protected $table = 'catatan_pembimbingan';

    protected $fillable = [
        'dosen_wali_id',
        'mahasiswa_id',
        'tanggal',
        'catatan',
        'rekomendasi',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function dosenWali(): BelongsTo
    {
        return $this->belongsTo(DosenWali::class, 'dosen_wali_id');
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }
}

