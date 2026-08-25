<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Libs\AutoNumberGenerator;
use App\Libs\Utilities;
use App\Models\AdminUser;
use App\Models\Branch;
use App\Models\CommissionEmployeeHistory;
use App\Models\CommissionPositionItem;
use App\Models\Configuration;
use App\Models\CouponList;
use App\Models\Employee;
use App\Models\FoodBookingDetail;
use App\Models\Inventory;
use App\Models\InventoryHistory;
use App\Models\Item;
use App\Models\JournalHeader;
use App\Models\Locker;
use App\Models\Member;
use App\Models\Membership;
use App\Models\Package;
use App\Models\Partner;
use App\Models\PosPaymentType;
use App\Models\Room;
use App\Models\Status;
use App\Models\TempBooking;
use App\Models\TransactionDetail;
use App\Models\TransactionDetailPartner;
use App\Models\TransactionHeader;
use App\Models\TransactionPayment;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class PosController extends Controller
{
    public function getTransaction(Request $request)
    {
        try{
            if(!$request->has('locker_id')){
                return Response::json('locker_id is required!', 400);
            }

            $header = TransactionHeader::where('locker_id', $request->input('locker_id'))
                ->where('status_id', 8)
                ->first();

            $isAthena = false;
            $details = [];
            $partners = [];
            $tmpDetails = TransactionDetail::where('header_id', $header->id)->get();
            foreach ($tmpDetails as $dt){
                $subtotal = ($dt->qty * $dt->price) - $dt->discount_amount;

                $nDt = [
                    'id'                    => $dt->id,
                    'type'                  => '',
                    'package_id'            => 0,
                    'package_name'          => '',
                    'item_id'               => 0,
                    'item_name'             => '',
                    'item_category_id'      => 0,
                    'qty'                   => $dt->qty,
                    'price'                 => $dt->price,
                    'status'                => '-',
                    'subtotal'              => $subtotal,
                    'discount_percentage'   => $dt->discount_percentage,
                    'discount_amount'       => $dt->discount_amount,
                    'discount_str'          => number_format($dt->discount_amount, '2', ',', '.'),
                    'price_str'             => number_format($dt->price, '2', ',', '.'),
                    'subtotal_str'          => number_format($subtotal, '2', ',', '.'),
                    'is_facility_only'      => false,
                    'is_cut'                => $dt->is_cut,
                    'time_display'          => '-',
                    'room_number'           => '-',
                ];

                $status = Status::where('id', $dt->status_id)->first();
                if($status != null){
                    $nDt['status'] = $status->display;
                }

                if($dt->package_id != null){
                    $package = Package::where('id', $dt->package_id)->first();
                    if($package != null){
                        $nDt['type'] = 'package';
                        $nDt['package_name'] = $package->name;
                        $nDt['package_id'] = $package->id;
                        if($package->room_type_id == 3){
                            $isAthena = true;
                        }

                        if($dt->room_id != null){
                            $tmpDtRoom = Room::where('id', $dt->room_id)->first();
                            $nDt['room_number'] = $tmpDtRoom->number;
                        }

                        $tmpDtStartTime = Carbon::parse($dt->start_time);
                        $tmpDtEndTime = Carbon::parse($dt->start_time);
                        $tmpDtEndTime->addMinutes($package->duration);
                        $nDt['time_display'] = $tmpDtStartTime->format('H:i') . ' - '. $tmpDtEndTime->format('H:i');

                        if($package->partner_count > 0){
                            //check for therapis
                            $tmpPartners = TransactionDetailPartner::where('transaction_detail_id', $dt->id)->get();

                            if(count($tmpPartners) == 0){
                                return Response::json('Locker ini belum pilih Therapis!', 400);
                            }
                            else{
                                foreach ($tmpPartners as $tmp){
                                    $tmpPartner = Partner::where('id', $tmp->partner_id)->first();
                                    if($tmpPartner != null){
                                        $partners[] = $tmpPartner->number;
                                    }
                                }
                            }
                        }
                    }
                }

                if($dt->item_id != null){
                    $item = Item::where('id', $dt->item_id)->first();
                    if($item != null){
                        $nDt['type'] = 'item';
                        $nDt['item_id'] = $item->id;
                        $nDt['item_name'] = $item->name;
                        $nDt['item_category_id'] = $item->category_id;
                    }
                }

                if($dt->is_facility_only == 1){
                    $nDt['is_facility_only'] = true;
                    $nDt['status'] = 'Masih Berjalan';
                }

                $details[] = $nDt;

                if($dt->is_extended == 1){
                    //its extended
                    $package = Package::where('id', $dt->package_id)->first();
                    $extendDt = [
                        'id'                    => 0,
                        'type'                  => 'package',
                        'package_id'            => 0,
                        'package_name'          => 'Perpanjangan Waktu',
                        'discount_percentage'   => 0,
                        'discount_str'          => '0,00',
                        'item_id'               => 0,
                        'item_name'             => '',
                        'status'                => $status->display,
                        'qty'                   => $dt->extension_times,
                        'price'                 => $package->extend_price,
                        'subtotal'              => $dt->extension_amount,
                        'price_str'             => number_format($package->extend_price, '2', ',', '.'),
                        'subtotal_str'          => number_format($dt->extension_amount, '2', ',', '.'),
                    ];
                    $details[] = $extendDt;
                }

                if($dt->additional_partner_qty > 0){
                    //its extended
                    $package = Package::where('id', $dt->package_id)->first();
                    $extendDt = [
                        'id'            => 0,
                        'type'          => 'package',
                        'package_id'    => 0,
                        'package_name'  => 'Penambahan Therapis',
                        'discount_percentage'  => 0,
                        'discount_str'  => '0,00',
                        'item_id'       => 0,
                        'item_name'     => '',
                        'status'        => $status->display,
                        'qty'           => $dt->additional_partner_qty,
                        'price'         => $package->additional_partner_price,
                        'subtotal'      => $dt->additional_partner_amount,
                        'price_str'     => number_format($package->additional_partner_price, '2', ',', '.'),
                        'subtotal_str'  => number_format($dt->additional_partner_amount, '2', ',', '.'),
                    ];
                    $details[] = $extendDt;
                }

                if($dt->additional_person_qty > 0){
                    //its extended
                    $package = Package::where('id', $dt->package_id)->first();
                    $extendDt = [
                        'id'            => 0,
                        'type'          => 'package',
                        'package_id'    => 0,
                        'package_name'  => 'Penambahan Customer',
                        'discount_percentage'  => 0,
                        'discount_str'  => '0,00',
                        'item_id'       => 0,
                        'item_name'     => '',
                        'status'        => $status->display,
                        'qty'           => $dt->additional_person_qty,
                        'price'         => $package->additional_person_price,
                        'subtotal'      => $dt->additional_person_amount,
                        'price_str'     => number_format($package->additional_person_price, '2', ',', '.'),
                        'subtotal_str'  => number_format($dt->additional_person_amount, '2', ',', '.'),
                    ];
                    $details[] = $extendDt;
                }
            }

            if($header == null){
                return Response::json('Transaksi untuk locker ini tidak ditemukan!', 404);
            }

            $partner = Employee::where('id', $header->employee_id)->first();

            $data = [
                'header'    => $header,
                'details'   => $details,
                'partners'  => $partners,
                'gro'       => $partner,
                'isAthena'  => $isAthena,
            ];
            if($request->filled('is_showing')){
                Log::channel('pos')->info($request->input('is_showing'));
                Log::channel('pos')->info($request->query('is_showing'));
                if($request->input('is_showing') == 1){
                    $header->is_showing = 1;
                    $header->save();
                }
            }

            return Response::json($data, 200);
        }
        catch (\Exception $ex){
            return Response::json($ex, 500);
        }
    }

    public function getItems(Request $request)
    {
        try{
            $user = Auth::guard('admin')->user();
            $userData = AdminUser::find($user->id);
            $skip = 0;
            $take = 10;
            if($request->exists('skip')){
                $skip = $request->query('skip');
            }

            if($request->exists('take')){
                $take = $request->query('take');
            }
            $tmpItems = Item::with('item_category')->where('branch_id', $userData->branch_id)
                        ->where('status_id', 1);
            if($request->exists('search')){
                $tmpItems = $tmpItems->where('name', 'like', '%' . $request->query('search') . '%');
            }
            if($request->exists('is_fnb')){
                $tmpItems = $tmpItems->where('category_id', 2);
            }
            $tmpItems = $tmpItems->skip($skip)->take($take)->get();
            $count = DB::table('items')->count();
            $pages = [];
            $tmpPageCount = ceil($count / 10);
            for($i=1; $i<=$tmpPageCount; $i++){
                $pages[] = $i;
            }
            $items = [];

            foreach ($tmpItems as $tmp){
                $nItem = [
                    'id'        => $tmp->id,
                    'name'      => $tmp->name,
                    'price_str' => number_format($tmp->price,2,',','.'),
                    'price'     => $tmp->price,
                    'selected'  => false,
                    'category'  => $tmp->item_category->name,
                    'img_path'  => $tmp->img_path ?? asset('images/nasi_goreng.jpg'),
                    'qty'       => 0,
                ];

                $items[] = $nItem;
            }

            $data = [
                'items'     => $items,
                'itemCount' => $count,
                'pages'     => $pages,
            ];

            return Response::json($data, 200);
        }
        catch (\Exception $ex){
            return Response::json($ex, 500);
        }
    }

    public function getAllItems(Request $request)
    {
        try{
            $user = Auth::guard('admin')->user();
            $userData = AdminUser::find($user->id);
            $tmpItems = Item::with(['item_category', 'inventories'])
                ->where('branch_id', $userData->branch_id)
                ->where('status_id', 1)
                // ->where('category_id', 2)->get();
                ->where(function($q){
                    $q->where('category_id', 2)->orWhere('category_id', 21)->orWhere('category_id', 22)->orWhere('category_id', 23)->orWhere('category_id', 25);
                })->get();

            $config = Configuration::where('key', 'selling_warehouse_id')->where('branch_id', $userData->branch_id)->first();

            $items = [];
            foreach ($tmpItems as $tmp){
                $nItem = [
                    'id'                    => $tmp->id,
                    'name'                  => $tmp->name,
                    'price_str'             => number_format($tmp->price,0,',','.'),
                    'price'                 => $tmp->price,
                    'selected'              => false,
                    'category'              => $tmp->item_category->name,
                    'img_path'              => $tmp->img_path ?? asset('images/nasi_goreng.jpg'),
                    'qty'                   => 0,
                    'stock'                 => 0,
                    'is_unlimited_stock'    => $tmp->is_unlimited_stock,
                ];

                $inv = $tmp->inventories->where('branch_id', $userData->branch_id)->where('warehouse_id', $config->value_int)->first();

                if($inv != null){
                    if($inv->qty > 0){
                        $nItem['stock'] = $inv->qty;
                    }
                }

                $items[] = $nItem;
            }

            return Response::json($items, 200);
        }
        catch (\Exception $ex){
            Log::error($ex);
            return Response::json($ex, 500);
        }
    }

    public function getItemAll(Request $request)
    {
        try{
            $user = Auth::guard('admin')->user();
            $userData = AdminUser::find($user->id);
            $tmpItems = Item::with(['item_category', 'inventories'])
                ->where('branch_id', $userData->branch_id)
                ->where('status_id', 1)
                ->get();

            $config = Configuration::where('key', 'selling_warehouse_id')->where('branch_id', $userData->branch_id)->first();

            $items = [];

            foreach ($tmpItems as $tmp){
                $isAddon = intval($tmp->is_addon);

                $nItem = [
                    'id' => $tmp->id,
                    'name' => $tmp->name,
                    'price_str' => number_format($tmp->price,0,',','.'),
                    'price' => $tmp->price,
                    'selected' => false,
                    'category' => $tmp->item_category->name,
                    'img_path' => $tmp->img_path ?? asset('images/nasi_goreng.jpg'),
                    'qty' => $isAddon == 1 ? 1 : 0,
                    'stock' => 0,
                    'is_unlimited_stock' => $tmp->is_unlimited_stock,
                    'is_addon' => $isAddon
                ];
                $inv = $tmp->inventories->where('branch_id', $userData->branch_id)->where('warehouse_id', $config->value_int)->first();

                if($inv != null){
                    if($inv->qty > 0){
                        $nItem['stock'] = $inv->qty;
                    }
                }

                $items[] = $nItem;
            }

            return Response::json($items, 200);
        }
        catch (\Exception $ex){
            Log::error($ex);
            return Response::json($ex, 500);
        }
    }

    public function getPaymentTypes(Request $request)
    {
        try{
            $user = Auth::guard('admin')->user();
            $payments = PosPaymentType::where('branch_id', $user->branch_id)
                ->where('status_id', 1)
                ->get();

            return Response::json($payments, 200);
        }
        catch (\Exception $ex){
            return Response::json($ex, 500);
        }
    }

    public function select(Request $request)
    {
        $term = trim($request->search);

        $user = Auth::guard('admin')->user();
        $userData = AdminUser::find($user->id);

        $paymentMethods = DB::table('pos_payment_types')
            ->where('status_id', '!=', 2)
            ->where(function($q) use ($term){
                $q->where('code', 'LIKE', '%'. $term. '%')
                    ->orWhere('description', 'LIKE', '%'. $term. '%');
            })
            ->where('branch_id', '=', $userData->branch_id)
            ->get();

        $formatted_tags = [];

        foreach ($paymentMethods as $paymentMethod) {
            $formatted_tags[] = ['id' => $paymentMethod->id, 'text' => $paymentMethod->code];
        }

        return Response::json($formatted_tags);
    }

    public function submit(Request $request)
    {
        try{
            $rules = array(
                'locker_id' => 'required',
                'trx_id'    => 'required',
                'payments'  => 'required|array',
            );

            $message = array(
                'locker_id.required'    => 'Locker wajib dipilih!',
                'trx_id.required'       => 'Transaksi wajib dipilih!',
                'payments.required'     => 'Detil Transaksi wajib dipilih!',
            );

            $validator = Validator::make($request->all(), $rules, $message);

            if($validator->fails()){
                return Response::json(['error' => $validator->errors()->all()]);
            }

            //update transaction
            $header = TransactionHeader::where('id', $request->input('trx_id'))->first();
            if($header == null){
                return Response::json(['error' => 'Transaksi tidak ditemukan untuk locker ini!'], 404);
            }

            $autoJournalConfig = DB::table('configurations')
                ->where('key', '=', 'is_auto_journal')
                ->first();
            $isAutoJournal = $autoJournalConfig->value_int > 0;

            $user = Auth::guard('admin')->user();
            $userData = AdminUser::find($user->id);

            //foc types
            //FOC = full FOC (100% on Everything)
            //FOC_PACKAGE = FOC only on package
            //FOC_ITEM = FOC only on item
            $foc = $request->input('foc');
            $focNumber = $request->input('foc_number');

            if(strtolower($foc) != 'no'){
                $header->foc_type = strtolower($foc);
                $header->foc_number = $focNumber;
                //reset happy hour status
                if($header->is_happy_hour == 1){
                    $header->is_happy_hour = 0;
                    $header->discount = 0;
                    $header->save();

                    //reset details
                    $details = TransactionDetail::where('header_id', $header->id)->get();
                    foreach($details as $detail){
                        $detail->discount_amount = 0;
                        $detail->discount_percentage = 0;
                        $detail->subtotal = $detail->qty * $detail->price;
                        $detail->save();
                    }
                }
            }

            $payments = $request->input('payments');
            //check on payments first
            $totalPayment = 0;
            foreach ($payments as $payment){
                $totalPayment += $payment['amount'];
            }
            $totalTrx = 0;
            $allDetails = TransactionDetail::where('header_id', $header->id)->get();
            foreach ($allDetails as $dt){
                if($dt->item_id != null){
                    $totalTrx += ($dt->price * $dt->qty);
                }
                else{
                    $totalTrx += $dt->subtotal;
                }
            }

//            $headerDiscount = 0;
//            if($request->exists('header_discount')){
//                $headerDiscount = Utilities::toFloat($request->input('header_discount'));
//            }
//
//            if(($totalPayment - $headerDiscount) < $totalTrx){
//                return Response::json('Payment tidak boleh kurang dari nilai transaksi!', 400);
//            }

            if($isAutoJournal){
                //Insert journal Header
                //also insert journal for kas
                $journal = new JournalHeader();
                $journal->transaction_code = AutoNumberGenerator::generateAutonumberTrx('JN');
                $journal->transaction_type = 'POS';
                $journal->transaction_date = Carbon::now('Asia/Jakarta');
                $journal->created_by = $userData->id;
                $journal->branch_id = $userData->branch_id;
                $journal->notes = 'Journal Transaksi ' . $header->code;
                $journal->reference_id = $header->id;
                $journal->status_id = 1;
                $journal->save();
            }

            //check on membership
            if(!empty($request->input('membership_id')) && $request->input('membership_id') != 0){
                $dbMembership = Membership::where('id', $request->input('membership_id'))->first();
                if($dbMembership != null){
                    //membership found
                    $nDetail = new TransactionDetail();
                    $nDetail->membership_id = $dbMembership->id;
                    $nDetail->qty = 1;
                    $nDetail->price = $dbMembership->price;
                    $nDetail->subtotal = floatval($dbMembership->price);
                    $nDetail->header_id = $header->id;

                    if($foc == 'FOC_PACKAGE' || $foc == 'FOC'){
                        //discount
                        $nDetail->discount_percentage = 100;
                        $nDetail->discount_amount = $nDetail->subtotal;
                    }

                    $nDetail->save();

                    //add the person to member
                    $memberData = $request->input('member_data');
                    $newMember = new Member();
                    $newMember->name = $memberData['name'];
                    $newMember->email = $memberData['email'];
                    $newMember->phone = $memberData['phone'];
                    $newMember->code = $memberData['number'];
                    $newMember->join_date = Carbon::now('Asia/Jakarta');
                    $newMember->duration = $dbMembership->duration;
                    $newMember->is_infinite = $dbMembership->is_infinite;
                    $newMember->membership_id = $dbMembership->id;
                    $newMember->created_by = $user->id;
                    $newMember->is_active = 1;
                    $newMember->save();
                }
            }

            // If member found
            if($request->filled('member_code')){
                $membershipCode = trim($request->input('member_code'));
                if(!empty($membershipCode)){
                    $memberModel = DB::table('members')
                        ->where('code', '=', $membershipCode)
                        ->first();
                    if(!empty($memberModel)){
                        // Get membership benefits and check each trx details for their transaction type
                        $membershipBenefits = DB::table('membership_benefits')
                            ->where('membership_id', '=', $memberModel->membership_id)
                            ->get();
                        foreach ($membershipBenefits as $membershipBenefit){
                            $tmpTrxDetails = TransactionDetail::where('header_id', '=', $header->id)
                                ->get();

                            $discountUsed = 0;
                            $isFreeUsed = false;
                            foreach ($tmpTrxDetails as $tmpTrxDetail){
                                $isMembershipBenefitApplied = false;
                                if($membershipBenefit->type === 'treatment' && !empty($tmpTrxDetail->package_id)){
                                    // Skip checking discount if discount usage is exhausted or free facility used
                                    if($discountUsed >= 2 || $isFreeUsed){
                                        continue;
                                    }

                                    $isMembershipBenefitApplied = true;
                                }
                                elseif($membershipBenefit->type === 'item' && !empty($tmpTrxDetail->item_id)){
                                    if(!empty($membershipBenefit->item_category_id)){
                                        $tmpItem = DB::table('items')
                                            ->where('id', '=', $tmpTrxDetail->item_id)
                                            ->first();

                                        if($tmpItem->category_id == $membershipBenefit->item_category_id){
                                            $isMembershipBenefitApplied = true;
                                        }
                                    }

                                    if($tmpTrxDetail->is_cut == 1){
                                        $isMembershipBenefitApplied = true;
                                    }
                                }
                                elseif($membershipBenefit->type === 'facility' && $tmpTrxDetail->is_facility_only === 1){
                                    // Skip checking discount if discount usage is exhausted or free facility used
                                    if($discountUsed >= 2 || $isFreeUsed){
                                        continue;
                                    }

                                    $isMembershipBenefitApplied = true;
                                }

                                if($isMembershipBenefitApplied){
                                    //error_log('test');
                                    $membershipDisc = 0;
                                    if($membershipBenefit->free_amount > 0){
                                        if($tmpTrxDetail->is_facility_only == 1 && $discountUsed < 2){
//                                            $tmpTrxDetail->discount_amount = $tmpTrxDetail->subtotal;
                                            $tmpTrxDetail->discount_percentage = 100;
                                            $tmpTrxDetail->member_id = $memberModel->id;
                                            $tmpTrxDetail->save();

                                            $isFreeUsed = true;

                                            $membershipDisc = $tmpTrxDetail->subtotal;
                                        }
                                        // Unhandled case
                                    }
                                    else{
                                        if($membershipBenefit->discount_percentage > 0){
                                            $membershipDisc = (floatval($tmpTrxDetail->price) * $tmpTrxDetail->qty) * floatval($membershipBenefit->discount_percentage / 100);
                                        }
                                        else{
                                            $membershipDisc = floatval($membershipBenefit->discount_flat) > 0 ? floatval($membershipBenefit->discount_flat) : 0;
                                        }
                                    }

                                    if($membershipDisc > 0){
                                        $tmpTrxDetail->discount_amount = $membershipDisc;

                                        if(!empty($tmpTrxDetail->package_id)){
                                            $tmpTrxDetail->subtotal = (floatval($tmpTrxDetail->price) - $membershipDisc) +
                                                floatval($tmpTrxDetail->extension_amount) +
                                                floatval($tmpTrxDetail->additional_person_amount) +
                                                floatval($tmpTrxDetail->additional_partner_amount);
                                        }
                                        else{
                                            $tmpTrxDetail->subtotal = (floatval($tmpTrxDetail->price) * $tmpTrxDetail->qty) - $membershipDisc;
                                        }

                                        $tmpTrxDetail->member_id = $memberModel->id;
                                        $tmpTrxDetail->save();

                                        $discountUsed++;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            //insert payments
            $payments = $request->input('payments');
            foreach ($payments as $payment){
                $dbPayment = PosPaymentType::where('id', $payment['id'])->first();

                if($dbPayment != null){
                    $nPayment = new TransactionPayment();

                    $nPayment->header_id = $header->id;
                    $nPayment->payment_type_id = $payment['id'];
                    $nPayment->payment_type_code = $dbPayment->code;
                    $nPayment->amount = $payment['amount'];
                    if($dbPayment->code == 'CASH' && $request->input('change') != 0){
                        $nPayment->amount = $payment['amount'] - $request->input('change');
                    }
                    $nPayment->branch_id = $userData->branch_id;
                    $nPayment->created_by = $user->id;
                    $nPayment->save();
                }
            }

            $totalQty = DB::table('transaction_details')->where('header_id', $header->id)->sum('qty');
            $totalTrx = DB::table('transaction_details')->where('header_id', $header->id)->sum('subtotal');
            $totalDiscount = DB::table('transaction_details')->where('header_id', $header->id)->sum('discount_amount');
            $header->discount = $totalDiscount;

            //count global discount
            $headerDiscount = 0;
            if($request->exists('header_discount')){
                $headerDiscount = Utilities::toFloat($request->input('header_discount'));
                $header->discount += $headerDiscount;
            }
            $header->amount = $totalTrx - $header->discount;
            $header->grand_total = $totalTrx - $headerDiscount;
            $header->total_qty = $totalQty;
            $header->return_value = $request->input('change');
            $header->end_time = Carbon::now('Asia/Jakarta');
            $header->status_id = 9;
            $header->updated_by = $user->id;
            $header->save();

            //update locker
            $locker = Locker::where('id', $header->locker_id)->first();
            $locker->status_id = 5;
            $locker->save();

            //update room status
            $trxPackages = TransactionDetail::where('header_id', $header->id)->where('package_id', '!=', null)->where('status_id', 8)->get();

            $totalCommission = 0;
            foreach ($trxPackages as $trxPackage){
                //update room
                $room = Room::where('id', $trxPackage->room_id)->first();
                if($room != null){
                    $room->status_id = 4;
                    $room->duration = 900;
                    //check on double booking
                    $tmpRoomBook = TempBooking::where('room_id', $room->id)
                        ->where('partner_id', null)
                        ->where('status_id', 8)
                        ->first();
                    if($tmpRoomBook != null){
                        $tmpRoomBook->status_id = 9;
                        $tmpRoomBook->save();
                        $room->status_id = 11;
                        $room->duration = 0;
                    }
                    $room->save();
                }

                //update therapis
                $package = Package::where('id', $trxPackage->package_id)->first();
                if($package->partner_count > 0){
                    $partners = TransactionDetailPartner::where('transaction_detail_id', $trxPackage->id)
                        ->get();

                    foreach ($partners as $tmp){
                        $partner = Partner::with('partner_level')->where('id', $tmp->partner_id)->first();

                        $commissionAdd = 0;
                        if($partner != null){

                            $partner->duration = 900;
                            if($tmp->is_checkout != 1){
                                $tmp->is_checkout = 1;
                                $tmp->save();
                                $partner->status_id = 12;
                            }

                            //check on double booking
                            $tmpRoomBook = TempBooking::where('partner_id', $partner->id)
                                ->where('room_id', null)
                                ->where('status_id', 8)
                                ->first();

                            if($tmpRoomBook != null){
                                $tmpRoomBook->status_id = 9;
                                $tmpRoomBook->save();
                                $partner->status_id = 11;
                                $partner->duration = 0;
                            }
                            $partner->save();
                            $tmp->is_checkout = 1;
                            $tmp->save();
                        }
                    }

                    $trxPackage->status_id = 9;
                    $trxPackage->end_time = Carbon::now('Asia/Jakarta');
                    // $trxPackage->subtotal_after_commission = $trxPackage->subtotal - $subtotalCommission - $trxPackage->extension_amount;
                    $trxPackage->subtotal_after_commission = 0;
                    $trxPackage->save();
                }
            }

            $tmpGrandTotal = $header->grand_total;
            if($totalCommission > 0){
                $header->total_after_commission = $tmpGrandTotal - $totalCommission;
                $header->save();
            }

            $trxItems = TransactionDetail::where('header_id', $header->id)->where('item_id', '!=', null)->get();

            //count commission for GRO
            $employee = Employee::where('id', $header->employee_id)->first();
            $totalEmployeeCommission = 0;
            if($employee != null){
                foreach ($trxItems as $trxItem){
                    $employeeCommission = CommissionPositionItem::where('branch_id', $header->branch_id)
                        ->where('position_id', $employee->position_id)
                        ->where('item_id', $trxItem->item_id)
                        ->where('status_id', '=', 1)
                        ->first();
                    $item = Item::where('id', $trxItem->item_id)->first();

                    if($employeeCommission != null && $item != null){
                        if($employeeCommission->commission_percentage > 0){
                            $commissionAdd = (floatval($trxItem->price) * intval($trxItem->qty)) * floatval($employeeCommission->commission_percentage / 100);
                        }
                        else{
                            $commissionAdd = floatval($employeeCommission->commission_flat) > 0 ? floatval($employeeCommission->commission_flat) * intval($trxItem->qty) : 0;
                        }

                        if($commissionAdd > 0){
                            $totalEmployeeCommission += $commissionAdd;

                            $eCHistory = new CommissionEmployeeHistory();
                            $eCHistory->branch_id = $header->branch_id;
                            $eCHistory->employee_id = $employee->id;
                            $eCHistory->employee_code = $employee->code;
                            $eCHistory->employee_name = $employee->name;
                            $eCHistory->employee_phone = $employee->phone;
                            $eCHistory->transaction_id = $header->id;
                            $eCHistory->transaction_code = $header->code;
                            $eCHistory->transaction_date = $header->created_at;
                            $eCHistory->commission_percentage = $employeeCommission->commission_percentage;
                            $eCHistory->item_id = $item->id;
                            $eCHistory->item_qty = $trxItem->qty;
                            $eCHistory->item_code = $item->code;
                            $eCHistory->item_name = $item->name;
                            //counting commission
                            $eCHistory->commission_add = $commissionAdd;
                            $eCHistory->commission_result = $commissionAdd;
                            $eCHistory->save();

                            $trxItem->subtotal_after_commission = $trxItem->subtotal - $commissionAdd;
                            $trxItem->save();
                        }
                    }
                }
            }

            $header->is_showing = 0;
            $header->save();

            if($totalEmployeeCommission > 0){
                $header->total_after_commission = $tmpGrandTotal - $totalEmployeeCommission;
                $header->save();
            }

            return Response::json('Sukses submit transaksi! Silahkan print Struk!', 200);
        }
        catch (\Exception $ex){
            error_log($ex);
            Log::channel('pos')->error('Admin/PosController - submit '. $ex);
            return Response::json($ex, 500);
        }
    }

    public function addItemToTrx(Request $request)
    {
        try{
            $rules = array(
                'locker_id' => 'required',
//                'trx_id'    => 'required',
            );

            $message = array(
                'locker_id.required'    => 'Locker wajib dipilih!',
//                'trx_id.required'       => 'Transaksi wajib dipilih!',
            );

            $validator = Validator::make($request->all(), $rules, $message);

            if($validator->fails()){
                return Response::json(['error' => $validator->errors()->all()]);
            }

            //update transaction
            //Log::channel('pos')->info($request->input('trx_id'));
            $user = Auth::guard('admin')->user();
            $userData = AdminUser::find($user->id);
            $config = Configuration::where('key', 'selling_warehouse_id')->where('branch_id', $userData->branch_id)->first();
            $header = TransactionHeader::where('locker_id', $request->input('locker_id'))
                ->where('status_id', 8)
                ->first();
            if($header == null){
                return Response::json(['error' => 'Transaksi tidak ditemukan untuk locker ini!'], 404);
            }

            if(!empty($request->input('items'))){
                //if item details is not empty
                $items = $request->input('items');
                foreach ($items as $item){
                    //check item in DB
                    $dbItem = Item::where('id', $item['id'])->first();
                    //check details in DB
                    $dt = TransactionDetail::where('item_id', $dbItem->id)->where('header_id', $header->id)->first();
                    if($dbItem != null){
                        if($dt == null){
                            //process if not null
                            $nDetail = new TransactionDetail();
                            $nDetail->item_id = $dbItem->id;
                            $nDetail->qty = $item['qty'];
                            $nDetail->price = $dbItem->price;
                            $nDetail->subtotal = floatval($dbItem->price) * intval($item['qty']);
                            $nDetail->header_id = $header->id;
                            $nDetail->status_id = 15;
                            if($dbItem->category_id == 2 || $dbItem->category_id == 21 || $dbItem->category_id == 22 || $dbItem->category_id == 23 || $dbItem->category_id == 25){
                                $nDetail->is_fnb = 1;
                                $nDetail->new_order_qty = $item['qty'];
                                $nDetail->is_new_order = 1;
                            }
                            $nDetail->save();

                            //insert the book things
                            if($request->input('person_number') != ''){
                                $nFnb = new FoodBookingDetail();
                                $nFnb->locker_id = $header->locker_id;
                                $nFnb->transaction_details_id = $nDetail->id;
                                $nFnb->number = $request->input('person_number');
                                $nFnb->save();
                            }
                        }
                        else{
                            $dt->new_order_qty = $item['qty'];
                            $dt->qty += $item['qty'];
                            $dt->subtotal += (floatval($dbItem->price) * intval($item['qty'])) ;
                            $dt->is_new_order = 1;
                            $dt->save();

                            //insert the book things
                            if($request->input('person_number') != ''){
                                $nFnb = new FoodBookingDetail();
                                $nFnb->locker_id = $header->locker_id;
                                $nFnb->transaction_details_id = $dt->id;
                                $nFnb->number = $request->input('person_number');
                                $nFnb->save();
                            }
                        }
                        //check on inventory
                        $wh = Warehouse::where('branch_id', $userData->branch_id)->where('id', $config->value_int)->first();
                        if($wh != null){
                            if($dbItem->is_unlimited_stock == 0){
                                $inv = Inventory::where('item_id', $dbItem->id)
                                    ->where('warehouse_id', $wh->id)
                                    ->where('branch_id', $userData->branch_id)
                                    ->first();

                                if($inv != null){
                                    $inv->qty -= $item['qty'];
                                    $inv->save();

                                    //save inv history
                                    $invHistory = new InventoryHistory();
                                    $invHistory->reference = $header->code;
                                    $invHistory->warehouse_id = $wh->id;
                                    $invHistory->warehouse_name = $wh->name;
                                    $invHistory->branch_id = $userData->branch_id;
                                    $invHistory->created_by = $userData->id;
                                    $invHistory->item_id = $dbItem->id;
                                    $invHistory->item_code = $dbItem->code;
                                    $invHistory->item_name = $dbItem->name;
                                    $invHistory->description = 'Tambah Item untuk ' . $header->code;
                                    $invHistory->qty_add = 0;
                                    $invHistory->qty_sub = $item['qty'];
                                    $invHistory->qty_result = $inv->qty;
                                    $invHistory->qty_total = $inv->qty;
                                    $invHistory->save();
                                }
                            }
                        }
                    }
                }
            }
            else{
                return Response::json(['error' => 'Item harus diisi!'], 404);
            }

            //insert payments
            $user = Auth::guard('admin')->user();

            $totalQty = DB::table('transaction_details')->where('header_id', $header->id)->sum('qty');
            $totalTrx = DB::table('transaction_details')->where('header_id', $header->id)->sum('subtotal');
            $header->grand_total = $totalTrx;
            $header->total_qty = $totalQty;
            $header->updated_by = $user->id;
            $header->save();

            return Response::json('Sukses Tambah item ke Transaksi!', 200);
        }
        catch (\Exception $ex){
            Log::error($ex);
            return Response::json($ex, 500);
        }
    }

    public function receipt(Request $request){
        try{
            $user = Auth::guard('admin')->user();
            $userData = AdminUser::where('id', $user->id)->first();
            $branch = Branch::where('id', $userData->branch_id)->first();
            $header = TransactionHeader::with(['locker', 'employee', 'updatedBy'])
                ->where('id', $request->input('id'))->first();
            if($header == null){
                Session::flash('error', 'Transaksi tidak ditemukan!');
                return Redirect::route('admin.monitor_locker.index');
            }

            $details = TransactionDetail::with(['package', 'item'])
                ->where('header_id', $header->id)
                //->where('is_cut', '!=', 1)
                ->get();

            //make new list
            $detailsNew = new Collection();
            $config = Configuration::where('branch_id', $userData->branch_id)->where('key', 'package_cut')->first();
            foreach ($details as $detail){
                $nDt = collect([
                    'id'        => $detail->id,
                    'header_id' => $detail->header_id,
                    'package_id' => $detail->package_id,
                    'package_name' => '',
                    'room_id' => $detail->room_id,
                    'status_id' => $detail->status_id,
                    'qty' => $detail->qty,
                    'price' => 0,
                    'subtotal' => $detail->subtotal,
                    'start_time' => $detail->start_time,
                    'end_time' => $detail->end_time,
                    'item_id' => $detail->item_id,
                    'item_name' => '',
                    'is_fnb' => $detail->is_fnb,
                    'is_extended' => $detail->is_extended,
                    'is_called' => $detail->is_called,
                    'is_cut' => $detail->is_cut,
                    'is_facility_only' => $detail->is_facility_only,
                    'discount_amount'   => $detail->discount_amount,
                    'additional_person_amount'   => $detail->additional_person_amount,
                    'additional_person_qty'   => $detail->additional_person_qty,
                    'additional_partner_amount'   => $detail->additional_partner_amount,
                    'additional_partner_qty'   => $detail->additional_partner_qty,
                    'discount_percentage'   => $detail->discount_percentage,
                    'membership_id'   => $detail->membership_id,
                    'subtotal_after_commission'   => $detail->subtotal_after_commission,
                    'extension_times'   => $detail->extension_times,
                    'extension_amount'   => $detail->extension_amount,
                    'partner_name'      => '',
                ]);

                $dtStartTime = Carbon::parse($detail->start_time);
                $dtEndTime = Carbon::parse($detail->end_time);

                $nDt['start_time'] = $dtStartTime->format('H:i');
                $nDt['end_time'] = $dtEndTime->format('H:i');

                if($detail->package_id != null){
                    $nDt['package_name'] = $detail->package->name;

                    $tmpSubTotal = ($detail->qty * $detail->price);
                    //$nDt['subtotal'] = $tmpSubTotal - ($config->value_int * $detail->package->partner_count);
                    $nDt['subtotal'] = $tmpSubTotal;
                    if($userData->branch_id != 1){
                        $nDt['subtotal'] = $tmpSubTotal;
                    }

                    $nDt['price'] = $detail->price;
                    if($detail->package->is_birthday_promo){
                        $nDt['price'] = 0;
                    }

                    if($detail->package->id == 15 || $detail->package->id == 16 || $detail->package->id == 17){
                        $nDt['subtotal'] = $tmpSubTotal;
                    }
                    if($branch->is_oura === 1){
                        //if oura
                        $dbPartner = TransactionDetailPartner::where('transaction_detail_id', $detail->id)->first();
                        if($dbPartner != null){
                            $tmpDbPartner = Partner::where('id', $dbPartner->partner_id)->first();
                            if($tmpDbPartner != null){
                                $nDt['partner_name'] = $tmpDbPartner->name;
                            }
                        }
                    }
                    //dd($tmpSubTotal, $detail->discount_amount, $detail->discount_percentage);
                    //dd($detail->discount_amount);
                    //dd($detail->discount_amount);
                }else if($detail->item_id != null){
                    $nDt['price'] = $detail->price;
                    $nDt['item_name'] = $detail->item->name;
                    if($detail->subtotal == 0){
                        $nDt['price'] = 0;
                    }
                }
                else{
                    $nDt['subtotal'] = $detail->subtotal - $detail->discount_amount;
                }

                $detailsNew->push($nDt);
            }

            $trxPackage = TransactionDetail::where('header_id', $header->id)->where('package_id', '!=', null)->first();
            $partners = [];
            $room = '';
            if($trxPackage != null){
                $tmpPartners = TransactionDetailPartner::where('transaction_detail_id', $trxPackage->id)->get();
                foreach ($tmpPartners as $tmp){
                    $tmpPartner = Partner::where('id', $tmp->partner_id)->first();
                    $nPartner = [
                        'number'    => $tmpPartner->number,
                        'name'      => $tmpPartner->name,
                    ];
                    $partners[] = $nPartner;
                }

                //getting room number
                $tmpRoom = Room::where('id', $trxPackage->room_id)->first();
                if($tmpRoom != null){
                    $room = $tmpRoom->number;
                }
                else{
                    $room = '';
                }
            }

            $tmpDate = Carbon::parse($header->created_at);
            $trxDate = $tmpDate->locale('id_ID')->translatedFormat('l, d-m-Y');

            $tmpStart = Carbon::parse($header->start_time);
            $tmpEnd = Carbon::parse($header->end_time);
            $startTime = $tmpStart->format('H:i');
            $endTime = $tmpEnd->format('H:i');
            //get payments
            $payments = TransactionPayment::where('header_id', $header->id)->get();

            $cashierName = $header->updatedBy->name;
            $dbEmployee = Employee::where('id', $header->updatedBy->employee_id)->first();
            if($dbEmployee != null){
                $cashierName = $dbEmployee->name;
            }

            $couponCode = 'none';
            if(!empty($header->extra_voucher_code)){
                $coupon = CouponList::where('code', $header->extra_voucher_code)->first();
                // dd($coupon);
                $couponCode = $coupon->code;
            }

            // Check home payment fee and point usage
            $homePaymentFee = 0;
            $pointUsed = 0;
            if($branch->is_oura === 1 && $payments->contains('payment_type_code', '=', 'ONLINE')){
                $bookingHeader = DB::table('booking_headers')
                    // ->where('type', '=', 'home_service')
                    ->where('transaction_id', '=', $header->id)
                    ->first();

                if(!empty($bookingHeader)){
                    $homePaymentFee = intval($bookingHeader->payment_extra_charge);
                    $pointUsed = intval($bookingHeader->point_used);
                }
                // else{
                //     $homePaymentFee = $branch->home_service_fee;
                // }
            }

            $data = [
                'trxDate'   => $trxDate,
                'branch'    => $branch,
                'header'    => $header,
                'details'   => $detailsNew,
                'partners'  => $partners,
                'time'      => $startTime . ' s/d ' . $endTime,
                'payments'  => $payments,
                'room'      => $room,
                'cashierName'=>$cashierName,
                'coupon'    => $couponCode,
                'homePaymentFee' => $homePaymentFee,
                'pointUsed' => $pointUsed
            ];

            if($user->branch->is_oura == 0){
                return view('admin.monitor_locker.receipt')->with($data);
            }
            else{
                return view('admin.monitor_locker.receipt_oura')->with($data);
            }
        }
        catch (\Exception $ex){
            dd($ex);
            Session::flash('error', $ex);
            Log::error($ex);
            return Redirect::route('admin.monitor_locker.index');
        }
    }

    public function secondReceipt(Request $request){
        try{
            $user = Auth::guard('admin')->user();
            $userData = AdminUser::where('id', $user->id)->first();
            $branch = Branch::where('id', $userData->branch_id)->first();
            $header = TransactionHeader::with(['locker', 'employee', 'updatedBy'])
                ->where('id', $request->input('id'))->first();
            if($header == null){
                Session::flash('error', 'Transaksi tidak ditemukan!');
                return Redirect::route('admin.monitor_locker.index');
            }

            $details = TransactionDetail::with(['package', 'item'])
                ->where('header_id', $header->id)
                //->where('is_cut', '!=', 1)
                ->get();

            //make new list
            $header->grand_total = 0;
            $detailsNew = new Collection();
            $config = Configuration::where('branch_id', $userData->branch_id)->where('key', 'package_cut')->first();
            foreach ($details as $detail){
                $nDt = collect([
                    'id'        => $detail->id,
                    'header_id' => $detail->header_id,
                    'package_id' => $detail->package_id,
                    'package_name' => '',
                    'room_id' => $detail->room_id,
                    'status_id' => $detail->status_id,
                    'qty' => $detail->qty,
                    'price' => 0,
                    'subtotal' => $detail->subtotal,
                    'start_time' => $detail->start_time,
                    'end_time' => $detail->end_time,
                    'item_id' => $detail->item_id,
                    'item_name' => '',
                    'is_fnb' => $detail->is_fnb,
                    'is_extended' => $detail->is_extended,
                    'is_called' => $detail->is_called,
                    'is_cut' => $detail->is_cut,
                    'is_facility_only' => $detail->is_facility_only,
                    'discount_amount'   => $detail->discount_amount,
                    'additional_person_amount'   => $detail->additional_person_amount,
                    'additional_person_qty'   => $detail->additional_person_qty,
                    'additional_partner_amount'   => $detail->additional_partner_amount,
                    'additional_partner_qty'   => $detail->additional_partner_qty,
                    'discount_percentage'   => $detail->discount_percentage,
                    'membership_id'   => $detail->membership_id,
                    'subtotal_after_commission'   => $detail->subtotal_after_commission,
                    'extension_times'   => $detail->extension_times,
                    'extension_amount'   => $detail->extension_amount,
                    'partner_name'      => '',
                ]);

                $dtStartTime = Carbon::parse($detail->start_time);
                $dtEndTime = Carbon::parse($detail->end_time);

                $nDt['start_time'] = $dtStartTime->format('H:i');
                $nDt['end_time'] = $dtEndTime->format('H:i');

                if($detail->package_id != null){
                    $nDt['package_name'] = $detail->package->name;

                    $tmpSubTotal = ($detail->qty * $detail->package->second_price);
                    $nDt['subtotal'] = $tmpSubTotal - ($config->value_int * $detail->package->partner_count);
                    if($userData->branch_id != 1){
                        $nDt['subtotal'] = $tmpSubTotal;
                    }
                    $nDt['price'] = $detail->package->second_price;

                    if($detail->package->id == 15 || $detail->package->id == 16 || $detail->package->id == 17){
                        $nDt['subtotal'] = $tmpSubTotal;
                    }
                    if($user->branch->is_oura == 1){
                        //if oura
                        $dbPartner = TransactionDetailPartner::where('transaction_detail_id', $detail->id)->first();
                        if($dbPartner != null){
                            $tmpDbPartner = Partner::where('id', $dbPartner->partner_id)->first();
                            if($tmpDbPartner != null){
                                $nDt['partner_name'] = $tmpDbPartner->name;
                            }
                        }
                    }
                    //dd($tmpSubTotal, $detail->discount_amount, $detail->discount_percentage);
                    //dd($detail->discount_amount);
                    //dd($detail->discount_amount);
                }
                else{
                    $nDt['subtotal'] = $detail->subtotal - $detail->discount_amount;
                }

                $header->grand_total += $nDt['subtotal'];
                $detailsNew->push($nDt);
            }

            $trxPackage = TransactionDetail::where('header_id', $header->id)->where('package_id', '!=', null)->first();
            $partners = [];
            $room = '';
            if($trxPackage != null){
                $tmpPartners = TransactionDetailPartner::where('transaction_detail_id', $trxPackage->id)->get();
                foreach ($tmpPartners as $tmp){
                    $tmpPartner = Partner::where('id', $tmp->partner_id)->first();
                    $nPartner = [
                        'number'    => $tmpPartner->number,
                        'name'      => $tmpPartner->name,
                    ];
                    $partners[] = $nPartner;
                }

                //getting room number
                $tmpRoom = Room::where('id', $trxPackage->room_id)->first();
                if($tmpRoom != null){
                    $room = $tmpRoom->number;
                }
                else{
                    $room = '';
                }
            }

            $tmpDate = Carbon::parse($header->created_at);
            $trxDate = $tmpDate->locale('id_ID')->translatedFormat('l, d-m-Y');

            $tmpStart = Carbon::parse($header->start_time);
            $tmpEnd = Carbon::parse($header->end_time);
            $startTime = $tmpStart->format('H:i');
            $endTime = $tmpEnd->format('H:i');
            //get payments
            $payments = TransactionPayment::where('header_id', $header->id)->get();

            $cashierName = $header->updatedBy->name;
            $dbEmployee = Employee::where('id', $header->updatedBy->employee_id)->first();
            if($dbEmployee != null){
                $cashierName = $dbEmployee->name;
            }

            // dd("test");

            $couponCode = 'none';
            if(!empty($header->extra_voucher_code)){
                $coupon = CouponList::where('code', $header->extra_voucher_code)->first();
                // dd($coupon);
                $couponCode = $coupon->code;
            }

            $data = [
                'trxDate'   => $trxDate,
                'branch'    => $branch,
                'header'    => $header,
                'details'   => $detailsNew,
                'partners'  => $partners,
                'time'      => $startTime . ' s/d ' . $endTime,
                'payments'  => $payments,
                'room'      => $room,
                'cashierName'=>$cashierName,
                'coupon'    => $couponCode,
            ];

            //dd($payments);

            if($user->branch_id != 6){
                return view('admin.monitor_locker.receipt')->with($data);
            }
            else{
                return view('admin.monitor_locker.receipt_oura')->with($data);
            }
        }
        catch (\Exception $ex){
            Session::flash('error', $ex);
            Log::error($ex);
            return Redirect::route('admin.monitor_locker.index');
        }
    }

    public function changePackage(Request $request){
        try{
            $rules = array(
                'package_id'    => 'required',
                'locker_id'     => 'required',
            );

            $message = array(
                'package_id.required'    => 'Paket wajib dipilih terlebih dahulu!',
            );

            $validator = Validator::make($request->all(), $rules, $message);

            if($validator->fails()){
                return Response::json(['error' => $validator->errors()->all()]);
            }

            $package = Package::where('id', $request->input('package_id'))->first();
            if($package == null){
                return Response::json(['error' => 'Package tidak ditemukan!'], 404);
            }

            $locker = Locker::where('id', $request->input('locker_id'))->first();
            if($locker == null){
                return Response::json(['error' => 'Locker tidak ditemukan!'], 404);
            }

            $header = TransactionHeader::where('locker_id', $request->input('locker_id'))
                ->where('status_id', 8)
                ->first();

            $detail = TransactionDetail::where('header_id', $header->id)->where('package_id', '!=', null)->first();
            //check on curr package
            $currPackage = Package::where('id', $detail->package_id)->first();
            if($package->duration < $currPackage->duration){
                //cannot choose duration lebih rendah
                return Response::json('Tidak bisa pilih paket ini! Paket yg dipilih durasinya lebih sedikit!', 400);
            }

            //check on time
            $startTime = Carbon::parse($detail->start_time);
            $now = Carbon::now('Asia/Jakarta');
            $diffMin = $now->diffInMinutes($startTime);
            if($diffMin > 15){
                return Response::json('Tidak bisa ubah paket! Sudah lewat 15 menit!', 400);
            }

            $detail->package_id = $package->id;
            $detail->save();

            return Response::json(['success' => 'Sukses mengubah Paket!'], 200);
        }
        catch (\Exception $ex){
            return Response::json($ex, 500);
        }
    }

    public function generateRooms(){
        try{
            for($i=31; $i<101; $i++){
                $lNumber = 'Locker ' . $i;
                $locker = new Locker();
                $locker->number = $i;
                $locker->status_id = 5;
                $locker->branch_id = 1;
                $locker->created_by = 1;
                $locker->name = $lNumber;
                $locker->description = '-';
                $locker->save();
            }
//            $athena = new Room();
//            $athena->status_id = 5;
//            $athena->branch_id = 1;
//            $athena->name = 'Athena/Emperor Room';
//            $athena->number = '888A';
//            $athena->type = 'athena';
//            $athena->save();
//
//            for($i = 1; $i<13; $i++){
//                if($i != 4){
//                    $number = '20' . $i;
//                    $royal = new Room();
//                    $royal->status_id = 5;
//                    $royal->branch_id = 1;
//                    $royal->name = 'Royal';
//                    $royal->number = $number;
//                    $royal->type = 'royal';
//                    $royal->save();
//                }
//            }
//
//            for($i = 0; $i<3; $i++){
//                $number = '22' . $i;
//                $royal = new Room();
//                $royal->status_id = 5;
//                $royal->branch_id = 1;
//                $royal->name = 'Royal';
//                $royal->number = $number;
//                $royal->type = 'royal';
//                $royal->save();
//            }
//
//            for($i = 5; $i<10; $i++){
//                $number = '21' . $i;
//                $caesar = new Room();
//                $caesar->status_id = 5;
//                $caesar->branch_id = 1;
//                $caesar->name = 'Caesar';
//                $caesar->number = $number;
//                $caesar->type = 'caesar';
//                $caesar->save();
//            }
//
//            for($i = 1; $i<9; $i++){
//                if($i != 4){
//                    $number = '30' . $i;
//                    $aphrodite = new Room();
//                    $aphrodite->status_id = 5;
//                    $aphrodite->branch_id = 1;
//                    $aphrodite->name = 'Aphrodite';
//                    $aphrodite->number = $number;
//                    $aphrodite->type = 'aphrodite';
//                    $aphrodite->save();
//                }
//            }
//
//            $athena = new Room();
//            $athena->status_id = 5;
//            $athena->branch_id = 1;
//            $athena->name = 'Kingsman';
//            $athena->number = '306';
//            $athena->type = 'kingsman';
//            $athena->save();

            return Response::json('Sukses', 200);
        }
        catch (\Exception $ex){
            return Response::json($ex, 500);
        }
    }
}
