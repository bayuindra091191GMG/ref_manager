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
                                <option value="All" @if($type == "All") selected @endif>All</option>
                                <option value="Voucher" @if($type == "Voucher") selected @endif>Voucher</option>
                                <option value="Kafe" @if($type == "Kafe") selected @endif>Kafe</option>
                                <option value="Studio" @if($type == "Studio") selected @endif>Studio</option>
                                <option value="Spa, Massage, Reflexology" @if($type == "Spa, Massage, Reflexology") selected @endif>Spa, Massage, Reflexology</option>
                                <option value="Salon" @if($type == "Salon") selected @endif>Salon</option>
                                <option value="Treatment Tambahan" @if($type == "Treatment Tambahan") selected @endif>Treatment Tambahan</option>
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
                    <div v-if="selectedType === 'All'">
                        <h2>Semua Transaksi</h2>
                        <div v-if="result.details.length === 0">
                            <div class="col-12">
                                <div class="text-center">
                                    <h3 class="">Tidak ada transaksi pada tanggal ini.</h3>
                                </div>
                            </div>
                        </div>
                        <div v-else>
                            <div class="d-flex justify-content-end mb-3">
                                <button type="button" id="btn_print" class="btn btn-outline-info me-3" @click="printReport()">Print</button>
                                <button type="button" id="btn_print" class="btn btn-outline-success" @click="exportToExcel()">Excel</button>
                            </div>
                            <div id="main_container">
                                <div class="table-responsive w-100">
                                    <table class="table table-bordered table-striped table-vcenter nowrap" style="width: 1800px">
                                        <thead>
                                        <tr>
                                            <th>Kode</th>
                                            <th>Customer</th>
                                            <th>Tanggal</th>
                                            <th>Waktu</th>
                                            <th>Tipe Pembayaran</th>
                                            <th>Dibuat oleh</th>
                                            <td>Katerori</td>
                                            <td>Item</td>
                                            <td>Terapis</td>
                                            <td>Qty</td>
                                            <td>Price</td>
                                            <td>Voucher Name</td>
                                            <td>Diskon</td>
                                            <td>Subtotal Item</td>
                                            <td>Pajak Item</td>
                                            <td>Subtotal</td>
                                            <td>Pajak</td>
                                            <td>Grand Total</td>
                                            <td>Tipe Pembayaran</td>
                                            <td>Total Pembayaran</td>
                                        </tr>
                                        </thead>
                                        <tbody>
                                            <template v-for="(header, index) in result.details">
                                                <tr v-for="(detail, indexDetail) in header.details">
                                                    <template v-if="indexDetail == 0">
                                                        <td class="text-left">@{{ header.code }}</td>
                                                        <td class="text-left">@{{ header.customer }}</td>
                                                        <td class="text-left" style="width: 10%">@{{ detail.date }}</td>
                                                        <td class="text-left" style="width: 10%">@{{ detail.time }}</td>
                                                        <td class="text-left" style="width: 10%">@{{ header.payment_code }}</td>
                                                        <td class="text-left" style="width: 10%">@{{ header.created_by }}</td>
                                                        <td class="text-left">@{{ detail.category_name }}</td>
                                                        <td class="text-left">@{{ detail.item_name }}</td>
                                                        <td class="text-left">@{{ detail.partner_name }}</td>
                                                        <td class="text-left">@{{ detail.qty }}</td>
                                                        <td class="text-left">@{{ detail.price }}</td>
                                                        <td class="text-left">@{{ detail.voucher_name }}</td>
                                                        <td class="text-left" style="width: 10%">@{{ detail.discount_amount }}</td>
                                                        <td class="text-left">@{{ detail.subtotal }}</td>
                                                        <td class="text-left">@{{ detail.pb2 }}</td>
                                                        <td class="text-left">@{{ header.subtotal }}</td>
                                                        <td class="text-left">@{{ header.pb2 }}</td>
                                                        <td class="text-left">@{{ header.total }}</td>
                                                        <td class="text-left">@{{ header.payment_code }}</td>
                                                        <td class="text-left">@{{ header.payment_amount }}</td>
                                                    </template>
                                                    <template v-else>
                                                        <td class="text-left">@{{ header.code }}</td>
                                                        <td class="text-left">@{{ header.customer }}</td>
                                                        <td class="text-left" style="width: 10%">@{{ detail.date }}</td>
                                                        <td class="text-left" style="width: 10%">@{{ detail.time }}</td>
                                                        <td class="text-left" style="width: 10%">@{{ header.payment_code }}</td>
                                                        <td class="text-left" style="width: 10%">@{{ header.created_by }}</td>
                                                        <td class="text-left">@{{ detail.category_name }}</td>
                                                        <td class="text-left">@{{ detail.item_name }}</td>
                                                        <td class="text-left">@{{ detail.partner_name }}</td>
                                                        <td class="text-left">@{{ detail.qty }}</td>
                                                        <td class="text-left">@{{ detail.price }}</td>
                                                        <td class="text-left">@{{ detail.voucher_name }}</td>
                                                        <td class="text-left" style="width: 10%">@{{ detail.discount_amount }}</td>
                                                        <td class="text-left">@{{ detail.subtotal }}</td>
                                                        <td class="text-left">@{{ detail.pb2 }}</td>
                                                        <td colspan="5"></td>
                                                    </template>
                                                    {{-- <td class="text-left">@{{ header.code }}</td>
                                                    <td class="text-left">@{{ header.customer }}</td>
                                                    <td class="text-left" style="width: 10%">@{{ header.date }}</td>
                                                    <td class="text-left" style="width: 10%">@{{ header.payment_code }}</td>
                                                    <td class="text-left" style="width: 10%">@{{ header.created_by }}</td>
                                                    <td class="text-left">@{{ detail.item_name }}</td>
                                                    <td class="text-left">@{{ detail.partner_name }}</td>
                                                    <td class="text-left">@{{ detail.qty }}</td>
                                                    <td class="text-left">@{{ detail.price }}</td>
                                                    <td class="text-left">@{{ detail.voucher_name }}</td>
                                                    <td class="text-left" style="width: 10%">@{{ detail.discount_amount }}</td>
                                                    <td class="text-left">@{{ detail.subtotal }}</td>
                                                    <td class="text-left">@{{ header.subtotal }}</td>
                                                    <td class="text-left">@{{ header.pb2 }}</td>
                                                    <td class="text-left">@{{ header.total }}</td>
                                                    <td class="text-left">@{{ header.payment_code }}</td>
                                                    <td class="text-left">@{{ header.payment_amount }}</td> --}}
                                                </tr>
                                            </template>
                                        </tbody>
                                        {{-- <tfooter>
                                            <tr>
                                                <td colspan="10" class="text-center" style="font-weight: bold; font-size: 1.2em">
                                                    Grandtotal
                                                </td>
                                                <td class="text-center" style="font-weight: bold; font-size: 1.2em" v-text="parseInt(result.totalDiscount).toLocaleString('id-ID')">
                                                </td>
                                                <td class="text-center" style="font-weight: bold; font-size: 1.2em" v-text="parseInt(result.totalGross).toLocaleString('id-ID')">
                                                </td>
                                            </tr>
                                        </tfooter> --}}
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else>
                        <div v-if="selectedType === 'Kafe'">
                            <h2>Kafe</h2>
                            <div v-if="result.details.length === 0">
                                <div class="col-12">
                                    <div class="text-center">
                                        <h3 class="">Tidak ada transaksi pada tanggal ini.</h3>
                                    </div>
                                </div>
                            </div>
                            <div v-else>
                                <div class="d-flex justify-content-end mb-3">
                                    <button type="button" id="btn_print" class="btn btn-outline-info me-3" @click="printReport()">Print</button>
                                    <button type="button" id="btn_print" class="btn btn-outline-success" @click="exportToExcel()">Excel</button>
                                </div>
                                <div id="main_container">
                                    <table class="table table-bordered table-striped table-vcenter" style="font-size: 12px !important;">
                                        <tr>
                                            <th class="text-left">No</th>
                                            <td>Category</td>
                                            <th>Nama</th>
                                            <th class="text-end">Qty Terjual</th>
                                            <th class="text-end">Gross</th>
                                            <th class="text-end">Tax</th>
                                            <th class="text-end">Nett</th>
                                        </tr>
                                        <tr v-for="(detail, index) in result.details">
                                            <td class="text-left">@{{ ++index }}</td>
                                            <td class="text-left" style="width: 10%">@{{ detail.category_name }}</td>
                                            <td class="text-left" style="width: 10%">@{{ detail.name }}</td>
                                            <td class="text-left" style="width: 10%">@{{ detail.qty }}</td>
                                            {{-- <td class="text-end" style="width: 20%" v-text="parseInt(detail.qty).toLocaleString('id-ID')"></td> --}}
                                            <td class="text-end" style="width: 20%" v-text="parseInt(detail.amount).toLocaleString('id-ID')"></td>
                                            <td class="text-end" style="width: 20%" v-text="parseInt(detail.tax).toLocaleString('id-ID')"></td>
                                            <td class="text-end" style="width: 20%" v-text="parseInt(detail.grandtotal).toLocaleString('id-ID')"></td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-center" style="font-weight: bold; font-size: 1.2em">
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
                            </div>
                        </div>

                        <div v-else>
                            <h2>@{{ selectedType }}</h2>
                            <div v-if="result.details.length === 0">
                                <div class="col-12">
                                    <div class="text-center">
                                        <h3 class="">Tidak ada transaksi pada tanggal ini.</h3>
                                    </div>
                                </div>
                            </div>
                            <div v-else>
                                <div class="d-flex justify-content-end mb-3">
                                    <button type="button" id="btn_print" class="btn btn-outline-info me-3" @click="printReport()">Print</button>
                                    <button type="button" id="btn_print" class="btn btn-outline-success" @click="exportToExcel()">Excel</button>
                                </div>
                                <div id="main_container">
                                    <table class="table table-bordered table-striped table-vcenter" style="font-size: 12px !important;">
                                        <tr>
                                            <th>No</th>
                                            <th>Kode</th>
                                            <th>Customer</th>
                                            <th>Tanggal</th>
                                            <th>Waktu</th>
                                            <th>Total</th>
                                            <th>Tipe Pembayaran</th>
                                            <th>Total Pembayaran</th>
                                            <th>Dibuat oleh</th>
                                        </tr>
                                        <template v-for="(header, index) in result.details">
                                            <tr>
                                                <td class="text-left">@{{ ++index }}</td>
                                                <td class="text-left">@{{ header.code }}</td>
                                                <td class="text-left">@{{ header.customer }}</td>
                                                <td class="text-left" style="width: 10%">@{{ header.date }}</td>
                                                <td class="text-left" style="width: 10%">@{{ header.time }}</td>
                                                <td class="text-left" style="width: 10%">@{{ header.total_grouping }}</td>
                                                <td class="text-left" style="width: 10%">@{{ header.payment_code }}</td>
                                                <td class="text-left" style="width: 10%">@{{ header.total_grouping }}</td>
                                                <td class="text-left" style="width: 10%">@{{ header.created_by }}</td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td>Details</td>
                                                <td colspan="7">
                                                    <table class="table table-bordered">
                                                        <thead>
                                                        <tr>
                                                            <td>No</td>
                                                            <td>Category</td>
                                                            <td>Item</td>
                                                            <td>Terapis</td>
                                                            <td>Qty</td>
                                                            <td>Price</td>
                                                            <td>Voucher Name</td>
                                                            <td>Diskon</td>
                                                            <td>Subtotal Item</td>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        <tr v-for="(detail, indexDetail) in header.details">
                                                            <td class="text-left">@{{ ++indexDetail }}</td>
                                                            <td class="text-left">@{{ detail.category_name }}</td>
                                                            <td class="text-left">@{{ detail.item_name }}</td>
                                                            <td class="text-left">@{{ detail.partner_name }}</td>
                                                            <td class="text-left">@{{ detail.qty }}</td>
                                                            <td class="text-left">@{{ detail.price }}</td>
                                                            <td class="text-left">@{{ detail.voucher_name }}</td>
                                                            <td class="text-left" style="width: 10%">@{{ detail.discount_amount }}</td>
                                                            <td class="text-left">@{{ detail.subtotal }}</td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>

                                        </template>
                                        <tr>
                                            <td colspan="8" class="text-center" style="font-weight: bold; font-size: 1.2em">
                                                Grandtotal
                                            </td>
                                            <td class="text-center" style="font-weight: bold; font-size: 1.2em" v-text="parseInt(result.totalGross).toLocaleString('id-ID')">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="8" class="text-center" style="font-weight: bold; font-size: 1.2em">
                                                Grandtotal Diskon
                                            </td>
                                            <td class="text-center" style="font-weight: bold; font-size: 1.2em" v-text="parseInt(result.totalDiscount).toLocaleString('id-ID')">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="8" class="text-center" style="font-weight: bold; font-size: 1.2em">
                                                Grandtotal Pembayaran
                                            </td>
                                            <td class="text-center" style="font-weight: bold; font-size: 1.2em" v-text="parseInt(result.totalPayment).toLocaleString('id-ID')">
                                            </td>
                                        </tr>
                                    </table>
                                </div>
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/printThis/1.15.0/printThis.min.js"></script>

    <script type="text/javascript" src="https://cdn.sheetjs.com/xlsx-0.20.3/package/dist/xlsx.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/html2pdf.js@0.10.1/dist/html2pdf.bundle.min.js"></script>

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
            exportToExcel() {
                if (!this.result || !Array.isArray(this.result.details)) {
                    toastr?.error?.('Belum ada data untuk diekspor.');
                    return;
                }

                // === helper konversi angka lokal ke number ===
                const toNumber = (val) => {
                    if (val === null || val === undefined || val === '') return null;
                    if (typeof val === 'number') return val;

                    let s = String(val).trim();
                    if (!s) return null;

                    // Hapus karakter non-angka kecuali tanda . , dan -
                    s = s.replace(/[^\d.,\-]/g, '');

                    // Format: 1.234,56 → 1234.56
                    if (/^\d{1,3}(\.\d{3})*(,\d+)?$/.test(s))
                        s = s.replace(/\./g, '').replace(',', '.');
                    // Format: 1,234.56 → 1234.56
                    else if (/^\d{1,3}(,\d{3})+(\.\d+)?$/.test(s))
                        s = s.replace(/,/g, '');
                    // Format: 1234,56 → 1234.56
                    else if (/^\d+,\d+$/.test(s))
                        s = s.replace(',', '.');

                    const n = Number(s);
                    return isNaN(n) ? null : n;
                };

                const dateText = `${this.selectedStartDate}_${this.selectedEndDate}`;
                const safeFileName = dateText.replace(/[\\/:*?"<>|]/g, "_");

                // === Header gabungan (header + detail) ===
                const header = [
                    'No','Kode','Customer','Tanggal','Waktu','Dibuat oleh',
                    'Category','Item','Terapis','Qty','Harga',
                    'Nama Voucher', 'Diskon','Pajak Item', 'Subtotal Item',
                    'Subtotal','Pajak','Grand Total',
                    'Tipe Pembayaran','Total Pembayaran'
                ];

                const aoa = [header];
                let rowNo = 1;
                (this.result.details || []).forEach((h) => {
                    const baseRow = [
                        '', '', '', '', '', '', '', '', '', '', '', '','', '', '','', '', '', '', '', ''
                    ];

                    (h?.details || []).forEach((d, idx) => {
                        const therapistName = d?.partner_name || '';

                        // Row data
                        const row = [...baseRow];

                        if (idx === 0) {
                            // Isi data header hanya di baris pertama per header
                            row[0]  = rowNo++;
                            row[1]  = h?.code ?? '';
                            row[2]  = h?.customer ?? '';
                            row[3]  = h?.date ?? '';
                            row[4]  = h?.time ?? '';
                            row[5]  = h?.created_by ?? '';
                            row[6]  = d?.category_name ?? '';
                            row[7]  = d?.item_name ?? '';
                            row[8]  = therapistName;
                            row[9]  = toNumber(d?.qty);
                            row[10]  = toNumber(d?.price);
                            row[11]  = d?.voucher_name ?? '';
                            row[12]  = toNumber(d?.discount_amount);
                            row[13]  = toNumber(d?.pb2);
                            row[14]  = toNumber(d?.subtotal);
                            row[15] = toNumber(h?.subtotal);
                            row[16] = toNumber(h?.pb2);
                            row[17] = toNumber(h?.total);
                            row[18] = h?.payment_code ?? '';
                            row[19] = toNumber(h?.payment_amount);
                        } else {
                            // Hanya detail produk di baris berikutnya
                            row[0]  = rowNo++;
                            row[1]  = h?.code ?? '';
                            row[2]  = h?.customer ?? '';
                            row[3]  = h?.date ?? '';
                            row[4]  = h?.time ?? '';
                            row[5]  = h?.created_by ?? '';
                            row[6]  = d?.category_name ?? '';
                            row[7]  = d?.item_name ?? '';
                            row[8]  = therapistName;
                            row[9]  = toNumber(d?.qty);
                            row[10]  = toNumber(d?.price);
                            row[11]  = d?.voucher_name ?? '';
                            row[12]  = toNumber(d?.discount_amount);
                            row[13]  = toNumber(d?.pb2);
                            row[14]  = toNumber(d?.subtotal);
                        }

                        aoa.push(row);
                    });
                });


                const wb = XLSX.utils.book_new();
                const ws = XLSX.utils.aoa_to_sheet(aoa);

                // === Auto width ===
                if (ws['!ref']) {
                    const range = XLSX.utils.decode_range(ws['!ref']);
                    const cols = [];

                    for (let C = range.s.c; C <= range.e.c; C++) {
                        let maxW = 10;
                        for (let R = range.s.r; R <= range.e.r; R++) {
                            const ref = XLSX.utils.encode_cell({ c: C, r: R });
                            const cell = ws[ref];
                            if (!cell || cell.v == null) continue;
                            const str = String(cell.v);
                            const longest = str.split(/\r?\n/).reduce((m, s) => Math.max(m, s.length), 0);
                            maxW = Math.max(maxW, longest);
                        }
                        cols.push({ wch: maxW + 2 });
                    }
                    ws['!cols'] = cols;
                }

                // === Number formatting ===
                const numColsInt  = new Set([9]); // Qty
                const numColsCur  = new Set([10, 12, 13, 14, 15, 16, 17, 19]); // Price, Total Item, Pajak item, Subtotal, Diskon, Pajak, Grand Total, Total Pembayaran

                if (ws['!ref']) {
                    const range = XLSX.utils.decode_range(ws['!ref']);
                    for (let R = 1; R <= range.e.r; R++) { // skip header
                        for (let C = range.s.c; C <= range.e.c; C++) {
                            const ref = XLSX.utils.encode_cell({ c: C, r: R });
                            const cell = ws[ref];
                            if (!cell || typeof cell.v !== 'number') continue;
                            if (numColsInt.has(C)) cell.z = '#,##0';
                            if (numColsCur.has(C)) cell.z = '#,##0.00';
                        }
                    }
                }

                XLSX.utils.book_append_sheet(wb, ws, (this.selectedType || 'Report').substring(0, 31));
                XLSX.writeFile(wb, `Laporan Transaksi ${this.selectedType} ${safeFileName}.xlsx`);
            },

            printReport()
            {
                $("#main_container").printThis({
                    importCSS: true,       // ikutkan CSS yang ada
                    importStyle: true,     // ikutkan style <style> di head
                    printContainer: true,  // print container juga
                    loadCSS: "",           // bisa kasih file css tambahan kalau perlu
                    pageTitle: "Laporan Transaksi",  // judul di tab print
                });
            },
            niceFileDate() {
                const s = (this.selectedStartDate || '').replaceAll('/', '-');
                const e = (this.selectedEndDate || '').replaceAll('/', '-');
                return s && e ? `${s}_to_${e}` : (s || e || '');
            },

            clearData() {
                app.result = null;
            },
            async getData() {
                app.loading = true;
                app.clearData();
                if(app.selectedType !== "") {
                    let url = "";
                    if(app.selectedType === "Kafe") {
                        url = '{{ route('admin.report.item.data') }}';
                    }
                    else{
                        url = '{{ route('admin.transaction.report.data') }}';
                    }
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
