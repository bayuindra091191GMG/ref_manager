@extends('layouts.admin')

@section('title')
    <title>Ubah Barang</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Ubah Barang
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
                            Ubah Barang
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
                                <form class="space-y-3" id="form_edit_item" method="POST"
                                    action="{{ route('admin.item.update', $data->id) }}" enctype="multipart/form-data">
                                    @csrf
                                    <div class="row">
                                        <div class="col-12 text-end">
                                            <button id="btn_loading" type="button" class="btn btn-success"
                                                style="display: none;">
                                                <i class="spinner-border spinner-border-sm text-green" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </i>
                                            </button>
                                            <input type="submit" id="btn_submit" class="btn btn-success"
                                                value="Simpan" />&nbsp;&nbsp;
                                            <a id="btn_cancel" href="{{ route('admin.item.index') }}"
                                                class="btn btn-danger">Batal</a>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            @include('partials._success')
                                            @include('partials._error')
                                        </div>
                                    </div>
                                    @if (count($errors))
                                        <div class="row">
                                            <div class="col-lg-8">
                                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                    <ul>
                                                        @foreach ($errors->all() as $error)
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
                                            <input type="hidden" id="id" name="id" class="form-control"
                                                value="{{ $data->id }}" />
                                        </div>
                                    </div>

                                    <ul class="nav nav-tabs nav-tabs-block" role="tablist">
                                        <li class="nav-item">
                                            <button type="button" class="nav-link active"
                                                id="btabs-animated-fade-general-tab" data-bs-toggle="tab"
                                                data-bs-target="#btabs-animated-fade-general" role="tab"
                                                aria-controls="btabs-animated-fade-general" aria-selected="true">
                                                Data Umum
                                            </button>
                                        </li>
{{--                                        <li class="nav-item">--}}
{{--                                            <button type="button" class="nav-link" id="btabs-animated-fade-featured-tab"--}}
{{--                                                data-bs-toggle="tab" data-bs-target="#btabs-animated-fade-featured"--}}
{{--                                                role="tab" aria-controls="btabs-animated-fade-featured"--}}
{{--                                                aria-selected="true">--}}
{{--                                                Definisi Barang (BOM)--}}
{{--                                            </button>--}}
{{--                                        </li>--}}
                                    </ul>
                                    <div class="block-content tab-content overflow-hidden">
                                        {{-- tab general data --}}
                                        <div class="tab-pane fade show active" id="btabs-animated-fade-general"
                                            role="tabpanel" aria-labelledby="btabs-animated-fade-general-tab">

                                            <div class="row mb-3">
                                                <label class="col-sm-3 col-form-label" for="category_id">Kategori <span
                                                        class="text-red">*</span></label>
                                                <div class="col-sm-4">
                                                    <select id="category_id" name="category_id"
                                                        class="form-select form-control-select2">
                                                        @foreach ($itemCategories as $itemCategory)
                                                            <option value="{{ $itemCategory->id }}"
                                                                @if ($itemCategory->id == $data->category_id) selected @endif>
                                                                {{ $itemCategory->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label class="col-sm-3 col-form-label" for="category_id">Kategori untuk
                                                    Jurnal <span class="text-red">*</span></label>
                                                <div class="col-sm-4">
                                                    <select id="category_2_id" name="category_2_id"
                                                        class="form-select form-control-select2">
                                                        @if (empty($data->category_2_id))
                                                            <option value="0" selected>-- PILIH KATEGORI --</option>
                                                        @else
                                                            @if ($data->category_2_id <= 4 || $data->category_2_id >= 19)
                                                                <option value="0" selected>-- PILIH KATEGORI --
                                                                </option>
                                                            @endif
                                                        @endif
                                                        @foreach ($itemJurnalCategories as $itemCategory)
                                                            <option value="{{ $itemCategory->id }}"
                                                                @if ($itemCategory->id == $data->category_2_id) selected @endif>
                                                                {{ $itemCategory->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label class="col-sm-3 col-12 col-form-label" for="code">Kode Barang
                                                    <span class="text-red">*</span></label>
                                                <div class="col-sm-6 col-12">
                                                    <input type="text" id="code" name="code"
                                                        class="form-control" placeholder="Kode Barang"
                                                        style="text-transform: uppercase;" value="{{ $data->code }}"
                                                        required />
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label class="col-sm-3 col-form-label" for="name">Nama Barang <span
                                                        class="text-red">*</span></label>
                                                <div class="col-sm-6">
                                                    <input type="text" id="name" name="name"
                                                        class="form-control" placeholder="Nama Barang"
                                                        value="{{ $data->name }}" required />
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label class="col-sm-3 col-12 col-form-label" for="code">Tipe Barang
                                                    <span class="text-red">*</span></label>
                                                <div class="col-sm-6 col-12">
                                                    <select class="form-select" v-model="isStock" name="is_stock" required>
                                                        <option value="1">Non Stock</option>
                                                        <option value="0">Stock</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label class="col-sm-3 col-form-label" for="barcode">Barcode</label>
                                                <div class="col-sm-6">
                                                    <input type="text" id="barcode" name="barcode"
                                                        class="form-control" placeholder="Barcode Barang"
                                                        value="{{ $data->barcode }}" />
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label class="col-sm-3 col-12 col-form-label" for="unit_id">Unit Material
                                                    <span class="text-red">*</span></label>
                                                <div class="col-sm-4 col-12">
                                                    <select id="unit_id" name="unit_id"
                                                            class="form-select form-control-select2">
                                                        <option value="-1"> --- Pilih Unit --- </option>
                                                        @foreach ($unitMaterials as $unitMaterial)
                                                            @if ($data->item_units != null)
                                                                <option value="{{ $unitMaterial->id }}" {{ $unitMaterial->id == $data->item_units[0]->unit_id ? 'selected' : '' }}>
                                                                    {{ $unitMaterial->name }}</option>
                                                            @else
                                                                <option value="{{ $unitMaterial->id }}" {{ $unitMaterial->name == 'PCS' ? 'selected' : '' }}>{{ $unitMaterial->name }}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label class="col-sm-3 col-12 col-form-label" for="price">Harga
                                                    Jual</label>
                                                <div class="col-sm-4 col-12">
                                                    <div class="input-group">
                                                        <span class="input-group-text">Rp</span>
                                                        <vue-autonumeric :options="autonumericFormat" name="price"
                                                            class="form-control text-end" placeholder="Harga Jual"
                                                            value="{{ $data->price }}">

                                                        </vue-autonumeric>
                                                    </div>

                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label class="col-sm-3 col-12 col-form-label" for="cost_price">Harga
                                                    Beli</label>
                                                <div class="col-sm-4 col-12">
                                                    <div class="input-group">
                                                        <span class="input-group-text">Rp</span>
                                                        <vue-autonumeric :options="autonumericFormat" name="cost_price"
                                                            class="form-control text-end" placeholder="Harga Beli"
                                                            value="{{ $data->cost_price ?? 0 }}">

                                                        </vue-autonumeric>
                                                    </div>

                                                </div>
                                            </div>
                                            @php($selectedBadgeStatus = old('badge_status', $data->badge_status ?? ''))
                                            <div class="row mb-3">
                                                <label class="col-sm-3 col-12 col-form-label">Badge Status</label>
                                                <div class="col-sm-6 col-12">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="badge_status" id="badge_status_none" value="" {{ $selectedBadgeStatus === '' ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="badge_status_none">
                                                            None
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="badge_status" id="badge_status_recommended" value="recommended" {{ $selectedBadgeStatus === 'recommended' ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="badge_status_recommended">
                                                            Recommended
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="badge_status" id="badge_status_best_seller" value="best_seller" {{ $selectedBadgeStatus === 'best_seller' ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="badge_status_best_seller">
                                                            Best Seller
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            @if($branch->is_oura === 1)
                                                <div class="row mb-3">
                                                    <label class="col-sm-3 col-12 col-form-label" for="is_unlimited_stock">Pemakaian Stok</label>
                                                    <div class="col-sm-6 col-12">
                                                        <select class="form-select" id="is_unlimited_stock" name="is_unlimited_stock">
                                                            <option value="0" @if($data->is_unlimited_stock === 0) selected @endif>Pakai Angka Stok</option>
                                                            <option value="1" @if($data->is_unlimited_stock === 1) selected @endif>Tidak Pakai Angka Stok</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row mb-3">
                                                    <label class="col-sm-3 col-12 col-form-label" for="is_addon">Apakah Addon? (Khusus Aplikasi)</label>
                                                    <div class="col-sm-6 col-12">
                                                        <select class="form-select" id="is_addon" name="is_addon">
                                                            <option value="0" @if(!$data->is_addon) selected @endif>Tidak</option>
                                                            <option value="1" @if($data->is_addon) selected @endif>Ya</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="row mb-3">
                                                <label class="col-sm-3 col-form-label" for="status">Status</label>
                                                <div class="col-sm-6">
                                                    <select class="form-select" id="status" name="status">
                                                        <option value="1" @if($data->status_id == 1) selected @endif>Aktif</option>
                                                        <option value="2" @if($data->status_id == 2) selected @endif>Non-Aktif</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <label class="col-sm-3 col-12 col-form-label" for="ktp">Foto
                                                    <br>
                                                    <img src="{{ asset('storage/images/items/'.$data->image_path) }}" alt="Foto" class="img-thumbnail" style="max-width: 200px; max-height: 200px;"/>
                                                </label>
                                                <div class="col-sm-8 col-12">
                                                    <input type="file" name="image" id="image" accept=".png,.jpg,.jpeg,.webp"/>
                                                    <small class="text-muted">Maksimal size image 3 MB</small>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- tab general data --}}

                                        {{-- tab featured menu --}}
                                        <div class="tab-pane fade show" id="btabs-animated-fade-featured" role="tabpanel"
                                            aria-labelledby="btabs-animated-fade-featured-tab">
                                            <h4 class="fw-normal">Definisi Barang (BOM)</h4>
                                            <hr />
                                            <div class="row">
                                                <table
                                                    class="table table-bordered table-striped table-vcenter js-dataTable-full"
                                                    id="table_data">
                                                    <thead>
                                                        <tr>
                                                            <th style="width: 5%">
                                                                <a class="btn btn-success" style="cursor:pointer;"
                                                                    v-on:click="addMoreItem">
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
                                                                <a class="btn btn-danger" style="cursor: pointer"
                                                                    v-on:click="removeItem(item)">
                                                                    <i class="fa fa-minus"></i>
                                                                </a>
                                                            </td>
                                                            <td><span>@{{ ++index }}</span></td>
                                                            <td>
                                                                <select2 url="{{ route('admin.item.select') }}"
                                                                    v-model="item.itemId" name="bom_product[]"
                                                                    class="form-select form-control-select2"
                                                                    placeholder="Pilih Barang">
                                                                </select2>
                                                            </td>
                                                            <td>
                                                                <vue-autonumeric :options="autonumericFormat"
                                                                    name="bom_qty[]" v-model="item.qty"
                                                                    class="form-control"></vue-autonumeric>
                                                            </td>
                                                            <td>
                                                                <select2 url="{{ route('admin.uom.select') }}"
                                                                    v-model="item.unitId" name="bom_unit[]"
                                                                    class="form-select form-control-select2"
                                                                    placeholder="Pilih Unit Material">
                                                                </select2>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>

                                        </div>
                                        {{-- tab featured menu --}}

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
    <style>
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            color: #000;
        }
    </style>
@endsection

@section('scripts')
    <!-- jQuery -->
    <script type="text/javascript" src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('js/plugins/bootstrap-fileinput/js/fileinput.js') }}"></script>

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
                ...@json($vueData),
                ...{
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
            },
            computed: {},
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
                removeItem(item) {
                    this.itemList = this.itemList.filter(function(x) {
                        return x !== item;
                    });
                },
            }
        });
    </script>

    <script>
        $(document).ready(function() {
            $(window).keydown(function(event) {
                if (event.keyCode === 13) {
                    event.preventDefault();
                    return false;
                }
            });
        });

        $('#form_edit_item').submit(function() {
            $('#btn_submit').hide();
            $('#btn_cancel').hide();
            $('#btn_loading').show(200);
        })

        $('#image').fileinput({
            'showUpload': false,
            'overwriteInitial': false,
        });
    </script>
@endsection
