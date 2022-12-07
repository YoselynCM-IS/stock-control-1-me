<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Element;
use App\Order;

class OrderController extends Controller
{
    // VISTA PARA LOS PEDIDOS DE LOS CLIENTES
    public function proveedor(){
        return view('information.pedidos.lista-proveedor');
    }

    // OBTENER TODOS LOS PEDIDOS
    public function index(){
        $ps = Order::orderBy('created_at', 'desc');
        if(auth()->user()->role_id == 3){
            $pedidos = $ps->where('total_bill', '>', 0)->paginate(20);
        } else {
            $pedidos = $ps->paginate(20);
        }
        
        return response()->json($pedidos);
    }

    // DETALLES DEL PEDIDO
    public function show($order_id){
        $order = Order::whereId($order_id)->with('elements.libro')->first();
        return view('information.orders.details-order', compact('order'));
    }

    // ACTUALIZAR ESTADO
    public function change_status(Request $request){
        $order = Order::whereId($request->pedido_id)->first();
        \DB::beginTransaction();
        try{
            $order->update([
                'status' => $request->status,
                'observations' => $order->observations.'<br>'.$request->observations
            ]);
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }

        return response()->json($order);
    }

    public function cancelar(Request $request){
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

    public function add_costo(Request $request){
        \DB::beginTransaction();
        try{
            $elements = collect($request->elements);
            $elements->map(function($element){
                Element::whereId($element['id'])->update([
                    'unit_price' => (float) $element['unit_price'],
                    'total' => (float) $element['total']
                ]);
            });

            Order::whereId($request->id)->update([
                'total_bill' => $request->total_bill
            ]);
        \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json(true);
    }
}
