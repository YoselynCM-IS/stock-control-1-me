<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Input;
use App\Exports\PromotionsExport;
use App\Exports\promociones\PromotionExport;
use Illuminate\Http\Request;
use App\Prodevolucione;
use App\Promotion;
use App\Departure;
use Carbon\Carbon;
use App\Libro;
use Excel;
use PDF;

class PromotionController extends Controller
{

    // OBTENER TODAS LAS PROMOCIONES PAGINADAS
    public function index(){
        $promotions = Promotion::with('departures')->orderBy('folio','desc')
                        ->paginate(20);
        return response()->json($promotions);
    }

    // MOSTRAR PROMOCIONES POR FOLIO
    // Función utilizada en PromocionesComponent
    public function buscar_folio(){
        $folio = Input::get('folio');
        $promotion = Promotion::where('folio', $folio)->first();
        return response()->json($promotion);
    }

    // MOSTRAR PROMOCIONES POR PLANTEL
    // Función utilizada en PromocionesComponent
    public function buscar_plantel(){
        $queryPlantel = Input::get('queryPlantel');
        $promotions = Promotion::where('plantel','like','%'.$queryPlantel.'%')
                ->orderBy('folio','desc')->paginate(20);
        return response()->json($promotions);
    }

    // MOSTRAR LOS DETALLES DE UNA PROMOCIÓN
    // Función utilizada en PromocionesComponent
    public function obtener_departures(){
        $promotion_id = Input::get('promotion_id');
        $promotion = Promotion::whereId($promotion_id)
            ->with('departures.libro', 'prodevoluciones.libro')->first();
        // $departures = Departure::where('promotion_id', $promotion_id)->with('libro')->get();
        return response()->json($promotion);
    }

    // GUARDAR UNA PROMOCIÓN
    // Función utilizada en PromocionesComponent
    public function store(Request $request){
        try{
            \DB::beginTransaction();
            $num = Promotion::get()->count() + 1;
            if($num < 10){
                $folio = 'A-P000'.$num;
            }
            if($num >= 10 && $num < 100){
                $folio = 'A-P00'.$num;
            }
            if($num >= 100 && $num < 1000){
                $folio = 'A-P0'.$num;
            }
            if($num >= 1000 && $num < 10000){
                $folio = 'A-P'.$num;
            }
            $promotion = Promotion::create([
                'folio' => $folio,
                'plantel' => strtoupper($request->plantel),
                'descripcion' => strtoupper($request->descripcion),
                'entregado_por' => $request->entregado_por,
                'creado_por' => auth()->user()->name
            ]);

            $unidades = 0;
            $departures = collect($request->departures);
            $departures->map(function($departure) use($promotion, &$unidades){
                $u = (int) $departure['unidades'];
                $libro_id = $departure['id'];
                Departure::create([
                    'promotion_id' => $promotion->id,
                    'libro_id' => $libro_id,
                    'unidades' => $u,
                    'unidades_pendientes' => $u
                ]);
                $libro = Libro::whereId($libro_id)->first();
                $libro->update(['piezas' => $libro->piezas - $u]);
                $unidades += $u;
            });
            $promotion->update([
                'unidades' => $unidades,
                'unidades_pendientes' => $unidades
            ]);
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
        }
        return response()->json($promotion);
    }

    // BUSCAR PROMOCIÓN POR FECHAS
    public function buscar_fecha_promo(){
        $inicio = Input::get('inicio');
        $final = Input::get('final');
        $plantel = Input::get('plantel');

        $fechas = $this->format_date($inicio, $final);
        $fecha1 = $fechas['inicio'];
        $fecha2 = $fechas['final'];

        if($plantel === null){
            $promotions = Promotion::whereBetween('created_at', [$fecha1, $fecha2])
                                ->orderBy('folio','desc')->paginate(20);
        } else {
            $promotions = Promotion::where('plantel','like','%'.$plantel.'%')
                                ->whereBetween('created_at', [$fecha1, $fecha2])
                                ->orderBy('folio','desc')->paginate(20);
        }
        
        return response()->json($promotions);
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

    // DESCARGAR REPORTE
    public function download_promotion($plantel, $inicio, $final, $tipo){
        return Excel::download(new PromotionsExport($plantel, $inicio, $final, $tipo), 'reporte-promociones.xlsx');
    }

    public function download_promocion($id){
        $promocion = Promotion::find($id);
        $name_archivo = 'promocion_' . $promocion->folio . '.xlsx';
        return Excel::download(new PromotionExport($promocion->id), $name_archivo);
    }

    public function cancel(Request $request){
        try{
            \DB::beginTransaction();
            $promotion = Promotion::find($request->promotion_id);

            $promotion->departures->map(function($departure){
                \DB::table('libros')->whereId($departure->libro_id)
                        ->increment('piezas', $departure->unidades);    
            });

            $promotion->update([
                'estado' => 'Cancelado',
                'unidades_pendientes' => 0
            ]);
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
        }
        return response()->json(true);
    }

    // GUARDAR DEVOLUCION
    public function devolucion(Request $request){
        try{
            \DB::beginTransaction();

            $devoluciones = collect($request->departures);
            $devoluciones->map(function($d){
                $unidades_devolucion = (int) $d['unidades'];

                if($unidades_devolucion > 0){
                    $departure = Departure::find($d['departure_id']);
                    $libro_id = $departure->libro_id;

                    Prodevolucione::create([
                        'promotion_id' => $departure->promotion_id, 
                        'libro_id' => $libro_id, 
                        'unidades' => $unidades_devolucion,
                        'creado_por' => auth()->user()->name
                    ]);

                    // ACTUALIZAR UNIDADES PENDIENTES DE ESE LIBRO EN ESA PROMOCION
                    $departure->update([
                        'unidades_pendientes' => $departure->unidades_pendientes - $unidades_devolucion
                    ]);

                    // AUMENTAR PIEZAS DE LOS LIBROS DEVUELTOS
                    \DB::table('libros')->whereId($libro_id)
                        ->increment('piezas', $unidades_devolucion); 
                }
            });
            
            $promotion = Promotion::find($request->id);
            $unidades_devolucion = $promotion->unidades_devolucion + (int)$request->unidades_devolucion;
            $unidades_pendientes = $promotion->unidades - $unidades_devolucion;
            $promotion->update([
                'unidades_devolucion' => $unidades_devolucion,
                'unidades_pendientes' => $unidades_pendientes
            ]);
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
        }
        return response()->json(true);
    }
}
