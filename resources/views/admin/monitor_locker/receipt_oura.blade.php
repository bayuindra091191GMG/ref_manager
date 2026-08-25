<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Receipt</title>
    <link rel="stylesheet" id="css-main" href="{{ asset('css/oneui.min.css')}}">
{{--    <style>--}}
{{--        @font-face { font-family: MerchantCopy; src: url('{{ asset('fonts/MerchantCopy-GOXq.ttf') }}'); }--}}
{{--    </style>--}}
</head>
<body>
<div id="main-container" class="justify-content-center" style="width: 155px; font-size: 10px; font-weight: 400;">
    <img src="{{ asset('images/oura_logo_bnw.png') }}" style="width: 50%; height: 50%; margin-left: 39px;"/>
    <div class="mb-1" style="text-align: center; text-justify: inter-word;">{{ $branch->name }}</div>
    <div class="mb-1" style="text-align: center; text-justify: inter-word;">{{ $branch->address }}</div>
    <div class="mb-1" style="text-align: center; text-justify: inter-word;">{{ $branch->phone }}</div>
    <div>============================</div>
    <table>
        <tr>
            <td>No Nota</td>
            <td>:</td>
            <td>{{ $header->code }}</td>
        </tr>
        <tr>
            <td>Waktu</td>
            <td>:</td>
            <td>{{ $header->created_at->toDateTimeString() }}</td>
        </tr>
        <tr>
            <td>Kasir</td>
            <td>:</td>
            <td>{{ $cashierName }}</td>
        </tr>
        @if($header->foc_type == 'FOC' || $header->foc_type == 'FOC_ITEM' || $header->foc_type == 'FOC_PACKAGE')
            <tr>
                <td>FOC</td>
                <td>:</td>
                <td>{{ $header->foc_number }}</td>
            </tr>
        @endif
        @if($room != '')
            <tr>
                <td>Room</td>
                <td>:</td>
                <td>{{ $room }}</td>
            </tr>
        @endif
    </table>
    <div>--------------------------------------------------------</div>
    <table>
        <tr>
            <td width="13%">Qty</td>
            <td>Description</td>
            <td class="text-end">Price</td>
        </tr>
    </table>
    <div>--------------------------------------------------------</div>
    <table>
        @foreach($details as $trx)
            <tr>
                <td width="13%" class="text-center">{{ $trx['qty'] }}</td>
                <td>
                    @if($trx['package_id'] != null)
                        <span>{{ $trx['package_name'] }}</span>
                        <br/>
                        <span>{{ $trx['partner_name'] }}</span>
                    @elseif ($trx['item_id'] != null)
                        <span>{{ $trx['item_name'] }}</span>
                    @elseif ($trx['is_facility_only'])
                        <span>Hanya Fasilitas</span>
                    @elseif ($trx['membership_id'] != 0)
                        <span>Membership</span>
                    @endif
                </td>
                <td class="text-end">
                    @if ($trx['item_id'] != null)
                        {{ number_format(($trx['price'] * $trx['qty']), 0, ',', '.') }}
                    @else
                        {{ number_format(($trx['subtotal']), 0, ',', '.') }}
                    @endif
                </td>
            </tr>

            @if($trx['package_name'] != null && $branch->id == 5)
                <tr>
                    <td></td>
                    <td>{{ $trx['start_time'] }} s/d {{ $trx['end_time'] }}</td>
                    <td></td>
                </tr>
            @endif

        @endforeach
        @if($homePaymentFee > 0)
            <tr>
                <td class="text-center">1</td>
                <td>HOME SERVICE FEE</td>
                <td class="text-end">{{ number_format($homePaymentFee, 0, ',', '.') }}</td>
            </tr>
        @endif
    </table>
    <div>--------------------------------------------------------</div>
    <table>
        @if($pointUsed > 0)
            <tr>
                <td colspan="2" class="text-start">PENGGUNAAN POIN</td>
                <td class="text-end">-{{ number_format($pointUsed, 0, ',', '.') }}</td>
            </tr>
        @endif
        @if($header->discount - $header->google_review_amount > 0)
            <tr>
                <td colspan="2" class="text-start">DISCOUNT</td>
                @if($header->google_review_amount > 0)
                    <td class="text-end">-{{ number_format($header->discount - $header->google_review_amount, 0, ',', '.') }}</td>
                @else
                    <td class="text-end">-{{ number_format($header->discount, 0, ',', '.') }}</td>
                @endif
            </tr>
        @endif
        @if($header->google_review_amount > 0)
            <tr>
                <td colspan="2" class="text-start">DISCOUNT GOOGLE REVIEW</td>
                <td class="text-end">-{{ number_format($header->google_review_amount, 0, ',', '.') }}</td>
            </tr>
        @endif
        <tr>
            <td colspan="2" class="text-start">TOTAL</td>
            <td class="text-end">{{ number_format($header->grand_total, 0, ',', '.') }}</td>
        </tr>
    </table>
    <table>
        @foreach($payments as $payment)
            <tr>
                <td>{{ $payment->payment_type_code }}</td>
                <td class="text-end">{{ number_format($payment->amount, 0, ',', '.') }}</td>
            </tr>
        @endforeach
        @if($header->return_value > 0)
            <tr>
                <td>KEMBALIAN</td>
                <td class="text-end">{{ number_format($header->return_value, 0, ',', '.') }}</td>
            </tr>
        @endif
    </table>
    <div>============================</div>
    @if($coupon != 'none')
    <div class="mt-1" style="text-align: center; text-justify: inter-word;">
        Nikmati Kunjungan Anda berikutnya dengan <br/>
        Menggunakan voucher dari kami <br/>
        Voucher: <b>{{ $coupon }}</b>
    </div>
    <div>============================</div>
    @endif
    <div class="mt-1" style="text-align: center; text-justify: inter-word;">
        Terima Kasih atas kunjungan Anda
    </div>
    <div style="text-align: center; text-justify: inter-word;">
        Harga sudah termasuk Pajak
    </div>
    <div style="text-align: center; text-justify: inter-word;">
        {{ $branch->extra_text }}
    </div>
</div>
<script>
    window.onload = function () {
        window.print();
    }
</script>
</body>
</html>
