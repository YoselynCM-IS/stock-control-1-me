@extends('layouts.app-simple')

@section('content')
    <details-pedido-component :pedido="{{$pedido}}"></details-pedido-component>
@endsection