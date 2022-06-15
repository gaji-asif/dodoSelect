<x-app-layout>

    @section('title')
        {{ __('translation.Shipping Cost') }}
    @endsection

    @push('bottom_css')
        <style>
            a:hover{
                text-decoration: none;
            }

            @media screen and (max-width: 600px) {
                .pricing_title{
                    margin-top: 60px;
                }
            }

            .card-header h2{
                font-size: 22px;
                font-weight: bold !important;
            }

            .lead{
               font-size: 18px;
            }
        </style>
    @endpush

        @if(\App\Models\Role::checkRolePermissions('Can access menu: Purchase Order - Product Cost'))

    <div class="col-span-12">
        <div class="row">
            <div class="col-lg-4 mt-3">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title" style="margin-bottom: 25px;">
                            @if (isset($editData))
                                <h4><strong>Edit Shipping Cost</strong></h4>
                            @else
                                <h4><strong>Add Shipping Cost</strong></h4>
                            @endif
                        </div>

                        @if(session()->has('success'))
                            <div class="alert alert-success mb-3 background-success" role="alert">
                            {{ session()->get('success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        @if(session()->has('danger'))
                            <div class="alert alert-danger mb-3 background-danger" role="alert">
                            {{ session()->get('danger') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        @if (isset($editData))
                            <form method="POST" action="{{url('shipping-cost_update/'.$editData->id)}}" id="form-import" enctype="multipart/form-data">
                        @else
                            <form method="POST" action="{{route('shipping-cost.store')}}" id="form-import" enctype="multipart/form-data">
                        @endif

                            @csrf
                            <input type="hidden" name="shipper_id" value="{{$shipper_id}}">
                            <div class="form-group">
                                <x-label for="name">
                                    Name <x-form.required-mark />
                                </x-label>
                                @if (isset($editData))
                                    <x-input type="text" name="name" id="name" placeholder="{{ __('translation.Name') }}" value="{{ $editData->name }}" />
                                @else
                                    <x-input type="text" name="name" id="name" placeholder="{{ __('translation.Name') }}" value="{{ old('name') }}" />
                                @endif
                            </div>
                            <div class="form-group">
                                <label for="weight_from">
                                    Weight From <x-form.required-mark />
                                </label>
                                @if (isset($editData))
                                    <x-input type="number" name="weight_from" id="weight_from" min="0" step="0.1" placeholder="{{ __('translation.Weight From') }}" value="{{ $editData->weight_from }}" />
                                @else
                                    <x-input type="number" name="weight_from" id="weight_from" min="0" step="0.1" placeholder="{{ __('translation.Weight From') }}" value="{{ old('weight_from') }}" />
                                @endif
                            </div>
                            <div class="form-group">
                                <x-label for="weight_to">
                                    Weight To <x-form.required-mark />
                                </x-label>

                                @if (isset($editData))
                                    <x-input type="number" name="weight_to" id="weight_to" min="0" step="0.1" placeholder="{{ __('translation.Weight To') }}" value="{{ $editData->weight_to }}" />
                                @else
                                    <x-input type="number" name="weight_to" id="weight_to" min="0" step="0.1" placeholder="{{ __('translation.Weight To') }}" value="{{ old('weight_to') }}" />
                                @endif
                            </div>
                            <div class="form-group">
                                <label for="price">
                                    Price <x-form.required-mark />
                                </label>

                                @if (isset($editData))
                                    <x-input type="number" name="price" id="price" min="0" step="0.1" placeholder="{{ __('translation.Price') }}" value="{{ $editData->price }}" />
                                @else
                                    <x-input type="number" name="price" id="price" min="0" step="0.1" placeholder="{{ __('translation.Price') }}" value="{{ old('price') }}" />
                                @endif
                            </div>
                            <div class="text-right">
                                <x-button type="submit" color="blue">
                                    Submit
                                </x-button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>

            <div class="col-lg-8 mt-3">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title" style="margin-bottom: 25px;">
                            <h4>
                                <strong>
                                    List Of Shipping Cost - @if (isset($shippingCompany)) {{$shippingCompany->name}} @endif  @if (isset($data)) ({{count($data)}}) @endif
                                </strong>
                            </h4>
                        </div>

                        <div class="mt-4">
                            <table class="w-full" id="datatable_1">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2 border-2">ID</th>
                                        <th class="px-4 py-2 border-2">Name</th>
                                        <th class="px-4 py-2 border-2">Weight From</th>
                                        <th class="px-4 py-2 border-2">Weight To</th>
                                        <th class="px-4 py-2 border-2">Price</th>
                                        <th class="px-4 py-2 border-2">Manage</th>
                                    </tr>
                                </thead>
                                <tbody >
                                    @if (isset($data))
                                        @foreach ($data as $key=>$row)
                                        <tr >
                                            <td class="pl-1">{{++$key}}</td>
                                            <td>{{$row->name}}</td>
                                            <td>{{$row->weight_from}} Unit</td>
                                            <td>{{$row->weight_to}} Unit</td>
                                            <td>฿ {{$row->price}}</td>

                                            <td>
                                                <a href="{{url('shipping-cost-edit/'.$row->id.'/'.$row->shipper_id)}}" class= "btn btn-sm bg-green-500 text-white rounded px-2 py-1 mr-1 capitalize cursor-pointer"  ><i class="fas fa-pencil-alt"></i></a>
                                                <button type="button"  class="btn btn-sm bg-red-500 text-white rounded px-2 py-1 mr-4 capitalize cursor-pointer"  data-toggle="modal" data-target="#deleteModal{{$row->id}}" id="BtnDelete"><i class="fas fa-trash-alt"></i></button>

                                                <div class="modal fade" id="deleteModal{{$row->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="exampleModalLabel">Delete</h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div>
                                                                <h6>Do you want to delete this Item</h6>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn bg-green-500 text-white" data-dismiss="modal">Close</button>
                                                            <a href="{{url('shipping-cost-delete/'.$row->id)}}">
                                                                <button type="button" class="btn bg-red-500 text-white">Yes</button>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            </td>
                                        </tr>
                                        @endforeach
                                    @endif

                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>

        </div>

    </div>

        @endif

    @push('bottom_js')
        <script>
            $(document).ready(function() {
                $('#datatable_1').DataTable({
                    processing: true,
                    order: [[ 0, "asc" ]]
                });
            });
        </script>
    @endpush

</x-app-layout>
