<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Libs\AutoNumberGenerator;
use App\Libs\JournalUtilities;
use App\Libs\Utilities;
use App\Models\AdminUser;
use App\Models\AdvancePayment;
use App\Models\AdvancePaymentDetail;
use App\Models\AttendanceData;
use App\Models\Branch;
use App\Models\Coa;
use App\Models\Currency;
use App\Models\Employee;
use App\Models\Partner;
use App\Models\PurchaseOrderHeader;
use App\Models\Supplier;
use App\Models\SupplierInvoiceDetail1;
use App\Transformer\AdvancePaymentTransformers;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\DataTables;
use Rap2hpoutre\FastExcel\FastExcel;

class AttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }


    public function index(Request $request){
        $user = Auth::guard('admin')->user();
        $filterStartDate = Carbon::today()->startOfWeek()->format('d-m-Y');
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
        $typeIds = AttendanceData::select('type_id')
            ->groupBy('type_id')
            ->get()
            ->pluck('type_id')
            ->toArray();

        $data = [
            'filterStartDate' => $filterStartDate,
            'filterEndDate' => $filterEndDate,
            'typeIds' => $typeIds,
        ];

        return view('admin.attendance.index')->with($data);
    }

    public function getIndex(Request $request){
        $user = Auth::guard('admin')->user();

        // Filter date
        $start = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
        $end = Carbon::createFromFormat('!d-m-Y', $request->input('end_date'), 'Asia/Jakarta');
        $end->endOfDay();

        $datas = AttendanceData::select(
                'timestamp',
                'state',
                'type_id',
                'machine_id',
                DB::raw("COALESCE(partner_name, employee_name) as partner_name")
            )
            ->whereBetween('timestamp', array($start->toDateTimeString(), $end->toDateTimeString()));

        if ($request->filled('type_id') && (int)$request->input('type_id') > 0) {
            $datas->where('type_id', (int)$request->input('type_id'));
        }
        if ($request->filled('state_id') && (int)$request->input('state_id') > 0) {
            $datas->where('state', (int)$request->input('state_id'));
        }

        return DataTables::of($datas)
            ->editColumn('timestamp', function ($data) {
                return $data->timestamp ? Carbon::parse($data->timestamp)->toIso8601String() : null;
            })
            ->rawColumns(['action', 'code'])
            ->make();
    }

    public function importForm(){
        try {
            $branches = DB::table('branches')->get();

            return view('admin.attendance.import', compact('branches'));
        }
        catch (\Exception $ex){
            dd($ex);
        }
    }


    public function importSubmit(Request $request){
        try {
            $branchId = intval($request->input('branch'));
            $branchDB = Branch::where('id', $branchId)->first();

            $reads = (new FastExcel)->import($request->file('excel'));
            // dd($reads);

            $partnerCounter = 1;
            $notfoundData = [];
            foreach ($reads as $read){
                $attendanceId = $read['No. ID'];
                $nama = $read['Nama'];
                $time = $read['Waktu'];
                $attendanceStatus = $read['Status'];
                $attendanceStatusBaru = $read['Status baru'];
                $pengecualian = $read['Pengecualian'];
                $operasi = $read['Operasi'];

                $timeString = Carbon::createFromFormat('!d/m/Y H:i', $time, 'Asia/Jakarta');
                $timeStartString = Carbon::createFromFormat('!d/m/Y H:i', $time, 'Asia/Jakarta')->startOfDay();
                $timeEndString = Carbon::createFromFormat('!d/m/Y H:i', $time, 'Asia/Jakarta')->endOfDay();

                //validasi jika status data Mengulang / Invalid
                if($pengecualian == "Mengulang" || $pengecualian == "Invalid"){
                    continue;
                }

                $typeId = 1;
                if($attendanceStatusBaru == 'Lembur Keluar' || $attendanceStatusBaru == 'C/Keluar'){
                    $typeId = 2;
                }

                $partner = Partner::where('attend_machine_id', $attendanceId)->first();
                $employee = Employee::where('attend_machine_id', $attendanceId)->first();
                if(empty($partner) && empty($employee)){
                    $notfoundData[] = $read;
                }
                else{
                    //validasi jika data sudah ada
                    if(!empty($partner)){
                        $existingData = AttendanceData::where('timestamp', '>=', $timeStartString)
                                ->where('timestamp', '<=', $timeEndString)
                                ->where('partner_id', $partner->id)
                                ->where('type_id', $typeId)
                                ->first();
                        if($existingData){
                            $existingData->state = $operasi == "" ? "ATTENDANCE" : $operasi;
                            $existingData->save();
                        }
                        else{
                            AttendanceData::create([
                                'state' => $operasi == "" ? "ATTENDANCE" : $operasi,
                                'machine_id' => $attendanceId,
                                'type_id'   => $typeId,
                                'timestamp' => Carbon::createFromFormat('!d/m/Y H:i', $time, 'Asia/Jakarta')->toDateTimeString(),
                                'employee_id' => empty($employee) ? null : $employee->id,
                                'employee_name' => empty($employee) ? null : $employee->name,
                                'partner_id' => empty($partner) ? null : $partner->id,
                                'partner_name' => empty($partner) ? null : $partner->name,
                            ]);
                        }
                        // if(AttendanceData::where('timestamp', '>=', $timeStartString)
                        //         ->where('timestamp', '<=', $timeEndString)
                        //         ->where('partner_id', $partner->id)
                        //         ->where('type_id', $typeId)
                        //         ->count() > 0){
                        //     continue;
                        // }
                    }
                    //validasi jika data sudah ada
                    if(!empty($employee)){
                        $existingData = AttendanceData::where('timestamp', '>=', $timeStartString)
                                ->where('timestamp', '<=', $timeEndString)
                                ->where('employee_id', $employee->id)
                                ->where('type_id', $typeId)
                                ->first();
                        if($existingData){
                            $existingData->state = $operasi == "" ? "ATTENDANCE" : $operasi;
                            $existingData->save();
                        }
                        else{

                            AttendanceData::create([
                                'state' => $operasi == "" ? "ATTENDANCE" : $operasi,
                                'machine_id' => $attendanceId,
                                'type_id'   => $typeId,
                                'timestamp' => Carbon::createFromFormat('!d/m/Y H:i', $time, 'Asia/Jakarta')->toDateTimeString(),
                                'employee_id' => empty($employee) ? null : $employee->id,
                                'employee_name' => empty($employee) ? null : $employee->name,
                                'partner_id' => empty($partner) ? null : $partner->id,
                                'partner_name' => empty($partner) ? null : $partner->name,
                            ]);
                        }
                        // if(AttendanceData::where('timestamp', '>=', $timeStartString)
                        //         ->where('timestamp', '<=', $timeEndString)
                        //         ->where('type_id', $typeId)
                        //         ->count() > 0){
                        //     continue;
                        // }
                    }
                    // AttendanceData::create([
                    //     'state' => $operasi == "" ? "ATTENDANCE" : $operasi,
                    //     'machine_id' => $attendanceId,
                    //     'type_id'   => $typeId,
                    //     'timestamp' => Carbon::createFromFormat('!d/m/Y H:i', $time, 'Asia/Jakarta')->toDateTimeString(),
                    //     'employee_id' => empty($employee) ? null : $employee->id,
                    //     'employee_name' => empty($employee) ? null : $employee->name,
                    //     'partner_id' => empty($partner) ? null : $partner->id,
                    //     'partner_name' => empty($partner) ? null : $partner->name,
                    // ]);
                }
            }

            if(count($notfoundData) > 0){
                Session::flash('error', 'Terdapat '.count($notfoundData).' data yang tidak ditemukan dalam Karyawan ataupun Terapis!');
            }
            else{
                Session::flash('success', 'Berhasil import data Absensi!');
            }
            return redirect()->route('admin.attendance.import');
        }
        catch (\Exception $ex){
            dd($ex);
        }
    }

}
