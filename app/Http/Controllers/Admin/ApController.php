<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Libs\AutoNumberGenerator;
use App\Libs\Utilities;
use App\Models\ApDetail1;
use App\Models\ApDetail2;
use App\Models\ApHeader;
use App\Models\Branch;
use App\Models\Coa;
use App\Models\Configuration;
use App\Models\GoodReceiveHeader;
use App\Models\Item;
use App\Models\JournalDetail;
use App\Models\JournalHeader;
use App\Models\PurchaseOrderHeader;
use App\Models\SupplierInvoiceDetail1;
use App\Models\SupplierInvoiceHeader;
use App\Transformer\ApTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class ApController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function select(Request $request){
        $user = Auth::guard('admin')->user();

        $term = trim($request->search);

        $searchDatas = DB::table('ap_headers')
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
        $datas = ApHeader::select(
            'ap_headers.id',
            'ap_headers.code',
            'ap_headers.date',
            'ap_headers.supplier_id',
            'suppliers.name as supplier_name',
            'ap_headers.grandtotal',
            'ap_headers.description',
            'ap_headers.status_id',
            'statuses.display as status',
            'ap_headers.created_at',
            'branches.name as branch_name',
        )
            ->leftJoin('suppliers', 'ap_headers.supplier_id', '=', 'suppliers.id')
            ->leftJoin('statuses', 'ap_headers.status_id', '=', 'statuses.id')
            ->leftJoin('branches', 'ap_headers.branch_id', '=', 'branches.id')
            ->where('ap_headers.status_id', '!=', 2)
            ->where('ap_headers.branch_id', $user->branch_id);
            // ->whereBetween('date', array($start->toDateTimeString(), $end->toDateTimeString()));

        return DataTables::of($datas)
            ->setTransformer(new ApTransformer)
            ->make(true);
    }

    public function index(){
        return view('admin.ap.index');
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

        return view('admin.ap.create')->with($data);
    }

    public function store(Request $request){
        try{
//            dd($request);
            $suppInvIds = $request->input('suppInvIds');
            $suppInvCodes = $request->input('suppInvCodes');
            $total = $request->input('total');
            $descs = $request->input('descs');

            if (count($suppInvIds) > 0) {
                if (count($suppInvIds) != count($total) && count($suppInvIds) != count($suppInvCodes)) {
                    return back()->withErrors("Detail Barang tidak lengkap")->withInput($request->all());
                }
            }

            $coaIds = $request->input('coaIds');
            $debits = $request->input('debits');
            $credits = $request->input('credits');
            $descCoas = $request->input('descCoas');
            if($coaIds == null){
                return back()->withErrors('Detil COA Tidak lengkap')->withInput($request->all());
            }

            if (count($coaIds) > 0) {
                if ((count($coaIds) != count($debits) || count($coaIds) != count($credits)) && count($coaIds) != count($descs)) {
                    return back()->withErrors("Detail COA tidak lengkap")->withInput($request->all());
                }
            }

            $user = Auth::guard('admin')->user();
            $data = new ApHeader();
            $data->code = AutoNumberGenerator::generateAutonumberTrx('AP');
            $data->branch_id = $user->branch_id;
            $data->supplier_id = $request->input('supplier_id');
            $data->date = Carbon::parse($request->input('date'))->toDateTimeString();
            $data->description = $request->input('description') ?? "";
            $data->grandtotal = Utilities::toFloat($request->input('grandtotal'));
            $data->status_id = 1;
            $data->created_by = $user->id;
            $data->save();

            $ct = 0;
            foreach ($suppInvIds as $itemId){
                $dataDetail = new ApDetail1();
                $dataDetail->header_code = $data->code;
                $dataDetail->header_id = $data->id;
                $dataDetail->supplier_invoice_id = $itemId;
                $dataDetail->supplier_invoice_code = $suppInvCodes[$ct] ?? "";
                $dataDetail->debit = $total[$ct]!= null ? Utilities::toFloat($total[$ct]) : 1;
                $dataDetail->description = $descs[$ct] ?? "";
                $dataDetail->save();

                $ct++;
            }

            if (count($coaIds) > 0) {
                $ct = 0;
                foreach ($coaIds as $coaId){
                    $dataDetail = new ApDetail2();
                    $dataDetail->header_code = $data->code;
                    $dataDetail->header_id = $data->id;
                    $dataDetail->coa_id = $coaId;
                    $coa = Coa::find($coaId);
                    $dataDetail->coa_code = $coa ? $coa->code. " - ". $coa->name : "";
                    $dataDetail->debit = $debits[$ct] != null ? Utilities::toFloat($debits[$ct]) : 0;
                    $dataDetail->kredit = $credits[$ct] != null ? Utilities::toFloat($credits[$ct]) : 0;
                    $dataDetail->description = $descCoas[$ct] ?? "";
                    $dataDetail->save();

                    $ct++;
                }
            }

            Session::flash('success', 'Sukses membuat data Acc Payment baru!');
            return redirect()->route('admin.ap.index');
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

        $dataDB = ApHeader::where('id', $id)->first();
        if($dataDB == null){
            Session::flash('error', 'Data tidak ditemukan!');
            return back();
        }
        $detailData = [];
        $detailData2 = [];
        foreach ($dataDB->ap_detail1s as $dataDetail){
            $vueData = [
                "suppInvId"    => $dataDetail->supplier_invoice_id,
                "suppInvCode"    => $dataDetail->supplier_invoice_code,
                "total"     => $dataDetail->debit,
                "desc"      => $dataDetail->description,
            ];
            array_push($detailData, $vueData);
        }
        $diffDebitCredit = 0;
        $grandTotalCoa = 0;
        foreach ($dataDB->ap_detail2s as $dataDetail){
            $vueData = [
                "coaId"     => $dataDetail->coa_id,
                "coaCode"   => $dataDetail->coa_code,
                "debit"     => $dataDetail->debit,
                "credit"     => $dataDetail->kredit,
                "desc"      => $dataDetail->description,
            ];
            array_push($detailData2, $vueData);
            $diffDebitCredit += $dataDetail->debit;
            $grandTotalCoa += ($dataDetail->debit - $dataDetail->kredit);
        }

        $vueData = [
            'itemList' => $detailData,
            'itemList2' => $detailData2,
            'diffDebitCredit' => $diffDebitCredit,
            'grandTotalCoa' => $grandTotalCoa,
        ];

        $data = [
            'branches'  => $branches,
            'data'      => $dataDB,
            'vueData'      => $vueData,
        ];

    //    dd($vueData);

        return view('admin.ap.edit')->with($data);
    }

    public function update(Request $request){
        try{
//            dd($request);
            $suppInvIds = $request->input('suppInvIds');
            $suppInvCodes = $request->input('suppInvCodes');
            $total = $request->input('total');
            $descs = $request->input('descs');

            if (count($suppInvIds) > 0) {
                if (count($suppInvIds) != count($total) && count($suppInvIds) != count($suppInvCodes)) {
                    return back()->withErrors("Detail Barang tidak lengkap")->withInput($request->all());
                }
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
            $data = ApHeader::where('id', $request->input('id'))->first();
            if($data == null){
                Session::flash('error', 'Data tidak ditemukan!');
                return back()->withInput($request->all());
            }
            $data->branch_id = $user->branch_id;
            $data->date = Carbon::parse($request->input('date'))->toDateTimeString();
            $data->supplier_id = $request->input('supplier_id');
            $data->description = $request->input('description') ?? "";
            $data->grandtotal = Utilities::toFloat($request->input('grandtotal'));
            $data->updated_by = $user->id;
            $data->save();

            $oldData = ApDetail1::where('header_id', $request->input('id'))->get();
            if(count($oldData) > 0){
                foreach($oldData as $oldDetaildata){
                    $oldDetaildata->delete();
                }
            }
            $oldData2 = ApDetail2::where('header_id', $request->input('id'))->get();
            if(count($oldData2) > 0){
                foreach($oldData2 as $oldDetaildata){
                    $oldDetaildata->delete();
                }
            }
            $ct = 0;
            foreach ($suppInvIds as $itemId){
                $dataDetail = new ApDetail1();
                $dataDetail->header_code = $data->code;
                $dataDetail->header_id = $data->id;
                $dataDetail->supplier_invoice_id = $itemId;
                $dataDetail->supplier_invoice_code = $suppInvCodes[$ct] ?? "";
                $dataDetail->debit = $total[$ct]!= null ? Utilities::toFloat($total[$ct]) : 1;
                $dataDetail->description = $descs[$ct] ?? "";
                $dataDetail->save();

                $ct++;
            }
            if (count($coaIds) > 0) {

                $ct = 0;
                foreach ($coaIds as $coaId){
                    $dataDetail = new ApDetail2();
                    $dataDetail->header_code = $data->code;
                    $dataDetail->header_id = $data->id;
                    $dataDetail->coa_id = $coaId;
                    $coa = Coa::find($coaId);
                    $dataDetail->coa_code = $coa ? $coa->code. " - ". $coa->name : "";
                    $dataDetail->debit = $debits[$ct] != null ? Utilities::toFloat($debits[$ct]) : 0;
                    $dataDetail->kredit = $credits[$ct] != null ? Utilities::toFloat($credits[$ct]) : 0;
                    $dataDetail->description = $descCoas[$ct] ?? "";
                    $dataDetail->save();

                    $ct++;
                }
            }

            Session::flash('success', 'Sukses mengubah data Acc Payment!');
            return redirect()->route('admin.ap.index');
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

        $dataDB = ApHeader::where('id', $id)->first();
        if($dataDB == null){
            Session::flash('error', 'Data tidak ditemukan!');
            return back();
        }
        $detailData = [];
        $detailData2 = [];
        foreach ($dataDB->ap_detail1s as $dataDetail){
            $vueData = [
                "suppInvId"    => $dataDetail->supplier_invoice_id,
                "suppInvCode"    => $dataDetail->supplier_invoice_code,
                "total"     => $dataDetail->debit,
                "desc"      => $dataDetail->description,
            ];
            array_push($detailData, $vueData);
        }

        $diffDebitCredit = 0;
        $grandTotalCoa = 0;
        foreach ($dataDB->ap_detail2s as $dataDetail){
            $vueData = [
                "coaId"     => $dataDetail->coa_id,
                "coaCode"   => $dataDetail->coa_code,
                "debit"     => $dataDetail->debit,
                "credit"     => $dataDetail->kredit,
                "desc"      => $dataDetail->description,
            ];
            array_push($detailData2, $vueData);
            $diffDebitCredit += $dataDetail->debit;
            $grandTotalCoa += ($dataDetail->debit - $dataDetail->kredit);

        }

        $vueData = [
            'itemList' => $detailData,
            'itemList2' => $detailData2,
            'diffDebitCredit' => $diffDebitCredit,
            'grandTotalCoa' => $grandTotalCoa,
        ];

        $data = [
            'branches'  => $branches,
            'data'      => $dataDB,
            'vueData'      => $vueData,
            'itemList'      => $detailData,
            'itemList2'      => $detailData2,
        ];
        $type = trim($request->type);
        if(!empty($type)){
            return view('admin.ap.print')->with($data);
        }


        return view('admin.ap.show')->with($data);
    }

    public function delete(Request $request){
        try{
            if(!$request->filled('password')){
                return Response::json('error', 200);
            }

            //check password first
            $config = Configuration::where('key', 'spv_password')->first();
            if(!Hash::check($request->input('password'), $config->value)){
                return Response::json('error', 200);
            }

            $warehouse = ApHeader::where('id', $request->input('id'))->first();
            if($warehouse == null){
                Session::flash('error', 'Data tidak ditemukan!');
                return back();
            }
            $warehouse->status_id = 2;
            $warehouse->save();

            return Response::json('Sukses', 200);
        }
        catch (\Exception $ex){
            return Response::json('error', 200);
        }
    }

    public function approve(Request $request){
        try{
            //check password first
            if(!$request->filled('password')){
                Session::flash('error', 'Harus mengisi Password terlebih dahulu ' . $ex);
                return back();
            }

            $config = Configuration::where('key', 'spv_password')->first();
            if(!Hash::check($request->input('password'), $config->value)){
                Session::flash('error', 'Password yang Diisi Salah ');
                return back();
            }

//            dd($request);
            $ids = $request->input('ids');
            $user = Auth::guard('admin')->user();
            if(empty($ids)){
                Session::flash('error', 'Tidak Ada Data yang Dipilih');
                return redirect()->route('admin.ap.index');
            }
            foreach ($ids as $id) {
                $dataDB = ApHeader::where('id', $id)->first();
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
                $jurnal->transaction_type = 'ACC_PAYMENT';
                $jurnal->transaction_date = $dataDB->date;
                $jurnal->notes = "Jurnal Acc Payment ".$dataDB->code;
                $jurnal->created_by = $user->id;
                $jurnal->branch_id = $user->branch_id;
                $jurnal->reference_id = $dataDB->id;
                $jurnal->reference_code = $dataDB->code;
                $jurnal->status_id = 1;
                $jurnal->save();
                // Journal Header Process END

                foreach ($dataDB->ap_detail1s as $dataDetail1){
                    $suppInvCode = $dataDetail1->supplier_invoice_code;
                    $suppInvDetails = SupplierInvoiceDetail1::where('header_code', $suppInvCode)->get();

                    $totalHutangBerelasi = 0;
                    $totalHutangUsaha = 0;

                    foreach ($suppInvDetails as $suppInvDetail){
                        $grHeader = GoodReceiveHeader::with(['good_receive_details'])
                            ->where('code', '=', $suppInvDetail->gr_code)
                            ->first();

                        // Check for PO discount and PPN
                        $poHeader = PurchaseOrderHeader::find($grHeader->po_id);
                        $poDiscount = $poHeader->disc1_value + $poHeader->disc2_value;
                        $ppnValue = $poHeader->ppn_value;

                        foreach ($grHeader->good_receive_details as $detail) {
                            $itemDB = Item::where('id', $detail->item_id)->first();
                            if(!empty($itemDB->category_2_id)){
                                if($itemDB->category_2_id == 14){
                                    $totalHutangBerelasi += $detail->total;

                                    Log::channel('ap_debug')->info('ap_code = '. $dataDB->code.
                                        ' supplier_invoice_code = '. $suppInvDetail->header_code.
                                        ' good_receive_code = '. $detail->header_code.
                                        ' item_code = '. $itemDB->code);
                                    Log::channel('ap_debug')->info('totalHutangBerelasi = '. $totalHutangUsaha);
                                }
                                else{
                                    $totalHutangUsaha += $detail->total;

                                    Log::channel('ap_debug')->info('ap_code = '. $dataDB->code.
                                        ' supplier_invoice_code = '. $suppInvDetail->header_code.
                                        ' good_receive_code = '. $detail->header_code.
                                        ' item_code = '. $itemDB->code);
                                    Log::channel('ap_debug')->info('totalHutangUsaha = '. $totalHutangUsaha);
                                }
                            }
                        }
                    }

                    if($poDiscount > 0){
                        if($totalHutangUsaha > 0){
                            if($totalHutangUsaha - $poDiscount <= 0){
                                $totalHutangBerelasi -= $poDiscount;
                            }
                            else{
                                $totalHutangUsaha -= $poDiscount;
                            }
                        }
                        else{
                            $totalHutangBerelasi -= $poDiscount;
                        }
                    }

                    if($ppnValue > 0){
                        if($totalHutangUsaha > 0){
                            $totalHutangUsaha += $ppnValue;
                            $totalHutangUsaha = round($totalHutangUsaha);
                        }
                        else{
                            $totalHutangBerelasi += $ppnValue;
                            $totalHutangBerelasi = round($totalHutangBerelasi);
                        }
                    }

                    // Journal Detail Process hutang START
                    if($totalHutangBerelasi != 0){
                        $coa = Coa::where('branch_id', $user->branch_id)
                            ->where('name', 'Hutang - Pihak Berelasi (Pendek)')
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
                            $jDt->debit = $totalHutangBerelasi;
                            $jDt->credit = 0;
                            $jDt->notes = 'Acc Payment : Hutang - Pihak Berelasi ' . $dataDB->code;
                            $jDt->reference_code = $dataDB->code;
//                            $jDt->transaction_code = AutoNumberGenerator::generateAutonumberTrx('JN');
                            $jDt->transaction_code = $jurnal->transaction_code;
                            $jDt->transaction_type = 'ACC_PAYMENT';
                            $jDt->original_amount = $totalHutangBerelasi;
                            $jDt->save();
                        }
                    }
                    if($totalHutangUsaha != 0){
                        $coa = Coa::where('branch_id', $user->branch_id)
                            ->where('name', 'Hutang Usaha')
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
                            $jDt->debit = $totalHutangUsaha;
                            $jDt->credit = 0;
                            $jDt->notes = 'Acc Payment : Hutang Usaha ' . $dataDB->code;
                            $jDt->reference_code = $dataDB->code;
//                            $jDt->transaction_code = AutoNumberGenerator::generateAutonumberTrx('JN');
                            $jDt->transaction_code = $jurnal->transaction_code;
                            $jDt->transaction_type = 'ACC_PAYMENT';
                            $jDt->original_amount = $totalHutangUsaha;
                            $jDt->save();
                        }
                    }
                    // Journal Detail Process hutang END
                }

                foreach ($dataDB->ap_detail2s as $dataDetail2){
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
                        $jDt->original_amount = $dataDetail2->debit != 0 ? $dataDetail2->debit : $dataDetail2->kredit;
                        $jDt->debit = $dataDetail2->debit;
                        $jDt->credit = $dataDetail2->kredit;
                        $jDt->notes = 'Acc Payment ' . $dataDB->code;
                        $jDt->reference_code = $dataDB->code;
//                            $jDt->transaction_code = AutoNumberGenerator::generateAutonumberTrx('JN');
                        $jDt->transaction_code = $jurnal->transaction_code;
                        $jDt->transaction_type = 'ACC_PAYMENT';
                        $jDt->save();
                    }
                }
            }

            Session::flash('success', 'Sukses approve data Acc Payment!');
            return redirect()->route('admin.ap.index');
        }
        catch (\Exception $ex){
            Session::flash('error', 'Maaf kesalahan terjadi!' . $ex);
            return back();
        }
    }

    public function filter($type){
        $startDate = Carbon::now('Asia/Jakarta')->startOfMonth();
        $endDate = Carbon::now('Asia/Jakarta')->endOfMonth();

        $data = [
            'startDate' => $startDate->format('d-m-Y'),
            'endDate'   => $endDate->format('d-m-Y'),
            'type'   => $type,
        ];

        return view('admin.ap.report_ap_filter')->with($data);
    }
    public function reportData(Request $request){
        try{
            $rules = array(
                'start_date'=> 'required',
//                'type '=> 'required',
            );

            $message = array(
                'start_date.required'   => 'Tanggal Mulai Wajib dipilih!',
            );

            $validator = Validator::make($request->all(), $rules, $message);

            if($validator->fails()){
                return Response::json(['error' => $validator->errors()->all()]);
            }
            //get data
            $startDate = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
            $endDate = Carbon::createFromFormat('!d-m-Y', $request->input('end_date'), 'Asia/Jakarta');
            $endDate->addDays(1)->addMinutes(-1);

            $result = [];
            $type = $request->input('type');

            $headers = SupplierInvoiceHeader::where('status_id', '!=' , 2)
                ->whereBetween('created_at', [$startDate->toDateTimeString(), $endDate->toDateTimeString()])
                ->orderby('supplier_id')->orderby('code')->get();
            $currentSupp = 0;
            $nResult = [];
            $totalPaidAmount = 0;
            $totalGrandTotal = 0;
            $no = 1;
            switch($request->input('type')) {
                case 'ap_card' :
                    foreach ($headers as $header){
                        $trxDate = Carbon::parse($header->created_at);

                        $apDetails = DB::table('ap_detail1s')
                            ->join('ap_headers', 'ap_headers.id', '=', 'ap_detail1s.header_id')
                            ->select(
                                'ap_detail1s.header_id as id',
                                'ap_detail1s.header_code as code',
                                'ap_detail1s.debit as debit',
                                'ap_headers.date as paid_date',
                            )
                            ->where('ap_headers.status_id', 13)
                            ->where('ap_detail1s.supplier_invoice_id', $header->id)
                            ->get();

                        $currentBalance = $header->grandtotal;
                        foreach($apDetails as $apDetail){
                            $balance =  $currentBalance - $apDetail->debit;
                            $nTrx = [
                                'no'                => $no,
                                'code'              => $header->code,
                                'date'              => $trxDate->locale('id_ID')->toDateTimeString(),
                                'supplier_name'     => $header->supplier->name,
                                'paid_no'           => $apDetail->code,
                                'paid_date'         => $apDetail->paid_date->locale('id_ID')->toDateTimeString(),
                                'grand_total'       => $header->grandtotal,
                                'paid_amount'       => $apDetail->debit,
                                'balance'           => $balance,
                                'created_by'        => $header->status->display,
                            ];
                            if($currentSupp != $header->supplier_id){
                                $nResult = [
                                    'supplier_code'     => $header->supplier->code,
                                    'supplier_name'     => $header->supplier->name,
                                    'transactions'      => [],
                                    'subtotal_grand_total'  => $header->grandtotal,
                                    'subtotal_paid_amount'  => $apDetail->debit
                                ];
                                $nResult['transactions'][] = $nTrx;
                                $currentSupp = $header->supplier_id;
                                $no = 1;
                            }
                            else{

                                if(!in_array($nTrx, $nResult['transactions'])){
                                    $nResult['transactions'][] = $nTrx;
                                    $nResult['subtotal_grand_total'] += $header->grandtotal;
                                    $nResult['subtotal_paid_amount'] += $apDetail->debit;
                                }
                            }
                            $result[] = $nResult;
                            $no++;

                            $totalGrandTotal += $header->grandtotal;
                            $totalPaidAmount += $apDetail->debit;
                        }

                    }
                    break;
                case 'ap_invoice_list' :
                    foreach ($headers as $header){
                        $trxDate = Carbon::parse($header->created_at);

                        $apDetails = DB::table('ap_detail1s')
                            ->join('ap_headers', 'ap_headers.id', '=', 'ap_detail1s.header_id')
                            ->select(
                                'ap_detail1s.header_id as id',
                                'ap_detail1s.header_code as code',
                                'ap_detail1s.debit as debit',
                                'ap_headers.date as paid_date',
                            )
                            ->where('ap_headers.status_id', 13)
                            ->where('ap_detail1s.supplier_invoice_id', $header->id)
                            ->first();

                        $nTrx = [
                            'no'                => $no,
                            'code'              => $header->code,
                            'date'              => $trxDate->locale('id_ID')->toDateTimeString(),
                            'supplier_name'     => $header->supplier->name,
                            'grand_total'       => $header->grandtotal,
                            'paid_amount'       => $apDetails->debit,
                            'created_by'        => $header->status->display,
                        ];
                        if($currentSupp != $header->supplier_id){
                            $nResult = [
                                'supplier_code'     => $header->supplier->code,
                                'supplier_name'     => $header->supplier->name,
                                'transactions'      => [],
                                'subtotal_grand_total'  => $header->grandtotal,
                                'subtotal_paid_amount'  => $apDetails->debit
                            ];
                            $nResult['transactions'][] = $nTrx;
                            $currentSupp = $header->supplier_id;
                            $no = 1;
                        }
                        else{

                            if(!in_array($nTrx, $nResult['transactions'])){
                                $nResult['transactions'][] = $nTrx;
                                $nResult['subtotal_grand_total'] += $header->grandtotal;
                                $nResult['subtotal_paid_amount'] += $apDetails->debit;
                            }
                        }
                        $result[] = $nResult;
                        $no++;

                        $totalGrandTotal += $header->grandtotal;
                        $totalPaidAmount += $apDetails->debit;
                    }
                    break;
                case 'ap_aging' :
                    $no = 1;
                    foreach ($headers as $header){
                        $trxDate = Carbon::parse($header->created_at);

                        $apDetailTotals = DB::table('ap_headers')
                            ->join('ap_detail1s', 'ap_headers.id', '=', 'ap_detail1s.header_id')
                            ->where('ap_headers.status_id', 13)
                            ->where('ap_detail1s.supplier_invoice_id', $header->id)
                            ->sum('ap_detail1s.debit');

                        $apDetails = DB::table('ap_headers')
                            ->join('ap_detail1s', 'ap_headers.id', '=', 'ap_detail1s.header_id')
                            ->select(
                                'ap_headers.date as paid_date',
                            )
                            ->where('ap_headers.status_id', 13)
                            ->where('ap_detail1s.supplier_invoice_id', $header->id)
                            ->first();
                        $invoiced = "x";
                        $t30 = 0;
                        $t60 = 0;
                        $t90 = 0;
                        $t120 = 0;
                        $t120_more = 0;
                        if(!empty($apDetails)){
                            $now = Carbon::now();
                            $invoiced = "v";
                            $diff = Carbon::parse($apDetails->paid_date)->diffInDays($now);
                            if($diff < 30){
                                $t30 = $apDetailTotals;
                            }
                            else if($diff >= 30 && $diff < 60){
                                $t60 = $apDetailTotals;
                            }
                            else if($diff >= 60 && $diff < 90){
                                $t90 = $apDetailTotals;
                            }
                            else if($diff >= 90 && $diff < 120){
                                $t120 = $apDetailTotals;
                            }
                            else{
                                $t120_more = $apDetailTotals;
                            }
                        }

                        $nTrx = [
                            'no'                => $no,
                            'code'              => $header->code,
                            'date'              => $trxDate->locale('id_ID')->toDateTimeString(),
                            'supplier_name'     => $header->supplier->name,
                            'grand_total'       => $header->grandtotal,
                            'paid_amount'       => $apDetailTotals,
                            'invoiced'          => $invoiced,
                            't30'               => $t30,
                            't60'               => $t60,
                            't90'               => $t90,
                            't120'              => $t120,
                            't120_more'         => $t120_more,
                            'created_by'        => $header->status->display,
                        ];
                        if($currentSupp != $header->supplier_id){
                            $nResult = [
                                'supplier_code'        => $header->supplier->code,
                                'supplier_name'        => $header->supplier->name,
                                'transactions'  => [],
                                'subtotal_grand_total'  => $header->grandtotal,
                                'subtotal_paid_amount'  => $apDetailTotals
                            ];
                            $nResult['transactions'][] = $nTrx;
                            $currentSupp = $header->supplier_id;
                            $no = 1;
                        }
                        else{

                            if(!in_array($nTrx, $nResult['transactions'])){
                                $nResult['transactions'][] = $nTrx;
                                $nResult['subtotal_grand_total'] += $header->grandtotal;
                                $nResult['subtotal_paid_amount'] += $apDetailTotals;
                            }
                        }
                        $result[] = $nResult;
                        $no++;

                        $totalGrandTotal += $header->grandtotal;
                        $totalPaidAmount += $apDetailTotals;
                    }
                    break;
            }
            $user = Auth::guard('admin')->user();
            $branch = Branch::where('id', $user->branch_id)->first();

            $data = [
                'details' => $result,
                'type' => $type,
                'branch' => $branch,
                'start_date' => $startDate->locale('id_ID')->toDateTimeString(),
                'end_date' => $endDate->locale('id_ID')->toDateTimeString(),
                'grand_total_sum' => number_format($totalGrandTotal, '0', ',', '.'),
                'paid_amount_sum' => number_format($totalPaidAmount, '0', ',', '.'),
            ];
            return $data;
        }
        catch (\Exception $ex){
            Session::flash('error', $ex);
            return back();
        }
    }

    public function report(Request $request){
        try{
            $rules = array(
                'start_date'=> 'required',
            //    'type '=> 'required',
            );

            $message = array(
                'start_date.required'   => 'Tanggal Mulai Wajib dipilih!',
            );

            $validator = Validator::make($request->all(), $rules, $message);

            if($validator->fails()){
                return Response::json(['error' => $validator->errors()->all()]);
            }
            //get data
            $startDate = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
            $endDate = Carbon::createFromFormat('!d-m-Y', $request->input('end_date'), 'Asia/Jakarta');
            $endDate->addDays(1)->addMinutes(-1);

            $result = [];
            $type = $request->input('type');

            $headers = SupplierInvoiceHeader::where('status_id', '!=' , 2)
                ->whereBetween('created_at', [$startDate->toDateTimeString(), $endDate->toDateTimeString()])
                ->orderby('supplier_id')->orderby('code')->get();
            $currentSupp = 0;
            $nResult = [];
            $totalPaidAmount = 0;
            $totalGrandTotal = 0;
            $no = 1;
            switch($request->input('type')) {
                case 'ap_card' :
                    foreach ($headers as $header){
                        $trxDate = Carbon::parse($header->created_at);

                        $apDetails = DB::table('ap_detail1s')
                            ->join('ap_headers', 'ap_headers.id', '=', 'ap_detail1s.header_id')
                            ->select(
                                'ap_detail1s.header_id as id',
                                'ap_detail1s.header_code as code',
                                'ap_detail1s.debit as debit',
                                'ap_headers.date as paid_date',
                            )
                            ->where('ap_headers.status_id', 13)
                            ->where('ap_detail1s.supplier_invoice_id', $header->id)
                            ->get();

                        $currentBalance = $header->grandtotal;
                        foreach($apDetails as $apDetail){
                            $balance =  $currentBalance - $apDetail->debit;
                            $nTrx = [
                                'no'                => $no,
                                'code'              => $header->code,
                                'date'              => $trxDate->locale('id_ID')->toDateTimeString(),
                                'supplier_name'     => $header->supplier->name,
                                'paid_no'           => $apDetail->code,
                                'paid_date'         => $apDetail->paid_date->locale('id_ID')->toDateTimeString(),
                                'grand_total'       => number_format($header->grandtotal, '0', ',', '.'),
                                'paid_amount'       => number_format($apDetail->debit, '0', ',', '.'),
                                'balance'           => number_format($balance, '0', ',', '.'),
                                'created_by'        => $header->status->display,
                            ];
                            if($currentSupp != $header->supplier_id){
                                $nResult = [
                                    'supplier_code'     => $header->supplier->code,
                                    'supplier_name'     => $header->supplier->name,
                                    'transactions'      => [],
                                    'subtotal_grand_total'  => $header->grandtotal,
                                    'subtotal_paid_amount'  => $apDetail->debit
                                ];
                                $nResult['transactions'][] = $nTrx;
                                $currentSupp = $header->supplier_id;
                                $no = 1;
                            }
                            else{

                                if(!in_array($nTrx, $nResult['transactions'])){
                                    $nResult['transactions'][] = $nTrx;
                                    $nResult['subtotal_grand_total'] += $header->grandtotal;
                                    $nResult['subtotal_paid_amount'] += $apDetail->debit;
                                }
                            }
                            $result[] = $nResult;
                            $no++;

                            $totalGrandTotal += $header->grandtotal;
                            $totalPaidAmount += $apDetail->debit;
                        }

                    }
                    break;
                case 'ap_invoice_list' :
                    foreach ($headers as $header){
                        $trxDate = Carbon::parse($header->created_at);

                        $apDetails = DB::table('ap_detail1s')
                            ->join('ap_headers', 'ap_headers.id', '=', 'ap_detail1s.header_id')
                            ->select(
                                'ap_detail1s.header_id as id',
                                'ap_detail1s.header_code as code',
                                'ap_detail1s.debit as debit',
                                'ap_headers.date as paid_date',
                            )
                            ->where('ap_headers.status_id', 13)
                            ->where('ap_detail1s.supplier_invoice_id', $header->id)
                            ->first();

                        $nTrx = [
                            'no'                => $no,
                            'code'              => $header->code,
                            'date'              => $trxDate->locale('id_ID')->toDateTimeString(),
                            'supplier_name'     => $header->supplier->name,
                            'grand_total'       => number_format($header->grandtotal, '0', ',', '.'),
                            'paid_amount'       => number_format($apDetails->debit, '0', ',', '.'),
                            'created_by'        => $header->status->display,
                        ];
                        if($currentSupp != $header->supplier_id){
                            $nResult = [
                                'supplier_code'     => $header->supplier->code,
                                'supplier_name'     => $header->supplier->name,
                                'transactions'      => [],
                                'subtotal_grand_total'  => $header->grandtotal,
                                'subtotal_paid_amount'  => $apDetails->debit
                            ];
                            $nResult['transactions'][] = $nTrx;
                            $currentSupp = $header->supplier_id;
                            $no = 1;
                        }
                        else{

                            if(!in_array($nTrx, $nResult['transactions'])){
                                $nResult['transactions'][] = $nTrx;
                                $nResult['subtotal_grand_total'] += $header->grandtotal;
                                $nResult['subtotal_paid_amount'] += $apDetails->debit;
                            }
                        }
                        $result[] = $nResult;
                        $no++;

                        $totalGrandTotal += $header->grandtotal;
                        $totalPaidAmount += $apDetails->debit;
                    }
                    break;
                case 'ap_aging' :
                    $no = 1;
                    foreach ($headers as $header){
                        $trxDate = Carbon::parse($header->created_at);

                        $apDetailTotals = DB::table('ap_headers')
                            ->join('ap_detail1s', 'ap_headers.id', '=', 'ap_detail1s.header_id')
                            ->where('ap_headers.status_id', 13)
                            ->where('ap_detail1s.supplier_invoice_id', $header->id)
                            ->sum('ap_detail1s.debit');

                        $apDetails = DB::table('ap_headers')
                            ->join('ap_detail1s', 'ap_headers.id', '=', 'ap_detail1s.header_id')
                            ->select(
                                'ap_headers.date as paid_date',
                            )
                            ->where('ap_headers.status_id', 13)
                            ->where('ap_detail1s.supplier_invoice_id', $header->id)
                            ->first();
                        $invoiced = "x";
                        $t30 = 0;
                        $t60 = 0;
                        $t90 = 0;
                        $t120 = 0;
                        $t120_more = 0;
                        if(!empty($apDetails)){
                            $now = Carbon::now();
                            $invoiced = "v";
                            $diff = Carbon::parse($apDetails->paid_date)->diffInDays($now);
                            if($diff < 30){
                                $t30 = $apDetailTotals;
                            }
                            else if($diff >= 30 && $diff < 60){
                                $t60 = $apDetailTotals;
                            }
                            else if($diff >= 60 && $diff < 90){
                                $t90 = $apDetailTotals;
                            }
                            else if($diff >= 90 && $diff < 120){
                                $t120 = $apDetailTotals;
                            }
                            else{
                                $t120_more = $apDetailTotals;
                            }
                        }

                        $nTrx = [
                            'no'                => $no,
                            'code'              => $header->code,
                            'date'              => $trxDate->locale('id_ID')->toDateTimeString(),
                            'supplier_name'     => $header->supplier->name,
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
                        if($currentSupp != $header->supplier_id){
                            $nResult = [
                                'supplier_code'        => $header->supplier->code,
                                'supplier_name'        => $header->supplier->name,
                                'transactions'  => [],
                                'subtotal_grand_total'  => $header->grandtotal,
                                'subtotal_paid_amount'  => $apDetailTotals
                            ];
                            $nResult['transactions'][] = $nTrx;
                            $currentSupp = $header->supplier_id;
                            $no = 1;
                        }
                        else{

                            if(!in_array($nTrx, $nResult['transactions'])){
                                $nResult['transactions'][] = $nTrx;
                                $nResult['subtotal_grand_total'] += $header->grandtotal;
                                $nResult['subtotal_paid_amount'] += $apDetailTotals;
                            }
                        }
                        $result[] = $nResult;
                        $no++;

                        $totalGrandTotal += $header->grandtotal;
                        $totalPaidAmount += $apDetailTotals;
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
            ];
            switch($request->input('type')) {
                case 'ap_card' :
                    return view('admin.ap.report-card')->with($data);
                    break;
                case 'ap_aging' :
                    return view('admin.ap.report-aging')->with($data);
                    break;
                default:
                    return view('admin.ap.report-invoice-list')->with($data);
                    break;
            }
        }
        catch (\Exception $ex){
            Session::flash('error', $ex);
            return back();
        }
    }
}
