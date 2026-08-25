@extends('layouts.admin')

@section('title')
    <title>Detail Transaksi Voucher Aplikasi</title>
@endsection

@section('content')
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">Detail Transaksi Voucher Aplikasi</h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.voucher_order.index') }}">Transaksi Voucher Aplikasi</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">{{ $order->code }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="content">
        @include('partials._success')
        @include('partials._error')
        <div class="block block-rounded">
            <div class="block-content">
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Kode Transaksi</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" value="{{ $order->code }}" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Status</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" value="{{ $order->status->display ?? $order->status_id }}" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">User ID</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" value="{{ $order->created_by }}" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Batas Waktu Pembayaran</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" value="{{ $order->payment_due_at ? $order->payment_due_at->format('d-m-Y H:i') : '-' }}" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Bukti Pembayaran</label>
                    <div class="col-sm-6">
                        @if($order->payment_proof != null)
                            <a href="{{ asset('storage/proofs/'. $order->payment_proof) }}" class="btn btn-primary" target="_blank"><i class="fa fa-fw fa-eye"></i> Lihat Bukti Pembayaran</a>
                            @if($order->status_id == 3 || $order->status_id == 14 || ((int) $order->status_id === 20 && ! $hasGeneratedVoucher))
                                <form id="proof-form" method="post" action="{{ route('admin.voucher_order.check_proof') }}">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $order->id }}">
                                    <input type="hidden" name="payment_proof" value="{{ $order->payment_proof }}">
                                    <input type="hidden" name="status" id="proof_status" value="20">
                                    <div class="d-flex flex-row mt-2">
                                        @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1 || auth()->guard('admin')->user()->hasAnyPermission(['voucher_order.approve']))
                                            <button type="button" id="approveBtn" class="btn btn-success">{{ (int) $order->status_id === 20 ? 'Generate Voucher' : 'Approve' }}</button>
                                        @endif
                                        @if ((int) $order->status_id !== 20 && (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1 || auth()->guard('admin')->user()->hasAnyPermission(['voucher_order.approve'])))
                                            <button type="button" id="rejectBtn" class="ms-2 btn btn-danger">Reject</button>
                                        @endif
                                    </div>
                                </form>
                            @endif
                        @else
                            <input type="text" class="form-control" value="Belum ada bukti pembayaran" readonly>
                        @endif
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-12">
                        <h5>Detail Voucher</h5>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-12">
                        <table class="table table-bordered table-striped table-vcenter">
                            <thead>
                            <tr>
                                <th>Nama Voucher</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Harga</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($details as $detail)
                                <tr>
                                    <td>{{ $detail['name'] }}</td>
                                    <td class="text-center">{{ $detail['qty'] }}</td>
                                    <td class="text-end">{{ number_format($detail['price'], 0, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($detail['subtotal'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Subtotal</th>
                                <th class="text-end">{{ number_format($order->grand_total, 0, ',', '.') }}</th>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end">PPN {{ number_format($order->payment_charge_ppn_percentage, 0, ',', '.') }}%</th>
                                <th class="text-end">{{ number_format($order->payment_charge_ppn_value, 0, ',', '.') }}</th>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end">Total Pembayaran</th>
                                <th class="text-end">{{ number_format($order->total_payment, 0, ',', '.') }}</th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                @if((int) $order->status_id !== 10 && (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1 || auth()->guard('admin')->user()->hasAnyPermission(['voucher_order.cancel'])))
                    <div class="row mb-3">
                        <div class="col-12 text-end">
                            <form id="cancel-form" method="post" action="{{ route('admin.voucher_order.cancel') }}">
                                @csrf
                                <input type="hidden" name="cancelled_order_voucher_id" value="{{ $order->id }}">
                                <button type="button" id="cancelBtn" class="btn btn-danger">Cancel Transaction</button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('js/plugins/sweetalert2/sweetalert2.css') }}">
@endsection

@section('scripts')
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script src="{{ asset('js/plugins/sweetalert2/sweetalert2.js') }}"></script>
    <script>
        function confirmWithPassword(options, onSuccess) {
            Swal.fire({
                icon: options.icon || 'info',
                title: options.title,
                text: options.text,
                showDenyButton: true,
                confirmButtonText: options.confirmButtonText || 'Yes',
                denyButtonText: options.denyButtonText || 'No',
                confirmButtonColor: options.confirmButtonColor,
                denyButtonColor: options.denyButtonColor,
                showClass: {
                    popup: `
                      animate__animated
                      animate__fadeIn
                      animate__faster
                    `
                },
                hideClass: {
                    popup: `
                      animate__animated
                      animate__fadeOut
                      animate__faster
                    `
                },
                showLoaderOnConfirm: true,
                input: 'password',
                inputAttributes: {
                    autocapitalize: 'off',
                    placeholder: 'Password'
                },
                preConfirm: (password) => {
                    return fetch('{{ route('admin.check_password') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            password: password
                        }),
                    }).then((response) => {
                        if (!response.ok) {
                            Swal.showValidationMessage('Gagal memverifikasi password');
                        }

                        return response.json();
                    }).then((response) => {
                        if (!response.success) {
                            Swal.showValidationMessage('Password Salah');
                        }

                        return response;
                    }).catch((error) => {
                        Swal.showValidationMessage('Gagal : ' + error);
                    });
                }
            }).then((result) => {
                if (result.isConfirmed && result.value.success) {
                    onSuccess();
                }
            });
        }

        $('#approveBtn').on('click', function () {
            confirmWithPassword({
                title: 'Approve bukti pembayaran',
                text: 'Apakah anda yakin ingin approve bukti pembayaran voucher?',
            }, function () {
                $('#proof_status').val(20);
                $('#proof-form').submit();
            });
        });

        $('#rejectBtn').on('click', function () {
            confirmWithPassword({
                title: 'Reject bukti pembayaran',
                text: 'Apakah anda yakin ingin reject bukti pembayaran voucher?',
            }, function () {
                $('#proof_status').val(14);
                $('#proof-form').submit();
            });
        });

        $('#cancelBtn').on('click', function () {
            confirmWithPassword({
                icon: 'warning',
                title: 'Batalkan transaksi voucher',
                text: 'Apakah Anda yakin ingin membatalkan transaksi voucher ini?',
                confirmButtonText: 'Ya',
                denyButtonText: 'Tidak',
                confirmButtonColor: 'red',
                denyButtonColor: 'orange',
            }, function () {
                $('#cancel-form').submit();
            });
        });
    </script>
@endsection
