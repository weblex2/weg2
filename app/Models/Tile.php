<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tile extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];
}
