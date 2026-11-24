@extends(config('permissions.layout', 'layouts.app'))

@section(config('permissions.layout_section', 'content'))
    @manifestRequire('permissions::roles')

    <div class="flex flex-col gap-6" data-roles>
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-2xl font-semibold">{{ $title }}</h1>

            @if($can['create'])
                <x-forms::link-button :url="$urls['create']" start-icon="plus">
                    {{ __('New role') }}
                </x-forms::link-button>
            @endif
        </div>

        @include('permissions::roles.partials.alerts')

        <x-forms::search-form :action="$urls['index']" :placeholder="__('Search roles')" />

        <x-forms::table model="roles" no-checkbox>
            <x-slot:headers>
                <x-forms::table.heading :label="__('Name')" />
                <x-forms::table.heading :label="__('Description')" />
                <x-forms::table.heading :label="__('Permissions')" />
                <x-forms::table.heading :label="__('Actions')" />
            </x-slot:headers>

            <x-slot:rows>
                @forelse($roles as $role)
                    @include('permissions::roles.partials.row', ['role' => $role])
                @empty
                    <x-forms::table.empty-row :columns="4" no-checkbox>
                        @if($search)
                            <p class="font-medium">{{ __('No roles match your search.') }}</p>
                        @else
                            <p class="font-medium">{{ __('No roles yet') }}</p>
                            <p class="text-sm text-muted-foreground">{{ __('Roles you create will appear here.') }}</p>
                        @endif
                    </x-forms::table.empty-row>
                @endforelse
            </x-slot:rows>

            @if($roles->hasPages())
                <x-slot:pagination>
                    <x-forms::pagination :paginator="$roles" />
                </x-slot:pagination>
            @endif
        </x-forms::table>
    </div>
@endsection
