<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhanLoai extends Model
{
    use HasFactory;
    protected $primarykey = 'maphanloai';
    protected $table = 'phanloai';
    protected $fillable = [
        'maphanloai',
        'tenphanloai',

    ];
}
