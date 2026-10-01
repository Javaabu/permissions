@php
    use Illuminate\Support\Str;
    $permissions = $permissions->groupBy('model');
@endphp

<div>
    <div class="card">
        <div class="card-body">
            <div class="d-flex card-title align-items-center justify-content-between">
                <x-forms::checkbox name="select-all-resp" value="1" data-all="" inline :show-label="false">
                    {{ __('All Permissions') }}
                </x-forms::checkbox>

                <div class="w-50">
                    <div class="d-flex flex-row align-items-center justify-content-end position-relative">
                        <div class="w-100">
                            <x-forms::text
                                name="permission_search"
                                placeholder="{{ __('Search permissions...') }}"
                                inline
                                :show-label="false"
                                autocomplete="off"
                                data-permission-search/>
                        </div>

                        <i class="zmdi zmdi-search position-absolute mr-3" aria-hidden="true"></i>
                    </div>
                </div>
            </div>

            @include('permissions::material-admin-26.errors._list', ['error' => $errors->get('permissions')])
        </div>
    </div>

    <div>
        @foreach($permissions as $model => $model_permissions)
            @php
                $model_slug = Str::slug($model);
            @endphp

            <div class="accordion w-100 bg-white permission-card mb-4"
                 data-search="{{ Str::lower($model.' '.$model_permissions->pluck('description')->join(' ')) }}">
                <div class="accordion__item w-100">
                    <div class="accordion__title d-flex align-items-center justify-content-between p-4"
                         data-toggle="collapse" data-target="#permission-group-{{ $loop->index }}"
                         aria-expanded="false" aria-controls="permission-group-{{ $loop->index }}"
                         aria-label="{{ __('Toggle :model permissions', ['model' => __(slug_to_title($model))]) }}">
                        <div class="checkbox">
                            <x-forms::checkbox name="{{ $model_slug.'-select-all-resp' }}" data-all="{{ $model_slug }}"
                                               inline :show-label="false">
                                {{ __(slug_to_title($model)) }}
                            </x-forms::checkbox>
                        </div>

                        <button class="border-0 bg-transparent" type="button"
                                data-toggle="collapse" data-target="#permission-group-{{ $loop->index }}"
                                aria-expanded="false" aria-controls="permission-group-{{ $loop->index }}"
                                aria-label="{{ __('Toggle :model permissions', ['model' => __(slug_to_title($model))]) }}">
                            <i class="zmdi zmdi-chevron-down" aria-hidden="true"></i>
                        </button>
                    </div>

                    <div id="permission-group-{{ $loop->index }}" class="collapse">
                        <div class="accordion__content border-top">
                            @foreach($model_permissions as $permission)
                                @php
                                    $checked = (isset($role) && $role->hasPermissionTo($permission)) || safe_in_array($permission->id, old('permissions'));
                                @endphp

                                <x-forms::checkbox
                                    name="permissions[]"
                                    id="{{ 'permission-'.$permission->id }}"
                                    :value="$permission->id"
                                    :checked="$checked"
                                    data-all="{{ $model_slug }}"
                                    inline
                                    :show-label="false"
                                >
                                    {{ $permission->description }}
                                </x-forms::checkbox>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

@push('scripts')
    <script>
        $(document).ready(function () {
            const $cards = $('.permission-card');
            $cards.each(function (index, cardElem) {
                const $card = $(cardElem);

                updateAllCardPermissionsCheckbox($card);
            });
            updateAllPermissionsCheckbox();

            // When all permissions is checked
            $(document).on('change', '#select-all-resp', function () {
                const isChecked = $(this).prop('checked');
                $('input[data-all]').prop('checked', isChecked); // All group level checkboxes
                $('input[name="permissions[]"]').prop('checked', isChecked); // All individual permissions
            });

            // When group level checkbox is checked (and not the all permissions checkbox)
            // $= means ends with
            $(document).on('change', 'input[name$="-select-all-resp"]', function () {
                const isChecked = $(this).prop('checked');
                const $card = $(this).closest('.permission-card');
                $card.find('input[name="permissions[]"]').prop('checked', isChecked);

                updateAllPermissionsCheckbox();
            });

            // When individual permission checkbox is checked
            $(document).on('change', 'input[name="permissions[]"]', function () {
                const $card = $(this).closest('.permission-card');

                updateAllCardPermissionsCheckbox($card);

                updateAllPermissionsCheckbox();
            });

            function updateAllCardPermissionsCheckbox($card) {
                $card.find('input[name$="-select-all-resp"').each(function () {
                    const allCardPermissions = $card.find('input[name="permissions[]"]');
                    const checkedCardPermissions = allCardPermissions.filter(':checked');

                    if (allCardPermissions.length === checkedCardPermissions.length) {
                        $(this).prop('checked', true);
                    } else {
                        $(this).prop('checked', false);
                    }
                });
            }

            function updateAllPermissionsCheckbox() {
                const allPermissions = $('input[name="permissions[]"]');
                const checkedPermissions = allPermissions.filter(':checked');

                if (allPermissions.length === checkedPermissions.length) {
                    $('#select-all-resp').prop('checked', true);
                } else {
                    $('#select-all-resp').prop('checked', false);
                }
            }

            $(document).on('input', '[data-permission-search]', function () {
                const query = $(this).val().trim().toLowerCase();

                $('.permission-card').each(function () {
                    const matches = $(this).data('search').includes(query);

                    $(this).toggleClass('d-none', !matches);
                    $(this).toggleClass('d-flex', matches);
                });
            });

            // Prevent the checkbox click from toggling the accordion
            $('.accordion__title .checkbox').on('click', function (event) {
                event.stopPropagation();
            });

            $('.permission-card .collapse').collapse('show');
        });
    </script>
@endpush
