<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhanLoai extends Model
{
    use HasFactory;
    protected $primaryKey = 'maphanloai';
    protected $table = 'phanloai';
    protected $fillable = [
        'maphanloai',
        'tenphanloai',

    ];
    public function Gia()
{
    return $this->hasMany(Gia::class, 'maphanloai');
}

}
