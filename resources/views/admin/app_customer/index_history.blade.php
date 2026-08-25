@extends('layouts.admin')

@section('title')
    <title>Daftar Histori Poin Customer</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Daftar Histori Poin Customer
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Daftar Histori Poin Customer
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
            <div class="block-header block-header-default">
                {{--                <h3 class="block-title">Users</h3>--}}
            </div>
            <div class="block-content block-content-full">
                <div class="row mb-2">
                    <div class="col-sm-3 col-12">
                        <label class="form-label" for="start_date">Tanggal Mulai</label>
                        <input type="text" id="start_date" class="form-control js-flatpickr" data-date-format="d-m-Y" value="{{ $filterStartDate }}"/>
                    </div>
                    <div class="col-sm-3 col-12">
                        <label class="form-label" for="end_date">Tanggal Berakhir</label>
                        <input type="text" id="end_date" class="form-control js-flatpickr" data-date-format="d-m-Y" value="{{ $filterEndDate }}"/>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-3 col-12">
                        <label class="form-label" for="user">Customer</label>
                        <select class="form-select" id="user">
                            @if(!empty($filterUser))
                                <option value="{{ $filterUser->id }}">{{ $filterUser->name. ' ('. $filterUser->phone. ')' }}</option>
                            @endif
                        </select>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-9 col-12">
                        <button type="button" class="btn btn-primary" onclick="filterData();">Filter Data</button>
                        &nbsp;
                        <a href="{{ route('admin.point_history.index') }}" class="btn btn-secondary">Reset Filter</a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <hr/>
                        <div class="table-responsive w-100">
                            <table class="table table-bordered table-striped table-vcenter nowrap" id="table_data">
                                <thead>
                                <tr>
                                    {{-- <th class="text-center">Tindakan</th> --}}
                                    <th class="text-center">Tanggal dan Waktu</th>
                                    {{-- <th class="text-center">Cabang</th> --}}
                                    <th class="text-center">Nama Customer</th>
                                    <th class="text-center">No. Telp Customer</th>
                                    <th class="text-center">Poin Bertambah</th>
                                    <th class="text-center">Poin Berkurang</th>
                                    <th class="text-center">Poin Hasil</th>
                                    <th class="text-center">Referensi 1</th>
                                    <th class="text-center">Referensi 2</th>
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
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
@endsection

@section('scripts')
    <!-- jQuery (required for DataTables plugin) -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>

    <!-- Page JS Plugins -->
    <script src="{{ asset('js/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-bs5/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('js/moment.min.js') }}"></script>
    <script src="{{ asset('js/moment-id.min.js') }}"></script>
    <script src="{{ asset('js/plugins/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
    <script src="{{ asset('js/plugins/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('js/plugins/flatpickr/l10n/id.js') }}"></script>
    <script src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>

    <script>
        flatpickr('.js-flatpickr', {
            "locale": "id"  // locale for this instance only
        });

        var datatable = $('#table_data').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 100,
            responsive: false,
            ajax: {
                url: '{!! route('admin.point_history.datatable') !!}',
                data: {
                    'user_id': '{{ $filterUserId }}',
                    'start_date': '{{ $filterStartDate }}',
                    'end_date': '{{ $filterEndDate }}'
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
            order: [ [0, 'desc'] ],
            columns: [
                { data: 'created_at', name: 'created_at', class: 'text-center', orderable: true, searchable: false,
                    render: function ( data, type, row ){
                        if ( type === 'display' || type === 'filter' ){
                            return moment(data).format('DD-MM-YYYY HH:mm');
                        }
                        return data;
                    }
                },
                // { data: 'branch_name', name: 'branch_name' },
                { data: 'user_name', name: 'user_name' },
                { data: 'user_phone', name: 'user_phone' },
                { data: 'amount_add', name: 'amount_add', class: 'text-end',
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
                { data: 'amount_sub', name: 'amount_sub', class: 'text-end',
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
                { data: 'amount_result', name: 'amount_result', class: 'text-end',
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
                { data: 'reference_1', name: 'reference_1', class: 'text-center'},
                { data: 'reference_2', name: 'reference_2', class: 'text-center'},

            ]
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

        $('#user').select2({
            placeholder: {
                id: '-1',
                text: ' --- Pilih User --- '
            },
            width: '100%',
            minimumInputLength: 0,
            ajax: {
                url: '{{ route('admin.user.select') }}',
                dataType: 'json',
                data: function (params) {
                    return {
                        search: $.trim(params.term)
                    };
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                }
            }
        });

        function filterData(){
            let startDate = $('#start_date').val();
            let endDate = $('#end_date').val();
            let userId = $('#user').val();
            let url = '{{ route('admin.point_history.index') }}';

            window.location = url + '?user_id=' + userId + '&start_date=' + startDate + '&end_date=' + endDate;
        }
    </script>
@endsection
