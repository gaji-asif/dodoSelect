<?php
namespace App\Console\Commands;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Storage;
use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\ProductMainStock;
use DB;
use URL;
use App\Exports\StockExport;
use Phattarachai\LineNotify\Facade\Line;
class ExcelReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'excel:report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Low Stock,Out of Stock Excel Report';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        /*$data1 =  DB::table("products")
            ->join('product_main_stocks','product_main_stocks.product_id','products.id')
            ->get();

            $low_stock = [];
            $out_of_stock = [];
            foreach($data1 as $key=>$row)
                {
                    if($row->quantity > 0 && $row->quantity <= $row->alert_stock){
                        $low_stock[$key]['product_id']= $row->id;
                        $low_stock[$key]['product_name']= $row->product_name;
                        $low_stock[$key]['product_code']= $row->product_code;
                        $low_stock[$key]['quantity']= $row->quantity;
                        $low_stock[$key]['alert_stock']= $row->alert_stock;
                    }

                    if($row->alert_stock != '' && $row->quantity <= 0)
                    {
                        $out_of_stock[$key]['product_id']= $row->id;
                        $out_of_stock[$key]['product_name']= $row->product_name;
                        $out_of_stock[$key]['product_code']= $row->product_code;
                        $out_of_stock[$key]['quantity']= $row->quantity;
                        $out_of_stock[$key]['alert_stock']= $row->alert_stock;
                    }
                }


             $export_low_stock = new StockExport($low_stock);
             $excel = Excel::download($export_low_stock, 'LowStockSheet.xlsx');
             $excel->setContentDisposition('attachment','LowStockSheet')->getFile()->move(public_path('/stock'), 'low-stock-'.time().'.xlsx');

             $export_out_of_stock = new StockExport($out_of_stock);
             $excel = Excel::download($export_out_of_stock, 'OutOfStockSheet.xlsx');
             $excel->setContentDisposition('attachment','OutOfStockSheet')->getFile()->move(public_path('/stock'), 'out-of-stock-'.time().'.xlsx');
             $time = time();
             $message =  view('qrCode.line_notify_stock',compact('time'));
             Line::send($message);*/
    }
}
