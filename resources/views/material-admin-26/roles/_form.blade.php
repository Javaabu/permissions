@include('permissions::material-admin-26.roles.form._general')

<x-permissions::permission-list :role="$role" :permissions="$permissions" />

<div class="button-group">
    <button class="btn btn-success btn--icon-text btn--raised">
        <i class="zmdi zmdi-check"></i> {{ __('Save') }}
    </button>

    <a class="btn btn-light btn--icon-text" href="{{ $cancel_redirect_url }}">
        <i class="zmdi zmdi-close-circle"></i> {{ __('Cancel') }}
    </a>
</div>

@include('permissions::material-admin-26.components.floating-submit')
