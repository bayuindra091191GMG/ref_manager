@extends('layouts.admin')

@section('title')
    <title>Laporan Transaksi Harian Per Payment</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Laporan Transaksi Harian Per Payment
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Laporan Transaksi Harian Per Payment
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <div class="content">
        <div class="block block-rounded">
            <div class="block-content">
                <div class="row">
                    <div class="col-12">
                        <h3>Laporan Transaksi Tipe {{ $type->code }}</h3>
                        <h6>{{ $start }} - {{ $end }}</h6>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive w-100" >
                            <table class="table table-bordered table-striped table-vcenter js-dataTable-full nowrap" id="table_data">
                                <thead>
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>No. Transaksi</th>
                                    <th class="text-center">Customer</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(count($result) == 0)
                                    <tr>
                                        <td colspan="4">Belum ada data</td>
                                    </tr>
                                @endif
                                @foreach($result as $payment)
                                    <tr>
                                        <td class="text-center">
                                            {{ $payment['no'] }}
                                        </td>
                                        <td>
                                            {{ $payment['code'] }}
                                        </td>
                                        <td class="text-center">
                                            {{ $payment['customers'] }}
                                        </td>
                                        <td class="text-end">
                                            {{ $payment['amount_str'] }}
                                        </td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Total</td>
                                    <td class="text-end fw-bold">{{ $total }}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="w-100" style="height: 400px"></div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <!-- Page JS Plugins CSS -->
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-bs5/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-buttons-bs5/buttons.bootstrap5.min.css') }}">
@endsection

@section('scripts')
    <!-- jQuery (required for DataTables plugin) -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>

    <!-- Page JS Plugins -->
    <script src="{{ asset('js/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-bs5/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('js/moment.min.js') }}"></script>
    <script src="{{ asset('js/plugins/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
@endsection
