<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Receipt</title>
    <link rel="stylesheet" href="{{ asset('pos/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('pos/css/style.css') }}">
    {{--    <style> --}}
    {{--        @font-face { font-family: MerchantCopy; src: url('{{ asset('fonts/MerchantCopy-GOXq.ttf') }}'); } --}}
    {{--    </style> --}}
    <style>
        body {
            font-family: Arial !important;
            font-size: 12px;
            color: #000000;
        }

        @page {
            size: 2.8in 11in;
            margin-top: 0cm;
            margin-left: -1cm;
            margin-right: 0cm;
        }
        #print-receipt .modal-body{
            color: #000000;
        }

        @media print {

            /* All your print styles go here */
            body {
                padding: 0 !important;
            }

            #print-receipt {
                width: 46mm;
            }
        }

        #print-receipt .modal-body table tbody tr table tr:last-child td {
            font-size: 12px !important;
        }

        /*#print-receipt .modal-body .tax-invoice h6::after, #print-receipt .modal-body .tax-invoice h6::before {*/
        /*    border-top: 5px solid #5B6670;*/
        /*}*/

        /*#print-receipt .modal-body table thead th {*/
        /*    border-top: 3px solid #5B6670;*/
        /*    border-bottom: 3px solid #5B6670;*/
        /*}*/

        /*#print-receipt .modal-body table tbody tr table {*/
        /*    border-top: 3px solid #5B6670;*/
        /*}*/

        /*#print-receipt .modal-body .invoice-bar {*/
        /*    border-top: 3px solid #5B6670;*/
        /*}*/

        #footer-table tr td {
            padding: 5px 0 !important;
        }
    </style>
</head>

<body>
<div id="print-receipt" class="justify-content-center" style="color: #000000;">
    <div class="modal-body p-0">
        <div class="icon-head text-center">
            <a href="javascript:void(0);">
                <img src="{{ asset('images/logo-only.png') }}" style="height: 30px;"
                     alt="Receipt Logo">
            </a>
        </div>
        <div class="text-center info text-center">
            <h6 style="font-family: Arial !important;color: #000000;" class="fw-bold mb-0">{{ $branch->name }}</h6>
            <p class="mb-0 text-center">{{ $branch->address }}</p>
            <p class="text-center">{{ $branch->phone }}</p>
        </div>

        {{--                @if ($reprint)--}}
        {{--                    <hr style="border-width: 2px; border-color: #000000; opacity: 1;">--}}

        {{--                    <div class="text-center"--}}
        {{--                        style="font-size: 16px; font-weight: bold; margin-top: 10px; margin-bottom: 10px;">THIS IS A--}}
        {{--                        COPY</div>--}}
        {{--                @endif--}}

        <div class="tax-invoice">
            <hr style="border-width: 2px; border-color: #000000; opacity: 1;">
            <div class="row">
                <div class="col-6 text-start">
                            <span class="invoice-user-name m-0">
                                {{ $invoice->created_at->timezone('Asia/Jakarta')->format('d M Y') }}
                            </span>
                </div>
                <div class="col-6 text-end">
                            <span class="invoice-user-name m-0">
                                {{ $invoice->created_at->timezone('Asia/Jakarta')->format('H:i') }}
                            </span>
                </div>
            </div>
            <div class="row" style="line-height: 1; margin-bottom: 0.5em; margin-top: 0.25em">
                <div class="col-6 text-start">
                            <span class="invoice-user-name m-0">
                                Receipt Number
                            </span>
                </div>
                <div class="col-6 text-end">
                            <span class="invoice-user-name m-0 text-break">
                                {{ $invoice->code }}
                            </span>
                </div>
            </div>
            <div class="row" style="line-height: 1; margin-bottom: 0.5em;">
                <div class="col-6 text-start">
                            <span class="invoice-user-name m-0">
                                Customer
                            </span>
                </div>
                <div class="col-6 text-end">
                            <span class="invoice-user-name m-0">
                                {{ $invoice->member_name }}
                            </span>
                </div>
            </div>
            <div class="row" style="line-height: 1; margin-bottom: 0.5em;">
                <div class="col-6 text-start">
                            <span class="invoice-user-name m-0">
                                Kasir
                            </span>
                </div>
                <div class="col-6 text-end">
                            <span class="invoice-user-name m-0">
                                {{ $prewed->employee->name }}
                            </span>
                </div>
            </div>
        </div>

        <hr style="border-width: 2px; border-color: #000000; opacity: 1;">

        <table class="table-borderless w-100 table-fit">
            <tbody>
            <tr>
                <td>{{ $prewed->package->name }}</td>
                <td class="text-end">{{ number_format($invoice->subtotal) }}</td>
            </tr>
            @foreach ($prewed->details as $detail)
                <td>{{ $detail->package_id != null ? $detail->package->name : $detail->item->name }}</td>
                <td class="text-end"></td>
            @endforeach
            </tbody>
        </table>

        <hr style="border-width: 2px; border-color: #000000; opacity: 1;">

        <div class="row">
            <div class="col-6 text-start">
                        <span class="invoice-user-name m-0">
                            Sub Total
                        </span>
            </div>
            <div class="col-6 text-end">
                        <span class="invoice-user-name m-0">
                            Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}
                        </span>
            </div>
        </div>
        <div class="row">
            <div class="col-6 text-start">
                        <span class="invoice-user-name m-0">
                            PB2 (10%)
                        </span>
            </div>
            <div class="col-6 text-end">
                        <span class="invoice-user-name m-0">
                            Rp {{ number_format($invoice->pb2, 0, ',', '.') }}
                        </span>
            </div>
        </div>
        <div class="row">
            <div class="col-12 fw-bold">
                @if ($invoice->coupon_id != null)
                    @foreach ($discountUsed as $discount)
                        <p style="font-size: 8px" class="pb-0 m-0">{{ $discount }}</p>
                    @endforeach
                @endif
                @if ($invoice->is_use_voucher_code != 1)
                    <p style="font-size: 8px" class="pb-0 m-0">{{ $membershipLevels }} Member Benefits <br />
                    </p>
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-6 text-start">
                        <span class="invoice-user-name m-0">
                            Grand Total
                        </span>
            </div>
            <div class="col-6 text-end">
                        <span class="invoice-user-name m-0">
                            Rp {{ number_format($invoice->grandtotal, 0, ',', '.') }}
                        </span>
            </div>
        </div>

        <hr style="border-width: 2px; border-color: #000000; opacity: 1;">

        <div class="row">
            <div class="col-6 text-start">
                        <span class="invoice-user-name m-0">
                            {{ $invoice->transaction_payment->pos_payment_type->description }}
                        </span>
            </div>
            <div class="col-6 text-end">
                        <span class="invoice-user-name m-0">
                            Rp {{ number_format($invoice->transaction_payment->amount, 0, ',', '.') }}
                        </span>
            </div>
        </div>
        @if ($invoice->return_value > 0)
            <div class="row">
                <div class="col-6 text-start">
                            <span class="invoice-user-name m-0">
                                Return
                            </span>
                </div>
                <div class="col-6 text-end">
                            <span class="invoice-user-name m-0">
                                Rp {{ number_format($invoice->return_value, 0, ',', '.') }}
                            </span>
                </div>
            </div>
        @endif

        <hr style="border-width: 2px; border-color: #000000; opacity: 1;">

        <div class="invoice-bar">
            <div class="d-flex flex-row align-items-center">
                <img src="{{ asset('pos/web.svg') }}" style="height: 12px;">
                <p style="margin: 0 !important; padding-left: 5px">simi.co.id</p>
            </div>
            <div class="d-flex flex-row align-items-center">
                <img src="{{ asset('pos/instagram.svg') }}" style="height: 12px;">
                <p style="margin: 0 !important; padding-left: 5px">simireflexology</p>
            </div>
            {{--            <p style="margin: 0 !important;">www.oemahetniek.com</p> --}}
            {{--            <p>0812-9177-3255</p> --}}
            {{--            <p>No Refund. Exchange 7 Days With Receipts</p> --}}
        </div>
    </div>
</div>
<script src="{{ asset('pos/js/bootstrap.bundle.min.js') }}"></script>
<script>
    window.onload = function() {
        window.print();
    }
</script>
</body>

</html>
