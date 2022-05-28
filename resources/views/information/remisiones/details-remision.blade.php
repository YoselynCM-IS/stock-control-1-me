@extends('layouts.app-simple')

@section('content')
    <details-remision-component :remision="{{$remision}}" :role_id="{{auth()->user()->role_id}}"></details-remision-component>
@endsection