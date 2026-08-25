@extends('layouts.admin')

@section('title')
    <title>Laporan Rekap Transaksi</title>
@endsection

@section('content')
    <!-- Hero -->
    <div class="print_hide">
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Laporan Rekap Transaksi
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Laporan Rekap Transaksi
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
                        <h4>Laporan Transaksi</h4>
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
                    <div class="col-6">
                        <h4>Customers</h4>
                    </div>
                    <div class="col-6">
                        <h4>Total Pemasukan</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <table class="table table-bordered table-striped table-vcenter js-dataTable-full nowrap">
                            <tbody>
                            <tr>
                                <td>Total Tamu</td>
                                <td>{{ $totalCustomer }}</td>
                            </tr>
                            <tr>
                                <td>Treatment</td>
                                <td>{{ $totalTreatment }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-6">
                        <table class="table table-bordered table-striped table-vcenter js-dataTable-full nowrap">
                            <tbody>
                            @foreach($payments as $payment)
                                <tr>
                                    <td>{{ $payment['payment'] }}</td>
                                    <td>Rp{{ $payment['total_str'] }}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td style="font-weight: bold;">Pendapatan</td>
                                <td style="font-weight: bold;">Rp{{ $totalStr }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <h4>Treatments</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <table class="table table-bordered table-striped table-vcenter js-dataTable-full nowrap">
                            <tbody>
                            @foreach($allPackage as $package)
                                <tr>
                                    <td>{{ $package['name'] }}</td>
                                    <td>{{ $package['qty'] }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <h4>Total Penjualan Add On</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 col-12">
                        <table class="table table-bordered table-striped table-vcenter nowrap">
                            <thead>
                            <tr>
                                <th class="text-center">Nama Barang</th>
                                <th class="text-center">Kode Barang</th>
                                <th class="text-center">Kuantitas Terjual</th>
                                <th class="text-center">Pendapatan</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($allItem as $item)
                                <tr>
                                    <td>{{ $item['name'] }}</td>
                                    <td>{{ $item['code'] }}</td>
                                    <td class="text-end">{{ $item['qty'] }}</td>
                                    <td class="text-end">Rp{{ number_format($item['total_price'], 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
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
