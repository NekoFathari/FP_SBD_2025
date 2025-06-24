<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Rate extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'buku';
    protected $fillable = [
        'user_id',
        'book_id',
        'rating',
        'comment',
    ];
}
