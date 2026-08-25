@extends('layouts.admin')

@section('title')
    <title>Ubah Jurnal</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Edit Jurnal
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.journal.index') }}">Daftar Jurnal</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Ubah Jurnal
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
                                <form class="space-y-3" id="form_edit_department" method="POST" action="{{ route('admin.journal.update') }}">
                                    @csrf
                                    <div class="row">
                                        <div class="col-12 text-end">
                                            <button id="btn_loading" type="button" class="btn btn-outline-success" style="display: none;">
                                                <i class="spinner-border spinner-border-sm text-green" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </i>
                                            </button>
                                            <input type="submit" id="btn_submit" class="btn btn-outline-success" value="Simpan" v-if="balance==0"/>&nbsp;&nbsp;
                                            <input type="submit" id="btn_submit" class="btn btn-outline-success" value="Simpan" disabled v-else/>&nbsp;&nbsp;
                                            <a id="btn_cancel" href="{{ route('admin.journal.index') }}" class="btn btn-outline-danger">Batal</a>
                                            <br>
                                            <br>
                                            <span v-if="balance!=0" style="color: red">Balance Kredit dan Debit tidak Seimbang</span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-6">
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
                                    <input id="id" type="hidden" class="form-control" name="id" value="{{ $data->id }}">

                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="name">Kode *</label>
                                        <div class="col-sm-4">
                                            <input id="code" type="text" class="form-control"
                                                   name="code" value="{{ $data->transaction_code }}" placeholder="Code" readonly>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="name">Tipe *</label>
                                        <div class="col-sm-4">
                                            <select id="type" name="transaction_type" class="form-select form-control-select2">
                                                @foreach($types as $type)
                                                    <option value="{{$type->id}}"
                                                            @if($type->code == $data->transaction_type) selected @endif
                                                    >{{$type->code}} - {{$type->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="name">Tanggal</label>
                                        <div class="col-sm-4">
                                            <input type="text" id="transaction_date" name="transaction_date"
                                                   class="form-control" placeholder="Tanggal" value="{{ \Carbon\Carbon::parse($data->transaction_date)->format('d-m-Y') }}"/>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="name">Deskripsi</label>
                                        <div class="col-sm-4">
                                            <textarea id="description" type="text" class="form-control"
                                                      name="notes" rows="4" cols="50"  placeholder="Description">{{ $data->notes }}</textarea>
                                        </div>
                                    </div>

                                    <h4 class="fw-normal">Daftar COA</h4>
                                    <hr />
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="table-responsive w-100" >
                                                <table class="table table-bordered table-striped table-vcenter js-dataTable-full" id="table_data" style="width: 1500px">
                                                    <thead>
                                                    <tr>
                                                        <th style="width: 2%">
                                                            <a class="btn btn-success" style="cursor:pointer;" v-on:click="addMoreItem">
                                                                <i class="far fa-plus-square"></i>
                                                            </a>
                                                        </th>
                                                        <th style="width: 2%">No</th>
                                                        <th style="width: 10%">Nama COA</th>
                                                        <th style="width: 8%">Debit</th>
                                                        <th style="width: 8%">Kredit</th>
                                                        <th style="width: 18%">Deskripsi</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <tr v-for="(item, index) in itemList" :key="item.id">
                                                        <td>
                                                            <a class="btn btn-danger" style="cursor: pointer" v-on:click="removeItem(item)">
                                                                <i class="far fa-minus-square"></i>
                                                            </a>
                                                        </td>
                                                        <td><span>@{{++index}}</span></td>
                                                        {{-- <td>
                                                            <select class="form-control" name="debitKredit[]" v-model="item.debitKredit">
                                                                <option value="D">Debit</option>
                                                                <option value="K">Kredit</option>
                                                            </select>
                                                        </td> --}}
                                                        <td>
                                                            <select2 url="{{route('admin.coa.select')}}" v-model="item.coaId"
                                                                     name="coaIds[]" class="form-select form-control-select2" placeholder="Pilih COA">
                                                            </select2>
                                                            <input type="hidden" name="debitKredit[]" v-model="item.debitKredit">
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
                                        </div>
                                    </div>

                                    <div class="row">
                                        <label class="col-sm-4 col-form-label" for="status">Total Debit</label>
                                        <label class="col-sm-4 col-form-label" for="status">Total Kredit</label>
                                        <label class="col-sm-4 col-form-label" for="status">Balance</label>
                                        <div class="col-sm-4">
                                            <vue-autonumeric :options="autonumericFormat" class="form-control" v-model="total_debit" readonly></vue-autonumeric>
                                        </div>
                                        <div class="col-sm-4">
                                            <vue-autonumeric :options="autonumericFormat" class="form-control" v-model="total_kredit" readonly></vue-autonumeric>
                                        </div>
                                        <div class="col-sm-4">
                                            <vue-autonumeric :options="autonumericFormat" class="form-control" v-model="balance" readonly></vue-autonumeric>
                                        </div>
                                    </div>

{{--                                    <div class="row">--}}
{{--                                        <label class="col-sm-2 col-form-label" for="status">Grand Total</label>--}}
{{--                                        <div class="col-sm-8">--}}
{{--                                            <vue-autonumeric :options="autonumericFormat" name="total_price" class="form-control" v-model="grandTotal" readonly></vue-autonumeric>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
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
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-buttons-bs5/css/buttons.bootstrap5.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('js/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}">
@endsection

@section('scripts')
    <!-- jQuery -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>
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
        let tmpDetail = {!! $vueData !!};
        let vueComponent = new Vue({
            el: "#vue-section",
            data: {
                itemList: tmpDetail,
                grandTotal: {{$totalD}},
                total_debit: {{$totalD}},
                total_kredit: {{$totalK}},
                balance: {{$balance}},
                autonumericFormat: {
                    minimumValue: '-9999999999999999',
                    maximumValue: '9999999999999999',
                    digitGroupSeparator: '.',
                    decimalCharacter: ',',
                    decimalPlaces: 0,
                    modifyValueOnWheel: false,
                    unformatOnSubmit: true,
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
                    let tempTotalD = 0;
                    let tempTotalK = 0;
                    for(var i = 0; i < this.itemList.length; i++){
                        tempTotalK += this.itemList[i].credit;
                        tempTotalD += this.itemList[i].debit;
                        this.grandTotal = tempTotalK;
                    }
                    this.total_debit = tempTotalD;
                    this.total_kredit = tempTotalK;
                    this.balance = tempTotalD - tempTotalK;
                },
            }
        });
    </script>

    <script>
        $('#form_edit_department').submit(function(){
            $('#btn_submit').hide();
            $('#btn_cancel').hide();
            $('#btn_loading').show(200);
        })

        $('#transaction_date').datepicker({
            autoclose: true,
            todayHighlight: true,
            format: "dd-mm-yyyy"
        });
    </script>
@endsection
