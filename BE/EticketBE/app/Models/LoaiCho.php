<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoaiCho extends Model
{
    use HasFactory;
    protected $primaryKey = 'maloaicho';
    protected $table = 'loaicho';
    protected $fillable = [
        'maloaicho',
        'tenloaicho',
        'maloaitoa',
    ];
    public function Cho(){
        return $this -> hasMany (Cho::class,'maloaicho');
    }
    public function LoaiToa(){
        return $this -> belongsTo(LoaiToa::class,'maloaitoa');
    }
}
