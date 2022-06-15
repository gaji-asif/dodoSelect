<x-app-layout>
    @section('title', 'Dropshipper Assign Permission')

    @push('top_css')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
        <link type="text/css" href="https://cdn.jsdelivr.net/npm/jquery-datatables-checkboxes@1.2.12/css/dataTables.checkboxes.css" rel="stylesheet" />
    @endpush

    @if (in_array('Can access menu: Dropshippers - Role Management', session('assignedPermissions')))
        <x-card class="mt-0">
            <div class="" style="margin-top: -2rem">
                @include('dropshipper.menu')
            </div>
            <hr>

            <card class="bg-gray-500 ">
                <div class="card-title my-4">
                    <h4><strong>Role {{ $role->name }} - Assign Permission ({{ isset($selectedPermissionCount) ? number_format($selectedPermissionCount) : 0 }})</strong></h4>
                </div>
                <div class="mt-6">
                    @if (session('success'))
                        <x-alert-success>
                            {{ session('success') }}
                        </x-alert-success>
                    @endif

                    @if (session('danger'))
                        <x-alert-danger>
                            {{ session('danger') }}
                        </x-alert-danger>
                    @endif

                    @if (session('error'))
                        <x-alert-danger>
                            {{ session('error') }}
                        </x-alert-danger>
                    @endif

                    <x-alert-success class="mb-6 alert hidden" id="__alertSuccess">
                        <div id="__alertSuccessContent"></div>
                    </x-alert-success>

                    <x-alert-danger class="mb-6 alert hidden" id="__alertDanger">
                        <div id="__alertDangerContent"></div>
                    </x-alert-danger>

                    @if ($errors->any())
                        <x-alert-danger>
                            <ul class="mt-3 list-disc list-inside text-sm text-red-600">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </x-alert-danger>
                    @endif

                    <div class="flex flex-col sm:flex-row sm:justify-between md:flex-row items-center justify-between mb-4">
                        <div class="w-full md:w-3/4 lg:w-3/4 xl:w-2/3 mb-4">
                            <div class="w-full flex flex-col sm:flex-row sm:justify-between">
                                <x-select name="parent_category" id="__selectParentCategoryFilter" class="category mr-2">
                                    <option value="" selected disabled>
                                        {{ '- ' . __('translation.Select Product Category') . ' -' }}
                                    </option>
                                    @if (isset($categories))
                                        @foreach ($categories as $cateroy)
                                            <option value="{{$cateroy->id}}">{{$cateroy->cat_name}}</option>
                                        @endforeach
                                    @endif
                                </x-select>

                                <x-select name="category" id="__selectCategoryFilter" class="category">
                                    <option value="" selected disabled>
                                        {{ '- ' . __('translation.Select Sub Category') . ' -' }}
                                    </option>
                                    @if (isset($sub_categories))
                                        @foreach ($sub_categories as $cateroy)
                                            <option value="{{$cateroy->id}}">{{$cateroy->cat_name}}</option>
                                        @endforeach
                                    @endif
                                </x-select>
                            </div>
                        </div>

                        <div class="w-full md:w-1/4 lg:w-1/4 xl:w-1/3 flex items-center justify-end lg:justify-start lg:ml-2">
                            <x-button type="button" color="yellow" class="relative -top-1 order-last md:order-first mx-1" id="__btnSubmitFilter">
                                {{ __('translation.Search') }}
                            </x-button>
                            <x-button type="button" color="grey" class="relative -top-1 reset-filter" id="__btnResetFilter">
                                {{ __('translation.Reset') }}
                            </x-button>
                        </div>
                    </div>

                    <div class="mb-8 md:mb-2">
                        <div class="flex flex-col sm:flex-row sm:justify-between items-start mb-4">
                            <div class="w-full md:w-1/2 lg:w-2/3 mb-6 sm:mb-0">
                                <div class="flex flex-row">
                                    <div class="w-full md:w-4/5 lg:w-2/5 xl:w-3/5 xl:ml-1 relative -top-1">
                                        <x-select class="text-sm" id="__sortByToolbar">
                                            <option disabled>
                                                - {{ __('translation.Sort by') }} -
                                            </option>
                                            <option value="all" selected>
                                                {{ __('translation.All') }} ({{ isset($totalPermissionCount) ? $totalPermissionCount : 0 }})
                                            </option>
                                            <option value="selected">
                                                {{ __('translation.Selected') }} ({{ isset($selectedPermissionCount) ? $selectedPermissionCount : 0 }})
                                            </option>
                                            <option value="unselected">
                                                {{ __('translation.Unselected') }} ({{ isset($unselectedPermissionCount) ? $unselectedPermissionCount : 0 }})
                                            </option>
                                        </x-select>
                                    </div>
                                </div>
                            </div>
                            <div class="w-full md:w-1/2 lg:w-1/3 flex flex-row sm:justify-end">
                                <x-button color="green" id="assign-btn">
                                    Assign Permission
                                </x-button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="w-full overflow-x-auto">
                    <table class="w-full table" id="datatable">
                        <thead>
                        <tr class="rounded-lg text-sm font-medium text-gray-700 text-left">
                            <th></th>
                            <th class="px-4 py-2 text-center">
                                {{ __('translation.Image') }}
                            </th>
                            <th class="px-4 py-2 text-center">
                                {{ __('translation.Product Name') }}
                            </th>
                            <th class="px-4 py-2 text-center">
                                {{ __('translation.Price') }}
                            </th>
                            <th class="px-4 py-2 text-center">
                                {{ __('translation.Dropship Price') }}
                            </th>
                            <th class="px-4 py-2 text-center">
                                {{ __('translation.Quantity') }}
                            </th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </card>
        </x-card>
    @endif

    @push('bottom_js')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery-datatables-checkboxes@1.2.12/js/dataTables.checkboxes.min.js"></script>

        <script>
            const roleId = {{ $role->id }};
            const permissionTableUrl = '{{ route('dropshipper.assign_permission.role_datatable') }}';
            var permissionTable = '';

            const loadPermissionTable = (categoryId, sortBySelected) => {
                permissionTable = $('#datatable').DataTable({
                    serverSide: true,
                    processing: true,
                    bDestroy: true,
                    pageLength: 10,
                    pagingType: 'numbers',
                    ajax: {
                        type: 'GET',
                        url: permissionTableUrl,
                        data: {
                            roleId: roleId,
                            categoryId: categoryId,
                            sortBySelected: sortBySelected
                        }
                    },
                    columnDefs: [
                        {
                            targets: [0],
                            orderable: false,

                            render: function(data, type, row, meta) {
                                data = '<input type="checkbox" class="dt-checkboxes permission" name="permission" id="'+row[0]+'">'
                                if (row[2].includes('checked')) {
                                    data = '<input type="checkbox" class="dt-checkboxes permission" name="permission" id="'+row[0]+'" checked>';
                                }
                                return data;
                            },
                            checkboxes: {
                                selectRow: true,
                            },
                        },
                        {
                            targets: [1],
                            orderable: false,
                        }
                    ],
                    select: {
                        style: 'multiple',
                    },
                    bDeferRender: true,
                    order: [
                        [ 1, 'desc' ]
                    ],
                });

            }

            loadPermissionTable();


            $('#__sortByToolbar').on('change', function() {
                let categoryId = $('#__selectCategoryFilter').val();
                let sortBySelected = $(this).val();

                loadPermissionTable(categoryId, sortBySelected);
            });


            $(document).ready(function() {
                $('#__selectParentCategoryFilter').select2({
                    placeholder: '- Select Product Category -',
                    allowClear: true
                });

                $('#__selectCategoryFilter').select2({
                    placeholder: '- Select Sub Category -',
                    allowClear: true
                });

                $('#__selectParentCategoryFilter').val('').trigger('change');
                $('#__selectCategoryFilter').val('').trigger('change');
            });

            $('#__btnSubmitFilter').click(function() {
                let parentCategoryId = $('#__selectParentCategoryFilter').val();
                let categoryId = $('#__selectCategoryFilter').val();

                $('#__sortByToolbar').val('all');

                if (parentCategoryId != '' && categoryId == 0)
                    alert('Please select a sub category to filter');
                else
                    loadPermissionTable(categoryId);
            });

            function loadSubCategoryAfterReset(){
                $.ajax({
                    url: "{{route('get all sub categories')}}",
                    type: "GET",
                    dataType: 'json',
                    success: function (result) {
                        $('#__selectCategoryFilter').html('<option disabled selected value="0" style="background-color: #999">- Select Sub Category -</option>');

                        $.each(result.sub_categories, function (key, value) {
                            $("#__selectCategoryFilter").append('<option value="' + value
                                .id + '">' + value.cat_name + '</option>');
                        });
                    }
                });
            }

            $('#__btnResetFilter').on('click',function() {
                loadPermissionTable();

                $('#__sortByToolbar').val('all');
                $('#__selectParentCategoryFilter').val('').trigger('change');
                $('#__selectCategoryFilter').val('').trigger('change');
                loadSubCategoryAfterReset();
            });

            $(document).ready(function () {
                $('#__selectParentCategoryFilter').on('change', function () {
                    var idParent = this.value;
                    $("#__selectCategoryFilter").html('');

                    $.ajax({
                        url: "{{route('fetch sub categories')}}",
                        type: "POST",
                        data: {
                            parent_category_id: idParent,
                            _token: '{{csrf_token()}}'
                        },
                        dataType: 'json',
                        success: function (result) {
                            $('#__selectCategoryFilter').html('<option value="">Select Sub Category</option>');
                            $.each(result.sub_categories, function (key, value) {
                                $("#__selectCategoryFilter").append('<option value="' + value
                                    .id + '">' + value.cat_name + '</option>');
                            });
                        }
                    });
                });
            });


            $('#assign-btn').on('click',function() {
                let drop = confirm('Confirm assigning permission?');

                if (drop) {
                    location.reload();
                }
            });


            function attachPermission(permission){
                $.ajax({
                    url: '{{ route('dropshipper.assign_permission.role_save') }}',
                    type: 'post',
                    data: {
                        'roleId': roleId,
                        'permission': permission,
                        'action': 'attach',
                        '_token': $('meta[name=csrf-token]').attr('content')
                    },
                }).done(function(result) {
                    if (result.status === 1) {
                        // alert('Permission assigned');
                    } else {
                        alert(result.message);
                    }
                });
            }

            function detachPermission(permission){
                $.ajax({
                    url: '{{ route('dropshipper.assign_permission.role_save') }}',
                    type: 'post',
                    data: {
                        'roleId': roleId,
                        'permission': permission,
                        'action': 'detach',
                        '_token': $('meta[name=csrf-token]').attr('content')
                    },
                }).done(function(result) {
                    if (result.status === 1) {
                        // alert('Permission removed');
                    } else {
                        alert(result.message);
                    }
                });
            }

            $(document).on('change', 'input.permission', function(e){
                var target = e.target;
                var permission = target.id;
                if($(target).is(":checked")){
                    attachPermission(permission);
                }else{
                    detachPermission(permission);
                }
            });

            $(document).on('change', '.dt-checkboxes-select-all', function(e){
                var target = e.target;
                var permission = [];
                if($(target).is(":checked")){
                    $("input:checkbox[name=permission]:checked").each(function(){
                        permission.push($(this).attr('id'));
                    });
                    attachPermission(permission);
                }else{
                    $("input:checkbox[name=permission]").each(function(){
                        permission.push($(this).attr('id'));
                    });
                    detachPermission(permission);
                }
            });

        </script>
    @endpush
</x-app-layout>
