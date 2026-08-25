@extends('layouts.admin')

@section('title')
    <title>Detil Karyawan</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Detil Gaji Karyawan {{ $salary->employee_code }}
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.salary.employee.index') }}">Daftar Gaji Karyawan</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Detil Gaji Karyawan
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
            <div class="row mb-3">
                <div class="col-sm-4 col-12">
                    <a href="{{ route('admin.salary.employee.index') }}" class="btn btn-dark">
                        <i class="fa fa-arrow-left"></i>
                    </a>
                </div>
                <div class="col-sm-8 col-12 text-end">

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
                                    <label class="col-sm-3 col-form-label" for="name">Kode Karyawan</label>
                                    <div class="col-sm-3">
                                        <input id="code" type="text" class="form-control"
                                               name="code" value="{{ $salary->employee_code }}" readonly/>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="name">Nama</label>
                                    <div class="col-sm-3">
                                        <input id="name" type="text" class="form-control"
                                               name="name" value="{{ $salary->employee_name }}" readonly/>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="department">Departemen</label>
                                    <div class="col-sm-3">
                                        <input id="department" type="text" class="form-control"
                                               name="department" value="{{ $salary->employee_department ?? '-' }}" readonly/>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="basic_salary_amount">Gaji Pokok</label>
                                    <div class="col-sm-3">
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input id="basic_salary_amount" type="text" class="form-control fw-bold text-end"
                                                   name="basic_salary_amount" value="{{ number_format($salary->basic_salary_amount, 0, ',', '.') }}" readonly />
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="commission_amount">Total Komisi</label>
                                    <div class="col-sm-3">
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input id="commission_amount" type="text" class="form-control fw-bold text-end"
                                                   name="commission_amount" value="{{ number_format($salary->commission_amount, 0, ',', '.') }}" readonly />
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="penalty_amount">Total Pinalti</label>
                                    <div class="col-sm-3">
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input id="penalty_amount" type="text" class="form-control fw-bold text-end"
                                                   name="penalty_amount" value="{{ number_format(($salary->penalty_amount * -1), 0, ',', '.') }}" readonly />
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="other_amount">Lain-lain</label>
                                    <div class="col-sm-3">
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input id="other_amount" type="text" class="form-control fw-bold text-end"
                                                   name="other_amount" value="{{ number_format($salary->other_amount, 0, ',', '.') }}" readonly />
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="total_salary_amount">Total Gaji Akhir</label>
                                    <div class="col-sm-3">
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input id="total_salary_amount" type="text" class="form-control fw-bold text-end"
                                                   name="total_salary_amount" value="{{ number_format($salary->total_salary_amount, 0, ',', '.') }}" readonly />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="block block-rounded">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Detil Komisi</h3>
                </div>
                <div class="block-content block-content-full">
                    <div class="row">
                        <div class="col-sm-6 col-12">
                            <table class="table table-bordered table-striped table-vcenter nowrap" id="table_commission_details">
                                <thead>
                                <tr>
                                    <th class="text-center">No</th>
                                    <th class="text-center">Jenis Komisi</th>
                                    <th class="text-center">Jumlah Komisi (Rp)</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(!empty($commissionDetails) && $commissionDetails->count() > 0)
                                    @php( $no = 1 )
                                    @foreach($commissionDetails as $detail)
                                        <tr>
                                            <td class="text-center">{{ $no }}</td>
                                            <td>{{ $detail->commission_display }}</td>
                                            <td class="text-end">{{ number_format($detail->commission_amount, 0, ',', '.') }}</td>
                                        </tr>
                                        @php( $no++ )
                                    @endforeach
                                    <tr>
                                        <td class="text-end fw-bold" colspan="2">Total:</td>
                                        <td class="text-end fw-bold">{{ number_format($salary->commission_amount, 0, ',', '.') }}</td>
                                    </tr>
                                @else
                                    <tr>
                                        <td class="text-center fw-bold" colspan="3">Tidak Ada Komisi</td>
                                    </tr>
                                @endif

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="block block-rounded">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Detil Pinalti</h3>
                </div>
                <div class="block-content block-content-full">
                    <div class="row">
                        <div class="col-sm-6 col-12">
                            <table class="table table-bordered table-striped table-vcenter nowrap" id="table_commission_details">
                                <thead>
                                <tr>
                                    <th class="text-center">No</th>
                                    <th class="text-center">Jenis Pinalti</th>
                                    <th class="text-center">Jumlah Pinalti (Rp)</th>
                                </tr>
                                </thead>
                                <tbody>
{{--                                @if(!empty($commissionDetails) && $commissionDetails->count() > 0)--}}
{{--                                    @php( $no = 1 )--}}
{{--                                    @foreach($commissionDetails as $detail)--}}
{{--                                        <tr>--}}
{{--                                            <td class="text-center">{{ $no }}</td>--}}
{{--                                            <td>{{ $detail->commission_display }}</td>--}}
{{--                                            <td class="text-end">{{ number_format($detail->commission_amount, 2, ',', '.') }}</td>--}}
{{--                                        </tr>--}}
{{--                                        @php( $no++ )--}}
{{--                                    @endforeach--}}
{{--                                    <tr>--}}
{{--                                        <td class="text-end fw-bold" colspan="2">Total:</td>--}}
{{--                                        <td class="text-end fw-bold">{{ number_format($salary->commission_amount, 2, ',', '.') }}</td>--}}
{{--                                    </tr>--}}
{{--                                @else--}}
{{--                                    <tr>--}}
{{--                                        <td class="text-center fw-bold" colspan="3">Tidak Ada Komisi</td>--}}
{{--                                    </tr>--}}
{{--                                @endif--}}
                                <tr>
                                    <td class="text-center fw-bold" colspan="3">Tidak Ada Pinalti</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
            <div class="block block-rounded">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Lain-lain</h3>
                </div>
                <div class="block-content block-content-full">
                    <div class="row">
                        <div class="col-sm-6 col-12">
                            <table class="table table-bordered table-striped table-vcenter nowrap" id="table_commission_details">
                                <thead>
                                <tr>
                                    <th class="text-center">No</th>
                                    <th class="text-center">Keterangan</th>
                                    <th class="text-center">Jumlah (Rp)</th>
                                </tr>
                                </thead>
                                <tbody>
                                {{--                                        @if(!empty($commissionDetails) && $commissionDetails->count() > 0)--}}
                                {{--                                            @php( $no = 1 )--}}
                                {{--                                            @foreach($commissionDetails as $detail)--}}
                                {{--                                                <tr>--}}
                                {{--                                                    <td class="text-center">{{ $no }}</td>--}}
                                {{--                                                    <td>{{ $detail->commission_display }}</td>--}}
                                {{--                                                    <td class="text-end">{{ number_format($detail->commission_amount, 2, ',', '.') }}</td>--}}
                                {{--                                                </tr>--}}
                                {{--                                                @php( $no++ )--}}
                                {{--                                            @endforeach--}}
                                {{--                                            <tr>--}}
                                {{--                                                <td class="text-end fw-bold" colspan="2">Total:</td>--}}
                                {{--                                                <td class="text-end fw-bold">{{ number_format($salary->commission_amount, 2, ',', '.') }}</td>--}}
                                {{--                                            </tr>--}}
                                {{--                                        @else--}}
                                {{--                                            <tr>--}}
                                {{--                                                <td class="text-center fw-bold" colspan="3">Tidak Ada Komisi</td>--}}
                                {{--                                            </tr>--}}
                                {{--                                        @endif--}}
                                <tr>
                                    <td class="text-center fw-bold" colspan="3">Tidak Ada Lain-lain</td>
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
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/sweetalert2/sweetalert2.css') }}">
@endsection

@section('scripts')
    <!-- jQuery -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('js/plugins/sweetalert2/sweetalert2.js') }}"></script>

    <script>

    </script>
@endsection
