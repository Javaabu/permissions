<h1>{{ $title }}</h1>{{ $guard }}|{{ $routes['index'] }}
@foreach($roles as $role)<li>{{ $role->translated_description }}</li>@endforeach
