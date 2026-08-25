<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RoomType;
use App\Models\VoucherPackage;
use App\Models\VoucherPackageDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class VoucherPackageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(){
        return view('admin.voucher_package.index');
    }

    public function datatable(Request $request){
        $query = VoucherPackage::query();

        return DataTables::of($query)
            ->addColumn('action', function ($row){
                $buttons = '';

                $buttons .= '<a href="' . route('admin.voucher_package.edit', ['id' => $row->id]) . '" class="btn btn-primary me-1"><i class="far fa fa-edit"></i> Edit</a>';

                return $buttons;
            })
            ->make();
    }

    public function create(){
        $categories = RoomType::orderBy('sequence')->get();
        return view('admin.voucher_package.create', compact('categories'));
    }

    public function store(Request $request){
        $this->validate($request, [
            'code' => 'required|max:255|unique:voucher_packages,code',
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

            $filename = null;
            $img = $request->file('image');
            if($img){
                $filename = 'voucher_transaction_' . Carbon::now()->format('dmY_His') . '.' . $img->getClientOriginalExtension();
                $img->storeAs('', $filename, 'vouchers');
            }

            $header = VoucherPackage::create([
                'code' => $request->input('code'),
                'name' => $request->input('name'),
                'price' => $request->input('price'),
                'expired' => $request->input('expired'),
                'is_shown' => $request->input('is_shown') ?? 0,
                'img' => $filename,
                'status_id' => 1
            ]);

            $details = $request->input('details') ?? [];
            foreach ($details as $detail){
                VoucherPackageDetail::create([
                    'header_id' => $header->id,
                    'package_id' => $detail['package_id'],
                ]);
            }

            DB::commit();

            Session::flash('success', 'Berhasil membuat voucher transaksi!');
            return redirect()->route('admin.voucher_package.index');
        } catch (\Exception $exception){
            DB::rollBack();
            Log::channel('voucher_package')->error('[Store] - ' . $exception);

            return redirect()->back()->withInput($request->except('_token'))->withErrors([
                'Internal Server Error!'
            ]);
        }
    }

    public function edit($id){
        $voucher = VoucherPackage::find($id);
        $details = [];

        foreach ($voucher->details as $detail){
            $details[] = [
                'id' => $detail->id,
                'package' => $detail->package_id,
                'name' => $detail->package->code . ' - ' . $detail->package->name
            ];
        }

        $categories = RoomType::orderBy('sequence')->get();

        return view('admin.voucher_package.edit', compact('voucher', 'details', 'categories'));
    }

    public function update(Request $request){
        $this->validate($request, [
            'code' => 'required|max:255|unique:voucher_packages,code,'.$request->input('id').',id',
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
            $header = VoucherPackage::find($request->input('id'));

            $filename = null;
            $img = $request->file('image');
            if($img){
                if($header->img != null){
                    Storage::disk('vouchers')->delete($header->img);
                }
                $filename = 'voucher_transaction_' . Carbon::now()->format('dmY_His') . '.' . $img->getClientOriginalExtension();
                $img->storeAs('', $filename, 'vouchers');
            }

            $header->update([
                'code' => $request->input('code'),
                'name' => $request->input('name'),
                'price' => $request->input('price'),
                'expired' => $request->input('expired'),
                'is_shown' => $request->input('is_shown') ?? 0,
                'img' => $filename ?? $header->img,
                'status_id' => $request->input('status'),
            ]);

            $details = $request->input('details') ?? [];
            foreach ($details as $detail){
                $check = VoucherPackageDetail::find($detail['id']);
                if($check){
                    $check->update([
                        'header_id' => $header->id,
                        'package_id' => $detail['package_id'],
                    ]);
                } else {
                    VoucherPackageDetail::create([
                        'header_id' => $header->id,
                        'package_id' => $detail['package_id'],
                    ]);
                }
            }

            DB::commit();

            Session::flash('success', 'Berhasil mengubah voucher transaksi!');
            return redirect()->route('admin.voucher_package.index');
        } catch (\Exception $exception){
            DB::rollBack();
            Log::channel('voucher_package')->error('[Update] - ' . $exception);

            return redirect()->back()->withInput($request->except('_token'))->withErrors([
                'Internal Server Error!'
            ]);
        }
    }

    public function delete(Request $request){

    }
}
