@extends('layouts.admin')

@section('title')
    <title>Ubah Room</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Ubah Room
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.room.index') }}">Daftar Room</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Ubah Room
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->
    <div class="content">
        <div class="row items-push">
            <div class="col-md-12 col-xl-12">
                <div class="block block-rounded h-100 mb-0">
                    <div class="block-content block-content-full">
                        <div class="row">
                            <div class="col-12">
                                <form class="space-y-3" id="form_update_room" method="POST" action="{{ route('admin.room.update') }}">
                                    @csrf
                                    <div class="row">
                                        <div class="col-12 text-end">
                                            <button id="btn_loading" type="button" class="btn btn-success" style="display: none;">
                                                <i class="spinner-border spinner-border-sm text-green" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </i>
                                            </button>
                                            <input type="submit" id="btn_submit" class="btn btn-success" value="Simpan"/>&nbsp;&nbsp;
                                            <a id="btn_cancel" href="{{ route('admin.room.index') }}" class="btn btn-danger">Batal</a>
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
{{--                                    <div class="row">--}}
{{--                                        <label class="col-sm-3 col-form-label" for="branch_id">Branch *</label>--}}
{{--                                        <div class="col-sm-6">--}}
{{--                                            <select class="form-select" id="branch_id" name="branch_id">--}}
{{--                                                @foreach($branches as $branch)--}}
{{--                                                    @if($room->branch_id == $branch->id)--}}
{{--                                                        <option value="{{ $branch->id }}" selected>{{ $branch->name }}</option>--}}
{{--                                                    @else--}}
{{--                                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>--}}
{{--                                                    @endif--}}
{{--                                                @endforeach--}}
{{--                                            </select>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="status_id">Status <span class="text-red">*</span></label>
                                        <div class="col-sm-6">
                                            <select class="form-select" id="status_id" name="status_id">
                                                <option value="5" @if($room->status_id === 5) selected @endif>Available</option>
                                                <option value="6" @if($room->status_id === 6) selected @endif>Sedang Bekerja</option>
                                                <option value="11" @if($room->status_id === 11) selected @endif>Dibooking</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="type">Tipe <span class="text-red">*</span></label>
                                        <div class="col-sm-6">
                                            <select class="form-select" id="type" name="type">
                                                @foreach($roomTypes as $type)
                                                    @if($room->type == $type->name)
                                                        <option value="{{ $type->name }}" selected>{{ $type->display }}</option>
                                                    @else
                                                        <option value="{{ $type->name }}">{{ $type->display }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <label class="col-form-label" for="number">Nomor Room <span class="text-red">*</span></label>
                                        </div>
                                        <div class="col-sm-9">
                                            <input type="hidden" name="id" class="form-control" value="{{ $room->id }}"/>
                                            <input type="text" id="number" name="number" class="form-control" placeholder="Nomor Room" value="{{ $room->number }}" required/>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="name">Nama Room</label>
                                        <div class="col-sm-6">
                                            <input type="text" id="name" name="name" class="form-control" placeholder="Nama Room" value="{{ $room->name }}"/>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <label class="col-form-label" for="description">Deskripsi Tambahan</label>
                                        </div>
                                        <div class="col-sm-9">
                                            <textarea id="description" name="description" class="form-control" rows="4" maxlength="150" placeholder="Deskripsi Tambahan">{{ $room->description }}</textarea>
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

@endsection

@section('scripts')
    <!-- jQuery -->
    <script type="text/javascript" src="{{ asset('js/lib/jquery.min.js') }}"></script>

    <script>
        $(document).ready(function () {
            $(window).keydown(function (event) {
                if (event.keyCode === 13) {
                    event.preventDefault();
                    return false;
                }
            });
        });

        $('#form_update_room').submit(function(){
            $('#btn_submit').hide();
            $('#btn_cancel').hide();
            $('#btn_loading').show(200);
        })
    </script>
@endsection
