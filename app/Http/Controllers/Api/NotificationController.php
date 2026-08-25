<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libs\ApiUtilities;
use App\Libs\Utilities;
use App\Models\CartHeader;
use App\Models\Notification;
use App\Models\User;
use App\Notifications\FCMNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    private $result;

    public function __construct(){
        $this->result = [
            'error'         => [],
            'message'       => '',
            'warning'       => '',
            'request_id'    => ApiUtilities::generateRandomShortString(20),
            'response'      => []
        ];
    }

    /**
     * API Function for fetching user notifications
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNotifications(Request $request){
        try {
            $user = $request->user();

            // Get skip filter
            $skip = 0;
            if($request->exists('skip')){
                if($request->query('skip') != null){
                    $skip = $request->query('skip');
                }
            }

            $notifications = DB::table('user_notifications')
                ->where('user_id', '=', $user->id)
                ->orderBy('created_at', 'desc')
                ->take(20)
                ->skip($skip)
                ->get();

            $notificationListArr = [];
            foreach ($notifications as $notification){
                $model = json_decode($notification->model);

                $notificationListArr[] = [
                    'id' => $notification->id,
                    'type' => $notification->notif_type,
                    'model' => $model,
                    'title' => $notification->title,
                    'body' => $notification->body,
                    'date' => Carbon::parse($notification->created_at)->locale('id_ID')->translatedFormat('d F Y'),
                    'time' => Carbon::parse($notification->created_at)->format('H:i')
                ];
            }

            $this->result['response'] = $notificationListArr;
            $this->result['message'] = 'Success getting user notification list!';
            return Response::json($this->result, 200);
        }
        catch (\Exception $ex){
            Log::channel('api_exception')->error('Api/Customer/NotificationController - getNotifications ERROR EX '. $ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Internal server error';
            return Response::json($this->result, 500);
        }
    }
}
