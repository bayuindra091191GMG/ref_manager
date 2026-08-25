@extends('layouts.admin')

@section('title')
    <title>Laporan Komisi Salon / Studio</title>
@endsection

@section('content')
    <!-- Hero -->
     <div>
        <div class="content space-top">
              <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                  <div class="flex-grow-1">
                      <h1 class="h3 fw-bold mb-2">
                          Laporan Komisi Salon / Studio
                      </h1>
                  </div>
                  <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                      <ol class="breadcrumb breadcrumb-alt">
                          <li class="breadcrumb-item">
                              <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                          </li>
                          <li class="breadcrumb-item" aria-current="page">
                              Laporan Komisi Salon / Studio
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
                    <div class="row mb-3">
                        <div class="col-12">
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
                    <div class="row mt-4 mb-4 align-items-center mb-5">
                        <div class="col-md-4">
                            <label for="period" class="form-label">Periode <span class="text-red">*</span></label>
                            <input type="text" id="period" class="form-control date" placeholder="Pilih Periode" v-model="period" readonly>
                        </div>
                        <div class="col-md-4">
                            <label for="type" class="form-label">Tipe <span class="text-red">*</span></label>
                            <select class="form-select" name="type" id="type" v-model="selectedType" required>
                                <option value="SALON">SALON</option>
                                <option value="STUDIO">STUDIO</option>
                            </select>
                        </div>
                        <div class="d-flex justify-content-end mb-3">
                            <button type="button" id="btn_print" class="btn btn-outline-success" @click="exportToExcel()">Excel</button>
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

                    <table id="table_data" class="table table-bordered"  v-if="!loading">
                        <thead>
                            <tr>
                                <th width="15%">Tanggal</th>
                                <th>Transaksi No</th>
                                <th>Treatment</th>
                                <th>Qty</th>
                                <th>Harga</th>
                                <th>Jumlah Transaksi</th>
                                <th>Jumlah Komisi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in results">
                                <td>@{{ item.date }}</td>
                                <td>@{{ item.code }}</td>
                                <td>@{{ item.package_name }}</td>
                                <td v-text="parseInt(item.qty).toLocaleString('id-ID')"></td>
                                <td v-text="parseInt(item.price).toLocaleString('id-ID')"></td>
                                <td v-text="parseInt(item.subtotal).toLocaleString('id-ID')"></td>
                                <td v-text="parseInt(item.commision_amount).toLocaleString('id-ID')"></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6">Total Komisi</td>
                                <td v-text="parseInt(totalCommision).toLocaleString('id-ID')"></td>
                            </tr>
                        </tfoot>
                    </table>


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
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>

    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/printThis/1.15.0/printThis.min.js"></script>

    <script type="text/javascript" src="https://cdn.sheetjs.com/xlsx-0.20.3/package/dist/xlsx.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/html2pdf.js@0.10.1/dist/html2pdf.bundle.min.js"></script>

    <script>
        let vueComponent = new Vue({
            el: "#vue-section",
            data: {
                period: '',
                selectedType: 'SALON',
                totalCommision: 0,
                results: [],
                loading: true,

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
                exportToExcel() {
                    if (!this.results || !Array.isArray(this.results) || this.results.length === 0) {
                        toastr?.error?.('Belum ada data untuk diekspor.');
                        return;
                    }

                    // Helper ubah angka lokal ke number
                    const toNumber = (val) => {
                        if (val === null || val === undefined || val === '') return 0;
                        if (typeof val === 'number') return val;
                        let s = String(val).trim();
                        if (!s) return 0;
                        s = s.replace(/[^\d.,\-]/g, '');
                        if (/^\d{1,3}(\.\d{3})*(,\d+)?$/.test(s)) {
                            s = s.replace(/\./g, '').replace(',', '.');
                        } else if (/^\d{1,3}(,\d{3})+(\.\d+)?$/.test(s)) {
                            s = s.replace(/,/g, '');
                        }
                        const n = Number(s);
                        return isNaN(n) ? 0 : n;
                    };

                    // Buat header
                    const header = [
                        'Tanggal',
                        'Transaksi No',
                        'Treatment',
                        'Qty',
                        'Harga',
                        'Jumlah Transaksi',
                        'Jumlah Komisi'
                    ];

                    // Konversi data ke array of array (untuk XLSX)
                    const aoa = [];
                    aoa.push(header);

                    this.results.forEach((item) => {
                        aoa.push([
                            item.date ?? '',
                            item.code ?? '',
                            item.package_name ?? '',
                            toNumber(item.qty),
                            toNumber(item.price),
                            toNumber(item.subtotal),
                            toNumber(item.commision_amount)
                        ]);
                    });

                    // Tambahkan total komisi di akhir
                    aoa.push([]);
                    aoa.push(['', '', '', '', '', 'Total Komisi', toNumber(this.totalCommision)]);

                    // Buat workbook dan worksheet
                    const wb = XLSX.utils.book_new();
                    const ws = XLSX.utils.aoa_to_sheet(aoa);

                    // Auto width
                    if (ws['!ref']) {
                        const range = XLSX.utils.decode_range(ws['!ref']);
                        const cols = [];
                        for (let C = range.s.c; C <= range.e.c; C++) {
                            let maxW = 10;
                            for (let R = range.s.r; R <= range.e.r; R++) {
                                const ref = XLSX.utils.encode_cell({ c: C, r: R });
                                const cell = ws[ref];
                                if (!cell || cell.v == null) continue;
                                const str = cell.v.toString();
                                const longest = str.split(/\r?\n/).reduce((m, s) => Math.max(m, s.length), 0);
                                maxW = Math.max(maxW, longest);
                            }
                            cols.push({ wch: maxW + 2 });
                        }
                        ws['!cols'] = cols;
                    }

                    // Format angka (Qty, Harga, Jumlah Transaksi, Jumlah Komisi)
                    if (ws['!ref']) {
                        const range = XLSX.utils.decode_range(ws['!ref']);
                        for (let R = 1; R <= range.e.r; R++) { // skip header
                            for (let C of [3, 4, 5, 6]) { // kolom Qty, Harga, Jumlah Transaksi, Jumlah Komisi
                                const ref = XLSX.utils.encode_cell({ c: C, r: R });
                                const cell = ws[ref];
                                if (cell && typeof cell.v === 'number') {
                                    cell.z = '#,##0';
                                }
                            }
                        }
                    }

                    XLSX.utils.book_append_sheet(wb, ws, 'Komisi');
                    const fileName = `Laporan_Komisi_${this.selectedType || ''}_${this.period || ''}.xlsx`;
                    XLSX.writeFile(wb, fileName);
                },
                getData(){
                    this.loading = true;
                    axios.get('{{ route('admin.salon_studio_commission.report.generate') }}', {
                        params: {
                            period: this.period,
                            type: this.selectedType
                        }
                    })
                    .then(response => {
                        if(response.data.status === 'success'){
                            // debugger;
                            this.results = response.data.data;
                            this.totalCommision = response.data.total_commision;
                            this.reloadTable();
                        } else {
                            this.showErrorNotif(response.data.message);
                        }
                        this.loading = false;
                    })
                    .catch(error => {
                        this.showErrorNotif(error.message ?? 'Terjadi kesalahan');
                    });
                },
                initTable() {
                    console.log('init');

                    // this.table = $('#table_data').DataTable({
                    //     dom: 'Blfrtip',
                    //     buttons: [{
                    //         extend: 'excel',
                    //         className: 'btn btn-success mb-3 button-excel',
                    //     }],
                    //     destroy: true,
                    //     responsive: true
                    // });
                },
                reloadTable(){
                    if(this.table){
                        this.initTable();
                        this.table.clear();
                        this.table.rows.add(this.results);
                        this.table.draw();
                    } else {
                        this.initTable();
                    }
                }
            },
            watch: {
                selectedType(newVal, oldVal) {
                    if (newVal !== oldVal) {
                        this.getData();
                    }
                },
                period(){
                    this.getData();
                },
            },
            mounted() {
                flatpickr('.date', {
                    "locale": "id",
                    "mode": "range"
                });

                // init datatable kosong dulu
                this.initTable();

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


                const vm = this;
                $('#type').select2({ placeholder: "Pilih Tipe", width: '100%' });
                $('#type').val(vm.selectedType).trigger('change'); // sinkron awal
                $('#type').on('change', function () {
                    vm.selectedType = $(this).val();
                });
                setTimeout(() => vm.getData(), 200);
            },
        });
    </script>
@endsection
