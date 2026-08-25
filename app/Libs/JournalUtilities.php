<?php
/**
 * Created by PhpStorm.
 * User: yanse
 * Date: 14-Sep-17
 * Time: 2:38 PM
 */

namespace App\Libs;

use App\Models\Branch;
use App\Models\Coa;
use App\Models\JournalDetail;
use App\Models\JournalHeader;
use App\Models\JournalMap;
use App\Models\Package;
use App\Models\RoomType;
use App\Models\TransactionDetail;
use App\Models\TransactionHeader;
use App\Models\TransactionInvoice;
use App\Models\TransactionPayment;
use App\Models\VoucherPackage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class JournalUtilities
{

    public static function insertRecord(JournalHeader $journal, $branchId, Coa $coa, $type, $amount, $notes, $reffCode){
        try{
            $nJournalDetail = new JournalDetail();
            $nJournalDetail->branch_id = $branchId;
            $nJournalDetail->header_id = $journal->id;
            $nJournalDetail->account_id = $coa->id;
            $nJournalDetail->account_type_name = $coa->type;
            $nJournalDetail->account_name = $coa->name;
            $nJournalDetail->account_type_id = $coa->type_id;
            $nJournalDetail->account_code = $coa->code;
            if($type == 'DEBET' || $type == 'DEBIT'){
                $nJournalDetail->debit = $amount;
                $nJournalDetail->credit = 0;
            }
            else{
                $nJournalDetail->debit = 0;
                $nJournalDetail->credit = $amount;
            }
            $nJournalDetail->original_amount = $amount;
            $nJournalDetail->transaction_code = $journal->transaction_code;
            $nJournalDetail->transaction_type = $journal->transaction_type;
            $nJournalDetail->notes = $notes;
            $nJournalDetail->reference_code = $reffCode;
            $nJournalDetail->reference_id = $journal->reference_id;
            $nJournalDetail->save();
            return true;
        }catch(\Exception $ex){
            Log::channel('pos')->error('Journal Utilities - insertRecord ' . $ex);
            return false;
        }
    }

    public static function approveProcess(TransactionHeader $header, $admin){
        try{
            // Create journal header
            $jHeader = new JournalHeader();
            $jHeader->transaction_code = AutoNumberGenerator::generateAutonumberTrx('JN');
            $jHeader->transaction_type = 'POS';
            $jHeader->transaction_date = $header->created_at;
            $jHeader->created_by = $admin->id;
            $jHeader->branch_id = $header->branch_id;
            $jHeader->notes = 'Journal Transaksi ' . $header->code;
            $jHeader->reference_id = $header->id;
            $jHeader->status_id = 1;
            $jHeader->save();

            $isApplication = false;
            $dbPayments = TransactionPayment::where('header_id', $header->id)->get();

            $totalTrx = 0;
            $totalPackageTrx = 0;
            $allDetails = TransactionDetail::where('header_id', $header->id)->get();
            foreach ($allDetails as $dt){
                if($dt->package_id != null){
                    $totalTrx += (($dt->price * $dt->qty) + $dt->additional_person_amount + $dt->additional_partner_amount + $dt->extension_amount);
                    $totalPackageTrx += (($dt->price * $dt->qty) + $dt->additional_person_amount + $dt->additional_partner_amount + $dt->extension_amount);
                }
                else{
                    $totalTrx += ($dt->price * $dt->qty);
                }
            }

            // Check if transaction from app
            $branch = Branch::find($header->branch_id);
            $branchId = $header->branch_id;
            $bookingHeader = null;
            if($branch->is_oura == 1){
                $bookingHeader = DB::table('booking_headers')
                    ->where('transaction_id', '=', $header->id)
                    ->first();
                $isApplication = !empty($bookingHeader);
            }

            $isFoc = false;
            if(strtolower($header->foc_type) == 'foc'){
                $isFoc = true;

                $journalMap = JournalMap::where('branch_id', $branchId)
                    ->where('transaction_type', 'POS_FOC')
                    ->first();
                if($journalMap != null){
                    $coa = Coa::where('id', $journalMap->coa_id)->first();
                    if($coa != null){
                        // Check if HT transaction
                        if($isApplication && $bookingHeader->type === 'home_service'){
                            $totalTrx += $bookingHeader->payment_extra_charge;
                        }

                        //insert journal detail
                        $notes = 'Transaksi FOC ' . $header->code;
                        JournalUtilities::insertRecord($jHeader, $branchId, $coa, 'DEBET', $totalTrx, $notes, $header->code);
                    }
                }
            }
            else if(strtoupper($header->foc_type) == 'FOC'){
                $isFoc = true;

                $totalTrx = 0;
                $allDetails = TransactionDetail::where('header_id', $header->id)->get();
                foreach ($allDetails as $dt){
                    $totalTrx += ($dt->price * $dt->qty);
                }
                $journalMap = JournalMap::where('branch_id', $branchId)
                    ->where('transaction_type', 'POS_FOC')
                    ->first();
                if($journalMap != null){
                    $coa = Coa::where('id', $journalMap->coa_id)->first();
                    if($coa != null){
                        // Check if HT transaction
                        if($isApplication && $bookingHeader->type === 'home_service'){
                            $totalTrx += $bookingHeader->payment_extra_charge;
                        }

                        //insert journal detail
                        $notes = 'Transaksi FOC ' . $header->code;
                        JournalUtilities::insertRecord($jHeader, $branchId, $coa, 'DEBET', $totalTrx, $notes, $header->code);
                    }
                }
            }
            else if(strtolower($header->foc_type) == 'foc_package'){
                $journalMap = JournalMap::where('branch_id', $branchId)
                    ->where('transaction_type', 'POS_FOC')
                    ->first();
                if($journalMap != null){
                    $coa = Coa::where('id', $journalMap->coa_id)->first();
                    if($coa != null){
                        // Check if HT transaction
                        if($isApplication && $bookingHeader->type === 'home_service'){
                            $totalPackageTrx += $bookingHeader->payment_extra_charge;
                        }

                        //insert journal detail
                        $notes = 'Transaksi FOC ' . $header->code;
                        JournalUtilities::insertRecord($jHeader, $branchId, $coa, 'DEBET', $totalPackageTrx, $notes, $header->code);
                    }
                }
            }

            if(!$isFoc){
                foreach ($dbPayments as $dbPayment){
                    if($branch->is_oura === 1){
                        if(!empty($bookingHeader)){
                            if($dbPayment->amount > 0){
                                $bcaPaymentCoa = Coa::where('branch_id', '=', $branch->id)
                                ->where('code', '=', '1-10004')
                                ->first();

                                $notes = 'Payment Transaksi Online ' . $header->code;
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

                    if($dbPayment->amount > 0){
                        $journalMap = JournalMap::where('branch_id', $branchId)
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
                                JournalUtilities::insertRecord($jHeader, $branchId, $coa, $type, $dbPayment->amount, $notes, $header->code);
                            }
                        }
                    }
                }

                if($header->app_bonus_point > 0){
                    $coa = Coa::where('branch_id', '=', $branchId)
                        ->where('name', '=', 'DISKON POINT MEMBER')
                        ->first();

                    JournalUtilities::insertRecord($jHeader,
                        $branchId, $coa, 'DEBET', $header->app_bonus_point, 'Point dari Transaksi '. $header->code, $header->code);

                    $coa = Coa::where('branch_id', '=', $branchId)
                        ->where('name', '=', 'HUTANG POINT MEMBER')
                        ->first();

                    JournalUtilities::insertRecord($jHeader,
                        $branchId, $coa, 'KREDIT', $header->app_bonus_point, 'Point dari Transaksi '. $header->code, $header->code);
                }
            }


            //Package Journal
            $tmpDtPackage = TransactionDetail::where('header_id', $header->id)->whereNotNull('package_id')->get();
            $appFee = 0;
            foreach ($tmpDtPackage as $tmpPackage){
                $tmpPackageDetail = Package::where('id', $tmpPackage->package_id)->first();
                if($branchId == 1){
                    $packageJournal = JournalMap::
                    where('branch_id', $branchId)
                        ->where('transaction_type', 'POS_PENDAPATAN')
                        ->where('room_type_id', $tmpPackageDetail->room_type_id)
                        ->first();
                }
                else if($branch->is_oura == 1){
                    $packageJournal = JournalMap::where('branch_id', $branchId)
                        ->where('transaction_type', 'POS_PENDAPATAN')
                        ->where('coa_name', 'Pendapatan')
                        ->first();


                    if($isApplication){
                        $appFee += 25000;
                    }
                }
                else{
                    $packageJournal = JournalMap::
                    where('branch_id', $branchId)
                        ->where('transaction_type', 'POS_PENDAPATAN')
                        ->where('coa_name', 'Pendapatan Massage')
                        ->first();
                }

                if($packageJournal != null) {
                    $coa = Coa::where('id', $packageJournal->coa_id)->first();
                    if($coa != null){
                        $value = ($tmpPackage->price * $tmpPackage->qty) + $tmpPackage->additional_partner_amount
                            + $tmpPackage->additional_person_amount + $tmpPackage->extension_amount;

                        $notes = 'Pendapatan Transaksi Paket ' . $tmpPackageDetail->name . ' : ' . $value;
                        JournalUtilities::insertRecord($jHeader, $branchId, $coa, 'KREDIT', $value, $notes, $header->code);
                    }
                }

                if($tmpPackageDetail->is_birthday_promo){
                    $bPackageJournal = JournalMap::where('branch_id', $branchId)
                        ->where('transaction_type', 'POS_PENDAPATAN')
                        ->where('coa_name', 'DISKON ULTAH MEMBER')
                        ->first();

                    $bCoa = Coa::where('id', $bPackageJournal->coa_id)->first();
                    if($bCoa != null){
                        $value = ($tmpPackage->price * $tmpPackage->qty) + $tmpPackage->additional_partner_amount
                            + $tmpPackage->additional_person_amount + $tmpPackage->extension_amount;

                        $notes = 'Free Treatment Ultah '. $tmpPackageDetail->name . ' : ' . $value;
                        JournalUtilities::insertRecord($jHeader, $branchId, $bCoa, 'DEBET', $value, $notes, $header->code);
                    }
                }
            }

            if($isApplication){
                if($bookingHeader->type === 'home_service'){
                    $jHomeServiceFee = JournalMap::where('branch_id', $branch->id)->where('coa_code', '4-40000')->first();
                    $coaPendapatan = Coa::where('id', $jHomeServiceFee->coa_id)
                        ->first();
                    JournalUtilities::insertRecord($jHeader,
                            $header->branch_id,
                            $coaPendapatan,
                            $jHomeServiceFee->journal_type,
                            $appFee,
                            'Fee Home Service ' . $header->code,
                            $header->code);
                }

                if($bookingHeader->point_used > 0){
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
                            'Penggunaan Point Member ' . $header->code,
                            $header->code);
                }
            }

            //Facility journal
            $facilityOnly = TransactionDetail::where('header_id', $header->id)->where('is_facility_only', 1)->first();
            if($facilityOnly != null){
                $facJournalMap = JournalMap::where('room_type_id', 99)
                                            ->where('branch_id', $branchId)->first();
                if($facJournalMap != null){
                    $coa = Coa::where('id', $facJournalMap->coa_id)->first();
                    if($coa != null){
                        $notes = 'Automate - Transaksi ' . $coa->coa_name;
                        JournalUtilities::insertRecord($jHeader, $branchId, $coa, $facJournalMap->journal_type, $facilityOnly->qty * $facilityOnly->price, $notes, $header->code);
                    }
                }
            }

            //Member Journal
            if($header->branch_id == 1){
                $memberOnly = TransactionDetail::where('header_id', $header->id)->where('membership_id', 1)->first();
                if($memberOnly != null){
                    $memberOnlyMap = JournalMap::where('transaction_type', 'POS_MEMBER')->where('branch_id', 1)->first();
                    if($memberOnlyMap != null){
                        $coa = Coa::where('id', $memberOnlyMap->coa_id)->first();
                        if($coa != null){
                            $notes = 'Automate - Transaksi ' . $coa->coa_name . ' ' . $header->code;
                            JournalUtilities::insertRecord($jHeader, $branchId, $coa, $memberOnlyMap->journal_type, $memberOnly->price * $memberOnly->qty, $notes, $header->code);
                        }
                    }
                }
            }

            //Insert Detail Item Journal
            $dtItems = TransactionDetail::where('header_id', $header->id)->where('item_id', '!=', null)->get();
            if($branch->is_oura == 1){
                $hppJournalMap = JournalMap::where('branch_id', $branchId)
                    ->where(function($q){
                        $q->where('transaction_type', 'POS_HPP')
                            ->orWhere('transaction_type', 'POS_PERSEDIAAN')
                            ->orWhere('transaction_type', 'POS_PENJUALAN')
                            ->orWhere('transaction_type', 'POS_PENDAPATAN');
                    })
                    ->get();
            }
            else{
                $hppJournalMap = JournalMap::where('branch_id', $branchId)
                ->where(function($q){
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
            //dd($hppJournalMap);

            $tmpJournalData = [];
            foreach ($hppJournalMap as $map){
                $nMap = [
                    'id'            => $map->id,
                    'coa_id'        => $map->coa_id,
                    'coa_name'      => $map->coa_name,
                    'coa_code'      => $map->coa_code,
                    'journal_type'  => $map->journal_type,
                    'amount'        => 0,
                ];

                $tmpJournalData[] = $nMap;
            }
            //dd($tmpJournalData);

            $foc = strtoupper($header->foc_type);
            foreach ($dtItems as $dt){
                //check item in DB
                if($foc == 'FOC_ITEM' || $foc == 'FOC'){
                    //discount
                    $dt->discount_percentage = 100;
                    $dt->discount_amount = $dt->subtotal;
                }

                //insert journal item
                $item = DB::table('items')
                        ->where('id', '=', $dt->item_id)
                        //->where('name', 'not like', 'TS%')
                        ->first();
                if($item != null){
                    //Journal Pendapatan
                    $hppName = 'HPP - Lain-lain';
                    $categoryName = 'Pendapatan - Lain-lain';
                    $persediaanName = 'Persediaan - Lain-lain';

                    if($branchId == 1){
                        if(str_contains($item->name, 'KIMO') || $item->category_id == 20){
                            $categoryName = 'Pendapatan - Kimo';
                            $hppName = 'HPP - Kimo';
                            $persediaanName = 'Persediaan - Kimo';
                        }
                        else if(str_contains($item->name, 'OIL')){
                            $categoryName = 'Pendapatan Oil';
                        }
                        else if(str_contains($item->name, 'HOT STONE')){
                            $categoryName = 'Pendapatan Hot Stone';
                        }

                        if($item->category_id == 2){
                            $categoryName = 'Pendapatan - F&B';
                            $hppName = 'HPP - F&B';
                            $persediaanName = 'Persediaan - F&B';
                        }

                        if($item->category_id == 21){
                            $categoryName = 'Pendapatan Meal';
                        }

                        if($item->category_id == 22){
                            $categoryName = 'PENDAPATAN MEAL EKSTERNAL';
                        }

                        if($item->category_id == 23){
                            $categoryName = 'PENDAPATAN MEAL INTERNAL';
                        }

                        if($item->category_id == 25){
                            $categoryName = 'PENJUALAN LIQUOR';
                            $hppName = 'HPP - LIQUOR';
                            $persediaanName = 'PERSEDIAAN LIQUOR';
                        }

                        if(str_contains($item->name, 'NURU')){
                            $categoryName = 'PENJUALAN NURU';
                            $hppName = 'HPP - NURU';
                            $persediaanName = 'PERSEDIAAN NURU';
                        }

                        if(str_contains($item->name, 'ICE CREAM LICK SENSATION')){
                            $categoryName = 'PENJUALAN ICE CREAM LICK SENSATION';
                            $hppName = 'HPP ICE CREAM LICK SENSATION';
                            $persediaanName = 'PERSEDIAAN ICE CREAM LICK SENSATION';
                        }
                    }
                    else if($branchId >= 6){
                        $hppName = 'Beban Pokok Penjualan';
                        $categoryName = 'Pendapatan';
                        $persediaanName = 'Persediaan';
                        if($item->category_id == 2){
                            $persediaanName = 'PERSEDIAAN - F&B';
                            $categoryName = 'PENJUALAN F&B';
                            $hppName = 'BEBAN POKOK PENJUALAN - F&B';
                        }

                        if($item->name == 'OIL THERAPY ( HOT )'){
                            $categoryName = 'PENDAPATAN OIL THERAPY (HOT)';
                            $persediaanName = 'PERSEDIAAN - OIL THERAPI (HOT)';
                            $hppName = 'BEBAN POKOK PENJUALAN - OIL THERAPI (HOT)';
                        }
                        else if(str_contains($item->name, 'TS')){
                            $categoryName = 'Biaya Tambahan Pelanggan';
                        }
                        //check for HPP
                        // if($item->category_id == 2){
                        //     $hppName = 'BEBAN POKOK PENJUALAN - F&B';
                        // }
                    }
                    else{
                        $hppName = 'HPP Lain-lain';
                        $categoryName = 'Penjualan Lain-lain';
                        $persediaanName = 'Persediaan Lain-lain';

                        if(str_contains($item->name, 'KIMO')){
                            $categoryName = 'Penjualan Kimo';
                            $hppName = 'HPP Kimo';
                            $persediaanName = 'Persediaan Kimo';
                        }
                        else if(str_contains($item->name, 'SCRUB')){
                            $categoryName = 'Pendapatan Scrub';
                        }
                        else if(str_contains($item->name, 'OIL')){
                            $categoryName = 'Pendapatan Oil';
                        }
                        else if(str_contains($item->name, 'HOT STONE')){
                            $categoryName = 'Pendapatan Hot Stone';
                        }
                        else if(str_contains($item->name, 'HOTSTONE')){
                            $categoryName = 'Pendapatan Hot Stone';
                        }

                        if($item->category_id == 2){
                            $categoryName = 'Penjualan F&B';
                            $hppName = 'HPP F&B';
                            $persediaanName = 'Persediaan F&B';
                        }

                        if($item->category_id == 20){
                            $categoryName = 'Penjualan Kimo';
                            $hppName = 'HPP Kimo';
                            $persediaanName = 'Persediaan Kimo';
                        }
                    }

                    $idx = 0;
                    foreach ($tmpJournalData as $map){
                        // if($map['coa_name'] == $categoryName){
                        //     $map['amount'] += (($dt->price * $dt->qty) - $dt->discount_amount);
                        // }
                        // else if($map['coa_name'] == $hppName){
                        //     $map['amount'] += (($item->cost_price * $dt->qty) - $dt->discount_amount);
                        // }
                        // else if($map['coa_name'] == $persediaanName){
                        //     $map['amount'] += (($item->cost_price * $dt->qty) - $dt->discount_amount);
                        // }

                        if($map['coa_name'] == $categoryName){
                            $map['amount'] += (($dt->price * $dt->qty));
                        }
                        if($item->category_id != 21 && !str_contains($item->name, 'TS')){
                            if($map['coa_name'] == $hppName){
                                $map['amount'] += (($item->cost_price * $dt->qty));
                            }
                            else if($map['coa_name'] == $persediaanName){
                                $map['amount'] += (($item->cost_price * $dt->qty));
                            }
                        }
                        $tmpJournalData[$idx] = $map;
                        $idx++;
                    }
                }
            }

            foreach ($tmpJournalData as $nJournal){
                if($nJournal['amount'] > 0){
                    $coa = Coa::where('id', $nJournal['coa_id'])->first();
                    if($coa != null){
                        //insert journal
                        $type = 'KREDIT';
                        if($nJournal['journal_type'] == 'DEBET'){
                            $type = 'DEBET';
                        }
                        $notes = 'Automate - Transaksi ' . $header->code;
                        JournalUtilities::insertRecord($jHeader, $branchId, $coa, $type, $nJournal['amount'], $notes, $header->code);
                    }
                }
            }

            if(!$isFoc && $header->discount > 0){
                $discountMap = JournalMap::where('branch_id', $branchId)->where('transaction_type', 'POS_POTONGAN')->first();
                if($discountMap != null){
                    $coa = Coa::where('id', $discountMap->coa_id)->first();
                    if($coa != null){
                        $notes = 'Diskon Transaksi ' . $header->discount;
                        JournalUtilities::insertRecord($jHeader, $branchId, $coa, 'DEBET', $header->discount, $notes, $header->code);
                    }
                }
            }

            // Mark approved
            DB::table('transaction_headers')
                ->where('id', '=', $header->id)
                ->update([
                    'is_approved' => 1,
                    'approved_by' => $admin->id,
                    'approved_at' => Carbon::now('Asia/Jakarta')->toDateTimeString()
                ]);

            //Session::flash('success', 'Sukses approve jurnal transaksi '. $header->code);
            return true;
        }
        catch (\Exception $ex) {
            Log::channel('pos')->error('Journal Utilities - processJournal ' . $ex);
            return false;
        }
    }
    public static function createJournalPos($id){
        try{
            // Create journal header
            $header = TransactionHeader::find($id);
            $dbInvoice = TransactionInvoice::where('header_ids', $header->id)->first();
            $dbPayments = TransactionPayment::where('invoice_id', $dbInvoice->id)->get();
            //delete pos journal
            $jHeader = JournalHeader::with('journal_details')->where('branch_id', $dbInvoice->branch_id)
                ->where('notes', 'like', '%' . $dbInvoice->code . '%')
                ->first();
            //dd($header, $jHeader);
            $pb = $dbInvoice->pb2;

            $branchId = $dbInvoice->branch_id;
            $branch = Branch::where('id', $branchId)->first();

            if(!empty($jHeader)){
                foreach ($jHeader->journal_details as $dt){
                    $dt->delete();
                }

                $jHeader->notes = 'Automate - Journal Transaksi ' . $dbInvoice->code;
                $jHeader->updated_at = Carbon::now('Asia/Jakarta');
                $jHeader->save();
            }
            else{
                // Create journal header
                $jHeader = new JournalHeader();
                $jHeader->transaction_code = AutoNumberGenerator::generateAutonumberTrx('JN');
                $jHeader->transaction_type = 'POS';
                $jHeader->transaction_date = $dbInvoice->created_at;
                $jHeader->created_by = $dbInvoice->created_by;
                $jHeader->branch_id = $dbInvoice->branch_id;
                $jHeader->notes = 'Journal Transaksi ' . $dbInvoice->code;
                $jHeader->reference_id = $dbInvoice->id;
                $jHeader->reference_code = $dbInvoice->code;
                $jHeader->status_id = 13;
                $jHeader->created_at = $dbInvoice->created_at;
                $jHeader->updated_at = $dbInvoice->created_at;
                $jHeader->save();
            }

            $isApplication = false;

            // Check if transaction from app
            $branch = Branch::find($dbInvoice->branch_id);
            $branchId = $dbInvoice->branch_id;
            $bookingHeader = null;
            if($branch->is_oura == 1){
                $bookingHeader = DB::table('booking_headers')
                    ->where('transaction_id', '=', $header->id)
                    ->first();
                $isApplication = !empty($bookingHeader);
            }

            $isFoc = false;

            if(!$isFoc){
                foreach ($dbPayments as $dbPayment){
                    // if($branch->is_oura === 1){
                    //     if(!empty($bookingHeader)){
                    //         // if($dbPayment->amount > 0){
                    //         //     if($dbPayment->payment_type_code == 'ONLINE'){
                    //         //         $bcaPaymentCoa = Coa::where('branch_id', '=', $branch->id)
                    //         //         ->where('code', '=', '1-10004')
                    //         //         ->first();

                    //         //         $notes = 'Payment Transaksi Online ' . $header->code;
                    //         //         JournalUtilities::insertRecord($jHeader,
                    //         //             $header->branch_id,
                    //         //             $bcaPaymentCoa,
                    //         //             'DEBET',
                    //         //             $header->grand_total,
                    //         //             $notes,
                    //         //             $header->code);
                    //         //         continue;
                    //         //     }
                    //         // }
                    //     }
                    // }

                    if($dbPayment->amount > 0){
                        $journalMap = JournalMap::where('branch_id', $branchId)
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
                                JournalUtilities::insertRecord($jHeader, $branchId, $coa, $type, $dbPayment->amount, $notes, $header->code);
                            }
                        }
                    }
                }

            }


            //Insert Detail Package Journal
            $tmpDtPackage = TransactionDetail::where('header_id', $header->id)->whereNotNull('package_id')->where('status_id', '!=', 16)->get();
            foreach ($tmpDtPackage as $tmpPackage){
                $tmpPackageDetail = Package::where('id', $tmpPackage->package_id)->first();

                //insert jurnal diskon
                if($tmpPackage->discount_amount > 0){
                    $coa = Coa::where('code', "4201")->first();
                    $value = $tmpPackage->discount_amount;

                    $notes = 'Diskon Transaksi ' . $tmpPackageDetail->name . ' : ' . $value;
                    JournalUtilities::insertRecord($jHeader, $branchId, $coa, 'DEBET', $value, $notes, $header->code);
                }

                //insert jurnal pendapatan
                $coa = Coa::where('id', $tmpPackageDetail->room_type->coa_id)->first();
                $value = ($tmpPackage->price * $tmpPackage->qty);

                $notes = 'Pendapatan Transaksi ' . $tmpPackageDetail->name . ' : ' . $value;
                JournalUtilities::insertRecord($jHeader, $branchId, $coa, 'KREDIT', $value, $notes, $header->code);
            }

            //Insert Detail Item Journal
            $dtItems = TransactionDetail::where('header_id', $header->id)->where('item_id', '!=', null)->get();

            $foc = strtoupper($header->foc_type);
            foreach ($dtItems as $dt){
                if($dt->item_id == 68){
                    continue;
                }
                //insert journal item
                $item = DB::table('items')
                        ->where('id', '=', $dt->item_id)
                        //->where('name', 'not like', 'TS%')
                        ->first();
                if($item != null){

                    //insert jurnal diskon
                    if($dt->discount_amount > 0){
                        $coa = Coa::where('code', "4201")->first();
                        $value = $dt->discount_amount;

                        $notes = 'Diskon Transaksi ' . $item->name . ' : ' . $value;
                        JournalUtilities::insertRecord($jHeader, $branchId, $coa, 'DEBET', $value, $notes, $header->code);
                    }

                    //jurnal pendapatan cafe
                    if($item->category_id == 2 || $item->category_id == 28){
                        $coa = Coa::where('code', "4105")->first();
                        $value = ($dt->price * $dt->qty);

                        $notes = 'Pendapatan Transaksi ' . $item->name . ' : ' . $value;
                        JournalUtilities::insertRecord($jHeader, $branchId, $coa, 'KREDIT', $value, $notes, $header->code);

                    }
                    else{
                        //jurnal pendapatan tambahan treatment, default masuk ke 4102
                        $coa = Coa::where('code', "4102")->first();

                        //journal tambahan salon, masuk ke 4104
                        if($item->name == 'Tambahan Catok' || $item->name = 'Tambahan Curly'){
                            $coa = Coa::where('code', "4104")->first();
                        }
                        $value = ($dt->price * $dt->qty);

                        $notes = 'Pendapatan Transaksi ' . $item->name . ' : ' . $value;
                        JournalUtilities::insertRecord($jHeader, $branchId, $coa, 'KREDIT', $value, $notes, $header->code);
                    }
                }
            }

            //insert jurnal pr PB
            $coa = Coa::where('code', "2208")->first();
            $value = $pb;

            $notes = 'Pajak Barang dan Jasa Transaksi ' . $tmpPackageDetail->name . ' : ' . $value;
            JournalUtilities::insertRecord($jHeader, $branchId, $coa, 'KREDIT', $value, $notes, $header->code);

            //Session::flash('success', 'Sukses approve jurnal transaksi '. $header->code);
            return true;
        }
        catch (\Exception $ex) {
            Log::channel('pos')->error('Journal Utilities - processJournal ' . $ex);
            return false;
        }
    }

    public static function createJournalPosVoucherRealisasi($id){
        try{
            // Create journal header
            $header = TransactionHeader::find($id);
            $dbInvoice = TransactionInvoice::where('header_ids', $header->id)->first();
            $dbPayments = TransactionPayment::where('invoice_id', $dbInvoice->id)->get();
            //delete pos journal
            $jHeader = JournalHeader::with('journal_details')->where('branch_id', $dbInvoice->branch_id)
                ->where('notes', 'like', '%' . $dbInvoice->code . '%')
                ->first();
            //dd($header, $jHeader);
            $pb = $dbInvoice->pb2;

            $branchId = $dbInvoice->branch_id;
            $branch = Branch::where('id', $branchId)->first();

            if(!empty($jHeader)){
                foreach ($jHeader->journal_details as $dt){
                    $dt->delete();
                }

                $jHeader->notes = 'Automate - Journal Transaksi ' . $dbInvoice->code;
                $jHeader->updated_at = Carbon::now('Asia/Jakarta');
                $jHeader->save();
            }
            else{
                // Create journal header
                $jHeader = new JournalHeader();
                $jHeader->transaction_code = AutoNumberGenerator::generateAutonumberTrx('JN');
                $jHeader->transaction_type = 'POS';
                $jHeader->transaction_date = $dbInvoice->created_at;
                $jHeader->created_by = $dbInvoice->created_by;
                $jHeader->branch_id = $dbInvoice->branch_id;
                $jHeader->notes = 'Journal Transaksi ' . $dbInvoice->code;
                $jHeader->reference_id = $dbInvoice->id;
                $jHeader->reference_code = $dbInvoice->code;
                $jHeader->status_id = 13;
                $jHeader->created_at = $dbInvoice->created_at;
                $jHeader->updated_at = $dbInvoice->created_at;
                $jHeader->save();
            }

            $isApplication = false;

            // Check if transaction from app
            $branch = Branch::find($dbInvoice->branch_id);
            $branchId = $dbInvoice->branch_id;
            $bookingHeader = null;

            //Insert Detail Package Journal
            $isVoucherPackage = false;
            $tmpDtPackage = TransactionDetail::where('header_id', $header->id)->whereNotNull('package_id')->get();
            foreach ($tmpDtPackage as $tDetail){
                $pb = ($tDetail->price * $tDetail->qty * 10) / 100;
                $totalDeposit = ($tDetail->price * $tDetail->qty) + $pb;

                //insert jurnal deposit Pranikah
                $coa = Coa::where('code', "2216")->first();
                $value = $totalDeposit;

                $notes = 'Deposit Voucher ' . ' : ' . $value;
                JournalUtilities::insertRecord($jHeader, $branchId, $coa, 'DEBIT', $value, $notes, $header->code);
                $packageOrItemName = "";

                //insert jurnal pendapatan
                $tmpPackageDetail = Package::where('id', $tDetail->package_id)->first();
                $packageOrItemName = $tmpPackageDetail->name;
                $coa = Coa::where('id', $tmpPackageDetail->room_type->coa_id)->first();
                $value = ($tDetail->price * $tDetail->qty);

                $notes = 'Pendapatan Transaksi ' . $tmpPackageDetail->name . ' : ' . $value;
                JournalUtilities::insertRecord($jHeader, $branchId, $coa, 'KREDIT', $value, $notes, $header->code);

                //jurnal PB / Pajak
                $coa = Coa::where('code', "2208")->first();
                $value = $pb;

                $notes = 'Pajak Barang dan Jasa Transaksi ' . $packageOrItemName . ' : ' . $value;
                JournalUtilities::insertRecord($jHeader, $branchId, $coa, 'KREDIT', $value, $notes, $header->code);
            }

            //Insert Detail Item Journal
            $dtItems = TransactionDetail::where('header_id', $header->id)->where('item_id', '!=', null)->get();

            $foc = strtoupper($header->foc_type);
            foreach ($dtItems as $dt){
                if($dt->item_id == 68){
                    continue;
                }
                //insert journal item
                $item = DB::table('items')
                        ->where('id', '=', $dt->item_id)
                        //->where('name', 'not like', 'TS%')
                        ->first();
                if($item != null){
                    $pb = ($dt->price * $dt->qty * 10) / 100;
                    $totalDeposit = ($dt->price * $dt->qty) + $pb;

                    //insert jurnal deposit Pranikah
                    $coa = Coa::where('code', "2216")->first();
                    $value = $totalDeposit;

                    $notes = 'Deposit Voucher ' . ' : ' . $value;
                    JournalUtilities::insertRecord($jHeader, $branchId, $coa, 'DEBIT', $value, $notes, $header->code);

                    //jurnal pendapatan cafe
                    if($item->category_id == 2 || $item->category_id == 28){
                        $coa = Coa::where('code', "4105")->first();
                        $value = ($dt->price * $dt->qty);

                        $notes = 'Pendapatan Transaksi ' . $item->name . ' : ' . $value;
                        JournalUtilities::insertRecord($jHeader, $branchId, $coa, 'KREDIT', $value, $notes, $header->code);

                    }
                    else{
                        //jurnal pendapatan tambahan treatment
                        $coa = Coa::where('code', "4102")->first();
                        $value = ($dt->price * $dt->qty);

                        $notes = 'Pendapatan Transaksi ' . $item->name . ' : ' . $value;
                        JournalUtilities::insertRecord($jHeader, $branchId, $coa, 'KREDIT', $value, $notes, $header->code);
                    }

                    //jurnal PB / Pajak
                    $coa = Coa::where('code', "2208")->first();
                    $value = $pb;

                    $notes = 'Pajak Barang dan Jasa Transaksi ' . $packageOrItemName . ' : ' . $value;
                    JournalUtilities::insertRecord($jHeader, $branchId, $coa, 'KREDIT', $value, $notes, $header->code);
                }
            }

            //Session::flash('success', 'Sukses approve jurnal transaksi '. $header->code);
            return true;
        }
        catch (\Exception $ex) {
            Log::channel('pos')->error('Journal Utilities - processJournal ' . $ex);
            return false;
        }
    }
    public static function createJournalV1($id){
        try{
            // Create journal header
            $header = TransactionHeader::find($id);
            //delete pos journal
            $jHeader = JournalHeader::with('journal_details')->where('branch_id', $header->branch_id)
                ->where('notes', 'like', '%' . $header->code . '%')
                ->first();
            //dd($header, $jHeader);

            $branchId = $header->branch_id;
            $branch = Branch::where('id', $branchId)->first();

            if(!empty($jHeader)){
                foreach ($jHeader->journal_details as $dt){
                    $dt->delete();
                }

                $jHeader->notes = 'Automate - Journal Transaksi ' . $header->code;
                $jHeader->updated_at = Carbon::now('Asia/Jakarta');
                $jHeader->save();
            }
            else{
                // Create journal header
                $jHeader = new JournalHeader();
                $jHeader->transaction_code = AutoNumberGenerator::generateAutonumberTrx('JN');
                $jHeader->transaction_type = 'POS';
                $jHeader->transaction_date = $header->created_at;
                $jHeader->created_by = $header->approved_by;
                $jHeader->branch_id = $header->branch_id;
                $jHeader->notes = 'Journal Transaksi ' . $header->code;
                $jHeader->reference_id = $header->id;
                $jHeader->reference_code = $header->code;
                $jHeader->status_id = 1;
                $jHeader->created_at = $header->approved_at;
                $jHeader->updated_at = $header->approved_at;
                $jHeader->save();
            }

            $isApplication = false;
            $dbPayments = TransactionPayment::where('header_id', $header->id)->get();

            $totalTrx = 0;
            $totalPackageTrx = 0;
            $allDetails = TransactionDetail::where('header_id', $header->id)->get();
            foreach ($allDetails as $dt){
                if($dt->package_id != null){
                    $totalTrx += (($dt->price * $dt->qty) + $dt->additional_person_amount + $dt->additional_partner_amount + $dt->extension_amount);
                    $totalPackageTrx += (($dt->price * $dt->qty) + $dt->additional_person_amount + $dt->additional_partner_amount + $dt->extension_amount);
                }
                else{
                    $totalTrx += ($dt->price * $dt->qty);
                }
            }

            // Check if transaction from app
            $branch = Branch::find($header->branch_id);
            $branchId = $header->branch_id;
            $bookingHeader = null;
            if($branch->is_oura == 1){
                $bookingHeader = DB::table('booking_headers')
                    ->where('transaction_id', '=', $header->id)
                    ->first();
                $isApplication = !empty($bookingHeader);
            }

            $isFoc = false;
            if(strtolower($header->foc_type) == 'foc'){
                $isFoc = true;

                $journalMap = JournalMap::where('branch_id', $branchId)
                    ->where('transaction_type', 'POS_FOC')
                    ->first();
                if($journalMap != null){
                    $coa = Coa::where('id', $journalMap->coa_id)->first();
                    if($coa != null){
                        // Check if HT transaction
                        if($isApplication && $bookingHeader->type === 'home_service'){
                            $totalTrx += $bookingHeader->payment_extra_charge;
                        }

                        //insert journal detail
                        $notes = 'Transaksi FOC ' . $header->code;
                        JournalUtilities::insertRecord($jHeader, $branchId, $coa, 'DEBET', $totalTrx, $notes, $header->code);
                    }
                }
            }
            else if(strtoupper($header->foc_type) == 'FOC'){
                $isFoc = true;

                $totalTrx = 0;
                $allDetails = TransactionDetail::where('header_id', $header->id)->get();
                foreach ($allDetails as $dt){
                    $totalTrx += ($dt->price * $dt->qty);
                }
                $journalMap = JournalMap::where('branch_id', $branchId)
                    ->where('transaction_type', 'POS_FOC')
                    ->first();
                if($journalMap != null){
                    $coa = Coa::where('id', $journalMap->coa_id)->first();
                    if($coa != null){
                        // Check if HT transaction
                        if($isApplication && $bookingHeader->type === 'home_service'){
                            $totalTrx += $bookingHeader->payment_extra_charge;
                        }

                        //insert journal detail
                        $notes = 'Transaksi FOC ' . $header->code;
                        JournalUtilities::insertRecord($jHeader, $branchId, $coa, 'DEBET', $totalTrx, $notes, $header->code);
                    }
                }
            }
            else if(strtolower($header->foc_type) == 'foc_package'){
                $journalMap = JournalMap::where('branch_id', $branchId)
                    ->where('transaction_type', 'POS_FOC')
                    ->first();
                if($journalMap != null){
                    $coa = Coa::where('id', $journalMap->coa_id)->first();
                    if($coa != null){
                        // Check if HT transaction
                        if($isApplication && $bookingHeader->type === 'home_service'){
                            $totalPackageTrx += $bookingHeader->payment_extra_charge;
                        }

                        //insert journal detail
                        $notes = 'Transaksi FOC ' . $header->code;
                        JournalUtilities::insertRecord($jHeader, $branchId, $coa, 'DEBET', $totalPackageTrx, $notes, $header->code);
                    }
                }
            }

            if(!$isFoc){
                foreach ($dbPayments as $dbPayment){
                    if($branch->is_oura === 1){
                        if(!empty($bookingHeader)){
                            if($dbPayment->amount > 0){
                                if($dbPayment->payment_type_code == 'ONLINE'){
                                    $bcaPaymentCoa = Coa::where('branch_id', '=', $branch->id)
                                    ->where('code', '=', '1-10004')
                                    ->first();

                                    $notes = 'Payment Transaksi Online ' . $header->code;
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

                    if($dbPayment->amount > 0){
                        $journalMap = JournalMap::where('branch_id', $branchId)
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
                                JournalUtilities::insertRecord($jHeader, $branchId, $coa, $type, $dbPayment->amount, $notes, $header->code);
                            }
                        }
                    }
                }

                if($header->app_bonus_point > 0){
                    $coa = Coa::where('branch_id', '=', $branchId)
                        ->where('name', '=', 'DISKON POINT MEMBER')
                        ->first();

                    JournalUtilities::insertRecord($jHeader,
                        $branchId, $coa, 'DEBET', $header->app_bonus_point, 'Point dari Transaksi '. $header->code, $header->code);

                    $coa = Coa::where('branch_id', '=', $branchId)
                        ->where('name', '=', 'HUTANG POINT MEMBER')
                        ->first();

                    JournalUtilities::insertRecord($jHeader,
                        $branchId, $coa, 'KREDIT', $header->app_bonus_point, 'Point dari Transaksi '. $header->code, $header->code);
                }
            }


            //Package Journal
            $tmpDtPackage = TransactionDetail::where('header_id', $header->id)->whereNotNull('package_id')->get();
            $appFee = 0;
            foreach ($tmpDtPackage as $tmpPackage){
                $tmpPackageDetail = Package::where('id', $tmpPackage->package_id)->first();
                if($branchId == 1){
                    //manually add the package shiets
                    if($tmpPackageDetail->id == 116 || $tmpPackageDetail->id == 115){
                        //if its Achiles
                        $packageJournal = JournalMap::
                        where('branch_id', $branchId)
                            ->where('transaction_type', 'POS_PENDAPATAN')
                            ->where('category_2_id', $tmpPackageDetail->id)
                            ->first();
                    }
                    else{
                        $packageJournal = JournalMap::
                        where('branch_id', $branchId)
                            ->where('transaction_type', 'POS_PENDAPATAN')
                            ->where('room_type_id', $tmpPackageDetail->room_type_id)
                            ->first();
                    }
                }
                else if($branch->is_oura == 1){
                    $packageJournal = JournalMap::where('branch_id', $branchId)
                        ->where('transaction_type', 'POS_PENDAPATAN')
                        ->where('coa_name', 'Pendapatan')
                        ->first();


                    if($isApplication){
                        $appFee += 25000;
                    }
                }
                else{
                    $packageJournal = JournalMap::
                    where('branch_id', $branchId)
                        ->where('transaction_type', 'POS_PENDAPATAN')
                        ->where('coa_name', 'Pendapatan Massage')
                        ->first();
                }

                if($packageJournal != null) {
                    $coa = Coa::where('id', $packageJournal->coa_id)->first();
                    if($coa != null){
                        $value = ($tmpPackage->price * $tmpPackage->qty) + $tmpPackage->additional_partner_amount
                            + $tmpPackage->additional_person_amount + $tmpPackage->extension_amount;

                        $notes = 'Pendapatan Transaksi Paket ' . $tmpPackageDetail->name . ' : ' . $value;
                        JournalUtilities::insertRecord($jHeader, $branchId, $coa, 'KREDIT', $value, $notes, $header->code);
                    }
                }

                if($tmpPackageDetail->is_birthday_promo){
                    $bPackageJournal = JournalMap::where('branch_id', $branchId)
                        ->where('transaction_type', 'POS_PENDAPATAN')
                        ->where('coa_name', 'DISKON ULTAH MEMBER')
                        ->first();

                    $bCoa = Coa::where('id', $bPackageJournal->coa_id)->first();
                    if($bCoa != null){
                        $value = ($tmpPackage->price * $tmpPackage->qty) + $tmpPackage->additional_partner_amount
                            + $tmpPackage->additional_person_amount + $tmpPackage->extension_amount;

                        $notes = 'Free Treatment Ultah '. $tmpPackageDetail->name . ' : ' . $value;
                        JournalUtilities::insertRecord($jHeader, $branchId, $bCoa, 'DEBET', $value, $notes, $header->code);
                    }
                }
            }

            if($isApplication){
                if($bookingHeader->type === 'home_service'){
                    $jHomeServiceFee = JournalMap::where('branch_id', $branch->id)->where('coa_code', '4-40000')->first();
                    $coaPendapatan = Coa::where('id', $jHomeServiceFee->coa_id)
                        ->first();
                    JournalUtilities::insertRecord($jHeader,
                            $header->branch_id,
                            $coaPendapatan,
                            $jHomeServiceFee->journal_type,
                            $appFee,
                            'Fee Home Service ' . $header->code,
                            $header->code);
                }

                if($bookingHeader->point_used > 0){
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
                            'Penggunaan Point Member ' . $header->code,
                            $header->code);
                }
            }

            //Facility journal
            $facilityOnly = TransactionDetail::where('header_id', $header->id)->where('is_facility_only', 1)->first();
            if($facilityOnly != null){
                $facJournalMap = JournalMap::where('room_type_id', 99)
                                            ->where('branch_id', $branchId)->first();
                if($facJournalMap != null){
                    $coa = Coa::where('id', $facJournalMap->coa_id)->first();
                    if($coa != null){
                        $notes = 'Automate - Transaksi ' . $coa->coa_name;
                        JournalUtilities::insertRecord($jHeader, $branchId, $coa, $facJournalMap->journal_type, $facilityOnly->qty * $facilityOnly->price, $notes, $header->code);
                    }
                }
            }

            //Member Journal
            if($header->branch_id == 1){
                $memberOnly = TransactionDetail::where('header_id', $header->id)->where('membership_id', 1)->first();
                if($memberOnly != null){
                    $memberOnlyMap = JournalMap::where('transaction_type', 'POS_MEMBER')->where('branch_id', 1)->first();
                    if($memberOnlyMap != null){
                        $coa = Coa::where('id', $memberOnlyMap->coa_id)->first();
                        if($coa != null){
                            $notes = 'Automate - Transaksi ' . $coa->coa_name . ' ' . $header->code;
                            JournalUtilities::insertRecord($jHeader, $branchId, $coa, $memberOnlyMap->journal_type, $memberOnly->price * $memberOnly->qty, $notes, $header->code);
                        }
                    }
                }
            }

            //Insert Detail Item Journal
            $dtItems = TransactionDetail::where('header_id', $header->id)->where('item_id', '!=', null)->get();
            if($branch->is_oura == 1){
                $hppJournalMap = JournalMap::where('branch_id', $branchId)
                    ->where(function($q){
                        $q->where('transaction_type', 'POS_HPP')
                            ->orWhere('transaction_type', 'POS_PERSEDIAAN')
                            ->orWhere('transaction_type', 'POS_PENJUALAN')
                            ->orWhere('transaction_type', 'POS_PENDAPATAN');
                    })
                    ->get();
            }
            else{
                $hppJournalMap = JournalMap::where('branch_id', $branchId)
                ->where(function($q){
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
            //dd($hppJournalMap);

            $tmpJournalData = [];
            foreach ($hppJournalMap as $map){
                $nMap = [
                    'id'            => $map->id,
                    'coa_id'        => $map->coa_id,
                    'coa_name'      => $map->coa_name,
                    'coa_code'      => $map->coa_code,
                    'journal_type'  => $map->journal_type,
                    'amount'        => 0,
                ];

                $tmpJournalData[] = $nMap;
            }
            //dd($tmpJournalData);

            $foc = strtoupper($header->foc_type);
            foreach ($dtItems as $dt){
                //check item in DB
                if($foc == 'FOC_ITEM' || $foc == 'FOC'){
                    //discount
                    $dt->discount_percentage = 100;
                    $dt->discount_amount = $dt->subtotal;
                }

                //insert journal item
                $item = DB::table('items')
                        ->where('id', '=', $dt->item_id)
                        //->where('name', 'not like', 'TS%')
                        ->first();
                if($item != null){
                    //Journal Pendapatan
                    $hppName = 'HPP - Lain-lain';
                    $categoryName = 'Pendapatan - Lain-lain';
                    $persediaanName = 'Persediaan - Lain-lain';

                    if($branchId == 1){
                        if(str_contains($item->name, 'KIMO') || $item->category_id == 20){
                            $categoryName = 'Pendapatan - Kimo';
                            $hppName = 'HPP - Kimo';
                            $persediaanName = 'Persediaan - Kimo';
                        }
                        else if(str_contains($item->name, 'OIL')){
                            $categoryName = 'Pendapatan Oil';
                        }
                        else if(str_contains($item->name, 'HOT STONE')){
                            $categoryName = 'Pendapatan Hot Stone';
                        }

                        if($item->category_id == 2){
                            $categoryName = 'Pendapatan - F&B';
                            $hppName = 'HPP - F&B';
                            $persediaanName = 'Persediaan - F&B';
                        }

                        if($item->category_id == 21){
                            $categoryName = 'Pendapatan Meal';
                        }

                        if($item->category_id == 22){
                            $categoryName = 'PENDAPATAN MEAL EKSTERNAL';
                        }

                        if($item->category_id == 23){
                            $categoryName = 'PENDAPATAN MEAL INTERNAL';
                        }

                        if($item->category_id == 25){
                            $categoryName = 'PENJUALAN LIQUOR';
                            $hppName = 'HPP - LIQUOR';
                            $persediaanName = 'PERSEDIAAN LIQUOR';
                        }

                        if(str_contains($item->name, 'NURU')){
                            $categoryName = 'PENJUALAN NURU';
                            $hppName = 'HPP - NURU';
                            $persediaanName = 'PERSEDIAAN NURU';
                        }

                        if(str_contains($item->name, 'ICE CREAM LICK SENSATION')){
                            $categoryName = 'PENJUALAN ICE CREAM LICK SENSATION';
                            $hppName = 'HPP ICE CREAM LICK SENSATION';
                            $persediaanName = 'PERSEDIAAN ICE CREAM LICK SENSATION';
                        }
                    }
                    else if($branchId >= 6){
                        $hppName = 'Beban Pokok Penjualan';
                        $categoryName = 'Pendapatan';
                        $persediaanName = 'Persediaan';
                        if($item->category_id == 2){
                            $persediaanName = 'PERSEDIAAN - F&B';
                            $categoryName = 'PENJUALAN F&B';
                            $hppName = 'BEBAN POKOK PENJUALAN - F&B';
                        }

                        if($item->name == 'OIL THERAPY ( HOT )'){
                            $categoryName = 'PENDAPATAN OIL THERAPY (HOT)';
                            $persediaanName = 'PERSEDIAAN - OIL THERAPI (HOT)';
                            $hppName = 'BEBAN POKOK PENJUALAN - OIL THERAPI (HOT)';
                        }
                        else if(str_contains($item->name, 'TS')){
                            $categoryName = 'Biaya Tambahan Pelanggan';
                        }
                        //check for HPP
                        // if($item->category_id == 2){
                        //     $hppName = 'BEBAN POKOK PENJUALAN - F&B';
                        // }
                    }
                    else{
                        $hppName = 'HPP Lain-lain';
                        $categoryName = 'Penjualan Lain-lain';
                        $persediaanName = 'Persediaan Lain-lain';

                        if(str_contains($item->name, 'KIMO')){
                            $categoryName = 'Penjualan Kimo';
                            $hppName = 'HPP Kimo';
                            $persediaanName = 'Persediaan Kimo';
                        }
                        else if(str_contains($item->name, 'SCRUB')){
                            $categoryName = 'Pendapatan Scrub';
                        }
                        else if(str_contains($item->name, 'OIL')){
                            $categoryName = 'Pendapatan Oil';
                        }
                        else if(str_contains($item->name, 'HOT STONE')){
                            $categoryName = 'Pendapatan Hot Stone';
                        }
                        else if(str_contains($item->name, 'HOTSTONE')){
                            $categoryName = 'Pendapatan Hot Stone';
                        }

                        if($item->category_id == 2){
                            $categoryName = 'Penjualan F&B';
                            $hppName = 'HPP F&B';
                            $persediaanName = 'Persediaan F&B';
                        }

                        if($item->category_id == 20){
                            $categoryName = 'Penjualan Kimo';
                            $hppName = 'HPP Kimo';
                            $persediaanName = 'Persediaan Kimo';
                        }
                    }

                    $idx = 0;
                    foreach ($tmpJournalData as $map){
                        // if($map['coa_name'] == $categoryName){
                        //     $map['amount'] += (($dt->price * $dt->qty) - $dt->discount_amount);
                        // }
                        // else if($map['coa_name'] == $hppName){
                        //     $map['amount'] += (($item->cost_price * $dt->qty) - $dt->discount_amount);
                        // }
                        // else if($map['coa_name'] == $persediaanName){
                        //     $map['amount'] += (($item->cost_price * $dt->qty) - $dt->discount_amount);
                        // }

                        if($map['coa_name'] == $categoryName){
                            $map['amount'] += (($dt->price * $dt->qty));
                        }
                        if($item->category_id != 21 && !str_contains($item->name, 'TS')){
                            if($map['coa_name'] == $hppName){
                                $map['amount'] += (($item->cost_price * $dt->qty));
                            }
                            else if($map['coa_name'] == $persediaanName){
                                $map['amount'] += (($item->cost_price * $dt->qty));
                            }
                        }
                        $tmpJournalData[$idx] = $map;
                        $idx++;
                    }
                }
            }

            foreach ($tmpJournalData as $nJournal){
                if($nJournal['amount'] > 0){
                    $coa = Coa::where('id', $nJournal['coa_id'])->first();
                    if($coa != null){
                        //insert journal
                        $type = 'KREDIT';
                        if($nJournal['journal_type'] == 'DEBET'){
                            $type = 'DEBET';
                        }
                        $notes = 'Automate - Transaksi ' . $header->code;
                        JournalUtilities::insertRecord($jHeader, $branchId, $coa, $type, $nJournal['amount'], $notes, $header->code);
                    }
                }
            }

            if(!$isFoc && $header->discount > 0){
                $discountMap = JournalMap::where('branch_id', $branchId)->where('transaction_type', 'POS_POTONGAN')->first();
                if($discountMap != null){
                    $coa = Coa::where('id', $discountMap->coa_id)->first();
                    if($coa != null){
                        $notes = 'Diskon Transaksi ' . $header->discount;
                        JournalUtilities::insertRecord($jHeader, $branchId, $coa, 'DEBET', $header->discount, $notes, $header->code);
                    }
                }
            }

            // $jHeader->status_id = 13;
            // $jHeader->is_approve = 1;
            // $jHeader->save();

            //Session::flash('success', 'Sukses approve jurnal transaksi '. $header->code);
            return true;
        }
        catch (\Exception $ex) {
            Log::channel('pos')->error('Journal Utilities - processJournal ' . $ex);
            return false;
        }
    }
}
