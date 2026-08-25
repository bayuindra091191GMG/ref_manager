<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<style>
    @media print {
        .print_show{
            display: block;
        }

        .print_hide {
            display: none;
        }
    }

    .txt-name,.txt-report-title{
            font-size: 1.5rem;
            font-weight: bold;
        }
    .border-black{
        border: 1px solid black;
    }
    p,td,th{
        font-size: 0.75rem;
        margin: 0;
    }
    .vertical-upright{
        writing-mode: vertical-rl;
        text-orientation: upright;
    }
    .bb{
        border-bottom: 1px solid black;
    }
    .br{
        border-right: 1px solid black;
    }
    .by{
        border-top: 1px solid black;
        border-bottom: 1px solid black;
    }
    .h-c{
        height: 8vh;
    }
    .container-fluid{
        width: 50%;
    }
    .no-width{
        width: 5%;
    }
    .kode-width{
        width: 20%;
    }
    .nama-width{
        width: 58%;
    }
    .merk-width{
        width:10%
    }
    .kts-width{
        width:10%
    }
    .satuan-width{
        width: 10%;
    }


    @media print{
        .txt-name{
            font-size: 1.5rem;
        }
        .container-fluid{
            width: 100%;
        }
        .no-width{
            width: 5%;
        }
        .kode-width{
            width: 20%;
        }
        .nama-width{
            width: 55%;
        }
        .merk-width{
            width:6%
        }
        .kts-width{
            width:6%
        }
        .satuan-width{
            width: 10%;
        }

    }
</style>
</head>
<body>
    <section>
        <div class="container-fluid">
            <div class="row print_hide mt-3">
                <div class="col-12 text-end">
                    <button type="button" id="btn_print" class="btn btn-outline-info">Print</button>
                    <a id="btn_cancel" href="{{ url()->previous() }}" class="btn btn-outline-danger">Back</a>

                </div>
            </div>
            <div class="row">
                <div class="col-4 text-center">
                    <img src="{{ asset('images/logo-only.png') }}" style="width:200px" alt="">
                </div>
                <div class="col-8">
                    <p class="txt-name">{{ $branches->name }}</p>
                    <p>{{ $branches->address }}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-12 text-center">
                    <p class="txt-name">MUTASI BARANG</p>
                </div>
            </div>
            <div class="row border-black mb-2">
                <div class="col-6">
                    <div class="row">
                        <div class="col-5">
                            <p>Terima dari Gudang<span class="float-end">:</span></p>
                        </div>
                        <div class="col-7">
                            <p>{{ $data->warehouse_origin_name }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-6" style="border-left: 1px solid black;">
                    <div class="row">
                        <div class="col-5">
                            <p>Nomor<span class="float-end">:</span></p>
                        </div>
                        <div class="col-7">
                            <p>{{ $data->code }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-5">
                            <p>Tanggal<span class="float-end">:</span></p>
                        </div>
                        <div class="col-7">
                            <p>{{ \Carbon\Carbon::parse($data->transfer_date)->format('j M Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row pt-3 pb-5 ">
                <div class="col-12">
                    <table>
                        <tr class="text-center bb" >
                            <th class="no-width">No</th>
                            <th class="kode-width">Kode Barang</th>
                            <th class="nama-width">Nama Barang</th>
                            <th class="merk-width">Merk</th>
                            <th class="kts-width">Kts.</th>
                            <th class="satuan-width">Satuan</th>
                        </tr>
                        @foreach($itemList as $i => $detail)
                            <tr class="text-center pb-3 bb" >
                                <td>{{ $i+1 }}</td>
                                <td>{{ $detail['itemCode'] }}</td>
                                <td>{{ $detail['itemName'] }}</td>
                                <td>{{ $detail['category'] }}</td>
                                <td>{{ $detail['qty'] }}</td>
                                <td>{{ $detail['unitName'] }}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
            <div class="row pb-5">
                <div class="col-5">
                    <p class="by fw-bold">Ke Gudang</p>
                    <p class="h-c">{{ $data->warehouse_destination_name }}</p>
                    <div style="border-top: 2px dashed black;"></div>
                </div>
                <div class="col-2"></div>
                <div class="col-5">
                    <p class="by fw-bold">Keterangan</p>
                    <p class="h-c">{{ $data->description }}</p>
                    <div style="border-top: 2px dashed black;"></div>
                </div>
            </div>
            <div class="row">
                <div class="col-4 text-center">
                    <p class="fw-bold ">Disiapkan Oleh,</p>
                    <div class="h-c"></div>
                    <div style="border-top: 2px solid black;"></div>
                    <p class="text-start">Tgl.</p>
                </div>
                <div class="col-4 text-center">
                    <p class="fw-bold ">Disetujui Oleh,</p>
                    <div class="h-c"></div>
                    <div style="border-top: 2px solid black;"></div>
                    <p class="text-start">Tgl.</p>
                </div>
                <div class="col-4 text-center">
                    <p class="fw-bold ">Penerima,</p>
                    <div class="h-c"></div>
                    <div style="border-top: 2px solid black;"></div>
                    <p class="text-start">Tgl.</p>
                </div>
            </div>
        </div>
    </section>

    <script type="text/javascript" src="{{ asset('js/lib/jquery.min.js') }}"></script>
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
</body>
</html>
