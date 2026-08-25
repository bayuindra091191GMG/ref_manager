@extends('layouts.admin')

@section('title')
    <title>Tambah Karyawan</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Tambah Karyawan
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
                        <li class="breadcrumb-item" aria-current="page">
                            Tambah Karyawan
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <div class="content">
        <form class="space-y-3" id="form_create_employee'" method="POST" action="{{ route('admin.employee.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-12 text-end">
                    <button id="btn_loading" type="button" class="btn btn-success" style="display: none;">
                        <i class="spinner-border spinner-border-sm text-green" role="status">
                            <span class="visually-hidden">Mohon tunggu...</span>
                        </i>
                    </button>
                    <input type="submit" id="btn_submit" class="btn btn-success" value="Simpan"/>&nbsp;&nbsp;
                    <a id="btn_cancel" href="{{ route('admin.employee.index') }}" class="btn btn-danger">Batal</a>
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

{{--                                <div class="row">--}}
{{--                                    <label class="col-sm-3 col-form-label" for="branch">Cabang <span class="text-red">*</span></label>--}}
{{--                                    <div class="col-sm-3">--}}
{{--                                        @if($isSuperAdmin)--}}
{{--                                            <select id="branch" name="branch" class="form-select" onchange="getPositions();">--}}
{{--                                                <option id="0" value="">----- Pilih Cabang ------</option>--}}
{{--                                                @foreach($branches as $branch)--}}
{{--                                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>--}}
{{--                                                @endforeach--}}
{{--                                            </select>--}}
{{--                                        @else--}}
                                            <input type="hidden" name="branch" value="{{ $adminBranchId }}">
{{--                                            <input type="text" class="form-control" value="{{ $adminBranch->name }}" readonly/>--}}
{{--                                        @endif--}}
{{--                                    </div>--}}
{{--                                </div>--}}
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="name">NIK Karyawan <span class="text-red">*</span></label>
                                    <div class="col-sm-3">
                                        <input id="code" type="text" class="form-control" style="text-transform: uppercase;"
                                               name="code" value="{{ old('code') }}" required/>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="name">Nama <span class="text-red">*</span></label>
                                    <div class="col-sm-4">
                                        <input id="name" type="text" class="form-control"
                                               name="name" value="{{ old('name') }}" required/>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="phone">Nomor Telp</label>
                                    <div class="col-sm-3">
                                        <input id="phone" type="text" class="form-control"
                                               name="phone" value="{{ old('phone') }}" />
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="identity_no">Nomor KTP</label>
                                    <div class="col-sm-3">
                                        <input id="identity_no" type="text" class="form-control"
                                               name="identity_no" value="{{ old('identity_no') }}"/>
                                    </div>
                                </div>
{{--                                <div class="row">--}}
{{--                                    <label class="col-sm-3 col-form-label" for="department">Departemen <span class="text-red">*</span></label>--}}
{{--                                    <div class="col-sm-3">--}}
{{--                                        <select class="form-select" id="department" name="department">--}}
{{--                                            <option value="0">----- Pilih Departemen -----</option>--}}
{{--                                            @foreach($departments as $department)--}}
{{--                                                <option value="{{ $department->id }}">{{ $department->name }}</option>--}}
{{--                                            @endforeach--}}
{{--                                        </select>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="position">Jabatan <span class="text-red">*</span></label>
                                    <div class="col-sm-3">
                                        <select class="form-select" id="position" name="position" @if(empty($positions)) disabled @endif>
                                            <option value="0">----- Pilih Posisi -----</option>
                                            @if(!empty($positions))
                                                @foreach($positions as $position)
                                                    <option value="{{ $position->id }}">{{ $position->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="basic_salary_amount">Gaji Pokok</label>
                                    <div class="col-sm-3">
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input id="basic_salary_amount" type="text" class="form-control text-end"
                                                   name="basic_salary_amount" />
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="attend_machine_id">Nomor Urut Mesin Absen</label>
                                    <div class="col-sm-3">
                                        <input id="attend_machine_id" type="text" class="form-control"
                                               name="attend_machine_id" value="{{ old('attend_machine_id') }}"/>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="status">Status <span class="text-red">*</span></label>
                                    <div class="col-sm-3">
                                        <select class="form-control" id="status" name="status">
                                            <option value="1" selected >Aktif</option>
                                            <option value="2">Non-Aktif</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="address">Alamat</label>
                                    <div class="col-sm-8">
                                        <textarea class="form-control" id="address" name="address" rows="5" style="resize: vertical;"></textarea>
                                    </div>
                                </div>
{{--                                <div class="row">--}}
{{--                                    <label class="col-sm-3 col-form-label" for="image">Upload Foto</label>--}}
{{--                                    <div class="col-sm-8 col-12">--}}
{{--                                        <input type="file" name="image" id="image" accept=".png,.jpg,.jpeg,.webp"/>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
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
@endsection

@section('scripts')
    <!-- jQuery -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('js/plugins/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
    <script src="{{ asset('js/AutoNumeric.js') }}"></script>

    <script>
        $('#form_create_employee').submit(function(){
            $('#btn_submit').hide();
            $('#btn_cancel').hide();
            $('#btn_loading').show(200);
        });

        $('#department').select2();
        $('#position').select2();

        new AutoNumeric('#basic_salary_amount', '{{ old('basic_salary_amount') ?? 0 }}', {
            minimumValue: '0',
            maximumValue: '99999999',
            digitGroupSeparator: '.',
            decimalCharacter: ',',
            decimalPlaces: 2,
            modifyValueOnWheel: false,
            allowDecimalPadding: false,
        });

        {{--function getPositions(){--}}
        {{--    let branchId = $('#branch').val();--}}
        {{--    if(branchId == 0){--}}
        {{--        return false;--}}
        {{--    }--}}

        {{--    $.ajax({--}}
        {{--        type:'GET',--}}
        {{--        url:'{{ route('admin.employee_position.get') }}?branch_id=' + branchId,--}}
        {{--        success:function(data){--}}
        {{--            let error = data['error'];--}}
        {{--            if(error === 'none'){--}}
        {{--                $('#position').val(null).trigger('change');--}}
        {{--                $('#position').empty();--}}

        {{--                for(let i = 0; i < data.response.positions.length; i++){--}}
        {{--                    let item = data.response.positions[i];--}}
        {{--                    var newOption = new Option(item.text, item.id, false, false);--}}
        {{--                    $('#position').append(newOption).trigger('change');--}}
        {{--                }--}}

        {{--                $('#position').prop('disabled', false);--}}
        {{--            }--}}
        {{--            else{--}}
        {{--                One.helpers('jq-notify', {type: 'danger', icon: 'fa fa-info-circle me-1', timer: 2000, message: 'Terjadi kesalahan input!'});--}}
        {{--            }--}}
        {{--        },--}}
        {{--        error:function(data){--}}
        {{--            One.helpers('jq-notify', {type: 'danger', icon: 'fa fa-info-circle me-1', timer: 2000, message: 'Terjadi kesalahan pada internal sistem!'});--}}
        {{--        }--}}
        {{--    });--}}
        {{--}--}}

        // $('#image').fileinput({
        //     'showUpload': false,
        //     'overwriteInitial': false,
        // });
    </script>
@endsection
