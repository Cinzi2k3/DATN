<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tau extends Model
{
    use HasFactory;
    protected $primarykey = 'matau';
    protected $table = 'tau';
    protected $fillable = [
        'matau',
        'maloaitau',
        'tentau',

    ];
}
