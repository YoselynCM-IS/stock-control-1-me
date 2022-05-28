<?php

namespace App\Http\Controllers;

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\libros\CodesImport;
use Illuminate\Http\Request;
use App\Libro;
use App\Code;
use App\Dato;

class CodeController extends Controller
{
    // LISTA DE CODIGOS
    public function index(){
        $codes = Code::whereNotIn('estado', ['proceso', 'eliminado'])
                        ->orderBy('created_at', 'desc')
                        ->with('libro')->paginate(50);
        return response()->json($codes);
    }

    // OBTENER CODIGOS POR TITULO
    public function by_libro(Request $request){
        $codes = Code::where('libro_id', $request->libro_id)
                        ->whereNotIn('estado', ['proceso', 'eliminado'])
                        ->orderBy('created_at', 'desc')
                        ->with('libro')->paginate(50);
        return response()->json($codes);
    }

    // OBTENER CODIGOS POR EDITORIAL
    public function by_editorial(Request $request){
        $libros = Libro::where('type','digital')
                    ->where('editorial', $request->editorial)
                    ->where('estado', 'activo')
                    ->select('id')
                    ->orderBy('titulo', 'asc')->get();
        $ids = [];
        $libros->map(function($libro) use(&$ids){
            $ids[] = $libro->id;
        });
        $codes = Code::whereIn('libro_id', $ids)
                        ->whereNotIn('estado', ['proceso', 'eliminado'])
                        ->orderBy('created_at', 'desc')
                        ->with('libro')->paginate(50);
        return response()->json($codes);
    }

    // OBTENER CODIGOS POR EL CLIENTE AL QUE SE LE VENDIO
    public function by_cliente(Request $request){
        $datos = \DB::table('datos')
                    ->join('remisiones', 'datos.remisione_id', '=', 'remisiones.id')
                    ->join('libros', 'datos.libro_id', '=', 'libros.id')
                    ->join('code_dato', 'datos.id', '=', 'code_dato.dato_id')
                    ->where('remisiones.cliente_id', $request->cliente_id)
                    ->whereNotIn('remisiones.estado', ['Cancelado'])
                    ->where('libros.type', 'digital')
                    ->select('code_dato.code_id as code_id')
                    ->orderBy('remisiones.id', 'asc')
                    ->get();

        $codes_ids = [];
        $datos->map(function($dato) use(&$codes_ids){
            $codes_ids[] = $dato->code_id;
        });

        $codes = Code::whereIn('id', $codes_ids)
                        ->whereNotIn('estado', ['proceso', 'eliminado'])
                        ->orderBy('created_at', 'desc')
                        ->with('libro')->paginate(2);
        return response()->json($codes);
    }

    // SUBIR CODIGOS
    public function upload(Request $request){
        $array = Excel::toArray(new CodesImport, request()->file('file'));
        try {
            $count = 0;
            $libro = Libro::find($request->libro_id);
            $lista = collect($array[0]);
            $codigos = collect();

            $lista->map(function($row) use(&$codigos, &$count, $libro){
                if($row[0] == $libro->titulo){
                    $code = Code::where('codigo', $row[1])->firstOr(function () use($libro, $row, &$codigos, &$count) {
                        $code = Code::create([
                            'libro_id' => $libro->id, 
                            'codigo' => $row[1],
                            'tipo'  => 'alumno'
                        ]);
                        $codigos->push($code);
                        $count++;
                        return $code;
                    });
                }                
            });

            // $libro->update(['piezas' => $libro->piezas + $count]);
            $datos = ['codes' => $codigos, 'libro_id' => $libro->id, 'libro' => $libro->titulo, 'unidades' => $count];
        }  catch (Exception $e) {
            $success = $exception->getMessage();
        }
        
        return response()->json($datos);
    }
}
