<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Libs\AutoNumberGenerator;
use App\Libs\JournalUtilities;
use App\Libs\Utilities;
use App\Models\AdminUser;
use App\Models\AppBirthdayPromoUsage;
use App\Models\AttendanceData;
use App\Models\Branch;
use App\Models\Coa;
use App\Models\CommissionPartnerHistory;
use App\Models\Configuration;
use App\Models\Coupon;
use App\Models\CouponList;
use App\Models\Employee;
use App\Models\EmployeePosition;
use App\Models\Item;
use App\Models\ItemCategory;
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
use App\Models\TransactionDetail;
use App\Models\TransactionDetailPartner;
use App\Models\TransactionHeader;
use App\Models\TransactionInvoice;
use App\Models\TransactionPayment;
use App\Models\User;
use App\Models\UserVoucherCode;
use App\Transformer\TransactionTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class TransactionController extends Controller
{

    public function reportTherapistCommissionFilter(Request $request)
    {
        return view('admin.report.report_therapist_commision');
    }

    public function generateTherapistCommissionReport(Request $request)
    {
        try{
            $dateRangeString = $request->input('period');

            if (strpos($dateRangeString, ' - ') === false) {
                $startDate = Carbon::createFromFormat('Y-m-d', $dateRangeString)->startOfDay();
                $endDate   = Carbon::createFromFormat('Y-m-d', $dateRangeString)->endOfDay();

            }
            else{
                $dateParts = explode(' - ', $dateRangeString);

                $startDate = Carbon::createFromFormat('Y-m-d', $dateParts[0])->startOfDay();
                $endDate   = Carbon::createFromFormat('Y-m-d', $dateParts[1])->endOfDay();

            }
            $partners = Partner::orderBy('status_id')->where('attend_machine_id', '>', 0)->where('employement_status', 'Aktif')->orderBy('code')->get();
            $result = [];
            foreach($partners as $partner) {
                // get hk by attendance
                $hk = $this->therapistData("attendance", $partner, $startDate, $endDate);
                $hk_amount = (int)$partner->basic_salary_amount ?? 30000;
                $late_amount = $this->therapistData("late", $partner, $startDate, $endDate);
                $lembur_amount = $this->therapistData("lembur", $partner, $startDate, $endDate);
                // dd($lembur_amount,  $partner, $startDate, $endDate);
                //get commission from table commision
                $commision = $this->therapistData("commission", $partner, $startDate, $endDate);
                $insentif = 0;
                $thp = ($hk * $hk_amount) - $late_amount + $lembur_amount + $commision + $insentif;

                $result[] = [
                    'code' => $partner->code,
                    'name' => $partner->name,
                    'periode'   => $dateRangeString,
                    'hk'    => $hk,
                    'hk_amount'    => $hk_amount,
                    'hk_total'    => ($hk * $hk_amount),
                    'late_amount'    => $late_amount,
                    'lembur_amount'    => $lembur_amount,
                    'subtotal'    => ($hk * $hk_amount) - $late_amount + $lembur_amount,
                    'commision'    => $commision,
                    'insentif'    => $insentif,
                    'thp'    => $thp,
                    'bank_name'    => $partner->account_bank_name ?? "",
                    'no_rek'    => $partner->account_number ?? "",
                ];
            }
            return Response::json(['status' => 'success', 'data' => $result]);
        }
        catch(\Exception $ex){
            return Response::json(['status' => 'error', 'message' => $ex->getMessage()]);
        }
    }
    function therapistData($type, $partner, $startDate, $endDate) {
        $total = 0;
        switch($type) {
            case "attendance":
                $total = AttendanceData::where('partner_id', $partner->id)
                    ->where('type_id', 2)
                    ->where('timestamp', '>=', $startDate)
                    ->where('timestamp', '<=', $endDate)
                    ->count();
                break;
            case "commission":
                    $queryQty = "
                        SELECT
                            p.id AS id,
                            p.name AS name,
                            SUM(dt.price * dt.qty) AS total_treatment,
                            COUNT(tdp.id) AS qty,
                            SUM((dt.price * dt.qty) * (pkg.commission_percent / 100)) AS commission_amount
                        FROM partners p
                        INNER JOIN transaction_detail_partners tdp ON tdp.partner_id = p.id
                        INNER JOIN transaction_details dt ON tdp.transaction_detail_id = dt.id
                        INNER JOIN transaction_headers th ON dt.header_id = th.id
                        INNER JOIN packages pkg ON dt.package_id = pkg.id
                        WHERE p.id = " . $partner->id . "
                            AND th.status_id = 9
                            AND dt.package_id IS NOT NULL
                            AND dt.updated_at >= '" . $startDate . "'
                            AND dt.updated_at <= '" . $endDate . "'
                        GROUP BY p.id, p.name
                    ";

                    $queryQtyAddon = "
                        SELECT
                            p.id AS id,
                            p.name AS name,
                            SUM(COALESCE(dt.price,0) * COALESCE(dt.qty,0)) AS total_treatment,
                            SUM(COALESCE(dt.qty,0)) AS qty,
                            SUM((COALESCE(dt.price,0) * COALESCE(dt.qty,0)) * (itm.commission_percent / 100)) AS commission_amount
                        FROM partners p
                        INNER JOIN transaction_detail_partners tdp ON tdp.partner_id = p.id
                        INNER JOIN transaction_details dt ON tdp.transaction_detail_id = dt.id
                        INNER JOIN items itm ON dt.item_id = itm.id
                        INNER JOIN transaction_headers th ON dt.header_id = th.id
                        WHERE p.id = " . $partner->id . "
                            AND th.status_id = 9
                            AND dt.item_id IS NOT NULL
                            AND itm.category_id = 27
                            AND itm.id NOT IN (48,49)
                            AND dt.updated_at >= '" . $startDate . "'
                            AND dt.updated_at <= '" . $endDate . "'
                        GROUP BY p.id, p.name
                    ";


                    $qtyData = DB::select($queryQty);
                    $qtyTambahan = DB::select($queryQtyAddon);

                    if (count($qtyData) > 0) {
                        $total += (int)$qtyData[0]->commission_amount;
                    }
                    if (count($qtyTambahan) > 0) {
                        $total += (int)$qtyTambahan[0]->commission_amount;
                    }

                // $total = CommissionPartnerHistory::where('partner_id', $partner->id)
                //     ->where('transaction_date', '>=', $startDate)
                //     ->where('transaction_date', '<=', $endDate)
                //     ->sum('commission_result');
                break;
            case "late":
                $totalLate1 = AttendanceData::where('partner_id', $partner->id)
                    ->where('state', "LATE 1")
                    ->where('timestamp', '>=', $startDate)
                    ->where('timestamp', '<=', $endDate)
                    ->count();
                $total += $totalLate1 * 15000;
                $totalLate2 = AttendanceData::where('partner_id', $partner->id)
                    ->where('state', "LATE 2")
                    ->where('timestamp', '>=', $startDate)
                    ->where('timestamp', '<=', $endDate)
                    ->count();
                $total += $totalLate2 * 30000;
                break;
            case "lembur":
                $totalLembur = AttendanceData::where('partner_id', $partner->id)
                    ->where('state', "LEMBUR 1")
                    ->where('timestamp', '>=', $startDate)
                    ->where('timestamp', '<=', $endDate)
                    ->count();
                $total += $totalLembur * 15000;
                break;
        }

        return (int) $total;
    }
    function therapistAttendance($partner, $startDate, $endDate) {
        $totalAttendance = AttendanceData::where('partner_id', $partner->id)
            ->where('type_id', 2)
            ->where('timestamp', '>=', $startDate)
            ->where('timestamp', '<=', $endDate)
            ->count();

        return (int) $totalAttendance;
    }

    public function reportSalonStudioCommissionFilter(Request $request)
    {
        return view('admin.report.report_salon_studio_commision');
    }

    public function generateSalonStudioCommissionReport(Request $request)
    {
        try{
            $dateRangeString = $request->input('period');
            $type = $request->input('type');

            if (strpos($dateRangeString, ' - ') === false) {
                $startDate = Carbon::createFromFormat('Y-m-d', $dateRangeString)->startOfDay();
                $endDate   = Carbon::createFromFormat('Y-m-d', $dateRangeString)->endOfDay();

            }
            else{
                $dateParts = explode(' - ', $dateRangeString);

                $startDate = Carbon::createFromFormat('Y-m-d', $dateParts[0])->startOfDay();
                $endDate   = Carbon::createFromFormat('Y-m-d', $dateParts[1])->endOfDay();

            }

            switch ($type) {
                case "STUDIO" :
                    $packageList = Package::whereIn('room_type_id', [6])->where('status_id', 1)->pluck('id');
                    $itemList = [];
                    break;
                case "SALON" :
                    $packageList = Package::whereIn('room_type_id', [4])->where('status_id', 1)->pluck('id');
                    $itemList = [48, 49];
                    break;
                case "SPORT" :
                    $packageList = Package::whereIn('room_type_id', [26])->where('status_id', 1)->pluck('id');
                    $itemList = [];
                    break;
            }
            // $transactions = TransactionInvoice::join('transaction_details', 'transaction_details.header_id', '=', 'transaction_invoices.header_ids')
            $transactions = TransactionDetail::join('transaction_invoices', 'transaction_details.header_id', '=', 'transaction_invoices.header_ids')
                ->join('packages', 'packages.id', '=', 'transaction_details.package_id')
                ->select(
                    'transaction_invoices.code as code',
                    'transaction_details.updated_at as created_at',
                    'packages.id as package_id',
                    'packages.name as package_name',
                    'packages.commission_percent as commission_percent',
                    'transaction_details.subtotal as subtotal',
                    'transaction_details.discount_amount as discount_amount',
                    'transaction_details.qty as qty',
                    'transaction_details.price as price',
                )
                ->whereIn('transaction_details.package_id', $packageList)
                ->wherebetween('transaction_details.updated_at', [$startDate->toDateTimeString(), $endDate->toDateTimeString()])
                ->where('transaction_invoices.status_id', 9)
                ->orderby('transaction_details.updated_at', 'asc')
                ->get();
            $transaction2s = TransactionDetail::join('transaction_invoices', 'transaction_details.header_id', '=', 'transaction_invoices.header_ids')
                ->join('items', 'items.id', '=', 'transaction_details.item_id')
                ->select(
                    'transaction_invoices.code as code',
                    'transaction_details.updated_at as created_at',
                    'items.id as package_id',
                    'items.name as package_name',
                    'items.commission_percent as commission_percent',
                    'transaction_details.subtotal as subtotal',
                    'transaction_details.discount_amount as discount_amount',
                    'transaction_details.qty as qty',
                    'transaction_details.price as price',
                )
                ->whereIn('transaction_details.item_id', $itemList)
                ->wherebetween('transaction_details.updated_at', [$startDate->toDateTimeString(), $endDate->toDateTimeString()])
                ->where('transaction_invoices.status_id', 9)
                ->orderby('transaction_details.updated_at', 'asc')
                ->get();
            $result = [];
            $totalCommision = 0;
            foreach($transactions as $transaction) {
                // get hk by attendance
                // $total = $transaction->subtotal;
                if($type == "STUDIO"){
                    $total = $transaction->subtotal;
                    $total += ceil($total * 0.1);
                }
                else{
                    $total = $transaction->subtotal - $transaction->discount_amount;
                }
                $hk = 1;
                $commisionAmount = $total * $transaction->commission_percent / 100;
                $result[] = [
                    'code' => $transaction->code,
                    'package_name' => $transaction->package_name,
                    'qty' => $transaction->qty,
                    'price' => $transaction->price,
                    'date' => Carbon::parse($transaction->created_at)->format('d-m-Y'),
                    'subtotal'   => $total,
                    'commision_amount'    => $commisionAmount
                ];
                $totalCommision += $commisionAmount;
            }

            foreach($transaction2s as $transaction) {
                // get hk by attendance
                // $total = $transaction->subtotal;
                if($type == "STUDIO"){
                    $total = $transaction->subtotal;
                    $total += ceil($total * 0.1);
                }
                else{
                    $total = $transaction->subtotal - $transaction->discount_amount;
                }
                $hk = 1;
                $commisionAmount = $total * $transaction->commission_percent / 100;
                $result[] = [
                    'code' => $transaction->code,
                    'package_name' => $transaction->package_name,
                    'qty' => $transaction->qty,
                    'price' => $transaction->price,
                    'date' => Carbon::parse($transaction->created_at)->format('d-m-Y'),
                    'subtotal'   => $total,
                    'commision_amount'    => $commisionAmount
                ];
                $totalCommision += $commisionAmount;
            }
            // dd($result);
            return Response::json(['status' => 'success', 'data' => $result, 'total_commision' => $totalCommision]);
        }
        catch(\Exception $ex){
            return Response::json(['status' => 'error', 'message' => $ex->getMessage()]);
        }
    }

    public function searchExistingCustomer(Request $request)
    {
        try {
            $nResult = [];

            $user = Auth::guard('admin')->user();

            $search = $request->input('search');

            $searchDatas = DB::table('users')
                ->select('name', 'phone', 'country', 'gender')
                ->distinct()
                ->where('status_id', '!=', 2)
                ->where(function ($query) use ($search) {
                    $query->Where('name', 'like', '%' . $search . '%');
                })
                ->get();

            foreach ($searchDatas as $searchData) {
                $result = [
                    'customer_name' => $searchData->name,
                    'customer_phone' => $searchData->phone,
                    'country' => $searchData->country,
                    'gender' => $searchData->gender,
                ];
                $nResult[] = $result;
            }

            return $nResult;
        } catch (\Exception $exception) {
            Log::channel('pos')->error('[Transaction - searchExistingMember] - ' . $exception);
        }
    }
    public function index(Request $request)
    {
        $user = Auth::guard('admin')->user();
        $userData = AdminUser::find($user->id);
        $position = EmployeePosition::where('code', 'GRO')->where('branch_id', $userData->branch_id)->first();
        $employees = [];
        if ($position != null) {
            $employees = Employee::where('branch_id', $userData->branch_id)->where('position_id', $position->id)->get();
        }

        $filterStartDate = Carbon::today()->startOfYear()->format('d-m-Y');
        $filterEndDate = Carbon::today()->format('d-m-Y');

        if ($request->start_date != null && $request->end_date != null) {
            $startDateDecoded = rawurldecode($request->start_date);
            $endDateDecoded = rawurldecode($request->end_date);
            $start = Carbon::createFromFormat('!d-m-Y', $startDateDecoded, 'Asia/Jakarta');
            $end = Carbon::createFromFormat('!d-m-Y', $endDateDecoded, 'Asia/Jakarta');

            if ($end->greaterThanOrEqualTo($start)) {
                $filterStartDate = $startDateDecoded;
                $filterEndDate = $endDateDecoded;
            }
        }

        $filterEmployeeId = -1;
        // Filter item
        if ($request->employee_id != null) {
            $filterEmployeeId = intval($request->employee_id);
            $filterEmployee = DB::table('employees')
                ->where('id', '=', $filterEmployeeId)
                ->first();

            if (empty($filterEmployee)) {
                $filterEmployeeId = -1;
            }
        }

        $filterApprove = -1;
        if ($request->approve != NULL) {
            $filterApprove = intval($request->approve);
        }

        $data = [
            'filterStartDate'   => $filterStartDate,
            'filterEndDate'     => $filterEndDate,
            'filterEmployeeId'  => $filterEmployeeId,
            'employees'         => $employees,
            'filterApprove'     => $filterApprove
        ];

        if ($user->branch->is_oura == 1) {
            return view('admin.transaction.index_oura')->with($data);
        } else {
            return view('admin.transaction.index')->with($data);
        }
    }

    public function getIndex(Request $request)
    {
        $user = Auth::guard('admin')->user();
        $userData = AdminUser::where('id', $user->id)->first();
        $headers = TransactionInvoice::select('transaction_invoices.id', 'transaction_invoices.code', 'transaction_invoices.created_at', 'transaction_invoices.grandtotal',
            'statuses.display as status', 'transaction_invoices.member_name', 'transaction_invoices.member_phone', 'transaction_invoices.return_value', 'transaction_invoices.status_id',
            'transaction_headers.start_time as start_time')
            ->leftJoin('statuses', 'statuses.id', '=', 'transaction_invoices.status_id')
            ->leftJoin('transaction_headers', 'transaction_headers.id', '=', 'transaction_invoices.header_ids')
            ->where('transaction_invoices.id', '>', 0)
            ->where('transaction_invoices.status_id', '!=', 2)
            ->where('transaction_invoices.branch_id', $userData->branch_id);

        if ($request->filled('start_date') || $request->filled('end_date')) {
            // Filter date
            $start = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
            $end = Carbon::createFromFormat('!d-m-Y', $request->input('end_date'), 'Asia/Jakarta');
            $end->addDays(1);

            $headers = $headers->whereBetween('transaction_headers.start_time', array($start->toDateTimeString(), $end->toDateTimeString()));
        }

        return DataTables::of($headers)
            ->addColumn('action', function ($row) {
                $urlShowTrx = route('admin.transaction.show', ['id' => $row->id]);
                $action = "<a class='btn btn-xs btn-primary' href='". $urlShowTrx."'><i class='fas fa-xs fa-info'></i></a>";
                if($row->status_id == 9){
                    $urlReprint = route('admin.monitor_room.print',['id' => $row->id]) . '?reprint=true';
                    $action .= "&nbsp;<a class='btn btn-xs btn-success' href='". $urlReprint."' target='_blank'><i class='fas fa-xs fa-receipt'></i></a>";
                }

                return $action;
            })
            ->make(true);
    }

    public function show($id)
    {
        $user = Auth::guard('admin')->user();
        $userData = AdminUser::where('id', $user->id)->first();
        $branch = Branch::where('id', $userData->branch_id)->first();

        $invoice = TransactionInvoice::find($id);

        $headerIds = explode('#', $invoice->header_ids);
        $headers = TransactionHeader::whereIn('id', $headerIds)->get();

        $partners = Partner::where('branch_id', $branch->id)->get();

        $journal = JournalHeader::where('reference_id', $id)->first();

        return view('admin.transaction.show', compact(
            'invoice',
            'branch',
            'userData',
            'headers',
            'partners',
            'journal',
        ));
    }

    public function showToChange($id)
    {
        $header = TransactionHeader::with(['branch', 'status', 'locker', 'employee'])
            ->where('id', '=', $id)
            ->first();

        if ($header == null) {
            Session::flash('error', 'Transaksi tidak ditemukan!');
            return Redirect::route('admin.transaction.index');
        }

        $transformer = new TransactionTransformer();
        $model = $transformer->transform($header);
        $details = TransactionDetail::with(['package', 'item', 'room', 'membership'])->where('header_id', $header->id)->get();
        $tmpDt = TransactionDetail::with('room')->where('header_id', $header->id)->where('room_id', '!=', null)->first();
        if ($tmpDt != null) {
            $room = $tmpDt->room->number;
        } else {
            $room = '-';
        }

        //$payments = TransactionPayment::where('header_id', $header->id)->get();

        $autoJournalConfig = DB::table('configurations')
            ->where('key', '=', 'is_auto_journal')
            ->first();
        $isAutoJournal = $autoJournalConfig->value_int > 0;

        // Check home payment fee and point usage
        $homeServiceFee = 0;
        $pointUsed = 0;
        if ($header->branch->is_oura === 1) {
            $bookingHeader = DB::table('booking_headers')
                ->where('transaction_id', '=', $header->id)
                ->first();

            if (!empty($bookingHeader)) {
                $homeServiceFee = intval($bookingHeader->payment_extra_charge);
                $pointUsed = intval($bookingHeader->point_used);
            }
        }

        $partners = [];
        $member = [
            'id'        => 0,
            'code'      => '',
            'name'      => '',
            'phone'     => '',
            'isMember'  => false,
        ];
        foreach ($details as $dt) {
            if ($dt->package_id != null) {
                //got packages
                $tdps = TransactionDetailPartner::where('transaction_detail_id', $dt->id)->get();
                foreach ($tdps as $tdp) {
                    $partners[] = [
                        'tdpId'         => $tdp->id,
                        'partnerId'     => $tdp->partner_id,
                        'number'        => $tdp->partner->number . ' ' . $tdp->partner->alias,
                    ];
                }
                if ($dt->member_id != null) {
                    $dbMember = Member::where('id', $dt->member_id)->first();
                    $member['id'] = $dbMember->id;
                    $member['code'] = $dbMember->code;
                    $member['name'] = $dbMember->name;
                    $member['phone'] = $dbMember->phone;
                    $member['isMember'] = true;
                }
            }
        }

        $dbPayments = TransactionPayment::where('header_id', $header->id)->get();
        $user = Auth::guard('admin')->user();
        $paymentOptions = PosPaymentType::where('branch_id', $user->branch_id)
            ->where('status_id', 1)->get();

        //creating new model
        $tmpAmount = 0;
        $payments = [];
        foreach ($dbPayments as $payment) {
            $payments[] = [
                'payment_type_id'   => $payment->payment_type_id,
                'payment_type_code' => $payment->payment_type_code,
                'amount'            => $payment->amount,
                'new_amount'        => $payment->amount,
            ];
            $tmpAmount += $payment->amount;
        }
        $model['amount'] = $tmpAmount;
        //dd($payments);

        $data = [
            'header'            => $model,
            'branch'            => $header->branch,
            'details'           => $details,
            'partners'          => $partners,
            'member'            => $member,
            'room'              => $room,
            'payments'          => $payments,
            'isAutoJournal'     => $isAutoJournal,
            'homeServiceFee'    => $homeServiceFee,
            'pointUsed'         => $pointUsed,
            'payments'          => $payments,
            'paymentOptions'    => $paymentOptions,
        ];

        return view('admin.transaction.show_to_change')->with($data);
    }

    public function changePayment(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'options'   => 'required',
                'password'  => 'required',
            ], [
                'options.required'  => 'Opsi Payment harus dipilih!',
                'password.required' => 'Passowrd Wajib Diisi!',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator);
            }

            //check password
            $user = Auth::guard('admin')->user();
            $userData = AdminUser::find($user->id);
            $config = Configuration::where('branch_id', $userData->branch_id)->where('key', 'cancel_password')->first();
            if ($config->value != $request->input('password')) {
                return back()->withErrors('Password salah!');
            }

            $header = TransactionHeader::where('id', $request->input('id'))->first();
            if ($header == null) {
                return back()->withErrors('Password salah!');
            }

            //change payment
            $journalHeaderId = 0;
            $dbPayments = TransactionPayment::where('header_id', $header->id)->get();
            if ($header->is_approved == 1) {
                foreach ($dbPayments as $dbp) {
                    //delete journal first
                    $journalMapOld = JournalMap::where('branch_id', $user->branch_id)
                        ->where(function ($q) {
                            $q->where('transaction_type', 'POS_CASH')
                                ->orWhere('transaction_type', 'POS_NON_CASH');
                        })
                        ->where('room_type_id', 'like', '%' . $dbp->payment_type_id . '%')
                        ->first();

                    $dbJournal = JournalDetail::where('reference_code', $header->code)
                        ->where('account_id', $journalMapOld->coa_id)
                        ->first();

                    $journalHeaderId = $dbJournal->header_id;

                    $dbJournal->delete();
                }
            }

            $oldPayments = TransactionPayment::where('header_id', $header->id)->get();
            foreach ($oldPayments as $py) {
                $py->delete();
            }

            //save new payment & journal
            $choosenOptions = $request->input('options');
            $choosenAmounts = $request->input('amounts');
            $idx = 0;
            $jHeader = JournalHeader::where('id', $journalHeaderId)->first();
            foreach ($choosenOptions as $option) {
                //payment
                $choosenOption = PosPaymentType::where('id', $option)->first();
                TransactionPayment::create([
                    'header_id'         => $header->id,
                    'payment_type_id'   => $choosenOption->id,
                    'payment_type_code' => $choosenOption->code,
                    'amount'            => $choosenAmounts[$idx],
                    'created_by'        => $user->id,
                    'updated_by'        => $user->id,
                    'branch_id'         => $user->branch_id,
                ]);

                if ($header->is_approved == 1) {
                    //journal
                    $journalMapNew = JournalMap::where('branch_id', $user->branch_id)
                        ->where(function ($q) {
                            $q->where('transaction_type', 'POS_CASH')
                                ->orWhere('transaction_type', 'POS_NON_CASH');
                        })
                        ->where('room_type_id', 'like', '%' . $choosenOption->id . '%')
                        ->first();

                    if ($journalMapNew != null) {
                        //insert journal
                        $coa = Coa::where('id', $journalMapNew->coa_id)->first();
                        if ($coa != null) {
                            //insert journal detail
                            $type = 'KREDIT';
                            if ($journalMapNew->journal_type == 'DEBET') {
                                $type = 'DEBET';
                            }
                            $notes = 'Payment Transaksi ' . $header->code;
                            JournalUtilities::insertRecord($jHeader, $userData->branch_id, $coa, $type, $choosenAmounts[$idx], $notes, $header->code);
                        }
                    }
                }

                $idx++;
            }

            Session::flash('success', 'Sukses mengubah tipe pembayaran Transaksi ' . $header->code . '!');
            return Redirect::route('admin.transaction.index');
        } catch (\Exception $ex) {
            dd($ex);
            Log::channel('pos')->error('Admin/TransactionController - changePayment error EX: ' . $ex);
            Session::flash('error', 'Terjadi kesalahan teknis');
            return back();
        }
    }

    public function create()
    {
        try {
            $admin = Auth::guard('admin')->user();
            $todayDate = Carbon::today('Asia/Jakarta')->format('d-m-Y');
            $coupons = DB::table('coupons')
                // ->where('branch_id', '=', $admin->branch_id)
                ->where('status_id', '=', 1)
                ->get();

            $firstPayment = DB::table('pos_payment_types')
                ->where('branch_id', '=', $admin->branch_id)
                ->first();

            $commissionPartners = DB::table('partners')
                ->where('branch_id', '=', $admin->branch_id)
                ->where('is_present', '=', 1)
                ->orderBy('name')
                ->get();

            $data = [
                'todayDate' => $todayDate,
                'branchId' => $admin->branch_id,
                'admin' => $admin,
                'coupons' => $coupons,
                'firstPaymentId' => $firstPayment->id,
                'commissionPartners' => $commissionPartners
            ];

            return view('admin.transaction.create')->with($data);
        } catch (\Exception $ex) {
            Log::channel('pos')->error('Admin/TransactionController - create error EX: ' . $ex);
            Session::flash('error', 'Terjadi kesalahan teknis');
            return redirect()->back();
        }
    }

    public function store(Request $request)
    {
        try {
            //            Log::channel('pos')->info(json_encode($request->post()));
            //            return response()->json(collect([
            //                'code' => 500,
            //                'error' => 'exception',
            //            ]), 200);

            $admin = Auth::guard('admin')->user();
            $details = $request->input('details');

            // Validate manual discount
            $isUsingInputDiscount = false;
            $inputDiscountFlat = 0;
            $inputDiscountPercentage = 0;
            if ($request->filled('discount_amount')) {
                $inputDiscountFlat = Utilities::toFloat($request->input('discount_amount'));
                if ($inputDiscountFlat > 0) {
                    $isUsingInputDiscount = true;
                }
            }

            if ($request->filled('discount_percentage')) {
                $inputDiscountPercentage = Utilities::toFloat($request->input('discount_percentage'));
                if ($inputDiscountPercentage > 0) {
                    $isUsingInputDiscount = true;
                }
            }

            $voucherCode = null;
            $voucherDiscType = 'percentage';
            $voucherDiscPercentage = 0;
            $voucherDiscAmount = 0;
            $isVoucherItemOnly = false;

            $appUserPhone = $request->filled('user_phone') ? trim($request->input('user_phone')) : null;
            $appUser = null;

            // Validate app user phone
            if (!empty($appUserPhone)) {
                $appUser = User::where('phone', '=', $appUserPhone)->first();
                if (empty($appUser)) {
                    return response()->json(collect([
                        'code' => 400,
                        'error' => 'validation_failed',
                        'result' => ['User Aplikasi tidak ditemukan!']
                    ]), 200);
                }
            }

            $isUsingInputVoucher = false;
            $inputVoucherCode = '';
            if ($request->filled('voucher_code')) {
                $inputVoucherCode = $request->input('voucher_code');
                $voucherDetail = CouponList::with(['coupon'])
                    ->where('code', '=', $inputVoucherCode)
                    ->first();

                if (!empty($voucherDetail)) {
                    $isUsingInputVoucher = true;

                    $voucherCode = $voucherDetail->coupon->code;
                    $voucherDiscAmount = $voucherDetail->coupon->discount_amount;
                    $voucherDiscPercentage = $voucherDetail->coupon->discount_percentage;
                    $isVoucherItemOnly = $voucherDetail->coupon->is_item_only;

                    if ($voucherDiscAmount > 0) {
                        $voucherDiscType = 'amount';
                    }

                    // Validate foc voucher
                    if ($voucherDetail->coupon->is_foc) {
                        return response()->json(collect([
                            'code' => 400,
                            'error' => 'bad_request',
                        ]), 200);
                    }

                    // Check unlimited usage
                    if (!$voucherDetail->coupon->is_unlimited) {
                        // Update is_used
                        DB::table('coupon_lists')
                            ->where('id', '=', $voucherDetail->id)
                            ->update([
                                'is_used' => 1
                            ]);
                    }

                    // Validate birthday promo
                    // if(!empty($voucherDetail->user_id) && !empty($appUser)){
                    //     if($voucherDetail->user_id !== $appUser->id){
                    //         return response()->json(collect([
                    //             'code' => 400,
                    //             'error' => 'validation_failed',
                    //             'result' => ['Voucher Promo Ultah bukan milik user aplikasi!']
                    //         ]), 200);
                    //     }
                    // }

                    // Validate treatment links
                    // $isValidStepOne = false;
                    // foreach($details as $detail){
                    //     if($detail['type'] === 'package' &&
                    //         DB::table('coupon_package_links')
                    //             ->where('coupon_id', '=', $voucherDetail->coupon_id)
                    //             ->where('package_id', '=', $detail['package_id'])
                    //             ->exists()){
                    //         $isValidStepOne = true;
                    //     }
                    // }

                    // if($isValidStepOne){
                    //     $detailPackageCount = 0;
                    //     foreach($details as $detail){
                    //         if($detail['type'] === 'package') $detailPackageCount++;
                    //     }

                    //     if($detailPackageCount > 1){
                    // return response()->json(collect([
                    //     'code' => 400,
                    //     'error' => 'validation_failed',
                    //     'result' => ['Penggunaan Voucher Gagal!']
                    // ]), 200);
                    //     }
                    // }
                }
            }

            $locker = DB::table('lockers')
                ->where('branch_id', '=', $admin->branch_id)
                ->first();

            $foc = $request->input('foc');

            $header = new TransactionHeader();
            $header->locker_id = $locker->id;
            $header->code = AutoNumberGenerator::generateAutonumberTrx('POS');
            $header->branch_id = $admin->branch_id;
            $header->status_id = 9;
            $header->start_time = Carbon::now('Asia/Jakarta')->toDateTimeString();

            $employee = DB::table('employees')
                ->where('admin_user_id', '=', $admin->id)
                ->first();
            if (!empty($employee)) {
                $header->employee_id = $employee->id;
            }

            $header->return_value = Utilities::toFloat($request->input('inputChangeTotal'));

            if ($voucherDiscPercentage > 99) {
                $header->foc_type = 'FOC_PACKAGE';
                $foc = 'FOC_PACKAGE';
            } else {
                $header->foc_type = $foc ?? 'NO';
            }

            $header->foc_number = $request->filled('foc_name') ? trim($request->input('foc_name')) : null;
            $header->app_coupon_code = $isUsingInputVoucher ? $inputVoucherCode : null;

            if (!empty($appUser)) {
                $header->is_app_user_phone_input = true;
                $header->app_user_id = $appUser->id;
                $header->app_user_phone = $appUser->phone;
                $header->app_user_name = $appUser->name;
            }

            $header->created_by = $admin->id;
            $header->updated_by = $admin->id;
            $header->save();

            $savedHeaderId = $header->id;

            $configWarehouse = DB::table('configurations')
                ->where('key', '=', 'selling_warehouse_id')
                ->where('branch_id', '=', $admin->branch_id)
                ->first();
            $warehouseId = intval($configWarehouse->value_int);

            $totalDiscount = 0;
            $grandTotal = 0;
            $tmpTotal = 0;

            $isUsingInputVoucher = false;
            $inputVoucherCode = '';
            if ($request->filled('voucher_code')) {
                $inputVoucherCode = $request->input('voucher_code');
                $voucherDetail = CouponList::with(['coupon'])
                    ->where('code', '=', $inputVoucherCode)
                    ->first();

                if (!empty($voucherDetail)) {
                    $voucherCode = $voucherDetail->coupon->code;

                    if (!$voucherDetail->coupon->is_foc) {
                        $header->app_coupon_code = $inputVoucherCode;
                        $header->save();

                        // Check unlimited usage
                        if (!$voucherDetail->coupon->is_unlimited) {
                            // Update is_used
                            DB::table('coupon_lists')
                                ->where('id', '=', $voucherDetail->id)
                                ->update([
                                    'is_used' => 1
                                ]);
                        }
                    }
                }
            }

            // Flag for birthday promo usage
            $isBirthdayPromo = false;

            // For item only discount
            $itemDiscountAmount = $isVoucherItemOnly ? $voucherDiscAmount : 0;

            // Read and process details
            $isPromoEligible = false;
            foreach ($details as $detail) {
                $isPackageBirthdayPromo = false;

                $newDetail = new TransactionDetail();
                $newDetail->header_id = $header->id;
                if ($detail['customer_name'] == '') {
                    $newDetail->customer_name = AutoNumberGenerator::generateAutonumberTrx('CUS');
                } else {
                    $newDetail->customer_name = $detail['customer_name'];
                }

                $discount = 0;
                if ($detail['type'] === 'package') {
                    $package = DB::table('packages')
                        ->where('id', '=', $detail['package_id'])
                        ->first();
                    $tmpTotal += $package->price;

                    // if($package->duration > 149){
                    //     $isPromoEligible = true;
                    // }

                    $newDetail->package_id = $package->id;
                    $newDetail->start_time = Carbon::now('Asia/Jakarta')->toDateTimeString();
                    $newDetail->qty = 1;
                    $newDetail->price = $package->price;
                    $newDetail->room_id = $detail['room_id'];
                    $newDetail->status_id = 9;

                    // Do not process birthday promo treatment to customer monitor
                    if (isset($detail['is_birthday_promo'])) {
                        if (!$isBirthdayPromo) {
                            $isBirthdayPromo = $detail['is_birthday_promo'] == 1;
                        }

                        $isPackageBirthdayPromo = $detail['is_birthday_promo'] == 1;
                        $newDetail->status_id = $detail['is_birthday_promo'] == 1 ? 9 : 8;
                        $newDetail->price = $package->price;
                    } else {
                        $newDetail->status_id = 8;
                    }

                    if ($foc === 'FOC' || $foc === 'FOC_PACKAGE') {
                        // $discount = $package->price;
                        // $totalDiscount += $discount;

                        // $newDetail->discount_amount = $discount;
                        // $newDetail->discount_percentage = 100;

                        $discount = 0;
                        $totalDiscount = 0;
                        $newDetail->discount_amount = $discount;
                        $newDetail->discount_percentage = 0;
                    } else {
                        if (!$isUsingInputVoucher && !$isPackageBirthdayPromo) {
                            if ($detail['coupon_id'] != 0) {
                                $coupon = DB::table('coupons')
                                    ->where('id', '=', $detail['coupon_id'])
                                    ->first();

                                $voucherCode = $coupon->code;

                                if ($coupon->discount_percentage > 0) {
                                    $voucherValue = $coupon->discount_percentage;

                                    $discount = $newDetail->price * (floatval($coupon->discount_percentage) / 100);
                                    $totalDiscount += $discount;

                                    $newDetail->discount_amount = $discount;
                                    $newDetail->discount_percentage = $coupon->discount_percentage;
                                } elseif ($coupon->discount_amount > 0) {
                                    $voucherValue = $coupon->discount_amount;

                                    $discount = $coupon->discount_amount;
                                    $totalDiscount += $discount;
                                    $newDetail->discount_amount = $discount;
                                }
                            }
                        } else {
                            if ($voucherDiscType === 'percentage' && $voucherDiscPercentage > 0) {
                                $discount = $newDetail->price * (floatval($voucherDiscPercentage) / 100);

                                $newDetail->discount_amount = $discount;
                                $newDetail->discount_percentage = $voucherDiscPercentage;
                            }
                        }
                    }

                    // Update room status
                    DB::table('rooms')
                        ->where('id', '=', $detail['room_id'])
                        ->update([
                            'status_id' => 6
                        ]);
                } elseif ($detail['type'] === 'item') {
                    $item = DB::table('items')
                        ->where('id', '=', $detail['item_id'])
                        ->first();
                    $tmpTotal += ($detail['qty'] * $item->price);

                    $newDetail->item_id = $item->id;
                    $newDetail->qty = $detail['qty'];
                    $newDetail->price = $item->price;
                    $newDetail->status_id = 3;

                    // Check addon
                    if (isset($detail['addon_partner_id'])) {
                        $addonPartnerId = intval($detail['addon_partner_id']);
                        if ($addonPartnerId > 0) {
                            $partner = Partner::find($addonPartnerId);
                            $newDetail->partner_id = $addonPartnerId;
                            $newDetail->partner_level = $partner->type;
                        }
                    }

                    if ($foc === 'FOC' || $foc === 'FOC_ITEM') {
                        // $discount = $newDetail->qty * $newDetail->price;

                        // $newDetail->discount_amount = $discount;
                        // $newDetail->discount_percentage = 100;

                        $discount = 0;
                        $newDetail->discount_amount = $discount;
                        $newDetail->discount_percentage = 0;
                    }

                    if ($item->is_unlimited_stock < 1) {
                        // Decrease stock
                        Utilities::updateStock(
                            $admin->branch_id,
                            'sub',
                            $newDetail->qty,
                            $newDetail->item_id,
                            $warehouseId,
                            $header->code,
                            'Sales untuk ' . $header->code
                        );
                    }

                    // Process item only discount
                    if ($itemDiscountAmount > 0) {
                        $tmpTotal += $newDetail->qty * $newDetail->price;

                        if ($itemDiscountAmount - $tmpTotal > 0) {
                            $discount = $tmpTotal;
                            $newDetail->discount_amount = $discount;

                            $itemDiscountAmount -= $discount;
                        } elseif ($itemDiscountAmount - $tmpTotal <= 0) {
                            $discount = $tmpTotal - $itemDiscountAmount;
                            $newDetail->discount_amount = $discount;

                            $itemDiscountAmount -= $discount;
                        }
                    }
                }

                $newDetail->subtotal = ($newDetail->qty * $newDetail->price) - $discount;
                $newDetail->save();

                $grandTotal += $newDetail->subtotal;

                if ($detail['type'] === 'package') {
                    $newDetailPartner = new TransactionDetailPartner();
                    $newDetailPartner->transaction_detail_id = $newDetail->id;
                    $newDetailPartner->partner_id = $detail['partner_id'];
                    $newDetailPartner->is_main = 1;
                    $newDetailPartner->save();

                    // Update partner status
                    DB::table('partners')
                        ->where('id', '=', $detail['partner_id'])
                        ->update([
                            'status_id' => 6
                        ]);
                }
            }

            if (!empty($voucherCode)) {
                $header->voucher_code = $voucherCode;
                $header->voucher_value = $voucherValue;
            }

            // If there's FOC, do not use discount on header for journaling purpose
            if ($header->foc_type == 'NO') {
                $header->discount = $totalDiscount;
                $header->discount_total = $totalDiscount;
            } else {
                $header->discount = 0;
                $header->discount_total = 0;
            }

            $header->amount = $grandTotal;
            $header->grand_total = $grandTotal;

            // If discount is manually inputted
            if ($isUsingInputDiscount) {
                $inputDisc = 0;
                if ($inputDiscountFlat > 1) {
                    $inputDisc = $inputDiscountFlat;
                } elseif ($inputDiscountPercentage > 1) {
                    $inputDisc = $tmpTotal * (floatval($inputDiscountPercentage) / 100);
                }

                $header->discount = $inputDisc;
                $header->discount_total = $inputDisc;
                $header->amount = $tmpTotal - $inputDisc;
                $header->grand_total = $tmpTotal - $inputDisc;
            }

            $pointBonus = 0;
            if (!empty($appUser) && (empty($foc) || $foc === 'NO' || $foc === 'no')) {
                // Check user membership
                $membership = DB::table('app_membership_details')
                    ->where('membership_id', '=', $appUser->membership_id)
                    ->first();

                // Calculate bonus point
                $tmpPoint = floor($header->grand_total / $membership->bonus_point_order_amount);
                $pointBonus = $tmpPoint * $membership->bonus_point_value * 1000;

                $header->app_bonus_point = $pointBonus;

                Utilities::updateUserPointBalance(
                    $appUser->id,
                    'add',
                    $pointBonus
                );
                Utilities::saveUserPointHistory(
                    $appUser->id,
                    $header->branch_id,
                    'transaction',
                    'add',
                    $pointBonus,
                    $header->code,
                    '',
                    null
                );
            }
            $header->save();

            $payments = $request->input('payments');
            foreach ($payments as $payment) {
                $newPayment = new TransactionPayment();
                $newPayment->branch_id = $admin->branch_id;
                $newPayment->header_id = $savedHeaderId;
                $newPayment->payment_type_id = $payment['payment_id'];

                $paymentObj = DB::table('pos_payment_types')
                    ->where('id', '=', $payment['payment_id'])
                    ->first();

                $newPayment->payment_type_code = $paymentObj->code;

                $payAmount = Utilities::toFloat($payment['amount']);
                $newPayment->amount = $payAmount;
                $newPayment->save();
            }

            //generate coupon list with exp date 30 day after transaction
            //only do this if the user didn't use any voucher or coupon
            // if(empty($voucherCode) && $isPromoEligible){
            //     $coupon = Coupon::where('code', 'promo10')
            //         // ->where('branch_id', '=', $admin->branch_id)
            //         ->first();

            //     if($coupon != null){
            //         $nVoucher = new CouponList();
            //         $nVoucher->branch_id = $admin->branch_id;
            //         $nVoucher->coupon_id = $coupon->id;
            //         $nVoucher->exp_date = Carbon::today('Asia/Jakarta')->addDays(30)->toDateTimeString();
            //         $nVoucher->created_by = 54;
            //         $nVoucher->updated_by = 54;
            //         //$nVoucher->code = AutoNumberGenerator::generateCouponCode($coupon->id, $coupon->list_code, $branch->voucher_code);
            //         $voucherNotFound = true;

            //         $nVoucher->code = strtoupper(Utilities::generateRandomStr(8));
            //         while($voucherNotFound){
            //             $tmpVoucher = CouponList::where('code', $nVoucher->code)->first();
            //             if($tmpVoucher == null){
            //                 //$nVoucher->code = strtoupper(Utilities::generateRandomStr(8));
            //                 $voucherNotFound = false;
            //             }
            //             else{
            //                 $nVoucher->code = strtoupper(Utilities::generateRandomStr(8));
            //             }
            //         }

            //         $nVoucher->save();

            //         DB::table('transaction_headers')
            //             ->where('id', '=', $savedHeaderId)
            //             ->update([
            //                 'extra_voucher_code' => $nVoucher->code
            //             ]);
            //     }
            // }

            // Check for birthday promo usage
            if ($isBirthdayPromo) {
                $newPromoUsage = new AppBirthdayPromoUsage();
                $newPromoUsage->user_id = $appUser->id;
                $newPromoUsage->user_phone = $appUser->phone;
                $newPromoUsage->user_name = $appUser->name;
                $newPromoUsage->membership = DB::table('app_memberships')->where('id', '=', $appUser->membership_id)->value('name');
                $newPromoUsage->transaction_id = $savedHeaderId;
                $newPromoUsage->year = Carbon::now('Asia/Jakarta')->year;
                $newPromoUsage->birthday_discount_percentage = 0;
                $newPromoUsage->status_id = 1;
                $newPromoUsage->save();
            }

            //count google review
            if ($request->filled('google_review_amount')) {
                $googleReviewAmount = Utilities::toFloat($request->input('google_review_amount'));
                $header->google_review_amount = $googleReviewAmount;
                $header->discount += $googleReviewAmount;
                $header->grand_total -= $googleReviewAmount;
                $header->save();
            }

            //Inserting Journal and Approve the header
            $autoJournalConfig = Configuration::where('key', 'auto_journal')->where('branch_id', $header->branch_id)->first();
            if ($autoJournalConfig->value_int == 1) {
                $header->is_approved = 1;
                $header->approved_at = Carbon::now('Asia/Jakarta');
                $header->approved_by = 1;
                $header->save();

                JournalUtilities::approveProcess($header, $admin);
            }

            return response()->json(collect([
                'code' => 200,
                'error' => 'none',
                'result' => [
                    'saved_trx_id' => $savedHeaderId
                ]
            ]), 200);
        } catch (\Exception $ex) {
            Log::channel('pos')->error('Admin/TransactionController - store ' . $ex);
            //            Log::error('TransactionController - store ' . $ex);
            return response()->json(collect([
                'code' => 500,
                'error' => $ex->getMessage(),
                'result' => 'exception',
            ]), 500);
        }
    }

    public function storeDev(Request $request)
    {
        try {
            $admin = Auth::guard('admin')->user();

            $locker = DB::table('lockers')
                ->where('branch_id', '=', $admin->branch_id)
                ->first();

            $foc = $request->input('foc');
            Log::channel('pos')->info($request->input('voucher_code'));

            $header = new TransactionHeader();
            $header->locker_id = $locker->id;
            $header->code = AutoNumberGenerator::generateAutonumberTrx('POS');
            $header->branch_id = $admin->branch_id;
            $header->status_id = 9;
            $header->start_time = Carbon::now('Asia/Jakarta')->toDateTimeString();

            $employee = DB::table('employees')
                ->where('admin_user_id', '=', $admin->id)
                ->first();
            if (!empty($employee)) {
                $header->employee_id = $employee->id;
            }

            $header->return_value = Utilities::toFloat($request->input('inputChangeTotal'));
            $header->foc_type = $foc ?? null;
            $header->foc_number = $request->filled('foc_name') ? trim($request->input('foc_name')) : null;
            $header->created_by = $admin->id;
            $header->updated_by = $admin->id;
            $header->save();

            $configWarehouse = DB::table('configurations')
                ->where('key', '=', 'selling_warehouse_id')
                ->where('branch_id', '=', $admin->branch_id)
                ->first();
            $warehouseId = intval($configWarehouse->value_int);

            $totalDiscount = 0;
            $grandTotal = 0;
            $details = $request->input('details');

            //check and get voucher data
            $voucher = null;
            if ($request->input('voucher_code') != '-') {
                $voucher = CouponList::with('coupon')->where('code', $request->input('voucher_code'))->first();
            }

            foreach ($details as $detail) {
                $newDetail = new TransactionDetail();
                $newDetail->header_id = $header->id;
                if ($detail['customer_name'] == '') {
                    $newDetail->customer_name = AutoNumberGenerator::generateAutonumberTrx('CUS');
                } else {
                    $newDetail->customer_name = $detail['customer_name'];
                }

                $discount = 0;
                if ($detail['type'] === 'package') {
                    $package = DB::table('packages')
                        ->where('id', '=', $detail['package_id'])
                        ->first();

                    $newDetail->package_id = $package->id;
                    $newDetail->start_time = Carbon::now('Asia/Jakarta')->toDateTimeString();
                    $newDetail->qty = 1;
                    $newDetail->price = $package->price;
                    $newDetail->room_id = $detail['room_id'];
                    $newDetail->status_id = 8;

                    if ($foc === 'FOC' || $foc === 'FOC_PACKAGE') {
                        $discount = $package->price;

                        $newDetail->discount_amount = $discount;
                        $newDetail->discount_percentage = 100;
                    } else {
                        if ($voucher != null) {
                            if ($voucher->coupon->discount_percentage > 0) {
                                //it means percentage discount for package
                                $discount = $newDetail->price * (floatval($voucher->coupon->discount_percentage) / 100);
                                $totalDiscount += $discount;

                                $newDetail->discount_amount = $discount;
                                $newDetail->discount_percentage = $voucher->coupon->discount_percentage;
                            }
                        }
                    }

                    // Update room status
                    DB::table('rooms')
                        ->where('id', '=', $detail['room_id'])
                        ->update([
                            'status_id' => 6
                        ]);
                } elseif ($detail['type'] === 'item') {
                    $item = DB::table('items')
                        ->where('id', '=', $detail['item_id'])
                        ->first();

                    $newDetail->item_id = $item->id;
                    $newDetail->qty = $detail['qty'];
                    $newDetail->price = $item->price;
                    $newDetail->status_id = 3;

                    if ($foc === 'FOC' || $foc === 'FOC_ITEM') {
                        $discount = $newDetail->qty * $newDetail->price;

                        $newDetail->discount_amount = $discount;
                        $newDetail->discount_percentage = 100;
                    }

                    if ($item->is_unlimited_stock < 1) {
                        // Decrease stock
                        Utilities::updateStock(
                            $admin->branch_id,
                            'sub',
                            $newDetail->qty,
                            $newDetail->item_id,
                            $warehouseId,
                            $header->code,
                            'Sales untuk ' . $header->code
                        );
                    }
                }

                $newDetail->subtotal = ($newDetail->qty * $newDetail->price) - $discount;
                $newDetail->save();

                $grandTotal += $newDetail->subtotal;

                if ($detail['type'] === 'package') {
                    $newDetailPartner = new TransactionDetailPartner();
                    $newDetailPartner->transaction_detail_id = $newDetail->id;
                    $newDetailPartner->partner_id = $detail['partner_id'];
                    $newDetailPartner->is_main = 1;
                    $newDetailPartner->save();

                    // Update partner status
                    DB::table('partners')
                        ->where('id', '=', $detail['partner_id'])
                        ->update([
                            'status_id' => 6
                        ]);
                }
            }

            if ($voucher != null) {
                if ($voucher->coupon->discount_amount > 0) {
                    //mean amount discount
                    $totalDiscount = $voucher->coupon->discount_amount;
                    $grandTotal -= $totalDiscount;
                }

                $header->voucher_code = $voucher->code;
                $header->voucher_value = $totalDiscount;

                //update voucher usage
                $voucher->is_used = 1;
                $voucher->save();
            }

            $header->discount = $totalDiscount;
            $header->discount_total = $totalDiscount;
            $header->amount = $grandTotal;
            //            $header->total_after_commission = $grandTotal - $totalCommission;
            $header->grand_total = $grandTotal;
            $header->save();

            $payments = $request->input('payments');
            foreach ($payments as $payment) {
                $newPayment = new TransactionPayment();
                $newPayment->branch_id = $admin->branch_id;
                $newPayment->header_id = $header->id;
                $newPayment->payment_type_id = $payment['payment_id'];

                $paymentObj = DB::table('pos_payment_types')
                    ->where('id', '=', $payment['payment_id'])
                    ->first();

                $newPayment->payment_type_code = $paymentObj->code;
                $newPayment->amount = Utilities::toFloat($payment['amount']);
                $newPayment->save();
            }

            return response()->json(collect([
                'code' => 200,
                'error' => 'none',
                'result' => [
                    'saved_trx_id' => $header->id
                ]
            ]), 200);
        } catch (\Exception $ex) {
            Log::channel('pos')->error('Admin/TransactionController - store ' . $ex);
            //            Log::error('TransactionController - store ' . $ex);
            return response()->json(collect([
                'code' => 500,
                'error' => $ex->getMessage(),
                'result' => 'exception',
            ]), 500);
        }
    }

    public function approveJournal(Request $request)
    {
        try {
            if (!$request->filled('approved_transaction_id')) {
                Session::flash('error', 'BAD REQUEST!');
                return redirect()->back();
            }

            $trxId = $request->input('approved_transaction_id');
            $header = TransactionHeader::with(['transaction_details', 'transaction_payments'])->find($trxId);
            if (empty($header)) {
                Session::flash('error', 'BAD REQUEST!');
                return redirect()->back();
            }

            // Check approved
            if ($header->is_approved) {
                // Check existing journal
                if (!DB::table('journal_headers')
                    ->where('branch_id', '=', $header->branch_id)
                    ->where('notes', '=', 'Journal Transaksi ' . $header->code)
                    ->exists()) {
                    $header->is_approved = false;
                    $header->save();
                }
            } else {
                // Check existing journal
                if (DB::table('journal_headers')
                    ->where('branch_id', '=', $header->branch_id)
                    ->where('notes', '=', 'Journal Transaksi ' . $header->code)
                    ->exists()
                ) {
                    $header->is_approved = true;
                    $header->approved_at = Carbon::now('Asia/Jakarta')->toDateTimeString();
                    $header->save();

                    Session::flash('error', 'Transaksi sudah di-approve!');
                    return redirect()->back();
                }
            }

            $branch = DB::table('branches')->find($header->branch_id);
            $branchId = $branch->id;

            $admin = Auth::guard('admin')->user();

            // Create journal header
            $jHeader = new JournalHeader();
            $jHeader->transaction_code = AutoNumberGenerator::generateAutonumberTrx('JN');
            $jHeader->transaction_type = 'POS';
            $jHeader->transaction_date = $header->created_at;
            $jHeader->created_by = $admin->id;
            $jHeader->branch_id = $header->branch_id;
            $jHeader->notes = 'Journal Transaksi ' . $header->code;
            $jHeader->reference_id = $header->id;
            $jHeader->status_id = 1;
            $jHeader->save();

            $isApplication = false;
            $dbPayments = TransactionPayment::where('header_id', $header->id)->get();

            $totalTrx = 0;
            $totalPackageTrx = 0;
            $allDetails = TransactionDetail::where('header_id', $header->id)->get();
            foreach ($allDetails as $dt) {
                if ($dt->package_id != null) {
                    $totalTrx += (($dt->price * $dt->qty) + $dt->additional_person_amount + $dt->additional_partner_amount + $dt->extension_amount);
                    $totalPackageTrx += (($dt->price * $dt->qty) + $dt->additional_person_amount + $dt->additional_partner_amount + $dt->extension_amount);
                } else {
                    $totalTrx += ($dt->price * $dt->qty);
                }
            }

            // Check if transaction from app
            $bookingHeader = null;
            if ($branch->is_oura == 1) {
                $bookingHeader = DB::table('booking_headers')
                    ->where('transaction_id', '=', $header->id)
                    ->first();
                $isApplication = !empty($bookingHeader);
            }

            $isFoc = false;
            if (strtolower($header->foc_type) == 'foc') {
                $isFoc = true;

                $journalMap = JournalMap::where('branch_id', $branchId)
                    ->where('transaction_type', 'POS_FOC')
                    ->first();
                if ($journalMap != null) {
                    $coa = Coa::where('id', $journalMap->coa_id)->first();
                    if ($coa != null) {
                        // Check if HT transaction
                        if ($isApplication && $bookingHeader->type === 'home_service') {
                            $totalTrx += $bookingHeader->payment_extra_charge;
                        }

                        //insert journal detail
                        $notes = 'Transaksi FOC ' . $header->code;
                        JournalUtilities::insertRecord($jHeader, $branchId, $coa, 'DEBET', $totalTrx, $notes, $header->code);
                    }
                }
            } else if (strtoupper($header->foc_type) == 'FOC') {
                $isFoc = true;

                $totalTrx = 0;
                $allDetails = TransactionDetail::where('header_id', $header->id)->get();
                foreach ($allDetails as $dt) {
                    $totalTrx += ($dt->price * $dt->qty);
                }
                $journalMap = JournalMap::where('branch_id', $branchId)
                    ->where('transaction_type', 'POS_FOC')
                    ->first();
                if ($journalMap != null) {
                    $coa = Coa::where('id', $journalMap->coa_id)->first();
                    if ($coa != null) {
                        // Check if HT transaction
                        if ($isApplication && $bookingHeader->type === 'home_service') {
                            $totalTrx += $bookingHeader->payment_extra_charge;
                        }

                        //insert journal detail
                        $notes = 'Transaksi FOC ' . $header->code;
                        JournalUtilities::insertRecord($jHeader, $branchId, $coa, 'DEBET', $totalTrx, $notes, $header->code);
                    }
                }
            } else if (strtolower($header->foc_type) == 'foc_package') {
                $journalMap = JournalMap::where('branch_id', $branchId)
                    ->where('transaction_type', 'POS_FOC')
                    ->first();
                if ($journalMap != null) {
                    $coa = Coa::where('id', $journalMap->coa_id)->first();
                    if ($coa != null) {
                        // Check if HT transaction
                        if ($isApplication && $bookingHeader->type === 'home_service') {
                            $totalPackageTrx += $bookingHeader->payment_extra_charge;
                        }

                        //insert journal detail
                        $notes = 'Transaksi FOC ' . $header->code;
                        JournalUtilities::insertRecord($jHeader, $branchId, $coa, 'DEBET', $totalPackageTrx, $notes, $header->code);
                    }
                }
            }

            if (!$isFoc) {
                foreach ($dbPayments as $dbPayment) {
                    if ($branch->is_oura === 1) {
                        if (!empty($bookingHeader)) {
                            if ($dbPayment->amount > 0) {
                                $bcaPaymentCoa = Coa::where('branch_id', '=', $branch->id)
                                    ->where('code', '=', '1-10004')
                                    ->first();

                                $notes = 'Payment Transaksi Online ' . $header->code;
                                JournalUtilities::insertRecord(
                                    $jHeader,
                                    $header->branch_id,
                                    $bcaPaymentCoa,
                                    'DEBET',
                                    $header->grand_total,
                                    $notes,
                                    $header->code
                                );
                                continue;
                            }
                        }
                    }

                    if ($dbPayment->amount > 0) {
                        $journalMap = JournalMap::where('branch_id', $branchId)
                            ->where(function ($q) {
                                $q->where('transaction_type', 'POS_CASH')
                                    ->orWhere('transaction_type', 'POS_NON_CASH');
                            })
                            ->where('room_type_id', 'like', '%' . $dbPayment->payment_type_id . '%')
                            ->first();

                        if ($journalMap != null) {
                            //insert journal
                            $coa = Coa::where('id', $journalMap->coa_id)->first();
                            if ($coa != null) {
                                //insert journal detail
                                $type = 'KREDIT';
                                if ($journalMap->journal_type == 'DEBET') {
                                    $type = 'DEBET';
                                }
                                $notes = 'Payment Transaksi ' . $header->code;
                                $tmpAmount = $dbPayment->amount;
                                if ($dbPayment->pos_payment_type->code == 'CASH' && $header->return_value > 0) {
                                    $tmpAmount -= $header->return_value;
                                }
                                JournalUtilities::insertRecord($jHeader, $branchId, $coa, $type, $tmpAmount, $notes, $header->code);
                            }
                        }
                    }
                }

                if ($header->app_bonus_point > 0) {
                    $coa = Coa::where('branch_id', '=', $branchId)
                        ->where('name', '=', 'DISKON POINT MEMBER')
                        ->first();

                    JournalUtilities::insertRecord(
                        $jHeader,
                        $branchId,
                        $coa,
                        'DEBET',
                        $header->app_bonus_point,
                        'Point dari Transaksi ' . $header->code,
                        $header->code
                    );

                    $coa = Coa::where('branch_id', '=', $branchId)
                        ->where('name', '=', 'HUTANG POINT MEMBER')
                        ->first();

                    JournalUtilities::insertRecord(
                        $jHeader,
                        $branchId,
                        $coa,
                        'KREDIT',
                        $header->app_bonus_point,
                        'Point dari Transaksi ' . $header->code,
                        $header->code
                    );
                }
            }


            //Package Journal
            $tmpDtPackage = TransactionDetail::where('header_id', $header->id)->whereNotNull('package_id')->get();
            $appFee = 0;
            foreach ($tmpDtPackage as $tmpPackage) {
                $tmpPackageDetail = Package::where('id', $tmpPackage->package_id)->first();
                if ($branchId == 1) {
                    //manually add the package shiets
                    if ($tmpPackageDetail->id == 116 || $tmpPackageDetail->id == 115) {
                        //if its Achiles
                        $packageJournal = JournalMap::where('branch_id', $branchId)
                            ->where('transaction_type', 'POS_PENDAPATAN')
                            ->where('category_2_id', $tmpPackageDetail->id)
                            ->first();
                    } else {
                        $packageJournal = JournalMap::where('branch_id', $branchId)
                            ->where('transaction_type', 'POS_PENDAPATAN')
                            ->where('room_type_id', $tmpPackageDetail->room_type_id)
                            ->first();
                    }
                } else if ($branch->is_oura == 1) {
                    $packageJournal = JournalMap::where('branch_id', $branchId)
                        ->where('transaction_type', 'POS_PENDAPATAN')
                        ->where('coa_name', 'Pendapatan')
                        ->first();


                    if ($isApplication) {
                        $appFee += 25000;
                    }
                } else {
                    $packageJournal = JournalMap::where('branch_id', $branchId)
                        ->where('transaction_type', 'POS_PENDAPATAN')
                        ->where('coa_name', 'Pendapatan Massage')
                        ->first();
                }

                if ($packageJournal != null) {
                    $coa = Coa::where('id', $packageJournal->coa_id)->first();
                    if ($coa != null) {
                        $value = ($tmpPackage->price * $tmpPackage->qty) + $tmpPackage->additional_partner_amount
                            + $tmpPackage->additional_person_amount + $tmpPackage->extension_amount;

                        $notes = 'Pendapatan Transaksi Paket ' . $tmpPackageDetail->name . ' : ' . $value;
                        JournalUtilities::insertRecord($jHeader, $branchId, $coa, 'KREDIT', $value, $notes, $header->code);
                    }
                }

                if ($tmpPackageDetail->is_birthday_promo) {
                    $bPackageJournal = JournalMap::where('branch_id', $branchId)
                        ->where('transaction_type', 'POS_PENDAPATAN')
                        ->where('coa_name', 'DISKON ULTAH MEMBER')
                        ->first();

                    $bCoa = Coa::where('id', $bPackageJournal->coa_id)->first();
                    if ($bCoa != null) {
                        $value = ($tmpPackage->price * $tmpPackage->qty) + $tmpPackage->additional_partner_amount
                            + $tmpPackage->additional_person_amount + $tmpPackage->extension_amount;

                        $notes = 'Free Treatment Ultah ' . $tmpPackageDetail->name . ' : ' . $value;
                        JournalUtilities::insertRecord($jHeader, $branchId, $bCoa, 'DEBET', $value, $notes, $header->code);
                    }
                }
            }

            if ($isApplication) {
                if ($bookingHeader->type === 'home_service') {
                    $jHomeServiceFee = JournalMap::where('branch_id', $branch->id)->where('coa_code', '4-40000')->first();
                    $coaPendapatan = Coa::where('id', $jHomeServiceFee->coa_id)
                        ->first();
                    JournalUtilities::insertRecord(
                        $jHeader,
                        $header->branch_id,
                        $coaPendapatan,
                        $jHomeServiceFee->journal_type,
                        $appFee,
                        'Fee Home Service ' . $header->code,
                        $header->code
                    );
                }

                if ($bookingHeader->point_used > 0) {
                    $jPointMember = JournalMap::where('branch_id', $branch->id)
                        ->where('coa_name', 'HUTANG POINT MEMBER')
                        ->first();
                    $coaPointMember = Coa::where('id', $jPointMember->coa_id)
                        ->first();
                    JournalUtilities::insertRecord(
                        $jHeader,
                        $header->branch_id,
                        $coaPointMember,
                        'DEBET',
                        $bookingHeader->point_used,
                        'Penggunaan Point Member ' . $header->code,
                        $header->code
                    );
                }
            }

            //Facility journal
            $facilityOnly = TransactionDetail::where('header_id', $header->id)->where('is_facility_only', 1)->first();
            if ($facilityOnly != null) {
                $facJournalMap = JournalMap::where('room_type_id', 99)
                    ->where('branch_id', $branchId)->first();
                if ($facJournalMap != null) {
                    $coa = Coa::where('id', $facJournalMap->coa_id)->first();
                    if ($coa != null) {
                        $notes = 'Automate - Transaksi ' . $coa->coa_name;
                        JournalUtilities::insertRecord($jHeader, $branchId, $coa, $facJournalMap->journal_type, $facilityOnly->qty * $facilityOnly->price, $notes, $header->code);
                    }
                }
            }

            //Member Journal
            if ($header->branch_id == 1) {
                $memberOnly = TransactionDetail::where('header_id', $header->id)->where('membership_id', 1)->first();
                if ($memberOnly != null) {
                    $memberOnlyMap = JournalMap::where('transaction_type', 'POS_MEMBER')->where('branch_id', 1)->first();
                    if ($memberOnlyMap != null) {
                        $coa = Coa::where('id', $memberOnlyMap->coa_id)->first();
                        if ($coa != null) {
                            $notes = 'Automate - Transaksi ' . $coa->coa_name . ' ' . $header->code;
                            JournalUtilities::insertRecord($jHeader, $branchId, $coa, $memberOnlyMap->journal_type, $memberOnly->price * $memberOnly->qty, $notes, $header->code);
                        }
                    }
                }
            }

            //Insert Detail Item Journal
            $dtItems = TransactionDetail::where('header_id', $header->id)->where('item_id', '!=', null)->get();
            if ($branch->is_oura == 1) {
                $hppJournalMap = JournalMap::where('branch_id', $branchId)
                    ->where(function ($q) {
                        $q->where('transaction_type', 'POS_HPP')
                            ->orWhere('transaction_type', 'POS_PERSEDIAAN')
                            ->orWhere('transaction_type', 'POS_PENJUALAN')
                            ->orWhere('transaction_type', 'POS_PENDAPATAN');
                    })
                    ->get();
            } else {
                $hppJournalMap = JournalMap::where('branch_id', $branchId)
                    ->where(function ($q) {
                        $q->where('transaction_type', 'POS_HPP')
                            ->orWhere('transaction_type', 'POS_PERSEDIAAN')
                            ->orWhere('transaction_type', 'POS_PENJUALAN')
                            ->orWhere('coa_name', 'Pendapatan Meal')
                            ->orWhere('coa_name', 'Pendapatan - F&B')
                            ->orWhere('coa_name', 'Pendapatan Scrub')
                            ->orWhere('coa_name', 'Pendapatan Hot Stone')
                            ->orWhere('coa_name', 'PENDAPATAN MEAL INTERNAL')
                            ->orWhere('coa_name', 'PENDAPATAN MEAL EKSTERNAL')
                            ->orWhere('coa_name', 'Pendapatan Oil');
                    })
                    ->get();
            }
            //dd($hppJournalMap);

            $tmpJournalData = [];
            foreach ($hppJournalMap as $map) {
                $nMap = [
                    'id'            => $map->id,
                    'coa_id'        => $map->coa_id,
                    'coa_name'      => $map->coa_name,
                    'coa_code'      => $map->coa_code,
                    'journal_type'  => $map->journal_type,
                    'amount'        => 0,
                ];

                $tmpJournalData[] = $nMap;
            }
            //dd($tmpJournalData);

            $foc = strtoupper($header->foc_type);
            foreach ($dtItems as $dt) {
                //check item in DB
                if ($foc == 'FOC_ITEM' || $foc == 'FOC') {
                    //discount
                    $dt->discount_percentage = 100;
                    $dt->discount_amount = $dt->subtotal;
                }

                //insert journal item
                $item = DB::table('items')
                    ->where('id', '=', $dt->item_id)
                    //->where('name', 'not like', 'TS%')
                    ->first();
                if ($item != null) {
                    //Journal Pendapatan
                    $hppName = 'HPP - Lain-lain';
                    $categoryName = 'Pendapatan - Lain-lain';
                    $persediaanName = 'Persediaan - Lain-lain';

                    if ($branchId == 1) {
                        if (str_contains($item->name, 'KIMO') || $item->category_id == 20) {
                            $categoryName = 'Pendapatan - Kimo';
                            $hppName = 'HPP - Kimo';
                            $persediaanName = 'Persediaan - Kimo';
                        } else if (str_contains($item->name, 'OIL')) {
                            $categoryName = 'Pendapatan Oil';
                        } else if (str_contains($item->name, 'HOT STONE')) {
                            $categoryName = 'Pendapatan Hot Stone';
                        }

                        if ($item->category_id == 2) {
                            $categoryName = 'Pendapatan - F&B';
                            $hppName = 'HPP - F&B';
                            $persediaanName = 'Persediaan - F&B';
                        }

                        if ($item->category_id == 21) {
                            $categoryName = 'Pendapatan Meal';
                        }

                        if ($item->category_id == 22) {
                            $categoryName = 'PENDAPATAN MEAL EKSTERNAL';
                        }

                        if ($item->category_id == 23) {
                            $categoryName = 'PENDAPATAN MEAL INTERNAL';
                        }

                        if ($item->category_id == 25) {
                            $categoryName = 'PENJUALAN LIQUOR';
                            $hppName = 'HPP - LIQUOR';
                            $persediaanName = 'PERSEDIAAN LIQUOR';
                        }

                        if (str_contains($item->name, 'NURU')) {
                            $categoryName = 'PENJUALAN NURU';
                            $hppName = 'HPP - NURU';
                            $persediaanName = 'PERSEDIAAN NURU';
                        }

                        if (str_contains($item->name, 'ICE CREAM LICK SENSATION')) {
                            $categoryName = 'PENJUALAN ICE CREAM LICK SENSATION';
                            $hppName = 'HPP ICE CREAM LICK SENSATION';
                            $persediaanName = 'PERSEDIAAN ICE CREAM LICK SENSATION';
                        }
                    } else if ($branchId >= 6) {
                        $hppName = 'Beban Pokok Penjualan';
                        $categoryName = 'Pendapatan';
                        $persediaanName = 'Persediaan';
                        if ($item->category_id == 2) {
                            $persediaanName = 'PERSEDIAAN - F&B';
                            $categoryName = 'PENJUALAN F&B';
                            $hppName = 'BEBAN POKOK PENJUALAN - F&B';
                        }

                        if ($item->name == 'OIL THERAPY ( HOT )') {
                            $categoryName = 'PENDAPATAN OIL THERAPY (HOT)';
                            $persediaanName = 'PERSEDIAAN - OIL THERAPI (HOT)';
                            $hppName = 'BEBAN POKOK PENJUALAN - OIL THERAPI (HOT)';
                        } else if (str_contains($item->name, 'TS')) {
                            $categoryName = 'Biaya Tambahan Pelanggan';
                        }
                        //check for HPP
                        // if($item->category_id == 2){
                        //     $hppName = 'BEBAN POKOK PENJUALAN - F&B';
                        // }
                    } else {
                        $hppName = 'HPP Lain-lain';
                        $categoryName = 'Penjualan Lain-lain';
                        $persediaanName = 'Persediaan Lain-lain';

                        if (str_contains($item->name, 'KIMO')) {
                            $categoryName = 'Penjualan Kimo';
                            $hppName = 'HPP Kimo';
                            $persediaanName = 'Persediaan Kimo';
                        } else if (str_contains($item->name, 'SCRUB')) {
                            $categoryName = 'Pendapatan Scrub';
                        } else if (str_contains($item->name, 'OIL')) {
                            $categoryName = 'Pendapatan Oil';
                        } else if (str_contains($item->name, 'HOT STONE')) {
                            $categoryName = 'Pendapatan Hot Stone';
                        } else if (str_contains($item->name, 'HOTSTONE')) {
                            $categoryName = 'Pendapatan Hot Stone';
                        }

                        if ($item->category_id == 2) {
                            $categoryName = 'Penjualan F&B';
                            $hppName = 'HPP F&B';
                            $persediaanName = 'Persediaan F&B';
                        }

                        if ($item->category_id == 20) {
                            $categoryName = 'Penjualan Kimo';
                            $hppName = 'HPP Kimo';
                            $persediaanName = 'Persediaan Kimo';
                        }

                        if (str_contains($item->name, 'ADD ON')) {
                            $categoryName = 'PENJUALAN NURU';
                            $hppName = 'HPP NURU';
                            $persediaanName = 'PERSEDIAAN NURU';
                        }
                    }

                    $idx = 0;
                    foreach ($tmpJournalData as $map) {
                        // if($map['coa_name'] == $categoryName){
                        //     $map['amount'] += (($dt->price * $dt->qty) - $dt->discount_amount);
                        // }
                        // else if($map['coa_name'] == $hppName){
                        //     $map['amount'] += (($item->cost_price * $dt->qty) - $dt->discount_amount);
                        // }
                        // else if($map['coa_name'] == $persediaanName){
                        //     $map['amount'] += (($item->cost_price * $dt->qty) - $dt->discount_amount);
                        // }

                        if ($map['coa_name'] == $categoryName) {
                            $map['amount'] += (($dt->price * $dt->qty));
                        }
                        if ($item->category_id != 21 && !str_contains($item->name, 'TS')) {
                            if ($map['coa_name'] == $hppName) {
                                $map['amount'] += (($item->cost_price * $dt->qty));
                            } else if ($map['coa_name'] == $persediaanName) {
                                $map['amount'] += (($item->cost_price * $dt->qty));
                            }
                        }
                        $tmpJournalData[$idx] = $map;
                        $idx++;
                    }
                }
            }

            foreach ($tmpJournalData as $nJournal) {
                if ($nJournal['amount'] > 0) {
                    $coa = Coa::where('id', $nJournal['coa_id'])->first();
                    if ($coa != null) {
                        //insert journal
                        $type = 'KREDIT';
                        if ($nJournal['journal_type'] == 'DEBET') {
                            $type = 'DEBET';
                        }
                        $notes = 'Automate - Transaksi ' . $header->code;
                        JournalUtilities::insertRecord($jHeader, $branchId, $coa, $type, $nJournal['amount'], $notes, $header->code);
                    }
                }
            }

            if (!$isFoc && $header->discount > 0) {
                $discountMap = JournalMap::where('branch_id', $branchId)->where('transaction_type', 'POS_POTONGAN')->first();
                if ($discountMap != null) {
                    $coa = Coa::where('id', $discountMap->coa_id)->first();
                    if ($coa != null) {
                        $notes = 'Diskon Transaksi ' . $header->discount;
                        JournalUtilities::insertRecord($jHeader, $branchId, $coa, 'DEBET', $header->discount, $notes, $header->code);
                    }
                }
            }

            // Mark approved
            DB::table('transaction_headers')
                ->where('id', '=', $header->id)
                ->update([
                    'is_approved' => 1,
                    'approved_by' => $admin->id,
                    'approved_at' => Carbon::now('Asia/Jakarta')->toDateTimeString()
                ]);

            Session::flash('success', 'Sukses approve jurnal transaksi ' . $header->code);
            return redirect()->route('admin.transaction.show', ['id' => $header->id]);
        } catch (\Exception $ex) {
            dd($ex);
            Log::channel('pos')->error('Admin/TransactionController - approveJournal ' . $ex);
            Session::flash('error', 'Terjadi kesalahan internal sistem!');
            return redirect()->back();
        }
    }

    public function approveJournalBackup(Request $request)
    {
        try {
            if (!$request->filled('approved_transaction_id')) {
                Session::flash('error', 'BAD REQUEST!');
                return redirect()->back();
            }

            $trxId = $request->input('approved_transaction_id');
            $trxHeader = TransactionHeader::with(['transaction_details'])->find($trxId);
            if (empty($trxHeader)) {
                Session::flash('error', 'BAD REQUEST!');
                return redirect()->back();
            }

            // Check approved
            if ($trxHeader->is_approved) {
                // Check existing journal
                if (!DB::table('journal_headers')
                    ->where('branch_id', '=', $trxHeader->branch_id)
                    ->where('notes', '=', 'Journal Transaksi ' . $trxHeader->code)
                    ->exists()) {
                    $trxHeader->is_approved = false;
                    $trxHeader->save();
                }
            } else {
                // Check existing journal
                if (DB::table('journal_headers')
                    ->where('branch_id', '=', $trxHeader->branch_id)
                    ->where('notes', '=', 'Journal Transaksi ' . $trxHeader->code)
                    ->exists()
                ) {
                    $trxHeader->is_approved = true;
                    $trxHeader->approved_at = Carbon::now('Asia/Jakarta')->toDateTimeString();
                    $trxHeader->save();

                    Session::flash('error', 'Transaksi sudah di-approve!');
                    return redirect()->back();
                }
            }

            $branch = DB::table('branches')->find($trxHeader->branch_id);

            $isMembershipPurchaseFound = false;
            $membershipDetailId = 0;

            $isFacilityOnlyFound = false;
            $isItemFound = false;
            $isPackageFound = false;

            // Calculate transaction raw total price
            $totalTrx = 0;
            foreach ($trxHeader->transaction_details as $detail) {
                if (!empty($detail->item_id)) {
                    $isItemFound = true;

                    $totalTrx += ($detail->qty * $detail->price);
                } else {
                    $totalTrx += $detail->subtotal;
                }

                if (!empty($detail->package_id)) {
                    $isPackageFound = true;
                }

                if (!empty($detail->membership_id)) {
                    $isMembershipPurchaseFound = true;
                    $membershipDetailId = $detail->id;
                }

                if ($detail->is_facility_only === 1) {
                    $isFacilityOnlyFound = true;
                }
            }

            $admin = Auth::guard('admin')->user();

            // Create journal header
            $journal = new JournalHeader();
            $journal->transaction_code = AutoNumberGenerator::generateAutonumberTrx('JN');
            $journal->transaction_type = 'POS';
            $journal->transaction_date = Carbon::now('Asia/Jakarta');
            $journal->created_by = $admin->id;
            $journal->branch_id = $trxHeader->branch_id;
            $journal->notes = 'Journal Transaksi ' . $trxHeader->code;
            $journal->reference_id = $trxHeader->id;
            $journal->status_id = 1;
            $journal->save();

            // Check for membership detail transaction
            if ($isMembershipPurchaseFound) {
                $membershipDetail = DB::table('transaction_details')
                    ->where('id', '=', $membershipDetailId)
                    ->first();

                $dbMembership = Membership::where('id', $membershipDetail->membership_id)->first();

                $membershipJournalMap = JournalMap::where('branch_id', $trxHeader->branch_id)
                    ->where('transaction_type', 'POS_MEMBER')
                    ->first();
                if ($membershipJournalMap != null) {
                    $coa = Coa::where('id', $membershipJournalMap->coa_id)->first();
                    if ($coa != null) {
                        $notes = 'Pendapatan Member Transaksi ' . $trxHeader->code;
                        JournalUtilities::insertRecord(
                            $journal,
                            $trxHeader->branch_id,
                            $coa,
                            'KREDIT',
                            $dbMembership->price,
                            $notes,
                            $trxHeader->code
                        );
                    }
                }
            }

            $trxPayments = DB::table('transaction_payments')
                ->where('header_id', '=', $trxHeader->id)
                ->get();

            // Payment journal creation
            foreach ($trxPayments as $trxPayment) {
                if (empty($trxHeader->foc_type) || strtolower($trxHeader->foc_type) === 'no') {
                    // Check for OURA online payment
                    if ($branch->is_oura === 1) {
                        $bookingHeader = DB::table('booking_headers')
                            ->where('transaction_id', '=', $trxHeader->id)
                            ->first();

                        if (!empty($bookingHeader)) {
                            $coa = Coa::where('id', '=', 2495)->first();
                            $notes = 'Payment Transaksi Online ' . $trxHeader->code;
                            JournalUtilities::insertRecord(
                                $journal,
                                $trxHeader->branch_id,
                                $coa,
                                'DEBET',
                                $trxHeader->grand_total,
                                $notes,
                                $trxHeader->code
                            );

                            if ($bookingHeader->type === 'home_service') {
                                $jHomeServiceFee = JournalMap::where('branch_id', 6)->where('coa_code', '4-40000')->first();
                                $coaPendapatan = Coa::where('id', $jHomeServiceFee->coa_id)
                                    ->first();
                                JournalUtilities::insertRecord(
                                    $journal,
                                    $trxHeader->branch_id,
                                    $coaPendapatan,
                                    $jHomeServiceFee->journal_type,
                                    25000,
                                    'Fee Home Service ' . $trxHeader->code,
                                    $trxHeader->code
                                );
                            }

                            continue;
                        }
                    }

                    $journalMap = JournalMap::where('branch_id', $admin->branch_id)
                        ->where(function ($q) {
                            $q->where('transaction_type', 'POS_CASH')
                                ->orWhere('transaction_type', 'POS_NON_CASH');
                        })
                        ->where('room_type_id', 'like', '%' . $trxPayment->payment_type_id . '%')
                        ->first();

                    if ($journalMap != null) {
                        $coa = Coa::where('id', $journalMap->coa_id)->first();
                        if ($coa != null) {
                            $type = 'KREDIT';
                            if ($journalMap->journal_type == 'DEBET') {
                                $type = 'DEBET';
                            }
                            $notes = 'Payment Transaksi ' . $trxHeader->code;
                            JournalUtilities::insertRecord(
                                $journal,
                                $admin->branch_id,
                                $coa,
                                $type,
                                $trxPayment->amount,
                                $notes,
                                $trxHeader->code
                            );
                        }
                    }
                } else {
                    // FOC journal
                    $journalMap = JournalMap::where('branch_id', $admin->branch_id)
                        ->where('transaction_type', 'POS_FOC')
                        ->first();
                    if ($journalMap != null) {
                        $coa = Coa::where('id', $journalMap->coa_id)->first();
                        if ($coa != null) {
                            $notes = 'Transaksi FOC ' . $trxHeader->code;
                            JournalUtilities::insertRecord(
                                $journal,
                                $admin->branch_id,
                                $coa,
                                'DEBET',
                                $totalTrx,
                                $notes,
                                $trxHeader->code
                            );
                        }
                    }
                }
            }

            // Facility only journal creation
            if ($isFacilityOnlyFound) {
                $facJournalMap = JournalMap::where('room_type_id', 99)->first();
                if ($facJournalMap != null) {
                    $coa = Coa::where('id', $facJournalMap->coa_id)->first();
                    if ($coa != null) {
                        $type = 'KREDIT';
                        if ($facJournalMap->journal_type == 'DEBET') {
                            $type = 'DEBET';
                        }
                        $notes = 'Transaksi ' . $trxHeader->code;

                        $facilityPrice = Configuration::where('branch_id', $trxHeader->branch_id)
                            ->where('key', 'facility')
                            ->first();

                        JournalUtilities::insertRecord(
                            $journal,
                            $trxHeader->branch_id,
                            $coa,
                            $type,
                            $facilityPrice->value_int,
                            $notes,
                            $trxHeader->code
                        );
                    }
                }
            }

            // Item journal creation
            if ($isItemFound) {
                $trxItems = DB::table('transaction_details')
                    ->where('header_id', '=', $trxHeader->id)
                    ->whereNotNull('item_id')
                    ->get();

                if ($trxHeader->branch->is_oura == 1) {
                    $hppJournalMap = JournalMap::where('branch_id', $trxHeader->branch_id)
                        ->where(function ($q) {
                            $q->where('transaction_type', 'POS_HPP')
                                ->orWhere('transaction_type', 'POS_PERSEDIAAN')
                                ->orWhere('transaction_type', 'POS_PENJUALAN')
                                ->orWhere('transaction_type', 'POS_PENDAPATAN');
                        })
                        ->get();
                } else {
                    $hppJournalMap = JournalMap::where('branch_id', $trxHeader->branch_id)
                        ->where(function ($q) {
                            $q->where('transaction_type', 'POS_HPP')
                                ->orWhere('transaction_type', 'POS_PERSEDIAAN')
                                ->orWhere('transaction_type', 'POS_PENJUALAN')
                                ->orWhere('coa_name', 'Pendapatan Meal')
                                ->orWhere('coa_name', 'Pendapatan - F&B')
                                ->orWhere('coa_name', 'Pendapatan Scrub')
                                ->orWhere('coa_name', 'Pendapatan Hot Stone')
                                ->orWhere('coa_name', 'PENDAPATAN MEAL INTERNAL')
                                ->orWhere('coa_name', 'PENDAPATAN MEAL EKSTERNAL')
                                ->orWhere('coa_name', 'Pendapatan Oil');
                        })
                        ->get();
                }

                $tmpJournalData = [];
                foreach ($hppJournalMap as $map) {
                    $nMap = [
                        'id'            => $map->id,
                        'coa_id'        => $map->coa_id,
                        'coa_name'      => $map->coa_name,
                        'coa_code'      => $map->coa_code,
                        'journal_type'  => $map->journal_type,
                        'amount'        => 0,
                    ];

                    $tmpJournalData[] = $nMap;
                }

                foreach ($trxItems as $dt) {
                    $item = Item::where('id', $dt->item_id)->first();
                    if ($item != null) {
                        //Journal Pendapatan
                        $hppName = 'HPP - Lain-lain';
                        $categoryName = 'Pendapatan - Lain-lain';
                        $persediaanName = 'Persediaan - Lain-lain';

                        if ($trxHeader->branch_id == 1) {
                            if (str_contains($item->name, 'KIMO') || $item->category_id == 20) {
                                $categoryName = 'Pendapatan - Kimo';
                                $hppName = 'HPP - Kimo';
                                $persediaanName = 'Persediaan - Kimo';
                            } else if (str_contains($item->name, 'OIL')) {
                                $categoryName = 'Pendapatan Oil';
                            } else if (str_contains($item->name, 'HOT STONE')) {
                                $categoryName = 'Pendapatan Hot Stone';
                            }

                            if ($item->category_id == 2) {
                                $categoryName = 'Pendapatan - F&B';
                                $hppName = 'HPP - F&B';
                                $persediaanName = 'Persediaan - F&B';
                            }

                            if ($item->category_id == 21) {
                                $categoryName = 'Pendapatan Meal';
                            }

                            if ($item->category_id == 22) {
                                $categoryName = 'PENDAPATAN MEAL EKSTERNAL';
                            }

                            if ($item->category_id == 23) {
                                $categoryName = 'PENDAPATAN MEAL INTERNAL';
                            }

                            if ($item->category_id == 25) {
                                $categoryName = 'PENJUALAN LIQUOR';
                                $hppName = 'HPP - LIQUOR';
                                $persediaanName = 'PERSEDIAAN LIQUOR';
                            }

                            if (str_contains($item->name, 'NURU')) {
                                $categoryName = 'PENJUALAN NURU';
                                $hppName = 'HPP - NURU';
                                $persediaanName = 'PERSEDIAAN NURU';
                            }
                        } else if ($trxHeader->branch_id >= 6) {
                            $hppName = 'Beban Pokok Penjualan';
                            $categoryName = 'Pendapatan';
                            $persediaanName = 'Persediaan';
                            if ($item->category_id == 2) {
                                $persediaanName = 'PERSEDIAAN - F&B';
                            }

                            if ($item->name == 'OIL THERAPY ( HOT )') {
                                $categoryName = 'PENDAPATAN OIL THERAPY (HOT)';
                            } else if (str_contains($item->name, 'TS')) {
                                $categoryName = 'Komisi terhutang';
                            }
                            //check for HPP
                            if ($item->category_id == 2) {
                                $hppName = 'BEBAN POKOK PENJUALAN - F&B';
                            }
                        } else {
                            $hppName = 'HPP Lain-lain';
                            $categoryName = 'Penjualan Lain-lain';
                            $persediaanName = 'Persediaan Lain-lain';

                            if (str_contains($item->name, 'KIMO')) {
                                $categoryName = 'Penjualan Kimo';
                                $hppName = 'HPP Kimo';
                                $persediaanName = 'Persediaan Kimo';
                            } else if (str_contains($item->name, 'SCRUB')) {
                                $categoryName = 'Pendapatan Scrub';
                            } else if (str_contains($item->name, 'OIL')) {
                                $categoryName = 'Pendapatan Oil';
                            } else if (str_contains($item->name, 'HOT STONE')) {
                                $categoryName = 'Pendapatan Hot Stone';
                            } else if (str_contains($item->name, 'HOTSTONE')) {
                                $categoryName = 'Pendapatan Hot Stone';
                            }

                            if ($item->category_id == 2) {
                                $categoryName = 'Penjualan F&B';
                                $hppName = 'HPP F&B';
                                $persediaanName = 'Persediaan F&B';
                            }
                        }

                        $idx = 0;
                        foreach ($tmpJournalData as $map) {
                            if ($map['coa_name'] == $categoryName) {
                                $map['amount'] += (($dt->price * $dt->qty));
                            }
                            if ($item->category_id != 21 && !str_contains($item->name, 'TS')) {
                                if ($map['coa_name'] == $hppName) {
                                    $map['amount'] += (($item->cost_price * $dt->qty));
                                } else if ($map['coa_name'] == $persediaanName) {
                                    $map['amount'] += (($item->cost_price * $dt->qty));
                                }
                            }
                            $tmpJournalData[$idx] = $map;
                            $idx++;
                        }
                    }
                }

                foreach ($tmpJournalData as $nJournal) {
                    if ($nJournal['amount'] > 0) {
                        $coa = Coa::where('id', $nJournal['coa_id'])->first();
                        if ($coa != null) {
                            //insert journal
                            $type = 'KREDIT';
                            if ($nJournal['journal_type'] == 'DEBET') {
                                $type = 'DEBET';
                            }
                            $notes = 'Transaksi ' . $trxHeader->code;
                            JournalUtilities::insertRecord(
                                $journal,
                                $trxHeader->branch_id,
                                $coa,
                                $type,
                                $nJournal['amount'],
                                $notes,
                                $trxHeader->code
                            );
                        }
                    }
                }

                // For EMP and KMC only
                // if($trxHeader->branch_id < 6){

                // }
                // For OURA only
                // else{
                //     foreach ($trxItems as $detailItem){
                //         $item = DB::table('items')
                //             ->where('id', '=', $detailItem->item_id)
                //             //->where('name', 'not like', 'TS%')
                //             ->first();

                //         if($item != null){
                //             if($item->category_id == 2){
                //                 $jPersediaan = JournalMap::where('branch_id', 6)->where('coa_code', '1-10201')->first();
                //             }
                //             else{
                //                 $jPersediaan = JournalMap::where('branch_id', 6)->where('coa_code', '1-10200')->first();
                //             }

                //             if($item->name == 'OIL THERAPY ( HOT )'){
                //                 $jPendapatan = JournalMap::where('branch_id', 6)->where('coa_code', '4-40001')->first();
                //             }
                //             else if(str_contains($item->name, 'TS')){
                //                 $jPendapatan = JournalMap::where('branch_id', 6)->where('coa_code', '2-10007')->first();
                //             }
                //             else{
                //                 $jPendapatan = JournalMap::where('branch_id', 6)->where('coa_code', '4-40000')->first();
                //             }

                //             //$jPendapatan = JournalMap::where('branch_id', 6)->where('coa_code', '4-40000')->first();
                //             //check for HPP
                //             if($item->category_id == 2){
                //                 $jHpp = JournalMap::where('branch_id', 6)->where('coa_code', '5-50001')->first();
                //             }
                //             else{
                //                 $jHpp = JournalMap::where('branch_id', 6)->where('coa_code', '5-50000')->first();
                //             }
                //             $notes = 'Transaksi ' . $trxHeader->code;

                //             // Journal Pendapatan
                //             $coaPendapatan = Coa::where('id', $jPendapatan->coa_id)
                //                 ->first();
                //             JournalUtilities::insertRecord($journal,
                //                 $admin->branch_id,
                //                 $coaPendapatan,
                //                 $jPendapatan->journal_type,
                //                 $detailItem->subtotal,
                //                 $notes,
                //                 $trxHeader->code);

                //             // Journal Persediaan
                //             $coaPersediaan = Coa::where('id', $jPersediaan->coa_id)
                //                 ->first();
                //             JournalUtilities::insertRecord($journal,
                //                 $admin->branch_id,
                //                 $coaPersediaan,
                //                 $jPersediaan->journal_type,
                //                 $item->cost_price * $detailItem->qty,
                //                 $notes,
                //                 $trxHeader->code);

                //             // Journal Hpp
                //             $coaHpp = Coa::where('id', $jPersediaan->coa_id)
                //                 ->first();
                //             JournalUtilities::insertRecord($journal,
                //                 $admin->branch_id,
                //                 $coaHpp,
                //                 $jHpp->journal_type,
                //                 $item->cost_price * $detailItem->qty,
                //                 $notes,
                //                 $trxHeader->code);
                //         }
                //     }
                // }
            }

            if ($isPackageFound) {
                $trxPackages = DB::table('transaction_details')
                    ->where('header_id', $trxHeader->id)
                    ->whereNotNull('package_id')
                    ->get();

                foreach ($trxPackages as $tmpPackage) {
                    //journal detail
                    $tmpPackageDetail = Package::where('id', $tmpPackage->package_id)->first();

                    if ($branch->is_oura === 1) {
                        $packageJournal = JournalMap::where('branch_id', $trxHeader->branch_id)
                            ->where('coa_code', '4-40000')
                            ->first();
                    } else {
                        if ($trxHeader->branch_id != 1) {
                            $packageJournal = JournalMap::where('branch_id', $trxHeader->branch_id)
                                ->where('transaction_type', 'POS_PENDAPATAN')
                                ->where('coa_name', 'Pendapatan Massage')
                                ->first();
                        } else {
                            $packageJournal = JournalMap::where('branch_id', $trxHeader->branch_id)
                                ->where('transaction_type', 'POS_PENDAPATAN')
                                ->where('room_type_id', $tmpPackageDetail->room_type_id)
                                ->first();
                        }
                    }

                    if ($packageJournal != null) {
                        $coa = Coa::where('id', $packageJournal->coa_id)->first();
                        if ($coa != null) {
                            $value = ($tmpPackage->price * $tmpPackage->qty) + $tmpPackage->additional_partner_amount
                                + $tmpPackage->additional_person_amount + $tmpPackage->extension_amount;

                            //if Oura check for TS
                            //check if its OURA
                            // if($trxHeader->branch_id >= 6){
                            //     $trxTs = TransactionDetail::where('header_id', $trxHeader->id)
                            //         ->where('item_id', '!=', null)
                            //         ->get();
                            //     foreach($trxTs as $ts){
                            //         $tmpTsItem = Item::where('id', $ts->item_id)->first();
                            //         if(str_contains($tmpTsItem->name, 'TS')){
                            //             //TS Exists
                            //             $value += $ts->subtotal;
                            //         }
                            //     }
                            // }

                            $notes = 'Pendapatan Transaksi Paket ' . $tmpPackageDetail->name . ' : ' . $value;
                            JournalUtilities::insertRecord(
                                $journal,
                                $trxHeader->branch_id,
                                $coa,
                                'KREDIT',
                                $value,
                                $notes,
                                $trxHeader->code
                            );
                        }
                    }
                }
            }

            // $totalDiscount = DB::table('transaction_details')
            //     ->where('header_id', $trxHeader->id)
            //     ->sum('discount_amount');

            //if($trxHeader->is_happy_hour == 0){
            $totalDiscount = $trxHeader->discount;
            //}

            if ($totalDiscount > 0) {
                $discountMap = JournalMap::where('branch_id', $trxHeader->branch_id)
                    ->where('transaction_type', 'POS_POTONGAN')
                    ->first();
                if ($discountMap != null) {
                    $coa = Coa::where('id', $discountMap->coa_id)->first();
                    if ($coa != null) {
                        $notes = 'Diskon Transaksi ' . $trxHeader->code;
                        JournalUtilities::insertRecord(
                            $journal,
                            $trxHeader->branch_id,
                            $coa,
                            'DEBET',
                            $totalDiscount,
                            $notes,
                            $trxHeader->code
                        );
                    }
                }
            }

            // Mark approved
            DB::table('transaction_headers')
                ->where('id', '=', $trxHeader->id)
                ->update([
                    'is_approved' => 1,
                    'approved_by' => $admin->id,
                    'approved_at' => Carbon::now('Asia/Jakarta')->toDateTimeString()
                ]);

            Session::flash('success', 'Sukses approve jurnal transaksi ' . $trxHeader->code);
            return redirect()->route('admin.transaction.show', ['id' => $trxHeader->id]);
        } catch (\Exception $ex) {
            dd($ex);
            Log::channel('pos')->error('Admin/TransactionController - approveJournal ' . $ex);
            Session::flash('error', 'Terjadi kesalahan internal sistem!');
            return redirect()->back();
        }
    }

    public function changePartner(Request $request)
    {
        try {
            if (!$request->filled('id')) {
                Session::flash('error', 'BAD REQUEST!');
                return back();
            }

            if (!$request->filled('password')) {
                Session::flash('error', 'Kata Sandi wajib diisi!');
                return back();
            }

            //check password first
            $user = Auth::guard('admin')->user();
            $userData = AdminUser::where('id', $user->id)->first();
            $config = Configuration::where('key', 'spv_password')->first();
            if (!Hash::check($request->input('password'), $config->value)) {
                Session::flash('error', 'Password Salah!');
                return back();
            }

            $invoice = TransactionInvoice::find($request->input('id'));
            $detailPartner = TransactionDetailPartner::where('id', $request->input('change_partner_id'))->first();
            $detailPartner->partner_id = $request->input('new_partner_id');
            $detailPartner->save();

            Session::flash('success', 'Sukses Change Partner transaksi!');
            return Redirect::route('admin.transaction.show', ['id' => $invoice->id]);
        } catch (\Exception $ex) {
            Log::channel('void')->error('ERROR EX: ' . $ex);
            Session::flash('error', 'Maaf terjadi kesalahan!');
            return back();
        }
    }

    public function voidDetail(Request $request)
    {
        try {
            if (!$request->filled('detail_id')) {
                Session::flash('error', 'BAD REQUEST!');
                return back();
            }

            if (!$request->filled('password_void')) {
                Session::flash('error', 'Kata Sandi wajib diisi!');
                return back();
            }

            //check password first
            $user = Auth::guard('admin')->user();
            $userData = AdminUser::where('id', $user->id)->first();
            $config = Configuration::where('key', 'spv_password')->first();
            if (!Hash::check($request->input('password_void'), $config->value)) {
                Session::flash('error', 'Password Salah!');
                return back();
            }

            DB::beginTransaction();
            $detail = TransactionDetail::find($request->input('detail_id'));
            $detail->voided_by = $userData->id;
            $detail->voided_at = Carbon::now('Asia/Jakarta')->toDateTimeString();
            $detail->status_id = 16;
            $detail->save();

            // regenerate journal
            $invoice = TransactionInvoice::where('header_ids', $detail->header_id)->first();
            $detailSum = TransactionDetail::where('header_id', '=', $detail->header_id)->where('status_id', '!=', 16)
                ->sum('subtotal');
            $pb2 =($detailSum * 0.1);
            $grandTotal = $detailSum + $pb2;

            $invoice->subtotal = $detailSum;
            $invoice->pb2 = $pb2;
            $invoice->grandtotal = $grandTotal;
            $invoice->save();

            $payment = TransactionPayment::where('invoice_id', '=', $invoice->id)->first();
            $payment->amount = $grandTotal;
            $payment->save();

            $journalHeader = JournalHeader::where('reference_id', '=', $invoice->id)
                ->first();

            if(empty($journalHeader)){
                $journalHeader = JournalHeader::where('notes', 'like', '%'. $invoice->code. '%')
                    ->first();
            }
            if(!empty($journalHeader)){

                // Delete its details
                DB::table('journal_details')
                    ->where('header_id', '=', $journalHeader->id)
                    ->delete();

                $journalHeader->delete();
            }
            //Journal Transaction
            $journal = new JournalUtilities();
            $journal->createJournalPos($detail->header_id);

            DB::commit();

            Session::flash('success', 'Sukses void Treatment!');
            return Redirect::route('admin.transaction.show', ['id' => $invoice->id]);
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::channel('void')->error('ERROR EX: ' . $ex);
            Session::flash('error', 'Maaf terjadi kesalahan!'. $ex->getMessage());
            return back();
        }
    }

    public function void(Request $request)
    {
        try {
            if (!$request->filled('id')) {
                Session::flash('error', 'BAD REQUEST!');
                return back();
            }

            if (!$request->filled('password_void')) {
                Session::flash('error', 'Kata Sandi wajib diisi!');
                return back();
            }

            //check password first
            $user = Auth::guard('admin')->user();
            $userData = AdminUser::where('id', $user->id)->first();
            $config = Configuration::where('key', 'spv_password')->first();
            if (!Hash::check($request->input('password_void'), $config->value)) {
                Session::flash('error', 'Password Salah!');
                return back();
            }

            DB::beginTransaction();
            $invoice = TransactionInvoice::find($request->input('id'));

            $oldStatusId = $invoice->status_id;
            $branch = DB::table('branches')
                ->where('id', '=', $invoice->branch_id)
                ->first();

            $invoice->voided_at = Carbon::now('Asia/Jakarta')->toDateTimeString();
            $invoice->status_id = 16;
            $invoice->save();

            $transactionHeaders = TransactionHeader::where('id', $invoice->header_ids)->first();
            $transactionHeaders->voided_at = Carbon::now('Asia/Jakarta')->toDateTimeString();
            $transactionHeaders->status_id = 16;
            $transactionHeaders->save();


            // Check for journal
            $journalHeader = JournalHeader::where('reference_id', '=', $invoice->id)
                ->first();

            if(empty($journalHeader)){
                $journalHeader = JournalHeader::where('notes', 'like', '%'. $invoice->code. '%')
                    ->first();
            }
            if(!empty($journalHeader)){

                // Delete its details
                DB::table('journal_details')
                    ->where('header_id', '=', $journalHeader->id)
                    ->delete();

                $journalHeader->delete();
            }
            DB::commit();
            //
            //            // Check for partner commission
            //            $commissionPartnerHistories = CommissionPartnerHistory::where('transaction_id', '=', $invoice->id)->get();
            //            if($commissionPartnerHistories->count() > 0){
            //                foreach($commissionPartnerHistories as $commissionPartnerHistory){
            //                    Log::channel('void')->info('ADMIN: '. $userData->username.
            //                        ' COMM_PARTNER_HISTORY_ID: '. $commissionPartnerHistory->id.
            //                        ' PARTNER_ID: '. $commissionPartnerHistory->partner_id.
            //                        ' PARTNER_NUMBER: '. $commissionPartnerHistory->partner_number.
            //                        ' PARTNER_ALIAS: '. $commissionPartnerHistory->partner_alias.
            //                        ' VOID DELETED');
            //
            //                        $commissionPartnerHistory->delete();
            //                }
            //            }
            //
            //            // Check for employee commission
            //            $commissionEmployeeHistories = CommissionEmployeeHistory::where('transaction_id', '=', $invoice->id)->get();
            //            if($commissionEmployeeHistories->count() > 0){
            //                foreach($commissionEmployeeHistories as $commissionEmployeeHistory){
            //                    Log::channel('void')->info('ADMIN: '. $userData->username.
            //                        ' COMM_EMPLOYEE_HISTORY_ID: '. $commissionEmployeeHistory->id.
            //                        ' EMPLOYEE_ID: '. $commissionEmployeeHistory->employee_id.
            //                        ' EMPLOYEE_CODE: '. $commissionEmployeeHistory->employee_code.
            //                        ' EMPLOYEE_NAME: '. $commissionEmployeeHistory->employee_name.
            //                        ' VOID DELETED');
            //
            //                        $commissionEmployeeHistory->delete();
            //                }
            //            }
            //
            //            // Check partner and room status
            //            $trxDetails = null;
            //            $isCheckDetails = false;
            //            if($branch->is_oura == 0){
            //                if($oldStatusId === 8){
            //                    $trxDetails = DB::table('transaction_details')
            //                    ->where('header_id', '=', $invoice->id)
            //                    ->get();
            //
            //                    $isCheckDetails = true;
            //                }
            //            }
            //            else{
            //                $trxDetails = DB::table('transaction_details')
            //                    ->where('header_id', '=', $header->id)
            //                    ->where('status_id', '=', 8)
            //                    ->get();
            //
            //                $isCheckDetails = true;
            //            }
            //
            //            if($isCheckDetails){
            //                foreach($trxDetails as $trxDetail){
            //                    if(!empty($trxDetail->package_id)){
            //                        // Update room status to available
            //                        DB::table('rooms')
            //                            ->where('id', '=', $trxDetail->room_id)
            //                            ->update([
            //                                'status_id' => 5,
            //                                'updated_at' => Carbon::now('Asia/Jakarta')->toDateTimeString()
            //                            ]);
            //
            //                        $trxPartnerDetails = DB::table('transaction_detail_partners')
            //                            ->where('transaction_detail_id', '=', $trxDetail->id)
            //                            ->get();
            //
            //                        foreach($trxPartnerDetails as $trxPartnerDetail){
            //                            // Update partner status to available
            //                            DB::table('partners')
            //                                ->where('id', '=', $trxPartnerDetail->partner_id)
            //                                ->update([
            //                                    'status_id' => 5,
            //                                    'updated_at' => Carbon::now('Asia/Jakarta')->toDateTimeString()
            //                                ]);
            //                        }
            //                    }
            //                }
            //            }
            //
            //            // Update its details
            //            DB::table('transaction_details')
            //                ->where('header_id', '=', $header->id)
            //                ->update(['status_id' => 16]);
            //
            //            $trxDetails = DB::table('transaction_details')
            //                ->where('header_id', '=', $header->id)
            //                ->where('status_id', '=', 16)
            //                ->get();
            //
            //            if($trxDetails->count() > 0){
            //                foreach($trxDetails as $trxDetail){
            //                    if(!empty($trxDetail->item_id)){
            //                        $item = DB::table('items')
            //                            ->where('id', '=', $trxDetail->item_id)
            //                            ->first();
            //                        if($item->is_unlimited_stock < 1){
            //                            $configWarehouse = DB::table('configurations')
            //                                ->where('key', '=', 'selling_warehouse_id')
            //                                ->where('branch_id', '=', $userData->branch_id)
            //                                ->first();
            //
            //                            $warehouseId = intval($configWarehouse->value_int);
            //                            // Decrease stock
            //                            Utilities::updateStock($userData->branch_id,
            //                                'add',
            //                                $trxDetail->qty,
            //                                $trxDetail->item_id,
            //                                $warehouseId,
            //                                $header->code,
            //                                'Void Item untuk '. $header->code);
            //                        }
            //                    }
            //                }
            //            }

            Session::flash('success', 'Sukses void transaksi!');
            return Redirect::route('admin.transaction.show', ['id' => $invoice->id]);
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::channel('void')->error('ERROR EX: ' . $ex);
            Session::flash('error', 'Maaf terjadi kesalahan!');
            return back();
        }
    }

    public function filter($type)
    {
        $startDate = Carbon::now('Asia/Jakarta')->startOfMonth();;
        $endDate = Carbon::now('Asia/Jakarta');
        $endDate->addDay();

        $data = [
            'startDate' => $startDate->format('d-m-Y'),
            'endDate'   => $endDate->format('d-m-Y'),
            'type'      => "Voucher"
        ];

        return view('admin.report.report_transaction_filter')->with($data);
    }

    // public function report(Request $request)
    // {
    //     try {
    //         $rules = array(
    //             'start_date' => 'required',
    //             'end_date'  => 'required',
    //         );

    //         $message = array(
    //             'start_date.required'   => 'Tanggal Mulai Wajib dipilih!',
    //             'end_date.required'     => 'Tanggal Akhir Wajib dipilih!',
    //         );

    //         $validator = Validator::make($request->all(), $rules, $message);

    //         if ($validator->fails()) {
    //             return Response::json(['error' => $validator->errors()->all()]);
    //         }
    //         $type = $request->input('type');

    //         //get data
    //         $startDate = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
    //         $endDate = Carbon::createFromFormat('!d-m-Y', $request->input('end_date'), 'Asia/Jakarta');
    //         $endDate->addDays(1);
    //         $user = Auth::guard('admin')->user();
    //         $invoices = TransactionInvoice::with(['createdBy'])
    //             ->where('status_id', 9)
    //             ->whereBetween('created_at', [$startDate->toDateTimeString(), $endDate->toDateTimeString()])
    //             ->where('branch_id', $user->branch_id)
    //             ->get();
    //         $result = [];
    //         $no = 1;
    //         foreach ($invoices as $invoice) {
    //             $headerIds = explode('#', $invoice->header_ids);
    //             $headers = TransactionHeader::whereIn('id', $headerIds)->get();

    //             $trxDate = Carbon::parse($invoice->created_at);
    //             $nHeader = [
    //                 'no'            => $no,
    //                 'code'          => $invoice->code,
    //                 'customer'      => implode(', ', array_column($headers->toArray(), 'member_name')),
    //                 'date'          => $trxDate->locale('id_ID')->toDateTimeString(),
    //                 'details'       => [],
    //                 'payments'      => [],
    //                 'payment_code'  => "",
    //                 'payment_amount'    => 0,
    //                 'partners'      => '',
    //                 'total'         => number_format($invoice->grandtotal, '0', ',', '.'),
    //                 'discount'      => number_format($invoice->voucher_discount_value, '0', ',', '.'),
    //                 'total_grouping'   => 0,
    //                 'created_by'    => $invoice->createdBy->name ?? '',
    //             ];

    //             switch ($type) {
    //                 case 'Voucher':
    //                     $details = TransactionDetail::whereIn('header_id', array_column($headers->toArray(), 'id'))
    //                         ->where('coupon_id', '!=', null)->get();
    //                     break;
    //                 case 'Studio':
    //                     $details = TransactionDetail::join('packages', 'packages.id', '=', 'transaction_details.package_id')
    //                         ->whereIn('transaction_details.header_id', array_column($headers->toArray(), 'id'))
    //                         ->whereIn('packages.room_type_id', [6])
    //                         ->where('packages.status_id', 1)
    //                         ->get();
    //                     break;
    //                 case 'Spa, Massage, Reflexology':
    //                     $details = TransactionDetail::join('packages', 'packages.id', '=', 'transaction_details.package_id')
    //                         ->leftJoin('items', 'items.id', '=', 'transaction_details.item_id')
    //                         ->whereIn('transaction_details.header_id', array_column($headers->toArray(), 'id'))
    //                         ->whereIn('packages.room_type_id', [1, 2, 3, 5, 25])
    //                         ->where(function ($query) {
    //                             $query->where(function ($q) {
    //                                 $q->whereIn('packages.room_type_id', [1, 2, 3, 5, 25])
    //                                 ->where('packages.status_id', 1);
    //                             })
    //                             ->orWhere('items.category_id', 27);
    //                         })
    //                         ->where('packages.status_id', 1)
    //                         ->get();
    //                     break;
    //                 case 'Salon':
    //                     $details = TransactionDetail::join('packages', 'packages.id', '=', 'transaction_details.package_id')
    //                         ->whereIn('transaction_details.header_id', array_column($headers->toArray(), 'id'))
    //                         ->whereIn('packages.room_type_id', [4])
    //                         ->where('packages.status_id', 1)
    //                         ->get();
    //                     break;
    //                 default:
    //                     $details = TransactionDetail::whereIn('header_id', array_column($headers->toArray(), 'id'))->get();
    //                     break;
    //             }
    //             if (count($details) == 0) {
    //                 continue;
    //             }

    //             // $details = TransactionDetail::whereIn('header_id', array_column($headers->toArray(), 'id'))->get();

    //             // dd($details, $nHeader);

    //             $dtNo = 1;
    //             $totalGrouping = 0;
    //             foreach ($details as $detail) {
    //                 $itemName = "";
    //                 if ($detail->package_id) {
    //                     $itemName = $detail->package->name;
    //                 } elseif ($detail->coupon_id) {
    //                     $itemName = $detail->user_voucher->voucher_code;
    //                 } else {
    //                     if ($detail->item_id) {
    //                         $itemName = $detail->item->name;
    //                     }
    //                 }

    //                 $nDetail = [
    //                     'no'        => $dtNo,
    //                     'item_name' => $itemName,
    //                     'qty'       => $detail->qty,
    //                     'price'     => number_format($detail->price, '0', ',', '.'),
    //                     'subtotal'  => number_format($detail->subtotal, '0', ',', '.'),
    //                 ];

    //                 //                    dd($nDetail);


    //                 $nHeader['details'][] = $nDetail;
    //                 $totalGrouping += $detail->subtotal;
    //                 $dtNo++;

    //                 //                    dd($nHeader);
    //             }
    //             $nHeader['total_grouping'] = number_format($totalGrouping, '0', ',', '.');

    //             $dtPartners = TransactionDetailPartner::whereIn('transaction_detail_id', array_column($details->toArray(), 'id'))->get();
    //             $nHeader['partners'] = $dtPartners->map(fn($dtPartner) => $dtPartner->partner->name)->implode(', ');

    //             $payments = TransactionPayment::where('invoice_id', $invoice->id)->first();
    //             if (!empty($payments)) {
    //                 $nHeader['payment_code'] = $payments->payment_type_code;
    //                 $nHeader['payment_amount'] = number_format($payments->amount, '0', ',', '.');
    //             }

    //             $result[] = $nHeader;
    //             $no++;
    //         }

    //         //            dd($result);

    //         return view('admin.transaction.transaction_report', compact('result', 'type'));
    //     } catch (\Exception $ex) {
    //         Session::flash('error', $ex);
    //         return back();
    //     }
    // }

    public function reportData(Request $request)
    {
        try {
            $rules = array(
                'start_date' => 'required',
                'end_date'  => 'required',
            );

            $message = array(
                'start_date.required'   => 'Tanggal Mulai Wajib dipilih!',
                'end_date.required'     => 'Tanggal Akhir Wajib dipilih!',
            );

            $validator = Validator::make($request->all(), $rules, $message);

            if ($validator->fails()) {
                return Response::json(['error' => $validator->errors()->all()]);
            }
            $type = $request->input('type');

            //get data
            $startDate = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
            $endDate = Carbon::createFromFormat('!d-m-Y', $request->input('end_date'), 'Asia/Jakarta');
            $endDate->endOfDay();
            $user = Auth::guard('admin')->user();
            $invoices = TransactionInvoice::with(['createdBy'])
                ->where('status_id', 9)
                ->whereBetween('created_at', [$startDate->toDateTimeString(), $endDate->toDateTimeString()])
                ->where('branch_id', $user->branch_id)
                ->get();
            $result = [];
            $no = 1;
            $totalGross = 0;
            $totalDiscount = 0;
            $totalPayment = 0;
            foreach ($invoices as $invoice) {
                $totalGroupingQty = 0;
                $headerIds = explode('#', $invoice->header_ids);
                $headers = TransactionHeader::whereIn('id', $headerIds)->get();
                $totalGroupingQty = TransactionHeader::whereIn('id', $headerIds)->sum('total_cust_male') + TransactionHeader::whereIn('id', $headerIds)->sum('total_cust_female');

                $trxDate = Carbon::parse($invoice->created_at);
                $nHeader = [
                    'no'            => $no,
                    'code'          => $invoice->code,
                    'customer'      => implode(', ', array_column($headers->toArray(), 'member_name')),
                    'date'          => $trxDate->locale('id_ID')->toDateString(),
                    'time'          => $trxDate->locale('id_ID')->toTimeString(),
                    'details'       => [],
                    'payments'      => [],
                    'payment_code'  => "",
                    'payment_amount'    => 0,
                    'partners'      => '',
                    'subtotal'         => number_format($invoice->subtotal, '0', ',', '.'),
                    'pb2'         => number_format($invoice->pb2, '0', ',', '.'),
                    'total'         => number_format($invoice->grandtotal, '0', ',', '.'),
                    'discount'      => number_format($invoice->voucher_discount_value, '0', ',', '.'),
                    'total_grouping'   => 0,
                    'created_by'    => $invoice->createdBy->name ?? '',
                    'total_male'    => implode(', ', array_column($headers->toArray(), 'total_cust_male')),
                    'total_female'    => implode(', ', array_column($headers->toArray(), 'total_cust_female')),
                ];

                $payments = TransactionPayment::where('invoice_id', $invoice->id)->first();
                if (!empty($payments)) {
                    $nHeader['payment_code'] = $payments->payment_type_code;
                    $nHeader['payment_amount'] = number_format($payments->amount, '0', ',', '.');
                }

                switch ($type) {
                    case 'All':
                        $details = TransactionDetail::leftJoin('packages', 'packages.id', '=', 'transaction_details.package_id')
                            ->leftJoin('items', 'items.id', '=', 'transaction_details.item_id')
                            ->select(
                                'transaction_details.*',
                                'packages.id as package_id',
                                'packages.name as package_name',
                                'packages.room_type_id as room_type_id',
                                'items.id as item_id',
                                'items.name as item_name',
                            )
                            ->whereIn('transaction_details.header_id', array_column($headers->toArray(), 'id'))
                            ->where(function ($q) {
                                $q->where(function ($sub) {
                                    $sub->whereNotNull('transaction_details.package_id')
                                        ->where('packages.status_id', 1);
                                })
                                ->orWhere(function ($sub) {
                                    $sub->whereNotNull('transaction_details.item_id')
                                        ->where('items.status_id', 1);
                                });
                            })
                            ->whereBetween('transaction_details.updated_at', [$startDate->toDateTimeString(), $endDate->toDateTimeString()])
                            ->get();
                        break;
                    case 'Voucher':
                        $details = TransactionDetail::whereIn('header_id', array_column($headers->toArray(), 'id'))
                            ->where('coupon_id', '!=', null)->get();
                        break;
                    case 'Studio':
                        $details = TransactionDetail::join('packages', 'packages.id', '=', 'transaction_details.package_id')
                            ->select(
                                'transaction_details.*',
                                'packages.id as package_id',
                                'packages.name as package_name',
                                'packages.room_type_id as room_type_id',
                            )
                            ->whereIn('transaction_details.header_id', array_column($headers->toArray(), 'id'))
                            ->whereIn('packages.room_type_id', [6])
                            ->where('packages.status_id', 1)
                            ->get();
                        break;
                    case 'Spa, Massage, Reflexology':
                        $details = TransactionDetail::join('packages', 'packages.id', '=', 'transaction_details.package_id')
                            ->select(
                                'transaction_details.*',
                                'packages.id as package_id',
                                'packages.name as package_name',
                                'packages.room_type_id as room_type_id',
                            )
                            ->whereIn('transaction_details.header_id', array_column($headers->toArray(), 'id'))
                            ->whereIn('packages.room_type_id', [1, 2, 3, 5, 25])
                            ->where('packages.status_id', 1)
                            ->get();
                        break;
                    case 'Treatment Tambahan':
                        $details = TransactionDetail::join('items', 'items.id', '=', 'transaction_details.item_id')
                            ->whereIn('transaction_details.header_id', array_column($headers->toArray(), 'id'))
                            ->where('items.category_id', 27)
                            ->where('items.status_id', 1)
                            ->get();
                        break;
                    case 'Salon':
                        $details = TransactionDetail::join('packages', 'packages.id', '=', 'transaction_details.package_id')
                            ->select(
                                'transaction_details.*',
                                'packages.id as package_id',
                                'packages.name as package_name',
                                'packages.room_type_id as room_type_id',
                            )
                            ->whereIn('transaction_details.header_id', array_column($headers->toArray(), 'id'))
                            ->whereIn('packages.room_type_id', [4])
                            ->where('packages.status_id', 1)
                            ->get();
                        break;
                    default:
                        $details = TransactionDetail::whereIn('header_id', array_column($headers->toArray(), 'id'))->get();
                        break;
                }
                // dd($details);
                if (count($details) == 0) {
                    continue;
                }

                // $details = TransactionDetail::whereIn('header_id', array_column($headers->toArray(), 'id'))->get();

                // dd($details, $nHeader);

                $dtNo = 1;
                $totalGrouping = 0;
                $totalDiscount =0;
                foreach ($details as $detail) {
                    $itemName = "";
                    $categoryName = "";
                    $qty = $detail->qty;

                    if ($detail->package_id) {
                        $itemName = $detail->package_name;
                        $categoryDB = RoomType::where('id', $detail->room_type_id)->first();
                        $categoryName = $categoryDB->name;

                        //if studio
                        if($detail->room_type_id == 6){
                            $qty = $totalGroupingQty;
                        }
                    } elseif ($detail->coupon_id) {
                        $itemName = $detail->user_voucher->voucher_code;
                        $voucherCodes = "";
                        foreach ($detail->user_voucher->user_voucher_code as $voucherCode)
                        {
                            $voucherCodes .= "[".$voucherCode->code."] ";
                        }
                        $itemName .= " " .$voucherCodes;
                        $categoryName = "Voucher";
                    } else {
                        if($type == "All"){
                            $itemName = $detail->item_name;
                            if ($detail->package_id) {
                                $packageDB = Package::where('id', $detail->package_id)->first();
                                $categoryDB = RoomType::where('id', $packageDB->room_type_id)->first();
                                $categoryName = $categoryDB->name;
                            }
                            else if($detail->item_id){
                                $itemDB = Item::where('id', $detail->item_id)->first();
                                $categoryDB = ItemCategory::where('id', $itemDB->category_id)->first();
                                $categoryName = $categoryDB->name;
                            }
                            //if studio
                            if($detail->room_type_id == 6){
                                $qty = $totalGroupingQty;
                            }
                        }
                        else{
                            if ($detail->item_id) {
                                $itemName = $detail->item->name;
                                $categoryDB = ItemCategory::where('id', $detail->item->category_id)->first();
                                $categoryName = $categoryDB->name;
                            }
                        }
                    }
                    $partnerName = "";
                    $detailPartner = TransactionDetailPartner::where('transaction_detail_id', $detail->id)->first();
                    // dd($detailPartner, $detail);
                    if($detailPartner){
                        $partnerName = $detailPartner->partner->name;
                    }

                    $dtGrandtotal = $detail->subtotal - $detail->discount_amount;
                    if($type == "All"){
                        $totalDiscount += $detail->discount_amount;
                        $dtPb2 = $dtGrandtotal * 0.1;
                        $trxDateDetail = $detail->start_time ? Carbon::parse($detail->start_time) : Carbon::parse($detail->updated_at);
                        $nDetail = [
                            'no'            => $no,
                            'code'          => $invoice->code,
                            'customer'      => implode(', ', array_column($headers->toArray(), 'member_name')),
                            'date'          => $trxDateDetail->locale('id_ID')->toDateString(),
                            'time'          => $trxDateDetail->locale('id_ID')->toTimeString(),
                            'category_name' => $categoryName,
                            'item_name' => $itemName,
                            'partner_name' => $partnerName,
                            'qty'       => $qty,
                            'price'     => number_format($detail->price, '0', ',', '.'),
                            'discount_amount'  => number_format($detail->discount_amount, '0', ',', '.'),
                            'voucher_name'       => $detail->voucher_name ?? '',
                            'pb2'  => number_format($dtPb2, '0', ',', '.'),
                            'subtotal'  => number_format($dtGrandtotal, '0', ',', '.'),
                            'payment_code' => $nHeader['payment_code'],
                            'payment_amount' => $nHeader['payment_amount'],
                            'total' => number_format($invoice->grandtotal, '0', ',', '.'),
                            'discount' => number_format($invoice->voucher_discount_value, '0', ',', '.'),
                            'total_grouping' => 0,
                            'created_by' => $invoice->createdBy->name ?? '',

                        ];
                    }
                    else{
                        $totalDiscount += $detail->discount_amount;
                        $nDetail = [
                            'no'        => $dtNo,
                            'category_name' => $categoryName,
                            'item_name' => $itemName,
                            'partner_name' => $partnerName,
                            'qty'       => $qty,
                            'price'     => number_format($detail->price, '0', ',', '.'),
                            'discount_amount'  => number_format($detail->discount_amount, '0', ',', '.'),
                            'voucher_name'       => $detail->voucher_name ?? '',
                            'subtotal'  => number_format($dtGrandtotal, '0', ',', '.'),
                        ];
                    }


                    $nHeader['details'][] = $nDetail;
                    $totalGrouping += $dtGrandtotal;
                    $dtNo++;

                    //                    dd($nHeader);
                }
                $nHeader['total_grouping'] = number_format($totalGrouping, '0', ',', '.');
                $nHeader['subtotal'] = number_format($totalGrouping, '0', ',', '.');
                $nHeader['total'] = number_format($totalGrouping + ($totalGrouping * 0.1), '0', ',', '.');
                $nHeader['pb2'] = number_format(($totalGrouping * 0.1), '0', ',', '.');
                $nHeader['payment_amount'] = number_format($totalGrouping + ($totalGrouping * 0.1), '0', ',', '.');

                $dtPartners = TransactionDetailPartner::whereIn('transaction_detail_id', array_column($details->toArray(), 'id'))->get();
                $nHeader['partners'] = $dtPartners->map(fn($dtPartner) => $dtPartner->partner->name)->implode(', ');

                $totalGross += $totalGrouping;
                $totalDiscount += $totalDiscount;
                $totalPayment += $totalPayment;

                $result[] = $nHeader;
                $no++;
            }

            //    dd($result);
            $allData = [
                'details' => $result,
                'totalGross' => $totalGross,
                'totalDiscount' => $totalDiscount,
                'totalPayment' => $totalPayment,

            ];
            return $allData;
        } catch (\Exception $ex) {
            dd($ex);
            Session::flash('error', $ex);
            return [];
        }
    }

    public function filterDailyPartnerReport()
    {
        $startDate = Carbon::now('Asia/Jakarta');

        $data = [
            'startDate' => $startDate->format('d-m-Y'),
        ];

        return view('admin.report.report_daily_partner_filter')->with($data);
    }

    public function generateDailyPartnerReport(Request $request)
    {
        try {
            $rules = array(
                'start_date' => 'required',
            );

            $message = array(
                'start_date.required'   => 'Tanggal Mulai Wajib dipilih!',
            );

            $validator = Validator::make($request->all(), $rules, $message);

            if ($validator->fails()) {
                return Response::json(['error' => $validator->errors()->all()]);
            }

            //get data
            $startDate = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
            $endDate = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
            $endDate->addDays(1);

            $user = Auth::guard('admin')->user();
            $userData = AdminUser::where('id', $user->id)->first();
            $partners = Partner::where('branch_id', $userData->branch_id)
                ->orderBy('number')
                ->get();
            $result = [];

            $details = TransactionDetail::with('partners', 'transaction_header')
                ->where('created_at', '>=', $startDate)
                ->where('package_id', '!=', null)
                ->get();

            foreach ($partners as $partner) {
                $nResult = [
                    'number'        => $partner->number,
                    'name'          => $partner->name,
                    'code'          => $partner->code,
                    'qty'           => 0,
                    'transactions'  => [],
                ];

                foreach ($details as $detail) {
                    $noDt = 1;
                    foreach ($detail->partners as $dbPartner) {
                        if ($dbPartner->id == $partner->id) {
                            //got the same id for partner in transaction
                            //$header = TransactionHeader::where('id', $detail->header_id)->first();
                            if ($detail->transaction_header != null && $detail->transaction_header->status_id === 9) {
                                $starTime = Carbon::parse($detail->transaction_header->start_time);
                                $endTime = Carbon::parse($detail->transaction_header->end_time);
                                $nTrx = [
                                    'no'            => $noDt,
                                    'customer'      => $detail->transaction_header->member_name,
                                    'code'          => $detail->transaction_header->code,
                                    'start_time'    => $starTime->toDateTimeString(),
                                    'end_time'      => $endTime->toDateTimeString(),
                                    'dif_min'       => (int) $starTime->diffInMinutes($endTime) . 'm',
                                ];

                                if (!in_array($nTrx, $nResult['transactions'])) {
                                    $nResult['transactions'][] = $nTrx;
                                    $nResult['qty'] += 1;
                                }
                            }
                            $noDt++;
                        }
                    }
                }

                $result[] = $nResult;
            }

            usort($result, function ($a, $b) {
                return $a['qty'] < $b['qty'];
            });

            //dd($result);

            return view('admin.transaction.daily_partner', compact('result'));
        } catch (\Exception $ex) {
            Session::flash('error', $ex);
            return back();
        }
    }

    public function filterDailyReportTrxPerRoom()
    {
        $startDate = Carbon::now('Asia/Jakarta');
        $endDate = Carbon::now('Asia/Jakarta');
        $endDate->addDay();

        $data = [
            'startDate' => $startDate->format('d-m-Y'),
            'endDate'   => $endDate->format('d-m-Y')
        ];

        return view('admin.report.report_trx_per_room_type_filter')->with($data);
    }

    public function generateDailyTrxPerRoom(Request $request)
    {
        try {
            //dd('asdf');
            $rules = array(
                'start_date' => 'required',
                'end_date'  => 'required',
            );

            $message = array(
                'start_date.required'   => 'Tanggal Mulai Wajib dipilih!',
                'end_date.required'     => 'Tanggal Akhir Wajib dipilih!',
            );

            $validator = Validator::make($request->all(), $rules, $message);

            if ($validator->fails()) {
                return Response::json(['error' => $validator->errors()->all()]);
            }

            //get data
            $startDate = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
            $endDate = Carbon::createFromFormat('!d-m-Y', $request->input('end_date'), 'Asia/Jakarta');
            $endDate->addDay();

            $user = Auth::guard('admin')->user();
            $userData = AdminUser::where('id', $user->id)->first();
            $result = [];
            $roomTypes = RoomType::where('branch_id', $userData->branch_id)->get();

            $details = TransactionHeader::with(['room', 'transaction_header'])
                ->whereBetween('created_at', [$startDate->toDateTimeString(), $endDate->toDateTimeString()])
                ->where('package_id', '!=', null)
                ->where('status_id', 9)
                ->whereHas('transaction_header', function ($q) {
                    $q->where('status_id', 9);
                })
                //                    ->whereHas('room', function($q) use($roomType){
                //                        return $q->where('room.type', 'like', '%' . $roomType->name . '%');
                //                    })
                ->get();

            foreach ($roomTypes as $roomType) {
                $nResult = [
                    'room_type'         => $roomType->name,
                    'room_type_display' => $roomType->display,
                    'transactions'      => [],
                ];

                //dd($details);

                $no = 1;
                foreach ($details as $detail) {
                    if ($detail->room->type == $roomType->name) {
                        $trxStartTime = Carbon::parse($detail->transaction_header->start_time);
                        $trxEndTime = Carbon::parse($detail->transaction_header->end_time);

                        $nTrx = [
                            'no'                            => $no,
                            'code'                          => $detail->transaction_header->code,
                            'room_number'                   => $detail->room->number,
                            'start_time'                    => $trxStartTime->locale('id_ID')->toDateTimeString(),
                            'end_time'                      => $trxEndTime->locale('id_ID')->toDateTimeString(),
                            'dif_min'                       => $trxEndTime->diffInMinutes($trxStartTime),
                            'subtotal'                      => $detail->subtotal,
                            'subtotal_after_commission'     => $detail->subtotal_after_commission,
                            'subtotal_str'                  => number_format($detail->subtotal, '0', ',', '.'),
                            'subtotal_after_commission_str' => number_format($detail->subtotal_after_commission, '0', ',', '.'),
                        ];

                        $nResult['transactions'][] = $nTrx;
                        $no++;
                    }
                }

                $result[] = $nResult;
            }

            return view('admin.transaction.daily_room', compact('result'));
        } catch (\Exception $ex) {
            Session::flash('error', $ex);
            return back();
        }
    }

    public function ratingReportFilter()
    {
        $startDate = Carbon::now('Asia/Jakarta');
        $endDate = Carbon::now('Asia/Jakarta');
        $endDate->addDay();

        $data = [
            'startDate' => $startDate->format('d-m-Y'),
            'endDate'   => $endDate->format('d-m-Y')
        ];

        return view('admin.report.report_trx_rating_filter')->with($data);
    }

    public function generateRatingTrxReport(Request $request)
    {
        try {
            $rules = array(
                'start_date' => 'required',
                'end_date'  => 'required',
            );

            $message = array(
                'start_date.required'   => 'Tanggal Mulai Wajib dipilih!',
                'end_date.required'     => 'Tanggal Akhir Wajib dipilih!',
            );

            $validator = Validator::make($request->all(), $rules, $message);

            if ($validator->fails()) {
                return Response::json(['error' => $validator->errors()->all()]);
            }

            //get data
            $startDate = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
            $endDate = Carbon::createFromFormat('!d-m-Y', $request->input('end_date'), 'Asia/Jakarta');
            $endDate->addDay();

            $user = Auth::guard('admin')->user();
            $userData = AdminUser::where('id', $user->id)->first();
            $partners = Partner::with('partner_level')->where('branch_id', $userData->branch_id)->orderBy('number')->get();
            $result = [];

            foreach ($partners as $partner) {
                $nResult = [
                    'id'                => $partner->id,
                    'number'            => $partner->number,
                    'name'              => $partner->name,
                    'alias'             => $partner->alias,
                    'code'              => $partner->code,
                    'level'             => $partner->partner_level->name,
                    'qty'               => 0,
                    'total_treatment'   => 0,
                    'qty_treatement_addon'  => 0,
                    'total_treatment_addon' => 0,
                    'total_commission'  => 0,
                    'grandtotal_qty_treatment'               => 0,
                    'grandtotal_treatment'   => 0,
                    'route' => route('admin.partner.show', ['id' => $partner->id])
                ];

                // if ($userData->branch_id == 1) {
                //     $queryQty = "select p.id as id, p.name as name, sum(dt.price * dt.qty) as total_treatment,
                //     count(tdp.id) as qty from partners p
                //     inner join transaction_detail_partners tdp on tdp.partner_id = p.id
                //     inner join transaction_details dt on tdp.transaction_detail_id = dt.id
                //     inner join transaction_headers th on dt.header_id = th.id
                //     where p.id = " . $partner->id . "
                //     and th.status_id != 16
                //     and dt.package_id is not null
                //     and dt.created_at >= '" . $startDate->format('Y-m-d H:i:s') . "' and dt.created_at <= '" . $endDate->format('Y-m-d H:i:s') . "'
                //     group by p.id, p.name;";
                // } else {
                //     $queryQty = "select p.id as id, p.name as name, sum((dt.price * dt.qty) + dt.extension_amount) as total_treatment,
                //     count(tdp.id) as qty from partners p
                //     inner join transaction_detail_partners tdp on tdp.partner_id = p.id
                //     inner join transaction_details dt on tdp.transaction_detail_id = dt.id
                //     inner join transaction_headers th on dt.header_id = th.id
                //     where p.id = " . $partner->id . "
                //     and th.status_id != 16
                //     and dt.package_id is not null
                //     and dt.created_at >= '" . $startDate->format('Y-m-d H:i:s') . "' and dt.created_at <= '" . $endDate->format('Y-m-d H:i:s') . "'
                //     group by p.id, p.name;";
                // }
                $queryQty = "select p.id as id, p.name as name, sum(dt.price * dt.qty) as total_treatment,
                    count(tdp.id) as qty from partners p
                    inner join transaction_detail_partners tdp on tdp.partner_id = p.id
                    inner join transaction_details dt on tdp.transaction_detail_id = dt.id
                    inner join transaction_headers th on dt.header_id = th.id
                    where p.id = " . $partner->id . "
                    and th.status_id != 16
                    and dt.package_id is not null
                    and dt.created_at >= '" . $startDate->format('Y-m-d H:i:s') . "' and dt.created_at <= '" . $endDate->format('Y-m-d H:i:s') . "'
                    group by p.id, p.name;";

                $queryQtyAddon = "
                    SELECT
                        p.id AS id,
                        p.name AS name,
                        SUM(COALESCE(dt.price,0) * COALESCE(dt.qty,0)) AS total_treatment,
                        SUM(COALESCE(dt.qty,0)) AS qty
                    FROM partners p
                    INNER JOIN transaction_detail_partners tdp ON tdp.partner_id = p.id
                    INNER JOIN transaction_details dt ON tdp.transaction_detail_id = dt.id
                    INNER JOIN items itm ON dt.item_id = itm.id
                    INNER JOIN transaction_headers th ON dt.header_id = th.id
                    WHERE p.id = " . $partner->id . "
                        AND th.status_id <> 16
                        AND dt.item_id IS NOT NULL
                        AND itm.category_id = 27
                    and dt.created_at >= '" . $startDate->format('Y-m-d H:i:s') . "' and dt.created_at <= '" . $endDate->format('Y-m-d H:i:s') . "'
                    GROUP BY p.id, p.name;
                ";

                $queryCommission = "select p.id as id, p.name as name, sum(hst.commission_add) as total_commission from partners p
                    inner join commission_partner_histories hst on hst.partner_id = p.id
                    where p.id = " . $partner->id . "
                    and hst.transaction_date >= '" . $startDate->format('Y-m-d H:i:s') . "' and hst.transaction_date <= '" . $endDate->format('Y-m-d H:i:s') . "'
                    group by p.id, p.name;";

                $qtyData = DB::select($queryQty);
                $qtyTambahan = DB::select($queryQtyAddon);
                $commissionData = DB::select($queryCommission);
                if (count($qtyData) > 0) {
                    $nResult['qty'] = $qtyData[0]->qty;
                    $nResult['total_treatment'] = (int)$qtyData[0]->total_treatment;
                    $nResult['grandtotal_qty_treatment'] += $qtyData[0]->qty;
                    $nResult['grandtotal_treatment'] += (int)$qtyData[0]->total_treatment;
                }
                if (count($qtyTambahan) > 0) {
                    $nResult['qty_treatement_addon'] = $qtyTambahan[0]->qty;
                    $nResult['total_treatment_addon'] = (int)$qtyTambahan[0]->total_treatment;
                    $nResult['grandtotal_qty_treatment'] += $qtyTambahan[0]->qty;
                    $nResult['grandtotal_treatment'] += (int)$qtyTambahan[0]->total_treatment;
                }
                if (count($commissionData) > 0) {
                    $nResult['total_commission'] = $commissionData[0]->total_commission;
                }

                $result[] = $nResult;
            }

                usort($result, function ($a, $b) {
                    return $a['grandtotal_treatment'] < $b['grandtotal_treatment'];
                });
            // if ($user->branch_id != 4) {
            //     usort($result, function ($a, $b) {
            //         return $a['qty'] < $b['qty'];
            //     });
            // }
            // usort($result, function ($a, $b) {
            //     return $a['number'] > $b['number'];
            // });

            return \response($result);

            //            return view('admin.transaction.rating_partner', compact('result'));
        } catch (\Exception $ex) {
            Session::flash('error', $ex);
            return back();
        }
    }

    public function generateRatingTrxReportASdf(Request $request)
    {
        try {
            $rules = array(
                'start_date' => 'required',
                'end_date'  => 'required',
            );

            $message = array(
                'start_date.required'   => 'Tanggal Mulai Wajib dipilih!',
                'end_date.required'     => 'Tanggal Akhir Wajib dipilih!',
            );

            $validator = Validator::make($request->all(), $rules, $message);

            if ($validator->fails()) {
                return Response::json(['error' => $validator->errors()->all()]);
            }

            //get data
            $startDate = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
            $endDate = Carbon::createFromFormat('!d-m-Y', $request->input('end_date'), 'Asia/Jakarta');
            $endDate->addDay();

            $user = Auth::guard('admin')->user();
            $userData = AdminUser::where('id', $user->id)->first();
            $partners = Partner::where('branch_id', $userData->branch_id)->orderBy('number')->get();
            $result = [];
            foreach ($partners as $partner) {
                $nResult = [
                    'number'        => $partner->number,
                    'name'          => $partner->name,
                    'alias'         => $partner->alias,
                    'code'          => $partner->code,
                    'qty'           => 0,
                    'total_treatment' => 0,
                    'total_extend' => 0,
                    'transactions'  => [],
                ];

                $details = TransactionDetail::with('partners')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->where('package_id', '!=', null)
                    ->get();

                foreach ($details as $detail) {
                    $noDt = 1;
                    foreach ($detail->partners as $dbPartner) {
                        if ($dbPartner->id == $partner->id) {
                            //got the same id for partner in transaction
                            $header = TransactionHeader::where('id', $detail->header_id)->first();
                            if ($header != null) {
                                $starTime = Carbon::parse($header->start_time);
                                $endTime = Carbon::parse($header->end_time);
                                $locker = Locker::find($header->locker_id);
                                $nTrx = [
                                    'no'            => $noDt,
                                    'locker'        => $locker->number,
                                    'code'          => $header->code,
                                    'start_time'    => $starTime->toDateTimeString(),
                                    'end_time'      => $endTime->toDateTimeString(),
                                    'dif_min'       => $endTime->diffInMinutes($starTime),
                                ];

                                if (!in_array($nTrx, $nResult['transactions'])) {
                                    $nResult['transactions'][] = $nTrx;
                                    $nResult['qty']++;
                                    $nResult['total_treatment'] += ($detail->price * $detail->qty);
                                    if ($detail->is_extended == 1) {
                                        $nResult['total_extend'] += $detail->extension_amount * $detail->extension_times;
                                    }
                                }
                            }
                            $noDt++;
                        }
                    }
                }

                $result[] = $nResult;
            }

            if ($user->branch_id != 4) {
                usort($result, function ($a, $b) {
                    return $a['qty'] < $b['qty'];
                });
            }
            if ($user->branch_id == 3) {
                usort($result, function ($a, $b) {
                    return $a['number'] > $b['number'];
                });
            }

            return view('admin.transaction.rating_partner', compact('result'));
        } catch (\Exception $ex) {
            Session::flash('error', $ex);
            return back();
        }
    }

    public function reportDailyTrxPerPayment()
    {
        $startDate = Carbon::now('Asia/Jakarta');
        $endDate = Carbon::now('Asia/Jakarta');
        $endDate->addDay();

        $user = Auth::guard('admin')->user();
        $userData = AdminUser::where('id', $user->id)->first();
        $types = PosPaymentType::where('branch_id', $userData->branch_id)
            ->where('status_id', 1)->get();

        $data = [
            'startDate' => $startDate->format('d-m-Y'),
            'endDate'   => $endDate->format('d-m-Y'),
            'types'     => $types,
        ];

        return view('admin.report.report_trx_per_payment')->with($data);
    }

    public function generateDailyTrxPerPayment(Request $request)
    {
        try {
            $period = $request->input('period');
            $type = $request->input('type') ?? 'CASH';
            // if (empty($period)) {
            //     $startDate = Carbon::now('Asia/Jakarta')->startOfMonth();
            //     $endDate = Carbon::now('Asia/Jakarta')->endOfMonth();
            //     $period = $startDate->format('d-m-Y') . ' - ' . $endDate->format('d-m-Y');
            // }
            if (str_contains($period, ' - ') === false) {
                $startDate = Carbon::createFromFormat('Y-m-d', $period, 'Asia/Jakarta')->startOfDay();
                $endDate = Carbon::createFromFormat('Y-m-d', $period, 'Asia/Jakarta')->endOfDay();
            } else {
                $tmpPeriod = explode(' - ', $period);
                $startDate = Carbon::createFromFormat('Y-m-d', $tmpPeriod[0], 'Asia/Jakarta')->startOfDay();
                $endDate = Carbon::createFromFormat('Y-m-d', $tmpPeriod[1], 'Asia/Jakarta')->endOfDay();
            }

            $user = Auth::guard('admin')->user();
            $userData = AdminUser::where('id', $user->id)->first();
            $typeArr = [];
            $no = 1;
            if (str_contains($type, ',') === false) {
                $paymentType = PosPaymentType::where('branch_id', $userData->branch_id)
                    ->where('id', $type)
                    ->first();
                if ($paymentType == null) {
                    throw new \Exception('Payment type tidak ditemukan!');
                }

                $trxPayment = TransactionPayment::join('transaction_invoices', 'transaction_payments.invoice_id', '=', 'transaction_invoices.id')
                    ->select(
                        'transaction_invoices.id',
                        'transaction_invoices.code',
                        'transaction_invoices.header_ids',
                        'transaction_invoices.return_value',
                        'transaction_payments.amount',
                    )
                    ->where('transaction_invoices.status_id', '=', 9)
                    ->where('transaction_invoices.branch_id', '=', $user->branch_id)
                    ->where('transaction_payments.payment_type_id', '=', $paymentType->id)
                    ->whereBetween('transaction_invoices.created_at', [$startDate, $endDate])
                    ->get();
            }
            else{
                $typeArr = explode(',', $type);
                $paymentType = PosPaymentType::where('branch_id', $userData->branch_id)
                    ->where('id', $typeArr[0])
                    ->first();
                $trxPayment = TransactionPayment::join('transaction_invoices', 'transaction_payments.invoice_id', '=', 'transaction_invoices.id')
                    ->select(
                        'transaction_invoices.id',
                        'transaction_invoices.code',
                        'transaction_invoices.header_ids',
                        'transaction_invoices.return_value',
                        'transaction_payments.amount',
                    )
                    ->where('transaction_invoices.status_id', '=', 9)
                    ->where('transaction_invoices.branch_id', '=', $user->branch_id)
                    ->whereIn('transaction_payments.payment_type_id', $typeArr)
                    ->whereBetween('transaction_invoices.created_at', [$startDate, $endDate])
                    ->get();
            }


            $result = [];
            $total = 0;
            foreach ($trxPayment as $trx) {
                $tmpAmount = 0;
                $nResult = [
                    'no'            => $no,
                    'code'          => $trx->code,
                    'customers'     => '-',
                    'amount'        => 0,
                    'amount_str'    => number_format($trx->amount, '0', ',', '.'),
                ];

                $nResult['amount'] = $trx->amount - $trx->return_value;
                $nResult['amount_str'] = number_format($nResult['amount'], '0', ',', '.');

                $headerIds = explode('#', $trx->header_ids);
                $headers = TransactionHeader::whereIn('id', $headerIds)->get();
                if ($headers != null) {
                    $nResult['customers'] = implode(', ', array_column($headers->toArray(), 'member_name'));
                }


                $result[] = $nResult;
                $total += $nResult['amount'];
                $no++;
            }

            $data = [
                'type'      => $paymentType,
                'start'     => $startDate,
                'end'       => $endDate,
                'result'    => $result,
                'total'     => number_format($total, '0', ',', '.'),
            ];

            return Response::json(['status' => 'success', 'data' => $data]);
        } catch (\Exception $ex) {
            return Response::json(['status' => 'error', 'message' => $ex->getMessage()]);
        }
    }

    public function reportDailyRecapFilter()
    {
        $startDate = Carbon::now('Asia/Jakarta');
        $endDate = Carbon::now('Asia/Jakarta');
        //$endDate->addDay();

        $user = Auth::guard('admin')->user();
        $userData = AdminUser::where('id', $user->id)->first();

        $data = [
            'startDate' => $startDate->format('d-m-Y'),
            'endDate'   => $endDate->format('d-m-Y'),
        ];

        return view('admin.report.report_recap_filter')->with($data);
    }

    public function generateDailyRecapReport(Request $request)
    {
        try {
            $period = $request->input('period');
            $type = $request->input('type') ?? 'CASH';
            // if (empty($period)) {
            //     $startDate = Carbon::now('Asia/Jakarta')->startOfMonth();
            //     $endDate = Carbon::now('Asia/Jakarta')->endOfMonth();
            //     $period = $startDate->format('d-m-Y') . ' - ' . $endDate->format('d-m-Y');
            // }
            if (str_contains($period, ' - ') === false) {
                $startDate = Carbon::createFromFormat('Y-m-d', $period, 'Asia/Jakarta')->startOfDay();
                $endDate = Carbon::createFromFormat('Y-m-d', $period, 'Asia/Jakarta')->endOfDay();
            } else {
                $tmpPeriod = explode(' - ', $period);
                $startDate = $tmpPeriod[0];
                $endDate = $tmpPeriod[1] ?? Carbon::now('Asia/Jakarta')->endOfDay()->format('Y-m-d');
            }

            $user = Auth::guard('admin')->user();
            $userData = AdminUser::where('id', $user->id)->first();

            $headers = TransactionHeader::where('branch_id', $userData->branch_id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('status_id', '!=', 16)
                ->get();

            //            dd($headers);
            $headerIds = $headers->pluck('id');

            $details = TransactionDetail::whereIn('header_id', $headerIds)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('status_id', '!=', 16)
                ->get();

            $totalCustomer = count($headers);

            $totalTreatment = $details->filter(function ($detail) {
                return !is_null($detail->package_id);
            })->count();

            //            dd($totalCustomer, $totalTreatment);

            $allPackage = [];
            $packages = Package::where('branch_id', $userData->branch_id)->get();
            foreach ($packages as $package) {
                $qtyP = $details->filter(fn($detail) => $detail->package_id == $package->id)->count();

                $nPackage = [
                    'name'          => $package->name,
                    'qty'           => $qtyP,
                    'package_id'    => $package->id,
                ];

                $allPackage[] = $nPackage;
            }

            //            dd($allPackage);

            $allItem = [];
            $items = Item::where('branch_id', $userData->branch_id)->get();
            foreach ($items as $item) {
                $qtyI = $details->filter(fn($detail) => $detail->item_id == $item->id)->count();

                $nItem = [
                    'name'          => $item->name,
                    'qty'           => $qtyI,
                    'total_price'   => $qtyI * $item->price,
                    'item_id'       => $item->id,
                    'code'          => $item->code,
                ];

                $allItem[] = $nItem;
            }

            //            dd($allItem);

            $types = PosPaymentType::where('status_id', 1)
                ->where('branch_id', $userData->branch_id)
                ->get();

            $payments = [];
            $tmpTotal = 0;

            $paymentTypes = TransactionPayment::join('transaction_invoices', 'transaction_payments.invoice_id', '=', 'transaction_invoices.id')
                ->select(
                    'transaction_invoices.id',
                    'transaction_invoices.code',
                    'transaction_invoices.header_ids',
                    'transaction_invoices.return_value',
                    'transaction_payments.amount',
                    'transaction_payments.payment_type_code'
                )
                ->whereIn('transaction_invoices.status_id', [9, 3])
                ->where('transaction_invoices.branch_id', '=', $user->branch_id)
                ->whereBetween('transaction_invoices.created_at', [$startDate, $endDate])
                ->get();

            foreach ($types as $type) {
                $total = 0;

                // Filter only transactions that match the current payment type
                $filteredPayments = $paymentTypes->filter(fn($history) => $history->payment_type_code == $type->code);

                foreach ($filteredPayments as $history) {
                    $tmpAmount = $history->amount;

                    if ($history->payment_type_code == 'CASH' && $history->return_value > 0) {
                        $tmpAmount -= $history->return_value;
                    }

                    $total += $tmpAmount;
                }

                $payments[] = [
                    'payment'   => $type->description,
                    'total'     => $total,
                    'total_str' => number_format($total, 2, ',', '.'),
                ];

                $tmpTotal += $total;
            }

            $totalStr = number_format($tmpTotal, '0', ',', '.');

            $data = [
                'branchId'              => $userData->branch_id,
                'totalCustomer'         => $totalCustomer,
                'totalTreatment'        => $totalTreatment,
                'allItem'               => $allItem,
                'allPackage'            => $allPackage,
                'payments'              => $payments,
                'totalStr'              => $totalStr,
                'total'                 => $tmpTotal,
                'startDate'             => $startDate,
                'endDate'               => $endDate,
                'diffDay'               => Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)),
            ];

            // if ($request->input('report_type') == 1) {
            //     return view('admin.transaction.daily_recap_new')->with($data);
            // } else if ($request->input('report_type') == 2) {
            //     return view('admin.transaction.daily_recap_without_total')->with($data);
            // } else if ($request->input('report_type') == 3) {
            //     return view('admin.transaction.daily_recap_total_only')->with($data);
            // } else if ($request->input('report_type') == 4) {
            //     return view('admin.transaction.daily_recap_right')->with($data);
            // } else if ($request->input('report_type') == 5) {
            //     return view('admin.transaction.daily_recap_oura')->with($data);
            // }

            return response()->json([
                'status' => 'success',
                'data' => $data,
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ]);
        }
    }

    public function filterDailyFnbReport()
    {
        $startDate = Carbon::today('Asia/Jakarta');

        $data = [
            'startDate' => $startDate->format('d-m-Y'),
            'endDate' => $startDate->copy()->format('d-m-Y'),
        ];

        return view('admin.report.report_trx_fnb_filter')->with($data);
    }

    public function generateDailyFnbReport(Request $request)
    {
        try {
            $rules = array(
                'start_date' => 'required',
                'end_date' => 'required',
            );

            $message = array(
                'start_date.required' => 'Tanggal Mulai Wajib dipilih!',
                'end_date.required' => 'Tanggal Mulai Wajib dipilih!',
            );

            $validator = Validator::make($request->all(), $rules, $message);

            if ($validator->fails()) {
                return Response::json(['error' => $validator->errors()->all()]);
            }

            $startDateStr = $request->input('start_date');
            $endDateStr = $request->input('end_date');

            //get data
            $startDate = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
            $endDate = Carbon::createFromFormat('!d-m-Y', $request->input('end_date'), 'Asia/Jakarta');
            $endDate->addDays(1);

            //    $user = Auth::guard('admin')->user();
            //            $userData = AdminUser::where('id', $user->id)->first();
            $items = Item::whereIn('category_id', [2, 4])
                ->orderBy('name')
                ->get();
            $result = [];
            $no = 1;
            foreach ($items as $item) {
                $nResult = [
                    'no' => $no,
                    'code' => $item->code,
                    'name' => $item->name,
                    'qty' => 0,
                    'transactions' => []
                ];

                $details = TransactionDetail::with('item')
                    ->whereBetween('created_at', array($startDate->toDateTimeString(), $endDate->toDateTimeString()))
                    ->whereNotNull('item_id')
                    ->get();

                $headerIds = [];
                foreach ($details as $detail) {
                    $noDt = 1;
                    if ($detail->item_id == $item->id) {
                        //got the same id for partner in transaction
                        if (!in_array($detail->header_id, $headerIds)) {
                            $headerIds[] = $detail->header_id;
                            $nResult['qty'] += $detail->qty;
                        }
                    }
                }

                $result[] = $nResult;
                $no++;
            }

            $data = [
                'startDate' => $startDateStr,
                'endDate' => $endDateStr,
                'result' => $result
            ];

            return view('admin.transaction.daily_fnb')->with($data);
        } catch (\Exception $ex) {
            Session::flash('error', $ex);
            return back();
        }
    }

    public function filterDailyWithMtdReport()
    {
        $startDate = Carbon::today('Asia/Jakarta');
        $maxDate = $startDate->copy()->addDays(2);
        //$minDate = $startDate->copy()->subDays(31);
        $minDate = $startDate->copy()->subMonths(4);

        $data = [
            'startDate' => $startDate->format('d-m-Y'),
            'maxDate' => $maxDate->format('d-m-Y'),
            'minDate' => $minDate->format('d-m-Y')
        ];

        return view('admin.report.report_trx_daily_mtd')->with($data);
    }

    public function generateDailyWithMtdReportNew(Request $request)
    {
        try {
            //ini_set('max_execution_time', 600);

            $rules = array(
                'start_date' => 'required',
            );

            $message = array(
                'start_date.required' => 'Tanggal Mulai Wajib dipilih!',
            );

            $validator = Validator::make($request->all(), $rules, $message);

            if ($validator->fails()) {
                return Response::json(['error' => $validator->errors()->all()]);
            }

            //setup dates
            $startDate = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
            $endDate = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
            $endDate->addDay();
            $startDateMtd = Carbon::create($startDate->year, $startDate->month, 1, 0, 0, 0);
            $startDateYtd = $endDate->copy()->startOfYear();

            $user = Auth::guard('admin')->user();

            //querys
            $sales = [];
            $dbPackages = Package::where('branch_id', $user->branch_id)->get();
            foreach ($dbPackages as $dbPackage) {
                $dailyQuery = "select sum((dt.qty * dt.price)) as total from transaction_details dt
                inner join transaction_headers hd on hd.id = dt.header_id
                where hd.status_id = 9
                and dt.package_id = " . $dbPackage->id . "
                and dt.created_at >= '" . $startDate->format('Y-m-d H:i:s') . "'
                and dt.created_at <= '" . $endDate->format('Y-m-d H:i:s') . "'
                and hd.branch_id =" . $user->branch_id;

                $mtdQuery = "select sum((dt.qty * dt.price)) as total from transaction_details dt
                inner join transaction_headers hd on hd.id = dt.header_id
                where hd.status_id = 9
                and dt.package_id = " . $dbPackage->id . "
                and dt.created_at >= '" . $startDateMtd->format('Y-m-d H:i:s') . "'
                and dt.created_at <= '" . $endDate->format('Y-m-d H:i:s') . "'
                and hd.branch_id =" . $user->branch_id;

                $ytdQuery = "select sum((dt.qty * dt.price)) as total from transaction_details dt
                inner join transaction_headers hd on hd.id = dt.header_id
                where hd.status_id = 9
                and dt.package_id = " . $dbPackage->id . "
                and dt.created_at >= '" . $startDateYtd->format('Y-m-d H:i:s') . "'
                and dt.created_at <= '" . $endDate->format('Y-m-d H:i:s') . "'
                and hd.branch_id =" . $user->branch_id;

                $nPackage = [
                    'name'  => $dbPackage->name,
                    'daily' => DB::select($dailyQuery),
                    'mtd'   => DB::select($mtdQuery),
                    'ytd'   => DB::select($ytdQuery),
                ];

                $sales[] = $nPackage;
            }

            //            dd($sales);

            // add on
            $otherItemQuery = "
                select sum((dt.qty * dt.price)) as total from transaction_details dt
                inner join items item on dt.item_id = item.id
                inner join transaction_headers hd on dt.header_id = hd.id
                where hd.status_id = 9
                and hd.branch_id = " . $user->branch_id . "
                and dt.item_id is not null
                and dt.created_at >= '" . $startDate->format('Y-m-d H:i:s') . "'
                and dt.created_at <= '" . $endDate->format('Y-m-d H:i:s') . "'";
            $otherItemQueryMtd = "
                select sum((dt.qty * dt.price)) as total from transaction_details dt
                inner join items item on dt.item_id = item.id
                inner join transaction_headers hd on dt.header_id = hd.id
                where hd.status_id = 9
                and hd.branch_id = " . $user->branch_id . "
                and dt.item_id is not null
                and dt.created_at >= '" . $startDateMtd->format('Y-m-d H:i:s') . "'
                and dt.created_at <= '" . $endDate->format('Y-m-d H:i:s') . "'";
            $otherItemQueryYtd = "
                select sum((dt.qty * dt.price)) as total from transaction_details dt
                inner join items item on dt.item_id = item.id
                inner join transaction_headers hd on dt.header_id = hd.id
                where hd.status_id = 9
                and hd.branch_id = " . $user->branch_id . "
                and dt.item_id is not null
                and dt.created_at >= '" . $startDateYtd->format('Y-m-d H:i:s') . "'
                and dt.created_at <= '" . $endDate->format('Y-m-d H:i:s') . "'";

            $otherItem = [
                'name'  => 'Add On',
                'daily' => DB::select($otherItemQuery),
                'mtd'   => DB::select($otherItemQueryMtd),
                'ytd'   => DB::select($otherItemQueryYtd),
            ];
            $sales[] = $otherItem;

            //            dd($sales);

            //Diskon
            $discountQuery = "select sum(hd.voucher_discount_value) as total from transaction_invoices hd where
                hd.branch_id = " . $user->branch_id . "
                and hd.status_id = 9
                and hd.created_at >= '" . $startDate->format('Y-m-d H:i:s') . "'
                and hd.created_at <= '" . $endDate->format('Y-m-d H:i:s') . "'";

            $discountQueryMtd = "select sum(hd.voucher_discount_value) as total from transaction_invoices hd where
                hd.branch_id = " . $user->branch_id . "
                and hd.status_id = 9
                and hd.created_at >= '" . $startDateMtd->format('Y-m-d H:i:s') . "'
                and hd.created_at <= '" . $endDate->format('Y-m-d H:i:s') . "'";

            $discountQueryYtd = "select sum(hd.voucher_discount_value) as total from transaction_invoices hd where
                hd.branch_id = " . $user->branch_id . "
                and hd.status_id = 9
                and hd.created_at >= '" . $startDateYtd->format('Y-m-d H:i:s') . "'
                and hd.created_at <= '" . $endDate->format('Y-m-d H:i:s') . "'";

            $discount = [
                'name'  => 'Diskon',
                'daily' => DB::select($discountQuery),
                'mtd'   => DB::select($discountQueryMtd),
                'ytd'   => DB::select($discountQueryYtd),
            ];

            //            dd($discount);

            //Payments
            $payments = [];
            $dbPayments = PosPaymentType::where('branch_id', $user->branch_id)->get();

            $totalPayment = [
                'name'  => 'Pendapatan',
                'daily' => 0,
                'mtd'   => 0,
                'ytd'   => 0,
            ];

            foreach ($dbPayments as $db) {
                $tmpQueryPayment = "select sum(py.amount) as total from transaction_payments py
                inner join transaction_invoices hd on py.invoice_id = hd.id
                inner join pos_payment_types ty on ty.id = py.payment_type_id
                where
                py.payment_type_id = " . $db->id . "
                and ty.status_id = 1
                and hd.status_id = 9
                and hd.created_at >= '" . $startDate->format('Y-m-d H:i:s') . "'
                and hd.created_at <= '" . $endDate->format('Y-m-d H:i:s') . "'";

                $tmpQueryPaymentMtd = "select sum(py.amount) as total from transaction_payments py
                inner join transaction_invoices hd on py.invoice_id = hd.id
                inner join pos_payment_types ty on ty.id = py.payment_type_id
                where
                py.payment_type_id = " . $db->id . "
                and ty.status_id = 1
                and hd.status_id = 9
                and hd.created_at >= '" . $startDateMtd->format('Y-m-d H:i:s') . "'
                and hd.created_at <= '" . $endDate->format('Y-m-d H:i:s') . "'";

                $tmpQueryPaymentYtd = "select sum(py.amount) as total from transaction_payments py
                inner join transaction_invoices hd on py.invoice_id = hd.id
                inner join pos_payment_types ty on ty.id = py.payment_type_id
                where
                py.payment_type_id = " . $db->id . "
                and ty.status_id = 1
                and hd.status_id = 9
                and hd.created_at >= '" . $startDateYtd->format('Y-m-d H:i:s') . "'
                and hd.created_at <= '" . $endDate->format('Y-m-d H:i:s') . "'";

                $nPayment = [
                    'name'  => $db->description,
                    'daily' => DB::select($tmpQueryPayment),
                    'mtd'   => DB::select($tmpQueryPaymentMtd),
                    'ytd'   => DB::select($tmpQueryPaymentYtd),
                ];
                $payments[] = $nPayment;
                $totalPayment['daily'] += $nPayment['daily'][0]->total;
                $totalPayment['mtd'] += $nPayment['mtd'][0]->total;
                $totalPayment['ytd'] += $nPayment['ytd'][0]->total;
            }

            //            dd($payments);

            //Room Revenue
            $revenueQueryToday = "select sum(dt.subtotal) as total, sum(dt.voucher_discount_value) as discount, count(dt.id) as dtCount from transaction_invoices dt
                where dt.branch_id = '" . $user->branch_id . "'
                and dt.status_id = 9
                and dt.created_at >= '" . $startDate->format('Y-m-d H:i:s') . "'
                and dt.created_at <= '" . $endDate->format('Y-m-d H:i:s') . "';";
            $revenueQueryMtd = "select sum(dt.subtotal) as total, sum(dt.voucher_discount_value) as discount, count(dt.id) as dtCount from transaction_invoices dt
                where dt.branch_id = '" . $user->branch_id . "'
                and dt.status_id = 9
                and dt.created_at >= '" . $startDateMtd->format('Y-m-d H:i:s') . "'
                and dt.created_at <= '" . $endDate->format('Y-m-d H:i:s') . "';";
            $revenueQueryYtd = "select sum(dt.subtotal) as total, sum(dt.voucher_discount_value) as discount, count(dt.id) as dtCount from transaction_invoices dt
                where dt.branch_id = '" . $user->branch_id . "'
                and dt.status_id = 9
                and dt.created_at >= '" . $startDateYtd->format('Y-m-d H:i:s') . "'
                and dt.created_at <= '" . $endDate->format('Y-m-d H:i:s') . "';";
            $revToday = DB::select($revenueQueryToday);
            //            dd($revToday);
            $revMtd = DB::select($revenueQueryMtd);
            //            dd($revMtd);
            $revYtd = DB::select($revenueQueryYtd);
            //            dd($revYtd);

            $roomRevenue = [
                'today'         => $revToday[0]->total,
                'discount'      => $revToday[0]->discount,
                'mtdRoom'       => $revMtd[0]->total,
                'mtdRoomDisc'   => $revMtd[0]->discount,
                'ytdRoom'       => $revYtd[0]->total,
                'ytdRoomDisc'   => $revYtd[0]->discount,
            ];

            //            dd($roomRevenue);

            //ARR
            $avgRate = [
                'today'     => $revToday[0]->total == 0 ? 0 : $revToday[0]->total / $revToday[0]->dtCount,
                'mtd'       => $revMtd[0]->total == 0 ? 0 : $revMtd[0]->total / $revMtd[0]->dtCount,
                'ytd'       => $revYtd[0]->total == 0 ? 0 : $revYtd[0]->total / $revYtd[0]->dtCount,
            ];

            //dd($sales, $payments, $occs, $roomRevenue, $avgRate, $totalPayment);

            $data = [
                'branchId'      => $user->branch_id,
                'sales'         => $sales,
                'payments'      => $payments,
                'totalPayment'  => $totalPayment,
                'roomRevenue'   => $roomRevenue,
                'avgRate'       => $avgRate,
                'startDate'     => $startDate->locale('id_ID')->toDateString(),
                'endDate'       => $endDate->locale('id_ID')->toDateString(),
                'diffDay'       => $endDate->diffInDays($startDate),
                'discount'      => $discount,
            ];

            return \response($data);

            //            return view('admin.transaction.daily_mtd_new')->with($data);
        } catch (\Exception $ex) {
            Log::error($ex);
            Session::flash('error', $ex);
            return back();
        }
    }

    public function generateDailyWithMtdReport(Request $request)
    {
        try {
            ini_set('max_execution_time', 600);

            $rules = array(
                'start_date' => 'required',
            );

            $message = array(
                'start_date.required' => 'Tanggal Mulai Wajib dipilih!',
            );

            $validator = Validator::make($request->all(), $rules, $message);

            if ($validator->fails()) {
                return Response::json(['error' => $validator->errors()->all()]);
            }

            //get data
            $startDate = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
            $endDate = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
            $endDate->addDay();

            $user = Auth::guard('admin')->user();
            $userData = AdminUser::where('id', $user->id)->first();

            $headers = TransactionHeader::where('branch_id', $userData->branch_id)
                ->where('status_id', '=', 9)
                ->whereBetween('created_at', [$startDate->toDateTimeString(), $endDate->toDateTimeString()])
                ->whereNotIn('locker_id', ['1', '2', '3', '5', '6', '100', '101', '102', '103', '104', '105', '106', '107', '108', '109', '110'])
                ->get();

            //            dd(TransactionHeader::where('branch_id', $userData->branch_id)
            //                ->whereBetween('created_at', [$startDate->toDateTimeString(), $endDate->toDateTimeString()])
            //                ->whereNotIn('locker_id', ['1', '2', '3', '5', '6', '100', '101', '102', '103', '104', '105', '106', '107', '108', '109', '110'])
            //                ->sum('grand_total'));

            $totals = [
                'today'         => TransactionHeader::where('branch_id', $userData->branch_id)
                    ->whereBetween('created_at', [$startDate->toDateTimeString(), $endDate->toDateTimeString()])
                    ->whereNotIn('locker_id', ['1', '2', '3', '5', '6', '100', '101', '102', '103', '104', '105', '106', '107', '108', '109', '110'])
                    ->sum('grand_total'),
                'todayDiscount' => 0,
                'mtd'           => 0,
                'mtdDiscount'   => 0,
                'ytd'           => 0,
                'ytdDiscount'   => 0,
            ];

            //Room Revenue Start
            $roomRevenue = [
                'today'         => 0,
                'discount'     => 0,
                'mtdRoom'       => 0,
                'mtdRoomDisc'   => 0,
                'ytdRoom'       => 0,
                'ytdRoomDisc'   => 0,
            ];
            //Room Revenue End

            $rooms = [];

            $packages = Package::where('branch_id', $userData->branch_id)->get();
            foreach ($packages as $package) {
                $nData = [
                    'name'  => $package->name,
                    'today' => 0,
                    'mtd'   => 0,
                    'ytd'   => 0,
                ];

                $rooms[] = $nData;
            }
            $rooms[] = $nData;
            $items = [
                'oil_massage'  => [
                    'today' => 0,
                    'mtd'   => 0,
                    'ytd'   => 0,
                ],
                'facility'  => [
                    'today' => 0,
                    'mtd'   => 0,
                    'ytd'   => 0,
                ],
                'fnb'  => [
                    'today' => 0,
                    'mtd'   => 0,
                    'ytd'   => 0,
                ],
                'hotstone'  => [
                    'today' => 0,
                    'mtd'   => 0,
                    'ytd'   => 0,
                ],
                'scrub'  => [
                    'today' => 0,
                    'mtd'   => 0,
                    'ytd'   => 0,
                ],
                'kimo'  => [
                    'today' => 0,
                    'mtd'   => 0,
                    'ytd'   => 0,
                ],
                'foc'  => [
                    'today' => 0,
                    'mtd'   => 0,
                    'ytd'   => 0,
                ],
                'discount'  => [
                    'today' => 0,
                    'mtd'   => 0,
                    'ytd'   => 0,
                ],
            ];

            $tmpDiscount = 0;
            $count = 0;
            $headerIds = '';
            $headerFocs = '';
            foreach ($headers as $header) {
                if ($header->foc_type == 'no') {
                    $tmpDiscount += $header->discount;
                }
                $tmpDt = TransactionDetail::where('header_id', $header->id)->where('package_id', '!=', null)
                    ->get();

                $tmpDtFacility = TransactionDetail::where('header_id', $header->id)->where('is_facility_only', '=', 1)
                    ->get();
                $items['facility']['today'] += (180000 * count($tmpDtFacility));

                //add is cut
                $tmpIsCut = TransactionDetail::where('header_id', $header->id)->where('is_cut', '=', 1)
                    ->get();
                $idx = count($rooms);
                $idx--;
                if (count($tmpIsCut) > 0) {
                    $rooms[$idx]['today'] += $tmpIsCut[0]->price * count($tmpIsCut);
                    //$totals['today'] += $tmpIsCut[0]->price * count($tmpIsCut);
                }

                //sales service
                foreach ($tmpDt as $dt) {
                    $package = Package::where('id', $dt->package_id)->first();
                    if ($package != null) {
                        if ($header->foc_type != 'no' && !empty($header->foc_type)) {
                            //counting FOC
                            $items['foc']['today'] += $package->price;
                            $count++;
                            $headerIds .= $header->id . ', ';
                            $headerFocs .= $header->foc_type . ', ';
                        }

                        for ($i = 0; $i < count($rooms); $i++) {
                            if ($rooms[$i]['name'] == $package->name) {
                                $rooms[$i]['today'] += $package->price;
                            }
                        }

                        //$totals['today'] += $package->price;
                        //$tmpDiscount += $dt->discount_amount;
                        $roomRevenue['today'] += $package->price;
                        $roomRevenue['discount'] += $dt->discount_amount;
                    }
                }

                //counting oil
                $tmpOilMsgs = Item::where('name', 'like', '%oil%')->where('branch_id', $userData->branch_id)->get();
                foreach ($tmpOilMsgs as $oilMsg) {
                    $tmpTrx = TransactionDetail::where('header_id', $header->id)->where('item_id', '=', $oilMsg->id)
                        ->get();

                    foreach ($tmpTrx as $oil) {
                        $items['oil_massage']['today'] += ($oil->qty * $oil->price);
                        //$totals['today'] += ($oil->qty * $oil->price);
                        //$tmpDiscount += $oil->discount_amount;
                    }
                }

                //counting FNB
                $tmpDtFnb = TransactionDetail::where('header_id', $header->id)->where('is_fnb', '=', 1)
                    ->get();
                foreach ($tmpDtFnb as $fnb) {
                    $items['fnb']['today'] += ($fnb->qty * $fnb->price);
                    //$totals['today'] += ($fnb->qty * $fnb->price);
                    //$tmpDiscount += $fnb->discount_amount;
                }

                //counting hotstone
                $tmpHotStone = Item::where('name', 'like', '%hotstone%')->where('branch_id', $userData->branch_id)->first();
                if ($tmpHotStone != null) {
                    $tmpDtHotStone = TransactionDetail::where('header_id', $header->id)->where('item_id', '=', $tmpHotStone->id)
                        ->get();

                    foreach ($tmpDtHotStone as $hotStone) {
                        $items['hotstone']['today'] += ($hotStone->qty * $hotStone->price);
                        //$totals['today'] += ($hotStone->qty * $hotStone->price);
                        //$tmpDiscount += $hotStone->discount_amount;
                    }
                }

                //counting scrub
                $tmpScrub = Item::where('name', 'like', '%scrub%')->where('branch_id', $userData->branch_id)->first();
                if ($tmpScrub != null) {
                    $tmpDtScrub = TransactionDetail::where('header_id', $header->id)->where('item_id', '=', $tmpScrub->id)
                        ->get();

                    foreach ($tmpDtScrub as $scrub) {
                        $items['scrub']['today'] += ($scrub->qty * $scrub->price);
                        //$totals['today'] += ($scrub->qty * $scrub->price);
                        //$tmpDiscount += $scrub->discount_amount;
                    }
                }

                //counting Kimo
                $tmpDtKimos = TransactionDetail::where('header_id', $header->id)->where('item_id', '!=', null)
                    ->get();

                foreach ($tmpDtKimos as $kimo) {
                    $tmpKimo = Item::where('id', $kimo->item_id)->first();
                    if ($tmpKimo != null) {
                        if (str_contains($tmpKimo->name, 'KIMO')) {
                            $items['kimo']['today'] += ($kimo->qty * $kimo->price);
                            //$totals['today'] += ($kimo->qty * $kimo->price);
                            //$tmpDiscount += $kimo->discount_amount;
                        }
                    }
                }
            }
            //dd($tmpDiscount, $totals['today'], $totals['today'] - $tmpDiscount);
            //dd($items['foc']['today'], $count, $headerIds, $headerFocs);
            //$totals['today'] -= $tmpDiscount;
            $totals['todayDiscount'] = $tmpDiscount;
            $items['discount']['today'] = $tmpDiscount;

            //MTD START
            $start = Carbon::create($startDate->year, $startDate->month, 1, 0, 0, 0);
            //dd($avgRateMtdTmp, $start->toDateTimeString(), $endDate->toDateTimeString());

            //            $mtdHeaders = TransactionHeader::where('branch_id', $userData->branch_id)
            //                ->whereBetween('created_at', [$start->toDateTimeString(), $endDate->toDateTimeString()])
            //                ->whereNotIn('locker_id', ['1', '2', '3', '5', '6', '100', '101', '102', '103', '104', '105', '106', '107', '108', '109', '110'])
            //                ->get();

            $mtdHeaders = DB::table('transaction_headers')
                ->select('id', 'discount', 'foc_type')
                ->where('branch_id', $userData->branch_id)
                ->whereBetween('created_at', [$start->toDateTimeString(), $endDate->toDateTimeString()])
                ->whereNotIn('locker_id', ['1', '2', '3', '5', '6', '100', '101', '102', '103', '104', '105', '106', '107', '108', '109', '110'])
                ->get();

            $tmpDiscount = 0;
            foreach ($mtdHeaders as $header) {
                if ($header->foc_type == 'no') {
                    $tmpDiscount += $header->discount;
                }
                $tmpDt = DB::table('transaction_details')
                    ->where('header_id', $header->id)
                    ->where('package_id', '!=', null)
                    ->get();

                $tmpDtFacilityCount = DB::table('transaction_details')
                    ->where('header_id', $header->id)
                    ->where('is_facility_only', '=', 1)
                    ->count();

                $items['facility']['mtd'] += (180000 * $tmpDtFacilityCount);

                //add is cut
                $tmpIsCut = DB::table('transaction_details')
                    ->where('header_id', $header->id)
                    ->where('is_cut', '=', 1)
                    ->get();

                $idx = count($rooms);
                $idx--;
                if (count($tmpIsCut) > 0) {
                    $rooms[$idx]['mtd'] += $tmpIsCut[0]->price * count($tmpIsCut);
                    $totals['mtd'] += $tmpIsCut[0]->price * count($tmpIsCut);
                }

                foreach ($tmpDt as $dt) {
                    $package = DB::table('packages')
                        ->where('id', $dt->package_id)
                        ->first();
                    if ($package != null) {
                        if ($header->foc_type != 'no' && !empty($header->foc_type)) {
                            //counting FOC
                            $items['foc']['mtd'] += $package->price;
                        }

                        for ($i = 0; $i < count($rooms); $i++) {
                            if ($rooms[$i]['name'] == $package->name) {
                                $rooms[$i]['mtd'] += $package->price;
                            }
                        }
                        $totals['mtd'] += $package->price;
                        //$tmpDiscount += $dt->discount_amount;
                        $roomRevenue['mtdRoom'] += $package->price;
                        $roomRevenue['mtdRoomDisc'] += $dt->discount_amount;
                    }
                }

                //counting oil
                $tmpOilMsgs = DB::table('items')
                    ->where('name', 'like', '%oil%')
                    ->where('branch_id', $userData->branch_id)
                    ->get();

                foreach ($tmpOilMsgs as $oilMsg) {
                    $tmpTrx = DB::table('transaction_details')
                        ->where('header_id', $header->id)
                        ->where('item_id', '=', $oilMsg->id)
                        ->get();

                    foreach ($tmpTrx as $oil) {
                        $items['oil_massage']['mtd'] += ($oil->qty * $oil->price);
                        $totals['mtd'] += ($oil->qty * $oil->price);
                        //$tmpDiscount += $oil->discount_amount;
                    }
                }

                //counting FNB
                $tmpDtFnb = DB::table('transaction_details')
                    ->where('header_id', $header->id)
                    ->where('is_fnb', '=', 1)
                    ->get();

                foreach ($tmpDtFnb as $fnb) {
                    $items['fnb']['mtd'] += ($fnb->qty * $fnb->price);
                    $totals['mtd'] += ($fnb->qty * $fnb->price);
                    //$tmpDiscount += $fnb->discount_amount;
                }

                //counting hotstone
                $tmpHotStone = DB::table('items')
                    ->where('name', 'like', '%hotstone%')
                    ->where('branch_id', $userData->branch_id)
                    ->first();
                if ($tmpHotStone != null) {
                    $tmpDtHotStone = DB::table('transaction_details')
                        ->where('header_id', $header->id)
                        ->where('item_id', '=', $tmpHotStone->id)
                        ->get();

                    foreach ($tmpDtHotStone as $hotStone) {
                        $items['hotstone']['mtd'] += ($hotStone->qty * $hotStone->price);
                        $totals['mtd'] += ($hotStone->qty * $hotStone->price);
                        //$tmpDiscount += $hotStone->discount_amount;
                    }
                }

                //counting scrub
                $tmpScrub = DB::table('items')
                    ->where('name', 'like', '%scrub%')
                    ->where('branch_id', $userData->branch_id)
                    ->first();

                if ($tmpScrub != null) {
                    $tmpDtScrubs = DB::table('transaction_details')
                        ->where('header_id', $header->id)
                        ->where('item_id', '=', $tmpScrub->id)
                        ->get();

                    foreach ($tmpDtScrubs as $scrub) {
                        $items['scrub']['mtd'] += ($scrub->qty * $scrub->price);
                        $totals['mtd'] += ($scrub->qty * $scrub->price);
                        //$tmpDiscount += $scrub->discount_amount;
                    }
                }

                //counting Kimo
                $tmpDtKimos = DB::table('transaction_details')
                    ->where('header_id', $header->id)
                    ->where('item_id', '!=', null)
                    ->get();

                foreach ($tmpDtKimos as $kimo) {
                    $tmpKimo = DB::table('items')
                        ->where('id', $kimo->item_id)
                        ->first();

                    if ($tmpKimo != null) {
                        if (str_contains($tmpKimo->name, 'KIMO')) {
                            $items['kimo']['mtd'] += ($kimo->qty * $kimo->price);
                            $totals['mtd'] += ($kimo->qty * $kimo->price);
                            //$tmpDiscount += $kimo->discount_amount;
                        }
                    }
                }
            }
            $totals['mtd'] -= $tmpDiscount;
            $totals['mtdDiscount'] = $tmpDiscount;
            $items['discount']['mtd'] = $tmpDiscount;
            //MTD End

            //YTD START
            $ytdStart = $endDate->copy()->startOfYear();

            $ytdHeaders = DB::table('transaction_headers')
                ->select('id', 'discount', 'foc_type')
                ->where('branch_id', $userData->branch_id)
                ->whereBetween('created_at', [$ytdStart->toDateTimeString(), $endDate->toDateTimeString()])
                ->whereNotIn('locker_id', ['1', '2', '3', '5', '6', '100', '101', '102', '103', '104', '105', '106', '107', '108', '109', '110'])
                ->get();

            $tmpDiscount = 0;
            foreach ($ytdHeaders as $header) {
                if ($header->foc_type == 'no') {
                    $tmpDiscount += $header->discount;
                }
                $tmpDt = DB::table('transaction_details')
                    ->where('header_id', $header->id)
                    ->where('package_id', '!=', null)
                    ->get();

                $tmpDtFacilityCount = DB::table('transaction_details')
                    ->where('header_id', $header->id)
                    ->where('is_facility_only', '=', 1)
                    ->count();
                $items['facility']['ytd'] += (180000 * $tmpDtFacilityCount);

                //add is cut
                $tmpIsCut = DB::table('transaction_details')
                    ->where('header_id', $header->id)
                    ->where('is_cut', '=', 1)
                    ->get();
                $idx = count($rooms);
                $idx--;
                if (count($tmpIsCut) > 0) {
                    $rooms[$idx]['ytd'] += $tmpIsCut[0]->price * count($tmpIsCut);
                    $totals['ytd'] += $tmpIsCut[0]->price * count($tmpIsCut);
                }

                foreach ($tmpDt as $dt) {
                    $package = DB::table('packages')
                        ->where('id', $dt->package_id)
                        ->first();
                    if ($package != null) {
                        if ($header->foc_type != 'no' && !empty($header->foc_type)) {
                            //counting FOC
                            $items['foc']['ytd'] += $package->price;
                        }

                        for ($i = 0; $i < count($rooms); $i++) {
                            if ($rooms[$i]['name'] == $package->name) {
                                $rooms[$i]['ytd'] += $package->price;
                            }
                        }
                        $totals['ytd'] += $package->price;
                        //$tmpDiscount += $dt->discount_amount;
                        $roomRevenue['ytdRoom'] += $package->price;
                        $roomRevenue['ytdRoomDisc'] += $dt->discount_amount;
                    }
                }

                //counting oil
                $tmpOilMsgs = DB::table('items')
                    ->where('name', 'like', '%oil%')
                    ->where('branch_id', $userData->branch_id)
                    ->get();
                foreach ($tmpOilMsgs as $oilMsg) {
                    $tmpTrx = DB::table('transaction_details')
                        ->where('header_id', $header->id)
                        ->where('item_id', '=', $oilMsg->id)
                        ->get();

                    foreach ($tmpTrx as $oil) {
                        $items['oil_massage']['ytd'] += ($oil->qty * $oil->price);
                        $totals['ytd'] += ($oil->qty * $oil->price);
                        //$tmpDiscount += $oil->discount_amount;
                    }
                }

                //counting FNB
                $tmpDtFnb = DB::table('transaction_details')
                    ->where('header_id', $header->id)
                    ->where('is_fnb', '=', 1)
                    ->get();
                foreach ($tmpDtFnb as $fnb) {
                    $items['fnb']['ytd'] += ($fnb->qty * $fnb->price);
                    $totals['ytd'] += ($fnb->qty * $fnb->price);
                    //$tmpDiscount += $fnb->discount_amount;
                }

                //counting hotstone
                $tmpHotStone = DB::table('items')
                    ->where('name', 'like', '%hotstone%')
                    ->where('branch_id', $userData->branch_id)
                    ->first();
                if ($tmpHotStone != null) {
                    $tmpDtHotStone = DB::table('transaction_details')
                        ->where('header_id', $header->id)
                        ->where('item_id', '=', $tmpHotStone->id)
                        ->get();

                    foreach ($tmpDtHotStone as $hotStone) {
                        $items['hotstone']['ytd'] += ($hotStone->qty * $hotStone->price);
                        $totals['ytd'] += ($hotStone->qty * $hotStone->price);
                        //$tmpDiscount += $hotStone->discount_amount;
                    }
                }

                //counting scrub
                $tmpScrub = DB::table('items')
                    ->where('name', 'like', '%scrub%')
                    ->where('branch_id', $userData->branch_id)
                    ->first();
                if ($tmpScrub != null) {
                    $tmpDtScrub = DB::table('transaction_details')
                        ->where('header_id', $header->id)
                        ->where('item_id', '=', $tmpScrub->id)
                        ->get();

                    foreach ($tmpDtScrub as $scrub) {
                        $items['scrub']['ytd'] += ($scrub->qty * $scrub->price);
                        $totals['ytd'] += ($scrub->qty * $scrub->price);
                        //$tmpDiscount += $scrub->discount_amount;
                    }
                }

                //counting Kimo
                $tmpDtKimos = DB::table('transaction_details')
                    ->where('header_id', $header->id)
                    ->where('item_id', '!=', null)
                    ->get();

                foreach ($tmpDtKimos as $kimo) {
                    $tmpKimo = DB::table('items')
                        ->where('id', $kimo->item_id)
                        ->first();
                    if ($tmpKimo != null) {
                        if (str_contains($tmpKimo->name, 'KIMO')) {
                            $items['kimo']['ytd']  += ($kimo->qty * $kimo->price);
                            $totals['ytd'] += ($kimo->qty * $kimo->price);
                            //$tmpDiscount += $kimo->discount_amount;
                        }
                    }
                }
            }

            $totals['ytd'] -= $tmpDiscount;
            $totals['ytdDiscount'] = $tmpDiscount;
            $items['discount']['ytd'] = $tmpDiscount;
            //YTD END

            //Payment Start
            $tmpTotal = 0;
            $tmpHeaderPayments = DB::table('transaction_headers')
                ->where('branch_id', $userData->branch_id)
                ->whereBetween('created_at', [$startDate->toDateTimeString(), $endDate->toDateTimeString()])
                ->whereNotIn('locker_id', ['1', '2', '3', '5', '6', '100', '101', '102', '103', '104', '105', '106', '107', '108', '109', '110'])
                ->where('foc_type', '=', 'no')
                ->get();

            $tmpMtdTotal = 0;
            $tmpMtdHeaderPayments = DB::table('transaction_headers')
                ->where('branch_id', $userData->branch_id)
                ->whereBetween('created_at', [$start->toDateTimeString(), $endDate->toDateTimeString()])
                ->whereNotIn('locker_id', ['1', '2', '3', '5', '6', '100', '101', '102', '103', '104', '105', '106', '107', '108', '109', '110'])
                ->where('foc_type', '=', 'no')
                ->get();

            $tmpYtdTotal = 0;
            $tmpYtdHeaderPayments = DB::table('transaction_headers')
                ->where('branch_id', $userData->branch_id)
                ->whereBetween('created_at', [$ytdStart->toDateTimeString(), $endDate->toDateTimeString()])
                ->whereNotIn('locker_id', ['1', '2', '3', '5', '6', '100', '101', '102', '103', '104', '105', '106', '107', '108', '109', '110'])
                ->where('foc_type', '=', 'no')
                ->get();

            $payments = [];
            $pTypes = PosPaymentType::where('branch_id', $userData->branch_id)->get();
            foreach ($pTypes as $type) {
                $nPayment = [
                    'id'        => $type->id,
                    'code'      => $type->code,
                    'payment'   => $type->description,
                    'total'     => 0,
                    'totalStr' => 0,
                    'totalMtd'     => 0,
                    'totalMtdStr' => 0,
                    'totalYtd'     => 0,
                    'totalYtdStr' => 0,
                ];
                $payments[] = $nPayment;
            }

            //Today
            foreach ($tmpHeaderPayments as $header) {
                $todayPayments = DB::table('transaction_payments')
                    ->where('header_id', $header->id)
                    ->whereBetween('created_at', [$startDate->toDateTimeString(), $endDate->toDateTimeString()])
                    ->get();

                $total = 0;
                foreach ($todayPayments as $history) {
                    $total += $history->amount;

                    for ($i = 0; $i < count($payments); $i++) {
                        if ($payments[$i]['id'] == $history->payment_type_id) {
                            $payments[$i]['total'] += $history->amount;
                            $payments[$i]['totalStr'] = number_format($history->amount, '0', ',', '.');
                        }
                    }
                }

                $tmpTotal += $total;
            }

            //MTD
            foreach ($tmpMtdHeaderPayments as $header) {
                $todayPayments = DB::table('transaction_payments')
                    ->where('header_id', $header->id)
                    ->whereBetween('created_at', [$start->toDateTimeString(), $endDate->toDateTimeString()])
                    ->get();

                $total = 0;
                foreach ($todayPayments as $history) {
                    $total += $history->amount;

                    for ($i = 0; $i < count($payments); $i++) {
                        if ($payments[$i]['id'] == $history->payment_type_id) {
                            $payments[$i]['totalMtd'] += $history->amount;
                            $payments[$i]['totalMtdStr'] = number_format($history->amount, '0', ',', '.');
                        }
                    }
                }

                $tmpMtdTotal += $total;
            }

            //YTD
            foreach ($tmpYtdHeaderPayments as $header) {
                $todayPayments = DB::table('transaction_payments')
                    ->where('header_id', $header->id)
                    ->whereBetween('created_at', [$ytdStart->toDateTimeString(), $endDate->toDateTimeString()])
                    ->get();

                $total = 0;
                foreach ($todayPayments as $history) {
                    $total += $history->amount;

                    for ($i = 0; $i < count($payments); $i++) {
                        if ($payments[$i]['id'] == $history->payment_type_id) {
                            $payments[$i]['totalYtd'] += $history->amount;
                            $payments[$i]['totalYtdStr'] = number_format($history->amount, '0', ',', '.');
                        }
                    }
                }

                $tmpYtdTotal += $total;
            }

            $totalStr = number_format($tmpTotal, '0', ',', '.');
            $totalMtdStr = number_format($tmpMtdTotal, '0', ',', '.');
            $totalYtdStr = number_format($tmpYtdTotal, '0', ',', '.');
            //$totals['today'] = $tmpTotal;
            $totals['mtd'] = $tmpMtdTotal;
            $totals['ytd'] = $tmpYtdTotal;
            //dd($totalTrx, $totalTrxPayment, $tmpTotal, $payments);
            //Payment Done

            //Occupancy Start
            //Today Start
            $occToday = [
                'free' => 0,
                'room' => 0,
                'roomAvailable' => Room::all()->count(),
                'totalOcc' => 0,
            ];

            $oHeaders = DB::table('transaction_headers')
                ->where('branch_id', $userData->branch_id)
                ->whereBetween('created_at', [$startDate->toDateTimeString(), $endDate->toDateTimeString()])
                ->whereNotIn('locker_id', ['1', '2', '3', '5', '6', '100', '101', '102', '103', '104', '105', '106', '107', '108', '109', '110'])
                ->get();

            foreach ($oHeaders as $header) {
                $tmpDt = TransactionDetail::where('header_id', $header->id)
                    ->where('package_id', '!=', null)
                    ->get();
                $occToday['room'] += count($tmpDt);
                if ($header->foc_type != 'no') {
                    $occToday['free']++;
                }
            }
            $occToday['totalOcc'] = $occToday['room'] / $occToday['roomAvailable'] * 100;
            //Today End

            //MTD START
            $occMtd = [
                'free' => 0,
                'room' => 0,
                'roomAvailable' => Room::all()->count(),
                'totalOcc' => 0,
            ];

            $oMtdHeaders = DB::table('transaction_headers')
                ->where('branch_id', $userData->branch_id)
                ->whereBetween('created_at', [$start->toDateTimeString(), $endDate->toDateTimeString()])
                ->whereNotIn('locker_id', ['1', '2', '3', '5', '6', '100', '101', '102', '103', '104', '105', '106', '107', '108', '109', '110'])
                ->get();

            //dd($start->toDateTimeString(), $endDate->toDateTimeString());

            foreach ($oMtdHeaders as $header) {
                $tmpDt = TransactionDetail::where('header_id', $header->id)->where('package_id', '!=', null)
                    ->get();
                $occMtd['room'] += count($tmpDt);

                if ($header->foc_type != 'no') {
                    $occMtd['free']++;
                }
            }
            $mtdDayDiff = $endDate->diffInDays($start->toDateTimeString());
            //dd($mtdDayDiff);
            $occMtd['totalOcc'] = $occMtd['room'] / $occMtd['roomAvailable'] * 100 / $mtdDayDiff;
            //MTD End
            //YTD Start
            $occYtd = [
                'free' => 0,
                'room' => 0,
                'roomAvailable' => Room::all()->count(),
                'totalOcc' => 0,
            ];

            $oYtdHeaders = DB::table('transaction_headers')
                ->where('branch_id', $userData->branch_id)
                ->whereBetween('created_at', [$ytdStart->toDateTimeString(), $endDate->toDateTimeString()])
                ->whereNotIn('locker_id', ['1', '2', '3', '5', '6', '100', '101', '102', '103', '104', '105', '106', '107', '108', '109', '110'])
                ->get();

            foreach ($oYtdHeaders as $header) {
                $tmpDt = TransactionDetail::where('header_id', $header->id)->where('package_id', '!=', null)
                    ->get();
                $occYtd['room'] += count($tmpDt);

                if ($header->foc_type != 'no') {
                    $occYtd['free']++;
                }
            }

            $ytdDayDiff = $endDate->diffInDays($ytdStart->toDateTimeString());
            $occYtd['totalOcc'] = $occYtd['room'] / $occYtd['roomAvailable'] * 100 / $ytdDayDiff;
            //YTD End
            //Occupancy End

            $avgRate = [
                'today'     => $roomRevenue['today'] / $occToday['roomAvailable'],
                'mtd'       => $roomRevenue['mtdRoom'] / $occMtd['roomAvailable'] / $mtdDayDiff,
                'ytd'       => $roomRevenue['ytdRoom'] / $occYtd['roomAvailable'] / $ytdDayDiff,
            ];

            $data = [
                'branchId'      => $userData->branch_id,
                'avgRate'       => $avgRate,
                'totals'        => $totals,
                'rooms'         => $rooms,
                'items'         => $items,
                'occToday'      => $occToday,
                'occMtd'        => $occMtd,
                'occYtd'        => $occYtd,
                'payments'      => $payments,
                'totalStr'      => $totalStr,
                'totalMtdStr'   => $totalMtdStr,
                'totalYtdStr'   => $totalYtdStr,
                'startDate'     => $startDate->locale('id_ID')->toDateString(),
                'endDate'       => $endDate->locale('id_ID')->toDateString(),
                'diffDay'       => $endDate->diffInDays($startDate),
                'roomRevenue'   => $roomRevenue,
            ];

            return view('admin.transaction.daily_mtd')->with($data);
        } catch (\Exception $ex) {
            Log::error($ex);
            Session::flash('error', $ex);
            return back();
        }
    }

    public function filterDailyOuraOnly()
    {
        $startDate = Carbon::today('Asia/Jakarta');

        $data = [
            'startDate' => $startDate->format('d-m-Y'),
            'endDate' => $startDate->copy()->format('d-m-Y'),
        ];

        return view('admin.report.report_oura_only')->with($data);
    }

    public function generateDailyReportOnlyOura(Request $request)
    {
        try {
            $rules = array(
                'start_date' => 'required',
                'end_date' => 'required',
            );

            $message = array(
                'start_date.required' => 'Tanggal Mulai Wajib dipilih!',
                'end_date.required' => 'Tanggal Mulai Wajib dipilih!',
            );

            $validator = Validator::make($request->all(), $rules, $message);

            if ($validator->fails()) {
                return Response::json(['error' => $validator->errors()->all()]);
            }

            //get data
            $startDate = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
            $endDate = Carbon::createFromFormat('!d-m-Y', $request->input('end_date'), 'Asia/Jakarta');
            $endDate->addDays(1);

            $user = Auth::guard('admin')->user();

            $discountQuery = "select sum(hd.voucher_discount_value) as total from transaction_invoices hd where
            hd.branch_id = " . $user->branch_id . "
            and hd.status_id = 9
            and hd.created_at >= '" . $startDate->format('Y-m-d H:i:s') . "'
            and hd.created_at <= '" . $endDate->format('Y-m-d H:i:s') . "'";

            $totalQuery = "select sum(py.amount - hd.return_value) as total from transaction_payments py
            inner join  transaction_invoices hd on hd.id = py.invoice_id where
            hd.branch_id = " . $user->branch_id . "
            and hd.status_id = 9
            and hd.created_at >= '" . $startDate->format('Y-m-d H:i:s') . "'
            and hd.created_at <= '" . $endDate->format('Y-m-d H:i:s') . "'";

            $total = DB::select($totalQuery);
            $discount = DB::select($discountQuery);

            //            dd($total, $discount);
            $tmpTotal = $total[0]->total;
            $totalDisc = $discount[0]->total;

            $data = [
                'totalTrx'      => number_format($tmpTotal + $totalDisc, '0', ',', '.'),
                'discount'      => number_format($totalDisc, '0', ',', '.'),
                'total'         => number_format($tmpTotal, '0', ',', '.'),
                'startDate'     => $startDate->toDateString(),
                'endDate'       => $endDate->toDateString(),
            ];

            //dd($data,$totalQuery, $focQuery, $discountQuery);
            //}

            return \response($data);

            //            return view('admin.transaction.daily_report_only_oura')->with($data);
        } catch (\Exception $ex) {
            Session::flash('error', $ex);
            return back();
        }
    }

    public function reportFocFilter(Request $request)
    {
        $filterStartDate = Carbon::now('Asia/Jakarta')->startOfMonth()->format('d-m-Y');
        $filterEndDate = Carbon::today('Asia/Jakarta')->endOfDay()->format('d-m-Y');
        if ($request->start_date != null && $request->end_date != null) {
            $startDateDecoded = rawurldecode($request->start_date);
            $endDateDecoded = rawurldecode($request->end_date);
            $start = Carbon::createFromFormat('!d-m-Y', $startDateDecoded, 'Asia/Jakarta');
            $end = Carbon::createFromFormat('!d-m-Y', $endDateDecoded, 'Asia/Jakarta');

            if ($end->greaterThanOrEqualTo($start)) {
                $filterStartDate = $startDateDecoded;
                $filterEndDate = $endDateDecoded;
            }
        }

        return view('admin.report.report_foc_filter', compact(
            'filterStartDate',
            'filterEndDate',
        ));
    }

    public function generateFocReport(Request $request)
    {
        try {
            $rules = array(
                'start_date'    => 'required',
                'end_date'      => 'required',
            );

            $message = array(
                'start_date.required'   => 'Tanggal Mulai Wajib dipilih!',
                'end_date.required'     => 'Tanggal Akhir Wajib dipilih!',
            );

            $validator = Validator::make($request->all(), $rules, $message);

            if ($validator->fails()) {
                return Response::json(['error' => $validator->errors()->all()]);
            }

            //get data
            $startDate = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
            $endDate = Carbon::createFromFormat('!d-m-Y', $request->input('end_date'), 'Asia/Jakarta');
            $endDate->addDay();

            $user = Auth::guard('admin')->user();
            $query = "select @n := @n + 1 no, id, voucher_id, voucher_code, subtotal, discount_amount, grandtotal, created_at from transaction_details, (SELECT @n := 0) m
                where voucher_code != ''
                and voucher_code is not null
                and branch_id = " . $user->branch_id . "
                and status_id != 16
                and created_at >= '" . $startDate->format('Y-m-d H:i:s') . "'
                and created_at <= '" . $endDate->format('Y-m-d H:i:s') . "';";
            $transactions = DB::select($query);

            $vouchers = [];
            $coupons = Coupon::where('branch_id', $user->branch_id)->orderBy('code')->get();
            foreach ($coupons as $coupon) {
                $nCoupon = [
                    'id'    => $coupon->id,
                    'name'  => $coupon->code,
                    'qty'   => 0,
                    'amount'   => 0,
                ];
                $vouchers[] = $nCoupon;
            }

            $groupedTransactions = collect($transactions)
                ->groupBy('voucher_id')
                ->map(fn($group, $key) => ['id' => $key, 'qty' => $group->count(), 'amount' => $group->sum('discount_amount')])
                ->values();

            //            dd($groupedTransactions);
            foreach ($groupedTransactions as $trx) {
                foreach ($vouchers as &$vc) {
                    if ($vc['id'] == $trx['id']) {
                        $vc['qty'] += $trx['qty'];
                        $vc['amount'] += $trx['qty'];
                    }
                }
            }
            unset($vc);

            return view('admin.transaction.foc_report', compact('transactions', 'vouchers'));
        } catch (\Exception $ex) {
            Session::flash('error', $ex);
            return back();
        }
    }

    public function reportFocData(Request $request)
    {
        $rules = array(
            'start_date'    => 'required',
            'end_date'      => 'required',
        );

        $message = array(
            'start_date.required'   => 'Tanggal Mulai Wajib dipilih!',
            'end_date.required'     => 'Tanggal Akhir Wajib dipilih!',
        );

        $validator = Validator::make($request->all(), $rules, $message);

        if ($validator->fails()) {
            return Response::json(['error' => $validator->errors()->all()]);
        }

        //get data
        $startDate = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
        $endDate = Carbon::createFromFormat('!d-m-Y', $request->input('end_date'), 'Asia/Jakarta')->endOfDay();

        $user = Auth::guard('admin')->user();
        $query = "
            SELECT
                @n := @n + 1 AS no,
                td.id,
                th.code,
                td.voucher_id,
                UPPER(td.voucher_code) as voucher_code,
                td.subtotal,
                td.discount_amount,
                td.created_at
            FROM transaction_details td
            JOIN transaction_headers th
                ON th.id = td.header_id,
                (SELECT @n := 0) m
            WHERE td.voucher_code IS NOT NULL
            AND td.voucher_code != ''
            AND td.status_id != 16
            AND td.created_at >= '" . $startDate->format('Y-m-d H:i:s') . "'
            AND td.created_at <= '" . $endDate->format('Y-m-d H:i:s') . "'
            ";

        $transactions = DB::select($query);

        $vouchers = [];
        $coupons = Coupon::where('branch_id', $user->branch_id)->orderBy('code')->get();
        foreach ($coupons as $coupon) {
            $nCoupon = [
                'id'    => $coupon->id,
                'code'  => $coupon->code,
                'qty'   => 0,
                'amount'   => 0,
            ];
            $vouchers[] = $nCoupon;
        }

        $groupedTransactions = collect($transactions)
            ->groupBy('voucher_code')
            ->map(fn($group, $key) => ['id' => $key, 'qty' => $group->count(), 'amount' => $group->sum('discount_amount')])
            ->values();

        //            dd($groupedTransactions);
        foreach ($groupedTransactions as $trx) {
            foreach ($vouchers as $vc) {
                if ($vc['code'] == $trx['voucher_code']) {
                    $vc['qty'] += $trx['qty'];
                    $vc['amount'] += $trx['amount'];
                }
            }
        }
        // unset($vc);

        return Response::json([
            'vouchers' => $vouchers,
            'transactions' => $transactions,
        ]);
    }
}
