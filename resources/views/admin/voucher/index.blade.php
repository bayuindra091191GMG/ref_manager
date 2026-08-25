@extends('layouts.admin')

@section('title')
    <title>Daftar Voucher</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Voucher
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Voucher
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <div class="content" id="app">
        @include('partials._success')
        @include('partials._error')
        <div class="block block-rounded">
            <div class="block-content">
                <div class="row mb-3">
                    <div class="col-12 text-end">
                        <a href="{{ route('admin.voucher.create') }}" class="btn btn-success mt-lg-3"><i class="fas fa-plus"></i> Tambah</a>
                        <a href="{{ route('admin.voucher.export') }}" class="btn btn-primary mt-lg-3"><i class="fas fa-book"></i></a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive w-100" >
                            <table class="table table-bordered table-striped table-vcenter js-dataTable-full" id="table_vouchers">
                                <thead>
                                <tr>
                                    <th class="text-center " style="width: 12%">Opsi</th>
                                    <th class="text-center " style="width: 15%">Owner</th>
                                    <th class="text-center " style="width: 15%">Jenis Voucher</th>
                                    <th class="text-center " style="width: 5%">Quantity</th>
                                    <th class="text-center " style="width: 15%">Notes</th>
                                    <th class="text-center " style="width: 5%">Status</th>
                                    <th class="text-center " style="width: 10%">Exp. Date</th>
                                    <th class="text-center " style="width: 15%">Tanggal Dibuat</th>
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
        <!-- Pop In Block Modal -->
        <div class="modal fade" id="modal-set-exp-date" tabindex="-1" role="dialog" aria-labelledby="modal-block-popin" aria-hidden="true">
            <div class="modal-dialog modal-dialog-popin" role="document">
                <div class="modal-content">
                    <div class="block block-rounded block-transparent mb-0">
                        <div class="block-header block-header-default">
                            <h3 class="block-title">Set Exp Date</h3>
                            <div class="block-options">
                                <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">
                                    <i class="fa fa-fw fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="block-content fs-sm">
                            <div class="row">
                                <div class="col-12">
                                    <span>Kode Voucher: </span><h6>@{{ voucherCode }}</h6>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4">
                                    <label>Tanggal Expired</label>
                                </div>
                                <div class="col-8">
                                    <input type="text"
                                           id="exp_date"
                                           name="exp_date"
                                           data-date-format="d-m-Y"
                                           class="form-control js-flatpickr" v-model="expDate"/>
                                </div>
                            </div>
                        </div>
                        <div class="block-content block-content-full text-end bg-body">
                            <button type="button" class="btn btn-danger" id="btn_exp_date_cancel" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-success" id="btn_exp_date_submit" v-on:click="submitExpDate()">Submit</button>
                            <button type="button" id="btn_submit_loading" class="btn btn-success" style="display: none;">
                                <i class="spinner-border spinner-border-sm text-green" role="status">
                                    <span class="visually-hidden">Mohon tunggu...</span>
                                </i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END Pop In Block Modal -->

        <!-- Modal for send email -->
        <div class="modal fade" id="modal-send-email" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-slideup">
                <div class="modal-content">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Send Voucher</h3>
                        <div class="block-options">
                            <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close" @click="clearData()">
                                <i class="fa fa-fw fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <div class="block-content">
                        <div class="row">
                            <div class="col-sm-4">
                                <label>Email</label>
                            </div>
                            <div class="col-sm-8">
                                <input class="form-control" placeholder="Masukkan email yang dituju" type="text" v-model="email">
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-sm-12">
                                <div class="text-end">
                                    <button type="button" class="btn btn-info" @click="sendEmail()">Send</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
                            <span>Apakah Anda yakin ingin menghapus Coupon ini </span>
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
                            <span class="visually-hidden">Mohon tunggu...</span>
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
    <link rel="stylesheet" href="{{ asset('js/plugins/flatpickr/flatpickr.css') }}">
@endsection

@section('scripts')
    <!-- jQuery (required for DataTables plugin) -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>

    <!-- Page JS Plugins -->
    <script src="{{ asset('js/vue.js') }}"></script>
    <script src="{{ asset('js/axios.js') }}"></script>
    <script src="{{ asset('js/AutoNumeric.js') }}"></script>
    <script src="{{ asset('js/vue-autonumeric.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-bs5/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('js/moment.min.js') }}"></script>
    <script src="{{ asset('js/plugins/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
    <script src="{{ asset('js/plugins/flatpickr/flatpickr.min.js') }}"></script>

    <script type="text/javascript">
        let today = new Date();
        let dd = String(today.getDate()).padStart(2, '0');
        let mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
        let yyyy = today.getFullYear();

        today = dd + '-' + mm + '-' + yyyy;

        let app = new Vue({
            el: "#app",
            data: {
                expDate: today,
                voucherId: 0,
                voucherCode: '',
                email: '',
                autonumericFormat: {
                    minimumValue: '0',
                    maximumValue: '9999999999',
                    digitGroupSeparator: '.',
                    decimalCharacter: ',',
                    decimalPlaces: 0,
                    modifyValueOnWheel: false,
                    allowDecimalPadding: false,
                    emptyInputBehavior: 'zero'
                },
                autonumericPercentageFormat: {
                    minimumValue: '0',
                    maximumValue: '100',
                    digitGroupSeparator: '.',
                    decimalCharacter: ',',
                    decimalPlaces: 0,
                    modifyValueOnWheel: false,
                    allowDecimalPadding: false,
                    emptyInputBehavior: 'zero'
                },
            },
            methods: {
                async clearData() {
                    app.voucherId = 0;
                    app.email = '';
                },
                async sendEmail() {
                    $.ajax({
                        url: '{{ route('admin.voucher.send_email') }}',
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        data: {
                            'email': app.email,
                            'voucher_id': app.voucherId,
                        },
                        success: function (data) {
                            modalSendEmail.hide();
                            app.clearData();
                            One.helpers('jq-notify', {
                                type: 'success',
                                icon: 'fa fa-check-circle me-1',
                                message: data.message
                            });
                        },
                        error: function (xhr, status, error) {
                            One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: xhr.responseText});
                        },
                    });
                },
                setExpDate(voucherId, voucherCode, expDate){
                    console.log('asdf');
                    app.voucherId = voucherId;
                    app.voucherCode = voucherCode;
                    app.expDate = expDate;
                    modalSetExpDate.show();
                },
                async submitExpDate(){
                    $('#btn_exp_date_cancel').hide();
                    $('#btn_exp_date_submit').hide();
                    $('#btn_submit_loading').show();

                    let data = {
                        'voucher_id': app.voucherId,
                        'exp_date': app.expDate,
                    };

                    await axios({
                        method: 'post',
                        url: '{{ route('admin.voucher.set_exp_date') }}',
                        data: data
                    }).catch(function (error){
                        if(error.response){
                            if(error.response.status === 400 || error.response.status === 404){
                                One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: error.response.data});
                            }
                            if(error.response.status === 500){
                                One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Maaf terjadi kesalahan!'});
                            }
                        }

                        $('#btn_exp_date_cancel').show();
                        $('#btn_exp_date_submit').show();
                        $('#btn_submit_loading').hide();

                        app.voucherId = 0;
                        app.voucherCode = '';
                        app.expDate = today;
                        modalSetExpDate.hide();
                        window.setTimeout( redirect, 2000 );
                    }).then(function (response) {
                        if(response.status === 200){
                            One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', message: 'Sukses setup Expired Date!'});
                            //refresh datatable
                            //close modal
                            //reset data
                            $('#btn_exp_date_cancel').show();
                            $('#btn_exp_date_submit').show();
                            $('#btn_submit_loading').hide();

                            app.voucherId = 0;
                            app.voucherCode = '';
                            app.expDate = today;
                            modalSetExpDate.hide();
                            window.setTimeout( redirect, 2000 );
                        }
                    });

                    $('#btn_exp_date_cancel').show();
                    $('#btn_exp_date_submit').show();
                    $('#btn_submit_loading').hide();
                    app.voucherId = 0;
                    app.voucherCode = '';
                    app.expDate = today;
                    modalSetExpDate.hide();
                    window.setTimeout( redirect, 2000 );
                },
            }
        });
    </script>

    <script>
        flatpickr('.js-flatpickr', {
            "locale": "id"  // locale for this instance only
        });

        let modalSendEmail = new bootstrap.Modal(document.getElementById('modal-send-email'), {'backdrop': 'static', 'keyboard' :false });
        let modalConfirmDelete = new bootstrap.Modal(document.getElementById('modal_confirm_delete'), {'backdrop': 'static', 'keyboard' :false });
        let modalSetExpDate = new bootstrap.Modal(document.getElementById('modal-set-exp-date'), {'backdrop': 'static', 'keyboard' :false });

        $(function() {
            let dataTable = $('#table_vouchers').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                responsive: false,
                saveState: true,
                ajax: {
                    url: '{!! route('admin.voucher.datatable') !!}'
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
                // order: [ [1, 'desc'] ],
                columns: [
                    { data: 'action', name: 'action', orderable: false, searchable: false, class: 'text-center'},
                    { data: 'owner', name: 'owner', class: 'text-center'},
                    { data: 'voucher_code', name: 'voucher_code', class: 'text-center',
                        render: function ( data, type, row ){
                            if ( type === 'display' || type === 'filter' ){
                                return data.toUpperCase();
                            }
                            return data;
                        }
                    },
                    { data: 'quantity', name: 'quantity', class: 'text-center', orderable: false, searchable: false},
                    { data: 'notes', name: 'notes', class: 'text-wrap text-center', orderable: false, searchable: false},
                    { data: 'status', name: 'status', class: 'text-center', orderable: false, searchable: false},
                    { data: 'expired_date', name: 'expired_date', class: 'text-center', orderable: false, searchable: false,
                        render: function ( data, type, row ){
                            if ( type === 'display' || type === 'filter' ){
                                if (data !== '-') {
                                    return moment(data).format('DD-MM-YYYY');
                                }
                            }
                            return data;
                        }
                    },
                    { data: 'created_at', name: 'created_at', class: 'text-center', orderable: true, searchable: false,
                        render: function ( data, type, row ){
                            if ( type === 'display' || type === 'filter' ){
                                return moment(data).format('DD-MM-YYYY');
                            }
                            return data;
                        }
                    },
                ]
            });
        });

        $(document).on('click', '.send-email-modal', function(){
            let id = $(this).data("id");

            sendEmailModal(id);
        });

        function sendEmailModal(id) {
            app.voucherId = id;

            modalSendEmail.show();
        }

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
                url:'{{ route('admin.coupon.delete') }}',
                data:{
                    '_token': '{{ csrf_token() }}',
                    'id': id
                },
                success:function(data){
                    if(data === 'Sukses'){
                        One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', timer: 2000, message: 'Coupon berhasil dihapus!'});
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
            window.location.href = '{{ route('admin.voucher.index') }}';
        }

        $(document).on('click', '.set-exp-modal', function(){
            let id = $(this).data("id");
            let name = $(this).data("code");
            let expDate = $(this).data('date');

            modalSetExp(id, name, expDate);
        });

        function modalSetExp(id, code, expDate){
            app.voucherId = id;
            app.voucherCode = code;
            app.expDate = expDate;

            modalSetExpDate.show();
        }
    </script>
@endsection
