@extends('layouts.admin')

@section('title')
    <title>Room Operator</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Room - Operator
                    </h1>
                </div>
                <div class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3 d-flex justify-content-end">
                    <div class='box green'></div>&nbsp;Available&nbsp;
                    <div class='box red ms-2'></div>&nbsp;Dipakai&nbsp;
                    <div class='box yellow ms-2'></div>&nbsp;Dibooking&nbsp;
                    <div class='box work-book-bg ms-2'></div>&nbsp;Double Book&nbsp;
                    <div class='box pink ms-2'></div>&nbsp;Dibersihkan&nbsp;
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
                                    <div class="col mb-2" v-for="(item, index) in rooms" @click="openModal(item)">
                                        <div :class="item.element_class" :id="item.element_id">
                                            <span style="font-size: 16px;" class="fw-bold">@{{ item.number }}</span><br/>
                                            @{{ item.package_name }}<br/>
                                            <span v-if="item.room_number !== 0">R: @{{ item.room_number }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
{{--        <div id="context-menu">--}}
{{--            <div class="item" v-for="(item, index) in menus" v-on:click="menuClicked(item)">--}}
{{--                <span>@{{ item.name }}</span>--}}
{{--            </div>--}}
{{--        </div>--}}
        <!-- END Pop In Treatment Modal -->
        @include('partials.monitor_locker._modal')
        @include('partials.monitor_locker._pos')
    </div>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/monitor_data.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/custom_loading.css') }}"/>
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
    <style>
        .select2-container--default .select2-selection--multiple .select2-selection__choice{
            color: #000;
        }

        .modal-open .select2-dropdown {
            z-index: 10060;
        }

        .modal-open .select2-close-mask {
            z-index: 10055;
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
    <script type="text/javascript" src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>

    <script type="text/x-template" id="select2-template">
        <select>
            <slot></slot>
        </select>
    </script>
    <script src="{{ asset('js/vueComponent-select2.js') }}"></script>

    <script>
        let tmpLockers = {!! $jsonLockers !!};
        //console.log(tmpLockers);

        let app = new Vue({
            el: '#app',
            data: {
                checkOutLoading: false,
                isAthena: false,
                checkoutMsg: '',
                changePackageMsg: '',
                changePackagePassword: '',
                deleteLoading: false,
                deleteMsg: '',
                deletePassword: '',
                selectedItem: null,
                cancelMsg: '',
                cancelPassword: '',
                fcLoading: false,
                lockers: tmpLockers,
                memberSearch: {
                    name: '',
                    loading: false,
                    id: 0,
                    message: '',
                    notFound: true,
                },
                membership: [],
                selectedMembership: null,
                memberData: {
                    name: '',
                    phone: '',
                    email: '',
                },
                membershipPagination: {
                    skip: 0,
                    take: 10,
                    search: '',
                    count: 0,
                    pages: [],
                    isLoading: false,
                },
                gros: [],
                groPagination: {
                    skip: 0,
                    take: 10,
                    search: '',
                    count: 0,
                    pages: [],
                    isLoading: false,
                },
                selectedGro: null,
                menus: [
                    {
                        name: 'CheckIn',
                        route: '',
                    },
                    // {
                    //     name: 'Payment',
                    //     route: '',
                    // },
                    {
                        name: 'Cancel',
                        route: '',
                    },
                ],
                activeLocker: null,
                activeMenu: '',
                packages: [],
                packagePagination: {
                    skip: 0,
                    take: 10,
                    search: '',
                    count: 0,
                    pages: [],
                    isLoading: false,
                },
                selectedLocker: null,
                selectedPackage: null,
                selectedRoom: null,
                rooms: [],
                roomPagination: {
                    skip: 0,
                    take: 10,
                    search: '',
                    count: 0,
                    pages: [],
                    isLoading: false,
                },
                //POS
                bookingName: '',
                bookingPackage: '',
                bookingTherapist: '',
                bookingMemberPhone: '',
                messageSearchMember: '',
                successSearchMember: false,
                posMsg: '',
                foc: 'NO',
                focNumber: '',
                header: null,
                details: [],
                partners: [],
                packageId: 0,
                addItemMsg: '',
                items: [],
                itemDisplays: [],
                itemLoading: false,
                itemPagination: {
                    skip: 0,
                    take: 10,
                    search: '',
                    count: 0,
                    pages: [],
                },
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
                // grandTotal: function(){
                //     let total = 0;
                //     for(let i =0; i<this.details.length; i++){
                //         total += this.details[i].subtotal;
                //     }
                //
                //     return total;
                // },
                // changeTotal: function(){
                //     let change = 0;
                //     let total = this.grandTotal;
                //     let tmpAllPayment = 0;
                //
                //     for(let i=0; i<this.payments.length; i++){
                //         tmpAllPayment += this.payments[i].amount;
                //     }
                //     if((tmpAllPayment - total) > 0){
                //         change = tmpAllPayment - total;
                //     }
                //
                //     return change;
                // },
                // grandTotalStr: function(){
                //     let separator = '';
                //     let angka = this.grandTotal + '';
                //     let number_string = angka.replace(/[^,\d]/g, '').toString(),
                //         split   		= number_string.split(','),
                //         sisa     		= split[0].length % 3,
                //         rupiah     		= split[0].substr(0, sisa),
                //         ribuan     		= split[0].substr(sisa).match(/\d{3}/gi);
                //
                //     if(ribuan){
                //         separator = sisa ? '.' : '';
                //         rupiah += separator + ribuan.join('.');
                //     }
                //
                //     rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
                //     return 'Rp'+ rupiah + ',00';
                // },
                // changeTotalStr: function(){
                //     let separator = '';
                //     let angka = this.changeTotal + '';
                //     let number_string = angka.replace(/[^,\d]/g, '').toString(),
                //         split   		= number_string.split(','),
                //         sisa     		= split[0].length % 3,
                //         rupiah     		= split[0].substr(0, sisa),
                //         ribuan     		= split[0].substr(sisa).match(/\d{3}/gi);
                //
                //     if(ribuan){
                //         separator = sisa ? '.' : '';
                //         rupiah += separator + ribuan.join('.');
                //     }
                //
                //     rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
                //     return 'Rp'+ rupiah + ',00';
                // },
                // extraItems: function(){
                //     return this.details.filter(function (x) { return x.type === 'item'; });
                // },
                // isHappyHour: function(){
                //     if(this.details != null){
                //         let isHappyHour = false;
                //         for(let i=0; i<this.details.length; i++){
                //             if(this.details[i].discount_percentage === 10){
                //                 isHappyHour = true;
                //             }
                //         }
                //
                //         return isHappyHour;
                //     }
                // },
            },
            methods: {
                async searchMember() {
                    $.ajax({
                        url: '{{ route('admin.member.search') }}',
                        type: 'get',
                        data: {
                            phone: app.bookingMemberPhone,
                        },
                        success: function (response) {
                            app.bookingName = response.name == null ? app.bookingName : response.name;
                            app.messageSearchMember = response.message;
                            app.successSearchMember = response.success;
                        },
                        error: function (xhr, status, error) {
                            console.log(error);
                        }
                    })

                    // await app.refreshData();
                },
                async startTreatment() {
                    $.ajax({
                        url: '{{ route('admin.booking.start_treatment') }}',
                        type: 'post',
                        data: {
                            partner_id: app.bookingTherapist,
                            room_id: app.activeLocker.id,
                            package_id: app.bookingPackage,
                            booking_name: app.bookingName,
                            booking_phone: app.bookingMemberPhone,
                        },
                        success: function (response) {
                            console.log(response);
                        },
                        error: function (xhr, status, error) {
                            console.log(error);
                        },
                    });

                    // await app.refreshData();
                    app.fcLoading = false;
                },
                // getIndexNumberLocker(item){
                //     let idx = 0;
                //     // console.log(item);
                //     for(let i=0; i<app.lockers.length; i++){
                //         if(item.id === app.lockers[i].id){
                //             idx = i;
                //         }
                //     }
                //     return idx;
                // },
                // async checkMenu(){
                //     contextMenu.classList.remove("visible");
                //
                //     if(this.activeMenu === 'CheckIn'){
                //         await this.checkIn();
                //     }
                //     if(this.activeMenu === 'Cancel'){
                //         await this.cancel();
                //     }
                //
                //     modalConfirmMenu.hide();
                // },
                // resetMenuData(){
                //     app.menus = [
                //         {
                //             name: 'CheckIn',
                //             route: '',
                //         },
                //     ];
                // },
                // changeMenuData(){
                //     app.menus = [
                //         {
                //             name: 'Pilih Gro',
                //             route: '',
                //         },
                //         {
                //             name: 'Tambah Item',
                //             route: '',
                //         },
                //         // {
                //         //     name: 'Pilih Treatment',
                //         //     route: '',
                //         // },
                //         {
                //             name: 'Cancel',
                //             route: '',
                //         },
                //         {
                //             name: 'Detil',
                //             route: '',
                //         },
                //     ];
                // },
                // changeMenuToRoom(){
                //     if(app.activeLocker.is_package_chosen === false && app.activeLocker.is_room_chosen === false && app.activeLocker.already_have_transaction){
                //         app.menus = [
                //             {
                //                 name: 'Detil',
                //                 route: '',
                //             },
                //             {
                //                 name: 'Pilih Gro',
                //                 route: '',
                //             },
                //             {
                //                 name: 'Tambah Paket',
                //                 route: '',
                //             },
                //             {
                //                 name: 'Tambah Item',
                //                 route: '',
                //             },
                //         ];
                //     }
                //     else if(app.activeLocker.is_gro_chosen && app.activeLocker.is_package_chosen === false){
                //         app.menus = [
                //             {
                //                 name: 'Pilih Gro',
                //                 route: '',
                //             },
                //             {
                //                 name: 'Detil',
                //                 route: '',
                //             },
                //             {
                //                 name: 'Tambah Item',
                //                 route: '',
                //             },
                //         ];
                //     }
                //     else if(app.activeLocker.is_gro_chosen && app.activeLocker.is_room_chosen === false){
                //         app.menus = [
                //             {
                //                 name: 'Pilih Room',
                //                 route: '',
                //             },
                //             {
                //                 name: 'Detil',
                //                 route: '',
                //             },
                //             {
                //                 name: 'Tambah Item',
                //                 route: '',
                //             },
                //         ];
                //     }
                //     else if(app.activeLocker.is_package_chosen === true && app.activeLocker.is_room_chosen === true && app.activeLocker.already_have_transaction){
                //         app.menus = [
                //             {
                //                 name: 'Detil',
                //                 route: '',
                //             },
                //             {
                //                 name: 'Pilih Gro',
                //                 route: '',
                //             },
                //             {
                //                 name: 'Tambah Paket',
                //                 route: '',
                //             },
                //             {
                //                 name: 'Ubah Paket',
                //                 route: '',
                //             },
                //             {
                //                 name: 'Pindah Room',
                //                 route: '',
                //             },
                //             {
                //                 name: 'Tambah Item',
                //                 route: '',
                //             },
                //             {
                //                 name: 'Checkout',
                //                 route: '',
                //             },
                //             // {
                //             //     name: 'Payment',
                //             //     route: '',
                //             // },
                //         ];
                //     }
                // },
                // changeMenuToPos(){
                //     app.menus = [
                //         {
                //             name: 'Tambah Paket',
                //             route: '',
                //         },
                //         {
                //             name: 'Tambah Item',
                //             route: '',
                //         },
                //         {
                //             name: 'Detil',
                //             route: '',
                //         },
                //         // {
                //         //     name: 'Payment',
                //         //     route: '',
                //         // },
                //     ];
                // },
                // async menuClicked(item){
                //     contextMenu.classList.remove("visible");
                //     app.activeMenu = item.name;
                //
                //     app.selectedLocker = item;
                //     // if(item.name === 'Pilih Treatment'){
                //     //     await app.getPackages();
                //     //     modalPackageMenu.show();
                //     // }
                //     if(item.name === 'Tambah Paket'){
                //         await app.getPackages();
                //         modalPackageMenu.show();
                //     }
                //     else if(item.name === 'Ubah Paket'){
                //         app.showPackageModal();
                //     }
                //     else if(item.name === 'Checkout'){
                //         modalConfirmCheckout.show();
                //     }
                //     else if(item.name === 'Pilih Gro'){
                //         await app.getGros();
                //         modalGro.show();
                //         $('#gro-search').focus();
                //     }
                //     else if(item.name === 'Pilih Room'){
                //         // await app.getRooms();
                //         // modalRoomMenu.show();
                //     }
                //     else if(item.name === 'Pindah Room'){
                //         await app.getRooms();
                //         modalSwitchRoomMenu.show();
                //     }
                //     else if(item.name === 'Tambah Item'){
                //         //go to payment view
                //         //await app.getTransactionWithoutModal();
                //         await app.showItemModal();
                //     }
                //     else if(item.name === 'Payment'){
                //         //go to payment view
                //         await app.getTransaction();
                //     }
                //     else if(item.name === 'Detil'){
                //         //go to payment view
                //         await app.showDetailLocker();
                //     }
                //     else{
                //         if(item.name === 'CheckIn'){
                //             $('#menu_description').html('Apakah Anda yakin untuk checkin locker ini?');
                //         }
                //         else if(item.name === 'Cancel'){
                //             $('#menu_description').html('Apakah Anda yakin untuk cancel locker ini?');
                //         }
                //         modalConfirmMenu.show();
                //         $('#cancel-password').focus();
                //     }
                // },
                {{--async checkIn(){--}}
                {{--    app.fcLoading = true;--}}
                {{--    await axios({--}}
                {{--        method: 'post',--}}
                {{--        url: '{{ route('admin.monitor_locker.check_in') }}',--}}
                {{--        data: {--}}
                {{--            locker_id: this.activeLocker.id--}}
                {{--        }--}}
                {{--    }).then(function (response) {--}}
                {{--        if(response.status === 200){--}}
                {{--            let index = app.getIndexNumberLocker(app.activeLocker);--}}
                {{--            app.lockers[index].element_class = 'custom-square-red mx-auto';--}}
                {{--            app.lockers[index].status_id = 6;--}}
                {{--            One.helpers('jq-notify', {type: 'info', icon: 'fa fa-info-circle me-1', message: 'Sukses Check In untuk Locker ' + app.activeLocker.number + '!'});--}}
                {{--        }--}}
                {{--        else{--}}
                {{--            One.helpers('jq-notify', {type: 'error', icon: 'fa fa-info-circle me-1', message: 'Maaf terjadi kesalahan!'});--}}
                {{--        }--}}
                {{--    });--}}
                {{--    app.fcLoading = false;--}}
                {{--},--}}
                {{--async cancel(){--}}
                {{--    app.fcLoading = true;--}}
                {{--    await axios({--}}
                {{--        method: 'post',--}}
                {{--        url: '{{ route('admin.monitor_locker.cancel') }}',--}}
                {{--        data: {--}}
                {{--            locker_id: this.activeLocker.id,--}}
                {{--            password: app.cancelPassword,--}}
                {{--        }--}}
                {{--    })--}}
                {{--        .catch(function (error){--}}
                {{--            if(error.response){--}}
                {{--                app.cancelMsg = error.response.data;--}}
                {{--            }--}}
                {{--            app.fcLoading = false;--}}
                {{--            app.cancelPassword = '';--}}
                {{--        })--}}
                {{--        .then(function (response) {--}}
                {{--            if(response.status === 200){--}}
                {{--                let index = app.getIndexNumberLocker(app.activeLocker);--}}
                {{--                app.lockers[index].element_class = 'custom-square mx-auto green';--}}
                {{--                app.lockers[index].status_id = 5;--}}
                {{--                One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', message: 'Sukses Cancel Transaksi!'});--}}
                {{--            }--}}
                {{--            app.cancelMsg = '';--}}
                {{--            app.fcLoading = false;--}}
                {{--            app.cancelPassword = '';--}}
                {{--    });--}}
                {{--},--}}
                {{--async selectGro(){--}}
                {{--    $('#btn_cancel_select_gro').hide();--}}
                {{--    $('#btn_submit_select_gro').hide();--}}
                {{--    $('#btn_loading_select_gro').show();--}}

                {{--    await axios({--}}
                {{--        method: 'post',--}}
                {{--        url: '{{ route('admin.monitor_locker.select_gro') }}',--}}
                {{--        data: {--}}
                {{--            locker_id: app.activeLocker.id,--}}
                {{--            employee_id: app.selectedGro.id,--}}
                {{--        }--}}
                {{--    }).then(function (response) {--}}
                {{--        if(response.status === 200){--}}
                {{--            let index = app.getIndexNumberLocker(app.activeLocker);--}}
                {{--            app.lockers[index].gro_code = app.selectedGro.number;--}}
                {{--            app.lockers[index].gro_name = app.selectedGro.name;--}}
                {{--            app.lockers[index].gro_id = app.selectedGro.id;--}}
                {{--            app.lockers[index].is_gro_chosen = true;--}}
                {{--            One.helpers('jq-notify', {type: 'info', icon: 'fa fa-info-circle me-1', message: 'Sukses memilih Gro untuk Locker ' + app.activeLocker.number + '!'});--}}
                {{--        }--}}
                {{--        else{--}}
                {{--            One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Maaf terjadi kesalahan!'});--}}
                {{--        }--}}
                {{--        app.selectedPackage = null;--}}
                {{--    });--}}

                {{--    $('#btn_cancel_select_gro').show();--}}
                {{--    $('#btn_submit_select_gro').show();--}}
                {{--    $('#btn_loading_select_gro').hide();--}}
                {{--    modalConfirmationGro.hide();--}}
                {{--    modalGro.hide();--}}
                {{--},--}}
                {{--showModalConfirmationGro(item){--}}
                {{--    app.selectedGro = item;--}}
                {{--    modalConfirmationGro.show();--}}
                {{--},--}}
                {{--async selectPackage(){--}}
                {{--    $('#btn_cancel_select_package').hide();--}}
                {{--    $('#btn_submit_select_package').hide();--}}
                {{--    $('#btn_loading_select_package').show();--}}

                {{--    await axios({--}}
                {{--        method: 'post',--}}
                {{--        url: '{{ route('admin.monitor_locker.select_package') }}',--}}
                {{--        data: {--}}
                {{--            locker_id: app.activeLocker.id,--}}
                {{--            package_id: app.selectedPackage.id,--}}
                {{--        }--}}
                {{--    }).catch(function (error){--}}
                {{--        if(error.response){--}}
                {{--            One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: error.response.data});--}}
                {{--        }--}}
                {{--        $('#btn_cancel_select_package').show();--}}
                {{--        $('#btn_submit_select_package').show();--}}
                {{--        $('#btn_loading_select_package').hide();--}}
                {{--        modalPackageConfirmation.hide();--}}
                {{--        modalPackageMenu.hide();--}}
                {{--    }).then(function (response) {--}}
                {{--        if(response.status === 200){--}}
                {{--            let index = app.getIndexNumberLocker(app.activeLocker);--}}
                {{--            app.lockers[index].package_id = app.selectedPackage.id;--}}
                {{--            app.lockers[index].package_name = app.selectedPackage.name;--}}
                {{--            app.lockers[index].room_type_name = app.selectedPackage.room_type_name;--}}
                {{--            app.lockers[index].is_package_chosen = true;--}}
                {{--            app.lockers[index].is_room_chosen = false;--}}
                {{--            One.helpers('jq-notify', {type: 'info', icon: 'fa fa-info-circle me-1', message: 'Sukses memilih Paket untuk Locker ' + app.activeLocker.number + '!'});--}}
                {{--            //app.changeMenuData();--}}
                {{--        }--}}
                {{--        else{--}}
                {{--            One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Maaf terjadi kesalahan!'});--}}
                {{--        }--}}
                {{--        app.selectedPackage = null;--}}
                {{--    });--}}
                {{--    $('#btn_cancel_select_package').show();--}}
                {{--    $('#btn_submit_select_package').show();--}}
                {{--    $('#btn_loading_select_package').hide();--}}
                {{--    modalPackageConfirmation.hide();--}}
                {{--    modalPackageMenu.hide();--}}
                {{--},--}}
                {{--showModalConfirmation(item){--}}
                {{--    app.selectedPackage = item;--}}
                {{--    modalPackageConfirmation.show();--}}
                {{--},--}}
                {{--async selectRoom(){--}}
                {{--    $('#btn_cancel_select_room').hide();--}}
                {{--    $('#btn_submit_select_room').hide();--}}
                {{--    $('#btn_loading_select_room').show();--}}

                {{--    await axios({--}}
                {{--        method: 'post',--}}
                {{--        url: '{{ route('admin.monitor_locker.select_room') }}',--}}
                {{--        data: {--}}
                {{--            locker_id: app.activeLocker.id,--}}
                {{--            room_id: app.selectedRoom.id,--}}
                {{--            package_id: app.activeLocker.package_id,--}}
                {{--        }--}}
                {{--    }).then(function (response) {--}}
                {{--        if(response.status === 200){--}}
                {{--            let index = app.getIndexNumberLocker(app.activeLocker);--}}
                {{--            app.lockers[index].room_id = app.selectedRoom.id;--}}
                {{--            app.lockers[index].room_number = app.selectedRoom.number;--}}
                {{--            app.lockers[index].is_room_chosen = true;--}}

                {{--            One.helpers('jq-notify', {type: 'info', icon: 'fa fa-info-circle me-1', message: 'Sukses memilih Room untuk Locker ' + app.activeLocker.number + '!'});--}}
                {{--        }--}}
                {{--        app.selectedRoom = null;--}}
                {{--    });--}}
                {{--    $('#btn_cancel_select_room').show();--}}
                {{--    $('#btn_submit_select_room').show();--}}
                {{--    $('#btn_loading_select_room').hide();--}}
                {{--    modalRoomConfirmation.hide();--}}
                {{--    modalRoomMenu.hide();--}}
                {{--},--}}
                {{--showModalConfirmationRoom(item){--}}
                {{--    app.selectedRoom = item;--}}
                {{--    modalRoomConfirmation.show();--}}
                {{--},--}}
                {{--showModalConfirmationSwitchRoom(item){--}}
                {{--    app.selectedRoom = item;--}}
                {{--    modalSwitchRoomConfirmation.show();--}}
                {{--},--}}
                {{--async switchRoom(){--}}
                {{--    $('#btn_cancel_switch_room').hide();--}}
                {{--    $('#btn_submit_switch_room').hide();--}}
                {{--    $('#btn_loading_switch_room').show();--}}

                {{--    console.log(app.selectedRoom);--}}

                {{--    await axios({--}}
                {{--        method: 'post',--}}
                {{--        url: '{{ route('admin.monitor_locker.switch_room') }}',--}}
                {{--        data: {--}}
                {{--            locker_id: app.activeLocker.id,--}}
                {{--            room_id: app.selectedRoom.id,--}}
                {{--        }--}}
                {{--    }).then(function (response) {--}}
                {{--        if(response.status === 200){--}}
                {{--            let index = app.getIndexNumberLocker(app.activeLocker);--}}
                {{--            app.lockers[index].room_id = app.selectedRoom.id;--}}
                {{--            app.lockers[index].room_number = app.selectedRoom.number;--}}
                {{--            app.lockers[index].is_room_chosen = true;--}}

                {{--            One.helpers('jq-notify', {type: 'info', icon: 'fa fa-info-circle me-1', message: 'Sukses pindah Room untuk Locker ' + app.activeLocker.number + '!'});--}}
                {{--        }--}}
                {{--        app.selectedRoom = null;--}}
                {{--    });--}}
                {{--    $('#btn_cancel_switch_room').show();--}}
                {{--    $('#btn_submit_switch_room').show();--}}
                {{--    $('#btn_loading_switch_room').hide();--}}
                {{--    modalSwitchRoomConfirmation.hide();--}}
                {{--    modalSwitchRoomMenu.hide();--}}
                {{--},--}}
                {{--async showDetailLocker(){--}}
                {{--    await this.getTransactionWithoutModal();--}}
                {{--    modalDetil.show();--}}
                {{--},--}}
                {{--//Room Modals--}}
                {{--async getRooms(){--}}
                {{--    app.fcLoading = true;--}}
                {{--    app.roomPagination.isLoading = true;--}}
                {{--    await axios({--}}
                {{--        method: 'get',--}}
                {{--        url: '{{ route('admin.room.get') }}?type=' + app.activeLocker.room_type_name + '&skip=' + app.roomPagination.skip + '&take=' + app.roomPagination.take + '&search=' + app.roomPagination.search,--}}
                {{--    }).then(function (response) {--}}
                {{--        if(response.status === 200){--}}
                {{--            app.rooms = response.data.rooms;--}}
                {{--            app.roomPagination.count = response.data.count;--}}
                {{--            app.roomPagination.pages = response.data.pages;--}}
                {{--        }--}}
                {{--    });--}}
                {{--    app.roomPagination.isLoading = false;--}}
                {{--    app.fcLoading = false;--}}
                {{--},--}}
                {{--async nextRoomPage(){--}}
                {{--    if((app.roomPagination.skip + 10) < app.roomPagination.count){--}}
                {{--        app.roomPagination.skip += 10;--}}
                {{--        await app.getRooms();--}}
                {{--    }--}}
                {{--},--}}
                {{--async previousRoomPage(){--}}
                {{--    if(app.roomPagination.skip >= 10){--}}
                {{--        app.roomPagination.skip -= 10;--}}
                {{--        await app.getRooms();--}}
                {{--    }--}}
                {{--},--}}
                //End Room Modals

                //Treatment Modals
                {{--async getPackages(){--}}
                {{--    app.fcLoading = true;--}}
                {{--    app.packagePagination.isLoading = true;--}}
                {{--    await axios({--}}
                {{--        method: 'get',--}}
                {{--        url: '{{ route('admin.package.get') }}?skip=' + app.packagePagination.skip + '&take=' + app.packagePagination.take + '&search=' + app.packagePagination.search,--}}
                {{--    }).then(function (response) {--}}
                {{--        if(response.status === 200){--}}
                {{--            app.packages = response.data.packages;--}}
                {{--            app.packagePagination.count = response.data.count;--}}
                {{--            app.packagePagination.pages = response.data.pages;--}}
                {{--        }--}}
                {{--    });--}}
                {{--    app.packagePagination.isLoading = false;--}}
                {{--    app.fcLoading = false;--}}
                {{--},--}}
                {{--async nextPackagePage(){--}}
                {{--    if((app.packagePagination.skip + 10) < app.packagePagination.count){--}}
                {{--        app.packagePagination.skip += 10;--}}
                {{--        await app.getPackages();--}}
                {{--    }--}}
                {{--},--}}
                {{--async previousPackagePage(){--}}
                {{--    if(app.packagePagination.skip >= 10){--}}
                {{--        app.packagePagination.skip -= 10;--}}
                {{--        await app.getPackages();--}}
                {{--    }--}}
                {{--},--}}
                //End Treatment modals

                //Gro Modals
                {{--async getGros(){--}}
                {{--    app.fcLoading = true;--}}
                {{--    app.groPagination.isLoading = true;--}}
                {{--    await axios({--}}
                {{--        method: 'get',--}}
                {{--        url: '{{ route('admin.gro.get') }}?skip=' + app.groPagination.skip + '&take=' + app.groPagination.take + '&search=' + app.groPagination.search,--}}
                {{--    }).then(function (response) {--}}
                {{--        if(response.status === 200){--}}
                {{--            app.gros = response.data.gros;--}}
                {{--            app.groPagination.count = response.data.count;--}}
                {{--            app.groPagination.pages = response.data.pages;--}}
                {{--        }--}}
                {{--    });--}}
                {{--    app.groPagination.isLoading = false;--}}
                {{--    app.fcLoading = false;--}}
                {{--},--}}
                {{--async nextGroPage(){--}}
                {{--    if((app.groPagination.skip + 10) < app.groPagination.count){--}}
                {{--        app.groPagination.skip += 10;--}}
                {{--        await app.getGros();--}}
                {{--    }--}}
                {{--},--}}
                {{--async previousGroPage(){--}}
                {{--    if(app.groPagination.skip >= 10){--}}
                {{--        app.groPagination.skip -= 10;--}}
                {{--        await app.getGros();--}}
                {{--    }--}}
                {{--},--}}
                //End Gro Modals

                //Membership Modals
                {{--async showMembershipModal(){--}}
                {{--    await app.getMemberships();--}}
                {{--    modalMembership.show();--}}
                {{--},--}}
                {{--async getMemberships(){--}}
                {{--    app.membershipPagination.isLoading = true;--}}
                {{--    await axios({--}}
                {{--        method: 'get',--}}
                {{--        url: '{{ route('admin.membership.get') }}?skip=' + app.membershipPagination.skip + '&take=' + app.membershipPagination.take + '&search=' + app.membershipPagination.search,--}}
                {{--    }).then(function (response) {--}}
                {{--        if(response.status === 200){--}}
                {{--            app.membership = response.data.membership;--}}
                {{--            app.membershipPagination.count = response.data.count;--}}
                {{--            app.membershipPagination.pages = response.data.pages;--}}
                {{--        }--}}
                {{--    });--}}
                {{--    app.membershipPagination.isLoading = false;--}}
                {{--},--}}
                {{--async nextMembershipPage(){--}}
                {{--    if((app.membershipPagination.skip + 10) < app.membershipPagination.count){--}}
                {{--        app.membershipPagination.skip += 10;--}}
                {{--        await app.getMemberships();--}}
                {{--    }--}}
                {{--},--}}
                {{--async previousMembershipPage(){--}}
                {{--    if(app.membershipPagination.skip >= 10){--}}
                {{--        app.membershipPagination.skip -= 10;--}}
                {{--        await app.getMemberships();--}}
                {{--    }--}}
                {{--},--}}
                {{--showMemberDetilModal(membership){--}}
                {{--    app.selectedMembership = membership;--}}
                {{--    modalMemberData.show();--}}
                {{--},--}}
                {{--chooseMembership(){--}}
                {{--    //add membership item to transaction detil--}}
                {{--    let nDt = {--}}
                {{--        id: 7777,--}}
                {{--        type: 'membership',--}}
                {{--        package_name: '',--}}
                {{--        item_name: '',--}}
                {{--        membership_id: app.selectedMembership.id,--}}
                {{--        membership_name: app.selectedMembership.code,--}}
                {{--        qty: 1,--}}
                {{--        price: app.selectedMembership.price,--}}
                {{--        subtotal: app.selectedMembership.price,--}}
                {{--        price_str: formatRupiah(app.selectedMembership.price + '', ''),--}}
                {{--        subtotal_str: formatRupiah(app.selectedMembership.price + '', ''),--}}
                {{--    };--}}
                {{--    app.details.push(nDt);--}}
                {{--    modalMemberData.hide();--}}
                {{--    modalDetil.hide();--}}
                {{--    modalMembership.hide();--}}
                {{--},--}}
                {{--async checkMember(){--}}
                {{--    let data = {--}}
                {{--        locker_id: app.activeLocker.id,--}}
                {{--        member_name: app.memberSearch.name,--}}
                {{--    };--}}

                {{--    app.memberSearch.loading = true;--}}

                {{--    await axios({--}}
                {{--        method: 'post',--}}
                {{--        url: '{{ route('admin.member.check') }}',--}}
                {{--        data: data,--}}
                {{--    }).catch(function (error){--}}
                {{--        if(error.response){--}}
                {{--            if(error.response.status === 400 || error.response.status === 404){--}}
                {{--                app.memberSearch.message = error.response.data;--}}
                {{--            }--}}
                {{--            if(error.response.status === 500){--}}
                {{--                app.memberSearch.message = error.response.data;--}}
                {{--            }--}}
                {{--            app.memberSearch.notFound = true;--}}
                {{--            app.memberSearch.loading = false;--}}
                {{--        }--}}
                {{--    }).then(function (response) {--}}
                {{--        //member is true--}}
                {{--        if(response.status === 200){--}}
                {{--            //count discount--}}
                {{--            let tmpDt = app.details.filter(function (x) { return x.type === 'package'; });--}}
                {{--            let idx = app.details.indexOf(tmpDt[0]);--}}
                {{--            app.details[idx] = response.data.dt;--}}
                {{--            app.header = response.data.header;--}}
                {{--            app.memberSearch.id = response.data.member.id;--}}
                {{--            app.memberSearch.notFound = false;--}}
                {{--        }--}}
                {{--    });--}}
                {{--    app.memberSearch.loading = false;--}}
                {{--},--}}
                //End Membership Modals

                //Item modals
                {{--generateItemDisplay(){--}}
                {{--    let result = [];--}}
                {{--    let long = parseInt(app.itemPagination.skip) + parseInt(app.itemPagination.take);--}}
                {{--    console.log(long);--}}
                {{--    for(let i=app.itemPagination.skip; i<long; i++){--}}
                {{--        result.push(app.items[i]);--}}
                {{--    }--}}

                {{--    app.itemDisplays = result;--}}
                {{--},--}}
                {{--searchItem(){--}}
                {{--    let result = [];--}}
                {{--    let endResult = [];--}}
                {{--    for(let i=0; i<app.items.length; i++){--}}
                {{--        if(app.items[i].name.toLowerCase() === app.itemPagination.search.toLowerCase()--}}
                {{--            || app.items[i].name.toLowerCase().includes(app.itemPagination.search.toLowerCase())){--}}
                {{--            result.push(app.items[i]);--}}
                {{--        }--}}
                {{--    }--}}

                {{--    app.itemPagination.skip = 0;--}}
                {{--    app.itemPagination.take = 10;--}}

                {{--    let long = parseInt(app.itemPagination.skip) + parseInt(app.itemPagination.take);--}}
                {{--    if(long > result.length){--}}
                {{--        long = result.length;--}}
                {{--    }--}}
                {{--    for(let i=app.itemPagination.skip; i<long; i++){--}}
                {{--        endResult.push(result[i]);--}}
                {{--    }--}}

                {{--    app.itemDisplays = endResult;--}}
                {{--    //return result;--}}
                {{--},--}}
                {{--async getAllItems(){--}}
                {{--    await axios({--}}
                {{--        method: 'get',--}}
                {{--        url: '{{ route('admin.pos.get_item_all') }}',--}}
                {{--    }).then(function (response) {--}}
                {{--        if(response.status === 200){--}}
                {{--            app.items = response.data;--}}
                {{--            app.generateItemDisplay();--}}
                {{--        }--}}
                {{--    });--}}
                {{--},--}}
                {{--async getItems(){--}}
                {{--    await axios({--}}
                {{--        method: 'get',--}}
                {{--        url: '{{ route('admin.pos.get_items') }}?skip=' + app.itemPagination.skip + '&take=' + app.itemPagination.take + '&search=' + app.itemPagination.search,--}}
                {{--    }).then(function (response) {--}}
                {{--        if(response.status === 200){--}}
                {{--            app.items = response.data.items;--}}
                {{--            app.itemPagination.count = response.data.itemCount;--}}
                {{--            app.itemPagination.pages = response.data.pages;--}}
                {{--        }--}}
                {{--    });--}}
                {{--},--}}
                {{--async nextItemPage(){--}}
                {{--    if((app.itemPagination.skip + 10) < app.items.length){--}}
                {{--        app.itemPagination.skip += 10;--}}
                {{--        //await app.getItems();--}}
                {{--        app.generateItemDisplay();--}}
                {{--    }--}}
                {{--},--}}
                {{--async previousItemPage(){--}}
                {{--    if(app.itemPagination.skip >= 10){--}}
                {{--        app.itemPagination.skip -= 10;--}}
                {{--        //await app.getItems();--}}
                {{--        app.generateItemDisplay();--}}
                {{--    }--}}
                {{--},--}}
                {{--async selectItem(item){--}}
                {{--    let index = app.items.indexOf(item);--}}
                {{--    app.items[index].selected = !app.items[index].selected;--}}
                {{--},--}}
                {{--plusItemQty(item){--}}
                {{--    let index = app.items.indexOf(item);--}}
                {{--    if(app.items[index].selected){--}}
                {{--        app.items[index].qty++;--}}
                {{--    }--}}
                {{--},--}}
                {{--minusItemQty(item){--}}
                {{--    let index = app.items.indexOf(item);--}}
                {{--    if(app.items[index].selected && app.items[index].qty > 0){--}}
                {{--        app.items[index].qty--;--}}
                {{--    }--}}
                {{--},--}}
                //end item modals

                //POS Start
                {{--async getTransaction(){--}}
                {{--    await axios({--}}
                {{--        method: 'get',--}}
                {{--        url: '{{ route('admin.pos.get_transaction') }}?locker_id=' + app.activeLocker.id,--}}
                {{--    }).catch(function (error){--}}
                {{--        if(error.response){--}}
                {{--            if(error.response.status === 400 || error.response.status === 404){--}}
                {{--                One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: error.response.data});--}}
                {{--            }--}}
                {{--            if(error.response.status === 500){--}}
                {{--                One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Maaf terjadi kesalahan!'});--}}
                {{--            }--}}
                {{--        }--}}
                {{--    }).then(function (response) {--}}
                {{--        if(response.status === 200){--}}
                {{--            app.header = response.data;--}}
                {{--            app.details = response.data.details;--}}
                {{--            app.partners = response.data.partners;--}}
                {{--            app.packageId = app.details[0].package_id;--}}
                {{--            modalPos.show();--}}
                {{--        }--}}
                {{--    });--}}
                {{--},--}}
                {{--async getTransactionWithoutModal(){--}}
                {{--    app.isAthena = false;--}}
                {{--    await axios({--}}
                {{--        method: 'get',--}}
                {{--        url: '{{ route('admin.pos.get_transaction') }}?locker_id=' + app.activeLocker.id,--}}
                {{--    }).catch(function (error){--}}
                {{--        if(error.response){--}}
                {{--            if(error.response.status === 400 || error.response.status === 404){--}}
                {{--                One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: error.response.data});--}}
                {{--            }--}}
                {{--            if(error.response.status === 500){--}}
                {{--                One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Maaf terjadi kesalahan!'});--}}
                {{--            }--}}
                {{--        }--}}
                {{--    }).then(function (response) {--}}
                {{--        if(response.status === 200){--}}
                {{--            app.header = response.data;--}}
                {{--            app.details = response.data.details;--}}
                {{--            app.partners = response.data.partners;--}}
                {{--            app.isAthena = response.data.isAthena;--}}
                {{--            if(response.data.details.length > 0){--}}
                {{--                app.packageId = response.data.details[0].package_id;--}}
                {{--            }--}}
                {{--        }--}}
                {{--    });--}}
                {{--},--}}
                {{--async getPaymentTypes(){--}}
                {{--    await axios({--}}
                {{--        method: 'get',--}}
                {{--        url: '{{ route('admin.pos.get_payment_types') }}',--}}
                {{--    }).then(function (response) {--}}
                {{--        if(response.status === 200){--}}
                {{--            app.payment_type = response.data;--}}
                {{--        }--}}
                {{--    });--}}
                {{--},--}}
                {{--async submitTransaction(){--}}
                {{--    let membershipId = 0;--}}
                {{--    if(app.selectedMembership != null){--}}
                {{--        membershipId = app.selectedMembership.id;--}}
                {{--    }--}}

                {{--    let data = {--}}
                {{--        locker_id: app.activeLocker.id,--}}
                {{--        trx_id: app.header.header.id,--}}
                {{--        payments: [],--}}
                {{--        items: [],--}}
                {{--        membership_id: membershipId,--}}
                {{--        member_data: app.memberData,--}}
                {{--        change: app.changeTotal,--}}
                {{--        foc: app.foc,--}}
                {{--        foc_number: app.focNumber--}}
                {{--    };--}}

                {{--    let canSubmit = true;--}}
                {{--    for(let i=0; i<app.payments.length; i++){--}}
                {{--        if(app.payments[i].amount === 0){--}}
                {{--            canSubmit = false;--}}
                {{--        }--}}
                {{--        let nPayment = {--}}
                {{--            id: app.payments[i].payment_id,--}}
                {{--            amount: app.payments[i].amount,--}}
                {{--        };--}}

                {{--        data.payments.push(nPayment);--}}
                {{--    }--}}

                {{--    if(app.foc !== 'NO'){--}}
                {{--        canSubmit = true;--}}
                {{--    }--}}

                {{--    if(app.extraItems.length > 0){--}}
                {{--        //add extra items--}}
                {{--        let extraItems = app.extraItems;--}}
                {{--        for(let i=0; i<extraItems.length; i++){--}}
                {{--            let nItem = {--}}
                {{--                id: extraItems[i].item_id,--}}
                {{--                qty: extraItems[i].qty,--}}
                {{--            };--}}

                {{--            data.items.push(nItem);--}}
                {{--        }--}}
                {{--    }--}}

                {{--    if(!canSubmit){--}}
                {{--        alert('Amount tidak boleh 0!');--}}
                {{--    }--}}
                {{--    if(canSubmit){--}}
                {{--        await axios({--}}
                {{--            method: 'post',--}}
                {{--            url: '{{ route('admin.pos.submit') }}',--}}
                {{--            data: data,--}}
                {{--        }).catch(function (error){--}}
                {{--            if(error.response){--}}
                {{--                if(error.response.status === 400 || error.response.status === 404){--}}
                {{--                    One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: error.response.data});--}}
                {{--                }--}}
                {{--                if(error.response.status === 500){--}}
                {{--                    One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Maaf terjadi kesalahan!'});--}}
                {{--                }--}}
                {{--            }--}}
                {{--        }).then(function (response) {--}}
                {{--            if(response.status === 200){--}}
                {{--                //move to other tab then print--}}
                {{--                let url = '{{ route('admin.pos.receipt') }}?id=' + app.header.header.id;--}}
                {{--                window.open(url);--}}
                {{--                One.helpers('jq-notify', {type: 'info', icon: 'fa fa-info-circle me-1', message: 'Sukses Submit Transaksi! Silahkan Print Struk!'});--}}

                {{--                //close all the modal--}}
                {{--                modalPos.hide();--}}
                {{--                modalPayment.hide();--}}

                {{--                //clean data--}}
                {{--                app.header = null;--}}
                {{--                app.details = [];--}}
                {{--                app.payments = [];--}}
                {{--                app.items = [];--}}
                {{--                app.activeLocker = null;--}}
                {{--            }--}}
                {{--        });--}}

                {{--        await app.refreshData();--}}
                {{--    }--}}
                {{--},--}}
                {{--async refreshData(){--}}
                {{--    await axios({--}}
                {{--        method: 'get',--}}
                {{--        url: '{{ route('admin.monitor_locker.refresh_data') }}',--}}
                {{--    }).then(function (response) {--}}
                {{--        if(response){--}}
                {{--            if(response.status === 200){--}}
                {{--                app.lockers = response.data;--}}
                {{--            }--}}
                {{--            else{--}}
                {{--                console.log(response);--}}
                {{--            }--}}
                {{--        }--}}
                {{--    });--}}
                {{--},--}}
                {{--//POS End--}}

                {{--//Item POS--}}
                {{--checkOnStock(item){--}}
                {{--    let idx = app.items.indexOf(item);--}}
                {{--    if(app.items[idx].is_unlimited_stock == 0){--}}
                {{--        if(app.items[idx].qty > app.items[idx].stock){--}}
                {{--            app.items[idx].qty = app.items[idx].stock;--}}
                {{--        }--}}
                {{--    }--}}
                {{--},--}}
                {{--async showModalPos(){--}}
                {{--    await app.getPaymentTypes();--}}
                {{--    modalPos.show();--}}
                {{--},--}}
                {{--async showItemModal(){--}}
                {{--    await app.getAllItems();--}}
                {{--    modalItem.show();--}}
                {{--},--}}
                {{--async showItemModalQty(){--}}
                {{--    //await app.getTransactionWithoutModal();--}}
                {{--    await app.getItems();--}}
                {{--    modalItemQty.show();--}}
                {{--},--}}
                {{--async addItemsToTrxSubmit(){--}}
                {{--    let data = {--}}
                {{--        locker_id: app.activeLocker.id,--}}
                {{--        //trx_id: app.header.header.id,--}}
                {{--        items: [],--}}
                {{--    };--}}

                {{--    let tmpItems = app.items.filter(n => n.selected === true);--}}
                {{--    //generate parameters--}}
                {{--    let canSubmit = true;--}}
                {{--    for(let i=0; i<tmpItems.length; i++){--}}
                {{--        let idx = app.items.indexOf(tmpItems[i]);--}}
                {{--        let nParam = {--}}
                {{--            id: app.items[idx].id,--}}
                {{--            qty: app.items[idx].qty--}}
                {{--        };--}}

                {{--        if(app.items[idx].qty <= 0){--}}
                {{--            canSubmit = false;--}}
                {{--        }--}}

                {{--        data.items.push(nParam);--}}
                {{--    }--}}

                {{--    if(canSubmit){--}}
                {{--        app.itemLoading = true;--}}
                {{--        await axios({--}}
                {{--            method: 'post',--}}
                {{--            url: '{{ route('admin.pos.add_items') }}',--}}
                {{--            data: data,--}}
                {{--        }).catch(function (error){--}}
                {{--            if(error.response){--}}
                {{--                if(error.response.status === 400 || error.response.status === 404){--}}
                {{--                    One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: error.response.data});--}}
                {{--                }--}}
                {{--                if(error.response.status === 500){--}}
                {{--                    One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Maaf terjadi kesalahan!'});--}}
                {{--                }--}}
                {{--            }--}}
                {{--            app.itemLoading = false;--}}
                {{--        }).then(function (response) {--}}
                {{--            if(response.status === 200){--}}
                {{--                let no = 100;--}}
                {{--                for(let i=0; i<tmpItems.length; i++){--}}
                {{--                    let idx = app.items.indexOf(tmpItems[i]);--}}
                {{--                    let nDt = {--}}
                {{--                        id: no,--}}
                {{--                        type: 'item',--}}
                {{--                        package_name: '',--}}
                {{--                        item_id: app.items[idx].id,--}}
                {{--                        item_name: app.items[idx].name,--}}
                {{--                        qty: 1,--}}
                {{--                        price: app.items[idx].price,--}}
                {{--                        subtotal: app.items[idx].price,--}}
                {{--                        price_str: formatRupiah(app.items[idx].price + '', ''),--}}
                {{--                        subtotal_str: formatRupiah(app.items[idx].price + '', ''),--}}
                {{--                    };--}}

                {{--                    let tmp = app.details.filter(n => n.item_name === nDt.item_name);--}}
                {{--                    if(tmp.length === 0){--}}
                {{--                        app.details.push(nDt);--}}
                {{--                    }--}}

                {{--                    no++;--}}
                {{--                }--}}
                {{--                One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', message: 'Sukses menambahkan Item!'});--}}
                {{--            }--}}
                {{--        });--}}
                {{--        app.itemLoading = false;--}}
                {{--        // //reset items status--}}
                {{--        // for(let i=0; i<app.items.length; i++){--}}
                {{--        //     app.items[i].selected = false;--}}
                {{--        // }--}}
                {{--        modalItem.hide();--}}
                {{--    }--}}
                {{--    else{--}}
                {{--        app.addItemMsg = 'Min Qty 1!';--}}
                {{--    }--}}
                {{--},--}}
                {{--async addItemsToTrx(){--}}
                {{--    let tmpItems = app.items.filter(n => n.selected === true);--}}
                {{--    let no = 100;--}}
                {{--    for(let i=0; i<tmpItems.length; i++){--}}
                {{--        let idx = app.items.indexOf(tmpItems[i]);--}}
                {{--        let nDt = {--}}
                {{--            id: no,--}}
                {{--            type: 'item',--}}
                {{--            package_name: '',--}}
                {{--            item_id: app.items[idx].id,--}}
                {{--            item_name: app.items[idx].name,--}}
                {{--            qty: 1,--}}
                {{--            price: app.items[idx].price,--}}
                {{--            subtotal: app.items[idx].price,--}}
                {{--            price_str: formatRupiah(app.items[idx].price + '', ''),--}}
                {{--            subtotal_str: formatRupiah(app.items[idx].price + '', ''),--}}
                {{--            discount_percentage: 0,--}}
                {{--            discount_amount: 0,--}}
                {{--            discount_str: '0',--}}
                {{--            is_cut: 0,--}}
                {{--        };--}}

                {{--        let tmp = app.details.filter(n => n.item_name === nDt.item_name);--}}
                {{--        if(tmp.length === 0){--}}
                {{--            app.details.push(nDt);--}}
                {{--        }--}}

                {{--        no++;--}}
                {{--    }--}}

                {{--    // //reset items status--}}
                {{--    // for(let i=0; i<app.items.length; i++){--}}
                {{--    //     app.items[i].selected = false;--}}
                {{--    // }--}}
                {{--    modalItem.hide();--}}
                {{--},--}}
                {{--deleteItemFromTrx(item){--}}
                {{--    app.details = app.details.filter(n => n !== item);--}}
                {{--},--}}
                {{--calculateSubTotal(item){--}}
                {{--    let idx = app.details.indexOf(item);--}}
                {{--    item.subtotal = item.qty * item.price;--}}
                {{--    item.subtotal_str = formatRupiah(item.subtotal + '', '');--}}
                {{--    app.details[idx] = item;--}}
                {{--},--}}
                {{--calculateDiscount(item){--}}
                {{--    let idx = app.details.indexOf(item);--}}
                {{--    if(item.discount_percentage >= 100){--}}
                {{--        item.discount_percentage = 100;--}}
                {{--        item.discount_amount = item.price;--}}
                {{--        item.discount_str = formatRupiah(item.discount_amount + '', '');--}}
                {{--        item.subtotal = item.price - item.discount_amount;--}}
                {{--        item.subtotal_str = formatRupiah(item.subtotal+ '', '');--}}
                {{--    }--}}
                {{--    else{--}}
                {{--        item.discount_amount = item.price * (item.discount_percentage/100);--}}
                {{--        item.discount_str = formatRupiah(item.discount_amount + '', '');--}}
                {{--        item.subtotal = item.price - item.discount_amount;--}}
                {{--        item.subtotal_str = formatRupiah(item.subtotal+ '', '');--}}
                {{--    }--}}

                {{--    app.details[idx] = item;--}}
                {{--},--}}
                {{--async showPackageModal(){--}}
                {{--    app.packagePagination.take = 100;--}}
                {{--    await app.getPackages();--}}
                {{--    modalChangePackage.show();--}}
                {{--},--}}
                {{--async changePackage(item){--}}
                {{--    let success = false;--}}

                {{--    await axios({--}}
                {{--        method: 'post',--}}
                {{--        url: '{{ route('admin.pos.change_package') }}',--}}
                {{--        data: {--}}
                {{--            locker_id: app.activeLocker.id,--}}
                {{--            package_id: item.id,--}}
                {{--            password_confirmation: app.changePackagePassword,--}}
                {{--        }--}}
                {{--    }).catch(function (error){--}}
                {{--        app.changePackageMsg = error.response.data;--}}
                {{--        app.changePackagePassword = '';--}}
                {{--    }).then(function (response) {--}}
                {{--        console.log(response);--}}
                {{--        if(response.status === 200){--}}
                {{--            success = true;--}}
                {{--        }--}}
                {{--    });--}}
                {{--    app.changePackagePassword = '';--}}
                {{--    if(success){--}}
                {{--        One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', message: 'Berhasil Mengubah Paket!'});--}}
                {{--        modalChangePackage.hide();--}}
                {{--    }--}}
                {{--    await app.refreshData();--}}
                {{--},--}}
                {{--calculateTransaction(){--}}
                {{--    if(app.foc === 'FOC'){--}}
                {{--        for(let i=0; i<app.details.length; i++){--}}
                {{--            app.details[i].discount_percentage = 100;--}}
                {{--            app.details[i].discount_amount = app.details[i].subtotal;--}}
                {{--            app.details[i].discount_str = formatRupiah(app.details[i].discount_amount + '', '');--}}
                {{--            app.details[i].subtotal = app.details[i].subtotal - app.details[i].discount_amount;--}}
                {{--            app.details[i].subtotal_str = formatRupiah(app.details[i].subtotal + '', '');--}}
                {{--        }--}}
                {{--    }--}}
                {{--    else if(app.foc === 'FOC_PACKAGE'){--}}
                {{--        //Discount on Treatment--}}
                {{--        for(let i=0; i<app.details.length; i++){--}}
                {{--            if(app.details[i].type === 'package'){--}}
                {{--                app.details[i].discount_percentage = 100;--}}
                {{--                app.details[i].discount_amount = app.details[i].subtotal;--}}
                {{--                app.details[i].discount_str = formatRupiah(app.details[i].discount_amount + '', '');--}}
                {{--                app.details[i].subtotal = app.details[i].subtotal - app.details[i].discount_amount;--}}
                {{--                app.details[i].subtotal_str = formatRupiah(app.details[i].subtotal + '', '');--}}
                {{--            }--}}
                {{--            else{--}}
                {{--                app.details[i].discount_percentage = 0;--}}
                {{--                app.details[i].discount_amount = 0;--}}
                {{--                app.details[i].discount_str = formatRupiah(app.details[i].discount_amount + '', '');--}}
                {{--                app.details[i].subtotal = app.details[i].price * app.details[i].qty;--}}
                {{--                app.details[i].subtotal_str = formatRupiah(app.details[i].subtotal + '', '');--}}
                {{--            }--}}
                {{--        }--}}
                {{--    }--}}
                {{--    else if(app.foc === 'FOC_ITEM'){--}}
                {{--        //Discount on ITEM--}}
                {{--        for(let i=0; i<app.details.length; i++){--}}
                {{--            if(app.details[i].type === 'item'){--}}
                {{--                app.details[i].discount_percentage = 100;--}}
                {{--                app.details[i].discount_amount = app.details[i].subtotal;--}}
                {{--                app.details[i].discount_str = formatRupiah(app.details[i].discount_amount + '', '');--}}
                {{--                app.details[i].subtotal = app.details[i].subtotal - app.details[i].discount_amount;--}}
                {{--                app.details[i].subtotal_str = formatRupiah(app.details[i].subtotal + '', '');--}}
                {{--            }--}}
                {{--            else{--}}
                {{--                app.details[i].discount_percentage = 0;--}}
                {{--                app.details[i].discount_amount = 0;--}}
                {{--                app.details[i].discount_str = formatRupiah(app.details[i].discount_amount + '', '');--}}
                {{--                app.details[i].subtotal = app.details[i].price * app.details[i].qty;--}}
                {{--                app.details[i].subtotal_str = formatRupiah(app.details[i].subtotal + '', '');--}}
                {{--            }--}}
                {{--        }--}}
                {{--    }--}}
                {{--    else if(app.foc === 'NO'){--}}
                {{--        //Reset Transaction--}}
                {{--        for(let i=0; i<app.details.length; i++){--}}
                {{--            app.details[i].discount_percentage = 0;--}}
                {{--            app.details[i].discount_amount = 0;--}}
                {{--            app.details[i].discount_str = formatRupiah(app.details[i].discount_amount + '', '');--}}
                {{--            app.details[i].subtotal = app.details[i].price * app.details[i].qty;--}}
                {{--            app.details[i].subtotal_str = formatRupiah(app.details[i].subtotal + '', '');--}}
                {{--        }--}}
                {{--    }--}}
                {{--},--}}
                {{--async showModalPayment(){--}}
                {{--    //Check on FOC first--}}
                {{--    let canGoOn = true;--}}
                {{--    if(app.foc !== 'NO'){--}}
                {{--        if(app.focNumber === ''){--}}
                {{--            app.posMsg = 'Masukan Nomor FOC terlebih dahulu!';--}}
                {{--            canGoOn = false;--}}
                {{--        }--}}
                {{--    }--}}
                {{--    if(canGoOn){--}}
                {{--        app.posMsg = '';--}}
                {{--        await app.getPaymentTypes();--}}

                {{--        app.payments = [--}}
                {{--            {--}}
                {{--                id: 0,--}}
                {{--                amount: app.grandTotal,--}}
                {{--                amountStr: formatRupiah(app.grandTotal + ''),--}}
                {{--                payment_id: 1,--}}
                {{--            }--}}
                {{--        ];--}}
                {{--        modalPayment.show();--}}
                {{--    }--}}
                {{--},--}}
                {{--addMorePayment(){--}}
                {{--    let tmpGrandTotal = app.grandTotal;--}}
                {{--    tmpGrandTotal = tmpGrandTotal / (app.payments.length + 1);--}}
                {{--    let tmpData = [];--}}

                {{--    for(let i = 0; i< app.payments.length + 1; i++){--}}
                {{--        let tmp =--}}
                {{--            {--}}
                {{--                id: i,--}}
                {{--                amount: 0,--}}
                {{--                amountStr: '0',--}}
                {{--                payment_id: 1,--}}
                {{--            };--}}
                {{--        if(i === 0){--}}
                {{--            tmp.amount = app.grandTotal;--}}
                {{--            tmp.amountStr = formatRupiah(app.grandTotal + '');--}}
                {{--        }--}}
                {{--        tmpData.push(tmp);--}}
                {{--    }--}}
                {{--    app.payments = tmpData;--}}
                {{--},--}}
                {{--deletePayment(item){--}}
                {{--    this.payments = this.payments.filter(function (x) { return x !== item; });--}}
                {{--},--}}
                {{--//Item POS End--}}

                {{--//delete items--}}
                {{--showDeleteItemModal(item){--}}
                {{--    app.deletePassword = '';--}}
                {{--    app.selectedItem = item;--}}
                {{--    modalConfirmationDelete.show();--}}
                {{--    $('#password-confirmation').focus();--}}
                {{--},--}}
                {{--async confirmDelete(){--}}
                {{--    app.deleteLoading = true;--}}
                {{--    $('#btn_delete_item_yes').hide();--}}
                {{--    $('#btn_delete_item_no').hide();--}}

                {{--    let data = {--}}
                {{--        dt_id: app.selectedItem.id,--}}
                {{--        password: app.deletePassword--}}
                {{--    };--}}

                {{--    await axios({--}}
                {{--        method: 'post',--}}
                {{--        url: '{{ route('admin.cashier.item.delete') }}',--}}
                {{--        data: data,--}}
                {{--    }).catch(function (error){--}}
                {{--        if(error.response){--}}
                {{--            app.deleteMsg = error.response.data;--}}
                {{--            app.deleteLoading = false;--}}
                {{--            $('#btn_delete_item_yes').show();--}}
                {{--            $('#btn_delete_item_no').show();--}}
                {{--        }--}}
                {{--        app.deletePassword = '';--}}
                {{--    }).then(function (response) {--}}
                {{--        if(response.status === 200){--}}
                {{--            One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', message: 'Sukses Hapus Item!'});--}}
                {{--            //clean data--}}
                {{--            app.details = app.details.filter(n => n !== app.selectedItem);--}}
                {{--            app.selectedItem = null;--}}
                {{--            app.deleteMsg = '';--}}
                {{--        }--}}
                {{--    });--}}

                {{--    app.deletePassword = '';--}}
                {{--    app.deleteLoading = false;--}}
                {{--    $('#btn_delete_item_yes').show();--}}
                {{--    $('#btn_delete_item_no').show();--}}
                {{--    modalConfirmationDelete.hide();--}}
                {{--},--}}
                {{--//end delete items--}}
                {{--//end item modification--}}

                {{--//checkout--}}
                {{--async checkoutLocker(){--}}
                {{--    app.checkOutLoading = true;--}}
                {{--    $('#btn_cancel_checkout').hide();--}}
                {{--    $('#btn_checkout').hide();--}}

                {{--    await axios({--}}
                {{--        method: 'post',--}}
                {{--        url: '{{ route('admin.monitor_locker.check_out') }}',--}}
                {{--        data: {--}}
                {{--            locker_id: app.activeLocker.id,--}}
                {{--        },--}}
                {{--    }).catch(function (error){--}}
                {{--        if(error.response){--}}
                {{--            if(error.response.status === 400 || error.response.status === 404){--}}
                {{--                One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: error.response.data});--}}
                {{--            }--}}
                {{--            if(error.response.status === 500){--}}
                {{--                One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Maaf terjadi kesalahan!'});--}}
                {{--            }--}}
                {{--        }--}}

                {{--        app.checkOutLoading = false;--}}
                {{--        $('#btn_cancel_checkout').show();--}}
                {{--        $('#btn_checkout').show();--}}
                {{--        modalConfirmCheckout.hide();--}}
                {{--    }).then(function (response) {--}}
                {{--        if(response.status === 200){--}}
                {{--            //move to other tab then print--}}
                {{--            One.helpers('jq-notify', {type: 'info', icon: 'fa fa-info-circle me-1', message: 'Sukses Checkout!'});--}}
                {{--        }--}}
                {{--    });--}}
                {{--    app.checkOutLoading = false;--}}
                {{--    $('#btn_cancel_checkout').show();--}}
                {{--    $('#btn_checkout').show();--}}
                {{--    modalConfirmCheckout.hide();--}}
                {{--}--}}
            },
            // created(){
            //     setInterval(async () => {
            //         if(!app.stopRefresh){
            //             await app.refreshData();
            //         }
            //     }, 5000);
            // }
        });
    </script>
    <script>
        let modalBooking = new bootstrap.Modal(
            document.getElementById(
                'modal-booking'
            ), {
                'backdrop': 'static',
                'keyboard' :false
            });

        // let modalConfirmMenu = new bootstrap.Modal(document.getElementById('modal-block-popin'), {'backdrop': 'static', 'keyboard' :false });
        // let modalPackageMenu = new bootstrap.Modal(document.getElementById('modal-block-large'), {'backdrop': 'static', 'keyboard' :false });
        // let modalRoomMenu = new bootstrap.Modal(document.getElementById('modal-block-room-large'), {'backdrop': 'static', 'keyboard' :false });
        // let modalSwitchRoomMenu = new bootstrap.Modal(document.getElementById('modal-switch-room'), {'backdrop': 'static', 'keyboard' :false });
        // let modalPackageConfirmation = new bootstrap.Modal(document.getElementById('modal_package_confirmation'), {'backdrop': 'static', 'keyboard' :false });
        // let modalRoomConfirmation = new bootstrap.Modal(document.getElementById('modal_room_confirmation'), {'backdrop': 'static', 'keyboard' :false });
        // let modalSwitchRoomConfirmation = new bootstrap.Modal(document.getElementById('modal-switch-room-confirmation'), {'backdrop': 'static', 'keyboard' :false });
        // let modalPos = new bootstrap.Modal(document.getElementById('modal-pos'), {'backdrop': 'static', 'keyboard' :false });
        // let modalItem = new bootstrap.Modal(document.getElementById('modal-item-qty-old'), {'backdrop': 'static', 'keyboard' :false });
        // let modalItemQty = new bootstrap.Modal(document.getElementById('modal-item-qty'), {'backdrop': 'static', 'keyboard' :false });
        // let modalChangePackage = new bootstrap.Modal(document.getElementById('modal-package'), {'backdrop': 'static', 'keyboard' :false });
        // let modalPayment = new bootstrap.Modal(document.getElementById('modal-payment'), {'backdrop': 'static', 'keyboard' :false });
        // let modalGro = new bootstrap.Modal(document.getElementById('modal-gro'), {'backdrop': 'static', 'keyboard' :false });
        // let modalConfirmationGro = new bootstrap.Modal(document.getElementById('modal-confirmation-gro'), {'backdrop': 'static', 'keyboard' :false });
        // let modalDetil = new bootstrap.Modal(document.getElementById('modal-detil'), {'backdrop': 'static', 'keyboard' :false });
        // let modalMembership = new bootstrap.Modal(document.getElementById('modal-membership'), {'backdrop': 'static', 'keyboard' :false });
        // let modalMemberData = new bootstrap.Modal(document.getElementById('modal-member-data'), {'backdrop': 'static', 'keyboard' :false });
        // let modalConfirmationDelete = new bootstrap.Modal(document.getElementById('modal-delete-item'), {'backdrop': 'static', 'keyboard' :false });
        // let modalConfirmCheckout = new bootstrap.Modal(document.getElementById('modal-checkout'), {'backdrop': 'static', 'keyboard' :false });

        function openModal(room) {
            app.activeRoom = room;

            if (app.activeLocker.status_id == 5) {
                modalBooking.show();
            }
        }

        // const contextMenu = document.getElementById("context-menu");
        // const scope = document.querySelector("body");

        // scope.addEventListener("contextmenu", (event) => {
        //     event.preventDefault();
        //     // let tmp = event.path[0].getAttribute('class');
        //     // let id = event.path[0].getAttribute('id');
        //     let tmp = event.srcElement.className;
        //     let id = event.srcElement.id;
        //     if(id === null || id === ''){
        //         id = event.srcElement.parentNode.id;
        //         tmp = event.srcElement.parentNode.className;
        //     }
        //     let tmpLocker = app.lockers.filter(n=> { return n.element_id === id});
        //     let index = app.getIndexNumberLocker(tmpLocker[0]);
        //     app.activeLocker = app.lockers[index];
        //
        //     if(tmp.includes('custom-square')){
        //         if(app.lockers[index].status_id === 6){
        //             app.changeMenuData();
        //             if(app.lockers[index].is_facility_only){
        //                 // let nMenu = {
        //                 //     name: 'Payment',
        //                 //     route: '',
        //                 // };
        //                 //
        //                 // app.menus.push(nMenu);
        //             }
        //
        //             app.changeMenuToRoom();
        //
        //             // if(app.lockers[index].is_package_chosen === true && app.lockers[index].is_room_chosen === false){
        //             //     app.changeMenuToRoom();
        //             // }
        //             // if(app.lockers[index].is_package_chosen === true && app.lockers[index].is_room_chosen === true){
        //             //     app.changeMenuToPos();
        //             // }
        //         }
        //         else if(app.lockers[index].status_id === 5){
        //             app.resetMenuData();
        //         }
        //         const { clientX: mouseX, clientY: mouseY } = event;
        //
        //         contextMenu.style.top = `${mouseY}px`;
        //         contextMenu.style.left = `${mouseX}px`;
        //
        //         contextMenu.classList.remove("visible");
        //
        //         setTimeout(() => {
        //             contextMenu.classList.add("visible");
        //         });
        //     }
        // });
        //
        // scope.addEventListener("click", (e) => {
        //     if (e.target.offsetParent !== contextMenu) {
        //         contextMenu.classList.remove("visible");
        //     }
        // });

        // function formatRupiah(angka, prefix){
        //     let number_string = angka.replace(/[^,\d]/g, '').toString(),
        //         split   		= number_string.split(','),
        //         sisa     		= split[0].length % 3,
        //         rupiah     		= split[0].substr(0, sisa),
        //         ribuan     		= split[0].substr(sisa).match(/\d{3}/gi);
        //
        //     if(ribuan){
        //         separator = sisa ? '.' : '';
        //         rupiah += separator + ribuan.join('.');
        //     }
        //
        //     rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
        //     return rupiah + ',00';
        // }
    </script>
@endsection
