<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Libs\AutoNumberGenerator;
use App\Libs\Utilities;
use App\Models\AdminUser;
use App\Models\Branch;
use App\Models\Coa;
use App\Models\CommissionEmployeeHistory;
use App\Models\CommissionPartnerHistory;
use App\Models\CommissionPartnerLevelPackage;
use App\Models\CommissionPositionItem;
use App\Models\Configuration;
use App\Models\Employee;
use App\Models\Inventory;
use App\Models\InventoryHistory;
use App\Models\Item;
use App\Models\JournalDetail;
use App\Models\JournalHeader;
use App\Models\JournalMap;
use App\Models\Locker;
use App\Models\Member;
use App\Models\Membership;
use App\Models\Package;
use App\Models\Partner;
use App\Models\PosPaymentType;
use App\Models\Room;
use App\Models\RoomType;
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

class PosNewController extends Controller
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
                'gro'       => $partner
            ];

            return Response::json($data, 200);
        }
        catch (\Exception $ex){
            return Response::json($ex, 500);
        }
    }

    public function getItems(Request $request)
    {
        try{
            $skip = 0;
            $take = 10;
            if($request->exists('skip')){
                $skip = $request->query('skip');
            }

            if($request->exists('take')){
                $take = $request->query('take');
            }
            $tmpItems = Item::with('item_category');
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

    public function getPaymentTypes(Request $request)
    {
        try{
            $payments = PosPaymentType::all();

            return Response::json($payments, 200);
        }
        catch (\Exception $ex){
            return Response::json($ex, 500);
        }
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

            $user = Auth::guard('admin')->user();
            $userData = AdminUser::find($user->id);

            //foc types
            //FOC = full FOC (100% on Everything)
            //FOC_PACKAGE = FOC only on package
            //FOC_ITEM = FOC only on item
            $foc = $request->input('foc');
            $focNumber = $request->input('foc_number');

            $dtItems = TransactionDetail::where('header_id', $header->id)->where('item_id', '!=', null)->get();
            foreach ($dtItems as $dt){
                //check item in DB
                if($foc == 'FOC_ITEM' || $foc == 'FOC'){
                    //discount
                    $dt->discount_percentage = 100;
                    $dt->discount_amount = $dt->subtotal;
                }
                //check on inventory
                $wh = Warehouse::where('branch_id', $userData->branch_id)->where('name', 'like', '%Store%')->first();
                if($wh != null){
                    $inv = Inventory::where('item_id', $dt->item_id)
                        ->where('warehouse_id', $wh->id)
                        ->where('branch_id', $userData->branch_id)
                        ->first();

                    if($inv != null){
                        $inv->qty -= $dt->qty;
                        $inv->save();

                        $itemModel = DB::table('items')
                            ->where('id', '=', $dt->item_id)
                            ->first();

                        //save inv histor
                        $invHistory = new InventoryHistory();
                        $invHistory->reference = $header->code;
                        $invHistory->warehouse_id = $wh->id;
                        $invHistory->warehouse_name = $wh->name;
                        $invHistory->branch_id = $userData->branch_id;
                        $invHistory->created_by = $userData->id;
                        $invHistory->item_id = $dt->item_id;
                        $invHistory->item_code = $itemModel->code;
                        $invHistory->item_name = $itemModel->name;
                        $invHistory->description = 'Sales untuk ' . $header->code;
                        $invHistory->qty_add = 0;
                        $invHistory->qty_sub = $dt->qty;
                        $invHistory->qty_result = $inv->qty;
                        $invHistory->qty_total = $inv->qty;
                        $invHistory->save();
                    }
                }
            }

//            if(!empty($request->input('items'))){
//                //delete current items
//                //TransactionDetail::where('header_id', $header->id)->where('item_id', '!=', null)->delete();
//
//                //if item details is not empty
//                $items = $request->input('items');
//                foreach ($items as $item){
//                    $dt = TransactionDetail::where('header_id', $header->id)->where('item_id', $item['id'])->first();
//                    //check item in DB
//                    if($foc == 'FOC_ITEM' || $foc == 'FOC'){
//                        //discount
//                        $dt->discount_percentage = 100;
//                        $dt->discount_amount = $dt->subtotal;
//                    }
//                    //check on inventory
//                    $wh = Warehouse::where('branch_id', $userData->branch_id)->where('name', 'like', '%Store%')->first();
//                    if($wh != null){
//                        $inv = Inventory::where('item_id', $dt->item_id)
//                            ->where('warehouse_id', $wh->id)
//                            ->where('branch_id', $userData->branch_id)
//                            ->first();
//
//                        if($inv != null){
//                            $inv->qty -= intval($item['qty']);
//                            $inv->save();
//
//                            $itemModel = DB::table('items')
//                                ->where('id', '=', $dt->item_id)
//                                ->first();
//
//                            //save inv histor
//                            $invHistory = new InventoryHistory();
//                            $invHistory->reference = $header->code;
//                            $invHistory->warehouse_id = $wh->id;
//                            $invHistory->warehouse_name = $wh->name;
//                            $invHistory->branch_id = $userData->branch_id;
//                            $invHistory->created_by = $userData->id;
//                            $invHistory->item_id = $dt->item_id;
//                            $invHistory->item_code = $itemModel->code;
//                            $invHistory->item_name = $itemModel->name;
//                            $invHistory->description = 'Sales untuk ' . $header->code;
//                            $invHistory->qty_add = 0;
//                            $invHistory->qty_sub = intval($item['qty']);
//                            $invHistory->qty_result = $inv->qty;
//                            $invHistory->qty_total = $inv->qty;
//                            $invHistory->save();
//                        }
//                    }
//                }
//            }

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
                    $newMember->join_date = Carbon::now('Asia/Jakarta');
                    $newMember->duration = $dbMembership->duration;
                    $newMember->is_infinite = $dbMembership->is_infinite;
                    $newMember->membership_id = $dbMembership->id;
                    $newMember->created_by = $user->id;
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
                                    $tmpItem = DB::table('items')
                                        ->where('id', '=', $tmpTrxDetail->item_id);
//                                    if(!empty($membershipBenefit->item_category_id)){
//                                        $tmpItem = $tmpItem->where('category_id', '=', $membershipBenefit->item_category_id);
//                                    }

                                    $tmpItem = $tmpItem
                                        ->whereIn('category_id', [2,4])
                                        ->first();
                                    if(!empty($tmpItem)){
                                        $isMembershipBenefitApplied = true;
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
                                            $tmpTrxDetail->discount_amount = $tmpTrxDetail->subtotal;
                                            $tmpTrxDetail->discount_percentage = 100;
                                            $tmpTrxDetail->member_id = $memberModel->id;
                                            $tmpTrxDetail->save();

                                            $isFreeUsed = true;
                                        }
                                        // Unhandled case
                                    }
                                    else{
                                        //error_log($membershipBenefit->discount_percentage);
                                        if($membershipBenefit->discount_percentage > 0){
                                            $membershipDisc = (floatval($tmpTrxDetail->price) * $tmpTrxDetail->qty) * floatval($membershipBenefit->discount_percentage / 100);
                                        }
                                        else{
                                            $membershipDisc = floatval($membershipBenefit->discount_flat) > 0 ? floatval($membershipBenefit->discount_flat) : 0;
                                        }
                                    }

                                    if($membershipDisc > 0){
                                        $tmpTrxDetail->discount_amount = $membershipDisc;
                                        $tmpTrxDetail->subtotal = (floatval($tmpTrxDetail->price) * $tmpTrxDetail->qty) - $membershipDisc;
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

            if($foc != 'NO'){
                $header->foc_type = $foc;
                $header->foc_number = $focNumber;
            }
            $totalQty = DB::table('transaction_details')->where('header_id', $header->id)->sum('qty');
            $totalTrx = DB::table('transaction_details')->where('header_id', $header->id)->sum('subtotal');
            $totalDiscount = DB::table('transaction_details')->where('header_id', $header->id)->sum('discount_amount');
            $header->discount = $totalDiscount;
//            $header->grand_total = $totalTrx - $totalDiscount;
            //count global discount
            if($request->exists('header_discount')){
                $headerDiscount = Utilities::toFloat($request->input('header_discount'));
                $header->discount += $headerDiscount;
            }
            $header->amount = $totalTrx - $header->discount;
            $header->grand_total = $totalTrx;
            $header->total_qty = $totalQty;
            $header->return_value = $request->input('change');
            $header->end_time = Carbon::now('Asia/Jakarta');
            $header->status_id = 9;
            $header->updated_by = $user->id;
            $header->save();

            //insert payments
            //also insert journal for kas
            $journal = new JournalHeader();
            $journal->transaction_code = AutoNumberGenerator::generateAutonumberTrx('JN');
            $journal->transaction_type = 'POS';
            $journal->transaction_date = Carbon::now('Asia/Jakarta');
            $journal->created_by = $userData->id;
            $journal->branch_id = $userData->branch_id;
            $journal->save();

            $payments = $request->input('payments');
            foreach ($payments as $payment){
                $dbPayment = PosPaymentType::where('id', $payment['id'])->first();
                if($dbPayment != null){
                    $nPayment = new TransactionPayment();
                    $nPayment->header_id = $header->id;
                    $nPayment->payment_type_id = $payment['id'];
                    $nPayment->payment_type_code = $dbPayment->code;
                    $nPayment->amount = $payment['amount'];
                    if($foc == 'FOC'){
                        //discount
                        $nPayment->amount = 0;
                    }
                    $nPayment->created_by = $user->id;
                    $nPayment->save();

                    //check for the coas
                    //if cash
                    if($nPayment->payment_type_id == 1){
                        $maps = JournalMap::where('transaction_type', 'POS_CASH')->get();
                        foreach ($maps as $map){
                            if($map->coa_code == 'search'){
                                //search for coa first per package types
                                $tmpTrxPackage = TransactionDetail::where('header_id', $header->id)->where('package_id', '!=', null)->get();
                                foreach ($tmpTrxPackage as $trxDt){
                                    $searchQuery = '';

                                    $tmpPackage = Package::where('id', $trxDt->package_id)->first();
                                    if($tmpPackage != null){
                                        $tmpPackageType = RoomType::where('id', $tmpPackage->room_type_id)->first();
                                        if($tmpPackageType != null){
                                            $searchQuery = $tmpPackageType->display;
                                        }
                                    }

                                    if($searchQuery != ''){
                                        //success determining package types
                                        $tmpCoa = Coa::where('name', 'like', '%' . $searchQuery . '%')->first();
                                        if($tmpCoa != null){
                                            $journalDetail = new JournalDetail();
                                            $journalDetail->header_id = $journal->id;
                                            $journalDetail->transaction_code = $header->code;
                                            $journalDetail->account_id = $tmpCoa->id;
                                            $journalDetail->account_code = $tmpCoa->code;
                                            $journalDetail->account_name = $tmpCoa->name;
                                            $journalDetail->account_type_id = $tmpCoa->type_id;
                                            $journalDetail->account_type_name = $tmpCoa->type;
                                            $journalDetail->original_amount = floatval($trxDt->price * $trxDt->qty);
                                            $journalDetail->credit = floatval($trxDt->price * $trxDt->qty);
                                            $journalDetail->save();
                                        }
                                    }
                                }
                            }
                            else{
                                //insert journal details
                                $tmpCoa = Coa::where('id', $map->coa_id)->first();
                                if($tmpCoa != null){
                                    $journalDetail = new JournalDetail();
                                    $journalDetail->header_id = $journal->id;
                                    $journalDetail->transaction_code = $header->code;
                                    $journalDetail->account_id = $tmpCoa->id;
                                    $journalDetail->account_code = $tmpCoa->name;
                                    $journalDetail->account_name = $tmpCoa->code;
                                    $journalDetail->account_type_id = $tmpCoa->type_id;
                                    $journalDetail->account_type_name = $tmpCoa->type;

                                    if(str_contains($tmpCoa->name, 'Kasir')){
                                        //discount coa
                                        $journalDetail->original_amount = $nPayment->amount;
                                        $journalDetail->debit = $header->discount;
                                    }
                                    else if(str_contains($tmpCoa->name, 'Potongan')){
                                        //discount coa
                                        $journalDetail->original_amount = $header->discount;
                                        $journalDetail->debit = $header->discount;
                                    }
                                    else if(str_contains($tmpCoa->name, 'HPP'))
                                    {

                                    }
                                    $journalDetail->save();
                                }
                            }
                        }
                    }
                }
            }

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
                        //->where('is_extend', '=', 0)
                        ->get();

                    $subtotalCommission = 0;
                    foreach ($partners as $tmp){
                        $partner = Partner::with('partner_level')->where('id', $tmp->partner_id)->first();

                        if($partner != null){
                            //count commission for partners (terapis)
                            $partnerCommission = CommissionPartnerLevelPackage::where('branch_id', $header->branch_id)
                                ->where('partner_level', $partner->partner_level->name)
                                ->where('status_id', '=', 1)
                                ->first();
                            if($partnerCommission != null){
                                $afterDeduction = floatval($trxPackage->price) - floatval($partnerCommission->deduction_before);
                                if($partnerCommission->commission_percentage > 0){
                                    $commissionAdd = $afterDeduction * floatval($partnerCommission->commission_percentage / 100);
                                }
                                else{
                                    $commissionAdd = floatval($partnerCommission->commission_flat) > 0 ? floatval($partnerCommission->commission_flat) : 0;
                                }

                                if($commissionAdd > 0){
                                    if($partnerCommission->divider_after > 0){
                                        $commissionAdd = $commissionAdd / $partnerCommission->divider_after;
                                    }

                                    $subtotalCommission += $commissionAdd;
                                    $totalCommission += $commissionAdd;

                                    $pCHistory = new CommissionPartnerHistory();
                                    $pCHistory->branch_id = $header->branch_id;
                                    $pCHistory->partner_id = $partner->id;
                                    $pCHistory->transaction_id = $header->id;
                                    $pCHistory->transaction_code = $header->code;
                                    $pCHistory->transaction_date = $header->created_at;
                                    $pCHistory->commission_percentage = $partnerCommission->commission_percentage;
                                    $pCHistory->partner_name = $partner->name;
                                    $pCHistory->partner_number = $partner->number;
                                    $pCHistory->partner_type = $partner->type;
                                    $pCHistory->partner_alias = $partner->alias;
                                    $pCHistory->package_id = $package->id;
                                    $pCHistory->package_name = $package->name;
                                    $pCHistory->package_code = $package->code;
                                    //counting commission
                                    $pCHistory->commission_add = $commissionAdd;
                                    $pCHistory->commission_result = $commissionAdd;
                                    $pCHistory->save();
                                }
                            }

                            $partner->duration = 900;
                            $partner->status_id = 12;
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
                    $trxPackage->subtotal_after_commission = $trxPackage->subtotal - $subtotalCommission - $trxPackage->extension_amount;
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

                    // Check for existing commissioned items
                    if(DB::table('commission_partner_level_items')
                        ->where('branch_id', '=', $header->branch_id)
                        ->where('item_id', '=', $trxItem->item_id)
                        ->exists()){

                        // Get trx detail package type
                        $detailTrxPackageIds = DB::table('transaction_details')
                            ->where('header_id', '=', $header->id)
                            ->whereNotNull('package_id')
                            ->pluck('id');

                        if(!empty($detailTrxPackageIds) && $detailTrxPackageIds->count() > 0){
                            $detailTrxPartner = DB::table('transaction_detail_partners')
                                ->join('partners', 'transaction_detail_partners.partner_id', '=', 'partners.id')
                                ->select('partners.id as partner_id',
                                    'partners.code as partner_code',
                                    'partners.name as partner_name',
                                    'partners.number as partner_number',
                                    'partners.alias as partner_alias',
                                    'partners.type as partner_level',
                                    'partners.is_can_use_hotstone as is_can_use_hotstone')
                                ->whereIn('transaction_detail_id',  $detailTrxPackageIds)
                                ->first();

                            error_log($detailTrxPartner->partner_id);

                            if(!empty($detailTrxPartner)){
                                $tmpItemCommission = DB::table('commission_partner_level_items')
                                    ->where('branch_id', '=', $header->branch_id)
                                    ->where('item_id', '=', $trxItem->item_id)
                                    ->where('partner_level', '=', $detailTrxPartner->partner_level)
                                    ->first();

                                if(!empty($tmpItemCommission)){
                                    if($tmpItemCommission->commission_percentage > 0){
                                        $commissionAdd = floatval($trxItem->price) * floatval($tmpItemCommission->commission_percentage / 100);
                                    }
                                    else{
                                        $commissionAdd = floatval($tmpItemCommission->commission_flat) > 0 ? floatval($tmpItemCommission->commission_flat) : 0;
                                    }

                                    if($commissionAdd > 0){
                                        $totalEmployeeCommission += $commissionAdd;

                                        error_log($detailTrxPartner->partner_id);
                                        error_log($detailTrxPartner->partner_name);

                                        $pCHistory = new CommissionPartnerHistory();
                                        $pCHistory->branch_id = $header->branch_id;
                                        $pCHistory->partner_id = $detailTrxPartner->partner_id;
                                        $pCHistory->transaction_id = $header->id;
                                        $pCHistory->transaction_code = $header->code;
                                        $pCHistory->transaction_date = $header->created_at;
                                        $pCHistory->commission_percentage = $partnerCommission->commission_percentage;
                                        $pCHistory->partner_name = $detailTrxPartner->partner_name;
                                        $pCHistory->partner_number = $detailTrxPartner->partner_number;
                                        $pCHistory->partner_type = $detailTrxPartner->partner_level;
                                        $pCHistory->partner_alias = $detailTrxPartner->partner_alias;
                                        $pCHistory->item_id = $item->id;
                                        $pCHistory->item_name = $item->name;
                                        $pCHistory->item_code = $item->code;

                                        //counting commission
                                        $pCHistory->commission_add = $commissionAdd;
                                        $pCHistory->commission_result = $commissionAdd;
                                        $pCHistory->save();

                                        $trxItem->subtotal_after_commission = $trxItem->subtotal - $commissionAdd;
                                        $trxItem->save();
                                    }
                                }
                            }
                        }


                    }
                }
            }

            if($totalEmployeeCommission > 0){
                $header->total_after_commission = $tmpGrandTotal - $totalEmployeeCommission;
                $header->save();
            }

            //Insert Journal

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
                'trx_id'    => 'required',
            );

            $message = array(
                'locker_id.required'    => 'Locker wajib dipilih!',
                'trx_id.required'       => 'Transaksi wajib dipilih!',
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
                            if($dbItem->category_id == 2){
                                $nDetail->is_fnb = 1;
                                $nDetail->new_order_qty = $item['qty'];
                                $nDetail->is_new_order = 1;
                            }
                            $nDetail->save();
                        }
                        else{
                            $dt->new_order_qty = $item['qty'];
                            $dt->qty += $item['qty'];
                            $dt->subtotal += (floatval($dbItem->price) * intval($item['qty'])) ;
                            $dt->is_new_order = 1;
                            $dt->save();
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
            $header = TransactionHeader::with(['locker', 'employee'])->where('id', $request->input('id'))->first();
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
                ]);

                if($detail->package_id != null){
                    $nDt['package_name'] = $detail->package->name;

                    $tmpSubTotal = ($detail->qty * $detail->package->price);
                    $nDt['subtotal'] = $tmpSubTotal - ($config->value_int * $detail->package->partner_count);
                    $nDt['price'] = $detail->package->price;
                    //dd($tmpSubTotal, $detail->discount_amount, $detail->discount_percentage);
                    //dd($detail->discount_amount);
                    //dd($detail->discount_amount);
                }else if($detail->item_id != null){
                    $nDt['price'] = $detail->item->price;
                    $nDt['item_name'] = $detail->item->name;
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

            $data = [
                'trxDate'   => $trxDate,
                'branch'    => $branch,
                'header'    => $header,
                'details'   => $detailsNew,
                'partners'  => $partners,
                'time'      => $startTime . ' s/d ' . $endTime,
                'payments'  => $payments,
                'room'      => $room,
            ];

            //dd($payments);

            return view('admin.monitor_locker.receipt')->with($data);
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
