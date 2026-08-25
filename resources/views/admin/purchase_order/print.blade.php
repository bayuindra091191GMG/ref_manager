@extends('layouts.admin')

@section('title')
    <title>Print Purchase Order</title>
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
                                                    <a id="btn_cancel" href="{{ route('admin.purchase_order.index') }}" class="btn btn-outline-danger">Back</a>
                                                </div>
                                            </div>
                                            <div class="row align-items-center" style="border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 10px;">
                                                <div class="col-2 text-center">
                                                    <img src="{{ asset('images/logo-only.png') }}" alt="Logo" style="width: 90px;">
                                                </div>
                                                <div class="col-10 text-center m-banner">
                                                    <h4 class="fw-bold mb-1" style="font-family: 'Times New Roman', serif;">
                                                        CV. Simi Family Reflexology
                                                    </h4>
                                                    <div class="f-banner" style=" line-height: 1.4;">
                                                        Alamat : {{ $branches->address }} {{ $branches->extra_text }}
                                                    </div>
                                                    <div class="f-banner" style="margin-top: 5px;">
                                                        🌐 <a href="https://www.simi.co.id" target="_blank" style="text-decoration:none; color:black;">www.simi.co.id</a>
                                                        &nbsp; | No Telepon : {{ $branches->phone }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row text-center">
                                                <div class="col-12">
                                                    <h2 class="text-decoration-underline fw-bold">PURCHASE ORDER SLIP</h2>
                                                </div>
                                            </div>
                                            {{-- <table style="font-size: 14px;width: 100%">
                                                <tr>
                                                    <td width="20%">No Purchase Order</td>
                                                    <td>:</td>
                                                    <td style="font-weight: bold;">{{$data->code}}</td>
                                                    <td width="20%">No Purchase Request</td>
                                                    <td>:</td>
                                                    <td>{{$data->reference_code}}</td>
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
                                                    <td>{{ \Carbon\Carbon::parse($data->transaction_date)->format('d-m-Y') }}</td>
                                                    <td width="20%">Jangka Waktu Pembayaran</td>
                                                    <td>:</td>
                                                    <td>{{$data->top}} Hari</td>
                                                </tr>
                                                <tr>
                                                    <td width="20%">Deskripsi PR</td>
                                                    <td>:</td>
                                                    <td>{{$prDescription}}</td>
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
                                                            <p class="fw-bold">To<span class="float-end">:</span></p>
                                                        </div>
                                                        <div class="col">
                                                            <p>{{ $supplierName }}</p>
                                                            {{-- <p>{{ $supplierAddress }} --}}
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-4">
                                                            <p class="fw-bold">Kontak<span class="float-end">:</span></p>
                                                        </div>
                                                        <div class="col">
                                                            {{-- <p>{{ $supplierDB->email }}</p> --}}
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-4">
                                                            <p class="fw-bold">Phone<span class="float-end">:</span></p>
                                                        </div>
                                                        <div class="col">
                                                            {{-- <p>{{ $supplierDB->phone }}</p> --}}
                                                        </div>
                                                    </div>
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
                                                        <div class="col-5">
                                                            <p class="fw-bold">Po No<span class="float-end">:</span></p>
                                                        </div>
                                                        <div class="col">
                                                            <p>{{ $data->code }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-5">
                                                            <p class="fw-bold">Po Date<span class="float-end">:</span></p>
                                                        </div>
                                                        <div class="col">
                                                            <p>{{ \Carbon\Carbon::parse($data->transaction_date)->format('d-m-Y') }}</p>
                                                        </div>
                                                    </div>
                                                <div class="row">
                                                    <div class="col-5">
                                                        <p class="fw-bold">Status<span class="float-end">:</span></p>
                                                    </div>
                                                    <div class="col">
                                                        <p>{{ $data->status->display }}</p>
                                                    </div>
                                                </div>
                                                    <div class="row">
                                                        <div class="col-5">
                                                            <p class="fw-bold">Approval<span class="float-end">:</span></p>
                                                        </div>
                                                        <div class="col">
                                                            {{-- <p>{{ $updaterName }}</p> --}}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            {{-- <div class="row">
                                                <div class="col-sm-12">
                                                    <div class="table-responsive w-100">
                                                        <table class="table table-bordered" id="budget-detail-table" style="font-size: 12px; overflow: hidden;">
                                                            <tr style="font-size: 12px; font-weight: bold;">
                                                                <th>No</th>
                                                                <th>Nama Produk</th>
                                                                <th>Unit</th>
                                                                <th>Qty PR</th>
                                                                <th>Qty</th>
                                                                <th>Price</th>
                                                                <th>Total</th>
                                                                <th>Disc1 Rp</th>
                                                                <th>Disc2 Rp</th>
                                                                <th>Subtotal</th>
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
                                                                        <span> {{number_format($item['qtyPr'], 1, ',', '.' )}} </span>
                                                                    </td>
                                                                    <td style="text-align: right;">
                                                                        <span> Rp {{number_format($item['qty'], 1, ',', '.' )}} </span>
                                                                    </td>
                                                                    <td style="text-align: right;">
                                                                        <span> Rp {{number_format($item['price'], 0, '.', '.' )}} </span>
                                                                    </td>
                                                                    <td style="text-align: right;">
                                                                        <span> Rp {{number_format($item['price'] * $item['qty'], 0, '.', '.' )}} </span>
                                                                    </td>
                                                                    <td style="text-align: right;">
                                                                        <span> Rp {{number_format($item['disc1Value'], 0, '.', '.' )}} </span>
                                                                    </td>
                                                                    <td style="text-align: right;">
                                                                        <span> Rp {{number_format($item['disc2Value'], 0, '.', '.' )}} </span>
                                                                    </td>
                                                                    <td style="text-align: right;">
                                                                        <span> Rp {{number_format($item['total'], 0, '.', '.' )}} </span>
                                                                    </td>
                                                                </tr>
                                                                @php($i++)
                                                                @php($totalQty += $item['qty'])
                                                                @php($subTotal += $item['total'])
                                                            @endforeach
                                                                <tr>
                                                                    <td colspan="4" style="text-align: right;">Sub Total:</td>
                                                                    <td style="text-align: right;"> Rp {{number_format($totalQty, 1, ',', '.' )}}</td>
                                                                    <td colspan="5" ></td>
                                                                    <td style="text-align: right;"> Rp {{number_format($subTotal, 0, '.', '.' )}}</td>
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
                                                            <th>SKU</th>
                                                            <th>DESCRIPTION</th>
                                                            <th>QTY</th>
                                                            <th>UOM</th>
                                                            <th>PRICE</th>
                                                            <th>TOTAL COST</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        @php($i = 1)
                                                        @foreach($itemList as $item)
                                                            <tr>
                                                                <td>{{ $i }}</td>
                                                                <td>{{ $item['itemId'] }}</td>
                                                                <td>{{ $item['itemName'] }}</td>
                                                                <td>{{number_format($item['qty'], 0, '.', '.' )}}</td>
                                                                <td>{{ $item['unitName'] }}</td>
                                                                <td class="amount-value">{{number_format($item['price'], 0, '.', '.' )}}</td>
                                                                <td class="text-end amount-value">{{number_format($item['total'], 0, '.', '.' )}}</td>
                                                            </tr>
                                                            @php($i++)
                                                        @endforeach
                                                        </tbody>
                                                        <tr class="amount">
                                                            <td colspan="5"></td>
                                                            <td class="fw-bold text-end">TOTAL AMOUNT</td>
                                                            <td class="text-end amount-value">{{number_format($data->subtotal, 0, '.', '.' )}}</td>
                                                        </tr>
                                                        <tr class="amount">
                                                            <td colspan="5"></td>
                                                            <td class="fw-bold text-end">DISCOUNT</td>
                                                            <td class="text-end amount-value">{{number_format($data->disc1_value, 0, '.', '.' )}}</td>
                                                        </tr>
                                                        <tr class="amount">
                                                            <td colspan="5"></td>
                                                            <td class="fw-bold text-end">PPN</td>
                                                            <td class="text-end amount-value">{{number_format($data->ppn_value, 0, '.', '.' )}}</td>
                                                        </tr>
                                                        <tr class="amount">
                                                            <td colspan="5"></td>
                                                            <td class="fw-bold text-end">TOTAL NETT</td>
                                                            <td class="text-end amount-value">{{number_format($data->grandtotal, 0, '.', '.' )}}</td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>


                                            {{-- <table width="850px" style="font-size: 14px;">
                                                <tr>
                                                    <td width="20%">Diskon 1</td>
                                                    <td>:</td>
                                                    <td>{{$data->disc1_percent}} %</td>
                                                    <td width="20%">Nilai Diskon 1</td>
                                                    <td>:</td>
                                                    <td>Rp {{number_format($data->disc1_value, 0, '.', '.' )}}</td>
                                                </tr>
                                                <tr>
                                                    <td width="20%">Diskon 2</td>
                                                    <td>:</td>
                                                    <td>{{$data->disc2_percent}} %</td>
                                                    <td width="20%">Nilai Diskon 2</td>
                                                    <td>:</td>
                                                    <td>Rp {{number_format($data->disc2_value, 0, '.', '.' )}}</td>
                                                </tr>
                                                <tr>
                                                    <td width="20%">PPH23</td>
                                                    <td>:</td>
                                                    <td>{{$data->ppn_percent}} %</td>
                                                    <td width="20%">Nilai PPH23</td>
                                                    <td>:</td>
                                                    <td>Rp {{number_format($data->ppn_value, 0, '.', '.' )}}</td>
                                                </tr>
                                                <tr style="font-weight: bold; font-size: 16px;">
                                                    <td width="20%">Grand Total</td>
                                                    <td>:</td>
                                                    <td>Rp {{number_format($data->grandtotal, 0, '.', '.' )}} </td>
                                                    <td colspan="3"></td>
                                                </tr>
                                            </table> --}}
                                            <div class="row pb-3" style="margin-top:-75px !important;">
                                                <div class="col-8">
                                                    <div class="row g-5">
                                                        <div class="col-4 text-center">
                                                            <p class="pb-5 fw-bold">PREPARED BY</p>
                                                            <p class="pt-4" style="border-bottom: 1px solid black; width: 100%;">{{ $createdBy }}</p>
                                                        </div>
                                                        <div class="col-4 text-center">
                                                            <p class="pb-5 fw-bold">CONFIRM BY</p>
                                                            <p class="pt-4" style="border-bottom: 1px solid black; width: 100%;">&nbsp;</p>
                                                        </div>
                                                        <div class="col-4 text-center">
                                                            <p class="pb-5 fw-bold">APPROVED BY</p>
                                                            <p class="pt-4" style="border-bottom: 1px solid black; width: 100%;">&nbsp;</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col"></div>
                                            </div>
                                            <div class="row">
                                                <div class="col-12 note">
                                                    <p>Note : Please Confirmed and signed the PO and re-fax to us</p>
                                                    <p>PENTING/IMPORTANT:</p>
                                                    <ol>
                                                        <li>Cantumkan No. PO ini pada Invoice copy & delivery note yang telah ditanda tangani kirim ke Bag. Keuangan</li>
                                                        <li>Barang-barang yg disebut pada PO tidak dapat diganti tanpa persetujuan tertulis dari bagian pembelian kami</li>
                                                        <li>Perusahaan berhak membatalkan PO apabila supplier tidak mengirimkan barang pada waktu yg telah ditentukan</li>
                                                        <li>Waktu penerimaan barang di gudang dikonfirmasikan terlebih dahulu</li>
                                                        <li>Waktu delivery: ...................................................... </li>
                                                        <li>Lama waktu pembayaran (term of payment): ..........................</li>
                                                        <li>Penukaran faktur wajib dilakukan selambat-lambatnya 6 bulan setelah barang dikirim, lewat dari 6 bulan kami tidak menerima
                                                            penukaran faktur</li>
                                                        <li>Setelah pembayaran dilakukan oleh PT. Farracon Precast Industri, maka tanda tukar faktur asli wajib dikembalikan ke
                                                            PT. Farracon Precast Industri, jika tidak dikembalikan maka kami berhak untuk menahan pembayaran berikutnya</li>
                                                        <li style="font-weight: 900;">Semua pengiriman maupun penerimaan barang wajib ditujukan atau diserahkan ke bagian Logistik PT. Farracon
                                                            Precast Industri, Pengiriman diluar bagian itu dianggap kami belum menerima</li>
                                                    </ol>
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
