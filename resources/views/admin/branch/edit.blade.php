@extends('layouts.admin')

@section('title')
    <title>Ubah Cabang</title>
@endsection

@section('content')
    @php
        $selectedExtraText = (array) old('extra_text', !empty($branch->extra_text) ? explode('|', $branch->extra_text) : []);
    @endphp

    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Ubah Cabang
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.branch.index') }}">Daftar Cabang</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Ubah Cabang
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <div class="content">
        <form class="space-y-3" id="form_edit_branch" method="POST" action="{{ route('admin.branch.update') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id" value="{{ $branch->id }}"/>
            <input type="hidden" name="current_image" value="{{ $branch->image_filename }}"/>
            <div class="row">
                <div class="col-12 text-end">
                    <button id="btn_loading" type="button" class="btn btn-success" style="display: none;">
                        <i class="spinner-border spinner-border-sm text-green" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </i>
                    </button>
                    <input type="submit" id="btn_submit" class="btn btn-success" value="Simpan"/>&nbsp;&nbsp;
                    <a id="btn_cancel" href="{{ route('admin.branch.index') }}" class="btn btn-danger">Batal</a>
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
                                    <label class="col-sm-3 col-form-label" for="code">Kode <span class="text-red">*</span></label>
                                    <div class="col-sm-4">
                                        <input id="code" type="text" class="form-control" style="text-transform: uppercase;"
                                               name="code" value="{{ old('code', $branch->code) }}" placeholder="Kode"
                                               maxlength="8" pattern="[A-Za-z0-9]{1,8}" required/>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="name">Nama <span class="text-red">*</span></label>
                                    <div class="col-sm-4">
                                        <input id="name" type="text" class="form-control" placeholder="Nama"
                                               name="name" value="{{ old('name', $branch->name) }}" maxlength="100" required/>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="description">Deskripsi</label>
                                    <div class="col-sm-6">
                                        <textarea name="description" id="description" rows="3" class="form-control"
                                                  maxlength="200" placeholder="Deskripsi">{{ old('description', $branch->description) }}</textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="address">Alamat</label>
                                    <div class="col-sm-6">
                                        <textarea name="address" id="address" rows="5" class="form-control"
                                                  maxlength="200" placeholder="Alamat">{{ old('address', $branch->address) }}</textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="phone">No Telepon</label>
                                    <div class="col-sm-4">
                                        <input id="phone" type="text" class="form-control" placeholder="No Telepon"
                                               name="phone" value="{{ old('phone', $branch->phone) }}" maxlength="30"/>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="latitude">Latitude</label>
                                    <div class="col-sm-4">
                                        <input id="latitude" type="number" step="any" min="-90" max="90"
                                               class="form-control" placeholder="Latitude"
                                               name="latitude" value="{{ old('latitude', $branch->latitude) }}"/>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="longitude">Longitude</label>
                                    <div class="col-sm-4">
                                        <input id="longitude" type="number" step="any" min="-180" max="180"
                                               class="form-control" placeholder="Longitude"
                                               name="longitude" value="{{ old('longitude', $branch->longitude) }}"/>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label">Fasilitas</label>
                                    <div class="col-sm-8">
                                        <div class="row">
                                            @foreach($extraTextOptions as $option)
                                                <div class="col-sm-4 col-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="extra_text[]"
                                                               id="extra_text_{{ $loop->index }}" value="{{ $option }}"
                                                               @if(in_array($option, $selectedExtraText, true)) checked @endif>
                                                        <label class="form-check-label" for="extra_text_{{ $loop->index }}">{{ $option }}</label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @if(!empty($branch->image_filename))
                                    <div class="row">
                                        <label class="col-sm-3 col-12 col-form-label" for="image">Gambar Sekarang</label>
                                        <div class="col-sm-8 col-12">
                                            @if(file_exists(public_path('images/branches/' . basename($branch->image_filename))))
                                                <img src="{{ asset('images/branches/' . basename($branch->image_filename)) }}" style="max-width: 250px; max-height: 160px; object-fit: contain;">
                                            @else
                                                <input type="text" class="form-control" value="{{ basename($branch->image_filename) }}" readonly>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                <div class="row">
                                    <label class="col-sm-3 col-12 col-form-label" for="image">Ubah Gambar @if(empty($branch->image_filename))<span class="text-red">*</span>@endif</label>
                                    <div class="col-sm-8 col-12">
                                        <input type="file" name="image" id="image" accept=".png,.jpg,.jpeg" @if(empty($branch->image_filename)) required @endif/>
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
    <link rel="stylesheet" href="{{ asset('js/plugins/bootstrap-fileinput/css/fileinput.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.min.css" crossorigin="anonymous">
@endsection

@section('scripts')
    <!-- jQuery -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script src="{{ asset('js/plugins/bootstrap-fileinput/js/fileinput.js') }}"></script>

    <script>
        $('#code').on('input', function(){
            this.value = this.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase().substring(0, 8);
        });

        $('#image').fileinput({
            showUpload: false,
            overwriteInitial: true,
            maxFileCount: 1,
            allowedFileExtensions: ['jpg', 'jpeg', 'png'],
            browseOnZoneClick: true
        });

        $('#form_edit_branch').submit(function(event){
            if (!this.checkValidity()) {
                return;
            }

            $('#btn_submit').hide();
            $('#btn_cancel').hide();
            $('#btn_loading').show(200);
        });
    </script>
@endsection
