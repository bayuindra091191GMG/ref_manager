<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\UserPointHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class UserPointHistoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index()
    {
        return view('admin.report.history_user_point');
    }

    public function datatable()
    {
        $user = Auth::guard('admin')->user();
        $userData = AdminUser::where('id', $user->id)->first();

        $userPointHistories = UserPointHistory::select(
            'id',
            'branch_id',
            'branch_name',
            'user_name',
            'user_phone',
            'amount_add',
            'amount_sub',
            'amount_result',
            'reference_1',
            'created_at',
        )
        ->where('branch_id', $userData->branch_id);

        return DataTables::of($userPointHistories)
            ->editColumn('created_at', function ($row) {
                return Carbon::parse($row->created_at)->toIso8601String();
            })
            ->make();
    }
}
