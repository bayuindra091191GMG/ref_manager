<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libs\ApiUtilities;
use App\Libs\Doku;
use App\Models\OrderVoucherDetail;
use App\Models\OrderVoucherHeader;
use App\Models\OrderVoucherPayment;
use App\Models\UserVoucher;
use App\Models\UserVoucherCode;
use App\Models\VoucherPackage;
use App\Notifications\FCMNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;

class DokuWebhookController extends Controller
{
    public function receiveNotification(Request $request){
        try {
            $data = $request->json()->all();
            if(!isset($data['transaction']['date'])){
                return Response::json([
                    'message'   => 'No Data Received',
                ], 400);
            }

            Log::channel('doku_webhook')->info('receiveNotification Notif: ' . json_encode($data));
            Log::channel('doku_webhook')->info('receiveNotification Signature: '. $request->header('Signature').
                ' TimeStamp:' . $request->header('Request-Timestamp'));

            $timeStamp = $data['transaction']['date'];
            $requestTarget = '/checkout/v1/payment';

            if($data['channel']['id'] === 'EMONEY_OVO'){
                $signature = Doku::generateSignature(json_encode($data), $data['transaction']['originalRequestId'], $timeStamp, $requestTarget);
            }
            else{
                $signature = Doku::generateSignature(json_encode($data), $data['transaction']['original_request_id'], $timeStamp, $requestTarget);
            }

            Log::channel('doku_webhook')->info('Generated Sign: '. $signature);
            if($request->header('Signature') != $signature){
                Log::channel('doku_webhook')->error($data['order']['invoice_number']. ' WRONG SIGNATURE');
//                return Response::json([
//                    'message' => 'wrong signature!'
//                ], 400);
            }

            if($data['transaction']['status'] === 'SUCCESS'){
                // Check for the Transactions
                if(DB::table('order_voucher_headers')
                    ->where('code', $data['order']['invoice_number'])
                    ->exists()){

                    $orderHeader = OrderVoucherHeader::where('code', '=', $data['order']['invoice_number'])
                        ->first();

                    if($orderHeader->status_id !== 3){
                        Log::channel('doku_webhook')->info($orderHeader->code. ' STATUS NOT PENDING');
                        return Response::json(
                            [
                                'message'   => 'Notification received'
                            ], 200
                        );
                    }

                    // Count bonus point
//                    $user = User::where('id', $orderHeader->createdBy)->first();
//                    $membership = AppMembership::where('id', $user->membership_id)->first();
//                    $pointBonus = floor($orderHeader->grand_total / $membership->point_conversion);

                    $orderHeader->status_id = 20;
                    $orderHeader->pg_status = 'SUCCESS';
                    $orderHeader->pg_service = $data['service']['id'];
                    $orderHeader->pg_acquirer = $data['acquirer']['id'];
                    $orderHeader->pg_settlement_date = Carbon::parse($data['transaction']['date'])->toDateTimeString();
//                    $orderHeader->point_bonus = round($pointBonus);
                    $orderHeader->save();

//                    $user->point += $orderHeader->point_bonus;
//                    $user->save();

//                    UserPointHistory::create(
//                        [
//                            'user_id'       => $user->id,
//                            'user_name'     => $user->name,
//                            'user_phone'    => $user->phone,
//                            'user_email'    => $user->email,
//                            'type'          => 'order_voucher',
//                            'amount_add'    => $orderHeader->point_bonus,
//                            'amount_sub'    => 0,
//                            'amount_result' => $user->point,
//                            'reference_1'   => $orderHeader->code,
//                        ],
//                    );

                    // Create user voucher
                    if (
                        Schema::hasColumn('user_vouchers', 'order_voucher_header_id')
                        && UserVoucher::where('order_voucher_header_id', $orderHeader->id)->exists()
                    ) {
                        Log::channel('doku_webhook')->info($orderHeader->code.' VOUCHER CODES ALREADY GENERATED');
                    } else {
                        $orderDetails = OrderVoucherDetail::where('header_id', $orderHeader->id)->get();
                        foreach($orderDetails as $dt){
                            $voucher = VoucherPackage::where('id', $dt->voucher_id)->first();

                            if($voucher != null){
                                $expiredDays = (int) ($voucher->expired ?? 0);
                                $tmpDate = $expiredDays > 0
                                    ? Carbon::now('Asia/Jakarta')->addDays($expiredDays)
                                    : null;
                                $userVoucherPayload = [
                                    'voucher_id'        => $voucher->id,
                                    'voucher_code'      => $voucher->code,
                                    'terms_condition'   => $voucher->name,
                                    'status_id'         => 1,
                                    'type'              => 'voucher',
                                    'user_id'           => $orderHeader->created_by,
                                ];

                                if (Schema::hasColumn('user_vouchers', 'order_voucher_header_id')) {
                                    $userVoucherPayload['order_voucher_header_id'] = $orderHeader->id;
                                }

                                $userVc = UserVoucher::create(attributes: $userVoucherPayload);

                                for($i=0; $i<$dt->qty; $i++){
                                    UserVoucherCode::create(attributes: [
                                        'code'          => ApiUtilities::generateUniqueVoucherCode(),
                                        'header_id'     => $userVc->id,
                                        'expired_date'  => $tmpDate,
                                        'is_used'       => 0,
                                    ]);
                                }
                            }
                            else{
                                Log::channel('doku_webhook')->error("DokuWebhookController - voucher not found error: ". $dt->voucher_id);
                            }
                        }
                    }

                    // $dataModel = collect([
                    //     'id'        => $header->id,
                    //     'order_no'  => $bookingHeader->code,
                    //     'type'      => $bookingHeader->type,
                    // ]);

                    // FCMNotification::setNotification('User',
                    //     $bookingHeader->user_id,
                    //     'Konfirmasi Pembayaran',
                    //     'Pesanan '. $bookingHeader->code. ' OURA',
                    //     'Pembayaran untuk pesanan '. $bookingHeader->code. ' telah terkonfirmasi',
                    //     200,
                    //     $dataModel);
                }
            }
            elseif($data['transaction']['status'] === 'FAILED' || $data['transaction']['status'] === 'TIMEOUT'){
                // Check for the Transactions
                if(DB::table('order_voucher_headers')
                    ->where('code', $data['order']['invoice_number'])
                    ->exists()){

                    $orderHeader = OrderVoucherHeader::where('code', '=', $data['order']['invoice_number'])
                        ->first();

                    if($orderHeader->status_id !== 3){
                        Log::channel('doku_webhook')->info($orderHeader->code. ' STATUS NOT PENDING');
                        return Response::json(
                            [
                                'message'   => 'Notification received'
                            ], 200
                        );
                    }

                    $canceledAt = Carbon::parse($data['transaction']['date'])->toDateTimeString();

                    $orderHeader->status_id = 10;
                    $orderHeader->pg_status = $data['transaction']['status'];
                    $orderHeader->canceled_at = $canceledAt;
                    $orderHeader->save();

                    $orderPayment = OrderVoucherPayment::where('header_id', $orderHeader->id)->first();
                    $orderPayment->pg_status = $data['transaction']['status'];
                    $orderPayment->save();
                }
            }

            return Response::json(
                [
                    'message'   => 'Notification received'
                ], 200
            );
        }
        catch (\Exception $ex){
            Log::channel('doku_webhook')->error("DokuWebhookController - receiveNotification error: ". $ex);
            return Response::json([
                'message' => "Sorry Something went wrong! {$ex}"
            ], 500);
        }
    }
}
