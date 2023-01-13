<li>
	<a class="nav-link" href="{{ route('information.actividades.lista') }}">{{ __("Actividades") }}</a>
</li>
<li>
	<a class="nav-link" href="{{ route('information.pedidos.cliente') }}">{{ __("Pedidos") }}</a>
</li>
<li>
	<a class="nav-link" href="{{ route('information.clientes.lista') }}">{{ __("Clientes") }}</a>
</li>
<user-notifications :user_id="{{auth()->user()->id}}" :noleidos="{{Auth::user()->unreadNotifications}}"></user-notifications>
@include('partials.navigations.logged')