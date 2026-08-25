@extends('layouts.admin')

@section('title')
    <title>Laporan {{$type}}</title>
@endsection

@section('content')
    <!-- Hero -->
     <div>
        <div class="content space-top">
              <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                  <div class="flex-grow-1">
                      <h1 class="h3 fw-bold mb-2">
                          Laporan {{$type}}
                      </h1>
                  </div>
                  <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                      <ol class="breadcrumb breadcrumb-alt">
                          <li class="breadcrumb-item">
                              <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                          </li>
                          <li class="breadcrumb-item" aria-current="page">
                              Laporan {{$type}}
                          </li>
                      </ol>
                  </nav>
              </div>
          </div>
      </div>
    <!-- END Hero -->

    <div class="content">
        <form class="space-y-3" id="form_report_filter" method="POST" action="{{ route('admin.general_ledger.report.generate') }}">
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
                                <input type="hidden" value="{{$type}}" name="type">

                                @if($type == "Cashflow")
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="start_date">Tahun <span
                                                class="text-red">*</span></label>
                                        <div class="col-sm-4 col-12">
                                            <select class="form-control" name="select_year">
                                                @foreach($select_year as $year)
                                                    <option value="{{$year}}">{{$year}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <input type="hidden" name="start_date" value="{{$startDate}}"/>
                                    <input type="hidden" name="end_date" value="{{$endDate}}"/>
                                    @elseif($type == "Profit and Loss")
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label" for="select_year">Tahun <span
                                                        class="text-red">*</span></label>
                                            <div class="col-sm-4 col-12">
                                                <select class="form-control" name="select_year">
                                                    @foreach($select_year as $year)
                                                        <option value="{{$year}}">{{$year}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label" for="start_date">Bulan Mulai <span
                                                class="text-red">*</span></label>
                                            <div class="col-sm-4 col-12">
                                                <select class="form-control" name="start_date">
                                                    <option value="1">Januari</option>
                                                    <option value="2">Februari</option>
                                                    <option value="3">Maret</option>
                                                    <option value="4">April</option>
                                                    <option value="5">Mei</option>
                                                    <option value="6">Juni</option>
                                                    <option value="7">Juli</option>
                                                    <option value="8">Agustus</option>
                                                    <option value="9">September</option>
                                                    <option value="10">Oktober</option>
                                                    <option value="11">November</option>
                                                    <option value="12">Desember</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label" for="end_date">Bulan Berakhir <span
                                                        class="text-red">*</span></label>
                                            <div class="col-sm-4 col-12">
                                                <select class="form-control" name="end_date">
                                                    <option value="1">Januari</option>
                                                    <option value="2">Februari</option>
                                                    <option value="3">Maret</option>
                                                    <option value="4">April</option>
                                                    <option value="5">Mei</option>
                                                    <option value="6">Juni</option>
                                                    <option value="7">Juli</option>
                                                    <option value="8">Agustus</option>
                                                    <option value="9">September</option>
                                                    <option value="10">Oktober</option>
                                                    <option value="11">November</option>
                                                    <option value="12">Desember</option>
                                                </select>
                                            </div>
                                        </div>
                                    @else
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label" for="start_date">Tanggal Mulai <span
                                                        class="text-red">*</span></label>
                                            <div class="col-sm-4 col-12">
                                                <input id="start_date" type="text" class="js-flatpickr form-control"
                                                    data-date-format="d-m-Y" name="start_date"
                                                    value="{{ $startDate }}" required/>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label" for="end_date">Tanggal Berakhir <span
                                                        class="text-red">*</span></label>
                                            <div class="col-sm-4 col-12">
                                                <input id="end_date" type="text" class="js-flatpickr form-control"
                                                    data-date-format="d-m-Y" name="end_date"
                                                    value="{{ $endDate }}" required/>
                                            </div>
                                        </div>
                                    @endif
                                @if($type == "Ledger")
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="start_date">Pilih COA <span
                                                class="text-red">*</span></label>
                                        <div class="col-sm-4 col-12">
                                            <select id="coa" class="form-control form-control-select2" name="coa"></select>
                                        </div>
                                    </div>
                                @endif
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
                            <input type="submit" id="btn_submit" class="btn btn-success" value="Buat Laporan"/>&nbsp;&nbsp;
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
    <link rel="stylesheet" href="{{ asset('js/plugins/flatpickr/flatpickr.css') }}">
@endsection

@section('scripts')
    <!-- jQuery -->
    <script src="{{ asset('js/plugins/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('js/plugins/flatpickr/l10n/id.js') }}"></script>
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>

    <script>
        flatpickr('.js-flatpickr', {
            "locale": "id" // locale for this instance only
        });
        $('#coa').select2({
            placeholder: {
                id: '-1',
                text: ' - Pilih COA - '
            },
            width: '100%',
            minimumInputLength: 0,
            ajax: {
                url: '{{ route('admin.coa.select') }}',
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
    </script>
@endsection
