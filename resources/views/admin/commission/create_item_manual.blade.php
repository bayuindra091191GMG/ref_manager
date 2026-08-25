@extends('layouts.admin')

@section('title')
    <title>Tambah Komisi Manual</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Tambah Komisi Manual
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.commission_manual.index') }}">Daftar Komisi Manual</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Tambah Komisi Manual
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <div class="content">
        <form class="space-y-3" id="form_create_item_commission'" method="POST" action="{{ route('admin.commission_manual.store') }}">
            @csrf
            <div class="row">
                <div class="col-12 text-end">
                    <button id="btn_loading" type="button" class="btn btn-success" style="display: none;">
                        <i class="spinner-border spinner-border-sm text-green" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </i>
                    </button>
                    <input type="submit" id="btn_submit" class="btn btn-success" value="Simpan"/>&nbsp;&nbsp;
                    <a id="btn_cancel" href="{{ route('admin.commission_manual.index') }}" class="btn btn-danger">Batal</a>
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
                            <div class="space-y-3">

                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="date">Tanggal</label>
                                    <div class="col-sm-3">
                                        <input type="text"
                                               id="date"
                                               name="date"
                                               data-date-format="d-m-Y"
                                               class="form-control js-flatpickr" value="{{ $today }}" />
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <span style="color: red;">Pilih salah satu!</span>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="employee">Karyawan</label>
                                    <div class="col-sm-3">
                                        <select class="form-control" id="employee" name="employee"></select>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="partner">Therapis</label>
                                    <div class="col-sm-3">
                                        <select class="form-control" id="partner" name="partner"></select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="block block-rounded" id="vue-section">
                <div class="block-content block-content-full">
                    <div class="row">
                        <div class="col-12">
                            <table class="table table-bordered nowrap">
                                <thead>
                                <tr>
                                    <th class="text-center">No</th>
                                    <th class="text-center">Barang</th>
                                    <th class="text-center">Qty Terjual</th>
                                    {{-- <th class="text-center">Total Komisi</th> --}}
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="(item, index) in itemCommissions">
                                    <td class="text-center">
                                        @{{ index + 1 }}
                                    </td>
                                    <td>
                                        @{{ item.item_name }}
                                        <input type="hidden" :name="'item_commissions[' + index + '][item_id]'" v-model="item.item_id" />
                                    </td>
                                    <td>
                                        <vue-autonumeric :options="autonumericFormat"
                                                         class="form-control text-end"
                                                         :name="'item_commissions[' + index + '][qty]'"
                                                         v-model="item.qty"></vue-autonumeric>
                                    </td>
                                    {{-- <td>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <vue-autonumeric :options="autonumericFormat"
                                                             class="form-control text-end"
                                                             :name="'item_commissions[' + index + '][commission_amount]'"
                                                             v-model="item.commission_amount"></vue-autonumeric>
                                        </div>

                                    </td> --}}
                                </tr>
                                </tbody>
                            </table>
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
    <link rel="stylesheet" href="{{ asset('js/plugins/flatpickr/flatpickr.css') }}">
@endsection

@section('scripts')
    <!-- jQuery -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('js/plugins/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
    <script src="{{ asset('js/plugins/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('js/plugins/flatpickr/l10n/id.js') }}"></script>

    <!-- Vue requirements -->
    <script src="{{ asset('js/vue.js') }}"></script>
    <script src="{{ asset('js/axios.js') }}"></script>
    <script src="{{ asset('js/AutoNumeric.js') }}"></script>
    <script src="{{ asset('js/vue-autonumeric.js') }}"></script>

    <script type="text/x-template" id="select2-template">
        <select>
            <slot></slot>
        </select>
    </script>

    <script src="{{ asset('js/vueComponent-select2.js') }}"></script>

    <script>
        $(document).keypress(
            function(event){
                if (event.which == '13') {
                    event.preventDefault();
                }
            });
        flatpickr('.js-flatpickr', {
            "locale": "id"  // locale for this instance only
        });

        $('#form_create_item_commission').submit(function(){
            $('#btn_submit').hide();
            $('#btn_cancel').hide();
            $('#btn_loading').show(200);
        });

        $('#employee').select2({
            placeholder: {
                id: '-1',
                text: ' --- Pilih Karyawan --- '
            },
            width: '100%',
            minimumInputLength: 0,
            ajax: {
                url: '{{ route('admin.employee.select') }}',
                dataType: 'json',
                data: function (params) {
                    return {
                        search: $.trim(params.term),
                        branch_id: {{ $branchId }}
                    };
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                }
            }
        });

        $('#partner').select2({
            placeholder: {
                id: '-1',
                text: ' --- Pilih Therapis --- '
            },
            width: '100%',
            minimumInputLength: 0,
            ajax: {
                url: '{{ route('admin.partner.select') }}',
                dataType: 'json',
                data: function (params) {
                    return {
                        search: $.trim(params.term),
                        branch_id: {{ $branchId }}
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

    <script>
        let vueComponent = new Vue({
            el: '#vue-section',
            data: {
                autonumericFormatPercent: {
                    minimumValue: '0',
                    maximumValue: '100',
                    digitGroupSeparator: '.',
                    decimalCharacter: ',',
                    decimalPlaces: 2,
                    modifyValueOnWheel: false,
                    allowDecimalPadding: false,
                },
                autonumericFormat: {
                    minimumValue: '0',
                    maximumValue: '999999999999999',
                    digitGroupSeparator: '.',
                    decimalCharacter: ',',
                    decimalPlaces: 0,
                    modifyValueOnWheel: false
                },
                itemCommissions: {!! $itemCommissionListArr !!},
                branch_id: {{ $branchId }}
            },
            methods: {
                removeCommission(item){
                    this.commissions = this.commissions.filter(function (x) { return x !== item; });
                },
                // addMoreCommission(){
                //     this.commissions.push({
                //         item_id: null,
                //         commission_percentage: 0,
                //         commission_flat: 0
                //     })
                // }
            },
            mounted: function() {
                // flatpickr('.js-flatpickr', {
                //     "locale": "id" // locale for this instance only
                // });
            },
        });

    </script>
@endsection
