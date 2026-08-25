<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class MonitoringPartnerController extends Controller
{
    public function index(){
        $partners = Partner::all();
        $tmpPartners = [];
        foreach ($partners as $partner){
            $nPartner = [
                'element_id'    => 'partner'.$partner->number,
                'id'            => $partner->id,
                'name'          => $partner->name,
                'type'          => $partner->type,
                'number'        => $partner->number,
                'class'         => 'custom-circle green'
            ];

            $nPartner['class'] = match ($partner->status_id) {
                11 => 'custom-circle yellow',
                12 => 'custom-circle grey',
                5 => 'custom-circle green',
                6 => 'custom-circle red',
            };

            $tmpPartners[] = $nPartner;
        }

        $jsonPartners = json_encode($tmpPartners);

        $data = [
            'jsonPartners'  => $jsonPartners,
        ];

        return view('admin.monitor.partner')->with($data);
    }

    public function setPartnerActiveOrNotActive(Request $request){
        try{
            $partnerId = $request->input('partner_id');
            $partner = Partner::where('id', $partnerId)->first();
            if($request->input('status_id') != 1 || $request->input('status_id') != 0){
                return Response::json('Salah tipe status!', 400);
            }

            if($partner == null){
                return Response::json('Partner Tidak Ditemukan!', 404);
            }

            //Update Partner Status
            $partner->is_present = $request->input('status_id');
            $partner->status_id = 6;
            $partner->save();

            $msg = '';

            if($request->input('status_id') == 1){
                $msg = 'Therapis Hadir!';
            }
            else if($request->input('status_id') == 0){
                $msg = 'Therapis Sudah tidak Hadir!';
            }

            return Response::json($msg, 200);
        }
        catch (\Exception $ex){
            return Response::json($ex, 500);
        }
    }

    public function setPartnerToReady(Request $request){
        try{
            $partnerId = $request->input('partner_id');
            $partner = Partner::where('id', $partnerId)->first();
            if($request->input('status_id') != 1 || $request->input('status_id') != 0){
                return Response::json('Salah tipe status!', 400);
            }

            if($partner == null){
                return Response::json('Partner Tidak Ditemukan!', 404);
            }

            //Update Partner Status
            $partner->is_present = 1;
            $partner->status_id = 5;
            $partner->save();

            $msg = 'Therapis Siap!';

            return Response::json($msg, 200);
        }
        catch (\Exception $ex){
            return Response::json($ex, 500);
        }
    }
}
