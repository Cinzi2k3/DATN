<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoaiCho extends Model
{
    use HasFactory;
    protected $primarykey = 'maloaicho';
    protected $table = 'loaicho';
    protected $fillable = [
        'maloaicho',
        'tenloaicho',
        'matoa',
    ];
}
