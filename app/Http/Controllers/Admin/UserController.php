<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Transformer\UserPointHistoryTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function select(Request $request){
        $term = trim($request->search);

        $users = DB::table('users')
            ->where(function($q) use ($term){
                $q->where('phone', 'LIKE', '%'. $term. '%')
                    ->orWhere('name', 'LIKE', '%'. $term. '%');
            })
            ->orderBy('name')
            ->get();;

        $formatted_tags = [];

        foreach ($users as $user) {
            $formatted_tags[] = ['id' => $user->id, 'text' => $user->name . ' - ' . $user->phone];
        }

        return Response::json($formatted_tags);
    }

    public function search(Request $request)
    {
        try {
            $member = User::where('phone', '=', "+62".$request->input('phone'))->first();

            if($member == null){
                return response()->json([
                    'success' => false,
                    'message' => 'Member tidak ditemukan!'
                ]);
            }

            return response()->json([
                'name' => $member->name,
                'success' => true,
                'message' => 'Berhasil menemukan member!',
            ]);
        } catch (\Exception $e) {
            error_log($e);
            return Response::json($e, 500);
        }
    }
}
