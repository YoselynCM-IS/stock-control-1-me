<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Pedido;

class Compra extends Model
{
    protected $fillable = [
        'id', 'pedido', 
        'usuario', 'unidades', 
        'total', 'tipo_pago', 
        'entregado', 'entregado_por'
    ];

    //Uno a muchos
    //Una compra puede tener muchos pedidos
    public function pedidos(){
        return $this->hasMany(Pedido::class);
    }
}
