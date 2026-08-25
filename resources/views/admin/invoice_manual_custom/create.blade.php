@extends('layouts.admin')

@section('title')
    <title>Buat Invoice Manual</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Buat Invoice Manual
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.invoice_manual.index') }}">Invoice Manual List</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Buat Invoice Manual
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
                                <form class="space-y-3" id="form_create_manual_invoice" method="POST" action="{{ route('admin.invoice_manual.store') }}">
                                    @csrf
                                    <div class="row">
                                        <div class="col-12 text-end">
                                            <button id="btn_loading" type="button" class="btn btn-outline-success" style="display: none;">
                                                <i class="spinner-border spinner-border-sm text-green" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </i>
                                            </button>
                                            <input type="submit" id="btn_submit" class="btn btn-outline-success" value="Submit"/>&nbsp;&nbsp;
                                            <a id="btn_cancel" href="{{ route('admin.invoice_manual.index') }}" class="btn btn-outline-danger">Cancel</a>
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
                                        <label class="col-sm-3 col-form-label" for="name">Tanggal Dibutuhkan</label>
                                        <div class="col-sm-6">
                                            <input type="text" id="transaction_date" name="transaction_date" class="form-control" placeholder="Name" value="{{ old('transaction_date') }}"/>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="name">Gudang Keluar</label>
                                        <div class="col-sm-6">
                                            <select class="form-control warehouse_select2" name="warehouse_id" id="warehouse_id"></select>
                                        </div>
                                    </div>
                                    {{-- <div class="row">
                                        <label class="col-sm-3 col-form-label" for="name">Nama Customer</label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control" placeholder="Nama Customer" name="customer_name"/>
                                        </div>
                                    </div> --}}
                                    <div class="row">
                                        <div class="col-12">
                                            <span style="color: red;">Pilih salah satu!</span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="employee">Karyawan</label>
                                        <div class="col-sm-6">
                                            <select class="form-control" id="employee" name="employee"></select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="partner">Therapis</label>
                                        <div class="col-sm-6">
                                            <select class="form-control" id="partner" name="partner"></select>
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
                                        <table class="table table-bordered table-striped table-vcenter js-dataTable-full" id="table_data">
                                            <thead>
                                            <tr>
                                                <th style="width: 3%">
                                                    <a class="btn btn-success" style="cursor:pointer;" v-on:click="addMoreItem">
                                                        <i class="far fa-plus-square"></i>
                                                    </a>
                                                </th>
                                                <th style="width: 3%">No</th>
                                                <th style="width: 15%">Item</th>
                                                <th style="width: 8%">Qty</th>
                                                <th style="width: 10%">Harga</th>
                                                <th style="width: 10%">Total</th>
                                                <th style="width: 10%">Disc</th>
                                                <th style="width: 10%">Sub Total</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr v-for="(item, index) in itemList">
                                                <td>
                                                    <a class="btn btn-danger" style="cursor: pointer" v-on:click="removeItem(item)">
                                                        <i class="far fa-minus-square"></i>
                                                    </a>
                                                </td>
                                                <td><span>@{{++index}}</span></td>
                                                <td>
                                                    <input type="text" class="form-control" placeholder="Nama Barang" name="itemNames[]"/>
{{--                                                    <select2 url="{{route('admin.item.selectWithoutFood')}}" v-model="item.itemId"--}}
{{--                                                             v-on:input="getItemDetail(item)"--}}
{{--                                                             name="itemIds[]" class="form-select form-control-select2" placeholder="Pilih Produk">--}}
{{--                                                    </select2>--}}
                                                </td>
                                                <td>
                                                    <vue-autonumeric :options="autonumericFormat" name="qty[]" v-model="item.qty" class="form-control" v-on:input="calculatePrice()"></vue-autonumeric>
                                                </td>
                                                <td>
                                                    <vue-autonumeric :options="autonumericFormat" name="prices[]" class="form-control" v-model="item.price"
                                                                     v-on:input="calculatePrice()"></vue-autonumeric>
                                                </td>
                                                <td>
                                                    <vue-autonumeric :options="autonumericFormat" name="totals[]" class="form-control" :value="item.price * item.qty" readonly></vue-autonumeric>
                                                </td>
                                                <td>
                                                    <vue-autonumeric :options="autonumericFormat" name="disc1_values[]" class="form-control" v-model="item.disc1Value" v-on:input="calculatePrice()"></vue-autonumeric>
                                                </td>
                                                <td>
                                                    <vue-autonumeric :options="autonumericFormat" name="subtotals[]" class="form-control" :value="(item.price * item.qty) - item.disc1Value" readonly></vue-autonumeric>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="row">
                                        <label class="col-sm-2 col-form-label" for="status">Subtotal </label>
                                        <div class="col-sm-8">
                                            <vue-autonumeric :options="autonumericFormat" name="subtotal" class="form-control" v-model="subTotal" readonly></vue-autonumeric>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <label class="col-sm-2 col-form-label" for="status">Diskon </label>
                                        <div class="col-sm-8">
                                            <vue-autonumeric :options="autonumericFormat" name="disc1_value" class="form-control" v-model="disc1Value" v-on:input="calculatePrice()"></vue-autonumeric>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <label class="col-sm-2 col-form-label" for="status">Ppn </label>
                                        <div class="col-sm-2">
                                            <div class="input-group">
                                                <vue-autonumeric :options="autonumericFormat" name="ppn_percent" class="form-control" v-model="ppnPercent" v-on:input="calculatePrice()"></vue-autonumeric>
                                                <span class="input-group-text" id="basic-addon2">%</span>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <vue-autonumeric :options="autonumericFormat" name="ppn_value" class="form-control" v-model="ppnValue" readonly></vue-autonumeric>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <label class="col-sm-2 col-form-label" for="status">Grand Total</label>
                                        <div class="col-sm-8">
                                            <vue-autonumeric :options="autonumericFormat" name="grandtotal" class="form-control" v-model="grandTotal" readonly></vue-autonumeric>
                                        </div>
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

                subTotal: 0,
                ppnPercent: 0,
                ppnValue: 0,
                disc1: 0,
                disc1Value: 0,
                grandTotal: 0,

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
                async getItemDetail(item){
                    await axios({
                        method: 'get',
                        url: '{{ route('admin.item.get') }}?item_id=' + item.itemId,
                    })
                        .catch(function (error){
                            if(error.response){
                                console.log(error.response);
                            }
                        })
                        .then(function (response) {
                            if(response.status === 200){
                                let index = vueComponent.itemList.indexOf(item);
                                vueComponent.itemList[index].price = response.data.price;
                                vueComponent.itemList[index].qty = 1;
                            }
                        });
                },
                addMoreItem(item) {
                    let itemIdx = this.itemList.length + 1;
                    let nItem = {
                        index: itemIdx,
                        no: itemIdx++,
                        itemId: 0,
                        qty: 0,
                        price: 0,
                    };
                    this.itemList.push(nItem);
                },
                removeItem(item) {
                    this.itemList = this.itemList.filter(function (x) { return x !== item; });
                },
                calculateDiscountHeader(){
                    if(this.itemList.length > 0){
                        // let discount = this.subTotal * (this.disc1/100);
                        // this.disc1Value = discount;
                        this.ppnValue = (this.subTotal * this.ppnPercent) / 100;
                        this.grandTotal = this.subTotal - this.disc1Value + this.ppnValue;
                    }
                },
                calculatePrice(){
                    if(this.itemList.length > 0){
                        this.subTotal = 0;
                        for(var i =0; i< this.itemList.length; i++){
                            var total = (this.itemList[i].qty * this.itemList[i].price) - this.itemList[i].disc1Value;
                            this.subTotal = this.subTotal + total;
                        }
                        this.ppnValue = (this.subTotal * this.ppnPercent) / 100;
                        this.grandTotal = this.subTotal - this.disc1Value + this.ppnValue;
                        this.calculateDiscountHeader();
                    }
                },
                calculateTotalQty() {
                    //debugger;
                    let tempTotal = 0;
                    for(var i = 0; i < this.itemList.length; i++){
                        //debugger;
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

        var date = new Date();
        var today = new Date(date.getFullYear(), date.getMonth(), date.getDate());

        $('#transaction_date').datepicker({
            autoclose: true,
            todayHighlight: true,
            format: "dd-mm-yyyy"
        });
        $( '#transaction_date' ).datepicker( 'setDate', today );

        $('#form_create_manual_invoice').submit(function(){
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
        $('#customer_id').select2({
            placeholder: {
                id: '-1',
                text: ' - Pilih Customer - '
            },
            width: '100%',
            minimumInputLength: 0,
            ajax: {
                {{--url: '{{ route('admin.customer.select') }}',--}}
                url: '{{ route('admin.customer.select') }}',
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

$('#employee').select2({
    placeholder: {
        id: '-1',
        text: ' --- Pilih Karyawan --- '
    },
    width: '100%',
    minimumInputLength: 0,
    ajax: {
        url: '{{ route('admin.employee.select') }}',
        dataType: 'json',
        data: function (params) {
            return {
                search: $.trim(params.term),
                branch_id: {{ $branches->id }}
            };
        },
        processResults: function (data) {
            return {
                results: data
            };
        }
    }
});

$('#partner').select2({
    placeholder: {
        id: '-1',
        text: ' --- Pilih Therapis --- '
    },
    width: '100%',
    minimumInputLength: 0,
    ajax: {
        url: '{{ route('admin.partner.select') }}',
        dataType: 'json',
        data: function (params) {
            return {
                search: $.trim(params.term),
                branch_id: {{ $branches->id }}
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
