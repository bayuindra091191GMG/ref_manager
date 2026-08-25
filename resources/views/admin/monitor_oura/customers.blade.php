@extends('layouts.admin')

@section('title')
    <title>Daftar Customer Berjalan</title>
@endsection

@section('content')
    <div class="content" id="app">
        <div class="block block-rounded">
            <div class="block-content">
                <div class="row justify-content-center">
                    <div class="col-lg-12 px-lg-1">
                        <div class="block block-rounded block-link-pop">
                            <div class="block-content block-content-full">
                                <h4>Customer Berjalan</h4>
{{--                                <div class="row">--}}
{{--                                    <div class="col-4">--}}
{{--                                        <h4>Customer Berjalan</h4>--}}
{{--                                    </div>--}}
{{--                                    <div class="col-8 d-flex justify-content-end">--}}
{{--                                        <div class='box green'></div>&nbsp;Available&nbsp;--}}
{{--                                        <div class='box red'></div>&nbsp;Sedang Digunakan--}}
{{--                                    </div>--}}
{{--                                </div>--}}
                                <hr/>
                                <h5 v-if="customers.length == 0">Belum ada Customer Berjalan!</h5>
                                <div class="row row-cols-auto">
                                    <div class="col mb-2 text-center" :id="item.element_id" v-for="(item, index) in customers" :key="item.key_id">
                                        <div class="oura-square">
                                            @{{ item.customer_name }}<br/>
                                            <span>@{{ item.package_name }}</span><br/>
                                            <span>@{{ item.room_number }}</span> |
                                            @{{ item.timer }}m<br/>
                                            <span>T: @{{ item.partner_name }}</span>
                                        </div>
                                        <button type="button" class="btn btn-primary mt-1" v-if="item.show_button" v-on:click="finish(item)">Selesai</button>
                                        <div class="spinner-border text-info" v-else>
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <br/>
                                        <button type="button" class="btn btn-danger mt-1" v-on:click="showSwitchRoomModal(item)">Ubah Room</button>
                                        <br/>
                                        <button type="button" class="btn btn-danger mt-1" v-on:click="showSwitchPartnerModal(item)">Ubah Terapis</button>
                                    </div>
                                </div>
                                <div class="row" style="height: 200px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END Pop In Treatment Modal -->
        @include('partials.monitor_oura._modal')
{{--        @include('partials.monitor_locker._pos')--}}
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
        let tmpCustomers = {!! $jsonCustomers !!};
        //console.log(tmpLockers);

        let app = new Vue({
            el: '#app',
            data: {
                stopRefresh: false,
                customers: tmpCustomers,
                selectedCustomer: null,
                selectedRoom: null,
                selectedPartner: null,
                rooms: [],
                roomPagination: {
                    skip: 0,
                    take: 10,
                    search: '',
                    count: 0,
                    pages: [],
                    isLoading: false,
                },
                partners: [],
                partnerPagination: {
                    skip: 0,
                    take: 10,
                    search: '',
                    count: 0,
                    pages: [],
                    isLoading: false,
                },
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
            methods:{
                async refreshData(){
                    await axios({
                        method: 'get',
                        url: '{{ route('admin.oura_monitor.customer_refresh') }}',
                    }).then(function (response) {
                        if(response){
                            if(response.status === 200){
                                app.customers = response.data;
                            }
                            else{
                                console.log(response);
                            }
                        }
                    });
                },
                async finish(customer){
                    app.stopRefresh = true;
                    let idx = app.customers.indexOf(customer);
                    app.customers[idx].show_button = false;

                    await axios({
                        method: 'post',
                        url: '{{ route('admin.oura_monitor.customer_finish') }}',
                        data: {
                            id: app.customers[idx].id,
                        }
                    }).catch(function (error){
                        if(error.response){
                            if(error.response.status === 400 || error.response.status === 404){
                                One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: error.response.data});
                            }
                            if(error.response.status === 500){
                                One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Maaf terjadi kesalahan!'});
                            }
                            app.customers.show_button = true;
                            app.stopRefresh = false;
                        }
                    }).then(function (response) {
                        if(response){
                            app.customers[idx].show_button = true;
                            One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', message: 'Sukses Menyelesaikan Transaksi!'});
                            app.hideItem(app.customers[idx]);
                            app.stopRefresh = false;
                        }
                    });
                    app.stopRefresh = false;
                },
                hideItem(item){
                    let idx = app.customers.indexOf(item);
                    console.log(idx);
                    app.customers[idx].status_id = 9;
                    setTimeout(app.fadeOutItem, 3000, item.element_id, idx);
                },
                fadeOutItem(itemId, idx){
                    fadeOut(itemId);
                    setTimeout(app.changeStatus, 1000, idx);
                },
                changeStatus(idx){
                    //app.customers[idx].display = false;
                    app.customers.splice(idx, 1);
                },

                // Room Modal
                async showSwitchRoomModal(customer){
                    app.selectedCustomer = customer;
                    await app.getRooms();
                    modalSwitchRoomMenu.show();
                },
                async getRooms(){
                    app.fcLoading = true;
                    app.roomPagination.isLoading = true;
                    await axios({
                        method: 'get',
                        url: '{{ route('admin.oura_room.get') }}',
                    }).then(function (response) {
                        if(response.status === 200){
                            app.rooms = response.data.rooms;
                            app.roomPagination.count = response.data.count;
                            app.roomPagination.pages = response.data.pages;
                        }
                    });
                    app.roomPagination.isLoading = false;
                    app.fcLoading = false;
                },
                async nextRoomPage(){
                    if((app.roomPagination.skip + 10) < app.roomPagination.count){
                        app.roomPagination.skip += 10;
                        await app.getRooms();
                    }
                },
                async previousRoomPage(){
                    if(app.roomPagination.skip >= 10){
                        app.roomPagination.skip -= 10;
                        await app.getRooms();
                    }
                },
                // End Room Modal

                // Partner Modal
                async showSwitchPartnerModal(customer){
                    app.selectedCustomer = customer;
                    await app.getPartners();
                    modalSwitchPartnerMenu.show();
                },
                async getPartners(){
                    app.fcLoading = true;
                    app.partnerPagination.isLoading = true;
                    await axios({
                        method: 'get',
                        url: '{{ route('admin.oura_partner.get') }}',
                    }).then(function (response) {
                        if(response.status === 200){
                            app.partners = response.data.partners;
                            app.partnerPagination.count = response.data.count;
                            app.partnerPagination.pages = response.data.pages;
                        }
                    });
                    app.partnerPagination.isLoading = false;
                    app.fcLoading = false;
                },
                async nextPartnerPage(){
                    if((app.partnerPagination.skip + 10) < app.partnerPagination.count){
                        app.partnerPagination.skip += 10;
                        await app.getPartners();
                    }
                },
                async previousPartnerPage(){
                    if(app.partnerPagination.skip >= 10){
                        app.partnerPagination.skip -= 10;
                        await app.getPartners();
                    }
                },
                // End Partner Modal

                showModalConfirmationSwitchRoom(item){
                    app.selectedRoom = item;
                    modalSwitchRoomConfirmation.show();
                },
                async switchRoom(){
                    app.stopRefresh = true;
                    $('#btn_cancel_switch_room').hide();
                    $('#btn_submit_switch_room').hide();
                    $('#btn_loading_switch_room').show();

                    await axios({
                        method: 'post',
                        url: '{{ route('admin.oura_monitor.switch_room') }}',
                        data: {
                            header_id: app.selectedCustomer.id,
                            detail_id: app.selectedCustomer.detail_id,
                            room_id: app.selectedRoom.id,
                        },
                    }).catch(function (error){
                        if(error.response){
                            if(error.response.status === 400 || error.response.status === 404){
                                One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: error.response.data});
                            }
                            if(error.response.status === 500){
                                One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Maaf terjadi kesalahan!'});
                            }
                            app.stopRefresh = false;
                            $('#btn_cancel_switch_room').show();
                            $('#btn_submit_switch_room').show();
                            $('#btn_loading_switch_room').hide();
                            app.selectedRoom = null;
                            app.selectedCustomer = null;
                            modalSwitchRoomConfirmation.hide();
                            modalSwitchRoomMenu.hide();
                        }
                    }).then(function (response) {
                        if(response){
                            One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', message: 'Sukses Pindah Room!'});
                            app.stopRefresh = false;
                        }
                        $('#btn_cancel_switch_room').show();
                        $('#btn_submit_switch_room').show();
                        $('#btn_loading_switch_room').hide();
                        app.selectedRoom = null;
                        app.selectedCustomer = null;
                        modalSwitchRoomConfirmation.hide();
                        modalSwitchRoomMenu.hide();
                    });

                    $('#btn_cancel_switch_room').show();
                    $('#btn_submit_switch_room').show();
                    $('#btn_loading_switch_room').hide();
                    await app.refreshData();
                    app.selectedRoom = null;
                    app.selectedCustomer = null;
                    modalSwitchRoomConfirmation.hide();
                    modalSwitchRoomMenu.hide();
                },
                showModalConfirmationSwitchPartner(item){
                    app.selectedPartner = item;
                    modalSwitchPartnerConfirmation.show();
                },
                async switchPartner(){
                    app.stopRefresh = true;
                    $('#btn_cancel_switch_partner').hide();
                    $('#btn_submit_switch_partner').hide();
                    $('#btn_loading_switch_partner').show();

                    console.log(app.selectedCustomer.id);
                    console.log(app.selectedPartner.id);
                    console.log(app.selectedCustomer.detail_id);

                    // return false;

                    await axios({
                        method: 'post',
                        url: '{{ route('admin.oura_monitor.switch_partner') }}',
                        data: {
                            header_id: app.selectedCustomer.id,
                            detail_id: app.selectedCustomer.detail_id,
                            partner_id: app.selectedPartner.id,
                        },
                    }).catch(function (error){
                        if(error.response){
                            if(error.response.status === 400 || error.response.status === 404){
                                One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: error.response.data});
                            }
                            if(error.response.status === 500){
                                One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Maaf terjadi kesalahan!'});
                            }
                            app.stopRefresh = false;
                            $('#btn_cancel_switch_partner').show();
                            $('#btn_submit_switch_partner').show();
                            $('#btn_loading_switch_partner').hide();
                            app.selectedPartner = null;
                            app.selectedCustomer = null;
                            modalSwitchPartnerConfirmation.hide();
                            modalSwitchPartnerMenu.hide();
                        }
                    }).then(function (response) {
                        if(response){
                            One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', message: 'Sukses Ubah Terapis!'});
                            app.stopRefresh = false;
                        }
                        $('#btn_cancel_switch_partner').show();
                        $('#btn_submit_switch_partner').show();
                        $('#btn_loading_switch_partner').hide();
                        app.selectedPartner = null;
                        app.selectedCustomer = null;
                        modalSwitchPartnerConfirmation.hide();
                        modalSwitchPartnerMenu.hide();
                    });

                    $('#btn_cancel_switch_partner').show();
                    $('#btn_submit_switch_partner').show();
                    $('#btn_loading_switch_partner').hide();
                    await app.refreshData();
                    app.selectedPartner = null;
                    app.selectedCustomer = null;
                    modalSwitchPartnerConfirmation.hide();
                    modalSwitchPartnerMenu.hide();
                },
            },
            created(){
                setInterval(async () => {
                    if(!app.stopRefresh){
                        await app.refreshData();
                    }
                }, 3000);
            }
        });
    </script>
    <script>
        let modalSwitchRoomMenu = new bootstrap.Modal(document.getElementById('modal-switch-room'), {'backdrop': 'static', 'keyboard' :false });
        let modalSwitchRoomConfirmation = new bootstrap.Modal(document.getElementById('modal-switch-room-confirmation'), {'backdrop': 'static', 'keyboard' :false });
        let modalSwitchPartnerMenu = new bootstrap.Modal(document.getElementById('modal-switch-partner'), {'backdrop': 'static', 'keyboard' :false });
        let modalSwitchPartnerConfirmation = new bootstrap.Modal(document.getElementById('modal-switch-partner-confirmation'), {'backdrop': 'static', 'keyboard' :false });

        function fadeOut(item){
            $('#' + item).addClass('animated fadeOut');
        }
    </script>
@endsection
