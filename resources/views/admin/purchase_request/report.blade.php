<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchase Request Report</title>
    <link rel="stylesheet" href="css/bootstrap.css">
    <style>

        body {
            background-color: #fff;
        }
        @page {
            size: landscape;
        }

        p {
            margin-bottom: 0px;
            line-height: 1.8em;
        }

        .p-rl-0 {
            padding-right: 0px;
            padding-left: 0px;
        }

        .border-top-custom {
            border-top: 5px solid black;
        }

        .border-bottom-custom {
            border-bottom: 5px solid black;
        }

        .border-top-custom-2 {
            border-top: 1px solid black;
        }

        .container {
            padding: 0px;
        }

        .logo {
            width: 180px;
        }
        @media print {
            table, td, th {
                border: 1px solid black !important;
            }

            #non-print {
                display: none;
            }
        }
    </style>
</head>
<body>
<section style="font-size:12px;">
    <div class="container">
        <div id='non-print' class="row mb-3">
            <a href="{{route('admin.pr.report.filter')}}">
            Back
            </a>
            &nbsp;
            <button class="btn btn-success rounded-round" onclick="printDiv('printMe')">
                Print
            </button>
        </div>
        <div class="row">
            <div class="col-2  pb-2">
{{--                <img src="{{asset('images/emp_logo.jpg')}}" alt="" style="width: 150px; height: auto">--}}
            </div>
            <div class="col-8 pl-5 font-weight-bold">
                <p class="" style="font-size: 20px;">{{$branch->name}}</p>
                <p class="">{{$branch->address ?? "-"}}</p>
                <p class="text-center font-weight-bold" style="font-size: 12px;">Period: {{$start_date}} To {{$end_date}}</p>
            </div>
            <div class="col-2">
                <p>Print Date: {{\Carbon\Carbon::now('Asia/Jakarta')}}</p>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <p class="text-center font-weight-bold">Purchase Request Reports</p>
            </div>
        </div>
    </div>
</section>
@foreach ($result as $data)

<section class="pb-2" style="font-size:12px;">
    <div class="container ">
        <div class="row font-weight-bold pb-2">
            <div class="col-2 ">
                <p>Transaction Number </p>
            </div>
            <div class="col-10">
                <p>: {{$data->code}}</p>
            </div>
        </div>
        <div class="row font-weight-bold ">
            <div class="col-2 ">
                <p>Transaction Date </p>
            </div>
            <div class="col-10">
                <p>: {{$data->required_date->format('d-m-Y')}}</p>
            </div>
        </div>
    </div>
    <div class="container border-top-custom border-bottom-custom ">
        <div class="row py-2">
            <div class="col-3 text-center">
                <div class="row">
                    <div class="col-5">
                        <p class="text-right">No</p>
                    </div>
                    <div class="col-7">
                        <p>Req. Date</p>
                    </div>
                </div>
            </div>
            <div class="col-4 text-center font-weight-bold p-rl-0">
                <p>Desc. head</p>
            </div>
            <div class="col-1 text-center font-weight-bold p-rl-0">
                <p>Code</p>
            </div>
            <div class="col-2 text-center font-weight-bold p-rl-0">
                <p>Name</p>
            </div>
            <div class="col-2">
                <div class="row">
                    <div class="col-3 text-center font-weight-bold p-rl-0">
                        <p>Qty</p>
                    </div>
                    <div class="col-3 text-center font-weight-bold p-rl-0">
                        <p>Unit</p>
                    </div>
                    <div class="col-3 text-center font-weight-bold p-rl-0">
                        <p>Description</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="border-top-custom-2 pb-1"></div>
        @php($ct=1)
        @foreach ($data->purchase_request_details as $detail)
            <div class="row p-1" style="font-size:9px;">
                <div class="col-3">
                    <div class="row">
                        <div class="col-5">
                            <p class="text-right">{{$ct}}</p>
                        </div>
                        <div class="col-7">
                            <p>: {{$data->required_date->format('d-m-Y')}}</p>
                        </div>
                    </div>
                </div>
                <div class="col-4  p-rl-0">
                    <p>{{$data->description}}</p>
                </div>
                <div class="col-1  ">
                    <p>{{$detail->item->code}}</p>
                </div>
                <div class="col-2 ">
                    <p>{{$detail->item->name}}</p>
                </div>
                <div class="col-2">
                    <div class="row">
                        <div class="col-3 text-right p-rl-0">
                            <p>{{number_format($detail->qty, '0', ',', '.')}}</p>
                        </div>
                        <div class="col-3">
                            <p>{{$detail->unit_material->name}}</p>
                        </div>
                        <div class="col-3  p-rl-0">
                            <p>{{$detail->description}}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="border-top-custom-2 pb-1"></div>
            @php($ct++)
        @endforeach
    </div>
</section>
@endforeach


<script>
    function printDiv(divName) {
        window.print();
    }
</script>
</body>
</html>
