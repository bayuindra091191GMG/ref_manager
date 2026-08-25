@extends('layouts.admin')

@section('title')
    <title>Detil Komisi Manual</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Detil Komisi Manual
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.commission_manual.index') }}">Daftar Komisi Manual</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Detil Komisi Manual
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <div class="content">
        <form class="space-y-3">
            @csrf
            <div class="row">
                <div class="col-sm-4 col-12">
                    <a href="{{ route('admin.commission_manual.index') }}" class="btn btn-dark">
                        <i class="fa fa-arrow-left"></i>
                    </a>
                </div>
                <div class="col-sm-8 col-12 text-end">
{{--                    <a href="{{ route('admin.commission_manual.edit', ['id' => $itemCommissionHeader->id]) }}" class="btn btn-primary">Ubah</a>--}}
                </div>
            </div>
            <div class="block block-rounded">
                <div class="block-content block-content-full">
                    <div class="row">
                        <div class="col-sm-6">
                            @include('partials._success')
                            @include('partials._error')
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="space-y-3">
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="date">Tanggal</label>
                                    <div class="col-sm-3">
                                        <input type="text"
                                               id="date"
                                               name="date"
                                               class="form-control" value="{{ \Carbon\Carbon::parse($itemCommissionHeader->date)->format('d-m-Y') }}" readonly />
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <span style="color: red;">Pilih salah satu!</span>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="employee">Karyawan</label>
                                    <div class="col-sm-3">
                                        <input type="text" id="employee" class="form-control" value="{{ $itemCommissionHeader->employee_name ?? '-' }}" readonly />
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="partner">Therapis</label>
                                    <div class="col-sm-3">
                                        @if(!empty($itemCommissionHeader->partner_id))
                                            <input type="text" id="partner" class="form-control" value="{{ $itemCommissionHeader->partner_number. ' - '. $itemCommissionHeader->partner_alias }}" readonly />
                                        @else
                                            <input type="text" id="employee" class="form-control" value="-" readonly />
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="block block-rounded">
                <div class="block-content block-content-full">
                    <div class="row">
                        <div class="col-12">
                            <table class="table table-bordered nowrap">
                                <thead>
                                <tr>
                                    <th class="text-center">No</th>
                                    <th class="text-center">Barang</th>
                                    <th class="text-center">Qty Terjual</th>
                                    <th class="text-center">Total Komisi (Rp)</th>
                                </tr>
                                </thead>
                                <tbody>
                                @php( $idx = 1 )
                                @foreach($itemCommissionDetails as $itemCommissionDetail)
                                    <tr>
                                        <td class="text-center">
                                            {{ $idx }}
                                        </td>
                                        <td>
                                            {{ $itemCommissionDetail->item_name }}
                                        </td>
                                        <td class="text-end">
                                            {{ $itemCommissionDetail->qty }}
                                        </td>
                                        <td class="text-end">
                                            {{ number_format($itemCommissionDetail->commission_amount, 2, ',', '.') }}

                                        </td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td class="text-end" colspan="3">Total: </td>
                                    <td class="text-end">{{ number_format($itemCommissionDetail->total_commission_amount, 2, ',', '.') }}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>
@endsection

@section('styles')
    <!-- Page JS Plugins CSS -->
@endsection

@section('scripts')
    <!-- jQuery -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script src="{{ asset('js/plugins/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
@endsection
