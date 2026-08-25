<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Libs\Utilities;
use App\Models\ApDetail1;
use App\Models\ApDetail2;
use App\Models\ApHeader;
use App\Models\BankCashinDetail;
use App\Models\BankCashinHeader;
use App\Models\BankCashoutDetail;
use App\Models\BankCashoutHeader;
use App\Models\Configuration;
use App\Models\GoodReceiveDetail;
use App\Models\GoodReceiveHeader;
use App\Models\InternalRequestDetail;
use App\Models\InternalRequestHeader;
use App\Models\Inventory;
use App\Models\InventoryHistory;
use App\Models\InventoryInDetail;
use App\Models\InventoryInHeader;
use App\Models\InventoryOutDetail;
use App\Models\InventoryOutHeader;
use App\Models\Item;
use App\Models\JournalDetail;
use App\Models\JournalHeader;
use App\Models\PurchaseOrderDetail;
use App\Models\PurchaseOrderHeader;
use App\Models\PurchaseRequestDetail;
use App\Models\PurchaseRequestHeader;
use App\Models\RevisionLog;
use App\Models\SupplierInvoiceDetail1;
use App\Models\SupplierInvoiceDetail2;
use App\Models\SupplierInvoiceHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RevisionController extends Controller
{
    public function getPassword(Request $request){
        $passwords = Configuration::select('configurations.value as password', 'branches.name as branch')
            ->leftJoin('branches', 'configurations.branch_id', '=', 'branches.id')
            ->where('configurations.key', 'cancel_password')
            ->get();

        return view('admin.revision.password', compact('passwords'));
    }


    public function reset(Request $request){
        try {
            if($request->get('hokdev') != 'titties'){
                return "";
            }
            RevisionLog::create([
                'code' => "-",
                'caused_by' => $request->get('requester'),
                'description' => 'START RESET PROCESS'
            ]);

            switch ($request->get('type')){
                case 'AP':
                    $this->resetAP($request->get('code'), $request->get('requester'));
                    break;
                case 'SI':
                    $this->resetSI($request->get('code'), $request->get('requester'));
                    break;
                case 'GR':
                    $this->resetGR($request->get('code'), $request->get('requester'));
                    break;
                case 'PO':
                    $this->resetPO($request->get('code'), $request->get('requester'));
                    break;
                case 'PR':
                    $this->resetPR($request->get('code'), $request->get('requester'));
                    break;
                case 'CO':
                    $this->resetCO($request->get('code'), $request->get('requester'));
                    break;
                case 'CI':
                    $this->resetCI($request->get('code'), $request->get('requester'));
                    break;
                case 'IR':
                    $this->resetIR($request->get('code'), $request->get('requester'));
                    break;
                case 'II':
                    $this->resetII($request->get('code'), $request->get('requester'));
                    break;
                case 'IO':
                    $this->resetIO($request->get('code'), $request->get('requester'));
                    break;
            }
            RevisionLog::create([
                'code' => "-",
                'caused_by' => $request->get('requester'),
                'description' => 'END RESET PROCESS'
            ]);

            return "OK";
        } catch (\Exception $exception){
            Log::error($exception);

            return $exception;
        }
    }

    public function rollback(Request $request){
        try {
            if($request->get('hokdev') != 'titties'){
                return "";
            }

            $deleteRequest = $request->has('delete');
            RevisionLog::create([
                'code' => "-",
                'caused_by' => $request->get('requester'),
                'description' => 'START ROLLBACK PROCESS'
            ]);

            switch ($request->get('type')){
                case 'AP':
                    $this->resetAP($request->get('code'), $request->get('requester'), $deleteRequest);
                    break;
                case 'SI':
                    $ap = [];
                    $apDetails = ApDetail1::where('supplier_invoice_code', $request->get('code'))->get();
                    foreach ($apDetails as $detail){
                        $ap[] = $detail->header_code;
                    }

                    $ap = array_unique($ap);

                    foreach ($ap as $invoice){
                        $this->resetAP($invoice, $request->get('requester'), true);
                    }

                    $this->resetSI($request->get('code'), $request->get('requester'), $deleteRequest);
                    break;
                case 'GR':
                    $ap = [];
                    $si = [];

                    $siDetails = SupplierInvoiceDetail1::where('gr_code', $request->get('code'))->get();
                    foreach ($siDetails as $detail){
                        $si[] = $detail->header_code;
                    }

                    $si = array_unique($si);

                    $apDetails = ApDetail1::whereIn('supplier_invoice_code', $si)->get();
                    foreach ($apDetails as $detail){
                        $ap[] = $detail->header_code;
                    }

                    $ap = array_unique($ap);

                    foreach ($ap as $invoice){
                        $this->resetAP($invoice, $request->get('requester'), true);
                    }

                    foreach ($si as $invoice){
                        $this->resetSI($invoice, $request->get('requester'), true);
                    }

                    $this->resetGR($request->get('code'), $request->get('requester'), $deleteRequest);
                    break;
                case 'PO':
                    $ap = [];
                    $si = [];
                    $gr = [];

                    $grDetails = GoodReceiveHeader::where('po_code', $request->get('code'))
                        ->get();
                    foreach ($grDetails as $detail){
                        $gr[] = $detail->code;
                    }

                    $gr = array_unique($gr);

                    $siDetails = SupplierInvoiceDetail1::whereIn('gr_code', $gr)
                        ->get();
                    foreach ($siDetails as $detail){
                        $si[] = $detail->header_code;
                    }

                    $si = array_unique($si);

                    $apDetails = ApDetail1::whereIn('supplier_invoice_code', $si)
                        ->get();
                    foreach ($apDetails as $detail){
                        $ap[] = $detail->header_code;
                    }

                    $ap = array_unique($ap);

                    foreach ($ap as $invoice){
                        $this->resetAP($invoice, $request->get('requester'), true);
                    }

                    foreach ($si as $invoice){
                        $this->resetSI($invoice, $request->get('requester'), true);
                    }

                    foreach ($gr as $invoice){
                        $this->resetGR($invoice, $request->get('requester'), true);
                    }

                    $this->resetPO($request->get('code'), $request->get('requester'), $deleteRequest);
                    break;
                case 'PR':
                    // TEST
                    $ap = [];
                    $si = [];
                    $gr = [];
                    $po = [];

                    $poDetails = PurchaseOrderHeader::where('reference_code', $request->get('code'))
                        ->where('status_id', '=', 13)
                        ->get();
                    foreach ($poDetails as $detail){
                        $po[] = $detail->code;
                    }

                    $po = array_unique($po);

                    $grDetails = GoodReceiveHeader::whereIn('po_code', $po)
                        ->where('status_id', '=', 13)
                        ->get();
                    foreach ($grDetails as $detail){
                        $gr[] = $detail->code;
                    }

                    $gr = array_unique($gr);

                    $siDetails = SupplierInvoiceDetail1::whereIn('gr_code', $gr)
                        ->get();
                    foreach ($siDetails as $detail){
                        $si[] = $detail->header_code;
                    }

                    $si = array_unique($si);

                    $apDetails = ApDetail1::whereIn('supplier_invoice_code', $si)
                        ->whereHas('ap_header', function($query){
                            $query->where('status_id', '=', 13);
                        })
                        ->get();
                    foreach ($apDetails as $detail){
                        $ap[] = $detail->header_code;
                    }

                    $ap = array_unique($ap);

                    foreach ($ap as $invoice){
                        $this->resetAP($invoice, $request->get('requester'), true);
                    }

                    foreach ($si as $invoice){
                        $this->resetSI($invoice, $request->get('requester'), true);
                    }

                    foreach ($gr as $invoice){
                        $this->resetGR($invoice, $request->get('requester'), true);
                    }

                    foreach ($po as $invoice){
                        $this->resetPO($invoice, $request->get('requester'), true);
                    }

                    $this->resetPR($request->get('code'), $request->get('requester'), $deleteRequest);
                    break;
            }
            RevisionLog::create([
                'code' => "-",
                'caused_by' => $request->get('requester'),
                'description' => 'END ROLLBACK PROCESS'
            ]);

            return "OK";
        } catch (\Exception $exception){
            Log::error($exception);

            return $exception;
        }
    }





    public function resetAP($invoice, $requester, $delete = false){
        $header = ApHeader::where('code', $invoice)->first();

        $header->update([
            'is_approve' => 0,
            'status_id' => 1,
        ]);

        $journalHeaders = [];
        $journals = JournalDetail::where('reference_code', $invoice)->get();
        foreach ($journals as $journal){
            $journalHeaders[] = $journal->header_id;
            $journal->delete();
        }

        JournalHeader::whereIn('id', array_unique($journalHeaders))->delete();
        RevisionLog::create([
            'code' => $invoice,
            'caused_by' => $requester,
            'description' => 'Delete Jurnal From AP '.$invoice
        ]);

        RevisionLog::create([
            'code' => $invoice,
            'caused_by' => $requester,
            'description' => $delete ? 'Hapus Acc Payment' : 'Reset status Acc Payment'
        ]);

        if($delete){
            ApDetail1::where('header_id', $header->id)->delete();
            ApDetail2::where('header_id', $header->id)->delete();
            $header->delete();
        }
    }

    public function resetSI($invoice, $requester, $delete = false){
        $header = SupplierInvoiceHeader::where('code', $invoice)->first();

        $header->update([
            'is_approve' => 0,
            'status_id' => 1,
        ]);

        RevisionLog::create([
            'code' => $invoice,
            'caused_by' => $requester,
            'description' => $delete ? 'Hapus Supplier Invoice' : 'Reset status Supplier Invoice'
        ]);

        if($delete){
            SupplierInvoiceDetail1::where('header_id', $header->id)->delete();
            SupplierInvoiceDetail2::where('header_id', $header->id)->delete();
            $header->delete();
        }
    }

    public function resetGR($invoice, $requester, $delete = false){
        $header = GoodReceiveHeader::where('code', $invoice)->first();
        $isRollback = $header->status_id == 13;

        $header->update([
            'is_approve' => 0,
            'status_id' => 1,
        ]);

        $journalHeaders = [];
        $journals = JournalDetail::where('reference_code', $invoice)->get();
        foreach ($journals as $journal){
            $journalHeaders[] = $journal->header_id;
            $journal->delete();
        }

        JournalHeader::whereIn('id', array_unique($journalHeaders))->delete();

        if($isRollback){
            foreach ($header->good_receive_details as $detail){
                $inventory = Inventory::where('branch_id', $header->branch_id)
                    ->where('item_id', $detail->item_id)
                    ->where('warehouse_id', $header->warehouse_id)
                    ->first();

                if($inventory){
                    $newQty = $inventory->qty - $detail->qty;
                    $inventory->update([
                        'qty' => $newQty
                    ]);

                    InventoryHistory::create([
                        'branch_id' => $header->branch_id,
                        'branch_name' => $header->branch->name,
                        'warehouse_id' => $header->warehouse_id,
                        'warehouse_name' => $header->warehouse->name,
                        'item_id' => $detail->item_id,
                        'item_code' => $detail->item->code,
                        'item_name' => $detail->item->name,
                        'qty_sub' => $detail->qty,
                        'qty_add' => 0,
                        'qty_result' => $newQty,
                        'reference' => $header->code,
                        'description' => 'Permintaan perubahan data oleh ' . $requester
                    ]);

                    RevisionLog::create([
                        'code' => $invoice,
                        'caused_by' => $requester,
                        'description' => 'Update Inventory History '.$detail->item->code." - ".$detail->item->name
                    ]);
                }
            }
        }

        RevisionLog::create([
            'code' => $invoice,
            'caused_by' => $requester,
            'description' => $delete ? 'Hapus Good Receiving' : 'Reset status Good Receiving'
        ]);

        if($delete){
            GoodReceiveDetail::where('header_id', $header->id)->delete();
            $header->delete();
        }
    }

    public function resetPO($invoice, $requester, $delete = false){
        $header = PurchaseOrderHeader::where('code', $invoice)->first();
        $isRollback = $header->status_id == 13;

        $header->update([
            'is_approve' => 0,
            'status_id' => 1,
            'is_gr' => 0
        ]);

        if($isRollback){
            $details = PurchaseOrderDetail::where('header_id', $header->id)->get();
            foreach($details as $detail){
                $detail->qty_gr = 0;
                $detail->save();

                $detailPr = PurchaseRequestDetail::where('header_id', $header->reference_id)
                    ->where('item_id', $detail->item_id)
                    ->first();
                $detailPr->qty_po = $detailPr->qty_po - $detail->qty;
                $detailPr->save();
            }
        }

        RevisionLog::create([
            'code' => $invoice,
            'caused_by' => $requester,
            'description' => $delete ? 'Hapus Purchase Order' : 'Reset status Purchase Order'
        ]);

        if($delete){
            PurchaseOrderDetail::where('header_id', $header->id)->delete();
            $header->delete();
        }
    }

    public function resetPR($invoice, $requester, $delete = false){
        $header = PurchaseRequestHeader::where('code', $invoice)->first();

        $header->update([
            'is_approve' => 0,
            'status_id' => 1,
        ]);

        $header->is_po = 0;
        $header->save();
        $details = PurchaseRequestDetail::where('header_id', $header->id)->get();
        foreach($details as $detail){
            // $detail->update([
            //     'qty_po' => 0,
            // ]);
            $detail->qty_po = 0;
            $detail->save();
        }

        RevisionLog::create([
            'code' => $invoice,
            'caused_by' => $requester,
            'description' => $delete ? 'Hapus Purchase Request' : 'Reset status Purchase Request'
        ]);

        if($delete){
            PurchaseRequestDetail::where('header_id', $header->id)->delete();
            $header->delete();
        }
    }

    public function resetCO($invoice, $requester, $delete = false){
        $header = BankCashoutHeader::where('code', $invoice)->first();

        $header->update([
            'status_id' => 1,
        ]);

        $journalHeaders = [];
        $journals = JournalDetail::where('reference_code', $invoice)->get();
        foreach ($journals as $journal){
            $journalHeaders[] = $journal->header_id;
            $journal->delete();
        }

        JournalHeader::whereIn('id', array_unique($journalHeaders))->delete();
        RevisionLog::create([
            'code' => $invoice,
            'caused_by' => $requester,
            'description' => 'Delete Jurnal From CO '.$invoice
        ]);

        RevisionLog::create([
            'code' => $invoice,
            'caused_by' => $requester,
            'description' => $delete ? 'Hapus Cash Out' : 'Reset status Cash Out'
        ]);

        if($delete){
            BankCashoutDetail::where('header_id', $header->id)->delete();
            $header->delete();
        }
    }

    public function resetCI($invoice, $requester, $delete = false){
        $header = BankCashinHeader::where('code', $invoice)->first();

        $header->update([
            'status_id' => 1,
        ]);

        $journalHeaders = [];
        $journals = JournalDetail::where('reference_code', $invoice)->get();
        foreach ($journals as $journal){
            $journalHeaders[] = $journal->header_id;
            $journal->delete();
        }

        JournalHeader::whereIn('id', array_unique($journalHeaders))->delete();
        RevisionLog::create([
            'code' => $invoice,
            'caused_by' => $requester,
            'description' => 'Delete Jurnal From CO '.$invoice
        ]);

        RevisionLog::create([
            'code' => $invoice,
            'caused_by' => $requester,
            'description' => $delete ? 'Hapus Cash Out' : 'Reset status Cash Out'
        ]);

        if($delete){
            BankCashinDetail::where('header_id', $header->id)->delete();
            $header->delete();
        }
    }

    public function resetIR($invoice, $requester, $delete = false){
        $header = InternalRequestHeader::where('code', $invoice)->first();

        $header->update([
            'status_id' => 1,
            'is_pr' => 0
        ]);

        RevisionLog::create([
            'code' => $invoice,
            'caused_by' => $requester,
            'description' => $delete ? 'Hapus Internal Request' : 'Reset status Internal Request'
        ]);

        if($delete){
            InternalRequestDetail::where('header_id', $header->id)->delete();
            $header->delete();
        }
    }

    public function resetII($invoice, $requester, $delete = false){
        $header = InventoryInHeader::where('code', $invoice)->first();

        $header->update([
            'status_id' => 1,
        ]);

        foreach ($header->inventory_in_details as $detail){
            $inventory = Inventory::where('branch_id', $header->branch_id)
                ->where('item_id', $detail->item_id)
                ->where('warehouse_id', $header->warehouse_id)
                ->first();

            if($inventory){
                $newQty = $inventory->qty - $detail->qty;
                $inventory->update([
                    'qty' => $newQty
                ]);

                InventoryHistory::create([
                    'branch_id' => $header->branch_id,
                    'branch_name' => $header->branch->name,
                    'warehouse_id' => $header->warehouse_id,
                    'warehouse_name' => $header->warehouse->name,
                    'item_id' => $detail->item_id,
                    'item_code' => $detail->item->code,
                    'item_name' => $detail->item->name,
                    'qty_sub' => $detail->qty,
                    'qty_add' => 0,
                    'qty_result' => $newQty,
                    'reference' => $header->code,
                    'description' => 'Permintaan perubahan data oleh ' . $requester
                ]);
                RevisionLog::create([
                    'code' => $invoice,
                    'caused_by' => $requester,
                    'description' => 'Update Inventory History '.$detail->item->code." - ".$detail->item->name
                ]);
            }
        }

        RevisionLog::create([
            'code' => $invoice,
            'caused_by' => $requester,
            'description' => $delete ? 'Hapus Inventory In' : 'Reset status Inventory In'
        ]);

        if($delete){
            InventoryInDetail::where('header_id', $header->id)->delete();
            $header->delete();
        }
    }

    public function resetIO($invoice, $requester, $delete = false){
        $header = InventoryOutHeader::where('code', $invoice)->first();
        $isRollback = $header->status_id == 13;

        $header->update([
            'status_id' => 1,
        ]);

        $journalHeaders = [];
        $journals = JournalDetail::where('reference_code', $invoice)->get();
        foreach ($journals as $journal){
            $journalHeaders[] = $journal->header_id;
            $journal->delete();
        }

        JournalHeader::whereIn('id', array_unique($journalHeaders))->delete();

        if($isRollback){
            foreach ($header->inventory_out_details as $detail){
                $inventory = Inventory::where('branch_id', $header->branch_id)
                    ->where('item_id', $detail->item_id)
                    ->where('warehouse_id', $header->warehouse_id)
                    ->first();

                if($inventory){
                    $newQty = $inventory->qty + $detail->qty;
                    $inventory->update([
                        'qty' => $newQty
                    ]);

                    InventoryHistory::create([
                        'branch_id' => $header->branch_id,
                        'branch_name' => $header->branch->name,
                        'warehouse_id' => $header->warehouse_id,
                        'warehouse_name' => $header->warehouse->name,
                        'item_id' => $detail->item_id,
                        'item_code' => $detail->item->code,
                        'item_name' => $detail->item->name,
                        'qty_sub' => 0,
                        'qty_add' => $detail->qty,
                        'qty_result' => $newQty,
                        'reference' => $header->code,
                        'description' => 'Permintaan perubahan data oleh ' . $requester
                    ]);
                    RevisionLog::create([
                        'code' => $invoice,
                        'caused_by' => $requester,
                        'description' => 'Update Inventory History '.$detail->item->code." - ".$detail->item->name
                    ]);
                }
            }
        }

        RevisionLog::create([
            'code' => $invoice,
            'caused_by' => $requester,
            'description' => $delete ? 'Hapus Inventory Out' : 'Reset status Inventory Out'
        ]);

        if($delete){
            InventoryOutDetail::where('header_id', $header->id)->delete();
            $header->delete();
        }
    }

    public function adjustStock(Request $request){
        try{
            $itemId = intval($request->query('item_id'));
            $qty = intval($request->query('qty'));
            $whId = intval($request->query('wh_id'));

            $itemDb = Item::find($itemId);

            // Check inv
            $currentInv = Inventory::where('warehouse_id', '=', $whId)
                ->where('item_id', '=', $itemId)
                ->first();

            $adjustMode = 'add';
            if($qty < $currentInv->qty){
                $adjustMode = 'sub';
                $adjustQty = $currentInv->qty - $qty;
            }
            elseif($qty > $currentInv->qty){
                $adjustQty = $qty - $currentInv->qty;
            }
            else{
                return 'SAME QTY!';
            }

            Utilities::updateStock($itemDb->branch_id,
                $adjustMode,
                $adjustQty,
                $itemId,
                $whId,
                'MANUAL',
                'Penyesuaian Stok');

            return 'ADJUST STOCK DONE!';
        }
        catch(\Exception $ex){
            dd($ex);
        }
    }
}
