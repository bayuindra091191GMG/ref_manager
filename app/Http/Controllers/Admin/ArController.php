<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Libs\AutoNumberGenerator;
use App\Libs\Utilities;
use App\Models\AdvancePayment;
use App\Models\ArDetail1;
use App\Models\ArDetail2;
use App\Models\ArHeader;
use App\Models\Branch;
use App\Models\Coa;
use App\Models\Employee;
use App\Models\InvoiceManualCustomHeader;
use App\Models\JournalDetail;
use App\Models\JournalHeader;
use App\Models\Partner;
use App\Models\SalesInvoiceHeader;
use App\Transformer\ArTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class ArController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function select(Request $request){
        $user = Auth::guard('admin')->user();

        $term = trim($request->search);

        $searchDatas = DB::table('ar_headers')
            ->select('id','code','description')
            ->where(function($q) use ($term){
                $q->Where('code', 'LIKE', '%'. $term. '%')
                    ->orWhere('description', 'LIKE', '%'. $term. '%');
            })
            ->where('status_id', 13)
            ->orderByDesc('id')
            ->where('branch_id', $user->branch_id)
            ->get();

        $formatted_tags = [];

        foreach ($searchDatas as $searchData) {
            $formatted_tags[] = ['id' => $searchData->id, 'text' => $searchData->code];
        }

        return Response::json($formatted_tags);
    }

    public function getIndex(Request $request){
        $user = Auth::guard('admin')->user();

        // Filter date
        $start = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
        $end = Carbon::createFromFormat('!d-m-Y', $request->input('end_date'), 'Asia/Jakarta');
        $end->addDays(1)->addMinutes(-1);

        $datas = ArHeader::with(['status'])
            ->where('status_id', '!=', 2)
            ->where('branch_id', $user->branch_id)
            ->whereBetween('date', array($start->toDateTimeString(), $end->toDateTimeString()))
            ->orderByDesc('id');

        return DataTables::of($datas)
            ->setTransformer(new ArTransformer)
            ->make(true);
    }

    public function index(Request $request){
        $filterStartDate = Carbon::today()->startOfYear()->format('d-m-Y');
        $filterEndDate = Carbon::today()->format('d-m-Y');

        if($request->start_date != null && $request->end_date != null){
            $startDateDecoded = rawurldecode($request->start_date);
            $endDateDecoded = rawurldecode($request->end_date);
            $start = Carbon::createFromFormat('!d-m-Y', $startDateDecoded, 'Asia/Jakarta');
            $end = Carbon::createFromFormat('!d-m-Y', $endDateDecoded, 'Asia/Jakarta');

            if($end->greaterThanOrEqualTo($start)){
                $filterStartDate = $startDateDecoded;
                $filterEndDate = $endDateDecoded;
            }
        }

        $data = [
            'filterStartDate' => $filterStartDate,
            'filterEndDate' => $filterEndDate
        ];

        return view('admin.ar.index')->with($data);
    }

    public function create(Request $request){
        $user = Auth::guard('admin')->user();
        if($user->can('branch.scope_all') == 1){
            $branches = Branch::all();
        }
        else{
            $branches = Branch::find($user->branch_id);
        }
        $data = [
            'branches'      => $branches,
        ];

        return view('admin.ar.create')->with($data);
    }

    public function store(Request $request){
        try{
//            dd($request);
            $salesInvIds = $request->input('salesInvIds');
            $salesInvCodes = $request->input('salesInvCodes');
            $nominal = $request->input('nominal');
            $total = $request->input('total');
            $descs = $request->input('descs');

            if (count($salesInvIds) > 0) {
                if (count($salesInvIds) != count($total) && count($salesInvIds) != count($salesInvCodes)) {
                    return back()->withErrors("Detail Invoice tidak lengkap")->withInput($request->all());
                }
            }
            if($request->filled('employee') && $request->filled('partner')){
                return back()->withErrors("Harus memilih Karyawan / Partner")->withInput($request->all());
            }
            if(!$request->filled('employee') && !$request->filled('partner')){
                return back()->withErrors("Karyawan / Partner harus terpilih")->withInput($request->all());
            }

            $coaIds = $request->input('coaIds');
            $coaCodes = $request->input('coaCodes');
            $debits = $request->input('debits');
            $credits = $request->input('credits');
            $descCoas = $request->input('descCoas');

            if (count($coaIds) > 0) {
                if ((count($coaIds) != count($debits) || count($coaIds) != count($credits)) && count($coaIds) != count($descs)) {
                    return back()->withErrors("Detail COA tidak lengkap")->withInput($request->all());
                }
            }

            $user = Auth::guard('admin')->user();
            $data = new ArHeader();
            $data->code = AutoNumberGenerator::generateAutonumberTrx('AR');
            $data->branch_id = $user->branch_id;
            // $data->customer_id = $request->input('customer_id');
            $data->date = Carbon::parse($request->input('date'))->toDateTimeString();
            $data->description = $request->input('description') ?? "";
            $data->subtotal = Utilities::toFloat($request->input('subtotal'));
            $data->grandtotal = Utilities::toFloat($request->input('grandtotal'));
            $data->coa_id = $request->input('coa_id');
            $data->coa_code = Coa::find($request->input('coa_id'))->code;
            $data->status_id = 1;
            $data->created_by = $user->id;
            $data->employee_name = "";
            $data->partner_number = "";
            $data->partner_alias = "";
            if($request->filled('employee')){
                $employeeId = $request->input('employee');
                $employeeDb = Employee::where('id', $employeeId)->first();
                $data->employee_id = $employeeId;
                $data->employee_name = $employeeDb->name;
            }
            else{
                $partnerId = $request->input('partner');
                $partnerDb = Partner::where('id', $partnerId)->first();
                $data->partner_id = $partnerId;
                $data->partner_number = $partnerDb->number;
                $data->partner_alias = $partnerDb->alias;
            }

            $data->save();

            $ct = 0;
            foreach ($salesInvIds as $itemId){
                $dataDetail = new ArDetail1();
                $dataDetail->header_code = $data->code;
                $dataDetail->header_id = $data->id;
                $dataDetail->invoice_id = $itemId;
                $dataDetail->invoice_code = $salesInvCodes[$ct] ?? "";
                $dataDetail->nominal_invoice = $nominal[$ct]!= null ? Utilities::toFloat($nominal[$ct]) : 0;
                $dataDetail->debit = $total[$ct]!= null ? Utilities::toFloat($total[$ct]) : 0;
                $dataDetail->description = $descs[$ct] ?? "";
                $dataDetail->save();

                $ct++;
            }

            if (count($coaIds) > 0) {
                $ct = 0;
                foreach ($coaIds as $itemId){
                    $dataDetail = new ArDetail2();
                    $dataDetail->header_code = $data->code;
                    $dataDetail->header_id = $data->id;
                    $dataDetail->coa_id = $itemId;
                    $dataDetail->coa_code = Coa::find($itemId)->code;
                    $dataDetail->debit = $debits[$ct] != null ? Utilities::toFloat($debits[$ct]) : 0;
                    $dataDetail->kredit = $credits[$ct] != null ? Utilities::toFloat($credits[$ct]) : 0;
                    $dataDetail->description = $descCoas[$ct] ?? "";
                    $dataDetail->save();

                    $ct++;
                }
            }

            Session::flash('success', 'Sukses membuat data Acc Receive baru!');
            return redirect()->route('admin.ar.index');
        }
        catch (\Exception $ex){
            Session::flash('error', 'Maaf kesalahan terjadi!' . $ex);
            return back()->withInput($request->all());
        }
    }

    public function edit($id){
        $user = Auth::guard('admin')->user();
        if($user->can('branch.scope_all') == 1){
            $branches = Branch::all();
        }
        else{
            $branches = Branch::find($user->branch_id);
        }

        $dataDB = ArHeader::where('id', $id)->first();
        if($dataDB == null){
            Session::flash('error', 'Data tidak ditemukan!');
            return back();
        }
        $detailData = [];
        $detailData2 = [];
        foreach ($dataDB->ar_detail1s as $dataDetail){
            $vueData = [
                "salesInvId"    => $dataDetail->invoice_id,
                "salesInvCode"    => $dataDetail->invoice_code,
                "nominal"     => $dataDetail->nominal_invoice,
                "total"     => $dataDetail->debit,
                "desc"      => $dataDetail->description,
            ];
            array_push($detailData, $vueData);
        }

        foreach ($dataDB->ar_detail2s as $dataDetail){
            $vueData = [
                "coaId"     => $dataDetail->coa_id,
                "coaCode"   => $dataDetail->coa_code,
                "debit"     => $dataDetail->debit,
                "credit"     => $dataDetail->kredit,
                "desc"      => $dataDetail->description,
            ];
            array_push($detailData2, $vueData);
        }

        $vueData = [
            'itemList' => $detailData,
            'itemList2' => $detailData2,
        ];

        $data = [
            'branches'  => $branches,
            'data'      => $dataDB,
            'vueData'      => $vueData,
        ];

//        dd($vueData);

        return view('admin.ar.edit')->with($data);
    }

    public function update(Request $request){
        try{
//            dd($request);
            $salesInvIds = $request->input('salesInvIds');
            $salesInvCodes = $request->input('salesInvCodes');
            $total = $request->input('total');
            $nominal = $request->input('nominal');
            $descs = $request->input('descs');

            if (count($salesInvIds) > 0) {
                if (count($salesInvIds) != count($total) && count($salesInvIds) != count($salesInvCodes)) {
                    return back()->withErrors("Detail Invoice tidak lengkap")->withInput($request->all());
                }
            }
            if($request->filled('employee') && $request->filled('partner')){
                return back()->withErrors("Harus memilih Karyawan / Partner")->withInput($request->all());
            }
            if(!$request->filled('employee') && !$request->filled('partner')){
                return back()->withErrors("Karyawan / Partner harus terpilih")->withInput($request->all());
            }

            $coaIds = $request->input('coaIds');
            $coaCodes = $request->input('coaCodes');
            $debits = $request->input('debits');
            $credits = $request->input('credits');
            $descCoas = $request->input('descCoas');

            if (count($coaIds) > 0) {
                if ((count($coaIds) != count($debits) || count($coaIds) != count($credits)) && count($coaIds) != count($descs)) {
                    return back()->withErrors("Detail COA tidak lengkap")->withInput($request->all());
                }
            }

            $user = Auth::guard('admin')->user();
            $data = ArHeader::where('id', $request->input('id'))->first();
            if($data == null){
                Session::flash('error', 'Data tidak ditemukan!');
                return back()->withInput($request->all());
            }
            $data->branch_id = $user->branch_id;
            $data->date = Carbon::parse($request->input('date'))->toDateTimeString();
            // $data->customer_id = $request->input('customer_id');
            $data->description = $request->input('description') ?? "";
            $data->subtotal = Utilities::toFloat($request->input('subtotal'));
            $data->grandtotal = Utilities::toFloat($request->input('grandtotal'));
            $data->coa_id = $request->input('coa_id');
            $data->coa_code = Coa::find($request->input('coa_id'))->code;
            $data->updated_by = $user->id;
            $data->employee_name = "";
            $data->partner_number = "";
            $data->partner_alias = "";
            if($request->filled('employee')){
                $employeeId = $request->input('employee');
                $employeeDb = Employee::where('id', $employeeId)->first();
                $data->employee_id = $employeeId;
                $data->employee_name = $employeeDb->name;
            }
            else{
                $partnerId = $request->input('partner');
                $partnerDb = Partner::where('id', $partnerId)->first();
                $data->partner_id = $partnerId;
                $data->partner_number = $partnerDb->number;
                $data->partner_alias = $partnerDb->alias;
            }
            $data->save();

            $oldData = ArDetail1::where('header_id', $request->input('id'))->get();
            if(count($oldData) > 0){
                foreach($oldData as $oldDetaildata){
                    $oldDetaildata->delete();
                }
            }
            $oldData2 = ArDetail2::where('header_id', $request->input('id'))->get();
            if(count($oldData2) > 0){
                foreach($oldData2 as $oldDetaildata){
                    $oldDetaildata->delete();
                }
            }
            $ct = 0;
            foreach ($salesInvIds as $itemId){
                $dataDetail = new ArDetail1();
                $dataDetail->header_code = $data->code;
                $dataDetail->header_id = $data->id;
                $dataDetail->invoice_id = $itemId;
                $dataDetail->invoice_code = $salesInvCodes[$ct] ?? "";
                $dataDetail->nominal_invoice = $nominal[$ct]!= null ? Utilities::toFloat($nominal[$ct]) : 0;
                $dataDetail->debit = $total[$ct]!= null ? Utilities::toFloat($total[$ct]) : 0;
                $dataDetail->description = $descs[$ct] ?? "";
                $dataDetail->save();

                $ct++;
            }
            if (count($coaIds) > 0) {

                $ct = 0;
                foreach ($coaIds as $coaId){
                    $dataDetail = new ArDetail2();
                    $dataDetail->header_code = $data->code;
                    $dataDetail->header_id = $data->id;
                    $dataDetail->coa_id = $coaId;
                    $dataDetail->coa_code = Coa::find($itemId)->code;
                    $dataDetail->debit = $debits[$ct] != null ? Utilities::toFloat($debits[$ct]) : 0;
                    $dataDetail->kredit = $credits[$ct] != null ? Utilities::toFloat($credits[$ct]) : 0;
                    $dataDetail->description = $descCoas[$ct] ?? "";
                    $dataDetail->save();

                    $ct++;
                }
            }

            Session::flash('success', 'Sukses mengubah data Acc Receive!');
            return redirect()->route('admin.ar.index');
        }
        catch (\Exception $ex){
            Session::flash('error', 'Maaf kesalahan terjadi!' . $ex);
            return back()->withInput($request->all());
        }
    }

    public function show($id, Request $request){
        $user = Auth::guard('admin')->user();
        if($user->can('branch.scope_all') == 1){
            $branches = Branch::all();
        }
        else{
            $branches = Branch::find($user->branch_id);
        }

        $dataDB = ArHeader::where('id', $id)->first();
        if($dataDB == null){
            Session::flash('error', 'Data tidak ditemukan!');
            return back();
        }
        $detailData = [];
        $detailData2 = [];
        foreach ($dataDB->ar_detail1s as $dataDetail){
            $vueData = [
                "salesInvId"    => $dataDetail->invoice_id,
                "salesInvCode"    => $dataDetail->invoice_code,
                "nominal"     => $dataDetail->nominal_invoice,
                "total"     => $dataDetail->debit,
                "desc"      => $dataDetail->description,
            ];
            array_push($detailData, $vueData);
        }

        foreach ($dataDB->ar_detail2s as $dataDetail){
            $vueData = [
                "coaId"     => $dataDetail->coa_id,
                "coaCode"   => $dataDetail->coa_code,
                "debit"     => $dataDetail->debit,
                "credit"     => $dataDetail->kredit,
                "desc"      => $dataDetail->description,
            ];
            array_push($detailData2, $vueData);
        }

        $vueData = [
            'itemList' => $detailData,
            'itemList2' => $detailData2,
        ];

        $data = [
            'branches'  => $branches,
            'data'      => $dataDB,
            'vueData'      => $vueData,
            'itemList' => $detailData,
            'itemList2' => $detailData2,
        ];
        $type = trim($request->type);
        if(!empty($type)){
            return view('admin.ar.print')->with($data);
        }


        return view('admin.ar.show')->with($data);
    }

    public function delete(Request $request){
        try{
            $ar = ArHeader::where('id', $request->input('id'))->first();
            if($ar == null){
                Session::flash('error', 'Data tidak ditemukan!');
                return back();
            }

            $ar->status_id = 2;
            $ar->save();

            return Response::json('Sukses', 200);
        }
        catch (\Exception $ex){
            Log::error($ex);
            return Response::json('error', 200);
        }
    }

    public function approve(Request $request){
        try{
//            dd($request);
            $ids = $request->input('ids');
            $user = Auth::guard('admin')->user();
            if(empty($ids)){
                Session::flash('error', 'Tidak Ada Data yang Dipilih');
                return redirect()->route('admin.ar.index');
            }
            foreach ($ids as $id) {
                $dataDB = ArHeader::where('id', $id)->first();
                if($dataDB == null){
                    Session::flash('error', 'Data tidak ditemukan!');
                    return back();
                }
                $dataDB->is_approve = 1;
                $dataDB->status_id = 13;
                $dataDB->updated_by = $user->id;
                $dataDB->save();

                // Journal Header Process START
                $jurnal = new JournalHeader();
                $jurnal->transaction_code = AutoNumberGenerator::generateAutonumberTrx('JN');
                $jurnal->transaction_type = 'ACC_RECEIVE';
                $jurnal->transaction_date = Carbon::now('Asia/Jakarta');
                $jurnal->notes = "Jurnal Acc Receive ".$dataDB->code;
                $jurnal->created_by = $user->id;
                $jurnal->branch_id = $user->branch_id;
                $jurnal->reference_id = $dataDB->id;
                $jurnal->reference_code = $dataDB->code;
                $jurnal->status_id = 1;
                $jurnal->save();
                // Journal Header Process END

                // Journal Detail START

                foreach ($dataDB->ar_detail2s as $dataDetail2){
                    $coa = Coa::where('id', $dataDetail2->coa_id)
                        ->first();
                    if(!empty($coa)){

                        $jDt = new JournalDetail();
                        $jDt->branch_id = $user->branch_id;
                        $jDt->header_id = $jurnal->id;
                        $jDt->account_code = $coa->code;
                        $jDt->account_type_id = $coa->type_id;
                        $jDt->account_name = $coa->name;
                        $jDt->account_id = $coa->id;
                        $jDt->account_type_name = $coa->type;
                        $jDt->debit = $dataDetail2->debit;
                        $jDt->credit = 0;
                        $jDt->notes = 'Acc Receive ' . $dataDB->code;
                        $jDt->reference_code = $dataDB->code;
//                            $jDt->transaction_code = AutoNumberGenerator::generateAutonumberTrx('JN');
                        $jDt->transaction_code = $jurnal->transaction_code;
                        $jDt->transaction_type = 'ACC_RECEIVE';
                        $jDt->original_amount = $dataDetail2->debit;
                        $jDt->save();
                    }
                }

                // jurnal piutang dari pilihan header
                $coa = Coa::where('id', $dataDB->coa_id)
                    ->first();
                if(!empty($coa)){
                    $jDt = new JournalDetail();
                    $jDt->branch_id = $user->branch_id;
                    $jDt->header_id = $jurnal->id;
                    $jDt->account_code = $coa->code;
                    $jDt->account_type_id = $coa->type_id;
                    $jDt->account_name = $coa->name;
                    $jDt->account_id = $coa->id;
                    $jDt->account_type_name = $coa->type;
                    $jDt->debit = 0;
                    $jDt->credit = $dataDB->grandtotal;
                    $jDt->notes = 'Acc Receive ' . $dataDB->code;
                    $jDt->reference_code = $dataDB->code;
    //                            $jDt->transaction_code = AutoNumberGenerator::generateAutonumberTrx('JN');
                    $jDt->transaction_code = $jurnal->transaction_code;
                    $jDt->transaction_type = 'ACC_RECEIVE';
                    $jDt->original_amount = $dataDB->grandtotal;
                    $jDt->save();
                }
                // Journal Detail END
            }

            Session::flash('success', 'Sukses approve data Acc Receive!');
            return redirect()->route('admin.ar.index');
        }
        catch (\Exception $ex){
            Session::flash('error', 'Maaf kesalahan terjadi!' . $ex);
            return back();
        }
    }

    public function filter($type){
        $startDate = Carbon::now('Asia/Jakarta');
        $endDate = Carbon::now('Asia/Jakarta');
        $typename = "ACC Receive";

        switch ($type){

            case 'ar_card' :
                $typename = 'AR CARD';
                break;
            case 'ar_invoice_list' :
                $typename = 'AR INVOICE';
                break;
            case 'ar_aging' :
                $typename = 'AR AGING';
                break;
        }
        $data = [
            'startDate' => $startDate->format('d-m-Y'),
            'endDate'   => $endDate->format('d-m-Y'),
            'type'   => $type,
            'typename'   => $typename,
        ];

        return view('admin.report.report_ar_filter')->with($data);
    }

    public function report(Request $request){
        try{
            $rules = array(
                'start_date'=> 'required',
//                'type '=> 'required',
            );

            $message = array(
                'start_date.required'   => 'Tanggal Mulai Wajib dipilih!',
            );

            $validator = Validator::make($request->all(), $rules, $message);

            if ($validator->fails()) return redirect()->back()->withErrors($validator->errors())->withInput($request->all());

            //get data
            $startDate = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
            $endDate = Carbon::createFromFormat('!d-m-Y', $request->input('end_date'), 'Asia/Jakarta');
            $endDate->addDays(1)->addMinutes(-1);

            $result = [];
            $type = $request->input('type');

            $headers = InvoiceManualCustomHeader::where('status_id', 13)
                ->whereBetween('created_at', [$startDate->toDateTimeString(), $endDate->toDateTimeString()])
                ->orderby('employee_id')->orderby('partner_id')->orderby('code')->get();
            // dd($type, $headers);
            $currentSupp = 0;
            $nResult = [];
            $totalPaidAmount = 0;
            $totalGrandTotal = 0;
            $totalOutstandingAmount = 0;
            $no = 1;

            switch($request->input('type')) {
                case 'ar_card' :
                    foreach ($headers as $header){
                        $trxDate = Carbon::parse($header->date);

                        $apDetails = DB::table('ar_detail1s')
                            ->join('ar_headers', 'ar_headers.id', '=', 'ar_detail1s.header_id')
                            ->select(
                                'ar_detail1s.header_id as id',
                                'ar_detail1s.header_code as code',
                                'ar_detail1s.nominal_invoice as nominal_invoice',
                                'ar_detail1s.debit as debit',
                                'ar_headers.date as paid_date',
                            )
                            ->where('ar_headers.status_id', 13)
                            ->where('ar_detail1s.invoice_id', $header->id)
                            ->get();
                    //    dd($apDetails);

                        $currentBalance = $header->grandtotal;
                        if(count($apDetails) > 0){
                            foreach($apDetails as $apDetail){
                                $balance =  $apDetail->nominal_invoice - $apDetail->debit;
                                $nTrx = [
                                    'no'                => $no,
                                    'code'              => $header->code,
                                    'date'              => $trxDate->locale('id_ID')->format('d-m-Y'),
                                    'customer_name'     => "",
                                    'grand_total'       => $header->grandtotal,
                                    'current_balance'   => $currentBalance,
                                    'paid_no'           => $apDetail->code,
                                    'paid_date'         => Carbon::parse($apDetail->paid_date)->locale('id_ID')->format('d-m-Y'),
                                    'paid_amount'       => $apDetail->debit,
                                    'balance'           => $balance,
                                    'created_by'        => $header->status->display,
                                ];
                                if($header->employee_id != null){
                                    $nTrx['customer_name'] = $header->employee_name;
                                }
                                else{
                                    $nTrx['customer_name'] = $header->partner_number. " - ". $header->partner_alias;
                                }
                                $exist = false;
                                for($i=0; $i<count($result); $i++){
                                    if($header->employee_id != null){
                                        if($header->employee_id == $result[$i]['customer_code']){
                                            $exist = true;
                                            $result[$i]['transactions'][] = $nTrx;
                                            $result[$i]['subtotal_grand_total'] += $header->grandtotal;
                                            $result[$i]['subtotal_paid_amount'] += 0;
                                        }
                                    }
                                    else{
                                        if($header->partner_id == $result[$i]['customer_code']){
                                            $exist = true;
                                            $result[$i]['transactions'][] = $nTrx;
                                            $result[$i]['subtotal_grand_total'] += $header->grandtotal;
                                            $result[$i]['subtotal_paid_amount'] += 0;
                                        }
                                    }
                                }
                                if(!$exist){
                                    $nResult = [
                                        'customer_code'     => "",
                                        'customer_name'     => "",
                                        'transactions'      => [],
                                        'subtotal_grand_total'  => $header->grandtotal,
                                        'subtotal_paid_amount'  => $apDetail->debit
                                    ];
                                    if($header->employee_id != null){
                                        $nResult['customer_code'] = $header->employee_id;
                                        $nResult['customer_name'] = $header->employee_name;
                                    }
                                    else{
                                        $nResult['customer_code'] = $header->partner_id;
                                        $nResult['customer_name'] = $header->partner_number. " - ". $header->partner_alias;
                                    }
                                    $nResult['transactions'][] = $nTrx;
                                    $currentSupp = $header->customer_id;
                                    $no = 1;
                                    $result[] = $nResult;
                                }
                                $no++;

                                $totalGrandTotal += $header->grandtotal;
                                $totalPaidAmount += $apDetail->debit;
                            }
                        }
                        else{
                            $balance =  $currentBalance - 0;
                            $nTrx = [
                                'no'                => $no,
                                'code'              => $header->code,
                                'date'              => $trxDate->locale('id_ID')->format('d-m-Y'),
                                'customer_name'     => "",
                                'grand_total'       => $header->grandtotal,
                                'current_balance'   => $currentBalance,
                                'paid_no'           => "-",
                                'paid_date'         => "-",
                                'paid_amount'       => 0,
                                'balance'           => $balance,
                                'created_by'        => $header->status->display,
                            ];
                            $exist = false;
                            for($i=0; $i<count($result); $i++){
                                if($header->employee_id != null){
                                    if($header->employee_id == $result[$i]['customer_code']){
                                        $exist = true;
                                        $result[$i]['transactions'][] = $nTrx;
                                        $result[$i]['subtotal_grand_total'] += $header->grandtotal;
                                        $result[$i]['subtotal_paid_amount'] += 0;
                                    }
                                }
                                else{
                                    if($header->partner_id == $result[$i]['customer_code']){
                                        $exist = true;
                                        $result[$i]['transactions'][] = $nTrx;
                                        $result[$i]['subtotal_grand_total'] += $header->grandtotal;
                                        $result[$i]['subtotal_paid_amount'] += 0;
                                    }
                                }
                            }
                            if(!$exist){
                                $nResult = [
                                    'customer_code'     => "",
                                    'customer_name'     => "",
                                    'transactions'      => [],
                                    'subtotal_grand_total'  => $header->grandtotal,
                                    'subtotal_paid_amount'  => 0
                                ];
                                if($header->employee_id != null){
                                    $nResult['customer_code'] = $header->employee_id;
                                    $nResult['customer_name'] = $header->employee_name;
                                }
                                else{
                                    $nResult['customer_code'] = $header->partner_id;
                                    $nResult['customer_name'] = $header->partner_number. " - ". $header->partner_alias;
                                }
                                $nResult['transactions'][] = $nTrx;
                                $currentSupp = $header->customer_id;
                                $no = 1;
                                $result[] = $nResult;
                            }
                            $no++;

                            $totalGrandTotal += $header->grandtotal;
                            $totalPaidAmount += 0;
                        }

                    }
                    break;
                case 'ar_invoice_list' :
                    $no = 1;
                    foreach ($headers as $header){
                        $trxDate = Carbon::parse($header->created_at);

                        $apDetailTotals = DB::table('ar_headers')
                            ->join('ar_detail1s', 'ar_headers.id', '=', 'ar_detail1s.header_id')
                            ->where('ar_headers.status_id', 13)
                            ->where('ar_detail1s.invoice_id', $header->id)
                            ->sum('ar_detail1s.debit');

                        if($apDetailTotals != $header->grandtotal){
                            $nTrx = [
                                'no'                => $no,
                                'code'              => $header->code,
                                'date'              => $trxDate->locale('id_ID')->format('d-m-Y'),
                                'customer_name'     => $header->customer->name,
                                'grand_total'       => number_format($header->grandtotal, '0', ',', '.'),
                                'paid_amount'       => number_format($apDetailTotals, '0', ',', '.'),
                                'created_by'        => $header->status->display,
                            ];
                            $exist = false;
                            for($i=0; $i<count($result); $i++){
                                if($header->customer->code == $result[$i]['customer_code']){
                                    $exist = true;
                                    $result[$i]['transactions'][] = $nTrx;
                                    $result[$i]['subtotal_grand_total'] += $header->grandtotal;
                                    $result[$i]['subtotal_paid_amount'] += $apDetailTotals;
                                }
                            }
                            if(!$exist){
                                $nResult = [
                                    'customer_code'     => $header->customer->code,
                                    'customer_name'     => $header->customer->name,
                                    'transactions'      => [],
                                    'subtotal_grand_total'  => $header->grandtotal,
                                    'subtotal_paid_amount'  => 0
                                ];
                                $nResult['transactions'][] = $nTrx;
                                $currentSupp = $header->customer_id;
                                $no = 1;
                                $result[] = $nResult;
                            }
                            $no++;

                            $totalGrandTotal += $header->grandtotal;
                            $totalPaidAmount += $apDetailTotals;
                        }

                    }
                    break;
                case 'ar_aging' :
                    $no = 1;
                    foreach ($headers as $header){
                        $trxDate = Carbon::parse($header->date);

                        $apDetailTotals = DB::table('ar_headers')
                            ->join('ar_detail1s', 'ar_headers.id', '=', 'ar_detail1s.header_id')
                            ->where('ar_headers.status_id', 13)
                            ->where('ar_detail1s.invoice_id', $header->id)
                            ->sum('ar_detail1s.debit');

                        if($apDetailTotals != $header->grandtotal){
                            $invoiced = "x";
                            $t30 = 0;
                            $t60 = 0;
                            $t90 = 0;
                            $t120 = 0;
                            $t120_more = 0;

                            $now = Carbon::now();
                            $diff = $trxDate->diffInDays($now);
                            if($diff < 30){
                                $t30 = $header->grandtotal - $apDetailTotals;
                            }
                            else if($diff >= 30 && $diff < 60){
                                $t60 = $header->grandtotal - $apDetailTotals;
                            }
                            else if($diff >= 60 && $diff < 90){
                                $t90 = $header->grandtotal - $apDetailTotals;
                            }
                            else if($diff >= 90 && $diff < 120){
                                $t120 = $header->grandtotal - $apDetailTotals;
                            }
                            else{
                                $t120_more = $header->grandtotal - $apDetailTotals;
                            }

                            $nTrx = [
                                'no'                => $no,
                                'code'              => $header->code,
                                'date'              => $trxDate->locale('id_ID')->format('d-m-Y'),
                                'customer_name'     => $header->customer->name,
                                'grand_total'       => number_format($header->grandtotal, '0', ',', '.'),
                                'paid_amount'       => number_format($apDetailTotals, '0', ',', '.'),
                                'invoiced'          => $invoiced,
                                't30'               => number_format($t30, '0', ',', '.'),
                                't60'               => number_format($t60, '0', ',', '.'),
                                't90'               => number_format($t90, '0', ',', '.'),
                                't120'              => number_format($t120, '0', ',', '.'),
                                't120_more'         => number_format($t120_more, '0', ',', '.'),
                                'created_by'        => $header->status->display,
                            ];
                            $exist = false;
                            for($i=0; $i<count($result); $i++){
                                if($header->customer->name == $result[$i]['customer_name']){
                                    $exist = true;
                                    $result[$i]['transactions'][] = $nTrx;
                                    $result[$i]['subtotal_grand_total'] += $header->grandtotal;
                                    $result[$i]['subtotal_paid_amount'] += 0;
                                    $result[$i]['subtotal_outstanding_amount'] += ($header->grandtotal - $apDetailTotals);
                                }
                            }
                            if(!$exist){
                                $nResult = [
                                    'customer_name'        => $header->customer->name,
                                    'transactions'  => [],
                                    'subtotal_grand_total'  => $header->grandtotal,
                                    'subtotal_paid_amount'  => $apDetailTotals,
                                    'subtotal_outstanding_amount'  => ($header->grandtotal - $apDetailTotals),
                                ];
                                $nResult['transactions'][] = $nTrx;
                                $currentSupp = $header->customer_id;
                                $no = 1;
                                $result[] = $nResult;
                            }
                            $no++;

                            $totalGrandTotal += $header->grandtotal;
                            $totalPaidAmount += $apDetailTotals;
                            $totalOutstandingAmount += ($header->grandtotal - $apDetailTotals);
                        }
                    }
                    break;
            }
            $user = Auth::guard('admin')->user();
            $branch = Branch::where('id', $user->branch_id)->first();

            $data = [
                'result' => $result,
                'type' => $type,
                'branch' => $branch,
                'start_date' => $startDate->locale('id_ID')->toDateTimeString(),
                'end_date' => $endDate->locale('id_ID')->toDateTimeString(),
                'grand_total_sum' => number_format($totalGrandTotal, '0', ',', '.'),
                'paid_amount_sum' => number_format($totalPaidAmount, '0', ',', '.'),
                'outstanding_amount_sum' => number_format($totalOutstandingAmount, '0', ',', '.'),
            ];
        //    dd($data);
            switch($request->input('type')) {
                case 'ar_card' :
                    return view('admin.ar.report-card')->with($data);
                    break;
                case 'ar_aging' :
                    return view('admin.ar.report-aging')->with($data);
                    break;
                default:
                    return view('admin.ar.report-invoice-list')->with($data);
                    break;
            }
        }
        catch (\Exception $ex){
            Session::flash('error', $ex);
            return back();
        }
    }
}
