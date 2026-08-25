<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libs\ApiUtilities;
use App\Models\BookingHeader;
use App\Models\Configuration;
use App\Models\FcmToken;
use App\Models\Item;
use App\Models\Package;
use App\Models\TransactionDetail;
use App\Models\TransactionDetailPartner;
use App\Models\TransactionHeader;
use App\Models\TransactionInvoice;
use App\Models\User;
use App\Models\UserPointHistory;
use App\Models\UserVoucher;
use App\Models\UserVoucherCode;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    private $result;

    public function __construct(){
        $this->result = [
            'error'         => [],
            'message'       => '',
            'warning'       => '',
            'request_id'    => ApiUtilities::generateRandomShortString(20),
            'response'      => []
        ];
    }

    public function get(Request $request){
        try{
            $user = $request->user();
            $userData = User::where('id', $user->id)->first();

            $membership = DB::table('app_memberships')
                ->where('id', '=', $userData->membership_id)
                ->first();


            $birthdayDate = !empty($userData->birthday_date) ? Carbon::parse($userData->birthday_date)->format('d-m-Y') : null;
            $isBirthday = false;
            $birthdayCouponCode = '';
            $birthdayBenefit = '';
            if(!empty($birthdayDate) && $userData->membership_id > 1){
                $today = Carbon::today('Asia/Jakarta');
                $birthdayDateCarbon = Carbon::parse($userData->birthday_date)->addMinutes(10);;
                $tmpBirthdayDate = Carbon::createFromDate($today->year, $birthdayDateCarbon->month, $birthdayDateCarbon->day);
                $startDate = $tmpBirthdayDate->copy()->startOfMonth()->startOfDay();
                $endDate = $tmpBirthdayDate->copy()->endOfMonth()->addDays(1)->startOfDay();

                $isBirthday = $today->between($startDate, $endDate);
            }

            $minPointUsage = DB::table('configurations')
                ->where('key', '=', 'min_point_usage')
                ->value('value_int');

            $onlineOrderMaxTime = DB::table('configurations')
                ->where('key', '=', 'online_order_max_time')
                ->value('value');

            // Check order max time
            $now = Carbon::now('Asia/Jakarta');
            $onlineOrderMaxTimeCarbon = Carbon::createFromFormat('Y-m-d H:i:s', $now->format('Y-m-d'). ' '. $onlineOrderMaxTime. ':00');
            $canOrder = $now->lessThan($onlineOrderMaxTimeCarbon);

            $totalBooking = BookingHeader::where('user_id', $user->id)->count();
            $totalTransactions = TransactionInvoice::where('member_id', $user->id)->count();

            $result = [
                'phone'             => $userData->phone,
                'email'             => $userData->email ?? '',
                'name'              => $userData->name,
                'point'             => $userData->point,
                'join_date'         => $userData->created_at->format('d-m-Y'),
                'membership'        => $membership->name ?? '-',
                'birthday_date'     => $birthdayDate ?? '',
                'is_birthday'       => $isBirthday,
                'voucher'           => $birthdayCouponCode,
                'min_point_usage'   => intval($minPointUsage),
                'can_use_point'     => $userData->point >= 10000,
                'birthday_benefit'  => $birthdayBenefit,
                'can_order'         => $canOrder,
                'total_booking'     => $totalBooking,
                'transaction_done'  => $totalTransactions,
                'gender'            => $userData->gender == "L" ? "Laki-laki" : "Perempuan",
                'imageUrl'          => $userData->image_path != null ? asset('storage/members/'. $userData->image_path) : '',
                'password_set'      => $userData->is_password_set ?? 0
            ];

            $this->result['response'] = $result;
            $this->result['message'] = 'Ambil data user sukses!';
            return Response::json($this->result, 200);
        }
        catch (\Exception $ex){
            Log::channel('api_exception')->error('Api/UserController - getProfile ERROR EX '. $ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Internal server error';
            return Response::json($this->result, 500);
        }
    }
    public function updateProfile(Request $request){
        try{

            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'name'          => 'required|string|max:100',
                'gender'        => 'required|in:Laki-laki,Perempuan',
                'birthday_date' => 'required|date',
                'image'         => 'nullable|image|mimes:jpg,jpeg,png|max:4048'
            ],[
                'name.required'          => 'Nama wajib diisi',
                'gender.required'        => 'Gender wajib dipilih',
                'gender.in'              => 'Gender tidak valid',
                'birthday_date.required' => 'Tanggal lahir wajib diisi',
                'birthday_date.date'     => 'Format tanggal lahir tidak valid',
                'image.image'            => 'File harus berupa gambar',
                'image.mimes'            => 'Format harus jpg, jpeg, png',
                'image.max'              => 'Ukuran maksimal 4MB'
            ]);

            if ($validator->fails()) {
                $this->result['error'] = $validator->errors()->first();
                $this->result['message'] = 'Terjadi kesalahan validasi!';
                return Response::json($this->result, 400);
            }
            // dd($request->all());

            DB::beginTransaction();

            $userData = User::where('id', $user->id)->first();

            $gender = $request->gender == "Laki-laki" ? "L" : "P";

            $imagePath = $userData->image_path;

            if ($request->hasFile('image')) {

                // delete old image (gunakan disk members)
                if (!empty($imagePath) && Storage::disk('members')->exists($imagePath)) {
                    Storage::disk('members')->delete($imagePath);
                }

                $file = $request->file('image');

                // generate filename
                $filename = $userData->id . '_' . time() . '.' . $file->getClientOriginalExtension();

                // simpan ke disk 'members'
                Storage::disk('members')->putFileAs('', $file, $filename);

                $imagePath = $filename;
            }

            $userData->update([
                'name'          => $request->name,
                'gender'        => $gender,
                'birthday_date' => $request->birthday_date,
                'image_path'    => $imagePath
            ]);

            if (!empty($request->phone)) {
                $userData->phone = $request->phone;
                $userData->save();
            }

            DB::commit();

            $this->result['message'] = 'Profile berhasil diperbarui '. Carbon::now()->toDateTimeString();

            $this->result['response'] = [
                'name'      => $userData->name,
                'email'     => $userData->email,
                'phone'     => $userData->phone,
                'gender'    => $request->gender,
                'imageUrl'  => $imagePath != null
                    ? asset('storage/members/'.$imagePath)
                    : ''
            ];

            return Response::json($this->result, 200);

        }
        catch (\Exception $ex){

            DB::rollBack();

            Log::channel('api_exception')
                ->error('Api/UserController - updateProfile ERROR EX '.$ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Internal server error';

            return Response::json($this->result, 500);
        }
    }

    public function saveFcmToken(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'token' => 'required|string',
            ], [
                'token.required' => 'Token FCM wajib diisi',
            ]);

            if ($validator->fails()) {
                $this->result['error'] = $validator->errors()->first();
                $this->result['message'] = 'Terjadi kesalahan validasi!';

                return Response::json($this->result, 400);
            }

            $user = $request->user();

            FcmToken::updateOrCreate(
                ['token' => $request->input('token')],
                ['user_id' => $user->id]
            );

            $this->result['message'] = 'Token FCM berhasil disimpan!';

            return Response::json($this->result, 200);
        } catch (\Exception $ex) {
            Log::channel('api_exception')
                ->error('Api/UserController - saveFcmToken ERROR EX '.$ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Internal server error';

            return Response::json($this->result, 500);
        }
    }

    public function getTransaction(Request $request){
        try{
            $user = $request->user();
            $result = [];

            $transactionInvoice = TransactionInvoice::where('member_id', $user->id)->get();
            foreach($transactionInvoice as $invoice){
                $headerIds = explode('#', $invoice->header_ids);
                $details = TransactionDetail::whereIn('header_id', $headerIds)->get();
                $detailDatas = [];
                foreach($details as $detail){
                    $partnerName = '';
                    $detailImage = asset('images/logo-only.png');
                    $detailName = '';
                    if(!empty($detail->package_id)){
                        $partner = TransactionDetailPartner::where('transaction_detail_id', $detail->id)->first();
                        if(!empty($partner)){
                            $partnerName = $partner->partner->name;
                        }
                        $package = Package::where('id', $detail->package_id)->first();
                        $detailName = $package->name;
                        $detailImage = asset('storage/images/items'.$package->image_path);
                    }
                    else if(!empty($detail->item_id)){
                        $item = Item::where('id', $detail->item_id)->first();
                        $detailName = $item->name;
                        $detailImage = asset('storage/images/items'.$item->image_path);

                    }
                    else if(!empty($detail->coupon_id)){
                        $detailName = $detail->user_voucher->voucher_code;

                    }
                    $detailDatas[] = [
                        'detail_type'   => !empty($detail->package_id) ? 'package' : 'item',
                        'detail_name'   => $detailName,
                        'detail_image'  => $detailImage,
                        'qty'           => $detail->qty,
                        'price'         => $detail->price,
                        'subtotal'      => $detail->subtotal,
                        'duration'      => !empty($detail->package_id) ? $detail->package->duration : 0,
                        'partner_name'  => $partnerName,
                    ];
                }

                $result[] = [
                    'id'        => $invoice->id,
                    'code'      => $invoice->code,
                    'date'      => $invoice->created_at->format('d-m-Y'),
                    'subtotal'  => $invoice->subtotal,
                    'pb2'       => $invoice->pb2,
                    'total'     => $invoice->grandtotal,
                    'details'   => $detailDatas,
                ];
            }

            $this->result['response'] = $result;
            $this->result['message'] = 'Sukses mengambil data Transaksi!';
            return Response::json($this->result, 200);
        }
        catch (\Exception $ex){
            Log::channel('api_exception')->error('Api/UserController - getTransaction ERROR EX '. $ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Internal server error';
            return Response::json($this->result, 500);
        }
    }

    public function getMyVoucher(Request $request){
        try{
            $skip = 0;
            $take = 10;
            if($request->exists('skip')){
                if($request->query('skip') > 0){
                    $skip = $request->query('skip');
                }
            }

            if($request->exists('take')){
                if($request->query('take') > 0){
                    $take = $request->query('take');
                }
            }

            $user = $request->user();
            $result = [];

            $vouchers = UserVoucher::with('coupon')
                ->where('user_id', $user->id)
                ->skip($skip)
                ->take($take)
                ->get();

            foreach($vouchers as $vc){
                $vcDts = UserVoucherCode::where('header_id', $vc->id)->get();
                foreach($vcDts as $vcDt){
                    $result[] = [
                        'id'        => $vc->id,
                        'code'      => $vc->voucher_code,
                        'img'       => $vc->coupon->img_path ? asset("images/coupons/{$vc->coupon->img_path}") : '-',
                        'terms_condition'   => $vc->terms_condition ?? '-',
                        'code'      => $vcDt->code,
                        'is_used'   => $vcDt->is_used,
                        'exp_date'  => Carbon::parse($vcDt->expired_date)->format('d-m-Y H:i'),
                    ];
                }
            }

            $this->result['response'] = $result;
            $this->result['message'] = 'Sukses Mengambil data Voucher!';
            return Response::json($this->result, 200);
        }
        catch (\Exception $ex){
            Log::channel('api_exception')->error('Api/UserController - getTransaction ERROR EX '. $ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Internal server error';
            return Response::json($this->result, 500);
        }
    }

    public function getPointHistories(Request $request){
        try{
            $skip = 0;
            if($request->exists('skip')){
                $skip = $request->query('skip');
            }

            $user = $request->user();
            $result = [];
            $pointHistories = UserPointHistory::where('user_id', $user->id)
                ->orderByDesc('created_at')
                ->skip($skip)
                ->take(20)->get();

            foreach($pointHistories as $history){
                $result[] = [
                    'type'  => $history->type,
                    'amount_add'    => $history->amount_add,
                    'amount_sub'    => $history->amount_sub,
                    'amount_result' => $history->amount_result,
                    'reference'     => $history->reference_1,
                    'created_at'    => Carbon::parse($history->created_at)->format('d-m-Y H:i'),
                ];
            }

            $this->result['response'] = $result;
            $this->result['message'] = 'Success getting histories!';
            return Response::json($this->result, 200);
        }
        catch (\Exception $ex){
            Log::channel('api_exception')->error('Api/UserController - getPointHistories ERROR EX '. $ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Internal server error';
            return Response::json($this->result, 500);
        }
    }

    public function deleteAccount(Request $request)
    {
        DB::beginTransaction();
        try {
            $user = $request->user();

            if (empty($user)) {
                DB::rollBack();
                $this->result['message'] = 'User tidak ditemukan!';

                return Response::json($this->result, 404);
            }

            $now = Carbon::now('Asia/Jakarta')->toDateTimeString();

            User::where('id', $user->id)->update([
                'deleted_at' => $now,
                'active_at' => null,
                'remember_token' => null,
                'updated_at' => $now,
            ]);

            $accessToken = $user->token();
            if (! empty($accessToken)) {
                DB::table('oauth_access_tokens')
                    ->where('id', $accessToken->id)
                    ->update([
                        'revoked' => true,
                    ]);

                DB::table('oauth_refresh_tokens')
                    ->where('access_token_id', $accessToken->id)
                    ->update([
                        'revoked' => true,
                    ]);
            }

            DB::commit();

            $this->result['message'] = 'Akun berhasil dihapus!';

            return Response::json($this->result, 200);
        } catch (\Exception $ex) {
            DB::rollBack();

            Log::channel('api_exception')
                ->error('Api/UserController - deleteAccount ERROR EX '.$ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Internal server error';

            return Response::json($this->result, 500);
        }
    }
}
