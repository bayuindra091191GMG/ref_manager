@extends('layouts.admin')

@section('title')
    <title>Detail Kode Voucher</title>
@endsection

@section('content')
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Detail Kode Voucher
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Detail Kode Voucher
                        </li>
                    </ol>
                </nav>
            </div>

            @include('partials._success')
            @include('partials._error')

            <div class="block block-rounded">
                <div class="block-content">

                    {{-- <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="owner">Pemilik</label>
                                <input type="text" class="form-control" id="owner" value="{{ $data['owner'] }}" readonly>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="created_by">Owner</label>
                                <input type="text" class="form-control" id="created_by" value="{{ $data['created_by'] }}" readonly>
                            </div>
                        </div>
                    </div> --}}
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="jenis_voucher">Jenis Voucher</label>
                                <input type="text" class="form-control" id="jenis_voucher" value="{{ $data['jenis_voucher'] }}" readonly>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="quantity">Quantity</label>
                                <input type="text" class="form-control" id="quantity" value="{{ $data['quantity'] }}" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="notes">Notes</label>
                                <textarea class="form-control" id="notes" readonly>{{ $data['notes'] }}</textarea>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <input type="text" class="form-control" id="status" value="{{ $data['status'] }}" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="exp_date">EXP Date</label>
                                <input type="text" class="form-control" id="exp_date" value="{{ $data['exp_date'] }}" readonly>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="tanggal_dibuat">Tanggal Dibuat</label>
                                <input type="text" class="form-control" id="tanggal_dibuat" value="{{ $data['tanggal_dibuat'] }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="block block-rounded">
                <div class="block-content">
                    <div class="row">
                        <table class="table table-bordered table-striped table-vcenter js-dataTable-full" id="table_vouchers">
                            <thead>
                            <tr>
                                <th class="text-center" style="width: 80px"></th>
                                <th class="text-center ">Kode Voucher</th>
                                <th class="text-center ">Tanggal Dibuat</th>
                                <th class="text-center ">Tanggal Kadaluarsa</th>
                                <th class="text-center " >Sudah Digunakan</th>
                                <th class="text-center ">Kode Transaksi</th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach ($data['userVoucherCode'] as $userVC)
                                    <tr>
                                        <td class="text-center">
                                            @if($userVC->is_used == 0 && $userVC->void == 0)
{{--                                                <button type="button" class="btn btn-danger void-btn" data-id="{{ $userVC->id }}"><i class="fa fa-fw fa-ban"></i></button>--}}
                                                <input class="form-check-input approvalCheckbox" type="checkbox"  value="{{ $userVC->id }}">
                                            @endif
                                        </td>
                                        <td>{{ $userVC->code }}</td>
                                        <td>{{ \Carbon\Carbon::parse($userVC->created_at)->format('d M Y') }}</td>
                                        <td>
                                            @if($userVC->is_lifetime == 1)
                                                -
                                            @else
                                                {{ \Carbon\Carbon::parse($userVC->expired_date)->format('d M Y') }}
                                            @endif
                                        </td>
                                        <td>{{ $userVC->void == 1 ? 'Void' : ($userVC->is_used == 1 ? 'Sudah' : "Belum") }}</td>
                                        @if ($userVC->transaction_id != null)
                                            <td><a href="{{ route('admin.transaction.show', ['id' => $userVC->transaction_id]) }}">{{ $userVC->transaction_code }}</a></td>
                                        @else
                                            <td></td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-confirm-cancel" tabindex="-1" role="dialog">
        <form action="{{ route('admin.voucher.void') }}" method="post" id="form-void">
            @csrf
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-slideup">
                <div class="modal-content">
                    <div class="block-content">
                        <div class="row">
                            <div class="col-12 text-center">
                                <span>Apakah Anda yakin untuk void Voucher?</span>
                                <div id="approve-id">

                                </div>
                            </div>
                        </div>
                        <div class="row justify-content-center">
                            <div class="col-2">
                                <label>Password</label>
                            </div>
                            <div class="col-6">
                                <input type="password" name="password" class="form-control" placeholder="Password"/>
                            </div>
                        </div>
                    </div>
                    <div class="block-content block-content-full text-center bg-body">
                        <button type="button" class="btn btn-warning" id="btn_submit_cancel" data-bs-dismiss="modal">No</button>
                        <button type="button" id="btn_submit" class="btn btn-danger" onclick="confirmVoid()">Yes</button>
                        <button type="button" id="btn_loading" class="btn btn-danger" style="display: none;">
                            <i class="spinner-border spinner-border-sm text-green" role="status">
                                <span class="visually-hidden">Please wait...</span>
                            </i>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('styles')
    <!-- Page JS Plugins CSS -->
    <link rel="stylesheet" href="{{ asset('js/plugins/flatpickr/flatpickr.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-bs5/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-buttons-bs5/buttons.bootstrap5.min.css') }}">
@endsection
@section('scripts')
    <!-- jQuery (required for DataTables plugin) -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>

    <!-- Page JS Plugins -->
    <script src="{{ asset('js/plugins/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-bs5/dataTables.bootstrap5.min.js') }}"></script>

    <script src="https://cdn.datatables.net/buttons/2.1.0/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.1.0/js/buttons.html5.min.js"></script>

    <script>
        $(function() {
            $('#table_vouchers').DataTable({
            pageLength: 10,
            responsive: false,
            sWrapper: "dataTables_wrapper dt-bootstrap5",
            sFilterInput: "form-control form-control-sm",
            sLengthSelect: "form-select form-select-sm",
            pagingType: 'full_numbers',
            dom: 'Blfrtip',
            buttons: [
                {
                    extend: 'excel',
                    className: 'btn btn-success mb-3 button-excel',
                    filename: 'voucher_detail_{{ $data["jenis_voucher"] ?? "export" }}'
                },
                {
                    extend: 'copy',
                    className: 'btn btn-warning mb-3 button-copy'
                },
                {
                    className: 'btn btn-danger mb-3 ms-auto',
                    text: 'Void',
                    action: function (e, dt, node, config) {
                        $('#approve-id').html('');
                        $(".approvalCheckbox:checked").each(function () {
                            var value = $(this).val();
                            $('#approve-id').append("<input name='ids[]' type='hidden' value='" + value +"'>");
                        });

                        $('#modal-confirm-cancel').modal('show');
                    }

                }
            ],
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
            }
            });
        });

        $('#table_vouchers').on('click', '.void-btn', function(){
            var id = $(this).data('id');
            $('#voucher_id').val(id);
            $('#modal-confirm-cancel').modal('show');
        });

        function confirmVoid(){
            $('#btn_loading').show();
            $('#btn_submit').hide();
            $('#btn_submit_cancel').hide();
            $('#form-void').submit();
        }
    </script>
@endsection
