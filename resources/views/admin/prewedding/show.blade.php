@extends('layouts.admin')

@section('title')
    <title>Detail Transaksi Paket</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Detail Transaksi Paket
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.prewedding.index') }}">Daftar Transaksi Paket</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Detail Transaksi Paket
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <div id="app" class="content">
        <div class="row">
            <div class="col-12 text-end">
                @if($data->is_from_application && (int) $data->status_id !== 10)
                    <form id="form_cancel_prewedding" method="post" action="{{ route('admin.prewedding.cancel') }}" class="d-inline-block"
                          onsubmit="return confirm('Apakah anda yakin ingin membatalkan transaksi paket ini?')">
                        @csrf
                        <input type="hidden" name="cancelled_prewedding_id" value="{{ $data->id }}">
                        <button type="submit" class="btn btn-danger">
                            <i class="fa fa-ban me-1"></i> Batalkan Transaksi Paket
                        </button>
                    </form>
                @endif
            </div>
        </div>
        <div class="block block-rounded">
            <div class="block-content block-content-full">
                <div class="row">
                    <div class="col-sm-6">
                        @include('partials._success')
                        @include('partials._error')
                    </div>
                </div>
                @if (count($errors))
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="row">
                    <div class="col-12">
                        <div class="space-y-3">
                            <div class="row">
                                <label class="col-sm-3 col-form-label" for="user_name">Kasir</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" value="{{ optional($data->employee)->name ?? '-' }}" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-sm-3 col-form-label" for="user_name">Nama Customer</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" value="{{ $data->member_name }}" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-sm-3 col-form-label" for="user_name">Nomor Telp Customer</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" value="{{ $data->member_phone }}" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-sm-3 col-form-label" for="user_name">Gender</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" value="{{ optional($data->member)->gender == 'L' ? 'Laki-Laki' : (optional($data->member)->gender == 'P' ? 'Perempuan' : '-') }}" readonly>
                                </div>
                            </div>

                            <div class="row">
                                <label class="col-sm-3 col-form-label" for="user_name">Paket</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" value="{{ optional($data->package)->name ?? '-' }}" readonly>
                                </div>
                            </div>

                            <div class="row">
                                <label class="col-sm-3 col-form-label" for="user_name">Transaksi</label>
                                <div class="col-sm-4">
                                    @if(!empty($data->transaction_id))
                                        <a href="{{ route('admin.transaction.show', ['id' => $invoice->id]) }}">{{ $trxCode }}</a>
                                    @else
                                        <span class="fw-bold">Belum Ada Transaksi</span>
                                    @endif
                                </div>
                            </div>

                            @if($data->is_from_application)
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="user_name">Bukti Pembayaran</label>
                                    <div class="col-sm-4">
                                        @if($data->payment_proof != null)
                                            <a href="{{ asset('storage/proofs/'. $data->payment_proof) }}" class="btn btn-primary" target="_blank"><i class="fa fa-fw fa-eye"></i> Lihat Bukti Pembayaran</a>
                                            @if($data->status_id == 3 || $data->status_id == 14)
                                                <form id="approve-form" method="post" action="{{ route('admin.prewedding.check_proof') }}">
                                                    @csrf
                                                    <input type="hidden" name="id" value="{{ $data->id }}">
                                                    <input type="hidden" name="status" id="status" value="13">
                                                    <input type="hidden" name="payment_proof" value="{{ $data->payment_proof }}">
                                                    <div class="d-flex flex-row mt-2">
                                                        <button type="button" id="approveBtn" class="btn btn-success">Approve</button>
                                                        <button type="button" id="rejectBtn" class="ms-2 btn btn-danger">Reject</button>
                                                    </div>
                                                </form>
                                            @elseif($data->status_id == 13)
                                                <div class="alert alert-success mt-2 mb-0">Bukti pembayaran sudah di-approve.</div>
                                            @endif
                                        @else
                                            <input type="text" class="form-control" value="Belum ada bukti pembayaran" readonly>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <div class="d-flex flex-row justify-content-between align-items-center">
                                <h4 class="fw-normal m-0">
                                    Detail Paket
                                </h4>

                                <button type="button" class="btn btn-primary" :disabled="(requiresProofReview && !paymentApproved) || checkSelected()" @click="openProcess">Proses</button>
                            </div>
                            <div class="alert alert-warning mt-3 mb-0" v-if="requiresProofReview && !paymentApproved">
                                Bukti pembayaran harus di-approve sebelum transaksi bisa diproses.
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="table-responsive w-100">
                                        <table
                                            class="table table-bordered table-striped table-vcenter js-dataTable-full"
                                            id="table_data">
                                            <thead>
                                            <tr>
                                                <th style="width: 5%;" class="text-center">
                                                </th>
                                                <th>Treatment/Addon</th>
                                                <th style="width: 15%;">Waktu Mulai</th>
                                                <th style="width: 15%;">Waktu Selesai</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr v-for="(item, index) in details" :key="item.id">
                                                <td class="text-center">
                                                    <input type="checkbox" v-model="item.selected" :disabled="item.status_id == 9">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control" v-model="item.name" readonly>
                                                </td>
                                                <td class="text-end">
                                                   <input type="text" class="form-control" v-model="item.start_time" readonly>
                                                </td>
                                                <td class="text-end">
                                                    <input type="text" class="form-control" v-model="item.end_time" readonly>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{--        <!-- Loading Div -->--}}
        {{--        <div class="loading" v-if="loading">--}}
        {{--            <div class="spinner-border text-info">--}}
        {{--                <span class="visually-hidden">Loading...</span>--}}
        {{--            </div>--}}
        {{--        </div>--}}
        {{--        <!-- End Loading Div -->--}}

        @include('admin.prewedding.process_modal')

        <div id="receipt" style="position: absolute; top: -1000px; left: -1000px;">
    </div>
@endsection

@section('styles')
    <!-- Page JS Plugins CSS -->
    <style>
        .modal {
            z-index: 1000 !important;
        }

        .modal-backdrop {
            z-index: 990 !important;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/bootstrap-fileinput/css/fileinput.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.min.css"
          crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('js/plugins/flatpickr/flatpickr.min.css') }}">
@endsection

@section('scripts')
    <!-- jQuery -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script src="{{ asset('js/vue.js') }}"></script>
    <script src="{{ asset('js/AutoNumeric.js') }}"></script>
    <script src="{{ asset('js/vue-autonumeric.js') }}"></script>
    <script src="{{ asset('js/plugins/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
    <script src="{{ asset('js/plugins/bootstrap-fileinput/js/fileinput.js') }}"></script>
    <script src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('js/plugins/flatpickr/flatpickr.min.js') }}"></script>

    <script type="text/x-template" id="select2-template">
        <select>
            <slot></slot>
        </select>
    </script>
    <script src="{{ asset('js/vueComponent-select2.js') }}"></script>

    <script>

        const app = new Vue({
            el: '#app',
            data: {
                package: null,
                loadDetail: false,
                details: @json($details),
                checkouts: [],
                employee: null,
                loading: false,
                requiresProofReview: @json((bool) $data->is_from_application),
                paymentApproved: @json((int) $data->status_id === 13),


                autonumericFormat: {
                    minimumValue: '-9999999999999',
                    maximumValue: '9999999999999',
                    digitGroupSeparator: '.',
                    decimalCharacter: ',',
                    decimalPlaces: 2,
                    modifyValueOnWheel: false,
                    unformatOnSubmit: true,
                    allowDecimalPadding: false,
                },
            },
            methods: {
                checkSelected(){
                    for(var i = 0; i < this.details.length; i++){
                        if(this.details[i].selected == true && this.details[i].status_id == 3){
                            return false;
                        }
                    }
                    return true;
                },

                openProcess(){
                    this.employee = null;
                    $('#employee').val('').trigger('change');
                    this.checkouts = [];
                    for(var i = 0; i < this.details.length; i++){
                        let item = this.details[i];
                        if(item.selected && item.status_id == 3){
                            this.checkouts.push({
                                detail_id: item.detail_id,
                                id: item.id,
                                type: item.type,
                                name: item.name,
                                partner_id: null,
                                room_id: null,
                                date: '{{ \Carbon\Carbon::today()->format('d-m-Y') }}',
                                time: null,
                                duration: item.duration,
                            });
                        }
                    }

                    $('#processModal').modal('show');

                    this.$nextTick(() => {
                        $('.date').flatpickr({
                            dateFormat: 'd-m-Y',
                        });
                        $('.time').flatpickr({
                            enableTime: true,
                            noCalendar: true,
                            dateFormat: "H:i",
                            time_24hr: true
                        });
                    });
                },

                validateProcess(){
                    if(this.employee == null){
                        return false;
                    }

                    for(var i = 0; i < this.checkouts.length; i++){
                        if(this.checkouts[i].partner_id == null || this.checkouts[i].room_id == null || this.checkouts[i].date == null || this.checkouts[i].time == null){
                            return false;
                        }
                    }

                    return true;
                },

                process(){
                    if(this.validateProcess()){
                        app.loading = true;

                        $.ajax({
                            url: '{{ route('admin.prewedding.process') }}',
                            type: 'post',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            },
                            data: {
                                header_id: {{ $data->id }},
                                employee_id: app.employee,
                                room_id: app.roomId,
                                details: app.checkouts,
                            },
                            success: function (response) {
                                if (response.success) {
                                    $('#processModal').modal('hide');
                                    app.details = response.details;

                                    setTimeout(function() {
                                        app.print(response.url);
                                    }, 200);

                                    $('#checkoutModal').modal('hide');
                                    One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', message: 'Berhasil Memproses'});
                                }
                                else {
                                    One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: response.message});
                                }
                            },
                            error: function (xhr, status, error) {
                                console.log(error);
                                One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: xhr.responseText});
                            },
                        }).always( function() {
                            app.loading = false;
                        });
                    } else {
                        One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Mohon isi semua informasi yang dibutuhkan!'});
                    }
                },

                async print(url) {
                    $('#receipt').html('<iframe src="'+url+'"></iframe>');
                },
                confirmProofReview(title, text, status) {
                    Swal.fire({
                        icon: 'info',
                        title: title,
                        text: text,
                        showDenyButton: true,
                        confirmButtonText: 'Yes',
                        denyButtonText: 'No',
                        showClass: {
                            popup: `
                              animate__animated
                              animate__fadeIn
                              animate__faster
                            `
                        },
                        hideClass: {
                            popup: `
                              animate__animated
                              animate__fadeOut
                              animate__faster
                            `
                        },
                        showLoaderOnConfirm: true,
                        input: "password",
                        inputAttributes: {
                            autocapitalize: "off",
                            placeholder: 'Password'
                        },
                        preConfirm: (password) => {
                            return fetch('{{ route('admin.check_password') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    password: password
                                }),
                            }).then(response => {
                                if (!response.ok) {
                                    Swal.showValidationMessage('Gagal memverifikasi password');
                                }

                                return response.json();
                            }).then((response) => {
                                if(!response.success){
                                    Swal.showValidationMessage('Password Salah');
                                }

                                return response;
                            }).catch((error) => {
                                Swal.showValidationMessage('Gagal : ' + error);
                            });
                        }
                    }).then(function (result) {
                        if(result.isConfirmed && result.value.success){
                            $('#status').val(status);
                            $('#approve-form')[0].submit();
                        }
                    });
                }
            },
            mounted(){
                $('#approveBtn').on('click', function (){
                    app.confirmProofReview('Approve bukti pembayaran', 'Apakah anda yakin ingin approve bukti pembayaran?', 13);
                });

                $('#rejectBtn').on('click', function (){
                    app.confirmProofReview('Reject bukti pembayaran', 'Apakah anda yakin ingin reject bukti pembayaran?', 14);
                });
            },
            watch: {

            }
        });
    </script>

    <script>
    </script>
@endsection
