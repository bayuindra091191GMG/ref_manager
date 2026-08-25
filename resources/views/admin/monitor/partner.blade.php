@extends('layouts.admin')

@section('title')
    <title>Monitor Partner</title>
@endsection

@section('content')
    <div class="content">
        <div class="row" id="app">
            <div class="col-lg-6 px-lg-1" style="height: 100vh;">
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
                                <div class='circle-custom grey'></div>&nbsp;Istirahat&nbsp;
                            </div>
                        </div>
                        <hr/>
                        <div class="row">
                            <div class="col-md-3 mb-2" v-for="(item, index) in partners">
                                <div :class="item.class" :id="item.element_id">@{{ item.number }}<br/>@{{ item.name }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="partner-menu">
            <div class="item justify-content-start" v-for="(item, index) in partnerMenus" v-on:click="partnerMenuClicked(item)"><span>@{{ item }}</span></div>
        </div>
    </div>
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ asset('css/monitor_data.css') }}"/>
@endsection
@section('scripts')
    <!-- Page JS Plugins -->
    <script src="{{ asset('js/plugins/chart.js/Chart.min.js') }}"></script>
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <!-- Page JS Code -->
    <script src="{{ asset('js/pages/be_pages_dashboard.min.js') }}"></script>
    <script src="{{ asset('js/plugins/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
    {{-- <script src="{{ asset('js/vue.js') }}"></script> --}}
    <script src="{{ asset('js/vue.js') }}"></script>
    <script src="{{ asset('js/axios.js') }}"></script>
    <script>
        let tmpPartners = {!! $jsonPartners !!};

        let app = new Vue({
            el: '#app',
            data: {
                activePartner: null,
                partners: tmpPartners,
                partnerMenus: [
                    'Hadir',
                    'Tidak Hadir/Pulang',
                    'Therapis Siap',
                ],
                activePartnerMenu: '',
            },
            methods: {
                async setPartnerToActive(){
                    await axios({
                        method: 'post',
                        url: '{{ route('admin.monitor.set_partner_active') }}',
                        data: {
                            partner_id: app.activePartner.id,
                            status_id: 1,
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
                            console.log(response);
                            let index = app.partners.indexOf(app.activePartner);
                            app.partners[index].class = 'custom-circle grey';

                            One.helpers('jq-notify', {type: 'info', icon: 'fa fa-info-circle me-1', message: 'Sukses membuat therapis Hadir ' + app.partners[index].number + '!'});
                        }
                        else{
                            One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Maaf terjadi kesalahan!'});
                        }
                    });
                },
                async setPartnerToNotActive(){
                    await axios({
                        method: 'post',
                        url: '{{ route('admin.monitor.set_partner_active') }}',
                        data: {
                            partner_id: app.activePartner.id,
                            status_id: 1,
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
                            console.log(response);
                            let index = app.partners.indexOf(app.activePartner);
                            app.partners[index].class = 'custom-circle grey';

                            One.helpers('jq-notify', {type: 'info', icon: 'fa fa-info-circle me-1', message: 'Sukses membuat therapis tidak hadir ' + app.partners[index].number + '!'});
                        }
                        else{
                            One.helpers('jq-notify', {type: 'warning', icon: 'fa fa-info-circle me-1', message: 'Maaf terjadi kesalahan!'});
                        }
                    });
                },
                async partnerMenuClicked(item){
                    partnerMenu.classList.remove("visible");
                    app.activePartnerMenu = item;

                    if(item === 'Hadir'){
                        await app.setPartnerToActive();
                    }
                    else if(item === 'Tidak Hadir/Pulang'){
                        await app.setPartnerToNotActive();
                    }
                    else if(item === 'Therapis Siap'){
                        await app.setPartnerToRest();
                    }
                },
            }
        });
    </script>

    <script>
        const partnerMenu = document.getElementById("partner-menu");
        const scope = document.querySelector("body");

        scope.addEventListener("contextmenu", (event) => {
            event.preventDefault();
            let tmp = event.path[0].getAttribute('class');
            let id = event.path[0].getAttribute('id');

            if(tmp.includes('custom-circle')){
                const { clientX: mouseX, clientY: mouseY } = event;

                partnerMenu.style.top = `${mouseY}px`;
                partnerMenu.style.left = `${mouseX}px`;

                partnerMenu.classList.remove("visible");

                setTimeout(() => {
                    partnerMenu.classList.add("visible");
                });

                let tmpPartner = app.partners.filter(n=> { return n.element_id === id});
                let index = app.partners.indexOf(tmpPartner[0]);
                app.activePartner = app.partners[index];
            }
        });

        scope.addEventListener("click", (e) => {
            if (e.target.offsetParent !== partnerMenu) {
                partnerMenu.classList.remove("visible");
            }
        });
    </script>
@endsection
