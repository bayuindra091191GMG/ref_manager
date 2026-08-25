@extends('layouts.admin')

@section('title')
    <title>BACKEND - Monitor</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Scan Barcode Locker
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Scan Barcode Locker
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <div class="content" id="app">
        <div class="row items-push">
            <div class="col-md-12 col-xl-12">
                <div class="block block-rounded h-100 mb-0">
                    <div class="block-content block-content-full">
                        <div class="row">
                            <div class="col-12 alert alert-danger alert-dismissible" role="alert" v-if="message !== '' && isSuccess === false">
                                <p class="mb-0">
                                    @{{ message }}
                                </p>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <div class="col-12 alert alert-success alert-dismissible" role="alert" v-if="message !== '' && isSuccess === true">
                                <p class="mb-0">
                                    @{{ message }}
                                </p>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-3 col-form-label" for="lockerNumber">Masukan 3 digit nomor loker/Scan menggunakan Alat Scan</label>
                        </div>
                        <div class="row">
                            <label class="col-sm-3 col-form-label" for="lockerNumber">Nomor Locker *</label>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <input id="lockerNumber" type="text" class="form-control" v-model="lockerNumber" v-on:input="submit" placeholder="Nomor Loker" autofocus/>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-sm-6">
{{--                                        <button type="button" class="btn btn-primary" v-on:click="submit">Check</button>--}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <!-- Page JS Plugins CSS -->
@endsection

@section('scripts')
    <!-- jQuery -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('js/vue.js') }}"></script>
    <script src="{{ asset('js/axios.js') }}"></script>
    <script src="{{ asset('js/plugins/bootstrap-notify/bootstrap-notify.min.js') }}"></script>

    <script>
       let app = new Vue({
           el: '#app',
           data: {
               lockerNumber: '',
               message: '',
               isLoading: false,
               isSuccess: false,
           },
           methods: {
               async submit(){
                   if(app.lockerNumber.length > 2){
                       app.isLoading = true;
                       await axios({
                           method: 'post',
                           url: '{{ route('admin.monitor_locker.barcode_scan') }}',
                           data: {
                               locker_number: app.lockerNumber
                           }
                       }).catch(function (error){
                           if(error.response){
                               app.message = error.response.data;
                           }
                           app.lockerNumber = '';
                           app.isSuccess = false;
                       }).then(function (response) {
                           if(response.status === 200){
                               app.message = 'Sukses Scan Locker '+ app.lockerNumber +'!';
                               app.isSuccess = true;
                               app.lockerNumber = '';
                               One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', message: 'Sukses Scan Locker!'});
                           }
                           else{
                               app.message = 'Maaf Terjadi Kesalahan!';
                               One.helpers('jq-notify', {type: 'error', icon: 'fa fa-info-circle me-1', message: 'Maaf terjadi kesalahan!'});
                           }
                       });
                       app.isLoading = false;
                   }
               }
           },
       });
    </script>
@endsection
