<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Libs\AutoNumberGenerator;
use App\Libs\JournalUtilities;
use App\Libs\Utilities;
use App\Models\Coa;
use App\Models\Coupon;
use App\Models\JournalHeader;
use App\Models\JournalMap;
use App\Models\PosPaymentType;
use App\Models\TransactionDetail;
use App\Models\TransactionDetailPartner;
use App\Models\TransactionHeader;
use App\Models\TransactionInvoice;
use App\Models\TransactionPayment;
use App\Models\User;
use App\Models\UserVoucher;
use App\Models\UserVoucherCode;
use App\Models\VoucherPackage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class VoucherPosController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request){
        $oldData = json_decode($request->session()->get('cart_voucher') ?? '[]', true);
        $oldMainData = json_decode($request->session()->get('cart_voucher_data') ?? '{"exist": false}', true);
        $countries = Utilities::getCountryCodes();

        $voucherData = VoucherPackage::where('status_id', 1)->get();

        $vouchers = [];
        foreach ($voucherData as $voucher) {
            $qty = 0;
            $picked = false;
            foreach ($oldData as $data){
                if($data['id'] == $voucher->id){
                    $picked = $data['picked'];
                    $qty = $data['qty'];
                    break;
                }
            }

            $vouchers[] = [
                'id' => $voucher->id,
                'code' => $voucher->code,
                'name' => $voucher->name,
                'price' => $voucher->price,
                'expired' => $voucher->expired,
                'img' => $voucher->img != null ? asset('storage/images/vouchers/'. $voucher->img) : '',
                'qty' => $qty,
                'picked' => $picked
            ];
        }

        return view('admin.voucher_pos.index', compact('vouchers', 'countries', 'oldMainData'));
    }

    public function updateSession(Request $request){
        $request->session()->put('cart_voucher', $request->input('cart'));
        $request->session()->put('cart_voucher_data', $request->input('data'));
    }

    public function checkout(Request $request){
        DB::beginTransaction();

        try {
            $start = Carbon::now();
            $member = User::where('phone', $request->input('member_phone'))->first();
            if(!$member){
                $member = User::create([
                    'name' => $request->input('member_name'),
                    'phone' => $request->input('member_phone'),
                    'gender' => $request->input('gender'),
                    'country' => $request->input('country'),
                    'password' => Hash::make('123456'),
                ]);
            }
            $posCode = AutoNumberGenerator::generateAutonumberTrx('POS');
            $header = TransactionHeader::create([
                'branch_id' => Auth::user()->branch_id,
                'code' => $posCode,
                'member_name' => $request->input('member_name'),
                'member_phone' => $request->input('member_phone'),
                'status_id' => 9,
                'foc_type' => 'no',
                'employee_id' => $request->input('employee'),
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            $expired = $request->input('expired');

            foreach ($request->input('details') as $detail) {
                $voucher = VoucherPackage::find($detail['id']);
                $voucherHeader = UserVoucher::create([
                    'user_id' => $member->id,
                    'voucher_id' => $detail['id'],
                    'voucher_code' => $detail['name'],
                    'type' => 'package',
                    'terms' => $voucher->terms_condition
                ]);

                for($i = 0; $i < $detail['qty']; $i++){
                    UserVoucherCode::create([
                        'header_id' => $voucherHeader->id,
                        'code' => AutoNumberGenerator::generateVoucherCode($member),
                        'expired_date' => empty($expired) ? $start->copy()->addDays($voucher->expired)->toDateTimeString() : Carbon::createFromFormat('d-m-Y', $expired)->toDateTimeString(),
                        'is_used' => 0
                    ]);
                }

                TransactionDetail::create([
                    'header_id' => $header->id,
                    'status_id' => 9,
                    'qty' => $detail['qty'],
                    'price' => $detail['price'],
                    'subtotal' => ($detail['price'] * $detail['qty']),
                    'new_order_qty' => $detail['qty'],
                    'coupon_id' => $voucherHeader->id,
                ]);
            }

            $invoice = TransactionInvoice::create([
                'branch_id' => Auth::user()->branch_id,
                'header_ids' => implode('#', [$header->id]),
                'code' => $posCode,
                'subtotal' => $request->input('subtotal'),
                'pb2' => $request->input('pb2'),
                'rounding_amount' => $request->input('custom_amount'),
                'grandtotal' => $request->input('grandtotal'),
                'is_use_voucher_code' =>  0,
                'return_value' => $request->input('return_value'),
                'member_name' => $request->input('member_name'),
                'member_phone' => $request->input('member_phone'),
                'status_id' => 9,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            $payment = PosPaymentType::find($request->input('selected_payment_method'));

            TransactionPayment::create([
                'branch_id' => Auth::user()->branch_id,
                'invoice_id' => $invoice->id,
                'payment_type_id' => $request->input('selected_payment_method'),
                'payment_type_code' => $payment->code,
                'amount' => $request->input('amount_received')
            ]);

            // generate jurnal Journal Transaction
            $jHeader = new JournalHeader();
            $jHeader->transaction_code = AutoNumberGenerator::generateAutonumberTrx('JN');
            $jHeader->transaction_type = 'POS';
            $jHeader->transaction_date = $invoice->created_at;
            $jHeader->created_by = $invoice->created_by;
            $jHeader->branch_id = $invoice->branch_id;
            $jHeader->notes = 'Journal Transaksi Voucher Deposit ' . $invoice->code;
            $jHeader->reference_id = $invoice->id;
            $jHeader->reference_code = $invoice->code;
            $jHeader->status_id = 13;
            $jHeader->created_at = $invoice->created_at;
            $jHeader->updated_at = $invoice->created_at;
            $jHeader->save();

            $dbPayments = TransactionPayment::where('invoice_id', $invoice->id)->get();
            foreach ($dbPayments as $dbPayment){
                if($dbPayment->amount > 0){
                    $journalMap = JournalMap::where('branch_id', Auth::user()->branch_id)
                    ->where(function($q){
                        $q->where('transaction_type', 'POS_CASH')
                            ->orWhere('transaction_type', 'POS_NON_CASH');
                    })
                    ->where('room_type_id', 'like', '%'.$dbPayment->payment_type_id.'%')
                    ->first();

                    if($journalMap != null){
                        //insert journal
                        $coa = Coa::where('id', $journalMap->coa_id)->first();
                        if($coa != null){
                            //insert journal detail
                            $type = 'KREDIT';
                            if($journalMap->journal_type == 'DEBET'){
                                $type = 'DEBET';
                            }
                            $notes = 'Payment Transaksi ' . $header->code;
                            JournalUtilities::insertRecord($jHeader, Auth::user()->branch_id, $coa, $type, $dbPayment->amount, $notes, $header->code);
                        }
                    }
                    //insert jurnal deposit voucher
                    $coa = Coa::where('code', "2216")->first();
                    $value = $dbPayment->amount;

                    $notes = 'Deposit Voucher ' . ' : ' . $value;
                    JournalUtilities::insertRecord($jHeader, Auth::user()->branch_id, $coa, "KREDIT", $dbPayment->amount, $notes, $header->code);
                }
            }

            DB::commit();

            return \response([
                'success' => true,
                'receiptPayment' => route('admin.monitor_room.print', ['id' => $invoice->id]),
            ]);
        } catch (\Exception $e){
            DB::rollBack();

            Log::channel('voucher_pos')->error('[Checkout] - ' . $e);
            return Response::json($e->getMessage(), 500);
        }
    }
}
