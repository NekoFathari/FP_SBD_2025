<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pinjam extends Model
{
    public $timestamps = false; // Tambahkan baris ini
    protected $table = 'riwayat';

    protected $primaryKey = 'id_riwayat';

    protected $fillable = [
        'id_buku',
        'id_anggota_pinjam',
        'tanggal_pinjam',
        'tanggal_kembali',
        'status',
    ];

    protected $hidden = [
        'id_riwayat',
    ];

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class, 'id_anggota_pinjam', 'id_anggota');
    }

    public function buku(): BelongsTo
    {
        return $this->belongsTo(Buku::class, 'id_buku', 'id_buku');
    }
}