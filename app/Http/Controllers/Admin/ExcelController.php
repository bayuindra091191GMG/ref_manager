<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Rap2hpoutre\FastExcel\FastExcel;

class ExcelController extends Controller
{
    public function excel(Request $request){
        if($request->get('code') == 'repotnya'){
            $excel = new FastExcel([]);

            return $excel->download('test.xlsx');
        }
        else {
            abort(403);
        }
    }

    public function text(Request $request){
        if($request->get('code') == 'repotnya'){
            $branchId = $request->get('branch_id');
            $excel = new FastExcel([]);

            $admin = Auth::guard('admin')->user();
            $now = Carbon::now();
            $yesterdayFromNow = Carbon::now()->addDays(-1);
            $yesterday = $yesterdayFromNow->locale('id_ID')->format('Y-m-d');

            $trxs = DB::connection('mysql_cloud')
                ->table('transaction_headers')
                ->join('transaction_payments', 'transaction_payments.header_id', '=', 'transaction_headers.id')
                ->join('admin_users', 'admin_users.id', '=', 'transaction_headers.created_by')
                ->select('transaction_headers.id as id',
                    'transaction_headers.code as NO_BILL',
                    'transaction_headers.created_at as TGL_TRANSAKSI',
                    'transaction_headers.grand_total as TOTAL_TRANSAKSI',
                    'admin_users.name as KASIR',
                    'transaction_headers.discount as DISCOUNT',
                    'transaction_headers.status_id', 'transaction_headers.branch_id',
                    'transaction_headers.foc_type')
                ->where('transaction_headers.branch_id', $branchId)
                ->where('transaction_headers.status_id', 9)
                ->where('transaction_headers.grand_total', '>', 0)
                // ->whereBetween('transaction_headers.created_at', array($startDate, $endDate))
                ->whereBetween('transaction_headers.created_at', array($yesterday.' 00:00:00', $yesterday.' 23:59:00'))
                ->orderBy('transaction_headers.id')
                ->get();

            $filename = 'transaction_data.txt';

            $string = "NO_BILL|TGL_TRANSAKSI|TOTAL_TRANSAKSI|TAX_AMOUNT|JUML_PENGUNJUNG|KASIR|DISCOUNT\n";
            foreach($trxs as $trx){
                $taxAmount = (($trx->TOTAL_TRANSAKSI * 0.65)) * 0.5;
                // $taxAmount = ((1/1.5) * ($trx->TOTAL_TRANSAKSI)) * 0.5;
                $string .= $trx->NO_BILL."|".$trx->TGL_TRANSAKSI."|".intval($trx->TOTAL_TRANSAKSI)."|".$taxAmount."|1|".$trx->KASIR."|".intval($trx->DISCOUNT)."\n";
            }

            // Create the text file
            $filePath = storage_path('app/public/'.$filename);
            file_put_contents($filePath, $string);

            // Serve the file for download
            return response()->download($filePath, $filename, ['Content-Type' => 'text/plain']);
        }
        else {
            abort(403);
        }
    }

    public function textBulk(Request $request){
        if($request->get('code') == 'repotnya'){
            $branchId = $request->get('branch_id');
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
            $excel = new FastExcel([]);

            $admin = Auth::guard('admin')->user();
            $now = Carbon::now();
            // $yesterdayFromNow = Carbon::now()->addDays(-1);
            // $yesterday = $yesterdayFromNow->locale('id_ID')->format('Y-m-d');
            $period = CarbonPeriod::create($startDate." 00:00:00", $endDate." 23:59:59");
            // dd($period);
            $filename = 'transaction_data.txt';

            $string = "NO_BILL|TGL_TRANSAKSI|TOTAL_TRANSAKSI|TAX_AMOUNT|JUML_PENGUNJUNG|KASIR|DISCOUNT\n";
            foreach ($period as $date) {
                $yesterday = $date->format('Y-m-d');
                // dd($yesterday);

                $trxs = DB::connection('mysql_cloud')
                ->table('transaction_headers')
                ->join('admin_users', 'admin_users.id', '=', 'transaction_headers.created_by')
                ->select('transaction_headers.id as id',
                    'transaction_headers.code as NO_BILL',
                    'transaction_headers.created_at as TGL_TRANSAKSI',
                    'transaction_headers.grand_total as TOTAL_TRANSAKSI',
                    'admin_users.name as KASIR',
                    'transaction_headers.discount as DISCOUNT',
                    'transaction_headers.status_id', 'transaction_headers.branch_id',
                    'transaction_headers.foc_type')
                ->where('transaction_headers.branch_id', $branchId)
                ->where('transaction_headers.status_id', 9)
                ->where('transaction_headers.grand_total', '>', 0)
                // ->whereBetween('transaction_headers.created_at', array($startDate, $endDate))
                ->whereBetween('transaction_headers.created_at', array($yesterday.' 00:00:00', $yesterday.' 23:59:00'))
                ->orderBy('transaction_headers.id')
                ->get();
                // dd($trxs);

                foreach($trxs as $trx){
                    // $taxAmount = (($trx->TOTAL_TRANSAKSI * 0.65)) * 0.5;
                    $taxAmount = intval(((1/1.5) * ($trx->TOTAL_TRANSAKSI)) * 0.5);
                    $string .= $trx->NO_BILL."|".$trx->TGL_TRANSAKSI."|".intval($trx->TOTAL_TRANSAKSI)."|".$taxAmount."|1|".$trx->KASIR."|".intval($trx->DISCOUNT)."\n";
                }
            }

            // Create the text file
            $filePath = storage_path('app/public/'.$filename);
            file_put_contents($filePath, $string);

            // Serve the file for download
            return response()->download($filePath, $filename, ['Content-Type' => 'text/plain']);

        }
        else {
            abort(403);
        }
    }
}
