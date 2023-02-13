<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th scope="col">N.</th>
                <th scope="col">ISBN</th>
                <th scope="col">TITULO</th>
                <th scope="col">EXISTENCIA</th>
            </tr>
        </thead>
        <tbody>
            @foreach($libros as $libro)
                <tr>
                    <th scope="row">{{$loop->index + 1}}</th>
                    <td>{{ $libro->libro->ISBN }}</td>
                    <td>{{ $libro->libro->titulo }}</td>
                    <td>{{ $libro->inventario }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>