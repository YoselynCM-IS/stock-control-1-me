<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Element;
use App\Reporte;
use App\Order;

class OrderController extends Controller
{
    // VISTA PARA LOS PEDIDOS DE LOS CLIENTES
    public function proveedor(){
        return view('information.orders.lista-proveedor');
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
            $status = $request->status;
            $order->update([
                'status' => $status,
                'observations' => $order->observations.'<br>'.$request->observations
            ]);

            $reporte = 'actualizo el estado de un pedido al proveedor '.$order->provider.' PEDIDO: '.$order->identifier.' / '.$status;
            $this->create_report($order->id, $reporte);

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

            $reporte = ' cancelo un pedido al proveedor '.$order->provider.' PEDIDO: '.$order->identifier;
            $this->create_report($order->id, $reporte);
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

            $order = Order::find($request->id);
            $order->update([
                'total_bill' => $request->total_bill
            ]);

            $reporte = 'registro los costos del pedido al proveedor '.$order->provider.' PEDIDO: '.$order->identifier;
            $this->create_report($order->id, $reporte);
        \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json(true);
    }

    public function create_report($order_id, $reporte){
        Reporte::create([
            'user_id' => auth()->user()->id, 
            'type' => 'cliente', 
            'reporte' => $reporte,
            'name_table' => 'orders', 
            'id_table' => $order_id
        ]);
    }
}
