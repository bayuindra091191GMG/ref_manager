<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>GL Balance Sheet</title>
    <link rel="stylesheet" id="css-main" href="{{ asset('css/oneui.min.css')}}">
    <style>
        .container-fluid{
            width: 60%;
        }
        .no-head {
            min-width: 50px;
        }

        .code-head {
            min-width: 100px;
        }

        .name-head {
            min-width: 150px;
        }

        .saldo-head {
            min-width: 100px;
        }


        @media print {
            .container-fluid{
                width: 100%;
            }
            .no-head {
                min-width: 30px;
            }

            .code-head {
                min-width: 80px;
            }

            .name-head {
                min-width: 120px;
            }

            .saldo-head {
                min-width: 80px;
            }
        }

        table, td, th {
            border: 2px solid black;
        }

        th {
            padding: 5px;
        }

        td {
            padding: 5px;
        }
        /*@@page {*/
        /*    size: landscape;*/
        /*}*/

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
{{--                <p class="">{{$branch->address ?? "-"}}</p>--}}
                <p class="text-center font-weight-bold" style="font-size: 18px;">Balance Sheet Reports</p>
                <p class="text-center font-weight-bold" style="font-size: 16px;">{{$branch->name}}</p>
                <p class="text-center font-weight-bold" style="font-size: 12px;">Period: {{$start_date}} To {{$end_date}}</p>
            </div>
            <div class="col-2">
                <p>Print Date: {{\Carbon\Carbon::now('Asia/Jakarta')}}</p>
            </div>
        </div>
    </div>
</section>

<section class=" pt-3">
    <div class="container ">
    </div>
    <div class="container-fluid" style="font-size: 12px;">
        <div class="row">
            <div class="col-6 ">
                <div class="row  ">
                    <div class="col-2 font-weight-bold" style="font-size: 16px;">
                        <p>Aktiva </p>
                    </div>
                </div>
                <table>
                    <tr class="text-center font-weight-bold p-rl-0" style="font-size: 13px;">
                        <th class="no-head">No</th>
                        <th class="code-head">Account</th>
                        {{-- <th class="name-head">Account Name</th> --}}
                        <th class="saldo-head">This Periode</th>
                    </tr>
                    @php($ct=1)
                    @php($grandtotal = 0)
                    @foreach ($result['aktiva'] as $data)
                        <tr class="">
                            <td class="text-center">{{$ct}}</td>
                            <td><a href='#' class='ledger' data-id='{{ $data['id'] }}'>({{ $data['coa_code'] }}) {{ $data['coa_name'] }}</a></td>
                            {{-- <td>{{$data['coa_code']}}</td>
                            <td>{{$data['coa_name']}}</td> --}}
                            {{--                            <td class="text-right">@detail.ThisPeriod</td>--}}
                            <td class="text-end">{{number_format($data['total'], '0', ',', '.')}}</td>
                        </tr>
                        @if($data['level'] == 1)
                        @php($grandtotal += $data['total'])
                        @endif

                        @php($ct++)
                    @endforeach
                        <tr class="">
                            <td colspan="2">Total Aktiva</td>
                            <td class="text-end font-weight-bold">{{number_format($grandtotal, '0', ',', '.')}}</td>
                        </tr>
                </table>
            </div>
            <div class="col-6 ">
                <div class="row  ">
                    <div class="col-2 font-weight-bold" style="font-size: 16px;">
                        <p>Pasiva </p>
                    </div>
                </div>
                <table>
                    <tr class="text-center font-weight-bold p-rl-0" style="font-size: 13px;">
                        <th class="no-head">No</th>
                        <th class="code-head">Account</th>
                        {{-- <th class="name-head">Account Name</th> --}}
                        <th class="saldo-head">This Periode</th>
                    </tr>

                    @php($ct2=1)
                    @php($grandtotal2 = 0)
                    @foreach ($result['pasiva'] as $data)

                        <tr class="">
                            <td class="text-center">{{$ct2}}</td>
                            <td><a href='#' class='ledger' data-id='{{ $data['id'] }}'>({{ $data['coa_code'] }}) {{ $data['coa_name'] }}</a></td>
                            {{-- <td>{{$data['coa_code']}}</td> --}}
                            {{-- <td>{{$data['coa_name']}}</td> --}}
                            {{--                            <td class="text-right">@detail.ThisPeriod</td>--}}
                            <td class="text-end">{{number_format($data['total'], '0', ',', '.')}}</td>
                        </tr>
                        @php($grandtotal2 += $data['total'])
                        @php($ct2++)
                    @endforeach
                        <tr class="">
                            <td colspan="2">Total Pasiva</td>
                            <td class="text-end font-weight-bold">{{number_format($grandtotal2, '0', ',', '.')}}</td>
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
