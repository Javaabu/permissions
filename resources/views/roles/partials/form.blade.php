{{-- The fields of the create and edit pages; $role is null on the create page --}}
<x-forms::card :title="__('Details')">
    <div class="grid gap-4 md:grid-cols-2">
        <x-forms::text
            name="name"
            :label="__('Name')"
            required
            maxlength="255"
            dir="ltr"
            autocomplete="off">
            <x-slot:help>{{ __('Letters, numbers, dashes and underscores, e.g. content_editor') }}</x-slot:help>
        </x-forms::text>

        @if($translations)
            {{-- In its own view, so it only compiles when javaabu/translatable is installed --}}
            @include('permissions::roles.partials.translatable-description')
        @else
            <x-forms::text name="description" :label="__('Description')" required maxlength="255" dir="auto" />
        @endif
    </div>
</x-forms::card>

<section class="flex flex-col gap-4" data-permission-groups>
    <h2 class="text-lg font-semibold">{{ __('Permissions') }}</h2>

    @foreach($permission_groups as $group)
        <x-forms::card data-permission-group="{{ $group['name'] }}">
            <x-slot:title>{{ $group['label'] }}</x-slot:title>
            <x-slot:header class="flex flex-wrap items-center justify-end gap-2">
                <x-forms::button type="button" color="secondary" size="sm" data-permission-group-select="all">{{ __('Select all') }}</x-forms::button>
                <x-forms::button type="button" color="secondary" size="sm" data-permission-group-select="none">{{ __('Select none') }}</x-forms::button>
            </x-slot:header>

            <div class="grid gap-x-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($group['permissions'] as $permission)
                    <x-forms::checkbox
                        name="permissions[]"
                        :id="'permission-' . $permission['id']"
                        :value="$permission['id']"
                        :label="$permission['description']"
                        :model="false"
                        :default="$permission['granted']"
                        :show-errors="false"
                        title="{{ $permission['name'] }}" />
                @endforeach
            </div>
        </x-forms::card>
    @endforeach

    <x-forms::errors name="permissions" />
</section>
