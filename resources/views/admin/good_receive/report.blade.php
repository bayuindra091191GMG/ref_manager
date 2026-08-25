<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Good Receive Report</title>
    <link rel="stylesheet" href="css/bootstrap.css">
    <style>
        .trans-head {
            min-width: 100px;
        }

        .trx-head {
            min-width: 150px;
        }

        .sup-head {
            min-width: 100px;
        }

        .curr-head {
            min-width: 40px;
        }

        .rate-head {
            min-width: 60px;
        }

        .code-head {
            min-width: 75px;
        }

        .name-head {
            min-width: 175px;
        }

        .qty-head {
            min-width: 50px;
        }

        .unit-head {
            min-width: 50px;
        }

        .price-head {
            min-width: 75px;
        }

        .total-head {
            min-width: 95px;
        }

        .d-qty-head {
            min-width: 50px;
        }

        .d-1-head {
            min-width: 50px;
        }

        .d-2-head {
            min-width: 50px;
        }

        .ppn-head {
            min-width: 50px;
        }

        .sub-head {
            min-width: 125px;
        }

        .desc-head {
            min-width: 90px;
        }

        @media print {
            .trans-head {
                min-width: 100px;
            }

            .trx-head {
                min-width: 100px;
            }

            .sup-head {
                min-width: 100px;
            }

            .curr-head {
                min-width: 25px;
            }

            .rate-head {
                min-width: 50px;
            }

            .code-head {
                min-width: 75px;
            }

            .name-head {
                min-width: 100px;
            }

            .qty-head {
                min-width: 50px;
            }

            .unit-head {
                min-width: 50px;
            }

            .price-head {
                min-width: 75px;
            }

            .total-head {
                min-width: 95px;
            }

            .d-qty-head {
                min-width: 40px;
            }

            .d-1-head {
                min-width: 40px;
            }

            .d-2-head {
                min-width: 40px;
            }

            .ppn-head {
                min-width: 40px;
            }

            .sub-head {
                min-width: 100px;
            }

            .desc-head {
                min-width: 80px;
            }
        }

        table, td, th {
            border: 1px solid black;
        }

        th {
            padding: 5px;
        }

        td {
            padding: 5px;
        }

        @page {
            size: landscape;
        }


        p {
            margin-bottom: 0px;
            line-height: 1.8em;
        }

        .p-rl-0 {
            padding-right: 0px !important;
            padding-left: 0px !important;
        }

        .border-top-custom {
            border-top: 5px solid black;
        }

        .border-bottom-custom {
            border-bottom: 5px solid black;
        }

        .border-bottom-custom-2 {
            border-bottom: 1px solid black;
        }

        .container {
            padding: 0px;
        }

        .logo {
            width: 180px;
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
    <div class="container">
        <div id='non-print' class="row mb-3">
            <a href="{{route('admin.gr.report.filter')}}">
            Back
            </a>
            &nbsp;
            <button class="btn btn-success rounded-round" onclick="printDiv('printMe')">
                Print
            </button>
        </div>
        <div class="row">
            <div class="col-2  pb-2">
                {{--                <img src="{{asset('images/emp_logo.jpg')}}" alt="" style="width: 150px; height: auto">--}}
            </div>
            <div class="col-8 pl-5 font-weight-bold text-center">
                <p class="" style="font-size: 20px;">{{$branch->name}}</p>
                <p class="">{{$branch->address ?? "-"}}</p>
                <p class="text-center font-weight-bold" style="font-size: 18px;">Good Receive Reports</p>
                <p class="text-center font-weight-bold" style="font-size: 12px;">Period: {{$start_date}} To {{$end_date}}</p>
            </div>
            <div class="col-2">
                <p>Print Date: {{\Carbon\Carbon::now('Asia/Jakarta')}}</p>
            </div>
        </div>
    </div>
</section>

@foreach($result as $data)
<section class="pb-2" style="font-size:10px;">
    <div class="container ">
        <div class="row  ">
            <div class="col-2">
                <p>Supplier </p>
            </div>
            <div class="col-10 font-weight-bold">
                <p>: {{$data['supplier_code']}} - {{$data['supplier_name']}}</p>
            </div>
        </div>
    </div>
    <div class="container" style="font-size:8px;">
        <div class="row pt-2">
            <div class="col-12 ">
                <table>
                    <tr class="text-center font-weight-bold p-rl-0">
                        <th style="min-width:25px;">No</th>
                        <th style="min-width:100px;">Transaction Number</th>
                        <th style="min-width:75px;">Date</th>
                        <th style="min-width:100px;">No PO</th>
                        <th style="min-width:100px;">No PR</th>
{{--                        <th style="min-width:100px;">No IR</th>--}}
                        <th style="min-width:100px;">Warehouse Code</th>
                        <th style="min-width:75px;">Item Code</th>
                        <th style="min-width:100px">Item Name</th>
                        <th style="min-width:50px;">Unit</th>
                        <th style="min-width:50px">Qty</th>
                        <th style="min-width:100px;">Description</th>
                    </tr>
                    @foreach ($data['transactions'] as $transaction)

                    <tr class="">
                            <td>{{$transaction['no']}}</td>
                            <td>{{$transaction['code']}}</td>
                            <td>{{$transaction['date']}}</td>
                            <td>{{$transaction['pr_code']}}</td>
                            <td>{{$transaction['po_code']}}</td>
{{--                            <td>@detail.IrTransactionNumber</td>--}}
                            <td>{{$transaction['warehouse']}}</td>
                            <td>{{$transaction['item_code']}}</td>
                            <td>{{$transaction['item_name']}}</td>
                            <td>{{$transaction['unit_name']}}</td>
                            <td class="text-right">{{$transaction['qty']}}</td>
                            <td>{{$transaction['description']}}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
        <div class="row pt-2">
            <div class="col-6"></div>
            <div class="col-2 " style="padding-left:150px;">
                <p>Total Qty</p>
            </div>
            <div class="col-1 " style="padding-left:35px;">
                <p>:</p>
            </div>
            <div class="col-2 text-right font-weight-bold" style="margin-left:-65px;">
                <td class="text-right">{{number_format($data['subtotal_qty'], '0', ',', '.')}}</td>
            </div>
        </div>
    </div>
</section>
@endforeach


<script>
    function printDiv(divName) {
        window.print();
    }
</script>
</body>
</html>
