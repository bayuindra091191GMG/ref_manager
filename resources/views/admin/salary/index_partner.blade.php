@extends('layouts.admin')

@section('title')
    <title>Daftar Gaji Therapis</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Daftar Gaji Therapis
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Daftar Gaji Therapis
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
                    <div class="col-sm-4 col-12">
                        <label class="form-label" for="filter_period">Filter Periode</label>
                        <input type="text" id="filter_period" class="form-control js-flatpickr" value="{{ $filterPeriod }}"/>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-9 col-12">
                        <button type="button" class="btn btn-primary" onclick="filterData();">Filter Data</button>
                        &nbsp;
                        <a href="{{ route('admin.salary.partner.index') }}" class="btn btn-primary">Reset Filter</a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <hr/>
                        <div class="table-responsive w-100">
                            <table class="table table-bordered table-striped table-vcenter nowrap" id="table_salary">
                                <thead>
                                <tr>
                                    <th class="text-center">Tindakan</th>
                                    <th class="text-center">Nama Therapis</th>
                                    <th class="text-center">Kode Therapis</th>
                                    <th class="text-center">Nomor Therapis</th>
                                    <th class="text-center">Alias Therapis</th>
                                    <th class="text-center">Total Komisi</th>
                                    <th class="text-center">Total Pinalti</th>
                                    <th class="text-center">Lain-lain</th>
                                    <th class="text-center">Total Gaji Akhir</th>
                                    <th class="text-center">Dibuat Pada</th>
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
    <link rel="stylesheet" href="{{ asset('js/plugins/flatpickr/plugins/monthSelect/style.css') }}">
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
    <script src="{{ asset('js/plugins/flatpickr/plugins/monthSelect/index.js') }}"></script>
    <script src="{{ asset('js/plugins/flatpickr/l10n/id.js') }}"></script>

    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script>
        $(function() {
            $('#table_salary').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                responsive: false,
                saveState: true,
                ajax: {
                    url: '{!! route('admin.salary.partner.datatable') !!}',
                    data: {
                        'filter_period': '{{ $filterPeriod }}',
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
                order: [ [1, 'asc'] ],
                columns: [
                    { data: 'action', name: 'action', orderable: false, searchable: false, class: 'text-center'},
                    { data: 'partner_name', name: 'partner_name'},
                    { data: 'partner_code', name: 'partner_code'},
                    { data: 'partner_number', name: 'partner_number', class: 'text-center'},
                    { data: 'partner_alias', name: 'partner_alias', class: 'text-center'},
                    { data: 'commission_amount', name: 'commission_amount', class: 'text-end',
                        render: function ( data, type, row ){
                            if ( type === 'display' || type === 'filter' ){
                                data = parseFloat(data);
                                return data.toLocaleString(
                                    "de-DE"
                                );
                            }
                            return data;
                        }
                    },
                    { data: 'penalty_amount', name: 'penalty_amount', class: 'text-end',
                        render: function ( data, type, row ){
                            if ( type === 'display' || type === 'filter' ){
                                data = parseFloat(data);
                                return data.toLocaleString(
                                    "de-DE"
                                );
                            }
                            return data;
                        }
                    },
                    { data: 'other_amount', name: 'other_amount', class: 'text-end',
                        render: function ( data, type, row ){
                            if ( type === 'display' || type === 'filter' ){
                                data = parseFloat(data);
                                return data.toLocaleString(
                                    "de-DE"
                                );
                            }
                            return data;
                        }
                    },
                    { data: 'total_salary_amount', name: 'total_salary_amount', class: 'text-end',
                        render: function ( data, type, row ){
                            if ( type === 'display' || type === 'filter' ){
                                data = parseFloat(data);
                                return data.toLocaleString(
                                    "de-DE"
                                );
                            }
                            return data;
                        }
                    },
                    { data: 'created_at', name: 'created_at', class: 'text-center', orderable: true, searchable: false,
                        render: function ( data, type, row ){
                            if ( type === 'display' || type === 'filter' ){
                                return moment(data).format('DD MMM YYYY HH:mm');
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

        function filterData(){
            let period = $('#filter_period').val();
            let url = '{{ route('admin.salary.partner.index') }}';

            window.location = url + '?filter_period=' + period;
        }

        flatpickr('.js-flatpickr', {
            "locale": "id", // locale for this instance only
            "plugins": [
                new monthSelectPlugin({
                    shorthand: true, //defaults to false
                    altInput: true,
                    dateFormat: "m-Y", //defaults to "F Y"
                    altFormat: "F Y", //defaults to "F Y"
                    theme: "dark" // defaults to "light"
                })
            ]
        });

    </script>
@endsection
