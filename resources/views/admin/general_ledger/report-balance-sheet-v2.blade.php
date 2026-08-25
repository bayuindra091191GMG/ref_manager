<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>GL Balance Sheet</title>
    <link rel="stylesheet" id="css-main" href="{{ asset('css/oneui.min.css')}}">
    <style>
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
        .text-right{
            text-align: right;
        }


        @media print {
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

        /*table, td, th {*/
        /*    border: 2px solid black;*/
        /*}*/

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

        .ps-10 {
            padding-left: 6rem !important;
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
                <p class="text-center font-weight-bold" style="font-size: 18px;">Balance Sheet Reports</p>
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
    <div class="container" style="font-size: 12px;">
        <div class="row">
            <div class="col-6 border-end border-dark">
                <div class="row  ">
                    <div colspan="3" class="fw-bold border-top border-bottom border-dark" style="font-size: 16px;">
                        <p>Aktiva </p>
                    </div>
                </div>
                <table style="width: 100%">
                    @php
                    $total = 0;
                    $grandtotal = 0;
                    $totalDebit = 0;
                    $totalCredit = 0;
                    @endphp
                    @foreach ($result['aktiva'] as $data)
                        @php
                            if($data['level'] != 1){
                                $total += $data['total'];
                                $totalDebit += $data['debit'];
                                $totalCredit += $data['credit'];
                            }
                        @endphp
                        @if ($data['total'] != 0)
                        <tr class="">
                            <td class="{{ $data['level'] == 1 ? 'fw-bold' : '' }} {{ $data['level'] == 2 ? 'ps-5' : '' }} {{ $data['level'] == 3 ? 'ps-3' : '' }}">
                                <a href='#' class='ledger' data-id="{{ $data['id'] }}">({{$data['coa_code']}}) {{$data['coa_name']}}</a>
                            </td>
                            @if($data['level'] == 1)
                                <td class="text-right {{ $data['level'] == 1 ? 'fw-bold' : '' }}">{{number_format($data['total'], '0', ',', '.')}}</td>
                                <td></td>
                            @else
                                <td></td>
                                <td class="text-right {{ $data['level'] == 1 ? 'fw-bold' : '' }}">{{number_format($data['total'], '0', ',', '.')}}</td>
                            @endif
                        </tr>
                        @endif

                        {{-- @if($data['coa_code'] == '16001')
                            <tr>
                                <th class="fw-bold border-top border-bottom border-dark">TOTAL ASSET LANCAR</th>
                                <th class="fw-bold border-top border-bottom border-dark text-right">{{ number_format($total, 0, ",", ".") }}</th>
                                <th class="fw-bold border-top border-bottom border-dark text-right">{{ number_format($total, 0, ",", ".") }}</th>
                            </tr>

                            <tr>
                                <td colspan="3"></td>
                            </tr>
                            <tr>
                                <td colspan="3"></td>
                            </tr>

                            @php
                                $grandtotal += $total;
                                $total = 0;
                            @endphp
                        @endif --}}
                    @endforeach

                    {{-- @php
                        $grandtotal += $total;
                    @endphp --}}

                    {{-- <tr>
                        <th class="fw-bold border-top border-bottom border-dark">TOTAL ASSET TETAP</th>
                        <th class="fw-bold border-top border-bottom border-dark text-right">{{ number_format($total, 0, ",", ".") }}</th>
                        <th class="fw-bold border-top border-bottom border-dark text-right">{{ number_format($total, 0, ",", ".") }}</th>
                    </tr>
                    <tr>
                        <td colspan="3"></td>
                    </tr> --}}
                    <tr>
                        <th class="fw-bold border-top border-bottom border-dark">TOTAL ASSET</th>
                        <th class="fw-bold border-top border-bottom border-dark text-right">{{ number_format($totalDebit, 0, ",", ".") }}</th>
                        <th class="fw-bold border-top border-bottom border-dark text-right">{{ number_format($totalCredit, 0, ",", ".") }}</th>

                    </tr>

                    <tr>
                        <td colspan="3"></td>
                    </tr>
                    <tr>
                        <td colspan="3"></td>
                    </tr>
                </table>
            </div>
            <div class="col-6 ">
                <div class="row  ">
                    <div colspan="3" class="fw-bold border-top border-bottom border-dark" style="font-size: 16px;">
                        <p>Pasiva </p>
                    </div>
                </div>
                <table style="width: 100%">
                    @php
                    $total = 0;
                    $grandtotal = 0;
                    $totalDebit = 0;
                    $totalCredit = 0;
                    @endphp
                    @foreach ($result['pasiva'] as $data)
                        @php
                            if($data['level'] != 1){
                                $total += $data['total'];
                                $totalDebit += $data['debit'];
                                $totalCredit += $data['credit'];
                            }
                        @endphp
                        @if($data['coa_code'] == '30000')
                            <tr>
                                <th class="fw-bold border-top border-bottom border-dark">TOTAL LIABILITAS</th>
                                <th class="fw-bold border-top border-bottom border-dark text-right">{{ number_format($total, 0, ",", ".") }}</th>
                                <th class="fw-bold border-top border-bottom border-dark text-right">{{ number_format($total, 0, ",", ".") }}</th>
                            </tr>

                            <tr>
                                <td colspan="3"></td>
                            </tr>
                            <tr>
                                <td colspan="3"></td>
                            </tr>

                            @php
                                $grandtotal += $total;
                                $total = 0;
                            @endphp
                        @endif
                        @if ($data['total'] != 0)
                        <tr class="">
                            <td class="{{ $data['level'] == 1 ? 'fw-bold' : '' }} {{ $data['level'] == 2 ? 'ps-5' : '' }} {{ $data['level'] == 3 ? 'ps-3' : '' }}">
                                {{-- ({{$data['coa_code']}}) {{$data['coa_name']}} --}}
                                <a href='#' class='ledger' data-id="{{ $data['id'] }}">({{$data['coa_code']}}) {{$data['coa_name']}}</a>
                            </td>
                            @if($data['level'] == 1)
                                <td class="text-right {{ $data['level'] == 1 ? 'fw-bold' : '' }}">{{number_format($data['total'], '0', ',', '.')}}</td>
                                <td></td>
                            @else
                                <td></td>
                                <td class="text-right {{ $data['level'] == 1 ? 'fw-bold' : '' }}">{{number_format($data['total'], '0', ',', '.')}}</td>
                            @endif
                        </tr>
                        @endif
                    @endforeach

                    @php
                        $grandtotal += $total;
                    @endphp

                    <tr>
                        <th class="fw-bold border-top border-bottom border-dark">TOTAL EQUITY</th>
                        <th class="fw-bold border-top border-bottom border-dark text-right">{{ number_format($total, 0, ",", ".") }}</th>
                        <th class="fw-bold border-top border-bottom border-dark text-right">{{ number_format($total, 0, ",", ".") }}</th>
                    </tr>
                    <tr>
                        <td colspan="3"></td>
                    </tr>
                    <tr>
                        <th class="fw-bold border-top border-bottom border-dark">TOTAL PASIVA</th>
                        <th class="fw-bold border-top border-bottom border-dark text-right">{{ number_format($grandtotal, 0, ",", ".") }}</th>
                        <th class="fw-bold border-top border-bottom border-dark text-right">{{ number_format($grandtotal, 0, ",", ".") }}</th>
                    </tr>

                    <tr>
                        <td colspan="3"></td>
                    </tr>
                    <tr>
                        <td colspan="3"></td>
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
