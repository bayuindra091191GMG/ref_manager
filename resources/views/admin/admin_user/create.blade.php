@extends('layouts.admin')

@section('title')
    <title>Tambah Admin</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Tambah Admin
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.admin_user.index') }}">Daftar Admin</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Tambah Admin
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <div class="content">
        <form class="space-y-3" id="form_create_admin'" method="POST" action="{{ route('admin.admin_user.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-12 text-end">
                    <button id="btn_loading" type="button" class="btn btn-success" style="display: none;">
                        <i class="spinner-border spinner-border-sm text-green" role="status">
                            <span class="visually-hidden">Mohon tunggu...</span>
                        </i>
                    </button>
                    <input type="submit" id="btn_submit" class="btn btn-success" value="Simpan"/>&nbsp;&nbsp;
                    <a id="btn_cancel" href="{{ route('admin.admin_user.index') }}" class="btn btn-danger">Batal</a>
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
                                    <label class="col-sm-3 col-form-label" for="username">Username <span class="text-red">*</span></label>
                                    <div class="col-sm-4">
                                        <input id="username" type="text" class="form-control"
                                               name="username" value="{{ old('username') }}" autocomplete="chrome-off" required/>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="name">Nama</label>
                                    <div class="col-sm-2">
                                        <input id="name" type="text" class="form-control"
                                               name="name" value="{{ old('name') }}" required/>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="new_password">Kata Sandi <span class="text-red">*</span></label>
                                    <div class="col-sm-4">
                                        <input id="new_password" type="password" class="form-control"
                                               name="new_password" required />
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="retry_password">Ulangi Kata Sandi <span class="text-red">*</span></label>
                                    <div class="col-sm-4">
                                        <input id="retry_password" type="password" class="form-control"
                                               name="retry_password" required />
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="employee">Hubungkan ke Karyawan</label>
                                    <div class="col-sm-4">
                                        <select id="employee" name="employee" class="form-select"></select>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="role">Role <span class="text-red">*</span></label>
                                    <div class="col-sm-4">
                                        <select class="form-select" id="role" name="role" required>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->name }}" {{ $role->name === 'staff' ? 'selected' : '' }}>
                                                    {{ ucwords(str_replace('_', ' ', $role->name)) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="status">Status <span class="text-red">*</span></label>
                                    <div class="col-sm-3">
                                        <select class="form-select" id="status" name="status">
                                            <option value="1" selected >Aktif</option>
                                            <option value="2">Non-Aktif</option>
                                        </select>
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
@endsection

@section('scripts')
    <!-- jQuery -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>

    <script>
        $('#form_create_admin').submit(function(){
            $('#btn_submit').hide();
            $('#btn_cancel').hide();
            $('#btn_loading').show(200);
        });

        $('#employee').select2({
            placeholder: {
                id: '-1',
                text: ' --- Pilih Karyawan --- '
            },
            width: '100%',
            minimumInputLength: 0,
            ajax: {
                url: '{{ route('admin.employee.select') }}',
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
