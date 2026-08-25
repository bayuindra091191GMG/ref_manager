@extends('layouts.admin')

@section('title')
    <title>Daftar Terapis</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Daftar Terapis
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Daftar Terapis
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
{{--                <div class="row mb-2">--}}
{{--                    <div class="col-sm-3 col-12">--}}
{{--                        <label class="form-label" for="filter_status">Filter Status</label>--}}
{{--                        <select class="form-select" id="filter_status" name="filter_status">--}}
{{--                            <option value="-1" @if($filterStatusId === -1) selected @endif>Semua</option>--}}
{{--                            <option value="1" @if($filterStatusId === 1) selected @endif>Aktif</option>--}}
{{--                            <option value="2" @if($filterStatusId === 2) selected @endif>Non-Aktif</option>--}}
{{--                        </select>--}}
{{--                    </div>--}}
{{--                </div>--}}
                <div class="row justify-content-between align-items-end mb-2">
                    <div class="col-auto">
                        <form>
                            <div class="d-flex flex-row align-items-end">
                                <div>
                                    <label class="form-label" for="filter_status">Filter Kontrak</label>
                                    <select name="contract" class="form-select" style="width: 300px;">
                                        <option value="">Semua</option>
                                        <option value="end" {{ request()->get('contract') ? 'selected' : '' }}>Kontrak yang hampir habis</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary ms-2">Filter Data</button>
                            </div>
                        </form>
                    </div>
{{--                    <div class="col-sm-9 col-12">--}}
{{--                        <button type="button" class="btn btn-primary" onclick="filterData();">Filter Data</button>--}}
{{--                        &nbsp;--}}
{{--                        <a href="{{ route('admin.employee.index') }}" class="btn btn-primary">Reset Filter</a>--}}
{{--                    </div>--}}
                    <div class="col-auto">
                        <button type="button" class="btn btn-warning" onclick="popPresentModal()">Semua Tidak Hadir</button>

                        @if(Auth::guard('admin')->user()->is_super == 1 || Auth::guard('admin')->user()->is_developer == 1
                        || (Auth::guard('admin')->user()->hasPermissionTo('employee.add')))
                            <a href="{{ route('admin.partner.create') }}" class="btn btn-success"><i class="fas fa-plus"></i> Tambah</a>
                        @endif
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <hr/>
                        <div class="table-responsive w-100">
                            <table class="table table-bordered table-striped table-vcenter nowrap" id="table_partner">
                                <thead>
                                <tr>
                                    <th class="text-center">Tindakan</th>
                                    <th class="text-center">Kode</th>
                                    <th class="text-center">Mesin Absen</th>
{{--                                    <th class="text-center">Nama</th>--}}
                                    <th class="text-center">Alias</th>
                                    <th class="text-center">Hadir?</th>
                                    <th class="text-center">Jenis Terapis</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Tanggal Gabung</th>
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

    <form id="form_toggle_present_all_inactive" method="post" action="{{ route('admin.partner.present.all.inactive') }}">
        @csrf
    </form>
@endsection

@section('styles')
    <!-- Page JS Plugins CSS -->
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-bs5/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-buttons-bs5/buttons.bootstrap5.min.css') }}">
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
    <script src="{{ asset('js/plugins/sweetalert2/sweetalert2.js') }}"></script>

    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script>
        var datatable = $('#table_partner').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 100,
            responsive: false,
            ajax: {
                url: '{!! route('admin.partner.datatable') !!}',
                data: {
                    'filter_branch_id': '{{ $filterBranchId }}',
                    'contract': '{{ request()->get('contract') }}'
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
                { data: 'code', name: 'code', class: 'text-center'},
                { data: 'number', name: 'number', class: 'text-center'},
                // { data: 'name', name: 'name', class: 'text-center'},
                { data: 'alias', name: 'alias', class: 'text-center'},
                { data: 'is_present', name: 'is_present', class: 'text-center', orderable: true, searchable: false,
                    render: function ( data, type, row ){
                        if ( type === 'display' || type === 'filter' ){
                            if(data === 1){
                                return 'YA'
                            }
                            else return 'TIDAK'
                        }
                        return data;
                    }
                },
                { data: 'type', name: 'type', class: 'text-center'},
                { data: 'status_display', name: 'statuses.display', class: 'text-center'},
                { data: 'join_date', name: 'join_date', class: 'text-center', orderable: true, searchable: false,
                    render: function ( data, type, row ){
                        if ( type === 'display' || type === 'filter' ){
                            return moment(data).format('DD-MM-YYYY');
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

        $(document).on("click", ".btn-present", function(){
            let toggledId = $(this).data('id');
            $('#btn_present_' + toggledId).hide(200);
            $.ajax({
                type:'POST',
                url:'{{ route('admin.partner.is_present.toggle') }}',
                data:{
                    '_token': '{{ csrf_token() }}',
                    'toggled_id': toggledId,
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
        })

        function popPresentModal(){
            Swal.fire({
                icon: 'warning',
                title: 'Jadikan Semua Tidak Hadir',
                text: 'Apakah Anda yakin ingin ubah kehadiran semua terapis ke Tidak Hadir?',
                showCancelButton: true,
                confirmButtonText: 'Ya',
                cancelButtonText: `Tidak`,
                confirmButtonColor: 'green',
                cancelButtonColor: 'orange'
            }).then((result) => {
                if(result.isConfirmed){
                    Swal.showLoading();
                    $('#form_toggle_present_all_inactive').submit();
                }
            });
        }

    </script>
@endsection
