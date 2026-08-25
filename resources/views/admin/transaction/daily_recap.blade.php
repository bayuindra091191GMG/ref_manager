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
                    <div class="col-12">
                        <h4>Customers</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <table class="table table-bordered table-striped table-vcenter js-dataTable-full nowrap">
                            <tbody>
                            <tr>
                                <td>Total Tamu</td>
                                <td>{{ $totalCustomer }}</td>
                            </tr>
                            <tr>
                                <td>Tamu FOC</td>
                                <td>{{ $totalFocCustomer }}</td>
                            </tr>
                            <tr>
                                <td>Tamu Reguler</td>
                                <td>{{ $totalRegularCustomer }}</td>
                            </tr>
                            <tr>
                                <td>Treatment</td>
                                <td>{{ $totalTreatment }}</td>
                            </tr>
                            <tr>
                                <td>Hanya Fasilitas</td>
                                <td>{{ $totalFacilityOnly }}</td>
                            </tr>
                            <tr>
                                <td>Paket Reflexiology 60 Menit</td>
                                <td>{{ $reflexiology['Paket Reflexiology 60 Menit'] }}</td>
                            </tr>
                            <tr>
                                <td>Paket Reflexiology 90 Menit</td>
                                <td>{{ $reflexiology['Paket Reflexiology 90 Menit'] }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <h4>Extra Therapis, Extra Athena (Tamu), & Extend Room</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <table class="table table-bordered table-striped table-vcenter js-dataTable-full nowrap">
                            <tbody>
                            <tr>
                                <td>Extra Therapis</td>
                                <td>{{ $extraPartner }}</td>
                            </tr>
                            <tr>
                                <td>Extra Athena (Tamu)</td>
                                <td>{{ $extraPerson }}</td>
                            </tr>
                            <tr>
                                <td>Extend</td>
                                <td>{{ $extendTimes }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <h4>Standard Room</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <table class="table table-bordered table-striped table-vcenter js-dataTable-full nowrap">
                            <tbody>
                            <tr>
                                <td>Royal Treatment 60"</td>
                                <td>{{ $standardRoom['Royal Treatment 60"'] }}</td>
                            </tr>
                            <tr>
                                <td>Royal Treatment 90"</td>
                                <td>{{ $standardRoom['Royal Treatment 90"'] }}</td>
                            </tr>
                            <tr>
                                <td>Royal Treatment 120"</td>
                                <td>{{ $standardRoom['Royal Treatment 120"'] }}</td>
                            </tr>
                            <tr>
                                <td>Caesar Treatment 60"</td>
                                <td>{{ $standardRoom['Caesar Treatment 60"'] }}</td>
                            </tr>
                            <tr>
                                <td>Caesar Treatment 90"</td>
                                <td>{{ $standardRoom['Caesar Treatment 90"'] }}</td>
                            </tr>
                            <tr>
                                <td>Caesar Treatment 120"</td>
                                <td>{{ $standardRoom['Caesar Treatment 120"'] }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <h4>VIP Room</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <table class="table table-bordered table-striped table-vcenter js-dataTable-full nowrap">
                            <tbody>
                            <tr>
                                <td>Aphrodite Treatment 90"</td>
                                <td>{{ $vipRoom['Aphrodite Treatment 90"'] }}</td>
                            </tr>
                            <tr>
                                <td>Aphrodite Treatment 120"</td>
                                <td>{{ $vipRoom['Aphrodite Treatment 120"'] }}</td>
                            </tr>
                            <tr>
                                <td>Kingsman Treatment 90"</td>
                                <td>{{ $vipRoom['Kingsman Treatment 90"'] }}</td>
                            </tr>
                            <tr>
                                <td>Kingsman Treatment 120"</td>
                                <td>{{ $vipRoom['Kingsman Treatment 120"'] }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <h4>VVIP Room</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <table class="table table-bordered table-striped table-vcenter js-dataTable-full nowrap">
                            <tbody>
                            <tr>
                                <td>Paket Athena 2</td>
                                <td>{{ $vvipRoom['Paket Athena 2'] }}</td>
                            </tr>
                            <tr>
                                <td>Paket Athena 3</td>
                                <td>{{ $vvipRoom['Paket Athena 3'] }}</td>
                            </tr>
                            <tr>
                                <td>Paket Athena 4</td>
                                <td>{{ $vvipRoom['Paket Athena 4'] }}</td>
                            </tr>
                            <tr>
                                <td>Paket Athena 5</td>
                                <td>{{ $vvipRoom['Paket Athena 5'] }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <h4>Penjualan per item per GRO</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <table class="table table-bordered table-striped table-vcenter js-dataTable-full nowrap">
                            <tbody>
                            @foreach($itemSalesEmp as $item)
                                <tr>
                                    <td style="font-weight: bold;">{{ $item['item_name'] }}</td>
                                    <td style="font-weight: bold;">{{ $item['total'] }}</td>
                                </tr>
                                @foreach($item['employees'] as $emp)
                                    <tr>
                                        <td>{{ $emp['employee_name'] }}</td>
                                        <td>{{ $emp['qty'] }}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <h4>Total Pemasukan</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
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
