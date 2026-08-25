
@extends('layouts.admin')

@section('title')
    <title>Import Absensi</title>
@endsection

@section('content')
    <!-- Hero -->
     <div>
        <div class="content space-top">
              <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                  <div class="flex-grow-1">
                      <h1 class="h3 fw-bold mb-2">
                          Import Absensi
                      </h1>
                  </div>
                  <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                      <ol class="breadcrumb breadcrumb-alt">
                          <li class="breadcrumb-item">
                              <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                          </li>
                          <li class="breadcrumb-item" aria-current="page">
                              Import Absensi
                          </li>
                      </ol>
                  </nav>
              </div>
          </div>
      </div>
    <!-- END Hero -->

    <div class="content">
            <div class="block block-rounded">
                <div class="block-content block-content-full">
                    <div class="row mb-3">
                        <div class="col-12">
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
                    <form class="space-y-3" id="form_import_employee'" method="POST" action="{{ route('admin.attendance.submit') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-4">
                                <label for="upload" class="form-label">Upload File Kehadiran Terapis <span class="text-red">*</span></label>
                                <input type="file" class="form-control" id="upload" name="excel" accept=".xlsx" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <button class="btn btn-primary" type="submit">Submit</button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
    </div>

@endsection

@section('styles')
    <!-- Page JS Plugins CSS -->

    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/flatpickr/flatpickr.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-bs5/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-buttons-bs5/buttons.bootstrap5.min.css') }}">

@endsection

@section('scripts')
    <!-- jQuery -->

    <script src="{{ asset('js/plugins/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('js/plugins/flatpickr/l10n/id.js') }}"></script>
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>
@endsection
