<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Libs\AutoNumberGenerator;
use App\Libs\Utilities;
use App\Models\AdminUser;
use App\Models\Configuration;
use App\Models\Employee;
use App\Models\Item;
use App\Models\Locker;
use App\Models\Package;
use App\Models\Partner;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\TempBooking;
use App\Models\TransactionDetail;
use App\Models\TransactionDetailPartner;
use App\Models\TransactionHeader;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class MonitoringLockerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(){
        $user = Auth::guard('admin')->user();
        $userData = AdminUser::where('id', $user->id)->first();

        $lockers = Locker::where('branch_id', $userData->branch_id)
            ->where('status_id', '!=', 2)
            ->get();

        $lockerArr = [];
        foreach ($lockers as $locker){
            $nArr = [
                'id'                        => $locker->id,
                'code'                      => $locker->code,
                'number'                    => $locker->number,
                'element_id'                => 'locker'.$locker->number,
                'status_id'                 => $locker->status_id,
                'element_class'             => 'custom-square mx-auto green',
                'room_type_name'            => 0,
                'room_id'                   => 0,
                'room_number'               => 0,
                'package_name'              => '',
                'package_id'                => 0,
                'duration'                  => 0,
                'timer'                     => 0,
                'already_have_transaction'  => false,
                'is_package_chosen'         => false,
                'is_room_chosen'            => false,
                'is_gro_chosen'             => false,
                'gro_id'                    => 0,
                'gro_code'                  => '',
                'gro_name'                  => '',
                'is_facility_only'          => false,
            ];

            $header = TransactionHeader::with('transaction_details')
                ->where('locker_id', $locker->id)
                ->where('status_id', 8)
                ->first();

            if($header != null){
                if($header->employee_id != null){
                    $employee = Employee::where('id', $header->employee_id)->first();
                    if($employee != null){
                        $nArr['is_gro_chosen'] = true;
                        $nArr['gro_id'] = $employee->id;
                        $nArr['gro_code'] = $employee->number;
                        if($userData->branch_id != 1){
                            $nArr['gro_code'] = $employee->name;
                        }
                        $nArr['gro_name'] = $employee->name;
                    }
                }
                if(count($header->transaction_details) > 0){
                    $trxDt = TransactionDetail::where('header_id', $header->id)
                        ->where('package_id', '!=', null)
                        ->where(function ($query){
                            $query->where('status_id', 8)
                                ->orWhere('status_id', 3);
                        })
                        ->first();

                    if($locker->number == '17'){
                        //dd($trxDt, count($header->transaction_details));
                    }

                    if($trxDt != null){
                        $package = Package::where('id', $trxDt->package_id)->first();
                        if($package != null){
                            $nArr['package_id'] = $package->id;
                            $nArr['package_name'] = $package->name;
                            $nArr['is_package_chosen'] = true;

                            $roomType = RoomType::where('id', $package->room_type_id)->first();
                            if($roomType != null){
                                $nArr['room_type_name'] = $roomType->name;
                            }
                        }
                        if($trxDt->room_id != null && $trxDt->room_id != 0){
                            $room = Room::where('id', $trxDt->room_id)->first();
                            $nArr['room_number'] = $room->number;
                            $nArr['room_id'] = $room->id;
                            if($nArr['room_type_name'] == 0){
                                $nArr['room_type_name'] = $room->type;
                            }
                            $nArr['is_room_chosen'] = true;
                        }
                    }
                    else{
                        $trxDt = TransactionDetail::where('header_id', $header->id)->where('is_facility_only', '=', 1)
                            ->first();
                        if($trxDt != null){
                            $nArr['is_facility_only'] = true;
                        }
                    }

                    $trxCount = TransactionDetail::where('header_id', $header->id)
                        ->where('package_id', '!=', null)
                        ->count();
                    if($trxCount > 0){
                        $nArr['already_have_transaction'] = true;
                    }
                }
            }

            if($locker->status_id == 6){
                $nArr['element_class'] = 'custom-square-red mx-auto';
            }

            $lockerArr[] = $nArr;
        }
        $data = [
            'jsonLockers'    => json_encode($lockerArr),
        ];

        return view('admin.monitor_locker.index')->with($data);
    }

    public function refreshData(){
        try{
            $user = Auth::guard('admin')->user();
            $userData = AdminUser::where('id', $user->id)->first();

            $lockers = Locker::where('branch_id', $userData->branch_id)
                ->where('status_id', '!=', 2)
                ->get();

            $lockerArr = [];
            foreach ($lockers as $locker){
                $nArr = [
                    'id'                        => $locker->id,
                    'code'                      => $locker->code,
                    'number'                    => $locker->number,
                    'element_id'                => 'locker'.$locker->number,
                    'status_id'                 => $locker->status_id,
                    'element_class'             => 'custom-square mx-auto green',
                    'room_type_name'            => 0,
                    'room_id'                   => 0,
                    'room_number'               => 0,
                    'package_name'              => '',
                    'package_id'                => 0,
                    'duration'                  => 0,
                    'already_have_transaction'  => false,
                    'is_package_chosen'         => false,
                    'is_room_chosen'            => false,
                    'is_gro_chosen'             => false,
                    'gro_id'                    => 0,
                    'gro_code'                  => '',
                    'gro_name'                  => '',
                    'is_facility_only'          => false,
                ];

                $header = TransactionHeader::with('transaction_details')
                    ->where('locker_id', $locker->id)
                    ->where('status_id', 8)
                    ->first();

                if($header != null){
                    if($header->employee_id != null){
                        $employee = Employee::where('id', $header->employee_id)->first();
                        if($employee != null){
                            $nArr['is_gro_chosen'] = true;
                            $nArr['gro_id'] = $employee->id;
                            $nArr['gro_code'] = $employee->number;
                            if($userData->branch_id != 1){
                                $nArr['gro_code'] = $employee->name;
                            }
                            $nArr['gro_name'] = $employee->name;
                        }
                    }
                    if(count($header->transaction_details) > 0){
                        $trxDt = TransactionDetail::where('header_id', $header->id)
                            ->where('package_id', '!=', null)
                            ->where(function ($query){
                                $query->where('status_id', 8)
                                    ->orWhere('status_id', 3);
                            })
                            ->orderByDesc('id')
                            ->first();
                        if($trxDt != null){
                            $package = Package::where('id', $trxDt->package_id)->first();
                            if($package != null){
                                $nArr['package_id'] = $package->id;
                                $nArr['package_name'] = $package->name;
                                $nArr['is_package_chosen'] = true;

                                $roomType = RoomType::where('id', $package->room_type_id)->first();
                                if($roomType != null){
                                    $nArr['room_type_name'] = $roomType->name;
                                }
                            }
                            if($trxDt->room_id != null && $trxDt->room_id != 0){
                                $room = Room::where('id', $trxDt->room_id)->first();
                                $nArr['room_number'] = $room->number;
                                $nArr['room_id'] = $room->id;
                                if($nArr['room_type_name'] == 0){
                                    $nArr['room_type_name'] = $room->type;
                                }
                                $nArr['is_room_chosen'] = true;
                            }
                        }
                        else{
                            $trxDt = TransactionDetail::where('header_id', $header->id)->where('is_facility_only', '=', 1)
                                ->first();
                            if($trxDt != null){
                                $nArr['is_facility_only'] = true;
                            }
                        }

                        $trxCount = TransactionDetail::where('header_id', $header->id)
                            ->where('package_id', '!=', null)
                            ->count();
                        if($trxCount > 0){
                            $nArr['already_have_transaction'] = true;
                        }
                    }
                }

                if($locker->status_id == 6){
                    $nArr['element_class'] = 'custom-square-red mx-auto';
                }

                $lockerArr[] = $nArr;
            }

            return Response::json($lockerArr, 200);
        }
        catch (\Exception $ex){
            return Response::json($ex, 500);
        }
    }

    public function checkIn(Request $request){
        try{
            if(!$request->filled('locker_id')){
                return Response::json('Invalid Parameters!', 400);
            }

            $locker = Locker::where('id', $request->input('locker_id'))->first();
            if($locker == null){
                return Response::json('Locker not found!', 404);
            }
            $locker->status_id = 6;
            $locker->save();

            //create Transaction Header
            $user = Auth::guard('admin')->user();

            $header = new TransactionHeader();
            $header->code = AutoNumberGenerator::generateAutonumberTrx('POS');
            $header->branch_id = $locker->branch_id;
            $header->locker_id = $locker->id;
            $header->created_by = $user->id;
            $header->status_id = 8;
            $header->start_time = Carbon::now('Asia/Jakarta');

            $now = Carbon::now('Asia/Jakarta');
            $startHappyHour = Carbon::createFromTime(10, 0, 0);
            $endHappyHour = Carbon::createFromTime(12, 0, 0);
            if($now->greaterThanOrEqualTo($startHappyHour) && $now->lessThan($endHappyHour)){
                $header->is_happy_hour = 1;
            }

            $header->save();

            return Response::json('sukses', 200);
        }
        catch (\Exception $ex){
            return Response::json($ex, 500);
        }
    }

    public function cancel(Request $request){
        try{
            if(!$request->filled('locker_id')){
                return Response::json('Invalid Parameters!', 400);
            }

            if(!$request->filled('password')){
                return Response::json('Password Required!', 400);
            }

            $locker = Locker::where('id', $request->input('locker_id'))->first();
            if($locker == null){
                return Response::json('Locker not found!', 404);
            }

            //check password first
            $user = Auth::guard('admin')->user();
            $userData = AdminUser::where('id', $user->id)->first();
            $config = Configuration::where('key', 'cancel_password')->where('branch_id', $userData->id)->first();
            if($config->value != $request->input('password')){
                return Response::json('Password Salah!', 400);
            }

            $locker->status_id = 5;
            $locker->save();

            //create Transaction Header
            $user = Auth::guard('admin')->user();
            $header = TransactionHeader::where('locker_id', $locker->id)->where('status_id', 8)->first();
            $header->updated_by = $user->id;
            $header->status_id = 10;
            $header->save();

            return Response::json('sukses', 200);
        }
        catch (\Exception $ex){
            return Response::json($ex, 500);
        }
    }

    public function selectPackage(Request $request){
        try{
            $user = Auth::guard('admin')->user();
            $userData = AdminUser::where('id', $user->id)->first();

            $package = Package::where('branch_id', $userData->branch_id)->where('id', $request->input('package_id'))->first();
            if($package == null){
                return Response::json('Paket tidak ditemukan!' . $request, 404);
            }

            $locker = Locker::where('id', $request->input('locker_id'))->first();
            if($locker == null){
                return Response::json('Loker tidak ditemukan!', 404);
            }

            $header = TransactionHeader::where('locker_id', $locker->id)->where('status_id', 8)->first();
            if($header == null){
                return Response::json('Loker tidak memiliki transaksi berjalan! Checkin dulu!', 400);
            }

            //Check transaction
            //Delete Facility Only Transaction if exist
            $tmpFacility = TransactionDetail::where('header_id', $header->id)->where('is_facility_only', 1)->first();
            if($tmpFacility != null){
                $tmpFacility->delete();
            }

            $tmpDt = TransactionDetail::where('header_id', $header->id)->where('package_id', '!=', null)->first();
            $createNew = false;
            if($tmpDt != null){
                //check if its still running
                $room = Room::where('id', $tmpDt->room_id)->first();
                if($room->status_id == 6){
                    //Still in progress
                    return Response::json('Masih ada paket berjalan!', 400);
                }
                else{
                    $createNew = true;
                }
            }
            else{
                $createNew = true;
            }
            if($createNew){
                $configCut = Configuration::where('branch_id', $userData->branch_id)->where('key', 'package_cut')->first();

                $detail = new TransactionDetail();
                $detail->header_id = $header->id;
                $detail->package_id = $package->id;
                $detail->qty = 1;
                $detail->price = $package->price - ($package->partner_count * $configCut->value_int);
                $detail->subtotal = $detail->price;
                $detail->status_id = 3;
                if($header->is_happy_hour == 1){
                    //happy hour time
                    $tmpPrice = $package->price - ($package->partner_count * $configCut->value_int);

//                    $detail->price = $tmpPrice - ($tmpPrice * 0.1);
                    $detail->price = $tmpPrice;
                    $detail->subtotal = $tmpPrice - ($tmpPrice * 0.1);
                    $detail->discount_amount = $tmpPrice * 0.1;
                    $detail->discount_percentage = 10;
                }
                $detail->save();

                $config = Configuration::where('branch_id', $userData->branch_id)
                    ->where('key', 'oil_id')
                    ->first();

                $nDt = new TransactionDetail();
                $nDt->header_id = $header->id;
                $nDt->qty = $package->partner_count;
                $nDt->price = $configCut->value_int;
                $nDt->subtotal = $package->partner_count * $configCut->value_int;
                $nDt->status_id = 1;
                $nDt->item_id = intval($config->value_int);
                $nDt->is_cut = 1;
                if($header->is_happy_hour == 1){
                    //happy hour time
                    $nDt->price = $configCut->value_int;
                    $nDt->subtotal = ($package->partner_count * $configCut->value_int) - ($configCut->value_int * 0.1);
                    $nDt->discount_amount = $configCut->value_int * 0.1;
                    $nDt->discount_percentage = 10;
                }
                $nDt->save();

                return Response::json('sukses', 200);
            }
            else{
                return Response::json('Masih ada paket berjalan!', 400);
            }
        }
        catch (\Exception $ex){
            return Response::json($ex, 500);
        }
    }

    public function selectRoom(Request $request){
        try{
            $user = Auth::guard('admin')->user();
            $userData = AdminUser::where('id', $user->id)->first();

            $room = Room::where('branch_id', $userData->branch_id)->where('id', $request->input('room_id'))->first();
            if($room == null){
                return Response::json('Room tidak ditemukan!' . $request, 404);
            }

            $package = Package::where('id', $request->input('package_id'))->first();
            if($package == null){
                return Response::json('Paket tidak ditemukan!' . $request, 404);
            }

            $locker = Locker::where('id', $request->input('locker_id'))->first();
            if($locker == null){
                return Response::json('Loker tidak ditemukan!', 404);
            }

            $header = TransactionHeader::where('locker_id', $locker->id)->where('status_id', 8)->first();
            if($header == null){
                return Response::json('Loker tidak memiliki transaksi berjalan! Checkin dulu!', 400);
            }

            $detail = TransactionDetail::where('header_id', $header->id)->where('package_id', $package->id)->first();
            if($detail != null){
                $detail->room_id = $room->id;
                $detail->save();

                $room->status_id = 11;
                $room->save();
            }

            return Response::json('sukses', 200);
        }
        catch (\Exception $ex){
            return Response::json($ex, 500);
        }
    }

    public function selectGro(Request $request){
        try{
            $user = Auth::guard('admin')->user();
            $userData = AdminUser::where('id', $user->id)->first();

            $employee = Employee::where('branch_id', $userData->branch_id)->where('id', $request->input('employee_id'))->first();
            if($employee == null){
                return Response::json('GRO tidak ditemukan!' . $request, 404);
            }

            $locker = Locker::where('id', $request->input('locker_id'))->first();
            if($locker == null){
                return Response::json('Loker tidak ditemukan!', 404);
            }

            $header = TransactionHeader::where('locker_id', $locker->id)->where('status_id', 8)->first();
            if($header == null){
                return Response::json('Loker tidak memiliki transaksi berjalan! Checkin dulu!', 400);
            }

            $header->employee_id = $employee->id;
            $header->save();

            return Response::json('sukses', 200);
        }
        catch (\Exception $ex){
            return Response::json($ex, 500);
        }
    }

    public function showScan(){
        return view('admin.monitor_locker.show_scan');
    }

    public function checkScan(Request $request){
        try{
            $user = Auth::guard('admin')->user();
            $userData = AdminUser::where('id', $user->id)->first();
            $lockerNumber = $request->input('locker_number');
            $locker = Locker::where('branch_id', $userData->branch_id)->where('number', $lockerNumber)->first();
            if($locker == null){
                return Response::json('Locker tidak ditemukan!', 404);
            }

            $header = TransactionHeader::where('status_id', 8)->where('locker_id', $locker->id)->first();
            if($header != null){
                if($userData->branch_id == 1){
                    $now = Carbon::now('Asia/Jakarta');
                    $startHappyHour = Carbon::createFromTime(10, 0, 0);
                    $endHappyHour = Carbon::createFromTime(12, 0, 0);
                    if($now->greaterThanOrEqualTo($startHappyHour) && $now->lessThan($endHappyHour)){
                        $header->is_happy_hour = 1;
                        $header->save();
                    }
                }

                $config = Configuration::where('branch_id', $userData->branch_id)->where('key', 'facility')->first();
                $nDt = TransactionDetail::where('header_id', $header->id)->where('is_facility_only', 1)->first();
                if($nDt == null){
                    $detail = new TransactionDetail();
                    $detail->status_id = 8;
                    $detail->header_id = $header->id;
                    $detail->is_facility_only = 1;
                    $detail->qty = 1;
                    $detail->subtotal = $config->value_int;
                    $detail->price = $config->value_int;
                    $detail->start_time = Carbon::now('Asia/Jakarta');

                    if($header->is_happy_hour == 1){
                        //happy hour time
                        $tmpPrice = $config->value_int;
                        $detail->price = $tmpPrice;
                        $detail->subtotal = $tmpPrice - ($tmpPrice * 0.1);
                        $detail->discount_amount = $tmpPrice * 0.1;
                        $detail->discount_percentage = 10;
                    }

                    $detail->save();

                    $locker->status_id = 6;
                    $locker->save();
                }
                else{
                    return Response::json('Locker sudah ada paket Fasilitas!', 400);
                }
            }
            else{
                return Response::json('Locker belum checkin '. $locker->number .'!', 400);
            }

            return Response::json('Sukses scan barcode '. $locker->number .'!', 200);
        }
        catch (\Exception $ex){
            return Response::json('Maaf terjadi kesalahan!', 500);
        }
    }

    public function changePackage(Request $request){
        try{
            if(!$request->filled('password_confirmation')){
                return Response::json('Password harus diisi!', 400);
            }

            $package = Package::where('id', $request->input('package_id'))->first();
            if($package == null){
                return Response::json('Package tidak ditemukan!', 404);
            }

            $locker = Locker::where('id', $request->input('locker_id'))->first();
            if($locker == null){
                return Response::json('Locker tidak ditemukan!', 404);
            }

            $user = Auth::guard('admin')->user();
            $userData = AdminUser::find($user->id);
            $config = Configuration::where('branch_id', $userData->branch_id)->where('key', 'cancel_password')->first();
            if($config->value != $request->input('password_confirmation')){
                return Response::json('Salah Password!', 400);
            }

            $header = TransactionHeader::where('locker_id', $request->input('locker_id'))
                ->where('status_id', 8)
                ->first();

            //check time
//            $startTime = Carbon::parse($header->created_at);
//            $now = Carbon::now('Asia/Jakarta');
//            if($now->diffInMinutes($startTime) >= 15){
//                return Response::json('Tidak bisa ubah paket karena sudah lewat 15 menit!', 400);
//            }

            $detail = TransactionDetail::where('header_id', $header->id)->where('package_id', '!=', null)->first();

            //check on package
            $currPackage = Package::where('id', $detail->package_id)->first();
            $room = Room::where('id', $detail->room_id)->first();
            if($room->type != $currPackage->room_type->name){
                return Response::json('Paket yang dipilih beda dengan tipe room!', 400);
            }

            if($currPackage->id == $package->id){
                return Response::json('Paket yang dipilih sama!', 400);
            }

            $detail->extension_amount = 0;
            $detail->extension_times = 0;
            $detail->is_extended = 0;
            $detail->additional_person_amount = 0;
            $detail->additional_person_qty = 0;
            $detail->additional_partner_amount = 0;
            $detail->additional_partner_qty = 0;
            if($userData->branch_id == 1){
                $configCut = Configuration::where('branch_id', $userData->branch_id)->where('key', 'package_cut')->first();
                $detail->price = $package->price - $configCut->value_int;
                $detail->subtotal = $package->price - $configCut->value_int;

                $detailCut = TransactionDetail::where('header_id', $header->id)->where('is_cut', 1)->first();
                $detailCut->qty = $package->partner_count;
                $detailCut->price = $configCut->value_int;
                $detailCut->subtotal = $configCut->value_int * $package->partner_count;
                $detailCut->save();
            }
            else{
                $tmpCurrItem = TransactionDetail::where('item_id', $currPackage->item_id)
                    ->where('header_id', $header->id)->first();
                if($tmpCurrItem != null){
                    $tmpCurrItem->delete();
                }
                $tmpItem = Item::where('id', $package->item_id)->first();
                if($tmpItem != null){
                    $nDt = new TransactionDetail();
                    $nDt->header_id = $header->id;
                    $nDt->qty = 1;
                    $nDt->price = $tmpItem->price;
                    $nDt->subtotal = $tmpItem->price;
                    $nDt->status_id = 1;
                    $nDt->item_id = $tmpItem->id;
                    $nDt->is_cut = 0;
                    $nDt->save();

                    $whConfig = Configuration::where('branch_id', $userData->branch_id)->where('key', 'selling_warehouse_id')->first();
                    if($whConfig != null){
                        Utilities::updateStock($userData->branch_id, 'min',1, $tmpItem->id, $whConfig->value_int, $header->code,'Transaksi ' . $header->code);
                    }
                }

                $detail->price = $package->price;
                $detail->subtotal = $package->price;
            }
            $detail->package_id = $package->id;

            //discounting if happy hour
            if($header->is_happy_hour){
                $discount = $detail->price * 0.1;
                $detail->discount_percentage = 10;
                $detail->discount_amount = $detail->price - $discount;
            }
            else if($detail->member_id != 0){
                $memberModel = DB::table('members')
                    ->where('id', '=', $detail->member_id)
                    ->first();

                if($memberModel != null){
                    // Get membership benefits and check each trx details for their type
                    $membershipBenefits = DB::table('membership_benefits')
                        ->where('membership_id', '=', $memberModel->membership_id)
                        ->get();
                    foreach ($membershipBenefits as $membershipBenefit){
                        $tmpTrxDetails = TransactionDetail::where('header_id', '=', $header->id)
                            ->get();
                        foreach ($tmpTrxDetails as $tmpTrxDetail){
                            $isMembershipBenefitApplied = false;
                            if($membershipBenefit->type === 'treatment' && !empty($tmpTrxDetail->package_id)){
                                $isMembershipBenefitApplied = true;
                            }
                            elseif($membershipBenefit->type === 'item' && !empty($tmpTrxDetail->item_id)){
                                $tmpItem = DB::table('items')
                                    ->where('id', '=', $tmpTrxDetail->item_id);
                                if(!empty($membershipBenefit->item_category_id)){
                                    $tmpItem = $tmpItem->where('category_id', '=', $membershipBenefit->item_category_id);
                                }

                                $tmpItem = $tmpItem->first();
                                if(!empty($tmpItem)){
                                    $isMembershipBenefitApplied = true;
                                }
                            }
                            elseif($membershipBenefit->type === 'facility' && $tmpTrxDetail->is_facility_only === 1){
                                $isMembershipBenefitApplied = true;
                            }

                            if($isMembershipBenefitApplied){
                                $membershipDisc = 0;
                                if($membershipBenefit->free_amount > 0){
                                    if($tmpTrxDetail->is_facility_only == 1){
                                        $tmpTrxDetail->discount_amount = $tmpTrxDetail->subtotal;
                                        $tmpTrxDetail->discount_percentage = 100;
                                        $tmpTrxDetail->member_id = $memberModel->id;
                                        $tmpTrxDetail->save();
                                    }
                                    // Unhandled case
                                }
                                else{
                                    if($membershipBenefit->discount_percentage > 0){
                                        $membershipDisc = floatval($tmpTrxDetail->price) * floatval($membershipBenefit->discount_percentage / 100);
                                    }
                                    else{
                                        $membershipDisc = floatval($membershipBenefit->discount_flat) > 0 ? floatval($membershipBenefit->discount_flat) : 0;
                                    }
                                }

                                if($membershipDisc > 0){
                                    $tmpTrxDetail->discount_amount = $membershipDisc;
                                    $tmpTrxDetail->subtotal -= $membershipDisc;
                                    $tmpTrxDetail->member_id = $memberModel->id;
                                    $tmpTrxDetail->save();
                                }
                            }
                        }
                    }
                }
            }

            $detail->save();

            return Response::json('Sukses mengubah Paket!', 200);
        }
        catch (\Exception $ex){
            Log::error($ex);
            return Response::json($ex, 500);
        }
    }

    public function checkOut(Request $request){
        try{
            if(!$request->filled('locker_id')){
                return Response::json('Locker harus diinput!', 400);
            }

            $locker = Locker::where('id', $request->input('locker_id'))->first();
            if($locker == null){
                return Response::json('Loker tidak ditemukan!', 404);
            }

            //locker found
            //check active transactions
            $header = TransactionHeader::where('locker_id', $locker->id)
                ->where('status_id', 8)
                ->first();

            if($header == null){
                return Response::json('Loker belum memiliki transaksi!', 400);
            }

            $detail = TransactionDetail::where('header_id', $header->id)
                ->where('package_id', '!=', null)
                ->where('status_id', 8)
                ->first();

            //check on the details
            $room = Room::where('id', $detail->room_id)->first();
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

            //check on partners
            $partners = TransactionDetailPartner::where('transaction_detail_id', $detail->id)->get();
            foreach ($partners as $dbPartner){
                $partner = Partner::where('id', $dbPartner->partner_id)->first();
                if($partner != null){
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
                    $dbPartner->is_checkout = 1;
                    $dbPartner->save();
                }
            }
            $detail->end_time = Carbon::now('Asia/Jakarta');
            $detail->status_id = 9;
            $detail->save();

            return Response::json('Sukses Checkout Locker dari ruangan!', 200);
        }
        catch (\Exception $ex){
            Log::error($ex);
            return Response::json($ex, 500);
        }
    }

    public function switchRoom(Request $request){
        try{
            $user = Auth::guard('admin')->user();
            $userData = AdminUser::find($user->id);

            $locker = Locker::where('id', $request->input('locker_id'))->first();
            if($locker == null){
                return Response::json('Locker not found!', 404);
            }

            $header = TransactionHeader::where('locker_id', $locker->id)->where('status_id', 8)->first();
            if($header == null){
                return Response::json('Locker tidak memiliki Transaksi Berjalan!', 400);
            }

            $detail = TransactionDetail::where('header_id', $header->id)->where('status_id', 8)->first();
            if($detail == null){
                return Response::json('Locker tidak memiliki Treatment Berjalan!', 400);
            }

            $room = Room::where('id', $request->input('room_id'))->first();
            if($room == null){
                return Response::json('Room tidak ditemukan!', 404);
            }

            if($room->status_id == 6){
                return Response::json('Room sedang dipakai!', 400);
            }
            else if($room->status_id == 12){
                return Response::json('Room sedang dibersihkan!', 400);
            }

            $currRoom = Room::where('id', $detail->room_id)->first();
            $detail->room_id = $room->id;
            $detail->updated_at = Carbon::now('Asia/Jakarta');
            $detail->save();

            $currRoom->status_id = 4;
            $currRoom->save();
            $room->status_id = 6;
            $room->save();

            return Response::json('Sukses mengubah Room!', 200);
        }
        catch (\Exception $ex){
            Log::error($ex);
            return Response::json($ex, 500);
        }
    }
}
