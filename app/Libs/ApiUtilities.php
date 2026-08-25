<?php
/**
 * Created by PhpStorm.
 * User: yanse
 * Date: 14-Sep-17
 * Time: 2:38 PM
 */

namespace App\Libs;

use App\Models\AutoNumber;
use App\Models\UserVoucher;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class ApiUtilities
{
    /**
     * Function to generate random string with lowercase characters
     * @param $length
     * @return false|string
     */
    public static function generateRandomShortString($length){
        return substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyz'),1,$length);
    }

    public static function generateRandomShortNumeric($length){
        return substr(str_shuffle('0123456789'),1,$length);
    }

    public static function GenerateShortNumber($prepend, $nextNumber)
    {
        $modulus = "";
        $mod = strlen($nextNumber);
        switch ($mod){
            case 1:
                $modulus = "00000";
                break;
            case 2:
                $modulus = "0000";
                break;
            case 3:
                $modulus = "000";
                break;
            case 4:
                $modulus = "00";
                break;
            case 5:
                $modulus = "0";
                break;
        }

        return $prepend. $modulus. $nextNumber;
    }

    public static function GeneratePeriodNumber($pattern, $nextNumber)
    {
        // Remove room code and year
        $tmp = substr($pattern, 5);
        $modulus = "";
        $mod = strlen($nextNumber);
        switch ($mod){
            case 1:
                $modulus = "0000";
                break;
            case 2:
                $modulus = "000";
                break;
            case 3:
                $modulus = "00";
                break;
            case 4:
                $modulus = "0";
                break;
        }

        return $tmp. $modulus. $nextNumber;
    }

    /**
     * Get next document auto number
     * @param $pattern
     * @return \Illuminate\Support\Collection
     */
    public static function GetNextAutoNumber($pattern): \Illuminate\Support\Collection
    {
        try{
            $autoNumber = DB::table('auto_numbers')
                ->where('code', $pattern)
                ->first();

            $nextNo = 1;
            if(empty($autoNumber)){
                $newAutoNumber = AutoNumber::create([
                    'code' => $pattern,
                    'next_no' => 1
                ]);

                $rowId = $newAutoNumber->id;
            }
            else{
                $rowId = $autoNumber->id;
                $nextNo = $autoNumber->next_no;
            }

            return collect([
                'id' => $rowId,
                'next_no' => $nextNo
            ]);
        }
        catch (\Exception $ex){
            Log::error('libs/Utilities GetNextAutoNumber error ex: '. $ex);
            return collect([
                'id' => -1,
                'next_no' => -1
            ]);
        }
    }

    /**
     * create slug from provided string
     * @param $string
     * @return string|void
     */
    public static function CreateSlug($string){
        try{
            $string = strtolower($string);
            $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
            $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.

            return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
        }catch(\Exception $ex){
//            dd($ex);
            error_log($ex);
            return "";
        }
    }

    public static function removeSpecialCharactes($string){
        try{
            $newString = trim($string);
            $newString = addslashes($newString);
            $newString = strip_tags($newString);
            $newString = preg_replace('/[^A-Za-z0-9!?& \-]/', '', $newString);

            return $newString; // Replaces multiple hyphens with single one.
        }catch(\Exception $ex){
//            dd($ex);
            error_log($ex);
        }
    }

    /**
     * Update incremental number of document auto number
     * @param $id
     * @return false|void
     */
    public static function UpdateAutoNumber($id){
        try{
            DB::table('auto_numbers')
                ->where('id', '=', $id)
                ->increment('next_no');
        }
        catch (\Exception $ex){
            Log::error('libs/Utilities UpdateAutoNumber error ex: '. $ex);
            return false;
        }
    }

    public static function arrayIsUnique($array){
        return array_unique($array) == $array;
    }

    public static function toFloat($raw){
        $valueStr1 = str_replace('.','', $raw);
        $valueStr2 = str_replace(',', '.', $valueStr1);

        return (double) $valueStr2;
    }

    public static function toInt($raw){
        $valueStr = str_replace('.','', $raw);
        return (int) $valueStr;
    }

    /**
     * Function to recursively generating unique auto number
     * @param $tableName
     * @param $rowName
     * @param $rowId
     * @param $uniqueAuto
     * @param $extraPrepend
     * @param $reference
     * @param $logChannel
     * @return string
     */
    public static function generateUniqueAutonumber($tableName, $rowName, $rowId, $uniqueAuto, $extraPrepend, $reference, $logChannel){
        try {
            $resultCollect = Utilities::GetNextAutoNumber($uniqueAuto);
            if($resultCollect['id'] === -1){
                return 'EXCEPTION';
            }

            $nextNo = $resultCollect['next_no'];
            $prepend = $uniqueAuto. $extraPrepend;
            $autonumber = Utilities::GenerateShortNumber($prepend, $nextNo);
            Utilities::UpdateAutoNumber($resultCollect['id']);

            DB::table($tableName)
                ->where('id', '=', $rowId)
                ->update([
                    $rowName => $autonumber,
                ]);

            Log::channel($logChannel)->info('GENERATE UNIQUE AUTONUMBER '.
                ' table_name: '. $tableName.
                ' row_id: '. $rowId.
                ' prepend: '. $prepend.
                ' reference: '. $reference);

            return $autonumber;
        }
        catch (QueryException $ex){
            $errorCode = $ex->errorInfo[1];
            if($errorCode == 1062){
                Log::channel($logChannel)->error('GENERATE UNIQUE AUTONUMBER FAILED '.
                    ' table_name: '. $tableName.
                    ' row_id: '. $rowId.
                    ' unique_auto: '. $uniqueAuto.
                    ' extra_prepend: '. $extraPrepend.
                    ' reference: '. $reference);

                return self::generateUniqueAutonumber($tableName, $rowName, $rowId, $uniqueAuto, $extraPrepend, $reference, $logChannel);
            }
            else{
                Log::channel($logChannel)->error('GENERATE UNIQUE AUTONUMBER QUERY EXCEPTION '.
                    ' table_name: '. $tableName.
                    ' row_id: '. $rowId.
                    ' unique_auto: '. $uniqueAuto.
                    ' extra_prepend: '. $extraPrepend.
                    ' reference: '. $reference);

                return 'EXCEPTION';
            }
        }
        catch (\Exception $ex){
            Log::error('libs/Utilities generateUniqueAutonumber error ex: '. $ex);
            return 'EXCEPTION';
        }
    }

    /**
     * Function to check stock availability
     * @param $productId
     * @param $qty
     * @return bool
     */
    public function checkReadyStock($productId, $qty){
        try {
            $inv = DB::table('inventories')
                ->where('product_id', $productId)
                ->first();

            if(empty($inv)){
                return false;
            }

            if($inv->qty - $qty < 0){
                return false;
            }

            return true;
        }
        catch (\Exception $ex){
            Log::error('libs/Utilities checkReadyStock error ex: '. $ex);
            return false;
        }
    }

    /**
     * Helper function to filter order time
     * @param $filterOrderTimeId
     * @return \Illuminate\Support\Collection
     */
    public static function utilityGetFilterOrderTime($filterOrderTimeId){
        try {
            if($filterOrderTimeId === 1){
                $start = Carbon::today('Asia/Jakarta')->subSeconds(1);
                $end = Carbon::today('Asia/Jakarta')->addDays(1)->addSeconds(1);
            }
            elseif($filterOrderTimeId === 2){
                $start = Carbon::today('Asia/Jakarta')->subDays(5)->subSeconds(1);
                $end = Carbon::today('Asia/Jakarta')->addDays(1)->addSeconds(1);
            }
            elseif($filterOrderTimeId === 3){
                $start = Carbon::today('Asia/Jakarta')->subDays(10)->subSeconds(1);
                $end = Carbon::today('Asia/Jakarta')->addDays(1)->addSeconds(1);
            }
            elseif($filterOrderTimeId === 4){
                $start = Carbon::today('Asia/Jakarta')->subDays(60)->subSeconds(1);
                $end = Carbon::today('Asia/Jakarta')->addDays(1)->addSeconds(1);
            }
            else{
                $start = Carbon::today('Asia/Jakarta')->subDays(5)->subSeconds(1);
                $end = Carbon::today('Asia/Jakarta')->addDays(1)->addSeconds(1);
            }

            return collect([
                'result' => 'SUCCESS',
                'start' => $start->toDateTimeString(),
                'end' => $end->toDateTimeString()
            ]);
        }
        catch (\Exception $ex){
            Log::error('OrderController/utilityGetFilterOrderTime '. $ex);
            return collect([
                'result' => 'EXCEPTION '. $ex->getMessage(),
                'start' => null,
                'end' => null
            ]);
        }
    }

    public static function utilityFilterPickupTime(Carbon $pickupDate){
        try {
//            $tmp = Carbon::create(2022, 6, 11, 8, 30);
//            $todayStartPickup = Carbon::now('Asia/Jakarta')->addHours(3)->startOfHour();
//            $todayTimeLimit = Carbon::today('Asia/Jakarta')->addHours(14);



            if($pickupDate->isToday()){
                $pickupDateNow = Carbon::now('Asia/Jakarta');
                if($pickupDateNow->hour > 17){
                    $dayTimeLimit = $pickupDate->copy()->addDays(2)->startOfDay()->subHours(4);
                    $dayStartPickup = Carbon::now('Asia/Jakarta')->addDay()->startOfDay()->addHours(8);
                }
                else{
                    $dayTimeLimit = $pickupDate->copy()->addDay()->startOfDay()->subHours(4);
                    $dayStartPickup = Carbon::now('Asia/Jakarta')->startOfHour()->addHours(2);
                }
            }
            else{
                $dayTimeLimit = $pickupDate->copy()->addDay()->startOfDay()->subHours(4);
                $dayStartPickup = $pickupDate->startOfDay()->addHours(8);
            }

            if($dayTimeLimit->lessThanOrEqualTo($dayStartPickup)){
                return collect([
                    'result' => null,
                    'error' => 'TIME_LIMIT_REACHED',
                ]);
            }

            $hoursDifference = $dayStartPickup->diffInHours($dayTimeLimit);

            $pickupSlotArr = [];
            for($i = 0; $i < $hoursDifference; $i += 2){
                $tmp1 = $dayStartPickup->copy()->addHours($i)->format('H');
                $tmp2 = $dayStartPickup->copy()->addHours($i + 2)->format('H');
                $pickupSlotArr[] = [
                    'start_hour' => $tmp1,
                    'end_hour' => $tmp2,
                    'range_hour_in_string' => $tmp1. '.00 - '. $tmp2. '.00'
                ];
            }

            return collect([
                'result' => $pickupSlotArr,
                'error' => 'none',
            ]);
        }
        catch (\Exception $ex){
            Log::error('OrderController/utilityFilterPickupTime '. $ex);
            return collect([
                'result' => 'EXCEPTION '. $ex->getMessage(),
                'error' => 'EXCEPTION'
            ]);
        }
    }

    public static function generateUniqueVoucherCode($length = 8)
    {
        do {
            // Generate a random 8-character string
            $code = Str::upper(Str::random($length));
        } while (UserVoucher::where('voucher_code', $code)->exists());

        return $code;
    }

}
