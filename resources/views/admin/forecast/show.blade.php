@extends('layouts.admin')

@section('title')
    <title>Forecast</title>
@endsection

@section('content')
    <!-- Hero -->
{{--    <div class="bg-body-light">--}}
{{--        <div class="content content-full">--}}
{{--            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">--}}
{{--                <div class="flex-grow-1">--}}
{{--                    <h1 class="h3 fw-bold mb-2">--}}
{{--                        Detil Terapis {{ $partner->alias }}--}}
{{--                    </h1>--}}
{{--                </div>--}}
{{--                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">--}}
{{--                    <ol class="breadcrumb breadcrumb-alt">--}}
{{--                        <li class="breadcrumb-item">--}}
{{--                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>--}}
{{--                        </li>--}}
{{--                        <li class="breadcrumb-item">--}}
{{--                            <a class="link-fx" href="{{ route('admin.partner.index') }}">Daftar Terapis</a>--}}
{{--                        </li>--}}
{{--                        <li class="breadcrumb-item" aria-current="page">--}}
{{--                            Detil Terapis--}}
{{--                        </li>--}}
{{--                    </ol>--}}
{{--                </nav>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
    <!-- END Hero -->

    <div id="vue-section" class="content">
        <form>
            <div class="block block-rounded">
                <div class="block-content block-content-full">
    {{--                    <div class="row">--}}
    {{--                        <div class="col-12">--}}
    {{--                            <h5>FORECAST</h5>--}}
    {{--                        </div>--}}
    {{--                    </div>--}}
                    <div class="row">
                        <div class="col-sm-6 col-12">
                            <table class="table table-bordered table-striped table-vcenter nowrap">
                                <thead>
                                <tr>
                                    <th>Treatment</th>
                                    <th class="text-center">Room</th>
                                    <th class="text-center">Therapis</th>
                                    <th class="text-center">Jam</th>
                                    <th class="text-center">Menit Tersisa</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="(forecast,index) in forecasts1">
                                    <td>@{{ forecast.package_name }}</td>
                                    <td class="text-center">@{{ forecast.room_number }}</td>
                                    <td class="text-center">@{{ forecast.partner_numbers }}</td>
                                    <td class="text-center">@{{ forecast.start_time }} - @{{ forecast.end_time }}</td>
                                    <td class="text-center" v-if="forecast.remaining_minutes === 'END'">
                                        <span style="color: red" class="fw-bold">WAKTU HABIS</span>
                                    </td>
                                    <td class="text-center" v-else >@{{ forecast.remaining_minutes }}m</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-sm-6 col-12">
                            <table class="table table-bordered table-striped table-vcenter nowrap">
                                <thead>
                                <tr>
                                    <th>Treatment</th>
                                    <th class="text-center">Room</th>
                                    <th class="text-center">Therapis</th>
                                    <th class="text-center">Jam</th>
                                    <th class="text-center">Menit Tersisa</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="(forecast,index) in forecasts2">
                                    <td>@{{ forecast.package_name }}</td>
                                    <td class="text-center">@{{ forecast.room_number }}</td>
                                    <td class="text-center">@{{ forecast.partner_numbers }}</td>
                                    <td class="text-center">@{{ forecast.start_time }} - @{{ forecast.end_time }}</td>
                                    <td class="text-center" v-if="forecast.remaining_minutes === 'END'">
                                        <span style="color: red" class="fw-bold">WAKTU HABIS</span>
                                    </td>
                                    <td class="text-center" v-else >@{{ forecast.remaining_minutes }}m</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('styles')
    <!-- Page JS Plugins CSS -->
    <style>
        .table>:not(caption)>*>*{
            padding: 0.40rem 0.40rem;
        }
    </style>
@endsection

@section('scripts')
    <!-- jQuery -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script src="{{ asset('js/plugins/bootstrap-notify/bootstrap-notify.min.js') }}"></script>

    <script src="{{ asset('js/vue.js') }}"></script>
    <script src="{{ asset('js/axios.js') }}"></script>
    <script src="{{ asset('js/AutoNumeric.js') }}"></script>
    <script src="{{ asset('js/vue-autonumeric.js') }}"></script>

    <script>
        let tmpForecasts = {!! $forecastCollects !!};

        let app = new Vue({
            el: '#vue-section',
            data: {
                forecasts: tmpForecasts
            },
            computed: {
                forecasts1(){
                    let result = [];
                    if(this.forecasts.length < 15){
                        for(let i = 0; i < (this.forecasts.length); i++){
                            result.push(this.forecasts[i]);
                        }
                    }
                    else{
                        for(let i = 0; i < 15; i++){
                            result.push(this.forecasts[i]);
                        }
                    }
                    // if(this.forecasts.length % 2 === 0){
                    //     for(let i = 0; i < (this.forecasts.length) / 2; i++){
                    //         result.push(this.forecasts[i]);
                    //     }
                    // }
                    // else{
                    //     for(let i = 0; i < (this.forecasts.length + 1) / 2; i++){
                    //         result.push(this.forecasts[i]);
                    //     }
                    // }

                    return result;
                },
                forecasts2(){
                    let result = [];
                    if(this.forecasts.length > 15){
                        for(let i = 15; i < this.forecasts.length; i++){
                            result.push(this.forecasts[i]);
                        }
                    }
                    // if(this.forecasts.length % 2 === 0){
                    //     for(let i = this.forecasts.length / 2; i < this.forecasts.length; i++){
                    //         result.push(this.forecasts[i]);
                    //     }
                    // }
                    // else{
                    //     for(let i = (this.forecasts.length + 1) / 2; i < this.forecasts.length; i++){
                    //         result.push(this.forecasts[i]);
                    //     }
                    // }

                    return result;
                },
            },
            methods: {
                async refreshData(){
                    await axios({
                        method: 'get',
                        url: '{{ route('admin.forecast.refresh') }}',
                    }).then(function (response) {
                        if(response){
                            if(response.status === 200){
                                app.forecasts = response.data;
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
                    await app.refreshData();
                }, 3000);
            }
        })
    </script>
@endsection
