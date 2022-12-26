<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Entdevolucione;
use App\Repayment;
use App\Registro;

class Entrada extends Model
{
    protected $fillable = [
        'id', 
        'corte_id',
        'folio',
        'editorial',
        'unidades', 
        'total',
        'total_pagos',
        'total_devolucion',
        'lugar',
        'creado_por',
        'name', 'size', 'extension', 'public_url'
    ];

    //Uno a muchos
    //Una entrada puede tener muchos registros
    public function registros(){
        return $this->hasMany(Registro::class);
    }

    //Uno a muchos
    //Una entrada puede tener muchos pagos
    public function repayments(){
        return $this->hasMany(Repayment::class);
    }

    //Uno a muchos
    //Una entrada puede tener muchas devoluciones
    public function entdevoluciones(){
        return $this->hasMany(Entdevolucione::class);
    }

}
