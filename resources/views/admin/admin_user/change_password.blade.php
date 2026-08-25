@extends('layouts.admin')

@section('title')
    <title>Ubah Kata Sandi</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Ubah Kata Sandi
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Ubah Kata Sandi
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <div class="content">
        <form class="space-y-3" id="form_change_password'" method="POST" action="{{ route('admin.my.change_password.submit') }}">
            @csrf
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
                                    <label class="col-sm-3 col-form-label" for="old_password">Kata Sandi Lama <span class="text-red">*</span></label>
                                    <div class="col-sm-3">
                                        <input id="old_password" type="password" class="form-control"
                                               name="old_password" required />
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="new_password">Kata Sandi Baru <span class="text-red">*</span></label>
                                    <div class="col-sm-4">
                                        <input id="new_password" type="password" class="form-control"
                                               name="new_password" required />
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="retry_password">Ulangi Kata Sandi Baru <span class="text-red">*</span></label>
                                    <div class="col-sm-4">
                                        <input id="retry_password" type="password" class="form-control"
                                               name="retry_password" required />
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12 text-center">
                                        <button id="btn_loading" type="button" class="btn btn-success" style="display: none;">
                                            <i class="spinner-border spinner-border-sm text-green" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </i>
                                        </button>
                                        <input type="submit" id="btn_submit" class="btn btn-success" value="Ubah"/>&nbsp;&nbsp;
                                        <a id="btn_cancel" href="{{ route('admin.dashboard') }}" class="btn btn-danger">Batal</a>
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
        $('#form_change_password').submit(function(){
            $('#btn_submit').hide();
            $('#btn_cancel').hide();
            $('#btn_loading').show(200);
        });
    </script>
@endsection
