@extends('layouts.admin')

@section('title')
    <title>Booking Notes</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Booking Notes
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Booking Notes
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
                        <button class="btn btn-primary mt-lg-3" onclick="redirect()">Reset Filter</button>
                        <a href="{{ route('admin.booking_notes.create') }}" class="btn btn-outline-success mt-lg-3" ><i class="fas fa-plus"></i> Tambah</a>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-6 text-start">
                        <div class="row">
                            <div class="col-10">
                                <select id="partner_id" class="form-select">
                                    @if($selectedPartnerId != -1)
                                        <option value="{{ $selectedPartnerId }}" selected>{{ $selectedPartner }}</option>
                                    @endif
                                </select>
                            </div>
                            <div class="col-2">
                                <button type="button" onclick="filter()" class="btn btn-primary"><i id="filter" class="fa fa-search"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 text-end">
                        <div class="row">
                            <div class="col-10">
                                <input type="text" id="filter_date" class="form-control js-flatpickr" data-date-format="d-m-Y" value="{{ $filterDate }}"/>
                            </div>
                            <div class="col-2">
                                <button type="button" onclick="filter()" class="btn btn-primary"><i id="filter" class="fa fa-search"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive w-100" >
                            <table class="table table-bordered table-striped table-vcenter js-dataTable-full nowrap" id="table_data">
                                <thead>
                                <tr>
                                    <th>Option</th>
                                    <th>Therapis</th>
                                    <th>Book by</th>
                                    <th>Book Date</th>
                                    <th>Status</th>
                                    <th>Created At</th>
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

    <!-- Modal for confirm cancel -->
    <div class="modal fade" id="modal_confirm_cancel" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-slideup">
            <div class="modal-content">
                <div class="block-content">
                    <div class="row">
                        <div class="col-12 text-center">
                            <span>Apakah Anda yakin untuk cancel </span>
                            <span> ?</span>
                            <input type="hidden" id="cancel_id">
                        </div>
                    </div>
                </div>
                <div class="block-content block-content-full text-center bg-body">
                    <button type="button" class="btn btn-warning" data-bs-dismiss="modal">No</button>
                    <button type="button" id="btn_submit_cancel" class="btn btn-danger" onclick="confirmCancel()">Yes</button>
                    <button type="button" id="btn_loading_cancel" class="btn btn-danger" style="display: none;">
                        <i class="spinner-border spinner-border-sm text-green" role="status">
                            <span class="visually-hidden">Please wait...</span>
                        </i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for confirm done -->
    <div class="modal fade" id="modal_confirm_done" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-slideup">
            <div class="modal-content">
                <div class="block-content">
                    <div class="row">
                        <div class="col-12 text-center">
                            <span>Apakah Anda yakin untuk done </span>
                            <span> ?</span>
                            <input type="hidden" id="done_id">
                        </div>
                    </div>
                </div>
                <div class="block-content block-content-full text-center bg-body">
                    <button type="button" class="btn btn-warning" data-bs-dismiss="modal">No</button>
                    <button type="button" id="btn_submit_done" class="btn btn-danger" onclick="confirmDone()">Yes</button>
                    <button type="button" id="btn_loading_done" class="btn btn-danger" style="display: none;">
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
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/flatpickr/flatpickr.css') }}">
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
    <script src="{{ asset('js/plugins/flatpickr/l10n/id.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>

    <script>
        flatpickr('.js-flatpickr', {
            "locale": "id"  // locale for this instance only
        });
        $('#partner_id').select2({
            placeholder: {
                id: '-1',
                text: '--- Pilih Therapis ---'
            },
            width: '100%',
            minimumInputLength: 0,
            ajax: {
                url: '{{ route('admin.partner.select') }}',
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

        let modalConfirmCancel = new bootstrap.Modal(document.getElementById('modal_confirm_cancel'), {'backdrop': 'static', 'keyboard' :false });
        let modalConfirmDone = new bootstrap.Modal(document.getElementById('modal_confirm_done'), {'backdrop': 'static', 'keyboard' :false });

        $(function() {
            $('#table_data').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                responsive: false,
                saveState: true,
                ajax: {
                    url: '{!! route('admin.booking_notes.get') !!}',
                    data: {
                        'filterDate': '{{ $filterDate }}',
                        'partnerId': '{{ $selectedPartnerId }}'
                    },
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
                order: [ ],
                columns: [
                    { data: 'action', name: 'action', orderable: false, searchable: false, class: 'text-center'},
                    { data: 'therapis', name: 'therapis', class: 'text-center'},
                    { data: 'book_by', name: 'book_by', class: 'text-center'},
                    { data: 'book_date', name: 'book_date', class: 'text-center'},
                    { data: 'status', name: 'status', class: 'text-center'},
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

        $(document).on('click', '.cancel-modal', function(){
            let id = $(this).data("id");

            modalCancel(id);
        });

        function modalCancel(id){
            $('#cancel_id').val(id);

            modalConfirmCancel.show();
        }

        function confirmCancel(){
            let id = $('#cancel_id').val();

            $('#btn_loading_cancel').show(100);
            $('#btn_submit_cancel').hide();

            $.ajax({
                type:'POST',
                url:'{{ route('admin.booking_notes.cancel') }}',
                data:{
                    '_token': '{{ csrf_token() }}',
                    'id': id
                },
                success:function(data){
                    if(data === 'Sukses'){
                        One.helpers('jq-notify', {type: 'info', icon: 'fa fa-info-circle me-1', timer: 2000, message: 'Sukses Cancel Booking Notes!'});
                        window.setTimeout( redirect, 2000 );
                    }
                    else{
                        $('#btn_submit_cancel').show(100);
                        $('#btn_loading_cancel').hide();
                        One.helpers('jq-notify', {type: 'danger', icon: 'fa fa-info-circle me-1', timer: 2000, message: data});
                    }
                },
                error:function(data){
                    $('#btn_submit_cancel').show(100);
                    $('#btn_loading_cancel').hide();
                    One.helpers('jq-notify', {type: 'danger', icon: 'fa fa-info-circle me-1', timer: 2000, message: 'Internal server error!'});
                }
            });
        }

        $(document).on('click', '.done-modal', function(){
            let id = $(this).data("id");

            modalDone(id, name);
        });

        function modalDone(id, name){
            $('#done_id').val(id);

            modalConfirmDone.show();
        }

        function confirmDone(){
            let id = $('#done_id').val();

            $('#btn_loading_done').show(100);
            $('#btn_submit_done').hide();

            $.ajax({
                type:'POST',
                url:'{{ route('admin.booking_notes.done') }}',
                data:{
                    '_token': '{{ csrf_token() }}',
                    'id': id
                },
                success:function(data){
                    if(data === 'Sukses'){
                        One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', timer: 2000, message: 'Sukses Selesaikan Booking Note!'});
                        window.setTimeout( redirect, 2000 );
                    }
                    else{
                        $('#btn_submit_done').show(100);
                        $('#btn_loading_done').hide();
                        One.helpers('jq-notify', {type: 'danger', icon: 'fa fa-info-circle me-1', timer: 2000, message: data});
                    }
                },
                error:function(data){
                    $('#btn_submit_done').show(100);
                    $('#btn_loading_done').hide();
                    One.helpers('jq-notify', {type: 'danger', icon: 'fa fa-info-circle me-1', timer: 2000, message: 'Internal server error!'});
                }
            });
        }

        function redirect(){
            window.location.href = '{{ route('admin.booking_notes.index') }}';
        }

        function filter(){
            let date = $('#filter_date').val();
            let partnerId = $('#partner_id').val();
            let url = '{{ route('admin.booking_notes.index') }}';
            if(date !== null && date !== ''){
                url += '?filterDate=' + date;
            }
            if(partnerId !== null && partnerId > 0){
                url += '&partnerId=' + partnerId;
            }
            window.location.href = url;
        }
    </script>
@endsection
