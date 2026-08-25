<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AR Card Report</title>
    <link rel="stylesheet" id="css-main" href="{{ asset('css/oneui.min.css')}}">
    <style>
        .col1-head {
            min-width: 130px;
        }
        .col2-head {
            min-width: 200px;
        }
        .padding-left-custom{
            padding-left: 8%;
        }

        @media print {
            .padding-left-custom{
                padding-left: 3%;
            }
            .col1-head {
                min-width: 130px;
            }

            .col2-head {
                min-width: 140px;
            }
        }
        table, td, th {
            border: 2px solid black;
        }
        th {
            padding: 5px;
        }
        td{
            padding:5px;
        }
        @page {
            size: landscape;
        }
        p {
            margin-bottom: 0px;
            line-height: 1.8em;
        }
        .p-rl-0{
            padding-right: 0px !important;
            padding-left: 0px !important;
        }
        .border-top-custom{
            border-top: 5px solid black;
        }
        .border-bottom-custom{
            border-bottom: 5px solid black;
        }
        .border-bottom-custom-2{
            border-bottom: 1px solid black;
        }
        .container{
            padding:0px;
        }
        .logo{
            width:180px;
        }

        @media print {
            table, td, th {
                border: 1px solid black !important;
            }

            #non-print {
                display: none;
            }
        }
        body {
            background-color: #fff;
        }
    </style>
</head>
<body>
<section style="font-size:12px;">
    <div class="container-fluid padding-left-custom">
        <div id='non-print' class="row mb-3">
            <div class="col-12 text-end">
                <a class="btn btn-danger mt-lg-3" href="{{route('admin.ar.report.filter', ['type' => $type])}}">
                    Back
                </a>
                &nbsp;
                <button class="btn btn-success rounded-round mt-lg-3" onclick="printDiv('printMe')">
                    Print
                </button>
            </div>
        </div>
        <div class="row">
            <div class="col-2  pb-2">
            </div>
            <div class="col-8 pl-5 font-weight-bold text-center">
                <p class="text-center font-weight-bold" style="font-size: 18px;">AR CARD REPORT</p>
                <p class="text-center font-weight-bold" style="font-size: 12px;">Period: {{$start_date}} To {{$end_date}}</p>
            </div>
            <div class="col-2">
                <p>Print Date<br> {{\Carbon\Carbon::now('Asia/Jakarta')}}</p>
            </div>
        </div>
    </div>
</section>
@php($grandTotalPaid = 0)
@php($grandTotal = 0)
@php($grandTotalBalance = 0)
@foreach($result as $data)
<section class=" pt-4">
    <div class="container-fluid padding-left-custom ">
        <div class="row  ">
            <div class="col-2">
                <p>Customer </p>
            </div>
            <div class="col-10 font-weight-bold">
                <p>: {{$data['customer_name']}}</p>
            </div>
        </div>
    </div>
    <div class="container-fluid padding-left-custom" style="font-size: 10px;">
        <div class="row pt-2">
            <div class="col-12 ">
                <table>
                    <tr class="text-center font-weight-bold p-rl-0">
                        <th class="col0-head">No </th>
                        <th class="col2-head">Transaction Number</th>
                        <th class="col2-head">Transaction Date</th>
                        <th class="col1-head">Transaction Amount</th>
                        <th class="col2-head">Paid No</th>
                        <th class="col2-head">Paid Date</th>
                        <th class="col1-head">Paid Amount</th>
                        <th class="col1-head">Balance</th>
                    </tr>
                    @php($ct=1)
                    @php($tempCode = "")
                    @php($subTotal = 0)
                    @php($subTotalPaid = 0)
                    @php($subTotalBalance = 0)
                    @foreach ($data['transactions'] as $transaction)
                        <tr class="">
                            <td class="col0-head">{{$ct}}</td>
                            @if($tempCode != $transaction['code'])
                                @php($subTotalPaid += $transaction['paid_amount'])
                                @php($subTotalBalance += $transaction['balance'])
                                @php($subTotal += $transaction['grand_total'])
                                @php($tempCode = $transaction['code'])
                                <td class="col2-head">{{$transaction['code']}}</td>
                                <td class="col2-head">{{$transaction['date']}}</td>
                                <td class="col1-head" style="text-align: right">{{number_format($transaction['grand_total'], '0', ',', '.')}}</td>
                            @else
                                @php($subTotalPaid += $transaction['paid_amount'])
                                @php($subTotalBalance += $transaction['balance'])
                                @php($subTotal += $transaction['grand_total'])
                                <td colspan="3"></td>
                            @endif
                            <td class="col2-head">{{$transaction['paid_no']}}</td>
                            <td class="col2-head">{{$transaction['paid_date']}}</td>
                            <td class="col1-head" style="text-align: right">{{number_format($transaction['paid_amount'], '0', ',', '.')}}</td>
                            <td class="col1-head" style="text-align: right">{{number_format($transaction['balance'], '0', ',', '.')}}</td>
                        </tr>

                        @php($grandTotal += $transaction['grand_total'])
                        @php($grandTotalPaid += $transaction['paid_amount'])
                        @php($grandTotalBalance += $transaction['balance'])
                        @php($ct++)
                    @endforeach
                    <tr class="font-weight-bold text-right" style="font-weight: bold;font-size: 12px;">
                            <td colspan="3" style="text-align: right">Subtotal</td>
                            <td style="text-align: right">{{number_format($subTotal, '0', ',', '.')}}</td>
                            <td colspan="2" style="text-align: right"></td>
                            <td style="text-align: right">{{number_format($subTotalPaid, '0', ',', '.')}}</td>
                            <td style="text-align: right">{{number_format($subTotalBalance, '0', ',', '.')}}</td>
                        </tr>
                </table>
            </div>
        </div>
    </div>
</section>
@endforeach
<div class="container-fluid padding-left-custom">
    <div class="row font-weight-bold">
        <div class="col-4"></div>
        <div class="col-4 text-end">
            <p>Jumlah GrandTotal<span class="float-right">:</span> </p>
        </div>
        <div class="col-2 text-right pr-2">
            <p>{{number_format($grandTotal, '0', ',', '.')}}</p>
        </div>
        <div class="col-2"></div>
    </div>
    <div class="row font-weight-bold">
        <div class="col-4"></div>
        <div class="col-4 text-end">
            <p>Jumlah Paid Amount<span class="float-right">:</span> </p>
        </div>
        <div class="col-2 text-right pr-2">
            <p>{{number_format($grandTotalPaid, '0', ',', '.')}}</p>
        </div>
        <div class="col-2"></div>
    </div>
    <div class="row font-weight-bold">
        <div class="col-4"></div>
        <div class="col-4 text-end">
            <p>Jumlah Balance<span class="float-right">:</span> </p>
        </div>
        <div class="col-2 text-right pr-2">
            <p>{{number_format($grandTotalBalance, '0', ',', '.')}}</p>
        </div>
        <div class="col-2"></div>
    </div>
</div>


<script>
    function printDiv(divName) {
        window.print();
    }
</script>
</body>
</html>
