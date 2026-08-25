<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendBookingCreatedNotification;
use App\Libs\ApiUtilities;
use App\Libs\AutoNumberGenerator;
use App\Models\BookingDetail;
use App\Models\BookingHeader;
use App\Models\Item;
use App\Models\Package;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BookingPointController extends Controller
{
    private array $result;

    public function __construct()
    {
        $this->result = [
            'error' => [],
            'message' => '',
            'warning' => '',
            'request_id' => ApiUtilities::generateRandomShortString(20),
            'response' => [],
        ];
    }

    public function submit(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'package_ids' => 'required|array|min:1',
            'date' => 'required',
            'time' => 'required',
            'items' => 'nullable|array',
            'items.*.item_id' => 'required_with:items|integer|exists:items,id',
            'items.*.qty' => 'required_with:items|integer|min:1',
        ], [
            'package_ids.required' => 'Tidak Ada Treatment Yang dipilih',
            'date.required' => 'Tanggal Booking Belum dipilih',
            'time.required' => 'Waktu Booking Belum dipilih',
        ]);

        if ($validator->fails()) {
            $this->result['response'] = $validator->errors()->all();
            $this->result['message'] = 'Terjadi kesalahan validasi!';

            return Response::json($this->result, 400);
        }

        $errors = [];
        $dateCarbon = null;

        try {
            $today = Carbon::today('Asia/Jakarta');
            $dateCarbon = Carbon::createFromFormat('d-m-Y', $data['date']);

            if ($dateCarbon->lessThanOrEqualTo($today)) {
                $errors[] = 'Hanya bisa pilih jadwal booking H+1!';
            }
        } catch (\Exception $ex) {
            $errors[] = 'Terjadi kesalahan format tanggal!';
        }

        if (count($errors) > 0) {
            $this->result['error'] = $errors;
            $this->result['message'] = 'Terjadi kesalahan validasi!';

            return Response::json($this->result, 400);
        }

        $packageIds = $data['package_ids'] ?? [];
        $itemPayloads = $data['items'] ?? [];
        $genderTerapis = $data['gender_terapis'] ?? [];
        $customerNames = $data['customer_name'] ?? [];

        $branch = DB::table('branches')
            ->where('id', '=', 1)
            ->first();

        $packages = Package::query()
            ->whereIn('id', $packageIds)
            ->get()
            ->keyBy('id');

        $items = Item::query()
            ->whereIn('id', collect($itemPayloads)->pluck('item_id')->filter()->all())
            ->get()
            ->keyBy('id');

        $pointUsed = 0;
        $grandTotal = 0;

        foreach ($packageIds as $packageId) {
            $package = $packages->get($packageId);

            if (! $package) {
                $errors[] = 'Treatment tidak ditemukan';

                continue;
            }

            $pointUsed += (int) ($package->second_price ?? 0);
            $grandTotal += (int) ($package->price ?? 0);
        }

        foreach ($itemPayloads as $itemPayload) {
            $item = $items->get($itemPayload['item_id'] ?? null);
            $qty = (int) ($itemPayload['qty'] ?? 0);

            if (! $item) {
                $errors[] = 'Item tidak ditemukan';

                continue;
            }

            $pointUsed += (int) ($item->second_price ?? 0) * $qty;
            $grandTotal += (int) ($item->price ?? 0) * $qty;
        }

        if (count($errors) > 0) {
            $this->result['error'] = $errors;
            $this->result['message'] = 'Terjadi kesalahan validasi!';

            return Response::json($this->result, 400);
        }

        DB::beginTransaction();

        try {
            $today = Carbon::today('Asia/Jakarta');
            $autonumber = AutoNumberGenerator::generateBookAutoNumber('BOOK/'.$today->month);
            $now = Carbon::now('Asia/Jakarta');
            $user = DB::table('users')
                ->where('id', '=', $request->user()->id)
                ->lockForUpdate()
                ->first();

            if (! $user) {
                throw new \RuntimeException('User tidak ditemukan');
            }

            if ((int) $user->point < $pointUsed) {
                DB::rollBack();
                $this->result['error'] = ['Point tidak mencukupi'];
                $this->result['message'] = 'Terjadi kesalahan validasi!';

                return Response::json($this->result, 400);
            }

            $newBooking = new BookingHeader;
            $newBooking->type = 'booking_point';
            $newBooking->code = $autonumber;
            $newBooking->branch_id = 1;
            $newBooking->branch_name = $branch->name ?? '';
            $newBooking->branch_code = $branch->code ?? '';
            $newBooking->user_id = $user->id;
            $newBooking->user_name = $user->name;
            $newBooking->user_phone = $user->phone;
            $newBooking->user_email = $user->email;
            $newBooking->booking_time = $data['time'];
            $newBooking->booking_date = $dateCarbon;
            $newBooking->booking_notes = is_string($data['notes'] ?? null) ? $data['notes'] : '';
            $newBooking->total_discount = 0;
            $newBooking->grand_total = $grandTotal;
            $newBooking->payment_charge = 0;
            $newBooking->payment_extra_charge = 0;
            $newBooking->total_payment_before = $grandTotal;
            $newBooking->total_payment = (int) ceil($grandTotal);
            $newBooking->udf_03 = Str::random(20);
            $newBooking->status_id = 3;
            $newBooking->payment_due_at = Carbon::now('Asia/Jakarta')->addHour();
            $newBooking->point_used = $pointUsed;
            $newBooking->is_birthday_discount = false;
            $newBooking->birthday_discount_percentage = 0;
            $newBooking->payment_proof = 'simi-pakaipoin.png';
            $newBooking->save();

            $detailIndex = 0;

            foreach ($packageIds as $packageId) {
                $package = $packages->get($packageId);

                $detail = new BookingDetail;
                $detail->header_id = $newBooking->id;
                $detail->package_id = $package->id;
                $detail->package_name = $package->name;
                $detail->package_code = $package->code;
                $detail->qty = 1;
                $detail->price = $package->price;
                $detail->second_price = $package->second_price ?? 0;
                $detail->discount_amount = 0;
                $detail->discount_percentage = 0;
                $detail->subtotal = $package->price;
                $detail->booking_time = $data['time'];
                $detail->therapist_gender = $genderTerapis[$detailIndex] ?? null;
                $detail->customer_name = $customerNames[$detailIndex] ?? null;
                $detail->status_id = 3;
                $detail->save();

                $detailIndex++;
            }

            foreach ($itemPayloads as $itemPayload) {
                $item = $items->get($itemPayload['item_id']);
                $qty = (int) ($itemPayload['qty'] ?? 0);

                $detail = new BookingDetail;
                $detail->header_id = $newBooking->id;
                $detail->item_id = $item->id;
                $detail->item_name = $item->name;
                $detail->item_code = $item->code;
                $detail->qty = $qty;
                $detail->price = $item->price;
                $detail->second_price = $item->second_price ?? 0;
                $detail->discount_amount = 0;
                $detail->discount_percentage = 0;
                $detail->subtotal = (int) $item->price * $qty;
                $detail->booking_time = $data['time'];
                $detail->status_id = 3;
                $detail->save();

                $detailIndex++;
            }

            $remainingPoint = (int) $user->point - $pointUsed;

            DB::table('users')
                ->where('id', '=', $user->id)
                ->update([
                    'point' => $remainingPoint,
                    'updated_at' => $now->toDateTimeString(),
                ]);

            DB::table('user_point_histories')->insert([
                'user_id' => $user->id,
                'user_phone' => $user->phone,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'type' => 'transaction',
                'amount_add' => 0,
                'amount_sub' => $pointUsed,
                'amount_result' => $remainingPoint,
                'reference_1' => $newBooking->code,
                'reference_2' => 'BOOKING_POINT',
                'reference_3' => null,
                'created_at' => $now->toDateTimeString(),
                'updated_at' => $now->toDateTimeString(),
            ]);

            DB::commit();

            SendBookingCreatedNotification::dispatch($newBooking);

            $this->result['message'] = 'Booking sukses!';
            $this->result['response'] = [[
                'booking_id' => $newBooking->id,
                'code' => $newBooking->code,
                'trxCode' => $newBooking->code,
                'point_used' => $pointUsed,
            ]];

            return Response::json($this->result, 200);
        } catch (\Exception $ex) {
            DB::rollBack();

            Log::channel('api_exception')->error('Api/BookingPointController - submit ERROR EX '.$ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Terjadi kesalahan internal sistem';

            return Response::json($this->result, 500);
        }
    }
}
