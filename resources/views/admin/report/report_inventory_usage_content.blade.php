<!DOCTYPE html>
<html lang="en">
<head>
    <title>Laporan Penjualan Barang</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" crossorigin="anonymous">

    <style>
        @media print {
            .print_show{
                display: block;
            }

            .print_hide {
                display: none;
            }
        }

        td{
            padding: 3px;
        }
    </style>
</head>
<body>
<div class="container" style="width: 700px !important;">
    <div id="print_button_section" class="row my-3">
        <div class="col-12">
            <button type="button" class="btn btn-primary" id="btn_print">CETAK</button>
        </div>
    </div>
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h5>Laporan Penjualan Barang</h5>
            <span>{{ $startDate }} - {{ $endDate }}</span>
        </div>
    </div>
    <div class="row mb-4" style="font-size: 11px !important;">
        <div class="col-12">
            <table class="w-100 table">
                <thead>
                <tr class="text-center">
                    <th class="text-center">No</th>
                    <th class="text-start">Nama</th>
                    <th class="text-center">Kode</th>
                    <th class="text-center">Kategori</th>
                    <th class="text-end" style="width: 15%;">Qty Terjual</th>
                </tr>
                </thead>
                <tbody>
                @php( $idx = 1 )
                @foreach($itemResults as $itemResult)
                    <tr>
                        <td class="text-center">{{ $idx }}</td>
                        <td>{{ $itemResult['name'] }}</td>
                        <td class="text-center">{{ $itemResult['code'] }}</td>
                        <td class="text-center">{{ $itemResult['category_name'] }}</td>
                        <td class="text-end">{{ $itemResult['qty'] }}</td>
                    </tr>
                    @php( $idx++ )
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="{{ asset('js/lib/jquery.min.js') }}"></script>
<script>
    $(document).on('click', '#btn_print', function(){
        // $('#print_doc').printThis({
        //     header: "<h3>Surat Jalan</h3>",
        // });
        $('#print_button_section').hide();
        window.print();
    });
    // window.onafterprint = function(){
    //     $('#print_button_section').hide();
    // }
</script>

</body>
</html>
