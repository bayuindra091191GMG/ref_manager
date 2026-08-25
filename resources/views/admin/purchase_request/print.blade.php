@extends('layouts.admin')

@section('title')
    <title>Print Purchase Request</title>
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

                                    <div class="row mt-3" id="main_content">
                                        <div class="col-12 space-y-3">
                                            <div class="row print_hide d-print-none">
                                                <div class="col-12 text-end">
                                                    <button type="button" id="btn_print" class="btn btn-outline-info">Print</button>
                                                    <a id="btn_cancel" href="{{ route('admin.purchase_request.index') }}" class="btn btn-outline-danger">Back</a>
                                                </div>
                                            </div>
                                            <div class="row align-items-end" style="border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 10px;">
                                                <div class="col-2 text-end">
                                                    <img src="{{ asset('images/logo-only.png') }}" alt="Logo" style="width: 90px;">
                                                </div>
                                                <div class="col-10 text-left">
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
                                                <div class="col-12">
                                                    <h2 class="fw-bold text-black">Purchase Request Slip</h2>
                                                </div>

                                            </div>
                                            {{-- <table style="font-size: 14px;width: 100%">
                                                <tr>
                                                    <td width="25%">No Purchase Request</td>
                                                    <td>:</td>
                                                    <td style="font-weight: bold;">{{$data->code}}</td>
                                                </tr>
                                                <tr>
                                                    <td width="25%">Gudang</td>
                                                    <td>:</td>
                                                    <td>{{$data->warehouse->name}}</td>
                                                </tr>
                                                <tr>
                                                    <td width="25%">Tanggal Dibutuhkan</td>
                                                    <td>:</td>
                                                    <td>{{ \Carbon\Carbon::parse($data->required_date)->format('d-m-Y') }}</td>
                                                </tr>
                                                <tr>
                                                    <td width="25%">Deskripsi</td>
                                                    <td>:</td>
                                                    <td>{{$data->description}}</td>
                                                </tr>
                                            </table> --}}

                                            <div class="row ">
                                                <div class="col-12">
                                                    <div class="row">
                                                        <div class="col-3">
                                                            <p class="fw-bold">ID<span class="float-end">:</span></p>
                                                        </div>
                                                        <div class="col">
                                                            <p>{{ $data->code }}</p>
                                                        </div>
                                                    </div>
                                                    {{-- <div class="row">
                                                        <div class="col-3">
                                                            <p class="fw-bold">Tgl. Transaksi<span class="float-end">:</span></p>
                                                        </div>
                                                        <div class="col">
                                                            {{-- <p>{{ $data->required_date->format('d-M-Y') }}</p> --}
                                                        </div>
                                                    </div> --}}
                                                    <div class="row">
                                                        <div class="col-3">
                                                            <p class="fw-bold">Gudang<span class="float-end">:</span></p>
                                                        </div>
                                                        <div class="col">
                                                            <p>{{$data->warehouse->name}}</p>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-3">
                                                            <p class="fw-bold">Kategori<span class="float-end">:</span></p>
                                                        </div>
                                                        <div class="col">
                                                            {{-- <p>{{ implode(', ', array_unique(array_column($itemList, 'category'))) }}</p> --}}
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-3">
                                                            <p class="fw-bold">Deskripsi<span class="float-end">:</span></p>
                                                        </div>
                                                        <div class="col">
                                                            <p>{{ $data->description }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-3">
                                                            <p class="fw-bold">Plan. Delivery Date<span class="float-end">:</span></p>
                                                        </div>
                                                        <div class="col">
                                                            <td><p>{{ \Carbon\Carbon::parse($data->required_date)->format('d-m-Y') }}</p></td>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- <div class="row" style="font-size: 14px;">
                                                <label class="col-sm-10 col-form-label" for="status">Product List</label>
                                            </div> --}}
                                            {{-- <div class="row">
                                                <div class="col-sm-12">
                                                    <div class="table-responsive w-100">
                                                        <table class="table table-bordered" id="budget-detail-table" style="font-size: 12px; overflow: hidden;">
                                                            <tr style="font-size: 12px; font-weight: bold;">
                                                                <th>No</th>
                                                                <th>Nama Produk</th>
                                                                <th>Unit</th>
                                                                <th>Qty</th>
                                                            </tr>
                                                            <tbody>
                                                            @php($i = 1)
                                                            @php($totalQty = 0)
                                                            @php($subTotal = 0)
                                                            @foreach ($itemList as $item)
                                                                <tr>
                                                                    <td>{{$i}}</td>
                                                                    <td>
                                                                        <span> {{$item['itemName']}} </span>
                                                                    </td>
                                                                    <td>
                                                                        <span> {{$item['unitName']}} </span>
                                                                    </td>
                                                                    <td style="text-align: right;">
                                                                        <span> {{number_format($item['qty'], 1, ',', '.' )}} </span>
                                                                    </td>
                                                                </tr>
                                                                @php($i++)
                                                                @php($totalQty += $item['qty'])
                                                            @endforeach
                                                            <tr>
                                                                <td colspan="3" style="text-align: right;">Sub Total:</td>
                                                                <td style="text-align: right;"> {{number_format($totalQty, 1, ',', '.' )}}</td>
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
                                                        <tr>
                                                            <th>No.</th>
                                                            <th>ID Item</th>
                                                            <th colspan="3">Nama Item</th>
                                                            <th>UOM</th>
                                                            <th class="text-end">Jml</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        @php($i = 0)
                                                        @php($totalQty = 0)
                                                        @foreach($itemList as $item)
                                                            <tr>
                                                                @php($i++)
                                                                <td>{{ $i }}</td>
                                                                <td>{{ $item['itemId'] }}</td>
                                                                <td colspan="3">{{ $item['itemName'] }}</td>
                                                                <td>{{ $item['unitName'] }}</td>
                                                                <td class="text-end">{{number_format($item['qty'], 0, '.', '.' )}}</td>
                                                                @php($totalQty += $item['qty'])
                                                            </tr>
                                                        @endforeach
                                                        </tbody>
                                                        <tr class="amount">
                                                            <td class="text-end" colspan="3">Tgl yg Diinginkan</td>
                                                            {{-- <td>{{ $data->required_date->format('d-M-Y') }}</td> --}}
                                                            <td class="fw-bold text-end">Total Item:</td>
                                                            <td>{{number_format($i, 0, '.', '.' )}}</td>
                                                            <td class="fw-bold text-end">Total Qty:</td>
                                                            <td class="text-end">{{number_format($totalQty, 0, '.', '.' )}}</td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="row g-5">
                                                        <div class="col-4 text-center">
                                                            <p class="pb-5 fw-bold">Dibuat Oleh</p>
                                                            <p style="border-bottom: 1px solid black; width: 100%;height:25px;">{{ $createdBy }}</p>
                                                        </div>
                                                        <div class="col-4 text-center">
                                                            <p class="pb-5 fw-bold">Disetujui Oleh</p>
                                                            <p style="border-bottom: 1px solid black; width: 100%;height:25px;"></p>
                                                        </div>
                                                        <div class="col-4 text-center">
                                                            <p class="pb-5 fw-bold">Diterima Oleh</p>
                                                            <p style="border-bottom: 1px solid black; width: 100%;height:25px;"></p>
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
        .container-fluid{
            width: 75% !important;
        }
        body{
            color: black !important;
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
