<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use App\Order;
use App\Element;

class PedidoController extends Controller
{
    // OBTENER TODOS LOS PEDIDOS
    public function index(){
        $pedidos = Order::orderBy('created_at', 'desc')->paginate(20);
        return response()->json($pedidos);
    }

    // Guardar pedido
    public function store(Request $request){
        \DB::beginTransaction();
        try{
            $order = Order::create([
                'identifier' => strtoupper($request->identifier),
                'date' => $request->date,
                'provider' => $request->provider,
                'destination' => strtoupper($request->destination),
                'total_bill' => $request->total_bill,
                'creado_por' => auth()->user()->name
            ]);

            foreach ($request->registros as $registro) {
                $element = Element::create([
                    'order_id' => $order->id,
                    'libro_id' => $registro['libro']['id'],
                    'quantity' => $registro['quantity'],
                    'unit_price' => $registro['unit_price'],
                    'total' => $registro['total']
                ]);
            }
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        
        $new_order = Order::find($order->id);
        return response()->json($new_order);
    }

    // DETALLES DEL PEDIDO
    public function show(){
        $pedido_id = Input::get('pedido_id');
        $order = Order::whereId($pedido_id)->with('elements.libro')->first();
        return response()->json($order);
    }

    // ACTUALIZAR ESTADO
    public function change_status(Request $request){
        $order = Order::whereId($request->pedido_id)->first();
        \DB::beginTransaction();
        try{
            $order->update([
                'status' => $request->status,
                'observations' => $request->observations
            ]);

            // if($order->status == 'incompleto'){
            //     $actual_total_bill = 0;

            //     foreach ($request->elements as $element) {
            //         $actual_total = (double) $element['actual_total'];

            //         Element::whereId($element['id'])->update([
            //             'actual_quantity' => (int) $element['actual_quantity'],
            //             'actual_total' => $actual_total
            //         ]);

            //         $actual_total_bill += $actual_total;
            //     }

            //     $order->update([ 'actual_total_bill' => $actual_total_bill ]);
            // }
            // if($order->status == 'completo'){
            //     foreach ($order->elements as $element) {
            //         Element::whereId($element->id)->update([
            //             'actual_quantity' => $element->quantity,
            //             'actual_total' => $element->total
            //         ]);
            //     }

            //     $order->update([ 'actual_total_bill' => $order->total_bill ]);
            // }
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }

        return response()->json($order);
    }

    public function cancelar_pedido(Request $request){
        $order = Order::find($request->id);
        \DB::beginTransaction();
        try{
            $order->update(['status' => 'cancelado']);
        \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json($order);
    }

    public function get_provider(){
        $provider = Input::get('provider');
        $pedidos = Order::where('provider', $provider)
                    ->orderBy('created_at', 'desc')->paginate(20);
        return response()->json($pedidos);
    }

    public function get_date(){
        $date = Input::get('date');
        $pedidos = Order::where('date','like','%'.$date.'%')
                    ->orderBy('created_at', 'desc')->paginate(20);
        return response()->json($pedidos);
    }
}
