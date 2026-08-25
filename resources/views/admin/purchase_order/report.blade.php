<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchase Order Report</title>
    <link rel="stylesheet" href="css/bootstrap.css">
    <style>

        body {
            background-color: #fff;
        }
        .sup-head{
            min-width:125px;
        }
        .curr-head{
            min-width:40px;
        }
        .rate-head{
            min-width:60px;
        }
        .code-head{
            min-width:75px;
        }
        .name-head{
            min-width:175px;
        }
        .qty-head{
            min-width:50px;
        }
        .unit-head{
            min-width:50px;
        }
        .price-head{
            min-width:75px;
        }
        .total-head{
            min-width:100px;
        }
        .d-qty-head{
            min-width:50px;
        }
        .d-1-head{
            min-width:50px;
        }
        .d-2-head{
            min-width:50px;
        }
        .ppn-head{
            min-width:50px;
        }
        .sub-head{
            min-width:100px;
        }
        .desc-head{
            min-width:85px;
        }
        @media print{
            .sup-head{
                min-width:125px;
            }
            .curr-head{
                min-width:25px;
            }
            .rate-head{
                min-width:50px;
            }
            .code-head{
                min-width:75px;
            }
            .name-head{
                min-width:100px;
            }
            .qty-head{
                min-width:50px;
            }
            .unit-head{
                min-width:50px;
            }
            .price-head{
                min-width:75px;
            }
            .total-head{
                min-width:100px;
            }
            .d-qty-head{
                min-width:40px;
            }
            .d-1-head{
                min-width:40px;
            }
            .d-2-head{
                min-width:40px;
            }
            .ppn-head{
                min-width:40px;
            }
            .sub-head{
                min-width:75px;
            }
            .desc-head{
                min-width:75px;
            }
        }
        table, td, th {
            border: 1px solid black;
        }
        th{
            padding:5px;
        }
        td{
            padding:5px;
        }
        @page {
            size: landscape;
        }
        p{
            margin-bottom:0px;
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
    </style>
</head>
<body>
<section style="font-size:12px;">
    <div class="container">
        <div id='non-print' class="row mb-3">
            <a href="{{route('admin.po.report.filter')}}">
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
                <p class="text-center font-weight-bold" style="font-size: 18px;">Purchase Order Reports</p>
                <p class="text-center font-weight-bold" style="font-size: 12px;">Period: {{$start_date}} To {{$end_date}}</p>
            </div>
            <div class="col-2">
                <p>Print Date: {{\Carbon\Carbon::now('Asia/Jakarta')}}</p>
            </div>
        </div>
    </div>
</section>
@foreach ($result as $data)
<section class="pb-2" style="font-size:10px;">
    <div class="container ">
        <div class="row  ">
            <div class="col-2">
                <p>Transaction Number </p>
            </div>
            <div class="col-10 font-weight-bold">
                <p>: {{$data->code}}</p>
            </div>
        </div>
        <div class="row  ">
            <div class="col-2">
                <p>Transaction Date </p>
            </div>
            <div class="col-10">
                <p>: {{$data->transaction_date->format('d-m-Y')}}</p>
            </div>
        </div>
        <div class="row  ">
            <div class="col-2">
                <p>No. Purchase Request </p>
            </div>
            <div class="col-10">
                <p>: {{$data->reference_code}}</p>
            </div>
        </div>
    </div>
    <div class="container" style="font-size:8px;">
        <div class="row pt-2">
            <div class="col-12 ">
                <table>
                    <tr class="text-center font-weight-bold p-rl-0">
                        <th class="sup-head">Supplier</th>
                        <th class="code-head">Item Code</th>
                        <th class="name-head">Item Name</th>
                        <th class="qty-head">Qty</th>
                        <th class="unit-head">Unit</th>
                        <th class="price-head">Price</th>
                        <th class="total-head">Total</th>
                        <th class="d-1-head">Disc %1</th>
                        <th class="d-2-head">Disc %2</th>
                        <th class="sub-head">Subtotal</th>
                        <th class="desc-head">Description</th>
                    </tr>
                    @php($ct=1)
                    @foreach ($data->purchase_order_details as $detail)
                        <tr class="">
                            <td>{{$data->supplier->name}}</td>
                            <td>{{$detail->item->code}}</td>
                            <td>{{$detail->item->name}}</td>
                            <td class="text-right">{{number_format($detail->qty, '0', ',', '.')}}</td>
                            <td>{{$detail->unit_material->name}}</td>
                            <td class="text-right">{{number_format($detail->price, '0', ',', '.')}}</td>
                            <td class="text-right">{{number_format($detail->total, '0', ',', '.')}}</td>
                            <td class="text-right">{{number_format($detail->disc1_value, '0', ',', '.')}}</td>
                            <td class="text-right">{{number_format($detail->disc2_value, '0', ',', '.')}}</td>
                            <td class="text-right">{{number_format($detail->subtotal, '0', ',', '.')}}</td>
                            <td>{{$detail->description}}</td>
                        </tr>
                        @php($ct++)
                    @endforeach
                </table>
            </div>
        </div>
        <div class="row pt-2">
            <div class="col-6"></div>
            <div class="col-2 " style="padding-left:100px;">
                <p>Total</p>
            </div>
            <div class="col-1 " style="padding-left:55px;">
                <p>:</p>
            </div>
            <div class="col-2 text-right font-weight-bold" style="padding-left:80px;">
                <p>{{number_format($data->subtotal, '0', ',', '.')}}</p>
            </div>
            <div class="col-1"></div>
        </div>
        <div class="row">
            <div class="col-6"></div>
            <div class="col-2 " style="padding-left:100px;">
                <p>Diskon 1</p>
            </div>
            <div class="col-1 p-rl-0">
                <p>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; :
                    <span>{{$data->disc1_percent}} %</span>
                </p>
            </div>
            <div class="col-2 text-right font-weight-bold" style="padding-left:80px;">
                <p>{{number_format($data->disc1_value, '0', ',', '.')}}</p>
            </div>
            <div class="col-1"></div>
        </div>
        <div class="row">
            <div class="col-6"></div>
            <div class="col-2 " style="padding-left:100px;">
                <p>Diskon 2</p>
            </div>
            <div class="col-1 p-rl-0">
                <p>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; :
                    <span>{{$data->disc2_percent}} %</span>
                </p>
            </div>
            <div class="col-2 text-right font-weight-bold" style="padding-left:80px;">
                <p>{{number_format($data->disc2_value, '0', ',', '.')}}</p>
            </div>
            <div class="col-1"></div>
        </div>
        <div class="row">
            <div class="col-6"></div>
            <div class="col-2 " style="padding-left:100px;">
                <p>PPN</p>
            </div>
            <div class="col-1 p-rl-0">
                <p>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; :
                    <span>{{$data->ppn_percent}} %</span>
                </p>
            </div>
            <div class="col-2 text-right font-weight-bold" style="padding-left:80px;">
                <p>{{number_format($data->ppn_value, '0', ',', '.')}}</p>
            </div>
            <div class="col-1"></div>
        </div>
        <div class="row">
            <div class="col-6"></div>
            <div class="col-2 " style="padding-left:100px;">
                <p>Grandtotal</p>
            </div>
            <div class="col-1 " style="padding-left:55px;">
                <p>:</p>
            </div>
            <div class="col-2 text-right font-weight-bold" style="padding-left:80px;">
                <p>{{number_format($data->grandtotal, '0', ',', '.')}}</p>
            </div>
            <div class="col-1"></div>
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
