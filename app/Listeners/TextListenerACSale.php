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

class TextListenerACSale
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
            $getText = strtolower(trim($event->request->events[0]['message']['text']));
            $httpClient = new CurlHTTPClient(env('LINE_BOT_AC_SALE_CHANNEL_ACCESS'));
            $bot = new LINEBot($httpClient, ['channelSecret' => env('LINE_BOT_AC_SALE_CHANNEL_SECRET')]);
            $token = $event->request->events[0]['replyToken'];

            if($getText == '/'){
                $shortCode = 'Shortcode: '.chr(10);
                $shortCode .= 'low =  /low'.chr(10);
                $shortCodeMsg = new TextMessageBuilder($shortCode);
                $bot->replyMessage($token, $shortCodeMsg);
                return;
            }

            $arrText = '';
            if(str_starts_with($getText, '/low')){
                if(str_starts_with($getText, '/low')){$arrText = explode('/low',$getText);}
                $product_code = trim($arrText[1]);

                $data = Product::where('product_code', $product_code)->get();

                $img_url = asset('img/default.jpeg');
                $msgText_c = '';
                foreach($data as $row){
                    $msgText_c .= 'Product Name: '.$row->product_name.chr(10);
                    $msgText_c .= 'SKU: '.$row->product_code.chr(10);
                    $msgText_c .= 'Pieces Per Pack: '.$row->pack.chr(10);
                    $msgText_c .= '------------------------------'.chr(10);
                    $msgText_c .= 'Normal Sell Price: ฿'.$row->price.chr(10);
                    $msgText_c .= 'Lowest Sell Price: ฿'.$row->lowest_sell_price.chr(10);
                    $msgText_c .= 'Quantity: '.$row->getQuantity->quantity.chr(10);
                    $file_headers = @get_headers(asset($row->image));
                    $exists = false;
                    if(!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
                        $exists = false;
                    }  else {
                        $exists = true;
                    }

                    if($exists){
                        $img_url = asset($row->image);
                    }
                }
                $multiMessageBuilder = new MultiMessageBuilder();
                $ImgMsg = new ImageMessageBuilder($img_url, $img_url);
                $multiMessageBuilder->add($ImgMsg);
                $textMsg = new TextMessageBuilder($msgText_c);
                $multiMessageBuilder->add($textMsg);
                $bot->replyMessage($token, $multiMessageBuilder);
                return;
            } else {
                /*$msgText = 'Oops! Couldn\'t find lowest sell price with this code. Incorrect product code maybe? Please try again...';
                $MessageBuilder = new TextMessageBuilder($msgText);
                $bot->replyMessage($token, $MessageBuilder);*/
                return;
            }
        else:
            return response()->json(["status" => "success"], 200);
        endif;
    }
}
