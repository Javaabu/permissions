<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <x-forms::text name="description" :placeholder="__('Description')" required />
            </div>

            <div class="col-md-4">
                <x-forms::text name="name" :placeholder="__('Description')" required />
            </div>
        </div>
    </div>
</div>


@push('scripts')
    <script type="text/javascript">
        $(document).ready(function() {
            //slugify
            $("#name").keypress(function (e) {
                return restrictCharacters($(this), e, /[A-Za-z0-9_]/g);
            });

            @unless( isset($role) )
            $('#description').keyup(function () {
                var str = $(this).val();
                $('#name').val(slugify(str, '_'));
            });
            @endunless
        });
    </script>
@endpush
