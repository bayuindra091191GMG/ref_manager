@extends('layouts.admin')

@section('title')
    <title>Daftar Jabatan Karyawan</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Daftar Jabatan Karyawan
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Daftar Jabatan Karyawan
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
{{--                <div class="row mb-2">--}}
{{--                    @if($isSuperAdmin)--}}
{{--                        <div class="col-sm-3 col-12">--}}
{{--                            <label class="form-label" for="filter_branch">Filter Cabang</label>--}}
{{--                            <select class="form-select" id="filter_branch" name="filter_branch">--}}
{{--                                <option value="-1" @if($filterBranchId === -1) selected @endif>Semua Cabang</option>--}}
{{--                                @foreach($branches as $branch)--}}
{{--                                    <option value="{{ $branch->id }}" @if($filterBranchId === $branch->id) selected @endif>Cabang {{ $branch->name }}</option>--}}
{{--                                @endforeach--}}
{{--                            </select>--}}
{{--                        </div>--}}
{{--                    @endif--}}
{{--                </div>--}}
                <div class="row mb-2">
                    <div class="col-sm-9 col-12">
{{--                        <button type="button" class="btn btn-primary" onclick="filterData();">Filter Data</button>--}}
{{--                        &nbsp;--}}
{{--                        <a href="{{ route('admin.position.index') }}" class="btn btn-primary">Reset Filter</a>--}}
                    </div>
                    <div class="col-sm-3 col-12 text-end">
                        @if(Auth::guard('admin')->user()->is_super == 1 || Auth::guard('admin')->user()->is_developer == 1
                        || (Auth::guard('admin')->user()->hasPermissionTo('position.add')))
                            <a href="{{ route('admin.position.create') }}" class="btn btn-success mt-lg-3"><i class="fas fa-plus"></i> Tambah</a>
                        @endif
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <hr/>
                        <div class="table-responsive w-100">
                            <table class="table table-bordered table-striped table-vcenter nowrap" id="table_employee_position">
                                <thead>
                                <tr>
                                    <th class="text-center">Tindakan</th>
{{--                                    <th class="text-center">Cabang</th>--}}
                                    <th class="text-center">Kode Jabatan</th>
                                    <th class="text-center">Nama</th>
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

    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script>
        $(function() {
            $('#table_employee_position').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                responsive: false,
                saveState: true,
                ajax: {
                    url: '{!! route('admin.position.datatable') !!}',
                    data: {
                        'filter_branch_id': '{{ $filterBranchId }}'
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
                    // { data: 'branch_name', name: 'branches.name', class: 'text-center'},
                    { data: 'code', name: 'code', class: 'text-center'},
                    { data: 'name', name: 'name', class: 'text-center'},
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
            let branchId = $('#filter_branch').val();
            let url = '{{ route('admin.position.index') }}';

            window.location = url + '?branch_id=' + branchId;
        }

    </script>
@endsection
