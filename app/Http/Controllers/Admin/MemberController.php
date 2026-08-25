<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Locker;
use App\Models\Member;
use App\Models\TransactionDetail;
use App\Models\TransactionHeader;
use App\Models\User;
use App\Transformer\MemberTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Yajra\DataTables\Facades\DataTables;

class MemberController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function search(Request $request)
    {
        try {
            $member = Member::where('phone', '=', $request->input('phone'))->first();

            if($member == null){
                return response()->json([
                    'success' => false,
                    'message' => 'Member tidak ditemukan!'
                ]);
            }

            return response()->json([
                'name' => $member->name,
                'success' => true,
                'message' => 'Berhasil menemukan member!',
            ]);
        } catch (\Exception $e) {
            error_log($e);
            return Response::json($e, 500);
        }
    }

    public function check(Request $request){
        try{
            $locker = Locker::where('id', $request->input('locker_id'))->first();
            if($locker == null){
                return Response::json('Loker tidak ditemukan!', 404);
            }
            $memberCode = $request->input('member_code');
            $member = Member::with('membership')
                ->where('code', '=', $memberCode)
                ->first();

            if($member == null){
                return Response::json('Member tidak ditemukan!', 404);
            }

            if($member->is_active == 0){
                return Response::json('Member sudah tidak aktif!', 400);
            }

            $header = TransactionHeader::where('locker_id', $request->input('locker_id'))
                ->where('status_id', 8)
                ->first();

            if($header == null){
                return Response::json('Locker belum ada transaksi!', 400);
            }

            $detail = TransactionDetail::where('header_id', $header->id)
                ->where('package_id', '!=', null)
                ->orWhere('is_facility_only', 1)
                ->first();

            if($detail == null){
                return Response::json('Locker belum ada transaksi!', 400);
            }

//            $detail->discount_percentage = $member->membership->discount_package;
//            $detail->discount_amount = ($detail->qty * $detail->price) * $detail->discount_percentage / 100;
//            $detail->subtotal = ($detail->qty * $detail->price) - $detail->discount_amount;
//            $detail->save();

            // Check for existing usage today

            $membershipBenefits = DB::table('membership_benefits')
                ->where('membership_id', '=', $member->membership_id)
                ->get();

            $todayStart = Carbon::today('Asia/Jakarta');
            $todayEnd = $todayStart->copy()->addDay();

            // Check for existing usage today
            $packageUsage = DB::table('transaction_details')
                ->where('member_id', '=', $member->id)
                ->whereNotNull('package_id')
                ->whereBetween('created_at', array($todayStart->toDateTimeString(), $todayEnd->toDateTimeString()))
                ->count();

            $benefitArr = [];
            foreach ($membershipBenefits as $membershipBenefit){
                if($membershipBenefit->type === 'treatment'){
                    if($packageUsage >= $membershipBenefit->usage_count){
                        return Response::json('Manfaat membership anda sudah melebihi batas maksimal!', 400);
                    }
                }
                elseif($membershipBenefit->type === 'facility'){
                    // Check for existing usage today
                    $isFreeUsage = DB::table('transaction_details')
                        ->where('is_facility_only', '=', $member->id)
                        ->where('member_id', '=', $member->id)
                        ->whereBetween('created_at', array($todayStart->toDateTimeString(), $todayEnd->toDateTimeString()))
                        ->count();

                    if($isFreeUsage >= $membershipBenefit->free_amount){
                        return Response::json('Manfaat membership anda sudah melebihi batas maksimal!', 400);
                    }
                    else{
                        if($packageUsage >= $membershipBenefit->usage_count){
                            return Response::json('Manfaat membership anda sudah melebihi batas maksimal!', 400);
                        }
                    }
                }

                $benefitArr[] = [
                    'type'                  => $membershipBenefit->type,
                    'discount_percentage'   => floatval($membershipBenefit->discount_percentage),
                    'is_free'               => $membershipBenefit->free_amount > 0 ? 1 : 0,
                    'item_category_id'      => $membershipBenefit->item_category_id,
                    'member_id'             => $member->id
                ];
            }

//            $data = [
//                'header'    => $header,
//                'dt'        => $detail,
//                'member'    => $member
//            ];

            $data = [
                'benefits'    => $benefitArr,
            ];

            return Response::json($data, 200);
        }
        catch (\Exception $ex){
            error_log($ex);
            return Response::json($ex, 500);
        }
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.member.index');
    }

    public function getIndex(Request $request){
        $query = User::query();

        return DataTables::of($query)
            ->make(true);
    }
}
