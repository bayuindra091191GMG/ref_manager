@extends('layouts.admin')

@section('title')
    <title>BACKEND - Monitor</title>
@endsection

@section('content')
    <div class="content" id="app">
        <div class="row">
            <div class="col-lg-12 px-lg-1" style="height: 100vh;">
                <div class="row">
                    <div class="col-12">
                        <h2>Kitchen</h2>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 mb-2" v-if="items.length == 0">
                        <h4>Belum ada item untuk Kitchen</h4>
                    </div>
                    <div class="block block-rounded col-md-3 mb-2"
                         v-for="(item, index) in items" :id="item.item_id">
                        <h3 class="block-title" v-if="item.room_number !== 'Belum pilih Room!'">Room @{{ item.room_number }}</h3>
                        <h3 class="block-title" v-else>@{{ item.room_number }}</h3>
                        <span v-if="item.status === 'Not Done'" style="color: red;"><b>@{{ item.status }}</b></span>
                        <span v-else style="color: green;"><b>@{{ item.status }}</b></span>
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <td><b>Makanan</b></td>
                                <td><b>Qty</b></td>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(foods, index) in item.details" :id="item.item_id">
                                <td>@{{ foods.item_name }}</td>
                                <td>@{{ foods.qty }}</td>
                            </tr>
                            </tbody>
                        </table>
                        <button :id="item.btn_id" type="button" class="btn w-100 btn-sm btn-alt-secondary mt-2 mb-2" v-on:click="submitDone(item)">Finish</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/custom_loading.css') }}"/>
@endsection

@section('scripts')
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script src="{{ asset('js/plugins/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
    <script src="{{ asset('js/vue.js') }}"></script>
    <script src="{{ asset('js/axios.js') }}"></script>
    <script>
        let app = new Vue({
            el: '#app',
            data: {
                isLoading: false,
                items: [],
            },
            methods: {
                async loadData(){
                    this.isLoading = true;
                    await axios({
                        method: 'get',
                        url: '{{ route('admin.kitchen.load_data') }}',
                    }).then(function (response) {
                        if(response){
                            if(response.status === 200){
                                app.items = response.data;
                            }
                            else{
                                console.log(response);
                            }
                        }
                    });
                    this.isLoading = false;
                },
                async refreshData(){
                    this.isLoading = true;
                    await axios({
                        method: 'get',
                        url: '{{ route('admin.kitchen.load_data') }}',
                    }).then(function (response) {
                        if(response){
                            if(response.status === 200){
                                app.items = response.data;
                            }
                            else{
                                console.log(response);
                            }
                        }
                    });
                    this.isLoading = false;
                },
                async submitDone(item){
                    let idx = app.items.indexOf(item);
                    app.items[idx].is_loading = true;
                    $('#' + app.items[idx].btn_id).prop('disabled', true);

                    await axios({
                        method: 'post',
                        url: '{{ route('admin.kitchen.finish') }}',
                        data: {
                            trx_id: item.id
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
                        app.items[idx].is_loading = false;
                        $('#' + app.items[idx].btn_id).prop('disabled', false);
                    }).then(function (response) {
                        One.helpers('jq-notify', {type: 'info', icon: 'fa fa-info-circle me-1', message: 'Sukses mengubah status!'});
                        app.items[idx].is_loading = false;
                        app.hideItem(app.items[idx]);
                    });
                },
                hideItem(item){
                    let idx = app.items.indexOf(item);
                    app.items[idx].status = 'Done';
                    setTimeout(app.fadeOutItem, 3000, item.item_id, idx);
                },
                fadeOutItem(itemId, idx){
                    fadeOut(itemId);
                    setTimeout(app.changeStatus, 1000, idx);
                },
                changeStatus(idx){
                    app.items[idx].display = false;
                    app.items.splice(app.items[idx], 1);
                },
            },
            created(){
                setInterval(async () => {
                    await this.refreshData();
                }, 5000);
            },
        });
        app.loadData();
    </script>
    <script>
        function fadeOut(item){
            $('#' + item).addClass('animated fadeOut');
        }
    </script>
@endsection
