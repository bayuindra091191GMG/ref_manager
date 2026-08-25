<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Libs\AutoNumberGenerator;
use App\Libs\Utilities;
use App\Models\AdminUser;
use App\Models\BankCashinDetail;
use App\Models\BankCashinHeader;
use App\Models\Branch;
use App\Models\Coa;
use App\Models\Configuration;
use App\Models\JournalDetail;
use App\Models\JournalHeader;
use App\Transformer\BankCashInTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\DataTables;

class BankCashInController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function select(Request $request){

        $term = trim($request->search);

        $searchDatas = DB::table('bank_cashin_headers')
            ->select('id','code','description')
            ->where(function($q) use ($term){
                $q->Where('code', 'LIKE', '%'. $term. '%')
                    ->orWhere('description', 'LIKE', '%'. $term. '%');
            })
            ->where('status_id', 13)
            ->orderByDesc('id')
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
        $start = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta')->startOfDay();
        $end = Carbon::createFromFormat('!d-m-Y', $request->input('end_date'), 'Asia/Jakarta')->endOfDay();

        $datas = BankCashinHeader::with(['status'])
            ->whereBetween('receive_date', array($start->toDateTimeString(), $end->toDateTimeString()))
            ->where('branch_id', $user->branch_id)
            ->orderByDesc('id');

        return DataTables::of($datas)
            ->setTransformer(new BankCashInTransformer)
            ->make(true);
    }

    public function index(Request $request){
        $filterStartDate = Carbon::today()->startOfMonth()->format('d-m-Y');
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
        return view('admin.bank_cash_in.index')->with($data);
    }

    public function create(){
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

        return view('admin.bank_cash_in.create')->with($data);
    }

    public function store(Request $request){
        try{
//            dd($request);
            $coaIds = $request->input('coaIds') ?? [];
            $debits = $request->input('debits') ?? [];
            $credits = $request->input('credits') ?? [];
            $descs = $request->input('desc') ?? [];

            if(count($coaIds) > 0){
                if(count($coaIds) != count($debits) || count($coaIds) != count($credits) || count($coaIds) != count($descs)){
                    return back()->withErrors("Detail COA tidak lengkap")->withInput($request->all());
                }
            } else {
                return back()->withErrors("Detail COA tidak lengkap")->withInput($request->all());
            }

            $totalDebit = 0;
            $totalCredit = 0;
            foreach ($coaIds as $ct => $itemId) {
                $totalDebit += Utilities::toFloat($debits[$ct] ?? 0);
                $totalCredit += Utilities::toFloat($credits[$ct] ?? 0);
            }

            $user = Auth::guard('admin')->user();
            $data = new BankCashinHeader();
            $data->code = AutoNumberGenerator::generateAutonumberTrx('CI');
            $data->branch_id = $user->branch_id;
            $data->receive_date = Carbon::parse($request->input('receive_date'))->toDateTimeString();
//            $data->employee_id = $request->input('employee_id');
//            $data->employee_name = Employee::find($request->input('employee_id'))->name;
//            $data->department_id = $request->input('department_id');
//            $data->department_name = Department::find($request->input('department_id'))->name;
            $data->coa_bank_id = $request->input('coa_bank_id');
            $data->coa_bank_code = Coa::find($request->input('coa_bank_id'))->code;
            $data->coa_bank_name = Coa::find($request->input('coa_bank_id'))->name;
            $data->total_price = $totalCredit - $totalDebit;
            $data->description = $request->input('description') ?? "";
            $data->status_id = 1;
            $data->created_by = $user->id;
            $data->save();

            $ct = 0;
            foreach ($coaIds as $itemId){
                $dataDetail = new BankCashinDetail();
                $dataDetail->header_code = $data->code;
                $dataDetail->header_id = $data->id;
                $debit = Utilities::toFloat($debits[$ct] ?? 0);
                $credit = Utilities::toFloat($credits[$ct] ?? 0);

                $dataDetail->debit_kredit = $debit != 0 ? 'D' : 'K';
                $dataDetail->coa_id = $itemId;
                $dataDetail->coa_code = Coa::find($itemId)->code;
                $dataDetail->coa_name = Coa::find($itemId)->name;
                $dataDetail->amount = $debit != 0 ? $debit : $credit;
                $dataDetail->description = $descs[$ct] ?? "";
                $dataDetail->created_by = $user->id;
                $dataDetail->save();

                $ct++;
            }

            Session::flash('success', 'Sukses membuat data Bank Cash In!');
            return redirect()->route('admin.bank_cash_in.index');
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

        $dataDB = BankCashinHeader::where('id', $id)->first();
        if($dataDB == null){
            Session::flash('error', 'Data tidak ditemukan!');
            return back();
        }
        $detailData = [];
        $totalD = 0;
        $totalK = 0;

        foreach ($dataDB->bank_cashin_details as $dataDetail){
            $debitKredit = $dataDetail->debit_kredit ?? 'K';
            $totalD += $debitKredit == 'D' ? $dataDetail->amount : 0;
            $totalK += $debitKredit == 'K' ? $dataDetail->amount : 0;
            $vueData = [
                "id"        => $dataDetail->id,
                "coaId"    => $dataDetail->coa_id,
                "amount"       => $dataDetail->amount,
                "debitKredit" => $debitKredit,
                "debit" => $debitKredit == 'D' ? $dataDetail->amount : 0,
                "credit" => $debitKredit == 'K' ? $dataDetail->amount : 0,
                "desc"       => $dataDetail->description,
            ];
            array_push($detailData, $vueData);
        }

        $vueData = [
            'itemList' => $detailData
        ];

        $data = [
            'branches'  => $branches,
            'data'      => $dataDB,
            'vueData'      => $vueData,
            'totalD' => $totalD,
            'totalK' => $totalK,
        ];

        return view('admin.bank_cash_in.edit')->with($data);
    }

    public function update(Request $request){
        try{
            $coaIds = $request->input('coaIds') ?? [];
            $debits = $request->input('debits') ?? [];
            $credits = $request->input('credits') ?? [];
            $descs = $request->input('desc') ?? [];

            if(count($coaIds) > 0){
                if(count($coaIds) != count($debits) || count($coaIds) != count($credits) || count($coaIds) != count($descs)){
                    return back()->withErrors("Detail COA tidak lengkap")->withInput($request->all());
                }
            } else {
                return back()->withErrors("Detail COA tidak lengkap")->withInput($request->all());
            }

            $totalDebit = 0;
            $totalCredit = 0;
            foreach ($coaIds as $ct => $itemId) {
                $totalDebit += Utilities::toFloat($debits[$ct] ?? 0);
                $totalCredit += Utilities::toFloat($credits[$ct] ?? 0);
            }

            $user = Auth::guard('admin')->user();
            $data = BankCashinHeader::where('id', $request->input('id'))->first();
            if($data == null){
                Session::flash('error', 'Data tidak ditemukan!');
                return back()->withInput($request->all());
            }
            $data->branch_id = $user->branch_id;
            $data->receive_date = Carbon::parse($request->input('receive_date'))->toDateTimeString();
//            $data->employee_id = $request->input('employee_id');
//            $data->employee_name = Employee::find($request->input('employee_id'))->name;
//            $data->department_id = $request->input('department_id');
//            $data->department_name = Department::find($request->input('department_id'))->name;
            $data->coa_bank_id = $request->input('coa_bank_id');
            $data->coa_bank_code = Coa::find($request->input('coa_bank_id'))->code;
            $data->coa_bank_name = Coa::find($request->input('coa_bank_id'))->name;
            $data->total_price = $totalCredit - $totalDebit;
            $data->description = $request->input('description') ?? "";
            $data->updated_by = $user->id;
            $data->save();

            $ct = 0;
            $oldData = BankCashinDetail::where('header_id', $request->input('id'))->get();
            if(count($oldData) > 0){
                foreach($oldData as $oldDetaildata){
                    $oldDetaildata->delete();
                }
            }
            foreach ($coaIds as $itemId){
                $dataDetail = new BankCashinDetail();
                $dataDetail->header_code = $data->code;
                $dataDetail->header_id = $data->id;
                $debit = Utilities::toFloat($debits[$ct] ?? 0);
                $credit = Utilities::toFloat($credits[$ct] ?? 0);

                $dataDetail->debit_kredit = $debit != 0 ? 'D' : 'K';
                $dataDetail->coa_id = $itemId;
                $dataDetail->coa_code = Coa::find($itemId)->code;
                $dataDetail->coa_name = Coa::find($itemId)->name;
                $dataDetail->amount = $debit != 0 ? $debit : $credit;
                $dataDetail->description = $descs[$ct] ?? "";
                $dataDetail->created_by = $user->id;
                $dataDetail->save();

                $ct++;
            }

            Session::flash('success', 'Sukses mengubah data Bank Cash In!');
            return redirect()->route('admin.bank_cash_in.index');
        }
        catch (\Exception $ex){
            Session::flash('error', 'Maaf kesalahan terjadi!' . $ex);
            return back()->withInput($request->all());
        }
    }

    public function show($id, Request $request){
        $user = Auth::guard('admin')->user();
        $branches = Branch::find($user->branch_id);

        $dataDB = BankCashinHeader::where('id', $id)->first();
        if($dataDB == null){
            Session::flash('error', 'Data tidak ditemukan!');
            return back();
        }
        $detailData = [];
        $totalD = 0;
        $totalK = 0;

        foreach ($dataDB->bank_cashin_details as $dataDetail){
            $debitKredit = $dataDetail->debit_kredit ?? 'K';
            $totalD += $debitKredit == 'D' ? $dataDetail->amount : 0;
            $totalK += $debitKredit == 'K' ? $dataDetail->amount : 0;
            $vueData = [
                "coaId"    => $dataDetail->coa_id,
                "coaCode"    => $dataDetail->coa_code,
                "coaName"    => $dataDetail->coa_name,
                "amount"       => $dataDetail->amount,
                "debitKredit" => $debitKredit,
                "debit" => $debitKredit == 'D' ? $dataDetail->amount : 0,
                "credit" => $debitKredit == 'K' ? $dataDetail->amount : 0,
                "desc"       => $dataDetail->description,
            ];
            array_push($detailData, $vueData);
        }

        $vueData = [
            'itemList' => $detailData
        ];

        $createdBy = AdminUser::where('id', $dataDB->created_by)->first();
        $data = [
            'branches'  => $branches,
            'data'      => $dataDB,
            'vueData'      => $vueData,
            'itemList'      => $detailData,
            'createdBy' => $createdBy != null ? $createdBy->name : "-",
            'totalD' => $totalD,
            'totalK' => $totalK,
        ];
        $type = trim($request->type);
        if(!empty($type)){
            return view('admin.bank_cash_in.print')->with($data);
        }

        return view('admin.bank_cash_in.show')->with($data);
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

            $warehouse = BankCashinHeader::where('id', $request->input('id'))->first();
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
                Session::flash('error', 'Harus mengisi Password terlebih dahulu ' );
                return back();
            }

            $config = Configuration::where('key', 'spv_password')->first();
            if(!Hash::check($request->input('password'), $config->value)){
                Session::flash('error', 'Password yang Diisi Salah ');
                return back();
            }

//            dd($request);
            $admin = Auth::guard('admin')->user();
            $userData = AdminUser::find($admin->id);
            $ids = $request->input('ids');
            foreach ($ids as $id) {
                $dataDB = BankCashinHeader::where('id', $id)->first();
                if($dataDB == null){
                    Session::flash('error', 'Data tidak ditemukan!');
                    return back();
                }
                $dataDB->status_id = 13;
                $dataDB->updated_by = $admin->id;
                $dataDB->save();

                // Journal Process START
                try{
                    $journal = new JournalHeader();
                    $journal->transaction_code = AutoNumberGenerator::generateAutonumberTrx('JN');
                    $journal->transaction_type = 'CASH_IN';
                    $journal->transaction_date = $dataDB->receive_date;
                    $journal->notes = "CASH IN - ".$dataDB->code;
                    $journal->created_by = $userData->id;
                    $journal->branch_id = $userData->branch_id;
                    $journal->reference_id = $dataDB->id;
                    $journal->reference_code = $dataDB->code;
                    $journal->status_id = 13;
                    $journal->save();

                    $tmpCoa = Coa::where('id', $dataDB->coa_bank_id)->first();

                    $journalDetail = new JournalDetail();
                    $journalDetail->header_id = $journal->id;
                    $journalDetail->branch_id = $userData->branch_id;
                    $journalDetail->transaction_code = $journal->transaction_code;
                    $journalDetail->reference_id = $dataDB->id;
                    $journalDetail->reference_code = $dataDB->code;
                    $journalDetail->account_id = $tmpCoa->id;
                    $journalDetail->account_name = $tmpCoa->name;
                    $journalDetail->account_code = $tmpCoa->code;
                    $journalDetail->account_type_id = $tmpCoa->type_id;
                    $journalDetail->account_type_name = $tmpCoa->type;
                    $journalDetail->original_amount = $dataDB->total_price;
                    $journalDetail->debit = $dataDB->total_price;
                    $journalDetail->credit = 0;
                    $journalDetail->notes = $journal->notes;
                    $journalDetail->transaction_type = 'CASH_IN';
                    $journalDetail->save();

                    foreach($dataDB->bank_cashin_details as $detail){
                        $tmpCoa2 = Coa::where('id', $detail->coa_id)->first();
                        $journalDetail2 = new JournalDetail();
                        $journalDetail2->header_id = $journal->id;
                        $journalDetail2->branch_id = $userData->branch_id;
                        $journalDetail2->transaction_code = $journal->transaction_code;
                        $journalDetail2->reference_code = $dataDB->code;
                        $journalDetail2->account_id = $tmpCoa2->id;
                        $journalDetail2->account_code = $tmpCoa2->code;
                        $journalDetail2->account_name = $tmpCoa2->name;
                        $journalDetail2->account_type_id = $tmpCoa2->type_id;
                        $journalDetail2->account_type_name = $tmpCoa2->type;
                        $journalDetail2->original_amount = $detail->amount;
                        $journalDetail2->debit = $detail->debit_kredit == 'D' ? $detail->amount : 0;
                        $journalDetail2->credit = $detail->debit_kredit == 'D' ? 0 : $detail->amount;
                        $journalDetail2->notes = $detail->description;
                        $journalDetail2->transaction_type = 'CASH_IN';
                        $journalDetail2->save();
                    }
                }
                catch (\Exception $ex){
//                    dd($ex);
                    $dataDB->status_id = 1;
                    $dataDB->save();

                    Session::flash('error', 'Maaf kesalahan terjadi pada Jurnal! ' . $ex);
                    return redirect()->route('admin.bank_cash_in.index');
                }
                // Journal Process END
            }

            Session::flash('success', 'Sukses approve data Bank Cash In!');
            return redirect()->route('admin.bank_cash_in.index');
        }
        catch (\Exception $ex){
            Session::flash('error', 'Maaf kesalahan terjadi!' . $ex);
            return back();
        }
    }

    public function unapprove(Request $request){
        try{
            $admin = Auth::guard('admin')->user();
            $dataDB = BankCashinHeader::where('id', $request->input('id'))->first();
            if($dataDB == null){
                Session::flash('error', 'Data tidak ditemukan!');
                return back();
            }
            $dataDB->status_id = 1;
            $dataDB->save();

            //update jurnal
            $jHeader = JournalHeader::with('journal_details')
                ->where('reference_id', $request->input('id'))
                ->first();

            foreach($jHeader->journal_details as $dt){
                $dt->delete();
            }
            $jHeader->delete();

            return Response::json('Sukses', 200);
        }
        catch (\Exception $ex){
            return Response::json('error', 200);
        }
    }
}
