<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gia extends Model
{
    use HasFactory;
    protected $primarykey = 'magia';
    protected $table = 'gia';
    protected $fillable = [
        'magia',
        'matau',
        'matoa',
        'matuyenduong',
        'matang',
        'maphanloai',
        'maloaive',
        'gia',
    ];
}
