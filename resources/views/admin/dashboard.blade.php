@extends('layouts.admin')

@section('title')
<title>BACKEND - Dashboard</title>
@endsection

@section('content')
<!-- Hero -->
<div class="d-none d-md-block">
    <div class="content space-top" style="position: fixed; top: 0; z-index: 10; background-color: white; width: 100%; padding-top: 2vh;">
        <div
            class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center py-2 text-center text-md-start">
            <div class="flex-grow-1 mb-1 mb-md-0">
                <h1 class="h3 fw-bold mb-2">
                    Welcome {{ $admin->name }}
                </h1>
            </div>
        </div>
        <!-- FILTER -->
        <div class="row items-push" >
            <div class="col-xs-12  col-sm-12 col-xxl-12">
                <div class="row mb-2">
                    <div class="col-sm-3 col-12">
                        <label class="form-label" for="start_date">Tanggal Mulai</label>
                        <input type="text" id="start_date" class="form-control js-flatpickr" data-date-format="d-m-Y" value="{{ $startDate }}"/>
                    </div>
                    <div class="col-sm-3 col-12">
                        <label class="form-label" for="end_date">Tanggal Berakhir</label>
                        <input type="text" id="end_date" class="form-control js-flatpickr" data-date-format="d-m-Y" value="{{ $endDate }}"/>
                    </div>
                    <div class="col-sm-3 col-12">
                        <label class="form-label" for="button">&nbsp;</label>
                        <button type="button" class="form-control btn btn-primary" onclick="filterData();">Filter Data</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- END FILTER -->
    </div>
</div>

<!-- MOBILE FILTER FLOATING -->
<div class="d-block d-md-none">
    <div
        class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center py-2 text-center text-md-start">
        <div class="flex-grow-1 mb-1 mb-md-0">
            <h1 class="h3 fw-bold mb-2">
                Welcome {{ $admin->name }}
            </h1>
        </div>
    </div>
</div>
<!-- END MOBILE FILTER FLOATING -->


<!-- END Hero -->

<!-- Dashboard Content -->
<div class="content" id="app">

    <div class="d-block d-md-none">
        <div class="position-fixed bottom-0 start-0 end-0 bg-white border-top shadow-lg p-3" style="z-index: 1050;">
            <div class="row g-2 align-items-end">
                <div class="col-6">
                    <label class="form-label mb-1" for="start_date_mobile">Mulai</label>
                    <input type="text" id="start_date_mobile" class="form-control js-flatpickr" data-date-format="d-m-Y" value="{{ $startDate }}">
                </div>
                <div class="col-6">
                    <label class="form-label mb-1" for="end_date_mobile">Berakhir</label>
                    <input type="text" id="end_date_mobile" class="form-control js-flatpickr" data-date-format="d-m-Y" value="{{ $endDate }}">
                </div>
                <div class="col-12 mt-2">
                    <button type="button" class="btn btn-primary w-100" onclick="filterDataMobile();">
                        <i class="fa fa-filter me-1"></i> Filter Data
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Overview -->
    <div class="row items-push">
        <div class="col-xs-12  col-sm-4 col-xxl-4">
            <!-- Gross Sales -->
            <div class="block block-rounded d-flex flex-column h-100 mb-0">
                <div
                    class="block-content block-content-full flex-grow-1 d-flex justify-content-between align-items-center">
                    <div class="spinner-border text-primary" role="status" v-if="loadingTotalGrossSales">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <dl class="mb-0" v-else>
                        <dt class="fs-3 fw-bold">@{{ formatCurrency(totalGrossSales) }}</dt>
                        <dd class="fs-sm fw-medium fs-sm fw-medium text-muted mb-0">Gross Sales</dd>
                    </dl>
                    <div class="item item-rounded-lg bg-body-light">
                        <i class="far fa-money-bill-alt fs-3 text-success"></i>
                    </div>
                </div>
                <div class="bg-body-light rounded-bottom">
                    <a class="block-content block-content-full block-content-sm fs-sm fw-medium d-flex align-items-center justify-content-between"
                       href="javascript:void(0)">
                        <span></span>
                    </a>
                </div>
            </div>
            <!-- END Gross Sales -->
        </div>
        <div class="col-xs-12  col-sm-4 col-xxl-4">
            <!-- Net Sales -->
            <div class="block block-rounded d-flex flex-column h-100 mb-0">
                <div
                    class="block-content block-content-full flex-grow-1 d-flex justify-content-between align-items-center">
                    <div class="spinner-border text-primary" role="status" v-if="loadingTotalNetSales">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <dl class="mb-0" v-else>
                        <dt class="fs-3 fw-bold">@{{ formatCurrency(totalNetSales) }}</dt>
                        <dd class="fs-sm fw-medium fs-sm fw-medium text-muted mb-0">Net Sales</dd>
                    </dl>
                    <div class="item item-rounded-lg bg-body-light">
                        <i class="far fa-money-bill-alt fs-3 text-success"></i>
                    </div>
                </div>
                <div class="bg-body-light rounded-bottom">
                    <a class="block-content block-content-full block-content-sm fs-sm fw-medium d-flex align-items-center justify-content-between"
                        href="javascript:void(0)">
                        <span></span>
                    </a>
                </div>
            </div>
            <!-- END Net Sales -->
        </div>
        <div class="col-xs-12  col-sm-4 col-xxl-4">
            <!-- Total Discount -->
            <div class="block block-rounded d-flex flex-column h-100 mb-0">
                <div
                    class="block-content block-content-full flex-grow-1 d-flex justify-content-between align-items-center">
                    <div class="spinner-border text-primary" role="status" v-if="loadingTotalDiscount">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <dl class="mb-0" v-else>
                        <dt class="fs-3 fw-bold">@{{ formatCurrency(totalDiscount) }}</dt>
                        <dd class="fs-sm fw-medium fs-sm fw-medium text-muted mb-0">Total Discount</dd>
                    </dl>
                    <div class="item item-rounded-lg bg-body-light">
                        <i class="far fa-money-bill-alt fs-3 text-success"></i>
                    </div>
                </div>
                <div class="bg-body-light rounded-bottom">
                    <a class="block-content block-content-full block-content-sm fs-sm fw-medium d-flex align-items-center justify-content-between"
                        href="javascript:void(0)">
                        <span></span>
                    </a>
                </div>
            </div>
            <!-- END Total Discount -->
        </div>
    </div>
    <!-- END Overview -->

    <!-- Booking Summary -->
    <div class="row items-push">
        <div class="col-xs-12 col-sm-4 col-xxl-4">
            <div class="block block-rounded d-flex flex-column h-100 mb-0">
                <div class="block-content block-content-full flex-grow-1 d-flex justify-content-between align-items-center">
                    <div class="spinner-border text-primary" role="status" v-if="loadingBookingSummary">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <dl class="mb-0" v-else>
                        <dt class="fs-3 fw-bold">@{{ formatNumber(todayBooking) }}</dt>
                        <dd class="fs-sm fw-medium fs-sm fw-medium text-muted mb-0">Today Booking</dd>
                    </dl>
                    <div class="item item-rounded-lg bg-body-light">
                        <i class="fa fa-calendar-day fs-3 text-primary"></i>
                    </div>
                </div>
                <div class="bg-body-light rounded-bottom">
                    <a class="block-content block-content-full block-content-sm fs-sm fw-medium d-flex align-items-center justify-content-between" href="javascript:void(0)">
                        <span></span>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-4 col-xxl-4">
            <div class="block block-rounded d-flex flex-column h-100 mb-0">
                <div class="block-content block-content-full flex-grow-1 d-flex justify-content-between align-items-center">
                    <div class="spinner-border text-primary" role="status" v-if="loadingBookingSummary">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <dl class="mb-0" v-else>
                        <dt class="fs-3 fw-bold">@{{ formatNumber(bookingNotApprove) }}</dt>
                        <dd class="fs-sm fw-medium fs-sm fw-medium text-muted mb-0">Booking Not Approve</dd>
                    </dl>
                    <div class="item item-rounded-lg bg-body-light">
                        <i class="fa fa-file-circle-xmark fs-3 text-warning"></i>
                    </div>
                </div>
                <div class="bg-body-light rounded-bottom">
                    <a class="block-content block-content-full block-content-sm fs-sm fw-medium d-flex align-items-center justify-content-between" href="javascript:void(0)">
                        <span></span>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-4 col-xxl-4">
            <div class="block block-rounded d-flex flex-column h-100 mb-0">
                <div class="block-content block-content-full flex-grow-1 d-flex justify-content-between align-items-center">
                    <div class="spinner-border text-primary" role="status" v-if="loadingBookingSummary">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <dl class="mb-0" v-else>
                        <dt class="fs-3 fw-bold">@{{ formatNumber(newBooking) }}</dt>
                        <dd class="fs-sm fw-medium fs-sm fw-medium text-muted mb-0">Total Booking</dd>
                    </dl>
                    <div class="item item-rounded-lg bg-body-light">
                        <i class="fa fa-bell fs-3 text-info"></i>
                    </div>
                </div>
                <div class="bg-body-light rounded-bottom">
                    <a class="block-content block-content-full block-content-sm fs-sm fw-medium d-flex align-items-center justify-content-between" href="javascript:void(0)">
                        <span></span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- END Booking Summary -->

    <!-- Overview Section 2 -->
    <div class="row items-push">
        <div class="col-xs-12  col-sm-4 col-xxl-4">
            <!-- Total Customer -->
            <div class="block block-rounded d-flex flex-column h-100 mb-0">
                <div
                    class="block-content block-content-full flex-grow-1 d-flex justify-content-between align-items-center">
                    <div class="spinner-border text-primary" role="status" v-if="loadingTotalCustomer">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <dl class="mb-0" v-else>
                        <dt class="fs-3 fw-bold">@{{ formatNumber(totalCustomerMale) }}</dt>
                        <dd class="fs-sm fw-medium fs-sm fw-medium text-muted mb-0">Customer Pria</dd>
                    </dl>
                    <div class="item item-rounded-lg bg-body-light">
                        <i class="fa fa-male fs-3" style="color: blue"></i>
                    </div>
                     <div class="spinner-border text-primary" role="status" v-if="loadingTotalCustomer">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <dl class="mb-0" v-else>
                        <dt class="fs-3 fw-bold">@{{ formatNumber(totalCustomerFemale) }}</dt>
                        <dd class="fs-sm fw-medium fs-sm fw-medium text-muted mb-0">Customer Wanita</dd>
                    </dl>
                    <div class="item item-rounded-lg bg-body-light">
                        <i class="fa fa-female fs-3" style="color: pink"></i>
                    </div>
                </div>
                <div class="bg-body-light rounded-bottom">
                    <a class="block-content block-content-full block-content-sm fs-sm fw-medium d-flex align-items-center justify-content-between"
                       href="javascript:void(0)">
                        <span></span>
                    </a>
                </div>
            </div>
            <!-- END Total Customer -->
        </div>
        <div class="col-xs-12  col-sm-4 col-xxl-4">
            <!-- Total Transaksi -->
            <div class="block block-rounded d-flex flex-column h-100 mb-0">
                <div
                    class="block-content block-content-full flex-grow-1 d-flex justify-content-between align-items-center">
                    <div class="spinner-border text-primary" role="status" v-if="loadingTotalTransaction">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <dl class="mb-0" v-else>
                        <dt class="fs-3 fw-bold">@{{ formatNumber(totalTransaction) }}</dt>
                        <dd class="fs-sm fw-medium fs-sm fw-medium text-muted mb-0">Total Transaksi</dd>
                    </dl>
                    <div class="item item-rounded-lg bg-body-light">
                        <i class="far fa-money-bill-alt fs-3 text-success"></i>
                    </div>
                </div>
                <div class="bg-body-light rounded-bottom">
                    <a class="block-content block-content-full block-content-sm fs-sm fw-medium d-flex align-items-center justify-content-between"
                        href="javascript:void(0)">
                        <span></span>
                    </a>
                </div>
            </div>
            <!-- END Total Transaksi -->
        </div>
        <div class="col-xs-12  col-sm-4 col-xxl-4">
            <!-- Average Sale -->
            <div class="block block-rounded d-flex flex-column h-100 mb-0">
                <div
                    class="block-content block-content-full flex-grow-1 d-flex justify-content-between align-items-center">
                    <div class="spinner-border text-primary" role="status" v-if="loadingTotalAverageSale">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <dl class="mb-0" v-else>
                        <dt class="fs-3 fw-bold">@{{ formatCurrency(totalAverageSale) }}</dt>
                        <dd class="fs-sm fw-medium fs-sm fw-medium text-muted mb-0">Average Sale per Transaction</dd>
                    </dl>
                    <div class="item item-rounded-lg bg-body-light">
                        <i class="far fa-money-bill-alt fs-3 text-success"></i>
                    </div>
                </div>
                <div class="bg-body-light rounded-bottom">
                    <a class="block-content block-content-full block-content-sm fs-sm fw-medium d-flex align-items-center justify-content-between"
                        href="javascript:void(0)">
                        <span></span>
                    </a>
                </div>
            </div>
            <!-- END Average Sale -->
        </div>
    </div>
    <!-- END Overview Section 2 -->

    <!-- Overview Section 3 -->
    <div class="row items-push">
        <div class="col-sm-6 col-xxl-6">
            <!-- Pendapatan Spa -->
            <div class="block block-rounded d-flex flex-column h-100 mb-0">
                <div
                    class="block-content block-content-full flex-grow-1 d-flex justify-content-between align-items-center">
                    <div class="spinner-border text-primary" role="status" v-if="loadingPendapatanSpa">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <dl class="mb-0" v-else>
                        <dt class="fs-3 fw-bold">@{{ formatCurrency(totalPendapatanSpa) }}</dt>
                        <dd class="fs-sm fw-medium fs-sm fw-medium text-muted mb-0">Simi Spa, Massage, Reflexology</dd>
                    </dl>
                    <div class="item item-rounded-lg bg-body-light">
                        <i class="far fa-money-bill-alt fs-3 text-success"></i>
                    </div>
                </div>
                <div class="bg-body-light rounded-bottom">
                    <a class="block-content block-content-full block-content-sm fs-sm fw-medium d-flex align-items-center justify-content-between"
                       href="javascript:void(0)">
                        <span></span>
                    </a>
                </div>
            </div>
            <!-- END Pendapatan Spa -->
        </div>
        <div class="col-sm-6 col-xxl-6">
            <!-- Pendapatan Salon -->
            <div class="block block-rounded d-flex flex-column h-100 mb-0">
                <div
                    class="block-content block-content-full flex-grow-1 d-flex justify-content-between align-items-center">
                    <div class="spinner-border text-primary" role="status" v-if="loadingPendapatanSalon">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <dl class="mb-0" v-else>
                        <dt class="fs-3 fw-bold">@{{ formatCurrency(totalPendapatanSalon) }}</dt>
                        <dd class="fs-sm fw-medium fs-sm fw-medium text-muted mb-0">Simi Salon</dd>
                    </dl>
                    <div class="item item-rounded-lg bg-body-light">
                        <i class="far fa-money-bill-alt fs-3 text-success"></i>
                    </div>
                </div>
                <div class="bg-body-light rounded-bottom">
                    <a class="block-content block-content-full block-content-sm fs-sm fw-medium d-flex align-items-center justify-content-between"
                        href="javascript:void(0)">
                        <span></span>
                    </a>
                </div>
            </div>
            <!-- END Pendapatan Salon -->
        </div>
        <div class="col-xs-12  col-sm-4 col-xxl-4">
            <!-- Pendapatan Studio -->
            <div class="block block-rounded d-flex flex-column h-100 mb-0">
                <div
                    class="block-content block-content-full flex-grow-1 d-flex justify-content-between align-items-center">
                    <div class="spinner-border text-primary" role="status" v-if="loadingPendapatanStudio">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <dl class="mb-0" v-else>
                        <dt class="fs-3 fw-bold">@{{ formatCurrency(totalPendapatanStudio) }}</dt>
                        <dd class="fs-sm fw-medium fs-sm fw-medium text-muted mb-0">Simi Studio</dd>
                    </dl>
                    <div class="item item-rounded-lg bg-body-light">
                        <i class="far fa-money-bill-alt fs-3 text-success"></i>
                    </div>
                </div>
                <div class="bg-body-light rounded-bottom">
                    <a class="block-content block-content-full block-content-sm fs-sm fw-medium d-flex align-items-center justify-content-between"
                        href="javascript:void(0)">
                        <span></span>
                    </a>
                </div>
            </div>
            <!-- END Pendapatan Studio -->
        </div>
        <div class="col-xs-12  col-sm-4 col-xxl-4">
            <!-- Pendapatan Kafe -->
            <div class="block block-rounded d-flex flex-column h-100 mb-0">
                <div
                    class="block-content block-content-full flex-grow-1 d-flex justify-content-between align-items-center">
                    <div class="spinner-border text-primary" role="status" v-if="loadingPendapatanKafe">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <dl class="mb-0" v-else>
                        <dt class="fs-3 fw-bold">@{{ formatCurrency(totalPendapatanKafe) }}</dt>
                        <dd class="fs-sm fw-medium fs-sm fw-medium text-muted mb-0">Simi Kafe</dd>
                    </dl>
                    <div class="item item-rounded-lg bg-body-light">
                        <i class="far fa-money-bill-alt fs-3 text-success"></i>
                    </div>
                </div>
                <div class="bg-body-light rounded-bottom">
                    <a class="block-content block-content-full block-content-sm fs-sm fw-medium d-flex align-items-center justify-content-between"
                        href="javascript:void(0)">
                        <span></span>
                    </a>
                </div>
            </div>
            <!-- END Pendapatan Kafe -->
        </div>
        <div class="col-xs-12  col-sm-4 col-xxl-4">
            <!-- Pendapatan Sport -->
            <div class="block block-rounded d-flex flex-column h-100 mb-0">
                <div
                    class="block-content block-content-full flex-grow-1 d-flex justify-content-between align-items-center">
                    <div class="spinner-border text-primary" role="status" v-if="loadingPendapatanSport">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <dl class="mb-0" v-else>
                        <dt class="fs-3 fw-bold">@{{ formatCurrency(totalPendapatanSport) }}</dt>
                        <dd class="fs-sm fw-medium fs-sm fw-medium text-muted mb-0">Simi Sport</dd>
                    </dl>
                    <div class="item item-rounded-lg bg-body-light">
                        <i class="far fa-money-bill-alt fs-3 text-success"></i>
                    </div>
                </div>
                <div class="bg-body-light rounded-bottom">
                    <a class="block-content block-content-full block-content-sm fs-sm fw-medium d-flex align-items-center justify-content-between"
                        href="javascript:void(0)">
                        <span></span>
                    </a>
                </div>
            </div>
            <!-- END Pendapatan Sport -->
        </div>
    </div>
    <!-- END Overview Section 3 -->

    <div class="row">
        <div class="col-xl-6 col-xxl-6 d-flex flex-column">
            <!-- Sales Summary (last 7 days) -->
            <div class="block block-rounded flex-grow-1 d-flex flex-column">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Day of Week Gross Sales</h3>
                </div>
                <div class="block-content block-content-full flex-grow-1">
                    <div class="row" v-if="this.loadingDailyTransaction">
                        <div class="col-12 align-items-center d-flex justify-content-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                    <!-- Earnings Chart Container -->
                    <canvas id="chartjsDailySummary"></canvas>
                </div>
            </div>
            <!-- END Sales Summary (last 7 days) -->
        </div>
        <div class="col-xl-6 col-xxl-6 d-flex flex-column">
            <!-- Sales Summary (last 7 days) -->
            <div class="block block-rounded flex-grow-1 d-flex flex-column">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Hourly Gross Sales</h3>
                </div>
                <div class="block-content block-content-full flex-grow-1">
                    <div class="row" v-if="this.loadingHourlyTransaction">
                        <div class="col-12 align-items-center d-flex justify-content-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                    <!-- Earnings Chart Container -->
                    <canvas id="chartjsHourlySummary"></canvas>
                </div>
            </div>
            <!-- END Sales Summary (last 7 days) -->
        </div>
    </div>


    <!-- Category Volume n Sales -->
    <div class="row">
        <div class="col-xl-6 col-xxl-6 d-flex flex-column">
            <div class="block block-rounded flex-grow-1 d-flex flex-column">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Category by Volume</h3>
                </div>
                <div class="block-content block-content-full flex-grow-1">
                    <div class="row" v-if="loadingCategoryVolume">
                        <div class="col-12 align-items-center d-flex justify-content-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>

                    <div v-show="!loadingCategoryVolume">
                        <div style="height: 350px;" v-if="hasCategoryVolumeData">
                            <canvas id="chartjsCategoryVolume"></canvas>
                        </div>
                        <div v-else class="d-flex justify-content-center align-items-center" style="height: 350px;">
                            <div class="text-center text-muted">
                                <i class="fas fa-chart-pie fa-3x mb-3 opacity-25"></i>
                                <h5 class="mb-0">Belum ada data</h5>
                                <small>Data akan muncul setelah ada transaksi</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 col-xxl-6 d-flex flex-column">
            <div class="block block-rounded flex-grow-1 d-flex flex-column">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Category by Sales</h3>
                </div>
                <div class="block-content block-content-full flex-grow-1">
                    <div class="row" v-if="loadingCategorySales">
                        <div class="col-12 align-items-center d-flex justify-content-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>

                    <div v-show="!loadingCategorySales">
                        <div style="height: 350px;" v-if="hasCategorySalesData">
                            <canvas id="chartjsCategorySales"></canvas>
                        </div>
                        <div v-else class="d-flex justify-content-center align-items-center" style="height: 350px;">
                            <div class="text-center text-muted">
                                <i class="fas fa-chart-pie fa-3x mb-3 opacity-25"></i>
                                <h5 class="mb-0">Belum ada data</h5>
                                <small>Data akan muncul setelah ada transaksi</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Items -->
    <div class="row">
        <div class="col-12 col-xxl-4  mt-4">
            <div class="block block-rounded">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Top Items</h3>
                </div>
                <div class="block-content">
                    <div v-if="loadingTopItems" class="d-flex justify-content-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div v-else-if="topItems.length > 0">
                        <table class="table table-striped table-vcenter">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th class="text-center">Item Sold</th>
                                    <th class="text-end">Gross Sales</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in topItems">
                                    <td class="fw-semibold fs-sm">@{{ item.name }}</td>
                                    <td class="text-center fs-sm">@{{ item.items_sold }}</td>
                                    <td class="text-end fs-sm">@{{ formatCurrency(item.gross_sales) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-center py-5">
                        <p class="text-muted mb-0">Belum ada data</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xxl-8 mt-4">
            <div class="d-none d-md-block">
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Top Terapis</h3>
                    </div>
                    <div class="block-content">
                        <div v-if="loadingTopTerapis" class="d-flex justify-content-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        <div v-else-if="topTerapis.length > 0">
                            <table class="table table-striped table-vcenter">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Treatment (Qty)</th>
                                        <th>Treatment (Rp)</th>
                                        <th>Tambahan (Qty)</th>
                                        <th>Tambahan (Rp)</th>
                                        <th>Total (Qty)</th>
                                        <th>Total (RP)</th>
                                    </tr>
                                </thead>
                                <tbody style="font-size: 12px">
                                    <tr v-for="(item, index) in topTerapis" v-if="index < 10">
                                        <td class="fw-semibold">@{{ item.name }}</td>
                                        <td class="text-center">@{{ item.qty }}</td>
                                        <td class="text-end">@{{ formatCurrency(item.total_treatment) }}</td>
                                        <td class="text-center">@{{ item.qty_treatement_addon }}</td>
                                        <td class="text-end">@{{ formatCurrency(item.total_treatment_addon) }}</td>
                                        <td class="text-center">@{{ item.grandtotal_qty_treatment }}</td>
                                        <td class="text-end">@{{ formatCurrency(item.grandtotal_treatment) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="text-center py-5">
                            <p class="text-muted mb-0">Belum ada data</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-block d-md-none">
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Top Terapis</h3>
                    </div>
                    <div class="block-content">
                        <div v-if="loadingTopTerapis" class="d-flex justify-content-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        <div v-else-if="topTerapis.length > 0">
                            <table class="table table-striped table-vcenter">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Total (Qty)</th>
                                        <th>Total (RP)</th>
                                    </tr>
                                </thead>
                                <tbody style="font-size: 12px">
                                    <tr v-for="(item, index) in topTerapis" v-if="index < 10">
                                        <td class="fw-semibold">@{{ item.name }}</td>
                                        <td class="text-center">@{{ item.grandtotal_qty_treatment }}</td>
                                        <td class="text-end">@{{ formatCurrency(item.grandtotal_treatment) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="text-center py-5">
                            <p class="text-muted mb-0">Belum ada data</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row">
        <div class="col-xl-6 col-xxl-6 col-xs-12  col-sm-12 mt-2">
          <!-- Simi Salon Berdasarkan Qty -->
          <div class="block block-rounded flex-grow-1 d-flex flex-column">
            <div class="block-header block-header-default">
              <h3 class="block-title">Simi Salon Berdasarkan Qty</h3>
              {{-- <div class="float-right" style="display: inline-flex">
                <span class="m-auto">Cabang</span>
                &nbsp;
                <select id="warehouse" name="warehouse" class="form-select" v-model="warehouseSales" style="width: 200px">
                  <option value="0">Semua Gudang</option>
                  @foreach ($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                  @endforeach
                </select>
                &nbsp;
                <span class="m-auto">Tahun</span>
                &nbsp;
                <select class="form-select" v-model="selectedFilterYear">
                  <option v-for="item in chartFiltersYear" :value="item.value">
                    @{{ item.display }}</option>
                </select>
                &nbsp;
                <button class="btn btn-xs btn-primary" v-on:click="changeChartFilter">Filter</button>
              </div> --}}
            </div>

            <div class="block-content block-content-full flex-grow-1">
              <div class="row" v-if="loadingSalonQty">
                <div class="col-12 align-items-center d-flex justify-content-center">
                  <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                  </div>
                </div>
              </div>
              <!-- Earnings Chart Container -->
              {{-- <canvas id="chartjsSalonQty" v-else></canvas> --}}
              <canvas id="chartjsSalonQty" v-show="!loadingSalonQty"></canvas>
            </div>
          </div>
          <!-- END Simi Salon Berdasarkan Qty -->
        </div>
        <div class="col-xl-6 col-xxl-6 col-xs-12  col-sm-12 mt-2">
          <!-- Simi Salon Berdasarkan Qty -->
          <div class="block block-rounded flex-grow-1 d-flex flex-column">
            <div class="block-header block-header-default">
              <h3 class="block-title">Simi Salon Berdasarkan Nominal</h3>
            </div>

            <div class="block-content block-content-full flex-grow-1">
              <div class="row" v-if="loadingSalonNominal">
                <div class="col-12 align-items-center d-flex justify-content-center">
                  <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                  </div>
                </div>
              </div>
              <!-- Earnings Chart Container -->
              {{-- <canvas id="chartjsSalonNominal" v-else></canvas> --}}
              <canvas id="chartjsSalonNominal" v-show="!loadingSalonNominal"></canvas>
            </div>
          </div>
          <!-- END Simi Salon Berdasarkan Qty -->
        </div>


        {{-- <div class="col-xl-12 col-xxl-12 d-flex flex-column">
            <!-- Sales Summary -->
            <div class="block block-rounded flex-grow-1 d-flex flex-column">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Sales Summary</h3>
                    <div class="block-options">
                        <div class="dropdown d-inline-block">
                            <span>Filter</span>
                            <div class="row">
                                <div class="col-12">
                                    <select class="form-select" v-model="selectedFilterYear">
                                        <option v-for="item in chartFiltersYear" :value="item.value">
                                            @{{ item.display }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <button type="button" class="btn btn-primary col-12"
                                            v-on:click="changeChartFilter">Change Filter</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="block-content block-content-full flex-grow-1">
                    <div class="row" v-if="this.loadingTransactionTotal">
                        <div class="col-12 align-items-center d-flex justify-content-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                    <!-- Earnings Chart Container -->
                    <canvas id="chartjssales-summary"></canvas>
                </div>
            </div>
            <!-- END Sales Summary -->
        </div> --}}
    </div>
    <div class="row">
        <div class="col-xl-6 col-xxl-6 col-xs-12  col-sm-12 mt-2">
          <!-- Simi Kafe Berdasarkan Qty -->
          <div class="block block-rounded flex-grow-1 d-flex flex-column">
            <div class="block-header block-header-default">
              <h3 class="block-title">Simi Kafe Berdasarkan Qty</h3>
              {{-- <div class="float-right" style="display: inline-flex">
                <span class="m-auto">Cabang</span>
                &nbsp;
                <select id="warehouse" name="warehouse" class="form-select" v-model="warehouseSales" style="width: 200px">
                  <option value="0">Semua Gudang</option>
                  @foreach ($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                  @endforeach
                </select>
                &nbsp;
                <span class="m-auto">Tahun</span>
                &nbsp;
                <select class="form-select" v-model="selectedFilterYear">
                  <option v-for="item in chartFiltersYear" :value="item.value">
                    @{{ item.display }}</option>
                </select>
                &nbsp;
                <button class="btn btn-xs btn-primary" v-on:click="changeChartFilter">Filter</button>
              </div> --}}
            </div>

            <div class="block-content block-content-full flex-grow-1">
              <div class="row" v-if="loadingKafeQty">
                <div class="col-12 align-items-center d-flex justify-content-center">
                  <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                  </div>
                </div>
              </div>
              <!-- Earnings Chart Container -->
              {{-- <canvas id="chartjsKafeQty" v-else></canvas> --}}
              <canvas id="chartjsKafeQty" v-show="!loadingKafeQty"></canvas>
            </div>
          </div>
          <!-- END Simi Kafe Berdasarkan Qty -->
        </div>
        <div class="col-xl-6 col-xxl-6 col-xs-12  col-sm-12 mt-2">
          <!-- Simi Kafe Berdasarkan Nominal -->
          <div class="block block-rounded flex-grow-1 d-flex flex-column">
            <div class="block-header block-header-default">
              <h3 class="block-title">Simi Kafe Berdasarkan Nominal</h3>
            </div>

            <div class="block-content block-content-full flex-grow-1">
              <div class="row" v-if="loadingKafeNominal">
                <div class="col-12 align-items-center d-flex justify-content-center">
                  <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                  </div>
                </div>
              </div>
              <!-- Earnings Chart Container -->
              {{-- <canvas id="chartjsKafeNominal" v-else></canvas> --}}
              <canvas id="chartjsKafeNominal" v-show="!loadingKafeNominal"></canvas>
            </div>
          </div>
          <!-- END Simi Kafe Berdasarkan Nominal -->
        </div>

    </div>
    <div class="row">
        <div class="col-xl-6 col-xxl-6 col-xs-12  col-sm-12 mt-2">
          <!-- Simi Spa dll Berdasarkan Qty -->
          <div class="block block-rounded flex-grow-1 d-flex flex-column">
            <div class="block-header block-header-default">
              <h3 class="block-title">Simi Spa, Massage, Reflexology Berdasarkan Qty</h3>
            </div>

            <div class="block-content block-content-full flex-grow-1">
              <div class="row" v-if="loadingSpaQty">
                <div class="col-12 align-items-center d-flex justify-content-center">
                  <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                  </div>
                </div>
              </div>
              <!-- Earnings Chart Container -->
              {{-- <canvas id="chartjsSpaQty" v-else></canvas> --}}
              <canvas id="chartjsSpaQty" v-show="!loadingSpaQty"></canvas>
            </div>
          </div>
          <!-- END Simi Spa dll Berdasarkan Qty -->
        </div>
        <div class="col-xl-6 col-xxl-6 col-xs-12  col-sm-12 mt-2">
          <!-- Simi Spa dll Berdasarkan Nominal -->
          <div class="block block-rounded flex-grow-1 d-flex flex-column">
            <div class="block-header block-header-default">
              <h3 class="block-title">Simi Spa, Massage, Reflexology Berdasarkan Nominal</h3>
            </div>

            <div class="block-content block-content-full flex-grow-1">
              <div class="row" v-if="loadingSpaNominal">
                <div class="col-12 align-items-center d-flex justify-content-center">
                  <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                  </div>
                </div>
              </div>
              <!-- Earnings Chart Container -->
              {{-- <canvas id="chartjsSpaNomimal" v-else></canvas> --}}
              <canvas id="chartjsSpaNominal" v-show="!loadingSpaNominal"></canvas>
            </div>
          </div>
          <!-- END Simi Spa dll Berdasarkan Nominal -->
        </div>

    </div>

    <!-- END Statistics -->
</div>
<!-- End Dashboard Content -->
@endsection

@section('styles')
    <!-- Page JS Plugins CSS -->
    <link rel="stylesheet" href="{{ asset('js/plugins/flatpickr/flatpickr.css') }}">
    <style>
        @media (max-width: 768px) {
            body {
                padding-bottom: 110px; /* beri ruang agar filter bar tidak menutup konten */
            }
        }
    </style>
@endsection
@section('scripts')
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <!-- Page JS Plugins -->
    <script src="{{ asset('js/plugins/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('js/plugins/chart.js/Chart.min.js') }}"></script>
    <script src="{{ asset('js/moment.min.js') }}"></script>
    <!-- Page JS Code -->
    <script src="{{ asset('js/pages/be_pages_dashboard.min.js') }}"></script>

    <script src="{{ asset('js/axios.js') }}"></script>
    <script src="{{ asset('js/vue.js') }}"></script>
    <script src="https://unpkg.com/vue-toasted"></script>
    <script>
        let currDate = new Date();

        const yyyy = currDate.getFullYear();
        let mm = currDate.getMonth() + 1; // Months start at 0!
        let dd = currDate.getDate();

        if (dd < 10) dd = '0' + dd;
        if (mm < 10) mm = '0' + mm;

        const formattedToday = dd + '/' + mm + '/' + yyyy;

        let app = new Vue({
            el: '#app',
            data: {
                loadingTotalGrossSales: false,
                totalGrossSales: 0,
                loadingTotalNetSales: false,
                totalNetSales: 0,
                loadingTotalDiscount: false,
                totalDiscount: 0,

                loadingTotalCustomer: false,
                totalCustomerMale: 0,
                totalCustomerFemale: 0,
                loadingBookingSummary: false,
                todayBooking: 0,
                bookingNotApprove: 0,
                newBooking: 0,
                loadingTotalTransaction: false,
                totalTransaction: 0,
                loadingTotalAverageSale: false,
                totalAverageSale: 0,

                loadingPendapatanSpa: false,
                totalPendapatanSpa: 0,
                loadingPendapatanSalon: false,
                totalPendapatanSalon: 0,
                loadingPendapatanStudio: false,
                totalPendapatanStudio: 0,
                loadingPendapatanKafe: false,
                totalPendapatanKafe: 0,
                loadingPendapatanSport: false,
                totalPendapatanSport: 0,

                loadingDailyTransaction: false,
                dailyOmset: [],
                loadingHourlyTransaction: false,
                hourlyOmset: [],

                loadingSalonQty: false,
                loadingSalonNominal: false,
                loadingKafeQty: false,
                loadingKafeNominal: false,
                loadingSpaQty: false,
                loadingSpaNominal: false,
                // Category
                loadingCategoryVolume: false,
                loadingCategorySales: false,
                hasCategoryVolumeData: true,
                hasCategorySalesData: true,

                // Top Items
                loadingTopItems: false,
                topItems: [],
                // Top Terapis
                loadingTopTerapis: false,
                topTerapis: [],
                omsetBranch: [],
                selectedFilterMonth: {{ $chosenFilterMonth }},
                selectedFilterYear: {{ $chosenFilterYear }},
                chartFiltersMonth: {!! $filterMonths !!},
                chartFiltersYear: {!! $filterYears !!},
            },
            methods: {
                async getTotalGrossSales() {
                    this.loadingTotalGrossSales = true;
                    await axios({
                        method: 'get',
                        url: '{{ route('admin.dashboard.get_total_gross') }}' + '?start_date=' + '{{ $startDate }}' + '&end_date=' + '{{ $endDate }}',
                    }).then(function(response) {
                        if (response) {
                            if (response.status === 200) {
                                app.totalGrossSales = response.data;
                                //this.totalFocCustomer = response.data.totalFocCustomer;
                            } else {}
                        }
                    });
                    this.loadingTotalGrossSales = false;
                },
                async getTotalNetSales() {
                    this.loadingTotalNetSales = true;
                    await axios({
                        method: 'get',
                        url: '{{ route('admin.dashboard.get_total_net') }}' + '?start_date=' + '{{ $startDate }}' + '&end_date=' + '{{ $endDate }}',
                    }).then(function(response) {
                        if (response) {
                            if (response.status === 200) {
                                app.totalNetSales = response.data;
                                //this.totalFocCustomer = response.data.totalFocCustomer;
                            } else {}
                        }
                    });
                    this.loadingTotalNetSales = false;
                },
                async getTotalDiscount() {
                    this.loadingTotalDiscount = true;
                    await axios({
                        method: 'get',
                        url: '{{ route('admin.dashboard.get_total_discount') }}' + '?start_date=' + '{{ $startDate }}' + '&end_date=' + '{{ $endDate }}',
                    }).then(function(response) {
                        if (response) {
                            if (response.status === 200) {
                                app.totalDiscount = response.data;
                                //this.totalFocCustomer = response.data.totalFocCustomer;
                            } else {}
                        }
                    });
                    this.loadingTotalDiscount = false;
                },

                async getTotalCustomer() {
                    this.loadingTotalCustomer = true;
                    await axios({
                        method: 'get',
                        url: '{{ route('admin.dashboard.get_total_customer') }}' + '?start_date=' + '{{ $startDate }}' + '&end_date=' + '{{ $endDate }}',
                    }).then(function(response) {
                        if (response) {
                            if (response.status === 200) {
                                app.totalCustomerMale = response.data.male;
                                app.totalCustomerFemale = response.data.female;
                                //this.totalFocCustomer = response.data.totalFocCustomer;
                            } else {}
                        }
                    });
                    this.loadingTotalCustomer = false;
                },
                async getBookingSummary() {
                    this.loadingBookingSummary = true;
                    await axios({
                        method: 'get',
                        url: '{{ route('admin.dashboard.get_booking_summary') }}',
                    }).then(function(response) {
                        if (response && response.status === 200) {
                            app.todayBooking = response.data.today_booking;
                            app.bookingNotApprove = response.data.booking_not_approve;
                            app.newBooking = response.data.new_booking;
                        }
                    });
                    this.loadingBookingSummary = false;
                },

                async getTotalTransaction() {
                    this.loadingTotalTransaction = true;
                    await axios({
                        method: 'get',
                        url: '{{ route('admin.dashboard.get_total_transaction') }}' + '?start_date=' + '{{ $startDate }}' + '&end_date=' + '{{ $endDate }}',
                    }).then(function(response) {
                        if (response) {
                            if (response.status === 200) {
                                app.totalTransaction = response.data;
                                //this.totalFocCustomer = response.data.totalFocCustomer;
                            } else {}
                        }
                    });
                    this.loadingTotalTransaction = false;
                },
                async getTotalAverage() {
                    this.loadingTotalAverageSale = true;
                    await axios({
                        method: 'get',
                        url: '{{ route('admin.dashboard.get_total_average') }}' + '?start_date=' + '{{ $startDate }}' + '&end_date=' + '{{ $endDate }}',
                    }).then(function(response) {
                        if (response) {
                            if (response.status === 200) {
                                app.totalAverageSale = response.data;
                                //this.totalFocCustomer = response.data.totalFocCustomer;
                            } else {}
                        }
                    });
                    this.loadingTotalAverageSale = false;
                },

                async getTransaction() {
                    this.loadingTransactionTotal = true;
                    await axios({
                        method: 'get',
                        url: '{{ route('admin.dashboard.get_transactions') }}?year=' + app
                            .selectedFilterYear,
                    }).then(function(response) {
                        if (response) {
                            if (response.status === 200) {
                                app.omsetBranch = response.data;
                                //this.totalFocCustomer = response.data.totalFocCustomer;
                            } else {}
                        }
                    });
                    this.loadingTransactionTotal = false;
                },
                async changeChartFilter() {
                    await app.getTransaction();
                    app.generateChart();
                },
                async getPendapatan(chartType) {
                    let information = 0;
                    switch(chartType){
                        case "SpaNominal" : this.loadingPendapatanSpa = true; break;
                        case "SalonNominal" : this.loadingPendapatanSalon = true; break;
                        case "KafeNominal" : this.loadingPendapatanKafe = true; break;
                        case "SportNominal" : this.loadingPendapatanSport = true; break;
                        case "StudioNominal" : this.loadingPendapatanStudio = true; break;
                    }
                    await axios({
                        method: 'get',
                        url: '{{ route('admin.dashboard.get_pendapatan') }}?type=' + chartType + '&start_date=' + '{{ $startDate }}' + '&end_date=' + '{{ $endDate }}'
                    }).then(function(response) {
                        if (response) {
                            if (response.status === 200) {
                                information = response.data;
                                switch(chartType){
                                    case "SpaNominal" : app.totalPendapatanSpa = information; break;
                                    case "SalonNominal" : app.totalPendapatanSalon = information; break;
                                    case "KafeNominal" : app.totalPendapatanKafe = information; break;
                                    case "SportNominal" : app.totalPendapatanSport = information; break;
                                    case "StudioNominal" : app.totalPendapatanStudio = information; break;
                                }
                            } else {}
                        }
                    });
                    switch(chartType){
                        case "SpaNominal" : this.loadingPendapatanSpa = false; break;
                        case "SalonNominal" : this.loadingPendapatanSalon = false; break;
                        case "KafeNominal" : this.loadingPendapatanKafe = false; break;
                        case "SportNominal" : this.loadingPendapatanSport = false; break;
                        case "StudioNominal" : this.loadingPendapatanStudio = false; break;
                    }
                },
                async generateLineChart(chartType) {
                    let labels = [];
                    let datas = [];
                    let information = null;

                    switch(chartType){
                        case "DailySummary" : this.loadingDailyTransaction = true; break;
                        case "HourlySummary" : this.loadingHourlyTransaction = true; break;
                    }

                    await axios({
                        method: 'get',
                        url: '{{ route('admin.dashboard.get_transaction_linechart') }}?type=' + chartType + '&start_date=' + '{{ $startDate }}' + '&end_date=' + '{{ $endDate }}',
                    }).then(function(response) {
                        if (response) {
                            if (response.status === 200) {
                                information = response.data;
                                for (let i = 0; i < information.length; i++) {
                                    labels.push(information[i].labels);
                                    datas.push(information[i].amount);
                                }
                                //this.totalFocCustomer = response.data.totalFocCustomer;
                            } else {}
                        }
                    });
                    var graphColor = "rgba(255, 215, 0, 0.3)";
                    var chartLabel = "";
                    switch(chartType){
                        case "DailySummary" : this.loadingDailyTransaction = false; break;
                        case "HourlySummary" : this.loadingHourlyTransaction = false; graphColor = "rgba(0, 0, 255, 0.3)"; break;
                    }
                    var chartName = "chartjs" + chartType;
                    console.log(chartName);

                    const ctx = document.getElementById(chartName).getContext('2d');
                    const myChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Gross Sales',
                                data: datas,
                                borderColor: graphColor,
                                tension: 0.1,
                                pointHoverRadius: 5,
                                pointRadius: 3,
                            }]
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                },
                async generateChart(chartType) {
                    let labels = [];
                    let datas = [];
                    let information = null;

                    switch(chartType){
                        case "SalonQty" : this.loadingSalonQty = true; break;
                        case "SalonNominal" : this.loadingSalonNominal = true; break;
                        case "KafeQty" : this.loadingKafeQty = true; break;
                        case "KafeNominal" : this.loadingKafeNominal = true; break;
                        case "SpaQty" : this.loadingSpaQty = true; break;
                        case "SpaNominal" : this.loadingSpaNominal = true; break;
                    }

                    await axios({
                        method: 'get',
                        url: '{{ route('admin.dashboard.get_transactions') }}?type=' + chartType + '&start_date=' + '{{ $startDate }}' + '&end_date=' + '{{ $endDate }}',
                    }).then(function(response) {
                        if (response) {
                            if (response.status === 200) {
                                information = response.data;
                                for (let i = 0; i < information.length; i++) {
                                    labels.push(information[i].labels);
                                    datas.push(information[i].amount);
                                }
                                //this.totalFocCustomer = response.data.totalFocCustomer;
                            } else {}
                        }
                    });
                    var graphColor = "rgba(255, 215, 0, 0.3)";
                    switch(chartType){
                        case "SalonQty" : this.loadingSalonQty = false; break;
                        case "SalonNominal" : this.loadingSalonNominal = false; break;
                        case "KafeQty" : this.loadingKafeQty = false; graphColor = "rgba(100, 255, 100, 0.3)"; break;
                        case "KafeNominal" : this.loadingKafeNominal = false; graphColor = "rgba(100, 255, 100, 0.3)"; break;
                        case "SpaQty" : this.loadingSpaQty = false; graphColor = "rgba(0, 0, 255, 0.3)"; break;
                        case "SpaNominal" : this.loadingSpaNominal = false; graphColor = "rgba(0, 0, 255, 0.3)"; break;
                    }
                    var chartName = "chartjs" + chartType;
                    console.log(chartName);

                    const ctx = document.getElementById(chartName).getContext('2d');
                    const myChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Transactions',
                                data: datas,
                                backgroundColor: [ graphColor ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                },
                async generatePieChart(summaryType) {
                    let chartId, loadingFlag, dataFlag;
                    if (summaryType === 'volume') {
                        chartId = 'chartjsCategoryVolume';
                        loadingFlag = 'loadingCategoryVolume';
                        dataFlag = 'hasCategoryVolumeData';
                    } else {
                        chartId = 'chartjsCategorySales';
                        loadingFlag = 'loadingCategorySales';
                        dataFlag = 'hasCategorySalesData';
                    }

                    this[loadingFlag] = true;

                    let labels = [];
                    let datas = [];

                    // // --- START DUMMY DATA CODE ---

                    // const dummyData = [
                    //     { labels: 'Spa', amount: (summaryType === 'sales' ? 15000000 : 75) },
                    //     { labels: 'Massage', amount: (summaryType === 'sales' ? 12000000 : 65) },
                    //     { labels: 'Reflexology', amount: (summaryType === 'sales' ? 8000000 : 45) },
                    //     { labels: 'Facial', amount: (summaryType === 'sales' ? 18000000 : 85) },
                    //     { labels: 'Salon', amount: (summaryType === 'sales' ? 8500000 : 120) },
                    //     { labels: 'Studio', amount: (summaryType === 'sales' ? 4000000 : 50) },
                    //     { labels: 'Kafe', amount: (summaryType === 'sales' ? 6200000 : 250) },
                    // ];

                    // for (let i = 0; i < dummyData.length; i++) {
                    //     labels.push(dummyData[i].labels);
                    //     datas.push(dummyData[i].amount);
                    // }

                    // // --- END DUMMY DATA CODE ---

                    // API
                    let information = [];
                    const startDate = document.getElementById('start_date').value;
                    const endDate = document.getElementById('end_date').value;
                    try {
                        const response = await axios({
                            method: 'get',
                            url: `{{ route('admin.dashboard.get_category_summary') }}?type=${summaryType}&start_date=${startDate}&end_date=${endDate}`,
                        });
                        if (response.status === 200) {
                            information = response.data;
                            for (let i = 0; i < information.length; i++) {
                                labels.push(information[i].labels);
                                datas.push(information[i].amount);
                            }
                        }
                    } catch (error) {
                        console.error(`Error fetching data for ${summaryType}:`, error);
                    }

                    this[loadingFlag] = false;

                    if (datas && datas.length > 0) {
                        this[dataFlag] = true;

                        this.$nextTick(() => {
                            const canvas = document.getElementById(chartId);
                            if (!canvas) return;

                            Chart.getChart(canvas)?.destroy();

                            new Chart(canvas.getContext('2d'), {
                                type: 'pie',
                                data: {
                                    labels: labels,
                                    datasets: [{
                                        label: 'Summary',
                                        data: datas,
                                        backgroundColor: [
                                            'rgba(255, 99, 132, 0.7)', 'rgba(54, 162, 235, 0.7)',
                                            'rgba(255, 206, 86, 0.7)', 'rgba(75, 192, 192, 0.7)',
                                            'rgba(153, 102, 255, 0.7)', 'rgba(255, 159, 64, 0.7)',
                                            'rgba(25, 159, 64, 0.7)'
                                        ],
                                        borderWidth: 1
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: { position: 'right' },
                                        tooltip: {
                                            callbacks: {
                                                label: function(context) {
                                                    let label = context.label || '';
                                                    if (label) { label += ': '; }
                                                    if (context.parsed !== null) {
                                                        if (summaryType === 'sales') {
                                                            label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(context.parsed);
                                                        } else {
                                                            label += new Intl.NumberFormat('id-ID').format(context.parsed);
                                                        }
                                                    }
                                                    return label;
                                                }
                                            }
                                        }
                                    }
                                }
                            });
                        });
                    } else {
                        this[dataFlag] = false;
                    }
                },
                formatCurrency(value) {
                    if (isNaN(value)) return 'Rp. 0';
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        minimumFractionDigits: 0
                    }).format(value);
                },
                formatNumber(value) {
                    if (isNaN(value)) return '0';
                    return new Intl.NumberFormat('id-ID').format(value);
                },
                async getTopItems() {
                    this.loadingTopItems = true;

                    // // --- START DUMMY DATA CODE ---
                    // const dummyTopItems = [
                    //     { name: 'M1. Massage Tradisional 90Mnt', items_sold: 11, gross_sales: 1760000 },
                    //     { name: 'Zumba 60Mnt', items_sold: 10, gross_sales: 318180 },
                    //     { name: 'S3. Simi Absolute Spa 120Mnt', items_sold: 5, gross_sales: 1150000 },
                    //     { name: 'R2. Refleksi Komplit 90Mnt', items_sold: 4, gross_sales: 560000 },
                    //     { name: 'F1. Simi Facial Tradisional 60Mnt', items_sold: 4, gross_sales: 400000 },
                    //     { name: 'S4. Simi Beauty Spa 70Mnt', items_sold: 3, gross_sales: 540000 },
                    //     { name: 'Lemon Sereh Hot', items_sold: 2, gross_sales: 30000 },
                    //     { name: 'T5. Scrub Badan (Lulur) 20Mnt', items_sold: 2, gross_sales: 140000 },
                    //     { name: 'T14. Tambahan Waktu 30 Menit', items_sold: 2, gross_sales: 100000 },
                    //     { name: 'M2. Massage Tradisional 120Mnt', items_sold: 2, gross_sales: 380000 },
                    // ];

                    // this.topItems = dummyTopItems;
                    // --- END DUMMY DATA CODE ---

                    const startDate = document.getElementById('start_date').value;
                    const endDate = document.getElementById('end_date').value;

                    try {
                        const response = await axios.get(`{{ route('admin.dashboard.get_top_items') }}?start_date=${startDate}&end_date=${endDate}`);
                        this.topItems = response.data;
                    } catch (error) {
                        console.error("Error fetching top items:", error);
                        this.topItems = [];
                    }

                    setTimeout(() => {
                        this.loadingTopItems = false;
                    }, 300);
                },
                async getTopTerapis() {
                    this.loadingTopTerapis = true;

                    const startDate = document.getElementById('start_date').value;
                    const endDate = document.getElementById('end_date').value;

                    try {
                        $.ajax({
                            url: '{{ route('admin.rating_partner.report.generate') }}',
                            type: 'post',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            },
                            data: {
                                start_date: startDate,
                                end_date: endDate,
                            },
                            success: function(response) {
                                app.topTerapis = response;
                            },
                            error: function(xhr, status, error) {
                                console.log(error);
                            }
                        })
                    } catch (error) {
                        console.error("Error fetching top items:", error);
                        this.topTerapis = [];
                    }

                    setTimeout(() => {
                        this.loadingTopTerapis = false;
                    }, 300);
                },
            },
            async mounted() {
                this.getTotalGrossSales();
                this.getTotalNetSales();
                this.getTotalDiscount();
                await this.getBookingSummary();

                await this.getTotalCustomer();
                await this.getTotalTransaction();
                await this.getTotalAverage();

                await this.getPendapatan("SpaNominal");
                await this.getPendapatan("SalonNominal");
                await this.getPendapatan("StudioNominal");
                await this.getPendapatan("KafeNominal");
                await this.getPendapatan("SportNominal");


                await this.generateLineChart("DailySummary");
                await this.generateLineChart("HourlySummary");

                await this.generatePieChart("volume");
                await this.generatePieChart("sales");

                await this.getTopItems();
                await this.getTopTerapis();

                await this.generateChart("SalonQty");
                await this.generateChart("SalonNominal");
                await this.generateChart("KafeQty");
                await this.generateChart("KafeNominal");
                await this.generateChart("SpaQty");
                await this.generateChart("SpaNominal");
            }
        });
    </script>
    <script>
        flatpickr('.js-flatpickr', {
            "locale": "id" // locale for this instance only
        });

        function filterData() {
            let startDate = $('#start_date').val();
            let endDate = $('#end_date').val();
            let url = '{{ route('admin.dashboard') }}';

            window.location = url + '?start_date=' + startDate + '&end_date=' + endDate;
        }
        function filterDataMobile() {
            let startDate = $('#start_date_mobile').val();
            let endDate = $('#end_date_mobile').val();
            let url = '{{ route('admin.dashboard') }}';

            window.location = url + '?start_date=' + startDate + '&end_date=' + endDate;
        }

    </script>
@endsection
