@extends('layouts.admin')

@section('title')
    <title>Daftar Treatment</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Daftar Treatment
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Daftar Treatment
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
                <div class="row mb-3">
                    <div class="col-12 text-end">
                        @if(Auth::guard('admin')->user()->is_super == 1 || Auth::guard('admin')->user()->is_developer == 1
                        || (Auth::guard('admin')->user()->hasPermissionTo('treatment.add')))
                            <a href="{{ route('admin.package.create') }}" class="btn btn-success mt-lg-3" ><i class="fas fa-plus"></i> Tambah</a>
                        @endif
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive w-100" >
                            <table class="table table-bordered table-striped table-vcenter js-dataTable-full nowrap" id="table_package">
                                <thead>
                                <tr class="text-center">
                                    <th>Tindakan</th>
                                    <th>Kode</th>
                                    <th>Nama</th>
                                    <th>Harga</th>
                                    <th>Durasi (Menit)</th>
                                    <th>Status</th>
                                    <th>Dibuat Pada</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach ($packages as $package)
                                        <tr>
                                            <td class="text-center">
                                                <a class="btn btn-xs btn-primary" href="{{ route('admin.package.edit', ['id' => $package->id]) }}">
                                                    <i class='fas fa-xs fa-pencil-alt'></i>
                                                </a>&nbsp;
                                                <a class="btn btn-xs btn-success" href="{{ route('admin.package.show', ['id' => $package->id]) }}">
                                                    <i class='fas fa-xs fa-list'></i>
                                                </a>
                                            </td>
                                            <td>
                                                {{ $package->code }}
                                            </td>
                                            <td>
                                                {{ $package->name }}
                                            </td>
                                            <td class="text-end">
                                                {{ number_format($package->price, 0, ',', '.') }}
                                            </td>
                                            <td class="text-center">
                                                {{ $package->duration }}
                                            </td>
                                            <td class="text-center">
                                                {{ $package->status_id == 1 ? 'Aktif' : 'Non-Aktif' }}
                                            </td>
                                            <td class="text-center">
                                                {{ Carbon\Carbon::parse($package->created_at)->format('d-m-Y') }}
                                            </td>
                                        </tr>
                                    @endforeach
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
        //var modalConfirmDelete = new bootstrap.Modal(document.getElementById('modal_confirm_delete'), {'backdrop': 'static', 'keyboard' :false });

        $(function() {
            $('#table_package').DataTable({
                serverSide: false,
                pageLength: 10,
                responsive: false,
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
            });
        });

        function redirect(){
            window.location.href = '{{ route('admin.package.index') }}';
        }
    </script>
@endsection
