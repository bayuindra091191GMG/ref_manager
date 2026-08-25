<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendBookingPaymentApprovedNotification;
use App\Libs\AutoNumberGenerator;
use App\Libs\JournalUtilities;
use App\Libs\Utilities;
use App\Models\AdminUser;
use App\Models\Branch;
use App\Models\Coa;
use App\Models\Item;
use App\Models\JournalHeader;
use App\Models\JournalMap;
use App\Models\Package;
use App\Models\PosPaymentType;
use App\Models\PreweddingDetail;
use App\Models\PreweddingHeader;
use App\Models\PreweddingPackage;
use App\Models\PreweddingReceipt;
use App\Models\TransactionDetail;
use App\Models\TransactionDetailPartner;
use App\Models\TransactionHeader;
use App\Models\TransactionInvoice;
use App\Models\TransactionPayment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class PreWeddingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request)
    {
        $user = null;

        // Filter user
        if ($request->get('filter_user')) {
            $filterUserId = intval($request->get('filter_user'));
            if ($filterUserId > 0) {
                $user = DB::table('users')
                    ->where('id', '=', $filterUserId)
                    ->first();
            }
        }

        return view('admin.prewedding.index', compact('user'));
    }

    public function getIndex(Request $request)
    {
        $query = PreweddingHeader::select('prewedding_headers.id', 'prewedding_headers.created_at', 'prewedding_headers.code', 'prewedding_packages.name as package',
            'prewedding_headers.member_name', 'prewedding_headers.member_phone', 'prewedding_headers.status_id', 'statuses.display as status')
            ->leftJoin('prewedding_packages', 'prewedding_headers.package_id', '=', 'prewedding_packages.id')
            ->leftJoin('statuses', 'prewedding_headers.status_id', '=', 'statuses.id');

        if ($request->get('date')) {
            $date = explode(' - ', $request->get('date'));

            if (count($date) > 1) {
                $query->whereDate('prewedding_headers.created_at', $date[0])
                    ->whereDate('prewedding_headers.created_at', '<=', $date[1]);
            } else {
                $query->whereDate('prewedding_headers.created_at', $date[0]);
            }
        }

        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                $buttons = '';
                $buttons .= '<a href="'.route('admin.prewedding.show', ['id' => $row->id]).'" class="btn btn-info me-1"><i class="far fa fa-eye"></i> Show</a>';
                if ($row->status_id == 3) {
                    $buttons .= '<a href="'.route('admin.prewedding.edit', ['id' => $row->id]).'" class="btn btn-primary me-1"><i class="far fa fa-edit"></i> Edit</a>';
                }

                return $buttons;
            })
            ->make();
    }

    public function create()
    {
        return view('admin.prewedding.create');
    }

    public function getDetail(Request $request)
    {
        $pw = PreweddingPackage::find($request->get('id'));

        $data = [];
        $ids = explode('#', ltrim(rtrim($pw->packages, '#'), '#'));
        $package_prices = explode('#', ltrim(rtrim($pw->package_prices, '#'), '#'));

        $ct = 0;
        foreach ($ids as $id) {
            $idSplit = explode('-', $id);
            if ($idSplit[0] == 'P') {
                $package = Package::find($idSplit[1]);
                $data[] = [
                    'id' => Utilities::generateRandomStr(10),
                    'detail_id' => $id,
                    'name' => $package->code.' - '.$package->name,
                    'date' => '',
                    'duration' => $package->duration,
                    'price' => $package_prices[$ct],
                ];
            } else {
                $item = Item::find($idSplit[1]);
                $data[] = [
                    'id' => Utilities::generateRandomStr(10),
                    'detail_id' => $id,
                    'name' => $item->code.' - '.$item->name,
                    'date' => '',
                    'duration' => 0,
                    'price' => $package_prices[$ct],
                ];
            }
            $ct++;
        }

        return \response([
            'details' => $data,
            'price' => $pw->price,
        ]);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $details = $request->input('details') ?? [];
            if (count($details) == 0) {
                DB::rollBack();

                return redirect()->back()->withInput()->withErrors([
                    'Anda belum mengisi detail!',
                ]);
            }

            if (round($request->input('amount_received')) < round($request->input('price'))) {
                DB::rollBack();

                return redirect()->back()->withInput()->withErrors([
                    'Jumlah bayar kurang dari total transaksi!',
                ]);
            }
            $phone = $request->input('phone_code').$request->input('user_phone');

            $member = User::where('phone', $request->input('user_phone'))->first();
            if (! $member) {
                $member = User::create([
                    'name' => $request->input('user_name'),
                    'phone' => $phone,
                    'gender' => $request->input('gender'),
                    'country' => $request->input('country'),
                    'password' => Hash::make('123456'),
                ]);
            }

            $header = PreweddingHeader::create([
                'code' => AutoNumberGenerator::generateAutonumberTrx('PW'),
                'employee_id' => $request->input('employee_id'),
                'member_id' => $member->id,
                'member_name' => $request->input('user_name'),
                'member_phone' => $phone,
                'package_id' => $request->input('package_id'),
                'status_id' => 3,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);
            $posCode = AutoNumberGenerator::generateAutonumberTrx('POS');
            $transaction = TransactionHeader::create([
                'branch_id' => Auth::user()->branch_id,
                'is_prewed' => 1,
                'code' => $posCode,
                'room_id' => null,
                'member_name' => $request->input('user_name'),
                'member_phone' => $phone,
                'status_id' => 9,
                'foc_type' => 'no',
                'employee_id' => $request->input('employee'),
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            $header->update([
                'transaction_id' => $transaction->id,
            ]);

            foreach ($details as $detail) {
                $detailId = explode('-', $detail['id']);
                $dt = PreweddingDetail::create([
                    'header_id' => $header->id,
                    'package_id' => $detailId[0] == 'P' ? $detailId[1] : null,
                    'item_id' => $detailId[0] == 'I' ? $detailId[1] : null,
                    'date' => null,
                    'duration' => $detail['duration'],
                    'qty' => 1,
                    'price' => $detail['price'],
                    'subtotal' => $detail['price'],
                    'status_id' => 3,
                ]);

                $tDt = TransactionDetail::create([
                    'header_id' => $transaction->id,
                    'package_id' => $detailId[0] == 'P' ? $detailId[1] : null,
                    'status_id' => 9,
                    'qty' => 1,
                    'price' => 0,
                    'subtotal' => 0,
                    'start_time' => null,
                    'end_time' => null,
                    'item_id' => $detailId[0] == 'I' ? $detailId[1] : null,
                    'new_order_qty' => 1,
                ]);

                $dt->update([
                    'transaction_detail_id' => $tDt->id,
                ]);
            }

            $invoice = TransactionInvoice::create([
                'branch_id' => Auth::user()->branch_id,
                'header_ids' => implode('#', [$transaction->id]),
                'code' => $posCode,
                'subtotal' => $request->input('subtotal'),
                'is_use_voucher_code' => 0,
                'pb2' => $request->input('pb2'),
                'grandtotal' => $request->input('grandtotal'),
                'return_value' => $request->input('return_value'),
                'member_name' => $request->input('user_name'),
                'member_phone' => $phone,
                'status_id' => 9,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            $payment = PosPaymentType::find($request->input('payment_method'));

            TransactionPayment::create([
                'branch_id' => Auth::user()->branch_id,
                'invoice_id' => $invoice->id,
                'payment_type_id' => $request->input('payment_method'),
                'payment_type_code' => $payment->code,
                'amount' => $request->input('amount_received'),
            ]);

            // add user point
            $pointUsed = (int) floor($request->input('grandtotal') / 10000);
            Utilities::addTransactionPoint(
                $member->id,
                $pointUsed,
                $invoice->code,
                'TRANSACTION '.$invoice->code
            );

            // generate jurnal Journal Transaction
            $jHeader = new JournalHeader;
            $jHeader->transaction_code = AutoNumberGenerator::generateAutonumberTrx('JN');
            $jHeader->transaction_type = 'POS';
            $jHeader->transaction_date = $invoice->created_at;
            $jHeader->created_by = $invoice->created_by;
            $jHeader->branch_id = $invoice->branch_id;
            $jHeader->notes = 'Journal Transaksi Deposit '.$invoice->code;
            $jHeader->reference_id = $invoice->id;
            $jHeader->reference_code = $invoice->code;
            $jHeader->status_id = 13;
            $jHeader->created_at = $invoice->created_at;
            $jHeader->updated_at = $invoice->created_at;
            $jHeader->save();

            $dbPayments = TransactionPayment::where('invoice_id', $invoice->id)->get();
            foreach ($dbPayments as $dbPayment) {
                if ($dbPayment->amount > 0) {
                    $journalMap = JournalMap::where('branch_id', Auth::user()->branch_id)
                        ->where(function ($q) {
                            $q->where('transaction_type', 'POS_CASH')
                                ->orWhere('transaction_type', 'POS_NON_CASH');
                        })
                        ->where('room_type_id', 'like', '%'.$dbPayment->payment_type_id.'%')
                        ->first();

                    if ($journalMap != null) {
                        // insert journal
                        $coa = Coa::where('id', $journalMap->coa_id)->first();
                        if ($coa != null) {
                            // insert journal detail
                            $type = 'KREDIT';
                            if ($journalMap->journal_type == 'DEBET') {
                                $type = 'DEBET';
                            }
                            $notes = 'Payment Transaksi '.$header->code;
                            JournalUtilities::insertRecord($jHeader, Auth::user()->branch_id, $coa, $type, $dbPayment->amount, $notes, $header->code);
                        }
                    }
                    // insert jurnal deposit voucher
                    $coa = Coa::where('code', '2217')->first();
                    $value = $dbPayment->amount;

                    $notes = 'Deposit Pranikah '.' : '.$value;
                    JournalUtilities::insertRecord($jHeader, Auth::user()->branch_id, $coa, 'KREDIT', $dbPayment->amount, $notes, $header->code);
                }
            }

            DB::commit();

            Session::flash('success', 'Berhasil membuat transaksi paket!');

            return redirect()->route('admin.prewedding.show', ['id' => $header->id]);
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::channel('prewedding')->error('[Store] - '.$ex);

            return redirect()->back()->withInput()->withErrors([
                'Internal Server Error!',
            ]);
        }
    }

    public function edit($id)
    {
        $data = PreweddingHeader::find($id);

        $customer = User::where('phone', $data->member_phone)->first();
        $countries = Utilities::getCountryCodes();
        $code = '+62';
        $country = 'ID';
        $phone = $data->member_phone;
        $gender = $customer->gender;
        foreach ($countries as $c) {
            if ($c['code'] == $customer->country) {
                $code = $c['dial_code'];
                $country = $c['code'];
            }
        }
        $phone = str_replace($code, '', $phone);

        $invoice = TransactionInvoice::where('header_ids', $data->transaction_id)->first();
        $payment = TransactionPayment::where('invoice_id', $invoice->id)->first();

        $details = [];
        foreach ($data->details as $detail) {
            $id = $detail->package_id != null ? 'P-'.$detail->package_id : 'I-'.$detail->item_id;
            $details[] = [
                'id' => $detail->id,
                'detail_id' => $id,
                'name' => $detail->package_id != null ? ($detail->package->code.' - '.$detail->package->name) : ($detail->item->code.' - '.$detail->item->name),
                'date' => '',
                'duration' => $detail->package_id != null ? $detail->package->duration : 0,
                'price' => $detail->price,
            ];
        }

        return view('admin.prewedding.edit', compact('data', 'details', 'invoice', 'payment', 'country', 'countries', 'phone', 'gender'));
    }

    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $details = $request->input('details') ?? [];
            if (count($details) == 0) {
                DB::rollBack();

                return redirect()->back()->withInput()->withErrors([
                    'Anda belum mengisi detail!',
                ]);
            }

            if (round($request->input('amount_received')) < round($request->input('price'))) {
                DB::rollBack();

                return redirect()->back()->withInput()->withErrors([
                    'Jumlah bayar kurang dari total transaksi!',
                ]);
            }

            $member = User::where('phone', $request->input('user_phone'))->first();
            if (! $member) {
                $member = User::create([
                    'name' => $request->input('user_name'),
                    'phone' => $request->input('user_phone'),
                    'gender' => $request->input('gender'),
                    'country' => $request->input('country'),
                    'password' => Hash::make('123456'),
                ]);
            }

            $header = PreweddingHeader::find($request->input('id'));

            $header->update([
                'employee_id' => $request->input('employee_id'),
                'member_id' => $member->id,
                'member_name' => $request->input('user_name'),
                'member_phone' => $request->input('user_phone'),
                'package_id' => $request->input('package_id'),
                'updated_by' => Auth::id(),
            ]);

            $transaction = TransactionHeader::find($header->transaction_id);
            $transaction->update([
                'member_name' => $request->input('user_name'),
                'member_phone' => $request->input('user_phone'),
                'updated_by' => Auth::id(),
            ]);

            $delIds = $request->input('delIds') ?? [];
            $delDetails = PreweddingDetail::whereIn('id', $delIds)->get();
            foreach ($delDetails as $delDetail) {
                TransactionDetail::where('id', $delDetail->transaction_detail_id)->delete();
                $delDetail->delete();
            }

            foreach ($details as $detail) {
                $detailId = explode('-', $detail['id']);

                $check = PreweddingDetail::find($detail['detail_id']);
                if ($check) {
                    $check->update([
                        'header_id' => $header->id,
                        'package_id' => $detailId[0] == 'P' ? $detailId[1] : null,
                        'item_id' => $detailId[0] == 'I' ? $detailId[1] : null,
                        'date' => null,
                        'duration' => $detail['duration'],
                        'qty' => 1,
                        'status_id' => 3,
                    ]);

                    $tDt = TransactionDetail::find($check->transaction_detail_id);
                    $tDt->update([
                        'header_id' => $transaction->id,
                        'package_id' => $detailId[0] == 'P' ? $detailId[1] : null,
                        'status_id' => 9,
                        'qty' => 1,
                        'price' => 0,
                        'subtotal' => 0,
                        'start_time' => null,
                        'end_time' => null,
                        'item_id' => $detailId[0] == 'I' ? $detailId[1] : null,
                        'new_order_qty' => 1,
                    ]);
                } else {
                    $dt = PreweddingDetail::create([
                        'header_id' => $header->id,
                        'package_id' => $detailId[0] == 'P' ? $detailId[1] : null,
                        'item_id' => $detailId[0] == 'I' ? $detailId[1] : null,
                        'date' => null,
                        'duration' => $detail['duration'],
                        'qty' => 1,
                        'status_id' => 3,
                    ]);

                    $tDt = TransactionDetail::create([
                        'header_id' => $transaction->id,
                        'package_id' => $detailId[0] == 'P' ? $detailId[1] : null,
                        'status_id' => 9,
                        'qty' => 1,
                        'price' => 0,
                        'subtotal' => 0,
                        'start_time' => null,
                        'end_time' => null,
                        'item_id' => $detailId[0] == 'I' ? $detailId[1] : null,
                        'new_order_qty' => 1,
                    ]);

                    $dt->update([
                        'transaction_detail_id' => $tDt->id,
                    ]);
                }
            }

            $invoice = TransactionInvoice::where('header_ids', $transaction->id)->first();
            $invoice->update([
                'subtotal' => $request->input('price'),
                'is_use_voucher_code' => 0,
                'pb2' => $request->input('pb2'),
                'grandtotal' => $request->input('grandtotal'),
                'return_value' => $request->input('return_value'),
                'member_name' => $request->input('user_name'),
                'member_phone' => $request->input('user_phone'),
                'updated_by' => Auth::id(),
            ]);

            $payment = PosPaymentType::find($request->input('payment_method'));

            $tPayment = TransactionPayment::where('invoice_id', $invoice->id)->first();
            $tPayment->update([
                'branch_id' => Auth::user()->branch_id,
                'invoice_id' => $invoice->id,
                'payment_type_id' => $request->input('payment_method'),
                'payment_type_code' => $payment->code,
                'amount' => $request->input('amount_received'),
            ]);

            DB::commit();

            Session::flash('success', 'Berhasil mengubah transaksi paket!');

            return redirect()->route('admin.prewedding.show', ['id' => $header->id]);
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::channel('prewedding')->error('[Update] - '.$ex);

            return redirect()->back()->withInput()->withErrors([
                'Internal Server Error!',
            ]);
        }
    }

    public function show($id)
    {
        $data = PreweddingHeader::find($id);

        $trxCode = null;
        $invoice = null;
        if (! empty($data->transaction_id)) {
            $trxCode = DB::table('transaction_headers')
                ->where('id', '=', $data->transaction_id)
                ->value('code');
            $invoice = TransactionInvoice::where('header_ids', $data->transaction_id)->first();
        }

        $details = [];
        foreach ($data->details as $detail) {
            $details[] = [
                'detail_id' => $detail->id,
                'type' => $detail->package_id != null ? 'package' : 'item',
                'id' => $detail->package_id != null ? $detail->package_id : $detail->item_id,
                'name' => $detail->package_id != null ? ($detail->package->code.' - '.$detail->package->name) : ($detail->item->code.' - '.$detail->item->name),
                'qty' => $detail->qty,
                'partner_id' => null,
                'start_time' => $detail->start_time != null ? Carbon::parse($detail->start_time)->format('d-m-Y H:i') : '-',
                'end_time' => $detail->end_time != null ? Carbon::parse($detail->end_time)->format('d-m-Y H:i') : '-',
                'status_id' => $detail->status_id,
                'selected' => $detail->status_id == 9,
                'duration' => $detail->package_id != null ? $detail->package->duration : $detail->item->duration,
            ];
        }

        return view('admin.prewedding.show', compact('data', 'trxCode', 'invoice', 'details'));
    }

    public function checkProof(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'status' => 'required|integer|in:13,14',
            'payment_proof' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $header = PreweddingHeader::find($request->input('id'));
            if (! $header) {
                DB::rollBack();
                Session::flash('error', 'Transaksi paket tidak ditemukan!');

                return redirect()->back();
            }

            if (! (bool) $header->is_from_application) {
                DB::rollBack();
                Session::flash('error', 'Review bukti pembayaran hanya berlaku untuk transaksi dari aplikasi!');

                return redirect()->route('admin.prewedding.show', ['id' => $header->id]);
            }

            if ($request->input('payment_proof') !== $header->payment_proof) {
                DB::rollBack();
                Session::flash('error', 'Bukti pembayaran sudah berubah. Muat ulang halaman lalu review ulang!');

                return redirect()->route('admin.prewedding.show', ['id' => $header->id]);
            }

            if (
                (int) $request->input('status') === 13
                && (
                    empty($header->payment_proof)
                    || ! Storage::disk('proofs')->exists($header->payment_proof)
                )
            ) {
                DB::rollBack();
                Session::flash('error', 'Bukti pembayaran belum diupload!');

                return redirect()->route('admin.prewedding.show', ['id' => $header->id]);
            }

            $payload = [
                'status_id' => (int) $request->input('status'),
                'updated_by' => Auth::id(),
            ];

            if ((int) $request->input('status') === 14) {
                if (! empty($header->payment_proof) && Storage::disk('proofs')->exists($header->payment_proof)) {
                    Storage::disk('proofs')->delete($header->payment_proof);
                }

                $payload['payment_proof'] = null;
            }

            $header->update($payload);

            DB::commit();

            if ((int) $request->input('status') === 13) {
                SendBookingPaymentApprovedNotification::dispatch($header);
            }

            Session::flash('success', 'Berhasil '.((int) $request->input('status') === 14 ? 'reject' : 'approve').' bukti pembayaran!');

            return redirect()->route('admin.prewedding.show', ['id' => $header->id]);
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::channel('prewedding')->error('[CheckProof] - '.$ex);

            Session::flash('error', 'Terjadi kesalahan pada sistem!');

            return redirect()->back()->withInput($request->except('_token'));
        }
    }

    public function cancel(Request $request)
    {
        try {
            if (! $request->filled('cancelled_prewedding_id')) {
                Session::flash('error', 'Terjadi kesalahan input!');

                return redirect()->back();
            }

            $header = PreweddingHeader::find($request->input('cancelled_prewedding_id'));
            if (! $header) {
                Session::flash('error', 'Transaksi paket tidak ditemukan!');

                return redirect()->back();
            }

            if (! (bool) $header->is_from_application) {
                Session::flash('error', 'Pembatalan hanya berlaku untuk transaksi dari aplikasi!');

                return redirect()->route('admin.prewedding.show', ['id' => $header->id]);
            }

            if ((int) $header->status_id === 10) {
                Session::flash('error', 'Transaksi paket sudah dibatalkan!');

                return redirect()->route('admin.prewedding.show', ['id' => $header->id]);
            }

            $admin = Auth::guard('admin')->user();

            $header->update([
                'status_id' => 10,
                'updated_by' => $admin->id,
            ]);

            if (! empty($header->transaction_id)) {
                DB::table('transaction_headers')
                    ->where('id', '=', $header->transaction_id)
                    ->update([
                        'status_id' => 16,
                        'voided_at' => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                        'updated_by' => $admin->id,
                    ]);
            }

            Session::flash('success', 'Berhasil membatalkan transaksi paket!');

            return redirect()->route('admin.prewedding.show', ['id' => $header->id]);
        } catch (\Exception $ex) {
            Log::channel('prewedding')->error('[Cancel] - '.$ex);
            Session::flash('error', 'Terjadi kesalahan pada sistem!');

            return redirect()->back();
        }
    }

    public function process(Request $request)
    {
        $header = PreweddingHeader::find($request->input('header_id'));
        if (! $header) {
            return \response([
                'success' => false,
                'message' => 'Transaksi paket tidak ditemukan!',
            ]);
        }

        if ((bool) $header->is_from_application && (int) $header->status_id !== 13) {
            return \response([
                'success' => false,
                'message' => 'Bukti pembayaran belum di-approve!',
            ]);
        }

        DB::beginTransaction();
        try {
            $details = $request->input('details') ?? [];

            $receipt = PreweddingReceipt::create([
                'header_id' => $request->input('header_id'),
                'employee_id' => $request->input('employee_id'),
            ]);

            $header->update([
                'status_id' => 1,
            ]);

            // generate jurnal Journal Transaction
            $invoice = TransactionInvoice::where('header_ids', $header->transaction_id)->first();
            $start = Carbon::createFromFormat('d-m-Y H:i', $details[0]['date'].' '.$details[0]['time']);

            // generate jurnal Journal Transaction waktu direalisasikan
            $jHeader = new JournalHeader;
            $jHeader->transaction_code = AutoNumberGenerator::generateAutonumberTrx('JN');
            $jHeader->transaction_type = 'POS';
            $jHeader->transaction_date = $start->toDateTimeString();
            $jHeader->created_by = $invoice->created_by;
            $jHeader->branch_id = $invoice->branch_id;
            $jHeader->notes = 'Journal Transaksi Realisasi '.$header->code;
            $jHeader->reference_id = $invoice->id;
            $jHeader->reference_code = $header->code;
            $jHeader->status_id = 13;
            $jHeader->created_at = $start->toDateTimeString();
            $jHeader->updated_at = $start->toDateTimeString();
            $jHeader->save();

            foreach ($details as $detail) {
                $check = PreweddingDetail::find($detail['detail_id']);
                $tDetail = TransactionDetail::find($check->transaction_detail_id);
                $itemPrice = 0;
                $itemDiscount = 0;
                if ($tDetail->package_id != null) {
                    $tmpPackageDetail = Package::where('id', $tDetail->package_id)->first();
                    $itemPrice = $tmpPackageDetail->price;
                    $itemDiscount = $tmpPackageDetail->price - $check->price;
                }
                if ($tDetail->item_id != null) {
                    // insert journal item
                    $item = DB::table('items')
                        ->where('id', '=', $tDetail->item_id)
                            // ->where('name', 'not like', 'TS%')
                        ->first();
                    $itemPrice = $item->price;
                    $itemDiscount = $item->price - $check->price;
                }

                $start = Carbon::createFromFormat('d-m-Y H:i', $detail['date'].' '.$detail['time']);
                $end = $start->copy()->addMinutes((int) $detail['duration']);

                $check->update([
                    'receipt_id' => $receipt->id,
                    'partner_id' => $detail['partner_id'],
                    'room_id' => $detail['room_id'],
                    'status_id' => 9,
                    'start_time' => $start->toDateTimeString(),
                    'end_time' => $end->toDateTimeString(),
                ]);

                $tDetail->update([
                    'price' => $itemPrice,
                    'subtotal' => $itemPrice,
                    'discount_amount' => $itemDiscount,
                    'start_time' => $start->toDateTimeString(),
                    'end_time' => $end->toDateTimeString(),
                    'created_at' => $start->toDateTimeString(),
                    'updated_at' => $end->toDateTimeString(),
                ]);

                TransactionDetailPartner::create([
                    'partner_id' => $detail['partner_id'],
                    'transaction_detail_id' => $tDetail->id,
                ]);

                $pb = ($tDetail->price * $tDetail->qty * 10) / 100;
                $totalDeposit = ($tDetail->price * $tDetail->qty) + $pb;

                $coa = Coa::where('code', '2217')->first();
                $value = $totalDeposit;

                $notes = 'Deposit Pranikah '.' : '.$value;
                JournalUtilities::insertRecord($jHeader, Auth::user()->branch_id, $coa, 'DEBIT', $value, $notes, $header->code);
                $packageOrItemName = '';
                if ($tDetail->package_id != null) {
                    $tmpPackageDetail = Package::where('id', $tDetail->package_id)->first();
                    $packageOrItemName = $tmpPackageDetail->name;

                    // insert jurnal pendapatan
                    $coa = Coa::where('id', $tmpPackageDetail->room_type->coa_id)->first();
                    $value = ($tDetail->price * $tDetail->qty);

                    $notes = 'Pendapatan Transaksi '.$tmpPackageDetail->name.' : '.$value;
                    JournalUtilities::insertRecord($jHeader, Auth::user()->branch_id, $coa, 'KREDIT', $value, $notes, $header->code);

                }
                if ($tDetail->item_id != null) {
                    // insert journal item
                    $item = DB::table('items')
                        ->where('id', '=', $tDetail->item_id)
                            // ->where('name', 'not like', 'TS%')
                        ->first();
                    $packageOrItemName = $item->name;

                    // jurnal pendapatan cafe
                    if ($item->category_id == 2 || $item->category_id == 28) {
                        $coa = Coa::where('code', '4105')->first();
                        $value = ($tDetail->price * $tDetail->qty);

                        $notes = 'Pendapatan Transaksi '.$item->name.' : '.$value;
                        JournalUtilities::insertRecord($jHeader, Auth::user()->branch_id, $coa, 'KREDIT', $value, $notes, $header->code);

                    } else {
                        // jurnal pendapatan tambahan treatment
                        $coa = Coa::where('code', '4102')->first();
                        $value = ($tDetail->price * $tDetail->qty);

                        $notes = 'Pendapatan Transaksi '.$item->name.' : '.$value;
                        JournalUtilities::insertRecord($jHeader, Auth::user()->branch_id, $coa, 'KREDIT', $value, $notes, $header->code);
                    }
                }

                // jurnal PB / Pajak
                $coa = Coa::where('code', '2208')->first();
                $value = $pb;

                $notes = 'Pajak Barang dan Jasa Transaksi '.$packageOrItemName.' : '.$value;
                JournalUtilities::insertRecord($jHeader, Auth::user()->branch_id, $coa, 'KREDIT', $value, $notes, $header->code);
            }

            $header->update([
                'status_id' => PreweddingDetail::where('header_id', $header->id)
                    ->where('status_id', '!=', 9)
                    ->exists() ? 1 : 9,
            ]);

            $newDetails = [];
            $dts = PreweddingDetail::where('header_id', $request->input('header_id'))->get();
            foreach ($dts as $detail) {
                $newDetails[] = [
                    'detail_id' => $detail->id,
                    'type' => $detail->package_id != null ? 'package' : 'item',
                    'id' => $detail->package_id != null ? $detail->package_id : $detail->item_id,
                    'name' => $detail->package_id != null ? ($detail->package->code.' - '.$detail->package->name) : ($detail->item->code.' - '.$detail->item->name),
                    'qty' => $detail->qty,
                    'partner_id' => null,
                    'start_time' => $detail->start_time != null ? Carbon::parse($detail->start_time)->format('d-m-Y H:i') : '-',
                    'end_time' => $detail->end_time != null ? Carbon::parse($detail->end_time)->format('d-m-Y H:i') : '-',
                    'status_id' => $detail->status_id,
                    'selected' => $detail->status_id == 9,
                    'duration' => $detail->package_id != null ? $detail->package->duration : $detail->item->duration,
                ];
            }

            DB::commit();

            return \response([
                'success' => true,
                'details' => $newDetails,
                'url' => route('admin.prewedding.print', ['id' => $receipt->id]),
            ]);
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::channel('prewedding')->error('[Update] - '.$ex);

            return \response([
                'success' => false,
                'message' => 'Internal Server Error!',
            ]);
        }
    }

    public function print($id)
    {
        $user = Auth::guard('admin')->user();
        $userData = AdminUser::where('id', $user->id)->first();
        $branch = Branch::where('id', $userData->branch_id)->first();

        $receipt = PreweddingReceipt::find($id);
        $header = PreweddingHeader::find($receipt->header_id);

        $invoice = null;
        if (! empty($header->transaction_id)) {
            $trxCode = DB::table('transaction_headers')
                ->where('id', '=', $header->transaction_id)
                ->value('code');
            $invoice = TransactionInvoice::where('header_ids', $header->transaction_id)->first();
        }

        $done = [];
        $details = [];
        $rests = [];

        foreach ($receipt->details as $detail) {
            $details[] = $detail->package_id != null ? $detail->package->name : $detail->item->name;
        }

        $doneDetails = PreweddingDetail::where('header_id', $receipt->header_id)
            ->where('status_id', 9)
            ->get();

        foreach ($doneDetails as $detail) {
            $done[] = $detail->package_id != null ? $detail->package->name : $detail->item->name;
        }

        $restDetails = PreweddingDetail::where('header_id', $receipt->header_id)
            ->where('status_id', '!=', 9)
            ->get();

        foreach ($restDetails as $detail) {
            $rests[] = $detail->package_id != null ? $detail->package->name : $detail->item->name;
        }

        return view('admin.prewedding.partial_receipt', compact('receipt', 'details', 'rests', 'branch', 'invoice', 'done'));
    }
}
