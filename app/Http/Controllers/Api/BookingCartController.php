<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libs\ApiUtilities;
use App\Libs\AutoNumberGenerator;
use App\Models\BookingCart;
use App\Models\BookingDetail;
use App\Models\BookingHeader;
use App\Models\TransactionInvoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BookingCartController extends Controller
{
    private array $result;

    public function __construct(){
        $this->result = [
            'error'         => [],
            'message'       => '',
            'warning'       => '',
            'request_id'    => ApiUtilities::generateRandomShortString(20),
            'response'      => []
        ];
    }

    public function get(Request $request) {
        try {
            $query = BookingCart::where('user_id', $request->user()->id)->get();

            $data = [];
            foreach ($query as $item) {
                $img = '';
                if($item->package_id != null) {
                    $img = $item->package->image_path != null ? asset('storage/images/items/'. $item->package->image_path) : '';
                }
                else if($item->item_id != null) {
                    $img = $item->item->image_path != null ? asset('storage/images/items/'. $item->item->image_path) : '';
                }

                $data[] = [
                    'id' => $item->id,
                    'package_id' => $item->package_id,
                    'item_id' => $item->item_id,
                    'name' => $item->package_id != null ? $item->package->name : $item->item->name,
                    'qty' => $item->qty,
                    'price' => (int)($item->package_id != null ? $item->package->price : $item->item->price),
                    'total' => ($item->package_id != null ? $item->package->price : $item->item->price) * $item->qty,
                    'img' => $img
                ];
            }

            $this->result['response'] = $data;
            $this->result['message'] = 'Sukses ambil data cart!';
            return Response::json($this->result, 200);
        } catch (\Exception $exception) {
            Log::channel('api_exception')->error('Api/BookingCartController - get ERROR EX '. $exception);

            $this->result['error'] = $exception->getMessage();
            $this->result['message'] = 'Internal server error';
            return Response::json($this->result, 500);
        }
    }

    public function addToCart(Request $request)
    {
        DB::beginTransaction();

        try {
            $data = $request->json()->all();
            $userId = $request->user()->id;

            $packageId = $data['package_id'] ?? null;
            $itemId    = $data['item_id'] ?? null;
            $qty       = $data['qty'] ?? 1;

            $check = BookingCart::where('user_id', $userId)
                ->when($packageId, function ($q) use ($packageId) {
                    $q->where('package_id', $packageId);
                })
                ->when($itemId, function ($q) use ($itemId) {
                    $q->where('item_id', $itemId);
                })
                ->first();

            if ($check) {
                $check->increment('qty', 1);
            } else {
                BookingCart::create([
                    'user_id'    => $userId,
                    'package_id' => $packageId,
                    'item_id'    => $itemId,
                    'qty'        => $qty,
                ]);
            }

            DB::commit();

            $this->result['response'] = $data;
            $this->result['message'] = 'Sukses menambah treatment / produk ke keranjang!';
            return Response::json($this->result, 200);

        } catch (\Throwable $exception) {
            DB::rollBack();

            Log::channel('api_exception')->error(
                'Api/BookingCartController - addToCart ERROR EX ' . $exception->getMessage(),
                ['trace' => $exception->getTraceAsString()]
            );

            $this->result['error'] = $exception->getMessage();
            $this->result['message'] = 'Terjadi kesalahan pada sistem!';
            return Response::json($this->result, 500);
        }
    }

    public function updateCart(Request $request)
    {
        DB::beginTransaction();

        try {
            $data = $request->json()->all();
            $userId = $request->user()->id;

            $packageId = $data['package_id'] ?? null;
            $itemId    = $data['item_id'] ?? null;
            $qty       = $data['qty'] ?? null;

            // 🔒 validation wajib ada salah satu
            if (!$packageId && !$itemId) {
                $this->result['message'] = 'Treatment atau produk wajib diisi';
                return Response::json($this->result, 422);
            }

            if (!$qty) {
                $this->result['message'] = 'Qty wajib diisi';
                return Response::json($this->result, 422);
            }

            // 🔍 cari cart berdasarkan user + package/item
            $cart = BookingCart::where('user_id', $userId)
                ->when($packageId, function ($q) use ($packageId) {
                    $q->where('package_id', $packageId);
                })
                ->when($itemId, function ($q) use ($itemId) {
                    $q->where('item_id', $itemId);
                })
                ->first();

            if (!$cart) {
                //create new cart
                BookingCart::create([
                    'user_id'    => $userId,
                    'package_id' => $packageId,
                    'item_id'    => $itemId,
                    'qty'        => $qty,
                ]);
            }
            else{
                // ✏️ update qty
                $cart->update([
                    'qty' => $qty
                ]);
            }


            DB::commit();

            $this->result['message'] = 'Sukses mengubah keranjang!';
            return Response::json($this->result, 200);

        } catch (\Throwable $exception) {
            DB::rollBack();

            Log::channel('api_exception')->error(
                'Api/BookingCartController - updateCart ERROR EX ' . $exception->getMessage(),
                ['trace' => $exception->getTraceAsString()]
            );

            $this->result['error'] = $exception->getMessage();
            $this->result['message'] = 'Terjadi kesalahan pada sistem!';
            return Response::json($this->result, 500);
        }
    }

    public function removeCart(Request $request) {
        DB::beginTransaction();
        try {
            $data = $request->json()->all();
            $cart = BookingCart::find($data['id']);

            if (!$cart) {
                $this->result['message'] = 'Cart tidak ditemukan';
                return Response::json($this->result, 422);
            }
            $cart->delete();

            DB::commit();

            $this->result['message'] = 'Sukses Menghapus treatment / produk dari keranjang!';
            return Response::json($this->result, 200);
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::channel('api_exception')->error('Api/BookingCartController - addToCart ERROR EX '. $exception);

            $this->result['error'] = $exception->getMessage();
            $this->result['message'] = 'Internal server error';
            return Response::json($this->result, 500);
        }
    }
}
