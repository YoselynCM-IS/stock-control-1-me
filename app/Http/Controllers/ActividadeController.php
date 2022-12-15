<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Actividade;
use App\Cliente;
use App\Reporte;

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
            if($cliente_id == null){
                $cliente = Cliente::create([
                    'tipo' => 'PROSPECTO',
                    'name' => strtoupper($request->prospecto['name']),
                    'contacto' => strtoupper($request->prospecto['contacto']),
                    'email' => $request->prospecto['email'],
                    'telefono' => $request->prospecto['telefono'],
                    'estado_id' => $request->prospecto['estado_id'],
                    'user_id' => $request->prospecto['user_id']
                ]);
                $cliente_id = $cliente->id;

                $reporte = 'creo al '.$cliente->tipo.' '.$cliente->name;
                $this->create_report($cliente_id, $reporte, 'clientes');
            }
            
            $tipo = $request->tipo;
            $descripcion = $request->descripcion;
            $actividad = Actividade::create([
                'user_id' => auth()->user()->id, 
                'cliente_id' => $cliente_id, 
                'tipo' => $tipo, 
                'descripcion' => $descripcion, 
                'estado' => 'pendiente', 
                'fecha_recordatorio' => $request->fecha_recordatorio
            ]);

            $reporte = 'creo la actividad '.$actividad->tipo.': '.$actividad->descripcion.' para '.$actividad->cliente->name;
            $this->create_report($actividad->id, $reporte, 'actividades');

            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }

        return response()->json($actividad);
    }

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

    // MARCAR ACTIVIDADES COMO TEMRINADAS
    public function mark_actividades(Request $request){
        \DB::beginTransaction();
        try {
            $actividades = collect($request->selected);
            $actividades->map(function($actividad) use (&$prueba){
                Actividade::whereId($actividad['id'])
                                ->update(['estado' => 'completado']);

                $reporte = 'marco como completado la actividad '.$actividad['tipo'].': '.$actividad['descripcion'].' para '.$actividad['cliente_name'];
                $this->create_report($actividad['id'], $reporte, 'actividades');
            });
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json();
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

    public function create_report($id_table, $reporte, $name_table){
        Reporte::create([
            'user_id' => auth()->user()->id, 
            'type' => 'cliente', 
            'reporte' => $reporte,
            'name_table' => $name_table,
            'id_table' => $id_table
        ]);
    }
}
