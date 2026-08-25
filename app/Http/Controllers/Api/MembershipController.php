<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libs\ApiUtilities;
use App\Models\AppMembership;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class MembershipController extends Controller
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

    public function get(){
        try{
            $db = AppMembership::all();
            $result = [];
            foreach($db as $d){
                $result[] = [
                    'code'                  => $d->code,
                    'name'                  => $d->name,
                    'min_spending'          => $d->min_spending,
                    'discount_percentage'   => $d->discount_percentage,
                    'img_path'              => $d->img_path ? asset("images/memberships/{$d->img_path}") : '-',
                    'description'           => $d->description,
                ];
            }

            $this->result['response'] = $result;
            $this->result['message'] = 'Success getting Membership Details!';
            return Response::json($this->result, 200);
        }
        catch(\Exception $ex){
            Log::channel('api_exception')->error('Api/MembershipController - get ERROR EX '. $ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Internal server error';
            return Response::json($this->result, 500);
        }
    }
}
