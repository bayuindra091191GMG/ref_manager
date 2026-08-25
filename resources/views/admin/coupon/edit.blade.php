@extends('layouts.admin')

@section('title')
    <title>Ubah Voucher Royalti/Poin</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Ubah Voucher Royalti/Poin
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.coupon.index') }}">Daftar Voucher Royalti/Poin</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Ubah Voucher Royalti/Poin
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
                                            <input type="submit" id="btn_submit" class="btn btn-success"
                                                value="Simpan" />&nbsp;&nbsp;
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
                                            <span style="color: red;">Isian dengan asterik (*) wajib diisi!</span>
                                            <input type="hidden" name="id" value="{{ $coupon->id }}">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="status">Status <span class="text-red">*</span></label>
                                        <div class="col-sm-4">
                                            <select class="form-control" id="status" name="status">
                                                <option value="1" {{ $coupon->status_id == 1 ? 'selected' : '' }} >Aktif</option>
                                                <option value="2" {{ $coupon->status_id == 2 ? 'selected' : '' }}>Non-Aktif</option>
                                            </select>
                                        </div>
                                    </div>
{{--                                    <div class="row">--}}
{{--                                        <label class="col-sm-3 col-form-label" for="is_for_sell">Apakah untuk dijual? <span class="text-red">*</span></label>--}}
{{--                                        <div class="col-sm-4">--}}
{{--                                            <select class="form-control" id="is_for_sell" name="is_for_sell">--}}
{{--                                                <option value="1" {{ $coupon->is_for_sell == 1 ? 'selected' : '' }}>Ya</option>--}}
{{--                                                <option value="0" {{ $coupon->is_for_sell == 0 ? 'selected' : '' }}>Tidak</option>--}}
{{--                                            </select>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="code">Kode Coupon <span
                                                class="text-red">*</span></label>
                                        <div class="col-sm-4">
                                            <input id="code" type="text" class="form-control" name="code"
                                                   value="{{ old('name', $coupon->code) }}" placeholder="Kode Coupon" required>
                                            {{-- <div class="mt-2">
                                                <input class="form-check-input" type="checkbox" id="can_redeem" name="can_redeem" v-model="can_redeem">
                                                <label class="ms-1">Bisa Redeem Point? </label>
                                            </div> --}}
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label for="can_redeem" class="col-sm-3 col-form-label">Tipe Voucher</label>
                                        <div class="col-sm-4">
                                            <select name="can_redeem" id="can_redeem" v-model="can_redeem" class="form-control" required>
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
                                                                 class="form-control"></vue-autonumeric>
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
{{--                                                                 class="form-control"></vue-autonumeric>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="discount_percentage">Tipe Diskon</label>
                                        <div class="col-sm-4">
                                            <select name="discount_type" class="form-control" v-model="discount_type" id="discount_type" required>
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
                                                <vue-autonumeric :options="autonumericFormat" name="discount_amount"
                                                                 v-model="discount_amount" class="form-control"></vue-autonumeric>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row" v-if="discount_type == 'percentage'">
                                        <label class="col-sm-3 col-form-label" for="discount_percentage">Diskon Persentase
                                            (%)</label>
                                        <div class="col-sm-4">
                                            <div class="input-group">
                                                <vue-autonumeric :options="autonumericFormat" v-model="discount_percentage"
                                                                 name="discount_percentage" class="form-control"></vue-autonumeric>
                                                <div class="input-group-append">
                                                    <span class="input-group-text">%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="discount_percentage">Tanggal Valid dan Expired Voucher</label>
                                        <div class="col-sm-4">
                                            <input type="text" id="date" class="form-control" :class="{'js-flatpickr': !lifetime}" v-model="date" data-date-format="d-m-Y" placeholder="Pilih tanggal valid sampai expired voucher" :disabled="lifetime"/>
                                            <input type="hidden" name="start_date" :value="startDate">
                                            <input type="hidden" name="end_date" :value="endDate">

                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" value="1" id="flexCheckChecked" name="lifetime" v-model="lifetime">
                                                <label class="form-check-label" for="flexCheckChecked">
                                                    Berlaku Lifetime
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="is_foc">Apakah FOC?</label>
                                        <div class="col-sm-4">
                                            <select class="form-select" name="is_foc">
                                                <option value="0" {{ $coupon->is_foc == 0 ? 'selected' : '' }}>Tidak</option>
                                                <option value="1" {{ $coupon->is_foc == 1 ? 'selected' : '' }}>Ya</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="is_free_treatment">Apakah Pemakaian Unlimited?</label>
                                        <div class="col-sm-4">
                                            <select class="form-select" name="is_unlimited">
                                                <option value="0" {{ $coupon->is_unlimited == 0 ? 'selected' : '' }}>Tidak</option>
                                                <option value="1" {{ $coupon->is_unlimited == 1 ? 'selected' : '' }}>Ya</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="is_free_treatment">Apakah Free Treatment?</label>
                                        <div class="col-sm-4">
                                            <select class="form-select" name="is_free_treatment" v-model="is_free_treatment">
                                                <option value="0" selected>Tidak</option>
                                                <option value="1">Ya</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row" v-if="is_free_treatment == 1">
                                        <label class="col-sm-3 col-form-label" for="packages">Treatments</label>
                                        <div class="col-sm-4">
                                            <select id="packages" class="form-control" name="packages[]"
                                                    multiple="multiple">
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-3">
                                            <label class="form-label">Voucher Image </label>
                                        </div>
                                        <div class="col-sm-4">
                                            <input
                                                @change="handleMainImageUpload($event)"
                                                type="file"
                                                accept="image/*"
                                                name="main_image"
                                                id="main_image"
                                                class="form-control"
                                            />
                                            <label for="main_image" class="text-muted mt-1">
                                                Maksimal ukuran gambar 3 MB
                                            </label>
                                            <div>
                                                <img
                                                    v-if="main_image"
                                                    :src="main_image"
                                                    alt="Preview Main Image"
                                                    class="mt-3"
                                                    style="height: auto; width: 100%"
                                                />
                                                <label for="main_image" class="text-danger mt-1">Jangan ubah jika tidak ingin mengganti gambar!</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="is_foc">Terms & Conditions</label>
                                        <div class="col-sm-4">
                                            <textarea id="js-ckeditor5-classic" rows="5" name="terms_conditions" placeholder="Terms & Conditions Voucher">{{ $coupon->terms_condition }}</textarea>
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
    <link rel="stylesheet" href="{{ asset('js/plugins/flatpickr/flatpickr.css') }}">
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
    <script src="{{ asset('js/plugins/flatpickr/flatpickr.min.js') }}"></script>

    <script type="text/x-template" id="select2-template">
        <select>
            <slot></slot>
        </select>
    </script>
    <script src="{{ asset('js/vueComponent-select2.js') }}"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let flatpickrInstance = flatpickr("#date", {
                dateFormat: "d-m-Y",
                allowInput: true,
                mode: "range", // Always use range mode
                onOpen: function (selectedDates, dateStr, instance) {
                    setTimeout(() => {
                        if (!document.querySelector(".flatpickr-custom-buttons")) {
                            const buttonContainer = document.createElement("div");
                            buttonContainer.className = "flatpickr-custom-buttons";
                            buttonContainer.style.display = "flex";
                            buttonContainer.style.flexWrap = "wrap";
                            buttonContainer.style.padding = "8px";
                            buttonContainer.style.gap = "5px";
                            buttonContainer.style.borderTop = "1px solid #ddd";
                            buttonContainer.style.marginTop = "5px";

                            // const options = [
                            //     { label: "Today", value: "today" },
                            //     { label: "Yesterday", value: "yesterday" },
                            //     { label: "This Week", value: "this_week" },
                            //     { label: "Last Week", value: "last_week" },
                            //     { label: "This Month", value: "this_month" },
                            //     { label: "Last Month", value: "last_month" },
                            //     { label: "This Year", value: "this_year" },
                            //     { label: "Last Year", value: "last_year" }
                            // ];

                            options.forEach(opt => {
                                const btn = document.createElement("button");
                                btn.innerText = opt.label;
                                btn.className = "flatpickr-button";
                                btn.style.padding = "5px 8px";
                                btn.style.fontSize = "12px";
                                btn.style.border = "1px solid #ccc";
                                btn.style.borderRadius = "4px";
                                btn.style.background = "#f8f9fa";
                                btn.style.cursor = "pointer";
                                btn.style.flex = "1 1 auto";
                                btn.onclick = () => setDate(opt.value, instance);
                                buttonContainer.appendChild(btn);
                            });

                            instance.calendarContainer.appendChild(buttonContainer);
                        }
                    }, 10);
                }
            });

            // function setDate(option, instance) {
            //     let today = new Date();
            //     let startDate, endDate;
            //
            //     switch (option) {
            //         case "today":
            //             startDate = endDate = today;
            //             break;
            //         case "yesterday":
            //             startDate = new Date(today);
            //             startDate.setDate(today.getDate() - 1);
            //             endDate = startDate;
            //             break;
            //         case "this_week":
            //             startDate = new Date(today);
            //             startDate.setDate(today.getDate() - today.getDay()); // Start of week (Sunday)
            //             endDate = new Date(startDate);
            //             endDate.setDate(startDate.getDate() + 6); // End of week (Saturday)
            //             break;
            //         case "last_week":
            //             startDate = new Date(today);
            //             startDate.setDate(today.getDate() - today.getDay() - 7); // Start of last week
            //             endDate = new Date(startDate);
            //             endDate.setDate(startDate.getDate() + 6); // End of last week
            //             break;
            //         case "this_month":
            //             startDate = new Date(today.getFullYear(), today.getMonth(), 1);
            //             endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0); // End of month
            //             break;
            //         case "last_month":
            //             startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            //             endDate = new Date(today.getFullYear(), today.getMonth(), 0); // End of last month
            //             break;
            //         case "this_year":
            //             startDate = new Date(today.getFullYear(), 0, 1);
            //             endDate = new Date(today.getFullYear(), 11, 31); // End of year
            //             break;
            //         case "last_year":
            //             startDate = new Date(today.getFullYear() - 1, 0, 1);
            //             endDate = new Date(today.getFullYear() - 1, 11, 31); // End of last year
            //             break;
            //     }
            //
            //     if (startDate && endDate) {
            //         flatpickrInstance.setDate([startDate, endDate], true);
            //     }
            //     flatpickrInstance.close();
            // }
        });
    </script>

    <script type="text/javascript">
        let vueComponent = new Vue({
            el: "#vue-section",
            data: {
                duration: '{{ $coupon->duration ?? 0 }}',
                can_redeem: '{{ $coupon->can_redeem ?? '' }}',
                is_free_treatment: {{ $coupon->is_free_treatment ?? 0 }},
                main_image: '{{ asset('images/coupons/' . $coupon->img_path) }}',
                date: '{{ $validDate }} to {{ $expiredDate }}',
                startDate: '{{ $validDate }}',
                endDate: '{{ $expiredDate }}',
                discount_type: '{{ $coupon->discount_percentage ? 'percentage' : 'flat' }}',
                discount_amount: {{ $coupon->discount_amount ?? 0 }},
                discount_percentage: {{ $coupon->discount_percentage ?? 0 }},
                redeem_points: {{ $coupon->redeem_points ?? 0 }},
                price: {{ $coupon->price ?? 0 }},
                packages: @json($packageIds),
                lifetime: {{ $coupon->is_lifetime == 1 ? 'true' : 'false' }},
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
                    emptyInputBehavior: 'zero'
                },
            },
            watch: {
                date(newVal, oldVal) {
                    if (newVal.includes(" to ")) {
                        const dateArray = newVal.split(" to ");
                        this.startDate = dateArray[0];
                        this.endDate = dateArray[1];
                    } else {
                        // If only one date is selected
                        this.startDate = newVal;
                        this.endDate = newVal;
                    }
                }
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
