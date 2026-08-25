@extends('layouts.admin')

@section('title')
    <title>Daftar Ijin Akses User</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Daftar Ijin Akses User
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Daftar Ijin Akses User
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <div class="content" id="vue-section">
        <div class="block block-rounded">
            <div class="block-content">
                <form method="post" action="{{ route('admin.access_user.update') }}">
                    @csrf

                    <div>
                        <template v-for="item in menus">
                            <input type="hidden" name="permission[]" v-for="action in item.action"  :value="action.code" v-if="action.checked">
                        </template>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            @include('partials._success')
                            @include('partials._error')
                        </div>
                    </div>
                    @if(count($errors))
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <ul>
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="row mb-3">
                        <label class="col-sm-1 col-form-label" for="name">User</label>
                        <div class="col-sm-6">
                            <select2 url="{{route('admin.admin_user.select')}}" v-model="userId" :value="userId" class="form-select part" name="user_id" placeholder="Pilih User" required>
                            </select2>
                        </div>
                    </div>

                    <p class="p-5 text-center" v-if="!loading && userId == null">Pilih User terlebih dahulu!</p>
                    <h1 class="p-5 text-center" v-if="loading"><i class="fa fa-fw fa-spin fa-circle-notch"></i></h1>

                    <div v-if="!loading && userId != null">
                        <div class="d-flex flex-row justify-content-between align-items-center mb-2">
                            <button type="submit" class="btn btn-success"><i class="far fa-save me-2"></i> Simpan</button>
                            <div class="d-flex flex-row">
                                <button type="button" class="btn btn-secondary" @click="checkAll"><i class="fa fa-fw fa-check-double"></i> Check Semua</button>
                                <button type="button" class="btn btn-danger ms-2" @click="uncheckAll"><i class="fa fa-fw fa-ban"></i> Uncheck Semua</button>
                                <button type="button" class="btn btn-info ms-2" @click="reset"><i class="fa fa-fw fa-refresh"></i> Reset Sesuai Jabatan</button>
                            </div>
                        </div>
                        <ul class="nav nav-tabs">
                            <li class="nav-item">
                                <span class="nav-link" :class="{'active': tab == 'pos'}" @click="tab = 'pos'">POS</span>
                            </li>
                            <li class="nav-item">
                                <span class="nav-link" :class="{'active': tab == 'master'}" @click="tab = 'master'">Master Data</span>
                            </li>
                            <li class="nav-item">
                                <span class="nav-link" :class="{'active': tab == 'barang'}" @click="tab = 'barang'">Barang</span>
                            </li>
                            <li class="nav-item">
                                <span class="nav-link" :class="{'active': tab == 'attendance'}" @click="tab = 'attendance'">Absensi</span>
                            </li>
                            <li class="nav-item">
                                <span class="nav-link" :class="{'active': tab == 'purchase'}" @click="tab = 'purchase'">Purchasing</span>
                            </li>
                            <li class="nav-item">
                                <span class="nav-link" :class="{'active': tab == 'finance'}" @click="tab = 'finance'">Finance</span>
                            </li>
                            <li class="nav-item">
                                <span class="nav-link" :class="{'active': tab == 'general_ledger'}" @click="tab = 'general_ledger'">General Ledger</span>
                            </li>
                            <li class="nav-item">
                                <span class="nav-link" :class="{'active': tab == 'report'}" @click="tab = 'report'">Laporan</span>
                            </li>
                        </ul>
                        <div class="p-3 mb-3 border-bottom border-start border-end rounded" style="margin-top: -2px">
                            <div class="row">
                                <div class="col-lg-3 col-md-4 col-12 h-auto mb-2" v-for="(menu, index) in menus" :key="menu.id" v-if="menu.category == tab">
                                    <div class="border rounded p-2 h-100">
                                        <div class="mb-3 d-flex flex-row justify-content-between align-items-center">
                                            <h6 class="fw-bold">@{{ menu.name }}</h6>
                                            <button type="button" class="btn btn-secondary" @click="checkPerMenu(index)"><i class="fa fa-check-double"></i></button>
                                        </div>

                                        <div class="row">
                                            <div class="col-6" v-for="action in menu.action">
                                                <div class="form-check">
                                                    <input class="form-check-input" name="permission[]" type="checkbox" :value="action.code" v-model="action.checked">
                                                    <label class="form-check-label" for="flexCheckDefault">@{{ action.name }}</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>


@endsection

@section('styles')
    <!-- Page JS Plugins CSS -->
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
    <style>
        .nav-link {
            cursor: pointer;
        }
    </style>
@endsection

@section('scripts')
    <!-- jQuery (required for DataTables plugin) -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('js/plugins/bootstrap-notify/bootstrap-notify.min.js') }}"></script>


    <!-- Page JS Plugins -->
    <script src="{{ asset('js/axios.js') }}"></script>
    <script src="{{ asset('js/vue.js') }}"></script>

    <script type="text/x-template" id="select2-template">
        <select>
            <slot></slot>
        </select>
    </script>
    <script src="{{ asset('js/vueComponent-select2.js') }}"></script>

    <script>
        let vueComponent = new Vue({
            el: "#vue-section",
            data: {
                tab: 'pos',
                loading: false,
                userId: null,
                menus: [],
                positionMenus: [],
            },
            methods: {
                checkAll(){
                    for(var i = 0; i < this.menus.length; i++){
                        for(var j = 0; j < this.menus[i].action.length; j++){
                            this.menus[i].action[j].checked = true;
                        }
                    }
                },
                uncheckAll(){
                    for(var i = 0; i < this.menus.length; i++){
                        for(var j = 0; j < this.menus[i].action.length; j++){
                            this.menus[i].action[j].checked = false;
                        }
                    }
                },

                checkPerMenu(index){
                    var check = false;
                    for(var i = 0; i < this.menus[index].action.length; i++){
                        if(!this.menus[index].action[i].checked){
                            check = true;
                            break;
                        }
                    }

                    for(var i = 0; i < this.menus[index].action.length; i++){
                        this.menus[index].action[i].checked = check;
                    }
                },

                reset(){
                    this.menus = this.positionMenus.map(a => ({...a}));
                },
            },
            watch: {
                userId(){
                    if(this.userId != ''){
                        this.loading = true;

                        let app = this;
                        axios({
                            method: 'get',
                            url: '{!! route('admin.access_user.permissions') !!}',
                            params: {
                                id: app.userId
                            },
                        }).then(function (response){
                            if(response.data.success){
                                app.menus = [];
                                setTimeout(function (){
                                    app.menus = response.data.menus;
                                    app.positionMenus = response.data.position_menus;
                                }, 10);
                            }
                            else {
                                app.userId = null;
                                One.helpers('jq-notify', {type: 'danger', icon: 'fa fa-info-circle me-1', timer: 2000, message: 'Internal server error!'});
                            }
                            app.loading = false;
                        });
                    }
                }
            },
        });
    </script>
@endsection
