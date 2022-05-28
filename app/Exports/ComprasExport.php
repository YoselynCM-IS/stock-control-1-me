<?php

namespace App\Exports;

use App\Compra;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ComprasExport implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    
    public function __construct($usuario, $inicio, $final, $tipo)
    {
        $this->usuario = $usuario;
        $this->inicio = $inicio;
        $this->final = $final;
        $this->tipo = $tipo;
    }

    public function view(): View
    {
        if($this->tipo === 'general'){
            $compras = $this->get_general();
        } else {
            $compras = $this->get_detallado();
        }
        $totales = $this->acumular_totales($compras);
        return view('download.excel.pedidos.reporte-pedidos', [
            'tipo' => $this->tipo,
            'fecha' => Carbon::now(),
            'inicio' => $this->inicio,
            'final' => $this->final,
            'compras' => $compras,
            'total_unidades' => $totales['total_unidades'],
            'total' => $totales['total']
        ]);
    }

    public function get_general(){
        if($this->usuario === 'null' && $this->final === '0000-00-00'){
            $compras = Compra::orderBy('id','desc')->get();
        }
        if($this->usuario !== 'null' && $this->inicio === '0000-00-00' && $this->final === '0000-00-00'){
            $compras = Compra::where('usuario','like','%'.$this->usuario.'%')->orderBy('id','desc')->get();
        }
        if($this->inicio !== '0000-00-00' && $this->final !== '0000-00-00'){
            $fechas = $this->format_date($this->inicio, $this->final);
            $fecha1 = $fechas['inicio'];
            $fecha2 = $fechas['final'];

            if($this->usuario === 'null'){
                $compras = Compra::whereBetween('created_at', [$fecha1, $fecha2])
                    ->orderBy('id','desc')->get(); 
            }
            if($this->usuario !== 'null'){
                $compras = Compra::where('usuario','like','%'.$this->usuario.'%')
                    ->whereBetween('created_at', [$fecha1, $fecha2])
                    ->orderBy('id','desc')->get(); 
            }
        }
        return $compras;
    }

    public function get_detallado(){
        if($this->usuario === 'null' && $this->final === '0000-00-00'){
            $compras = Compra::orderBy('id','desc')->with('pedidos.libro')->get();
        }
        if($this->usuario !== 'null' && $this->inicio === '0000-00-00' && $this->final === '0000-00-00'){
            $compras = Compra::where('usuario','like','%'.$this->usuario.'%')->with('pedidos.libro')->orderBy('id','desc')->get();
        }
        if($this->inicio !== '0000-00-00' && $this->final !== '0000-00-00'){
            $fechas = $this->format_date($this->inicio, $this->final);
            $fecha1 = $fechas['inicio'];
            $fecha2 = $fechas['final'];

            if($this->usuario === 'null'){
                $compras = Compra::whereBetween('created_at', [$fecha1, $fecha2])
                    ->with('pedidos.libro')
                    ->orderBy('id','desc')->get(); 
            }
            if($this->usuario !== 'null'){
                $compras = Compra::where('usuario','like','%'.$this->usuario.'%')
                    ->whereBetween('created_at', [$fecha1, $fecha2])
                    ->with('pedidos.libro')
                    ->orderBy('id','desc')->get(); 
            }
        }
        return $compras;
    }

    public function format_date($fecha1, $fecha2){
        $inicio = new Carbon($fecha1);
        $final 	= new Carbon($fecha2);
        $inicio = $inicio->format('Y-m-d 00:00:00');
        $final 	= $final->format('Y-m-d 23:59:59');

        $fechas = [
            'inicio' => $inicio,
            'final' => $final
        ];

        return $fechas;
    }

    public function acumular_totales($compras){
        $total_unidades = 0;
        $total = 0;
        foreach($compras as $compra){
            $total_unidades += $compra->unidades;     
            $total += $compra->total;

        }
        $totales = [
            'total_unidades' => $total_unidades,
            'total' => $total
        ];
        return $totales;
    }


}
