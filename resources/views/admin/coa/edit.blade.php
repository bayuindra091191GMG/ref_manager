@extends('layouts.admin')

@section('title')
    <title>Ubah COA</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Edit COA
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.coa.index') }}">Index COA</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Edit COA
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
                                <form class="space-y-3" id="form_edit_department" method="POST" action="{{ route('admin.coa.update') }}">
                                    @csrf
                                    <div class="row">
                                        <div class="col-12 text-end">
                                            <button id="btn_loading" type="button" class="btn btn-outline-success" style="display: none;">
                                                <i class="spinner-border spinner-border-sm text-green" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </i>
                                            </button>
                                            <input type="submit" id="btn_submit" class="btn btn-outline-success" value="Simpan"/>&nbsp;&nbsp;
                                            <a id="btn_cancel" href="{{ route('admin.coa.index') }}" class="btn btn-outline-danger">Batal</a>
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
                                    <input id="id" type="hidden" class="form-control" name="id" value="{{ $coa->id }}">
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="code">Kode</label>
                                        <div class="col-sm-4">
                                            <input id="code" type="text" class="form-control"
                                                   name="code" value="{{ $coa->code }}" placeholder="Code" >
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="name">Parent</label>
                                        <div class="col-sm-4">
                                            <select id="parent" class="form-control form-control-select2" name="parent">
                                                <option value="{{  $coa->parent_id }}" selected>{{  $coa->parent_code }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="name">Nama</label>
                                        <div class="col-sm-4">
                                            <input id="name" type="text" class="form-control"
                                                   oninput="this.value = this.value.toUpperCase()"
                                                   name="name" value="{{ $coa->name }}" placeholder="Name" >
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="name">Tipe *</label>
                                        <div class="col-sm-4">
                                            <select id="type" name="type" class="form-select form-control-select2">
                                                @foreach($coaTypes as $coatype)
                                                    <option value="{{$coatype->id}}" @if($coatype->id == $coa->type_id) selected @endif>{{$coatype->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="description">Deskripsi</label>
                                        <div class="col-sm-4">
                                            <textarea id="description" type="text" class="form-control"
                                                   name="description" rows="4" cols="50">{{ $coa->description }}</textarea>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="name">Report</label>
                                        <div class="col-sm-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="1" id="cashflow" name="cashflow" @if($coa->cashflow == 1) checked @endif>
                                                <label class="form-check-label" for="cashflow">
                                                    Arus Kas
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="1" id="profit_loss" name="profit_loss" @if($coa->profit_loss == 1) checked @endif>
                                                <label class="form-check-label" for="profit_loss">
                                                    Laba Rugi
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="1" id="balance_sheet" name="balance_sheet" @if($coa->balance_sheet == 1) checked @endif>
                                                <label class="form-check-label" for="balance_sheet">
                                                    Neraca
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="1" id="trial_balance" name="trial_balance" @if($coa->trial_balance == 1) checked @endif>
                                                <label class="form-check-label" for="trial_balance">
                                                    Neraca Saldo
                                                </label>
                                            </div>
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
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-buttons-bs5/css/buttons.bootstrap5.min.css') }}">
@endsection

@section('scripts')
    <!-- jQuery -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>
    <script>
        $('#form_edit_department').submit(function(){
            $('#btn_submit').hide();
            $('#btn_cancel').hide();
            $('#btn_loading').show(200);
        })
    </script>
    <script type="text/javascript">
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        $('#parent').select2({
            placeholder: {
                id: '-1',
                text: 'Pilih Parent COA...'
            },
            width: '100%',
            minimumInputLength: 0,
            ajax: {
                url: '{{ route('admin.coa.selectParent') }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        _token: CSRF_TOKEN,
                        search: params.term // search term
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
