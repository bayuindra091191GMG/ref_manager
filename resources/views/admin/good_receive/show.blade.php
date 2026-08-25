@extends('layouts.admin')

@section('title')
    <title>Detil Good Receive</title>
@endsection

@section('content')
    <!-- Hero -->
    <div class="bg-body-light print_hide">
        <div class="content content-full">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Detil Good Receive
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
                            Detil Good Receive
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
                                            @if($data->status_id == 13)
                                                <button type="button" id="btn_unapprove" class="btn btn-outline-warning">Unapprove</button>
                                            @endif
                                            <a id="btn_cancel" href="{{ route('admin.good_receive.show', ['id' => $data->id]) }}?type=print" class="btn btn-outline-info">Print</a>
                                            <a id="btn_cancel" href="{{ route('admin.good_receive.index') }}" class="btn btn-outline-danger">Kembali</a>
                                        </div>
                                    </div>
                                    <div class="row print_show text-center" style="display: none;">
                                        <h4>Good Receive</h4>
                                    </div>
                                    <div class="row">
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
                                        <label class="col-sm-3 col-form-label" for="name">Referensi Purchase Order</label>
                                        <div class="col-sm-6">
                                            <input type="hidden" class="form-control" name="po_id" value="{{$data->po_id}}"/>
                                            <input type="text" class="form-control" name="po_code" value="{{$data->po_code}}" readonly/>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="name">Kode PR</label>
                                        <div class="col-sm-6">
                                            <input type="hidden" class="form-control" name="pr_id" value="{{$data->pr_id}}"/>
                                            <input type="text" class="form-control" name="pr_code" value="{{$data->pr_code}}" readonly/>
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
                                        <label class="col-sm-3 col-form-label" for="name">Tanggal Dibutuhkan</label>
                                        <div class="col-sm-6">
                                            <input type="text" id="transaction_date" name="transaction_date" class="form-control" placeholder="Tanggal dibutuhkan"
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

                                    <h4 class="fw-normal">Daftar Barang</h4>
                                    <div class="row" v-if="qty_error">
                                        <label class="col-sm-10 col-form-label" style="color: red">Jumlah Item melebihi pesanan PR!</label>
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
                                                        <th style="width: 30px">Qty Sudah GR</th>
                                                        <th style="width: 20px">Qty</th>
                                                        <th style="width: 70px">Harga</th>
                                                        <th style="width: 70px">Total</th>
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
                                                            <vue-autonumeric :options="autonumericFormat" name="qtys[]" class="form-control" v-model="item.qty" readonly></vue-autonumeric>
                                                        </td>
                                                        <td>
                                                            <vue-autonumeric :options="autonumericFormat" name="prices[]" class="form-control" v-model="item.price" readonly></vue-autonumeric>
                                                        </td>
                                                        <td>
                                                            <vue-autonumeric :options="autonumericFormat" name="totals[]" class="form-control" :value="item.subtotal" readonly></vue-autonumeric>
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
        </div>
    </div>
    <!-- Modal for confirm unapprove -->
    <div class="modal fade" id="modal_confirm_unapprove" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-slideup">
            <div class="modal-content">
                <div class="block-content">
                    <div class="row">
                        <div class="col-12 text-center">
                            <span>Apakah Anda yakin untuk unapprove Good Receive </span><span id="deleted_locker_number"> ?</span>
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
                    <button type="button" id="btn_submit_delete" class="btn btn-danger" onclick="confirmUnapprove()">Yes</button>
                    <button type="button" id="btn_loading_delete" class="btn btn-danger" style="display: none;">
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
    <script src="{{ asset('js/plugins/bootstrap-notify/bootstrap-notify.min.js') }}"></script>

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
                    qty_error: false,
                    branchName: '',
                    isLoading: false,

                    subTotal: 0,
                    ppnPercent: 0,
                    ppnValue: 0,
                    disc1: 0,
                    disc1Value: 0,
                    disc2: 0,
                    disc2Value: 0,
                    grandTotal: 0,

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

        function confirmUnapprove(){
            let id = {{ $data->id }};
            let number = '{{ $data->code }}';

            $('#btn_loading_delete').show(100);
            $('#btn_submit_delete').hide();

            $.ajax({
                type:'POST',
                url:'{{ route('admin.good_receive.unapprove') }}',
                data:{
                    '_token': '{{ csrf_token() }}',
                    'id': id,
                    'password': $('#delPassword').val(),
                },
                success:function(data){
                    if(data === 'Sukses'){
                        One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', timer: 2000, message: 'Good Receive ' + number + ' has been successfully unapproved!'});
                        window.setTimeout( redirect, 2000 );
                    }
                    else{
                        $('#btn_submit_delete').show(100);
                        $('#btn_loading_delete').hide();
                        One.helpers('jq-notify', {type: 'danger', icon: 'fa fa-info-circle me-1', timer: 2000, message: 'BAD REQUEST!'});
                    }
                },
                error:function(data){
                    $('#btn_submit_delete').show(100);
                    $('#btn_loading_delete').hide();
                    One.helpers('jq-notify', {type: 'danger', icon: 'fa fa-info-circle me-1', timer: 2000, message: 'Internal server error!'});
                }
            });
        }

        function redirect(){
            window.location.href = '{{ route('admin.good_receive.index') }}';
        }
    </script>
@endsection
