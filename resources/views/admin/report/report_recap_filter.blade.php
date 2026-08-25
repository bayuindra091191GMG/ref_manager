@extends('layouts.admin')

@section('title')
    <title>Laporan Transaksi Rekap Harian</title>
@endsection

@section('content')
    <!-- Hero -->
     <div>
        <div class="content space-top">
              <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                  <div class="flex-grow-1">
                      <h1 class="h3 fw-bold mb-2">
                          Laporan Transaksi Rekap Harian
                      </h1>
                  </div>
                  <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                      <ol class="breadcrumb breadcrumb-alt">
                          <li class="breadcrumb-item">
                              <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                          </li>
                          <li class="breadcrumb-item" aria-current="page">
                              Laporan Transaksi Rekap Harian
                          </li>
                      </ol>
                  </nav>
              </div>
          </div>
      </div>
    <!-- END Hero -->

    <div class="content" id="vue-section">

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

                    <div class="row mb-4 align-items-center mb-5">
                        <div class="col-md-4">
                            <label for="period" class="form-label">Periode <span class="text-red">*</span></label>
                            <input type="text" id="period" class="form-control date" placeholder="Pilih Periode" v-model="period" readonly>
                        </div>
                        <div class="col-md-4">
                            <label for="type" class="form-label">Tipe Laporan <span class="text-red">*</span></label>
                            <select v-model="report_type" class="form-select" name="report_type" id="report_type">
                                <option value="1">Dengan Pemasukan</option>
                                <option value="2">Tanpa Pemasukan</option>
                                <option value="3">Hanya Pemasukan</option>
                                <option value="4">Laporan BOD</option>
                                <option value="5">Thermal</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-center" v-if="loading">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div v-else>
                        <div v-if="report_type == 1 || report_type == 2">
                        <div class="d-flex justify-content-end mb-3">
                            <button type="button" id="btn_print" class="btn btn-outline-info me-3" @click="printReport(1)">Print</button>
                            <button type="button" id="btn_print" class="btn btn-outline-success" @click="exportToExcel(1)">Excel</button>
                        </div>
                            <div id="main_container_1">
                                <div class="row">
                                    <div class="col-12">
                                        <h4>Laporan Transaksi</h4>
                                    </div>
                                </div>
                                <div class="row" id="date_1">
                                    <div class="col-6">
                                        <span>@{{ startDate }} - @{{ endDate }} / @{{ diffDay }} hari</span>
                                    </div>
                                    <div class="col-6 text-end">
                                        {{-- <button type="button" id="btn_print" class="btn btn-outline-info">Print</button> --}}
                                    </div>
                                </div>
                                <div class="row mt-5">
                                    <div class="col-12">
                                        <h4>Customers</h4>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <table name='customers' class="table table-bordered table-striped table-vcenter js-dataTable-full nowrap">
                                            <tbody>
                                            <tr>
                                                <td>Total Tamu</td>
                                                <td>@{{ totalCustomer }}</td>
                                            </tr>
                                            <tr>
                                                <td>Tamu Treatment</td>
                                                <td>@{{ totalTreatment }}</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <h4>Treatments</h4>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <table name='treatments' class="table table-bordered table-striped table-vcenter js-dataTable-full nowrap">
                                            <tbody>
                                            <tr v-for="package in allPackage">
                                                <td>@{{ package.name }}</td>
                                                <td>@{{ package.qty }}</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <h4>Total Penjualan Add On</h4>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 col-12">
                                        <table name='Total Penjualan Add On' class="table table-bordered table-striped table-vcenter nowrap">
                                            <thead>
                                            <tr>
                                                <th class="text-center">Nama Barang</th>
                                                <th class="text-center">Kode Barang</th>
                                                <th class="text-center">Kuantitas Terjual</th>
                                                <th class="text-center">Pendapatan</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr v-for="item in allItem">
                                                <td>@{{ item.name }}</td>
                                                <td>@{{ item.code }}</td>
                                                <td class="text-end">@{{ item.qty }}</td>
                                                <td class="text-end">@{{ formatCurrency(item.total_price) }}</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <h4>Total Pemasukan</h4>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <table name='Total Pemasukan' class="table table-bordered table-striped table-vcenter js-dataTable-full nowrap">
                                            <tbody>
                                            <tr v-for="payment in payments">
                                                <td>@{{ payment.payment }}</td>
                                                <td class="text-end">@{{ formatCurrency(payment.total) }}</td>
                                            </tr>
                                            <tr>
                                                <td style="font-weight: bold;">Pendapatan</td>
                                                <td style="font-weight: bold;" class="text-end">@{{ formatCurrency(total) }}</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-else-if="report_type == 3">
                            <div class="d-flex justify-content-end mb-3">
                                <button type="button" id="btn_print" class="btn btn-outline-info me-3" @click="printReport(3)">Print</button>
                                <button type="button" id="btn_print" class="btn btn-outline-success" @click="exportToExcel(3)">Excel</button>
                            </div>
                            <div id="main_container_3" class="row">

                                <div class="col-12">
                                    <table class="table table-bordered table-striped table-vcenter js-dataTable-full nowrap">
                                        <tbody>
                                        <tr v-for="payment in payments">
                                            <td>@{{ payment.payment }}</td>
                                            <td class="text-end">@{{ formatCurrency(payment.total) }}</td>
                                        </tr>
                                        <tr>
                                            <td style="font-weight: bold;">Pendapatan</td>
                                            <td style="font-weight: bold;" class="text-end">@{{ formatCurrency(total) }}</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div v-if="report_type == 4">
                            <div class="d-flex justify-content-end mb-3">
                                <button type="button" id="btn_print" class="btn btn-outline-info me-3" @click="printReport(4)">Print</button>
                                <button type="button" id="btn_print" class="btn btn-outline-success" @click="exportToExcel(4)">Excel</button>
                            </div>
                            <div id="main_container_4">
                                <div class="row">
                                    <div class="col-12">
                                        <h4>Laporan Transaksi</h4>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <span>@{{ startDate }} - @{{ endDate }} / @{{ diffDay }} hari</span>
                                    </div>
                                    <div class="col-6 text-end">
                                        {{-- <button type="button" id="btn_print" class="btn btn-outline-info">Print</button> --}}
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <h4>Customers</h4>
                                    </div>
                                    <div class="col-6">
                                        <h4>Total Pemasukan</h4>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <table class="table table-bordered table-striped table-vcenter js-dataTable-full nowrap">
                                            <tbody>
                                            <tr>
                                                <td>Total Tamu</td>
                                                <td>@{{ totalCustomer }}</td>
                                            </tr>
                                            <tr>
                                                <td>Treatment</td>
                                                <td>@{{ totalTreatment }}</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="col-6">
                                        <table class="table table-bordered table-striped table-vcenter js-dataTable-full nowrap">
                                            <tbody>
                                            <tr v-for="payment in payments">
                                                <td>@{{ payment.payment }}</td>
                                                <td class="text-end">@{{ formatCurrency(payment.total) }}</td>
                                            </tr>
                                            <tr>
                                                <td style="font-weight: bold;">Pendapatan</td>
                                                <td style="font-weight: bold;" class="text-end">@{{ formatCurrency(total) }}</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <h4>Treatments</h4>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <table class="table table-bordered table-striped table-vcenter js-dataTable-full nowrap">
                                            <tbody>
                                            <tr v-for="package in allPackage">
                                                <td>@{{ package.name }}</td>
                                                <td>@{{ package.qty }}</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <h4>Total Penjualan Add On</h4>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 col-12">
                                        <table class="table table-bordered table-striped table-vcenter nowrap">
                                            <thead>
                                            <tr>
                                                <th class="text-center">Nama Barang</th>
                                                <th class="text-center">Kode Barang</th>
                                                <th class="text-center">Kuantitas Terjual</th>
                                                <th class="text-center">Pendapatan</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr v-for="item in allItem">
                                                <td>@{{ item.name }}</td>
                                                <td>@{{ item.code }}</td>
                                                <td class="text-end">@{{ item.qty }}</td>
                                                <td class="text-end">@{{ formatCurrency(item.total_price) }}</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-else-if="report_type == 5">
                            <div class="d-flex justify-content-end mb-3">
                                <button type="button" id="btn_print" class="btn btn-outline-info me-3" @click="printReport(5)">Print</button>
                                <button type="button" id="btn_print" class="btn btn-outline-success" @click="exportToExcel(5)">Excel</button>
                            </div>
                            <div id="main_container_5" class="justify-content-center" style="width: 250px; font-size: 9px; font-weight: 400;">
                                <div class="row">
                                    <div class="col-12">
                                        <h5>Laporan Transaksi</h5>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <span>@{{ startDate }} - @{{ endDate }}</span><br/>
                                        <span>@{{ diffDay }} hari</span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <span><b>Total Pemasukan</b></span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <table>
                                            <tbody>
                                            <tr>
                                                <td style="font-weight: bold;">Total</td>
                                                <td class="text-end">@{{ formatCurrency(total) }}</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-12">
                                        <span><b>Total Penjualan Treatment</b></span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <table>
                                            <tr>
                                                <td>Deskripsi</td>
                                                <td>Qty</td>
                                            </tr>
                                            <tr v-for="package in allPackage">
                                                <td>@{{ package.name }}</td>
                                                <td>@{{ package.qty }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <h4>Total Penjualan Add On</h4>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <table>
                                            <tr>
                                                <th class="text-center">Nama Barang</th>
                                                <th class="text-center">Kuantitas Terjual</th>
                                                <th class="text-center">Pendapatan</th>
                                            </tr>
                                            <tr v-for="item in allItem">
                                                <td>@{{ item.name }}</td>
                                                <td class="text-end">@{{ item.qty }}</td>
                                                <td class="text-end">@{{ formatCurrency(item.total_price) }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12" style="height: 100px;"></div>
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

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-bs5/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-buttons-bs5/buttons.bootstrap5.min.css') }}">

    <style>

    @media print {
        #main_container_1 {
            padding: 20px;      /* atur padding dalam container */
            margin: 20px;       /* jarak dari tepi kertas */
        }
        #main_container_3 {
            padding: 20px;      /* atur padding dalam container */
            margin: 20px;       /* jarak dari tepi kertas */
        }

        #main_container_4 {
            padding: 20px;      /* atur padding dalam container */
            margin: 20px;       /* jarak dari tepi kertas */
        }

        #main_container_5 {
            padding: 20px;      /* atur padding dalam container */
            margin: 20px;       /* jarak dari tepi kertas */
        }


        body {
            margin: 0; /* reset margin default body */
        }
    }

        /* Paksa style toastr agar tidak ketiban */
        #toast-container > .toast {
            background-color: #333 !important;
            color: #fff !important;
            opacity: 1 !important;
            padding: 15px !important;
            border-radius: 8px !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2) !important;
        }

        #toast-container > .toast-success {
            background-color: #28a745 !important; /* hijau */
        }

        #toast-container > .toast-error {
            background-color: #dc3545 !important; /* merah */
        }

        #toast-container > .toast-info {
            background-color: #17a2b8 !important; /* biru */
        }

        #toast-container > .toast-warning {
            background-color: #ffc107 !important; /* kuning */
            color: #000 !important;
        }

        #toast-container > .toast {
            padding-left: 50px !important; /* ruang untuk icon */
            background-position: 15px center !important; /* posisi icon */
            background-repeat: no-repeat !important;
            background-size: 24px 24px !important;
        }

/* success */
        #toast-container > .toast-success {
            background-image: url("data:image/svg+xml,%3Csvg fill='white' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath d='M20.285 6.709l-11.285 11.291-5.285-5.291 1.414-1.414 3.871 3.877 9.871-9.877z'/%3E%3C/svg%3E") !important;
        }

        /* error */
        #toast-container > .toast-error {
            background-image: url("data:image/svg+xml,%3Csvg fill='white' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath d='M12 0c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm0 17c-.553 0-1-.447-1-1s.447-1 1-1 1 .447 1 1-.447 1-1 1zm1-4h-2v-8h2v8z'/%3E%3C/svg%3E") !important;
        }

        /* info */
        #toast-container > .toast-info {
            background-image: url("data:image/svg+xml,%3Csvg fill='white' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath d='M11 9h2v-2h-2v2zm1-9c-6.075 0-11 4.925-11 11s4.925 11 11 11 11-4.925 11-11-4.925-11-11-11zm1 17h-2v-6h2v6z'/%3E%3C/svg%3E") !important;
        }

        /* warning */
        #toast-container > .toast-warning {
            background-image: url("data:image/svg+xml,%3Csvg fill='black' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath d='M1 21h22l-11-19-11 19zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z'/%3E%3C/svg%3E") !important;
        }

    </style>
@endsection

@section('scripts')
    <!-- jQuery -->
    <script src="{{ asset('js/plugins/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('js/plugins/flatpickr/l10n/id.js') }}"></script>
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>


    <script src="{{ asset('js/vue.js') }}"></script>
    <script src="{{ asset('js/axios.js') }}"></script>
    <script src="{{ asset('js/AutoNumeric.js') }}"></script>
    <script src="{{ asset('js/vue-autonumeric.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script src="{{ asset('js/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-bs5/dataTables.bootstrap5.min.js') }}"></script>

    <script src="https://cdn.datatables.net/buttons/2.1.0/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.1.0/js/buttons.html5.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/printThis/1.15.0/printThis.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

    <script>
        let vueComponent = new Vue({
            el: "#vue-section",
            data: {
                period: '',
                report_type: 1,
                data: {},
                loading: false,

                //report type 1 and 2
                totalCustomer: 0,
                totalTreatment: 0,
                allItem: [],
                allPackage: [],
                payments: [],
                totalStr: 0,
                total: 0,
                startDate: moment().startOf('month').format('DD-MM-YYYY'),
                endDate: moment().endOf('month').format('DD-MM-YYYY'),
                diffDay: moment().endOf('month').diff(moment().startOf('month'), 'days') + 1,

                autonumericFormat: {
                    minimumValue: '0',
                    maximumValue: '9999999999999',
                    digitGroupSeparator: '.',
                    decimalCharacter: ',',
                    decimalPlaces: 0,
                    modifyValueOnWheel: false,
                    unformatOnSubmit: true,
                    allowDecimalPadding: false,
                },
            },
            methods: {

                exportToExcel(type) {
                    let container = document.getElementById("main_container_" + type);
                    let cloned = container.cloneNode(true);

                    // ambil teks tanggal dari #date_1
                    let dateText = cloned.querySelector("#date_" + type + " span")?.innerText || "laporan";

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

                    if(type == 1){
                        var typeReport = 'dengan Pemasukan';
                    }else if(type == 2){
                        var typeReport = 'Tanpa Pemasukan';
                    }else if(type == 3){
                        var typeReport = 'Hanya Pemasukan';
                    }else if(type == 4){
                        var typeReport = 'BOD';
                    }else if(type == 5){
                        var typeReport = 'Thermal';
                    }

                    // pakai date_1 sebagai nama file
                    XLSX.writeFile(wb, 'Laporan rekap daily transaksi ' + safeFileName + " " + typeReport + ".xlsx");
                },
                printReport(type)
                {
                    if(type == 1)
                    {
                       $("#main_container_1").printThis({
                            importCSS: true,       // ikutkan CSS yang ada
                            importStyle: true,     // ikutkan style <style> di head
                            printContainer: true,  // print container juga
                            loadCSS: "",           // bisa kasih file css tambahan kalau perlu
                            pageTitle: "Laporan dengan Pemasukan dan Tanpa Pemasukan",  // judul di tab print
                        });
                    }
                    else if(type == 3)
                    {
                       $("#main_container_3").printThis({
                            importCSS: true,       // ikutkan CSS yang ada
                            importStyle: true,     // ikutkan style <style> di head
                            printContainer: true,  // print container juga
                            loadCSS: "",           // bisa kasih file css tambahan kalau perlu
                            pageTitle: "Laporan Hanya Pemasukan Rekap Harian",  // judul di tab print
                        });
                    }

                    else if(type == 4)
                    {
                       $("#main_container_4").printThis({
                            importCSS: true,       // ikutkan CSS yang ada
                            importStyle: true,     // ikutkan style <style> di head
                            printContainer: true,  // print container juga
                            loadCSS: "",           // bisa kasih file css tambahan kalau perlu
                            pageTitle: "Laporan BOD Rekap Harian",  // judul di tab print
                        });
                    }

                    else if(type == 5)
                    {
                       $("#main_container_5").printThis({
                            importCSS: true,       // ikutkan CSS yang ada
                            importStyle: true,     // ikutkan style <style> di head
                            printContainer: true,  // print container juga
                            loadCSS: "",           // bisa kasih file css tambahan kalau perlu
                            pageTitle: "Laporan Thermal Rekap Harian",  // judul di tab print
                        });
                    }
                },

                formatCurrency(value) {
                    if (!value) return "Rp 0";
                    return "Rp " + Number(value).toLocaleString("id-ID", {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                },
                showErrorNotif(message){
                    toastr.options = {
                        "closeButton": true,
                        "progressBar": true,
                        "positionClass": "toast-top-right",
                        "timeOut": "4000"
                    };
                    toastr.error(message, "Error");
                },
                showSuccessNotif(message){
                    toastr.options = {
                        "closeButton": true,
                        "progressBar": true,
                        "positionClass": "toast-top-right",
                        "timeOut": "4000"
                    };
                    toastr.success(message, "Success");
                },


                getData(){
                    this.loading = true;
                    console.log(this.report_type);

                    axios.get('{{ route('admin.recap.report.generate') }}', {
                        params: {
                            period: this.period,
                            report_type: this.report_type,
                        }
                    })
                    .then(response => {
                        console.log(response);

                        if(response.data.status === 'success'){
                            this.totalCustomer = response.data.data.total_customer ?? 0;
                            this.totalTreatment = response.data.data.total_treatment ?? 0;

                            this.allItem = response.data.data.allItem ?? [];
                            this.allPackage = response.data.data.allPackage ?? [];

                            this.payments = response.data.data.payments ?? [];
                            this.totalStr = parseInt(response.data.data.totalStr ?? 0).toString().replace(/,/g, '');
                            this.total = response.data.data.total ?? 0;



                        } else {
                            this.showErrorNotif(response.data.message);
                        }
                    })
                    .catch(error => {
                        this.showErrorNotif(error.message ?? 'Terjadi kesalahan');
                    })
                    .finally(() => {
                        setTimeout(() => {
                            this.loading = false;
                        }, 500);
                    });
                },

            },
            watch: {
                period(){
                    console.log('tes period');

                    this.getData();
                },
                report_type(){
                    this.getData();
                }
            },
            mounted() {
                flatpickr('.date', {
                    "locale": "id",
                    "mode": "range"
                });



                let today = new Date();

                // awal bulan
                let firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
                // akhir bulan
                let lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);

                // format ke dd-mm-yyyy
                let format = (d) => {
                    let day = String(d.getDate()).padStart(2, '0');
                    let month = String(d.getMonth() + 1).padStart(2, '0');
                    let year = d.getFullYear();
                    return `${year}-${month}-${day}`;
                };

                this.period = `${format(firstDay)} - ${format(lastDay)}`;
            },
        });
    </script>
@endsection
