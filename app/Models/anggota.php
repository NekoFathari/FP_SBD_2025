<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Anggota extends Model
{   
    public $timestamps = false; // Tambahkan baris ini
    protected $table = 'anggota';

    protected $primaryKey = 'id_anggota';

    protected $fillable = [
        'id_anggota',
        'username',
        'name',
        'alamat',
        'no_hp',
        'tanggal_join',
    ];

    protected $hidden = [
        'id_anggota'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }
}