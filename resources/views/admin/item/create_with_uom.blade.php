@extends('layouts.admin')

@section('title')
    <title>Buat Produk</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Buat Barang
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.item.index') }}">Daftar Barang</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Buat Barang
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->
    <div id="vue-section" class="content">
        <div class="row items-push">
            <div class="col-md-12 col-xl-12">
                <div class="block block-rounded h-100 mb-0">
                    <div class="block-content block-content-full">
                        <div class="row">
                            <div class="col-12">
                                <form class="space-y-3" id="form_create_item" method="POST" action="{{ route('admin.item.store') }}">
                                    @csrf
                                    <div class="row">
                                        <div class="col-12 text-end">
                                            <button id="btn_loading" type="button" class="btn btn-success" style="display: none;">
                                                <i class="spinner-border spinner-border-sm text-green" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </i>
                                            </button>
                                            <input type="submit" id="btn_submit" class="btn btn-success" value="Simpan"/>&nbsp;&nbsp;
                                            <a id="btn_cancel" href="{{ route('admin.item.index') }}" class="btn btn-danger">Batal</a>
                                        </div>
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
                                    <div class="row">
                                        <div class="col-12">
                                            <span style="color: red;">Isian dengan asterik (*) wajib diisi!</span>
                                        </div>
                                    </div>

                                    <ul class="nav nav-tabs nav-tabs-block" role="tablist">
                                        <li class="nav-item">
                                            <button type="button" class="nav-link active" id="btabs-animated-fade-general-tab" data-bs-toggle="tab"
                                                    data-bs-target="#btabs-animated-fade-general" role="tab" aria-controls="btabs-animated-fade-general" aria-selected="true">
                                                Data Umum
                                            </button>
                                        </li>
                                        <li class="nav-item">
                                            <button type="button" class="nav-link" id="tab_uoms" data-bs-toggle="tab"
                                                    data-bs-target="#tab_uom" role="tab" aria-controls="tab_uom" aria-selected="true">
                                                Definisi Unit of Material (UOM)
                                            </button>
                                        </li>
                                        <li class="nav-item">
                                            <button type="button" class="nav-link" id="btabs-animated-fade-featured-tab" data-bs-toggle="tab"
                                                    data-bs-target="#btabs-animated-fade-featured" role="tab" aria-controls="btabs-animated-fade-featured" aria-selected="true">
                                                Definisi Barang (BOM)
                                            </button>
                                        </li>
                                    </ul>
                                    <div class="block-content tab-content overflow-hidden">
                                        {{-- tab general data--}}
                                        <div class="tab-pane fade show active" id="btabs-animated-fade-general" role="tabpanel" aria-labelledby="btabs-animated-fade-general-tab">

                                            <div class="row mb-3">
                                                <label class="col-sm-3 col-12 col-form-label" for="name">Kategori <span class="text-red">*</span></label>
                                                <div class="col-sm-4 col-12">
                                                    <select id="category_id" name="category_id" class="form-select form-control-select2">
                                                        @foreach($itemCategories as $itemCategory)
                                                            <option value="{{$itemCategory->id}}">{{$itemCategory->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label class="col-sm-3 col-12 col-form-label" for="name">Nama <span class="text-red">*</span></label>
                                                <div class="col-sm-6 col-12">
                                                    <input type="text" id="name" name="name" class="form-control" placeholder="Nama" value="{{ old('name') }}"/>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label class="col-sm-3 col-12 col-form-label" for="name">Barcode</label>
                                                <div class="col-sm-6 col-12">
                                                    <input type="text" id="barcode" name="barcode" class="form-control" placeholder="Barcode Barang" value="{{ old('barcode') }}"/>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label class="col-sm-3 col-12 col-form-label" for="price">Harga Jual</label>
                                                <div class="col-sm-4 col-12">
                                                    <div class="input-group">
                                                        <span class="input-group-text">Rp</span>
                                                        <vue-autonumeric :options="autonumericFormat"
                                                                         name="price"
                                                                         class="form-control text-end"
                                                                         placeholder="Harga Jual"
                                                                         value="{{ old('price') }}">

                                                        </vue-autonumeric>
                                                    </div>

                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label class="col-sm-3 col-12 col-form-label" for="image">Unggah Gambar</label>
                                                <div class="col-sm-6 col-12">
                                                    <input type="file" id="image" name="image" />
                                                </div>
                                            </div>
                                        </div>
                                        {{-- tab general data--}}

                                        {{-- tab UOM --}}
                                        <div class="tab-pane fade show" id="tab_uom" role="tabpanel" aria-labelledby="tab_uoms">
                                            <h4 class="fw-normal">Definisi Unit of Material (UOM)</h4>
                                            <hr />
                                            <div class="row">
                                                <table class="table table-bordered table-striped table-vcenter" id="table_uoms">
                                                    <thead>
                                                    <tr>
                                                        <th class="text-center" style="width: 5%">
                                                            <a class="btn btn-success" style="cursor:pointer;" v-on:click="addMoreUomItem">
                                                                <i class="fa fa-plus"></i>
                                                            </a>
                                                        </th>
                                                        <th class="text-center" style="width: 5%">No</th>
                                                        <th class="text-center" style="width: 15%">Unit</th>
                                                        <th class="text-center" style="width: 8%">Qty</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <tr v-for="(uom, index) in uomList">
                                                        <td class="text-center">
                                                            <a class="btn btn-danger" style="cursor: pointer" v-on:click="removeUomItem(item)">
                                                                <i class="fa fa-minus"></i>
                                                            </a>
                                                        </td>
                                                        <td class="text-center"><span>@{{++index}}</span></td>
                                                        <td>
                                                            <select2 url="{{route('admin.uom.select')}}" v-model="uom.unitId"
                                                                     name="units[]" class="form-select form-control-select2" placeholder="Pilih Unit Material">
                                                            </select2>
                                                        </td>
                                                        <td>
                                                            <vue-autonumeric :options="autonumericFormat" name="uom_qty[]" v-model="uom.qty" class="form-control text-end"></vue-autonumeric>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>

                                        </div>
                                        {{-- tab UOM--}}

                                        {{-- tab featured menu--}}
                                        <div class="tab-pane fade show" id="btabs-animated-fade-featured" role="tabpanel" aria-labelledby="btabs-animated-fade-featured-tab">
                                            <h4 class="fw-normal">Definisi Barang (BOM)</h4>
                                            <hr />
                                            <div class="row">
                                                <table class="table table-bordered table-striped table-vcenter js-dataTable-full" id="table_data">
                                                    <thead>
                                                    <tr>
                                                        <th style="width: 5%">
                                                            <a class="btn btn-success" style="cursor:pointer;" v-on:click="addMoreItem">
                                                                <i class="fa fa-plus"></i>
                                                            </a>
                                                        </th>
                                                        <th style="width: 5%">No</th>
                                                        <th style="width: 15%">Nama Barang</th>
                                                        <th style="width: 8%">Qty</th>
                                                        <th style="width: 10%">Unit</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <tr v-for="(item, index) in itemList">
                                                        <td>
                                                            <a class="btn btn-danger" style="cursor: pointer" v-on:click="removeItem(item)">
                                                                <i class="fa fa-minus"></i>
                                                            </a>
                                                        </td>
                                                        <td><span>@{{++index}}</span></td>
                                                        <td>
                                                            <select2 url="{{route('admin.item.select')}}" v-model="item.itemId"
                                                                     name="bom_product[]" class="form-select form-control-select2" placeholder="Pilih Produk">
                                                            </select2>
                                                        </td>
                                                        <td>
                                                            <vue-autonumeric :options="autonumericFormat" name="bom_qty[]" v-model="item.qty" class="form-control"></vue-autonumeric>
                                                        </td>
                                                        <td>
                                                            <select2 url="{{route('admin.uom.select')}}" v-model="item.unitId"
                                                                     name="bom_unit[]" class="form-select form-control-select2" placeholder="Pilih Unit Material">
                                                            </select2>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>

                                        </div>
                                        {{-- tab featured menu--}}

                                    </div>
                                </form>
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
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/bootstrap-fileinput/css/fileinput.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('js/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}">
    <style>

        .select2-container--default .select2-selection--multiple .select2-selection__choice{
            color: #000;
        }
    </style>

@endsection

@section('scripts')
    <!-- jQuery -->
    <script type="text/javascript" src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/plugins/bootstrap-fileinput/js/fileinput.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>

    <script src="{{ asset('js/axios.js') }}"></script>
    <script src="{{ asset('js/vue.js') }}"></script>
    <script src="{{ asset('js/AutoNumeric.js') }}"></script>
    <script src="{{ asset('js/vue-autonumeric.js') }}"></script>


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
                can_submit: 1,
                qty_error: false,
                totalPrice: 0,

                itemList: [],
                uomList: [{
                    uniId: 0,
                    qty: 1
                }],
                autonumericFormat: {
                    minimumValue: '0',
                    maximumValue: '9999999999',
                    digitGroupSeparator: '.',
                    decimalCharacter: ',',
                    decimalPlaces: 0,
                    modifyValueOnWheel: false,
                    allowDecimalPadding: false,
                    emptyInputBehavior: 'zero'
                },
            },
            computed:{
            },
            methods: {
                addMoreItem(item) {
                    let itemIdx = this.itemList.length + 1;
                    let nItem = {
                        index: itemIdx,
                        no: itemIdx++,
                        itemId: 0,
                        qty: 0,
                    };
                    this.itemList.push(nItem);
                },
                addMoreUomItem(item) {
                    let nItem = {
                        unit: 0,
                        qty: 0,
                    };
                    this.uomList.push(nItem);
                },
                removeItem(item) {
                    this.itemList = this.itemList.filter(function (x) { return x !== item; });
                },
                removeUomItem(uom) {
                    this.uomList = this.uomList.filter(function (x) { return x !== uom; });
                },
            }
        });
    </script>

    <script>
        $(document).ready(function () {
            $(window).keydown(function (event) {
                if (event.keyCode === 13) {
                    event.preventDefault();
                    return false;
                }
            });
        });

        // $('#unit_id').select2();

        $("#image").fileinput({
            allowedFileExtensions: ["jpg", "jpeg", "png", "webp"],
            overwriteInitial: false,
            maxFileSize: 2048,
            showUpload: false,
        });

        $('#form_create_item').submit(function(){
            $('#btn_submit').hide();
            $('#btn_cancel').hide();
            $('#btn_loading').show(200);
        })
    </script>
@endsection
