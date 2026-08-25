
<div class="content d-lg-none d-flex align-items-center print_hide d-print-none">
    <button type="button" class="btn btn-sm btn-alt-secondary me-2 print_hide" data-toggle="layout" data-action="sidebar_toggle">
        <i class="fa fa-fw fa-bars"></i>
    </button>
</div>

<!-- Navigation -->
<div id="sidebar" aria-label="Main Navigation" class="print_hide d-print-none shadow-sm">
    <div class="content-header">
        <a class="fw-bold text-dual d-flex align-items-center justify-content-center" href="{{ route('admin.dashboard') }}">
            <span class="smini-visible">
              <i class="fa fa-circle-notch text-primary"></i>
            </span>
            <img src="{{ asset('images/logo-only.png') }}" alt="" class="smini-hide mt-3" style="width: 40%; height: auto">
        </a>
        <div>
            <a class="d-lg-none btn btn-sm btn-alt-secondary ms-1" data-toggle="layout" data-action="sidebar_close" href="javascript:void(0)">
                <i class="fa fa-fw fa-times"></i>
            </a>
        </div>
    </div>

    <div class="js-sidebar-scroll">
        <div class="content-side">
            <ul class="nav-main">
                <li class="nav-main-heading text-center" style="font-size: 14px; font-weight: 800; line-height: 1.2;">{{ auth()->guard('admin')->user()->branch->name }}</li>

                <li class="nav-main-item">
                    <a class="nav-main-link {{ \Illuminate\Support\Facades\Route::is('admin.dashboard*') ? 'active' : '' }}"
                       href="{{ route('admin.dashboard') }}">
                        <i class="nav-main-link-icon fa fa-dashboard"></i>
                        <span class="nav-main-link-name">Dashboard</span>
                    </a>
                </li>

                @if(auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1 ||
                       (auth()->guard('admin')->user()->hasAnyPermission([
                           'transaction.list',
                            'room.list',
                           'booking.list',
                           'monitor.list',
                           'prewedding.list',
                           'trx_preview.show',
                           'voucher_order.list',
                    ])))
                    <li class="nav-main-item">
                        <a class="nav-main-link nav-main-link-submenu
                                    {{ \Illuminate\Support\Facades\Route::is('admin.cashier*') ||
                                    \Illuminate\Support\Facades\Route::is('admin.monitor*') ||
                                    \Illuminate\Support\Facades\Route::is('admin.kitchen*') ||
                                    \Illuminate\Support\Facades\Route::is('admin.booking*') ||
                                    \Illuminate\Support\Facades\Route::is('admin.booking_notes*') ||
                                    \Illuminate\Support\Facades\Route::is('admin.forecast*') ? 'active' : '' }}"
                           data-toggle="submenu" aria-haspopup="true" aria-expanded="false" href="#">
                            <i class="nav-main-link-icon fa fa-desktop"></i>
                            <span class="nav-main-link-name">POS</span>
                        </a>
                        <ul class="nav-main-submenu">
                            @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1 ||
                                   (auth()->guard('admin')->user()->hasAnyPermission([
                                       'transaction.list'
                                ])))
                                <li class="nav-main-item">
                                    <a class="nav-main-link" href="{{ route('admin.monitor_room.index') }}">
                                        <span class="nav-main-link-name">Transaksi Baru - Treatment</span>
                                    </a>
                                </li>
                            @endif

                                @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1 ||
                                       (auth()->guard('admin')->user()->hasAnyPermission([
                                           'transaction.list'
                                    ])))
                                    <li class="nav-main-item">
                                        <a class="nav-main-link" href="{{ route('admin.voucher_pos.index') }}">
                                            <span class="nav-main-link-name">Transaksi Baru - Voucher</span>
                                        </a>
                                    </li>
                                @endif

                                @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1 ||
                                   (auth()->guard('admin')->user()->hasAnyPermission([
                                       'transaction.list',
                                ])))
                                    <li class="nav-main-item">
                                        <a class="nav-main-link" href="{{ route('admin.prewedding.index') }}">
                                            <span class="nav-main-link-name">Transaksi Baru - Paket</span>
                                        </a>
                                    </li>
                                @endif

                                @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1 ||
                                       (auth()->guard('admin')->user()->hasAnyPermission([
                                           'booking.list',
                                    ])))
                                    <li class="nav-main-item">
                                        <a class="nav-main-link" href="{{ route('admin.booking.index') }}">
                                            <span class="nav-main-link-name">Booking</span>
                                        </a>
                                    </li>
                                @endif
                                @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                                        (auth()->guard('admin')->user()->hasAnyPermission(['voucher_order.list'])))
                                    <li class="nav-main-item">
                                        <a class="nav-main-link" href="{{ route('admin.voucher_order.index') }}">
                                            <span class="nav-main-link-name">Transaksi Voucher (Aplikasi)</span>
                                        </a>
                                    </li>
                                @endif

{{--                            @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1)--}}
{{--                                <li class="nav-main-item">--}}
{{--                                    <a class="nav-main-link" href="{{ route('admin.cashier.index') }}">--}}
{{--                                        <span class="nav-main-link-name">Kasir</span>--}}
{{--                                    </a>--}}
{{--                                </li>--}}
{{--                            @endif--}}

{{--                            @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1 ||--}}
{{--                                   (auth()->guard('admin')->user()->hasAnyPermission([--}}
{{--                                       'trx_preview.show',--}}
{{--                                ])))--}}
{{--                                <li class="nav-main-item">--}}
{{--                                    <a class="nav-main-link" href="{{ route('admin.pos.trx_preview') }}">--}}
{{--                                        <span class="nav-main-link-name">Tampilan Pembayaran</span>--}}
{{--                                    </a>--}}
{{--                                </li>--}}
{{--                            @endif--}}

                            @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1 ||
                                   (auth()->guard('admin')->user()->hasAnyPermission([
                                       'monitor.list',
                                ])))
                                <li class="nav-main-item">
                                    <a class="nav-main-link" href="{{ route('admin.monitor.index') }}">
                                        <span class="nav-main-link-name">Monitor</span>
                                    </a>
                                </li>
                            @endif

{{--                            @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1)--}}
{{--                                <li class="nav-main-item">--}}
{{--                                    <a class="nav-main-link" href="{{ route('admin.monitor.screen') }}">--}}
{{--                                        <span class="nav-main-link-name">Monitor Screen</span>--}}
{{--                                    </a>--}}
{{--                                </li>--}}
{{--                            @endif--}}
                        </ul>
                    </li>
                @endif

                @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1 ||
                    (auth()->guard('admin')->user()->hasAnyPermission([
                        'branch.list',
                        'room.list',
                        'banner.list',
                        'voucher.list',
                        'partner.list',
                        'treatment.list',
                        'position.list',
                        'employee.list',
                        'supplier.list',
                        'member.list',
                    ])))
                    <li class="nav-main-item">
                        <a class="nav-main-link nav-main-link-submenu {{ \Illuminate\Support\Facades\Route::is('admin.branch*') ||
                            \Illuminate\Support\Facades\Route::is('admin.room*') ||
                            \Illuminate\Support\Facades\Route::is('admin.employee*') ||
                            \Illuminate\Support\Facades\Route::is('admin.department*') ||
                            \Illuminate\Support\Facades\Route::is('admin.supplier.*') ||
                            \Illuminate\Support\Facades\Route::is('admin.partner.*') ||
                            \Illuminate\Support\Facades\Route::is('admin.package.*') ||
                            \Illuminate\Support\Facades\Route::is('admin.banner.*') ||
                            \Illuminate\Support\Facades\Route::is('admin.position*')
                                ? 'active'
                                : '' }}"
                           data-toggle="submenu" aria-haspopup="true" aria-expanded="false" href="#">
                            <i class="nav-main-link-icon fa fa-database"></i>
                            <span class="nav-main-link-name">Master Data</span>
                        </a>
                        <ul class="nav-main-submenu">

                            @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                                   (auth()->guard('admin')->user()->hasAnyPermission([
                                       'branch.list',
                                ])))
                                <li class="nav-main-item">
                                    <a class="nav-main-link" href="{{ route('admin.branch.index') }}">
                                        <span class="nav-main-link-name">Cabang</span>
                                    </a>
                                </li>
                            @endif

                            @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                                   (auth()->guard('admin')->user()->hasAnyPermission([
                                       'room.list',
                                ])))
                                <li class="nav-main-item">
                                    <a class="nav-main-link" href="{{ route('admin.room.index') }}">
                                        <span class="nav-main-link-name">Room</span>
                                    </a>
                                </li>
                            @endif

                            @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                                   (auth()->guard('admin')->user()->hasAnyPermission([
                                       'banner.list',
                                ])))
                                <li class="nav-main-item">
                                    <a class="nav-main-link" href="{{ route('admin.banner.index') }}">
                                        <span class="nav-main-link-name">Banner</span>
                                    </a>
                                </li>
                            @endif

                            @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                                   (auth()->guard('admin')->user()->hasAnyPermission([
                                       'voucher.list',
                                ])))
                                <li class="nav-main-item">
                                    <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true" aria-expanded="false" href="#">
                                        <span class="nav-main-link-name">Voucher</span>
                                    </a>
                                    <ul class="nav-main-submenu">
                                        @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                                               (auth()->guard('admin')->user()->hasAnyPermission(['voucher.list'])))
                                            <li class="nav-main-item">
                                                <a class="nav-main-link" href="{{ route('admin.coupon.index') }}">
                                                    <span class="nav-main-link-name">Voucher Royalti/Poin</span>
                                                </a>
                                            </li>
                                            <li class="nav-main-item">
                                                <a class="nav-main-link" href="{{ route('admin.voucher_package.index') }}">
                                                    <span class="nav-main-link-name">Voucher Treatment</span>
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </li>
                            @endif

                            @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                                   (auth()->guard('admin')->user()->hasAnyPermission([
                                       'voucher.list',
                                ])))
                                <li class="nav-main-item">
                                    <a class="nav-main-link" href="{{ route('admin.voucher.index') }}">
                                        <span class="nav-main-link-name">Kode Voucher</span>
                                    </a>
                                </li>
                            @endif


                            @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                                   (auth()->guard('admin')->user()->hasAnyPermission([
                                       'partner.list',
                                ])))
                                <li class="nav-main-item">
                                    <a class="nav-main-link" href="{{ route('admin.partner.index') }}">
                                        <span class="nav-main-link-name">Terapis</span>
                                    </a>
                                </li>
                            @endif

                            @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                                   (auth()->guard('admin')->user()->hasAnyPermission([
                                       'treatment.list',
                                ])))
                                <li class="nav-main-item">
                                    <a class="nav-main-link" href="{{ route('admin.package.index') }}">
                                        <span class="nav-main-link-name">Treatment</span>
                                    </a>
                                </li>
                            @endif

                            @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                                   (auth()->guard('admin')->user()->hasAnyPermission([
                                       'treatment.list',
                                ])))
                                <li class="nav-main-item">
                                    <a class="nav-main-link" href="{{ route('admin.prewedding_package.index') }}">
                                        <span class="nav-main-link-name">Paket</span>
                                    </a>
                                </li>
                            @endif

                            @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                                   (auth()->guard('admin')->user()->hasAnyPermission([
                                       'position.list',
                                ])))
                                <li class="nav-main-item">
                                    <a class="nav-main-link" href="{{ route('admin.position.index') }}">
                                        <span class="nav-main-link-name">Master Jabatan</span>
                                    </a>
                                </li>
                            @endif

                            @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                                   (auth()->guard('admin')->user()->hasAnyPermission([
                                       'employee.list',
                                ])))
                                <li class="nav-main-item">
                                    <a class="nav-main-link" href="{{ route('admin.employee.index') }}">
                                        <span class="nav-main-link-name">Karyawan</span>
                                    </a>
                                </li>
                            @endif

                            @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                                   (auth()->guard('admin')->user()->hasAnyPermission([
                                       'supplier.list',
                                ])))
                                <li class="nav-main-item">
                                    <a class="nav-main-link" href="{{ route('admin.supplier.index') }}">
                                        <span class="nav-main-link-name">Supplier</span>
                                    </a>
                                </li>
                            @endif

                            @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                                   (auth()->guard('admin')->user()->hasAnyPermission([
                                       'member.list',
                                ])))
                                <li class="nav-main-item">
                                    <a class="nav-main-link" href="{{ route('admin.member.index') }}">
                                        <span class="nav-main-link-name">Member</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif


                @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                    (auth()->guard('admin')->user()->hasAnyPermission([
                        'item_category.list',
                        'item.list',
                        'unit_material.list',
                        'warehouse.list',
                        'item_transfer.list',
                        'inventori.list',
                        'stock_card.list',
                        'move_in.list',
                        'move_out.list',
                    ])))
                    <li class="nav-main-item">
                        <a class="nav-main-link nav-main-link-submenu {{ \Illuminate\Support\Facades\Route::is('admin.item_category*') ||
                            \Illuminate\Support\Facades\Route::is('admin.locker*') ||
                            \Illuminate\Support\Facades\Route::is('admin.item*') ||
                            \Illuminate\Support\Facades\Route::is('admin.uom*') ||
                            \Illuminate\Support\Facades\Route::is('admin.warehouse*') ||
                            \Illuminate\Support\Facades\Route::is('admin.item_transfer*')
                                ? 'active'
                                : '' }}"
                           data-toggle="submenu" aria-haspopup="true" aria-expanded="false" href="#">
                            <i class="nav-main-link-icon fa fa-warehouse"></i>
                            <span class="nav-main-link-name">Barang</span>
                        </a>
                        <ul class="nav-main-submenu">
                            @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                                   (auth()->guard('admin')->user()->hasAnyPermission([
                                       'item_category.list',
                                ])))
                                <li class="nav-main-item">
                                    <a class="nav-main-link" href="{{ route('admin.item_category.index') }}">
                                        <span class="nav-main-link-name">Kategori Barang</span>
                                    </a>
                                </li>
                            @endif


                            @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                                   (auth()->guard('admin')->user()->hasAnyPermission([
                                       'item.list',
                                ])))
                                <li class="nav-main-item">
                                    <a class="nav-main-link" href="{{ route('admin.item.index') }}">
                                        <span class="nav-main-link-name">Master Barang</span>
                                    </a>
                                </li>
                            @endif

                            @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                                   (auth()->guard('admin')->user()->hasAnyPermission([
                                       'unit_material.list',
                                ])))
                                <li class="nav-main-item">
                                    <a class="nav-main-link" href="{{ route('admin.uom.index') }}">
                                        <span class="nav-main-link-name">Unit Material</span>
                                    </a>
                                </li>
                            @endif

                            @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                                   (auth()->guard('admin')->user()->hasAnyPermission([
                                       'warehouse.list',
                                ])))
                                <li class="nav-main-item">
                                    <a class="nav-main-link" href="{{ route('admin.warehouse.index') }}">
                                        <span class="nav-main-link-name">Gudang</span>
                                    </a>
                                </li>
                            @endif

                            @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                                   (auth()->guard('admin')->user()->hasAnyPermission([
                                       'item_transfer.list',
                                ])))
                                <li class="nav-main-item">
                                    <a class="nav-main-link" href="{{ route('admin.item_transfer.index') }}">
                                        <span class="nav-main-link-name">Mutasi</span>
                                    </a>
                                </li>
                            @endif

                            @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                                   (auth()->guard('admin')->user()->hasAnyPermission([
                                       'stock_card.list',
                                ])))
                                <li class="nav-main-item">
                                    <a class="nav-main-link" href="{{ route('admin.stock_card.index') }}">
                                        <span class="nav-main-link-name">Stock Card</span>
                                    </a>
                                </li>
                            @endif

                            @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                                   (auth()->guard('admin')->user()->hasAnyPermission([
                                       'inventori.list',
                                ])))
                                <li class="nav-main-item">
                                    <a class="nav-main-link" href="{{ route('admin.inventory.index') }}">
                                        <span class="nav-main-link-name">Inventori</span>
                                    </a>
                                </li>
                            @endif

                            @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                                   (auth()->guard('admin')->user()->hasAnyPermission([
                                       'move_in.list',
                                ])))
                                <li class="nav-main-item">
                                    <a class="nav-main-link" href="{{ route('admin.inventory_in.index') }}">
                                        <span class="nav-main-link-name">Move In</span>
                                    </a>
                                </li>
                            @endif

                            @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                                   (auth()->guard('admin')->user()->hasAnyPermission([
                                       'move_out.list',
                                ])))
                                <li class="nav-main-item">
                                    <a class="nav-main-link" href="{{ route('admin.inventory_out.index') }}">
                                        <span class="nav-main-link-name">Move Out</span>
                                    </a>
                                </li>
                            @endif

                            {{-- @if ($authorizedModules->contains('SUPER ADMIN') || $authorizedModules->contains('Barang - SEMUA') || $authorizedModules->contains('Barang - Report - SEMUA'))
                  <li class="nav-main-item">
                    <a class="nav-main-link" href="#">
                      <span class="nav-main-link-name">Laporan Stock Barang</span>
                    </a>
                  </li>
                  @endif --}}
                        </ul>
                    </li>
                @endif

                @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1 ||
                        (auth()->guard('admin')->user()->hasAnyPermission([
                            'purchase_request.list',
                            'purchase_order.list',
                            'good_receive.list',
                            'supplier_invoice.list',
                    ])))
                    <li class="nav-main-item">
                        <a class="nav-main-link nav-main-link-submenu {{ \Illuminate\Support\Facades\Route::is('admin.purchase_request*') ||
                            \Illuminate\Support\Facades\Route::is('admin.purchase_order*') ||
                            \Illuminate\Support\Facades\Route::is('admin.good_receive*') ||
                            \Illuminate\Support\Facades\Route::is('admin.supplier_invoice*')
                                ? 'active'
                                : '' }}"
                           data-toggle="submenu" aria-haspopup="true" aria-expanded="false" href="#">
                            <i class="nav-main-link-icon fa fa-money-bill-wave-alt"></i>
                            <span class="nav-main-link-name">Purchasing</span>
                        </a>
                        <ul class="nav-main-submenu">

                            @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1 ||
                                (auth()->guard('admin')->user()->hasAnyPermission([
                                    'purchase_request.list',
                                ])))
                                <li class="nav-main-item">
                                    <a class="nav-main-link" href="{{ route('admin.purchase_request.index') }}">
                                        <span class="nav-main-link-name">Purchase Request</span>
                                    </a>
                                </li>
                            @endif

                            @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                                (auth()->guard('admin')->user()->hasAnyPermission([
                                    'purchase_order.list',
                                ])))
                                <li class="nav-main-item">
                                    <a class="nav-main-link" href="{{ route('admin.purchase_order.index') }}">
                                        <span class="nav-main-link-name">Purchase Order</span>
                                    </a>
                                </li>
                            @endif

                            @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                                (auth()->guard('admin')->user()->hasAnyPermission([
                                    'good_receive.list',
                                ])))
                                <li class="nav-main-item">
                                    <a class="nav-main-link" href="{{ route('admin.good_receive.index') }}">
                                        <span class="nav-main-link-name">Goods Receive</span>
                                    </a>
                                </li>
                            @endif

                            @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                                (auth()->guard('admin')->user()->hasAnyPermission([
                                    'supplier_invoice.list',
                                ])))
                                <li class="nav-main-item">
                                    <a class="nav-main-link" href="{{ route('admin.supplier_invoice.index') }}">
                                        <span class="nav-main-link-name">Supplier Invoice</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                    (auth()->guard('admin')->user()->hasAnyPermission([
                        'coa.list',
                        'bank_cash_out.list',
                        'bank_cash_in.list',
                    ])))
                    <li class="nav-main-item">
                        <a class="nav-main-link nav-main-link-submenu
                          {{ \Illuminate\Support\Facades\Route::is('admin.coa.*') ||
                          \Illuminate\Support\Facades\Route::is('admin.bank_cash_out.*') ||
                          \Illuminate\Support\Facades\Route::is('admin.bank_cash_in.*')
                              ? ' active'
                              : '' }}"
                           data-toggle="submenu" aria-haspopup="true" aria-expanded="false" href="#">
                            <i class="nav-main-link-icon fa fa-money-check"></i>
                            <span class="nav-main-link-name">Finance</span>
                        </a>
                        <ul class="nav-main-submenu">
                            @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                                (auth()->guard('admin')->user()->hasAnyPermission([
                                    'coa.list',
                                ])))
                                <li class="nav-main-item">
                                    <a class="nav-main-link" href="{{ route('admin.coa.index') }}">
                                        <span class="nav-main-link-name">Master COA</span>
                                    </a>
                                </li>
                            @endif

                            @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                                (auth()->guard('admin')->user()->hasAnyPermission([
                                    'bank_cash_out.list',
                                ])))
                                <li class="nav-main-item">
                                    <a class="nav-main-link" href="{{route('admin.bank_cash_out.index')}}">
                                        <span class="nav-main-link-name">Bank Cash Out</span>
                                    </a>
                                </li>
                            @endif

                            @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                                (auth()->guard('admin')->user()->hasAnyPermission([
                                    'bank_cash_in.list',
                                ])))
                                <li class="nav-main-item">
                                    <a class="nav-main-link" href="{{route('admin.bank_cash_in.index')}}">
                                        <span class="nav-main-link-name">Bank Cash In</span>
                                    </a>
                                </li>
                            @endif

                            {{-- @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1)
                                <li class="nav-main-item">
                                    <a class="nav-main-link" href="{{route('admin.ap.index')}}">
                                        <span class="nav-main-link-name">Acc Payment</span>
                                    </a>
                                </li>
                            @endif --}}
                        </ul>
                    </li>
                @endif

                @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                    (auth()->guard('admin')->user()->hasAnyPermission([
                        'journal_manual.list',
                    ])))
                    <li class="nav-main-item">
                        <a class="nav-main-link nav-main-link-submenu {{ \Illuminate\Support\Facades\Route::is('admin.journal*')
                            ? 'active'
                            : '' }}"
                           data-toggle="submenu" aria-haspopup="true" aria-expanded="false" href="#">
                            <i class="nav-main-link-icon fa fa-money-bill-wave-alt"></i>
                            <span class="nav-main-link-name">General Ledger</span>
                        </a>
                        <ul class="nav-main-submenu">
                            @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                                (auth()->guard('admin')->user()->hasAnyPermission([
                                    'journal_manual.list',
                                ])))
                                <li class="nav-main-item">
                                    <a class="nav-main-link" href="{{ route('admin.journal.index') }}">
                                        <span class="nav-main-link-name">Journal Umum</span>
                                    </a>
                                </li>
                            @endif

                        </ul>
                    </li>
                @endif
                @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                    (auth()->guard('admin')->user()->hasAnyPermission([
                        'attendance.list',
                        'attendance.import',
                    ])))
                    <li class="nav-main-item">
                        <a class="nav-main-link nav-main-link-submenu {{ \Illuminate\Support\Facades\Route::is('admin.attendance*') ||
                            \Illuminate\Support\Facades\Route::is('admin.attendance*')
                                ? 'active'
                                : '' }}"
                           data-toggle="submenu" aria-haspopup="true" aria-expanded="false" href="#">
                            <i class="nav-main-link-icon fa fa-warehouse"></i>
                            <span class="nav-main-link-name">Absensi</span>
                        </a>
                        <ul class="nav-main-submenu">
                            @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                                (auth()->guard('admin')->user()->hasAnyPermission([
                                    'attendance.list',
                                ])))
                                <li class="nav-main-item">
                                    <a class="nav-main-link" href="{{ route('admin.attendance.index') }}">
                                        <span class="nav-main-link-name">Daftar Absensi</span>
                                    </a>
                                </li>
                            @endif
                            @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                                (auth()->guard('admin')->user()->hasAnyPermission([
                                    'attendance.import',
                                ])))
                                <li class="nav-main-item">
                                    <a class="nav-main-link" href="{{ route('admin.attendance.import') }}">
                                        <span class="nav-main-link-name">Import Absensi</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                    (auth()->guard('admin')->user()->hasAnyPermission([
                        'reporting.laba_kotor',
                        'reporting.terapis',
                        'reporting.voucher',
                        'reporting.trx_payment',
                        'reporting.daily_recap',
                        'reporting.trx_report',
                        'reporting.trx_commision',
                        'reporting.mtdytd',
                        'general_ledger.cashflow',
                        'general_ledger.trial_balance',
                        'general_ledger.profit_and_loss',
                        'general_ledger.balance_sheet',
                        'general_ledger.ledger',
                        'member.list',
                    ])))
                    <li class="nav-main-item">
                        <a class="nav-main-link nav-main-link-submenu {{ \Illuminate\Support\Facades\Route::is('admin.pr.report*') ||
                            \Illuminate\Support\Facades\Route::is('admin.po.report*') ||
                            \Illuminate\Support\Facades\Route::is('admin.po.report*') ||
                            \Illuminate\Support\Facades\Route::is('admin.good_receive.report*') ||
                            \Illuminate\Support\Facades\Route::is('admin.sales.report*') ||
                            \Illuminate\Support\Facades\Route::is('admin.report*') ||
                            \Illuminate\Support\Facades\Route::is('admin.transaction*') ||
                            \Illuminate\Support\Facades\Route::is('admin.invoice.report*') ||
                            \Illuminate\Support\Facades\Route::is('admin.price.report*') ||
                            \Illuminate\Support\Facades\Route::is('admin.mo.report*') ||
                            \Illuminate\Support\Facades\Route::is('admin.ar.report*') ||
                            \Illuminate\Support\Facades\Route::is('admin.ap.report*') ||
                            \Illuminate\Support\Facades\Route::is('admin.general_ledger.report*')
                                ? 'active'
                                : '' }}"
                           data-toggle="submenu" aria-haspopup="true" aria-expanded="false" href="#">
                            <i class="nav-main-link-icon fa fa-file-alt"></i>
                            <span class="nav-main-link-name">Report</span>
                        </a>
                        <ul class="nav-main-submenu">

                            @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                                (auth()->guard('admin')->user()->hasAnyPermission([
                                    'reporting.laba_kotor',
                                    'reporting.terapis',
                                    'reporting.voucher',
                                    'reporting.trx_payment',
                                    'reporting.daily_recap',
                                    'reporting.trx_report',
                                    'reporting.trx_commision',
                                    'reporting.mtdytd',
                                ])))
                                <li class="nav-main-item">
                                    <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true" aria-expanded="false" href="#">
                                        <span class="nav-main-link-name">Transaksi</span>
                                    </a>
                                    <ul class="nav-main-submenu">
                                        @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                                            (auth()->guard('admin')->user()->hasAnyPermission([
                                                'reporting.trx_report',
                                            ])))
                                            <li class="nav-main-item">
                                                <a class="nav-main-link" href="{{ route('admin.transaction.index') }}">
                                                    <span class="nav-main-link-name">Daftar Transaksi</span>
                                                </a>
                                            </li>
                                        @endif
                                        @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                                            (auth()->guard('admin')->user()->hasAnyPermission([
                                                'reporting.laba_kotor',
                                            ])))
                                            <li class="nav-main-item">
                                                <a class="nav-main-link" href="{{ route('admin.report.oura_only.filter') }}">
                                                    <span class="nav-main-link-name">Laporan Laba Kotor</span>
                                                </a>
                                            </li>
                                        @endif
                                        @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                                            (auth()->guard('admin')->user()->hasAnyPermission([
                                                'reporting.terapis',
                                            ])))
                                            <li class="nav-main-item">
                                                <a class="nav-main-link" href="{{ route('admin.report.rating_partner.filter') }}">
                                                    <span class="nav-main-link-name">Laporan Terapis</span>
                                                </a>
                                            </li>
                                        @endif
                                        @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                                            (auth()->guard('admin')->user()->hasAnyPermission([
                                                'reporting.voucher',
                                            ])))
                                            <li class="nav-main-item">
                                                <a class="nav-main-link" href="{{ route('admin.report.foc.filter') }}">
                                                    <span class="nav-main-link-name">Laporan Voucher</span>
                                                </a>
                                            </li>
                                        @endif
                                        @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                                            (auth()->guard('admin')->user()->hasAnyPermission([
                                                'reporting.trx_payment',
                                            ])))
                                            <li class="nav-main-item">
                                                <a class="nav-main-link" href="{{ route('admin.report.trx_payment.filter') }}">
                                                    <span class="nav-main-link-name">Laporan Transaksi per Payment</span>
                                                </a>
                                            </li>
                                        @endif
                                        @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                                            (auth()->guard('admin')->user()->hasAnyPermission([
                                                'reporting.daily_recap',
                                            ])))
                                            <li class="nav-main-item">
                                                <a class="nav-main-link" href="{{ route('admin.report.recap.filter') }}">
                                                    <span class="nav-main-link-name">Laporan Rekap Transaksi (Daily Recap)</span>
                                                </a>
                                            </li>
                                        @endif
                                        @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                                            (auth()->guard('admin')->user()->hasAnyPermission([
                                                'reporting.trx_report',
                                            ])))
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="{{ route('admin.report.transaction.filter', ['type'=>'Voucher']) }}">
                                                <span class="nav-main-link-name">Laporan Transaksi</span>
                                            </a>
                                        </li>
                                        @endif
                                        @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                                            (auth()->guard('admin')->user()->hasAnyPermission([
                                                'reporting.trx_commision',
                                            ])))
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="{{ route('admin.report.therapist_commission.filter') }}">
                                                <span class="nav-main-link-name">Laporan Komisi Terapis</span>
                                            </a>
                                        </li>
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="{{ route('admin.report.salon_studio_commission.filter') }}">
                                                <span class="nav-main-link-name">Laporan Komisi Studio / Salon</span>
                                            </a>
                                        </li>
                                        @endif
                                        @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1||
                                            (auth()->guard('admin')->user()->hasAnyPermission([
                                                'reporting.mtdytd',
                                            ])))
                                        <li class="nav-main-item">
                                            <a class="nav-main-link" href="{{ route('admin.report.mtd.filter') }}">
                                                <span class="nav-main-link-name">Laporan Rekap Today, MTD, YTD</span>
                                            </a>
                                        </li>
                                        @endif
                                    </ul>
                                </li>
                            @endif

                            {{-- @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1)
                                <li class="nav-main-item">
                                    <a class="nav-main-link" href="{{ route('admin.ap.report.filter', ['type' => 'ap_card']) }}">
                                        <span class="nav-main-link-name">Finance</span>
                                    </a>
                                    <ul class="nav-main-submenu">
                                        @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1 )
                                            <li class="nav-main-item">
                                                <a class="nav-main-link" href="{{ route('admin.ap.report.filter', ['type' => 'ap_card']) }}">
                                                    <span class="nav-main-link-name">Laporan AP Card</span>
                                                </a>
                                            </li>
                                        @endif
                                        @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1 )
                                            <li class="nav-main-item">
                                                <a class="nav-main-link" href="{{ route('admin.ap.report.filter', ['type' => 'ap_aging']) }}">
                                                    <span class="nav-main-link-name">Laporan AP Aging</span>
                                                </a>
                                            </li>
                                        @endif
                                        @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1)
                                            <li class="nav-main-item">
                                                <a class="nav-main-link" href="{{ route('admin.ap.report.filter', ['type' => 'ap_invoice_list']) }}">
                                                    <span class="nav-main-link-name">Laporan AP Invoice List</span>
                                                </a>
                                            </li>
                                        @endif
                                        @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1)
                                            <li class="nav-main-item">
                                                <a class="nav-main-link" href="{{ route('admin.ar.report.filter', ['type' => 'ar_card']) }}">
                                                    <span class="nav-main-link-name">Laporan AR Card</span>
                                                </a>
                                            </li>
                                        @endif
                                        @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1)
                                            <li class="nav-main-item">
                                                <a class="nav-main-link" href="{{ route('admin.ar.report.filter', ['type' => 'ar_aging']) }}">
                                                    <span class="nav-main-link-name">Laporan AR Aging</span>
                                                </a>
                                            </li>
                                        @endif
                                        @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1)
                                            <li class="nav-main-item">
                                                <a class="nav-main-link" href="{{ route('admin.ar.report.filter', ['type' => 'ar_invoice_list']) }}">
                                                    <span class="nav-main-link-name">Laporan AR Invoice List</span>
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </li>
                            @endif --}}
                            @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1 ||
                                    (auth()->guard('admin')->user()->hasAnyPermission([
                                        'general_ledger.cashflow',
                                        'general_ledger.trial_balance',
                                        'general_ledger.profit_and_loss',
                                        'general_ledger.balance_sheet',
                                        'general_ledger.ledger',
                                ])))
                                <li class="nav-main-item">
                                    <a class="nav-main-link" href="{{ route('admin.general_ledger.report') }}">
                                        <span class="nav-main-link-name  {{\Illuminate\Support\Facades\Route::is('admin.general_ledger.report')  ?
                                                ' active': '' }}">Accounting</span>
                                    </a>
                                </li>
                            @endif

                            @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1 ||
                                    (auth()->guard('admin')->user()->hasAnyPermission([
                                        'member.list',
                                ])) )
                                <li class="nav-main-item">
                                    <a class="nav-main-link" href="{{ route('admin.report.user_point_history.index') }}">
                                        <span class="nav-main-link-name">Riwayat Poin User</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                @if (auth()->guard('admin')->user()->is_super == 1 || auth()->guard('admin')->user()->is_developer == 1)
                    <li class="nav-main-item">
                        <a class="nav-main-link nav-main-link-submenu
                    {{ \Illuminate\Support\Facades\Route::is('admin.access*') ||
                    \Illuminate\Support\Facades\Route::is('admin.admin_user*')
                        ? ' active'
                        : '' }}"
                           data-toggle="submenu" aria-haspopup="true" aria-expanded="false" href="#">
                            <i class="nav-main-link-icon fa fa-cog"></i>
                            <span class="nav-main-link-name">Pengaturan</span>
                        </a>
                        <ul class="nav-main-submenu">
                            <li class="nav-main-item">
                                <a class="nav-main-link" href="{{ route('admin.admin_user.index') }}">
                                    <span class="nav-main-link-name">User Admin</span>
                                </a>
                            </li>
                            <li class="nav-main-item">
                                <a class="nav-main-link" href="{{ route('admin.access.index') }}">
                                    <span class="nav-main-link-name">Template Ijin Akses</span>
                                </a>
                            </li>
                            <li class="nav-main-item">
                                <a class="nav-main-link" href="{{ route('admin.access_user.index') }}">
                                    <span class="nav-main-link-name">Ijin Akses User</span>
                                </a>
                            </li>
                            <li class="nav-main-item">
                                <a class="nav-main-link" href="{{ route('admin.setting_system.edit') }}">
                                    <span class="nav-main-link-name">General Settings</span>
                                </a>
                            </li>
{{--                            <li class="nav-main-item">--}}
{{--                                <a class="nav-main-link" href="{{ route('admin.access.index') }}">--}}
{{--                                    <span class="nav-main-link-name">Template Ijin Akses</span>--}}
{{--                                </a>--}}
{{--                            </li>--}}
{{--                            <li class="nav-main-item">--}}
{{--                                <a class="nav-main-link" href="{{ route('admin.access_user.index') }}">--}}
{{--                                    <span class="nav-main-link-name">Ijin Akses User</span>--}}
{{--                                </a>--}}
{{--                            </li>--}}
{{--                            <li class="nav-main-item">--}}
{{--                                <a class="nav-main-link" href="{{ route('admin.transaction_utility.index') }}">--}}
{{--                                    <span class="nav-main-link-name">Transaction Utility</span>--}}
{{--                                </a>--}}
{{--                            </li>--}}
                        </ul>
                    </li>
                @endif

                <li class="nav-main-heading">Halo, {{ auth()->guard('admin')->user()->name }}</li>

                <li class="nav-main-item">
                    <a href="{{ route('admin.my.change_password') }}" class="nav-main-link {{ \Illuminate\Support\Facades\Route::is('admin.my.change_password*') ? 'active' : '' }}">
                        <i class="nav-main-link-icon fa fa-cog"></i>
                        <span class="nav-main-link-name">Change Password</span>
                    </a>
                    <a onclick="event.preventDefault();document.getElementById('logout-form').submit();" class="nav-main-link">
                        <i class="nav-main-link-icon fa fa-arrow-left"></i>
                        <span class="nav-main-link-name">Log Out</span>
                        <form id="logout-form" method="POST" action="{{ route('admin.logout') }}">
                            {{ csrf_field() }}
                        </form>
                    </a>
                </li>
            </ul>
{{--            <div class="col-lg-1 text-end">--}}
{{--                <div class="dropdown d-inline-block ms-2">--}}
{{--                    <button type="button" class="btn btn-sm btn-alt-secondary" id="page-header-user-dropdown"--}}
{{--                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">--}}
{{--                        --}}{{--                            <img class="rounded" src="https://images.pexels.com/photos/1239291/pexels-photo-1239291.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1" alt="Header Avatar" style="width: 21px;" /> --}}
{{--                        --}}{{--                            <span class="d-none d-sm-inline-block ms-1">Charlote</span> --}}
{{--                        <i class="fa fa-fw fa-angle-down d-none d-sm-inline-block"></i>--}}
{{--                    </button>--}}
{{--                    <div class="dropdown-menu dropdown-menu-md dropdown-menu-end p-0 border-0"--}}
{{--                         aria-labelledby="page-header-user-dropdown">--}}
{{--                        <div class="p-2">--}}
{{--                            <a class="dropdown-item d-flex align-items-center justify-content-between"--}}
{{--                               href="{{ route('admin.my.change_password') }}">--}}
{{--                                <span class="fs-sm fw-medium">Change Password</span>--}}
{{--                            </a>--}}

{{--                            <a class="dropdown-item d-flex align-items-center justify-content-between"--}}
{{--                               onclick="event.preventDefault();document.getElementById('logout-form').submit();">--}}
{{--                                <span class="fs-sm fw-medium">Log Out</span>--}}
{{--                                <form id="logout-form" method="POST" action="{{ route('admin.logout') }}">--}}
{{--                                    {{ csrf_field() }}--}}
{{--                                </form>--}}
{{--                            </a>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
        </div>
    </div>
</div>
<!-- END Navigation -->
