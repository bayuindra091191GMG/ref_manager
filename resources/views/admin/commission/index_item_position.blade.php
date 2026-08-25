@extends('layouts.admin')

@section('title')
    <title>Daftar Komisi Barang berdasarkan Posisi</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Daftar Komisi Barang berdasarkan Posisi
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Daftar Komisi Barang berdasarkan Posisi
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
                @if($filterBranchId < 0)
                    <div class="row mb-2">
                        <div class="col-sm-3 col-12">
                            <label class="form-label" for="filter_branch">Filter Cabang</label>
                            <select class="form-select" id="filter_branch" name="filter_branch">
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" @if($filterBranchId === $branch->id) selected @endif>Cabang {{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-3 col-12">
                            <button type="button" class="btn btn-primary" onclick="filterBranch();" style="margin-top: 25px;">Pilih Cabang</button>
                        </div>
                    </div>
                @else
                    <input type="hidden" id="branch_id" value="{{ $filterBranchId }}" />
                    <div class="row mb-2">
                        <div class="col-sm-3 col-12">
                            <label class="form-label" for="filter_status">Filter Status</label>
                            <select class="form-select" id="filter_status" name="filter_status">
                                <option value="-1" @if($filterStatusId === -1) selected @endif>Semua</option>
                                <option value="1" @if($filterStatusId === 1) selected @endif>Aktif</option>
                                <option value="2" @if($filterStatusId === 2) selected @endif>Non-Aktif</option>
                            </select>
                        </div>
                        <div class="col-sm-3 col-12">
                            <label class="form-label" for="filter_position">Filter Posisi</label>
                            <select class="form-select" id="filter_position" name="filter_position">
                                <option value="-1" @if($filterPositionId === -1) selected @endif>Semua</option>
                                @foreach($positions as $position)
                                    <option value="{{ $position->id }}" @if($filterPositionId === $position->id) selected @endif>{{ $position->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-3 col-12">
                            <label class="form-label" for="filter_item">Filter Barang</label>
                            <select class="form-select" id="filter_item" name="filter_item">
                                @if(!empty($filterItem))
                                    <option value="{{ $filterItem->id }}">{{ $filterItem->code. ' - '. $filterItem->name }}</option>
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-9 col-12">
                            <button type="button" class="btn btn-primary" onclick="filterData();">Filter Data</button>
                            &nbsp;
                            <a href="{{ route('admin.commission.position.item.index', ['branch_id' => $filterBranchId]) }}" class="btn btn-warning">Reset Filter</a>
                            @if($isSuperAdmin)
                                &nbsp;
                                <a href="{{ route('admin.commission.position.item.index') }}" class="btn btn-dark">Ganti Cabang</a>
                            @endif

                        </div>
                        <div class="col-sm-3 col-12 text-end">
                            <a href="{{ route('admin.commission.position.item.create') }}" class="btn btn-success mt-lg-3"><i class="fas fa-plus"></i> Tambah</a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <hr/>
                            <div class="table-responsive w-100">
                                <table class="table table-bordered table-striped table-vcenter nowrap" id="table_commission_position_item">
                                    <thead>
                                    <tr>
                                        <th class="text-center">Tindakan</th>
                                        <th class="text-center">Jabatan</th>
                                        <th class="text-center">Kode Barang</th>
                                        <th class="text-center">Nama Barang</th>
                                        <th class="text-center">Komisi (%)</th>
                                        <th class="text-center">Komisi Flat (Rp)</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>

    <!-- Modal to update commission data -->
    <div class="modal fade" id="modal_edit_commission" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-dialog-slideup">
            <div class="modal-content">
                <div class="block-content">
                    <div class="row mb-2">
                        <label class="col-sm-3 col-form-label" for="edited_item_name">Barang</label>
                        <div class="col-sm-9">
                            <input id="edited_item_name" type="text" class="form-control"
                                   name="edited_item_name" readonly/>
                        </div>
                        <input type="hidden" id="edited_id"/>
                    </div>
                    <div class="row mb-2">
                        <label class="col-sm-3 col-form-label" for="edited_status">Status</label>
                        <div class="col-sm-9">
                            <select class="form-select" id="edited_status" name="edited_status">
                                <option value="1">Aktif</option>
                                <option value="2">Non-Aktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <label class="col-sm-3 col-form-label" for="edited_commission_percentage">Komisi (%)</label>
                        <div class="col-sm-3">
                            <div class="input-group">
                                <input id="edited_commission_percentage" type="text" class="form-control text-end"
                                       name="edited_commission_percentage"/>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <label class="col-sm-3 col-form-label" for="edited_commission_flat">Komisi Flat</label>
                        <div class="col-sm-4">
                            <input id="edited_commission_flat" type="text" class="form-control text-end"
                                   name="edited_commission_flat"/>
                        </div>
                    </div>
                </div>
                <div class="block-content block-content-full text-center bg-body">
                    <button type="button" id="btn_dismiss_update" class="btn btn-warning" data-bs-dismiss="modal">Batal</button>
                    <button type="button" id="btn_confirm_update" class="btn btn-success" onclick="updateCommission();">Ubah</button>
                    <button type="button" id="btn_loading_update" class="btn btn-success" style="display: none;">
                        <i class="spinner-border spinner-border-sm text-green" role="status">
                            <span class="visually-hidden">Mohon Tunggu...</span>
                        </i>
                    </button>
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
    <script src="{{ asset('js/AutoNumeric.js') }}"></script>

    <script>
        @if($filterBranchId > 0)
            var modalEdit = new bootstrap.Modal(document.getElementById('modal_edit_commission'), {'backdrop': 'static', 'keyboard' :false });

            var datatable = $('#table_commission_position_item').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                responsive: false,
                saveState: true,
                ajax: {
                    url: '{!! route('admin.commission.position.item.datatable') !!}',
                    data: {
                        'filter_branch_id': '{{ $filterBranchId }}',
                        'filter_status_id': '{{ $filterStatusId }}',
                        'filter_position_id': '{{ $filterPositionId }}'
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
                order: [ [2, 'asc'] ],
                columns: [
                    { data: 'action', name: 'action', orderable: false, searchable: false, class: 'text-center'},
                    { data: 'position_name', name: 'employee_positions.name', class: 'text-center'},
                    { data: 'item_code', name: 'items.code'},
                    { data: 'item_name', name: 'items.name'},
                    { data: 'commission_percentage', name: 'commission_percentage', class: 'text-center'},
                    { data: 'commission_flat', name: 'commission_flat', class: 'text-end',
                        render: function ( data, type, row ){
                            if ( type === 'display' || type === 'filter' ){
                                data = parseInt(data);
                                return data.toLocaleString(
                                    "de-DE"
                                );
                            }
                            return data;
                        }
                    },
                    { data: 'status_id', name: 'status_id', class: 'text-center', orderable: false, searchable: false,
                        render: function ( data, type, row ){
                            if ( type === 'display' || type === 'filter' ){
                                if(data == 1){
                                    return 'AKTIF';
                                }
                                else{
                                    return 'NON-AKTIF'
                                }
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

            $('#filter_item').select2({
                placeholder: {
                    id: '-1',
                    text: '--- Pilih Barang ---'
                },
                width: '100%',
                minimumInputLength: 0,
                ajax: {
                    url: '{{ route('admin.item.select') }}',
                    dataType: 'json',
                    data: function (params) {
                        return {
                            q: $.trim(params.term),
                            branch_id: {{ $filterBranchId }}
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data
                        };
                    }
                }
            });

            function filterData(){
                let branchId = $('#branch_id').val();
                let statusId = $('#filter_status').val();
                let positionId = $('#filter_position').val();
                let itemId = $('#filter_item').val();
                let url = '{{ route('admin.commission.position.item.index') }}';

                window.location = url + '?branch_id=' + branchId + '&status_id=' + statusId + '&position_id=' + positionId + '&item_id=' + itemId;
            }

            var editedCommissionPercentageFormat = new AutoNumeric('#edited_commission_percentage', {
                minimumValue: '0',
                maximumValue: '100',
                digitGroupSeparator: '.',
                decimalCharacter: ',',
                decimalPlaces: 2,
                modifyValueOnWheel: false,
                allowDecimalPadding: false
            });

            var editedCommissionFlatFormat = new AutoNumeric('#edited_commission_flat', {
                minimumValue: '0',
                maximumValue: '999999999999',
                digitGroupSeparator: '.',
                decimalCharacter: ',',
                decimalPlaces: 2,
                modifyValueOnWheel: false,
                allowDecimalPadding: false
            });

            $(document).on("click", ".btn-edit", function(){
                // alert($(this).data('id'));
                let commissionPercentage = parseFloat($(this).data('commission-percentage'));
                let commissionFlat = parseFloat($(this).data('commission-flat'));

                modalEdit.show();
                $('#btn_dismiss_update').show();
                $('#btn_confirm_update').show();
                $('#btn_loading_update').hide();

                $('#edited_id').val($(this).data('id'));
                $('#edited_item_name').val($(this).data('item-name'));
                $('#edited_status').val($(this).data('status-id'));
                editedCommissionPercentageFormat.set(commissionPercentage);
                editedCommissionFlatFormat.set(commissionFlat);
            });

            function updateCommission(){
                $('#btn_dismiss_update').hide();
                $('#btn_confirm_update').hide();
                $('#btn_loading_update').show(200);

                $.ajax({
                    type:'POST',
                    url:'{{ route('admin.commission.position.item.update') }}',
                    data:{
                        '_token': '{{ csrf_token() }}',
                        'edited_id': $('#edited_id').val(),
                        'edited_status_id': $('#edited_status').val(),
                        'edited_commission_percentage': $('#edited_commission_percentage').val(),
                        'edited_commission_flat': $('#edited_commission_flat').val()
                    },
                    success:function(data){
                        let error = data['error'];
                        if(error === 'none'){
                            One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', timer: 2000, message: 'Berhasil ubah data komisi ' + $('#edited_item_name').val()});
                            datatable.ajax.reload();
                        }
                        else{
                            One.helpers('jq-notify', {type: 'danger', icon: 'fa fa-info-circle me-1', timer: 300, message: 'Terjadi kesalahan input!'});
                        }

                        $('#btn_dismiss_update').show();
                        $('#btn_confirm_update').show();
                        $('#btn_loading_update').hide();
                        modalEdit.hide();
                    },
                    error:function(data){
                        One.helpers('jq-notify', {type: 'danger', icon: 'fa fa-info-circle me-1', timer: 300, message: 'Terjadi kesalahan pada internal sistem!'});

                        $('#btn_dismiss_update').show();
                        $('#btn_confirm_update').show();
                        $('#btn_loading_update').hide();
                        modalEdit.hide();
                    }
                });
            }
        @endif


        function filterBranch(){
            let branchId = $('#filter_branch').val();
            let url = '{{ route('admin.commission.position.item.index') }}';

            window.location = url + '?branch_id=' + branchId;
        }

    </script>
@endsection
