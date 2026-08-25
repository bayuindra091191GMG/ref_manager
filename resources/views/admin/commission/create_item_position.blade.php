@extends('layouts.admin')

@section('title')
    <title>Tambah Pengaturan Komisi</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Tambah Pengaturan Komisi
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.commission.position.item.index', ['branch_id' => $activeBranchId]) }}">Daftar Komisi</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Tambah Pengaturan Komisi
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <div class="content">
        <form class="space-y-3" id="form_create_item_commission'" method="POST" action="{{ route('admin.commission.position.item.store') }}">
            @csrf
            <div class="row">
                <div class="col-12 text-end">
                    <button id="btn_loading" type="button" class="btn btn-success" style="display: none;">
                        <i class="spinner-border spinner-border-sm text-green" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </i>
                    </button>
                    <input type="submit" id="btn_submit" class="btn btn-success" value="Simpan"/>&nbsp;&nbsp;
                    <a id="btn_cancel" href="{{ route('admin.commission.position.item.index', ['branch_id' => $activeBranchId]) }}" class="btn btn-danger">Batal</a>
                </div>
            </div>
            <div class="block block-rounded">
                <div class="block-content block-content-full">
                    <div class="row">
                        <div class="col-sm-6">
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
                    <div class="row">
                        <div class="col-12">
                            <div class="space-y-3">
                                <div class="row">
                                    <div class="col-12">
                                        <span style="color: red;">Isian dengan asterik (*) wajib diisi!</span>
                                    </div>
                                </div>
{{--                                <div class="row">--}}
{{--                                    <label class="col-sm-3 col-form-label" for="branch">Cabang <span class="text-red">*</span></label>--}}
{{--                                    <div class="col-sm-3">--}}
{{--                                        @if($isSuperAdmin)--}}
{{--                                            <select id="branch" name="branch" class="form-select">--}}
{{--                                                <option id="0" value="" @if($activeBranchId === 0) selected @endif>----- Pilih Cabang ------</option>--}}
{{--                                                @foreach($branches as $branch)--}}
{{--                                                    <option value="{{ $branch->id }}" @if($activeBranchId === $branch->id) selected @endif>{{ $branch->name }}</option>--}}
{{--                                                @endforeach--}}
{{--                                            </select>--}}
{{--                                        @else--}}
                                            <input type="hidden" name="branch" value="{{ $activeBranchId }}">
{{--                                            <input type="text" id="branch" class="form-control" value="{{ $activeBranchName }}" readonly/>--}}
{{--                                        @endif--}}
{{--                                    </div>--}}
{{--                                    <div class="col-sm-4">--}}
{{--                                        <button type="button" class="btn btn-primary" onclick="updateBranch();">Pilih Cabang</button>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
                                @if($activeBranchId > 0)
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="position">Jabatan <span class="text-red">*</span></label>
                                        <div class="col-sm-3">
                                            <select class="form-control" id="position" name="position">
                                                <option value="0">----- Pilih Jabatan -----</option>
                                                @if(!empty($positions))
                                                    @foreach($positions as $position)
                                                        <option value="{{ $position->id }}">{{ $position->name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @if($activeBranchId > 0)
                <div class="block block-rounded" id="vue-section">
                    <div class="block-content block-content-full">
                        <div class="row">
                            <div class="col-12">
                                <table class="table table-bordered nowrap">
                                    <thead>
                                    <tr>
                                        <th class="text-center">No</th>
                                        <th class="text-center">Barang</th>
                                        <th class="text-center">Komisi Persentase</th>
                                        <th class="text-center">Komisi Flat</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="(item, index) in commissions">
                                        <td class="text-center">
                                            @{{ index + 1 }}
                                        </td>
                                        <td>
                                            <select2 url="{{ route('admin.item.select') }}" v-model="item.item_id"
                                                     :name="'commissions[' + index + '][item_id]'" class="form-select form-control-select2" placeholder="--- Pilih Barang ---">
                                            </select2>
                                        </td>
                                        <td>
                                            <div class="input-group">
                                                <vue-autonumeric :options="autonumericFormatPercent"
                                                                 class="form-control text-end"
                                                                 :name="'commissions[' + index + '][commission_percentage]'"
                                                                 v-model="item.commission_percentage"></vue-autonumeric>
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <vue-autonumeric :options="autonumericFormat"
                                                                 class="form-control text-end"
                                                                 :name="'commissions[' + index + '][commission_flat]'"
                                                                 v-model="item.commission_flat"></vue-autonumeric>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-danger" v-on:click="removeCommission(item)"><i class="fa fa-trash"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-center">
                                            <button type="button" class="btn btn-success" v-on:click="addMoreCommission"><i class="fa fa-plus"></i>&nbsp;Tambah</button>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        </form>
    </div>
@endsection

@section('styles')
    <!-- Page JS Plugins CSS -->
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
@endsection

@section('scripts')
    <!-- jQuery -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('js/plugins/bootstrap-notify/bootstrap-notify.min.js') }}"></script>

    <!-- Vue requirements -->
    <script src="{{ asset('js/vue.js') }}"></script>
    <script src="{{ asset('js/axios.js') }}"></script>
    <script src="{{ asset('js/AutoNumeric.js') }}"></script>
    <script src="{{ asset('js/vue-autonumeric.js') }}"></script>

    <script type="text/x-template" id="select2-template">
        <select>
            <slot></slot>
        </select>
    </script>

    <script src="{{ asset('js/vueComponent-select2.js') }}"></script>

    <script>
        $('#form_create_item_commission').submit(function(){
            $('#btn_submit').hide();
            $('#btn_cancel').hide();
            $('#btn_loading').show(200);
        });

        $('#position').select2();

        // function updateBranch(){
        //     let branchId = $('#branch').val();
        //     if(branchId == 0){
        //         return false;
        //     }

        {{--    window.location.href = '{{ route('admin.commission.position.item.create') }}?branch_id=' + branchId;--}}
        {{--}--}}

        // $('#image').fileinput({
        //     'showUpload': false,
        //     'overwriteInitial': false,
        // });
    </script>

    <script>
        let vueComponent = new Vue({
            el: '#vue-section',
            data: {
                autonumericFormatPercent: {
                    minimumValue: '0',
                    maximumValue: '100',
                    digitGroupSeparator: '.',
                    decimalCharacter: ',',
                    decimalPlaces: 2,
                    modifyValueOnWheel: false,
                    allowDecimalPadding: false,
                },
                autonumericFormat: {
                    minimumValue: '0',
                    maximumValue: '999999999999999',
                    digitGroupSeparator: '.',
                    decimalCharacter: ',',
                    decimalPlaces: 0,
                    modifyValueOnWheel: false
                },
                commissions: [
                    {
                        item_id: null,
                        commission_percentage: 0,
                        commission_flat: 0
                    }
                ],
                branch_id: {{ $activeBranchId }}
            },
            methods: {
                removeCommission(item){
                    this.commissions = this.commissions.filter(function (x) { return x !== item; });
                },
                addMoreCommission(){
                    this.commissions.push({
                        item_id: null,
                        commission_percentage: 0,
                        commission_flat: 0
                    })
                }
            },
            mounted: function() {
                // flatpickr('.js-flatpickr', {
                //     "locale": "id" // locale for this instance only
                // });
            },
        });

    </script>
@endsection
