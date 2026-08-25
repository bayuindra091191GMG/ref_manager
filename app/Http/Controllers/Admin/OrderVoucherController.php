<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendVoucherCancelledNotification;
use App\Jobs\SendVoucherPaymentApprovedNotification;
use App\Libs\ApiUtilities;
use App\Models\OrderVoucherDetail;
use App\Models\OrderVoucherHeader;
use App\Models\UserVoucher;
use App\Models\UserVoucherCode;
use App\Models\VoucherPackage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class OrderVoucherController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index()
    {
        return view('admin.voucher_order.index');
    }

    public function datatable(Request $request)
    {
        $orders = OrderVoucherHeader::select('order_voucher_headers.*', 'statuses.display as status')
            ->leftJoin('statuses', 'order_voucher_headers.status_id', '=', 'statuses.id')
            ->orderByDesc('order_voucher_headers.created_at');

        return DataTables::of($orders)
            ->addColumn('action', function ($row) {
                return '<a href="'.route('admin.voucher_order.show', ['id' => $row->id]).'" class="btn btn-info me-1"><i class="far fa fa-eye"></i> Show</a>';
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null;
            })
            ->editColumn('payment_due_at', function ($row) {
                return $row->payment_due_at ? Carbon::parse($row->payment_due_at)->toIso8601String() : null;
            })
            ->make();
    }

    public function show($id)
    {
        $order = OrderVoucherHeader::with(['details', 'status'])->find($id);
        if (! $order) {
            abort(404, 'Transaksi voucher tidak ditemukan');
        }

        $details = $order->details->map(function ($detail) {
            $voucher = VoucherPackage::find($detail->voucher_id);

            return [
                'name' => $voucher ? $voucher->name : '-',
                'qty' => $detail->qty,
                'price' => $detail->price,
                'subtotal' => $detail->subtotal,
            ];
        });

        $hasGeneratedVoucher = $this->hasGeneratedVoucher($order);

        return view('admin.voucher_order.show', compact('order', 'details', 'hasGeneratedVoucher'));
    }

    public function checkProof(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'status' => 'required|integer|in:14,20',
            'payment_proof' => 'nullable|string',
        ]);

        $shouldSendPaymentApprovedNotification = false;

        DB::beginTransaction();
        try {
            $order = OrderVoucherHeader::with('details')->lockForUpdate()->find($request->input('id'));
            if (! $order) {
                DB::rollBack();
                Session::flash('error', 'Transaksi voucher tidak ditemukan!');

                return Redirect::back();
            }

            if (
                $request->filled('payment_proof')
                && $request->input('payment_proof') !== $order->payment_proof
            ) {
                DB::rollBack();
                Session::flash('error', 'Bukti pembayaran sudah berubah. Muat ulang halaman lalu review ulang!');

                return Redirect::back();
            }

            if ((int) $request->input('status') === 20) {
                if (empty($order->payment_proof) || ! Storage::disk('proofs')->exists($order->payment_proof)) {
                    DB::rollBack();
                    Session::flash('error', 'Bukti pembayaran belum diupload!');

                    return Redirect::back();
                }

                $this->approveOrder($order);
                $shouldSendPaymentApprovedNotification = true;
                Session::flash('success', 'Berhasil approve bukti pembayaran voucher!');
            } else {
                $this->rejectOrder($order);
                Session::flash('success', 'Berhasil reject bukti pembayaran voucher!');
            }

            DB::commit();

            if ($shouldSendPaymentApprovedNotification) {
                $order->refresh();
                SendVoucherPaymentApprovedNotification::dispatch($order);
            }

            return Redirect::back();
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::channel('voucher')->error('[OrderVoucherController - checkProof] - '.$ex);

            Session::flash('error', 'Terjadi kesalahan pada sistem! '.$ex->getMessage());

            return Redirect::back()->withInput($request->except('_token'));
        }
    }

    public function cancel(Request $request)
    {
        try {
            if (! $request->filled('cancelled_order_voucher_id')) {
                Session::flash('error', 'Terjadi kesalahan input!');

                return Redirect::back();
            }

            $order = OrderVoucherHeader::find($request->input('cancelled_order_voucher_id'));
            if (! $order) {
                Session::flash('error', 'Transaksi voucher tidak ditemukan!');

                return Redirect::back();
            }

            if ((int) $order->status_id === 10) {
                Session::flash('error', 'Transaksi voucher sudah dibatalkan!');

                return Redirect::back();
            }

            $payload = [
                'status_id' => 10,
            ];

            if (Schema::hasColumn('order_voucher_headers', 'canceled_at')) {
                $payload['canceled_at'] = Carbon::now('Asia/Jakarta')->toDateTimeString();
            }

            $order->update($payload);
            $order->refresh();

            SendVoucherCancelledNotification::dispatch($order, 'admin');

            Session::flash('success', 'Pembatalan transaksi voucher sukses!');

            return Redirect::back();
        } catch (\Exception $ex) {
            Log::channel('voucher')->error('[OrderVoucherController - cancel] - '.$ex);
            Session::flash('error', 'Terjadi kesalahan pada sistem!');

            return Redirect::back();
        }
    }

    private function approveOrder(OrderVoucherHeader $order): void
    {
        if ((int) $order->status_id !== 20) {
            $payload = [
                'status_id' => 20,
                'payment_approved_at' => Carbon::now('Asia/Jakarta')->toDateTimeString(),
            ];

            $order->update($payload);
        }

        $this->generateVoucherCodes($order);
    }

    private function rejectOrder(OrderVoucherHeader $order): void
    {
        if (! empty($order->payment_proof) && Storage::disk('proofs')->exists($order->payment_proof)) {
            Storage::disk('proofs')->delete($order->payment_proof);
        }

        $payload = [
            'status_id' => 14,
            'payment_proof' => null,
        ];

        $order->update($payload);
    }

    private function generateVoucherCodes(OrderVoucherHeader $order): void
    {
        if ($this->hasGeneratedVoucher($order)) {
            return;
        }

        $orderDetails = OrderVoucherDetail::where('header_id', $order->id)->get();
        foreach ($orderDetails as $detail) {
            $voucher = VoucherPackage::find($detail->voucher_id);
            if (! $voucher) {
                Log::channel('voucher')->error('OrderVoucherController - voucher not found: '.$detail->voucher_id);
                continue;
            }

            $expiredDays = (int) ($voucher->expired ?? 0);
            $expiredDate = $expiredDays > 0
                ? Carbon::now('Asia/Jakarta')->addDays($expiredDays)
                : null;
            $payload = [
                'order_voucher_header_id' => $order->id,
                'voucher_id' => $voucher->id,
                'voucher_code' => $voucher->code,
                'terms_condition' => $voucher->name,
                'status_id' => 1,
                'type' => 'voucher',
                'user_id' => $order->created_by,
            ];

            if (! Schema::hasColumn('user_vouchers', 'order_voucher_header_id')) {
                unset($payload['order_voucher_header_id']);
            }

            $userVoucher = UserVoucher::create($payload);

            for ($i = 0; $i < $detail->qty; $i++) {
                UserVoucherCode::create([
                    'code' => ApiUtilities::generateUniqueVoucherCode(),
                    'header_id' => $userVoucher->id,
                    'expired_date' => $expiredDate,
                    'is_used' => 0,
                ]);
            }
        }
    }

    private function hasGeneratedVoucher(OrderVoucherHeader $order): bool
    {
        return Schema::hasColumn('user_vouchers', 'order_voucher_header_id')
            && UserVoucher::where('order_voucher_header_id', $order->id)->exists();
    }
}
