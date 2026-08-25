<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\UserVoucher;
use App\Models\UserVoucherCode;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class CouponController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function getIndex(Request $request){
        $user = Auth::guard('admin')->user();

        $coupons = Coupon::query();

        return DataTables::of($coupons)
            ->editColumn('created_at', function ($row) {
                return Carbon::parse($row->created_at)->toIso8601String();
            })
            ->editColumn('is_for_sell', function ($row) {
                return $row->is_for_sell == 0 ? 'Tidak' : 'Ya';
            })
            ->editColumn('is_foc', function ($row) {
                return $row->is_foc == 0 ? 'Tidak' : 'Ya';
            })
            ->editColumn('is_unlimited', function ($row) {
                return $row->is_unlimited == 0 ? 'Tidak' : 'Ya';
            })
            ->editColumn('is_free_treatment', function ($row) {
                return $row->is_free_treatment == 0 ? 'Tidak' : 'Ya';
            })
            ->editColumn('can_redeem', function ($row) {
                return $row->can_redeem == 0 ? 'Umum' : 'Loyalty';
            })
            ->editColumn('status', function ($row) {
                return $row->status->display;
            })
            ->editColumn('created_by', function ($row) {
                return $row->createdBy->name;
            })
            ->addColumn('action', function ($row) {
                $buttons = '';
                $buttons .= '<a href="' . route('admin.coupon.show', ['id' => $row->id]) . '" class="btn btn-info me-1"><i class="far fa fa-eye"></i></a>';
                $buttons .= '<a href="' . route('admin.coupon.edit', ['id' => $row->id]) . '" class="btn btn-primary me-1"><i class="fa fa-pencil-alt"></i></a>';

                return $buttons;
            })
            ->make();
    }

    public function index(){
        return view('admin.coupon.index');
    }
    public function create(){
        return view('admin.coupon.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required',
        ], [
            'code.required' => 'Kode voucher wajib diisi!',
        ]);

        if ($validator->fails()) {
            Session::flash('error', $validator->errors()->first());
            return back()->withInput($request->all());
        }

        try {
            DB::beginTransaction();

            $imageName = '';
            if ($request->hasFile('main_image')) {
                $image = $request->file('main_image');
                $imageName = $this->compressImage($request->input('code'), $image);
            }

            $packageIds = '';
            if ($request->has('packages') && !empty($request->input('packages'))) {
                $packageIds = implode(',', $request->input('packages'));
            }

            $startDate = null;
            $endDate = null;
            if(!$request->has('lifetime')){
                $startDate = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'))->startOfDay();
                $endDate = Carbon::createFromFormat('d-m-Y', $request->input('end_date'))->endOfDay();
            }

            $voucher = Coupon::create([
                'code' => $request->input('code'),
                'discount_percentage' => !$request->has('discount_percentage') ? null : $request->input('discount_percentage'),
                'discount_amount' => !$request->has('discount_amount') ? null : $request->input('discount_amount'),
                'status_id' => $request->input('status'),
                'is_foc' => $request->input('is_foc'),
                'is_unlimited' => $request->input('is_unlimited'),
                'is_free_treatment' => $request->input('is_free_treatment'),
                'valid_date' => $startDate,
                'expired_date' => $endDate,
                'package_ids' => $packageIds,
                'can_redeem' => (int) $request->input('can_redeem'),
                'redeem_points' => $request->input('redeem_points') ?? null,
                'img_path' => $imageName,
                'terms_condition' => $request->input('terms_conditions'),
                'created_by' => Auth::guard('admin')->user()->id,
                'is_lifetime' => $request->has('lifetime') ? 1 : 0
            ]);

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

            UserVoucherCode::create([
                'code'              => $voucher->code,
                'header_id'         => $userVc->id,
                'expired_date'      => Carbon::now('Asia/Jakarta')->addYear(10)->toDateTimeString(),
                'is_used'           => 0,
                'is_lifetime'       => $voucher->is_lifetime,
            ]);

            DB::commit();

            Session::flash('success', 'Sukses membuat coupon baru!');
            return redirect()->route('admin.coupon.index');
        }
        catch (\Exception $ex){
            DB::rollBack();
            Session::flash('error', $ex);
            return back();
        }
    }

    public function edit($id){
        $coupon = Coupon::find($id);

        if($coupon == null){
            Session::flash('error', 'Coupon tidak ditemukan!');
            return back();
        }

        $packageIds = [];
        if ($coupon->package_ids != null) {
            $packageIds = array_map('intval', explode(',', $coupon->package_ids));
        }

        $validDate = Carbon::parse($coupon->valid_date)->format('d-m-Y');
        $expiredDate = Carbon::parse($coupon->expired_date)->format('d-m-Y');

        return view('admin.coupon.edit', compact('coupon', 'packageIds', 'validDate', 'expiredDate'));
    }

    public function show($id){
        $coupon = Coupon::find($id);

        if($coupon == null){
            Session::flash('error', 'Coupon tidak ditemukan!');
            return back();
        }

        $packageIds = [];
        if ($coupon->package_ids != null) {
            $packageIds = array_map('intval', explode(',', $coupon->package_ids));
        }

        $validDate = Carbon::parse($coupon->valid_date)->format('d-m-Y');
        $expiredDate = Carbon::parse($coupon->expired_date)->format('d-m-Y');

        return view('admin.coupon.show', compact('coupon', 'packageIds', 'validDate', 'expiredDate'));
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required',
        ], [
            'code.required' => 'Kode voucher wajib diisi!',
        ]);

        if ($validator->fails()) {
            Session::flash('error', $validator->errors()->first());
            return back()->withInput($request->all());
        }

        try {
            DB::beginTransaction();

            $coupon = Coupon::find($request->input('id'));

            $imageName = '';
            if ($request->hasFile('main_image')) {
                $image = $request->file('main_image');
                $imageName = $this->compressImage($request->input('name'), $image);
                $coupon->update(['image_path' => $imageName]);
            }

            $packageIds = '';
            if ($request->input('can_redeem') == 'on') {
                $packageIds = implode(',', $request->input('packages'));
                $coupon->update(['package_ids' => $packageIds]);
            }

            $startDate = null;
            $endDate = null;
            if(!$request->has('lifetime')){
                $startDate = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'))->startOfDay();
                $endDate = Carbon::createFromFormat('d-m-Y', $request->input('end_date'))->endOfDay();
            }

            $coupon->update([
                'code' => $request->input('code'),
                'discount_percentage' => !$request->has('discount_percentage') ? null : $request->input('discount_percentage'),
                'discount_amount' => !$request->has('discount_amount') ? null : $request->input('discount_amount'),
                'status_id' => $request->input('status'),
                'is_foc' => $request->input('is_foc'),
                'is_unlimited' => $request->input('is_unlimited'),
                'is_free_treatment' => $request->input('is_free_treatment'),
                'valid_date' => $startDate,
                'expired_date' => $endDate,
                'can_redeem' => (int) $request->input('can_redeem'),
                'redeem_points' => $request->input('redeem_points') ?? null,
                'terms_condition' => $request->input('terms_conditions'),
                'updated_by' => Auth::guard('admin')->user()->id,
                'is_lifetime' => $request->has('lifetime') ? 1 : 0
            ]);

            $userVc = UserVoucher::where('voucher_id', $coupon->id)->first();
            $userVc->update([
                'voucher_code' => $coupon->code,
                'is_lifetime' => $request->has('lifetime') ? 1 : 0
            ]);
            $userVcCode = UserVoucherCode::where('header_id', $coupon->id)->first();
            $userVcCode->update([
                'code' => $userVc->code,
                'is_lifetime' => $request->has('lifetime') ? 1 : 0
            ]);

            DB::commit();

            Session::flash('success', 'Sukses mengubah data voucher!');
            return redirect()->route('admin.coupon.index');
        }
        catch (\Exception $ex){
            DB::rollBack();

            Log::channel('voucher')->error('Coupon Controller - update voucher ' . $ex);
            Session::flash('error', $ex);
            return back();
        }
    }

    public function delete(Request $request){
        try{
            $coupon = Coupon::where('id', $request->input('id'))->first();
            if($coupon == null){
                Session::flash('error', 'Coupon tidak ditemukan!');
                return back();
            }
            $coupon->status_id = 2;
            $coupon->save();

            $userVc = UserVoucher::where('header_id', $coupon->id)->first();
            $userVcCode = UserVoucherCode::where('header_id', $coupon->id)->first();
            $userVcCode->delete();
            $userVc->delete();

            return Response::json('Sukses', 200);
        }
        catch (\Exception $ex){
            return Response::json('error', 200);
        }
    }

    public function select(Request $request)
    {
        $term = trim($request->search);

        $coupons = DB::table('coupons')
            ->where(function($q) use ($term){
                $q->where('code', 'LIKE', '%'. $term. '%');
            })
            ->get();

        $formatted_tags = [];

        foreach ($coupons as $coupon) {
            $formatted_tags[] = ['id' => $coupon->id, 'text' => strtoupper($coupon->code)];
        }

        return Response::json($formatted_tags);
    }

    public function compressImage($name, $image)
    {
        $bannerName = strtolower(str_replace(' ', '_', $name)) . '_' . date("Ymd");
        $imageMimeType = strtolower($image->getClientOriginalExtension());
        $bannerImageName = $bannerName . '.' . $imageMimeType;

        // Define the full storage path
        $storagePath = Storage::disk('coupons')->path($bannerImageName);

        // Save the original image
        Storage::disk('coupons')->put($bannerImageName, file_get_contents($image->getRealPath()));

        // Check file size
        if ($image->getSize() > 700000) {
            $compressionQuality = 50;

            // Create an image resource
            $sourceImage = imagecreatefromstring(file_get_contents($image->getRealPath()));

            if (!$sourceImage) {
                return false; // Prevent errors if the image fails to load
            }

            // Resize
            $width = imagesx($sourceImage);
            $height = imagesy($sourceImage);
            $newWidth = $width * 0.4;
            $newHeight = $height * 0.4;
            $newImage = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            // Save the compressed image
            if ($imageMimeType == 'jpeg' || $imageMimeType == 'jpg') {
                imagejpeg($newImage, $storagePath, $compressionQuality);
            } elseif ($imageMimeType == 'png') {
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                imagepng($newImage, $storagePath, 9);
            } elseif ($imageMimeType == 'webp') {
                imagewebp($newImage, $storagePath, $compressionQuality);
            }

            // Clean up
            imagedestroy($sourceImage);
            imagedestroy($newImage);
        }

        return $bannerImageName; // Return filename only
    }
}
