<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use App\Devolucione;
use App\Remcliente;
use Carbon\Carbon;
use App\Remisione;
use App\Cctotale;
use App\Vendido;
use App\Cliente;
use App\Libro;
use App\Fecha;
use App\Dato;

class DevolucioneController extends Controller
{
    // GUARDAR DEVOLUCIÓN DE REMISIÓN
    // Función utilizada en DevolucionController
    public function update(Request $request){
        try {
            \DB::beginTransaction();
            // Buscar remisión
            $remision = Remisione::whereId($request->id)->first();
            $remcliente = Remcliente::where('cliente_id', $remision->cliente_id)->first();

            $entregado_por = $request->entregado_por;
            $total_devolucion = 0;
            
            // DEVOLUCIONES
            $prueba = [];

            $lista_fechas = [];
            $devoluciones = collect($request->devoluciones);
            $hoy = Carbon::now();
            $devoluciones->map(function($devolucion) use(&$prueba, &$lista_fechas, $remision, $entregado_por, &$total_devolucion, $hoy){
                $unidades_base = $devolucion['unidades_base'];
                $total_base = $devolucion['total_base'];

                if($unidades_base != 0){
                    // Buscar devolución
                    $d = Devolucione::find($devolucion['id']);
                    $lista_fechas[] = [
                        'remisione_id' => $remision->id,
                        'fecha_devolucion' => $hoy->format('Y-m-d'),
                        'libro_id' => $d->libro->id,
                        'unidades' => $unidades_base,
                        'total' => $total_base,
                        'entregado_por' => $entregado_por,
                        'creado_por' => auth()->user()->name,
                        'created_at' => $hoy,
                        'updated_at' => $hoy
                    ];
                    
                    $unidades = $d->unidades + $unidades_base;
                    $total = $d->total + $total_base;
                    $unidades_resta = $d->unidades_resta - $unidades_base;
                    $total_resta = $d->total_resta - $total_base;
                    // Actualizar la tabla de devolución
                    $d->update([
                        'unidades' => $unidades, 
                        'unidades_resta' => $unidades_resta,
                        'total' => $total,
                        'total_resta' => $total_resta
                    ]);

                    // AUMENTAR PIEZAS DE LOS LIBROS DEVUELTOS
                    \DB::table('libros')->whereId($d->libro->id)
                        ->increment('piezas', $unidades_base);  
                    
                    // DEVOLUCION DE CODIGOS
                    $codes = $d->dato->codes()->where('devolucion', 0)->limit($unidades_base)->get();
                    $codes->map(function($code){
                        $code->update(['estado' => 'inventario']);
                        $code->datos()
                            ->updateExistingPivot($code->pivot->dato_id, [
                                'devolucion' => true
                            ]);
                    });
                } 

                $total_devolucion += $total_base;
            });

            // Crear registros de fecha de la devolución
            Fecha::insert($lista_fechas);
            
            $total_pagar = $remision->total_pagar - $total_devolucion;
            $t_devolucion = $remision->total_devolucion + $total_devolucion;
            
            // ACTUALIZAR REMISION
            $remision->update([
                'total_devolucion' => $t_devolucion,
                'total_pagar'   => $total_pagar
            ]);
            if ((int) $total_pagar === 0) {
                if ($remision->depositos->count() > 0)
                    $this->restantes_to_cero($remision);
                $remision->update(['estado' => 'Terminado']); 
            }

            // ACTUALIZA LA CUENTA DEL CORTE CORRESPONDIENTE
            $cctotale = Cctotale::where([
                'cliente_id' => $remision->cliente_id,
                'corte_id'  => $remision->corte_id
            ])->first();
            $cctotale->update([
                'total_devolucion' => $cctotale->total_devolucion + $total_devolucion,
                'total_pagar' => $cctotale->total_pagar - $total_devolucion
            ]);
            
            // ACTUALIZAR CUENTA GENERAL DEL CLIENTE
            $remcliente->update([
                'total_devolucion' => $remcliente->total_devolucion + $total_devolucion,
                'total_pagar' => $remcliente->total_pagar - $total_devolucion
            ]);

            \DB::commit();

        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json($remision);
    } 

    // ACTUALIZAR LAS UNIDADES RESTANTES DE LAS REMISIONES
    // SOLO SI EN LA REMISIÓN SE REALIZO UN DEPOSITO
    public function restantes_to_cero($remision) {
        Devolucione::where('remisione_id', $remision->id)->update([
            'unidades_resta' => 0,
            'total_resta' => 0
        ]);
    }
    
    //Mostrar todas las devoluciones (ELIMINADA)
    public function all_devoluciones(){
        $remisiones = Remisione::where('total_pagar', '>', '0')
                                ->where(function ($query) {
                                    $query->where('estado', 'Proceso')
                                            ->orWhere('estado', 'Terminado');
                                })->orderBy('id','desc')
                                ->with('cliente')->get(); 
        return response()->json($remisiones);
    }
}
