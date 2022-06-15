@php
	$statusForStepContent = [
		$orderStatusPending,
		$orderStatusPendingPayment,
		$orderStatusPaymentUnconfirmed
	];

	$statusForEnabledForm = [
		$orderStatusPending
	];

	$statusForConfirmationStepDefault = [
		$orderStatusPending
	];

	$statusForDisableReceiptForm = [
		$orderStatusPaymentUnconfirmed,
		$orderStatusProcessing
	];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<title>{{ config('app.name') }} - {{ __('translation.Place Order') }}</title>

	<link rel="preconnect" href="https://fonts.googleapis.com/" crossorigin>
	<link rel="preconnect" href="https://cdn.jsdelivr.net/" crossorigin>
	<link rel="preconnect" href="https://cdnjs.cloudflare.com/" crossorigin>
	<link rel="preconnect" href="https://code.jquery.com/" crossorigin>
	<link rel="dns-prefetch" href="https://fonts.googleapis.com/">
	<link rel="dns-prefetch" href="https://cdn.jsdelivr.net/">
	<link rel="dns-prefetch" href="https://cdnjs.cloudflare.com/">
	<link rel="dns-prefetch" href="https://code.jquery.com/">

	<!-- Fonts and Icons -->
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-ui-dist@1.12.1/jquery-ui.min.css">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-timepicker@1.3.3/jquery.timepicker.min.css">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css">

	<link rel="stylesheet" href="{{ asset('css/app.css?_=' . rand()) }}">
	<link rel="stylesheet" href="{{ asset('css/buyer_page.css') }}">

	<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
	<script src="{{ asset('js/dodo-modal.js?_=' . rand()) }}"></script>

	@routes
</head>

<body class="font-sans antialiased min-h-screen bg-gray-100">
	{{-- @if (in_array($orderManagement->order_status, $statusForStepContent)) --}}
		<main class="pb-6">

			<div class="w-11/12 mx-auto my-5 sm:w-3/5 xl:max-w-3xl">
				<div class="flex flex-row items-center justify-end gap-2">
					<select id="__lang_switcher" class="w-32 px-2 py-1 rounded-md border-transparent outline-none focus:outline-none bg-white">

						@foreach ($userPrefLangs as $value => $label)
							<option value="{{ $value }}" @if ($value == app()->getLocale()) selected @endif>
								{{ $label }}
							</option>
						@endforeach
					</select>
				</div>
			</div>

			<div id="__mainContent" class="pt-6">
				<div class="w-1/4 sm:w-32 xl:w-28 mx-auto">
					<img src="{{ $orderManagement->shop->logo_url }}" alt="{{ $orderManagement->shop->name }}" class="w-full h-auto">
				</div>

				<div class="w-11/12 sm:w-3/5 xl:max-w-3xl mx-auto my-10">
					<div class="flex flex-row">
						<div class="w-1/4">
							<div class="relative mb-2">
								<div class="w-11 md:w-12 h-11 md:h-12 mx-auto rounded-full flex items-center justify-center shadow-md bg-blue-500 text-white">
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-4 h-4 bi bi-cart3" viewBox="0 0 16 16">
										<path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1 5a.5.5 0 0 1-.465.401l-9.397.472L4.415 11H13a.5.5 0 0 1 0 1H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l.84 4.479 9.144-.459L13.89 4H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
									</svg>
								</div>
							</div>
							<div class="text-center">
								<span>
									{{ __('translation.Order Items') }}
								</span>
							</div>
						</div>
						<div class="w-1/4">
							<div class="relative mb-2">
								<div class="absolute flex align-center items-center align-middle content-center" style="width: calc(100% - 2.5rem - 1rem); top: 50%; transform: translate(-50%, -50%)">
									<div class="w-full bg-transparent items-center align-middle align-center flex-1">
										<div class="w-11/12 mx-auto bg-blue-200 px-1 py-1 rounded"></div>
									</div>
								</div>

								<div class="w-11 md:w-12 h-11 md:h-12 mx-auto rounded-full flex items-center justify-center shadow-md @if (!in_array($orderManagement->order_status, $statusForEnabledForm)) bg-blue-500 text-white @else bg-white text-blue-500 @endif" id="__shippingStepItem">
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-5 h-5 bi bi-truck" viewBox="0 0 16 16">
										<path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-3.998-.085A1.5 1.5 0 0 1 0 10.5v-7zm1.294 7.456A1.999 1.999 0 0 1 4.732 11h5.536a2.01 2.01 0 0 1 .732-.732V3.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .294.456zM12 10a2 2 0 0 1 1.732 1h.768a.5.5 0 0 0 .5-.5V8.35a.5.5 0 0 0-.11-.312l-1.48-1.85A.5.5 0 0 0 13.02 6H12v4zm-9 1a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm9 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
									</svg>
								</div>
							</div>
							<div class="text-center">
								<span>
									{{ __('translation.Shipping') }}
								</span>
							</div>
						</div>
						<div class="w-1/4">
							<div class="relative mb-2">
								<div class="absolute flex align-center items-center align-middle content-center" style="width: calc(100% - 2.5rem - 1rem); top: 50%; transform: translate(-50%, -50%)">
									<div class="w-full bg-transparent items-center align-middle align-center flex-1">
										<div class="w-11/12 mx-auto bg-blue-200 px-1 py-1 rounded"></div>
									</div>
								</div>

								<div class="w-11 md:w-12 h-11 md:h-12 mx-auto rounded-full flex items-center justify-center shadow-md @if (!in_array($orderManagement->order_status, $statusForEnabledForm)) bg-blue-500 text-white @else bg-white text-blue-500 @endif" id="__paymentStepItem">
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-4 h-4 bi bi-currency-dollar" viewBox="0 0 16 16">
										<path d="M4 10.781c.148 1.667 1.513 2.85 3.591 3.003V15h1.043v-1.216c2.27-.179 3.678-1.438 3.678-3.3 0-1.59-.947-2.51-2.956-3.028l-.722-.187V3.467c1.122.11 1.879.714 2.07 1.616h1.47c-.166-1.6-1.54-2.748-3.54-2.875V1H7.591v1.233c-1.939.23-3.27 1.472-3.27 3.156 0 1.454.966 2.483 2.661 2.917l.61.162v4.031c-1.149-.17-1.94-.8-2.131-1.718H4zm3.391-3.836c-1.043-.263-1.6-.825-1.6-1.616 0-.944.704-1.641 1.8-1.828v3.495l-.2-.05zm1.591 1.872c1.287.323 1.852.859 1.852 1.769 0 1.097-.826 1.828-2.2 1.939V8.73l.348.086z"/>
									</svg>
								</div>
							</div>
							<div class="text-center">
								<span>
									{{ __('translation.Payment') }}
								</span>
							</div>
						</div>
						<div class="w-1/4">
							<div class="relative mb-2">
								<div class="absolute flex align-center items-center align-middle content-center" style="width: calc(100% - 2.5rem - 1rem); top: 50%; transform: translate(-50%, -50%)">
									<div class="w-full bg-transparent items-center align-middle align-center flex-1">
										<div class="w-11/12 mx-auto bg-blue-200 px-1 py-1 rounded"></div>
									</div>
								</div>

								<div class="w-11 md:w-12 h-11 md:h-12 mx-auto rounded-full flex items-center justify-center shadow-md @if (!in_array($orderManagement->order_status, $statusForEnabledForm)) bg-blue-500 text-white @else bg-white text-blue-500 @endif" id="__confirmationStepItem">
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-5 h-5 bi bi-card-checklist" viewBox="0 0 16 16">
										<path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h13zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-13z"/>
										<path d="M7 5.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0zM7 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 0 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0z"/>
									</svg>
								</div>
							</div>
							<div class="text-center">
								<span>
									{{ __('translation.Confirmation') }}
								</span>
							</div>
						</div>
					</div>
				</div>

				@if ($orderManagement->order_status == $orderStatusPendingPayment)
					<div class="w-11/12 sm:w-3/5 xl:max-w-3xl mx-auto mb-5"
					id="order_sts">
						<div class="rounded-md shadow-sm px-5 py-3 text-center bg-red-100 text-red-600">
							<span>
								{{ __('translation.Order Status') }} :
							</span>
							<span class="font-bold">
								{{ $orderManagement->str_order_status }}
							</span>
						</div>
					</div>
				@endif

				@if ($orderManagement->order_status == $orderStatusPaymentUnconfirmed)
					<div class="w-11/12 sm:w-3/5 xl:max-w-3xl mx-auto mb-5" id="order_sts">
						<div class="rounded-md shadow-sm px-5 py-3 text-center bg-yellow-100 text-yellow-600">
							<span>
								{{ __('translation.Order Status') }} :
							</span>
							<span class="font-bold">
								{{ $orderManagement->str_order_status }}
							</span>

							@if ($orderManagement->payment_method == $paymentMethodBankTransfer)
								<span class="block mt-1">
									{{ __('translation.We have received your transfer receipt, please allow upto 24 hours to your payment to be confirmed') }}
								</span>
							@endif
						</div>
					</div>
				@endif

				@if (in_array($orderManagement->order_status, array_keys($statusForInfoAlert)))
					<div class="w-11/12 sm:w-3/5 xl:max-w-3xl mx-auto mb-5" id="order_sts">
						<div class="rounded-md shadow-sm px-5 py-3 text-center bg-green-100 text-green-600">
							<span>
								{{ __('translation.Order Status') }} :
							</span>
							<span class="font-bold">
								{{ $orderManagement->str_order_status }}
							</span>

							@if($orderManagement->order_status == $orderStatusProcessing AND $orderManagement->payment_status == $paymentStatusPaid)
								<br>
								<span>
									{{ __('translation.Payment Status') }} :
								</span>
								<span class="font-bold">
									{{ __('translation.Paid') }}<br><br>
									{{ __('translation.We are preparing to ship your Order') }}
								</span>
							@endif
						</div>
					</div>
				@endif

				@if ($orderManagement->order_status == $orderStatusCancel)
					<div class="w-11/12 sm:w-3/5 xl:max-w-3xl mx-auto mb-5" id="order_sts">
						<div class="rounded-md shadow-sm px-5 py-3 text-center bg-red-100 text-red-600">
							<span>
								{{ __('translation.Order Status') }} :
							</span>
							<span class="font-bold">
								{{ $orderManagement->str_order_status }}
							</span>
						</div>
					</div>
				@endif

				<div class="w-11/12 sm:w-3/5 xl:max-w-3xl mx-auto">
					<x-card.card-default>
						<x-card.body>

			<form action="{{ route('order-management.buyer.place-order', [ 'order_id' => $orderManagement->order_id ]) }}" method="post" id="__formBuyerPage">
				@csrf

				<div @if (!in_array($orderManagement->order_status, $statusForEnabledForm)) class="hidden" @endif id="__confirmOrderStepContentWrapper">
					<div class="mb-6">
						<h1 class="text-xl text-gray-800 font-bold leading-tight relative top-1">
							{{ __('translation.Order Details') }} (#{{$orderManagement->id}})
						</h1>
					</div>

					<x-section.section>
						<x-section.title>
							{{ __('translation.Your Items Order') }}
						</x-section.title>
						<x-section.body>
							<div class="w-full">
							@php $total_product_discount = 0;@endphp
							@foreach ($orderManagement->order_management_details as $detail)

							@if($detail->discount_price == 0)
	                            @php $total_product_discount += 0; @endphp
	                        @else
	                           @php $total_product_discount +=($detail->price-$detail->discount_price)*$detail->quantity; @endphp
	                        @endif

									<div class="mb-5 py-4 border border-solid border-t-0 border-r-0 border-l-0 border-gray-200" id="__row_ProductItem_{{ $detail->product->product_code }}">
										<div class="flex flex-row">
											<div class="w-1/4 sm:w-1/4 md:w-1/5 lg:w-1/6 xl:w-1/5 mb-4 md:mb-0">
												<div class="mb-4">
													<img src="{{ $detail->product->image_url }}" alt="{{ $detail->product->product_name }}" class="w-full h-auto rounded-md">
												</div>
											</div>
											<div class="w-3/4 sm:w-3/4 md:w-4/5 lg:w-5/6 xl:w-4/5 ml-4 sm:ml-6">
												<div class="mb-2 xl:mb-4">
													<label class="hidden lg:block mb-0">
														{{ __('translation.Product Name') }} :
													</label>
													<p class="font-bold">
														{{ $detail->product->product_name }} <br>
														<span>{{ $detail->product->product_code }}</span>
													</p>
												</div>
												<div>
													<div class="grid grid-cols-1 lg:grid-cols-2 gap-2 lg:gap-x-8">
														<div>
															<label class="mb-0 block">
																{{ __('translation.Price') }} :
															</label>

															@if ($detail->discount_price == 0)
																<span class="font-bold">
																	{{ currency_symbol('THB') }}
																	{{ currency_number($detail->price, 3) }}
																</span>
															@endif

															@if ($detail->discount_price > 0)
																@php
																	$displayedDiscountPrice = $detail->discount_price;
																@endphp

																<span class="font-bold line-through text-red-400">
																	{{ currency_symbol('THB') }}
																	{{ currency_number($detail->price, 3) }}
																</span>
																<span class="ml-3 bg-transparent border-0 outline-none focus:outline-none font-bold btn-product-discount" data-product-code="{{ $detail->product->product_code }}">
																	{{ currency_symbol('THB') . ' ' . currency_number($displayedDiscountPrice, 3) }}
																</span>
															@endif
														</div>

														<div>
															<label class="mb-0 block">
																{{ __('translation.Ordered Qty') }} :
															</label>
															<span class="font-bold lg:block">
																{{ number_format($detail->quantity) }}
															</span>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								@endforeach

							</div>
						</x-section.body>
					</x-section.section>

					<x-section.section>
						<x-section.title>
							{{ __('translation.Available Shipping Methods') }}
						</x-section.title>
						<x-section.body>
							<div id="__shippingMethodOuterWrapper">
								@php
									$shippingItemId = 1;
								@endphp
								@foreach ($orderManagement->customer_shipping_methods as $customerShipping)
									@if (!empty($customerShipping->shipping_cost->shipper->name))
										<div class="mb-4 pb-3 border border-t-0 border-r-0 border-l-0 border-dashed border-gray-300" id="__shippingMethodItem_{{ $shippingItemId }}">
											<div class="flex flex-row items-start">
												<div>
													@if (!in_array($orderManagement->order_status, $statusForEnabledForm))
														<input type="radio" name="shipping_method_radio[]" id="__shipping_method_{{ $shippingItemId }}" class="shipping-method__id-radio-field" value="0" data-id="{{ $shippingItemId }}" @if ($customerShipping->is_selected == $isSelectedShippingMethod) checked @endif disabled>
													@else
														<input type="radio" name="shipping_method_radio[]" id="__shipping_method_{{ $shippingItemId }}" class="shipping-method__id-radio-field" value="0" data-id="{{ $shippingItemId }}" @if ($customerShipping->is_selected == $isSelectedShippingMethod) checked @endif>
													@endif
													<input type="hidden" name="shipping_method_id[]" value="{{ $customerShipping->id }}" class="shipping-method__id-input--field" data-id="{{ $shippingItemId }}">
													<input type="hidden" name="shipping_method_name[]" value="{{ $customerShipping->shipping_cost->name }} ({{ $customerShipping->shipping_cost->shipper->name }})" class="shiping-method__name-field">
													<input type="hidden" name="shipping_method_price[]" value="{{ $customerShipping->price }}" class="shiping-method__price-field">
													<input type="hidden" name="shipping_method_discount[]" value="{{ $customerShipping->discount_price }}" class="shiping-method__discount-field">
													<input type="hidden" name="shipping_method_selected[]" value="{{ $customerShipping->is_selected }}" class="shiping-method__selected-input-field">
												</div>
												<div class="ml-2">
													<div class="flex flex-col lg:flex-row lg:items-center shipping-method__content-wrapper">
														<div class="flex flex-col sm:flex-row sm:items-center">
															<div class="mb-2 sm:mb-0">
																<label for="__shipping_method_{{ $shippingItemId }}" class="ml-1">
																	{{ $customerShipping->shipping_cost->name }} ({{ $customerShipping->shipping_cost->shipper->name }})
																</label>
															</div>
															<div class="hidden sm:block ml-6">
																-
															</div>
															<div class="sm:ml-2">
																@if ($customerShipping->discount_price == 0)
																	<span class="font-bold shipping-method__price-display">
																		{{ currency_symbol('THB') }} {{ currency_number($customerShipping->price, 3) }}
																	</span>
																@endif

																@if ($customerShipping->discount_price > 0)
																	@php
																		$discountPrice = $customerShipping->price - $customerShipping->discount_price;
																	@endphp
																	<span class="font-bold line-through text-red-400 shipping-method__price-display">
																		{{ currency_symbol('THB') }} {{ currency_number($customerShipping->price, 3) }}
																	</span>
																	<span class="ml-2 bg-transparent border-0 outline-none focus:outline-none font-bold shipping-method__btn-product-discount" data-id="{{ $shippingItemId }}">
																		{{ currency_symbol('THB') . ' ' . currency_number($discountPrice, 3) }}
																	</span>
																@endif
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									@else
										<div class="mb-4 pb-3 border border-t-0 border-r-0 border-l-0 border-dashed border-gray-300" id="__shippingMethodItem_{{ $shippingItemId }}">
											<div class="flex flex-row items-start">
												<div>
													@if (!in_array($orderManagement->order_status, $statusForEnabledForm))
														<input type="radio" name="shipping_method_radio[]" id="__shipping_method_{{ $shippingItemId }}" class="shipping-method__id-radio-field" data-id="{{ $shippingItemId }}"  @if ($customerShipping->is_selected == $isSelectedShippingMethod) checked @endif disabled>
													@else
														<input type="radio" name="shipping_method_radio[]" id="__shipping_method_{{ $shippingItemId }}" class="shipping-method__id-radio-field" data-id="{{ $shippingItemId }}"  @if ($customerShipping->is_selected == $isSelectedShippingMethod) checked @endif>
													@endif
													<input type="hidden" name="shipping_method_id[]" value="{{ $customerShipping->id }}" class="shipping-method__id-input--field" data-id="{{ $shippingItemId }}">
													<input type="hidden" name="shipping_method_name[]" value="{{ $customerShipping->shipping_cost->name }}" class="shiping-method__name-field">
													<input type="hidden" name="shipping_method_price[]" value="{{ $customerShipping->price }}" class="shiping-method__price-field">
													<input type="hidden" name="shipping_method_discount[]" value="{{ $customerShipping->discount_price }}" class="shiping-method__discount-field">
													<input type="hidden" name="shipping_method_selected[]" value="{{ $customerShipping->is_selected }}" class="shiping-method__selected-input-field">
												</div>
												<div class="ml-2">
													<div class="flex flex-col lg:flex-row lg:items-center shipping-method__content-wrapper">
														<div class="flex flex-col sm:flex-row sm:items-center">
															<div class="mb-2 sm:mb-0">
																<label for="__shipping_method_{{ $shippingItemId }}" class="ml-1">
																	{{ $customerShipping->shipping_cost->name }}
																</label>
															</div>
															<div class="hidden sm:block ml-6">
																-
															</div>
															<div class="sm:ml-2">
																@if ($customerShipping->discount_price == 0)
																	<span class="font-bold shipping-method__price-display">
																		{{ currency_symbol('THB') }} {{ currency_number($customerShipping->price, 3) }}
																	</span>
																@endif

																@if ($customerShipping->discount_price > 0)
																	@php
																		$discountPrice = $customerShipping->price - $customerShipping->discount_price;
																	@endphp
																	<span class="font-bold line-through text-red-400 shipping-method__price-display">
																		{{ currency_symbol('THB') }} {{ currency_number($customerShipping->price, 3) }}
																	</span>
																	<button type="button" class="ml-2 bg-transparent border-0 outline-none focus:outline-none font-bold shipping-method__btn-product-discount" data-id="{{ $shippingItemId }}">
																		{{ currency_symbol('THB') . ' ' . currency_number($discountPrice, 3) }}
																	</button>
																@endif
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									@endif

									@php
										$shippingItemId++;
									@endphp
								@endforeach
							</div>
						</x-section.body>
					</x-section.section>

					<x-section.section>
						<x-section.title>
							{{ __('translation.Tax Details') }}
						</x-section.title>
						<x-section.body>
							<div class="mb-4">
								<label for="__tax_enable_BuyerPage" class="block mb-2">
									{{ __('translation.Request Tax') }} <x-form.required-mark/>
								</label>
								<div class="flex flex-row gap-x-4">
									@foreach ($taxEnableValues as $value => $text)
										@if ($value == $orderManagement->tax_enable)
											@if (!in_array($orderManagement->order_status, $statusForEnabledForm))
												<x-form.input-radio name="tax_enable" id="__tax_enable_{{ $value }}BuyerPage" value="{{ $value }}" checked="true" disabled="true">
													{{ $text }}
												</x-form.input-radio>
											@else
												<x-form.input-radio name="tax_enable" id="__tax_enable_{{ $value }}BuyerPage" value="{{ $value }}" checked="true">
													{{ $text }}
												</x-form.input-radio>
											@endif
										@else
											@if (!in_array($orderManagement->order_status, $statusForEnabledForm))
												<x-form.input-radio name="tax_enable" id="__tax_enable_{{ $value }}BuyerPage" value="{{ $value }}" disabled="true">
													{{ $text }}
												</x-form.input-radio>
											@else
												<x-form.input-radio name="tax_enable" id="__tax_enable_{{ $value }}BuyerPage" value="{{ $value }}">
													{{ $text }}
												</x-form.input-radio>
											@endif
										@endif
									@endforeach
								</div>
							</div>

							<div class="mt-8" id="__taxCompanyInfoWrapper" @if ($orderManagement->tax_enable != $taxEnableYes) style="display:none" @endif>
								<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-x-8">
									<div>
										<x-label for="__company_nameBuyerPage">
											{{ __('translation.Company Name') }} <x-form.required-mark />
										</x-label>
										@if (!in_array($orderManagement->order_status, $statusForEnabledForm))
											<x-input type="text" name="company_name" id="__company_nameBuyerPage" value="{{ $orderManagement->company_name }}" disabled="true" />
										@else
											<x-input type="text" name="company_name" id="__company_nameBuyerPage" value="{{ $orderManagement->company_name }}" />
										@endif
									</div>
									<div>
										<x-label for="__tax_numberBuyerPage">
											{{ __('translation.Tax Number') }} <x-form.required-mark />
										</x-label>
										@if (!in_array($orderManagement->order_status, $statusForEnabledForm))
											<x-input type="text" name="tax_number" id="__tax_numberBuyerPage" value="{{ $orderManagement->tax_number }}" disabled="true" />
										@else
											<x-input type="text" name="tax_number" id="__tax_numberBuyerPage" value="{{ $orderManagement->tax_number }}" />
										@endif
									</div>
									<div>
										<x-label for="__company_phone_numberBuyerPage">
											{{ __('translation.Phone Number') }} <x-form.required-mark />
										</x-label>
										@if (!in_array($orderManagement->order_status, $statusForEnabledForm))
											<x-input type="text" name="company_phone_number" id="__company_phone_numberBuyerPage" value="{{ $orderManagement->company_phone_number }}" disabled="true" />
										@else
											<x-input type="text" name="company_phone_number" id="__company_phone_numberBuyerPage" value="{{ $orderManagement->company_phone_number }}" />
										@endif
									</div>
									<div>
										<x-label for="__company_contact_nameBuyerPage">
											{{ __('translation.Contact Name') }} <x-form.required-mark />
										</x-label>
										@if (!in_array($orderManagement->order_status, $statusForEnabledForm))
											<x-input type="text" name="company_contact_name" id="__company_contact_nameBuyerPage" value="{{ $orderManagement->company_contact_name }}" disabled="true" />
										@else
											<x-input type="text" name="company_contact_name" id="__company_contact_nameBuyerPage" value="{{ $orderManagement->company_contact_name }}" />
										@endif
									</div>
									<div class="sm:col-span-2">
										<x-label for="__company_addressBuyerPage">
											{{ __('translation.Address') }} <x-form.required-mark />
										</x-label>
										@if (!in_array($orderManagement->order_status, $statusForEnabledForm))
											<x-form.textarea name="company_address" id="__company_addressBuyerPage" rows="3" disabled>{{ $orderManagement->company_address }}</x-form.textarea>
										@else
											<x-form.textarea name="company_address" id="__company_addressBuyerPage" rows="3">{{ $orderManagement->company_address }}</x-form.textarea>
										@endif
									</div>
									<div>
										<x-label for="__company_provinceBuyerPage">
											{{ __('translation.Province') }} <x-form.required-mark />
										</x-label>
										@if (!in_array($orderManagement->order_status, $statusForEnabledForm))
											<x-select name="company_province" id="__company_provinceBuyerPage" style="width: 100%" disabled>
												<option value="{{ $orderManagement->company_province }}" selected>
													{{ $orderManagement->company_province }}
												</option>
											</x-select>
										@else
											<x-select name="company_province" id="__company_provinceBuyerPage" style="width: 100%">
												<option value="{{ $orderManagement->company_province }}" selected>
													{{ $orderManagement->company_province }}
												</option>
											</x-select>
										@endif
									</div>
									<div>
										<x-label for="__company_districtBuyerPage">
											{{ __('translation.District') }} <x-form.required-mark />
										</x-label>
										@if (!empty($orderManagement->company_district))
											@if (!in_array($orderManagement->order_status, $statusForEnabledForm))
												<x-select name="company_district" id="__company_districtBuyerPage" style="width: 100%" disabled>
													<option value="{{ $orderManagement->company_district }}">
														{{ $orderManagement->company_district }}
													</option>
												</x-select>
											@else
												<x-select name="company_district" id="__company_districtBuyerPage" style="width: 100%">
													<option value="{{ $orderManagement->company_district }}">
														{{ $orderManagement->company_district }}
													</option>
												</x-select>
											@endif
										@else
											<x-select name="company_district" id="__company_districtBuyerPage" style="width: 100%" disabled></x-select>
										@endif
									</div>
									<div>
										<x-label for="__company_sub_districtBuyerPage">
											{{ __('translation.Sub-District') }} <x-form.required-mark />
										</x-label>
										@if (!empty($orderManagement->company_sub_district))
											@if (!in_array($orderManagement->order_status, $statusForEnabledForm))
												<x-select name="company_sub_district" id="__company_sub_districtBuyerPage" style="width: 100%" disabled>
													<option value="{{ $orderManagement->company_sub_district }}">
														{{ $orderManagement->company_sub_district }}
													</option>
												</x-select>
											@else
												<x-select name="company_sub_district" id="__company_sub_districtBuyerPage" style="width: 100%">
													<option value="{{ $orderManagement->company_sub_district }}">
														{{ $orderManagement->company_sub_district }}
													</option>
												</x-select>
											@endif
										@else
											<x-select name="company_sub_district" id="__company_sub_districtBuyerPage" style="width: 100%" disabled></x-select>
										@endif
									</div>
									<div>
										<x-label for="__company_postcodeBuyerPage">
											{{ __('translation.Postal Code') }} <x-form.required-mark />
										</x-label>
										@if (!empty($orderManagement->company_postcode))
											@if (!in_array($orderManagement->order_status, $statusForEnabledForm))
												<x-select name="company_postcode" id="__company_postcodeBuyerPage" style="width: 100%" disabled>
													<option value="{{ $orderManagement->company_postcode }}">
														{{ $orderManagement->company_postcode }}
													</option>
												</x-select>
											@else
												<x-select name="company_postcode" id="__company_postcodeBuyerPage" style="width: 100%">
													<option value="{{ $orderManagement->company_postcode }}">
														{{ $orderManagement->company_postcode }}
													</option>
												</x-select>
											@endif
										@else
											<x-select name="company_postcode" id="__company_postcodeBuyerPage" style="width: 100%" disabled></x-select>
										@endif
									</div>
								</div>
							</div>
						</x-section.body>
					</x-section.section>

									<x-section.section>
										<x-section.title>
											{{ __('translation.Cart Totals') }}
										</x-section.title>
										<x-section.body>
											<div class="w-full">
												<table class="w-full -mt-1">
													<tbody>
														<tr>
															<td class="pr-3 py-1">
																{{ __('translation.Sub Total') }}
															</td>
															<td class="py-1">
																<span class="text-white">-</span>
																<span class="font-bold">
																	{{ currency_symbol('THB') }}
																</span>
															</td>
															<td class="pl-3 py-1 text-right">
																<span class="font-bold" id="__subTotalCurrency">
																	{{ number_format($orderManagement->sub_total, 2) }}
																</span>
															</td>
														</tr>
														<tr>
															<td class="pr-3 py-1">
																{{ __('translation.Shipping Cost') }}
															</td>
															<td class="py-1">
																<span class="text-white">-</span>
																<span class="font-bold">
																	{{ currency_symbol('THB') }}
																</span>
															</td>
															<td class="pl-3 py-1 text-right">
																<span class="font-bold" id="__shippingCostCurrency">
																	{{ number_format($orderManagement->shipping_cost, 2) }}
																</span>
															</td>
														</tr>
														<tr id="__taxRateRowCartTotals" @if ($orderManagement->tax_enable != $taxEnableYes) style="display: none;" @endif>
															<td class="pr-3 py-1">
																{{ $taxRateSetting->tax_name ?? '' }} (<span class="__taxRateCartTotal">{{ currency_number($taxRateSetting->tax_rate, 2) . '%' }}</span>)
															</td>
															<td class="py-1">
																<span class="text-white">-</span>
																<span class="font-bold">
																	{{ currency_symbol('THB') }}
																</span>
															</td>
															<td class="pl-3 py-1 text-right">
																<span class="font-bold" id="__taxRateCurrency">
                                                                    {{ number_format($orderManagement->tax_rate, 2) }}
																</span>
															</td>
														</tr>
														<tr>
															<td class="pr-3 py-1">
																{{ __('translation.Discount') }}
															</td>
															<td class="py-1">
																<span class="text-gray-900">-</span>
																<span class="font-bold">
																	{{ currency_symbol('THB') }}
																</span>
															</td>
															<td class="pl-3 py-1 text-right">
																<span class="font-bold" id="__discountCurrency">
																	{{ number_format($total_product_discount, 2) }}
																</span>
															</td>
														</tr>
														<tr>
															<td colspan="3" class="pt-1 border border-dashed border-r-0 border-b-0 border-l-0 border-gray-400"></td>
														</tr>
														<tr>
															<td class="pr-3 py-1 font-bold text-red-500">
																{{ __('translation.Total Amount') }}
															</td>
															<td class="py-1">
																<span class="text-white">-</span>
																<span class="font-bold text-red-500">
																	{{ currency_symbol('THB') }}
																</span>
															</td>

															<td class="pl-3 py-1 text-right">
																<span class="font-bold text-red-500 __grandTotalCurrency">

																	@php $in_total = ($orderManagement->sub_total + $orderManagement->shipping_cost + $orderManagement->tax_rate) - $total_product_discount; @endphp
																	{{ number_format($in_total, 2) }}

																	<input type="hidden" name="in_total_amount" value="{{ $in_total }}">
																	<input type="hidden" name="tax_vat_amount" id="tax_vat_amount" value="{{currency_number($orderManagement->tax_rate, 2)}}">
																</span>
															</td>
														</tr>
													</tbody>
												</table>
											</div>
										</x-section.body>
									</x-section.section>

									<div class="pb-3">
										<div class="flex items-center justify-center gap-1">
											<x-button type="button" color="blue" id="__btnNextShippingStep">
												<span class="mr-2">
													{{ __('translation.Next Steps') }}
												</span>
												<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-4 h-4 bi bi-arrow-right" viewBox="0 0 16 16">
													<path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
												</svg>
											</x-button>
										</div>
									</div>
								</div>

								<div class="hidden" id="__shippingStepContentWrapper">
									<div class="mb-6">
										<h1 class="text-xl text-gray-800 font-bold leading-tight relative top-1">
											{{ __('translation.Shipping Details') }} (#{{$orderManagement->id}})
										</h1>
									</div>

									<x-section.section>
										<x-section.title>
											{{ __('translation.Where Should We Deliver Your Order') }}
										</x-section.title>
										<x-section.body>
											<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
												<div>
													<x-label for="__shipping_nameBuyerPage">
														{{ __('translation.Your Name') }} <x-form.required-mark/>
													</x-label>
													@if (!in_array($orderManagement->order_status, $statusForEnabledForm))
														<x-input type="text" name="shipping_name" id="__shipping_nameBuyerPage" value="{{ $orderManagement->shipping_name }}" disabled />
													@else
														<x-input type="text" name="shipping_name" id="__shipping_nameBuyerPage" value="{{ $orderManagement->shipping_name }}" />
													@endif
												</div>
												<div>
													<x-label for="__shipping_phoneBuyerPage">
														{{ __('translation.Phone Number') }} <x-form.required-mark/>
													</x-label>
													@if (!in_array($orderManagement->order_status, $statusForEnabledForm))
														<x-input type="text" name="shipping_phone" id="__shipping_phoneBuyerPage" value="{{ $orderManagement->shipping_phone }}" disabled />
													@else
														<x-input type="text" name="shipping_phone" id="__shipping_phoneBuyerPage" value="{{ $orderManagement->shipping_phone }}" />
													@endif
												</div>
												<div class="md:col-span-2">
													<x-label for="__addressBuyerPage">
														{{ __('translation.Address') }} <x-form.required-mark/>
													</x-label>
													@if (!in_array($orderManagement->order_status, $statusForEnabledForm))
														<x-form.textarea name="shipping_address" id="__addressBuyerPage" rows="4" disabled>{{ $orderManagement->shipping_address }}</x-form.textarea>
													@else
														<x-form.textarea name="shipping_address" id="__addressBuyerPage" rows="4">{{ $orderManagement->shipping_address }}</x-form.textarea>
													@endif
												</div>
												<div>
													<x-label for="__shipping_provinceBuyerPage">
														{{ __('translation.Province') }} <x-form.required-mark/>
													</x-label>
													@if (!in_array($orderManagement->order_status, $statusForEnabledForm))
														<x-input type="text" name="shipping_province" value="{{ $orderManagement->shipping_province }}" disabled />
													@else
														<x-select name="shipping_province" id="__shipping_provinceBuyerPage" style="width: 100%">
															<option value="{{ $orderManagement->shipping_province }}" selected>
																{{ $orderManagement->shipping_province }}
															</option>
														</x-select>
													@endif
												</div>
												<div>
													<x-label for="__shipping_districtBuyerPage">
														{{ __('translation.District') }} <x-form.required-mark/>
													</x-label>
													@if (!in_array($orderManagement->order_status, $statusForEnabledForm))
														<x-input type="text" name="shipping_district" value="{{ $orderManagement->shipping_district }}" disabled />
													@else
														@if (empty($orderManagement->shipping_district))
															<x-select name="shipping_district" id="__shipping_districtBuyerPage" style="width: 100%" disabled>
																<option value="{{ $orderManagement->shipping_district }}" selected>
																	{{ $orderManagement->shipping_district }}
																</option>
															</x-select>
														@else
															<x-select name="shipping_district" id="__shipping_districtBuyerPage" style="width: 100%">
																<option value="{{ $orderManagement->shipping_district }}" selected>
																	{{ $orderManagement->shipping_district }}
																</option>
															</x-select>
														@endif
													@endif
												</div>
												<div>
													<x-label for="__shipping_sub_districtBuyerPage">
														{{ __('translation.Sub District') }} <x-form.required-mark/>
													</x-label>
													@if (!in_array($orderManagement->order_status, $statusForEnabledForm))
														<x-input type="text" name="shipping_sub_district" value="{{ $orderManagement->shipping_sub_district }}" disabled />
													@else
														@if (empty($orderManagement->shipping_sub_district))
															<x-select name="shipping_sub_district" id="__shipping_sub_districtBuyerPage" style="width: 100%" disabled>
																<option value="{{ $orderManagement->shipping_sub_district }}" selected>
																	{{ $orderManagement->shipping_sub_district }}
																</option>
															</x-select>
														@else
															<x-select name="shipping_sub_district" id="__shipping_sub_districtBuyerPage" style="width: 100%">
																<option value="{{ $orderManagement->shipping_sub_district }}" selected>
																	{{ $orderManagement->shipping_sub_district }}
																</option>
															</x-select>
														@endif
													@endif
												</div>
												<div>
													<x-label for="__shipping_postcodeBuyerPage">
														{{ __('translation.Postal Code') }} <x-form.required-mark/>
													</x-label>
													@if (!in_array($orderManagement->order_status, $statusForEnabledForm))
														<x-input type="text" name="shipping_postcode" value="{{ $orderManagement->shipping_postcode }}" disabled />
													@else
														@if (empty($orderManagement->shipping_postcode))
															<x-select name="shipping_postcode" id="__shipping_postcodeBuyerPage" style="width: 100%" disabled>
																<option value="{{ $orderManagement->shipping_postcode }}" selected>
																	{{ $orderManagement->shipping_postcode }}
																</option>
															</x-select>
														@else
															<x-select name="shipping_postcode" id="__shipping_postcodeBuyerPage" style="width: 100%">
																<option value="{{ $orderManagement->shipping_postcode }}" selected>
																	{{ $orderManagement->shipping_postcode }}
																</option>
															</x-select>
														@endif
													@endif
												</div>
											</div>
										</x-section.body>
									</x-section.section>

									<div class="pb-3">
										<div class="flex items-center justify-center gap-1">
											<x-button-outline type="button" color="blue" id="__btnBackOrderItemStep">
												<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-4 h-4 bi bi-arrow-left" viewBox="0 0 16 16">
													<path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
												</svg>
												<span class="ml-2">
													{{ __('translation.Prev Steps') }}
												</span>
											</x-button-outline>
											<x-button type="button" color="blue" id="__btnNextPaymentStep">
												<span class="mr-2">
													{{ __('translation.Next Step') }}
												</span>
												<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-4 h-4 bi bi-arrow-right" viewBox="0 0 16 16">
													<path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
												</svg>
											</x-button>
										</div>
									</div>
								</div>

								<div class="hidden" id="__paymentStepContentWrapper">
									<div class="mb-6">
										<h1 class="text-xl text-gray-800 font-bold leading-tight relative top-1">
											{{ __('translation.Payment') }}
											(#{{$orderManagement->id}})
										</h1>
									</div>

									<x-section.section>
										<x-section.title>
											{{ __('translation.Select Payment Method') }}
										</x-section.title>
										<x-section.body>

											@if (!in_array($orderManagement->order_status, $statusForEnabledForm))
												<div class="mb-5">
													<div class="mb-2">
														@if ($orderManagement->payment_method == $paymentMethodInstant)
															<x-form.input-radio name="payment_method" id="__paymentMethodBuyerPage_2" value="{{ $paymentMethodInstant }}" checked="true" disabled="true">
																{{ __('translation.Instant Payment') }}
															</x-form.input-radio>
														@else
															<x-form.input-radio name="payment_method" id="__paymentMethodBuyerPage_2" value="{{ $paymentMethodInstant }}" disabled="true">
																{{ __('translation.Instant Payment') }}
															</x-form.input-radio>
														@endif
													</div>
													<div class="px-6" id="__instantPaymentMethodWrapper" @if ($orderManagement->payment_method == $paymentMethodBankTransfer) style="display: none" @endif>
														<div class="border border-solid border-gray-300 rounded-md p-4">
															<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">

																<div class="flex items-center justify-center">
																	<img src="
																	{{Storage::disk('s3')->url('img/promtpay.png')}}
																	" class="w-full h-auto" alt="PromptPay">
																</div>
																<div class="flex items-center justify-center">
																	<img src="{{ asset('img/shopeepay.png') }}" class="w-full h-auto" alt="Shopee Pay">
																</div>
																<div class="flex items-center justify-center">
																	<img src="{{ asset('img/truemoney.png') }}" class="w-full h-auto" alt="True Money">
																</div>
																<div class="flex items-center justify-center">
																	<img src="{{ asset('img/credit_debit.png') }}" class="w-full h-auto" alt="Credit and debit card Pay">
																</div>
															</div>
														</div>
													</div>
												</div>
												<div class="mb-5">
													<div class="mb-2">
														@if ($orderManagement->payment_method == $paymentMethodBankTransfer)
															<x-form.input-radio name="payment_method" id="__paymentMethodBuyerPage_1" value="{{ $paymentMethodBankTransfer }}" checked="true" disabled="true">
																{{ __('translation.Bank Transfer') }}
															</x-form.input-radio>

														@else
															<x-form.input-radio name="payment_method" id="__paymentMethodBuyerPage_1" value="{{ $paymentMethodBankTransfer }}" disabled="true">
																{{ __('translation.Bank Transfer') }}
															</x-form.input-radio>
														@endif
													</div>
												</div>
												<p id="bank_details_information" class="hidden ml-4 text-success border-gray-300">Bank details provided in the next page, please place order.</p>
											@else
												<div class="mb-5">
													<div class="mb-2">
														<x-form.input-radio name="payment_method" id="__paymentMethodBuyerPage_2" value="{{ $paymentMethodInstant }}" checked="true">
															{{ __('translation.Instant Payment') }}
														</x-form.input-radio>
													</div>
													<div class="px-6" id="__instantPaymentMethodWrapper">
														<div class="border border-solid border-gray-300 rounded-md p-4">
															<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">

																<div class="flex items-center justify-center">
																	<img src="
																	{{Storage::disk('s3')->url('img/promtpay.png')}}
																	" class="w-full h-auto" alt="PromptPay">
																</div>
																<div class="flex items-center justify-center">
																	<img src="{{ asset('img/shopeepay.png') }}" class="w-full h-auto" alt="Shopee Pay">
																</div>
																<div class="flex items-center justify-center">
																	<img src="{{ asset('img/truemoney.png') }}" class="w-full h-auto" alt="True Money">
																</div>
																<div class="flex items-center justify-center">
																	<img src="{{ asset('img/credit_debit.png') }}" class="w-full h-auto" alt="Credit and debit card Pay">
																</div>
															</div>
														</div>
													</div>
												</div>
												<div class="mb-5">
													<div class="mb-2">
														<x-form.input-radio name="payment_method" id="__paymentMethodBuyerPage_1" value="{{ $paymentMethodBankTransfer }}">
															{{ __('translation.Bank Transfer') }}
														</x-form.input-radio>
													</div>
												</div>
												<p id="bank_details_information" class="hidden ml-4 text-success border-gray-300">Bank details provided in the next page, please place order.</p>
											@endif
										</x-section.body>
									</x-section.section>

									<div class="pb-3" id="place_order_wrapper">
										<div class="flex items-center justify-center gap-1">
											<x-button-outline type="button" color="blue" id="__btnBackShippingStep">
												<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-4 h-4 bi bi-arrow-left" viewBox="0 0 16 16">
													<path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
												</svg>
												<span class="ml-2">
													{{ __('translation.Prev Steps') }}
												</span>
											</x-button-outline>

											@if (!in_array($orderManagement->order_status, $statusForEnabledForm))
												<x-button class="__btnNextConfirmationStep" type="button" color="blue" id="__btnNextConfirmationStep">
													<span class="mr-2">
														{{ __('translation.Next Steps') }}
													</span>
													<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-4 h-4 bi bi-arrow-right" viewBox="0 0 16 16">
														<path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
													</svg>
												</x-button>
											@else
												<x-button type="button" color="blue" id="__btnPlaceOrder">
													<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-4 h-4 bi bi-check2-all" viewBox="0 0 16 16">
														<path d="M12.354 4.354a.5.5 0 0 0-.708-.708L5 10.293 1.854 7.146a.5.5 0 1 0-.708.708l3.5 3.5a.5.5 0 0 0 .708 0l7-7zm-4.208 7-.896-.897.707-.707.543.543 6.646-6.647a.5.5 0 0 1 .708.708l-7 7a.5.5 0 0 1-.708 0z"/>
														<path d="m5.354 7.146.896.897-.707.707-.897-.896a.5.5 0 1 1 .708-.708z"/>
													</svg>
													<span class="ml-2">
														{{ __('translation.Place Order') }}
													</span>
												</x-button>
											@endif
										</div>
									</div>

									<div class="pb-3" id="place_order_div">
										<div class="flex items-center justify-center gap-1">
											<x-button-outline type="button" color="blue" id="__btnBackShippingStepForChnagePM">
												<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-4 h-4 bi bi-arrow-left" viewBox="0 0 16 16">
													<path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
												</svg>
												<span class="ml-2">
													{{ __('translation.Prev Steps') }}
												</span>
											</x-button-outline>

											<x-button type="button" color="blue" id="__btnPlaceOrderForChnagePM">
												<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-4 h-4 bi bi-check2-all" viewBox="0 0 16 16">
													<path d="M12.354 4.354a.5.5 0 0 0-.708-.708L5 10.293 1.854 7.146a.5.5 0 1 0-.708.708l3.5 3.5a.5.5 0 0 0 .708 0l7-7zm-4.208 7-.896-.897.707-.707.543.543 6.646-6.647a.5.5 0 0 1 .708.708l-7 7a.5.5 0 0 1-.708 0z"/>
													<path d="m5.354 7.146.896.897-.707.707-.897-.896a.5.5 0 1 1 .708-.708z"/>
												</svg>
												<span class="ml-2">
													{{ __('translation.Place Order') }}
												</span>
											</x-button>

										</div>
									</div>
								</div>

								<div @if (in_array($orderManagement->order_status, $statusForConfirmationStepDefault)) class="hidden" @endif id="__confirmationStepContentWrapper">
									<div class="mb-6">
										<h1 class="text-xl text-gray-800 font-bold leading-tight relative top-1">
											{{ __('translation.Order Confirmation') }} #{{ $orderManagement->id }}
										</h1>
									</div>

									<x-section.section>
										<x-section.title>
											{{ __('translation.Shipping Address') }}
										</x-section.title>
										<x-section.body>
											<p>
												<span class="text-base">
													{{ $orderManagement->shipping_name }}
												</span>
												<br>
												{{ $orderManagement->shipping_phone }}
											</p>
											<p class="-mt-2">
												{{ $orderManagement->shipping_address }}
												<br>
												{{ $orderManagement->shipping_district . ', ' . $orderManagement->shipping_sub_district }}
												<br>
												{{ $orderManagement->shipping_province }}
												<br>
												{{ $orderManagement->shipping_postcode }}
											</p>
										</x-section.body>
									</x-section.section>

									<x-section.section>
										<x-section.title>
											{{ __('translation.Order Summary') }}
										</x-section.title>
										<x-section.body>
											<div class="w-full">
												<table class="w-full -mt-1">
													<tbody>
														<tr>
															<td class="pr-3 py-1">
																<span class="mr-2">
																	{{ __('translation.Sub Total') }}
																</span>
																<span>
																	(
																</span>
																<button type="button" class="border-0 outline-none focus:outline-none bg-transparent text-blue-500 hover:underline" onClick="viewProduct(this)">
																	{{ __('translation.View Products') }}
																</button>
																<span>
																	)
																</span>
															</td>
															<td class="py-1">
																<span class="text-white">-</span>
																<span class="font-bold">
																	{{ currency_symbol('THB') }}
																</span>
															</td>
															<td class="pl-3 py-1 text-right">
																<span class="font-bold">
																	{{ currency_number($orderManagement->sub_total, 3) }}
																</span>
															</td>
														</tr>
														<tr>
															<td class="pr-3 py-1">
																<span class="mr-2">
																	{{ __('translation.Shipping Cost') }}
																</span>
																<span>
																	(
																</span>
																<button type="button" class="border-0 outline-none focus:outline-none bg-transparent text-blue-500 hover:underline" onClick="viewShippingMethod(this)">
																	{{ __('translation.View Details') }}
																</button>
																<span>
																	)
																</span>
															</td>
															<td class="py-1">
																<span class="text-white">-</span>
																<span class="font-bold">
																	{{ currency_symbol('THB') }}
																</span>
															</td>
															<td class="pl-3 py-1 text-right">
																<span class="font-bold">


																	@php $selected_shipment_cost = $selectedShippingMethod->price - $selectedShippingMethod->discount_price @endphp

																	 {{ currency_number($selected_shipment_cost, 3) }}
																</span>
															</td>
														</tr>
														<tr>
															<td class="pr-3 py-1">
																{{ __('translation.Discount') }}
															</td>
															<td class="py-1">
																<span class="text-gray-900">-</span>
																<span class="font-bold">
																	{{ currency_symbol('THB') }}
																</span>
															</td>
															<td class="pl-3 py-1 text-right">
																<span class="font-bold">
																	<!-- {{ currency_number($orderManagement->amount_discount_total, 3) }} -->
																	{{ currency_number($total_product_discount, 3) }}
																</span>
															</td>
														</tr>
														<tr @if ($orderManagement->tax_enable != $taxEnableYes) style="display: none;" @endif>
															<td class="pr-3 py-1">
																{{ $taxRateSetting->tax_name ?? '' }} ({{ currency_number($orderManagement->tax_rate, 2) . '%' }})
															</td>
															<td class="py-1">
																<span class="text-white">-</span>
																<span class="font-bold">
																	{{ currency_symbol('THB') }}
																</span>
															</td>
															<td class="pl-3 py-1 text-right">
																<span class="font-bold">
																	@php
																		$vat_amount = $orderManagement->sub_total - $total_product_discount;
																		$vatAmountFinal = ($vat_amount * $orderManagement->tax_rate) / 100;
																	@endphp

				                                                    {{ currency_number($vatAmountFinal, 2) }}
																</span>
															</td>
														</tr>
														<tr>
															<td colspan="3" class="pt-1 border border-dashed border-r-0 border-b-0 border-l-0 border-gray-400"></td>
														</tr>
														<tr>
															<td class="pr-3 py-1 font-bold text-red-500">
																<span class="text-base">
																	{{ __('translation.Total Amount') }}
																</span>
															</td>
															<td class="py-1">
																<span class="text-white text-base">-</span>
																<span class="font-bold text-red-500 text-base">
																	{{ currency_symbol('THB') }}
																</span>
															</td>
															<td class="pl-3 py-1 text-right">
																<span class="font-bold text-red-500 text-base">
																	@php
																		$in_total = ($orderManagement->sub_total + $selected_shipment_cost + $vatAmountFinal) - $total_product_discount;
																	@endphp
                                                					{{ currency_number($in_total, 3) }}
																</span>
															</td>
														</tr>
													</tbody>
												</table>
											</div>
										</x-section.body>
									</x-section.section>

									@if ($orderManagement->payment_method == $paymentMethodBankTransfer)

										@if ($orderManagement->order_status == $orderStatusPendingPayment)
											<x-section.section>
												<x-section.title>
													{{ __('translation.Please Transfer to This Account') }}
												</x-section.title>
												<x-section.body>
													<div>
														<div class="border border-solid border-gray-300 rounded-md p-4">
															<h3 class="mb-5 text-base font-bold">
																{{ __('translation.Bank Transfer') }}
															</h3>
															<p class="mb-2">
																สนใจสั่งซื้อ
															</p>
															<p class="font-bold">
																ธนาคารกสิกรไทย <br>
																เลขบัญชี : 023-3-85884-6<br>
																AC Plus Global Co., Ltd
															</p>
														</div>
													</div>
												</x-section.body>
											</x-section.section>
										@endif


										@if(isset($orderPaymentDeatails))

										@else
											@if($orderManagement->payment_status == $paymentStatusUnPaid AND $orderManagement->payment_method == $paymentMethodBankTransfer)
												<div class="row total_due_wrapper">
													<div class="col-lg-3"></div>
													<div id="total_amount_due" class="col-lg-6">
														{{ __('translation.Total Amount Due') }} : 	{{ currency_symbol('THB') }}
														@if($orderManagement->payment_status == $paymentStatusUnPaid)
															{{ currency_number($in_total, 3) }}
														@endif
													</div>
													<div class="col-lg-3"></div>
												</div>
											@endif
										@endif

			<x-section.section>
				<x-section.title>
					{{ __('translation.Receipt of Transfer') }}
				</x-section.title>
				<x-section.body>

					<div class="mb-3">
						<div>
							<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 md:gap-x-8">
								<div>
									<x-label for="__payment_dateBuyerPage">
										{{ __('translation.Date') }} <x-form.required-mark/>
									</x-label>
									@if (in_array($orderManagement->order_status, $statusForDisableReceiptForm))
										<x-input type="text" name="payment_date" id="__payment_dateBuyerPage" value="{{ date('d-m-Y', strtotime($orderManagement->payments[0]->payment_date)) }}" placeholder="DD-MM-YYYY" disabled />
									@else
										<x-input type="text" name="payment_date" id="__payment_dateBuyerPage" value="{{ date('d-m-Y') }}" placeholder="DD-MM-YYYY" />
									@endif
								</div>
								<div>
									<x-label for="__payment_timeBuyerPage">
										{{ __('translation.Time') }} <x-form.required-mark/>
									</x-label>
									<div class="flex flex-row items-center">
										@if (in_array($orderManagement->order_status, $statusForDisableReceiptForm))
											<x-input type="text" name="payment_time" id="__payment_timeBuyerPage" value="{{ date('H:i', strtotime($orderManagement->payments[0]->payment_time)) }}" placeholder="HH:MM" disabled />
										@else
											<x-input type="text" name="payment_time" id="__payment_timeBuyerPage" placeholder="HH:MM" />
										@endif
									</div>
								</div>
								<div>
									<x-label for="__payment_receiptBuyerPage">
										{{ __('translation.Receipt') }} <x-form.required-mark/>
									</x-label>
									@if (in_array($orderManagement->order_status, $statusForDisableReceiptForm))
										<a href="{{ $orderManagement->payments[0]->payment_slip_url }}" target="_blank" class=" block py-4 hover:underline text-red-500">
											{{ __('translation.View Attachment') }}
										</a>
									@else
										<x-input type="file" name="payment_receipt" id="__payment_receiptBuyerPage" onchange="previewFile(this);" />
									@endif
									<div class="mb-5 mt-3 hide" id="preview_image_div">
									<x-label>
										{{ __('translation.Preview Image') }}
									</x-label>
									<img id="previewImg" width="120" height="120" src="{{asset('img/No_Image_Available.jpg')}}" alt="Placeholder">
								</div>
								</div>
							</div>
					</div>

				</x-section.body>
			</x-section.section>

										@if (!in_array($orderManagement->order_status, $statusForDisableReceiptForm))
											<div class="pb-7">
												<div class="flex flex-col items-center justify-center gap-2 sm:flex-row">
													<x-button type="button" color="" id="__btnChangePaymentMthd">
														<i class="fa fa-pencil"></i>
														<span class="ml-2">
															{{ __('translation.Change Payment Method') }}
														</span>
													</x-button>
													<x-button type="button" color="blue" id="__btnSubmitReceipt">
														<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-4 h-4 bi bi-cursor" viewBox="0 0 16 16">
															<path d="M14.082 2.182a.5.5 0 0 1 .103.557L8.528 15.467a.5.5 0 0 1-.917-.007L5.57 10.694.803 8.652a.5.5 0 0 1-.006-.916l12.728-5.657a.5.5 0 0 1 .556.103zM2.25 8.184l3.897 1.67a.5.5 0 0 1 .262.263l1.67 3.897L12.743 3.52 2.25 8.184z"/>
														</svg>
														<span class="ml-2">
															{{ __('translation.Submit Data') }}
														</span>
													</x-button>
												</div>
											</div>
										@endif

									@endif

									<!-- start For bank transfer payment -->
									@if(isset($orderPaymentDeatails) AND $orderManagement->payment_method == $paymentMethodBankTransfer)
									<div class="row total_due_wrapper">
										<div class="col-lg-3"></div>
										<div id="total_amount_due" class="col-lg-6">
											{{ __('translation.Total Amount Due') }} : 	{{ currency_symbol('THB') }} 0
										</div>
										<div class="col-lg-3"></div>
									</div>
									<x-section.section>
										<x-section.title>
											{{ __('translation.Payment Details') }}
										</x-section.title>
										<x-section.body>
											<span class="text-base">
												{{ __('translation.Payment Method') }} :
												<span class="font_weight_b">
													{{ __('translation.Bank Transfer') }}
												</span>
											</span>
											<br>
											<span class="text-base">
												{{ __('translation.Datetime') }} :
												<span class="font_weight_b">
													{{date('d-M-Y', strtotime($orderPaymentDeatails->payment_date))}}
													{{$orderPaymentDeatails->payment_time}}
												</span>
											</span>
											<br>
											<span class="text-base">
												{{ __('translation.Payment Amount') }} : {{ currency_symbol('THB') }}
												<span class="font_weight_b">
													<!-- {{$orderManagement->in_total}} -->
													{{ currency_number($in_total, 3) }}
												</span>
											</span>
										</x-section.body>
									</x-section.section>
									@endif
									<!-- end For bank transfer payment -->


									<!-- start For instant payment -->
									@if($orderManagement->payment_status == $paymentStatusPaid && isset($orderPaymentDeatails) && $orderManagement->payment_method == $paymentMethodInstant)
									<div class="row total_due_wrapper">
										<div class="col-lg-3"></div>
											<div id="total_amount_due" class="col-lg-6">
												{{ __('translation.Total Amount Due') }} : 	{{ currency_symbol('THB') }}0
											</div>
										<div class="col-lg-3"></div>
									</div>
									<x-section.section>
										<x-section.title>
											{{ __('translation.Payment Details') }}
										</x-section.title>
										<x-section.body>
											<span class="text-base">
												{{ __('translation.Payment Method') }} :
												<span class="font_weight_b">
													{{ __('translation.Instant Payment') }}
												</span>
											</span>
											<br><br>
											@if($orderManagement->payment_method == $paymentMethodInstant)
												<span class="text-base">
													{{ __('translation.Paid By') }} :
													<span class="font_weight_b">@if($orderManagement->payment_channel_from_ksher == 'ktbcard')
														{{ __('translation.Credit Card') }}
														@elseif($orderManagement->payment_channel_from_ksher == 'bbl_promptpay')
															{{ __('translation.Prompt Pay') }}
														@else
															{{ $orderManagement->payment_channel_from_ksher }}
														@endif
													</span>
												</span>
											@endif
											<br>
											<span class="text-base">
												{{ __('translation.Datetime') }} :
												<span class="font_weight_b">
													{{ $orderManagement->payment_date }}
												</span>
											</span>
											<br>
											<span class="text-base">
												{{ __('translation.Payment Amount') }} : {{ currency_symbol('THB') }}
												<span class="font_weight_b">
													<!-- {{ $orderManagement->in_total }} -->
													{{ currency_number($in_total, 3) }}
												</span>
											</span>
										</x-section.body>
									</x-section.section>
									@endif
									<!-- End For instant payment -->

									@if ($orderManagement->payment_method == $paymentMethodInstant AND $orderManagement->payment_status == $paymentStatusUnPaid)
									<div class="row total_due_wrapper">
										<div class="col-lg-3"></div>
										<div id="total_amount_due" class="col-lg-6">
											{{ __('translation.Total Amount Due') }} : 	{{ currency_symbol('THB') }}
											<!-- {{ $orderManagement->in_total }} -->
											{{ currency_number($in_total, 3) }}
										</div>
										<div class="col-lg-3"></div>
									</div>
										<x-section.section>
											<x-section.title>
												{{ __('translation.Please make a payment') }}
											</x-section.title>
											<x-section.body>
												<div class="mb-5">
													<div class="mb-2">
														<p class="text-center font-bold text-lg">
															{{ __('translation.You can pay with one of the following wallet') }}
														</p>
													</div>
													<div>
														<div class="border border-dashed border-gray-300 rounded-md p-4">
															<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
																<div class="flex items-center justify-center">
																	<img src="{{ asset('img/promtpay.png') }}" class="w-full h-auto" alt="PromptPay">
																</div>
																<div class="flex items-center justify-center">
																	<img src="{{ asset('img/shopeepay.png') }}" class="w-full h-auto" alt="Shopee Pay">
																</div>
																<div class="flex items-center justify-center">
																	<img src="{{ asset('img/truemoney.png') }}" class="w-full h-auto" alt="True Money">
																</div>
																<div class="flex items-center justify-center">
																	<img src="{{ asset('img/credit_debit.png') }}" class="w-full h-auto" alt="Credit and debit card Pay">
																</div>
															</div>
														</div>
													</div>
												</div>
												<div>
													<div class="mb-4 text-center text-blue-500">
														{{ __('translation.Click this button bellow to process the payment') }}
													</div>
													<div class="flex items-center justify-center">
														<x-button type="button" class="btn btn-info mr-2" color="" id="__btnChangePaymentMthd">
														<i class="fa fa-angle-double-left"></i>
														<span class="ml-2">
															{{ __('translation.Change Payment Method') }}
														</span>
													</x-button>
														<x-button-link href="{{ $orderManagement->payment_url }}" color="blue">
															<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-4 h-4 bi bi-currency-dollar" viewBox="0 0 16 16">
																<path d="M4 10.781c.148 1.667 1.513 2.85 3.591 3.003V15h1.043v-1.216c2.27-.179 3.678-1.438 3.678-3.3 0-1.59-.947-2.51-2.956-3.028l-.722-.187V3.467c1.122.11 1.879.714 2.07 1.616h1.47c-.166-1.6-1.54-2.748-3.54-2.875V1H7.591v1.233c-1.939.23-3.27 1.472-3.27 3.156 0 1.454.966 2.483 2.661 2.917l.61.162v4.031c-1.149-.17-1.94-.8-2.131-1.718H4zm3.391-3.836c-1.043-.263-1.6-.825-1.6-1.616 0-.944.704-1.641 1.8-1.828v3.495l-.2-.05zm1.591 1.872c1.287.323 1.852.859 1.852 1.769 0 1.097-.826 1.828-2.2 1.939V8.73l.348.086z"/>
															</svg>
															<span class="ml-1">
																{{ __('translation.Pay Now') }}
															</span>
														</x-button-link>
													</div>
												</div>
											</x-section.body>
										</x-section.section>
									@endif
								</div>
							</form>
						</x-card.body>
					</x-card.card-default>

				</div>
			</div>

			@if ((Agent::isMobile() OR Agent::isTablet()) AND ($orderManagement->order_status == $orderStatusPendingPayment OR $orderManagement->order_status == $orderStatusPaymentUnconfirmed OR $orderManagement->payment_status == $paymentStatusPaid))
				<div class="my-4 xl:hidden">
					<div class="flex items-center justify-center">
						<x-button type="button" color="green" onClick="shareThisPage(this)">
							<i class="bi bi-share"></i>
							<span class="ml-2">
								{{ __('translation.Share') }}
							</span>
						</x-button>
					</div>
				</div>
			@endif

			{{-- Any modal should be here --}}
			<x-modal.modal-small id="__modalConfirmPlaceOrder">
				<x-modal.header>
					<x-modal.title>
						{{ __('translation.Confirm') }}
					</x-modal.title>
				</x-modal.header>
				<x-modal.body>

					<x-alert-success id="__alertSuccessConfirmPlaceOrder" class="hidden alert"></x-alert-danger>
					<x-alert-danger id="__alertDangerConfirmPlaceOrder" class="hidden alert"></x-alert-danger>

					<div class="mb-5 text-center">
						<p>
							{{ __('translation.Are you sure to place this order') }}?
						</p>
					</div>
					<div class="pb-3">
						<div class="flex flex-row items-center justify-center gap-1">
							<x-button type="button" color="gray" id="__btnNoModalConfirmPlaceOrder">
								{{ __('translation.No, Close') }}
							</x-button>
							<x-button type="button"  color="green" id="__btnYesModalConfirmPlaceOrder">
								{{ __('translation.Yes, Continue') }}
							</x-button>
						</div>
					</div>
				</x-modal.body>
			</x-modal.modal-small>

			<x-modal.modal-small id="__modalConfirmSubmitReceipt">
				<x-modal.header>
					<x-modal.title>
						{{ __('translation.Confirm') }}
					</x-modal.title>
				</x-modal.header>
				<x-modal.body>

					<x-alert-success id="__alertSuccessConfirmSubmitReceipt" class="hidden alert"></x-alert-danger>
					<x-alert-danger id="__alertDangerConfirmSubmitReceipt" class="hidden alert"></x-alert-danger>

					<div class="mb-5 text-center">
						<p>
							{{ __('translation.Are you sure to submit this receipt data') }}?
						</p>
					</div>
					<div class="pb-3">
						<div class="flex flex-row items-center justify-center gap-1">
							<x-button type="button" color="gray" id="__btnNoModalConfirmSubmitReceipt">
								{{ __('translation.No, Close') }}
							</x-button>
							<x-button type="button" color="red" id="__btnYesModalConfirmSubmitReceipt">
								{{ __('translation.Yes, Continue') }}
							</x-button>
						</div>
					</div>
				</x-modal.body>
			</x-modal.modal-small>

			<x-modal.modal-small id="__modalConfirmChangePayMethod">
				<x-modal.header>
					<x-modal.title>
						{{ __('translation.Confirm') }}
					</x-modal.title>
				</x-modal.header>
				<x-modal.body>
				    <div class="mb-5 text-center">
						<p>
							{{ __('translation.Are you sure to change the payment method') }}?
						</p>
					</div>
					<div class="pb-3">
						<div class="flex flex-row items-center justify-center gap-1">
							<x-button type="button" color="gray" id="__btnNoModalPaymentMethod">
								{{ __('translation.No, Close') }}
							</x-button>
							<x-button type="button" color="green" id="__btnYesModalPaymentMethod">
								{{ __('translation.Yes, Continue') }}
							</x-button>
						</div>
					</div>
				</x-modal.body>
			</x-modal.modal-small>

			<x-modal.modal-medium id="__modalViewProduct">
				<x-modal.header>
					<x-modal.title>
						{{ __('translation.Ordered Products') }}
					</x-modal.title>
					<x-modal.close-button id="__btnCloseModalViewProduct" />
				</x-modal.header>
				<x-modal.body>

					@foreach ($orderManagement->order_management_details as $detail)
						<div class="mb-5 py-4 border border-solid border-t-0 border-r-0 border-l-0 border-gray-200" id="__row_ProductItem_{{ $detail->product->product_code }}">
							<div class="flex flex-row">
								<div class="w-1/4 sm:w-1/4 md:w-1/5 lg:w-1/6 xl:w-1/5 mb-4 md:mb-0">
									<div class="mb-4">
									@if (!empty($detail->product->image) && Storage::disk('s3')->exists($detail->product->image) && !empty($detail->product->image))
									<img src="{{ Storage::disk('s3')->url($detail->product->image) }}" alt="{{ $detail->product->product_name }}" class="w-full h-auto rounded-md">
								  </div>
									@else
									<img src="Storage::disk('s3')->url('uploads/No-Image-Found.png')" class="w-full h-auto rounded-md">
									@endif
								</div>
								<div class="w-3/4 sm:w-3/4 md:w-4/5 lg:w-5/6 xl:w-4/5 ml-4 sm:ml-6">
									<div class="mb-2 xl:mb-4">
										<label class="hidden lg:block mb-0">
											{{ __('translation.Product Name') }} :
										</label>
										<p class="font-bold">
											{{ $detail->product->product_name }} <br>
											<span>{{ $detail->product->product_code }}</span>
										</p>
									</div>
									<div>
										<div class="grid grid-cols-1 lg:grid-cols-2 gap-2 lg:gap-x-8">
											<div>
												<label class="mb-0 block">
													{{ __('translation.Price') }} :
												</label>

												@if ($detail->discount_price == 0)
													<span class="font-bold">
														{{ currency_symbol('THB') }}
														{{ currency_number($detail->price, 3) }}
													</span>
												@endif

												@if ($detail->discount_price > 0)
													@php
														$displayedDiscountPrice =  $detail->discount_price;
													@endphp


													<span class="font-bold line-through text-red-400">
														{{ currency_symbol('THB') }}
														{{ currency_number($detail->price, 3) }}
													</span>
													<span class="ml-3 bg-transparent border-0 outline-none focus:outline-none font-bold btn-product-discount" data-product-code="{{ $detail->product->product_code }}">
														{{ currency_symbol('THB') . ' ' . currency_number($displayedDiscountPrice, 3) }}
													</span>
												@endif

											</div>

											<div>
												<label class="mb-0 block">
													{{ __('translation.Ordered Qty') }} :
												</label>
												<span class="font-bold lg:block">
													{{ number_format($detail->quantity) }}
												</span>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					@endforeach
				</x-modal.body>
			</x-modal.modal-medium>

			@if ($selectedShippingMethod)
				<x-modal.modal-small id="__modalViewShippingMethod">
					<x-modal.header>
						<x-modal.title>
							{{ __('translation.Selected Shipping Method') }}
						</x-modal.title>
						<x-modal.close-button id="__btnCloseModalViewShippingMethod" />
					</x-modal.header>
					<x-modal.body>
						<div class="grid grid-cols-1 gap-2">
							<div>
								<label class="block mb-1">
									{{ __('translation.Service Name') }}:
								</label>
								<p class="font-bold">
									{{ $selectedShippingMethod->shipping_cost->name }}
								</p>
							</div>
							@if ($selectedShippingMethod->shipping_cost->shipper)
								<div>
									<label class="block mb-1">
										{{ __('translation.Shipper Name') }}:
									</label>
									<p class="font-bold">
										{{ $selectedShippingMethod->shipping_cost->shipper->name }}
									</p>
								</div>
							@endif
							<div>
								<label class="block mb-1">
									{{ __('translation.Cost') }}:
								</label>
								<p class="font-bold">

									@if ($selectedShippingMethod->discount_price == 0)
										<span>
											{{ currency_symbol('THB') . ' ' . currency_number($selectedShippingMethod->price, 3) }}
										</span>
									@endif

									@if ($selectedShippingMethod->discount_price > 0)
										@php
											$discountPrice = $selectedShippingMethod->price - $selectedShippingMethod->discount_price;
										@endphp
										<span class="text-red-400 line-through">
											{{ currency_symbol('THB') . ' ' . currency_number($selectedShippingMethod->price, 3) }}
										</span>
										<span class="ml-4">
											{{ currency_symbol('THB') . ' ' . currency_number($discountPrice, 3) }}
										</span>
									@endif
								</p>
							</div>
						</div>
					</x-modal.body>
				</x-modal.modal-small>
			@endif

		</main>

		<footer class="w-full">
			<div class="text-center py-6">
				<span class="text-gray-500">Powered By</span>
				<a href="https://dodoselect.com/" target="_blank" class="no-underline hover:underline text-gray-900 font-bold">Dodoselect.com</a>
			</div>
		</footer>

		<script src="https://cdn.jsdelivr.net/npm/jquery-ui-dist@1.12.1/jquery-ui.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/jquery-timepicker@1.3.3/jquery.timepicker.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

		<script>
			const orderId = '{{ $orderManagement->order_id }}';
			const orderPrimaryId = '{{ $orderManagement->id }}';

			const shippingMethodUpdateUrl = '{{ route('buyer-page.shipping-method.update', [ 'order_id' => $orderManagement->order_id ]) }}';
			const shippingAddressUpdateUrl = '{{ route('buyer-page.shipping-address.update', [ 'order_id' => $orderManagement->order_id ]) }}';
			const paymentReceiptStoreUrl = '{{ route('buyer-page.bank-transfer-confirm.store', [ 'order_id' => $orderManagement->order_id ]) }}';
			const selectProvinceUrl = '{{ route('buyer-page.select-province') }}';
			const selectDistrictUrl = '{{ route('buyer-page.select-district') }}';
			const selectSubDistrictUrl = '{{ route('buyer-page.select-sub-district') }}';
			const selectPostCodeUrl = '{{ route('buyer-page.select-post-code') }}';

			const paymentMethodBankTransfer = {{ $paymentMethodBankTransfer }};
			const paymentMethodInstant = {{ $paymentMethodInstant }};

			const taxRateValue = {{ $taxRateSetting->tax_rate ?? 0 }};
            const taxEnableYes = {{ $taxEnableYes }};

			var subTotal = {{ $orderManagement->sub_total ?? 0 }};
			var shippingCost = {{ $orderManagement->shipping_cost ?? 0 }};
			var originalDiscountTotal = {{ $total_product_discount ?? 0 }};
			var discountTotal = {{ $orderManagement->amount_discount_total ?? 0 }};
			var totalAmount = {{ $orderManagement->in_total ?? 0 }};

			const currentOrderStatus = {{ $orderManagement->order_status }};
			const orderStatusPending = {{ $orderStatusPending }};

			const shippingAddress = {
				provinceCode: -1,
				districtCode: -1,
				subDistrictCode: -1
			};

			const companyAddress = {
                provinceCode: -1,
				districtCode: -1,
				subDistrictCode: -1
            };


			const editableData = () => {
				return currentOrderStatus == orderStatusPending;
			}


			const calculateCartTotal = () => {
				shippingCost = 0;
				discountTotal = originalDiscountTotal;
				totalAmount = 0;


				$('#__shippingMethodOuterWrapper').find('.shipping-method__id-radio-field').each(function() {
					let itemId = $(this).data('id');

					let $shippingItemWrapper = $('#__shippingMethodItem_' + itemId);
					let $nameField = $shippingItemWrapper.find('.shiping-method__name-field');
					let $priceField = $shippingItemWrapper.find('.shiping-method__price-field');
					let $discountField = $shippingItemWrapper.find('.shiping-method__discount-field');


					if ($(this).is(':checked')) {
						let priceValue = parseFloat($priceField.val());
						if (isNaN(priceValue)) {
							priceValue = 0;
						}

						let discountValue = parseFloat($discountField.val());
						if (isNaN(discountValue)) {
							discountValue = 0;
						}

						// if shipping cost has discount then in cart total shipping cost will be new discounted price, Not regular Shipping Price

						if(discountValue > 0 && discountValue !=0){
							priceAfterDiscount = priceValue - discountValue;
							shippingCost += priceAfterDiscount;
						}
						else{
							shippingCost += priceValue;
						}

						//shippingCost += priceValue;
						//discountTotal += discountValue;
					}
				});


                let taxRateAmount = 0;
				let subTotalAndShippingCost = subTotal + shippingCost;
                if (taxRateValue > 0 && parseInt($('input[name="tax_enable"]:checked').val()) === taxEnableYes) {
                    taxRateAmount = (subTotal - discountTotal) * taxRateValue / 100;
                }

                $('#tax_vat_amount').val(taxRateAmount);
                totalAmount = subTotalAndShippingCost - discountTotal + taxRateAmount;

				$('#__subTotalCurrency').html(subTotal.toLocaleString());
				$('#__discountCurrency').html(discountTotal.toLocaleString());
				$('#__shippingCostCurrency').html(shippingCost.toLocaleString());
                $('#__taxRateCurrency').html(taxRateAmount.toLocaleString());
				$('.__grandTotalCurrency').html(totalAmount.toLocaleString());
			}


			$('body').on('change', '.shipping-method__id-radio-field', function() {
				let itemId = $(this).data('id');
				let $shippingItemWrapper = $('#__shippingMethodItem_' + itemId);
				let $selectedInputField = $shippingItemWrapper.find('.shiping-method__selected-input-field');

				$('.shiping-method__selected-input-field').each(function() {
					$(this).val(0);
				});

				$selectedInputField.val(0);
				if ($(this).is(':checked')) {
					$selectedInputField.val(1);
				}

				calculateCartTotal();
			});

			$('#__btnChangePaymentMthd').on('click', function() {
				$('#__modalConfirmChangePayMethod').doModal('open');
			});

			$('#__btnNoModalPaymentMethod').on('click', function() {
				$('#__modalConfirmChangePayMethod').doModal('close');
			});

			$('#__btnYesModalPaymentMethod').on('click', function() {
				$('#__modalConfirmChangePayMethod').doModal('close');
				const formData = new FormData($('#__formBuyerPage')[0]);
				const paymentMethod = formData.get('payment_method');

				$.ajax({
					type: 'POST',
					data:{order_Id:orderPrimaryId},
					url: '{{url('changePaymentMethod')}}',
					headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
					beforeSend: function() {

					},
					success: function(response) {
						const responseData = response.data;

						$('#__confirmationStepContentWrapper').addClass('hidden animate__animated animate__fadeOut');
						$('#__paymentStepContentWrapper').removeClass('hidden').addClass('animate__animated animate__fadeIn');
						$("#order_sts").hide();
						$("#order_sts").html();
						$("#__paymentMethodBuyerPage_2").prop("checked", true);
						$("#__paymentMethodBuyerPage_2").attr("disabled",false);
						$("#__paymentMethodBuyerPage_1").prop("checked", false);
						$("#__paymentMethodBuyerPage_1").attr("disabled",false);
						$("#__instantPaymentMethodWrapper").show();
						$(".__btnNextConfirmationStep").hide();
						$("#place_order_wrapper").hide();
						$("#place_order_div").show();
						$('#__confirmationStepItem').addClass('bg-white text-blue-500').removeClass('bg-blue-500 text-white');
						},
					error: function(error) {
						let responseJson = error.responseJSON;
					}
				});

			});

			$('#__shipping_provinceBuyerPage').select2({
				width: 'resolve',
				placeholder: '- {{ __('translation.Select Province') }} -',
				ajax: {
					type: 'GET',
					url: selectProvinceUrl,
					data: function(params) {
						return {
							page: params.page || 1,
							search: params.term
						};
					},
					delay: 500
				}
			});


			$('#__shipping_districtBuyerPage').select2({
				width: 'resolve',
				placeholder: '- {{ __('translation.Select District') }} -',
				ajax: {
					type: 'GET',
					url: selectDistrictUrl,
					data: function(params) {
						return {
							page: params.page || 1,
							search: params.term,
							province_code: shippingAddress.provinceCode
						};
					},
					delay: 500
				}
			});


			$('#__shipping_sub_districtBuyerPage').select2({
				width: 'resolve',
				placeholder: '- {{ __('translation.Select Sub District') }} -',
				ajax: {
					type: 'GET',
					url: selectSubDistrictUrl,
					data: function(params) {
						return {
							page: params.page || 1,
							search: params.term,
							district_code: shippingAddress.districtCode
						};
					},
					delay: 500
				}
			});


			$('#__shipping_postcodeBuyerPage').select2({
				width: 'resolve',
				placeholder: '- {{ __('translation.Select Postal Code') }} -',
				ajax: {
					type: 'GET',
					url: selectPostCodeUrl,
					data: function(params) {
						return {
							page: params.page || 1,
							search: params.term,
							sub_district_code: shippingAddress.subDistrictCode
						};
					},
					delay: 500
				}
			});


			$('#__shipping_provinceBuyerPage').on('select2:select', function(event) {
				const selectedData = event.params.data;
				shippingAddress.provinceCode = selectedData.code;

				$('#__shipping_districtBuyerPage').attr('disabled', false).trigger('change');
				$('#__shipping_sub_districtBuyerPage').attr('disabled', true).trigger('change');
				$('#__shipping_postcodeBuyerPage').attr('disabled', true).trigger('change');

				$('#__shipping_districtBuyerPage').val(null).trigger('change');
				$('#__shipping_sub_districtBuyerPage').val(null).trigger('change');
				$('#__shipping_postcodeBuyerPage').val(null).trigger('change');
			});


			// $('#__shipping_provinceBuyerPage').on('select2:clear', function(event) {
			// 	shippingAddress.provinceCode = -1;
			// 	shippingAddress.districtCode = -1;
			// 	shippingAddress.subDistrictCode = -1;

			// 	$('#__shipping_districtBuyerPage').val(null).trigger('change');
			// 	$('#__shipping_sub_districtBuyerPage').val(null).trigger('change');
			// 	$('#__shipping_postcodeBuyerPage').val(null).trigger('change');
			// });


			$('#__shipping_districtBuyerPage').on('select2:select', function(event) {
				const selectedData = event.params.data;
				shippingAddress.districtCode = selectedData.code;

				$('#__shipping_sub_districtBuyerPage').attr('disabled', false).trigger('change');
				$('#__shipping_postcodeBuyerPage').attr('disabled', true).trigger('change');

				$('#__shipping_sub_districtBuyerPage').val(null).trigger('change');
				$('#__shipping_postcodeBuyerPage').val(null).trigger('change');
			});


			// $('#__shipping_districtBuyerPage').on('select2:clear', function(event) {
			// 	shippingAddress.districtCode = -1;
			// 	shippingAddress.subDistrictCode = -1;

			// 	$('#__shipping_sub_districtBuyerPage').val(null).trigger('change');
			// 	$('#__shipping_postcodeBuyerPage').val(null).trigger('change');
			// });


			$('#__shipping_sub_districtBuyerPage').on('select2:select', function(event) {
				const selectedData = event.params.data;
				shippingAddress.subDistrictCode = selectedData.code;

				$('#__shipping_postcodeBuyerPage').attr('disabled', false).trigger('change');

				$('#__shipping_postcodeBuyerPage').val(null).trigger('change');
			});


			// $('#__shipping_sub_districtBuyerPage').on('select2:clear', function(event) {
			// 	shippingAddress.subDistrictCode = -1;

			// 	$('#__shipping_postcodeBuyerPage').val(null).trigger('change');
			// });


			$('#__btnNextShippingStep').on('click', function() {
				if (editableData()) {
					const formData = new FormData($('#__formBuyerPage')[0]);

					$.ajax({
						type: 'POST',
						url: shippingMethodUpdateUrl,
						data: formData,
						processData: false,
						contentType: false,
						beforeSend: function() {
							$('#__btnNextShippingStep').attr('disabled', true);
						},
						success: function(response) {
							const responseData = response.data;

							nextToShippingStep();

							setTimeout(() => {
								$('#__btnNextShippingStep').attr('disabled', false);
							}, 500);
						},
						error: function(error) {
							const errorResponse = error.responseJSON;
							let alertMessage = '';

							$('#__btnNextShippingStep').attr('disabled', false);

							if (error.status == 422) {
								let errorFields = Object.keys(errorResponse.errors);
								errorFields.map(field => {
									alertMessage += `- ${errorResponse.errors[field][0]} <br>`
								});

							} else {
								alertMessage = errorResponse.message;

							}

							Swal.fire({
								icon: 'error',
								html: alertMessage
							});
						}
					});

				} else {
					nextToShippingStep();

				}
			});


			const nextToShippingStep = () => {
				$('#__confirmOrderStepContentWrapper').addClass('hidden animate__animated animate__fadeOut');
				$('#__shippingStepContentWrapper').removeClass('hidden').addClass('animate__animated animate__fadeIn');

				$('html, body').animate({
					scrollTop: 0
				}, 500);

				setTimeout(() => {
					$('#__shippingStepItem').addClass('bg-blue-500 text-white').removeClass('bg-white text-blue-500');
				}, 500);

				setTimeout(() => {
					$('#__confirmOrderStepContentWrapper').removeClass('animate__animated animate__fadeOut');
					$('#__shippingStepContentWrapper').removeClass('animate__animated animate__fadeIn');
				}, 1100);
			}


			$('#__btnBackOrderItemStep').on('click', function() {
				backToOrderItemStep();
			});


			const backToOrderItemStep = () => {
				$('#__shippingStepContentWrapper').addClass('hidden animate__animated animate__fadeOut');
				$('#__confirmOrderStepContentWrapper').removeClass('hidden').addClass('animate__animated animate__fadeIn');

				$('html, body').animate({
					scrollTop: 0
				}, 500);

				setTimeout(() => {
					$('#__shippingStepItem').addClass('bg-white text-blue-500').removeClass('bg-blue-500 text-white');
				}, 500);

				setTimeout(() => {
					$('#__shippingStepContentWrapper').removeClass('animate__animated animate__fadeOut');
					$('#__confirmOrderStepContentWrapper').removeClass('animate__animated animate__fadeIn');
				}, 1100);
			}


			$('#__btnNextPaymentStep').on('click', function() {
				if (editableData()) {

					const formData = new FormData($('#__formBuyerPage')[0]);

					$.ajax({
						type: 'POST',
						url: shippingAddressUpdateUrl,
						data: formData,
						processData: false,
						contentType: false,
						beforeSend: function() {
							$('#__btnBackOrderItemStep').attr('disabled', true);
							$('#__btnNextPaymentStep').attr('disabled', true);
						},
						success: function(response) {
							const responseData = response.data;

							nextToPaymentStep();

							setTimeout(() => {
								$('#__btnBackOrderItemStep').attr('disabled', false);
								$('#__btnNextPaymentStep').attr('disabled', false);
							}, 500);
						},
						error: function(error) {
							const errorResponse = error.responseJSON;
							let alertMessage = '';

							$('#__btnBackOrderItemStep').attr('disabled', false);
							$('#__btnNextPaymentStep').attr('disabled', false);

							if (error.status == 422) {
								let errorFields = Object.keys(errorResponse.errors);
								errorFields.map(field => {
									alertMessage += `- ${errorResponse.errors[field][0]} <br>`
								});

							} else {
								alertMessage = errorResponse.message;

							}

							Swal.fire({
								icon: 'error',
								html: alertMessage
							});
						}
					});

				} else {
					nextToPaymentStep();

				}
			});


			const nextToPaymentStep = () => {
				$('#__shippingStepContentWrapper').addClass('hidden animate__animated animate__fadeOut');
				$('#__paymentStepContentWrapper').removeClass('hidden').addClass('animate__animated animate__fadeIn');

				$('html, body').animate({
					scrollTop: 0
				}, 500);

				setTimeout(() => {
					$('#__paymentStepItem').addClass('bg-blue-500 text-white').removeClass('bg-white text-blue-500');
				}, 500);

				setTimeout(() => {
					$('#__shippingStepContentWrapper').removeClass('animate__animated animate__fadeOut');
					$('#__paymentStepContentWrapper').removeClass('animate__animated animate__fadeIn');
				}, 1100);
			}


			$('#__btnBackShippingStep').on('click', function() {
				$('#__paymentStepContentWrapper').addClass('hidden animate__animated animate__fadeOut');
				$('#__shippingStepContentWrapper').removeClass('hidden').addClass('animate__animated animate__fadeIn');

				$('html, body').animate({
					scrollTop: 0
				}, 500);

				setTimeout(() => {
					$('#__paymentStepItem').addClass('bg-white text-blue-500').removeClass('bg-blue-500 text-white');
				}, 500);

				setTimeout(() => {
					$('#__paymentStepContentWrapper').removeClass('animate__animated animate__fadeOut');
					$('#__shippingStepContentWrapper').removeClass('animate__animated animate__fadeIn');
				}, 1100);
			});

			$('#__btnBackShippingStepForChnagePM').on('click', function() {
				$('#__paymentStepContentWrapper').addClass('hidden animate__animated animate__fadeOut');
				$('#__shippingStepContentWrapper').removeClass('hidden').addClass('animate__animated animate__fadeIn');
				$("#place_order_div").show();
				$("#place_order_wrapper").hide();
				$('html, body').animate({
					scrollTop: 0
				}, 500);

				setTimeout(() => {
					$('#__paymentStepItem').addClass('bg-white text-blue-500').removeClass('bg-blue-500 text-white');
				}, 500);

				setTimeout(() => {
					$('#__paymentStepContentWrapper').removeClass('animate__animated animate__fadeOut');
					$('#__shippingStepContentWrapper').removeClass('animate__animated animate__fadeIn');
				}, 1100);
			});


			$('input[name="payment_method"]').on('change', function() {
				let selectedPaymentMethod = paymentMethodInstant;
				if ($(this).is(':checked')) {
					selectedPaymentMethod = $(this).val();
				}

				if (selectedPaymentMethod == paymentMethodInstant) {
					$('#__instantPaymentMethodWrapper').show('medium');
					$('#bank_details_information').addClass('animate__animated animate__fadeIn hidden');
				}

				if (selectedPaymentMethod == paymentMethodBankTransfer) {
					$('#__instantPaymentMethodWrapper').hide('medium');
					$('#bank_details_information').removeClass('hidden').addClass('animate__animated animate__fadeIn');
				}
			});


			$('#__btnPlaceOrder').on('click', function() {
				$('#__modalConfirmPlaceOrder').doModal('open');

			});

			$('#__btnPlaceOrderForChnagePM').on('click', function() {
				$('#__modalConfirmPlaceOrder').doModal('open');

			});

			$('#__btnNoModalConfirmPlaceOrder').on('click', function() {
				$('.alert').addClass('hidden').find('.alert-content').html(null);

				$('#__modalConfirmPlaceOrder').doModal('close');
			});


			$('#__btnYesModalConfirmPlaceOrder').on('click', function() {
				const formData = new FormData($('#__formBuyerPage')[0]);
				const placeOrderUrl = $('#__formBuyerPage').attr('action');
				const paymentMethod = formData.get('payment_method');

				$('#__alertDangerConfirmPlaceOrder').addClass('hidden');
				$('#__alertDangerConfirmPlaceOrder').find('.alert-content').html(null);
				$('#__alertSuccessConfirmPlaceOrder').find('.alert-content').html(null);

				$.ajax({
					type: 'POST',
					data: formData,
					processData: false,
					contentType: false,
					beforeSend: function() {
						$('#__btnNoModalConfirmPlaceOrder').attr('disabled', true);
						$('#__btnYesModalConfirmPlaceOrder').attr('disabled', true).html('Processing...');
					},
					success: function(response) {
						const responseData = response.data;

						$('#__alertSuccessConfirmPlaceOrder').find('.alert-content').html(response.message);
						$('#__alertSuccessConfirmPlaceOrder').removeClass('hidden');

						setTimeout(() => {
							if (paymentMethod == paymentMethodBankTransfer) {
								window.location.reload(false);
							}

							if (paymentMethod == paymentMethodInstant) {
								window.location.href = responseData.payment_url;
							}
						}, 1500);

					},
					error: function(error) {
						let responseJson = error.responseJSON;

						$('#__btnNoModalConfirmPlaceOrder').attr('disabled', false);
						$('#__btnYesModalConfirmPlaceOrder').attr('disabled', false).html('Yes, Continue');

						if (error.status == 422) {
							let errorFields = Object.keys(responseJson.errors);
							errorFields.map(field => {
								$('#__alertDangerConfirmPlaceOrder').find('.alert-content').append(
									$('<span/>', {
										class: 'block mb-1',
										html: `- ${responseJson.errors[field][0]}`
									})
								);
							});

						} else {
							$('#__alertDangerConfirmPlaceOrder').find('.alert-content').html(responseJson.message);

						}

						$('#__alertDangerConfirmPlaceOrder').removeClass('hidden');
					}
				});
			});


			$('#__btnNextConfirmationStep').on('click', function() {
				nextToConfirmationStep();
			});


			const nextToConfirmationStep = () => {
				$('#__modalConfirmPlaceOrder').doModal('close');

				$('#__paymentStepContentWrapper').addClass('hidden animate__animated animate__fadeOut');
				$('#__confirmationStepContentWrapper').removeClass('hidden').addClass('animate__animated animate__fadeIn');

				$('html, body').animate({
					scrollTop: 0
				}, 500);

				setTimeout(() => {
					$('#__confirmationStepItem').addClass('bg-blue-500 text-white').removeClass('bg-white text-blue-500');
				}, 500);

				setTimeout(() => {
					$('#__paymentStepContentWrapper').removeClass('animate__animated animate__fadeOut');
					$('#__confirmationStepContentWrapper').removeClass('animate__animated animate__fadeIn');
				}, 1100);
			}


			$('#__payment_dateBuyerPage').datepicker({
				dateFormat: 'dd-mm-yy',
			});


			$('#__payment_timeBuyerPage').timepicker({
				timeFormat: 'HH:mm',
				interval: 1,
				defaultTime: '09',
				dynamic: false,
				dropdown: true,
				scrollbar: true
			});


			$('#__btnSubmitReceipt').on('click', function() {
				$('#__modalConfirmSubmitReceipt').doModal('open');
			});


			$('#__btnNoModalConfirmSubmitReceipt').on('click', function() {
				$('.alert').addClass('hidden');
				$('#__modalConfirmSubmitReceipt').doModal('close');
			});


			$('#__btnYesModalConfirmSubmitReceipt').on('click', function() {
				const formData = new FormData($('#__formBuyerPage')[0]);

				$.ajax({
					type: 'POST',
					url: paymentReceiptStoreUrl,
					data: formData,
					processData: false,
					contentType: false,
					beforeSend: function() {
						$('#__alertSuccessConfirmSubmitReceipt').addClass('hidden').find('.alert-content').html(null);
						$('#__alertDangerConfirmSubmitReceipt').addClass('hidden').find('.alert-content').html(null);

						$('#__btnNoModalConfirmSubmitReceipt').attr('disabled', true);
						$('#__btnYesModalConfirmSubmitReceipt').attr('disabled', true).html('Processing...');
					},
					success: function(response) {
						let responseMessage = response.message;

						$('#__alertSuccessConfirmSubmitReceipt')
							.removeClass('hidden')
							.find('.alert-content').html(responseMessage);

						setTimeout(() => {
							window.location.reload(false);
						}, 1500);
					},
					error: function(error) {
						const errorResponse = error.responseJSON;
						let alertMessage = '';

						$('#__btnNoModalConfirmSubmitReceipt').attr('disabled', false);
						$('#__btnYesModalConfirmSubmitReceipt').attr('disabled', false).html('Yes, Continue');

						if (error.status == 422) {
							let errorFields = Object.keys(errorResponse.errors);
							errorFields.map(field => {
								alertMessage += `- ${errorResponse.errors[field][0]} <br>`;
							});

						} else {
							alertMessage = errorResponse.message;

						}

						$('#__alertDangerConfirmSubmitReceipt')
							.removeClass('hidden')
							.find('.alert-content').html(alertMessage);
					}
				})
			});


			const viewProduct = () => {
				$('#__modalViewProduct').doModal('open');
			}


			$('#__btnCloseModalViewProduct').on('click', function() {
				$('#__modalViewProduct').doModal('hide');
			});


			const viewShippingMethod = () => {
				$('#__modalViewShippingMethod').doModal('open');
			}


			$('#__btnCloseModalViewShippingMethod').on('click', function() {
				$('#__modalViewShippingMethod').doModal('close');
			});

			function previewFile(input){
                var preview_div = $("#preview_image_div");
                if($(preview_div).hasClass('hide'))
                {
                    $(preview_div).removeClass('hide');
                    $(preview_div).addClass('show');
                }

                var file = $("#__payment_receiptBuyerPage").get(0).files[0];

                if(file){
                    var reader = new FileReader();
                    reader.onload = function(){
                        $("#previewImg").attr("src", reader.result);
                    }
                    reader.readAsDataURL(file);
                }
            }
		</script>
		<script src="{{ asset('js/lang-switcher.js?_=' . rand()) }}"></script>

		@if ($orderManagement->order_status == $orderStatusPending)
			<script>
				$(document).ready(function(){
				 	$("#place_order_div").hide();
				});
			</script>

			<script src="{{ asset('pages/buyer-page/tax_invoice.js?_=' . rand()) }}"></script>
		@endif

		<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.3.3/dist/html2canvas.min.js"></script>

		@if ((Agent::isMobile() OR Agent::isTablet()) AND ($orderManagement->order_status == $orderStatusPendingPayment OR $orderManagement->order_status == $orderStatusPaymentUnconfirmed OR $orderManagement->payment_status == $paymentStatusPaid))
			<script src="{{ asset('pages/buyer-page/share-this-page.js?_=' . rand()) }}"></script>
		@endif

		@stack('bottom_js')

	{{-- @else
		<main class="mt-10">
			<div class="w-11/12 sm:w-3/5 xl:max-w-3xl mx-auto my-10">
				<x-alert-danger>
					Sorry, this order is not available.
				</x-alert-danger>
			</div>
		</main>
	@endif --}}

</body>

</html>
