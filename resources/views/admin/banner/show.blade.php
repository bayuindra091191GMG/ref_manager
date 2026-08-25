@extends('layouts.admin')

@section('title')
    <title>Detail Banner</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Detail Banner
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.banner.index') }}">Daftar Banner</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Detail Banner
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <div id="app" class="content">
        <form class="space-y-3" id="form_create_employee'" method="POST" action="{{ route('admin.banner.update') }}" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-12 text-end">
                    <button id="btn_loading" type="button" class="btn btn-success" style="display: none;">
                        <i class="spinner-border spinner-border-sm text-green" role="status">
                            <span class="visually-hidden">Mohon tunggu...</span>
                        </i>
                    </button>
                    <a id="btn_cancel" href="{{ route('admin.banner.index') }}" class="btn btn-danger">Batal</a>
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
                                    <label class="col-sm-3 col-form-label" for="status">Status <span class="text-red">*</span></label>
                                    <div class="col-sm-4">
                                        <select class="form-control" id="status" name="status" disabled>
                                            <option value="1" {{ $banner->status_id == 1 ? 'selected' : '' }}>Aktif</option>
                                            <option value="2" {{ $banner->status_id == 2 ? 'selected' : '' }}>Non-Aktif</option>
                                        </select>
                                    </div>
                                </div>
{{--                                <div class="row">--}}
{{--                                    <label class="col-sm-3 col-form-label" for="name">Tipe <span class="text-red">*</span></label>--}}
{{--                                    <div class="col-sm-4">--}}
{{--                                        <select id="type" type="text" class="form-control"--}}
{{--                                                name="type" v-model="type" disabled>--}}
{{--                                            <option value="" disabled selected>Pilih Tipe</option>--}}
{{--                                            <option value="branch" {{ $banner->type == 'branch' ? 'selected' : '' }}>Branch</option>--}}
{{--                                            <option value="voucher" {{ $banner->type == 'voucher' ? 'selected' : '' }}>Voucher</option>--}}
{{--                                        </select>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="name">Judul <span class="text-red">*</span></label>
                                    <div class="col-sm-4">
                                        <input id="name" type="text" class="form-control" placeholder="Judul Banner"
                                               name="name" value="{{ old('name', $banner->name) }}" disabled/>
                                    </div>
                                </div>
{{--                                <div v-if="type == 'branch'">--}}
{{--                                    <div class="row">--}}
{{--                                        <label class="col-sm-3 col-form-label" for="name">Branch <span class="text-red">*</span></label>--}}
{{--                                        <div class="col-sm-4">--}}
{{--                                            <select2 url="{{ route('admin.branch.select') }}" id="branch_id" type="text" class="form-control"--}}
{{--                                                     name="branch_id" v-model="branch_id" placeholder="Pilih branch yang dipromosikan" disabled>--}}
{{--                                            </select2>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
                                <div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="name">Voucher <span class="text-red">*</span></label>
                                        <div class="col-sm-4">
                                            <select2 url="{{ route('admin.coupon.select') }}" id="coupon_id" type="text" class="form-control"
                                                     name="coupon_id" v-model="coupon_id" placeholder="Pilih voucher yang dipromosikan" disabled>
                                            </select2>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="address">Description</label>
                                    <div class="col-sm-4">
                                        <textarea class="form-control" id="description" name="description" rows="5" style="resize: vertical;" placeholder="Deskripsi Banner" readonly>{{ old('description', $banner->description) }}</textarea>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <label class="form-label">Banner Image <span class="text-red">*</span></label>
                                    </div>
                                    <div class="col-sm-4">
                                        <div>
                                            <img
                                                v-if="main_image"
                                                :src="main_image"
                                                alt="Preview Main Image"
                                                style="height: auto; width: 100%"
                                            />
                                        </div>
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
    <script src="{{ asset('js/vue.js') }}"></script>
    <script src="{{ asset('js/axios.js') }}"></script>
    <script src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('js/plugins/bootstrap-notify/bootstrap-notify.min.js') }}"></script>

    <script type="text/x-template" id="select2-template">
        <select>
            <slot></slot>
        </select>
    </script>
    <script src="{{ asset('js/vueComponent-select2.js') }}"></script>

    <script>
        let app = new Vue({
            el: '#app',
            data: {
                type: '{{ $banner->type }}',
                main_image: '{{ asset('images/banners/' . $banner->image_path) }}',
                branch_id: '{{ $banner->branch_id ?? '' }}',
                coupon_id: '{{ $banner->coupon_id ?? '' }}',
            },
            methods: {
                handleMainImageUpload(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    const validTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp'];
                    if (!validTypes.includes(file.type)) {
                        alert('The file must be in one of the following formats: PNG, JPEG, JPG, or WEBP.');
                        event.target.value = '';
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.main_image = e.target.result;
                    };
                    reader.readAsDataURL(file);
                },
            }
        })
    </script>

    <script>
        $('#form_create_employee').submit(function(){
            $('#btn_submit').hide();
            $('#btn_cancel').hide();
            $('#btn_loading').show(200);
        });
    </script>
@endsection
