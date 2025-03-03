<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Toa extends Model
{
    use HasFactory;
    protected $primarykey = 'matoa';
    protected $table = 'toa';
    protected $fillable = [
        'matoa',
        'maloaitoa',
        'matau',
        'tentoa',
    ];
}
