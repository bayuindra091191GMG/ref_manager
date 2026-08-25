@extends('layouts.admin')

@section('title')
    <title>Laporan Rekap Transaksi Today, MTD, YTD</title>
@endsection

@section('content')
    <!-- Hero -->
    <div class="print_hide">
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Laporan Rekap Transaksi Today, MTD, YTD
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Laporan Rekap Transaksi Today, MTD, YTD
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <div class="content print_div">
        <div class="block block-rounded">
            <div class="block-content">
                <div class="row">
                    <div class="12">
                        <h4>Laporan Transaksi Today, MTD, YTD</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <span>{{ $startDate }}</span>
                    </div>
                    <div class="col-6 text-end">
                        <button type="button" id="btn_print" class="btn btn-outline-info">Print</button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <h4>Revenue Summary</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <table class="table table-bordered table-striped table-vcenter js-dataTable-full nowrap">
                            <tr>
                                <td>Category</td>
                                <td>Today</td>
                                <td>MTD</td>
                                <td>YTD</td>
                            </tr>
                            <tr>
                                <td>Room Revenue</td>
                                <td style="font-weight: bold;">Rp{{ number_format($roomRevenue['today'], '2', ',', '.') }}</td>
                                <td style="font-weight: bold;">Rp{{ number_format($roomRevenue['mtdRoom'], '2', ',', '.') }}</td>
                                <td style="font-weight: bold;">Rp{{ number_format($roomRevenue['ytdRoom'], '2', ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>AVG Room Rate</td>
                                <td style="font-weight: bold;">Rp{{ number_format($avgRate['today'], '2', ',', '.') }}</td>
                                <td style="font-weight: bold;">Rp{{ number_format($avgRate['mtd'], '2', ',', '.') }}</td>
                                <td style="font-weight: bold;">Rp{{ number_format($avgRate['ytd'], '2', ',', '.') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <h4>Detail Revenue</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <table class="table table-bordered table-striped table-vcenter js-dataTable-full nowrap">
                            <thead>
                            <tr>
                                <td>No</td>
                                <td>Keterangan</td>
                                <td>Today</td>
                                <td>MTD</td>
                                <td>YTD</td>
                            </tr>
                            </thead>
                            <tbody>
                            @php($no = 1)
                            @php($total = 0)
                            @php($totalMtd = 0)
                            @php($totalYtd = 0)
                            @foreach($sales as $sale)
                                @if ($sale['name'] != 'FOC' && $sale['name'] != 'Diskon')
                                    @php($total += $sale['daily'][0]->total)
                                    @php($totalMtd += $sale['mtd'][0]->total)
                                    @php($totalYtd += $sale['ytd'][0]->total)
                                @else
                                    @php($total -= $sale['daily'][0]->total)
                                    @php($totalMtd -= $sale['mtd'][0]->total)
                                    @php($totalYtd -= $sale['ytd'][0]->total)
                                @endif
                                <tr>
                                    <td>{{ $no++ }}</td>
                                    <td>{{ $sale['name'] }}</td>
                                    @if($sale['name'] == 'FOC' || $sale['name'] == 'Diskon')
                                        <td>-{{ number_format($sale['daily'][0]->total, '2', ',', '.') }}</td>
                                        @else
                                        <td>{{ number_format($sale['daily'][0]->total, '2', ',', '.') }}</td>
                                    @endif
                                    @if($sale['name'] == 'FOC' || $sale['name'] == 'Diskon')
                                        <td>-{{ number_format($sale['mtd'][0]->total, '2', ',', '.') }}</td>
                                        @else
                                        <td>{{ number_format($sale['mtd'][0]->total, '2', ',', '.') }}</td>
                                    @endif
                                    @if($sale['name'] == 'FOC' || $sale['name'] == 'Diskon')
                                        <td>-{{ number_format($sale['ytd'][0]->total, '2', ',', '.') }}</td>
                                        @else
                                        <td>{{ number_format($sale['ytd'][0]->total, '2', ',', '.') }}</td>
                                    @endif
                                </tr>
                            @endforeach
                            @php($no++)
                            <tr>
                                <td colspan="2" style="font-weight: bold; font-size: 18px;">Total Sales</td>
                                <td class="fw-bold">{{ number_format($total, '2', ',', '.') }}</td>
                                <td class="fw-bold">{{ number_format($totalMtd, '2', ',', '.') }}</td>
                                <td class="fw-bold">{{ number_format($totalYtd, '2', ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>{{ $discount['name'] }}</td>
                                <td>-{{ number_format($discount['daily'][0]->total, '2', ',', '.') }}</td>
                                <td>-{{ number_format($discount['mtd'][0]->total, '2', ',', '.') }}</td>
                                <td>-{{ number_format($discount['ytd'][0]->total, '2', ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td colspan="2" style="font-weight: bold; font-size: 18px;">Grand Total</td>
                                <td class="fw-bold">{{ number_format($total - ($discount['daily'][0]->total), '2', ',', '.') }}</td>
                                <td class="fw-bold">{{ number_format($totalMtd - ($discount['mtd'][0]->total), '2', ',', '.') }}</td>
                                <td class="fw-bold">{{ number_format($totalYtd - ($discount['ytd'][0]->total), '2', ',', '.') }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <h4>Pembayaran</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        @php($no = 1)
                        <table class="table table-bordered table-striped table-vcenter js-dataTable-full nowrap">
                            <thead>
                            <tr>
                                <td>No</td>
                                <td>Keterangan</td>
                                <td>Today</td>
                                <td>MTD</td>
                                <td>YTD</td>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($payments as $payment)
                                <tr>
                                    <td>{{ $no++ }}</td>
                                    <td>{{ $payment['name'] }}</td>
                                    <td>Rp{{ number_format($payment['daily'][0]->total, '2', ',', '.') }}</td>
                                    <td>Rp{{ number_format($payment['mtd'][0]->total, '2', ',', '.') }}</td>
                                    <td>Rp{{ number_format($payment['ytd'][0]->total, '2', ',', '.') }}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td style="font-weight: bold; font-size: 18px;" colspan="2" class="text-right">Pendapatan</td>
                                <td class="fw-bold">Rp{{ number_format($totalPayment['daily'], '2', ',', '.') }}</td>
                                <td class="fw-bold">Rp{{ number_format($totalPayment['mtd'], '2', ',', '.') }}</td>
                                <td class="fw-bold">Rp{{ number_format($totalPayment['ytd'], '2', ',', '.') }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <!-- Page JS Plugins CSS -->
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-bs5/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-buttons-bs5/buttons.bootstrap5.min.css') }}">
    <style>
        @media print {
            .print_div{
                display: block;
            }

            .print_hide {
                display: none;
            }
        }
    </style>
@endsection

@section('scripts')
    <!-- jQuery (required for DataTables plugin) -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>

    <!-- Page JS Plugins -->
    <script src="{{ asset('js/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-bs5/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('js/moment.min.js') }}"></script>
    <script src="{{ asset('js/plugins/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
    <script>
        $(document).on('click', '#btn_print', function(){
            $('#btn_print').hide();
            window.print();
        });
        window.onafterprint = function(){
            $('#btn_print').show();
            $('.print_hide').show();
        }
    </script>
@endsection
