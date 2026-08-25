<?php

namespace App\Libs;

use App\Models\BookingHeader;
use App\Models\CommissionPartnerHistory;
use App\Models\CommissionPartnerLevelItem;
use App\Models\CommissionPartnerLevelPackage;
use App\Models\Item;
use App\Models\Package;
use App\Models\Partner;
use App\Models\TransactionDetailPartner;
use App\Models\TransactionHeader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommissionUtilities
{
    public static function generatePartnerCommission($trxId){
        $trxHeader = TransactionHeader::with(['transaction_details', 'transaction_details.item'])->find($trxId);

        // Check for app transaction
        $bookingHeader = BookingHeader::where('transaction_id', '=', $trxHeader->id)->first();
        $isAppTransaction = !empty($bookingHeader);

        $treatmentCount = 0;
        // $tmpPartner = null;
        foreach ($trxHeader->transaction_details as $detail){
            $branchId = $trxHeader->branch_id;
            if(!empty($detail->package_id)){
                $package = Package::where('id', $detail->package_id)->first();
                $detailPartners = TransactionDetailPartner::where('transaction_detail_id', $detail->id)->get();

                foreach($detailPartners as $detailPartner){
                    $partner = Partner::find($detailPartner->partner_id);
                    $partnerType = $partner->type;
                    // $tmpPartner = $partner;

                    if($package->partner_count > 1){
                        $packagePrice = $detail->price / $package->partner_count;
                    }
                    else{
                        $packagePrice = $detail->price;
                    }

                    $commissionPercentage = 0;
                    $commissionFlat = 0;
                    if($isAppTransaction && $bookingHeader->type === 'home_service'){
                        if($trxHeader->branch_id == 6){
                            $partnerType = 'level 3';
                        }
                        elseif($trxHeader->branch_id == 7){
                            $partnerType = 'level HS';
                        }
                    }

                    $partnerCommissionLevel = CommissionPartnerLevelPackage::where('branch_id', '=', $branchId)
                            ->where('partner_level', '=', $partnerType)
                            ->where('package_id', '=', $detail->package_id)
                            ->where('status_id', '=', 1)
                            ->first();

                        if(empty($partnerCommissionLevel)) continue;

                        // Check for treatment commission
                        if($partnerCommissionLevel->commission_percentage > 0){
                            $commissionPercentage = $partnerCommissionLevel->commission_percentage;

                            $commissionAdd = ($commissionPercentage / 100) * $packagePrice;
                        }
                        elseif($partnerCommissionLevel->commission_flat > 0){
                            $commissionFlat = $partnerCommissionLevel->commission_flat;
                            $commissionAdd = $commissionFlat;
                        }

                    if($commissionAdd > 0){
                        $pCHistory = new CommissionPartnerHistory();
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

                        if($isAppTransaction){
                            $pCHistory->udf_01 = $bookingHeader->type;
                        }

                        $pCHistory->save();
                    }
                }

                $treatmentCount++;
            }
        }

        // if($treatmentCount < 2 && !empty($tmpPartner)){
        //     foreach ($trxHeader->transaction_details as $detail){
        //         if(!empty($detail->item_id)){
        //             if(str_contains($detail->item->name, 'EXTEND')){
        //                 $commissionAdd = ($detail->item->price / 100) * $tmpPartner->commission_percentage;

        //                 $pCHistory = new CommissionPartnerHistory();
        //                 $pCHistory->branch_id = $trxHeader->branch_id;
        //                 $pCHistory->partner_id = $tmpPartner->id;
        //                 $pCHistory->transaction_id = $trxHeader->id;
        //                 $pCHistory->transaction_code = $trxHeader->code;
        //                 $pCHistory->transaction_date = $trxHeader->created_at;
        //                 $pCHistory->commission_percentage = $tmpPartner->commission_percentage;
        //                 $pCHistory->partner_name = $tmpPartner->name;
        //                 $pCHistory->partner_number = $tmpPartner->number;
        //                 $pCHistory->partner_type = $tmpPartner->type;
        //                 $pCHistory->partner_alias = $tmpPartner->alias;
        //                 $pCHistory->package_name = $detail->item->name;
        //                 $pCHistory->item_id = $detail->item_id;
        //                 $pCHistory->item_name = $detail->item->name;
        //                 $pCHistory->item_code = $detail->item->code;
        //                 $pCHistory->commission_add = $commissionAdd;
        //                 $pCHistory->commission_result = $commissionAdd;
        //                 $pCHistory->save();
        //             }
        //         }
        //     }
        // }

        return 1;
    }

    public static function generateItemCommission($trxId){
        $trxHeader = TransactionHeader::with(['transaction_details', 'transaction_details.item'])->find($trxId);
        $branchId = $trxHeader->branch_id;

        // Check for app transaction
        $bookingHeader = BookingHeader::where('transaction_id', '=', $trxHeader->id)->first();
        $isAppTransaction = !empty($bookingHeader);

        $itemCommissions = CommissionPartnerLevelItem::where('branch_id', '=', $branchId)
            ->pluck('item_id')
            ->unique()
            ->toArray();

        foreach ($trxHeader->transaction_details as $detail){
            if(!in_array($detail->item_id, $itemCommissions)){
                continue;
            }

            $partnerType = $detail->partner_level;
            if(!empty($isAppTransaction) &&
                $bookingHeader->type === 'home_service'){
                if($branchId == 6){
                    Log::channel('debug')->info($detail->item_id);
                    $partnerType = 'level 3';
                }
                elseif($branchId == 7){
                    Log::channel('debug')->info($detail->item_id);
                    $partnerType = 'level HS';
                }
            }

            $itemCommission = DB::table('commission_partner_level_items')
                // ->where('branch_id', '=', $branchId)
                ->where('partner_level', '=', $partnerType)
                ->where('item_id', '=', $detail->item_id)
                ->first();

            // Check for item commission value
            $commissionPercentage = 0;
            $commissionFlat = 0;
            $commissionAdd = 0;
            if($itemCommission->commission_percentage > 0){
                $commissionPercentage = $itemCommission->commission_percentage;
                $commissionAdd = ($commissionPercentage / 100) * $detail->price;
            }
            elseif($itemCommission->commission_flat > 0){
                $commissionFlat = $itemCommission->commission_flat;
                $commissionAdd = $commissionFlat;
            }

            if($commissionAdd > 0){
                $pCHistory = new CommissionPartnerHistory();
                $pCHistory->branch_id = $branchId;
                $pCHistory->partner_id = $detail->partner_id;

                $partner = Partner::find($detail->partner_id);
                $pCHistory->partner_name = $partner->name;
                $pCHistory->partner_number = $partner->number;
                $pCHistory->partner_type = $partner->type;
                $pCHistory->partner_alias = $partner->alias;

                $pCHistory->transaction_id = $trxHeader->id;
                $pCHistory->transaction_code = $trxHeader->code;
                $pCHistory->transaction_date = $trxHeader->created_at;

                $item = Item::find($detail->item_id);
                $pCHistory->item_id = $item->id;
                $pCHistory->item_code = $item->code;
                $pCHistory->item_name = $item->name;

                $pCHistory->commission_percentage = $commissionPercentage;
                $pCHistory->commission_flat = $commissionFlat;
                $pCHistory->commission_add = $commissionAdd;
                $pCHistory->commission_result = $commissionAdd;

                $pCHistory->udf_01 = $isAppTransaction ? $bookingHeader->type : '';
                $pCHistory->udf_02 = $item->is_addon ? 'addon' : '';

                $pCHistory->save();
            }
        }

        return 1;
    }
}
