<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Reporte;
use App\User;

class ReporteController extends Controller
{
    public function lista(){
        return view('information.reportes.lista');
    }

    public function by_type_estado(Request $request){
        $reportes = $this->set_type($request)->paginate(20);
        return response()->json($reportes);
    }

    public function set_type($request){
        if($request->type == 'general')
            $reportes = Reporte::whereIn('type', ['cliente', 'proveedor']);
        else
            $reportes = Reporte::where('type', 'libro');
        
        return $reportes->with('user')->where('estado', $request->estado);
    }

    public function by_type_estado_fecha(Request $request){
        $reportes = $this->set_type($request)
                        ->where('created_at', 'like', '%'.$request->fecha.'%')        
                        ->paginate(20);
        return response()->json($reportes);
    }

    public function by_type_estado_usuario(Request $request){
        $reportes = $this->set_type($request)->where('user_id', $request->user_id)        
                        ->paginate(20);
        return response()->json($reportes);
    }
}
