<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Remcliente;
use App\Remisione;
use App\Adeudo;

class Cliente extends Model
{
    protected $fillable = [
        'id', 'name', 'contacto', 'email', 'telefono', 'direccion', 'condiciones_pago', 'rfc', 'fiscal'
    ];

    //Uno a muchos
    //Un cliente puede tener muchas remisiones
    public function remisiones(){
        return $this->hasMany(Remisione::class);
    }

    //Uno a muchos
    //Un cliente puede tener muchos adeudos
    public function adeudos(){
        return $this->hasMany(Adeudo::class);
    }

    //Uno a uno
    //Un cliente solo puede tener un remcliente
    public function remcliente(){
        return $this->hasOne(Remcliente::class);
    }
}
