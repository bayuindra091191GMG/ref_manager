<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\Configuration;
use App\Models\Item;
use App\Models\Locker;
use App\Models\Package;
use App\Models\Partner;
use App\Models\PreweddingDetail;
use App\Models\Room;
use App\Models\TempBooking;
use App\Models\TransactionDetail;
use App\Models\TransactionDetailPartner;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class MonitorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function screen(){
        $user = Auth::guard('admin')->user();
        $userData = AdminUser::where('id', $user->id)->first();
        if($userData == null){
            return back();
        }
        $rooms = Room::where('branch_id', $userData->branch_id)->where('status_id', '!=', 2);

        if($user->branch_id > 1){
            $rooms = $rooms->orderBy('number');
        }

        $rooms = $rooms->get();

        $partners = Partner::where('branch_id', $userData->branch_id)
//            ->where('is_present', 1)
            ->whereIn('type', ['gold', 'silver'])
            ->orderBy('number')
            ->get();

        $reflexyPartners = Partner::where('branch_id', $userData->branch_id)
//            ->where('is_present', 1)
            ->where('type', 'refleksi')
            ->get();

        $tmpRooms = [];
        foreach ($rooms as $room){
            $nRoom = [
                'element_id'        => 'room'.$room->number,
                'id'                => $room->id,
                'name'              => $room->name,
                'type'              => $room->type,
                'number'            => $room->number,
                'ext_no'            => $room->number,
                'partners'          => [],
                'partner_display'   => '',
                'max_partners'      => 0,
                'locker_id'         => 0,
                'locker_number'     => '',
                'timer'             => 0,
                'trx_status'        => 0,
                'status_id'         => $room->status_id,
                'package_type'      => 'normal',
                'class'             => 'custom-square-pos green',
            ];

            //check on transaction
            $nRoom['class'] = match ($room->status_id) {
                11 => 'custom-square-pos yellow',
                4 => 'custom-square-pos pink',
                5 => 'custom-square-pos green',
                6 => 'custom-square-pos red',
            };

            if($room->status_id == 11 || $room->status_id == 6){
                $trx = DB::table('transaction_details')
                    ->join('transaction_headers', 'transaction_details.header_id', '=', 'transaction_headers.id')
                    ->select('transaction_details.id',
                        'transaction_details.package_id',
                        'transaction_details.status_id',
                        'transaction_details.room_id',
                        'transaction_details.start_time',
                        'transaction_details.is_extended',
                        'transaction_details.extension_amount',
                        'transaction_details.is_called',
                        'transaction_details.extension_times',
                        'transaction_details.additional_person_amount',
                        'transaction_details.additional_person_qty',
                        'transaction_details.additional_partner_amount',
                        'transaction_details.additional_partner_qty',
                        'transaction_headers.locker_id as locker_id')
                    ->where('transaction_headers.status_id', 8)
                    ->where('transaction_details.status_id', 8)
                    ->where('room_id', $room->id)
                    ->first();
                //dd($trx);

//                $trx = TransactionDetail::with('transaction_header')
//                    ->where('transaction_headers.status_id', 8)
//                    ->where('room_id', $room->id)->first();

                if($trx != null){
                    $nRoom['trx_status'] = $trx->status_id;
                    //get data
                    $locker = Locker::where('id', $trx->locker_id)->first();
                    if($locker != null){
                        $nRoom['locker_id'] = $locker->id;
                        $nRoom['locker_number'] = $locker->number;
                    }

                    if($trx->is_called != 0){
                        $nRoom['is_called'] = $trx->is_called;
                    }

                    $package = Package::where('id', $trx->package_id)->first();
                    $nRoom['max_partners'] = $package->partner_count;
                    if($package->room_type_id == 3){
                        $dtPartners = TransactionDetailPartner::where('transaction_detail_id', $trx->id)->get();
                        if(count($dtPartners) > 0){
                            foreach ($dtPartners as $tmpPartner){
                                $partner = Partner::where('id', $tmpPartner->partner_id)->first();
                                if($partner != null){
                                    $roomPartner = [
                                        'partner_id'        => $partner->id,
                                        'partner_number'    => $partner->number,
                                    ];
                                    $nRoom['partners'][] = $roomPartner;
                                    $nRoom['partner_display'] .= $partner->number .',';
                                }
                            }
                        }
                    }
                    else{
                        $dtPartners = TransactionDetailPartner::where('transaction_detail_id', $trx->id)->orderByDesc('id')->first();
                        if($dtPartners != null){
                            $partner = Partner::where('id', $dtPartners->partner_id)->first();
                            if($partner != null){
                                $roomPartner = [
                                    'partner_id'        => $partner->id,
                                    'partner_number'    => $partner->number,
                                ];
                                $nRoom['partners'][] = $roomPartner;
                                $nRoom['partner_display'] .= $partner->number .',';
                            }
                        }
                    }

                    if($room->status_id == 6){
                        $tmpDate = Carbon::parse($trx->start_time);
                        $now = Carbon::now('Asia/Jakarta');
                        $timeConsumed = $now->diffInMinutes($tmpDate);

                        $tmpTime = 0;
                        if($trx->is_extended == 1){
                            $tmpTime = $trx->extension_times * $package->extend_duration;
                            //dd($trx->extension_times, $package->extend_duration, $tmpTime);
                        }

                        //dd($now, $tmpDate, $timeConsumed);
                        if($timeConsumed > 0){
                            if($tmpTime > 0){
                                $nRoom['timer'] = $package->duration + $tmpTime - $timeConsumed;
                            }
                            else{
                                if($package->duration - $timeConsumed < 0){
                                    $nRoom['timer'] = 0;
                                }
                                else{
                                    $nRoom['timer'] = $package->duration - $timeConsumed;
                                }
                            }
                        }
                        else{
                            $nRoom['timer'] = $package->duration;
                        }

                        if($package->room_type_id == 3){
                            //athena type
                            $nRoom['package_type'] = 'athena';
                        }

                        if($nRoom['timer'] <= 10 && $trx->is_called != 1){
                            $nRoom['class'] = 'custom-square-pos blink-bg';
                        }
                    }
                }
            }

            $tmpRooms[] = $nRoom;
        }

        $tmpPartners = [];
        $tmpAbsents = [];
        foreach ($partners as $partner){
            $nPartner = [
                'element_id'    => 'partner'.$partner->number,
                'id'            => $partner->id,
                'name'          => $partner->alias,
                'type'          => $partner->type,
                'chosen'        => false,
                'number'        => $partner->number,
                'status_id'     => $partner->status_id,
                'status'        => '-',
                'class'         => 'custom-square-pos green',
                'book_name'     => $partner->book_name
            ];

            $nPartner['class'] = match ($partner->status_id) {
                11 => 'custom-square-pos yellow',
                12 => 'custom-square-pos grey',
                5 => 'custom-square-pos green',
                6 => 'custom-square-pos red',
            };

            $nPartner['status'] = match ($partner->status_id) {
                11 => 'Dibooking',
                12 => 'Istirahat',
                5 => 'Available',
                6 => 'Sedang Bekerja',
            };

            if($partner->status_id == 6){
                // $tmpPartnerBooking = TempBooking::where('branch_id', $userData->branch_id)
                //     ->where('partner_id', $partner->id)
                //     ->where('status_id', 8)
                //     ->first();

                $tmpPartnerBooking = DB::table('temp_bookings')
                    ->where('id', '>', 210410)
                    ->where('branch_id', $userData->branch_id)
                    ->where('partner_id', $partner->id)
                    ->where('status_id', 8)
                    ->first();

                if($tmpPartnerBooking != null){
                    $nPartner['class'] = 'custom-square-pos work-book-bg';
                    $nPartner['status'] = 'Double Booking';
                    $nPartner['is_double_book'] = true;
                }
            }

            if($partner->is_present == 1){
                $tmpPartners[] = $nPartner;
            } else {
                $tmpAbsents[] = $nPartner;
            }
        }

        foreach ($reflexyPartners as $partner){
            $nPartner = [
                'element_id'    => 'partner'.$partner->number,
                'id'            => $partner->id,
                'name'          => $partner->alias,
                'type'          => $partner->type,
                'chosen'        => false,
                'number'        => $partner->code,
                'status_id'     => $partner->status_id,
                'status'        => '-',
                'class'         => 'custom-square-pos green',
                'book_name'     => $partner->book_name
            ];

            $nPartner['class'] = match ($partner->status_id) {
                11 => 'custom-square-pos yellow',
                12 => 'custom-square-pos grey',
                5 => 'custom-square-pos green',
                6 => 'custom-square-pos red',
            };

            $nPartner['status'] = match ($partner->status_id) {
                11 => 'Dibooking',
                12 => 'Istirahat',
                5 => 'Available',
                6 => 'Sedang Bekerja',
            };

            if($partner->status_id == 6){
                // $tmpPartnerBooking = TempBooking::where('branch_id', $userData->branch_id)
                //     ->where('partner_id', $partner->id)
                //     ->where('status_id', 8)
                //     ->first();

                $tmpPartnerBooking = DB::table('temp_bookings')
                    ->where('id', '>', 210410)
                    ->where('branch_id', $userData->branch_id)
                    ->where('partner_id', $partner->id)
                    ->where('status_id', 8)
                    ->first();

                if($tmpPartnerBooking != null){
                    $nPartner['class'] = 'custom-square-pos work-book-bg';
                    $nPartner['status'] = 'Double Booking';
                    $nPartner['is_double_book'] = true;
                }
            }

            if($partner->is_present == 1){
                $tmpPartners[] = $nPartner;
            } else {
                $tmpAbsents[] = $nPartner;
            }
        }

        $dbBookings = TempBooking::where('branch_id', $userData->branch_id)
            ->where('status_id', 8)
            ->get();
        $tmpBookings = [];
        foreach ($dbBookings as $booking){
            $nBooking = [
                'room_id'       => $booking->room_id,
                'partner_id'    => $booking->partner_id,
                'status_id'     => $booking->status_id
            ];

            $tmpBookings[] = $nBooking;
        }

        $data = [
            'jsonRooms'     => $tmpRooms,
            'jsonPartners'  => $tmpPartners,
            'jsonAbsents'   => $tmpAbsents,
            'jsonBookings'  => $tmpBookings,
        ];

        if($userData->branch_id != 1){
            $dbAnnouncement = Configuration::where('key', 'announcement')->where('branch_id', $user->branch_id)
                ->first();
            $announcement = 'Belum ada Pengumuman!';
            if($dbAnnouncement != null){
                if($dbAnnouncement->value != null && $dbAnnouncement->value != ''){
                    $announcement = $dbAnnouncement->value;
                }
            }

            $data = [
                'jsonRooms'     => $tmpRooms,
                'jsonPartners'  => $tmpPartners,
                'jsonAbsents'   => $tmpAbsents,
                'jsonBookings'  => $tmpBookings,
                'announcement'  => $announcement,
            ];

            return view('admin.monitor.screen_not_emp')->with($data);
        }
        else {
            return view('admin.monitor.screen')->with($data);
        }
    }

    public function index()
    {
        $user     = Auth::guard('admin')->user();
        $userData = DB::table('admin_users')->where('id', $user->id)->first();
        if ($userData == null) {
            return back();
        }

        $startDate    = Carbon::today('Asia/Jakarta')->subDays(7)->toDateTimeString();
        $configBuffer = Configuration::select('value_int')->where('key', 'buffer_end_time')->first();

        $rooms = DB::table('rooms')
            ->where('branch_id', $userData->branch_id)
            ->where('status_id', '!=', 2)
            ->orderBy('number')
            ->get();

        $partners = DB::table('partners')
            ->where('branch_id', $userData->branch_id)
            ->where('employement_status', 'Aktif')
//            ->where('is_present', 1)
            ->orderBy('alias', 'asc')
            ->get();

        $now = Carbon::now();

        //dd($partners);

        $tmpRooms = [];
        foreach ($rooms as $room) {
            $nRoom = [
                'element_id'      => 'room' . $room->number,
                'id'              => $room->id,
                'name'            => $room->name,
                'type'            => $room->type,
                'number'          => $room->number,
                'ext_no'          => $room->number,
                'partner'         => null,
                'partner_display' => '',
                'therapists'      => '',
                'max_partners'    => 0,
                'member_name'     => '',
                'member_phone'    => '',
                'timer'           => 0,
                'trx_status'      => 0,
                'status_id'       => $room->status_id,
                'class'           => 'custom-square-pos green',
            ];

            //check on prewedding
            $pwDt = PreweddingDetail::where('room_id', $room->id)
                ->where('status_id', 9)
                ->whereRaw('? BETWEEN start_time AND end_time', [$now->toDateTimeString()])
                ->orderBy('end_time')
                ->get();

            if(count($pwDt) > 0){
                $nRoom['class'] = 'custom-square-pos red';
                $nRoom['trx_status'] = 9;

                $nRoom['member_phone']   = $pwDt[0]->header->member_phone;
                $nRoom['member_name'] = $pwDt[0]->header->member_phone;

                $packages = Package::whereIn('id', array_column($pwDt->toArray(), 'package_id'))->get();
                $items = Item::whereIn('id', array_column($pwDt->toArray(), 'item_id'))->get();
                if(count($packages) > 0 || count($items) > 0){
                    $nRoom['package_id']   = implode(', ', array_merge(array_unique(array_column($packages->toArray(), 'id')), array_unique(array_column($items->toArray(), 'id'))));
                    $nRoom['package_name'] = implode(', ', array_merge(array_unique(array_column($packages->toArray(), 'name')), array_unique(array_column($items->toArray(), 'name'))));

                    if ($pwDt->first()->start_time != null) {
                        $startTime = Carbon::parse($pwDt->first()->start_time);
                        $endTime   = Carbon::parse($pwDt->first()->start_time);
                        $endTime->addMinutes($packages->sum('duration'))->addMinutes((int) $configBuffer->value_int);

                        $nRoom['time_display'] = $startTime->format('H:i') . ' - ' . $endTime->format('H:i');
                    }
                }
            }

            //check on transaction
            $trxDt = TransactionDetail::whereHas('transaction_header', function ($query) use ($room) {
                $query->where('room_id', $room->id);
            })
                ->where('status_id', 9)
                ->whereNotNull('package_id')
                ->whereRaw('? BETWEEN start_time AND end_time', [$now->toDateTimeString()])
                ->get();
            if (count($trxDt) > 0) {
                $nRoom['class'] = 'custom-square-pos red';
                $nRoom['trx_status'] = 9;

                $packages = Package::whereIn('id', array_column($trxDt->toArray(), 'package_id'))->get();
                if (! $packages->isEmpty()) {
                    $nRoom['package_id']   = implode(', ', array_column($packages->toArray(), 'id'));
                    $nRoom['package_name'] = implode(', ', array_column($packages->toArray(), 'name'));

                    $startTime = null;
                    $endTime = Carbon::now();

                    foreach ($trxDt as $dt) {
                        if ($dt->end_time != null) {
                            if($startTime == null) {
                                $startTime = Carbon::parse($dt->start_time);
                            }

                            $newEndTime = Carbon::parse($dt->end_time);
                            if($newEndTime->greaterThan($endTime)){
                                $endTime = $newEndTime->copy();
                            }
                        }
                    }

                    $nRoom['time_display'] = $startTime->format('H:i') . ' - ' . $endTime->format('H:i');
                }

                $arrPartner = [];
                foreach ($trxDt as $dt) {
                    if ($dt->transaction_detail_partner) {
                        $partner = $dt->transaction_detail_partner->partner;
                        $arrPartner[] = $partner->alias . ' (' . $partner->gender . ')';
                    }
                }

                $nRoom['therapists'] = implode(', ', $arrPartner);

                if ($room->status_id == 6) {
                    $tmpDate      = Carbon::parse($trxDt->first()->start_time, 'Asia/Jakarta');
                    $now          = Carbon::now('Asia/Jakarta');
                    $timeConsumed = abs($tmpDate->diffInMinutes($now));

//                            dd($tmpDate->format('Y-m-d H:i:s'), $now->format('Y-m-d H:i:s'), $timeConsumed);
                    $nRoom['timer'] = $packages->sum('duration') - (int) $timeConsumed;

//                            dd($nRoom['timer']);

                    if ($nRoom['timer'] <= 10) {
                        $nRoom['class'] = 'custom-square-pos blink-bg';
                    }
                }
            }

            $tmpRooms[] = $nRoom;
        }

        $tmpPartners = [];
        $tmpAbsents = [];
        foreach ($partners as $partner) {
            $nPartner = [
                'element_id' => 'partner' . $partner->number,
                'id'         => $partner->id,
                'alias'      => $partner->alias,
                'name'       => $partner->name,
                'gender'       => $partner->gender,
                'specialty'       => $partner->specialty,
                'img'       => $partner->photo_filename != null ? asset('storage/images/partners/'.$partner->photo_filename) : '',
                'type'       => $partner->type,
                'chosen'     => false,
                'number'     => $partner->number,
                'status_id'  => $partner->status_id,
                'status'     => '-',
                'class'      => 'custom-square-pos green',
                'book_name'  => $partner->book_name,
                'opened'     => false,
            ];

            $numLength = strlen((string) $partner->number);
            if ($numLength == 1) {
                $nPartner['number'] = '0' . $partner->number;
            }

            $nPartner['class'] = match ($partner->status_id) {
                11 => 'custom-square-pos yellow',
                12 => 'custom-square-pos grey',
                5 => 'custom-square-pos green',
                6 => 'custom-square-pos red',
            };

            if($partner->is_present == 0){
                $nPartner['class'] = 'custom-square-pos grey';
            }

            $nPartner['status'] = match ($partner->status_id) {
                11 => 'Dibooking',
                12 => 'Istirahat',
                5 => 'Available',
                6 => 'Sedang Bekerja',
            };

            if($partner->is_present == 1){
                $tmpPartners[] = $nPartner;
            } else {
                $tmpAbsents[] = $nPartner;
            }
        }

        $data = [
            'jsonRooms'    => $tmpRooms,
            'jsonPartners' => $tmpPartners,
            'jsonAbsents'   => $tmpAbsents,
        ];

        return view('admin.monitor.index')->with($data);
    }

    public function refreshData()
    {
        $user     = Auth::guard('admin')->user();
        $userData = DB::table('admin_users')->where('id', $user->id)->first();
        if ($userData == null) {
            return back();
        }

        $startDate    = Carbon::today('Asia/Jakarta')->subDays(7)->toDateTimeString();
        $configBuffer = Configuration::select('value_int')->where('key', 'buffer_end_time')->first();

        $rooms = DB::table('rooms')
            ->where('branch_id', $userData->branch_id)
            ->where('status_id', '!=', 2)
            ->orderBy('number')
            ->get();

        $partners = DB::table('partners')
            ->where('branch_id', $userData->branch_id)
//            ->where('is_present', 1)
            ->orderBy('alias', 'asc')
            ->get();

        //dd($partners);

        $now = Carbon::now();

        $tmpRooms = [];
        foreach ($rooms as $room) {
            $nRoom = [
                'element_id'      => 'room' . $room->number,
                'id'              => $room->id,
                'name'            => $room->name,
                'type'            => $room->type,
                'number'          => $room->number,
                'ext_no'          => $room->number,
                'partner'         => null,
                'partner_display' => '',
                'therapists'      => '',
                'max_partners'    => 0,
                'member_name'     => '',
                'member_phone'    => '',
                'timer'           => 0,
                'trx_status'      => 0,
                'status_id'       => $room->status_id,
                'class'           => 'custom-square-pos green',
            ];

            //check on prewedding
            $pwDt = PreweddingDetail::where('room_id', $room->id)
                ->where('status_id', 9)
                ->whereRaw('? BETWEEN start_time AND end_time', [$now->toDateTimeString()])
                ->orderBy('end_time')
                ->get();

            if(count($pwDt) > 0){
                $nRoom['class'] = 'custom-square-pos red';
                $nRoom['trx_status'] = 9;

                $nRoom['member_phone']   = $pwDt[0]->header->member_phone;
                $nRoom['member_name'] = $pwDt[0]->header->member_phone;

                $packages = Package::whereIn('id', array_column($pwDt->toArray(), 'package_id'))->get();
                $items = Item::whereIn('id', array_column($pwDt->toArray(), 'item_id'))->get();
                if(count($packages) > 0 || count($items) > 0){
                    $nRoom['package_id']   = implode(', ', array_merge(array_unique(array_column($packages->toArray(), 'id')), array_unique(array_column($items->toArray(), 'id'))));
                    $nRoom['package_name'] = implode(', ', array_merge(array_unique(array_column($packages->toArray(), 'name')), array_unique(array_column($items->toArray(), 'name'))));

                    if ($pwDt->first()->start_time != null) {
                        $startTime = Carbon::parse($pwDt->first()->start_time);
                        $endTime   = Carbon::parse($pwDt->first()->start_time);
                        $endTime->addMinutes($packages->sum('duration'))->addMinutes((int) $configBuffer->value_int);

                        $nRoom['time_display'] = $startTime->format('H:i') . ' - ' . $endTime->format('H:i');
                    }
                }
            }

            //check on transaction
            $trxDt = TransactionDetail::where('room_id', $room->id)
                ->where('status_id', 9)
                ->whereNotNull('package_id')
                ->whereRaw('? BETWEEN start_time AND end_time', [$now->toDateTimeString()])
                ->get();
            if (count($trxDt) > 0) {
                $nRoom['class'] = 'custom-square-pos red';
                $nRoom['trx_status'] = 9;

                $nRoom['member_phone']   = $trxDt[0]->transaction_header->member_phone;
                $nRoom['member_name'] = $trxDt[0]->transaction_header->member_phone;

                $packages = Package::whereIn('id', array_column($trxDt->toArray(), 'package_id'))->get();
                if (! $packages->isEmpty()) {
                    $nRoom['package_id']   = implode(', ', array_column($packages->toArray(), 'id'));
                    $nRoom['package_name'] = implode(', ', array_column($packages->toArray(), 'name'));

                    $startTime = null;
                    $endTime = Carbon::now();

                    foreach ($trxDt as $dt) {
                        if ($dt->end_time != null) {
                            if($startTime == null) {
                                $startTime = Carbon::parse($dt->start_time);
                            }

                            $newEndTime = Carbon::parse($dt->end_time);
                            if($newEndTime->greaterThan($endTime)){
                                $endTime = $newEndTime->copy();
                            }
                        }
                    }

                    $nRoom['time_display'] = $startTime->format('H:i') . ' - ' . $endTime->format('H:i');
                }
                $arrPartner = [];
                foreach ($trxDt as $dt) {
                    if ($dt->transaction_detail_partner) {
                        $partner = $dt->transaction_detail_partner->partner;
                        $arrPartner[] = $partner->alias . ' (' . $partner->gender . ')';
                    }
                }

                $nRoom['therapists'] = implode(', ', $arrPartner);

                if ($room->status_id == 6) {
                    $tmpDate      = Carbon::parse($trxDt->first()->start_time, 'Asia/Jakarta');
                    $now          = Carbon::now('Asia/Jakarta');
                    $timeConsumed = abs($tmpDate->diffInMinutes($now));

//                            dd($tmpDate->format('Y-m-d H:i:s'), $now->format('Y-m-d H:i:s'), $timeConsumed);
                    $nRoom['timer'] = $packages->sum('duration') - (int) $timeConsumed;

//                            dd($nRoom['timer']);

                    if ($nRoom['timer'] <= 10) {
                        $nRoom['class'] = 'custom-square-pos blink-bg';
                    }
                }
            }

            $tmpRooms[] = $nRoom;
        }

        $tmpPartners = [];
        $tmpAbsents = [];
        foreach ($partners as $partner) {
            $nPartner = [
                'element_id' => 'partner' . $partner->number,
                'id'         => $partner->id,
                'alias'      => $partner->alias,
                'name'       => $partner->name,
                'gender'       => $partner->gender,
                'specialty'       => $partner->specialty,
                'img'       => $partner->photo_filename != null ? asset('storage/images/partners/'.$partner->photo_filename) : '',
                'type'       => $partner->type,
                'chosen'     => false,
                'number'     => $partner->number,
                'status_id'  => $partner->status_id,
                'status'     => '-',
                'class'      => 'custom-square-pos green',
                'book_name'  => $partner->book_name,
                'opened'     => false,
            ];

            $numLength = strlen((string) $partner->number);
            if ($numLength == 1) {
                $nPartner['number'] = '0' . $partner->number;
            }

            $nPartner['class'] = match ($partner->status_id) {
                11 => 'custom-square-pos yellow',
                12 => 'custom-square-pos grey',
                5 => 'custom-square-pos green',
                6 => 'custom-square-pos red',
            };

            if($partner->is_present == 0){
                $nPartner['class'] = 'custom-square-pos grey';
            }

            $nPartner['status'] = match ($partner->status_id) {
                11 => 'Dibooking',
                12 => 'Istirahat',
                5 => 'Available',
                6 => 'Sedang Bekerja',
            };

            if($partner->is_present == 1){
                $tmpPartners[] = $nPartner;
            } else {
                $tmpAbsents[] = $nPartner;
            }
        }

        $data = [
            'jsonRooms'    => $tmpRooms,
            'jsonPartners' => $tmpPartners,
            'jsonAbsents'  => $tmpAbsents,
        ];

        return $data;
    }

    public function setPartnerAvailable(Request $request)
    {
        try {
            if (!$request->filled('partner_id')
            ) {
                return Response::json('Invalid Parameters!', 400);
            }

            $user = Auth::guard('admin')->user();
            $userData = AdminUser::where('id', $user->id)->first();

            $partner = Partner::find($request->input('partner_id'));
            if ($partner == null) {
                return Response::json('Terapis tidak ditemukan!', 400);
            }

            DB::beginTransaction();

            $partner->status_id = 5;
            $partner->updated_by = $userData->id;
            $partner->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Terapis berhasil di-set menjadi available!',
            ]);
        } catch (\Exception $exception) {
            DB::rollBack();

            Log::channel('monitor')->error('[Monitor - setPartnerAvailable] - '.$exception);
        }
    }

    public function setPartnerPresent(Request $request)
    {
        try {
            if (!$request->filled('partner_id')
            ) {
                return Response::json('Invalid Parameters!', 400);
            }

            $user = Auth::guard('admin')->user();
            $userData = AdminUser::where('id', $user->id)->first();

            $partner = Partner::find($request->input('partner_id'));
            if ($partner == null) {
                return Response::json('Terapis tidak ditemukan!', 400);
            }

            DB::beginTransaction();

            $partner->is_present = $request->input('present');
            if($request->input('present') == 1){
                $partner->status_id = 5;
            }
            $partner->updated_by = $userData->id;
            $partner->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Terapis berhasil dibuat ' . ($request->input('present') == 1 ? 'hadir' : 'tidak hadir'),
            ]);
        } catch (\Exception $exception) {
            DB::rollBack();

            Log::channel('monitor')->error('[Monitor - setPartnerAvailable] - '.$exception);
        }
    }

    public function setStatus(Request $request)
    {
        try {
            if (!$request->filled('partner_id')
            ) {
                return Response::json('Invalid Parameters!', 400);
            }

            $user = Auth::guard('admin')->user();
            $userData = AdminUser::where('id', $user->id)->first();

            $partner = Partner::find($request->input('partner_id'));
            if ($partner == null) {
                return Response::json('Terapis tidak ditemukan!', 400);
            }

            DB::beginTransaction();

            $partner->status_id = $request->input('status');
            $partner->updated_by = $userData->id;
            $partner->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mengubah status terapis',
            ]);
        } catch (\Exception $exception) {
            DB::rollBack();

            Log::channel('monitor')->error('[Monitor - setPartnerAvailable] - '.$exception);
        }
    }
}
