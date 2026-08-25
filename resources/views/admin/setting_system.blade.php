@extends('layouts.admin')

@section('title')
    <title>General Settings</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        General Settings
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            General Settings
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <div id="app" class="content">
        <form class="space-y-3" id="form_create_voucher_package'" method="POST" action="{{ route('admin.setting_system.update') }}"
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
                                @foreach ($data as $config)
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="user_name">Ubah {{ $config->name }} *</label>
                                        <div class="col-sm-6">
                                            <input type="hidden" name="setting_id[]" value="{{ $config->id  }}">
                                            @if($config->key === 'spv_password')
                                                <input type="password" class="form-control" name="setting_value[]" placeholder="Password Baru">
                                                <input type="hidden" class="form-control" name="setting_value_int[]" value="{{ $config->value_int }}">
                                            @elseif(empty($config->value_int))
                                                <input type="text" class="form-control" name="setting_value[]" value="{{ $config->value }}" required>
                                                <input type="hidden" class="form-control" name="setting_value_int[]" value="{{ $config->value_int }}">
                                            @else
                                                <input type="hidden" class="form-control" name="setting_value[]" value="{{ $config->value }}">
                                                <vue-autonumeric class="form-control" name="setting_value_int[]" :value="{{ $config->value_int }}" :options="autonumericFormat" required></vue-autonumeric>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="tnc_booking">T&amp;C Booking</label>
                                    <div class="col-sm-6">
                                        <textarea class="form-control" id="tnc_booking" name="tnc_booking" rows="10">{{ old('tnc_booking', $tncBooking->value) }}</textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="tnc_payment">T&amp;C Payment</label>
                                    <div class="col-sm-6">
                                        <textarea class="form-control" id="tnc_payment" name="tnc_payment" rows="10">{{ old('tnc_payment', $tncPayment->value) }}</textarea>
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
            },
            watch: {

            },
            mounted() {
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

        $('#form_create_voucher_package').submit(function() {
            $('#btn_submit').hide();
            $('#btn_cancel').hide();
            $('#btn_loading').show(200);
        });
    </script>
@endsection
