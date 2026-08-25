<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Partner;
use App\Transformer\AttendanceTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Laravel\Facades\Image;
use Yajra\DataTables\DataTables;

class AttendanceFaceController extends Controller
{
    public function index(){
        $startDate = Carbon::now('Asia/Jakarta');

        $data = [
            'startDate' => $startDate->format('d-m-Y'),
        ];

        return view('admin.attendance_face.filter')->with($data);
    }

    public function show(Request $request){
        try{
            $rules = array(
                'start_date'    => 'required',
            );

            $message = array(
                'start_date.required'   => 'Tanggal Mulai Wajib dipilih!',
            );

            $validator = Validator::make($request->all(), $rules, $message);

            if ($validator->fails()) {
                return Response::json(['error' => $validator->errors()->all()]);
            }

            $startDate = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
            $endDate = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
            $endDate->addDay();
            $user = Auth::guard('admin')->user();

            $data = [];
            $employees = Employee::where('branch_id', $user->branch_id)->get();
            $no = 1;
            foreach ($employees as $employee){
                $nAtt = [
                    'no'        => $no,
                    'type'      => 'Karyawan',
                    'name'      => $employee->name,
                    'date'      => $startDate->format('d M Y'),
                    'in_time'   => '-',
                    'out_time'  => '-',
                    'img_in'    => '',
                    'img_out'   => '',
                ];

                $dbAttInTime = Attendance::where('status_id', 1)
                    ->where('branch_id', $user->branch_id)
                    ->where('employee_id', $employee->id)
                    ->whereBetween('created_at', [$startDate->toDateTimeString(), $endDate->toDateTimeString()])
                    ->whereNotNull('in_time')
                    ->first();

                if($dbAttInTime != null){
                    $inTime = Carbon::parse($dbAttInTime->in_time);
                    $nAtt['in_time'] = $inTime->format('H:i');
                    $nAtt['img_in'] = Storage::disk('attendance_img')->url($dbAttInTime->img_path);
                }

                $dbAttOutTime = Attendance::where('status_id', 1)
                    ->where('branch_id', $user->branch_id)
                    ->where('employee_id', $employee->id)
                    ->whereBetween('created_at', [$startDate->toDateTimeString(), $endDate->toDateTimeString()])
                    ->whereNotNull('out_time')
                    ->first();

                if($dbAttOutTime != null){
                    $outTime = Carbon::parse($dbAttOutTime->out_time);
                    $nAtt['out_time'] = $outTime->format('H:i');
                    $nAtt['img_out'] = Storage::disk('attendance_img')->url($dbAttOutTime->img_path);
                }

                $data[] = $nAtt;
                $no++;
            }

            $partners = Partner::where('branch_id', $user->branch_id)->get();
            foreach ($partners as $partner){
                $nAtt = [
                    'no'        => $no,
                    'type'      => 'Therapis',
                    'name'      => $partner->name,
                    'date'      => $startDate->format('d M Y'),
                    'in_time'   => '-',
                    'out_time'  => '-',
                    'img_in'    => '',
                    'img_out'   => '',
                ];

                $dbAttInTime = Attendance::where('status_id', 1)
                    ->where('branch_id', $user->branch_id)
                    ->where('partner_id', $partner->id)
                    ->whereBetween('created_at', [$startDate->toDateTimeString(), $endDate->toDateTimeString()])
                    ->whereNotNull('in_time')
                    ->first();

                if($dbAttInTime != null){
                    $inTime = Carbon::parse($dbAttInTime->in_time);
                    $nAtt['in_time'] = $inTime->format('H:i');
                    $nAtt['img_in'] = Storage::disk('attendance_img')->url($dbAttInTime->img_path);
                }

                $dbAttOutTime = Attendance::where('status_id', 1)
                    ->where('branch_id', $user->branch_id)
                    ->where('partner_id', $partner->id)
                    ->whereBetween('created_at', [$startDate->toDateTimeString(), $endDate->toDateTimeString()])
                    ->whereNotNull('out_time')
                    ->first();

                if($dbAttOutTime != null){
                    $outTime = Carbon::parse($dbAttOutTime->out_time);
                    $nAtt['out_time'] = $outTime->format('H:i');
                    $nAtt['img_out'] = Storage::disk('attendance_img')->url($dbAttOutTime->img_path);
                }

                $data[] = $nAtt;
                $no++;
            }

            //dd($data);

            return view('admin.attendance_face.index', compact('data'));
        }
        catch (\Exception $ex){
            Session::flash('error', $ex);
            return back();
        }
    }

    public function getIndex(Request $request){
        $user = Auth::guard('admin')->user();
        $coas = Attendance::where('status_id', 1)
            ->where('branch_id', $user->branch_id)
            ->orderBy('employee_name', 'ASC')
            ->orderBy('partner_name', 'ASC')
            ->get();

        return DataTables::of($coas)
            ->setTransformer(new AttendanceTransformer)
            ->addIndexColumn()
            ->make(true);
    }

    public function create(Request $request){
        $data = [
            'employeeId'    => 0,
            'employeeName'  => '',
            'type'          => 'Absen Masuk',
        ];

        if($request->exists('employee_id')){
            $employeeId = $request->query('employee_id');
            $data['employeeId'] = $employeeId;
            $employee = Employee::where('id', $employeeId)->first();
            if($employee != null){
                $data['employeeName'] = $employee->name;
                //check for attendance
                $nowStart = Carbon::now('Asia/Jakarta')->startOfDay();
                $nowEnd = Carbon::now('Asia/Jakarta')->endOfDay();
                $tmpAtt = Attendance::where('employee_id', $employee->id)
                    //->where('in_time', '!=', null)
                    ->whereBetween('created_at', [$nowStart->toDateTimeString(), $nowEnd->toDateTimeString()])
                    ->orderByDesc('created_at')
                    ->first();
                //dd($tmpAtt);
                    //->first();

                if($tmpAtt != null){
                    if($tmpAtt->in_time != null){
                        $data['type'] = 'Absen Pulang';
                    }
                }
            }
        }

        return view('admin.attendance_face.create')->with($data);
    }

    public function store(Request $request){
        try{
            //dd($request->input('type'));
            if(!$request->hasFile('filename')){
                Session::flash('error', 'Harus foto!');
                return back();
            }
            $user = Auth::guard('admin')->user();
            $branch = Branch::where('id', $user->branch_id)->first();
            $employee = Employee::where('id', $request->input('employee_id'))->first();
            $department = Department::where('id', $employee->department_id)->first();
            $newAtt = new Attendance();
            $newAtt->status_id = 1;
            $newAtt->branch_id = $user->branch_id;
            $newAtt->created_by = $user->id;
            $newAtt->branch_name = $branch->name;
            $newAtt->branch_code = $branch->code;
            $newAtt->employee_id = $employee->id;
            $newAtt->employee_code = $employee->code;
            $newAtt->employee_name = $employee->name;
            $newAtt->employee_phone = $employee->phone;
            $newAtt->employee_department = $department->name;
            if($request->input('type') == 'Absen Masuk'){
                $newAtt->in_time = Carbon::now('Asia/Jakarta');
            }
            else{
                $newAtt->out_time = Carbon::now('Asia/Jakarta');
            }

            //$newAtt->save();

            // Save featured image
            //$folderPath = 'uploads/attendance';
            $type = 'ABSEN_PULANG';
            if($request->input('type') == 'Absen Masuk'){
                $type = 'ABSEN_MASUK';
            }
            $now = Carbon::now('Asia/Jakarta');
            $fileName = 'ATT_KARYAWAN_' . $type . '_' . $employee->name . '_'. $now->format('dmyHi') . '.webp';
            $uploadedPath = Storage::disk('attendance_img')->path('');
            $img = Image::read($request->file('filename'));
            $img->orient();

            if ($img->width() > $img->height() && $img->width() > 800) {
                $img->scale(width: 500);
            } elseif ($img->height() > 800) {
                $img->scale(height: 800);
            }

            $img->save($uploadedPath . '/' . $fileName, 70);

            //dd($path);
            $newAtt->img_path = $fileName;
            $newAtt->save();

            Session::flash('success', 'Sukses Melakukan Absen!');
            return Redirect::route('admin.attendance.create');
        }
        catch (\Exception $ex){
            Log::channel('hrd')->error('Admin/AttendanceFaceController - store error EX: '. $ex);
            Session::flash('error', $ex);
            return back();
        }
    }

    public function createPartner(Request $request){
        $data = [
            'partnerId'    => 0,
            'partnerName'  => '',
            'type'         => 'Absen Masuk',
        ];

        if($request->exists('partner_id')){
            $partnerId = $request->query('partner_id');
            $data['partnerId'] = $partnerId;
            $partner = Partner::where('id', $partnerId)->first();
            if($partner != null){
                $data['partnerName'] = $partner->name;
                //check for attendance
                $nowStart = Carbon::now('Asia/Jakarta')->startOfDay();
                $nowEnd = Carbon::now('Asia/Jakarta')->endOfDay();
                $tmpAtt = Attendance::where('partner_id', $partner->id)
                    //->where('in_time', '!=', null)
                    ->whereBetween('created_at', [$nowStart->toDateTimeString(), $nowEnd->toDateTimeString()])
                    ->orderByDesc('created_at')
                    ->first();
                //dd($tmpAtt);
                //->first();

                if($tmpAtt != null){
                    if($tmpAtt->in_time != null){
                        $data['type'] = 'Absen Pulang';
                    }
                }
            }
        }

        return view('admin.attendance_face.create_partners')->with($data);
    }

    public function storePartner(Request $request){
        try{
            //dd($request->input('type'));
            if(!$request->hasFile('filename')){
                Session::flash('error', 'Harus foto!');
                return back();
            }
            $user = Auth::guard('admin')->user();
            $branch = Branch::where('id', $user->branch_id)->first();
            $partner = Partner::where('id', $request->input('employee_id'))->first();
            $newAtt = new Attendance();
            $newAtt->status_id = 1;
            $newAtt->branch_id = $user->branch_id;
            $newAtt->created_by = $user->id;
            $newAtt->branch_name = $branch->name;
            $newAtt->branch_code = $branch->code;
            $newAtt->partner_id = $partner->id;
            $newAtt->partner_number = $partner->number;
            $newAtt->partner_name = $partner->name;
            $newAtt->partner_phone = $partner->phone;
            if($request->input('type') == 'Absen Masuk'){
                $newAtt->in_time = Carbon::now('Asia/Jakarta');
            }
            else{
                $newAtt->out_time = Carbon::now('Asia/Jakarta');
            }

            //$newAtt->save();

            // Save featured image
            //$folderPath = 'uploads/attendance';
            $type = 'ABSEN_PULANG';
            if($request->input('type') == 'Absen Masuk'){
                $type = 'ABSEN_MASUK';
            }
            $now = Carbon::now('Asia/Jakarta');
            $fileName = 'ATT_THERAPIS_'. $type. '_'. $partner->alias. '_'. $now->format('dmyHi') . '.webp';
            $uploadedPath = Storage::disk('attendance_img')->path('');
            $img = Image::read($request->file('filename'));
            $img->orient();

            if ($img->width() > $img->height() && $img->width() > 800) {
                $img->scale(width: 500);
            } elseif ($img->height() > 800) {
                $img->scale(height: 800);
            }

            $img->save($uploadedPath . '/' . $fileName, 70);

            //dd($path);
            $newAtt->img_path = $fileName;
            $newAtt->save();

            Session::flash('success', 'Sukses Melakukan Absen!');
            return Redirect::route('admin.attendance.create_partner');
        }
        catch (\Exception $ex){
            Log::channel('hrd')->error('Admin/AttendanceFaceController - storePartner error EX: '. $ex);
            Session::flash('error', $ex);
            return back();
        }
    }
}
