<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Buku extends Model
{
    protected $table = 'buku';
    protected $primaryKey = 'id_buku';

    protected $fillable = [
        'id_buku',
        'judul',
        'genre',
        'penulis',
        'penerbit',
        'tahun_terbit',
        'stok'
    ];

    protected $casts = [
        'tahun_terbit' => 'year',
    ];

    protected $hidden = [
        'id_buku'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    public function ratings()
    {
        return $this->hasMany(Rate::class, 'book_id', 'id_buku');
    }
}