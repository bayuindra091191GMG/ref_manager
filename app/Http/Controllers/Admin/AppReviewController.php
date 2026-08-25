<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserReview;
use App\Transformer\ReviewTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;

class AppReviewController extends Controller
{
    public function index(Request $request){
        try{
            $admin = Auth::guard('admin')->user();

            $filterStartDate = Carbon::today()->subDays(7)->format('d-m-Y');
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

            $branches = DB::table('branches')
                ->where('is_oura', '=', 1)
                ->get();

            $filterBranchId = $admin->branch_id;
            if($request->branch_id != null){
                $filterBranchId = $request->branch_id;
            }

            $data = [
                'filterStartDate' => $filterStartDate,
                'filterEndDate' => $filterEndDate,
                'branches' => $branches,
                'filterBranchId' => $filterBranchId
            ];

            return view('admin.review.index')->with($data);
        }
        catch (\Exception $ex){
            Log::channel('online_system')->error('Admin/ReviewController - index '. $ex);
            return 'Terjadi kesalahan pada sistem!';
        }
    }

    public function getIndex(Request $request){
        $reviews = DB::table('user_reviews')
            ->join('branches', 'user_reviews.branch_id', '=', 'branches.id')
            ->join('statuses', 'user_reviews.status_id', '=', 'statuses.id')
            ->join('booking_headers', 'user_reviews.booking_id', '=', 'booking_headers.id')
            ->leftJoin('transaction_headers', 'booking_headers.transaction_id', '=', 'transaction_headers.id')
            ->select('user_reviews.id as id',
                'user_reviews.booking_id as booking_id',
                'user_reviews.user_name as user_name',
                'user_reviews.user_phone as user_phone',
                'user_reviews.partner_name as partner_name',
                'user_reviews.package_name as package_name',
                'user_reviews.star as star',
                'user_reviews.created_at as created_at',
                'branches.name as branch_name',
                'statuses.display as status_display',
                'booking_headers.code as booking_code',
                'booking_headers.type as booking_type',
                'booking_headers.transaction_id as transaction_id',
                'transaction_headers.code as transaction_code');

        if($request->filled('branch_id')){
            $filterBranchId = $request->input('branch_id');
            if($filterBranchId > 0){
                $reviews = $reviews->where('user_reviews.branch_id', '=', $filterBranchId);
            }
        }

        if($request->filled('start_date') || $request->filled('end_date')){
            // Filter date
            $start = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
            $end = Carbon::createFromFormat('!d-m-Y', $request->input('end_date'), 'Asia/Jakarta');
            $end->addDays(1);


            $reviews = $reviews->whereBetween('user_reviews.created_at', array($start->toDateTimeString(), $end->toDateTimeString()));
        }

        return DataTables::of($reviews)
            ->setTransformer(new ReviewTransformer)
            ->make(true);
    }

    public function show($id){
        try{
            $userReview = UserReview::with(['user_review_images', 'booking_header'])->find($id);
            if(empty($userReview)){
                return redirect()->back();
            }

            $trxShowUrl = null;
            $trxCode = null;
            if(!empty($userReview->booking_header->transaction_id)){
                $trxShowUrl = route('admin.transaction.show', ['id' => $userReview->booking_header->transaction_id]);

                $trxCode = DB::table('transaction_headers')
                    ->where('id', '=', $userReview->booking_header->transaction_id)
                    ->value('code');
            }

            $data = [
                'userReview' => $userReview,
                'trxShowUrl' => $trxShowUrl,
                'trxCode' => $trxCode
            ];

            return view('admin.review.show')->with($data);
        }
        catch (\Exception $ex){
            Log::channel('online_system')->error('Admin/ReviewController - show '. $ex);
            return 'Terjadi kesalahan pada sistem!';
        }
    }
}
