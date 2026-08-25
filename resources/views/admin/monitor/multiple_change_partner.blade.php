@extends('layouts.admin')

@section('title')
    <title>Mengganti Multiple Therapis</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Mengganti Multiple Therapis
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Mengganti Multiple Therapis
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <div class="content">
        <form class="space-y-3" id="form_report_filter" method="POST" action="{{ route('admin.monitor.change_partner_multiple_view_submit') }}">
            @csrf
            <div class="block block-rounded">
                <div class="block-content block-content-full">
                    <div class="row">
                        <div class="col-sm-6">
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
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="space-y-3">
                                <div class="row">
                                    <div class="col-12">
                                        <span style="color: red;">Isian dengan asterik (*) wajib diisi!</span>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-6">
                                        Nomor Therapis Pengganti
                                    </div>
                                    <div class="col-6">
                                        <input name="locker_id" value="{{ $locker_id }}" type="hidden"/>
                                        <input name="room_id" value="{{ $room_id }}" type="hidden"/>
                                        <select multiple id="change-partner-multiple" name="partner_ids[]" class="form-select form-control-select2">
                                            @foreach($selectedPartners as $partner)
                                                <option value="{{ $partner['id'] }}" selected>{{ $partner['text'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 text-start">
                            <button id="btn_loading" type="button" class="btn btn-success" style="display: none;">
                                <i class="spinner-border spinner-border-sm text-green" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </i>
                            </button>
                            <input type="submit" id="btn_submit" class="btn btn-success" value="Submit"/>&nbsp;&nbsp;
                            {{--                            <a id="btn_cancel" href="{{ route('invoices.index') }}" class="btn btn-danger">Batal</a> --}}
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
    <style>
        .select2-container--default .select2-selection--multiple .select2-selection__choice
        {
            color: black;
        }
    </style>
@endsection

@section('scripts')
    <!-- jQuery -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>

    <script>
        $('#change-partner-multiple').select2({
            placeholder: {
                id: '-1',
                text: '--- Pilih Pengganti Therapis ---'
            },
            width: '100%',
            minimumInputLength: 0,
            maximumSelectionLength: {{ $max }},
            ajax: {
                url: '{{ route('admin.partner.select_change') }}',
                dataType: 'json',
                data: function (params) {
                    return {
                        search: $.trim(params.term)
                    };
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                }
            }
        });
    </script>
@endsection
