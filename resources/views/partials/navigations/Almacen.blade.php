<li>
	<a class="nav-link" href="{{ route('information.remisiones.lista') }}">{{ __("Remisiones") }}</a>
</li>
<li>
	<a class="nav-link" href="{{ route('information.pedidos.proveedor') }}">{{ __("Pedidos") }}</a>
</li>
<li>
	<a class="nav-link" href="{{ route('almacen.libros') }}">{{ __("Libros") }}</a>
</li>
@include('partials.navigations.logged')