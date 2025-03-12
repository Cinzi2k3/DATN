<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tau extends Model
{
    use HasFactory;
    protected $primaryKey = 'matau';
    protected $table = 'tau';
    protected $fillable = [
        'matau',
        'maloaitau',
        'tentau',
    ];
    public function LoaiTau(){
        return $this -> belongsTo(LoaiTau::class,'maloaitau');
    }
    public function Toa(){
        return $this ->hasMany(Toa::class,'matau');
    }

    public function Cho(){
        return $this ->hasMany(Cho::class,'matau');
    }
    public function LichTrinh() {
        return $this->hasMany(LichTrinh::class, 'matau');
    }

}
