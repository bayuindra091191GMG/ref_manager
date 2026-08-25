@extends('layouts.admin')

@section('title')
    <title>Transaksi</title>
@endsection

@section('content')
    <div class="content" id="app">
        <div class="block block-rounded mb-0">
            <div class="block-header block-header-default">
                <h3 class="block-title">Transaksi</h3>
            </div>
            <div class="block-content fs-sm" v-if="isShowing === false">
                <div class="row">
                    <h2>Terima Kasih sudah bertransaksi dengan kami!</h2>
                </div>
            </div>
            <div class="block-content fs-sm" v-else-if="isShowing && data != null">
                <div class="row">
                    <div class="col-2">
                        <span>Nomor Locker</span>
                    </div>
                    <div class="col-10">
                        <span>@{{ data.locker.number }}</span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-2">
                        <span>GRO</span>
                    </div>
                    <div class="col-10">
                        <span>@{{ data.gro.number }}</span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-2">
                        <span>Therapis</span>
                    </div>
                    <div class="col-10">
                        <span>@{{ data.partners }}</span>
                    </div>
                </div>
                <div class="row" v-if="data.header !== null && data.header.is_happy_hour === 1">
                    <div class="col-6">
                        <h4 style="color: red">Happy Hour!</h4>
                    </div>
                </div>
                <div class="table-responsive w-100 mt-1">
                    <table class="table table-bordered table-striped table-vcenter js-dataTable-full" id="table_data_transaction">
                        <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Item</th>
                            <th>Qty</th>
                            <th>Room</th>
                            <th>Time</th>
                            <th>Harga</th>
                            <th>Subtotal</th>
                            <th>Diskon</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(item, index) in data.details">
                            <td>@{{ index+1 }}</td>
                            <td>
                                <span v-if="item.type === 'package'">@{{ item.package_name }}</span>
                                <span v-if="item.type === 'item'">@{{ item.item_name }}</span>
                                <span v-if="item.is_facility_only">Hanya Fasilitas</span>
                            </td>
                            <td>
                                <input type="number" class="form-control text-end" v-model="item.qty" readonly/>
                            </td>
                            <td>
                                @{{ item.room_number }}
                            </td>
                            <td>
                                @{{ item.time_display }}
                            </td>
                            <td class="text-end">@{{ item.price_str }}</td>
                            <td><input type="text" class="form-control text-end" v-model="item.subtotal_str" readonly/></td>
                            <td><input type="text" class="form-control text-end" v-model="item.discount_str" readonly/></td>
                        </tr>
                        <tr>
                            <td colspan="5" class="text-end"><b>Total</b></td>
                            <td class="text-end"><b>@{{ data.grandTotal }}</b></td>
                            <td></td>
                            <td></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Page JS Plugins -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    {{--    <script src="{{ asset('js/plugins/chart.js/Chart.min.js') }}"></script>--}}
    <!-- Page JS Code -->
    {{--    <script src="{{ asset('js/pages/be_pages_dashboard.min.js') }}"></script>--}}
    <script src="{{ asset('js/plugins/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
    <script src="{{ asset('js/vue.js') }}"></script>
    <script src="{{ asset('js/axios.js') }}"></script>
    <script src="{{ asset('js/AutoNumeric.js') }}"></script>
    <script src="{{ asset('js/vue-autonumeric.js') }}"></script>
    <script>
        let app = new Vue({
            el: '#app',
            data: {
                data: null,
                isShowing: false,
            },
            methods: {
                async getTransaction(){
                    await axios({
                        method: 'get',
                        url: '{{ route('admin.pos.get_trx_preview') }}',
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
                            app.data = response.data;
                            app.isShowing = true;
                        }
                        else if(response.status === 202){
                            app.isShowing = false;
                        }
                    });
                }
            },
            created(){
                setInterval(async () => {
                    await app.getTransaction();
                }, 1000);
            }
        });
    </script>
@endsection
