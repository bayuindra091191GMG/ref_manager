@extends('layouts.admin')

@section('title')
    <title>Buat Voucher</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Generate Voucher
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.voucher.index') }}">Daftar Voucher</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Generate Voucher
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <div class="content" id="vue-section">
        <div class="row items-push">
            <div class="col-md-12 col-xl-12">
                <div class="block block-rounded h-100 mb-0">
                    <div class="block-content block-content-full">
                        <div class="row">
                            <div class="col-12">
                                <form class="space-y-3" id="form_create_voucher" method="POST" action="{{ route('admin.voucher.generate') }}">
                                    @csrf
                                    <div class="row">
                                        <div class="col-12 text-end">
                                            <button id="btn_loading" type="button" class="btn btn-success" style="display: none;">
                                                <i class="spinner-border spinner-border-sm text-green" role="status">
                                                    <span class="visually-hidden">Mohon tunggu...</span>
                                                </i>
                                            </button>
                                            <input type="submit" id="btn_submit" class="btn btn-success" value="Simpan"/>&nbsp;&nbsp;
                                            <a id="btn_cancel" href="{{ route('admin.voucher.index') }}" class="btn btn-danger">Batal</a>
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
                                    <div class="row">
                                        <div class="col-12">
                                            <span style="color: red;">Isian dengan asterik (*) wajib diisi!</span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="coupon">Coupon <span class="text-red">*</span></label>
                                        <div class="col-sm-4">
                                            <select2 url="{{ route('admin.coupon.select') }}" class="form-select" name="coupon" placeholder="Pilih Voucher">
                                            </select2>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="count">Jumlah Voucher</label>
                                        <div class="col-sm-4">
                                            <vue-autonumeric :options="autonumericFormat" name="count" class="form-control"></vue-autonumeric>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="notes">Notes</label>
                                        <div class="col-sm-4">
                                            <textarea name="notes" rows="3" class="form-control" placeholder="Notes voucher"></textarea>
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
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
@endsection

@section('scripts')
    <!-- jQuery -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('js/vue.js') }}"></script>
    <script src="{{ asset('js/axios.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/autonumeric/4.10.7/autoNumeric.min.js" integrity="sha512-PeXqWg6jrDiMcCruPw4Xr59oeIzqHlD2z7ffueHcWQE7+1DuzPAQz1ywu3gfCHAPXxHE6RejKe2DlhjYTmQgLw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="{{ asset('js/vue-autonumeric.js') }}"></script>

    <script type="text/x-template" id="select2-template">
        <select>
            <slot></slot>
        </select>
    </script>
    <script src="{{ asset('js/vueComponent-select2.js') }}"></script>

    <script type="text/javascript">
        let vueComponent = new Vue({
            el: "#vue-section",
            data: {
                autonumericFormat: {
                    minimumValue: '0',
                    maximumValue: '9999999999999',
                    digitGroupSeparator: '.',
                    decimalCharacter: ',',
                    decimalPlaces: 2,
                    modifyValueOnWheel: false,
                    unformatOnSubmit: true,
                    allowDecimalPadding: false,
                },
                autonumericPercentageFormat: {
                    minimumValue: '0',
                    maximumValue: '100',
                    digitGroupSeparator: '.',
                    decimalCharacter: ',',
                    decimalPlaces: 0,
                    modifyValueOnWheel: false,
                    allowDecimalPadding: false,
                    emptyInputBehavior: 'zero',
                    unformatOnSubmit: true
                },
            },
        });
    </script>
    <script>
        $('#form_create_voucher').submit(function(){
            $('#btn_submit').hide();
            $('#btn_cancel').hide();
            $('#btn_loading').show(200);
        })
    </script>
@endsection
