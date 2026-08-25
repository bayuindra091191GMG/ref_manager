@extends('layouts.admin')

@section('title')
    <title>Print Bank Cash Out</title>
@endsection

@section('content')
    <div class="content" id="main-content">
        <div class="row items-push">
            <div class="col-md-12 col-xl-12">
                <div class="block block-rounded h-100 mb-0">
                    <div class="block-content block-content-full">
                        <div class="row">
                            <div class="col-12">
                                <div>
                                    <div class="row mt-3" id="main_content">
                                        <div class="col-12 space-y-3">
                                            <div class="row print_hide d-print-none">
                                                <div class="col-12 text-end">
                                                    <button type="button" id="btn_print" class="btn btn-outline-info print_hide">Print</button>
                                                    <a id="btn_cancel" href="{{ route('admin.bank_cash_out.index') }}" class="btn btn-outline-danger">Back</a>
                                                </div>
                                            </div>
                                            <div class="row align-items-center" style="border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 10px;">
                                                <div class="col-2 text-center">
                                                    <img src="{{ asset('images/logo-only.png') }}" alt="Logo" style="width: 90px;">
                                                </div>
                                                <div class="col-10 text-center">
                                                    <h4 class="fw-bold mb-1" style="font-family: 'Times New Roman', serif;">
                                                        CV. Simi Family Reflexology
                                                    </h4>
                                                    <div style="font-size: 15px; line-height: 1.4;">
                                                        Alamat : {{ $branches->address }} {{ $branches->extra_text }}
                                                    </div>
                                                    <div style="font-size: 15px; margin-top: 5px;">
                                                        🌐 <a href="https://www.simi.co.id" target="_blank" style="text-decoration:none; color:black;">www.simi.co.id</a>
                                                        &nbsp; | No Telepon : {{ $branches->phone }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row text-center">
                                                <h2>Bank Cash Out</h2>
                                            </div>
                                            <table style="font-size: 12px;width: 100%; font-weight: bold;">
                                                <tr>
                                                    <td width="20%">No Bank Cash Out</td>
                                                    <td>:</td>
                                                    <td style="font-weight: bold;">{{$data->code}}</td>
                                                    <td width="20%">Tanggal</td>
                                                    <td>:</td>
                                                    <td >{{\Carbon\Carbon::parse($data->payment_date)->format('d-m-Y')}}</td>
                                                </tr>
                                                <tr>
                                                    <td width="20%"></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td width="20%">Status</td>
                                                    <td>:</td>
                                                    <td>{{ (string) $data->status_id === '1' ? 'Open' : 'Approved' }}</td>
                                                </tr>
                                                {{-- <tr>
                                                    <td width="20%">COA Cash / Bank</td>
                                                    <td>:</td>
                                                    <td colspan="4">{{$data->coa_bank_code}} - {{$data->coa_bank_name}}</td>
                                                </tr> --}}
                                                <tr>
                                                    <td width="20%">Referensi</td>
                                                    <td>:</td>
                                                    <td colspan="4">{{$data->reference ?? ''}}</td>
                                                </tr>
                                                <tr>
                                                    <td width="20%">Deskripsi</td>
                                                    <td>:</td>
                                                    <td colspan="4">{{$data->description}}</td>
                                                </tr>
                                            </table>

                                            {{-- <div class="row" style="font-size: 14px;">
                                                <label class="col-sm-10 col-form-label" for="status">Product List</label>
                                            </div> --}}
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <div class="table-responsive w-100">
                                                        <table class="table table-bordered" id="budget-detail-table"
                                                            style="font-size: 12px; overflow: hidden;border: 1px solid black;">
                                                            <tr style="font-size: 12px; font-weight: bold; border: 1px solid black;">
                                                                <th>Nama COA</th>
                                                                <th>Kode COA</th>
                                                                <th>Deskripsi</th>
                                                                <th>Debit</th>
                                                                <th>Credit</th>
                                                            </tr>
                                                            <tbody>
                                                            @php($i = 1)
                                                            @foreach ($itemList as $item)
                                                                <tr>
                                                                    <td>
                                                                        <span> {{$item['coaName']}} </span>
                                                                    </td>
                                                                    <td>
                                                                        <span> {{$item['coaCode']}} </span>
                                                                    </td>
                                                                    <td>
                                                                        <span> {{$item['desc']}} </span>
                                                                    </td>
                                                                    <td style="text-align: right;">
                                                                        <span> {{number_format($item['debit'] ?? 0, 0, '.', '.' )}} </span>
                                                                    </td>
                                                                    <td style="text-align: right;">
                                                                        <span> {{number_format($item['credit'] ?? 0, 0, '.', '.' )}} </span>
                                                                    </td>
                                                                </tr>
                                                                @php($i++)
                                                            @endforeach
                                                                <tr>
                                                                    <td>
                                                                        <span> {{$data->coa_bank_name}} </span>
                                                                    </td>
                                                                    <td>
                                                                        <span> {{$data->coa_bank_code}} </span>
                                                                    </td>
                                                                    <td>
                                                                        <span> {{$data->description}} </span>
                                                                    </td>
                                                                    <td style="text-align: right;">
                                                                        <span> 0 </span>
                                                                    </td>
                                                                    <td style="text-align: right;">
                                                                        <span> {{number_format($data->total_price, 0, '.', '.' )}} </span>
                                                                    </td>
                                                                </tr>
                                                                <tr style="font-weight: bold;">
                                                                    <td colspan="3" style="text-align: right;">Total</td>
                                                                    <td style="text-align: right;">
                                                                        <span> {{number_format($totalD, 0, '.', '.' )}} </span>
                                                                    </td>
                                                                    <td style="text-align: right;">
                                                                        <span> {{number_format($data->total_price + $totalK, 0, '.', '.' )}} </span>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row pt-2">
                                                <div class="col-12">
                                                    <div class="row g-5">
                                                        <div class="col text-center">
                                                            <p class="pb-5 fw-bold">Dibuat Oleh</p>
                                                            <p style="width: 100%;height:30px;">({{ $createdBy }})</p>
                                                        </div>
                                                        <div class="col text-center">
                                                            <p class="pb-5 fw-bold">Mengetahui</p>
                                                            <p style="border-bottom: 1px solid black; width: 100%;height:30px;"><span class="float-start">(</span><span class="float-end">)</span></p>
                                                        </div>
                                                        <div class="col text-center">
                                                            <p class="pb-5 fw-bold">Menyetujui</p>
                                                            <p style="border-bottom: 1px solid black; width: 100%;height:30px;"><span class="float-start">(</span><span class="float-end">)</span></p>
                                                        </div>
                                                        <div class="col text-center">
                                                            <p class="pb-5 fw-bold">Diterima</p>
                                                            <p style="border-bottom: 1px solid black; width: 100%;height:30px;"><span class="float-start">(</span><span class="float-end">)</span></p>
                                                        </div>
                                                        <div class="col text-center">
                                                            <p class="pb-5 fw-bold">Ditransfer</p>
                                                            <p style="border-bottom: 1px solid black; width: 100%;height:30px;"><span class="float-start">(</span><span class="float-end">)</span></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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
    {{--    <link rel="stylesheet" href="{{ asset('custom/summernote/summernote-bs4.css') }}">--}}
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('custom/bootstrap-fileinput/css/fileinput.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('js/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}">

    <style>
        @media print {
            .print_show{
                display: block;
            }

            .print_hide {
                display: none;
            }
        }
        #main-content{
            color: #000 !important;
        }
        .main_content {
            color: #000 !important;
        }
        .table-bordered {
            color: #000 !important;
            border-collapse: collapse !important;
            border: 1px solid #000 !important;
        }
        .table-bordered th,
        .table-bordered td {
            border: 1px solid #000 !important;
            padding: .35rem .35rem !important;
        }
    </style>
@endsection

@section('scripts')
    <!-- jQuery -->
    <script type="text/javascript" src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>
    {{--    <script type="text/javascript" src="{{ asset('custom/summernote/summernote-bs4.min.js') }}"></script>--}}
    <script type="text/javascript" src="{{ asset('custom/bootstrap-fileinput/js/fileinput.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>

    <script type="text/javascript" src="{{ asset('custom/dist/bootstrap-tagsinput.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            $(window).keydown(function (event) {
                if (event.keyCode === 13) {
                    event.preventDefault();
                    return false;
                }
            });
        });

        $(document).on('click', '#btn_print', function(){
            $('.print_show').show();
            window.print();
        });
        window.onafterprint = function(){
            $('.print_show').hide();
        }
    </script>
@endsection
