<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Anggota extends Model
{   
    public $timestamps = false; // Tambahkan baris ini
    protected $table = 'admin';

    protected $primaryKey = 'id_admin';

    protected $fillable = [
        'id_admin',
        'username',
        'no_hp',
        'shift',
    ];

    protected $hidden = [
        'id_admin'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }
}