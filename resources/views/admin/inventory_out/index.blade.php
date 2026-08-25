@extends('layouts.admin')

@section('title')
    <title>Move Out</title>
@endsection

@section('content')
    <!-- Hero -->
    <div class="bg-body-light">
        <div class="content content-full">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Move Out
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Move Out
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
                </div>
                <div class="row mb-3">
                    <div class="col-sm-6 col-12">
                        <button type="button" class="btn btn-primary" onclick="filterData();">Filter Data</button>
                        <a href="{{ route('admin.inventory_out.index') }}" class="btn btn-dark">Reset Filter</a>
                    </div>
                    <div class="col-sm-6 col-12 text-end">
                        @if (auth()->user()->is_super == 1 ||
                                auth()->user()->is_developer == 1 ||
                                (auth()->user()->hasAnyPermission('move_out.approve')))
                            <button type="button" class="btn btn-warning" data-bs-toggle="modal"
                                data-bs-target="#modal_approve_process">Approve</button>
                        @endif


                        @if (auth()->user()->is_super == 1 ||
                                auth()->user()->is_developer == 1 ||
                                (auth()->user()->hasAnyPermission('move_out.add')))
                            <a href="{{ route('admin.inventory_out.create') }}" class="btn btn-outline-success"><i
                                    class="fas fa-plus"></i></a>
                        @endif
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive w-100">
                            <table class="table table-bordered table-striped table-vcenter js-dataTable-full nowrap"
                                id="table_locker">
                                <thead>
                                    <tr>
                                        <th class="text-center " style="width: 5%">Action <br><input type="checkbox"
                                                id="select_all" /></th>
                                        <th>Tindakan</th>
                                        <th>Tanggal</th>
                                        <th>Kode</th>
                                        <th>Gudang</th>
                                        <th>Status</th>
                                        <th>Tanggal Dibuat</th>
                                        <th>Diupdate Pada</th>
                                        <th>Diupdate Oleh</th>
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

    <!-- Approve Block Modal-->
    <div id="modal_approve_process" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modal_approve_process"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="block block-rounded block-transparent mb-0">
                    <form id="form_approve_process" method="POST" action="{{ route('admin.inventory_out.approve') }}">
                        @csrf
                        <div class="block-header block-header-default">
                            <h3 class="block-title">Approval</h3>
                            <div class="block-options">
                                <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">
                                    <i class="fa fa-fw fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="block-content fs-sm">
                            <span>Apakah Anda yakin untuk Approve Move Out ini ?</span>
                            <br>
                            <span> (Jika sudah di Approve akan menambahkan stok di gudang)</span>
                            <div id="approve-id">

                            </div>
                        </div>
                        <div class="block-content block-content-full text-end bg-body">
                            <button type="button" class="btn btn-sm btn-alt-secondary me-1"
                                data-bs-dismiss="modal">Close</button>
                            <input id="approve_submit" type="button" class="btn btn-sm btn-primary" value="Submit" />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- END Block Modal -->

    <!-- Modal for confirm delete -->
    <div class="modal fade" id="modal_confirm_delete" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-slideup">
            <div class="modal-content">
                <div class="block-content">
                    <div class="row">
                        <div class="col-12 text-center">
                            <span>Apakah Anda yakin untuk menghapus Move Out ini ?</span>
                            <input type="hidden" id="deleted_locker_id">
                        </div>
                    </div>
                </div>
                <div class="block-content block-content-full text-center bg-body">
                    <button type="button" class="btn btn-warning" data-bs-dismiss="modal">No</button>
                    <button type="button" id="btn_submit_delete" class="btn btn-danger"
                        onclick="confirmDelete()">Yes</button>
                    <button type="button" id="btn_loading_delete" class="btn btn-danger" style="display: none;">
                        <i class="spinner-border spinner-border-sm text-green" role="status">
                            <span class="visually-hidden">Please wait...</span>
                        </i>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <!-- Page JS Plugins CSS -->
    <link rel="stylesheet" href="{{ asset('js/plugins/flatpickr/flatpickr.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-bs5/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-buttons-bs5/buttons.bootstrap5.min.css') }}">
@endsection

@section('scripts')
    <!-- jQuery (required for DataTables plugin) -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>

    <!-- Page JS Plugins -->
    <script src="{{ asset('js/plugins/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-bs5/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('js/moment.min.js') }}"></script>
    <script src="{{ asset('js/plugins/bootstrap-notify/bootstrap-notify.min.js') }}"></script>

    <script>
        flatpickr('.js-flatpickr', {
            "locale": "id" // locale for this instance only
        });

        var modalConfirmDelete = new bootstrap.Modal(document.getElementById('modal_confirm_delete'), {
            'backdrop': 'static',
            'keyboard': false
        });

        $(function() {
            $('#table_locker').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                responsive: false,
                saveState: true,
                ajax: {
                    url: '{!! route('admin.inventory_out.datatable') !!}',
                    data: {
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
                order: [6, 'desc'],
                columns: [{
                        data: null,
                        render: function(data, type, row) {
                            if (data.status_id == 13 || data.status_id == 2) {
                                check =
                                    '<input class="form-check-input" type="checkbox" checked disabled/>';
                            } else {
                                check =
                                    '<input class="form-check-input approvalCheckbox" type="checkbox"  value=' +
                                    data.id + '>';
                            }
                            return check;
                        },
                        class: "text-center",
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        class: 'text-center'
                    },
                    {
                        data: 'date',
                        name: 'date',
                        class: 'text-center',
                        searchable: false,
                        render: function(data, type, row) {
                            if (type === 'display' || type === 'filter') {
                                return moment(data).format('DD-MM-YYYY');
                            }
                            return data;
                        }
                    },
                    {
                        data: 'code',
                        name: 'code',
                        class: 'text-center'
                    },
                    {
                        data: 'warehouse_name',
                        name: 'warehouse_name',
                        class: 'text-center'
                    },
                    {
                        data: 'status',
                        name: 'status.display',
                        class: 'text-center'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        class: 'text-center',
                        orderable: true,
                        searchable: false,
                        render: function(data, type, row) {
                            if (type === 'display' || type === 'filter') {
                                return moment(data).format('DD MMM YYYY');
                            }
                            return data;
                        }
                    },
                    {
                        data: 'updated_at',
                        name: 'updated_at',
                        class: 'text-center',
                        searchable: false,
                        render: function(data, type, row) {
                            if (type === 'display' || type === 'filter') {
                                return moment(data).format('DD-MM-YYYY HH:mm');
                            }
                            return data;
                        }
                    },
                    {
                        data: 'updated_by',
                        name: 'updated_by',
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
            let url = '{{ route('admin.inventory_out.index') }}';

            window.location = url + '?start_date=' + startDate + '&end_date=' + endDate;
        }

        $(document).on('click', '.delete-modal', function() {
            let id = $(this).data("id");
            let name = $(this).data("number");

            modalDelete(id, name);
        });

        function modalDelete(id, name) {
            $('#deleted_locker_id').val(id);
            $('#deleted_locker_number').html(name);

            modalConfirmDelete.show();
        }

        function confirmDelete() {
            let id = $('#deleted_locker_id').val();
            let number = $('#deleted_locker_number').html();

            $('#btn_loading_delete').show(100);
            $('#btn_submit_delete').hide();

            $.ajax({
                type: 'POST',
                url: '{{ route('admin.inventory_out.delete') }}',
                data: {
                    '_token': '{{ csrf_token() }}',
                    'id': id
                },
                success: function(data) {
                    if (data === 'Sukses') {
                        One.helpers('jq-notify', {
                            type: 'success',
                            icon: 'fa fa-info-circle me-1',
                            timer: 2000,
                            message: 'Data has been successfully deleted!'
                        });
                        window.setTimeout(redirect, 2000);
                    } else {
                        $('#btn_submit_delete').show(100);
                        $('#btn_loading_delete').hide();
                        One.helpers('jq-notify', {
                            type: 'danger',
                            icon: 'fa fa-info-circle me-1',
                            timer: 2000,
                            message: 'BAD REQUEST!'
                        });
                    }
                },
                error: function(data) {
                    $('#btn_submit_delete').show(100);
                    $('#btn_loading_delete').hide();
                    One.helpers('jq-notify', {
                        type: 'danger',
                        icon: 'fa fa-info-circle me-1',
                        timer: 2000,
                        message: 'Internal server error!'
                    });
                }
            });
        }

        function redirect() {
            window.location.href = '{{ route('admin.inventory_out.index') }}';
        }

        $('#approve_submit').click(function() {

            $(".approvalCheckbox:checked").each(function() {
                var value = $(this).val();
                $('#approve-id').append("<input name='ids[]' type='hidden' value='" + value + "'>");
            });

            $('#approve_submit').hide();
            $('#btn_loading').show(200);

            $('#form_approve_process').submit();
        });
        $("#select_all").click(function() {
            $('.approvalCheckbox').prop('checked', this.checked);
        });
    </script>
@endsection
