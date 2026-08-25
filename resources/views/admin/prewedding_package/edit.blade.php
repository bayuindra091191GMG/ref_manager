@extends('layouts.admin')

@section('title')
    <title>Ubah Paket</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Ubah Paket
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.prewedding_package.index') }}">Daftar Paket</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Ubah Paket
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <div id="app" class="content">
        <form class="space-y-3" id="form_create_prewedding_package'" method="POST" action="{{ route('admin.prewedding_package.update') }}"
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
                    <a id="btn_cancel" href="{{ route('admin.prewedding_package.index') }}" class="btn btn-danger">Batal</a>
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

                                <input type="hidden" name="id" value="{{ $package->id }}">

                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="user_name">Status *</label>
                                    <div class="col-sm-2">
                                        <select class="form-select" name="status" v-model="status" required>
                                            <option value="1">Aktif</option>
                                            <option value="2">Non-Aktif</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="user_name">Kode Paket *</label>
                                    <div class="col-sm-2">
                                        <input type="text" class="form-control" name="code" placeholder="Kode" value="{{ old('code') ?? $package->code }}" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="user_name">Nama Paket *</label>
                                    <div class="col-sm-4">
                                        <input type="text" class="form-control" name="name" placeholder="Nama" value="{{ old('name') ?? $package->name }}" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="user_name">Harga Paket *</label>
                                    <div class="col-sm-2">
                                        <div class="input-group">
                                            <span class="input-group-text" id="basic-addon1">Rp</span>
                                            <vue-autonumeric class="form-control" name="price" v-model="price" :options="autonumericFormat" required></vue-autonumeric>
                                        </div>
                                    </div>
                                </div>

                                <hr style="opacity: 1">

                                <div class="d-flex flex-row justify-content-between align-items-center">
                                    <h4 class="fw-normal">
                                        Treatment
                                    </h4>

                                    <div class="d-flex flex-row">
                                        <select class="form-select" v-model="category">
                                            <option value="all">Semua</option>
                                            @foreach($categories as $category)
                                                @if($category->id != 7)
                                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                @endif
                                            @endforeach
                                        </select>

                                        <button type="button" class="btn btn-primary ms-2" disabled v-if="loadAll"><i class="fa fa-fw fa-spin fa-circle-notch"></i></button>
                                        <button type="button" class="btn btn-primary ms-2" @click="getAll" v-else>Tambah</button>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="table-responsive w-100">
                                            <table
                                                class="table table-bordered table-striped table-vcenter js-dataTable-full"
                                                id="table_data">
                                                <thead>
                                                <tr>
                                                    <th style="width: 5%;" class="text-center">
                                                        <a class="btn btn-success" style="cursor:pointer;"
                                                           @click="addMoreDetail">
                                                            <i class="fa fa-plus"></i>
                                                        </a>
                                                    </th>
                                                    <th>Treatment/Addon</th>
                                                    <th>Harga Net</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <tr v-for="(item, index) in details" :key="item.id">
                                                    <td class="text-center">
                                                        <a class="btn btn-danger text-center" style="cursor: pointer"
                                                           @click="removeItem(item)" v-if="index > 0">
                                                            <i class="fa fa-minus"></i>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <select :id="item.id" class="form-control"
                                                                v-model="item.package" :name="'details[]'">
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <vue-autonumeric class="form-control"
                                                            :name="'prices[]'"
                                                            v-model="item.price"
                                                            :options="autonumericFormat"></vue-autonumeric>
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
                details: @json($details),
                category: 'all',
                loadAll: false,
                status: {{ old('status') ?? $package->status_id }},
                price: {{ old('price') ?? $package->price }},

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
            methods: {
                getAll(){
                    this.loadAll = true;
                    $.ajax({
                        url: '{{ route('admin.prewedding_package.get_all') }}',
                        data: {
                            category: app.category,
                        },
                        success: function (response) {
                            app.details = response.data;

                            app.$nextTick(() => {
                                for(var i = 0; i < app.details.length; i++){
                                    let newItem = app.details[i];

                                    let escapedId = CSS.escape(newItem.id);

                                    $('#' + escapedId).select2({
                                        placeholder: 'Pilih Treatment/Addon',
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
                                        let index = app.details.findIndex(x => x.id === newItem.id);
                                        app.details[index].package = e.params.data.id;
                                    });

                                    var newOption = new Option(newItem.name, newItem.package, true, true);

                                    // Append the option to the Select2 control
                                    $('#' + newItem.id).append(newOption).trigger('change');
                                }
                            });

                            app.loadAll = false;
                        },
                        error: function (xhr, status, error) {
                            console.log(error);
                        }
                    });
                },

                addMoreDetail() {
                    let newItem = {
                        id: makeid(10),
                        package: '',
                    };

                    this.details.push(newItem);

                    this.$nextTick(() => {
                        let escapedId = CSS.escape(newItem.id);

                        $('#' + escapedId).select2({
                            placeholder: 'Pilih Treatment/Addon',
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
                            let index = app.details.findIndex(x => x.id === newItem.id);
                            app.details[index].package = e.params.data.id;
                        });
                    });
                },
                removeItem(item) {
                    this.details = this.details.filter(function(x) {
                        return x !== item;
                    });
                },
            },
            watch: {

            },
            mounted() {
                for(var i = 0; i < this.details.length; i++){
                    $('#' + this.details[i].id).select2({
                        placeholder: 'Pilih Treatment/Addon',
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
                        app.details[index].package = selectedId;
                    });

                    var newOption = new Option(this.details[i].name, this.details[i].package, true, true);

                    // Append the option to the Select2 control
                    $('#' + this.details[i].id).append(newOption).trigger('change');
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

        $('#form_create_prewedding_package').submit(function() {
            $('#btn_submit').hide();
            $('#btn_cancel').hide();
            $('#btn_loading').show(200);
        });
    </script>
@endsection
