@extends(config('permissions.layout', 'layouts.app'))

@section(config('permissions.layout_section', 'content'))
    @manifestRequire('permissions::roles')

    <div class="flex flex-col gap-6" data-roles>
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-2xl font-semibold">{{ $title }}</h1>
            <x-forms::link-button :url="$urls['index']" color="secondary" size="sm" start-icon="chevron-left">
                {{ __('Back to roles') }}
            </x-forms::link-button>
        </div>

        @include('permissions::roles.partials.alerts')

        <x-forms::form method="POST" :action="$urls['store']" class="flex flex-col gap-6" data-role-form summary>
            @include('permissions::roles.partials.form', ['role' => null])

            <div class="flex flex-wrap items-center gap-2">
                <x-forms::submit start-icon="check">{{ __('Create role') }}</x-forms::submit>
            </div>
        </x-forms::form>
    </div>
@endsection
