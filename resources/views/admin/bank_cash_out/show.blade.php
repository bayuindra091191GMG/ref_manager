@extends('layouts.admin')

@section('title')
    <title>Detil Bank Cash Out</title>
@endsection

@section('content')
    <!-- Hero -->
    <div class="bg-body-light print_hide">
        <div class="content content-full">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Detil Bank Cash Out
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.bank_cash_out.index') }}">Bank Cash Out List</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Detil Bank Cash Out
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
                                            @if ($data->status_id == 13)
                                            <button type="button" id="btn_unapprove" class="btn btn-outline-warning">Unapprove</button>&nbsp;&nbsp;&nbsp;
                                            @endif
                                            <a id="btn_cancel" href="{{ route('admin.bank_cash_out.show', ['id' => $data->id]) }}?type=print" class="btn btn-outline-info">Print</a>
                                            <a id="btn_cancel" href="{{ route('admin.bank_cash_out.index') }}" class="btn btn-outline-danger">Back</a>
                                        </div>
                                    </div>
                                    <div class="row print_show text-center" style="display: none;">
                                        <h4>Bank Cash Out</h4>
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
                                        <label class="col-sm-3 col-form-label" for="name">Tanggal</label>
                                        <div class="col-sm-6">
                                            <input type="text" id="payment_date" name="payment_date" class="form-control"
                                                   placeholder="Tanggal" value="{{ \Carbon\Carbon::parse($data->payment_date)->format('d-m-Y') }}" disabled/>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="name">COA Cash / Bank *</label>
                                        <div class="col-sm-6">
                                            <select id="coa_bank_id" class="form-control form-control-select2" name="coa_bank_id" disabled>
                                                <option value="{{$data->coa_bank_id}}">{{$data->coa_bank_code}} - {{$data->coa_bank_name}}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <label class="col-form-label" for="description">Referensi</label>
                                        </div>
                                        <div class="col-sm-6">
                                            <textarea id="description" name="reference" class="form-control" rows="4" placeholder="Referensi Supplier" disabled>{{ $data->reference }}</textarea>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <label class="col-form-label" for="description">Description</label>
                                        </div>
                                        <div class="col-sm-6">
                                            <textarea id="description" name="description" class="form-control" rows="4" maxlength="150"
                                                      placeholder="Description" disabled>{{ $data->description }}</textarea>
                                        </div>
                                    </div>

                                    <h4 class="fw-normal">Daftar COA</h4>
                                    <hr />
                                    <div class="row">
                                        <table class="table table-bordered table-striped table-vcenter js-dataTable-full" id="table_data">
                                            <thead>
                                            <tr>
                                                <th style="width: 5%">No</th>
                                                <th style="width: 15%">Nama COA</th>
                                                <th style="width: 8%">Debit</th>
                                                <th style="width: 8%">Kredit</th>
                                                <th style="width: 8%">Pajak</th>
                                                <th style="width: 8%">Deskripsi</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr v-for="(item, index) in itemList">
                                                <td><span>@{{ index + 1 }}</span></td>
                                                <td>
                                                    <select2 url="{{route('admin.coa.select')}}" v-model="item.coaId"
                                                             name="coaIds[]" class="form-select form-control-select2"
                                                             placeholder="Pilih COA" disabled>
                                                    </select2>
                                                </td>
                                                <td>
                                                    <vue-autonumeric :options="autonumericFormat" name="debits[]"
                                                                     v-model="item.debit" class="form-control"
                                                                     readonly></vue-autonumeric>
                                                </td>
                                                <td>
                                                    <vue-autonumeric :options="autonumericFormat" name="credits[]"
                                                                     v-model="item.credit" class="form-control"
                                                                     readonly></vue-autonumeric>
                                                </td>
                                                <td>
                                                    <input type="checkbox" class="form-check-input" name="taxes[]" v-model="item.isTax" disabled> Terdapat Pajak
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control" name="desc[]" v-model="item.desc" readonly>
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
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for confirm unapprove -->
    <div class="modal fade" id="modal_confirm_unapprove" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-slideup">
            <div class="modal-content">
                <div class="block-content">
                    <div class="row">
                        <div class="col-12 text-center">
                            <span>Apakah Anda yakin untuk unapprove Bank Cash Out </span><span id="deleted_locker_number"> ?</span>
                            <input type="hidden" id="deleted_locker_id">
                        </div>
                    </div>
                    <div class="row justify-content-center">
                        <div class="col-2">
                            <label>Password</label>
                        </div>
                        <div class="col-6">
                            <input id="delPassword" type="password" name="password" class="form-control" placeholder="Password"/>
                        </div>
                    </div>
                </div>
                <div class="block-content block-content-full text-center bg-body">
                    <button type="button" class="btn btn-warning" data-bs-dismiss="modal">No</button>
                    <button type="button" id="btn_submit_unapprove" class="btn btn-danger" onclick="confirmUnapprove()">Yes</button>
                    <button type="button" id="btn_loading_unapprove" class="btn btn-danger" style="display: none;">
                        <i class="spinner-border spinner-border-sm text-green" role="status">
                            <span class="visually-hidden">Please wait...</span>
                        </i>
                    </button>
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
    <script src="{{ asset('js/plugins/bootstrap-notify/bootstrap-notify.min.js') }}"></script>

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
                    grandTotal: {{$data->total_price}},
                    total_debit: {{$totalD}},
                    total_kredit: {{$totalK}},
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

        var modalConfirmUnapprove = new bootstrap.Modal(document.getElementById('modal_confirm_unapprove'), {'backdrop': 'static', 'keyboard' :false });

        $(document).on('click', '#btn_unapprove', function(){
            let id = $(this).data("id");
            let name = $(this).data("number");

            modalDelete(id, name);
        });

        function modalDelete(id, name){
            $('#deleted_locker_id').val(id);
            $('#deleted_locker_number').html(name);

            modalConfirmUnapprove.show();
        }

        function redirect(){
            window.location.href = '{{ route('admin.bank_cash_out.index') }}';
        }

        function confirmUnapprove(){
            let id = {{ $data->id }};
            let number = '{{ $data->code }}';
            let password = $('#delPassword').val();

            $('#btn_loading_unapprove').show(100);
            $('#btn_submit_unapprove').hide();

            $.ajax({
                type:'POST',
                url:'{{ route('admin.bank_cash_out.unapprove') }}',
                data:{
                    '_token': '{{ csrf_token() }}',
                    'id': id,
                    'password': password
                },
                success:function(data){
                    if(data === 'Sukses'){
                        One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', timer: 2000, message: 'Bank Cashout ' + number + ' has been successfully unapproved!'});
                        window.setTimeout( redirect, 2000 );
                    }
                    else{
                        $('#btn_submit_unapprove').show(100);
                        $('#btn_loading_unapprove').hide();
                        One.helpers('jq-notify', {type: 'danger', icon: 'fa fa-info-circle me-1', timer: 2000, message: 'BAD REQUEST!'});
                    }
                },
                error:function(data){
                    $('#btn_submit_unapprove').show(100);
                    $('#btn_loading_unapprove').hide();
                    One.helpers('jq-notify', {type: 'danger', icon: 'fa fa-info-circle me-1', timer: 2000, message: 'Internal server error!'});
                }
            });
        }
    </script>
@endsection
