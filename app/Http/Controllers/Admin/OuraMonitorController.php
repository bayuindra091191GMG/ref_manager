<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Libs\CommissionUtilities;
use App\Libs\JournalUtilities;
use App\Libs\Utilities;
use App\Models\AdminUser;
use App\Models\BookingDetail;
use App\Models\BookingDetailPartner;
use App\Models\BookingHeader;
use App\Models\Configuration;
use App\Models\Locker;
use App\Models\Package;
use App\Models\Partner;
use App\Models\Room;
use App\Models\TempBooking;
use App\Models\TransactionDetail;
use App\Models\TransactionDetailPartner;
use App\Models\TransactionHeader;
use App\Models\UserReview;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class OuraMonitorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(){
        try{
            $user = Auth::guard('admin')->user();
            $userData = AdminUser::where('id', $user->id)->first();
            if($userData == null){
                return back();
            }
            $rooms = Room::where('branch_id', $userData->branch_id);

            if($user->branch_id > 1){
                $rooms = $rooms->orderBy('number');
            }

            $rooms = $rooms->get();

            $partners = Partner::where('branch_id', $userData->branch_id)
                ->where('is_present', 1)
                ->orderBy('number')
                ->get();

            $tmpRooms = [];
            foreach ($rooms as $room){
                $nRoom = [
                    'element_id'        => 'room'.$room->number,
                    'id'                => $room->id,
                    'name'              => $room->name,
                    'type'              => $room->description,
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
                    'is_double_book'    => false,
                    'class'             => 'custom-square green',
                ];

                //check on transaction
                $nRoom['class'] = match ($room->status_id) {
                    11 => 'custom-square yellow',
                    4 => 'custom-square pink',
                    5 => 'custom-square green',
                    6 => 'custom-square red',
                };

                if($room->status_id == 11 || $room->status_id == 6){
                    $trx = DB::table('transaction_details')
                        ->join('transaction_headers', 'transaction_details.header_id', '=', 'transaction_headers.id')
                        ->select('transaction_details.id',
                            'transaction_details.header_id',
                            'transaction_details.package_id',
                            'transaction_details.status_id',
                            'transaction_details.room_id',
                            'transaction_details.start_time',
                            'transaction_details.is_extended',
                            'transaction_details.extension_amount',
                            'transaction_details.extension_times',
                            'transaction_details.is_called',
                            'transaction_details.extension_times',
                            'transaction_details.additional_person_amount',
                            'transaction_details.additional_person_qty',
                            'transaction_details.additional_partner_amount',
                            'transaction_details.additional_partner_qty',
                            'transaction_headers.locker_id as locker_id')
                        ->where('transaction_headers.status_id', 9)
                        ->where('transaction_details.status_id', 8)
                        ->where('room_id', $room->id)
                        ->first();

                    if($trx != null){
                        $nRoom['trx_status'] = $trx->status_id;
                        //get data
                        $locker = Locker::where('id', $trx->locker_id)->first();
                        if($locker != null && $trx->status_id == 8){
                            $nRoom['locker_id'] = $locker->id;
                            $nRoom['locker_number'] = $locker->number;
                        }

                        if($trx->is_called != 0){
                            $nRoom['is_called'] = $trx->is_called;
                        }

                        $package = Package::where('id', $trx->package_id)->first();
                        $addTime = 0;

                        if($package != null){
                            if($trx->start_time != null){
                                $startTime = Carbon::parse($trx->start_time);
                                $endTime = Carbon::parse($trx->start_time);
                                $endTime->addMinutes($package->duration);
                                //add on add more 30 min
                                $query = "select count(td.id) as total from transaction_details td
                                    inner join items it on it.id = td.item_id
                                    WHERE
                                    td.header_id = ". $trx->header_id ."
                                    and it.is_addon = 1
                                    and it.name not like '%oil therapy%';";
                                $dbData = DB::select($query);
                                $addTime = 0;
                                if($dbData[0]->total > 0){
                                    $addTime = 30 * $dbData[0]->total;
                                }
                                $endTime->addMinutes($addTime);
                                //check for extend
                                if($trx->extension_times > 0)
                                {
                                    //add extension times
                                    $extensionTime = $package->extend_duration * $trx->extension_times;
                                    $endTime->addMinutes($extensionTime);
                                }

                                $nRoom['time_display'] = $startTime->format('H:i') . ' - ' . $endTime->format('H:i');
                            }
                        }

                        $nRoom['max_partners'] = $package->partner_count;

                        //if($package->room_type_id == 3){
                        $dtPartners = TransactionDetailPartner::where('transaction_detail_id', $trx->id)
                            ->where('is_checkout', 0)
                            ->get();

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

                        if($room->status_id == 6){
                            $tmpDate = Carbon::parse($trx->start_time);
                            $now = Carbon::now('Asia/Jakarta');
                            $timeConsumed = $now->diffInMinutes($tmpDate);

                            $tmpTime = 0;
                            if($trx->is_extended == 1){
                                $tmpTime = $trx->extension_times * $package->extend_duration;
                                //dd($trx->extension_times, $package->extend_duration, $tmpTime);
                            }

                            $tmpDuration = $package->duration + $addTime + 15;

                            //dd($now, $tmpDate, $timeConsumed);
                            if($timeConsumed > 0){
                                if($tmpTime > 0){
                                    $nRoom['timer'] = $tmpDuration + $tmpTime - $timeConsumed;
                                }
                                else{
                                    if($tmpDuration - $timeConsumed < 0){
                                        $nRoom['timer'] = 0;
                                        if($userData->branch_id != 1){
                                            $nRoom['timer'] = $tmpDuration - $timeConsumed;
                                        }
                                    }
                                    else{
                                        $nRoom['timer'] = $tmpDuration - $timeConsumed;
                                    }
                                }
                            }
                            else{
                                $nRoom['timer'] = $tmpDuration;
                            }

                            if($package->room_type_id == 3){
                                //athena type
                                $nRoom['package_type'] = 'athena';
                            }

                            if($nRoom['timer'] <= 10 && $trx->is_called != 1){
                                $nRoom['class'] = 'custom-square blink-bg';
                            }
                        }
                    }

                    $booking = TempBooking::where('branch_id', $userData->branch_id)
                        ->where('room_id', $room->id)
                        ->where('status_id', 8)->first();

                    if($booking != null){
                        $partner = Partner::where('id', $booking->partner_id)->first();

                        if($partner != null){
                            $roomPartner = [
                                'partner_id'        => $partner->id,
                                'partner_number'    => $partner->number,
                                'partner_name'      => $partner->alias,
                            ];
//                        if($partner->number == 0){
//                            $roomPartner['partner_number'] = 'R3';
//                        }
                            $nRoom['partners'][] = $roomPartner;
                            $nRoom['partner_display'] .= $partner->number .',';
//                        if($room->id == 29){
//                            dd($nRoom);
//                        }
                        }
                    }
                }

                if($room->status_id == 6){
                    $tmpRoomBooking = TempBooking::where('branch_id', $userData->branch_id)
                        ->where('room_id', $room->id)
                        ->where('status_id', 8)
                        ->first();

                    if($tmpRoomBooking != null){
                        $nRoom['class'] = 'custom-square work-book-bg';
                        $nRoom['is_double_book'] = true;
                    }
                }

                $tmpRooms[] = $nRoom;
            }

            //dd($tmpRooms);

            $tmpPartners = [];
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
                    'class'         => 'custom-circle green',
                    'book_name'     => $partner->book_name
                ];

                $numLength = strlen((string)$partner->number);
                if($numLength == 1){
                    $nPartner['number'] = '0' . $partner->number;
                }

                $nPartner['class'] = match ($partner->status_id) {
                    11 => 'custom-circle yellow',
                    12 => 'custom-circle grey',
                    5 => 'custom-circle green',
                    6 => 'custom-circle red',
                };

                $nPartner['status'] = match ($partner->status_id) {
                    11 => 'Dibooking',
                    12 => 'Istirahat',
                    5 => 'Available',
                    6 => 'Sedang Bekerja',
                };

                if($partner->status_id == 6){
                    $tmpPartnerBooking = TempBooking::where('branch_id', $userData->branch_id)
                        ->where('partner_id', $partner->id)
                        ->where('status_id', 8)
                        ->first();

                    if($tmpPartnerBooking != null){
                        $nPartner['class'] = 'custom-circle work-book-bg';
                        $nPartner['status'] = 'Double Booking';
                        $nPartner['is_double_book'] = true;
                    }
                }

                $tmpPartners[] = $nPartner;
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

            $jsonRooms = json_encode($tmpRooms);
            $jsonPartners = json_encode($tmpPartners);
            $jsonBooking = json_encode($tmpBookings);

            $data = [
                'jsonRooms'     => $jsonRooms,
                'jsonPartners'  => $jsonPartners,
                'jsonBookings'  => $jsonBooking,
            ];

            if($userData->branch_id == 1){
                return view('admin.monitor.index')->with($data);
            }
            else{
                $dbAnnouncement = Configuration::where('key', 'announcement')->where('branch_id', $user->branch_id)
                    ->first();
                $announcement = 'Belum ada Pengumuman!';
                if($dbAnnouncement != null){
                    if($dbAnnouncement->value != null && $dbAnnouncement->value != ''){
                        $announcement = $dbAnnouncement->value;
                    }
                }

                $data = [
                    'jsonRooms'     => $jsonRooms,
                    'jsonPartners'  => $jsonPartners,
                    'jsonBookings'  => $jsonBooking,
                    'announcement'  => $announcement,
                ];
                //dd($announcement);
                return view('admin.monitor_oura.index')->with($data);
            }
        }
        catch (\Exception $ex){
            dd($ex);
            Log::channel('oura')->error($ex);
            return back();
        }
    }

    public function refreshData(){
        try{
            $user = Auth::guard('admin')->user();
            $userData = AdminUser::where('id', $user->id)->first();
            if($userData == null){
                return back();
            }
            $rooms = Room::where('branch_id', $userData->branch_id);
            if($user->branch_id > 1){
                $rooms = $rooms->orderBy('number');
            }

            $rooms = $rooms->get();

            $partners = Partner::where('branch_id', $userData->branch_id)
                ->where('is_present', 1)
                //->whereIn('type', ['gold', 'silver'])
                //->where('type', '!=', 'refleksi')
                ->orderBy('number')
                ->get();

            $tmpRooms = [];
            foreach ($rooms as $room){
                $nRoom = [
                    'element_id'        => 'room'.$room->number,
                    'id'                => $room->id,
                    'name'              => $room->name,
                    'type'              => $room->description,
                    'number'            => $room->number,
                    'ext_no'            => $room->number,
                    'partners'          => [],
                    'partner_display'   => '',
                    'max_partners'      => 0,
                    'locker_id'         => 0,
                    'locker_number'     => '',
                    'timer'             => 0,
                    'status_id'         => $room->status_id,
                    'trx_status'        => 0,
                    'package_type'      => 'normal',
                    'class'             => 'custom-square green',
                    'is_double_book'    => false,
                    'time_display'      => '',
                ];

                //check on transaction
                $nRoom['class'] = match ($room->status_id) {
                    11 => 'custom-square yellow',
                    4 => 'custom-square pink',
                    5 => 'custom-square green',
                    6 => 'custom-square red',
                };

                if($room->status_id == 11 || $room->status_id == 6){
                    $trx = DB::table('transaction_details')
                        ->join('transaction_headers', 'transaction_details.header_id', '=', 'transaction_headers.id')
                        ->select('transaction_details.id',
                            'transaction_details.header_id',
                            'transaction_details.package_id',
                            'transaction_details.status_id',
                            'transaction_details.room_id',
                            'transaction_details.start_time',
                            'transaction_details.is_extended',
                            'transaction_details.extension_amount',
                            'transaction_details.extension_times',
                            'transaction_details.is_called',
                            'transaction_details.extension_times',
                            'transaction_details.additional_person_amount',
                            'transaction_details.additional_person_qty',
                            'transaction_details.additional_partner_amount',
                            'transaction_details.additional_partner_qty',
                            'transaction_headers.locker_id as locker_id')
                        ->where('transaction_headers.status_id', 9)
                        ->where('transaction_details.status_id', 8)
                        ->where('room_id', $room->id)
                        ->first();

                    if($trx != null){
                        $nRoom['trx_status'] = $trx->status_id;
                        //get data
                        $locker = Locker::where('id', $trx->locker_id)->first();
                        if($locker != null && $trx->status_id == 8){
                            $nRoom['locker_id'] = $locker->id;
                            $nRoom['locker_number'] = $locker->number;
                        }

                        if($trx->is_called != 0){
                            $nRoom['is_called'] = $trx->is_called;
                        }

                        $package = Package::where('id', $trx->package_id)->first();
                        $addTime = 0;
                        if($package != null){
                            if($trx->start_time != null){
                                $startTime = Carbon::parse($trx->start_time);
                                $endTime = Carbon::parse($trx->start_time);
                                $endTime->addMinutes($package->duration);
                                //add on add more 30 min
                                $query = "select count(td.id) as total from transaction_details td
                                    inner join items it on it.id = td.item_id
                                    WHERE
                                    td.header_id = ". $trx->header_id ."
                                    and it.is_addon = 1
                                    and it.name not like '%oil therapy%';";
                                $dbData = DB::select($query);
                                if($dbData[0]->total > 0){
                                    $addTime = 30 * $dbData[0]->total;
                                }
                                $endTime->addMinutes($addTime);

                                //check for extend
                                if($trx->extension_times > 0)
                                {
                                    //add extension times
                                    $extensionTime = $package->extend_duration * $trx->extension_times;
                                    $endTime->addMinutes($extensionTime);
                                }
                                $nRoom['time_display'] = $startTime->format('H:i') . ' - ' . $endTime->format('H:i');
                            }
                        }
                        $nRoom['max_partners'] = $package->partner_count;

                        //if($package->room_type_id == 3){
                        $dtPartners = TransactionDetailPartner::where('transaction_detail_id', $trx->id)
                            ->where('is_checkout', 0)
                            ->get();

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

                        if($room->status_id == 6){
                            $tmpDate = Carbon::parse($trx->start_time);
                            $now = Carbon::now('Asia/Jakarta');
                            $timeConsumed = $now->diffInMinutes($tmpDate);

                            $tmpTime = 0;
                            if($trx->is_extended == 1){
                                $tmpTime = $trx->extension_times * $package->extend_duration;
                                //dd($trx->extension_times, $package->extend_duration, $tmpTime);
                            }

                            $tmpDuration = $package->duration + $addTime + 15;

                            //dd($now, $tmpDate, $timeConsumed);
                            if($timeConsumed > 0){
                                if($tmpTime > 0){
                                    $nRoom['timer'] = $tmpDuration + $tmpTime - $timeConsumed;
                                }
                                else{
                                    if($tmpDuration - $timeConsumed < 0){
                                        $nRoom['timer'] = 0;
                                        if($userData->branch_id != 1){
                                            $nRoom['timer'] = $tmpDuration - $timeConsumed;
                                        }
                                    }
                                    else{
                                        $nRoom['timer'] = $tmpDuration - $timeConsumed;
                                    }
                                }
                            }
                            else{
                                $nRoom['timer'] = $tmpDuration;
                            }

                            if($package->room_type_id == 3){
                                //athena type
                                $nRoom['package_type'] = 'athena';
                            }

                            if($nRoom['timer'] <= 10 && $trx->is_called != 1){
                                $nRoom['class'] = 'custom-square blink-bg';
                            }
                        }
                    }
                    $booking = TempBooking::where('branch_id', $userData->branch_id)
                        ->where('room_id', $room->id)
                        ->where('status_id', 8)->first();


                    if($booking != null){
                        $partner = Partner::where('id', $booking->partner_id)->first();
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
                    $tmpRoomBooking = TempBooking::where('branch_id', $userData->branch_id)
                        ->where('room_id', $room->id)
                        ->where('status_id', 8)
                        ->first();

                    if($tmpRoomBooking != null){
                        $nRoom['class'] = 'custom-square work-book-bg';
                        $nRoom['is_double_book'] = true;
                    }
                }

                $tmpRooms[] = $nRoom;
            }

            $tmpPartners = [];
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
                    'class'         => 'custom-circle green',
                    'is_double_book'=> false,
                    'book_name'     => $partner->book_name
                ];

                $numLength = strlen((string)$partner->number);
                if($numLength == 1){
                    $nPartner['number'] = '0' . $partner->number;
                }

                $nPartner['class'] = match ($partner->status_id) {
                    11 => 'custom-circle yellow',
                    12 => 'custom-circle grey',
                    5 => 'custom-circle green',
                    6 => 'custom-circle red',
                };

                $nPartner['status'] = match ($partner->status_id) {
                    11 => 'Dibooking',
                    12 => 'Istirahat',
                    5 => 'Available',
                    6 => 'Sedang Bekerja',
                };

                if($partner->status_id == 6){
                    $tmpPartnerBooking = TempBooking::where('branch_id', $userData->branch_id)
                        ->where('partner_id', $partner->id)
                        ->where('status_id', 8)
                        ->first();

                    if($tmpPartnerBooking != null){
                        $nPartner['class'] = 'custom-circle work-book-bg';
                        $nPartner['status'] = 'Double Booking';
                        $nPartner['is_double_book'] = true;
                    }
                }

                $tmpPartners[] = $nPartner;
            }

            $dbBookings = TempBooking::where('branch_id', $userData->branch_id)
                ->where('status_id', 8)->get();
            $tmpBookings = [];
            foreach ($dbBookings as $booking){
                $nBooking = [
                    'room_id'       => $booking->room_id,
                    'partner_id'    => $booking->partner_id,
                    'status_id'     => $booking->status_id
                ];

                $tmpBookings[] = $nBooking;
            }

            $jsonRooms = json_encode($tmpRooms);
            $jsonPartners = json_encode($tmpPartners);
            $jsonBooking = json_encode($tmpBookings);

            $data = [
                'jsonRooms'     => $jsonRooms,
                'jsonPartners'  => $jsonPartners,
                'jsonBookings'  => $jsonBooking,
                'rooms'         => $tmpRooms,
                'partners'      => $tmpPartners,
                'bookings'      => $tmpBookings,
            ];

            return Response::json($data, 200);
        }
        catch (\Exception $ex){
            Log::channel('oura')->error($ex);
            return Response::json($ex, 500);
        }
    }

    public function customerIndex(){
        try{
            //getting transaction based on current going transaction
            $user = Auth::guard('admin')->user();
            $today = Carbon::today('Asia/Jakarta');

            $trxHeaders = [];
            $headers = TransactionHeader:: where('created_at', '>=', $today)
                ->where('branch_id', $user->branch_id)
                ->where('is_app_transaction', 0)
                ->get();

            foreach($headers as $header){
                $trxHeaders[] = $header;
            }

            // Check app booking
            $startDate = Carbon::today()->subDays(20);
            $bookings = DB::table('booking_headers')
                ->where('branch_id', '=', $user->branch_id)
                ->where('status_id', '=', 15)
                ->whereNotNull('transaction_id')
                ->where('created_at', '>', $startDate->toDateTimeString())
                ->get();

            foreach($bookings as $booking){
                $header = TransactionHeader::find($booking->transaction_id);
                $trxHeaders[] = $header;
            }

            $customers = [];
            $idx = 1;
            foreach ($trxHeaders as $header){
                $details = TransactionDetail::where('header_id', $header->id)
                    ->where('status_id', 8)
                    ->where('package_id', '!=', null)
                    ->get();
                foreach ($details as $detail){
                    $package = Package::where('id', $detail->package_id)->first();
                    $nCust = [
                        'id'            => $header->id,
                        'detail_id'     => $detail->id,
                        'package_name'  => $package->name,
                        'customer_name' => $detail->customer_name,
                        'timer'         => 0,
                        'status_id'     => $detail->status_id,
                        'element_id'    => 'customer'.$idx,
                        'show_button'   => true,
                        'room_id'       => $detail->room_id,
                        'room_number'   => true,
                        'partner_name'  => '',
                        'key_id' => Utilities::generateRandomStr(15)
                    ];

                    $dbPartners = TransactionDetailPartner::where('transaction_detail_id', $detail->id)->first();
                    if($dbPartners != null){
                        $partner = Partner::where('id', $dbPartners->partner_id)->first();
                        $nCust['partner_name'] = $partner->alias;

                        $room = Room::where('id', $detail->room_id)->first();
                        $nCust['room_number'] = $room->name;
                    }
                    $idx++;

                    $tmpDate = Carbon::parse($detail->start_time);
                    $now = Carbon::now('Asia/Jakarta');
                    $timeConsumed = $now->diffInMinutes($tmpDate);

                    $query = "select count(td.id) as total from transaction_details td
                        inner join items it on it.id = td.item_id
                        WHERE
                        td.header_id = ". $detail->header_id ."
                        and it.is_addon = 1
                        and it.name not like '%oil therapy%';";

                    $dbData = DB::select($query);
                    $addTime = 0;

                    if($dbData[0]->total > 0){
                        $addTime = 30 * $dbData[0]->total;
                    }

                    $tmpDuration = $package->duration + $addTime + 15;
                    //dd($now, $tmpDate, $timeConsumed);
                    if($timeConsumed > 0){
                        $nCust['timer'] = $tmpDuration - $timeConsumed;
                    }
                    else{
                        $nCust['timer'] = $tmpDuration;
                    }

                    $customers[] = $nCust;
                }
            }

            $data = [
                'customers'     => $customers,
                'jsonCustomers' => json_encode($customers)
            ];

            return view('admin.monitor_oura.customers')->with($data);
        }
        catch (\Exception $ex){
            dd($ex);
            Log::channel('oura')->error($ex);
            return back();
        }
    }

    public function customerRefreshData(){
        try{
            //getting transaction based on current going transaction
            $user = Auth::guard('admin')->user();
            $today = Carbon::today('Asia/Jakarta');
            $trxHeaders = [];
            $headers = TransactionHeader::where('created_at', '>=', $today)
                ->where('branch_id', $user->branch_id)
                ->where('is_app_transaction', 0)
                ->get();

            foreach($headers as $header){
                $trxHeaders[] = $header;
            }

            // Check app booking
            $startDate = Carbon::today()->subDays(20);
            $bookings = DB::table('booking_headers')
                ->where('branch_id', '=', $user->branch_id)
                ->where('status_id', '=', 15)
                ->whereNotNull('transaction_id')
                ->where('created_at', '>', $startDate->toDateTimeString())
                ->get();

            foreach($bookings as $booking){
                $header = TransactionHeader::find($booking->transaction_id);
                $trxHeaders[] = $header;
            }

            $customers = [];
            $idx = 1;
            foreach ($trxHeaders as $header){
                $details = TransactionDetail::where('header_id', $header->id)
                    ->where('status_id', 8)->get();
                foreach ($details as $detail){
                    $package = Package::where('id', $detail->package_id)->first();
                    $nCust = [
                        'id'            => $header->id,
                        'detail_id'     => $detail->id,
                        'package_name'  => $package->name,
                        'customer_name' => $detail->customer_name,
                        'timer'         => 0,
                        'status_id'     => $detail->status_id,
                        'element_id'    => 'customer'.$idx,
                        'show_button'   => true,
                        'room_id'       => $detail->room_id,
                        'room_number'   => true,
                        'partner_name'  => '',
                        'key_id' => Utilities::generateRandomStr(15)
                    ];

                    $dbPartners = TransactionDetailPartner::where('transaction_detail_id', $detail->id)->first();
                    if($dbPartners != null){
                        $partner = Partner::where('id', $dbPartners->partner_id)->first();
                        $nCust['partner_name'] = $partner->alias;

                        $room = Room::where('id', $detail->room_id)->first();
                        $nCust['room_number'] = $room->name;
                    }
                    $idx++;

                    $tmpDate = Carbon::parse($detail->start_time);
                    $now = Carbon::now('Asia/Jakarta');
                    $timeConsumed = $now->diffInMinutes($tmpDate);
                    $query = "select count(td.id) as total from transaction_details td
                        inner join items it on it.id = td.item_id
                        WHERE
                        td.header_id = ". $detail->header_id ."
                        and it.is_addon = 1
                        and it.name not like '%oil therapy%';";

                    $dbData = DB::select($query);
                    $addTime = 0;

                    if($dbData[0]->total > 0){
                        $addTime = 30 * $dbData[0]->total;
                    }

                    $tmpDuration = $package->duration + $addTime + 15;
                    //dd($now, $tmpDate, $timeConsumed);
                    if($timeConsumed > 0){
                        $nCust['timer'] = $tmpDuration - $timeConsumed;
                    }
                    else{
                        $nCust['timer'] = $tmpDuration;
                    }

                    $customers[] = $nCust;
                }
            }

            return Response::json($customers, 200);
        }
        catch (\Exception $ex){
            Log::channel('oura')->error($ex);
            return Response::json($ex, 500);
        }
    }

    public function finish(Request $request){
        try{
            if(!$request->exists('id')){
                return Response::json('Id Transaksi dibutuhkan!', 400);
            }
            $header = TransactionHeader::where('id', $request->input('id'))->first();
            if($header == null){
                return Response::json('Transaksi tidak ditemukan!', 404);
            }

            $details = TransactionDetail::where('header_id', $header->id)
                ->where('status_id', 8)
                ->get();
            foreach ($details as $detail){
                $room = Room::where('id', $detail->room_id)->first();
                $room->status_id = 5;
                $room->save();

                //partners
                $dtPartners = TransactionDetailPartner::where('transaction_detail_id', $detail->id)->get();
                foreach ($dtPartners as $tmpPartner){
                    $dbPartner = Partner::where('id', $tmpPartner->partner_id)->first();
                    if($dbPartner != null){
                        $dbPartner->status_id = 5;
                        $dbPartner->save();
                    }
                }

                $detail->end_time = Carbon::now('Asia/Jakarta');
                $detail->status_id = 9;
                $detail->save();
            }

            // Check for app flag
            $bookingHeader = BookingHeader::where('transaction_id', '=', $header->id)
                ->first();

            if(!empty($bookingHeader)){
                $bookingHeader->status_id = 9;
                $bookingHeader->save();

                $details = BookingDetail::where('header_id', '=', $bookingHeader->id)
                    ->whereNotNull('package_id')
                    ->get();

                foreach ($details as $detail){
                    if(!empty($detail->package_id)){
                        $newPendingReview = new UserReview();
                        $newPendingReview->branch_id = $bookingHeader->branch_id;
                        $newPendingReview->booking_id = $bookingHeader->id;
                        $newPendingReview->booking_detail_id = $detail->id;
                        $newPendingReview->user_id = $bookingHeader->user_id;
                        $newPendingReview->user_name = $bookingHeader->user_name;
                        $newPendingReview->user_phone = $bookingHeader->user_phone;
                        $newPendingReview->package_id = $detail->package_id;
                        $newPendingReview->package_name = $detail->package_name;
                        $newPendingReview->is_image_uploaded = false;
                        $newPendingReview->star = 0;
                        $newPendingReview->status_id = 3;

                        $dtPartner = BookingDetailPartner::where('detail_id', $detail->id)->first();
                        $partner = DB::table('partners')
                            ->where('id', '=', $dtPartner->partner_id)
                            ->first();
                        $newPendingReview->partner_id = $dtPartner->partner_id;
                        $newPendingReview->partner_alias = $partner->alias;
                        $newPendingReview->partner_number = $partner->number;
                        $newPendingReview->partner_name = $partner->name;

                        $newPendingReview->save();
                    }
                }

                // Generate partner commission
                CommissionUtilities::generatePartnerCommission($bookingHeader->transaction_id);
                CommissionUtilities::generateItemCommission($bookingHeader->transaction_id);

                // Auto journal
                $autoJournalConfig = Configuration::where('key', 'auto_journal')
                    ->where('branch_id', $bookingHeader->branch_id)
                    ->first();
                if($autoJournalConfig->value_int == 1){
                    $updatedTrxHeader = TransactionHeader::find($bookingHeader->transaction_id);

                    $updatedTrxHeader->is_approved = 1;
                    $updatedTrxHeader->approved_at = Carbon::now('Asia/Jakarta');
                    $updatedTrxHeader->approved_by = 1;
                    $updatedTrxHeader->save();

                    $admin = Auth::guard('admin')->user();
                    JournalUtilities::approveProcess($updatedTrxHeader, $admin);
                }
            }

            return Response::json('Customer Selesai!', 200);
        }
        catch (\Exception $ex){
            Log::channel('oura')->error($ex);
            return Response::json($ex, 500);
        }
    }

    public function switchRoom(Request $request){
        try{
            $room = Room::where('id', $request->input('room_id'))->first();
            if($room == null){
                return Response::json('Room tidak ditemukan!', 404);
            }

            $header = TransactionHeader::where('id', $request->input('header_id'))->first();
            if($header == null){
                return Response::json('Transaksi tidak ditemukan!', 404);
            }

            $detail = TransactionDetail::where('id', $request->input('detail_id'))->where('status_id', 8)->first();
            if($detail == null){
                return Response::json('Transaksi berjalan tidak ditemukan!', 404);
            }

            $currRoom = Room::where('id', $detail->room_id)->first();
            $currRoom->status_id = 5;
            $currRoom->save();

            $room->status_id = 6;
            $room->save();
            $detail->room_id = $room->id;
            $detail->save();

            return Response::json('Sukses mengubah Room!', 200);
        }
        catch (\Exception $ex){
            Log::channel('oura')->error($ex);
            return Response::json($ex, 500);
        }
    }

    public function switchPartner(Request $request){
        try{
            $partner = Partner::where('id', $request->input('partner_id'))->first();
            if($partner == null){
                return Response::json('Terapis tidak ditemukan!', 404);
            }

            $header = TransactionHeader::where('id', $request->input('header_id'))->first();
            if($header == null){
                return Response::json('Transaksi tidak ditemukan!', 404);
            }

            $detail = TransactionDetail::where('id', $request->input('detail_id'))->where('status_id', 8)
                ->first();
            if($detail == null){
                return Response::json('Transaksi berjalan tidak ditemukan!', 404);
            }

            $detailPartner = TransactionDetailPartner::where('transaction_detail_id', $detail->id)
                ->first();
            if(empty($detailPartner)){
                return Response::json('Transaksi berjalan tidak ditemukan!', 404);
            }

            $currPartner = Partner::where('id', $detailPartner->partner_id)->first();
            $currPartner->status_id = 5;
            $currPartner->save();

            $partner->status_id = 6;
            $partner->save();

            $detailPartner->partner_id = $partner->id;
            $detailPartner->save();

            return Response::json('Sukses Ubah Terapis!', 200);
        }
        catch (\Exception $ex){
            error_log($ex);
            Log::channel('oura')->error($ex);
            return Response::json($ex, 500);
        }
    }
}
