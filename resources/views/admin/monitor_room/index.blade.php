@extends('layouts.admin')

@section('title')
    <title>Transaksi Baru</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Transaksi Baru
                    </h1>

                </div>
                <div class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3 d-flex justify-content-end">
                </div>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <div class="content position-relative" id="app">
        <p class="badge bg-danger fs-6" style="position:absolute; bottom: 18vh; right: 3vw; z-index: 25; border-radius: 100px" v-if="checkSelected() > 0">@{{ checkSelected() }}</p>
        <div class="card" style="position:absolute; bottom: 15vh; right: 4vw; z-index: 20; border-radius: 100px; overflow: hidden; cursor: pointer;
        box-shadow: 2px 2px 19px 6px rgba(0,0,0,0.36);
-webkit-box-shadow: 2px 2px 19px 6px rgba(0,0,0,0.36);
-moz-box-shadow: 2px 2px 19px 6px rgba(0,0,0,0.36);" v-if="checkSelected() > 0">
            <div class="card-body ps-3 pb-2 pt-3 pe-3" @click="startCheckout">
                <div class="d-flex flex-row justify-content-between align-items-center">
                    <h1 class="m-0"><i class="fa fa-fw fa-shopping-cart"></i></h1>
{{--                    <h5 class="m-0 fw-bold"><span class="text-info">@{{ checkSelected() }}</span> treatment/item terpilih</h5>--}}
{{--                    <button class="btn btn-primary" @click="startCheckout">Checkout</button>--}}
                </div>
            </div>
        </div>

        <div class="input-group mb-3">
            <span class="input-group-text" id="basic-addon1"><i class="fa fa-fw fa-search"></i></span>
            <input type="text" class="form-control" placeholder="Cari Treatment/Add-On" v-model="search">
        </div>

        <div class="row">
            <div class="col-lg-6" style="overflow-y: scroll; height: calc(100vh - 150px);">
                <div class="block block-rounded block-link-pop">
                    <div class="block-content block-content-full">
                        <div v-for="(item, index) in packages">
                            <h4>@{{ item.name }}</h4>
                            <div class="row">
                                <div style="cursor: pointer" class="col-sm-3 p-0 mb-5 custom-square-pos ms-3 position-relative" :class="{'bg-primary': package.picked, 'bg-gray': package.status == 2 }"
                                     v-for="(package, index) in item.packages" @click="pickItem(package)" v-if="search == '' || package.name.toLowerCase().includes(search.toLowerCase()) || package.code.toLowerCase().includes(search.toLowerCase())">
                                    <div style="aspect-ratio: 1/1; width: 100%; background-color: #2f2f2f">
                                        <img :src="package.img" style="width: 100%; height: 100%; object-fit: cover">
                                    </div>

                                    <div style="padding: 11px 12px; height: 110px" class="position-relative">
                                        <button class="btn btn-sm btn-danger" style="position: absolute; bottom: 5px; right: 5px;" @click.stop="removeItem(package)" v-if="package.qty > 0"><i class="fa fa-fw fa-minus-circle"></i> @{{ package.qty }}</button>

                                        <div class="d-flex flex-column justify-content-start align-items-start" :id="package.id">
                                            <span class="">@{{ package.code }}</span>
                                            <span style="font-size: 16px" class="fw-bold">@{{ package.name }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{--                                <div class="py-3">--}}
                        {{--                                    <span style="color: red">Terdapat tambahan <b>{{ (int) $configBuffer->value_int }}--}}
                        {{--                                            Menit</b>, untuk persiapan Customer</span>--}}
                        {{--                                </div>--}}
                        {{--                                <div class="row row-cols-auto">--}}
                        {{--                                    <div class="col mb-2" v-for="(item, index) in rooms" @click="openModal(item)">--}}
                        {{--                                        <div :class="item.element_class" :id="item.element_id" class="d-flex flex-column">--}}
                        {{--                                            <span style="font-size: 16px;" class="fw-bold">@{{ item.number }}</span>--}}
                        {{--                                            <span v-if="item.status_id == 6"><strong>P:</strong> @{{ item.package_name }}</span>--}}
                        {{--                                            <span v-if="item.status_id == 6"><strong>C:</strong> @{{ item.member_name }}</span>--}}
                        {{--                                            <span v-if="item.status_id == 6"><strong>T:</strong> @{{ item.therapists }}</span>--}}

                        {{--                                            <div class="mt-auto">--}}
                        {{--                                                <div v-if="item.timer > 0">@{{ item.timer }}m</div>--}}
                        {{--                                                <div v-if="item.trx_status === 8 && item.timer <= 0" class="close-text">Waktu Habis</div>--}}
                        {{--                                                <div v-if="item.time_display !== ''">@{{ item.time_display }}</div>--}}
                        {{--                                            </div>--}}

                        {{--                                            <div class="mt-auto" v-if="item.status_id == 4">--}}
                        {{--                                                <button type="button" class="btn btn-primary w-100" @click="setAvailableModal(item)">Set Available</button>--}}
                        {{--                                            </div>--}}
                        {{--                                        </div>--}}
                        {{--                                    </div>--}}
                        {{--                                </div>--}}
                    </div>
                </div>
            </div>
            <div class="col-lg-6" style="overflow-y: scroll; height: calc(100vh - 150px);">
                <div class="block block-rounded block-link-pop">
                    <div class="block-content block-content-full">
                        <div v-for="(item, index) in items">
                            <h4>@{{ item.name }}</h4>
                            <div class="row">
                                <div style="cursor: pointer" class="col-sm-3 p-0 mb-5 custom-square-pos position-relative n ms-3" :class="{'bg-primary': item2.picked, 'bg-gray': item2.status == 2 }"
                                     v-for="(item2, index) in item.items" @click="pickItem(item2)" v-if="search == '' || item2.name.toLowerCase().includes(search.toLowerCase()) || item2.code.toLowerCase().includes(search.toLowerCase())">
                                    <div style="aspect-ratio: 1/1; width: 100%; background-color: #2f2f2f">
                                        <img :src="item2.img" style="width: 100%; height: 100%; object-fit: cover">
                                    </div>

                                    <div style="padding: 11px 12px; height: 110px" class="position-relative">
                                        <button class="btn btn-sm btn-danger" style="position: absolute ; bottom: 5px; right: 5px;" @click.stop="removeItem(item2)" v-if="item2.qty > 0"><i class="fa fa-fw fa-minus-circle"></i> @{{ item2.qty }}</button>

                                        <div class="d-flex flex-column align-items-start" :id="item2.id">
                                            <span class="">@{{ item2.code }}</span>
                                            <span style="font-size: 16px" class="fw-bold">@{{ item2.name }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{--                                <div class="py-3">--}}
                        {{--                                    <span style="color: red">Terdapat tambahan <b>{{ (int) $configBuffer->value_int }}--}}
                        {{--                                            Menit</b>, untuk persiapan Customer</span>--}}
                        {{--                                </div>--}}
                        {{--                                <div class="row row-cols-auto">--}}
                        {{--                                    <div class="col mb-2" v-for="(item, index) in rooms" @click="openModal(item)">--}}
                        {{--                                        <div :class="item.element_class" :id="item.element_id" class="d-flex flex-column">--}}
                        {{--                                            <span style="font-size: 16px;" class="fw-bold">@{{ item.number }}</span>--}}
                        {{--                                            <span v-if="item.status_id == 6"><strong>P:</strong> @{{ item.package_name }}</span>--}}
                        {{--                                            <span v-if="item.status_id == 6"><strong>C:</strong> @{{ item.member_name }}</span>--}}
                        {{--                                            <span v-if="item.status_id == 6"><strong>T:</strong> @{{ item.therapists }}</span>--}}

                        {{--                                            <div class="mt-auto">--}}
                        {{--                                                <div v-if="item.timer > 0">@{{ item.timer }}m</div>--}}
                        {{--                                                <div v-if="item.trx_status === 8 && item.timer <= 0" class="close-text">Waktu Habis</div>--}}
                        {{--                                                <div v-if="item.time_display !== ''">@{{ item.time_display }}</div>--}}
                        {{--                                            </div>--}}

                        {{--                                            <div class="mt-auto" v-if="item.status_id == 4">--}}
                        {{--                                                <button type="button" class="btn btn-primary w-100" @click="setAvailableModal(item)">Set Available</button>--}}
                        {{--                                            </div>--}}
                        {{--                                        </div>--}}
                        {{--                                    </div>--}}
                        {{--                                </div>--}}
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

        @include('admin.monitor_room.checkout_modal')

        <div class="loading" v-if="loading">
            <div class="spinner-border text-info">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <div id="receipt" v-if="paymentReceipt != null" style="position: absolute; top: -1000px; left: -1000px;">
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
                packages: @json($packageArr),
                items: @json($itemArr),
                details: [],

                countries: @json($countries),
                employee: {{ $oldMainData['exist'] ? ($oldMainData['employee'] != '' ? $oldMainData['employee'] : 'null') : 'null' }},
                bookingName: '{{ $oldMainData['exist'] ? $oldMainData['name'] : '' }}',
                phoneCode: '{{ $oldMainData['exist'] ? $oldMainData['phone_code'] : '+62' }}',
                country: '{{ $oldMainData['exist'] ? $oldMainData['country'] : 'ID' }}',
                bookingMemberPhone: '{{ $oldMainData['exist'] ? $oldMainData['phone'] : '' }}',
                gender: '{{ $oldMainData['exist'] ? $oldMainData['gender'] : 'L' }}',
                existingCustomers: [],
                {{--roomId: {{ $oldMainData['exist'] ? ($oldMainData['room_id'] != '' ? $oldMainData['room_id'] : 'null') : 'null' }},--}}
                date: '{{ $oldMainData['exist'] ? $oldMainData['date'] : '' }}',
                time: '{{ $oldMainData['exist'] ? $oldMainData['time'] : '' }}',
                {{--duration: {{ $oldMainData['exist'] ? $oldMainData['duration'] : 0 }},--}}
                total_cust_male: 0,
                total_cust_female: 0,

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
                discountPercent: 0,
                discountAmount: 0,
                amountReceived: 0,
                selectedPaymentMethod: 0,
                customAmount: 0,

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
                        if(!this.details[i].isFreeAddOn){
                            let subtotal = (this.details[i].price * this.details[i].qty) - this.details[i].discount;
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
                roomId() {
                    debouncePusher(function() {
                        app.updateSession();
                    }, 250);
                },
                date() {
                    debouncePusher(function() {
                        app.updateSession();
                    }, 250);
                },
                time() {
                    debouncePusher(function() {
                        app.updateSession();
                    }, 250);
                },
                duration() {
                    debouncePusher(function() {
                        app.updateSession();
                    }, 250);
                },
                items: {
                    handler: function(newStudents, oldStudents) {
                        debouncePusher(function() {
                            app.updateSession();
                        }, 250);
                    },
                    deep: true, // Enable deep watching
                },
                packages: {
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

                    for(var i = 0; i < this.items.length; i++){
                        let cat = this.items[i];
                        for(var j = 0; j < cat.items.length; j++){
                            if(cat.items[j].picked){
                                count += cat.items[j].qty;
                            }
                        }
                    }

                    for(var i = 0; i < this.packages.length; i++){
                        let cat = this.packages[i];
                        for(var j = 0; j < cat.packages.length; j++){
                            if(cat.packages[j].picked){
                                count += cat.packages[j].qty;
                            }
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
                    // this.roomId = null;
                    // $('#roomId').val('').trigger('change');
                    // this.date = '';
                    // this.time = '';
                    // this.duration = 0;

                    this.voucherCode = '';
                    this.messageCheckVoucher = '';
                    this.successCheckVoucher = false;
                    this.isUseVoucherCode = 'true';
                    this.voucherList = [{
                        code: '',
                        isSuccess: 0,
                        isDisable: 0,
                        informationStatus: '',
                        amount: 0,
                    }];
                    this.amountReceived = 0;
                    this.customAmount = 0;

                    this.selectedPaymentMethod = null;
                    $('#paymentMethod').val('').trigger('change');

                    for(var i = 0; i < this.packages.length; i++){
                        let cat = this.packages[i];
                        for(var j = 0; j < cat.packages.length; j++){
                            if(cat.packages[j].picked){
                                if(cat.packages[j].type == 'item'){
                                    if(cat.packages[j].need_partner) {
                                        for(var k = 0; k < cat.packages[j].qty; k++){
                                            var newCart = $.parseJSON(JSON.stringify(cat.packages[j]));
                                            newCart.from = 'package';
                                            newCart.uid = makeid(10);
                                            newCart.qty = 1;

                                            this.details.push(newCart);
                                            if(cat.packages[j].item != null){
                                                var newItem = $.parseJSON(JSON.stringify(cat.packages[j].item));
                                                newItem.uid = makeid(10);
                                                newItem.parentUid = newCart.uid;
                                                this.details.push(newItem);
                                            }
                                        }
                                    }
                                    else {
                                        var newCart = $.parseJSON(JSON.stringify(cat.packages[j]));
                                        newCart.from = 'package';
                                        newCart.uid = makeid(10);

                                        this.details.push(newCart);
                                        if(cat.packages[j].item != null){
                                            var newItem = $.parseJSON(JSON.stringify(cat.packages[j].item));
                                            newItem.uid = makeid(10);
                                            newItem.parentUid = newCart.uid;
                                            this.details.push(newItem);
                                        }
                                    }
                                } else {
                                    for(var k = 0; k < cat.packages[j].qty; k++){
                                        var newCart = $.parseJSON(JSON.stringify(cat.packages[j]));
                                        newCart.from = 'package';
                                        newCart.uid = makeid(10);
                                        newCart.qty = 1;

                                        this.details.push(newCart);
                                        if(cat.packages[j].item != null){
                                            var newItem = $.parseJSON(JSON.stringify(cat.packages[j].item));
                                            newItem.uid = makeid(10);
                                            newItem.parentUid = newCart.uid;
                                            this.details.push(newItem);
                                        }
                                    }
                                }
                            }
                        }
                    }

                    for(var i = 0; i < this.items.length; i++){
                        let cat = this.items[i];
                        for(var j = 0; j < cat.items.length; j++){
                            if(cat.items[j].picked){
                                var newCart = $.parseJSON(JSON.stringify(cat.items[j]));
                                newCart.from = 'item';
                                newCart.uid = makeid(10);

                                this.details.push(newCart);
                                if(cat.items[j].item != null){
                                    var newItem = $.parseJSON(JSON.stringify(cat.items[j].item));
                                    newItem.parentUid = newCart.uid;
                                    this.details.push(newItem);
                                }
                            }
                        }
                    }

                    $('#checkoutModal').modal('show');
                    {{--$('#paymentMethod').select2({--}}
                    {{--    placeholder: {--}}
                    {{--        id: '-1',--}}
                    {{--        text: ' - Pilih Metode Pembayaran - '--}}
                    {{--    },--}}
                    {{--    width: '100%',--}}
                    {{--    minimumInputLength: 0,--}}
                    {{--    ajax: {--}}
                    {{--        url: '{{ route('admin.pos.select') }}',--}}
                    {{--        dataType: 'json',--}}
                    {{--        data: function (params) {--}}
                    {{--            return {--}}
                    {{--                search: $.trim(params.term),--}}
                    {{--            };--}}
                    {{--        },--}}
                    {{--        processResults: function (data) {--}}
                    {{--            return {--}}
                    {{--                results: data--}}
                    {{--            };--}}
                    {{--        }--}}
                    {{--    }--}}
                    {{--}).on("select2:select", function(e) {--}}
                    {{--    // this.selectedPaymentMethod = e.params.data.id;--}}
                    {{--});--}}
                    // this.$nextTick(() => {
                    //     $('#paymentMethod').select2();
                    // });

                    setTimeout(function (){
                        flatpickr('#date', {
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

                removeCart(item){
                    if(item.from == 'package'){
                        for(var i = 0; i < this.packages.length; i++){
                            let cat = this.packages[i];
                            for(var j = 0; j < cat.packages.length; j++){
                                if(cat.packages[j].id == item.id){
                                    if(cat.packages[j].type == 'item'){
                                        cat.packages[j].qty = 0;
                                        cat.packages[j].picked = false;
                                    } else {
                                        cat.packages[j].qty--;
                                        if(cat.packages[j].qty <= 0){
                                            cat.packages[j].qty = 0;
                                            cat.packages[j].picked = false;
                                        }
                                    }
                                    break;
                                }
                            }
                        }
                    } else {
                        for(var i = 0; i < this.items.length; i++){
                            let cat = this.items[i];
                            for(var j = 0; j < cat.items.length; j++){
                                if(cat.items[j].id == item.id){
                                    if(cat.items[j].type == 'item'){
                                        cat.items[j].qty = 0;
                                        cat.items[j].picked = false;
                                    } else {
                                        cat.items[j].qty--;
                                        if(cat.items[j].qty <= 0){
                                            cat.items[j].qty = 0;
                                            cat.items[j].picked = false;
                                        }
                                    }
                                    break;
                                }
                            }
                        }
                    }

                    this.details = this.details.filter(function(x) {
                        return x !== item;
                    });

                    this.details = this.details.filter(function(x) {
                        return x.parentUid != item.uid;
                    });

                    if(this.details.length == 0){
                        $('#checkoutModal').modal('hide');
                    }
                },

                updateSession(){
                     var cart = [];

                    for(var i = 0; i < this.packages.length; i++){
                        let cat = this.packages[i];
                        for(var j = 0; j < cat.packages.length; j++){
                            if(cat.packages[j].picked){
                                cart.push($.parseJSON(JSON.stringify(cat.packages[j])));
                            }
                        }
                    }

                    for(var i = 0; i < this.items.length; i++){
                        let cat = this.items[i];
                        for(var j = 0; j < cat.items.length; j++){
                            if(cat.items[j].picked){
                                cart.push($.parseJSON(JSON.stringify(cat.items[j])));
                            }
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
                        room_id: this.roomId,
                        date: this.date,
                        time: this.time,
                        duration: this.duration,
                    };

                    $.ajax({
                        url: '{{ route('admin.monitor_room.session') }}',
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


                getPrice(item){
                    item.initiated = false;

                    $.ajax({
                        url: '{{ route('admin.item.price') }}',
                        type: 'get',
                        data: {
                            id: item.item_id,
                        },
                        success: function (response) {
                            item.is_unlimited = response.is_unlimited;
                            item.price = response.price;
                            item.stock = response.stock;
                            item.initiated = true;
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

                async checkVoucher(item)
                {
                    if(item.voucher != '') {
                        item.checking = true;

                        $.ajax({
                            url: '{{ route('admin.voucher.check') }}',
                            type: 'get',
                            data: {
                                code: item.voucher,
                                phone: app.phoneCode + '' + app.bookingMemberPhone,
                                package: item.id
                            },
                            success: function(response) {
                                if (response.success) {
                                    item.voucher_name = response.voucher.code;
                                    if(response.type == 'voucher'){
                                        if (response.voucher.discount_percentage != null) {
                                            item.discount = parseInt(response.voucher.discount_percentage) * (item.price * item.qty) / 100;

                                        } else if (response.voucher.discount_amount != null) {
                                            item.discount = parseInt(response.voucher.discount_amount);

                                        } else if (response.voucher.discount_duration != null) {

                                            // amount = app.calculateAmountDiscount(response.voucher
                                            //     .discount_duration_amount);
                                            //
                                            // app.discountAmount =
                                            //     parseInt(app.discountAmount) +
                                            //     amount; // use discount_duration_amount
                                            //
                                            // app.voucherList[index].type = 'duration';
                                        }
                                    }
                                    else {
                                        item.discount = item.price * item.qty;
                                    }

                                    if (app.selectedPaymentMethod !== 0) {
                                        app.amountReceived = app.grandTotalPayment;
                                    }
                                } else {
                                    One.helpers('jq-notify', {
                                        type: 'danger',
                                        icon: 'fa fa-info-circle me-1',
                                        message: response.message
                                    });
                                    return; // Hentikan eksekusi jika duplikat
                                }
                            },
                            error: function(xhr, status, error) {
                                console.log(error);
                            },
                        }).always( function() {
                            item.checking = false;
                        });
                    }
                },

                removeVoucher(item){
                    item.voucher = '';
                    item.voucher_name = '';
                    item.discount = 0;
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

                // async removeVoucher() {
                //     this.discountPercent = 0;
                //     this.voucherList = [{
                //         code: '',
                //         isSuccess: 0,
                //         isDisable: 0,
                //         informationStatus: '',
                //         amount: 0,
                //     }];
                // },

                validate(){
                    this.validateMessages = [];
                    if(this.employee == null || this.bookingName == '' || this.bookingMemberPhone == ''
                        || this.selectedPaymentMethod == 0 || this.date == '' || this.time == ''){

                        if(this.employee == null) {
                            this.validateMessages.push('- Kasir belum dipilih');
                        }

                        if(this.roomId == null) {
                            this.validateMessages.push('- Ruangan belum dipilih');
                        }

                        if(this.bookingName == '') {
                            this.validateMessages.push('- Member Name belum dipilih');
                        }

                        if(this.bookingMemberPhone == '') {
                            this.validateMessages.push('- Member Phone Number belum dipilih');
                        }

                        if(this.date == '') {
                            this.validateMessages.push('- Tanggal belum dipilih');
                        }

                        if(this.time == '') {
                            this.validateMessages.push('- Jam Mulai belum dipilih');
                        }

                        if(this.duration == 0) {
                            this.validateMessages.push('- Durasi belum dipilih');
                        }

                        if(this.selectedPaymentMethod == 0) {
                            this.validateMessages.push('- Metode Pembayaran belum dipilih');
                        }
                    }

                    if(this.total_cust_male == 0 && this.total_cust_female == 0){
                        this.validateMessages.push('- Total Tamu belum dipilih');
                    }

                    if(Math.round(this.amountReceived) < Math.round(this.grandTotalPayment)){
                        this.validateMessages.push('- Total Pembayaran tidak sesuai dengan total transaksi');
                    }

                    for(var i = 0; i < this.details.length; i++){
                        if((this.details[i].type == 'package' || this.details[i].need_partner) && (this.details[i].partner_id == null || this.details[i].partner_id == '')){
                            this.validateMessages.push('- Ada Therapist yang belum dipilih');
                            break;
                        }
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
                            url: '{{ route('admin.monitor_room.checkout') }}',
                            type: 'post',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            },
                            data: {
                                employee: app.employee,
                                room_id: app.roomId,
                                member_name: app.bookingName,
                                member_phone: app.phoneCode + "" + app.bookingMemberPhone,
                                country: app.country,
                                gender: app.gender,
                                date: app.date,
                                time: app.time,
                                duration: app.duration,
                                vouchers: app.voucherList,
                                selected_payment_method: app.selectedPaymentMethod,
                                amount_received: app.amountReceived,
                                return_value: app.returnValue,
                                is_use_voucher_code: app.isUseVoucherCode,
                                subtotal: app.subTotalPayment,
                                pb2: app.pb2,
                                custom_amount: app.customAmount,
                                grandtotal: app.grandTotalPayment,
                                total_cust_male: app.total_cust_male,
                                total_cust_female: app.total_cust_female,
                                details: app.details,
                            },
                            success: function (response) {
                                if (response.success) {
                                    app.paymentReceipt = response.receiptPayment;

                                    app.messageCheckVoucher = '';
                                    app.successSearchMember = '';
                                    app.messageSearchMember = '';

                                    app.details = [];
                                    app.bookingName = '';
                                    app.bookingMemberPhone = '';
                                    app.roomId = null;
                                    $('#roomId').val('').trigger('change');
                                    app.time = '';
                                    app.duration = 0;

                                    app.voucherCode = '';
                                    app.messageCheckVoucher = '';
                                    app.successCheckVoucher = false;
                                    app.isUseVoucherCode = 'true';
                                    app.voucherList = [{
                                        code: '',
                                        isSuccess: 0,
                                        isDisable: 0,
                                        informationStatus: '',
                                        amount: 0,
                                    }];
                                    app.amountReceived = 0;

                                    app.selectedPaymentMethod = null;
                                    $('#paymentMethod').val('').trigger('change');

                                    for(var i = 0; i < app.packages.length; i++){
                                        let cat = app.packages[i];
                                        for(var j = 0; j < cat.packages.length; j++){
                                            cat.packages[j].picked = false;
                                            cat.packages[j].qty = 0;
                                        }
                                    }

                                    for(var i = 0; i < app.items.length; i++){
                                        let cat = app.items[i];
                                        for(var j = 0; j < cat.items.length; j++){
                                            cat.items[j].picked = false;
                                            cat.items[j].qty = 0;
                                        }
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
                        One.helpers('jq-notify', {type: 'danger', icon: 'fa fa-info-circle me-1', message: 'Mohon lengkapi semua informasi yang dibutuhkan! ' + this.validateMessages.join("<br>")});
                    }
                },

                async print() {
                    $('#receipt').html('<iframe src="'+this.paymentReceipt+'"></iframe>');
                },
            },
        });
    </script>
@endsection
