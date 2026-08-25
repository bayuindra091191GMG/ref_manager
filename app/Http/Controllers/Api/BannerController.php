<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libs\ApiUtilities;
use App\Models\Banner;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class BannerController extends Controller
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
     * API Function to get all banners
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function get(){
        try {
            $banners = Banner::where('status_id', '=', 1)
                ->orderBy('index')
                ->get();

//            dd($banners);

            $bannerArr = [];
            foreach ($banners as $banner){

                $type = 'EMPTY';
                $valueId = 0;
                $valueName = '';
                $data = [];

                if ($banner->type == "branch") {
                    $type = 'branch';
                    $valueId = $banner->branch_id;
                }

                // if ($banner->type == "voucher") {
                //     $type = 'voucher';
                //     $valueId = $banner->coupon_id;

                //     $data = [
                //         'id'                => $banner->coupon->id,
                //         'code'              => $banner->coupon->code,
                //         'img'               => $banner->coupon->img_path ? asset("images/coupons/{$banner->coupon->img_path}") : '-',
                //         'disc_percentage'   => $banner->coupon->discount_percentage ?? 0,
                //         'disc_amount'       => $banner->coupon->discount_amount ?? 0,
                //         'duration'          => $banner->coupon->duration ?? 0,
                //         'price'             => $banner->coupon->price ?? 0,
                //         'terms_condition'   => $banner->coupon->terms_condition ?? '-',
                //     ];
                // }

                $bannerArr[] = [
                    'banner_id' => $banner->id,
                    'image_path' => url('images/banners/'. $banner->image_path),
                    'type' => $type,
                    'value_id' => $valueId,
                    'value_name' => $valueName,
                    'url' => $banner->url ?? '',
                    'model' => (object) $data,
                ];
            }

            $this->result['response'] = $bannerArr;
            $this->result['message'] = 'Get banners success';
            return Response::json($this->result, 200);
        }
        catch (\Exception $ex){
            Log::channel('api_exception')->error('Api/BannerController - getBanners ERROR EX '. $ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Internal server error';
            return Response::json($this->result, 500);
        }
    }
}
