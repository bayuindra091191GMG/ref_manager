<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ItemCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use OpenSpout\Common\Entity\Style\Style;
use Rap2hpoutre\FastExcel\FastExcel;

class ExportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function exportEmployeeCommissionFilter(){
        $startDate = Carbon::today('Asia/Jakarta');

        $data = [
            'startDate' => $startDate->format('d-m-Y'),
            'endDate' => $startDate->copy()->format('d-m-Y')
        ];

        return view('admin.export.employee_commission_export_filter')->with($data);
    }

    public function exportEmployeeCommissionSubmit(Request $request){
        try {
            $rules = array(
                'start_date'=> 'required',
                'end_date'=> 'required',
            );

            $message = array(
                'start_date.required' => 'Tanggal Mulai Wajib dipilih!',
                'end_date.required' => 'Tanggal Akhir Wajib dipilih!',
            );

            $validator = Validator::make($request->all(), $rules, $message);

            if($validator->fails()){
                return Response::json(['error' => $validator->errors()->all()]);
            }

            $admin = Auth::guard('admin')->user();
            $branchId = $admin->branch_id;

            // Get range date
            $startDate = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
            $endDate = Carbon::createFromFormat('!d-m-Y', $request->input('end_date'), 'Asia/Jakarta');
            $endDate->addDays(1);

            Log::channel('report_watcher')->info('ExportController - exportEmployeeCommissionSubmit'.
                ' branch_id: '. $branchId.
                ' start_date: '. $startDate->toDateTimeString().
                ' end_date: '. $endDate->toDateTimeString().
                ' admin_id: '. $admin->id);

            // Get auto commission data
            $employees = DB::table('employees')
                ->where('branch_id', '=', $branchId)
                ->where('is_external', '=', 0)
                ->where('status_id', '=', 1)
                ->where('name', '!=', 'Shadow')
                ->get();

            $employeeCommissionList = collect();
            $idx = 1;
            foreach ($employees as $employee){
                $totalPositionItemCommission = 0;
                if(DB::table('commission_position_items')
                    ->where('branch_id', '=', $branchId)
                    ->where('position_id', '=', $employee->position_id)
                    ->exists()){
                    $totalPositionItemCommission = DB::table('commission_employee_histories')
                        ->where('branch_id', '=', $branchId)
                        ->whereBetween('transaction_date', array($startDate->toDateTimeString(), $endDate->toDateTimeString()))
                        ->where('employee_id', '=', $employee->id)
                        ->sum('commission_add');

                }

                $totalManualCommission = 0;
                if(DB::connection('hrd')->table('commission_item_manual_headers')
                    ->where('branch_id', '=', $branchId)
                    ->whereBetween('date', array($startDate->toDateTimeString(), $endDate->toDateTimeString()))
                    ->where('employee_id', '=', $employee->id)
                    ->exists()){
                    $totalManualCommission = DB::connection('hrd')->table('commission_item_manual_details')
                        ->join('commission_item_manual_headers', 'commission_item_manual_details.header_id', '=', 'commission_item_manual_headers.id')
                        ->where('commission_item_manual_headers.branch_id', '=', $branchId)
                        ->whereBetween('commission_item_manual_headers.date', array($startDate->toDateTimeString(), $endDate->toDateTimeString()))
                        ->where('commission_item_manual_headers.employee_id', '=', $employee->id)
                        ->sum('commission_item_manual_details.commission_amount');

                }

                $employeeCommissionList->push(collect([
                    'NO' => $idx,
                    'NAMA KARYAWAN' => $employee->name,
                    'KODE KARYAWAN' => $employee->code,
                    'TOTAL KOMISI OTOMATIS' => floatval($totalPositionItemCommission),
                    'TOTAL KOMISI MANUAL' => floatval($totalManualCommission)
                ]));

                $idx++;
            }


            $today = Carbon::today('Asia/Jakarta');
            $fileName = 'LAPORAN_KOMISI_KARYAWAN_'. $today->year. '_'. $today->month. '_'. $today->day. '.xlsx';

            $headerStyle = (new Style())->setCellAlignment('center');

//            return (new FastExcel($employeeCommissionList))
//                ->headerStyle($headerStyle)
//                ->download($fileName);

            $export = (new FastExcel($employeeCommissionList))
                ->headerStyle($headerStyle)
                ->download($fileName);

            $export->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', true);
            $export->headers->set('Content-Disposition', 'attachment; ' .
                'filename="' . rawurldecode($fileName) . '"; ' .
                'filename*=UTF-8\'\'' . rawurldecode($fileName), true);
            $export->headers->set('Cache-Control', 'max-age=0', true);
            $export->headers->set('Pragma', 'public', true);

            return $export;
        }
        catch (\Exception $ex){
            Log::channel('hrd')->error('Admin/ExportController exportEmployeeCommissionSubmit - error EX: '. $ex);
            Session::flash('Terjadi Kesalahan Teknis!', $ex);
            return back();
        }
    }

    public function exportPartnerCommissionFilter(){
        $startDate = Carbon::today('Asia/Jakarta');

        $data = [
            'startDate' => $startDate->format('d-m-Y'),
            'endDate' => $startDate->copy()->format('d-m-Y')
        ];

        return view('admin.export.partner_commission_export_filter')->with($data);
    }

    public function exportPartnerCommissionSubmit(Request $request){
        try {
            $rules = array(
                'start_date'=> 'required',
                'end_date'=> 'required',
            );

            $message = array(
                'start_date.required' => 'Tanggal Mulai Wajib dipilih!',
                'end_date.required' => 'Tanggal Akhir Wajib dipilih!',
            );

            $validator = Validator::make($request->all(), $rules, $message);

            if($validator->fails()){
                return Response::json(['error' => $validator->errors()->all()]);
            }

            $admin = Auth::guard('admin')->user();
            $branchId = $admin->branch_id;

            // Get range date
            $startDate = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
            $endDate = Carbon::createFromFormat('!d-m-Y', $request->input('end_date'), 'Asia/Jakarta');
            $endDate->addDays(1);

            // Get auto commission data
            $partners = DB::table('partners')
                ->where('branch_id', '=', $branchId)
                ->whereIn('status_id', [5,6,11,12])
                ->get();

            Log::channel('report_watcher')->info('ExportController - exportPartnerCommissionSubmit'.
                ' branch_id: '. $branchId.
                ' start_date: '. $startDate->toDateTimeString().
                ' end_date: '. $endDate->toDateTimeString().
                ' admin_id: '. $admin->id);

            $partnerCommissionList = collect();
            $idx = 1;
            foreach ($partners as $partner){
                $totalPackageCommission = DB::table('commission_partner_histories')
                    ->where('branch_id', '=', $branchId)
                    ->whereBetween('transaction_date', array($startDate->toDateTimeString(), $endDate->toDateTimeString()))
                    ->where('partner_id', '=', $partner->id)
//                    ->whereNotNull('package_id')
                    ->sum('commission_add');

                $totalManualCommission = 0;
                if(DB::connection('hrd')->table('commission_item_manual_headers')
                    ->where('branch_id', '=', $branchId)
                    ->whereBetween('date', array($startDate->toDateTimeString(), $endDate->toDateTimeString()))
                    ->where('partner_id', '=', $partner->id)
                    ->exists()){
                    $totalManualCommission = DB::connection('hrd')->table('commission_item_manual_details')
                        ->join('commission_item_manual_headers', 'commission_item_manual_details.header_id', '=', 'commission_item_manual_headers.id')
                        ->where('commission_item_manual_headers.branch_id', '=', $branchId)
                        ->whereBetween('commission_item_manual_headers.date', array($startDate->toDateTimeString(), $endDate->toDateTimeString()))
                        ->where('commission_item_manual_headers.partner_id', '=', $partner->id)
                        ->sum('commission_item_manual_details.commission_amount');

                }

                $partnerCommissionList->push(collect([
                    'NO' => $idx,
                    'NAMA TERAPIS' => $partner->name,
                    'ALIAS TERAPIS' => $partner->alias,
                    'KODE TERAPIS' => $partner->code,
                    'TOTAL KOMISI OTOMATIS' => floatval($totalPackageCommission),
                    'TOTAL KOMISI MANUAL' => floatval($totalManualCommission)
                ]));

                $idx++;
            }


            $today = Carbon::today('Asia/Jakarta');
            $fileName = 'LAPORAN_KOMISI_TERAPIS_'. $today->year. '_'. $today->month. '_'. $today->day. '.xlsx';

//            return \response()->streamDownload(function () use($partnerCommissionList, $fileName) {
//                $headerStyle = (new Style())->setCellAlignment('center');
//
//                return (new FastExcel($partnerCommissionList))
//                    ->headerStyle($headerStyle)
//                    ->export('php://output');
//            }, $fileName);

            $headerStyle = (new Style())->setCellAlignment('center');

            $export = (new FastExcel($partnerCommissionList))
                ->headerStyle($headerStyle)
                ->download($fileName);

            $export->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', true);
            $export->headers->set('Content-Disposition', 'attachment; ' .
                'filename="' . rawurldecode($fileName) . '"; ' .
                'filename*=UTF-8\'\'' . rawurldecode($fileName), true);
            $export->headers->set('Cache-Control', 'max-age=0', true);
            $export->headers->set('Pragma', 'public', true);

            return $export;
        }
        catch (\Exception $ex){
            Log::channel('hrd')->error('Admin/ExportController exportPartnerCommissionSubmit - error EX: '. $ex);
            Session::flash('Terjadi Kesalahan Teknis!', $ex);
            return back();
        }
    }

    public function exportItemSalesFilter(){
        $startDate = Carbon::today('Asia/Jakarta');

        $data = [
            'startDate' => $startDate->format('d-m-Y'),
            'endDate' => $startDate->copy()->format('d-m-Y')
        ];

        return view('admin.export.item_sales_export_filter')->with($data);
    }

    public function exportItemSalesSubmit(Request $request){
        try {
            $rules = array(
                'start_date'=> 'required',
                'end_date'=> 'required',
            );

            $message = array(
                'start_date.required' => 'Tanggal Mulai Wajib dipilih!',
                'end_date.required' => 'Tanggal Akhir Wajib dipilih!',
            );

            $validator = Validator::make($request->all(), $rules, $message);

            if($validator->fails()){
                return Response::json(['error' => $validator->errors()->all()]);
            }

            $admin = Auth::guard('admin')->user();
            $branchId = $admin->branch_id;

            // Get range date
            $startDate = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
            $endDate = Carbon::createFromFormat('!d-m-Y', $request->input('end_date'), 'Asia/Jakarta');
            $endDate->addDays(1);

            Log::channel('report_watcher')->info('ExportController - exportItemSalesSubmit'.
                ' branch_id: '. $branchId.
                ' start_date: '. $startDate->toDateTimeString().
                ' end_date: '. $endDate->toDateTimeString().
                ' admin_id: '. $admin->id);

            $items = DB::select("select i.id, i.code, i.name, sum(dt.qty) as total_qty, sum(dt.qty * dt.price) as total_sales
                        from items i
                        inner join transaction_details dt on dt.item_id = i.id
                        where i.branch_id = ". $branchId ."
                        and dt.created_at >= '". $startDate->toDateTimeString() ."'
                        and dt.created_at <= '". $endDate->toDateTimeString() ."'
                        group by i.id, i.code, i.name;");

            $itemSalesList = collect();
            $idx = 1;
            foreach ($items as $item){
                $itemSalesList->push(collect([
                    'NO' => $idx,
                    'NAMA BARANG' => $item->name,
                    'KODE BARANG' => $item->code,
                    'TOTAL QTY' => $item->total_qty,
                    'TOTAL SALES' => floatval($item->total_sales)
                ]));

                $idx++;
            }

            $today = Carbon::today('Asia/Jakarta');
            $fileName = 'LAPORAN_PENJUALAN_BARANG_'. $today->year. '_'. $today->month. '_'. $today->day. '.xlsx';

            $headerStyle = (new Style())->setCellAlignment('center');

            $export = (new FastExcel($itemSalesList))
                ->headerStyle($headerStyle)
                ->download($fileName);

            $export->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', true);
            $export->headers->set('Content-Disposition', 'attachment; ' .
                'filename="' . rawurldecode($fileName) . '"; ' .
                'filename*=UTF-8\'\'' . rawurldecode($fileName), true);
            $export->headers->set('Cache-Control', 'max-age=0', true);
            $export->headers->set('Pragma', 'public', true);

            return $export;
        }
        catch (\Exception $ex){
            dd($ex);
            Log::channel('hrd')->error('Admin/ExportController exportItemSalesSubmit - error EX: '. $ex);
            Session::flash('Terjadi Kesalahan Teknis!', $ex);
            return back();
        }
    }

    public function exportAttendanceSummaryFilter(){
        $startDate = Carbon::today()->subMonths(1)->format('d-m-Y');
        $endDate = Carbon::today()->format('d-m-Y');

        $data = [
            'startDate' => $startDate,
            'endDate' => $endDate
        ];

        return view('admin.export.attendance_summary_export_filter')->with($data);
    }

    // public function exportAttendanceSummarySubmit(Request $request){
    //     try{
    //         if(!$request->filled('period_month')){
    //             Session::flash('Mohon pilih periode absen!', 'BAD REQUEST!');
    //             return redirect()->back();
    //         }

    //         $admin = Auth::guard('admin')->user();

    //         $periodMonthArr = explode('-', $request->input('period_month'));
    //         $periodCarbon = Carbon::createFromDate($periodMonthArr[1], $periodMonthArr[0], 1)->startOfDay();

    //         $summaries = DB::connection('hrd')
    //             ->table('attendance_summaries')
    //             ->where('branch_id', '=', $admin->branch_id)
    //             ->where('type', '=', $request->input('type'))
    //             ->where('date_period', '=', $periodCarbon->toDateTimeString())
    //             ->get();

    //         $summaryList = collect();
    //         $idx = 1;
    //         foreach($summaries as $summary){
    //             $summaryList->push(collect([
    //                 'NO' => $idx,
    //                 'PERIODE' => $request->input('period_month'),
    //                 'KODE' => $summary->employee_code,
    //                 'NAME' => $summary->employee_name,
    //                 'TOTAL ABSEN' => $summary->total_attend_count,
    //                 'TOTAL TELAT' => $summary->total_late_count,
    //                 'TOTAL MENIT TELAT' => $summary->total_late_minutes,
    //                 'TOTAL ALPHA' => $summary->total_alpha_count,
    //                 'TOTAL OFF' => $summary->total_off_count,
    //                 'TOTAL IJIN' => $summary->total_leave_count,
    //                 'TOTAL LEMBUR' => $summary->total_leave_count,
    //                 'TOTAL JAM LEMBUR' => $summary->total_long_shift_hours,
    //                 'TOTAL PINALTI' => intval($summary->grand_total_penalty_amount)
    //             ]));

    //             $idx++;
    //         }

    //         $today = Carbon::today('Asia/Jakarta');
    //         $fileName = 'LAPORAN_REKAP_ABSEN_'. $today->year. '_'. $today->month. '_'. $today->day. '.xlsx';

    //         $headerStyle = (new Style())->setCellAlignment('center');

    //         $export = (new FastExcel($summaryList))
    //             ->headerStyle($headerStyle)
    //             ->download($fileName);

    //         $export->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', true);
    //         $export->headers->set('Content-Disposition', 'attachment; ' .
    //             'filename="' . rawurldecode($fileName) . '"; ' .
    //             'filename*=UTF-8\'\'' . rawurldecode($fileName), true);
    //         $export->headers->set('Cache-Control', 'max-age=0', true);
    //         $export->headers->set('Pragma', 'public', true);

    //         return $export;
    //     }
    //     catch (\Exception $ex){
    //         dd($ex);
    //         Log::channel('hrd')->error('Admin/ExportController exportAttendanceSummarySubmit - error EX: '. $ex);
    //         Session::flash('Terjadi Kesalahan Teknis!', $ex);
    //         return back();
    //     }
    // }

    public function exportAttendanceSummarySubmit(Request $request){
        try{
            // if(!$request->filled('period_month')){
            //     Session::flash('Mohon pilih periode absen!', 'BAD REQUEST!');
            //     return redirect()->back();
            // }

            $admin = Auth::guard('admin')->user();
            $type = $request->input('type');

            // Filter date
            $start = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
            $end = Carbon::createFromFormat('!d-m-Y', $request->input('end_date'), 'Asia/Jakarta');
            $end->addDays(1);

            if($type === 'employee'){
                $attendanceSummaryQueries =
                    DB::connection('hrd')
                        ->table('attendance_checks')
                        ->select(DB::raw('SUM(is_late = 1) as total_late, '.
                            'SUM(is_alpha = 1) as total_alpha, '.
                            'SUM(is_long_shift = 1) as total_long_shift, '.
                            'SUM(is_attend = 1) as total_attend, '.
                            'SUM(is_off = 1) as total_off, '.
                            'SUM(is_leave = 1) as total_leave, '.
                            'SUM(alpha_penalty_amount) as total_alpha_penalty_amount, '.
                            'SUM(long_shift_hour) as total_long_shift_hour, '.
                            'SUM(late_minutes) as total_late_minutes, '.
                            'employee_id, employee_code, employee_name'))
                        ->where('branch_id', '=', $admin->branch_id)
                        ->whereNotNull('employee_id')
                        ->whereBetween('current_date', array($start->toDateTimeString(), $end->toDateTimeString()))
                        ->groupBy(['employee_id', 'employee_code', 'employee_name'])
                        ->get();

                $summaryList = collect();
                $idx = 1;
                foreach($attendanceSummaryQueries as $summary){
                    $summaryList->push(collect([
                        'NO' => $idx,
                        'PERIODE' => $request->input('period_month'),
                        'KODE' => $summary->employee_code,
                        'NAMA' => $summary->employee_name,
                        'TOTAL ABSEN' => $summary->total_attend,
                        'TOTAL TELAT' => $summary->total_late,
                        'TOTAL MENIT TELAT' => $summary->total_late_minutes,
                        'TOTAL ALPHA' => $summary->total_alpha,
                        'TOTAL PINALTI ALPHA' => intval($summary->total_alpha_penalty_amount) * 1000,
                        'TOTAL OFF' => $summary->total_off,
                        'TOTAL IJIN' => $summary->total_leave,
                        'TOTAL LEMBUR' => $summary->total_long_shift,
                        'TOTAL JAM LEMBUR' => $summary->total_long_shift_hour
                    ]));

                    $idx++;
                }
            }
            else{
                $attendanceSummaryQueries =
                    DB::connection('hrd')
                        ->table('attendance_checks')
                        ->select(DB::raw('SUM(is_late = 1) as total_late, '.
                            'SUM(is_alpha = 1) as total_alpha, '.
                            'SUM(is_long_shift = 1) as total_long_shift, '.
                            'SUM(is_attend = 1) as total_attend, '.
                            'SUM(is_off = 1) as total_off, '.
                            'SUM(is_leave = 1) as total_leave, '.
                            'SUM(alpha_penalty_amount) as total_alpha_penalty_amount, '.
                            'SUM(long_shift_hour) as total_long_shift_hour, '.
                            'SUM(late_minutes) as total_late_minutes, '.
                            'partner_id, partner_code, partner_number, partner_name'))
                            ->where('branch_id', '=', $admin->branch_id)
                        ->whereNotNull('partner_id')
                        ->whereBetween('current_date', array($start->toDateTimeString(), $end->toDateTimeString()))
                        ->groupBy(['partner_id', 'partner_number', 'partner_name'])
                        ->get();

                $summaryList = collect();
                $idx = 1;
                foreach($attendanceSummaryQueries as $summary){
                    $summaryList->push(collect([
                        'NO' => $idx,
                        'PERIODE' => $request->input('period_month'),
                        'KODE' => $summary->partner_code,
                        'NAMA' => $summary->partner_name,
                        'ALIAS' => $summary->partner_alias,
                        'NOMOR' => $summary->partner_number,
                        'TOTAL ABSEN' => $summary->total_attend,
                        'TOTAL TELAT' => $summary->total_late,
                        'TOTAL MENIT TELAT' => $summary->total_late_minutes,
                        'TOTAL ALPHA' => $summary->total_alpha,
                        'TOTAL PINALTI ALPHA' => intval($summary->total_alpha_penalty_amount) * 1000,
                        'TOTAL OFF' => $summary->total_off,
                        'TOTAL IJIN' => $summary->total_leave,
                        'TOTAL LEMBUR' => $summary->total_long_shift,
                        'TOTAL JAM LEMBUR' => $summary->total_long_shift_hour,
                    ]));

                    $idx++;
                }
            }

            $today = Carbon::today('Asia/Jakarta');
            $fileName = 'LAPORAN_REKAP_ABSEN_'. $today->year. '_'. $today->month. '_'. $today->day. '.xlsx';

            $headerStyle = (new Style())->setCellAlignment('center');

            $export = (new FastExcel($summaryList))
                ->headerStyle($headerStyle)
                ->download($fileName);

            $export->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', true);
            $export->headers->set('Content-Disposition', 'attachment; ' .
                'filename="' . rawurldecode($fileName) . '"; ' .
                'filename*=UTF-8\'\'' . rawurldecode($fileName), true);
            $export->headers->set('Cache-Control', 'max-age=0', true);
            $export->headers->set('Pragma', 'public', true);

            return $export;
        }
        catch (\Exception $ex){
            dd($ex);
            Log::channel('hrd')->error('Admin/ExportController exportAttendanceSummarySubmit - error EX: '. $ex);
            Session::flash('Terjadi Kesalahan Teknis!', $ex);
            return back();
        }
    }

    public function exportItemInventoryFilter(){
        $today = Carbon::today('Asia/Jakarta');
        $month = $today->month;
        $year = $today->year;

        $periodMonth = $month. '-'. $year;

        return view('admin.export.item_inventory_export_filter', compact('periodMonth'));
    }

    public function exportItemInventorySubmit(Request $request){
        try{
            $admin = Auth::guard('admin')->user();

            $tmpArr = explode('-', $request->input('period_month'));
            $tmpCarbon = Carbon::createFromDate($tmpArr[1], $tmpArr[0], 1, 'Asia/Jakarta');

            $firstDate = $tmpCarbon->copy();
            $secondDate = $tmpCarbon->lastOfMonth()->addDay();

            $branchId = $admin->branch_id;

            $category = $request->input('category');
            if($category == 'PERSEDIAAN'){
                $categoryIds = ItemCategory::where('name', 'like', '%PERSEDIAAN - %')->pluck('id');
                // $categoryIds = [13,14,15,16,27];
            }
            else{
                $categoryIds = ItemCategory::where('name', 'like', '%PERLENGKAPAN - %')->pluck('id');
                // $categoryIds = [5,6,7,8,9,10,11,12,17,18];
            }

            $items = DB::table('items')
                ->where('branch_id', '=', $branchId)
                ->whereIn('category_2_id', $categoryIds)
                ->orderBy('code')
                ->get();

            $histories = [];
            foreach($items as $item){
                $firstInventoryHistory = DB::table('inventory_histories')
                    ->where('branch_id', '=', $branchId)
                    ->where('item_id', '=', $item->id)
                    ->where('created_at', '<', $firstDate->toDateTimeString())
                    ->orderByDesc('created_at')
                    ->first();

                $firstQty = 0;
                $firstPoPrice = 0;
                if(!empty($firstInventoryHistory)){
                    $firstQty = $firstInventoryHistory->qty_result;

                    $firstPoDetail = DB::table('purchase_order_details')
                        ->join('purchase_order_headers', 'purchase_order_details.header_id', '=', 'purchase_order_headers.id')
                        ->select('purchase_order_details.item_id as item_id',
                            'purchase_order_details.price as price')
                        ->where('purchase_order_headers.branch_id', '=', $branchId)
                        ->where('purchase_order_headers.status_id', '=', 13)
                        ->where('purchase_order_details.item_id', '=', $item->id)
                        ->where('purchase_order_headers.created_at', '<', $firstDate->toDateTimeString())
                        ->orderByDesc('purchase_order_headers.created_at')
                        ->first();

                    $firstPoPrice = !empty($firstPoDetail) ? $firstPoDetail->price : 0;
                }

                $secondInventoryHistory = DB::table('inventory_histories')
                    ->where('branch_id', '=', $branchId)
                    ->where('item_id', '=', $item->id)
                    ->where('created_at', '<', $secondDate->toDateTimeString())
                    ->orderByDesc('created_at')
                    ->first();

                $secondQty = 0;
                $secondPoPrice = 0;
                if(!empty($secondInventoryHistory)){
                    $secondQty = $secondInventoryHistory->qty_result;

                    $secondPoDetail = DB::table('purchase_order_details')
                        ->join('purchase_order_headers', 'purchase_order_details.header_id', '=', 'purchase_order_headers.id')
                        ->select('purchase_order_details.item_id as item_id',
                            'purchase_order_details.price as price')
                        ->where('purchase_order_headers.branch_id', '=', $branchId)
                        ->where('purchase_order_headers.status_id', '=', 13)
                        ->where('purchase_order_details.item_id', '=', $item->id)
                        ->where('purchase_order_headers.created_at', '<', $secondDate->toDateTimeString())
                        ->orderByDesc('purchase_order_headers.created_at')
                        ->first();

                    $secondPoPrice = !empty($secondPoDetail) ? $secondPoDetail->price : 0;
                }

                $categoryName = DB::table('item_categories')
                    ->where('id', '=', $item->category_2_id)
                    ->value('name');

                $histories[] = [
                    'item_code' => $item->code,
                    'item_name' => $item->name,
                    'category_name' => $categoryName,
                    'first_qty' => $firstQty,
                    'first_po_price' => $firstPoPrice,
                    'first_total' => $firstQty > 0 ? $firstQty * $firstPoPrice : 0,
                    'second_qty' => $secondQty,
                    'second_po_price' => $secondPoPrice,
                    'second_total' => $secondQty > 0 ? $secondQty * $secondPoPrice : 0,
                ];
            }

            $filename = $category. '_STOCK_'. $firstDate->year. '_'. $firstDate->month. '.xlsx';

            $export =  (new FastExcel($histories))->download($filename, function ($history) use($firstDate, $secondDate) {
                $tmpFirst = $firstDate->format('d-m-Y');
                $tmpSecond = $secondDate->copy()->subDay()->format('d-m-Y');

                return [
                    'NAMA' => $history['item_name'],
                    'KATEGORI' => $history['category_name'],
                    'QTY '. $tmpFirst => $history['first_qty'],
                    'HARGA '. $tmpFirst => $history['first_po_price'],
                    'JUMLAH '. $tmpFirst => $history['first_total'],
                    'QTY '. $tmpSecond => $history['second_qty'],
                    'HARGA '. $tmpSecond => $history['second_po_price'],
                    'JUMLAH '. $tmpSecond => $history['second_total'],
                ];
            });

            $export->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', true);
            $export->headers->set('Content-Disposition', 'attachment; ' .
                'filename="' . rawurldecode($filename) . '"; ' .
                'filename*=UTF-8\'\'' . rawurldecode($filename), true);
            $export->headers->set('Cache-Control', 'max-age=0', true);
            $export->headers->set('Pragma', 'public', true);

            return $export;
        }
        catch (\Exception $ex){
            Log::channel('inventory')->error('Admin/ExportController exportItemInventorySubmit - error EX: '. $ex);
            Session::flash('Terjadi Kesalahan Teknis!', $ex);
            return back();
        }
    }
}
