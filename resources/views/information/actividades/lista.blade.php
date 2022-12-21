@extends('layouts.app')

@section('content')
    <crm-inicio-component :role_id="{{ auth()->user()->role_id }}"></crm-inicio-component>
    <!-- <actividades-component tipo_cliente="{{$tipo}}"></actividades-component> -->
@endsection