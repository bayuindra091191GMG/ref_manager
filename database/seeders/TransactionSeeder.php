<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Starting TransactionSeeder...');

                           // Ambil data master untuk efisiensi
        $branchId     = 1; // Asumsi branch_id selalu 1
        $adminUsers   = DB::table('admin_users')->pluck('id');
        $rooms        = DB::table('rooms')->where('branch_id', $branchId)->pluck('id');
        $users        = DB::table('users')->get();
        $packages     = DB::table('packages')->where('branch_id', $branchId)->get();
        $items        = DB::table('items')->where('branch_id', $branchId)->get();
        $paymentTypes = DB::table('pos_payment_types')->where('branch_id', $branchId)->get();
        $partners     = DB::table('partners')->pluck('id');

        if ($users->isEmpty() || $packages->isEmpty() || $items->isEmpty() || $rooms->isEmpty() || $partners->isEmpty()) {
            $this->command->error('Master data (users, packages, items, rooms, partners) is empty. Please seed them first.');
            return;
        }

        // **BARU**: Tentukan rentang tanggal untuk tahun 2025
        $startTimestamp = Carbon::create(2025, 1, 1, 0, 0, 0)->timestamp;
        $endTimestamp   = Carbon::create(2025, 12, 31, 23, 59, 59)->timestamp;

        for ($i = 1; $i <= 250; $i++) {
            // **BARU**: Buat tanggal acak hanya di dalam tahun 2025
            $randomTimestamp = mt_rand($startTimestamp, $endTimestamp);
            $createdAt       = Carbon::createFromTimestamp($randomTimestamp);

            $createdBy  = $adminUsers->random();
            $randomUser = $users->random();
            $randomRoom = $rooms->random();

            $headerStatusId = (rand(1, 5) > 1) ? 9 : 8; // 80% kemungkinan status 9 (Completed)

            // 1. Buat Transaction Header
            $headerData = [
                'branch_id'    => $branchId, 'room_id'            => $randomRoom,
                'member_id'    => $randomUser->id, 'member_name'  => $randomUser->name,
                'member_phone' => $randomUser->phone, 'status_id' => $headerStatusId,
                'code'         => 'POS/' . $createdAt->format('Y/m/') . str_pad($i + 32, 5, '0', STR_PAD_LEFT),
                'created_by'   => $createdBy, 'created_at'        => $createdAt,
                'updated_at'   => $createdAt, 'updated_by'        => $createdBy,
                'start_time'   => $createdAt,
                'end_time'     => ($headerStatusId == 9) ? $createdAt->copy()->addHours(rand(1, 3)) : null,
            ];

            $headerId = DB::table('transaction_headers')->insertGetId($headerData);

            // 2. Buat Transaction Details
            $totalSubtotal = 0;
            $detailCount   = rand(1, 4);

            for ($j = 0; $j < $detailCount; $j++) {
                $isPackage = rand(0, 1);
                $qty       = rand(1, 2);

                $detailData = [
                    'header_id'  => $headerId, 'package_id' => null,
                    'item_id'    => null, 'status_id'       => $headerStatusId,
                    'qty'        => $qty, 'price'           => 0, 'subtotal' => 0,
                    'start_time' => null, 'end_time'        => null,
                    'is_fnb'     => 0, 'created_at'         => $createdAt,
                    'updated_at' => $createdAt,
                ];

                $isValidDetail = false;

                if ($isPackage && $packages->isNotEmpty()) {
                    $package  = $packages->random();
                    $price    = $package->price;
                    $subtotal = $price * $qty;

                    $detailData['package_id'] = $package->id;
                    $detailData['price']      = $price;
                    $detailData['subtotal']   = $subtotal;
                    $detailData['start_time'] = $createdAt;
                    $detailData['end_time']   = ($headerStatusId == 9) ? $createdAt->copy()->addMinutes($package->duration ?? 90) : null;

                    $detailId = DB::table('transaction_details')->insertGetId($detailData);

                    DB::table('transaction_detail_partners')->insert([
                        'partner_id'            => $partners->random(),
                        'transaction_detail_id' => $detailId,
                        'is_main'               => 1,
                        'is_extend'             => 0,
                        'is_checkout'           => 1,
                    ]);

                    $isValidDetail = true;

                } else if ($items->isNotEmpty()) {
                    $item     = $items->random();
                    $price    = $item->price;
                    $subtotal = $price * $qty;

                    $detailData['item_id']  = $item->id;
                    $detailData['price']    = $price;
                    $detailData['subtotal'] = $subtotal;
                    $detailData['is_fnb']   = 1;

                    DB::table('transaction_details')->insert($detailData);
                    $isValidDetail = true;
                }

                if ($isValidDetail) {
                    $totalSubtotal += $detailData['subtotal'];
                }
            }

            if ($headerStatusId == 9) {
                $invoiceStatusId = (rand(1, 10) > 9) ? 16 : 9;

                $invoiceData = [
                    'branch_id'    => $branchId, 'header_ids'          => (string) $headerId,
                    'code'         => 'POS/' . $createdAt->format('Y/m/') . str_pad($i + 25, 5, '0', STR_PAD_LEFT),
                    'subtotal'     => $totalSubtotal, 'grandtotal'     => $totalSubtotal,
                    'member_id'    => $randomUser->id, 'member_name'   => $randomUser->name,
                    'member_phone' => $randomUser->phone, 'created_by' => $createdBy,
                    'created_at'   => $createdAt->copy()->addMinutes(5),
                    'updated_at'   => $createdAt->copy()->addMinutes(5),
                    'status_id'    => $invoiceStatusId,
                    'voided_at'    => ($invoiceStatusId == 16) ? $createdAt->copy()->addDay() : null,
                ];

                $invoiceId = DB::table('transaction_invoices')->insertGetId($invoiceData);

                if ($invoiceStatusId == 9 && $paymentTypes->isNotEmpty()) {
                    $paymentType = $paymentTypes->random();
                    $paymentData = [
                        'branch_id'       => $branchId, 'invoice_id'               => $invoiceId,
                        'payment_type_id' => $paymentType->id, 'payment_type_code' => $paymentType->code,
                        'amount'          => $totalSubtotal, 'created_by'          => $createdBy,
                        'created_at'      => $createdAt->copy()->addMinutes(6),
                        'updated_at'      => $createdAt->copy()->addMinutes(6),
                    ];
                    DB::table('transaction_payments')->insert($paymentData);
                }
            }
        }
        $this->command->info('TransactionSeeder finished successfully.');
    }
}
