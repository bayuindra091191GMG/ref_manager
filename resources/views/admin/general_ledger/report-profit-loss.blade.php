<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>GL Profit & Loss</title>
    <link rel="stylesheet" id="css-main" href="{{ asset('css/oneui.min.css')}}">
    <style>
        .code-head{
            min-width:250px;
        }
        .name-head{
            min-width:400px;
        }
        .debit-head {
            min-width: 250px;
        }

        .credit-head {
            min-width: 250px;
        }

        .ending-head {
            min-width: 250px;
        }


        @media print{
            .code-head{
                min-width:225px;
            }
            .name-head{
                min-width:325px;
            }
            .debit-head {
                min-width: 225px;
            }

            .credit-head {
                min-width: 225px;
            }

            .ending-head {
                min-width: 225px;
            }
        }
        table, td, th {
            border: 2px solid black;
        }
        th{
            padding:5px;
        }
        td{
            padding:5px;
        }
        /*@@page {*/
        /*    size: landscape;*/
        /*}*/
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
        body {
            background-color: #fff;
        }
    </style>
</head>
<body>
<section style="font-size:12px;">
    <div class="container">
        <div id='non-print' class="row mb-3">
            <div class="col-12 text-end">
                <a class="btn btn-danger mt-lg-3" href="{{route('admin.general_ledger.report.filter', ['type' => $type])}}">
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
{{--                <img src="{{asset('images/emp_logo.jpg')}}" alt="" style="width: 150px; height: auto">--}}
            </div>
            <div class="col-8 pl-5 font-weight-bold text-center">
{{--                <p class="" style="font-size: 20px;">{{$branch->name}}</p>--}}
{{--                <p class="">{{$branch->address ?? "-"}}</p>--}}
                <p class="text-center font-weight-bold" style="font-size: 18px;">Profit and Loss Reports</p>
                <p class="text-center font-weight-bold" style="font-size: 12px;">Period: {{$start_date}} To {{$end_date}}</p>
            </div>
            <div class="col-2">
                <p>Print Date: {{\Carbon\Carbon::now('Asia/Jakarta')}}</p>
            </div>
        </div>
    </div>
</section>

<section class=" pt-4">
    <div class="container" style="font-size: 12px;">
        <div class="row pb-5">
            <div class="col-12 ">
                <table>
                    <tr class="text-center font-weight-bold p-rl-0" style="font-size: 14px;">
                        <th class="code-head">Account Code</th>
                        <th class="name-head">Account Name</th>
{{--                        <th class="debit-head">This Period</th>--}}
                        <th class="credit-head">Cumulative</th>
                    </tr>
                    @php($totalPeriod = 0)
                    @foreach ($result['data'] as $data)
                        @php($totalPeriod += $data['this_period'])
                        <tr class="">
                            <td>{{$data['coa_code']}}</td>
                            <td>{{$data['coa_name']}}</td>
{{--                            <td class="text-right">@detail.ThisPeriod</td>--}}
                            <td class="text-right">Rp {{number_format($data['this_period'], '0', ',', '.')}}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
</section>
<section>
    <div class="container" style="font-size: 12px;">
        <div class="row font-weight-bold" style="font-size: 16px;">
            <div class="col-8" style="border:2px solid black;">
                <div class="row text-right">
                    <div class="col-6">
                        <p>Total This Period :</p>
{{--                        <p>Total Cumulative :</p>--}}
                    </div>
                    <div class="col-6">
                        <p>Rp {{number_format($totalPeriod, '0', ',', '.')}}</p>
{{--                        <p>@header.TotalSaldo</p>--}}
                    </div>
                </div>
            </div>
            <div class="col-2"></div>
        </div>
    </div>
</section>


<script>
    function printDiv(divName) {
        window.print();
    }
</script>
</body>
</html>
