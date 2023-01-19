@extends('layouts.app')

@section('content')
    <b-tabs content-class="mt-3" justified>
        <b-tab title="Licencias" active>
            @include('information.codes.partials.tabla', ['libros' => $profesor])
        </b-tab>
        <b-tab title="Demos">
            @include('information.codes.partials.tabla', ['libros' => $demo])
        </b-tab>
    </b-tabs>
@endsection