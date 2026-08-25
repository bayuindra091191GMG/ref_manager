<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Libs\Utilities;
use App\Models\AdminUser;
use App\Models\Item;
use App\Models\Package;
use App\Models\PreweddingPackage;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\Facades\DataTables;

class PreWeddingPackageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(){
        return view('admin.prewedding_package.index');
    }

    public function datatable(Request $request){
        $query = PreweddingPackage::query();

        return DataTables::of($query)
            ->addColumn('action', function ($row){
                $buttons = '';

                $buttons .= '<a href="' . route('admin.prewedding_package.edit', ['id' => $row->id]) . '" class="btn btn-primary me-1"><i class="far fa fa-edit"></i> Edit</a>';

                return $buttons;
            })
            ->make();
    }

    public function select(Request $request){
        $packages = PreweddingPackage::where('status_id', 1)->get();

        $data = [];
        foreach ($packages as $package){
            $data[] = [
                'id' => $package->id,
                'text' => $package->name,
            ];
        }

        return \response($data);
    }

    public function selectDetail(Request $request){
        $term = trim($request->search);

        $user = Auth::guard('admin')->user();
        $userData = AdminUser::find($user->id);
        $branchId = $userData->branch_id;

        $packages = DB::table('packages')
            ->join('room_types', 'packages.room_type_id', '=', 'room_types.id')
            ->select('packages.id', 'packages.code', 'packages.name', 'packages.duration', 'room_types.name as room_type')
            ->where('status_id', '!=', 2)
            ->where(function($q) use ($term) {
                $q->where('packages.code', 'LIKE', '%'. $term. '%')
                    ->orWhere('packages.name', 'LIKE', '%'. $term. '%');
            })
            ->where('packages.branch_id', $branchId)
            ->orderBy('packages.room_type_id')
            ->orderBy('packages.code') // Then order by name
            ->get();

        $grouped_packages = [];

        foreach ($packages as $package) {
            $grouped_packages[$package->room_type]['text'] = $package->room_type;
            $grouped_packages[$package->room_type]['children'][] = [
                'id' => 'P-'.$package->id,
                'text' => $package->code . ' - ' . $package->name
            ];
        }

        $items = Item::where('branch_id', $branchId)
            ->where(function($q) use ($term){
                $q->where('code', 'LIKE', '%'. $term. '%')
                    ->orWhere('name', 'LIKE', '%'. $term. '%');
            });

        $items = $items->orderBy('id')
            ->get();

        foreach ($items as $item) {
            $grouped_packages[$item->category_id]['text'] = $item->item_category->name;
            $grouped_packages[$item->category_id]['children'][] = [
                'id' => 'I-'.$item->id,
                'text' => $item->code . ' - ' . $item->name
            ];
        }

        return Response::json(array_values($grouped_packages));
    }

    public function getAll(Request $request){
        $packages = Package::where('status_id', '!=', 2);

        if($request->get('category') != 'all'){
            $packages->where('room_type_id', $request->get('category'));
        }

        $data = [];
        foreach ($packages->get() as $package){
            $data[] = [
                'id' => Utilities::generateRandomStr(10),
                'package' => 'P-' . $package->id,
                'name' => $package->code . ' - ' . $package->name,
            ];
        }

        return \response([
            'data' => $data
        ]);
    }

    public function create(){
        $categories = RoomType::orderBy('sequence')->get();
        return view('admin.prewedding_package.create', compact('categories'));
    }

    public function store(Request $request){
        $this->validate($request, [
            'code' => 'required|max:255|unique:prewedding_packages,code',
            'name' => 'required|max:255',
            'details' => 'min:1',
        ], [
            'code.required' => 'Kode wajib diisi!',
            'code.max' => 'Maksimum karakter kode adalah 255!',
            'code.unique' => 'Kode sudah digunakan!',
            'name.required' => 'Nama wajib diisi!',
            'name.max' => 'Maksimum karakter nama adalah 255!',
            'details.min' => 'Wajib menambahkan treatment minimal 1!'
        ]);

        DB::beginTransaction();
        try {
            $details = $request->input('details') ?? [];
            $prices = $request->input('prices') ?? [];

            PreweddingPackage::create([
                'code' => $request->input('code'),
                'name' => $request->input('name'),
                'price' => $request->input('price'),
                'packages' => '#' . implode('#', $details) . '#',
                'package_prices' => '#' . implode('#', $prices) . '#',
                'status_id' => 1
            ]);

            DB::commit();

            Session::flash('success', 'Berhasil membuat paket!');
            return redirect()->route('admin.prewedding_package.index');
        } catch (\Exception $exception){
            DB::rollBack();
            Log::channel('prewedding_package')->error('[Store] - ' . $exception);

            return redirect()->back()->withInput($request->except('_token'))->withErrors([
                'Internal Server Error!'
            ]);
        }
    }

    public function edit($id){
        $categories = RoomType::orderBy('sequence')->get();
        $package = PreweddingPackage::find($id);
        $ids = explode('#', ltrim(rtrim($package->packages, '#'), '#'));
        $packagePrices = explode('#', ltrim(rtrim($package->package_prices, '#'), '#'));


        $details = [];
        $ct = 0;
        foreach ($ids as $id){
            $idSplit = explode('-', $id);
            if($idSplit[0] == 'P'){
                $p = Package::find($idSplit[1]);
                $name = $p->code . ' - ' . $p->name;
            }
            else {
                $item = Item::find($idSplit[1]);
                $name = $item->code . ' - ' . $item->name;
            }
            $details[] = [
                'id' => Utilities::generateRandomStr(10),
                'package' => $id,
                'price' => $packagePrices[$ct],
                'name' => $name
            ];
            $ct++;
        }

        return view('admin.prewedding_package.edit', compact('package', 'details', 'categories'));
    }

    public function update(Request $request){
        $this->validate($request, [
            'code' => 'required|max:255|unique:prewedding_packages,code,'.$request->input('id').',id',
            'name' => 'required|max:255',
            'details' => 'min:1',
        ], [
            'code.required' => 'Kode wajib diisi!',
            'code.max' => 'Maksimum karakter kode adalah 255!',
            'code.unique' => 'Kode sudah digunakan!',
            'name.required' => 'Nama wajib diisi!',
            'name.max' => 'Maksimum karakter nama adalah 255!',
            'details.min' => 'Wajib menambahkan treatment minimal 1!'
        ]);

        DB::beginTransaction();
        try {
            $details = $request->input('details') ?? [];
            $prices = $request->input('prices') ?? [];

            $header = PreweddingPackage::find($request->input('id'));
            $header->update([
                'code' => $request->input('code'),
                'name' => $request->input('name'),
                'price' => $request->input('price'),
                'packages' => '#' . implode('#', $details) . '#',
                'package_prices' => '#' . implode('#', $prices) . '#',
                'status_id' => 1
            ]);

            DB::commit();

            Session::flash('success', 'Berhasil mengubah paket!');
            return redirect()->route('admin.prewedding_package.index');
        } catch (\Exception $exception){
            DB::rollBack();
            Log::channel('prewedding_package')->error('[Store] - ' . $exception);

            return redirect()->back()->withInput($request->except('_token'))->withErrors([
                'Internal Server Error!'
            ]);
        }
    }
}
