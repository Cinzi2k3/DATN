<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Toa extends Model
{
    use HasFactory;
    protected $primaryKey = 'matoa';
    protected $table = 'toa';
    protected $fillable = [
        'matoa',
        'maloaitoa',
        'matau',
        'tentoa',
        'sotang',
        'socho',
        'sochodadat',
        'sochocon',
    ];
    public function LoaiToa(){
        return $this -> belongsTo(LoaiToa::class, 'maloaitoa');
    }
    
    public function Cho(){
        return $this -> hasMany(Cho::class,'matoa');
    }
    public function Gia(){
        return $this->hasMany(Gia::class, 'matoa');
    }
    public function Tau()
{
    return $this->belongsTo(Tau::class, 'matau');
}
public function sochocon()
{
    return $this->socho - $this->sochodadat;
}
}
