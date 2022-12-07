<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Libro;

class Peticione extends Model
{
    protected $fillable = [
        'pedido_id',
        'libro_id', 
        'quantity',
        'existencia',
        'faltante',
        'solicitar'
    ];

    public function libro(){
        return $this->belongsTo(Libro::class);
    }
}
