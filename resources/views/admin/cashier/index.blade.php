@extends('layouts.admin')

@section('title')
    <title>Cashier</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                       Locker - Kasir
                    </h1>
                </div>
                <div class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3 d-flex justify-content-end">
                    <div class='box green'></div>&nbsp;Available&nbsp;
                    <div class='box red ms-3'></div>&nbsp;Sedang Digunakan
                </div>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <div class="content" id="app">
        <div class="block block-rounded">
            <div class="block-content">
                <div class="row justify-content-center">
                    <div class="col-lg-12 px-lg-1">
                        <div class="block block-rounded block-link-pop">
                            <div class="block-content block-content-full">
                                <div class="row row-cols-auto">
                                    <div class="col mb-2" v-for="(item, index) in lockers">
                                        <div :class="item.element_class" :id="item.element_id">
                                            @{{ item.number }}<br/>
                                            <span v-if="item.gro_id !== 0">GRO: @{{ item.gro_code }}</span><br/>
                                            @{{ item.package_name }}<br/>
                                            <span v-if="item.room_number !== 0">Room: @{{ item.room_number }}</span>
                                        </div>
                                    </div>
                                </div>
{{--                                <div class="row" style="height: 400px;"></div>--}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
{{--        <div id="context-menu">--}}
{{--            <div class="item" v-for="(item, index) in menus" v-on:click="menuClicked(item)">--}}
{{--                <span>@{{ item }}</span>--}}
{{--            </div>--}}
{{--        </div>--}}
        <!-- END Pop In Treatment Modal -->
        @include('partials.cashier._pos')
    </div>
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ asset('css/monitor_data.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/custom_loading.css') }}"/>
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

    <script>
        let tmpLockers = {!! $jsonLockers !!};
        //console.log(tmpLockers);

        let app = new Vue({
            el: '#app',
            data: {
                deleteLoading: false,
                deleteMsg: '',
                deletePassword: '',
                selectedItem: null,
                fcLoading: false,
                lockers: tmpLockers,
                menus: [
                    'Detil',
                ],
                activeLocker: null,
                activeMenu: '',
                selectedLocker: null,
                memberSearch: {
                    code: '',
                    loading: false,
                    id: 0,
                    message: '',
                    notFound: true,
                },
                foundMemberCode: '',
                membership: [],
                selectedMembership: null,
                memberData: {
                    name: '',
                    phone: '',
                    email: '',
                    number: '',
                },
                membershipPagination: {
                    skip: 0,
                    take: 10,
                    search: '',
                    count: 0,
                    pages: [],
                    isLoading: false,
                },
                //POS
                gro: null,
                posMsg: '',
                foc: 'NO',
                focNumber: '',
                returnError: '',
                header: null,
                headerDiscount: 0,
                details: [],
                partners: [],
                packageId: 0,
                payment_type: [],
                trxItems: [],
                payments: [
                    {
                        id: 0,
                        amount: 0,
                        payment_id: 1,
                    }
                ],
                autonumericFormat: {
                    minimumValue: '0',
                    maximumValue: '9999999999',
                    digitGroupSeparator: '.',
                    decimalCharacter: ',',
                    decimalPlaces: 0,
                    modifyValueOnWheel: false,
                    allowDecimalPadding: false,
                },
            },
            computed: {
                grandTotal: function(){
                    let total = 0;
                    for(let i =0; i<this.details.length; i++){
                        total += this.details[i].subtotal;
                    }

                    return total - this.headerDiscount;
                    // return total;
                },
                // headerDiscount: function(){
                //     let totalDiscount = 0;
                //     for(let i =0; i<this.details.length; i++){
                //         totalDiscount += this.details[i].discount_amount;
                //     }
                //
                //     return totalDiscount;
                // },
                changeTotal: function(){
                    let change = 0;
                    let total = this.grandTotal;
                    let tmpAllPayment = 0;

                    for(let i=0; i<this.payments.length; i++){
                        tmpAllPayment += this.payments[i].amount;
                    }
                    if((tmpAllPayment - total) > 0){
                        change = tmpAllPayment - total;
                    }
                    return change;
                },
                grandTotalStr: function(){
                    let separator = '';
                    let angka = this.grandTotal + '';
                    let number_string = angka.replace(/[^,\d]/g, '').toString(),
                        split   		= number_string.split(','),
                        sisa     		= split[0].length % 3,
                        rupiah     		= split[0].substr(0, sisa),
                        ribuan     		= split[0].substr(sisa).match(/\d{3}/gi);

                    if(ribuan){
                        separator = sisa ? '.' : '';
                        rupiah += separator + ribuan.join('.');
                    }

                    rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
                    return 'Rp'+ rupiah + ',00';
                },
                changeTotalStr: function(){
                    let separator = '';
                    let angka = this.changeTotal + '';
                    let number_string = angka.replace(/[^,\d]/g, '').toString(),
                        split   		= number_string.split(','),
                        sisa     		= split[0].length % 3,
                        rupiah     		= split[0].substr(0, sisa),
                        ribuan     		= split[0].substr(sisa).match(/\d{3}/gi);

                    if(ribuan){
                        separator = sisa ? '.' : '';
                        rupiah += separator + ribuan.join('.');
                    }

                    //check the payment type
                    // let cash = this.payments.find(function (x) { return x.code === 'CASH'; });
                    // if(cash === undefined){
                    //     this.returnError = 'Jika kembalian Payment harus Cash';
                    // }
                    // else{
                    //     this.returnError = '';
                    // }

                    rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;

                    return 'Rp'+ rupiah + ',00';
                },
                extraItems: function(){
                    return this.details.filter(function (x) { return x.type === 'item'; });
                },
                isHappyHour: function(){
                    if(this.details != null){
                        let isHappyHour = false;
                        for(let i=0; i<this.details.length; i++){
                            if(this.details[i].discount_percentage === 10 && this.details[i].package_id !== 0){
                                isHappyHour = true;
                            }
                        }

                        return isHappyHour;
                    }
                },
            },
            methods: {
                getIndexNumberLocker(item){
                    let idx = 0;
                    for(let i=0; i<app.lockers.length; i++){
                        if(item.id === app.lockers[i].id){
                            idx = i;
                        }
                    }
                    return idx;
                },
                async menuClicked(item){
                    contextMenu.classList.remove("visible");
                    app.activeMenu = item;

                    app.selectedLocker = item;
                    if(item === 'Detil'){
                        //go to payment view
                        await app.getTransaction();
                    }
                },

                //POS Start
                async cancelShowTransaction(){
                    await axios({
                        method: 'get',
                        url: '{{ route('admin.pos.cancel_preview') }}?locker_id=' + app.activeLocker.id,
                    }).catch(function (error){
                        if(error.response){
                            if(error.response.status === 400 || error.response.status === 404){
                                One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: error.response.data});
                            }
                            if(error.response.status === 500){
                                One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Maaf terjadi kesalahan!'});
                            }
                        }
                    }).then(function (response) {
                        if(response.status === 200){
                        }
                    });
                },
                async getTransaction(){
                    await axios({
                        method: 'get',
                        url: '{{ route('admin.pos.get_transaction') }}?locker_id=' + app.activeLocker.id + '&is_showing=1',
                    }).catch(function (error){
                        if(error.response){
                            if(error.response.status === 400 || error.response.status === 404){
                                One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: error.response.data});
                            }
                            if(error.response.status === 500){
                                One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Maaf terjadi kesalahan!'});
                            }
                        }
                    }).then(function (response) {
                        if(response.status === 200){
                            app.header = response.data;
                            app.details = response.data.details;
                            app.partners = response.data.partners;
                            if(app.details.length > 0){
                                app.packageId = app.details[0].package_id;
                            }
                            app.gro = response.data.gro;
                            modalPos.show();
                        }
                    });
                },
                async getTransactionWithoutModal(){
                    await axios({
                        method: 'get',
                        url: '{{ route('admin.pos.get_transaction') }}?locker_id=' + app.activeLocker.id  + '&is_showing=1',
                    }).catch(function (error){
                        if(error.response){
                            if(error.response.status === 400 || error.response.status === 404){
                                One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: error.response.data});
                            }
                            if(error.response.status === 500){
                                One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Maaf terjadi kesalahan!'});
                            }
                        }
                    }).then(function (response) {
                        if(response.status === 200){
                            app.header = response.data;
                            app.details = response.data.details;
                            app.partners = response.data.partners;
                            if(response.data.details.length > 0){
                                app.packageId = response.data.details[0].package_id;
                            }
                            app.gro = response.data.gro;
                        }
                    });
                },
                async getPaymentTypes(){
                    await axios({
                        method: 'get',
                        url: '{{ route('admin.pos.get_payment_types') }}',
                    }).then(function (response) {
                        if(response.status === 200){
                            app.payment_type = response.data;
                            app.payment_id = app.payment_type[0].id;
                        }
                    });
                },
                async submitTransaction(){
                    $('#btn_finish_payment').hide();
                    $('#btn_add_more_payment').hide();
                    $('#payment_loading').show();
                    let membershipId = 0;
                    if(app.selectedMembership != null){
                        membershipId = app.selectedMembership.id;
                    }

                    if(app.header.gro == null){
                        alert('Pilih Gro terlebih dahulu! Lalu tutup window ini!');
                        $('#btn_finish_payment').show();
                        $('#btn_add_more_payment').show();
                        $('#payment_loading').hide();
                    }
                    else{
                        let data = {
                            locker_id: app.activeLocker.id,
                            trx_id: app.header.header.id,
                            payments: [],
                            items: [],
                            membership_id: membershipId,
                            member_data: app.memberData,
                            member_code: app.memberSearch.code,
                            change: app.changeTotal,
                            foc: app.foc,
                            foc_number: app.focNumber,
                            header_discount: app.headerDiscount,
                        };

                        let canSubmit = true;
                        let msg = 'Amount tidak boleh 0!';
                        let tmpTotal = 0;
                        for(let i=0; i<app.payments.length; i++){
                            if(app.payments[i].amount === 0){
                                canSubmit = false;
                            }
                            let nPayment = {
                                id: app.payments[i].payment_id,
                                amount: app.payments[i].amount,
                            };
                            tmpTotal += app.payments[i].amount;

                            data.payments.push(nPayment);
                        }

                        if(tmpTotal < app.grandTotal){
                            canSubmit = false;
                            msg = 'Jumlah Pembayaran tidak boleh kurang dari Total Transaksi!';
                        }

                        //check the payment type
                        // let cash = app.payments.find(function (x) { return x.code === 'CASH'; });
                        // if(cash === undefined){
                        //     app.returnError = 'Jika kembalian Payment harus Cash';
                        //     canSubmit = false;
                        //     msg = 'Payment dengan kembalian harus ada Cash!';
                        // }
                        // else{
                        //     app.returnError = '';
                        //     canSubmit = true;
                        // }

                        if(app.foc !== 'NO'){
                            canSubmit = true;
                        }

                        if(app.memberSearch.id !== 0){
                            canSubmit = true;
                        }

                        if(app.extraItems.length > 0){
                            //add extra items
                            let extraItems = app.extraItems;
                            for(let i=0; i<extraItems.length; i++){
                                let nItem = {
                                    id: extraItems[i].item_id,
                                    qty: extraItems[i].qty,
                                };

                                data.items.push(nItem);
                            }
                        }

                        if(!canSubmit){
                            alert(msg);
                            $('#btn_finish_payment').show();
                            $('#btn_add_more_payment').show();
                            $('#payment_loading').hide();
                        }
                        if(canSubmit){
                            await axios({
                                method: 'post',
                                url: '{{ route('admin.pos.submit') }}',
                                data: data,
                            }).catch(function (error){
                                if(error.response){
                                    if(error.response.status === 400 || error.response.status === 404){
                                        One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: error.response.data});
                                    }
                                    if(error.response.status === 500){
                                        One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Maaf terjadi kesalahan!'});
                                    }
                                }
                                $('#btn_finish_payment').show();
                                $('#btn_add_more_payment').show();
                                $('#payment_loading').hide();
                                //clean data
                                app.headerDiscount = 0;
                                app.header = null;
                                app.details = [];
                                app.payments = [];
                                app.items = [];
                                app.activeLocker = null;
                                app.memberData = {
                                    name: '',
                                    phone: '',
                                    email: '',
                                    number: '',
                                };

                                app.memberSearch.code = {
                                    code: '',
                                    loading: false,
                                    id: 0,
                                    message: '',
                                    notFound: true,
                                };

                                //close all the modal
                                modalPos.hide();
                                modalPayment.hide();
                            }).then(function (response) {
                                if(response.status === 200){
                                    //move to other tab then print
                                    let url = '{{ route('admin.pos.receipt') }}?id=' + app.header.header.id;
                                    window.open(url);
                                    One.helpers('jq-notify', {type: 'info', icon: 'fa fa-info-circle me-1', message: 'Sukses Submit Transaksi! Silahkan Print Struk!'});

                                    //clean data
                                    app.headerDiscount = 0;
                                    app.header = null;
                                    app.details = [];
                                    app.payments = [];
                                    app.items = [];
                                    app.activeLocker = null;
                                    app.memberData = {
                                        name: '',
                                        phone: '',
                                        email: '',
                                        number: '',
                                    };

                                    app.memberSearch.code = {
                                        code: '',
                                        loading: false,
                                        id: 0,
                                        message: '',
                                        notFound: true,
                                    };

                                    //close all the modal
                                    modalPos.hide();
                                    modalPayment.hide();
                                    $('#btn_finish_payment').show();
                                    $('#btn_add_more_payment').show();
                                    $('#payment_loading').hide();
                                }
                            });

                            app.foc = 'NO';
                            app.foc_number = '';
                            app.memberData = {
                                name: '',
                                phone: '',
                                email: '',
                                number: '',
                            };
                            app.memberSearch={
                                code: '',
                                loading: false,
                                id: 0,
                                message: '',
                                notFound: true,
                            },
                            await app.refreshData();
                        }
                    }
                },
                async refreshData(){
                    await axios({
                        method: 'get',
                        url: '{{ route('admin.monitor_locker.refresh_data') }}',
                    }).then(function (response) {
                        if(response){
                            if(response.status === 200){
                                app.lockers = response.data;
                            }
                            else{
                                console.log(response);
                            }
                        }
                    });
                },
                //POS End

                calculateTransaction(){
                    if(app.foc === 'FOC'){
                        for(let i=0; i<app.details.length; i++){
                            app.details[i].discount_percentage = 100;
                            app.details[i].discount_amount = app.details[i].subtotal;
                            app.details[i].discount_str = formatRupiah(app.details[i].discount_amount + '', '');
                            app.details[i].subtotal = app.details[i].subtotal - app.details[i].discount_amount;
                            app.details[i].subtotal_str = formatRupiah(app.details[i].subtotal + '', '');
                        }
                    }
                    else if(app.foc === 'FOC_PACKAGE'){
                        //Discount on Treatment
                        for(let i=0; i<app.details.length; i++){
                            if(app.details[i].type === 'package'){
                                app.details[i].discount_percentage = 100;
                                app.details[i].discount_amount = app.details[i].subtotal;
                                app.details[i].discount_str = formatRupiah(app.details[i].discount_amount + '', '');
                                app.details[i].subtotal = app.details[i].subtotal - app.details[i].discount_amount;
                                app.details[i].subtotal_str = formatRupiah(app.details[i].subtotal + '', '');
                            }
                            else{
                                app.details[i].discount_percentage = 0;
                                app.details[i].discount_amount = 0;
                                app.details[i].discount_str = formatRupiah(app.details[i].discount_amount + '', '');
                                app.details[i].subtotal = app.details[i].price * app.details[i].qty;
                                app.details[i].subtotal_str = formatRupiah(app.details[i].subtotal + '', '');
                            }
                        }
                    }
                    else if(app.foc === 'FOC_ITEM'){
                        //Discount on ITEM
                        for(let i=0; i<app.details.length; i++){
                            if(app.details[i].type === 'item'){
                                app.details[i].discount_percentage = 100;
                                app.details[i].discount_amount = app.details[i].subtotal;
                                app.details[i].discount_str = formatRupiah(app.details[i].discount_amount + '', '');
                                app.details[i].subtotal = app.details[i].subtotal - app.details[i].discount_amount;
                                app.details[i].subtotal_str = formatRupiah(app.details[i].subtotal + '', '');
                            }
                            else{
                                app.details[i].discount_percentage = 0;
                                app.details[i].discount_amount = 0;
                                app.details[i].discount_str = formatRupiah(app.details[i].discount_amount + '', '');
                                app.details[i].subtotal = app.details[i].price * app.details[i].qty;
                                app.details[i].subtotal_str = formatRupiah(app.details[i].subtotal + '', '');
                            }
                        }
                    }
                    else if(app.foc === 'NO'){
                        //Reset Transaction
                        for(let i=0; i<app.details.length; i++){
                            app.details[i].discount_percentage = 0;
                            app.details[i].discount_amount = 0;
                            app.details[i].discount_str = formatRupiah(app.details[i].discount_amount + '', '');
                            app.details[i].subtotal = app.details[i].price * app.details[i].qty;
                            app.details[i].subtotal_str = formatRupiah(app.details[i].subtotal + '', '');
                        }
                    }
                },
                async showModalPayment(){
                    //Check on FOC first
                    let canGoOn = true;
                    if(app.foc !== 'NO'){
                        if(app.focNumber === ''){
                            app.posMsg = 'Masukan Nomor FOC terlebih dahulu!';
                            canGoOn = false;
                        }
                    }
                    if(canGoOn){
                        app.posMsg = '';
                        await app.getPaymentTypes();

                        app.payments = [
                            {
                                id: 0,
                                amount: app.grandTotal,
                                amountStr: formatRupiah(app.grandTotal + ''),
                                payment_id: 1,
                                code: app.payment_type[0].code,
                            }
                        ];
                        modalPayment.show();
                    }
                },
                checkOnPayment(paymentId){
                    let tmp = app.payment_type.find(function (x) { return x.id === app.payments[paymentId].payment_id; });
                    app.payments[paymentId].code = tmp.code;
                },
                addMorePayment(){
                    let tmpGrandTotal = app.grandTotal;
                    tmpGrandTotal = tmpGrandTotal / (app.payments.length + 1);
                    let tmpData = [];

                    for(let i = 0; i< app.payments.length + 1; i++){
                        let tmp =
                            {
                                id: i,
                                amount: 0,
                                amountStr: '0',
                                payment_id: 1,
                                code: 'CASH',
                            };
                        if(i === 0){
                            tmp.amount = app.grandTotal;
                            tmp.amountStr = formatRupiah(app.grandTotal + '');
                        }
                        tmpData.push(tmp);
                    }
                    app.payments = tmpData;
                    app.payment_id = app.payment_type[0].id;
                },
                deletePayment(item){
                    this.payments = this.payments.filter(function (x) { return x !== item; });
                },
                calculateSubTotal(item){
                    let idx = app.details.indexOf(item);
                    item.subtotal = item.qty * item.price;
                    item.subtotal_str = formatRupiah(item.subtotal + '', '');
                    app.details[idx] = item;
                },
                calculateDiscount(item){
                    let idx = app.details.indexOf(item);
                    if(item.discount_percentage >= 100){
                        item.discount_percentage = 100;
                        item.discount_amount = item.price;
                        item.discount_str = formatRupiah(item.discount_amount + '', '');
                        item.subtotal = item.price - item.discount_amount;
                        item.subtotal_str = formatRupiah(item.subtotal+ '', '');
                    }
                    else{
                        item.discount_amount = (item.price * item.qty) * (item.discount_percentage/100);
                        item.discount_str = formatRupiah(item.discount_amount + '', '');
                        item.subtotal = (item.price * item.qty) - item.discount_amount;
                        item.subtotal_str = formatRupiah(item.subtotal+ '', '');
                    }

                    app.details[idx] = item;
                },
                //Item POS End

                //Membership Modals
                async showMembershipModal(){
                    await app.getMemberships();
                    modalMembership.show();
                },
                async getMemberships(){
                    app.membershipPagination.isLoading = true;
                    await axios({
                        method: 'get',
                        url: '{{ route('admin.membership.get') }}?skip=' + app.membershipPagination.skip + '&take=' + app.membershipPagination.take + '&search=' + app.membershipPagination.search,
                    }).then(function (response) {
                        if(response.status === 200){
                            app.membership = response.data.membership;
                            app.membershipPagination.count = response.data.count;
                            app.membershipPagination.pages = response.data.pages;
                        }
                    });
                    app.membershipPagination.isLoading = false;
                },
                async nextMembershipPage(){
                    if((app.membershipPagination.skip + 10) < app.membershipPagination.count){
                        app.membershipPagination.skip += 10;
                        await app.getMemberships();
                    }
                },
                async previousMembershipPage(){
                    if(app.membershipPagination.skip >= 10){
                        app.membershipPagination.skip -= 10;
                        await app.getMemberships();
                    }
                },
                showMemberDetilModal(membership){
                    app.selectedMembership = membership;
                    modalMemberData.show();
                },
                chooseMembership(){
                    //add membership item to transaction detil
                    let nDt = {
                        id: 7777,
                        type: 'membership',
                        package_name: '',
                        item_name: '',
                        membership_id: app.selectedMembership.id,
                        membership_name: app.selectedMembership.code,
                        qty: 1,
                        price: app.selectedMembership.price,
                        subtotal: app.selectedMembership.price,
                        price_str: formatRupiah(app.selectedMembership.price + '', ''),
                        subtotal_str: formatRupiah(app.selectedMembership.price + '', ''),
                    };
                    app.details.push(nDt);
                    modalMemberData.hide();
                    modalMembership.hide();
                },
                async checkMember(){
                    let data = {
                        locker_id: app.activeLocker.id,
                        member_code: app.memberSearch.code,
                    };

                    app.memberSearch.loading = true;

                    await axios({
                        method: 'post',
                        url: '{{ route('admin.member.check') }}',
                        data: data,
                    }).catch(function (error){
                        if(error.response){
                            if(error.response.status === 400 || error.response.status === 404){
                                app.memberSearch.message = error.response.data;
                            }
                            if(error.response.status === 500){
                                app.memberSearch.message = error.response.data;
                            }
                            app.memberSearch.notFound = true;
                            app.memberSearch.loading = false;
                            app.memberSearch = '';
                        }
                    }).then(function (response) {
                        //member is true
                        if(response.status === 200){
                            for(let i = 0; i < response.data.benefits.length; i++){
                                if(response.data.benefits[i].type === 'treatment'){
                                    let tmpDt = app.details.filter(function (x) { return x.type === 'package'; });
                                    if(tmpDt.length > 0){
                                        let discPercentage = response.data.benefits[i].discount_percentage;
                                        for(let h = 0; h < tmpDt.length; h++){
                                            if(h <= 1){
                                                let discAmount = (tmpDt[h].price * tmpDt[h].qty) * (discPercentage / 100);
                                                // app.headerDiscount += discAmount;
                                                if(tmpDt[h].package_name != 'Perpanjangan Waktu' && tmpDt[h].package_name != 'Penambahan Therapis' && tmpDt[h].package_name != 'Penambahan Customer'){
                                                    tmpDt[h].discount_percentage = discPercentage;
                                                    tmpDt[h].discount_amount = discAmount;
                                                    tmpDt[h].discount_str = formatRupiah(tmpDt[h].discount_amount + '', '');
                                                    tmpDt[h].subtotal = (tmpDt[h].price * tmpDt[h].qty) - tmpDt[h].discount_amount;
                                                    tmpDt[h].subtotal_str = formatRupiah(tmpDt[h].subtotal + '', '');
                                                }
                                                // tmpDt[h].discount_percentage = discPercentage;
                                                // tmpDt[h].discount_amount = discAmount;
                                                // tmpDt[h].discount_str = formatRupiah(tmpDt[h].discount_amount + '', '');
                                                // tmpDt[h].subtotal = (tmpDt[h].price * tmpDt[h].qty) - tmpDt[h].discount_amount;
                                                // tmpDt[h].subtotal_str = formatRupiah(tmpDt[h].subtotal + '', '');
                                            }
                                        }
                                    }
                                }
                                else if(response.data.benefits[i].type === 'item'){
                                    let tmpDt = app.details.filter(function (x) { return x.type == 'item'; });
                                    let discPercentage = response.data.benefits[i].discount_percentage;
                                    if(tmpDt.length > 0){
                                        for(let h = 0; h < tmpDt.length; h++){
                                            if(tmpDt[h].item_id === 138){
                                                let discAmount = (tmpDt[h].price * tmpDt[h].qty) * (discPercentage / 100);
                                                // app.headerDiscount += discAmount;

                                                tmpDt[h].discount_percentage = discPercentage;
                                                tmpDt[h].discount_amount = discAmount;
                                                tmpDt[h].discount_str = formatRupiah(tmpDt[h].discount_amount + '', '');
                                                tmpDt[h].subtotal = (tmpDt[h].price * tmpDt[h].qty) - tmpDt[h].discount_amount;
                                                tmpDt[h].subtotal_str = formatRupiah(tmpDt[h].subtotal + '', '');
                                                console.log(tmpDt[h].discount_percentage);
                                                console.log(tmpDt[h].discount_amount);
                                            }
                                            //else if(tmpDt[h].item_category_id === 2 || tmpDt[h].item_category_id === 4){
                                            else if(tmpDt[h].item_category_id == response.data.benefits[i].item_category_id){
                                                let discAmount = (tmpDt[h].price * tmpDt[h].qty) * (discPercentage / 100);
                                                // app.headerDiscount += discAmount;

                                                tmpDt[h].discount_percentage = discPercentage;
                                                tmpDt[h].discount_amount = discAmount;
                                                tmpDt[h].discount_str = formatRupiah(tmpDt[h].discount_amount + '', '');
                                                tmpDt[h].subtotal = (tmpDt[h].price * tmpDt[h].qty) - tmpDt[h].discount_amount;
                                                tmpDt[h].subtotal_str = formatRupiah(tmpDt[h].subtotal + '', '');
                                                console.log(tmpDt[h].discount_percentage);
                                                console.log(tmpDt[h].discount_amount);
                                            }
                                        }

                                        console.log(app.details);
                                    }
                                }
                                else if(response.data.benefits[i].type === 'facility'){
                                    let tmpDt = app.details.filter(function (x) { return x.is_facility_only === true; });
                                    debugger;
                                    if(tmpDt.length > 0){
                                        let isFree = response.data.benefits[i].is_free;
                                        if(isFree === 1){
                                            // app.headerDiscount += discAmount;

                                            let idx = app.details.indexOf(tmpDt[0]);
                                            let discAmount = app.details[idx].price;
                                            app.details[idx].discount_percentage = 100;
                                            app.details[idx].discount_amount = discAmount;
                                            app.details[idx].discount_str = formatRupiah(app.details[idx].discount_amount + '', '');
                                            app.details[idx].subtotal = app.details[idx].price - app.details[idx].discount_amount;
                                            app.details[idx].subtotal_str = formatRupiah(app.details[idx].subtotal + '', '');
                                        }
                                    }
                                }
                            }

                            app.memberSearch.id = response.data.benefits[0].member_id;
                            app.memberSearch.notFound = false;
                            //app.memberSearch = '';
                            // this.$forceUpdate();
                        }
                    });
                    app.memberSearch.loading = false;
                },
                //End Membership Modals

                //delete items
                showDeleteItemModal(item){
                    app.selectedItem = item;
                    modalConfirmationDelete.show();
                },
                async confirmDelete(){
                    app.deleteLoading = true;
                    $('#btn_delete_item_yes').hide();
                    $('#btn_delete_item_no').hide();

                    let data = {
                        dt_id: app.selectedItem.id,
                        password: app.deletePassword
                    };

                    await axios({
                        method: 'post',
                        url: '{{ route('admin.cashier.item.delete') }}',
                        data: data,
                    }).catch(function (error){
                        if(error.response){
                            app.deleteMsg = error.response.data;
                            app.deleteLoading = false;
                            $('#btn_delete_item_yes').show();
                            $('#btn_delete_item_no').show();
                        }
                    }).then(function (response) {
                        if(response.status === 200){
                            One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', message: 'Sukses Hapus Item!'});
                            //clean data
                            app.details = app.details.filter(n => n !== app.selectedItem);
                            app.selectedItem = null;
                            app.deleteMsg = '';
                        }
                    });

                    app.deleteLoading = false;
                    $('#btn_delete_item_yes').show();
                    $('#btn_delete_item_no').show();
                    modalConfirmationDelete.hide();
                },
                //end delete items
                async refreshPayment(){
                    await app.getTransactionWithoutModal();
                }
            },
            created(){
                setInterval(async () => {
                    if(!app.stopRefresh){
                        await app.refreshData();
                    }
                }, 5000);
            }
        });
    </script>
    <script>
        let modalPos = new bootstrap.Modal(document.getElementById('modal-pos'), {'backdrop': 'static', 'keyboard' :false });
        let modalPayment = new bootstrap.Modal(document.getElementById('modal-payment'), {'backdrop': 'static', 'keyboard' :false });
        let modalMembership = new bootstrap.Modal(document.getElementById('modal-membership'), {'backdrop': 'static', 'keyboard' :false });
        let modalConfirmationDelete = new bootstrap.Modal(document.getElementById('modal-delete-item'), {'backdrop': 'static', 'keyboard' :false });
        let modalMemberData = new bootstrap.Modal(document.getElementById('modal-member-data'), {'backdrop': 'static', 'keyboard' :false });

        scope.addEventListener("click", (e) => {
            if (e.target.offsetParent !== contextMenu) {
                contextMenu.classList.remove("visible");
            }
        });

        function formatRupiah(angka, prefix){
            let number_string = angka.replace(/[^,\d]/g, '').toString(),
                split   		= number_string.split(','),
                sisa     		= split[0].length % 3,
                rupiah     		= split[0].substr(0, sisa),
                ribuan     		= split[0].substr(sisa).match(/\d{3}/gi);

            if(ribuan){
                separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
            return rupiah + ',00';
        }
    </script>
@endsection
