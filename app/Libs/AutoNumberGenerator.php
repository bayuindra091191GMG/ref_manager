<?php

namespace App\Libs;

use App\Models\Autonumber;
use App\Models\BatchAutonumber;
use App\Models\Coupon;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoNumberGenerator
{
    public static function generateAutonumberMasterData($code)
    {
        try{
            $autonumberDB = Autonumber::where('doc_code', $code)->first();
            $number = str_pad($autonumberDB->next_no, 4,'0', STR_PAD_LEFT);

            $result = strtoupper($code) .$number;

            $autonumberDB->next_no++;
            $autonumberDB->save();

            return $result;
        }
        catch (\Exception $ex){
            Log::error("AutoNumberGenerator Lib - generateAutonumber error: ". $ex);
            return -1;
        }
    }
    public static function generateAutonumberTrxV1($code)
    {
        try{
            $autonumberDB = Autonumber::where('doc_code', $code)->first();
            $number = str_pad($autonumberDB->next_no, 5,'0', STR_PAD_LEFT);
            $datenow = Carbon::now();

            $result = strtoupper($code) . '/'.$datenow->format('Y').'/'.$datenow->format('m').'/' .$number;

            $autonumberDB->next_no++;
            $autonumberDB->save();
            return $result;
        }
        catch (\Exception $ex){
            Log::error("AutoNumberGenerator Lib - generateAutonumber error: ". $ex);
            return -1;
        }
    }
    public static function generateAutonumberTrx($code)
    {
        try{
            $autonumberDB = Autonumber::where('doc_code', $code)->first();
            $datenow = Carbon::now();

            if(intval($autonumberDB->current_month) != $datenow->month){
                $autonumberDB->current_month = $datenow->month;
                $autonumberDB->next_no = 1;
                $autonumberDB->save();
            }

            if(intval($autonumberDB->current_year) != $datenow->year){
                $autonumberDB->current_year = $datenow->year;
                $autonumberDB->next_no = 1;
                $autonumberDB->save();
            }

            $number = str_pad($autonumberDB->next_no, 5,'0', STR_PAD_LEFT);

            $result = strtoupper($code) . '/'.$datenow->format('Y').'/'.$datenow->format('m').'/' .$number;

            $autonumberDB->next_no++;
            $autonumberDB->save();

            return $result;
        }
        catch (\Exception $ex){
            Log::error("AutoNumberGenerator Lib - generateAutonumber error: ". $ex);
            return -1;
        }
    }

    public static function generateCustomerNumber($code)
    {
        try{
            $autonumberDB = Autonumber::where('doc_code', $code)->first();
            $number = str_pad($autonumberDB->next_no, 4,'0', STR_PAD_LEFT);
            $datenow = Carbon::now();

            $result = strtoupper($code) . '/' .$number;

            $autonumberDB->next_no++;
            $autonumberDB->save();
            return $result;
        }
        catch (\Exception $ex){
            Log::error("AutoNumberGenerator Lib - generateAutonumber error: ". $ex);
            return -1;
        }
    }

    public static function generateBatchAutoNumber($number)
    {
        try{
            $autonumber = BatchAutonumber::where('number', $number)->first();
            if(empty($autonumber)){
                $newAutonumber = new BatchAutonumber();
                $newAutonumber->number = $number;
                $newAutonumber->next_no = 1;
                $newAutonumber->save();

                $autoId = $newAutonumber->id;
                $nextNo = 1;
            }
            else{
                $autoId = $autonumber->id;
                $nextNo = $autonumber->next_no;
            }

            $tmp = str_pad($nextNo, 3,'0', STR_PAD_LEFT);

            $result = strtoupper($number) . '/' .$tmp;

            // Increment next number
            DB::table('batch_autonumbers')
                ->where('id', '=', $autoId)
                ->increment('next_no', 1, [
                    'updated_at' => Carbon::now('Asia/Jakarta')->toDateTimeString()
                ]);

            return $result;
        }
        catch (\Exception $ex){
            Log::error("AutoNumberGenerator Lib - generateAutonumber error: ". $ex);
            return -1;
        }
    }

    public static function generateBookAutoNumber($code)
    {
        try{
            $autonumber = Autonumber::where('doc_code', $code)->first();
            if(empty($autonumber)){
                $newAutoNumber = new Autonumber();
                $newAutoNumber->doc_code = $code;
                $newAutoNumber->next_no = 1;
                $newAutoNumber->save();

                $nextNo = 1;
                $id = $newAutoNumber->id;
            }
            else{
                $nextNo = $autonumber->next_no;
                $id = $autonumber->id;
            }

            $number = str_pad($nextNo, 4,'0', STR_PAD_LEFT);
            $result = strtoupper($code) . '/' .$number;

            // Increment autonumber
            DB::table('autonumbers')
                ->where('id', '=', $id)
                ->increment('next_no');

            return $result;
        }
        catch (\Exception $ex){
            Log::error("AutoNumberGenerator Lib - generateAutonumber error: ". $ex);
            return -1;
        }
    }

    public static function nextAutonumber($code)
    {
        try{
            $autonumberDB = Autonumber::where('doc_code', $code)->first();
            $autonumberDB->next_no += 1;
            $autonumberDB->save();

            return 1;
        }
        catch (\Exception $ex){
            Log::error("AutoNumberGenerator Lib - generateAutonumber error: ". $ex);
            return -1;
        }
    }

    /**
     * CouponId = Coupon Id
     * Code = Voucher Code
     * Branch Code = Branch Voucher Code
    */
    public static function generateCouponCode($couponId, $code, $branchCode)
    {
        try{
            $autonumberDB = Coupon::where('id', $couponId)->first();
            if($autonumberDB == null){
                return 'Coupon not found!';
            }

            $number = str_pad($autonumberDB->next_no, 5,'0', STR_PAD_LEFT);
            $result = strtoupper($branchCode). strtoupper($code) .$number;

            $autonumberDB->next_no++;
            $autonumberDB->save();
            return $result;
        }
        catch (\Exception $ex){
            Log::error("AutoNumberGenerator Lib - generateCouponCode error: ". $ex);
            return -1;
        }
    }

    public static function generateVoucherCode($user){
        $autonumberDB = Autonumber::where('doc_code', 'CPN')->first();
        $datenow = Carbon::now();

        if(intval($autonumberDB->current_month) != $datenow->month){
            $autonumberDB->current_month = $datenow->month;
            $autonumberDB->next_no = 1;
            $autonumberDB->save();
        }

        if(intval($autonumberDB->current_year) != $datenow->year){
            $autonumberDB->current_year = $datenow->year;
            $autonumberDB->next_no = 1;
            $autonumberDB->save();
        }

        $number = str_pad($autonumberDB->next_no, 3,'0', STR_PAD_LEFT);

        $autonumberDB->next_no++;
        $autonumberDB->save();

        return strtoupper(substr($user->name, 0, 3) . str_pad($user->id, 2,'0', STR_PAD_LEFT) . $number);
    }
}
