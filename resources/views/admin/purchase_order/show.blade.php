@extends('layouts.admin')

@section('title')
    <title>Detil Purchase Order</title>
@endsection

@section('content')
    <!-- Hero -->
    <div class="bg-body-light print_hide">
        <div class="content content-full">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Detil Purchase Order
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.purchase_order.index') }}">Daftar Purchase Order</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Detil Purchase Order
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
                                <div class="space-y-3">
                                    <div class="row print_hide">
                                        <div class="col-sm-4 col-12">
                                            <a href="{{ route('admin.purchase_order.index') }}" class="btn btn-dark">
                                                <i class="fa fa-arrow-left"></i>
                                            </a>
                                        </div>
                                        <div class="col-sm-8 col-12 text-end">
{{--                                            <button type="button" id="btn_print" class="btn btn-outline-info">Print</button>--}}
                                            <a id="btn_cancel" href="{{ route('admin.purchase_order.show', ['id' => $data->id]) }}?type=print" class="btn btn-outline-info">Print</a>
                                        </div>
                                    </div>
                                    <div class="row print_show text-center" style="display: none;">
                                        <h4>Purchase Order</h4>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="reference_id">Referensi Purchase Request</label>
                                        <div class="col-sm-6">
                                            <select class="form-select" id="reference_id" name="reference_id" disabled>
                                                @if(!empty($data->reference_id))
                                                    <option value="{{$data->reference_id}}">{{$data->reference_code}}</option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="name">Kode</label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control" placeholder="Name" value="{{$data->code}}" readonly/>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="supplier_id">Supplier</label>
                                        <div class="col-sm-6">
                                            <input type="hidden" class="form-control" name="supplier_id" value="{{$supplierId}}"/>
                                            <input type="text" class="form-control" value="{{$supplierName}}" readonly/>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="warehouse_id">Gudang Penerima</label>
                                        <div class="col-sm-6">
                                            <input type="hidden" class="form-control" name="warehouse_id" value="{{$warehouseId}}"/>
                                            <input type="text" class="form-control" value="{{$warehouseName}}" readonly/>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <label class="col-form-label" for="description">Deskripsi Pr</label>
                                        </div>
                                        <div class="col-sm-6">
                                            <textarea id="description" name="description" class="form-control" rows="4" maxlength="150" placeholder="Description" disabled>{{ $prDescription }}</textarea>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="transaction_date">Tanggal Dibutuhkan</label>
                                        <div class="col-sm-6">
                                            <input type="text" id="transaction_date" name="transaction_date" class="form-control" placeholder="Tanggal dibutuhkan"
                                                   value="{{ \Carbon\Carbon::parse($data->transaction_date)->format('d-m-Y') }}" readonly/>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <label class="col-form-label" for="description">Deskripsi</label>
                                        </div>
                                        <div class="col-sm-6">
                                            <textarea id="description" name="description" class="form-control" rows="4" maxlength="150" placeholder="Description" disabled>{{ $data->description }}</textarea>
                                        </div>
                                    </div>

                                    <h4 class="fw-normal">Daftar Barang</h4>
                                    <div class="row" v-if="qty_error">
                                        <label class="col-sm-10 col-form-label" style="color: red">Jumlah Item melebihi pesanan PR!</label>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="table-responsive w-100">
                                                <table class="table table-bordered table-striped table-vcenter js-dataTable-full" id="table_data" style="width: 1800px">
                                                    <thead>
                                                    <tr>
                                                        {{--                                                <th style="width: 5%">--}}
                                                        {{--                                                    <a class="btn btn-success" style="cursor:pointer;" v-on:click="addMoreItem">--}}
                                                        {{--                                                        <i class="far fa-plus-square"></i>--}}
                                                        {{--                                                    </a>--}}
                                                        {{--                                                </th>--}}
                                                        <th style="width: 30px">No</th>
                                                        <th style="width: 150px">Nama Produk</th>
                                                        <th style="width: 100px">Unit</th>
                                                        <th style="width: 80px">Qty PR</th>
                                                        <th style="width: 80px">Qty</th>
                                                        <th style="width: 100px">Harga</th>
                                                        <th style="width: 130px">Total</th>
{{--                                                        <th style="width: 100px">Disc1 %</th>--}}
                                                        <th style="width: 100px">Disc1 Rp</th>
{{--                                                        <th style="width: 100px">Disc2 %</th>--}}
                                                        <th style="width: 100px">Disc2 Rp</th>
                                                        <th style="width: 130px">Sub Total</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <tr v-for="(item, index) in itemList">
                                                        {{--                                                <td>--}}
                                                        {{--                                                    <a class="btn btn-danger" style="cursor: pointer" v-on:click="removeItem(item)">--}}
                                                        {{--                                                        <i class="far fa-minus-square"></i>--}}
                                                        {{--                                                    </a>--}}
                                                        {{--                                                </td>--}}
                                                        <td><span>@{{++index}}</span></td>
                                                        <td>
                                                            <select2 url="{{route('admin.item.select')}}" v-model="item.itemId"
                                                                     name="itemIds[]" class="form-select form-control-select2" placeholder="Pilih Produk" disabled>
                                                            </select2>
                                                        </td>
                                                        <td>
                                                            <select2 url="{{route('admin.uom.select')}}" v-model="item.unitId"
                                                                     name="unitIds[]" class="form-select form-control-select2" placeholder="Pilih Produk" disabled>
                                                            </select2>
                                                        </td>
                                                        <td>
                                                            <input type="text" name="qtyPrs[]" v-model="item.qtyPr" class="form-control" readonly />
                                                        </td>
                                                        <td>
                                                            <vue-autonumeric :options="autonumericFormat" name="qtys[]" class="form-control" v-model="item.qty"
                                                                             v-on:input="checkQty(item, index)" readonly></vue-autonumeric>
                                                        </td>
                                                        <td>
                                                            <vue-autonumeric :options="autonumericFormat" name="prices[]" class="form-control" v-model="item.price"
                                                                             v-on:input="calculatePrice()" readonly></vue-autonumeric>
                                                        </td>
                                                        <td>
                                                            <vue-autonumeric :options="autonumericFormat" name="totals[]" class="form-control"
                                                                v-model="item.total" readonly></vue-autonumeric>
                                                        </td>
{{--                                                        <td>--}}
{{--                                                            <vue-autonumeric :options="autonumericFormat" name="disc1_percents[]" class="form-control" v-model="item.disc1" readonly></vue-autonumeric>--}}
{{--                                                        </td>--}}
                                                        <td>
                                                            <vue-autonumeric :options="autonumericFormat" name="disc1_values[]" class="form-control" v-model="item.disc1Value" readonly></vue-autonumeric>
                                                        </td>
{{--                                                        <td>--}}
{{--                                                            <vue-autonumeric :options="autonumericFormat" name="disc2_percents[]" class="form-control" v-model="item.disc2" readonly></vue-autonumeric>--}}
{{--                                                        </td>--}}
                                                        <td>
                                                            <vue-autonumeric :options="autonumericFormat" name="disc2_values[]" class="form-control" v-model="item.disc2Value" readonly></vue-autonumeric>
                                                        </td>
                                                        <td>
                                                            <vue-autonumeric :options="autonumericFormat" name="subtotals[]" class="form-control"
                                                                v-model="item.subtotal" readonly></vue-autonumeric>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <label class="col-sm-2 col-form-label" for="status">Subtotal </label>
                                        <div class="col-sm-8">
                                            <vue-autonumeric :options="autonumericFormat" name="subtotal" class="form-control" v-model="subTotal" readonly></vue-autonumeric>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <label class="col-sm-2 col-form-label" for="status">Jangka Waktu Pembayaran </label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <vue-autonumeric :options="autonumericFormat" name="top" class="form-control" value="{{$data->top}}" readonly></vue-autonumeric>
                                                <span class="input-group-text" id="basic-addon2">Hari</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <label class="col-sm-2 col-form-label" for="status">Diskon 1</label>
                                        <div class="col-sm-2">
                                            <div class="input-group">
                                                <vue-autonumeric :options="autonumericFormat" name="disc1_percent" class="form-control" v-model="disc1"  readonly></vue-autonumeric>
                                                <span class="input-group-text" id="basic-addon2">%</span>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <vue-autonumeric :options="autonumericFormat" name="disc1_value" class="form-control" v-model="disc1Value"  readonly></vue-autonumeric>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <label class="col-sm-2 col-form-label" for="status">Diskon 2</label>
                                        <div class="col-sm-2">
                                            <div class="input-group">
                                                <vue-autonumeric :options="autonumericFormat" name="disc2_percent" class="form-control" v-model="disc2"  readonly></vue-autonumeric>
                                                <span class="input-group-text" id="basic-addon2">%</span>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <vue-autonumeric :options="autonumericFormat" name="disc2_value" class="form-control" v-model="disc2Value"  readonly></vue-autonumeric>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <label class="col-sm-2 col-form-label" for="status">PPH23 </label>
                                        <div class="col-sm-2">
                                            <div class="input-group">
                                                <vue-autonumeric :options="autonumericFormat" name="ppn_percent" class="form-control" v-model="ppnPercent"  readonly></vue-autonumeric>
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
                                </div>
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
    <style>
        @media print {
            .print_show{
                display: block;
            }

            .print_hide {
                display: none;
            }
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
                ...@json($vueData), ...{
                    can_submit: 1,
                    qty_error: false,
                    branchName: '',
                    isLoading: false,

                    subTotal: {{$data->subtotal}},
                    ppnPercent: {{$data->ppn_percent}},
                    ppnValue: {{$data->ppn_value}},
                    disc1: {{$data->disc1_percent}},
                    disc1Value: {{$data->disc1_value}},
                    disc2: {{$data->disc2_percent}},
                    disc2Value: {{$data->disc2_value}},
                    grandTotal: {{$data->grandtotal}},

                    autonumericFormat: {
                        minimumValue: '-9999999999',
                        maximumValue: '9999999999',
                        digitGroupSeparator: '.',
                        decimalCharacter: ',',
                        decimalPlaces: 2,
                        modifyValueOnWheel: false,
                        allowDecimalPadding: false,
                    },
                },
            },
            computed:{
            },
            methods: {
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

        $(document).on('click', '#btn_print', function(){
            $('.print_show').show();
            window.print();
        });
        window.onafterprint = function(){
            $('.print_show').hide();
        }
    </script>
@endsection
