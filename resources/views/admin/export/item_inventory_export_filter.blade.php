@extends('layouts.admin')

@section('title')
    <title>Ekspor Data Stok Barang</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Ekspor Data Stok Barang
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Ekspor Data Stok Barang
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <div class="content">
        <form class="space-y-3" id="form_export_filter" method="POST" action="{{ route('admin.export.item_inventory.submit') }}">
            @csrf
            <div class="block block-rounded">
                <div class="block-content block-content-full">
                    <div class="row">
                        <div class="col-sm-6">
                            @include('partials._success')
                            @include('partials._error')
                        </div>
                    </div>
                    @if (count($errors))
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="space-y-3">
                                <div class="row">
                                    <div class="col-12">
                                        <span style="color: red;">Isian dengan asterik (*) wajib diisi!</span>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="period_month">Periode Bulan <span
                                            class="text-red">*</span></label>
                                    <div class="col-sm-4 col-12">
                                        <input id="start_date" type="text" class="js-flatpickr form-control"
                                            value="{{ $periodMonth }}" name="period_month" required/>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="category">Kategori <span
                                            class="text-red">*</span></label>
                                    <div class="col-sm-4 col-12">
                                        <select class="form-control" name="category">
                                            <option value="PERSEDIAAN">PERSEDIAAN</option>
                                            <option value="PERLENGKAPAN">PERLENGKAPAN</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 text-start">
                            <span style="color: red;">Setelah klik Ekspor Excel, mohon perhatikan loading pada tab browser diatas
                            </br/>Tunggu sampai keluar jendela unduh file Excel, jangan diklik lagi!
                            </span>
                            </br/>
                            <input type="submit" id="btn_submit" class="btn btn-success" value="Ekspor Excel"/>&nbsp;&nbsp;
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
    <link rel="stylesheet" href="{{ asset('js/plugins/flatpickr/flatpickr.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/flatpickr/plugins/monthSelect/style.css') }}">
@endsection

@section('scripts')
    <!-- jQuery -->
    <script src="{{ asset('js/plugins/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('js/plugins/flatpickr/plugins/monthSelect/index.js') }}"></script>
    <script src="{{ asset('js/plugins/flatpickr/l10n/id.js') }}"></script>
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>

    <script>
        flatpickr('.js-flatpickr', {
            "locale": "id", // locale for this instance only
            "plugins": [
                new monthSelectPlugin({
                    dateFormat: "m-Y", //defaults to "F Y"
                })
            ]
        });
    </script>
@endsection
