@extends('layouts.admin')

@section('title')
    <title>Member</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Member
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Member
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
                    <div class="row">
                        <div class="col-12">
                            <div class="table-responsive w-100" >
                                <table class="table table-bordered table-striped table-vcenter js-dataTable-full" id="table_department">
                                    <thead>
                                    <tr>
                                        <th class="text-center ">Nama</th>
                                        <th class="text-center ">Telepon</th>
                                        <th class="text-center ">Gender</th>
                                        <th class="text-center ">Tgl Lahir</th>
                                        <th class="text-center " style="width: 15%">Poin</th>
                                        <th class="text-center " style="width: 15%">Join Date</th>
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
                    <form id="form_approve_process" method="POST" action="{{ route('admin.supplier_invoice.approve') }}">
                        @csrf
                        <div class="block-header block-header-default">
                            <h3 class="block-title">Ubah Password Member</h3>
                            <div class="block-options">
                                <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">
                                    <i class="fa fa-fw fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="block-content fs-sm">
                            <h6 class="font-weight-semibold">Anda yakin untuk Ubah Password Member?</h6>
                            <div id="approve-id">

                            </div>
                            <div class="row justify-content-center">
                                <div class="col-2">
                                    <label>Password Verifikasi</label>
                                </div>
                                <div class="col-6">
                                    <input type="password" name="password" class="form-control" placeholder="Password"/>
                                </div>
                            </div>
                            <div class="row justify-content-center">
                                <div class="col-2">
                                    <label>Password Member</label>
                                </div>
                                <div class="col-6">
                                    <input type="text" name="new_password" class="form-control" placeholder="Password Baru"/>
                                </div>
                            </div>
                            <div class="row justify-content-center">
                                <div class="col-2">
                                    <label>Tulis Ulang Password Member</label>
                                </div>
                                <div class="col-6">
                                    <input type="text" name="retype_password" class="form-control" placeholder="Tulis Ulang Password"/>
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

        $(function() {
            $('#table_department').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                responsive: false,
                ajax: {
                    url: '{!! route('admin.member.datatable') !!}'
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
                order: [ [0, 'desc'] ],
                columns: [
                    { data: 'name', name: 'name', class: 'text-center'},
                    { data: 'phone', name: 'phone', class: 'text-left'},
                    { data: 'gender', name: 'gender', class: 'text-center', orderable: true, searchable: false,
                        render: function ( data, type, row ){
                            return data == 'L' ? 'Laki-Laki' : 'Perempuan';
                        }
                    },
                    { data: 'birthday_date', name: 'created_at', class: 'text-center', orderable: true, searchable: false,
                        render: function ( data, type, row ){
                            if (data != null ){
                                return moment(data).format('DD MMM YYYY');
                            }
                            return '-';
                        }
                    },
                    { data: 'point', name: 'point', class: 'text-left'},
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
                url:'{{ route('admin.coa.delete') }}',
                data:{
                    '_token': '{{ csrf_token() }}',
                    'id': id
                },
                success:function(data){
                    if(data === 'success'){
                        One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', timer: 2000, message: 'COA ' + number + ' has been successfully deleted!'});
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
            window.location.href = '{{ route('admin.coa.index') }}';
        }
    </script>
@endsection
