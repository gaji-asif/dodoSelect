<x-app-layout>
    @section('title', 'Check-in/Check-Out')
        <link href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">
        <link href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css" rel="stylesheet">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
        <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
        <script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>
        <style>
            [x-cloak] {
                display: none;
            }

            .duration-300 {
                transition-duration: 300ms;
            }

            .ease-in {
                transition-timing-function: cubic-bezier(0.4, 0, 1, 1);
            }

            .ease-out {
                transition-timing-function: cubic-bezier(0, 0, 0.2, 1);
            }

            .scale-90 {
                transform: scale(.9);
            }

            .scale-100 {
                transform: scale(1);
            }

            .modal-hide {
                display: none !important;
            }
            .cutome_image{
                height: 70px;
                width: 100px
            }
        </style>
        
        <x-card title="{{$product->product_name}} - Quantity Log ({{count($quantityLogs)}})">
            <div class="mt-6">
                @if(session('success'))
                <x-alert-success>{{ session('success') }}</x-alert-success>
                @endif
                @if(session('danger'))
                <x-alert-danger>{{ session('danger') }}</x-alert-danger>
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
           {{-- update modal --}}
           <div class="modal-update modal-hide">
            <div style="background-color: rgba(0,0,0,0.5)"
                class="overflow-auto fixed  inset-0 z-10 flex items-center justify-center">
                <div class="bg-white w-11/12 md:max-w-md overflow-y-auto mx-auto rounded shadow-lg pt-4 pb-6 text-left px-6"
                    x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-90"
                    x-transition:enter-end="opacity-100 scale-100">

                    <div class="flex justify-between items-center pb-3">
                        <p class="text-2xl font-bold">Update Quantity</p>
                        {{-- tombol close --}}
                        <div class="cursor-pointer z-50" id="closeModalUpdate">
                            <svg class="fill-current text-black" xmlns="http://www.w3.org/2000/svg" width="18"
                                height="18" viewBox="0 0 18 18">
                                <path
                                    d="M14.53 4.53l-1.06-1.06L9 7.94 4.53 3.47 3.47 4.53 7.94 9l-4.47 4.47 1.06 1.06L9 10.06l4.47 4.47 1.06-1.06L10.06 9z">
                                </path>
                            </svg>
                        </div>
                    </div>

                    <form style="max-height:90vh" method="POST" action="{{ route('update quantity log') }}"
                        id="form-update"></form>
                </div>
            </div>
        </div>
            <div class="flex justify-between flex-col">
                <div class="overflow-x-auto">
                    <table class="table-auto border-collapse w-full border mt-4" id="datatable_1">
                        <thead class="border bg-green-300">
                            <tr class="rounded-lg text-sm font-medium text-gray-700 text-left">
                                <th class="px-4 py-2 border-2">Id</th>
                                <th class="px-4 py-2 border-2">In / Out</th>
                                <th class="px-4 py-2 border-2">Quantity</th>
                                <th class="px-4 py-2 border-2">Date/Time</th>
                                <th class="px-4 py-2 border-2">User</th>
                                <th class="px-4 py-2 border-2">Manage</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (isset($quantityLogs))
                                @foreach ($quantityLogs as $row)
                                    <tr>
                                        <td>{{$row->id}}</td>
                                        <td>
                                            @if($row->check_in_out == 1)
                                                <button type="button" class="rounded-full px-4 mr-2 bg-blue-600 text-white p-1 rounded  leading-none flex items-center">In</button>
                                            @else
                                                <button type="button" class="rounded-full px-4 mr-2 bg-red-600 text-white p-1 rounded  leading-none flex items-center">Out</button>
                                            @endif
                                        </td>
                                        <td>{{$row->quantity}}</td>
                                        <td>{{ date('Y-m-d H:i A',strtotime($row->date))}}</td>
                                        @if (empty($row->staff))
                                            <td>{{ $row->seller->name }}</td>
                                        @else 
                                            <td>{{ $row->staff->name }}</td>
                                        @endif
                                        @if (Auth::user()->role == "member")
                                            <td>
                                                <span x-on:click=" showEditModal=true"class="modal-open bg-green-500 text-white rounded px-2 py-1 mr-1 capitalize cursor-pointer" data-id="{{ $row->id}}" id="BtnUpdate"><i class="fas fa-pencil-alt"></i></span>
                                                <span class="bg-red-500 text-white rounded px-2 py-1 mr-4 capitalize cursor-pointer" data-id="{{$row->id }}" id="BtnDelete"><i class="fas fa-trash-alt"></i></span> 
                                            </td>
                                        @endif
                                    </tr>
             
                                      </div>
                                    
                                @endforeach
                            @endif
                       
                        </tbody>
                    </table>
                </div>
            </div>
        </x-card>


        <script>
            $(document).ready(function() {
                datatable = $('#datatable_1').DataTable({
                processing: true,
                order: [[ 0, "asc" ]]
                });
            });

            $(document).ready(function() {
       
            
                $(document).on('click', '#BtnUpdate', function() {
                    $('.modal-update').removeClass('modal-hide');
                    $.ajax({
                        url: '{{ route('date quantity log') }}?id=' + $(this).data('id'),
                        beforeSend: function() {
                            $('#form-update').html('Loading');
                        }
                    }).done(function(result) {
                        $('#form-update').html(result);
                    });
                });

                $(document).on('click', '#closeModalUpdate', function() {
                    $('.modal-update').addClass('modal-hide');
                });
   
                $(document).on('click', '#BtnDelete', function() {
                    let drop = confirm('Are you sure?');
                    if (drop) {
                        $.ajax({
                            url: '{{ route('delete quantity log') }}',
                            type: 'post',
                            data: {
                                'id': $(this).data('id'),
                                '_token': $('meta[name=csrf-token]').attr('content')
                            },
                            beforeSend: function() {
                                // Pesan yang muncul ketika memproses delete
                            }
                        }).done(function(result) {
                            if (result.status === 1) {
                                // Pesan jika data berhasil di hapus
                                alert('Data deleted successfully');
                                // $('#datatable').DataTable().ajax.reload();
                                location.reload();
                            } else {

                                alert(result.message);

                            }
                        });
                    }
                });
            });

        </script>
    </x-app-layout>
