<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Configuration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AnnouncementController extends Controller
{
    public function index(){
        $user = Auth::guard('admin')->user();
        $config = Configuration::where('branch_id', $user->branch_id)
            ->where('key', 'announcement')
            ->first();
        $announcement = '';
        if($config != null){
            $announcement = $config->value;
        }

        return view('admin.announcement.index', compact('announcement'));
    }

    public function create(){
        $user = Auth::guard('admin')->user();
        $config = Configuration::where('branch_id', $user->branch_id)
            ->where('key', 'announcement')
            ->first();
        $announcement = '';
        if($config != null){
            $announcement = $config->value;
        }

        return view('admin.announcement.create', compact('announcement'));
    }

    public function store(Request $request){
        try{
            //save announcement
            $user = Auth::guard('admin')->user();
            $config = Configuration::where('branch_id', $user->branch_id)
                ->where('key', 'announcement')
                ->first();
            if($config == null){
                //create new config
                $newConfig = new Configuration();
                $newConfig->branch_id = $user->branch_id;
                $newConfig->key = 'announcement';
                $newConfig->value = $request->input('announcement');
                $newConfig->save();
            }
            else{
                $config->value = $request->input('announcement');
                $config->save();
            }
            Session::flash('success', 'Sukses mengubah Pengumuman!');
            return back();
        }
        catch (\Exception $ex){
            Session::flash('error', $ex);
            return back();
        }
    }
}
