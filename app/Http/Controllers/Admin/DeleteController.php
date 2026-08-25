<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Libs\Utilities;
use App\Models\InventoryHistory;
use App\Models\Item;
use App\Models\JournalDetail;
use App\Models\JournalHeader;
use App\Models\Partner;
use App\Models\Room;
use App\Models\TransactionDetail;
use App\Models\TransactionDetailPartner;
use App\Models\TransactionHeader;
use App\Models\TransactionPayment;
use Exception;
use Illuminate\Http\Request;

class DeleteController extends Controller
{
    public function deleteTrx(Request $request){
        try{
            if($request->query('code') != 'repotnya'){
                return 'Not Authorized!';
            }
            $header = TransactionHeader::where('id', $request->query('trx_id'))->first();
            if($header == null){
                return 'Transaction not found!';
            }                                   
            
            //delete journal
            $journalHeader = JournalHeader::where('notes', 'like', '%' . $header->code . '%')->first();
            if($journalHeader != null){
                $journalDetails = JournalDetail::where('header_id', $journalHeader->id)->get();
                //delete details
                foreach($journalDetails as $dt)
                {
                    $dt->delete();
                }
                $journalHeader->delete();
            }

            //delete transaction details
            $details = TransactionDetail::where('header_id', $header->id)->get();
            foreach($details as $dt){
                //partner stuff
                $tdps = TransactionDetailPartner::where('transaction_detail_id', $dt->id)->get();
                foreach($tdps as $tdp){
                    $partner = Partner::where('id', $tdp->partner_id)->first();
                    $partner->status_id = 5;
                    $partner->save();
                    $tdp->delete();
                }
                $room = Room::where('id', $dt->room_id)->first();
                $room->status_id = 5;
                $room->save();
                if($dt->item_id != null){
                    //item stuff
                    $item = Item::where('id', $dt->item_id)->first();
                    //update inventories
                    $warehouse = InventoryHistory::where('item_id', $item->id)->where('reference', $header->code)->first();
                    $inventories = 
                    Utilities::updateStock($header->branch_id,
                    'add',
                    $dt->qty,
                    $dt->item_id,
                    $warehouse->warehouse_id,
                    $header->code,
                    'Penghapusan Transaksi');
                }
                $dt->delete();
            }
            $payments = TransactionPayment::where('header_id', $header->id)->get();
            foreach($payments as $payment){
                $payment->delete();
            }
            $header->delete();
            //dd($header, $details, $journalHeader, $journalDetails);
            return 'Success!';
        }
        catch(Exception $ex){
            return $ex;
        }
    }
}