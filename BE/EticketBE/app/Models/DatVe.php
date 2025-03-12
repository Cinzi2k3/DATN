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
        'matau',
        'matoa',
    ];
    public function Tau()
    {
        return $this->belongsTo(Tau::class, 'matau');
    }
    public function Toa()
    {
        return $this->belongsTo(Toa::class, 'matoa');
    }
}
