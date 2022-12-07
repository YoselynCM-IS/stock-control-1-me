<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Peticione;
use App\Element;
use App\Pedido;
use App\Order;

class PedidoController extends Controller
{
    // VISTA PARA LOS PEDIDOS DE LOS CLIENTES
    public function cliente(){
        return view('information.pedidos.lista-cliente');
    }

    // OBTENER TODOS LOS PEDIDOS
    public function index(){
        $pedidos = Pedido::orderBy('created_at', 'desc')
                    ->with('user', 'cliente')->paginate(20);
        return response()->json($pedidos);
    }

    // DETALLES DEL PEDIDO
    public function show($pedido_id){
        $pedido = $this->get_pedido($pedido_id);
        return view('information.pedidos.details-pedido', compact('pedido'));
    }

    public function get_pedido($pedido_id){
        return Pedido::whereId($pedido_id)->with('user', 'cliente', 'peticiones.libro')->first();
    }

    // GUARDAR PEDIDO
    public function store(Request $request){
        \DB::beginTransaction();
        try {
            $pedido = Pedido::create([
                'user_id' => auth()->user()->id,
                'cliente_id' => $request->cliente_id, 
                'total_quantity' => $request->total_quantity
            ]);
            
            $peticiones = collect($request->libros);
            $peticiones->map(function($peticione) use ($pedido){
                Peticione::create([
                    'pedido_id' => $pedido->id,
                    'libro_id' => $peticione['libro']['id'], 
                    'quantity' => (int) $peticione['quantity']
                ]);
            });
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json($pedido);
    }

    // PREPRAR PEDIDO
    public function preparar($pedido_id){
        $p = $this->get_pedido($pedido_id);
        $peticiones = collect();
        $p->peticiones->map(function($peticione) use (&$peticiones){
            $quantity = $peticione->quantity;
            $piezas = $peticione->libro->piezas;

            $faltante = 0;
            if($quantity > $piezas) $faltante = $quantity - $piezas;
            
            $peticiones->push([
                'id' => $peticione->id,
                'libro_id' => $peticione->libro_id, 
                'editorial' => $peticione->libro->editorial,
                'ISBN' => $peticione->libro->ISBN,
                'titulo' => $peticione->libro->titulo,
                'quantity' => $quantity,
                'existencia' => $piezas,
                'faltante' => $faltante,
                'solicitar' => 0
            ]);
        });
        
        $pedido = collect([
            'id' => $p->id,
            'user_name' => $p->user->name,
            'cliente_name' => $p->cliente->name, 
            'total_quantity' => $p->total_quantity,
            'total_solicitar' => 0,
            'created_at' => $p->created_at,
            'peticiones' => $peticiones
        ]);
        return view('information.pedidos.preparar-pedido', compact('pedido'));
    }

    // GUARDAR PEDIDO YA PREPARADO PARA PROVEEDOR
    public function preparado(Request $request){
        \DB::beginTransaction();
        try {
            $e = $this->create_peticiones($request->peticiones);

            $pedido = Pedido::find($request->id);
            $pedido->update([
                'total_solicitar' => $request->total_solicitar,
                'estado' => 'en orden'
            ]);

            // CREAR ORDEN POR SEPARADO
            $es = $e->unique();
            $editoriales = collect($es->values()->all());

            $fecha_actual = Carbon::now();
            $order_ids = collect();
            $editoriales->map(function($editorial) use(&$order_ids, $fecha_actual, $pedido){
                $provider_count = Order::where('provider', $editorial)->count();
                $order = Order::create([
                    'pedido_id' => $pedido->id,
                    'cliente_id' => $pedido->cliente_id,
                    'destination' => $pedido->cliente->name,
                    'identifier' => 'PED '.($provider_count + 1).'-'.$fecha_actual->format('Y'),
                    'date' => $fecha_actual->format('Y-m-d'),
                    'provider' => $editorial,
                    'creado_por' => auth()->user()->name
                ]);
                $order_ids->push([
                    'order_id' => $order->id,
                    'editorial' => $order->provider
                ]);
            });

            $prueba = [];
            $peticiones = $pedido->peticiones;
            $peticiones->map(function($peticione) use($order_ids, &$prueba){
                if($peticione->solicitar > 0){
                    $order_ids->map(function($oi) use(&$prueba, $peticione){
                        $editorial = $peticione->libro->editorial;
                        if($oi['editorial'] == $editorial) {
                            $element = Element::create([
                                'order_id' => $oi['order_id'],
                                'libro_id' => $peticione->libro_id,
                                'quantity' => $peticione->solicitar
                            ]);
                        }
                    });
                }
            });
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }

        return response()->json($prueba);
    }

    public function create_peticiones($ps){
        $editoriales = collect();
        $peticiones = collect($ps);
        $peticiones->map(function($peticione) use(&$editoriales){
            Peticione::whereId($peticione['id'])->update([
                'existencia' => $peticione['existencia'],
                'faltante' => $peticione['faltante'],
                'solicitar' => (int) $peticione['solicitar']
            ]);

            $editoriales->push($peticione['editorial']);
        });
        return $editoriales;
    }

    public function despachar(Request $request){
        \DB::beginTransaction();
        try {
            $pedido = Pedido::find($request->id);
            $pedido->update([
                'estado' => 'de inventario',
                'comentarios' => $pedido->comentarios.'<p>El pedido se tomará de lo disponible en inventario.</p>'
            ]);

            $this->create_peticiones($request->peticiones);
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json($pedido);
    }

    public function cancelar(Request $request){
        \DB::beginTransaction();
        try {
            Pedido::whereId($request->pedido_id)->update([
                'estado' => 'cancelado',
            ]);
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json(true);
    }

    public function by_provider(Request $request){
        $pedidos = Order::where('provider', $request->provider)
                    ->orderBy('created_at', 'desc')->paginate(20);
        return response()->json($pedidos);
    }
}
