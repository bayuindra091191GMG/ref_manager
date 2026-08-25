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
                        Laporan Acc Payment
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Laporan Acc Payment
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
                                <option value="ap_card" @if($type == "ap_card") selected @endif>AP CARD</option>
                                <option value="ap_aging" @if($type == "ap_aging") selected @endif>AP AGING</option>
                                <option value="ap_invoice_list" @if($type == "ap_invoice_list") selected @endif>AP INVOICE</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-3 col-12">
                        <label class="form-label" for="end_date">Tanggal</label>
                        <input type="text" id="end_date" class="form-control js-flatpickr" data-date-format="d-m-Y" v-model="selectedDate"/>
                    </div>
                </div>
                <div class="row mt-6" v-if="loading">
                    <div class="col-12">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                            </div>
                            <h3 class="">Mohon tunggu! Data anda sedang diproses.</h3>
                        </div>
                    </div>
                </div>
                <div class="row mt-6" v-if="!loading">
                    <div v-if="selectedType === 'ap_card'">
                        <h2>AP Card</h2>
                        <div v-if="result.details.length === 0">
                            <div class="col-12">
                                <div class="text-center">
                                    <h3 class="">Tidak ada transaksi pada tanggal ini.</h3>
                                </div>
                            </div>
                        </div>
                        <div v-else>
                            <div v-for="(item, index) in result.details">
                                <h6 style="font-weight: bold;">@{{ item.supplier_name }}</h6>
                            <table class="table table-bordered table-striped table-vcenter" style="font-size: 12px !important;">
                                <tr>
                                    <th class="col0-head">No </th>
                                    <th class="col1-head">Transaction Number</th>
                                    <th class="col1-head">Transaction Date</th>
                                    <th class="col10-head">Transaction Amount</th>
                                    <th class="col3-head">Paid No</th>
                                    <th class="col6-head">Paid Date</th>
                                    <th class="col10-head">Paid Amount</th>
                                    <th class="col10-head">Balance</th>
                                </tr>
                                <tr v-for="(detail, index) in item.transactions">
                                    <td class="text-left">@{{ ++index }}</td>
                                    <td class="text-left" style="width: 10%">@{{ detail.code }}</td>
                                    <td class="text-left" style="width: 10%">@{{ detail.date }}</td>
                                    <td class="text-end" style="width: 20%" v-text="parseInt(detail.grand_total).toLocaleString('id-ID')"></td>
                                    <td class="text-left" style="width: 10%">@{{ detail.paid_no }}</td>
                                    <td class="text-left" style="width: 10%">@{{ detail.paid_date }}</td>
                                    <td class="text-end" style="width: 20%" v-text="parseInt(detail.paid_amount).toLocaleString('id-ID')"></td>
                                    <td class="text-end" style="width: 20%" v-text="parseInt(detail.balance).toLocaleString('id-ID')"></td>
                                </tr>
                                <tr>
                                    <td colspan="3" style="text-align: right">Grandtotal</td>
                                    <td class="text-right" style="font-weight: bold; font-size: 1.2em" v-text="parseInt(result.subtotal_grand_total).toLocaleString('id-ID')">
                                    </td>
                                    <td colspan="2" style="text-align: right"></td>
                                    <td class="text-right" style="font-weight: bold; font-size: 1.2em" v-text="parseInt(result.subtotal_paid_amount).toLocaleString('id-ID')">
                                    </td>
                                </tr>
                            </table>
                            </div>
                        </div>
                    </div>

                    <div v-if="selectedType === 'ap_aging'">
                        <h2>AP Aging</h2>
                        <div v-if="result.details.length === 0">
                            <div class="col-12">
                                <div class="text-center">
                                    <h3 class="">Tidak ada transaksi pada tanggal ini.</h3>
                                </div>
                            </div>
                        </div>
                        <div v-else>
                            <div v-for="(item, index) in result.details">
                                <h6 style="font-weight: bold;">@{{ item.supplier_name }}</h6>
                                <table class="table table-bordered table-striped table-vcenter" style="font-size: 12px !important;">
                                    <tr>
                                        <th class="col0-head">No </th>
                                        <th class="col1-head">Transaction Number</th>
                                        <th class="col2-head">Transaction Date</th>
                                        <th class="col3-head">Invoiced</th>
                                        <th class="col10-head">Grand Total</th>
                                        <th class="col10-head">Paid Amount</th>
                                        <th class="col10-head">&lt;30</th>
                                        <th class="col10-head">30-59</th>
                                        <th class="col10-head">60-89</th>
                                        <th class="col10-head">90-119</th>
                                        <th class="col10-head">&gt;120</th>
                                    </tr>
                                    <tr v-for="(detail, index) in item.transactions">
                                        <td class="text-left">@{{ ++index }}</td>
                                        <td class="text-left" style="width: 10%">@{{ detail.code }}</td>
                                        <td class="text-left" style="width: 10%">@{{ detail.date }}</td>
                                        <td class="text-left" style="width: 10%">@{{ detail.invoiced }}</td>
                                        <td class="text-end" style="width: 20%" v-text="parseInt(detail.grand_total).toLocaleString('id-ID')"></td>
                                        <td class="text-end" style="width: 20%" v-text="parseInt(detail.paid_amount).toLocaleString('id-ID')"></td>
                                        <td class="text-end" style="width: 20%" v-text="parseInt(detail.t30).toLocaleString('id-ID')"></td>
                                        <td class="text-end" style="width: 20%" v-text="parseInt(detail.t60).toLocaleString('id-ID')"></td>
                                        <td class="text-end" style="width: 20%" v-text="parseInt(detail.t90).toLocaleString('id-ID')"></td>
                                        <td class="text-end" style="width: 20%" v-text="parseInt(detail.t120).toLocaleString('id-ID')"></td>
                                        <td class="text-end" style="width: 20%" v-text="parseInt(detail.t120_more).toLocaleString('id-ID')"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-center" style="font-weight: bold; font-size: 1.2em">
                                            Grandtotal
                                        </td>
                                        <td class="text-right" style="font-weight: bold; font-size: 1.2em" v-text="parseInt(item.subtotal_grand_total).toLocaleString('id-ID')">
                                        </td>
                                        <td class="text-right" style="font-weight: bold; font-size: 1.2em" v-text="parseInt(item.subtotal_paid_amount).toLocaleString('id-ID')">
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div v-if="selectedType === 'ap_invoice_list'">
                        <h2>AP INVOICE LIST</h2>
                        <div v-if="result.details.length === 0">
                            <div class="col-12">
                                <div class="text-center">
                                    <h3 class="">Tidak ada transaksi pada tanggal ini.</h3>
                                </div>
                            </div>
                        </div>
                        <div v-else>
                            <div v-for="(item, index) in result.details">
                                <h6 style="font-weight: bold;">@{{ item.supplier_name }}</h6>
                                <table class="table table-bordered table-striped table-vcenter" style="font-size: 12px !important;">
                                    <tr>
                                        <th class="col0-head">No </th>
                                        <th class="col1-head">Transaction Number</th>
                                        <th class="col2-head">Transaction Date</th>
                                        <th class="col5-head">Total</th>
                                        <th class="col5-head">Paid Amount</th>
                                    </tr>
                                    <template v-for="(header, index) in item.transactions">
                                        <tr>
                                            <td class="text-left">@{{ ++index }}</td>
                                            <td class="text-left">@{{ header.code }}</td>
                                            <td class="text-left">@{{ header.code }}</td>
                                            <td class="text-left" style="width: 10%">@{{ header.date }}</td>
                                            <td class="text-end" style="width: 20%" v-text="parseInt(header.grand_total).toLocaleString('id-ID')"></td>
                                            <td class="text-end" style="width: 20%" v-text="parseInt(header.paid_amount).toLocaleString('id-ID')"></td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-center" style="font-weight: bold; font-size: 1.2em">
                                                Grandtotal
                                            </td>
                                            <td class="text-right" style="font-weight: bold; font-size: 1.2em" v-text="parseInt(result.subtotal_grand_total).toLocaleString('id-ID')">
                                            </td>
                                            <td class="text-right" style="font-weight: bold; font-size: 1.2em" v-text="parseInt(result.subtotal_paid_amount).toLocaleString('id-ID')">
                                            </td>
                                        </tr>
                                    </template>
                                </table>
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
                    let url = '{{ route('admin.ap.report.data') }}';
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
