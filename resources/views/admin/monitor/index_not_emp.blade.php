@extends('layouts.admin')

@section('title')
    <title>BACKEND - Monitor</title>
@endsection

@section('content')
    <div class="mt-2" id="app">
        <div class="row">
            <div class="col-lg-6 px-lg-1" style="overflow-y: scroll; height: 100vh;">
                <div class="block block-rounded block-link-pop">
                    <div class="block-content block-content-full">
                        <div class="row">
                            <div class="col-2">
                                <h4 class="m-0">Therapis</h4>
                            </div>
                            <div class="col-10 d-flex justify-content-end">
                                <div class='circle-custom green'></div>&nbsp;Available&nbsp;
                                <div class='circle-custom red'></div>&nbsp;Bekerja&nbsp;
                                <div class='circle-custom yellow'></div>&nbsp;Dibooking&nbsp;
                                <div class='circle-custom work-book-bg'></div>&nbsp;Double Book&nbsp;
                                <div class='circle-custom grey'></div>&nbsp;Istirahat&nbsp;
                            </div>
                        </div>
                        <hr/>
                        <div class="row row-cols-6">
                            <div class="col mb-2" v-for="(item, index) in partners">
                                <div :class="item.class" :id="item.element_id" draggable="true" ondragstart="drag(event)">
                                    <span style="font-size: 20px;">@{{ item.number }}</span>
                                    <br/>
                                    @{{ item.name }}
                                    <br/>
                                    @{{ item.book_name }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 px-lg-1" style="overflow-y: scroll; height: 100vh;">
                <div class="block block-rounded block-link-pop">
                    <div class="block-content block-content-full">
                        <div class="row">
                            <div class="col-2">
                                <h4 class="m-0">Room</h4>
                            </div>
                            <div class="col-10 d-flex justify-content-end">
                                <div class='circle-custom green'></div>&nbsp;Available&nbsp;
                                <div class='circle-custom red'></div>&nbsp;Dipakai&nbsp;
                                <div class='circle-custom yellow'></div>&nbsp;Dibooking&nbsp;
                                <div class='circle-custom work-book-bg'></div>&nbsp;Double Book&nbsp;
                                <div class='circle-custom pink'></div>&nbsp;Dibersihkan&nbsp;
                            </div>
                        </div>
                        <hr/>
                        <div class="row row-cols-6">
                            <div class="col mb-2" v-for="(item, index) in rooms">
                                <div :class="item.class" :id="item.element_id" ondrop="drop(event)" ondragover="allowDrop(event)">
                                    @{{ item.number }}
                                    <div v-if="item.locker_number !== ''">L: @{{ item.locker_number }}</div>
                                    <div v-if="item.partners.length !== 0">T: @{{ item.partner_display }}</div>
                                    <div v-if="item.timer !== 0 && item.is_called !== 1" class="close-text">@{{ item.timer }}m</div>
                                    <div v-else-if="item.timer !== 0" style="color: black;">@{{ item.timer }}m</div>
                                    <div v-if="item.trx_status === 8 && item.timer === 0" class="close-text">Waktu Habis</div>
                                    <div v-if="item.time_display !== ''">@{{ item.time_display }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <h6>Announcement</h6>
                            <textarea class="form-control" rows="10" cols="10" style="font-size: 18px;" readonly>{{ $announcement }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div id="context-menu">
                <div class="item justify-content-start" v-for="(item, index) in roomMenus" v-on:click="roomMenuClicked(item)"><span>@{{ item }}</span></div>
            </div>
            <div id="partner-menu">
                <div class="item justify-content-start" v-for="(item, index) in partnerMenus" v-on:click="partnerMenuClicked(item)"><span>@{{ item }}</span></div>
            </div>
        </div>
        @include('partials.monitor._modal')
    </div>
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ asset('css/monitor_data_not_emp.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/custom_loading.css') }}"/>
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
@endsection
@section('scripts')
    <!-- Page JS Plugins -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    {{--    <script src="{{ asset('js/plugins/chart.js/Chart.min.js') }}"></script>--}}
    <!-- Page JS Code -->
    {{--    <script src="{{ asset('js/pages/be_pages_dashboard.min.js') }}"></script>--}}
    <script src="{{ asset('js/plugins/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
    {{-- <script src="{{ asset('js/vue.js') }}"></script> --}}
    <script src="{{ asset('js/vue.js') }}"></script>
    <script src="{{ asset('js/axios.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>
    <script type="text/x-template" id="select2-template">
        <select>
            <slot></slot>
        </select>
    </script>
    <script src="{{ asset('js/vueComponent-select2-monitor.js') }}"></script>
    <script>
        let tmpRooms = {!! $jsonRooms !!};
        let tmpPartners = {!! $jsonPartners !!};
        let tmpBookings = {!! $jsonBookings !!};

        let app = new Vue({
            el: '#app',
            data: {
                cancelPartnerPassword: '',
                checkoutPartnerErrorMsg: '',
                checkoutPartnerSuccessMsg: '',
                checkoutPartnerOptions: [],
                stopRefresh: false,
                fcLoading: false,
                rooms: tmpRooms,
                partners: tmpPartners,
                morePartners: tmpPartners,
                bookingLoading: false,
                bookingName: '',
                activePartner: null,
                activeRoom: null,
                tmpPartnerId: '',
                partnerMenus: [
                    'Set Available',
                    'Booking',
                    'Cancel Booking',
                    'Istirahat',
                ],
                activePartnerMenu: '',
                roomMenus: [
                    'Set Available',
                    'Dibersihkan',
                    'Pilih Therapis',
                    'Telpon Room',
                ],
                activeRoomMenu: '',
                loadingExtendSubmit: false,
                loadingCallRoom: false,
                loadingAddPeople: false,
                loadingAddPartner: false,
                loadingGetPartner: false,
                extendPartnerId: 0,
                changePartner: false,
                changePartnerMessage: '',
                //New Booking Style
                booking: {
                    lockerId: 0,
                    packageId: 0,
                    isLoading: false,
                    packages: [],
                },
                bookingPackages: [],
                bookings: tmpBookings,
                changePartnerMsg: '',
                changePartnerId: 0,
                changePartnerLoading: false,
                changePartnerOptions: [],
            },
            computed: {
                royalRoom: function(){
                    return this.rooms.filter(n=> { return n.type === 'royal' });
                },
                aphroditeRoom: function(){
                    return this.rooms.filter(n=> { return n.type === 'aphrodite' });
                },
                athenaRoom: function(){
                    return this.rooms.filter(n=> { return n.type === 'athena' });
                },
                kingsmanRoom: function(){
                    return this.rooms.filter(n=> { return n.type === 'kingsman' });
                },
                caesarRoom: function(){
                    return this.rooms.filter(n=> { return n.type === 'caesar' });
                },
                reflexiologyRoom: function(){
                    return this.rooms.filter(n=> { return n.type === 'reflexiology' });
                },
                chosenPartner: function(){
                    return this.morePartners.filter(n=> {return n.chosen === true});
                },
                chosenPartnerData: function(){
                    let tmp = this.morePartners.filter(n=> {return n.chosen === true});
                    let data = [];
                    for(let i=0; i<tmp.length; i++){
                        data.push(this.morePartners[i].id);
                    }
                    return data;
                },
                partnerRestMenu: function(){
                    return [
                        'Set Available',
                        'Booking',
                        'Cancel Booking',
                    ];
                },
                partnerAvailMenu: function(){
                    return [
                        'Booking',
                        'Cancel Booking',
                        'Istirahat',
                    ];
                },
                partnerOnWorkMenu: function(){
                    return [
                        'Double Book',
                    ];
                },
                partnerDoubleBookMenu: function(){
                    return [
                        'Cancel Double Booking',
                    ];
                },
                normalMenu: function(){
                    let menu = [
                        'Dibersihkan',
                    ];

                    if(app.activeRoom.status_id !== 11){
                        menu.push('Booking');
                    }
                    else{
                        menu.push('Cancel Booking');
                        menu.push('Start Treatment');
                    }

                    return menu;
                },
                normalMenuWithTrx: function(){
                    return [
                        'Pilih Therapis',
                    ];
                },
                onGoingMenu: function(){
                    if(app.activeRoom.package_type === 'athena'){
                        return [
                            'Tambah Orang',
                            'Tambah Therapis',
                            'Checkout Therapis',
                            'Cancel Therapis',
                            'Ubah Therapis',
                            'Extend',
                            'Telpon Room',
                        ];
                    }
                    else if(app.activeRoom.status_id === 6 && app.activeRoom.is_double_book){
                        return [
                            'Cancel Double Booking',
                            'Extend',
                            'Telpon Room',
                            'Checkout Therapis',
                            'Cancel Therapis',
                        ];
                    }
                    else{
                        return [
                            'Booking',
                            'Extend',
                            'Telpon Room',
                            'Ubah Therapis',
                            'Checkout Therapis',
                            'Cancel Therapis',
                        ];
                    }
                },
                cleaningMenu: function(){
                    return [
                        'Set Available',
                    ];
                },
            },
            methods: {
                allTimerOn(){
                    for(let i=0; i< this.rooms.length; i++){
                        if(this.rooms[i].timer > 0){
                            this.countDownTimer(i);
                        }
                    }
                },
                countDownTimer (index) {
                    if(this.rooms[index].timer === 0){
                        this.rooms[index].class = 'custom-square green';
                    }
                    if(this.rooms[index].timer === 10 && this.rooms[index].is_called !== 1){
                        this.rooms[index].class = 'custom-square blink-bg';
                        //console.log('asdf');
                    }
                    if (this.rooms[index].timer > 0) {
                        setTimeout(() => {
                            this.rooms[index].timer -= 1
                            this.countDownTimer(index)
                        }, 60000);
                    }
                },
                async refreshData(){
                    await axios({
                        method: 'get',
                        url: '{{ route('admin.monitor.refresh_data') }}',
                    }).then(function (response) {
                        if(response){
                            if(response.status === 200){
                                app.rooms = response.data.rooms;
                                app.partners = response.data.partners;
                                app.bookings = response.data.bookings;
                            }
                            else{
                                console.log(response);
                            }
                        }
                    });
                },
                async addMorePartner(isSingle){
                    app.loadingAddPartner = true;
                    app.stopRefresh = true;
                    $('#btnAddMorePartner').hide();
                    let tmpRoom = app.rooms.filter(n=> { return n.id === app.activeRoom.id});
                    let index = app.getIndexNumberRoom(tmpRoom[0]);

                    let data = {};
                    if(isSingle){
                        data = {
                            partner_ids: [],
                            room_id: app.rooms[index].id,
                            locker_id: app.rooms[index].locker_id,
                            is_single: true,
                            partner_id: app.activePartner.id,
                        };
                    }
                    else{
                        data = {
                            partner_ids: app.chosenPartnerData,
                            room_id: app.rooms[index].id,
                            locker_id: app.rooms[index].locker_id,
                            is_single: false,
                            partner_id: 0,
                        };
                    }

                    await axios({
                        method: 'post',
                        url: '{{ route('admin.monitor.add_more_partner') }}',
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
                    }).then(function (response) {
                        if(response){
                            if(response.status === 200){
                                console.log(response);
                                if(!isSingle){
                                    let chosenPartners = app.chosenPartner;
                                    console.log(chosenPartners);
                                    for(let i=0; i<chosenPartners.length; i++){
                                        let nPartner = {
                                            'partner_id': chosenPartners[i].id,
                                            'partner_number': chosenPartners[i].number,
                                        };
                                        if(app.rooms[index].package_type === 'athena' && app.rooms[index].partners.length < app.rooms[index].max_partners){
                                            app.rooms[index].partners.push(nPartner);
                                        }
                                        else{
                                            app.rooms[index].partners[0] = nPartner;
                                        }
                                        app.rooms[index].timer = response.data.duration;
                                        app.rooms[index].status_id = 6;
                                        app.rooms[index].class = 'custom-square red';
                                        let idx = app.getIndexNumberPartner(chosenPartners[i]);
                                        app.partners[idx].status_id = 6;
                                        app.partners[idx].class = 'custom-circle red';
                                    }
                                }
                                else{
                                    let nPartner = {
                                        'partner_id': app.activePartner.id,
                                        'partner_number': app.activePartner.number,
                                    };
                                    if(app.rooms[index].package_type === 'athena' && app.rooms[index].partners.length < app.rooms[index].max_partners){
                                        app.rooms[index].partners.push(nPartner);
                                    }
                                    else{
                                        app.rooms[index].partners[0] = nPartner;
                                    }
                                    app.rooms[index].timer = response.data.duration;
                                    app.rooms[index].status_id = 6;
                                    app.rooms[index].class = 'custom-square red';
                                    let idx = app.getIndexNumberPartner(app.activePartner);
                                    app.partners[idx].status_id = 6;
                                    app.partners[idx].class = 'custom-circle red';
                                }

                                app.countDownTimer(index);

                                One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', message: 'Sukses memilih Therapis untuk Room ' + app.rooms[index].number + '!'});
                            }
                            else{
                                One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Maaf terjadi kesalahan!'});
                            }
                        }
                    });

                    app.stopRefresh = false;
                    app.loadingAddPartner = false;
                    $('#btnAddMorePartner').show();
                    modalAddPartner.hide();
                    await app.refreshData();
                },
                async setPartnerToRoom(index, partner){
                    //console.log(index);
                    //axios to set partner to a room
                    app.fcLoading = true;
                    app.stopRefresh = true;
                    if(app.rooms[index].package_type !== 'athena' && app.rooms[index].partners.length >= app.rooms[index].max_partners){
                        One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Therapis sudah terpilih!'});
                    }
                    else{
                        await axios({
                            method: 'post',
                            url: '{{ route('admin.monitor.set_partner_to_room') }}',
                            data: {
                                partner_id: partner.id,
                                room_id: app.rooms[index].id,
                                locker_id: app.rooms[index].locker_id,
                            }
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
                            if(response){
                                if(response.status === 200){
                                    let nPartner = {
                                        'partner_id': partner.id,
                                        'partner_number': partner.number,
                                    };
                                    if(app.rooms[index].type === 'athena' && app.rooms[index].partners.length < app.rooms[index].max_partners){
                                        app.rooms[index].partners.push(nPartner);
                                    }
                                    else{
                                        app.rooms[index].partners[0] = nPartner;
                                    }
                                    app.rooms[index].timer = response.data.duration;
                                    app.rooms[index].status_id = 6;
                                    app.rooms[index].class = 'custom-square red';
                                    let idx = app.getIndexNumberPartner(partner);
                                    app.partners[idx].status_id = 6;
                                    app.partners[idx].class = 'custom-circle red';

                                    app.countDownTimer(index);

                                    One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', message: 'Sukses memilih Therapis untuk Room ' + app.rooms[index].number + '!'});
                                }
                                else{
                                    One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Maaf terjadi kesalahan!'});
                                }
                            }
                        });
                    }
                    await app.refreshData();
                    app.fcLoading = false;
                    app.stopRefresh = false;
                },
                async confirmBooking(){
                    app.bookingLoading = true;
                    $('#btn_cancel_booking').hide();
                    $('#btn_submit_booking').hide();

                    await axios({
                        method: 'post',
                        url: '{{ route('admin.monitor.book_partner') }}',
                        data: {
                            partner_id: app.activePartner.id,
                            book_name: app.bookingName
                        }
                    }).catch(function (error){
                        if(error.response){
                            if(error.response.status === 400 || error.response.status === 404){
                                One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: error.response.data});
                            }
                            if(error.response.status === 500){
                                One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Maaf terjadi kesalahan!'});
                            }
                        }
                        app.bookingName = '';
                        app.bookingLoading = false;
                        $('#btn_cancel_booking').show();
                        $('#btn_submit_booking').show();
                        modalPartnerBooking.hide();
                    }).then(function (response) {
                        if(response.status === 200){
                            let index = app.getIndexNumberPartner(app.activePartner);
                            app.partners[index].book_name = app.bookingName;
                            app.partners[index].class = 'custom-circle yellow';
                            app.partners[index].status_id = 11;

                            One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', message: 'Sukses booking Therapis ' + app.partners[index].number + '!'});
                        }
                        else{
                            One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Maaf terjadi kesalahan!'});
                        }
                    });

                    app.bookingName = '';
                    app.bookingLoading = false;
                    $('#btn_cancel_booking').show();
                    $('#btn_submit_booking').show();
                    modalPartnerBooking.hide();
                    await app.refreshData();
                },
                async bookingPartner(){
                    if(app.activePartner.status_id === 11){
                        One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Therapis sudah dibooking!'});
                    }
                    else{
                        app.fcLoading = true;
                        app.stopRefresh = true;
                        await axios({
                            method: 'post',
                            url: '{{ route('admin.monitor.set_partner_status') }}',
                            data: {
                                partner_id: app.activePartner.id,
                                status_id: 11,
                            }
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
                                let index = app.getIndexNumberPartner(app.activePartner);
                                app.partners[index].class = 'custom-circle yellow';
                                app.partners[index].status_id = 11;

                                One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', message: 'Sukses booking Therapis ' + app.partners[index].number + '!'});
                            }
                            else{
                                One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Maaf terjadi kesalahan!'});
                            }
                        });
                        app.fcLoading = false;
                        app.stopRefresh = false;
                        await app.refreshData();
                    }
                },
                async cancelBooking(){
                    if(app.activePartner.status_id !== 11){
                        One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Therapis sedang tidak dibooking!'});
                    }
                    else{
                        app.fcLoading = true;
                        app.stopRefresh = true;
                        await axios({
                            method: 'post',
                            url: '{{ route('admin.monitor.set_partner_status') }}',
                            data: {
                                partner_id: app.activePartner.id,
                                status_id: 5,
                            }
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
                                let index = app.getIndexNumberPartner(app.activePartner);
                                app.partners[index].class = 'custom-circle green';

                                One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', message: 'Sukses cancel booking Therapis ' + app.partners[index].number + '!'});
                            }
                            else{
                                One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Maaf terjadi kesalahan!'});
                            }
                        });
                        app.fcLoading = false;
                        app.stopRefresh = false;
                        await app.refreshData();
                    }
                },
                async cancelDoubleBooking(){
                    if(app.activePartner.status_id !== 6 && app.activePartner.is_double_book){
                        One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Therapis sedang tidak di-double booking!'});
                    }
                    else{
                        app.fcLoading = true;
                        app.stopRefresh = true;
                        await axios({
                            method: 'post',
                            url: '{{ route('admin.monitor.set_partner_status') }}',
                            data: {
                                partner_id: app.activePartner.id,
                                status_id: 6,
                                cancel_booking: true,
                            }
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
                                let index = app.getIndexNumberPartner(app.activePartner);
                                app.partners[index].class = 'custom-circle red';

                                One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', message: 'Sukses cancel booking Therapis ' + app.partners[index].number + '!'});
                            }
                            else{
                                One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Maaf terjadi kesalahan!'});
                            }
                        });
                        app.fcLoading = false;
                        app.stopRefresh = false;
                        await app.refreshData();
                    }
                },
                async setPartnerToRest(){
                    app.fcLoading = true;
                    app.stopRefresh = true;
                    await axios({
                        method: 'post',
                        url: '{{ route('admin.monitor.set_partner_status') }}',
                        data: {
                            partner_id: app.activePartner.id,
                            status_id: 12,
                        }
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
                            let index = app.getIndexNumberPartner(app.activePartner);
                            app.partners[index].class = 'custom-circle grey';
                            app.partners[index].status_id = 12;

                            One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', message: 'Sukses membuat therapis istirahat ' + app.partners[index].number + '!'});
                        }
                        else{
                            One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Maaf terjadi kesalahan!'});
                        }
                    });
                    app.fcLoading = false;
                    app.stopRefresh = false;
                    await app.refreshData();
                },
                async setPartnerAvailable(){
                    app.fcLoading = true;
                    app.stopRefresh = true;
                    await axios({
                        method: 'post',
                        url: '{{ route('admin.monitor.set_partner_status') }}',
                        data: {
                            partner_id: app.activePartner.id,
                            status_id: 5,
                        }
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
                            let index = app.getIndexNumberPartner(app.activePartner);
                            app.partners[index].class = 'custom-circle green';
                            app.partners[index].status_id = 5;

                            One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', message: 'Sukses membuat therapis available ' + app.partners[index].number + '!'});
                        }
                        else{
                            One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Maaf terjadi kesalahan!'});
                        }
                    });
                    app.fcLoading = false;
                    app.stopRefresh = false;
                    await app.refreshData();
                },
                async setRoomToAvailable(){
                    app.fcLoading = true;
                    app.stopRefresh = true;
                    await axios({
                        method: 'post',
                        url: '{{ route('admin.monitor.set_room_status') }}',
                        data: {
                            room_id: app.activeRoom.id,
                            status_id: 5,
                        }
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
                            let index = app.getIndexNumberRoom(app.activeRoom);
                            app.rooms[index].class = 'custom-square green';
                            app.rooms[index].status_id = 5;
                            One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', message: 'Sukses buat Room Available ' + app.rooms[index].number + '!'});
                        }
                        else{
                            One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Maaf terjadi kesalahan!'});
                        }
                    });
                    app.fcLoading = false;
                    app.stopRefresh = false;
                    await app.refreshData();
                },
                async setRoomToBeClean(){
                    app.fcLoading = true;
                    app.stopRefresh = true;
                    await axios({
                        method: 'post',
                        url: '{{ route('admin.monitor.set_room_status') }}',
                        data: {
                            room_id: app.activeRoom.id,
                            status_id: 4,
                        }
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
                            let index = app.getIndexNumberRoom(app.activeRoom);
                            app.rooms[index].class = 'custom-square pink';
                            app.rooms[index].status_id = 4;
                            One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', message: 'Sukses buat Room Dibersihkan ' + app.rooms[index].number + '!'});
                        }
                        else{
                            One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Maaf terjadi kesalahan!'});
                        }
                    });
                    app.fcLoading = false;
                    app.stopRefresh = false;
                    await app.refreshData();
                },
                async bookingRoom(){
                    if(app.activeRoom.status_id === 11){
                        One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Room sudah dibooking!'});
                    }
                    else{
                        app.fcLoading = true;
                        app.stopRefresh = true;
                        await axios({
                            method: 'post',
                            url: '{{ route('admin.monitor.set_room_status') }}',
                            data: {
                                room_id: app.activeRoom.id,
                                status_id: 11,
                            }
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
                                let index = app.getIndexNumberRoom(app.activeRoom);
                                app.rooms[index].class = 'custom-square yellow';
                                app.rooms[index].status_id = 11;

                                One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', message: 'Sukses booking Room ' + app.rooms[index].number + '!'});
                            }
                            else{
                                One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Maaf terjadi kesalahan!'});
                            }
                        });
                        app.fcLoading = false;
                        app.stopRefresh = false;
                        await app.refreshData();
                    }
                },
                async cancelBookingRoom(){
                    if(app.activeRoom.status_id !== 11){
                        One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Room sedang tidak dibooking!'});
                    }
                    else{
                        app.fcLoading = true;
                        app.stopRefresh = true;
                        await axios({
                            method: 'post',
                            url: '{{ route('admin.monitor.set_room_status') }}',
                            data: {
                                room_id: app.activeRoom.id,
                                status_id: 5,
                            }
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
                                let index = app.getIndexNumberRoom(app.activeRoom);
                                app.rooms[index].class = 'custom-square green';
                                app.rooms[index].status_id = 5;

                                One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', message: 'Sukses cancel booking Room ' + app.rooms[index].number + '!'});
                            }
                            else{
                                One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Maaf terjadi kesalahan!'});
                            }
                        });
                        app.fcLoading = false;
                        app.stopRefresh = false;
                        await app.refreshData();
                    }
                },
                async cancelDoubleBookingRoom(){
                    if(app.activeRoom.status_id !== 6 && app.activeRoom.is_double_book){
                        One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Room sedang tidak di-double booking!'});
                    }
                    else{
                        app.fcLoading = true;
                        app.stopRefresh = true;
                        await axios({
                            method: 'post',
                            url: '{{ route('admin.monitor.set_room_status') }}',
                            data: {
                                room_id: app.activeRoom.id,
                                status_id: 6,
                                cancel_booking: true,
                            }
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
                                let index = app.getIndexNumberRoom(app.activeRoom);
                                app.rooms[index].class = 'custom-square red';
                                app.rooms[index].status_id = 6;

                                One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', message: 'Sukses cancel booking Room ' + app.rooms[index].number + '!'});
                            }
                            else{
                                One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Maaf terjadi kesalahan!'});
                            }
                        });
                        app.fcLoading = false;
                        app.stopRefresh = false;
                        await app.refreshData();
                    }
                },
                async setPartnerToRoomByClick(partner){
                    //axios to set partner to a room
                    let index = app.getIndexNumberRoom(app.activeRoom);
                    await axios({
                        method: 'post',
                        url: '{{ route('admin.monitor.set_partner_to_room') }}',
                        data: {
                            partner_id: partner.id,
                            room_id: app.activeRoom.id,
                            locker_id: app.activeRoom.locker_id,
                        }
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
                        if(response){
                            if(response.status === 200){
                                app.rooms[index].partner_id = partner.id;
                                app.rooms[index].partner_number = partner.number;
                                app.rooms[index].timer = response.data.duration;
                                app.rooms[index].status_id = 6;
                                app.rooms[index].class = 'custom-square red';
                                let idx = app.getIndexNumberPartner(partner);
                                app.partners[idx].status_id = 6;
                                app.partners[idx].class = 'custom-circle red';

                                app.countDownTimer(index);

                                One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', message: 'Sukses memilih Therapis untuk Room ' + app.rooms[index].number + '!'});
                            }
                            else{
                                One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Maaf terjadi kesalahan!'});
                            }
                        }
                    });
                    modalPartners.hide();
                    await app.refreshData();
                },
                async getMorePartners(){
                    app.loadingGetPartner = true;
                    await axios({
                        method: 'get',
                        url: '{{ route('admin.partner.get') }}',
                    }).then(function (response) {
                        if(response.status === 200){
                            app.morePartners = response.data.partners;
                        }
                    });
                    app.loadingGetPartner = false;
                },
                async roomMenuClicked(item){
                    contextMenu.classList.remove("visible");
                    app.activeRoomMenu = item;

                    if(item === 'Set Available'){
                        await app.setRoomToAvailable();
                    }
                    else if(item === 'Dibersihkan'){
                        await app.setRoomToBeClean();
                    }
                    else if(item === 'Pilih Therapis'){
                        modalPartners.show();
                    }
                    else if(item === 'Extend'){
                        modalExtendConfirmation.show();
                    }
                    else if(item === 'Telpon Room'){
                        modalCallRoom.show();
                    }
                    else if(item === 'Tambah Orang'){
                        modalAddPeople.show();
                    }
                    else if(item === 'Tambah Therapis'){
                        await app.getMorePartners();
                        modalAddPartner.show();
                    }
                    else if(item === 'Booking'){
                        await app.bookingRoom();
                    }
                    else if(item === 'Cancel Booking'){
                        await app.cancelBookingRoom();
                    }
                    else if(item === 'Cancel Double Booking'){
                        await app.cancelDoubleBookingRoom();
                    }
                    else if(item === 'Start Treatment'){
                        await app.showBookingModal();
                    }
                    else if(item === 'Checkout Therapis'){
                        await app.getCurrentWorkingPartner();
                        modalCheckoutPartner.show();
                    }
                    else if(item === 'Cancel Therapis'){
                        await app.getCurrentWorkingPartner();
                        modalCancelPartner.show();
                    }
                    else if(item === 'Ubah Therapis'){
                        await app.getChangePartnerOptions();
                        if(app.activeRoom.package_type === 'athena'){
                            let url = '{{ route('admin.monitor.change_partner_multiple_view') }}?locker_id='
                                + app.activeRoom.locker_id + '&room_id=' + app.activeRoom.id;
                            window.open(url);
                        }
                        else{
                            modalChangePartnerSingle.show();
                        }
                    }
                },
                async partnerMenuClicked(item){
                    partnerMenu.classList.remove("visible");
                    app.activePartnerMenu = item;

                    if(item === 'Booking'){
                        modalPartnerBooking.show();
                        $('#booking-text').focus();
                    }
                    else if(item === 'Double Book'){
                        await app.bookingPartner();
                    }
                    else if(item === 'Cancel Booking'){
                        await app.cancelBooking();
                    }
                    else if(item === 'Cancel Double Booking'){
                        await app.cancelDoubleBooking();
                    }
                    else if(item === 'Istirahat'){
                        await app.setPartnerToRest();
                    }
                    else if(item === 'Set Available'){
                        await app.setPartnerAvailable();
                    }
                },
                getIndexNumberRoom(item){
                    let idx = 0;
                    for(let i=0; i<app.rooms.length; i++){
                        if(item.id === app.rooms[i].id){
                            idx = i;
                        }
                    }
                    return idx;
                },
                getIndexNumberPartner(item){
                    let idx = 0;
                    for(let i=0; i<app.partners.length; i++){
                        if(item.id === app.partners[i].id){
                            idx = i;
                        }
                    }
                    return idx;
                },
                async extendSubmit(){
                    app.stopRefresh = true;
                    let idx = app.getIndexNumberRoom(app.activeRoom);
                    app.changePartnerMessage = '';
                    let data = {
                        locker_id: app.rooms[idx].locker_id,
                        room_id: app.rooms[idx].id,
                        changePartner: app.changePartner,
                        partner_id: app.extendPartnerId,
                    };

                    app.loadingExtendSubmit = true;
                    //disable buttons
                    $('#btn_extend_cancel').hide();
                    $('#btn_extend_submit').hide();

                    await axios({
                        method: 'post',
                        url: '{{ route('admin.monitor.extend') }}',
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

                        app.loadingExtendSubmit = false;
                        //disable buttons
                        $('#btn_extend_cancel').show();
                        $('#btn_extend_submit').show();
                        modalExtendConfirmation.hide();
                    }).then(function (response) {
                        console.log(response.data.extend_duration);
                        app.rooms[idx].timer += response.data.extend_duration;
                        app.rooms[idx].class = 'custom-square red';
                        One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', message: 'Room Sukses di-Extend!'});
                    });
                    app.loadingExtendSubmit = false;
                    $('#btn_extend_cancel').show();
                    $('#btn_extend_submit').show();
                    modalExtendConfirmation.hide();
                    app.stopRefresh = true;
                    await app.refreshData();
                },
                async doneCallRoom(){
                    let idx = app.getIndexNumberRoom(app.activeRoom);
                    console.log('idx: ' + idx);
                    let data = {
                        locker_id: app.rooms[idx].locker_id,
                        room_id: app.rooms[idx].id
                    };

                    app.loadingCallRoom = true;
                    //disable buttons
                    $('#btn_call_room').hide();

                    await axios({
                        method: 'post',
                        url: '{{ route('admin.monitor.done_call_room') }}',
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
                    }).then(function (response) {
                        app.rooms[idx].is_called = 1;
                        app.rooms[idx].class = 'custom-square red';
                        console.log(app.rooms[idx]);
                        One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', message: 'Sukses call room!'});
                    });
                    app.loadingCallRoom = false;
                    $('#btn_call_room').show();
                    modalCallRoom.hide();
                    await app.refreshData();
                },
                async addMorePeople(){
                    let qty = $('#addMorePeopleQty').val();
                    if(app.activeRoom.package_type === 'athena'){
                        let idx = app.getIndexNumberRoom(app.activeRoom);
                        let data = {
                            locker_id: app.rooms[idx].locker_id,
                            room_id: app.rooms[idx].id,
                            qty: qty,
                        };

                        app.loadingAddPeople = true;
                        //disable buttons
                        $('#btn_submit_people').hide();

                        await axios({
                            method: 'post',
                            url: '{{ route('admin.monitor.add_more_people') }}',
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
                        }).then(function (response) {
                            One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', message: 'Sukses menambah orang ('+ qty +')!'});
                        });
                        app.loadingAddPeople = false;
                        $('#addMorePeopleQty').val(0);
                        $('#btn_submit_people').show();
                        modalAddPeople.hide();
                        await app.refreshData();
                    }
                    else{
                        One.helpers('jq-notify', {type: 'danger', icon: 'fa fa-info-circle me-1', message: 'Paket untuk room ini bukan tipe Athena!'});
                    }
                },
                isThereBooking(roomId){
                    let result = app.bookings.filter(n=> { return n.room_id === roomId});
                    if(result.length > 0){
                        return result[0];
                    }
                    else{
                        return null;
                    }
                },
                async book(){
                    app.fcLoading = true;

                    console.log(app.booking);
                    let data = {
                        room_id: app.activeRoom.id,
                        partner_id: app.activePartner.id,
                    };

                    await axios({
                        method: 'post',
                        url: '{{ route('admin.booking.book') }}',
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
                    }).then(function (response) {
                        One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', message: 'Sukses Booking!'});
                    });

                    await app.refreshData();
                    app.fcLoading = false;
                },
                async showBookingModal(){
                    let tmpBooking = app.isThereBooking(app.activeRoom.id);
                    if(tmpBooking == null){
                        One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Belum ada Therapis yang diajak ke sini!'});
                    }
                    else{
                        //get packages
                        await axios({
                            method: 'get',
                            url: '{{ route('admin.package.select_get') }}?room_id=' + app.activeRoom.id,
                        }).catch(function (error){
                            console.log(error);
                        }).then(function (response) {
                            console.log(response);
                            if(response.status === 200){
                                app.bookingPackages = response.data;
                                console.log(app.bookingPackages);
                            }
                        });
                        modalStartTreatment.show();
                    }
                },
                async startTreatment(){
                    app.booking.isLoading = true;
                    $('#btn_submit_booking').hide();

                    let tmpBooking = app.isThereBooking(app.activeRoom.id);
                    if(tmpBooking == null){
                        One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Belum ada Therapis yang diajak ke sini!'});
                    }

                    let data = {
                        package_id: app.booking.packageId,
                        locker_id: app.booking.lockerId,
                        room_id: tmpBooking.room_id,
                        partner_id: tmpBooking.partner_id
                    };

                    await axios({
                        method: 'post',
                        url: '{{ route('admin.booking.start_treatment') }}',
                        data: data,
                    }).catch(function (error){
                        console.log(error);
                        if(error.response){
                            if(error.response.status === 400 || error.response.status === 404){
                                One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: error.response.data});
                            }
                            if(error.response.status === 500){
                                One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Maaf terjadi kesalahan!'});
                            }
                        }
                        app.booking.isLoading = false;
                        app.booking.lockerId = 0;
                        $('#btn_submit_booking').show();
                        modalStartTreatment.hide();
                    }).then(function (response) {
                        app.booking.lockerId = 0;
                        One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', message: 'Sukses Booking!'});
                    });

                    app.booking.isLoading = false;
                    $('#btn_submit_booking').show();
                    modalStartTreatment.hide();
                    await app.refreshData();
                },
                async getChangePartnerOptions(){
                    await axios({
                        method: 'get',
                        url: '{{ route('admin.partner.select_change') }}',
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
                        app.changePartnerOptions = response.data;
                    });
                },
                async changePartnerSingle(){
                    app.changePartnerLoading = true;
                    $('#btn_submit_change_partner').hide();

                    if(app.changePartnerId === 0){
                        app.changePartnerMsg = 'Therapis harus dipilih!';
                    }
                    else{
                        let success = true;

                        await axios({
                            method: 'post',
                            url: '{{ route('admin.monitor.change_partner_single') }}',
                            data: {
                                locker_id: app.activeRoom.locker_id,
                                partner_id: app.changePartnerId,
                            },
                        }).catch(function (error){
                            if(error.response){
                                if(error.response.status === 400 || error.response.status === 404){
                                    One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: error.response.data});
                                }
                                if(error.response.status === 500){
                                    One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Maaf terjadi kesalahan!'});
                                }
                            }
                            app.changePartnerLoading = false;
                            app.changePartnerId = 0;
                            $('#btn_submit_change_partner').show();
                            success = false;
                        }).then(function (response) {
                            if(success){
                                console.log(response);
                                app.booking.lockerId = 0;
                                One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', message: 'Sukses Ubah Partner!'});
                            }
                        });

                        app.changePartnerLoading = false;
                        $('#btn_submit_change_partner').show();
                        modalChangePartnerSingle.hide();
                        await app.refreshData();
                    }
                },
                async getCurrentWorkingPartner(){
                    let url = '{{ route('admin.current.partner.get') }}?locker_id=' + app.activeRoom.locker_id;

                    await axios({
                        method: 'get',
                        url: url,
                    }).then(function (response) {
                        if(response.status === 200){
                            app.checkoutPartnerOptions = response.data;
                        }
                    });
                },
                async confirmCheckoutPartner(item){
                    //checkout partner
                    let index = app.checkoutPartnerOptions.indexOf(item);
                    $('#' + app.checkoutPartnerOptions[index].btn_id).hide();
                    $('#' + app.checkoutPartnerOptions[index].loading_id).show();

                    let data = {
                        locker_id: app.activeRoom.locker_id,
                        id: item.id,
                    };

                    await axios({
                        method: 'post',
                        url: '{{ route('admin.room.partner.checkout') }}',
                        data: data,
                    }).catch(function (error){
                        if(error.response){
                            if(error.response.status === 400 || error.response.status === 404){
                                app.checkoutPartnerErrorMsg = error.response.data;
                                app.checkoutPartnerSuccessMsg = '';
                            }
                            if(error.response.status === 500){
                                app.checkoutPartnerErrorMsg = 'Maaf terjadi kesalahan!';
                                app.checkoutPartnerSuccessMsg = '';
                            }
                        }
                    }).then(function (response) {
                        app.checkoutPartnerErrorMsg = '';
                        app.checkoutPartnerSuccessMsg = 'Sukses Checkout Partner!';
                    });

                    $('#' + app.checkoutPartnerOptions[index].btn_id).show();
                    $('#' + app.checkoutPartnerOptions[index].loading_id).hide();
                    await app.getCurrentWorkingPartner();
                },
                async confirmCancelPartner(item){
                    //checkout partner
                    if(app.cancelPartnerPassword === ''){
                        app.checkoutPartnerErrorMsg = 'Password harus diisi!';
                    }
                    else{
                        let index = app.checkoutPartnerOptions.indexOf(item);
                        $('#' + app.checkoutPartnerOptions[index].btn_id).hide();
                        $('#' + app.checkoutPartnerOptions[index].loading_id).show();

                        let data = {
                            locker_id: app.activeRoom.locker_id,
                            id: item.id,
                            password: app.cancelPartnerPassword,
                        };

                        await axios({
                            method: 'post',
                            url: '{{ route('admin.room.partner.cancel') }}',
                            data: data,
                        }).catch(function (error){
                            if(error.response){
                                if(error.response.status === 400 || error.response.status === 404){
                                    app.checkoutPartnerErrorMsg = error.response.data;
                                    app.checkoutPartnerSuccessMsg = '';
                                    console.log(error.response.data);
                                }
                                if(error.response.status === 500){
                                    app.checkoutPartnerErrorMsg = 'Maaf terjadi kesalahan!';
                                    app.checkoutPartnerSuccessMsg = '';
                                }
                            }
                            app.cancelPartnerPassword = '';
                        }).then(function (response) {
                            if(response.status === 200){
                                app.checkoutPartnerErrorMsg = '';
                                app.checkoutPartnerSuccessMsg = 'Sukses Cancel Partner!';
                            }
                        });

                        app.cancelPartnerPassword = '';
                        $('#' + app.checkoutPartnerOptions[index].btn_id).show();
                        $('#' + app.checkoutPartnerOptions[index].loading_id).hide();
                        await app.getCurrentWorkingPartner();
                    }
                },
            },
            created(){
                this.allTimerOn();
                setInterval(async () => {
                    if(!app.stopRefresh){
                        await app.refreshData();
                    }
                }, 5000);
            }
        });
    </script>
    <script>
        function allowDrop(ev) {
            ev.preventDefault();
        }

        function drag(ev) {
            ev.dataTransfer.setData("text", ev.target.id);
        }

        function drop(ev) {
            ev.preventDefault();
            let data = ev.dataTransfer.getData("text");
            let tmpPartner = app.partners.filter(n=> { return n.element_id === data});
            let tmpRoom = app.rooms.filter(n=> { return n.element_id === ev.target.id});
            if(ev.target.id === null || ev.target.id === ''){
                tmpRoom = app.rooms.filter(n=> { return n.element_id === ev.target.parentElement.id});
            }

            let index = app.getIndexNumberRoom(tmpRoom[0]);
            let idxPartner = app.getIndexNumberPartner(tmpPartner[0]);
            // app.rooms[index].partner_number = tmpPartner[0].number;
            // app.rooms[index].timer = 20;
            // app.countDownTimer(index);

            app.activePartner = app.partners[idxPartner];
            app.activeRoom = app.rooms[index];

            if(app.activePartner.status_id === 11 && app.activeRoom.status_id === 11){
                app.book();
            }
            else if(index > -1){
                console.log(app.activeRoom);
                if(app.activeRoom.partners.length >= app.activeRoom.max_partners){
                    console.log('1');
                    app.addMorePartner(true);
                }
                else{
                    console.log('2');
                    app.setPartnerToRoom(index, tmpPartner[0]);
                }
            }
        }
    </script>
    <script>
        let modalPartners = new bootstrap.Modal(document.getElementById('modal-partners'), {'backdrop': 'static', 'keyboard' :false });
        let modalExtendConfirmation = new bootstrap.Modal(document.getElementById('modal-extend-confirmation'), {'backdrop': 'static', 'keyboard' :false });
        let modalCallRoom = new bootstrap.Modal(document.getElementById('modal-call-room'), {'backdrop': 'static', 'keyboard' :false });
        let modalAddPeople = new bootstrap.Modal(document.getElementById('modal-add-people'), {'backdrop': 'static', 'keyboard' :false });
        let modalAddPartner = new bootstrap.Modal(document.getElementById('modal-more-partners'), {'backdrop': 'static', 'keyboard' :false });
        let modalStartTreatment = new bootstrap.Modal(document.getElementById('modal-start-treatment'), {'backdrop': 'static', 'keyboard' :false });
        let modalChangePartnerSingle = new bootstrap.Modal(document.getElementById('modal-change-partner-single'), {'backdrop': 'static', 'keyboard' :false });
        let modalPartnerBooking = new bootstrap.Modal(document.getElementById('modal-booking'), {'backdrop': 'static', 'keyboard' :false });
        let modalCheckoutPartner = new bootstrap.Modal(document.getElementById('modal-checkout-partner'), {'backdrop': 'static', 'keyboard' :false });
        let modalCancelPartner = new bootstrap.Modal(document.getElementById('modal-cancel-partner'), {'backdrop': 'static', 'keyboard' :false });
        //let modalChangePartnerMany = new bootstrap.Modal(document.getElementById('modal-change-partner-many'), {'backdrop': 'static', 'keyboard' :false });

        const contextMenu = document.getElementById("context-menu");
        const partnerMenu = document.getElementById("partner-menu");
        const scope = document.querySelector("body");

        scope.addEventListener("contextmenu", (event) => {
            event.preventDefault();
            // let tmp = event.path[0].getAttribute('class');
            // let id = event.path[0].getAttribute('id');
            // if(id === null){
            //     id = event.path[1].getAttribute('id');
            // }
            // if(tmp === null){
            //     tmp = event.path[1].getAttribute('class');
            // }

            let tmp = event.srcElement.className;
            let id = event.srcElement.id;
            if(id === null || id === ''){
                id = event.srcElement.parentNode.id;
                tmp = event.srcElement.parentNode.className;
            }

            if(tmp.includes('custom-square')){
                partnerMenu.classList.remove("visible");
                const { clientX: mouseX, clientY: mouseY } = event;

                contextMenu.style.top = `${mouseY}px`;
                contextMenu.style.left = `${mouseX}px`;

                contextMenu.classList.remove("visible");

                setTimeout(() => {
                    contextMenu.classList.add("visible");
                });

                let tmpRoom = app.rooms.filter(n=> { return n.element_id === id});
                let index = app.getIndexNumberRoom(tmpRoom[0]);
                app.activeRoom = app.rooms[index];

                if(app.activeRoom.status_id === 6){
                    app.roomMenus = app.onGoingMenu;
                }
                else if(app.activeRoom.status_id === 4){
                    app.roomMenus = app.cleaningMenu;
                }
                else if(app.activeRoom.status_id === 11 && app.activeRoom.locker_id !== 0){
                    app.roomMenus = app.normalMenuWithTrx;
                }
                else{
                    app.roomMenus = app.normalMenu;
                }
            }

            if(tmp.includes('custom-circle')){
                contextMenu.classList.remove("visible");
                const { clientX: mouseX, clientY: mouseY } = event;

                partnerMenu.style.top = `${mouseY}px`;
                partnerMenu.style.left = `${mouseX}px`;

                partnerMenu.classList.remove("visible");

                setTimeout(() => {
                    partnerMenu.classList.add("visible");
                });

                let tmpPartner = app.partners.filter(n=> { return n.element_id === id});
                let index = app.getIndexNumberPartner(tmpPartner[0]);
                app.activePartner = app.partners[index];

                if(app.activePartner.status_id === 5){
                    app.partnerMenus = app.partnerAvailMenu;
                }
                else if(app.activePartner.status_id === 12){
                    app.partnerMenus = app.partnerRestMenu;
                }
                else if(app.activePartner.status_id === 6 && app.activePartner.is_double_book){
                    app.partnerMenus = app.partnerDoubleBookMenu;
                }
                else if(app.activePartner.status_id === 6){
                    app.partnerMenus = app.partnerOnWorkMenu;
                }
            }
        });

        scope.addEventListener("click", (e) => {
            if (e.target.offsetParent !== contextMenu) {
                contextMenu.classList.remove("visible");
            }

            if (e.target.offsetParent !== partnerMenu) {
                partnerMenu.classList.remove("visible");
            }
        });
    </script>
@endsection
