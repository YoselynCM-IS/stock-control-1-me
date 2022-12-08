<?php

namespace App\Http\Controllers;

use App\Exports\DonacionesExport;
use App\Exports\donaciones\DonacionExport;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use App\Donacione;
use Carbon\Carbon;
use App\Regalo;
use App\Libro;
use Excel;
use PDF;

class DonacioneController extends Controller
{
    // OBTENER TODAS LAS DONACIONES
    public function index(){
        $regalos = Regalo::orderBy('id','desc')->paginate(20);
        return response()->json($regalos);
    }

    // GUARDAR UNA DONACIÓN
    // Función utilizada en DevoluciónController
    public function store(Request $request) {
        \DB::beginTransaction();
        try{
            $regalo = Regalo::create([
                'cliente_id' => $request->cliente_id,
                'plantel' => strtoupper($request->plantel),
                'descripcion' => strtoupper($request->descripcion),
                'unidades' => (int) $request->unidades,
                'entregado_por' => null,
                'creado_por' => auth()->user()->name
            ]);

            $lista_donaciones = [];
            $donaciones = collect($request->donaciones);
            $hoy = Carbon::now();
            $donaciones->map(function($donacion) use(&$lista_donaciones, $regalo, $hoy){
                $unidades = $donacion['unidades'];
                $libro_id = $donacion['id'];

                $lista_donaciones[] = [
                    'regalo_id' => $regalo->id,
                    'libro_id' => $libro_id,
                    'unidades' => $unidades,
                    'created_at' => $hoy,
                    'updated_at' => $hoy
                ];

                // DISMINUIR PIEZAS DE LOS LIBROS
                \DB::table('libros')->whereId($libro_id)
                    ->decrement('piezas', $unidades);
            });

            // Crear registros de donación
            Donacione::insert($lista_donaciones);
            
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
        }
        
        return response()->json($regalo);
    }

    public function detalles_donacion(){
        $regalo_id = Input::get('regalo_id');
        $regalo = Regalo::whereId($regalo_id)->with('donaciones.libro')->first();
        return response()->json($regalo);
    }

    public function by_plantel(){
        $queryPlantel = Input::get('queryPlantel');
        $regalos = Regalo::where('plantel','like','%'.$queryPlantel.'%')
                    ->orderBy('id','desc')->paginate(20);
        return response()->json($regalos);
    }

    public function by_fecha(){
        $inicio = Input::get('inicio');
        $final = Input::get('final');
        $plantel = Input::get('plantel');

        $fechas = $this->format_date($inicio, $final);
        $fecha1 = $fechas['inicio'];
        $fecha2 = $fechas['final'];

        if($plantel === null){
            $regalos = Regalo::whereBetween('created_at', [$fecha1, $fecha2])
                                ->orderBy('id','desc')->paginate(20);
        } else {
            $regalos = Regalo::where('plantel','like','%'.$plantel.'%')
                                ->whereBetween('created_at', [$fecha1, $fecha2])
                                ->orderBy('id','desc')->paginate(20);
        }
        
        return response()->json($regalos);
    }

    public function format_date($fecha1, $fecha2){
        $inicio = new Carbon($fecha1);
        $final 	= new Carbon($fecha2);
        $inicio = $inicio->format('Y-m-d 00:00:00');
        $final 	= $final->format('Y-m-d 23:59:59');

        $fechas = [
            'inicio' => $inicio,
            'final' => $final
        ];

        return $fechas;
    }

    public function download_donacion($plantel, $inicio, $final, $tipo){
        return Excel::download(new DonacionesExport($plantel, $inicio, $final, $tipo), 'reporte-donaciones.xlsx');
    }

    public function entrega_donacion(Request $request){
        $regalo = Regalo::whereId($request->id)->first();
        \DB::beginTransaction();
        try {
            $regalo->update(['entregado_por' => $request->entregado_por]);
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json($regalo);
    }

    public function download_regalo($id){
        return Excel::download(new DonacionExport($id), 'nota-donacion.xlsx');
    }
}
