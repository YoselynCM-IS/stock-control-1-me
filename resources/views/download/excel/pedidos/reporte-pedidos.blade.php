<!doctype html>
<html>
    <head>
        <title>Pedidos</title>
    </head>
    <body>
        <div>
            <div>
                <label><b>Fecha: {{ $fecha }}</b> </label><br>
                @if($final != '0000-00-00')
                    <label><b>De:</b> {{ $inicio }} - <b>A:</b> {{ $final }}</label><br>
                @endif
                <br>
                @if($tipo === 'general')
                    @include('download.excel.pedidos.general', ['compras' => $compras, 'total_unidades' => $total_unidades, 'total' => $total])
                @else 
                @include('download.excel.pedidos.detallado', ['compras' => $compras])
                @endif
            </div>
        </div>
    </body>
</html>
