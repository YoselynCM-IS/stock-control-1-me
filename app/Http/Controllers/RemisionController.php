<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Input;
use App\Exports\RemisionesExport;
use App\Exports\RemisionExport;
use App\Exports\RemisionesGExport;
use App\Helper\CollectionHelper;
use App\Exports\GAccountExport;
use Illuminate\Http\Request;
use App\Remcliente;
use NumerosEnLetras;
use App\Devolucione;
use App\Remdeposito;
use App\Comentario;
use Carbon\Carbon;
use App\Remisione;
use App\Donacione;
use App\Deposito;
use App\Cctotale;
use App\Vendido;
use App\Cliente;
use App\Libro;
use App\Fecha;
use App\Corte;
use App\Dato;
use App\Pago;
use App\Code;
use Excel;
use PDF;
use DB;

class RemisionController extends Controller
{
    // MOSTRAR TODAS LAS REMISIONES
    public function index(){
        $remisiones = Remisione::with(['cliente:id,name'])
                    ->withCount('depositos')
                    ->orderBy('id','desc')->paginate(20);
        return response()->json($remisiones);
    }

    // --- BUSQUEDAS ---
    // BUSCAR REMISIÓN POR NUMERO
    // Función utilizada en ListadoComponent y RemisionesComponent
    public function por_numero(){
        $num_remision = Input::get('num_remision');
        $remision = Remisione::whereId($num_remision)->with('cliente')->first();
        return response()->json(['remision' => $remision]);
    }

    // MOSTRAR REMISIONES POR CLIENTE
    // Función utilizada en ListadoComponent y RemisionesComponent
    public function buscar_por_cliente(){
        $id = Input::get('id');
        $cliente = Cliente::find($id);

        // INICIO TOTALES
        $totales = $this->get_remcliente_totales($id);
        // FIN TOTALES

        $remisiones = Remisione::where('cliente_id', $cliente->id)
                        ->orderBy('id','desc')->with('cliente')
                        ->paginate(20);

        return response()->json(['remisiones' => $remisiones, 'totales' => $totales]);
    }

    public function get_remcliente_totales($id){
        $remcliente = Remcliente::where('cliente_id', $id)->first();
        return [
            'total' => $remcliente->total,
            'total_devolucion' => $remcliente->total_devolucion,
            'total_pagos' => $remcliente->total_pagos,
            'total_pagar' => $remcliente->total_pagar
        ];
    }

    public function f_totales($ids, $remdepositos, $depositos){
        $sum_totales = Remisione::whereIn('id', $ids)
                    ->select(
                        \DB::raw('SUM(total) as total'),
                        \DB::raw('SUM(total_devolucion) as total_devolucion')
                    )->get();
        $total = $sum_totales[0]['total'];
        $total_devolucion = $sum_totales[0]['total_devolucion'];
        $total_pagos = $remdepositos + $depositos;
        return [
            'total' => $total,
            'total_devolucion' => $total_devolucion,
            'total_pagos' => $total_pagos,
            'total_pagar' => $total - ($total_devolucion + $total_pagos)
        ];
    }

    // MOSTRAR REMISIONES POR ESTADO
    // Función utilizado en ListadoComponent
    public function buscar_por_estado(){
        $estado = Input::get('estado');
        $cliente_id = Input::get('cliente_id');

        if($estado === 'cancelado'){
            $remisiones = $this->pag_estado_SF(4, $cliente_id);
        }
        if($estado === 'no_entregado'){
            $remisiones = $this->pag_estado_SF(1, $cliente_id);
        }
        if($estado === 'entregado'){
            $remisiones = $this->pag_estado_SF(2, $cliente_id);
        }
        if($estado === 'pagado'){
            $remisiones = $this->pag_pagado_SF($cliente_id);
        }
        return response()->json($remisiones);
    }

    // FUNCIÓN PARA LA BUSQUEDA DE REMISIÓN POR ESTADO SIN FECHA PAGINADO
    public function pag_estado_SF($estado, $cliente_id){
        if ($estado == 1 || $estado == 4) {
            // REMISIONES INICIADAS Y CANCELADAS
            if($cliente_id === null){
                return Remisione::where('estado',$estado)->orderBy('id','desc')
                                ->with('cliente:id,name')->paginate(20);
            } else {
                return Remisione::where('estado',$estado)
                            ->where('cliente_id', $cliente_id)
                            ->orderBy('id','desc')
                            ->with('cliente:id,name')->paginate(20);
            } 
        }
        if ($estado == 2){
            // REMISIONES EN PROCESO
            if($cliente_id === null){
                return Remisione::where('total_pagar', '>', 0)
                    ->where('estado', 'Proceso')
                    ->orderBy('id','desc')
                    ->with('cliente:id,name')->paginate(20);
            } else {
                return Remisione::where('cliente_id', $cliente_id)
                    ->where('total_pagar', '>', 0)
                    ->where('estado', 'Proceso')
                    ->orderBy('id','desc')
                    ->with('cliente:id,name')->paginate(20);
            }
        }
    }

    // FUNCIÓN PARA LA BUSQUEDA DE REMISIÓN POR ESTADO (PAGADO) SIN FECHA PAGINADO
    public function pag_pagado_SF($cliente_id){
        if($cliente_id === null){
            return Remisione::where('total_pagar', '=', 0)
                        ->where(function ($query) {
                            $query->where('pagos', '>', 0)
                                    ->orWhere('total_devolucion', '>', 0);
                        })->orderBy('id','desc')
                        ->with('cliente:id,name')->paginate(20);
        } else {
            return Remisione::where('cliente_id', $cliente_id)
                        ->where('total_pagar', '=', 0)
                        ->where(function ($query) {
                            $query->where('pagos', '>', 0)
                                    ->orWhere('total_devolucion', '>', 0);
                        })->orderBy('id','desc')
                        ->with('cliente:id,name')->paginate(20);
        }
    }

    // FUNCIÓN PARA LA BUSQUEDA DE REMISIÓN POR ESTADO CON FECHA
    public function estadoS_CF($estado, $inicio, $final){
        if ($estado == 1 || $estado == 4) {
            // REMISIONES INICIADAS Y CANCELADAS
            return Remisione::where('estado',$estado)
                ->whereBetween('fecha_creacion', [$inicio, $final])
                ->orderBy('id','desc')
                ->with('cliente:id,name')->get();
        }
        if ($estado == 2){
            // REMISIONES EN PROCESO
            return Remisione::where('total_pagar', '>', 0)
                ->whereBetween('fecha_creacion', [$inicio, $final])
                ->orderBy('id','desc')
                ->with('cliente:id,name')->get();
        }
    }
    // FUNCIÓN PARA LA BUSQUEDA DE REMISIÓN POR ESTADO SIN FECHA
    public function estadoS_SF($estado, $cliente_id){
        if ($estado == 1 || $estado == 4) {
            // REMISIONES INICIADAS Y CANCELADAS
            if($cliente_id === null){
                return Remisione::where('estado',$estado)->orderBy('id','desc')
                                ->with('cliente:id,name')->get();
            } else {
                return Remisione::where('estado',$estado)
                            ->where('cliente_id', $cliente_id)
                            ->orderBy('id','desc')
                            ->with('cliente:id,name')->get();
            } 
        }
        if ($estado == 2){
            // REMISIONES EN PROCESO
            if($cliente_id === null){
                return Remisione::where('total_pagar', '>', 0)
                    ->where('estado', 'Proceso')
                    ->orderBy('id','desc')
                    ->with('cliente:id,name')->get();
            } else {
                return Remisione::where('cliente_id', $cliente_id)
                    ->where('total_pagar', '>', 0)
                    ->where('estado', 'Proceso')
                    ->orderBy('id','desc')
                    ->with('cliente:id,name')->get();
            }
        }
    }

    // FUNCIÓN PARA LA BUSQUEDA DE REMISIÓN POR ESTADO (PAGADO) CON FECHA
    public function pagado_CF($inicio, $final){
        return Remisione::where('total_pagar', '=', 0)
                        ->where(function ($query) {
                            $query->where('pagos', '>', 0)
                                    ->orWhere('total_devolucion', '>', 0);
                        })
                        ->whereBetween('fecha_creacion', [$inicio, $final])
                        ->withCount('depositos')
                        ->orderBy('cliente_id','asc')
                        ->with('cliente:id,name')->get();
    }

    // FUNCIÓN PARA LA BUSQUEDA DE REMISIÓN POR ESTADO (PAGADO) SIN FECHA
    public function pagado_SF($cliente_id){
        if($cliente_id === null){
            return Remisione::where('total_pagar', '=', 0)
                        ->where(function ($query) {
                            $query->where('pagos', '>', 0)
                                    ->orWhere('total_devolucion', '>', 0);
                        })
                        ->orderBy('id','desc')
                        ->with('cliente:id,name')->get();
        } else {
            return Remisione::where('cliente_id', $cliente_id)
                        ->where('total_pagar', '=', 0)
                        ->where(function ($query) {
                            $query->where('pagos', '>', 0)
                                    ->orWhere('total_devolucion', '>', 0);
                        })
                        ->orderBy('id','desc')
                        ->with('cliente:id,name')->get();
        }
    }

    public function f_ids($remisiones){
        $ids = [];
        $remisiones->map(function($remision) use(&$ids){
            $ids[] = $remision->id;
        });
        return $ids;
    }

    // MOSTRAR REMISIONES POR FECHAS
    // Función utilizada en ListadoComponent
    public function buscar_por_fecha(){
        $cliente_id = Input::get('cliente_id');
        $inicio = Input::get('inicio');
        $final = Input::get('final');
        if($cliente_id === null){
            $remisiones = Remisione::whereBetween('fecha_creacion', [$inicio, $final])
                ->orderBy('id','desc')
                ->with('cliente')->paginate(20); 
        } else{
            $cliente = Cliente::find($cliente_id);

            $remisiones = Remisione::where('cliente_id', $cliente->id)
                        ->whereBetween('fecha_creacion', [$inicio, $final])
                        ->withCount('depositos')->with('cliente')
                        ->orderBy('id','desc')->paginate(20);
        }
        return response()->json($remisiones);
    }

    public function get_totales_fecha(){
        $cliente_id = Input::get('cliente_id');
        $inicio = Input::get('inicio');
        $final = Input::get('final');
        if($cliente_id === null){
            // INICIO TOTALES
            $remisiones_ids = Remisione::whereBetween('fecha_creacion', [$inicio, $final])
                    ->whereNotIn('estado', ['Cancelado'])
                    ->select('id')->get();
            $ids = $this->f_ids($remisiones_ids);
            $depositos = Deposito::whereIn('remisione_id', $ids)->sum('pago');
            $clientes_ids = Remisione::whereIn('id', $ids)->select('cliente_id')->get();
            $rc_ids = [];
            $clientes_ids->map(function($ci) use(&$rc_ids){
                $cliente = Cliente::find($ci->cliente_id);
                $rc_ids[] = $cliente->remcliente->id;
            });
            $remdepositos = Remdeposito::whereIn('remcliente_id', $rc_ids)
                    ->whereBetween('created_at', [$inicio.' 00:00:00', $final.' 23:59:59'])
                    ->sum('pago');
            $totales = $this->f_totales($ids, $remdepositos, $depositos);
            // FIN TOTALES
        } else {
            // INICIO TOTALES
            $cliente = Cliente::find($cliente_id);
            $remisiones_ids = Remisione::where('cliente_id', $cliente->id)
                ->whereBetween('fecha_creacion', [$inicio, $final])
                ->whereNotIn('estado', ['Cancelado'])
                ->select('id')->get();
            $ids = $this->f_ids($remisiones_ids);
            $depositos = Deposito::whereIn('remisione_id', $ids)->sum('pago');
            $remdepositos = Remdeposito::where('remcliente_id', $cliente->remcliente->id)
                ->whereBetween('created_at', [$inicio.' 00:00:00', $final.' 23:59:59'])
                ->sum('pago');
            $totales = $this->f_totales($ids, $remdepositos, $depositos);
            // FIN TOTALES
        }
        return response()->json($totales);
    }

    // --- DESCARGAR ---
    // DESCARGAR REPORTE GENERAL Y DETALLADO
    public function imprimirEstado($estado, $cliente_id, $inicio, $final){
        if($estado === 'cancelado'){
            if($final != '0000-00-00'){ $remisiones = $this->estadoS_CF(4, $inicio, $final); }
            else { 
                if($cliente_id === 'null'){ $remisiones = $this->estadoS_SF(4, null); }
                else { $remisiones = $this->estadoS_SF(4, $cliente_id); } 
            }
        }
        if($estado === 'no_entregado'){
            if($final != '0000-00-00'){ $remisiones = $this->estadoS_CF(1, $inicio, $final); }
            else { 
                if($cliente_id === 'null'){ $remisiones = $this->estadoS_SF(1, null); }
                else { $remisiones = $this->estadoS_SF(1, $cliente_id); }
            }
        }
        if($estado === 'entregado'){
            if($final != '0000-00-00'){ $remisiones = $this->estadoS_CF(2, $inicio, $final); }
            else {
                if($cliente_id === 'null'){ $remisiones = $this->estadoS_SF(2, null); }
                else { $remisiones = $this->estadoS_SF(2, $cliente_id); }
            }
        }
        if($estado === 'pagado'){
            if($final != '0000-00-00'){ $remisiones = $this->pagado_CF($inicio, $final); }
            else {
                if($cliente_id === 'null'){ $remisiones = $this->pagado_SF(null);  }
                else { $remisiones = $this->pagado_SF($cliente_id);  }
                
            }
        }

        $valores = $this->totales($remisiones);
        $data['remisiones'] = $remisiones;
        $data['estado'] = $estado;
        $data['inicio'] = $inicio;
        $data['final'] = $final;
        $data['fecha'] = $valores['fecha'];
        $data['totales'] = $valores['totales'];
        $pdf = PDF::loadView('download.pdf.remisiones.reporte-estado-gral', $data);
        return $pdf->download('reporte-estado-gral.pdf');
    }

    // DESCARGAR PDF DE REMISIONES POR FECHA
    // Función utilizada en ListadoComponent
    public function imprimirFecha($inicio, $final){
        $remisiones = Remisione::whereBetween('fecha_creacion', [$inicio, $final])
                ->whereNotIn('estado', ['Iniciado', 'Cancelado'])
                ->orderBy('cliente_id','asc')
                ->with('cliente')->get();

        $valores = $this->totales($remisiones);
        $data['fecha'] = $valores['fecha'];
        $data['inicio'] = $inicio;
        $data['final'] = $final;
        $data['remisiones'] = $remisiones;
        $data['totales'] = $valores['totales'];
        $pdf = PDF::loadView('download.pdf.remisiones.reporte-fecha-gral', $data);
        return $pdf->download('reporte-fecha-gral.pdf');
    }

    
    // IMPRIMIR PDF CON LOS DATOS DE LAS REMISIONES POR CLIENTE
    // Función utilizada en ListadoComponent
    public function imprimirCliente($cliente_id, $inicio, $final){
        if($inicio != '0000-00-00' && $final != '0000-00-00'){
            $remisiones = Remisione::where('cliente_id', $cliente_id)
                            ->whereBetween('fecha_creacion', [$inicio, $final])
                            ->whereNotIn('estado', ['Iniciado', 'Cancelado'])
                            ->orderBy('id','desc')
                            ->get();
        }
        else {
            $remisiones = Remisione::where('cliente_id', $cliente_id)
                    ->whereNotIn('estado', ['Iniciado', 'Cancelado'])
                    ->orderBy('id','desc')
                    ->get();
        }
        $valores = $this->totales($remisiones);
        $data['fecha'] = $valores['fecha'];
        $data['inicio'] = $inicio;
        $data['final'] = $final;
        $data['remisiones'] = $remisiones;
        $data['totales'] = $valores['totales'];
        $pdf = PDF::loadView('download.pdf.remisiones.reporte-cliente-gral', $data);
        return $pdf->download('reporte-cliente-gral.pdf');
    }

    public function down_gral_excel($cliente_id, $inicio, $final, $estado){
        return Excel::download(new RemisionesGExport($cliente_id, $inicio, $final, $estado), 'reporte-remisiones.xlsx');
    }

    public function down_remisiones_excel($cliente_id, $inicio, $final, $estado){
        return Excel::download(new RemisionesExport($cliente_id, $inicio, $final, $estado), 'reporte-detallado.xlsx');
    }
    
    public function down_remisiones_pdf($cliente_id, $inicio, $final, $estado){
        if($cliente_id === 'null' && $inicio === '0000-00-00' && $final === '0000-00-00' && $estado === 'null'){
            $remisiones = Remisione::with(['cliente:id,name'])->with('datos.libro')
                    ->orderBy('id','desc')
                    ->get();
        }
        if($cliente_id !== 'null' && $inicio === '0000-00-00' && $final === '0000-00-00' && $estado === 'null'){
            $remisiones = Remisione::where('cliente_id', $cliente_id)
                    ->orderBy('id','desc')
                    ->get();
        }
        if($cliente_id !== 'null' && $inicio != '0000-00-00' && $final != '0000-00-00' && $estado === 'null'){
            $remisiones = Remisione::where('cliente_id', $cliente_id)
                            ->whereBetween('fecha_creacion', [$inicio, $final])
                            ->orderBy('id','desc')
                            ->get();
        }
        if($cliente_id === 'null' && $inicio != '0000-00-00' && $final != '0000-00-00' && $estado === 'null'){
            $remisiones = Remisione::whereBetween('fecha_creacion', [$inicio, $final])
                            ->orderBy('cliente_id','asc')
                            ->with('cliente')->get();
        }
        if($estado !== 'null'){
            if($estado === 'cancelado'){
                if($cliente_id === 'null'){ $remisiones = $this->estadoS_SF(4, null); }
                else { $remisiones = $this->estadoS_SF(4, $cliente_id); } 
            }
            if($estado === 'no_entregado'){
                if($cliente_id === 'null'){ $remisiones = $this->estadoS_SF(1, null); }
                else { $remisiones = $this->estadoS_SF(1, $cliente_id); }
            }
            if($estado === 'entregado'){
                if($cliente_id === 'null'){ $remisiones = $this->estadoS_SF(2, null); }
                else { $remisiones = $this->estadoS_SF(2, $cliente_id); }
            }
            if($estado === 'pagado'){
                if($cliente_id === 'null'){ $remisiones = $this->pagado_SF(null);  }
                else { $remisiones = $this->pagado_SF($cliente_id);  }
            }
        }

        $valores = $this->totales($remisiones);
        $data['fecha'] = $valores['fecha'];
        $data['inicio'] = $inicio;
        $data['final'] = $final;
        $data['estado'] = $estado;
        $data['remisiones'] = $remisiones;
        $data['totales'] = $valores['totales'];
        $pdf = PDF::loadView('download.pdf.remisiones.reporte-remisiones-gral', $data);
        return $pdf->download('reporte-remisiones.pdf');
    }

    // OBTENER TOTALES DE LAS REMISIONES
    public function totales($remisiones){
        $total_salida = 0;
        $total_pagos = 0;
        $total_devolucion = 0;
        $total_donacion = 0;
        $total_pagar = 0;

        foreach($remisiones as $r){
            if($r->estado === 'Proceso' || $r->estado === 'Terminado'){
                $total_salida += $r->total;
                $total_pagos += $r->pagos;
                $total_devolucion += $r->total_devolucion;
                $total_donacion += $r->total_donacion;
                $total_pagar += $r->total_pagar;  
            }          
        }
        $datos = [
            'fecha' => $fecha = Carbon::now(),
            'totales' => [
                'total_salida' => $total_salida,
                'total_pagos' => $total_pagos,
                'total_devolucion' => $total_devolucion,
                'total_donacion' => $total_donacion,
                'total_pagar' => $total_pagar
            ]
        ];
        return $datos;
    }
    
    // MOSTRAR DETALLES DE REMISIÓN
    // Función utilizada en ListadoComponent, DevoluciónComponent, RemisionesComponent
    public function show(){
        $numero = Input::get('numero');
        $remision = Remisione::whereId($numero)
                    ->with([
                        'datos.libro',
                        'devoluciones.libro',
                        'devoluciones.dato',
                        'fechas.libro',
                        'depositos',
                        'comentarios.user'
                    ])->withCount('depositos')->first(); 
        // $vendidos = Vendido::where('remisione_id', $remision->id)->with('libro', 'dato', 'pagos')->get();
        $vendidos = null;
        return response()->json(['remision' => $remision, 'vendidos' => $vendidos]);
    }

    // CANCELAR REMISIÓN
    // Función utilizada en ListadoComponent
    public function cancel(Request $request){
        $remision = Remisione::whereId($request->id)->first();
        \DB::beginTransaction();
        try{ 
            $prueba = [];
            $remision->datos->map(function($dato) use(&$prueba){
                // REGRESAR EL NUMERO DE PIEZAS TOMADAS
                \DB::table('libros')->whereId($dato->libro_id)
                                    ->increment('piezas',  $dato->unidades);

                // BORRAR CODIGOS
                $dato->codes->map(function($code){
                    $code->update(['estado' => 'inventario']);
                });
                $dato->codes()->detach();
            });

            // BORRAR LOS REGISTROS DE DEVOLUCION
            Devolucione::where('remisione_id', $remision->id)->delete();
            
            // ACTUALIZA LA CUENTA DEL CORTE CORRESPONDIENTE
            $cctotale = $this->get_cctotale($remision);
            $cctotale->update([
                'total' => $cctotale->total - $remision->total,
                'total_pagar' => $cctotale->total_pagar - $remision->total
            ]);

            // ACTUALIZAR LA CUENTA DEL CLIENTE
            $remcliente = Remcliente::where('cliente_id', $remision->cliente_id)->first();
            $remcliente->update([
                'total' => $remcliente->total - $remision->total,
                'total_pagar' => $remcliente->total_pagar - $remision->total
            ]);

            $remision->update(['estado' => 'Cancelado']);
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
		}
        
        return response()->json($remision);
    }

    public function get_cctotale($remision){
        return Cctotale::where([
            'cliente_id' => $remision->cliente_id,
            'corte_id'  => $remision->corte_id
        ])->first();
    }

    // GUARDAR REMISION
    // Función utilizada en RemisionComponent
    public function store(Request $request){
        \DB::beginTransaction();
        try {
            $hoy = Carbon::now();
            $fecha_hoy = $hoy->format('Y-m-d');

            $month = $hoy->format('m');
            // CORTE A: 07 - 11 / CORTE B: 12 - 06 
            $tipo = 'B';
            if($month >= 7 && $month <= 11) $tipo = 'A';

            $corte = Corte::whereTipo($tipo)->get()->last();
            
            $total = (double) $request->total;
            // CREAR REMISIÓN
            $remision = Remisione::create([
                'user_id' => auth()->user()->id,
                'corte_id' => $corte->id,
                'cliente_id' => $request->cliente['id'],
                'total' => $total,
                'total_pagar' => $total,
                'fecha_entrega' => $request->fecha_entrega,
                'estado' => 'Proceso',
                'fecha_creacion' => $fecha_hoy,
                'fecha_devolucion' => $fecha_hoy
            ]);
            
            $this->save_datos($request->datos, $remision);
            
            // ACTUALIZA LA CUENTA DEL CORTE CORRESPONDIENTE
            $cctotale = $this->get_cctotale($remision);
            $cctotale->update([
                'total' => $cctotale->total + $remision->total,
                'total_pagar' => $cctotale->total_pagar + $remision->total
            ]);

            // BUSCAR EL CLIENTE Y AFECTAR SU CUENTA GENERAL
            $remcliente = Remcliente::where('cliente_id', $remision->cliente_id)->first();
            if($remcliente === null){
                Remcliente::create([
                    'cliente_id' => $remision->cliente_id,
                    'total' => $remision->total,
                    'total_pagar' => $remision->total
                ]);
            } else {
                $remcliente->update([
                    'total' => $remcliente->total + $remision->total,
                    'total_pagar' => $remcliente->total_pagar + $remision->total
                ]);
            }
            
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json();
    }

    public function save_datos($req_datos, $remision){
        $lista_datos = [];
        $request_datos = collect($req_datos);
        $hoy = Carbon::now();
        $request_datos->map(function($dato) use (&$lista_datos, $remision, $hoy){
            $lista_datos[] = [
                'remisione_id' => $remision->id,
                'libro_id'  => $dato['libro']['id'],
                'costo_unitario' => (float) $dato['costo_unitario'],
                'unidades'  => (int) $dato['unidades'],
                'total'     => (double) $dato['total'],
                'created_at' => $hoy,
                'updated_at' => $hoy
            ];
        });
        
        // CREAR REGISTROS DE DATOS
        Dato::insert($lista_datos);

        $lista_devoluciones = [];
        $lista_codes = collect();
        $datos = Dato::where('remisione_id', $remision->id)->get();
        $datos->map(function($dato) use(&$lista_devoluciones, &$lista_codes, $hoy){
            $libro_id = $dato->libro_id;
            $lista_devoluciones[] = [
                'remisione_id' => $dato->remisione_id,
                'dato_id'   => $dato->id,
                'libro_id' => $libro_id,
                'unidades_resta' => $dato->unidades,
                'total_resta' => $dato->total,
                'created_at' => $hoy,
                'updated_at' => $hoy
            ];

            if($dato->libro->type == 'digital'){
                $lista_codes->push([
                    'dato_id'   => $dato->id,
                    'libro_id'  => $dato->libro_id,
                    'unidades'  => $dato->unidades
                ]);
            }
            
            // DISMINUIR PIEZAS DE LOS LIBROS
            \DB::table('libros')->whereId($libro_id)
                                ->decrement('piezas',  $dato->unidades);
        });

        // CREAR REGISTROS DE DEVOLUCION
        Devolucione::insert($lista_devoluciones);

        $hoy = Carbon::now();
        $lista_codes->map(function($lc, $hoy){
            $codes = Code::where('libro_id', $lc['libro_id'])
                            ->where('estado', 'inventario')
                            ->orderBy('created_at', 'asc')
                            ->limit($lc['unidades'])
                            ->get();
            
            $code_dato = [];
            $codes->map(function($code) use (&$code_dato){
                $code_dato[] = $code->id;
                $code->update(['estado' => 'ocupado']);
            });

            $dato = Dato::find($lc['dato_id']);
            $dato->codes()->sync($code_dato);
        });
    }

    // MARCAR COMO ENTREGADA UNA REMISIÓN
    // Función utilizada en RemisionesComponent
    public function registrar_vendidos(Request $request){
        $remision = Remisione::whereId($request->remision)->with('datos.libro')->first();
        try {
            if(Vendido::where('remisione_id', $remision->id)->count() === 0){  
                \DB::beginTransaction();
                $remision->update([
                    'estado' => 'Proceso',
                    'responsable' => $request->responsable
                ]);
                $total_pagar = $remision->total;
                $remision->update(['total_pagar' => $total_pagar]);
                foreach($remision->datos as $dato){
                    $vendido = Vendido::create([
                        'remisione_id' => $dato->remisione_id,
                        'dato_id'   => $dato->id,
                        'libro_id' => $dato->libro_id, 
                        'unidades_resta' => $dato->unidades,
                        'total_resta' => $dato->total,
                    ]);
                    $devolucion = Devolucione::create([
                        'remisione_id' => $dato->remisione_id,
                        'dato_id'   => $dato->id,
                        'libro_id' => $dato->libro_id,
                        'unidades_resta' => $dato->unidades,
                        'total_resta' => $dato->total,
                        'fecha_devolucion' => Carbon::now()->format('Y-m-d')
                    ]);
                }
                $remcliente = Remcliente::where('cliente_id', $remision->cliente_id)->first();
                if($remcliente === null){
                    Remcliente::create([
                        'cliente_id' => $remision->cliente_id,
                        'total' => $total_pagar,
                        'total_pagar' => $total_pagar
                    ]);
                } else {
                    $remcliente->update([
                        'total' => $remcliente->total + $total_pagar,
                        'total_pagar' => $remcliente->total_pagar + $total_pagar
                    ]);
                }
                
                \DB::commit();
            }
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json(['remision' => $remision]);
    }
    // OBTENER TODAS LAS REMISIONES DE LOS CLIENTES
    // Función utilizada en ListadoComponent
    public function todos(){
        $remisiones = Remisione::with('cliente:id,name')->orderBy('id','desc')->get();
        return response()->json($remisiones);
    } 

    // DESCARGAR REMISIÓN
    public function imprimirSalida($id){ 
        $remision = Remisione::whereId($id)->with('datos.libro')->first();
        $data['remision'] = $remision;
        $data['fecha'] = Carbon::now();
        $data['total_salida'] = NumerosEnLetras::convertir($remision->total);
        $pdf = PDF::loadView('download.pdf.remisiones.nota', $data); 
        
        return $pdf->download('remision-'.$id.'.pdf');
    }

    // GUARDAR COMENTARIO DE LA REMISIÓN
    public function guardar_comentario(Request $request){
        try {
            \DB::beginTransaction();
            $comentario = Comentario::create([
                'remisione_id' => $request->remision_id, 
                'user_id' => auth()->user()->id,
                'comentario' => $request->comentario
            ]);
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        $data = Comentario::whereId($comentario->id)->with('user')->first();
        return response()->json($data);
    }

    public function download_remision($id){
        return Excel::download(new RemisionExport($id), 'remision-'.$id.'.xlsx');
    }

    // Asignar responsable de entregar la remisión
    public function assign_responsable(Request $request){
        $remision = Remisione::whereId($request->remision_id)->first();
        \DB::beginTransaction();
        try {
            $remision->update(['responsable' => $request->responsable]);
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json($remision);
    }

    public function descargar_gralClientes(){
        return Excel::download(new GAccountExport, 'lista-clientes.xlsx');
    }

    // OBTENER REMISIÓN POR ID
    public function get_remision_id(){
        $id = Input::get('id');
        $remision = Remisione::whereId($id)->with(['cliente', 'datos.libro'])->first();
        $clientes = Cliente::orderBy('name', 'asc')->get();
        return response()->json(['remision' => $remision, 'clientes' => $clientes]);
    }

    public function update(Request $request){
        DB::beginTransaction();
        try {
            $remision = Remisione::find($request->id);
            // OBTENER EL TOTAL Y CASTEAR
            $total = (double) $request->total;
            $cliente_id = $request->cliente['id'];
            
            // ELIMINADOS
            $eliminados = collect($request->eliminados);
            $eliminados->map(function($eliminado){
                $dato_id = $eliminado['id'];
                $unidades = (int) $eliminado['unidades'];
    
                // AUMENTAR PIEZAS DE LOS LIBROS ELIMINADOS
                \DB::table('libros')->whereId($eliminado['libro_id'])
                                    ->increment('piezas',  $unidades);
                // ELIMINAR DATOS Y DEVOLUCIONES
                Devolucione::where('dato_id', $dato_id)->delete();
                Dato::whereId($dato_id)->delete();
            });
            
            // NUEVOS
            $lista_devoluciones = [];
            $nuevos = collect($request->nuevos);
            $hoy = Carbon::now();
            $nuevos->map(function($nuevo) use (&$lista_devoluciones, $remision, $hoy){
                $libro_id = $nuevo['libro']['id'];
                $unidades = (int) $nuevo['unidades'];
                $total_nuevo = (double) $nuevo['total'];

                // CREAR REGISTROS DE DATOS
                $dato = Dato::create([
                    'remisione_id' => $remision->id,
                    'libro_id'  => $libro_id,
                    'costo_unitario' => (float) $nuevo['costo_unitario'],
                    'unidades'  => $unidades,
                    'total'     => $total_nuevo
                ]);

                $lista_devoluciones[] = [
                    'remisione_id' => $remision->id,
                    'dato_id'   => $dato->id,
                    'libro_id' => $libro_id,
                    'unidades_resta' => $unidades,
                    'total_resta' => $total_nuevo,
                    'created_at' => $hoy,
                    'updated_at' => $hoy
                ];

                // DISMINUIR PIEZAS DE LOS LIBROS
                \DB::table('libros')->whereId($libro_id)->decrement('piezas',  $unidades);
            });

            // CREAR REGISTROS DE DEVOLUCION
            Devolucione::insert($lista_devoluciones);

            if($cliente_id === $remision->cliente_id && $total !== $remision->total){
                // ACTUALIZA LA CUENTA DEL CORTE CORRESPONDIENTE
                $cctotale = $this->get_cctotale($remision);
                $cct_total = ($cctotale->total - $remision->total) + $total;
                $cct_tpagar = ($cctotale->total_pagar - $remision->total) + $total;
                $cctotale->update([
                    'total' => $cct_total,
                    'total_pagar' => $cct_tpagar
                ]);

                // BUSCAR EL CLIENTE Y AFECTAR SU CUENTA GENERAL
                $remcliente = Remcliente::where('cliente_id', $remision->cliente_id)->first(); 
                $total_gral = ($remcliente->total - $remision->total) + $total;
                $total_pagar = ($remcliente->total_pagar - $remision->total) + $total;
                $remcliente->update([
                    'total' => $total_gral,
                    'total_pagar' => $total_pagar
                ]);
            }
            if($cliente_id !== $remision->cliente_id){
                //*** BUSCAR EL CLIENTE Y AFECTAR SU CUENTA (GENERAL Y CORTE) */
                // 1ro - CUENTA DEL CORTE, AFECTAR SU CUENTA, QUITANDO EL TOTAL DE LA REMISIÓN
                $p_cctotale = $this->get_cctotale($remision);
                $p_cctotale->update([
                    'total' => $p_cctotale->total - $remision->total,
                    'total_pagar' => $p_cctotale->total_pagar - $remision->total
                ]);

                // Quitarle lo que se le habia asignado de la remisión, pero como se cambio de cliente tiene que quitarse
                $primero = Remcliente::where('cliente_id', $remision->cliente_id)->first(); 
                $primero->update([
                    'total' => $primero->total - $remision->total,
                    'total_pagar' => $primero->total_pagar - $remision->total
                ]);
                //*************** */

                // *** AGREGARLE AL OTRO CLIENTE EL TOTAL */
                // Agregarle lo de la remisión editada, porque se cambio de cliente y ahora a este se le tiene que sumar
                $segundo = Remcliente::where('cliente_id', $cliente_id)->first(); 
                if($segundo === null){
                    Remcliente::create([
                        'cliente_id' => $cliente_id,
                        'total' => $total,
                        'total_pagar' => $total
                    ]);
                } else {
                    $segundo->update([
                        'total' => $segundo->total + $total,
                        'total_pagar' => $segundo->total_pagar + $total
                    ]);
                }
                // 2do CUENTA DEL CORTE
                $s_cctotale = Cctotale::where([
                    'cliente_id' => $cliente_id,
                    'corte_id'  => $remision->corte_id
                ])->first();
                $s_cctotale->update([
                    'total' => $s_cctotale->total + $total,
                    'total_pagar' => $s_cctotale->total_pagar + $total
                ]);
                //*************** */
            }
            if($cliente_id !== $remision->cliente_id || $request->fecha_entrega !== $remision->fecha_entrega || $total !== $remision->total){
                // ACTUALIZAR REMISIÓN
                $remision->update([
                    'cliente_id' => $cliente_id,
                    'total' => $total,
                    'total_pagar' => $total,
                    'fecha_entrega' => $request->fecha_entrega
                ]);
            } 

            $get_remision = Remisione::whereId($remision->id)
                            ->with(['cliente:id,name'])
                            ->first();
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json($get_remision);
    }

    public function get_remcliente(){
        $cliente_id = Input::get('cliente_id');
        $remcliente = Remcliente::where('cliente_id', $cliente_id)->first(); 
        return response()->json($remcliente);
    }

    // OBTENER TODAS LAS REMISIONES EN PROCESO O TERMINADAS
    public function pay_remisiones(){
        $remisiones = Remisione::where('total_pagar', '>', '0')
            ->where(function ($query) {
                $query->where('estado', 'Proceso')
                        ->orWhere('estado', 'Terminado');
            })->orderBy('id','desc')
            ->with('cliente')->paginate(20);
        return response()->json($remisiones);
    }

    // NO UTLIZADOS
    public function valores($remisiones, $inicio, $final, $cliente){
        $total_salida = 0;
        $total_devolucion = 0;
        $total_pagos = 0;
        $total_pagar = 0;

        foreach($remisiones as $r){
            if($r->estado != 'Iniciado' && $r->estado != 'Cancelado'){
                $total_salida += $r->total;
                $total_devolucion += $r->total_devolucion;
                $total_pagos += $r->pagos;
                $total_pagar += $r->total_pagar;
            }            
        }

        $data['remisiones'] = $remisiones;
        $data['total_salida'] = $total_salida;
        $data['total_devolucion'] = $total_devolucion;
        $data['total_pagos'] = $total_pagos;
        $data['total_pagar'] = $total_pagar;
        $data['fecha_inicio'] = $inicio;
        $data['fecha_final'] = $final;
        $data['cliente'] = $cliente;

        return $data;
    }

    // CERRAR REMISION
    public function close(Request $request){
        $remision = Remisione::find($request->id);
        \DB::beginTransaction();
        try{ 
            $pagos = $remision->pagos + $remision->total_pagar;
            $remision->update([
                'pagos' => $pagos,
                'total_pagar'   => 0,
                'estado' => 'Terminado',
                'cerrado_por' => auth()->user()->name
            ]);
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
		}
        
        return response()->json($remision);
    }

    // OBTENER REMISIONES PENDIENTES
    public function obtener_pendientes(){
        $clientes = \DB::table('remisiones')
                        ->join('clientes', 'remisiones.cliente_id', '=', 'clientes.id')
                        ->select('clientes.id', 'clientes.name')
                        ->where('remisiones.estado', 'Proceso')
                        ->orderBy('clientes.name', 'asc')
                        ->groupBy('clientes.id', 'clientes.name')
                        ->get();
        $ref_clientes = [];
        $clientes->map(function($cliente) use(&$ref_clientes){
            $ref_clientes[] = $this->remisiones_proccess($cliente);
        });
        $remisiones = collect($ref_clientes);
        return response()->json($remisiones);
    }

    public function remisiones_proccess($cliente){
        $rs = Remisione::where('estado', 'Proceso')
                        ->where('cliente_id', $cliente->id)
                        ->orderBy('created_at','asc')->get();
        $remcliente = Remcliente::where('cliente_id', $cliente->id)
                                ->first();
        $ds = $this->set_table_cliente_adeudos($rs, $remcliente);
        $remisiones = collect($ds['datos']);
        $datos = [ 
            'cliente_id' => $cliente->id,
            'cliente_name' => $cliente->name,
            'remisiones' => $remisiones,
            'total_pagar' => $remcliente->total_pagar,
            'all_total_pagar' => $ds['total_pagar']
        ];
        
        return $datos;
    }

    // OBTENER PENDIENTES POR CLIENTE
    public function by_cliente_pendientes(){
        $cliente = Cliente::find(Input::get('cliente_id'));
        $datos = $this->remisiones_proccess($cliente);
        return response()->json($datos);
    }

    // OBTENER CONSULTA DE CLIENTES DE ADEUDOS
    public function set_table_cliente_adeudos($remisiones, $remcliente){
        $datos = [];
        $hoy = Carbon::now();
        $total_pagar  = 0;
        $remisiones->map(function($remision) use(&$datos, &$total_pagar, $hoy, $remcliente){
            $diferencia = $remision->created_at->diffInDays($hoy);

            $situacion = '';
            if($remision->total_pagar > $remcliente->total_pagar)
                $situacion = 'Se recomienda cerrar remisión, pero antes revisar libros.';
            
            $data = [
                'id'            => $remision->id,
                'fecha_creacion' => $remision->fecha_creacion,
                // 'cliente'       => $remision->cliente->name,
                // 'total'         => $remision->total,
                // 'pagos'         => $remision->pagos,
                // 'total_devolucion' => $remision->total_devolucion,
                'total_pagar'   => $remision->total_pagar,
                'diferencia'    => $diferencia,
                'situacion'     => $situacion
            ];

            $datos[] = $data;
            $total_pagar += $remision->total_pagar;
        });
        return [
            'datos' => $datos,
            'total_pagar' => $total_pagar
        ];
    }

    // CREAR REMISION
    public function ce_remision($remisione_id, $editar){
        $remision = 0;
        if($editar == 'true') 
            $remision = Remisione::whereId($remisione_id)->with('cliente', 'datos.libro')->first();
        
        $clientes = Cliente::orderBy('name', 'asc')->get();
        return view('information.remisiones.ce-remision', compact('remision', 'clientes', 'editar'));
    }

    // OBTENER DETALLES DE LA REMISION
    public function get_details($id){
        $remision = Remisione::whereId($id)
                    ->with([
                        'cliente',
                        'datos.libro',
                        'datos.codes',
                        'devoluciones.libro',
                        'devoluciones.dato',
                        'fechas.libro',
                        'depositos',
                        'comentarios.user'
                    ])->withCount('depositos')->first(); 
        return view('information.remisiones.details-remision', compact('remision'));
    }

    public function get_responsables(){
        $responsables = \DB::table('responsables')->orderBy('responsable', 'asc')->get();
        return response()->json($responsables);
    }
    
}
