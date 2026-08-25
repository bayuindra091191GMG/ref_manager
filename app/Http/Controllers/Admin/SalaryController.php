<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Transformer\MonthlySalaryPartnerTransformer;
use App\Transformer\MonthlySalaryTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\DataTables;

class SalaryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request){
        try {
            $admin = Auth::guard('admin')->user();

            // Filter period
            $filterPeriod = Carbon::today('Asia/Jakarta')->startOfMonth()->format('m-Y');
            if($request->filter_period != null){
                $filterPeriod = $request->filter_period;
            }

            $data = [
                'filterPeriod' => $filterPeriod
            ];

            return view('admin.salary.index')->with($data);
        }
        catch (\Exception $ex){
            Log::channel('hrd')->error('Admin/SalaryController - index '. $ex);
            return 'Terjadi kesalahan pada sistem!';
        }
    }

    public function getIndex(Request $request){
        $admin = Auth::guard('admin')->user();

        $filterPeriod = $request->input('filter_period');
        $filterPeriodArr = explode('-', $filterPeriod);
        $filterPeriodCarbon = Carbon::today('Asia/Jakarta')
            ->month($filterPeriodArr[0])
            ->year($filterPeriodArr[1])
            ->startOfMonth();

        $salaries = DB::connection('hrd')
            ->table('monthly_salaries')
            ->whereNotNull('employee_id')
            ->where('branch_id', '=', $admin->branch_id)
            ->where('period_date', '=', $filterPeriodCarbon)
            ->get();

        return DataTables::of($salaries)
            ->setTransformer(new MonthlySalaryTransformer)
            ->make(true);
    }

    public function show($id){
        try {
            $salary = DB::connection('hrd')
                ->table('monthly_salaries')
                ->where('id', '=', $id)
                ->first();

            if(empty($salary)){
                Session::flash('error', 'BAD REQUEST');
                return redirect()->back();
            }

            $commissionDetails = DB::connection('hrd')
                ->table('monthly_salary_commission_details')
                ->where('header_id', '=', $id)
                ->get();

            $data = [
                'salary' => $salary,
                'commissionDetails' => $commissionDetails
            ];

            return view('admin.salary.show')->with($data);
        }
        catch (\Exception $ex){
            Log::channel('hrd')->error('Admin/SalaryController - index '. $ex);
            Session::flash('error', $ex);
            return redirect()->back();
        }
    }

    public function indexPartner(Request $request){
        try {
            $admin = Auth::guard('admin')->user();

            // Filter period
            $filterPeriod = Carbon::today('Asia/Jakarta')->startOfMonth()->format('m-Y');
            if($request->filter_period != null){
                $filterPeriod = $request->filter_period;
            }

            $data = [
                'filterPeriod' => $filterPeriod
            ];

            return view('admin.salary.index_partner')->with($data);
        }
        catch (\Exception $ex){
            Log::channel('hrd')->error('Admin/SalaryController - index '. $ex);
            return 'Terjadi kesalahan pada sistem!';
        }
    }

    public function getIndexPartner(Request $request){
        $admin = Auth::guard('admin')->user();

        $filterPeriod = $request->input('filter_period');
        $filterPeriodArr = explode('-', $filterPeriod);
        $filterPeriodCarbon = Carbon::today('Asia/Jakarta')
            ->month($filterPeriodArr[0])
            ->year($filterPeriodArr[1])
            ->startOfMonth();

        $salaries = DB::connection('hrd')
            ->table('monthly_salaries')
            ->whereNotNull('partner_id')
            ->where('branch_id', '=', $admin->branch_id)
            ->where('period_date', '=', $filterPeriodCarbon)
            ->get();

        return DataTables::of($salaries)
            ->setTransformer(new MonthlySalaryPartnerTransformer)
            ->make(true);
    }

    public function showPartner($id){
        try {
            $salary = DB::connection('hrd')
                ->table('monthly_salaries')
                ->where('id', '=', $id)
                ->first();

            if(empty($salary)){
                Session::flash('error', 'BAD REQUEST');
                return redirect()->back();
            }

            $commissionDetails = DB::connection('hrd')
                ->table('monthly_salary_commission_details')
                ->where('header_id', '=', $id)
                ->get();

            $data = [
                'salary' => $salary,
                'commissionDetails' => $commissionDetails
            ];

            return view('admin.salary.show_partner')->with($data);
        }
        catch (\Exception $ex){
            Log::channel('hrd')->error('Admin/SalaryController - index '. $ex);
            Session::flash('error', $ex);
            return redirect()->back();
        }
    }

    public function filterEmployeeSalaryReport(){
        $admin = Auth::guard('admin')->user();

        // Filter period
        $periodDate = Carbon::today('Asia/Jakarta')->startOfMonth()->format('m-Y');

        $data = [
            'periodDate' => $periodDate
        ];

        return view('admin.report.report_employee_salary_filter')->with($data);
    }

    public function generateEmployeeSalaryReport(Request $request){
        try {
            $admin = Auth::guard('admin')->user();

            $filterPeriod = $request->input('period_date');
            $filterPeriodArr = explode('-', $filterPeriod);
            $filterPeriodCarbon = Carbon::today('Asia/Jakarta')
                ->month($filterPeriodArr[0])
                ->year($filterPeriodArr[1])
                ->startOfMonth();

            $grandTotalSalary = DB::connection('hrd')
                ->table('monthly_salaries')
                ->whereNotNull('employee_id')
                ->where('branch_id', '=', $admin->branch_id)
                ->where('period_date', '=', $filterPeriodCarbon->toDateTimeString())
                ->sum('total_salary_amount');

//            dd($grandTotalSalary);

            $salaries = DB::connection('hrd')
                ->table('monthly_salaries')
                ->whereNotNull('employee_id')
                ->where('branch_id', '=', $admin->branch_id)
                ->where('period_date', '=', $filterPeriodCarbon->toDateTimeString())
                ->get();

            $data = [
                'grandTotalSalary' => $grandTotalSalary,
                'salaries' => $salaries,
                'periodDate' => $filterPeriodCarbon->format('m-Y')
            ];

            return view('admin.report.report_employee_salary_content')->with($data);
        }
        catch (\Exception $ex){
            Log::channel('hrd')->error('Admin/SalaryController - generateEmployeeSalaryReport '. $ex);
            Session::flash('error', $ex);
            return redirect()->back();
        }
    }
}
