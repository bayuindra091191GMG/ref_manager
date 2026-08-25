
<!-- Navigation -->
<div class="bg-primary-darker print_hide d-print-none">
    <div class="content py-3">
        <!-- Toggle Main Navigation -->
        <div class="d-lg-none">
            <!-- Class Toggle, functionality initialized in Helpers.oneToggleClass() -->
            <button type="button" class="btn w-100 btn-alt-secondary d-flex justify-content-between align-items-center" data-toggle="class-toggle" data-target="#main-navigation" data-class="d-none">
                Menu
                <i class="fa fa-bars"></i>
            </button>
        </div>
        <!-- END Toggle Main Navigation -->

        <!-- Main Navigation -->
        <div id="main-navigation" class="d-none d-lg-block mt-2 mt-lg-0">
            <div class="row">
                <div class="col-lg-11">
                    <ul class="nav-main nav-main-dark nav-main-horizontal nav-main-hover">
                        <li class="nav-main-item">
                            <a class="nav-main-link {{ \Illuminate\Support\Facades\Route::is('admin.dashboard*') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                                <i class="nav-main-link-icon si si-compass"></i>
                                <span class="nav-main-link-name">Dashboard</span>
                            </a>
                        </li>

                        @if($authorizedModules->contains('SUPER ADMIN') ||
                                        $authorizedModules->contains('Master Data - SEMUA') ||
                                        $authorizedModules->contains('Master Data - Profit Center - SEMUA') ||
                                        $authorizedModules->contains('Master Data - Customer - SEMUA') ||
                                        $authorizedModules->contains('Master Data - Karyawan - SEMUA') ||
                                        $authorizedModules->contains('Master Data - Departemen - SEMUA') ||
                                        $authorizedModules->contains('Master Data - Jabatan - SEMUA') ||
                                        $authorizedModules->contains('Master Data - Supplier - SEMUA'))
                        <li class="nav-main-item">
                            <a class="nav-main-link nav-main-link-submenu {{ \Illuminate\Support\Facades\Route::is('admin.customer*') ||
                                     \Illuminate\Support\Facades\Route::is('admin.profit_center*') ||
                                     \Illuminate\Support\Facades\Route::is('admin.employee*') ||
                                     \Illuminate\Support\Facades\Route::is('admin.department*') ||
                                     \Illuminate\Support\Facades\Route::is('admin.supplier*') ||
                                     \Illuminate\Support\Facades\Route::is('admin.position*') ? 'active' : '' }}"
                               data-toggle="submenu" aria-haspopup="true" aria-expanded="false" href="#">
                                <i class="nav-main-link-icon si si-list"></i>
                                <span class="nav-main-link-name">Master Data</span>
                            </a>
                            <ul class="nav-main-submenu">

                                @if($authorizedModules->contains('SUPER ADMIN') ||
                                    $authorizedModules->contains('Master Data - SEMUA') ||
                                    $authorizedModules->contains('Master Data - Profit Center - SEMUA'))
                                    <li class="nav-main-item">
                                        <a class="nav-main-link" href="{{ route('admin.profit_center.index') }}">
                                            <span class="nav-main-link-name">Profit Center</span>
                                        </a>
                                    </li>
                                @endif

                                @if($authorizedModules->contains('SUPER ADMIN') ||
                                    $authorizedModules->contains('Master Data - SEMUA') ||
                                    $authorizedModules->contains('Master Data - Customer - SEMUA'))
                                    <li class="nav-main-item">
                                        <a class="nav-main-link" href="{{ route('admin.customer.index') }}">
                                            <span class="nav-main-link-name">Customer</span>
                                        </a>
                                    </li>
                                @endif

                                @if($authorizedModules->contains('SUPER ADMIN') ||
                                    $authorizedModules->contains('Master Data - SEMUA') ||
                                    $authorizedModules->contains('Master Data - Karyawan - SEMUA'))
                                    <li class="nav-main-item">
                                        <a class="nav-main-link" href="{{ route('admin.employee.index') }}">
                                            <span class="nav-main-link-name">Karyawan</span>
                                        </a>
                                    </li>
                                @endif

                                @if($authorizedModules->contains('SUPER ADMIN') ||
                                    $authorizedModules->contains('Master Data - SEMUA') ||
                                    $authorizedModules->contains('Master Data - Departemen - SEMUA'))
                                    <li class="nav-main-item">
                                        <a class="nav-main-link" href="{{ route('admin.department.index') }}">
                                            <span class="nav-main-link-name">Departemen</span>
                                        </a>
                                    </li>
                                @endif

                                @if($authorizedModules->contains('SUPER ADMIN') ||
                                    $authorizedModules->contains('Master Data - SEMUA') ||
                                    $authorizedModules->contains('Master Data - Jabatan - SEMUA'))
                                    <li class="nav-main-item">
                                        <a class="nav-main-link" href="{{ route('admin.employee_position.index') }}">
                                            <span class="nav-main-link-name">Jabatan</span>
                                        </a>
                                    </li>
                                @endif

                                @if($authorizedModules->contains('SUPER ADMIN') ||
                                    $authorizedModules->contains('Master Data - SEMUA') ||
                                    $authorizedModules->contains('Master Data - Supplier - SEMUA'))
                                    <li class="nav-main-item">
                                        <a class="nav-main-link" href="{{ route('admin.supplier.index') }}">
                                            <span class="nav-main-link-name">Supplier</span>
                                        </a>
                                    </li>
                                @endif

                            </ul>
                        </li>
                        @endif
                        @if($authorizedModules->contains('SUPER ADMIN') ||
                                        $authorizedModules->contains('Barang - SEMUA') ||
                                        $authorizedModules->contains('Barang - Kategori Barang - SEMUA') ||
                                        $authorizedModules->contains('Barang - Master Barang - SEMUA') ||
                                        $authorizedModules->contains('Barang - Unit Material - SEMUA')||
                                        $authorizedModules->contains('Barang - Gudang - SEMUA')||
                                        $authorizedModules->contains('Barang - Transfer Barang - SEMUA')||
                                        $authorizedModules->contains('Barang - Report - SEMUA')||
                                        $authorizedModules->contains('Barang - Inventori - SEMUA'))
                            <li class="nav-main-item">
                                <a class="nav-main-link nav-main-link-submenu {{ \Illuminate\Support\Facades\Route::is('admin.item_category*') ||
                                    \Illuminate\Support\Facades\Route::is('admin.locker*') ||
                                     \Illuminate\Support\Facades\Route::is('admin.item*') ||
                                     \Illuminate\Support\Facades\Route::is('admin.uom*') ||
                                     \Illuminate\Support\Facades\Route::is('admin.warehouse*') ||
                                     \Illuminate\Support\Facades\Route::is('admin.item_transfer*') ? 'active' : '' }}" data-toggle="submenu" aria-haspopup="true" aria-expanded="false" href="#">
                                    <i class="nav-main-link-icon fa fa-warehouse"></i>
                                    <span class="nav-main-link-name">Barang</span>
                                </a>
                                <ul class="nav-main-submenu">
{{--                                    <li class="nav-main-item">--}}
                                        {{--                                    <a class="nav-main-link" href="#">--}}
                                        {{--                                        <span class="nav-main-link-name">Barang Masuk</span>--}}
                                        {{--                                    </a>--}}
                                        {{--                                    <a class="nav-main-link" href="#">--}}
                                        {{--                                        <span class="nav-main-link-name">Barang Keluar</span>--}}
                                        {{--                                    </a>--}}
                                        {{--                                    <a class="nav-main-link" href="#">--}}
                                        {{--                                        <span class="nav-main-link-name">Histori</span>--}}
                                        {{--                                    </a>--}}
{{--                                    </li>--}}
                                   @if($authorizedModules->contains('SUPER ADMIN') ||
                                            $authorizedModules->contains('Barang - SEMUA') ||
                                            $authorizedModules->contains('Barang - Kategori Barang - SEMUA'))
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="{{ route('admin.item_category.index') }}">
                                                <span class="nav-main-link-name">Kategori Barang</span>
                                            </a>
                                        </li>
                                    @endif


                                    @if($authorizedModules->contains('SUPER ADMIN') ||
                                            $authorizedModules->contains('Barang - SEMUA') ||
                                            $authorizedModules->contains('Barang - Master Barang - SEMUA'))
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="{{ route('admin.item.index') }}">
                                                <span class="nav-main-link-name">Master Barang</span>
                                            </a>
                                        </li>
                                    @endif

                                    @if($authorizedModules->contains('SUPER ADMIN') ||
                                        $authorizedModules->contains('Barang - SEMUA') ||
                                        $authorizedModules->contains('Barang - Unit Material - SEMUA'))
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="{{ route('admin.uom.index') }}">
                                                <span class="nav-main-link-name">Unit Material</span>
                                            </a>
                                        </li>
                                    @endif

                                    @if($authorizedModules->contains('SUPER ADMIN') ||
                                        $authorizedModules->contains('Barang - SEMUA') ||
                                        $authorizedModules->contains('Barang - Gudang - SEMUA'))
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="{{ route('admin.warehouse.index') }}">
                                                <span class="nav-main-link-name">Gudang</span>
                                            </a>
                                        </li>
                                    @endif

                                    @if($authorizedModules->contains('SUPER ADMIN') ||
                                        $authorizedModules->contains('Barang - SEMUA') ||
                                        $authorizedModules->contains('Barang - Transfer Barang - SEMUA'))
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="{{route('admin.item_transfer.index')}}">
                                                <span class="nav-main-link-name">Transfer Barang</span>
                                            </a>
                                        </li>
                                    @endif

                                    @if($authorizedModules->contains('SUPER ADMIN') ||
                                        $authorizedModules->contains('Barang - SEMUA') ||
                                        $authorizedModules->contains('Barang - Stock Card - SEMUA'))
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="{{ route('admin.stock_card.index') }}">
                                                <span class="nav-main-link-name">Stock Card</span>
                                            </a>
                                        </li>
                                    @endif

                                    {{-- @if($authorizedModules->contains('SUPER ADMIN') ||
                                        $authorizedModules->contains('Barang - SEMUA') ||
                                        $authorizedModules->contains('Barang - Stock Card - SEMUA'))
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="{{ route('admin.opname.index') }}">
                                                <span class="nav-main-link-name">Stock Opname</span>
                                            </a>
                                        </li>
                                    @endif --}}

                                    @if($authorizedModules->contains('SUPER ADMIN') ||
                                        $authorizedModules->contains('Barang - SEMUA') ||
                                        $authorizedModules->contains('Barang - Inventori - SEMUA'))
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="{{ route('admin.inventory.index') }}">
                                                <span class="nav-main-link-name">Inventori</span>
                                            </a>
                                        </li>
                                    @endif

                                    @if($authorizedModules->contains('SUPER ADMIN') ||
                                        $authorizedModules->contains('Barang - SEMUA') ||
                                        $authorizedModules->contains('Barang - Inventori In - SEMUA'))
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="{{ route('admin.inventory_in.index') }}">
                                                <span class="nav-main-link-name">Inventori In</span>
                                            </a>
                                        </li>
                                    @endif

                                    @if($authorizedModules->contains('SUPER ADMIN') ||
                                        $authorizedModules->contains('Barang - SEMUA') ||
                                        $authorizedModules->contains('Barang - Inventori Out - SEMUA'))
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="{{ route('admin.inventory_out.index') }}">
                                                <span class="nav-main-link-name">Inventori Out</span>
                                            </a>
                                        </li>
                                    @endif

                                   @if($authorizedModules->contains('SUPER ADMIN') ||
                                        $authorizedModules->contains('Barang - SEMUA') ||
                                        $authorizedModules->contains('Barang - Report - SEMUA'))
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="{{ route('admin.report.stock.filter') }}">
                                                <span class="nav-main-link-name">Laporan Stock Barang</span>
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif
                        
                        @if($authorizedModules->contains('SUPER ADMIN') ||
                                        $authorizedModules->contains('Purchasing - SEMUA') ||
                                        $authorizedModules->contains('Purchasing - Internal Request - SEMUA') ||
                                        $authorizedModules->contains('Purchasing - Purchase Request - SEMUA') ||
                                        $authorizedModules->contains('Purchasing - Purchase Order - SEMUA') ||
                                        $authorizedModules->contains('Purchasing - Goods Receive - SEMUA') ||
                                        $authorizedModules->contains('Purchasing - Supplier Invoice - SEMUA')||
                                        $authorizedModules->contains('Purchasing - Laporan - SEMUA'))
                            <li class="nav-main-item">
                                <a class="nav-main-link nav-main-link-submenu {{ \Illuminate\Support\Facades\Route::is('admin.internal_request*') ||
                                    \Illuminate\Support\Facades\Route::is('admin.purchase_request*') ||
                                     \Illuminate\Support\Facades\Route::is('admin.purchase_order*') ||
                                     \Illuminate\Support\Facades\Route::is('admin.good_receive*') ||
                                     \Illuminate\Support\Facades\Route::is('admin.supplier_invoice*') ? 'active' : '' }}"
                                    data-toggle="submenu" aria-haspopup="true" aria-expanded="false" href="#">
                                    <i class="nav-main-link-icon fa fa-money-bill-wave-alt"></i>
                                    <span class="nav-main-link-name">Purchasing</span>
                                </a>
                                <ul class="nav-main-submenu">
                                   @if($authorizedModules->contains('SUPER ADMIN') ||
                                            $authorizedModules->contains('Purchasing - SEMUA') ||
                                            $authorizedModules->contains('Purchasing - Internal Request - SEMUA'))
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="{{route('admin.internal_request.index')}}">
                                                <span class="nav-main-link-name">Internal Request</span>
                                            </a>
                                        </li>
                                    @endif

                                    @if($authorizedModules->contains('SUPER ADMIN') ||
                                        $authorizedModules->contains('Purchasing - SEMUA') ||
                                        $authorizedModules->contains('Purchasing - Purchase Request - SEMUA'))
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="{{route('admin.purchase_request.index')}}">
                                                <span class="nav-main-link-name">Purchase Request</span>
                                            </a>
                                        </li>
                                    @endif

                                    @if($authorizedModules->contains('SUPER ADMIN') ||
                                        $authorizedModules->contains('Purchasing - SEMUA') ||
                                        $authorizedModules->contains('Purchasing - Purchase Order - SEMUA'))
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="{{route('admin.purchase_order.index')}}">
                                                <span class="nav-main-link-name">Purchase Order</span>
                                            </a>
                                        </li>
                                    @endif

                                    @if($authorizedModules->contains('SUPER ADMIN') ||
                                        $authorizedModules->contains('Purchasing - SEMUA') ||
                                        $authorizedModules->contains('Purchasing - Goods Receive - SEMUA'))
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="{{route('admin.good_receive.index')}}">
                                                <span class="nav-main-link-name">Goods Receive</span>
                                            </a>
                                        </li>
                                    @endif

                                    @if($authorizedModules->contains('SUPER ADMIN') ||
                                        $authorizedModules->contains('Purchasing - SEMUA') ||
                                        $authorizedModules->contains('Purchasing - Supplier Invoice - SEMUA'))
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="{{route('admin.supplier_invoice.index')}}">
                                                <span class="nav-main-link-name">Supplier Invoice</span>
                                            </a>
                                        </li>
                                    @endif

                                    @if($authorizedModules->contains('SUPER ADMIN') ||
                                        $authorizedModules->contains('Purchasing - SEMUA') ||
                                        $authorizedModules->contains('Purchasing - Laporan - SEMUA'))
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="{{route('admin.pr.report.filter')}}">
                                                <span class="nav-main-link-name">Laporan Purchase Request</span>
                                            </a>
                                        </li>
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="{{route('admin.po.report.filter')}}">
                                                <span class="nav-main-link-name">Laporan Purchase Order</span>
                                            </a>
                                        </li>
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="{{route('admin.good_receive.report')}}">
                                                <span class="nav-main-link-name">Laporan Goods Receive</span>
                                            </a>
                                        </li>
                                    @endif

                                    {{-- @if($authorizedModules->contains('SUPER ADMIN') ||
                                        $authorizedModules->contains('Purchasing - SEMUA') ||
                                        $authorizedModules->contains('Purchasing - Supplier Invoice - SEMUA'))
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="{{route('admin.si.report.filter')}}">
                                                <span class="nav-main-link-name">Laporan Supplier Invoice</span>
                                            </a>
                                        </li>
                                    @endif --}}
                                </ul>
                            </li>
                        @endif

                        
                        @if($authorizedModules->contains('SUPER ADMIN') ||
                                $authorizedModules->contains('Sales - SEMUA') ||
                                $authorizedModules->contains('Sales - Quotation - SEMUA') ||
                                $authorizedModules->contains('Sales - Sales Order - SEMUA') ||
                                $authorizedModules->contains('Sales - Delivery Order - SEMUA') ||
                                $authorizedModules->contains('Sales - Sales Invoice - SEMUA')||
                                $authorizedModules->contains('Sales - Sales Return - SEMUA') ||
                                $authorizedModules->contains('Sales - Laporan - SEMUA'))
                            <li class="nav-main-item">
                                <a class="nav-main-link nav-main-link-submenu {{ \Illuminate\Support\Facades\Route::is('admin.quotation*') ||
                                     \Illuminate\Support\Facades\Route::is('admin.sales_order*') ||
                                     \Illuminate\Support\Facades\Route::is('admin.delivery_order*') ||
                                     \Illuminate\Support\Facades\Route::is('admin.sales_invoice*') ? 'active' : '' }}"
                                   data-toggle="submenu" aria-haspopup="true" aria-expanded="false" href="#">
                                    <i class="nav-main-link-icon fa fa-money-bill-wave-alt"></i>
                                    <span class="nav-main-link-name">Sales</span>
                                </a>
                                <ul class="nav-main-submenu">

                                    {{-- @if($authorizedModules->contains('SUPER ADMIN') ||
                                        $authorizedModules->contains('Sales - SEMUA') ||
                                        $authorizedModules->contains('Sales - Quotation - SEMUA'))
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="{{route('admin.sales_quotation.index')}}">
                                                <span class="nav-main-link-name">Sales Quotation</span>
                                            </a>
                                        </li>
                                    @endif --}}

                                    @if($authorizedModules->contains('SUPER ADMIN') ||
                                        $authorizedModules->contains('Sales - SEMUA') ||
                                        $authorizedModules->contains('Sales - Sales Order - SEMUA'))
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="{{route('admin.sales_order.index')}}">
                                                <span class="nav-main-link-name">Sales Order</span>
                                            </a>
                                        </li>
                                    @endif

                                    @if($authorizedModules->contains('SUPER ADMIN') ||
                                        $authorizedModules->contains('Sales - SEMUA') ||
                                        $authorizedModules->contains('Sales - Delivery Order - SEMUA'))
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="{{route('admin.delivery_order.index')}}">
                                                <span class="nav-main-link-name">Delivery Order</span>
                                            </a>
                                        </li>
                                    @endif

                                    @if($authorizedModules->contains('SUPER ADMIN') ||
                                        $authorizedModules->contains('Sales - SEMUA') ||
                                        $authorizedModules->contains('Sales - Sales Invoice - SEMUA'))
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="{{route('admin.sales_invoice.index')}}">
                                                <span class="nav-main-link-name">Sales Invoice</span>
                                            </a>
                                        </li>
                                    @endif

                                    @if($authorizedModules->contains('SUPER ADMIN') ||
                                        $authorizedModules->contains('Sales - SEMUA') ||
                                        $authorizedModules->contains('Sales - Sales Return - SEMUA'))
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="{{route('admin.sales_return.index')}}">
                                                <span class="nav-main-link-name">Sales Return</span>
                                            </a>
                                        </li>
                                    @endif

                                    @if($authorizedModules->contains('SUPER ADMIN') ||
                                        $authorizedModules->contains('Sales - SEMUA') ||
                                        $authorizedModules->contains('Sales - Laporan - SEMUA'))
                                        {{-- <li class="nav-main-item">
                                            <a class="nav-main-link" href="{{route('admin.sales.report.filter')}}">
                                                <span class="nav-main-link-name">Laporan Sales</span>
                                            </a>
                                        </li> --}}
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="{{route('admin.sales.report.filter')}}">
                                                <span class="nav-main-link-name">Laporan Sales Order</span>
                                            </a>
                                        </li>
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="{{route('admin.invoice.report.filter')}}">
                                                <span class="nav-main-link-name">Laporan Invoice Penjualan</span>
                                            </a>
                                        </li>
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="{{route('admin.invoice_issued.report.filter')}}">
                                                <span class="nav-main-link-name">Laporan Invoice Issued</span>
                                            </a>
                                        </li>
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="{{route('admin.price.report.filter')}}">
                                                <span class="nav-main-link-name">Laporan History Penjualan</span>
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif

                        @if($authorizedModules->contains('SUPER ADMIN') ||
                                        $authorizedModules->contains('Production - SEMUA') ||
                                        $authorizedModules->contains('Production - Manufacture Order - SEMUA') ||
                                        $authorizedModules->contains('Production - Production Raw Material - SEMUA') ||
                                        $authorizedModules->contains('Production - Production Finish Good - SEMUA') ||
                                        $authorizedModules->contains('Production - Laporan - SEMUA'))
                            <li class="nav-main-item">
                                <a class="nav-main-link nav-main-link-submenu {{ \Illuminate\Support\Facades\Route::is('admin.manufacture_order*') ||
                                    \Illuminate\Support\Facades\Route::is('admin.purchase_request*') ||
                                     \Illuminate\Support\Facades\Route::is('admin.purchase_order*') ||
                                     \Illuminate\Support\Facades\Route::is('admin.good_receive*') ||
                                     \Illuminate\Support\Facades\Route::is('admin.supplier_invoice*') ? 'active' : '' }}"
                                    data-toggle="submenu" aria-haspopup="true" aria-expanded="false" href="#">
                                    <i class="nav-main-link-icon fa fa-money-bill-wave-alt"></i>
                                    <span class="nav-main-link-name">Production</span>
                                </a>
                                <ul class="nav-main-submenu">
                                   @if($authorizedModules->contains('SUPER ADMIN') ||
                                            $authorizedModules->contains('Purchasing - SEMUA') ||
                                            $authorizedModules->contains('Purchasing - Manufacture Order - SEMUA'))
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="#">
                                                <span class="nav-main-link-name">Manufacture Order</span>
                                            </a>
                                        </li>
                                    @endif

                                    @if($authorizedModules->contains('SUPER ADMIN') ||
                                        $authorizedModules->contains('Purchasing - SEMUA') ||
                                        $authorizedModules->contains('Purchasing - Production Move Out - SEMUA'))
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="#">
                                                <span class="nav-main-link-name">Production Raw Material</span>
                                            </a>
                                        </li>
                                    @endif

                                    @if($authorizedModules->contains('SUPER ADMIN') ||
                                        $authorizedModules->contains('Purchasing - SEMUA') ||
                                        $authorizedModules->contains('Purchasing - Production Finish Good - SEMUA'))
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="#">
                                                <span class="nav-main-link-name">Production Finish Good</span>
                                            </a>
                                        </li>
                                    @endif

                                    @if($authorizedModules->contains('SUPER ADMIN') ||
                                        $authorizedModules->contains('Purchasing - SEMUA') ||
                                        $authorizedModules->contains('Purchasing - Laporan - SEMUA'))
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="#">
                                                <span class="nav-main-link-name">Laporan Manufacture Order</span>
                                            </a>
                                        </li>
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="#">
                                                <span class="nav-main-link-name">Laporan Finish Good</span>
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif

                        @if($authorizedModules->contains('SUPER ADMIN') ||
                            $authorizedModules->contains('Finance - SEMUA') ||
                            $authorizedModules->contains('Finance - Master COA - SEMUA') ||
                            $authorizedModules->contains('Finance - Bank Cash Out - SEMUA') ||
                            $authorizedModules->contains('Finance - Bank Cash In - SEMUA') ||
                            $authorizedModules->contains('Finance - Invoice Manual - SEMUA') ||
                            $authorizedModules->contains('Finance - Acc Payment - SEMUA')||
                            $authorizedModules->contains('Finance - Acc Receive - SEMUA'))
                            <li class="nav-main-item">
                                <a class="nav-main-link nav-main-link-submenu
                                {{ \Illuminate\Support\Facades\Route::is('admin.coa.*') ||
                                    \Illuminate\Support\Facades\Route::is('admin.bank_cash_out.*') ||
                                    \Illuminate\Support\Facades\Route::is('admin.bank_cash_in.*') ||
                                    \Illuminate\Support\Facades\Route::is('admin.invoice_manual.*') ||
                                    \Illuminate\Support\Facades\Route::is('admin.ap.*') ? ' active' : '' }}"
                                    data-toggle="submenu" aria-haspopup="true" aria-expanded="false" href="#">
                                    <i class="nav-main-link-icon fa fa-money-check"></i>
                                    <span class="nav-main-link-name">Finance</span>
                                </a>
                                <ul class="nav-main-submenu">
                                    @if($authorizedModules->contains('SUPER ADMIN') ||
                                        $authorizedModules->contains('Finance - SEMUA') ||
                                        $authorizedModules->contains('Finance - Master COA - SEMUA'))
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="{{ route('admin.coa.index') }}">
                                                <span class="nav-main-link-name">Master COA</span>
                                            </a>
                                        </li>
                                    @endif

                                    @if($authorizedModules->contains('SUPER ADMIN') ||
                                        $authorizedModules->contains('Finance - SEMUA') ||
                                        $authorizedModules->contains('Finance - Bank Cash Out - SEMUA'))
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="{{route('admin.bank_cash_out.index')}}">
                                                <span class="nav-main-link-name">Bank Cash Out</span>
                                            </a>
                                        </li>
                                    @endif

                                    @if($authorizedModules->contains('SUPER ADMIN') ||
                                        $authorizedModules->contains('Finance - SEMUA') ||
                                        $authorizedModules->contains('Finance - Bank Cash In - SEMUA'))
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="{{route('admin.bank_cash_in.index')}}">
                                                <span class="nav-main-link-name">Bank Cash In</span>
                                            </a>
                                        </li>
                                    @endif

                                    @if($authorizedModules->contains('SUPER ADMIN') ||
                                        $authorizedModules->contains('Finance - SEMUA') ||
                                        $authorizedModules->contains('Finance - Advance Payment - SEMUA'))
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="{{ route('admin.adv.index') }}">
                                                <span class="nav-main-link-name">Advance Payment</span>
                                            </a>
                                        </li>
                                    @endif

{{--                                    @if($authorizedModules->contains('SUPER ADMIN') ||--}}
{{--                                        $authorizedModules->contains('Finance - SEMUA') ||--}}
{{--                                        $authorizedModules->contains('Finance - Invoice Manual - SEMUA'))--}}
{{--                                        <li class="nav-main-item">--}}
{{--                                            <a class="nav-main-link" href="{{route('admin.invoice_manual.index')}}">--}}
{{--                                                <span class="nav-main-link-name">Invoice Manual</span>--}}
{{--                                            </a>--}}
{{--                                        </li>--}}
{{--                                    @endif--}}

                                    @if($authorizedModules->contains('SUPER ADMIN') ||
                                        $authorizedModules->contains('Finance - SEMUA') ||
                                        $authorizedModules->contains('Finance - Acc Payment - SEMUA'))
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="{{route('admin.ap.index')}}">
                                                <span class="nav-main-link-name">Acc Payment</span>
                                            </a>
                                        </li>
                                        <li class="nav-main-item">
                                            <a class="nav-main-link nav-main-link-submenu" href="#" data-toggle="submenu" aria-haspopup="true" aria-expanded="false">
                                                <span class="nav-main-link-name">Laporan AP</span>
                                            </a>
                                            <ul class="nav-main-submenu">
                                                <li class="nav-main-item">
                                                    <a class="nav-main-link" href="{{ route('admin.ap.report.filter', ['type' => 'ap_card']) }}">
                                                        <span class="nav-main-link-name">Laporan AP Card</span>
                                                    </a>
                                                </li>
                                                <li class="nav-main-item">
                                                    <a class="nav-main-link" href="{{ route('admin.ap.report.filter', ['type' => 'ap_aging']) }}">
                                                        <span class="nav-main-link-name">Laporan AP Aging</span>
                                                    </a>
                                                </li>
                                                <li class="nav-main-item">
                                                    <a class="nav-main-link" href="{{ route('admin.ap.report.filter', ['type' => 'ap_invoice_list']) }}">
                                                        <span class="nav-main-link-name">Laporan AP Invoice List</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>
                                    @endif

                                    @if($authorizedModules->contains('SUPER ADMIN') ||
                                        $authorizedModules->contains('Finance - SEMUA') ||
                                        $authorizedModules->contains('Finance - Advance Receive - SEMUA'))
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="{{ route('admin.advance_receive.index') }}">
                                                <span class="nav-main-link-name">Advance Receive</span>
                                            </a>
                                        </li>
                                    @endif

                                    @if($authorizedModules->contains('SUPER ADMIN') ||
                                        $authorizedModules->contains('Finance - SEMUA') ||
                                        $authorizedModules->contains('Finance - Acc Receive - SEMUA'))
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="{{route('admin.ar.index')}}">
                                                <span class="nav-main-link-name">Acc Receive</span>
                                            </a>
                                        </li>
                                        <li class="nav-main-item">
                                            <a class="nav-main-link nav-main-link-submenu" href="#" data-toggle="submenu" aria-haspopup="true" aria-expanded="false">
                                                <span class="nav-main-link-name">Laporan AR</span>
                                            </a>
                                            <ul class="nav-main-submenu">
                                                <li class="nav-main-item">
                                                    <a class="nav-main-link" href="{{ route('admin.ar.report.filter', ['type' => 'ar_card']) }}">
                                                        <span class="nav-main-link-name">Laporan AR Card</span>
                                                    </a>
                                                </li>
                                                <li class="nav-main-item">
                                                    <a class="nav-main-link" href="{{ route('admin.ar.report.filter', ['type' => 'ar_aging']) }}">
                                                        <span class="nav-main-link-name">Laporan AR Aging</span>
                                                    </a>
                                                </li>
                                                <li class="nav-main-item">
                                                    <a class="nav-main-link" href="{{ route('admin.ar.report.filter', ['type' => 'ar_invoice_list']) }}">
                                                        <span class="nav-main-link-name">Laporan AR Invoice List</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif

                        @if($authorizedModules->contains('SUPER ADMIN') ||
                                        $authorizedModules->contains('General Ledger - SEMUA') ||
                                        $authorizedModules->contains('General Ledger - Journal Manual - SEMUA') ||
                                        $authorizedModules->contains('General Ledger - Laporan - SEMUA'))
                            <li class="nav-main-item">
                                <a class="nav-main-link nav-main-link-submenu {{ \Illuminate\Support\Facades\Route::is('admin.journal*') ||
                                    \Illuminate\Support\Facades\Route::is('admin.general_ledger*')  ? 'active' : '' }}"
                                   data-toggle="submenu" aria-haspopup="true" aria-expanded="false" href="#">
                                    <i class="nav-main-link-icon fa fa-money-bill-wave-alt"></i>
                                    <span class="nav-main-link-name">General Ledger</span>
                                </a>
                                <ul class="nav-main-submenu">
                                    @if($authorizedModules->contains('SUPER ADMIN') ||
                                            $authorizedModules->contains('General Ledger - SEMUA') ||
                                            $authorizedModules->contains('General Ledger - Journal Manual - SEMUA'))
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="{{route('admin.journal.index')}}">
                                                <span class="nav-main-link-name">Journal Manual</span>
                                            </a>
                                        </li>
                                    @endif

                                    @if($authorizedModules->contains('SUPER ADMIN') ||
                                        $authorizedModules->contains('General Ledger - SEMUA') ||
                                        $authorizedModules->contains('General Ledger - Laporan - SEMUA'))
                                        <li class="nav-main-item">
                                            <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true" aria-expanded="false" href="#">
                                                <span class="nav-main-link-name">Laporan</span>
                                            </a>
                                            <ul class="nav-main-submenu">
                                                <li class="nav-main-item">
                                                    <a class="nav-main-link" href="{{ route('admin.general_ledger.report.filter', ['type' => 'Cashflow']) }}">
                                                        <span class="nav-main-link-name">Laporan Cashflow</span>
                                                    </a>
                                                </li>
                                                <li class="nav-main-item">
                                                    <a class="nav-main-link" href="{{ route('admin.general_ledger.report.filter', ['type' => 'Trial Balance']) }}">
                                                        <span class="nav-main-link-name">Laporan Trial Balance</span>
                                                    </a>
                                                </li>
                                                <li class="nav-main-item">
                                                    <a class="nav-main-link" href="{{ route('admin.general_ledger.report.filter', ['type' => 'Profit and Loss']) }}">
                                                        <span class="nav-main-link-name">Laporan Profit And Loss</span>
                                                    </a>
                                                </li>
                                                <li class="nav-main-item">
                                                    <a class="nav-main-link" href="{{ route('admin.general_ledger.report.filter', ['type' => 'Balance Sheet']) }}">
                                                        <span class="nav-main-link-name">Laporan Balance Sheet</span>
                                                    </a>
                                                </li>
                                                <li class="nav-main-item">
                                                    <a class="nav-main-link" href="{{ route('admin.general_ledger.report.filter', ['type' => 'Ledger']) }}">
                                                        <span class="nav-main-link-name">Laporan Ledger</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif

                        @if($authorizedModules->contains('SUPER ADMIN') ||
                            $authorizedModules->contains('Pengaturan - SEMUA') ||
                            $authorizedModules->contains('Pengaturan - User Admin - SEMUA') ||
                            $authorizedModules->contains('Pengaturan - Ijin Akses - SEMUA') )
                            <li class="nav-main-item">
                                <a class="nav-main-link nav-main-link-submenu
                                {{ \Illuminate\Support\Facades\Route::is('admin.auth.module*') ||
                                   \Illuminate\Support\Facades\Route::is('admin.commission.position.item*') ||
                                   \Illuminate\Support\Facades\Route::is('admin.commission.position.partner_level*')? ' active' : '' }}"
                                   data-toggle="submenu" aria-haspopup="true" aria-expanded="false" href="#">
                                    <i class="nav-main-link-icon fa fa-cog"></i>
                                    <span class="nav-main-link-name">Pengaturan</span>
                                </a>
                                <ul class="nav-main-submenu">
                                    @if($authorizedModules->contains('SUPER ADMIN') ||
                                    $authorizedModules->contains('Pengaturan - SEMUA') ||
                                    $authorizedModules->contains('Pengaturan - User Admin - SEMUA'))
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="{{ route('admin.admin_user.index') }}">
                                                <span class="nav-main-link-name">User Admin</span>
                                            </a>
                                        </li>
                                    @endif

                                    @if($authorizedModules->contains('SUPER ADMIN') ||
                                        $authorizedModules->contains('Pengaturan - SEMUA') ||
                                        $authorizedModules->contains('Pengaturan - Ijin Akses - SEMUA'))
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="{{ route('admin.auth.module.index') }}">
                                                <span class="nav-main-link-name">Ijin Akses</span>
                                            </a>
                                        </li>
                                    @endif

                                </ul>
                            </li>
                        @endif

                    </ul>
                </div>
                <div class="col-lg-1 text-end">
                    <div class="dropdown d-inline-block ms-2">
                        <button type="button" class="btn btn-sm btn-alt-secondary" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
{{--                            <img class="rounded" src="https://images.pexels.com/photos/1239291/pexels-photo-1239291.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1" alt="Header Avatar" style="width: 21px;" />--}}
{{--                            <span class="d-none d-sm-inline-block ms-1">Charlote</span>--}}
                            <i class="fa fa-fw fa-angle-down d-none d-sm-inline-block"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-md dropdown-menu-end p-0 border-0" aria-labelledby="page-header-user-dropdown">
                            <div class="p-2">
                                <a class="dropdown-item d-flex align-items-center justify-content-between" href="{{ route('admin.my.change_password') }}">
                                    <span class="fs-sm fw-medium">Change Password</span>
                                </a>

                                <a class="dropdown-item d-flex align-items-center justify-content-between"
                                   onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                                    <span class="fs-sm fw-medium">Log Out</span>
                                    <form id="logout-form" method="POST" action="{{ route('admin.logout') }}">
                                        {{csrf_field()}}
                                    </form>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END Main Navigation -->
    </div>
</div>
<!-- END Navigation -->
