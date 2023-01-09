<?php

namespace App\Http\Controllers;

use App\Exports\actividades\ActividadExport;
use Illuminate\Http\Request;
use App\Actividade;
use Carbon\Carbon;
use App\Cliente;
use App\Reporte;
use Excel;

class ActividadeController extends Controller
{
    // OBTENER LA LISTA DE ACTIVIDADES
    // public function index(){
    //     return view('information.actividades.lista');
    // }

    // OBTENER ACTIVIDADES POR TIPO DE CLIENTE
    public function lista(){
        return view('information.actividades.lista');
    }

    // OBTENER LAS ACTIVIDADES POR STATUS
    public function get_status($status){
        return view('information.actividades.status-lista', compact('status'));
    }

    // GUARDAR ACTIVIDAD
    public function store(Request $request){
        \DB::beginTransaction();
        try {
            $tipo = $request->tipo;
            $descripcion = $request->descripcion;

            $fecha = new Carbon($request->fecha.' '.$request->hora);
            $estado = $this->set_tiempo_estado($fecha);
            
            $actividad = Actividade::create([
                'user_id' => auth()->user()->id,
                'nombre' => strtoupper($request->nombre),
                'tipo' => $tipo, 
                'descripcion' => $descripcion, 
                'estado' => $estado, 
                'fecha' => $fecha,
                'lugar' => $request->lugar
            ]);

            $clientes = collect($request->clientes);
            $clientes->map(function($cliente) use(&$actividad){
                $actividad->clientes()->attach($cliente['id']);
            });

            $reporte = 'creo la actividad '.$actividad->tipo.': '.$actividad->nombre.' / '.$actividad->descripcion;
            $this->create_report($actividad->id, $reporte, 'actividades');

            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }

        return response()->json($actividad);
    }

    // MARCAR ACTIVIDADES COMO TEMRINADAS
    public function update_estado(Request $request){
        \DB::beginTransaction();
        try {
            $hoy = Carbon::now();
            $actividad = Actividade::find($request->id);
            $observaciones = $actividad->observaciones.'<p><b>ACTIVIDAD COMPLETADA - '.$hoy.' :</b> '.$request->observaciones.'</p>';
            
            $actividad->update([
                'estado' => $request->estado,
                'exitosa' => $request->exitosa, 
                'observaciones' => $observaciones
            ]);

            $reporte = 'marco como '.$actividad->estado.' la actividad '.$actividad->tipo.': '.$actividad->nombre.' / '.$actividad->descripcion;
            $this->create_report($actividad->id, $reporte, 'actividades');
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json($actividad);
    }

    public function create_report($id_table, $reporte, $name_table){
        Reporte::create([
            'user_id' => auth()->user()->id, 
            'type' => 'cliente', 
            'reporte' => $reporte,
            'name_table' => $name_table,
            'id_table' => $id_table
        ]);
    }

    public function set_tiempo_estado($fecha){
        $ahora = Carbon::now();
        $mañana = Carbon::tomorrow();

        $estado = 'pendiente';
        if($fecha < $ahora) $estado = 'vencido';
        if($fecha->format('Y-m-d') >= $mañana->format('Y-m-d')) $estado = 'proximo';
        return $estado;
    }

    public function update(Request $request){
        \DB::beginTransaction();
        try {
            $actividad = Actividade::find($request->id);
            $hoy = Carbon::now();

            $descripcion = $actividad->descripcion.'<p><b>ACTUALIZACIÓN ('.$hoy.'):</b> '.$request->observaciones.'</p>';
            
            $fecha = new Carbon($request->fecha.' '.$request->hora);
            $estado = $this->set_tiempo_estado($fecha);

            $actividad->update([
                'estado' => $estado,
                'fecha' => $fecha,
                'descripcion' => $descripcion
            ]);

            $reporte = 'edito la actividad '.$actividad->tipo.': '.$actividad->nombre.' / '.$actividad->descripcion;
            $this->create_report($actividad->id, $reporte, 'actividades');
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json($actividad);
    }

    public function by_user_fecha_actual(){
        $hoy = Carbon::now();
        $actividades = $this->get_user_actividades()
                        ->where('fecha', 'like', '%'.$hoy->format('Y-m-d').'%')
                        ->get();
        return response()->json($actividades);
    }

    public function by_user_estado(Request $request){
        $actividades = $this->get_user_actividades()
                        ->where('estado', $request->estado)
                        ->get();
        return response()->json($actividades);
    }

    public function get_user_actividades(){
        return Actividade::with('clientes')->orderBy('fecha', 'desc');
        // where('user_id', auth()->user()->id)
    }

    public function download($id){
        return Excel::download(new ActividadExport($id), 'actividad.xlsx');
    }

    // *** FUNCIONES PENDIENTES POR REVISAR

    // OBTENER TODAS LAS ACTIVIDADES POR ESTADO Y TIPO
    public function by_tipo_estado(Request $request){
        $actividades = $this->get_tipo_estado($request)
                            ->where('clientes.tipo', $request->clientetipo)
                            ->get();
        return response()->json($actividades);
    }

    // OBTENER TODAS LAS ACTIVIDADES POR CLIENTE, ESTADO Y TIPO
    public function by_cliente_tipo_estado(Request $request){
        $actividades = $this->get_tipo_estado($request)
                            ->where('actividades.cliente_id', $request->cliente_id)
                            ->get();
        return response()->json($actividades);
    }

    // OBTENER ACTIVIDADES DEL USUARIO EN SESION
    public function by_userid_tipo_estado(Request $request){
        $actividades = $this->get_tipo_estado($request)
                        ->where('clientes.tipo', $request->clientetipo)
                        ->where('actividades.user_id', auth()->user()->id)->get();
        return response()->json($actividades);
    }

    public function get_tipo_estado($request){
        return \DB::table('actividades')
                    ->select('actividades.*', 
                        'clientes.name as cliente_name','users.name as user_name')
                    ->join('clientes', 'actividades.cliente_id', '=', 'clientes.id')
                    ->join('users', 'actividades.user_id', '=', 'users.id')
                    ->where('actividades.tipo', $request->tipo)
                    ->where('actividades.estado', $request->estado)
                    ->orderBy('actividades.created_at', 'desc');
    }

    // *** FUNCIONES PENDIENTES POR REVISAR
}
