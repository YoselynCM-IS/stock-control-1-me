<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Input;
use App\Exports\ComprasExport;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Pedido;
use App\Compra;
use App\Libro;
use Excel;
use PDF;

class CompraController extends Controller
{
    public function store(Request $request){
        \DB::beginTransaction();
        try{
            $compra = Compra::create([
                'pedido'   => $request->num_pedido,
                'usuario'  => strtoupper($request->cliente),
                'tipo_pago'=> $request->tipo_pago,
                'unidades' => $request->total_unidades,
                'total' => $request->total
            ]);
    
            foreach($request->pedidos as $pedido){
                $libro_id = $pedido['id'];
                $unidades = $pedido['unidades'];
                $pedido = Pedido::create([
                    'compra_id'     => $compra->id,
                    'libro_id'      => $libro_id, // libro_id
                    'costo_unitario'=> $pedido['costo_unitario'], // costo_unitario
                    'unidades'      => $unidades, // unidades
                    'total'         => $pedido['total'] // total
                ]);
    
                $libro = Libro::whereId($libro_id)->first();
                $libro->update(['piezas' => $libro->piezas - $unidades]);;
            }
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
        }

        return response()->json($compra);
    }

    public function detalles_compra(){
        $compra_id = Input::get('compra_id');
        $compra = Compra::whereId($compra_id)->with('pedidos.libro')->first();
        return response()->json($compra);
    }

    public function buscar_n_pedido(){
        $num_pedido = Input::get('num_pedido');
        $compra = Compra::where('pedido', $num_pedido)->first();
        return response()->json($compra);
    }

    public function buscar_usuario_p(){
        $usuario = Input::get('usuario');
        $compras = Compra::where('usuario','like','%'.$usuario.'%')->orderBy('id','desc')->get();
        return response()->json($compras);
    }

    public function buscar_fecha_p(){
        $inicio = Input::get('inicio');
        $final = Input::get('final');
        $usuario = Input::get('usuario');

        $fechas = $this->format_date($inicio, $final);
        $fecha1 = $fechas['inicio'];
        $fecha2 = $fechas['final'];

        if($usuario === null){
            $compras = Compra::whereBetween('created_at', [$fecha1, $fecha2])
                ->orderBy('id','desc')->get(); 
        } else {
            $compras = Compra::where('usuario','like','%'.$usuario.'%')
                ->whereBetween('created_at', [$fecha1, $fecha2])
                ->orderBy('id','desc')->get(); 
        }
        
        return response()->json($compras);
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

    public function download_compra($usuario, $inicio, $final, $tipo){
        return Excel::download(new ComprasExport($usuario, $inicio, $final, $tipo), 'reporte-pedidos.xlsx');
    }

    public function marcar_pedido(Request $request){
        $compra = Compra::whereId($request->id)->first();
        \DB::beginTransaction();
        try{
            $compra->update([
                'entregado_por' => $request->entregado_por,
                'entregado' => true
            ]);
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
        }
        return response()->json($compra);
    }

    public function download_pedido($id){
        $compra = Compra::whereId($id)->with('pedidos.libro')->first();
        $data['compra'] = $compra;
        $pdf = PDF::loadView('download.pdf.compras.pedido', $data); 
        return $pdf->download('pedido.pdf');
    }
}
