@extends('layouts.admin')

@section('title')
    <title>Laporan Harian dengan MTD & YTD</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Laporan Harian dengan MTD & YTD
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Laporan Harian dengan MTD & YTD
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
                               v-model="date" required/>
                    </div>
                </div>

                <hr/>

                <h1 class="text-center p-5" v-if="loading"><i class="fa fa-fw fa-spin fa-circle-notch"></i></h1>
                <template v-else-if="data != null">
                    <div class="d-flex justify-content-end mb-3">
                        <button type="button" id="btn_print" class="btn btn-outline-info me-3" @click="printReport()">Print</button>
                        <button type="button" id="btn_print" class="btn btn-outline-success" @click="exportToExcel()">Excel</button>
                    </div>

                    <div id="main_container">
                        <table class="table table-bordered table-striped table-vcenter js-dataTable-full nowrap">
                            {{--Revenue Summary--}}
                            <thead>
                            <tr>
                                <th colspan="5" class="fs-4">Revenue Summary</th>
                            </tr>
                            </thead>
                            <tr>
                                <td colspan="2">Category</td>
                                <td>Today</td>
                                <td>MTD</td>
                                <td>YTD</td>
                            </tr>
                            <tr>
                                <td colspan="2">Room Revenue</td>
                                <td style="font-weight: bold;">Rp @{{ numberFormat(data.roomRevenue.today) }}</td>
                                <td style="font-weight: bold;">Rp @{{ numberFormat(data.roomRevenue.mtdRoom) }}</td>
                                <td style="font-weight: bold;">Rp @{{ numberFormat(data.roomRevenue.ytdRoom) }}</td>
                            </tr>
                            <tr>
                                <td colspan="2">AVG Room Rate</td>
                                <td style="font-weight: bold;">Rp @{{ numberFormat(data.avgRate.today) }}</td>
                                <td style="font-weight: bold;">Rp @{{ numberFormat(data.avgRate.mtdRoom) }}</td>
                                <td style="font-weight: bold;">Rp @{{ numberFormat(data.avgRate.ytdRoom) }}</td>
                            </tr>
                            <tr>
                                <th colspan="5"></th>
                            </tr>

                            {{--Detail Revenue--}}
                            <thead>
                            <tr>
                                <th colspan="5" class="fs-4">Detail Revenue</th>
                            </tr>
                            </thead>
                            <thead>
                            <tr>
                                <td>No</td>
                                <td>Keterangan</td>
                                <td>Today</td>
                                <td>MTD</td>
                                <td>YTD</td>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(item, index) in data.sales">
                                <td>@{{ (index+1) }}</td>
                                <td>@{{ item.name }}</td>
                                <td v-if="item.name == 'FOC' || item.name == 'Diskon'">
                                    -@{{ numberFormat(item.daily[0].total) }}
                                </td>
                                <td v-else>
                                    @{{ numberFormat(item.daily[0].total) }}
                                </td>
                                <td v-if="item.name == 'FOC' || item.name == 'Diskon'">
                                    -@{{ numberFormat(item.mtd[0].total) }}
                                </td>
                                <td v-else>
                                    @{{ numberFormat(item.mtd[0].total) }}
                                </td>
                                <td v-if="item.name == 'FOC' || item.name == 'Diskon'">
                                    -@{{ numberFormat(item.ytd[0].total) }}
                                </td>
                                <td v-else>
                                    @{{ numberFormat(item.ytd[0].total) }}
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" style="font-weight: bold; font-size: 18px;">Total Sales</td>
                                <td>-@{{ numberFormat(getTotal()) }}</td>
                                <td>-@{{ numberFormat(getTotalMtd()) }}</td>
                                <td>-@{{ numberFormat(getTotalYtd()) }}</td>
                            </tr>
                            <tr>
                                <td>@{{ data.sales.length+1 }}</td>
                                <td>@{{ data.discount.name }}</td>
                                <td>-@{{ numberFormat(data.discount.daily[0].total) }}</td>
                                <td>-@{{ numberFormat(data.discount.mtd[0].total) }}</td>
                                <td>-@{{ numberFormat(data.discount.ytd[0].total) }}</td>
                            </tr>
                            <tr>
                                <td colspan="2" style="font-weight: bold; font-size: 18px;">Grand Total</td>
                                <td class="fw-bold">@{{ numberFormat(getTotal() - data.discount.daily[0].total) }}</td>
                                <td class="fw-bold">@{{ numberFormat(getTotalMtd() - data.discount.mtd[0].total) }}</td>
                                <td class="fw-bold">@{{ numberFormat(getTotalYtd() - data.discount.ytd[0].total) }}</td>
                            </tr>
                            <tr>
                                <th colspan="5"></th>
                            </tr>
                            </tbody>

                            {{--Pembayaran--}}
                            <thead>
                            <tr>
                                <th colspan="5" class="fs-4">Pembayaran</th>
                            </tr>
                            </thead>
                            <thead>
                            <tr>
                                <td>No</td>
                                <td>Keterangan</td>
                                <td>Today</td>
                                <td>MTD</td>
                                <td>YTD</td>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(item, index) in data.payments">
                                <td>@{{ (index+1) }}</td>
                                <td>@{{ item.name }}</td>
                                <td>-@{{ numberFormat(item.daily[0].total) }}</td>
                                <td>-@{{ numberFormat(item.mtd[0].total) }}</td>
                                <td>-@{{ numberFormat(item.ytd[0].total) }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold; font-size: 18px;" colspan="2" class="text-right">Pendapatan</td>
                                <td class="fw-bold">Rp @{{ numberFormat(data.totalPayment.daily) }}</td>
                                <td class="fw-bold">Rp @{{ numberFormat(data.totalPayment.mtd) }}</td>
                                <td class="fw-bold">Rp @{{ numberFormat(data.totalPayment.ytd) }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </template>
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
    <script type="text/javascript" src="{{ asset('js/numeral.min.js') }}"></script>

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
                date : '{{ $startDate }}',
            },
            watch: {
                date(newVal, oldVal) {
                    if (newVal !== oldVal) {
                        app.getData();
                    }
                },
            },
            computed: {

            },
            methods: {
                numberFormat(number){
                    return numeral(number).format('0,0[.]00').replace(/,/g,'.');
                },

                exportToExcel() {
                    let container = document.getElementById("main_container");
                    let cloned = container.cloneNode(true);

                    // ambil teks tanggal dari #date_1
                    let dateText = this.date;

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
                    XLSX.writeFile(wb, 'Laporan Daily MTD YTD ' + safeFileName + ".xlsx");
                },
                printReport()
                {
                    $("#main_container").printThis({
                        importCSS: true,       // ikutkan CSS yang ada
                        importStyle: true,     // ikutkan style <style> di head
                        printContainer: true,  // print container juga
                        loadCSS: "",           // bisa kasih file css tambahan kalau perlu
                        pageTitle: "Laporan Daily MTD YTD " + this.date,  // judul di tab print
                    });
                },

                clearData() {
                    app.data = null;
                },
                async getData() {
                    app.loading = true;
                    app.clearData();

                    $.ajax({
                        url: '{{ route('admin.report.mtd.generate') }}',
                        type: 'post',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        data: {
                            start_date: app.date,
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

                getTotal(){
                    var total = 0;
                    if(this.data != null) {
                        for(var i = 0; i < this.data.sales.length; i++){
                            let item = this.data.sales[i];
                            console.log(item);
                            if(item.name == 'FOC' || item.name == 'Diskon') {
                                total += item.daily[0].total;
                            } else {
                                total -= item.daily[0].total;
                            }
                        }
                    }

                    return total;
                },
                getTotalMtd(){
                    var total = 0;
                    if(this.data != null) {
                        for(var i = 0; i < this.data.sales.length; i++){
                            let item = this.data.sales[i];
                            if(item.name == 'FOC' || item.name == 'Diskon') {
                                total += item.mtd[0].total;
                            } else {
                                total -= item.mtd[0].total;
                            }
                        }
                    }

                    return total;
                },
                getTotalYtd(){
                    var total = 0;
                    if(this.data != null) {
                        for(var i = 0; i < this.data.sales.length; i++){
                            let item = this.data.sales[i];
                            if(item.name == 'FOC' || item.name == 'Diskon') {
                                total += item.ytd[0].total;
                            } else {
                                total -= item.ytd[0].total;
                            }
                        }
                    }

                    return total;
                }
            },
            mounted() {
                setTimeout(() => {
                    this.getData();
                }, 1000);

                flatpickr('.js-flatpickr', {
                    "locale": "id",
                    "maxDate": "{{ $maxDate }}",
                    "minDate": "{{ $minDate }}",
                });
            },
        });
    </script>
@endsection
