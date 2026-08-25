<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cash Flow Report</title>
    <link rel="stylesheet" id="css-main" href="{{ asset('css/oneui.min.css')}}">
    <style>
        .container-fluid{
            width: 80%
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

        @media print {
            .container-fluid{
                width: 100%
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
                <p class="text-center font-weight-bold" style="font-size: 18px;">Cash Flow Report</p>
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
    <div class="container-fluid ">
        <div class="row  ">
            <div class="col-2">
                <p>Cash In </p>
            </div>
        </div>
    </div>
    <div class="container-fluid" style="font-size: 7px;">
        <div class="row pt-2">
            <div class="col-12 ">
                <table>
                    <tr class="text-center font-weight-bold p-rl-0">
                        <th class="col0-head">No </th>
                        <th class="col1-head">Account</th>
                        <th class="col5-head">Jan-{{$year}}</th>
                        <th class="col5-head">Feb-{{$year}}</th>
                        <th class="col5-head">Mar-{{$year}}</th>
                        <th class="col5-head">Apr-{{$year}}</th>
                        <th class="col5-head">May-{{$year}}</th>
                        <th class="col5-head">Jun-{{$year}}</th>
                        <th class="col5-head">Jul-{{$year}}</th>
                        <th class="col5-head">Aug-{{$year}}</th>
                        <th class="col5-head">Sept-{{$year}}</th>
                        <th class="col5-head">Oct-{{$year}}</th>
                        <th class="col5-head">Nov-{{$year}}</th>
                        <th class="col5-head">Dec-{{$year}}</th>
                    </tr>
                    @php($ct=1)

                    @foreach ($result['transaction_cash_in'] as $transaction)
                        <tr class="">
                            <td class="col0-head">{{$ct}}</td>
                            <td class="col1-head"><a href='#' class='ledger' data-id='{{ $transaction['id'] }}'>({{ $transaction['coa_code'] }}) {{ $transaction['coa_name'] }}</a></td>
                            {{-- <td class="col1-head">{{$transaction['coa_code']}}</td>
                            <td class="col1-head">{{$transaction['coa_name']}}</td> --}}
                            <td class="col5-head text-center">Rp {{number_format($transaction['01'], '0', ',', '.')}}</td>
                            <td class="col5-head text-center">Rp {{number_format($transaction['02'], '0', ',', '.')}}</td>
                            <td class="col5-head text-center">Rp {{number_format($transaction['03'], '0', ',', '.')}}</td>
                            <td class="col5-head text-center">Rp {{number_format($transaction['04'], '0', ',', '.')}}</td>
                            <td class="col5-head text-center">Rp {{number_format($transaction['05'], '0', ',', '.')}}</td>
                            <td class="col5-head text-center">Rp {{number_format($transaction['06'], '0', ',', '.')}}</td>
                            <td class="col5-head text-center">Rp {{number_format($transaction['07'], '0', ',', '.')}}</td>
                            <td class="col5-head text-center">Rp {{number_format($transaction['08'], '0', ',', '.')}}</td>
                            <td class="col5-head text-center">Rp {{number_format($transaction['09'], '0', ',', '.')}}</td>
                            <td class="col5-head text-center">Rp {{number_format($transaction['10'], '0', ',', '.')}}</td>
                            <td class="col5-head text-center">Rp {{number_format($transaction['11'], '0', ',', '.')}}</td>
                            <td class="col5-head text-center">Rp {{number_format($transaction['12'], '0', ',', '.')}}</td>
                        </tr>
                        @php($ct++)
                    @endforeach
{{--                        <tr class="font-weight-bold text-right">--}}
{{--                            <td colspan="2">Subtotal</td>--}}
{{--                            <td class="text-center">@string.Format(System.Globalization.CultureInfo.CreateSpecificCulture("id-id"), "{0:N}", header.BeginningBalance)</td>--}}
{{--                            <td class="text-center">@string.Format(System.Globalization.CultureInfo.CreateSpecificCulture("id-id"), "{0:N}", header.JanBalance)</td>--}}
{{--                            <td class="text-center">@string.Format(System.Globalization.CultureInfo.CreateSpecificCulture("id-id"), "{0:N}", header.FebBalance)</td>--}}
{{--                            <td class="text-center">@string.Format(System.Globalization.CultureInfo.CreateSpecificCulture("id-id"), "{0:N}", header.MarBalance)</td>--}}
{{--                            <td class="text-center">@string.Format(System.Globalization.CultureInfo.CreateSpecificCulture("id-id"), "{0:N}", header.AprBalance)</td>--}}
{{--                            <td class="text-center">@string.Format(System.Globalization.CultureInfo.CreateSpecificCulture("id-id"), "{0:N}", header.MayBalance)</td>--}}
{{--                            <td class="text-center">@string.Format(System.Globalization.CultureInfo.CreateSpecificCulture("id-id"), "{0:N}", header.JunBalance)</td>--}}
{{--                            <td class="text-center">@string.Format(System.Globalization.CultureInfo.CreateSpecificCulture("id-id"), "{0:N}", header.JulBalance)</td>--}}
{{--                            <td class="text-center">@string.Format(System.Globalization.CultureInfo.CreateSpecificCulture("id-id"), "{0:N}", header.AugBalance)</td>--}}
{{--                            <td class="text-center">@string.Format(System.Globalization.CultureInfo.CreateSpecificCulture("id-id"), "{0:N}", header.SeptBalance)</td>--}}
{{--                            <td class="text-center">@string.Format(System.Globalization.CultureInfo.CreateSpecificCulture("id-id"), "{0:N}", header.OctBalance)</td>--}}
{{--                            <td class="text-center">@string.Format(System.Globalization.CultureInfo.CreateSpecificCulture("id-id"), "{0:N}", header.NovBalance)</td>--}}
{{--                            <td class="text-center">@string.Format(System.Globalization.CultureInfo.CreateSpecificCulture("id-id"), "{0:N}", header.DecBalance)</td>--}}
{{--                            <td class="text-center">@string.Format(System.Globalization.CultureInfo.CreateSpecificCulture("id-id"), "{0:N}", header.CurrentBalance)</td>--}}
{{--                        </tr>--}}
                </table>
            </div>
        </div>
    </div>

    <div class="container-fluid ">
        <div class="row  ">
            <div class="col-2">
                <p>Cash Out </p>
            </div>
        </div>
    </div>
    <div class="container-fluid" style="font-size: 7px;">
        <div class="row pt-2">
            <div class="col-12 ">
                <table>
                    <tr class="text-center font-weight-bold p-rl-0">
                        <th class="col0-head">No </th>
                        <th class="col1-head">Account</th>
                        {{-- <th class="col5-head">Account Name</th> --}}
                        <th class="col5-head">Jan-{{$year}}</th>
                        <th class="col5-head">Feb-{{$year}}</th>
                        <th class="col5-head">Mar-{{$year}}</th>
                        <th class="col5-head">Apr-{{$year}}</th>
                        <th class="col5-head">May-{{$year}}</th>
                        <th class="col5-head">Jun-{{$year}}</th>
                        <th class="col5-head">Jul-{{$year}}</th>
                        <th class="col5-head">Aug-{{$year}}</th>
                        <th class="col5-head">Sept-{{$year}}</th>
                        <th class="col5-head">Oct-{{$year}}</th>
                        <th class="col5-head">Nov-{{$year}}</th>
                        <th class="col5-head">Dec-{{$year}}</th>
                    </tr>
                    @php($ct=1)

                    @foreach ($result['transaction_cash_out'] as $transaction)
                        <tr class="">
                            <td class="col0-head">{{$ct}}</td>
                            <td class="col1-head"><a href='#' class='ledger' data-id='{{ $transaction['id'] }}'>({{ $transaction['coa_code'] }}) {{ $transaction['coa_name'] }}</a></td>
                            {{-- <td class="col1-head">{{$transaction['coa_code']}}</td>
                            <td class="col1-head">{{$transaction['coa_name']}}</td> --}}
                            <td class="col5-head text-center">Rp {{number_format($transaction['01'], '0', ',', '.')}}</td>
                            <td class="col5-head text-center">Rp {{number_format($transaction['02'], '0', ',', '.')}}</td>
                            <td class="col5-head text-center">Rp {{number_format($transaction['03'], '0', ',', '.')}}</td>
                            <td class="col5-head text-center">Rp {{number_format($transaction['04'], '0', ',', '.')}}</td>
                            <td class="col5-head text-center">Rp {{number_format($transaction['05'], '0', ',', '.')}}</td>
                            <td class="col5-head text-center">Rp {{number_format($transaction['06'], '0', ',', '.')}}</td>
                            <td class="col5-head text-center">Rp {{number_format($transaction['07'], '0', ',', '.')}}</td>
                            <td class="col5-head text-center">Rp {{number_format($transaction['08'], '0', ',', '.')}}</td>
                            <td class="col5-head text-center">Rp {{number_format($transaction['09'], '0', ',', '.')}}</td>
                            <td class="col5-head text-center">Rp {{number_format($transaction['10'], '0', ',', '.')}}</td>
                            <td class="col5-head text-center">Rp {{number_format($transaction['11'], '0', ',', '.')}}</td>
                            <td class="col5-head text-center">Rp {{number_format($transaction['12'], '0', ',', '.')}}</td>
                        </tr>
                        @php($ct++)
                    @endforeach
{{--                        <tr class="font-weight-bold text-right">--}}
{{--                            <td colspan="2">Subtotal</td>--}}
{{--                            <td class="text-center">@string.Format(System.Globalization.CultureInfo.CreateSpecificCulture("id-id"), "{0:N}", header.BeginningBalance)</td>--}}
{{--                            <td class="text-center">@string.Format(System.Globalization.CultureInfo.CreateSpecificCulture("id-id"), "{0:N}", header.JanBalance)</td>--}}
{{--                            <td class="text-center">@string.Format(System.Globalization.CultureInfo.CreateSpecificCulture("id-id"), "{0:N}", header.FebBalance)</td>--}}
{{--                            <td class="text-center">@string.Format(System.Globalization.CultureInfo.CreateSpecificCulture("id-id"), "{0:N}", header.MarBalance)</td>--}}
{{--                            <td class="text-center">@string.Format(System.Globalization.CultureInfo.CreateSpecificCulture("id-id"), "{0:N}", header.AprBalance)</td>--}}
{{--                            <td class="text-center">@string.Format(System.Globalization.CultureInfo.CreateSpecificCulture("id-id"), "{0:N}", header.MayBalance)</td>--}}
{{--                            <td class="text-center">@string.Format(System.Globalization.CultureInfo.CreateSpecificCulture("id-id"), "{0:N}", header.JunBalance)</td>--}}
{{--                            <td class="text-center">@string.Format(System.Globalization.CultureInfo.CreateSpecificCulture("id-id"), "{0:N}", header.JulBalance)</td>--}}
{{--                            <td class="text-center">@string.Format(System.Globalization.CultureInfo.CreateSpecificCulture("id-id"), "{0:N}", header.AugBalance)</td>--}}
{{--                            <td class="text-center">@string.Format(System.Globalization.CultureInfo.CreateSpecificCulture("id-id"), "{0:N}", header.SeptBalance)</td>--}}
{{--                            <td class="text-center">@string.Format(System.Globalization.CultureInfo.CreateSpecificCulture("id-id"), "{0:N}", header.OctBalance)</td>--}}
{{--                            <td class="text-center">@string.Format(System.Globalization.CultureInfo.CreateSpecificCulture("id-id"), "{0:N}", header.NovBalance)</td>--}}
{{--                            <td class="text-center">@string.Format(System.Globalization.CultureInfo.CreateSpecificCulture("id-id"), "{0:N}", header.DecBalance)</td>--}}
{{--                            <td class="text-center">@string.Format(System.Globalization.CultureInfo.CreateSpecificCulture("id-id"), "{0:N}", header.CurrentBalance)</td>--}}
{{--                        </tr>--}}
                </table>
            </div>
        </div>
    </div>
</section>

{{--<div class="container">--}}
{{--    <div class="row font-weight-bold">--}}
{{--        <div class="col-6"></div>--}}
{{--        <div class="col-2">--}}
{{--            <p>CurrentBalance Jumlah<span class="float-right">:</span> </p>--}}
{{--        </div>--}}
{{--        <div class="col-2 text-right pr-2">--}}
{{--            <p>@string.Format(System.Globalization.CultureInfo.CreateSpecificCulture("id-id"), "{0:N}", Model.CurrentBalance)</p>--}}
{{--        </div>--}}
{{--        <div class="col-2"></div>--}}
{{--    </div>--}}
{{--</div>--}}



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
