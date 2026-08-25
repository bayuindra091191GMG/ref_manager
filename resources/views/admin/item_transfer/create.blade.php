@extends('layouts.admin')

@section('title')
    <title>Buat Transfer Barang</title>
@endsection

@section('content')
    <!-- Hero -->
    <div class="bg-body-light">
        <div class="content content-full">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Buat Transfer Barang
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.item_transfer.index') }}">Daftar Transfer Barang</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Buat Transfer Barang
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
                                <form class="space-y-3" id="form_create_locker" method="POST" action="{{ route('admin.item_transfer.store') }}">
                                    @csrf
                                    <div class="row">
                                        <div class="col-12 text-end">
                                            <button id="btn_loading" type="button" class="btn btn-success" style="display: none;">
                                                <i class="spinner-border spinner-border-sm text-green" role="status">
                                                    <span class="visually-hidden">Mohon Tunggu...</span>
                                                </i>
                                            </button>
                                            <input type="submit" id="btn_submit" class="btn btn-success" value="Simpan"/>&nbsp;&nbsp;
                                            <a id="btn_cancel" href="{{ route('admin.item_transfer.index') }}" class="btn btn-danger">Kembali</a>
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
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="name">Kode</label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control" placeholder="Name" value="AUTO" readonly/>
                                        </div>
                                    </div>

                                    {{-- <div class="row mb-2">
                                        <label class="col-sm-3 col-form-label" for="branch_id">Referensi Purchase Request <span class="text-red">*</span></label>
                                        <div class="col-sm-6">
                                            <select class="form-select" id="reference_id" name="reference_id">
                                                @if($isPr == 1)
                                                    <option value="{{$referenceId}}">{{$referenceCode}}</option>
                                                @endif
                                            </select>
                                        </div>
                                        <div class="col-sm-3">
                                            <button type="button" id="btn_load_data" class="btn btn-outline-info" v-on:click="loadData()">Ambil Data</button>
                                        </div>
                                    </div> --}}
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="name">Gudang Asal</label>
                                        <div class="col-sm-6">
                                            <select id="origin_warehouse" class="form-control form-control-select2 warehouse_select2" name="warehouse_origin_id"></select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="name">Gudang Tujuan</label>
                                        <div class="col-sm-6">
                                            <select id="destination_warehouse" class="form-control form-control-select2 warehouse_select2" name="warehouse_destination_id"></select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="name">Tanggal Pengiriman</label>
                                        <div class="col-sm-6">
                                            <input type="text" id="transfer_date" name="transfer_date" class="form-control" placeholder="Name" value="{{ old('transfer_date') }}"/>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <label class="col-form-label" for="description">Plat No</label>
                                        </div>
                                        <div class="col-sm-6">
                                            <input type="text" name="description" class="form-control" placeholder="Name" value="{{ old('description') }}"/>
{{--                                            <textarea id="description" name="description" class="form-control" rows="4" maxlength="150" placeholder="Description">{{ old('description') }}</textarea>--}}
                                        </div>
                                    </div>

                                    <h4 class="fw-normal">Daftar Barang</h4>
                                    <hr />
                                    <div class="row">
                                        <table class="table table-bordered table-striped table-vcenter" id="table_data">
                                            <thead>
                                            <tr>
                                                <th style="width: 5%">
                                                    <a class="btn btn-success" style="cursor:pointer;" v-on:click="addMoreItem">
                                                        <i class="fa fa-plus"></i>
                                                    </a>
                                                </th>
                                                <th style="width: 5%">No</th>
                                                <th style="width: 15%">Nama Produk</th>
                                                <th style="width: 8%">Unit</th>
                                                <th style="width: 8%">Qty</th>
                                                <th style="width: 8%">Stok</th>
                                                <th style="width: 8%">Keterangan</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr v-for="(item, index) in itemList">
                                                <td>
                                                    <a class="btn btn-danger" style="cursor: pointer" v-on:click="removeItem(item)">
                                                        <i class="fa fa-minus"></i>
                                                    </a>
                                                </td>
                                                <td><span>@{{index+1}}</span></td>
                                                <td>
                                                    <select2 :url="selectUrl"
                                                        v-model="item.itemId"
                                                             name="transfer_product[]" :data-index="index" class="form-select form-control-select2 item" placeholder="Pilih Produk">
                                                    </select2>
                                                </td>
                                                <td>
                                                    <select2 :url="item.url + item.itemId" :id="'unit' + item.id"
                                                        v-model="item.unitId" :value="item.unitId" name="unitIds[]"
                                                        class="form-select form-control-select2"
                                                        placeholder="Pilih Satuan"
                                                        :disabled="item.itemId == null || item.itemId == 0" required>
                                                    </select2>
                                                </td>
                                                <td>
                                                    <vue-autonumeric :options="autonumericFormat" name="transfer_qty[]" v-model="item.qty" class="form-control" v-on:input="calculateTotalQty()"></vue-autonumeric>
                                                </td>
                                                <td>
                                                    <vue-autonumeric :options="autonumericFormat" v-model="item.stock" class="form-control" readonly=""></vue-autonumeric>
                                                </td>
                                                <td>
                                                    <input type="text" name="descriptions[]" class="form-control" placeholder="Keterangan">
                                                </td>
                                            </tr>
                                            </tbody>
                                            <tfoot>
                                            <tr>
                                                <td colspan="4" style="text-align:right;vertical-align: middle;">Total Qty</td>
                                                <td>
                                                    <vue-autonumeric :options="autonumericFormat" name="total_qty" v-model="total_qty" class="form-control" readonly></vue-autonumeric>
                                                </td>
                                            </tr>
                                            </tfoot>
                                        </table>
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
    {{--    <link rel="stylesheet" href="{{ asset('custom/summernote/summernote-bs4.css') }}">--}}
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('custom/bootstrap-fileinput/css/fileinput.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('js/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}">
    <style>
        .bootstrap-tagsinput {
            background-color: #fff;
            border: 1px solid #ccc;
            box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
            display: block;
            padding: 4px 6px;
            color: #555;
            vertical-align: middle;
            border-radius: 4px;
            max-width: 100%;
            line-height: 22px;
            cursor: text;
        }
        .bootstrap-tagsinput input {
            border: none;
            box-shadow: none;
            outline: none;
            background-color: transparent;
            padding: 0 6px;
            margin: 0;
            width: auto;
            max-width: inherit;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice{
            color: #000;
        }
    </style>

@endsection

@section('scripts')
    <!-- jQuery -->
    <script type="text/javascript" src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>
    {{--    <script type="text/javascript" src="{{ asset('custom/summernote/summernote-bs4.min.js') }}"></script>--}}
    <script type="text/javascript" src="{{ asset('custom/bootstrap-fileinput/js/fileinput.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>

    <script type="text/javascript" href="{{ asset('custom/dist/bootstrap-tagsinput.min.js') }}"></script>

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
                total_qty: 0,
                itemList: [],

                autonumericFormat: {
                    minimumValue: '0',
                    maximumValue: '9999999999',
                    digitGroupSeparator: '.',
                    decimalCharacter: ',',
                    decimalPlaces: 2,
                    modifyValueOnWheel: false,
                    unformatOnSubmit: true,
                    allowDecimalPadding: true,
                },
            },
            computed: {
                selectUrl: function(){
                    let url = '{{ route('admin.item.select') }}' + '?ids=';
                    for(let i=0; i<this.itemList.length; i++){
                        url += this.itemList[i].itemId + ',';
                    }
                    url = url.substring(0, url.length - 1);
                    console.log(url);
                    return url;
                }
            },
            methods: {
                loadData : async () => {
                    let reference = $('#reference_id').val();
                    if (reference === '') {
                        alert('Harus Pilih Purchase Request Terlebih Dahulu!');
                    }

                    window.location.href = "{{route('admin.item_transfer.create')}}?reference_id=" + reference;
                },
                addMoreItem(item) {
                    let itemIdx = this.itemList.length + 1;
                    let nItem = {
                        index: itemIdx,
                        no: itemIdx++,
                        itemId: 0,
                        unitId: 0,
                        qty: 0,
                        stock: 0,
                        url: '{{ route('admin.item.unit.select') }}?id=',
                        location: ''
                    };
                    this.itemList.push(nItem);
                },
                removeItem(item) {
                    this.itemList = this.itemList.filter(function (x) { return x !== item; });
                    this.calculateTotalQty();
                },
                calculateTotalQty() {
                    debugger;
                    let tempTotal = 0;
                    for(var i = 0; i < this.itemList.length; i++){
                        debugger;
                        tempTotal = tempTotal + this.itemList[i].qty;

                    }
                    this.total_qty = tempTotal;
                },

                getStockAndLocation(index){
                    if(this.itemList[index].itemId < 1) return false;

                    axios.get('{{ route('admin.item_transfer.stock') }}', {
                        params: {
                            item_id: this.itemList[index].itemId,
                            warehouse_id: $('#origin_warehouse').val(),
                        }
                    }).then(response => {
                        this.itemList[index].stock = response.data.stock;
                        this.itemList[index].location = response.data.location;
                    });
                }
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

        var date = new Date();
        var today = new Date(date.getFullYear(), date.getMonth(), date.getDate());

        $('#transfer_date').datepicker({
            autoclose: true,
            todayHighlight: true,
            format: "dd-mm-yyyy"
        });
        $( '#transfer_date' ).datepicker( 'setDate', today );

        $('#form_create_locker').submit(function(){
            $('#btn_submit').hide();
            $('#btn_cancel').hide();
            $('#btn_loading').show(200);
        })
        $('.warehouse_select2').select2({
            placeholder: {
                id: '-1',
                text: ' - Pilih Gudang - '
            },
            width: '100%',
            minimumInputLength: 0,
            ajax: {
                url: '{{ route('admin.warehouse.select') }}',
                dataType: 'json',
                data: function (params) {
                    return {
                        search: $.trim(params.term),
                    };
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                }
            }
        });

        $('#form_create_locker').on('change', '.item', function (){
            console.log($(this).data('index'));
            if (vueComponent.itemList[$(this).data('index')].edited) {
                vueComponent.itemList[$(this).data('index')].unit_id = null;
                $(`#unit${vueComponent.itemList[$(this).data('index')].id}`).val('').trigger('change');
            } else {
                vueComponent.itemList[$(this).data('index')].edited = true;
            }
            vueComponent.getStockAndLocation($(this).data('index'));
        });

        $('#origin_warehouse').on('change', function (){
            for(var i = 0; i < vueComponent.itemList.length; i++){
                vueComponent.getStockAndLocation(i);
            }
        });
    </script>
@endsection
