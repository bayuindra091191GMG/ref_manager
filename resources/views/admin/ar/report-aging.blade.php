<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AR Aging Report</title>
    <link rel="stylesheet" id="css-main" href="{{ asset('css/oneui.min.css')}}">
    <style>
        .col0-head {
            min-width: 30px;
        }
        .col1-head {
            min-width: 130px;
        }
        .col2-head {
            min-width: 130px;
        }
        .col3-head {
            min-width: 75px;
        }
        .col4-head {
            min-width: 75px;
        }
        .col5-head {
            min-width: 100px;
        }
        .col6-head {
            min-width: 100px;
        }
        .col7-head {
            min-width: 100px;
        }
        .col8-head {
            min-width: 100px;
        }
        .col9-head {
            min-width: 100px;
        }
        .col10-head {
            min-width: 130px;
        }
        .padding-left-custom{
            padding-left: %;
        }

        @media print {
            .padding-left-custom{
                padding-left: 3%;
            }
            .col0-head {
                min-width: 20px;
            }
            .col1-head {
                min-width: 100px;
            }

            .col2-head {
                min-width: 100px;
            }

            .col3-head {
                min-width: 50px;
            }

            .col4-head {
                min-width: 50px;
            }

            .col5-head {
                min-width: 80px;
            }

            .col6-head {
                min-width: 80px;
            }

            .col7-head {
                min-width: 80px;
            }

            .col8-head {
                min-width: 80px;
            }

            .col9-head {
                min-width: 80px;
            }

            .col10-head {
                min-width: 100px;
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
    <div class="container-fluid">
        <div id='non-print' class="row mb-3">
            <div class="col-12 text-end">
                <a class="btn btn-danger mt-lg-3" href="{{route('admin.ap.report.filter', ['type' => $type])}}">
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
                <p class="text-center font-weight-bold" style="font-size: 18px;">AR Aging Report</p>
                <p class="text-center font-weight-bold" style="font-size: 12px;">Period: {{$start_date}} To {{$end_date}}</p>
            </div>
            <div class="col-2">
                <p>Print Date<br> {{\Carbon\Carbon::now('Asia/Jakarta')}}</p>
            </div>
        </div>
    </div>
</section>
@foreach($result as $data)
<section class=" pt-4">
    <div class="container-fluid padding-left-custom">
        <div class="row  ">
            <div class="col-2">
                <p>Customer </p>
            </div>
            <div class="col-10 font-weight-bold">
                <p>: {{$data['customer_name']}}</p>
            </div>
        </div>
    </div>
    <div class="container-fluid padding-left-custom pb-3" style="font-size: 10px;">
        <div class="row pt-2">
            <div class="col-12 ">
                <table>
                    <tr class="text-center font-weight-bold p-rl-0">
                        <th class="col0-head">No </th>
                        <th class="col1-head">Transaction Number</th>
                        <th class="col2-head">Transaction Date</th>
                        <th class="col3-head">Invoiced</th>
                        <th class="col10-head">Grand Total</th>
                        <th class="col10-head">Paid Amount</th>
                        <th class="col10-head">&lt;30</th>
                        <th class="col10-head">30-59</th>
                        <th class="col10-head">60-89</th>
                        <th class="col10-head">90-119</th>
                        <th class="col10-head">&gt;120</th>
                    </tr>
                    @foreach ($data['transactions'] as $transaction)

                        <tr class="">
                            <td class="col0-head">{{$transaction['no']}}</td>
                            <td class="col1-head">{{$transaction['code']}}</td>
                            <td class="col2-head">{{$transaction['date']}}</td>
                            <td class="text-center">{{$transaction['invoiced']}}</td>
                            <td class="col3-head" style="text-align: right">{{$transaction['grand_total']}}</td>
                            <td class="col3-head" style="text-align: right">{{$transaction['paid_amount']}}</td>
                            <td class="col3-head" style="text-align: right">{{$transaction['t30']}}</td>
                            <td class="col3-head" style="text-align: right">{{$transaction['t60']}}</td>
                            <td class="col3-head" style="text-align: right">{{$transaction['t90']}}</td>
                            <td class="col3-head" style="text-align: right">{{$transaction['t120']}}</td>
                            <td class="col3-head" style="text-align: right">{{$transaction['t120_more']}}</td>
                        </tr>
                    @endforeach
                    <tr class="font-weight-bold text-right" style="font-weight: bold;font-size: 12px;">
                        <td colspan="4" style="text-align: right">Subtotal</td>
                        <td style="text-align: right">{{number_format($data['subtotal_grand_total'], '0', ',', '.')}}</td>
                        <td style="text-align: right">{{number_format($data['subtotal_paid_amount'], '0', ',', '.')}}</td>
                    </tr>
                    <tr class="font-weight-bold text-right" style="font-weight: bold;font-size: 12px;">
                        <td colspan="4" style="text-align: right">Outstanding</td>
                        <td style="text-align: right">{{number_format($data['subtotal_outstanding_amount'], '0', ',', '.')}}</td>
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
            <p>Jumlah GrandTotal :</p>
        </div>
        <div class="col-2 text-right pr-2">
            <p>{{$grand_total_sum}}</p>
        </div>
        <div class="col-2"></div>
    </div>
    <div class="row font-weight-bold">
        <div class="col-4"></div>
        <div class="col-4 text-end">
            <p>Jumlah Paid Amount :</p>
        </div>
        <div class="col-2 text-right pr-2">
            <p>{{$paid_amount_sum}}</p>
        </div>
        <div class="col-2"></div>
    </div>
    <div class="row font-weight-bold">
        <div class="col-4"></div>
        <div class="col-4 text-end">
            <p>Jumlah Outstanding Amount :</p>
        </div>
        <div class="col-2 text-right pr-2">
            <p>{{$outstanding_amount_sum}}</p>
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
