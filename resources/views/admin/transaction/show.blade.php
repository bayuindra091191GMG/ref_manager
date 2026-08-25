@extends('layouts.admin')

@section('title')
    <title>Detil Transaksi</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Detil Transaksi
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.transaction.index') }}">Daftar Transaksi</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Detil Transaksi
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->
    <div id="vue-section" class="content">
        <div class="row items-push">
            <div class="col-md-12 col-xl-12">
                <div class="block block-rounded h-100 mb-0">
                    <div class="block-content block-content-full">
                        <div class="row">
                            <div class="col-12">
                                <div class="row">
                                    <div class="col-sm-12">
                                        @include('partials._success')
                                        @include('partials._error')
                                    </div>
                                </div>
                                @if(count($errors))
                                    <div class="row">
                                        <div class="col-lg-8">
                                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                <ul>
                                                    @foreach($errors->all() as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <div class="block-content">
                                        <div class="row mb-3">
                                            <div class="col-12 text-end">
                                                @php
                                                    $urlReprint = route('admin.monitor_room.print',['id' => $invoice->id]) . '?reprint=true';
                                                @endphp
                                                <a href="{{ $urlReprint }}"  target="_blank" class="btn btn-primary">Reprint</a>
                                                @if($invoice->status_id != 16)
                                                    <button type="button" onclick="showModalVoid()" class="btn btn-danger">Void</button>
{{--                                                    <a target="_blank" href="{{ route('admin.transaction.show_to_change', ['id' => $invoice->id]) }}" class="btn btn-warning">Ubah Payment</a>--}}
                                                @endif
                                                @if(!empty($journal))
                                                    <a href="{{ route('admin.journal.show', ['id'=> $journal->id ]) }}" target="_blank" class="btn btn-info">Lihat Jurnal</a>
                                                @endif
{{--                                                @if(!$header['is_approved'] && $header['status_id'] == 9)--}}
{{--                                                    <button type="button" class="btn btn-success" onclick="approveModal();">Approve</button>--}}
{{--                                                @endif--}}

{{--                                                @if($header['is_approved'])--}}
{{--                                                    <a href="{{ route('admin.journal.show', ['id'=> $journal_id ]) }}" target="_blank" class="btn btn-info">Lihat Jurnal</a>--}}
{{--                                                @endif--}}

{{--                                                @if($header['is_happy_hour'] == 1 && $header['gro'] != 'Shadow')--}}
{{--                                                    <button type="button" class="btn btn-primary" onclick="fixHappyHour();">Fix Happy Hour</button>--}}
{{--                                                    <button type="button" class="btn btn-warning" onclick="deleteHappyHour();">Delete Happy Hour</button>--}}
{{--                                                @endif--}}
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label" for="code">Status</label>
                                            <div class="col-sm-6">
                                                <input type="text" id="code" class="form-control" readonly value="{{ $invoice->status->display }}"/>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label" for="code">Kode</label>
                                            <div class="col-sm-6">
                                                <input type="text" id="code" class="form-control" readonly value="{{ $invoice->code }}"/>
                                            </div>
                                        </div>
                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label" for="room">Dibuat Pada</label>
                                        <div class="col-sm-6">
                                            <input type="text" id="room" class="form-control" readonly value="{{ \Carbon\Carbon::parse($invoice->created_at)->format('d-m-Y H:i') }}"/>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label" for="locker">Kasir</label>
                                        <div class="col-sm-6">
                                            <input type="text" id="locker" class="form-control" readonly value="{{ $headers->map(fn($header) => $header->employee_id != null ? $header->employee->name : '-')->implode(', ') }}"/>
                                        </div>
                                    </div>
                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label" for="locker">Nama Member</label>
                                            <div class="col-sm-6">
                                                <input type="text" id="locker" class="form-control" readonly value="{{ $invoice->member_name }}"/>
                                            </div>
                                        </div>
                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label" for="locker">Nomor Telepon Member</label>
                                        <div class="col-sm-6">
                                            <input type="text" id="locker" class="form-control" readonly value="{{ $invoice->member_phone }}"/>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label" for="room">Room</label>
                                        <div class="col-sm-6">
                                            <input type="text" id="room" class="form-control" readonly value="{{ $headers->map(fn($header) => $header->is_prewed == 0 && $header->room_id != null ? $header->room->number : '-')->implode(', ') }}"/>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label" for="room">Tanggal/Waktu Treatment</label>
                                        <div class="col-sm-6">
                                            <input type="text" id="room" class="form-control" readonly value="{{ $headers->map(fn($header) => \Carbon\Carbon::parse($header->start_time)->format('d M Y, H:i'))->implode(', ') }}"/>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label" for="room">Total Tamu</label>
                                        <div class="col-sm-6">
                                            <div class="input-group mb-2">
                                                <span class="input-group-text">Pria</span>
                                                <input type="text" class="form-control" id="male_guest" name="male_guest" value="{{ $headers->map(fn($header) => $header->total_cust_male)->implode(', ') }}" readonly>
                                            </div>
                                            <div class="input-group">
                                                <span class="input-group-text">Wanita</span>
                                                <input type="text" class="form-control" id="female_guest" name="female_guest" value="{{ $headers->map(fn($header) => $header->total_cust_female)->implode(', ') }}" readonly>
                                            </div>
                                        </div>
                                    </div>

                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label" for="amount">Amount</label>
                                            <div class="col-sm-6">
                                                <input type="text" id="amount" class="form-control" readonly value="{{ number_format($invoice->grandtotal, 0, ',', '.') }}"/>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label" for="return">Kembalian</label>
                                            <div class="col-sm-6">
                                                <input type="text" id="return" class="form-control" readonly value="{{ number_format($invoice->return_value, 0, ',', '.') }}"/>
                                            </div>
                                        </div>
{{--                                        <div class="row mb-3">--}}
{{--                                            <label class="col-sm-3 col-form-label" for="approved_at">Tanggal Approve</label>--}}
{{--                                            <div class="col-sm-4">--}}
{{--                                                <input type="text" id="approved_at" class="form-control" readonly value="{{ $header['approved_at'] }}"/>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                        <div class="row mb-3">--}}
{{--                                            <label class="col-sm-3 col-form-label" for="approved_by">Approve Oleh</label>--}}
{{--                                            <div class="col-sm-4">--}}
{{--                                                <input type="text" id="approved_by" class="form-control" readonly value="{{ $header['approved_by'] }}"/>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                        @if($member['isMember'])--}}
{{--                                        <div class="row mb-3">--}}
{{--                                            <label class="col-sm-3 col-form-label" for="approved_by">Member Data</label>--}}
{{--                                            <div class="col-sm-4">--}}
{{--                                                <ul>--}}
{{--                                                    <li>Kode: {{ $member['code'] }}</li>--}}
{{--                                                    <li>Nama: {{ $member['name'] }}</li>--}}
{{--                                                    <li>Phone: {{ $member['phone'] ?? '-' }}</li>--}}
{{--                                                </ul>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                        @endif--}}
                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label" for="return">Detil Transaksi
{{--                                                @if($header['is_happy_hour'])--}}
{{--                                                    <br/><label style="color: red;">Happy Hour</label>--}}
{{--                                                @endif--}}
{{--                                                @if($header['foc'] != null)--}}
{{--                                                    @if($header['foc'] != 'no' && $header['foc'] != 'NO')--}}
{{--                                                        <br/><label style="color: red; font-size: 20px;">FOC</label>--}}
{{--                                                    @endif--}}
{{--                                                @endif--}}
                                            </label>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-12">
                                                <table class="table table-bordered table-striped table-vcenter">
                                                    <thead>
                                                    <tr>
                                                        <th>No</th>
                                                        <th>Therapist</th>
                                                        <th>Nama Paket/Item</th>
                                                        <th>Qty</th>
                                                        <th>Harga</th>
                                                        <th>Subtotal</th>
                                                        <th>Diskon</th>
                                                        <th>Voucher</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @php($no = 1)
                                                    @foreach($headers as $header)
                                                        {{-- @php($detailCount = $header->transaction_details->count()) --}}
                                                        @foreach($header->transaction_details as $index => $detail)
                                                            @php($startTime = \Carbon\Carbon::parse($detail->start_time) )
                                                            @php ($isToday = $startTime->isSameDay(now()))
                                                            <tr>
                                                                <td>{{ $no }}</td>
                                                                {{-- <td>
                                                                </td> --}}
                                                                <td>

                                                                    @if ($detail->transaction_detail_partner != null)
                                                                        {{ $detail->transaction_detail_partner->partner->name ?? '' }}
                                                                        <br>

                                                                        @if($isToday)
                                                                            <button
                                                                                type="button"
                                                                                class="btn btn-primary ms-2"
                                                                                onclick="showModalChangePartner({{ $detail->transaction_detail_partner->id }})">
                                                                                Change Partner
                                                                            </button>
                                                                        @endif
                                                                        @if($detail->status_id != 16)
                                                                            <button
                                                                                type="button"
                                                                                class="btn btn-danger ms-2"
                                                                                onclick="showModalVoidTreatment({{ $detail->id }})">
                                                                                Void Treatment
                                                                            </button>
                                                                        @else
                                                                            <span class="badge bg-danger">VOID</span>
                                                                        @endif

                                                                    @endif
                                                                </td>


                                                                <td>
                                                                    {{ $detail->package_id ? $detail->package->name : ($detail->coupon_id ? $detail->coupon->voucher_code : $detail->item->name) }}
                                                                    @if (!empty($detail->coupon_id))
                                                                        <br>
                                                                        @foreach ($detail->coupon->user_voucher_code as $voucherCode)
                                                                            <small>({{ $voucherCode->code }})</small>
                                                                        @endforeach
                                                                    @endif
                                                                </td>
                                                                <td>{{ $detail->qty }}</td>
                                                                <td class="text-end">{{ number_format($detail->price, 0, ',', '.') }}</td>
                                                                <td class="text-end">{{ number_format($detail->price * $detail->qty, 0, ',', '.') }}</td>
                                                                <td class="text-end">{{ number_format($detail->discount_amount, 0, ',', '.') }}</td>
                                                                <td class="text-end">{{ $detail->voucher_code ?? '-' }}</td>
                                                            </tr>
                                                            @php($no++)
                                                        @endforeach
                                                    @endforeach

                                                    <tr>
                                                        <td colspan="7" class="text-end"><b>Subtotal</b></td>
                                                        <td class="text-end"><b>{{ number_format($invoice->subtotal, 0, ',', '.') }}</b></td>
                                                    </tr>
                                                    @if($invoice->voucher_discount_value > 0)
                                                        <tr>
                                                            <td colspan="7" class="text-end"><b>Discount</b></td>
                                                            <td class="text-end"><b>-{{ number_format($invoice->voucher_discount_value, 0, ',', '.') }}</b></td>
                                                        </tr>
                                                    @endif
                                                    <tr>
                                                        <td colspan="7" class="text-end"><b>PB2 (10%)</b></td>
                                                        <td class="text-end"><b>{{ number_format($invoice->pb2, 0, ',', '.') }}</b></td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="7" class="text-end"><b>Rounding Amount</b></td>
                                                        <td class="text-end"><b>{{ number_format($invoice->rounding_amount ?? 0, 0, ',', '.') }}</b></td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="7" class="text-end"><b>Total</b></td>
                                                        <td class="text-end"><b>{{ number_format($invoice->grandtotal, 0, ',', '.') }}</b></td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label" for="return">Detil Pembayaran</label>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-12">
                                                <table class="table table-bordered table-striped table-vcenter">
                                                    <thead>
                                                    <tr>
                                                        <th>Payment Method</th>
                                                        <th>Amount</th>
                                                        <th>Return</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <tr>
                                                        <td>{{ $invoice->transaction_payment->payment_type_code }}</td>
                                                        <td>{{ number_format($invoice->transaction_payment->amount, 0, ',', '.') }}</td>
                                                        <td>{{ number_format($invoice->return_value, 0, ',', '.') }}</td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for Change Therapist -->
    <div class="modal fade" id="modal-confirm-change-therapist" tabindex="-1" role="dialog">
        <form action="{{ route('admin.transaction.change_partner') }}" method="post" id="form-change-therapist">
            @csrf
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-slideup">
                <div class="modal-content">
                    <div class="block-content">
                        <div class="row justify-content-center mb-3">
                            <div class="col-8 text-center">
                                <span>Apakah Anda yakin untuk Mengganti Terapis Transaksi ini?</span>
                                <select class="form-select" name="new_partner_id" id="new_partner_id">
                                    <option value="0">-- PILIH THERAPIS --</option>
                                    @foreach ($partners as $partner)
                                        <option value="{{ $partner->id }}">{{ $partner->name }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" id="change_partner_id" name="change_partner_id" value="0">
                                <input type="hidden" name="id" value="{{ $invoice->id }}">
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
                        <button type="button" id="btn_submit" class="btn btn-danger" onclick="confirmChangePartner()">Yes</button>
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

    <!-- Modal for confirm void -->
    <div class="modal fade" id="modal-confirm-cancel" tabindex="-1" role="dialog">
        <form action="{{ route('admin.transaction.void') }}" method="post" id="form-void">
            @csrf
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-slideup">
                <div class="modal-content">
                    <div class="block-content">
                        <div class="row">
                            <div class="col-12 text-center">
                                <span>Apakah Anda yakin untuk void Transaksi?</span>
                                <input type="hidden" name="id" value="{{ $invoice->id }}">
                            </div>
                        </div>
                        <div class="row justify-content-center">
                            <div class="col-2">
                                <label>Password</label>
                            </div>
                            <div class="col-6">
                                <input type="password" name="password_void" class="form-control" placeholder="Password"/>
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

    <!-- Modal Void Treatment -->
    <div class="modal fade" id="modal-confirm-void-treatment" tabindex="-1" role="dialog">
        <form action="{{ route('admin.transaction.void_treatment') }}" method="post" id="form-void-treatment">
            @csrf
            <input type="hidden" name="detail_id" id="void_detail_id">

            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-slideup">
                <div class="modal-content">

                    <div class="block-content">
                        <div class="row">
                            <div class="col-12 text-center">
                                <span>Apakah Anda yakin ingin void treatment ini?</span>
                            </div>
                        </div>

                        <div class="row justify-content-center mt-3">
                            <div class="col-2">
                                <label>Password</label>
                            </div>
                            <div class="col-6">
                                <input type="password" name="password_void" class="form-control" placeholder="Password"/>
                            </div>
                        </div>
                    </div>

                    <div class="block-content block-content-full text-center bg-body">
                        <button type="button" class="btn btn-warning" data-bs-dismiss="modal">No</button>

                        <button type="button" id="btn_submit_void_treatment"
                                class="btn btn-danger"
                                onclick="confirmVoidTreatment()">
                            Yes
                        </button>

                        <button type="button"
                                id="btn_loading_void_treatment"
                                class="btn btn-danger"
                                style="display:none">
                            <i class="spinner-border spinner-border-sm"></i>
                        </button>

                    </div>
                </div>
            </div>
        </form>
    </div>

    <form id="form_approve" method="post" action="{{ route('admin.transaction.approve') }}">
        @csrf
        <input type="hidden" id="approved_transaction_id" name="approved_transaction_id" value="{{ $header['id'] }}" />
    </form>

    <form id="form_fix_happy_hour" method="post" action="{{ route('admin.transaction.fix_happy_hour') }}">
        @csrf
        <input type="hidden" id="happy_hour_transaction_id" name="id" value="{{ $header['id'] }}" />
    </form>

    <form id="form_delete_happy_hour" method="post" action="{{ route('admin.transaction.delete_happy_hour') }}">
        @csrf
        <input type="hidden" id="happy_hour_trx_id" name="id" value="{{ $header['id'] }}" />
    </form>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('js/plugins/sweetalert2/sweetalert2.css') }}">
@endsection

@section('scripts')
    <!-- jQuery -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script src="{{ asset('js/plugins/sweetalert2/sweetalert2.js') }}"></script>
    <script>
        let modalConfirmVoid = new bootstrap.Modal(document.getElementById('modal-confirm-cancel'), {'backdrop': 'static', 'keyboard' :false });

        function showModalVoid(){
            modalConfirmVoid.show();
        }
        let modalConfirmChangePartner = new bootstrap.Modal(document.getElementById('modal-confirm-change-therapist'), {'backdrop': 'static', 'keyboard' :false });

        function showModalChangePartner(id){
            $("#change_partner_id").val(id);
            modalConfirmChangePartner.show();
        }

        function confirmVoid(){
            $('#btn_loading').show();
            $('#btn_submit').hide();
            $('#btn_submit_cancel').hide();
            $('#form-void').submit();
        }

        function confirmChangePartner(){
            $('#btn_loading').show();
            $('#btn_submit').hide();
            $('#btn_submit_cancel').hide();
            $('#form-change-therapist').submit();
        }

        function approveModal(){
            Swal.fire({
                icon: 'warning',
                title: 'Approve Transaksi',
                text: 'Apakah Anda yakin ingin approve transaksi ini?',
                showCancelButton: true,
                confirmButtonText: 'Ya',
                cancelButtonText: `Tidak`,
                confirmButtonColor: 'green',
                cancelButtonColor: 'orange'
            }).then((result) => {
                if(result.isConfirmed){
                    Swal.showLoading();
                    $('#form_approve').submit();
                }
            });
        }

        let modalConfirmVoidTreatment = new bootstrap.Modal(
            document.getElementById('modal-confirm-void-treatment'),
            {'backdrop': 'static', 'keyboard': false}
        );

        function showModalVoidTreatment(id){
            $('#void_detail_id').val(id);
            modalConfirmVoidTreatment.show();
        }

        function confirmVoidTreatment(){
            $('#btn_loading_void_treatment').show();
            $('#btn_submit_void_treatment').hide();
            $('#form-void-treatment').submit();
        }
    </script>
@endsection
