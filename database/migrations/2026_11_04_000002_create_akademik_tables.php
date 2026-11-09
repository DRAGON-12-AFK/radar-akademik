<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Dosen Wali
        Schema::create('dosen_walis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nidn')->unique();
            $table->string('nama_lengkap');
            $table->string('gelar')->nullable();
            $table->string('email')->nullable();
            $table->string('no_hp')->nullable();
            $table->string('prodi')->default('Teknologi Informasi');
            $table->timestamps();
        });

        // 2. Mahasiswa
        Schema::create('mahasiswas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('dosen_wali_id')->nullable()->constrained('dosen_walis')->nullOnDelete();
            $table->string('nim')->unique();
            $table->string('nama_lengkap');
            $table->string('program_studi')->default('Teknologi Informasi');
            $table->string('fakultas')->default('Fakultas Teknologi Informasi');
            $table->string('angkatan', 4);
            $table->unsignedTinyInteger('semester_berjalan')->default(1);
            $table->enum('status_akademik', ['aktif', 'cuti', 'lulus', 'drop_out'])->default('aktif');
            $table->decimal('ipk', 4, 2)->default(0.00);
            $table->unsignedSmallInteger('total_sks')->default(0);
            $table->decimal('persentase_kehadiran_total', 5, 2)->default(0.00);
            $table->enum('status_risiko', ['hijau', 'kuning', 'merah'])->default('hijau');
            $table->string('share_token', 64)->nullable()->unique();
            $table->boolean('share_token_active')->default(false);
            $table->timestamps();
        });

        // 3. Semester
        Schema::create('semesters', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 10)->unique(); // e.g. 20241
            $table->string('nama'); // e.g. Ganjil 2024/2025
            $table->string('tahun_ajaran', 10); // e.g. 2024/2025
            $table->enum('tipe', ['ganjil', 'genap', 'pendek'])->default('ganjil');
            $table->boolean('is_aktif')->default(false);
            $table->timestamps();
        });

        // 4. Keahlian (Kompetensi Prodi TI UNUJA)
        Schema::create('keahlians', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        // 5. Mata Kuliah
        Schema::create('mata_kuliahs', function (Blueprint $table) {
            $table->id();
            $table->string('kode_mk', 20)->unique();
            $table->string('nama_mk');
            $table->unsignedTinyInteger('sks');
            $table->unsignedTinyInteger('semester_paket')->default(1);
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        // 6. Pivot Mata Kuliah & Keahlian
        Schema::create('mata_kuliah_keahlian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mata_kuliah_id')->constrained('mata_kuliahs')->cascadeOnDelete();
            $table->foreignId('keahlian_id')->constrained('keahlians')->cascadeOnDelete();
            $table->decimal('bobot', 3, 2)->default(1.00); // 0.00 - 1.00
            $table->timestamps();
            $table->unique(['mata_kuliah_id', 'keahlian_id'], 'mk_keahlian_unique');
        });

        // 7. Nilai Mahasiswa
        Schema::create('nilai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->cascadeOnDelete();
            $table->foreignId('mata_kuliah_id')->constrained('mata_kuliahs')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->decimal('nilai_angka', 5, 2)->nullable();
            $table->string('nilai_huruf', 5)->default('E'); // A, B+, B, C+, C, D, E
            $table->decimal('bobot', 3, 2)->default(0.00); // 4.00, 3.50, 3.00, etc.
            $table->enum('status', ['lulus', 'tidak_lulus', 'sedang_ditempuh'])->default('sedang_ditempuh');
            $table->timestamps();
            $table->unique(['mahasiswa_id', 'mata_kuliah_id', 'semester_id'], 'mhs_mk_smt_unique');
        });

        // 8. Kehadiran Mahasiswa
        Schema::create('kehadiran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->cascadeOnDelete();
            $table->foreignId('mata_kuliah_id')->constrained('mata_kuliahs')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->unsignedTinyInteger('total_pertemuan')->default(16);
            $table->unsignedTinyInteger('total_hadir')->default(0);
            $table->unsignedTinyInteger('total_izin')->default(0);
            $table->unsignedTinyInteger('total_sakit')->default(0);
            $table->unsignedTinyInteger('total_alpa')->default(0);
            $table->decimal('persentase', 5, 2)->default(0.00);
            $table->timestamps();
            $table->unique(['mahasiswa_id', 'mata_kuliah_id', 'semester_id'], 'mhs_presensi_smt_unique');
        });

        // 9. Sistem Peringatan Dini Akademik
        Schema::create('peringatan_akademik', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->cascadeOnDelete();
            $table->foreignId('semester_id')->nullable()->constrained('semesters')->nullOnDelete();
            $table->enum('status_risiko', ['hijau', 'kuning', 'merah'])->default('hijau');
            $table->decimal('skor_risiko', 5, 2)->default(0.00);
            $table->json('indikator')->nullable();
            $table->text('alasan');
            $table->text('rekomendasi');
            $table->timestamp('dihitung_pada')->useCurrent();
            $table->timestamps();
        });

        // 10. Catatan Pembimbingan Dosen Wali
        Schema::create('catatan_pembimbingan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dosen_wali_id')->constrained('dosen_walis')->cascadeOnDelete();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->cascadeOnDelete();
            $table->date('tanggal');
            $table->text('catatan');
            $table->text('rekomendasi')->nullable();
            $table->enum('status', ['menunggu', 'ditindaklanjuti', 'selesai'])->default('menunggu');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catatan_pembimbingan');
        Schema::dropIfExists('peringatan_akademik');
        Schema::dropIfExists('kehadiran');
        Schema::dropIfExists('nilai');
        Schema::dropIfExists('mata_kuliah_keahlian');
        Schema::dropIfExists('mata_kuliahs');
        Schema::dropIfExists('keahlians');
        Schema::dropIfExists('semesters');
        Schema::dropIfExists('mahasiswas');
        Schema::dropIfExists('dosen_walis');
    }
};

