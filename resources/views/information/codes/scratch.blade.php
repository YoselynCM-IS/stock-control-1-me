@extends('layouts.app')

@section('content')
    <h5><b>Scratch</b></h5><hr>
    @include('information.codes.partials.tabla', ['libros' => $scratch, 'diferencia' => true])
@endsection