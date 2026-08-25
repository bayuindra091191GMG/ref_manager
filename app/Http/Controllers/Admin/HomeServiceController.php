<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendBookingCancelledNotification;
use App\Libs\CommissionUtilities;
use App\Libs\JournalUtilities;
use App\Libs\Utilities;
use App\Models\BookingDetail;
use App\Models\BookingDetailPartner;
use App\Models\BookingHeader;
use App\Models\Configuration;
use App\Models\Item;
use App\Models\Partner;
use App\Models\TransactionDetailPartner;
use App\Models\TransactionHeader;
use App\Models\UserReview;
use App\Notifications\FCMNotification;
use App\Transformer\HomeServiceTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\DataTables;

class HomeServiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request){
        try {
            $admin = Auth::guard('admin')->user();

            $filterStartDate = Carbon::today()->subMonths(1)->format('d-m-Y');
            $filterEndDate = Carbon::today()->format('d-m-Y');

            if($request->start_date != null && $request->end_date != null){
                $startDateDecoded = rawurldecode($request->start_date);
                $endDateDecoded = rawurldecode($request->end_date);
                $start = Carbon::createFromFormat('!d-m-Y', $startDateDecoded, 'Asia/Jakarta');
                $end = Carbon::createFromFormat('!d-m-Y', $endDateDecoded, 'Asia/Jakarta');

                if($end->greaterThanOrEqualTo($start)){
                    $filterStartDate = $startDateDecoded;
                    $filterEndDate = $endDateDecoded;
                }
            }

            $filterUserId = -1;
            $user = null;

            // Filter user
            if($request->user_id != null){
                $filterUserId = intval($request->user_id);
                if($filterUserId > 0){
                    $user = DB::table('users')
                        ->where('id', '=', $filterUserId)
                        ->first();
                }
            }

            $data = [
                'filterStartDate' => $filterStartDate,
                'filterEndDate' => $filterEndDate,
                'user' => $user
            ];

            return view('admin.booking.index_home_service')->with($data);
        }
        catch (\Exception $ex){
            Log::channel('booking')->error('Admin/HomeServiceController - index '. $ex);
            return 'Terjadi kesalahan pada sistem!';
        }
    }

    public function getIndex(Request $request){
        // Filter date
        $start = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
        $end = Carbon::createFromFormat('!d-m-Y', $request->input('end_date'), 'Asia/Jakarta');
        $end->addDays(1);

        $admin = Auth::guard('admin')->user();

        $bookings = DB::table('booking_headers')
            ->join('statuses', 'booking_headers.status_id', '=', 'statuses.id')
            ->select('booking_headers.id as booking_id',
                'booking_headers.user_name as user_name',
                'booking_headers.user_phone as user_phone',
                'booking_headers.code as code',
                'booking_headers.customer_distance as customer_distance',
                'booking_headers.booking_time as booking_time',
                'booking_headers.created_at as created_at',
                'statuses.display as status_display')
            ->where('booking_headers.branch_id', '=', $admin->branch_id)
            ->where('booking_headers.type', '=', 'home_service')
            ->whereBetween('booking_headers.created_at', array($start->toDateTimeString(), $end->toDateTimeString()));


        if($request->filled('filter_user_id')){
            $filterUserId = intval($request->input('filter_user_id'));
            if($filterUserId > 0){
                $bookings = $bookings->where('booking_headers.user_id', '=', $filterUserId);
            }
        }

        return DataTables::of($bookings)
            ->setTransformer(new HomeServiceTransformer)
            ->make(true);
    }

    public function show($id){
        try {
            $booking = BookingHeader::with(['status', 'booking_details'])
                ->find($id);

            $trxCode = null;
            if(!empty($booking->transaction_id)){
                $trxCode = DB::table('transaction_headers')
                    ->where('id', '=', $booking->transaction_id)
                    ->value('code');
            }

            $packageDetails = DB::table('booking_details')
                ->leftJoin('booking_detail_partners', 'booking_detail_partners.detail_id', '=', 'booking_details.id')
                ->join('statuses', 'booking_details.status_id', '=', 'statuses.id')
                ->select('booking_details.id as id',
                    'booking_details.package_name as package_name',
                    'booking_details.item_name as item_name',
                    'booking_details.start_time as start_time',
                    'statuses.display as status_display',
                    'booking_detail_partners.partner_id as partner_id',
                    'booking_detail_partners.partner_alias as partner_alias',
                    'booking_detail_partners.partner_number as partner_number')
                ->where('booking_details.header_id', '=', $id)
                ->whereNotNull('booking_details.package_id')
                ->get();

            $tsItems = DB::table('items')
                ->where('branch_id', '=', $booking->branch_id)
                ->where('name', 'LIKE', '%TS%')
                ->orderBy('name')
                ->get();

            $tmps = $booking->booking_details->whereNotNull('ts_notes');
            $isTs = count($tmps) > 0;

            $tsItemArr = [];
            foreach($tsItems as $tsItem){
                $tsItemArr[] = $tsItem->id;
            }

            $addonDetails = null;
            $addonPartnerOptions = [];
            $addonDetailArr = [];

            if(!empty($booking->transaction_id)){
                $addonDetails = DB::table('transaction_details')
                    ->where('transaction_details.header_id', '=', $booking->transaction_id)
                    ->whereNotNull('transaction_details.item_id')
                    ->whereNotIn('transaction_details.item_id', $tsItemArr)
                    ->get();

                $addonPartnerOptions = [];
                foreach($packageDetails as $packageDetail){
                    if(!empty($packageDetail->partner_id)){
                        if(in_array($packageDetail->partner_id, $addonPartnerOptions)) continue;
                        $addonPartnerOptions[] = [
                            'partner_id' => $packageDetail->partner_id,
                            'partner_alias' => $packageDetail->partner_alias
                        ];
                    }
                }

                $addonDetailArr = [];
                foreach($addonDetails as $addon){
                    $item = Item::find($addon->item_id);

                    $partnerId = 0;
                    $partnerAlias = '';
                    if(!empty($addon->partner_id)){
                        $partner = Partner::find($addon->partner_id);
                        $partnerId = $partner->id;
                        $partnerAlias = $partner->alias;
                    }

                    $addonDetailArr[] = [
                        'trx_detail_id' => $addon->id,
                        'item_id' => $addon->item_id,
                        'item_name' => $item->name,
                        'partner_id' => $partnerId,
                        'partner_alias' => $partnerAlias,
                    ];
                }
            }

            $isAllowSetAddonCommission = false;
            if(!empty($booking->transaction_id)){
                $isAllowSetAddonCommission = !DB::table('commission_partner_histories')
                    ->where('transaction_date', '>', '2024-06-01 00:00:00')
                    ->where('transaction_id', '=', $booking->transaction_id)
                    ->exists();
            }

            $data = [
                'booking' => $booking,
                'packageDetails' => $packageDetails,
                'trxCode' => $trxCode,
                'tsItems' => $tsItems,
                'isTs' => $isTs,
                'addonDetailArr' => $addonDetailArr,
                'addonPartnerOptions' => $addonPartnerOptions,
                'isAllowSetAddonCommission' => $isAllowSetAddonCommission
            ];

            return view('admin.booking.show_home_service')->with($data);
        }
        catch (\Exception $ex){
            Log::channel('booking')->error('Admin/BookingController - show '. $ex);
            return 'Terjadi kesalahan pada sistem!';
        }
    }

    public function assign(Request $request){
        try {
            if(!$request->filled('assign_partner') ||
                !$request->filled('booking_detail_id')){
                    return response()->json(collect([
                        'code' => 400,
                        'error' => 'Terjadi kesalahan input!'
                    ]), 200);
            }

            $partnerId = $request->input('assign_partner');
            $partner = DB::table('partners')
                ->where('id', '=', $partnerId)
                ->first();

            $bookingStartTime = Carbon::createFromFormat('d-m-Y H:i', $request->input('assign_time'));
            if($bookingStartTime->lessThanOrEqualTo(Carbon::now('Asia/Jakarta'))){
                Session::flash('error', 'Terjadi kesalahan input waktu booking!');
                return redirect()->back();
            }

            $bookingDetail = BookingDetail::find($request->input('booking_detail_id'));

            $bookingHeader = BookingHeader::find($bookingDetail->header_id);

            // Set start time
            $bookingDetail->start_time = $bookingStartTime->toDateTimeString();
            $bookingDetail->status_id = 1;
            $bookingDetail->save();

            // Set the partner
            $newDetailPartner = new BookingDetailPartner();
            $newDetailPartner->detail_id = $bookingDetail->id;
            $newDetailPartner->partner_id = $partner->id;
            $newDetailPartner->partner_name = $partner->name;
            $newDetailPartner->partner_number = $partner->number;
            $newDetailPartner->partner_code = $partner->code;
            $newDetailPartner->partner_alias = $partner->alias;
            $newDetailPartner->status_id = 1;
            $newDetailPartner->save();

            // Update the start time
            DB::table('transaction_details')
                ->where('header_id', '=', $bookingHeader->transaction_id)
                ->where('package_id', '=', $bookingDetail->package_id)
                ->update([
                    'start_time' => $bookingStartTime->toDateTimeString()
                ]);

            // Update partner status
            // DB::table('partners')
            //     ->where('id', '=', $partnerId)
            //     ->update([
            //         'status_id' => 6,
            //         'updated_at' => Carbon::now('Asia/Jakarta')->toDateTimeString()
            //     ]);

            // Validate completed assigned partner
            if(!DB::table('booking_details')
                ->where('header_id', '=', $bookingDetail->header_id)
                ->whereNotNull('package_id')
                ->where('status_id', '!=', 1)
                ->exists()){
                $bookingHeader->status_id = 15;
                $bookingHeader->save();

                // Update related transaction
                DB::table('transaction_headers')
                    ->where('id', '=', $bookingHeader->transaction_id)
                    ->update([
                    'status_id' => 9
                ]);
            }

            $dataModel = collect([
                'id'        => $bookingHeader->id,
                'order_no'  => $bookingHeader->code,
                'type'      => $bookingHeader->type,
            ]);

            FCMNotification::setNotification('User',
                $bookingHeader->user_id,
                'Konfirmasi Home Service',
                'Home Service '. $bookingHeader->code. ' OURA',
                'Yay! Therapis sedang menuju tempat Anda!',
                100,
                $dataModel);

            return response()->json(collect([
                'response' => [
                    'partner_data' => $partner->alias. '('. $partner->number. ')'
                ],
                'code' => 200,
                'error' => 'none'
            ]), 200);
        }
        catch (\Exception $ex){
            error_log($ex);
            Log::channel('booking')->error('Admin/BookingController - assign '. $ex);
            return response()->json(collect([
                'code' => 500,
                'error' => $ex->getMessage()
            ]), 500);
        }
    }

    public function cancel(Request $request){
        try {
            if(!$request->filled('cancelled_booking_id')){
                Session::flash('error', 'Terjadi kesalahan input!');
                return redirect()->back();
            }

            $bookingHeader = BookingHeader::find($request->input('cancelled_booking_id'));
            if(empty($bookingHeader)){
                Session::flash('error', 'Home Service tidak ditemukan!');
                return redirect()->back();
            }

            // Validate status
            if($bookingHeader->status_id === 1){
                Session::flash('error', 'Home Service tidak bisa dibatalkan!');
                return redirect()->back();
            }

            // Cancel booking
            $bookingHeader->status_id = 10;
            $bookingHeader->canceled_at = Carbon::now('Asia/Jakarta')->toDateTimeString();
            $bookingHeader->save();

            // Cancel transaction if exists
            if(!empty($bookingHeader->transaction_id)){
                DB::table('transaction_headers')
                    ->where('id', '=', $bookingHeader->transaction_id)
                    ->update([
                        'status_id' => 16
                    ]);
            }

            if($bookingHeader->point_used > 0){
                // Refund user point
                Utilities::updateUserPointBalance($bookingHeader->user_id,
                        'add',
                        $bookingHeader->point_used);
                Utilities::saveUserPointHistory($bookingHeader->user_id,
                    $bookingHeader->branch_id,
                    'transaction',
                    'add',
                    $bookingHeader->point_used,
                    '',
                    $bookingHeader->code,
                    'REFUND');
            }

            SendBookingCancelledNotification::dispatch($bookingHeader, 'admin');

            Session::flash('success', 'Pembatalan booking sukses!');
            return redirect()->route('admin.booking.show', ['id' => $bookingHeader->id]);
        }
        catch (\Exception $ex){
            Log::channel('booking')->error('Admin/BookingController - show '. $ex);
            Session::flash('error', 'Maaf terjadi kesalahan sistem!' . $ex);
            return redirect()->back()->withInput($request->all());
        }
    }

    public function finish(Request $request){
        try{
            if(!$request->filled('finished_booking_id')){
                Session::flash('error', 'Terjadi kesalahan input!');
                return redirect()->back();
            }

            $bookingHeader = BookingHeader::find($request->input('finished_booking_id'));
            if(empty($bookingHeader)){
                Session::flash('error', 'Home Service tidak ditemukan!');
                return redirect()->back();
            }

            // Validate status
            if($bookingHeader->status_id !== 15){
                Session::flash('error', 'Status harus Dalam Proses!');
                return redirect()->back();
            }

            // Update status
            $bookingHeader->status_id = 9;
            $bookingHeader->home_service_finished_at = Carbon::now('Asia/Jakarta')->toDateTimeString();
            $bookingHeader->save();

            $bookingDetails = DB::table('booking_details')
                ->where('header_id', '=', $bookingHeader->id)
                ->whereNotNull('package_id')
                ->get();

            foreach($bookingDetails as $bookingDetail){
                $detailPartner = DB::table('booking_detail_partners')
                    ->where('detail_id', '=', $bookingDetail->id)
                    ->first();

                if(!empty($detailPartner)){
                    $trxDetails = DB::table('transaction_details')
                        ->where('header_id', '=', $bookingHeader->transaction_id)
                        ->where('package_id', '=', $bookingDetail->package_id)
                        ->get();

                    if(!empty($trxDetails)){
                        $isGenerated = false;
                        foreach($trxDetails as $trxDetail){
                            if($isGenerated) continue;
                            if(!DB::table('transaction_detail_partners')
                                ->where('transaction_detail_id', '=', $trxDetail->id)
                                ->exists()){

                                $newTrxDetailPartner = new TransactionDetailPartner();
                                $newTrxDetailPartner->transaction_detail_id = $trxDetail->id;
                                $newTrxDetailPartner->partner_id = $detailPartner->partner_id;
                                $newTrxDetailPartner->is_main = 1;
                                $newTrxDetailPartner->save();

                                $isGenerated = true;
                            }
                        }
                    }
                }
            }

            // Check for app flag
            $updatedBookingHeader = BookingHeader::where('id', '=', $bookingHeader->id)
                ->first();

            $details = BookingDetail::where('header_id', '=', $bookingHeader->id)
                ->whereNotNull('package_id')
                ->get();

            foreach ($details as $detail){
                if(!empty($detail->package_id)){
                    $newPendingReview = new UserReview();
                    $newPendingReview->branch_id = $updatedBookingHeader->branch_id;
                    $newPendingReview->booking_id = $updatedBookingHeader->id;
                    $newPendingReview->booking_detail_id = $detail->id;
                    $newPendingReview->user_id = $updatedBookingHeader->user_id;
                    $newPendingReview->user_name = $updatedBookingHeader->user_name;
                    $newPendingReview->user_phone = $updatedBookingHeader->user_phone;
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
            CommissionUtilities::generatePartnerCommission($updatedBookingHeader->transaction_id);
            CommissionUtilities::generateItemCommission($updatedBookingHeader->transaction_id);

            // Auto journal
            $autoJournalConfig = Configuration::where('key', 'auto_journal')
                ->where('branch_id', $updatedBookingHeader->branch_id)
                ->first();

            if($autoJournalConfig->value_int == 1){
                $updatedTrxHeader = TransactionHeader::find($updatedBookingHeader->transaction_id);

                $updatedTrxHeader->is_approved = 1;
                $updatedTrxHeader->approved_at = Carbon::now('Asia/Jakarta');
                $updatedTrxHeader->approved_by = 1;
                $updatedTrxHeader->save();

                $admin = Auth::guard('admin')->user();
                JournalUtilities::approveProcess($updatedTrxHeader, $admin);
            }

            Session::flash('success', 'Home Service sukses diselesaikan!');
            return redirect()->route('admin.home_service.show', ['id' => $bookingHeader->id]);
        }
        catch (\Exception $ex){
            Log::channel('booking')->error('Admin/BookingController - finish '. $ex);
            Session::flash('error', 'Maaf terjadi kesalahan sistem!' . $ex);
            return redirect()->back()->withInput($request->all());
        }
    }
}
