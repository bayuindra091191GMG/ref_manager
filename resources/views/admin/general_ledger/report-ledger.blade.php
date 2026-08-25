<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>GL Ledger</title>
    <link rel="stylesheet" id="css-main" href="{{ asset('css/oneui.min.css')}}">
    <style>
        .date-head{
            min-width:125px;
        }
        .voucher-head{
            min-width:150px;
        }
        .ref-head{
            min-width:150px;
        }
        .desc-head{
            min-width:275px;
        }
        .debit-head{
            min-width:150px;
        }
        .credit-head{
            min-width:150px;
        }
        .saldo-head{
            min-width:150px;
        }
        @media print{
            .date-head{
                min-width:125px;
            }
            .voucher-head{
                min-width:125px;
            }
            .ref-head{
                min-width:125px;
            }
            .desc-head{
                min-width:225px;
            }
            .debit-head{
                min-width:125px;
            }
            .credit-head{
                min-width:125px;
            }
            .saldo-head{
                min-width:125px;
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
                <p class="text-center font-weight-bold" style="font-size: 18px;">Ledger Reports</p>
                <p class="text-center font-weight-bold" style="font-size: 16px;">{{$branch->name}}</p>
                <p class="text-center font-weight-bold" style="font-size: 12px;">Period: {{$start_date}} To {{$end_date}}</p>
            </div>
            <div class="col-2">
                <p>Print Date: {{\Carbon\Carbon::now('Asia/Jakarta')}}</p>
            </div>
        </div>
    </div>
</section>

@foreach ($result as $resultData)
<section class="" style="font-size:12px;">
    <div class="container py-4">
        <div class="row  ">
            <div class="col-12 ">
                <p>{{$resultData['coa_code']}} - {{$resultData['coa_name']}}</p>
            </div>
        </div>
    </div>
    <div class="container" style="font-size:12px;">
        <div class="row pt-2">
            <div class="col-12 ">
                <table>
                    <tr class="text-center font-weight-bold p-rl-0">
                        <th class="date-head">Date</th>
                        <th class="voucher-head">Kode Transaksi</th>
                        <th class="ref-head">Ref. No</th>
                        <th class="desc-head">Description</th>
                        <th class="debit-head">Debit</th>
                        <th class="credit-head">Credit</th>
                        <th class="saldo-head">Saldo</th>
                    </tr>
                    @php($totalDebit=0)
                    @php($totalCredit = 0)
                    @foreach ($resultData['data'] as $data)
                        <tr class="">
                            <td>{{$data['transaction_date']}}</td>
                            <td>{{$data['transaction_code']}}</td>
                            <td>{{$data['reference_code']}}</td>
                            <td>{{$data['notes']}}</td>
                            <td style="text-align: right;">{{number_format($data['debit'], '0', ',', '.')}}</td>
                            <td style="text-align: right;">{{number_format($data['credit'], '0', ',', '.')}}</td>
                            <td style="text-align: right;">{{number_format($data['saldo'], '0', ',', '.')}}</td>
                        </tr>

                        @php($totalDebit += $data['debit'])
                        @php($totalCredit += $data['credit'])
                    @endforeach
                    <tr class="">
                        <td colspan="4"></td>
                        <td class="font-weight-bold" style="text-align: right;">{{number_format($totalDebit, '0', ',', '.')}}</td>
                        <td class="font-weight-bold" style="text-align: right;">{{number_format($totalCredit, '0', ',', '.')}}</td>
                        <td class="font-weight-bold" style="text-align: right;">&nbsp;</td>
                    </tr>
                </table>
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
