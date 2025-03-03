<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ve extends Model
{
    use HasFactory;
    protected $primarykey = 'mave';
    protected $table = 've';
    protected $fillable = [
        'mave',
        'maloaive',
        'malichtrinh',
        'manguoidung',
        'macho',
        'thoihan',
        'trangthai'
    ];
}
