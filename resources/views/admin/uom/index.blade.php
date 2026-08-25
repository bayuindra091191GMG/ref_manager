@extends('layouts.admin')

@section('title')
    <title>Unit Material</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Unit Material
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Unit Material
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
                                (auth()->user()->hasAnyPermission('unit_material.add')))
                            <a href="{{ route('admin.uom.create') }}" class="btn btn-outline-success mt-lg-3" ><i class="fas fa-plus"></i> Tambah</a>
                        @endif
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive w-100" >
                            <table class="table table-bordered table-striped table-vcenter js-dataTable-full nowrap" id="table_locker">
                                <thead>
                                <tr>
                                    <th>Opsi</th>
                                    <th>Nama</th>
                                    <th>Status</th>
                                    <th>Tanggal Dibuat</th>
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

    <!-- Modal for confirm delete -->
    <div class="modal fade" id="modal_confirm_delete" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-slideup">
            <div class="modal-content">
                <div class="block-content">
                    <div class="row">
                        <div class="col-12 text-center">
                            <span>Apakah Anda yakin untuk menghapus Unit Material </span>
                            <span id="deleted_locker_number"></span>
                            <span> ?</span>
                            <input type="hidden" id="deleted_locker_id">
                        </div>
                    </div>
                </div>
                <div class="block-content block-content-full text-center bg-body">
                    <button type="button" class="btn btn-warning" data-bs-dismiss="modal">No</button>
                    <button type="button" id="btn_submit_delete" class="btn btn-danger" onclick="confirmDelete()">Yes</button>
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
        var modalConfirmDelete = new bootstrap.Modal(document.getElementById('modal_confirm_delete'), {'backdrop': 'static', 'keyboard' :false });

        $(function() {
            $('#table_locker').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                responsive: false,
                saveState: true,
                ajax: {
                    url: '{!! route('admin.uom.datatable') !!}'
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
                    { data: 'name', name: 'name', class: 'text-center'},
                    { data: 'status', name: 'status.description', class: 'text-center'},
                    { data: 'created_at', name: 'created_at', class: 'text-center', orderable: true, searchable: false,
                        render: function ( data, type, row ){
                            if ( type === 'display' || type === 'filter' ){
                                return moment(data).format('DD MMM YYYY');
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

        $(document).on('click', '.delete-modal', function(){
            let id = $(this).data("id");
            let name = $(this).data("number");

            modalDelete(id, name);
        });

        function modalDelete(id, name){
            $('#deleted_locker_id').val(id);
            $('#deleted_locker_number').html(name);

            modalConfirmDelete.show();
        }

        function confirmDelete(){
            let id = $('#deleted_locker_id').val();
            let number = $('#deleted_locker_number').html();

            $('#btn_loading_delete').show(100);
            $('#btn_submit_delete').hide();

            $.ajax({
                type:'POST',
                url:'{{ route('admin.uom.delete') }}',
                data:{
                    '_token': '{{ csrf_token() }}',
                    'id': id
                },
                success:function(data){
                    if(data === 'Sukses'){
                        One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', timer: 2000, message: 'Unit Material ' + number + ' has been successfully deleted!'});
                        window.setTimeout( redirect, 2000 );
                    }
                    else{
                        $('#btn_submit_delete').show(100);
                        $('#btn_loading_delete').hide();
                        One.helpers('jq-notify', {type: 'danger', icon: 'fa fa-info-circle me-1', timer: 2000, message: 'BAD REQUEST!'});
                    }
                },
                error:function(data){
                    $('#btn_submit_delete').show(100);
                    $('#btn_loading_delete').hide();
                    One.helpers('jq-notify', {type: 'danger', icon: 'fa fa-info-circle me-1', timer: 2000, message: 'Internal server error!'});
                }
            });
        }

        function redirect(){
            window.location.href = '{{ route('admin.uom.index') }}';
        }
    </script>
@endsection
