<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Prodevolucione;
use App\Departure;

class Promotion extends Model
{
    protected $fillable = [
        'id', 
        'folio',
        'plantel',
        'descripcion', 
        'unidades',  
        'unidades_devolucion',
        'unidades_pendientes',
        'entregado_por',
        'estado',
        'creado_por'
    ];

    //Uno a muchos
    //Una promoción puede tener muchas salidas
    public function departures(){
        return $this->hasMany(Departure::class);
    }

    //Uno a muchos
    //Una promoción puede tener muchas devoluciones
    public function prodevoluciones(){
        return $this->hasMany(Prodevolucione::class);
    }
}
