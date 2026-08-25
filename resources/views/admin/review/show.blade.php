@extends('layouts.admin')

@section('title')
    <title>Detil Ulasan</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Detil Ulasan
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.app_review.index') }}">Daftar Ulasan</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Detil Ulasan
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
                            <div class="col-12 space-y-3">
                                <div class="row">
                                    <div class="col-12">
                                        <a href="{{ route('admin.app_review.index') }}" class="btn btn-dark">
                                            <i class="fa fa-arrow-left"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="type">Nama/No. Telp Customer</label>
                                    <div class="col-sm-6">
                                        <input type="text" id="type"
                                            class="form-control" value="{{ $userReview->user_name. ' - '. $userReview->user_phone }}" readonly/>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <span>Kode Booking</span>
                                    </div>
                                    <div class="col-sm-6">
                                        <a href="{{ route('admin.booking.show', ['id' => $userReview->booking_id]) }}">
                                            {{ $userReview->booking_header->code }}</a>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <span>Kode Transaksi</span>
                                    </div>
                                    <div class="col-sm-6">
                                        @if(!empty($trxCode))
                                            <a href="{{ $trxShowUrl }}">
                                                {{ $trxCode }}</a>
                                        @else
                                            <span>-</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="package">Treatment</label>
                                    <div class="col-sm-6">
                                        <input type="text" id="package"
                                            class="form-control" value="{{ $userReview->package_name }}" readonly/>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="partner">Therapis</label>
                                    <div class="col-sm-6">
                                        <input type="text" id="partner"
                                            class="form-control" value="{{ $userReview->partner_name. ' ('. $userReview->partner_alias. ')' }}" readonly/>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="star">Rating</label>
                                    <div class="col-sm-4">
                                        <div class="input-group">
                                            <span class="input-group-text">Bintang</span>
                                            <input type="text" id="star"
                                                class="form-control" value="{{ $userReview->star }}" readonly/>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <label class="col-form-label" for="description">Notes</label>
                                    </div>
                                    <div class="col-sm-6">
                                        <textarea id="description" name="description"
                                            class="form-control" rows="4"
                                            readonly>{{ $userReview->text ?? '' }}</textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <h5>Foto</h5>
                                    </div>
                                </div>
                                <div class="row">
                                    @if($userReview->user_review_images->count() > 0)
                                        @foreach ($userReview->user_review_images as $image)
                                            <div class="col-sm-3 col-12 text-center">
                                                <a href="{{ Illuminate\Support\Facades\Storage::disk('review_image')->url($image->image_filename) }}"
                                                    data-lightbox="img-1"
                                                    data-title="IMG 1">
                                                    <img src={{ Illuminate\Support\Facades\Storage::disk('review_image')->url($image->image_filename) }}
                                                        style="width: 150px; height:auto;"/>
                                                </a>
                                            </div>
                                        @endforeach
                                    @else
                                        <span>Tidak Ada Foto</span>
                                    @endif

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/lightbox2/lightbox.css') }}">
    <!-- Page JS Plugins CSS -->

@endsection

@section('scripts')
    <!-- jQuery -->
    <script type="text/javascript" src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('plugins/lightbox2/lightbox.js') }}"></script>
@endsection
