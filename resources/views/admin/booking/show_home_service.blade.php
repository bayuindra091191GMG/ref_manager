@extends('layouts.admin')

@section('title')
    <title>Detil Home Service</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Detil Home Service
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.home_service.index') }}">Daftar Home Service</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Detil Home Service
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->
    <div class="content">
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
                                            @if($booking->status_id !== 10)
                                                <button type="button" onclick="showModalCancel()" class="btn btn-danger">Batalkan</button>
                                                &nbsp;
                                            @endif

                                            @if($booking->status_id !== 9 && $booking->status_id !== 10 && $booking->status_id !== 3)
                                                <button type="button" onclick="showModalAddTs()" class="btn btn-primary">Tambah TS</button>
                                                &nbsp;
                                            @endif

                                            @if(count($addonDetailArr) > 0 && $isAllowSetAddonCommission)
                                                <button type="button" onclick="showModalAddonCommission()" class="btn btn-primary">Atur Komisi Addon</button>
                                            @endif

                                            @if($booking->status_id === 15)
                                                <button type="button" onclick="showModalFinish()" class="btn btn-success">Selesai</button>
                                            @endif
                                        </div>
                                    </div>

                                    @if($isTs && $booking->status_id !== 9 && $booking->status_id !== 10)
                                        <div class="row mb-3">
                                            <div class="col-sm-9 col-12">
                                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                    <span class="fw-bold">Catatan TS ditemukan! Anda harus menambahkan item TS!</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label" for="code">Status</label>
                                        <div class="col-sm-3">
                                            <input type="text" id="code" class="form-control fw-bold" readonly value="{{ strtoupper($booking->status->display) }}"/>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label" for="code">Kode Booking</label>
                                        <div class="col-sm-6">
                                            <input type="text" id="code" class="form-control" readonly value="{{ $booking->code }}"/>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-3" for="code">Nomor Transaksi</div>
                                        <div class="col-sm-6">
                                            @if(!empty($booking->transaction_id))
                                                <a href="{{ route('admin.transaction.show', ['id' => $booking->transaction_id]) }}">{{ $trxCode }}</a>
                                            @else
                                                <span class="fw-bold">Belum Ada Transaksi</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label" for="ts_note">Catatan TS</label>
                                        <div class="col-sm-4">
                                            <input type="text" id="ts_note" class="form-control" readonly value="{{ $booking->ts_note ?? '-' }}"/>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label" for="user_name">Nama Customer</label>
                                        <div class="col-sm-6">
                                            <input type="text" id="user_name" class="form-control" readonly value="{{ $booking->user_name }}"/>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label" for="user_phone">Nomor Telp Customer</label>
                                        <div class="col-sm-6">
                                            <input type="text" id="user_phone" class="form-control" readonly value="{{ $booking->user_phone }}"/>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label" for="membership_name">Membership Customer</label>
                                        <div class="col-sm-4">
                                            <input type="text" id="membership_name" class="form-control" readonly value="{{ $booking->membership_name ?? '-' }}"/>
                                        </div>
                                    </div>

                                    {{-- <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label" for="is_membership_discount">Apakah pakai Diskon Membership?</label>
                                        <div class="col-sm-6">
                                            <input type="text" id="is_membership_discount" class="form-control" readonly
                                                value="{{ $booking->is_membership_discount ? 'YA' : 'TIDAK' }}"/>
                                        </div>
                                    </div>

                                    @if($booking->is_membership_discount)
                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label" for="membership_discount">Diskon Membership Tercatat</label>
                                            <div class="col-sm-2">
                                                <div class="input-group">
                                                    <input type="text" id="membership_discount_percentage" class="form-control text-end" readonly
                                                    value="{{ $booking->membership_discount_percentage }}"/>
                                                    <span class="input-group-text">%</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endif --}}

                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <hr/>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label" for="return">Alamat Customer</label>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <div class="row">
                                                <div class="col-sm-6 col-12">
                                                    <div class="row mb-3">
                                                        <label class="col-sm-3 col-form-label" for="province_name">Provinsi</label>
                                                        <div class="col-sm-9">
                                                            <input type="text" id="province_name" class="form-control" readonly value="{{ $booking->customer_province_name }}"/>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <label class="col-sm-3 col-form-label" for="city_name">Kota/Kab</label>
                                                        <div class="col-sm-9">
                                                            <input type="text" id="city_name" class="form-control" readonly value="{{ $booking->customer_city_name }}"/>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <label class="col-sm-3 col-form-label" for="district_name">Kecamatan</label>
                                                        <div class="col-sm-9">
                                                            <input type="text" id="district_name" class="form-control" readonly value="{{ $booking->customer_district_name }}"/>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6 col-12">
                                                    <div class="row mb-3">
                                                        <label class="col-sm-3 col-form-label" for="full_address">Alamat Jalan</label>
                                                        <div class="col-sm-9">
                                                            <textarea class="form-control" readonly>{{  $booking->customer_full_address }}</textarea>
                                                        </div>
                                                    </div>
                                                    {{-- <div class="row mb-3">
                                                        <label class="col-sm-3 col-form-label" for="latitude">Latitude</label>
                                                        <div class="col-sm-9">
                                                            <input type="text" id="latitude" class="form-control" readonly value="{{ $booking->customer_latitude }}"/>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <label class="col-sm-3 col-form-label" for="longitude">Longitude</label>
                                                        <div class="col-sm-9">
                                                            <input type="text" id="longitude" class="form-control" readonly value="{{ $booking->customer_longitude }}"/>
                                                        </div>
                                                    </div> --}}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <hr/>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <h5>Detil Room dan Therapis</h5>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label" for="customer_notes">Catatan Customer</label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control" value="{{ $booking->booked_gender. ' '. $booking->booked_style }}" readonly />
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <table class="table table-bordered table-striped table-vcenter">
                                                <thead>
                                                <tr>
                                                    <td class="text-center">No</td>
                                                    <td class="text-center">Treatment/Addon</td>
                                                    <td class="text-center">Waktu Booking</td>
                                                    <td class="text-center">Qty</td>
                                                    <td class="text-center">Harga</td>
                                                    <td class="text-center">Diskon</td>
                                                    <td class="text-center">Subtotal</td>
                                                    <td class="text-center">Catatan TS</td>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @php($no = 1)
                                                @foreach($booking->booking_details as $detail)
                                                    <tr>
                                                        <td class="text-center">{{ $no }}</td>
                                                        <td class="text-center">
                                                            @if($detail->package_id != null)
                                                                {{ $detail->package->name }}
                                                            @elseif($detail->item_id != null)
                                                                {{ $detail->item->name }}
                                                            @endif
                                                        </td>
                                                        <td class="text-center">{{ !empty($detail->start_time) ? \Carbon\Carbon::parse($detail->start_time)->format('d-m-Y H:i') : '-' }}</td>
                                                        <td class="text-center">{{ $detail->qty }}</td>
                                                        <td class="text-end">{{ number_format($detail->price, 0, ',', '.') }}</td>
                                                        <td class="text-end">{{ number_format($detail->discount_amount, 0, ',', '.') }}</td>
                                                        <td class="text-end">{{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                                        <td class="text-center">{{  $detail->ts_notes ?? '-' }}</td>
                                                    </tr>
                                                    @php($no++)
                                                @endforeach
                                                @if($booking->payment_extra_charge > 0)
                                                    <tr>
                                                        <td class="text-center">{{ $no + 1 }}</td>
                                                        <td class="text-center">Home Service Fee</td>
                                                        <td class="text-center">-</td>
                                                        <td class="text-center">1</td>
                                                        <td class="text-end">{{ number_format($booking->payment_extra_charge, 0, ',', '.') }}</td>
                                                        <td class="text-end">0</td>
                                                        <td class="text-end">{{ number_format($booking->payment_extra_charge, 0, ',', '.') }}</td>
                                                        <td class="text-center">-</td>
                                                    </tr>
                                                @endif
                                                @if($booking->point_used > 0)
                                                    <tr>
                                                        <td colspan="6" class="text-end"><b>Penggunaan Point</b></td>
                                                        <td class="text-end"><b>-{{ number_format($booking->point_used, 0, ',', '.') }}</b></td>
                                                    </tr>
                                                @endif
                                                <tr>
                                                    <td colspan="6" class="text-end"><b>Total</b></td>
                                                    <td class="text-end"><b>{{ number_format($booking->grand_total, 0, ',', '.') }}</b></td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <hr/>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label" for="return">Detil Therapis</label>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label" for="customer_notes">Catatan Customer</label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control" value="{{ $booking->booked_gender. ' '. $booking->booked_style }}" readonly />
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <table class="table table-bordered table-striped table-vcenter">
                                                <thead>
                                                <tr>
                                                    <td class="text-center">No</td>
                                                    <td class="text-center">Treatment/Addon</td>
                                                    <td class="text-center">Status</td>
                                                    <td class="text-center">Waktu Mulai</td>
                                                    <td class="text-center">Therapis</td>
                                                    <td class="text-center">Tindakan</td>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @if(count($packageDetails) > 0)
                                                    @php($no = 1)
                                                    @foreach($packageDetails as $detail)
                                                        <tr>
                                                            <td class="text-center">{{ $no }}</td>
                                                            <td class="text-center">
                                                                {{ $detail->package_name }}
                                                            </td>
                                                            <td class="text-center" id="status_{{ $detail->id }}">
                                                                @if($detail->status_display === 'Pending')
                                                                    <span style="color: orange;" class="fw-bold">{{ strtoupper($detail->status_display) }}</span>
                                                                @elseif($detail->status_display === 'Aktif')
                                                                    <span style="color: green;" class="fw-bold">{{ strtoupper($detail->status_display) }}</span>
                                                                @else
                                                                    {{ strtoupper($detail->status_display) }}
                                                                @endif
                                                            </td>
                                                            <td class="text-center">{{ !empty($detail->start_time) ? \Carbon\Carbon::parse($detail->start_time)->format('d-m-Y H:i') : '-' }}</td>
                                                            <td class="text-center" id="partner_{{ $detail->id }}">
                                                                {{ !empty($detail->partner_id) ? $detail->partner_alias. '('. $detail->partner_number. ')' : '-' }}
                                                            </td>
                                                            <td class="text-center">
                                                                @if($booking->status_id === 17 && empty($detail->partner_id))
                                                                <button type="button"
                                                                    id="btn_modal_assign_{{ $detail->id }}"
                                                                    onclick="showModalAssign({{ $detail->id }})"
                                                                    class="btn btn-success">
                                                                    Assign Therapis
                                                                </button>
                                                                @endif

                                                            </td class="text-center">
                                                        </tr>
                                                        @php($no++)
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td colspan="6" class="text-center"><span class="fw-bold">Tidak Ada</span></td>
                                                    </tr>
                                                @endif

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

    <!-- Modal to assign partners -->
    <div class="modal fade" id="modal_assign" tabindex="-1" role="dialog" aria-labelledby="modal_assign" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-slideup">
            <div class="modal-content">
                <div class="block-content mb-3">
                    <div class="row mb-2">
                        <div class="col-12 text-center">
                            <span>Pilih Therapis</span>
                            <input type="hidden" id="booking_detail_id" name="booking_detail_id">
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-12">
                            <label class="form-label" for="assign_partner">Therapis</label>
                            <select class="form-select" id="assign_partner" name="assign_partner"></select>
                        </div>
                    </div>
                    <div class="row justify-content-center">
                        <div class="col-sm-12">
                            <label class="form-label" for="assign_time">Tanggal dan Jam Mulai Treatment</label>
                            <input type="text" id="assign_time" name="assign_time" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="block-content block-content-full text-center bg-body">
                    <button type="button" class="btn btn-warning" id="btn_submit_cancel" data-bs-dismiss="modal">Batal</button>
                    <button type="button" id="btn_submit" class="btn btn-success" onclick="confirmAssign()">Pilih</button>
                    <button type="button" id="btn_loading" class="btn btn-danger" style="display: none;">
                        <i class="spinner-border spinner-border-sm text-green" role="status">
                            <span class="visually-hidden">Mohon tunggu...</span>
                        </i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal to add TS -->
    <div class="modal fade" id="modal_add_ts" tabindex="-1" role="dialog" aria-labelledby="modal_assign" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-slideup">
            <div class="modal-content">
                <form method="post" action="{{ route('admin.booking.ts.add', ['id' => $booking->id]) }}">
                    {{ csrf_field() }}
                    <div class="block-content mb-3">
                        <div class="row mb-2">
                            <div class="col-sm-12">
                                <label class="form-label" for="ts_item">Pilih Item TS</label>
                                <select class="form-select" id="ts_item" name="ts_item">
                                    @foreach ($tsItems as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="block-content block-content-full text-center bg-body">
                        <button type="button" class="btn btn-warning" id="btn_submit_cancel" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" id="btn_submit" class="btn btn-success">Tambah</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal to add partner to addons for commission -->
    <div class="modal fade" id="modal_update_addon_commission" tabindex="-1" role="dialog" aria-labelledby="modal_assign" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-slideup">
            <div class="modal-content">
                <form method="post" action="{{ route('admin.booking.addon.partner.update', ['id' => $booking->id]) }}">
                    {{ csrf_field() }}
                    <div class="block-content mb-3">
                        <div class="row mb-2">
                            <div class="col-sm-12">
                                <table class="table">
                                    @php($addonIdx = 0)
                                    @foreach ($addonDetailArr as $addonArr)
                                    <tr>
                                        <td style="width: 40%;">
                                            {{ $addonArr['item_name'] }}
                                            <input type="hidden" name="addons[{{ $addonIdx }}][item_id]" value="{{ $addonArr['item_id'] }}" />
                                            <input type="hidden" name="addons[{{ $addonIdx }}][trx_detail_id]" value="{{ $addonArr['trx_detail_id'] }}" />
                                        </td>
                                        <td>
                                            <select class="form-select" name="addons[{{ $addonIdx }}][partner_id]">
                                                <option value="0" @if($addonArr['partner_id'] == 0) selected @endif> -- Pilih Therapis -- </option>
                                                @foreach ($addonPartnerOptions as $partnerOption)
                                                    <option value="{{ $partnerOption['partner_id'] }}"
                                                        @if($partnerOption['partner_id'] == $addonArr['partner_id']) selected @endif>
                                                            {{ $partnerOption['partner_alias'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                    @php( $addonIdx++ )
                                    @endforeach
                                </table>

                            </div>
                        </div>
                    </div>
                    <div class="block-content block-content-full text-center bg-body">
                        <button type="button" class="btn btn-warning" id="btn_submit_cancel" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" id="btn_submit" class="btn btn-success">Tambah</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <form id="form_cancel_booking" method="post" action="{{ route('admin.home_service.cancel') }}">
        @csrf
        <input type="hidden" id="cancelled_booking_id" name="cancelled_booking_id" value="{{ $booking->id }}">
    </form>

    <form id="form_finish_home_service" method="post" action="{{ route('admin.home_service.finish') }}">
        @csrf
        <input type="hidden" id="finished_booking_id" name="finished_booking_id" value="{{ $booking->id }}">
    </form>
@endsection

@section('styles')
    <!-- Page JS Plugins CSS -->
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/sweetalert2/sweetalert2.css') }}">
@endsection

@section('scripts')
    <!-- jQuery -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('js/plugins/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('js/plugins/flatpickr/l10n/id.js') }}"></script>
    <script src="{{ asset('js/plugins/sweetalert2/sweetalert2.js') }}"></script>
    <script src="{{ asset('js/plugins/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
    <script>
        let modalAssign = new bootstrap.Modal(document.getElementById('modal_assign'));
        let modalAddTs = new bootstrap.Modal(document.getElementById('modal_add_ts'));
        let modalAddonCommission = new bootstrap.Modal(document.getElementById('modal_update_addon_commission'));

        function showModalAssign(detailId){
            modalAssign.show();
            $('#booking_detail_id').val(detailId);
        }

        function showModalAddTs(detailId){
            modalAddTs.show();
        }

        function showModalAddonCommission(){
            modalAddonCommission.show();
        }

        function showModalCancel(){
            Swal.fire({
                icon: 'warning',
                title: 'Batalkan Home Service',
                text: 'Apakah Anda yakin ingin membatalkan Home Service ini?',
                showCancelButton: true,
                confirmButtonText: 'Ya',
                cancelButtonText: `Tidak`,
                confirmButtonColor: 'red',
                cancelButtonColor: 'orange'
            }).then((result) => {
                if(result.isConfirmed){
                    Swal.showLoading();
                    $('#form_cancel_booking').submit();
                }
            });
        }

        function showModalFinish(){
            Swal.fire({
                icon: 'warning',
                title: 'Selesaikan Home Service',
                text: 'Apakah Anda yakin ingin menyelesaikan Home Service ini?',
                showCancelButton: true,
                confirmButtonText: 'Ya',
                cancelButtonText: `Tidak`,
                confirmButtonColor: 'green',
                cancelButtonColor: 'orange'
            }).then((result) => {
                if(result.isConfirmed){
                    Swal.showLoading();
                    $('#form_finish_home_service').submit();
                }
            });
        }

        function confirmAssign(){
            let bookingDetailId = $('#booking_detail_id').val();
            let assignedPartnerId = $('#assign_partner').val();
            let assignedTime = $('#assign_time').val();

            if(!bookingDetailId || !assignedPartnerId || !assignedTime){
                return false;
            }

            $('#btn_submit_cancel').hide();
            $('#btn_submit').hide();
            $('#btn_loading').show(200);

            $.ajax({
                type:'POST',
                url:'{{ route('admin.home_service.assign') }}',
                data:{
                    '_token': '{{ csrf_token() }}',
                    'booking_detail_id': bookingDetailId,
                    'assign_partner': assignedPartnerId,
                    'assign_time': assignedTime,
                },
                success:function(data){
                    let error = data['error'];
                    if(error === 'none'){
                        let response = data['response'];
                        $('#partner_' + bookingDetailId).html(response['partner_data']);
                        $('#status_' + bookingDetailId).html('<span style="color: green;" class="fw-bold">AKTIF</span>');

                        One.helpers('jq-notify', {type: 'success', icon: 'fa fa-info-circle me-1', timer: 300, message: 'Berhasil assign Therapis!'});
                    }
                    else{
                        One.helpers('jq-notify', {type: 'danger', icon: 'fa fa-info-circle me-1', timer: 300, message: 'Terjadi kesalahan input!'});
                    }

                    $('#btn_submit_cancel').show();
                    $('#btn_submit').show();
                    $('#btn_loading').hide();

                    modalAssign.hide();
                },
                error:function(data){
                    One.helpers('jq-notify', {type: 'danger', icon: 'fa fa-info-circle me-1', timer: 300, message: 'Terjadi kesalahan pada internal sistem!'});

                    $('#btn_submit_cancel').show();
                    $('#btn_submit').show();
                    $('#btn_loading').hide();
                }
            });
        }

        $('#assign_time').flatpickr({
            'enableTime': true,
            'dateFormat': 'd-m-Y H:i',
            'time_24hr': true,
            'locale': 'id'
        });

        $('#assign_partner').select2({
            placeholder: {
                id: '-1',
                text: ' --- Pilih Therapis --- '
            },
            width: '100%',
            dropdownParent: $('#modal_assign'),
            minimumInputLength: 0,
            ajax: {
                url: '{{ route('admin.partner.oura.select') }}',
                dataType: 'json',
                data: function (params) {
                    return {
                        search: $.trim(params.term),
                        branch_id: {{ \Illuminate\Support\Facades\Auth::guard('admin')->user()->branch_id }}
                    };
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                }
            }
        });
    </script>
@endsection
