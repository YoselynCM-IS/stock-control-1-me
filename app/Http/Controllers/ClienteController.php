<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Cliente;
use App\Remisione;
use App\Dato;
use App\Exports\ClientesExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Remcliente;
use App\Cctotale;
use App\Corte;

class ClienteController extends Controller
{
    // OBTENER TODOS LOS CLIENTES
    public function index(){
        $clientes = Cliente::with('user', 'estado')->orderBy('name', 'asc')->paginate(20);
        return response()->json($clientes);
    }

    // MOSTRAR LOS CLIENTES POR COINCIDENCIA DE NOMBRE PAGINADO
    public function by_name(){
        $cliente = Input::get('cliente');
        $clientes = Cliente::where('name','like','%'.$cliente.'%')
                ->with('user', 'estado')->orderBy('name', 'asc')->paginate(20);
        return response()->json($clientes);
    }

    // OBTENER UN CLIENTE POR ID
    public function show(){
        $cliente_id = Input::get('cliente_id');
        $cliente = Cliente::whereId($cliente_id)->with('user', 'estado')->first();
        return response()->json($cliente);
    }
    
    // MOSTRAR TODOS LOS CLIENTES
    // Función utilizada en los componentes
    // - AdeudosComponent - ClientesComponent - DevolucionAdeudosComponent
    // - DevolucionComponent - ListadoComponent - PagosComponent - RemisionComponent - RemisionesComponent
    public function mostrarClientes(){
        $queryCliente = Input::get('queryCliente');
        $clientes = $this->get_likename($queryCliente)->get();
        return response()->json($clientes);
    }

    public function get_likename($queryCliente){
        return Cliente::where('name','like','%'.$queryCliente.'%')->orderBy('name', 'asc');
    }

    // EDITAR DATOS DE CLIENTE
    // Función utilizada en ClientesComponent
    public function update(Request $request){
        $cliente_id = $request->id;
        $cliente = Cliente::whereId($cliente_id)->first();
        $cliente->name = 'CLIENTE-'.$cliente->name;
        $cliente->save();
        $this->validacion($request);
        \DB::beginTransaction();
        try {
            $cliente->update([
                'name' => strtoupper($request->name),
                'contacto' => strtoupper($request->contacto),
                'email' => $request->email,
                'telefono' => $request->telefono,
                'direccion' => strtoupper($request->direccion),
                'condiciones_pago' => strtoupper($request->condiciones_pago),
                'rfc' => strtoupper($request->rfc),
                'fiscal' => strtoupper($request->fiscal),
                'tipo' => $request->tipo, 
                'user_id' => $request->user_id, 
                'estado_id' => $request->estado_id, 
                'tel_oficina' => $request->tel_oficina
            ]);
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json($cliente);
    }

    // GUARDAR NUEVO CLIENTE
    // Función utilizada en NewClienteComponent
    public function store(Request $request){
        $this->validacion($request);
        \DB::beginTransaction();
        try {
            $cliente = Cliente::create([
                'name' => strtoupper($request->name),
                'contacto' => strtoupper($request->contacto),
                'email' => $request->email,
                'telefono' => $request->telefono,
                'direccion' => strtoupper($request->direccion),
                'condiciones_pago' => strtoupper($request->condiciones_pago),
                'rfc' => strtoupper($request->rfc),
                'fiscal' => strtoupper($request->fiscal),
                'tipo' => $request->tipo, 
                'user_id' => $request->user_id, 
                'estado_id' => $request->estado_id, 
                'tel_oficina' => $request->tel_oficina
            ]);

            Remcliente::create([
                'cliente_id' => $cliente->id,
                'total' => 0,
                'total_pagar' => 0
            ]);


            $hoy = Carbon::now();
            $month = $hoy->format('m');
            
            // CORTE A: 07 - 11 / CORTE B: 12 - 06 
            $tipo = 'B';
            if($month >= 7 && $month <= 11) $tipo = 'A';

            $corte = Corte::whereTipo($tipo)
                                ->get()->last();

            Cctotale::create([
                'corte_id' => $corte->id, 
                'cliente_id' => $cliente->id
            ]);

            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json($cliente);
    }

    public function validacion($request){
        $this->validate($request, [
            'name' => 'min:3|max:100|required|string|unique:clientes',
            'email' => 'min:8|max:50|required|email',
            'telefono' => 'required|numeric|max:9999999999|min:1000000',
            'direccion' => 'min:3|max:250|required|string',
            'condiciones_pago' => 'min:3|max:150|required|string',
            'rfc' => 'min:3|max:50|required|string',
            'fiscal' => 'min:3|max:250|required|string',
        ]);
    }

    public function descargar_clientes(){
        return Excel::download(new ClientesExport, 'clientes.xlsx');
    }

    public function getTodo(){
        $clientes = Cliente::orderBy('name', 'asc')->get();
        return response()->json($clientes);
    }

    public function get_estados(){
        $estados = \DB::table('estados')->orderBy('estado', 'asc')->get();
        return response()->json($estados);
    }

    public function get_usuarios(){
        $users = \DB::table('users')->whereNotIn('role_id', [6])
                        ->orderBy('name', 'asc')->get();
        return response()->json($users);
    }

    public function save_libro(Request $request){
        $cliente = Cliente::find($request->cliente_id);
        $cliente->libros()->attach($request->libro_id, ['costo_unitario' => (float) $request->costo_unitario]);
        return response()->json($cliente);
    }

    public function update_libro(Request $request){
        $cliente = Cliente::find($request->cliente_id);
        $costo_unitario = (float) $request->costo_unitario;
        $cliente->libros()->updateExistingPivot($request->libro_id, [
            'costo_unitario' => $costo_unitario
        ]);
        return response()->json($costo_unitario);
    }

    public function get_libros(Request $request){
        $cliente = Cliente::find($request->cliente_id);
        return response()->json($cliente->libros);
    }

    public function delete_libro(Request $request){
        $cliente = Cliente::find($request->cliente_id);
        $cliente->libros()->detach($request->libro_id);
        return response()->json($cliente);
    }

    public function by_tipo(Request $request){
        $clientes = $this->get_likename($request->queryCliente)
                        ->where('tipo', $request->tipo)->get();
        return response()->json($clientes);
    }
}
