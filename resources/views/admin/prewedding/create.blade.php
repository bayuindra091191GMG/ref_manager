@extends('layouts.admin')

@section('title')
    <title>Tambah Transaksi Paket</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Tambah Transaksi Paket
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.prewedding.index') }}">Daftar Transaksi Paket</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Tambah Transaksi Paket
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <div id="app" class="content">
        <form class="space-y-3" id="form_create_prewedding'" method="POST" action="{{ route('admin.prewedding.store') }}"
              enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-12 text-end">
                    <button id="btn_loading" type="button" class="btn btn-success" style="display: none;">
                        <i class="spinner-border spinner-border-sm text-green" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </i>
                    </button>
                    <button type="submit" id="btn_submit" class="btn btn-success">Simpan</button>
                    <a id="btn_cancel" href="{{ route('admin.prewedding.index') }}" class="btn btn-danger">Batal</a>
                </div>
            </div>
            <div class="block block-rounded">
                <div class="block-content block-content-full">
                    <div class="row">
                        <div class="col-sm-6">
                            @include('partials._success')
                            @include('partials._error')
                        </div>
                    </div>
                    @if (count($errors))
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="row">
                        <div class="col-12">
                            <div class="space-y-3">
                                <div class="row">
                                    <div class="col-12">
                                        <span style="color: red;">Isian dengan asterik (*) wajib diisi!</span>
                                    </div>
                                </div>
                                {{--                                <div class="row">--}}
                                {{--                                    <label class="col-sm-3 col-form-label" for="package">Branch <span--}}
                                {{--                                            class="text-red">*</span></label>--}}
                                {{--                                    <div class="col-sm-4">--}}
                                {{--                                        <select id="branch_id" name="branch_id" class="form-select">--}}
                                {{--                                            @foreach ($branches as $branch)--}}
                                {{--                                                <option value="{{ $branch->id }}"--}}
                                {{--                                                    {{ auth()->user()->branch_id == $branch->id ? 'selected' : '' }}>--}}
                                {{--                                                    {{ $branch->name }}</option>--}}
                                {{--                                            @endforeach--}}
                                {{--                                        </select>--}}
                                {{--                                    </div>--}}
                                {{--                                </div>--}}
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="user_name">Kasir *</label>
                                    <div class="col-sm-4">
                                        <select2 id="employee" url="{{ route('admin.employee.select', ['position_id' => 1]) }}" name="employee_id" class="form-control" v-model="employee" :value="employee"
                                                 placeholder="Pilih Kasir">
                                        </select2>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="user_name">Nama Customer *</label>
                                    <div class="col-sm-4">
                                        <input id="user_name" type="text" class="form-control"
                                               @input="fetchExistedCustomer($event.target.value)" name="user_name"
                                               placeholder="Nama Customer" value="{{ old('user_name') }}"
                                               @focus="isDropdownVisible = true" @blur="hideDropdown" required/>
                                        <div v-if="isDropdownVisible && existingCustomers.length > 0" class="dropdown-menu"
                                             style="width: 26% !important;">
                                            <div class="dropdown-section" v-for="customer in existingCustomers"
                                                 :key="customer.customer_phone">
                                                <div class="dropdown-item" @click="selectExistingCustomer(customer)">
                                                    <label>@{{ customer.customer_name }} @{{ customer.customer_phone ? '- ' + customer.customer_phone : '' }}</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="user_name">Nomor Telp Customer *</label>
                                    <div class="col-sm-4">
                                        <div class="input-group">
                                            <input type="hidden" name="country" v-model="country">
                                            <select name="phone_code" class="form-select w-auto" style="flex: 0 0 auto !important;" v-model="phoneCode">
                                                <option v-for="item in countries" :value="item.dial_code">@{{ item.dial_code }}</option>
                                            </select>
                                            <input id="user_phone" type="text" class="form-control" name="user_phone"
                                                   placeholder="Nomor Telepon Customer" value="{{ old('user_phone') }}" />
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="user_name">Gender *</label>
                                    <div class="col-sm-4">
                                        <select class="form-select" name="gender" v-model="gender" required>
                                            <option value="L">Laki - Laki</option>
                                            <option value="P">Perempuan</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="user_name">Paket *</label>
                                    <div class="col-sm-4">
                                        <select2 id="package_id" name="package_id" url="{{ route('admin.prewedding_package.select') }}" v-model="package" :value="package" placeholder="Pilih Paket"></select2>
                                    </div>
                                </div>

                                <hr style="opacity: 1">
                                <div class="row">
                                    <div class="col-sm-4 mb-3">
                                        <label class="mb-2">Subtotal</label>
                                        <vue-autonumeric :options="autonumericFormat" class="form-control" v-model="price" name="subtotal" readonly>
                                        </vue-autonumeric>
                                    </div>
                                    <div class="col-sm-4 mb-3">
                                        <label class="mb-2">PB2 (10%)</label>
                                        <vue-autonumeric :options="autonumericFormat" class="form-control" :value="pb2" name="pb2" readonly>
                                        </vue-autonumeric>
                                    </div>
                                    <div class="col-sm-4 mb-3">
                                        <label class="mb-2">Grand Total</label>
                                        <vue-autonumeric :options="autonumericFormat" class="form-control" :value="grandtotal" name="grandtotal" readonly>
                                        </vue-autonumeric>
                                    </div>
                                    <div class="col-sm-4">
                                        <label class="mb-2">Jumlah Dibayar</label>
                                        <vue-autonumeric :options="autonumericFormat" class="form-control" v-model="received" name="amount_received" :readonly="paymentMethod != 1" required>
                                        </vue-autonumeric>
                                    </div>
                                    <div class="col-sm-4">
                                        <label class="mb-2">Kembalian</label>
                                        <vue-autonumeric :options="autonumericFormat" class="form-control" :value="getReturn()" name="return_value" readonly>
                                        </vue-autonumeric>
                                    </div>
                                    <div class="col-sm-4">
                                        <label class="mb-2">Metode Pembayaran</label>
                                        <select2 ref="paymentSelect" id="paymentMethod" url="{{ route('admin.pos.select') }}" class="form-control" v-model="paymentMethod" :value="paymentMethod"
                                                 placeholder="Pilih metode pembayaran" name="payment_method" required>
                                        </select2>
                                    </div>
                                </div>

                                <h4 class="fw-normal">
                                    Detail Paket
                                </h4>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="table-responsive w-100">
                                            <table
                                                class="table table-bordered table-striped table-vcenter js-dataTable-full"
                                                id="table_data">
                                                <thead>
                                                <tr>
                                                    <th>Treatment/Addon</th>
{{--                                                    <th style="width: 15%;">Tanggal</th>--}}
                                                    <th style="width: 15%;">Durasi</th>
                                                    <th style="width: 15%;">Harga Net</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <tr v-if="loadDetail">
                                                    <td colspan="4" class="text-center"><i class="fa fa-fw fa-spin fa-circle-notch"></i></td>
                                                </tr>
                                                <tr v-for="(item, index) in details" :key="item.id">
                                                    <td>
{{--                                                        <select :id="item.id" class="form-control"--}}
{{--                                                                v-model="item.detail_id" :name="'details['+index+'][id]'">--}}
{{--                                                        </select>--}}
                                                        <input type="hidden" v-model="item.detail_id" :name="'details['+index+'][id]'">
                                                        <input type="text" class="form-control" :value="item.name" readonly>
                                                    </td>
{{--                                                    <td class="text-end">--}}
{{--                                                       <input type="text" class="form-control date" :name="'details['+index+'][date]'" v-model="item.date" readonly>--}}
{{--                                                    </td>--}}
                                                    <td class="text-end">
                                                        <div class="input-group">
                                                            <vue-autonumeric class="form-control" :name="'details['+index+'][duration]'" v-model="item.duration" :options="autonumericFormat" readonly></vue-autonumeric>
                                                            <span class="input-group-text" id="basic-addon2">Menit</span>
                                                        </div>
                                                    </td>
                                                    <td class="text-end">
                                                        <div class="input-group">
                                                            <span class="input-group-text" id="basic-addon2">Rp</span>
                                                            <vue-autonumeric class="form-control" :name="'details['+index+'][price]'" v-model="item.price" :options="autonumericFormat" readonly></vue-autonumeric>
                                                        </div>
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
        </form>

        {{--        <!-- Loading Div -->--}}
        {{--        <div class="loading" v-if="loading">--}}
        {{--            <div class="spinner-border text-info">--}}
        {{--                <span class="visually-hidden">Loading...</span>--}}
        {{--            </div>--}}
        {{--        </div>--}}
        {{--        <!-- End Loading Div -->--}}
    </div>
@endsection

@section('styles')
    <!-- Page JS Plugins CSS -->
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/bootstrap-fileinput/css/fileinput.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.min.css"
          crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('js/plugins/flatpickr/flatpickr.min.css') }}">
@endsection

@section('scripts')
    <!-- jQuery -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script src="{{ asset('js/axios.js') }}"></script>
    <script src="{{ asset('js/vue.js') }}"></script>
    <script src="{{ asset('js/AutoNumeric.js') }}"></script>
    <script src="{{ asset('js/vue-autonumeric.js') }}"></script>
    <script src="{{ asset('js/plugins/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
    <script src="{{ asset('js/plugins/bootstrap-fileinput/js/fileinput.js') }}"></script>
    <script src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('js/plugins/flatpickr/flatpickr.min.js') }}"></script>

    <script type="text/x-template" id="select2-template">
        <select>
            <slot></slot>
        </select>
    </script>
    <script src="{{ asset('js/vueComponent-select2.js') }}"></script>

    <script>

        $(document).ready(function () {
            $('form').on('submit', function () {
                $('#btn_submit').hide();
                $('#btn_loading').show();
            });
        });

        const app = new Vue({
            el: '#app',
            data: {
                employee: null,
                package: null,
                loadDetail: false,
                details: [],
                countries: @json(\App\Libs\Utilities::getCountryCodes()),
                phoneCode: '+62',
                country: 'ID',
                gender: 'L',
                paymentMethod: null,
                price: 0,
                received: 0,
                existingCustomers: [],
                messageSearchMember: '',
                successSearchMember: false,
                isDropdownVisible: false,
                typeCustomer: 'NC',
                loadNationalityMember: false,
                nationality: 0,
                notesTransaction: '',

                autonumericFormat: {
                    minimumValue: '-9999999999999',
                    maximumValue: '9999999999999',
                    digitGroupSeparator: '.',
                    decimalCharacter: ',',
                    decimalPlaces: 0,
                    modifyValueOnWheel: false,
                    allowDecimalPadding: false,
                    unformatOnSubmit: true
                },
            },
            computed: {
                pb2(){
                    return this.price * 10/100;
                },
                grandtotal(){
                    return this.price + this.pb2;
                },
            },
            methods: {
                async fetchExistedCustomer(query) {
                    $.ajax({
                        url: '{{ route('admin.transaction.search.existing_customer') }}',
                        type: 'get',
                        data: {
                            search: query,
                        },
                        success: function(response) {
                            app.existingCustomers = response;
                        },
                        error: function(xhr, status, error) {
                            console.log(error);
                        }
                    })
                },
                async selectExistingCustomer(customer) {
                    $('#user_name').val(customer.customer_name);
                    app.typeCustomer = customer.type;
                    app.notesTransaction = customer.notes;
                    for(var i = 0; i < app.countries.length; i++){
                        if(customer.country == app.countries[i].code){
                            app.phoneCode = app.countries[i].dial_code;
                            app.country = app.countries[i].code;
                            break;
                        }
                    }
                    $('#user_phone').val(customer.customer_phone.replace(app.phoneCode, ''));

                    if (customer.country_id !== null) {
                        let newId = new Option(customer.country_name, customer.country_id, true, true);
                        $('#nationality').append(newId).trigger("change");
                    } else {
                        app.loadNationalityMember = true;
                    }

                    // if (customer.customer_phone !== null) {
                    //     $('#user_phone').val(customer.customer_phone);
                    //     this.searchMember();
                    // } else {
                    //     app.preweddingMemberPhone = '';
                    //     app.messageSearchMember = 'Belum memiliki member!';
                    //     app.successSearchMember = false;
                    // }
                },
                async hideDropdown() {
                    setTimeout(() => {
                        this.isDropdownVisible = false;
                    }, 200);
                },
                searchMember() {
                    $.ajax({
                        url: '{{ route('admin.user.search') }}',
                        type: 'get',
                        data: {
                            phone: $('#member_phone').val(),
                        },
                        success: function(response) {
                            if (response.success) {
                                // $('#user_name').val(response.name);
                                app.messageSearchMember = response.message;
                                app.successSearchMember = response.success;
                            } else {
                                app.messageSearchMember = response.message;
                                app.successSearchMember = response.success;
                            }
                        },
                        error: function(xhr, status, error) {
                            console.log(error);
                        }
                    })
                },

                async getContents(id) {
                    this.details = [];
                    this.loadDetail = true;
                    $.ajax({
                        url: '{{ route('admin.prewedding.detail_package') }}',
                        type: 'get',
                        data: {
                            id: id,
                        },
                    }).then(function (result){
                        app.details = result.details;
                        app.price = result.price;
                        app.received = app.grandtotal;
                        // setTimeout(function (){
                        //     for(var i = 0; i < app.details.length; i++){
                        //         app.initiateDetail(app.details[i]);
                        //     }
                        // }, 100);
                    }).always(function (){
                        app.loadDetail = false;
                    });
                },
                async getPackageDetail(id) {
                    return $.ajax({
                        url: '{{ route('admin.package.get.detail') }}',
                        type: 'get',
                        data: {
                            package_id: id,
                        },
                    });
                },
                async getItemDetail(id) {
                    return $.ajax({
                        url: '{{ route('admin.item.get.detail') }}',
                        type: 'get',
                        data: {
                            item_id: id,
                        },
                    });
                },

                initiateDetail(item){
                    $('#' + item.id).select2({
                        placeholder: 'Pilih Treatment/Add On',
                        width: '100%',
                        minimumInputLength: 0,
                        ajax: {
                            url: '{{ route('admin.prewedding_package.select_detail') }}',
                            dataType: 'json',
                            delay: 250,
                            data: function(params) {
                                return {
                                    search: params.term,
                                };
                            },
                            processResults: function(data) {
                                return {
                                    results: data.map(group => ({
                                        text: group.text,
                                        children: group.children.map(item => ({
                                            id: item.id,
                                            text: item.text
                                        }))
                                    }))
                                };
                            }
                        }
                    }).on('select2:select', async function(e) {
                        let selectedId = e.params.data.id;

                        let index = app.details.findIndex(x => x.id === newItem.id);
                        if (index !== -1) {
                            app.details[index].detail_id = selectedId;
                        }
                    });

                    var newOption = new Option(item.name, item.detail_id, true, true);

                    // Append the option to the Select2 control
                    $('#' + item.id).append(newOption).trigger('change');

                    $('.date').flatpickr({
                        dateFormat: 'd-m-Y',
                    });
                },

                addMoreDetail(){
                    let newItem = {
                        id: makeid(10),
                        detail_id: null,
                        date: '',
                        duration: 0,
                    };

                    this.details.push(newItem);

                    this.$nextTick(() => {
                        let escapedId = CSS.escape(newItem.id);

                        $('#' + escapedId).select2({
                            placeholder: 'Pilih Treatment/Add On',
                            width: '100%',
                            minimumInputLength: 0,
                            ajax: {
                                url: '{{ route('admin.prewedding_package.select_detail') }}',
                                dataType: 'json',
                                delay: 250,
                                data: function(params) {
                                    return {
                                        search: params.term,
                                    };
                                },
                                processResults: function(data) {
                                    return {
                                        results: data.map(group => ({
                                            text: group.text,
                                            children: group.children.map(item => ({
                                                id: item.id,
                                                text: item.text
                                            }))
                                        }))
                                    };
                                }
                            }
                        }).on('select2:select', async function(e) {
                            let selectedId = e.params.data.id;

                            let index = app.details.findIndex(x => x.id === newItem.id);
                            if (index !== -1) {
                                app.details[index].detail_id = selectedId;
                            }
                        });

                        $('.date').flatpickr({
                            dateFormat: 'd-m-Y',
                        });
                    });
                },

                removeDetail(item) {
                    this.details = this.details.filter(function(x) {
                        return x !== item;
                    });
                },

                getReturn(){
                    var total = this.received - this.grandtotal;
                    if(total < 0){
                        total = 0;
                    }

                    return total;
                }
            },
            watch: {
                package(){
                    this.getContents(this.package);
                },
                paymentMethod(){
                    this.received = this.grandtotal;
                }
            }
        });
    </script>

    <script>
        // document.addEventListener("DOMContentLoaded", function () {
        //     flatpickr('.js-flatpickr', {
        //         enableTime: true,
        //         noCalendar: true,
        //         dateFormat: "H:i",
        //         time_24hr: true,
        //         locale: "id"
        //     });
        // });

        $('#form_create_prewedding').submit(function() {
            $('#btn_submit').hide();
            $('#btn_cancel').hide();
            $('#btn_loading').show(200);
        });
    </script>
@endsection
