<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Cliente;

class Seguimiento extends Model
{
    protected $fillable = ['user_id', 'cliente_id', 'tipo', 'situacion', 'respuesta', 'fecha_hora', 'duracion', 'comentario'];

    public function cliente(){
        return $this->belongsTo(Cliente::class);
    }
}
