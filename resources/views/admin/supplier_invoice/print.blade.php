@extends('layouts.admin')

@section('title')
    <title>Print Supplier Invoice</title>
@endsection

@section('content')
<div class="container-fluid">
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
                                                    <a id="btn_cancel" href="{{ route('admin.supplier_invoice.index') }}" class="btn btn-outline-danger">Back</a>
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
                                                    <div style="font-size: 14px; line-height: 1.4;">
                                                        Alamat : {{ $branches->address }} {{ $branches->extra_text }}
                                                    </div>
                                                    <div style="font-size: 14px; margin-top: 5px;">
                                                        🌐 <a href="https://www.simi.co.id" target="_blank" style="text-decoration:none; color:black;">www.simi.co.id</a>
                                                        &nbsp; | No Telepon : {{ $branches->phone }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row text-center">
                                                <h2 class="text-decoration-underline fw-bold">Supplier Invoice</h2>
                                            </div>
                                            {{-- <table style="font-size: 14px;width: 100%">
                                                <tr>
                                                    <td width="20%">No Supplier Invoice</td>
                                                    <td>:</td>
                                                    <td style="font-weight: bold;">{{$data->code}}</td>
                                                </tr>
                                                <tr>
                                                    <td width="20%">Supplier</td>
                                                    <td>:</td>
                                                    <td>{{$data->supplier->name}}</td>
                                                    <td width="20%">Tanggal Dibutuhkan</td>
                                                    <td>:</td>
                                                    <td>{{ \Carbon\Carbon::parse($data->date)->format('d-m-Y') }}</td>
                                                </tr>
                                                <tr>
                                                    <td width="20%">Term Of Payment</td>
                                                    <td>:</td>
                                                    <td>{{$data->top}}</td>
                                                    <td width="20%">Transaction Type</td>
                                                    <td>:</td>
                                                    <td>{{$data->type}}</td>
                                                </tr>
                                                <tr>
                                                    <td width="20%">Deskripsi</td>
                                                    <td>:</td>
                                                    <td colspan="4">{{$data->description}}</td>
                                                </tr>
                                            </table> --}}

                                            <div class="row pb-3">
                                                <div class="col-6 ">
                                                    <div class="row">
                                                        <div class="col-5">
                                                            <p class="fw-bold">ID<span class="float-end">:</span></p>
                                                        </div>
                                                        <div class="col">
                                                            <p>{{ $data->code }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-5">
                                                            <p class="fw-bold">ID Pesanan<span class="float-end">:</span></p>
                                                        </div>
                                                        <div class="col">
                                                            <p>{{ implode(', ', array_unique(array_column($itemList, 'poCode'))) }}</p></div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-5">
                                                            <p class="fw-bold">Pembelian Dari<span class="float-end">:</span></p>
                                                        </div>
                                                        <div class="col">
                                                            <p>{{ $data->supplier_id }}/{{ $data->supplier->name }}</p>
                                                        </div>
                                                    </div>
                                                    {{-- <div class="row">
                                                        <div class="col-5">
                                                            <p class="fw-bold">Kategori<span class="float-end">:</span></p>
                                                        </div>
                                                        <div class="col">
                                                            <p>{{ implode(', ', array_unique(array_column($itemList, 'category'))) }}</p>
                                                        </div>
                                                    </div> --}}
                                                    <div class="row">
                                                        <div class="col-5">
                                                            <p class="fw-bold">ID Gudang<span class="float-end">:</span></p>
                                                        </div>
                                                        <div class="col">
                                                            {{-- <p>{{ $data->warehouse_id }}/{{ $data->warehouse_name }}</p> --}}
                                                            <p>18 / Gudang Sukabumi</p>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-5">
                                                            <p class="fw-bold">Tgl. Transaksi<span class="float-end">:</span></p>
                                                        </div>
                                                        <div class="col">
                                                            <p>{{ \Carbon\Carbon::parse($data->date)->format('d-M-Y') }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-5">
                                                            <p class="fw-bold">Deskripsi<span class="float-end">:</span></p>
                                                        </div>
                                                        <div class="col">
                                                            <p>{{ $data->description }}</p>
                                                        </div>
                                                    </div>
                                                </div>

                                            @if($data->type == "GR")
                                                {{-- <div class="row" style="font-size: 14px;">
                                                    <label class="col-sm-10 col-form-label" for="status">Daftar Good Receive</label>
                                                </div> --}}
                                                {{-- <div class="row">
                                                    <div class="col-sm-12">
                                                        <div class="table-responsive w-100">
                                                            <table class="table table-bordered" id="budget-detail-table" style="font-size: 12px; overflow: hidden;">
                                                                <tr style="font-size: 12px; font-weight: bold;">
                                                                    <th>No</th>
                                                                    <th>Good receive</th>
                                                                    <th>No Invoice</th>
                                                                    <th>Total</th>
                                                                    <th>Deskripsi</th>
                                                                </tr>
                                                                <tbody>
                                                                @php($i = 1)
                                                                @foreach ($itemList as $item)
                                                                    <tr>
                                                                        <td>{{$i}}</td>
                                                                        <td>
                                                                            <span> {{$item['grCode']}} </span>
                                                                        </td>
                                                                        <td>
                                                                            <span> {{$item['noInv']}} </span>
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
                                                </div> --}}

                                                <div class="row py-3">
                                                    <div class="col-12">
                                                        <table class="table">
                                                            <thead>
                                                            <tr class="text-center">
                                                                <th>No</th>
                                                                <th>ID Item</th>
                                                                <th>Reference</th>
                                                                <th>Jml.</th>

                                                                <th colspan="3">Amount</th>
                                                            </thead>
                                                            <tbody>
                                                            @php($i = 0)
                                                            @php($totalQty = 0)
                                                            @foreach($itemList as $item)
                                                                @php($i++)
                                                                <tr>
                                                                    <td>{{ $i }}</td>
                                                                    <td>
                                                                        {{-- {{ $item['itemId'] }}-{{ $item['itemName'] }} --}}
                                                                    </td>
                                                                    <td>{{ $item['poCode'] }}</td>
                                                                    <td class="text-end">
                                                                        {{ $item['qty'] }}
                                                                    </td>

                                                                    <td class="text-end amount-value" colspan="3">{{number_format($item['total'], 0, '.', '.' )}}</td>
                                                                </tr>
                                                                @php($totalQty += $item['qty'])
                                                            @endforeach
                                                            <tr>
                                                                <td style="border: none;"></td>
                                                                <td style="border: none;" class="fw-bold">Total Item</td>
                                                                <td style="border: none;">{{ $i }}</td>
                                                                <td class="text-end" style="border: none;">{{number_format($totalQty, 0, '.', '.' )}}</td>
                                                                <td class="text-end amount-value" colspan="3">{{number_format($data->grandtotal, 0, '.', '.' )}}</td>
                                                            </tr>
                                                        <tr >
                                                            <td style="border: none;"></td>
                                                            <td class="fw-bold" style="border: none;">Planning Delivery Date &nbsp;<span class="">:</span> &nbsp; {{ $data->delivery_date ? $data->delivery_date->format('d-M-Y') : \Carbon\Carbon::parse($data->date)->format('d-M-Y') }}</td>
                                                            <td style="border: none;" colspan="2">&nbsp;</td>
                                                            <td class="fw-bold">DISC</td>
                                                            <td class="text-end amount-value">Rp {{number_format($ppn_value, 0, '.', '.' )}}</td>
                                                        </tr>
                                                        <tr >
                                                            <td style="border: none;"></td>
                                                                <td class="fw-bold" style="border: none;">
                                                                    Payment Term &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                                    <span class="">:</span> &nbsp;
                                                                    {{-- {{ \App\Libs\Utilities::topValue($data->top) }} --}}
                                                                </td>
                                                            <td style="border: none;" colspan="2">&nbsp;</td>
                                                            <td class="fw-bold">PPN</td>
                                                            <td class="text-end amount-value">Rp {{number_format($ppn_value, 0, '.', '.' )}}</td>
                                                        </tr>
                                                            <tr >
                                                                <td style="border: none;"></td>
                                                                <td class="fw-bold" style="border: none;">
                                                                    Payment Date &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                                    <span class="">:</span> &nbsp; {{ $data->payment_date ? $data->payment_date->format('d-M-Y') : \Carbon\Carbon::parse($data->date)->format('d-M-Y') }}
                                                                </td>
                                                            <td style="border: none;" colspan="2"></td>
                                                                <td class="fw-bold">TOTAL IDR</td>
                                                                <td class="text-end amount-value" colspan="2">Rp {{number_format($grandtotal, 0, '.', '.' )}}</td>
                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>

                                            @endif
                                            @if($data->type == "NONGR")
                                                <div class="row" style="font-size: 14px;">
                                                    <label class="col-sm-10 col-form-label" for="status">Daftar COA</label>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <div class="table-responsive w-100">
                                                            <table class="table table-bordered" id="budget-detail-table" style="font-size: 12px; overflow: hidden;">
                                                                <tr style="font-size: 12px; font-weight: bold;">
                                                                    <th>No</th>
                                                                    <th>COA</th>
                                                                    <th>Total</th>
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
                                                                            <span>Rp {{number_format($item['total'], 0, '.', '.' )}} </span>
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
                                            @endif

                                            <div class="row gx-5">
                                                <div class="col-4 text-center">
                                                    <p class="fw-bold" style="height: 100px;">Dibuat Oleh</p>
                                                    <p class="bb w-100">{{ $createdBy }}</p>
                                                </div>
                                                <div class="col-4 text-center">
                                                    <p class="fw-bold" style="height: 100px;">Disetujui Oleh</p>
                                                    <p class="bb w-100">&nbsp;</p>
                                                </div>
                                                <div class="col-4 text-center">
                                                    <p class="fw-bold" style="height: 100px;">Diterima Oleh</p>
                                                    <p class="bb w-100">&nbsp;</p>
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
        .container-fluid{
            width: 75% !important;
        }
        body{
            color: black !important;
        }
        .f-banner{
            font-size: 15px;
        }
        .block-rounded{
            box-shadow: none !important;
        }
        p{
            margin: 0 !important;
        }

        .bt{
            border-top: 2px solid black;
        }
        .bb{
            border-bottom: 1px solid black;
        }

        tr th{
            vertical-align: middle;
            font-weight: bold;
        }
        tr th,tr td{
            font-size: 14px;
            border: 1px solid black;
        }
        p,span{
            font-size: 16px;
        }
        table{
            border-top: 2px solid black !important;
            border-collapse: collapse;
        }
        table>:not(:first-child) {
            border-top: 2px solid black !important;
        }
        table>:not(:last-child)>:last-child>* {
            border-bottom-color: black !important;
        }

        tr.amount td{
            border: none !important;
        }

        .table>:not(caption)>*>* {
            padding: .3rem .5rem;
        }
        .note p, .note ol li{
            font-size: 12px;
        }
        h2{
            margin-bottom: 0rem !important;
        }
        .space-y-3>*+* {
            margin-top: 1rem !important;
        }
        .table>:not(caption)>*>* {
            padding: .3rem .5rem !important;
        }


        @media print {
            .print_show{
                display: block;
            }

            .print_hide {
                display: none;
            }
            .container-fluid{
                width: 100% !important;
            }
            .f-banner{
                font-size: 14px;
            }
            p
            tr th,tr td{
                font-size: 12px;
            }
            p, span{
                font-size: 12px;
            }
            h2, h3{
                font-size: 18px;
                margin-bottom: :0.5rem;
            }
            h5{
                font-size: 13px;
            }
            .note p, .note ol li{
                font-size: 8px;
            }
            .content, .block-content{
                padding: 0 !important;
                margin: 0 !important;
            }
            .space-y-3>*+* {
                margin-top: 0.5rem !important;
            }
            .table>:not(caption)>*>* {
                padding: .3rem .5rem !important;
            }
        }

        .main_content, body {
            color: #000 !important;
        }
        table {
            color: #000 !important;
        }
    </style>

    {{-- <style type="text/css" media="print">
        @page { size: landscape; }
    </style> --}}
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
