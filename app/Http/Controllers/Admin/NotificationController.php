<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\FCMNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function form(){
        return view('admin.notification.test_form');
    }

    public function send(Request $request){
        try{
            $phone = $request->input('phone');
            // $receiverType = $request->input('receiver_type');

            $userDb = User::where('phone', $phone)->first();
            if(empty($userDb)){
                return "User with number ". $phone ."Not Found";
            }
            $userId = $userDb->id;

            $temp = Carbon::now('Asia/Jakarta');
            $nowDay = $temp->day;

            //Send notification to
            $body = $request->input('message');
            $title = $request->input('title');
            $notification = array(
                "title" => $title,
                "body"  => $body,
                //for IOS notification
                "sound"  => "default",
                "mutable_content"  => true,
                "content_available" => true
            );

            $dataModel = collect([
                'id'        => 70,
                'order_no'  => 'INV/2022/10/11000004',
                'type'      => 'home_service',
                'room_id'   => 3,
            ]);

            $notifData = array(
                "type_id"   => $request->input('type'),
                "id"        => $nowDay,
                "message"   => $body,
                "data_model"    => $dataModel,
            );

            //Push Notification to App.
            $messageId = "message-testing-".$nowDay.rand(0,999999);

            $isSuccess = FCMNotification::SendNotification($userId, $messageId, $notification, $notifData);

            return "TestingController - testSendNotif Result : ". json_encode($isSuccess);
        }
        catch (\Exception $exception){
            dd($exception);
            return $exception;
        }
    }
}
