@extends('layouts.admin')

@section('title')
    <title>Laporan Transaksi Voucher</title>
@endsection

@section('content')
    <!-- Hero -->
     <div>
        <div class="content space-top">
              <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                  <div class="flex-grow-1">
                      <h1 class="h3 fw-bold mb-2">
                          Laporan Transaksi Voucher
                      </h1>
                  </div>
                  <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                      <ol class="breadcrumb breadcrumb-alt">
                          <li class="breadcrumb-item">
                              <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                          </li>
                          <li class="breadcrumb-item" aria-current="page">
                              Laporan Transaksi Voucher
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
                <div class="row mb-2">
                    <div class="col-sm-3 col-12">
                        <div class="col-sm-12 col-12">
                            <label class="form-label" for="end_date">Tanggal</label>
                            <input type="text" id="end_date" class="form-control js-flatpickr" data-date-format="d-m-Y" v-model="selectedDate"/>
                        </div>
                    </div>
                    <div class="col-sm-9 col-12 text-end">
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
                <div id="voucherContainer" class="row" v-if="!loading">
                    <div class="col-12">
                        <hr/>
                        <div class="table-responsive w-100">
                            <h4>Penggunaan Voucher</h4>
                            <table class="table table-bordered table-striped table-vcenter js-dataTable-full nowrap" id="table_summary">
                                <thead>
                                <tr>
                                    <th class="text-center" style="width: 50%">Code</th>
                                    <th class="text-center" style="width: 10%">Qty</th>
                                    <th class="text-center" style="width: 40%">Nominal</th>
                                </tr>
                                </thead>
                                <tr v-for="(item, index) in vouchers">
                                    <td class="text-center">@{{ item.code }}</td>
                                    <td class="text-center">@{{ item.qty.toLocaleString('id-ID') }}</td>
                                    <td class="text-end">@{{ item.amount.toLocaleString('id-ID') }}</td>
                                </tr>
                            </table>
                            <table class="table table-bordered table-striped table-vcenter js-dataTable-full nowrap" id="table_data">
                                <thead>
                                <tr>
                                    <th class="text-center">No</th>
                                    <th class="text-center">No Transaksi</th>
                                    <th class="text-center">Kode Voucher</th>
                                    <th class="text-center">Subtotal</th>
                                    <th class="text-center">Discount</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Tanggal</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-if="transactions.length == 0">
                                    <td colspan="7" class="text-center">Belum ada transaksi...</td>
                                </tr>
                                <tr v-for="(item, index) in transactions">
                                    <td class="text-center">@{{ item.no }}</td>
                                    <td class="text-center">@{{ item.code }}</td>
                                    <td class="text-center">@{{ item.voucher_code }}</td>
                                    <td class="text-end">@{{ parseInt(item.subtotal || 0).toLocaleString('id-ID') }}</td>
                                    <td class="text-end">@{{ parseInt(item.discount_amount || 0).toLocaleString('id-ID') }}</td>
                                    <td class="text-end">@{{ (item.subtotal - item.discount_amount).toLocaleString('id-ID') }}</td>
                                    <td class="text-center">@{{ item.created_at }}</td>
                                </tr>
                                </tbody>
                            </table>
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
            #voucherContainer, #voucherContainer * {
                visibility: visible !important;
            }

            /* position the visible report at the top-left for a clean print */
            #voucherContainer {
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
                vouchers: null,
                transactions: null,
                selectedDate: '{{ $filterStartDate }} to {{ $filterEndDate }}',
                selectedStartDate : '{{ $filterStartDate }}',
                selectedEndDate : '{{ $filterEndDate }}',
            },
            watch: {
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
                    app.vouchers = null;
                    app.transactions = null;
                },
                async getData() {
                    app.loading = true;
                    app.clearData();

                    $.ajax({
                        url: '{{ route('admin.report.foc.data') }}',
                        type: 'get',
                        data: {
                            start_date: app.selectedStartDate,
                            end_date: app.selectedEndDate,
                        },
                        success: function(response) {
                            app.vouchers = response.vouchers;
                            app.transactions = response.transactions;
                        },
                        error: function(xhr, status, error) {
                            console.log(error);
                        }
                    }).always( function() {
                        app.loading = false;
                    });
                },
                getActiveSectionEl() {
                    return document.getElementById('voucherContainer');
                },
                niceFileDate() {
                    const s = (this.selectedStartDate || '').replaceAll('/', '-');
                    const e = (this.selectedEndDate || '').replaceAll('/', '-');
                    return s && e ? `${s}_to_${e}` : (s || e || '');
                },
                exportToPDF() {
                    const el = this.getActiveSectionEl();
                    if (!el) return;

                    const fileName = `Transaksi_Voucher_${this.niceFileDate() || 'export'}.pdf`;

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

                    const fileName = `Transaksi_Voucher_${this.niceFileDate() || 'export'}.xlsx`;

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

                        // Stack each ledger table vertically with a title and a blank row between
                        const tables = el.querySelectorAll('table');
                        if (!tables.length) return;

                        let currentRow = 0;
                        tables.forEach((table, idx) => {
                            const aoa = XLSX.utils.sheet_to_json(XLSX.utils.table_to_sheet(table, { raw: true }), { header: 1, raw: true });
                            const meta = (this.ledger?.result?.[idx]) || {};
                            let title = '';
                            if (idx === 0) {
                                title = `Penggunaan Voucher`.trim();
                            } else {
                                title = '';
                            }

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

                        // Build workbook with a single sheet
                        const wb = XLSX.utils.book_new();
                        XLSX.utils.book_append_sheet(wb, ws, 'Transaksi_Voucher');
                        XLSX.writeFile(wb, fileName);
                    });
                },
                printTable() {
                    // ensure the DOM reflects the current v-if state before printing
                    this.$nextTick(() => window.print());
                }
            },
            mounted() {
                setTimeout(() => {
                    this.getData();
                }, 1000);
            },
        });
    </script>
@endsection
