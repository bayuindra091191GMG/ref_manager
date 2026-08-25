<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Libs\AutoNumberGenerator;
use App\Libs\JournalUtilities;
use App\Libs\Utilities;
use App\Models\AdminUser;
use App\Models\ApHeader;
use App\Models\AuthorizationModule;
use App\Models\BankCashinHeader;
use App\Models\BankCashoutHeader;
use App\Models\BookingDetail;
use App\Models\BookingHeader;
use App\Models\Branch;
use App\Models\Coa;
use App\Models\CommissionEmployeeHistory;
use App\Models\CommissionItemMonthlyDetail;
use App\Models\CommissionPartnerHistory;
use App\Models\CommissionPartnerLevelItem;
use App\Models\CommissionPartnerLevelPackage;
use App\Models\CommissionPositionItem;
use App\Models\Employee;
use App\Models\GoodReceiveDetail;
use App\Models\GoodReceiveHeader;
use App\Models\Inventory;
use App\Models\InventoryHistory;
use App\Models\InventoryOutHeader;
use App\Models\Item;
use App\Models\JournalDetail;
use App\Models\JournalHeader;
use App\Models\JournalMap;
use App\Models\Package;
use App\Models\Partner;
use App\Models\PosPaymentType;
use App\Models\PurchaseOrderHeader;
use App\Models\SupplierInvoiceDetail1;
use App\Models\SupplierInvoiceHeader;
use App\Models\TransactionDetail;
use App\Models\TransactionDetailPartner;
use App\Models\TransactionHeader;
use App\Models\TransactionInvoice;
use App\Models\TransactionPayment;
use App\Models\User;
use App\Services\SaldoAwalGenerator;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PHPUnit\Exception;
use Rap2hpoutre\FastExcel\FastExcel;

class ScriptController extends Controller
{
    //    public function setGroCommission(){
    //        $items = DB::table('items')
    //            ->get();
    //
    //        foreach ($items as $item){
    //            if(!DB::table('commission_position_items')
    //                ->where('item_id', '=', $item->id)
    //                ->exists()){
    //                $newCommission = new CommissionPositionItem();
    //                $newCommission->branch_id = 1;
    //                $newCommission->position_id = 7;
    //                $newCommission->item_id = $item->id;
    //                $newCommission->commission_percentage = 0;
    //                $newCommission->commission_flat = 800;
    //                $newCommission->status_id = 1;
    //                $newCommission->save();
    //            }
    //        }
    //
    //        return 'SUCCESS';
    //    }

    public function fixInv()
    {
        try {
            $inventoryHistories = InventoryHistory::where('warehouse_id', '=', null)
                ->get();
            foreach ($inventoryHistories as $inv) {
                $item = Item::where('id', $inv->item_id)->first();

                $inv->branch_name = 'Emperor Spa';
                $inv->warehouse_id = 2;
                $inv->warehouse_name = 'Gudang Store';
                $inv->item_code = $item->code;
                $inv->item_name = $item->name;
                $inv->save();

                $invData = Inventory::where('item_id', $inv->item_id)
                    ->where('warehouse_id', 2)
                    ->where('branch_id', 1)->first();
                if ($invData != null) {
                    $invData->qty -= $inv->qty_sub;
                    $invData->save();
                }

                // Utilities::updateStock(1, 'min', $inv->qty_sub, $item->id, 2, '', '');
            }

            return 'sukses';
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function fixPayment()
    {
        try {
            $trx = TransactionHeader::all();
            foreach ($trx as $header) {
                $payment = TransactionPayment::where('header_id', $header->id)->first();
                if ($payment != null) {
                    if ($header->foc_type == 'no') {
                        $payment->amount = $header->grand_total;
                        $payment->save();
                    } else {
                        $payment->amount = 0;
                        $payment->save();
                    }
                }

            }

            return 'sukses';
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function fixStockCard()
    {
        try {
            $stockCards = InventoryHistory::whereNull('item_name')->get();
            foreach ($stockCards as $stockCard) {
                $stockCard->item_code = $stockCard->item->code;
                $stockCard->item_name = $stockCard->item->name;
                $stockCard->save();
            }

            return 'SUCCESS';
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function fixEmployeeCommission($branchId)
    {
        try {
            ini_set('memory_limit', '512M');
            ini_set('max_execution_time', '600');

            $trxItems = DB::table('transaction_details')
                ->join('transaction_headers', 'transaction_details.header_id', '=', 'transaction_headers.id')
                ->select('transaction_details.id as detail_id',
                    'transaction_details.header_id as header_id',
                    'transaction_details.item_id as item_id',
                    'transaction_details.qty as qty',
                    'transaction_headers.branch_id as branch_id',
                    'transaction_headers.employee_id as employee_id',
                    'transaction_headers.code as header_code',
                    'transaction_headers.created_at as header_created_at')
                ->where('transaction_headers.branch_id', '=', $branchId)
                ->where('transaction_headers.status_id', '=', 9)
                ->whereNotNull('item_id')
                ->whereBetween('transaction_headers.created_at', ['2023-04-01 00:00:00', '2023-04-01 23:40:00'])
                ->get();

            foreach ($trxItems as $trxItem) {
                $employee = DB::table('employees')->where('id', $trxItem->employee_id)->first();
                $totalEmployeeCommission = 0;
                if ($employee != null) {
                    $employeeCommission = DB::table('commission_position_items')
                        ->where('branch_id', $trxItem->branch_id)
                        ->where('position_id', $employee->position_id)
                        ->where('item_id', $trxItem->item_id)
                        ->where('status_id', '=', 1)
                        ->first();

                    $item = DB::table('items')->where('id', $trxItem->item_id)->first();

                    if (empty($employeeCommission) || empty($item)) {
                        continue;
                    }

                    $existingHistory = CommissionEmployeeHistory::where('branch_id', '=', $trxItem->branch_id)
                        ->where('transaction_id', '=', $trxItem->header_id)
                        ->where('item_id', '=', $item->id)
                        ->where('item_qty', '=', 0)
                        ->first();

                    if (! empty($existingHistory)) {
                        echo $trxItem->header_code.' '.$item->name.' '.$trxItem->qty.' UPDATE <br/>';

                        if ($employeeCommission->commission_percentage > 0) {
                            $commissionAdd = (floatval($trxItem->price) * intval($trxItem->qty)) * floatval($employeeCommission->commission_percentage / 100);
                        } else {
                            $commissionAdd = floatval($employeeCommission->commission_flat) > 0 ? floatval($employeeCommission->commission_flat) * intval($trxItem->qty) : 0;
                        }

                        $existingHistory->item_qty = $trxItem->qty;
                        $existingHistory->commission_add = $commissionAdd;
                        $existingHistory->commission_result = $commissionAdd;
                        $existingHistory->save();
                    } else {
                        echo $trxItem->header_code.' '.$item->name.' '.$trxItem->qty.' NEW <br/>';

                        if ($employeeCommission->commission_percentage > 0) {
                            $commissionAdd = (floatval($trxItem->price) * intval($trxItem->qty)) * floatval($employeeCommission->commission_percentage / 100);
                        } else {
                            $commissionAdd = floatval($employeeCommission->commission_flat) > 0 ? floatval($employeeCommission->commission_flat) * intval($trxItem->qty) : 0;
                        }

                        if ($commissionAdd > 0) {
                            $eCHistory = new CommissionEmployeeHistory;
                            $eCHistory->branch_id = $trxItem->branch_id;
                            $eCHistory->employee_id = $employee->id;
                            $eCHistory->employee_code = $employee->code;
                            $eCHistory->employee_name = $employee->name;
                            $eCHistory->employee_phone = $employee->phone;
                            $eCHistory->transaction_id = $trxItem->header_id;
                            $eCHistory->transaction_code = $trxItem->header_code;
                            $eCHistory->transaction_date = $trxItem->header_created_at;
                            $eCHistory->commission_percentage = $employeeCommission->commission_percentage;
                            $eCHistory->item_id = $item->id;
                            $eCHistory->item_qty = $trxItem->qty;
                            $eCHistory->item_code = $item->code;
                            $eCHistory->item_name = $item->name;
                            $eCHistory->commission_add = $commissionAdd;
                            $eCHistory->commission_result = $commissionAdd;
                            $eCHistory->save();

                            DB::table('commission_employee_histories')
                                ->where('id', '=', $eCHistory->id)
                                ->update([
                                    'created_at' => $trxItem->header_created_at,
                                    'updated_at' => $trxItem->header_created_at,
                                ]);

                            //                            DB::table('transaction_details')
                            //                                ->where('id', '=', $trxItem->detail_id)
                            //                                ->decrement('subtotal_after_commission', $commissionAdd);
                        }
                    }
                }
            }

            return 'SUCCESS';
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function generatePartnerCommission(Request $request)
    {
        try {
            ini_set('memory_limit', '512M');
            ini_set('max_execution_time', '600');

            $partnerId = 0;
            if ($request->exists('partner_id')) {
                $partnerId = intval($request->get('partner_id'));
            }

            $branchId = $request->get('branch_id');

            $trxPackages = DB::table('transaction_details')
                ->join('transaction_headers', 'transaction_details.header_id', '=', 'transaction_headers.id')
                ->select('transaction_details.id as detail_id',
                    'transaction_details.header_id as header_id',
                    'transaction_details.package_id as package_id',
                    'transaction_details.qty as qty',
                    'transaction_details.price as price',
                    'transaction_details.is_extended as is_extended',
                    'transaction_details.extension_amount as extension_amount',
                    'transaction_details.extension_times as extension_times',
                    'transaction_headers.branch_id as branch_id',
                    'transaction_headers.employee_id as employee_id',
                    'transaction_headers.code as header_code',
                    'transaction_headers.created_at as header_created_at')
                ->where('transaction_headers.branch_id', '=', $branchId)
                ->where('transaction_headers.status_id', '=', 9)
                ->whereNotNull('transaction_details.package_id')
                ->whereBetween('transaction_headers.created_at', [$request->get('start'), $request->get('end')])
                ->get();

            foreach ($trxPackages as $trxPackage) {
                $package = Package::where('id', $trxPackage->package_id)->first();
                if ($package->partner_count > 0) {
                    $partners = TransactionDetailPartner::where('transaction_detail_id', $trxPackage->detail_id);

                    if ($partnerId > 0) {
                        $partners = $partners->where('partner_id', '=', $partnerId);
                    }

                    $partners = $partners->get();

                    foreach ($partners as $tmp) {
                        $partner = Partner::with('partner_level')->where('id', $tmp->partner_id)->first();

                        $commissionAdd = 0;
                        $commissionPercentage = 0;
                        $commissionFlat = 0;

                        // Check exists
                        if (DB::table('commission_partner_histories')
                            ->where('transaction_id', '=', $trxPackage->header_id)
                            ->where('package_id', '=', $trxPackage->package_id)
                            ->where('partner_id', '=', $tmp->partner_id)
                            ->where('transaction_date', '>=', $request->get('start'))
                            ->exists()) {
                            continue;
                        }

                        if ($partner != null) {
                            // count commission for partners (terapis)
                            $partnerCommission = CommissionPartnerLevelPackage::where('branch_id', $branchId)
                                ->where('package_id', $package->id)
                                ->where('partner_level', $partner->partner_level->name)
                                ->where('status_id', '=', 1)
                                ->first();

                            if ($partnerCommission != null) {
                                $commissionPercentage = $partnerCommission->commission_percentage;

                                $afterDeduction = floatval($trxPackage->price) - floatval($partnerCommission->deduction_before);
                                if ($partnerCommission->commission_percentage > 0) {
                                    $commissionAdd = $afterDeduction * floatval($partnerCommission->commission_percentage / 100);
                                } else {
                                    $commissionAdd = floatval($partnerCommission->commission_flat) > 0 ? floatval($partnerCommission->commission_flat) : 0;
                                    $commissionFlat += $commissionAdd;
                                }

                                if ($partnerCommission->divider_after > 0) {
                                    $commissionAdd = $commissionAdd / $partnerCommission->divider_after;
                                }

                                // Check extend
                                if ($trxPackage->extension_amount > 0 && $partnerCommission->extend_flat > 0) {
                                    if ($partners->count() > 1) {
                                        if (str_contains($package->name, 'SULTAN')) {
                                            $commissionAdd += $partnerCommission->extend_flat;
                                            $commissionFlat += $partnerCommission->extend_flat;
                                        } else {
                                            if ($tmp->is_extend == 1) {
                                                $commissionAdd += $partnerCommission->extend_flat;
                                                $commissionFlat += $partnerCommission->extend_flat;
                                            }
                                        }
                                    } else {
                                        $commissionAdd += ($trxPackage->extension_times * $partnerCommission->extend_flat);
                                        $commissionFlat += ($trxPackage->extension_times * $partnerCommission->extend_flat);
                                    }
                                }
                            }

                            if ($commissionAdd > 0) {
                                $pCHistory = new CommissionPartnerHistory;
                                $pCHistory->branch_id = $branchId;
                                $pCHistory->partner_id = $partner->id;
                                $pCHistory->transaction_id = $trxPackage->header_id;
                                $pCHistory->transaction_code = $trxPackage->header_code;
                                $pCHistory->transaction_date = $trxPackage->header_created_at;
                                $pCHistory->commission_percentage = $commissionPercentage;
                                $pCHistory->commission_flat = $commissionFlat;
                                $pCHistory->partner_name = $partner->name;
                                $pCHistory->partner_number = $partner->number;
                                $pCHistory->partner_type = $partner->type;
                                $pCHistory->partner_alias = $partner->alias;
                                $pCHistory->package_id = $package->id;
                                $pCHistory->package_name = $package->name;
                                $pCHistory->package_code = $package->code;
                                $pCHistory->commission_add = $commissionAdd;
                                $pCHistory->commission_result = $commissionAdd;
                                $pCHistory->save();
                            }
                        }
                    }
                }
            }

            return 'SUCCESS';
        } catch (\Exception $ex) {
            dd($ex);
        }
    }

    public function testPdf()
    {
        try {
            // get data
            $startDate = Carbon::createFromFormat('!d-m-Y', '11-02-2023', 'Asia/Jakarta');
            $endDate = $startDate->copy()->addDays(1);

            $user = Auth::guard('admin')->user();
            $userData = AdminUser::where('id', $user->id)->first();

            $headers = TransactionHeader::where('branch_id', $userData->branch_id)
                ->whereBetween('created_at', [$startDate->toDateTimeString(), $endDate->toDateTimeString()])
                ->whereNotIn('locker_id', ['1', '2', '3', '5', '6', '100', '101', '102', '103', '104', '105', '106', '107', '108', '109', '110'])
                ->get();

            $totalCustomer = count($headers);

            $standardRoom = [
                'Royal Treatment 60"' => 0,
                'Royal Treatment 90"' => 0,
                'Royal Treatment 120"' => 0,
                'Caesar Treatment 60"' => 0,
                'Caesar Treatment 90"' => 0,
                'Caesar Treatment 120"' => 0,
            ];
            $vipRoom = [
                'Aphrodite Treatment 90"' => 0,
                'Aphrodite Treatment 120"' => 0,
                'Kingsman Treatment 90"' => 0,
                'Kingsman Treatment 120"' => 0,
            ];
            $vvipRoom = [
                'Paket Athena 2' => 0,
                'Paket Athena 3' => 0,
                'Paket Athena 4' => 0,
                'Paket Athena 5' => 0,
            ];
            $reflexiology = [
                'Paket Reflexiology 60 Menit' => 0,
                'Paket Reflexiology 90 Menit' => 0,
                'Reflexiology After Treatment' => 0,
            ];

            $extraCustomer = 0;
            $extraPartner = 0;
            $extendTimes = 0;
            $totalTreatment = 0;
            $totalFacilityOnly = 0;
            $totalFocCustomer = 0;
            // $totalCustomer = 0;

            foreach ($headers as $header) {
                if ($header->foc_type != 'no') {
                    $totalFocCustomer++;
                }
                $tmpDt = TransactionDetail::where('header_id', $header->id)->where('package_id', '!=', null)
                    ->get();

                $tmpDtFacility = TransactionDetail::where('header_id', $header->id)->where('is_facility_only', '=', 1)
                    ->get();

                // sales service
                $totalTreatment += count($tmpDt);
                //                $totalCustomer += count($tmpDt);
                $totalFacilityOnly += count($tmpDtFacility);
                foreach ($tmpDt as $dt) {
                    $extraCustomer += $dt->additional_person_qty;
                    $totalTreatment += $dt->additional_person_qty;
                    $extraPartner += $dt->additional_partner_qty;
                    $extendTimes += $dt->extension_times;
                    $package = Package::where('id', $dt->package_id)->first();
                    if ($package != null) {
                        // counting total customers
                        if ($package->room_type_id == 3) {
                            // dd($dt);
                            $totalCustomer--;
                            $totalCustomer += $package->partner_count;
                        }
                        $totalCustomer += $dt->additional_person_qty;
                        if ($header->foc_type != 'no' && $package->room_type_id == 3) {
                            // counting FOC
                            $totalFocCustomer--;
                            $totalFocCustomer += $package->partner_count;
                            $totalFocCustomer += $dt->additional_partner_qty;
                            // dd($totalFocCustomer);
                        }

                        if ($package->name == 'Paket Royal 60 Menit') {
                            $standardRoom['Royal Treatment 60"']++;
                        } elseif ($package->name == 'Paket Royal 90 Menit') {
                            $standardRoom['Royal Treatment 90"']++;
                        } elseif ($package->name == 'Paket Royal 120 Menit') {
                            $standardRoom['Royal Treatment 120"']++;
                        } elseif ($package->name == 'Paket Caesar 60 Menit') {
                            error_log($package->name);
                            $standardRoom['Caesar Treatment 60"']++;
                        } elseif ($package->name == 'Paket Caesar 90 Menit') {
                            $standardRoom['Caesar Treatment 90"']++;
                        } elseif ($package->name == 'Paket Caesar 120 Menit') {
                            $standardRoom['Caesar Treatment 120"']++;
                        } elseif ($package->name == 'Paket Aphrodite 90 Menit') {
                            $vipRoom['Aphrodite Treatment 90"']++;
                        } elseif ($package->name == 'Paket Aphrodite 120 Menit') {
                            $vipRoom['Aphrodite Treatment 120"']++;
                        } elseif ($package->name == 'Paket Kingsman 90 Menit') {
                            $vipRoom['Kingsman Treatment 90"']++;
                        } elseif ($package->name == 'Paket Kingsman 120 Menit') {
                            $vipRoom['Kingsman Treatment 120"']++;
                        } elseif ($package->name == 'Paket Athena 2') {
                            $vvipRoom['Paket Athena 2']++;
                        } elseif ($package->name == 'Paket Athena 3') {
                            $vvipRoom['Paket Athena 3']++;
                        } elseif ($package->name == 'Paket Athena 4') {
                            $vvipRoom['Paket Athena 4']++;
                        } elseif ($package->name == 'Paket Athena 5') {
                            $vvipRoom['Paket Athena 5']++;
                        } elseif ($package->name == 'Paket Reflexiology 60 Menit') {
                            $reflexiology['Paket Reflexiology 60 Menit']++;
                        } elseif ($package->name == 'Paket Reflexiology 90 Menit') {
                            $reflexiology['Paket Reflexiology 90 Menit']++;
                        } elseif ($package->name == 'Reflexiology After Treatment') {
                            $reflexiology['Reflexiology After Treatment']++;
                        }
                    }
                }
            }

            $totalRegularCustomer = $totalCustomer - $totalFocCustomer;
            // $totalCustomer += $totalFocCustomer;

            // sales commission for items
            $commResult = [];
            $commissionEmpLevel = CommissionPositionItem::where('branch_id', $userData->branch_id)
                ->where('item_id', '!=', 137)
                ->get();
            $employeeData = Employee::where('role', 'GRO')->where('branch_id', $userData->branch_id)
                ->where('id', '!=', 80)
                ->orderBy('name')
                ->get();

            foreach ($commissionEmpLevel as $comm) {
                $item = Item::find($comm->item_id);
                $nCommResult = [
                    'item_name' => $item->name,
                    'total' => 0,
                    'employees' => [],
                ];

                // check on item trx commission
                $employees = Employee::where('role', 'GRO')->where('branch_id', $userData->branch_id)
                    ->where('id', '!=', 80)
                    ->orderBy('name')
                    ->get();

                foreach ($employees as $employee) {
                    $itemCount = DB::table('commission_employee_histories')
                        ->whereBetween('created_at', [$startDate->toDateTimeString(), $endDate->toDateTimeString()])
                        ->where('employee_id', $employee->id)
                        ->where('item_id', $item->id)
                        ->sum('item_qty');

                    $empData = [
                        'employee_name' => $employee->number.' - '.$employee->name,
                        'qty' => $itemCount,
                    ];

                    $nCommResult['total'] += $empData['qty'];

                    $nCommResult['employees'][] = $empData;
                }
                $commResult[] = $nCommResult;
            }

            $payments = [];

            $tmpTotal = 0;
            $types = PosPaymentType::where('status_id', 1)->get();
            foreach ($types as $type) {
                $nPayment = [
                    'payment' => '',
                    'total' => 0,
                    'total_str' => 0,
                ];
                $paymentTypes = TransactionPayment::where('payment_type_id', $type->id)
                    ->whereBetween('created_at', [$startDate->toDateTimeString(), $endDate->toDateTimeString()])
                    ->get();

                $total = 0;
                foreach ($paymentTypes as $history) {
                    $tmpTrxHeader = TransactionHeader::where('id', $history->header_id)->first();
                    $total += min($tmpTrxHeader->grand_total, $history->amount);
                }
                $nPayment['payment'] = $type->description;
                $nPayment['total'] = $total;
                $nPayment['total_str'] = number_format($total, '2', ',', '.');
                $payments[] = $nPayment;
                $tmpTotal += $total;
            }

            $totalStr = number_format($tmpTotal, '2', ',', '.');

            $data = [
                'totalCustomer' => $totalCustomer,
                'totalFocCustomer' => $totalFocCustomer,
                'totalRegularCustomer' => $totalRegularCustomer,
                'standardRoom' => $standardRoom,
                'vipRoom' => $vipRoom,
                'vvipRoom' => $vvipRoom,
                'reflexiology' => $reflexiology,
                'extraPartner' => $extraPartner,
                'extraPerson' => $extraCustomer,
                'extendTimes' => $extendTimes,
                'totalTreatment' => $totalTreatment,
                'totalFacilityOnly' => $totalFacilityOnly,
                'itemSalesEmp' => $commResult,
                'payments' => $payments,
                'totalStr' => $totalStr,
                'currentDate' => $startDate->format('d-m-Y'),
                'diffDay' => $endDate->diffInDays($startDate),
                'employees' => $employeeData,
            ];

            $pdf = Pdf::loadView('documents.daily_recap', $data)
                ->setPaper('a4');

            return $pdf
                ->download('pdf_file.pdf');
        } catch (\Exception $ex) {
            dd($ex);
        }
    }

    public function fixJurnalCashout()
    {
        try {
            $user = Auth::guard('admin')->user();
            $userData = AdminUser::where('id', $user->id)->first();
            //            dd($userData);
            $cashouts = BankCashoutHeader::where('status_id', 13)->get();
            foreach ($cashouts as $cashout) {
                $dataDB = BankCashoutHeader::where('id', $cashout->id)->first();

                $journal = new JournalHeader;
                $journal->transaction_code = AutoNumberGenerator::generateAutonumberTrx('JN');
                $journal->transaction_type = 'CASH_OUT';
                $journal->transaction_date = $dataDB->payment_date;
                $journal->created_by = $userData->id;
                $journal->branch_id = $userData->branch_id;
                $journal->notes = $dataDB->description;
                $journal->save();

                $tmpCoa = Coa::where('id', $dataDB->coa_bank_id)->first();

                $journalDetail = new JournalDetail;
                $journalDetail->header_id = $journal->id;
                $journalDetail->branch_id = $userData->branch_id;
                $journalDetail->transaction_code = $journal->transaction_code;
                $journalDetail->reference_code = $dataDB->code;
                $journalDetail->account_id = $tmpCoa->id;
                $journalDetail->account_code = $tmpCoa->name;
                $journalDetail->account_name = $tmpCoa->code;
                $journalDetail->account_type_id = $tmpCoa->type_id;
                $journalDetail->account_type_name = $tmpCoa->type;
                $journalDetail->original_amount = $dataDB->total_price;
                $journalDetail->credit = $dataDB->total_price;
                $journalDetail->notes = $journal->notes;
                $journalDetail->transaction_type = 'CASH_OUT';
                $journalDetail->save();

                foreach ($dataDB->bank_cashout_details as $detail) {
                    $tmpCoa2 = Coa::where('id', $detail->coa_id)->first();
                    $journalDetail2 = new JournalDetail;
                    $journalDetail2->header_id = $journal->id;
                    $journalDetail2->branch_id = $userData->branch_id;
                    $journalDetail2->transaction_code = $journal->transaction_code;
                    $journalDetail2->reference_code = $dataDB->code;
                    $journalDetail2->account_id = $tmpCoa2->id;
                    $journalDetail2->account_code = $tmpCoa2->name;
                    $journalDetail2->account_name = $tmpCoa2->code;
                    $journalDetail2->account_type_id = $tmpCoa2->type_id;
                    $journalDetail2->account_type_name = $tmpCoa2->type;
                    $journalDetail2->original_amount = $detail->amount;
                    $journalDetail2->debit = $detail->amount;
                    $journalDetail2->notes = $detail->description;
                    $journalDetail2->transaction_type = 'CASH_OUT';
                    $journalDetail2->save();
                }
            }

            return 'SUCCESS';
        } catch (\Exception $ex) {
            return dd($ex);
        }
    }

    public function fixJurnalCashin()
    {
        try {
            $user = Auth::guard('admin')->user();
            $userData = AdminUser::where('id', $user->id)->first();
            //            dd($userData);
            $cashouts = BankCashinHeader::where('status_id', 13)->get();
            //            dd($cashouts);
            foreach ($cashouts as $dataDB) {
                // Journal Process START
                try {
                    $journal = new JournalHeader;
                    $journal->transaction_code = AutoNumberGenerator::generateAutonumberTrx('JN');
                    $journal->transaction_type = 'CASH_IN';
                    $journal->transaction_date = $dataDB->receive_date;
                    $journal->notes = 'CASH IN - '.$dataDB->code;
                    $journal->created_by = $userData->id;
                    $journal->branch_id = $dataDB->branch_id;
                    $journal->status_id = 13;
                    $journal->save();

                    $tmpCoa = Coa::where('id', $dataDB->coa_bank_id)->first();

                    $journalDetail = new JournalDetail;
                    $journalDetail->header_id = $journal->id;
                    $journalDetail->branch_id = $dataDB->branch_id;
                    $journalDetail->transaction_code = $journal->transaction_code;
                    $journalDetail->reference_code = $dataDB->code;
                    $journalDetail->account_id = $tmpCoa->id;
                    $journalDetail->account_name = $tmpCoa->name;
                    $journalDetail->account_code = $tmpCoa->code;
                    $journalDetail->account_type_id = $tmpCoa->type_id;
                    $journalDetail->account_type_name = $tmpCoa->type;
                    $journalDetail->original_amount = $dataDB->total_price;
                    $journalDetail->debit = $dataDB->total_price;
                    $journalDetail->credit = 0;
                    $journalDetail->notes = $journal->notes;
                    $journalDetail->transaction_type = 'CASH_IN';
                    $journalDetail->save();

                    foreach ($dataDB->bank_cashin_details as $detail) {
                        $tmpCoa2 = Coa::where('id', $detail->coa_id)->first();
                        $journalDetail2 = new JournalDetail;
                        $journalDetail2->header_id = $journal->id;
                        $journalDetail2->branch_id = $dataDB->branch_id;
                        $journalDetail2->transaction_code = $journal->transaction_code;
                        $journalDetail2->reference_code = $dataDB->code;
                        $journalDetail2->account_id = $tmpCoa2->id;
                        $journalDetail2->account_code = $tmpCoa2->code;
                        $journalDetail2->account_name = $tmpCoa2->name;
                        $journalDetail2->account_type_id = $tmpCoa2->type_id;
                        $journalDetail2->account_type_name = $tmpCoa2->type;
                        $journalDetail2->original_amount = $detail->amount;
                        $journalDetail2->debit = 0;
                        $journalDetail2->credit = $detail->amount;
                        $journalDetail2->notes = $detail->description;
                        $journalDetail2->transaction_type = 'CASH_IN';
                        $journalDetail2->save();
                    }
                } catch (\Exception $ex) {
                    dd($ex);
                }
            }

            return 'SUCCESS';
        } catch (\Exception $ex) {
            return dd($ex);
        }
    }

    public function checkDailyQuery(Request $request)
    {
        try {
            // query to check journal daily
            $paymentQuery = "select sum(jd.debit) as paymentAmount from journal_details jd
            inner join transaction_headers hd on hd.code = jd.reference_code
            where hd.created_at >= '".$request->query('start')."'
            and hd.created_at <= '".$request->query('end')."'
            and jd.transaction_type = 'POS'
            and jd.notes like '%payment%'
            and hd.branch_id = ".$request->query('branch_id').';';

            $pendapatanQuery = "select sum(jd.credit) as pendapatanAmount from journal_details jd
            inner join transaction_headers hd on hd.code = jd.reference_code
            where hd.created_at >= '".$request->query('start')."'
            and hd.created_at <= '".$request->query('end')."'
            and jd.transaction_type = 'POS'
            and jd.account_type_name like '%Pendapatan%'
            and hd.branch_id = ".$request->query('branch_id').';';

            $discountQuery = "select sum(jd.debit) as discountAmount from journal_details jd
            inner join transaction_headers hd on hd.code = jd.reference_code
            where hd.created_at >= '".$request->query('start')."'
            and hd.created_at <= '".$request->query('end')."'
            and jd.transaction_type = 'POS'
            and jd.account_name  = 'Potongan Penjualan'
            and hd.branch_id = ".$request->query('branch_id').';';

            $focQuery = "select sum(jd.debit) as focAmount from journal_details jd
            inner join transaction_headers hd on hd.code = jd.reference_code
            where hd.created_at >= '".$request->query('start')."'
            and hd.created_at <= '".$request->query('end')."'
            and jd.transaction_type = 'POS'
            and jd.account_name  = 'FOC'
            and hd.branch_id = ".$request->query('branch_id').';';

            // run Query
            $payment = DB::select($paymentQuery);
            $pendapatan = DB::select($pendapatanQuery);
            $discount = DB::select($discountQuery);
            $foc = DB::select($focQuery);

            $data = [
                'payment' => $payment[0]->paymentAmount,
                'pendapatan' => $pendapatan[0]->pendapatanAmount,
                'discount' => $discount[0]->discountAmount,
                'foc' => $foc[0]->focAmount,
                'totalDiscount' => $discount[0]->discountAmount + $foc[0]->focAmount,
                'diff' => $payment[0]->paymentAmount + $discount[0]->discountAmount + $foc[0]->focAmount - $pendapatan[0]->pendapatanAmount,
            ];

            dd($data);
        } catch (Exception $ex) {
            dd($ex);
        }
    }

    public function checkNotBalancedJournal(Request $request)
    {
        $sqlRaw = "select dt.header_id, min(hd.transaction_code) as transaction_code, sum(dt.debit) as debit, sum(dt.credit) as credit from journal_details dt
            inner join journal_headers hd on hd.id = dt.header_id
            where hd.transaction_date >= '".$request->query('start')."'
            and hd.transaction_date <= '".$request->query('end')."'
            and hd.branch_id = '".$request->query('branch_id')."'
            and hd.transaction_type = 'POS'
            group by dt.header_id;";

        $data = DB::select($sqlRaw);

        foreach ($data as $j) {
            if ($j->debit != $j->credit) {
                echo $j->transaction_code.'<br/>';
            }
        }

        return 'DONE';
    }

    public function copyItems(Request $request)
    {
        try {
            // $headers = TransactionHeader::where('branch_id', 1)
            //     ->where('created_at', '>=', '2024-06-19 00:00:00')
            //     ->where('created_at', '<=', '2024-06-23 00:00:00')
            //     ->where('foc_type', '!=', 'foc')
            //     ->where('status_id', 9)
            //     ->get();

            // $transactions = [];
            // foreach($headers as $header){
            //     $trxItem = TransactionDetail::where('header_id', $header->id)->where('item_id', '=', 1951)->first();
            //     if($trxItem != null){
            //         $transactions[] = $header->code;
            //         //count commission for GRO
            //         $employee = Employee::where('id', $header->employee_id)->first();
            //         $totalEmployeeCommission = 0;
            //         if($employee != null){
            //             $employeeCommission = CommissionPositionItem::where('branch_id', $header->branch_id)
            //                     ->where('position_id', $employee->position_id)
            //                     ->where('item_id', $trxItem->item_id)
            //                     ->where('status_id', '=', 1)
            //                     ->first();
            //             $item = Item::where('id', $trxItem->item_id)->first();

            //             if($employeeCommission != null && $item != null){
            //                 if($employeeCommission->commission_percentage > 0){
            //                     $commissionAdd = (floatval($trxItem->price) * intval($trxItem->qty)) * floatval($employeeCommission->commission_percentage / 100);
            //                 }
            //                 else{
            //                     $commissionAdd = floatval($employeeCommission->commission_flat) > 0 ? floatval($employeeCommission->commission_flat) * intval($trxItem->qty) : 0;
            //                 }

            //                 if($commissionAdd > 0){
            //                     $totalEmployeeCommission += $commissionAdd;

            //                     $eCHistory = new CommissionEmployeeHistory();
            //                     $eCHistory->branch_id = $header->branch_id;
            //                     $eCHistory->employee_id = $employee->id;
            //                     $eCHistory->employee_code = $employee->code;
            //                     $eCHistory->employee_name = $employee->name;
            //                     $eCHistory->employee_phone = $employee->phone;
            //                     $eCHistory->transaction_id = $header->id;
            //                     $eCHistory->transaction_code = $header->code;
            //                     $eCHistory->transaction_date = $header->created_at;
            //                     $eCHistory->commission_percentage = $employeeCommission->commission_percentage;
            //                     $eCHistory->item_id = $item->id;
            //                     $eCHistory->item_qty = $trxItem->qty;
            //                     $eCHistory->item_code = $item->code;
            //                     $eCHistory->item_name = $item->name;
            //                     //counting commission
            //                     $eCHistory->commission_add = $commissionAdd;
            //                     $eCHistory->commission_result = $commissionAdd;
            //                     $eCHistory->save();

            //                     $trxItem->subtotal_after_commission = $trxItem->subtotal - $commissionAdd;
            //                     $trxItem->save();
            //                 }
            //             }
            //         }
            //     }

            // }
            // dd($transactions);
            // check2 oura manyar
            // $startDate = Carbon::createFromFormat('!d-m-Y', '11-03-2024', 'Asia/Jakarta');
            // $endDate = Carbon::createFromFormat('!d-m-Y', '17-03-2024', 'Asia/Jakarta');
            // $endDate->addDays(1);

            // $user = Auth::guard('admin')->user();

            // $focQuery = "select sum((dt.qty * dt.price) + dt.extension_amount + dt.additional_person_amount + dt.additional_partner_amount) as total from transaction_details dt
            // inner join  transaction_headers hd on hd.id = dt.header_id where
            // hd.foc_type != 'NO'
            // and hd.status_id = 9
            // and hd.branch_id = 6
            // and hd.created_at >= '". $startDate->format('Y-m-d H:i:s') ."'
            // and hd.created_at <= '". $endDate->format('Y-m-d H:i:s') ."'";

            // $discountQuery = "select sum(hd.discount) as total from hokicuan_db.transaction_headers hd where
            // hd.branch_id = 6
            // and hd.status_id = 9
            // and hd.created_at >= '". $startDate->format('Y-m-d H:i:s') ."'
            // and hd.created_at <= '". $endDate->format('Y-m-d H:i:s') ."'";

            // $totalQuery = "select sum(py.amount) as total from transaction_payments py
            // inner join  transaction_headers hd on hd.id = py.header_id where
            // hd.branch_id = 6
            // and hd.status_id = 9
            // and hd.foc_type = 'no'
            // and hd.created_at >= '". $startDate->format('Y-m-d H:i:s') ."'
            // and hd.created_at <= '". $endDate->format('Y-m-d H:i:s') ."'";

            // $hdQuery = "select sum(dt.qty * dt.price) as total from transaction_headers hd
            // inner join transaction_details dt on hd.id = dt.header_id where
            // hd.branch_id = 6
            // and hd.status_id = 9
            // and hd.foc_type = 'no'
            // and hd.created_at >= '". $startDate->format('Y-m-d H:i:s') ."'
            // and hd.created_at <= '". $endDate->format('Y-m-d H:i:s') ."'";
            // //dd($totalQuery, $focQuery, $discountQuery);

            // $total = DB::select($totalQuery);
            // $foc = DB::select($focQuery);
            // $discount = DB::select($discountQuery);
            // $tmpTotal = $total[0]->total;
            // $totalDisc = $foc[0]->total + $discount[0]->total;
            // $tmpHd = DB::select($hdQuery);

            // $data = [
            //     'hdTotal'       => number_format($tmpHd[0]->total, '2', ',', '.'),
            //     'totalTrx'      => number_format($tmpTotal + $totalDisc, '2', ',', '.'),
            //     'discount'      => number_format($totalDisc, '2', ',', '.'),
            //     'total'         => number_format($tmpTotal, '2', ',', '.'),
            //     'startDate'     => $startDate->toDateString(),
            //     'endDate'       => $endDate->toDateString(),
            //     'foc'           => $foc[0]->total,
            // ];

            // dd($data);
            // Query untuk cek payment between transaction and journal
            $startDate = Carbon::createFromFormat('Y-m-d H:i:s', '2024-03-11 00:00:00');
            $endDate = Carbon::createFromFormat('Y-m-d H:i:s', '2024-03-12 00:00:00');
            $result = [];
            for ($i = 0; $i < 7; $i++) {
                $journalQuery = "select sum(jd.debit) as debit from journal_details jd
                    inner join transaction_headers hd on hd.code = jd.reference_code
                    where hd.created_at >= '".$startDate->format('Y-m-d H:i:s')."'
                    and hd.created_at <= '".$endDate->format('Y-m-d H:i:s')."'
                    and hd.branch_id = 6
                    and hd.status_id = 9
                    and hd.foc_type = 'no'
                    and jd.notes like '%Payment%';";

                $paymentQuery = "select sum(py.amount) as amount from transaction_payments py
                    inner join transaction_headers hd on hd.id = py.header_id
                    where hd.created_at >= '".$startDate->format('Y-m-d H:i:s')."'
                    and hd.created_at <= '".$endDate->format('Y-m-d H:i:s')."'
                    and hd.branch_id = 6
                    and hd.foc_type = 'no'
                    and hd.status_id = 9;";

                $journalAmount = DB::select($journalQuery);
                $paymentAmount = DB::select($paymentQuery);
                if ($journalAmount[0]->debit != $paymentAmount[0]->amount) {
                    $result[] = [
                        'date' => $startDate->format('Y-m-d H:i:s'),
                        'debitAmount' => $journalAmount[0]->debit,
                        'paymentAmount' => $paymentAmount[0]->amount,
                        'diff' => $journalAmount[0]->debit - $paymentAmount[0]->amount,
                    ];
                }

                $startDate->addDay();
                $endDate->addDay();
            }

            dd($result);

            // $tmpQuery = "select hd.code, sum((dt.price * qty) + dt.additional_partner_amount + dt.extension_amount + dt.additional_person_amount) as trx_total,
            // min(jHd.transaction_code) as jCode,
            // min(jHd.id) as journalId
            // from transaction_details dt
            // left join transaction_headers hd on hd.id = dt.header_id
            // left join journal_headers jHd on jHd.reference_id = hd.id
            // where hd.created_at >= '2023-11-01 00:00:00'
            // and hd.created_at <= '2023-12-01 00:00:00'
            // and hd.status_id = 9
            // and hd.foc_type != 'no'
            // and hd.branch_id = 1
            // group by hd.code;";
            // $data = DB::select($tmpQuery);
            // $testResult = [];
            // $nullChecker = [];
            // foreach($data as $d){
            //     $jDtTotal = JournalDetail::where('header_id', $d->journalId)->where('account_id', 259)->first();
            //     if($jDtTotal != null){
            //         if($jDtTotal->debit != $d->trx_total){
            //             $testResult[] = [
            //                 'trxCode'       => $d->code,
            //                 'trxTotal'      => $d->trx_total,
            //                 'journalCode'   => $d->jCode,
            //                 'journalTotal'  => $jDtTotal->debit,
            //             ];
            //         }
            //     }
            //     else{
            //         $nullChecker[] = [
            //             'trxCode'       => $d->code,
            //             'journalCode'   => $d->jCode,
            //             'jId'           => $d->journalId,
            //         ];
            //     }
            // }
            // dd($testResult, $nullChecker);

            // $qTest = "select hd.code, hd.id from transaction_headers hd
            // where hd.created_at >= '2024-01-13 00:00:00'
            // and hd.created_at <= '2024-01-12 00:00:00'
            // and hd.branch_id = 1";

            // $data = DB::select($qTest);
            // $noJournalOrPayment = [];
            // $codes = [];
            // foreach($data as $d){
            //     $paymentQuery = "select sum(py.amount) as amount from transaction_payments py where py.header_id = " . $d->id;
            //     $paymentTotal = DB::select($paymentQuery);

            //     $jd = "select sum(jd.debit) as debit, jd.transaction_code from journal_details jd where jd.reference_code = '" . $d->code . "'
            //     and jd.notes like '%payment%'
            //     group by jd.transaction_code";
            //     $journalDt = DB::select($jd);

            //     if(count($journalDt) > 0 && count($paymentTotal) > 0){
            //         if($paymentTotal[0]->amount != $journalDt[0]->debit){
            //             $data = [
            //                 'header_id'     => $d->id,
            //                 'header_code'   => $d->code,
            //                 'journal_code'  => $journalDt[0]->transaction_code,
            //                 'paymentAmount' => $paymentTotal[0]->amount,
            //                 'journalAmount' => $journalDt[0]->debit
            //             ];
            //             $codes[] = $data;
            //         }
            //     }
            //     else{
            //         $noJournalOrPayment[] = $d;
            //     }
            // }

            // dd($codes, $noJournalOrPayment);

            // journal
            // $sqlRaw = "select dt.header_id, min(hd.transaction_code) as transaction_code, sum(dt.debit) as debit, sum(dt.credit) as credit from journal_details dt
            // inner join journal_headers hd on hd.id = dt.header_id
            // where hd.transaction_date >= '2024-01-03 00:00:00'
            // and hd.transaction_date <= '2024-01-04 00:00:00'
            // and hd.branch_id = 4
            // and hd.transaction_type = 'POS'
            // group by dt.header_id;";

            // $data = DB::select($sqlRaw);

            // $codes = [];
            // foreach($data as $j){
            //     if($j->debit != $j->credit){
            //         $codes[] = $j->transaction_code;
            //     }
            // }
            // dd($codes);

            // code check not balance journal
            $sqlRaw = "select dt.header_id, min(hd.transaction_code) as transaction_code, sum(dt.debit) as debit, sum(dt.credit) as credit from journal_details dt
            inner join journal_headers hd on hd.id = dt.header_id
            where hd.transaction_date >= '2024-03-11 00:00:00'
            and hd.transaction_date <= '2024-03-18 00:00:00'
            and hd.branch_id = 6
            and hd.transaction_type = 'POS'
            group by dt.header_id;";

            $data = DB::select($sqlRaw);

            $codes = [];
            foreach ($data as $j) {
                if ($j->debit != $j->credit) {
                    $codes[] = $j->transaction_code;
                }
            }
            dd($codes);

            // real code for this function here
            $items = DB::table('items')
                ->whereIn('category_id', [2, 4])
                ->where('name', 'NOT LIKE', '%STAFF MEAL%')
                ->get();

            foreach ($items as $item) {
                $newDetail = new CommissionItemMonthlyDetail;
                $newDetail->header_id = 2;
                $newDetail->item_id = $item->id;
                $newDetail->status_id = 1;
                $newDetail->save();
            }

            return 'SUCCESS';
        } catch (\Exception $ex) {
            return dd($ex);
        }
    }

    public function checkTotalDifference()
    {
        $sqlRaw = "select dt.header_id, min(hd.code) as transaction_code, sum(dt.subtotal) as total_detail, max(hd.grand_total) as total_header from transaction_details dt
            inner join transaction_headers hd on hd.id = dt.header_id
            where hd.created_at >= '2023-10-01 00:00:00'
            and hd.created_at <= '2023-11-01 00:00:00'
            and hd.branch_id = 4
            group by dt.header_id;";

        $data = DB::select($sqlRaw);

        $codes = [];
        foreach ($data as $j) {
            if ($j->total_detail != $j->total_header) {
                $codes[] = $j->transaction_code;
            }
        }
        dd($codes);
    }

    /**
     * Insert your own branch id and status accordingly
     * change the value of $branchId to the branch you want
     */
    public function generateJournal(Request $request)
    {
        $posCode = '';
        try {
            $branchId = intval($request->get('branch_id'));

            // $items = DB::table('items')
            //     ->select('id')
            //     ->where('branch_id', $branchId)
            //     ->where('name', 'like', '%TS%')
            //     ->get();

            // $itemIds = [];
            // foreach($items as $item){
            //     $itemIds[] = $item->id;
            // }

            $headers = TransactionHeader::with('transaction_payments')
                ->where('branch_id', $branchId)
                ->where('created_at', '>', $request->get('start'))
                ->where('created_at', '<', $request->get('end'))
                ->where('status_id', 9)
                ->where('is_approved', 1)
                ->where('is_app_transaction', 1)
                ->get();

            // dd($headers);

            // $details = DB::table('transaction_details')
            //     ->join('transaction_headers', 'transaction_details.header_id', '=', 'transaction_headers.id')
            //     ->join('items', 'transaction_details.item_id', '=', 'items.id')
            //     ->select('transaction_details.header_id as header_id')
            //     ->where('transaction_headers.branch_id', $branchId)
            //     ->where('transaction_headers.created_at', '>', $request->get('start'))
            //     ->where('transaction_headers.created_at', '<', $request->get('end'))
            //     ->where('transaction_headers.status_id', 9)
            //     ->where('transaction_headers.is_approved', 1)
            //     ->where('items.name', 'LIKE', '%TS%')
            //     // ->whereIn('transaction_details.item_id', $itemIds)
            //     ->distinct()
            //     ->get('header_id');

            // $headerIds = [];
            // foreach($details as $detail){
            //     $headerIds[] = $detail->header_id;
            // }

            // $headers = TransactionHeader::whereIn('id', $headerIds)->get();
            // dd($headers);

            $isApplication = false;
            $branch = Branch::where('id', $branchId)->first();
            foreach ($headers as $header) {
                $jHeader = JournalHeader::with('journal_details')
                    ->where('notes', 'like', '%'.$header->code.'%')
                    ->where('created_at', '>', $request->get('start'))
                    ->first();

                $jHeader->notes = 'Automate - Journal Transaksi '.$header->code;
                $jHeader->updated_at = Carbon::now('Asia/Jakarta');
                $jHeader->save();

                foreach ($jHeader->journal_details as $dt) {
                    $dt->delete();
                }

                $isApplication = false;
                $dbPayments = TransactionPayment::where('header_id', $header->id)->get();
                $totalTrx = 0;
                $totalPackageTrx = 0;
                $allDetails = TransactionDetail::where('header_id', $header->id)->get();
                foreach ($allDetails as $dt) {
                    if ($dt->package_id != null) {
                        $totalTrx += (($dt->price * $dt->qty) + $dt->additional_person_amount + $dt->additional_partner_amount + $dt->extension_amount);
                        $totalPackageTrx += (($dt->price * $dt->qty) + $dt->additional_person_amount + $dt->additional_partner_amount + $dt->extension_amount);
                    } else {
                        $totalTrx += ($dt->price * $dt->qty);
                    }
                }
                $isFoc = false;
                if (strtolower($header->foc_type) == 'foc') {
                    $isFoc = true;
                    $journalMap = JournalMap::where('branch_id', $branchId)
                        ->where('transaction_type', 'POS_FOC')
                        ->first();
                    if ($journalMap != null) {
                        $coa = Coa::where('id', $journalMap->coa_id)->first();
                        if ($coa != null) {
                            // insert journal detail
                            $notes = 'Transaksi FOC '.$header->code;
                            JournalUtilities::insertRecord($jHeader, $branchId, $coa, 'DEBET', $totalTrx, $notes, $header->code);
                        }
                    }
                } elseif (strtoupper($header->foc_type) == 'FOC') {
                    $isFoc = true;

                    $journalMap = JournalMap::where('branch_id', $branchId)
                        ->where('transaction_type', 'POS_FOC')
                        ->first();
                    if ($journalMap != null) {
                        $coa = Coa::where('id', $journalMap->coa_id)->first();
                        if ($coa != null) {
                            // insert journal detail
                            $notes = 'Transaksi FOC '.$header->code;
                            JournalUtilities::insertRecord($jHeader, $branchId, $coa, 'DEBET', $totalTrx, $notes, $header->code);
                        }
                    }
                } elseif (strtolower($header->foc_type) == 'foc_package') {
                    $isFoc = true;
                    $journalMap = JournalMap::where('branch_id', $branchId)
                        ->where('transaction_type', 'POS_FOC')
                        ->first();
                    // dd($journalMap);
                    if ($journalMap != null) {
                        $coa = Coa::where('id', $journalMap->coa_id)->first();
                        if ($coa != null) {
                            // insert journal detail
                            $notes = 'Transaksi FOC '.$header->code;
                            JournalUtilities::insertRecord($jHeader, $branchId, $coa, 'DEBET', $totalPackageTrx, $notes, $header->code);
                        }
                    }
                }

                if (! $isFoc) {
                    foreach ($dbPayments as $dbPayment) {

                        if ($branch->is_oura === 1) {
                            $bookingHeader = DB::table('booking_headers')
                                ->where('transaction_id', '=', $header->id)
                                ->first();

                            if (! empty($bookingHeader)) {
                                $isApplication = true;

                                if ($dbPayment->amount > 0) {
                                    $bcaPaymentCoa = Coa::where('branch_id', '=', $branch->id)
                                        ->where('code', '=', '1-10004')
                                        ->first();

                                    $notes = 'Payment Transaksi Online '.$header->code;
                                    JournalUtilities::insertRecord($jHeader,
                                        $header->branch_id,
                                        $bcaPaymentCoa,
                                        'DEBET',
                                        $header->grand_total,
                                        $notes,
                                        $header->code);

                                    continue;
                                }
                            }
                        }

                        if ($dbPayment->amount > 0) {
                            $journalMap = JournalMap::where('branch_id', $branchId)
                                ->where(function ($q) {
                                    $q->where('transaction_type', 'POS_CASH')
                                        ->orWhere('transaction_type', 'POS_NON_CASH');
                                })
                                ->where('room_type_id', 'like', '%'.$dbPayment->payment_type_id.'%')
                                ->first();

                            if ($journalMap != null) {
                                // insert journal
                                $coa = Coa::where('id', $journalMap->coa_id)->first();
                                if ($coa != null) {
                                    // insert journal detail
                                    $type = 'KREDIT';
                                    if ($journalMap->journal_type == 'DEBET') {
                                        $type = 'DEBET';
                                    }

                                    $tmpAmount = $dbPayment->amount;
                                    if ($dbPayment->pos_payment_type->code == 'CASH' && $header->return_value > 0) {
                                        $tmpAmount -= $header->return;
                                    }

                                    $notes = 'Payment Transaksi '.$header->code;
                                    JournalUtilities::insertRecord($jHeader, $branchId, $coa, $type, $tmpAmount, $notes, $header->code);
                                }
                            }
                        }
                    }

                    if ($header->app_bonus_point > 0) {
                        $coa = Coa::where('branch_id', '=', $branchId)
                            ->where('name', '=', 'DISKON POINT MEMBER')
                            ->first();

                        JournalUtilities::insertRecord($jHeader,
                            $branchId, $coa, 'DEBET', $header->app_bonus_point, 'Point dari Transaksi '.$header->code, $header->code);

                        $coa = Coa::where('branch_id', '=', $branchId)
                            ->where('name', '=', 'HUTANG POINT MEMBER')
                            ->first();

                        JournalUtilities::insertRecord($jHeader,
                            $branchId, $coa, 'KREDIT', $header->app_bonus_point, 'Point dari Transaksi '.$header->code, $header->code);
                    }

                }

                // Package Journal
                $tmpDtPackage = TransactionDetail::where('header_id', $header->id)->whereNotNull('package_id')->get();
                $appFee = 0;
                foreach ($tmpDtPackage as $tmpPackage) {
                    $tmpPackageDetail = Package::where('id', $tmpPackage->package_id)->first();
                    if ($branchId == 1) {
                        $packageJournal = JournalMap::where('branch_id', $branchId)
                            ->where('transaction_type', 'POS_PENDAPATAN')
                            ->where('room_type_id', $tmpPackageDetail->room_type_id)
                            ->first();
                    } elseif ($branch->is_oura == 1) {
                        $packageJournal = JournalMap::where('branch_id', $branchId)
                            ->where('transaction_type', 'POS_PENDAPATAN')
                            ->where('coa_name', 'Pendapatan')
                            ->first();
                        if ($isApplication) {
                            $appFee += 25000;
                        }
                    } else {
                        $packageJournal = JournalMap::where('branch_id', $branchId)
                            ->where('transaction_type', 'POS_PENDAPATAN')
                            ->where('coa_name', 'Pendapatan Massage')
                            ->first();
                    }

                    if ($packageJournal != null) {
                        $coa = Coa::where('id', $packageJournal->coa_id)->first();
                        if ($coa != null) {
                            $value = ($tmpPackage->price * $tmpPackage->qty) + $tmpPackage->additional_partner_amount
                                + $tmpPackage->additional_person_amount + $tmpPackage->extension_amount;

                            $notes = 'Pendapatan Transaksi Paket '.$tmpPackageDetail->name.' : '.$value;
                            JournalUtilities::insertRecord($jHeader, $branchId, $coa, 'KREDIT', $value, $notes, $header->code);
                        }
                    }
                }

                if ($isApplication) {
                    if ($bookingHeader->type === 'home_service') {
                        $jHomeServiceFee = JournalMap::where('branch_id', $branch->id)->where('coa_code', '4-40000')->first();
                        $coaPendapatan = Coa::where('id', $jHomeServiceFee->coa_id)
                            ->first();
                        JournalUtilities::insertRecord($jHeader,
                            $header->branch_id,
                            $coaPendapatan,
                            $jHomeServiceFee->journal_type,
                            $appFee,
                            'Fee Home Service '.$header->code,
                            $header->code);
                    }

                    if ($bookingHeader->point_used > 0) {
                        $jPointMember = JournalMap::where('branch_id', $branch->id)
                            ->where('coa_name', 'HUTANG POINT MEMBER')
                            ->first();
                        $coaPointMember = Coa::where('id', $jPointMember->coa_id)
                            ->first();
                        JournalUtilities::insertRecord($jHeader,
                            $header->branch_id,
                            $coaPointMember,
                            'DEBET',
                            $bookingHeader->point_used,
                            'Penggunaan Point Member '.$header->code,
                            $header->code);
                    }
                }

                // Facility Journal
                $facilityOnly = TransactionDetail::where('header_id', $header->id)->where('is_facility_only', 1)->first();
                if ($facilityOnly != null) {
                    $facJournalMap = JournalMap::where('room_type_id', 99)->first();
                    if ($facJournalMap != null) {
                        $coa = Coa::where('id', $facJournalMap->coa_id)->first();
                        if ($coa != null) {
                            $notes = 'Automate - Transaksi '.$coa->coa_name;
                            JournalUtilities::insertRecord($jHeader, $branchId, $coa, $facJournalMap->journal_type, $facilityOnly->price * $facilityOnly->qty, $notes, $header->code);
                        }
                    }
                }

                // Member Journal
                if ($branchId == 1) {
                    $memberOnly = TransactionDetail::where('header_id', $header->id)->where('membership_id', 1)->first();
                    if ($memberOnly != null) {
                        $memberOnlyMap = JournalMap::where('transaction_type', 'POS_MEMBER')->where('branch_id', 1)->first();
                        if ($memberOnlyMap != null) {
                            $coa = Coa::where('id', $memberOnlyMap->coa_id)->first();
                            if ($coa != null) {
                                $notes = 'Automate - Transaksi '.$coa->coa_name.' '.$header->code;
                                JournalUtilities::insertRecord($jHeader, $branchId, $coa, $memberOnlyMap->journal_type, $memberOnly->price * $memberOnly->qty, $notes, $header->code);
                            }
                        }
                    }
                }

                // Insert Detail Item Journal
                $dtItems = TransactionDetail::where('header_id', $header->id)->where('item_id', '!=', null)->get();
                if ($branch->is_oura == 1) {
                    $hppJournalMap = JournalMap::where('branch_id', $branchId)
                        ->where(function ($q) {
                            $q->where('transaction_type', 'POS_HPP')
                                ->orWhere('transaction_type', 'POS_PERSEDIAAN')
                                ->orWhere('transaction_type', 'POS_PENJUALAN')
                                ->orWhere('transaction_type', 'POS_PENDAPATAN');
                        })
                        ->get();
                } else {
                    $hppJournalMap = JournalMap::where('branch_id', $branchId)
                        ->where(function ($q) {
                            $q->where('transaction_type', 'POS_HPP')
                                ->orWhere('transaction_type', 'POS_PERSEDIAAN')
                                ->orWhere('transaction_type', 'POS_PENJUALAN')
                                ->orWhere('coa_name', 'Pendapatan Meal')
                                ->orWhere('coa_name', 'Pendapatan - F&B')
                                ->orWhere('coa_name', 'Pendapatan Scrub')
                                ->orWhere('coa_name', 'Pendapatan Hot Stone')
                                ->orWhere('coa_name', 'PENDAPATAN MEAL INTERNAL')
                                ->orWhere('coa_name', 'PENDAPATAN MEAL EKSTERNAL')
                                ->orWhere('coa_name', 'Pendapatan Oil');
                        })
                        ->get();
                }
                // dd($hppJournalMap);

                $tmpJournalData = [];
                foreach ($hppJournalMap as $map) {
                    $nMap = [
                        'id' => $map->id,
                        'coa_id' => $map->coa_id,
                        'coa_name' => $map->coa_name,
                        'coa_code' => $map->coa_code,
                        'journal_type' => $map->journal_type,
                        'amount' => 0,
                    ];

                    $tmpJournalData[] = $nMap;
                }
                // dd($tmpJournalData);

                $foc = $header->foc_type;
                foreach ($dtItems as $dt) {
                    // check item in DB
                    if ($foc == 'FOC_ITEM' || $foc == 'FOC') {
                        // discount
                        $dt->discount_percentage = 100;
                        $dt->discount_amount = $dt->subtotal;
                    }

                    // insert journal item
                    $item = DB::table('items')
                        ->where('id', '=', $dt->item_id)
                            // ->where('name', 'not like', 'TS%')
                        ->first();
                    if ($item != null) {
                        // Journal Pendapatan
                        $hppName = 'HPP - Lain-lain';
                        $categoryName = 'Pendapatan - Lain-lain';
                        $persediaanName = 'Persediaan - Lain-lain';

                        if ($branchId == 1) {
                            if (str_contains($item->name, 'KIMO') || $item->category_id == 20) {
                                $categoryName = 'Pendapatan - Kimo';
                                $hppName = 'HPP - Kimo';
                                $persediaanName = 'Persediaan - Kimo';
                            } elseif (str_contains($item->name, 'OIL')) {
                                $categoryName = 'Pendapatan Oil';
                            } elseif (str_contains($item->name, 'HOT STONE')) {
                                $categoryName = 'Pendapatan Hot Stone';
                            }

                            if ($item->category_id == 2) {
                                $categoryName = 'Pendapatan - F&B';
                                $hppName = 'HPP - F&B';
                                $persediaanName = 'Persediaan - F&B';
                            }

                            if ($item->category_id == 21) {
                                $categoryName = 'Pendapatan Meal';
                            }

                            if ($item->category_id == 22) {
                                $categoryName = 'PENDAPATAN MEAL EKSTERNAL';
                            }

                            if ($item->category_id == 23) {
                                $categoryName = 'PENDAPATAN MEAL INTERNAL';
                            }

                            if ($item->category_id == 25) {
                                $categoryName = 'PENJUALAN LIQUOR';
                                $hppName = 'HPP - LIQUOR';
                                $persediaanName = 'PERSEDIAAN LIQUOR';
                            }

                            if (str_contains($item->name, 'NURU')) {
                                $categoryName = 'PENJUALAN NURU';
                                $hppName = 'HPP - NURU';
                                $persediaanName = 'PERSEDIAAN NURU';
                            }
                        } elseif ($branchId >= 6) {
                            $hppName = 'Beban Pokok Penjualan';
                            $categoryName = 'Pendapatan';
                            $persediaanName = 'Persediaan';
                            if ($item->category_id == 2) {
                                $persediaanName = 'PERSEDIAAN - F&B';
                            }

                            if ($item->name == 'OIL THERAPY ( HOT )') {
                                $categoryName = 'PENDAPATAN OIL THERAPY (HOT)';
                            } elseif (str_contains($item->name, 'TS')) {
                                $categoryName = 'Biaya Tambahan Pelanggan';
                            }
                            // check for HPP
                            if ($item->category_id == 2) {
                                $hppName = 'BEBAN POKOK PENJUALAN - F&B';
                            }
                        } else {
                            $hppName = 'HPP Lain-lain';
                            $categoryName = 'Penjualan Lain-lain';
                            $persediaanName = 'Persediaan Lain-lain';

                            if (str_contains($item->name, 'KIMO')) {
                                $categoryName = 'Penjualan Kimo';
                                $hppName = 'HPP Kimo';
                                $persediaanName = 'Persediaan Kimo';
                            } elseif (str_contains($item->name, 'SCRUB')) {
                                $categoryName = 'Pendapatan Scrub';
                            } elseif (str_contains($item->name, 'OIL')) {
                                $categoryName = 'Pendapatan Oil';
                            } elseif (str_contains($item->name, 'HOT STONE')) {
                                $categoryName = 'Pendapatan Hot Stone';
                            } elseif (str_contains($item->name, 'HOTSTONE')) {
                                $categoryName = 'Pendapatan Hot Stone';
                            }

                            if ($item->category_id == 2) {
                                $categoryName = 'Penjualan F&B';
                                $hppName = 'HPP F&B';
                                $persediaanName = 'Persediaan F&B';
                            }

                            if ($item->category_id == 20) {
                                $categoryName = 'Penjualan Kimo';
                                $hppName = 'HPP Kimo';
                                $persediaanName = 'Persediaan Kimo';
                            }
                        }

                        $idx = 0;
                        foreach ($tmpJournalData as $map) {
                            // if($map['coa_name'] == $categoryName){
                            //     $map['amount'] += (($dt->price * $dt->qty) - $dt->discount_amount);
                            // }
                            // else if($map['coa_name'] == $hppName){
                            //     $map['amount'] += (($item->cost_price * $dt->qty) - $dt->discount_amount);
                            // }
                            // else if($map['coa_name'] == $persediaanName){
                            //     $map['amount'] += (($item->cost_price * $dt->qty) - $dt->discount_amount);
                            // }

                            if ($map['coa_name'] == $categoryName) {
                                $map['amount'] += (($dt->price * $dt->qty));
                            }
                            if ($item->category_id != 21 && ! str_contains($item->name, 'TS')) {
                                if ($map['coa_name'] == $hppName) {
                                    $map['amount'] += (($item->cost_price * $dt->qty));
                                } elseif ($map['coa_name'] == $persediaanName) {
                                    $map['amount'] += (($item->cost_price * $dt->qty));
                                }
                            }
                            $tmpJournalData[$idx] = $map;
                            $idx++;
                        }
                    }
                }

                foreach ($tmpJournalData as $nJournal) {
                    if ($nJournal['amount'] > 0) {
                        $coa = Coa::where('id', $nJournal['coa_id'])->first();
                        if ($coa != null) {
                            // insert journal
                            $type = 'KREDIT';
                            if ($nJournal['journal_type'] == 'DEBET') {
                                $type = 'DEBET';
                            }
                            $notes = 'Automate - Transaksi '.$header->code;
                            JournalUtilities::insertRecord($jHeader, $branchId, $coa, $type, $nJournal['amount'], $notes, $header->code);
                        }
                    }
                }

                if ($header->discount > 0) {
                    $discountMap = JournalMap::where('branch_id', $branchId)->where('transaction_type', 'POS_POTONGAN')->first();
                    if ($discountMap != null) {
                        $coa = Coa::where('id', $discountMap->coa_id)->first();
                        if ($coa != null) {
                            $notes = 'Diskon Transaksi '.$header->discount;
                            JournalUtilities::insertRecord($jHeader, $branchId, $coa, 'DEBET', $header->discount, $notes, $header->code);
                        }
                    }
                }

                echo 'JOURNAL GENERATED id: '.$jHeader->id.'<br/>';
            }

            return 'SUCCESS';
        } catch (\Exception $ex) {
            return dd($posCode, $ex);
        }
    }

    public function generateJournalBu(Request $request)
    {
        try {
            $branchId = 6;

            $headers = $headers = TransactionHeader::with('transaction_payments')
                ->with('transaction_details')->where('branch_id', $branchId)
            // ->where('created_at', '>=', '2023-07-01 00:00:00')
            // ->where('created_at', '<', '2023-07-16 00:00:00')
                ->where('created_at', '>=', '2023-08-01 00:00:00')
                ->where('created_at', '<', '2023-09-01 00:00:00')
                ->where('status_id', 9)
                ->where('foc_type', '=', 'NO')
                // ->where('code', 'POS/2023/08/51458')
                ->get();

            $diffHeader = [];

            foreach ($headers as $header) {
                $nHeader = [
                    'header_id' => $header->id,
                    'header_code' => $header->code,
                    'amount' => $header->transaction_payments->sum('amount'),
                    'headerAmount' => $header->grand_total,
                    'detailAmount' => 0,
                    'headerDiscount' => $header->discount,
                    'totalWithDiscount' => $header->grand_total + $header->discount,
                ];
                foreach ($header->transaction_details as $dt) {
                    $nHeader['detailAmount'] += ($dt->qty * $dt->price);
                }
                if ($nHeader['detailAmount'] != $nHeader['headerDiscount'] + $nHeader['amount']) {
                    $diffHeader[] = $nHeader;
                }
            }

            dd($diffHeader);

            // $branchId = $request->query('branch_id');
            // update ts if branch is 6
            //            if($branchId == 6){
            //                $items = Item::where('branch_id', $branchId)
            //                    ->where('name', 'like', '%TS%')
            //                    ->get();
            //                foreach ($items as $item){
            //                    $item->cost_price = $item->price;
            //                    $item->save();
            //                }
            //            }
            // delete pos journal
            $jHeaders = JournalHeader::with('journal_details')->where('branch_id', $branchId)
        //    ->where('created_at', '>=', '2023-07-01 00:00:00')
        //    ->where('created_at', '<', '2023-07-16 00:00:00')
                ->where('created_at', '>=', '2023-09-01 00:00:00')
                ->where('created_at', '<', '2023-09-05 00:00:00')
                ->where('transaction_type', 'POS')
                 // ->where('transaction_code', 'JN/2023/09/17046')
                ->get();
            // $jHeaders = JournalHeader::with('journal_details')
            //     ->where('transaction_code', 'JN/2023/08/76203')
            //     ->get();
            foreach ($jHeaders as $header) {
                // $header->journal_details->delete();
                $canDelete = true;
                if (str_contains($header->notes, 'POS/2023/09/00353')) {
                    $canDelete = false;
                }
                if (str_contains($header->notes, 'POS/2023/09/00292')) {
                    $canDelete = false;
                }
                if (str_contains($header->notes, 'POS/2023/09/00284')) {
                    $canDelete = false;
                }
                if (str_contains($header->notes, 'POS/2023/09/00267')) {
                    $canDelete = false;
                }
                if ($canDelete) {
                    foreach ($header->journal_details as $dt) {
                        $dt->delete();
                    }
                    $header->delete();
                }
            }
            dd('sukses');
            $headers = TransactionHeader::where('branch_id', $branchId)
            // ->where('created_at', '>=', '2023-07-01 00:00:00')
            // ->where('created_at', '<', '2023-07-16 00:00:00')
                ->where('created_at', '>=', '2023-09-01 00:00:00')
                ->where('created_at', '<', '2023-09-06 00:00:00')
                ->where('status_id', 9)
                // ->where('code', 'POS/2023/08/51458')
                ->get();
            // $headers = TransactionHeader::where('branch_id', $branchId)
            //     ->where('code', 'POS/2023/08/47374')
            //     ->get();
            // dd($headers);

            // $journalNew = '';
            foreach ($headers as $header) {
                // Insert journal Header
                // also insert journal for kas
                $journal = new JournalHeader;
                $journal->transaction_code = AutoNumberGenerator::generateAutonumberTrx('JN');
                $journal->transaction_type = 'POS';
                $journal->transaction_date = $header->created_at;
                $journal->created_by = 1;
                $journal->branch_id = $branchId;
                $journal->notes = 'Automate - Journal Transaksi '.$header->code;
                $journal->status_id = 13;
                $journal->is_approve = 1;
                $journal->created_at = $header->created_at;
                $journal->save();
                // $journalNew = $journal->transaction_code;
                // dd($journal);

                $dbPayments = TransactionPayment::where('header_id', $header->id)->get();
                foreach ($dbPayments as $dbPayment) {
                    $totalTrx = 0;
                    $allDetails = TransactionDetail::where('header_id', $header->id)->get();
                    foreach ($allDetails as $dt) {
                        if ($dt->package_id != null) {
                            $totalTrx += (($dt->price * $dt->qty) + $dt->additional_person_amount + $dt->additional_partner_amount + $dt->extension_amount);
                        } else {
                            $totalTrx += ($dt->price * $dt->qty);
                        }
                    }

                    if (strtolower($header->foc_type) == 'foc') {
                        $journalMap = JournalMap::where('branch_id', $branchId)
                            ->where('transaction_type', 'POS_FOC')
                            ->first();
                        if ($journalMap != null) {
                            $coa = Coa::where('id', $journalMap->coa_id)->first();
                            if ($coa != null) {
                                // insert journal detail
                                $notes = 'Transaksi FOC '.$header->code;
                                JournalUtilities::insertRecord($journal, $branchId, $coa, 'DEBET', $totalTrx, $notes, $header->code);
                            }
                        }
                    } elseif (strtoupper($header->foc_type) == 'FOC') {
                        $totalTrx = 0;
                        $allDetails = TransactionDetail::where('header_id', $header->id)->get();
                        foreach ($allDetails as $dt) {
                            $totalTrx += ($dt->price * $dt->qty);
                        }
                        $journalMap = JournalMap::where('branch_id', $branchId)
                            ->where('transaction_type', 'POS_FOC')
                            ->first();
                        if ($journalMap != null) {
                            $coa = Coa::where('id', $journalMap->coa_id)->first();
                            if ($coa != null) {
                                // insert journal detail
                                $notes = 'Transaksi FOC '.$header->code;
                                JournalUtilities::insertRecord($journal, $branchId, $coa, 'DEBET', $totalTrx, $notes, $header->code);
                            }
                        }
                    } else {
                        $journalMap = JournalMap::where('branch_id', $branchId)
                            ->where(function ($q) {
                                $q->where('transaction_type', 'POS_CASH')
                                    ->orWhere('transaction_type', 'POS_NON_CASH');
                            })
                            ->where('room_type_id', 'like', '%'.$dbPayment->payment_type_id.'%')
                            ->first();

                        if ($journalMap != null) {
                            // insert journal
                            $coa = Coa::where('id', $journalMap->coa_id)->first();
                            if ($coa != null) {
                                // insert journal detail
                                $type = 'KREDIT';
                                if ($journalMap->journal_type == 'DEBET') {
                                    $type = 'DEBET';
                                }
                                $notes = 'Automate -Payment Transaksi '.$header->code;
                                JournalUtilities::insertRecord($journal, $branchId, $coa, $type, $dbPayment->amount, $notes, $header->code);
                            }
                        }
                    }
                }

                // Package Journal
                $tmpDtPackage = TransactionDetail::where('header_id', $header->id)->whereNotNull('package_id')->get();
                foreach ($tmpDtPackage as $tmpPackage) {
                    $tmpPackageDetail = Package::where('id', $tmpPackage->package_id)->first();
                    if ($branchId == 1) {
                        $packageJournal = JournalMap::where('branch_id', $branchId)
                            ->where('transaction_type', 'POS_PENDAPATAN')
                            ->where('room_type_id', $tmpPackageDetail->room_type_id)
                            ->first();
                    } elseif ($branchId == 6) {
                        $packageJournal = JournalMap::where('branch_id', $branchId)
                            ->where('transaction_type', 'POS_PENDAPATAN')
                            ->where('coa_name', 'Pendapatan')
                            ->first();
                    } else {
                        $packageJournal = JournalMap::where('branch_id', $branchId)
                            ->where('transaction_type', 'POS_PENDAPATAN')
                            ->where('coa_name', 'Pendapatan Massage')
                            ->first();
                    }

                    if ($packageJournal != null) {
                        $coa = Coa::where('id', $packageJournal->coa_id)->first();
                        if ($coa != null) {
                            $value = ($tmpPackage->price * $tmpPackage->qty) + $tmpPackage->additional_partner_amount
                                + $tmpPackage->additional_person_amount + $tmpPackage->extension_amount;

                            // if Oura check for TS
                            // check if its OURA
                            // if($header->branch_id >= 6){
                            //     $trxTs = TransactionDetail::where('header_id', $header->id)
                            //         ->where('item_id', '!=', null)
                            //         ->get();
                            //     foreach($trxTs as $ts){
                            //         $tmpTsItem = Item::where('id', $ts->item_id)->first();
                            //         if(str_contains($tmpTsItem->name, 'TS')){
                            //             //TS Exists
                            //             $value += $ts->subtotal;
                            //         }
                            //     }
                            // }

                            $notes = 'Pendapatan Transaksi Paket '.$tmpPackageDetail->name.' : '.$value;
                            JournalUtilities::insertRecord($journal, $branchId, $coa, 'KREDIT', $value, $notes, $header->code);
                        }
                    }
                }

                $facilityOnly = TransactionDetail::where('header_id', $header->id)->where('is_facility_only', 1)->first();
                if ($facilityOnly != null) {
                    $facJournalMap = JournalMap::where('room_type_id', 99)->first();
                    if ($facJournalMap != null) {
                        $coa = Coa::where('id', $facJournalMap->coa_id)->first();
                        if ($coa != null) {
                            $notes = 'Automate - Transaksi '.$coa->coa_name;
                            JournalUtilities::insertRecord($journal, $branchId, $coa, $facJournalMap->journal_type, $facilityOnly->subtotal, $notes, $header->code);
                        }
                    }
                }

                // Insert Detail Item Journal
                $dtItems = TransactionDetail::where('header_id', $header->id)->where('item_id', '!=', null)->get();
                if ($branchId == 6) {
                    $hppJournalMap = JournalMap::where('branch_id', $branchId)
                        ->where(function ($q) {
                            $q->where('transaction_type', 'POS_HPP')
                                ->orWhere('transaction_type', 'POS_PERSEDIAAN')
                                ->orWhere('transaction_type', 'POS_PENJUALAN')
                                ->orWhere('transaction_type', 'POS_PENDAPATAN');
                        })
                        ->get();
                } else {
                    $hppJournalMap = JournalMap::where('branch_id', $branchId)
                        ->where(function ($q) {
                            $q->where('transaction_type', 'POS_HPP')
                                ->orWhere('transaction_type', 'POS_PERSEDIAAN')
                                ->orWhere('transaction_type', 'POS_PENJUALAN')
                                ->orWhere('coa_name', 'Pendapatan Meal')
                                ->orWhere('coa_name', 'Pendapatan - F&B')
                                ->orWhere('coa_name', 'Pendapatan Scrub')
                                ->orWhere('coa_name', 'Pendapatan Hot Stone')
                                ->orWhere('coa_name', 'PENDAPATAN MEAL INTERNAL')
                                ->orWhere('coa_name', 'PENDAPATAN MEAL EKSTERNAL')
                                ->orWhere('coa_name', 'Pendapatan Oil');
                        })
                        ->get();
                }
                // dd($hppJournalMap);

                $tmpJournalData = [];
                foreach ($hppJournalMap as $map) {
                    $nMap = [
                        'id' => $map->id,
                        'coa_id' => $map->coa_id,
                        'coa_name' => $map->coa_name,
                        'coa_code' => $map->coa_code,
                        'journal_type' => $map->journal_type,
                        'amount' => 0,
                    ];

                    $tmpJournalData[] = $nMap;
                }
                // dd($tmpJournalData);

                $foc = $header->foc_type;
                foreach ($dtItems as $dt) {
                    // check item in DB
                    if ($foc == 'FOC_ITEM' || $foc == 'FOC') {
                        // discount
                        $dt->discount_percentage = 100;
                        $dt->discount_amount = $dt->subtotal;
                    }

                    // insert journal item
                    $item = DB::table('items')
                        ->where('id', '=', $dt->item_id)
                            // ->where('name', 'not like', 'TS%')
                        ->first();
                    if ($item != null) {
                        // Journal Pendapatan
                        $hppName = 'HPP - Lain-lain';
                        $categoryName = 'Pendapatan - Lain-lain';
                        $persediaanName = 'Persediaan - Lain-lain';

                        if ($branchId == 1) {
                            if (str_contains($item->name, 'KIMO')) {
                                $categoryName = 'Pendapatan - Kimo';
                                $hppName = 'HPP - Kimo';
                                $persediaanName = 'Persediaan - Kimo';
                            } elseif (str_contains($item->name, 'OIL')) {
                                $categoryName = 'Pendapatan Oil';
                            } elseif (str_contains($item->name, 'HOT STONE')) {
                                $categoryName = 'Pendapatan Hot Stone';
                            }

                            if ($item->category_id == 2) {
                                $categoryName = 'Pendapatan - F&B';
                                $hppName = 'HPP - F&B';
                                $persediaanName = 'Persediaan - F&B';
                            }

                            if ($item->category_id == 21) {
                                $categoryName = 'Pendapatan Meal';
                            }

                            if ($item->category_id == 22) {
                                $categoryName = 'PENDAPATAN MEAL EKSTERNAL';
                            }

                            if ($item->category_id == 23) {
                                $categoryName = 'PENDAPATAN MEAL INTERNAL';
                            }
                        } elseif ($branchId >= 6) {
                            $hppName = 'Beban Pokok Penjualan';
                            $categoryName = 'Pendapatan';
                            $persediaanName = 'Persediaan';
                            if ($item->category_id == 2) {
                                $persediaanName = 'PERSEDIAAN - F&B';
                            }

                            if ($item->name == 'OIL THERAPY ( HOT )') {
                                $categoryName = 'PENDAPATAN OIL THERAPY (HOT)';
                            } elseif (str_contains($item->name, 'TS')) {
                                $categoryName = 'Komisi terhutang';
                            }
                            // check for HPP
                            if ($item->category_id == 2) {
                                $hppName = 'BEBAN POKOK PENJUALAN - F&B';
                            }
                        } else {
                            $hppName = 'HPP Lain-lain';
                            $categoryName = 'Penjualan Lain-lain';
                            $persediaanName = 'Persediaan Lain-lain';

                            if (str_contains($item->name, 'KIMO')) {
                                $categoryName = 'Penjualan Kimo';
                                $hppName = 'HPP Kimo';
                                $persediaanName = 'Persediaan Kimo';
                            } elseif (str_contains($item->name, 'SCRUB')) {
                                $categoryName = 'Pendapatan Scrub';
                            } elseif (str_contains($item->name, 'OIL')) {
                                $categoryName = 'Pendapatan Oil';
                            } elseif (str_contains($item->name, 'HOT STONE')) {
                                $categoryName = 'Pendapatan Hot Stone';
                            } elseif (str_contains($item->name, 'HOTSTONE')) {
                                $categoryName = 'Pendapatan Hot Stone';
                            }

                            if ($item->category_id == 2) {
                                $categoryName = 'Penjualan F&B';
                                $hppName = 'HPP F&B';
                                $persediaanName = 'Persediaan F&B';
                            }

                            if ($item->category_id == 20) {
                                $categoryName = 'Penjualan Kimo';
                                $hppName = 'HPP Kimo';
                                $persediaanName = 'Persediaan Kimo';
                            }
                        }

                        $idx = 0;
                        foreach ($tmpJournalData as $map) {
                            // if($map['coa_name'] == $categoryName){
                            //     $map['amount'] += (($dt->price * $dt->qty) - $dt->discount_amount);
                            // }
                            // else if($map['coa_name'] == $hppName){
                            //     $map['amount'] += (($item->cost_price * $dt->qty) - $dt->discount_amount);
                            // }
                            // else if($map['coa_name'] == $persediaanName){
                            //     $map['amount'] += (($item->cost_price * $dt->qty) - $dt->discount_amount);
                            // }

                            if ($map['coa_name'] == $categoryName) {
                                $map['amount'] += (($dt->price * $dt->qty));
                            }
                            if ($item->category_id != 21 && ! str_contains($item->name, 'TS')) {
                                if ($map['coa_name'] == $hppName) {
                                    $map['amount'] += (($item->cost_price * $dt->qty));
                                } elseif ($map['coa_name'] == $persediaanName) {
                                    $map['amount'] += (($item->cost_price * $dt->qty));
                                }
                            }
                            $tmpJournalData[$idx] = $map;
                            $idx++;
                        }
                    }
                }

                foreach ($tmpJournalData as $nJournal) {
                    if ($nJournal['amount'] > 0) {
                        $coa = Coa::where('id', $nJournal['coa_id'])->first();
                        if ($coa != null) {
                            // insert journal
                            $type = 'KREDIT';
                            if ($nJournal['journal_type'] == 'DEBET') {
                                $type = 'DEBET';
                            }
                            $notes = 'Automate - Transaksi '.$header->code;
                            JournalUtilities::insertRecord($journal, $branchId, $coa, $type, $nJournal['amount'], $notes, $header->code);
                        }
                    }
                }

                if ($header->discount > 0) {
                    $discountMap = JournalMap::where('branch_id', $branchId)->where('transaction_type', 'POS_POTONGAN')->first();
                    if ($discountMap != null) {
                        $coa = Coa::where('id', $discountMap->coa_id)->first();
                        if ($coa != null) {
                            $notes = 'Diskon Transaksi '.$header->discount;
                            JournalUtilities::insertRecord($journal, $branchId, $coa, 'DEBET', $header->discount, $notes, $header->code);
                        }
                    }
                }
            }

            return 'sukses';
        } catch (\Exception $ex) {
            return dd($ex);
        }
    }

    public function deleteDoubleJournal()
    {
        try {
            // double journal stuff
            $query = "SELECT hd.branch_id, hd.code AS trx_code, jh.id AS journal_id, jh.transaction_code AS journal_code
                FROM journal_headers jh
                INNER JOIN transaction_headers hd ON hd.id = jh.reference_id
                WHERE hd.id IN (
                    SELECT reference_id
                    FROM journal_headers
                    GROUP BY reference_id
                    HAVING COUNT(*) > 1
                )
                and jh.transaction_type = 'POS'
                order by hd.branch_id, hd.code, jh.id;";
            $dbData = DB::select($query);
            // dd($dbData);
            $testData = [];
            $testDataCode = [];
            $tmpData = [
                'headerCode' => '',
                'journalId' => 0,
                'counter' => 0,
            ];
            foreach ($dbData as $data) {
                if ($tmpData['headerCode'] != $data->trx_code) {
                    $tmpData['headerCode'] = $data->trx_code;
                    $tmpData['journalId'] = $data->journal_id;
                    $tmpData['counter'] = 0;
                }

                $tmpData['counter']++;
                if ($tmpData['counter'] > 1) {
                    $testData[] = $data->journal_id;
                    $testDataCode[] = $data->trx_code;
                }
            }
            $count = count($testData);
            // dd($testData);
            foreach ($testData as $jId) {
                JournalDetail::where('header_id', $jId)->delete();
                JournalHeader::where('id', $jId)->delete();
            }
            echo 'Sukses Delete '.$count.' double journal!<br/>';
            if ($count > 0) {
                echo 'Transactions:';
                $idx = 1;
                foreach ($testDataCode as $dData) {
                    echo $idx.'. '.$dData.'<br/>';
                    $idx++;
                }
            }
        } catch (\Exception $ex) {
            return dd($ex);
        }
    }

    /**
     * Insert your own branch id and status accordingly
     * change the value of $branchId to the branch you want
     */
    public function generateSingleJournal(Request $request)
    {
        try {

            // setup stuff
            // $query = "select hd.id, it.name from transaction_headers hd
            //     inner join transaction_details dt on dt.header_id = hd.id
            //     inner join items it on it.id = dt.item_id
            //     where
            //     hd.branch_id = 7
            //     and hd.created_at >= '2024-09-01 00:00:00'
            //     and dt.item_id is not null
            //     and it.category_id = 2;";
            // $headers = DB::select($query);
            // foreach($headers as $hd){
            //     JournalUtilities::createJournal($hd->id);
            // dd($hd->id);
            // }
            // dd('Done');
            // dd($request->query('branch_id'), $request->query('code'));
            $header = TransactionHeader::with('transaction_payments')
                ->with('transaction_details')
                ->where('branch_id', $request->query('branch_id'))
                ->where('code', $request->query('code'))
                ->where('status_id', 9)
                ->first();

            // delete pos journal
            $jHeader = JournalHeader::with('journal_details')->where('branch_id', $request->query('branch_id'))
                ->where('notes', 'like', '%'.$request->query('code').'%')
                ->first();
            // dd($header, $jHeader);

            $branchId = $request->query('branch_id');
            $branch = Branch::where('id', $branchId)->first();

            if (! empty($jHeader)) {
                foreach ($jHeader->journal_details as $dt) {
                    $dt->delete();
                }

                $jHeader->notes = 'Automate - Journal Transaksi '.$header->code;
                $jHeader->updated_at = Carbon::now('Asia/Jakarta');
                $jHeader->save();
            } else {
                // Create journal header
                $jHeader = new JournalHeader;
                $jHeader->transaction_code = AutoNumberGenerator::generateAutonumberTrx('JN');
                $jHeader->transaction_type = 'POS';
                $jHeader->transaction_date = $header->created_at;
                $jHeader->created_by = $header->approved_by;
                $jHeader->branch_id = $header->branch_id;
                $jHeader->notes = 'Journal Transaksi '.$header->code;
                $jHeader->reference_id = $header->id;
                $jHeader->status_id = 1;
                $jHeader->created_at = $header->approved_at;
                $jHeader->updated_at = $header->approved_at;
                $jHeader->save();
            }

            $isApplication = false;
            $dbPayments = TransactionPayment::where('header_id', $header->id)->get();

            $totalTrx = 0;
            $totalPackageTrx = 0;
            $totalPackageCount = 0;
            $allDetails = TransactionDetail::where('header_id', $header->id)->get();
            foreach ($allDetails as $dt) {
                if ($dt->package_id != null) {
                    $totalTrx += (($dt->price * $dt->qty) + $dt->additional_person_amount + $dt->additional_partner_amount + $dt->extension_amount);
                    $totalPackageTrx += (($dt->price * $dt->qty) + $dt->additional_person_amount + $dt->additional_partner_amount + $dt->extension_amount);
                    $totalPackageCount++;
                } else {
                    $totalTrx += ($dt->price * $dt->qty);
                }
            }

            // Check if transaction from app
            $bookingHeader = null;
            if ($branch->is_oura == 1) {
                $bookingHeader = DB::table('booking_headers')
                    ->where('transaction_id', '=', $header->id)
                    ->first();
                $isApplication = ! empty($bookingHeader);
            }

            $isFoc = false;
            if (strtolower($header->foc_type) == 'foc') {
                $isFoc = true;
                $journalMap = JournalMap::where('branch_id', $branchId)
                    ->where('transaction_type', 'POS_FOC')
                    ->first();
                if ($journalMap != null) {
                    $coa = Coa::where('id', $journalMap->coa_id)->first();
                    if ($coa != null) {
                        // Check if HT transaction
                        if ($isApplication && $bookingHeader->type === 'home_service') {
                            $totalTrx += $bookingHeader->payment_extra_charge;
                        }

                        // insert journal detail
                        $notes = 'Transaksi FOC '.$header->code;
                        JournalUtilities::insertRecord($jHeader, $branchId, $coa, 'DEBET', $totalTrx, $notes, $header->code);
                    }
                }
            } elseif (strtoupper($header->foc_type) == 'FOC') {
                $isFoc = true;

                $journalMap = JournalMap::where('branch_id', $branchId)
                    ->where('transaction_type', 'POS_FOC')
                    ->first();
                if ($journalMap != null) {
                    $coa = Coa::where('id', $journalMap->coa_id)->first();
                    if ($coa != null) {
                        // Check if HT transaction
                        if ($isApplication && $bookingHeader->type === 'home_service') {
                            $totalTrx += $bookingHeader->payment_extra_charge;
                        }

                        // insert journal detail
                        $notes = 'Transaksi FOC '.$header->code;
                        JournalUtilities::insertRecord($jHeader, $branchId, $coa, 'DEBET', $totalTrx, $notes, $header->code);
                    }
                }
            } elseif (strtolower($header->foc_type) == 'foc_package') {
                $isFoc = true;
                $journalMap = JournalMap::where('branch_id', $branchId)
                    ->where('transaction_type', 'POS_FOC')
                    ->first();

                if ($journalMap != null) {
                    $coa = Coa::where('id', $journalMap->coa_id)->first();
                    if ($coa != null) {
                        // Check if HT transaction
                        if ($isApplication && $bookingHeader->type === 'home_service') {
                            $totalPackageTrx += $bookingHeader->payment_extra_charge;
                        }

                        // insert journal detail
                        $notes = 'Transaksi FOC '.$header->code;
                        JournalUtilities::insertRecord($jHeader, $branchId, $coa, 'DEBET', $totalPackageTrx, $notes, $header->code);
                    }
                }
            }

            if (! $isFoc) {
                foreach ($dbPayments as $dbPayment) {
                    if ($branch->is_oura === 1) {
                        if (! empty($bookingHeader)) {
                            if ($dbPayment->amount > 0) {
                                if ($dbPayment->payment_type_code == 'ONLINE') {
                                    $bcaPaymentCoa = Coa::where('branch_id', '=', $branch->id)
                                        ->where('code', '=', '1-10004')
                                        ->first();

                                    $notes = 'Payment Transaksi Online '.$header->code;
                                    JournalUtilities::insertRecord($jHeader,
                                        $header->branch_id,
                                        $bcaPaymentCoa,
                                        'DEBET',
                                        $header->grand_total,
                                        $notes,
                                        $header->code);

                                    continue;
                                }
                            }
                        }
                    }

                    if ($dbPayment->amount > 0) {
                        $journalMap = JournalMap::where('branch_id', $branchId)
                            ->where(function ($q) {
                                $q->where('transaction_type', 'POS_CASH')
                                    ->orWhere('transaction_type', 'POS_NON_CASH');
                            })
                            ->where('room_type_id', 'like', '%'.$dbPayment->payment_type_id.'%')
                            ->first();

                        if ($journalMap != null) {
                            // insert journal
                            $coa = Coa::where('id', $journalMap->coa_id)->first();
                            if ($coa != null) {
                                // insert journal detail
                                $type = 'KREDIT';
                                if ($journalMap->journal_type == 'DEBET') {
                                    $type = 'DEBET';
                                }

                                $tmpAmount = $dbPayment->amount;
                                if ($dbPayment->pos_payment_type->code == 'CASH' && $header->return_value > 0) {
                                    $tmpAmount -= $header->return_value;
                                }

                                $notes = 'Payment Transaksi '.$header->code;
                                JournalUtilities::insertRecord($jHeader, $branchId, $coa, $type, $tmpAmount, $notes, $header->code);
                            }
                        }
                    }
                }

                if ($header->app_bonus_point > 0) {
                    $coa = Coa::where('branch_id', '=', $branchId)
                        ->where('name', '=', 'DISKON POINT MEMBER')
                        ->first();

                    JournalUtilities::insertRecord($jHeader,
                        $branchId, $coa, 'DEBET', $header->app_bonus_point, 'Point dari Transaksi '.$header->code, $header->code);

                    $coa = Coa::where('branch_id', '=', $branchId)
                        ->where('name', '=', 'HUTANG POINT MEMBER')
                        ->first();

                    JournalUtilities::insertRecord($jHeader,
                        $branchId, $coa, 'KREDIT', $header->app_bonus_point, 'Point dari Transaksi '.$header->code, $header->code);
                }
            }

            // Package Journal
            $tmpDtPackage = TransactionDetail::where('header_id', $header->id)->whereNotNull('package_id')->get();
            $appFee = 0;
            foreach ($tmpDtPackage as $tmpPackage) {
                $tmpPackageDetail = Package::where('id', $tmpPackage->package_id)->first();
                if ($branchId == 1) {
                    // manually add the package shiets
                    if ($tmpPackageDetail->id == 116 || $tmpPackageDetail->id == 115) {
                        // if its Achiles
                        $packageJournal = JournalMap::where('branch_id', $branchId)
                            ->where('transaction_type', 'POS_PENDAPATAN')
                            ->where('category_2_id', $tmpPackageDetail->id)
                            ->first();
                    } else {
                        $packageJournal = JournalMap::where('branch_id', $branchId)
                            ->where('transaction_type', 'POS_PENDAPATAN')
                            ->where('room_type_id', $tmpPackageDetail->room_type_id)
                            ->first();
                    }
                } elseif ($branch->is_oura == 1) {
                    $packageJournal = JournalMap::where('branch_id', $branchId)
                        ->where('transaction_type', 'POS_PENDAPATAN')
                        ->where('coa_name', 'Pendapatan')
                        ->first();
                    if ($isApplication) {
                        $appFee += 25000;
                    }
                } else {
                    $packageJournal = JournalMap::where('branch_id', $branchId)
                        ->where('transaction_type', 'POS_PENDAPATAN')
                        ->where('coa_name', 'Pendapatan Massage')
                        ->first();
                }

                if ($packageJournal != null) {
                    $coa = Coa::where('id', $packageJournal->coa_id)->first();
                    if ($coa != null) {
                        $value = ($tmpPackage->price * $tmpPackage->qty) + $tmpPackage->additional_partner_amount
                            + $tmpPackage->additional_person_amount + $tmpPackage->extension_amount;

                        $notes = 'Pendapatan Transaksi Paket '.$tmpPackageDetail->name.' : '.$value;
                        JournalUtilities::insertRecord($jHeader, $branchId, $coa, 'KREDIT', $value, $notes, $header->code);
                    }
                }
            }

            if ($isApplication) {
                if ($bookingHeader->type === 'home_service') {
                    $jHomeServiceFee = JournalMap::where('branch_id', $branch->id)->where('coa_code', '4-40000')->first();
                    $coaPendapatan = Coa::where('id', $jHomeServiceFee->coa_id)
                        ->first();
                    JournalUtilities::insertRecord($jHeader,
                        $header->branch_id,
                        $coaPendapatan,
                        $jHomeServiceFee->journal_type,
                        $appFee,
                        'Fee Home Service '.$header->code,
                        $header->code);
                }

                if ($bookingHeader->point_used > 0) {
                    $jPointMember = JournalMap::where('branch_id', $branch->id)
                        ->where('coa_name', 'HUTANG POINT MEMBER')
                        ->first();
                    $coaPointMember = Coa::where('id', $jPointMember->coa_id)
                        ->first();
                    JournalUtilities::insertRecord($jHeader,
                        $header->branch_id,
                        $coaPointMember,
                        'DEBET',
                        $bookingHeader->point_used,
                        'Penggunaan Point Member '.$header->code,
                        $header->code);
                }
            }

            // Facility Journal
            $facilityOnly = TransactionDetail::where('header_id', $header->id)->where('is_facility_only', 1)->first();
            if ($facilityOnly != null) {
                $facJournalMap = JournalMap::where('room_type_id', 99)
                    ->where('branch_id', $branchId)->first();
                if ($facJournalMap != null) {
                    $coa = Coa::where('id', $facJournalMap->coa_id)->first();
                    if ($coa != null) {
                        $notes = 'Automate - Transaksi '.$coa->coa_name;
                        JournalUtilities::insertRecord($jHeader, $branchId, $coa, $facJournalMap->journal_type, $facilityOnly->price * $facilityOnly->qty, $notes, $header->code);
                    }
                }
            }

            // Member Journal
            if ($branchId == 1) {
                $memberOnly = TransactionDetail::where('header_id', $header->id)->where('membership_id', 1)->first();
                if ($memberOnly != null) {
                    $memberOnlyMap = JournalMap::where('transaction_type', 'POS_MEMBER')->where('branch_id', 1)->first();
                    if ($memberOnlyMap != null) {
                        $coa = Coa::where('id', $memberOnlyMap->coa_id)->first();
                        if ($coa != null) {
                            $notes = 'Automate - Transaksi '.$coa->coa_name.' '.$header->code;
                            JournalUtilities::insertRecord($jHeader, $branchId, $coa, $memberOnlyMap->journal_type, $memberOnly->price * $memberOnly->qty, $notes, $header->code);
                        }
                    }
                }
            }

            // Insert Detail Item Journal
            $dtItems = TransactionDetail::where('header_id', $header->id)->whereNotNull('item_id')->get();
            if ($branchId >= 6) {
                $hppJournalMap = JournalMap::where('branch_id', $branchId)
                    ->where(function ($q) {
                        $q->where('transaction_type', 'POS_HPP')
                            ->orWhere('transaction_type', 'POS_PERSEDIAAN')
                            ->orWhere('transaction_type', 'POS_PENJUALAN')
                            ->orWhere('transaction_type', 'POS_PENDAPATAN');
                    })
                    ->get();
            } else {
                $hppJournalMap = JournalMap::where('branch_id', $branchId)
                    ->where(function ($q) {
                        $q->where('transaction_type', 'POS_HPP')
                            ->orWhere('transaction_type', 'POS_PERSEDIAAN')
                            ->orWhere('transaction_type', 'POS_PENJUALAN')
                            ->orWhere('coa_name', 'Pendapatan Meal')
                            ->orWhere('coa_name', 'Pendapatan - F&B')
                            ->orWhere('coa_name', 'Pendapatan Scrub')
                            ->orWhere('coa_name', 'Pendapatan Hot Stone')
                            ->orWhere('coa_name', 'PENDAPATAN MEAL INTERNAL')
                            ->orWhere('coa_name', 'PENDAPATAN MEAL EKSTERNAL')
                            ->orWhere('coa_name', 'Pendapatan Oil');
                    })
                    ->get();
            }

            $tmpJournalData = [];
            foreach ($hppJournalMap as $map) {
                $nMap = [
                    'id' => $map->id,
                    'coa_id' => $map->coa_id,
                    'coa_name' => $map->coa_name,
                    'coa_code' => $map->coa_code,
                    'journal_type' => $map->journal_type,
                    'amount' => 0,
                ];

                $tmpJournalData[] = $nMap;
            }
            // dd($tmpJournalData);

            $foc = strtoupper($header->foc_type);
            foreach ($dtItems as $dt) {
                // check item in DB
                if ($foc == 'FOC_ITEM' || $foc == 'FOC') {
                    // discount
                    $dt->discount_percentage = 100;
                    $dt->discount_amount = $dt->subtotal;
                }

                // insert journal item
                $item = DB::table('items')
                    ->where('id', '=', $dt->item_id)
                        // ->where('name', 'not like', 'TS%')
                    ->first();
                if ($item != null) {
                    // Journal Pendapatan
                    $hppName = 'HPP - Lain-lain';
                    $categoryName = 'Pendapatan - Lain-lain';
                    $persediaanName = 'Persediaan - Lain-lain';

                    if ($branchId == 1) {
                        if (str_contains($item->name, 'KIMO') || $item->category_id == 20) {
                            $categoryName = 'Pendapatan - Kimo';
                            $hppName = 'HPP - Kimo';
                            $persediaanName = 'Persediaan - Kimo';
                        } elseif (str_contains($item->name, 'OIL')) {
                            $categoryName = 'Pendapatan Oil';
                        } elseif (str_contains($item->name, 'HOT STONE')) {
                            $categoryName = 'Pendapatan Hot Stone';
                        }

                        if ($item->category_id == 2) {
                            $categoryName = 'Pendapatan - F&B';
                            $hppName = 'HPP - F&B';
                            $persediaanName = 'Persediaan - F&B';
                        }

                        if ($item->category_id == 21) {
                            $categoryName = 'Pendapatan Meal';
                        }

                        if ($item->category_id == 22) {
                            $categoryName = 'PENDAPATAN MEAL EKSTERNAL';
                        }

                        if ($item->category_id == 23) {
                            $categoryName = 'PENDAPATAN MEAL INTERNAL';
                        }

                        if ($item->category_id == 25) {
                            $categoryName = 'PENJUALAN LIQUOR';
                            $hppName = 'HPP - LIQUOR';
                            $persediaanName = 'PERSEDIAAN LIQUOR';
                        }

                        if (str_contains($item->name, 'NURU')) {
                            $categoryName = 'PENJUALAN NURU';
                            $hppName = 'HPP - NURU';
                            $persediaanName = 'PERSEDIAAN NURU';
                        }

                        if (str_contains($item->name, 'ICE CREAM LICK SENSATION')) {
                            $categoryName = 'PENJUALAN ICE CREAM LICK SENSATION';
                            $hppName = 'HPP ICE CREAM LICK SENSATION';
                            $persediaanName = 'PERSEDIAAN ICE CREAM LICK SENSATION';
                        }
                    } elseif ($branchId >= 6) {
                        $hppName = 'Beban Pokok Penjualan';
                        $categoryName = 'Pendapatan';
                        $persediaanName = 'Persediaan';
                        if ($item->category_id == 2) {
                            $persediaanName = 'PERSEDIAAN - F&B';
                            $categoryName = 'PENJUALAN F&B';
                            $hppName = 'BEBAN POKOK PENJUALAN - F&B';
                        }

                        if ($item->name == 'OIL THERAPY ( HOT )') {
                            $categoryName = 'PENDAPATAN OIL THERAPY (HOT)';
                            $persediaanName = 'PERSEDIAAN - OIL THERAPI (HOT)';
                            $hppName = 'BEBAN POKOK PENJUALAN - OIL THERAPI (HOT)';
                        } elseif (str_contains($item->name, 'TS')) {
                            $categoryName = 'Biaya Tambahan Pelanggan';
                        }
                        // check for HPP
                        // if($item->category_id == 2){
                        //     $hppName = 'BEBAN POKOK PENJUALAN - F&B';
                        // }
                    } else {
                        $hppName = 'HPP Lain-lain';
                        $categoryName = 'Penjualan Lain-lain';
                        $persediaanName = 'Persediaan Lain-lain';

                        if (str_contains($item->name, 'KIMO')) {
                            $categoryName = 'Penjualan Kimo';
                            $hppName = 'HPP Kimo';
                            $persediaanName = 'Persediaan Kimo';
                        } elseif (str_contains($item->name, 'SCRUB')) {
                            $categoryName = 'Pendapatan Scrub';
                        } elseif (str_contains($item->name, 'OIL')) {
                            $categoryName = 'Pendapatan Oil';
                        } elseif (str_contains($item->name, 'HOT STONE')) {
                            $categoryName = 'Pendapatan Hot Stone';
                        } elseif (str_contains($item->name, 'HOTSTONE')) {
                            $categoryName = 'Pendapatan Hot Stone';
                        }

                        if ($item->category_id == 2) {
                            $categoryName = 'Penjualan F&B';
                            $hppName = 'HPP F&B';
                            $persediaanName = 'Persediaan F&B';
                        }

                        if ($item->category_id == 20) {
                            $categoryName = 'Penjualan Kimo';
                            $hppName = 'HPP Kimo';
                            $persediaanName = 'Persediaan Kimo';
                        }

                        if (str_contains($item->name, 'ADD ON')) {
                            $categoryName = 'PENJUALAN NURU';
                            $hppName = 'HPP NURU';
                            $persediaanName = 'PERSEDIAAN NURU';
                        }
                    }

                    $idx = 0;
                    foreach ($tmpJournalData as $map) {
                        // if($map['coa_name'] == $categoryName){
                        //     $map['amount'] += (($dt->price * $dt->qty) - $dt->discount_amount);
                        // }
                        // else if($map['coa_name'] == $hppName){
                        //     $map['amount'] += (($item->cost_price * $dt->qty) - $dt->discount_amount);
                        // }
                        // else if($map['coa_name'] == $persediaanName){
                        //     $map['amount'] += (($item->cost_price * $dt->qty) - $dt->discount_amount);
                        // }

                        if ($map['coa_name'] == $categoryName) {
                            $map['amount'] += (($dt->price * $dt->qty));
                        }
                        if ($item->category_id != 21 && ! str_contains($item->name, 'TS')) {
                            if ($map['coa_name'] == $hppName) {
                                $map['amount'] += (($item->cost_price * $dt->qty));
                            } elseif ($map['coa_name'] == $persediaanName) {
                                $map['amount'] += (($item->cost_price * $dt->qty));
                            }
                        }
                        $tmpJournalData[$idx] = $map;
                        $idx++;
                    }
                }
            }

            // dd($tmpJournalData);

            foreach ($tmpJournalData as $nJournal) {
                if ($nJournal['amount'] > 0) {
                    $coa = Coa::where('id', $nJournal['coa_id'])->first();
                    if ($coa != null) {
                        // insert journal
                        $type = 'KREDIT';
                        if ($nJournal['journal_type'] == 'DEBET') {
                            $type = 'DEBET';
                        }
                        $notes = 'Automate - Transaksi '.$header->code;
                        JournalUtilities::insertRecord($jHeader, $branchId, $coa, $type, $nJournal['amount'], $notes, $header->code);
                    }
                }
            }

            if (! $isFoc && $header->discount > 0) {
                $discountMap = JournalMap::where('branch_id', $branchId)->where('transaction_type', 'POS_POTONGAN')->first();
                if ($discountMap != null) {
                    $coa = Coa::where('id', $discountMap->coa_id)->first();
                    if ($coa != null) {
                        $notes = 'Diskon Transaksi '.$header->discount;
                        JournalUtilities::insertRecord($jHeader, $branchId, $coa, 'DEBET', $header->discount, $notes, $header->code);
                    }
                }
            }

            return $jHeader->transaction_code;
        } catch (\Exception $ex) {
            dd($ex);
        }
    }

    public function copyAuthorizations()
    {
        try {
            $authModules = DB::table('authorization_modules')
                ->where('branch_id', '=', 1)
                ->get();

            foreach ($authModules as $authModule) {
                for ($i = 2; $i <= 5; $i++) {
                    $newAuthModule = new AuthorizationModule;
                    $newAuthModule->branch_id = $i;
                    $newAuthModule->menu_level_1_name = $authModule->menu_level_1_name;
                    $newAuthModule->menu_level_2_name = $authModule->menu_level_2_name;
                    $newAuthModule->menu_level_3_name = $authModule->menu_level_3_name;
                    $newAuthModule->action = $authModule->action;
                    $newAuthModule->save();
                }
            }
        } catch (\Exception $ex) {
            return dd($ex);
        }
    }

    public function fixPartnerItemCommission($itemId, $branchId)
    {
        try {
            $details = DB::table('transaction_details')
                ->join('transaction_headers', 'transaction_details.header_id', '=', 'transaction_headers.id')
                ->select('transaction_details.id as id',
                    'transaction_headers.id as header_id',
                    'transaction_headers.code as header_code',
                    'transaction_details.price as item_price',
                    'transaction_headers.created_at as created_at')
                ->where('transaction_details.item_id', '=', $itemId)
                ->where('transaction_headers.status_id', '=', 9)
                ->where('transaction_headers.branch_id', '=', $branchId)
                ->whereBetween('transaction_headers.created_at', ['2023-07-01 00:00:00', '2023-08-01 01:00:00'])
                ->get();

            $item = DB::table('items')
                ->where('id', '=', $itemId)
                ->first();

            foreach ($details as $detail) {
                $tmpDetails = DB::table('transaction_details')
                    ->where('header_id', '=', $detail->header_id)
                    ->whereNotNull('package_id')
                    ->get();

                foreach ($tmpDetails as $tmpDetail) {
                    $partner = DB::table('transaction_detail_partners')
                        ->join('partners', 'transaction_detail_partners.partner_id', '=', 'partners.id')
                        ->select('partners.id as id',
                            'partners.code as partner_code',
                            'partners.name as partner_name',
                            'partners.number as partner_number',
                            'partners.alias as partner_alias',
                            'partners.type as partner_level',
                            'partners.is_can_use_hotstone as is_can_use_hotstone')
                        ->where('transaction_detail_partners.transaction_detail_id', '=', $tmpDetail->id)
                        ->where('partners.is_can_use_hotstone', '=', 1)
                        ->first();

                    if (empty($partner)) {
                        continue;
                    }

                    $tmpItemCommission = DB::table('commission_partner_level_items')
                        ->where('branch_id', '=', $branchId)
                        ->where('item_id', '=', $itemId)
                        ->where('partner_level', '=', $partner->partner_level)
                        ->first();

                    if (empty($tmpItemCommission)) {
                        continue;
                    }

                    if ($tmpItemCommission->commission_percentage > 0) {
                        $commissionAdd = floatval($detail->item_price) * floatval($tmpItemCommission->commission_percentage / 100);
                    } else {
                        $commissionAdd = floatval($tmpItemCommission->commission_flat) > 0 ? floatval($tmpItemCommission->commission_flat) : 0;
                    }

                    if ($commissionAdd > 0) {
                        $pCHistory = new CommissionPartnerHistory;
                        $pCHistory->branch_id = $branchId;
                        $pCHistory->partner_id = $partner->id;
                        $pCHistory->transaction_id = $detail->header_id;
                        $pCHistory->transaction_code = $detail->header_code;
                        $pCHistory->transaction_date = $detail->created_at;
                        $pCHistory->commission_percentage = 0;
                        $pCHistory->partner_name = $partner->partner_name;
                        $pCHistory->partner_number = $partner->partner_number;
                        $pCHistory->partner_type = $partner->partner_level;
                        $pCHistory->partner_alias = $partner->partner_alias;
                        $pCHistory->item_id = $item->id;
                        $pCHistory->item_name = $item->name;
                        $pCHistory->item_code = $item->code;

                        // counting commission
                        $pCHistory->commission_add = $commissionAdd;
                        $pCHistory->commission_result = $commissionAdd;
                        $pCHistory->save();
                    }
                }
            }

            return 'SUCCESS';
        } catch (\Exception $ex) {
            return dd($ex);
        }
    }

    public function generateOtherJournalMap()
    {
        try {
            $journalMaps = JournalMap::where('branch_id', 2)->get();
            for ($i = 3; $i <= 5; $i++) {
                foreach ($journalMaps as $journal) {
                    $coa = Coa::where('code', $journal->coa_code)->where('branch_id', $i)->first();
                    $nJournal = new JournalMap;
                    $nJournal->branch_id = $i;
                    $nJournal->coa_id = $coa->id;
                    $nJournal->coa_code = $journal->coa_code;
                    $nJournal->coa_name = $journal->coa_name;
                    $nJournal->journal_type = $journal->journal_type;
                    $nJournal->transaction_type = $journal->transaction_type;
                    $nJournal->save();
                }
            }

            return 'SUCCESS';
        } catch (\Exception $ex) {
            dd($ex);
        }
    }

    public function generateOtherCoas()
    {
        try {
            $coas = Coa::where('branch_id', 2)->get();
            for ($i = 3; $i <= 5; $i++) {
                foreach ($coas as $coa) {
                    $newCoa = new Coa;
                    $newCoa->code = $coa->code;
                    $newCoa->branch_id = $i;
                    $newCoa->name = $coa->name;
                    $newCoa->type_id = $coa->type_id;
                    $newCoa->type = $coa->type;
                    $newCoa->level = $coa->level;
                    $newCoa->parent_code = $coa->parent_code;
                    $newCoa->status_id = 1;

                    if (! empty($coa->parent_code)) {
                        $parentCoa = DB::table('coas')
                            ->where('code', '=', $coa->parent_code)
                            ->where('branch_id', '=', $i)
                            ->first();

                        $newCoa->parent_id = $parentCoa->id;
                    }

                    $newCoa->save();
                }
            }

            return 'SUCCESS';
        } catch (\Exception $ex) {
            dd($ex);
        }
    }

    public function fixSupplierInvoice()
    {
        try {
            $supplierInvoiceDetails = SupplierInvoiceDetail1::where('id', '>', 0)->get();
            foreach ($supplierInvoiceDetails as $supplierInvoiceDetail) {
                $grDB = GoodReceiveHeader::where('id', $supplierInvoiceDetail->gr_id)->first();
                $dataPO = PurchaseOrderHeader::where('id', $grDB->po_id)->first();

                $supplierInvoiceDetail->total = $dataPO->grandtotal;
                $supplierInvoiceDetail->save();
            }

            return 'SUCCESS';
        } catch (\Exception $ex) {
            dd($ex);
        }
    }

    public function checkEmptyJournal($branchId, $type)
    {
        $journals = DB::table('journal_headers')
            ->leftJoin('journal_details', 'journal_details.header_id', '=', 'journal_headers.id')
            ->select('journal_headers.transaction_code as code',
                'journal_headers.notes as notes',
                'journal_headers.reference_id as reference_id',
                'journal_details.id as detail_id')
            ->where('journal_headers.branch_id', '=', $branchId)
            ->where('journal_headers.transaction_type', '=', $type)
            ->whereBetween('journal_headers.created_at', ['2023-09-01 00:00:00', '2024-01-10 01:00:00'])
            ->get();

        foreach ($journals as $journal) {
            if (empty($journal->detail_id)) {
                echo $journal->code.' '.$journal->notes.' EMPTY DETAILS<br/>';
            }
        }

        return 'SUCCESS';
    }

    public function fixJurnalGoodReceive($branchId)
    {
        try {
            $jurnals = JournalHeader::where('transaction_type', 'GOOD_RECEIVE')
                ->where('branch_id', $branchId)
                ->whereBetween('created_at', ['2023-09-01 00:00:00', '2024-01-10 01:00:00'])
                ->get();

            $datas = [];
            foreach ($jurnals as $jurnal) {
                if (count($jurnal->journal_details) > 0) {
                    foreach ($jurnal->journal_details as $dt) {
                        $dt->delete();
                    }
                }
                $noGR = str_replace('Jurnal Good Receive ', '', $jurnal->notes);

                $dataDB = GoodReceiveHeader::where('code', $noGR)->first();
                $grDetails = GoodReceiveDetail::where('header_code', $noGR)->get();

                $totalHutangBerelasi = 0;
                $totalHutangUsaha = 0;

                foreach ($grDetails as $detail) {

                    // Journal Detail Process Item START
                    $itemsDB = Item::where('id', $detail->item_id)->first();
                    if (! empty($itemsDB->category_2_id)) {
                        $journalMap = JournalMap::where('branch_id', $jurnal->branch_id)
                            ->where('transaction_type', 'GOOD_RECEIVE')
                            ->where('journal_type', 'DEBET')
                            // ->where('coa_name', '!=', 'BEBAN ONGKIR')
                            ->where('category_2_id', $itemsDB->category_2_id)
                            ->first();

                        if (! empty($journalMap)) {
                            $coa = Coa::where('id', $journalMap->coa_id)->first();

                            $jDt = new JournalDetail;
                            $jDt->branch_id = $jurnal->branch_id;
                            $jDt->header_id = $jurnal->id;
                            $jDt->account_code = $coa->code;
                            $jDt->account_type_id = $coa->type_id;
                            $jDt->account_name = $coa->name;
                            $jDt->account_id = $coa->id;
                            $jDt->account_type_name = $coa->type;
                            $jDt->debit = $detail->total;
                            $jDt->credit = 0;
                            $jDt->notes = 'Good Receive '.$dataDB->code.' item '.$itemsDB->name;
                            $jDt->reference_code = $dataDB->code;
                            $jDt->transaction_code = $jurnal->transaction_code;
                            $jDt->transaction_type = 'GOOD_RECEIVE';
                            $jDt->original_amount = $detail->total;
                            $jDt->save();

                            if ($itemsDB->category_2_id == 14) {
                                $totalHutangBerelasi += $detail->total;
                            } else {
                                $totalHutangUsaha += $detail->total;
                            }
                        }
                    }
                    // Journal Detail Process Item END
                }

                // Journal Detail Process beban ongkir START
                if (! empty($dataDB->beban_ongkir)) {
                    if ($dataDB->beban_ongkir != 0) {
                        $journalMapBebanOngkir = JournalMap::where('branch_id', $jurnal->branch_id)
                            ->where('transaction_type', 'GOOD_RECEIVE')
                            ->where('coa_name', 'BEBAN ONGKIR')
                            ->first();
                        if (! empty($journalMapBebanOngkir)) {
                            $coa = Coa::where('id', $journalMapBebanOngkir->coa_id)->first();

                            $jDt = new JournalDetail;
                            $jDt->branch_id = $jurnal->branch_id;
                            $jDt->header_id = $jurnal->id;
                            $jDt->account_code = $coa->code;
                            $jDt->account_type_id = $coa->type_id;
                            $jDt->account_name = $coa->name;
                            $jDt->account_id = $coa->id;
                            $jDt->account_type_name = $coa->type;
                            $jDt->debit = $dataDB->beban_ongkir;
                            $jDt->credit = 0;
                            $jDt->notes = 'Good Receive : BEBAN ONGKIR '.$dataDB->code;
                            $jDt->reference_code = $dataDB->code;
                            $jDt->transaction_code = $jurnal->transaction_code;
                            $jDt->transaction_type = 'GOOD_RECEIVE';
                            $jDt->original_amount = $dataDB->beban_ongkir;
                            $jDt->save();
                        }
                    }
                }
                // Journal Detail Process beban ongkir END

                // Journal Detail Process hutang START
                $datas[] = [
                    'totalHutangBerelasi' => $totalHutangBerelasi,
                    'totalHutangUsaha' => $totalHutangUsaha,
                    'noGR' => $noGR,
                ];
                if ($totalHutangBerelasi != 0) {
                    $journalMapHutangBerelasi = JournalMap::where('branch_id', $jurnal->branch_id)
                        ->where('transaction_type', 'GOOD_RECEIVE')
                        ->where('coa_name', 'Hutang - Pihak Berelasi (Pendek)')
                        ->first();
                    if (! empty($journalMapHutangBerelasi)) {
                        $coa = Coa::where('id', $journalMapHutangBerelasi->coa_id)->first();

                        $jDt = new JournalDetail;
                        $jDt->branch_id = $jurnal->branch_id;
                        $jDt->header_id = $jurnal->id;
                        $jDt->account_code = $coa->code;
                        $jDt->account_type_id = $coa->type_id;
                        $jDt->account_name = $coa->name;
                        $jDt->account_id = $coa->id;
                        $jDt->account_type_name = $coa->type;
                        $jDt->debit = 0;
                        $jDt->credit = $totalHutangBerelasi;
                        $jDt->notes = 'Good Receive : Hutang - Pihak Berelasi '.$dataDB->code;
                        $jDt->reference_code = $dataDB->code;
                        $jDt->transaction_code = $jurnal->transaction_code;
                        $jDt->transaction_type = 'GOOD_RECEIVE';
                        $jDt->original_amount = $totalHutangBerelasi;
                        $jDt->save();
                    }
                }
                if ($totalHutangUsaha != 0) {
                    $journalMapHutangUsaha = JournalMap::where('branch_id', $jurnal->branch_id)
                        ->where('transaction_type', 'GOOD_RECEIVE')
                        ->where('coa_name', 'Hutang Usaha')
                        ->first();
                    if (! empty($journalMapHutangUsaha)) {
                        $coa = Coa::where('id', $journalMapHutangUsaha->coa_id)->first();

                        $jDt = new JournalDetail;
                        $jDt->branch_id = $jurnal->branch_id;
                        $jDt->header_id = $jurnal->id;
                        $jDt->account_code = $coa->code;
                        $jDt->account_type_id = $coa->type_id;
                        $jDt->account_name = $coa->name;
                        $jDt->account_id = $coa->id;
                        $jDt->account_type_name = $coa->type;
                        $jDt->debit = 0;
                        $jDt->credit = $totalHutangUsaha;
                        $jDt->notes = 'Good Receive : Hutang Usaha '.$dataDB->code;
                        $jDt->reference_code = $dataDB->code;
                        $jDt->transaction_code = $jurnal->transaction_code;
                        $jDt->transaction_type = 'GOOD_RECEIVE';
                        $jDt->original_amount = $totalHutangUsaha;
                        $jDt->save();
                    }
                }
                // Journal Detail Process hutang END
            }

            return 'SUCCESS';
        } catch (\Exception $ex) {
            dd($ex);
        }
    }

    public function fixSingleJurnalGoodReceive($id)
    {
        try {
            $admin = Auth::guard('admin')->user();
            $dataDB = GoodReceiveHeader::find($id);

            if (empty($dataDB)) {
                return 'GR NOT FOUND!';
            }

            $jurnal = JournalHeader::where('branch_id', '=', $dataDB->branch_id)
                ->where('notes', 'LIKE', '%'.$dataDB->code)
                ->first();

            if (empty($jurnal)) {
                return 'JHEADER NOT FOUND!';
            }

            if (count($jurnal->journal_details) > 0) {
                foreach ($jurnal->journal_details as $dt) {
                    $dt->delete();
                }
            }

            // $dataDB = GoodReceiveHeader::where('code', $noGR)->first();
            // $grDetails = GoodReceiveDetail::where('header_code', $noGR)->get();

            $totalHutangBerelasi = 0;
            $totalHutangUsaha = 0;

            foreach ($dataDB->good_receive_details as $detail) {

                // Journal Detail Process Item START
                $itemsDB = Item::where('id', $detail->item_id)->first();
                if (! empty($itemsDB->category_2_id)) {
                    $journalMap = JournalMap::where('branch_id', $jurnal->branch_id)
                        ->where('transaction_type', 'GOOD_RECEIVE')
                        ->where('journal_type', 'DEBET')
                        // ->where('coa_name', '!=', 'BEBAN ONGKIR')
                        ->where('category_2_id', $itemsDB->category_2_id)
                        ->first();

                    if (! empty($journalMap)) {
                        $coa = Coa::where('id', $journalMap->coa_id)->first();

                        $jDt = new JournalDetail;
                        $jDt->branch_id = $jurnal->branch_id;
                        $jDt->header_id = $jurnal->id;
                        $jDt->account_code = $coa->code;
                        $jDt->account_type_id = $coa->type_id;
                        $jDt->account_name = $coa->name;
                        $jDt->account_id = $coa->id;
                        $jDt->account_type_name = $coa->type;
                        $jDt->debit = $detail->total;
                        $jDt->credit = 0;
                        $jDt->notes = 'Good Receive '.$dataDB->code.' item '.$itemsDB->name;
                        $jDt->reference_code = $dataDB->code;
                        $jDt->transaction_code = $jurnal->transaction_code;
                        $jDt->transaction_type = 'GOOD_RECEIVE';
                        $jDt->original_amount = $detail->total;
                        $jDt->save();

                        if ($itemsDB->category_2_id == 14) {
                            $totalHutangBerelasi += $detail->total;
                        } else {
                            $totalHutangUsaha += $detail->total;
                        }
                    }
                }
                // Journal Detail Process Item END
            }

            // Journal Detail Process beban ongkir START
            if (! empty($dataDB->beban_ongkir)) {
                if ($dataDB->beban_ongkir != 0) {
                    $journalMapBebanOngkir = JournalMap::where('branch_id', $jurnal->branch_id)
                        ->where('transaction_type', 'GOOD_RECEIVE')
                        ->where('coa_name', 'BEBAN ONGKIR')
                        ->first();
                    if (! empty($journalMapBebanOngkir)) {
                        $coa = Coa::where('id', $journalMapBebanOngkir->coa_id)->first();

                        $jDt = new JournalDetail;
                        $jDt->branch_id = $jurnal->branch_id;
                        $jDt->header_id = $jurnal->id;
                        $jDt->account_code = $coa->code;
                        $jDt->account_type_id = $coa->type_id;
                        $jDt->account_name = $coa->name;
                        $jDt->account_id = $coa->id;
                        $jDt->account_type_name = $coa->type;
                        $jDt->debit = $dataDB->beban_ongkir;
                        $jDt->credit = 0;
                        $jDt->notes = 'Good Receive : BEBAN ONGKIR '.$dataDB->code;
                        $jDt->reference_code = $dataDB->code;
                        $jDt->transaction_code = $jurnal->transaction_code;
                        $jDt->transaction_type = 'GOOD_RECEIVE';
                        $jDt->original_amount = $dataDB->beban_ongkir;
                        $jDt->save();
                    }
                }
            }
            // Journal Detail Process beban ongkir END

            // Journal Detail Process hutang START
            // $datas[] = [
            //     'totalHutangBerelasi' => $totalHutangBerelasi,
            //     'totalHutangUsaha' => $totalHutangUsaha,
            //     'noGR' => $dataDB->code,
            // ];

            if ($totalHutangBerelasi != 0) {
                $journalMapHutangBerelasi = JournalMap::where('branch_id', $jurnal->branch_id)
                    ->where('transaction_type', 'GOOD_RECEIVE')
                    ->where('coa_name', 'Hutang - Pihak Berelasi (Pendek)')
                    ->first();
                if (! empty($journalMapHutangBerelasi)) {
                    $coa = Coa::where('id', $journalMapHutangBerelasi->coa_id)->first();

                    $jDt = new JournalDetail;
                    $jDt->branch_id = $jurnal->branch_id;
                    $jDt->header_id = $jurnal->id;
                    $jDt->account_code = $coa->code;
                    $jDt->account_type_id = $coa->type_id;
                    $jDt->account_name = $coa->name;
                    $jDt->account_id = $coa->id;
                    $jDt->account_type_name = $coa->type;
                    $jDt->debit = 0;
                    $jDt->credit = $totalHutangBerelasi;
                    $jDt->notes = 'Good Receive : Hutang - Pihak Berelasi '.$dataDB->code;
                    $jDt->reference_code = $dataDB->code;
                    $jDt->transaction_code = $jurnal->transaction_code;
                    $jDt->transaction_type = 'GOOD_RECEIVE';
                    $jDt->original_amount = $totalHutangBerelasi;
                    $jDt->save();
                }
            }
            if ($totalHutangUsaha != 0) {
                $journalMapHutangUsaha = JournalMap::where('branch_id', $jurnal->branch_id)
                    ->where('transaction_type', 'GOOD_RECEIVE')
                    ->where('coa_name', 'Hutang Usaha')
                    ->first();
                if (! empty($journalMapHutangUsaha)) {
                    $coa = Coa::where('id', $journalMapHutangUsaha->coa_id)->first();

                    $jDt = new JournalDetail;
                    $jDt->branch_id = $jurnal->branch_id;
                    $jDt->header_id = $jurnal->id;
                    $jDt->account_code = $coa->code;
                    $jDt->account_type_id = $coa->type_id;
                    $jDt->account_name = $coa->name;
                    $jDt->account_id = $coa->id;
                    $jDt->account_type_name = $coa->type;
                    $jDt->debit = 0;
                    $jDt->credit = $totalHutangUsaha;
                    $jDt->notes = 'Good Receive : Hutang Usaha '.$dataDB->code;
                    $jDt->reference_code = $dataDB->code;
                    $jDt->transaction_code = $jurnal->transaction_code;
                    $jDt->transaction_type = 'GOOD_RECEIVE';
                    $jDt->original_amount = $totalHutangUsaha;
                    $jDt->save();
                }
            }
            // Journal Detail Process hutang END

            return 'SUCCESS';
        } catch (\Exception $ex) {
            dd($ex);
        }
    }

    public function fixJurnalAccPayment(Request $request)
    {
        try {
            $jurnals = JournalHeader::where('transaction_type', 'ACC_PAYMENT')
                ->where('transaction_code', $request->query('code'))
                ->get();

            // if(!empty($request->query('code'))){
            //     $jurnals = JournalHeader::where('transaction_type', 'ACC_PAYMENT')
            //         ->where('transaction_code', $request->query('code'))
            //         ->get();
            // }
            // else{
            //     $jurnals = JournalHeader::where('transaction_type', 'ACC_PAYMENT')
            //         ->whereBetween('created_at', array('2024-04-16 00:00:00', '2024-04-29 23:59:00'))
            //         ->get();
            // }

            foreach ($jurnals as $jurnal) {
                if (count($jurnal->journal_details) > 0) {
                    foreach ($jurnal->journal_details as $dt) {
                        $dt->delete();
                    }
                }

                $refCode = str_replace('Jurnal Acc Payment ', '', $jurnal->notes);
                $dataDB = ApHeader::where('code', $refCode)->first();

                foreach ($dataDB->ap_detail1s as $dataDetail1) {
                    $suppInvCode = $dataDetail1->supplier_invoice_code;
                    $suppInvDetails = SupplierInvoiceDetail1::where('header_code', $suppInvCode)->get();

                    $totalHutangBerelasi = 0;
                    $totalHutangUsaha = 0;
                    foreach ($suppInvDetails as $suppInvDetail) {
                        $grHeader = GoodReceiveHeader::with(['good_receive_details'])
                            ->where('code', '=', $suppInvDetail->gr_code)
                            ->first();

                        // Check for PO discount and PPN
                        $poHeader = PurchaseOrderHeader::find($grHeader->po_id);
                        $poDiscount = $poHeader->disc1_value + $poHeader->disc2_value;
                        $ppnValue = $poHeader->ppn_value;

                        foreach ($grHeader->good_receive_details as $detail) {
                            $itemsDB = Item::where('id', $detail->item_id)->first();
                            if (! empty($itemsDB->category_2_id)) {
                                if ($itemsDB->category_2_id == 14) {
                                    $totalHutangBerelasi += $detail->total;
                                } else {
                                    $totalHutangUsaha += $detail->total;
                                }
                            }
                        }
                    }

                    if ($poDiscount > 0) {
                        if ($totalHutangUsaha > 0) {
                            if ($totalHutangUsaha - $poDiscount <= 0) {
                                $totalHutangBerelasi -= $poDiscount;
                            } else {
                                $totalHutangUsaha -= $poDiscount;
                            }
                        } else {
                            $totalHutangBerelasi -= $poDiscount;
                        }
                    }

                    if ($ppnValue > 0) {
                        if ($totalHutangUsaha > 0) {
                            $totalHutangUsaha += $ppnValue;
                            $totalHutangUsaha = round($totalHutangUsaha);
                        } else {
                            $totalHutangBerelasi += $ppnValue;
                            $totalHutangBerelasi = round($totalHutangBerelasi);
                        }
                    }

                    // Journal Detail Process hutang START
                    if ($totalHutangBerelasi != 0) {
                        $coa = Coa::where('branch_id', $dataDB->branch_id)
                            ->where('name', 'Hutang - Pihak Berelasi (Pendek)')
                            ->first();
                        if (! empty($coa)) {

                            $jDt = new JournalDetail;
                            $jDt->branch_id = $dataDB->branch_id;
                            $jDt->header_id = $jurnal->id;
                            $jDt->account_code = $coa->code;
                            $jDt->account_type_id = $coa->type_id;
                            $jDt->account_name = $coa->name;
                            $jDt->account_id = $coa->id;
                            $jDt->account_type_name = $coa->type;
                            $jDt->debit = $totalHutangBerelasi;
                            $jDt->credit = 0;
                            $jDt->notes = 'Acc Payment : Hutang - Pihak Berelasi '.$dataDB->code;
                            $jDt->reference_code = $dataDB->code;
                            $jDt->transaction_code = $jurnal->transaction_code;
                            $jDt->transaction_type = 'ACC_PAYMENT';
                            $jDt->original_amount = $totalHutangBerelasi;
                            $jDt->save();
                        }
                    }

                    if ($totalHutangUsaha != 0) {
                        $coa = Coa::where('branch_id', $dataDB->branch_id)
                            ->where('name', 'Hutang Usaha')
                            ->first();
                        if (! empty($coa)) {

                            $jDt = new JournalDetail;
                            $jDt->branch_id = $dataDB->branch_id;
                            $jDt->header_id = $jurnal->id;
                            $jDt->account_code = $coa->code;
                            $jDt->account_type_id = $coa->type_id;
                            $jDt->account_name = $coa->name;
                            $jDt->account_id = $coa->id;
                            $jDt->account_type_name = $coa->type;
                            $jDt->debit = $totalHutangUsaha;
                            $jDt->credit = 0;
                            $jDt->notes = 'Acc Payment : Hutang Usaha '.$dataDB->code;
                            $jDt->reference_code = $dataDB->code;
                            $jDt->transaction_code = $jurnal->transaction_code;
                            $jDt->transaction_type = 'ACC_PAYMENT';
                            $jDt->original_amount = $totalHutangUsaha;
                            $jDt->save();
                        }
                    }
                    // Journal Detail Process hutang END
                }

                foreach ($dataDB->ap_detail2s as $dataDetail2) {
                    $coa = Coa::where('id', $dataDetail2->coa_id)
                        ->first();
                    if (! empty($coa)) {

                        $jDt = new JournalDetail;
                        $jDt->branch_id = $dataDB->branch_id;
                        $jDt->header_id = $jurnal->id;
                        $jDt->account_code = $coa->code;
                        $jDt->account_type_id = $coa->type_id;
                        $jDt->account_name = $coa->name;
                        $jDt->account_id = $coa->id;
                        $jDt->account_type_name = $coa->type;
                        $jDt->debit = 0;
                        $jDt->credit = $dataDetail2->kredit;
                        $jDt->notes = 'Acc Payment '.$dataDB->code;
                        $jDt->reference_code = $dataDB->code;
                        $jDt->transaction_code = $jurnal->transaction_code;
                        $jDt->transaction_type = 'ACC_PAYMENT';
                        $jDt->original_amount = $dataDetail2->kredit;
                        $jDt->save();
                    }
                }

                // Journal Detail Process hutang END
            }

            return 'SUCCESS';
        } catch (\Exception $ex) {
            dd($ex);
        }
    }

    public function fixJurnalInventoryOut($branchId)
    {
        try {
            $jurnals = JournalHeader::where('transaction_type', 'INVENTORY_OUT')
                ->where('branch_id', $branchId)
                ->whereBetween('created_at', ['2023-09-01 00:00:00', '2024-01-10 01:00:00'])
                ->get();
            foreach ($jurnals as $jurnal) {
                if (count($jurnal->journal_details) > 0) {
                    foreach ($jurnal->journal_details as $dt) {
                        $dt->delete();
                    }
                }

                $noCode = str_replace('Jurnal Inventory Out ', '', $jurnal->notes);
                $dataDB = InventoryOutHeader::where('code', $noCode)->first();

                foreach ($dataDB->inventory_out_details as $detail) {

                    // Journal Detail Process Item START
                    $itemsDB = Item::where('id', $detail->item_id)->first();
                    //                    dd($itemsDB->category_2_id);

                    if (! empty($itemsDB->category_2_id)) {
                        $journalMaps = JournalMap::where('branch_id', $dataDB->branch_id)
                            ->where('transaction_type', 'INVENTORY_OUT')
                            ->where('room_type_id', 'like', '%'.$itemsDB->category_2_id.'%')
                            ->get();
                        //                            dd($journalMap);
                        if (count($journalMaps) > 0) {
                            foreach ($journalMaps as $journalMap) {
                                $coa = Coa::where('id', $journalMap->coa_id)->first();

                                $jDt = new JournalDetail;
                                $jDt->branch_id = $dataDB->branch_id;
                                $jDt->header_id = $jurnal->id;
                                $jDt->account_code = $coa->code;
                                $jDt->account_type_id = $coa->type_id;
                                $jDt->account_name = $coa->name;
                                $jDt->account_id = $coa->id;
                                $jDt->account_type_name = $coa->type;
                                $jDt->notes = 'Inventory Out '.$dataDB->code.' item ('.$itemsDB->name.')'.$itemsDB->name;
                                $jDt->reference_code = $dataDB->code;
                                //                            $jDt->transaction_code = AutoNumberGenerator::generateAutonumberTrx('JN');
                                $jDt->transaction_code = $jurnal->transaction_code;
                                $jDt->transaction_type = 'INVENTORY_OUT';

                                $totalPrice = $itemsDB->cost_price * $detail->qty;
                                if ($journalMap->journal_type == 'DEBET') {
                                    $jDt->debit = $totalPrice;
                                    $jDt->credit = 0;
                                    $jDt->original_amount = $totalPrice;
                                } else {
                                    $jDt->debit = 0;
                                    $jDt->credit = $totalPrice;
                                    $jDt->original_amount = $totalPrice;
                                }
                                $jDt->save();
                            }
                        }
                    }
                    // Journal Detail Process Item END
                }

            }

            return 'SUCCESS';
        } catch (\Exception $ex) {
            dd($ex);
        }
    }

    public function checkData()
    {
        try {
            //            $trxCode = 'POS/2023/05/18769';
            //            $header = TransactionHeader::where('code', $trxCode)->first();
            //            $payments = TransactionPayment::where('header_id', $header->id)->get();
            //            $result = [];
            //            $paymentId = [];
            //            $coas = [];
            //            $journal = [];
            //            foreach ($payments as $dbPayment){
            //                $journalMap = JournalMap::where('branch_id', 1)
            //                    ->where(function($q){
            //                        $q->where('transaction_type', 'POS_CASH')
            //                            ->orWhere('transaction_type', 'POS_NON_CASH');
            //                    })
            //                    ->where('room_type_id', 'like', '%'.$dbPayment->payment_type_id.'%')
            //                    ->first();
            //                $result[] = $journalMap;
            //                $paymentId[] = $dbPayment->payment_type_id;
            //                if($journalMap != null){
            //                    //insert journal
            //                    $coa = Coa::where('id', $journalMap->coa_id)->first();
            //                    $coas[] = $coa;
            //                    if($coa != null){
            //                        //insert journal detail
            //                        $type = 'KREDIT';
            //                        if($journalMap->journal_type == 'DEBET'){
            //                            $type = 'DEBET';
            //                        }
            //                        $notes = 'Payment Transaksi ' . $header->code;
            //                    }
            //                }
            //            }
            //
            //            dd($result, $paymentId, $coas);
            // $headers = TransactionHeader::with('transaction_details')
            //     ->where('branch_id', 1)
            //     ->whereBetween('transaction_headers.created_at', ['2023-07-30 00:00:00', '2023-07-31 00:00:00'])
            //     ->get();

            // $result = [];
            // $kembalian = [];
            // foreach($headers as $header){
            //     $tmpDate = Carbon::parse($header->created_at);
            //     $payments = TransactionPayment::where('header_id', $header->id)->get();
            //     if($header->return_value > 0){
            //         $kembalian[] = 'HeaderId:' . $header->id . ' - value:' . $header->return_value;
            //     }
            //     foreach($payments as $payment){
            //         $tmpPaymentDate = Carbon::parse($payment->created_at);
            //         // if($tmpPaymentDate->day != $tmpDate->day){
            //         //     $result[] = 'HeaderId:' . $header->id . ' - PaymentId:' . $payment->id;
            //         // }
            //         if($header->grand_total != $payment->amount){
            //             $result[] = 'HeaderId('. $header->grand_total .'):' . $header->id . ' - PaymentId('. $payment->amount.'):' . $payment->id;
            //         }
            //     }
            // }
            // dd($kembalian, $result);

            //             $trx = [];
            //             $result = [];
            //             $commission = [];
            //             $notCounted = [];
            //             foreach ($headers as $header){
            //                 $hasPackage = $header->transaction_details->where('package_id', '!=', 'null')->first();
            //                 $hasHotstone = $header->transaction_details->where('item_id', '=', '135')->first();

            //                 if($hasPackage != null && $hasHotstone != null){
            //                     foreach ($header->transaction_details as $detail){
            //                         if($detail->package_id != null){
            //                             //has package
            //                             $tdps = TransactionDetailPartner::with('partner')->where('transaction_detail_id', $detail->id)->get();
            //                             foreach ($tdps as $tdp){
            //                                 $result[] = $tdp->partner->id . '-' . $tdp->partner->number . '-' . $tdp->partner->alias . '-' . $header->code;
            //                             }
            //                         }
            //                     }
            //                     $trx[] = $header->id;
            //                 }
            // //                if($hasHotstone != null){
            // //                    $trx[] = $header->code;
            // //                }
            //             }

            //             foreach ($trx as $t){
            //                 $tmpCommission = CommissionPartnerHistory::where('transaction_id', $t)->first();
            //                 if($tmpCommission != null){
            //                     $commission[] = $tmpCommission->id . '-HeaderId:' . $t;
            //                 }
            //                 else{
            //                     $notCounted[] = $t;
            //                 }
            //             }

            //             dd($result, $trx, $commission, $notCounted);
        } catch (\Exception $ex) {
            dd($ex);
        }
    }

    public function checkDataAll()
    {
        try {
            $headers = TransactionHeader::where('branch_id', 1)
                ->whereBetween('created_at', ['2023-05-01 00:00:00', '2023-05-31 23:59:59'])
                ->get();
            $result = [];
            $journals = [];
            foreach ($headers as $header) {
                $payments = TransactionPayment::where('header_id', $header->id)->get();
                foreach ($payments as $dbPayment) {
                    $journalMap = JournalMap::where('branch_id', 1)
                        ->where(function ($q) {
                            $q->where('transaction_type', 'POS_CASH')
                                ->orWhere('transaction_type', 'POS_NON_CASH');
                        })
                        ->where('room_type_id', 'like', '%'.$dbPayment->payment_type_id.'%')
                        ->first();

                    if ($journalMap != null) {
                        // insert journal
                        $coa = Coa::where('id', $journalMap->coa_id)->first();
                        $notes = 'Payment Transaksi '.$header->code;
                        $journalHeader = JournalHeader::where('notes', 'like', '%'.$header->code.'%')->first();
                        if ($journalHeader != null) {
                            $journals[] = $journalHeader;
                            $journalDetail = JournalDetail::where('header_id', $journalHeader->id)->where('notes', $notes)->first();
                            if ($journalDetail != null) {
                                if ($journalDetail->account_id != $coa->id) {
                                    $nHeader = [
                                        'id' => $header->id,
                                        'code' => $header->code,
                                    ];
                                    $result[] = $nHeader;
                                }
                            }
                        }
                    }
                }
            }

            dd($result, $journals);
        } catch (\Exception $ex) {
            dd($ex);
        }
    }

    public function fixJournal()
    {
        try {
            $affectedHd = [];
            $invoices = TransactionInvoice::where('status_id', 16)->get();
            foreach ($invoices as $invoice) {
                $journalHeader = JournalHeader::where('reference_id', '=', $invoice->id)
                    ->first();

                if (empty($journalHeader)) {
                    $journalHeader = JournalHeader::where('notes', 'like', '%'.$invoice->code.'%')
                        ->first();
                }
                if (! empty($journalHeader)) {

                    // Delete its details
                    DB::table('journal_details')
                        ->where('header_id', '=', $journalHeader->id)
                        ->delete();

                    $journalHeader->delete();

                    $affectedHd[] = $invoice->code;
                }
            }

            // $headers = TransactionHeader::where('branch_id', 1)
            //     ->whereBetween('created_at', ['2023-05-01 00:00:00', '2023-05-31 23:59:59'])
            //     ->get();
            // $affectedHd = [];
            // foreach ($headers as $header){
            //     $jHeaders = JournalHeader::where('notes', 'like', '%' . $header->code . '%')->get();
            //     if(count($jHeaders) > 1){
            //         //delete the duplicate
            //         $jDetails = JournalDetail::where('header_id', $jHeaders[0]->id)->get();
            //         foreach ($jDetails as $dt){
            //             $dt->delete();
            //         }
            //         $jHeaders[0]->delete();
            //         $nHeader = [
            //             'id'    => $header->id,
            //             'code'  => $header->code,
            //         ];
            //         $affectedHd[] = $nHeader;
            //     }
            // }

            dd($affectedHd);
        } catch (\Exception $ex) {
            dd($ex);
        }
    }

    public function fixJournalPayment()
    {
        try {
            $headers = TransactionHeader::where('branch_id', 1)
                ->whereBetween('created_at', ['2023-05-01 00:00:00', '2023-05-31 23:59:59'])
                ->get();
            $result = [];
            foreach ($headers as $header) {
                $payments = TransactionPayment::where('header_id', $header->id)->get();
                foreach ($payments as $dbPayment) {
                    $journalMap = JournalMap::where('branch_id', 1)
                        ->where(function ($q) {
                            $q->where('transaction_type', 'POS_CASH')
                                ->orWhere('transaction_type', 'POS_NON_CASH');
                        })
                        ->where('room_type_id', 'like', '%'.$dbPayment->payment_type_id.'%')
                        ->first();

                    if ($journalMap != null) {
                        // insert journal
                        $coa = Coa::where('id', $journalMap->coa_id)->first();
                        $notes = 'Payment Transaksi '.$header->code;
                        $journalHeader = JournalHeader::where('notes', 'like', '%'.$header->code.'%')->first();
                        if ($journalHeader != null) {
                            $journalDetail = JournalDetail::where('header_id', $journalHeader->id)->where('account_id', $coa->id)->first();
                            $journalDetail?->delete();
                            JournalUtilities::insertRecord($journalHeader, 1, $coa, 'DEBET', $dbPayment->amount, $notes, $header->code);
                            $result[] = $header->code;
                        }
                    }
                }
            }

            dd($result);
        } catch (\Exception $ex) {
            dd($ex);
        }
    }

    public function checkBalance()
    {
        try {
            //            for($i=2;$i<=6;$i++){
            //                $headers = JournalHeader::with('journal_details')->where('branch_id', $i)->where('transaction_type', 'POS')->get();
            //                foreach ($headers as $header){
            //                    foreach ($header->journal_details as $dt){
            //                        $dt->branch_id = $header->branch_id;
            //                        $dt->save();
            //                    }
            //                }
            //            }
            //            $headers = JournalHeader::where('branch_id', 6)->where('transaction_type', 'POS')->get();
            //            $result = [];
            //            foreach ($headers as $header){
            //                $query = "select hd.transaction_code, sum(dt.debit) as debit, sum(dt.credit) as credit, sum(dt.debit) - sum(dt.credit) as result from hokicuan_db.journal_headers hd
            //                inner join hokicuan_db.journal_details dt on dt.header_id = hd.id
            //                where hd.id = ". $header->id."
            //                group by hd.transaction_code;";
            //                $data = DB::select($query);
            //
            //                if(count($data) > 0){
            //                    if($data[0]->result != 0){
            //                        $nData = [
            //                            'id'    => $header->id,
            //                            'code'  => $header->transaction_code,
            //                            'diff'  => $data[0]->result
            //                        ];
            //
            //                        $result[] = $nData;
            //                    }
            //                }
            //            }
            //            $headers = TransactionHeader::where('branch_id', 1)
            //                ->where('foc_type', '=', 'no')
            //                ->whereBetween('created_at', ['2023-06-01 00:00:00', '2023-06-30 23:59:59'])
            //                ->get();
            //            //dd($headers);
            //            $result = [];
            //            foreach ($headers as $header){
            //                $nResult = [
            //                    'detail_total'      => 0,
            //                    'payment_total'     => TransactionPayment::where('header_id', $header->id)->sum('amount'),
            //                    'header_id'         => $header->id,
            //                    'foc_type'          => $header->foc_type,
            //                    'discount'          => $header->discount,
            //                    'selisih'           => 0,
            //                ];
            //                $query = "select sum((qty * price) + extension_amount + additional_person_amount + additional_partner_amount) as total from transaction_details where header_id = " . $header->id . ";";
            //                $dtTotal = DB::select($query);
            //                $nResult['detail_total'] = $dtTotal[0]->total - $header->discount;
            //                $nResult['selisih'] = floatval($nResult['detail_total']) - floatval($nResult['payment_total']);
            //                //dd($nResult);
            //                if(floatval($nResult['detail_total']) != floatval($nResult['payment_total']))
            //                {
            //                    $result[] = $nResult;
            //                }
            //            }

            //            $headers = TransactionHeader::where('branch_id', 1)
            //                ->whereBetween('created_at', ['2023-06-21 00:00:00', '2023-06-30 23:59:59'])
            //                ->get();
            //            //dd($headers);
            //            $result = [];
            //            foreach ($headers as $header){
            //                $journalQuery = "select id from journal_headers where notes like '%". $header->code . "%'";
            //                $journal = DB::select($journalQuery);
            //                if(count($journal) == 0){
            //                    $result[] = $header;
            //                }
            //            }
            //            dd($result);
            //            $headers = TransactionHeader::where('branch_id', 1)
            //                ->where('foc_type', '!=', 'no')
            //                ->whereBetween('created_at', ['2023-06-01 00:00:00', '2023-06-30 23:59:59'])
            //                ->get();
            //
            //            $result = [];
            //            $totalFoc = 0;
            //            foreach ($headers as $header){
            //                $nResult = [
            //                    'detail_total'      => 0,
            //                    'payment_total'     => TransactionPayment::where('header_id', $header->id)->sum('amount'),
            //                    'header_id'         => $header->id,
            //                    'foc_type'          => $header->foc_type,
            //                    'discount'          => $header->discount,
            //                    'selisih'           => 0,
            //                ];
            //                $query = "select sum((qty * price) + extension_amount + additional_person_amount + additional_partner_amount) as total from transaction_details where header_id = " . $header->id . ";";
            //                $dtTotal = DB::select($query);
            //                $nResult['detail_total'] = $dtTotal[0]->total;
            //                $nResult['selisih'] = floatval($nResult['detail_total']) - floatval($nResult['payment_total']);
            //                //dd($nResult);
            //                if(floatval($nResult['payment_total']) != 0)
            //                {
            //                    $result[] = $nResult;
            //                }
            //                $totalFoc += floatval($nResult['detail_total']);
            //            }
            //            $discountQuery = "select sum(hd.discount) as total from hokicuan_db.transaction_headers hd where
            //                hd.branch_id = 1
            //                and hd.created_at >= '2023-06-01 00:00:00'
            //                and hd.created_at <= '2023-07-01 00:00:00'";
            //            $discount = DB::select($discountQuery);
            //
            //            $focQuery = "select sum((dt.qty * dt.price) + dt.extension_amount + dt.additional_person_amount + dt.additional_partner_amount) as total from transaction_details dt
            //                inner join  transaction_headers hd on hd.id = dt.header_id where
            //                hd.foc_type != 'no'
            //                and hd.branch_id = 1
            //                and hd.created_at >= '2023-06-01 00:00:00'
            //                and hd.created_at <= '2023-07-01 00:00:00'";
            //            $foc = DB::select($focQuery);
            //            dd($totalFoc, $foc[0]->total, $discount[0]->total);
        } catch (\Exception $ex) {
            dd($ex);
        }
    }

    public function fixUnBalanceJournal()
    {
        try {
            $branchId = 6;
            $headers = JournalHeader::where('branch_id', $branchId)->where('transaction_type', 'POS')->get();
            $result = [];
            foreach ($headers as $journal) {
                $query = 'select hd.transaction_code, hd.notes, sum(dt.debit) as debit, sum(dt.credit) as credit, sum(dt.debit) - sum(dt.credit) as result from hokicuan_db.journal_headers hd
                inner join hokicuan_db.journal_details dt on dt.header_id = hd.id
                where hd.id = '.$journal->id.'
                group by hd.transaction_code, hd.notes;';
                $data = DB::select($query);

                if (count($data) > 0) {
                    if ($data[0]->result != 0) {
                        // unbalance then check
                        $journalDetails = JournalDetail::where('header_id', $journal->id)->get();
                        $tmpTrxCode = '';
                        foreach ($journalDetails as $dt) {
                            $tmpTrxCode = $dt->reference_code;
                            $dt->delete();
                        }
                        // dd($tmpTrxCode);
                        // create new journal DT
                        $header = TransactionHeader::where('code', $tmpTrxCode)->first();
                        $dbPayments = TransactionPayment::where('header_id', $header->id)->get();
                        foreach ($dbPayments as $dbPayment) {
                            $totalTrx = 0;
                            $allDetails = TransactionDetail::where('header_id', $header->id)->get();
                            foreach ($allDetails as $dt) {
                                if ($dt->package_id != null) {
                                    $totalTrx += ($dt->price * $dt->qty) + ($dt->extension_amount + $dt->additional_partner_amount + $dt->additional_person_amount);
                                } else {
                                    $totalTrx += ($dt->price * $dt->qty);
                                }
                            }

                            if (strtolower($header->foc_type) == 'foc' || strtolower($header->foc_type) == 'foc_item' || strtolower($header->foc_type) == 'foc_package') {
                                $journalMap = JournalMap::where('branch_id', $branchId)
                                    ->where('transaction_type', 'POS_FOC')
                                    ->first();
                                $totalTrx -= $header->discount;
                                if ($journalMap != null) {
                                    $coa = Coa::where('id', $journalMap->coa_id)->first();
                                    if ($coa != null) {
                                        // insert journal detail
                                        $notes = 'Transaksi FOC '.$header->code;
                                        JournalUtilities::insertRecord($journal, $branchId, $coa, 'DEBET', $totalTrx, $notes, $header->code);
                                    }
                                }
                            } elseif (strtolower($header->foc_type) == 'FOC' || strtolower($header->foc_type) == 'FOC_ITEM' || strtolower($header->foc_type) == 'FOC_PACKAGE') {
                                $journalMap = JournalMap::where('branch_id', $branchId)
                                    ->where('transaction_type', 'POS_FOC')
                                    ->first();
                                if ($journalMap != null) {
                                    $coa = Coa::where('id', $journalMap->coa_id)->first();
                                    if ($coa != null) {
                                        // insert journal detail
                                        $notes = 'Transaksi FOC '.$header->code;
                                        JournalUtilities::insertRecord($journal, $branchId, $coa, 'DEBET', $totalTrx, $notes, $header->code);
                                    }
                                }
                            } else {
                                $journalMap = JournalMap::where('branch_id', $branchId)
                                    ->where(function ($q) {
                                        $q->where('transaction_type', 'POS_CASH')
                                            ->orWhere('transaction_type', 'POS_NON_CASH');
                                    })
                                    ->where('room_type_id', 'like', '%'.$dbPayment->payment_type_id.'%')
                                    ->first();

                                if ($journalMap != null) {
                                    // insert journal
                                    $coa = Coa::where('id', $journalMap->coa_id)->first();
                                    if ($coa != null) {
                                        // insert journal detail
                                        $type = 'KREDIT';
                                        if ($journalMap->journal_type == 'DEBET') {
                                            $type = 'DEBET';
                                        }
                                        $notes = 'Automate -Payment Transaksi '.$header->code;
                                        JournalUtilities::insertRecord($journal, $branchId, $coa, $type, $dbPayment->amount, $notes, $header->code);
                                    }
                                }
                            }
                        }

                        // Package Journal
                        $tmpDtPackage = TransactionDetail::where('header_id', $header->id)->whereNotNull('package_id')->get();
                        foreach ($tmpDtPackage as $tmpPackage) {
                            $tmpPackageDetail = Package::where('id', $tmpPackage->package_id)->first();
                            if ($branchId == 1) {
                                $packageJournal = JournalMap::where('branch_id', $branchId)
                                    ->where('transaction_type', 'POS_PENDAPATAN')
                                    ->where('room_type_id', $tmpPackageDetail->room_type_id)
                                    ->first();
                            } elseif ($branchId == 6) {
                                $packageJournal = JournalMap::where('branch_id', $branchId)
                                    ->where('transaction_type', 'POS_PENDAPATAN')
                                    ->where('coa_name', 'Pendapatan')
                                    ->first();
                            } else {
                                $packageJournal = JournalMap::where('branch_id', $branchId)
                                    ->where('transaction_type', 'POS_PENDAPATAN')
                                    ->where('coa_name', 'Pendapatan Massage')
                                    ->first();
                            }

                            if ($packageJournal != null) {
                                $coa = Coa::where('id', $packageJournal->coa_id)->first();
                                if ($coa != null) {
                                    $value = ($tmpPackage->price * $tmpPackage->qty) + $tmpPackage->additional_partner_amount
                                        + $tmpPackage->additional_person_amount + $tmpPackage->extension_amount;
                                    $notes = 'Pendapatan Transaksi Paket '.$tmpPackageDetail->name.' : '.$value;
                                    JournalUtilities::insertRecord($journal, $branchId, $coa, 'KREDIT', $value, $notes, $header->code);
                                }
                            }
                        }

                        $facilityOnly = TransactionDetail::where('header_id', $header->id)->where('is_facility_only', 1)->first();
                        if ($facilityOnly != null) {
                            $facJournalMap = JournalMap::where('room_type_id', 99)->first();
                            if ($facJournalMap != null) {
                                $coa = Coa::where('id', $facJournalMap->coa_id)->first();
                                if ($coa != null) {
                                    $notes = 'Automate - Transaksi '.$coa->coa_name;
                                    JournalUtilities::insertRecord($journal, $branchId, $coa, $facJournalMap->journal_type, $facilityOnly->subtotal, $notes, $header->code);
                                }
                            }
                        }

                        // Insert Detail Item Journal
                        $dtItems = TransactionDetail::where('header_id', $header->id)->where('item_id', '!=', null)->get();
                        $hppJournalMap = JournalMap::where('branch_id', $branchId)
                            ->where(function ($q) {
                                $q->where('transaction_type', 'POS_HPP')
                                    ->orWhere('transaction_type', 'POS_PERSEDIAAN')
                                    ->orWhere('transaction_type', 'POS_PENJUALAN')
                                    ->orWhere('coa_name', 'Pendapatan Meal')
                                    ->orWhere('coa_name', 'Pendapatan Oil');
                            })
                            ->get();
                        if ($branchId == 6) {
                            $hppJournalMap = JournalMap::where('branch_id', $branchId)
                                ->where(function ($q) {
                                    $q->where('transaction_type', 'POS_HPP')
                                        ->orWhere('transaction_type', 'POS_PERSEDIAAN')
                                        ->orWhere('transaction_type', 'POS_PENJUALAN')
                                        ->orWhere('coa_name', 'Pendapatan');
                                })
                                ->get();
                        }
                        // dd($hppJournalMap);

                        $tmpJournalData = [];
                        foreach ($hppJournalMap as $map) {
                            $nMap = [
                                'id' => $map->id,
                                'coa_id' => $map->coa_id,
                                'coa_name' => $map->coa_name,
                                'coa_code' => $map->coa_code,
                                'journal_type' => $map->journal_type,
                                'amount' => 0,
                            ];

                            $tmpJournalData[] = $nMap;
                        }
                        // dd($tmpJournalData);

                        $foc = $header->foc_type;
                        foreach ($dtItems as $dt) {
                            // insert journal item
                            $item = Item::where('id', $dt->item_id)->first();
                            if ($item != null) {
                                // Journal Pendapatan
                                $hppName = 'HPP - Lain-lain';
                                $categoryName = 'Pendapatan - Lain-lain';
                                $persediaanName = 'Persediaan - Lain-lain';

                                if ($branchId == 1) {
                                    if (str_contains($item->name, 'KIMO')) {
                                        $categoryName = 'Pendapatan - Kimo';
                                        $hppName = 'HPP - Kimo';
                                        $persediaanName = 'Persediaan - Kimo';
                                    } elseif (str_contains($item->name, 'OIL')) {
                                        $categoryName = 'Pendapatan Oil';
                                    } elseif (str_contains($item->name, 'HOT STONE')) {
                                        $categoryName = 'Pendapatan Hot Stone';
                                    }

                                    if ($item->category_id == 2) {
                                        $categoryName = 'Pendapatan Meal';
                                        $hppName = 'HPP - F&B';
                                        $persediaanName = 'Persediaan - F&B';
                                    }
                                } elseif ($branchId == 6) {
                                    $hppName = 'Beban Pokok Penjualan';
                                    $categoryName = 'Pendapatan';
                                    $persediaanName = 'Persediaan';
                                } else {
                                    $hppName = 'HPP Lain-lain';
                                    $categoryName = 'Penjualan Lain-lain';
                                    $persediaanName = 'Persediaan Lain-lain';

                                    if (str_contains($item->name, 'KIMO')) {
                                        $categoryName = 'Penjualan Kimo';
                                        $hppName = 'HPP Kimo';
                                        $persediaanName = 'Persediaan Kimo';
                                    }

                                    if ($item->category_id == 2) {
                                        $categoryName = 'Penjualan F&B';
                                        $hppName = 'HPP F&B';
                                        $persediaanName = 'Persediaan F&B';
                                    }
                                }

                                $idx = 0;
                                foreach ($tmpJournalData as $map) {
                                    if ($map['coa_name'] == $categoryName) {
                                        $map['amount'] += ($dt->price * $dt->qty);
                                    } elseif ($map['coa_name'] == $hppName) {
                                        $map['amount'] += ($item->cost_price * $dt->qty);
                                    } elseif ($map['coa_name'] == $persediaanName) {
                                        $map['amount'] += ($item->cost_price * $dt->qty);
                                    }
                                    $tmpJournalData[$idx] = $map;
                                    $idx++;
                                }
                            }
                        }
                        // dd($tmpJournalData);

                        foreach ($tmpJournalData as $nJournal) {
                            if ($nJournal['amount'] > 0) {
                                $coa = Coa::where('id', $nJournal['coa_id'])->first();
                                if ($coa != null) {
                                    // insert journal
                                    $type = 'KREDIT';
                                    if ($nJournal['journal_type'] == 'DEBET') {
                                        $type = 'DEBET';
                                    }
                                    $notes = 'Automate - Transaksi '.$header->code;
                                    JournalUtilities::insertRecord($journal, $branchId, $coa, $type, $nJournal['amount'], $notes, $header->code);
                                }
                            }
                        }

                        if ($header->discount > 0) {
                            $discountMap = JournalMap::where('branch_id', $branchId)->where('transaction_type', 'POS_POTONGAN')->first();
                            if ($discountMap != null) {
                                $coa = Coa::where('id', $discountMap->coa_id)->first();
                                if ($coa != null) {
                                    $notes = 'Diskon Transaksi '.$header->discount;
                                    JournalUtilities::insertRecord($journal, $branchId, $coa, 'DEBET', $header->discount, $notes, $header->code);
                                }
                            }
                        }
                    }
                }
            }
            dd('sukses');
        } catch (\Exception $ex) {
            dd($ex);
        }
    }

    public function deleteDuplicate()
    {
        try {
            $headers = DB::select('SELECT transaction_code, COUNT(*) c FROM hokicuan_db.journal_headers GROUP BY transaction_code HAVING c > 1;');
            dd($headers);
            foreach ($headers as $header) {
                if ($header->c > 1) {
                    // delete others
                    $tmpHeader = JournalHeader::with('journal_details')->where('transaction_code', $header->transaction_code)->first();
                    foreach ($tmpHeader->journal_details as $dt) {
                        $dt->delete();
                    }
                    $tmpHeader->delete();
                }
            }

            return 'sukses';
        } catch (\Exception $ex) {
            dd($ex);
        }
    }

    public function JournalKonsolidasi(Request $request)
    {
        try {
            $branchId = intval($request->input('branch'));
            //            $reads = (new FastExcel)->import($request->file('excel'));

            $journalCodes = JournalDetail::distinct()
                ->where('transaction_type', 'GOOD_RECEIVE')
                ->orWhere('transaction_type', 'ACC_PAYMENT')
                ->orWhere('transaction_type', 'INVENTORY_OUT')
                ->get(['transaction_code', 'reference_code', 'transaction_type']);

            foreach ($journalCodes as $journalCode) {
                $date = Carbon::now('Asia/Jakarta');
                switch ($journalCode->transaction_type) {
                    case 'GOOD_RECEIVE':
                        $data = GoodReceiveHeader::where('code', $journalCode->reference_code)->first();
                        if (empty($data)) {
                            dd($data, $journalCode);
                        }
                        $date = $data->date;
                        break;
                    case 'ACC_PAYMENT':
                        $data = ApHeader::where('code', $journalCode->reference_code)->first();
                        if (empty($data)) {
                            dd($data, $journalCode);
                        }
                        $date = $data->date;
                        break;
                    case 'INVENTORY_OUT':
                        $data = InventoryOutHeader::where('code', $journalCode->reference_code)->first();
                        if (empty($data)) {
                            dd($data, $journalCode);
                        }
                        $date = $data->date;
                        break;
                }
                $reference = JournalHeader::where('transaction_code', $journalCode->transaction_code)->first();
                $reference->transaction_date = $date;
                $reference->save();
            }

            return 'SUCCESS';
        } catch (\Exception $ex) {
            dd($ex);
        }
    }

    public function fixGroCommission()
    {
        try {
            $header = TransactionHeader::where('id', 25398)->first();
            // count commission for GRO
            $employee = Employee::where('id', $header->employee_id)->first();
            $totalEmployeeCommission = 0;
            $trxItems = TransactionDetail::where('header_id', $header->id)->where('item_id', '!=', null)->get();
            if ($employee != null) {
                foreach ($trxItems as $trxItem) {
                    $employeeCommission = CommissionPositionItem::where('branch_id', $header->branch_id)
                        ->where('position_id', $employee->position_id)
                        ->where('item_id', $trxItem->item_id)
                        ->where('status_id', '=', 1)
                        ->first();
                    $item = Item::where('id', $trxItem->item_id)->first();

                    if ($employeeCommission != null && $item != null) {
                        if ($employeeCommission->commission_percentage > 0) {
                            $commissionAdd = (floatval($trxItem->price) * intval($trxItem->qty)) * floatval($employeeCommission->commission_percentage / 100);
                        } else {
                            $commissionAdd = floatval($employeeCommission->commission_flat) > 0 ? floatval($employeeCommission->commission_flat) * intval($trxItem->qty) : 0;
                        }

                        if ($commissionAdd > 0) {
                            $totalEmployeeCommission += $commissionAdd;

                            $eCHistory = new CommissionEmployeeHistory;
                            $eCHistory->branch_id = $header->branch_id;
                            $eCHistory->employee_id = $employee->id;
                            $eCHistory->employee_code = $employee->code;
                            $eCHistory->employee_name = $employee->name;
                            $eCHistory->employee_phone = $employee->phone;
                            $eCHistory->transaction_id = $header->id;
                            $eCHistory->transaction_code = $header->code;
                            $eCHistory->transaction_date = $header->created_at;
                            $eCHistory->commission_percentage = $employeeCommission->commission_percentage;
                            $eCHistory->item_id = $item->id;
                            $eCHistory->item_qty = $trxItem->qty;
                            $eCHistory->item_code = $item->code;
                            $eCHistory->item_name = $item->name;
                            // counting commission
                            $eCHistory->commission_add = $commissionAdd;
                            $eCHistory->commission_result = $commissionAdd;
                            $eCHistory->save();

                            $trxItem->subtotal_after_commission = $trxItem->subtotal - $commissionAdd;
                            $trxItem->save();
                        }

                    }
                }
            }

            return 'sukses';
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function JournalKmcMandiriKonsolidasi(Request $request)
    {
        try {
            $string = '';
            //            $reads = (new FastExcel)->import($request->file('excel'));
            $transactions = TransactionHeader::whereBetween('created_at', ['2023-05-05', '2023-05-22'])
                ->where('branch_id', 3)
                ->get();
            foreach ($transactions as $transaction) {
                foreach ($transaction->transaction_payments as $payment) {
                    if ($payment->payment_type_id == 27 or $payment->payment_type_id == 31) {
                        $jurnalDetail = JournalDetail::where('reference_code', $transaction->code)
                            ->where('account_type_name', 'Aset')
                            ->where('account_code', '!=', '111001')
                            ->where('debit', '>', 0)
                            ->first();

                        if (! empty($jurnalDetail)) {
                            // dd($transaction, $payment, $jurnalDetail);
                            $jurnalDetail->account_id = 2037;
                            $jurnalDetail->account_name = 'Mandiri-PT';
                            $jurnalDetail->account_code = '111001';

                            $jurnalDetail->save();

                            $string .= " \n $transaction->code";
                        }
                    }
                }
            }

            return $string;
        } catch (\Exception $ex) {
            dd($ex);
        }
    }


    public function testGenerateAttendanceInfo()
    {
        try {
            $attendanceSummaryQuery =
                DB::connection('hrd')
                    ->table('attendances')
                    ->select(DB::raw('SUM(is_late = 1) as total_late, '.
                        'COUNT(id) as total_attend, '.
                        'employee_id, employee_code, employee_name'))
                    ->where('branch_id', '=', 6)
                    ->whereNotNull('employee_id')
                    ->whereBetween('created_at', ['2023-03-01 00:00:00', '2023-04-01 00:00:00'])
                    ->groupBy(['employee_id', 'employee_code', 'employee_name'])
                    ->get();

            dd($attendanceSummaryQuery);
        } catch (\Exception $exception) {
            dd($exception);
        }
    }

    public function editGRStatus()
    {
        try {
            $items = SupplierInvoiceHeader::where('status_id', 13)->get();

            foreach ($items as $item) {
                foreach ($item->supplier_invoice_detail1s as $detail) {
                    $grData = GoodReceiveHeader::where('id', $detail->gr_id)->first();
                    if (! empty($grData)) {
                        $grData->is_close = 1;
                        $grData->save();
                    }
                }
            }

            return 'SUCCESS';
        } catch (\Exception $ex) {
            return dd($ex);
        }
    }

    public function checkCommission()
    {
        try {
            $commission = CommissionPartnerHistory::whereBetween('transaction_date', ['2023-06-01 00:00:00', '2023-06-30 23:59:00'])
                ->where('partner_id', 96)
                ->sum('commission_add');

            $transactions = TransactionDetailPartner::with('transaction_detail')
                ->where('partner_id', 96)
                ->whereHas('transaction_detail', function ($query) {
                    $query->whereBetween('created_at', ['2023-06-01 00:00:00', '2023-06-30 23:59:00']);
                })
                ->get();
            // dd($transactions);
            $notCommissioned = [];
            $totalPrice = 0;
            $totalSultan = 0;

            foreach ($transactions as $trx) {
                // check commission
                if ($trx->transaction_detail->package_id != 22 && $trx->transaction_detail->package_id != 23) {
                    $totalPrice += $trx->transaction_detail->price;
                } elseif ($trx->transaction_detail->package_id == 35) {
                    $totalSultan += 54000;
                }
                $header = TransactionHeader::where('id', $trx->transaction_detail->header_id)->first();
                if ($header != null) {
                    $tmpComm = CommissionPartnerHistory::where('partner_id', 92)
                        ->where('transaction_id', $header->id)
                        ->first();
                    if ($tmpComm == null) {
                        $notCommissioned[] = [
                            'id' => $header->id,
                            'code' => $header->code,
                        ];
                    }
                }
            }

            dd($commission, $notCommissioned, $totalPrice, ($totalPrice * 0.2), $totalSultan, ($totalPrice * 0.2) + $totalSultan);
        } catch (\Exception $ex) {
            return dd($ex);
        }
    }

    public function deleteTransaction($id)
    {
        try {
            $trxHeader = TransactionHeader::find($id);

            $trxDetails = TransactionDetail::where('header_id', '=', $id)->get();
            foreach ($trxDetails as $trxDetail) {
                if (! empty($trxDetail->package_id)) {
                    DB::table('transaction_detail_partners')
                        ->where('transaction_detail_id', '=', $trxDetail->id)
                        ->delete();
                }

                $trxDetail->delete();
            }

            DB::table('transaction_payments')
                ->where('header_id', '=', $id)
                ->delete();

            $trxHeader->delete();

            return 'SUCCESS';
        } catch (\Exception $ex) {
            return dd($ex);
        }
    }

    public function deleteBooking($id)
    {
        try {
            $bookingHeader = BookingHeader::find($id);

            $bookingDetails = BookingDetail::where('header_id', '=', $id)->get();
            foreach ($bookingDetails as $bookingDetail) {
                if (! empty($bookingDetail->package_id)) {
                    DB::table('booking_detail_partners')
                        ->where('detail_id', '=', $bookingDetail->id)
                        ->delete();
                }

                $bookingDetail->delete();
            }

            $bookingHeader->delete();

            return 'SUCCESS';
        } catch (\Exception $ex) {
            return dd($ex);
        }
    }

    public function check_TotalTransaction_Payment_Unbalance()
    {

        $branchId = 5;
        $headers = TransactionHeader::where('branch_id', $branchId)
            // ->where('created_at', '>=', '2023-07-01 00:00:00')
            // ->where('created_at', '<', '2023-07-16 00:00:00')
            ->where('created_at', '>=', '2023-08-01 00:00:00')
            ->where('created_at', '<', '2023-08-23 00:00:00')
            ->where('status_id', 9)
            ->get();

        $headerCode = [];
        $headerPayment = [];
        $headerGrandtotal = [];
        foreach ($headers as $header) {
            $allDetails = TransactionDetail::where('header_id', $header->id)->get();
            $totalDetail = 0;
            foreach ($allDetails as $dt) {
                $totalDetail += (($dt->price * $dt->qty) + $dt->additional_person_amount + $dt->additional_partner_amount + $dt->extension_amount);
            }
            $paymentAmount = TransactionPayment::where('header_id', $header->id)->get();

            if ($paymentAmount->sum('amount') != $header->grand_total) {
                if ($paymentAmount->sum('amount') != 0) {
                    $headerCode[] = $header->code;
                    $headerPayment[] = $paymentAmount->sum('amount');
                    $headerGrandtotal[] = $header->grand_total;
                }
            }
        }
        dd($headerCode, $headerPayment, $headerGrandtotal);
    }

    public function doubleBookOld()
    {
        try {
            $branchs = Branch::all();
            $yesterday = '2023-08-09';
            // $yesterdayFromNow = Carbon::now()->addDays(-1);
            // $yesterday = $yesterdayFromNow->locale('id_ID')->format('Y-m-d');
            // dd($yesterday);
            foreach ($branchs as $branch) {
                $payments = DB::table('transaction_payments')
                    ->where('branch_id', $branch->id)
                    ->whereBetween('created_at', [$yesterday.' 00:00:00', $yesterday.' 23:59:59']);
                $grandtotal = $payments->sum('amount');
                $totalTarget = $grandtotal * 0.05;

                $target = $totalTarget / 3;
                $currTarget = 0;
                $headerIdArr = [];
                // batch 1 (10.00 - 14.00)
                $payments1 = $payments
                    ->whereBetween('created_at', [$yesterday.' 10:00:00', $yesterday.' 14:00:00'])
                    ->get();
                $payments1 = $payments1->shuffle();
                foreach ($payments1 as $payment) {
                    if ($currTarget >= $totalTarget) {
                        break;
                    } else {
                        $currTarget += $payment->amount;
                        array_push($headerIdArr, $payment->header_id);
                    }
                }

                // batch 2 (14.00 - 18.00)
                $payments2 = $payments
                    ->whereBetween('created_at', [$yesterday.' 14:00:00', $yesterday.' 18:00:00'])
                    ->get();
                $payments2 = $payments2->shuffle();
                foreach ($payments2 as $payment) {
                    if ($currTarget >= $totalTarget) {
                        break;
                    } else {
                        $currTarget += $payment->amount;
                        array_push($headerIdArr, $payment->header_id);
                    }
                }

                // batch 3 (18.00 - end)
                $payments3 = $payments
                    ->whereBetween('created_at', [$yesterday.' 18:00:00', $yesterday.' 23:59:59'])
                    ->get();
                $payments3 = $payments3->shuffle();
                foreach ($payments3 as $payment) {
                    if ($currTarget >= $totalTarget) {
                        break;
                    } else {
                        $currTarget += $payment->amount;
                        array_push($headerIdArr, $payment->header_id);
                    }
                }

                // get selected transaction
                $transactionHeaders = TransactionHeader::with(['transaction_details', 'transaction_payments'])->whereIn('id', $headerIdArr)->orderby('id')->get();
                foreach ($transactionHeaders as $transactionHeader) {
                    $headerCode = $transactionHeader->code;
                    // copy transaction_headers
                    $newTaskHeader = (new TransactionHeader)
                        ->setConnection('mysql')
                        ->setTable('transaction_headers')
                        ->find($transactionHeader->id)
                        ->replicate()
                        ->setConnection('mysql_cloud')
                        ->setTable('transaction_headers')
                        ->save();
                    $nHeader = TransactionHeader::on('mysql_cloud')->where('code', $headerCode)->first();

                    // copy transaction_details
                    foreach ($transactionHeader->transaction_details as $transactionDetail) {
                        $nDetail = new TransactionDetail;
                        $nDetail->setConnection('mysql_cloud');
                        $nDetail->header_id = $nHeader->id;
                        $nDetail->package_id = $transactionDetail->package_id;
                        $nDetail->room_id = $transactionDetail->room_id;
                        $nDetail->status_id = $transactionDetail->status_id;
                        $nDetail->qty = $transactionDetail->qty;
                        $nDetail->price = $transactionDetail->price;
                        $nDetail->subtotal = $transactionDetail->subtotal;
                        $nDetail->start_time = $transactionDetail->start_time;
                        $nDetail->end_time = $transactionDetail->end_time;
                        $nDetail->item_id = $transactionDetail->item_id;
                        $nDetail->is_fnb = $transactionDetail->is_fnb;
                        $nDetail->is_extended = $transactionDetail->is_extended;
                        $nDetail->is_called = $transactionDetail->is_called;
                        $nDetail->extension_amount = $transactionDetail->extension_amount;
                        $nDetail->extension_times = $transactionDetail->extension_times;
                        $nDetail->additional_person_amount = $transactionDetail->additional_person_amount;
                        $nDetail->additional_person_qty = $transactionDetail->additional_person_qty;
                        $nDetail->additional_partner_amount = $transactionDetail->additional_partner_amount;
                        $nDetail->additional_partner_qty = $transactionDetail->additional_partner_qty;
                        $nDetail->discount_percentage = $transactionDetail->discount_percentage;
                        $nDetail->discount_amount = $transactionDetail->discount_amount;
                        $nDetail->membership_id = $transactionDetail->membership_id;
                        $nDetail->is_facility_only = $transactionDetail->is_facility_only;
                        $nDetail->is_cut = $transactionDetail->is_cut;
                        $nDetail->subtotal_after_commission = $transactionDetail->subtotal_after_commission;
                        $nDetail->is_new_order = $transactionDetail->is_new_order;
                        $nDetail->customer_name = $transactionDetail->customer_name;
                        $nDetail->save();

                        // $newTask = (new TransactionDetail)
                        //     ->setConnection('mysql')
                        //     ->setTable('transaction_details')
                        //     ->find($transactionDetail->id)
                        //     ->replicate()
                        //     ->setConnection('mysql_cloud')
                        //     ->setTable('transaction_details')
                        //     ->save();

                        // copy transaction_detail_partners
                        $detailPartners = DB::table('transaction_detail_partners')->where('transaction_detail_id', $transactionDetail->id)->get();
                        foreach ($detailPartners as $detailPartner) {
                            $nDetailPartner = new TransactionDetailPartner;
                            $nDetailPartner->setConnection('mysql_cloud');
                            $nDetailPartner->transaction_detail_id = $nDetail->id;
                            $nDetailPartner->partner_id = $detailPartner->partner_id;
                            $nDetailPartner->is_main = $detailPartner->is_main;
                            $nDetailPartner->is_extend = $detailPartner->is_extend;
                            $nDetailPartner->book_name = $detailPartner->book_name;
                            $nDetailPartner->is_checkout = $detailPartner->is_checkout;
                            $nDetailPartner->save();
                        }
                    }
                    // copy transaction_payments
                    foreach ($transactionHeader->transaction_payments as $transactionPayment) {
                        // $newTask = (new TransactionPayment)
                        //     ->setConnection('mysql')
                        //     ->setTable('transaction_payments')
                        //     ->find($transactionPayment->id)
                        //     ->replicate()
                        //     ->setConnection('mysql_cloud')
                        //     ->setTable('transaction_payments')
                        //     ->save();

                        $nPayment = new TransactionPayment;
                        $nPayment->setConnection('mysql_cloud');
                        $nPayment->header_id = $nHeader->id;
                        $nPayment->payment_type_id = $transactionPayment->payment_type_id;
                        $nPayment->payment_type_code = $transactionPayment->payment_type_code;
                        $nPayment->amount = $transactionPayment->amount;
                        $nPayment->branch_id = $transactionPayment->branch_id;
                        $nPayment->created_by = $transactionPayment->created_by;
                        $nPayment->save();
                    }
                    // copy journal_headers
                    $journalHeaders = DB::table('journal_headers')->where('notes', 'like', '%'.$transactionHeader->code.'%')->get();
                    foreach ($journalHeaders as $journalHeader) {
                        $newTask = (new JournalHeader)
                            ->setConnection('mysql')
                            ->setTable('journal_headers')
                            ->find($journalHeader->id)
                            ->replicate()
                            ->setConnection('mysql_cloud')
                            ->setTable('journal_headers')
                            ->save();
                        $nJournalHeader = JournalHeader::on('mysql_cloud')->where('notes', 'like', '%'.$transactionHeader->code.'%')->first();

                        // copy journal_details
                        $journalDetails = DB::table('journal_details')->where('reference_code', $transactionHeader->code)->get();
                        foreach ($journalDetails as $journalDetail) {
                            $nDetailJournal = new JournalDetail;
                            $nDetailJournal->setConnection('mysql_cloud');
                            $nDetailJournal->header_id = $nJournalHeader->id;
                            $nDetailJournal->branch_id = $journalDetail->branch_id;
                            $nDetailJournal->transaction_code = $journalDetail->transaction_code;
                            $nDetailJournal->reference_code = $journalDetail->reference_code;
                            $nDetailJournal->account_id = $journalDetail->account_id;
                            $nDetailJournal->account_code = $journalDetail->account_code;
                            $nDetailJournal->account_name = $journalDetail->account_name;
                            $nDetailJournal->account_type_id = $journalDetail->account_type_id;
                            $nDetailJournal->account_type_name = $journalDetail->account_type_name;
                            $nDetailJournal->debit = $journalDetail->debit;
                            $nDetailJournal->credit = $journalDetail->credit;
                            $nDetailJournal->rate = $journalDetail->rate;
                            $nDetailJournal->transaction_type = $journalDetail->transaction_type;
                            $nDetailJournal->original_amount = $journalDetail->original_amount;
                            $nDetailJournal->notes = $journalDetail->notes;
                            $nDetailJournal->save();
                        }
                    }
                    // $journalDetails = DB::table('journal_details')->where('reference_code', $transactionHeader->code)->get();
                    // foreach($journalDetails as $journalDetail){
                    //     $newTask = (new JournalDetail)
                    //         ->setConnection('mysql')
                    //         ->setTable('journal_details')
                    //         ->find($journalDetail->id)
                    //         ->replicate()
                    //         ->setConnection('mysql_cloud')
                    //         ->setTable('journal_details')
                    //         ->save();
                    // }

                }

            }

            return 'SUCCESS';
        } catch (\Exception $ex) {
            return dd($ex);
        }
    }

    public function doubleBookOld2()
    {
        try {

            $this->check_TotalTransaction_Payment_Unbalance();
            dd('asdf');
            $branchs = Branch::all();
            $yesterday = '2023-08-10';
            // $yesterdayFromNow = Carbon::now()->addDays(-1);
            // $yesterday = $yesterdayFromNow->locale('id_ID')->format('Y-m-d');
            // dd($yesterday);
            foreach ($branchs as $branch) {
                $payments = DB::table('transaction_payments')
                    ->where('branch_id', $branch->id)
                    ->whereBetween('created_at', [$yesterday.' 00:00:00', $yesterday.' 23:59:59']);
                $grandtotal = $payments->sum('amount');
                $totalTarget = $grandtotal * 0.05;

                $target = $totalTarget / 3;
                $currTarget = 0;
                $headerIdArr = [];
                // batch 1 (10.00 - 14.00)
                $payments1 = $payments
                    ->whereBetween('created_at', [$yesterday.' 10:00:00', $yesterday.' 14:00:00'])
                    ->get();
                $payments1 = $payments1->shuffle();
                foreach ($payments1 as $payment) {
                    if ($currTarget >= $totalTarget) {
                        break;
                    } else {
                        $currTarget += $payment->amount;
                        array_push($headerIdArr, $payment->header_id);
                    }
                }

                // batch 2 (14.00 - 18.00)
                $payments2 = $payments
                    ->whereBetween('created_at', [$yesterday.' 14:00:00', $yesterday.' 18:00:00'])
                    ->get();
                $payments2 = $payments2->shuffle();
                foreach ($payments2 as $payment) {
                    if ($currTarget >= $totalTarget) {
                        break;
                    } else {
                        $currTarget += $payment->amount;
                        array_push($headerIdArr, $payment->header_id);
                    }
                }

                // batch 3 (18.00 - end)
                $payments3 = $payments
                    ->whereBetween('created_at', [$yesterday.' 18:00:00', $yesterday.' 23:59:59'])
                    ->get();
                $payments3 = $payments3->shuffle();
                foreach ($payments3 as $payment) {
                    if ($currTarget >= $totalTarget) {
                        break;
                    } else {
                        $currTarget += $payment->amount;
                        array_push($headerIdArr, $payment->header_id);
                    }
                }

                // get selected transaction
                $transactionHeaders = TransactionHeader::with(['transaction_details', 'transaction_payments'])->whereIn('id', $headerIdArr)->orderby('id')->get();
                foreach ($transactionHeaders as $transactionHeader) {
                    // copy transaction_headers
                    try {
                        $nHeader = new TransactionHeader;
                        $nHeader = $transactionHeader->replicate();
                        $nHeader->setConnection('mysql_cloud');
                        $nHeader->created_at = $transactionHeader->created_at;
                        $nHeader->updated_at = $transactionHeader->updated_at;
                        $nHeader->save();
                    } catch (\Exception $ex) {
                        continue;
                    }

                    // copy transaction_details
                    foreach ($transactionHeader->transaction_details as $transactionDetail) {
                        $nDetail = new TransactionDetail;
                        $nDetail = $transactionDetail->replicate();
                        $nDetail->setConnection('mysql_cloud');
                        $nDetail->created_at = $transactionDetail->created_at;
                        $nDetail->updated_at = $transactionDetail->updated_at;
                        $nDetail->header_id = $nHeader->id;
                        $nDetail->save();

                        // copy transaction_detail_partners
                        $detailPartners = TransactionDetailPartner::where('transaction_detail_id', $transactionDetail->id)->get();
                        foreach ($detailPartners as $detailPartner) {
                            $nDetailPartner = new TransactionDetailPartner;
                            $nDetailPartner = $detailPartner->replicate();
                            $nDetailPartner->setConnection('mysql_cloud');
                            $nDetailPartner->transaction_detail_id = $nDetail->id;
                            $nDetailPartner->save();
                        }
                    }

                    // copy transaction_payments
                    foreach ($transactionHeader->transaction_payments as $transactionPayment) {
                        $nPayment = new TransactionPayment;
                        $nPayment = $transactionPayment->replicate();
                        $nPayment->setConnection('mysql_cloud');
                        $nPayment->created_at = $transactionPayment->created_at;
                        $nPayment->updated_at = $transactionPayment->updated_at;
                        $nPayment->header_id = $nHeader->id;
                        $nPayment->save();
                    }

                    // copy journal_headers
                    $journalHeaders = JournalHeader::where('notes', 'like', '%'.$transactionHeader->code.'%')->get();
                    foreach ($journalHeaders as $journalHeader) {
                        $nJournalHeader = new JournalHeader;
                        $nJournalHeader = $journalHeader->replicate();
                        $nJournalHeader->setConnection('mysql_cloud');
                        $nJournalHeader->status_id = 13;
                        $nJournalHeader->is_approve = 1;
                        $nJournalHeader->created_at = $journalHeader->created_at;
                        $nJournalHeader->updated_at = $journalHeader->updated_at;
                        $nJournalHeader->save();

                        // copy journal_details
                        $journalDetails = JournalDetail::where('reference_code', $transactionHeader->code)->get();
                        foreach ($journalDetails as $journalDetail) {
                            $nDetailJournal = new JournalDetail;
                            $nDetailJournal = $journalDetail->replicate();
                            $nDetailJournal->header_id = $nJournalHeader->id;

                            $nDetailJournal->setConnection('mysql_cloud');
                            $nDetailJournal->save();
                        }
                    }
                }

            }

            return 'SUCCESS';
        } catch (\Exception $ex) {
            return dd($ex);
        }
    }

    public function doubleBookv3()
    {
        try {
            $branchs = Branch::all();
            $yesterday = '2023-11-06';
            // $yesterdayFromNow = Carbon::now()->addDays(-1);
            // $yesterday = $yesterdayFromNow->locale('id_ID')->format('Y-m-d');
            // dd($yesterday);
            foreach ($branchs as $branch) {
                $payments = DB::table('transaction_headers')
                    ->where('branch_id', $branch->id)
                    ->whereBetween('created_at', [$yesterday.' 00:00:00', $yesterday.' 23:59:59']);
                $grandtotal = $payments->count();

                // $totalTarget = $grandtotal * 0.05;

                // $target = $totalTarget / 3;
                $currTarget = 0;
                $headerIdArr = [];
                // batch 1 (10.00 - 14.00)
                $payments1 = $payments
                    ->whereBetween('created_at', [$yesterday.' 10:00:00', $yesterday.' 14:00:00'])
                    ->get();
                $payments1 = $payments1->shuffle();
                foreach ($payments1 as $payment) {
                    if ($currTarget >= ($grandtotal * 0.2)) {
                        break;
                    } else {
                        $currTarget += 1;
                        array_push($headerIdArr, $payment->id);
                    }
                }

                // batch 2 (14.00 - 18.00)
                $payments2 = $payments
                    ->whereBetween('created_at', [$yesterday.' 14:00:00', $yesterday.' 18:00:00'])
                    ->get();
                $payments2 = $payments2->shuffle();
                foreach ($payments2 as $payment) {
                    if ($currTarget >= ($grandtotal * 0.2)) {
                        break;
                    } else {
                        $currTarget += 1;
                        array_push($headerIdArr, $payment->id);
                    }
                }

                // batch 3 (18.00 - end)
                $payments3 = $payments
                    ->whereBetween('created_at', [$yesterday.' 18:00:00', $yesterday.' 23:59:59'])
                    ->get();
                $payments3 = $payments3->shuffle();
                foreach ($payments3 as $payment) {
                    if ($currTarget >= ($grandtotal * 0.6)) {
                        break;
                    } else {
                        $currTarget += 1;
                        array_push($headerIdArr, $payment->id);
                    }
                }

                // get selected transaction
                $transactionHeaders = TransactionHeader::with(['transaction_details', 'transaction_payments'])->whereIn('id', $headerIdArr)->orderby('id')->get();
                foreach ($transactionHeaders as $transactionHeader) {
                    // copy transaction_headers
                    try {
                        $nHeader = new TransactionHeader;
                        $nHeader = $transactionHeader->replicate();
                        $nHeader->setConnection('mysql_cloud');
                        $nHeader->created_at = $transactionHeader->created_at;
                        $nHeader->updated_at = $transactionHeader->updated_at;
                        $nHeader->save();
                    } catch (\Exception $ex) {
                        continue;
                    }

                    // copy transaction_details
                    foreach ($transactionHeader->transaction_details as $transactionDetail) {
                        $nDetail = new TransactionDetail;
                        $nDetail = $transactionDetail->replicate();
                        $nDetail->setConnection('mysql_cloud');
                        $nDetail->created_at = $transactionDetail->created_at;
                        $nDetail->updated_at = $transactionDetail->updated_at;
                        $nDetail->header_id = $nHeader->id;
                        $nDetail->save();

                        // copy transaction_detail_partners
                        $detailPartners = TransactionDetailPartner::where('transaction_detail_id', $transactionDetail->id)->get();
                        foreach ($detailPartners as $detailPartner) {
                            $nDetailPartner = new TransactionDetailPartner;
                            $nDetailPartner = $detailPartner->replicate();
                            $nDetailPartner->setConnection('mysql_cloud');
                            $nDetailPartner->transaction_detail_id = $nDetail->id;
                            $nDetailPartner->save();
                        }
                    }

                    // copy transaction_payments
                    foreach ($transactionHeader->transaction_payments as $transactionPayment) {
                        $nPayment = new TransactionPayment;
                        $nPayment = $transactionPayment->replicate();
                        $nPayment->setConnection('mysql_cloud');
                        $nPayment->created_at = $transactionPayment->created_at;
                        $nPayment->updated_at = $transactionPayment->updated_at;
                        $nPayment->header_id = $nHeader->id;
                        $nPayment->save();
                    }

                    // copy journal_headers
                    $journalHeaders = JournalHeader::where('notes', 'like', '%'.$transactionHeader->code.'%')->get();
                    foreach ($journalHeaders as $journalHeader) {
                        $nJournalHeader = new JournalHeader;
                        $nJournalHeader = $journalHeader->replicate();
                        $nJournalHeader->setConnection('mysql_cloud');
                        $nJournalHeader->status_id = 13;
                        $nJournalHeader->is_approve = 1;
                        $nJournalHeader->created_at = $journalHeader->created_at;
                        $nJournalHeader->updated_at = $journalHeader->updated_at;
                        $nJournalHeader->save();

                        // copy journal_details
                        $journalDetails = JournalDetail::where('reference_code', $transactionHeader->code)->get();
                        foreach ($journalDetails as $journalDetail) {
                            $nDetailJournal = new JournalDetail;
                            $nDetailJournal = $journalDetail->replicate();
                            $nDetailJournal->header_id = $nJournalHeader->id;

                            $nDetailJournal->setConnection('mysql_cloud');
                            $nDetailJournal->save();
                        }
                    }
                }

            }

            return 'SUCCESS';
        } catch (\Exception $ex) {
            return dd($ex);
        }
    }

    public function doubleBook()
    {
        try {

            // $this->check_TotalTransaction_Payment_Unbalance();
            // $branchs = Branch::all();
            $branchs = Branch::where('id', 6)->get();
            $yesterday = '2023-08-28';

            $realAmount = 0;
            $secondAmount = 0;
            $secondAmountTotal = 0;
            $totalTamu = 0;
            $payment1 = 0.1;
            $payment2 = 0.1;
            $payment3 = 0.2;

            foreach ($branchs as $branch) {
                if ($branch->id == 1) {
                    $payment1 = 0.05;
                    $payment2 = 0.08;
                    $payment3 = 0.03;
                }
                if ($branch->id == 1) {
                    $payment1 = 0.03;
                    $payment2 = 0.05;
                    $payment3 = 0.03;
                }

                $payments = DB::table('transaction_headers')
                    ->join('transaction_details', 'transaction_details.header_id', 'transaction_headers.id')
                    ->select('transaction_headers.id as id')
                    ->where('transaction_headers.branch_id', $branch->id)
                    ->where('transaction_headers.foc_type', '!=', 'YES')
                    // ->where(DB::raw("upper(transaction_headers.foc_type) ='NO'"))
                    ->where('transaction_details.package_id', '!=', null)
                    ->whereBetween('transaction_headers.created_at', [$yesterday.' 00:00:00', $yesterday.' 23:59:59']);
                $grandtotal = $payments->count();
                $totalTamu = $payments->count();
                // $totalTarget = $grandtotal * 0.05;

                // $target = $totalTarget / 3;
                $currTarget = 0;
                $headerIdArr = [];
                $dataArr = [];
                // batch 1 (10.00 - 14.00)
                $payments1 = $payments
                    ->whereBetween('transaction_headers.created_at', [$yesterday.' 10:00:00', $yesterday.' 14:00:00'])
                    ->get();
                $payments1 = $payments1->shuffle();
                foreach ($payments1 as $payment) {
                    $tmpDt = TransactionDetail::where('header_id', $payment->id)->whereIn('package_id', [11, 12, 13, 14])
                        ->get();
                    if (count($tmpDt) > 0) {
                        break;
                    }
                    if ($currTarget >= ($grandtotal * $payment1)) {
                        break;
                    } else {
                        $currTarget += 1;
                        array_push($headerIdArr, $payment->id);
                    }
                }

                // batch 2 (14.00 - 18.00)
                $payments = DB::table('transaction_headers')
                    ->join('transaction_details', 'transaction_details.header_id', 'transaction_headers.id')
                    ->select('transaction_headers.id as id')
                    ->where('transaction_headers.branch_id', $branch->id)
                    ->where('transaction_headers.foc_type', '!=', 'YES')
                    // ->where(DB::raw("upper(transaction_headers.foc_type) ='NO'"))
                    ->where('transaction_details.package_id', '!=', null)
                    ->whereBetween('transaction_headers.created_at', [$yesterday.' 00:00:00', $yesterday.' 23:59:59']);
                $currTarget = 0;
                $payments2 = $payments
                    ->whereBetween('transaction_headers.created_at', [$yesterday.' 14:00:00', $yesterday.' 18:00:00'])
                    ->get();
                $payments2 = $payments2->shuffle();
                foreach ($payments2 as $payment) {
                    $tmpDt = TransactionDetail::where('header_id', $payment->id)->whereIn('package_id', [11, 12, 13, 14])
                        ->get();
                    if (count($tmpDt) > 0) {
                        break;
                    }
                    if ($currTarget >= ($grandtotal * $payment2)) {
                        break;
                    } else {
                        $currTarget += 1;
                        array_push($headerIdArr, $payment->id);
                    }
                }

                // batch 3 (18.00 - end)
                $payments = DB::table('transaction_headers')
                    ->join('transaction_details', 'transaction_details.header_id', 'transaction_headers.id')
                    ->select('transaction_headers.id as id')
                    ->where('transaction_headers.branch_id', $branch->id)
                    ->where('transaction_headers.foc_type', '!=', 'YES')
                    // ->where(DB::raw("upper(transaction_headers.foc_type) ='NO'"))
                    ->where('transaction_details.package_id', '!=', null)
                    ->whereBetween('transaction_headers.created_at', [$yesterday.' 00:00:00', $yesterday.' 23:59:59']);
                $currTarget = 0;
                $payments3 = $payments
                    ->whereBetween('transaction_headers.created_at', [$yesterday.' 18:00:00', $yesterday.' 23:59:59'])
                    ->get();
                $payments3 = $payments3->shuffle();
                foreach ($payments3 as $payment) {
                    $tmpDt = TransactionDetail::where('header_id', $payment->id)->whereIn('package_id', [11, 12, 13, 14])
                        ->get();
                    if (count($tmpDt) > 0) {
                        break;
                    }
                    if ($currTarget >= ($grandtotal * $payment3)) {
                        break;
                    } else {
                        $currTarget += 1;
                        array_push($headerIdArr, $payment->id);
                    }
                }

                // get selected transaction
                $transactionHeaders = TransactionHeader::with(['transaction_details', 'transaction_payments'])
                    ->whereIn('id', $headerIdArr)
                    ->where('foc_type', '!=', 'YES')->orderby('id')->get();

                foreach ($transactionHeaders as $transactionHeader) {

                    // copy transaction_details
                    foreach ($transactionHeader->transaction_details as $transactionDetail) {
                        if ($transactionDetail->package_id != null) {
                            $package = Package::where('id', $transactionDetail->package_id)->first();
                            $secondAmount = ($package->second_price * $transactionDetail->qty);

                            $realAmount += $transactionDetail->subtotal;
                            $dataArr[] = [
                                'id' => $transactionHeader->id,
                                'real_amount' => $realAmount,
                                'second_amount' => $secondAmount,
                            ];
                        }
                    }

                }

            }
            foreach ($dataArr as $arr) {
                $secondAmountTotal += $arr['second_amount'];
            }
            $temp = ($secondAmountTotal * 30);
            dd($dataArr, 'total Tamu: '.number_format($totalTamu, 0, '.', ','),
                'total Tamu hehe: '.number_format(count($dataArr), 0, '.', ','),
                'real Amount: '.number_format($realAmount, 0, '.', ','),
                'hehe amount: '.number_format($secondAmountTotal, 0, '.', ','),
                'perkiraan hehe amount 30 hari: '.number_format($temp, 0, '.', ','),
                'bayar pajak: '.number_format(($temp * 0.65) * 0.5, 0, '.', ','));

            return 'SUCCESS';
        } catch (\Exception $ex) {
            return dd($ex);
        }
    }

    public function doubleBookRevisionv1(Request $request)
    {
        try {
            $branchId = $request->get('branch_id');
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');

            $period = CarbonPeriod::create($startDate.' 00:00:00', $endDate.' 23:59:59');

            $codeArr = [];
            foreach ($period as $dateData) {

                // $branchs = Branch::all();
                $branchs = Branch::where('id', $branchId)->get();
                $yesterday = $dateData->format('Y-m-d');
                $yesterday2 = $dateData->format('Y-m-d');

                $payment1 = 0.1;
                $payment2 = 0.1;
                $payment3 = 0.2;
                foreach ($branchs as $branch) {
                    // persenan emperor
                    if ($branch->id == 1) {
                        //     $payment1 = 0.2;
                        //     $payment2 = 0.2;
                        //     $payment3 = 0.4;
                        $payment1 = 0.1;
                        $payment2 = 0.15;
                        $payment3 = 0.25;
                    }
                    // persenan semut
                    if ($branch->id == 2) {
                        $payment2 = 0.2;
                    }
                    // persenan manyar
                    if ($branch->id == 4) {
                        $payment1 = 0.3;
                        $payment2 = 0.3;
                        $payment3 = 0.4;
                    }
                    // persenan darmo
                    if ($branch->id == 5) {
                        $payment1 = 0.08;
                        $payment2 = 0.08;
                        $payment3 = 0.18;
                    }
                    // persenan oura
                    if ($branch->is_oura == 1) {
                        $payment1 = 0.05;
                        $payment2 = 0.08;
                        $payment3 = 0.03;
                    }

                    $payments = DB::table('transaction_headers')
                        ->join('transaction_details', 'transaction_details.header_id', 'transaction_headers.id')
                        ->select('transaction_headers.id as id')
                        ->where('transaction_headers.branch_id', $branch->id)
                        ->whereRaw("LOWER(transaction_headers.foc_type) = 'no'")
                        ->where('transaction_details.package_id', '!=', null)
                        ->whereBetween('transaction_headers.created_at', [$yesterday.' 00:00:00', $yesterday2.' 23:59:59']);
                    $grandtotal = $payments->count();
                    $totalTamu = $payments->count();

                    // $totalTarget = $grandtotal * 0.05;

                    // $target = $totalTarget / 3;
                    $currTarget = 0;
                    $headerIdArr = [];
                    $dataArr = [];
                    // batch 1 (10.00 - 14.00)
                    $payments1 = $payments
                        ->whereBetween('transaction_headers.created_at', [$yesterday.' 10:00:00', $yesterday2.' 14:00:00'])
                        ->get();
                    $payments1 = $payments1->shuffle();
                    // dd($payments->get(), $payments1);

                    foreach ($payments1 as $payment) {
                        $tmpDt = TransactionDetail::where('header_id', $payment->id)->whereIn('package_id', [11, 12, 13, 14, 47, 66, 48, 67, 84, 85, 86, 87])
                            ->get();
                        if (count($tmpDt) > 0) {
                            break;
                        }
                        if ($currTarget >= ($grandtotal * $payment1)) {
                            break;
                        } else {
                            $currTarget += 1;
                            array_push($headerIdArr, $payment->id);
                        }
                    }

                    // batch 2 (14.00 - 18.00)
                    $payments = DB::table('transaction_headers')
                        ->join('transaction_details', 'transaction_details.header_id', 'transaction_headers.id')
                        ->select('transaction_headers.id as id')
                        ->where('transaction_headers.branch_id', $branch->id)
                        ->whereRaw("LOWER(transaction_headers.foc_type) = 'no'")
                        ->where('transaction_details.package_id', '!=', null)
                        ->whereBetween('transaction_headers.created_at', [$yesterday.' 00:00:00', $yesterday2.' 23:59:59']);
                    $currTarget = 0;
                    $payments2 = $payments
                        ->whereBetween('transaction_headers.created_at', [$yesterday.' 14:00:00', $yesterday2.' 18:00:00'])
                        ->get();
                    $payments2 = $payments2->shuffle();
                    foreach ($payments2 as $payment) {
                        $tmpDt = TransactionDetail::where('header_id', $payment->id)->whereIn('package_id', [11, 12, 13, 14, 47, 66, 48, 67, 84, 85, 86, 87])
                            ->get();
                        if (count($tmpDt) > 0) {
                            break;
                        }
                        if ($currTarget >= ($grandtotal * $payment2)) {
                            break;
                        } else {
                            $currTarget += 1;
                            array_push($headerIdArr, $payment->id);
                        }
                    }

                    // batch 3 (18.00 - end)
                    $payments = DB::table('transaction_headers')
                        ->join('transaction_details', 'transaction_details.header_id', 'transaction_headers.id')
                        ->select('transaction_headers.id as id')
                        ->where('transaction_headers.branch_id', $branch->id)
                        ->whereRaw("LOWER(transaction_headers.foc_type) = 'no'")
                        ->where('transaction_details.package_id', '!=', null)
                        ->whereBetween('transaction_headers.created_at', [$yesterday.' 00:00:00', $yesterday2.' 23:59:59']);
                    $currTarget = 0;
                    $payments3 = $payments
                        ->whereBetween('transaction_headers.created_at', [$yesterday.' 18:00:00', $yesterday2.' 23:59:59'])
                        ->get();
                    $payments3 = $payments3->shuffle();
                    foreach ($payments3 as $payment) {
                        $tmpDt = TransactionDetail::where('header_id', $payment->id)->whereIn('package_id', [11, 12, 13, 14, 47, 66, 48, 67, 84, 85, 86, 87])
                            ->get();
                        if (count($tmpDt) > 0) {
                            break;
                        }
                        if ($currTarget >= ($grandtotal * $payment3)) {
                            break;
                        } else {
                            $currTarget += 1;
                            array_push($headerIdArr, $payment->id);
                        }
                    }

                    // get selected transaction
                    $transactionHeaders = TransactionHeader::with(['transaction_details', 'transaction_payments'])
                        ->whereIn('id', $headerIdArr)
                        ->whereRaw("LOWER(foc_type) = 'no'")
                        ->orderby('id')->get();

                    Log::channel('filter_transaction')->info('doubleBookRevision TRANSACTION FILTER = '.count($transactionHeaders));
                    foreach ($transactionHeaders as $transactionHeader) {
                        // copy transaction_headers
                        try {
                            $nHeader = new TransactionHeader;
                            $nHeader = $transactionHeader->replicate();
                            $nHeader->setConnection('mysql_cloud');
                            $nHeader->created_at = $transactionHeader->created_at;
                            $nHeader->updated_at = $transactionHeader->updated_at;
                            $nHeader->save();

                            $totalAmount = 0;
                            // copy transaction_details
                            foreach ($transactionHeader->transaction_details as $transactionDetail) {
                                if ($transactionDetail->package_id != null) {
                                    $package = Package::where('id', $transactionDetail->package_id)->first();

                                    $netPrice = $package->second_price;
                                    if ($branch->is_oura === 1) {
                                        // $netPrice = $package->price - 35000;
                                        // $netPrice = $netPrice - 17500;
                                        $netPrice = ($package->price - intval($package->price * 0.3)) - 35000;
                                    }
                                    $totalAmount = ($netPrice * $transactionDetail->qty);
                                }
                            }
                            $nHeader->grand_total = $totalAmount;
                            $nHeader->discount = 0;
                            $nHeader->discount_total = 0;
                            $nHeader->amount = 0;
                            $nHeader->rounding_amount = 0;
                            $nHeader->save();
                        } catch (\Exception $ex) {
                            Log::channel('filter_transaction')->info('doubleBookRevision ERROR = '.$ex);

                            continue;
                        }

                        // copy transaction_payments
                        foreach ($transactionHeader->transaction_payments as $transactionPayment) {
                            $nPayment = new TransactionPayment;
                            $nPayment = $transactionPayment->replicate();
                            $nPayment->setConnection('mysql_cloud');
                            $nPayment->created_at = $transactionPayment->created_at;
                            $nPayment->updated_at = $transactionPayment->updated_at;
                            $nPayment->header_id = $nHeader->id;
                            $nPayment->amount = $totalAmount;
                            $nPayment->save();
                        }
                        $codeArr[] = $transactionHeader->code;

                    }

                }
            }

            dd('SELESAI', $transactionHeaders, $codeArr);
        } catch (\Exception $ex) {
            return dd($ex);
        }
    }

    public function doubleBookRevision(Request $request)
    {
        try {
            $branchId = $request->get('branch_id');
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');

            $period = CarbonPeriod::create($startDate.' 00:00:00', $endDate.' 23:59:59');

            $codeArr = [];
            foreach ($period as $dateData) {

                // $branchs = Branch::all();
                $branchs = Branch::where('id', $branchId)->get();
                $yesterday = $dateData->format('Y-m-d');
                $yesterday2 = $dateData->format('Y-m-d');
                // dd($dateData, $yesterday, $yesterday2);

                $payment1 = 0.1;
                $payment2 = 0.1;
                $payment3 = 0.2;
                $totalTargetPerDay = 330000;
                foreach ($branchs as $branch) {
                    // persenan emperor
                    if ($branch->id == 1) {
                        //     $payment1 = 0.2;
                        //     $payment2 = 0.2;
                        //     $payment3 = 0.4;
                        $payment1 = 0.1;
                        $payment2 = 0.15;
                        $payment3 = 0.25;
                        $totalTargetPerDay = 3540000;
                    }
                    // persenan semut
                    if ($branch->id == 2) {
                        $payment1 = 0.01;
                        $payment2 = 0.01;
                        $payment3 = 0.05;
                        $totalTargetPerDay = 330000;
                    }
                    // persenan manyar - darmo
                    if ($branch->id >= 3 && $branch->id <= 5) {
                        $payment1 = 0.03;
                        $payment2 = 0.03;
                        $payment3 = 0.08;
                        $totalTargetPerDay = 532000;
                    }
                    // persenan oura
                    if ($branch->is_oura == 1) {
                        $payment1 = 0.05;
                        $payment2 = 0.08;
                        $payment3 = 0.03;
                        $totalTargetPerDay = 60000;
                    }

                    // $payments = DB::table('transaction_headers')
                    //     ->join('transaction_details', 'transaction_details.header_id', 'transaction_headers.id')
                    //     ->select('transaction_headers.id as id','transaction_details.subtotal as subtotal',)
                    //     ->where('transaction_headers.branch_id', $branch->id)
                    //     ->whereRaw("LOWER(transaction_headers.foc_type) = 'no'")
                    //     ->where('transaction_details.package_id', '!=', null)
                    //     ->whereBetween('transaction_headers.created_at', [$yesterday.' 00:00:00', $yesterday2.' 23:59:59']);
                    // $grandtotal = $payments->count();
                    // $totalTamu = $payments->count();

                    // $totalTarget = $grandtotal * 0.05;

                    $target = $totalTargetPerDay / 2;
                    $currTarget = 0;
                    $headerIdArr = [];
                    $dataArr = [];
                    // batch 1 (10.00 - 14.00)
                    $payments1 = DB::table('transaction_headers')
                        ->join('transaction_details', 'transaction_details.header_id', 'transaction_headers.id')
                        ->select('transaction_headers.id as id', 'transaction_details.subtotal as subtotal')
                        ->where('transaction_headers.branch_id', $branch->id)
                        ->whereRaw("LOWER(transaction_headers.foc_type) = 'no'")
                        ->where('transaction_details.package_id', '!=', null)
                        ->whereBetween('transaction_headers.created_at', [$yesterday.' 10:00:00', $yesterday2.' 14:00:00'])
                        ->get();
                    $payments1 = $payments1->shuffle();
                    // dd($payments->get(), $payments1);

                    foreach ($payments1 as $payment) {
                        $tmpDt = TransactionDetail::where('header_id', $payment->id)->whereIn('package_id', [11, 12, 13, 14, 47, 66, 48, 67, 84, 85, 86, 87])
                            ->get();
                        if (count($tmpDt) > 0) {
                            break;
                        }
                        $taxAmount = (($payment->subtotal * 0.65)) * 0.5;

                        if ($currTarget >= ($target)) {
                            break;
                        } else {
                            $currTarget += $taxAmount;
                            array_push($headerIdArr, $payment->id);
                        }
                    }

                    // batch 2 (14.00 - 18.00)
                    // $payments = DB::table('transaction_headers')
                    //     ->join('transaction_details', 'transaction_details.header_id', 'transaction_headers.id')
                    //     ->select('transaction_headers.id as id','transaction_details.subtotal as subtotal',)
                    //     ->where('transaction_headers.branch_id', $branch->id)
                    //     ->whereRaw("LOWER(transaction_headers.foc_type) = 'no'")
                    //     ->where('transaction_details.package_id', '!=', null)
                    //     ->whereBetween('transaction_headers.created_at', [$yesterday.' 00:00:00', $yesterday2.' 23:59:59']);
                    $currTarget = 0;
                    $payments2 = DB::table('transaction_headers')
                        ->join('transaction_details', 'transaction_details.header_id', 'transaction_headers.id')
                        ->select('transaction_headers.id as id', 'transaction_details.subtotal as subtotal')
                        ->where('transaction_headers.branch_id', $branch->id)
                        ->whereRaw("LOWER(transaction_headers.foc_type) = 'no'")
                        ->where('transaction_details.package_id', '!=', null)
                        ->whereBetween('transaction_headers.created_at', [$yesterday.' 14:00:00', $yesterday2.' 18:00:00'])
                        ->get();
                    $payments2 = $payments2->shuffle();
                    foreach ($payments2 as $payment) {
                        $tmpDt = TransactionDetail::where('header_id', $payment->id)->whereIn('package_id', [11, 12, 13, 14, 47, 66, 48, 67, 84, 85, 86, 87])
                            ->get();
                        if (count($tmpDt) > 0) {
                            break;
                        }
                        $taxAmount = (($payment->subtotal * 0.65)) * 0.5;
                        if ($currTarget >= ($target)) {
                            break;
                        } else {
                            $currTarget += $taxAmount;
                            array_push($headerIdArr, $payment->id);
                        }
                    }

                    // batch 3 (18.00 - end)
                    // $payments = DB::table('transaction_headers')
                    //     ->join('transaction_details', 'transaction_details.header_id', 'transaction_headers.id')
                    //     ->select('transaction_headers.id as id','transaction_details.subtotal as subtotal',)
                    //     ->where('transaction_headers.branch_id', $branch->id)
                    //     ->whereRaw("LOWER(transaction_headers.foc_type) = 'no'")
                    //     ->where('transaction_details.package_id', '!=', null)
                    //     ->whereBetween('transaction_headers.created_at', [$yesterday.' 00:00:00', $yesterday2.' 23:59:59']);
                    $currTarget = 0;
                    $payments3 = DB::table('transaction_headers')
                        ->join('transaction_details', 'transaction_details.header_id', 'transaction_headers.id')
                        ->select('transaction_headers.id as id', 'transaction_details.subtotal as subtotal')
                        ->where('transaction_headers.branch_id', $branch->id)
                        ->whereRaw("LOWER(transaction_headers.foc_type) = 'no'")
                        ->where('transaction_details.package_id', '!=', null)
                        ->whereBetween('transaction_headers.created_at', [$yesterday.' 18:00:00', $yesterday2.' 23:59:59'])
                        ->get();
                    $payments3 = $payments3->shuffle();
                    foreach ($payments3 as $payment) {
                        $tmpDt = TransactionDetail::where('header_id', $payment->id)->whereIn('package_id', [11, 12, 13, 14, 47, 66, 48, 67, 84, 85, 86, 87])
                            ->get();
                        if (count($tmpDt) > 0) {
                            break;
                        }
                        $taxAmount = (($payment->subtotal * 0.65)) * 0.5;
                        if ($currTarget >= ($target)) {
                            break;
                        } else {
                            $currTarget += $taxAmount;
                            array_push($headerIdArr, $payment->id);
                        }
                    }

                    // get selected transaction
                    $transactionHeaders = TransactionHeader::with(['transaction_details', 'transaction_payments'])
                        ->whereIn('id', $headerIdArr)
                        ->whereRaw("LOWER(foc_type) = 'no'")
                        ->orderby('id')->get();

                    Log::channel('filter_transaction')->info('doubleBookRevision TRANSACTION FILTER = '.count($transactionHeaders));
                    foreach ($transactionHeaders as $transactionHeader) {
                        // copy transaction_headers
                        try {
                            $nHeader = new TransactionHeader;
                            $nHeader = $transactionHeader->replicate();
                            $nHeader->setConnection('mysql_cloud');
                            $nHeader->created_at = $transactionHeader->created_at;
                            $nHeader->updated_at = $transactionHeader->updated_at;
                            $nHeader->save();

                            $totalAmount = 0;
                            // copy transaction_details
                            foreach ($transactionHeader->transaction_details as $transactionDetail) {
                                if ($transactionDetail->package_id != null) {
                                    $package = Package::where('id', $transactionDetail->package_id)->first();

                                    $netPrice = $package->second_price;
                                    if ($branch->is_oura === 1) {
                                        // $netPrice = $package->price - 35000;
                                        // $netPrice = $netPrice - 17500;
                                        $netPrice = ($package->price - intval($package->price * 0.3)) - 35000;
                                    }
                                    $totalAmount = ($netPrice * $transactionDetail->qty);
                                }
                            }
                            $nHeader->grand_total = $totalAmount;
                            $nHeader->discount = 0;
                            $nHeader->discount_total = 0;
                            $nHeader->amount = 0;
                            $nHeader->rounding_amount = 0;
                            $nHeader->save();
                        } catch (\Exception $ex) {
                            Log::channel('filter_transaction')->info('doubleBookRevision ERROR = '.$ex);

                            continue;
                        }

                        // copy transaction_payments
                        foreach ($transactionHeader->transaction_payments as $transactionPayment) {
                            $nPayment = new TransactionPayment;
                            $nPayment = $transactionPayment->replicate();
                            $nPayment->setConnection('mysql_cloud');
                            $nPayment->created_at = $transactionPayment->created_at;
                            $nPayment->updated_at = $transactionPayment->updated_at;
                            $nPayment->header_id = $nHeader->id;
                            $nPayment->amount = $totalAmount;
                            $nPayment->save();
                        }
                        $codeArr[] = $transactionHeader->code;

                    }

                }
            }

            dd('SELESAI', $transactionHeaders, $codeArr);
        } catch (\Exception $ex) {
            return dd($ex);
        }
    }

    public function doubleBookBulkv1()
    {
        try {
            $branchs = Branch::where('id', 6)->get();
            $yesterday = '2023-11-12';
            $period = CarbonPeriod::create('2023-11-01', '2023-11-30');

            foreach ($period as $date) {
                foreach ($branchs as $branch) {
                    $payments = DB::table('transaction_payments')
                        ->where('branch_id', $branch->id)
                        ->whereBetween('created_at', [$yesterday.' 00:00:00', $yesterday.' 23:59:59']);
                    $grandtotal = $payments->sum('amount');
                    $totalTarget = $grandtotal * 0.08;

                    $target = $totalTarget / 3;
                    $currTarget = 0;
                    $headerIdArr = [];
                    // batch 1 (10.00 - 14.00)
                    $payments1 = $payments
                        ->whereBetween('created_at', [$yesterday.' 10:00:00', $yesterday.' 14:00:00'])
                        ->get();
                    $payments1 = $payments1->shuffle();
                    foreach ($payments1 as $payment) {
                        if ($currTarget >= $totalTarget) {
                            break;
                        } else {
                            $currTarget += $payment->amount;
                            array_push($headerIdArr, $payment->header_id);
                        }
                    }

                    // batch 2 (14.00 - 18.00)
                    $payments2 = $payments
                        ->whereBetween('created_at', [$yesterday.' 14:00:00', $yesterday.' 18:00:00'])
                        ->get();
                    $payments2 = $payments2->shuffle();
                    foreach ($payments2 as $payment) {
                        if ($currTarget >= $totalTarget) {
                            break;
                        } else {
                            $currTarget += $payment->amount;
                            array_push($headerIdArr, $payment->header_id);
                        }
                    }

                    // batch 3 (18.00 - end)
                    $payments3 = $payments
                        ->whereBetween('created_at', [$yesterday.' 18:00:00', $yesterday.' 23:59:59'])
                        ->get();
                    $payments3 = $payments3->shuffle();
                    foreach ($payments3 as $payment) {
                        if ($currTarget >= $totalTarget) {
                            break;
                        } else {
                            $currTarget += $payment->amount;
                            array_push($headerIdArr, $payment->header_id);
                        }
                    }

                    // get selected transaction
                    $transactionHeaders = TransactionHeader::with(['transaction_details', 'transaction_payments'])->whereIn('id', $headerIdArr)->orderby('id')->get();
                    foreach ($transactionHeaders as $transactionHeader) {
                        // copy transaction_headers
                        try {
                            $nHeader = new TransactionHeader;
                            $nHeader = $transactionHeader->replicate();
                            $nHeader->setConnection('mysql_cloud');
                            $nHeader->created_at = $transactionHeader->created_at;
                            $nHeader->updated_at = $transactionHeader->updated_at;

                            // copy transaction_details
                            foreach ($transactionHeader->transaction_details as $transactionDetail) {
                                if ($transactionDetail->package_id != null) {
                                    $package = Package::where('id', $transactionDetail->package_id)->first();

                                    $netPrice = $package->second_price;
                                    if ($branch->is_oura === 1) {
                                        // $netPrice = $package->price - 35000;
                                        $netPrice = $netPrice - 17500;
                                    }
                                    $totalAmount = ($netPrice * $transactionDetail->qty);
                                }
                            }
                            $nHeader->grand_total = $totalAmount;
                            $nHeader->discount = 0;
                            $nHeader->discount_total = 0;
                            $nHeader->amount = 0;
                            $nHeader->rounding_amount = 0;
                            $nHeader->save();
                        } catch (\Exception $ex) {
                            continue;
                        }

                        // copy transaction_payments
                        foreach ($transactionHeader->transaction_payments as $transactionPayment) {
                            $nPayment = new TransactionPayment;
                            $nPayment = $transactionPayment->replicate();
                            $nPayment->setConnection('mysql_cloud');
                            $nPayment->created_at = $transactionPayment->created_at;
                            $nPayment->updated_at = $transactionPayment->updated_at;
                            $nPayment->header_id = $nHeader->id;
                            $nPayment->amount = $totalAmount;
                            $nPayment->save();
                        }

                    }

                }
            }

            return 'DONE';
        } catch (\Exception $ex) {
            return dd($ex);
        }
    }

    public function doubleBookBulkv2()
    {
        try {
            $branchs = Branch::where('id', 6)->get();
            // $branchs = Branch::all();
            // $yesterdayFromNow = Carbon::now()->addDays(-1);
            // $yesterday = $yesterdayFromNow->locale('id_ID')->format('Y-m-d');

            $period = CarbonPeriod::create('2023-12-01', '2023-12-01');

            // Iterate over the period
            foreach ($period as $date) {
                $yesterday = $date->format('Y-m-d');

                // FILTERING START
                // persenan kimochi
                $payment1 = 0.1;
                $payment2 = 0.1;
                $payment3 = 0.2;
                foreach ($branchs as $branch) {
                    // persenan emperor
                    if ($branch->id == 1) {
                        $payment1 = 0.15;
                        $payment2 = 0.15;
                        $payment3 = 0.2;
                    }
                    // persenan semut
                    if ($branch->id == 2) {
                        $payment1 = 0.03;
                        $payment2 = 0.03;
                        $payment3 = 0.08;
                    }
                    // persenan manyar - darmo
                    if ($branch->id >= 3 && $branch->id <= 5) {
                        $payment1 = 0.05;
                        $payment2 = 0.05;
                        $payment3 = 0.1;
                    }
                    // persenan oura
                    if ($branch->is_oura == 1) {
                        $payment1 = 0.05;
                        $payment2 = 0.08;
                        $payment3 = 0.03;
                    }

                    $payments = DB::table('transaction_headers')
                        ->join('transaction_details', 'transaction_details.header_id', 'transaction_headers.id')
                        ->select('transaction_headers.id as id')
                        ->where('transaction_headers.branch_id', $branch->id)
                        ->whereRaw("LOWER(transaction_headers.foc_type) = 'no'")
                        ->where('transaction_details.package_id', '!=', null)
                        ->whereBetween('transaction_headers.created_at', [$yesterday.' 00:00:00', $yesterday.' 23:59:59']);
                    $grandtotal = $payments->count();
                    $totalTamu = $payments->count();
                    Log::channel('filter_transaction')->info('GRANDTOTAL = '.$grandtotal);

                    // $totalTarget = $grandtotal * 0.05;

                    // $target = $totalTarget / 3;
                    $currTarget = 0;
                    $headerIdArr = [];
                    $dataArr = [];
                    // batch 1 (10.00 - 14.00)
                    $payments1 = $payments
                        ->whereBetween('transaction_headers.created_at', [$yesterday.' 10:00:00', $yesterday.' 14:00:00'])
                        ->get();
                    $payments1 = $payments1->shuffle();
                    foreach ($payments1 as $payment) {
                        $tmpDt = TransactionDetail::where('header_id', $payment->id)->whereIn('package_id', [11, 12, 13, 14, 85, 86, 87])
                            ->get();
                        if (count($tmpDt) > 0) {
                            break;
                        }
                        if ($currTarget >= ($grandtotal * $payment1)) {
                            break;
                        } else {
                            $currTarget += 1;
                            array_push($headerIdArr, $payment->id);
                        }
                    }
                    Log::channel('filter_transaction')->info('BATCH 1 = '.count($headerIdArr));

                    // batch 2 (14.00 - 18.00)
                    $payments = DB::table('transaction_headers')
                        ->join('transaction_details', 'transaction_details.header_id', 'transaction_headers.id')
                        ->select('transaction_headers.id as id')
                        ->where('transaction_headers.branch_id', $branch->id)
                        ->whereRaw("LOWER(transaction_headers.foc_type) = 'no'")
                        ->where('transaction_details.package_id', '!=', null)
                        ->whereBetween('transaction_headers.created_at', [$yesterday.' 00:00:00', $yesterday.' 23:59:59']);
                    $currTarget = 0;
                    $payments2 = $payments
                        ->whereBetween('transaction_headers.created_at', [$yesterday.' 14:00:00', $yesterday.' 18:00:00'])
                        ->get();
                    $payments2 = $payments2->shuffle();
                    foreach ($payments2 as $payment) {
                        $tmpDt = TransactionDetail::where('header_id', $payment->id)->whereIn('package_id', [11, 12, 13, 14, 85, 86, 87])
                            ->get();
                        if (count($tmpDt) > 0) {
                            break;
                        }
                        if ($currTarget >= ($grandtotal * $payment2)) {
                            break;
                        } else {
                            $currTarget += 1;
                            array_push($headerIdArr, $payment->id);
                        }
                    }
                    Log::channel('filter_transaction')->info('BATCH 2 = '.count($headerIdArr));

                    // batch 3 (18.00 - end)
                    $payments = DB::table('transaction_headers')
                        ->join('transaction_details', 'transaction_details.header_id', 'transaction_headers.id')
                        ->select('transaction_headers.id as id')
                        ->where('transaction_headers.branch_id', $branch->id)
                        ->whereRaw("LOWER(transaction_headers.foc_type) = 'no'")
                        ->where('transaction_details.package_id', '!=', null)
                        ->whereBetween('transaction_headers.created_at', [$yesterday.' 00:00:00', $yesterday.' 23:59:59']);
                    $currTarget = 0;
                    $payments3 = $payments
                        ->whereBetween('transaction_headers.created_at', [$yesterday.' 18:00:00', $yesterday.' 23:59:59'])
                        ->get();
                    $payments3 = $payments3->shuffle();
                    foreach ($payments3 as $payment) {
                        $tmpDt = TransactionDetail::where('header_id', $payment->id)->whereIn('package_id', [11, 12, 13, 14, 85, 86, 87])
                            ->get();
                        if (count($tmpDt) > 0) {
                            break;
                        }
                        if ($currTarget >= ($grandtotal * $payment3)) {
                            break;
                        } else {
                            $currTarget += 1;
                            array_push($headerIdArr, $payment->id);
                        }
                    }
                    Log::channel('filter_transaction')->info('BATCH 3 = '.count($headerIdArr));
                    // dd($headerIdArr);

                    // get selected transaction
                    $transactionHeaders = TransactionHeader::with(['transaction_details', 'transaction_payments'])
                        ->whereIn('id', $headerIdArr)
                        ->where('foc_type', '!=', 'YES')
                        ->orderby('id')->get();
                    Log::channel('filter_transaction')->info('SELECTED TRANSACTION = '.count($transactionHeaders));

                    foreach ($transactionHeaders as $transactionHeader) {
                        // copy transaction_headers
                        try {
                            $nHeader = new TransactionHeader;
                            $nHeader = $transactionHeader->replicate();
                            $nHeader->setConnection('mysql_cloud');
                            $nHeader->created_at = $transactionHeader->created_at;
                            $nHeader->updated_at = $transactionHeader->updated_at;
                            $nHeader->save();

                            // copy transaction_details
                            foreach ($transactionHeader->transaction_details as $transactionDetail) {
                                if ($transactionDetail->package_id != null) {
                                    $package = Package::where('id', $transactionDetail->package_id)->first();

                                    $netPrice = $package->second_price;
                                    if ($branch->is_oura === 1) {
                                        // $netPrice = $package->price - 35000;
                                        $netPrice = $netPrice - 17500;
                                        $netPrice = ($package->price * 0.3) - 35000;
                                    }
                                    $totalAmount = ($netPrice * $transactionDetail->qty);
                                }
                            }
                            $nHeader->grand_total = $totalAmount;
                            $nHeader->discount = 0;
                            $nHeader->discount_total = 0;
                            $nHeader->amount = 0;
                            $nHeader->rounding_amount = 0;
                            $nHeader->save();
                        } catch (\Exception $ex) {
                            Log::channel('filter_transaction')->info('EX = '.$ex->getMessage());

                            continue;
                        }

                        // copy transaction_payments
                        foreach ($transactionHeader->transaction_payments as $transactionPayment) {
                            $nPayment = new TransactionPayment;
                            $nPayment = $transactionPayment->replicate();
                            $nPayment->setConnection('mysql_cloud');
                            $nPayment->created_at = $transactionPayment->created_at;
                            $nPayment->updated_at = $transactionPayment->updated_at;
                            $nPayment->header_id = $nHeader->id;
                            $nPayment->amount = $totalAmount;
                            $nPayment->save();
                        }

                        Log::channel('filter_transaction')->info($nHeader->code.' | '.$nHeader->grand_total);
                    }

                }
                // FILTERING END
            }

            return 'DONE';
        } catch (\Exception $ex) {
            return dd($ex);
        }
    }

    public function doubleBookBulk()
    {
        try {
            $branchs = Branch::where('id', 6)->get();
            // $branchs = Branch::all();
            // $yesterdayFromNow = Carbon::now()->addDays(-1);
            // $yesterday = $yesterdayFromNow->locale('id_ID')->format('Y-m-d');

            $period = CarbonPeriod::create('2023-12-01', '2023-12-01');

            // Iterate over the period
            foreach ($period as $date) {
                $yesterday = $date->format('Y-m-d');

                // FILTERING START
                // persenan kimochi
                $payment1 = 0.1;
                $payment2 = 0.1;
                $payment3 = 0.2;
                $totalTargetPerDay = 330000;
                foreach ($branchs as $branch) {
                    // persenan emperor
                    if ($branch->id == 1) {
                        $payment1 = 0.15;
                        $payment2 = 0.15;
                        $payment3 = 0.2;
                        $totalTargetPerDay = 1140000;
                    }
                    // persenan semut
                    if ($branch->id == 2) {
                        $payment1 = 0.01;
                        $payment2 = 0.01;
                        $payment3 = 0.05;
                        $totalTargetPerDay = 330000;
                    }
                    // persenan jemur - manyar
                    if ($branch->id >= 3 && $branch->id <= 4) {
                        $payment1 = 0.03;
                        $payment2 = 0.03;
                        $payment3 = 0.08;
                        $totalTargetPerDay = 532000;
                    }
                    // persenan darmo
                    if ($branch->id == 5) {
                        $payment1 = 0.03;
                        $payment2 = 0.03;
                        $payment3 = 0.08;
                        $totalTargetPerDay = 432000;
                    }
                    // persenan oura
                    if ($branch->is_oura == 1) {
                        $payment1 = 0.05;
                        $payment2 = 0.08;
                        $payment3 = 0.03;
                        $totalTargetPerDay = 60000;
                    }

                    $payments = DB::table('transaction_headers')
                        ->join('transaction_details', 'transaction_details.header_id', 'transaction_headers.id')
                        ->select('transaction_headers.id as id', 'transaction_details.subtotal as subtotal')
                        ->where('transaction_headers.branch_id', $branch->id)
                        ->whereRaw("LOWER(transaction_headers.foc_type) = 'no'")
                        ->where('transaction_details.package_id', '!=', null)
                        ->whereBetween('transaction_headers.created_at', [$yesterday.' 00:00:00', $yesterday.' 23:59:59']);
                    $grandtotal = $payments->count();
                    $totalTamu = $payments->count();
                    Log::channel('filter_transaction')->info('GRANDTOTAL = '.$grandtotal);

                    // $totalTarget = $grandtotal * 0.05;

                    $target = $totalTargetPerDay / 3;
                    $currTarget = 0;
                    $headerIdArr = [];
                    $dataArr = [];
                    // batch 1 (10.00 - 14.00)
                    $payments1 = $payments
                        ->whereBetween('transaction_headers.created_at', [$yesterday.' 10:00:00', $yesterday.' 14:00:00'])
                        ->get();
                    $payments1 = $payments1->shuffle();
                    foreach ($payments1 as $payment) {
                        $tmpDt = TransactionDetail::where('header_id', $payment->id)->whereIn('package_id', [11, 12, 13, 14, 85, 86, 87])
                            ->get();
                        if (count($tmpDt) > 0) {
                            break;
                        }
                        $taxAmount = (($payment->subtotal * 0.65)) * 0.5;

                        if ($currTarget >= ($target)) {
                            break;
                        } else {
                            $currTarget += $taxAmount;
                            array_push($headerIdArr, $payment->id, $payment->subtotal, $taxAmount);
                        }
                    }
                    Log::channel('filter_transaction')->info('BATCH 1 = '.count($headerIdArr));

                    // batch 2 (14.00 - 18.00)
                    $payments = DB::table('transaction_headers')
                        ->join('transaction_details', 'transaction_details.header_id', 'transaction_headers.id')
                        ->select('transaction_headers.id as id', 'transaction_details.subtotal as subtotal')
                        ->where('transaction_headers.branch_id', $branch->id)
                        ->whereRaw("LOWER(transaction_headers.foc_type) = 'no'")
                        ->where('transaction_details.package_id', '!=', null)
                        ->whereBetween('transaction_headers.created_at', [$yesterday.' 00:00:00', $yesterday.' 23:59:59']);
                    $currTarget = 0;
                    $payments2 = $payments
                        ->whereBetween('transaction_headers.created_at', [$yesterday.' 14:00:00', $yesterday.' 18:00:00'])
                        ->get();
                    $payments2 = $payments2->shuffle();
                    foreach ($payments2 as $payment) {
                        $tmpDt = TransactionDetail::where('header_id', $payment->id)->whereIn('package_id', [11, 12, 13, 14, 85, 86, 87])
                            ->get();
                        if (count($tmpDt) > 0) {
                            break;
                        }
                        $taxAmount = (($payment->subtotal * 0.65)) * 0.5;
                        if ($currTarget >= ($target)) {
                            break;
                        } else {
                            $currTarget += $taxAmount;
                            array_push($headerIdArr, $payment->id, $payment->subtotal, $taxAmount);
                        }
                    }
                    Log::channel('filter_transaction')->info('BATCH 2 = '.count($headerIdArr));

                    // batch 3 (18.00 - end)
                    $payments = DB::table('transaction_headers')
                        ->join('transaction_details', 'transaction_details.header_id', 'transaction_headers.id')
                        ->select('transaction_headers.id as id', 'transaction_details.subtotal as subtotal')
                        ->where('transaction_headers.branch_id', $branch->id)
                        ->whereRaw("LOWER(transaction_headers.foc_type) = 'no'")
                        ->where('transaction_details.package_id', '!=', null)
                        ->whereBetween('transaction_headers.created_at', [$yesterday.' 00:00:00', $yesterday.' 23:59:59']);
                    $currTarget = 0;
                    $payments3 = $payments
                        ->whereBetween('transaction_headers.created_at', [$yesterday.' 18:00:00', $yesterday.' 23:59:59'])
                        ->get();
                    $payments3 = $payments3->shuffle();
                    foreach ($payments3 as $payment) {
                        $tmpDt = TransactionDetail::where('header_id', $payment->id)->whereIn('package_id', [11, 12, 13, 14, 85, 86, 87])
                            ->get();
                        if (count($tmpDt) > 0) {
                            break;
                        }
                        $taxAmount = (($payment->subtotal * 0.65)) * 0.5;
                        if ($currTarget >= ($target)) {
                            break;
                        } else {
                            $currTarget += $taxAmount;
                            array_push($headerIdArr, $payment->id, $payment->subtotal, $taxAmount);
                        }
                    }
                    Log::channel('filter_transaction')->info('BATCH 3 = '.count($headerIdArr));
                    // dd($headerIdArr);

                    // get selected transaction
                    $transactionHeaders = TransactionHeader::with(['transaction_details', 'transaction_payments'])
                        ->whereIn('id', $headerIdArr)
                        ->where('foc_type', '!=', 'YES')
                        ->orderby('id')->get();
                    Log::channel('filter_transaction')->info('SELECTED TRANSACTION = '.count($transactionHeaders));

                    foreach ($transactionHeaders as $transactionHeader) {
                        // copy transaction_headers
                        try {
                            $nHeader = new TransactionHeader;
                            $nHeader = $transactionHeader->replicate();
                            $nHeader->setConnection('mysql_cloud');
                            $nHeader->created_at = $transactionHeader->created_at;
                            $nHeader->updated_at = $transactionHeader->updated_at;
                            $nHeader->save();

                            // copy transaction_details
                            foreach ($transactionHeader->transaction_details as $transactionDetail) {
                                if ($transactionDetail->package_id != null) {
                                    $package = Package::where('id', $transactionDetail->package_id)->first();

                                    $netPrice = $package->second_price;
                                    if ($branch->is_oura === 1) {
                                        // $netPrice = $package->price - 35000;
                                        $netPrice = $netPrice - 17500;
                                        $netPrice = ($package->price * 0.3) - 35000;
                                    }
                                    $totalAmount = ($netPrice * $transactionDetail->qty);
                                }
                            }
                            $nHeader->grand_total = $totalAmount;
                            $nHeader->discount = 0;
                            $nHeader->discount_total = 0;
                            $nHeader->amount = 0;
                            $nHeader->rounding_amount = 0;
                            $nHeader->save();
                        } catch (\Exception $ex) {
                            Log::channel('filter_transaction')->info('EX = '.$ex->getMessage());

                            continue;
                        }

                        // copy transaction_payments
                        foreach ($transactionHeader->transaction_payments as $transactionPayment) {
                            $nPayment = new TransactionPayment;
                            $nPayment = $transactionPayment->replicate();
                            $nPayment->setConnection('mysql_cloud');
                            $nPayment->created_at = $transactionPayment->created_at;
                            $nPayment->updated_at = $transactionPayment->updated_at;
                            $nPayment->header_id = $nHeader->id;
                            $nPayment->amount = $totalAmount;
                            $nPayment->save();
                        }

                        Log::channel('filter_transaction')->info($nHeader->code.' | '.$nHeader->grand_total);
                    }

                }
                // FILTERING END
            }

            return 'DONE';
        } catch (\Exception $ex) {
            return dd($ex);
        }
    }

    public function generateOuraPartnerCommission(Request $request)
    {
        try {
            ini_set('memory_limit', '512M');
            ini_set('max_execution_time', '600');

            $partnerId = 0;
            if ($request->exists('partner_id')) {
                $partnerId = intval($request->get('partner_id'));
            }

            $branchId = intval($request->get('branch_id'));

            // $extendItemIds = DB::table('items')
            //     ->where('branch_id', '=', $request->get('branch_id'))
            //     ->where('status_id', '=', 1)
            //     ->where('is_addon_partner_commission', '=', 1)
            //     ->pluck('id')
            //     ->toArray();

            $trxPackages = DB::table('transaction_details')
                ->join('transaction_headers', 'transaction_details.header_id', '=', 'transaction_headers.id')
                ->leftJoin('booking_headers', 'booking_headers.transaction_id', '=', 'transaction_headers.id')
                ->select('transaction_details.id as detail_id',
                    'transaction_details.header_id as header_id',
                    'transaction_details.package_id as package_id',
                    'transaction_details.item_id as item_id',
                    'transaction_details.qty as qty',
                    'transaction_details.price as price',
                    'transaction_headers.branch_id as branch_id',
                    'transaction_headers.employee_id as employee_id',
                    'transaction_headers.code as header_code',
                    'transaction_headers.created_at as header_created_at',
                    'booking_headers.type as online_type')
                ->where('transaction_headers.branch_id', '=', $branchId)
                ->where('transaction_headers.status_id', '=', 9)
                ->whereBetween('transaction_headers.created_at', [$request->get('start'), $request->get('end')])
                ->get();

            foreach ($trxPackages as $trxPackage) {
                $branchId = $trxPackage->branch_id;
                if (! empty($trxPackage->package_id)) {
                    $package = Package::where('id', $trxPackage->package_id)->first();
                    if ($package->partner_count > 0) {
                        $partners = TransactionDetailPartner::where('transaction_detail_id', $trxPackage->detail_id)
                            ->get();

                        foreach ($partners as $tmp) {
                            if ($partnerId > 0 && $partnerId !== $tmp->partner_id) {
                                continue;
                            }

                            $partner = Partner::with('partner_level')->where('id', $tmp->partner_id)->first();
                            $partnerType = $partner->type;

                            $commissionAdd = 0;
                            $commissionPercentage = 0;
                            $commissionFlat = 0;

                            if ($package->partner_count > 1) {
                                $packagePrice = $trxPackage->price / $package->partner_count;
                            } else {
                                $packagePrice = $trxPackage->price;
                            }

                            // Check for treatment commission
                            if (! empty($trxPackage->online_type) &&
                                $trxPackage->online_type === 'home_service') {
                                if ($branchId == 6) {
                                    $partnerType = 'level 3';
                                } elseif ($branchId == 7) {
                                    $partnerType = 'level HS';
                                }
                            }

                            $partnerCommissionLevel = CommissionPartnerLevelPackage::where('branch_id', '=', $branchId)
                                ->where('partner_level', '=', $partnerType)
                                ->where('package_id', '=', $trxPackage->package_id)
                                ->where('status_id', '=', 1)
                                ->first();

                            if (empty($partnerCommissionLevel)) {
                                continue;
                            }

                            if ($commissionPercentage > 0) {
                                $commissionPercentage = $partner->commission_percentage;

                                $commissionAdd = ($commissionPercentage / 100) * $packagePrice;
                            } elseif ($partnerCommissionLevel->commission_flat > 0) {
                                $commissionFlat = $partnerCommissionLevel->commission_flat;
                                $commissionAdd = $commissionFlat;
                            }

                            if ($commissionAdd > 0) {
                                $pCHistory = new CommissionPartnerHistory;
                                $pCHistory->branch_id = $request->get('branch_id');
                                $pCHistory->partner_id = $partner->id;
                                $pCHistory->transaction_id = $trxPackage->header_id;
                                $pCHistory->transaction_code = $trxPackage->header_code;
                                $pCHistory->transaction_date = $trxPackage->header_created_at;
                                $pCHistory->commission_percentage = $commissionPercentage;
                                $pCHistory->commission_flat = $commissionFlat;
                                $pCHistory->partner_name = $partner->name;
                                $pCHistory->partner_number = $partner->number;
                                $pCHistory->partner_type = $partner->type;
                                $pCHistory->partner_alias = $partner->alias;
                                $pCHistory->package_id = $package->id;
                                $pCHistory->package_name = $package->name;
                                $pCHistory->package_code = $package->code;
                                $pCHistory->commission_add = $commissionAdd;
                                $pCHistory->commission_result = $commissionAdd;
                                $pCHistory->udf_01 = $trxPackage->online_type ?? '';
                                $pCHistory->save();

                                echo 'COMMISSION CREATED id: '.$pCHistory->id.
                                    ' partner_id: '.$partner->id.
                                    ' trx_code: '.$trxPackage->header_code.
                                    '<br/>';
                            }
                        }
                    }
                }

                // if(!empty($trxPackage->item_id) && in_array($trxPackage->item_id, $extendItemIds)){
                //     $item = DB::table('items')
                //         ->where('id', '=', $trxPackage->item_id)
                //         ->first();

                //     // Check for extend treatment commission
                //     $commissionAdd = 0;
                //     if($partner->commission_percentage > 0){
                //         $commissionPercentage = $partner->commission_percentage;
                //         $commissionAdd = ($partner->commission_percentage / 100) * $item->price;
                //     }

                //     if($commissionAdd > 0){
                //         $pCHistory = new CommissionPartnerHistory();
                //         $pCHistory->branch_id = $request->get('branch_id');
                //         $pCHistory->partner_id = $partner->id;
                //         $pCHistory->partner_name = $partner->name;
                //         $pCHistory->partner_number = $partner->number;
                //         $pCHistory->partner_type = $partner->type;
                //         $pCHistory->partner_alias = $partner->alias;
                //         $pCHistory->transaction_id = $trxPackage->header_id;
                //         $pCHistory->transaction_code = $trxPackage->header_code;
                //         $pCHistory->transaction_date = $trxPackage->header_created_at;
                //         $pCHistory->commission_percentage = $commissionPercentage;
                //         $pCHistory->item_id = $item->id;
                //         $pCHistory->item_name = $item->name;
                //         $pCHistory->item_code = $item->code;
                //         $pCHistory->commission_add = $commissionAdd;
                //         $pCHistory->commission_result = $commissionAdd;
                //         $pCHistory->save();
                //     }
                // }
            }

            return 'SUCCESS';
        } catch (\Exception $ex) {
            return dd($ex);
        }
    }

    public function fixOnlinePartnerDetails()
    {
        try {
            $bookingHeaders = DB::table('booking_headers')
                ->where('status_id', '=', 9)
                ->get();

            foreach ($bookingHeaders as $bookingHeader) {
                $details = DB::table('booking_details')
                    ->where('header_id', '=', $bookingHeader->id)
                    ->whereNotNull('package_id')
                    ->get();

                foreach ($details as $detail) {
                    $detailPartners = DB::table('booking_detail_partners')
                        ->where('detail_id', '=', $detail->id)
                        ->get();

                    foreach ($detailPartners as $detailPartner) {
                        $trxDetails = DB::table('transaction_details')
                            ->where('header_id', '=', $bookingHeader->transaction_id)
                            ->where('package_id', '=', $detail->package_id)
                            ->get();

                        if (! empty($trxDetails)) {
                            $isGenerated = false;
                            foreach ($trxDetails as $trxDetail) {
                                if ($isGenerated) {
                                    continue;
                                }
                                if (! DB::table('transaction_detail_partners')
                                    ->where('transaction_detail_id', '=', $trxDetail->id)
                                    ->exists()) {

                                    $newTrxDetailPartner = new TransactionDetailPartner;
                                    $newTrxDetailPartner->transaction_detail_id = $trxDetail->id;
                                    $newTrxDetailPartner->partner_id = $detailPartner->partner_id;
                                    $newTrxDetailPartner->is_main = 1;
                                    $newTrxDetailPartner->save();

                                    $isGenerated = true;
                                }
                            }
                        }
                    }
                }
            }

            return 'SUCCESS';
        } catch (\Exception $ex) {
            return dd($ex);
        }
    }

    public function fixOuraPartnerCommission($branchId)
    {
        try {
            $commissionPartnerHistories = CommissionPartnerHistory::where('branch_id', '=', $branchId)
                ->whereBetween('transaction_date', ['2023-08-26 00:00:00', '2023-09-27 19:00:00'])
                ->whereNotNull('item_id')
                ->get();

            foreach ($commissionPartnerHistories as $pCHistory) {
                $partner = DB::table('partners')
                    ->where('id', '=', $pCHistory->partner_id)
                    ->first();

                $pCHistory->partner_name = $partner->name;
                $pCHistory->partner_number = $partner->number;
                $pCHistory->partner_type = $partner->type;
                $pCHistory->partner_alias = $partner->alias;
                $pCHistory->package_name = $pCHistory->item_name;
                $pCHistory->save();
            }

            return 'SUCCESS';
        } catch (\Exception $ex) {
            return dd($ex);
        }
    }

    public function fixOuraStuck($branchId)
    {
        try {
            $startTime = Carbon::today('Asia/Jakarta')->subDays(5);
            $endTime = Carbon::today('Asia/Jakarta');

            $trxDetails = DB::table('transaction_details')
                ->join('transaction_headers', 'transaction_details.header_id', '=', 'transaction_headers.id')
                ->select('transaction_details.id as id',
                    'transaction_headers.code as header_code',
                    'transaction_headers.status_id as header_status_id',
                    'transaction_details.status_id as status_id')
                ->where('transaction_headers.branch_id', '=', $branchId)
                ->where('transaction_headers.status_id', '=', 9)
                ->where('transaction_details.status_id', '=', 8)
                ->where('transaction_headers.created_at', '>', $startTime->toDateTimeString())
                ->where('transaction_headers.created_at', '<', $endTime->toDateTimeString())
                ->get();

            foreach ($trxDetails as $trxDetail) {
                echo $trxDetail->header_code.'</br>';
            }

            return 'SUCCESS';
        } catch (\Exception $ex) {
            return dd($ex);
        }
    }

    public function generateOuraExtendCommission($trxId, $partnerId)
    {
        try {
            $partner = Partner::find($partnerId);

            $trxHeader = TransactionHeader::with(['transaction_details', 'transaction_details.item'])->find($trxId);
            foreach ($trxHeader->transaction_details as $detail) {
                if (str_contains($detail->item->name, 'EXTEND')) {
                    $commissionAdd = ($detail->item->price / 100) * $partner->commission_percentage;

                    $pCHistory = new CommissionPartnerHistory;
                    $pCHistory->branch_id = $trxHeader->branch_id;
                    $pCHistory->partner_id = $partner->id;
                    $pCHistory->transaction_id = $trxHeader->id;
                    $pCHistory->transaction_code = $trxHeader->code;
                    $pCHistory->transaction_date = $trxHeader->created_at;
                    $pCHistory->commission_percentage = $partner->commission_percentage;
                    $pCHistory->partner_name = $partner->name;
                    $pCHistory->partner_number = $partner->number;
                    $pCHistory->partner_type = $partner->type;
                    $pCHistory->partner_alias = $partner->alias;
                    $pCHistory->package_name = $detail->item->name;
                    $pCHistory->item_id = $detail->item_id;
                    $pCHistory->item_name = $detail->item->name;
                    $pCHistory->item_code = $detail->item->code;
                    $pCHistory->commission_add = $commissionAdd;
                    $pCHistory->commission_result = $commissionAdd;
                    $pCHistory->save();
                }
            }

            return 'SUCCESS';
        } catch (\Exception $ex) {
            return dd($ex);
        }
    }

    public function generateSinglePartnerCommission($trxId)
    {
        try {
            $trxHeader = TransactionHeader::with(['transaction_details', 'transaction_details.item'])->find($trxId);
            $branch = Branch::find($trxHeader->branch_id);

            $isHomeService = false;
            if ($trxHeader->branch->is_oura == 1) {
                // Check for home service
                $bookingHeader = BookingHeader::where('transaction_id', '=', $trxHeader->id)
                    ->where('type', '=', 'home_service')
                    ->first();
                $isHomeService = ! empty($bookingHeader);
            }

            $treatmentCount = 0;
            foreach ($trxHeader->transaction_details as $detail) {
                if (! empty($detail->package_id)) {
                    $package = Package::where('id', $detail->package_id)->first();
                    $detailPartners = TransactionDetailPartner::where('transaction_detail_id', $detail->id)->get();

                    foreach ($detailPartners as $detailPartner) {
                        // Check exists
                        if (DB::table('commission_partner_histories')
                            ->where('transaction_id', '=', $trxHeader->header_id)
                            ->where('package_id', '=', $detail->package_id)
                            ->where('partner_id', '=', $detailPartner->partner_id)
                            ->exists()) {
                            continue;
                        }

                        $partner = Partner::find($detailPartner->partner_id);

                        $commissionPercentage = 0;
                        $commissionFlat = 0;
                        if ($branch->is_oura == 0) {
                            $partnerCommissionLevel = CommissionPartnerLevelPackage::where('branch_id', $trxHeader->branch_id)
                                ->where('package_id', $package->id)
                                ->where('partner_level', $partner->partner_level->name)
                                ->where('status_id', '=', 1)
                                ->first();

                            if (empty($partnerCommissionLevel)) {
                                continue;
                            }

                            $commissionAdd = 0;
                            $commissionPercentage = 0;
                            $commissionFlat = 0;

                            if ($package->partner_count > 1) {
                                $packagePrice = $detail->price / $package->partner_count;
                            } else {
                                $packagePrice = $detail->price;
                            }

                            // Check for Oura treatment commission
                            if ($partnerCommissionLevel->commission_percentage > 0) {
                                $commissionPercentage = $partnerCommissionLevel->commission_percentage;

                                $commissionAdd = ($commissionPercentage / 100) * $packagePrice;
                            } elseif ($partnerCommissionLevel->commission_flat > 0) {
                                $commissionFlat = $partnerCommissionLevel->commission_flat;
                                $commissionAdd = $commissionFlat;
                            }

                            // Check extend
                            if ($detail->extension_amount > 0 && $partnerCommissionLevel->extend_flat > 0) {
                                if ($detailPartners->count() > 1) {
                                    if ($detailPartner->is_extend == 1) {
                                        $commissionAdd += $partnerCommissionLevel->extend_flat;
                                        $commissionFlat += $partnerCommissionLevel->extend_flat;
                                    }
                                } else {
                                    $commissionAdd += ($detail->extension_times * $partnerCommissionLevel->extend_flat);
                                    $commissionFlat += ($detail->extension_times * $partnerCommissionLevel->extend_flat);
                                }
                            }
                        } else {
                            $partnerCommissionLevel = CommissionPartnerLevelPackage::where('branch_id', '=', $trxHeader->branch_id)
                                ->where('partner_level', '=', $partner->type)
                                ->where('package_id', '=', $package->id)
                                ->where('status_id', '=', 1)
                                ->first();

                            if (empty($partnerCommissionLevel)) {
                                continue;
                            }

                            $commissionAdd = 0;
                            $commissionPercentage = 0;
                            $commissionFlat = 0;

                            if ($package->partner_count > 1) {
                                $packagePrice = $detail->price / $package->partner_count;
                            } else {
                                $packagePrice = $detail->price;
                            }

                            // Check for Oura treatment commission
                            if ($partnerCommissionLevel->commission_percentage > 0) {
                                $commissionPercentage = $partnerCommissionLevel->commission_percentage;

                                $commissionAdd = ($commissionPercentage / 100) * $packagePrice;
                            } elseif ($partnerCommissionLevel->commission_flat > 0) {
                                $commissionFlat = $partnerCommissionLevel->commission_flat;
                                $commissionAdd = $commissionFlat;
                            }
                        }

                        if ($commissionAdd > 0) {
                            $pCHistory = new CommissionPartnerHistory;
                            $pCHistory->branch_id = $trxHeader->branch_id;
                            $pCHistory->partner_id = $partner->id;
                            $pCHistory->transaction_id = $trxHeader->id;
                            $pCHistory->transaction_code = $trxHeader->code;
                            $pCHistory->transaction_date = $trxHeader->created_at;
                            $pCHistory->commission_percentage = $commissionPercentage;
                            $pCHistory->commission_flat = $commissionFlat;
                            $pCHistory->partner_name = $partner->name;
                            $pCHistory->partner_number = $partner->number;
                            $pCHistory->partner_type = $partner->type;
                            $pCHistory->partner_alias = $partner->alias;
                            $pCHistory->package_id = $package->id;
                            $pCHistory->package_name = $package->name;
                            $pCHistory->package_code = $package->code;
                            $pCHistory->commission_add = $commissionAdd;
                            $pCHistory->commission_result = $commissionAdd;
                            $pCHistory->udf_01 = $isHomeService ? 'home_service' : '';
                            $pCHistory->save();
                        }
                    }

                    $treatmentCount++;
                }
            }

            return 'SUCCESS';
        } catch (\Exception $ex) {
            return dd($ex);
        }
    }

    public function fixOuraPointHistories()
    {
        $pointHistories = DB::table('user_point_histories')
            ->get();

        foreach ($pointHistories as $history) {
            $branchId = DB::table('booking_headers')
                ->where('code', '=', $history->reference_2)
                ->value('branch_id');

            $branch = DB::table('branches')->where('id', '=', $branchId)->first();

            DB::table('user_point_histories')
                ->where('id', '=', $history->id)
                ->update([
                    'branch_id' => $branchId,
                    'branch_name' => $branch->name,
                ]);
        }

        return 'FINISHED';
    }

    public function disableCoupon120()
    {
        $details = DB::table('transaction_details')
            ->join('transaction_headers', 'transaction_details.header_id', '=', 'transaction_headers.id')
            ->join('packages', 'transaction_details.package_id', '=', 'packages.id')
            ->select('transaction_details.id as id',
                'transaction_details.package_id as package_id',
                'transaction_headers.code as header_code',
                'transaction_headers.extra_voucher_code as coupon_code',
                'packages.name as package_name',
                'packages.duration as package_duration')
            ->whereIn('transaction_headers.branch_id', [6, 7])
            ->where('transaction_headers.status_id', '=', 9)
            ->where('transaction_headers.created_at', '>', '2023-10-01 00:00:00')
            ->whereNotNull('transaction_details.package_id')
            ->whereNotNull('transaction_headers.extra_voucher_code')
            ->where('packages.duration', '=', 120)
            ->get();

        $counter = 0;
        foreach ($details as $detail) {
            DB::table('coupon_lists')
                ->where('code', '=', $detail->coupon_code)
                ->update([
                    'is_used' => 1,
                    'updated_at' => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                    'updated_by' => 56,
                ]);

            $counter++;
        }

        return $counter.' UPDATED!';
    }

    public function checkDiscountJournal($branchId)
    {
        try {
            $trxHeaders = DB::table('transaction_headers')
                ->where('branch_id', '=', $branchId)
                ->where('status_id', '=', 9)
                ->whereBetween('created_at', ['2023-12-01 01::00:00', '2024-01-01 01:00:00'])
                ->where('discount', '>', 0)
                ->get();

            // dd($trxHeaders);

            foreach ($trxHeaders as $header) {
                $jDetail = DB::table('journal_details')
                    ->where('id', '>', 1000000)
                    ->where('branch_id', '=', $branchId)
                    ->where('reference_code', '=', $header->code)
                    ->where('account_code', '=', '4-40100')
                    ->first();

                if (empty($jDetail)) {
                    echo 'JOURNAL NOT FOUND code: '.$header->code.' discount: '.$header->discount.'<br/>';
                } else {
                    if ($jDetail->original_amount != $header->discount) {
                        echo 'NOT MATCH jAmount: '.intval($jDetail->original_amount).' != hAmount: '.intval($header->discount).'<br/>';
                    }
                }
            }
        } catch (\Exception $ex) {
            return dd($ex);
        }
    }

    public function generateNewUserJournal($userId)
    {
        try {
            $user = User::find($userId);

            // Create journal
            $jHeader = new JournalHeader;
            $jHeader->transaction_code = AutoNumberGenerator::generateAutonumberTrx('JN');
            $jHeader->transaction_type = 'GJV';
            $jHeader->transaction_date = Carbon::now('Asia/Jakarta')->toDateTimeString();
            $jHeader->created_by = 54;
            $jHeader->branch_id = 6;
            $jHeader->notes = 'Journal Registrasi User Baru '.$user->phone;
            $jHeader->reference_id = $user->id;
            $jHeader->status_id = 1;
            $jHeader->save();

            $coa = Coa::where('branch_id', '=', 6)
                ->where('name', '=', 'DISKON POINT MEMBER')
                ->first();

            JournalUtilities::insertRecord($jHeader,
                6, $coa, 'DEBET', 25000, 'Point user baru', $user->phone);

            $coa = Coa::where('branch_id', '=', 6)
                ->where('name', '=', 'HUTANG POINT MEMBER')
                ->first();

            JournalUtilities::insertRecord($jHeader,
                6, $coa, 'KREDIT', 25000, 'Point user baru', $user->phone);

            return 'DONE';
        } catch (\Exception $ex) {
            return dd($ex);
        }
    }

    public function fixPartnerLevel($branchId)
    {
        try {
            $partners = Partner::where('branch_id', '=', $branchId)
                ->whereNotIn('type', ['basic', 'non-basic'])
                ->get();

            foreach ($partners as $partner) {
                $partnerLevel = DB::table('partner_levels')
                    ->where('branch_id', '=', $branchId)
                    ->where('name', '=', $partner->type)
                    ->first();

                $partner->level_id = $partnerLevel->id;
                $partner->save();
            }

            return 'SUCCESS';
        } catch (\Exception $ex) {
            return dd($ex);
        }
    }

    public function generateOuraItemCommission(Request $request)
    {
        try {
            ini_set('memory_limit', '512M');
            ini_set('max_execution_time', '600');

            $branchId = intval($request->get('branch_id'));

            $partnerId = 0;
            if ($request->exists('partner_id')) {
                $partnerId = intval($request->get('partner_id'));
            }

            $itemCommissions = CommissionPartnerLevelItem::where('branch_id', '=', $branchId)
                ->pluck('item_id')
                ->unique()
                ->toArray();

            $itemIds = [];
            foreach ($itemCommissions as $item) {
                $itemIds[] = $item;
            }

            $trxItems = DB::table('transaction_details')
                ->join('transaction_headers', 'transaction_details.header_id', '=', 'transaction_headers.id')
                ->leftJoin('booking_headers', 'booking_headers.transaction_id', '=', 'transaction_headers.id')
                ->select('transaction_details.id as detail_id',
                    'transaction_details.header_id as header_id',
                    'transaction_details.package_id as package_id',
                    'transaction_details.item_id as item_id',
                    'transaction_details.qty as qty',
                    'transaction_details.price as price',
                    'transaction_details.partner_id as partner_id',
                    'transaction_details.partner_level as partner_level',
                    'transaction_headers.branch_id as branch_id',
                    'transaction_headers.employee_id as employee_id',
                    'transaction_headers.code as header_code',
                    'transaction_headers.created_at as header_created_at',
                    'booking_headers.type as online_type')
                ->where('transaction_headers.branch_id', '=', $branchId)
                ->where('transaction_headers.status_id', '=', 9)
                ->whereIn('transaction_details.item_id', $itemIds)
                ->whereNotNull('transaction_details.partner_id');

            if ($partnerId > 0) {
                $trxItems = $trxItems->where('transaction_details.partner_id', '=', $partnerId);
            }

            $trxItems = $trxItems->whereBetween('transaction_headers.created_at', [$request->get('start'), $request->get('end')])
                ->get();

            foreach ($trxItems as $trxItem) {
                // Check exists
                $existingHistory = DB::table('commission_partner_histories')
                    ->where('transaction_id', '=', $trxItem->header_id)
                    ->where('item_id', '=', $trxItem->item_id)
                    ->where('partner_id', '=', $trxItem->partner_id)
                    ->where('transaction_date', '>=', $request->get('start'))
                    ->first();
                if (! empty($existingHistory)) {
                    DB::table('commission_partner_histories')
                        ->where('id', '=', $existingHistory->id)
                        ->delete();
                }

                $partnerDb = Partner::find($trxItem->partner_id);

                $partnerType = $partnerDb->type;
                if (! empty($trxItem->online_type) &&
                    $trxItem->online_type === 'home_service') {
                    if ($branchId == 6) {
                        $partnerType = 'level 3';
                    } elseif ($branchId == 7) {
                        $partnerType = 'level HS';
                    }
                }

                $itemCommission = DB::table('commission_partner_level_items')
                    ->where('branch_id', '=', $branchId)
                    ->where('partner_level', '=', $partnerType)
                    ->where('item_id', '=', $trxItem->item_id)
                    ->where('status_id', '=', 1)
                    ->first();

                if (! empty($itemCommission)) {
                    $commissionAdd = 0;
                    $commissionPercentage = 0;
                    $commissionFlat = 0;

                    // Check for item commission value
                    if ($itemCommission->commission_percentage > 0) {
                        $commissionPercentage = $itemCommission->commission_percentage;
                        $commissionAdd = ($commissionPercentage / 100) * $trxItem->price;
                    } elseif ($itemCommission->commission_flat > 0) {
                        $commissionFlat = $itemCommission->commission_flat;
                        $commissionAdd = $commissionFlat;
                    }

                    if ($commissionAdd > 0) {

                        $pCHistory = new CommissionPartnerHistory;
                        $pCHistory->branch_id = $branchId;
                        $pCHistory->partner_id = $trxItem->partner_id;

                        $partner = Partner::find($trxItem->partner_id);
                        $pCHistory->partner_name = $partner->name;
                        $pCHistory->partner_number = $partner->number;
                        $pCHistory->partner_type = $partner->type;
                        $pCHistory->partner_alias = $partner->alias;

                        $pCHistory->transaction_id = $trxItem->header_id;
                        $pCHistory->transaction_code = $trxItem->header_code;
                        $pCHistory->transaction_date = $trxItem->header_created_at;

                        $item = Item::find($trxItem->item_id);
                        $pCHistory->item_id = $item->id;
                        $pCHistory->item_code = $item->code;
                        $pCHistory->item_name = $item->name;
                        $pCHistory->udf_01 = $trxItem->online_type ?? '';

                        if ($item->is_addon) {
                            $pCHistory->udf_02 = 'addon';
                        }

                        $pCHistory->commission_percentage = $commissionPercentage;
                        $pCHistory->commission_flat = $commissionFlat;
                        $pCHistory->commission_add = $commissionAdd;
                        $pCHistory->commission_result = $commissionAdd;

                        $pCHistory->save();

                        echo 'COMMISSION CREATED id: '.$pCHistory->id.
                            ' partner_id: '.$partner->id.
                            ' trx_code: '.$trxItem->header_code.
                            '<br/>';
                    }
                }
            }

            return 'SUCCESS';
        } catch (\Exception $ex) {
            return dd($ex);
        }
    }

    public function testUnique()
    {
        $itemCommissions = CommissionPartnerLevelItem::where('branch_id', '=', 6)
            ->pluck('item_id')
            ->unique();
        dd($itemCommissions);
    }

    public function activateOuraUser($id)
    {
        try {
            $user = User::find($id);
            if ($user->status_id != 2) {
                return 'INVALID STATUS';
            }

            $user->status_id = 1;
            $user->active_at = Carbon::now('Asia/Jakarta')->toDateTimeString();
            $user->membership_id = 1;
            $user->membership_status = 'ACTIVE';
            $user->membership_start_at = Carbon::today('Asia/Jakarta')->toDateTimeString();
            $user->save();

            // Check bonus point
            $newUserBonusPoint = DB::table('configurations')
                ->where('key', '=', 'oura_new_user_bonus_point')
                ->value('value_int');

            $newUserBonusPoint = intval($newUserBonusPoint);
            if ($newUserBonusPoint > 0) {
                Utilities::updateUserPointBalance($user->id,
                    'add',
                    $newUserBonusPoint);
                Utilities::saveUserPointHistory($user->id,
                    6,
                    'new_user',
                    'add',
                    $newUserBonusPoint,
                    '',
                    '',
                    null);

                // Create journal
                $jHeader = new JournalHeader;
                $jHeader->transaction_code = AutoNumberGenerator::generateAutonumberTrx('JN');
                $jHeader->transaction_type = 'GJV';
                $jHeader->transaction_date = Carbon::now('Asia/Jakarta')->toDateTimeString();
                $jHeader->created_by = 54;
                $jHeader->branch_id = 6;
                $jHeader->notes = 'Journal Registrasi User Baru '.$user->phone;
                $jHeader->reference_id = $user->id;
                $jHeader->status_id = 1;
                $jHeader->save();

                $coa = Coa::where('branch_id', '=', 6)
                    ->where('name', '=', 'DISKON POINT MEMBER')
                    ->first();

                JournalUtilities::insertRecord($jHeader,
                    6, $coa, 'DEBET', $newUserBonusPoint, 'Point user baru', $user->phone);

                $coa = Coa::where('branch_id', '=', 6)
                    ->where('name', '=', 'HUTANG POINT MEMBER')
                    ->first();

                JournalUtilities::insertRecord($jHeader,
                    6, $coa, 'KREDIT', $newUserBonusPoint, 'Point user baru', $user->phone);
            }

            return 'USER ACTIVATED!';
        } catch (\Exception $ex) {
            return dd($ex);
        }
    }

    public function addTs(Request $request)
    {
        try {
            $bookId = $request->get('book_id');
            $tsId = $request->get('ts_id');

            $bookingHeader = DB::table('booking_headers')
                ->where('id', '=', $bookId)
                ->first();

            if (empty($bookingHeader)) {
                return redirect()->back();
            }

            $tsItem = Item::find($tsId);

            // Add to booking details
            $newItemDetail = new BookingDetail;
            $newItemDetail->header_id = $bookId;
            $newItemDetail->item_id = $tsItem->id;
            $newItemDetail->item_name = $tsItem->name;
            $newItemDetail->item_code = $tsItem->code;
            $newItemDetail->qty = 1;
            $newItemDetail->price = $tsItem->price;
            $newItemDetail->discount_amount = 0;
            $newItemDetail->discount_percentage = 0;
            $newItemDetail->subtotal = $tsItem->price;
            $newItemDetail->status_id = 1;
            $newItemDetail->save();

            // Add to transaction details
            $newDetail = new TransactionDetail;
            $newDetail->header_id = $bookingHeader->transaction_id;
            $newDetail->item_id = $tsItem->id;
            $newDetail->qty = 1;
            $newDetail->price = $tsItem->price;
            $newDetail->subtotal = $tsItem->price;
            $newDetail->discount_amount = 0;
            $newDetail->discount_percentage = 0;
            $newDetail->status_id = 1;
            $newDetail->save();

            return 'SUCCESS';
        } catch (\Exception $ex) {
            return dd($ex);
        }
    }

    public function fixQrisJournal(Request $request)
    {
        try {
            $payments = DB::table('transaction_payments')
                ->join('transaction_headers', 'transaction_payments.header_id', '=', 'transaction_headers.id')
                ->select('transaction_headers.id as trx_id',
                    'transaction_headers.code as trx_code',
                    'transaction_headers.created_at as trx_created_at')
                ->where('transaction_payments.payment_type_id', '=', 51)
                ->where('transaction_payments.amount', '>', 0)
                ->where('transaction_headers.status_id', '=', 9)
                ->where('transaction_headers.created_at', '>', '2023-09-26 00:00:00')
                ->get();

            foreach ($payments as $payment) {
                $jHeader = DB::table('journal_headers')
                    ->where('notes', 'LIKE', '%'.$payment->trx_code.'%')
                    ->where('transaction_date', '>', '2023-09-26 00:00:00')
                    ->first();

                if (! empty($jHeader)) {
                    $jDetail = JournalDetail::where('header_id', '=', $jHeader->id)
                        ->where('id', '>', 776339)
                        ->where('account_code', '=', '1-10002')
                        ->first();

                    if (! empty($jDetail)) {
                        $jDetail->account_id = 2694;
                        $jDetail->account_code = '1-10003';
                        $jDetail->account_name = 'Kas - Mandiri OP';
                        $jDetail->save();
                    } else {
                        echo 'JOURNAL DETAIL NOT FOUND '.$payment->trx_code.' '.$payment->trx_created_at.'<br/>';
                    }
                } else {
                    echo 'JOURNAL NOT FOUND '.$payment->trx_code.' '.$payment->trx_created_at.'<br/>';
                }
            }

            return 'SUCCESS';
        } catch (\Exception $ex) {
            return dd($ex);
        }
    }

    public function exportInventoryHistory(Request $request)
    {
        try {
            $branchId = intval($request->get('branch_id'));

            $items = DB::table('items')
                ->where('branch_id', '=', $branchId)
                ->orderBy('code')
                ->get();

            $histories = [];
            foreach ($items as $item) {
                $lastInventoryHistory = DB::table('inventory_histories')
                    ->where('branch_id', '=', $branchId)
                    ->where('item_id', '=', $item->id)
                    ->where('created_at', '<', $request->get('last_date'))
                    ->orderByDesc('created_at')
                    ->first();

                $lastQty = 0;
                $lastPoPrice = 0;
                if (! empty($lastInventoryHistory)) {
                    $lastQty = $lastInventoryHistory->qty_result;

                    $lastPoDetail = DB::table('purchase_order_details')
                        ->join('purchase_order_headers', 'purchase_order_details.header_id', '=', 'purchase_order_headers.id')
                        ->select('purchase_order_details.item_id as item_id',
                            'purchase_order_details.price as price')
                        ->where('purchase_order_headers.branch_id', '=', $branchId)
                        ->where('purchase_order_details.item_id', '=', $item->id)
                        ->where('purchase_order_headers.created_at', '<', $request->get('last_date'))
                        ->orderByDesc('purchase_order_headers.created_at')
                        ->first();

                    $lastPoPrice = ! empty($lastPoDetail) ? $lastPoDetail->price : 0;
                }

                $histories[] = [
                    'item_code' => $item->code,
                    'item_name' => $item->name,
                    'last_qty' => $lastQty,
                    'last_po_price' => $lastPoPrice,
                ];
            }

            $date = Carbon::parse($request->get('last_date'));
            $filename = 'INVENTORY_STOCK_'.$date->year.$date->month.$date->day.'.xlsx';

            $export = (new FastExcel($histories))->download($filename, function ($history) {
                return [
                    'KODE BARANG' => $history['item_code'],
                    'NAMA BARANG' => $history['item_name'],
                    'QTY' => $history['last_qty'],
                    'HARGA' => $history['last_po_price'],
                ];
            });

            $export->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', true);
            $export->headers->set('Content-Disposition', 'attachment; '.
                'filename="'.rawurldecode($filename).'"; '.
                'filename*=UTF-8\'\''.rawurldecode($filename), true);
            $export->headers->set('Cache-Control', 'max-age=0', true);
            $export->headers->set('Pragma', 'public', true);

            return $export;
        } catch (\Exception $ex) {
            return dd($ex);
        }
    }

    public function exportInventoryHistoryV2(Request $request)
    {
        try {
            $branchId = intval($request->get('branch_id'));

            $category = $request->get('category');
            if ($category == 'PERSEDIAAN') {
                $categoryIds = [13, 14, 15, 16];
            } else {
                $categoryIds = [5, 6, 7, 8, 9, 10, 11, 12, 17, 18];
            }

            $items = DB::table('items')
                ->where('branch_id', '=', $branchId)
                ->whereIn('category_2_id', $categoryIds)
                ->orderBy('code')
                ->get();

            $histories = [];
            foreach ($items as $item) {
                $firstInventoryHistory = DB::table('inventory_histories')
                    ->where('branch_id', '=', $branchId)
                    ->where('item_id', '=', $item->id)
                    ->where('created_at', '<', $request->get('first_date'))
                    ->orderByDesc('created_at')
                    ->first();

                $firstQty = 0;
                $firstPoPrice = 0;
                if (! empty($firstInventoryHistory)) {
                    $firstQty = $firstInventoryHistory->qty_result;

                    $firstPoDetail = DB::table('purchase_order_details')
                        ->join('purchase_order_headers', 'purchase_order_details.header_id', '=', 'purchase_order_headers.id')
                        ->select('purchase_order_details.item_id as item_id',
                            'purchase_order_details.price as price')
                        ->where('purchase_order_headers.branch_id', '=', $branchId)
                        ->where('purchase_order_details.item_id', '=', $item->id)
                        ->where('purchase_order_headers.created_at', '<', $request->get('first_date'))
                        ->orderByDesc('purchase_order_headers.created_at')
                        ->first();

                    $firstPoPrice = ! empty($firstPoDetail) ? $firstPoDetail->price : 0;
                }

                $secondInventoryHistory = DB::table('inventory_histories')
                    ->where('branch_id', '=', $branchId)
                    ->where('item_id', '=', $item->id)
                    ->where('created_at', '<', $request->get('second_date'))
                    ->orderByDesc('created_at')
                    ->first();

                $secondQty = 0;
                $secondPoPrice = 0;
                if (! empty($secondInventoryHistory)) {
                    $secondQty = $secondInventoryHistory->qty_result;

                    $secondPoDetail = DB::table('purchase_order_details')
                        ->join('purchase_order_headers', 'purchase_order_details.header_id', '=', 'purchase_order_headers.id')
                        ->select('purchase_order_details.item_id as item_id',
                            'purchase_order_details.price as price')
                        ->where('purchase_order_headers.branch_id', '=', $branchId)
                        ->where('purchase_order_details.item_id', '=', $item->id)
                        ->where('purchase_order_headers.created_at', '<', $request->get('second_date'))
                        ->orderByDesc('purchase_order_headers.created_at')
                        ->first();

                    $secondPoPrice = ! empty($secondPoDetail) ? $secondPoDetail->price : 0;
                }

                $categoryName = DB::table('item_categories')
                    ->where('id', '=', $item->category_2_id)
                    ->value('name');

                $histories[] = [
                    'item_code' => $item->code,
                    'item_name' => $item->name,
                    'category_name' => $categoryName,
                    'first_qty' => $firstQty,
                    'first_po_price' => $firstPoPrice,
                    'first_total' => $firstQty > 0 ? $firstQty * $firstPoPrice : 0,
                    'second_qty' => $secondQty,
                    'second_po_price' => $secondPoPrice,
                    'second_total' => $secondQty > 0 ? $secondQty * $secondPoPrice : 0,
                ];
            }

            $firstDate = Carbon::parse($request->get('first_date'));
            $secondDate = Carbon::parse($request->get('second_date'));
            $filename = $category.'_STOCK_'.$firstDate->year.$firstDate->month.$firstDate->day.'_'.$secondDate->year.$secondDate->month.$secondDate->day.'.xlsx';

            $export = (new FastExcel($histories))->download($filename, function ($history) use ($firstDate, $secondDate) {
                $tmpFirst = $firstDate->format('d-m-Y');
                $tmpSecond = $secondDate->copy()->subDay()->format('d-m-Y');

                return [
                    'NAMA' => $history['item_name'],
                    'KATEGORI' => $history['category_name'],
                    'QTY '.$tmpFirst => $history['first_qty'],
                    'HARGA '.$tmpFirst => $history['first_po_price'],
                    'JUMLAH '.$tmpFirst => $history['first_total'],
                    'QTY '.$tmpSecond => $history['second_qty'],
                    'HARGA '.$tmpSecond => $history['second_po_price'],
                    'JUMLAH '.$tmpSecond => $history['second_total'],
                ];
            });

            $export->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', true);
            $export->headers->set('Content-Disposition', 'attachment; '.
                'filename="'.rawurldecode($filename).'"; '.
                'filename*=UTF-8\'\''.rawurldecode($filename), true);
            $export->headers->set('Cache-Control', 'max-age=0', true);
            $export->headers->set('Pragma', 'public', true);

            return $export;
        } catch (\Exception $ex) {
            return dd($ex);
        }
    }

    public function copyPointUsed()
    {
        try {
            $bookings = DB::table('booking_headers')
                ->where('status_id', '=', 9)
                ->where('point_used', '>', 0)
                ->get();

            foreach ($bookings as $booking) {
                DB::table('transaction_headers')
                    ->where('id', '=', $booking->transaction_id)
                    ->update([
                        'app_point_used' => $booking->point_used,
                    ]);
            }

            return 'SUCCESS';
        } catch (\Exception $ex) {
            return dd($ex);
        }
    }

    public function setAllPartnersToInactive(Request $request)
    {
        try {
            DB::table('partners')
                ->where('id', '>', 0)
                ->where('branch_id', '=', $request->query('branch_id'))
                ->update([
                    'is_present' => 0,
                ]);

            return 'SUCCESS';
        } catch (\Exception $ex) {
            return dd($ex);
        }
    }

    public function generateMissingSaldoAwal(Request $request)
    {
        $branchId = intval($request->query('branch_id'));
        if ($branchId <= 0) {
            return response('branch_id wajib diisi', 422);
        }

        $endMonth = null;
        if ($request->filled('end_month')) {
            try {
                $endMonth = Carbon::createFromFormat('Y-m', $request->query('end_month'), 'Asia/Jakarta')
                    ->startOfMonth();
            } catch (\Exception $ex) {
                return response('Format end_month harus Y-m', 422);
            }
        }

        $result = app(SaldoAwalGenerator::class)->generateForwardFromLatestExisting($branchId, $endMonth);
        $generatedMonths = empty($result['generated_months']) ? '-' : implode(', ', $result['generated_months']);
        $latestExistingMonth = $result['latest_existing_month'] ?? '-';
        $targetEndMonth = ($endMonth ?? Carbon::now('Asia/Jakarta')->startOfMonth())->format('Y-m');

        return implode('<br>', [
            'status: '.$result['status'],
            'branch_id: '.$branchId,
            'latest_existing_month: '.$latestExistingMonth,
            'generated_months: '.$generatedMonths,
            'target_end_month: '.$targetEndMonth,
            'message: '.$result['message'],
        ]);
    }

    public function regenerateSaldoAwal(Request $request){
        $user = Auth::guard('admin')->user();
        if (! $user) {
            return response('Unauthorized', 401);
        }

        $month = intval($request->get('month'));
        if ($month < 1 || $month > 12) {
            return response('month wajib diisi antara 1 sampai 12', 422);
        }

        $year = intval($request->get('year', Carbon::now('Asia/Jakarta')->year));
        if ($year <= 0) {
            return response('year tidak valid', 422);
        }

        $startDate = Carbon::createFromDate($year, $month, 1, 'Asia/Jakarta')->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $previousStartDate = $startDate->copy()->subMonthNoOverflow()->startOfMonth();
        $previousEndDate = $previousStartDate->copy()->endOfMonth();

        $saldoAwals = DB::table('coa_monthly_amounts')
            ->where('branch_id', '=', $user->branch_id)
            ->where('month', '=', $month)
            ->where('year', '=', $year)
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get();

        $results = [];
        DB::transaction(function () use ($saldoAwals, $previousStartDate, $previousEndDate, $user, &$results) {
            foreach($saldoAwals as $saldoAwal) {
                $data = Utilities::ledgerFunc($previousStartDate, $previousEndDate, $saldoAwal->coa_id, $user->branch_id);
                $ledgerRows = collect($data[0]['data'] ?? []);
                $lastLedger = $ledgerRows->last();
                $previousEndingBalance = $lastLedger['saldo'] ?? 0;

                DB::table('coa_monthly_amounts')
                    ->where('id', '=', $saldoAwal->id)
                    ->update([
                        'debit_add' => 0,
                        'debit_total' => 0,
                        'credit_add' => 0,
                        'credit_total' => 0,
                        'balance_total' => $previousEndingBalance,
                        'updated_at' => now(),
                    ]);

                $results[] = [
                    'coa_code' => $saldoAwal->coa_code,
                    'coa_name' => $saldoAwal->coa_name,
                    'old_balance_total' => $saldoAwal->balance_total,
                    'new_balance_total' => $previousEndingBalance,
                    'previous_month_start_date' => $previousStartDate->toDateString(),
                    'previous_month_end_date' => $previousEndDate->toDateString(),
                    'old_debit_add' => $saldoAwal->debit_add,
                    'old_debit_total' => $saldoAwal->debit_total,
                    'old_credit_add' => $saldoAwal->credit_add,
                    'old_credit_total' => $saldoAwal->credit_total,
                ];
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Saldo awal berhasil diregenerate',
            'branch_id' => $user->branch_id,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'source_start_date' => $previousStartDate->toDateString(),
            'source_end_date' => $previousEndDate->toDateString(),
            'total_coa' => count($results),
            'data' => $results,
        ]);
    }
}
