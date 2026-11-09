<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nilai extends Model
{
    use HasFactory;

    protected $table = 'nilai';

    protected $fillable = [
        'mahasiswa_id',
        'mata_kuliah_id',
        'semester_id',
        'nilai_angka',
        'nilai_huruf',
        'bobot',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'nilai_angka' => 'decimal:2',
            'bobot' => 'decimal:2',
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

    /**
     * Konversi nilai angka ke bobot dan nilai huruf standar UNUJA
     */
    public static function konversiNilai(float $angka): array
    {
        if ($angka >= 85) {
            return ['huruf' => 'A', 'bobot' => 4.00, 'status' => 'lulus'];
        } elseif ($angka >= 80) {
            return ['huruf' => 'A-', 'bobot' => 3.75, 'status' => 'lulus'];
        } elseif ($angka >= 75) {
            return ['huruf' => 'B+', 'bobot' => 3.50, 'status' => 'lulus'];
        } elseif ($angka >= 70) {
            return ['huruf' => 'B', 'bobot' => 3.00, 'status' => 'lulus'];
        } elseif ($angka >= 65) {
            return ['huruf' => 'B-', 'bobot' => 2.75, 'status' => 'lulus'];
        } elseif ($angka >= 60) {
            return ['huruf' => 'C+', 'bobot' => 2.50, 'status' => 'lulus'];
        } elseif ($angka >= 55) {
            return ['huruf' => 'C', 'bobot' => 2.00, 'status' => 'lulus'];
        } elseif ($angka >= 40) {
            return ['huruf' => 'D', 'bobot' => 1.00, 'status' => 'tidak_lulus'];
        } else {
            return ['huruf' => 'E', 'bobot' => 0.00, 'status' => 'tidak_lulus'];
        }
    }
}

