<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Corte extends Model
{
    protected $fillable = [
        'tipo', 'inicio', 'final'
    ];
}
