<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MataKuliah extends Model
{
    use HasFactory;

    protected $table = 'mata_kuliahs';

    protected $fillable = [
        'kode_mk',
        'nama_mk',
        'sks',
        'semester_paket',
        'deskripsi',
    ];

    protected function casts(): array
    {
        return [
            'sks' => 'integer',
            'semester_paket' => 'integer',
        ];
    }

    public function keahlians(): BelongsToMany
    {
        return $this->belongsToMany(Keahlian::class, 'mata_kuliah_keahlian')
            ->withPivot('bobot')
            ->withTimestamps();
    }

    public function nilai(): HasMany
    {
        return $this->hasMany(Nilai::class, 'mata_kuliah_id');
    }

    public function kehadiran(): HasMany
    {
        return $this->hasMany(Kehadiran::class, 'mata_kuliah_id');
    }
}

