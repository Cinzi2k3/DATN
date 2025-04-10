<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatVe extends Model
{
    use HasFactory;
    protected $primaryKey = 'madatve';
    protected $table = 'datve';
    protected $fillable = [
        'madatve',
        'macho',
        'malichtrinh',
        'trangthai'
    ];
    public function Cho()
    {
        return $this->belongsTo(Cho::class, 'macho');
    }
    public function LichTrinh()
    {
        return $this->belongsTo(LichTrinh::class, 'malichtrinh');
    }
}
