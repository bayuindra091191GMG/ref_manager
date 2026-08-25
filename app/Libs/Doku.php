<?php
/**
 * Created by PhpStorm.
 * User: GMG-Developer
 * Date: 15/09/2017
 * Time: 9:59
 */

namespace App\Libs;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class Doku
{
    /**
     * Doku function to get Payment URL.
     *
     * @return mixed|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function jokulCheckout($totalPayment,
                                         $invoiceNo,
                                         $paymentType,
                                         $randomStr)
    {
        try{
            $mode = DB::table('configurations')
                ->where('key', '=', value: 'doku_mode')
                ->value('value');

            if($mode === 'sandbox'){
                $host = DB::table('configurations')
                    ->where('key', '=', 'doku_sandbox_host')
                    ->value('value');

                $clientId = DB::table('configurations')
                    ->where('key', '=', 'doku_sandbox_client_id')
                    ->value('value');
            }
            else{
                $host = DB::table('configurations')
                    ->where('key', '=', 'doku_production_host')
                    ->value('value');

                $clientId = DB::table('configurations')
                    ->where('key', '=', 'doku_production_client_id')
                    ->value('value');
            }

            $url = $host. '/checkout/v1/payment';

            $jsonObject = [
                'order'     => [
                    'amount'            => $totalPayment,
                    'invoice_number'    => $invoiceNo,
                    'line_items'        => []
                ],
                'payment'   => [
                    'payment_due_date'      => 180,
                    'payment_method_types'  => [
                        $paymentType
                    ]
                ],
//                'additional_info' => [
//                    'settlement' => [
//                        [
//                            "bank_account_settlement_id" => $branch->bank_pg_settlement_id,
//                            "value" => 100,
//                            "type" => "PERCENTAGE"
//                        ]
//                    ],
//                    "hold_settlement" => true
//                ]
            ];

            $tmpItem = [
                'name'  => $invoiceNo,
                'price' => floatval($totalPayment),
                'quantity'  => 1
            ];

            array_push($jsonObject['order']['line_items'], $tmpItem);

//            $tmpItem = [
//                'name'  => 'Biaya Transaksi',
//                'price' => floatval($totalPayment),
//                'quantity'  => 1
//            ];
//
//            array_push($jsonObject['order']['line_items'], $tmpItem);

            $jsonBody = json_encode($jsonObject);

            Log::channel('doku_api')->info('/checkout/v1/payment JSON REQ: '. $jsonBody);

            $currTimeStamp = Carbon::now('UTC');
            $requestTarget = '/checkout/v1/payment';

            $signature = Doku::generateSignature($jsonBody,
                $randomStr,
                $currTimeStamp->toIso8601ZuluString(),
                $requestTarget);

            $client = new Client([
                'verify'    => false,
                'headers' => [
                    'Client-Id'         => $clientId,
                    'Request-Id'        => $randomStr,
                    'Request-Timestamp' => $currTimeStamp->toIso8601ZuluString(),
                    'Signature'         => $signature,
                    'Request-Target'    => $requestTarget
                ]
            ]);

            $request = $client->request('POST', $url, [
                'json'  => $jsonObject
            ]);

            Log::channel('doku_api')->info('/checkout/v1/payment JSON RESP: '. $request->getBody());

            if($request->getStatusCode() == 200){
                return [
                    'error' => 'none',
                    'response' => json_decode($request->getBody()),
                ];
            }
            else{
                Log::channel('doku_api')->error('/checkout/v1/payment STATUS CODE: '. $request->getStatusCode());
                Log::channel('doku_api')->error('/checkout/v1/payment JSON RESP: '. $request->getBody());

                return [
                    'error' => 'other_status_code',
                    'response' => json_decode($request->getBody()),
                ];
            }
        }
        catch (\Exception $ex){
            Log::error('libs/Doku - jokulCheckout error EX: '. $ex);
            Log::channel('doku_api')->error('/checkout/v1/payment EXCEPTION: '. $ex);

            return [
                'error' => 'exception',
                'response' => $ex->getMessage(),
            ];
        }
    }

    public static function jokulPushOvoPayment($totalPayment,
                                               $invoiceNo,
                                               $phoneNumber,
                                               $randomStr)
    {
        try{
            $mode = DB::table('configurations')
                ->where('key', '=', 'doku_mode')
                ->value('value');

            if($mode === 'sandbox'){
                $host = DB::table('configurations')
                    ->where('key', '=', 'doku_sandbox_host')
                    ->value('value');

                $clientId = DB::table('configurations')
                    ->where('key', '=', 'doku_sandbox_client_id')
                    ->value('value');

                $clientSecret = DB::table('configurations')
                    ->where('key', '=', 'doku_sandbox_client_secret')
                    ->value('value');
            }
            else{
                $host = DB::table('configurations')
                    ->where('key', '=', 'doku_production_host')
                    ->value('value');

                $clientId = DB::table('configurations')
                    ->where('key', '=', 'doku_production_client_id')
                    ->value('value');

                $clientSecret = DB::table('configurations')
                    ->where('key', '=', 'doku_production_client_secret')
                    ->value('value');
            }

            $totalPayment = intval($totalPayment);

            // Generate checksum
            $stringToBeHashed = $totalPayment. $clientId. $invoiceNo. $phoneNumber. $clientSecret;
            $checkSum = Hash::make($stringToBeHashed);

            $url = $host. '/ovo-emoney/v1/payment';

            $jsonObject = [
                'client' => [
                    'id' => $clientId
                ],
                'order' => [
                    'amount' => $totalPayment,
                    'invoice_number' => $invoiceNo
                ],
                'ovo_info' => [
                    'ovo_id' => $phoneNumber,
                ],
                'security' => [
                    'checksum' => $checkSum
                ]
            ];

            $jsonBody = json_encode($jsonObject);

            Log::channel('doku_api')->info('/ovo-emoney/v1/payment JSON REQ: '. $jsonBody);


            $currTimeStamp = Carbon::now('UTC');
            $requestTarget = '/ovo-emoney/v1/payment';
//
//            $signature = Doku::generateSignature($jsonBody,
//                $clientId,
//                $randomStr,
//                $currTimeStamp->toIso8601ZuluString(),
//                $requestTarget);

            $client = new Client([
                'verify'    => false,
                'headers' => [
                    'Client-Id'         => $clientId,
                    'Request-Id'        => $randomStr,
                    'Request-Timestamp' => $currTimeStamp->toIso8601ZuluString(),
//                    'Signature'         => $signature,
                    'Request-Target'    => $requestTarget
                ]
            ]);

            $request = $client->request('POST', $url, [
                'json'  => $jsonObject
            ]);

            Log::channel('doku_api')->info('/ovo-emoney/v1/payment JSON RESP: '. $request->getBody());

            if($request->getStatusCode() == 200){
                return [
                    'error' => 'none',
                    'response' => json_decode($request->getBody()),
                ];
            }
            else{
                Log::channel('doku_api')->error('/ovo-emoney/v1/payment STATUS CODE: '. $request->getStatusCode());
                Log::channel('doku_api')->error('/ovo-emoney/v1/payment JSON RESP: '. $request->getBody());

                return [
                    'error' => 'other_status_code',
                    'response' => json_decode($request->getBody()),
                ];
            }
        }
        catch (\Exception $ex){
            Log::error('libs/Doku - jokulPushOvoPayment error EX: '. $ex);
            Log::channel('doku_api')->error('/ovo-emoney/v1/payment EXCEPTION: '. $ex);

            return [
                'error' => 'exception',
                'response' => $ex->getMessage(),
            ];
        }
    }

    /**
     * Function to generate Signature.
     *
     * @param $jsonBody
     * @param $clientId
     * @param $requestId
     * @param $requestTimeStamp
     * @param $requestTarget
     * @return string
     */
    public static function generateSignature($jsonBody, $requestId, $requestTimeStamp, $requestTarget): string
    {
        try{
            $mode = DB::table('configurations')
                ->where('key', '=', 'doku_mode')
                ->value('value');

            if($mode === 'sandbox'){
                $clientSecret = DB::table('configurations')
                    ->where('key', '=', 'doku_sandbox_client_secret')
                    ->value('value');

                $clientId = DB::table('configurations')
                    ->where('key', '=', 'doku_sandbox_client_id')
                    ->value('value');
            }
            else{
                $clientSecret = DB::table('configurations')
                    ->where('key', '=', 'doku_production_client_secret')
                    ->value('value');

                $clientId = DB::table('configurations')
                    ->where('key', '=', 'doku_production_client_id')
                    ->value('value');
            }

            //Generate Digest
            //Digest = SHA256 base64 hash from JsonBody
            $digest = base64_encode(hash('sha256', $jsonBody, true));

            Log::channel('doku_api')->info('clientId: '. $clientId.
                ' | requestId: '. $requestId.
                ' | timestamp: '. $requestTimeStamp.
                ' | target: '. $requestTarget.
                ' | digest: '. $digest);

            //Generate Signature
            $tmpStr = "Client-Id:" . $clientId . "\n" .
                        "Request-Id:" . $requestId . "\n" .
                        "Request-Timestamp:" . $requestTimeStamp . "\n" .
                        "Request-Target:" . $requestTarget . "\n" .
                        "Digest:" . $digest;

            return 'HMACSHA256=' . base64_encode(hash_hmac('sha256', $tmpStr, $clientSecret, true));
        }
        catch (\Exception $ex){
            Log::error('libs/Doku - generateSignature error EX: '. $ex);
            return $ex;
        }
    }

    /**
     * Doku function to release transaction
     *
     * @return mixed|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function releaseSettlement($totalPayment,
                                             $invoiceNo,
                                             $randomStr,
                                             $originalReqId)
    {
        try{
            $mode = DB::table('configurations')
                ->where('key', '=', 'doku_mode')
                ->value('value');

            if($mode === 'sandbox'){
                $host = DB::table('configurations')
                    ->where('key', '=', 'doku_sandbox_host')
                    ->value('value');

                $clientId = DB::table('configurations')
                    ->where('key', '=', 'doku_sandbox_client_id')
                    ->value('value');
            }
            else{
                $host = DB::table('configurations')
                    ->where('key', '=', 'doku_production_host')
                    ->value('value');

                $clientId = DB::table('configurations')
                    ->where('key', '=', 'doku_production_client_id')
                    ->value('value');
            }

            $url = $host. '/finance/v1/release';

            $jsonObject = [
                'order'     => [
                    'invoice_number' => $invoiceNo,
                    'amount' => $totalPayment,
                    'currency' => 'IDR'
                ],
                'transaction'   => [
                    'original_request_id' => $originalReqId,
                ],
            ];

            $jsonBody = json_encode($jsonObject);

            Log::channel('doku_api')->info('/finance/v1/release JSON REQ: '. $jsonBody);

            $currTimeStamp = Carbon::now('UTC');
            $requestTarget = '/finance/v1/release';

            $signature = Doku::generateSignature($jsonBody,
                $randomStr,
                $currTimeStamp->toIso8601ZuluString(),
                $requestTarget);

            $client = new Client([
                'verify' => false,
                'headers' => [
                    'Client-Id'         => $clientId,
                    'Request-Id'        => $randomStr,
                    'Request-Timestamp' => $currTimeStamp->toIso8601ZuluString(),
                    'Signature'         => $signature,
                    'Request-Target'    => $requestTarget
                ]
            ]);

            $request = $client->request('POST', $url, [
                'json'  => $jsonObject
            ]);

            Log::channel('doku_api')->info('/finance/v1/release JSON RESP: '. $request->getBody());

            if($request->getStatusCode() == 200){
                return [
                    'error' => 'none',
                    'response' => json_decode($request->getBody()),
                ];
            }
            else{
                Log::channel('doku_api')->error('/finance/v1/release STATUS CODE: '. $request->getStatusCode());
                Log::channel('doku_api')->error('/finance/v1/release JSON RESP: '. $request->getBody());

                return [
                    'error' => 'other_status_code',
                    'response' => json_decode($request->getBody()),
                ];
            }
        }
        catch (\Exception $ex){
            Log::error('libs/Doku - releaseSettlement error EX: '. $ex);
            Log::channel('doku_api')->error('/finance/v1/release EXCEPTION: '. $ex);

            return [
                'error' => 'exception',
                'response' => $ex->getMessage(),
            ];
        }
    }
}
