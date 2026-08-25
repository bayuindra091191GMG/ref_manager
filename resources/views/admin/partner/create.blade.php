@extends('layouts.admin')

@section('title')
    <title>Tambah Terapis</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Tambah Terapis
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.partner.index') }}">Daftar Terapis</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Tambah Terapis
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <div class="content">
        <form class="space-y-3" id="form_create_partner'" method="POST" action="{{ route('admin.partner.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-12 text-end">
                    <button id="btn_loading" type="button" class="btn btn-success" style="display: none;">
                        <i class="spinner-border spinner-border-sm text-green" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </i>
                    </button>
                    <input type="submit" id="btn_submit" class="btn btn-success" value="Simpan"/>&nbsp;&nbsp;
                    <a id="btn_cancel" href="{{ route('admin.partner.index') }}" class="btn btn-danger">Batal</a>
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
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="code">Kode Karyawan <span class="text-red">*</span></label>
                                    <div class="col-sm-4">
                                        <input id="code" type="text" class="form-control" style="text-transform: uppercase;" placeholder="Kode Karyawan"
                                               name="code" value="{{ old('code') }}" required/>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="number">No. KTP </label>
                                    <div class="col-sm-4">
                                        <input id="no_ktp" type="text" class="form-control" placeholder="No. KTP"
                                               name="no_ktp" value="{{ old('no_ktp') }}"/>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="name">Nama <span class="text-red">*</span></label>
                                    <div class="col-sm-4">
                                        <input id="name" type="text" class="form-control" placeholder="Nama"
                                               name="name" value="{{ old('name') }}" required/>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="alias">Alias <span class="text-red">*</span></label>
                                    <div class="col-sm-4">
                                        <input id="alias" type="text" class="form-control" placeholder="Alias"
                                               name="alias" value="{{ old('alias') }}" required/>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="level">Jenis Kelamin</label>
                                    <div class="col-sm-4">
                                        <select id="level" name="level" class="form-select">
                                            <option value="P" selected>Perempuan</option>
                                            <option value="L" >Laki - laki</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="phone">Nomor Kontak</label>
                                    <div class="col-sm-4">
                                        <input id="phone" type="text" class="form-control" placeholder="Nomor Kontak"
                                               name="phone" value="{{ old('phone') }}" />
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="phone">Alamat</label>
                                    <div class="col-sm-4">
                                        <textarea name="address" id="address" rows="10" class="form-control" placeholder="Alamat Domisili">{{ old('address') }}</textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="alias">Spesialis skill
                                        <span class="text-red">*</span></label>
                                    <div class="col-sm-4">
                                        <select name="specialty[]" id="specialty" class="form-select select2" multiple="multiple">
                                            <option value="SALON">SALON</option>
                                            <option value="FIT">FIT</option>
                                            <option value="FACIAL">FACIAL</option>
                                            <option value="FACIAL TRADISIONAL">FACIAL TRADISIONAL</option>
                                            <option value="MANICURE PEDICURE">MANICURE PEDICURE</option>
                                            <option value="SPA">SPA</option>
                                            <option value="MASSAGE">MASSAGE</option>
                                            <option value="REFLEKSI">REFLEKSI</option>
                                            <option value="REFLEKSI (KECUALI MIXTHAI)">REFLEKSI (KECUALI MIXTHAI)</option>
                                            <option value="REFLEKSI (KECUALI BEKAM DAN MIXTHAI)">REFLEKSI (KECUALI BEKAM DAN MIXTHAI)</option>
                                            <option value="REFLEKSI (KECUALI BEKAM KOMBINASI DAN MIXTHAI)">REFLEKSI (KECUALI BEKAM KOMBINASI DAN MIXTHAI)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="level">Jenis Terapis</label>
                                    <div class="col-sm-4">
                                        <select id="level" name="level" class="form-select">
                                            <option value="0" selected>----- Pilih Jenis Terapis Therapis -----</option>
                                            @foreach($partnerLevels as $partnerLevel)
                                                <option value="{{ $partnerLevel->id }}">{{ $partnerLevel->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="join_date">Tanggal Bergabung <span class="text-red">*</span></label>
                                    <div class="col-sm-4">
                                        <input id="join_date" type="text" class="form-control js-flatpickr" data-date-format="d-m-Y"
                                               name="join_date" value="{{ $currentDate }}" />
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="is_present">Apakah Hadir saat ini?</label>
                                    <div class="col-sm-4">
                                        <select id="is_present" name="is_present" class="form-select">
                                            <option value="0" selected>Tidak Hadir</option>
                                            <option value="1">Hadir</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="employement_status">Status Kepegawaian?</label>
                                    <div class="col-sm-4">
                                        <select id="employement_status" name="employement_status" class="form-select">
                                            <option value="Aktif" selected>Aktif</option>
                                            <option value="Cuti">Cuti</option>
                                            <option value="Resign">Resign</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-sm-3 col-12 col-form-label" for="price">Uang Kehadiran</label>
                                    <div class="col-sm-4 col-12">
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input id="basic_salary_amount" type="text" class="form-control"
                                                    name="basic_salary_amount" />
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="attend_machine_id">Nomor Urut Mesin Absen</label>
                                    <div class="col-sm-4">
                                        <input id="attend_machine_id" type="text" class="form-control"
                                               name="attend_machine_id" value="{{ old('attend_machine_id') }}"/>
                                    </div>
                                </div>

                                @if($branchId == 1)
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="commission_percentage">Persentase Komisi Manual</label>
                                        <div class="col-sm-4">
                                            <div class="input-group">
                                                <input id="commission_percentage" type="text" class="form-control"
                                                       name="commission_percentage" />
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="row">
                                    <label class="col-sm-3 col-12 col-form-label" for="image">Upload Foto</label>
                                    <div class="col-sm-8 col-12">
                                        <input type="file" name="image" id="image" accept=".png,.jpg,.jpeg,.webp"/>
                                    </div>
                                </div>

                                <hr>

                                <h4 class="mt-4">Informasi Rekening</h4>

                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="phone">Nomor Rekening</label>
                                    <div class="col-sm-4">
                                        <input id="account_number" type="text" class="form-control" placeholder="Nomor Rekening"
                                               name="account_number" value="{{ old('account_number') }}" />
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="phone">Nama Bank</label>
                                    <div class="col-sm-4">
                                        <input id="account_bank_name" type="text" class="form-control" placeholder="Nama Bank"
                                               name="account_bank_name" value="{{ old('account_bank_name') }}" />
                                    </div>
                                </div>

                                <hr>

                                <h4 class="mt-4">Kontak Emergency</h4>

                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="phone">Nama Emergency</label>
                                    <div class="col-sm-4">
                                        <input id="emergency_name" type="text" class="form-control" placeholder="Nama Emergency"
                                               name="emergency_name" value="{{ old('emergency_name') }}" />
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="phone">Nomor Kontak Emergency</label>
                                    <div class="col-sm-4">
                                        <input id="emergency_phone" type="text" class="form-control" placeholder="Nomor Kontak Emergency"
                                               name="emergency_phone" value="{{ old('emergency_phone') }}" />
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
        $('#form_create_partner').submit(function(){
            $('#btn_submit').hide();
            $('#btn_cancel').hide();
            $('#btn_loading').show(200);
        });

        flatpickr('.js-flatpickr', {
            "locale": "id"  // locale for this instance only
        });

        @if($branchId > 0 && $branchId < 6)
            new AutoNumeric('#commission_percentage', '{{ old('commission_percentage') ?? 0 }}', {
                minimumValue: '0',
                maximumValue: '80',
                digitGroupSeparator: '.',
                decimalCharacter: ',',
                decimalPlaces: 1,
                modifyValueOnWheel: false,
                allowDecimalPadding: false,
                emptyInputBehavior: 'zero'
            });
            new AutoNumeric('#basic_salary_amount', '{{ old('basic_salary_amount') ?? 0 }}', {
                minimumValue: '0',
                maximumValue: '999999999',
                digitGroupSeparator: '.',
                decimalCharacter: ',',
                decimalPlaces: 0,
                modifyValueOnWheel: false,
                allowDecimalPadding: false,
                unformatOnSubmit: true,
                emptyInputBehavior: 'zero'
            });
        @endif


        $('#image').fileinput({
            'showUpload': false,
            'overwriteInitial': false,
        });

        $('#specialty').select2();
    </script>
@endsection
