@extends('layouts.admin')

@section('title')
    <title>Absensi</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Absensi
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.attendance.index') }}">Filter</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Absensi
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
            <div class="block-content">
                <div class="row mb-3">
                    <div class="col-12 text-end">
                        <a href="{{ route('admin.attendance.create') }}" class="btn btn-outline-success mt-lg-3" ><i class="fas fa-plus"></i> Absen Karyawan</a>
                        <a href="{{ route('admin.attendance.create_partner') }}" class="btn btn-outline-success mt-lg-3" ><i class="fas fa-plus"></i> Absen Therapis</a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive w-100" >
                            <table class="table table-bordered table-striped table-vcenter js-dataTable-full nowrap">
                                <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tipe</th>
                                    <th>Nama</th>
                                    <th>Tanggal</th>
                                    <th>Jam Masuk</th>
                                    <th>Jam Pulang</th>
                                    <th>Foto Masuk</th>
                                    <th>Foto Pulang</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($data as $item)
                                    <tr>
                                        <td>{{ $item['no'] }}</td>
                                        <td>{{ $item['type'] }}</td>
                                        <td>{{ $item['name'] }}</td>
                                        <td>{{ $item['date'] }}</td>
                                        <td>{{ $item['in_time'] }}</td>
                                        <td>{{ $item['out_time'] }}</td>
                                        <td>
                                            @if($item['img_in'] != '')
                                                <img src="{{ $item['img_in'] }}" width="100"/>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($item['img_out'] != '')
                                                <img src="{{ $item['img_out'] }}" width="100"/>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
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
@endsection

@section('scripts')
    <!-- jQuery (required for DataTables plugin) -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>

    <!-- Page JS Plugins -->
    <script src="{{ asset('js/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-bs5/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('js/moment.min.js') }}"></script>
    <script src="{{ asset('js/plugins/bootstrap-notify/bootstrap-notify.min.js') }}"></script>

{{--    <script>--}}
{{--        $(function() {--}}
{{--            $('#table_locker').DataTable({--}}
{{--                processing: true,--}}
{{--                serverSide: true,--}}
{{--                pageLength: 10,--}}
{{--                responsive: false,--}}
{{--                ajax: {--}}
{{--                    url: '{!! route('admin.attendance.datatable') !!}'--}}
{{--                },--}}
{{--                sWrapper: "dataTables_wrapper dt-bootstrap5",--}}
{{--                sFilterInput: "form-control form-control-sm",--}}
{{--                sLengthSelect: "form-select form-select-sm",--}}
{{--                pagingType: 'full_numbers',--}}
{{--                language: {--}}
{{--                    lengthMenu: "_MENU_",--}}
{{--                    search: "_INPUT_",--}}
{{--                    searchPlaceholder: "Search keyword..",--}}
{{--                    info: "Page <strong>_PAGE_</strong> of <strong>_PAGES_</strong>",--}}
{{--                    paginate: {--}}
{{--                        first: '<i class="fa fa-angle-double-left"></i>',--}}
{{--                        previous: '<i class="fa fa-angle-left"></i>',--}}
{{--                        next: '<i class="fa fa-angle-right"></i>',--}}
{{--                        last: '<i class="fa fa-angle-double-right"></i>'--}}
{{--                    }--}}
{{--                },--}}
{{--                order: [ ],--}}
{{--                columns: [--}}
{{--                    { data: 'type', name: 'type', class: 'text-center'},--}}
{{--                    { data: 'code', name: 'code', class: 'text-center'},--}}
{{--                    { data: 'name', name: 'name', class: 'text-center'},--}}
{{--                    { data: 'date', name: 'date', class: 'text-center', orderable: true, searchable: false,--}}
{{--                        render: function ( data, type, row ){--}}
{{--                            if ( type === 'display' || type === 'filter' ){--}}
{{--                                return moment(data).format('DD MMM YYYY');--}}
{{--                            }--}}
{{--                            return data;--}}
{{--                        }--}}
{{--                    },--}}
{{--                    { data: 'in_time', name: 'in_time', class: 'text-center', orderable: false, searchable: false,},--}}
{{--                    { data: 'out_time', name: 'out_time', class: 'text-center', orderable: true, searchable: false,},--}}
{{--                    { data: 'img_path', name: 'img_path', class: 'text-center', orderable: true, searchable: false,},--}}
{{--                ]--}}
{{--            });--}}
{{--        });--}}
{{--        $.fn.dataTable.ext.search.push(--}}
{{--            function( settings, data, dataIndex ) {--}}
{{--                var exc = $('#exc').val();--}}
{{--                var vfw = data[2]; // use data for the vfw column--}}
{{--                var ref = new RegExp(exc, 'g');--}}

{{--                // VFW--}}
{{--                if (vfw.match(ref))--}}
{{--                {--}}
{{--                    return false;--}}
{{--                }--}}

{{--                return true;--}}
{{--            }--}}
{{--        );--}}

{{--        $(document).on('click', '.delete-modal', function(){--}}
{{--            let id = $(this).data("id");--}}
{{--            let name = $(this).data("number");--}}

{{--            modalDelete(id, name);--}}
{{--        });--}}

{{--        function modalDelete(id, name){--}}
{{--            $('#deleted_locker_id').val(id);--}}
{{--            $('#deleted_locker_number').html(name);--}}

{{--            modalConfirmDelete.show();--}}
{{--        }--}}

{{--        function confirmDelete(){--}}
{{--            let id = $('#deleted_locker_id').val();--}}
{{--            let number = $('#deleted_locker_number').html();--}}

{{--            $('#btn_loading_delete').show(100);--}}
{{--            $('#btn_submit_delete').hide();--}}

{{--            $.ajax({--}}
{{--                type:'POST',--}}
{{--                url:'{{ route('admin.bank_cash_in.delete') }}',--}}
{{--                data:{--}}
{{--                    '_token': '{{ csrf_token() }}',--}}
{{--                    'id': id--}}
{{--                },--}}
{{--                success:function(data){--}}
{{--                    if(data === 'Sukses'){--}}
{{--                        One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1',--}}
{{--                            timer: 2000,--}}
{{--                            message: 'Selected data has been successfully deleted!'});--}}
{{--                        window.setTimeout( redirect, 2000 );--}}
{{--                    }--}}
{{--                    else{--}}
{{--                        $('#btn_submit_delete').show(100);--}}
{{--                        $('#btn_loading_delete').hide();--}}
{{--                        One.helpers('jq-notify', {type: 'danger', icon: 'fa fa-info-circle me-1', timer: 2000, message: 'BAD REQUEST!'});--}}
{{--                    }--}}
{{--                },--}}
{{--                error:function(data){--}}
{{--                    $('#btn_submit_delete').show(100);--}}
{{--                    $('#btn_loading_delete').hide();--}}
{{--                    One.helpers('jq-notify', {type: 'danger', icon: 'fa fa-info-circle me-1', timer: 2000, message: 'Internal server error!'});--}}
{{--                }--}}
{{--            });--}}
{{--        }--}}

{{--        function redirect(){--}}
{{--            window.location.href = '{{ route('admin.bank_cash_in.index') }}';--}}
{{--        }--}}

{{--        $('#approve_submit').click(function () {--}}

{{--            $(".approvalCheckbox:checked").each(function () {--}}
{{--                var value = $(this).val();--}}
{{--                $('#approve-id').append("<input name='ids[]' type='hidden' value='" + value +"'>");--}}
{{--            });--}}

{{--            $('#approve_submit').hide();--}}
{{--            $('#btn_loading').show(200);--}}

{{--            $('#form_approve_process').submit();--}}
{{--        });--}}
{{--        $("#select_all").click(function(){--}}
{{--            $('.approvalCheckbox').prop('checked', this.checked);--}}
{{--        });--}}
{{--    </script>--}}
@endsection
