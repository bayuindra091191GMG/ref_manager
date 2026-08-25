@extends('layouts.admin')

@section('title')
    <title>Riwayat Point User</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Riwayat Point User
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Riwayat Point User
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
{{--                <div class="row mb-2">--}}
{{--                    <div class="col-sm-3 col-12">--}}
{{--                        <label class="form-label" for="start_date">Tanggal Mulai</label>--}}
{{--                        <input type="text" id="start_date" class="form-control js-flatpickr" data-date-format="d-m-Y" value="{{ $filterStartDate }}"/>--}}
{{--                    </div>--}}
{{--                    <div class="col-sm-3 col-12">--}}
{{--                        <label class="form-label" for="end_date">Tanggal Berakhir</label>--}}
{{--                        <input type="text" id="end_date" class="form-control js-flatpickr" data-date-format="d-m-Y" value="{{ $filterEndDate }}"/>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="row mb-2">--}}
{{--                    <div class="col-sm-9 col-12">--}}
{{--                        <button type="button" class="btn btn-primary" onclick="filterData();">Filter Data</button>--}}
{{--                    </div>--}}
{{--                </div>--}}
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive w-100" >
                            <table class="table table-bordered table-striped table-vcenter js-dataTable-full nowrap" id="table_data">
                                <thead>
                                <tr>
{{--                                    <th style="width: 5%">Tindakan</th>--}}
                                    <th style="width: 25%">Nama</th>
                                    <th>No Telepon</th>
                                    <th>Tambah/Kurang Poin</th>
                                    <th>Total Poin</th>
                                    <th>Referensi</th>
                                    <th>Tanggal Pemakaian</th>
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
                    url: '{!! route('admin.report.user_point_history.datatable') !!}',
                    {{--data: {--}}
                    {{--    'employee_id': '{{ $filterEmployeeId }}',--}}
                    {{--    'start_date': '{{ $filterStartDate }}',--}}
                    {{--    'end_date': '{{ $filterEndDate }}',--}}
                    {{--    'approve': '{{ $filterApprove }}'--}}
                    {{--}--}}
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
                    [3, 'desc']
                ],
                columns: [
                    { data: 'user_name', name: 'user_name', class: 'text-center'},
                    { data: 'user_phone', name: 'user_phone', class: 'text-center'},
                    {
                        data: 'amount_add',
                        name: 'amount_add',
                        class: 'text-center',
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row) {
                            if (type === 'display' || type === 'filter') {
                                let amount = row.amount_add ? parseInt(row.amount_add) : 0;
                                let amountSub = row.amount_sub ? parseInt(row.amount_sub) : 0;
                                let formattedAmount, colorClass;

                                if (amount > 0) {
                                    formattedAmount = `+${amount.toLocaleString("de-DE")}`;
                                    colorClass = 'text-success'; // Green for positive values
                                } else if (amountSub > 0) {
                                    formattedAmount = `-${amountSub.toLocaleString("de-DE")}`;
                                    colorClass = 'text-danger'; // Red for negative values
                                } else {
                                    formattedAmount = '0';
                                    colorClass = ''; // Default, no styling
                                }

                                return `<span class="${colorClass}">${formattedAmount}</span>`;
                            }
                            return data;
                        }
                    },
                    {
                        data: 'amount_result',
                        name: 'amount_result',
                        class: 'text-center',
                        orderable: false,
                        searchable: false,
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
                    { data: 'reference_1', name: 'reference_1', orderable: false, searchable: false, class: 'text-center'},
                    { data: 'created_at', name: 'created_at', class: 'text-center', orderable: true, searchable: false,
                        render: function ( data, type, row ){
                            if ( type === 'display' || type === 'filter' ){
                                return moment(data).format('DD-MM-YYYY HH:mm');
                            }
                            return data;
                        }
                    },
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
