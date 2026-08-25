@extends('layouts.admin')

@section('title')
    <title>Detil Invoice Manual</title>
@endsection

@section('content')
    <!-- Hero -->
    <div class="bg-body-light print_hide">
        <div class="content content-full">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Detil Invoice Manual
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
                            Detil Invoice Manual
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
                                            <a id="btn_cancel" href="{{ route('admin.invoice_manual.show', ['id' => $data->id]) }}?type=print" class="btn btn-outline-info">Print</a>
                                            <a id="btn_cancel" href="{{ route('admin.invoice_manual.index') }}" class="btn btn-outline-danger">Back</a>
                                        </div>
                                    </div>
                                    <div class="row print_show text-center" style="display: none;">
                                        <h4>Invoice Manual</h4>
                                    </div>
                                    <div class="row print_hide">
                                        <div class="col-12">
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
                                        <div class="col-sm-3">
                                            <label class="col-form-label" for="description">Deskripsi</label>
                                        </div>
                                        <div class="col-sm-6">
                                            <textarea id="description" name="description" class="form-control" rows="4" maxlength="150" placeholder="Description" disabled>{{ $data->description }}</textarea>
                                        </div>
                                    </div>

                                    <h4 class="fw-normal">Daftar Barang</h4>
                                    <hr />
                                    <div class="row">
                                        <table class="table table-bordered table-striped table-vcenter js-dataTable-full" id="table_data">
                                            <thead>
                                            <tr>
                                                <th style="width: 5%">No</th>
                                                <th style="width: 15%">Nama Produk</th>
                                                <th style="width: 8%">Unit</th>
                                                <th style="width: 8%">Qty</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr v-for="(item, index) in itemList">
                                                <td><span>@{{++index}}</span></td>
                                                <td>
                                                    <select2 url="{{route('admin.item.select')}}" v-model="item.itemId"
                                                             name="itemIds[]" class="form-select form-control-select2" placeholder="Pilih Produk" disabled>
                                                    </select2>
                                                </td>
                                                <td>
{{--                                                    <select2 url="{{route('admin.uom.select')}}" v-model="item.unitId"--}}
{{--                                                             name="unitIds[]" class="form-select form-control-select2" placeholder="Pilih Produk" disabled>--}}
{{--                                                    </select2>--}}
                                                    PCS
                                                </td>
                                                <td>
                                                    <vue-autonumeric :options="autonumericFormat" name="qty[]" v-model="item.qty" class="form-control"
                                                                     v-on:input="calculateTotalQty()" readonly></vue-autonumeric>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
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
                    can_submit: 1,
                    total_qty: 0,
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
