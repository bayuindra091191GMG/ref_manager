@extends('layouts.admin')

@section('title')
    <title>Laporan Transaksi per Payment</title>
@endsection

@section('content')
    <!-- Hero -->
     <div>
        <div class="content space-top">
              <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                  <div class="flex-grow-1">
                      <h1 class="h3 fw-bold mb-2">
                          Laporan Transaksi per Payment
                      </h1>
                  </div>
                  <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                      <ol class="breadcrumb breadcrumb-alt">
                          <li class="breadcrumb-item">
                              <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                          </li>
                          <li class="breadcrumb-item" aria-current="page">
                              Laporan Transaksi per Payment
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

                    <div class="row mb-4 align-items-center mb-5">
                        <div class="col-md-4">
                            <label for="period" class="form-label">Periode <span class="text-red">*</span></label>
                            <input type="text" id="period" class="form-control date" placeholder="Pilih Periode" v-model="period" readonly>
                        </div>
                        <div class="col-md-4">
                            <label for="type" class="form-label">Tipe Pembayaran <span class="text-red">*</span></label>
                            <select v-model="type" class="form-select" name="type" id="type" required>
                                <option value="2,5,6">EDC 899</option>
                                <option value="8,9,10">EDC 394</option>
                                @foreach($types as $type)
                                    <option value="{{ $type->id }}">{{ $type->description }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <table id="table_data" class="table table-bordered">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>No. Transaksi</th>
                                <th>Customer</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Grand Total</th>
                                <th id="grand_total_footer">Rp 0</th>
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

    <script src="https://cdn.datatables.net/buttons/2.1.0/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.1.0/js/buttons.html5.min.js"></script>

    <script>
        let vueComponent = new Vue({
            el: "#vue-section",
            data: {
                period: '',
                type: 1,
                data: {},
                loading: false,

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
            computed: {
                paymentLabel(){
                    // ambil element select
                    let select = document.getElementById('type');
                    // ambil opsi terpilih
                    let option = select.options[select.selectedIndex];
                    return option.text; // contoh: "EDC 899"
                }
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

                newexportaction(e, dt, button, config) {
                    // console.log(button[0].className);
                    $('#modal_loading').modal('show');

                    var self = this;
                    var oldStart = dt.settings()[0]._iDisplayStart;
                    dt.one('preXhr', function(e, s, data) {
                        // Just this once, load all data from the server...
                        data.start = 0;
                        data.length = 2147483647;
                        dt.one('preDraw', function(e, settings) {
                            // Call the original action function
                            if (button[0].className.indexOf('buttons-copy') >= 0) {
                                $.fn.dataTable.ext.buttons.copyHtml5.action.call(self, e, dt, button,
                                    config);
                            } else if (button[0].className.indexOf('buttons-excel') >= 0) {
                                $.fn.dataTable.ext.buttons.excelHtml5.available(dt, config) ?
                                    $.fn.dataTable.ext.buttons.excelHtml5.action.call(self, e, dt,
                                        button, config) :
                                    $.fn.dataTable.ext.buttons.excelFlash.action.call(self, e, dt,
                                        button, config);
                            } else if (button[0].className.indexOf('buttons-csv') >= 0) {
                                $.fn.dataTable.ext.buttons.csvHtml5.available(dt, config) ?
                                    $.fn.dataTable.ext.buttons.csvHtml5.action.call(self, e, dt, button,
                                        config) :
                                    $.fn.dataTable.ext.buttons.csvFlash.action.call(self, e, dt, button,
                                        config);
                            } else if (button[0].className.indexOf('buttons-pdf') >= 0) {
                                $.fn.dataTable.ext.buttons.pdfHtml5.available(dt, config) ?
                                    $.fn.dataTable.ext.buttons.pdfHtml5.action.call(self, e, dt, button,
                                        config) :
                                    $.fn.dataTable.ext.buttons.pdfFlash.action.call(self, e, dt, button,
                                        config);
                            } else if (button[0].className.indexOf('buttons-print') >= 0) {
                                $.fn.dataTable.ext.buttons.print.action(e, dt, button, config);
                            }
                            dt.one('preXhr', function(e, s, data) {
                                // DataTables thinks the first item displayed is index 0, but we're not drawing that.
                                // Set the property to what it was before exporting.
                                settings._iDisplayStart = oldStart;
                                data.start = oldStart;
                            });
                            // Reload the grid with the original page. Otherwise, API functions like table.cell(this) don't work properly.
                            setTimeout(dt.ajax.reload, 0);
                            // Prevent rendering of the full data to the DOM

                            setTimeout(function() {
                                $('#modal_loading').modal('hide');
                            }, 3000);
                            return false;


                        });
                    });
                    // Requery the server with the new one-time export settings
                    dt.ajax.reload();

                },

                updateFooter(){
                    const el = document.getElementById("grand_total_footer");
                    if(el){
                        el.innerHTML = "Rp " + this.total;
                    }
                },
                getData(){
                    axios.get('{{ route('admin.trx_payment.report.generate') }}', {
                        params: {
                            period: this.period,
                            type: this.type,
                        }
                    })
                    .then(response => {
                        if(response.data.status === 'success'){
                            this.data = response.data.data.result;

                            // AMBIL TOTAL DARI API
                            this.total = response.data.data.total;

                            this.reloadTable();
                        } else {
                            this.showErrorNotif(response.data.message);
                        }
                    })
                    .catch(error => {
                        this.showErrorNotif(error.message ?? 'Terjadi kesalahan');
                    });
                },
                initTable(){
                    console.log('init');

                    this.table = $('#table_data').DataTable({
                        dom: 'Blfrtip',
                        buttons: [{
                            extend: 'excelHtml5',
                            className: 'btn btn-success mb-3 button-excel',

                            filename: () => {
                                return 'Laporan_Transaksi_per_Payment_' + vueComponent.paymentLabel.replace(/\s+/g, '_');
                            },

                            title: null, // penting: jangan pakai title bawaan datatable

                            exportOptions: {
                                format: {
                                    body: function (data, row, column, node) {
                                        if (column === 3) {
                                            let raw = data.toString()
                                                .replace(/Rp\s?/g, '')
                                                .replace(/\./g, '')
                                                .replace(/,/g, '');
                                            return Number(raw);
                                        }
                                        return data;
                                    }
                                }
                            },

                            customize: function (xlsx) {

                                let sheet = xlsx.xl.worksheets['sheet1.xml'];
                                let sheetData = $('sheetData', sheet);

                                // ===================
                                // 1. TAMBAH HEADER DI BARIS PERTAMA
                                // ===================
                                let labelText = "Laporan Transaksi per Payment " + vueComponent.paymentLabel;

                                // Buat row pertama
                                let headerRow = `
                                    <row r="1">
                                        <c t="inlineStr" r="A1">
                                            <is><t>${labelText}</t></is>
                                        </c>
                                    </row>
                                `;

                                // Insert di paling atas
                                sheetData.prepend(headerRow);

                                // pergeseran row lainnya otomatis ditangani Excel sebagian besar
                                // ===================
                                // 2. AMBIL GRAND TOTAL
                                // ===================

                                let grandTotal = $('#grand_total_footer').text()
                                    .replace(/Rp\s?/g, '')
                                    .replace(/\./g, '')
                                    .replace(/,/g, '');

                                // Hitung jumlah row setelah header ditambahkan
                                let lastRow = $('sheetData row', sheet).length + 1;

                                // Tambahkan baris grand total
                                let totalRow = `
                                    <row r="${lastRow}">
                                        <c t="inlineStr" r="A${lastRow}">
                                            <is><t>Grand Total</t></is>
                                        </c>
                                        <c t="inlineStr" r="B${lastRow}"><is><t></t></is></c>
                                        <c t="inlineStr" r="C${lastRow}"><is><t></t></is></c>
                                        <c t="n" r="D${lastRow}">
                                            <v>${grandTotal}</v>
                                        </c>
                                    </row>
                                `;

                                sheetData.append(totalRow);
                            }
                        }],
                        data: this.data,
                        columns: [
                            { data: null, render: (data, type, row, meta) => meta.row + 1 },
                            { data: 'code' },
                            { data: 'customers' },

                            // tampil di screen tetap format rupiah
                            {
                                data: 'amount',
                                render: $.fn.dataTable.render.number('.', ',', 0, 'Rp ')
                            }
                        ],
                        destroy: true,
                        responsive: true
                    });

                },
                reloadTable(){
                    if(this.table){
                        this.table.clear();
                        this.table.rows.add(this.data);
                        this.table.draw();
                    } else {
                        this.initTable();
                    }

                    this.updateFooter();
                }
            },
            watch: {
                period(){
                    this.getData();
                },
                type(){
                    this.getData();
                }
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
            },
        });
    </script>
@endsection
