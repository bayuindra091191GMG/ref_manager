@extends('layouts.admin')

@section('title')
    <title>Transaksi Voucher Aplikasi</title>
@endsection

@section('content')
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">Transaksi Voucher Aplikasi</h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">Transaksi Voucher Aplikasi</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="content">
        @include('partials._success')
        @include('partials._error')
        <div class="block block-rounded">
            <div class="block-content block-content-full">
                <div class="table-responsive w-100">
                    <table class="table table-bordered table-striped table-vcenter nowrap" id="table_voucher_orders">
                        <thead>
                        <tr>
                            <th style="width: 10%">Aksi</th>
                            <th>Kode</th>
                            <th>User ID</th>
                            <th>Status</th>
                            <th>Subtotal</th>
                            <th>PPN</th>
                            <th>Total Bayar</th>
                            <th>Bukti</th>
                            <th>Batas Bayar</th>
                            <th>Tanggal Dibuat</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-bs5/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-buttons-bs5/buttons.bootstrap5.min.css') }}">
@endsection

@section('scripts')
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-bs5/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('js/moment.min.js') }}"></script>
    <script src="{{ asset('js/moment-id.min.js') }}"></script>

    <script>
        $(function() {
            $('#table_voucher_orders').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 25,
                responsive: false,
                ajax: {
                    url: '{!! route('admin.voucher_order.datatable') !!}',
                },
                pagingType: 'full_numbers',
                language: {
                    lengthMenu: "_MENU_",
                    search: "_INPUT_",
                    searchPlaceholder: "Search keyword..",
                    info: "Page <strong>_PAGE_</strong> of <strong>_PAGES_</strong>",
                    paginate: {
                        first: '<i class="fa fa-angle-double-left"></i>',
                        previous: '<i class="fa fa-angle-left"></i>',
                        next: '<i class="fa fa-angle-right"></i>',
                        last: '<i class="fa fa-angle-double-right"></i>'
                    }
                },
                order: [[9, 'desc']],
                columns: [
                    { data: 'action', name: 'action', orderable: false, searchable: false, class: 'text-center' },
                    { data: 'code', name: 'order_voucher_headers.code', class: 'text-center' },
                    { data: 'created_by', name: 'order_voucher_headers.created_by', class: 'text-center' },
                    { data: 'status', name: 'statuses.display', class: 'text-center' },
                    {
                        data: 'grand_total',
                        name: 'order_voucher_headers.grand_total',
                        class: 'text-end',
                        render: $.fn.dataTable.render.number('.', ',', 0)
                    },
                    {
                        data: 'payment_charge_ppn_value',
                        name: 'order_voucher_headers.payment_charge_ppn_value',
                        class: 'text-end',
                        render: $.fn.dataTable.render.number('.', ',', 0)
                    },
                    {
                        data: 'total_payment',
                        name: 'order_voucher_headers.total_payment',
                        class: 'text-end',
                        render: $.fn.dataTable.render.number('.', ',', 0)
                    },
                    {
                        data: 'payment_proof',
                        name: 'order_voucher_headers.payment_proof',
                        class: 'text-center',
                        render: function(data) {
                            return data ? '<span class="badge bg-success">Ada</span>' : '<span class="badge bg-secondary">Belum Ada</span>';
                        }
                    },
                    {
                        data: 'payment_due_at',
                        name: 'order_voucher_headers.payment_due_at',
                        class: 'text-center',
                        render: function(data) {
                            return data ? moment(data).format('DD-MM-YYYY HH:mm') : '-';
                        }
                    },
                    {
                        data: 'created_at',
                        name: 'order_voucher_headers.created_at',
                        class: 'text-center',
                        render: function(data) {
                            return data ? moment(data).format('DD-MM-YYYY HH:mm') : '-';
                        }
                    },
                ]
            });
        });
    </script>
@endsection
