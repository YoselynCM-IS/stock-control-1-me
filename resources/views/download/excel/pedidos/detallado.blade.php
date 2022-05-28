@foreach($compras as $compra)
    <table>
        <tr>
            <th><b>Num. pedido</b></th>
            <th><b>Cliente</b></th>
            <th><b>Fecha de creación</b></th>
            <th><b>Unidades</b></th>
            <th><b>Total</b></th>
            <th><b>Forma de pago</b></th>
        </tr>
        <tr>
            <td>{{ $compra->pedido }}</td> 
            <td>{{ $compra->usuario }}</td>
            <td>{{ $compra->created_at->format('Y-m-d') }}</td>
            <td>{{ $compra->unidades }}</td>
            <td>{{ $compra->total }}</td>
            <td>{{ $compra->tipo_pago }}</td>
        </tr>
    </table>
    <table>
        <tr>
            <th><b>ISBN</b></th>
            <th><b>Libro</b></th>
            <th><b>Costo unitario</b></th>
            <th><b>Unidades</b></th>
            <th><b>Subtotal</b></th>

        </tr>
        @foreach($compra->pedidos as $pedido)
            <tr>
                <td>{{ $pedido->libro->ISBN }}</td>
                <td>{{ $pedido->libro->titulo }}</td>
                <td>{{ $pedido->costo_unitario }}</td>
                <td>{{ $pedido->unidades }}</td>
                <td>{{ $pedido->total }}</td>
            </tr>
        @endforeach
    </table><br>
@endforeach