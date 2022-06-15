<x-app-layout>
    @section('title', 'Purchase Order')

    @push('top_css')
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css">
    @endpush

    <style type="text/css">
        .nav>li a.active{
            background: #dbeafe;
        }
        .nav>li>a {
            position: relative;
            display: block;
            padding: 10px 15px;
            border: 1px solid #adb0b3;
            margin-right: 15px;
        }
    </style>

    @if(\App\Models\Role::checkRolePermissions('Can access menu: Purchase Order - Purchase Order'))
        <div class="col-span-12">
        @if ($errors->any())
            <x-alert-danger class="mb-5">
                <ul class="mt-3 list-disc list-inside text-sm text-red-600">
                    @foreach ($errors->all() as $error)
                        <li>
                            {{ $error }}
                        </li>
                    @endforeach
                </ul>
            </x-alert-danger>
        @endif

        <x-card.card-default>
            <x-card.header>
                <x-card.back-button href="{{ route('order_purchase.index') }}" />
                <x-card.title>
                    {{ __('translation.Create Purchase Order') }}
                </x-card.title>
            </x-card.header>
            <x-card.body>

                <x-alert-danger class="alert mb-5 hidden" id="__alertDanger">
                    <span id="__content_alertDanger"></span>
                </x-alert-danger>

                <x-alert-success class="alert mb-5 hidden" id="__alertSuccess">
                    <span id="__content_alertSuccess"></span>
                </x-alert-success>

                <form method="POST" action="{{ route('order_purchase.store') }}" id="__formPurchaseOrder" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="order_purchase_id" name="order_purchase_id" value="{{$timestamp}}">
                    <ul  class="nav nav-pills">
                        <li class="active">
                            <a  href="#po-info" data-toggle="tab">PO Information</a>
                        </li>
                        <li>
                            <a href="#shipment-details" data-toggle="tab">Shipment Details</a>
                        </li>
                        <li>
                            <a href="#payment" data-toggle="tab">Payment</a>
                        </li>
                    </ul>

                    <div class="tab-content clearfix">
                        <div class="tab-pane active" id="po-info">
                            <div class="mb-10">
                                <div class="flex flex-row items-center justify-between mb-3 mt-3">
                                    <h2 class="block whitespace-nowrap text-gray-600 text-base font-bold">
                                        {{ __('translation.Purchase Order Info') }}
                                    </h2>
                                    <hr class="w-full ml-3 relative -top-1 border border-r-0 border-b-0 border-l-0 border-gray-300">
                                </div>

                                <div class="w-full mb-5">
                                    <x-label>
                                        {{ __('translation.Supply From') }}
                                    </x-label>
                                    <div class="flex flex-row">
                                        <div>
                                            <x-form.input-radio name="check" id="__importSupplyFrom" value="1" checked="true">
                                                {{ __('translation.Import') }}
                                            </x-form.input-radio>
                                        </div>
                                        <div class="ml-5">
                                            <x-form.input-radio name="check" id="__domesticSupplyFrom" value="2">
                                                {{ __('translation.Domestic') }}
                                            </x-form.input-radio>
                                        </div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-x-8">
                                    <div class="sm:col-span-2 lg:col-span-4 grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-x-8">
                                        <div class="lg:col-span-2">
                                            <x-label>
                                                {{ __('translation.Order Date') }} <x-form.required-mark/>
                                            </x-label>
                                            <x-input type="text" name="order_date" id="order_date" class="datepicker-1" required />
                                        </div>

                                        <div class="lg:col-span-2">
                                            <x-label>
                                                {{ __('translation.Supplier Name') }} <x-form.required-mark/>
                                            </x-label>
                                            <x-select id="supplier_id" name="supplier_id" style="width: 100%">
                                                <option disabled selected value="0">
                                                    - {{ __('translation.Select Supplier Name') }} -
                                                </option>
                                                @foreach ($suppliers as $supplier)
                                                    <option value="{{ $supplier->id }}">
                                                        {{ $supplier->supplier_name }}
                                                    </option>
                                                @endforeach
                                            </x-select>
                                        </div>
                                    </div>

                                    <div class='imports'>
                                        <x-label>
                                            {{ __('translation.Ship Type') }}
                                        </x-label>
                                        <x-select name="shipping_type_id" id="shipping_type_id" style="width: 100%">
                                            <option disabled selected value="0">
                                                - {{ __('translation.Select Ship Type') }} -
                                            </option>
                                            @foreach ($shiptypes as $shiptype)
                                                <option value="{{ $shiptype->id }}">
                                                    {{ $shiptype->name }}
                                                </option>
                                            @endforeach
                                        </x-select>
                                    </div>

                                    <div class='imports'>
                                        <x-label>
                                            {{ __('translation.Cargo') }}
                                        </x-label>
                                        <x-select name="agent_cargo_id" id="agent_cargo_id" style="width: 100%">
                                            <option disabled selected value="0">
                                                - {{ __('translation.Select Cargo') }} -
                                            </option>
                                            @foreach ($cargos as $cargo)
                                                <option value="{{ $cargo->id }}">
                                                    {{ $cargo->name }}
                                                </option>
                                            @endforeach
                                        </x-select>
                                    </div>

                                    <div class='imports'>
                                        <x-label>
                                            {{ __('translation.Shipping Mark') }}
                                        </x-label>
                                        <x-select name="shipping_mark_id" id="shipping_mark_id" style="width: 100%">
                                        </x-select>
                                    </div>
                                </div>
                            </div>

                            <x-button type="button" color="green" class="relative -top-1 ml-1" id="__btnAddProducts">
                                {{ __('translation.Add Product') }}
                            </x-button>


                            <x-modal.modal-full class="modal-products modal-hide ">
                                <x-modal.header>
                                    <x-modal.title>
                                        {{ __('translation.Product Details') }}
                                    </x-modal.title>
                                    <x-modal.close-button id="closeModalproduct" />
                                </x-modal.header>
                                <x-modal.body>
                                    <div class="mb-10">
                                        <div class="flex flex-row items-center justify-between mb-3 mt-3">
                                            <h2 class="block whitespace-nowrap text-gray-600 text-base font-bold">
                                                {{ __('translation.Products Info') }}
                                            </h2>
                                            <hr class="w-full ml-3 relative -top-1 border border-r-0 border-b-0 border-l-0 border-gray-300">
                                        </div>

                                        <div class="mb-8 md:mb-2">
                                            <div class="flex flex-col md:flex-row items-center justify-between">
                                                <div class="w-full md:w-full lg:w-full xl:w-full flex flex-col sm:flex-row mb-0 sm:mb-4">

                                                    <div class="w-full lg:w-3/5 mb-4 sm:mb-0 sm:ml-2">
                                                        <x-label>
                                                            {{ __('translation.Supplier Name') }} <x-form.required-mark/>
                                                        </x-label>
                                                        <x-select name="supplier" id="__selectSupplierFilter" style="width: 100%">
                                                            <option disabled selected value="0">
                                                                - {{ __('translation.Select Supplier Name') }} -
                                                            </option>
                                                            @foreach ($suppliers as $supplier)
                                                                <option value="{{ $supplier->id }}">
                                                                    {{ $supplier->supplier_name }}
                                                                </option>
                                                            @endforeach
                                                        </x-select>
                                                    </div>


                                                    <div class="w-full lg:w-5/5 mb-4 sm:mb-0 sm:ml-2">
                                                        <x-label>
                                                            {{ __('translation.Select Status') }} <x-form.required-mark/>
                                                        </x-label>
                                                        <x-select name="status" id="__selectStatusFilter"  multiple="multiple" class="status" style="width: 100%;">
                                                            <option value="0" disabled>
                                                                {{ '- ' . __('translation.Select Status') . ' -' }}
                                                            </option>
                                                            <option value="1">
                                                                {{ __('translation.Out Of Stock') }}
                                                            </option>
                                                            <option value="2">
                                                                {{ __('translation.Low Stock') }}
                                                            </option>
                                                            <option value="3">
                                                                {{ __('translation.OVER STOCK') }}
                                                            </option>
                                                            <option value="4">
                                                                N/A
                                                            </option>
                                                        </x-select>
                                                    </div>
                                                    <div hidden class="w-full lg:w-3/5 mb-4 sm:mb-0 sm:ml-2">
                                                        <x-select name="category" id="__selectCategoryFilter" class="category" style="width: 100%;">
                                                            <option value="" selected disabled>
                                                                {{ '- ' . __('translation.Select Product Category') . ' -' }}
                                                            </option>
                                                            @if (isset($categories))
                                                                @foreach ($categories as $cateroy)
                                                                    <option value="{{$cateroy->id}}">{{$cateroy->cat_name}}</option>
                                                                @endforeach
                                                            @endif
                                                        </x-select>
                                                    </div>

                                                    <div style='margin-top:30px' class="w-full md:w-1/4 lg:w-1/3 xl:w-1/2 flex items-center justify-end lg:justify-start lg:ml-2">
                                                        <x-button type="button" color="blue" class="relative -top-1 order-last md:order-first mx-1" id="__btnSubmitFilter">
                                                            {{ __('translation.Search') }}
                                                        </x-button>
                                                        <x-button type="button" color="yellow" class="relative -top-1 reset-filter" id="__btnResetFilter">
                                                            {{ __('translation.Reset') }}
                                                        </x-button>

                                                        <x-button type="button" color="green" class="relative -top-1 ml-1" id="__btnLoadProducts">
                                                            {{ __('translation.Load') }}
                                                        </x-button>
                                                    </div>

                                                </div>

                                            </div>
                                        </div>

                                        <div class="overflow-x-auto">
                                            <table class="w-full" id="datatable">
                                                <thead>
                                                <tr>
                                                    <th class="text-center"></th>
                                                    <th class="text-center">Product ID</th>
                                                    <th class="text-center">Image</th>
                                                    <th class="text-center">Product Name</th>
                                                    <th class="text-center">Product Code</th>
                                                    <th class="text-center">Quantity</th>
                                                    <th class="text-center">Incoming</th>
                                                    <th class="text-center">Alert Stock</th>
                                                    <th class="text-center">Reorder Qty</th>
                                                    <th class="text-center">Supplier</th>
                                                    <th class="text-center">Report Status</th>
                                                </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </x-modal.body>
                            </x-modal.modal-full>

                            <div class="mb-10">
                            <div class="mb-5">
                                    {{-- <x-input type="text" class="qr-code" id="__productCodeSearch" placeholder="{{ __('translation.Enter Product Code') }}" />
                                    <x-input type="text" id="__productCodeSearch" placeholder="{{ __('translation.Enter Product Name or Code') }}" />--}}
                                </div>
                                <div id="__wrapper_ProductList"></div>
                                <div id="__wrapper_NoProduct">
                                    <div class="w-full p-4 rounded-lg text-center">
										<span class="font-bold text-lg text-gray-500">
											--- No Product Added ---
										</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane" id="shipment-details">
                            <div class="mb-10">
                                <div class="flex flex-row items-center justify-between mb-3 mt-3">
                                    <h2 class="block whitespace-nowrap text-gray-600 text-base font-bold">
                                        {{ __('translation.Shipment Info') }}
                                    </h2>
                                    <hr class="w-full ml-3 relative -top-1 border border-r-0 border-b-0 border-l-0 border-gray-300">
                                </div>

                                <x-button type="button" color="green" class="relative -top-1 ml-1" id="__btnAddShippment">
                                    {{ __('translation.Add Shipment') }}
                                </x-button>

                                <div class="mb-10" id="shipment_wrapper_list">
                                    <div class="overflow-x-auto">
                                        <table class="w-full hide" id="datatable_shipment">
                                            <thead>
                                            <tr>
                                                <th class="text-center">Date</th>
                                                <th class="text-center">Estimated Arrival</th>
                                                <th class="text-center">Details</th>
                                                <th class="text-center">Status</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane" id="payment">
                            <div class="mb-10">
                                <div class="flex flex-row items-center justify-between mb-3 mt-3">
                                    <h2 class="block whitespace-nowrap text-gray-600 text-base font-bold">
                                        {{ __('translation.Payment') }}
                                    </h2>
                                    <hr class="w-full ml-3 relative -top-1 border border-r-0 border-b-0 border-l-0 border-gray-300">
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-x-8">

                                    <div>
                                        <x-label>
                                            {{ __('translation.Amount Invoice') }}
                                        </x-label>
                                        <x-input type="number" name="amount" id="amount" style="width: 100%"></x-input>
                                    </div>


                                    <div>
                                        <x-label>
                                            {{ __('translation.Paid') }}
                                        </x-label>
                                        <x-input type="number" name="paid" id="paid" style="width: 100%">
                                            </x-select>
                                    </div>

                                    <div>
                                        <x-label>
                                            {{ __('translation.Currency') }} :
                                        </x-label>
                                        <x-select name="payment_exchange_rate_id" id="exchange_rate_id">
                                            <option value="" selected>
                                                {{ '- ' . __('translation.Select Currency') . ' -' }}
                                            </option>
                                            @foreach ($exchangeRates as $exchangeRate)
                                                <option value="{{ $exchangeRate->id }}" {selected_{{ $exchangeRate->id }}}>
                                                    {{ $exchangeRate->name }}
                                                </option>
                                            @endforeach
                                        </x-select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-4 sm:gap-x-8 mt-4">
                                    <div>
                                        <x-label>
                                            {{ __('translation.Payment Status') }}
                                        </x-label>

                                        <x-select name="payment_status" id="payment_status" style="width: 100%">
                                            <option disabled selected value="0">
                                                - {{ __('translation.Select Status') }} -
                                            </option>
                                            <option value="unpaid" >Unpaid</option>
                                            <option value="paid" >Paid</option>
                                            <option value="complete">Complete</option>
                                        </x-select>
                                    </div>

                                    <div>
                                        <x-label>
                                            {{ __('translation.Bank Account') }}
                                        </x-label>
                                        <x-textarea name="bank_account" id="bank_account" class="bank_account"></x-textarea>
                                    </div>

                                    <div>
                                        <x-label>
                                            {{ __('translation.Notes') }}
                                        </x-label>
                                        <x-textarea name="notes" id="notes" class="notes"></x-textarea>
                                    </div>

                                    <div>
                                        <x-label>
                                            {{ __('translation.Invoice') }}
                                        </x-label>
                                        <x-input type="file" name="file_invoice" id="file_invoice" style="width: 100%"></x-input>
                                    </div>

                                    <div>
                                        <x-label>
                                            {{ __('translation.Payment') }}
                                        </x-label>
                                        <x-input type="file" name="file_payment" id="file_payment" style="width: 100%">
                                            </x-select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="text-center pb-5">
                        <x-button type="submit" color="blue" id="__btnSubmitPurchaseOrder">
                            {{ __('translation.Submit Data') }}
                        </x-button>
                    </div>

                </form>

            </x-card.body>
        </x-card.card-default>
    </div>
    @endif

    <x-modal.alert id="__modalAlert" class="hidden">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Info') }}
            </x-modal.title>
            <x-modal.close-button id="__btnClose_modalAlert" />
        </x-modal.header>
        <x-modal.body>
            <div class="text-center pb-10 text-base" id="__content_modalAlert"></div>
        </x-modal.body>
    </x-modal.alert>




	<x-modal.modal-full class="modal-shipment modal-hide ">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Shipment Details') }}
            </x-modal.title>
            <x-modal.close-button class="__closeModalShipment" />
        </x-modal.header>
        <x-modal.body>

		<div id="LoadShipmentForm"></div>


        </x-modal.body>
    </x-modal.modal-full>



    <div hidden id="allTemplateWrappers">
		<div id="__templateProductItem">
			<div class="flex flex-row mb-5 py-4 border border-solid border-t-0 border-r-0 border-l-0 border-gray-200 __row_ProductItem_{product_code}">
				<input type="hidden" name="product_id[]" value="{product_id}">

				<div class="w-1/4 sm:w-1/4 md:w-1/6 mb-4 md:mb-0">
					<div class="mb-4">
						<img src="{{asset('{product_img}')}}" alt="Image" class="w-full h-auto rounded-sm">
					</div>
					<div class="block lg:hidden">
						<x-button type="button" color="red" class="block w-full" data-code="{product_code}" onClick="removeProductItem(this)">
							<span class="block sm:hidden">
								<i class="fas fa-times"></i>
							</span>
							<span class="hidden sm:block">
								{{ __('translation.Remove') }}
							</span>
						</x-button>
					</div>
				</div>
				<div class="w-3/4 sm:w-3/4 md:w-5/6 ml-4 sm:ml-6">
					<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-2 gap-4 sm:gap-x-6 lg:pt-1">
						<div>
							<div class="mb-2 xl:mb-4 lg:col-span-2 xl:col-span-3">
								<label class="hidden lg:block mb-0">
									{{ __('translation.Product Name') }} :
								</label>
								<p class="font-bold">
									{product_name} <br>
									<span class="text-blue-500">{product_code}</span>
								</p>
							</div>
							<div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
								<div>
									<label class="mb-0">
										{{ __('translation.Price') }} :
									</label>
									<span class="font-bold lg:block">
										{{ currency_symbol('THB') }}
										{price}
									</span>
								</div>
								<div>
									<label class="mb-0">
										{{ __('translation.Pieces/Pack') }} :
									</label>
									<span id="pieces_per_pack" class="font-bold lg:block">
										{pieces_per_pack}
									</span>
								</div>
								<div>
									<label class="mb-0">
										{{ __('translation.Available Qty') }} :
									</label>
									<span class="font-bold lg:block">
										{available_qty}
									</span>
								</div>
							</div>

							<div class="mb-4 grid grid-cols-1 lg:grid-cols-3 gap-3">
								<div>
									<label class="mb-0">
										{{ __('translation.Status') }} :
									</label>
									<span class="font-bold lg:block">
									   {current_status}
									</span>
								</div>
								<div>
									<label class="mb-0">
										{{ __('translation.Incoming') }} :
									</label>
									<span class="font-bold lg:block">
										{total_incoming}
									</span>
								</div>
							</div>
							<hr>
							<div class="mb-4 grid grid-cols-1 lg:grid-cols-3 gap-3">
								<div>
									<label class="mb-0">
										{{ __('translation.Supplier') }} :
									</label>
									<span class="font-bold lg:block">
										{supplier_name}
									</span>
								</div>
								<div>
									<label class="mb-0">
										{{ __('translation.Pieces/Carton') }} :
									</label>
									<span class="font-bold lg:block">
										{pieces_per_carton}
									</span>
								</div>
								<div>
									<label class="mb-0">
										{{ __('translation.Re-Order') }} :
									</label>
									<span class="font-bold lg:block">
										{reorder}
									</span>
								</div>
							</div>

							<div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
								<div>
									<label class="mb-0">
										{{ __('translation.Cost Per Piece') }} :
									</label>
									<div class="w-full">
										<x-input type="number" name="product_price[]" min="0" step="0.001" />
										{default_cost_currency}
									</div>
								</div>
								<div>
									<label class="mb-0">
										{{ __('translation.Currency') }} :
									</label>
									<div class="w-full">
										<x-select name="exchange_rate_id[]" id="exchange_rate_id">
											<option value="" selected>
												{{ '- ' . __('translation.Select Currency') . ' -' }}
											</option>
											@foreach ($exchangeRates as $exchangeRate)
												<option value="{{ $exchangeRate->id }}" {selected_{{ $exchangeRate->id }}}>
													{{ $exchangeRate->name }}
												</option>
											@endforeach
										</x-select>
									</div>
								</div>
							</div>
						</div>
						<div class="md:col-span-1 lg:col-span-1">
							<div class="mb-4 lg:mb-2 " style="border: 1px solid #e6e6e6;padding:20px;">
								<label class="mb-0">
									{{ __('translation.Order') }} (Packs) <x-form.required-mark/> :
								</label>
								<div class="w-full md:w-full md:pr-2">
									<x-input type="number" name="product_quantity[]"  data-code="{product_code}" class="order-qty__field order-qty__field_{product_code} mb-2" value="1" min="0" />
									<strong>{{ __('translation.Total Pieces') }} : <span class='res_pieces'>{pieces_per_pack_label}</span></strong>
								</div>
							</div>
							<div class="hidden lg:block text-right lg:text-left lg:mt-5 xl:mt-3">
								<x-button type="button" color="red" class="block lg:relative w-full lg:w-auto" data-code="{product_code}" onClick="removeProductItem(this)">
									{{ __('translation.Remove') }}
								</x-button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div id="__templateShippedProductItem">
			<div class="flex flex-row mb-5 py-4 border border-solid border-t-0 border-r-0 border-l-0 border-gray-200 __row_ProductItem_{product_code_shipped}">
				<div class="w-1/4 sm:w-1/4 md:w-1/6 mb-4 md:mb-0">
					<div class="mb-4">
						<img src="{{asset('{product_img}')}}" alt="Image" class="w-full h-auto rounded-sm">
					</div>
					<div class="hidden lg:hidden">
						<x-button type="button" color="red" class="block w-full" data-code="{product_code_shipped}" onClick="removeProductItem(this)">
							<span class="block sm:hidden">
								<i class="fas fa-times"></i>
							</span>
							<span class="hidden sm:block">
								<i class="fas fa-trash-alt"></i>
							</span>
						</x-button>
					</div>
				</div>
				<div class="w-3/4 sm:w-3/4 md:w-5/6 ml-4 sm:ml-6">
					<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-2 gap-4 sm:gap-x-6 lg:pt-1">
						<div>
							<div class="mb-2 xl:mb-4 lg:col-span-2 xl:col-span-3">
								<label class="hidden lg:block mb-0">
									{{ __('translation.Product Name') }} :
								</label>
								<p class="font-bold">
									{product_name_shipped} <br>
									<span class="text-blue-500">{product_code_shipped}</span>
								</p>
							</div>
							<div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
								<div>
									<label class="mb-0">
										{{ __('translation.Order (Packs)') }} :
									</label>
									<span class="font-bold lg:block" id="order_qty_{product_code_shipped}">
										{order_qty}
									</span>
								</div>
								<div>
									<label class="mb-0">
										{{ __('translation.Shipped') }} :
									</label>

									<span hidden class=" font-bold" id="shipped_qty_{product_code_shipped}">
										{shipped_quantity}
									</span>

                                    <span  class="font-bold lg:block" id="total_shipped_qty_{product_code_shipped}">
										{total_shipped}
									</span>

								</div>
								<div>
									<label class="mb-0">
										{{ __('translation.Available Qty') }} :
									</label>
									<span class="font-bold lg:block" id="available_qty_{product_code_shipped}">
										{available_shipped_qty}
									</span>
								</div>
							</div>
						</div>
						<div class="grid grid-cols-1 lg:grid-cols-1 gap-3">

							<div class="mb-4 lg:mb-2 " style="border: 1px solid #e6e6e6;padding:20px;">
								<label class="mb-0">
									{{ __('translation.Shipped') }} (Packs) :
								</label>
								<div class="w-full md:w-full md:pr-2">
									<x-input data-code="{product_code_shipped}" data-product_id="{product_id_shipped}" id="ship_quantity_{product_code_shipped}" class="mb-2 ship_quantity" type="number" name="ship_quantity[]" data-old-qty="{shipQtyOld}"  pack="{pack}" max="{available_shipped_qty}"  value="{shipQty}"  />
									<strong>{{ __('translation.Total Pieces') }} : <span class='res_pieces'>{total_pieces}</span></strong>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>


		<div id="add_shipment_wrapper_new">
			<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-x-8">
				<div class="imports">
					<x-label>
						{{ __('translation.Factory Tracking') }}
					</x-label>
					<x-input type="text" name="factory_tracking[]" value="{factory_tracking}"  id="factory_tracking"/>
					<x-input type="hidden" name="id[]" value="{id}"  id="id"/>
				</div>

				<div>
					<x-label>
						{{ __('translation.Number Of Cartons') }}
					</x-label>
					<x-input type="text" name="number_of_cartons[]" value="{number_of_cartons}" id="number_of_cartons" />
				</div>

				<div>
					<x-label>
						{{ __('translation.Ship Date') }}
					</x-label>
					<x-input type="text" name="ship_date[]" class="datepicker-1" value="{ship_date}" id="ship_date" />
				</div>

				<div>
					<x-label>
						{{ __('translation.Estimated Arrival Date') }} <span class="font-bold">{{ __('translation.From') }}</span>
					</x-label>
					<x-input type="text" name="e_a_d_f[]" value="{e_a_d_f}"  class="datepicker-1" id="e_a_d_f"  />
				</div>
				<div>
					<x-label>
						{{ __('translation.Estimated Arrival Date') }} <span class="font-bold">{{ __('translation.To') }}</span>
					</x-label>
					<x-input type="text" name="e_a_d_t[]" value="{e_a_d_t}" class="datepicker-1" id="e_a_d_t" />
				</div>

				<div>
					<x-label>
						{{ __('translation.Status') }}
					</x-label>

					<select name="status[]" id="status" class="block w-full h-10 px-2 py-2 rounded-md shadow-sm border border-solid border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 bg-white form-control">
                        <option value="open" {selected_open}>{{ __('translation.Open') }}</option>
                        <option value="arrive" {selected_arrive}>{{ __('translation.Arrive') }}</option>
                        <option value="close" {selected_close}>{{ __('translation.Close') }}</option>
					</select>
				</div>
			</div>

			<div class="imports">
				<div class="flex flex-row items-center justify-between mb-3 mt-3">
					<h2 class="block whitespace-nowrap text-gray-600 text-base font-bold">
						{{ __('translation.Cargo Information') }}
					</h2>
					<hr class="w-full ml-3 relative -top-1 border border-r-0 border-b-0 border-l-0 border-gray-300">
				</div>
				<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-x-8">
					<div>
						<x-label>
							<strong>{{ __('translation.Shipping Type') }} :</strong> <span id="label_shipping_type">{label_shipping_type}</span>
						</x-label>

						<x-label>
						<strong>{{ __('translation.Shipping Ref') }} :</strong> <span id="label_shipping_mark">{label_shipping_mark}</span>
						</x-label>

					</div>

					<div>
						<x-label>
							{{ __('translation.Cargo Reference') }}
						</x-label>
						<x-input type="text" name="cargo_ref[]"  value="{cargo_ref}" id="cargo_ref"  />
					</div>
				</div>
			</div>


			<div style="display: none">
				<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-x-8">
					<div>
						<x-label>
							{{ __('translation.Number Of Cartons') }}
						</x-label>
						<x-input type="text" name="number_of_cartons1[]" value="{number_of_cartons1}" id="number_of_cartons1" />
					</div>
				</div>
			</div>



			<div class="flex flex-row items-center justify-between mb-3 mt-3">
				<h2 class="block whitespace-nowrap text-gray-600 text-base font-bold">
					{{ __('translation.Last Mile') }}
				</h2>
				<hr class="w-full ml-3 relative -top-1 border border-r-0 border-b-0 border-l-0 border-gray-300">
			</div>

			<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-x-8">

				<div>
					<x-label>
						{{ __('translation.Domestic Shipper') }}
					</x-label>
					<select name="domestic_shipper_id[]" id="domestic_shipper_id"  style="width: 100%" class="block w-full h-10 px-2 py-2 rounded-md shadow-sm border border-solid border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 bg-white form-control">
					<option selected value="0">
							- {{ __('translation.All Domestic Shipper') }} -
						</option>
						@foreach ($domesticShippers as $domesticShipper)
							<option value="{{ $domesticShipper->id }}" {selected_{{$domesticShipper->id}}} >
								{{ $domesticShipper->name }}
							</option>
						@endforeach
					</select>
				</div>

				<div>
					<x-label>
						{{ __('translation.Domestic Tracking') }}
					</x-label>
					<x-input type="text" name="domestic_logistics[]" value="{domestic_logistics}" id="domestic_logistics"  />
				</div>

			</div>

			<div class="mt-5 flex flex-row items-center justify-between mb-3 mt-3">
				<h2 class="block whitespace-nowrap text-gray-600 text-base font-bold">
					{{ __('translation.Product') }}
				</h2>
				<hr class="w-full ml-3 relative -top-1 border border-r-0 border-b-0 border-l-0 border-gray-300">
			</div>

			<div class="mb-20">
				<div class="flex1 flex-row1 items-center justify-between mb-3">
					<div class="__wrapper_ShipmentProductList"></div>
					<div class="__wrapper_ShipNoProduct">
						<div class="w-full p-4 rounded-lg text-center">
							<span class="font-bold text-lg text-gray-500">
								--- No Shipping Item Added ---
							</span>
						</div>
					</div>
				</div>
			</div>


			<div class="__btnWrapper text-center pb-5">
				<x-button type="button" color="blue" class="__confirmNewShipment" is_edit="{is_edit}">
					{{ __('translation.Confirm') }}
				</x-button>
			</div>
		</div>



		<!-- <form> Tempate to load inside  </form> -->
		<!-- -->
		<div id="shipmentFormTemplateReadyForSave">
			<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-x-8">
				<div class="imports">
					<x-label>
						{{ __('translation.Factory Tracking') }}
					</x-label>
					<x-input type="text" name="factory_tracking[{id}]" value="{factory_tracking}" />
					<x-input type="hidden" name="po_shipment_id[{id}]"  value="{po_shipment_id}" />
				</div>

				<div>
					<x-label>
						{{ __('translation.Number Of Cartons') }}
					</x-label>
					<x-input type="text" name="number_of_cartons[{id}]" value="{number_of_cartons}"/>
				</div>

				<div>
					<x-label>
						{{ __('translation.Ship Date') }}
					</x-label>
					<x-input type="text" name="ship_date[{id}]" class="datepicker-1" value="{ship_date}" />
				</div>

				<div>
					<x-label>
						{{ __('translation.Estimated Arrival Date') }} <span class="font-bold">{{ __('translation.From') }}</span>
					</x-label>
					<x-input type="text" name="e_a_d_f[{id}]" value="{e_a_d_f}"  class="datepicker-1" />
				</div>
				<div>
					<x-label>
						{{ __('translation.Estimated Arrival Date') }} <span class="font-bold">{{ __('translation.To') }}</span>
					</x-label>
					<x-input type="text" name="e_a_d_t[{id}]" value="{e_a_d_t}" class="datepicker-1" />
				</div>

				<div>
					<x-label>
						{{ __('translation.Status') }}
					</x-label>

					<select name="status[{id}]" class="block w-full h-10 px-2 py-2 rounded-md shadow-sm border border-solid border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 bg-white form-control">
                        <option value="open">{{ __('translation.Open') }}</option>
                        <option value="arrive">{{ __('translation.Arrive') }}</option>
                        <option value="close">{{ __('translation.Close') }}</option>
					</select>
				</div>
			</div>

			<div class="imports">
				<div class="flex flex-row items-center justify-between mb-3 mt-3">
					<h2 class="block whitespace-nowrap text-gray-600 text-base font-bold">
						{{ __('translation.Cargo Information') }}
					</h2>
					<hr class="w-full ml-3 relative -top-1 border border-r-0 border-b-0 border-l-0 border-gray-300">
				</div>
				<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-x-8">
					<div>
						<x-label>
							<strong>{{ __('translation.Shipping Type') }} :</strong> <span id="label_shipping_type">****</span>
						</x-label>

						<x-label>
						<strong>{{ __('translation.Shipping Ref') }} :</strong> <span id="label_shipping_mark">*****</span>
						</x-label>

					</div>

					<div>
						<x-label>
							{{ __('translation.Cargo Reference') }}
						</x-label>
						<x-input type="text" name="cargo_ref[{id}]"  value="{cargo_ref}"   />
					</div>
				</div>
			</div>


			<div style="display: none">
				<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-x-8">
					<div>
						<x-label>
							{{ __('translation.Number Of Cartons') }}
						</x-label>
						<x-input type="text" name="number_of_cartons1[{id}]" value="{number_of_cartons1}"  />
					</div>
				</div>
			</div>



			<div class="flex flex-row items-center justify-between mb-3 mt-3">
				<h2 class="block whitespace-nowrap text-gray-600 text-base font-bold">
					{{ __('translation.Last Mile') }}
				</h2>
				<hr class="w-full ml-3 relative -top-1 border border-r-0 border-b-0 border-l-0 border-gray-300">
			</div>

			<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-x-8">

				<div>
					<x-label>
						{{ __('translation.Domestic Shipper') }}
					</x-label>
					<x-select name="domestic_shipper_id[{id}]" style="width: 100%">
					<option value="0">
							- {{ __('translation.All Domestic Shipper') }} -
						</option>
						@foreach ($domesticShippers as $domesticShipper)
							<option value="{{ $domesticShipper->id }}" {ds_selected_{{ $domesticShipper->id }}}>
								{{ $domesticShipper->name }}
							</option>
						@endforeach
					</x-select>
				</div>

				<div>
					<x-label>
						{{ __('translation.Domestic Tracking') }}
					</x-label>
					<x-input type="text" name="domestic_logistics[{id}]" value="{domestic_logistics}"  />
				</div>

			</div>

			<div class="mt-5 flex flex-row items-center justify-between mb-3 mt-3">
				<h2 class="block whitespace-nowrap text-gray-600 text-base font-bold">
					{{ __('translation.Product') }}
				</h2>
				<hr class="w-full ml-3 relative -top-1 border border-r-0 border-b-0 border-l-0 border-gray-300">
			</div>
		</div>




		<div id="loadTemplateShippedProductQty" class="mb-4 lg:mb-2 " style="border: 1px solid #e6e6e6;padding:20px;">
			<label class="mb-0">
				{{ __('translation.Shipped') }} (Packs) :
			</label>
			<div class="w-full md:w-full md:pr-2">
				<x-input class="mb-2" type="number" name="ship_quantity[{id}][{product_id}]"  value="{load_ship_quantity}"  />
			</div>
		</div>
	</div>


    @push('bottom_js')
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/typeahead.js@0.11.1/dist/typeahead.bundle.min.js"></script>
        <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery-datatables-checkboxes@1.2.12/js/dataTables.checkboxes.min.js"></script>
        <script>
            const productSource = {!! $products->toJson() !!};
            const supplyFromImport = {{ \App\Models\OrderPurchase::SUPPLY_FROM_IMPORT }};;
            const supplyFromDomestic = {{ \App\Models\OrderPurchase::SUPPLY_FROM_DOMESTIC }};
             // This will load DataTable On PO Create Page

            window.localStorage.removeItem('newPOProducts'); // Clear on Page Reload
            window.localStorage.removeItem('newShippingDetails'); // Clear on Page Reload

            var selectedProductsToList = [];
            $(window).on('load', function() {
                $('#__domesticSupplyFrom').prop('checked', false);
                $('#__importSupplyFrom').prop('checked', true);
                $('#po-info .imports').find('input').attr('required', true);
                $('#po-info .domestic').find('input').attr('required', false);
                $('#__productCodeSearch').typeahead('destroy');
                $('#__productCodeSearch').val(null);
                $('#__formPurchaseOrder')[0].reset();
            });
            $('#shipping_type_id').select2({
                width: 'resolve'
            });
            $('#__selectSupplierFilter').select2({
                width: 'resolve'
            });

            $(".datepicker-1").datepicker({
                dateFormat: 'dd-mm-yy'
            });
            $('#__btnClose_modalAlert').click(function() {
                $('#__modalAlert').addClass('hidden');
                $('body').removeClass('modal-open');
            });
            $('input[name="check"]').on('change', function() {
                let selectedValue = $('input[name="check"]:checked').val();
                let factory_tracking = $('#factory_tracking');
                let cargo_ref = $('#cargo_ref');
                let number_of_cartons = $('#number_of_cartons');
                let domestic_logistics = $('#domestic_logistics');
                let number_of_cartons1 = $('#number_of_cartons1');
                let domestic_logistics1 = $('#domestic_logistics1');
                if (selectedValue == supplyFromImport) {
                    $('.domestic').hide();
                    $('.imports').show();
                    $('#po-info .imports').find('input').attr('required', true);
                    $('#po-info .domestic').find('input').attr('required', false);
                }
                if (selectedValue == supplyFromDomestic) {
                    $('.imports').hide();
                    $('.domestic').show();
                    $('#po-info .domestic').find('input').attr('required', true);
                    $('#po-info .imports').find('input').attr('required', false);
                }
            });
            $(document).ready(function() {
                let imports = $('.imports');
                let domestic = $('.domestic');
                let factory_tracking = $('#factory_tracking');
                let cargo_ref = $('#cargo_ref');
                let number_of_cartons = $('#number_of_cartons');
                let domestic_logistics = $('#domestic_logistics');
                let number_of_cartons1 = $('#number_of_cartons1');
                let domestic_logistics1 = $('#domestic_logistics1');
                let getVal = $('.getSupplier_from').val();
                if (getVal == 1) {
                    $('#checkin').prop('checked', true);
                    if ($(imports).hasClass('hide')) {
                        $(imports).removeClass('hide');
                        $(imports).addClass('show');
                    }
                    if ($(domestic).hasClass('show')) {
                        $(domestic).removeClass('show');
                        $(domestic).addClass('hide');
                    }
                }
                if (getVal == 2) {
                    $('#checkout').prop('checked', true);
                    if ($(imports).hasClass('show')) {
                        $(imports).removeClass('show');
                        $(imports).addClass('hide');
                    }
                    if ($(domestic).hasClass('hide')) {
                        $(domestic).removeClass('hide');
                        $(domestic).addClass('show');
                    }
                }
            });
        </script>
        <script>
            const substringMatcher = function(strs) {
                return function findMatches(q, cb) {
                    var matches, substringRegex;
                    matches = [];
                    substrRegex = new RegExp(q, 'i');
                    $.each(strs, function(i, str) {
                        if (substrRegex.test(str)) {
                            matches.push(str);
                        }
                    });
                    cb(matches);
                };
            };


            var arrProductAdded = [];
			$("#__btnLoadProducts").click(function(){
                var checked_rows = $('input.dt-checkboxes:checkbox:checked').parents("tr");
                $.each(checked_rows, function (key, val) {
					selectedProductsToList.push("'"+$(this).attr('data-product_code')+"'");

                });

				renderProductToList(selectedProductsToList.join(','));



                $('.modal-products').addClass('modal-hide');
                $('body').removeClass('modal-open');
                return false;
            });


			// This will fetch and store product details into local Storage
            const renderProductToList = productCodes => {
                if (productCodes !== '') {
						$.ajax({
							type: 'GET',
							data: {
								productCodes: productCodes,
								order_purchase_id: $("#order_purchase_id").val(),
							},
							url: '{{ route('get_product_wise_po_shipping_info') }}',
							success: function(responseJson) {
								$('#__wrapper_NoProduct').hide();
								let templateProductItemElement = $('#__templateProductItem').clone();
								let products = responseJson.data;

								//Set Data into Local Storage
                                localStorage.setItem('newPOProducts', JSON.stringify(products));
								//Set products inside the edit form to submit
                                displayProductsIntoToCreateForm(products);

							},
							error: function(error) {
								let responseJson = error.responseJSON;
								$('#__modalAlert').removeClass('hidden');
								$('body').addClass('modal-open');
								$('#__content_modalAlert').html(null);
								$('#__content_modalAlert').html(responseJson.message);
							}
						});
					}
            }




            const renderProductToShippingForm = (productCodes,shipmentDetails) => {
                if (productCodes !== '') {
						$.ajax({
							type: 'GET',
							data: {
								productCodes: productCodes,
								order_purchase_id: $("#order_purchase_id").val(),
							},
							url: '{{ route('get_product_wise_po_shipping_info') }}',
							success: function(responseJson) {
								$('#__wrapper_NoProduct').hide();
								let products = responseJson.data;

                                renderShippingProducts(null,products,shipmentDetails);

							},
							error: function(error) {
								let responseJson = error.responseJSON;
								$('#__modalAlert').removeClass('hidden');
								$('body').addClass('modal-open');
								$('#__content_modalAlert').html(null);
								$('#__content_modalAlert').html(responseJson.message);
							}
						});
					}
            }





            renderProductToList(selectedProductsToList.join(','));

            // This will generate a  product template when select & load products //
			// You will find this template by clicking TAB "PO Information" > "Add Products" //

            const displayProductsIntoToCreateForm = (all_products) => {

                $.each(all_products, function (key, product) {
                    $('#__wrapper_NoProduct').hide();



                    let templateProductItemElement = $('#__templateProductItem').clone();

                        templateProductItemElement.html(function(index, html) {
                            return html.replaceAll('{product_img}', product.image);
                        });
                        templateProductItemElement.html(function(index, html) {
                            return html.replaceAll('{product_id}', product.id);
                        });
                        templateProductItemElement.html(function(index, html) {
                            return html.replaceAll('{product_name}', product.product_name);
                        });
                        templateProductItemElement.html(function(index, html) {
                            return html.replaceAll('{product_code}', product.product_code);
                        });
                        templateProductItemElement.html(function(index, html) {
                            return html.replace('{price}', product.price);
                        });
                        templateProductItemElement.html(function(index, html) {
                            return html.replace('{pieces_per_pack}', product.pack);
                        });
                        templateProductItemElement.html(function(index, html) {
                            var pieces = parseInt(product.pack) * parseInt(product.order_packs);
                            return html.replace('{pieces_per_pack_label}', pieces);
                        });
                        templateProductItemElement.html(function(index, html) {
                            return html.replace('{pieces_per_carton}', product.pieces_per_carton_for_default_supplier);
                        });
                        templateProductItemElement.html(function(index, html) {
                            return html.replace('{cost_per_piece}', product.product_cost ? product.product_cost : 0);
                        });
                        templateProductItemElement.html(function(index, html) {
                                return html.replace('{selected_'+product.price_exchange_rate_id+'}','selected');
                        });


                        templateProductItemElement.html(function(index, html) {
                            return html.replace('{product_price}', product.product_price ? product.product_price : 0);
                        });



                        templateProductItemElement.html(function(index, html) {
                            let product_cost = product.product_cost ? product.product_cost :0;
                            let default_cost_currency = product.default_cost_currency ? product.default_cost_currency : '';
                            let label = "Default: "+product_cost +" "+default_cost_currency;
                            return html.replace('{default_cost_currency}', label);
                        });


                        templateProductItemElement.html(function(index, html) {
                           //let total_available_qty = 0;
                           // if(undefined !== product.total_available_qty){
                                //let total_available_qty = product.total_available_qty;
                           // }
                            return html.replace('{available_qty}',  product.stock_quantity);
                        });

                        templateProductItemElement.html(function(index, html) {
                            var supplier_name = '';
                            if(undefined !== product.supplier_name){
                                var supplier_name = product.supplier_name;
                            }
                            return html.replace('{supplier_name}', supplier_name );
                        });
                        templateProductItemElement.html(function(index, html) {
                            let current_status ='';
                            let stock_quantity = product.stock_quantity ? product.stock_quantity : 0;
                            let alert_stock = product.alert_stock ? product.alert_stock : 0;
                            if(alert_stock > 0 && stock_quantity <=0) {
                                current_status = 'Out Of Stock';
                            }
                            if(alert_stock > 0 && (stock_quantity <= alert_stock && stock_quantity > 0)){
                                current_status = 'Low Stock';
                            }
                            if(alert_stock > 0 && stock_quantity > alert_stock ) {current_status = 'Over Stock';}
                            if(alert_stock=='') {current_status = 'N/A';}

                            return html.replace('{current_status}', current_status);
                        });

                        templateProductItemElement.html(function(index, html) {
                            var total_incoming = 1;
                            if(parseInt(product.total_incoming) > 0 ){ total_incoming = product.total_incoming;}
                            return html.replaceAll('{total_incoming}', total_incoming);
                        });
                        templateProductItemElement.html(function(index, html) {
                            let arrReorderStatus =[];
                            let arrReorderQty = [];
                            let arrReorderShipType = [];
                            if(product.reorder_status){
                                 arrReorderStatus = product.reorder_status.split(",");
                            }
                            if(product.reorder_shiptype){
                                arrReorderShipType = product.reorder_shiptype.split(",");
                            }

                            if(product.reorder_qty){
                                arrReorderQty = product.reorder_qty.split(",");
                            }
                            if(product.reorder_status){
                                var reorder_str = ' ';
                                $.each(arrReorderStatus, function (key, val) {
                                    var reorder_status = arrReorderStatus[key].replace("_", ' ');
                                    reorder_status = reorder_status.replace("_", ' ');
                                    reorder_ship_type = arrReorderShipType[key] ? arrReorderShipType[key].replace("_", ' ') : ' ';
                                    reorder_str +="<p style='text-transform: capitalize;'>"+reorder_status+" ("+ reorder_ship_type +") "+arrReorderQty[key]+"</p>";
                                });
                            }
                            return html.replace('{reorder}', reorder_str ? reorder_str : '' );
                        });

                        templateProductItemElement.html(function(index, html) {
                            return html.replace('{order_packs}', product.order_packs ? product.order_packs : '' );
                        });

                        // Restrict Duplict Item to add
                        if(jQuery.inArray(product.product_code, arrProductAdded) == -1){
                            $('#__wrapper_ProductList').prepend(templateProductItemElement.html());
                            arrProductAdded.push(product.product_code);
                        }


				});
            }




            // This will generate a shipping products template when select & load products //
			// You will find this template by clicking TAB "Shipment Details" > "Add Shipment" //

			renderShippingProducts = (id,products,shipmentDetails) => {

                $('.__wrapper_ShipmentProductList').html(null);

                $.each(products, function (key, product) {
                    //console.log(products);
                   $('.__wrapper_ShipNoProduct').hide();
                   var templateShippedProductItemElement = $('#__templateShippedProductItem').clone();

                   templateShippedProductItemElement.html(function(index, html) {
                       return html.replaceAll('{product_img}', product.image);
                   });

                   templateShippedProductItemElement.html(function(index, html) {
                       return html.replaceAll('{product_name_shipped}', product.product_name);
                   });

                   templateShippedProductItemElement.html(function(index, html) {
                       return html.replaceAll('{shipping_product_id}', product.id);
                   });

                   templateShippedProductItemElement.html(function(index, html) {
                       return html.replaceAll('{product_id_shipped}', product.id);
                   });

                   templateShippedProductItemElement.html(function(index, html) {
                       return html.replaceAll('{product_code_shipped}', product.product_code);
                   });

                   // GET SHIP QUANTITY IF HAVE PO SHIPMENT ID
                   // GET SHIP QUANTITY IF HAVE PO SHIPMENT ID
                   var po_shipment_id = id;
                   var ship_quantity = 0;
                    $.each(shipmentDetails, function (key, detail) {
                        if(detail.product_id==product.id)
                        ship_quantity = detail.ship_quantity;
                    });

                   var order_qty = $(".order-qty__field_"+product.product_code).val();

                   templateShippedProductItemElement.html(function(index, html) {
                       return html.replace('{shipped_quantity}', parseInt(product.total_shipped));
                   });

                   templateShippedProductItemElement.html(function(index, html) {
                       return html.replace('{total_shipped}', parseInt(product.total_shipped));
                   });


                   var available_shipped_qty = parseInt(parseInt(order_qty)- parseInt(product.total_shipped));

                   templateShippedProductItemElement.html(function(index, html) {
                       return html.replaceAll('{available_shipped_qty}', available_shipped_qty);
                   });

                   templateShippedProductItemElement.html(function(index, html) {
                       return html.replace('{order_qty}', order_qty);
                   });

                   templateShippedProductItemElement.html(function(index, html) {
                       return html.replace('{shipQtyOld}', ship_quantity);
                   });

                   templateShippedProductItemElement.html(function(index, html) {
                       return html.replace('{shipQty}', ship_quantity);
                   });

                   templateShippedProductItemElement.html(function(index, html) {
                       return html.replace('{pack}', product.pack ? product.pack : 0);
                   });


                   templateShippedProductItemElement.html(function(index, html) {
                      var total_pieces = ship_quantity * product.pack;
                       return html.replace('{total_pieces}', total_pieces ? total_pieces : 0);
                   });


                   $('.__wrapper_ShipmentProductList').prepend(templateShippedProductItemElement.html());
                });

            }



            var arrProductCodeQty = [];
            //Generate Ship Quantity BY PO Purchase ID & Product ID
            const getTotalShippedQtyByPOIDAndProductID = (product_code,order_purchase_id,old_qty,current_qty) => {

                $.ajax({
                    type: 'GET',
                    data: {
                        productCode: "'"+product_code+"'",
                        order_purchase_id: order_purchase_id,
                    },
                    url: '{{ route('get_po_shipment_details_by_order_purchase_id') }}',
                    success: function(responseJson) {
                        var total_shipped_qty = parseInt(responseJson.po_shipment_details[0].total_shipped) - parseInt(old_qty) + parseInt(current_qty);
                        var order_qty = parseInt($("#order_qty_"+product_code).html());

                        let available_qty = parseInt(order_qty) - parseInt(total_shipped_qty);
                         let max = parseInt(available_qty) + parseInt(current_qty);

                        $("#total_shipped_qty_"+product_code).html(total_shipped_qty);
                        $("#available_qty_"+product_code).html(available_qty);
                        $("#ship_quantity_"+product_code).attr('max',max);
                        if(parseInt(available_qty) <= '0'){
                            $("#ship_quantity_"+product_code).val(max);
                            $("#total_shipped_qty_"+product_code).html(order_qty);
                            $("#available_qty_"+product_code).html(0);
                            $("#ship_quantity_"+product_code).attr('max',max);
                        }


                        return false;
                    },
                    error: function(error) {
                        let responseJson = error.responseJSON;

                    }
                });


                var newShippingDetails = JSON.parse(localStorage.getItem("newShippingDetails"));
                var total_shipped = 0;
                $.each(newShippingDetails, function (key, shipping) {
                    $.each(shipping.po_shipment_details, function (key, detail) {
                        if(detail.product_id==product_id)
                        total_shipped += parseInt(detail.ship_quantity);
                        });
                });
                return total_shipped;
            }





			$(document).on('click', '#__btnAddShippment', function() {

                $(".modal-shipment #LoadShipmentForm").html(null);
				var templateShipmentNewForm = $('#add_shipment_wrapper_new').clone();


                templateShipmentNewForm.html(function(index, html) {
                       return html.replace('{factory_tracking}', '');
                });

                templateShipmentNewForm.html(function(index, html) {
                       return html.replace('{number_of_cartons}', ' ');
                });

                templateShipmentNewForm.html(function(index, html) {
                       return html.replace('{ship_date}', ' ');
                });

                templateShipmentNewForm.html(function(index, html) {
                       return html.replace('{e_a_d_f}', ' ');
                });

                templateShipmentNewForm.html(function(index, html) {
                       return html.replace('{e_a_d_t}', ' ');
                });

                templateShipmentNewForm.html(function(index, html) {
                       return html.replace('{cargo_ref}', ' ');
                });

                templateShipmentNewForm.html(function(index, html) {
                    var label_shipping_type = " ***";
                    if($("#agent_cargo_id option:selected" ).val() > 0){
                        label_shipping_type = $("#agent_cargo_id option:selected" ).text();
                    }
                    return html.replace('{label_shipping_type}', label_shipping_type);
                });

                templateShipmentNewForm.html(function(index, html) {
                    var shipping_mark_text = " ***";
                    if($("#shipping_mark_id option:selected" ).val() > 0){
                        shipping_mark_text = $("#shipping_mark_id option:selected" ).text();
                    }

                    return html.replace('{label_shipping_mark}', shipping_mark_text);
                });


                templateShipmentNewForm.html(function(index, html) {
                       return html.replace('{domestic_logistics}', '');
                });


				// This will fix error and run the datetpicker into modal form
				templateShipmentNewForm.find('input.datepicker-1').removeClass('hasDatepicker').datepicker({
					dateFormat: 'dd-mm-yy'
				});

                //renderShippingProducts(null,null);
                renderProductToShippingForm(selectedProductsToList.join(','),null);

			    $(".modal-shipment #LoadShipmentForm").html(templateShipmentNewForm);

				$('.modal-shipment').removeClass('modal-hide');
				$('body').addClass('modal-open');
            });




           // This will load Shipment Form to Edit
           $(document).on('click', '.__BtnEditShipment', function() {
                var id = $(this).attr('data-id');
                var editShippingDetails = [];
                    $.ajax({
                        url: '{{ route('edit_po_shipment') }}',
                        type: 'post',
                        data: {
                            'id': $(this).data('id'),
                            '_token': $('meta[name=csrf-token]').attr('content')
                        }
                    }).done(function(result) {

                        editShippingDetails = result;

                        // Edit Shipment Form
				$('#add_shipment_wrapper_new').removeClass("hide");


                $(".modal-shipment #LoadShipmentForm").html(" ");

				$('#add_shipment_wrapper_new').removeClass("hide");
				var templateShipmentEditForm = $('#add_shipment_wrapper_new').clone();

                templateShipmentEditForm.html(function(index, html) {
                       return html.replace('{id}', editShippingDetails.id);
                });

                templateShipmentEditForm.html(function(index, html) {
                       return html.replace('{factory_tracking}', editShippingDetails.factory_tracking);
                });

                templateShipmentEditForm.html(function(index, html) {
                       return html.replace('{number_of_cartons}', editShippingDetails.number_of_cartons);
                });

                templateShipmentEditForm.html(function(index, html) {
                       return html.replace('{ship_date}', editShippingDetails.ship_date);
                });

                templateShipmentEditForm.html(function(index, html) {
                       return html.replace('{e_a_d_f}', editShippingDetails.e_a_d_f);
                });

                templateShipmentEditForm.html(function(index, html) {
                       return html.replace('{e_a_d_t}', editShippingDetails.e_a_d_t);
                });


                templateShipmentEditForm.html(function(index, html) {
                    return html.replace('{selected_'+editShippingDetails.status+'}', "selected");
                });

                templateShipmentEditForm.html(function(index, html) {
                        return html.replace('{selected_'+editShippingDetails.domestic_shipper_id+'}','selected');
                });

                templateShipmentEditForm.html(function(index, html) {
                       return html.replace('{cargo_ref}', editShippingDetails.cargo_ref);
                });

                templateShipmentEditForm.html(function(index, html) {
                       return html.replace('{domestic_logistics}', editShippingDetails.domestic_logistics);
                });

                templateShipmentEditForm.html(function(index, html) {
                       return html.replace('{is_edit}', true);
                });


				// This will fix error and run the datetpicker into modal form
				templateShipmentEditForm.find('input.datepicker-1').removeClass('hasDatepicker').datepicker({
					dateFormat: 'dd-mm-yy'
				});

			    $(".modal-shipment #LoadShipmentForm").html(templateShipmentEditForm);

                var shipmentDetails = editShippingDetails.po_shipment_details;

				renderProductToShippingForm(selectedProductsToList.join(','),shipmentDetails);

				$('.modal-shipment').removeClass('modal-hide');
				$('body').addClass('modal-open');


                });


            });





            // Handle to Export as a excel file
            $(document).on('click', '.__confirmNewShipment', function(event) {


                var is_edit = $(this).attr('is_edit');

                var order_purchase_id = $('#order_purchase_id').val();
                var id = $('#id').val();
                var supplier_id = $('#supplier_id').val();

                var order_date = $('#order_date').val();
                var shipping_type_id = $('#shipping_type_id').val();
                var shipping_mark_id = $('#shipping_mark_id').val();
                var agent_cargo_id = $('#agent_cargo_id').val();
                var factory_tracking = $('#factory_tracking').val();
                var number_of_cartons = $('#number_of_cartons').val();
                var ship_date = $('#ship_date').val();
                var e_a_d_f = $('#e_a_d_f').val();
                var e_a_d_t = $('#e_a_d_t').val();
                var status = $('#status').val();
                var cargo_ref = $('#cargo_ref').val();
                var domestic_shipper_id = $('#domestic_shipper_id').val();
                var domestic_logistics = $('#domestic_logistics').val();
                if(!supplier_id || (supplier_id == 0) || (supplier_id == undefined)){
                    alert("Supplier Name is Required");
                    return false;
                }


                var ship_quantites = $(".modal-shipment .ship_quantity");

                var product_code = null;
                var ship_quantity = null;
                var arr_po_shipment_details = [];
                for(var i = 0; i < ship_quantites.length; i++){
                    product_id = $(ship_quantites[i]).attr('data-product_id');
                    product_code = $(ship_quantites[i]).attr('data-code');
                    ship_quantity = $(ship_quantites[i]).val();

                    ship_qty_by_code = {
							product_id : product_id,
							product_code : product_code,
                            ship_quantity : ship_quantity
                    }
                    arr_po_shipment_details.push(ship_qty_by_code);
                }

                $.ajax({
                       type: 'POST',
                        url: '{{ route('add_po_shipment') }}',
                        data: {
                            id:id,
                            order_purchase_id:order_purchase_id,
                            order_date: order_date,
                            supplier_id : supplier_id,
                            factory_tracking: factory_tracking,
                            number_of_cartons: number_of_cartons,
                            ship_date: ship_date,
                            e_a_d_f: e_a_d_f,
                            e_a_d_t: e_a_d_t,
                            status: status,
                            cargo_ref: cargo_ref,
                            domestic_shipper_id: domestic_shipper_id,
                            domestic_logistics: domestic_logistics,
                            arr_po_shipment_details : arr_po_shipment_details
                        },
                    beforeSend: function() {

                        $('#wrapper').html('processing...');
                        //$('.modal-shipment').removeClass('modal-hide');
                    }
                }).done(function(result) {
                    alert("Shipment has been updated successfully");
                    loadShipmentDataTable(result.po_shipments);
                    $('.modal-shipment').addClass('modal-hide');
				    $('body').removeClass('modal-open');

                });
            });





            $(document).on('click', '.__BtnDeleteShipment', function() {
                let drop = confirm('Are you sure?');
                if (drop) {
                    var id = $(this).attr('data-id');
                    //remove from data table
                    $(this).closest('tr').remove();
                    //remove data from submit data
                    $('#'+id).remove();

                    $.ajax({
                        url: '{{ route('delete_po_shipment') }}',
                        type: 'post',
                        data: {
                            'id': $(this).data('id'),
                            '_token': $('meta[name=csrf-token]').attr('content')
                        }
                    }).done(function(result) {
                        if (result.status === 1) {
                            alert('Data deleted successfully');
                        } else {
                            alert(result.message);
                        }
                    });
                }
            });


            const removeProductItem = el => {
                const productCode = el.getAttribute('data-code');
                selectedProductsToList.splice(selectedProductsToList.indexOf(productCode), 1);
                arrProductAdded.splice(selectedProductsToList.indexOf(productCode), 1);
                $(`.__row_ProductItem_${productCode}`).remove();
                if (selectedProductsToList.length === 0) {
                    $('#__wrapper_NoProduct').show();
                }
            }




            $(document).on('keyup', '.order-qty__field', function(event) {

                let min = $(this).attr('min');

                // Check if gether than max, else revert back to max value.
                if ( !$(this).val() || ($(this).val() == undefined)){
                    $(this).val(min);
                }



                let pieces_per_pack = parseInt($("#pieces_per_pack").html());
                let pack = parseInt($(this).val());
                let pieces  = pack*pieces_per_pack;
                $(this).next().find(".res_pieces").html(pieces);
                let product_code = $(this).data('code');
                $("#order_qty_"+product_code).html(pack);

            });



            $(document).on('keyup', '.ship_quantity', function(event) {
                let order_purchase_id = $("#order_purchase_id").val();
                let product_code = $(this).data('code');
                let product_id = $(this).data('product_id');
                let old_qty = $(this).data('old-qty');
                let current_qty = $(this).val();
                let max = $(this).attr('max') - parseInt($(this).val());


                // Check if empty then set 0.
                if ($(this).val() == "" || $(this).val()== undefined){
                    current_qty = 0;
                }
               getTotalShippedQtyByPOIDAndProductID(product_code,order_purchase_id,old_qty,current_qty);

               let qty = parseInt($(this).val());
               let pack = parseInt($(this).attr('pack'));
                let pieces  = pack*qty;
               $(this).next().find(".res_pieces").html(pieces);

            });


            $('#__btnClearProductList').click(function() {
                selectedProductsToList = [];
                $('#__wrapper_ProductList').html(null);
                $('#__wrapper_NoProduct').show();
            });




            $('#__formPurchaseOrder').submit(function(event) {
                event.preventDefault();
                let formData = new FormData($(this)[0]);
                $.ajax({
                    type: $(this).attr('method'),
                    url: $(this).attr('action'),
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        $('#__btnSubmitPurchaseOrder').attr('disabled', true).html('{{ __('translation.Processing') }}');
                        $('.alert').addClass('hidden');
                    },
                    success: function(responseJson) {
                        $('#__btnSubmitPurchaseOrder').attr('disabled', false).html('{{ __('translation.Submit Data') }}');
                        //console.log(responseJson);
                        $('html, body').animate({
                            scrollTop: 0
                        }, 500);
                        $('.alert').addClass('hidden');
                        $('#__alertSuccess').removeClass('hidden');
                        $('#__content_alertSuccess').html(null);
                        $('#__content_alertSuccess').html(responseJson.message);

                        setTimeout(() => {
                            window.location.href = '{{ route('order_purchase.index') }}';
                        }, 1500);
                    },
                    error: function(response) {
                        let responseJson = response.responseJSON;
                        $('#__btnSubmitPurchaseOrder').attr('disabled', false).html('{{ __('translation.Submit Data') }}');
                        $('.alert').addClass('hidden');
                        $('#__alertDanger').removeClass('hidden');
                        $('#__content_alertDanger').html(null);
                        $('html, body').animate({
                            scrollTop: 0
                        }, 500);
                        if (response.status == 422) {
                            let errorFields = Object.keys(responseJson.errors);
                            errorFields.map(field => {
                                $('#__content_alertDanger').append(
                                    $('<div/>', {
                                        html: responseJson.errors[field][0],
                                        class: 'mb-2'
                                    })
                                );
                            });
                        }
                        else {
                            $('#__content_alertDanger').html(responseJson.message);
                        }
                    }
                });
                return false;
            });


            $(document).on('change', '#shipping_type_id', function(event) {
                event.preventDefault();
                let shipping_type_id = $(this).val();
                let shipping_type_text = $("#shipping_type_id option:selected" ).text();
                $("#label_shipping_type").html(shipping_type_text);
                renderShippingMarkDropdown(shipping_type_id);
                return false;
            });
            $(document).on('change', '#shipping_mark_id', function(event) {
                event.preventDefault();
                let shipping_mark_text = $("#shipping_mark_id option:selected" ).text();
                $("#label_shipping_mark").html(shipping_mark_text);
                return false;
            });
            const renderShippingMarkDropdown = ClickValue => {
                ship_type_id = ClickValue;
                if (ship_type_id !== '') {
                    $.ajax({
                        type: 'GET',
                        data: {
                            ship_type_id: ship_type_id
                        },
                        url: '{{ route('get_shipping_mark_by_shipping_type_id') }}',
                        success: function(responseJson) {
                            $('#__wrapper_NoProduct').hide();
                            $('#shipping_mark_id').html(responseJson);
                            $('#shipping_mark_id').select2({
                                width: 'resolve'
                            });
                        },
                        error: function(error) {
                            let responseJson = error.responseJSON;
                            $('#__modalAlert').removeClass('hidden');
                            $('body').addClass('modal-open');
                            $('#__content_modalAlert').html(null);
                            $('#__content_modalAlert').html(responseJson.message);
                        }
                    });
                }
            }
        </script>

        <script>
            const productDataTableUrl = '{{ route('data_product_analysis') }}';
            var table;
            const loadProductDataTable = (supplierId,status, categoryId) => {
                table = $('#datatable').DataTable({
                    processing: true,
                    serverSide: true,
                    bDestroy: true,
                    ajax: {
                        type: 'GET',
                        url: productDataTableUrl,
                        data: {
                            supplierId: supplierId,
                            status: status,
                            categoryId: categoryId
                        },
                        dataSrc: function ( json ) {
                            return json.data;
                        }
                    },
                    columns: [
                        {
                            name: 'checkbox',
                            data: 'checkbox'
                        },
                        {
                            name: 'id',
                            data: 'id'
                        },
                        {
                            name: 'image',
                            data: 'image'
                        },
                        {
                            name: 'product_name',
                            data: 'product_name'
                        },
                        {
                            name: 'product_code',
                            data: 'product_code'
                        },
                        {
                            name: 'quantity',
                            data: 'quantity'
                        },
                        {
                            name: 'incoming',
                            data: 'incoming'
                        },
                        {
                            name: 'alert_stock',
                            data: 'alert_stock'
                        },
                        {
                            name: 'reorder_qty',
                            data: 'reorder_qty'
                        },
                        {
                            name: 'supplier_name',
                            data: 'supplier_name'
                        },
                        {
                            name: 'report_status',
                            data: 'report_status'
                        },
                    ],
                    createdRow: function( row, data, dataIndex ) {
                        // Set the data-product_id attribute
                        $(row).attr('data-product_code', data.product_code);
                    },
                    columnDefs: [
                        {
                            'targets': 0,
                            'checkboxes': {
                                'selectRow': true
                            }
                        },
                        { 'visible': false, 'targets':1},
                        {
                            targets: [1, 8],
                            orderable: false
                        },
                        {
                            targets: [4, 5, 6, 7],
                            className: 'text-center'
                        },
                        { "width": "22%", "targets": 7 },
                        { "width": "23%", "targets": 8 },
                    ],
                    select: {
                        style: 'multiple',
                    },
                    bDeferRender: true,
                    paginationType: 'numbers'
                });
            }

            const loadShipmentDataTable = (po_shipments) => {

                var t = $('#datatable_shipment').DataTable().clear();

                $.each(po_shipments, function (key, item) {
                    //console.log(item);
                    var order_no ='--';
                    var supplier_name ='--';
                    if(item.order_purchase_id){
                        order_no = item.order_purchase_id;
                    }


                   if(undefined !== item.supplier.supplier_name){
                        supplier_name = item.supplier.supplier_name;
                    }

                    var today = new Date();
                    var dd = String(today.getDate()).padStart(2, '0');
                    var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
                    var yyyy = today.getFullYear();

                    today = yyyy + '-' + mm + '-' + dd;

                    var created_at =  today;
                    if(item.created_at){
                        created_at = item.created_at.split('T')[0];
                    }

                    //console.log(item);


                    var order_date = '<span class="whitespace-nowrap cursor-pointer">';
                            order_date += '<a href="#"class="underline text-blue-500 font-bold">Order No: # --</a><br>';
                            order_date += ' Shipment ID : <strong>'+item.id+'</strong><br>';
                            order_date += ' Order Date : <strong>'+item.order_date+'</strong><br>';
                            order_date += ' Created : <strong>'+created_at+' </strong><br>';
                    var estimatedArriveDate ='<span class="whitespace-nowrap cursor-pointer hover:text-blue-500"> From: <strong>'+ item.e_a_d_f+'</strong></span><br> ';
                        estimatedArriveDate +='<span class="whitespace-nowrap cursor-pointer hover:text-blue-500"> To: <strong>'+ item.e_a_d_t+'</strong></span><br> ';

                    var details = '<span class="whitespace-nowrap cursor-pointer hover:text-blue-500">';
                    details += 'Supplier Name: <strong>'+supplier_name+'</strong></span><br>';
                    details += '<span class="whitespace-nowrap cursor-pointer hover:text-blue-500">';
                    details += 'Type: <strong>Import</strong></span><br>';
                    details += '<span class="whitespace-nowrap cursor-pointer hover:text-blue-500">';
                    details += 'No. Cartons: <strong>'+item.number_of_cartons+'</strong></span><br>';
                    details += '<span class="whitespace-nowrap cursor-pointer hover:text-blue-500">';
                    details += 'Domestic Logistics: <strong>'+item.domestic_logistics+'</strong></span><br>';


                    var status = '<span class="bg-red-200 text-red-700 text-xs px-2 rounded-md">'+ item.status +'</span>';
                    if (item.status == 'arrive') {
                        status = '<span class="bg-green-200 text-green-700 text-xs px-2 rounded-md">'+ item.status +'</span>';
                    }
                    if (item.status == 'open') {
                        status = '<span class="bg-green-200 text-green-700 text-xs px-2 rounded-md">'+ item.status +'</span>';
                    }
                    if (item.status == 'close') {
                        status = '<span class="bg-yellow-200 text-yellow-700 text-xs px-2 rounded-md">'+ item.status +'</span>';
                    }


                    var action = '<div class="pt-2 mb-0 ">';
                    action += '<button type="button" class="modal-open btn-action--green __BtnEditShipment" x-on:click="showEditModal=true" data-id="'+item.id+'">';
                    action += '<i class="fas fa-pencil-alt"></i></button>';

                    action += '<button type="button" class="btn-action--red __BtnDeleteShipment" data-id="'+item.id+'">';
                    action += '<i class="fas fa-trash-alt"></i></button> </div>';

                    t.row.add( [
                        order_date,
                        estimatedArriveDate,
                        details,
                        status,
                        action,
                    ] ).draw(false);

                });

                $("#datatable_shipment").removeClass("hide");
            }


            $('.modal-products').addClass('modal-hide');
            $('body').removeClass('modal-open');
            $("#__btnAddProducts").click(function(){
                $('.modal-products').removeClass('modal-hide');
                $('body').addClass('modal-open');
            });



            let supplierId = $('#__selectSupplierFilter').val();

            let status = $('#__selectStatusFilter').val();
            let categoryId = $('#__selectCategoryFilter').val();
            if(supplierId==null){
                supplierId=0;
            }
            if(categoryId==null){
                categoryId=0;
            }
            loadProductDataTable(supplierId,status, categoryId);



            $(document).ready(function() {
                $('#__selectStatusFilter').select2({
                    placeholder: '- Select Status -',
                    allowClear: true
                });
                $('#__selectSupplierFilter').select2({
                    placeholder: '- Select Supplier -',
                    allowClear: true
                });
                $('#__selectCategoryFilter').select2({
                    placeholder: '- Select Product Category -',
                    allowClear: true
                });
                $('#__selectStatusFilter').val('').trigger('change');
                $('#__selectSupplierFilter').val('').trigger('change');
                $('#__selectCategoryFilter').val('').trigger('change');
            });
            $('#__btnSubmitFilter').click(function() {
                let supplierId = $('#__selectSupplierFilter').val();
                let status = $('#__selectStatusFilter').val();
                let categoryId = $('#__selectCategoryFilter').val();
                if(supplierId==null){
                    supplierId=0;
                }
                if(categoryId==null){
                    categoryId=0;
                }
                loadProductDataTable(supplierId,status, categoryId);
            });
            $('#__btnResetFilter').on('click',function() {
                let supplierId = $('#__selectSupplierFilter').val();
                let status = $('#__selectStatusFilter').val();
                let categoryId = $('#__selectCategoryFilter').val();
                if(supplierId==null){
                    supplierId=0;
                }
                if(categoryId==null){
                    categoryId=0;
                }
                loadProductDataTable(supplierId,status, categoryId);
                $('#__selectStatusFilter').val('').trigger('change');
                $('#__selectSupplierFilter').val('').trigger('change');
                $('#__selectCategoryFilter').val('').trigger('change');
            });
            $(document).on('click', '#BtnProduct', function() {
                $.ajax({
                    url: '{{ route('update form') }}?id=' + $(this).data('id'),
                    beforeSend: function() {
                        $('#form-update-stock-reorder').html('Loading');
                    }
                }).done(function(result) {
                    $('#form-update-stock-reorder').html(result);
                    $('.modal-producut').removeClass('modal-hide');
                    $('body').addClass('modal-open');
                });
            });
            $('#closeModalproduct').click(function() {
                $('.modal-products').addClass('modal-hide');
                $('body').removeClass('modal-open');
            });

			$('.__closeModalShipment').click(function() {
               $('.modal-shipment').addClass('modal-hide');
			   $('body').removeClass('modal-open');
            });
        </script>
    @endpush

</x-app-layout>
