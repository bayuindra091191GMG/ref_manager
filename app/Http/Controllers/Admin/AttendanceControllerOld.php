<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Transformer\AttendanceCheckEmployeeTransformer;
use App\Transformer\AttendanceCheckPartnerTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Rap2hpoutre\FastExcel\FastExcel;
use Yajra\DataTables\DataTables;

class AttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function indexEmployee(Request $request){
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

        $filterEmployeeId = -1;
        // Filter Employee
        if($request->employee_id != null){
            $filterEmployeeId = intval($request->employee_id);
            $filterEmployee = DB::table('employees')
                ->where('id', '=', $filterEmployeeId)
                ->first();

            if(empty($filterEmployee)){
                $filterEmployeeId = -1;
            }
        }

        $employees = DB::table('employees')
            ->where('branch_id', '=', $admin->branch_id)
            ->where('status_id', '=', 1)
            ->orderBy('name')
            ->get();

        $data = [
            'filterStartDate' => $filterStartDate,
            'filterEndDate' => $filterEndDate,
            'filterEmployeeId' => $filterEmployeeId,
            'employees' => $employees,
        ];

        return view('admin.attendance.index_employee')->with($data);
    }

    public function getIndexEmployee(Request $request){
        $admin = Auth::guard('admin')->user();
        $attendances = DB::connection('hrd')
            ->table('attendance_checks')
            ->where('branch_id', '=', $admin->branch_id)
            ->where('type', '=', 'employee');

        if($request->filled('start_date') || $request->filled('end_date')){
            // Filter date
            $start = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
            $end = Carbon::createFromFormat('!d-m-Y', $request->input('end_date'), 'Asia/Jakarta');
            $end->addDays(1);

            $attendances = $attendances->whereBetween('current_date', array($start->toDateTimeString(), $end->toDateTimeString()));
        }

        if($request->filled('employee_id')){
            $filterEmployeeId = $request->input('employee_id');
            if($filterEmployeeId > 0){
                $attendances = $attendances->where('employee_id', '=', $filterEmployeeId);
            }
        }

        return DataTables::of($attendances)
            ->setTransformer(new AttendanceCheckEmployeeTransformer)
            ->make(true);
    }

    public function indexPartner(Request $request){
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

        $filterPartnerId = -1;
        // Filter Partner
        if($request->employee_id != null){
            $filterPartnerId = intval($request->employee_id);
            $filterPartner = DB::table('employees')
                ->where('id', '=', $filterPartnerId)
                ->first();

            if(empty($filterPartner)){
                $filterPartnerId = -1;
            }
        }

        $partners = DB::table('partners')
            ->where('branch_id', '=', $admin->branch_id)
            ->where('status_id', '!=', 2)
            ->orderBy('name')
            ->get();

        $data = [
            'filterStartDate' => $filterStartDate,
            'filterEndDate' => $filterEndDate,
            'filterPartnerId' => $filterPartnerId,
            'partners' => $partners,
        ];

        return view('admin.attendance.index_partner')->with($data);
    }

    public function getIndexPartner(Request $request){
        $admin = Auth::guard('admin')->user();
        $attendances = DB::connection('hrd')
            ->table('attendance_checks')
            ->where('branch_id', '=', $admin->branch_id)
            ->where('type', '=', 'partner')
            ->get();

        if($request->filled('start_date') || $request->filled('end_date')){
            // Filter date
            $start = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
            $end = Carbon::createFromFormat('!d-m-Y', $request->input('end_date'), 'Asia/Jakarta');
            $end->addDays(1);

            $attendances = $attendances->whereBetween('current_date', array($start->toDateTimeString(), $end->toDateTimeString()));
        }

        if($request->filled('partner_id')){
            $filterEmployeeId = $request->input('employee_id');
            if($filterEmployeeId > 0){
                $attendances = $attendances->where('employee_id', '=', $filterEmployeeId);
            }
        }

        return DataTables::of($attendances)
            ->setTransformer(new AttendanceCheckPartnerTransformer)
            ->make(true);
    }

    public function showImportForm(){
        try {
            $admin = Auth::guard('admin')->user();
            $routeSubmit = route('admin.import.attendance.submit');

            return view('admin.attendance.import', compact('routeSubmit'));
        }
        catch (\Exception $ex){
            Log::channel('hrd')->error('Admin/AttendanceController - showImportForm '. $ex);
            return 'Terjadi kesalahan pada sistem!';
        }
    }

    // public function submitImportForm(Request $request){
    //     try {
    //         $admin = Auth::guard('admin')->user();
    //         $branch = DB::table('branches')
    //             ->where('id', '=', $admin->branch_id)
    //             ->first();

    //         $attendMonthArr = explode('-', $request->input('month'));
    //         $attendMonthCarbon = Carbon::createFromDate($attendMonthArr[1], $attendMonthArr[0], 1);

    //         $lateToleranceMinutes = DB::table('configurations')
    //             ->where('key', '=', 'employee_late_tolerance_minutes')
    //             ->value('value_int');

    //         $reads = (new FastExcel)->withoutHeaders()->import($request->file('excel'));

    //         $idx = 0;
    //         $totalDays = 0;
    //         foreach ($reads as $read){
    //             if($idx === 2){
    //                 $isDone = false;
    //                 $tmpIdx = 3;
    //                 while(!$isDone){
    //                     if(empty($read[$tmpIdx])) break;

    //                     $totalDays++;
    //                     $tmpIdx++;
    //                 }
    //             }
    //             elseif($idx > 3){
    //                 $isDone = false;
    //                 $tmpIdx = 3;
    //                 for($i = 0; $i <= $totalDays; $i++){
    //                     if(!empty($read[$tmpIdx])){
    //                         $employee = DB::table('employees')
    //                             ->where('branch_id', '=', $admin->branch_id)
    //                             ->where('attend_machine_id', '=', $read[0])
    //                             ->first();

    //                         if(empty($employee)){
    //                             continue;
    //                         }

    //                         $department = DB::table('departments')
    //                             ->where('id', '=', $employee->department_id)
    //                             ->first();

    //                         $tmpDate = Carbon::createFromDate($attendMonthCarbon->year,
    //                             $attendMonthCarbon->month,
    //                             $reads[2][$tmpIdx],
    //                             'Asia/Jakarta')
    //                             ->hour(0)
    //                             ->minute(0)
    //                             ->second(0);

    //                         $inputArr = preg_split('/\r\n|\r|\n/', $read[$tmpIdx]);

    //                         $inTime = null;
    //                         $outTime = null;

    //                         $arrIdx = 0;
    //                         if(count($inputArr) > 1){
    //                             $inTimeArr = explode(':', $inputArr[0]);
    //                             $inTime = $tmpDate->copy()->hour($inTimeArr[0])->minute($inTimeArr[1]);

    //                             if(!empty($inputArr[count($inputArr) - 1])){
    //                                 $outTimeArr = explode(':', $inputArr[count($inputArr) - 1]);
    //                                 $outTime = $tmpDate->copy()->hour($outTimeArr[0])->minute($outTimeArr[1]);
    //                             }
    //                             else{
    //                                 $outTimeArr = explode(':', $inputArr[count($inputArr) - 2]);
    //                                 $outTime = $tmpDate->copy()->hour($outTimeArr[0])->minute($outTimeArr[1]);
    //                             }
    //                         }
    //                         else{
    //                             $tmpTimeArr = explode(':', $inputArr[0]);
    //                             $inTime = $tmpDate->copy()->hour($tmpTimeArr[0])->minute($tmpTimeArr[1]);
    //                         }

    //                         if(!empty($inTime)){
    //                             $newAttendance = new Attendance();
    //                             $newAttendance->branch_id = $admin->branch_id;
    //                             $newAttendance->branch_code = $branch->code;
    //                             $newAttendance->branch_name = $branch->name;
    //                             $newAttendance->employee_id = $employee->id;
    //                             $newAttendance->employee_code = $employee->code;
    //                             $newAttendance->employee_name = $employee->name;
    //                             $newAttendance->employee_department = $department->name;
    //                             $newAttendance->current_date = $tmpDate->startOfDay()->toDateTimeString();
    //                             $newAttendance->in_time = $inTime->toDateTimeString();

    //                             if(!empty($outTime)){
    //                                 if($outTime->lessThan($inTime)){
    //                                     $outTime->addDays(1);
    //                                 }
    //                             }

    //                             $newAttendance->out_time = !empty($outTime) ? $outTime->toDateTimeString() : null;
    //                             $newAttendance->status_id = 1;
    //                             $newAttendance->is_late = false;

    //                             // Checking late
    //                             $employeeShift = DB::connection('mysql_hrd')
    //                                 ->table('employee_shifts')
    //                                 ->where('shift_date', '=', $tmpDate->toDateTimeString())
    //                                 ->where('employee_id', '=', $employee->id)
    //                                 ->where('employee_id', '=', $employee->id)
    //                                 ->where('employee_department_id', '=', $employee->department_id)
    //                                 ->first();

    //                             if(!empty($employeeShift)){
    //                                 $shiftStartTime = Carbon::parse($employeeShift->shift_start_time);
    //                                 // Compare time
    //                                 if($inTime->greaterThan($shiftStartTime)){
    //                                     if($inTime->diffInMinutes($shiftStartTime) > $lateToleranceMinutes){
    //                                         $newAttendance->is_late = true;
    //                                     }
    //                                 }
    //                             }

    //                             $newAttendance->save();
    //                         }
    //                     }

    //                     $tmpIdx++;
    //                 }
    //             }

    //             $idx++;
    //         }

    //         Session::flash('success', 'Berhasil import data absen!');
    //         return redirect()->back();
    //     }
    //     catch (\Exception $ex){
    //         Log::channel('hrd')->error('Admin/AttendanceController - submitImportForm '. $ex);
    //         Session::flash('error', 'Terjadi kesalahan pada sistem');
    //         return redirect()->back();
    //     }
    // }

    public function submitImportForm(Request $request){
        try {
            $admin = Auth::guard('admin')->user();
            $branch = DB::table('branches')
                ->where('id', '=', $admin->branch_id)
                ->first();

            $type = $request->input('type');
            if($type === 'default'){
                Session::flash('error', 'Mohon pilih Untuk Absen!');
                return redirect()->back();
            }

            $attendanceDateArr = explode('-', $request->input('month'));
            $attendanceDateCarbon = Carbon::createFromDate($attendanceDateArr[1], $attendanceDateArr[0], 1);

            $reads = (new FastExcel)->withoutHeaders()->import($request->file('excel'));

            $totalDays = 0;
            $idx = 0;
            foreach ($reads as $read){
                if($idx === 1){
                    $isDone = false;
                    $tmpIdx = 3;
                    while(!$isDone){
                        if(empty($read[$tmpIdx])) break;

                        $totalDays++;
                        $tmpIdx++;
                    }
                }
                elseif($idx > 1 && !empty($read[2])){
                    $tmpIdx = 3;

                    if($type === 'employee'){
                        $employee = DB::table('employees')
                        ->where('branch_id', '=', $admin->branch_id)
                        ->where('code', '=', $read[2])
                        ->first();

                        if(empty($employee)){
                            continue;
                        }
                    }
                    else{
                        $partner = DB::table('partners')
                            ->where('branch_id', '=', $admin->branch_id)
                            ->where('code', '=', $read[2])
                            ->first();

                        if(empty($partner)){
                            continue;
                        }
                    }

                    for($i = 0; $i <= $totalDays; $i++){
                        if(!empty($read[$tmpIdx])){

                            $departmentName = null;
                            if($type === 'employee'){
                                $department = DB::table('departments')
                                    ->where('id', '=', $employee->department_id)
                                    ->first();
                                $departmentName = $department->name;
                            }

                            $tmpDate = Carbon::createFromDate($attendanceDateCarbon->year,
                                $attendanceDateCarbon->month,
                                $reads[1][$tmpIdx],
                                'Asia/Jakarta')
                                ->hour(0)
                                ->minute(0)
                                ->second(0);

                            if($type === 'employee'){
                                if(DB::connection('mysql_hrd')
                                    ->table('attendance_checks')
                                    ->where('type', '=', $type)
                                    ->where('employee_id', '=', $employee->id)
                                    ->where('current_date', '=', $tmpDate->toDateTimeString())
                                    ->exists()){
                                    continue;
                                }
                            }
                            else{
                                if(DB::connection('mysql_hrd')
                                    ->table('attendance_checks')
                                    ->where('type', '=', $type)
                                    ->where('partner_id', '=', $partner->id)
                                    ->where('current_date', '=', $tmpDate->toDateTimeString())
                                    ->exists()){
                                    continue;
                                }
                            }

                            $isAttend = false;
                            $isLate = false;
                            $lateMinute = 0;
                            $isAlpha = false;
                            $isLongShift = false;
                            $longShiftHour = 0;
                            $isLeave = false;
                            $isOff = false;
                            $alphaPenaltyAmount = 0;

                            $tmp = $i + 3;
                            $raw = $read[$tmp];
                            if(str_contains($raw, '#')){
                                $rawArr = explode('#', $raw);
                                foreach($rawArr as $rawItem){
                                    if($rawItem === 'H'){
                                        $isAttend = true;
                                    }
                                    elseif($rawItem === 'I'){
                                        $isLeave = true;
                                    }
                                    elseif($rawItem === 'O'){
                                        $isOff = true;
                                    }
                                    else{
                                        if(str_contains($rawItem, 'L')){
                                            $isLongShift = true;
                                            if(str_contains($rawItem, '-')){
                                                $longShiftArr = explode('-', $rawItem);
                                                if(!empty($longShiftArr[1])){
                                                    $longShiftHour = intval($longShiftArr[1]);
                                                }
                                            }
                                        }
                                        elseif(str_contains($rawItem, 'T')){
                                            $isLate = true;
                                            if(str_contains($rawItem, '-')){
                                                $lateArr = explode('-', $rawItem);
                                                if(!empty($lateArr[1])){
                                                    $lateMinuteStr = ltrim($lateArr[1], 'n');
                                                    $lateMinute = intval($lateMinuteStr);
                                                }
                                            }
                                        }
                                        elseif(str_contains($rawItem, 'A')){
                                            $isAlpha = true;
                                            if(str_contains($rawItem, '-')){
                                                $alphaArr = explode('-', $rawItem);
                                                if(!empty($alphaArr[1])){
                                                    $alphaPenaltyAmount = intval($alphaArr[1]);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            else{
                                if($raw === 'H'){
                                    $isAttend = true;
                                }
                                elseif($raw === 'T'){
                                    $isLate = true;
                                    if(str_contains($raw, '-')){
                                        $lateArr = explode('-', $raw);
                                        if(!empty($lateArr[1])){
                                            $lateMinuteStr = ltrim($lateArr[1], 'n');
                                            $lateMinute = intval($lateMinuteStr);
                                        }
                                    }
                                }
                                elseif($raw === 'I'){
                                    $isLeave = true;
                                }
                                elseif($raw === 'O'){
                                    $isOff = true;
                                }
                                else{
                                    if(str_contains($raw, 'L')){
                                        $isLongShift = true;
                                        if(str_contains($raw, '-')){
                                            $longShiftArr = explode('-', $raw);
                                            if(!empty($longShiftArr[1])){
                                                $longShiftHour = intval($longShiftArr[1]);
                                            }
                                        }
                                    }
                                    elseif(str_contains($raw, 'T')){
                                        $isLate = true;
                                        if(str_contains($raw, '-')){
                                            $lateArr = explode('-', $raw);
                                            if(!empty($lateArr[1])){
                                                $lateMinuteStr = ltrim($lateArr[1], 'n');
                                                $lateMinute = intval($lateMinuteStr);
                                            }
                                        }
                                    }
                                    elseif(str_contains($raw, 'A')){
                                        $isAlpha = true;
                                        if(str_contains($raw, '-')){
                                            $alphaArr = explode('-', $raw);
                                            if(!empty($alphaArr[1])){
                                                $alphaPenaltyAmount = intval($alphaArr[1]);
                                            }
                                        }
                                    }
                                }
                            }

                            DB::connection('mysql_hrd')
                                ->table('attendance_checks')
                                ->insert([
                                    'branch_id' => $branch->id,
                                    'branch_name' => $branch->name,
                                    'branch_code' => $branch->code,
                                    'type' => $type,
                                    'current_date' => $tmpDate->toDateTimeString(),
                                    'employee_id' => $type === 'employee' ? $employee->id : null,
                                    'employee_code' => $type === 'employee' ? $employee->code : null,
                                    'employee_name' => $type === 'employee' ? $employee->name : null,
                                    'employee_department' => $type === 'employee' ? $departmentName : null,
                                    'partner_id' => $type === 'partner' ? $partner->id : null,
                                    'partner_code' => $type === 'partner' ? $partner->code : null,
                                    'partner_number' => $type === 'partner' ? $partner->number : null,
                                    'partner_name' => $type === 'partner' ? $partner->name : null,
                                    'partner_alias' => $type === 'partner' ? $partner->alias : null,
                                    'raw' => $raw,
                                    'is_attend' => $isAttend,
                                    'is_late' => $isLate,
                                    'is_alpha' => $isAlpha,
                                    'is_long_shift' => $isLongShift,
                                    'is_leave' => $isLeave,
                                    'is_off' => $isOff,
                                    'long_shift_hour' => $longShiftHour,
                                    'late_minutes' => $lateMinute,
                                    'alpha_penalty_amount' => $alphaPenaltyAmount,
                                    'created_at' => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                                    'created_by' => $admin->id,
                                    'updated_at' => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                                    'updated_by' => $admin->id,
                                ]);
                        }
                        $tmpIdx++;
                    }
                }

                $idx++;
            }

            Session::flash('success', 'Berhasil import data absensi!');
            return redirect()->back();
        }
        catch (\Exception $ex){
            dd($ex);
            Log::channel('hrd')->error('Admin/AttendanceController - submitImportForm '. $ex);
            Session::flash('error', 'Terjadi kesalahan pada sistem');
            return redirect()->back();
        }
    }

    public function submitImportKmcForm(Request $request){
        try {
            $admin = Auth::guard('admin')->user();
            $branch = DB::table('branches')
                ->where('id', '=', $admin->branch_id)
                ->first();

            $attendMonthArr = explode('-', $request->input('month'));
            $attendMonthCarbon = Carbon::createFromDate($attendMonthArr[1], $attendMonthArr[0], 1);

            $lateToleranceMinutes = DB::table('configurations')
                ->where('key', '=', 'employee_late_tolerance_minutes')
                ->value('value_int');

            $reads = (new FastExcel)->withoutHeaders()->import($request->file('excel'));
            dd($reads);

            $idx = 0;
            $totalDays = 0;
            foreach ($reads as $read){
                if($idx === 2){
                    $isDone = false;
                    $tmpIdx = 3;
                    while(!$isDone){
                        if(empty($read[$tmpIdx])) break;

                        $totalDays++;
                        $tmpIdx++;
                    }
                }
                elseif($idx > 3){
                    $isDone = false;
                    $tmpIdx = 3;
                    for($i = 0; $i <= $totalDays; $i++){
                        if(!empty($read[$tmpIdx])){
                            $employee = DB::table('employees')
                                ->where('branch_id', '=', $admin->branch_id)
                                ->where('attend_machine_id', '=', $read[0])
                                ->first();

                            if(empty($employee)){
                                continue;
                            }

                            $department = DB::table('departments')
                                ->where('id', '=', $employee->department_id)
                                ->first();

                            $tmpDate = Carbon::createFromDate($attendMonthCarbon->year,
                                $attendMonthCarbon->month,
                                $reads[2][$tmpIdx],
                                'Asia/Jakarta')
                                ->hour(0)
                                ->minute(0)
                                ->second(0);

                            $inputArr = preg_split('/\r\n|\r|\n/', $read[$tmpIdx]);

                            $inTime = null;
                            $outTime = null;

                            $arrIdx = 0;
                            if(count($inputArr) > 1){
                                $inTimeArr = explode(':', $inputArr[0]);
                                $inTime = $tmpDate->copy()->hour($inTimeArr[0])->minute($inTimeArr[1]);

                                if(!empty($inputArr[count($inputArr) - 1])){
                                    $outTimeArr = explode(':', $inputArr[count($inputArr) - 1]);
                                    $outTime = $tmpDate->copy()->hour($outTimeArr[0])->minute($outTimeArr[1]);
                                }
                                else{
                                    $outTimeArr = explode(':', $inputArr[count($inputArr) - 2]);
                                    $outTime = $tmpDate->copy()->hour($outTimeArr[0])->minute($outTimeArr[1]);
                                }
                            }
                            else{
                                $tmpTimeArr = explode(':', $inputArr[0]);
                                $inTime = $tmpDate->copy()->hour($tmpTimeArr[0])->minute($tmpTimeArr[1]);
                            }

                            if(!empty($inTime)){
                                $newAttendance = new Attendance();
                                $newAttendance->branch_id = $admin->branch_id;
                                $newAttendance->branch_code = $branch->code;
                                $newAttendance->branch_name = $branch->name;
                                $newAttendance->employee_id = $employee->id;
                                $newAttendance->employee_code = $employee->code;
                                $newAttendance->employee_name = $employee->name;
                                $newAttendance->employee_department = $department->name;
                                $newAttendance->current_date = $tmpDate->startOfDay()->toDateTimeString();
                                $newAttendance->in_time = $inTime->toDateTimeString();

                                if(!empty($outTime)){
                                    if($outTime->lessThan($inTime)){
                                        $outTime->addDays(1);
                                    }
                                }

                                $newAttendance->out_time = !empty($outTime) ? $outTime->toDateTimeString() : null;
                                $newAttendance->status_id = 1;
                                $newAttendance->is_late = false;

                                // Checking late
                                $employeeShift = DB::connection('mysql_hrd')
                                    ->table('employee_shifts')
                                    ->where('shift_date', '=', $tmpDate->toDateTimeString())
                                    ->where('employee_id', '=', $employee->id)
                                    ->where('employee_id', '=', $employee->id)
                                    ->where('employee_department_id', '=', $employee->department_id)
                                    ->first();

                                if(!empty($employeeShift)){
                                    $shiftStartTime = Carbon::parse($employeeShift->shift_start_time);
                                    // Compare time
                                    if($inTime->greaterThan($shiftStartTime)){
                                        if($inTime->diffInMinutes($shiftStartTime) > $lateToleranceMinutes){
                                            $newAttendance->is_late = true;
                                        }
                                    }
                                }

                                $newAttendance->save();
                            }
                        }

                        $tmpIdx++;
                    }
                }

                $idx++;
            }

            Session::flash('success', 'Berhasil import data absen!');
            return redirect()->back();
        }
        catch (\Exception $ex){
            Log::channel('hrd')->error('Admin/AttendanceController - submitImportForm '. $ex);
            Session::flash('error', 'Terjadi kesalahan pada sistem');
            return redirect()->back();
        }
    }

    public function filter(){
        $startDate = Carbon::today('Asia/Jakarta');

        $data = [
            'startDate' => $startDate->format('d-m-Y'),
            'endDate' => $startDate->copy()->format('d-m-Y'),
        ];

        return view('admin.report.filter')->with($data);
    }

    public function showImportShiftForm(){
        try {
            return view('admin.attendance.import_shift');
        }
        catch (\Exception $ex){
            Log::channel('hrd')->error('Admin/AttendanceController - showImportShiftForm '. $ex);
            return 'Terjadi kesalahan pada sistem!';
        }
    }

    public function submitImportShiftForm(Request $request){
        try {
            $admin = Auth::guard('admin')->user();
            $branch = DB::table('branches')
                ->where('id', '=', $admin->branch_id)
                ->first();

            $shiftDateArr = explode('-', $request->input('month'));
            $shiftDateCarbon = Carbon::createFromDate($shiftDateArr[1], $shiftDateArr[0], 1);

            $reads = (new FastExcel)->withoutHeaders()->import($request->file('excel'));

            $totalDays = 0;
            $idx = 0;
            foreach ($reads as $read){
                if($idx === 1){
                    $isDone = false;
                    $tmpIdx = 3;
                    while(!$isDone){
                        if(empty($read[$tmpIdx])) break;

                        $totalDays++;
                        $tmpIdx++;
                    }
                }
                elseif($idx > 2 && !empty($read[2])){
                    $tmpIdx = 3;
                    $employee = DB::table('employees')
                        ->where('branch_id', '=', $admin->branch_id)
                        ->where('code', '=', $read[2])
                        ->first();

                    if(empty($employee)){
                        continue;
                    }

                    for($i = 0; $i <= $totalDays; $i++){
                        if(!empty($read[$tmpIdx]) && is_int($read[$tmpIdx])){

                            $department = DB::table('departments')
                                ->where('id', '=', $employee->department_id)
                                ->first();

                            $tmpDate = Carbon::createFromDate($shiftDateCarbon->year,
                                $shiftDateCarbon->month,
                                $reads[1][$tmpIdx],
                                'Asia/Jakarta')
                                ->hour(0)
                                ->minute(0)
                                ->second(0);

                            $attendanceShift = DB::table('attendance_shifts')
                                ->where('department_id', '=', $department->id)
                                ->where('shift_index', '=', $read[$tmpIdx])
                                ->first();

                            $startTimeArr = explode('.', $attendanceShift->time_start);
                            $startTimeCarbon = $tmpDate->copy()->hour($startTimeArr[0])->minute($startTimeArr[1])->second(0);

                            $endTimeArr = explode('.', $attendanceShift->time_end);
                            $endTimeCarbon = $tmpDate->copy()->hour($endTimeArr[0])->minute($endTimeArr[1])->second(0);

                            $existingShift = DB::connection('mysql_hrd')
                                ->table('employee_shifts')
                                ->where('shift_date', '=', $tmpDate->toDateTimeString())
                                ->where('employee_id', '=', $employee->id)
                                ->where('employee_department_id', '=', $employee->department_id)
                                ->first();

                            if(!empty($existingShift)){
                                DB::connection('mysql_hrd')
                                    ->table('employee_shifts')
                                    ->where('id', '=', $existingShift->id)
                                    ->update([
                                        'shift_name' => $attendanceShift->shift_name,
                                        'shift_index' => $attendanceShift->shift_index,
                                        'shift_start_time' => $startTimeCarbon->toDateTimeString(),
                                        'shift_end_time' => $endTimeCarbon->toDateTimeString(),
                                        'status_id' => 1,
                                        'updated_at' => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                                        'updated_by' => $admin->id,
                                    ]);
                            }
                            else{
                                DB::connection('mysql_hrd')
                                    ->table('employee_shifts')
                                    ->insert([
                                        'branch_id' => $branch->id,
                                        'branch_name' => $branch->name,
                                        'branch_code' => $branch->code,
                                        'employee_id' => $employee->id,
                                        'employee_code' => $employee->code,
                                        'employee_name' => $employee->name,
                                        'employee_department_id' => $department->id,
                                        'employee_department' => $department->name,
                                        'shift_name' => $attendanceShift->shift_name,
                                        'shift_index' => $attendanceShift->shift_index,
                                        'shift_date' => $tmpDate->toDateTimeString(),
                                        'shift_start_time' => $startTimeCarbon->toDateTimeString(),
                                        'shift_end_time' => $endTimeCarbon->toDateTimeString(),
                                        'update_count' => 0,
                                        'status_id' => 1,
                                        'created_at' => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                                        'created_by' => $admin->id,
                                        'updated_at' => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                                        'updated_by' => $admin->id,
                                    ]);
                            }
                        }
                        $tmpIdx++;
                    }
                }

                $idx++;
            }

            Session::flash('success', 'Berhasil import data shift karyawan!');
            return redirect()->back();
        }
        catch (\Exception $ex){
            Log::channel('hrd')->error('Admin/AttendanceController - submitImportShiftForm '. $ex);
            Session::flash('error', 'Terjadi kesalahan pada sistem');
            return redirect()->back();
        }
    }

    public function downloadExcelTemplate() {
        try{
            return Storage::disk('excel')->download('attendance_template_excel.xlsx', 'TEMPLATE EXCEL ABSEN.xlsx');
        }
        catch (\Exception $ex){
            Log::channel('hrd')->error('Admin/AttendanceController - downloadExcelTemplate '. $ex);
            Session::flash('error', 'Terjadi kesalahan pada sistem');
            return redirect()->back();
        }
    }
}
