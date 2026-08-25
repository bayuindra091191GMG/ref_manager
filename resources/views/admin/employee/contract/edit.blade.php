@extends('layouts.admin')

@section('title')
    <title>Ubah Kontrak</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Ubah Kontrak
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.employee.index') }}">Daftar Karyawan</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.employee.show', ['id' => $contract->employee_id]) }}">Detail Karyawan</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Ubah Kontrak
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <div class="content">
        <form class="space-y-3" id="form_create_contract'" method="POST" action="{{ route('admin.employee.contract.update') }}" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-12 text-end">
                    <button id="btn_loading" type="button" class="btn btn-success" style="display: none;">
                        <i class="spinner-border spinner-border-sm text-green" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </i>
                    </button>
                    <input type="submit" id="btn_submit" class="btn btn-success" value="Simpan"/>&nbsp;&nbsp;
                    <a id="btn_cancel" href="{{ route('admin.employee.show', ['id' => $contract->employee_id]) }}" class="btn btn-danger">Batal</a>
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
                    <div class="row">
                        <div class="col-12">
                            <div class="space-y-3">
                                <div class="row">
                                    <div class="col-12">
                                        <span style="color: red;">Isian dengan asterik (*) wajib diisi!</span>
                                    </div>
                                </div>

                                <input type="hidden" name="id" value="{{ $contract->id }}">

                                <div class="row">
                                    <label class="col-sm-3 col-form-label">Nomor Kontrak</label>
                                    <div class="col-sm-3">
                                        <input id="code" type="text" class="form-control" style="text-transform: uppercase;"
                                               name="code" value="{{ old('code') ?? $contract->code }}" required />
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="gender">Jangka Waktu</label>
                                    <div class="col-sm-2">
                                        <div class="input-group">
                                            <input id="duration" type="text" class="form-control numeric"
                                                   name="duration" value="{{ old('duration') ?? $contract->duration }}" required/>
                                            <span class="input-group-text">Bulan</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="join_date">Tanggal Mulai <span class="text-red">*</span></label>
                                    <div class="col-sm-3">
                                        <input id="start_date" type="text" class="form-control js-flatpickr" data-date-format="d-m-Y"
                                               name="start_date" value="{{ old('start_date') ?? $contract->start_date->format('d-m-Y') }}" required/>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="join_date">Tanggal Berakhir <span class="text-red">*</span></label>
                                    <div class="col-sm-3">
                                        <input id="end_date" type="text" class="form-control js-flatpickr" data-date-format="d-m-Y"
                                               name="end_date" value="{{ old('end_date') ?? $contract->end_date->format('d-m-Y') }}" required/>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="name">Nama</label>
                                    <div class="col-sm-4">
                                        <input id="name" type="text" class="form-control"
                                               name="name" value="{{ $contract->name }}" readonly />
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="marital_status">Status</label>
                                    <div class="col-sm-3">
                                        <select id="marital_status" type="text" class="form-select"
                                                name="marital_status" required>
                                            <option value="KAWIN" @if($contract->marital_status == "KAWIN") selected @endif>KAWIN</option>
                                            <option value="BELUM KAWIN" @if($contract->marital_status == "BELUM KAWIN") selected @endif>BELUM KAWIN</option>
                                            <option value="CERAI" @if($contract->marital_status == "CERAI") selected @endif>CERAI</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="gender">Jenis Kelamin</label>
                                    <div class="col-sm-3">
                                        {{-- <input type="text" class="form-control" value="{{ $contract->gender }}" readonly> --}}
                                        <select id="gender" type="text" class="form-select"
                                                name="gender" required>
                                            <option value="LAKI-LAKI" @if($contract->gender == "LAKI-LAKI") selected @endif>LAKI-LAKI</option>
                                            <option value="PEREMPUAN" @if($contract->gender == "PEREMPUAN") selected @endif>PEREMPUAN</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="gender">Pendidikan & Jurusan</label>
                                    <div class="col-sm-4">
                                        <input id="education" type="text" class="form-control"
                                               name="education" value="{{ $contract->education }}" />
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="gender">Alamat</label>
                                    <div class="col-sm-4">
                                        <textarea class="form-control" rows="5" id="address" name="address">{{ $contract->address }}</textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="gender">No Telp. / HP.</label>
                                    <div class="col-sm-4">
                                        <input id="phone_number" type="text" class="form-control"
                                               name="phone_number" value="{{ $contract->phone_number }}" required/>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="gender">Gaji</label>
                                    <div class="col-sm-4">
                                        <input id="salary" type="text" class="form-control numeric"
                                               name="salary" value="{{ old('salary') ?? $contract->salary }}" required />
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="gender">Komisi</label>
                                    <div class="col-sm-4">
                                        <input id="commission" type="text" class="form-control numeric-decimal"
                                               name="commission" value="{{ old('commission') ?? $contract->commission }}" required />
                                    </div>
                                </div>

                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="gender">KTP</label>
                                    <div class="col-sm-8 col-12">
                                        <a href="{{ \Illuminate\Support\Facades\Storage::disk('partner_ktp')->url($contract->ktp_image_path) }}" target="_blank">
                                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('partner_ktp')->url($contract->ktp_image_path) }}" style="max-height: 250px;">
                                        </a>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-12 col-form-label" for="pkwt">Dokumen PKWT</label>
                                    <div class="col-sm-8 col-12">
                                        <input type="file" name="pkwt" id="pkwt" accept=".pdf" required/>
                                    </div>
                                </div>

                            </div>
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
    <link rel="stylesheet" href="{{ asset('js/plugins/bootstrap-fileinput/css/fileinput.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.min.css" crossorigin="anonymous">
@endsection

@section('scripts')
    <!-- jQuery -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('js/plugins/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
    <script src="{{ asset('js/plugins/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('js/plugins/flatpickr/l10n/id.js') }}"></script>
    <script src="{{ asset('js/AutoNumeric.js') }}"></script>
    <script src="{{ asset('js/plugins/bootstrap-fileinput/js/fileinput.js') }}"></script>

    <script>
        $('#form_create_contract').submit(function(){
            $('#btn_submit').hide();
            $('#btn_cancel').hide();
            $('#btn_loading').show(200);
        });

        flatpickr('.js-flatpickr', {
            "locale": "id"  // locale for this instance only
        });

        new AutoNumeric.multiple('.numeric', {
            digitGroupSeparator: '.',
            decimalCharacter: ',',
            decimalPlaces: 1,
            modifyValueOnWheel: false,
            allowDecimalPadding: false,
            emptyInputBehavior: 'zero',
            unformatOnSubmit: true,
        });

        new AutoNumeric.multiple('.numeric-decimal', {
            digitGroupSeparator: '.',
            decimalCharacter: ',',
            decimalPlaces: 1,
            modifyValueOnWheel: false,
            allowDecimalPadding: false,
            emptyInputBehavior: 'zero',
            unformatOnSubmit: true,
        });

        $('#pkwt').fileinput({
            'showUpload': false,
            'overwriteInitial': false,
        });
    </script>
@endsection
