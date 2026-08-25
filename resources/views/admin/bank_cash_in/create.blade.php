@extends('layouts.admin')

@section('title')
    <title>Buat Bank Cash In</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Buat Bank Cash In
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.bank_cash_in.index') }}">Daftar Bank Cash In</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Buat Bank Cash In
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
                                <form class="space-y-3" id="form_create_locker" method="POST" action="{{ route('admin.bank_cash_in.store') }}">
                                    @csrf
                                    <div class="row">
                                        <div class="col-12 text-end">
                                            <button id="btn_loading" type="button" class="btn btn-outline-success" style="display: none;">
                                                <i class="spinner-border spinner-border-sm text-green" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </i>
                                            </button>
                                            <input type="submit" id="btn_submit" class="btn btn-outline-success" value="Submit"/>&nbsp;&nbsp;
                                            <a id="btn_cancel" href="{{ route('admin.bank_cash_in.index') }}" class="btn btn-outline-danger">Back</a>
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
                                        <label class="col-sm-3 col-form-label" for="name">Tanggal</label>
                                        <div class="col-sm-6">
                                            <input type="text" id="receive_date" name="receive_date" class="form-control" placeholder="Tanggal" value="{{ old('date') }}"/>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="name">COA Cash / Bank *</label>
                                        <div class="col-sm-6">
                                            <select id="coa_bank_id" class="form-control form-control-select2" name="coa_bank_id"></select>
                                        </div>
                                    </div>
{{--                                    <div class="row">--}}
{{--                                        <label class="col-sm-3 col-form-label" for="name">Mata Uang</label>--}}
{{--                                        <div class="col-sm-6">--}}
{{--                                            <select id="currency_id" class="form-control form-control-select2" name="currency_id"></select>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <label class="col-form-label" for="description">Deskripsi</label>
                                        </div>
                                        <div class="col-sm-6">
                                            <textarea id="description" name="description" class="form-control" rows="4" maxlength="150" placeholder="Description">{{ old('description') }}</textarea>
                                        </div>
                                    </div>

                                    <h4 class="fw-normal">Daftar COA</h4>
                                    <hr />
                                    <div class="row">
                                        <table class="table table-bordered table-striped table-vcenter js-dataTable-full" id="table_data">
                                            <thead>
                                            <tr>
                                                <th style="width: 5%">
                                                    <a class="btn btn-success" style="cursor:pointer;" v-on:click="addMoreItem">
                                                        <i class="far fa-plus-square"></i>
                                                    </a>
                                                </th>
                                                <th style="width: 5%">No</th>
{{--                                                <th style="width: 8%">Debit/Kredit</th>--}}
                                                <th style="width: 15%">Nama COA</th>
                                                <th style="width: 8%">Debit</th>
                                                <th style="width: 8%">Kredit</th>
                                                <th style="width: 8%">Deskripsi</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr v-for="(item, index) in itemList" :key="item.id">
                                                <td>
                                                    <a class="btn btn-danger" style="cursor: pointer" v-on:click="removeItem(item)">
                                                        <i class="far fa-minus-square"></i>
                                                    </a>
                                                </td>
                                                <td><span>@{{ index + 1 }}</span></td>
{{--                                                <td>--}}
{{--                                                    <select class="form-control" name="debitKredit[]" v-model="item.debitKredit">--}}
{{--                                                        <option value="D">Debit</option>--}}
{{--                                                        <option value="K">Kredit</option>--}}
{{--                                                    </select>--}}
{{--                                                </td>--}}
                                                <td>
                                                    <select2 url="{{route('admin.coa.select')}}" v-model="item.coaId"
                                                             name="coaIds[]" class="form-select form-control-select2" placeholder="Pilih COA">
                                                    </select2>
                                                </td>
                                                <td>
                                                    <vue-autonumeric :options="autonumericFormat" name="debits[]" class="form-control" v-on:input="calculateTotal()" v-if="item.credit != 0" :value="0" readonly></vue-autonumeric>
                                                    <vue-autonumeric :options="autonumericFormat" name="debits[]" v-model="item.debit" class="form-control" v-on:input="calculateTotal()" v-else></vue-autonumeric>
                                                </td>
                                                <td>
                                                    <vue-autonumeric :options="autonumericFormat" name="credits[]" class="form-control" v-on:input="calculateTotal()" v-if="item.debit != 0" :value="0" readonly></vue-autonumeric>
                                                    <vue-autonumeric :options="autonumericFormat" name="credits[]" v-model="item.credit" class="form-control" v-on:input="calculateTotal()" v-else></vue-autonumeric>
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control" name="desc[]" v-model="item.desc">
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="row">
                                        <label class="col-sm-4 col-form-label" for="status">Total Debit</label>
                                        <label class="col-sm-4 col-form-label" for="status">Total Kredit</label>
                                        <label class="col-sm-4 col-form-label" for="status">Grand Total</label>
                                        <div class="col-sm-4">
                                            <vue-autonumeric :options="autonumericFormat" class="form-control" v-model="total_debit" readonly></vue-autonumeric>
                                        </div>
                                        <div class="col-sm-4">
                                            <vue-autonumeric :options="autonumericFormat" class="form-control" v-model="total_kredit" readonly></vue-autonumeric>
                                        </div>
                                        <div class="col-sm-4">
                                            <vue-autonumeric :options="autonumericFormat" name="total_price" class="form-control" v-model="grandTotal" readonly></vue-autonumeric>
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
                grandTotal: 0,
                total_debit: 0,
                total_kredit: 0,

                itemList: [],
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
                addMoreItem(item) {
                    let itemIdx = this.itemList.length + 1;
                    let nItem = {
                        id: makeid(10),
                        index: itemIdx,
                        no: itemIdx++,
                        debitKredit: 'D',
                        coaId: 0,
                        amount: 0,
                        debit: 0,
                        credit: 0,
                        desc: "",
                    };
                    this.itemList.push(nItem);
                },
                removeItem(item) {
                    this.itemList = this.itemList.filter(function (x) { return x !== item; });
                    this.calculateTotal();
                },
                calculateTotal() {
                    let tempTotal = 0;
                    let tempTotalD = 0;
                    let tempTotalK = 0;
                    for(var i = 0; i < this.itemList.length; i++){
                        tempTotalD += this.itemList[i].debit;
                        tempTotalK += this.itemList[i].credit;
                    }
                    tempTotal = tempTotalK - tempTotalD;
                    this.total_debit = tempTotalD;
                    this.total_kredit = tempTotalK;
                    this.grandTotal = tempTotal;
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

        $('#receive_date').datepicker({
            autoclose: true,
            todayHighlight: true,
            format: "dd-mm-yyyy"
        });
        $( '#receive_date' ).datepicker( 'setDate', today );

        $('#form_create_locker').submit(function(){
            $('#btn_submit').hide();
            $('#btn_cancel').hide();
            $('#btn_loading').show(200);
        })
        $('#employee_id').select2({
            placeholder: {
                id: '-1',
                text: ' - Pilih Karyawan - '
            },
            width: '100%',
            minimumInputLength: 0,
            ajax: {
                url: '{{ route('admin.employee.select') }}',
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
        $('#department_id').select2({
            placeholder: {
                id: '-1',
                text: ' - Pilih Department - '
            },
            width: '100%',
            minimumInputLength: 0,
            ajax: {
                url: '{{ route('admin.department.select') }}',
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
        $('#coa_bank_id').select2({
            placeholder: {
                id: '-1',
                text: ' - Pilih COA Bank - '
            },
            width: '100%',
            minimumInputLength: 0,
            ajax: {
                url: '{{ route('admin.coa.select') }}',
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
        $('#currency_id').select2({
            placeholder: {
                id: '-1',
                text: ' - Pilih Mata Uang - '
            },
            width: '100%',
            minimumInputLength: 0,
            ajax: {
                url: '{{ route('admin.currency.select') }}',
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
