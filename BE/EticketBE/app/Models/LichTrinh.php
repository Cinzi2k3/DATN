<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LichTrinh extends Model
{
    use HasFactory;
    protected $primarykey = 'malichtrinh';
    protected $table = 'lichtrinh';
    protected $fillable = [
        'malichtrinh',
        'matau',
        'matuyenduong',
        'thoigiandi',
        'thoigianden',
    ];
}
