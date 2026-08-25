@extends('layouts.admin')

@section('title')
    <title>Laporan Transaksi</title>
@endsection

@section('content')
    <!-- Hero -->
    <div class="bg-body-light">
        <div class="content content-full">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Laporan Transaksi
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Laporan Transaksi
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <div id="app" class="content">
        @include('partials._success')
        @include('partials._error')
        <div class="block block-rounded">
            <div class="block-content block-content-full">
                <div class="row mb-4">
                    <div class="col-sm-3 col-12">
                        <div class="col-sm-12 col-12">
                            <label class="form-label" for="end_date">Tipe</label>
                            <select class="form-select form-control-select2" id="type">
                                <option value="VOUCHER" @if($type == "VOUCHER") selected @endif>VOUCHER</option>
                                <option value="KAFE" @if($type == "KAFE") selected @endif>KAFE</option>
                                <option value="STUDIO" @if($type == "STUDIO") selected @endif>STUDIO</option>
                                <option value="SPA" @if($type == "SPA") selected @endif>SPA</option>
                                <option value="SALON" @if($type == "SALON") selected @endif>SALON</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-3 col-12">
                        <label class="form-label" for="end_date">Tanggal</label>
                        <input type="text" id="end_date" class="form-control js-flatpickr" data-date-format="d-m-Y" v-model="selectedDate"/>
                    </div>
                </div>
                <div class="row mt-6">
                    <p><strong>PERIODE:</strong> 2025</p>

                    <div v-if="selectedType === 'KAFE'">
                        <h2>KAFE</h2>
                        <table>
                            <tr>
                                <th class="text-center">No</th>
                                <th>Nama</th>
                                <th class="text-center">Qty Terjual</th>
                                <th class="text-center">Gross</th>
                                <th class="text-center">Tax</th>
                                <th class="text-center">Nett</th>
                            </tr>
                            <tr v-for="(detail, index) in result.details">
                                <td class="text-left">@{{ ++index }}</td>
                                <td class="text-left" style="width: 10%">@{{ detail.name }}</td>
                                <td class="text-end" style="width: 20%" v-text="parseInt(detail.qty).toLocaleString('id-ID')"></td>
                                <td class="text-end" style="width: 20%" v-text="parseInt(detail.amount).toLocaleString('id-ID')"></td>
                                <td class="text-end" style="width: 20%" v-text="parseInt(detail.tax).toLocaleString('id-ID')"></td>
                                <td class="text-end" style="width: 20%" v-text="parseInt(detail.grandtotal).toLocaleString('id-ID')"></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-center" style="font-weight: bold; font-size: 1.2em">
                                    Grandtotal
                                </td>
                                <td class="text-center" style="font-weight: bold; font-size: 1.2em" v-text="parseInt(result.totalGross).toLocaleString('id-ID')">
                                </td>
                                <td class="text-center" style="font-weight: bold; font-size: 1.2em" v-text="parseInt(result.totalTax).toLocaleString('id-ID')">
                                </td>
                                <td class="text-center" style="font-weight: bold; font-size: 1.2em" v-text="parseInt(result.totalNett).toLocaleString('id-ID')">
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div v-else>
                        <h2>@{{ selectedType }}</h2>
                        <table>
                            <tr>
                                <th>No</th>
                                <th>Kode</th>
                                <th>Customer</th>
                                <th>Tanggal</th>
                                <th>Diskon</th>
                                <th>Total</th>
                                <th>Total {{ $type }}</th>
                                <th>Tipe Pembayaran</th>
                                <th>Total Pembayaran</th>
                                <th>Dibuat oleh</th>
                            </tr>
                            <tr v-for="(header, index) in result.details">
                                <td class="text-left">@{{ ++index }}</td>
                                <td class="text-left">@{{ header.code }}</td>
                                <td class="text-left">@{{ header.customer }}</td>
                                <td class="text-left" style="width: 10%">@{{ header.date }}</td>
                                <td class="text-end" style="width: 20%" v-text="parseInt(header.discount).toLocaleString('id-ID')"></td>
                                <td class="text-end" style="width: 20%" v-text="parseInt(header.total).toLocaleString('id-ID')"></td>
                                <td class="text-end" style="width: 20%" v-text="parseInt(header.total_grouping).toLocaleString('id-ID')"></td>
                                <td class="text-left" style="width: 10%">@{{ header.payment_code }}</td>
                                <td class="text-end" style="width: 20%" v-text="parseInt(header.payment_amount).toLocaleString('id-ID')"></td>
                                <td class="text-left" style="width: 10%">@{{ header.created_by }}</td>
                            </tr>
                            <tr v-for="(header, index) in result.details">
                                <td></td>
                                <td>Details</td>
                                <td colspan="10">
                                    <table class="table table-bordered">
                                        <thead>
                                        <tr>
                                            <td>No</td>
                                            <td>Item</td>
                                            <td>Qty</td>
                                            <td>Price</td>
                                            <td>Subtotal</td>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="(detail, indexDetail) in header.details">
                                            <td class="text-left">@{{ ++indexDetail }}</td>
                                            <td class="text-left">@{{ detail.item_name }}</td>
                                            <td class="text-left">@{{ detail.qty }}</td>
                                            <td class="text-left">@{{ detail.price }}</td>
                                            <td class="text-left">@{{ detail.subtotal }}</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
<!-- Page JS Plugins CSS -->
    <link rel="stylesheet" href="{{ asset('js/plugins/flatpickr/flatpickr.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-bs5/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-buttons-bs5/buttons.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">


<style>
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
    }
    h2 {
        margin-top: 40px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 30px;
    }
    th, td {
        border: 1px solid #aaa;
        padding: 8px;
        text-align: center;
    }
    th {
        background-color: #f3f3f3;
    }
    .text-left {
        text-align: left;
    }
</style>
@endsection

@section('scripts')
    <!-- jQuery (required for DataTables plugin) -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script src="{{ asset('js/vue.js') }}"></script>
    <script src="{{ asset('js/axios.js') }}"></script>

    <!-- Page JS Plugins -->
    <script src="{{ asset('js/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-bs5/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('js/moment.min.js') }}"></script>
    <script src="{{ asset('js/moment-id.min.js') }}"></script>
    <script src="{{ asset('js/plugins/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
    <script src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('js/plugins/flatpickr/flatpickr.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>

    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

    <script type="text/x-template" id="select2-template">
        <select>
            <slot></slot>
        </select>
    </script>
    <script src="{{ asset('js/vueComponent-select2.js') }}"></script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        let flatpickrInstance = flatpickr("#end_date", {
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

                        const options = [
                            { label: "Today", value: "today" },
                            { label: "Yesterday", value: "yesterday" },
                            { label: "This Week", value: "this_week" },
                            { label: "Last Week", value: "last_week" },
                            { label: "This Month", value: "this_month" },
                            { label: "Last Month", value: "last_month" },
                            { label: "This Year", value: "this_year" },
                            { label: "Last Year", value: "last_year" }
                        ];

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
        // let flatpickrInstance = flatpickr("#end_date", {
        //     dateFormat: "m-Y",
        //     allowInput: true,
        //     mode: "single",
        //     plugins: [
        //         new monthSelectPlugin({
        //             shorthand: true,
        //             dateFormat: "m-Y",
        //             altFormat: "F Y"
        //         })
        //     ],
        //     onChange: function(selectedDates, dateStr, instance) {
        //         // Optionally, update Vue's selectedDate here if needed
        //     }
        // });

        function setDate(option, instance) {
            let today = new Date();
            let startDate, endDate;

            switch (option) {
                case "today":
                    startDate = endDate = today;
                    break;
                case "yesterday":
                    startDate = new Date(today);
                    startDate.setDate(today.getDate() - 1);
                    endDate = startDate;
                    break;
                case "this_week":
                    startDate = new Date(today);
                    startDate.setDate(today.getDate() - today.getDay()); // Start of week (Sunday)
                    endDate = new Date(startDate);
                    endDate.setDate(startDate.getDate() + 6); // End of week (Saturday)
                    break;
                case "last_week":
                    startDate = new Date(today);
                    startDate.setDate(today.getDate() - today.getDay() - 7); // Start of last week
                    endDate = new Date(startDate);
                    endDate.setDate(startDate.getDate() + 6); // End of last week
                    break;
                case "this_month":
                    startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                    endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0); // End of month
                    break;
                case "last_month":
                    startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                    endDate = new Date(today.getFullYear(), today.getMonth(), 0); // End of last month
                    break;
                case "this_year":
                    startDate = new Date(today.getFullYear(), 0, 1);
                    endDate = new Date(today.getFullYear(), 11, 31); // End of year
                    break;
                case "last_year":
                    startDate = new Date(today.getFullYear() - 1, 0, 1);
                    endDate = new Date(today.getFullYear() - 1, 11, 31); // End of last year
                    break;
            }

            if (startDate && endDate) {
                flatpickrInstance.setDate([startDate, endDate], true);
            }
            flatpickrInstance.close();
        }
    });
</script>

<script>
    let app = new Vue({
        el: '#app',
        data: {
            loading: true,
            selectedYear: 2025,
            selectedType: '{{ $type }}',
            result: null,
            selectedDate: '{{ $startDate }} to {{ $endDate }}',
            selectedStartDate : '{{ $startDate }}',
            selectedEndDate : '{{ $endDate }}',
        },
        watch: {
            selectedType(newVal, oldVal) {
                if (newVal !== oldVal) {
                    app.getData();
                }
            },
            selectedBranch(newVal, oldVal) {
                if (JSON.stringify(newVal) !== JSON.stringify(oldVal)) {
                    this.getData();
                }
            },
            selectedDate(newVal, oldVal) {
                if (newVal.includes(" to ")) {
                    const dateArray = newVal.split(" to ");
                    this.selectedStartDate = dateArray[0];
                    this.selectedEndDate = dateArray[1];
                } else {
                    // If only one date is selected
                    this.selectedStartDate = newVal;
                    this.selectedEndDate = newVal;
                }

                app.getData();
            },
        },
        computed: {},
        methods: {
            clearData() {
                app.result = null;
            },
            async getData() {
                app.loading = true;
                app.clearData();
                if(app.selectedType !== "") {
                    let url = "";
                    if(app.selectedType === "KAFE") {
                        url = '{{ route('admin.report.item.data') }}';
                    }
                    else{
                        url = '{{ route('admin.transaction.report.data') }}';
                    }
                    debugger;
                    $.ajax({
                        url: url,
                        type: 'get',
                        data: {
                            type: app.selectedType,
                            start_date: app.selectedStartDate,
                            end_date: app.selectedEndDate,
                        },
                        success: function(response) {
                            app.result = response;
                        },
                        error: function(xhr, status, error) {
                            console.log(error);
                        }
                    }).always( function() {
                        app.loading = false;
                    });
                }
            },
        },
        mounted() {
            const vm = this;
            $('#type').select2({ placeholder: "Pilih Tipe", width: '100%' });
            $('#type').val(vm.selectedType).trigger('change'); // sinkron awal
            $('#type').on('change', function () {
                vm.selectedType = $(this).val();
            });
            setTimeout(() => vm.getData(), 200);
        }

    });

</script>
@endsection
