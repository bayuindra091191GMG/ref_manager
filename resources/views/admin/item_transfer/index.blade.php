@extends('layouts.admin')

@section('title')
    <title>Transfer Barang</title>
@endsection

@section('content')
    <!-- Hero -->
    <div class="bg-body-light">
        <div class="content content-full">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Transfer Barang
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Transfer Barang
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
                        @if (auth()->user()->is_super == 1 ||
                                auth()->user()->is_developer == 1 ||
                                (auth()->user()->hasAnyPermission('item_transfer.approve')))
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#modal_approve_process">Receive</button>
                        @endif

                        @if (auth()->user()->is_super == 1 ||
                                auth()->user()->is_developer == 1 ||
                                (auth()->user()->hasAnyPermission('item_transfer.add')))
                            <a href="{{ route('admin.item_transfer.create') }}" class="btn btn-success"><i
                                    class="fas fa-plus"></i></a>
                        @endif
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive w-100">
                            <table class="table table-bordered table-striped table-vcenterl nowrap" id="table_locker">
                                <thead>
                                    <tr>
                                        <th class="text-center " style="width: 5%"><input type="checkbox" id="select_all" />
                                        </th>
                                        <th>Tindakan</th>
                                        <th>Kode</th>
                                        <th>Gudang Asal</th>
                                        <th>Gudang Tujuan</th>
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

    <!-- Modal for approve delete -->
    <div class="modal fade" id="modal_approve_process" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-slideup">

            <form id="form_approve_process" method="POST" action="{{ route('admin.item_transfer.approve') }}">
                @csrf
                <div class="modal-content">
                    <div class="block-content">
                        <div class="row">
                            <div class="col-12 text-center">
                                <span>Apakah Anda yakin untuk Approve Transfer Barang ini ?</span>
                                <span> (Jika sudah di Approve akan mengurangi stok pada gudang asal dan menambahkan stok di
                                    gudang tujuan)</span>
                                <div id="approve-id">

                                </div>
                                <div class="row justify-content-center">
                                    <div class="col-2">
                                        <label>Password</label>
                                    </div>
                                    <div class="col-6">
                                        <input type="password" name="password" class="form-control" placeholder="Password"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="block-content block-content-full text-center bg-body">
                        <button type="button" class="btn btn-sm btn-alt-secondary me-1" data-bs-dismiss="modal">No</button>
                        <input id="approve_submit" type="button" class="btn btn-sm btn-primary" value="Submit" />
                        <button type="button" id="btn_loading" class="btn btn-sm btn-danger" style="display: none;">
                            <i class="spinner-border spinner-border-sm text-green" role="status">
                                <span class="visually-hidden">Please wait...</span>
                            </i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal for confirm delete -->
    <div class="modal fade" id="modal_confirm_delete" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-slideup">
            <div class="modal-content">
                <div class="block-content">
                    <div class="row">
                        <div class="col-12 text-center">
                            <span>Apakah Anda yakin untuk menghapus Transfer Barang ini ?</span>
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
                    url: '{!! route('admin.item_transfer.datatable') !!}'
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
                    [2, 'desc']
                ],
                columns: [{
                        data: null,
                        render: function(data, type, row) {
                            if (data.status_id === 6 || data.status_id === 17) {
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
                        data: 'code',
                        name: 'code',
                        class: 'text-center'
                    },
                    {
                        data: 'warehouse_origin_name',
                        name: 'warehouse_origin_name',
                        class: 'text-center'
                    },
                    {
                        data: 'warehouse_destination_name',
                        name: 'warehouse_destination_name',
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
                        orderable: true,
                        searchable: false,
                        render: function(data, type, row) {
                            if (type === 'display' || type === 'filter') {
                                return moment(data).format('DD MMM YYYY HH:mm');
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
                url: '{{ route('admin.item_transfer.delete') }}',
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
                            message: 'Gudang ' + number + ' has been successfully deleted!'
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
            window.location.href = '{{ route('admin.item_transfer.index') }}';
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
