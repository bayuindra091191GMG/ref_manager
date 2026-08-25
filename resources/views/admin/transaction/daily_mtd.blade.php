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
                        <span>{{ $startDate }} - {{ $endDate }} / {{ $diffDay }} hari</span>
                    </div>
                    <div class="col-6 text-end">
                        <button type="button" id="btn_print" class="btn btn-outline-info">Print</button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <h4>Sales</h4>
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
                            @foreach($rooms as $room)
                                <tr>
                                    <td>{{ $no++ }}</td>
                                    <td>{{ $room['name'] }}</td>
                                    <td>{{ number_format($room['today'], '2', ',', '.') }}</td>
                                    <td>{{ number_format($room['mtd'], '2', ',', '.') }}</td>
                                    <td>{{ number_format($room['ytd'], '2', ',', '.') }}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>Oil Massage</td>
                                <td>{{ number_format($items['oil_massage']['today'], '2', ',', '.') }}</td>
                                <td>{{ number_format($items['oil_massage']['mtd'], '2', ',', '.') }}</td>
                                <td>{{ number_format($items['oil_massage']['ytd'], '2', ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>Fasilitas</td>
                                <td>{{ number_format($items['facility']['today'], '2', ',', '.') }}</td>
                                <td>{{ number_format($items['facility']['mtd'], '2', ',', '.') }}</td>
                                <td>{{ number_format($items['facility']['ytd'], '2', ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>F&B</td>
                                <td>{{ number_format($items['fnb']['today'], '2', ',', '.') }}</td>
                                <td>{{ number_format($items['fnb']['mtd'], '2', ',', '.') }}</td>
                                <td>{{ number_format($items['fnb']['ytd'], '2', ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>Hotstone</td>
                                <td>{{ number_format($items['hotstone']['today'], '2', ',', '.') }}</td>
                                <td>{{ number_format($items['hotstone']['mtd'], '2', ',', '.') }}</td>
                                <td>{{ number_format($items['hotstone']['ytd'], '2', ',', '.') }}</td>
                            </tr>
                            @if($branchId != 1)
                                <tr>
                                    <td>{{ $no++ }}</td>
                                    <td>Scrub</td>
                                    <td>{{ number_format($items['scrub']['today'], '2', ',', '.') }}</td>
                                    <td>{{ number_format($items['scrub']['mtd'], '2', ',', '.') }}</td>
                                    <td>{{ number_format($items['scrub']['ytd'], '2', ',', '.') }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>Produk Kimo</td>
                                <td>{{ number_format($items['kimo']['today'], '2', ',', '.') }}</td>
                                <td>{{ number_format($items['kimo']['mtd'], '2', ',', '.') }}</td>
                                <td>{{ number_format($items['kimo']['ytd'], '2', ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>Diskon</td>
                                <td>-{{ number_format($items['discount']['today'], '2', ',', '.') }}</td>
                                <td>-{{ number_format($items['discount']['mtd'], '2', ',', '.') }}</td>
                                <td>-{{ number_format($items['discount']['ytd'], '2', ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>FOC</td>
                                <td>-{{ number_format($items['foc']['today'], '2', ',', '.') }}</td>
                                <td>-{{ number_format($items['foc']['mtd'], '2', ',', '.') }}</td>
                                <td>-{{ number_format($items['foc']['ytd'], '2', ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-right" colspan="2"><span style="font-size: 16px; font-weight: bold;">Total Sales</span></td>
                                <td>{{ number_format($totals['today'], '2', ',', '.') }}</td>
                                <td>{{ number_format($totals['mtd'], '2', ',', '.') }}</td>
                                <td>{{ number_format($totals['ytd'], '2', ',', '.') }}</td>
                            </tr>
                            @php($no++)
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
                                    <td>{{ $payment['payment'] }}</td>
                                    <td>Rp{{ number_format($payment['total'], '2', ',', '.') }}</td>
                                    <td>Rp{{ number_format($payment['totalMtd'], '2', ',', '.') }}</td>
                                    <td>Rp{{ number_format($payment['totalYtd'], '2', ',', '.') }}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td style="font-weight: bold;" colspan="2" class="text-right">Pendapatan</td>
                                <td style="font-weight: bold;">Rp{{ $totalStr }}</td>
                                <td style="font-weight: bold;">Rp{{ $totalMtdStr }}</td>
                                <td style="font-weight: bold;">Rp{{ $totalYtdStr }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <h4>Occupancy</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <table class="table table-bordered table-striped table-vcenter js-dataTable-full nowrap">
                            <thead>
                            <tr>
                                <td colspan="2">Today</td>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>Free</td>
                                <td>{{ $occToday['free'] }}</td>
                            </tr>
                            <tr>
                                <td>Today Room</td>
                                <td>{{ $occToday['room'] }}</td>
                            </tr>
                            <tr>
                                <td>Today Room Available</td>
                                <td>{{ $occToday['roomAvailable'] }}</td>
                            </tr>
                            <tr>
                                <td>Total OCC</td>
                                <td>{{ number_format($occToday['totalOcc'], '2', ',', '.') }}%</td>
                            </tr>
                            </tbody>
                        </table>
                        <table class="table table-bordered table-striped table-vcenter js-dataTable-full nowrap">
                            <thead>
                            <tr>
                                <td colspan="2">MTD</td>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>Free</td>
                                <td>{{ $occMtd['free'] }}</td>
                            </tr>
                            <tr>
                                <td>MTD Room</td>
                                <td>{{ $occMtd['room'] }}</td>
                            </tr>
                            <tr>
                                <td>MTD Room Available</td>
                                <td>{{ $occMtd['roomAvailable'] }}</td>
                            </tr>
                            <tr>
                                <td>MTD OCC</td>
                                <td>{{ number_format($occMtd['totalOcc'], '2', ',', '.') }}%</td>
                            </tr>
                            </tbody>
                        </table>
                        <table class="table table-bordered table-striped table-vcenter js-dataTable-full nowrap">
                            <thead>
                            <tr>
                                <td colspan="2">YTD</td>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>Free</td>
                                <td>{{ $occYtd['free'] }}</td>
                            </tr>
                            <tr>
                                <td>YTD Room</td>
                                <td>{{ $occYtd['room'] }}</td>
                            </tr>
                            <tr>
                                <td>YTD Room Available</td>
                                <td>{{ $occYtd['roomAvailable'] }}</td>
                            </tr>
                            <tr>
                                <td>YTD OCC</td>
                                <td>{{ number_format($occYtd['totalOcc'], '2', ',', '.') }}%</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <h4>Room Revenue</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <table class="table table-bordered table-striped table-vcenter js-dataTable-full nowrap">
                            <tbody>
                            <tr>
                                <td style="font-weight: bold;" colspan="2" class="text-right">Today</td>
                                <td style="font-weight: bold;">Rp{{ number_format($roomRevenue['today'], '2', ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold;" colspan="2" class="text-right">Today Discount</td>
                                <td style="font-weight: bold;">Rp{{ number_format($roomRevenue['discount'], '2', ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold;" colspan="2" class="text-right">MTD</td>
                                <td style="font-weight: bold;">Rp{{ number_format($roomRevenue['mtdRoom'], '2', ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold;" colspan="2" class="text-right">Mtd Discount</td>
                                <td style="font-weight: bold;">Rp{{ number_format($roomRevenue['mtdRoomDisc'], '2', ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold;" colspan="2" class="text-right">YTD</td>
                                <td style="font-weight: bold;">Rp{{ number_format($roomRevenue['ytdRoom'], '2', ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold;" colspan="2" class="text-right">YTD Discount</td>
                                <td style="font-weight: bold;">Rp{{ number_format($roomRevenue['ytdRoomDisc'], '2', ',', '.') }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <h4>Average Room Rate (ARR)</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <table class="table table-bordered table-striped table-vcenter js-dataTable-full nowrap">
                            <tbody>
                            <tr>
                                <td style="font-weight: bold;" colspan="2" class="text-right">Today</td>
                                <td style="font-weight: bold;">Rp{{ number_format($avgRate['today'], '2', ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold;" colspan="2" class="text-right">MTD</td>
                                <td style="font-weight: bold;">Rp{{ number_format($avgRate['mtd'], '2', ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold;" colspan="2" class="text-right">YTD</td>
                                <td style="font-weight: bold;">Rp{{ number_format($avgRate['ytd'], '2', ',', '.') }}</td>
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
