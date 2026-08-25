<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Laporan Rekap Transaksi</title>
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
    <div id="main-container" class="justify-content-center" style="width: 305px; font-size: 10px;">
        <div class="row">
            <div class="col-12">
                <h5>Laporan Transaksi</h5>
            </div>
        </div>
        <div class="row">
            <div class="col-6">
                @if($branchId == 6 || $branchId == 7)
                    <span>{{ $startDate }} - {{ $endDate }}</span><br/>
                    <span>{{ $diffDay }} hari</span>
                @else
                    <span>{{ $startDate }} - {{ $endDate }}</span><br/>
                @endif
            </div>
            <div class="col-6 text-end">
                <button type="button" id="btn_print" class="btn btn-outline-info">Print</button>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <h5>Total Pemasukan</h5>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <table>
                    <tbody>
                    @foreach($payments as $payment)
                        <tr>
                            <td>{{ $payment['payment'] }}&nbsp;&nbsp;&nbsp;</td>
                            <td>Rp{{ $payment['total_str'] }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td style="font-weight: bold;">PENDAPATAN</td>
                        <td>Rp{{ $totalStr }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- jQuery (required for DataTables plugin) -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script>
        $(document).on('click', '#btn_print', function(){
            $('#btn_print').hide();
            window.print();
        });
        window.onafterprint = function(){
            $('#btn_print').show();
            $('.print_hide').show();
        }
    </script>
</body>
</html>
