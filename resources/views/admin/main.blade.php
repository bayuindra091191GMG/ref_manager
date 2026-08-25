@extends('layouts.admin-login')

@section('title')
    <title>Welcome</title>
@endsection

@section('content')
    <main id="main-container">
        <!-- Page Content -->
        <div class="hero-static d-flex align-items-center">
            <div class="w-100">
                <div >
                    <div class="content content-full">
                        <div class="row g-0 justify-content-center">
                            <div class="col-md-8 col-lg-6 col-xl-4 py-4 px-4 px-lg-5">
                                <div class="text-center">
                                    <h2 class="fw-medium text-muted mb-3">
                                        WELCOME
                                    </h2>
                                    <div class="py-3">
                                        @foreach($branches as $branch)
                                            <div class="mb-4">
                                                <a href="{{ route('admin.login', ['code' => $branch->code]) }}">LOGIN {{ $branch->name }}</a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END Sign In Section -->

                <!-- Footer -->
                <div class="fs-sm text-center text-muted py-3">
{{--                    <strong>Hoki Cuan </strong> &copy; <span data-toggle="year-copy"></span>--}}
                </div>
                <!-- END Footer -->
            </div>
        </div>
        <!-- END Page Content -->
    </main>
@endsection
