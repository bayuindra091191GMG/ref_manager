<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\BookingHeader;
use App\Models\Employee;
use App\Models\Item;
use App\Models\Package;
use App\Models\Partner;
use App\Models\PreweddingHeader;
use App\Models\TransactionDetail;
use App\Models\TransactionHeader;
use App\Models\TransactionInvoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request){
        $user = $request->user();
        $admin = DB::table('admin_users')->where('admin_users.id', '=', $user->id)->first();

        $filterMonths = [];
        $filterYears = [];

        $currDate = Carbon::now('Asia/Jakarta');
        $now = Carbon::now('Asia/Jakarta');
        $startDate = $currDate->addMonth(-1)->startOfDay();
        $endDate = Carbon::now('Asia/Jakarta');
        $nYear = [
            'value'     => $now->year,
            'display'   => $now->year,
        ];

        $filterYears[] = $nYear;

        for($i=0; $i<12; $i++){
            $nMonth = [
                'value'     => $now->month,
                'display'   => $now->locale('id_ID')->format('M'),
            ];

            $filterMonths[] = $nMonth;
            $now->addMonths(-1);
        }
        if($request->start_date != null && $request->end_date != null){
          $startDateDecoded = rawurldecode($request->start_date);
          $endDateDecoded = rawurldecode($request->end_date);
          $startDateObj = Carbon::parse($startDateDecoded, 'Asia/Jakarta')->startOfDay();
          $endDateObj = Carbon::parse($endDateDecoded, 'Asia/Jakarta')->endOfDay();

          if($endDateObj->greaterThanOrEqualTo($startDateObj)){
            $startDate = $startDateObj;
            $endDate = $endDateObj;
          }
        }

        $data = [
            'admin' => $admin,
            'startDate' => $startDate->format('d-m-Y'),
            'endDate' => $endDate->format('d-m-Y'),
            'filterMonths' => json_encode($filterMonths),
            'filterYears' => json_encode($filterYears),
            'chosenFilterMonth' => $currDate->month,
            'chosenFilterYear' => $currDate->year,
        ];

        return view('admin.dashboard')->with($data);
    }

    public function getTotalGross(Request $request){
        try{
            $user = Auth::guard('admin')->user();
            $startDate = Carbon::createFromFormat('!d-m-Y', $request->query('start_date'), 'Asia/Jakarta')->startOfDay();
            $endDate = Carbon::createFromFormat('!d-m-Y', $request->query('end_date'), 'Asia/Jakarta')->endOfDay();

            $total = TransactionInvoice::where('branch_id', $user->branch_id)
                ->where('status_id', 9)
                ->where('created_at', '>=', $startDate->todateTimeString())
                    ->where('created_at', '<=', $endDate->todateTimeString())
                ->sum('subtotal');

            return Response::json($total, 200);
        }
        catch (\Exception $ex){
            return Response::json($ex->getMessage(), 500);
        }
    }

    public function getTotalNet(Request $request){
        try{
            $user = Auth::guard('admin')->user();
            $startDate = Carbon::createFromFormat('!d-m-Y', $request->query('start_date'), 'Asia/Jakarta')->startOfDay();
            $endDate = Carbon::createFromFormat('!d-m-Y', $request->query('end_date'), 'Asia/Jakarta')->endOfDay();

            $total = TransactionInvoice::where('branch_id', $user->branch_id)
                ->where('status_id', 9)
                ->where('created_at', '>=', $startDate->todateTimeString())
                    ->where('created_at', '<=', $endDate->todateTimeString())
                ->sum('grandtotal');


            return Response::json($total, 200);
        }
        catch (\Exception $ex){
            return Response::json($ex->getMessage(), 500);
        }
    }

    public function getTotalDiscount(Request $request){
        try{
            $user = Auth::guard('admin')->user();
            $startDate = Carbon::createFromFormat('!d-m-Y', $request->query('start_date'), 'Asia/Jakarta')->startOfDay();
            $endDate = Carbon::createFromFormat('!d-m-Y', $request->query('end_date'), 'Asia/Jakarta')->endOfDay();

            $total = TransactionInvoice::where('branch_id', $user->branch_id)
                ->where('status_id', 9)
                ->where('created_at', '>=', $startDate->todateTimeString())
                    ->where('created_at', '<=', $endDate->todateTimeString())
                ->sum('voucher_discount_value');


            return Response::json($total, 200);
        }
        catch (\Exception $ex){
            return Response::json($ex->getMessage(), 500);
        }
    }

    public function getTotalCustomer(Request $request){
        try{
            $user = Auth::guard('admin')->user();
            $startDate = Carbon::createFromFormat('!d-m-Y', $request->query('start_date'), 'Asia/Jakarta')->startOfDay();
            $endDate = Carbon::createFromFormat('!d-m-Y', $request->query('end_date'), 'Asia/Jakarta')->endOfDay();

            $totalMale = TransactionHeader::where('branch_id', $user->branch_id)
                ->where('status_id', 9)
                ->whereBetween('transaction_headers.created_at', [$startDate->todateTimeString(), $endDate->todateTimeString()])
                ->sum('total_cust_male');

            $totalFemale = TransactionHeader::where('branch_id', $user->branch_id)
                ->where('status_id', 9)
                ->whereBetween('transaction_headers.created_at', [$startDate->todateTimeString(), $endDate->todateTimeString()])
                ->sum('total_cust_female');

            $data = [
                'male' => $totalMale,
                'female' => $totalFemale,
            ];
            return Response::json($data, 200);
        }
        catch (\Exception $ex){
            return Response::json($ex->getMessage(), 500);
        }
    }

    public function getTotalTransaction(Request $request){
        try{
            $user = Auth::guard('admin')->user();

            $startDate = Carbon::createFromFormat('!d-m-Y', $request->query('start_date'), 'Asia/Jakarta')->startOfDay();
            $endDate = Carbon::createFromFormat('!d-m-Y', $request->query('end_date'), 'Asia/Jakarta')->endOfDay();

            $total = TransactionInvoice::where('branch_id', $user->branch_id)
                ->where('status_id', 9)
                ->where('created_at', '>=', $startDate->todateTimeString())
                    ->where('created_at', '<=', $endDate->todateTimeString())
                ->count();
            return Response::json($total, 200);
        }
        catch (\Exception $ex){
            return Response::json($ex->getMessage(), 500);
        }
    }

    public function getTotalAverage(Request $request){
        try{
            $user = Auth::guard('admin')->user();
            $startDate = Carbon::createFromFormat('!d-m-Y', $request->query('start_date'), 'Asia/Jakarta')->startOfDay();
            $endDate = Carbon::createFromFormat('!d-m-Y', $request->query('end_date'), 'Asia/Jakarta')->endOfDay();

            $total = TransactionInvoice::where('branch_id', $user->branch_id)
                ->where('status_id', 9)
                ->where('created_at', '>=', $startDate->todateTimeString())
                    ->where('created_at', '<=', $endDate->todateTimeString())
                ->avg('grandtotal');

            return Response::json($total, 200);
        }
        catch (\Exception $ex){
            return Response::json($ex->getMessage(), 500);
        }
    }

    public function getBookingSummary(Request $request)
    {
        try {
            $user = Auth::guard('admin')->user();
            $today = Carbon::now('Asia/Jakarta')->toDateString();

            $summary = [
                'today_booking' => BookingHeader::where('branch_id', $user->branch_id)
                    ->whereNotIn('status_id', [2, 10])
                    ->whereDate('booking_date', $today)
                    ->count(),
                'booking_not_approve' => BookingHeader::where('branch_id', $user->branch_id)
                    ->whereIn('status_id', [3, 14])
                    ->whereNotNull('payment_proof')
                    ->count(),
                'new_booking' => BookingHeader::where('branch_id', $user->branch_id)
                    ->whereNotIn('status_id', [2, 10])
                    ->count(),
            ];

            return Response::json($summary, 200);
        } catch (\Exception $ex) {
            return Response::json($ex->getMessage(), 500);
        }
    }

    public function getPendapatan(Request $request){
        try{
            $user = Auth::guard('admin')->user();

            $startDate = Carbon::createFromFormat('!d-m-Y', $request->query('start_date'), 'Asia/Jakarta')->startOfDay();
            $endDate = Carbon::createFromFormat('!d-m-Y', $request->query('end_date'), 'Asia/Jakarta')->endOfDay();

            $type = "StudioNominal";
            if($request->exists('type')){
                $type = $request->query('type');
            }
            switch($type){
                case "StudioNominal" :
                    $labelLists = Package::select('id', 'name')->whereIn('room_type_id', [6])->where('status_id', 1)->get();
                    break;
                case "SalonNominal" :
                    $labelLists = Package::select('id', 'name')->whereIn('room_type_id', [4])->where('status_id', 1)->get();
                    break;
                case "SportNominal" :
                    $labelLists = Package::select('id', 'name')->whereIn('room_type_id', [26])->where('status_id', 1)->get();
                    break;
                case "KafeNominal" :
                    $labelLists = Item::select('id', 'name')->whereIn('category_id', [2,28])->where('status_id', 1)->get();
                    break;
                case "SpaNominal" :
                    $labelLists = Package::select('id', 'name')->whereIn('room_type_id', [1,2,3,5,25])->where('status_id', 1)->get();
                    $labelLists2 = Item::select('id', 'name')->whereIn('category_id', [27])->where('status_id', 1)->get();
                    break;
            }
            $total = 0;
            if($type == "KafeNominal"){
                foreach($labelLists as $labelList){
                    $transactionAmount = TransactionDetail::join('transaction_headers', 'transaction_headers.id', '=', 'transaction_details.header_id')
                        ->join('transaction_invoices', 'transaction_invoices.header_ids', '=', 'transaction_headers.id')
                        ->where('transaction_headers.branch_id', $user->branch_id)
                        ->where('transaction_details.item_id', $labelList->id)
                        ->where('transaction_invoices.status_id', 9)
                        ->whereBetween('transaction_details.start_time', [$startDate->todateTimeString(), $endDate->todateTimeString()])
                        ->sum('transaction_details.subtotal');
                    $total += $transactionAmount;
                }
            }
            else{
                if($type == "SpaNominal"){
                    foreach($labelLists as $labelList){
                        $transactionAmount = TransactionDetail::join('transaction_headers', 'transaction_headers.id', '=', 'transaction_details.header_id')
                            ->join('transaction_invoices', 'transaction_invoices.header_ids', '=', 'transaction_headers.id')
                            ->where('transaction_headers.branch_id', $user->branch_id)
                            ->where('transaction_details.package_id', $labelList->id)
                            ->where('transaction_invoices.status_id', 9)
                            ->whereBetween('transaction_details.start_time', [$startDate->todateTimeString(), $endDate->todateTimeString()])
                            ->sum('transaction_details.subtotal');
                        $discountAmount = TransactionDetail::join('transaction_headers', 'transaction_headers.id', '=', 'transaction_details.header_id')
                            ->join('transaction_invoices', 'transaction_invoices.header_ids', '=', 'transaction_headers.id')
                            ->where('transaction_headers.branch_id', $user->branch_id)
                            ->where('transaction_details.package_id', $labelList->id)
                            ->where('transaction_invoices.status_id', 9)
                            ->whereBetween('transaction_details.start_time', [$startDate->todateTimeString(), $endDate->todateTimeString()])
                            ->sum('transaction_details.discount_amount');
                        $total += ($transactionAmount - $discountAmount);
                    }
                    foreach($labelLists2 as $labelList){
                        $transactionAmount = TransactionDetail::join('transaction_headers', 'transaction_headers.id', '=', 'transaction_details.header_id')
                            ->join('transaction_invoices', 'transaction_invoices.header_ids', '=', 'transaction_headers.id')
                            ->where('transaction_headers.branch_id', $user->branch_id)
                            ->where('transaction_details.item_id', $labelList->id)
                            ->where('transaction_invoices.status_id', 9)
                            ->whereBetween('transaction_details.start_time', [$startDate->todateTimeString(), $endDate->todateTimeString()])
                            ->sum('transaction_details.subtotal');
                        $discountAmount = TransactionDetail::join('transaction_headers', 'transaction_headers.id', '=', 'transaction_details.header_id')
                            ->join('transaction_invoices', 'transaction_invoices.header_ids', '=', 'transaction_headers.id')
                            ->where('transaction_headers.branch_id', $user->branch_id)
                            ->where('transaction_details.item_id', $labelList->id)
                            ->where('transaction_invoices.status_id', 9)
                            ->whereBetween('transaction_details.start_time', [$startDate->todateTimeString(), $endDate->todateTimeString()])
                            ->sum('transaction_details.discount_amount');
                        $total += ($transactionAmount - $discountAmount);
                    }

                    // //get pranikah
                    // $transactionAmount = PreweddingHeader::join('transaction_invoices', 'transaction_invoices.header_ids', '=', 'prewedding_headers.transaction_id')
                    //     ->where('transaction_invoices.status_id', 9)
                    //     ->whereBetween('prewedding_headers.created_at', [$startDate->todateTimeString(), $endDate->todateTimeString()])
                    //     ->sum('transaction_invoices.subtotal');
                    // $total += $transactionAmount;

                }
                else if($type == "StudioNominal"){
                    foreach($labelLists as $labelList){
                        $transactionAmount = TransactionDetail::join('transaction_headers', 'transaction_headers.id', '=', 'transaction_details.header_id')
                            ->join('transaction_invoices', 'transaction_invoices.header_ids', '=', 'transaction_headers.id')
                            ->where('transaction_headers.branch_id', $user->branch_id)
                            ->where('transaction_details.package_id', $labelList->id)
                            ->where('transaction_invoices.status_id', 9)
                            ->whereBetween('transaction_details.start_time', [$startDate->todateTimeString(), $endDate->todateTimeString()])
                            ->sum('transaction_details.subtotal');
                        $discountAmount = TransactionDetail::join('transaction_headers', 'transaction_headers.id', '=', 'transaction_details.header_id')
                            ->join('transaction_invoices', 'transaction_invoices.header_ids', '=', 'transaction_headers.id')
                            ->where('transaction_headers.branch_id', $user->branch_id)
                            ->where('transaction_details.package_id', $labelList->id)
                            ->where('transaction_invoices.status_id', 9)
                            ->whereBetween('transaction_details.start_time', [$startDate->todateTimeString(), $endDate->todateTimeString()])
                            ->sum('transaction_details.discount_amount');
                        $roundingAmount = TransactionDetail::join('transaction_headers', 'transaction_headers.id', '=', 'transaction_details.header_id')
                            ->join('transaction_invoices', 'transaction_invoices.header_ids', '=', 'transaction_headers.id')
                            ->where('transaction_headers.branch_id', $user->branch_id)
                            ->where('transaction_details.package_id', $labelList->id)
                            ->where('transaction_invoices.status_id', 9)
                            ->whereBetween('transaction_details.start_time', [$startDate->todateTimeString(), $endDate->todateTimeString()])
                            ->sum('transaction_invoices.rounding_amount');
                        $total += ($transactionAmount - $discountAmount) + $roundingAmount;
                    }
                }
                else{
                    foreach($labelLists as $labelList){
                        $transactionAmount = TransactionDetail::join('transaction_headers', 'transaction_headers.id', '=', 'transaction_details.header_id')
                            ->join('transaction_invoices', 'transaction_invoices.header_ids', '=', 'transaction_headers.id')
                            ->where('transaction_headers.branch_id', $user->branch_id)
                            ->where('transaction_details.package_id', $labelList->id)
                            ->where('transaction_invoices.status_id', 9)
                            ->where('transaction_details.start_time', '>=', $startDate->todateTimeString())
                            ->where('transaction_details.start_time', '<=', $endDate->todateTimeString())
                            // ->whereBetween('transaction_details.start_time', [$startDate->todateTimeString(), $endDate->todateTimeString()])
                            ->sum('transaction_details.subtotal');
                        $discountAmount = TransactionDetail::join('transaction_headers', 'transaction_headers.id', '=', 'transaction_details.header_id')
                            ->join('transaction_invoices', 'transaction_invoices.header_ids', '=', 'transaction_headers.id')
                            ->where('transaction_headers.branch_id', $user->branch_id)
                            ->where('transaction_details.package_id', $labelList->id)
                            ->where('transaction_invoices.status_id', 9)
                            ->where('transaction_details.start_time', '>=', $startDate->todateTimeString())
                            ->where('transaction_details.start_time', '<=', $endDate->todateTimeString())
                            // ->whereBetween('transaction_details.start_time', [$startDate->todateTimeString(), $endDate->todateTimeString()])
                            ->sum('transaction_details.discount_amount');
                        $total += ($transactionAmount - $discountAmount);
                    }
                }
            }


            return Response::json($total, 200);
        }
        catch (\Exception $ex){
            //dd($ex);
            return Response::json($ex->getMessage(), 500);
        }
    }


    /**
     * Get transaction data for barchart data
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     *
     * @throws \Exception
     */
    public function getTransaction(Request $request){
        try{
            $user = Auth::guard('admin')->user();
            $result = [];
            $now = Carbon::now();


            $type = "SalonQty";
            if($request->exists('type')){
                $type = $request->query('type');
            }
            $startDate = Carbon::createFromFormat('!d-m-Y', $request->query('start_date'), 'Asia/Jakarta')->startOfDay();
            $endDate   = Carbon::createFromFormat('!d-m-Y', $request->query('end_date'), 'Asia/Jakarta')->endOfDay();


            switch($type){
                case "SalonQty" :
                    $labelLists = Package::select('id', 'name')->whereIn('room_type_id', [4])->where('status_id', 1)->get();
                    foreach($labelLists as $labelList){
                        $totalAmount = 0;
                        $totalAmount += TransactionDetail::whereHas('package', function ($query) use ($labelList) {
                            $query->where('id', $labelList->id);
                        })
                            ->join('transaction_headers', 'transaction_headers.id', '=', 'transaction_details.header_id')
                            ->join('transaction_invoices', 'transaction_invoices.header_ids', '=', 'transaction_headers.id')
                            ->where('transaction_invoices.status_id', 9)
                            ->where('transaction_details.start_time', '>=', $startDate->todateTimeString())
                            ->where('transaction_details.start_time', '<=', $endDate->todateTimeString())
                            ->sum('transaction_details.qty');
                        if ($totalAmount != 0) {
                            $result[] = [
                                'labels' => $labelList->name,
                                'amount' => $totalAmount
                            ];
                        }
                    }
                    break;
                case "SalonNominal" :
                    $labelLists = Package::select('id', 'name')->whereIn('room_type_id', [4])->where('status_id', 1)->get();
                    foreach($labelLists as $labelList){
                        $totalAmount = 0;
                        $totalAmount += TransactionDetail::whereHas('package', function ($query) use ($labelList) {
                            $query->where('id', $labelList->id);
                        })
                            ->join('transaction_headers', 'transaction_headers.id', '=', 'transaction_details.header_id')
                            ->join('transaction_invoices', 'transaction_invoices.header_ids', '=', 'transaction_headers.id')
                            ->where('transaction_invoices.status_id', 9)
                            ->where('transaction_details.start_time', '>=', $startDate->todateTimeString())
                            ->where('transaction_details.start_time', '<=', $endDate->todateTimeString())
                            ->sum("transaction_details.subtotal");
                        if ($totalAmount != 0) {
                            $result[] = [
                                'labels' => $labelList->name,
                                'amount' => $totalAmount
                            ];
                        }
                    }
                    break;
                case "KafeQty" :
                    $labelLists = Item::select('id', 'name')->whereIn('category_id', [2,28])->where('status_id', 1)->where('id', '!=', 68)->get();
                    foreach($labelLists as $labelList){
                        $totalAmount = 0;
                        $totalAmount += TransactionDetail::whereHas('item', function ($query) use ($labelList) {
                            $query->where('id', $labelList->id);
                        })
                            ->join('transaction_headers', 'transaction_headers.id', '=', 'transaction_details.header_id')
                            ->join('transaction_invoices', 'transaction_invoices.header_ids', '=', 'transaction_headers.id')
                            ->where('transaction_invoices.status_id', 9)
                            ->where('transaction_details.start_time', '>=', $startDate->todateTimeString())
                            ->where('transaction_details.start_time', '<=', $endDate->todateTimeString())
                            ->sum('transaction_details.qty');
                        if ($totalAmount != 0) {
                            $result[] = [
                                'labels' => $labelList->name,
                                'amount' => $totalAmount
                            ];
                        }
                    }
                    break;
                case "KafeNominal" :
                    $labelLists = Item::select('id', 'name')->whereIn('category_id', [2,28])->where('status_id', 1)->where('id', '!=', 68)->get();
                    foreach($labelLists as $labelList){
                        $totalAmount = 0;
                        $totalAmount += TransactionDetail::whereHas('item', function ($query) use ($labelList) {
                            $query->where('id', $labelList->id);
                        })
                            ->join('transaction_headers', 'transaction_headers.id', '=', 'transaction_details.header_id')
                            ->join('transaction_invoices', 'transaction_invoices.header_ids', '=', 'transaction_headers.id')
                            ->where('transaction_invoices.status_id', 9)
                            ->where('transaction_details.start_time', '>=', $startDate->todateTimeString())
                            ->where('transaction_details.start_time', '<=', $endDate->todateTimeString())
                            ->sum('transaction_details.subtotal');
                        if ($totalAmount != 0) {
                            $result[] = [
                                'labels' => $labelList->name,
                                'amount' => $totalAmount
                            ];
                        }
                    }
                    break;
                case "SpaQty" :
                    $labelLists = Package::select('id', 'name')->whereIn('room_type_id', [1,2,3,5,25])->where('status_id', 1)->get();
                    foreach($labelLists as $labelList){
                        $totalAmount = 0;
                        $totalAmount += TransactionDetail::whereHas('package', function ($query) use ($labelList) {
                            $query->where('id', $labelList->id);
                        })
                            ->join('transaction_headers', 'transaction_headers.id', '=', 'transaction_details.header_id')
                            ->join('transaction_invoices', 'transaction_invoices.header_ids', '=', 'transaction_headers.id')
                            ->where('transaction_invoices.status_id', 9)
                            ->where('transaction_details.start_time', '>=', $startDate->todateTimeString())
                            ->where('transaction_details.start_time', '<=', $endDate->todateTimeString())
                            ->sum('transaction_details.qty');
                        if ($totalAmount != 0) {
                            $result[] = [
                                'labels' => $labelList->name,
                                'amount' => $totalAmount
                            ];
                        }
                    }
                    $labelListItems = Item::select('id', 'name')->whereIn('category_id', [27])->where('status_id', 1)->where('id', '!=', 68)->get();
                    foreach($labelListItems as $labelListItem){
                        $totalAmount = 0;
                        $totalAmount += TransactionDetail::whereHas('item', function ($query) use ($labelListItem) {
                            $query->where('id', $labelListItem->id);
                        })
                            ->join('transaction_headers', 'transaction_headers.id', '=', 'transaction_details.header_id')
                            ->join('transaction_invoices', 'transaction_invoices.header_ids', '=', 'transaction_headers.id')
                            ->where('transaction_invoices.status_id', 9)
                            ->where('transaction_details.start_time', '>=', $startDate->todateTimeString())
                            ->where('transaction_details.start_time', '<=', $endDate->todateTimeString())
                            ->sum('transaction_details.qty');
                        if ($totalAmount != 0) {
                            $result[] = [
                                'labels' => $labelListItem->name,
                                'amount' => $totalAmount
                            ];
                        }
                    }
                    break;
                case "SpaNominal" :
                    $labelLists = Package::select('id', 'name')->whereIn('room_type_id', [1,2,3,5,25])->where('status_id', 1)->get();
                    foreach($labelLists as $labelList){
                        $totalAmount = 0;
                        $totalAmountSubtotal = TransactionDetail::whereHas('package', function ($query) use ($labelList) {
                            $query->where('id', $labelList->id);
                        })
                            ->join('transaction_headers', 'transaction_headers.id', '=', 'transaction_details.header_id')
                            ->join('transaction_invoices', 'transaction_invoices.header_ids', '=', 'transaction_headers.id')
                            ->where('transaction_invoices.status_id', 9)
                            ->where('transaction_details.start_time', '>=', $startDate->todateTimeString())
                            ->where('transaction_details.start_time', '<=', $endDate->todateTimeString())
                            ->sum('transaction_details.subtotal');
                        $totalAmountDiscount = (TransactionDetail::whereHas('package', function ($query) use ($labelList) {
                            $query->where('id', $labelList->id);
                        })
                            ->join('transaction_headers', 'transaction_headers.id', '=', 'transaction_details.header_id')
                            ->join('transaction_invoices', 'transaction_invoices.header_ids', '=', 'transaction_headers.id')
                            ->where('transaction_invoices.status_id', 9)
                            ->where('transaction_details.start_time', '>=', $startDate->todateTimeString())
                            ->where('transaction_details.start_time', '<=', $endDate->todateTimeString())
                            ->sum('transaction_details.discount_amount'));
                        $totalAmount += ($totalAmountSubtotal - $totalAmountDiscount);
                        if ($totalAmount != 0) {
                            $result[] = [
                                'labels' => $labelList->name,
                                'amount' => $totalAmount
                            ];
                        }
                    }
                    $labelListItems = Item::select('id', 'name')->whereIn('category_id', [27])->where('status_id', 1)->where('id', '!=', 68)->get();
                    foreach($labelListItems as $labelListItem){
                        $totalAmount = 0;
                        $totalAmountSubtotal = TransactionDetail::whereHas('item', function ($query) use ($labelListItem) {
                            $query->where('id', $labelListItem->id);
                        })
                            ->where('status_id', 9)
                            ->where('transaction_details.start_time', '>=', $startDate->todateTimeString())
                            ->where('transaction_details.start_time', '<=', $endDate->todateTimeString())
                            ->sum('transaction_details.subtotal');
                        $totalAmountDiscount = (TransactionDetail::whereHas('item', function ($query) use ($labelListItem) {
                            $query->where('id', $labelListItem->id);
                        })
                            ->where('status_id', 9)
                            ->where('transaction_details.start_time', '>=', $startDate->todateTimeString())
                            ->where('transaction_details.start_time', '<=', $endDate->todateTimeString())
                            ->sum('transaction_details.discount_amount'));
                        $totalAmount += ($totalAmountSubtotal - $totalAmountDiscount);
                        if ($totalAmount != 0) {
                            $result[] = [
                                'labels' => $labelListItem->name,
                                'amount' => $totalAmount
                            ];
                        }
                    }
                    break;
            }

            // Sort $result descending berdasarkan 'amount'
            usort($result, function ($a, $b) {
                return $b['amount'] <=> $a['amount']; // Descending order
            });


            return Response::json($result, 200);
        }
        catch (\Exception $ex){
            //dd($ex);
            return Response::json($ex->getMessage(), 500);
        }
    }

    public function getTransactionLinechart(Request $request){
        try{
            $user = Auth::guard('admin')->user();
            $result = [];
            $startDate = $request->filled('start_date')
                ? Carbon::createFromFormat('!d-m-Y', $request->query('start_date'), 'Asia/Jakarta')->startOfDay()
                : Carbon::now('Asia/Jakarta')->startOfMonth()->startOfDay();
            $endDate = $request->filled('end_date')
                ? Carbon::createFromFormat('!d-m-Y', $request->query('end_date'), 'Asia/Jakarta')->endOfDay()
                : Carbon::now('Asia/Jakarta')->endOfMonth()->endOfDay();

            $type = "DailySummary";
            if($request->exists('type')){
                $type = $request->query('type');
            }

            switch($type){
                case "DailySummary":
                    $dailySummary = TransactionDetail::join('transaction_headers', 'transaction_headers.id', '=', 'transaction_details.header_id')
                        ->join('transaction_invoices', 'transaction_invoices.header_ids', '=', 'transaction_headers.id')
                        ->where('transaction_headers.branch_id', $user->branch_id)
                        ->where('transaction_invoices.status_id', 9)
                        ->where('transaction_details.start_time', '>=', $startDate->toDateTimeString())
                        ->where('transaction_details.start_time', '<=', $endDate->toDateTimeString())
                        ->select(
                            DB::raw('DAYOFWEEK(transaction_details.start_time) as day_of_week'),
                            DB::raw('SUM(transaction_details.subtotal) as total_subtotal')
                        )
                        ->groupBy('day_of_week')
                        ->pluck('total_subtotal', 'day_of_week');


                    $daysOfWeek = [
                        'Senin'  => 2,
                        'Selasa' => 3,
                        'Rabu'   => 4,
                        'Kamis'  => 5,
                        'Jumat'  => 6,
                        'Sabtu'  => 7,
                        'Minggu' => 1,
                    ];

                    $result = [];
                    foreach ($daysOfWeek as $dayName => $dayNumber) {
                        $result[] = [
                            'labels' => $dayName,
                            'amount' => $dailySummary[$dayNumber] ?? 0,
                        ];
                    }
                    break;
                case "HourlySummary":
                    $result = [];

                    for ($hour = 10; $hour <= 23; $hour++) {
                        $totalSubtotal = TransactionDetail::join('transaction_headers', 'transaction_headers.id', '=', 'transaction_details.header_id')
                            ->join('transaction_invoices', 'transaction_invoices.header_ids', '=', 'transaction_headers.id')
                            ->where('transaction_headers.branch_id', $user->branch_id)
                            ->where('transaction_invoices.status_id', 9)
                            ->where('transaction_details.start_time', '>=', $startDate->toDateTimeString())
                            ->where('transaction_details.start_time', '<=', $endDate->toDateTimeString())
                            ->whereRaw('HOUR(transaction_details.start_time) = ?', [$hour])
                            ->sum('transaction_details.subtotal');

                        $result[] = [
                            'labels' => sprintf('%02d:00 - %02d:00', $hour, $hour + 1),
                            'amount' => $totalSubtotal,
                        ];
                    }
                    break;

            }

            return Response::json($result, 200);
        }
        catch (\Exception $ex){
            //dd($ex);
            return Response::json($ex->getMessage(), 500);
        }
    }

    public function getCategorySummary(Request $request)
    {
        try {
            $startDate = Carbon::createFromFormat('!d-m-Y', $request->query('start_date'), 'Asia/Jakarta')->startOfDay();
            $endDate = Carbon::createFromFormat('!d-m-Y', $request->query('end_date'), 'Asia/Jakarta')->endOfDay();

            $summaryType = $request->query('type', 'volume');
            $columnToSum = ($summaryType === 'sales') ? 'subtotal' : 'qty';

            $categories = [
                'Spa, Massage, Reflexology' => ['package_room_types' => [1, 2, 3, 5, 25], 'item_categories' => [27]],
                'Salon' => ['package_room_types' => [4]],
                'Studio' => ['package_room_types' => [6]],
                'Sport' => ['package_room_types' => [26]],
                'Kafe' => ['item_categories' => [2, 28]],
            ];

            $result = [];

            // Calculating Total
            if($summaryType == 'volume'){
                foreach ($categories as $label => $ids) {
                    $totalAmount = 0;

                    // Calculate total for packages
                    if (isset($ids['package_room_types'])) {
                        if($label == 'Spa, Massage, Reflexology'){
                            $totalAmount += TransactionDetail::whereHas('package', function ($query) use ($ids) {
                                $query->whereIn('room_type_id', $ids['package_room_types']);
                            })
                            ->where('status_id', 9)
                            ->where('start_time', '>=', $startDate->todateTimeString())
                            ->where('start_time', '<=', $endDate->todateTimeString())
                            ->sum($columnToSum);

                            $totalAmount += TransactionDetail::whereHas('item', function ($query) use ($ids) {
                                $query->whereIn('category_id', $ids['item_categories']);
                            })
                            ->where('status_id', 9)
                            ->where('start_time', '>=', $startDate->todateTimeString())
                            ->where('start_time', '<=', $endDate->todateTimeString())
                            ->sum($columnToSum);
                        }
                        else{
                            $totalAmount += TransactionDetail::whereHas('package', function ($query) use ($ids) {
                                $query->whereIn('room_type_id', $ids['package_room_types']);
                            })
                            ->where('status_id', 9)
                            ->where('start_time', '>=', $startDate->todateTimeString())
                            ->where('start_time', '<=', $endDate->todateTimeString())
                            ->sum($columnToSum);
                        }
                        // $totalAmount += TransactionDetail::whereHas('package', function ($query) use ($ids) {
                        //     $query->whereIn('room_type_id', $ids['package_room_types']);
                        // })
                        // ->where('status_id', 9)
                        // ->where('start_time', '>=', $startDate->todateTimeString())
                        // ->where('start_time', '<=', $endDate->todateTimeString())
                        // ->sum($columnToSum);
                    }

                    // Calculate total for items
                    if (isset($ids['item_categories'])) {
                        $totalAmount += TransactionDetail::whereHas('item', function ($query) use ($ids) {
                            $query->whereIn('category_id', $ids['item_categories']);
                        })
                        ->where('status_id', 9)
                        ->where('start_time', '>=', $startDate->todateTimeString())
                        ->where('start_time', '<=', $endDate->todateTimeString())
                        ->sum($columnToSum);
                    }

                    $result[] = [
                        'labels' => $label,
                        'amount' => $totalAmount,
                    ];
                }
            }
            else{
                foreach ($categories as $label => $ids) {
                    $totalAmount = 0;

                    // Calculate total for packages
                    if (isset($ids['package_room_types'])) {
                        if($label == 'Spa, Massage, Reflexology'){
                            $transactionAmount = TransactionDetail::join('packages', 'packages.id', '=', 'transaction_details.package_id')
                                ->join('transaction_headers', 'transaction_headers.id', '=', 'transaction_details.header_id')
                                ->join('transaction_invoices', 'transaction_invoices.header_ids', '=', 'transaction_headers.id')
                                ->whereIn('packages.room_type_id', $ids['package_room_types'])
                                ->where('transaction_invoices.status_id', 9)
                                ->whereBetween('transaction_details.start_time', [$startDate->todateTimeString(), $endDate->todateTimeString()])
                                ->sum('transaction_details.subtotal');
                            $discountAmount = TransactionDetail::join('packages', 'packages.id', '=', 'transaction_details.package_id')
                                ->join('transaction_headers', 'transaction_headers.id', '=', 'transaction_details.header_id')
                                ->join('transaction_invoices', 'transaction_invoices.header_ids', '=', 'transaction_headers.id')
                                ->whereIn('packages.room_type_id', $ids['package_room_types'])
                                ->where('transaction_invoices.status_id', 9)
                                ->whereBetween('transaction_details.start_time', [$startDate->todateTimeString(), $endDate->todateTimeString()])
                                ->sum('transaction_details.discount_amount');
                            $totalAmount += ($transactionAmount - $discountAmount);
                        }
                        else if($label == 'Studio'){
                            $transactionAmount = TransactionDetail::join('packages', 'packages.id', '=', 'transaction_details.package_id')
                                ->join('transaction_headers', 'transaction_headers.id', '=', 'transaction_details.header_id')
                                ->join('transaction_invoices', 'transaction_invoices.header_ids', '=', 'transaction_headers.id')
                                ->whereIn('packages.room_type_id', $ids['package_room_types'])
                                ->where('transaction_invoices.status_id', 9)
                                ->whereBetween('transaction_details.start_time', [$startDate->todateTimeString(), $endDate->todateTimeString()])
                                ->sum('transaction_details.subtotal');
                            $discountAmount = TransactionDetail::join('packages', 'packages.id', '=', 'transaction_details.package_id')
                                ->join('transaction_headers', 'transaction_headers.id', '=', 'transaction_details.header_id')
                                ->join('transaction_invoices', 'transaction_invoices.header_ids', '=', 'transaction_headers.id')
                                ->whereIn('packages.room_type_id', $ids['package_room_types'])
                                ->where('transaction_invoices.status_id', 9)
                                ->whereBetween('transaction_details.start_time', [$startDate->todateTimeString(), $endDate->todateTimeString()])
                                ->sum('transaction_details.discount_amount');
                            $roundingAmount = TransactionDetail::join('packages', 'packages.id', '=', 'transaction_details.package_id')
                                ->join('transaction_headers', 'transaction_headers.id', '=', 'transaction_details.header_id')
                                ->join('transaction_invoices', 'transaction_invoices.header_ids', '=', 'transaction_headers.id')
                                ->whereIn('packages.room_type_id', $ids['package_room_types'])
                                ->where('transaction_invoices.status_id', 9)
                                ->whereBetween('transaction_details.start_time', [$startDate->todateTimeString(), $endDate->todateTimeString()])
                                ->sum('transaction_invoices.rounding_amount');
                            $totalAmount += ($transactionAmount - $discountAmount) + $roundingAmount;

                        }
                        else{
                            $transactionAmount = TransactionDetail::join('packages', 'packages.id', '=', 'transaction_details.package_id')
                                ->join('transaction_headers', 'transaction_headers.id', '=', 'transaction_details.header_id')
                                ->join('transaction_invoices', 'transaction_invoices.header_ids', '=', 'transaction_headers.id')
                                ->whereIn('packages.room_type_id', $ids['package_room_types'])
                                ->where('transaction_invoices.status_id', 9)
                                ->whereBetween('transaction_details.start_time', [$startDate->todateTimeString(), $endDate->todateTimeString()])
                                ->sum('transaction_details.subtotal');
                            $discountAmount = TransactionDetail::join('packages', 'packages.id', '=', 'transaction_details.package_id')
                                ->join('transaction_headers', 'transaction_headers.id', '=', 'transaction_details.header_id')
                                ->join('transaction_invoices', 'transaction_invoices.header_ids', '=', 'transaction_headers.id')
                                ->whereIn('packages.room_type_id', $ids['package_room_types'])
                                ->where('transaction_invoices.status_id', 9)
                                ->whereBetween('transaction_details.start_time', [$startDate->todateTimeString(), $endDate->todateTimeString()])
                                ->sum('transaction_details.discount_amount');
                            $totalAmount += ($transactionAmount - $discountAmount);
                        }
                        // $totalAmount += TransactionDetail::whereHas('package', function ($query) use ($ids) {
                        //     $query->whereIn('room_type_id', $ids['package_room_types']);
                        // })
                        // ->where('status_id', 9)
                        // ->where('created_at', '>=', $startDate->todateTimeString())
                        // ->where('created_at', '<=', $endDate->todateTimeString())
                        // ->sum($columnToSum);
                    }

                    // Calculate total for items
                    if (isset($ids['item_categories'])) {
                        $transactionAmount = TransactionDetail::join('items', 'items.id', '=', 'transaction_details.item_id')
                            ->join('transaction_headers', 'transaction_headers.id', '=', 'transaction_details.header_id')
                            ->join('transaction_invoices', 'transaction_invoices.header_ids', '=', 'transaction_headers.id')
                            ->whereIn('items.category_id', $ids['item_categories'])
                            ->where('transaction_invoices.status_id', 9)
                            ->whereBetween('transaction_details.start_time', [$startDate->todateTimeString(), $endDate->todateTimeString()])
                            ->sum('transaction_details.subtotal');
                        $discountAmount = TransactionDetail::join('items', 'items.id', '=', 'transaction_details.item_id')
                            ->join('transaction_headers', 'transaction_headers.id', '=', 'transaction_details.header_id')
                            ->join('transaction_invoices', 'transaction_invoices.header_ids', '=', 'transaction_headers.id')
                            ->whereIn('items.category_id', $ids['item_categories'])
                            ->where('transaction_invoices.status_id', 9)
                            ->whereBetween('transaction_details.start_time', [$startDate->todateTimeString(), $endDate->todateTimeString()])
                            ->sum('transaction_details.discount_amount');
                        $totalAmount += ($transactionAmount - $discountAmount);

                        // $totalAmount += TransactionDetail::whereHas('item', function ($query) use ($ids) {
                        //     $query->whereIn('category_id', $ids['item_categories']);
                        // })
                        // ->where('status_id', 9)
                        // ->where('created_at', '>=', $startDate->todateTimeString())
                        // ->where('created_at', '<=', $endDate->todateTimeString())
                        // ->sum($columnToSum);
                    }

                    $result[] = [
                        'labels' => $label,
                        'amount' => $totalAmount,
                    ];
                }
            }

            return Response::json($result, 200);
        } catch (\Exception $ex) {
            return Response::json(['error' => $ex->getMessage()], 500);
        }
    }

    public function getTopItems(Request $request)
    {
        try {
            $startDate = Carbon::createFromFormat('!d-m-Y', $request->query('start_date'), 'Asia/Jakarta')->startOfDay();
            $endDate = Carbon::createFromFormat('!d-m-Y', $request->query('end_date'), 'Asia/Jakarta')->endOfDay();
            $topItems = DB::table('transaction_details as td')
                ->join('transaction_headers as th', 'th.id', '=', 'td.header_id')
                ->join('transaction_invoices as ti', 'ti.header_ids', '=', 'th.id')
                ->join('items as i', 'i.id', '=', 'td.item_id')
                ->whereNotNull('td.item_id')
                ->where('ti.status_id', 9)
                ->where('td.item_id', '!=', 68)
                ->whereBetween('td.start_time', [$startDate, $endDate]) // ⬅️ FIX PENTING
                ->select(
                    'td.item_id',
                    'i.name',
                    DB::raw('SUM(td.qty) as items_sold'),
                    DB::raw('SUM(td.subtotal) as gross_sales')
                )
                ->groupBy('td.item_id', 'i.name')
                ->orderByDesc('items_sold')
                ->limit(10)
                ->get();

            return Response::json($topItems, 200);

        } catch (\Exception $ex) {
            return Response::json(['error' => $ex->getMessage()], 500);
        }
    }

    public function examplePage(){
        return view('admin.create-form (template)');
    }
}
