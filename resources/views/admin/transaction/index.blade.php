@extends('layouts.admin')

@section('title')
    <title>Transaksi</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Transaksi
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Transaksi
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <div class="content">
        @include('partials._success')
        @include('partials._error')
        <div class="block block-rounded">
            <div class="block-content">
                <div class="row mb-2">
                    <div class="col-sm-3 col-12">
                        <label class="form-label" for="start_date">Tanggal Treatment Mulai</label>
                        <input type="text" id="start_date" class="form-control js-flatpickr" data-date-format="d-m-Y" value="{{ $filterStartDate }}"/>
                    </div>
                    <div class="col-sm-3 col-12">
                        <label class="form-label" for="end_date">Tanggal Treatment Berakhir</label>
                        <input type="text" id="end_date" class="form-control js-flatpickr" data-date-format="d-m-Y" value="{{ $filterEndDate }}"/>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-9 col-12">
                        <button type="button" class="btn btn-primary" onclick="filterData();">Filter Data</button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive w-100" >
                            <table class="table table-bordered table-striped table-vcenter js-dataTable-full nowrap" id="table_data">
                                <thead>
                                <tr>
                                    <th style="width: 5%">Tindakan</th>
                                    <th style="width: 25%">Kode</th>
                                    <th>Dibuat Pada</th>
                                    <th>Tanggal Treatment</th>
                                    <th>Nama Customer</th>
                                    <th>No HP Customer</th>
                                    <th>Total</th>
                                    <th>Kembalian</th>
                                    <th>Status</th>
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
    <link rel="stylesheet" href="{{ asset('js/plugins/flatpickr/flatpickr.css') }}">
@endsection

@section('scripts')
    <!-- jQuery (required for DataTables plugin) -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>

    <!-- Page JS Plugins -->
    <script src="{{ asset('js/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-bs5/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('js/moment.min.js') }}"></script>
    <script src="{{ asset('js/plugins/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
    <script src="{{ asset('js/plugins/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('js/plugins/flatpickr/l10n/id.js') }}"></script>

    <script>
        flatpickr('.js-flatpickr', {
            "locale": "id"  // locale for this instance only
        });
        $(function() {
            $('#table_data').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                responsive: false,
                saveState: true,
                ajax: {
                    url: '{!! route('admin.transaction.datatable') !!}',
                    data: {
                        'employee_id': '{{ $filterEmployeeId }}',
                        'start_date': '{{ $filterStartDate }}',
                        'end_date': '{{ $filterEndDate }}',
                        'approve': '{{ $filterApprove }}'
                    }
                },
                sWrapper: "dataTables_wrapper dt-bootstrap5",
                sFilterInput: "form-control form-control-sm",
                sLengthSelect: "form-select form-select-sm",
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
                order: [
                    [2, 'desc']
                ],
                columns: [
                    { data: 'action', name: 'action', orderable: false, searchable: false, class: 'text-center'},
                    { data: 'code', name: 'transaction_invoices.code', class: 'text-center'},
                    { data: 'created_at', name: 'transaction_invoices.created_at', class: 'text-center', orderable: true, searchable: false,
                        render: function ( data, type, row ){
                            if ( type === 'display' || type === 'filter' ){
                                return moment(data).format('DD-MM-YYYY HH:mm');
                            }
                            return data;
                        }
                    },
                    { data: 'start_time', name: 'transaction_headers.start_time', class: 'text-center', orderable: true, searchable: false,
                        render: function ( data, type, row ){
                            if ( type === 'display' || type === 'filter' ){
                                return moment(data).format('DD-MM-YYYY HH:mm');
                            }
                            return data;
                        }
                    },
                    { data: 'member_name', name: 'transaction_invoices.member_name', class: 'text-center'},
                    { data: 'member_phone', name: 'transaction_invoices.member_phone', class: 'text-center'},
                    {
                        data: 'grandtotal',
                        name: 'transaction_invoices.grandtotal',
                        class: 'text-end',
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
                    {
                        data: 'return_value',
                        name: 'transaction_invoices.return_value',
                        class: 'text-end',
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
                    { data: 'status', name: 'statuses.display', class: 'text-center'},
                ]
            });
        });

        $.fn.dataTable.ext.search.push(
            function( settings, data, dataIndex ) {
                var exc = $('#exc').val();
                var vfw = data[2]; // use data for the vfw column
                var ref = new RegExp(exc, 'g');

                // VFW
                if (vfw.match(ref))
                {
                    return false;
                }

                return true;
            }
        );
    </script>
    <script>
        function filterData(){
            let startDate = $('#start_date').val();
            let endDate = $('#end_date').val();
            let employeeId = $('#filter_employee').val();
            let approve = $('#filter_approve').val();
            let url = '{{ route('admin.transaction.index') }}';

            window.location = url + '?employee_id=' + employeeId + '&approve=' + approve + '&start_date=' + startDate + '&end_date=' + endDate;
        }
    </script>
@endsection
