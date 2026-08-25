@extends('layouts.admin')

@section('title')
    <title>Ubah Jabatan Karyawan</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Ubah Jabatan
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.position.index') }}">Daftar Jabatan</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Ubah Jabatan
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <div class="content">
        <form class="space-y-3" id="form_edit_position'" method="POST" action="{{ route('admin.position.update', ['id' => $position->id]) }}">
            @csrf
            <div class="row">
                <div class="col-12 text-end">
                    <button id="btn_loading" type="button" class="btn btn-success" style="display: none;">
                        <i class="spinner-border spinner-border-sm text-green" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </i>
                    </button>
                    <input type="submit" id="btn_submit" class="btn btn-success" value="Simpan"/>&nbsp;&nbsp;
                    <a id="btn_cancel" href="{{ route('admin.position.index') }}" class="btn btn-danger">Batal</a>
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
{{--                                                @foreach($branches as $branch)--}}
{{--                                                    <option value="{{ $branch->id }}" @if($position->branch_id === $branch->id) selected @endif>{{ $branch->name }}</option>--}}
{{--                                                @endforeach--}}
{{--                                            </select>--}}
{{--                                        @else--}}
                                            <input type="hidden" name="branch" value="{{ $adminBranchId }}">
{{--                                            <input type="text" class="form-control" value="{{ $adminBranch->name }}" readonly/>--}}
{{--                                        @endif--}}
{{--                                    </div>--}}
{{--                                </div>--}}
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="name">Kode Jabatan <span class="text-red">*</span></label>
                                    <div class="col-sm-2">
                                        <input id="code" type="text" class="form-control" style="text-transform: uppercase;"
                                               name="code" value="{{ $position->code }}" required/>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="name">Nama <span class="text-red">*</span></label>
                                    <div class="col-sm-4">
                                        <input id="name" type="text" class="form-control"
                                               name="name" value="{{ $position->name }}" required/>
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
@endsection

@section('scripts')
    <!-- jQuery -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>

    <script>
        $('#form_edit_position').submit(function(){
            $('#btn_submit').hide();
            $('#btn_cancel').hide();
            $('#btn_loading').show(200);
        });
    </script>
@endsection
