<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>GL Profit & Loss</title>
    <link rel="stylesheet" id="css-main" href="{{ asset('css/oneui.min.css')}}">
    <style>
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
            min-width: 80px;
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
        .col15-head{
            min-width: 200px;
        }

        @media print {
            .px-10{
                padding-left: 0 !important;
                padding-right: 0 !important;
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
                min-width: 55px;
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
            /* border: 2px solid black; */
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
        .ps-10 {
            padding-left: 6rem !important;
        }
        .ps-15 {
            padding-left: 9rem !important;
        }
        .ps-20 {
            padding-left: 12rem !important;
        }
        .ps-25 {
            padding-left: 15rem !important;
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
                /* border: 1px solid black !important; */
            }

            #non-print {
                display: none;
            }
        }
    </style>
</head>
<body>
@php
    if (count($range) <= 4){
        $fontSize = 'font-size: 12px;';
        $fontSizeTotal = 'font-size: 13px;';
    }
    else if(count($range) > 4 && count($range) <= 6){
        $fontSize = 'font-size: 10px;';
        $fontSizeTotal = 'font-size: 11px;';
    }
    else if(count($range) > 6 && count($range) <= 8){
        $fontSize = 'font-size: 9px;';
        $fontSizeTotal = 'font-size: 10px;';
    }
    else if(count($range) > 8 && count($range) <= 10){
        $fontSize = 'font-size: 8px;';
        $fontSizeTotal = 'font-size: 9px;';
    }
    else if(count($range) > 10){
        $fontSize = 'font-size: 7px;';
        $fontSizeTotal = 'font-size: 8px;';
    }
    $child = 0;

    $months = [
        '1' => 'Jan',
        '2' => 'Feb',
        '3' => 'Mar',
        '4' => 'Apr',
        '5' => 'May',
        '6' => 'Jun',
        '7' => 'Jul',
        '8' => 'Aug',
        '9' => 'Sept',
        '10' => 'Oct',
        '11' => 'Nov',
        '12' => 'Dec',
    ];

    function showChild($item, $range, $child): string{
        $result = '';
        $baseChild = $child;
        foreach ($item as $data){
            $child = $baseChild;

            $bold = "";
            if($data['total'] == 0 && array_key_exists('child', $data)){
                $bold = "fw-bold";
            }

            $result .= '<tr>';
            $result .= "<th class='ps-". ($baseChild+5) ." ". $bold ."'><a href='#' class='ledger' data-id='". $data['id'] ."'>(". $data['code'] . ") " .$data['name'] ."</a></th>";

            if($data['total'] == 0 && array_key_exists('child', $data)){
                $result .= "<th colspan=". (count($range)+1) ."></th>";
            }
            else {
                foreach ($data['data'] as $i => $total){
                    $result .= '<th>Rp. '. number_format($total["total"], 0, ",", ".") .'</th>';
                }
                $result .= "<th>Rp. ". number_format($data['total'], 0, ".", ",") ."</th>";
            }


            $result .= '</tr>';

            if(array_key_exists('child', $data)){
                $child += 5;
                $result .= showChild($data['child'], $range, $child);
            }
        }

        return $result;
    }
@endphp
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
                <p class="text-center font-weight-bold" style="font-size: 18px;">Profit and Loss Reports</p>
                <p class="text-center font-weight-bold" style="font-size: 16px;">{{$branch->name}}</p>
                <p class="text-center font-weight-bold" style="font-size: 12px;">Period:
                     {{$start_date}}
                    To
                     {{$end_date}}
                </p>
            </div>
            <div class="col-2">
                <p>Print Date: {{\Carbon\Carbon::now('Asia/Jakarta')}}</p>
            </div>
        </div>
    </div>
</section>

<section class=" pt-3">
    <div class="container-fluid" style="{{ $fontSize }}">
        <div class="row pt-2">
            <div class="col-12 ">
                <table style="width: 100%;">
                    <tr class="fw-bold p-rl-0">
                        <th class="col15-head"></th>
                        @foreach($range as $m)
                            <th class="col5-head text-center">{{ $months[$m] }}-{{$year}}</th>
                        @endforeach
                        <th class="col5-head">Total</th>
                    </tr>

                    @php
                    $currentType = 4;
                    @endphp

                    @foreach($result as $data)
                        {{-- @if($data['type_id'] != 4 && $currentType == 4)
                            @php
                                $currentType = 5;
                                $totalIncome = 0;
                            @endphp
                            <tr>
                                <th class="fw-bold border-top border-bottom border-dark" style="{{ $fontSizeTotal }}">TOTAL INCOME</th>
                                @foreach($extraTotal['income'] as $total)
                                    <th class="border-top border-bottom border-dark fw-bold">Rp. {{ number_format($total, 0, ",", ".") }}</th>
                                    @php
                                        $totalIncome += $total;
                                    @endphp
                                @endforeach
                                <th class="border-top border-bottom border-dark fw-bold">Rp. {{ number_format($totalIncome, 0, ".", ",") }}</th>
                            </tr>
                            <tr>
                                <td colspan="{{ (count($range)+2) }}"></td>
                            </tr>
                            <tr>
                                <td colspan="{{ (count($range)+2) }}"></td>
                            </tr>
                        @endif --}}

                        @php
                            $child = 0;
                            $grandTotalHeader = 0;
                        @endphp
                        <tr>
                            <th class="fw-bold"><a href='#' class='ledger' data-id='{{ $data['id'] }}'>({{ $data['code'] }}) {{ $data['name'] }}</a></th>
                            @if($data['total'] == 0 && array_key_exists('child', $data))
                                <th colspan="{{ (count($range)+1) }}"></th>
                            @else
                                @foreach($data['data'] as $i => $total)
                                    <th>Rp. {{ number_format($total['total'], 0, ",", ".") }}</th>
                                @endforeach
                                <th>Rp. {{ number_format($data['total'], 0, ".", ",") }}</th>
                            @endif
                        </tr>
                        @if(array_key_exists('child', $data))
                            @php
                                echo showChild($data['child'], $range, $child);
                            @endphp
                        @endif
                        <tr>
                            <th class="fw-bold border-top border-bottom border-dark" style="{{ $fontSizeTotal }}">Total {{ $data['name'] }}</th>
                            @foreach($data['header_total'] as $total)
                                <th class="border-top border-bottom border-dark">Rp. {{ number_format($total, 0, ",", ".") }}</th>
                                @php
                                    $grandTotalHeader += $total;
                                @endphp
                            @endforeach
                            <th class="border-top border-bottom border-dark">Rp. {{ number_format($grandTotalHeader, 0, ".", ",") }}</th>
                        </tr>
                        <tr>
                            <td colspan="{{ (count($range)+2) }}"></td>
                        </tr>
                        <tr>
                            <td colspan="{{ (count($range)+2) }}"></td>
                        </tr>
                    @endforeach
                    @php
                        $totalExpense = 0;
                        $totalNett = 0;
                    @endphp
                    <tr>
                        <th class="fw-bold border-top border-bottom border-dark" style="{{ $fontSizeTotal }}">TOTAL EXPENSE</th>
                        @foreach($extraTotal['expense'] as $total)
                            <th class="border-top border-bottom border-dark fw-bold">Rp. {{ number_format($total, 0, ",", ".") }}</th>
                            @php
                                $totalExpense += $total;
                            @endphp
                        @endforeach
                        <th class="border-top border-bottom border-dark fw-bold">Rp. {{ number_format($totalExpense, 0, ".", ",") }}</th>
                    </tr>
                    <tr>
                        <td colspan="{{ (count($range)+2) }}"></td>
                    </tr>
                    <tr>
                        <th class="fw-bold border-top border-bottom border-dark" style="{{ $fontSizeTotal }}">NETT PROFIT/LOSS </th>
                        @foreach($extraTotal['nett'] as $total)
                            <th class="border-top border-bottom border-dark fw-bold">Rp. {{ number_format($total, 0, ",", ".") }}</th>
                            @php
                                $totalNett += $total;
                            @endphp
                        @endforeach
                        <th class="border-top border-bottom border-dark fw-bold">Rp. {{ number_format($totalNett, 0, ".", ",") }}</th>
                    </tr>
                    <tr>
                        <td colspan="{{ (count($range)+2) }}"></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</section>


<form id="form-ledger" method="POST" action="{{ route('admin.general_ledger.report.generate') }}" target="_blank">
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
