<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>GL Trial & Balance</title>
    <link rel="stylesheet" id="css-main" href="{{ asset('css/oneui.min.css')}}">
    <style>
        .container-fluid{
            width: 60%
        }
        .code-head{
            min-width:175px;
        }
        .name-head{
            min-width:250px;
        }
        .begin-head{
            min-width:175px;
        }
        .debit-head{
            min-width:175px;
        }
        .credit-head{
            min-width:175px;
        }
        .ending-head{
            min-width:175px;
        }

        @media print{
            .container-fluid{
                width: 100%;
            }
            .code-head{
                min-width:125px;
            }
            .name-head{
                min-width:250px;
            }
            .begin-head{
                min-width:150px;
            }
            .debit-head{
                min-width:150px;
            }
            .credit-head{
                min-width:150px;
            }
            .ending-head{
                min-width:150px;
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
    <div class="container-fluid">
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
                <p class="text-center font-weight-bold" style="font-size: 18px;">Trial and Balance Reports</p>
                <p class="text-center font-weight-bold" style="font-size: 16px;">{{$branch->name}}</p>
                <p class="text-center font-weight-bold" style="font-size: 12px;">Period: {{$start_date}} To {{$end_date}}</p>
            </div>
            <div class="col-2">
                <p>Print Date: {{\Carbon\Carbon::now('Asia/Jakarta')}}</p>
            </div>
        </div>
    </div>
</section>

<section class=" pt-4">
    <div class="container-fluid" style="font-size: 12px;">
        <div class="row pt-2">
            <div class="col-12 ">
                <table>
                    <tr class="text-center font-weight-bold p-rl-0">
                        <th class="code-head">Account</th>
                        {{-- <th class="name-head">Name</th> --}}
                        <th class="begin-head">Beginning Balance</th>
                        <th class="debit-head">Debit</th>
                        <th class="credit-head">Credit</th>
                        <th class="ending-head">Ending Balance</th>
                    </tr>
@php($totalBegining = 0)
@php($totalDebit = 0)
@php($totalCredit = 0)
@php($totalEnding = 0)
                    @foreach ($result['data'] as $data)
                        @php($totalBegining += $data['begining_balance'])
                        @php($totalDebit += $data['debit'])
                        @php($totalCredit += $data['credit'])
                        @php($totalEnding += $data['ending_balance'])
                        <tr class="">
                            <td><a href='#' class='ledger' data-id='{{ $data['id'] }}'>({{ $data['coa_code'] }}) {{ $data['coa_name'] }}</a></td>
                            {{-- <td>{{$data['coa_code']}}</td>
                            <td>{{$data['coa_name']}}</td> --}}
                            <td class="text-right">Rp {{number_format($data['begining_balance'], '0', ',', '.')}}</td>
                            <td class="text-right">Rp {{number_format($data['debit'], '0', ',', '.')}}</td>
                            <td class="text-right">Rp {{number_format($data['credit'], '0', ',', '.')}}</td>
                            <td class="text-right">Rp {{number_format($data['ending_balance'], '0', ',', '.')}}</td>
                        </tr>
                    @endforeach
                        <tr class="font-weight-bold text-right">
                            <td>Total</td>
                            <td>Rp {{number_format($totalBegining, '0', ',', '.')}}</td>
                            <td>Rp {{number_format($totalDebit, '0', ',', '.')}}</td>
                            <td>Rp {{number_format($totalCredit, '0', ',', '.')}}</td>
                            <td>Rp {{number_format($totalEnding, '0', ',', '.')}}</td>
                        </tr>
                </table>
            </div>
        </div>
    </div>
</section>

<form id="form-ledger" method="post" action="{{ route('admin.general_ledger.report.generate') }}" target="_blank">
    @csrf
    <input type="hidden" name="type" value="Ledger Detail">
    <input type="hidden" name="start_date" value="{{ date('d-m-Y', strtotime($start_date)) }}">
    <input type="hidden" name="end_date" value="{{ date('d-m-Y', strtotime($end_date)) }}">
    <input type="hidden" id="coa" name="coa" value="">
</form>


<script src="{{ asset('js/lib/jquery.min.js') }}"></script>
<script>
    function printDiv(divName) {
        window.print();
    }

    $('.ledger').on('click', function (e){
        e.preventDefault();

        $('#coa').val($(this).data('id'));
        $('#form-ledger')[0].submit();
    });
</script>
</body>
</html>
