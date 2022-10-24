<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Remdeposito;

class Corte extends Model
{
    protected $fillable = [
        'tipo', 'inicio', 'final'
    ];

    //Uno a muchos
    //Un corte puede tener muchos depositos
    public function remdepositos(){
        return $this->hasMany(Remdeposito::class);
    }
}
