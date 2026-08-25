@extends('layouts.admin')

@section('title')
    <title>Laporan General Ledger</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Laporan General Ledger
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Laporan General Ledger
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <div id="app" class="content">
        <div class="block block-rounded">
            <div class="block-content block-content-full">
                <div class="row mb-4">
                    <div class="col-sm-3 col-12">
                        <div class="col-sm-12 col-12">
                            <label class="form-label" for="end_date">Tanggal</label>
                            <input type="text" id="end_date" class="form-control js-flatpickr" data-date-format="d-m-Y" v-model="selectedDate"/>
                        </div>
                    </div>
                    <div class="col-sm-3 col-12">
                        <div class="col-sm-12 col-12">
                            <label class="form-label" for="end_date">Category</label>
                            <select name="category" id="category" class="form-control" v-model="selectedCategory">
                                @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1 ||
                                       (auth()->guard('admin')->user()->employee->employee_position->hasAnyPermission([
                                           'general_ledger.cashflow',
                                    ])))
                                    <option value="cashflow">Arus Kas</option>
                                @endif
                                @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1 ||
                                       (auth()->guard('admin')->user()->employee->employee_position->hasAnyPermission([
                                           'general_ledger.profit_loss',
                                    ])))
                                    <option value="profit_loss">Laba Rugi</option>
                                @endif
                                @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1 ||
                                       (auth()->guard('admin')->user()->employee->employee_position->hasAnyPermission([
                                           'general_ledger.balance_sheet',
                                    ])))
                                    <option value="balance_sheet">Neraca</option>
                                @endif
                                @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1 ||
                                       (auth()->guard('admin')->user()->employee->employee_position->hasAnyPermission([
                                           'general_ledger.ledger',
                                    ])))
                                    <option value="ledger">Buku Besar</option>
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6 col-12 text-end">
                        <button class="btn btn-outline-primary" :disabled="loading" @click="printTable">
                            <i class="fa fa-print me-1"></i> Print / Save PDF
                        </button>
                        <button class="btn btn-outline-success" :disabled="loading" @click="exportToExcel">
                            <i class="fa fa-file-excel-o me-1"></i> Export Excel
                        </button>
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
                <div id="cashflowContainer" class="row" v-if="selectedCategory == 'cashflow' && !loading">
                    <div class="container-fluid ">
                        <div class="row">
                            <div class="col-2">
                                <h6>Cash In </h6>
                            </div>
                        </div>
                    </div>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12 ">
                                <div class="table-responsive w-100">
                                    <table class="table table-bordered table-striped table-vcenter nowrap" style="width: 1800px">
                                        <thead>
                                            <tr class="text-center font-weight-bold p-rl-0">
                                                <th class="col0-head">No </th>
                                                <th class="col1-head">Account</th>
                                                <th class="col5-head">Jan-@{{ cashflow.year }}</th>
                                                <th class="col5-head">Feb-@{{ cashflow.year }}</th>
                                                <th class="col5-head">Mar-@{{ cashflow.year }}</th>
                                                <th class="col5-head">Apr-@{{ cashflow.year }}</th>
                                                <th class="col5-head">May-@{{ cashflow.year }}</th>
                                                <th class="col5-head">Jun-@{{ cashflow.year }}</th>
                                                <th class="col5-head">Jul-@{{ cashflow.year }}</th>
                                                <th class="col5-head">Aug-@{{ cashflow.year }}</th>
                                                <th class="col5-head">Sept-@{{ cashflow.year }}</th>
                                                <th class="col5-head">Oct-@{{ cashflow.year }}</th>
                                                <th class="col5-head">Nov-@{{ cashflow.year }}</th>
                                                <th class="col5-head">Dec-@{{ cashflow.year }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(item, index) in cashflow.result.transaction_cash_in">
                                                <td>@{{ item['index'] }}</td>
                                                <th>
                                                    <a href="#" class="ledger" @click.prevent="showLedger(item.id)">
                                                        @{{ item['coa_code'] }} @{{ item['coa_name'] }}
                                                    </a>
                                                </th>
                                                <td>@{{ item['01'].toLocaleString('id-ID') }}</td>
                                                <td>@{{ item['02'].toLocaleString('id-ID') }}</td>
                                                <td>@{{ item['03'].toLocaleString('id-ID') }}</td>
                                                <td>@{{ item['04'].toLocaleString('id-ID') }}</td>
                                                <td>@{{ item['05'].toLocaleString('id-ID') }}</td>
                                                <td>@{{ item['06'].toLocaleString('id-ID') }}</td>
                                                <td>@{{ item['07'].toLocaleString('id-ID') }}</td>
                                                <td>@{{ item['08'].toLocaleString('id-ID') }}</td>
                                                <td>@{{ item['09'].toLocaleString('id-ID') }}</td>
                                                <td>@{{ item['10'].toLocaleString('id-ID') }}</td>
                                                <td>@{{ item['11'].toLocaleString('id-ID') }}</td>
                                                <td>@{{ item['12'].toLocaleString('id-ID') }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="container-fluid mt-5">
                        <div class="row">
                            <div class="col-2">
                                <h6>Cash Out </h6>
                            </div>
                        </div>
                    </div>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12">
                                <div class="table-responsive w-100">
                                    <table class="table table-bordered table-striped table-vcenter nowrap" style="width: 1800px">
                                        <thead>
                                            <tr class="text-center font-weight-bold p-rl-0">
                                                <th class="col0-head">No </th>
                                                <th class="col1-head">Account</th>
                                                {{-- <th class="col5-head">Account Name</th> --}}
                                                <th class="col5-head">Jan-@{{ cashflow.year }}</th>
                                                <th class="col5-head">Feb-@{{ cashflow.year }}</th>
                                                <th class="col5-head">Mar-@{{ cashflow.year }}</th>
                                                <th class="col5-head">Apr-@{{ cashflow.year }}</th>
                                                <th class="col5-head">May-@{{ cashflow.year }}</th>
                                                <th class="col5-head">Jun-@{{ cashflow.year }}</th>
                                                <th class="col5-head">Jul-@{{ cashflow.year }}</th>
                                                <th class="col5-head">Aug-@{{ cashflow.year }}</th>
                                                <th class="col5-head">Sept-@{{ cashflow.year }}</th>
                                                <th class="col5-head">Oct-@{{ cashflow.year }}</th>
                                                <th class="col5-head">Nov-@{{ cashflow.year }}</th>
                                                <th class="col5-head">Dec-@{{ cashflow.year }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(item, index) in cashflow.result.transaction_cash_out">
                                                <td>@{{ item['index'] }}</td>
                                                <th>
                                                    <a href="#" class="ledger" @click.prevent="showLedger(item.id)">
                                                        @{{ item['coa_code'] }} @{{ item['coa_name'] }}
                                                    </a>
                                                </th>
                                                <td>@{{ item['01'].toLocaleString('id-ID') }}</td>
                                                <td>@{{ item['02'].toLocaleString('id-ID') }}</td>
                                                <td>@{{ item['03'].toLocaleString('id-ID') }}</td>
                                                <td>@{{ item['04'].toLocaleString('id-ID') }}</td>
                                                <td>@{{ item['05'].toLocaleString('id-ID') }}</td>
                                                <td>@{{ item['06'].toLocaleString('id-ID') }}</td>
                                                <td>@{{ item['07'].toLocaleString('id-ID') }}</td>
                                                <td>@{{ item['08'].toLocaleString('id-ID') }}</td>
                                                <td>@{{ item['09'].toLocaleString('id-ID') }}</td>
                                                <td>@{{ item['10'].toLocaleString('id-ID') }}</td>
                                                <td>@{{ item['11'].toLocaleString('id-ID') }}</td>
                                                <td>@{{ item['12'].toLocaleString('id-ID') }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="profitLossContainer" class="row" v-if="selectedCategory == 'profit_loss' && !loading">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12">
                                <div class="table-responsive w-100">
                                    <table class="table table-bordered table-striped table-vcenter nowrap">
                                        <thead>
                                        <tr>
                                            <th></th>
                                            <th v-for="m in profitLoss.range" :key="m" style="width: 150px" class="text-center">@{{ months[m] }}-@{{ profitLoss.year }}</th>
                                            <th style="width: 175px" class="text-center">Total</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <template v-for="(data, index) in profitLoss.result">
                                            <!-- Header Row -->
                                            <tr>
                                                <th>
                                                    <a href="#" class="ledger" @click.prevent="showLedger(data.id)">
                                                        (@{{ data.coa_code }}) @{{ data.coa_name }}
                                                    </a>
                                                </th>
                                                <template v-if="data.total === 0 && data.child">
                                                    <th :colspan="profitLoss.range.length + 1"></th>
                                                </template>
                                                <template v-else>
                                                    <th v-for="d in data.data">Rp. @{{ d.total }}</th>
                                                    <th>Rp. @{{ data.total }}</th>
                                                </template>
                                            </tr>

                                            <!-- Child Rows -->
                                            <template v-if="data.child">
                                                <template v-for="row in renderChild(data.child, 5)">
                                                    <tr v-html="row"></tr>
                                                </template>
                                            </template>

                                            <!-- Total per Header -->
                                            <tr>
                                                <th>
                                                    Total @{{ data.name }}
                                                </th>
                                                <th v-for="t in data.header_total" >
                                                    Rp. @{{ t.toLocaleString('id-ID') }}
                                                </th>
                                                <th>
                                                    Rp. @{{ sumArray(data.header_total).toLocaleString('id-ID') }}
                                                </th>
                                            </tr>

                                            <!-- Spacer -->
                                            <tr><td :colspan="profitLoss.range.length + 2"></td></tr>
                                        </template>

                                        <!-- Total Expense -->
                                        <tr>
                                            <th class="fw-bold">TOTAL EXPENSE</th>
                                            <th v-for="t in profitLoss.extraTotal.expense" class="fw-bold">
                                                Rp. @{{ t.toLocaleString('id-ID') }}
                                            </th>
                                            <th class="fw-bold">
                                                Rp. @{{ sumArray(profitLoss.extraTotal.expense).toLocaleString('id-ID') }}
                                            </th>
                                        </tr>

                                        <!-- Net Profit -->
                                        <tr>
                                            <th class="fw-bold">NETT PROFIT/LOSS</th>
                                            <th v-for="t in profitLoss.extraTotal.nett" class="fw-bold">
                                                Rp. @{{ t.toLocaleString('id-ID') }}
                                            </th>
                                            <th class="fw-bold">
                                                Rp. @{{ sumArray(profitLoss.extraTotal.nett).toLocaleString('id-ID') }}
                                            </th>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="balanceSheetContainer" class="row" v-if="selectedCategory == 'balance_sheet' && !loading">
                    <!-- Aktiva Section -->
                    <div class="col-6">
                        <div class="row">
                            <div class="col-2 fw-bold" style="font-size: 16px;">
                                <p>Aktiva</p>
                            </div>
                        </div>
                        <table class="table table-bordered table-striped table-vcenter nowrap">
                            <thead>
                            <tr class="text-center fw-bold" style="font-size: 13px;">
                                <th>No</th>
                                <th>Account</th>
                                <th>This Period</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(data, index) in balanceSheet.aktiva" :key="'a' + data.id">
                                <td class="text-center">@{{ index + 1 }}</td>
                                <td>
                                    <a href="#" class="ledger" @click.prevent="showLedger(data.id)">
                                        (@{{ data.coa_code }}) @{{ data.coa_name }}
                                    </a>
                                </td>
                                <td class="text-end">@{{ data.total.toLocaleString('id-ID') }}</td>
                            </tr>
                            <tr>
                                <td class="text-end fw-bold" colspan="2">Total Aktiva</td>
                                <td class="text-end fw-bold">
                                    @{{ totalAktiva.toLocaleString('id-ID') }}
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pasiva Section -->
                    <div class="col-6">
                        <div class="row">
                            <div class="col-2 fw-bold" style="font-size: 16px;">
                                <p>Pasiva</p>
                            </div>
                        </div>
                        <table class="table table-bordered table-striped table-vcenter nowrap">
                            <thead>
                            <tr class="text-center fw-bold" style="font-size: 13px;">
                                <th>No</th>
                                <th>Account</th>
                                <th>This Period</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(data, index) in balanceSheet.pasiva" :key="'p' + data.id">
                                <td class="text-center">@{{ index + 1 }}</td>
                                <td>
                                    <a href="#" class="ledger" @click.prevent="showLedger(data.id)">
                                        (@{{ data.coa_code }}) @{{ data.coa_name }}
                                    </a>
                                </td>
                                <td class="text-end">@{{ data.total.toLocaleString('id-ID') }}</td>
                            </tr>
                            <tr>
                                <td class="text-end fw-bold" colspan="2">Total Pasiva</td>
                                <td class="text-end fw-bold">
                                    @{{ totalPasiva.toLocaleString('id-ID') }}
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="ledgerContainer" class="row" v-if="selectedCategory == 'ledger' && !loading">
                    <div v-for="(resultData, index) in ledger.result" :key="index" class="mb-3">
                        <div class="row">
                            <div class="col-12">
                                <h6 class="mb-1 fw-bold">
                                    @{{ resultData.coa_code }} - @{{ resultData.coa_name }}
                                </h6>
                            </div>
                        </div>

                        <div class="row pt-1">
                            <div class="col-12">
                                <div class="table-responsive w-100">
                                    <table class="table table-bordered table-striped table-vcenter nowrap">
                                        <thead class="text-center fw-bold">
                                        <tr>
                                            <th scope="col">Date</th>
                                            <th scope="col">Kode Transaksi</th>
                                            <th scope="col">Ref. No</th>
                                            <th scope="col">Description</th>
                                            <th scope="col">Debit</th>
                                            <th scope="col">Credit</th>
                                            <th scope="col">Saldo</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="(data, i) in resultData.data" :key="i">
                                            <td>@{{ data.transaction_date }}</td>
                                            <td>@{{ data.transaction_code }}</td>
                                            <td>@{{ data.reference_code }}</td>
                                            <td>@{{ data.notes }}</td>
                                            <td class="text-end">@{{ (parseInt(data.debit) || 0).toLocaleString('id-ID') }}</td>
                                            <td class="text-end">@{{ (parseInt(data.credit) || 0).toLocaleString('id-ID') }}</td>
                                            <td class="text-end">@{{ (parseInt(data.saldo) || 0).toLocaleString('id-ID') }}</td>
                                        </tr>
                                        <tr class="fw-bold">
                                            <td colspan="4" class="text-end">Total</td>
                                            <td class="text-end">
                                                @{{ (totalDebit(resultData.data) || 0).toLocaleString('id-ID') }}
                                            </td>
                                            <td class="text-end">
                                                @{{ (totalCredit(resultData.data) || 0).toLocaleString('id-ID') }}
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
@endsection

@section('styles')
    <!-- Page JS Plugins CSS -->
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/flatpickr/flatpickr.css') }}">

    <style>
        /* Print only the current report table (the one shown by v-if) */
        @media print {
            /* hide everything by default */
            body * {
                visibility: hidden !important;
            }

            /* show report containers AND all of their children */
            #cashflowContainer, #cashflowContainer *,
            #profitLossContainer, #profitLossContainer *,
            #balanceSheetContainer, #balanceSheetContainer *,
            #ledgerContainer, #ledgerContainer * {
                visibility: visible !important;
            }

            /* position the visible report at the top-left for a clean print */
            #cashflowContainer,
            #profitLossContainer,
            #balanceSheetContainer,
            #ledgerContainer {
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                width: 100% !important;
            }

            /* optional: improve table printing */
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
            th, td { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
@endsection

@section('scripts')
    <!-- jQuery -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script src="{{ asset('js/vue.js') }}"></script>
    <script src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('js/plugins/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('js/plugins/flatpickr/l10n/id.js') }}"></script>
    <!-- use xlsx.full.min.js from version 0.20.3 -->
    <script type="text/javascript" src="https://cdn.sheetjs.com/xlsx-0.20.3/package/dist/xlsx.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/html2pdf.js@0.10.1/dist/html2pdf.bundle.min.js"></script>

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
                cashflow: null,
                profitLoss: null,
                balanceSheet: null,
                ledger: null,
                selectedCategory: 'profit_loss',
                selectedDate: '{{ $filterStartDate }} to {{ $filterEndDate }}',
                selectedStartDate : '{{ $filterStartDate }}',
                selectedEndDate : '{{ $filterEndDate }}',
                months: {
                    '1': 'Jan', '2': 'Feb', '3': 'Mar', '4': 'Apr',
                    '5': 'May', '6': 'Jun', '7': 'Jul', '8': 'Aug',
                    '9': 'Sept', '10': 'Oct', '11': 'Nov', '12': 'Dec',
                },
            },
            watch: {
                selectedCategory(newVal, oldVal) {
                    if (newVal !== oldVal) {
                        app.getData();
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
            computed: {
                totalAktiva() {
                    if (!this.balanceSheet || !Array.isArray(this.balanceSheet.aktiva)) return 0;
                    return this.balanceSheet.aktiva
                        .filter(d => d.level === 1)
                        .reduce((sum, d) => sum + (d.total || 0), 0);
                },
                totalPasiva() {
                    if (!this.balanceSheet || !Array.isArray(this.balanceSheet.pasiva)) return 0;
                    return this.balanceSheet.pasiva
                        .filter(d => d.level === 1)
                        .reduce((sum, d) => sum + (d.total || 0), 0);
                }
            },
            methods: {
                showLedger(id) {
                    console.log("CLICK", id);
                    // ambil tanggal dari v-model
                    let start = this.selectedStartDate || '';
                    let end = this.selectedEndDate || '';

                    // encode agar aman dikirim via URL
                    start = encodeURIComponent(start);
                    end = encodeURIComponent(end);

                    // URL menuju filter
                    const url = "{{ route('admin.general_ledger.report.filter') }}"
                        + `?start_date=${start}&end_date=${end}&coa_id=${id}&type=ledger`;

                    // buka tab baru
                    window.open(url, "_blank");
                },
                clearData() {
                    app.profitLoss = null;
                    app.balanceSheet = null;
                    app.ledger = null;
                    app.cashflow = null;
                },
                async getData() {
                    app.loading = true;
                    app.clearData();

                    $.ajax({
                        url: '{{ route('admin.general_ledger.report.data') }}',
                        type: 'get',
                        data: {
                            category: app.selectedCategory,
                            start_date: app.selectedStartDate,
                            end_date: app.selectedEndDate,
                        },
                        success: function(response) {
                            if (app.selectedCategory === 'profit_loss') {
                                app.profitLoss = response;
                            } else if (app.selectedCategory === 'balance_sheet') {
                                app.balanceSheet = response;
                            } else if (app.selectedCategory === 'ledger') {
                                app.ledger = response;
                            } else if (app.selectedCategory === 'cashflow') {
                                app.cashflow = response;
                            }
                        },
                        error: function(xhr, status, error) {
                            console.log(error);
                        }
                    }).always( function() {
                        app.loading = false;
                    });
                },
                sumArray(arr) {
                    return Object.values(arr).reduce((a, b) => a + b, 0);
                },
                renderChild(children, indent) {
                    return Object.values(children).map(data => {
                        let row = `
                            <th class="ps-5">
                                <a href="#" class="ledger" @click.prevent="showLedger(${data.id})">
                                    (${data.code}) ${data.name}
                                </a>
                            </th>`;


                        if (data.total === 0 && data.child) {
                            row += `<th colspan='${app.profitLoss.range.length + 1}'></th>`;
                        } else if (data.data && typeof data.data === 'object') {
                            Object.values(data.data).forEach(d => {
                                row += `<th>Rp. ${d.total.toLocaleString('id-ID')}</th>`;
                            });
                            row += `<th>Rp. ${data.total.toLocaleString('id-ID')}</th>`;
                        } else {
                            // fallback if data.data is not an array
                            row += `<th colspan='${app.profitLoss.range.length + 1}'>Invalid data</th>`;
                        }

                        return `<tr>${row}</tr>`;
                    });
                },
                totalDebit(data) {
                    if (!Array.isArray(data)) return 0;
                    return data.reduce((sum, d) => sum + (parseInt(d.debit) || 0), 0);
                },
                totalCredit(data) {
                    if (!Array.isArray(data)) return 0;
                    return data.reduce((sum, d) => sum + (parseInt(d.credit) || 0), 0);
                },
                getActiveSectionEl() {
                    if (this.selectedCategory === 'profit_loss') return document.getElementById('profitLossContainer');
                    if (this.selectedCategory === 'balance_sheet') return document.getElementById('balanceSheetContainer');
                    if (this.selectedCategory === 'ledger') return document.getElementById('ledgerContainer');
                    return null;
                },
                niceFileDate() {
                    const s = (this.selectedStartDate || '').replaceAll('/', '-');
                    const e = (this.selectedEndDate || '').replaceAll('/', '-');
                    return s && e ? `${s}_to_${e}` : (s || e || '');
                },
                exportToPDF() {
                    const el = this.getActiveSectionEl();
                    if (!el) return;

                    // Build a clean title
                    const titles = {
                        profit_loss: 'Profit_and_Loss',
                        balance_sheet: 'Balance_Sheet',
                        ledger: 'General_Ledger',
                        cashflow: 'Cashflow'
                    };
                    const fileName = `${titles[this.selectedCategory]}_${this.niceFileDate() || 'export'}.pdf`;

                    // Use html2pdf
                    const opt = {
                        margin:       [10, 10, 10, 10],
                        filename:     fileName,
                        image:        { type: 'jpeg', quality: 0.95 },
                        html2canvas:  { scale: 2, useCORS: true },
                        jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' } // landscape fits wide tables
                    };

                    // Clone to avoid exporting hidden columns due to responsive tables
                    const clone = el.cloneNode(true);
                    // Optional: ensure table takes full width in export
                    clone.style.width = '100%';
                    clone.querySelectorAll('table').forEach(t => t.style.width = '100%');

                    html2pdf().from(clone).set(opt).save();
                },
                exportToExcel() {
                    if (typeof XLSX === 'undefined') {
                        alert("SheetJS (XLSX) not loaded");
                        return;
                    }

                    const titles = {
                        profit_loss: 'Profit_and_Loss',
                        balance_sheet: 'Balance_Sheet',
                        ledger: 'General_Ledger',
                        cashflow: 'Cashflow'
                    };
                    const fileName = `${titles[this.selectedCategory]}_${this.niceFileDate() || 'export'}.xlsx`;

                    const el = this.getActiveSectionEl();
                    if (!el) return;

                    // helpers
                    const wsEnsureRef = (ws) => { if (!ws['!ref']) ws['!ref'] = 'A1'; };
                    const aoaMaxCols = (aoa) => Math.max(0, ...aoa.map(r => r.length));
                    const sheetAddAOA = (ws, aoa, origin) => {
                        XLSX.utils.sheet_add_aoa(ws, aoa, { origin });
                        wsEnsureRef(ws);
                    };

                    this.$nextTick(() => {
                        // Start with an empty sheet
                        const ws = XLSX.utils.aoa_to_sheet([[]]); ws['!ref'] = 'A1';

                        if (this.selectedCategory === 'balance_sheet') {
                            // Grab the two tables (Aktiva left, Pasiva right)
                            const tables = el.querySelectorAll('table');
                            if (!tables.length) return;

                            // Convert each table -> AOA (array of arrays)
                            const aktivaAOA = tables[0] ? XLSX.utils.sheet_to_json(XLSX.utils.table_to_sheet(tables[0], { raw: true }), { header: 1, raw: true }) : [];
                            const pasivaAOA = tables[1] ? XLSX.utils.sheet_to_json(XLSX.utils.table_to_sheet(tables[1], { raw: true }), { header: 1, raw: true }) : [];

                            // Place left at A1
                            sheetAddAOA(ws, [[`Aktiva`]], { r: 0, c: 0 });
                            sheetAddAOA(ws, aktivaAOA, { r: 1, c: 0 });

                            // Place right starting a few columns to the right of Aktiva
                            const leftCols = Math.max(1, aoaMaxCols(aktivaAOA));
                            const gap = 1; // visual gap columns
                            const startColRight = leftCols + gap;

                            sheetAddAOA(ws, [[`Pasiva`]], { r: 0, c: startColRight });
                            sheetAddAOA(ws, pasivaAOA, { r: 1, c: startColRight });

                            // Optional: set some column widths
                            ws['!cols'] = [];
                            const totalCols = startColRight + Math.max(1, aoaMaxCols(pasivaAOA));
                            for (let i = 0; i < totalCols; i++) ws['!cols'].push({ wch: 18 });

                        } else if (this.selectedCategory === 'ledger') {
                            // Stack each ledger table vertically with a title and a blank row between
                            const tables = el.querySelectorAll('table');
                            if (!tables.length) return;

                            let currentRow = 0;
                            tables.forEach((table, idx) => {
                                const aoa = XLSX.utils.sheet_to_json(XLSX.utils.table_to_sheet(table, { raw: true }), { header: 1, raw: true });
                                const meta = (this.ledger?.result?.[idx]) || {};
                                const title = `${meta.coa_code || 'Ledger'} - ${meta.coa_name || ''}`.trim();

                                // Title row
                                sheetAddAOA(ws, [[title]], { r: currentRow, c: 0 });
                                currentRow += 1;

                                // Table content
                                sheetAddAOA(ws, aoa, { r: currentRow, c: 0 });
                                currentRow += aoa.length;

                                // Blank spacer row
                                currentRow += 1;
                            });

                            // Optional: wider columns for money columns
                            ws['!cols'] = Array.from({ length: 8 }, () => ({ wch: 18 }));

                        } else {
                            // Profit / Loss: single table into one sheet
                            const table = el.querySelector('table');
                            if (!table) return;
                            const wsSingle = XLSX.utils.table_to_sheet(table, { raw: true });
                            // Copy into our ws (starting at A1)
                            const aoa = XLSX.utils.sheet_to_json(wsSingle, { header: 1, raw: true });
                            sheetAddAOA(ws, aoa, { r: 0, c: 0 });
                            ws['!cols'] = Array.from({ length: 12 }, () => ({ wch: 18 }));
                        }

                        // Build workbook with a single sheet
                        const wb = XLSX.utils.book_new();
                        XLSX.utils.book_append_sheet(wb, ws, titles[this.selectedCategory].slice(0, 31));
                        XLSX.writeFile(wb, fileName);
                    });
                },
                printTable() {
                    // ensure the DOM reflects the current v-if state before printing
                    this.$nextTick(() => window.print());
                },

            },
            mounted() {
                setTimeout(() => {
                    this.getData();
                }, 1000);
            },
        });
    </script>
@endsection
