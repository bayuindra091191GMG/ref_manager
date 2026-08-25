<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Libs\ApiUtilities;
use App\Mail\SendVoucherCode;
use App\Models\AdminUser;
use App\Models\Branch;
use App\Models\Configuration;
use App\Models\Coupon;
use App\Models\CouponList;
use App\Models\UserVoucher;
use App\Models\UserVoucherCode;
use App\Transformer\VoucherExcelTransformer;
use App\Transformer\VoucherTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Rap2hpoutre\FastExcel\FastExcel;
use Yajra\DataTables\DataTables;

class VoucherController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(){
        return view('admin.voucher.index');
    }

    public function getIndex(Request $request)
    {
        $vouchers = UserVoucher::with('user_voucher_code');

        return DataTables::of($vouchers)
            ->editColumn('created_at', function ($row) {
                return Carbon::parse($row->created_at)->toIso8601String();
            })
            ->editColumn('notes', function ($row) {
                return $row->notes ?? 'Pembelian/Hasil Redeem User';
            })
            ->editColumn('owner', function ($row) {
                return $row->user->name ?? $row->createdBy->name;
            })
            ->editColumn('quantity', function ($row) {
                return $row->user_voucher_code->count(); // Optimized count
            })
            ->editColumn('status', function ($row) {
                return $row->status->display;
            })
            ->editColumn('expired_date', function ($row) {
                if($row->is_lifetime == 1) {
                    return '-';
                }
                else{
                    return Carbon::parse($row->user_voucher_code->first()->expired_date)->toIso8601String(); // Avoid errors if empty
                }
            })
            ->addColumn('action', function ($row) {
                $buttons = '';
                $buttons .= '<a href="' . route('admin.voucher.show', ['id' => $row->id]) . '" class="btn btn-info me-1"><i class="far fa fa-eye"></i> Show</a>';
                $buttons .= '<a data-id="'. $row->id .'" class="send-email-modal btn btn-primary me-1"><i class="fa fa-share"></i> Email</a>';
                return $buttons;
            })
            ->make();
    }

    public function create(){
        $user = Auth::guard('admin')->user();
        $coupons = Coupon::where('branch_id', $user->branch_id)->get();
        //dd($coupons);

        return view('admin.voucher.create', compact('coupons'));
    }
    public function show($id)
    {
        // Ambil data dari tabel user_vouchers berdasarkan id
        $voucher = UserVoucher::with(['user', 'createdBy'])
            ->find($id);

        // Jika data tidak ditemukan, kembalikan response 404
        if (!$voucher) {
            abort(404, 'Voucher tidak ditemukan');
        }

        $userVoucherCode = UserVoucherCode::where('header_id', $voucher->id)->get();

        $datas = UserVoucherCode::select(
            'user_voucher_codes.code',
            'user_voucher_codes.is_used',
            'transaction_headers.voucher_code',
            'transaction_headers.id as transaction_id',
            'transaction_invoices.code as transaction_code',
            'user_voucher_codes.created_at',
            'user_voucher_codes.is_lifetime',
            'user_voucher_codes.expired_date')
        ->leftJoin('transaction_invoices', 'transaction_invoices.voucher_code', '=', 'user_voucher_codes.code')
        ->leftJoin('transaction_headers', 'transaction_headers.id', '=', 'transaction_invoices.header_ids')
        ->where('user_voucher_codes.header_id', $voucher->id)->get();

        // Siapkan data untuk ditampilkan di view
        $data = [
            'owner' => $voucher->user ? $voucher->user->name : 'N/A', // Ambil nama user dari relasi
            'created_by' => $voucher->createdBy ? $voucher->createdBy->name : 'N/A', // Ambil nama created_by dari relasi
            'jenis_voucher' => $voucher->voucher_code, // Ambil jenis voucher dari kolom voucher_code
            'quantity' => $voucher->user_voucher_code->count(), // Kosongkan quantity
            'notes' => $voucher->notes ?? 'Pembelian/Hasil Redeem User', // Ambil notes dari kolom notes
            'status' => $voucher->status->display,
            'exp_date' => $voucher->is_lifetime == 1 ? "-" : \Carbon\Carbon::parse($voucher->user_voucher_code->first()->expired_date)->format('d M Y H:i:s'),// Kosongkan exp date
            'tanggal_dibuat' => $voucher->created_at->format('d M Y H:i:s'), // Format created_at
            'userVoucherCode' => $userVoucherCode,
        ];

        // Kirim data ke view
        return view('admin.voucher.show', compact('data'));
    }

    public function generate(Request $request){
        try{
            if($request->input('count') == 0){
                Session::flash('error', 'Jumlah Voucher tidak boleh 0!');
                return back();
            }

            $user = Auth::guard('admin')->user();
            $branch = Branch::where('id', $user->branch_id)->first();

            //generate voucher
            $voucher = Coupon::where('id', $request->input('coupon'))->first();

            $userVc = UserVoucher::create([
                'voucher_id'        => $voucher->id,
                'voucher_code'      => $voucher->code,
                'terms_condition'   => $voucher->terms_condition,
                'status_id'         => 1,
                'type'              => 'voucher',
                'created_by'        => Auth::guard('admin')->user()->id,
                'notes'             => $request->input('notes'),
                'is_lifetime'       => $voucher->is_lifetime,
            ]);

            $now = Carbon::now('Asia/Jakarta');
            // $tmpDate = $now->addDays($voucher->duration);
            $tmpDate = Carbon::parse($voucher->expired_date) ?? $now->toDateTimeString();

            for($i = 0; $i<$request->input('count'); $i++){
                UserVoucherCode::create([
                    'code'              => ApiUtilities::generateUniqueVoucherCode(),
                    'header_id'         => $userVc->id,
                    'expired_date'      => $tmpDate,
                    'is_used'           => 0,
                    'is_lifetime'       => $voucher->is_lifetime,
                ]);
            }

            Session::flash('success', 'Sukses generate ' . $request->input('count') . ' voucher!');
            return Redirect::route('admin.voucher.index');
        }
        catch (\Exception $ex){
            Session::flash('error', $ex);
            return back();
        }
    }

    public function setExpDate(Request $request){
        try{
            //set exp date for that voucher
            $user = Auth::guard('admin')->user();
            $voucher = CouponList::where('id', $request->input('voucher_id'))->first();
            if($voucher == null){
                return Response::json('Voucher tidak ditemukan!', 404);
            }

            if($request->exists('exp_date')){
                $expDate = Carbon::createFromFormat('d-m-Y', $request->input('exp_date'), 'Asia/Jakarta');
                $voucher->exp_date = $expDate;
                $voucher->updated_by = $user->id;
                $voucher->save();
            }

            return Response::json('Sukses setup Exp Date!', 200);
        }
        catch (\Exception $ex){
            return Response::json($ex, 500);
        }
    }

    public function check(Request $request)
    {
        try {
            // $coupon = Coupon::where('code', $request->input('code'))->first();
            // if($coupon == null) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Voucher tidak ditemukan',
            //     ]);
            // }
            // else{
            //     if($coupon->is_unlimited == 0) {
            //         return response()->json([
            //             'success' => false,
            //             'message' => 'Voucher sudah digunakan',
            //         ]);
            //     }
            //     else{
            //         return response()->json([
            //             'voucher' => $coupon,
            //             'type' => "voucher",
            //             'success' => true,
            //             'message' => 'Voucher berhasil digunakan!',
            //         ]);
            //     }
            // }

            $voucher = UserVoucherCode::where('code', $request->input('code'))
                ->whereHas('user_voucher', function ($query) use ($request) {
                    $query->whereNull('user_id')
                        ->orWhereHas('user', function ($query2) use ($request) {
                        $query2->where('phone', $request->input('phone'));
                    });
                })
                ->first();

            if ($voucher == null || $voucher->void == 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Voucher tidak ditemukan',
                ]);
            }

            if ($voucher->is_used == 1) {
                $coupon = Coupon::where('id', $voucher->user_voucher->voucher_id)->first();
                if($coupon == null) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Voucher tidak ditemukan',
                    ]);
                }
                else{
                    if($coupon->is_unlimited == 0) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Voucher sudah digunakan',
                        ]);
                    }
                }
            }


//            if($voucher->user_voucher->type != 'package'
//                && ($voucher->user_voucher->coupon->is_lifetime == 0 && $voucher->expired_date < Carbon::now())) {
//                return response()->json([
//                    'success' => false,
//                    'message' => 'Voucher sudah digunakan atau tidak ditemukan',
//                ]);
//            }

            if($voucher->user_voucher->type == 'package'){
                $check = $voucher->user_voucher->voucher_package->details()->where('package_id', $request->input('package'))->first();
                if(!$check){
                    return response()->json([
                        'success' => false,
                        'message' => 'Voucher tidak dapat digunakan pada treatment ini!',
                    ]);
                }
            }
            else {
                $coupon = $voucher->user_voucher->coupon;
                $today = Carbon::now('Asia/Jakarta');
                $start = Carbon::parse($coupon->valid_date);
                $end = Carbon::parse($coupon->expired_date);

                if($coupon->is_lifetime == 0 &&  ($today->lessThan($start) || $today->greaterThan($end))){
                    return response()->json([
                        'success' => false,
                        'message' => 'Voucher tidak dapat digunakan!',
                    ]);
                }
            }

            return response()->json([
                'voucher' => $voucher->user_voucher->type == 'voucher' ? $voucher->user_voucher->coupon : $voucher->user_voucher->voucher_package,
                'type' => $voucher->user_voucher->type,
                'success' => true,
                'message' => 'Voucher berhasil digunakan!',
            ]);
        } catch (\Exception $ex) {
            Log::channel('voucher')->error('[Voucher Check] - ' . $ex);
            return Response::json($ex, 500);
        }
    }

//    public function checkVoucher(Request $request){
//        try{
//            if(!$request->exists('voucher_code')){
//                return Response::json('Kode voucher diperlukan!', 400);
//            }
//
//            $voucher = CouponList::with(['coupon', 'branch', 'updatedBy', 'createdBy'])
//                ->where('code', $request->input('voucher_code'))
//                ->first();
//
//            if($voucher == null){
//                return Response::json('Kode Voucher tidak ditemukan!', 404);
//            }
//
//            if($voucher->coupon->is_foc){
//                return Response::json('Voucher ini tidak dapat digunakan!', 400);
//            }
//
//            if(!$voucher->coupon->is_unlimited && $voucher->is_used == 1){
//                return Response::json('Kode Voucher sudah digunakan!', 400);
//            }
//
//            $now = Carbon::now('Asia/Jakarta');
//            if($voucher->exp_date != null){
//                if($voucher->exp_date < $now){
//                    return Response::json('Kode voucher sudah Expired!', 400);
//                }
//            }
//
//            $masterType = 'discount';
//            $type = 'package';
//            $discType = 'percentage';
//            $freeTreamentArr = [];
//
//            if($voucher->coupon->is_free_treatment){
//                $masterType = 'treatment';
//            }
//
//            if($voucher->coupon->is_item_only){
//                $type = 'item';
//            }
//
//            if($masterType === 'discount'){
//                if($voucher->coupon->discount_amount > 0){
//                    $discType = 'amount';
//                }
//            }
//            else{
//                $freeTreaments = CouponPackageLink::where('coupon_id', '=', $voucher->coupon_id)
//                    ->get();
//
//                foreach($freeTreaments as $freeTreament){
//                    $freeTreamentArr[] = [
//                        'package_id' => $freeTreament->package_id,
//                        'package_code' => $freeTreament->package->code,
//                        'package_name' => $freeTreament->package->name,
//                    ];
//                }
//            }
//
//            $responseArr = [
//                'voucher_header_id' => $voucher->coupon_id,
//                'master_type' => $masterType,
//                'type' => $type,
//                'discount_type' => $discType,
//                'discount_percentage' => floatval($voucher->coupon->discount_percentage),
//                'discount_amount' => floatval($voucher->coupon->discount_amount),
//                'code' => $request->input('voucher_code'),
//                'free_treaments' => $freeTreamentArr
//            ];
//
//            // $transformer = new VoucherTransformer();
//            // $result = $transformer->transform($voucher);
//
//            return Response::json($responseArr, 200);
//        }
//        catch (\Exception $ex){
//            return Response::json($ex, 500);
//        }
//    }

    public function exportView(Request $request){
        $startDate = Carbon::now('Asia/Jakarta')->startOfYear();
        $endDate = Carbon::now('Asia/Jakarta')->endOfYear()->addDays(-1);

        return view('admin.voucher.export', compact('startDate', 'endDate'));
    }

    public function export(Request $request){
        try{
            $user = Auth::guard('admin')->user();

            $startDate = Carbon::createFromFormat("d-m-Y", $request->input("start_date"))->format("Y-m-d");
            $endDate = Carbon::createFromFormat("d-m-Y", $request->input("end_date"))->format("Y-m-d");

            $dbVouchers = CouponList::where('branch_id', $user->branch_id)
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->get();

            $vouchers = [];
            $transformer = new VoucherExcelTransformer();
            foreach ($dbVouchers as $db){
                $vouchers[] = $transformer->transform($db);
            }

            return (new FastExcel($vouchers))->download('vouchers.xlsx');
        }
        catch (\Exception $ex){
            Session::flash('error', $ex);
            return Redirect::route('admin.voucher.export');
        }
    }

    public function sendEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ], [
            'email.required' => 'Email harus diisi!',
            'email.email' => 'Email tidak memiliki format yang valid!',
        ]);

        if ($validator->fails()) {
            return Response::json(['error' => $validator->messages()], 400);
        }

        try {
            $vouchers = UserVoucherCode::where('header_id', $request->input('voucher_id'))->get();

            $urls = [];
            foreach($vouchers as $voucher){
                $urls[] = route('admin.voucher.view', ['code' => $voucher->code]);
            }

            Mail::to($request->input('email'))
                ->send(new SendVoucherCode($urls, 'Kode Voucher'));

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mengirim kode voucher melalui email!',
            ]);
        } catch (\Exception $exception) {
            Log::error('Send email voucher error: ' . $exception);
            return Response::json($exception, 500);
        }
    }

    public function void(Request $request){
        DB::beginTransaction();
        try {
            if(!$request->filled('password')){
                DB::rollBack();
                Session::flash('error', 'Kata Sandi wajib diisi!');
                return redirect()->back();
            }

            //check password first
            $config = Configuration::where('key', 'spv_password')->first();
            if(!Hash::check($request->input('password'), $config->value)){
                DB::rollBack();
                Session::flash('error', 'Password Salah!');
                return redirect()->back();
            }

            $voucher = UserVoucherCode::whereIn('id', $request->input('ids') ?? [])->get();

            if(count($voucher) == 0){
                DB::rollBack();
                Session::flash('error', 'Data tidak ditemukan!');
                return redirect()->back();
            }

            UserVoucherCode::whereIn('id', $request->input('ids'))->update([
               'void' => 1
            ]);

            DB::commit();

            Session::flash('success', 'Sukses void voucher!');
            return Redirect::route('admin.voucher.show', ['id' => $voucher->header_id]);
        } catch (\Exception $exception){
            DB::rollBack();
            Log::channel('void')->error('ERROR VOID VOUCHER: '. $exception);

            Session::flash('error', 'Maaf terjadi kesalahan!');
            return redirect()->back();
        }
    }
}
