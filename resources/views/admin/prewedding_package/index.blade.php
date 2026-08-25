@extends('layouts.admin')

@section('title')
    <title>Daftar Paket</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Daftar Paket
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Daftar Paket
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
            <div class="block-header">
                {{--                <h3 class="block-title">Users</h3>--}}
            </div>
            <div class="block-content block-content-full">
                <form>
                    <div class="row mb-2">
                        <div class="col-auto ms-auto">
                        @if(Auth::guard('admin')->user()->is_super == 1 || Auth::guard('admin')->user()->is_developer == 1
                        || (Auth::guard('admin')->user()->hasPermissionTo('treatment.add')))
                            <a href="{{ route('admin.prewedding_package.create') }}" class="btn btn-success"><i class="fas fa-plus"></i> Tambah</a>
                        @endif
                        </div>
                    </div>
                </form>
                <div class="row">
                    <div class="col-12">
                        <hr/>
                        <div class="table-responsive w-100">
                            <table class="table table-bordered table-striped table-vcenter nowrap" id="table_prewedding_package">
                                <thead>
                                <tr>
                                    <th style="width: 10%">Aksi</th>
                                    <th>Kode</th>
                                    <th>Nama</th>
                                    <th>Harga</th>
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
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
@endsection

@section('scripts')
    <!-- jQuery (required for DataTables plugin) -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>

    <!-- Page JS Plugins -->
    <script src="{{ asset('js/plugins/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('js/plugins/flatpickr/l10n/id.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-bs5/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('js/moment.min.js') }}"></script>
    <script src="{{ asset('js/moment-id.min.js') }}"></script>
    <script src="{{ asset('js/plugins/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
    <script src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>

    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script>
        flatpickr('.js-flatpickr', {
            "locale": "id",
            mode: "range",
        });

        $(function() {
            $('#table_prewedding_package').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 25,
                responsive: false,
                saveState: true,
                ajax: {
                    url: '{!! route('admin.prewedding_package.datatable') !!}',
                    data: @json(request()->all())
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
                order: [ [1, 'desc'] ],
                columns: [
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        class: 'text-center'
                    },
                    {
                        data: 'code',
                        name: 'prewedding_packages.code',
                        class: 'text-center'
                    },
                    {
                        data: 'name',
                        name: 'prewedding_packages.name',
                        class: 'text-center'
                    },
                    {
                        data: 'price',
                        name: 'prewedding_packages.price',
                        class: 'text-center',
                        render: $.fn.dataTable.render.number(',', '.', 0)
                    },
                    {
                        data: 'status_id',
                        name: 'prewedding_packages.status_id',
                        class: 'text-center',
                        render: function (data) {
                            if (data == 1) {
                                return `<span class="text-success fw-bold">Aktif</span>`;
                            } else {
                                return `<span class="text-danger fw-bold">Non Aktif</span>`;
                            }
                        }
                    }
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
            let startDate = $('#start_date').val();
            let endDate = $('#end_date').val();
            let url = '{{ route('admin.prewedding_package.index') }}';

            let tmp = url + '?start_date=' + startDate + '&end_date=' + endDate;

            window.location = tmp;
        }

    </script>
@endsection
