<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\OpenBed;
use App\Jobs\OpenTerapis;
use App\Libs\AutoNumberGenerator;
use App\Libs\JournalUtilities;
use App\Libs\NotificationUtilities;
use App\Libs\Utilities;
use App\Models\AdminUser;
use App\Models\AppMembership;
use App\Models\Branch;
use App\Models\Configuration;
use App\Models\Coupon;
use App\Models\FcmToken;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\MonitoringLog;
use App\Models\Package;
use App\Models\Partner;
use App\Models\PosPaymentType;
use App\Models\PreweddingHeader;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\TransactionDetail;
use App\Models\TransactionDetailPartner;
use App\Models\TransactionHeader;
use App\Models\TransactionInvoice;
use App\Models\TransactionPayment;
use App\Models\User;
use App\Models\UserPointHistory;
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

class MonitorRoomController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request)
    {
        $oldData = json_decode($request->session()->get('cart') ?? '[]', true);
        $oldMainData = json_decode($request->session()->get('cart_data') ?? '{"exist":false}', true);

        $user     = Auth::guard('admin')->user();
        $userData = AdminUser::where('id', $user->id)->first();

        $countries = Utilities::getCountryCodes();

        $rooms = Room::where('branch_id', $userData->branch_id)
            ->where('status_id', '!=', 2)
            ->get();

        $packages = Package::where('branch_id', $userData->branch_id)
            ->get();

        $roomTypes = RoomType::orderBy('sequence')->get();

        $itemCategories = ItemCategory::where('branch_id', $userData->branch_id)
            ->whereIn('id', [2,27,28])
            ->orderBy('sequence')
            ->get();

        $items = Item::where('branch_id', $userData->branch_id)
            ->get();

        $configBuffer = Configuration::select('value_int')->where('key', 'buffer_end_time')->first();

        $packageArr = [];
        $studio = [];
        $salon = [];
        foreach ($roomTypes as $type) {
            if($type->id != 7){
                $filteredPackages = $packages->filter(fn ($package) => $package->room_type_id == $type->id);

                $packagesData = $filteredPackages->map(function ($package) use ($oldData) {
                    $itemData = null;
                    if($package->item_id != null && $package->item_id != 0){
                        $item = Item::find($package->item_id);
                        $itemData = [
                            'id' => $item->id,
                            'type' => 'item',
                            'code' => $item->code,
                            'name' => $item->name,
                            'price' => 0,
                            'isFreeAddOn'     => true,
                            'picked' => false,
                            'qty' => 1,
                            'status' => $item->status_id,
                            'img' => $item->image_path != null ? asset('storage/images/items/'. $item->image_path) : '',
                        ];
                    }

                    $qty = 0;
                    $picked = false;
                    foreach ($oldData as $data){
                        if($data['type'] == 'package' && $data['id'] == $package->id){
                            $picked = $data['picked'];
                            $qty = $data['qty'];
                            break;
                        }
                    }

                    return [
                        'id'           => $package->id,
                        'type'         => 'package',
                        'code'         => $package->code,
                        'name'         => $package->name,
                        'description'  => $package->description,
                        'room_type_id' => $package->room_type_id,
                        'duration'     => $package->duration,
                        'room_id' => null,
                        'price'        => $package->price,
                        'voucher'              => '',
                        'voucher_name'  => '',
                        'discount'             => 0,
                        'checking'          => false,
                        'isRequest'     => false,
                        'isFreeAddOn'     => false,
                        'partner_id'     => null,
                        'picked' => $picked,
                        'qty' => $qty,
                        'status' => $package->status_id,
                        'img' => $package->image_path != null ? asset('storage/images/items/'. $package->image_path) : '',
                        'item' => $itemData
                    ];
                } )->values()->all();

                usort($packagesData, function ($a, $b) {
                   return $a['name'] >= $b['name'];
                });

                if($type->id == 6){
                    $studio = [
                        'id'      => $type->id,
                        'name'    => $type->name,
                        'display' => $type->display,
                        'items' => $packagesData,
                    ];
                } else if($type->id == 4){
                    $salon = [
                        'id'      => $type->id,
                        'name'    => $type->name,
                        'display' => $type->display,
                        'packages' => $packagesData,
                    ];
                } else {
                    $packageArr[] = [
                        'id'      => $type->id,
                        'name'    => $type->name,
                        'display' => $type->display,
                        'packages' => $packagesData,
                    ];
                }
            }
        }


        $itemArr = [];
        foreach ($itemCategories as $category) {
            $filteredItems = $items->filter(fn ($item) => $item->category_id == $category->id);


            if (count($filteredItems) > 0) {
                $itemsData = $filteredItems->map(function ($item) use ($oldData, $category) {
                    $qty = 0;
                    $picked = false;
                    foreach ($oldData as $data){
                        if($data['type'] == 'item' && $data['id'] == $item->id){
                            $picked = $data['picked'];
                            $qty = $data['qty'];
                            break;
                        }
                    }

                    return [
                        'id' => $item->id,
                        'type' => 'item',
                        'code' => $item->code,
                        'name' => $item->name,
                        'price' => $item->price,
                        'isFreeAddOn'     => false,
                        'need_partner'     => $category->id == 27,
                        'partner_id'     => null,
                        'room_id' => null,
                        'duration' => $item->duration,
                        'voucher'              => '',
                        'voucher_name'  => '',
                        'discount'             => 0,
                        'picked' => $picked,
                        'qty' => $qty,
                        'status' => $item->status_id,
                        'img' => $item->image_path != null ? asset('storage/images/items/'. $item->image_path) : '',
                    ];
                })->values()->all();

                usort($itemsData, function ($a, $b) {
                    return $a['name'] >= $b['name'];
                });

                if($category->id == 27){
                    $packageArr[] = [
                        'id' => $category->id,
                        'name' => $category->name,
                        'packages' => $itemsData,
                    ];
                } else {
                    $itemArr[] = [
                        'id' => $category->id,
                        'name' => $category->name,
                        'items' => $itemsData,
                    ];
                }
            }
        }
        $packageArr[] = $salon;
        $itemArr[] = $studio;

//        $roomArr = [];
//        foreach ($rooms as $room) {
//            $data = [
//                'id'                       => $room->id,
//                'name'                     => $room->name,
//                'code'                     => $room->code,
//                'status_id'                => $room->status_id,
//                'number'                   => $room->number,
//                'element_class'            => 'custom-square mx-auto green',
//                'package_name'             => '',
//                'package_id'               => 0,
//                'duration'                 => 0,
//                'already_have_transaction' => false,
//                'member_id'                => 0,
//                'member_name'              => '',
//                'member_phone'             => '',
//                'timer'                    => 0,
//                'trx_status'               => 0,
//                'therapists'               => '',
//            ];
//
//            //check on transaction
//            $data['element_class'] = match ($room->status_id) {
//                11 => 'custom-square mx-auto yellow',
//                4 => 'custom-square mx-auto pink',
//                5 => 'custom-square mx-auto green',
//                6 => 'custom-square mx-auto red',
//            };
//
//            $header = TransactionHeader::with('transaction_details')
//                ->where('room_id', $room->id)
//                ->where('status_id', 8)
//                ->first();
//
//            if ($header != null) {
//                $data['trx_status'] = $header->status_id;
//
//                if (count($header->transaction_details) > 0) {
//                    $data['member_name'] = $header->type_customer ? $header->member_name . ' (' . $header->type_customer . ')' : $header->member_name;
//
//                    $trxDt = TransactionDetail::where('header_id', $header->id)
//                        ->where('package_id', '!=', null)
//                        ->where(function ($query) {
//                            $query->where('status_id', 8)
//                                ->orWhere('status_id', 3);
//                        })
//                        ->get();
//
//                    if ($trxDt != null) {
//                        $packages = Package::whereIn('id', array_column($trxDt->toArray(), 'package_id'))->get();
//                        if (! $packages->isEmpty()) {
////                        $package = Package::where('id', $trxDt->package_id)->first();
////                        if ($package != null) {
//                            $data['package_id']   = implode(', ', array_column($packages->toArray(), 'id'));
//                            $data['package_name'] = implode(', ', array_column($packages->toArray(), 'name'));
//
//                            if ($trxDt->first()->start_time != null) {
//                                $startTime = Carbon::parse($trxDt->first()->start_time);
//                                $endTime   = Carbon::parse($trxDt->first()->start_time);
//                                $endTime->addMinutes($packages->sum('duration'))->addMinutes((int) $configBuffer->value_int);
//
//                                $data['time_display'] = $startTime->format('H:i') . ' - ' . $endTime->format('H:i');
//                            }
//                        }
//
//                        $arrPartner = [];
//                        foreach ($trxDt as $dt) {
//                            if ($dt->transaction_detail_partner) {
//                                $partner = $dt->transaction_detail_partner->partner;
//                                $arrPartner[] = $partner->alias . ' (' . $partner->gender . ')';
//                            }
//                        }
//
//                        $data['therapists'] = implode(', ', $arrPartner);
//
//                        if ($room->status_id == 6) {
//                            $tmpDate      = Carbon::parse($trxDt->first()->start_time, 'Asia/Jakarta');
//                            $now          = Carbon::now('Asia/Jakarta');
//                            $timeConsumed = abs($tmpDate->diffInMinutes($now));
//
////                            dd($tmpDate->format('Y-m-d H:i:s'), $now->format('Y-m-d H:i:s'), $timeConsumed);
//                            $data['timer'] = $packages->sum('duration') - (int) $timeConsumed;
//
////                            dd($data['timer']);
//
//                            if ($data['timer'] <= 10) {
//                                $data['class'] = 'custom-square blink-bg';
//                            }
//                        }
//                    }
//
//                    $trxCount = TransactionDetail::where('header_id', $header->id)
//                        ->where('package_id', '!=', null)
//                        ->count();
//                    if ($trxCount > 0) {
//                        $data['already_have_transaction'] = true;
//                    }
//                }
//            }
//            $roomArr[] = $data;
//        }

        $branch = Branch::where('id', $userData->branch_id)->first();

//        dd($roomArr);

        return view('admin.monitor_room.index', compact('configBuffer','itemArr', 'packageArr', 'countries', 'oldMainData'));
    }

    public function updateSession(Request $request){
        $request->session()->put('cart', $request->input('cart'));
        $request->session()->put('cart_data', $request->input('data'));
    }

    public function checkout(Request $request){
        DB::beginTransaction();
        try {
            $time = explode(':', $request->input('time'));
            $start = Carbon::createFromFormat('d-m-Y', $request->input('date'))->setTime(intval($time[0]), intval($time[1]), 0);

//            $checkTransaction = TransactionHeader::whereBetween('start_time', [$start->toDateTimeString(), $end->toDateTimeString()])
//                ->orWhereBetween('end_time', [$start->toDateTimeString(), $end->toDateTimeString()])
//                ->where('branch_id', Auth::user()->branch_id)
//                ->where('room_id', $request->input('room_id'))
//                ->first();
//
//            if($checkTransaction){
//                return Response::json('Sudah terdapat transaksi pada waktu dan ruang tersebut!', 500);
//            }

            $member = User::where('phone', $request->input('member_phone'))->first();
            if(!$member){
                $member = User::create([
                    'name' => ucwords($request->input('member_name')),
                    'phone' => $request->input('member_phone'),
                    'gender' => $request->input('gender'),
                    'country' => $request->input('country'),
                    'password' => Hash::make('123456'),
                ]);
            }
            $trxCode = AutoNumberGenerator::generateAutonumberTrx('POS');
            $header = TransactionHeader::create([
                'branch_id' => Auth::user()->branch_id,
                'code' => $trxCode,
//                'room_id' => $request->input('room_id'),
                'member_id' => $member->id,
                'member_name' => ucwords($request->input('member_name')),
                'member_phone' => $request->input('member_phone'),
                'status_id' => 9,
                'start_time' => $start->toDateTimeString(),
                'foc_type' => 'no',
                'employee_id' => $request->input('employee'),
                'total_cust_male' => $request->input('total_cust_male') ?? 0,
                'total_cust_female' => $request->input('total_cust_female') ?? 0,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            $finalEnd =  Carbon::createFromFormat('d-m-Y', $request->input('date'))->setTime(intval($time[0]), intval($time[1]), 0);
            foreach ($request->input('details') as $detail) {
                if($detail['type'] == 'voucher') {
                    $voucher = Coupon::find($detail['id']);
                    $voucherHeader = UserVoucher::create([
                        'user_id' => $member->id,
                        'voucher_id' => $detail['id'],
                        'voucher_code' => $detail['name'],
                        'type' => 'voucher',
                        'terms' => $voucher->terms_condition
                    ]);

                    for($i = 0; $i < $detail['qty']; $i++){
                        UserVoucherCode::create([
                            'header_id' => $voucherHeader->id,
                            'code' => AutoNumberGenerator::generateVoucherCode($member),
                            'expired_date' => Carbon::parse($start)->addDays($voucher->duration)->toDateTimeString(),
                            'is_used' => 0
                        ]);
                    }
                }

                $voucher = null;
                $isVoucherPackage = false;
                if(!empty($detail['voucher'])){
                    $voucher = UserVoucherCode::where('code', $detail['voucher'])->first();
                    if($voucher){
                        $voucher->update([
                            'is_used' => 1
                        ]);

                        $voucherPackage = VoucherPackage::where('name', $detail['voucher_name'])->first();
                        if(!empty($voucherPackage)){
                            $isVoucherPackage = true;
                        }
                    }
                }

                $endTime = null;
                if(array_key_exists('duration', $detail)){
                    $endTime = $start->copy()->addMinutes((int)$detail['duration']);
                }

                //update Yansen
                $totalQty = $detail['type'] == 'item' ? $detail['qty'] : 1;
                $subtotal = $detail['type'] == 'item' ? ($detail['price'] * $detail['qty']) : $detail['price'];
                if($detail['type'] == 'package'){
                    $packages = Package::find($detail['id']);
                    //khusus untuk studio
                    if($packages->room_type_id == 6){
                        $totalQty = $detail['qty'];
                        $subtotal = $detail['price'] * $detail['qty'];
                    }
                }
                //update Yansen END

                $dt = TransactionDetail::create([
                    'header_id' => $header->id,
                    'package_id' => $detail['type'] == 'package' ? $detail['id'] : null,
                    'room_id' => array_key_exists('room_id', $detail) ?$detail['room_id'] : null,
                    'status_id' => 9,
                    'qty' => $totalQty,
                    'price' => $detail['price'],
                    'subtotal' => $subtotal,
                    'start_time' => $detail['type'] == 'package' || (array_key_exists('need_partner', $detail) && $detail['need_partner']) ? $start->toDateTimeString() : null,
                    'end_time' => $detail['type'] == 'package' || (array_key_exists('need_partner', $detail) && $detail['need_partner']) ? $endTime->toDateTimeString() : null,
                    'item_id' => $detail['type'] == 'item' ? $detail['id'] : null,
                    'coupon_id' => $detail['type'] == 'voucher' ? $voucherHeader->id : null,
                    'new_order_qty' => $totalQty,
                    'voucher_id' => $voucher != null ? $voucher->id : null,
                    'voucher_code' => $voucher != null ? $detail['voucher'] : null,
                    'voucher_name' => $voucher != null ? $detail['voucher_name'] : null,
                    'discount_amount' => $voucher != null ? $detail['discount'] : null,
                ]);


                if($endTime != null && $endTime->greaterThan($finalEnd)) {
                    $finalEnd = $endTime->copy();
                }

                if($detail['type'] == 'package' || (array_key_exists('need_partner', $detail) && $detail['need_partner'] == 'true')) {
                    TransactionDetailPartner::create([
                        'partner_id' => $detail['partner_id'],
                        'transaction_detail_id' => $dt->id,
                    ]);
                }
            }

            $header->update([
                'end_time' => $finalEnd
            ]);

            $vouchers = $request->input('vouchers') ?? [];
            $voucherStr = '';
            $couponStr = '';
            $voucherDiscountValue = 0;
            $grandTotal = $request->input('subtotal');
            if($request->input('is_use_voucher_code') == 'true') {
                $transactionDetails = TransactionDetail::where('header_id', $header->id)->get();

                foreach ($vouchers as $voucher) {
                    if($voucher['isSuccess'] != 0){
                        $voucherData = UserVoucherCode::where('code', $voucher['code'])->first();

                        if ($voucherData == null) {
                            DB::rollBack();
                            return Response::json('Voucher tidak ditemukan!', 400);
                        }

                        if ($voucherData->user_voucher->coupon->is_unlimited == 0) {
                            $voucherData->update([
                                'is_used' => 1
                            ]);
                        }

                        $couponStr .= '|' . $voucherData->user_voucher->coupon->id . '|';
                        $voucherStr .= '|' . $voucher['code'] . '|';

                        if ($voucherData->user_voucher->coupon->discount_percentage !== null) {
                            $subtotalNoAddOn = $transactionDetails->whereNotNull('package_id')->sum('subtotal');
                            $tmpValue        = $subtotalNoAddOn * $voucherData->user_voucher->coupon->discount_percentage / 100;
                            $voucherDiscountValue += $tmpValue;
                            $grandTotal -= $tmpValue ?? 0;
                        } else if ($voucherData->user_voucher->coupon->discount_amount != null) { //if not percentage, then subtract the nominal value from form
                            $voucherDiscountValue += $voucherData->user_voucher->coupon->discount_amount;
                            $grandTotal -= $voucherData->user_voucher->coupon->discount_amount ?? 0;
                        }
                    }
                }
            }

            $grandTotal += $request->input('pb2');
            $grandTotal += $request->input('custom_amount');

            $invoice = TransactionInvoice::create([
                'branch_id' => Auth::user()->branch_id,
                'header_ids' => implode('#', [$header->id]),
                'code' => $trxCode,
                'subtotal' => $request->input('subtotal'),
                'is_use_voucher_code' => $request->input('is_use_voucher_code') == 'true' ? 1 : 0,
                'voucher_code' => $voucherStr,
                'coupon_id' => $couponStr,
                'voucher_discount_value' => $voucherDiscountValue,
                'pb2' => $request->input('pb2'),
                'grandtotal' => $grandTotal,
                'rounding_amount' => $request->input('custom_amount'),
                'return_value' => $request->input('return_value'),
                'member_id' => $member->id,
                'member_name' => ucwords($request->input('member_name')),
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

            //add user point
            $pointUsed = (int) floor($grandTotal / 10000);
            Utilities::addTransactionPoint(
                $member->id,
                $pointUsed,
                $invoice->code,
                'TRANSACTION ' . $invoice->code
            );

            //Journal Transaction
            $journal = new JournalUtilities();
            if($isVoucherPackage){
                $journal->createJournalPosVoucherRealisasi($header->id);
            }
            else{
                $journal->createJournalPos($header->id);
            }

            DB::commit();

            return \response([
                'success' => true,
                'receiptPayment' => route('admin.monitor_room.print', ['id' => $invoice->id]),
            ]);
        } catch (\Exception $e){
            DB::rollBack();

            Log::channel('monitor_room')->error('[Monitor Room - Start Treatment] - ' . $e);
            return Response::json($e->getMessage(), 500);
        }
    }

    public function refreshData()
    {
        try {
            $user     = Auth::guard('admin')->user();
            $userData = AdminUser::where('id', $user->id)->first();

            $rooms = Room::where('branch_id', $userData->branch_id)
                ->where('status_id', '!=', 2)
                ->get();

            $configBuffer = Configuration::select('value_int')->where('key', 'buffer_end_time')->first();

            $roomArr = [];
            foreach ($rooms as $room) {
                $data = [
                    'id'                       => $room->id,
                    'name'                     => $room->name,
                    'code'                     => $room->code,
                    'status_id'                => $room->status_id,
                    'number'                   => $room->number,
                    'element_class'            => 'custom-square mx-auto green',
                    'package_name'             => '',
                    'package_id'               => 0,
                    'duration'                 => 0,
                    'already_have_transaction' => false,
                    'member_id'                => 0,
                    'member_name'              => '',
                    'member_phone'             => '',
                    'timer'                    => 0,
                    'trx_status'               => 0,
                    'therapists'               => '',
                ];

                //check on transaction
                $data['element_class'] = match ($room->status_id) {
                    11 => 'custom-square mx-auto yellow',
                    4 => 'custom-square mx-auto pink',
                    5 => 'custom-square mx-auto green',
                    6 => 'custom-square mx-auto red',
                };

                $header = TransactionHeader::with('transaction_details')
                    ->where('room_id', $room->id)
                    ->where('status_id', 8)
                    ->first();

                if ($header != null) {
                    $data['trx_status'] = $header->status_id;

                    if (count($header->transaction_details) > 0) {
                        $data['member_name'] = $header->type_customer ? $header->member_name . ' (' . $header->type_customer . ')' : $header->member_name;

                        $trxDt = TransactionDetail::where('header_id', $header->id)
                            ->where('package_id', '!=', null)
                            ->where(function ($query) {
                                $query->where('status_id', 8)
                                    ->orWhere('status_id', 3);
                            })
                            ->get();

                        if ($trxDt != null) {
                            $packages = Package::whereIn('id', array_column($trxDt->toArray(), 'package_id'))->get();
                            if (! $packages->isEmpty()) {
//                        $package = Package::where('id', $trxDt->package_id)->first();
//                        if ($package != null) {
                                $data['package_id']   = implode(', ', array_column($packages->toArray(), 'id'));
                                $data['package_name'] = implode(', ', array_column($packages->toArray(), 'name'));

                                if ($trxDt->first()->start_time != null) {
                                    $startTime = Carbon::parse($trxDt->first()->start_time);
                                    $endTime   = Carbon::parse($trxDt->first()->start_time);
                                    $endTime->addMinutes($packages->sum('duration'))->addMinutes((int) $configBuffer->value_int);

                                    $data['time_display'] = $startTime->format('H:i') . ' - ' . $endTime->format('H:i');
                                }
                            }

                            $arrPartner = [];
                            foreach ($trxDt as $dt) {
                                if ($dt->transaction_detail_partner) {
                                    $partner = $dt->transaction_detail_partner->partner;
                                    $arrPartner[] = $partner->alias . ' (' . $partner->gender . ')';
                                }
                            }

                            $data['therapists'] = implode(', ', $arrPartner);

                            if ($room->status_id == 6) {
                                $tmpDate      = Carbon::parse($trxDt->first()->start_time, 'Asia/Jakarta');
                                $now          = Carbon::now('Asia/Jakarta');
                                $timeConsumed = abs($tmpDate->diffInMinutes($now));

//                            dd($tmpDate->format('Y-m-d H:i:s'), $now->format('Y-m-d H:i:s'), $timeConsumed);
                                $data['timer'] = $packages->sum('duration') - (int) $timeConsumed;

//                            dd($data['timer']);

                                if ($data['timer'] <= 10) {
                                    $data['class'] = 'custom-square blink-bg';
                                }
                            }
                        }

                        $trxCount = TransactionDetail::where('header_id', $header->id)
                            ->where('package_id', '!=', null)
                            ->count();
                        if ($trxCount > 0) {
                            $data['already_have_transaction'] = true;
                        }
                    }
                }
                $roomArr[] = $data;
            }

            $branch = Branch::where('id', $userData->branch_id)->first();

            return Response::json($roomArr);
        } catch (\Exception $ex) {
            Log::channel('monitor_room')->error('[Monitor Room Refresh Data] - ' . $ex);
            return Response::json($ex, 500);
        }
    }

    public function startTreatmentMultiple(Request $request)
    {
        $datas = $request->input('details');
        try {
            DB::beginTransaction();
            foreach($datas as $data){

                $user = Auth::guard('admin')->user();
                $userData = AdminUser::find($user->id);

                $package = Package::find($data['package_id']);
                if($package == null){
                    DB::rollBack();
                    return Response::json('Treatment Tidak ditemukan!', 400);
                }

                $room = Room::find($data['room_id']);
                if($room == null){
                    DB::rollBack();
                    return Response::json('Room tidak ditemukan!', 400);
                }

                $partner = Partner::find($data['partner_id']);
                if($partner == null){
                    DB::rollBack();
                    return Response::json('Terapis tidak ditemukan!', 400);
                }

                // if ($request->filled('booking_phone')) {
                //     $member = User::where('phone', '=', $request->input('booking_phone'))->first();
                //     if ($member == null) {
                //         return Response::json('Member tidak ditemukan!', 400);
                //     }
                // }

                $header = new TransactionHeader();
                $header->room_id = $room->id;
                $header->member_id = $member->id ?? null;
                $header->member_name = $member->name ?? $data['customer_name'];
                $header->member_phone = $member->phone ?? null;
                $header->code = AutoNumberGenerator::generateAutonumberTrx('POS');
                $header->branch_id = $room->branch_id;
                $header->created_by = $userData->id;
                $header->status_id = 8;
                $header->start_time = Carbon::now('Asia/Jakarta');

                $header->save();
                // Log::channel('monitor_room')->error('Header ID = ' . $header->id);

                // Create details for package
                $detail = new TransactionDetail();
                $detail->header_id = $header->id;
                $detail->package_id = $package->id;
                $detail->qty = 1;
                $detail->price = $package->price;
                $detail->subtotal = $detail->price;
                $detail->status_id = 3;
                $detail->save();
                // Log::channel('monitor_room')->error('Detail ID = ' . $detail->id);

                // $addons = $request->input('details') ?? [];
                // foreach ($addons as $addon) {
                //     $item = Item::find($addon['item_id']);

                //     if ($item != null) {
                //         $detailAddOn = new TransactionDetail();
                //         $detailAddOn->header_id = $header->id;
                //         $detailAddOn->item_id = $item->id;
                //         $detailAddOn->qty = $addon['qty'];
                //         $detailAddOn->price = $item->price;
                //         $detailAddOn->subtotal = $item->price * $addon['qty'];
                //         $detailAddOn->status_id = 1;
                //         $detailAddOn->save();

                //         $qty = $addon['qty'];
                //         $inventories = Inventory::where('item_id', $item->id)->get();
                //         foreach ($inventories as $inventory){
                //             if($qty > $inventory->qty){
                //                 $qty -= $inventory->qty;
                //                 Utilities::updateStock(Auth::guard('admin')->user()->branch_id, 'sub', $inventory->qty, $item->id, $inventory->warehouse_id, 'Treatment', 'Memulai Treatment '.$header->code);
                //             }
                //             else {
                //                 Utilities::updateStock(Auth::guard('admin')->user()->branch_id, 'sub', $qty, $item->id, $inventory->warehouse_id, 'Treatment', 'Memulai Treatment '.$header->code);
                //                 break;
                //             }
                //         }
                //     }
                // }
                // Create Details for Add On if exists
    //            if ($partner->partner_level->item_id != null) {
    //                $addOn = Item::where('id', $partner->partner_level->item_id)->first();
    //
    //                if ($addOn != null) {
    //                    $detailAddOn = new TransactionDetail();
    //                    $detailAddOn->header_id = $header->id;
    //                    $detailAddOn->item_id = $addOn->id;
    //                    $detailAddOn->qty = 1;
    //                    $detailAddOn->price = $request->input('is_free_add_on') === 'true' ? 0 : $addOn->price;
    //                    $detailAddOn->subtotal = $request->input('is_free_add_on') === 'true' ? 0 : $addOn->price;
    //                    $detailAddOn->status_id = 1;
    //                    $detailAddOn->save();
    //                }
    //            }

                $trxPartner = new TransactionDetailPartner();
                $trxPartner->transaction_detail_id = $detail->id;
                $trxPartner->partner_id = $partner->id;
                $trxPartner->is_main = 1;
                $trxPartner->book_name = $partner->book_name;
                $trxPartner->save();
                // Log::channel('monitor_room')->error('trxPartner ID = ' . $trxPartner->id);

                //update room partner and locker
                $room->status_id = 6;
                $room->save();

                $package->status_id = 6;
                $package->save();

                $partner->status_id = 6;
                $partner->book_name = '';
                $partner->save();

                $detail->start_time = Carbon::now('Asia/Jakarta');
                $detail->status_id = 8;
                $detail->save();

                //Update Room Status
                $duration = $package->duration * 60;
                $room->duration = $duration;
                $room->status_id = 6;
                $room->save();

                $log = new MonitoringLog();
                $log->partner_id = $partner->id;
                $log->room_id = $room->id;
                $log->admin_user_id = $user->id;
                $log->created_at = Carbon::now('Asia/Jakarta');
                $log->save();

    //            $tmpBook->status_id = 9;
    //            $tmpBook->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil memulai treatment!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::channel('monitor_room')->error('[Monitor Room - Start Treatment] - ' . $e);
            return Response::json($e->getMessage(), 500);
        }
    }

    public function startTreatment(Request $request)
    {
        try {
            if (!$request->filled('package_id') || !$request->filled('partner_id') || !$request->filled('room_id') || !$request->filled('booking_name'))
            {
                return Response::json('Invalid Parameters!', 400);
            }

            DB::beginTransaction();

            $user = Auth::guard('admin')->user();
            $userData = AdminUser::find($user->id);

            $package = Package::find($request->input('package_id'));
            if($package == null){
                return Response::json('Treatment Tidak ditemukan!', 400);
            }

            $room = Room::find($request->input('room_id'));
            if($room == null){
                return Response::json('Room tidak ditemukan!', 400);
            }

            $partner = Partner::find($request->input('partner_id'));
            if($partner == null){
                return Response::json('Terapis tidak ditemukan!', 400);
            }

            if ($request->filled('booking_phone')) {
                $member = User::where('phone', '=', $request->input('booking_phone'))->first();
                if ($member == null) {
                    return Response::json('Member tidak ditemukan!', 400);
                }
            }

            $header = new TransactionHeader();
            $header->room_id = $room->id;
            $header->member_id = $member->id ?? null;
            $header->member_name = $member->name ?? $request->input('booking_name');
            $header->member_phone = $member->phone ?? null;
            $header->code = AutoNumberGenerator::generateAutonumberTrx('POS');
            $header->branch_id = $room->branch_id;
            $header->created_by = $userData->id;
            $header->status_id = 8;
            $header->start_time = Carbon::now('Asia/Jakarta');

            $header->save();

            // Create details for package
            $detail = new TransactionDetail();
            $detail->header_id = $header->id;
            $detail->package_id = $package->id;
            $detail->qty = 1;
            $detail->price = $package->price;
            $detail->subtotal = $detail->price;
            $detail->status_id = 3;
            $detail->save();

            $addons = $request->input('details') ?? [];
            foreach ($addons as $addon) {
                $item = Item::find($addon['item_id']);

                if ($item != null) {
                    $detailAddOn = new TransactionDetail();
                    $detailAddOn->header_id = $header->id;
                    $detailAddOn->item_id = $item->id;
                    $detailAddOn->qty = $addon['qty'];
                    $detailAddOn->price = $item->price;
                    $detailAddOn->subtotal = $item->price * $addon['qty'];
                    $detailAddOn->status_id = 1;
                    $detailAddOn->save();

                    $qty = $addon['qty'];
                    $inventories = Inventory::where('item_id', $item->id)->get();
                    foreach ($inventories as $inventory){
                        if($qty > $inventory->qty){
                            $qty -= $inventory->qty;
                            Utilities::updateStock(Auth::guard('admin')->user()->branch_id, 'sub', $inventory->qty, $item->id, $inventory->warehouse_id, 'Treatment', 'Memulai Treatment '.$header->code);
                        }
                        else {
                            Utilities::updateStock(Auth::guard('admin')->user()->branch_id, 'sub', $qty, $item->id, $inventory->warehouse_id, 'Treatment', 'Memulai Treatment '.$header->code);
                            break;
                        }
                    }
                }
            }
            // Create Details for Add On if exists
//            if ($partner->partner_level->item_id != null) {
//                $addOn = Item::where('id', $partner->partner_level->item_id)->first();
//
//                if ($addOn != null) {
//                    $detailAddOn = new TransactionDetail();
//                    $detailAddOn->header_id = $header->id;
//                    $detailAddOn->item_id = $addOn->id;
//                    $detailAddOn->qty = 1;
//                    $detailAddOn->price = $request->input('is_free_add_on') === 'true' ? 0 : $addOn->price;
//                    $detailAddOn->subtotal = $request->input('is_free_add_on') === 'true' ? 0 : $addOn->price;
//                    $detailAddOn->status_id = 1;
//                    $detailAddOn->save();
//                }
//            }

            $trxPartner = new TransactionDetailPartner();
            $trxPartner->transaction_detail_id = $detail->id;
            $trxPartner->partner_id = $partner->id;
            $trxPartner->is_main = 1;
            $trxPartner->book_name = $partner->book_name;
            $trxPartner->save();

            //update room partner and locker
            $room->status_id = 6;
            $room->save();

            $package->status_id = 6;
            $package->save();

            $partner->status_id = 6;
            $partner->book_name = '';
            $partner->save();

            $detail->start_time = Carbon::now('Asia/Jakarta');
            $detail->status_id = 8;
            $detail->save();

            //Update Room Status
            $duration = $package->duration * 60;
            $room->duration = $duration;
            $room->status_id = 6;
            $room->save();

            $log = new MonitoringLog();
            $log->partner_id = $partner->id;
            $log->room_id = $room->id;
            $log->admin_user_id = $user->id;
            $log->created_at = Carbon::now('Asia/Jakarta');
            $log->save();

//            $tmpBook->status_id = 9;
//            $tmpBook->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil memulai treatment!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::channel('monitor_room')->error('[Monitor Room - Start Treatment] - ' . $e);
            return Response::json($e, 500);
        }
    }

    public function getDetailRoomTransaction(Request $request)
    {
        try {
            if (!$request->filled('room_id')) {
                return Response::json('Invalid parameters!');
            }

            $user = Auth::guard('admin')->user();
            $userData = AdminUser::where('id', $user->id)->first();

            $trxHeader = TransactionHeader::where('room_id', $request->input('room_id'))
                ->where('branch_id', $userData->branch_id)
                ->where('status_id', 8)
                ->first();

            if ($trxHeader == null) {
                return Response::json('Tidak ada transaksi berjalan pada room tersebut!', 200);
            }

            $trxDetails = TransactionDetail::where('header_id', $trxHeader->id)
                ->orderBy('id', 'asc')
                ->get();
//            $trxDetails = $trxDetails->sortByDesc(function ($item) {
//                return !is_null($item->start_time);
//            });

            $trxTherapist = TransactionDetailPartner::where('transaction_detail_id', $trxDetails[0]->id)->first();

            $startTime = Carbon::parse($trxDetails[0]->start_time)->format('H:i');
            $endTime = Carbon::parse($trxDetails[0]->start_time)
                ->addMinutes($trxDetails[0]->package->duration)
                ->format('H:i');

            $room = [
                'header_id' => $trxHeader->id,
                'id' => $trxHeader->room_id,
                'member_id' => $trxHeader->member_id ?? '',
                'member_name' => $trxHeader->member_name,
                'member_phone' => $trxHeader->member_phone ?? '',
                'therapist_name' => $trxTherapist->partner->name . ' - ' . $trxTherapist->partner->partner_level->name,
                'time' => $startTime . ' - ' . $endTime,
            ];

            $grandTotal = 0;

            $transactions = [];
            foreach ($trxDetails as $detailData) {
                $data = [
                    'id' => $detailData->id,
                    'package_id' => $detailData->package_id ?? '',
                    'package_name' => $detailData->package_id != null ? $detailData->package->name . ' - ' . $detailData->package->duration . ' Menit' : $detailData->item->name,
                    'description' => $detailData->package_id != null
                        ? $detailData->package->description
                        : 'ADD ON - ' . $detailData->item->name,
                    'item_id' => $detailData->item_id ?? '',
                    'qty' => $detailData->qty ?? 0,
                    'price' => $detailData->price ?? 0,
                    'discount' => $detailData->discount_amount ?? 0,
                    'subtotal' => $detailData->subtotal ?? 0,
                ];

                $grandTotal += $detailData->subtotal;

                array_push($transactions, $data);
            }

            return Response::json([
                'room' => $room,
                'transactions' => $transactions,
                'grand_total' => $grandTotal,
            ]);

        } catch (\Exception $ex) {
            Log::channel('monitor_room')->error('[Monitor Room - Get Detail Room Transaction] - ' . $ex);
            return Response::json($ex, 500);
        }
    }

    public function addAddon(Request $request){
        try {
            DB::beginTransaction();

            $header = TransactionHeader::find($request->input('id'));

            $addons = $request->input('details') ?? [];
            foreach ($addons as $addon) {
                $item = Item::find($addon['item_id']);

                if ($item != null) {
                    $detailAddOn = new TransactionDetail();
                    $detailAddOn->header_id = $header->id;
                    $detailAddOn->item_id = $item->id;
                    $detailAddOn->qty = $addon['qty'];
                    $detailAddOn->price = $item->price;
                    $detailAddOn->subtotal = $item->price * $addon['qty'];
                    $detailAddOn->status_id = 1;
                    $detailAddOn->save();

                    $qty = $addon['qty'];
                    $inventories = Inventory::where('item_id', $item->id)->get();
                    foreach ($inventories as $inventory){
                        if($qty > $inventory->qty){
                            $qty -= $inventory->qty;
                            Utilities::updateStock(Auth::guard('admin')->user()->branch_id, 'sub', $inventory->qty, $item->id, $inventory->warehouse_id, 'Treatment', 'Add On untuk Treatment '.$header->code);
                        }
                        else {
                            Utilities::updateStock(Auth::guard('admin')->user()->branch_id, 'sub', $qty, $item->id, $inventory->warehouse_id, 'Treatment', 'Add On untuk Treatment '.$header->code);
                            break;
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil menambah Add On!',
            ]);
        } catch (\Exception $exception) {
            DB::rollBack();

            Log::channel('monitor_room')->error('[Monitor Room - Get Detail Room Transaction] - ' . $exception);
            return Response::json($exception, 500);
        }
    }

    public function switchRoom(Request $request)
    {
        try {
            if (!$request->filled('selected_room_change_id') || !$request->filled('room_id')) {
                return Response::json('Invalid parameters!', 400);
            }

            $user = Auth::guard('admin')->user();
            $userData = AdminUser::find($user->id);

            $header = TransactionHeader::where('room_id', $request->input('room_id'))->where('branch_id', $userData->branch_id)->where('status_id', 8)->first();
            if ($header == null) {
                return Response::json('Room tidak memiliki transaksi berjalan!', 400);
            }

            $selectedRoomChange = Room::where('id', $request->input('selected_room_change_id'))->first();
            if($selectedRoomChange == null){
                return Response::json('Room tidak ditemukan!', 400);
            }

            if($selectedRoomChange->status_id == 6){
                return Response::json('Room sedang dipakai!', 400);
            }

            DB::beginTransaction();

            $currRoom = Room::where('id', $header->room_id)->first();
            $header->room_id = $selectedRoomChange->id;
            $header->updated_at = Carbon::now('Asia/Jakarta');
            $header->save();

            $currRoom->status_id = 5;
            $currRoom->save();
            $selectedRoomChange->status_id = 6;
            $selectedRoomChange->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mengganti room!',
            ]);
        } catch (\Exception $ex) {
            DB::rollBack();

            Log::channel('monitor_room')->error('[Monitor Room - Switch Room] - ' . $ex);
            return Response::json($ex, 500);
        }
    }

    public function switchPackage(Request $request)
    {
        try {
            if (!$request->filled('selected_package_change_id') || !$request->filled('room_id')) {
                return Response::json('Invalid parameters!', 400);
            }

            $user = Auth::guard('admin')->user();
            $userData = AdminUser::find($user->id);

            $header = TransactionHeader::where('room_id', $request->input('room_id'))->where('branch_id', $userData->branch_id)->where('status_id', 8)->first();
            if ($header == null) {
                return Response::json('Room tidak memiliki transaksi berjalan!', 400);
            }

            $detail = TransactionDetail::where('header_id', $header->id)
                ->whereNotNull('package_id')
                ->first();

            $package = Package::where('id', $request->input('selected_package_change_id'))->first();
            $currPackage = Package::where('id', $detail->package_id)->first();

            if ($currPackage->id == $package->id) {
                return Response::json('Paket yang dipilih sama!', 400);
            }

            DB::beginTransaction();

            $header->updated_by = $userData->id;
            $detail->package_id = $package->id;
            $detail->price = $package->price;
            $detail->subtotal = $package->price;

            $detail->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mengganti paket!',
            ]);
        } catch (\Exception $ex) {
            DB::rollBack();

            Log::channel('monitor_room')->error('[Monitor Room - Switch Package] - ' . $ex);
            return Response::json($ex, 500);
        }
    }

    public function switchPartner(Request $request)
    {
        try {
            if (!$request->filled('selected_partner_change_id') || !$request->filled('room_id')) {
                return Response::json('Invalid parameters!', 400);
            }

            $user = Auth::guard('admin')->user();
            $userData = AdminUser::find($user->id);

            $partner = Partner::where('id', $request->input('selected_partner_change_id'))->first();
            if ($partner == null) {
                return Response::json('Terapis tidak ditemukan!', 400);
            }

            $header = TransactionHeader::where('room_id', $request->input('room_id'))->where('branch_id', $userData->branch_id)->where('status_id', 8)->first();
            if ($header == null) {
                return Response::json('Room tidak memiliki transaksi berjalan!', 400);
            }

            $detail = TransactionDetail::where('header_id', $header->id)
                ->whereNotNull('package_id')
                ->first();

            DB::beginTransaction();

            if ($detail !== null) {
                $dtPartner = TransactionDetailPartner::where('transaction_detail_id', $detail->id)->first();

                if (!$dtPartner) {
                    return Response::json('Terapis data tidak ditemukan!', 400);
                }

                if ($partner->id == $dtPartner->partner_id) {
                    return Response::json('Terapis yang dipilih sama!', 400);
                }

                $oldPartner = Partner::find($dtPartner->partner_id);

                if (!$oldPartner) {
                    return Response::json('Terapis sebelumnya tidak ditemukan!', 400);
                }

                $oldDetailAddOn = TransactionDetail::where('header_id', $header->id)
                    ->whereNotNull('item_id')
                    ->first();

                $newItemId = $partner->partner_level->item_id ?? null;

                if ($oldPartner->level_id !== $partner->level_id) {
                    // If there was an add-on for the old partner, remove it
                    if ($oldDetailAddOn) {
                        $oldDetailAddOn->delete();
                    }

                    // Add new add-on if applicable
                    if ($newItemId) {
                        $addOn = Item::find($newItemId);

                        if ($addOn) {
                            $detailAddOn = new TransactionDetail();
                            $detailAddOn->header_id = $header->id;
                            $detailAddOn->item_id = $addOn->id;
                            $detailAddOn->qty = 1;
                            $detailAddOn->price = $request->input('is_free_add_on') === 'true' ? 0 : $addOn->price;
                            $detailAddOn->subtotal = $detailAddOn->price;
                            $detailAddOn->status_id = 1;
                            $detailAddOn->save();
                        }
                    }
                } else {
                    // If the level is the same, update the existing add-on price
                    if ($oldDetailAddOn && $newItemId) {
                        $addOn = Item::find($newItemId);

                        if ($addOn) {
                            $oldDetailAddOn->update([
                                'price' => $request->input('is_free_add_on') === 'true' ? 0 : $addOn->price,
                                'subtotal' => $request->input('is_free_add_on') === 'true' ? 0 : $addOn->price,
                            ]);
                        }
                    } elseif (!$oldDetailAddOn && $newItemId) {
                        // If no add-on exists but should be added
                        $addOn = Item::find($newItemId);

                        if ($addOn) {
                            TransactionDetail::create([
                                'header_id' => $header->id,
                                'item_id' => $addOn->id,
                                'qty' => 1,
                                'price' => $request->input('is_free_add_on') === 'true' ? 0 : $addOn->price,
                                'subtotal' => $request->input('is_free_add_on') === 'true' ? 0 : $addOn->price,
                                'status_id' => 1,
                            ]);
                        }
                    }
                }
            }

            $oldPartner->status_id = 5;
            $oldPartner->updated_by = $user->id;
            $oldPartner->save();

            $dtPartner->partner_id = $partner->id;
            $dtPartner->save();

            $partner->status_id = 6;
            $partner->updated_by = $user->id;
            $partner->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mengganti terapis!',
            ]);
        } catch (\Exception $ex) {
            DB::rollBack();

            Log::channel('monitor_room')->error('[Monitor Room - Switch Partner] - ' . $ex);
            return Response::json($ex, 500);
        }
    }

    public function getPaymentDetail(Request $request)
    {
        try {
            $user = Auth::guard('admin')->user();
            $userData = AdminUser::where('id', $user->id)->first();

            $header = TransactionHeader::where('room_id', $request->input('room_id'))
                ->where('status_id', 8)
                ->where('branch_id', $userData->branch_id)
                ->first();

            if ($header == null) {
                return Response::json('Room tidak memiliki transaksi berjalan!');
            }

            $details = TransactionDetail::where('header_id', $header->id)
                ->orderBy('id', 'asc')
                ->get();

            $transactions = [
                'id' => $header->id,
                'roomId' => $header->room_id,
                'memberId' => $header->member_id ?? null,
                'memberName' => $header->member_name,
                'memberPhone' => $header->member_phone ?? '',
                'details' => [],
            ];

            $member = User::where('id', $header->member_id)->first();

            if ($member != null) {
                $membership = AppMembership::where('id', $member->membership_id)->first();
            }

            foreach ($details as $detail) {
                $discount_membership = 0;

                if ($member != null) {
                    if ($detail->package_id && $detail->package->duration >= 60 && $membership->discount_percentage > 0) {
                        $discount_membership = $detail->subtotal * $membership->discount_percentage / 100;
                    }
                }

                $data = [
                    'id' => $detail->id,
                    'packageName' => $detail->package_id ? $detail->package->name : $detail->item->name,
                    'subtotal' => $detail->subtotal,
                    'discount_membership' => $discount_membership,
                    'is_add_on' => (bool) $detail->item_id,
                ];

                $transactions['details'][] = $data;
            }

            return Response::json($transactions);
        } catch (\Exception $ex) {
            Log::channel('monitor_room')->error('[Monitor Room - Get Payment Detail] - ' . $ex);
            return Response::json($ex, 500);
        }
    }

    public function updateTransactionPaymentDetail(Request $request)
    {
        try {
            $user = Auth::guard('admin')->user();
            $userData = AdminUser::where('id', $user->id)->first();

            $header = TransactionHeader::where('room_id', $request->input('room_id'))
                ->where('status_id', 8)
                ->where('branch_id', $userData->branch_id)
                ->first();

            if ($header == null) {
                return Response::json('Room tidak memiliki transaksi berjalan!');
            }

            $details = TransactionDetail::where('header_id', $header->id)
                ->orderBy('id', 'asc')
                ->get();

            $member = User::where('id', $request->input('member_id'))->first();

            if ($user != null) {
                $membership = AppMembership::where('id', $member->membership_id)->first();
            }

            $transactions = [
                'id' => $header->id,
                'roomId' => $header->room_id,
                'memberId' => $member->id ?? null,
                'memberName' => $member->name,
                'memberPhone' => $member->phone ?? '',
                'details' => [],
            ];

            foreach ($details as $detail) {
                $discount_membership = 0;

                if ($member != null) {
                    if ($detail->package_id && $detail->package->duration >= 60 && $membership->discount_percentage > 0) {
                        $discount_membership = $detail->subtotal * $membership->discount_percentage / 100;
                    }
                }

                $data = [
                    'id' => $detail->id,
                    'packageName' => $detail->package_id ? $detail->package->name : $detail->item->name,
                    'subtotal' => $detail->subtotal,
                    'discount_membership' => $discount_membership,
                    'is_add_on' => (bool) $detail->item_id,
                ];

                $transactions['details'][] = $data;
            }

            return Response::json($transactions);
        } catch (\Exception $exception) {
            Log::channel('monitor_room')->error('[Monitor Room - Update Transaction Payment Detail] - ' . $exception);
            return Response::json($exception, 500);
        }
    }

    public function payment(Request $request)
    {
        try {
            if (!$request->filled('room_id')
                || !$request->filled('transactions')
                || !$request->filled('selected_payment_method')
                || !$request->filled('amount_received')
                || !$request->filled('return_value')
                || !$request->filled('is_use_voucher_code')
            ) {
                return Response::json('Invalid Parameters!', 400);
            }

            DB::beginTransaction();

            $user = Auth::guard('admin')->user();
            $userData = AdminUser::where('id', $user->id)->first();

            $transactions = collect($request->input('transactions'));

            $roomIds = array_column($transactions->toArray(), 'roomId');
            $memberIds = array_column($transactions->toArray(), 'memberId');
            $memberNames = array_column($transactions->toArray(), 'memberName');
            $memberPhones = array_column($transactions->toArray(), 'memberPhone');
//            dd($memberIds, $memberNames, $memberPhones);

            $transactionHeaders = TransactionHeader::whereIn('room_id', $roomIds)
                ->where('status_id', 8)
                ->get();

            if (array_diff($roomIds, array_column($transactionHeaders->toArray(), 'room_id'))) {
                return Response::json('Room tidak memiliki transaksi berjalan!', 400);
            }

            foreach ($transactionHeaders as $index => $header) {
                $member = User::where('id', $memberIds[$index])->first();

                $header->member_id = $member != null ? $member->id : null;
                $header->member_name = $member != null ? $member->name : $memberNames[$index];
                $header->member_phone = $member != null ? $member->phone : null;
                $header->member_membership_id = $member != null ? $member->membership_id : null;
                $header->member_membership_code = $member != null ? $member->membership->code : null;
            }

            $implTransactionHeaders = implode('#', array_column($transactionHeaders->toArray(), 'id'));

            $transactionDetails = TransactionDetail::whereIn('header_id', array_column($transactionHeaders->toArray(), 'id'))->get();

            $subtotal = $transactionDetails->sum('subtotal');

            $grandTotal = $subtotal;

            $voucher = null;
            $voucherDiscountValue = 0;

            if ($request->input('is_use_voucher_code') == 'true' && $request->filled('voucher_code')) {
                $voucher = UserVoucherCode::where('code', $request->input('voucher_code'))->first();

                if ($voucher == null) {
                    return Response::json('Voucher tidak ditemukan!', 400);
                }

                if ($voucher->user_voucher->coupon->discount_percentage !== null) {
                    $subtotalNoAddOn = $transactionDetails->whereNotNull('package_id')->sum('subtotal');
                    $voucherDiscountValue = $subtotalNoAddOn * $voucher->user_voucher->coupon->discount_percentage / 100;
                } else {
                    $voucherDiscountValue = $voucher->user_voucher->coupon->discount_amount;
                }

                $grandTotal -= $voucherDiscountValue ?? 0;
            }

            if ($request->input('is_use_voucher_code') !== 'true') {
                $totalDiscountMembership = 0;

                foreach ($transactions as $transaction) {
                    foreach ($transaction['details'] as $detail) {
                        if ($detail['is_add_on'] == 'false') {
                            $totalDiscountMembership += $detail['discount_membership'];
                        }
                    }
                }

                $voucherDiscountValue += $totalDiscountMembership;

                $grandTotal -= $voucherDiscountValue ?? 0;
            }

            // Create Invoice
            $invoice = new TransactionInvoice();
            $invoice->branch_id = $userData->branch_id;
            $invoice->header_ids = $implTransactionHeaders;
            $invoice->code = AutoNumberGenerator::generateAutonumberTrx('POS');
            $invoice->subtotal = $subtotal;
            $invoice->is_use_voucher_code = $request->input('is_use_voucher_code') == 'true' ? 1 : 0;
            $invoice->coupon_id = $voucher->user_voucher->coupon->id ?? null;
            $invoice->voucher_code = $request->input('voucher_code');
            $invoice->voucher_discount_value = $voucherDiscountValue;
            $invoice->grandtotal = $grandTotal;
            $invoice->return_value = $request->input('return_value');
            $invoice->member_id = $memberIds[0] ?? null;
            $invoice->member_name = $memberNames[0];
            $invoice->member_phone = $memberPhones[0] ?? null;
            $invoice->status_id = 9;
            $invoice->created_by = $userData->id;
            $invoice->save();

            // Insert Payment Method
            $paymentMethod = PosPaymentType::where('id', $request->input('selected_payment_method'))->first();
            if ($paymentMethod == null) {
                return Response::json('Metode pembayaran tidak tersedia!', 400);
            }

            $payment = new TransactionPayment();
            $payment->branch_id = $userData->branch_id;
            $payment->invoice_id = $invoice->id;
            $payment->payment_type_id = $paymentMethod->id;
            $payment->payment_type_code = $paymentMethod->code;
            $payment->amount = $request->input('amount_received');
            $payment->created_by = $userData->id;
            $payment->save();

            // Count point for membership, include history
            foreach ($memberIds as $id) {
                if ($id != null) {
                    $member = User::where('id', $id)->first();
                    $membership = AppMembership::where('id', $member->membership_id)->first();

                    if ($member == null) {
                        return Response::json('Member tidak ditemukan!', 400);
                    }

                    $memberTransaction = $transactions->where('memberId', $id)->first();

                    $memberSubtotal = 0;
                    foreach ($memberTransaction['details'] as $detail) {
                        $memberSubtotal += $detail['subtotal'];
                        if (!$detail['is_add_on']) {
                            $memberSubtotal -= $detail['discount_membership'];
                        }
                    }

                    if ($voucher != null) {
                        $discountProportion = ($memberSubtotal / $grandTotal) * $voucherDiscountValue;
                        $memberSubtotal -= $discountProportion;
                    }

                    $pointMember = floor($memberSubtotal / $membership->point_conversion);

                    $memberPointHistory = new UserPointHistory();
                    $memberPointHistory->branch_id = $userData->branch_id;
                    $memberPointHistory->branch_name = $userData->branch->name;
                    $memberPointHistory->user_id = $member->id;
                    $memberPointHistory->user_name = $member->name;
                    $memberPointHistory->user_email = $member->email;
                    $memberPointHistory->user_phone = $member->phone;
                    $memberPointHistory->amount_add = $pointMember;
                    $memberPointHistory->amount_result = $member->point + $pointMember;
                    $memberPointHistory->reference_1 = $invoice->code;
                    $memberPointHistory->save();

                    $member->point += $pointMember;
                    $member->save();
                }
            }

            // Update Voucher Usage
            if ($voucher != null) {
                if ($voucher->user_voucher->coupon->is_unlimited == 0) {
                    $voucher->is_used = 1;
                    $voucher->save();

                    $tmp = [
                        'id'                => $voucher->user_voucher->id,
                        'code'              => $voucher->user_voucher->voucher_code,
                        'img'               => $voucher->user_voucher->coupon->img_path ? asset("images/coupons/{$voucher->user_voucher->coupon->img_path}") : '-',
                        'terms_condition'   => $voucher->user_voucher->terms_condition ?? '-',
                        'e_vouchers'        => [],
                    ];

                    $vcDts = UserVoucherCode::where('header_id', $voucher->user_voucher->id)->get();
                    foreach($vcDts as $vcDt){
                        $tmp['e_vouchers'][] = [
                            'code'      => $vcDt->code,
                            'is_used'   => $vcDt->is_used,
                            'exp_date'  => Carbon::parse($vcDt->expired_date)->format('d-m-Y H:i'),
                        ];
                    }

                    $notification = [
                        'title' => 'Voucher anda berhasil di Redeem',
                        'body' => 'Voucher anda dengan kode "'. $voucher->code .'" telah di redeem.',
                    ];

                    $bodyData = [
                        'title' => 'Voucher anda berhasil di Redeem',
                        'body' => 'Voucher anda dengan kode "'. $voucher->code .'" telah di redeem.',
                        'type' => 'voucher',
                        'model' => $tmp
                    ];

                    $tokens = FcmToken::where('user_id', $voucher->user_voucher->user_id)->get();

                    foreach ($tokens as $token){
                        Log::channel('firebase')->error('Sending Notification : ' . $token->token);
                        NotificationUtilities::sendNotification($token->token, $notification, $bodyData);
                    }
                }
            }

            // Update Room Status
            $rooms = Room::whereIn('id', $roomIds)->get();
            foreach ($rooms as $room) {
                $room->status_id = 4;
                $room->duration = 0;
                $room->updated_by = $userData->id;
                $room->save();

                OpenBed::dispatch($room->id)->delay(now()->addMinutes(15));
            }

            // Update Therapist
            $transactionDetailPartners = TransactionDetailPartner::whereIn('transaction_detail_id', array_column($transactionDetails->toArray(), 'id'))->get();
            $partners = Partner::whereIn('id', array_column($transactionDetailPartners->toArray(), 'partner_id'))->get();

            foreach ($transactionDetailPartners as $transactionDetailPartner) {
                $transactionDetailPartner->is_checkout = 1;
                $transactionDetailPartner->save();
            }

            foreach ($partners as $partner) {
                $partner->status_id = 12;
                $partner->updated_by = $userData->id;
                $partner->save();

                OpenTerapis::dispatch($partner->id)->delay(now()->addMinutes(15));
            }

            // Update Headers dan Details Status
            foreach ($transactionHeaders as $header) {
                $header->status_id = 9;
                $header->updated_by = $userData->id;
                $header->end_time = Carbon::now('Asia/Jakarta');
                $header->save();
            }

            foreach ($transactionDetails as $detail) {
                $detail->status_id = 9;
                $detail->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil melakukan pembayaran transaksi!',
                'receiptPayment' => route('admin.monitor_room.print', ['id' => $invoice->id]),
            ]);
        } catch (\Exception $ex) {
            DB::rollBack();

            Log::channel('monitor_room')->error('[Monitor Room - Payment] - ' . $ex);
            return Response::json($ex, 500);
        }
    }

    public function setRoomAvailable(Request $request)
    {
        try {
            if (!$request->filled('room_id')
            ) {
                return Response::json('Invalid Parameters!', 400);
            }

            $user = Auth::guard('admin')->user();
            $userData = AdminUser::where('id', $user->id)->first();

            $room = Room::find($request->input('room_id'));
            if($room == null){
                return Response::json('Room tidak ditemukan!', 400);
            }

            DB::beginTransaction();

            $room->status_id = 5;
            $room->updated_by = $userData->id;
            $room->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Room berhasil di-set menjadi available!',
            ]);
        } catch (\Exception $exception) {
            DB::rollBack();

            Log::channel('monitor_room')->error('[Monitor Room - Set Room Available] - ' . $exception);
            return Response::json($exception, 500);
        }
    }

    public function print($id, Request $request)
    {
        $user     = Auth::guard('admin')->user();
        $userData = AdminUser::where('id', $user->id)->first();
        $branch   = Branch::where('id', $userData->branch_id)->first();

        $invoice = TransactionInvoice::find($id);

        $headerIds = explode('#', $invoice->header_ids);
        $headers   = TransactionHeader::whereIn('id', $headerIds)->get();

        $reprint = false;
        if ($request->filled('reprint')) {
            $reprint = true;
            $invoice->reprint++;
            $invoice->save();
        }

        $discountUsed = [];
        if ($invoice->coupon_id != null) {
            preg_match_all('/\d+/', $invoice->coupon_id, $matchesCoupon);
            preg_match_all('/\|([^|]+)\|/', $invoice->voucher_code, $matchesVoucher);

            $arrCouponIds    = $matchesCoupon[0];
            $arrVoucherCodes = $matchesVoucher[0];
            $arrVoucherCodes = array_map(function ($value) {
                return trim($value, '|'); // Remove "|" only from start and end
            }, $arrVoucherCodes);

            foreach ($arrCouponIds as $index => $couponId) {
                $coupon = Coupon::find($couponId);

                if ($coupon != null) {
                    $discountUsed[] = $coupon->code . ' - ' . $arrVoucherCodes[$index];
                }
            }
        }

        $membershipLevels = [];
        if ($invoice->is_use_voucher_code == 0) {
            foreach ($headers as $header) {
                if ($header->member_membership_id != null) {
                    if ($header->membership->discount_percentage > 0) {
                        $membershipLevels[] = $header->membership->name;
                    }
                }
            }

            $membershipLevels = implode(', ', array_unique($membershipLevels));
        }

        $isPrewed = false;
        $prewed = null;
        foreach ($headers as $header){
            $isPrewed = $header->is_prewed == 1;
            $prewed = PreweddingHeader::where('transaction_id', $header->id)->first();
        }

        if($isPrewed){
            return view('admin.prewedding.receipt',
                compact(
                    'invoice',
                    'prewed',
                    'branch',
                    'userData',
                    'headers',
                    'reprint',
                    'membershipLevels',
                    'discountUsed',
                ));
        } else {
            $vouchers = [];
            foreach ($headers as $header) {
                foreach ($header->transaction_details as $detail) {
                    if ($detail->coupon_id != null) {
                        $codes = UserVoucherCode::where('header_id', $detail->coupon_id)->get();

                        foreach ($codes as $code) {
                            $vouchers[] = [
                                'code' => $code->code,
                                'name' => $code->user_voucher->voucher_code
                            ];
                        }
                    }
                }
            }

            return view('admin.monitor_room.receipt',
                compact(
                    'invoice',
                    'branch',
                    'userData',
                    'headers',
                    'reprint',
                    'membershipLevels',
                    'discountUsed',
                    'vouchers'
                ));
        }

//        dd($membershipLevels);
    }
}
