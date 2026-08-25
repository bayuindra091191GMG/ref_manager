@extends('layouts.admin')

@section('title')
    <title>Daftar Voucher Royalti/Poin</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Voucher Royalti/Poin
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Voucher Royalti/Poin
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <div class="content" id="vue-section">
        @include('partials._success')
        @include('partials._error')
        <div class="block block-rounded">
            <div class="block-content">
                <div class="row mb-3">
                    <div class="col-12 text-end">
                        <a href="{{ route('admin.coupon.create') }}" class="btn btn-success mt-lg-3"><i class="fas fa-plus"></i> Tambah</a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive w-100" >
                            <table class="table table-bordered table-striped table-vcenter" id="table_coupon">
                                <thead>
                                <tr>
                                    <th class="text-center" style="width: 12%">Aksi</th>
                                    <th class="text-center">Nama</th>
                                    {{-- <th class="text-center" style="width: 5%">For Sell?</th> --}}
                                    <th class="text-center" style="width: 10%">Diskon Flat</th>
                                    <th class="text-center" style="width: 10%">Diskon %</th>
                                    <th class="text-center" style="width: 5%">Free of Charge?</th>
                                    <th class="text-center" style="width: 5%">Unlimited Use?</th>
                                    <th class="text-center" style="width: 5%">Free Treatment?</th>
                                    <th class="text-center" style="width: 5%">Voucher Type</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center" style="width: 10%">Dibuat Oleh</th>
                                    <th class="text-center">Tanggal Dibuat</th>
                                </tr>
                                </thead>
                                <tbody>
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
        $(function() {
            $('#table_coupon').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                responsive: false,
                saveState: true,
                ajax: {
                    url: '{!! route('admin.coupon.datatable') !!}'
                },
                sWrapper: "dataTables_wrapper dt-bootstrap5",
                sFilterInput: "form-control form-control-sm",
                sLengthSelect: "form-select form-select-sm",
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
                order: [ [1, 'desc'] ],
                columns: [
                    { data: 'action', name: 'action', orderable: false, searchable: false, class: 'text-center'},
                    { data: 'code', name: 'code', class: "text-center"},
                    // { data: 'is_for_sell', name: 'is_for_sell', class: 'text-center'},
                    { data: 'discount_amount', name: 'discount_amount', class: 'text-center',
                        render: function (data, type, row) {
                            if (type === 'display' || type === 'filter') {
                                if(!data) return 0;
                                data = parseInt(data);
                                return data.toLocaleString(
                                    "de-DE"
                                );
                            }
                            return data;
                        },
                    },
                    { data: 'discount_percentage', name: 'discount_percentage', class: 'text-center',
                        render: function (data, type, row) {
                            if (type === 'display' || type === 'filter') {
                                if(!data) return 0;
                                return parseInt(data);
                            }
                            return data;
                        },
                    },
                    { data: 'is_foc', name: 'is_foc', class: 'text-center'},
                    { data: 'is_unlimited', name: 'is_unlimited', class: 'text-center'},
                    { data: 'is_free_treatment', name: 'is_free_treatment', class: 'text-center'},
                    { data: 'can_redeem', name: 'can_redeem', class: 'text-center'},
                    { data: 'status', name: 'status', class: 'text-center'},
                    { data: 'created_by', name: 'admin_users.name', orderable: false, searchable: false, class: 'text-center'},
                    { data: 'created_at', name: 'created_at', class: 'text-center', orderable: true, searchable: false,
                        render: function ( data, type, row ){
                            if ( type === 'display' || type === 'filter' ){
                                return moment(data).format('DD-MM-YYYY');
                            }
                            return data;
                        }
                    },
                ]
            });
        });
    </script>
@endsection
