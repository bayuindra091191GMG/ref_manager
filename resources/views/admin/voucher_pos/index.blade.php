@extends('layouts.admin')

@section('title')
    <title>Transaksi Voucher</title>
@endsection

@section('content')
    <!-- Hero -->
<div>
    <div class="content space-top">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
            <div class="flex-grow-1">
                <h1 class="h3 fw-bold mb-2">
                    Transaksi Voucher
                </h1>

            </div>
            <div class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3 d-flex justify-content-end">
            </div>
        </div>
    </div>
</div>
<!-- END Hero -->

<div class="content position-relative" id="app">
    <p class="badge bg-danger fs-6" style="position:absolute; bottom: 140px; right: 30px; z-index: 25; border-radius: 100px" v-if="checkSelected() > 0">@{{ checkSelected() }}</p>
    <div class="card" style="position:absolute; bottom: 120px; right: 40px; z-index: 20; border-radius: 100px; overflow: hidden; cursor: pointer;
    box-shadow: 2px 2px 19px 6px rgba(0,0,0,0.36);
-webkit-box-shadow: 2px 2px 19px 6px rgba(0,0,0,0.36);
-moz-box-shadow: 2px 2px 19px 6px rgba(0,0,0,0.36);" v-if="checkSelected() > 0">
        <div class="card-body ps-3 pb-2 pt-3 pe-3" @click="startCheckout">
            <div class="d-flex flex-row justify-content-between align-items-center">
                <h1 class="m-0"><i class="fa fa-fw fa-shopping-cart"></i></h1>
            </div>
        </div>
    </div>

    <div class="input-group mb-3">
        <span class="input-group-text" id="basic-addon1"><i class="fa fa-fw fa-search"></i></span>
        <input type="text" class="form-control" placeholder="Cari Treatment/Add-On" v-model="search">
    </div>

    <div class="row">
        <div class="col" style="overflow-y: scroll; height: calc(100vh - 150px);">
            <div class="block block-rounded block-link-pop">
                <div class="block-content block-content-full">
                    <h4>Voucher</h4>
                    <div class="row">
                        <div style="cursor: pointer" class="col-sm-2 p-0 mb-5 custom-square-pos ms-3 position-relative" :class="{'bg-primary': item.picked, 'bg-gray': item.status == 2 }"
                             v-for="(item, index) in vouchers" @click="pickItem(item)" v-if="search == '' || item.name.toLowerCase().includes(search.toLowerCase())">
                            <div style="aspect-ratio: 1/1; width: 100%; background-color: #2f2f2f">
                                <img :src="item.img" style="width: 100%; height: 100%; object-fit: cover">
                            </div>

                            <div style="padding: 11px 12px; height: 110px" class="position-relative">
                                <button class="btn btn-sm btn-danger" style="position: absolute; bottom: 5px; right: 5px;" @click.stop="removeItem(item)" v-if="item.qty > 0"><i class="fa fa-fw fa-minus-circle"></i> @{{ item.qty }}</button>

                                <div class="d-flex flex-column justify-content-start align-items-start" :id="item.id">
                                    <span class="">@{{ item.code }}</span>
                                    <span style="font-size: 16px" class="fw-bold">@{{ item.name }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="block block-rounded">
        <div class="block-content">
            <div class="row justify-content-center">
                <div class="col-lg-12 px-lg-1">
                </div>
            </div>
        </div>
    </div>

    @include('admin.voucher_pos.checkout_modal')

    <div class="loading" v-if="loading">
        <div class="spinner-border text-info">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <div id="receipt" v-if="paymentReceipt != null" style="position: absolute; top: -1000px; left: -1000px;">
    </div>
</div>

@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('css/monitor_data.css') }}"/>
<link rel="stylesheet" href="{{ asset('css/custom_loading.css') }}"/>
<link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('js/plugins/flatpickr/flatpickr.min.css') }}">
<style>
    .select2-container--default .select2-selection--multiple .select2-selection__choice{
        color: #000;
    }

    /*.modal-open .select2-dropdown {*/
    /*    z-index: 10060;*/
    /*}*/

    .select2-container {
        z-index: 1067;
    }

    /*.modal-open .select2-close-mask {*/
    /*    z-index: 10055;*/
    /*}*/

    .alert {
        z-index: 200000 !important;
    }

    .modal-open .dropdown-menu {
        z-index: 10080 !important;
    }

    .loading {
        z-index: 10081 !important;
    }
</style>
@endsection

@section('scripts')
<!-- Page JS Plugins -->
<script src="{{ asset('js/plugins/chart.js/Chart.min.js') }}"></script>
<script src="{{ asset('js/lib/jquery.min.js') }}"></script>
<!-- Page JS Code -->
<script src="{{ asset('js/pages/be_pages_dashboard.min.js') }}"></script>
<script src="{{ asset('js/plugins/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
<script src="{{ asset('js/vue.js') }}"></script>
<script src="{{ asset('js/axios.js') }}"></script>
<script src="{{ asset('js/AutoNumeric.js') }}"></script>
<script src="{{ asset('js/vue-autonumeric.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"></script>
<script src="{{ asset('js/plugins/flatpickr/flatpickr.min.js') }}"></script>

<script type="text/javascript" src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>

<script type="text/x-template" id="select2-template">
    <select>
        <slot></slot>
    </select>
</script>
<script src="{{ asset('js/vueComponent-select2.js') }}"></script>

<script>
    let pusherTimer;

    function debouncePusher(func, timeout = 300) {
        clearTimeout(pusherTimer);
        pusherTimer = setTimeout(func, timeout);
    }

    let app = new Vue({
        el: '#app',
        data: {
            {{--rooms: @json($roomArr),--}}
            search: '',
            loading: false,
            vouchers: @json($vouchers),
            details: [],

            countries: @json($countries),
            employee: {{ $oldMainData['exist'] ? ($oldMainData['employee'] != '' ? $oldMainData['employee'] : 'null') : 'null' }},
            bookingName: '{{ $oldMainData['exist'] ? $oldMainData['name'] : '' }}',
            phoneCode: '{{ $oldMainData['exist'] ? $oldMainData['phone_code'] : '+62' }}',
            country: '{{ $oldMainData['exist'] ? $oldMainData['country'] : 'null' }}',
            bookingMemberPhone: '{{ $oldMainData['exist'] ? $oldMainData['phone'] : '' }}',
            gender: '{{ $oldMainData['exist'] ? $oldMainData['gender'] : '' }}',
            expiredDate: '',
            existingCustomers: [],
            amountReceived: 0,
            customAmount: 0,
            selectedPaymentMethod: 0,

            isDropdownVisible: false,
            successSearchMember: false,
            messageSearchMember: '',
            paymentReceipt: null,

            validateMessages: [],

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

        },
        computed: {
            subTotalPayment: function() {
                let total = 0;
                let discount_membership = 0;

                for (let i = 0; i < this.details.length; i++) {
                    let subtotal = (this.details[i].price * this.details[i].qty);
                    total += subtotal;
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
                // else {
                //     if (this.transactionsPayment.length > 0) {
                //         for (let i = 0; i < this.transactionsPayment.length; i++) {
                //             for (let j = 0; j < this.transactionsPayment[i].details.length; j++) {
                //                 if (!this.transactionsPayment[i].details[j].is_add_on) {
                //                     applicableSubtotal += this.transactionsPayment[i].details[j].discount_membership;
                //                 }
                //             }
                //         }
                //     }
                //
                //     total = applicableSubtotal;
                // }

                return Math.round(total);
            },
            pb2: function() {
                let total = 0;

                let subtotal = this.subTotalPayment || 0;

                total += subtotal;
                total -= this.discountValue ?? 0;

                let pb2 = total * 10/100;

                return Math.round(pb2);
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
            selectedPaymentMethod(newVal, oldVal) {
                if (newVal !== oldVal) {
                    this.amountReceived = this.grandTotalPayment;
                }
            },

            employee() {
                debouncePusher(function() {
                    app.updateSession();
                }, 250);
            },
            bookingName() {
                this.fetchExistedCustomer(this.bookingName);
                debouncePusher(function() {
                    app.updateSession();
                }, 250);
            },
            bookingMemberPhone() {
                debouncePusher(function() {
                    app.updateSession();
                }, 250);
            },
            country() {
                debouncePusher(function() {
                    app.updateSession();
                }, 250);
            },
            phoneCode() {
                debouncePusher(function() {
                    app.updateSession();
                }, 250);
            },
            vouchers: {
                handler: function(newStudents, oldStudents) {
                    debouncePusher(function() {
                        app.updateSession();
                    }, 250);
                },
                deep: true, // Enable deep watching
            },
        },
        methods: {
            checkSelected(){
                var count = 0;

                for(var i = 0; i < this.vouchers.length; i++){
                    let cat = this.vouchers[i];
                    if(cat.picked){
                        count += cat.qty;
                    }
                }

                return count;
            },

            pickItem(item){
                if(item.status != 2){
                    item.qty++;
                    item.picked = true;
                }
            },

            removeItem(item){
                item.qty--;
                if(item.qty <= 0){
                    item.qty = 0;
                    item.picked = false;
                }
            },

            startCheckout(){
                this.messageCheckVoucher = '';
                this.successSearchMember = '';
                this.messageSearchMember = '';

                // this.employee = null;
                // $('#employee').val('').trigger('change');
                this.details = [];
                // this.bookingName = '';
                // this.bookingMemberPhone = '';

                this.amountReceived = 0;
                this.customAmount = 0;

                this.selectedPaymentMethod = null;
                $('#paymentMethod').val('').trigger('change');

                for(var i = 0; i < this.vouchers.length; i++){
                    let cat = this.vouchers[i];
                    if(cat.picked){
                        var newItem = $.parseJSON(JSON.stringify(cat));
                        newItem.uid = makeid(10);
                        this.details.push(newItem);
                    }
                }

                $('#checkoutModal').modal('show');

                setTimeout(function (){
                    flatpickr('.date', {
                        dateFormat: "d-m-Y", // Set time interval to 30 minutes
                    });

                    flatpickr('.js-flatpickr', {
                        enableTime: true,
                        noCalendar: true,
                        dateFormat: "H:i",
                        time_24hr: true,
                        locale: "id",
                        minTime: "10:00",
                        maxTime: "22:00",// Set time interval to 30 minutes
                    });
                }, 100);
            },

            updateSession(){
                var cart = [];

                for(var i = 0; i < this.vouchers.length; i++){
                    let cat = this.vouchers[i];
                    if(cat.picked){
                        cart.push($.parseJSON(JSON.stringify(cat)));
                    }
                }

                var data = {
                    exist: true,
                    employee: this.employee,
                    name: this.bookingName,
                    phone: this.bookingMemberPhone,
                    phone_code: this.phoneCode,
                    country: this.country,
                    gender: this.gender,
                };

                $.ajax({
                    url: '{{ route('admin.voucher_pos.session') }}',
                    type: 'post',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    data: {
                        cart: JSON.stringify(cart),
                        data: JSON.stringify(data)
                    },
                    success: function (response) {

                    },
                    error: function (xhr, status, error) {
                        console.log(error);
                    }
                });
            },

            async fetchExistedCustomer(query) {
                $.ajax({
                    url: '{{ route('admin.transaction.search.existing_customer') }}',
                    type: 'get',
                    data: {
                        search: query,
                    },
                    success: function(response) {
                        app.existingCustomers = response;
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                    }
                })
            },
            async selectExistingCustomer(customer) {
                app.bookingName = customer.customer_name;
                app.gender = customer.gender;

                if (customer.customer_phone !== null) {
                    for(var i = 0; i < app.countries.length; i++){
                        if(customer.country == app.countries[i].code){
                            app.phoneCode = app.countries[i].dial_code;
                            app.country = app.countries[i].code;
                            break;
                        }
                    }
                    app.bookingMemberPhone = customer.customer_phone.replace(app.phoneCode, '');
                    this.searchMember(customer.customer_phone);
                } else {
                    app.bookingMemberPhone = '';
                    app.messageSearchMember = 'Belum memiliki member!';
                    app.successSearchMember = false;
                }
            },

            async hideDropdown() {
                setTimeout(() => {
                    this.isDropdownVisible = false;
                }, 200);
            },

            async searchMember(phone) {
                $.ajax({
                    url: '{{ route('admin.user.search') }}',
                    type: 'get',
                    data: {
                        phone: phone,
                    },
                    success: function (response) {
                        app.bookingName = response.name;
                        app.messageSearchMember = response.message;
                        app.successSearchMember = response.success;
                    },
                    error: function (xhr, status, error) {
                        console.log(error);
                    }
                })
            },

            validate(){
                if(app.employee == null ||  app.bookingName == '' || app.bookingMemberPhone == ''
                    || app.selectedPaymentMethod == 0){
                    if(this.employee == null) {
                        this.validateMessages.push('- Kasir belum dipilih');
                    }

                    if(this.bookingName == '') {
                        this.validateMessages.push('- Member Name belum dipilih');
                    }

                    if(this.bookingMemberPhone == '') {
                        this.validateMessages.push('- Member Phone Number belum dipilih');
                    }

                    if(this.selectedPaymentMethod == 0) {
                        this.validateMessages.push('- Metode Pembayaran belum dipilih');
                    }
                }

                if(Math.round(app.amountReceived) < Math.round(app.grandTotalPayment)){
                    this.validateMessages.push('- Total Pembayaran tidak sesuai dengan total transaksi');
                }

                if(this.validateMessages.length > 0){
                    return false;
                }

                return true;
            },

            async checkout() {
                if(this.validate()){
                    app.loading = true;

                    $.ajax({
                        url: '{{ route('admin.voucher_pos.checkout') }}',
                        type: 'post',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        data: {
                            employee: app.employee,
                            member_name: app.bookingName,
                            member_phone: app.phoneCode + "" + app.bookingMemberPhone,
                            country: app.country,
                            gender: app.gender,
                            expired: app.expiredDate,
                            selected_payment_method: app.selectedPaymentMethod,
                            amount_received: app.amountReceived,
                            return_value: app.returnValue,
                            subtotal: app.subTotalPayment,
                            custom_amount: app.customAmount,
                            grandtotal: app.grandTotalPayment,
                            details: app.details,
                        },
                        success: function (response) {
                            if (response.success) {
                                app.paymentReceipt = response.receiptPayment;

                                app.successSearchMember = '';
                                app.messageSearchMember = '';

                                app.details = [];
                                app.bookingName = '';
                                app.bookingMemberPhone = '';
                                app.amountReceived = 0;

                                app.selectedPaymentMethod = null;
                                $('#paymentMethod').val('').trigger('change');

                                for(var i = 0; i < app.vouchers.length; i++){
                                    let cat = app.vouchers[i];
                                    cat.picked = false;
                                    cat.qty = 0;
                                }

                                setTimeout(function() {
                                    app.print();
                                }, 200);

                                $('#checkoutModal').modal('hide');
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
                    One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Mohon isi semua informasi yang dibutuhkan! ' + this.validateMessages.join("<br>")});
                }
            },

            async print() {
                $('#receipt').html('<iframe src="'+this.paymentReceipt+'"></iframe>');
            },
        },
    });
</script>
@endsection
