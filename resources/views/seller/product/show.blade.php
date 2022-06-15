<x-app-layout>

    @section('title')
        Product Detail : {{ $product->product_name }}
    @endsection

    @push('top_css')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
        <style type="text/css">
            h2 {
                color: white;
            }
        </style>
    @endpush

        @if(\App\Models\Role::checkRolePermissions('Can access menu: Product'))
            <div class="col-span-12">
        <x-card.card-default>
            <x-card.header>
                <x-card.title>
                    {{ __('translation.Product Detail') }}
                </x-card.title>
            </x-card.header>
            <x-card.body>
                <div class="flex flex-col sm:flex-row justify-between mb-5">
                    <div class="w-full sm:w-1/3 lg:w-1/4 mb-6 sm:mb-0 sm:mr-3">
                        <img class="w-full h-auto rounded-md border border-solid border-gray-200" src="{{ $product->image_url }}" alt="{{ $product->product_name }}">
                    </div>
                    <div class="w-full sm:w-2/3 lg:w-3/4 px-1 sm:px-0 sm:ml-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-x-5 mb-5">
                            <div>
                                <label class="block mb-1 text-blue-600">
                                    {{ __('translation.Product Name') }}
                                </label>
                                <p class="font-bold mb-0">
                                    {{ $product->product_name }}
                                </p>
                            </div>
                            <div>
                                <label class="block mb-1 text-blue-600">
                                    {{ __('translation.Product Code') }}
                                </label>
                                <p class="font-bold mb-0">
                                    {{ !empty($product->product_code) ? $product->product_code : '-' }}
                                </p>
                            </div>
                            <div>
                                <label class="block mb-1 text-blue-600">
                                    {{ __('translation.Category') }}
                                </label>
                                <p class="font-bold mb-0">
                                    {{ !empty($product->category->cat_name) ? $product->category->cat_name : '-' }}
                                </p>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block mb-1 text-blue-600">
                                    {{ __('translation.Specifications') }}
                                </label>
                                <p class="font-bold mb-0">
                                    {{ !empty($product->specifications) ? $product->specifications : '-' }}
                                </p>
                            </div>
                            <div>
                                <label class="block mb-1 text-blue-600">
                                    {{ __('translation.Supplier Name') }}
                                </label>
                                <p class="font-bold mb-0">
                                    {{ !empty($product->supplier->supplier_name) ? $product->supplier->supplier_name : '-' }}
                                </p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-x-5 mb-5">
                            <div>
                                <label class="block mb-1 text-blue-600">
                                    {{ __('translation.Quantity') }}
                                </label>
                                <p class="font-bold mb-0">
                                    {{ !empty($product->getQuantity->quantity) ? number_format($product->getQuantity->quantity) : '0' }}
                                </p>
                            </div>
                            <div>
                                <label class="block mb-1 text-blue-600">
                                    {{ __('translation.Incoming') }}
                                </label>
                                <p class="font-bold mb-0">
                                    {{ !empty($product->total_incoming) ? number_format($product->total_incoming) : '0' }}
                                </p>
                            </div>
                            <div>
                                <label class="block mb-1 text-blue-600">
                                    {{ __('translation.Alert Stock') }}
                                </label>
                                <p class="font-bold mb-0">
                                    {{ !empty($product->alert_stock) ? number_format($product->alert_stock) : '0' }}
                                </p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-x-5 mb-5">
                            <div>
                                <label class="block mb-1 text-blue-600">
                                    {{ __('translation.Price') }}
                                </label>
                                <p class="font-bold mb-0">
                                    {{ currency_symbol('THB') }}
                                    {{ !empty($product->price) ? number_format($product->price) : '0' }}
                                </p>
                            </div>
                            <div>
                                <label class="block mb-1 text-blue-600">
                                    {{ __('translation.Dropship Price') }}
                                </label>
                                <p class="font-bold mb-0">
                                    {{ currency_symbol('THB') }}
                                    {{ !empty($product->dropship_price) ? number_format($product->dropship_price) : '0' }}
                                </p>
                            </div>
                            <div>
                                <label class="block mb-1 text-blue-600">
                                    {{ __('translation.Weight') }}
                                </label>
                                <p class="font-bold mb-0">
                                    {{ !empty($product->weight) ? number_format($product->weight) : '0' }}
                                </p>
                            </div>
                            <div>
                                <label class="block mb-1 text-blue-600">
                                    {{ __('translation.Piece / Pack') }}
                                </label>
                                <p class="font-bold mb-0">
                                    {{ !empty($product->pack) ? number_format($product->pack) : '-' }}
                                </p>
                            </div>
                            <div>
                                <label class="block mb-1 text-blue-600">
                                    {{ __('translation.Cost / Pc') }}
                                </label>
                                <p class="font-bold mb-0">
                                    {{ !empty($product->cost_pc) ? number_format($product->cost_pc) : '0' }}
                                </p>
                            </div>
                            <div>
                                <label class="block mb-1 text-blue-600">
                                    {{ __('translation.Currency') }}
                                </label>
                                <p class="font-bold mb-0">
                                    {{ !empty($product->currency) ? number_format($product->currency) : '0' }}
                                </p>
                            </div>
                        </div>
                        <hr />
                        @if($product->child_of != null)
                            <table class="table table-striped table-condensed">
                                <tbody>
                                <tr>
                                    <td colspan="2">This product is child of <a href="{{ route("product.show", ["id" => product_id_by_code($product->child_of)]) }}" target="_blank">{{ $product->child_of }}</a></td>
                                </tr>
                                </tbody>
                            </table>
                        @else
                            <div class="grid grid-cols-2 lg:grid-cols-2 gap-5 sm:gap-x-5 mb-5">
                                <div>
                                    <label class="block mb-1 text-blue-600">
                                        {{ __('translation.Add child SKU') }}
                                    </label>
                                    <form method="POST" action="{{ route("product.child.sku.add", ["product_code" => $product->product_code]) }}">
                                        {{ csrf_field() }}
                                        <x-select name="product_sku" class="live-search" required></x-select>
                                        <button class="btn-action--blue float-right" type="submit">Save SKU</button>
                                    </form>
                                    @if(!empty(session('msg')))
                                        <span class="alert-warning">{!! session('msg') !!}</span>
                                    @endif
                                </div>
                                <div>
                                    <label class="block mb-1 text-blue-600">
                                        {{ __('translation.List of Child SKU') }}
                                    </label>
                                    @if(isset($product->child_products))
                                        <table class="table table-striped table-condensed">
                                            <tbody>
                                            @foreach(explode(",", $product->child_products) as $child)
                                                <tr>
                                                    <td><a href="{{ route("product.show", ["id" => product_id_by_code($child)]) }}" target="_blank">{{ $child }}</a></td>
                                                    <td><button class="btn-action--red" id="{{ trim($child) }}" onclick="deleteChildProduct(this.id)"><i class="fas fa-trash-alt"></i></button></td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <table class="table table-striped table-condensed">
                                            <tbody>
                                            <tr>
                                                <td colspan="2">No child SKU added</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="text-right">
                    <x-button-link href="{{ route('product') }}" color="green">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="w-4 h-4 bi bi-arrow-left" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
                        </svg>
                        <span class="ml-2">
                            {{ __('translation.Go back') }}
                        </span>
                    </x-button-link>
                </div>
            </x-card.body>
        </x-card.card-default>
    </div>
        @endif

    @push('bottom_js')
        <script type="text/javascript">
            function deleteChildProduct(product_code)
            {
                $.ajax({
                    url: "{{route('product.child.sku.delete', ["product_code" => "__product_code__"])}}".replace("__product_code__", product_code),
                    type: "POST",
                    data: {
                        product_sku: "{{ $product->product_code }}",
                        _token: '{{ csrf_token() }}'
                    },
                    dataType: 'json',
                    success: function () {
                        location.reload();
                    }
                });
            }
        </script>

        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
        <script type="text/javascript">
        $('.live-search').select2({
            placeholder: 'Select SKU',
            ajax: {
                url: '{{ route('product.child.sku.search') }}',
                dataType: 'json',
                delay: 250,
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.product_code,
                                id: item.product_code
                            }
                        })
                    };
                },
                cache: true
            }
        });
    </script>
    @endpush

</x-app-layout>
