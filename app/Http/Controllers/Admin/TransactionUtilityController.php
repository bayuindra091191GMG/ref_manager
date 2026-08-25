<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Libs\Utilities;
use App\Models\ApDetail1;
use App\Models\ApDetail2;
use App\Models\ApHeader;
use App\Models\ArDetail1;
use App\Models\ArDetail2;
use App\Models\ArHeader;
use App\Models\BankCashinDetail;
use App\Models\BankCashinHeader;
use App\Models\BankCashoutDetail;
use App\Models\BankCashoutHeader;
use App\Models\Configuration;
use App\Models\DeliveryOrderDetail;
use App\Models\DeliveryOrderHeader;
use App\Models\GoodReceiveDetail;
use App\Models\GoodReceiveHeader;
use App\Models\InternalRequestDetail;
use App\Models\InternalRequestHeader;
use App\Models\Inventory;
use App\Models\InventoryHistory;
use App\Models\InventoryInHeader;
use App\Models\InventoryOutHeader;
use App\Models\Item;
use App\Models\ItemTransferHeader;
use App\Models\ItemUnit;
use App\Models\JournalDetail;
use App\Models\JournalHeader;
use App\Models\ManufactureOrderHeader;
use App\Models\PurchaseOrderDetail;
use App\Models\PurchaseOrderHeader;
use App\Models\PurchaseRequestDetail;
use App\Models\PurchaseRequestHeader;
use App\Models\RequestForQuotationHeader;
use App\Models\RevisionLog;
use App\Models\SalesInvoiceDetail;
use App\Models\SalesInvoiceDP;
use App\Models\SalesInvoiceHeader;
use App\Models\SalesInvoiceItemDetail;
use App\Models\SalesOrderDetail;
use App\Models\SalesOrderHeader;
use App\Models\SupplierInvoiceDetail1;
use App\Models\SupplierInvoiceDetail2;
use App\Models\SupplierInvoiceHeader;
use App\Transformer\RevisionLogTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Webpatser\Uuid\Uuid;
use Yajra\DataTables\Facades\DataTables;

class TransactionUtilityController extends Controller
{
    public function editSetting(){
        $data = Configuration::where('is_editable', 1)
            ->whereNotIn('key', ['tnc_booking', 'tnc_payment'])
            ->orderBy('sequence')
            ->get();

        $tncBooking = Configuration::firstOrNew(['key' => 'tnc_booking']);
        $tncPayment = Configuration::firstOrNew(['key' => 'tnc_payment']);

        return view('admin.setting_system', compact('data', 'tncBooking', 'tncPayment'));
    }
    public function updateSetting(Request $request){
        try{
            $ids = $request->input('setting_id', []);
            $values = $request->input('setting_value', []);
            $valueInts = $request->input('setting_value_int', []);

            $ct = 0;
            foreach($ids as $id){
                $config = Configuration::find($id);
                if(!$config){
                    $ct++;
                    continue;
                }

                if($config->key == 'spv_password'){
                    $newPassword = trim($values[$ct]);
                    if(empty($values[$ct])) {
                        $ct++;
                        continue;
                    }
                    $config->value = Hash::make($newPassword);
                }
                else{
                    $config->value = $values[$ct];
                    $config->value_int = $valueInts[$ct];
                }
                $config->save();
                $ct++;
            }

            Configuration::updateOrCreate(
                ['key' => 'tnc_booking'],
                [
                    'value' => $request->input('tnc_booking'),
                    'value_int' => null,
                ]
            );

            Configuration::updateOrCreate(
                ['key' => 'tnc_payment'],
                [
                    'value' => $request->input('tnc_payment'),
                    'value_int' => null,
                ]
            );

            Session::flash('success', 'Sukses mengubah General Settings');
            return redirect()->route('admin.setting_system.edit');
        }
        catch (\Exception $ex){
            Session::flash('error', 'Maaf kesalahan terjadi! ' . $ex);
            return back()->withInput($request->all());
        }
    }
    public function index(){
        return view('admin.transaction_utility.index');
    }

    public function checkAction(Request $request){
        try {
            $status = '';
            $action = [];
            $check = null;
            if($request->get('type') == 'PO' || $request->get('type') == 'SO'){
                if($request->get('type') == 'PO'){
                    $check = PurchaseOrderHeader::on(session()->get('connection_name'))->where('code', $request->get('code'))
                        ->first();
                }
                else {
                    $check = SalesOrderHeader::on(session()->get('connection_name'))->where('code', $request->get('code'))
                        ->first();
                }

                if($check){
                    if($check->status_id == 1){
                        $status = 'Aktif';
                        $action = ['Void'];
                    }
                    else if($check->status_id == 6){
                        $status = 'Approved';
                        $action = ['Unapprove', 'Close'];
                    }
                    else if($check->status_id == 8){
                        $status = 'Void';
                        $action = ['Unvoid'];
                    }
                    else if($check->status_id == 9){
                        $status = 'Closed';
                        $action = ['Reopen'];
                    }
                }
            }
            else {
                if($request->get('type') == 'MR'){
                    $check = InternalRequestHeader::on(session()->get('connection_name'))->where('code', $request->get('code'))
                        ->first();
                }
                else if($request->get('type') == 'PR'){
                    $check = PurchaseRequestHeader::on(session()->get('connection_name'))->where('code', $request->get('code'))
                        ->first();
                }
                else if($request->get('type') == 'RFQ'){
                    $check = RequestForQuotationHeader::on(session()->get('connection_name'))->where('code', $request->get('code'))
                        ->first();
                }
                else if($request->get('type') == 'GR'){
                    $check = GoodReceiveHeader::on(session()->get('connection_name'))->where('code', $request->get('code'))
                        ->first();
                }
                else if($request->get('type') == 'PI'){
                    $check = SupplierInvoiceHeader::on(session()->get('connection_name'))->where('code', $request->get('code'))
                        ->first();
                }
                else if($request->get('type') == 'DO'){
                    $check = DeliveryOrderHeader::on(session()->get('connection_name'))->where('code', $request->get('code'))
                        ->first();
                }
                else if($request->get('type') == 'SID'){
                    $check = SalesInvoiceDP::on(session()->get('connection_name'))->where('code', $request->get('code'))
                        ->first();
                }
                else if($request->get('type') == 'SI'){
                    $check = SalesInvoiceHeader::on(session()->get('connection_name'))->where('code', $request->get('code'))
                        ->first();
                }
                else if($request->get('type') == 'AP'){
                    $check = ApHeader::on(session()->get('connection_name'))->where('code', $request->get('code'))
                        ->first();
                }
                else if($request->get('type') == 'AR'){
                    $check = ArHeader::on(session()->get('connection_name'))->where('code', $request->get('code'))
                        ->first();
                }
                else if($request->get('type') == 'CO'){
                    $check = BankCashoutHeader::on(session()->get('connection_name'))->where('code', $request->get('code'))
                        ->first();
                }
                else if($request->get('type') == 'CI'){
                    $check = BankCashinHeader::on(session()->get('connection_name'))->where('code', $request->get('code'))
                        ->first();
                }
                else if($request->get('type') == 'MO'){
                    $check = InventoryOutHeader::on(session()->get('connection_name'))->where('code', $request->get('code'))
                        ->first();
                }
                else if($request->get('type') == 'MI'){
                    $check = InventoryInHeader::on(session()->get('connection_name'))->where('code', $request->get('code'))
                        ->first();
                }

                if($check){
                    if($check->status_id == 1){
                        $status = 'Aktif';
                        $action = ['Void'];
                    }
                    else if($check->status_id == 6){
                        $status = 'Approved';
                        $action = ['Unapprove'];
                    }
                    else if($check->status_id == 8){
                        $status = 'Void';
                        $action = ['Unvoid'];
                    }
                    else {
                        return response([
                            'success' => false,
                            'message' => 'Transaksi tidak dapat dimodifikasi!'
                        ]);
                    }
                }
            }

            if($check == null){
                return response([
                    'success' => false,
                    'message' => 'Transaksi tidak ditemukan!'
                ]);
            }

            return response([
                'success' => true,
                'status' => $status,
                'action' => $action
            ]);
        } catch (\Exception $exception){
            Log::channel('utility')->error($exception);

            return response([
                'success' => false,
                'message' => 'Gagal memeriksa kode!'
            ]);
        }
    }

    public function submit(Request $request){
        try {
            $check = null;
            if($request->input('type') == 'PO' || $request->input('type') == 'SO'){
                if($request->input('type') == 'PO'){
                    $check = PurchaseOrderHeader::on(session()->get('connection_name'))->where('code', $request->input('code'))
                        ->first();
                }
                else {
                    $check = SalesOrderHeader::on(session()->get('connection_name'))->where('code', $request->input('code'))
                        ->first();
                }

                if($check){
                    if($request->input('action') == 'Void'){
                        $check->update([
                            'status_id' => 8
                        ]);
                    }
                    else if($request->input('action') == 'Unvoid'){
                        $check->update([
                            'status_id' => 1
                        ]);
                    }
                    else if($request->input('action') == 'Unapprove'){
                        if($request->input('type') == 'PO'){
                            if(!$this->unapprovePO($check)){
                                return redirect()->back()->withInput()->withErrors([
                                    'Tidak dapat unapprove karena transaksi sudah digunakan!'
                                ]);
                            }
                        }
                        else {
                            if(!$this->unapproveSO($check)){
                                return redirect()->back()->withInput()->withErrors([
                                    'Tidak dapat unapprove karena transaksi sudah digunakan!'
                                ]);
                            }
                        }

                    }
                    else if($request->input('action') == 'Close'){
                        $check->update([
                            'status_id' => 9
                        ]);
                    }
                    else if($request->input('action') == 'Reopen'){
                        $check->update([
                            'status_id' => 6
                        ]);
                    }
                }
            } else {
                if($request->input('type') == 'MR'){
                    $check = InternalRequestHeader::on(session()->get('connection_name'))->where('code', $request->input('code'))
                        ->first();
                }
                else if($request->input('type') == 'PR'){
                    $check = PurchaseRequestHeader::on(session()->get('connection_name'))->where('code', $request->input('code'))
                        ->first();
                }
                else if($request->input('type') == 'RFQ'){
                    $check = RequestForQuotationHeader::on(session()->get('connection_name'))->where('code', $request->input('code'))
                        ->first();
                }
                else if($request->input('type') == 'GR'){
                    $check = GoodReceiveHeader::on(session()->get('connection_name'))->where('code', $request->input('code'))
                        ->first();
                }
                else if($request->input('type') == 'PI'){
                    $check = SupplierInvoiceHeader::on(session()->get('connection_name'))->where('code', $request->input('code'))
                        ->first();
                }
                else if($request->input('type') == 'DO'){
                    $check = DeliveryOrderHeader::on(session()->get('connection_name'))->where('code', $request->input('code'))
                        ->first();
                }
                else if($request->input('type') == 'SID'){
                    $check = SalesInvoiceDP::on(session()->get('connection_name'))->where('code', $request->input('code'))
                        ->first();
                }
                else if($request->input('type') == 'SI'){
                    $check = SalesInvoiceHeader::on(session()->get('connection_name'))->where('code', $request->input('code'))
                        ->first();
                }
                else if($request->get('type') == 'AP'){
                    $check = ApHeader::on(session()->get('connection_name'))->where('code', $request->get('code'))
                        ->first();
                }
                else if($request->get('type') == 'AR'){
                    $check = ArHeader::on(session()->get('connection_name'))->where('code', $request->get('code'))
                        ->first();
                }
                else if($request->get('type') == 'CO'){
                    $check = BankCashoutHeader::on(session()->get('connection_name'))->where('code', $request->get('code'))
                        ->first();
                }
                else if($request->get('type') == 'CI'){
                    $check = BankCashinHeader::on(session()->get('connection_name'))->where('code', $request->get('code'))
                        ->first();
                }
                else if($request->get('type') == 'MO'){
                    $check = InventoryOutHeader::on(session()->get('connection_name'))->where('code', $request->get('code'))
                        ->first();
                }
                else if($request->get('type') == 'MI'){
                    $check = InventoryInHeader::on(session()->get('connection_name'))->where('code', $request->get('code'))
                        ->first();
                }

                if($check){
                    if($request->input('action') == 'Void'){
                        $check->update([
                            'status_id' => 8
                        ]);

                        if($request->input('type') == 'DO'){
                            foreach($check->delivery_order_details as $doDetail){
                                // Unmark qty DO
                                DB::connection(session()->get('connection_name'))->table('sales_order_details')
                                    ->where('id', '=', $doDetail->so_detail_id)
                                    ->decrement('qty_do', $doDetail->qty, [
                                        'updated_at' => Carbon::now('Asia/Jakarta')->toDateTimeString()
                                    ]);
                            }

                            foreach($check->delivery_order_detail2s as $doDetail){
                                // Unmark qty DO
                                DB::connection(session()->get('connection_name'))->table('sales_order_details')
                                    ->where('id', '=', $doDetail->so_detail_id)
                                    ->decrement('qty_bap', $doDetail->qty, [
                                        'updated_at' => Carbon::now('Asia/Jakarta')->toDateTimeString()
                                    ]);
                            }
                        }
                        else if($request->input('type') == 'SI'){
                            $dataDOs = DeliveryOrderHeader::on(session()->get('connection_name'))
                                ->where('selected_by_si_id', $check->id)->get();
                            foreach($dataDOs as $do){
                                $do->update([
                                    'is_close' => 0,
                                    'is_si_selected' => null,
                                    'selected_by_si_id' => null
                                ]);
                            }
                        }
                    }
                    else if($request->input('action') == 'Unvoid'){
                        $check->update([
                            'status_id' => 1
                        ]);
                    }
                    else if($request->input('action') == 'Unapprove'){
                        if($request->input('type') == 'MR'){
                            if(!$this->unapproveMR($check)){
                                $relation = PurchaseRequestHeader::on(session()->get('connection_name'))->where('reference_id', $check->id)
                                    ->first();
                                return redirect()->back()->withInput()->withErrors([
                                    'Tidak dapat unapprove karena transaksi sudah digunakan '. $relation->code
                                ]);
                            }
                        }
                        else if($request->input('type') == 'PR'){
                            if(!$this->unapprovePR($check)){
                                $relation = PurchaseOrderHeader::on(session()->get('connection_name'))->where('reference_id', $check->id)
                                    ->first();
                                return redirect()->back()->withInput()->withErrors([
                                    'Tidak dapat unapprove karena transaksi sudah digunakan!'. $relation->code
                                ]);
                            }
                        }
                        else if($request->input('type') == 'RFQ'){
                            if(!$this->unapproveRFQ($check)){
                                return redirect()->back()->withInput()->withErrors([
                                    'Tidak dapat unapprove karena transaksi sudah digunakan!'
                                ]);
                            }
                        }
                        else if($request->input('type') == 'GR'){
                            if(!$this->unapproveGR($check)){
                                $relation = SupplierInvoiceDetail1::on(session()->get('connection_name'))->where('gr_id', $check->id)
                                    ->first();

                                return redirect()->back()->withInput()->withErrors([
                                    'Tidak dapat unapprove karena transaksi sudah digunakan!'. $relation->header_code
                                ]);
                            }
                        }
                        else if($request->input('type') == 'PI'){
                            if(!$this->unapprovePI($check)){
                                $relation = ApDetail1::on(session()->get('connection_name'))->where('supplier_invoice_id', $check->id)
                                    ->first();
                                return redirect()->back()->withInput()->withErrors([
                                    'Tidak dapat unapprove karena transaksi sudah digunakan!'. $relation->header_code
                                ]);
                            }
                        }
                        else if($request->input('type') == 'DO'){
                            if(!$this->unapproveDO($check)){
                                $relation = SalesInvoiceDetail::on(session()->get('connection_name'))->where('do_id', $check->id)
                                    ->first();
                                return redirect()->back()->withInput()->withErrors([
                                    'Tidak dapat unapprove karena transaksi sudah digunakan!'. $relation->header_code
                                ]);
                            }
                        }
                        else if($request->input('type') == 'SID'){
                            if(!$this->unapproveSID($check)){
                                return redirect()->back()->withInput()->withErrors([
                                    'Tidak dapat unapprove karena transaksi sudah digunakan!'
                                ]);
                            }
                        }
                        else if($request->input('type') == 'SI'){
                            if(!$this->unapproveSI($check)){
                                $relation = ArDetail1::on(session()->get('connection_name'))->where('sales_invoice_id', $check->id)
                                    ->first();
                                return redirect()->back()->withInput()->withErrors([
                                    'Tidak dapat unapprove karena transaksi sudah digunakan!'. $relation->header_code
                                ]);
                            }
                        }
                        else if($request->get('type') == 'AP'){
                            if(!$this->unapproveAP($check)){
                                return redirect()->back()->withInput()->withErrors([
                                    'Tidak dapat unapprove karena transaksi sudah digunakan!'
                                ]);
                            }
                        }
                        else if($request->get('type') == 'AR'){
                            if(!$this->unapproveAR($check)){
                                return redirect()->back()->withInput()->withErrors([
                                    'Tidak dapat unapprove karena transaksi sudah digunakan!'
                                ]);
                            }
                        }
                        else if($request->get('type') == 'CO'){
                            if(!$this->unapproveCO($check)){
                                return redirect()->back()->withInput()->withErrors([
                                    'Tidak dapat unapprove karena transaksi sudah digunakan!'
                                ]);
                            }
                        }
                        else if($request->get('type') == 'CI'){
                            if(!$this->unapproveCI($check)){
                                return redirect()->back()->withInput()->withErrors([
                                    'Tidak dapat unapprove karena transaksi sudah digunakan!'
                                ]);
                            }
                        }
                        else if($request->get('type') == 'MO'){
                            if(!$this->unapproveMO($check)){
                                return redirect()->back()->withInput()->withErrors([
                                    'Tidak dapat unapprove karena transaksi sudah digunakan!'
                                ]);
                            }
                        }
                        else if($request->get('type') == 'MI'){
                            if(!$this->unapproveMI($check)){
                                return redirect()->back()->withInput()->withErrors([
                                    'Tidak dapat unapprove karena transaksi sudah digunakan!'

                                ]);
                            }
                        }
                    }
                }
            }

            if($check == null){
                return redirect()->back()->withInput()->withErrors([
                    'Transaksi tidak ditemukan!'
                ]);
            }

            RevisionLog::on(session()->get('connection_name'))->create([
                'code' => $request->get('code'),
                'caused_by' => Auth::guard('admin')->user()->username,
                'description' => $request->input('action') . " - " . $request->input('type')
            ]);

            Session::flash('success', 'Sukses mengubah data!');
            return redirect()->route('admin.dashboard');
        } catch (\Exception $exception) {
            Log::channel('utility')->error($exception);

            return redirect()->back()->withInput()->withErrors([
                'Internal Server Error!'
            ]);
        }
    }

    private function unapprovePO($po){
        $relation = GoodReceiveHeader::on(session()->get('connection_name'))->where('po_id', $po->id)
            ->first();

        if($relation){
            return false;
        }

        foreach ($po->purchase_order_details as $detail) {
            $prDetail = PurchaseRequestDetail::on(session()->get('connection_name'))->where('item_id', $detail->item_id)->where('header_id', $po->reference_id)->first();
            if (!empty($prDetail)) {
                $prDetail->qty_po = $prDetail->qty_po - $detail->qty;
                $prDetail->save();
            }
        }

        //check on all pr details and update PR
        $prData = PurchaseRequestHeader::on(session()->get('connection_name'))->where('id', $po->reference_id)->first();
        $donePrDtCount = PurchaseRequestDetail::on(session()->get('connection_name'))->where('header_id', $po->reference_id)
            ->whereRaw('purchase_request_details.qty = purchase_request_details.qty_po')
            ->count();
        $prDtCount = PurchaseRequestDetail::on(session()->get('connection_name'))->where('header_id', $po->reference_id)->count();
        if ($donePrDtCount != $prDtCount) {
            $prData->update([
                'is_po' => 0
            ]);
        }

        $po->update([
            'is_approve' => 0,
            'status_id' => 1
        ]);

        return true;
    }

    private function unapproveSO($so){
        $relation = DeliveryOrderHeader::on(session()->get('connection_name'))->where('so_id', $so->id)
            ->first();

        if($relation){
            return false;
        }

        $relation = ManufactureOrderHeader::on(session()->get('connection_name'))->where('so_id', $so->id)
            ->first();

        if($relation){
            return false;
        }

        $so->update([
            'is_approve' => 0,
            'status_id' => 1
        ]);

        return true;
    }

    private function unapproveMR($mr){
        $relation = PurchaseRequestHeader::on(session()->get('connection_name'))->where('reference_id', $mr->id)
            ->first();

        if($relation){
            return false;
        }

        $mr->update([
            'status_id' => 1
        ]);

        return true;
    }

    private function unapprovePR($pr){
        $relation = PurchaseOrderHeader::on(session()->get('connection_name'))->where('reference_id', $pr->id)
            ->first();

        if($relation){
            return false;
        }

        $pr->update([
            'is_approve' => 0,
            'status_id' => 1
        ]);

        return true;
    }

    private function unapproveRFQ($pr){
        $pr->update([
            'is_approve' => 0,
            'status_id' => 1
        ]);

        return true;
    }

    private function unapproveGR($gr){
        $relation = SupplierInvoiceDetail1::on(session()->get('connection_name'))->where('gr_id', $gr->id)
            ->first();

        if($relation){
            return false;
        }

        $poData = PurchaseOrderHeader::on(session()->get('connection_name'))->where('id', $gr->po_id)->first();

        foreach ($gr->good_receive_details as $detail) {
            $poDetail = PurchaseOrderDetail::on(session()->get('connection_name'))->where('item_id', $detail->item_id)->where('header_id', $gr->po_id)->first();
            if (!empty($poDetail)) {
                $poDetail->qty_gr = $poDetail->qty_gr - $detail->qty;
                $poDetail->save();
            }

            $item = Item::on(session()->get('connection_name'))->where('id', $detail->item_id)->first();
            $totalStock = Inventory::on(session()->get('connection_name'))->where('item_id', $detail->item_id)->sum('qty');

            $totalQty = $totalStock - $detail->qty;
            $currentStockValue = $totalStock * $item->cost_price;
            if ($totalQty > 0) {
                $newStockValue = ($currentStockValue - $detail->total) / ($totalQty);
            } else {
                $newStockValue = 0;
            }
            $item->cost_price = $newStockValue;
            $item->save();

            Utilities::updateStock(Auth::guard('admin')->user()->branch_id,
                'sub',
                $detail->qty,
                $detail->item_id,
                $gr->warehouse_id,
                $gr->id,
                $gr->code,
                'Unapprove Good Receive ; VENDOR ' . $gr->supplier->name . ' ;' . $gr->code,
                $gr->date);
        }

        $poData->is_gr = 0;
        $poData->save();

        $journals = JournalHeader::on(session()->get('connection_name'))->where('reference_id', $gr->id)->get();
        $journalIds = [];
        foreach ($journals as $journal){
            $journalIds[] = $journal->id;
            JournalDetail::on(session()->get('connection_name'))->where('header_id', $journal->id)->delete();
        }

        JournalHeader::on(session()->get('connection_name'))->whereIn('id', $journalIds)->delete();

        $gr->update([
            'is_approve' => 0,
            'status_id' => 1
        ]);

        return true;
    }

    private function unapprovePI($pi){
        $relation = ApDetail1::on(session()->get('connection_name'))->where('supplier_invoice_id', $pi->id)
            ->first();

        if($relation){
            return false;
        }

        foreach ($pi->supplier_invoice_detail1s as $dataDetail){
            $dataGR = GoodReceiveHeader::on(session()->get('connection_name'))->where('id', $dataDetail->gr_id)->first();
            if(!empty($dataGR)){
                $checkExist = SupplierInvoiceDetail1::where('gr_id', $dataGR->id)
                    ->where('header_id', '!=', $pi->id)
                    ->first();

                if(!$checkExist){
                    $dataGR->is_close = 0;
                    $dataGR->save();
                }
            }
        }

        $journals = JournalHeader::on(session()->get('connection_name'))->where('reference_id', $pi->id)->get();
        $journalIds = [];
        foreach ($journals as $journal){
            $journalIds[] = $journal->id;
            JournalDetail::on(session()->get('connection_name'))->where('header_id', $journal->id)->delete();
        }

        JournalHeader::on(session()->get('connection_name'))->whereIn('id', $journalIds)->delete();

        $pi->update([
            'is_approve' => 0,
            'status_id' => 1
        ]);

        return true;
    }

    private function unapproveDO($do){
        $relation = SalesInvoiceDetail::on(session()->get('connection_name'))->where('do_id', $do->id)
            ->first();

        if($relation){
            return false;
        }

        foreach ($do->delivery_order_details as $detail) {
            if($do->type == 'bast'){
                DB::connection(session()->get('connection_name'))->table('sales_order_details')
                    ->where('id', '=', $detail->so_detail_id)
                    ->decrement('qty_do_approved', $detail->qty, [
                        'updated_at' => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                    ]);

                Utilities::updateStock(Auth::guard('admin')->user()->branch_id,
                    'add',
                    $detail->qty,
                    $detail->item_id,
                    $do->warehouse_id,
                    $do->id,
                    $do->code,
                    'Unapprove Delivery Order ; CUSTOMER ' . $do->customer->name . ' ;' . $do->code,
                    $do->date);
            }
            else {
                DB::connection(session()->get('connection_name'))->table('sales_order_details')
                    ->where('id', '=', $detail->so_detail_id)
                    ->decrement('qty_bap_approved', $detail->qty, [
                        'updated_at' => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                    ]);
            }
        }

        DB::connection(session()->get('connection_name'))->table('sales_order_headers')
            ->where('id', '=', $do->so_id)
            ->update([
                'is_do' => 0,
                'updated_at' => Carbon::now('Asia/Jakarta')->toDateTimeString(),
            ]);

        $journals = JournalHeader::on(session()->get('connection_name'))->where('reference_id', $do->id)->get();
        $journalIds = [];
        foreach ($journals as $journal){
            $journalIds[] = $journal->id;
            JournalDetail::on(session()->get('connection_name'))->where('header_id', $journal->id)->delete();
        }

        JournalHeader::on(session()->get('connection_name'))->whereIn('id', $journalIds)->delete();

        $do->update([
            'is_approve' => 0,
            'status_id' => 1
        ]);

        return true;
    }

    private function unapproveSI($si){
        $relation = ArDetail1::on(session()->get('connection_name'))->where('sales_invoice_id', $si->id)
            ->first();

        if($relation){
            return false;
        }

        foreach ($si->sales_invoice_details as $detail) {
            $dataDO = DeliveryOrderHeader::on(session()->get('connection_name'))->where('id', $detail->do_id)->first();
            if ($dataDO) {
                $so = SalesOrderHeader::on(session()->get('connection_name'))->find($dataDO->so_id);
                $so->update([
                    'is_dp' => 0,
                ]);

                $checkExist = SalesInvoiceDetail::where('do_id', $dataDO->id)
                    ->where('header_id', '!=', $si->id)
                    ->first();

                if(!$checkExist){
                    $dataDO->is_close = 0;
                    $dataDO->is_si_selected = 0;
                    $dataDO->selected_by_si_id = 0;
                    $dataDO->updated_by = Auth::guard('admin')->id();
                    $dataDO->save();
                }

            }
        }

        $journals = JournalHeader::on(session()->get('connection_name'))->where('reference_id', $si->id)->get();
        $journalIds = [];
        foreach ($journals as $journal){
            $journalIds[] = $journal->id;
            JournalDetail::on(session()->get('connection_name'))->where('header_id', $journal->id)->delete();
        }

        JournalHeader::on(session()->get('connection_name'))->whereIn('id', $journalIds)->delete();

        $si->update([
            'is_approve' => 0,
            'status_id' => 1
        ]);

        return true;
    }

    private function unapproveSID($si){
        $journals = JournalHeader::on(session()->get('connection_name'))->where('reference_id', $si->id)->get();
        $journalIds = [];
        foreach ($journals as $journal){
            $journalIds[] = $journal->id;
            JournalDetail::on(session()->get('connection_name'))->where('header_id', $journal->id)->delete();
        }

        JournalHeader::on(session()->get('connection_name'))->whereIn('id', $journalIds)->delete();

        $si->update([
            'is_approve' => 0,
            'status_id' => 1
        ]);

        return true;
    }

    private function unapproveAP($ap){
        $journals = JournalHeader::on(session()->get('connection_name'))->where('reference_id', $ap->id)->get();
        $journalIds = [];
        foreach ($journals as $journal){
            $journalIds[] = $journal->id;
            JournalDetail::on(session()->get('connection_name'))->where('header_id', $journal->id)->delete();
        }

        JournalHeader::on(session()->get('connection_name'))->whereIn('id', $journalIds)->delete();

        $ap->update([
            'is_approve' => 0,
            'status_id' => 1
        ]);

        return true;
    }

    private function unapproveCO($co){
        $journals = JournalHeader::on(session()->get('connection_name'))->where('reference_id', $co->id)->get();
        $journalIds = [];
        foreach ($journals as $journal){
            $journalIds[] = $journal->id;
            JournalDetail::on(session()->get('connection_name'))->where('header_id', $journal->id)->delete();
        }

        JournalHeader::on(session()->get('connection_name'))->whereIn('id', $journalIds)->delete();

        $co->update([
            'status_id' => 1
        ]);

        return true;
    }

    private function unapproveCI($ci){
        $journals = JournalHeader::on(session()->get('connection_name'))->where('reference_id', $ci->id)->get();
        $journalIds = [];
        foreach ($journals as $journal){
            $journalIds[] = $journal->id;
            JournalDetail::on(session()->get('connection_name'))->where('header_id', $journal->id)->delete();
        }

        JournalHeader::on(session()->get('connection_name'))->whereIn('id', $journalIds)->delete();

        $ci->update([
            'status_id' => 1
        ]);

        return true;
    }

    private function unapproveMO($mo){
        $mo->update([
            'status_id' => 1
        ]);
        foreach ($mo->inventory_out_details as $detail) {
            Utilities::updateStock($mo->branch_id,
                'add',
                $detail->qty,
                $detail->item_id,
                $mo->warehouse_id,
                $mo->id,
                $mo->code,
                'Unapprove; PENGELUARAN BARANG ; ' . $mo->description . ' ; ',
                $mo->date);
        }


        return true;
    }

    private function unapproveMI($mi){
        $mi->update([
            'status_id' => 1
        ]);

        foreach ($mi->inventory_in_details as $detail){
            Utilities::updateStock($mi->branch_id,
            'sub',
            $detail->qty,
            $detail->item_id,
            $mi->warehouse_id,
            $mi->id,
            $mi->code,
            'Unapprove; PENAMBAHAN BARANG ; '. $mi->description .' ; ',
            $mi->date);
        }

        return true;
    }

    private function unapproveAR($ar){
        $ar->update([
            'is_approve' => 0,
            'status_id' => 1
        ]);

        foreach ($ar->ar_detail1s as $detail) {
            $dataInvoice = SalesInvoiceHeader::where('id', $detail->sales_invoice_id)
                ->first();
            $amount = $dataInvoice->grandtotal;

            $sumArDetail = DB::connection(session()->get('connection_name'))->table('ar_detail1s')
                ->join('ar_headers', 'ar_headers.id', '=', 'ar_detail1s.header_id')
                ->select(
                    'ar_headers.status_id as status_id',
                    'ar_detail1s.sales_invoice_id as sales_invoice_id',
                    'ar_detail1s.debit as debit',
                )
                ->where('ar_headers.status_id', 6)
                ->where('ar_detail1s.sales_invoice_id', $detail->sales_invoice_id)
                ->sum('ar_detail1s.debit');
            $detail->nominal_invoice = $amount + $sumArDetail;
            $detail->save();
        }

        $journals = JournalHeader::on(session()->get('connection_name'))->where('reference_id', $ar->id)->get();
        $journalIds = [];
        foreach ($journals as $journal){
            $journalIds[] = $journal->id;
            JournalDetail::on(session()->get('connection_name'))->where('header_id', $journal->id)->delete();
        }

        JournalHeader::on(session()->get('connection_name'))->whereIn('id', $journalIds)->delete();

        return true;
    }
}
