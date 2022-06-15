<?php

namespace App\Http\Requests\CustomOrder;

use App\Models\Channel;
use App\Models\CustomOrder;
use App\Models\Shop;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateRequest extends FormRequest
{

    /**
     * Define properties
     *
     * @var mixed
     */
    private $allowedShopIds = [];
    private $allowedChanneldIds = [];

    /**
     * Create new instance
     *
     * @return void
     */
    public function __construct()
    {
        $sellerId = Auth::user()->id;

        $channels = Channel::selectRaw('id')->where('seller_id', $sellerId)->where('display_channel', 1)->get();
        $channels->map(function($channel) {
            array_push($this->allowedChanneldIds, $channel->id);
        });

        $shops = Shop::selectRaw('id')->where('seller_id', $sellerId)->get();
        $shops->map(function($shop) {
            array_push($this->allowedShopIds, $shop->id);
        });
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id' => [
                'required', 'integer'
            ],
            'shop_id' => [
                'required', 'integer', 'in:' . implode(',' , $this->allowedShopIds)
            ],
            'channel_id' => [
                'required', 'integer', 'in:' . implode(',', $this->allowedChanneldIds)
            ],
            'contact_name' => [
                'required', 'string', 'min:3', 'max:50'
            ],
            'customer_name' => [
                'required', 'string', 'min:3', 'max:50'
            ],
            'contact_phone' => [
                'required', 'string', 'min:10', 'max:20'
            ],
            'product_id' => [
                'required', 'array'
            ],
            'product_id.*' => [
                'required', 'integer', 'min:1'
            ],
            'product_name' => [
                'required', 'array'
            ],
            'product_name.*' => [
                'required', 'string', 'min:3', 'max:100'
            ],
            'product_price' => [
                'required', 'array'
            ],
            'product_price.*' => [
                'required', 'min:0'
            ],
            'quantity' => [
                'required', 'array'
            ],
            'quantity.*' => [
                'required', 'integer', 'min:1'
            ],
            'product_description' => [
                'required', 'array'
            ],
            'product_description.*' => [
                'nullable', 'string'
            ],
            // 'product_image_one' => [
            //     'nullable',
            //     'array'
            // ],
            // 'product_image_one.*' => [
            //     'nullable',
            //     'image',
            //     'mimetypes:image/jpg,image/jpeg,image/png',
            //     'max:5120'
            // ],
            // 'product_image_two' => [
            //     'nullable',
            //     'array'
            // ],
            // 'product_image_two.*' => [
            //     'nullable',
            //     'image',
            //     'mimetypes:image/jpg,image/jpeg,image/png',
            //     'max:5120'
            // ],
            // 'product_image_three' => [
            //     'nullable',
            //     'array'
            // ],
            // 'product_image_three.*' => [
            //     'nullable',
            //     'image',
            //     'mimetypes:image/jpg,image/jpeg,image/png',
            //     'max:5120'
            // ],
            // 'product_image_three' => [
            //     'nullable',
            //     'array'
            // ],
            // 'product_image_three.*' => [
            //     'nullable',
            //     'image',
            //     'mimetypes:image/jpg,image/jpeg,image/png',
            //     'max:5120'
            // ],
            // 'product_image_four' => [
            //     'nullable',
            //     'array'
            // ],
            // 'product_image_four.*' => [
            //     'nullable',
            //     'image',
            //     'mimetypes:image/jpg,image/jpeg,image/png'
            // ],
            'shipping_name' => [
                'required', 'string', 'min:3', 'max:50'
            ],
            'shipping_cost' => [
                'required', 'min:0'
            ],
            'order_status' => [
                'required', 'in:' . implode(',', array_keys(CustomOrder::getAllOrderStatus()))
            ],
            'payment_status' => [
                'required', 'in:' . implode(',', array_keys(CustomOrder::getAllPaymentStatus()))
            ]
        ];
    }

    /**
     * Get the attributes name
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'id' => 'ID',
            'shop_id' => 'Shop',
            'channel' => 'Channel Name',
            'contact_name' => 'Channel ID',
            'customer_name' => 'Customer Name',
            'contact_phone' => 'Customer Phone Number',
            'product_id.*' => 'Products ID',
            'product_name.*' => 'Products Name',
            'product_price.*' => 'Products Price',
            'quantity.*' => 'Products Quantity',
            'product_description.*' => 'Products Description',
            // 'product_image_one.*' => 'Product Images',
            // 'product_image_two.*' => 'Product Images',
            // 'product_image_three.*' => 'Product Images',
            // 'product_image_four.*' => 'Product Images',
            'shipping_name' => 'Shipping Name',
            'shipping_cost' => 'Shipping Cost',
            'order_status' => 'Order Status',
            'payment_status' => 'Payment Status'
        ];
    }
}
