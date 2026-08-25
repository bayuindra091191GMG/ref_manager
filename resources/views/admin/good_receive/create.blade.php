@extends('layouts.admin')

@section('title')
    <title>Buat Good Receive</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Buat Good Receive
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.good_receive.index') }}">Good Receive List</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Buat Good Receive
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
                                <form class="space-y-3" id="form_create_locker" method="POST" action="{{ route('admin.good_receive.store') }}">
                                    @csrf
                                    <div class="row">
                                        <div class="col-12 text-end">
                                            <button id="btn_loading" type="button" class="btn btn-outline-success" style="display: none;">
                                                <i class="spinner-border spinner-border-sm text-green" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </i>
                                            </button>
                                            <input type="submit" id="btn_submit" class="btn btn-outline-success" value="Simpan" :disabled="can_submit == 0"/>&nbsp;&nbsp;
                                            <a id="btn_cancel" href="{{ route('admin.good_receive.index') }}" class="btn btn-outline-danger">Batal</a>
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
                                    <div class="row mb-5">
                                        <label class="col-sm-3 col-form-label" for="branch_id">Referensi Purchase Order *</label>
                                        <div class="col-sm-6">
                                            <select class="form-select" id="reference_id" name="po_id">
                                                @if($isPo == 1)
                                                    <option value="{{$referenceId}}">{{$referenceCode}}</option>
                                                @endif
                                            </select>
                                        </div>
                                        <div class="col-sm-3">
                                            <button type="button" id="btn_load_data" class="btn btn-outline-info" v-on:click="loadData()">Ambil Data</button>
                                        </div>
                                    </div>
                                    @if($isPo == 1)
                                        @php($display = "block")
                                    @else
                                        @php($display = "none")
                                    @endif
                                    <div class="space-y-3" style="display: {{$display}}">
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label" for="name">Kode</label>
                                            <div class="col-sm-6">
                                                <input type="text" class="form-control" placeholder="Name" value="AUTO" readonly/>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label" for="name">Kode PR</label>
                                            <div class="col-sm-6">
                                                <input type="hidden" class="form-control" name="pr_id" value="{{$prId}}"/>
                                                <input type="text" class="form-control" name="pr_code" value="{{$prCode}}" readonly/>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label" for="name">Supplier</label>
                                            <div class="col-sm-6">
                                                <input type="hidden" class="form-control" name="supplier_id" value="{{$supplierId}}"/>
                                                <input type="text" class="form-control" value="{{$supplierName}}" readonly/>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label" for="name">Gudang Penerima</label>
                                            <div class="col-sm-6">
                                                <input type="hidden" class="form-control" name="warehouse_id" value="{{$warehouseId}}"/>
                                                <input type="text" class="form-control" value="{{$warehouseName}}" readonly/>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-3">
                                                <label class="col-form-label" for="description">Deskripsi Po</label>
                                            </div>
                                            <div class="col-sm-6">
                                                <textarea id="description" name="description" class="form-control" rows="4" maxlength="150" placeholder="Description" disabled>{{ $poDescription }}</textarea>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label" for="name">Tanggal Diterima</label>
                                            <div class="col-sm-6">
                                                <input type="text" id="date" name="date" class="form-control" placeholder="Name" value="{{ old('date') }}"/>
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
                                        <div class="row" v-if="qty_error">
                                            <label class="col-sm-10 col-form-label" style="color: red">Jumlah Item melebihi pesanan PO!</label>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div class="table-responsive w-100">
                                                    <table class="table table-bordered table-striped table-vcenter js-dataTable-full" id="table_data">
                                                        <thead>
                                                        <tr>
                                                            <th style="width: 10px">No</th>
                                                            <th style="width: 150px">Nama Produk</th>
                                                            <th style="width: 50px">Unit</th>
                                                            <th style="width: 20px">Qty PO</th>
                                                            <th style="width: 30px">Qty Alokasi</th>
                                                            <th style="width: 20px">Qty</th>
                                                            <th style="width: 70px">Harga</th>
                                                            <th style="width: 70px">Total</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        <tr v-for="(item, index) in itemList">
{{--                                                            <td>--}}
{{--                                                                <a class="btn btn-danger" style="cursor: pointer" v-on:click="removeItem(item)">--}}
{{--                                                                    <i class="far fa-minus-square"></i>--}}
{{--                                                                </a>--}}
{{--                                                            </td>--}}
                                                            <td><span>@{{++index}}</span></td>
                                                            <td>
                                                                <input type="hidden" class="form-control" name="itemIds[]" v-model="item.itemId"/>
                                                                <input type="text" class="form-control" v-model="item.itemName" readonly/>
                                                            </td>
                                                            <td>
                                                                <input type="hidden" class="form-control" name="unitIds[]" v-model="item.unitId"/>
                                                                <input type="text" class="form-control" v-model="item.unitName" readonly/>
                                                            </td>
                                                            <td>
                                                                <input type="text" name="maxQtys[]" v-model="item.qtyPo" class="form-control" readonly />
                                                            </td>
                                                            <td>
                                                                <input type="text" v-model="item.qtyGr" class="form-control" readonly />
                                                            </td>
                                                            <td>
                                                                <vue-autonumeric :options="autonumericFormat" name="qtys[]" class="form-control" v-model="item.qty"
                                                                                 v-on:input="checkQty(item, index)"></vue-autonumeric>
                                                            </td>
                                                            <td>
                                                                <vue-autonumeric :options="autonumericFormat" name="prices[]" class="form-control" v-model="item.price" readonly></vue-autonumeric>
                                                            </td>
                                                            <td>
                                                                <vue-autonumeric :options="autonumericFormat" name="totals[]" class="form-control" v-model="item.subtotal" readonly></vue-autonumeric>
                                                            </td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
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
                ...@json($vueData),...{
                    can_submit: 1,
                    qty_error: false,
                    branchName: '',
                    isLoading: false,

                    autonumericFormat: {
                        minimumValue: '-9999999999999',
                        maximumValue: '9999999999999',
                        digitGroupSeparator: '.',
                        decimalCharacter: ',',
                        decimalPlaces: 2,
                        modifyValueOnWheel: false,
                        allowDecimalPadding: false,
                    },
                }
            },
            computed:{
            },
            methods: {
                checkQty(item, index) {
                    var qty = item.qty;
                    var maxQty = item.qtyPo - item.qtyGr;

                    if (parseInt(qty) > parseInt(maxQty)) {
                        this.qty_error = true;
                        this.can_submit = 0;
                    }
                    else {
                        this.can_submit = 1;
                        this.qty_error = false;

                        this.calculatePrice();
                    }
                },
                loadData : async () => {
                    let reference = $('#reference_id').val();
                    if (reference === '') {
                        alert('Harus Pilih Purchase Order Terlebih Dahulu!');
                    }

                    window.location.href = "{{route('admin.good_receive.create')}}?reference_id=" + reference;
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
                calculateTotalQty() {
                    debugger;
                    let tempTotal = 0;
                    for(var i = 0; i < this.itemList.length; i++){
                        debugger;
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
        $('#reference_id').select2({
            placeholder: {
                id: '-1',
                text: ' - Pilih Purchase Order - '
            },
            width: '100%',
            minimumInputLength: 0,
            ajax: {
                url: '{{ route('admin.purchase_order.select') }}',
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
