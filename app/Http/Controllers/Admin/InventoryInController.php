<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Libs\AutoNumberGenerator;
use App\Libs\Utilities;
use App\Models\Branch;
use App\Models\InventoryInDetail;
use App\Models\InventoryInHeader;
use App\Models\ItemUnit;
use App\Models\Warehouse;
use App\Transformer\InventoryInTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class InventoryInController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request){
        $filterStartDate = Carbon::today()->startOfYear()->format('d-m-Y');
        $filterEndDate = Carbon::today()->format('d-m-Y');

        if($request->start_date != null && $request->end_date != null){
          $startDateDecoded = rawurldecode($request->start_date);
          $endDateDecoded = rawurldecode($request->end_date);
          $start = Carbon::createFromFormat('!d-m-Y', $startDateDecoded, 'Asia/Jakarta');
          $end = Carbon::createFromFormat('!d-m-Y', $endDateDecoded, 'Asia/Jakarta');

          if($end->greaterThanOrEqualTo($start)){
            $filterStartDate = $startDateDecoded;
            $filterEndDate = $endDateDecoded;
          }
        }

        $data = [
          'filterStartDate' => $filterStartDate,
          'filterEndDate' => $filterEndDate
        ];

        return view('admin.inventory_in.index')->with($data);
      }

      public function getIndex(Request $request){
        $admin = Auth::guard('admin')->user();

        // Filter date
        $start = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
        $end = Carbon::createFromFormat('!d-m-Y', $request->input('end_date'), 'Asia/Jakarta');
        $end->addDays(1)->addMinutes(-1);

        $datas = InventoryInHeader::with('status')->where('branch_id', $admin->branch_id)
        // ->where('status_id', '!=', 2)
        ->whereBetween('date', array($start->toDateTimeString(), $end->toDateTimeString()));

        return DataTables::of($datas)
        ->setTransformer(new InventoryInTransformer)
        ->make(true);
      }

    public function create(){
        $branches = Branch::all();

        $data = [
            'branches'      => $branches,
        ];

        return view('admin.inventory_in.create')->with($data);
    }

    public function store(Request $request){
        try{
            $validator = Validator::make($request->all(),[
                'warehouse_id'       => 'required',
                'date'             => 'required',
            ],[
                'warehouse_id.required'          => 'Nama Gudang wajib dipilih!',
                'date.required'                => 'Tanggal Pengiriman wajib dipilih!',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput($request->all());
            }

            $admin = Auth::guard('admin')->user();
            $originWarehouseId = $request->input('warehouse_id');
            $itemIds = $request->input('transfer_product');
            $itemQty = $request->input('transfer_qty');
            $unitIds = $request->input('unitIds');

            // Validate stock
//            $ct = 0;
//            foreach ($itemIds as $itemId){
//                $originInv = DB::table('inventories')
//                    ->where('branch_id', '=', $admin->branch_id)
//                    ->where('warehouse_id', '=', $originWarehouseId)
//                    ->where('item_id', '=', $itemId)
//                    ->first();
//                if(empty($originInv)){
//                    return back()->withErrors("Stok di Gudang tidak mencukupi!")->withInput($request->all());
//                }
//
//                $tmpQty = intval($itemQty[$ct]);
//                if($originInv->qty < $tmpQty){
//                    return back()->withErrors("Stok di Gudang tidak mencukupi!")->withInput($request->all());
//                }
//            }


            $data = new InventoryInHeader();
            $data->branch_id = $admin->branch_id;
            $data->code = AutoNumberGenerator::generateAutonumberTrx('INV/IN');
            $data->date = Carbon::parse($request->input('date'))->toDateTimeString();
            $data->description = $request->input('description');
            $data->warehouse_id = $originWarehouseId;
            $data->warehouse_name = Warehouse::find($originWarehouseId)->name;
            $data->total_qty = Utilities::toFloat($request->input('total_qty'));
            $data->status_id = 1;
            $data->created_by = $admin->id;
            $data->save();

//            AutoNumberGenerator::nextAutonumber('INV/IN');

            $ct = 0;
            foreach ($itemIds as $itemId){
                // $itemUnit = ItemUnit::where('item_id', $itemId)->first();
              $itemUnit = ItemUnit::on(session()->get('connection_name'))
                  ->where('item_id', $itemId)
                  ->where('unit_id', $unitIds[$ct])
                  ->first();
              $dataDetail = new InventoryInDetail();
              $dataDetail->header_code = $data->code;
              $dataDetail->header_id = $data->id;
              $dataDetail->item_id = $itemId;
              $dataDetail->qty = (double)($itemQty[$ct]);
              $dataDetail->unit_id = $itemUnit->unit_id;
              $dataDetail->qty_unit = $itemUnit != null ? $itemUnit->qty : 1;
              $dataDetail->created_by = $admin->id;
              $dataDetail->save();

                // Stock entry to warehouse
                // Utilities::updateStock($admin->branch_id,
                //     'add',
                //     Utilities::toFloat($itemQty[$ct]),
                //     $itemId,
                //     $data->warehouse_id,
                //     $data->code,
                //     'Inventory In');

                $ct++;
            }

            Session::flash('success', 'Sukses membuat data Inventory In baru!');
            return redirect()->route('admin.inventory_in.index');
        }
        catch (\Exception $ex){
            Session::flash('error', 'Maaf kesalahan terjadi!' . $ex);
            return back()->withInput($request->all());
        }
    }

    public function edit($id){
        $branches = Branch::all();
        $dataDB = InventoryInHeader::where('id', $id)->first();
        if($dataDB == null){
            Session::flash('error', 'Data tidak ditemukan!');
            return back();
        }
        $detailData = [];

        foreach ($dataDB->inventory_in_details as $dataDetail){
            $vueData = [
                "id"    => $dataDetail->id,
                "itemId"    => $dataDetail->item_id,
                "unitId"    => $dataDetail->unit_id,
                'url'       => route('admin.item.unit.select') . '?id=',
                "qty"       => $dataDetail->qty,
            ];
            array_push($detailData, $vueData);
        }

        $vueData = [
            'itemList' => $detailData
        ];

        $data = [
            'branches'  => $branches,
            'data'      => $dataDB,
            'vueData'   => $vueData,
        ];

        return view('admin.inventory_in.edit')->with($data);
    }

    public function update(Request $request){
        try{
            $validator = Validator::make($request->all(),[
                'warehouse_id'       => 'required',
                'date'             => 'required',
            ],[
                'warehouse_id.required'          => 'Nama Gudang wajib dipilih!',
                'date.required'                => 'Tanggal Pengiriman wajib dipilih!',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput($request->all());
            }

            $user = Auth::guard('admin')->user();
            $data = InventoryInHeader::where('id', $request->input('id'))->first();
            if($data == null){
                Session::flash('error', 'Data Inventory In tidak ditemukan!');
                return back()->withInput($request->all());
            }
            $data->branch_id = $user->branch_id;
            $data->date = Carbon::parse($request->input('date'))->toDateTimeString();
            $data->description = $request->input('description');
            $data->warehouse_id = $request->input('warehouse_id');
            $data->warehouse_name = Warehouse::find($request->input('warehouse_id'))->name;
            $data->total_qty = Utilities::toFloat($request->input('total_qty'));
            $data->updated_by = $user->id;
            $data->save();

            $itemIds = $request->input('transfer_product');
            $itemQty = $request->input('transfer_qty');
            $unitIds = $request->input('unitIds');

            $ct = 0;
            $oldData = InventoryInDetail::where('header_id', $request->input('id'))->get();
            if(count($oldData) > 0){
                foreach($oldData as $oldDetaildata){
                    $oldDetaildata->delete();
                }
            }
            foreach ($itemIds as $itemId){
                // $itemUnit = ItemUnit::where('item_id', $itemId)->first();
              $itemUnit = ItemUnit::on(session()->get('connection_name'))
                  ->where('item_id', $itemId)
                  ->where('unit_id', $unitIds[$ct])
                  ->first();

              $dataDetail = new InventoryInDetail();
              $dataDetail->setConnection(session()->get('connection_name'));
              $dataDetail->header_code = $data->code;
              $dataDetail->header_id = $data->id;
              $dataDetail->item_id = $itemId;
              $dataDetail->qty = (double)($itemQty[$ct]);
              $dataDetail->unit_id = $unitIds[$ct];
              $dataDetail->qty_unit = $itemUnit != null ? $itemUnit->qty : 1;
              $dataDetail->created_by = $user->id;
              $dataDetail->save();

              $ct++;
            }

            Session::flash('success', 'Sukses mengubah data Inventory In!');
            return redirect()->route('admin.inventory_in.index');
        }
        catch (\Exception $ex){
            Session::flash('error', 'Maaf kesalahan terjadi!' . $ex);
            return back()->withInput($request->all());
        }
    }

    public function show($id){
        $branches = Branch::all();
        $dataDB = InventoryInHeader::where('id', $id)->first();
        if($dataDB == null){
            Session::flash('error', 'Data tidak ditemukan!');
            return back();
        }
        $detailData = [];

        foreach ($dataDB->inventory_in_details as $dataDetail){
            $vueData = [
              "itemId"    => $dataDetail->item_id,
              "itemName"    => $dataDetail->item->name,
              "unitId"    => $dataDetail->unit_id,
              'url'       => route('admin.item.unit.select') . '?id=',
              "qty"       => $dataDetail->qty,
            ];
            array_push($detailData, $vueData);
          }

        $vueData = [
            'itemList' => $detailData
        ];

        $data = [
            'branches'  => $branches,
            'data'      => $dataDB,
            'vueData'   => $vueData,
        ];

        return view('admin.inventory_in.show')->with($data);
    }

    public function delete(Request $request){
        try{
            $warehouse = InventoryInHeader::where('id', $request->input('id'))->first();
            if($warehouse == null){
                Session::flash('error', 'Data tidak ditemukan!');
                return back();
            }
            $warehouse->status_id = 2;
            $warehouse->save();

            return Response::json('Sukses', 200);
        }
        catch (\Exception $ex){
            return Response::json('error', 200);
        }
    }

    public function approve(Request $request){
        try{
//            dd($request);
            $user = Auth::guard('admin')->user();
            $ids = $request->input('ids');
            if(empty($ids)){
                Session::flash('error', 'Tidak Ada Data yang Dipilih');
                return redirect()->route('admin.inventory_in.index');
            }
            foreach ($ids as $id) {
                $dataDB = InventoryInHeader::where('id', $id)->first();
                if($dataDB == null){
                    Session::flash('error', 'Data tidak ditemukan!');
                    return back();
                }
                $dataDB->status_id = 13;
                $dataDB->updated_by = $user->id;
                $dataDB->save();


                foreach ($dataDB->inventory_in_details as $detail){
                    // Stock entry to warehouse
                    Utilities::updateStock($user->branch_id,
                        'add',
                        Utilities::toFloat($detail->qty),
                        $detail->item_id,
                        $dataDB->warehouse_id,
                        $dataDB->code,
                        'Inventory In');
                }
            }

            Session::flash('success', 'Sukses approve data Inventory In!');
            return redirect()->route('admin.inventory_in.index');
        }
        catch (\Exception $ex){
            Session::flash('error', 'Maaf kesalahan terjadi!' . $ex);
            return back();
        }
    }
}
