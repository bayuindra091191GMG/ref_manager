<!doctype html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
        <title>Laporan Transaksi</title>
        <!-- Icons -->
        <!-- The following icons can be replaced with your own, they are used by desktop and mobile browsers -->
        <link rel="shortcut icon" href="{{ asset('images/logo_karada_orange.webp') }}">
        <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/logo_karada_orange.webp') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/logo_karada_orange.webp') }}">
        <!-- END Icons -->

        <!-- Stylesheets -->
        <!-- Fonts and OneUI framework -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" crossorigin="anonymous">
    </head>
    <body>
    <div class="text-center">
        <h1>Laporan Transaksi</h1>
    </div>
    <table class="table table-bordered" style="font-size: 12px !important;">
        <thead>
        <tr>
            <th>No</th>
            <th>Kode</th>
            <th>Customer</th>
            <th>Tanggal</th>
            <th>Diskon</th>
            <th>Total</th>
            <th>Dibuat oleh</th>
        </tr>
        </thead>
        <tbody>
        @foreach($result as $header)
            <tr>
                <td>{{ $header['no'] }}</td>
                <td>{{ $header['code'] }}</td>
                <td>{{ $header['customer'] }}</td>
                <td>{{ $header['date'] }}</td>
                <td>{{ $header['discount'] }}</td>
                <td>{{ $header['total'] }}</td>
                <td>{{ $header['created_by'] }}</td>
            </tr>
            <!-- Foreach Details and Payment also -->
            <tr>
                <td></td>
                <td>Items</td>
                <td colspan="7">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <td>No</td>
                            <td>Item</td>
                            <td>Qty</td>
                            <td>Price</td>
                            <td>Subtotal</td>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($header['details'] as $detail)
                            <tr>
                                <td>{{ $detail['no'] }}</td>
                                <td>{{ $detail['item_name'] }}</td>
                                <td>{{ $detail['qty'] }}</td>
                                <td>{{ $detail['price'] }}</td>
                                <td>{{ $detail['subtotal'] }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>Pembayaran</td>
                <td colspan="6">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <td>No</td>
                            <td>Kode</td>
                            <td>Amount</td>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($header['payments'] as $payment)
                            <tr>
                                <td>{{ $payment['no'] }}</td>
                                <td>{{ $payment['code'] }}</td>
                                <td>{{ $payment['amount'] }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan=7>&nbsp;</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </body>
</html>
