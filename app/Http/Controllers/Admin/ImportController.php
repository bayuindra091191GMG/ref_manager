<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Libs\Utilities;
use App\Models\Branch;
use App\Models\Coa;
use App\Models\CoaMonthlyAmount;
use App\Models\CoaType;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\ItemUnit;
use App\Models\Partner;
use App\Models\UnitMaterial;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Rap2hpoutre\FastExcel\FastExcel;

class ImportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function showEmployeeForm(){
        try {
            $branches = DB::table('branches')->get();

            return view('admin.import.employee', compact('branches'));
        }
        catch (\Exception $ex){
            dd($ex);
        }
    }

    public function employeeImportSubmit(Request $request){
        try {
            $branchId = intval($request->input('branch'));
            $branchDB = Branch::where('id', $branchId)->first();

            $reads = (new FastExcel)->import($request->file('excel'));
            dd($reads);

            $partnerCounter = 1;
            foreach ($reads as $read){
//                if(!empty($read[1])){
//                    $employee = Employee::where('code', '=', $read[1])->first();
//                    if(!empty($employee)){
//                        $employee->attend_machine_id = $read[0];
//                        $employee->save();
//                    }
//                }

                if(!empty($read['NOCAN'])){
                    $newPartner = new Partner();
                    $newPartner->branch_id = $branchId;
                    $newPartner->status_id = 5;
                    $newPartner->is_present = 1;
                    $newPartner->number = trim($read['NOCAN']);

                    $postFix = Utilities::generateEmployeePostfix($partnerCounter);

                    $newPartner->code = strtoupper($branchDB->code).'/'. $postFix;
                    $newPartner->name = trim($read['DATA KARYAWAN']);
                    $newPartner->alias = trim($read['ALIAS']);

                    $partnerLevel = strtolower($read['LEVEL']);
                    $newPartner->type = $partnerLevel;
    //                    $newPartner->join_date = $read['JOIN DATE'];

                    $levelId = DB::table('partner_levels')
                        ->where('branch_id', '=', $branchId)
                        ->where('name', '=', $partnerLevel)
                        ->value('id');
                    $newPartner->level_id = $levelId;

                    $newPartner->basic_salary_amount = $read['GAJI KARYAWAN'];
                    $newPartner->commission_percentage = $read['KOMISI'];
                    $newPartner->save();

                    $partnerCounter++;
                }
                else{
                    $newEmployee = new Employee();
                    $newEmployee->branch_id = $branchId;
                    $newEmployee->status_id = 1;
                    $newEmployee->name = trim($read['DATA KARYAWAN']);

                    $postFix = Utilities::generateEmployeePostfix($partnerCounter);

                    $newEmployee->code = strtoupper($branchDB->code).'/'. $postFix;

                    $dept = DB::table('departments')
                        ->where('branch_id', $branchId)
                        ->where('name', '=', $read['DEPARTEMEN'])
                        ->first();
                    $deptId = 0;
                    if(empty($dept)){
                        $newDepartmen = new Department();
                        $newDepartmen->branch_id = $branchId;
                        $newDepartmen->name = $read['DEPARTEMEN'];
                        $newDepartmen->save();

                        $deptId = $newDepartmen->id;
                    }
                    else{
                        $deptId = $dept->id;
                    }
                    $newEmployee->department_id = $deptId;
                    $newEmployee->role = trim($read['DEPARTEMEN']);
                    $newEmployee->basic_salary_amount = $read['GAJI KARYAWAN'];
                    $newEmployee->save();

                    $partnerCounter++;
                }
            }

            Session::flash('success', 'Berhasil import data!');
            return redirect()->route('admin.import.employee.form');
        }
        catch (\Exception $ex){
            dd($ex);
        }
    }

    public function showItemForm(){
        try {
            $branches = DB::table('branches')->get();

            return view('admin.import.item', compact('branches'));
        }
        catch (\Exception $ex){
            dd($ex);
        }
    }

    public function itemImportEmpSubmit(Request $request){
        try {
            $branchId = intval($request->input('branch'));

            $reads = (new FastExcel)->import($request->file('excel'));
            foreach ($reads as $read){
                if(!empty($read['No'])){
                    $categoryName = trim($read['Kategori']);
                    $category = DB::table('item_categories')
                        ->where('name', '=', $categoryName)
                        ->first();

                    $newItem = new Item();
                    $newItem->branch_id = $branchId;
                    $newItem->category_id = $category->id;
                    $newItem->status_id = 1;
                    $newItem->code = trim($read['Kode']);
                    $newItem->name = trim($read['NAMA ITEM']);
                    $newItem->price = $read['HARGA'];
                    $newItem->save();

                    $uom = trim($read['Satuan']);
                    $tmpUnit = DB::table('unit_materials')
                        ->where('name', '=', $uom)
                        ->first();


                    if(empty($tmpUnit)){
                        $newUnit = new UnitMaterial();
                        $newUnit->branch_id = $branchId;
                        $newUnit->status_id = 1;
                        $newUnit->name = $uom;
                        $newUnit->save();

                        $unitId = $newUnit->id;
                    }
                    else{
                        $unitId = $tmpUnit->id;
                    }

                    $tmpItemUnit = DB::table('item_units')
                        ->where('item_id', '=', $newItem->id)
                        ->where('unit_id', '=', $unitId)
                        ->first();
                    if(empty($tmpItemUnit)){
                    }
                }
            }

            Session::flash('success', 'Berhasil import data!');
            return redirect()->route('admin.import.item.form');
        }
        catch (\Exception $ex){
            dd($ex);
        }
    }

    public function itemImportSubmit(Request $request){
        try {
            $branchId = intval($request->input('branch'));
//            $branchDb = Branch::where('id', $branchId)->first();

            $reads = (new FastExcel)->import($request->file('excel'));
            foreach ($reads as $read){
                if(!empty($read['Nama'])){
                    $categoryId = trim($read['Kategori']);

                    $readName = trim($read['Nama']);
                    $existingItem = Item::where('branch_id', '=', $branchId)
                        ->where('name', '=', $readName)
                        ->first();

                    if(empty($existingItem)){
                        //create item
                        $newItem = new Item();
                        $newItem->branch_id = $branchId;
                        $newItem->category_id = $categoryId;
                        $newItem->category_2_id = $categoryId;
                        $newItem->status_id = 1;
                        $newItem->name = $readName;
                        $newItem->price = 0;
                        $newItem->cost_price = $read['Harga Beli'];
                        $newItem->is_unlimited_stock = 0;
                        $newItem->save();

//                    $strPad = str_pad($ct, 4,'0', STR_PAD_LEFT);
//                    $newItem->code =  strtoupper($branchDb->code).'/'.$strPad;
                        $newItem->code = trim($read['Kode']);
                        $newItem->save();

                        //save to item unit
                        $newItemUnit = new ItemUnit();
                        $newItemUnit->item_id = $newItem->id;
                        $newItemUnit->unit_id = $read['Satuan'];
                        $newItemUnit->qty = 1;
                        $newItemUnit->purchase_price = $read['Harga Beli'];
                        $newItemUnit->selling_price = 0;
                        $newItemUnit->save();

                        //save to inventory
                        $warehouseDB = Warehouse::where('branch_id', $branchId)->where('name', 'Gudang Store')->first();
                        $newInventory = new Inventory();
                        $newInventory->branch_id = $branchId;
                        $newInventory->item_id = $newItem->id;
                        $newInventory->warehouse_id = $warehouseDB->id;
                        $newInventory->qty = $read['Stok'];
                        $newInventory->save();
                    }
                    else{
                        $existingItem->code = trim($read['Kode']);
                        $existingItem->save();
                    }

                }
            }

            Session::flash('success', 'Berhasil import data!');
            return redirect()->route('admin.import.item.form');
        }
        catch (\Exception $ex){
            dd($ex);
        }
    }


    public function showItemAdjustForm(){
        try {
            $branches = DB::table('branches')->get();

            return view('admin.import.item_adjust', compact('branches'));
        }
        catch (\Exception $ex){
            dd($ex);
        }
    }
    public function itemImportAdjustSubmit(Request $request){
        try {
            $branchId = intval($request->input('branch'));
            $branchDb = Branch::where('id', $branchId)->first();

            $reads = (new FastExcel)->import($request->file('excel'));
            $ct = 1;
            foreach ($reads as $read){
                if(!empty($read['NO'])){
                    //create item
                    $newItem = Item::where('name', trim($read['NAMA ITEM']))->first();

                    //save to inventory
                    $warehouseDB = Warehouse::where('branch_id', $branchId)->where('name', 'Gudang Store')->first();
                    $inventory = Inventory::where('warehouse_id',$warehouseDB->id)
                        ->where('item_id',$newItem->id)
                        ->where('branch_id',$branchId)
                        ->first();

                    if(!empty($inventory->qty)){
                        if(intval($inventory->qty) != intval($read['STOCK'])){
                            $amount = intval($inventory->qty) - intval($read['STOCK']);
//                            dd(intval($inventory->qty), intval($read['STOCK']), $amount);

                            if($amount < 0){
                                // Stock entry to warehouse
                                Utilities::updateStock($branchId,
                                    'add',
                                    $amount * -1,
                                    $newItem->id,
                                    $warehouseDB->id,
                                    "",
                                    'Adjustment stock 9 Maret 2023');
                            }
                            else{
                                Utilities::updateStock($branchId,
                                    'min',
                                    $amount,
                                    $newItem->id,
                                    $warehouseDB->id,
                                    "",
                                    'Adjustment stock 9 Maret 2023');
                            }

                            $inventory->qty = intval($read['STOCK']);
                            $inventory->save();
                        }
                    }
                    $ct++;
                }
            }

            Session::flash('success', 'Berhasil import data!');
            return redirect()->route('admin.import.item.form');
        }
        catch (\Exception $ex){
            dd($ex);
        }
    }

    public function showInventoryForm(){
        try {
            $branches = DB::table('branches')->get();

            return view('admin.import.inventory', compact('branches'));
        }
        catch (\Exception $ex){
            dd($ex);
        }
    }

    public function inventoryImportSubmit(Request $request){
        try {
            $branchId = intval($request->input('branch'));

            $reads = (new FastExcel)->withoutHeaders()->import($request->file('excel'));
//            dd($reads);

            $rowIdx = 0;
            foreach ($reads as $read){
                if($rowIdx > 1){
                    $date = Carbon::parse($read[2]);
                    if($date->day === 9){
                        $nameArr = explode('-', $read[1]);
                        $itemName = trim($nameArr[2]);
                        if($itemName === 'lain'){
                            $itemName = trim($nameArr[3]);
                        }

                        $itemModel = DB::table('items')
                            ->where('name', '=', $itemName)
                            ->first();
                        if(!empty($itemModel)){
                            $newInv = new Inventory();
                            $newInv->branch_id = 1;
                            $newInv->item_id = $itemModel->id;
                            $newInv->warehouse_id = 1;
                            $newInv->qty = $read[3];
                            $newInv->save();
                        }
                    }
                }

                $rowIdx++;
            }

//            $items = DB::table('items')->get();
//            foreach ($items as $item){
//                if(!DB::table('inventories')
//                    ->where('item_id', '=', $item->id)
//                    ->exists()){
//                    $newInv = new Inventory();
//                    $newInv->branch_id = 1;
//                    $newInv->item_id = $item->id;
//                    $newInv->warehouse_id = 1;
//                    $newInv->qty = 0;
//                    $newInv->save();
//                }
//            }

            Session::flash('success', 'Berhasil import data!');
            return redirect()->route('admin.import.inventory.form');
        }
        catch (\Exception $ex){
            dd($ex);
        }
    }

    public function showBasicSalaryForm(){
        try {
            $branches = DB::table('branches')->get();

            return view('admin.import.basic_salary', compact('branches'));
        }
        catch (\Exception $ex){
            dd($ex);
        }
    }

    public function basicSalaryImportSubmit(Request $request){
        try {
            $branchId = intval($request->input('branch'));
            $reads = (new FastExcel)->import($request->file('excel'));

            foreach ($reads as $read){
                $employee = Employee::where('name', '=', $read['NAMA'])->first();
                if(empty($employee)){
                    echo $read['NAMA']. ' NOT FOUND!<br/>';
                }
                else{
                    $employee->basic_salary_amount = $read['GAJI POKOK'];
                    $employee->save();
                }
            }

            return 'SUCCESS';
        }
        catch (\Exception $ex){
            dd($ex);
        }
    }

    public function showItemBuyPriceForm(){
        try {
            $branches = DB::table('branches')->get();

            return view('admin.import.item_buy_price', compact('branches'));
        }
        catch (\Exception $ex){
            dd($ex);
        }
    }

    public function itemBuyPriceSubmit(Request $request){
        try {
            $branchId = intval($request->input('branch'));
            $reads = (new FastExcel)->import($request->file('excel'));

            foreach ($reads as $read){
                $item = Item::where('name', '=', $read['Nama Barang'])
                    ->where('branch_id', '=', $branchId)
                    ->first();
                if(empty($item)){
                    echo $read['Nama Barang']. ' NOT FOUND!<br/>';
                }
                else{
                    $item->cost_price = $read['Harga Beli'];
                    $item->save();
                }
            }

            //return 'SUCCESS';
        }
        catch (\Exception $ex){
            dd($ex);
        }
    }

    public function StockManyarForm(){
        try {
            $branches = DB::table('branches')->get();

            return view('admin.import.stock_manyar', compact('branches'));
        }
        catch (\Exception $ex){
            dd($ex);
        }
    }

    public function StockManyarSubmit(Request $request){
        try {
            $branchId = intval($request->input('branch'));
            $reads = (new FastExcel)->import($request->file('excel'));

            foreach ($reads as $read){
                $item = Item::where('name', '=', $read['Nama Barang'])
                    ->where('branch_id', '=', $branchId)
                    ->first();
                if(empty($item)){
                    echo $read['Nama Barang']. ' NOT FOUND!<br/>';
                }
                else{
                    $warehouseManyar = Warehouse::where('branch_id', $branchId)->where('name', $read['Gudang'])->first();
                    $selisih = intval($read['Stok sistem'] - $read['Stock fisik asli']);
//                    dd($selisih, $read['Stok sistem'], $read['Stock fisik asli']);
                    Utilities::updateStock($branchId,
                        'sub',
                        $selisih,
                        $item->id,
                        $warehouseManyar->id,
                        "",
                        'Adjustment stock Manyar 29 Mei 2023');
                }
            }

            return 'SUCCESS';
        }
        catch (\Exception $ex){
            dd($ex);
        }
    }

    public function coaForm(){
        try {
            $branches = DB::table('branches')->get();

            return view('admin.import.coa', compact('branches'));
        }
        catch (\Exception $ex){
            dd($ex);
        }
    }

    public function coaImportSubmit(Request $request){
        $accountCode = "";
        try {
            $branchId = intval($request->input('branch'));

            $reads = (new FastExcel)->withoutHeaders()->import($request->file('excel'));

            $rowIdx = 0;
            DB::beginTransaction();
            foreach ($reads as $read){
                if($rowIdx > 1){
                    // dd($read);
                    $accountName = $read[0];
                    $amount = (double)$read[1];

                    $accountNameUpper = strtoupper($accountName);

                    $coa = Coa::whereRaw('UPPER(name) = ?', [$accountNameUpper])->first();
                    if(!empty($coa)){
                        if($amount < 0){
                            // $amount = $amount * -1;
                            CoaMonthlyAmount::create([
                                'branch_id' => $branchId,
                                'date' => '2025-12-01 00:00:00',
                                'month' => 12,
                                'year' => 2025,
                                'coa_id' => $coa->id,
                                'coa_code' => $coa->code,
                                'coa_name' => $coa->name,
                                'debit_total' => 0,
                                'credit_total' => $amount,
                                'balance_total' => $amount
                            ]);
                        }
                        else{
                            CoaMonthlyAmount::create([
                                'branch_id' => $branchId,
                                'date' => '2025-12-01 00:00:00',
                                'month' => 12,
                                'year' => 2025,
                                'coa_id' => $coa->id,
                                'coa_code' => $coa->code,
                                'coa_name' => $coa->name,
                                'credit_total' => 0,
                                'debit_total' => $amount,
                                'balance_total' => $amount
                            ]);
                        }
                    }

                }

                $rowIdx++;
            }
            DB::commit();

            Session::flash('success', 'Berhasil import data!');
            return redirect()->route('admin.import.coa.form');
        }
        catch (\Exception $ex){
            DB::rollBack();
            dd($ex, $accountCode);
        }
    }

    public function coaImportSubmitV1(Request $request){
        $accountCode = "";
        try {
            $branchId = intval($request->input('branch'));

            $reads = (new FastExcel)->withoutHeaders()->import($request->file('excel'));

            $rowIdx = 0;
            foreach ($reads as $read){
                if($rowIdx > 1){
                    // dd($read);
                    $accountName = $read[0];
                    $accountCode = $read[1];
                    $headerCode = $read[3];
                    $parentId = null;
                    $typeId = $read[4];

                    $header = Coa::where('code', $headerCode)->where('branch_id', $branchId)->first();
                    $parentId = null;
                    $level = 1;
                    if(!empty($headerCode)){
                      $parentId = Coa::where('code', $headerCode)->first()->id;
                      $level = Coa::where('code', $headerCode)->first()->level + 1;
                    }

                    $typename = '';
                    $coaType = CoaType::where('id', $typeId)->first();
                    if(!empty($coaType)){
                      $typeId = $coaType->id;
                      $typename = $coaType->name;
                    }
                    // dd($accountCode, $headerCode, $header, $selectedCoa);

                    $newInv = new Coa();
                    $newInv->branch_id = $branchId;
                    $newInv->code = $accountCode;
                    $newInv->name = $accountName;
                    $newInv->type_id = $typeId;
                    $newInv->type = $typename;
                    $newInv->level = $level;
                    $newInv->is_header = $level == 1 ? 1 : 0;
                    $newInv->parent_code = $headerCode;
                    $newInv->parent_id = $parentId;
                    $newInv->status_id = 1;

                    $newInv->save();
                    // dd($accountCode, $headerCode, $header, $selectedCoa);
                }

                $rowIdx++;
            }

            Session::flash('success', 'Berhasil import data!');
            return redirect()->route('admin.import.coa.form');
        }
        catch (\Exception $ex){
            dd($ex, $accountCode);
        }
    }
}
