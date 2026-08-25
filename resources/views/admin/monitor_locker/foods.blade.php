@extends('layouts.admin')

@section('title')
    <title>Monitor Locker Foods</title>
@endsection

@section('content')
    <div class="content" id="app">
        <div class="block block-rounded">
            <div class="block-content">
                <div class="row justify-content-center">
                    <div class="col-lg-12 px-lg-1">
                        <div class="block block-rounded block-link-pop">
                            <div class="block-content block-content-full">
                                <div class="row">
                                    @if(\Illuminate\Support\Facades\Session::has('error'))
                                        <div class="col-12 alert alert-danger alert-dismissible" role="alert">
                                            <p class="mb-0">
                                                {{ \Illuminate\Support\Facades\Session::get('error') }}
                                            </p>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    @endif
                                </div>
                                <div class="row">
                                    <div class="col-4">
                                        <h4>Locker - Kitchen</h4>
                                    </div>
                                    <div class="col-8 d-flex justify-content-end">
                                        <div class='box green'></div>&nbsp;Available&nbsp;
                                        <div class='box red'></div>&nbsp;Sedang Digunakan
                                    </div>
                                </div>
                                <hr/>
                                <div class="row row-cols-auto">
                                    <div class="col mb-2" v-for="(item, index) in lockers">
                                        <div :class="item.element_class" :id="item.element_id">
                                            @{{ item.number }}
                                            <br/>
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
        <div id="context-menu">
            <div class="item" v-for="(item, index) in menus" v-on:click="menuClicked(item)">
                <span>@{{ item.name }}</span>
            </div>
        </div>
        <!-- END Pop In Treatment Modal -->
        @include('partials.monitor_locker._modal-foods')
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
                fcLoading: false,
                lockers: tmpLockers,
                foods: [],
                items: [],
                itemDisplays: [],
                headerId: 0,
                personNumber: '',
                itemPagination: {
                    skip: 0,
                    take: 10,
                    search: '',
                    count: 0,
                    pages: [],
                },
                menus: [
                    {
                        name: 'Pesan Makanan',
                        route: '',
                    },
                    {
                        name: 'Detil',
                        route: '',
                    },
                    {
                        name: 'Print Receipt',
                        route: '',
                    },
                ],
                activeLocker: null,
                activeMenu: '',
                selectedLocker: null,
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
                extraItems: function(){
                    return this.details.filter(function (x) { return x.type === 'item'; });
                },
            },
            methods: {
                async menuClicked(item){
                    contextMenu.classList.remove("visible");
                    app.activeMenu = item.name;

                    app.selectedLocker = item;
                    if(item.name === 'Pesan Makanan'){
                        await app.getLockerFoodDetail();
                        await app.showItemModalQty();
                    }
                    else if(item.name === 'Detil'){
                        await app.showDetailLocker();
                    }
                    else if(item.name === 'Print Receipt'){
                        app.printReceipt();
                    }
                },
                async showDetailLocker(){
                    await this.getLockerFoodDetail();
                    modalDetil.show();
                },
                printReceipt(){
                    let url = '{{ route('admin.kitchen.receipt') }}?locker_id=' + app.activeLocker.id;
                    console.log(url);
                    window.open(url);
                },
                async getLockerFoodDetail(){
                    app.fcLoading = true;
                    await axios({
                        method: 'post',
                        url: '{{ route('admin.kitchen.details') }}',
                        data: {
                            locker_id: app.activeLocker.id,
                        }
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
                        app.fcLoading = false;
                    }).then(function (response) {
                        console.log(response);
                        if(response.status === 200){
                            app.foods = response.data.foods;
                            app.headerId = response.data.header_id;
                        }
                    });

                    app.fcLoading = false;
                },

                //Item modals
                generateItemDisplay(){
                    let result = [];
                    let long = parseInt(app.itemPagination.skip) + parseInt(app.itemPagination.take);
                    console.log(long);
                    for(let i=app.itemPagination.skip; i<long; i++){
                        result.push(app.items[i]);
                    }

                    app.itemDisplays = result;
                },
                searchItem(){
                    let result = [];
                    let endResult = [];
                    for(let i=0; i<app.items.length; i++){
                        if(app.items[i].name.toLowerCase() === app.itemPagination.search.toLowerCase()
                            || app.items[i].name.toLowerCase().includes(app.itemPagination.search.toLowerCase())){
                            result.push(app.items[i]);
                        }
                    }

                    app.itemPagination.skip = 0;
                    app.itemPagination.take = 10;

                    let long = parseInt(app.itemPagination.skip) + parseInt(app.itemPagination.take);
                    if(long > result.length){
                        long = result.length;
                    }
                    for(let i=app.itemPagination.skip; i<long; i++){
                        endResult.push(result[i]);
                    }

                    app.itemDisplays = endResult;
                    //return result;
                },
                async getAllItems(){
                    await axios({
                        method: 'get',
                        url: '{{ route('admin.pos.get_all_items') }}',
                    }).then(function (response) {
                        if(response.status === 200){
                            app.items = response.data;
                            app.generateItemDisplay();
                        }
                    });
                },
                async getItems(){
                    await axios({
                        method: 'get',
                        url: '{{ route('admin.pos.get_items') }}?skip=' + app.itemPagination.skip + '&take=' + app.itemPagination.take + '&search=' + app.itemPagination.search + '&is_fnb=1',
                    }).then(function (response) {
                        if(response.status === 200){
                            app.items = response.data.items;
                            app.itemPagination.count = response.data.itemCount;
                            app.itemPagination.pages = response.data.pages;
                        }
                    });
                },
                async nextItemPage(){
                    if((app.itemPagination.skip + 10) < app.items.length){
                        app.itemPagination.skip += 10;
                        //await app.getItems();
                        app.generateItemDisplay();
                    }
                },
                async previousItemPage(){
                    if(app.itemPagination.skip >= 10){
                        app.itemPagination.skip -= 10;
                        //await app.getItems();
                        app.generateItemDisplay();
                    }
                },
                async selectItem(item){
                    let index = app.items.indexOf(item);
                    app.items[index].selected = !app.items[index].selected;
                },
                plusItemQty(item){
                    let index = app.items.indexOf(item);
                    if(app.items[index].selected){
                        app.items[index].qty++;
                    }
                    let idx = app.items.indexOf(item);
                    if(app.items[idx].is_unlimited_stock == 0){
                        if(app.items[idx].qty > app.items[idx].stock){
                            app.items[idx].qty = app.items[idx].stock;
                        }
                    }
                },
                minusItemQty(item){
                    let index = app.items.indexOf(item);
                    if(app.items[index].selected && app.items[index].qty > 0){
                        app.items[index].qty--;
                    }
                },
                async showItemModalQty(){
                    await app.getAllItems();
                    modalItemQty.show();
                    $('#food-search').focus();
                },
                async addItemsToTrxSubmit(){
                    let data = {
                        locker_id: app.activeLocker.id,
                        trx_id: app.headerId,
                        items: [],
                        person_number: app.personNumber,
                    };

                    let tmpItems = app.items.filter(n => n.selected === true);
                    //generate parameters
                    let canSubmit = true;
                    for(let i=0; i<tmpItems.length; i++){
                        let idx = app.items.indexOf(tmpItems[i]);
                        let nParam = {
                            id: app.items[idx].id,
                            qty: app.items[idx].qty
                        };

                        if(app.items[idx].qty <= 0){
                            canSubmit = false;
                        }

                        data.items.push(nParam);
                    }

                    if(canSubmit){
                        app.itemLoading = true;
                        await axios({
                            method: 'post',
                            url: '{{ route('admin.pos.add_items') }}',
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
                            app.itemPagination.search = '';
                            app.personNumber = '';
                        }).then(function (response) {
                            if(response.status === 200){
                                let no = 100;
                                for(let i=0; i<tmpItems.length; i++){
                                    let idx = app.items.indexOf(tmpItems[i]);
                                    let nDt = {
                                        id: no,
                                        type: 'item',
                                        package_name: '',
                                        item_id: app.items[idx].id,
                                        item_name: app.items[idx].name,
                                        qty: 1,
                                    };

                                    let tmp = app.foods.filter(n => n.item_name === nDt.item_name);
                                    if(tmp.length === 0){
                                        app.foods.push(nDt);
                                    }

                                    no++;
                                }
                                One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', message: 'Sukses menambahkan Makanan!'});
                            }
                            app.itemPagination.search = '';
                            app.personNumber = '';
                        });
                        app.itemLoading = false;
                        // //reset items status
                        // for(let i=0; i<app.items.length; i++){
                        //     app.items[i].selected = false;
                        // }
                        modalItemQty.hide();
                    }
                    else{
                        app.addItemMsg = 'Min Qty 1!';
                        app.itemPagination.search = '';
                    }
                    app.itemPagination.search = '';
                    app.personNumber = '';
                },
                //end item modals

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
        let modalItemQty = new bootstrap.Modal(document.getElementById('modal-item-qty'), {'backdrop': 'static', 'keyboard' :false });
        let modalDetil = new bootstrap.Modal(document.getElementById('modal-detil'), {'backdrop': 'static', 'keyboard' :false });

        const contextMenu = document.getElementById("context-menu");
        const scope = document.querySelector("body");

        scope.addEventListener("contextmenu", (event) => {
            event.preventDefault();
            // let tmp = event.path[0].getAttribute('class');
            // let id = event.path[0].getAttribute('id');
            let tmp = event.srcElement.className;
            let id = event.srcElement.id;
            if(id === null || id === ''){
                id = event.srcElement.parentNode.id;
                tmp = event.srcElement.parentNode.className;
            }
            let tmpLocker = app.lockers.filter(n=> { return n.element_id === id});
            let index = app.lockers.indexOf(tmpLocker[0]);
            app.activeLocker = app.lockers[index];

            if(tmp.includes('custom-square') && app.activeLocker.status_id === 6){
                const { clientX: mouseX, clientY: mouseY } = event;

                contextMenu.style.top = `${mouseY}px`;
                contextMenu.style.left = `${mouseX}px`;

                contextMenu.classList.remove("visible");

                setTimeout(() => {
                    contextMenu.classList.add("visible");
                });
            }
        });

        scope.addEventListener("click", (e) => {
            if (e.target.offsetParent !== contextMenu) {
                contextMenu.classList.remove("visible");
            }
        });
    </script>
@endsection
