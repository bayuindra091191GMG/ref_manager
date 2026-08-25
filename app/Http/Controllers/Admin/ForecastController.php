<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;

class ForecastController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function showForecast(){
        try {
            $admin = Auth::guard('admin')->user();

            $forecasts = DB::table('transaction_details')
                ->join('transaction_headers', 'transaction_details.header_id','=', 'transaction_headers.id')
                ->join('packages', 'transaction_details.package_id','=', 'packages.id')
                ->join('rooms', 'transaction_details.room_id','=', 'rooms.id')
                ->select('transaction_details.id as detail_id',
                    'transaction_details.start_time as start_time',
                    'packages.name as package_name',
                    'rooms.name as room_name',
                    'rooms.number as room_number',
                    DB::raw('DATE_ADD(transaction_details.start_time, INTERVAL packages.duration MINUTE) as end_time'))
                ->where('transaction_headers.branch_id', '=', $admin->branch_id)
                ->whereNotNull('transaction_details.package_id')
                ->where('transaction_headers.status_id', '=', 8)
                ->get();

//            dd($forecasts);

            $forecastCollects = collect();
            foreach ($forecasts as $forecast){
                $detailPartners = DB::table('transaction_detail_partners')
                    ->join('partners', 'transaction_detail_partners.partner_id', '=', 'partners.id')
                    ->select('partners.number as partner_number')
                    ->where('transaction_detail_id', '=', $forecast->detail_id)
                    ->get();

                $partnerNumbers = '';
                $idx = 0;
                foreach($detailPartners as $detailPartner){
                    $partnerNumbers .= $detailPartner->partner_number;

                    $idx++;

                    if($idx < $detailPartners->count()){
                        $partnerNumbers .= ',';
                    }
                }

                $carbonEndTime = Carbon::parse($forecast->end_time);
                if(Carbon::now('Asia/Jakarta')->greaterThanOrEqualTo($carbonEndTime)){
                    $remainingMinuteStr = 'END';
                }
                else{
                    $remainingMinuteStr = Carbon::now('Asia/Jakarta')->diffInMinutes($carbonEndTime);
                }

                $forecastCollects->push([
                    'package_name' => $forecast->package_name,
                    'room_name' => $forecast->room_name,
                    'room_number' => $forecast->room_number,
                    'remaining_minutes' => $remainingMinuteStr,
                    'real_remaining_minutes' => Carbon::now('Asia/Jakarta')->diffInMinutes($carbonEndTime, false),
                    'partner_numbers' => $partnerNumbers,
                    'start_time' => Carbon::parse($forecast->start_time)->format('H:i'),
                    'end_time' => Carbon::parse($forecast->end_time)->format('H:i')
                ]);
            }

            $forecastCollects->sortBy('real_remaining_minutes');

            return view('admin.forecast.show', compact('forecastCollects'));
        }
        catch (\Exception $ex){
            Log::channel('pos')->error('Admin/ForecastController - show '. $ex);
            Session::flash('error', $ex);
            return redirect()->back();
        }
    }

    public function refreshForecast(){
        try {
            $admin = Auth::guard('admin')->user();

            $forecasts = DB::table('transaction_details')
                ->join('transaction_headers', 'transaction_details.header_id','=', 'transaction_headers.id')
                ->join('packages', 'transaction_details.package_id','=', 'packages.id')
                ->join('rooms', 'transaction_details.room_id','=', 'rooms.id')
                ->select('transaction_details.id as detail_id',
                    'transaction_details.start_time as start_time',
                    'transaction_details.is_extended as is_extended',
                    'transaction_details.extension_times as extension_times',
                    'packages.extend_duration as extend_duration',
                    'packages.name as package_name',
                    'rooms.name as room_name',
                    'rooms.number as room_number',
                    DB::raw('DATE_ADD(transaction_details.start_time, INTERVAL packages.duration MINUTE) as end_time'))
                ->where('transaction_headers.branch_id', '=', $admin->branch_id)
                ->whereNotNull('transaction_details.package_id')
                ->where('transaction_headers.status_id', '=', 8);

            $forecasts = $forecasts->orderby('end_time')->get();
//            dd($forecasts);

            $forecastCollects = collect();
            foreach ($forecasts as $forecast){
                $detailPartners = DB::table('transaction_detail_partners')
                    ->join('partners', 'transaction_detail_partners.partner_id', '=', 'partners.id')
                    ->select('partners.number as partner_number')
                    ->where('transaction_detail_id', '=', $forecast->detail_id)
                    ->get();

                $partnerNumbers = '';
                $idx = 0;
                foreach($detailPartners as $detailPartner){
                    $partnerNumbers .= $detailPartner->partner_number;
                    $idx++;

                    if($idx < $detailPartners->count()){
                        $partnerNumbers .= ',';
                    }
                }
                $minutes = 0;
                if($forecast->is_extended == 1){
                    $minutes = $forecast->extension_times * $forecast->extend_duration;
                }

                $carbonEndTime = Carbon::parse($forecast->end_time)->addMinutes($minutes);
                if(Carbon::now('Asia/Jakarta')->greaterThanOrEqualTo($carbonEndTime)){
                    $remainingMinuteStr = 'END';
                }
                else{
                    $remainingMinuteStr = Carbon::now('Asia/Jakarta')->diffInMinutes($carbonEndTime);
                }

                $forecastCollects->push([
                    'package_name' => $forecast->package_name,
                    'room_name' => $forecast->room_name,
                    'room_number' => $forecast->room_number,
                    'remaining_minutes' => $remainingMinuteStr,
                    'real_remaining_minutes' => Carbon::now('Asia/Jakarta')->diffInMinutes($carbonEndTime, false),
                    'partner_numbers' => $partnerNumbers,
                    'start_time' => Carbon::parse($forecast->start_time)->format('H:i'),
                    'end_time' => Carbon::parse($carbonEndTime)->format('H:i')
                ]);
            }

            $forecastCollects->sortBy('real_remaining_minutes');

            return Response::json($forecastCollects, 200);
        }
        catch (\Exception $ex){
            Log::channel('pos')->error('Admin/ForecastController - refreshForecast '. $ex);
            return Response::json($ex, 500);
        }
    }
}
