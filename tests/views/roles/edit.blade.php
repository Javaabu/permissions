<h1>{{ $title }}</h1>{{ $guard }}|{{ $routes['index'] }}
{{ $role->name }}@foreach($permission_groups as $group)<h2>{{ $group['label'] }}</h2>@foreach($group['permissions'] as $permission)<span @class(['granted' => $permission['granted']])>{{ $permission['description'] }}</span>@endforeach @endforeach
