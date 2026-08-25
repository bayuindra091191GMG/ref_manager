@extends('layouts.admin')

@section('title')
    <title>Daftar Absensi</title>
@endsection

@php
    $typeId = request()->query('type_id', 0);
    $stateId = request()->query('state_id', 0);
@endphp

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Daftar Absensi
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Daftar Absensi
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
                        <label class="form-label" for="start_date">Tanggal Mulai</label>
                        <input type="text" id="start_date" class="form-control js-flatpickr" data-date-format="d-m-Y"
                            value="{{ $filterStartDate }}" />
                    </div>
                    <div class="col-sm-3 col-12">
                        <label class="form-label" for="end_date">Tanggal Berakhir</label>
                        <input type="text" id="end_date" class="form-control js-flatpickr" data-date-format="d-m-Y"
                            value="{{ $filterEndDate }}" />
                    </div>
                    <div class="col-sm-3 col-12">
                        <label class="form-label" for="end_date">Type</label>
                        <select name="type_id" id="type_id" class="form-select">
                            <option value="0" @if ($typeId == 0) selected @endif>Semua</option>
                            @foreach ($typeIds as $type)
                                <option value="{{ $type }}" @if ($typeId == $type) selected @endif>
                                    @if($type == 1) Masuk @else Keluar @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    {{-- <div class="col-sm-3 col-12">
                        <label class="form-label" for="end_date">State</label>
                        <select name="state_id" id="state_id" class="form-select">
                            <option value="0" @if ($stateId == 0) selected @endif>Semua</option>
                            @foreach ($stateIds as $state)
                                <option value="{{ $state }}" @if ($stateId == $state) selected @endif>
                                    {{ $state }}
                                </option>
                            @endforeach
                        </select>
                    </div> --}}
                </div>
                <div class="row mb-3">
                    <div class="col-sm-6 col-12">
                        <button type="button" class="btn btn-primary" onclick="filterData();">Filter Data</button>
                        <a href="{{ route('admin.bank_cash_out.index') }}" class="btn btn-dark">Reset Filter</a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive w-100">
                            <table class="table table-bordered table-striped table-vcenter js-dataTable-full nowrap"
                                id="table_locker">
                                <thead>
                                    <tr>
                                        <th>Nama Karyawan</th>
                                        <th>ID Mesin Absensi</th>
                                        <th>State</th>
                                        <th>Waktu</th>
                                        <th>Tipe</th>
                                    </tr>
                                </thead>
                                <tbody>
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
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/flatpickr/flatpickr.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-bs5/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-buttons-bs5/buttons.bootstrap5.min.css') }}">
@endsection

@section('scripts')
    <!-- jQuery (required for DataTables plugin) -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>

    <!-- Page JS Plugins -->
    <script type="text/javascript" src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('js/plugins/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-bs5/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('js/moment.min.js') }}"></script>
    <script src="{{ asset('js/plugins/bootstrap-notify/bootstrap-notify.min.js') }}"></script>

    <script>
        flatpickr('.js-flatpickr', {
            "locale": "id" // locale for this instance only
        });

        $(function() {
            $('#table_locker').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 25,
                responsive: false,
                ajax: {
                    url: '{!! route('admin.attendance.datatable') !!}',
                    data: {
                        'start_date': '{{ $filterStartDate }}',
                        'end_date': '{{ $filterEndDate }}',
                        'type_id': '{{ $typeId }}',
                        'state_id': '{{ $stateId }}',
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
                    [3, 'desc']
                ],
                columns: [
                    {
                        data: 'partner_name',
                        name: 'partner_name',
                        class: 'text-center'
                    },
                    {
                        data: 'machine_id',
                        name: 'machine_id',
                        class: 'text-center'
                    },
                    {
                        data: 'state',
                        name: 'state',
                        class: 'text-center'
                    },
                    {
                        data: 'timestamp',
                        name: 'timestamp',
                        class: 'text-center',
                        orderable: true,
                        searchable: false,
                        render: function(data, type, row) {
                            if (type === 'display' || type === 'filter') {
                                return moment(data).format('DD-MM-YYYY HH:mm');
                            }
                            return data;
                        }
                    },
                    {
                        data: 'type_id',
                        name: 'type_id',
                        class: 'text-center',
                    },
                ]
            });
        });
        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                var exc = $('#exc').val();
                var vfw = data[2]; // use data for the vfw column
                var ref = new RegExp(exc, 'g');

                // VFW
                if (vfw.match(ref)) {
                    return false;
                }

                return true;
            }
        );
        function filterData() {
            let startDate = $('#start_date').val();
            let endDate = $('#end_date').val();
            let typeId = $('#type_id').val();
            let stateId = $('#state_id').val();

            let url = '{{ route('admin.attendance.index') }}';

            window.location = url + '?start_date=' + startDate +
            '&end_date=' + endDate +
            '&type_id=' + typeId +
            '&state_id=' + stateId
            ;
        }

    </script>
@endsection
