<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\Branch;
use App\Models\Locker;
use App\Models\Status;
use App\Models\TransactionDetail;
use App\Models\TransactionHeader;
use App\Transformer\LockerTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class LockerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(){
        return view('admin.locker.index');
    }

    public function getIndex(Request $request){
        $user = Auth::guard('admin')->user();
        $lockers = Locker::with('branch')
            ->where('branch_id', $user->branch_id)
            ->where('lockers.id', '>', 0);

        return DataTables::of($lockers)
            ->setTransformer(new LockerTransformer)
            ->make(true);
    }

    public function create(){
        $branches = Branch::all();

        $data = [
            'branches'      => $branches,
        ];

        return view('admin.locker.create')->with($data);
    }

    public function store(Request $request){
        try{
            $validator = Validator::make($request->all(),[
                'number' => 'required',
//                'branch_id' => 'required',
            ],[
                'number.required' => 'Nomor Locker wajib diisi!',
//                'branch_id.required'    => 'Branch wajib dipilih!',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput($request->all());
            }

            $admin = Auth::guard('admin')->user();

            if(DB::table('lockers')
                ->where('branch_id', '=', $admin->branch_id)
                ->where('number', '=', $request->input('number'))
                ->exists()){
                Session::flash('error', 'Nomor Loker sudah terdaftar!');
                return back()->withInput($request->all());
            }

            $locker = new Locker();
            $locker->name = $request->input('name');
            $locker->number = $request->input('number');
            $locker->description = $request->input('description');
//            $locker->branch_id = $request->input('branch_id');
            $locker->branch_id = $admin->branch_id;
            $locker->status_id = 5;
            $locker->created_by = $admin->id;
            $locker->updated_by = $admin->id;
            $locker->save();

            Session::flash('success', 'Sukses membuat data locker baru!');
            return redirect()->route('admin.locker.index');
        }
        catch (\Exception $ex){
            Session::flash('error', 'Maaf terjadi kesalahan sistem!' . $ex);
            return back()->withInput($request->all());
        }
    }

    public function edit($id){
        $branches = Branch::all();
        $locker = Locker::where('id', $id)->first();
        $statuses = Status::where('id', 6)->orWhere('id', 7)->get();
        if($locker == null){
            Session::flash('error', 'Locker tidak ditemukan!');
            return back();
        }

        $data = [
            'locker'        => $locker,
            'branches'      => $branches,
            'statuses'      => $statuses,
        ];

        return view('admin.locker.edit')->with($data);
    }

    public function update(Request $request){
        try{
            $validator = Validator::make($request->all(),[
                'number'    => 'required',
//                'branch_id' => 'required',
            ],[
                'number.required'       => 'Nomor Locker wajib diisi!',
//                'branch_id.required'    => 'Branch wajib dipilih!',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput($request->all());
            }

            $admin = Auth::guard('admin')->user();

            if(DB::table('lockers')
                ->where('id', '!=', $request->input('id'))
                ->where('branch_id', '=', $admin->branch_id)
                ->where('number', '=', $request->input('number'))
                ->exists()){
                Session::flash('error', 'Nomor Locker sudah terdaftar!');
                return back()->withInput($request->all());
            }

            $locker = Locker::where('id', $request->input('id'))->first();
            if($locker == null){
                Session::flash('error', 'Locker tidak ditemukan!');
                return back()->withInput($request->all());
            }

            $locker->name = $request->input('name');
            $locker->number = $request->input('number');
            $locker->description = $request->input('description');
            $locker->status_id = $request->input('status_id');
            $locker->updated_by = $admin->id;
            $locker->save();

            Session::flash('success', 'Sukses mengubah data locker!');
            return redirect()->route('admin.locker.index');
        }
        catch (\Exception $ex){
            Session::flash('error', 'Maaf terjadi kesalahan sistem!' . $ex);
            return back()->withInput($request->all());
        }
    }

    public function delete(Request $request){
        try{
            $locker = Locker::where('id', $request->input('id'))->first();
            if($locker == null){
                Session::flash('error', 'Locker tidak ditemukan!');
                return back();
            }
            $locker->delete();

            return Response::json('success', 200);
        }
        catch (\Exception $ex){
            return Response::json('error', 200);
        }
    }

    public function select(Request $request){
        $term = trim($request->search);

        $user = Auth::guard('admin')->user();
        $userData = AdminUser::find($user->id);
        $branchId = $userData->branch_id;

        $lockers = DB::table('lockers')
            ->where(function($q) use ($term){
                $q->where('code', 'LIKE', '%'. $term. '%')
                    ->orWhere('number', 'LIKE', '%'. $term. '%');
            });
        $lockers = $lockers->where('branch_id', $branchId)->orderBy('number')
            //->where('status_id', 5)
            ->get();

        $formatted_tags = [];

        foreach ($lockers as $locker) {
            $canGoIn = true;
            $header = TransactionHeader::where('locker_id', $locker->id)
                ->where('status_id', 8)
                ->first();
            if($header != null){
                $dt = TransactionDetail::where('header_id', $header->id)
                    ->whereNotNull('package_id')
                    ->where('status_id', 8)
                    ->first();
                if($dt != null){
                    $canGoIn = false;
                }
            }

            if($canGoIn){
                $formatted_tags[] = ['id' => $locker->id, 'text' => $locker->number];
            }
        }

        return Response::json($formatted_tags);
    }
}
