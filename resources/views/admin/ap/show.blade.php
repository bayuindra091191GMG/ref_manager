@extends('layouts.admin')

@section('title')
    <title>Detil Acc Payment</title>
@endsection

@section('content')
    <!-- Hero -->
    <div class="bg-body-light print_hide">
        <div class="content content-full">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Detil Acc Payment
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.ap.index') }}">Acc Payment List</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Detil Acc Payment
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
                                        <div class="col-12 text-end">
                                            {{--                                            <button type="button" id="btn_print" class="btn btn-outline-info">Print</button>--}}
                                            <a id="btn_cancel" href="{{ route('admin.ap.show', ['id' => $data->id]) }}?type=print" class="btn btn-outline-info">Print</a>
                                            <a id="btn_cancel" href="{{ route('admin.ap.index') }}" class="btn btn-outline-danger">Back</a>
                                        </div>
                                    </div>
                                    <div class="row print_show text-center" style="display: none;">
                                        <h4>Acc Payment</h4>
                                    </div>
                                    <div class="row print_hide">
                                        <div class="col-12">
                                            <span style="color: red;">Fields with asterisk (*) must be filled!</span>
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
                                            <select id="supplier_id" class="form-control form-control-select2" name="supplier_id" disabled>
                                                <option value="{{$data->supplier_id}}">{{$data->supplier->name}}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="name">Tanggal</label>
                                        <div class="col-sm-6">
                                            <input type="text" id="required_date" name="required_date" class="form-control" placeholder="Tanggal dibutuhkan"
                                                   value="{{ \Carbon\Carbon::parse($data->date)->format('d-m-Y') }}" readonly/>
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

                                        <div id="gr-div">
                                            <h4 class="fw-normal">Daftar</h4>
                                            <hr />
                                            <div class="row">
                                                <table class="table table-bordered table-striped table-vcenter js-dataTable-full" id="table_data">
                                                    <thead>
                                                    <tr>
                                                        <th style="width: 5%">No</th>
                                                        <th style="width: 20%">Supp Invoice</th>
                                                        <th style="width: 15%">Total</th>
                                                        <th style="width: 20%">Deskripsi</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <tr v-for="(item, index) in itemList">
                                                        <td><span>@{{++index}}</span></td>
                                                        <td>
                                                            <input type="hidden" class="form-control" v-model="item.suppInvId" name="suppInvIds[]">
                                                            <input type="text" class="form-control" v-model="item.suppInvCode" name="suppInvCodes[]" readonly>
                                                        </td>
                                                        <td>
                                                            <vue-autonumeric :options="autonumericFormat" name="total[]" v-model="item.total"
                                                                             v-on:input="calculatePrice()"
                                                                             class="form-control" readonly></vue-autonumeric>
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control" v-model="item.desc" name="descs[]" readonly>
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
                                                        <th style="width: 5%">No</th>
                                                        <th style="width: 20%">COA</th>
                                                        <th style="width: 15%">Debit</th>
                                                        <th style="width: 15%">Kredit</th>
                                                        <th style="width: 20%">Deskripsi</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <tr v-for="(item, index) in itemList2">
                                                        <td><span>@{{++index}}</span></td>
                                                        <td>
                                                            <select2 url="{{route('admin.coa.select')}}" v-model="item.coaId" disabled
                                                                     name="coaIds[]" class="form-select form-control-select2" placeholder="Pilih COA">
                                                            </select2>
                                                        </td>
                                                        <td>
                                                            <vue-autonumeric :options="autonumericFormat" name="debits[]" v-model="item.debit" :value="item.debit"
                                                                             readonly
                                                                             class="form-control"></vue-autonumeric>
                                                        </td>
                                                        <td>
                                                            <vue-autonumeric :options="autonumericFormat" name="credits[]" v-model="item.credit" :value="item.credit"
                                                                             readonly
                                                                             class="form-control"></vue-autonumeric>
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control" v-model="item.desc" name="descs[]" readonly>
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
                        maximumValue: '9999999999999999',
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
