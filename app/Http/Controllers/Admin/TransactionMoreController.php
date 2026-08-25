<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\Branch;
use App\Models\CommissionPartnerHistory;
use App\Models\CommissionPartnerLevelPackage;
use App\Models\Configuration;
use App\Models\Item;
use App\Models\Package;
use App\Models\Partner;
use App\Models\TransactionDetail;
use App\Models\TransactionDetailPartner;
use App\Models\TransactionHeader;
use App\Transformer\TransactionTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class TransactionMoreController extends Controller
{
    public function fixHappyHour(Request $request){
        try{
            if(!$request->filled('id')){
                Session::flash('error', 'BAD REQUEST!');
                return redirect()->back();
            }

            $trxId = $request->input('id');
            $header = TransactionHeader::find($trxId);
            if(empty($header)){
                Session::flash('error', 'BAD REQUEST!');
                return redirect()->back();
            }

            if($header->is_happy_hour != 1){
                Session::flash('error', 'Transaksi ini tidak Happy Hour!');
                return redirect()->back();
            }

            $user = Auth::guard('admin')->user();
            $userData = AdminUser::find($user->id);
            $details = TransactionDetail::where('header_id', $trxId)->get();

            foreach($details as $dt){
                if($dt->package_id != null){
                    $package = Package::find($dt->package_id);
                    if($package != null){
                        $configuration = Configuration::where('branch_id', $userData->branch_id)
                            ->where('key', 'oil_id')->first();
                        $oil = Item::find($configuration->value_int);

                        $dt->discount_amount = $dt->price * 0.1;
                        $dt->discount_percentage = 10;
                        $dt->subtotal = $dt->price * 0.9;
                        $dt->save();
                    }
                }
            }

            Session::flash('success', 'Sukses fix transaksi Happy Hour '. $header->code);
            return redirect()->route('admin.transaction.show', ['id' => $header->id]);
        }
        catch(\Exception $ex){
            dd($ex);
        }
    }

    public function showChangePartner(Request $request){
        if(!$request->filled('header_id')){
            Session::flash('error', 'BAD REQUEST!');
            return redirect()->back();
        }
        if(!$request->filled('detail_id')){
            Session::flash('error', 'BAD REQUEST!');
            return redirect()->back();
        }
        $user = Auth::guard('admin')->user();

        $allPartners = Partner::where('branch_id', $user->branch_id)
            ->where('number', '!=', '0')
            ->orderBy('number')
            ->get();

        $detail = TransactionDetail::where('id', $request->get('detail_id'))->first();
        $package = Package::where('id', $detail->package_id)->first();
        $partners = [];
        $tdps = TransactionDetailPartner::where('transaction_detail_id', $request->get('detail_id'))->get();
        foreach($tdps as $tdp){
            $partner = Partner::where('id', $tdp->partner_id)->first();
            if($partner != null){
                $partners[] = [
                    'id'        => $partner->id,
                    'number'    => $partner->number,
                    'alias'     => $partner->alias,
                ];
            }
        }

        $dbHeader = TransactionHeader::where('id', $request->get('header_id'))->first();
        $transformer = new TransactionTransformer();
        $header = $transformer->transform($dbHeader);

        $data = [
            'headerId'          => $request->get('header_id'),
            'detailId'          => $request->get('detail_id'),
            'allPartners'       => $allPartners,
            'choosenPartner'    => $partners,
            'package'           => $package,
            'header'            => $header,
        ];

        //dd($data);
        return view('admin.transaction.show_change_partner')->with($data);
    }

    public function changePartner(Request $request){
        try{
            if(!$request->filled('id')){
                Session::flash('error', 'BAD REQUEST!');
                return redirect()->back();
            }

            if(!$request->filled('detailId')){
                Session::flash('error', 'BAD REQUEST!');
                return redirect()->back();
            }

            if(!$request->filled('partnerIds')){
                Session::flash('error', 'BAD REQUEST!');
                return redirect()->back();
            }

            $partnerIds = $request->input('partnerIds');
            $trxId = $request->input('id');
            $header = TransactionHeader::find($trxId);
            if(empty($header)){
                Session::flash('error', 'BAD REQUEST!');
                return redirect()->back();
            }

            //dd($partnerIds, $trxId, $header);

            $branch = Branch::find($header->branch_id);
            $detail = TransactionDetail::find($request->input('detailId'));
            if(empty($detail)){
                Session::flash('error', 'BAD REQUEST!');
                return redirect()->back();
            }

            $tmpTdps = TransactionDetailPartner::where('transaction_detail_id', $detail->id)->get();
            foreach($tmpTdps as $tdp){
                //delete commission
                $commisionHistory = CommissionPartnerHistory::where('transaction_id', $header->id)
                                    ->where('partner_id', $tdp->partner_id)
                                    ->first();
                if($commisionHistory != null){
                    $commisionHistory->delete();
                }
                $tdp->delete();
            }

            $isMain = 1;
            foreach($partnerIds as $partnerId){
                $partner = Partner::with('partner_level')->where('id', $partnerId)->first();
                if($partner != null){
                    //save new partner
                    TransactionDetailPartner::create([
                        'partner_id'            => $partner->id,
                        'transaction_detail_id' => $detail->id,
                        'is_main'               => $isMain,
                        'is_extend'             => 0,
                        'is_checkout'           => 0,
                        'book_name'             => '',
                    ]);
                    $isMain = 0;
                    //count commission
                    //not oura
                    $package = Package::where('id', $detail->package_id)->first();

                    if($branch->is_oura == 0){
                        $commissionAdd = 0;
                        $commissionPercentage = 0;
                        $commissionFlat = 0;
                        $partnerCommission = CommissionPartnerLevelPackage::where('branch_id', $branch->id)
                            ->where('package_id', $detail->package_id)
                            ->where('partner_level', $partner->partner_level->name)
                            ->where('status_id', '=', 1)
                            ->first();
                        if($partnerCommission != null){
                            $commissionPercentage = $partnerCommission->commission_percentage;

                            $afterDeduction = floatval($detail->price) - floatval($partnerCommission->deduction_before);
                            if($partnerCommission->commission_percentage > 0){
                                $commissionAdd = $afterDeduction * floatval($partnerCommission->commission_percentage / 100);
                            }
                            else{
                                $commissionAdd = floatval($partnerCommission->commission_flat) > 0 ? floatval($partnerCommission->commission_flat) : 0;
                                $commissionFlat += $commissionAdd;
                            }

                            if($partnerCommission->divider_after > 0){
                                $commissionAdd = $commissionAdd / $partnerCommission->divider_after;
                            }

                            // Check extend
                            if($detail->extension_amount > 0 && $partnerCommission->extend_flat > 0){
                                $commissionAdd += $partnerCommission->extend_flat;
                                $commissionFlat += $partnerCommission->extend_flat;
                            }
                        }

                        if($commissionAdd > 0){
                            $pCHistory = new CommissionPartnerHistory();
                            $pCHistory->branch_id = $branch->id;
                            $pCHistory->partner_id = $partner->id;
                            $pCHistory->transaction_id = $header->id;
                            $pCHistory->transaction_code = $header->code;
                            $pCHistory->transaction_date = $header->created_at;
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
                    else{
                        $totalTreatmentQty = 0;
                        $totalExtendQty = 0;
                        $totalTreatmentCommission = 0;
                        $totalExtendCommission = 0;

                        $partnerCommissionLevel = CommissionPartnerLevelPackage::where('branch_id', '=', $branch->id)
                                ->where('partner_level', '=', $partner->type)
                                ->where('package_id', '=', $detail->package_id)
                                ->where('status_id', '=', 1)
                                ->first();

                        if($partnerCommissionLevel != null)
                        {
                            $commissionAdd = 0;
                            $commissionPercentage = 0;
                            $commissionFlat = 0;
                            if($partner != null){
                                $totalTreatmentQty++;

                                if($package->partner_count > 1){
                                    $packagePrice = $detail->price / $package->partner_count;
                                }
                                else{
                                    $packagePrice = $detail->price;
                                }

                                // Check for treatment commission
                                if($partnerCommissionLevel->commission_percentage > 0){
                                    $commissionPercentage = $partnerCommissionLevel->commission_percentage;
                                    // if(!empty($detail->online_type) &&
                                    //     $detail->online_type === 'home_service'){
                                    //     $commissionPercentage = $homeServicePercent;
                                    // }

                                    $commissionAdd = ($commissionPercentage / 100) * $packagePrice;
                                }
                                elseif($partnerCommissionLevel->commission_flat > 0){
                                    $commissionFlat = $partnerCommissionLevel->commission_flat;
                                    $commissionAdd = $commissionFlat;
                                }

                                if($commissionAdd > 0){
                                    $totalTreatmentCommission += intval($commissionAdd);

                                    $pCHistory = new CommissionPartnerHistory();
                                    $pCHistory->branch_id = $branch->id;
                                    $pCHistory->partner_id = $partner->id;
                                    $pCHistory->transaction_id = $header->id;
                                    $pCHistory->transaction_code = $header->code;
                                    $pCHistory->transaction_date = $header->created_at;
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
                                    $pCHistory->udf_01 = $detail->online_type ?? '';
                                    $pCHistory->save();
                                }
                            }
                        }
                    }
                }
            }

            Session::flash('success', 'Sukses mengganti Therapis pada Transaksi '. $header->code);
            return redirect()->route('admin.transaction.show', ['id' => $header->id]);
        }
        catch(\Exception $ex){
            dd($ex);
        }
    }

    public function deleteHappyHour(Request $request){
        try{
            $headerId = $request->input('id');
            $header = TransactionHeader::where('id', $headerId)->first();
            if($header == null){
                Session::flash('error', 'Transaksi tidak ditemukan!');
                return redirect()->route('admin.transaction.index');
            }

            $user = Auth::guard('admin')->user();
            if($user->branch_id == 1){
                Session::flash('error', 'Emperor tidak boleh hapus Happy Hour!');
                return redirect()->route('admin.transaction.show', ['id' => $header->id]);
            }

            $header->is_happy_hour = 0;
            $header->save();

            Session::flash('success', 'Sukses menghapus Happy Hour!');
            return redirect()->route('admin.transaction.show', ['id' => $header->id]);
        }
        catch(\Exception $ex){
            dd($ex);
        }
    }
}
