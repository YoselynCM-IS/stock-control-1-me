<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Actividade;
use App\Cliente;
use App\Reporte;
use Carbon\Carbon;

class ActividadeController extends Controller
{
    // OBTENER LA LISTA DE ACTIVIDADES
    // public function index(){
    //     return view('information.actividades.lista');
    // }

    // OBTENER ACTIVIDADES POR TIPO DE CLIENTE
    public function get_tipocliente($tipo){
        return view('information.actividades.lista', compact('tipo'));
    }

    // GUARDAR ACTIVIDAD
    public function store(Request $request){
        \DB::beginTransaction();
        try {
            $cliente_id = $request->cliente_id;
            $tipo = $request->tipo;
            $descripcion = $request->descripcion;
            $fecha = $request->fecha.' '.$request->hora;

            $actividad = Actividade::create([
                'user_id' => auth()->user()->id, 
                'cliente_id' => $cliente_id, 
                'nombre' => $request->nombre,
                'tipo' => $tipo, 
                'descripcion' => $descripcion, 
                'estado' => 'pendiente', 
                'fecha' => $fecha,
                'lugar' => $request->lugar
            ]);

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
            $actividad = Actividade::find($request->id);
            $actividad->update([
                'estado' => $request->estado,
                'exitosa' => $request->exitosa, 
                'observaciones' => $request->observaciones
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

    public function update(Request $request){
        \DB::beginTransaction();
        try {
            $actividad = Actividade::find($request->id);
            $hoy = Carbon::now();

            $descripcion = $actividad->descripcion.'<p><b>ACTUALIZACIÓN ('.$hoy.'):</b> '.$request->observaciones.'</p>';
            $fecha = $request->fecha.' '.$request->hora;
            
            $estado = 'pendiente';
            if($fecha < $hoy->toDateTimeString()) $estado = 'vencido';

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
        return Actividade::where('user_id', auth()->user()->id)
                ->with('cliente')->orderBy('fecha', 'desc');
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
}
