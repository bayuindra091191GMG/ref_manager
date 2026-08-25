@extends('layouts.admin')

@section('title')
    <title>Buat Move In</title>
@endsection

@section('content')
    <!-- Hero -->
    <div class="bg-body-light">
        <div class="content content-full">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Buat Move In
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.inventory_in.index') }}">Daftar Move In</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Buat Move In
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
                                <form class="space-y-3" id="form_create_locker" method="POST" action="{{ route('admin.inventory_in.store') }}">
                                    @csrf
                                    <div class="row">
                                        <div class="col-12 text-end">
                                            <button id="btn_loading" type="button" class="btn btn-success" style="display: none;">
                                                <i class="spinner-border spinner-border-sm text-green" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </i>
                                            </button>
                                            <input type="submit" id="btn_submit" class="btn btn-success" value="Simpan"/>&nbsp;&nbsp;
                                            <a id="btn_cancel" href="{{ route('admin.inventory_in.index') }}" class="btn btn-danger">Batal</a>
                                            {{-- <a id="btn_cancel" href="{{ route('admin.inventory_in.create_cn') }}" class="btn btn-info">Chinese</a> --}}
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
                                        <label class="col-sm-3 col-form-label" for="name">Cabang</label>
                                        <div class="col-sm-6">
                                            <select id="warehouse_id" class="form-control form-control-select2 warehouse_select2" name="warehouse_id"></select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="name">Tanggal</label>
                                        <div class="col-sm-6">
                                            <input type="text" id="date" name="date" class="form-control" placeholder="Date" value="{{ old('date') }}"/>
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
                                                <th style="width: 5%">
                                                    <a class="btn btn-success" style="cursor:pointer;" v-on:click="addMoreItem">
                                                        <i class="fa fa-plus"></i>
                                                    </a>
                                                </th>
                                                <th class="text-center" style="width: 10%">No</th>
                                                <th>Nama Barang</th>
                                                <th style="width: 8%">Unit</th>
                                                <th class="text-center" style="width: 15%">Qty</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr v-for="(item, index) in itemList">
                                                <td class="text-center">
                                                    <a class="btn btn-danger" style="cursor: pointer" v-on:click="removeItem(item)">
                                                        <i class="fa fa-minus"></i>
                                                    </a>
                                                </td>
                                                <td class="text-center"><span>@{{index+1}}</span></td>
                                                <td>
                                                    <select2 :url="selectUrl"
                                                        v-model="item.itemId"
                                                             name="transfer_product[]" class="form-select form-control-select2" placeholder="Pilih Produk">
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
{{--                                                <td>--}}
{{--                                                    <select2 url="{{route('admin.uom.select')}}" v-model="item.unitId"--}}
{{--                                                             name="unitIds[]" class="form-select form-control-select2" placeholder="Pilih Produk">--}}
{{--                                                    </select2>--}}
{{--                                                </td>--}}
                                                <td>
                                                    <vue-autonumeric :options="autonumericFormat" name="transfer_qty[]" v-model="item.qty" class="form-control" v-on:input="calculateTotalQty()"></vue-autonumeric>
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
                    maximumValue: '9999999999999999',
                    digitGroupSeparator: '.',
                    decimalCharacter: ',',
                    decimalPlaces: 4,
                    modifyValueOnWheel: false,
                    unformatOnSubmit: true,
                    allowDecimalPadding: true,
                    default: 0,
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
                addMoreItem(item) {
                    let itemIdx = this.itemList.length + 1;
                    let nItem = {
                        index: itemIdx,
                        no: itemIdx++,
                        itemId: 0,
                        qty: 0,
                        url: '{{ route('admin.item.unit.select') }}?id=',
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
            },
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
        });
    </script>
@endsection
