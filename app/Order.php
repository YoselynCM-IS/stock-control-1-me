<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Element;
use App\Pedido;

class Order extends Model
{
    protected $fillable = [
        'id',
        'pedido_id',
        'cliente_id',
        'identifier', 
        'date',
        'provider',
        'destination',  
        'total_bill', 
        'status', 
        'observations', 
        'actual_total_bill',
        'creado_por'
    ];

    public function elements(){
        return $this->hasMany(Element::class);
    }

    public function pedido(){
        return $this->belongsTo(Pedido::class);
    }
}
