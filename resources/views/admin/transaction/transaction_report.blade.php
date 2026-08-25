@extends('layouts.admin')

@section('title')
    <title>Laporan Transaksi {{ $type }}</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Laporan Transaksi {{ $type }}
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3 print_hide" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Laporan Transaksi {{ $type }}
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
                        <div class="row print_hide">
                            <div class="col-12 text-end">
                                <button type="button" id="btn_print" class="btn btn-outline-info">Print</button>
{{--                                <a id="btn_cancel" href="{{ route('admin.purchase_order.index') }}" class="btn btn-outline-danger">Back</a>--}}
                            </div>
                        </div>
                        <div class="table-responsive w-100">
                            <table class="table table-bordered table-striped table-vcenter" style="font-size: 12px !important;">
                                <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kode</th>
                                    <th>Customer</th>
                                    <th>Tanggal</th>
                                    <th>Diskon</th>
                                    <th>Total</th>
                                    <th>Total {{ $type }}</th>
                                    <th>Tipe Pembayaran</th>
                                    <th>Total Pembayaran</th>
                                    <th>Dibuat oleh</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($result as $header)
                                    <tr>
                                        <td>{{ $header['no'] }}</td>
                                        <td>{{ $header['code'] }}</td>
                                        <td>{{ $header['customer'] }}</td>
                                        <td>{{ $header['date'] }}</td>
                                        <td>{{ $header['discount'] }}</td>
                                        <td>{{ $header['total'] }}</td>
                                        <td>{{ $header['total_grouping'] }}</td>
                                        <td>{{ $header['payment_code'] }}</td>
                                        <td>{{ $header['payment_amount'] }}</td>
                                        <td>{{ $header['created_by'] }}</td>
                                    </tr>
                                    <!-- Foreach Details and Payment also -->
                                    <tr>
                                        <td></td>
                                        <td>Details</td>
                                        <td colspan="10">
                                            <table class="table table-bordered">
                                                <thead>
                                                <tr>
                                                    <td>No</td>
                                                    <td>Item</td>
                                                    <td>Qty</td>
                                                    <td>Price</td>
                                                    <td>Subtotal</td>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($header['details'] as $detail)
                                                    <tr>
                                                        <td>{{ $detail['no'] }}</td>
                                                        <td>{{ $detail['item_name'] }}</td>
                                                        <td>{{ $detail['qty'] }}</td>
                                                        <td>{{ $detail['price'] }}</td>
                                                        <td>{{ $detail['subtotal'] }}</td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
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
            .print_show{
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
        $(document).ready(function () {
            $(window).keydown(function (event) {
                if (event.keyCode === 13) {
                    event.preventDefault();
                    return false;
                }
            });
        });

        $(document).on('click', '#btn_print', function(){
            $('.print_show').show();
            window.print();
        });
        window.onafterprint = function(){
            $('.print_show').hide();
        }
    </script>
@endsection
