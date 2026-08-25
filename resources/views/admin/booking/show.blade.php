@extends('layouts.admin')

@section('title')
    <title>Detil Booking</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Detil Booking
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.booking.index') }}">Daftar Booking</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Detil Booking
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->
    <div class="content" id="app">
        <div class="row items-push">
            <div class="col-md-12 col-xl-12">
                <div class="block block-rounded h-100 mb-0">
                    <div class="block-content block-content-full">
                        <div class="row">
                            <div class="col-12">
                                <div class="row">
                                    <div class="col-sm-12">
                                        @include('partials._success')
                                        @include('partials._error')
                                    </div>
                                </div>
                                @if(count($errors))
                                    <div class="row">
                                        <div class="col-lg-8">
                                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                <ul>
                                                    @foreach($errors->all() as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <div class="block-content">
                                    <div class="row mb-3">
                                        <div class="col-12 text-end">
                                            <button type="button" @click="showModalCancel()" class="btn btn-danger" v-if="status != 9 && status != 10">Batal Booking</button>

                                            <button type="button" class="btn btn-success" @click="startCheckout" v-if="status == 13">Finalisasi</button>

                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label" for="code">Status</label>
                                        <div class="col-sm-3">
                                            <input type="text" id="code" class="form-control fw-bold {{ $booking->status->display == 'Selesai' || $booking->status->display == 'Aktif' ? 'text-success' : ($booking->status->display == 'On Process' ? 'text-warning' : ($booking->status->display == 'Approved' ? 'text-info' : ($booking->status->display == 'Pending' ? 'text-dark' : 'text-danger'))) }}" readonly value="{{ strtoupper($booking->status->display) }}"/>                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label" for="code">Kode Booking</label>
                                        <div class="col-sm-6">
                                            <input type="text" id="code" class="form-control" readonly value="{{ $booking->code }}"/>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-3" for="code">Nomor Transaksi</div>
                                        <div class="col-sm-6" v-if="code == null">
                                            @if(!empty($booking->transaction_id))
                                                <a href="{{ route('admin.transaction.show', ['id' => $invoice]) }}">{{ $trxCode }}</a>
                                            @else
                                                <span class="fw-bold">Belum Ada Transaksi</span>
                                            @endif
                                        </div>
                                        <div class="col-sm-6" v-else>
                                            <a :href="detailUrl">@{{ code }}</a>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label" for="user_name">Nama Customer</label>
                                        <div class="col-sm-6">
                                            <input type="text" id="user_name" class="form-control" readonly value="{{ $booking->user_name }}"/>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label" for="user_phone">Nomor Telp Customer</label>
                                        <div class="col-sm-6">
                                            <input type="text" id="user_phone" class="form-control" readonly value="{{ $booking->user_phone }}"/>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label" for="user_phone">Gender</label>
                                        <div class="col-sm-6">
                                            <input type="text" id="user_phone" class="form-control" readonly value="{{ $booking->user->gender == 'L' ? 'Laki-Laki' : 'Perempuan' }}"/>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label" for="user_name">Tanggal Booking</label>
                                        <div class="col-sm-4">
                                            <input id="booking_date" type="text"
                                                   class="form-control" name="booking_date"
                                                   placeholder="Tanggal Booking" value="{{ \Carbon\Carbon::parse($booking->booking_date)->format('d-m-Y') }}" readonly/>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label" for="user_name">Jam Booking</label>
                                        <div class="col-sm-4">
                                            <input id="booking_time" type="text" class="form-control"
                                                   name="booking_time" placeholder="Jam Booking"
                                                   value="{{ $booking->booking_time }}" readonly/>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label">Catatan Customer</label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control" readonly value="{{ $booking->booking_notes }}"/>
                                        </div>
                                    </div>

{{--                                    <div class="row mb-3">--}}
{{--                                        <label class="col-sm-3 col-form-label" for="user_name">Durasi</label>--}}
{{--                                        <div class="col-sm-2">--}}
{{--                                            <div class="input-group">--}}
{{--                                                <input type="text" class="form-control" value="{{ $booking->duration }}" readonly>--}}
{{--                                                <span class="input-group-text" id="basic-addon2">Menit</span>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}

                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label" for="user_name">Bukti Pembayaran</label>
                                        <div class="col-sm-4">
                                            @if($booking->payment_proof != null)
                                                <a href="{{ asset('storage/proofs/'. $booking->payment_proof) }}" class="btn btn-primary" target="_blank"><i class="fa fa-fw fa-eye"></i> Lihat Bukti Pembayaran</a>
                                                @if($booking->status_id == 3 || $booking->status_id == 14)
                                                    <form id="approve-form" method="post" action="{{ route('admin.booking.check_proof') }}">
                                                        @csrf
                                                        <input type="hidden" name="id" value="{{ $booking->id }}">
                                                        <input type="hidden" name="status" id="status" value="13">
                                                        <div class="d-flex flex-row mt-2">
                                                            <button type="button" id="approveBtn" class="btn btn-success">Approve</button>
                                                            <button type="button" id="rejectBtn"  class="ms-2 btn btn-danger">Reject</button>
                                                        </div>
                                                    </form>
                                                @endif
                                            @else
                                                <input type="text" class="form-control" value="Belum ada bukti pembayaran" readonly>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label" for="is_membership_discount">Apakah pakai Diskon Membership?</label>
                                        <div class="col-sm-6">
                                            <input type="text" id="is_membership_discount" class="form-control" readonly
                                                value="{{ $booking->is_membership_discount ? 'YA' : 'TIDAK' }}"/>
                                        </div>
                                    </div>

                                    @if($booking->is_membership_discount)
                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label" for="membership_discount">Diskon Membership Tercatat</label>
                                            <div class="col-sm-2">
                                                <div class="input-group">
                                                    <input type="text" id="membership_discount_percentage" class="form-control text-end" readonly
                                                    value="{{ $booking->membership_discount_percentage }}"/>
                                                    <span class="input-group-text">%</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endif --}}
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <h5>Detil Booking</h5>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <table class="table table-bordered table-striped table-vcenter">
                                                <thead>
                                                <tr>
                                                    <td class="text-center">No</td>
                                                    <td class="text-center">Treatment/Addon</td>
                                                    <td class="text-center">Qty</td>
                                                    <td class="text-center">Harga</td>
                                                    <td class="text-center">Total</td>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @php
                                                    $no = 1;
                                                    $subTotal = (float) $booking->grand_total; // belum termasuk PB2
                                                    $pb2Rate = 10;

                                                    // hitung PB2 dari subtotal yang belum termasuk PB2
                                                    $pb2 = $subTotal * ($pb2Rate / 100);
                                                    $grandTotal = $subTotal + $pb2;
                                                @endphp
                                                @foreach($booking->booking_details as $detail)
                                                    <tr>
                                                        <td class="text-center">{{ $no }}</td>
                                                        <td class="text-center">
                                                            @if($detail->package_id != null)
                                                                {{ $detail->package->name }}
                                                            @elseif($detail->item_id != null)
                                                                {{ $detail->item->name }}
                                                            @endif
                                                        </td>
                                                        <td class="text-center">{{ $detail->qty }}</td>
                                                        <td class="text-end">{{ number_format($detail->price, 0, ',', '.') }}</td>
                                                        <td class="text-end">{{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                                    </tr>
                                                    @php
                                                        $no++;
                                                    @endphp
                                                @endforeach

                                                <tr>
                                                    <td colspan="4" class="text-end"><b>Subtotal</b></td>
                                                    <td class="text-end"><b>{{ number_format($subTotal, 0, ',', '.') }}</b></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4" class="text-end"><b>PB2 (10%)</b></td>
                                                    <td class="text-end"><b>{{ number_format($pb2, 0, ',', '.') }}</b></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4" class="text-end"><b>Grand Total</b></td>
                                                    <td class="text-end"><b>{{ number_format($grandTotal, 0, ',', '.') }}</b></td>
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
        </div>

        @include('admin.booking.checkout_modal')

        <div class="loading" v-if="loading">
            <div class="spinner-border text-info">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <div id="receipt" v-if="paymentReceipt != null" style="position: absolute; top: -1000px; left: -1000px;">
    </div>



    <form id="form_cancel_booking" method="post" action="{{ route('admin.booking.cancel') }}">
        @csrf
        <input type="hidden" id="cancelled_booking_id" name="cancelled_booking_id" value="{{ $booking->id }}">
    </form>
@endsection

@section('styles')
    <!-- Page JS Plugins CSS -->
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/sweetalert2/sweetalert2.css') }}">
    <link rel="stylesheet" href="{{ asset('css/monitor_data.css') }}">
    <style>
        .modal {
            z-index: 1000 !important;
        }

        .modal-backdrop {
            z-index: 990 !important;
        }
    </style>
@endsection

@section('scripts')
    <!-- jQuery -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script src="{{ asset('js/plugins/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('js/plugins/flatpickr/l10n/id.js') }}"></script>
    <script src="{{ asset('js/plugins/sweetalert2/sweetalert2.js') }}"></script>
    <script src="{{ asset('js/plugins/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
    <script src="{{ asset('js/vue.js') }}"></script>
    <script src="{{ asset('js/axios.js') }}"></script>
    <script src="{{ asset('js/AutoNumeric.js') }}"></script>
    <script src="{{ asset('js/vue-autonumeric.js') }}"></script>

    <script type="text/javascript" src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>

    <script type="text/x-template" id="select2-template">
        <select>
            <slot></slot>
        </select>
    </script>
    <script src="{{ asset('js/vueComponent-select2.js') }}"></script>

    <script>
        let app = new Vue({
            el: '#app',
            data: {
                {{--rooms: @json($roomArr),--}}
                status: {{ $booking->status_id }},
                loading: false,
                details: @json($details),

                employee: null,
                bookingName: '{{ $booking->user_name }}',
                bookingMemberPhone: '{{ $booking->user_phone }}',
                roomId: null,
                date: '{{ $booking->booking_date }}',
                time: '{{ $booking->booking_time }}',

                voucherCode: '',
                messageCheckVoucher: '',
                successCheckVoucher: false,
                isUseVoucherCode: 'true',
                voucherList: [{
                    code: '',
                    isSuccess: 0,
                    isDisable: 0,
                    informationStatus: '',
                    amount: 0,
                }],
                customAmount: 0,
                discountPercent: 0,
                discountAmount: 0,
                amountReceived: 0,
                selectedPaymentMethod: 0,

                isDropdownVisible: false,
                successSearchMember: false,
                messageSearchMember: '',
                paymentReceipt: null,

                code: null,
                detailUrl: null,

                autonumericFormat: {
                    minimumValue: '-9999999999999',
                    maximumValue: '9999999999999',
                    digitGroupSeparator: '.',
                    decimalCharacter: ',',
                    decimalPlaces: 0,
                    modifyValueOnWheel: false,
                    unformatOnSubmit: true,
                    allowDecimalPadding: false,
                },
            },
            mounted(){
                $('#approveBtn').on('click', function (){
                    Swal.fire({
                        icon: 'info',
                        title: 'Approve bukti pembayaran',
                        text: "Apakah anda yakin ingin approve bukti pembayaran?",
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
                                    Swal.showValidationMessage('Gagal : ' + error);
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
                        if(result.isConfirmed){
                            if(result.value.success){
                                app.startCheckout();
                            }
                        }
                    });
                });
                $('#rejectBtn').on('click', function (){
                    Swal.fire({
                        icon: 'info',
                        title: 'Reject bukti pembayaran',
                        text: "Apakah anda yakin ingin reject bukti pembayaran?",
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
                                    Swal.showValidationMessage('Gagal : ' + error);
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
                        if(result.isConfirmed){
                            if(result.value.success){
                                $('#status').val(14);
                                $('#approve-form')[0].submit();
                            }
                        }
                    });
                });


            },
            computed: {
                subTotalPayment: function() {
                    let total = 0;
                    let discount_membership = 0;

                    for (let i = 0; i < this.details.length; i++) {
                        if(!this.details[i].isFreeAddOn){
                            let subtotal = (this.details[i].price * this.details[i].qty);
                            total += subtotal;
                        }
                    }

                    return Math.round(total);
                },
                discountValue: function() {
                    let total = 0;
                    let applicableSubtotal = 0;

                    if (this.isUseVoucherCode) {
                        if (this.details.length > 0) {
                            for (let i = 0; i < this.details.length; i++) {
                                if (!this.details[i].isFreeAddOn) {
                                    applicableSubtotal += this.details[i].price;
                                }
                            }
                        }

                        if (this.discountPercent !== 0) {
                            total = applicableSubtotal * this.discountPercent / 100;
                        }

                        if (this.discountAmount !== 0) {
                            total = this.discountAmount;
                        }
                    }

                    return Math.round(total);
                },
                pb2: function() {
                    let total = 0;

                    let subtotal = this.subTotalPayment || 0;

                    total += subtotal;
                    total -= this.discountValue ?? 0;

                    return Math.round(total * 10/100);
                },
                grandTotalPayment: function() {
                    let total = 0;

                    let subtotal = this.subTotalPayment || 0;

                    total += subtotal;
                    total -= this.discountValue ?? 0;
                    total += this.pb2;
                    total += this.customAmount;

                    return Math.round(total);
                },
                returnValue: function() {
                    let total = 0;

                    let subtotal = this.grandTotalPayment || 0;

                    total += subtotal;
                    total = this.amountReceived - total;

                    return total >= 0 ? total : 0;
                },
            },
            watch: {
                isUseVoucherCode(newVal, oldVal) {
                    if (newVal !== oldVal) {
                        this.voucherCode = '';
                        // reset voucher list when isUseVoucherCode changed
                        this.voucherList = [{
                            code: '',
                            isSuccess: 0,
                            isDisable: 0,
                            informationStatus: '',
                            amount: 0,
                        }];

                        this.discountPercent = 0;
                        this.discountAmount = 0;
                    }
                },
                voucherCode(newVal, oldVal) {
                    if (newVal !== oldVal) {
                        app.discountPercent = 0;
                        app.discountAmount = 0;
                        app.successCheckVoucher = false;
                        app.messageCheckVoucher = '';
                    }
                },
                bookingName(newVal) {
                    this.fetchExistedCustomer(newVal);
                },
                isFreeAddOn(newVal, oldVal) {
                    if (newVal !== oldVal) {
                        if (newVal == true) {
                            this.detailAddOn.price = 0;
                            this.messageAddOnTherapist = '';
                        } else {
                            if (this.bookingTherapist !== null) {
                                this.getAddOnDetail(this.bookingTherapist);
                            }
                            if (this.selectedPartnerChange !== null) {
                                this.getAddOnDetail(this.selectedPartnerChange);
                            }
                        }
                    }
                },
                selectedPaymentMethod(newVal, oldVal) {
                    if (newVal !== oldVal) {
                        this.amountReceived = this.grandTotalPayment;
                    }
                },
            },
            methods: {
                showModalCancel(){
                    Swal.fire({
                        icon: 'warning',
                        title: 'Batalkan Booking',
                        text: 'Apakah Anda yakin ingin membatalkan booking ini?',
                        showCancelButton: true,
                        confirmButtonText: 'Ya',
                        cancelButtonText: `Tidak`,
                        confirmButtonColor: 'red',
                        cancelButtonColor: 'orange',
                        showLoaderOnConfirm: true,
                        input: 'password',
                        inputAttributes: {
                            autocapitalize: 'off',
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
                            }).then((response) => {
                                if (!response.ok) {
                                    Swal.showValidationMessage('Gagal memverifikasi password');
                                }

                                return response.json();
                            }).then((response) => {
                                if (!response.success) {
                                    Swal.showValidationMessage('Password Salah');
                                }

                                return response;
                            }).catch((error) => {
                                Swal.showValidationMessage('Gagal : ' + error);
                            });
                        }
                    }).then((result) => {
                        if(result.isConfirmed && result.value.success){
                            $('#form_cancel_booking').submit();
                        }
                        else if(result.isDismissed){
                            $('#btn_modal_assign').show();
                        }
                    });
                },

                startCheckout(){
                    this.messageCheckVoucher = '';
                    this.successSearchMember = '';
                    this.messageSearchMember = '';
                    this.customAmount = 0;

                    this.employee = null;
                    $('#employee').val('').trigger('change');
                    this.roomId = null;
                    $('#roomId').val('').trigger('change');

                    $('#checkoutModal').modal('show');
                },

                async checkVoucher(voucherCode, index)
                {
                    app.loading = true;
                    // Cek apakah kode voucher sudah ada di daftar voucherList
                    const isDuplicate = app.voucherList.some((v, i) => v.code === voucherCode && i !== index);

                    // if (isDuplicate) {
                    //     One.helpers('jq-notify', {
                    //         type: 'warning',
                    //         icon: 'fa fa-info-circle me-1',
                    //         message: 'Kode voucher sudah digunakan'
                    //     });
                    //     return; // Hentikan eksekusi jika duplikat
                    // }

                    // Jika tidak duplikat, jalankan AJAX
                    $.ajax({
                        url: '{{ route('admin.voucher.check') }}',
                        type: 'get',
                        data: {
                            code: voucherCode,
                            room_id: app.roomId,
                            voucher_list: app.voucherList,
                        },
                        success: function(response) {

                            if (response.success) {
                                let amount = 0;
                                if (response.voucher.discount_percentage != null) {
                                    app.discountPercent = parseInt(app.discountPercent) + parseInt(
                                        response.voucher
                                            .discount_percentage);

                                } else if (response.voucher.discount_amount != null) {
                                    amount = app.calculateAmountDiscount(response
                                        .voucher.discount_amount);
                                    app.discountAmount = parseInt(app.discountAmount) + amount;

                                } else if (response.voucher.discount_duration != null) {

                                    amount = app.calculateAmountDiscount(response.voucher
                                        .discount_duration_amount);

                                    app.discountAmount =
                                        parseInt(app.discountAmount) +
                                        amount; // use discount_duration_amount

                                    app.voucherList[index].type = 'duration';
                                }

                                app.voucherList[index].isSuccess = 1;
                                app.voucherList[index].amount = amount;
                                app.voucherList[index].informationStatus = response.message ?? '';
                                // app.addVoucher();

                                if (app.selectedPaymentMethod !== 0) {
                                    app.amountReceived = app.grandTotalPayment;
                                }
                            } else {
                                One.helpers('jq-notify', {
                                    type: 'danger',
                                    icon: 'fa fa-info-circle me-1',
                                    message: 'Kode Voucher Tidak Valid'
                                });
                                return; // Hentikan eksekusi jika duplikat
                            }
                        },
                        error: function(xhr, status, error) {
                            console.log(error);
                        },
                    }).always( function() {
                        app.loading = false;
                    });
                },

                async addVoucher() {
                    this.voucherList.push({
                        code: '',
                        isSuccess: 0,
                        isDisable: 0,
                        informationStatus: '',
                        amount: 0,
                    });
                },

                async removeVoucher() {
                    this.discountPercent = 0;
                    this.voucherList = [{
                        code: '',
                        isSuccess: 0,
                        isDisable: 0,
                        informationStatus: '',
                        amount: 0,
                    }];
                },

                validate(){
                    if(app.employee == null){
                        return false;
                    }

                    for(var i = 0; i < app.details.length; i++){
                        if(app.details[i].type == 'package' && (app.details[i].partner_id == null || app.details[i].partner_id == '')){
                            return false;
                        }
                    }

                    return true;
                },

                async checkout() {
                    if(this.validate()){
                        app.loading = true;

                        $.ajax({
                            url: '{{ route('admin.booking.finalize') }}',
                            type: 'post',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            },
                            data: {
                                booking: {{ $booking->id }},
                                employee: app.employee,
                                member_name: app.bookingName,
                                member_phone: app.bookingMemberPhone,
                                date: app.date,
                                time: app.time,
                                vouchers: app.voucherList,
                                selected_payment_method: app.selectedPaymentMethod,
                                amount_received: app.grandTotalPayment,
                                return_value: 0,
                                is_use_voucher_code: app.isUseVoucherCode,
                                subtotal: app.subTotalPayment,
                                custom_amount: app.customAmount,
                                grandtotal: app.grandTotalPayment,
                                details: app.details,
                            },
                            success: function (response) {
                                if (response.success) {
                                    app.paymentReceipt = response.receiptPayment;
                                    app.code = response.code;
                                    app.detailUrl = response.detail;
                                    app.status = 9;

                                    setTimeout(function() {
                                        app.print();
                                    }, 200);

                                    $('#checkoutModal').modal('hide');
                                    $('#approve-form').hide();
                                    One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', message: 'Transaksi berhasil'});
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
            },

            async print() {
                $('#receipt').html('<iframe src="'+this.paymentReceipt+'"></iframe>');
            },
        });
    </script>
@endsection
