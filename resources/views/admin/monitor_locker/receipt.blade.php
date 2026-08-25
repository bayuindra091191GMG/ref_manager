<!doctype html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
        <title>Receipt</title>
        <link rel="stylesheet" id="css-main" href="{{ asset('css/oneui.min.css')}}">
    </head>
    <body>
    <div id="main-container" class="justify-content-center" style="width: 305px; font-size: 10px;">
        <div class="mb-1">{{ $branch->description }}</div>
        <div class="mb-1">{{ $trxDate }}</div>
        <div class="mb-1">
            <table>
                <tr>
                    <td>Locker</td>
                    <td>:</td>
                    <td>{{ $header->locker->number }}</td>
                </tr>
                <tr>
                    <td>Time</td>
                    <td>:</td>
                    <td>{{ $time }}</td>
                </tr>
            </table>
        </div>
        <div class="mb-1">PREVIEW BILL</div>
        @if($branch->id == 1)
            <div>
                GRO : {{ $header->employee->number }}
            </div>
        @else
            <div>
                GRO : {{ $header->employee->name }}
            </div>
        @endif
        @if($branch->id != 1)
            <div>
                Kasir : {{ $cashierName }}
            </div>
        @endif
        <div>
            THERAPIST :
            @foreach($partners as $partner)
                <span>
                    {{ $partner['number'] }}
                    @if(count($partners) > 1)
                        <span>/</span>
                    @endif
                </span>
            @endforeach
        </div>
        @if($header->foc_type == 'FOC' || $header->foc_type == 'FOC_ITEM' || $header->foc_type == 'FOC_PACKAGE')
            <div>
                FOC: <span>{{ $header->foc_number }}</span>
            </div>
        @endif
        @if($room != '')
            <div>
                ROOM: {{ $room }}
            </div>
        @endif
        <div>--------------------------------------------</div>
        <table>
            <tr>
                <td width="13%">Qty</td>
                <td>Description</td>
                <td class="text-end">Price</td>
            </tr>
        </table>
        <div>--------------------------------------------</div>
        <table>
            @foreach($details as $trx)
                <tr>
                    <td width="13%" class="text-center">{{ $trx['qty'] }}</td>
                    <td>
                        @if($trx['package_id'] != null)
                            <span>{{ $trx['package_name'] }}</span>
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
                            Rp{{ number_format(($trx['price'] * $trx['qty']), 0, ',', '.') }}
                        @else
                            Rp{{ number_format(($trx['subtotal']), 0, ',', '.') }}
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
                @if($trx['extension_times'] > 0)
                    <tr>
                        <td width="13%" class="text-center">{{ $trx['extension_times'] }}</td>
                        <td>
                            <span>Perpanjangan Waktu</span>
                        </td>
                        <td class="text-end">
                            Rp{{ number_format($trx['extension_amount'], 0, ',', '.') }}
                        </td>
                    </tr>
                @endif
                @if($trx['additional_person_qty'] > 0)
                    <tr>
                        <td width="13%" class="text-center">{{ $trx['additional_person_qty'] }}</td>
                        <td>
                            <span>Tambahan Orang</span>
                        </td>
                        <td class="text-end">
                            Rp{{ number_format($trx['additional_person_amount'], 0, ',', '.') }}
                        </td>
                    </tr>
                @endif
                @if($trx['additional_partner_qty'] > 0)
                    <tr>
                        <td width="13%" class="text-center">{{ $trx['additional_partner_qty'] }}</td>
                        <td>
                            <span>Tambahan Therapis</span>
                        </td>
                        <td class="text-end">
                            Rp{{ number_format($trx['additional_partner_amount'], 0, ',', '.') }}
                        </td>
                    </tr>
                @endif
            @endforeach
        </table>
        <div>--------------------------------------------</div>
        <table>
            @if($header->discount > 0)
                <tr>
                    <td colspan="2" class="text-start">DISCOUNT</td>
                    <td class="text-end">-Rp{{ number_format($header->discount, 0, ',', '.') }}</td>
                </tr>
            @endif
            <tr>
                <td colspan="2" class="text-start">TOTAL</td>
                <td class="text-end">Rp{{ number_format($header->grand_total, 0, ',', '.') }}</td>
            </tr>
        </table>
        <table>
            @foreach($payments as $payment)
                <tr>
                    <td>{{ $payment->payment_type_code }}</td>
                    <td class="text-end">Rp{{ number_format($payment->amount, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </table>
        <div>--------------------------------------------</div>
        <table>
            <tr>
                <td>KEMBALIAN</td>
                <td class="text-end">Rp{{ number_format($header->return_value, 0, ',', '.') }}</td>
            </tr>
        </table>
        <div class="mt-1" style="text-align: center; text-justify: inter-word;">
            Terima Kasih atas kunjungan Anda
        </div>
        <div style="text-align: center; text-justify: inter-word;">
            Harga sudah termasuk Pajak
        </div>
    </div>
    <script>
        window.onload = function () {
            window.print();
        }
    </script>
    </body>
</html>
