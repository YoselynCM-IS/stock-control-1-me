<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reporte extends Model
{
    protected $fillable = [
        'id', 'user_id', 'type', 'reporte', 'estado', 'comentario',
        'name_table', 'id_table'
    ];
}
