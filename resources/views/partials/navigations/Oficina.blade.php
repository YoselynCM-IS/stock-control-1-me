<li class="nav-item dropdown">
	<a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
		Remisiones <span class="caret"></span>
	</a>
	<div class="dropdown-menu dropdown-menu-left" aria-labelledby="navbarDropdown">
		<a class="dropdown-item" href="{{ route('oficina.remisiones') }}">
			{{ __('Remisiones') }}
		</a>
		<a class="dropdown-item" href="{{ route('oficina.cerrar') }}">
			{{ __('Devoluciones / Cerrar') }}
		</a>
		<a class="dropdown-item" href="{{ route('oficina.fecha-adeudo') }}">
			{{ __('Fecha de adeudos') }}
		</a>
	</div>
</li>
<li>
	<a class="nav-link" href="{{ route('oficina.pagos') }}">{{ __("Pagos") }}</a>
</li>
<li>
	<a class="nav-link" href="{{ route('oficina.pedidos') }}">{{ __("Pedidos") }}</a>
</li>
<li class="nav-item dropdown">
	<a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
		Entradas <span class="caret"></span>
	</a>
	<div class="dropdown-menu dropdown-menu-left" aria-labelledby="navbarDropdown">
		<a class="dropdown-item" href="{{ route('oficina.entradas') }}">
			{{ __('Lista') }}
		</a>
		<a class="dropdown-item" href="{{ route('oficina.entradas.pagos') }}">
			{{ __('Pagos') }}
		</a>
	</div>
</li>
<li>
	<a class="nav-link" href="{{ route('oficina.salidas') }}">{{ __("Salidas") }}</a>
</li>
<li class="nav-item dropdown">
	<a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
		Libros <span class="caret"></span>
	</a>
	<div class="dropdown-menu dropdown-menu-left" aria-labelledby="navbarDropdown">
		<a class="dropdown-item" href="{{ route('oficina.libros') }}">{{ __("Lista") }}</a>
		<a class="dropdown-item" href="{{ route('oficina.codes') }}">{{ __("Códigos") }}</a>
	</div>
</li>
<li>
	<a class="nav-link" href="{{ route('oficina.clientes') }}">{{ __("Clientes") }}</a>
</li>
<li class="nav-item dropdown">
	<a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
		Otros <span class="caret"></span>
	</a>
	<div class="dropdown-menu dropdown-menu-left" aria-labelledby="navbarDropdown">
		<a class="dropdown-item" href="{{ route('oficina.promociones') }}">
			{{ __('Promociones') }}
		</a>
		<a class="dropdown-item" href="{{ route('oficina.donaciones') }}">
			{{ __('Donaciones') }}
		</a>
		<a class="dropdown-item" href="{{ route('oficina.entradas-salidas') }}">
			{{ __("Entradas / Salidas") }}
		</a>
	</div>
</li>
<li>
	<a class="nav-link" href="{{ route('information.majestic') }}" target="_blank">{{ __("MAJESTIC") }}</a>
</li>
@include('partials.navigations.logged')