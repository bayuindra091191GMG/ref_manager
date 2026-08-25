<?php

namespace App\Http\Controllers\Admin;
use App\Models\AdminUser;
use App\Models\Branch;
use App\Models\Configuration;
use App\Models\Employee;
use App\Models\FoodBookingDetail;
use App\Models\Item;
use App\Models\Locker;
use App\Models\Package;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Status;
use App\Models\TransactionDetail;
use App\Models\TransactionHeader;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class KitchenController extends \App\Http\Controllers\Controller
{
    public function index(){
        return view('admin.kitchen.index');
    }

    public function getData(Request $request){
        try{
            //get the data
            $model = [];
            $user = Auth::guard('admin')->user();
            $userData = AdminUser::find($user->id);
            $headers = TransactionHeader::where('status_id', 8)->where('branch_id', $userData->branch_id)->get();
            $idx = 1;
            foreach ($headers as $header){
                $details = TransactionDetail::where('header_id', $header->id)
                    ->where('is_fnb', 1)
                    ->where('status_id', 15)
                    ->get();

                if(count($details) > 0){
                    //get room data
                    $roomData = TransactionDetail::where('header_id', $header->id)->where('room_id', '!=', null)->first();
                    $roomNumber = 'Belum pilih Room!';
                    if($roomData != null){
                        $room = Room::where('id', $roomData->room_id)->first();
                        $roomNumber = $room->number;
                    }

                    $nModel = [
                        'id'            => $header->id,
                        'room_number'   => $roomNumber,
                        'details'       => [],
                        'status'        => 'Not Done',
                        'is_loading'    => false,
                        'display'       => true,
                        'item_id'       => 'item' . $idx,
                        'btn_id'        => 'btn' . $idx,
                    ];

                    foreach ($details as $detail){
                        $item = Item::where('id', $detail->item_id)->first();
                        if($item != null){
                            $nDetail = [
                                'dt_id'     => $detail->id,
                                'item_id'   => $item->id,
                                'item_name' => $item->name,
                                'qty'       => $detail->qty,
                            ];

                            $nModel['details'][] = $nDetail;
                        }
                    }
                    $model[] = $nModel;
                    $idx++;
                }
            }

            return Response::json($model, 200);
        }
        catch (\Exception $ex){
            return Response::json($ex, 500);
        }
    }

    public function finish(Request $request){
        try{
            $rules = array(
                'trx_id'    => 'required',
            );

            $message = array(
                'trx_id.required'       => 'Transaksi wajib dipilih!',
            );

            $validator = Validator::make($request->all(), $rules, $message);

            if($validator->fails()){
                return Response::json(['error' => $validator->errors()->all()]);
            }

            $header = TransactionHeader::where('id', $request->input('trx_id'))->first();
            if($header == null){
                return Response::json('Transaksi tidak ditemukan!', 404);
            }

            $details = TransactionDetail::where('header_id', $header->id)->where('is_fnb', 1)->get();
            if(count($details)){
                foreach ($details as $detail){
                    $detail->status_id = 9;
                    $detail->save();
                }
            }
            $locker = Locker::where('id', $header->locker_id)->first();
            $msg = 'Sukses Selesaikan Pesanan untuk Loker ' . $locker->number . '!';

            return Response::json($msg, 200);
        }
        catch (\Exception $ex){
            return Response::json($ex, 500);
        }
    }

    public function foods(){
        $user = Auth::guard('admin')->user();
        $userData = AdminUser::where('id', $user->id)->first();

        $lockers = Locker::where('branch_id', $userData->branch_id)
            ->where('status_id', '!=', 2)
            ->get();

        $lockerArr = [];
        foreach ($lockers as $locker){
            $nArr = [
                'id'                    => $locker->id,
                'code'                  => $locker->code,
                'number'                => $locker->number,
                'element_id'            => 'locker'.$locker->number,
                'status_id'             => $locker->status_id,
                'element_class'         => 'custom-square mx-auto green',
                'room_type_name'        => 0,
                'room_id'               => 0,
                'room_number'           => 0,
                'package_name'          => '',
                'package_id'            => 0,
                'duration'              => 0,
                'is_package_chosen'     => false,
                'is_room_chosen'        => false,
                'is_gro_chosen'         => false,
                'gro_id'                => 0,
                'gro_code'              => '',
                'gro_name'              => '',
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
                        $nArr['gro_name'] = $employee->name;
                    }
                }
                if(count($header->transaction_details) > 0){
                    $trxDt = TransactionDetail::where('header_id', $header->id)->where('package_id', '!=', null)
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
                            $nArr['room_type_name'] = $room->type;
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

        return view('admin.monitor_locker.foods')->with($data);
    }

    public function getLockerDetail(Request $request){
        try{
            if(!$request->filled('locker_id')){
                return Response::json('Locker Required!', 400);
            }

            $locker = Locker::where('id', $request->input('locker_id'))->first();
            if($locker == null){
                return Response::json('Locker tidak ditemukan!', 404);
            }

            //get foods data
            $foods = [];
            $header = TransactionHeader::where('locker_id', $request->input('locker_id'))
                ->where('status_id', 8)
                ->first();

            $details = TransactionDetail::where('header_id', $header->id)
                ->where('item_id', '!=', null)
                ->get();


            $user = Auth::guard('admin')->user();
            $userData = AdminUser::where('id', $user->id)->first();

            $config = Configuration::where('key', 'oil_id')
                ->where('branch_id', $userData->branch_id)
                ->first();

            foreach ($details as $detail){
                if($detail->item_id != $config->value_int){
                    $dbItem = Item::where('id', $detail->item_id)->first();
                    $nFood = [
                        'id'        => $detail->id,
                        'name'      => $dbItem->name,
                        'img_path'  => $dbItem->image_path ?? asset('images/nasi_goreng.jpg'),
                        'qty'       => $detail->qty,
                        'status'    => '',
                    ];

                    $status = Status::where('id', $detail->status_id)->first();
                    if($status != null){
                        $nFood['status'] = $status->display;
                    }

                    $foods[] = $nFood;
                }
            }

            $data = [
                'foods'     => $foods,
                'header_id' => $header->id,
            ];

            return Response::json($data, 200);
        }
        catch (\Exception $ex){
            return Response::json($ex, 500);
        }
    }

    public function receipt(Request $request){
        try{
            $user = Auth::guard('admin')->user();
            $userData = AdminUser::where('id', $user->id)->first();
            $branch = Branch::where('id', $userData->branch_id)->first();
            $locker = Locker::where('id', $request->query('locker_id'))->first();
            if($locker == null){
                Session::flash('error', 'Locker tidak ditemukan!');
                return Redirect::route('admin.kitchen.foods');
            }

            $header = TransactionHeader::with(['locker', 'employee'])->where('locker_id', $locker->id)
                ->where('status_id', 8)->first();
            if($header == null){
                Session::flash('error', 'Locker belum memesan makanan!');
                return Redirect::route('admin.kitchen.foods');
            }

            $details = TransactionDetail::with('item')
                ->where('item_id', '!=', null)
                ->where('header_id', '=', $header->id)
                ->where('is_fnb', '=', 1)
                ->where('is_new_order', '=', 1)
                ->get();

            $date = Carbon::parse($header->created_at);

            $nci = [];
            foreach ($details as $dt){
                $tmpDb = FoodBookingDetail::where('transaction_details_id', $dt->id)->orderByDesc('created_at')->first();

                if($tmpDb != null){
                    $nci[] = $tmpDb;
                }
            }

            $data = [
                'branch'    => $branch,
                'locker_no' => $locker->number,
                'details'   => $details,
                'nci'       => $nci,
                'date'      => $date->locale('id_ID')->toDateTimeString()
            ];

            //dd($data);

            $dbDetails = TransactionDetail::with('item')
                ->where('item_id', '!=', null)
                ->where('header_id', '=', $header->id)
                ->where('is_fnb', '=', 1)
                ->where('is_new_order', '=', 1)
                ->get();

            foreach ($dbDetails as $dt){
                $dt->is_new_order = 0;
                $dt->save();
            }

            return view('admin.monitor_locker.food_receipt')->with($data);
        }
        catch (\Exception $ex){
            Session::flash('error', $ex);
            Log::error($ex);
            return Redirect::route('admin.kitchen.foods');
        }
    }
}
