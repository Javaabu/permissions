@php($user = auth()->user())
<div class="flex flex-wrap items-center gap-2" data-role-actions>
    @if(($show_view ?? false) && $user?->can('view', $role))
        <x-forms::link-button :url="route($routes['show'], $role)" color="secondary" size="sm" start-icon="eye">
            {{ __('View') }}
        </x-forms::link-button>
    @endif

    @if(($show_edit ?? true) && $user?->can('update', $role))
        <x-forms::link-button :url="route($routes['edit'], $role)" color="secondary" size="sm" start-icon="pencil">
            {{ __('Edit') }}
        </x-forms::link-button>
    @endif

    @if($user?->can('delete', $role))
        <x-forms::form method="DELETE" :action="route($routes['destroy'], $role)" data-confirm="{{ __('Delete this role? Users with it lose its permissions.') }}">
            <x-forms::submit color="danger" size="sm" start-icon="trash-2" :animate="false">{{ __('Delete') }}</x-forms::submit>
        </x-forms::form>
    @endif
</div>
