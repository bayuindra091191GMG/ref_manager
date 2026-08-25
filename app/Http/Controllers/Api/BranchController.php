<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libs\ApiUtilities;
use App\Models\Branch;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class BranchController extends Controller
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
            $branches = Branch::where('status_id', 1)->get();
            $result = [];
            foreach($branches as $branch){
                $result[] = [
                    'id'            => $branch->id,
                    'name'          => $branch->name,
                    'description'   => $branch->description,
                    'address'       => $branch->address,
                    'latitude'      => $branch->latitude,
                    'longitude'     => $branch->longitude,
                    'phone'         => $branch->phone,
                    'facilities'    => $branch->extra_text,
                    'image'         => asset('images/branches/' . $branch->image_filename),
                ];
            }
            $this->result['response'] = $result;
            $this->result['message'] = 'Get branches success';
            return Response::json($this->result, 200);
        }
        catch (\Exception $ex){
            Log::channel('api_exception')->error('Api/BranchController - get ERROR EX '. $ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Internal server error';
            return Response::json($this->result, 500);
        }
    }
}
