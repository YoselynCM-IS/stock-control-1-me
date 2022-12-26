<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ectotale extends Model
{
    protected $fillable = [
        'corte_id', 'editorial_id', 'total', 'total_devolucion', 'total_pagos',
        'total_pagar', 'total_favor', 'corte_id_favor'
    ];
}
