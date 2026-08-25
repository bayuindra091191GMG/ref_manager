@extends('layouts.admin')

@section('title')
    <title>Tambah Booking</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Tambah Booking
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.booking.index') }}">Daftar Booking</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Tambah Booking
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <div id="app" class="content">
        <form class="space-y-3" id="form_create_booking'" method="POST" action="{{ route('admin.booking.store') }}"
              enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-12 text-end">
                    <button id="btn_loading" type="button" class="btn btn-success" style="display: none;">
                        <i class="spinner-border spinner-border-sm text-green" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </i>
                    </button>
                    <input type="submit" id="btn_submit" class="btn btn-success" value="Simpan" />&nbsp;&nbsp;
                    <a id="btn_cancel" href="{{ route('admin.booking.index') }}" class="btn btn-danger">Batal</a>
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
                                    <label class="col-sm-3 col-form-label" for="user_name">Nama Customer *</label>
                                    <div class="col-sm-4">
                                        <input id="user_name" type="text" class="form-control"
                                               @input="fetchExistedCustomer($event.target.value)" name="user_name"
                                               placeholder="Nama Customer" value="{{ old('user_name') }}"
                                               @focus="isDropdownVisible = true" @blur="hideDropdown" />
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
                                    <label class="col-sm-3 col-form-label" for="user_name">Tanggal Booking *</label>
                                    <div class="col-sm-4">
                                        <input id="booking_date" type="text"
                                               class="js-calendar-flatpickr form-control" name="booking_date"
                                               placeholder="Tanggal Booking" value="{{ old('booking_date') }}" />
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="user_name">Jam Booking *</label>
                                    <div class="col-sm-4">
                                        <input id="booking_time" type="text" class="js-flatpickr form-control"
                                               name="booking_time" placeholder="Jam Booking"
                                               value="{{ old('booking_time') }}" />
                                    </div>
                                </div>

{{--                                <div class="row">--}}
{{--                                    <label class="col-sm-3 col-form-label" for="user_name">Durasi *</label>--}}
{{--                                    <div class="col-sm-2">--}}
{{--                                        <div class="input-group">--}}
{{--                                            <vue-autonumeric class="form-control" name="duration" v-model="duration" :options="autonumericFormat"></vue-autonumeric>--}}
{{--                                            <span class="input-group-text" id="basic-addon2">Menit</span>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}

                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="user_name">Bukti Pembayaran (Optional)</label>
                                    <div class="col-sm-4">
                                        <input type="file" class="form-control" name="payment_proof">
                                    </div>
                                </div>

                                <h4 class="fw-normal">
                                    Detail Booking
                                </h4>
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
                                                    <th>Treatment</th>
                                                    <th style="width: 15%;">Harga</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <tr v-for="(item, index) in bookings" :key="item.id">
                                                    <td class="text-center">
                                                        <a class="btn btn-danger text-center" style="cursor: pointer"
                                                           @click="removeItem(item)">
                                                            <i class="fa fa-minus"></i>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <select :id="item.id" class="form-control"
                                                                v-model="item.package" :name="'packages['+index+'][id]'">
                                                        </select>
                                                    </td>
                                                    <td class="text-end">
                                                        <input type="hidden" :value="item.price" :name="'packages['+index+'][price]'">
                                                        @{{ Number(item.price).toLocaleString('id-ID') }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2" class="fw-bold text-end">Total</td>
                                                    <td class="fw-bold text-end">@{{ Number(grandTotal).toLocaleString('id-ID') }}</td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <h4 class="fw-normal">
                                    Add On
                                </h4>
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
                                                           @click="addMoreAddons">
                                                            <i class="fa fa-plus"></i>
                                                        </a>
                                                    </th>
                                                    <th>Add On</th>
                                                    <th style="width: 5%">Qty</th>
                                                    <th style="width: 15%;">Harga</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <tr v-for="(item, index) in addons" :key="item.id">
                                                    <td class="text-center">
                                                        <a class="btn btn-danger text-center" style="cursor: pointer"
                                                           @click="removeAddons(item)">
                                                            <i class="fa fa-minus"></i>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <select :id="item.id" class="form-control"
                                                                v-model="item.item" :name="'items['+index+'][id]'">
                                                        </select>
                                                    </td>
                                                    <td class="text-end">
                                                        <vue-autonumeric class="form-control" :name="'items['+index+'][qty]'" v-model="item.qty" :options="autonumericFormat"></vue-autonumeric>
                                                    </td>
                                                    <td class="text-end">
                                                        <input type="hidden" :value="item.price" :name="'items['+index+'][price]'">
                                                        @{{ Number(item.price * item.qty).toLocaleString('id-ID') }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3" class="fw-bold text-end">Total</td>
                                                    <td class="fw-bold text-end">@{{ Number(grandTotal2).toLocaleString('id-ID') }}</td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive w-100">
                                    <table
                                        class="table table-bordered table-striped table-vcenter js-dataTable-full"
                                        id="table_data">
                                        <tbody>
                                        <tr>
                                            <td colspan="3" class="fw-bold text-end">Subtotal</td>
                                            <td class="fw-bold text-end" style="width: 20%">@{{ Number(subtotal).toLocaleString('id-ID') }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="fw-bold text-end">PB2 (10%)</td>
                                            <td class="fw-bold text-end">@{{ Number(pb2).toLocaleString('id-ID') }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="fw-bold text-end">Grand Total</td>
                                            <input type="hidden" name="grand_total" :value="finalTotal">
                                            <td class="fw-bold text-end">@{{ Number(finalTotal).toLocaleString('id-ID') }}</td>
                                        </tr>
                                        </tbody>
                                    </table>
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
        document.addEventListener("DOMContentLoaded", function(event) {
            const datePicker = flatpickr('.js-calendar-flatpickr', {
                dateFormat: "d-m-Y",
                locale: "id",
                minDate: "today",
                defaultDate: "today",
                onChange: function(selectedDates) {
                    const timePicker = document.querySelector('.js-flatpickr')._flatpickr;
                    if (timePicker) {
                        updateMinTime(timePicker, selectedDates[0]);
                        resetTimeIfInvalid(timePicker, selectedDates[0]);
                    }
                }
            });

            const timePicker = flatpickr('.js-flatpickr', {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                time_24hr: true,
                locale: "id",
                minTime: "10:00",
                maxTime: "22:00", // Set time interval to 30 minutes
                defaultDate: getValidDefaultTime(),
                onOpen: function(selectedDates, dateStr, instance) {
                    const selectedDate = datePicker.selectedDates[0];
                    updateMinTime(instance, selectedDate);
                },
                onClose: function(selectedDates, dateStr, instance) {
                    if (selectedDates.length > 0) {
                        const roundedTime = roundToNearest30(selectedDates[0]);
                        instance.setDate(roundedTime, true);
                    }
                }
            });

            function roundToNearest30(date) {
                let minutes = date.getMinutes();
                let roundedMinutes = Math.round(minutes / 30) * 30;

                if (roundedMinutes === 60) {
                    date.setHours(date.getHours() + 1);
                    roundedMinutes = 0;
                }

                date.setMinutes(roundedMinutes);
                return date;
            }

            function getValidDefaultTime() {
                const now = new Date();
                let currentHour = now.getHours();
                let currentMinute = now.getMinutes();

                if (currentHour < 10) {
                    return "10:00";
                } else if (currentHour >= 22) {
                    return "22:00";
                } else {
                    // Round up to the next 30-minute interval
                    let roundedMinutes = Math.ceil(currentMinute / 30) * 30;
                    if (roundedMinutes === 60) {
                        currentHour += 1;
                        roundedMinutes = 0;
                    }
                    return `${String(currentHour).padStart(2, '0')}:${String(roundedMinutes).padStart(2, '0')}`;
                }
            }

            function updateMinTime(instance, selectedDate) {
                const now = new Date();
                const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                const selectedDay = selectedDate ? new Date(selectedDate.getFullYear(), selectedDate.getMonth(),
                    selectedDate.getDate()) : null;

                if (selectedDay && selectedDay.getTime() === today.getTime()) {
                    let minSelectableTime = getValidDefaultTime();
                    instance.set("minTime", minSelectableTime);
                } else {
                    instance.set("minTime", "10:00");
                }
            }

            function resetTimeIfInvalid(instance, selectedDate) {
                const now = new Date();
                const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                const selectedDay = selectedDate ? new Date(selectedDate.getFullYear(), selectedDate.getMonth(),
                    selectedDate.getDate()) : null;

                if (selectedDay && selectedDay.getTime() === today.getTime()) {
                    let minSelectableTime = getValidDefaultTime();
                    let selectedTime = instance.input.value;

                    if (selectedTime < minSelectableTime) {
                        instance.setDate(minSelectableTime, true);
                    }
                }
            }
        });

        $(document).ready(function () {
            $('form').on('submit', function () {
                $('#btn_submit').hide();
                $('#btn_loading').show();
            });
        });

        const app = new Vue({
            el: '#app',
            data: {
                duration: 0,
                bookings: [],
                addons: [],
                countries: @json(\App\Libs\Utilities::getCountryCodes()),
                phoneCode: '+62',
                country: 'ID',
                gender: 'L',
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
                    decimalPlaces: 2,
                    modifyValueOnWheel: false,
                    unformatOnSubmit: true,
                    allowDecimalPadding: false,
                },
            },
            computed: {
                grandTotal: function() {
                    let total = 0;

                    if (this.bookings.length > 0) {
                        for (let i = 0; i < this.bookings.length; i++) {
                            total += Number(this.bookings[i].price);
                        }
                    }

                    return total;
                },

                grandTotal2: function() {
                    let total = 0;

                    if (this.addons.length > 0) {
                        for (let i = 0; i < this.addons.length; i++) {
                            total += Number(this.addons[i].price * this.addons[i].qty);
                        }
                    }

                    return total;
                },

                subtotal: function() {
                    let total = this.grandTotal + this.grandTotal2;

                    return total;
                },

                pb2: function (){
                    let pb2 = this.subtotal * 10/100;

                    return pb2;
                },

                finalTotal: function (){
                    let total = this.subtotal + this.pb2;

                    return total;
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
                    app.gender = customer.gender;
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
                    //     app.bookingMemberPhone = '';
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
                calculateSubtotal(item) {
                    return (item.qty || 0) * (item.price || 0);
                },
                addMoreDetail() {
                    let newItem = {
                        id: makeid(10),
                        package: '',
                        notes: '',
                        price: 0,
                    };

                    this.bookings.push(newItem);

                    this.$nextTick(() => {
                        let escapedId = CSS.escape(newItem.id);

                        $('#' + escapedId).select2({
                            placeholder: 'Pilih Treatment',
                            width: '100%',
                            minimumInputLength: 0,
                            ajax: {
                                url: '{{ route('admin.package.select') }}',
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

                            let response = await app.getPackageDetail(selectedId);
                            if (response) {
                                let index = app.bookings.findIndex(x => x.id === newItem.id);
                                if (index !== -1) {
                                    app.bookings[index].package = selectedId;
                                    app.bookings[index].price = response.price;
                                }
                            }
                        });
                    });
                },
                removeItem(item) {
                    this.bookings = this.bookings.filter(function(x) {
                        return x !== item;
                    });
                },

                addMoreAddons() {
                    let newItem = {
                        id: makeid(10),
                        item: '',
                        qty: 1,
                        notes: '',
                        price: 0,
                    };

                    this.addons.push(newItem);

                    this.$nextTick(() => {
                        let escapedId = CSS.escape(newItem.id);

                        $('#' + escapedId).select2({
                            placeholder: 'Pilih Add On',
                            width: '100%',
                            minimumInputLength: 0,
                            ajax: {
                                url: '{{ route('admin.item.selectGroup') }}',
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

                            let response = await app.getItemDetail(selectedId);
                            if (response) {
                                let index = app.addons.findIndex(x => x.id === newItem.id);
                                if (index !== -1) {
                                    app.addons[index].item = selectedId;
                                    app.addons[index].price = response.price;
                                }
                            }
                        });
                    });
                },
                removeAddons(item) {
                    this.addons = this.addons.filter(function(x) {
                        return x !== item;
                    });
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

        $('#form_create_booking').submit(function() {
            $('#btn_submit').hide();
            $('#btn_cancel').hide();
            $('#btn_loading').show(200);
        });
    </script>
@endsection
