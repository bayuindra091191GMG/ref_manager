@extends('layouts.admin')

@section('title')
    <title>Daftar Komisi Manual</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Daftar Komisi Manual
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Daftar Komisi Manual
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <div class="content">
        @include('partials._success')
        @include('partials._error')
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                {{--                <h3 class="block-title">Users</h3>--}}
            </div>
            <div class="block-content block-content-full">
                <div class="row mb-2">
                    <div class="col-sm-3 col-12">
                        <label class="form-label" for="filter_date">Tanggal</label>
                        <input type="text" id="filter_date" class="form-control js-flatpickr" data-date-format="d-m-Y" value="{{ $filterDate }}"/>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-9 col-12">
                        <button type="button" class="btn btn-primary" onclick="filterData();">Filter Data</button>
                    </div>
                    <div class="col-sm-3 col-12 text-end">
                        <a href="{{ route('admin.commission_manual.create') }}" class="btn btn-success mt-lg-3"><i class="fas fa-plus"></i> Tambah</a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <hr/>
                        <div class="table-responsive w-100">
                            <table class="table table-bordered table-striped table-vcenter nowrap" id="table_commission_item_manual">
                                <thead>
                                <tr>
                                    <th class="text-center">Tindakan</th>
                                    <th class="text-center">Tanggal</th>
                                    <th class="text-center">Nama Karyawan</th>
                                    <th class="text-center">Kode Karyawan</th>
                                    <th class="text-center">No. Terapis</th>
                                    <th class="text-center">Alias Terapis</th>
                                    <th class="text-center">Qty Item</th>
                                    <th class="text-center">Jumlah Komisi</th>
                                </tr>
                                </thead>
                                <tbody>
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
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-bs5/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-buttons-bs5/buttons.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/flatpickr/flatpickr.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/sweetalert2/sweetalert2.css') }}">
@endsection

@section('scripts')
    <!-- jQuery (required for DataTables plugin) -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>

    <!-- Page JS Plugins -->
    <script src="{{ asset('js/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-bs5/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('js/moment.min.js') }}"></script>
    <script src="{{ asset('js/moment-id.min.js') }}"></script>
    <script src="{{ asset('js/plugins/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
    <script src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('js/plugins/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('js/plugins/sweetalert2/sweetalert2.js') }}"></script>

    <script>
        flatpickr('.js-flatpickr', {
            "locale": "id"  // locale for this instance only
        });

        let datatable = $('#table_commission_item_manual').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 10,
            responsive: false,
            ajax: {
                url: '{!! route('admin.commission_manual.datatable') !!}',
                data: {
                    'filter_date': '{{ $filterDate }}',
                }
            },
            sWrapper: "dataTables_wrapper dt-bootstrap5",
            sFilterInput: "form-control form-control-sm",
            sLengthSelect: "form-select form-select-sm",
            pagingType: 'full_numbers',
            language: {
                lengthMenu: "_MENU_",
                search: "_INPUT_",
                searchPlaceholder: "Search keyword..",
                info: "Page <strong>_PAGE_</strong> of <strong>_PAGES_</strong>",
                paginate: {
                    first: '<i class="fa fa-angle-double-left"></i>',
                    previous: '<i class="fa fa-angle-left"></i>',
                    next: '<i class="fa fa-angle-right"></i>',
                    last: '<i class="fa fa-angle-double-right"></i>'
                }
            },
            order: [ [1, 'asc'] ],
            columns: [
                { data: 'action', name: 'action', orderable: false, searchable: false, class: 'text-center'},
                { data: 'date', name: 'date', class: 'text-center',
                    render: function ( data, type, row ){
                        if ( type === 'display' || type === 'filter' ){
                            return moment(data).format('DD-M-YYYY');
                        }
                        return data;
                    }
                },
                { data: 'employee_name', name: 'employee_name'},
                { data: 'employee_name', name: 'employee_name'},
                { data: 'partner_number', name: 'partner_number'},
                { data: 'partner_alias', name: 'partner_alias'},
                { data: 'total_qty', name: 'total_qty', class: 'text-end'},
                { data: 'total_commission_amount', name: 'total_commission_amount', class: 'text-end',
                    render: function ( data, type, row ){
                        if ( type === 'display' || type === 'filter' ){
                            data = parseFloat(data);
                            return data.toLocaleString(
                                "de-DE"
                            );
                        }
                        return data;
                    }
                },
            ]
        });

        $.fn.dataTable.ext.search.push(
            function( settings, data, dataIndex ) {
                var exc = $('#exc').val();
                var vfw = data[2]; // use data for the vfw column
                var ref = new RegExp(exc, 'g');

                // VFW
                if (vfw.match(ref))
                {
                    return false;
                }

                return true;
            }
        );

        function filterData(){
            let filterDate = $('#filter_date').val();
            let url = '{{ route('admin.commission_manual.index') }}';

            window.location = url + '?filter_date=' + filterDate;
        }

        $(document).on("click", ".btn-delete", function(){
            let deletedId = $(this).data('id');
            Swal.fire({
                icon: 'warning',
                title: 'Hapus Komisi Manual',
                text: 'Apakah Anda yakin ingin hapus Komisi Manual ini?',
                showCancelButton: true,
                confirmButtonText: 'Ya',
                cancelButtonText: `Tidak`,
                confirmButtonColor: 'red',
                cancelButtonColor: 'orange'
            }).then((result) => {
                if(result.isConfirmed){
                    Swal.showLoading();
                    $.ajax({
                        type:'POST',
                        url:'{{ route('admin.commission_manual.delete') }}',
                        data:{
                            '_token': '{{ csrf_token() }}',
                            'deleted_id': deletedId,
                        },
                        success:function(data){
                            let error = data['error'];
                            if(error === 'none'){
                                datatable.ajax.reload();
                            }
                            else{
                                One.helpers('jq-notify', {type: 'danger', icon: 'fa fa-info-circle me-1', timer: 300, message: 'Terjadi kesalahan input!'});
                            }
                        },
                        error:function(data){
                            One.helpers('jq-notify', {type: 'danger', icon: 'fa fa-info-circle me-1', timer: 300, message: 'Terjadi kesalahan pada internal sistem!'});
                        }
                    });
                }
            });
        });
    </script>
@endsection
