@extends('layouts.screen')

@section('title')
    <title>Screen</title>
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
                                <div class='circle-custom green'></div>&nbsp;Ready&nbsp;
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
        </div>
    </div>
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ asset('css/monitor_data_not_emp.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/custom_loading.css') }}"/>
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
    <script>
        let tmpRooms = {!! $jsonRooms !!};
        let tmpPartners = {!! $jsonPartners !!};

        let app = new Vue({
            el: '#app',
            data: {
                stopRefresh: false,
                fcLoading: false,
                rooms: tmpRooms,
                partners: tmpPartners,
                morePartners: tmpPartners,
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
                normalMenu: function(){
                    let menu = [
                        'Dibersihkan',
                    ];

                    if(app.activeRoom.status_id !== 11){
                        menu.push('Booking');
                    }
                    else{
                        menu.push('Cancel Booking');
                    }

                    return menu;
                },
                normalMenuWithTrx: function(){
                    return [
                        'Pilih Therapis',
                    ];
                },
                onGoingMenu: function(){
                    if(app.activeRoom.package_type === 'athena' && app.activeRoom.max_partners === app.partners.length){
                        return [
                            'Tambah Orang',
                            'Tambah Therapis',
                            'Extend',
                            'Telpon Room',
                        ];
                    }
                    else{
                        return [
                            'Tambah Orang',
                            'Extend',
                            'Telpon Room',
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
                            }
                            else{
                                console.log(response);
                            }
                        }
                    });
                },
                async addMorePartner(){
                    app.loadingAddPartner = true;
                    app.stopRefresh = true;
                    $('#btnAddMorePartner').hide();
                    let tmpRoom = app.rooms.filter(n=> { return n.id === app.activeRoom.id});
                    let index = app.rooms.indexOf(tmpRoom[0]);

                    if(app.rooms[index].package_type === 'athena' && app.rooms[index].partners.length >= app.rooms[index].max_partners){
                        let data = {
                            partner_ids: app.chosenPartnerData,
                            room_id: app.rooms[index].id,
                            locker_id: app.rooms[index].locker_id,
                        };

                        console.log('data: ' + data);

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
                                    let chosenPartners = app.chosenPartner;
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
                                        let tmpPartner = app.partners.filter(n=> { return n.id === chosenPartners[i].id});
                                        let idx = app.getIndexNumberPartner(tmpPartner[0]);
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
                    }
                    else{
                        One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Room bukan paket Athena!'});
                    }
                    app.stopRefresh = false;
                    app.loadingAddPartner = false;
                    $('#btnAddMorePartner').show();
                    modalAddPartner.hide();
                },
                async setPartnerToRoom(index, partner){
                    //console.log(index);
                    //axios to set partner to a room
                    app.fcLoading = true;
                    app.stopRefresh = true;
                    if(app.rooms[index].package_type !== 'athena' && app.rooms[index].partners.length < app.rooms[index].max_partners){
                        One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Therapis sudah terpilih!'});
                    }
                    {{--else if(app.rooms[index].package_type === 'athena' && app.rooms[index].partners.length){--}}
                        {{--    let data = {--}}
                        {{--        partner_ids: [partner.id],--}}
                        {{--        room_id: app.rooms[index].id,--}}
                        {{--        locker_id: app.rooms[index].locker_id,--}}
                        {{--    };--}}

                        {{--    //console.log(data);--}}

                        {{--    await axios({--}}
                        {{--        method: 'post',--}}
                        {{--        url: '{{ route('admin.monitor.add_more_partner') }}',--}}
                        {{--        data: data--}}
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
                        {{--        if(response){--}}
                        {{--            if(response.status === 200){--}}
                        {{--                let nPartner = {--}}
                        {{--                    'partner_id': partner.id,--}}
                        {{--                    'partner_number': partner.number,--}}
                        {{--                };--}}
                        {{--                if(app.rooms[index].package_type === 'athena' && app.rooms[index].partners.length < app.rooms[index].max_partners){--}}
                        {{--                    app.rooms[index].partners.push(nPartner);--}}
                        {{--                }--}}
                        {{--                else{--}}
                        {{--                    app.rooms[index].partners[0] = nPartner;--}}
                        {{--                }--}}
                        {{--                app.rooms[index].timer = response.data.duration;--}}
                        {{--                app.rooms[index].status_id = 6;--}}
                        {{--                app.rooms[index].class = 'custom-square red';--}}
                        {{--                let idx = app.partners.indexOf(partner);--}}
                        {{--                app.partners[idx].status_id = 6;--}}
                        {{--                app.partners[idx].class = 'custom-circle red';--}}

                        {{--                app.countDownTimer(index);--}}

                        {{--                One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', message: 'Sukses memilih Therapis untuk Room ' + app.rooms[index].number + '!'});--}}
                        {{--            }--}}
                        {{--            else{--}}
                        {{--                One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Maaf terjadi kesalahan!'});--}}
                        {{--            }--}}
                        {{--        }--}}
                        {{--    });--}}
                        {{--}--}}
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
                    app.fcLoading = false;
                    app.stopRefresh = false;
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

                                One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', message: 'Sukses booking Therapis ' + app.partners[index].number + '!'});
                            }
                            else{
                                One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Maaf terjadi kesalahan!'});
                            }
                        });
                        app.fcLoading = false;
                        app.stopRefresh = false;
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
                            let index = app.rooms.indexOf(app.activeRoom);
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
                            let index = app.rooms.indexOf(app.activeRoom);
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
                                let index = app.getIndexNumberPartner(app.activeRoom);
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
                    }
                },
                async cancelBookingRoom(){
                    if(app.activeRoom.status_id !== 11){
                        One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Therapis sedang tidak dibooking!'});
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
                                let index = app.getIndexNumberPartner(app.activeRoom);
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
                    }
                },
                async setPartnerToRoomByClick(partner){
                    //axios to set partner to a room
                    let index = app.rooms.indexOf(app.activeRoom);
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
                },
                async partnerMenuClicked(item){
                    partnerMenu.classList.remove("visible");
                    app.activePartnerMenu = item;

                    if(item === 'Booking'){
                        await app.bookingPartner();
                    }
                    else if(item === 'Cancel Booking'){
                        await app.cancelBooking();
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
                    if(app.extendPartnerId === 0){
                        app.changePartnerMessage = 'Tolong Pilih Therapis!';
                    }
                    else{
                        app.changePartnerMessage = '';
                        let data = {
                            locker_id: app.rooms[idx].locker_id,
                            room_id: app.rooms[idx].id,
                            changePartner: app.changePartner,
                            partner_id: app.extendPartnerId,
                        };
                        console.log(data);

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
                    }
                    app.stopRefresh = true;
                },
                async doneCallRoom(){
                    let idx = app.rooms.indexOf(app.activeRoom);
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
                },
                async addMorePeople(){
                    let qty = $('#addMorePeopleQty').val();
                    if(app.activeRoom.package_type === 'athena'){
                        let idx = app.rooms.indexOf(app.activeRoom);
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
                    }
                    else{
                        One.helpers('jq-notify', {type: 'danger', icon: 'fa fa-info-circle me-1', message: 'Paket untuk room ini bukan tipe Athena!'});
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

            let index = app.rooms.indexOf(tmpRoom[0]);
            // app.rooms[index].partner_number = tmpPartner[0].number;
            // app.rooms[index].timer = 20;
            // app.countDownTimer(index);
            if(index > -1){
                app.setPartnerToRoom(index, tmpPartner[0]);
            }
        }
    </script>
@endsection
