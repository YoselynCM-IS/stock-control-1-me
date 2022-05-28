<table>
    <tr>
        <td><b>Folio:</b></td>
        <td>{{ $promotion->folio }}</td>
        <td></td>
        <td><b>Fecha:</b></td>
        <td>{{ $promotion->created_at }}</td>
    </tr>
    <tr>
        <td><b>Plantel:</b></td>
        <td>{{ $promotion->plantel }}</td>
        <td></td>
        <td><b>Descripción:</b></td>
        <td>{{ $promotion->descripcion }}</td>
    </tr>
    <tr></tr>
    <tr>
        <th><b>ISBN</b></th>
        <th><b>Libro</b></th> 
        <th><b>Unidades</b></th>
    </tr>
    @foreach($promotion->departures as $departure)
        <tr>
            <td>{{ $departure->libro->ISBN }}</td> 
            <td>{{ $departure->libro->titulo }}</td>
            <td>{{ number_format($departure->unidades) }}</td>
        </tr>
    @endforeach 
    <tr>
        <td></td>
        <td><b>TOTAL</b></td>
        <td><b>{{ number_format($promotion->unidades) }}</b></td>
    </tr>
</table>