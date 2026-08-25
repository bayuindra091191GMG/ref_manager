<!doctype html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
        <title>Food Receipt</title>
        <link rel="stylesheet" id="css-main" href="{{ asset('css/oneui.min.css')}}">
    </head>
    <body>
    <div id="main-container" class="justify-content-center" style="width: 305px; font-size: 10px;">
        <div class="mb-1 text-center" style="font-size: 30px; font-weight: bold;">{{ $locker_no }}</div>
        <div class="mb-1 text-center" style="font-size: 25px; font-weight: bold;">** Order List **</div>
        @if(count($nci) > 0)
            <div class="mb-1 text-center" style="font-size: 20px; font-weight: bold;">Therapis: {{ $nci[0]->number }}</div>
        @endif
        <table>
            <tr style="font-size: 18px;">
                <td width="13%">Qty</td>
                <td>Description</td>
            </tr>
        </table>
        <div>----------------------------------------------------</div>
        <table>
            @foreach($details as $trx)
                <tr style="font-size: 18px;">
                    <td width="13%" class="text-center">{{ $trx->new_order_qty }}</td>
                    <td>
                        <span>{{ $trx->item->name }}</span>
                    </td>
                </tr>
            @endforeach
        </table>
        <div>----------------------------------------------------</div>
        <div class="mt-1" style="text-align: center; text-justify: inter-word;">
            {{ $date }}
        </div>
    </div>
    <script>
        window.onload = function () {
            window.print();
        }
    </script>
    </body>
</html>
