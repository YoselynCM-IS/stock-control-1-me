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
        $clientes = Cliente::orderBy('name', 'asc')->paginate(20);
        return response()->json($clientes);
    }

    // MOSTRAR LOS CLIENTES POR COINCIDENCIA DE NOMBRE PAGINADO
    public function by_name(){
        $cliente = Input::get('cliente');
        $clientes = Cliente::where('name','like','%'.$cliente.'%')
                ->orderBy('name', 'asc')->paginate(20);
        return response()->json($clientes);
    }

    // OBTENER UN CLIENTE POR ID
    public function show(){
        $cliente_id = Input::get('cliente_id');
        $cliente = Cliente::find($cliente_id);
        return response()->json($cliente);
    }
    
    // MOSTRAR TODOS LOS CLIENTES
    // Función utilizada en los componentes
    // - AdeudosComponent - ClientesComponent - DevolucionAdeudosComponent
    // - DevolucionComponent - ListadoComponent - PagosComponent - RemisionComponent - RemisionesComponent
    public function mostrarClientes(){
        $queryCliente = Input::get('queryCliente');
        $clientes = Cliente::where('name','like','%'.$queryCliente.'%')->orderBy('name', 'asc')->get();
        return response()->json($clientes);
    }

    // EDITAR DATOS DE CLIENTE
    // Función utilizada en ClientesComponent
    public function editar(Request $request){
        $cliente = Cliente::whereId($request->id)->first();
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
                'fiscal' => strtoupper($request->fiscal)
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
                'fiscal' => strtoupper($request->fiscal)
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
}
