@extends('layouts.admin')

@section('title')
    <title>Buat Stock In</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Buat Stock In
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.stock_in.index') }}">Daftar Stock In</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Buat Stock In
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
                                <form class="space-y-3" id="form_create_stock_in" method="POST" action="{{ route('admin.stock_in.store') }}">
                                    @csrf
                                    <div class="row">
                                        <div class="col-12 text-end">
                                            <button id="btn_loading" type="button" class="btn btn-success" style="display: none;">
                                                <i class="spinner-border spinner-border-sm text-green" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </i>
                                            </button>
                                            <input type="submit" id="btn_submit" class="btn btn-success" value="Simpan"/>&nbsp;&nbsp;
                                            <a id="btn_cancel" href="{{ route('admin.stock_in.index') }}" class="btn btn-danger">Batal</a>
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
                                        <label class="col-sm-3 col-form-label" for="code">Kode</label>
                                        <div class="col-sm-6">
                                            <input type="text" id="code" class="form-control" placeholder="Name" value="AUTO" readonly/>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="origin_warehouse">Gudang Keluar <span class="text-red">*</span></label>
                                        <div class="col-sm-6">
                                            <select id="origin_warehouse" class="form-control form-control-select2 warehouse_select2" name="warehouse_origin_id"></select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="requested_by">Diminta Oleh</label>
                                        <div class="col-sm-4">
                                            <input type="text" id="requested_by" name="requested_by" class="form-control" value="{{ old('requested_by') }}"/>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="purpose">Tujuan</label>
                                        <div class="col-sm-6">
                                            <input type="text" id="purpose" name="purpose" class="form-control" placeholder="Tujuan pengeluaran barang" value="{{ old('purpose') }}"/>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <label class="col-form-label" for="description">Deskripsi</label>
                                        </div>
                                        <div class="col-sm-6">
                                            <textarea id="description" name="description" class="form-control" rows="4" maxlength="200" placeholder="Deskripsi">{{ old('description') }}</textarea>
                                        </div>
                                    </div>

                                    <h4 class="fw-normal">Daftar Barang</h4>
                                    <hr />
                                    <div class="row">
                                        <table class="table table-bordered table-striped table-vcenter js-dataTable-full" id="table_data">
                                            <thead>
                                            <tr>
                                                <th class="text-center" style="width: 5%">
                                                    <a class="btn btn-success" style="cursor:pointer;" v-on:click="addMoreItem">
                                                        <i class="fa fa-plus"></i>
                                                    </a>
                                                </th>
                                                <th class="text-center" style="width: 5%">No</th>
                                                <th style="width: 15%">Nama Produk</th>
                                                <th class="text-end" style="width: 8%">Qty</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr v-for="(item, index) in itemList">
                                                <td class="text-center">
                                                    <a class="btn btn-danger" style="cursor: pointer" v-on:click="removeItem(item)">
                                                        <i class="fa fa-minus"></i>
                                                    </a>
                                                </td>
                                                <td class="text-center"><span>@{{++index}}</span></td>
                                                <td>
                                                    <select2 url="{{route('admin.item.select')}}" v-model="item.itemId"
                                                             name="items[]" class="form-select form-control-select2" placeholder="Pilih Barang">
                                                    </select2>
                                                </td>
                                                <td>
                                                    <vue-autonumeric :options="autonumericFormat" name="qtys[]" v-model="item.qty" class="form-control text-end" v-on:input="calculateTotalQty()"></vue-autonumeric>
                                                </td>
                                            </tr>
                                            </tbody>
                                            <tfoot>
                                            <tr>
                                                <td colspan="3" style="text-align:right;vertical-align: middle;">Total Qty</td>
                                                <td>
                                                    <vue-autonumeric :options="autonumericFormat" name="total_qty" v-model="total_qty" class="form-control text-end" readonly></vue-autonumeric>
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
                        index: itemIdx,
                        no: itemIdx++,
                        itemId: 0,
                        qty: 0,
                    };
                    this.itemList.push(nItem);
                },
                removeItem(item) {
                    this.itemList = this.itemList.filter(function (x) { return x !== item; });
                },
                calculateTotalQty() {
                    let tempTotal = 0;
                    for(var i = 0; i < this.itemList.length; i++){
                        tempTotal = tempTotal + this.itemList[i].qty;

                    }
                    this.total_qty = tempTotal;
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


        $('#form_create_stock_in').submit(function(){
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
