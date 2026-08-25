@extends('layouts.admin')

@section('title')
    <title>Laporan Komisi Therapist</title>
@endsection

@section('content')
    <!-- Hero -->
     <div>
        <div class="content space-top">
              <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                  <div class="flex-grow-1">
                      <h1 class="h3 fw-bold mb-2">
                          Laporan Komisi Therapist
                      </h1>
                  </div>
                  <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                      <ol class="breadcrumb breadcrumb-alt">
                          <li class="breadcrumb-item">
                              <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                          </li>
                          <li class="breadcrumb-item" aria-current="page">
                              Laporan Komisi Therapist
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
                        {{-- <div class="col-md-4">
                            <label for="type" class="form-label">Tipe Pembayaran <span class="text-red">*</span></label>
                            <select class="form-select" name="type" id="type" required>
                                @foreach($types as $type)
                                    <option value="{{ $type->id }}">{{ $type->description }}</option>
                                @endforeach
                            </select>
                        </div> --}}
                    </div>

                    <table id="table_data" class="table table-bordered">
                        <thead>
                            <tr>
                                <th width="15%">Tanggal</th>
                                <th>Nama</th>
                                <th>HK</th>
                                <th>U. Kehadiran</th>
                                <th>Jumlah</th>
                                <th width="5%">Pot Telat</th>
                                <th>Fee Lembur</th>
                                <th>Jumlah</th>
                                <th>Komisi</th>
                                <th>Insentif</th>
                                <th>THP</th>
                                <th>Nama Bank</th>
                                <th>No Rekening</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- <tr v-for="item in results">
                                <td>@{{ item.periode }}</td>
                                <td>@{{ item.name }}</td>
                                <td>@{{ item.hk }} Hari</td>
                                <td v-text="parseInt(item.hk_amount).toLocaleString('id-ID')"></td>
                                <td v-text="parseInt(item.hk_total).toLocaleString('id-ID')"></td>
                                <td v-text="parseInt(item.late_amount).toLocaleString('id-ID')"></td>
                                <td v-text="parseInt(item.lembur_amount).toLocaleString('id-ID')"></td>
                                <td v-text="parseInt(item.subtotal).toLocaleString('id-ID')"></td>
                                <td v-text="parseInt(item.commision).toLocaleString('id-ID')"></td>
                                <td v-text="parseInt(item.insentif).toLocaleString('id-ID')"></td>
                                <td v-text="parseInt(item.thp).toLocaleString('id-ID')"></td>
                                <td>@{{ item.bank_name }}</td>
                                <td>@{{ item.no_rek }}</td>
                            </tr> --}}
                        </tbody>
                        <tfoot>
                            <tr style="font-weight: bold; background-color: #f8f9fa;">
                                <td colspan="7" class="text-end">TOTAL</td>
                                <td v-text="sumColumn('subtotal').toLocaleString('id-ID')"></td>
                                <td v-text="sumColumn('commision').toLocaleString('id-ID')"></td>
                                <td></td>
                                <td v-text="sumColumn('thp').toLocaleString('id-ID')"></td>
                                <td colspan="2"></td>
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
                type: 'CASH',
                results: [],
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
                sumColumn(column) {
                    if (!this.results || this.results.length === 0) return 0;
                    return this.results.reduce((total, item) => {
                        const val = parseFloat(item[column]) || 0;
                        return total + val;
                    }, 0);
                },

                getData(){
                    axios.get('{{ route('admin.therapist_commission.report.generate') }}', {
                        params: {
                            period: this.period
                        }
                    })
                    .then(response => {
                        if(response.data.status === 'success'){
                            debugger;
                            this.results = response.data.data;
                            this.reloadTable();
                        } else {
                            this.showErrorNotif(response.data.message);
                        }
                    })
                    .catch(error => {
                        this.showErrorNotif(error.message ?? 'Terjadi kesalahan');
                    });
                },
                initTable() {
                    let self = this;

                    this.table = $('#table_data').DataTable({
                        dom: 'Blfrtip',
                        destroy: true,
                        responsive: true,

                        data: self.results,
                        pageLength: 100,
                        lengthMenu: [[100, -1], [100, "Semua"]],

                        columns: [
                            { data: 'periode' },
                            { data: 'name' },
                            {
                                data: 'hk',
                                render: data => (data ? data + " Hari" : "0 Hari")
                            },

                            {
                                data: 'hk_amount',
                                render: data => (parseInt(data) || 0).toLocaleString('id-ID')
                            },
                            {
                                data: 'hk_total',
                                render: data => (parseInt(data) || 0).toLocaleString('id-ID')
                            },
                            {
                                data: 'late_amount',
                                render: data => (parseInt(data) || 0).toLocaleString('id-ID')
                            },
                            {
                                data: 'lembur_amount',
                                render: data => (parseInt(data) || 0).toLocaleString('id-ID')
                            },
                            {
                                data: 'subtotal',
                                render: data => (parseInt(data) || 0).toLocaleString('id-ID')
                            },
                            {
                                data: 'commision',
                                render: data => (parseInt(data) || 0).toLocaleString('id-ID')
                            },
                            {
                                data: 'insentif',
                                render: data => (parseInt(data) || 0).toLocaleString('id-ID')
                            },
                            {
                                data: 'thp',
                                render: data => (parseInt(data) || 0).toLocaleString('id-ID')
                            },
                            { data: 'bank_name' },
                            { data: 'no_rek' },
                        ],


                        buttons: [
                            {
                                extend: 'excelHtml5',
                                className: 'btn btn-success mb-3 button-excel',

                                filename: () => {
                                    return 'Laporan_Komisi_Therapist_' + self.period.replace(/ /g, '_');
                                },

                                title: null,

                                exportOptions: {
                                    format: {
                                        body: function (data, row, column) {

                                            if ([2,3,4,5,6,7,8,9,10].includes(column)) {
                                                return data.toString()
                                                    .replace(/\./g, '')
                                                    .replace(/,/g, '')
                                                    .replace(/[^0-9\-]/g, '') || 0;
                                            }
                                            return data;
                                        }
                                    }
                                },

                                customize: function (xlsx) {
                                    let sheet = xlsx.xl.worksheets['sheet1.xml'];
                                    let sheetData = $('sheetData', sheet);

                                    // Header
                                    let headerTitle = "Laporan Komisi Therapist - Periode " + self.period;
                                    sheetData.prepend(`
                                        <row r="1">
                                            <c t="inlineStr" r="A1">
                                                <is><t>${headerTitle}</t></is>
                                            </c>
                                        </row>
                                    `);

                                    // Total
                                    let subtotal = self.sumColumn("subtotal");
                                    let komisi = self.sumColumn("commision");
                                    let thp = self.sumColumn("thp");

                                    let lastRow = $('sheetData row', sheet).length + 1;

                                    sheetData.append(`
                                        <row r="${lastRow}">
                                            <c t="inlineStr" r="A${lastRow}">
                                                <is><t>GRAND TOTAL</t></is>
                                            </c>
                                            <c t="n" r="H${lastRow}"><v>${subtotal}</v></c>
                                            <c t="n" r="I${lastRow}"><v>${komisi}</v></c>
                                            <c t="n" r="K${lastRow}"><v>${thp}</v></c>
                                        </row>
                                    `);
                                }
                            }
                        ]
                    });
                },


                reloadTable(){
                    if(this.table){
                        this.table.clear();
                        this.table.rows.add(this.results);
                        this.table.draw();
                    } else {
                        this.initTable();
                    }
                }

            },
            watch: {
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
            },
        });
    </script>
@endsection
