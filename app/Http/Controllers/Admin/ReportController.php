<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeePosition;
use App\Models\Item;
use App\Models\Package;
use App\Models\TransactionHeader;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function filterItemReport(){
        $startDate = Carbon::now('Asia/Jakarta');
        $endDate = Carbon::now('Asia/Jakarta');
        $endDate->addDay();

        $data = [
            'startDate' => $startDate->format('d-m-Y'),
            'endDate'   => $endDate->format('d-m-Y')
        ];

        return view('admin.report.report_item')->with($data);
    }

    public function generateItemReport(Request $request){
        try{
            $rules = array(
                'start_date'    => 'required',
                'end_date'      => 'required',
            );

            $message = array(
                'start_date.required'   => 'Tanggal Mulai Wajib dipilih!',
                'end_date.required'     => 'Tanggal Akhir Wajib dipilih!',
            );

            $validator = Validator::make($request->all(), $rules, $message);

            if ($validator->fails()) {
                return Response::json(['error' => $validator->errors()->all()]);
            }

            //get data
            $startDate = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
            $endDate = Carbon::createFromFormat('!d-m-Y', $request->input('end_date'), 'Asia/Jakarta');
            $endDate->addDay();

            $user = Auth::guard('admin')->user();

            $items = Item::where('branch_id', $user->branch_id)
                ->get();

            $result = [];
            $no = 1;
            foreach ($items as $item){
                $query = "select it.id, it.name, sum(dt.qty) as total, sum(dt.subtotal) as amount from transaction_details dt
                    inner join transaction_headers hd on dt.header_id = hd.id
                    inner join items it on dt.item_id = it.id
                    where dt.item_id = ". $item->id."
                    and dt.created_at >= '". $startDate->format('Y-m-d H:i:s') ."'
                    and dt.created_at <= '". $endDate->format('Y-m-d H:i:s') ."'
                    group by it.id, it.name;";
//                dd($query);
                $dbQuery = DB::select($query);
                //dd($dbQuery);
                if(count($dbQuery) > 0){
                    $ppn = $dbQuery[0]->amount * 0.1;
                    $grandtotal = $dbQuery[0]->amount + $ppn;
                    $nResult = [
                        'no'        => $no,
                        'name'      => $dbQuery[0]->name,
                        'qty'       => $dbQuery[0]->total,
                        'amount'    => $dbQuery[0]->amount,
                        'tax'    => $ppn,
                        'grandtotal'    => $grandtotal,
                    ];
                    $result[] = $nResult;
                    $no++;
                }
            }
            $data = [
                'result'        => $result,
                'startDate'     => $startDate->format('d M Y'),
                'endDate'       => $endDate->format('d M Y'),
            ];
            return view('admin.transaction.daily_item')->with($data);
        }
        catch (\Exception $ex)
        {
            Session::flash('error', $ex);
            return back();
        }
    }

    public function generateItemReportData(Request $request){
        try{
            $rules = array(
                'start_date'    => 'required',
                'end_date'      => 'required',
            );

            $message = array(
                'start_date.required'   => 'Tanggal Mulai Wajib dipilih!',
                'end_date.required'     => 'Tanggal Akhir Wajib dipilih!',
            );

            $validator = Validator::make($request->all(), $rules, $message);

            if ($validator->fails()) {
                return Response::json(['error' => $validator->errors()->all()]);
            }

            //get data
            $startDate = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
            $endDate = Carbon::createFromFormat('!d-m-Y', $request->input('end_date'), 'Asia/Jakarta');
            $endDate->addDay();

            $user = Auth::guard('admin')->user();

            $items = Item::where('branch_id', $user->branch_id)
                ->get();

            $result = [];
            $no = 1;
            $totalGross = 0;
            $totalTax = 0;
            $totalNett = 0;
            foreach ($items as $item){
                $query = "select it.id, it.name, sum(dt.qty) as total, sum(dt.subtotal) as amount from transaction_details dt
                    inner join transaction_headers hd on dt.header_id = hd.id
                    inner join items it on dt.item_id = it.id
                    where dt.item_id = ". $item->id."
                    and dt.created_at >= '". $startDate->format('Y-m-d H:i:s') ."'
                    and dt.created_at <= '". $endDate->format('Y-m-d H:i:s') ."'
                    group by it.id, it.name;";
//                dd($query);
                $dbQuery = DB::select($query);
                //dd($dbQuery);
                if(count($dbQuery) > 0){
                    $ppn = $dbQuery[0]->amount * 0.1;
                    $grandtotal = $dbQuery[0]->amount + $ppn;
                    $nResult = [
                        'no'        => $no,
                        'name'      => $dbQuery[0]->name,
                        'qty'       => $dbQuery[0]->total,
                        'amount'    => $dbQuery[0]->amount,
                        'tax'    => $ppn,
                        'grandtotal'    => $grandtotal,
                    ];
                    $result[] = $nResult;
                    $no++;
                    $totalGross += $dbQuery[0]->amount;
                    $totalTax += $ppn;
                    $totalNett += $grandtotal;
                }
            }
            $data = [
                'details'        => $result,
                'totalGross'        => $totalGross,
                'totalTax'        => $totalTax,
                'totalNett'        => $totalNett,
                'periode'     => $startDate->format('d M Y') ." sampai ". $endDate->format('d M Y'),
            ];
            return $data;
        }
        catch (\Exception $ex)
        {
            Session::flash('error', $ex);
            return back();
        }
    }

    public function filterGroReport(){
        $startDate = Carbon::now('Asia/Jakarta');
        $endDate = Carbon::now('Asia/Jakarta');
        $endDate->addDay();

        $data = [
            'startDate' => $startDate->format('d-m-Y'),
            'endDate'   => $endDate->format('d-m-Y')
        ];

        return view('admin.report.report_gro')->with($data);
    }

    public function generateGroReport(Request $request){
        try{
            $rules = array(
                'start_date'    => 'required',
                'end_date'      => 'required',
            );

            $message = array(
                'start_date.required'   => 'Tanggal Mulai Wajib dipilih!',
                'end_date.required'     => 'Tanggal Akhir Wajib dipilih!',
            );

            $validator = Validator::make($request->all(), $rules, $message);

            if ($validator->fails()) {
                return Response::json(['error' => $validator->errors()->all()]);
            }

            $user = Auth::user();
            $packages = Package::where('branch_id', $user->branch_id)
                //->where('name', 'not like', '%Reflexiology%')
                ->get();

            $startDate = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
            $endDate = Carbon::createFromFormat('!d-m-Y', $request->input('end_date'), 'Asia/Jakarta');
            $endDate->addDay();

            $headers = TransactionHeader::where('branch_id', $user->branch_id)
                ->whereBetween('created_at', [$startDate->toDateTimeString(), $endDate->toDateTimeString()])
                ->get();
            $results = [];
            $positionData = EmployeePosition::where('branch_id', $user->branch_id)->where('code', 'GRO')->first();
            $employees = Employee::where('branch_id', $user->branch_id)
                ->where('id', '!=', 80)
                ->where('status_id', '=', 1)
                ->where('position_id', $positionData->id)
                ->orderBy('name')
                ->get();
            $gros = [];
            foreach($employees as $emp){
                $nGro = [
                    'id'        => $emp->id,
                    'number'    => $emp->number,
                    'name'      => $emp->name,
                    'total'     => 0,
                ];

                $gros[] = $nGro;
            }

            foreach($packages as $package){
                $nArr = [
                    'name'      => $package->name,
                    'gros'      => [],
                ];

                $i = 0;
                foreach($gros as $gro){
                    $query = "select count(dt.id) as total from transaction_details dt
                        inner join transaction_headers hd on hd.id = dt.header_id
                        where hd.employee_id = ". $gro['id'] ."
                        and dt.package_id = ". $package->id ."
                        and dt.created_at >= '". $startDate->format('Y-m-d H:i:s') ."'
                        and dt.created_at <= '". $endDate->format('Y-m-d H:i:s') ."';";

                    $total = DB::select($query);
                    $groArr = [
                        'number'    => $gro['number'],
                        'name'      => $gro['name'],
                        'total'     => $total[0]->total,
                    ];

                    $gros[$i]['total'] += $total[0]->total;
                    $nArr['gros'][] = $groArr;
                    $i++;
                }

                $result[] = $nArr;
            }

            $data = [
                'startDate' => $startDate->format('Y-m-d H:i:s'),
                'endDate'   => $endDate->format('Y-m-d H:i:s'),
                'result'    => $result,
                'gros'      => $gros,
            ];

            return view('admin.transaction.gro_report')->with($data);
        }
        catch (\Exception $ex)
        {
            Session::flash('error', $ex);
            return back();
        }
    }
}
