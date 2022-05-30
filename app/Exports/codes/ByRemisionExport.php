<?php

namespace App\Exports\codes;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ByRemisionExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $remisione_id;
    
    public function __construct($remisione_id)
    {
        $this->remisione_id = $remisione_id;
    }

    public function headings(): array
    {
        return [
            'ISBN', 
            'LIBRO',
            'CÓDIGO'
        ];
    }

    public function collection(){ 
        $codigos = \DB::table('code_dato')
                        ->join('codes', 'code_dato.code_id', '=', 'codes.id')
                        ->join('datos', 'code_dato.dato_id', '=', 'datos.id')
                        ->join('libros', 'datos.libro_id', '=', 'libros.id')
                        ->where('datos.remisione_id', $this->remisione_id)
                        ->select('libros.ISBN', 'libros.titulo', 'codes.codigo')
                        ->get();
        return $codigos;
    }
}
