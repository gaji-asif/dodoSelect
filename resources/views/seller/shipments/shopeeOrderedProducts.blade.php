<div class="row">
 <div class="col-lg-12">
    <h6 class="pt-4"><strong class="order_shipment_color mb-2">{{ __('translation.Ordered Product Details')}} </strong>
    <font class="pull-right float_right color-green font_bold">Total Items : 
    @if(isset($orderDetails)) {{count($orderDetails)}} @endif
    </font>
    </h6>
   @if (isset($orderDetails))
        <div class="full-card show">
            <table class="table table-responsive">
                <thead class="thead-light">
                <tr >
                    <th>{{ __('translation.Image') }}</th>
                    <th>{{ __('translation.Product Details') }}</th>
                </tr>
                </thead>
                <tbody class="table-body">
                  @if (isset($orderDetails))
                    @foreach ($orderDetails as $item)
                        <?php
                        $image_url = asset('No-Image-Found.png');
                        if (isset($item->item_sku) and !empty($item->item_sku)) {
                              $product = \App\Models\ShopeeOrderPurchase::getProductDetails($item->item_sku);
                            if (isset($product) and isset($product->images) and !empty($product->images)) {
                                $images = json_decode($product->images);
                                if (!empty($images[0])) {
                                    $image_url = $images[0];
                                }
                            }
                        }

                        $currency_symbol = '';
                        if(isset($item->currency_symbol) and !empty($item->currency_symbol) and strlen($item->currency_symbol) === 3) {
                            $currency_symbol = currency_symbol($item->currency_symbol);
                        } else {
                            $currency_symbol = currency_symbol('THB');
                        }
                        ?>
                      <tr class="new">
                           <td>
                                @if (!empty($image_url) && file_exists($image_url))
                                    <img src="{{asset($image_url)}}" height="80" width="80" alt="">
                                @else
                                    <img src="{{asset('No-Image-Found.png')}}" height="80" width="80"  alt="">
                                @endif
                            </td>
                            <td>
                                <div class="mb-2">@if (isset($item->item_name))
                                    {{($item->item_name)}}
                                @endif
                                </div>
                               
                                <div class="text-blue-500 margin-bottom-3">
                                @if (isset($item->item_sku))
                                    {{($item->item_sku)}}
                                @endif
                                </div>
                               
                               <div class="margin-bottom-3">
                                <strong>Price:</strong>
                                <?php echo 
                                $currency_symbol . number_format(floatval($item->variation_original_price), 2);
                                ?>
                               </div>
                                <div class="margin-bottom-3">
                                <strong>Ordered Quantity: </strong>
                                <input type="number" class="ordered_quantity" name="product_quantity[]" min='1' style="width:50px" class="form-control rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" value='{{number_format($item->variation_quantity_purchased)}}'>
                              </div>
                            </td>
                        </tr>
                    @endforeach
                @endif
                </tbody>
            </table>
        </div>
    @endif

</div>
</div>
