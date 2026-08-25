@extends('layouts.admin')

@section('title')
    <title>Histori Komisi Karyawan</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Histori Komisi Karyawan
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Histori Komisi Karyawan
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
                @if($filterBranchId < 0)
                    <div class="row mb-2">
                        <div class="col-sm-3 col-12">
                            <label class="form-label" for="filter_branch">Filter Cabang</label>
                            <select class="form-select" id="filter_branch" name="filter_branch">
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" @if($filterBranchId === $branch->id) selected @endif>Cabang {{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-3 col-12">
                            <button type="button" class="btn btn-primary" onclick="filterBranch();" style="margin-top: 25px;">Pilih Cabang</button>
                        </div>
                    </div>
                @else
                    <input type="hidden" id="branch_id" value="{{ $filterBranchId }}" />
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
                            <label class="form-label" for="filter_employee">Filter Karyawan</label>
                            <select class="form-select" id="filter_employee" name="filter_employee">
                                @if(!empty($filterEmployee))
                                    <option value="{{ $filterEmployee->id }}">{{ $filterEmployee->code. ' - '. $filterEmployee->name }}</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-sm-3 col-12">
                            <label class="form-label" for="filter_item">Filter Barang</label>
                            <select class="form-select" id="filter_item" name="filter_item">
                                @if(!empty($filterItem))
                                    <option value="{{ $filterItem->id }}">{{ $filterItem->code. ' - '. $filterItem->name }}</option>
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-9 col-12">
                            <button type="button" class="btn btn-primary" onclick="filterData();">Filter Data</button>
                            &nbsp;
                            <a href="{{ route('admin.commission_history.employee.index', ['branch_id' => $filterBranchId]) }}" class="btn btn-warning">Reset Filter</a>
                            @if($isSuperAdmin)
                                &nbsp;
                                <a href="{{ route('admin.commission_history.employee.index') }}" class="btn btn-dark">Ganti Cabang</a>
                            @endif

                        </div>
                        <div class="col-sm-3 col-12 text-end">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <hr/>
                            <div class="table-responsive w-100">
                                <table class="table table-bordered table-striped table-vcenter nowrap" id="table_commission_employee_history">
                                    <thead>
                                    <tr>
                                        <th class="text-center">Tindakan</th>
                                        <th class="text-center">No. Transaksi</th>
                                        <th class="text-center">Kode Karyawan</th>
                                        <th class="text-center">Nama Karyawan</th>
                                        <th class="text-center">Komisi</th>
                                        <th class="text-center">Kode Barang</th>
                                        <th class="text-center">Nama Barang</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

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
    <script src="{{ asset('js/plugins/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('js/plugins/flatpickr/l10n/id.js') }}"></script>
    <script src="{{ asset('js/moment.min.js') }}"></script>
    <script src="{{ asset('js/moment-id.min.js') }}"></script>
    <script src="{{ asset('js/plugins/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
    <script src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('js/AutoNumeric.js') }}"></script>

    <script>
        @if($filterBranchId > 0)
            flatpickr('.js-flatpickr', {
                "locale": "id"  // locale for this instance only
            });

            var datatable = $('#table_commission_employee_history').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                responsive: false,
                saveState: true,
                ajax: {
                    url: '{!! route('admin.commission_history.employee.datatable') !!}',
                    data: {
                        'filter_branch_id': '{{ $filterBranchId }}',
                        'filter_employee_id': '{{ $filterEmployeeId }}',
                        'filter_item_id': '{{ $filterItemId }}',
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
                    { data: 'created_at', name: 'created_at', class: 'text-center', searchable: false,
                        render: function ( data, type, row ){
                            if ( type === 'display' || type === 'filter' ){
                                return moment(data).format('DD-MM-YYYY');
                            }
                            return data;
                        }
                    },
                    { data: 'transaction_code', name: 'transaction_code' },
                    { data: 'employee_code', name: 'employee_code' },
                    { data: 'employee_name', name: 'employee_name' },
                    { data: 'commission_add', name: 'commission_add', class: 'text-end', searchable: false,
                        render: function ( data, type, row ){
                            data = parseInt(data);
                            return data.toLocaleString(
                                "de-DE"
                            );
                        }
                    },
                    { data: 'item_code', name: 'item_code'},
                    { data: 'item_name', name: 'item_name'},
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

        $('#filter_employee').select2({
            placeholder: {
                id: '-1',
                text: '--- Pilih Karyawan ---'
            },
            width: '100%',
            minimumInputLength: 0,
            ajax: {
                url: '{{ route('admin.employee.select') }}',
                dataType: 'json',
                data: function (params) {
                    return {
                        q: $.trim(params.term),
                        branch_id: {{ $filterBranchId }}
                    };
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                }
            }
        });

            $('#filter_item').select2({
                placeholder: {
                    id: '-1',
                    text: '--- Pilih Barang ---'
                },
                width: '100%',
                minimumInputLength: 0,
                ajax: {
                    url: '{{ route('admin.item.select') }}',
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
                let branchId = $('#branch_id').val();
                let startDate = $('#start_date').val();
                let endDate = $('#end_date').val();
                let employeeId = $('#filter_employee').val();
                let itemId = $('#filter_item').val();
                let url = '{{ route('admin.commission_history.employee.index') }}';

                window.location = url + '?branch_id=' + branchId + '&employee_id=' + employeeId + '&item_id=' + itemId + '&start_date=' + startDate + '&end_date=' + endDate;
            }
        @endif


        function filterBranch(){
            let branchId = $('#filter_branch').val();
            let url = '{{ route('admin.commission_history.employee.index') }}';

            window.location = url + '?branch_id=' + branchId;
        }

    </script>
@endsection
