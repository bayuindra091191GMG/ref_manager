<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Libs\AutoNumberGenerator;
use App\Libs\JournalUtilities;
use App\Libs\Utilities;
use App\Models\Coa;
use App\Models\JournalHeader;
use App\Models\JournalMap;
use App\Models\Package;
use App\Models\PreweddingHeader;
use App\Models\TransactionHeader;
use App\Models\TransactionInvoice;
use App\Models\TransactionPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ScriptBayuController extends Controller
{
    public function checkPaymentJournal(Request $request){
        try{
            ini_set('memory_limit', '1024M');
            ini_set('max_execution_time', '3600');

            // $journalMaps = DB::table('journal_maps')
            //     ->where('branch_id', '=', $request->query('branch_id'))
            //     ->get();

            $transactions = DB::table('transaction_headers')
                ->where('transaction_headers.branch_id', '=', $request->query('branch_id'))
                ->where('transaction_headers.status_id', '=', 9)
                ->where('transaction_headers.created_at', '>', $request->query('start'))
                ->where('transaction_headers.created_at', '<', $request->query('end'))
                ->get();

            foreach($transactions as $trx){
                $jDetails = DB::table('journal_details')
                    ->where('reference_code', '=', $trx->code)
                    ->get();

                if($jDetails->count() < 1){
                    echo $trx->code. ' JOURNAL NOT FOUND!<br/>';
                    continue;
                }

                $trxDetails = DB::table('transaction_details')
                    ->where('header_id', '=', $trx->id)
                    ->get();

                $totalTrx = 0;
                $totalPackageTrx = 0;
                foreach($trxDetails as $dt){
                    if($dt->package_id != null){
                        $totalTrx += (($dt->price * $dt->qty) + $dt->additional_person_amount + $dt->additional_partner_amount + $dt->extension_amount);
                        $totalPackageTrx += (($dt->price * $dt->qty) + $dt->additional_person_amount + $dt->additional_partner_amount + $dt->extension_amount);
                    }
                    else{
                        $totalTrx += ($dt->price * $dt->qty);
                    }
                }

                if(strtolower($trx->foc_type) == 'foc'){
                    $journalMap = DB::table('journal_maps')
                        ->where('branch_id', $trx->branch_id)
                        ->where('transaction_type', 'POS_FOC')
                        ->first();

                    $coa = Coa::where('id', $journalMap->coa_id)->first();
                    $tmpJDetail = $jDetails->firstWhere('account_id', '=', $coa->id);
                    if(empty($tmpJDetail)){
                        echo $trx->code. ' JOURNAL FOC NOT FOUND!<br/>';
                        continue;
                    }
                    else{
                        if(intval($tmpJDetail->debit) != intval($totalTrx)){
                            echo $trx->code. ' JOURNAL FOC DIFFERENT VALUE!<br/>';
                            echo $tmpJDetail->debit. ' != '. $totalTrx. '<br/>';
                            continue;
                        }
                    }
                }
                elseif(strtolower($trx->foc_type) == 'foc_package'){
                    $journalMap = DB::table('journal_maps')->where('branch_id', $trx->branch_id)
                        ->where('transaction_type', 'POS_FOC')
                        ->first();

                    $coa = Coa::where('id', $journalMap->coa_id)->first();
                    $tmpJDetail = $jDetails->firstWhere('account_id', '=', $coa->id);
                    if(empty($tmpJDetail)){
                        echo $trx->code. ' JOURNAL FOC PACKAGE NOT FOUND!<br/>';
                        continue;
                    }
                    else{
                        if(intval($tmpJDetail->debit) != intval($totalPackageTrx)){
                            echo $trx->code. ' JOURNAL FOC PACKAGE DIFFERENT VALUE!<br/>';
                            echo $tmpJDetail->debit. ' != '. $totalPackageTrx. '<br/>';
                            continue;
                        }
                    }
                }
                else{
                    $branch = DB::table('branches')->where('id', '=', $trx->branch_id)->first();
                    $trxPayments = DB::table('transaction_payments')
                        ->where('header_id', '=', $trx->id)
                        ->get();

                    foreach($trxPayments as $payment){
                        if($branch->is_oura == 1){
                            $bookingHeader = DB::table('booking_headers')
                                ->where('transaction_id', '=', $trx->id)
                                ->first();

                            if(!empty($bookingHeader)){
                                $isApplication = true;

                                if($payment->amount > 0){
                                    $bcaPaymentCoa = Coa::where('branch_id', '=', $branch->id)
                                        ->where('code', '=', '1-10004')
                                        ->first();

                                    $tmpJDetail = $jDetails->firstWhere('account_id', '=', $bcaPaymentCoa->id);
                                    if(empty($tmpJDetail)){
                                        echo $trx->code. ' JOURNAL PAYMENT ONLINE NOT FOUND!<br/>';
                                        continue;
                                    }
                                    else{
                                        if(intval($tmpJDetail->debit) != intval($payment->amount)){
                                            echo $trx->code. ' JOURNAL PAYMENT ONLINE DIFFERENT VALUE!<br/>';
                                            echo $tmpJDetail->debit. ' != '. $payment->amount. '<br/>';
                                            continue;
                                        }
                                    }
                                }

                                continue;
                            }
                        }

                        $journalMap = DB::table('journal_maps')->where('branch_id', $trx->branch_id)
                            ->where(function($q){
                                $q->where('transaction_type', 'POS_CASH')
                                    ->orWhere('transaction_type', 'POS_NON_CASH');
                            })
                            ->where('room_type_id', 'like', '%'. $payment->payment_type_id. '%')
                            ->first();

                        if($journalMap != null){

                            $coa = Coa::where('id', $journalMap->coa_id)->first();
                            $tmpJDetail = $jDetails->firstWhere('account_id', '=', $coa->id);
                            if(empty($tmpJDetail)){
                                echo $trx->code. ' JOURNAL PAYMENT '. $payment->payment_type_code. ' NOT FOUND!<br/>';
                                continue;
                            }
                            else{
                                if($journalMap->journal_type == 'DEBET'){
                                    if(intval($tmpJDetail->debit) != intval($payment->amount)){
                                        echo $trx->code. ' JOURNAL PAYMENT '. $payment->payment_type_code. ' DIFFERENT VALUE!<br/>';
                                        echo $tmpJDetail->debit. ' != '. $payment->amount. '<br/>';
                                        continue;
                                    }
                                }
                                else{
                                    if(intval($tmpJDetail->credit) != intval($payment->amount)){
                                        echo $trx->code. ' JOURNAL PAYMENT '. $payment->payment_type_code. ' DIFFERENT VALUE!<br/>';
                                        echo $tmpJDetail->credit. ' != '. $payment->amount. '<br/>';
                                        continue;
                                    }
                                }

                            }
                        }
                    }
                }

                // echo $trx->code. ' '. $trx->created_at. ' CHECKED!<br/>';
            }

            return 'DONE CHECKING!';
        }
        catch (\Exception $ex){
            return dd($ex);
        }
    }

    public function checkNotBalancedJournal(Request $request){
        try{
            $jHeaders = DB::table('journal_headers')
                ->where('branch_id', '=', $request->query('branch_id'))
                ->where('transaction_date', '>', $request->query('start'))
                ->where('transaction_date', '<', $request->query('end'))
                ->get();

            $result = [];
            foreach ($jHeaders as $header){
                $query = "select hd.transaction_code, sum(dt.debit) as debit, sum(dt.credit) as credit, sum(dt.debit) - sum(dt.credit) as result from hokicuan_db.journal_headers hd
                inner join hokicuan_db.journal_details dt on dt.header_id = hd.id
                where hd.id = ". $header->id."
                group by hd.transaction_code;";
                $data = DB::select($query);

                if(count($data) > 0){
                    if($data[0]->result != 0){
                        $nData = [
                            'id'    => $header->id,
                            'code'  => $header->transaction_code,
                            'diff'  => $data[0]->result
                        ];

                        $result[] = $nData;
                    }
                }
            }

            dd($result);
        }
        catch (\Exception $ex){
            return dd($ex);
        }
    }

    public function checkOuraTS(Request $request){
        try{
            $branchId = $request->query('branch_id');

            $bookingDetails = DB::table('booking_details')
                ->join('booking_headers', 'booking_details.header_id', '=', 'booking_headers.id')
                ->join('transaction_headers', 'booking_headers.transaction_id', '=', 'transaction_headers.id')
                ->select('booking_headers.id as header_id',
                    'booking_headers.code as book_code',
                    'booking_headers.transaction_id as transaction_id',
                    'transaction_headers.code as trx_code')
                ->where('booking_headers.branch_id', '=', $branchId)
                ->where('booking_headers.status_id', '=', 9)
                ->whereNotNull('booking_details.ts_notes')
                ->where('transaction_headers.created_at', '>', $request->query('start'))
                ->where('transaction_headers.created_at', '<', $request->query('end'))
                ->get();

            $tsCoa = DB::table('journal_maps')
                ->where('branch_id', '=', $branchId)
                ->where('coa_name', '=', 'Biaya Tambahan Pelanggan')
                ->first();

            foreach($bookingDetails as $bookingDetail){
                if(!DB::table('journal_details')
                    ->where('reference_code', '=', $bookingDetail->trx_code)
                    ->where('account_id', '=', $tsCoa->coa_id)
                    ->exists()){
                    echo $bookingDetail->trx_code. ' TS NOT EXISTS IN JOURNAL!<br/>';
                }
            }

            return 'SUCCESS';
        }
        catch (\Exception $ex){
            return dd($ex);
        }
    }

    public function checkIllegalOuraTS(Request $request){
        try{
            $branchId = $request->query('branch_id');

            $tsCoa = DB::table('journal_maps')
                ->where('branch_id', '=', $branchId)
                ->where('coa_name', '=', 'Biaya Tambahan Pelanggan')
                ->first();

            $trxHeaders = DB::table('transaction_headers')
                ->join('booking_headers', 'booking_headers.transaction_id', '=', 'transaction_headers.id')
                ->select('booking_headers.id as book_id',
                    'transaction_headers.code as trx_code')
                ->where('transaction_headers.status_id', '=', 9)
                ->whereNotNull('booking_headers.id')
                ->where('transaction_headers.created_at', '>', $request->query('start'))
                ->where('transaction_headers.created_at', '<', $request->query('end'))
                ->get();

            foreach($trxHeaders as $trxHeader){
                if(!DB::table('booking_details')
                    ->where('header_id', '=', $trxHeader->book_id)
                    ->whereNotNull('ts_notes')
                    ->exists()){
                    if(DB::table('journal_details')
                        ->where('reference_code', '=', $trxHeader->trx_code)
                        ->where('account_id', '=', $tsCoa->coa_id)
                        ->exists()){
                        echo $trxHeader->trx_code. ' TS ILLEGAL!<br/>';
                    }
                }
            }

            return 'SUCCESS';
        }
        catch (\Exception $ex){
            return dd($ex);
        }
    }

    public function checkPointJournal(Request $request){
        try{
            $branchId = $request->query('branch_id');

            $pointCoa = DB::table('journal_maps')
                ->where('branch_id', '=', $branchId)
                ->where('coa_name', '=', 'HUTANG POINT MEMBER')
                ->first();

            $trxHeaders = DB::table('transaction_headers')
                ->where('transaction_headers.branch_id', '=', $branchId)
                ->where('transaction_headers.status_id', '=', 9)
                ->where('transaction_headers.app_point_used', '>', 0)
                ->where('transaction_headers.created_at', '>', $request->query('start'))
                ->where('transaction_headers.created_at', '<', $request->query('end'))
                ->get();

            foreach($trxHeaders as $trxHeader){
                $tmp = $trxHeader->app_point_used;

                if(!DB::table('journal_details')
                    ->where('reference_code', '=', $trxHeader->code)
                    ->where('account_id', '=', 2890)
                    ->where('original_amount', '=', $tmp)
                    ->exists()){
                    echo $trxHeader->code. ' COA NOT EXIST!<br/>';
                }
            }

            return 'SUCCESS';
        }
        catch (\Exception $ex){
            return dd($ex);
        }
    }

    public function checkPayment(Request $request){
        try{
            $branchId = $request->query('branch_id');

            $trxHeaders = DB::table('transaction_headers')
                ->where('transaction_headers.branch_id', '=', $branchId)
                ->where('transaction_headers.status_id', '=', 9)
                ->where('transaction_headers.created_at', '>', $request->query('start'))
                ->where('transaction_headers.created_at', '<', $request->query('end'))
                ->get();

            foreach($trxHeaders as $trxHeader){
                $paymentAmount = DB::table('transaction_payments')
                    ->where('header_id', '=', $trxHeader->id)
                    ->sum('amount');

                if($trxHeader->grand_total != $paymentAmount){
                    echo $trxHeader->code. ' PAYMENT NOT MATCH!<br/>';
                }
            }

            return 'SUCCESS';
        }
        catch (\Exception $ex){
            return dd($ex);
        }
    }

    public function checkOuraHomeServiceFee(Request $request){
        try{
            $branchId = $request->query('branch_id');

            $bookingDetails = DB::table('booking_headers')
                ->join('transaction_headers', 'booking_headers.transaction_id', '=', 'transaction_headers.id')
                ->select('booking_headers.id as header_id',
                    'booking_headers.code as book_code',
                    'booking_headers.transaction_id as transaction_id',
                    'transaction_headers.code as trx_code')
                ->where('booking_headers.branch_id', '=', $branchId)
                ->where('booking_headers.status_id', '=', 9)
                ->where('booking_headers.type', '=', 'home_service')
                ->where('transaction_headers.created_at', '>', $request->query('start'))
                ->where('transaction_headers.created_at', '<', $request->query('end'))
                ->get();

            foreach($bookingDetails as $bookingDetail){
                if(!DB::table('journal_details')
                    ->where('reference_code', '=', $bookingDetail->trx_code)
                    ->where('credit', '=', 25000.00000)
                    ->exists()){
                    echo $bookingDetail->trx_code. ' HOME SERVICE FEE NOT EXISTS IN JOURNAL!<br/>';
                }
            }

            return 'SUCCESS';
        }
        catch (\Exception $ex){
            return dd($ex);
        }
    }

    public function checkDuplicatedCommission(Request $request){
        try{
            $histories = DB::table('commission_partner_histories')
                ->where('branch_id', '=', $request->get('branch_id'))
                ->whereBetween('transaction_date', array($request->get('start'), $request->get('end')))
                ->whereNotNull('package_id')
                ->get();

            $tmpArr = [];
            foreach($histories as $history){
                $isInput = true;
                foreach($tmpArr as $tmp){
                    if($history->transaction_code == $tmp['transaction_code'] &&
                        $history->package_id == $tmp['package_id'] &&
                        $history->partner_id == $tmp['partner_id']){
                        $isInput = false;
                    }
                }

                if($isInput){
                    $tmpArr[] = [
                        'transaction_code' => $history->transaction_code,
                        'package_id' => $history->package_id,
                        'partner_id' => $history->partner_id,
                    ];
                }
                else{
                    echo "history_id=". $history->id. ' DUPLICATED! transaction_id='. $history->transaction_id. '<br/>';
                    DB::table('commission_partner_histories')
                        ->where('id', '=', $history->id)
                        ->delete();
                }
            }

            return 'SUCCESS';

        }
        catch (\Exception $ex){
            return dd($ex);
        }
    }

    public function generateCoaMonthlyAmountAll(Request $request){
        try{
            $branchId = intval($request->query('branch_id'));
            $endDateStr = $request->query('branch_id');

            $startDate = Carbon::createFromDate(2023,4,1, 'Asia/Jakarta');
            $endDate = Carbon::parse($endDateStr);

            $coas = DB::table('coas')
                ->where('branch_id', '=', $branchId)
                ->get();

            foreach($coas as $coa){
                $iterateDate = $startDate->copy();

                $accumulatedCredit = 0;
                $accumulatedDebit = 0;
                while($iterateDate->lessThan($endDate)){
                    $monthCredit = 0;
                    $monthDebit = 0;

                    $tmpDate = $iterateDate->copy();
                    $tmpStartDate = $tmpDate->startOfMonth()->toDateTimeString();
                    $tmpEndDate = $tmpDate->endOfMonth()->toDateTimeString();

                    $jDetails = DB::table('journal_details')
                        ->join('journal_headers', 'journal_details.header_id', '=', 'journal_headers.id')
                        ->select('journal_details.debit as debit',
                            'journal_details.credit as credit')
                        ->where('journal_headers.branch_id', '=', $branchId)
                        ->where('journal_details.account_id', '=', $coa->id)
                        ->where('journal_headers.status_id', '=', 13)
                        ->where('journal_headers.transaction_date', '>=', $tmpStartDate)
                        ->where('journal_headers.transaction_date', '<=', $tmpEndDate)
                        ->get();

                    foreach($jDetails as $jDetail){
                        $monthCredit += $jDetail->credit;
                        $accumulatedCredit += $jDetail->credit;
                        $monthDebit += $jDetail->debit;
                        $accumulatedDebit += $jDetail->debit;
                    }

                    if($monthCredit > 0 || $monthDebit > 0){
                        DB::table('coa_monthly_amounts')
                            ->insert([
                                'branch_id' => $branchId,
                                'month' => $iterateDate->month,
                                'year' => $iterateDate->year,
                                'coa_id' => $coa->id,
                                'coa_code' => $coa->code,
                                'coa_name' => $coa->name,
                                'debit_add' => $monthDebit,
                                'credit_add' => $monthCredit,
                                'debit_total' => $accumulatedDebit,
                                'credit_total' => $accumulatedCredit,
                                'aktiva' => $monthDebit - $monthCredit,
                                'pasiva' => $monthCredit - $monthDebit
                            ]);
                    }

                    $iterateDate->addMonthNoOverflow(1);
                }
            }

            return 'SUCCESS';
        }
        catch (\Exception $ex){
            return dd($ex);
        }
    }

    public function generateCoaMonthlyAmountSpecific(Request $request){
        try{
            $branchId = intval($request->query('branch_id'));
            $startDateStr = $request->query('start');
            $endDateStr = $request->query('end');
            $coaId = $request->exists('coa_id') ? $request->query('coa_id') : 0;

            $startDate = Carbon::parse($startDateStr);
            $endDate = Carbon::parse($endDateStr);
            $endDate->subMinutes(1);

            $coas = DB::table('coas')
                ->where('branch_id', '=', $branchId)
                ->get();

            foreach($coas as $coa){
                if($coaId != 0 && $coaId != $coa->id) continue;

                // Get previous period data
                $previousStartDate = $startDate->copy()->subMonthNoOverflow(1);
                $previousRecord = DB::table('coa_monthly_amounts')
                    ->where('branch_id', '=', $branchId)
                    ->where('coa_id', '=', $coa->id)
                    ->where('year', '=', $previousStartDate->year)
                    ->where('month', '=', $previousStartDate->month)
                    ->first();

                $accumulatedCredit = $previousRecord->credit_total ?? 0;
                $accumulatedDebit = $previousRecord->debit_total ?? 0;
                $monthCredit = 0;
                $monthDebit = 0;

                $jDetails = DB::table('journal_details')
                    ->join('journal_headers', 'journal_details.header_id', '=', 'journal_headers.id')
                    ->select('journal_details.debit as debit',
                        'journal_details.credit as credit')
                    ->where('journal_headers.branch_id', '=', $branchId)
                    ->where('journal_details.account_id', '=', $coa->id)
                    ->where('journal_headers.status_id', '=', 13)
                    ->where('journal_headers.transaction_date', '>=', $startDate)
                    ->where('journal_headers.transaction_date', '<=', $endDate)
                    ->get();

                foreach($jDetails as $jDetail){
                    $monthCredit += $jDetail->credit;
                    $accumulatedCredit += $jDetail->credit;
                    $monthDebit += $jDetail->debit;
                    $accumulatedDebit += $jDetail->debit;
                }

                if($monthCredit > 0 || $monthDebit > 0){
                    $date = Carbon::createFromDate($startDate->year, $startDate->month, 1);
                    $date->hour(2);

                    DB::table('coa_monthly_amounts')
                        ->insert([
                            'branch_id' => $branchId,
                            'month' => $startDate->month,
                            'year' => $startDate->year,
                            'date' => $date->toDateTimeString(),
                            'coa_id' => $coa->id,
                            'coa_code' => $coa->code,
                            'coa_name' => $coa->name,
                            'debit_add' => $monthDebit,
                            'credit_add' => $monthCredit,
                            'debit_total' => $accumulatedDebit,
                            'credit_total' => $accumulatedCredit,
                            'aktiva' => $monthDebit - $monthCredit,
                            'pasiva' => $monthCredit - $monthDebit
                        ]);

                    echo 'branch_id: '. $branchId. ' coa_code: '. $coa->code. ' debit_add: '. intval($monthDebit). ' credit_add: '. intval($monthCredit). '<br/>';
                }
            }

            return 'SUCCESS';
        }
        catch (\Exception $ex){
            return dd($ex);
        }
    }

    public function fillCoaDates(){
        try{
            $coaAmounts = DB::table('coa_monthly_amounts')->get();
            foreach($coaAmounts as $coaAmount){
                $date = Carbon::parse($coaAmount->year. '-'. $coaAmount->month. '-01 00:00:00');
                DB::table('coa_monthly_amounts')
                    ->where('id', '=', $coaAmount->id)
                    ->update([
                        'date' => $date->toDateTimeString()
                    ]);
            }

            return 'SUCCESS';
        }
        catch (\Exception $ex){
            return dd($ex);
        }
    }

    public function checkDuplicatedJournals(Request $request){
        try{
            $startDate = Carbon::today('Asia/Jakarta')->subDays($request->query('sub_days'))->toDateTimeString();
            $endDate = Carbon::today('Asia/Jakarta')->toDateTimeString();

            $jHeaders = DB::table('journal_headers')
                ->where('branch_id', '=', $request->query('branch_id'))
                ->where('transaction_type', '=', 'POS')
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->get();

            $tmpArr = [];
            foreach($jHeaders as $jHeader){
                if(in_array($jHeader->notes, $tmpArr)){
                    echo 'DUPLICATED FOUND! notes: '. $jHeader->notes. '<br/>';

                    // if($request->query('delete')){
                    //     // Delete duplicated journals
                    //     DB::table('journal_details')
                    //         ->where('header_id', '=', $jHeader->id)
                    //         ->delete();

                    //     DB::table('journal_headers')
                    //         ->where('id', '=', $jHeader->id)
                    //         ->delete();
                    // }
                }
                else{
                    $tmpArr[] = $jHeader->notes;
                }
            }
            echo 'DONE! <br/>';
        }
        catch (\Exception $ex){
            return dd($ex);
        }
    }

    public function processStock(Request $request){
        try{
            $configWarehouse = DB::table('configurations')
                ->where('key', '=', 'selling_warehouse_id')
                ->where('branch_id', '=', $request->query('branch_id'))
                ->first();
            $warehouseId = intval($configWarehouse->value_int);

            $trxHeaders = DB::table('transaction_headers')
                ->where('branch_id', '=', $request->query('branch_id'))
                ->where('created_at', '>', '2024-12-01 00:00:00')
                ->where('created_at', '<', '2024-12-02 01:00:00')
                ->get();

            foreach($trxHeaders as $header){
                $trxDetails = DB::table('transaction_details')
                    ->join('items', 'transaction_details.item_id', '=', 'items.id')
                    ->select('transaction_details.id as id',
                        'transaction_details.qty as qty',
                        'transaction_details.item_id as item_id',
                        'items.is_unlimited_stock as is_unlimited_stock')
                    ->where('transaction_details.header_id', '=', $header->id)
                    ->get();

                foreach($trxDetails as $trxDetail){
                    if($trxDetail->is_unlimited_stock < 1){
                        // Decrease stock
                        Utilities::updateStock($request->query('branch_id'),
                            'sub',
                            $trxDetail->qty,
                            $trxDetail->item_id,
                            $warehouseId,
                            $header->code,
                            'Tambah Item untuk '. $header->code);
                    }
                }
            }

            return 'DONE';
        }
        catch (\Exception $ex){
            return dd($ex);
        }
    }

    public function checkTransactionComponentExists(Request $request){
        try{
            $journalHeaders = DB::table('journal_headers')
                ->select('journal_headers.*')
                ->where('status_id', '=', 13)
                ->where('transaction_type', '=', 'POS')
                ->whereExists(function ($subquery){
                    $subquery->select(DB::raw(1))
                        ->from('journal_details')
                        ->whereColumn('journal_details.header_id', '=', 'journal_headers.id')
                        ->where('journal_details.credit', '=', 40000.0000);
                })
                ->where('transaction_date', '>=', '2024-12-01 00:00:00')
                ->where('transaction_date', '<', '2025-01-01 00:00:00')
                ->get();

            foreach($journalHeaders as $jHeader){
                $count = DB::table('journal_details')
                    ->where('header_id', '=', $jHeader->id)
                    ->where('credit', '=', 40000.0000)
                    ->count();

                $tCount = DB::table('transaction_details')
                    ->select('transaction_details.item_id as item_id')
                    ->join('transaction_headers', 'transaction_details.header_id', '=', 'transaction_headers.id')
                    ->where('transaction_headers.id', '=', $jHeader->reference_id)
                    ->where('transaction_details.price', '=', 40000.0000)
                    ->count();

                if($tCount == 0) continue;
                if($count != $tCount){
                    echo $jHeader->reference_id. ' DIFFERENT!<br/>';
                }
            }
        }
        catch (\Exception $ex){
            return dd($ex);
        }
    }

    public function fixJurnalPranikah(Request $request){
        try{
            $listofJournal = [];
            $pranikahDatas = PreweddingHeader::where('status_id', 1)->get();
            DB::beginTransaction();
            foreach($pranikahDatas as $pranikahData){

                $header = TransactionHeader::where('id', $pranikahData->transaction_id)->first();
                $invoice = TransactionInvoice::where('header_ids', $pranikahData->transaction_id)->first();
                //delete existing journal
                DB::table('journal_headers')
                    ->where('reference_code', '=', $header->code)
                    ->where('status_id', '=', 13)
                    ->delete();
                DB::table('journal_details')
                    ->where('reference_code', '=', $header->code)
                    ->delete();

                // generate jurnal Journal Transaction waktu pertama kali beli paket
                $jHeader = new JournalHeader();
                $jHeader->transaction_code = AutoNumberGenerator::generateAutonumberTrx('JN');
                $jHeader->transaction_type = 'POS';
                $jHeader->transaction_date = $invoice->created_at;
                $jHeader->created_by = $invoice->created_by;
                $jHeader->branch_id = $invoice->branch_id;
                $jHeader->notes = 'Journal Transaksi Deposit ' . $header->code;
                $jHeader->reference_id = $invoice->id;
                $jHeader->reference_code = $header->code;
                $jHeader->status_id = 13;
                $jHeader->created_at = $invoice->created_at;
                $jHeader->updated_at = $invoice->created_at;
                $jHeader->save();

                $listofJournal[] = $jHeader->transaction_code;

                $dbPayments = TransactionPayment::where('invoice_id', $invoice->id)->get();
                foreach ($dbPayments as $dbPayment){
                    if($dbPayment->amount > 0){
                        $journalMap = JournalMap::where('branch_id', 1)
                        ->where(function($q){
                            $q->where('transaction_type', 'POS_CASH')
                                ->orWhere('transaction_type', 'POS_NON_CASH');
                        })
                        ->where('room_type_id', 'like', '%'.$dbPayment->payment_type_id.'%')
                        ->first();

                        if($journalMap != null){
                            //insert journal
                            $coa = Coa::where('id', $journalMap->coa_id)->first();
                            if($coa != null){
                                //insert journal detail
                                $type = 'KREDIT';
                                if($journalMap->journal_type == 'DEBET'){
                                    $type = 'DEBET';
                                }
                                $notes = 'Payment Transaksi ' . $header->code;
                                JournalUtilities::insertRecord($jHeader, 1, $coa, $type, $dbPayment->amount, $notes, $header->code);
                            }
                        }
                        //insert jurnal deposit voucher
                        $coa = Coa::where('code', "2217")->first();
                        $value = $dbPayment->amount;

                        $notes = 'Deposit Pranikah ' . ' : ' . $value;
                        JournalUtilities::insertRecord($jHeader, 1, $coa, "KREDIT", $dbPayment->amount, $notes, $header->code);
                    }
                }


                foreach($header->transaction_details as $tDetail){
                    $pb = ($tDetail->price * $tDetail->qty * 10) / 100;
                    $totalDeposit = ($tDetail->price * $tDetail->qty) + $pb;

                    // generate jurnal Journal Transaction waktu direalisasikan
                    $jHeader = new JournalHeader();
                    $jHeader->transaction_code = AutoNumberGenerator::generateAutonumberTrx('JN');
                    $jHeader->transaction_type = 'POS';
                    $jHeader->transaction_date = $tDetail->updated_at;
                    $jHeader->created_by = $invoice->created_by;
                    $jHeader->branch_id = $invoice->branch_id;
                    $jHeader->notes = 'Journal Transaksi Realisasi ' . $header->code;
                    $jHeader->reference_id = $invoice->id;
                    $jHeader->reference_code = $header->code;
                    $jHeader->status_id = 13;
                    $jHeader->created_at = $tDetail->updated_at;
                    $jHeader->updated_at = $tDetail->updated_at;
                    $jHeader->save();

                    $listofJournal[] = $jHeader->transaction_code;

                    //insert jurnal deposit Pranikah
                    $coa = Coa::where('code', "2217")->first();
                    $value = $totalDeposit;

                    $notes = 'Deposit Pranikah ' . ' : ' . $value;
                    JournalUtilities::insertRecord($jHeader, 1, $coa, 'DEBIT', $value, $notes, $header->code);
                    $packageOrItemName = "";
                    if($tDetail->package_id != null){
                        $tmpPackageDetail = Package::where('id', $tDetail->package_id)->first();
                        $packageOrItemName = $tmpPackageDetail->name;

                        //insert jurnal pendapatan
                        $coa = Coa::where('id', $tmpPackageDetail->room_type->coa_id)->first();
                        $value = ($tDetail->price * $tDetail->qty);

                        $notes = 'Pendapatan Transaksi ' . $tmpPackageDetail->name . ' : ' . $value;
                        JournalUtilities::insertRecord($jHeader, 1, $coa, 'KREDIT', $value, $notes, $header->code);

                    }
                    if($tDetail->item_id != null){
                        //insert journal item
                        $item = DB::table('items')
                                ->where('id', '=', $tDetail->item_id)
                                //->where('name', 'not like', 'TS%')
                                ->first();
                        $packageOrItemName = $item->name;

                        //jurnal pendapatan cafe
                        if($item->category_id == 2 || $item->category_id == 28){
                            $coa = Coa::where('code', "4105")->first();
                            $value = ($tDetail->price * $tDetail->qty);

                            $notes = 'Pendapatan Transaksi ' . $item->name . ' : ' . $value;
                            JournalUtilities::insertRecord($jHeader, 1, $coa, 'KREDIT', $value, $notes, $header->code);

                        }
                        else{
                            //jurnal pendapatan tambahan treatment
                            $coa = Coa::where('code', "4102")->first();
                            $value = ($tDetail->price * $tDetail->qty);

                            $notes = 'Pendapatan Transaksi ' . $item->name . ' : ' . $value;
                            JournalUtilities::insertRecord($jHeader, 1, $coa, 'KREDIT', $value, $notes, $header->code);
                        }
                    }

                    //jurnal PB / Pajak
                    $coa = Coa::where('code', "2208")->first();
                    $value = $pb;

                    $notes = 'Pajak Barang dan Jasa Transaksi ' . $packageOrItemName . ' : ' . $value;
                    JournalUtilities::insertRecord($jHeader, 1, $coa, 'KREDIT', $value, $notes, $header->code);
                }
            }

            DB::commit();
            return dd($listofJournal);
        }
        catch (\Exception $ex){
            DB::rollBack();
            return dd($ex);
        }
    }
}
