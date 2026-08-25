<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Libs\Utilities;
use App\Models\AdminUser;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\ItemBom;
use App\Models\ItemCategory;
use App\Models\ItemUnit;
use App\Models\Status;
use App\Transformer\ItemTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class ItemController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index()
    {
        return view('admin.item.index');
    }

    public function getIndex(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $items = Item::with(['branch', 'status'])
            ->where('branch_id', '=', $admin->branch_id);

        return DataTables::of($items)
            ->setTransformer(new ItemTransformer)
            ->make(true);
    }

    public function create()
    {
        $admin = Auth::guard('admin')->user();
        $branch = DB::table('branches')
            ->where('id', '=', $admin->branch_id)
            ->first();
        //        $uomMode = DB::table('branches')
        //            ->where('id', '=', $admin->branch_id)
        //            ->first();

        $itemCategories = DB::table('item_categories')
//            ->where('branch_id', $admin->branch_id)
            ->where('status_id', 1)
            ->get();

        $itemJurnalCategories = DB::table('item_categories')
            ->where(function ($q) {
                $q->where('name', 'like', 'PERLENGKAPAN - %')
                    ->orwhere('name', 'like', 'PERSEDIAAN - %');
            })
            ->where('status_id', 1)
            ->get();

        $unitMaterials = DB::table('unit_materials')
//            ->where('branch_id', $admin->branch_id)
            ->orderBy('name')
            ->get();

        $data = [
            'itemCategories' => $itemCategories,
            'itemJurnalCategories' => $itemJurnalCategories,
            'unitMaterials' => $unitMaterials,
            'branch' => $branch,
        ];

        return view('admin.item.create')->with($data);
        //        if($uomMode === 'single'){
        //            return view('admin.item.create')->with($data);
        //        }
        //        else{
        //            return view('admin.item.create_with_uom')->with($data);
        //        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|max:200',
                'code' => 'required|max:100',
                'category_2_id' => 'required',
                'badge_status' => 'nullable|in:recommended,best_seller',
                'image' => 'nullable|image|max:3072',
            ], [
                'name.required' => 'Nama Barang wajib diisi!',
                'name.max' => 'Nama Barang maksimal 200 karakter!',
                'code.required' => 'Kode Barang wajib diisi!',
                'code.max' => 'Kode Barang maksimal 100 karakter!',
                'category_id.required' => 'Kategori Barang wajib diisi!',
                'category_2_id.required' => 'Kategori Jurnal Barang wajib diisi!',
                'badge_status.in' => 'Badge status tidak valid!',
                'image.image' => 'Gambar harus berupa gambar!',
                'image.max' => 'Ukuran gambar maksimal 3MB!',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput($request->all());
            }

            $admin = Auth::guard('admin')->user();
            $branch = DB::table('branches')
                ->where('id', '=', $admin->branch_id)
                ->first();

            // Validate code
            $code = strtoupper(trim($request->input('code')));
            if (DB::table('items')
                ->where('branch_id', '=', $admin->branch_id)
                ->where('code', '=', $code)
                ->exists()) {
                Session::flash('error', 'Kode Barang sudah terpakai!');

                return redirect()->back()->withInput($request->all());
            }

            // Validate unit material
            $unitId = intval($request->input('unit_id'));
            if ($unitId < 1) {
                Session::flash('error', 'Mohon pilih Unit Material!');

                return redirect()->back()->withInput($request->all());
            }

            $bomProductIds = $request->input('bom_product');
            $bomQty = $request->input('bom_qty');
            $bomUnit = $request->input('bom_unit');
            $uomMode = DB::table('branches')
                ->where('id', '=', $admin->branch_id)
                ->first();

            $item = new Item;
            //            $item->code = AutoNumberGenerator::generateAutonumberMasterData('ITM');
            $item->code = $code;
            $item->branch_id = $admin->branch_id;
            $item->barcode = $request->input('barcode');
            $item->name = trim($request->input('name'));
            // $item->is_unlimited_stock = isset($request->input('is_stock')) ? trim($request->input('is_stock')) : null;
            $item->category_id = $request->input('category_id');
            $item->category_2_id = $request->input('category_2_id');
            $item->price = Utilities::toFloat($request->input('price'));
            $item->cost_price = Utilities::toFloat($request->input('cost_price'));
            $item->badge_status = $request->input('badge_status');
            $item->status_id = 1;
            $item->created_by = $admin->id;
            $item->updated_by = $admin->id;
            $item->have_bom = 0;

            if ($branch->is_oura === 1) {
                // $item->is_unlimited_stock = $request->input('is_unlimited_stock') == 1;
                $item->is_addon = $request->input('is_addon') == 1;
            }

            $item->save();

            //            if($uomMode === 'single'){
            //                $newItemUnit = new ItemUnit();
            //                $newItemUnit->item_id = $item->id;
            //                $newItemUnit->unit_id = $unitId;
            //                $newItemUnit->qty = 1;
            //                $newItemUnit->selling_price = $item->price;
            //                $newItemUnit->save();
            //            }
            //            else{
            //
            //            }

            $newItemUnit = new ItemUnit;
            $newItemUnit->item_id = $item->id;
            $newItemUnit->unit_id = $unitId;
            $newItemUnit->qty = 1;
            $newItemUnit->selling_price = $item->price;
            $newItemUnit->save();

            if (! empty($bomProductIds)) {
                $ct = 0;
                foreach ($bomProductIds as $bomProductId) {
                    $item->have_bom = 1;
                    $item->save();

                    $itemBom = new ItemBom;
                    $itemBom->branch_id = $request->input('branch_id');
                    $itemBom->item_id = $item->id;
                    $itemBom->qty = Utilities::toFloat($bomQty[$ct]);
                    $itemBom->material_id = $bomProductId;
                    $itemBom->material_name = Item::find($bomProductId)->name;
                    $itemBom->uom_id = $bomUnit[$ct];
                    $itemBom->save();

                    $ct++;
                }
            }

            if ($request->hasFile('image')) {
                // Save new image
                $image = $request->file('image');
                $imageName = $this->saveImage($image, $item->code);
                $item->image_path = $imageName;
                $item->save();
            }
            //            AutoNumberGenerator::nextAutonumber('ITM');

            Session::flash('success', 'Sukses membuat data Barang baru!');

            return redirect()->route('admin.item.index');
        } catch (\Exception $ex) {
            Session::flash('error', 'Maaf kesalahan terjadi!'.$ex->getMessage());

            return back()->withInput($request->all());
        }
    }

    public function edit($id)
    {
        $admin = Auth::guard('admin')->user();
        $branch = DB::table('branches')
            ->where('id', '=', $admin->branch_id)
            ->first();
        //        if($user->is_super == 1){
        //            $branches = Branch::all();
        //            $itemCategories = ItemCategory::where('status_id', 1)->get();
        //        }
        //        else{
        //            $branches = Branch::find($user->branch_id);
        //            $itemCategories = ItemCategory::where('branch_id', $user->branch_id)->where('status_id', 1)->get();
        //        }
        $itemCategories = ItemCategory::where('status_id', 1)->get();

        $itemJurnalCategories = DB::table('item_categories')
            ->where(function ($q) {
                $q->where('name', 'like', 'PERLENGKAPAN - %')
                    ->orwhere('name', 'like', 'PERSEDIAAN - %');
            })
            ->where('status_id', 1)
            ->get();

        $unitMaterials = DB::table('unit_materials')
//            ->where('branch_id', $admin->branch_id)
            ->orderBy('name')
            ->get();

        $item = Item::where('id', $id)->first();
        $dataDetails = ItemBom::where('item_id', $id)->get();
        $statuses = Status::where('id', 1)->orWhere('id', 2)->get();
        if ($item == null) {
            Session::flash('error', 'Produk tidak ditemukan!');

            return back();
        }
        $detailData = [];

        foreach ($dataDetails as $dataDetail) {
            $vueData = [
                'unitId' => $dataDetail->uom_id,
                'qty' => $dataDetail->qty,
                'itemId' => $dataDetail->material_id,
                'itemName' => $dataDetail->material_name,
            ];
            array_push($detailData, $vueData);
        }

        $vueData = [
            'itemList' => $detailData,
            'isStock' => $item->is_unlimited_stock,
        ];

        $data = [
            'data' => $item,
            'itemCategories' => $itemCategories,
            'itemJurnalCategories' => $itemJurnalCategories,
            'unitMaterials' => $unitMaterials,
            'statuses' => $statuses,
            'vueData' => $vueData,
            'branch' => $branch,
        ];

        return view('admin.item.edit')->with($data);
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|max:200',
                'code' => 'required|max:100',
                'category_2_id' => 'required',
                'badge_status' => 'nullable|in:recommended,best_seller',
                'image' => 'nullable|image|max:3072',
            ], [
                'name.required' => 'Nama Barang wajib diisi!',
                'name.max' => 'Nama Barang maksimal 200 karakter!',
                'code.required' => 'Kode Barang wajib diisi!',
                'code.max' => 'Kode Barang maksimal 100 karakter!',
                'category_id.required' => 'Kategori Barang wajib diisi!',
                'category_2_id.required' => 'Kategori Jurnal Barang wajib diisi!',
                'badge_status.in' => 'Badge status tidak valid!',
                'image.image' => 'Gambar harus berupa gambar!',
                'image.max' => 'Ukuran gambar maksimal 3MB!',
            ]);
            // dd($request);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput($request->all());
            }
            if ($request->input('category_2_id') == 0) {
                return back()->withErrors('Kategori Jurnal Barang wajib diisi!')->withInput($request->all());
            }

            $item = Item::where('id', $id)->first();
            if ($item == null) {
                Session::flash('error', 'Barang tidak ditemukan!');

                return back()->withInput($request->all());
            }

            $unitId = intval($request->input('unit_id'));
            if ($unitId < 1) {
                Session::flash('error', 'Mohon pilih Unit Material!');

                return redirect()->back()->withInput($request->all());
            }

            $admin = Auth::guard('admin')->user();
            $branch = DB::table('branches')
                ->where('id', '=', $admin->branch_id)
                ->first();

            // Validate code
            $code = strtoupper(trim($request->input('code')));
            if (DB::table('items')
                ->where('branch_id', '=', $admin->branch_id)
                ->where('id', '!=', $id)
                ->where('code', '=', $code)
                ->exists()) {
                Session::flash('error', 'Kode Barang sudah terpakai!');

                return redirect()->back();
            }

            $bomProductIds = $request->input('bom_product');
            $bomQty = $request->input('bom_qty');
            $bomUnit = $request->input('bom_unit');

            $item->barcode = $request->filled('barcode') ? trim($request->input('barcode')) : null;
            $item->code = $code;
            $item->name = trim($request->input('name'));
            $item->is_unlimited_stock = trim($request->input('is_stock'));
            $item->category_id = $request->input('category_id');
            $item->category_2_id = $request->input('category_2_id');
            $item->price = Utilities::toFloat($request->input('price'));
            $item->cost_price = Utilities::toFloat($request->input('cost_price'));
            $item->badge_status = $request->input('badge_status');
            $item->updated_by = $admin->id;
            $item->have_bom = 0;
            $item->status_id = $request->input('status');

            if ($branch->is_oura === 1) {
                $item->is_unlimited_stock = $request->input('is_unlimited_stock') == 1;
                $item->is_addon = $request->input('is_addon') == 1;
            }

            $item->save();

            $itemUnit = ItemUnit::where('item_id', $request->input('id'))->first();
            if ($itemUnit) {
                $itemUnit->update([
                    'unit_id' => $unitId,
                    'selling_price' => $item->price,
                ]);
            } else {
                $newItemUnit = new ItemUnit;
                $newItemUnit->item_id = $item->id;
                $newItemUnit->unit_id = $unitId;
                $newItemUnit->qty = 1;
                $newItemUnit->selling_price = $item->price;
                $newItemUnit->save();
            }

            if (! empty($bomProductIds)) {
                $oldData = ItemBom::where('item_id', $request->input('id'))->get();
                if (count($oldData) > 0) {
                    foreach ($oldData as $data) {
                        $data->delete();
                    }
                }
                $ct = 0;
                foreach ($bomProductIds as $bomProductId) {
                    $item->have_bom = 1;
                    $item->save();

                    $itemBom = new ItemBom;
                    $itemBom->branch_id = $admin->branch_id;
                    $itemBom->item_id = $item->id;
                    $itemBom->qty = Utilities::toFloat($bomQty[$ct]);
                    $itemBom->material_id = $bomProductId;
                    $itemBom->material_name = Item::find($bomProductId)->name;
                    $itemBom->uom_id = $bomUnit[$ct];
                    $itemBom->save();

                    $ct++;
                }
            }

            if ($request->hasFile('image')) {
                // Delete old image
                if (! is_null($item->image_path) && $item->image_path !== 'storage/images/items/default.jpg') {
                    Storage::disk('package_photos')->delete($item->image_path);
                }
                // Save new image
                $image = $request->file('image');
                $imageName = $this->saveImage($image, $item->code);

                $item->image_path = $imageName;
                $item->save();
            }

            Session::flash('success', 'Sukses mengubah data Barang!');

            return redirect()->route('admin.item.index');
        } catch (\Exception $ex) {
            Session::flash('error', 'Maaf kesalahan terjadi!'.$ex->getMessage());

            return back()->withInput($request->all());
        }
    }

    public function delete(Request $request)
    {
        try {
            $item = Item::where('id', $request->input('id'))->first();
            if ($item == null) {
                Session::flash('error', 'Produk tidak ditemukan!');

                return back();
            }
            $item->status_id = 2;
            $item->save();

            return Response::json('Sukses', 200);
        } catch (\Exception $ex) {
            return Response::json('error', 200);
        }
    }

    public function select(Request $request)
    {
        $term = trim($request->search);

        $haveBom = -1;
        $withoutFood = false;
        $isAddon = 0;

        // Check have bom parameter
        if ($request->have_bom != null) {
            $haveBom = intval($request->have_bom);
        }

        // Check branch parameter
        if ($request->without_food != null) {
            $withoutFood = true;
        }

        // Check addon parameter
        if ($request->is_addon != null) {
            $isAddon = intval($request->is_addon);
        }

        $admin = Auth::guard('admin')->user();

        $items = DB::table('items')
            ->where('branch_id', '=', $admin->branch_id)
            ->where(function ($q) use ($term) {
                $q->where('code', 'LIKE', '%'.$term.'%')
                    ->orWhere('name', 'LIKE', '%'.$term.'%');
            })
            ->where('branch_id', $admin->branch_id);

        if ($haveBom > -1) {
            $items = $items->where('have_bom', $haveBom);
        }

        if ($withoutFood) {
            $items = $items->where('category_id', '!=', 2);
        }

        if ($isAddon == 1) {
            $items = $items->where('is_addon', '=', 1);
        }

        if ($request->is_unlimited != null) {
            $items->where('is_unlimited_stock', $request->is_unlimited);
        }

        $items = $items->orderBy('id')
            ->get();

        $formatted_tags = [];

        foreach ($items as $item) {
            $formatted_tags[] = ['id' => $item->id, 'text' => $item->code.' - '.$item->name];
        }

        return Response::json($formatted_tags);
    }

    public function selectGroup(Request $request)
    {
        $term = trim($request->search);

        $haveBom = -1;
        $withoutFood = false;
        $isAddon = 0;

        // Check have bom parameter
        if ($request->have_bom != null) {
            $haveBom = intval($request->have_bom);
        }

        // Check branch parameter
        if ($request->without_food != null) {
            $withoutFood = true;
        }

        // Check addon parameter
        if ($request->is_addon != null) {
            $isAddon = intval($request->is_addon);
        }

        $admin = Auth::guard('admin')->user();

        $items = Item::where('branch_id', '=', $admin->branch_id)
            ->where(function ($q) use ($term) {
                $q->where('code', 'LIKE', '%'.$term.'%')
                    ->orWhere('name', 'LIKE', '%'.$term.'%');
            })
            ->where('branch_id', $admin->branch_id);

        if ($haveBom > -1) {
            $items = $items->where('have_bom', $haveBom);
        }

        if ($withoutFood) {
            $items = $items->where('category_id', '!=', 2);
        }

        if ($isAddon == 1) {
            $items = $items->where('is_addon', '=', 1);
        }

        if ($request->is_unlimited != null) {
            $items->where('is_unlimited_stock', $request->is_unlimited);
        }

        $items = $items->orderBy('id')
            ->get();

        $grouped_packages = [];

        foreach ($items as $item) {
            $grouped_packages[$item->category_id]['text'] = $item->item_category->name;
            $grouped_packages[$item->category_id]['children'][] = [
                'id' => $item->id,
                'text' => $item->code.' - '.$item->name,
            ];
        }

        return Response::json(array_values($grouped_packages));
    }

    public function selectWithoutFood(Request $request)
    {
        $term = trim($request->search);

        $haveBom = -1;
        $branchId = -1;

        // Check have bom parameter
        if ($request->have_bom != null) {
            $haveBom = intval($request->have_bom);
        }

        // Check branch parameter
        if ($request->branch_id != null) {
            $branchId = intval($request->branch_id);
        } else {
            $user = Auth::guard('admin')->user();
            $branchId = $user->branch_id;
        }

        $items = DB::table('items')
            ->where('branch_id', '=', $branchId)
            ->where(function ($q) use ($term) {
                $q->where('code', 'LIKE', '%'.$term.'%')
                    ->orWhere('name', 'LIKE', '%'.$term.'%');
            });

        $items = $items->where('category_id', '!=', 2);
        if ($haveBom > -1) {
            $items = $items->where('have_bom', $haveBom);
        }

        $items = $items->orderBy('id')
            ->get();

        $formatted_tags = [];

        foreach ($items as $item) {
            $formatted_tags[] = ['id' => $item->id, 'text' => $item->code.' - '.$item->name];
        }

        return Response::json($formatted_tags);
    }

    public function get(Request $request)
    {
        try {
            $user = Auth::guard('admin')->user();
            $userData = AdminUser::where('id', $user->id)->first();
            if (! $request->filled('item_id')) {
                return Response::json('Item Id Required!', 400);
            }

            $item = Item::where('id', $request->query('item_id'))
                ->where('branch_id', $userData->branch_id)
                ->first();

            if ($item == null) {
                return Response::json('Item tidak ditemukan!', 404);
            }

            return Response::json($item, 200);
        } catch (\Exception $ex) {
            return Response::json($ex, 500);
        }
    }

    public function getDetail(Request $request)
    {
        try {
            $item = Item::where('id', $request->input('item_id'))->first();
            if ($item == null) {
                return Response::json([], 200);
            }

            return Response::json($item, 200);
        } catch (\Exception $ex) {
            Log::channel('item')->error('[Item Get Detail] - '.$ex);

            return Response::json($ex, 500);
        }
    }

    public function getPrice(Request $request)
    {
        $item = Item::find($request->get('id'));

        $stock = 0;
        $inventories = Inventory::where('item_id', $item->id)->get();
        foreach ($inventories as $inventory) {
            $stock += $inventory->qty;
        }

        return \response([
            'price' => $item->price,
            'is_unlimited' => $item->is_unlimited_stock,
            'stock' => $stock,
        ]);
    }

    public function saveImage($image, $code)
    {
        // check folder exists
        $tmpPath = Storage::disk('items')->path('');
        if (! Storage::exists($tmpPath)) {
            // create the folder
            Storage::disk('items')->makeDirectory('');
        }

        $mimeType = array_slice(explode('/', $image->getMimeType()), -1)[0];

        $date = Carbon::now('Asia/Jakarta');
        $imageName = $code.'_'.$date->year.$date->month.$date->day.time().'.'.$mimeType;

        $imageLoc = Storage::disk('items')->path(''); // save to directory
        // $imagePath = array_slice(explode('public', $imageLoc), -1)[0]; //save path to database
        $imagePath = $imageLoc.$imageName; // save path to database

        // reduce size img
        if ($mimeType == 'jpeg') {
            $sourceImage = imagecreatefromjpeg($image);
            $width = imagesx($sourceImage);
            $height = imagesy($sourceImage);
            $newWidth = $width * 0.2;
            $newHeight = $height * 0.2;
            $newImage = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagejpeg($newImage, $imagePath, 60);
        } elseif ($mimeType == 'png') {
            $sourceImage = imagecreatefrompng($image);
            $width = imagesx($sourceImage);
            $height = imagesy($sourceImage);
            $newWidth = $width * 0.2;
            $newHeight = $height * 0.2;
            $newImage = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            imagepng($newImage, $imagePath, 5);
        } elseif ($mimeType == 'webp') {
            $sourceImage = imagecreatefromwebp($image);
            $width = imagesx($sourceImage);
            $height = imagesy($sourceImage);
            $newWidth = $width * 0.2;
            $newHeight = $height * 0.2;
            $newImage = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagewebp($newImage, $imagePath, 60);
        }

        return $imageName;
    }

    public function selectUnit(Request $request)
    {
        $units = ItemUnit::on(session()->get('connection_name'))
            ->where('item_id', '=', $request->get('id'))
            ->get();

        $data = [];
        foreach ($units as $unit) {
            $data[] = [
                'id' => $unit->unit_id,
                'text' => $unit->unit_material->name,
            ];
        }

        return \response($data);
    }
}
