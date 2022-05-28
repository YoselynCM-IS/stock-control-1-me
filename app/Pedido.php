<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Compra;
use App\Libro;

class Pedido extends Model
{
    protected $fillable = ['compra_id', 'libro_id', 'costo_unitario', 'unidades', 'total'];

    //Uno a muchos (Inversa)
    //Un pedido solo puede pertenecer a una compra
    public function compra(){
        return $this->belongsTo(Compra::class);
    }

    //Uno a muchos (Inversa)
    //Un pedido solo puede tener un libro
    public function libro(){
        return $this->belongsTo(Libro::class);
    }
}
