@extends('layouts.admin')

@section('title')
    <title>Print Acc Payment</title>
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
                                                    <button type="button" id="btn_print" class="btn btn-outline-info">Print</button>
                                                    <a id="btn_cancel" href="{{ route('admin.ap.index') }}" class="btn btn-outline-danger">Back</a>
                                                </div>
                                            </div>
                                            <div class="row text-center">
                                                <h2>Acc Payment</h2>
                                            </div>
                                            <table style="font-size: 12px;width: 100%">
                                                <tr>
                                                    <td width="20%">No Acc Payment</td>
                                                    <td>:</td>
                                                    <td style="font-weight: bold;">{{$data->code}}</td>
                                                </tr>
                                                <tr>
                                                    <td width="20%">Supplier</td>
                                                    <td>:</td>
                                                    <td>{{$data->supplier->name}}</td>
                                                    <td width="20%">Tanggal Dibutuhkan</td>
                                                    <td>:</td>
                                                    <td>{{\Carbon\Carbon::parse($data->date)->format('d-m-Y')}}</td>
                                                </tr>
                                                <tr>
                                                    <td width="20%">Deskripsi</td>
                                                    <td>:</td>
                                                    <td colspan="4">{{$data->description}}</td>
                                                </tr>
                                            </table>

                                                <div class="row" style="font-size: 12px;">
                                                    <label class="col-sm-10 col-form-label" for="status">Daftar Good Receive</label>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <div class="table-responsive w-100">
                                                            <table class="table table-bordered" id="budget-detail-table" style="font-size: 9px; overflow: hidden;">
                                                                <tr style="font-size: 9px; font-weight: bold;">
                                                                    <th>No</th>
                                                                    <th>Supp Invoice</th>
                                                                    <th>Total</th>
                                                                    <th>Deskripsi</th>
                                                                </tr>
                                                                <tbody>
                                                                @php($i = 1)
                                                                @foreach ($itemList as $item)
                                                                    <tr>
                                                                        <td>{{$i}}</td>
                                                                        <td>
                                                                            <span> {{$item['suppInvCode']}} </span>
                                                                        </td>
                                                                        <td style="text-align: right;">
                                                                            <span> Rp {{number_format($item['total'], 0, '.', '.' )}} </span>
                                                                        </td>
                                                                        <td>
                                                                            <span> {{$item['desc']}} </span>
                                                                        </td>
                                                                    </tr>
                                                                    @php($i++)
                                                                @endforeach
                                                                <tr>
                                                                    <td colspan="3" style="text-align: right;">Sub Total:</td>
                                                                    <td style="text-align: right;">Rp {{number_format($data->grandtotal, 0, '.', '.' )}}</td>
                                                                </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row" style="font-size: 12px;">
                                                    <label class="col-sm-10 col-form-label" for="status">Daftar COA</label>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <div class="table-responsive w-100">
                                                            <table class="table table-bordered" id="budget-detail-table" style="font-size: 9px; overflow: hidden;">
                                                                <tr style="font-size: 9px; font-weight: bold;">
                                                                    <th>No</th>
                                                                    <th>COA</th>
                                                                    <th>Debit</th>
                                                                    <th>Kredit</th>
                                                                    <th>Deskripsi</th>
                                                                </tr>
                                                                <tbody>
                                                                @php($i = 1)
                                                                @foreach ($itemList2 as $item)
                                                                    <tr>
                                                                        <td>{{$i}}</td>
                                                                        <td>
                                                                            <span> {{$item['coaCode']}} </span>
                                                                        </td>
                                                                        <td style="text-align: right;">
                                                                            <span>Rp {{number_format($item['debit'], 0, '.', '.' )}} </span>
                                                                        </td>
                                                                        <td style="text-align: right;">
                                                                            <span>Rp {{number_format($item['credit'], 0, '.', '.' )}} </span>
                                                                        </td>
                                                                        <td>
                                                                            <span> {{$item['desc']}} </span>
                                                                        </td>
                                                                    </tr>
                                                                    @php($i++)
                                                                @endforeach
                                                                <tr>
                                                                    <td colspan="3" style="text-align: right;">Sub Total:</td>
                                                                    <td style="text-align: right;">Rp {{number_format($data->grandtotal, 0, '.', '.' )}}</td>
                                                                </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>

                                            <table width="100%">
                                                <tr><td colspan="5"><br /><br /><br /><br /></td></tr>
                                                <tr>
                                                    <td style="text-align:center;" width="20%"><span style="border-bottom: 1px solid black;">(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</span></td>
                                                    <td style="text-align:center;" width="20%">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                                    <td style="text-align:center;" width="20%">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                                    <td style="text-align:center;" width="20%">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                                    <td style="text-align:center;" width="20%"><span style="border-bottom: 1px solid black;">(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</span></td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align:center;" width="20%">Pembuat</td>
                                                    <td style="text-align:center;" width="20%"></td>
                                                    <td style="text-align:center;" width="20%"></td>
                                                    <td style="text-align:center;" width="20%"></td>
                                                    <td style="text-align:center;" width="20%">Penanggung Jawab</td>
                                                </tr>
                                            </table>
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
    </style>

    <style type="text/css" media="print">
        @page { size: landscape; }
    </style>
@endsection

@section('scripts')
    <!-- jQuery -->
    <script type="text/javascript" src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>
    {{--    <script type="text/javascript" src="{{ asset('custom/summernote/summernote-bs4.min.js') }}"></script>--}}
    <script type="text/javascript" src="{{ asset('custom/bootstrap-fileinput/js/fileinput.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>

    <script type="text/javascript" href="{{ asset('custom/dist/bootstrap-tagsinput.min.js') }}"></script>
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
