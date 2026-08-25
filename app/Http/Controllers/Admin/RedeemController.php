<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserVoucherCode;

class RedeemController extends Controller
{
    public function viewVoucher($code){
        $voucher = UserVoucherCode::where('code', $code)->first();

        return view('admin.voucher.view', compact('voucher'));
    }
}
