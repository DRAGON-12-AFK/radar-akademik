<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Keahlian extends Model
{
    use HasFactory;

    protected $table = 'keahlians';

    protected $fillable = [
        'kode',
        'nama',
        'deskripsi',
    ];

    public function mataKuliahs(): BelongsToMany
    {
        return $this->belongsToMany(MataKuliah::class, 'mata_kuliah_keahlian')
            ->withPivot('bobot')
            ->withTimestamps();
    }
}

