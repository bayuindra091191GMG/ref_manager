<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\CustomerImport;
use App\Jobs\ImportJob;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;


class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }


    public function importCustomerProcess(Request $request){

        $request->validate([
            'file' => 'required|mimes:xlsx,csv',
        ]);

        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('imported_file', $fileName, 'public');

        // Kirim file ke Job Queue
        ImportJob::dispatch($filePath);

        // Excel::import(new UserImport, $request->file('file'));

        return 'berhasil, harap tunggu data masuk';
    }


    public function select(Request $request){

        $term = trim($request->search);

        $user = Auth::guard('admin')->user();
        $userData = AdminUser::find($user->id);

        $searchDatas = DB::table('customers')
            ->select('id','code','name')
            ->where('branch_id', $userData->branch_id)
            ->where('delete_status', '!=',  1)
            ->where(function($q) use ($term){
                $q->where('code', 'LIKE', '%'. $term. '%')
                    ->orWhere('name', 'LIKE', '%'. $term. '%');
            })
            ->orderBy('code')
            ->get();

        $formatted_tags = [];

        foreach ($searchDatas as $searchData) {
            $formatted_tags[] = ['id' => $searchData->id, 'text' => $searchData->code. " - " .$searchData->name];
        }

        return Response::json($formatted_tags);
    }
}
