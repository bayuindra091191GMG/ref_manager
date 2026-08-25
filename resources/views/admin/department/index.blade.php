@extends('layouts.admin')

@section('title')
    <title>Daftar Departemen</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Daftar Departemen
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Daftar Departemen
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
{{--                    @if($isSuperAdmin)--}}
{{--                        <div class="col-sm-9 col-12">--}}
{{--                            <button type="button" class="btn btn-primary" onclick="filterData();">Filter Data</button>--}}
{{--                            &nbsp;--}}
{{--                            <a href="{{ route('admin.department.index') }}" class="btn btn-primary">Reset Filter</a>--}}
{{--                        </div>--}}
{{--                    @endif--}}

                    <div class="col-12 text-end">
                        <a href="{{ route('admin.department.create') }}" class="btn btn-success mt-lg-3"><i class="fas fa-plus"></i> Tambah</a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <hr/>
                        <div class="table-responsive w-100">
                            <table class="table table-bordered table-striped table-vcenter nowrap" id="table_department">
                                <thead>
                                <tr>
                                    <th class="text-center" style="width: 10%;">Tindakan</th>
{{--                                    <th class="text-center">Cabang</th>--}}
                                    <th class="text-center">Kode</th>
                                    <th class="text-center">Nama</th>
                                    <th class="text-center">Dibuat Pada</th>
                                </tr>
                                </thead>
                                <tbody>
{{--                                @php(dd($departments))--}}
                                @foreach($departments as $department)
                                    <tr>
                                        <td class="text-center">
                                            <a href="{{ route('admin.department.edit', ['id' => $department->id]) }}" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i></a>
                                        </td>
{{--                                        <td class="text-center">{{ $department->branch->name }}</td>--}}
                                        <td class="text-center">{{ $department->code ?? '-' }}</td>
                                        <td class="text-center">{{ $department->name }}</td>
                                        <td class="text-center">{{ \Carbon\Carbon::parse($department->created_at)->format('d-m-Y') }}</td>
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
@endsection

@section('scripts')
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>

    <!-- jQuery (required for DataTables plugin) -->
    <script src="{{ asset('js/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-bs5/dataTables.bootstrap5.min.js') }}"></script>

    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script>
        $(function() {
            $('#table_department').DataTable({
                serverSide: false,
                pageLength: 10,
                responsive: false,
                order: [ [1, 'asc'] ],
            });
        });

        function filterData(){
            let branchId = $('#filter_branch').val();
            let url = '{{ route('admin.department.index') }}';

            window.location = url + '?branch_id=' + branchId;
        }

    </script>
@endsection
