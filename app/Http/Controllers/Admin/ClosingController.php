<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\Partner;
use App\Models\Room;
use App\Models\TempBooking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class ClosingController extends Controller
{
    public function dailyClose(){
        try{
            $user = Auth::guard('admin')->user();
            $userData = AdminUser::find($user->id);

            //partner status
            $partners = Partner::where('branch_id', $userData->branch_id)->get();
            foreach ($partners as $partner){
                $partner->is_present = 0;
                $partner->status_id = 5;
                $partner->save();
            }

            $rooms = Room::where('branch_id', $userData->branch_id)->get();
            foreach ($rooms as $room){
                $room->status_id = 5;
                $room->save();
            }

            $tempBook = TempBooking::where('branch_id', $userData->branch_id)
                ->where('created_at', '>=', Carbon::now('Asia/Jakarta'))
                ->get();
            foreach ($tempBook as $item) {
                $item->status_id = 9;
                $item->save();
            }

            Session::flash('success', 'Sukses closing Store!');
            return back();
        }
        catch (\Exception $ex){
            Log::error($ex);
            Session::flash('error', 'Maaf terjadi kesalahan!');
            return back();
        }
    }
}
