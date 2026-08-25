@extends('layouts.admin-login')

@section('title')
    <title>Simi INDONESIA - Backend Login</title>
@endsection

@section('styles')
    <style>
        #main-container {
            overflow-x: hidden;
            overflow-y: hidden;
            background-color: #547155;
        }
    </style>
@endsection

@section('content')
    <main id="main-container">
        <!-- Page Content -->
        <div class="hero-static d-flex align-items-center justify-content-center">
            <div style="width: 100%; max-width: 500px;">
                <!-- Sign In Section -->
                <div class="card" style="border-radius: 50px !important;">
                    <div class="card-body p-5">
                        <div class="text-center">
                            <p class="mb-3">
                                <img src="{{ asset('images/logo-only.png') }}" alt="" style="width: 200px; height: auto">
                            </p>
                            <h2 class="fw-bold text-muted mb-3">
                                Admin Login<br>
                            </h2>
                        </div>
                        <!-- END Header -->

                        @foreach($errors->all() as $error)
                            <span class="help-block">
                                        <strong style="color: #ff3d00;"> {{ $error }} </strong>
                                    </span>
                        @endforeach

                        <form method="POST" action="{{ route('admin.login.submit') }}">
                            @csrf
                            <div class="py-3">
                                <div class="mb-4">
                                    <select class="form-control form-control-lg" name="branch_id" >
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <input type="text" class="form-control form-control-lg form-control-alt"
                                           id="username" name="username" placeholder="Username" value="{{ old('username') }}"
                                           oninput="this.value = this.value.toUpperCase()" >
                                </div>
                                <div class="mb-4">
                                    <input type="password" class="form-control form-control-lg form-control-alt"
                                           id="password" name="password" placeholder="Kata Sandi">
                                </div>
                                <div class="mb-4">
                                    <div class="d-md-flex align-items-md-center justify-content-md-between">
                                    </div>
                                </div>
                            </div>
                            <div class="row justify-content-center">
                                <div class="col-lg-6 col-xxl-5">
                                    <button type="submit" class="btn w-100 btn-alt-primary" onclick="this.form.submit(); this.disabled=true;">
                                        <i class="fa fa-fw fa-sign-in-alt me-1 opacity-75"></i> Sign In
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- END Sign In Section -->

                <!-- Footer -->
                <div class="fs-sm text-center text-white py-3">
                    <strong> Simi Reflexology Massage & Spa </strong> &copy; <span data-toggle="year-copy"></span>
                </div>
                <!-- END Footer -->
            </div>
        </div>
        <!-- END Page Content -->
    </main>
@endsection
