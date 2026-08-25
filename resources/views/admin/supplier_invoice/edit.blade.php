@extends('layouts.admin')

@section('title')
    <title>Ubah Supplier Invoice</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Ubah Supplier Invoice
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.supplier_invoice.index') }}">Supplier Invoice List</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Ubah Supplier Invoice
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
                                <form class="space-y-3" id="form_create_locker" method="POST" action="{{ route('admin.supplier_invoice.update') }}">
                                    @csrf
                                    <div class="row">
                                        <div class="col-12 text-end">
                                            <button id="btn_loading" type="button" class="btn btn-outline-success" style="display: none;">
                                                <i class="spinner-border spinner-border-sm text-green" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </i>
                                            </button>
                                            <input type="submit" id="btn_submit" class="btn btn-outline-success" value="Submit"/>&nbsp;&nbsp;
                                            <a id="btn_cancel" href="{{ route('admin.supplier_invoice.index') }}" class="btn btn-outline-danger">Cancel</a>
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
                                            <input type="hidden" id="id" name="id" class="form-control" value="{{ $data->id }}" />
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="name">Kode</label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control" placeholder="Name" value="{{$data->code}}" readonly/>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="name">Supplier</label>
                                        <div class="col-sm-6">
                                            <select id="supplier_id" class="form-control form-control-select2" name="supplier_id">
                                                <option value="{{$data->supplier_id}}">{{$data->supplier->name}}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="name">Tanggal Dibutuhkan</label>
                                        <div class="col-sm-6">
                                            <input type="text" id="required_date" name="required_date" class="form-control" placeholder="Tanggal dibutuhkan"
                                                   value="{{ \Carbon\Carbon::parse($data->required_date)->format('d-m-Y') }}"/>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="name">Jangka Waktu Pembayaran</label>
                                        <div class="col-sm-6">
                                            <vue-autonumeric :options="autonumericFormat" name="top" class="form-control" value="{{ $data->top }}" ></vue-autonumeric>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <label class="col-form-label" for="description">Deskripsi</label>
                                        </div>
                                        <div class="col-sm-6">
                                            <textarea id="description" name="description" class="form-control" rows="4" maxlength="150" placeholder="Description">{{ $data->description }}</textarea>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="name">Tipe (Perhatian : Jika berubah maka data lama akan terhapus)</label>
                                        <div class="col-sm-6">
                                            <select class="form-select" id="type" name="type">
                                                <option value="GR" @if($data->type == "GR") selected @endif>GOOD RECEIVE</option>
                                                <option value="NONGR" @if($data->type == "NONGR") selected @endif>NON GOOD RECEIVE</option>
                                            </select>
                                        </div>
                                    </div>
                                    @if($data->type == "GR")
                                        @php($displayDivGr = 'block')
                                        @php($displayDivNonGr = 'none')
                                    @else
                                        @php($displayDivGr = 'none')
                                        @php($displayDivNonGr = 'block')
                                    @endif
                                    <div id="gr-div" style="display: {{$displayDivGr}}">
                                        <div class="row mt-5">
                                            <label class="col-sm-3 col-form-label" for="branch_id">Referensi Good Receive (kalau ada)</label>
                                            <div class="col-sm-6">
                                                <select class="form-select" id="reference_id" name="reference_id"></select>
                                            </div>
                                            <div class="col-sm-3">
                                                <button type="button" id="btn_load_data" class="btn btn-outline-info" v-on:click="addData()">Tambah Data</button>
                                            </div>
                                        </div>

                                        <h4 class="fw-normal">Daftar Barang</h4>
                                        <hr />
                                        <div class="row">
                                            <table class="table table-bordered table-striped table-vcenter js-dataTable-full" id="table_data">
                                                <thead>
                                                <tr>
                                                    <th style="width: 5%">
                                                        {{--                                                    <a class="btn btn-success" style="cursor:pointer;" v-on:click="addMoreItem">--}}
                                                        {{--                                                        <i class="far fa-plus-square"></i>--}}
                                                        {{--                                                    </a>--}}
                                                        Opsi
                                                    </th>
                                                    <th style="width: 5%">No</th>
                                                    <th style="width: 20%">Purchase Order</th>
                                                    <th style="width: 20%">Good receive</th>
                                                    <th style="width: 15%">Total</th>
                                                    <th style="width: 15%">No Invoice</th>
                                                    <th style="width: 20%">Deskripsi</th>
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
                                                        <input type="text" class="form-control" v-model="item.poCode" name="poCodes[]" readonly>
                                                    </td>
                                                    <td>
                                                        <input type="hidden" class="form-control" v-model="item.grId" name="grIds[]">
                                                        <input type="text" class="form-control" v-model="item.grCode" name="grCodes[]" readonly>
                                                    </td>
                                                    <td>
                                                        <vue-autonumeric :options="autonumericFormat" name="total[]" v-model="item.total"
                                                                         v-on:input="calculatePrice()"
                                                                         class="form-control" readonly></vue-autonumeric>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control" v-model="item.noInv" name="noInvs[]">
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control" v-model="item.desc" name="descs[]">
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div id="nongr-div" style="display: {{$displayDivNonGr}}">
                                        <h4 class="fw-normal">Daftar COA</h4>
                                        <hr />
                                        <div class="row">
                                            <table class="table table-bordered table-striped table-vcenter js-dataTable-full" id="table_data">
                                                <thead>
                                                <tr>
                                                    <th style="width: 5%">
                                                        <a class="btn btn-success" style="cursor:pointer;" v-on:click="addMoreItem2">
                                                            <i class="far fa-plus-square"></i>
                                                        </a>
                                                    </th>
                                                    <th style="width: 5%">No</th>
                                                    <th style="width: 20%">COA</th>
                                                    <th style="width: 15%">Nilai</th>
                                                    <th style="width: 20%">Deskripsi</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <tr v-for="(item, index) in itemList2">
                                                    <td>
                                                        <a class="btn btn-danger" style="cursor: pointer" v-on:click="removeItem2(item)">
                                                            <i class="far fa-minus-square"></i>
                                                        </a>
                                                    </td>
                                                    <td><span>@{{++index}}</span></td>
                                                    <td>
                                                        <select2 url="{{route('admin.coa.select')}}" v-model="item.coaId"
                                                                 name="coaIds[]" class="form-select form-control-select2" placeholder="Pilih COA">
                                                        </select2>
                                                    </td>
                                                    <td>
                                                        <vue-autonumeric :options="autonumericFormat" name="total[]" v-model="item.total"
                                                                         v-on:input="calculatePrice()"
                                                                         class="form-control"></vue-autonumeric>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control" v-model="item.desc" name="descs[]">
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
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
                ...@json($vueData), ...{
                    grandTotal: {{ $data->grandtotal }},
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
            },
            computed:{
            },
            methods: {
                calculatePrice(){
                    if(this.itemList.length > 0){
                        this.grandTotal = 0;
                        for(var i =0; i< this.itemList.length; i++){
                            var total = this.itemList[i].total;
                            this.grandTotal += total;
                        }
                    }
                    if(this.itemList2.length > 0){
                        this.grandTotal = 0;
                        for(var j =0; j< this.itemList2.length; j++){
                            var total = this.itemList2[j].total;
                            this.grandTotal += total;
                        }
                    }
                },
                resetData(){
                    this.itemList = [];
                    this.itemList2 = [];
                    this.grandTotal = 0;
                },
                addData(){
                    let reference = $('#reference_id').val();
                    if (reference === '') {
                        alert('Harus Pilih Good Receive Terlebih Dahulu!');
                    }
                    var supplierId = $("#supplier_id").val();
                    var itemIdIdx = this.itemList.length + 1;
                    var itemIdName = 'item'+ itemIdIdx;
                    var route = '{{route('admin.good_receive.getTotalforInvoice')}}?gr_id=' + reference;
                    console.log(route);
                    var isExist = false;
                    axios.get(route).then(response => {
                        for($i = 0; $i < this.itemList.length; $i++){
                            if(this.itemList[$i].grId == response.data.gr_id){
                                isExist = true;
                            }
                        }
                        console.log(response.data);
                        if(!isExist){
                            let itemIdx = this.itemList.length + 1;
                            var nData = {
                                index: 0,
                                no: itemIdx++,
                                itemId: itemIdName,

                                grId: response.data.gr_id,
                                grCode: response.data.gr_code,
                                poCode: response.data.po_code,
                                total: response.data.amount,
                                noInv: "",
                                desc: response.data.desc,
                            };
                            this.itemList.push(nData);
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
                    };
                    this.itemList.push(nItem);
                },
                removeItem(item) {
                    this.itemList = this.itemList.filter(function (x) { return x !== item; });
                },
                addMoreItem2(item) {
                    let itemIdx = this.itemList2.length + 1;
                    let nItem = {
                        index: itemIdx,
                        no: itemIdx++,
                        coaId: 0,
                        total: 0,
                        desc: "",
                    };
                    this.itemList2.push(nItem);
                },
                removeItem2(item) {
                    this.itemList2 = this.itemList2.filter(function (x) { return x !== item; });
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

        $('#date').datepicker({
            autoclose: true,
            todayHighlight: true,
            format: "dd-mm-yyyy"
        });

        $('#form_create_locker').submit(function(){
            $('#btn_submit').hide();
            $('#btn_cancel').hide();
            $('#btn_loading').show(200);
        })
        $('#supplier_id').select2({
            placeholder: {
                id: '-1',
                text: ' - Pilih Supplier - '
            },
            width: '100%',
            minimumInputLength: 0,
            ajax: {
                url: '{{ route('admin.supplier.select') }}',
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
        $('#reference_id').select2({
            placeholder: {
                id: '-1',
                text: ' - Pilih Good Receive - '
            },
            width: '100%',
            minimumInputLength: 0,
            ajax: {
                url: '{{ route('admin.good_receive.selectInvoice') }}',
                dataType: 'json',
                data: function (params) {
                    return {
                        search: $.trim(params.term),
                        supplierId: $('#supplier_id').val(),
                    };
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                }
            }
        });
        $('#type').on('change', function (e) {
            // var optionSelected = $("option:selected", this);
            var valueSelected = this.value;

            if(valueSelected === 'GR'){
                $("#gr-div").show();
                $("#nongr-div").hide();
            }
            else{
                $("#gr-div").hide();
                $("#nongr-div").show();
            }
            vueComponent.resetData();
        });
    </script>
@endsection
