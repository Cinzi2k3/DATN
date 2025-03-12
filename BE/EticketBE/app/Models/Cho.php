<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cho extends Model
{
    use HasFactory;
    protected $primaryKey = 'macho';
    protected $table = 'cho';
    protected $fillable = [
        'macho',
        'maloaicho',
        'matoa',
        'matau',
        'sohieu',
        'trangthai',
    ];
    public function LoaiCho(){
        return $this -> belongsTo(LoaiCho::class,'maloaicho');
    }
    public function Toa(){
        return $this -> belongsTo(Toa::class,'matoa');
    }
    public function Tau(){
        return $this -> belongsTo(Tau::class,'matau');
    }
    public function Ve(){
        return $this -> hasOne(Ve::class,'macho');
    }
}
