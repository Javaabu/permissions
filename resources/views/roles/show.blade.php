@extends(config('permissions.layout', 'layouts.app'))

@section(config('permissions.layout_section', 'content'))
    @manifestRequire('permissions::roles')

    <div class="flex flex-col gap-6" data-roles>
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-2xl font-semibold" dir="auto">{{ $title }}</h1>
            <x-forms::link-button :url="$urls['index']" color="secondary" size="sm" start-icon="chevron-left">
                {{ __('Back to roles') }}
            </x-forms::link-button>
        </div>

        @include('permissions::roles.partials.alerts')

        <x-forms::card :title="__('Details')">
            <dl class="grid gap-4 sm:grid-cols-3">
                <div>
                    <dt class="text-sm text-muted-foreground">{{ __('Name') }}</dt>
                    <dd dir="ltr" class="text-start">{{ $role->name }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-muted-foreground">{{ __('Description') }}</dt>
                    <dd dir="auto">{{ $role->translated_description }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-muted-foreground">{{ __('Guard') }}</dt>
                    <dd dir="ltr" class="text-start">{{ $guard }}</dd>
                </div>
            </dl>

            <x-slot:footer>
                @include('permissions::roles.partials.actions', ['role' => $role])
            </x-slot:footer>
        </x-forms::card>

        <section class="flex flex-col gap-4" data-permission-groups>
            <h2 class="text-lg font-semibold">{{ __('Permissions') }}</h2>

            @forelse($permission_groups as $group)
                <x-forms::card :title="$group['label']" data-permission-group="{{ $group['name'] }}">
                    <ul class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($group['permissions'] as $permission)
                            <li class="flex items-center gap-2" title="{{ $permission['name'] }}">
                                <x-forms::icon name="check" class="size-4 shrink-0 text-success" />
                                <span>{{ $permission['description'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </x-forms::card>
            @empty
                <p class="text-muted-foreground">{{ __('This role has no permissions.') }}</p>
            @endforelse
        </section>
    </div>
@endsection
