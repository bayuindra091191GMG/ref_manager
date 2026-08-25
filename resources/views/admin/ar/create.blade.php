@extends('layouts.admin')

@section('title')
    <title>Buat Acc Receive</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Buat Acc Receive
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.ar.index') }}">Acc Receive List</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Buat Acc Receive
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
                                <form class="space-y-3" id="form_create_locker" method="POST" action="{{ route('admin.ar.store') }}">
                                    @csrf
                                    <div class="row">
                                        <div class="col-12 text-end">
                                            <button id="btn_loading" type="button" class="btn btn-outline-success" style="display: none;">
                                                <i class="spinner-border spinner-border-sm text-green" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </i>
                                            </button>
                                            <input type="submit" id="btn_submit" class="btn btn-outline-success" value="Submit"/>&nbsp;&nbsp;
                                            <a id="btn_cancel" href="{{ route('admin.ar.index') }}" class="btn btn-outline-danger">Cancel</a>
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
                                        <label class="col-sm-3 col-form-label" for="name">Tanggal</label>
                                        <div class="col-sm-6">
                                            <input type="text" id="date" name="date" class="form-control" placeholder="Tanggal" value="{{ old('date') }}"/>
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
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="name">COA Piutang *</label>
                                        <div class="col-sm-6">
                                            <select id="coa_id" class="form-control form-control-select2" name="coa_id"></select>
                                        </div>
                                    </div>
{{--                                    <div class="row">--}}
{{--                                        <label class="col-sm-3 col-form-label" for="name">Tipe</label>--}}
{{--                                        <div class="col-sm-6">--}}
{{--                                            <select class="form-select" id="type" name="type">--}}
{{--                                                <option value="GR">GOOD RECEIVE</option>--}}
{{--                                                <option value="NONGR">NON GOOD RECEIVE</option>--}}
{{--                                            </select>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}

                                    <div id="gr-div">
                                        <div class="row mt-5">
                                            <label class="col-sm-3 col-form-label" for="branch_id">Referensi Invoice</label>
                                            <div class="col-sm-6">
                                                <select class="form-select" id="reference_id" name="reference_id"></select>
                                            </div>
                                            <div class="col-sm-3">
                                                <button type="button" id="btn_load_data" class="btn btn-outline-info" v-on:click="addData()">Tambah Data</button>
                                            </div>
                                        </div>

                                        <h4 class="fw-normal">Daftar Invoice</h4>
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
                                                    <th style="width: 20%">Invoice</th>
                                                    <th style="width: 15%">Nominal</th>
                                                    <th style="width: 15%">Total Pembayaran</th>
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
                                                        <input type="hidden" class="form-control" v-model="item.salesInvId" name="salesInvIds[]">
                                                        <input type="text" class="form-control" v-model="item.salesInvCode" name="salesInvCodes[]" readonly>
                                                    </td>
                                                    <td>
                                                        <vue-autonumeric :options="autonumericFormat" name="nominal[]" :value="item.nominal"
                                                                         class="form-control" readonly></vue-autonumeric>
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
                                    <div id="nongr-div">
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
                                                    <th style="width: 15%">Debit</th>
{{--                                                    <th style="width: 15%">Kredit</th>--}}
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
                                                        <select2 url="{{route('admin.coa.selectBankCash')}}" v-model="item.coaId"
                                                                 name="coaIds[]" class="form-select form-control-select2" placeholder="Pilih COA">
                                                        </select2>
                                                    </td>
                                                    <td>
                                                        <vue-autonumeric :options="autonumericFormat" name="debits[]" v-model="item.debit"
                                                                         v-on:input="totalAmountAdjFunction('db', item)" :readonly="item.debitReadonly == 1"
                                                                         class="form-control"></vue-autonumeric>
                                                        <input type="hidden" name="credits[]" value="0">
                                                    </td>
{{--                                                    <td>--}}
{{--                                                        <vue-autonumeric :options="autonumericFormat" name="credits[]" v-model="item.credit"--}}
{{--                                                                         v-on:input="totalAmountAdjFunction('cr', item)" :readonly="item.creditReadonly == 1"--}}
{{--                                                                         class="form-control"></vue-autonumeric>--}}
{{--                                                    </td>--}}
                                                    <td>
                                                        <input type="text" class="form-control" v-model="item.desc" name="descCoas[]">
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <input type="hidden" name="advID[]" v-for="id in advIds" v-model="id">

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
                deposit: false,
                advIds: [],
                subTotal: 0,
                totalDeposit: 0,
                grandTotal: 0,
                itemList: [],
                itemList2: [],
                autonumericFormat: {
                    minimumValue: '0',
                    maximumValue: '9999999999999999',
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
                calculatePrice(){
                    this.subTotal = 0;
                    if(this.itemList.length > 0){
                        for(var i =0; i< this.itemList.length; i++){
                            var total = this.itemList[i].total;
                            this.subTotal += total;
                        }
                    }

                    this.grandTotal = this.subTotal - this.totalDeposit;
                    // if(this.itemList2.length > 0){
                    //     for(var j =0; j< this.itemList2.length; j++){
                    //         var total = this.itemList2[j].debit - this.itemList2[j].credit;
                    //         this.grandTotal += total;
                    //     }
                    // }
                },
                resetData(){
                    this.itemList = [];
                    this.itemList2 = [];
                    this.grandTotal = 0;
                },
                addData(){
                    let reference = $('#reference_id').val();
                    if (reference === '') {
                        alert('Harus Pilih Invoice Terlebih Dahulu!');
                    }
                    var supplierId = $("#customer_id").val();
                    var itemIdIdx = this.itemList.length + 1;
                    var itemIdName = 'item'+ itemIdIdx;
                    var route = '{{route('admin.invoice_manual.getTotalforAr')}}?salesInv_id=' + reference;
                    console.log(route);
                    var isExist = false;
                    axios.get(route).then(response => {
                        for($i = 0; $i < this.itemList.length; $i++){
                            if(this.itemList[$i].salesInvId == response.data.salesInvId){
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

                                salesInvId: response.data.invId,
                                salesInvCode: response.data.invCode,
                                nominal: response.data.amount,
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
                        salesInvId: 0,
                        customerId: $('#customer_id').val(),
                        total: 0,
                        noInv: "",
                        desc: "",
                    };
                    this.itemList.push(nItem);
                },
                removeItem(item) {
                    this.itemList = this.itemList.filter(function (x) { return x !== item; });
                    this.calculatePrice()
                },
                addMoreItem2(item) {
                    let itemIdx = this.itemList2.length + 1;
                    let nItem = {
                        index: itemIdx,
                        no: itemIdx++,
                        coaId: 0,
                        debit: 0,
                        credit: 0,
                        desc: "",
                    };
                    this.itemList2.push(nItem);
                },
                removeItem2(item) {
                    this.itemList2 = this.itemList2.filter(function (x) { return x !== item; });
                    this.calculatePrice()
                },
                totalAmountAdjFunction(type, unit) {
                    this.calculatePrice();

                    //debit or credit validation
                    debugger;
                    // if (type === "cr") {
                    //     if (unit.credit > 0) {
                    //         unit.debitReadonly = 1;
                    //         unit.creditReadonly = 0;
                    //         unit.debit = 0;
                    //     }
                    //     else {
                    //         unit.debitReadonly = 0;
                    //         unit.creditReadonly = 0;
                    //         unit.debit = 0;
                    //         unit.credit = 0;
                    //     }
                    //     debugger;
                    // }
                    // else {
                    //     if (unit.debit > 0) {
                    //         unit.debitReadonly = 0;
                    //         unit.creditReadonly = 1;
                    //         unit.credit = 0;
                    //     }
                    //     else {
                    //         unit.debitReadonly = 0;
                    //         unit.creditReadonly = 0;
                    //         unit.debit = 0;
                    //         unit.credit = 0;
                    //     }
                    //     debugger;
                    // }
                    debugger;
                },
                getCustomerAdvanceReceive(){
                    this.totalDeposit = 0;

                    var customerId = $("#customer_id").val();

                    var route = '{{route('admin.ar.getCustAdv')}}?cust_id=' + customerId;
                    axios.get(route).then(response => {
                        this.advIds = response.data.ids;
                        this.totalDeposit = response.data.custAdvReceive;

                        this.calculatePrice();
                    });
                },
            },
            watch: {
                deposit(){
                    if(this.deposit){
                        this.getCustomerAdvanceReceive();
                    }
                    else {
                        this.totalDeposit = 0;
                        this.advIds = [];
                        this.calculatePrice();
                    }
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

        $('#date').datepicker({
            autoclose: true,
            todayHighlight: true,
            format: "dd-mm-yyyy"
        });
        $( '#date' ).datepicker( 'setDate', today );

        $('#form_create_locker').submit(function(){
            $('#btn_submit').hide();
            $('#btn_cancel').hide();
            $('#btn_loading').show(200);
        })
        $('#customer_id').select2({
            placeholder: {
                id: '-1',
                text: ' - Pilih Customer - '
            },
            width: '100%',
            minimumInputLength: 0,
            ajax: {
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
        $('#customer_id').on('select2:select', function () {
            vueComponent.deposit = false;
            vueComponent.itemList = [];
        });

        $('#reference_id').select2({
            placeholder: {
                id: '-1',
                text: ' - Pilih Sales Invoice - '
            },
            width: '100%',
            minimumInputLength: 0,
            ajax: {
                url: '{{ route('admin.invoice_manual.selectAr') }}',
                dataType: 'json',
                data: function (params) {
                    return {
                        search: $.trim(params.term),
                        employeeId: $('#employee').val(),
                        partnerId: $('#partner').val(),
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
        $('#coa_id').select2({
            placeholder: {
                id: '-1',
                text: ' - Pilih COA Piutang - '
            },
            width: '100%',
            minimumInputLength: 0,
            ajax: {
                url: '{{ route('admin.coa.selectPiutang') }}',
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
