@extends('layouts.admin')

@section('title')
    <title>Detil Treatment</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Detil Treatment
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
                            Detil Treatment
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
                                        <a href="{{ route('admin.package.index') }}" class="btn btn-dark">
                                            <i class="fa fa-arrow-left"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="type">Tipe Room</label>
                                    <div class="col-sm-6">
                                        <input type="text" id="type" class="form-control" value="{{ $package['room_type'] }}" readonly/>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <label class="col-form-label" for="name">Nama</label>
                                    </div>
                                    <div class="col-sm-6">
                                        <input type="text" id="name" name="name" class="form-control" placeholder="Nama Paket" value="{{ $package['name'] }}" readonly/>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <label class="col-form-label" for="duration">Durasi</label>
                                    </div>
                                    <div class="col-sm-6">
                                        <input type="number" min="0" max="300" id="duration" name="duration" class="form-control" value="{{ $package['duration'] }}" readonly/>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="price">Harga</label>
                                    <div class="col-sm-6">
                                        <vue-autonumeric :options="autonumericFormat" name="price" class="form-control" value="{{ $package['price'] }}" readonly></vue-autonumeric>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="partner_count">Jumlah Therapis</label>
                                    <div class="col-sm-6">
                                        <vue-autonumeric :options="autonumericFormatTime" id="partner_count" name="partner_count" class="form-control" value="{{ $package['partner_count'] }}" readonly></vue-autonumeric>
                                    </div>
                                </div>
{{--                                <div class="row">--}}
{{--                                    <label class="col-sm-3 col-form-label" for="additional_person_price">Biaya Tambahan Customer</label>--}}
{{--                                    <div class="col-sm-6">--}}
{{--                                        <vue-autonumeric :options="autonumericFormat" id="additional_person_price" name="additional_person_price" class="form-control" value="{{ $package['additional_person_price'] }}" readonly></vue-autonumeric>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                                <div class="row">--}}
{{--                                    <label class="col-sm-3 col-form-label" for="additional_partner_cost">Biaya Tambahan Therapis</label>--}}
{{--                                    <div class="col-sm-6">--}}
{{--                                        <vue-autonumeric :options="autonumericFormat" id="additional_partner_cost" name="additional_partner_cost" class="form-control" value="{{ $package['additional_partner_price'] }}" readonly></vue-autonumeric>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                                <div class="row">--}}
{{--                                    <label class="col-sm-3 col-form-label" for="extend_duration">Durasi Extend</label>--}}
{{--                                    <div class="col-sm-6">--}}
{{--                                        <vue-autonumeric :options="autonumericFormatTime" name="extend_duration" class="form-control" value="{{ $package['extend_duration'] }}" readonly></vue-autonumeric>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                                <div class="row">--}}
{{--                                    <label class="col-sm-3 col-form-label" for="extend_price">Biaya Extend</label>--}}
{{--                                    <div class="col-sm-6">--}}
{{--                                        <vue-autonumeric :options="autonumericFormat" id="extend_price" name="extend_price" class="form-control" value="{{ $package['extend_price'] }}" readonly></vue-autonumeric>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
                                 <div class="row">
                                     <div class="col-sm-3">
                                         <label class="col-form-label" for="description">Deskripsi</label>
                                     </div>
                                     <div class="col-sm-6">
                                         <textarea id="description" name="description" class="form-control" rows="4" maxlength="150" placeholder="Description" readonly>{{ $package['description'] }}</textarea>
                                     </div>
                                 </div>
                                 @php($selectedBadgeStatus = $package['badge_status'] ?? '')
                                 <div class="row">
                                     <label class="col-sm-3 col-form-label">Badge Status</label>
                                     <div class="col-sm-6">
                                         <div class="form-check">
                                             <input class="form-check-input" type="radio" name="badge_status" id="badge_status_none" value="" {{ $selectedBadgeStatus === '' ? 'checked' : '' }} disabled>
                                             <label class="form-check-label" for="badge_status_none">
                                                 None
                                             </label>
                                         </div>
                                         <div class="form-check">
                                             <input class="form-check-input" type="radio" name="badge_status" id="badge_status_recommended" value="recommended" {{ $selectedBadgeStatus === 'recommended' ? 'checked' : '' }} disabled>
                                             <label class="form-check-label" for="badge_status_recommended">
                                                 Recommended
                                             </label>
                                         </div>
                                         <div class="form-check">
                                             <input class="form-check-input" type="radio" name="badge_status" id="badge_status_best_seller" value="best_seller" {{ $selectedBadgeStatus === 'best_seller' ? 'checked' : '' }} disabled>
                                             <label class="form-check-label" for="badge_status_best_seller">
                                                 Best Seller
                                             </label>
                                         </div>
                                     </div>
                                 </div>
                                 <div class="row">
                                     <label class="col-sm-3 col-12 col-form-label" for="ktp">Foto
                                         <br>
                                         <img src="{{ $package['img'] }}" alt="Foto" class="img-thumbnail" style="max-width: 200px; max-height: 200px;"/>
                                    </label>
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
    <!-- Page JS Plugins CSS -->

@endsection

@section('scripts')
    <!-- jQuery -->
    <script type="text/javascript" src="{{ asset('js/lib/jquery.min.js') }}"></script>

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
    </script>
@endsection
