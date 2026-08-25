@extends('layouts.admin')

@section('title')
    <title>Laporan Laba Kotor</title>
@endsection

@section('content')
    <!-- Hero -->
     <div>
        <div class="content space-top">
              <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                  <div class="flex-grow-1">
                      <h1 class="h3 fw-bold mb-2">
                          Laporan Laba Kotor
                      </h1>
                  </div>
                  <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                      <ol class="breadcrumb breadcrumb-alt">
                          <li class="breadcrumb-item">
                              <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                          </li>
                          <li class="breadcrumb-item" aria-current="page">
                              Laporan Laba Kotor
                          </li>
                      </ol>
                  </nav>
              </div>
          </div>
      </div>
    <!-- END Hero -->

    <div class="content" id="app">
        <div class="block block-rounded">
            <div class="block-content block-content-full">
                <div class="row">
                    <div class="col-lg-4">
                        <label class="form-label" for="start_date">Tanggal Mulai <span
                                class="text-red">*</span></label>
                        <input id="start_date" type="text" class="js-flatpickr form-control"
                               data-date-format="d-m-Y" name="start_date"
                               v-model="selectedStartDate" required/>
                    </div>
                    <div class="col-lg-4">
                        <label class="form-label" for="end_date">Tanggal Akhir <span
                                class="text-red">*</span></label>
                        <input id="end_date" type="text" class="js-flatpickr form-control"
                               data-date-format="d-m-Y" name="end_date"
                               v-model="selectedEndDate" required/>
                    </div>
                </div>

                <hr/>

                <h1 class="text-center p-5" v-if="loading"><i class="fa fa-fw fa-spin fa-circle-notch"></i></h1>
                <div v-else-if="data != null">
                    <div class="d-flex justify-content-end mb-3">
                        <button type="button" id="btn_print" class="btn btn-outline-info me-3" @click="printReport()">Print</button>
                        <button type="button" id="btn_print" class="btn btn-outline-success" @click="exportToExcel()">Excel</button>
                    </div>

                    <div id="main_container">
                        <table class="table table-bordered table-striped table-vcenter js-dataTable-full nowrap">
                            <tbody>
                            <tr>
                                <td>Penjualan Kotor</td>
                                <td>@{{ data.totalTrx }}</td>
                            </tr>
                            <tr>
                                <td>Biaya Promo</td>
                                <td>@{{ data.discount }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Total</td>
                                <td class="fw-bold">@{{ data.total }}</td>
                            </tr>
                            </tbody>
                        </table>
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
@endsection

@section('scripts')
    <!-- jQuery -->
    <script src="{{ asset('js/plugins/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('js/plugins/flatpickr/l10n/id.js') }}"></script>
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>

    <script src="{{ asset('js/vue.js') }}"></script>
    <script src="{{ asset('js/axios.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/printThis/1.15.0/printThis.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

    <script>
        let app = new Vue({
            el: '#app',
            data: {
                loading: true,
                data: null,
                selectedStartDate : '{{ $startDate }}',
                selectedEndDate : '{{ $endDate }}',
            },
            watch: {
                selectedStartDate(newVal, oldVal) {
                    if (newVal !== oldVal) {
                        app.getData();
                    }
                },
                selectedEndDate(newVal, oldVal) {
                    if (newVal !== oldVal) {
                        app.getData();
                    }
                },
            },
            computed: {

            },
            methods: {
                exportToExcel() {
                    let container = document.getElementById("main_container");
                    let cloned = container.cloneNode(true);

                    // ambil teks tanggal dari #date_1
                    let dateText = this.selectedStartDate + '_' + this.selectedEndDate;

                    // sanitize supaya nama file aman (hapus karakter aneh)
                    let safeFileName = dateText.replace(/[\\/:*?"<>|]/g, "_");

                    let tables = cloned.querySelectorAll("table");
                    let wb = XLSX.utils.book_new();

                    tables.forEach((table, index) => {
                        let ws = XLSX.utils.table_to_sheet(table);

                        // === auto width ===
                        let colWidths = [];
                        let range = XLSX.utils.decode_range(ws['!ref']);
                        for (let C = range.s.c; C <= range.e.c; ++C) {
                            let maxWidth = 10;
                            for (let R = range.s.r; R <= range.e.r; ++R) {
                                let cellAddress = {c: C, r: R};
                                let cellRef = XLSX.utils.encode_cell(cellAddress);
                                let cell = ws[cellRef];
                                if (cell && cell.v) {
                                    let cellValue = cell.v.toString();
                                    maxWidth = Math.max(maxWidth, cellValue.length);
                                }
                            }
                            colWidths.push({wch: maxWidth + 2});
                        }
                        ws['!cols'] = colWidths;
                        // ==================

                        let sheetName = table.getAttribute("name") || "Sheet" + (index+1);
                        XLSX.utils.book_append_sheet(wb, ws, sheetName.substring(0, 31));
                    });

                    // pakai date_1 sebagai nama file
                    XLSX.writeFile(wb, 'Laporan Laba Kotor ' + safeFileName + ".xlsx");
                },
                printReport()
                {
                    $("#main_container").printThis({
                        importCSS: true,       // ikutkan CSS yang ada
                        importStyle: true,     // ikutkan style <style> di head
                        printContainer: true,  // print container juga
                        loadCSS: "",           // bisa kasih file css tambahan kalau perlu
                        pageTitle: "Laporan Laba Kotor " + this.selectedStartDate + '_' + this.selectedEndDate,  // judul di tab print
                    });
                },

                clearData() {
                    app.data = null;
                },
                async getData() {
                    app.loading = true;
                    app.clearData();

                    $.ajax({
                        url: '{{ route('admin.report.oura_only.generate') }}',
                        type: 'post',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        data: {
                            start_date: app.selectedStartDate,
                            end_date: app.selectedEndDate,
                        },
                        success: function(response) {
                            app.data = response;
                        },
                        error: function(xhr, status, error) {
                            console.log(error);
                        }
                    }).always( function() {
                        app.loading = false;
                    });
                },
            },
            mounted() {
                setTimeout(() => {
                    this.getData();
                }, 1000);

                flatpickr('.js-flatpickr', {
                    "locale": "id" // locale for this instance only
                });
            },
        });
    </script>
@endsection
