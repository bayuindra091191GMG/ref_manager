<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendBookingCancelledNotification;
use App\Jobs\SendBookingPaymentApprovedNotification;
use App\Libs\AutoNumberGenerator;
use App\Libs\JournalUtilities;
use App\Libs\Utilities;
use App\Models\AdminUser;
use App\Models\BookingDetail;
use App\Models\BookingDetailPartner;
use App\Models\BookingHeader;
use App\Models\Branch;
use App\Models\Configuration;
use App\Models\Item;
use App\Models\MonitoringLog;
use App\Models\Package;
use App\Models\Partner;
use App\Models\TransactionDetail;
use App\Models\TransactionDetailPartner;
use App\Models\TransactionHeader;
use App\Models\TransactionInvoice;
use App\Models\TransactionPayment;
use App\Models\User;
use App\Models\UserVoucherCode;
use App\Notifications\FCMNotification;
use App\Transformer\BookingTransformer;
use Carbon\Carbon;
use Carbon\CarbonConverterInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;

class BookingController extends Controller
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
            if($request->get('filter_user')){
                $filterUserId = intval($request->get('filter_user'));
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

            return view('admin.booking.index')->with($data);
        }
        catch (\Exception $ex){
            Log::channel('booking')->error('Admin/BookingController - index '. $ex);
            return 'Terjadi kesalahan pada sistem!';
        }
    }

    public function getIndex(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $bookings = BookingHeader::select('booking_headers.*', 'statuses.display as status')
            ->leftJoin('statuses', 'booking_headers.status_id', '=', 'statuses.id')
            ->where('branch_id', $admin->branch_id)
            ->where('status_id', '!=', 2);

        if($request->filled('filter_user')){
            $filterUserId = intval($request->input('filter_user'));
            if($filterUserId > 0){
                $bookings = $bookings->where('booking_headers.user_id', '=', $filterUserId);
            }
        };

        if($request->get('date')){
            $date = explode(' - ', $request->get('date'));

            if(count($date) > 1){
                $bookings->whereDate('booking_headers.booking_date', '>=', $date[0])
                    ->whereDate('booking_headers.booking_date', '<=', $date[1]);
            }
            else {
                $bookings->whereDate('booking_headers.booking_date', $date[0]);
            }
        }

        return DataTables::of($bookings)
            ->addColumn('action', function ($row) {
                $buttons = '';
                $buttons .= '<a href="' . route('admin.booking.show', ['id' => $row->id]) . '" class="btn btn-info me-1"><i class="far fa fa-eye"></i> Show</a>';
                if($row->status_id != 9 && $row->status_id != 10){
                    $buttons .= '<a href="' . route('admin.booking.edit', ['id' => $row->id]) . '" class="btn btn-primary me-1"><i class="far fa fa-edit"></i> Edit</a>';
                }

                return $buttons;
            })
            ->make();
    }

//    public function getIndex(Request $request){
//        // Filter date
//        $start = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
//        $end = Carbon::createFromFormat('!d-m-Y', $request->input('end_date'), 'Asia/Jakarta');
//        $end->addDays(1);
//
//        $admin = Auth::guard('admin')->user();
//
//        $bookings = DB::table('booking_headers')
//            ->join('booking_details', 'booking_headers.id', '=', 'booking_details.header_id')
//            ->join('statuses', 'booking_headers.status_id', '=', 'statuses.id')
//            ->select('booking_headers.id as booking_id',
//                'booking_headers.user_name as user_name',
//                'booking_headers.user_phone as user_phone',
//                'booking_headers.code as code',
//                'booking_headers.booking_time as booking_time',
//                'booking_details.package_name as package_name',
//                'booking_headers.voucher_code as voucher_code',
//                'booking_headers.total_payment as total_payment',
//                'booking_headers.pg_service as pg_service',
//                'booking_headers.created_at as created_at',
//                'booking_details.start_time as start_time',
//                'statuses.display as status_display')
//            ->where('booking_headers.branch_id', '=', $admin->branch_id)
//            ->where('booking_headers.type', '=', 'book')
//            ->whereBetween('booking_headers.created_at', array($start->toDateTimeString(), $end->toDateTimeString()))
//            ->whereNotNull('booking_details.package_id');
//
//
//        if($request->filled('filter_user_id')){
//            $filterUserId = intval($request->input('filter_user_id'));
//            if($filterUserId > 0){
//                $bookings = $bookings->where('booking_headers.user_id', '=', $filterUserId);
//            }
//        }
//
//        return DataTables::of($bookings)
//            ->setTransformer(new BookingTransformer)
//            ->make(true);
//    }



    public function create()
    {
        $user = Auth::guard('admin')->user();

        $branches = Branch::where('status_id', '>', 0)->get();
        $users    = AdminUser::where('branch_id', $user->branch_id)->get();

        return view('admin.booking.create', compact('branches', 'users'));
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'booking_date' => 'required',
            'booking_time' => 'required',
            'user_name'    => 'required',
            'user_phone'    => 'required',
        ], [
            'booking_date.required' => 'Tanggal booking harus dipilih!',
            'booking_time.required' => 'Jam booking harus dipilih!',
            'user_name.required'    => 'Nama customer harus diisi!',
            'user_phone.required'    => 'Nomor Telp Customer harus diisi!',
        ]);

        if ($validator->fails()) {
            Session::flash('error', $validator->errors()->first());
            return back()->withInput($request->except('_token'));
        }

        DB::beginTransaction();

        $packages = $request->input('packages') ?? [];
        $items = $request->input('items') ?? [];

        if(count($packages) == 0){
            DB::rollBack();

            Session::flash('error', 'Detail booking wajib diisi!');
            return back()->withInput($request->except('_token'));
        }

        try {
            $phone = $request->input('phone_code') . $request->input('user_phone');

            $member = User::where('phone', $phone)->first();
            if(!$member){
                User::create([
                    'name' => $request->input('user_name'),
                    'phone' => $phone,
                    'gender' => $request->input('gender'),
                    'country' => $request->input('country'),
                    'password' => Hash::make('123456'),
                ]);
            }

            $file = $request->file('payment_proof');
            $filename = null;
            if($file){
                $filename = 'Proof_' . Carbon::now()->format('Y-m-d_H-i-s') . '.' . $file->getClientOriginalExtension();
                $file->storeAs('', $filename, 'proofs');
            }

            $dateCarbon = Carbon::createFromFormat('d-m-Y', $request->input('booking_date'));
            $today      = Carbon::today('Asia/Jakarta');
            $pattern    = 'BOOK/' . $today->month;
            $autonumber = AutoNumberGenerator::generateBookAutoNumber($pattern);

            $header = BookingHeader::create([
                'type'                 => 'booking',
                'branch_id'            => Auth::user()->branch_id,
                'branch_code'          => Auth::user()->branch->code,
                'branch_name'          => Auth::user()->branch->name,
                'user_id'              => $member != null ? $member->id : null,
                'user_phone'           => $phone,
                'user_name'            => $request->input('user_name'),
                'user_email'           => $member != null ? $member->email : null,
                'notes'                => $request->input('notes'),
                'code'                 => $autonumber,
                'grand_total'          => $request->input('grand_total'),
                'total_payment_before' => $request->input('grand_total'),
                'total_payment'        => $request->input('grand_total'),
                'is_settled'           => 1,
                'status_id'            => 3,
                'created_by'           => Auth::user()->id,
                'booking_time'         => $request->input('booking_time'),
                'booking_date'         => $dateCarbon,
//                'duration'             => $request->input('duration'),
                'payment_proof'        => $filename
            ]);

            $startTime = Carbon::createFromFormat('d-m-Y H:i', $request->input('booking_date') . ' ' . $request->input('booking_time'));
            $endTime          = $startTime->copy()->addMinutes((int)$request->input('duration'));
            $bookingTimeRange = $startTime->format('H:i') . ' - ' . $endTime->format('H:i');

            foreach ($packages as $detail){
                $package = Package::find($detail['id']);
                BookingDetail::create([
                    'type' => 'pos',
                    'header_id'    => $header->id,
                    'package_id'   => $package->id,
                    'package_code' => $package->code,
                    'package_name' => $package->name,
                    'qty'          => 1,
                    'price'        => $detail['price'],
                    'subtotal'     => $detail['price'],
                    'booking_time' => $bookingTimeRange,
                    'status_id'    => 3,
                ]);
            }

            foreach ($items as $detail){
                $item = Item::find($detail['id']);
                BookingDetail::create([
                    'header_id'    => $header->id,
                    'item_id'   => $item->id,
                    'item_code' => $item->code,
                    'item_name' => $item->name,
                    'qty'          => $detail['qty'],
                    'price'        => $detail['price'],
                    'subtotal'     => $detail['price'] * $detail['qty'],
                    'booking_time' => $bookingTimeRange,
                    'status_id'    => 3,
                ]);
            }

            DB::commit();

            Session::flash('success', 'Berhasil booking jadwal treatment!');
            return redirect()->route('admin.booking.index');
        } catch (\Exception $exception){
            DB::rollBack();
            Log::channel('booking')->error('[BookingController - store] - ' . $exception);

            Session::flash('error', 'Terjadi kesalahan pada sistem!');
            return back()->withInput($request->except('_token'));
        }
    }

    public function show($id){
        try {
            $booking = BookingHeader::with(['status', 'booking_details'])
                ->find($id);

            $trxCode = null;
            $invoice = null;
            if(!empty($booking->transaction_id)){
                $trxCode = DB::table('transaction_headers')
                    ->where('id', '=', $booking->transaction_id)
                    ->value('code');
                $invoice = TransactionInvoice::where('header_ids', $booking->transaction_id)->first();
            }

            $details = [];
            foreach ($booking->booking_details as $detail){
                $details[] = [
                    'id' => $detail->package_id != null ? $detail->package_id : $detail->item_id,
                    'type' => $detail->package_id != null ? 'package' : 'item',
                    'name' => $detail->package_id != null ? $detail->package_name : $detail->item_name,
                    'qty' => $detail->qty,
                    'price' => $detail->price,
                    'partner_id' => null,
                    'need_partner' => $detail->item_id != null && $detail->item->category_id == 27,
                    'room_id' => null,
                    'duration' => $detail->package_id != null ? $detail->package->duration : $detail->item->duration,
                ];

                if($detail->package_id != null ){
                    if($detail->package->item_id != null){
                        $details[] = [
                            'id' => $detail->package->item_id,
                            'type' => 'item',
                            'name' => $detail->package->item->name,
                            'qty' => 1,
                            'price' => 0,
                            'partner_id' => null,
                        ];
                    }
                }
            }

            $data = [
                'booking' => $booking,
                'trxCode' => $trxCode,
                'details' => $details,
                'invoice' => $invoice != null ? $invoice->id : 0,
            ];

//            dd($data);

            return view('admin.booking.show')->with($data);
        }
        catch (\Exception $ex){
            Log::channel('booking')->error('Admin/BookingController - show '. $ex);
            return 'Terjadi kesalahan pada sistem!';
        }
    }

    public function edit($id){
        $booking = BookingHeader::find($id);
        $customer = User::where('phone', $booking->user_phone)->first();

        $countries = Utilities::getCountryCodes();
        $code = '+62';
        $country = 'ID';
        $phone = $booking->user_phone;
        $gender = $customer->gender;
        foreach ($countries as $c){
            if($c['code'] == $customer->country){
                $code = $c['dial_code'];
                $country = $c['code'];
            }
        }
        $phone = str_replace($code, '', $phone);

        $details = [];
        $addons = [];
        foreach($booking->booking_details as $detail){
            if($detail->package_id != null){
                $details[] = [
                    'id' => $detail->id,
                    'package' => $detail->package_id,
                    'name' => $detail->package_code . ' - ' . $detail->package_name,
                    'price' => $detail->price,
                    'qty' => 1
                ];
            }

            if($detail->item_id != null){
                $addons[] = [
                    'id' => $detail->id,
                    'item' => $detail->item_id,
                    'name' => $detail->item_code . ' - ' . $detail->item_name,
                    'price' => $detail->price,
                    'qty' => $detail->qty
                ];
            }
        }

        return view('admin.booking.edit', compact('details', 'addons', 'booking', 'countries', 'country', 'code', 'phone', 'gender'));
    }

    public function update(Request $request){
        $validator = Validator::make($request->all(), [
            'booking_date' => 'required',
            'booking_time' => 'required',
            'user_name'    => 'required',
            'user_phone'    => 'required',
        ], [
            'booking_date.required' => 'Tanggal booking harus dipilih!',
            'booking_time.required' => 'Jam booking harus dipilih!',
            'user_name.required'    => 'Nama customer harus diisi!',
            'user_phone.required'    => 'Nomor Telp Customer harus diisi!',
        ]);

        if ($validator->fails()) {
            Session::flash('error', $validator->errors()->first());
            return back()->withInput($request->except('_token'));
        }

        DB::beginTransaction();

        $packages = $request->input('packages') ?? [];
        $items = $request->input('items') ?? [];

        if(count($packages) == 0){
            DB::rollBack();

            Session::flash('error', 'Detail booking wajib diisi!');
            return back()->withInput($request->except('_token'));
        }

        try {
            $phone = $request->input('phone_code') . $request->input('user_phone');

            $member = User::where('phone', $phone)->first();
            if(!$member){
                User::create([
                    'name' => $request->input('user_name'),
                    'phone' => $phone,
                    'gender' => $request->input('gender'),
                    'country' => $request->input('country'),
                    'password' => Hash::make('123456'),
                ]);
            }

            $dateCarbon = Carbon::createFromFormat('d-m-Y', $request->input('booking_date'));
            $today      = Carbon::today('Asia/Jakarta');

            $header = BookingHeader::find($request->input('id'));

            $file = $request->file('payment_proof');
            $filename = null;
            if($file){
                $filename = 'Proof_' . Carbon::now()->format('Y-m-d_H-i-s') . '.' . $file->getClientOriginalExtension();
                $file->storeAs('', $filename, 'proofs');
            }

            $header->update([
                'type'                 => 'booking',
                'branch_id'            => Auth::user()->branch_id,
                'branch_code'          => Auth::user()->branch->code,
                'branch_name'          => Auth::user()->branch->name,
                'user_id'              => $member != null ? $member->id : null,
                'user_phone'           => $phone,
                'user_name'            => $request->input('user_name'),
                'user_email'           => $member != null ? $member->email : null,
                'notes'                => $request->input('notes'),
                'grand_total'          => $request->input('grand_total'),
                'total_payment_before' => $request->input('grand_total'),
                'total_payment'        => $request->input('grand_total'),
                'updated_by'           => Auth::user()->id,
                'booking_time'         => $request->input('booking_time'),
                'booking_date'         => $dateCarbon,
//                'duration'             => $request->input('duration'),
                'payment_proof'        => $filename
            ]);

            $startTime = Carbon::createFromFormat('d-m-Y H:i', $request->input('booking_date') . ' ' . $request->input('booking_time'));
            $endTime          = $startTime->copy()->addMinutes((int)$request->input('duration'));
            $bookingTimeRange = $startTime->format('H:i') . ' - ' . $endTime->format('H:i');

            $delIds = $request->input('del_ids') ?? [];
            BookingDetail::whereIn('id', $delIds)->delete();

            foreach ($packages as $detail){
                $package = Package::find($detail['id']);
                $check = BookingDetail::find($detail['detail_id']);
                if($check){
                    $check->update([
                        'type' => 'pos',
                        'header_id'    => $header->id,
                        'package_id'   => $package->id,
                        'package_code' => $package->code,
                        'package_name' => $package->name,
                        'qty'          => 1,
                        'price'        => $detail['price'],
                        'subtotal'     => $detail['price'],
                        'booking_time' => $bookingTimeRange,
                        'status_id'    => 3,
                    ]);
                }
                else {
                    BookingDetail::create([
                        'type' => 'pos',
                        'header_id'    => $header->id,
                        'package_id'   => $package->id,
                        'package_code' => $package->code,
                        'package_name' => $package->name,
                        'qty'          => 1,
                        'price'        => $detail['price'],
                        'subtotal'     => $detail['price'],
                        'booking_time' => $bookingTimeRange,
                        'status_id'    => 3,
                    ]);
                }
            }

            foreach ($items as $detail){
                $item = Item::find($detail['id']);
                $check = BookingDetail::find($detail['detail_id']);
                if($check){
                    $check->update([
                        'header_id'    => $header->id,
                        'item_id'   => $item->id,
                        'item_code' => $item->code,
                        'item_name' => $item->name,
                        'qty'          => $detail['qty'],
                        'price'        => $detail['price'],
                        'subtotal'     => $detail['price'] * $detail['qty'],
                        'booking_time' => $bookingTimeRange,
                        'status_id'    => 3,
                    ]);
                } else {
                    BookingDetail::create([
                        'header_id'    => $header->id,
                        'item_id'   => $item->id,
                        'item_code' => $item->code,
                        'item_name' => $item->name,
                        'qty'          => $detail['qty'],
                        'price'        => $detail['price'],
                        'subtotal'     => $detail['price'] * $detail['qty'],
                        'booking_time' => $bookingTimeRange,
                        'status_id'    => 3,
                    ]);
                }
            }

            DB::commit();

            Session::flash('success', 'Berhasil booking jadwal treatment!');
            return redirect()->route('admin.booking.index');
        } catch (\Exception $exception){
            DB::rollBack();
            Log::channel('booking')->error('[BookingController - store] - ' . $exception);

            Session::flash('error', 'Terjadi kesalahan pada sistem!');
            return back()->withInput($request->except('_token'));
        }
    }

    public function checkProof(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:booking_headers,id',
            'status' => 'required|integer|in:13,14',
        ]);

        if ($validator->fails()) {
            Session::flash('error', 'Data bukti pembayaran tidak valid!');
            return back()->withInput($request->except('_token'));
        }

        try {
            DB::beginTransaction();

            $booking = BookingHeader::findOrFail($request->input('id'));
            $status = (int) $request->input('status');
            $previousProof = $booking->payment_proof;
            $payload = ['status_id' => $status];

            if ($status === 14) {
                $payload['payment_proof'] = null;
            }

            $booking->update($payload);
            DB::commit();

            if ($status === 14) {
                Utilities::deleteProofImageIfExists($previousProof);
            }

            Session::flash('success', 'Berhasil ' . ($status === 14 ? 'reject' : 'approve') . ' bukti pembayaran!');
            return redirect()->route('admin.booking.show', ['id' => $booking->id]);
        } catch (\Exception $exception){
            DB::rollBack();
            Log::channel('booking')->error('[BookingController - store] - ' . $exception);

            Session::flash('error', 'Terjadi kesalahan pada sistem!');
            return back()->withInput($request->except('_token'));
        }
    }

    public function finalize(Request $request){
        DB::beginTransaction();
        try {
            $booking = BookingHeader::find($request->input('booking'));

            $time = explode(':', $request->input('time'));
            $start = Carbon::parse($request->input('date'))->setTime(intval($time[0]), intval($time[1]), 0);

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
                User::create([
                    'name' => $request->input('member_name'),
                    'phone' => $request->input('member_phone'),
                    'gender' => $request->input('gender'),
                    'password' => Hash::make('123456'),
                ]);
            }

            $header = TransactionHeader::create([
                'branch_id' => Auth::user()->branch_id,
                'code' => AutoNumberGenerator::generateAutonumberTrx('POS'),
                'member_name' => $request->input('member_name'),
                'member_phone' => $request->input('member_phone'),
                'status_id' => 9,
                'start_time' => $start->toDateTimeString(),
                'foc_type' => 'no',
                'employee_id' => $request->input('employee'),
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            $booking->update([
                'transaction_id' => $header->id,
                'status_id' => 9,
                'payment_approved_at' => Carbon::now('Asia/Jakarta')->toDateTimeString()
            ]);

            $finalEnd = $start->copy();
            foreach ($request->input('details') as $detail) {
                $endTime = null;
                if(array_key_exists('duration', $detail)){
                    $endTime = $start->copy()->addMinutes((int)$detail['duration']);
                }

                $dt = TransactionDetail::create([
                    'header_id' => $header->id,
                    'package_id' => $detail['type'] == 'package' ? $detail['id'] : null,
                    'room_id' => array_key_exists('room_id', $detail) ?$detail['room_id'] : null,
                    'status_id' => 9,
                    'qty' => $detail['type'] == 'item' ? $detail['qty'] : 1,
                    'price' => $detail['price'],
                    'subtotal' => $detail['type'] == 'item' ? ($detail['price'] * $detail['qty']) : $detail['price'],
                    'start_time' => $detail['type'] == 'package' || (array_key_exists('need_partner', $detail) && $detail['need_partner']) ? $start->toDateTimeString() : null,
                    'end_time' => $detail['type'] == 'package' || (array_key_exists('need_partner', $detail) && $detail['need_partner']) ? $endTime->toDateTimeString() : null,
                    'item_id' => $detail['type'] == 'item' ? $detail['id'] : null,
                    'new_order_qty' => $detail['type'] == 'item' ? $detail['qty'] : 1,
                ]);

                if($detail['type'] == 'package' || (array_key_exists('need_partner', $detail) && $detail['need_partner'] == 'true')) {
                    TransactionDetailPartner::create([
                        'partner_id' => $detail['partner_id'],
                        'transaction_detail_id' => $dt->id,
                    ]);
                }

                if($endTime != null && $endTime->greaterThan($finalEnd)) {
                    $finalEnd = $endTime->copy();
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

            $invoice = TransactionInvoice::create([
                'branch_id' => Auth::user()->branch_id,
                'header_ids' => implode('#', [$header->id]),
                'code' => AutoNumberGenerator::generateAutonumberTrx('POS'),
                'subtotal' => $request->input('subtotal'),
                'is_use_voucher_code' => $request->input('is_use_voucher_code') == 'true' ? 1 : 0,
                'voucher_code' => $voucherStr,
                'coupon_id' => $couponStr,
                'voucher_discount_value' => $voucherDiscountValue,
                'rounding_amount' => $request->input('grandtotal'),
                'custom_amount' => $request->input('custom_amount'),
                'return_value' => $request->input('return_value'),
                'member_name' => $request->input('member_name'),
                'member_phone' => $request->input('member_phone'),
                'status_id' => 9,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            TransactionPayment::create([
                'branch_id' => Auth::user()->branch_id,
                'invoice_id' => $invoice->id,
                'payment_type_id' => 7,
                'payment_type_code' => 'TF',
                'amount' => $request->input('amount_received')
            ]);

            // //add user point
            if($booking->point_used < 1){
                $pointUsed = (int) floor($request->input('grandtotal') / 10000);
                Utilities::addTransactionPoint(
                    $member->id,
                    $pointUsed,
                    $invoice->code,
                    'TRANSACTION ' . $invoice->code
                );
            }


            //Journal Transaction
            $journal = new JournalUtilities();
            $journal->createJournalPos($header->id);

            DB::commit();

            SendBookingPaymentApprovedNotification::dispatch($booking);

            return \response([
                'success' => true,
                'receiptPayment' => route('admin.monitor_room.print', ['id' => $invoice->id]),
                'code' => $header->code,
                'detail' => route('admin.transaction.show', ['id' => $invoice->id])
            ]);
        } catch (\Exception $e){
            DB::rollBack();

            Log::channel('monitor_room')->error('[Monitor Room - Start Treatment] - ' . $e);
            return Response::json($e->getMessage(), 500);
        }
    }

    public function assign(Request $request){
        try {
            if(!$request->filled('assign_room') ||
                !$request->filled('assign_partner') ||
                !$request->filled('booking_detail_id')){
                    return response()->json(collect([
                        'code' => 400,
                        'error' => 'Terjadi kesalahan input!'
                    ]), 200);
            }

            $user = Auth::guard('admin')->user();

            $roomId = $request->input('assign_room');
            $room = DB::table('rooms')
                ->where('id', '=', $roomId)
                ->first();

            $partnerId = $request->input('assign_partner');
            $partner = Partner::find($partnerId);

            $now = Carbon::now('Asia/Jakarta');

            $bookingDetail = BookingDetail::find($request->input('booking_detail_id'));

            $bookingHeader = BookingHeader::find($bookingDetail->header_id);

            $package = Package::where('id', $bookingDetail->package_id)->first();

            if ($package == null) {
                return response()->json(collect([
                    'code' => 400,
                    'error' => 'Treatment tidak ditemukan'
                ]), 200);
            }

            // Set the room
            $bookingDetail->room_id = $room->id;
            $bookingDetail->room_name = $room->name;
            $bookingDetail->room_code = $room->code;
            $bookingDetail->start_time = $now->toDateTimeString();
            $bookingDetail->status_id = 9;
            $bookingDetail->save();

            // Set the partner
            $newDetailPartner = new BookingDetailPartner();
            $newDetailPartner->detail_id = $bookingDetail->id;
            $newDetailPartner->partner_id = $partner->id;
            $newDetailPartner->partner_name = $partner->name;
            $newDetailPartner->partner_number = $partner->number;
            $newDetailPartner->partner_code = $partner->code;
            $newDetailPartner->partner_alias = $partner->alias;
            $newDetailPartner->status_id = 9;
            $newDetailPartner->save();

            // Create new Transaction Header
            $trxHeader = new TransactionHeader();
            $trxHeader->room_id = $room->id;
            $trxHeader->member_id = $bookingHeader->user_id ?? null;
            $trxHeader->member_name = $bookingHeader->user_name;
            $trxHeader->member_phone = $bookingHeader->user_phone;
            $trxHeader->code = AutoNumberGenerator::generateAutonumberTrx('POS');
            $trxHeader->branch_id = $room->branch_id;
            $trxHeader->created_by = $user->id;
            $trxHeader->status_id = 8;
            $trxHeader->start_time = $now;
            $trxHeader->save();

            // Create new Transaction Details
            $trxDetail = new TransactionDetail();
            $trxDetail->header_id = $trxHeader->id;
            $trxDetail->package_id = $package->id;
            $trxDetail->qty = 1;
            $trxDetail->price = $package->price;
            $trxDetail->subtotal = $trxDetail->price;
            $trxDetail->status_id = 3;
            $trxDetail->save();

            // Create Details for Add On if exists
            if ($partner->partner_level->item_id != null) {
                $addOn = Item::where('id', $partner->partner_level->item_id)->first();

                if ($addOn != null) {
                    $detailAddOn = new TransactionDetail();
                    $detailAddOn->header_id = $trxHeader->id;
                    $detailAddOn->item_id = $addOn->id;
                    $detailAddOn->qty = 1;
                    $detailAddOn->price = $request->input('is_free_add_on') ? 0 : $addOn->price;
                    $detailAddOn->subtotal = $request->input('is_free_add_on') ? 0 : $addOn->price;
                    $detailAddOn->status_id = 1;
                    $detailAddOn->save();
                }
            }

            $trxPartner = new TransactionDetailPartner();
            $trxPartner->transaction_detail_id = $trxDetail->id;
            $trxPartner->partner_id = $partner->id;
            $trxPartner->is_main = 1;
            $trxPartner->book_name = $partner->book_name;
            $trxPartner->save();

            // Update partner status
            DB::table('partners')
            ->where('id', '=', $partnerId)
            ->update([
                'status_id' => 6,
                'updated_at' => $now->toDateTimeString(),
                'updated_by' => $user->id,
            ]);

            // Update room status
            DB::table('rooms')
                ->where('id', '=', $roomId)
                ->update([
                    'status_id' => 6,
                    'updated_at' => Carbon::now('Asia/Jakarta')->toDateTimeString()
                ]);

            $log = new MonitoringLog();
            $log->partner_id = $partner->id;
            $log->room_id = $room->id;
            $log->admin_user_id = $user->id;
            $log->created_at = Carbon::now('Asia/Jakarta');
            $log->save();

            // Validate completed assigned partner
            if(!DB::table('booking_details')
                ->where('header_id', '=', $bookingDetail->header_id)
                ->whereNotNull('package_id')
                ->where('status_id', '!=', 9)
                ->exists())
            {
                $bookingHeader->status_id = 9;
                $bookingHeader->save();
            } else {
                $bookingHeader->status_id = 15;
                $bookingHeader->save();
            }

            return response()->json(collect([
                'response' => [
                    'partner_number' => $partner->number,
                    'room_name' => $room->name
                ],
                'code' => 200,
                'error' => 'none'
            ]), 200);
        }
        catch (\Exception $ex){
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

            $bookingHeader = BookingHeader::with('booking_details')->find($request->input('cancelled_booking_id'));
            if(empty($bookingHeader)){
                Session::flash('error', 'Booking tidak ditemukan!');
                return redirect()->back();
            }

            // Validate status
            if($bookingHeader->status_id === 1){
                Session::flash('error', 'Booking tidak bisa dibatalkan!');
                return redirect()->back();
            }

            $admin = Auth::guard('admin')->user();

            // Cancel booking
            $bookingHeader->status_id = 10;
            $bookingHeader->canceled_at = Carbon::now('Asia/Jakarta')->toDateTimeString();
            $bookingHeader->updated_by = $admin->id;
            $bookingHeader->save();

            // Cancel transaction if exists
            if(!empty($bookingHeader->transaction_id)){
                DB::table('transaction_headers')
                    ->where('id', '=', $bookingHeader->transaction_id)
                    ->update([
                        'status_id' => 16,
                        'voided_at' => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                        'updated_by' => $admin->id
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

            $dataModel = collect([
                'id'        => $bookingHeader->id,
                'order_no'  => $bookingHeader->code,
                'type'      => $bookingHeader->type,
            ]);

//            FCMNotification::setNotification('User',
//                $bookingHeader->user_id,
//                'Konfirmasi Batal Booking',
//                'Booking '. $bookingHeader->code. ' OURA',
//                'Booking Anda telah dibatalkan!',
//                111,
//                $dataModel);

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

    public function devCreate($branchId, $mode){
        try{
            $packages = DB::table('packages')
                ->where('branch_id', '=', $branchId)
                ->where('status_id', '!=', 2)
                ->where('is_happy_hour', '=', 0);

            if($mode === 'home_service'){
                $packages = $packages->where('is_home_service', '=', 1);
            }

            $packages = $packages->get();

            $addons = DB::table('items')
                ->where('branch_id', '=', $branchId)
                ->where('status_id', '=', 1)
                ->where('is_addon', '=', 1)
                ->orderBy('name')
                ->get();

            $partners = DB::table('partners')
                ->where('branch_id', '=', $branchId)
                ->get();

            $data = [
                'branchId' => $branchId,
                'mode' => $mode,
                'packages' => $packages,
                'addons' => $addons,
                'partners' => $partners
            ];

            return view('admin.booking.create')->with($data);
        }
        catch (\Exception $ex){
            dd($ex);
        }
    }

    public function devStore(Request $request){
        try{
            $mode = $request->input('mode');
            $branchId = $request->input('branchId');
            $branch = DB::table('branches')->where('id', '=', $branchId)->first();

            $errors = [];
            $voucherCode = null;
            $isOwnerCoupon = false;
            $isUnlimited = false;
            $voucherItem = null;

            if($request->filled('voucher_code')){
                $voucherCode = $request->input('voucher_code');

                $voucherItem = DB::table('coupon_lists')
                    ->where('code', '=', $voucherCode)
                    ->first();

                if(empty($voucherItem)){
                    $errors[] = 'Kode Voucher tidak ditemukan!';
                }
                else{
                    $voucher = DB::table('coupons')
                        ->where('id', '=', $voucherItem->coupon_id)
                        ->first();

                    if($voucher->is_foc === 1){
                        $isOwnerCoupon = true;
                    }

                    if($voucher->is_unlimited === 1){
                        $isUnlimited = true;
                    }

                    if(!empty($voucherItem->exp_date)){
                        $expiredDate = Carbon::parse($voucherItem->exp_date);
                        if($expiredDate->lessThanOrEqualTo(Carbon::now('Asia/Jakarta'))){
                            $errors[] = 'Kode Voucher sudah kadaluarsa!';
                        }
                    }

                    if($voucherItem->is_used === 1){
                        $errors[] = 'Kode Voucher sudah terpakai!';
                    }

                    if(isset($data['package_ids']) || !empty($data['package_ids'])){
                        if(count($data['package_ids']) > 1){
                            $errors[] = 'Penggunaan voucher diskon hanya untuk 1 treatment!';
                        }
                    }
                }
            }

            if(count($errors) > 0){
                dd($errors);
            }

            $tmpTotal = 0;
            $package = DB::table('packages')
                ->where('id', '=', $request->input('package'))
                ->first();

            $tmpTotal += $package->price;

            $addons = [];
            if($request->filled('addons')){
                $addons = $request->input('addons');
                foreach($addons as $addon){
                    $item = Item::find($addon);
                    if(!empty($item)){
                        $tmpTotal += $item->price;
                    }
                }
            }

            $discount = 0;
            $discountPercentage = 0;
            $voucher = null;

            if($request->filled('voucher_code')){
                $voucherItem = DB::table('coupon_lists')
                    ->where('code', '=', $voucherCode)
                    ->first();

                if(!empty($voucherItem)){
                    $voucher = DB::table('coupons')
                        ->where('id', '=', $voucherItem->coupon_id)
                        ->first();

                    if(!$isOwnerCoupon){
                        $discountPercentage = $voucher->discount_percentage;
                        $discount = $tmpTotal * ($voucher->discount_percentage / 100);
                        $tmpTotal -= $discount;
                    }

                    if(!$isUnlimited){
                        // Update is_used
                        DB::table('coupon_lists')
                            ->where('id', '=', $voucherItem->id)
                            ->update([
                                'is_used' => 1
                            ]);
                    }
                }
            }

            // Check for extra fee
            $totalExtraFee = 0;
            if(!$isOwnerCoupon){
                if($mode === 'home_service' && $branch->home_service_fee > 0){
                    $totalExtraFee += $branch->home_service_fee;
                    $tmpTotal += $branch->home_service_fee;
                }
            }

            $admin = Auth::guard('admin')->user();

            $today = Carbon::today('Asia/Jakarta');
            $pattern = 'BOOK/'. $today->month;
            $autonumber = AutoNumberGenerator::generateBookAutoNumber($pattern);

            $user = User::find(3);

            $newBooking = new BookingHeader();
            $newBooking->type = $mode;
            $newBooking->code = $autonumber;
            $newBooking->branch_id = $branchId;
            $newBooking->branch_name = $branch->name;
            $newBooking->branch_code = $branch->code;
            $newBooking->user_id = $user->id;
            $newBooking->user_name = $user->name;
            $newBooking->user_phone = $user->phone;
            $newBooking->user_email = $user->email;
            $newBooking->booking_time = $request->input('time');
            $newBooking->voucher_code = $voucher->code ?? null;
            $newBooking->voucher_value = $discountPercentage;
            $newBooking->app_coupon_code = $voucherItem->code ?? null;
            $newBooking->total_discount = $discount;
            $newBooking->grand_total = $tmpTotal;
            $newBooking->payment_charge_ppn_percentage = 0;
            $newBooking->payment_charge_ppn_value = 0;
            $newBooking->payment_charge = 0;
            $newBooking->payment_extra_charge = $totalExtraFee;
            $newBooking->total_payment_before = $tmpTotal + $newBooking->payment_charge;
            $newBooking->total_payment = ceil($newBooking->total_payment_before);
            $newBooking->udf_03 = Str::random(20);
            $newBooking->pg_code = null;
            $newBooking->payment_type = !$isOwnerCoupon ? 'PAYMENT_GATEWAY' : 'FOC';
            $newBooking->point_used = 0;
            $newBooking->booked_massage_style = $request->input('style');
            $newBooking->booked_gender = $request->input('gender');
            $newBooking->status_id = 9;
            $newBooking->created_by = $admin->id;
            $newBooking->updated_by = $admin->id;
            $newBooking->save();


            // if($type === 'home_service'){
            //     $newBooking->customer_province_name = $data['province_name'];
            //     $newBooking->customer_city_name = $data['city_name'];
            //     $newBooking->customer_district_name = $data['district_name'];
            //     $newBooking->customer_full_address = $data['full_address'];
            //     $newBooking->customer_postal_code = $data['postal_code'] ?? '';
            //     $newBooking->customer_latitude = $data['latitude'];
            //     $newBooking->customer_longitude = $data['longitude'];
            //     $newBooking->customer_distance = $distanceFormatted;
            //     $newBooking->save();
            // }

            $newDetail = new BookingDetail();
            $newDetail->header_id = $newBooking->id;
            $newDetail->package_id = $package->id;
            $newDetail->package_name = $package->name;
            $newDetail->package_code = $package->code;
            $newDetail->qty = 1;
            $newDetail->price = $package->price;

            $discountDetail = 0;
            if($discountPercentage > 0){
                $discountDetail = $package->price * ($discountPercentage / 100);
                $subtotal = $package->price - $discountDetail;
            }
            else{
                $subtotal = $package->price;
            }

            $newDetail->discount_amount = $discountDetail;
            $newDetail->discount_percentage = $discountPercentage;
            $newDetail->subtotal = $subtotal;
            $newDetail->booking_time = $request->input('time');
            $newDetail->status_id = 9;
            $newDetail->save();

            // Set the partner
            $partner = Partner::find($request->input('partner'));
            $newDetailPartner = new BookingDetailPartner();
            $newDetailPartner->detail_id = $newDetail->id;
            $newDetailPartner->partner_id = $partner->id;
            $newDetailPartner->partner_name = $partner->name;
            $newDetailPartner->partner_number = $partner->number;
            $newDetailPartner->partner_code = $partner->code;
            $newDetailPartner->partner_alias = $partner->alias;
            $newDetailPartner->status_id = 1;
            $newDetailPartner->save();

            foreach($addons as $addon){
                $item = Item::find($addon);
                if(!empty($item)){
                    $newItemDetail = new BookingDetail();
                    $newItemDetail->header_id = $newBooking->id;
                    $newItemDetail->item_id = $item->id;
                    $newItemDetail->item_name = $item->name;
                    $newItemDetail->item_code = $item->code;
                    $newItemDetail->qty = 1;
                    $newItemDetail->price = $item->price;

                    $discountDetail = 0;
                    if($discountPercentage > 0){
                        $discountDetail = $item->price * ($discountPercentage / 100);
                        $subtotal = $item->price - $discountDetail;
                    }
                    else{
                        $subtotal = $item->price;
                    }

                    $newItemDetail->discount_amount = $discountDetail;
                    $newItemDetail->discount_percentage = $discountPercentage;
                    $newItemDetail->subtotal = $subtotal;
                    $newItemDetail->status_id = 1;
                    $newItemDetail->save();
                }
            }

            $bookingHeader = BookingHeader::find($newBooking->id);

            // Get locker
            $locker = DB::table('lockers')
                ->where('branch_id', '=', $bookingHeader->branch_id)
                ->first();

            // Create transaction
            $header = new TransactionHeader();
            $header->code = AutoNumberGenerator::generateAutonumberTrx('POS');
            $header->branch_id = $bookingHeader->branch_id;
            $header->locker_id = $locker->id;
            $header->status_id = 9;
            $header->discount = $bookingHeader->total_discount;
            $header->voucher_code = $bookingHeader->voucher_code ?? null;
            $header->voucher_value = $bookingHeader->voucher_value ?? 0;
            $header->app_coupon_code = $bookingHeader->app_coupon_code ?? null;
            $header->foc_type = $isOwnerCoupon ? 'foc' : null;
            $header->foc_number = $isOwnerCoupon ? 'ONLINE' : null;
            $header->discount_total = $bookingHeader->total_discount;
            $header->grand_total = $bookingHeader->grand_total;
            $header->amount = $bookingHeader->grand_total;
            $header->return_value = 0;
            $header->created_by = $admin->id;
            $header->updated_by = $admin->id;
            $header->save();

            $bookingHeader->transaction_id = $header->id;
            $bookingHeader->save();

            foreach ($bookingHeader->booking_details as $detail){
                $newDetail = new TransactionDetail();
                $newDetail->header_id = $header->id;
                $newDetail->package_id = $detail->package_id ?? null;
                $newDetail->item_id = $detail->item_id ?? null;
                $newDetail->start_time = $detail->start_time;
                $newDetail->qty = 1;
                $newDetail->price = $detail->price;
                $newDetail->subtotal = $detail->subtotal;
                $newDetail->discount_amount = $isOwnerCoupon ? 0 : $detail->discount_amount;
                $newDetail->discount_percentage = $isOwnerCoupon ? 0 : $detail->discount_percentage;
                $newDetail->status_id = 9;
                $newDetail->customer_name = !empty($detail->package_id) ? $bookingHeader->user_name : null;
                $newDetail->save();
            }

            $newPayment = new TransactionPayment();
            $newPayment->branch_id = $header->branch_id;
            $newPayment->header_id = $header->id;

            $paymentType = DB::table('pos_payment_types')
                ->where('branch_id', '=', $bookingHeader->branch_id)
                ->where('code', '=', 'ONLINE')
                ->first();

            $newPayment->payment_type_id = $paymentType->id;
            $newPayment->payment_type_code = 'ONLINE';
            $newPayment->amount = $isOwnerCoupon ? 0 : $header->grand_total;
            $newPayment->save();

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

            return 'SUCCESS';
        }
        catch (\Exception $ex){
            dd($ex);
        }
    }

    public function addTs(Request $request, $id){
        try{
            $bookingHeader = DB::table('booking_headers')
                ->where('id', '=', $id)
                ->first();

            if(empty($bookingHeader)){
                return redirect()->back();
            }

            $tsItemId = $request->input('ts_item');

            if(DB::table('booking_details')
                ->where('header_id', '=', $id)
                ->where('item_id', '=', $tsItemId)
                ->exists()){
                Session::flash('error', 'TS terapis sudah ada dan tidak bisa ditambahkan lagi!');
            }

            $tsItem = Item::find($tsItemId);

            // Add to booking details
            $newItemDetail = new BookingDetail();
            $newItemDetail->header_id = $id;
            $newItemDetail->item_id = $tsItem->id;
            $newItemDetail->item_name = $tsItem->name;
            $newItemDetail->item_code = $tsItem->code;
            $newItemDetail->qty = 1;
            $newItemDetail->price = $tsItem->price;
            $newItemDetail->discount_amount = 0;
            $newItemDetail->discount_percentage = 0;
            $newItemDetail->subtotal = $tsItem->price;
            $newItemDetail->status_id = 1;
            $newItemDetail->save();

            // Add to transaction details
            $newDetail = new TransactionDetail();
            $newDetail->header_id = $bookingHeader->transaction_id;
            $newDetail->item_id = $tsItem->id;
            $newDetail->qty = 1;
            $newDetail->price = $tsItem->price;
            $newDetail->subtotal = $tsItem->price;
            $newDetail->discount_amount = 0;
            $newDetail->discount_percentage = 0;
            $newDetail->status_id = 1;
            $newDetail->save();

            Session::flash('success', 'Berhasil tambah TS terapis!');
            return redirect()->back();
        }
        catch (\Exception $ex){
            Log::channel('booking')->error('Admin/BookingController - addTs '. $ex);
            Session::flash('error', 'Maaf terjadi kesalahan sistem!' . $ex);
            return redirect()->back();
        }
    }

    public function updateAddonPartner(Request $request, $id){
        try{
            $bookingHeader = DB::table('booking_headers')
                ->where('id', '=', $id)
                ->first();

            if(empty($bookingHeader)){
                return redirect()->back();
            }

            $addonCommissions = $request->input('addons');

            $trxHeader = TransactionHeader::find($bookingHeader->transaction_id);
            foreach($trxHeader->transaction_details as $detail){
                foreach($addonCommissions as $addonCommission){
                    if($detail->id == $addonCommission['trx_detail_id'] && $addonCommission['partner_id'] > 0){
                        $partner = Partner::find($addonCommission['partner_id']);
                        $detail->partner_id = $partner->id;
                        $detail->partner_level = $partner->type;
                        $detail->save();

                        break;
                    }
                }
            }

            Session::flash('success', 'Berhasil atur komisi Addon Terapis!');
            return redirect()->back();
        }
        catch (\Exception $ex){
            Log::channel('booking')->error('Admin/BookingController - updateAddonPartner '. $ex);
            Session::flash('error', 'Maaf terjadi kesalahan sistem!' . $ex);
            return redirect()->back();
        }
    }

    public function checkPassword(Request $request){
        $config = Configuration::where('key', 'spv_password')->first();
        if(!Hash::check($request->input('password'), $config->value)){
            return \response([
                'success' => false,
            ]);
        }

        return \response([
            'success' => true,
        ]);
    }
}
