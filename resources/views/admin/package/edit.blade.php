@extends('layouts.admin')

@section('title')
    <title>Ubah Treatment</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Ubah Treatment
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.package.index') }}">Daftar Treatment</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Ubah Treatment
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->
    <div class="content" id="vue-section">
        <div class="row items-push">
            <div class="col-md-12 col-xl-12">
                <div class="block block-rounded h-100 mb-0">
                    <div class="block-content block-content-full">
                        <div class="row">
                            <div class="col-12">
                                <form class="space-y-3" id="form_create_package" method="POST" action="{{ route('admin.package.update') }}"
                                      enctype="multipart/form-data">
                                    @csrf
                                    <div class="row">
                                        <div class="col-12 text-end">
                                            <button id="btn_loading" type="button" class="btn btn-success" style="display: none;">
                                                <i class="spinner-border spinner-border-sm text-green" role="status">
                                                    <span class="visually-hidden">Mohon Tunggu...</span>
                                                </i>
                                            </button>
                                            <input type="submit" id="btn_submit" class="btn btn-success" value="Simpan"/>&nbsp;&nbsp;
                                            <a id="btn_cancel" href="{{ route('admin.package.index') }}" class="btn btn-danger">Batal</a>
                                        </div>
                                    </div>
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
                                            <span style="color: red;">Isian dengan asterik (*) wajib diisi!</span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="type">Tipe Room</label>
                                        <div class="col-sm-6">
                                            <select class="form-select" id="type" name="room_type_id">
                                                @foreach($roomTypes as $type)
                                                    <option value="{{ $type->id }}" @selected($package->room_type_id == $type->id)>{{ $type->display }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="status">Status</label>
                                        <div class="col-sm-6">
                                            <select class="form-select" id="status" name="status">
                                                <option value="1" @if($package->status_id === 1) selected @endif>Aktif</option>
                                                <option value="2" @if($package->status_id === 2) selected @endif>Non-Aktif</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <label class="col-form-label" for="name">Nama <span class="text-red">*</span></label>
                                        </div>
                                        <div class="col-sm-6">
                                            <input type="hidden" name="id" value="{{ $package->id }}"/>
                                            <input type="text" id="name" name="name" class="form-control" placeholder="Nama Treatment" value="{{ $package->name }}" required/>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <label class="col-form-label" for="duration">Durasi <span class="text-red">*</span></label>
                                        </div>
                                        <div class="col-sm-6">
                                            <input type="number" min="0" max="300" id="duration" name="duration" class="form-control" placeholder="Durasi" value="{{ $package->duration }}" required/>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="price">Price <span class="text-red">*</span></label>
                                        <div class="col-sm-6">
                                            <vue-autonumeric :options="autonumericFormat" name="price" class="form-control" value="{{ $package->price }}"></vue-autonumeric>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="partner_count">Jumlah Therapis <span class="text-red">*</span></label>
                                        <div class="col-sm-6">
                                            <vue-autonumeric :options="autonumericFormatTime" id="partner_count" name="partner_count" class="form-control" value="{{ $package->partner_count }}"></vue-autonumeric>
                                        </div>
                                    </div>
{{--                                    <div class="row">--}}
{{--                                        <label class="col-sm-3 col-form-label" for="additional_person_price">Biaya Tambahan Customer</label>--}}
{{--                                        <div class="col-sm-6">--}}
{{--                                            <vue-autonumeric :options="autonumericFormat" id="additional_person_price" name="additional_person_price" class="form-control" value="{{ $package->additional_person_price }}"></vue-autonumeric>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                    <div class="row">--}}
{{--                                        <label class="col-sm-3 col-form-label" for="additional_partner_cost">Biaya Tambahan Therapis</label>--}}
{{--                                        <div class="col-sm-6">--}}
{{--                                            <vue-autonumeric :options="autonumericFormat" id="additional_partner_price" name="additional_partner_price" class="form-control" value="{{ $package->additional_partner_price }}"></vue-autonumeric>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                    <div class="row">--}}
{{--                                        <label class="col-sm-3 col-form-label" for="extend_duration">Durasi Extend</label>--}}
{{--                                        <div class="col-sm-6">--}}
{{--                                            <vue-autonumeric :options="autonumericFormatTime" name="extend_duration" class="form-control" value="{{ $package->extend_duration }}"></vue-autonumeric>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                    <div class="row">--}}
{{--                                        <label class="col-sm-3 col-form-label" for="extend_price">Biaya Extend </label>--}}
{{--                                        <div class="col-sm-6">--}}
{{--                                            <vue-autonumeric :options="autonumericFormat" id="extend_price" name="extend_price" class="form-control" value="{{ $package->extend_price }}"></vue-autonumeric>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
                                     <div class="row">
                                         <div class="col-sm-3">
                                             <label class="col-form-label" for="description">Deskripsi</label>
                                         </div>
                                         <div class="col-sm-6">
                                             <textarea id="description" name="description" class="form-control" rows="4" maxlength="150" placeholder="Description">{{ $package->description }}</textarea>
                                         </div>
                                     </div>
                                     @php($selectedBadgeStatus = old('badge_status', $package->badge_status ?? ''))
                                     <div class="row">
                                         <label class="col-sm-3 col-form-label">Badge Status</label>
                                         <div class="col-sm-6">
                                             <div class="form-check">
                                                 <input class="form-check-input" type="radio" name="badge_status" id="badge_status_none" value="" {{ $selectedBadgeStatus === '' ? 'checked' : '' }}>
                                                 <label class="form-check-label" for="badge_status_none">
                                                     None
                                                 </label>
                                             </div>
                                             <div class="form-check">
                                                 <input class="form-check-input" type="radio" name="badge_status" id="badge_status_recommended" value="recommended" {{ $selectedBadgeStatus === 'recommended' ? 'checked' : '' }}>
                                                 <label class="form-check-label" for="badge_status_recommended">
                                                     Recommended
                                                 </label>
                                             </div>
                                             <div class="form-check">
                                                 <input class="form-check-input" type="radio" name="badge_status" id="badge_status_best_seller" value="best_seller" {{ $selectedBadgeStatus === 'best_seller' ? 'checked' : '' }}>
                                                 <label class="form-check-label" for="badge_status_best_seller">
                                                     Best Seller
                                                 </label>
                                             </div>
                                         </div>
                                     </div>

                                     <div class="row">
                                         <label class="col-sm-3 col-12 col-form-label" for="ktp">Foto
                                             <br>
                                            <img src="{{ asset('storage/images/items/'.$package->image_path) }}" alt="Foto" class="img-thumbnail" style="max-width: 200px; max-height: 200px;"/>
                                        </label>
                                        <div class="col-sm-8 col-12">
                                            <input type="file" name="image" id="image" accept=".png,.jpg,.jpeg,.webp"/>
                                            <small class="text-muted">Maksimal size image 3 MB</small>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
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
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('custom/bootstrap-fileinput/css/fileinput.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('js/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/bootstrap-fileinput/css/fileinput.min.css') }}">
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

@endsection

@section('scripts')
    <!-- jQuery -->
    <script type="text/javascript" src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('custom/bootstrap-fileinput/js/fileinput.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    <script type="text/javascript" src="{{ asset('js/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ asset('js/plugins/bootstrap-fileinput/js/fileinput.js') }}"></script>

    <script type="text/javascript" href="{{ asset('custom/dist/bootstrap-tagsinput.min.js') }}"></script>
    <script src="{{ asset('js/axios.js') }}"></script>
    <script src="{{ asset('js/vue.js') }}"></script>
    <script src="{{ asset('js/AutoNumeric.js') }}"></script>
    <script src="{{ asset('js/vue-autonumeric.js') }}"></script>

    <script>
        let vueComponent = new Vue({
            el: "#vue-section",
            data: {
                autonumericFormat: {
                    minimumValue: '0',
                    maximumValue: '9999999999',
                    digitGroupSeparator: '.',
                    decimalCharacter: ',',
                    modifyValueOnWheel: false,
                    allowDecimalPadding: false,
                },
                autonumericFormatTime: {
                    minimumValue: '0',
                    digitGroupSeparator: '',
                    maximumValue: '120',
                    modifyValueOnWheel: false,
                    allowDecimalPadding: false,
                },
            },
        });
    </script>

    <script>
        $(document).ready(function () {
            $(window).keydown(function (event) {
                if (event.keyCode === 13) {
                    event.preventDefault();
                    return false;
                }
            });
        });

        $('#form_create_package').submit(function(){
            $('#btn_submit').hide();
            $('#btn_cancel').hide();
            $('#btn_loading').show(200);
        })

        $('#image').fileinput({
            'showUpload': false,
            'overwriteInitial': false,
        });
    </script>
@endsection
