@extends('layouts.admin')

@section('title')
    <title>BACKEND - Dashboard</title>
@endsection

@section('content')

    <!-- Hero -->
    <div class="content space-top">
        <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center py-2 text-center text-md-start">
            <div class="flex-grow-1 mb-1 mb-md-0">
                <h1 class="h3 fw-bold mb-2">
                    Dashboard
                </h1>
                <h2 class="h6 fw-medium fw-medium text-muted mb-0">
{{--                    Welcome <a class="fw-semibold" href="{{route('admin.adminuser.edit', ['id'=>$admin->id])}}">{{$admin->name}}</a>, everything looks great.--}}
                    Welcome {{$admin->name}}, {{ $quote }}
                </h2>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <!-- Dashboard Content -->
    <div class="content" id="app">
        <!-- Overview -->
        <div class="row items-push">
            <div class="col-sm-4 col-xxl-4">
                <!-- Jumlah Sales Order -->
                <div class="block block-rounded d-flex flex-column h-100 mb-0">
                    <div class="block-content block-content-full flex-grow-1 d-flex justify-content-between align-items-center">
                        
                        <div class="spinner-border text-primary" role="status" v-if="loadingTotalPr">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <dl class="mb-0" v-else>
                            <a href="#" id="report-outstanding" @click="getReportOutstanding">
                                <dt class="fs-3 fw-bold">@{{ totalPr }}</dt>
                                <dd class="fs-sm fw-medium fs-sm fw-medium text-muted mb-0">Outstanding PR</dd>
                            </a>
                            <form id="form-outstanding" method="post" action="{{ route('admin.pr.report.generate') }}" target="_blank">
                                @csrf
                                <input type="hidden" name="type" value="Ledger">
                                <input type="hidden" name="start_date" value="{{ date('01-01-2023') }}">
                                <input type="hidden" name="end_date" value="{{ date('d-m-Y') }}">
                                <input type="hidden" name="status" value="OUTSTANDING">
                            </form>
                        </dl>
                        <div class="item item-rounded-lg bg-body-light">
                            <i class="fa fa-truck-moving fs-3 text-success"></i>
                        </div>
                    </div>
                    <div class="bg-body-light rounded-bottom">
                        <a class="block-content block-content-full block-content-sm fs-sm fw-medium d-flex align-items-center justify-content-between" href="javascript:void(0)">
                            <span></span>
                        </a>
                    </div>
                </div>
                <!-- END Jumlah Sales Order -->
            </div>
            <div class="col-sm-4 col-xxl-4">
                <!-- Jumlah Sales Order -->
                <div class="block block-rounded d-flex flex-column h-100 mb-0">
                    <div class="block-content block-content-full flex-grow-1 d-flex justify-content-between align-items-center">
                        <div class="spinner-border text-primary" role="status" v-if="loadingTotalInventory">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <dl class="mb-0" v-else>
                            <a href="#" id="report-inventory" @click="getReportInventory">
                                <dt class="fs-3 fw-bold">@{{ TotalInventory }}</dt>
                                <dd class="fs-sm fw-medium fs-sm fw-medium text-muted mb-0">Warning Inventory</dd>
                            </a>
                            <form id="form-inventory" method="post" action="{{ route('admin.report.stock.generate') }}" target="_blank">
                                @csrf
                                <input type="hidden" name="type" value="Ledger">
                                <input type="hidden" name="start_date" value="{{ date('01-01-Y') }}">
                                <input type="hidden" name="end_date" value="{{ date('d-m-Y') }}">
                                <input type="hidden" name="warehouse" value="0">
                                <input type="hidden" name="type" value="warning">
                            </form>
                        </dl>
                        <div class="item item-rounded-lg bg-body-light">
                            <i class="fa fa-truck-loading fs-3 text-success"></i>
                        </div>
                    </div>
                    <div class="bg-body-light rounded-bottom">
                        <a class="block-content block-content-full block-content-sm fs-sm fw-medium d-flex align-items-center justify-content-between" href="javascript:void(0)">
                            <span></span>
                        </a>
                    </div>
                </div>
                <!-- END Jumlah Sales Order -->
            </div>
        </div>
    </div>
    <!-- End Dashboard Content -->

@endsection
@section('scripts')
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <!-- Page JS Plugins -->
    <script src="{{ asset('js/plugins/chart.js/Chart.min.js') }}"></script>
    <!-- Page JS Code -->
    <script src="{{ asset('js/pages/be_pages_dashboard.min.js') }}"></script>
    <script src="{{ asset('js/axios.js') }}"></script>
    <script src="{{ asset('js/vue.js') }}"></script>
    <script src="https://unpkg.com/vue-toasted"></script>
    <script>
        (Chart.defaults.color = "#818d96"),
        (Chart.defaults.scale.grid.lineWidth = 0),
        (Chart.defaults.scale.beginAtZero = !0),
        (Chart.defaults.datasets.bar.maxBarThickness = 45),
        (Chart.defaults.elements.bar.borderRadius = 4),
        (Chart.defaults.elements.bar.borderSkipped = !1),
        (Chart.defaults.elements.point.radius = 0),
        (Chart.defaults.elements.point.hoverRadius = 0),
        (Chart.defaults.plugins.tooltip.radius = 3),
        (Chart.defaults.plugins.legend.labels.boxWidth = 10);

        let app = new Vue({
            el: '#app',
            data: {
                loadingTotalSo: false,
                totalSo: 0,
                loadingTotalPr: false,
                totalPr: 0,
                loadingTotalInventory: false,
                TotalInventory: 0,
                loadingTransactionTotal: false,
                monthlyTransaction: [],
                loadingDailyExpense: false,
                dailyExpenses: [],
            },
            methods: {
                async getTotalPr(){
                    this.loadingTotalPr = true;
                    await axios({
                        method: 'get',
                        url: '{{ route('admin.dashboard.get_total_pr') }}',
                    }).then(function (response) {
                        if(response){
                            if(response.status === 200){
                                app.totalPr = response.data;
                                //this.totalFocCustomer = response.data.totalFocCustomer;
                            }
                            else{
                                console.log(response);
                            }
                        }
                    });
                    this.loadingTotalPr = false;
                },
                async getTotalInventory(){
                    this.loadingTotalInventory = true;
                    await axios({
                        method: 'get',
                        url: '{{ route('admin.dashboard.get_total_inventory') }}',
                    }).then(function (response) {
                        if(response){
                            if(response.status === 200){
                                app.TotalInventory = response.data;
                                //this.totalFocCustomer = response.data.totalFocCustomer;
                            }
                            else{
                                console.log(response);
                            }
                        }
                    });
                    this.loadingTotalInventory = false;
                },

                getReportOutstanding(){
                    $('#form-outstanding')[0].submit();
                },

                getReportInventory(){
                    $('#form-inventory')[0].submit();
                }
            },
            async mounted(){
                this.getTotalPr();
                this.getTotalInventory();
            }

        });
    </script>
@endsection
