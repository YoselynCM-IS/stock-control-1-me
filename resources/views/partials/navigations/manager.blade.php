<li class="nav-item dropdown">
	<a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
		Remisiones <span class="caret"></span>
	</a>
	<div class="dropdown-menu dropdown-menu-left" aria-labelledby="navbarDropdown">
		<a class="dropdown-item" href="{{ route('manager.remisiones.lista') }}">
			{{ __('Lista') }}
		</a>
		<a class="dropdown-item" href="{{ route('manager.remisiones.pago_devolucion') }}">
			{{ __('Devoluciones / Cerrar') }}
		</a>
		<a class="dropdown-item" href="{{ route('manager.remisiones.fecha_adeudo') }}">
			{{ __('Fecha de adeudos') }}
		</a>
	</div>
</li>
<li class="nav-item dropdown">
	<a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
		Movimientos <span class="caret"></span>
	</a>
	<div class="dropdown-menu dropdown-menu-left" aria-labelledby="navbarDropdown">
		<a class="dropdown-item" href="{{ route('manager.movimientos.clientes') }}">
			{{ __("Clientes") }}
		</a>
		<a class="dropdown-item" href="{{ route('manager.movimientos.libros') }}">
			{{ __("Libros") }}
		</a>
		<a class="dropdown-item" href="{{ route('manager.movimientos.entradas-salidas') }}">
			{{ __("Entradas / Salidas") }}
		</a>
	</div>
</li>
<li class="nav-item dropdown">
	<a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
		Cortes <span class="caret"></span>
	</a>
	<div class="dropdown-menu dropdown-menu-left" aria-labelledby="navbarDropdown">
		<a class="dropdown-item" href="{{ route('manager.cortes.lista') }}">
			{{ __("Lista") }}
		</a>
		<a class="dropdown-item" href="{{ route('manager.cortes.pagos') }}">
			{{ __("Pagos") }}
		</a>
	</div>
</li>
<li class="nav-item dropdown">
	<a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
		Historial <span class="caret"></span>
	</a>
	<div class="dropdown-menu dropdown-menu-left" aria-labelledby="navbarDropdown">
		<a class="dropdown-item" href="{{ route('historial.crear_remision') }}">
			{{ __("Crear remision") }}
		</a>
		
	</div>
</li>
<li class="nav-item dropdown">
	<a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
		Entradas <span class="caret"></span>
	</a>
	<div class="dropdown-menu dropdown-menu-left" aria-labelledby="navbarDropdown">
		<a class="dropdown-item" href="{{ route('manager.entradas.lista') }}">
			{{ __("Lista") }}
		</a>
		<a class="dropdown-item" href="{{ route('manager.entradas.pagos') }}">
			{{ __("Pagos") }}
		</a>
	</div>
</li>
<li>
	<a class="nav-link" href="{{ route('manager.salidas') }}">{{ __("Salidas") }}</a>
</li>
<li class="nav-item dropdown">
	<a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
		Libros <span class="caret"></span>
	</a>
	<div class="dropdown-menu dropdown-menu-left" aria-labelledby="navbarDropdown">
		<a class="dropdown-item" href="{{ route('manager.libros') }}">{{ __("Lista") }}</a>
		<a class="dropdown-item" href="{{ route('manager.codes') }}">{{ __("Códigos") }}</a>
	</div>
</li>
<li>
	<a class="nav-link" href="{{ route('manager.clientes') }}">{{ __("Clientes") }}</a>
</li>
<li class="nav-item dropdown">
	<a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
		Otros <span class="caret"></span>
	</a>
	<div class="dropdown-menu dropdown-menu-left" aria-labelledby="navbarDropdown">
		<a class="dropdown-item" href="{{ route('manager.otros.notas') }}">{{ __("Notas") }}</a>
		<a class="dropdown-item" href="{{ route('manager.otros.pedidos') }}">{{ __("Pedidos") }}</a>
		<a class="dropdown-item" href="{{ route('manager.otros.promociones') }}">{{ __("Promociones") }}</a>
		<a class="dropdown-item" href="{{ route('manager.otros.donaciones') }}">{{ __("Donaciones") }}</a>
	</div>
</li>
<li>
	<a class="nav-link" href="{{ route('information.majestic') }}" target="_blank">{{ __("MAJESTIC") }}</a>
</li>	
@include('partials.navigations.logged')