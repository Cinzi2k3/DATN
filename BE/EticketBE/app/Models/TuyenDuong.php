<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TuyenDuong extends Model
{
    use HasFactory;
    protected $primaryKey = 'matuyenduong';
    protected $table = 'tuyenduong';
    protected $fillable = [
        'matuyenduong',
        'gadi',
        'gaden',
        'khoangcach',
    ];
    public function LichTrinh(){
       return $this->hasMany(LichTrinh::class, 'matuyenduong');
    }
    public function GaDi(){
        return $this -> belongsTo(Ga::class,'gadi','maga');
    }
    public function GaDen(){
        return $this -> belongsTo(Ga::class,'gaden','maga');
    }

}
