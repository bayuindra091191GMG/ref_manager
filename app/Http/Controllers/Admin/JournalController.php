<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Libs\AutoNumberGenerator;
use App\Models\AdminUser;
use App\Models\Branch;
use App\Models\Coa;
use App\Models\Configuration;
use App\Models\JournalDetail;
use App\Models\JournalHeader;
use App\Models\JournalType;
use App\Models\TransactionHeader;
use App\Transformer\JournalTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class JournalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function getIndex(Request $request)
    {
        $user = Auth::guard('admin')->user();
        $journals = JournalHeader::where('status_id', '!=', 2)
            ->where('branch_id', $user->branch_id);

        if ($request->get('type')) {
            $journals->where('transaction_type', $request->get('type'));
        }

        if ($request->get('is_approve')) {
            $journals->where('status_id', $request->get('is_approve'));
        }

        if ($request->get('period')) {
            $dates = explode(' to ', $request->get('period'));
            if (count($dates) > 1) {
                $journals->whereDate('transaction_date', '>=', Carbon::parse($dates[0])->toDateTimeString())
                    ->whereDate('transaction_date', '<=', Carbon::parse($dates[1])->toDateTimeString());
            } else {
                $journals->whereDate('transaction_date', Carbon::parse($dates[0])->toDateTimeString());
            }
        }

        return DataTables::of($journals)
            ->setTransformer(new JournalTransformer)
            ->addIndexColumn()
            ->make(true);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $types = JournalType::where('status_id', 1)->get();

        return view('admin.journal.index', compact('types'));
    }

    public function create()
    {
        $user = Auth::guard('admin')->user();
        if ($user->can('branch.scope_all') == 1) {
            $branches = Branch::all();
        } else {
            $branches = Branch::find($user->branch_id);
        }
        $journalTypes = JournalType::where('status_id', 1)->get();

        $data = [
            'types' => $journalTypes,
        ];

        return view('admin.journal.create')->with($data);
    }

    public function store(Request $request)
    {
        //        dd($request);
        $validator = Validator::make($request->all(), [
            'transaction_type' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors())->withInput($request->all());
        }

        $debits = $request->input('debits');
        $credits = $request->input('credits');
        $coaIds = $request->input('coaIds');
        $desc = $request->input('desc');

        if (count($coaIds) > 0) {
            if (count($coaIds) != count($debits) && count($coaIds) != count($credits)) {
                return back()->withErrors('Detail COA tidak lengkap')->withInput($request->all());
            }
        } else {
            return back()->withErrors('Detail COA tidak lengkap')->withInput($request->all());
        }

        // Create Admin
        $user = Auth::guard('admin')->user();

        $data = new JournalHeader;
        $data->branch_id = $user->branch_id;
        $data->transaction_code = AutoNumberGenerator::generateAutonumberTrx('JN');
        $data->transaction_type = $request->input('transaction_type');
        $data->transaction_date = Carbon::parse($request->input('transaction_date'))->toDateTimeString();
        $data->notes = $request->input('notes') ?? '';
        $data->status_id = 1;
        $data->created_by = $user->id;
        $data->save();
        //        AutoNumberGenerator::nextAutonumber('JOUR');
        $ct = 0;
        foreach ($coaIds as $itemId) {

            $dataDetail = new JournalDetail;
            $dataDetail->branch_id = $user->branch_id;
            $dataDetail->header_id = $data->id;
            $dataDetail->transaction_code = $data->transaction_code;
            $dataDetail->debit = (float) $debits[$ct];
            $dataDetail->credit = (float) $credits[$ct];
            $dataDetail->original_amount = (float) $debits[$ct] == 0 ? (float) $credits[$ct] : (float) $debits[$ct];
            $dataDetail->account_id = $itemId;
            $dataDetail->account_code = Coa::find($itemId)->code;
            $dataDetail->account_name = Coa::find($itemId)->name;
            $dataDetail->account_type_id = Coa::find($itemId)->type_id;
            $dataDetail->account_type_name = Coa::find($itemId)->type;
            $dataDetail->transaction_type = $data->transaction_type;
            $dataDetail->notes = $desc[$ct] ?? '';
            $dataDetail->save();

            $ct++;
        }

        Session::flash('success', 'Sukses membuat Jurnal baru');

        return redirect()->route('admin.journal.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $dataDB = JournalHeader::find($id);
        $admin = Auth::guard('admin')->user();

        $journalTypes = JournalType::where('status_id', 1)->get();
        $detailData = [];
        $journalDetails = JournalDetail::where('header_id', $dataDB->id)->get();

        $totalD = 0;
        $totalK = 0;
        foreach ($journalDetails as $dataDetail) {
            $vueData = [
                'id' => $dataDetail->id,
                'coaId' => $dataDetail->account_id,
                'amount' => $dataDetail->debit == 0 ? $dataDetail->credit : $dataDetail->debit,
                'debitKredit' => $dataDetail->debit == 0 ? 'K' : 'D',
                'debit' => $dataDetail->debit,
                'credit' => $dataDetail->credit,
                'desc' => $dataDetail->notes,
            ];
            array_push($detailData, $vueData);
            if ($dataDetail->debit == 0) {
                $totalK = $totalK + $dataDetail->credit;
            } else {
                $totalD = $totalD + $dataDetail->debit;
            }

        }

        $data = [
            'types' => $journalTypes,
            'data' => $dataDB,
            'vueData' => json_encode($detailData),
            'totalK' => $totalK,
            'totalD' => $totalD,
            'balance' => $totalD - $totalK,
        ];

        return view('admin.journal.edit')->with($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transaction_type' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors())->withInput($request->all());
        }

        $debits = $request->input('debits');
        $credits = $request->input('credits');
        $coaIds = $request->input('coaIds');
        $desc = $request->input('desc');

        if (count($coaIds) > 0) {
            if (count($coaIds) != count($debits) && count($coaIds) != count($credits)) {
                return back()->withErrors('Detail COA tidak lengkap')->withInput($request->all());
            }
        } else {
            return back()->withErrors('Detail COA tidak lengkap')->withInput($request->all());
        }
        // dd($coaIds);

        // Edit Admin
        $id = $request->input('id');
        $user = Auth::guard('admin')->user();

        $data = JournalHeader::where('id', $id)->first();
        $trxHeader = TransactionHeader::where('id', $data->reference_id)->first();
        $data->transaction_type = JournalType::find($request->input('transaction_type'))->code;
        $data->transaction_date = Carbon::parse($request->input('transaction_date'))->toDateTimeString();
        $data->notes = $request->input('notes') ?? '';
        $data->updated_by = $user->id;
        $data->save();

        $oldData = JournalDetail::where('transaction_code', $data->transaction_code)->get();
        if (count($oldData) > 0) {
            foreach ($oldData as $oldDetaildata) {
                $oldDetaildata->delete();
            }
        }
        $ct = 0;
        foreach ($coaIds as $itemId) {

            $dataDetail = new JournalDetail;
            $dataDetail->branch_id = $user->branch_id;
            $dataDetail->header_id = $data->id;
            $dataDetail->transaction_code = $data->transaction_code;
            $dataDetail->debit = (float) $debits[$ct];
            $dataDetail->credit = (float) $credits[$ct];
            $dataDetail->original_amount = (float) $debits[$ct] == 0 ? (float) $credits[$ct] : (float) $debits[$ct];
            $dataDetail->account_id = $itemId;
            $dataDetail->account_code = Coa::find($itemId)->code;
            $dataDetail->account_name = Coa::find($itemId)->name;
            $dataDetail->account_type_id = Coa::find($itemId)->type_id;
            $dataDetail->account_type_name = Coa::find($itemId)->type;
            $dataDetail->transaction_type = $data->transaction_type;
            $dataDetail->notes = $desc[$ct] ?? '';
            $dataDetail->reference_code = empty($trxHeader) ? '' : $trxHeader->code;
            $dataDetail->save();

            $ct++;
        }

        Session::flash('success', 'Success Updating Jurnal!');

        return redirect()->route('admin.journal.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
    {
        $dataDB = JournalHeader::find($id);
        $admin = Auth::guard('admin')->user();
        $branches = Branch::find($dataDB->branch_id ?? $admin->branch_id);

        $journalTypes = JournalType::where('status_id', 1)->get();
        $detailData = [];
        $journalDetails = JournalDetail::where('header_id', $dataDB->id)->get();
        $totalD = 0;
        $totalK = 0;
        foreach ($journalDetails as $dataDetail) {
            $vueData = [
                'coaId' => $dataDetail->account_id,
                'coaCode' => $dataDetail->account_code,
                'coaName' => $dataDetail->account_name,
                'amount' => $dataDetail->debit == 0 ? $dataDetail->credit : $dataDetail->debit,
                'debitKredit' => $dataDetail->debit == 0 ? 'K' : 'D',
                'debit' => $dataDetail->debit ?? 0,
                'credit' => $dataDetail->credit ?? 0,
                'desc' => $dataDetail->notes,
            ];
            array_push($detailData, $vueData);
            if ($dataDetail->debit == 0) {
                $totalK = $totalK + $dataDetail->credit;
            } else {
                $totalD = $totalD + $dataDetail->debit;
            }
        }

        $createdBy = AdminUser::where('id', $dataDB->created_by)->first();
        $journalType = $journalTypes->firstWhere('code', $dataDB->transaction_type);

        $data = [
            'branches' => $branches,
            'types' => $journalTypes,
            'data' => $dataDB,
            'journalType' => $journalType,
            'vueData' => json_encode($detailData),
            'itemList' => $detailData,
            'createdBy' => $createdBy != null ? $createdBy->name : '-',
            'totalK' => $totalK,
            'totalD' => $totalD,
            'balance' => $totalD - $totalK,
        ];

        $type = trim((string) $request->query('type'));
        if (! empty($type)) {
            return view('admin.journal.print')->with($data);
        }

        return view('admin.journal.show')->with($data);
    }

    public function delete(Request $request)
    {
        //
        try {
            if (! $request->filled('password')) {
                return Response::json(['errors' => 'Password harus diisi']);
            }

            // check password first
            $config = Configuration::where('key', 'spv_password')->first();
            if (! Hash::check($request->input('password'), $config->value)) {
                return Response::json(['errors' => 'Pasword yang Diisi Salah']);
            }

            $coaId = $request->input('id');
            $coa = JournalHeader::find($coaId);

            if (empty($coa)) {
                return Response::json(['errors' => 'INVALID']);
            }
            $coa->status_id = 2;
            $coa->save();

            // $adminUser->delete();

            Session::flash('success', 'Sukses menghapus Jurnal '.$coa->name);

            return Response::json('Sukses', 200);
        } catch (\Exception $ex) {
            Log::error('Admin/CoaController - destroy - error EX: '.$ex);

            return Response::json('error', 200);
        }
    }

    public function approve(Request $request)
    {
        try {
            // check password first
            if (! $request->filled('password')) {
                Session::flash('error', 'Harus mengisi Password terlebih dahulu ');

                return back();
            }

            $config = Configuration::where('key', 'spv_password')->first();
            if (! Hash::check($request->input('password'), $config->value)) {
                Session::flash('error', 'Password yang Diisi Salah ');

                return back();
            }

            //            dd($request);
            $user = Auth::guard('admin')->user();
            $ids = $request->input('ids');
            if (empty($ids)) {
                Session::flash('error', 'Tidak Ada Data yang Dipilih');

                return redirect()->route('admin.journal.index');
            }
            foreach ($ids as $id) {
                $dataDB = JournalHeader::where('id', $id)->first();
                if ($dataDB == null) {
                    Session::flash('error', 'Data tidak ditemukan!');

                    return back();
                }
                $dataDB->is_approve = 1;
                $dataDB->status_id = 13;
                $dataDB->updated_by = $user->id;
                $dataDB->save();
            }

            Session::flash('success', 'Sukses approve data Acc Payment!');

            return redirect()->route('admin.journal.index');
        } catch (\Exception $ex) {
            Session::flash('error', 'Maaf kesalahan terjadi!'.$ex);

            return back();
        }
    }

    public function unapprove(Request $request)
    {
        //
        try {
            // check password first
            if (! $request->filled('password')) {
                return Response::json(['errors' => 'Password harus diisi']);
            }

            $config = Configuration::where('key', 'spv_password')->first();
            if (! Hash::check($request->input('password'), $config->value)) {
                return Response::json(['errors' => 'Pasword yang Diisi Salah']);
            }

            $coaId = $request->input('id');
            $coa = JournalHeader::find($coaId);

            if (empty($coa)) {
                return Response::json(['errors' => 'INVALID']);
            }
            $coa->status_id = 1;
            $coa->is_approve = 0;
            $coa->save();

            // $adminUser->delete();

            Session::flash('success', 'Sukses unapprove Jurnal '.$coa->name);

            return Response::json('Sukses', 200);
        } catch (\Exception $ex) {
            Log::error('Admin/CoaController - destroy - error EX: '.$ex);

            return Response::json('error', 200);
        }
    }
}
