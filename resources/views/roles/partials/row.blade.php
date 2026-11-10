@php($user = auth()->user())
<x-forms::table.row :model="$role" name="roles" no-checkbox data-role-row="{{ $role->getKey() }}">
    <x-forms::table.cell name="name" :label="__('Name')">
        @if($user?->can('view', $role))
            <a href="{{ route($routes['show'], $role) }}" class="font-medium hover:underline" dir="ltr">{{ $role->name }}</a>
        @else
            <span class="font-medium" dir="ltr">{{ $role->name }}</span>
        @endif
    </x-forms::table.cell>
    <x-forms::table.cell name="description" :label="__('Description')">
        <span dir="auto">{{ $role->translated_description }}</span>
    </x-forms::table.cell>
    <x-forms::table.cell name="permissions_count" :label="__('Permissions')">
        <span class="tabular-nums">{{ __(':count permissions', ['count' => $role->permissions_count ?? $role->permissions()->count()]) }}</span>
    </x-forms::table.cell>
    <x-forms::table.cell name="actions" :label="__('Actions')">
        @include('permissions::roles.partials.actions', ['role' => $role, 'show_view' => false])
    </x-forms::table.cell>
</x-forms::table.row>
