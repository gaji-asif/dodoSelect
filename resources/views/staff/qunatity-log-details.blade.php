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

                            </tr>
                        </thead>
                        <tbody>
                            @if (isset($quantityLogs))
                            @if (isset($quantityLogs))
                            @foreach ($quantityLogs as $row)
                                <tr>
                                    <td>{{$row->id}}</td>
                                    <td>
                                        @if($row->check_in_out == 1)
                                            <button type="button" class="rounded-full px-4 mr-2 bg-blue-600 text-white p-1 leading-none flex items-center">In</button>
                                        @else
                                            <button type="button" class="rounded-full px-4 mr-2 bg-red-600 text-white p-1 leading-none flex items-center">Out</button>
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
                                            <span x-on:click=" showEditModal=true"class="modal-open bg-green-500 text-white rounded px-2 py-1 mr-4 capitalize cursor-pointer" data-id="{{ $row->id}}" id="BtnUpdate"><i class="fas fa-pencil-alt"></i></span>
                                            <span class="bg-red-500 text-white rounded px-2 py-1 mr-4 capitalize cursor-pointer" data-id="{{$row->id }}" id="BtnDelete"><i class="fas fa-trash-alt"></i></span>
                                        </td>
                                    @endif
                                </tr>

                                  </div>

                            @endforeach
                        @endif
                            @endif

                        </tbody>
                    </table>
                </div>
            </div>
        </x-card>


        <script>
            $(document).ready(function() {
                datatable = $('#datatable_1').DataTable({
                                processing: true
                            });
            });
    </script>
    </x-app-layout>
