@extends('layouts.admin')

@section('title')
    <title>Buat Transfer Barang</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
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
                                <form class="space-y-3" id="form_item_transfer" method="POST" action="{{ route('admin.item_transfer.store') }}">
                                    @csrf
                                    <div class="row">
                                        <div class="col-12 text-end">
                                            <button id="btn_loading" type="button" class="btn btn-success" style="display: none;">
                                                <i class="spinner-border spinner-border-sm text-green" role="status">
                                                    <span class="visually-hidden">Mohon Tunggu...</span>
                                                </i>
                                            </button>
                                            <input type="submit" id="btn_submit" class="btn btn-success" value="Simpan" :disabled="validateForm()"/>&nbsp;&nbsp;
                                            <a id="btn_cancel" href="{{ route('admin.item_transfer.index') }}" class="btn btn-danger">Batal</a>
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
                                        <label class="col-sm-3 col-form-label" for="transfer_date">Tanggal Pengiriman</label>
                                        <div class="col-sm-6">
                                            <input type="text" id="transfer_date" name="transfer_date" class="form-control" placeholder="Name" value="{{ old('transfer_date') }}"/>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <label class="col-form-label" for="description">Deskripsi</label>
                                        </div>
                                        <div class="col-sm-6">
                                            <textarea id="description" name="description" class="form-control" rows="4" maxlength="150" placeholder="Description">{{ old('description') }}</textarea>
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
                                                <th style="width: 8%">Stok</th>
                                                <th style="width: 8%">Qty</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr v-for="(item, index) in itemList" :key="item.id">
                                                <td>
                                                    <a class="btn btn-danger" style="cursor: pointer" v-on:click="removeItem(item)">
                                                        <i class="fa fa-minus"></i>
                                                    </a>
                                                </td>
                                                <td style="vertical-align: top"><span>@{{index+1}}</span></td>
                                                <td style="vertical-align: top">
                                                    <select2 url="{{route('admin.item.select')}}" v-model="item.itemId"
                                                             name="transfer_product[]" class="form-select form-control-select2 select-item" :data-index="index" placeholder="Pilih Produk">
                                                    </select2>
                                                </td>
                                                <td style="vertical-align: top">
                                                    <vue-autonumeric :options="autonumericFormat" name="transfer_qty[]" v-model="item.stock" class="form-control" readonly=""></vue-autonumeric>
                                                </td>
                                                <td>
                                                    <vue-autonumeric :options="autonumericFormat" name="transfer_qty[]" v-model="item.qty" class="form-control" v-on:input="calculateTotalQty()"></vue-autonumeric>
                                                    <p class="text-danger fs-xs m-0" v-if="item.qty > item.stock">Qty tidak boleh melebihi stok!</p>
                                                </td>
                                            </tr>
                                            </tbody>
                                            <tfoot>
                                            <tr>
                                                <td colspan="3" style="text-align:right;vertical-align: middle;">Total Qty</td>
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
    {{--    <script type="text/javascript" src="{{ asset('custom/summernote/summernote-bs4.min.js') }}"></script>--}}
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
                total_qty: 0,

                itemList: [],
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
            computed:{
            },
            methods: {
                addMoreItem(item) {
                    let itemIdx = this.itemList.length + 1;
                    let nItem = {
                        id: makeid(10),
                        index: itemIdx,
                        no: itemIdx++,
                        itemId: 0,
                        stock: 0,
                        qty: 0,
                    };
                    this.itemList.push(nItem);
                },

                getStock(index){
                    let app = this;
                    $.ajax({
                        url: '{{ route('admin.item_transfer.stock') }}',
                        type: 'GET',
                        data: {
                            item: app.itemList[index].itemId,
                            warehouse: $('#origin_warehouse').val(),
                        }
                    }).then(function (result){
                        app.itemList[index].stock = result.stock;
                    });
                },

                removeItem(item) {
                    this.itemList = this.itemList.filter(function (x) { return x !== item; });
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

                validateForm(){
                    var check = false;

                    if(this.itemList.length == 0){
                        check = true;
                    }

                    for(var i = 0; i < this.itemList.length; i++){
                        if(this.itemList[i].qty == 0){
                            check = true;
                            break;
                        }

                        if(this.itemList[i].qty > this.itemList[i].stock){
                            check = true;
                            break;
                        }
                    }

                    return check;
                }
            },
            mounted(){
                let app = this;

                $('#form_item_transfer').on('change', '.select-item', function (){
                    app.getStock($(this).data("index"));
                });

                $('#origin_warehouse').on('change', function (){
                    for(var i = 0; i < app.itemList.length; i++){
                        app.getStock(i);
                    }
                });
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

        $('#form_item_transfer').submit(function(){
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
    </script>
@endsection
