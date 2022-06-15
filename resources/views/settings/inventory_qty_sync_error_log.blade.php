<x-app-layout>
    @section('title', 'Category')

    @push('top_css')
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>


	<link href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">
        <link href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css" rel="stylesheet">
        <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>

        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">

        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">
        <style type="text/css">

        tr.parent td {
            background-color: #5384d52b;
            box-shadow: inset -1px -18px 20px 20px #bdc3ce45;
            font-weight: bold;
            font-size: 20px;
        }


        .subcategory {width: 70% !important;     box-shadow: none !important;}
        .subcategory td{    background: #fff;}
        .subcategory td:first-child{padding-left: 5rem !important;}
        </style>
    @endpush

    @if(session('roleName') != 'dropshipper')
    <x-card class="mt-0">
        <div class="" style="margin-top: -2rem">
            @include('settings.menu')
        </div>
        <hr>

        <card class="bg-gray-500 ">
            <div class="card-title my-4">
                <h4><strong>Inventory Qty Sync Error Log @if (isset($totalErrorLog)) ({{$totalErrorLog}}) @endif</strong></h4>
                <h4></h4>
            </div>
            <div class="mt-6 row">
                @if(session('success'))
                    <x-alert-success>{{ session('success') }}</x-alert-success>
                @endif
                @if (session()->has('error'))
                    <x-alert-danger>{{ session('error') }}</x-alert-danger>
                @endif
                @if ($errors->any())
                    <x-alert-danger>
                        <ul class="mt-3 list-disc list-inside text-sm text-red-600">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </x-alert-danger>
                @endif
            </div>

            <div class="row col-md-12">
                <div class="lg:w-1/4 mb-6 lg:mb-3">
                    <x-button color="red" id="BtnInsert" data-toggle="modal" data-target="#deleteAllErrorLogModal">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="ml-2">
                            {{ __('translation.Delete Error Logs') }}
                        </span>
                    </x-button>
                </div>
            </div>

            <div class="modal fade" id="deleteAllErrorLogModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form method="POST" action="{{route('store sub category')}}" id="form-create" enctype="multipart/form-data">
                            <div class="modal-header">
                                <h3 class="modal-title font-bold text-lg">
                                    Delete Error Logs
                                </h3>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true" class="text-xl">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div>
                                    <p>Are you sure you want to delete all the error logs?</p>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn bg-gray-500 text-white" data-dismiss="modal">Close</button>
                                <button type="submit" id="remove-all-error_logs-btn" class="btn btn-danger">Delete</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="flex justify-between flex-col">
                <div class="w-full overflow-x-auto">
                    <table class="w-full" id="__quantityLogTable">
                        <thead class="bg-blue-500">
                            <tr class="rounded-lg text-sm font-medium text-gray-700 text-left">
                                <th class="px-4 py-2"></th>
                                <th class="px-4 py-2">
                                    {{ __('translation.Product') }}
                                </th>
                                <th class="px-4 py-2">
                                    {{ __('translation.Quantity') }}
                                </th>
                                <th class="px-4 py-2">
                                    {{ __('translation.Failed Reason') }}
                                </th>
                                <th class="px-4 py-2">
                                    {{ __('translation.Platform') }}
                                </th>
                                <th class="px-4 py-2">
                                    {{ __('translation.Shop') }}
                                </th>
                                <th class="px-4 py-2">
                                    {{ __('translation.Created At') }}
                                </th>
                                <th class="px-4 py-2">
                                    {{ __('translation.Actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </card>
    </x-card>
    @endif

    @push('bottom_js')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery-datatables-checkboxes@1.2.12/js/dataTables.checkboxes.min.js"></script>

        <script type="text/javascript">
            const dataTableUrl = "{{ route('inventory_qty_sync_error_log_index.datatable') }}";
            const errorLogDeleteUrl = "{{ route('inventory_qty_sync_error_log_index.delete') }}";
            const errorLogDeleteAllUrl = "{{ route('inventory_qty_sync_error_log_index.delete_all') }}";

            var selectedRows = [];

            var quantityLogTable = $('#__quantityLogTable').DataTable({
                serverSide: true,
                processing: true,
                ajax: {
                    type: 'GET',
                    url: dataTableUrl,
                    data: {
                    }
                },
                columnDefs: [
                    {
                        targets: [0],
                        orderable: false,
                        checkboxes: {
                            selectRow: true
                        }
                    },
                    {
                        targets: [1],
                        orderable: false,
                        render: function (data, type) {
                            let url = '{{ route("product.inventory_sync", ["id" => ":id"]) }}';
                            url = url.replace(':id', data.dodo_product_id);
                            return '<a href="'+url+'" target="__blank">'+data.product_name+'</a>';
                        }
                    },
                    {
                        targets: [2,3,4,5,6],
                        orderable: false
                    },
                    {
                        targets: [7],
                        orderable: false,
                        render: function (data, type) {
                            return '<a class="btn btn-sm btn-danger btn-delete-error-log" data-id='+data.id+'" title="Delete error log"><i class="fas fa-trash"></i></a>';
                        }
                    },
                ],
                paginationType: 'numbers',
                select: {
                    style: 'multiple'
                }
            });


            $(document).on('click', '.btn-delete-error-log', function(e) {
                e.preventDefault();
                let drop = confirm('Are you sure?');
                if (drop) {
                    $(this).closest('tr').remove();

                    $.ajax({
                        url: errorLogDeleteUrl,
                        type: 'post',
                        data: {
                            'id': $(this).data('id'),
                            '_token': $('meta[name=csrf-token]').attr('content')
                        }
                    }).done(function(response) {
                        if (response.success) {
                            alert('Data deleted successfully');
                        } else {
                            alert(response.message);
                        }
                    });
                }
            });


            $(document).on('click', '#remove-all-error_logs-btn', function(e) {
                e.preventDefault();
                $("#deleteAllErrorLogModal").modal("hide");

                $.ajax({
                    url: errorLogDeleteAllUrl,
                    type: 'post',
                    data: {
                        '_token': $('meta[name=csrf-token]').attr('content')
                    }
                }).done(function(response) {
                    if (response.success) {
                        alert('Data deleted successfully');
                        $('#__quantityLogTable').DataTable().ajax.reload();
                    } else {
                        alert(response.message);
                    }
                });
            });

        </script>
    @endpush
</x-app-layout>





