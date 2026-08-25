@extends('layouts.admin')

@section('title')
    <title>Detail Coupon</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Detail Coupon
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.coupon.index') }}">Daftar Coupon</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Detail Coupon
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
                                <form class="space-y-3" id="form_edit_coupon" method="POST"
                                      action="{{ route('admin.coupon.update') }}" enctype="multipart/form-data">
                                    @csrf
                                    <div class="row">
                                        <div class="col-12 text-end">
                                            <button id="btn_loading" type="button" class="btn btn-success"
                                                    style="display: none;">
                                                <i class="spinner-border spinner-border-sm text-green" role="status">
                                                    <span class="visually-hidden">Mohon tunggu...</span>
                                                </i>
                                            </button>
                                            <a id="btn_cancel" href="{{ route('admin.coupon.index') }}"
                                               class="btn btn-danger">Batal</a>
                                        </div>
                                    </div>
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
                                            <input type="hidden" name="id" value="{{ $coupon->id }}">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="status">Status <span class="text-red">*</span></label>
                                        <div class="col-sm-4">
                                            <select class="form-control" id="status" name="status" disabled>
                                                <option value="1" {{ $coupon->status_id == 1 ? 'selected' : '' }} >Aktif</option>
                                                <option value="2" {{ $coupon->status_id == 2 ? 'selected' : '' }}>Non-Aktif</option>
                                            </select>
                                        </div>
                                    </div>
{{--                                    <div class="row">--}}
{{--                                        <label class="col-sm-3 col-form-label" for="is_for_sell">Apakah untuk dijual? <span class="text-red">*</span></label>--}}
{{--                                        <div class="col-sm-4">--}}
{{--                                            <select class="form-control" id="is_for_sell" name="is_for_sell" disabled>--}}
{{--                                                <option value="1" {{ $coupon->is_for_sell == 1 ? 'selected' : '' }}>Ya</option>--}}
{{--                                                <option value="0" {{ $coupon->is_for_sell == 0 ? 'selected' : '' }}>Tidak</option>--}}
{{--                                            </select>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="code">Kode Coupon <span
                                                class="text-red">*</span></label>
                                        <div class="col-sm-4">
                                            <input id="code" type="text" class="form-control" name="code" readonly
                                                   value="{{ old('name', $coupon->code) }}" placeholder="Kode Coupon" required>
{{--                                            <div class="mt-2">--}}
{{--                                                <input class="form-check-input" type="checkbox" id="can_redeem" name="can_redeem" v-model="can_redeem" disabled>--}}
{{--                                                <label class="ms-1">Bisa Redeem Point? </label>--}}
{{--                                            </div>--}}
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label for="can_redeem" class="col-sm-3 col-form-label">Tipe Voucher</label>
                                        <div class="col-sm-4">
                                            <select name="can_redeem" id="can_redeem" v-model="can_redeem" class="form-control" disabled>
                                                <option value="" selected disabled>Pilih Tipe Voucher</option>
                                                <option value="0">Umum</option>
                                                <option value="1">Loyalty</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row" v-if="can_redeem == '1'">
                                        <label class="col-sm-3 col-form-label" for="price">Harga Voucher (dalam point)</label>
                                        <div class="col-sm-4">
                                            <div class="input-group">
                                                <vue-autonumeric :options="autonumericFormat" name="redeem_points" v-model="redeem_points"
                                                                 class="form-control" readonly></vue-autonumeric>
                                                <div class="input-group-append">
                                                    <span class="input-group-text">Point</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
{{--                                    <div class="row">--}}
{{--                                        <label class="col-sm-3 col-form-label" for="price">Harga Voucher dalam Rupiah</label>--}}
{{--                                        <div class="col-sm-4">--}}
{{--                                            <div class="input-group">--}}
{{--                                                <div class="input-group-prepend">--}}
{{--                                                    <span class="input-group-text">RP</span>--}}
{{--                                                </div>--}}
{{--                                                <vue-autonumeric :options="autonumericFormat" name="price" v-model="price"--}}
{{--                                                                 class="form-control" readonly></vue-autonumeric>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="discount_percentage">Tipe Diskon</label>
                                        <div class="col-sm-4">
                                            <select name="discount_type" class="form-control" v-model="discount_type" id="discount_type" disabled>
                                                <option value="" selected disabled>Pilih Tipe Diskon</option>
                                                <option value="flat">Nominal</option>
                                                <option value="percentage">Persen</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row" v-if="discount_type == 'flat'">
                                        <label class="col-sm-3 col-form-label" for="discount_amount">Diskon Flat</label>
                                        <div class="col-sm-4">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">RP</span>
                                                </div>
                                                <vue-autonumeric :options="autonumericFormat" name="discount_amount" v-model="discount_amount"
                                                                 class="form-control" readonly></vue-autonumeric>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row" v-if="discount_type == 'percentage'">
                                        <label class="col-sm-3 col-form-label" for="discount_percentage">Diskon Persentase
                                            (%)</label>
                                        <div class="col-sm-4">
                                            <div class="input-group">
                                                <vue-autonumeric :options="autonumericFormat" v-model="discount_percentage"
                                                                 name="discount_percentage" class="form-control" readonly></vue-autonumeric>
                                                <div class="input-group-append">
                                                    <span class="input-group-text">%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="discount_percentage">Tanggal Valid dan Expired Voucher</label>
                                        <div class="col-sm-4">
                                            <input type="text" id="date" class="form-control" v-model="date" data-date-format="d-m-Y" placeholder="Pilih tanggal valid sampai expired voucher" readonly/>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="is_foc">Apakah FOC?</label>
                                        <div class="col-sm-4">
                                            <select class="form-select" name="is_foc" disabled>
                                                <option value="0" {{ $coupon->is_foc == 0 ? 'selected' : '' }}>Tidak</option>
                                                <option value="1" {{ $coupon->is_foc == 1 ? 'selected' : '' }}>Ya</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="is_free_treatment">Apakah Pemakaian Unlimited?</label>
                                        <div class="col-sm-4">
                                            <select class="form-select" name="is_unlimited" disabled>
                                                <option value="0" {{ $coupon->is_unlimited == 0 ? 'selected' : '' }}>Tidak</option>
                                                <option value="1" {{ $coupon->is_unlimited == 1 ? 'selected' : '' }}>Ya</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="is_free_treatment">Apakah Free Treatment?</label>
                                        <div class="col-sm-4">
                                            <select class="form-select" name="is_free_treatment" v-model="is_free_treatment" disabled>
                                                <option value="0" selected>Tidak</option>
                                                <option value="1">Ya</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row" v-if="is_free_treatment == 1">
                                        <label class="col-sm-3 col-form-label" for="packages">Treatments</label>
                                        <div class="col-sm-4">
                                            <select id="packages" class="form-control" name="packages[]"
                                                    multiple="multiple" disabled>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-3">
                                            <label class="form-label">Voucher Image </label>
                                        </div>
                                        <div class="col-sm-4">
                                            <div>
                                                <img
                                                    v-if="main_image"
                                                    :src="main_image"
                                                    alt="Preview Main Image"
                                                    style="height: auto; width: 100%"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="is_foc">Terms & Conditions</label>
                                        <div class="col-sm-4">
                                            <div style="height: 400px; overflow-y: auto" class="border p-3">
                                                {!! $coupon->terms_condition !!}
                                            </div>
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
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-buttons-bs5/css/buttons.bootstrap5.min.css') }}">
@endsection

@section('scripts')
    <!-- jQuery -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('js/vue.js') }}"></script>
    <script src="{{ asset('js/axios.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/autonumeric/4.10.7/autoNumeric.min.js" integrity="sha512-PeXqWg6jrDiMcCruPw4Xr59oeIzqHlD2z7ffueHcWQE7+1DuzPAQz1ywu3gfCHAPXxHE6RejKe2DlhjYTmQgLw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="{{ asset('js/vue-autonumeric.js') }}"></script>
    <script src="{{ asset('js/plugins/ckeditor5-classic/build/ckeditor.js') }}"></script>

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
                duration: '{{ $coupon->duration ?? 0 }}',
                can_redeem: '{{ $coupon->can_redeem ?? '' }}',
                is_free_treatment: {{ $coupon->is_free_treatment ?? 0 }},
                main_image: '{{ asset('images/coupons/' . $coupon->img_path) }}',
                date: '{{ $coupon->is_lifetime == 1 ? 'Berlaku Lifetime' : ($validDate . " to " . $expiredDate) }}',
                startDate: '{{ $validDate }}',
                endDate: '{{ $expiredDate }}',
                discount_type: '{{ $coupon->discount_percentage ? 'percentage' : 'flat' }}',
                discount_amount: {{ $coupon->discount_amount ?? 0 }},
                discount_percentage: {{ $coupon->discount_percentage ?? 0 }},
                redeem_points: {{ $coupon->redeem_points ?? 0 }},
                price: {{ $coupon->price ?? 0 }},
                packages: @json($packageIds),
                autonumericFormat: {
                    minimumValue: '-9999999999999',
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
                    emptyInputBehavior: 'zero'
                },
            },
            mounted() {
                let vm = this; // Store reference to Vue instance

                $('#packages').select2({
                    placeholder: {
                        id: '-1',
                        text: 'Pilih Treatment'
                    },
                    width: '100%',
                    minimumInputLength: 0,
                    ajax: {
                        url: '{{ route('admin.package.select') }}',
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

                // Fetch available options and prepopulate
                $.ajax({
                    url: '{{ route('admin.package.select') }}',
                    dataType: 'json',
                    success: function (data) {
                        if (!Array.isArray(vm.packages)) {
                            return;
                        }

                        // Extract all options from the nested "children" arrays
                        let allOptions = [];
                        data.forEach(group => {
                            if (Array.isArray(group.children)) {
                                allOptions = allOptions.concat(group.children);
                            }
                        });

                        // Filter only selected options
                        let selectedOptions = allOptions.filter(item => vm.packages.includes(item.id));

                        selectedOptions.forEach(option => {
                            let newOption = new Option(option.text, option.id, true, true);
                            $('#packages').append(newOption);
                        });

                        $('#packages').trigger('change'); // Ensure Select2 updates the UI
                    },
                    error: function (xhr, status, error) {
                        console.error("Error fetching data:", error);
                    }
                });
            }
        });
    </script>

    <script>One.helpersOnLoad(['js-ckeditor5']);</script>

    <script>
        $('#form_edit_coupon').submit(function() {
            $('#btn_submit').hide();
            $('#btn_cancel').hide();
            $('#btn_loading').show(200);
        })
    </script>
@endsection
