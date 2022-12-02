<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Cliente;
use App\User;

class Actividade extends Model
{
    protected $fillable = [
        'id', 'user_id', 'cliente_id', 'tipo', 'descripcion', 'estado', 'fecha_recordatorio'
    ];   

    public function cliente(){
        return $this->belongsTo(Cliente::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}