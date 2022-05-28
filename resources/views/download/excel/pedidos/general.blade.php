<table>
    <tr>
        <th><b>Num. pedido</b></th>
        <th><b>Cliente</b></th>
        <th><b>Fecha de creación</b></th>
        <th><b>Unidades</b></th>
        <th><b>Total</b></th>
        <th><b>Forma de pago</b></th>
    </tr>
    @foreach($compras as $compra)
        <tr>
            <td>{{ $compra->pedido }}</td> 
            <td>{{ $compra->usuario }}</td>
            <td>{{ $compra->created_at->format('Y-m-d') }}</td>
            <td>{{ $compra->unidades }}</td>
            <td>{{ $compra->total }}</td>
            <td>{{ $compra->tipo_pago }}</td>
        </tr>
    @endforeach  
    <tr>
        <td></td><td></td><td></td>
        <td><b>{{ $total_unidades }}</b></td>
        <td><b>{{ $total }}</b></td>
    </tr>
</table>