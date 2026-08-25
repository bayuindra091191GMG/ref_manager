<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\Configuration;
use App\Models\Employee;
use App\Models\Inventory;
use App\Models\InventoryHistory;
use App\Models\Item;
use App\Models\Locker;
use App\Models\Package;
use App\Models\Partner;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Status;
use App\Models\TransactionDetail;
use App\Models\TransactionDetailPartner;
use App\Models\TransactionHeader;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class CashierController extends Controller
{
    public function index(){
        $user = Auth::guard('admin')->id();
        $userData = AdminUser::where('id', $user)->first();

        $lockers = Locker::where('status_id', '!=', 2)
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

        $firstPayment = DB::table('pos_payment_types')
//            ->where('branch_id', '=', $userData->branch_id)
            ->first();

        $data = [
            'firstPaymentId' => $firstPayment->id,
            'jsonLockers'    => json_encode($lockerArr),
        ];

        return view('admin.cashier.index')->with($data);

//        if($userData->branch_id == 1){
//            return view('admin.cashier.index')->with($data);
//        }
//        else{
//            return view('admin.cashier.index_not_emp')->with($data);
//        }
    }

    public function confirmDeleteItem(Request $request){
        try{
            if(!$request->filled('dt_id')){
                return Response::json('Item Required!', 400);
            }

            if(!$request->filled('password')){
                return Response::json('Password Required!', 400);
            }

            //check password first
            $user = Auth::guard('admin')->user();
            $userData = AdminUser::where('id', $user->id)->first();
            $config = Configuration::where('key', 'cancel_password')->where('branch_id', $userData->branch_id)->first();
            if($config->value != $request->input('password')){
                return Response::json('Password Salah!', 400);
            }

            $dt = TransactionDetail::where('id', $request->input('dt_id'))->first();
            $header = TransactionHeader::where('id', $dt->header_id)->first();

            //Save log first
            Log::channel('pos')->info('User ' . $userData->username . ' Deleting Item/Package with data ' . $dt);
            if($dt->package_id != null){
                $partners = TransactionDetailPartner::where('transaction_detail_id', $dt->id)->get();
                foreach ($partners as $partner){
                    $dbPartner = Partner::find($partner->partner_id);
                    $dbPartner->status_id = 12;
                    $dbPartner->save();
                }
                TransactionDetailPartner::where('transaction_detail_id', $dt->id)->delete();
                $room = Room::where('id', $dt->room_id)->first();
                $room->status_id = 4;
                $room->save();
            }
            else if($dt->item_id != null){
                $configOil = Configuration::where('key', 'oil_id')->where('branch_id', $userData->branch_id)->first();
                if($dt->item_id != $configOil->value_int){
                    $configWh = Configuration::where('key', 'selling_warehouse_id')->where('branch_id', $userData->branch_id)->first();
                    $wh = Warehouse::where('branch_id', $userData->branch_id)->where('id', $configWh->value_int)->first();
                    if($wh != null){
                        $inv = Inventory::where('item_id', $dt->item_id)
                            ->where('warehouse_id', $wh->id)
                            ->where('branch_id', $userData->branch_id)
                            ->first();

                        if($inv != null){
                            $dbItem = Item::where('id', $dt->item_id)->first();
                            if($dbItem->is_unlimited_stock == 0){
                                $inv->qty += $dt->qty;
                                $inv->save();

                                //save inv history
                                $invHistory = new InventoryHistory();
                                $invHistory->reference = $header->code;
                                $invHistory->warehouse_id = $wh->id;
                                $invHistory->warehouse_name = $wh->name;
                                $invHistory->branch_id = $userData->branch_id;
                                $invHistory->created_by = $userData->id;
                                $invHistory->item_id = $dt->item_id;
                                $invHistory->item_code = $dbItem->code;
                                $invHistory->item_name = $dbItem->name;
                                $invHistory->description = 'Cancel Pembelian Item untuk ' . $header->code;
                                $invHistory->qty_sub = 0;
                                $invHistory->qty_add = $dt->qty;
                                $invHistory->qty_result = $inv->qty;
                                $invHistory->qty_total = $inv->qty;
                                $invHistory->save();
                            }
                        }
                    }
                }
            }
            $dt->delete();

            return Response::json('Hapus Item Berhasil!', 200);
        }
        catch (\Exception $ex){
            Log::channel('pos')->error($ex);
            return Response::json('Maaf terjadi kesalahan!', 500);
        }
    }

    public function trxPreview(){
        return view('admin.cashier.trx_preview');
    }

    public function checkOnPassword(Request $request){
        try{
            if(!$request->filled('password')){
                return Response::json('Password harus diisi!', 400);
            }
            $user = Auth::guard('admin')->user();
            $config = Configuration::where('key', 'cancel_password')->where('branch_id', $user->branch_id)->first();
            if($config == null){
                return Response::json('Password untuk branch ini belum disetup!', 404);
            }

            if($config->value != $request->input('password')){
                return Response::json('Password salah!', 400);
            }

            return Response::json('Password benar!', 200);
        }
        catch (\Exception $ex){
            Log::channel('pos')->error($ex);
            return Response::json('Maaf terjadi kesalahan!', 500);
        }
    }

    public function getCurrentTransaction(){
        try{
            $user = Auth::guard('admin')->user();
            $header = TransactionHeader::where('is_showing', 1)->where('branch_id', $user->branch_id)->first();
            $data = [
                'header'        => null,
                'locker'        => null,
                'partners'      => null,
                'gro'           => null,
                'details'       => null,
                'grandTotal'    => 0,
            ];
            if($header != null){

                $locker = Locker::where('id', $header->locker_id)->first();
                if($locker != null){
                    $data['locker'] = $locker;
                }
                $gro = Employee::where('id', $header->employee_id)->first();
                if($gro != null){
                    $data['gro'] = $gro;
                }
                $tmpDt = TransactionDetail::where('header_id', $header->id)->get();
                $details = [];
                $tmpPartner = '';
                foreach ($tmpDt as $dt){
                    $tmpDtPartner = TransactionDetailPartner::where('transaction_detail_id', $dt->id)->get();
                    //details
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
                                        $dbPartner = Partner::where('id', $tmp->partner_id)->first();
                                        if($dbPartner != null){
                                            if($tmpPartner == ''){
                                                $tmpPartner .= $dbPartner->number;
                                            }
                                            else{
                                                $tmpPartner .= '/' . $dbPartner->number;
                                            }
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
                $data['details'] = $details;
                $data['partners'] = $tmpPartner;
                $data['header'] = $header;
                $totalTrx = DB::table('transaction_details')->where('header_id', $header->id)->sum('subtotal');
                $data['grandTotal'] = number_format($totalTrx, '2', ',', '.');
                return Response::json($data, 200);
            }
            else{
                return Response::json('Tidak ada transaksi', 202);
            }
        }
        catch (\Exception $ex){
            Log::channel('pos')->error($ex);
            return Response::json('Maaf terjadi kesalahan!', 500);
        }
    }

    public function cancelPreview(Request $request){
        try{
            $header = TransactionHeader::where('locker_id', $request->query('locker_id'))
                ->where('is_showing', 1)
                ->first();
            Log::channel('pos')->info($request->query('locker_id'));

            if($header != null){
                Log::channel('pos')->info($header);
                $header->is_showing = 0;
                $header->save();
            }
            return Response::json('Sukses', 200);
        }
        catch (\Exception $ex){
            Log::channel('pos')->error($ex);
            return Response::json('Maaf terjadi kesalahan!', 500);
        }
    }
}
