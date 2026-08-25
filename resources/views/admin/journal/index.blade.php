@extends('layouts.admin')

@section('title')
    <title>Daftar Jurnal</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Jurnal
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Jurnal
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
            <div class="block-content tab-content">
                <div class="tab-pane"></div>

                <div class="tab-pane active">
                    <div class="row mb-3 justify-content-between align-items-end">
                        <div class="col-lg col-12">
                            <form>
                                <div class="row align-items-end">
                                    <div class="col-lg-3 col-12 mb-lg-0 mb-3">
                                        <label class="form-label" for="name">Tipe</label>
                                        <select id="type" name="type" class="form-select form-control-select2">
                                            <option value="">Semua</option>
                                            @foreach($types as $type)
                                                <option value="{{$type->code}}" {{ request()->get('type') ? (request()->get('type') == $type->code ? 'selected' : '') : '' }}>{{$type->code}} - {{$type->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-3 col-12 mb-lg-0 mb-3">
                                        <label class="form-label" for="period">Periode</label>
                                        <input type="text" class="form-control" id="period" name="period" value="{{ request()->get('period') }}">
                                    </div>
                                    <div class="col-lg-3 col-12 mb-lg-0 mb-3">
                                        <label class="form-label" for="period">Status</label>
                                        <select class="form-select" id="is_approve" name="is_approve">
                                            <option value="">Semua</option>
                                            <option value="1" {{ request()->get('is_approve') ? (request()->get('is_approve') == 1 ? 'selected' : '') : '' }}>Unapprove</option>
                                            <option value="13" {{ request()->get('is_approve') ? (request()->get('is_approve') == 13 ? 'selected' : '') : '' }}>Approve</option>
                                        </select>
                                    </div>
                                    <div class="col-auto">
                                        <button type="submit" class="btn btn-success">Filter</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="col-lg-auto col-12 text-end">
                            @if(Auth::guard('admin')->user()->is_super == 1 || Auth::guard('admin')->user()->is_developer == 1
                            || (Auth::guard('admin')->user()->hasPermissionTo('journal_manual.approve')))
                                <button type="button" class="btn btn-outline-primary mt-lg-3" data-bs-toggle="modal" data-bs-target="#modal_approve_process">Approve</button>
                            @endif

                            @if(Auth::guard('admin')->user()->is_super == 1 || Auth::guard('admin')->user()->is_developer == 1
                            || (Auth::guard('admin')->user()->hasPermissionTo('journal_manual.add')))
                                <a href="{{ route('admin.journal.create') }}" class="btn btn-outline-success mt-lg-3"><i class="fas fa-plus"></i> Tambah</a>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="table-responsive w-100" >
                                <table class="table table-bordered table-striped table-vcenter js-dataTable-full" id="table_department">
                                    <thead>
                                    <tr>
                                        <th class="text-center" style="width: 5%">Approve <br><input type="checkbox" id="select_all"/></th>
                                        <th class="text-center " style="width: 15%">Opsi</th>
                                        <th class="text-center " style="width: 15%">Tanggal</th>
                                        <th class="text-center " style="width: 15%">Kode</th>
                                        <th class="text-center " style="width: 15%">Tipe</th>
                                        <th class="text-center " style="width: 15%">Deskripsi</th>
                                        <th class="text-center " style="width: 15%">Waktu Dibuat</th>
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
    </div>

    <!-- Approve Block Modal-->
    <div id="modal_approve_process" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modal_approve_process" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="block block-rounded block-transparent mb-0">
                    <form id="form_approve_process" method="POST" action="{{ route('admin.journal.approve') }}">
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
                            <h6 class="font-weight-semibold">Anda yakin untuk Approve?</h6>
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
                        <div class="block-content block-content-full text-end bg-body">
                            <button type="button" class="btn btn-sm btn-alt-secondary me-1" data-bs-dismiss="modal">Close</button>
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
                            <span>Apakah Anda yakin untuk menghapus Jurnal ini </span>
                            <span id="deleted_locker_number"></span>
                            <span> ?</span>
                            <input type="hidden" id="deleted_locker_id">
                            <div class="row justify-content-center">
                                <div class="col-2">
                                    <label>Password</label>
                                </div>
                                <div class="col-6">
                                    <input id="delPassword" type="password" name="password" class="form-control" placeholder="Password"/>
                                </div>
                            </div>
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

    <!-- Modal for confirm unapprove -->
    <div class="modal fade" id="modal_confirm_unapprove" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-slideup">
            <div class="modal-content">
                <div class="block-content">
                    <div class="row">
                        <div class="col-12 text-center">
                            <span>Apakah Anda yakin untuk unapprove Jurnal ini </span>
                            <span id="unapprove_code"></span>
                            <span> ?</span>
                            <input type="hidden" id="unapprove_id">
                            <div class="row justify-content-center mt-3">
                                <div class="col-2">
                                    <label>Password</label>
                                </div>
                                <div class="col-6">
                                    <input id="unapprovePassword" type="password" name="password" class="form-control" placeholder="Password"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="block-content block-content-full text-center bg-body">
                    <button type="button" class="btn btn-warning" data-bs-dismiss="modal">No</button>
                    <button type="button" id="btn_submit_delete" class="btn btn-danger" onclick="confirmUnapprove()">Yes</button>
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
    <link rel="stylesheet" href="{{ asset('js/plugins/flatpickr/flatpickr.min.css') }}">
@endsection

@section('scripts')
    <!-- jQuery (required for DataTables plugin) -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>

    <!-- Page JS Plugins -->
    <script src="{{ asset('js/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-bs5/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('js/moment.min.js') }}"></script>
    <script src="{{ asset('js/plugins/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
    <script src="{{ asset('js/plugins/flatpickr/flatpickr.min.js') }}"></script>

    <script>
        var modalConfirmDelete = new bootstrap.Modal(document.getElementById('modal_confirm_delete'), {'backdrop': 'static', 'keyboard' :false });
        var modalConfirmUnapprove = new bootstrap.Modal(document.getElementById('modal_confirm_unapprove'), {'backdrop': 'static', 'keyboard' :false });

        $(function() {
            $('#table_department').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                responsive: false,
                saveState: true,
                ajax: {
                    url: '{!! route('admin.journal.datatable') !!}',
                    data: @json(request()->all())
                },
                sWrapper: "dataTables_wrapper dt-bootstrap5",
                sFilterInput: "form-control form-control-sm",
                sLengthSelect: "form-select form-select-sm",
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
                order: [ [2, 'desc'], [3, 'desc'] ],
                columns: [
                    {
                        data: null,
                        render: function (data, type, row) {
                            if(data.status_id == 13 || data.status_id == 2){
                                check = '<input class="form-check-input" type="checkbox" checked disabled/>';
                            }
                            else{
                                check = '<input class="form-check-input approvalCheckbox" type="checkbox"  value=' + data.id + '>';
                            }
                            return check;
                        },
                        class: "text-center", searchable: false, orderable: false
                    },
                    { data: 'action', name: 'action', orderable: false, searchable: false, class: 'text-center'},
                    { data: 'date', name: 'transaction_date', class: 'text-center', orderable: true, searchable: false,
                        render: function ( data, type, row ){
                            if ( type === 'display' || type === 'filter' ){
                                return moment(data).format('DD-MM-YYYY');
                            }
                            return data;
                        }
                    },
                    { data: 'transaction_code', name: 'transaction_code', class: 'text-center'},
                    { data: 'transaction_type', name: 'transaction_type', class: 'text-center'},
                    { data: 'notes', name: 'notes', class: 'text-center'},
                    { data: 'created_at', name: 'created_at', class: 'text-center', orderable: true, searchable: false,
                        render: function ( data, type, row ){
                            if ( type === 'display' || type === 'filter' ){
                                return moment(data).format('DD-MM-YYYY HH:mm');
                            }
                            return data;
                        }
                    },
                ]
            });
        });

        $('#period').flatpickr({
            mode: 'range',
            dateFormat: 'd-m-Y'
        });

        $(document).on('click', '.delete-modal', function(){
            let id = $(this).data("id");
            let name = $(this).data("number");

            modalDelete(id, name);
        });

        $(document).on('click', '.cancel-btn', function(){
            let id = $(this).data("id");
            let name = $(this).data("number");

            modalUnapprove(id, name);
        });

        function modalDelete(id, name){
            $('#deleted_locker_id').val(id);
            $('#deleted_locker_number').html(name);

            modalConfirmDelete.show();
        }

        function modalUnapprove(id, name){
            $('#unapprove_id').val(id);
            $('#unapprove_code').html(name);

            modalConfirmUnapprove.show();
        }

        function confirmDelete(){
            let id = $('#deleted_locker_id').val();
            let number = $('#deleted_locker_number').html();
            let password = $('#delPassword').val();

            $('#btn_loading_delete').show(100);
            $('#btn_submit_delete').hide();

            $.ajax({
                type:'POST',
                url:'{{ route('admin.journal.delete') }}',
                data:{
                    '_token': '{{ csrf_token() }}',
                    'id': id,
                    'password': password
                },
                success:function(data){
                    if(data === 'Sukses'){
                        One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1',
                            timer: 2000,
                            message: 'Jurnal ' + number + ' has been successfully deleted!'});
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

        function confirmUnapprove(){
            let id = $('#unapprove_id').val();
            let number = $('#unapprove_code').html();
            let password = $('#unapprovePassword').val();

            $('#btn_loading_delete').show(100);
            $('#btn_submit_delete').hide();

            $.ajax({
                type:'POST',
                url:'{{ route('admin.journal.unapprove') }}',
                data:{
                    '_token': '{{ csrf_token() }}',
                    'id': id,
                    'password': password
                },
                success:function(data){
                    if(data === 'Sukses'){
                        One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1',
                            timer: 2000,
                            message: 'Jurnal ' + number + ' has been successfully unapproved!'});
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
            window.location.href = '{{ route('admin.journal.index') }}';
        }


        $('#approve_submit').click(function () {

            $(".approvalCheckbox:checked").each(function () {
                var value = $(this).val();
                $('#approve-id').append("<input name='ids[]' type='hidden' value='" + value +"'>");
            });

            $('#approve_submit').hide();
            $('#btn_loading').show(200);

            $('#form_approve_process').submit();
        });
        $("#select_all").click(function(){
            $('.approvalCheckbox').prop('checked', this.checked);
        });
        $('#form_approve_process').on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                return false;
            }
        });
    </script>
@endsection
