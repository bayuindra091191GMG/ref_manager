<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Laporan Rekap Transaksi Oura</title>
    <link rel="stylesheet" id="css-main" href="{{ asset('css/oneui.min.css')}}">
    <style>
        @media print {
            .print_div{
                display: block;
            }

            .print_hide {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div id="main-container" class="justify-content-center" style="width: 155px; font-size: 9px; font-weight: 400;">
        <div class="row">
            <div class="col-12">
                <h5>Laporan Transaksi</h5>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                @if($branchId != 6)
                    <span>{{ $startDate }} - {{ $endDate }}</span><br/>
                    <span>{{ $diffDay }} hari</span>
                @else
                    <span>{{ $startDate }} - {{ $endDate }}</span>
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <span><b>Total Pemasukan</b></span>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <table>
                    <tbody>
                    @foreach($payments as $payment)
                        <tr>
                            <td>{{ $payment['payment'] }}&nbsp;&nbsp;&nbsp;</td>
                            <td class="text-end">{{ $payment['total_str'] }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td style="font-weight: bold;">Total</td>
                        <td class="text-end">{{ $totalStr }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-12">
                <span><b>Total Penjualan Treatment</b></span>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <table>
                    <tr>
                        <td>Deskripsi</td>
                        <td>Qty</td>
                    </tr>
                    @foreach($allPackage as $package)
                        <tr>
                            <td>{{ $package['name'] }}</td>
                            <td>{{ $package['qty'] }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <h4>Total Penjualan Add On</h4>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <table>
                    <tr>
                        <th class="text-center">Nama Barang</th>
                        <th class="text-center">Kuantitas Terjual</th>
                        <th class="text-center">Pendapatan</th>
                    </tr>
                    @foreach($allItem as $item)
                        <tr>
                            <td>{{ $item['name'] }}</td>
                            <td class="text-end">{{ $item['qty'] }}</td>
                            <td class="text-end">Rp{{ number_format($item['total_price'], 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
        <div class="row">
            <div class="col-12" style="height: 100px;"></div>
        </div>
    </div>
    <!-- jQuery (required for DataTables plugin) -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script>
        window.onload = function () {
            window.print();
        }
    </script>
</body>
</html>
