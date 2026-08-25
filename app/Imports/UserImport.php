<?php

namespace App\Imports;

use App\Models\Customer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class UserImport implements ToCollection, WithHeadingRow, WithStartRow, WithChunkReading, ShouldQueue
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection): void
    {
        try {
            DB::beginTransaction();
            foreach ($collection as $key => $row) {
                $formattedDate = isset($row['birthday']) ? Carbon::createFromFormat('d M Y', $row['birthday'])->format('Y-m-d') : null;

                if(empty($row['email']) || empty($row['name  '])) continue;

                $data = User::where('email', $row['email'])->first();
                if(!is_null($data)) {
                    $data->delete();
                }

                $user = User::create([
                    'membership_id' => $row['card_number'],
                    'program_name' => $row['program_name'],
                    'name' => $row['name'],
                    'password' => Hash::make('123456'),
                    'phone' => $row['phone_number'],
                    'gender' => $row['gender'],
                    'birthday_date' => $formattedDate,
                    'age' => $row['age'],
                    'city' => $row['city'],
                    'address' => $row['address'],
                    'balance_amount' => $row['balance'],
                    'frequency' => $row['frequency'],
                    'average_spending' => $row['average_spending'],
                    'store_activation' => $row['store_activation'],
                    'activation_date' => isset($row['activation_date']) ? Carbon::createFromFormat('d M Y', $row['activation_date'])->format('Y-m-d') : null,
                    'membership_start_at' => isset($row['activation_date']) ? Carbon::createFromFormat('d M Y', $row['activation_date'])->format('Y-m-d') : null,
                    'last_transaction_date' => isset($row['last_transaction']) ? Carbon::createFromFormat('d M Y H:i:s', $row['last_transaction']) : null,
                    'total_transaction_idr' => $row['total_transaction_idr'],

                    'total_spending_idr' => $row['total_spending_idr'],
                    'total_reward_earning_balance_idr' => $row['total_reward_earning_balance_idr'],
                    'total_reward_earning_wallet_idr' => $row['total_reward_earning_wallet_idr'],
                    'total_redeem_balance_idr' => $row['total_redeem_balance_idr'],
                    'total_redeem_wallet_idr' => $row['total_redeem_wallet_idr'],
                    'registration_date' => isset($row['registration_date']) ? Carbon::createFromFormat('d M Y', $row['registration_date'])->format('Y-m-d') : null,
                    'customer_status' => $row['customer_status'],
                    'card_status' => $row['card_status'],
                    'revenue_generated_from_referral_idr' => $row['revenue_generated_from_referral_idr'],
                    'referred_by' => $row['referred_by'],
                    'total_referred_customers' => $row['total_referred'],
                    'rfm_segmentation' => $row['rfm_segmentation'],
                    'membership_status' => $row['level'],
                ]);

                // Gunakan regex untuk mengekstrak key dan value
                preg_match_all('/(\w[\w\s]+): ([^,\]]+)/', $row['wallet_info'], $matches);

                // Ubah menjadi array asosiatif
                $result = array_combine($matches[1], $matches[2]);

                if(!is_null($result)){
                    $user->update([
                        'wallet_name' => $result['wallet name'] ?? null,
                        'wallet_type' => $result['wallet type'] ?? null,
                        'point' => $result['amount'] ?? 0,
                        'point_expired_date' => isset($result['expired date']) ? Carbon::createFromFormat('d M Y', $result['expired date'])->format('Y-m-d') : null,
                        'converted_amount' => isset($result['converted amount']) ? $result['converted amount'] : 0,
                    ]);
                }
            }


            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            dd($th);
            throw $th;
        }
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function startRow(): int
    {
        return 2;
    }
}
