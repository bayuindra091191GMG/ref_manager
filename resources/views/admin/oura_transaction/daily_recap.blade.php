@extends('layouts.admin')

@section('title')
    <title>Laporan Rekap Transaksi</title>
@endsection

@section('content')
    <!-- Hero -->
    <div class="bg-body-light print_hide">
        <div class="content content-full">
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
                        <h4>Total Pemasukan</h4>
                    </div>
                    <div class="col-6">
                        <h4>Customers</h4>
                    </div>
                </div>
                <div class="row">
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
                                <td style="font-weight: bold;">Pendapatan Kotor</td>
                                <td style="font-weight: bold;">Rp{{ $totalBrutoStr }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold;">Diskon</td>
                                <td style="font-weight: bold;">Rp{{ $totalDiscountStr }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold;">Pendapatan Bersih</td>
                                <td style="font-weight: bold;">Rp{{ $totalStr }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-6">
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
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <h4>Penggunaan Voucher dan FOC</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <table class="table table-bordered table-striped table-vcenter js-dataTable-full nowrap">
                            <tbody>
                            <tr>
                                <td>No</td>
                                <td>No Transaksi</td>
                                <td>Voucher Code</td>
                                <td>FOC</td>
                                <td>FOC Name</td>
                                <td>Amount</td>
                            </tr>
                            @if(count($discounts) > 0)
                                @foreach($discounts as $discount)
                                    <tr>
                                        <td>{{ $discount['no'] }}</td>
                                        <td>{{ $discount['trx_number'] }}</td>
                                        <td>
                                            @if($discount['foc_type'] == '' || $discount['foc_type'] == 'no')
                                                {{ $discount['voucher_code'] }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($discount['foc_type'] == '' || $discount['foc_type'] == 'no')
                                                -
                                            @else
                                                {{ $discount['foc_type'] }}
                                            @endif
                                        </td>
                                        <td>
                                            @if($discount['foc_type'] == '' || $discount['foc_type'] == 'no')
                                                -
                                            @else
                                                {{ $discount['foc_name'] }}
                                            @endif
                                        </td>
                                        <td>Rp{{ number_format($discount['amount'], '2', ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <td colspan="6">Tidak ada penggunaan Diskon/FOC</td>
                            @endif
                            <tr>
                                <td colspan="5" class="text-end"><b>Total</b></td>
                                <td><b>Rp{{ $totalDiscountStr }}</b></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    @foreach($allRooms as $room)
                        <div class="col-6">
                            <h4>{{ $room['name'] }}</h4>
                            <table class="table table-bordered table-striped table-vcenter js-dataTable-full nowrap">
                                <tbody>
                                @foreach($room['packages'] as $tmpPackage)
                                    <tr>
                                        <td>{{ $tmpPackage['name'] }}</td>
                                        <td>{{ $tmpPackage['qty'] }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                </div>
                @if(count($commTheraResult) > 0)
                    <div class="row">
                        <div class="col-12">
                            <h4>Penjualan Therapis</h4>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <table class="table table-bordered table-striped table-vcenter js-dataTable-full nowrap">
                                <thead>
                                <tr>
                                    <th></th>
                                    @foreach($partnerData as $partner)
                                        <th>{{ $partner['name'] }}</th>
                                    @endforeach
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($commTheraResult as $item)
                                    <tr>
                                        <td>{{ $item['item_name'] }} ({{ $item['total'] }})</td>
                                        @foreach($item['partners'] as $emp)
                                            <td>{{ $emp['qty'] }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
                <div class="row">
                    <div class="col-12">
                        <h4>Penjualan item</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <table class="table table-bordered table-striped table-vcenter js-dataTable-full nowrap">
                            <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Item</th>
                                <th>Qty Penjualan</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($items as $item)
                                <tr>
                                    <td>{{ $item['no'] }}</td>
                                    <td>{{ $item['name'] }}</td>
                                    <td>{{ $item['qty'] }}</td>
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
