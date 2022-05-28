<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Element;

class Order extends Model
{
    protected $fillable = [
        'id',
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
}
