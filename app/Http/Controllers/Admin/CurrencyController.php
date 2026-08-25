<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;


class CurrencyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }
    public function select(Request $request){

        $term = trim($request->search);

        $searchDatas = DB::table('currencies')
            ->select('id','code','name')
            ->where('status_id', 1)
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
