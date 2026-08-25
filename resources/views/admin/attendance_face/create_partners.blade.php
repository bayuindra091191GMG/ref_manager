@extends('layouts.admin')

@section('title')
    <title>Absensi</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Absensi Therapis
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.attendance.index') }}">Daftar Absensi</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Absensi
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->
    <div id="vue-section" class="content">
        <div class="row items-push">
            <div class="col-md-12 col-xl-12">
                <div class="block block-rounded h-100 mb-0">
                    <div class="block-content block-content-full">
                        <form method="post" action="{{ route('admin.attendance.submit_partner') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-12">
                                    <div class="row">
                                        <div class="col-sm-12">
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
                                    @if($partnerId != 0)
                                        <div class="row">
                                            <div class="col-6">
                                                <span style="color: red; font-size: 16px;">{{ $type }}</span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <span style="font-size: 16px;">Absen Untuk <b>{{ $partnerName }}</b></span>
                                                <input type="hidden" value="{{ $partnerId }}" name="employee_id"/>
                                                <input type="hidden" value="{{ $type }}" name="type"/>
                                            </div>
                                        </div>
                                        <div class="row mt-2 mb-2">
                                            <div class="col-6">
                                                <button type="button" class="btn btn-danger" id="btn_reset">Reset</button>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="row mt-2">
                                        <div class="col-2">
                                            <label>Karyawan</label>
                                        </div>
                                        <div class="col-6">
                                            @if($partnerId == 0)
                                                <select id="employee_select" class="form-control form-control-select2" name="employee_id"></select>
                                            @else
                                                <select id="employee_select" class="form-control form-control-select2" name="employee_id" disabled></select>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-2">
                                            <label>Foto</label>
                                        </div>
                                        <div class="col-6">
                                            <input type="file" class="form-control" name="filename" accept="image/png, image/jpg, image/jpeg, image/webp" />
                                            <label for="main_image" class="text-muted mt-1">
                                                Maksimal ukuran gambar 3 MB
                                            </label>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-8 text-end">
                                            <button id="btn_loading" type="button" class="btn btn-outline-success" style="display: none;">
                                                <i class="spinner-border spinner-border-sm text-green" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </i>
                                            </button>
                                            <input type="submit" id="btn_submit" class="btn btn-outline-success" value="Submit"/>&nbsp;&nbsp;
                                            <a id="btn_cancel" href="#" class="btn btn-outline-danger">Cancel</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <!-- Page JS Plugins CSS -->
    {{--    <link rel="stylesheet" href="{{ asset('custom/summernote/summernote-bs4.css') }}">--}}
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('custom/bootstrap-fileinput/css/fileinput.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('js/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}">

    <style>
        .bootstrap-tagsinput {
            background-color: #fff;
            border: 1px solid #ccc;
            box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
            display: block;
            padding: 4px 6px;
            color: #555;
            vertical-align: middle;
            border-radius: 4px;
            max-width: 100%;
            line-height: 22px;
            cursor: text;
        }
        .bootstrap-tagsinput input {
            border: none;
            box-shadow: none;
            outline: none;
            background-color: transparent;
            padding: 0 6px;
            margin: 0;
            width: auto;
            max-width: inherit;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice{
            color: #000;
        }
    </style>
    <style>
    </style>
@endsection

@section('scripts')
    <!-- jQuery -->
    <script type="text/javascript" src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('custom/bootstrap-fileinput/js/fileinput.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
    <script type="text/javascript" href="{{ asset('custom/dist/bootstrap-tagsinput.min.js') }}"></script>
    <script src="{{ asset('js/axios.js') }}"></script>
    <script src="{{ asset('js/vue.js') }}"></script>
    <script src="{{ asset('js/AutoNumeric.js') }}"></script>
    <script src="{{ asset('js/vue-autonumeric.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/webcam.min.js') }}"></script>

    <script>
        // Webcam.set({
        //     width: 400,
        //     height: 320,
        //     image_format: 'jpeg',
        //     jpeg_quality: 90
        // });
        // Webcam.attach( '#my_camera' );
        //
        // $('#btn_submit').click(function(){
        //     Webcam.snap( function(data_uri) {
        //         // display results in page
        //         document.getElementById('results').innerHTML =
        //             '<img src="'+data_uri+'"/>';
        //         $('#filename').val(data_uri);
        //     } );
        // });
    </script>

    <script>
        $('#employee_select').select2({
            placeholder: {
                id: '-1',
                text: ' - Pilih Therapis - '
            },
            width: '100%',
            minimumInputLength: 0,
            ajax: {
                url: '{{ route('admin.partner.select') }}',
                dataType: 'json',
                data: function (params) {
                    return {
                        search: $.trim(params.term),
                    };
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                }
            }
        });

        $('#employee_select').on("select2:select", function(e) {
            // what you would like to happen
            let url = '{{ route('admin.attendance.create_partner') }}?partner_id=' + $('#employee_select').val();
            window.location.href = url;
        });

        $('#btn_reset').click(function(){
            window.location.href = '{{ route('admin.attendance.create_partner') }}';
        });
    </script>
@endsection
