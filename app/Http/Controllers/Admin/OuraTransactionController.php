<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\CommissionPositionItem;
use App\Models\Employee;
use App\Models\Item;
use App\Models\Package;
use App\Models\PosPaymentType;
use App\Models\RoomType;
use App\Models\TransactionDetail;
use App\Models\TransactionHeader;
use App\Models\TransactionPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class OuraTransactionController extends Controller
{
    public function reportDailyRecapFilter(){
        $startDate = Carbon::now('Asia/Jakarta');
        $endDate = Carbon::now('Asia/Jakarta');
        //$endDate->addDay();

        $user = Auth::guard('admin')->user();
        $userData = AdminUser::where('id', $user->id)->first();

        $data = [
            'startDate' => $startDate->format('d-m-Y'),
            'endDate'   => $endDate->format('d-m-Y'),
        ];

        return view('admin.oura_transaction.report_recap_filter')->with($data);
    }

    public function generateDailyRecapReport(Request $request){
        try {
            $rules = array(
                'start_date'    => 'required',
                'end_date'      => 'required',
            );

            $message = array(
                'start_date.required'   => 'Tanggal Mulai Wajib dipilih!',
                'end_date.required'     => 'Tanggal Akhir Wajib dipilih!',
            );

            $validator = Validator::make($request->all(), $rules, $message);

            if ($validator->fails()) {
                return Response::json(['error' => $validator->errors()->all()]);
            }

            //get data
            $startDate = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
            $endDate = Carbon::createFromFormat('!d-m-Y', $request->input('end_date'), 'Asia/Jakarta');
            $endDate->addDay();

            $user = Auth::guard('admin')->user();
            $userData = AdminUser::where('id', $user->id)->first();

            $headers = TransactionHeader::where('branch_id', $userData->branch_id)
                ->whereBetween('created_at', [$startDate->toDateTimeString(), $endDate->toDateTimeString()])
                ->where('status_id', '!=', 16)
                ->get();

            $totalCustomer = 0;
            $discounts = [];

            $dbItems = Item::where('branch_id', $userData->branch_id)->get();
            $itemResults = [];
            $no = 1;
            foreach ($dbItems as $db){
                $tmpItem = [
                    'no'    => $no,
                    'id'    => $db->id,
                    'name'  => $db->name,
                    'qty'   => 0,
                ];
                $itemResults[] = $tmpItem;
                $no++;
            }

            $roomTypes = RoomType::where('branch_id', $userData->branch_id)->distinct()->get(['type_name']);
            $allRooms = [];
            foreach ($roomTypes as $type){
                $nRooms = [
                    'name'      => $type->type_name,
                    'packages'  => [],
                ];

                $tmpPackages = Package::with('room_type')->where('branch_id', $userData->branch_id)->get();
                foreach ($tmpPackages as $pg){
                    if($pg->room_type->type_name == $type->type_name){
                        $nPackage = [
                            'name'          => $pg->name,
                            'qty'           => 0,
                            'package_id'    => $pg->id,
                        ];

                        $nRooms['packages'][] = $nPackage;
                    }
                }
                $allRooms[] = $nRooms;
            }

            $extraCustomer = 0;
            $extraPartner = 0;
            $extendTimes = 0;
            $totalTreatment = 0;
            $totalFocCustomer = 0;
            $totalDiscount = 0;
            $no = 1;

            foreach ($headers as $header){
                $nDiscount = [
                    'no'            => $no,
                    'trx_number'    => $header->code,
                    'foc_type'      => $header->foc_type,
                    'foc_name'      => $header->foc_number,
                    'voucher_code'  => $header->voucher_code,
                    'amount'        => 0,
                ];

                $tmpDt = TransactionDetail::where('header_id', $header->id)->where('package_id', '!=', null)
                    ->get();

                //sales service
                $totalTreatment += count($tmpDt);
                $totalCustomer += count($tmpDt);

                foreach ($tmpDt as $dt){
                    $nDiscount['amount'] += $dt->discount_amount;
                    $extraCustomer += $dt->additional_person_qty;
                    $extraPartner += $dt->additional_partner_qty;
                    $extendTimes += $dt->extension_times;
                    $package = Package::with('room_type')->where('id', $dt->package_id)->first();
                    if($package != null){
                        $hIndex = 0;
                        $dIndex = 0;
                        foreach ($allRooms as $room){
                            //if this is emp
                            if($room['name'] == $package->room_type->type_name){
                                foreach ($room['packages'] as $tmpRP){
                                    if($tmpRP['name'] == $package->name){
                                        $tmpRP['qty']++;
                                        $tmpRP['qty'] += $dt->additional_person_qty;
                                        $allRooms[$hIndex]['packages'][$dIndex] = $tmpRP;
                                    }
                                    $dIndex++;
                                }
                            }
                            $hIndex++;
                        }
                    }
                }

                //check on items
                $tmpDtItem = TransactionDetail::where('header_id', $header->id)->where('item_id', '!=', null)
                    ->get();
                foreach ($tmpDtItem as $dtItem){
                    $nDiscount['amount'] += $dtItem->discount_amount;
                    $idx = 0;
                    foreach ($itemResults as $itemResult){
                        if($itemResult['id'] == $dtItem->item_id){
                            $itemResult['qty']++;
                            $itemResults[$idx] = $itemResult;
                        }
                        $idx++;
                    }
                }

                if(strtolower($header->foc_type) != 'no' && !empty($header->foc_type)){
                    $totalFocCustomer++;
                }
                if($nDiscount['amount'] > 0){
                    $discounts[] = $nDiscount;
                    $no++;
                }
            }

            $totalRegularCustomer = $totalCustomer - $totalFocCustomer;

            //sales commission for items
            $commResult = [];
            $commissionEmpLevel = CommissionPositionItem::where('branch_id', $userData->branch_id)
                ->where('item_id', '!=', 137)
                ->get();
            $employeeData = Employee::where('role', 'GRO')->where('branch_id', $userData->branch_id)
                ->where('id', '!=', 80)
                ->orderBy('name')
                ->get();

            foreach ($commissionEmpLevel as $comm){
                $item = Item::find($comm->item_id);
                $nCommResult = [
                    'item_name' => $item->name,
                    'total'     => 0,
                    'employees' => [],
                ];

                //check on item trx commission
                $employees = Employee::where('role', 'GRO')->where('branch_id', $userData->branch_id)
                    ->where('id', '!=', 80)
                    ->orderBy('name')
                    ->get();

                foreach ($employees as $employee){
                    $itemCount = DB::table('commission_employee_histories')
                        ->whereBetween('transaction_date', [$startDate->toDateTimeString(), $endDate->toDateTimeString()])
                        ->where('employee_id', $employee->id)
                        ->where('item_id', $item->id)
                        ->sum('item_qty');

                    $empData = [
                        'employee_name' => $employee->number . ' - ' . $employee->name,
                        'qty'           => $itemCount,
                    ];

                    $nCommResult['total'] += $empData['qty'];

                    $nCommResult['employees'][] = $empData;
                }
                $commResult[] = $nCommResult;
            }

            $commTheraResult = [];
            $partnerData = [];

            $payments = [];

            $tmpTotal = 0;
            $types = PosPaymentType::where('branch_id', $userData->branch_id)->get();

            foreach ($types as $type){
                $nPayment = [
                    'payment'   => '',
                    'total'     => 0,
                    'total_str' => 0,
                ];
                $paymentTypes = TransactionPayment::where('payment_type_id', $type->id)
                    ->whereHas('transaction_header', function($query) use ($startDate, $endDate){
                        $query
                            ->where(function($q){
                                $q->where('status_id', '=', 9)->orWhere('status_id', '=', 3);
                            })
                            ->whereBetween('created_at', [$startDate->toDateTimeString(), $endDate->toDateTimeString()]);
                    })
                    ->where('branch_id', $userData->branch_id)
                    ->get();

                $total = 0;
                foreach ($paymentTypes as $history){
                    // $tmpTrxHeader = TransactionHeader::where('id', $history->header_id)
                    //     ->whereBetween('created_at', [$startDate->toDateTimeString(), $endDate->toDateTimeString()])
                    //     ->first();
                    // if($tmpTrxHeader != null){
                    //     $total += min($tmpTrxHeader->grand_total, $history->amount);
                    // }

                    $total += $history->amount;
                }
                $nPayment['payment'] = $type->description;
                $nPayment['total'] = $total;
                $nPayment['total_str'] = number_format($total, '2', ',', '.');
                $payments[] = $nPayment;
                $tmpTotal += $total;
            }

            $totalStr = number_format($tmpTotal, '2', ',', '.');
            foreach ($discounts as $discount){
                $totalDiscount += $discount['amount'];
            }

            $data = [
                'branchId'              => $userData->branch_id,
                'totalCustomer'         => $totalCustomer,
                'totalFocCustomer'      => $totalFocCustomer,
                'totalRegularCustomer'  => $totalRegularCustomer,
                'allRooms'              => $allRooms,
                'extraPartner'          => $extraPartner,
                'extraPerson'           => $extraCustomer,
                'extendTimes'           => $extendTimes,
                'totalTreatment'        => $totalTreatment,
                'discounts'             => $discounts,
                'itemSalesEmp'          => $commResult,
                'payments'              => $payments,
                'totalStr'              => $totalStr,
                'totalDiscountStr'      => number_format($totalDiscount, '2', ',', '.'),
                'totalBrutoStr'         => number_format($totalDiscount + $tmpTotal, '2', ',', '.'),
                'startDate'             => $startDate->locale('id_ID')->format('d/m/y'),
                'endDate'               => $endDate->locale('id_ID')->format('d/m/y'),
                'diffDay'               => $endDate->diffInDays($startDate),
                'employees'             => $employeeData,
                'commTheraResult'       => $commTheraResult,
                'partnerData'           => $partnerData,
                'items'                 => $itemResults,
            ];

            return view('admin.oura_transaction.daily_recap')->with($data);
        }
        catch (\Exception $ex){
            Session::flash('error', $ex);
            return back();
        }
    }
}
