<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ga extends Model
{
    use HasFactory;
    protected $primarykey = 'maga';
    protected $table = 'ga';
    protected $fillable = [
        'maga',
        'tenga',
        'diachi',
    ];
}
