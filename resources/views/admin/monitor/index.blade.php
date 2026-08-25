@extends('layouts.admin')

@section('title')
    <title>BACKEND - Monitor</title>
@endsection

@section('content')
    <div class="content" id="app">
        <div class="row">
            <div class="col-lg-6">
                <!-- Hero -->
                <div>
                    <div class="content space-top">
                        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center">
                            <div class="flex-grow-1">
                                <h1 class="h3 fw-bold mb-2">
                                    Therapis
                                </h1>
                            </div>
                            <div class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3 d-flex justify-content-end">
                                <div class='circle-custom green'></div>&nbsp;Available&nbsp;
                                <div class='circle-custom red ms-2'></div>&nbsp;Bekerja&nbsp;
{{--                                <div class='circle-custom yellow ms-2'></div>&nbsp;Dibooking&nbsp;--}}
{{--                                <div class='circle-custom work-book-bg ms-2'></div>&nbsp;Double Book&nbsp;--}}
                                <div class='circle-custom grey ms-2'></div>&nbsp;Istirahat&nbsp;
                            </div>
                        </div>
                    </div>
                </div>

                <div style="overflow-y: scroll; height: 100vh;">
                    <div class="content">
                        <div class="block block-rounded block-link-pop">
                            <div class="block-content block-content-full">
                                <div class="row row-cols-auto">
                                    <div class="col mb-4 h-auto" v-for="(item, index) in partners" :key="item.id">
                                        <div :class="item.class" :id="item.element_id" class="d-flex flex-column position-relative h-100">
                                            <div style="aspect-ratio: 1/1; width: 100%; background-color: #2f2f2f">
                                                <img :src="item.img" style="width: 100%; height: 100%; object-fit: cover">
                                            </div>
                                            <div class="p-3">
                                                <span style="font-size: 0.7rem">@{{ item.gender == 'P' ? 'Perempuan' : 'Laki-Laki' }}</span><br>
                                                <span class="fw-bold">@{{ item.alias }}</span><br>
                                                <span style="font-size: 0.7rem;">@{{ item.specialty }}</span>
                                            </div>

{{--                                            <div class="mt-auto" v-if="item.status_id == 12">--}}
{{--                                                <button type="button" class="btn btn-primary w-100" @click="setAvailableModal(item)">Set Available</button>--}}
{{--                                            </div>--}}

                                            <div class="bg-white rounded position-absolute px-1" style="top: 10px; right: 10px; z-index: 10; cursor:pointer;" @click.stop="openMenu(item)">
                                               <i class="fa fa-fw fa-ellipsis-h text-dark"></i>
                                            </div>

                                            <div class="bg-white rounded position-absolute" style="top: 30px; right: 10px; z-index: 10; width: 100px; overflow: hidden" v-if="item.opened">
                                                <p class="m-0 p-2 text-dark tmenu" @click="setPresent(item.id, 0)">Tidak Hadir</p>
                                                <p class="m-0 p-2 text-dark tmenu" v-if="item.status_id != 5" @click="setStatus(item.id, 5)">Available</p>
                                                <p class="m-0 p-2 text-dark tmenu" v-if="item.status_id != 6" @click="setStatus(item.id, 6)">Bekerja</p>
                                                <p class="m-0 p-2 text-dark tmenu" v-if="item.status_id != 12" @click="setStatus(item.id, 12)">Istirahat</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="content space-top">
                            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center">
                                <div class="flex-grow-1">
                                    <h1 class="h3 fw-bold mb-2">
                                        Tidak Hadir
                                    </h1>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content">
                        <div class="block block-rounded block-link-pop">
                            <div class="block-content block-content-full">
                                <div class="row row-cols-auto">
                                    <div class="col mb-4 h-auto" v-for="(item, index) in absents" :key="item.id">
                                        <div :class="item.class" :id="item.element_id" class="d-flex flex-column position-relative h-100">
                                            <div style="aspect-ratio: 1/1; width: 100%; background-color: #2f2f2f">
                                                <img :src="item.img" style="width: 100%; height: 100%; object-fit: cover">
                                            </div>
                                            <div class="p-3">
                                                <span style="font-size: 0.7rem">@{{ item.gender == 'P' ? 'Perempuan' : 'Laki-Laki' }}</span><br>
                                                <span class="fw-bold">@{{ item.alias }}</span><br>
                                                <span style="font-size: 0.7rem;">@{{ item.specialty }}</span>
                                            </div>

{{--                                            <div class="mt-auto" v-if="item.status_id == 12">--}}
{{--                                                <button type="button" class="btn btn-primary w-100" @click="setAvailableModal(item)">Set Available</button>--}}
{{--                                            </div>--}}

                                            <div class="bg-white rounded position-absolute px-1" style="top: 10px; right: 10px; z-index: 10; cursor:pointer;" @click.stop="openMenu(item)">
                                                <i class="fa fa-fw fa-ellipsis-h text-dark"></i>
                                            </div>

                                            <div class="bg-white rounded position-absolute" style="top: 30px; right: 10px; z-index: 10; width: 100px; overflow: hidden" v-if="item.opened">
                                                <p class="m-0 p-2 text-dark tmenu" @click="setPresent(item.id, 1)">Hadir</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END Hero -->
            </div>
            <div class="col-lg-6">
                <!-- Hero -->
                <div>
                    <div class="content space-top">
                        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                            <div class="flex-grow-1">
                                <h1 class="h3 fw-bold mb-2">
                                    Room
                                </h1>
                            </div>
                            <div class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3 d-flex justify-content-end">
                                <div class='circle-custom green'></div>&nbsp;Available&nbsp;
                                <div class='circle-custom red ms-2'></div>&nbsp;Dipakai&nbsp;
{{--                                <div class='circle-custom yellow ms-2'></div>&nbsp;Dibooking&nbsp;--}}
{{--                                <div class='circle-custom work-book-bg ms-2'></div>&nbsp;Double Book&nbsp;--}}
                                <div class='circle-custom pink ms-2'></div>&nbsp;Dibersihkan&nbsp;
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END Hero -->
                <div class="content" style="overflow-y: scroll; height: 100vh;">
                    <div class="block block-rounded block-link-pop">
                        <div class="block-content block-content-full">
                            <div class="row row-cols-auto mb-4">
                                <div class="col mb-3" v-for="(item, index) in rooms">
                                    <div :class="item.class" class="d-flex flex-column p-3" :id="item.element_id" style="height: 210px;" ondrop="drop(event)" ondragover="allowDrop(event)">
                                        <span style="font-size: 16px;" class="fw-bold">@{{ item.number }}</span>
                                        <span v-if="item.trx_status == 9"><strong>P:</strong> @{{ item.package_name }}</span>
                                        <span v-if="item.trx_status == 9"><strong>C:</strong> @{{ item.member_name }}</span>
                                        <span v-if="item.trx_status == 9"><strong>T:</strong> @{{ item.therapists }}</span>

                                        <div class="mt-auto">
                                            <div v-if="item.timer > 0">@{{ item.timer }}m</div>
                                            <div v-if="item.trx_status === 8 && item.timer <= 0" class="close-text">Waktu Habis</div>
                                            <div v-if="item.time_display !== ''">@{{ item.time_display }}</div>
                                        </div>

                                        <div class="mt-auto" v-if="item.status_id == 4">
                                            <button type="button" class="btn btn-primary w-100" @click="setAvailableRoomModal(item)">Set Available</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('partials.monitor._modal')
        @include('partials.monitor.loading')
    </div>
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ asset('css/monitor_data.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/custom_loading.css') }}"/>
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
    <style>
        .tmenu {
            cursor: pointer;
            transition: all ease-in 0.2s;
        }

        .tmenu:hover {
            background-color: #547155 !important;
            color: white !important;
            font-weight: bold !important;
        }
    </style>
@endsection
@section('scripts')
    <!-- Page JS Plugins -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script src="{{ asset('js/vue.js') }}"></script>
    <script src="{{ asset('js/axios.js') }}"></script>
    <!-- Page JS Code -->
    <script src="{{ asset('js/plugins/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>
    <script type="text/x-template" id="select2-template">
        <select>
            <slot></slot>
        </select>
    </script>
    <script src="{{ asset('js/vueComponent-select2-monitor.js') }}"></script>
    <script>

        let app = new Vue({
            el: '#app',
            data: {
                cancelPartnerPassword: '',
                checkoutPartnerErrorMsg: '',
                checkoutPartnerSuccessMsg: '',
                checkoutPartnerOptions: [],
                stopRefresh: false,
                fcLoading: false,
                rooms: @json($jsonRooms),
                partners: @json($jsonPartners),
                absents: @json($jsonAbsents),
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
                changePartnerMsg: '',
                changePartnerId: 0,
                changePartnerLoading: false,
                changePartnerOptions: [],
            },
            computed: {

            },
            methods: {
                openMenu(item){
                    this.cleanMenu();
                    item.opened = !item.opened;
                },

                cleanMenu(){
                    for(var i = 0; i < this.partners.length; i++){
                        this.partners[i].opened = false;
                    }

                    for(var i = 0; i < this.absents.length; i++){
                        this.absents[i].opened = false;
                    }
                },

                allTimerOn(){
                    for (let i=0; i< this.rooms.length; i++) {
                        if (this.rooms[i].timer > 0) {
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
                            this.rooms[index].timer = Math.max(this.rooms[index].timer - 1, 0); // Ensure timer never goes negative
                            this.countDownTimer(index); // Recursively call itself
                        }, 60000);
                    }
                },
                refreshData() {
                    $.ajax({
                        url: '{{ route('admin.monitor.refresh_data') }}',
                        type: 'get',
                        success: function (response) {
                            app.rooms = response.jsonRooms;
                            app.partners = response.jsonPartners;
                            app.absents = response.jsonAbsents;
                        },
                        error: function (xhr, status, error) {
                            console.log(error);
                        }
                    });
                },
                async setAvailableModal(item) {
                    app.activePartner = item;
                    modalSetAvailablePartner.show();
                },
                async setPartnerAvailable(id) {
                    $.ajax({
                        url: '{{ route('admin.monitor.set_partner_available') }}',
                        type: 'post',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        data: {
                            partner_id: id
                        },
                        success: function (response) {
                            modalSetAvailablePartner.hide();
                            app.refreshData();
                            One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', message: response.message});
                        },
                        error: function (xhr, status, error) {
                            One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: xhr.responseText});
                        }
                    });
                },
                async setPresent(id, status) {
                    $('#loadingModal').modal('show');
                    $.ajax({
                        url: '{{ route('admin.monitor.set_present') }}',
                        type: 'post',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        data: {
                            partner_id: id,
                            present: status,
                        },
                        success: function (response) {
                            app.refreshData();
                            One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', message: response.message});
                        },
                        error: function (xhr, status, error) {
                            One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: xhr.responseText});
                        }
                    }).always(function (){
                        $('#loadingModal').modal('hide');
                    });
                },
                async setStatus(id, status) {
                    $('#loadingModal').modal('show');
                    $.ajax({
                        url: '{{ route('admin.monitor.set_status') }}',
                        type: 'post',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        data: {
                            partner_id: id,
                            status: status,
                        },
                        success: function (response) {
                            app.refreshData();
                            One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', message: response.message});
                        },
                        error: function (xhr, status, error) {
                            One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: xhr.responseText});
                        }
                    }).always(function (){
                        $('#loadingModal').modal('hide');
                    });
                },
                async setAvailableRoomModal(room) {
                    app.activeRoom = room;
                    console.log(app.activeRoom);
                    modalSetAvailableRoom.show();
                },
                async clearData(status) {
                    app.activeRoom = null;
                },
                async setRoomAvailable(id) {
                    $.ajax({
                        url: '{{ route('admin.monitor_room.set_room_available') }}',
                        type: 'post',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        data: {
                            room_id: id
                        },
                        success: function (response) {
                            modalSetAvailableRoom.hide();
                            app.refreshData();
                            One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', message: response.message});
                        },
                        error: function (xhr, status, error) {
                            One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: xhr.responseText});
                        }
                    });
                },
            },
            mounted() {
                this.interval = setInterval(() => {
                    app.refreshData();
                }, 5000);
            },
            unmount() {
                clearInterval(this.interval);
            }
        });

        $('body').on('click', function (){
            app.cleanMenu();
        });
    </script>

    <script>
        let modalSetAvailablePartner = new bootstrap.Modal(
            document.getElementById(
                'modal-set-available-partner'
            ), {
                'backdrop': 'static',
                'keyboard' :false
            });

        let modalSetAvailableRoom = new bootstrap.Modal(
            document.getElementById(
                'modal-set-available-room'
            ), {
                'backdrop': 'static',
                'keyboard' :false
            });
    </script>
@endsection
