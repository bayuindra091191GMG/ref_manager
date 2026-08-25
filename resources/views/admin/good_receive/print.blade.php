@extends('layouts.admin')

@section('title')
    <title>Print Good Receive</title>
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
                                                    <a id="btn_cancel" href="{{ route('admin.good_receive.index') }}" class="btn btn-outline-danger">Kembali</a>
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
                                                <h2 class="text-decoration-underline fw-bold">GOOD RECEIVE</h2>
                                            </div>
                                            {{-- <table style="font-size: 12px;width: 100%">
                                                <tr>
                                                    <td width="20%">No Good Receive</td>
                                                    <td>:</td>
                                                    <td style="font-weight: bold;">{{$data->code}}</td>
                                                    <td width="20%">No Purchase Request</td>
                                                    <td>:</td>
                                                    <td>{{$data->po_code}}</td>
                                                </tr>
                                                <tr>
                                                    <td width="20%">Supplier</td>
                                                    <td>:</td>
                                                    <td>{{$supplierName}}</td>
                                                    <td width="20%">Gudang</td>
                                                    <td>:</td>
                                                    <td>{{$warehouseName}}</td>
                                                </tr>
                                                <tr>
                                                    <td width="20%">Tanggal Dibutuhkan</td>
                                                    <td>:</td>
                                                    <td>{{ \Carbon\Carbon::parse($data->date)->format('d-m-Y') }}</td>
                                                </tr>
                                                <tr>
                                                    <td width="20%">Deskripsi PO</td>
                                                    <td>:</td>
                                                    <td>{{$poDescription}}</td>
                                                </tr>
                                                <tr>
                                                    <td width="20%">Deskripsi</td>
                                                    <td>:</td>
                                                    <td>{{$data->description}}</td>
                                                </tr>
                                            </table> --}}

                                            <div class="row pb-3">
                                                <div class="col-7">
                                                    <div class="row">
                                                        <div class="col-4">
                                                            <p class="fw-bold">Receive Id<span class="float-end">:</span></p>
                                                        </div>
                                                        <div class="col">
                                                            <p>{{ $data->code }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-4">
                                                            <p class="fw-bold">PO Id<span class="float-end">:</span></p>
                                                        </div>
                                                        <div class="col">
                                                            <p>{{ $data->po_code }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-4">
                                                            <p class="fw-bold">Dari<span class="float-end">:</span></p>
                                                        </div>
                                                        <div class="col">
                                                            {{-- <p>{{ $supplierCode }}</p> --}}
                                                            <p>{{ $supplierName }}</p>
                                                        </div>
                                                    </div>
                                                    {{-- <div class="row">
                                                        <div class="col-4">
                                                            <p class="fw-bold">Kategori<span class="float-end">:</span></p>
                                                        </div>
                                                        <div class="col">
                                                            <p>{{ implode(', ', array_unique(array_column($itemList, 'categoryName'))) }}</p>
                                                        </div>
                                                    </div> --}}
                                                    <div class="row">
                                                        <div class="col-4">
                                                            <p class="fw-bold">Description<span class="float-end">:</span></p>
                                                        </div>
                                                        <div class="col">
                                                            <p>{{ $data->description }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-5">
                                                    <div class="row">
                                                        <div class="col-4">
                                                            <p class="fw-bold">ID Gudang<span class="float-end">:</span></p>
                                                        </div>
                                                        <div class="col">
                                                            <p>{{ $warehouseId }} / {{ $warehouseName }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-4">
                                                            <p class="fw-bold">Tgl. Transaksi<span class="float-end">:</span></p>
                                                        </div>
                                                        <div class="col">
                                                            <p>{{ \Carbon\Carbon::parse($data->date)->format('d-M-Y') }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-4">
                                                            <p class="fw-bold">UserId<span class="float-end">:</span></p>
                                                        </div>
                                                        <div class="col">
                                                            <p>{{ $createdBy }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-4">
                                                            <p class="fw-bold">Status<span class="float-end">:</span></p>
                                                        </div>
                                                        <div class="col">
                                                            <p>{{ $data->status->display }}</p>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>

                                            {{-- <div class="row">
                                                <div class="col-sm-12">
                                                    <div class="table-responsive w-100">
                                                        <table class="table table-bordered" id="budget-detail-table" style="font-size: 9px; overflow: hidden;">
                                                            <tr style="font-size: 9px; font-weight: bold;">
                                                                <th>No</th>
                                                                <th>Nama Produk</th>
                                                                <th>Unit</th>
                                                                <th>Qty PO</th>
                                                                <th>Qty Sudah GR</th>
                                                                <th>Qty</th>
                                                            </tr>
                                                            <tbody>
                                                            @php($i = 1)
                                                            @php($totalQty = 0)

                                                            @foreach ($itemList as $item)
                                                                <tr>
                                                                    <td>{{$i}}</td>
                                                                    <td>
                                                                        <span> {{$item['itemName']}}} </span>
                                                                    </td>
                                                                    <td>
                                                                        <span> {{$item['unitName']}} </span>
                                                                    </td>
                                                                    <td>
                                                                        <span> {{number_format($item['qtyPo'], 1, ',', '.' )}} </span>
                                                                    </td>
                                                                    <td style="text-align: right;">
                                                                        <span> {{number_format($item['qtyGr'], 1, ',', '.' )}} </span>
                                                                    </td>
                                                                    <td style="text-align: right;">
                                                                        <span> {{number_format($item['qty'], 1, ',', '.' )}} </span>
                                                                    </td>
                                                                </tr>
                                                                @php($i++)
                                                                @php($totalQty += $item['qty'])
                                                            @endforeach
                                                                <tr>
                                                                    <td colspan="5" style="text-align: right;">Sub Total:</td>
                                                                    <td style="text-align: right;"> {{number_format($totalQty, 1, ',', '.' )}}</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div> --}}

                                            <div class="row">
                                                <div class="col-12">
                                                    <table class="table">
                                                        <thead>
                                                        <tr>
                                                            <th>NO.</th>
                                                            <th>ID Item</th>
                                                            <th>UOM</th>
                                                            <th class="text-end">Jml</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        @php($i = 0)
                                                        @php($totalQty = 0)
                                                        @foreach($itemList as $item)
                                                            @php($i++)
                                                            <tr>
                                                                <td>{{ $i }}</td>
                                                                <td>{{ $item['itemId'] }} {{ $item['itemName'] }} </td>
                                                                <td>{{ $item['unitName'] }}</td>
                                                                <td class="text-end">{{number_format($item['qty'], 0, '.', '.' )}}</td>
                                                            </tr>
                                                            @php($totalQty+=$item['qty'])
                                                        @endforeach
                                                        </tbody>
                                                        <tr class="amount">
                                                            <td class="fw-bold">Total Item:</td>
                                                            <td>{{number_format($i, 0, '.', '.' )}}</td>
                                                            <td class="fw-bold">Total Qty:</td>
                                                            <td class="text-end">{{number_format($totalQty, 0, '.', '.' )}}</td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="row pb-3" >
                                                <div class="col-4">
                                                    <p class="fw-bold">Tgl. Kedatangan<span class="float-end">:</span></p>
                                                </div>
                                                <div class="col">
                                                    <p>{{ \Carbon\Carbon::parse($data->date)->format('d-M-Y') }}</p>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="row g-5">
                                                        <div class="col-4 text-center">
                                                            <p class="pb-5 fw-bold">Dibuat Oleh</p>
                                                            <p style="border-bottom: 1px solid black; width: 100%;height:25px;">
                                                                {{-- {{ $updaterName }} --}}

                                                            </p>
                                                        </div>
                                                        <div class="col-4 text-center">
                                                            <p class="pb-5 fw-bold">Mengetahui</p>
                                                            <p style="border-bottom: 1px solid black; width: 100%;height:25px;"></p>
                                                        </div>
                                                        <div class="col-4 text-center">
                                                            <p class="pb-5 fw-bold">Disetujui Oleh</p>
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
