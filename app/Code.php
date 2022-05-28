<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Libro;
use App\Dato;

class Code extends Model
{
    protected $fillable = [
        'id', 
        'libro_id', 
        'codigo',
        'tipo',
        'estado'
    ];

    //Uno a muchos (Inversa)
    //Un codigo solo puede tener un libro
    public function libro(){
        return $this->belongsTo(Libro::class);
    }

    // Muchos a muchos
    public function datos(){
        return $this->belongsToMany(Dato::class)->withPivot('devolucion');
    }
}
