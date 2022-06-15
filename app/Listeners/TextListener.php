<?php
namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use LINE\LINEBot;
use LINE\LINEBot\Constant\HTTPHeader;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use Revolution\Line\Facades\Bot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use App\Models\Product;
use App\Models\ExchangeRate;
use DB;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;

class TextListener
{
    /**
     * Handle the event.
     *
     * @param $event
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function handle($event)
    {
        if(isset($event->request->events[0]['message']['text'])):
            $msgText_s = " ";
            $getText = strtolower(trim($event->request->events[0]['message']['text']));
            $httpClient = new CurlHTTPClient(env('LINE_BOT_CHANNEL_ACCESS_TOKEN'));
            $bot = new LINEBot($httpClient, ['channelSecret' => env('LINE_BOT_CHANNEL_SECRET')]);
            $token = $event->request->events[0]['replyToken'];

            if($getText == '/'){
                $shortCode = 'Shortcode: '.chr(10);
                $shortCode .= 'cost =  /cost'.chr(10);
                $shortCode .= 'price =  /price'.chr(10);
                $shortCode .= 'product =  /product'.chr(10);
                $shortCodeMsg = new TextMessageBuilder($shortCode);
                $bot->replyMessage($token, $shortCodeMsg);
                return;
            }

            $arrText = '';
            if(str_starts_with($getText, '/cost') || str_starts_with($getText, '/price') || str_starts_with($getText, '/product')){
                if(str_starts_with($getText, '/cost')){$arrText = explode('/cost',$getText);}
                if(str_starts_with($getText, '/price')){$arrText = explode('/price',$getText);}
                if(str_starts_with($getText, '/product')){$arrText = explode('/product',$getText);}
                $product_code = trim($arrText[1]);

                $data = Product::with('getQuantity')->where('product_code',$product_code)->orderBy('id', 'desc')->get();
                $product_id = $data[0]->id;
                $incoimg_products = \Illuminate\Support\Facades\DB::table('order_purchase_details')
                    ->join('order_purchases', 'order_purchases.id', '=', 'order_purchase_details.order_purchase_id')
                            ->where('order_purchase_details.product_id', '=', $product_id)
                            ->where('order_purchases.status', '=', 'open')
                            ->select('order_purchases.*', 'order_purchase_details.quantity', 'order_purchase_details.order_purchase_id')
                            ->get();


                $img_url = asset('img/dodoselect.png');
                $msgText_c = '';
                foreach($data as $row){
                    $margin = 0;
                    if($row->cost_currency=='RMB'){
                        $exchange = ExchangeRate::where('name', $row->cost_currency)
                            ->orderBy('id', 'desc')
                            ->take(1)
                            ->get();

                            $exchange_rate = $exchange[0]->rate;
                            $cost_price= $row->cost_price . ' '.$row->cost_currency;
                            $final_cost_per_pack = $row->cost_price * $exchange_rate  * $row->ship_cost;
                        }else{
                            $cost_price = $row->cost_price * $row->ship_cost. ' '.$row->cost_currency;
                            $final_cost_per_pack = $row->cost_price * $row->ship_cost;

                        }
                        if($row->price>0){
                            $margin =  (($row->price-$final_cost_per_pack)/$row->price)*100;
                            $margin = number_format($margin, 2);
                        }
                    $product_name =  $row->product_name;
                    $product_code =  $row->product_code;
                    $current_stock = $row->getQuantity->quantity;

                    $msgText_c = 'Name: '.$row->product_name.chr(10);
                    $msgText_c .= 'SKU: '.$row->product_code.chr(10);
                    $msgText_c .= 'Price: ฿'.$row->price.chr(10);
                    $msgText_c .= 'Pieces/Pack: '.$row->pack.chr(10);
                    $msgText_c .= 'Cost/Pack: ฿'.$final_cost_per_pack.chr(10);
                    $msgText_c .= 'Margin: '.$margin.'%'.chr(10);
                    $msgText_c .= 'Lowest Sell Price: ฿'.$row->lowest_value.chr(10);
                    $img_url = asset($row->image);
                }

                $multiMessageBuilder = new MultiMessageBuilder();

                $ImgMsg = new ImageMessageBuilder($img_url, $img_url);
                $multiMessageBuilder->add($ImgMsg);

                $textMsg = new TextMessageBuilder($msgText_c);
                $multiMessageBuilder->add($textMsg);

                $bot->replyMessage($token, $multiMessageBuilder);
                return;

            } elseif(str_starts_with($getText, '/s') || str_starts_with($getText, '/stock')){
                if(str_starts_with($getText, '/s')){$arrText = explode('/s',$getText);}
                if(str_starts_with($getText, '/stock')){$arrText = explode('/stock',$getText);}
                $product_code = trim($arrText[1]);

                $data = Product::with('getQuantity')->where('product_code',$product_code)->orderBy('id', 'desc')->get();
                $product_id = $data[0]->id;
                $incoimg_products = \Illuminate\Support\Facades\DB::table('order_purchase_details')
                    ->join('order_purchases', 'order_purchases.id', '=', 'order_purchase_details.order_purchase_id')
                            ->where('order_purchase_details.product_id', '=', $product_id)
                            ->where('order_purchases.status', '=', 'open')
                            ->select('order_purchases.*', 'order_purchase_details.quantity', 'order_purchase_details.order_purchase_id')
                            ->get();

                $exchange = ExchangeRate::orderBy('id', 'desc')->take(1)->get();

                $img_url = asset('img/dodoselect.png');
                foreach($data as $row){
                    $exchange_rate = $exchange[0]->rate;
                    $margin = 0;
                    if($row->cost_currency=='RMB'){
                        $cost_price= $row->cost_price . ' '.$row->cost_currency;
                        $final_cost_per_pack= $row->cost_price * $exchange_rate  * $row->ship_cost;
                    }else{
                        $cost_price = $row->cost_price * $row->ship_cost. ' '.$row->cost_currency;
                        $final_cost_per_pack = $row->cost_price * $row->ship_cost;

                    }
                    if($row->price>0){
                        $margin =  ($row->price-($final_cost_per_pack)/$row->price)*100;
                        $margin = number_format($margin, 2);
                    }
                    $product_name =  $row->product_name;
                    $product_code =  $row->product_code;
                    $current_stock = $row->getQuantity->quantity;
                    $alert_stock = $row->alert_stock;


                    if($alert_stock != '' && $current_stock <= '0')
                    {
                        $current_status =  'OUT OF STOCK';
                    }
                    elseif($current_stock > 0 && $current_stock <= $alert_stock )
                    {
                        $current_status =  'LOW STOCK';
                    }
                    elseif($alert_stock != '' && $current_stock > $alert_stock )
                    {
                        $current_status =  'OVERSTOCK';
                    }
                    elseif ($alert_stock == '')
                    {
                        $current_status =  'N/A';
                    }

                    $msgText_c = 'Name: '.$row->product_name.chr(10);
                    $msgText_c .= 'SKU: '.$row->product_code.chr(10);
                    $msgText_c .= 'Price: ฿'.$row->price.chr(10);
                    $msgText_c .= 'Pieces/Pack: '.$row->pack.chr(10);
                    $msgText_c .= 'Cost/Pack: ฿'.$final_cost_per_pack.chr(10);
                    $msgText_c .= 'Margin: '.$margin.'%'.chr(10);
                    $msgText_c .= 'Lowest Sell Price: ฿'.$row->lowest_value.chr(10);
                    $img_url = asset($row->image);

                    $product_name = $row->product_name;
                    $sku = $row->product_code.chr(10);
                }


                if(isset($current_status)){
                    $msgText_s = '*** '.$current_status.' ALERT ***'.chr(10);
                }
                if(isset($product_name)){
                    $msgText_s .= 'Name: '.$product_name.chr(10);
                }
                if(isset($product_code)){
                    $msgText_s .= 'SKU: '.$product_code.chr(10);
                }
                if(isset($current_stock)){
                    $msgText_s .= 'QTY: '.$current_stock.chr(10).chr(10);
                }

                if (count($incoimg_products) > 0){
                    $msgText_s .= 'INCOMING: '.chr(10);
                    foreach($incoimg_products as $key=>$purchase){
                        if(isset($purchase->order_purchase_id)){
                            $msgText_s .= '   PO: '.$purchase->order_purchase_id.chr(10);
                        }
                        if(isset($purchase->quantity)){
                            $msgText_s .= '   QTY: '.$purchase->quantity.chr(10);
                        }
                        if(isset($purchase->e_a_d_f)){
                            $msgText_s .= "   ".$purchase->e_a_d_f." to".$purchase->e_a_d_t.chr(10);
                        }
                    }
                }

                $multiMessageBuilder = new MultiMessageBuilder();
                $ImgMsg = new ImageMessageBuilder($img_url, $img_url);
                $multiMessageBuilder->add($ImgMsg);

                $textMsg = new TextMessageBuilder($msgText_s);
                $multiMessageBuilder->add($textMsg);
                $bot->replyMessage($token, $multiMessageBuilder);
                return;
            } else {
                $msgText = 'Product code cannot be found. Please try again.';
                $MessageBuilder = new TextMessageBuilder($msgText);
                $response = $bot->replyMessage($token, $MessageBuilder);

                if ($response->isSucceeded()) {
                    echo 'Succeeded!';
                    return;
                }
            }
        else:
            return response()->json(["status" => "success"], 200);
        endif;
    }
}
