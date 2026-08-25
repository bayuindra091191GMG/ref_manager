@extends('layouts.admin')

@section('title')
    <title>Buat Transaksi Baru</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Mulai Beberapa Transaksi
                    </h1>
                </div>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <div id="app">
        <form id="form_create_transaction">
            <div class="content">
                <div class="space-y-3">
                    {{ csrf_field() }}
                    <div class="row">
                        <div class="col-12 text-end">
                            <button id="btn_loading" type="button" class="btn btn-success" style="display: none;">
                                <i class="spinner-border spinner-border-sm text-green" role="status">
                                    <span class="visually-hidden">Mohon tunggu...</span>
                                </i>
                            </button>
                            <button type="button" id="btn_submit" class="btn btn-success"
                                v-on:click="submitForm">Start</button>&nbsp;&nbsp;
                            <a id="btn_cancel" href="{{ route('admin.monitor.index') }}"
                                class="btn btn-danger">Kembali</a>
                        </div>
                    </div>
                    <div class="block block-rounded">
                        <div class="block-content block-content-full">
                            <div class="row">
                                <div class="col-sm-6">
                                    @include('partials._success')
                                    @include('partials._error')
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="space-y-3">
                                        <div id="section_validation_error" class="row" style="display: none;">
                                            <div class="col-lg-8 col-12">
                                                <div class="alert alert-danger alert-dismissible" role="alert">
                                                    <ul id="validation_error_list">

                                                    </ul>
                                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                        aria-label="Close"></button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <span style="color: red;">Isian dengan asterik (*) wajib diisi!</span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label" for="code">Kode Transaksi</label>
                                            <div class="col-sm-3">
                                                <input id="code" type="text" class="form-control"
                                                    style="text-transform: uppercase;" name="code" value="AUTO"
                                                    readonly />
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label" for="date">Tanggal</label>
                                            <div class="col-sm-3">
                                                <input id="date" type="text" class="form-control" name="date"
                                                    value="{{ $todayDate }}" readonly />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="block block-rounded">
                        <div class="block-content block-content-full">
                            <div class="row">
                                <div class="col-12 alert alert-danger alert-dismissible" role="alert"
                                    v-if="posMsg !== ''">
                                    <p class="mb-0">
                                        @{{ posMsg }}
                                    </p>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-sm-3 col-12">
                                    <span>FOC?</span>
                                </div>
                                <div class="col-sm-4 col-12">
                                    <select class="form-select" name="foc" v-model="foc"
                                        v-on:change="calculateTransaction" id="foc_select"
                                        :disabled="passwordCheck.focDisabled == 1">
                                        <option value="NO" selected>Tidak FOC</option>
                                        <option value="FOC">FOC</option>
                                        <option value="FOC_PACKAGE">FOC Paket</option>
                                        <option value="FOC_ITEM">FOC Item</option>
                                    </select>
                                </div>
                                <div class="col-2">
                                    <button type="button" class="btn btn-success" id="btn_enable_foc"
                                        v-on:click="showFocModal">Enable FOC</button>
                                </div>
                            </div>
                            <div class="row mb-2" v-if="foc !== 'NO'">
                                <div class="col-sm-3 col-12">
                                    <span>Nama FOC</span>
                                </div>
                                <div class="col-sm-4 col-12">
                                    <input type="text" name="foc_name" v-model="focNumber" class="form-control"
                                        placeholder="Nama FOC" />
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-sm-3 col-12">
                                    <span>No Telp. User Aplikasi</span>
                                </div>
                                <div class="col-sm-4 col-12">
                                    <input type="text" name="user_phone" v-model="userPhone" class="form-control"
                                        placeholder="No Telp. User Aplikasi" />
                                    <input type="hidden" name="app_user_found" v-model="appUserFound" />
                                </div>
                                <div class="col-2" v-if="!isAppUserFound">
                                    <button type="button" class="btn btn-success" id="btn_check_app_user"
                                        v-on:click="checkAppUser">Cari User</button>
                                </div>
                                <div class="col-2" v-if="isAppUserFound">
                                    <button type="button" class="btn btn-danger" id="btn_cancel_app_user"
                                        v-on:click="cancelAppUser">Batal</button>
                                </div>
                            </div>
                            <div class="row mb-2" v-if="isAppUserFound">
                                <div class="col-6">
                                    <input type="hidden" name="app_user_birthday_found"
                                        v-model="appUserBirthdayFound" />
                                    <span style="color: green; font-size: 20px;">User Aplikasi ditemukan!</span><br />
                                    <span style="color: green; font-size: 20px;">Membership @{{ appUserMembershipName }}, Point
                                        @{{ appUserPoint }}</span><br />
                                    <span style="color: green; font-size: 20px;" v-if="isAppUserBirthday">Ulang tahun
                                        tanggal @{{ appUserBirthdayDate }}</span>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-12">
                                    <button type="button" class="btn btn-primary" v-on:click="showPackageModal"><i
                                            class="fa fa-plus"></i>&nbsp;Treatment</button>
                                    &nbsp;
                                    {{--<button type="button" class="btn btn-primary" v-on:click="showItemModal"><i
                                            class="fa fa-plus"></i>&nbsp;Item</button>
                                     &nbsp;
                                    <button type="button" class="btn btn-success" v-on:click="setGoogleReview"><i
                                            class="fa fa-plus"></i>&nbsp;Google Review Discount</button>
                                    &nbsp;
                                    <button type="button" class="btn btn-warning" v-on:click="cancelGoogleReview"><i
                                            class="fa fa-minus"></i>&nbsp;Cancel Google Review Discount</button> --}}
                                    {{--                                &nbsp; --}}
                                    {{--                                <button type="button" class="btn btn-primary" v-on:click="showCouponModal"><i class="fa fa-plus"></i>&nbsp;Coupon</button> --}}
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-12">
                                    <div class="table-responsive w-100 mb-2">
                                        <table class="table table-bordered table-striped table-vcenter"
                                            id="table_data_transaction">
                                            <thead>
                                                <tr>
                                                    <th class="text-center">Tindakan</th>
                                                    <th class="text-center">No</th>
                                                    <th class="text-center">Nama</th>
                                                    <th class="text-center">Customer</th>
                                                    <th class="text-center" width="20%">Qty</th>
                                                    <th class="text-center">Harga</th>
                                                    <th class="text-center">Diskon</th>
                                                    <th class="text-center">Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(item, index) in details">
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-danger"
                                                            v-on:click="removeDetail(item)"><i class="fa fa-trash"></i></button>
                                                    </td>
                                                    <td class="text-center">
                                                        @{{ index + 1 }}
                                                    </td>
                                                    <td>
                                                        <input type="hidden" :name="'details[' + index + '][type]'"
                                                            v-model="item.type" />
                                                        <input type="hidden" :name="'details[' + index + '][item_id]'"
                                                            v-model="item.item_id" />
                                                        <input type="hidden" :name="'details[' + index + '][package_id]'"
                                                            v-model="item.package_id" />
                                                        <input type="hidden" :name="'details[' + index + '][room_id]'"
                                                            v-model="item.room_id" />
                                                        <input type="hidden" :name="'details[' + index + '][partner_id]'"
                                                            v-model="item.partner_id" />
                                                        <input type="hidden" :name="'details[' + index + '][is_birthday_promo]'"
                                                            v-model="item.is_birthday_promo" />
                                                        <span v-if="item.type === 'package'">@{{ item.package_name }} (Room
                                                            @{{ item.room_name }}, @{{ item.partner_alias }})</span>
                                                        <span v-if="item.type === 'item'">@{{ item.item_name }}</span>
                                                        {{-- <select class="form-select"
                                                            v-if="item.type === 'item' && item.is_item_addon == 1"
                                                            :name="'details[' + index + '][addon_partner_id]'">
                                                            <option value="0"> -- PILIH THERAPIS -- </option>
                                                            <option v-for="(partner, index) in packagePartnerArr" :value="partner.partner_id"> @{{ partner.partner_alias }}</option>
                                                        </select> --}}

                                                        <select class="form-select select-commission-partners"
                                                            v-if="item.type === 'item' && item.is_item_addon == 1"
                                                            :name="'details[' + index + '][addon_partner_id]'">
                                                            <option value="0"> -- PILIH THERAPIS -- </option>
                                                            @foreach ($commissionPartners as $commissionPartner)
                                                                <option value="{{ $commissionPartner->id }}">{{ $commissionPartner->name }}</option>
                                                            @endforeach
                                                        </select>

                                                        <span v-if="item.is_facility_only">Hanya Fasilitas</span>
                                                        <span v-if="item.type === 'membership'">
                                                            @{{ item.membership_name }}
                                                            <br />
                                                            Data Member<br />
                                                            Number: @{{ memberData.number }}<br />
                                                            Nama: @{{ memberData.name }}<br />
                                                            Email: <span
                                                                v-if="memberData.email !== ''">@{{ memberData.email }}</span>
                                                            <span v-else>-</span>
                                                            <br />
                                                            Phone: <span
                                                                v-if="memberData.phone !== ''">@{{ memberData.phone }}</span>
                                                            <span v-else>-</span>
                                                            <br />
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <input type="hidden" :name="'details[' + index + '][customer_name]'"
                                                            v-model="item.customer_name" />
                                                        @{{ item.customer_name }}
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control text-end"
                                                            :name="'details[' + index + '][qty]'"
                                                            v-if="item.type === 'package' || item.type === 'membership'"
                                                            v-model="item.qty" readonly />
                                                        <input type="number" class="form-control text-end"
                                                            :name="'details[' + index + '][qty]'"
                                                            v-if="item.type === 'item' && item.is_cut == 0" v-model="item.qty"
                                                            readonly />
                                                        <input type="number" class="form-control text-end"
                                                            :name="'details[' + index + '][qty]'"
                                                            v-else-if="item.type === 'item' && item.is_cut == 1"
                                                            v-model="item.qty" readonly />
                                                        <input type="number" class="form-control text-end"
                                                            :name="'details[' + index + '][qty]'" v-if="item.is_facility_only"
                                                            value="1" readonly />
                                                    </td>
                                                    <td class="text-end">@{{ item.price_str }}</td>
                                                    <td class="text-end">
                                                        <input type="hidden" :name="'details[' + index + '][coupon_id]'"
                                                            v-model="item.coupon_id" />
                                                        @{{ item.discount_str }}
                                                    </td>
                                                    <td><input type="text" class="form-control text-end"
                                                            v-model="item.subtotal_str" readonly /></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="7" class="text-end"><b>Discount (%)</b></td>
                                                    <td class="text-end">
                                                        <div class="input-group">
                                                            <input type="text" class="form-control disc_input text-end"
                                                                name="discount_percentage" v-model="discountPercentage"
                                                                data-type="percentage" :disabled="details.length == 0" />
                                                            <span class="input-group-text">%</span>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="7" class="text-end"><b>Discount Flat</b></td>
                                                    <td class="text-end">
                                                        <div class="input-group">
                                                            <span class="input-group-text">Rp</span>
                                                            <vue-autonumeric :options="autonumericFormat" type="text"
                                                                class="form-control disc_input text-end" name="discount_amount"
                                                                v-model="discountAmount" data-type="amount"
                                                                :disabled="details.length == 0"></vue-autonumeric>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr v-if="isGoogleReview">
                                                    <td colspan="7" class="text-end"><b>Discount Google Review</b></td>
                                                    <td class="text-end">
                                                        <div class="input-group">
                                                            <span class="input-group-text">Rp</span>
                                                            <vue-autonumeric :options="autonumericFormat" type="text"
                                                                class="form-control disc_input text-end" name="google_review_amount"
                                                                v-model="googleReviewAmount" data-type="amount" readonly>
                                                            </vue-autonumeric>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="7" class="text-end"><b>Total</b></td>
                                                    <td class="text-end"><b>@{{ grandTotalStr }}</b></td>
                                                </tr>
                                                <tr v-if="this.isPackageExists && !this.isUsingInputVoucherCode">
                                                    <td colspan="5" class="text-end">Kenakan Kupon: </td>
                                                    <td colspan="2">
                                                        <select class="form-select" v-model="selectedCoupon"
                                                            v-on:change="applyCoupon">
                                                            <option value="0">----- Pilih Kupon -----</option>
                                                            @foreach ($coupons as $coupon)
                                                                <option
                                                                    value="{{ $coupon->id . '#' . floatval($coupon->discount_percentage) . '#' . floatval($coupon->discount_amount) }}">
                                                                    {{ $coupon->code }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                                <tr v-if="this.isPackageExists">
                                                    <td colspan="5" class="text-end">Kode Kupon: </td>
                                                    <td colspan="2">
                                                        <input type="hidden" name="voucher_code" v-if="voucherModel != null"
                                                            v-model="voucherModel.code" />
                                                        <input type="hidden" name="voucher_code" v-else value="-" />
                                                        <input type="text" id="voucher_code" class="form-control"
                                                            v-model="voucherCode" v-on:input="checkOnVoucher" />
                                                        <span style="color: red; display: none;"
                                                            id="voucher_success">@{{ voucherMsg }}</span>
                                                    </td>
                                                    <td>
                                                        <button type="button" id="voucher_check_loading" class="btn btn-success"
                                                            style="display: none;">
                                                            <i class="spinner-border spinner-border-sm text-green" role="status">
                                                                <span class="visually-hidden">Mohon tunggu...</span>
                                                            </i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <input type="hidden" name="inputChangeTotal" v-model="inputChangeTotal" />
                    {{--                <div class="table-responsive w-100 mt-1" style="display: none;"> --}}
                    {{--                    <table class="table table-bordered table-striped table-vcenter"> --}}
                    {{--                        <thead> --}}
                    {{--                        <tr> --}}
                    {{--                            <th>No</th> --}}
                    {{--                            <th>Tipe Payment</th> --}}
                    {{--                            <th>Jumlah</th> --}}
                    {{--                            <th>Opsi</th> --}}
                    {{--                        </tr> --}}
                    {{--                        </thead> --}}
                    {{--                        <tbody> --}}
                    {{--                        <tr v-for="(item, index) in payments"> --}}
                    {{--                            <td>@{{ index + 1 }}</td> --}}
                    {{--                            <td> --}}
                    {{--                                <input type="hidden" :name="'payments[' + index + '][payment_id]'" v-model="item.payment_id" /> --}}
                    {{--                            </td> --}}
                    {{--                            <td> --}}
                    {{--                                <input type="hidden" :name="'payments[' + index + '][amount]'" v-model="item.amount" /> --}}
                    {{--                            </td> --}}
                    {{--                        </tr> --}}
                    {{--                        </tbody> --}}
                    {{--                    </table> --}}
                    {{--                </div> --}}
                </div>
            </div>
            <!-- Payment Modal -->
            <div class="modal" id="modal-payment" tabindex="-1" role="dialog" aria-labelledby="modal-payment"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="block block-rounded block-transparent mb-0">
                            <div class="block-header block-header-default" style="background: red; color: white;">
                                <h3 class="block-title">Pembayaran</h3>
                                <div class="block-options">
                                    <button type="button" class="btn-block-option" data-bs-dismiss="modal"
                                        aria-label="Close">
                                        <i class="fa fa-fw fa-times" style="color: white;"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="block-content fs-sm">
                                <div class="row">
                                    <div class="col-12 justify-content-end">
                                        <span style="font-size: 20px;">Total Transaksi: @{{ grandTotalStr }}</span>
                                    </div>
                                </div>
                                <div class="row mt-2 mb-2">
                                    <div class="col-12 justify-content-end">
                                        <span style="font-size: 16px;">Total Kembalian: @{{ changeTotalStr }}</span>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-6 justify-content-start">
                                        <button type="button" id="btn_finish_payment" class="btn btn-primary"
                                            v-on:click="submitForm" v-if="!is_loading_payment"><i class="fa fa-money"></i>Finish Pembayaran</button>
                                        <button id="btn_loading_payment" type="button" class="btn btn-primary" v-if="is_loading_payment">
                                            <i class="spinner-border spinner-border-sm text-green" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </i>
                                        </button>
                                    </div>
                                    <div class="col-6 text-end" v-if="grandTotal !== 0">
                                        <button type="button" id="btn_add_more_payment" class="btn btn-success"
                                            v-on:click="addMorePayment"><i class="fa fa-plus"></i>Tambah
                                            Pembayaran</button>
                                    </div>
                                    <div id="payment_loading" class="spinner-border text-info col-12"
                                        style="display: none;">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                                <div class="table-responsive w-100 mt-1">
                                    <table class="table table-bordered table-striped table-vcenter js-dataTable-full"
                                        id="table_data_ubah_paket">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Tipe Payment</th>
                                                <th>Jumlah</th>
                                                <th>Opsi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(item, index) in payments">
                                                <td>@{{ index + 1 }}</td>
                                                <td>
                                                    <select class="form-select"
                                                        :name="'payments[' + index + '][payment_id]'"
                                                        v-model="item.payment_id">
                                                        <option v-for="(payment, index2) in paymentTypes"
                                                            :value="payment.id">@{{ payment.description }}</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <vue-autonumeric :options="autonumericFormat"
                                                        :name="'payments[' + index + '][amount]'" v-if="grandTotal == 0"
                                                        readonly v-model="item.amount"
                                                        class="form-control"></vue-autonumeric>
                                                    <vue-autonumeric :options="autonumericFormat"
                                                        :name="'payments[' + index + '][amount]'" v-else
                                                        v-model="item.amount" class="form-control"></vue-autonumeric>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-success" v-if="index !== 0"
                                                        v-on:click="deletePayment(item)"><i
                                                            class="fa fa-minus"></i>Hapus</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END Payment Modal -->
        </form>

        <div class="modal" id="modal-item-qty-old" tabindex="-1" role="dialog" aria-labelledby="modal-item-qty"
            aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-fullscreen-md-down" role="document">
                <div class="modal-content">
                    <div class="block block-rounded block-transparent mb-0">
                        <div class="block-header block-header-default" style="background: red; color: white;">
                            <h3 class="block-title">Tambah Item</h3>
                            <div class="block-options">
                                <button type="button" class="btn-block-option" data-bs-dismiss="modal"
                                    aria-label="Close">
                                    <i class="fa fa-fw fa-times" style="color: white;"></i>
                                </button>
                            </div>
                        </div>
                        <div class="block-content fs-sm">
                            <div class="row">
                                <div class="col-6 text-start" v-if="!itemLoading">
                                    <button type="button" class="btn btn-success" v-on:click="addItems"><i
                                            class="fa fa-plus"></i>&nbsp;Pilih Item</button>
                                </div>
                                <div class="col-6 spinner-border text-primary" v-if="itemLoading">
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-2 text-start">
                                    <select v-on:change="generateItemDisplay" v-model="itemPagination.take"
                                        class="form-select">
                                        <option>10</option>
                                        <option>25</option>
                                        <option>50</option>
                                    </select>
                                </div>
                                <div class="col-10 text-end">
                                    <input type="text" class="form-control" v-model="itemPagination.search"
                                        v-on:input="searchItem" placeholder="Cari Item" />
                                </div>
                            </div>
                            <div class="row mt-2 ms-1 me-1" v-if="addItemMsg !== ''">
                                <div class="col-12 alert alert-danger alert-dismissible" role="alert">
                                    <p class="mb-0">
                                        @{{ addItemMsg }}
                                    </p>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            </div>
                            <div class="row" v-if="itemPagination.isLoading">
                                <div class="col-12 spinner-border text-primary">
                                </div>
                            </div>
                            <div class="table-responsive w-100 mt-1" style="font-size: 12px !important;">
                                <table class="table table-bordered table-striped table-vcenter js-dataTable-full"
                                    id="table_data_item">
                                    <thead>
                                        <tr class="text-center">
                                            <td>Check</td>
                                            <td>Nama Item</td>
                                            <td>Harga</td>
                                            <td>Stock</td>
                                            <td>Qty</td>
                                            <td>Tipe</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="packagePagination.isLoading">
                                            <td colspan="5" class="text-center">
                                                <div class="spinner-border text-info mt-2 mb-2">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr v-for="(item, index) in itemDisplays">
                                            <td class="text-center">
                                                <input type="checkbox" v-model="item.selected"
                                                    v-if="item.stock === 0 && item.is_unlimited_stock === 0" disabled />
                                                <input type="checkbox" v-model="item.selected" v-else />
                                            </td>
                                            <td>@{{ item.name }}</td>
                                            <td class="text-end">@{{ item.price_str }}</td>
                                            <td class="text-center">@{{ item.stock }}</td>
                                            <td style="width: 25%;">
                                                <input type="number" v-model="item.qty" class="form-control text-end"
                                                    v-on:input="checkOnStock(item)"
                                                    v-if="item.stock === 0 && item.is_unlimited_stock === 0" readonly />
                                                <input type="number" v-model="item.qty" class="form-control text-end"
                                                    v-on:input="checkOnStock(item)"
                                                    v-else-if="item.is_addon == 1" readonly />
                                                <input type="number" v-model="item.qty" class="form-control text-end"
                                                    v-on:input="checkOnStock(item)" v-else />
                                            </td>
                                            <td>@{{ item.category }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="4">Showing <span>@{{ itemDisplays.length + itemPagination.skip }}</span> of
                                                @{{ items.length }}
                                            <td class="text-center" colspan="2">
                                                <button type="button" v-on:click="previousItemPage"><i
                                                        class="fa fa-chevron-left"></i></button>
                                                &nbsp;&nbsp;
                                                <button type="button" v-on:click="nextItemPage"><i
                                                        class="fa fa-chevron-right"></i></button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Treatment Modal -->
        <div class="modal" id="modal-package" tabindex="-1" role="dialog" aria-labelledby="modal-package"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="block block-rounded block-transparent mb-0">
                        <div class="block-header block-header-default">
                            <h3 class="block-title">Pilih Treatment</h3>
                            <div class="block-options">
                                <button type="button" class="btn-block-option" data-bs-dismiss="modal"
                                    aria-label="Close">
                                    <i class="fa fa-fw fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="block-content fs-sm">
                            <div class="row mb-2">
                                <label class="col-form-label col-sm-4 col-12">Nama Customer</label>
                                <div class="col-sm-8 col-12">
                                    <input type="text" v-model="selectedCustomer" class="form-control" />
                                </div>
                            </div>
                            <div class="row mb-2">
                                <label class="col-form-label col-sm-4 col-12">Room <span class="text-red">*</span></label>
                                <div class="col-sm-8 col-12">
                                    <select2 url="{{ route('admin.room.oura.select') }}" v-model="selectedRoom"
                                        modal="modal-package" class="form-select form-control-select2"
                                        placeholder="Pilih Room">
                                    </select2>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <label class="col-form-label col-sm-4 col-12">Therapis <span
                                        class="text-red">*</span></label>
                                <div class="col-sm-8 col-12">
                                    <select2 url="{{ route('admin.partner.select') }}" v-model="selectedPartner"
                                        modal="modal-package" class="form-select form-control-select2"
                                        placeholder="Pilih Therapis">
                                    </select2>
                                </div>
                            </div>
                            {{-- <div class="row mb-2">
                                <label class="col-form-label col-sm-4 col-12">Treatment <span
                                        class="text-red">*</span></label>
                                <div class="col-sm-8 col-12">
                                    <select2 url="{{ route('admin.package.select') }}" v-model="selectedPackage"
                                        modal="modal-package" class="form-select form-control-select2"
                                        placeholder="Pilih Treatment">
                                    </select2>
                                </div>
                            </div> --}}

                            <div class="row">
                                <div class="col-12">
                                    <div class="table-responsive w-100 mt-1">
                                        <div class="row" v-if="changePackageMsg !== ''">
                                            <div class="col-12 alert alert-danger alert-dismissible" role="alert">
                                                <p class="mb-0">
                                                    @{{ changePackageMsg }}
                                                </p>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                    aria-label="Close"></button>
                                            </div>
                                        </div>
                                        <table id="treatment_table" class="table table-bordered table-striped table-vcenter">
                                            <thead>
                                                <tr>
                                                    <th class="text-center">No</th>
                                                    <th class="text-center">Treatment</th>
                                                    <th class="text-center">Harga</th>
                                                    <th class="text-center">Opsi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(item, index) in packages">
                                                    <td class="text-center">@{{ index + 1 }}</td>
                                                    <td>@{{ item.name }}</td>
                                                    <td>@{{ item.price_str }}</td>
                                                    <td>
                                                        <button type="button" class="btn btn-success"
                                                            v-on:click="addPackage(item)"><i
                                                                class="fa fa-box-tissue"></i>Pilih Paket</button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END Item Modal -->

        <!-- Modal Enable FOC -->
        <div class="modal fade" id="modal-enable-foc" tabindex="-1" role="dialog" aria-labelledby="modal-block-popin"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-popin" role="document">
                <div class="modal-content">
                    <div class="block block-rounded block-transparent mb-0">
                        <div class="block-header block-header-default">
                            <h3 class="block-title">Enable FOC</h3>
                            <div class="block-options">
                                <button type="button" class="btn-block-option" data-bs-dismiss="modal"
                                    aria-label="Close">
                                    <i class="fa fa-fw fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="block-content fs-sm">
                            <p id="menu_description"></p>
                            <div class="col-12 alert alert-danger alert-dismissible" role="alert"
                                v-if="passwordCheck.apiMsg !== ''">
                                <p class="mb-0">
                                    @{{ passwordCheck.apiMsg }}
                                </p>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <label>Konfirmasi Password</label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <label>Apakah yakin enable FOC?</label>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-6">
                                    <input type="password" v-model="passwordCheck.password" class="form-control"
                                        placeholder="Konfirmasi Password" />
                                </div>
                            </div>
                        </div>
                        <div class="block-content block-content-full text-end bg-body">
                            <button type="button" id="btn_enable_foc_cancel" class="btn btn-sm btn-alt-secondary me-1"
                                data-bs-dismiss="modal">No</button>
                            <button type="button" id="btn_enable_foc_confirm" class="btn btn-sm btn-primary"
                                v-on:click="confirmEnableFoc()">Yes</button>
                            <i v-if="passwordCheck.isLoading" class="spinner-border spinner-border-sm text-green"
                                role="status"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END Modal Enable FOC -->
    </div>
@endsection

@section('styles')
    <!-- Page JS Plugins CSS -->
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-bs5/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-buttons-bs5/buttons.bootstrap5.min.css') }}">
@endsection

@section('scripts')
    <!-- jQuery -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('js/plugins/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-bs5/dataTables.bootstrap5.min.js') }}"></script>

    <script src="{{ asset('js/AutoNumeric.js') }}"></script>
    <script src="{{ asset('js/vue.js') }}"></script>
    <script src="{{ asset('js/axios.js') }}"></script>
    <script src="{{ asset('js/vue-autonumeric.js') }}"></script>
    <script type="text/x-template" id="select2-template">
        <select>
            <slot></slot>
        </select>
    </script>
    <script src="{{ asset('js/vueComponent-select2-monitor.js') }}"></script>

    <script>
        var app = new Vue({
            el: '#app',
            data: {
                autonumericFormatPercent: {
                    minimumValue: '0',
                    maximumValue: '100',
                    digitGroupSeparator: '.',
                    decimalCharacter: ',',
                    decimalPlaces: 2,
                    modifyValueOnWheel: false,
                    allowDecimalPadding: false,
                },
                autonumericFormat: {
                    minimumValue: '0',
                    maximumValue: '999999999999999',
                    digitGroupSeparator: '.',
                    decimalCharacter: ',',
                    decimalPlaces: 0,
                    modifyValueOnWheel: false
                },
                passwordCheck: {
                    password: '',
                    apiMsg: '',
                    isLoading: false,
                    focDisabled: 1,
                    discountDisabled: 1,
                },
                branch_id: {{ $branchId }},
                grandTotal: 0,
                partner: {
                    id: 0,
                    alias: ''
                },
                room: {
                    id: 0,
                    name: ''
                },
                cashier: {
                    id: {{ $admin->id }},
                    name: '{{ $admin->name }}'
                },
                isGoogleReview: false,
                googleReviewAmount: 0,
                foc: 'NO',
                focNumber: '',
                details: [],
                headerDiscount: 0,
                userPhone: '',
                items: [],
                itemDisplays: [],
                itemPagination: {
                    skip: 0,
                    take: 10,
                    search: '',
                    count: 0,
                    pages: [],
                },
                itemLoading: false,
                packages: [],
                packagePagination: {
                    skip: 0,
                    take: 100,
                    search: '',
                    count: 0,
                    pages: [],
                    isLoading: false,
                },
                addItemMsg: '',
                changePackageMsg: '',
                selectedCustomer: '',
                selectedRoom: null,
                selectedPartner: null,
                selectedPackage: null,
                payments: [{
                    id: 0,
                    amount: 0,
                    payment_id: {{ $firstPaymentId }},
                }],
                paymentTypes: [],
                saved_trx_id: 0,
                isPackageExists: false,
                selectedCoupon: 0,
                posMsg: '',
                inputChangeTotal: 0,
                inputCoupon: '',
                voucherCode: '',
                voucherMsg: '',
                voucherModel: null,
                isUsingInputVoucherCode: false,
                discountPercentage: 0,
                discountAmount: 0,
                discPercentageEnable: true,
                discAmountEnable: true,
                discountFocus: '',
                appUserFound: 0,
                isAppUserFound: false,
                appUserBirthdayFound: 0,
                isAppUserBirthday: false,
                appUserBirthdayDate: '',
                appUserMembershipName: '',
                appUserPoint: '',
                packagePartnerArr: [],
                is_loading_payment: false
            },
            computed: {
                grandTotalStr: {
                    get: function() {
                        if (this.details.length > 0) {
                            //this.grandTotal = this.details.reduce((acc, item) => acc + item.subtotal, 0);
                            this.grandTotal = this.details.reduce((acc, item) => acc + item.subtotal, 0) - this
                                .discountAmount - this.googleReviewAmount;
                            return formatRupiah(this.grandTotal + '', '');
                        } else {
                            return 0;
                        }

                        // let tmp = app.details.reduce((acc, item) => acc + item.subtotal, 0);
                        // return formatRupiah(tmp, '');
                    },
                    set: function(newValue) {}
                },
                changeTotal: function() {
                    let change = 0;
                    let total = this.grandTotal;
                    let tmpAllPayment = 0;

                    for (let i = 0; i < this.payments.length; i++) {
                        tmpAllPayment += this.payments[i].amount;
                    }
                    if ((tmpAllPayment - total) > 0) {
                        change = tmpAllPayment - total;
                    }

                    this.inputChangeTotal = change;

                    return change;
                },
                changeTotalStr: function() {
                    let separator = '';
                    let angka = this.changeTotal + '';
                    let number_string = angka.replace(/[^,\d]/g, '').toString(),
                        split = number_string.split(','),
                        sisa = split[0].length % 3,
                        rupiah = split[0].substr(0, sisa),
                        ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                    if (ribuan) {
                        separator = sisa ? '.' : '';
                        rupiah += separator + ribuan.join('.');
                    }

                    rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
                    return 'Rp' + rupiah + ',00';
                },
                // isPackageExists: {
                //     get: function () {
                //         let tmp = this.details.filter(function (x) {
                //             return x.type === 'package';
                //         });
                //
                //         debugger;
                //
                //         return tmp.length > 1;
                //     },
                //     set: function (newValue) {
                //     }
                // }
                havePackage: function() {
                    let result = false;
                    for (let i = 0; i < app.details.length; i++) {
                        if (app.details[i].type === 'package') {
                            result = true;
                        }
                    }

                    return result;
                }
            },
            watch: {
                discountPercentage() {
                    if (this.discountFocus == 'percentage') {
                        if (this.discountPercentage > 100) {
                            this.discountPercentage = 100;
                        }
                        let tmpDisc = this.details.reduce((acc, item) => acc + item.subtotal, 0) * (this
                            .discountPercentage / 100);
                        this.discountAmount = tmpDisc;
                    }
                    //this.grandTotal = this.details.reduce((acc, item) => acc + item.subtotal, 0) - tmpDisc;
                },
                discountAmount() {
                    if (this.discountFocus == 'amount') {
                        if (this.discountAmount > this.details.reduce((acc, item) => acc + item.subtotal, 0)) {
                            this.discountAmount = this.details.reduce((acc, item) => acc + item.subtotal, 0);
                        }
                        this.grandTotal -= this.discountAmount;
                        this.discountPercentage = (this.discountAmount / this.details.reduce((acc, item) => acc +
                            item.subtotal, 0)) * 100;
                    }
                }
            },
            methods: {
                setGoogleReview(){
                    this.isGoogleReview = true;
                    this.googleReviewAmount = 0;
                    for(let i=0;i<this.details.length;i++){
                        console.log(this.details[i]);
                        if(this.details[i].item_name === 'OIL THERAPY ( HOT )'){
                            this.googleReviewAmount += this.details[i].price * this.details[i].qty;
                        }
                    }
                    if(this.googleReviewAmount === 0){
                        this.isGoogleReview = false;
                    }
                },
                cancelGoogleReview(){
                    this.isGoogleReview = false;
                    this.googleReviewAmount = 0;
                },
                checkDiscount() {
                    if (this.details.length == 0) {
                        this.discountPercentage = 0;
                        this.discountAmount = 0;
                    } else {
                        let tmpDisc = this.discountPercentage * this.details.reduce((acc, item) => acc + item
                            .subtotal, 0) / 100;
                        this.grandTotal = this.details.reduce((acc, item) => acc + item.subtotal, 0) - tmpDisc;
                        this.discountAmount = tmpDisc;
                    }
                },
                removeDetail(item) {
                    this.details = this.details.filter(function(x) {
                        return x !== item;
                    });

                    let tmp = this.details.filter(function(x) {
                        return x.type === 'package';
                    });

                    this.packagePartnerArr = this.packagePartnerArr.filter(function(x) {
                        return x.partner_id !== item.partner_id;
                    });

                    this.isPackageExists = tmp.length > 0;
                    this.checkDiscount();
                },
                generateItemDisplay() {
                    let result = [];
                    let long = parseInt(app.itemPagination.skip) + parseInt(app.itemPagination.take);
                    if (app.items.length < long) {
                        long = app.items.length;
                    }

                    for (let i = app.itemPagination.skip; i < long; i++) {
                        result.push(app.items[i]);
                    }

                    app.itemDisplays = result;
                },
                searchItem() {
                    let result = [];
                    let endResult = [];
                    for (let i = 0; i < app.items.length; i++) {
                        if (app.items[i].name.toLowerCase() === app.itemPagination.search.toLowerCase() ||
                            app.items[i].name.toLowerCase().includes(app.itemPagination.search.toLowerCase())) {
                            result.push(app.items[i]);
                        }
                    }

                    app.itemPagination.skip = 0;
                    app.itemPagination.take = 10;

                    let long = parseInt(app.itemPagination.skip) + parseInt(app.itemPagination.take);
                    if (long > result.length) {
                        long = result.length;
                    }
                    for (let i = app.itemPagination.skip; i < long; i++) {
                        endResult.push(result[i]);
                    }

                    app.itemDisplays = endResult;
                    //return result;
                },
                async getAllItems() {
                    await axios({
                        method: 'get',
                        url: '{{ route('admin.pos.get_item_all') }}',
                    }).then(function(response) {
                        if (response.status === 200) {
                            app.items = response.data;
                            console.log(app.items);
                            app.generateItemDisplay();
                            console.log(app.itemDisplays);
                        }
                    });
                },
                async showItemModal() {
                    await app.getAllItems();
                    modalItem.show();
                },
                checkOnStock(item) {
                    let idx = app.items.indexOf(item);
                    if (app.items[idx].is_unlimited_stock == 0) {
                        if(app.items[idx].is_addon == 1){
                            app.items[idx].qty = 1;
                        }
                        else if (app.items[idx].qty > app.items[idx].stock) {
                            app.items[idx].qty = app.items[idx].stock;
                        }
                    }
                },
                async nextItemPage() {
                    if ((app.itemPagination.skip + 10) < app.items.length) {
                        app.itemPagination.skip += 10;
                        //await app.getItems();
                        app.generateItemDisplay();
                    }
                },
                async previousItemPage() {
                    if (app.itemPagination.skip >= 10) {
                        app.itemPagination.skip -= 10;
                        //await app.getItems();
                        app.generateItemDisplay();
                    }
                },
                calculateTransaction() {
                    if (app.foc === 'FOC') {
                        for (let i = 0; i < app.details.length; i++) {
                            app.details[i].discount_percentage = 100;
                            app.details[i].discount_amount = app.details[i].subtotal;
                            app.details[i].discount_str = formatRupiah(app.details[i].discount_amount + '', '');
                            app.details[i].subtotal = app.details[i].subtotal - app.details[i].discount_amount;
                            app.details[i].subtotal_str = formatRupiah(app.details[i].subtotal + '', '');
                        }
                    } else if (app.foc === 'FOC_PACKAGE') {
                        //Discount on Treatment
                        for (let i = 0; i < app.details.length; i++) {
                            if (app.details[i].type === 'package') {
                                app.details[i].discount_percentage = 100;
                                app.details[i].discount_amount = app.details[i].subtotal;
                                app.details[i].discount_str = formatRupiah(app.details[i].discount_amount + '', '');
                                app.details[i].subtotal = app.details[i].subtotal - app.details[i].discount_amount;
                                app.details[i].subtotal_str = formatRupiah(app.details[i].subtotal + '', '');
                            } else {
                                app.details[i].discount_percentage = 0;
                                app.details[i].discount_amount = 0;
                                app.details[i].discount_str = formatRupiah(app.details[i].discount_amount + '', '');
                                app.details[i].subtotal = app.details[i].price * app.details[i].qty;
                                app.details[i].subtotal_str = formatRupiah(app.details[i].subtotal + '', '');
                            }
                        }
                    } else if (app.foc === 'FOC_ITEM') {
                        //Discount on ITEM
                        for (let i = 0; i < app.details.length; i++) {
                            if (app.details[i].type === 'item') {
                                app.details[i].discount_percentage = 100;
                                app.details[i].discount_amount = app.details[i].subtotal;
                                app.details[i].discount_str = formatRupiah(app.details[i].discount_amount + '', '');
                                app.details[i].subtotal = app.details[i].subtotal - app.details[i].discount_amount;
                                app.details[i].subtotal_str = formatRupiah(app.details[i].subtotal + '', '');
                            } else {
                                app.details[i].discount_percentage = 0;
                                app.details[i].discount_amount = 0;
                                app.details[i].discount_str = formatRupiah(app.details[i].discount_amount + '', '');
                                app.details[i].subtotal = app.details[i].price * app.details[i].qty;
                                app.details[i].subtotal_str = formatRupiah(app.details[i].subtotal + '', '');
                            }
                        }
                    } else if (app.foc === 'NO') {
                        //Reset Transaction
                        for (let i = 0; i < app.details.length; i++) {
                            app.details[i].discount_percentage = 0;
                            app.details[i].discount_amount = 0;
                            app.details[i].discount_str = formatRupiah(app.details[i].discount_amount + '', '');
                            app.details[i].subtotal = app.details[i].price * app.details[i].qty;
                            app.details[i].subtotal_str = formatRupiah(app.details[i].subtotal + '', '');
                        }
                    }
                },
                showFocModal() {
                    app.passwordCheck.password = '';
                    app.passwordCheck.apiMsg = '';
                    modalEnableFoc.show();
                },
                async addItems() {
                    let data = {
                        // locker_id: app.activeLocker.id,
                        //trx_id: app.header.header.id,
                        items: [],
                    };

                    let tmpItems = app.items.filter(n => n.selected === true);
                    //generate parameters
                    let canSubmit = true;
                    for (let i = 0; i < tmpItems.length; i++) {
                        let idx = app.items.indexOf(tmpItems[i]);
                        let nParam = {
                            id: app.items[idx].id,
                            qty: app.items[idx].qty
                        };

                        if (app.items[idx].qty <= 0) {
                            canSubmit = false;
                        }

                        data.items.push(nParam);
                    }

                    if (canSubmit) {
                        let no = 100;
                        for (let i = 0; i < tmpItems.length; i++) {
                            let idx = app.items.indexOf(tmpItems[i]);
                            let tmpSubtotal = app.items[idx].qty * app.items[idx].price;
                            let nDt = {
                                id: no,
                                type: 'item',
                                package_id: 0,
                                package_name: '',
                                room_id: 0,
                                room_name: '',
                                partner_id: 0,
                                partner_alias: '',
                                coupon_id: 0,
                                customer_name: '',
                                item_id: app.items[idx].id,
                                item_name: app.items[idx].name,
                                qty: app.items[idx].qty,
                                price: app.items[idx].price,
                                subtotal: tmpSubtotal,
                                price_str: formatRupiah(app.items[idx].price + '', ''),
                                subtotal_str: formatRupiah(tmpSubtotal + '', ''),
                                is_cut: 0,
                                is_birthday_promo: false,
                                is_item_addon: app.items[idx].is_addon
                            };

                            //if we got the same name
                            $isFoundDuplicate = false;
                            for(let j=0; j<app.details.length; j++){
                                if(app.details[j].item_name === nDt.item_name && app.details[j].is_item_addon == 0){
                                    app.details[j].qty = parseInt(app.details[j].qty) + parseInt(nDt.qty);
                                    app.details[j].subtotal = app.details[j].qty * app.items[idx].price;
                                    app.details[j].subtotal_str = formatRupiah(app.details[j].subtotal + '', '');

                                    $isFoundDuplicate = true;
                                    break;
                                }
                            }
                            // let tmp = app.details.filter(n => n.item_name === nDt.item_name);
                            // if (tmp.length === 0) {
                            //     app.details.push(nDt);
                            // }

                            if(!$isFoundDuplicate){
                                app.details.push(nDt);
                            }

                            no++;
                        }
                        // app.itemLoading = false;
                        modalItem.hide();
                    } else {
                        app.addItemMsg = 'Min Qty 1!';
                    }
                    app.itemPagination.search = '';
                },
                async getPackages() {
                    app.fcLoading = true;
                    app.packagePagination.isLoading = true;
                    await axios({
                        method: 'get',
                        url: '<?php echo e(route('admin.package.get')); ?>?skip=' + app.packagePagination.skip +
                            '&take=' + app.packagePagination.take +
                            '&search=' + app.packagePagination.search +
                            '&is_birthday=' + app.appUserBirthdayFound,
                    }).then(function(response) {
                        if (response.status === 200) {
                            app.packages = response.data.packages;
                            app.packagePagination.count = response.data.count;
                            app.packagePagination.pages = response.data.pages;
                        }
                    });
                    app.packagePagination.isLoading = false;
                    app.fcLoading = false;
                },
                async showPackageModal() {
                    await app.getPackages();
                    modalPackage.show();
                },
                async addPackage(item) {
                    if (!app.selectedRoom || !app.selectedPartner) {
                        return false;
                    }

                    let selectedRoomArr = app.selectedRoom.split('#');
                    let selectedPartnerArr = app.selectedPartner.split('#');

                    let price = item.price;
                    let subtotal = item.price;
                    let priceStr = formatRupiah(item.price + '', '');
                    let subtotalStr = formatRupiah(item.price + '', '');
                    if (item.is_birthday_promo == 1) {
                        price = 0;
                        subtotal = 0;
                        priceStr = 'Rp0,00'
                        subtotalStr = 'Rp0,00'
                    }

                    let nDt = {
                        id: 202,
                        type: 'package',
                        package_id: item.id,
                        package_name: item.name,
                        room_id: selectedRoomArr[0],
                        room_name: selectedRoomArr[1],
                        partner_id: selectedPartnerArr[0],
                        partner_alias: selectedPartnerArr[1],
                        item_id: 0,
                        item_name: '',
                        coupon_id: 0,
                        customer_name: app.selectedCustomer,
                        qty: 1,
                        price: price,
                        subtotal: subtotal,
                        price_str: priceStr,
                        subtotal_str: subtotalStr,
                        is_cut: 0,
                        is_birthday_promo: item.is_birthday_promo == 1 ? 1 : 0
                    };

                    // let tmp = app.details.filter(n => n.package_id === nDt.package_id);
                    // if(tmp.length === 0){
                    //     app.details.push(nDt);
                    // }

                    app.details.push(nDt);

                    // Push partner data for addon purpose
                    this.packagePartnerArr.push({
                        partner_id: selectedPartnerArr[0],
                        partner_alias: selectedPartnerArr[1],
                    });

                    app.selectedRoom = null;
                    app.selectedPartner = null;
                    app.selectedCustomer = '';
                    app.isPackageExists = true;

                    modalPackage.hide();
                },
                submitForm() {
                    this.is_loading_payment = true;

                    // Validate FOC
                    let isValidated = true;
                    if (app.foc !== 'NO') {
                        if (app.focNumber === '') {
                            app.posMsg = 'Masukan Nomor FOC terlebih dahulu!';
                            isValidated = false;
                        }
                    }

                    if (!isValidated) {
                        this.is_loading_payment = false;
                        return false;
                    }

                    $('#btn_submit').hide();
                    $('#btn_loading').show(200);
                    $('#section_validation_error').hide();

                    let form = $('#form_create_transaction')[0];
                    let formData = new FormData(form);

                    $.ajax({
                        type: 'POST',
                        url: '{{ route('admin.booking.start_treatment_multiple') }}',
                        data: formData,
                        processData: false,
                        contentType: false,
                        cache: false,
                        enctype: 'multipart/form-data',
                        success: function(data) {
                            if (data.success) {
                                let url = '{{ route('admin.monitor_room.index') }}';
                                One.helpers('jq-notify', {
                                    type: 'info',
                                    icon: 'fa fa-info-circle me-1',
                                    message: data.message
                                });
                                window.location.href = url;
                            } else {
                                One.helpers('jq-notify', {
                                    type: 'danger',
                                    icon: 'fa fa-info-circle me-1',
                                    message: 'An error occurred!'
                                });
                            }

                            // let error = data['error'];
                            // if (data['success'] === 'true') {
                            //     // One.helpers('jq-notify', {
                            //     //     type: 'success',
                            //     //     icon: 'fa fa-info-circle me-1',
                            //     //     timer: 2000,
                            //     //     message: 'Transaksi sukses tersimpan!'
                            //     // });
                            //     // app.saved_trx_id = data['result']['saved_trx_id'];

                            //     // location.reload();
                            // } else if (error === 'validation_failed') {
                            //     One.helpers('jq-notify', {
                            //         type: 'danger',
                            //         icon: 'fa fa-info-circle me-1',
                            //         timer: 2000,
                            //         message: 'Validasi gagal!'
                            //     });

                            //     $('#validation_error_list').empty();

                            //     if (data['result']) {
                            //         for (let errorIdx = 0; errorIdx < data['result']
                            //             .length; errorIdx++) {
                            //             $('#validation_error_list').append('<li>' + data['result'][
                            //                 errorIdx
                            //             ] + '</li>');
                            //         }

                            //         $('#section_validation_error').show(100);

                            //         // Scroll to validation feedback list
                            //         $([document.documentElement, document.body]).animate({
                            //             scrollTop: $("#validation_error_list").offset().top -
                            //                 100
                            //         }, 100);
                            //     }

                            //     $('#btn_submit').show(200);
                            //     $('#btn_loading').hide(200);

                            // } else if (error === 'validation_custom_failed') {
                            //     One.helpers('jq-notify', {
                            //         type: 'danger',
                            //         icon: 'fa fa-info-circle me-1',
                            //         timer: 2000,
                            //         message: 'Validasi gagal!'
                            //     });

                            //     $('#validation_error_list').empty();

                            //     if (data['result']) {
                            //         for (let errorIdx = 0; errorIdx < data['result']
                            //             .length; errorIdx++) {
                            //             $('#validation_error_list').append('<li>' + data['result'][
                            //                 errorIdx
                            //             ] + '</li>');
                            //         }

                            //         $('#section_validation_error').show(100);

                            //         // Scroll to validation feedback list
                            //         $([document.documentElement, document.body]).animate({
                            //             scrollTop: $("#validation_error_list").offset().top -
                            //                 100
                            //         }, 100);
                            //     }

                            //     $('#btn_submit').show(200);
                            //     $('#btn_loading').hide(200);
                            // } else if (error === 'bad_request') {
                            //     One.helpers('jq-notify', {
                            //         type: 'danger',
                            //         icon: 'fa fa-info-circle me-1',
                            //         timer: 2000,
                            //         message: 'Terjadi kesalahan input data!'
                            //     });

                            //     $('#btn_submit').show(200);
                            //     $('#btn_loading').hide(200);
                            // } else if (error === 'query_exception') {
                            //     One.helpers('jq-notify', {
                            //         type: 'danger',
                            //         icon: 'fa fa-info-circle me-1',
                            //         timer: 2000,
                            //         message: 'Terjadi kesalahan pada penyimpanan data transaksi!'
                            //     });

                            //     $('#btn_submit').show(200);
                            //     $('#btn_loading').hide(200);
                            // }
                        },
                        error: function(data) {
                            One.helpers('jq-notify', {
                                type: 'danger',
                                icon: 'fa fa-info-circle me-1',
                                timer: 2000,
                                message: 'Terjadi kesalahan internal sistem: ' + data['error']
                            });
                            $('#btn_submit').show(200);
                            $('#btn_loading').hide(200);
                        }
                    });
                },
                async getPaymentTypes() {
                    await axios({
                        method: 'get',
                        url: '{{ route('admin.pos.get_payment_types') }}',
                    }).then(function(response) {
                        if (response.status === 200) {
                            app.paymentTypes = response.data;
                        }
                    });
                },
                async showModalPayment() {
                    app.posMsg = '';
                    await app.getPaymentTypes();

                    this.payments = [{
                        id: 0,
                        amount: app.grandTotal,
                        amountStr: formatRupiah(app.grandTotal + ''),
                        payment_id: {{ $firstPaymentId }},
                    }];
                    modalPayment.show();
                },
                addMorePayment() {
                    let tmpGrandTotal = app.grandTotal;
                    tmpGrandTotal = tmpGrandTotal / (app.payments.length + 1);
                    let tmpData = [];

                    for (let i = 0; i < app.payments.length + 1; i++) {
                        let tmp = {
                            id: i,
                            amount: 0,
                            amountStr: '0',
                            payment_id: 1,
                        };
                        if (i === 0) {
                            tmp.amount = app.grandTotal;
                            tmp.amountStr = formatRupiah(app.grandTotal + '');
                        }
                        tmpData.push(tmp);
                    }
                    app.payments = tmpData;
                },
                deletePayment(item) {
                    this.payments = this.payments.filter(function(x) {
                        return x !== item;
                    });
                },
                applyCoupon() {
                    if (app.selectedCoupon == '0') {
                        return false;
                    }

                    let couponArr = app.selectedCoupon.split('#');
                    let discountPercentage = parseFloat(couponArr[1]);
                    let discountFlat = parseInt(couponArr[2]);

                    // let isDoneDiscounted = false;
                    for (let i = 0; i < app.details.length; i++) {
                        if (app.details[i].type === 'package') {
                            app.details[i].coupon_id = couponArr[0];

                            let discount = 0;
                            if (discountPercentage > 0) {
                                discount = (app.details[i].price * app.details[i].qty) * (discountPercentage / 100);
                            } else {
                                if (discountFlat > 0) {
                                    discount = discountFlat;
                                }
                            }

                            app.details[i].discount = discount;
                            app.details[i].discount_str = formatRupiah(discount + '', '');

                            let subtotal = (app.details[i].price * app.details[i].qty) - discount;
                            app.details[i].subtotal = subtotal;
                            app.details[i].subtotal_str = formatRupiah(subtotal + '', '');
                            // isDoneDiscounted = true;
                        }
                    }
                },
                async checkOnVoucher() {
                    if (app.voucherCode.length > 7) {
                        if (app.selectedCoupon != 0) {
                            One.helpers('jq-notify', {
                                type: 'warning',
                                icon: 'fa fa-info-circle me-1',
                                message: 'Pilihan Kupon harus dikosongkan!'
                            });
                            return false;
                        }

                        if (app.details.length === 0) {
                            app.voucherMsg = 'Pilih item terlebih dahulu!';
                            $('#voucher_success').show();
                        } else {
                            //check
                            $('#voucher_code').prop('readonly', true);
                            $('#voucher_check_loading').show();
                            $('#voucher_success').hide();
                            //axios time
                            let data = {
                                'voucher_code': app.voucherCode,
                            };

                            await axios({
                                method: 'post',
                                url: '{{ route('admin.voucher.check') }}',
                                data: data
                            }).catch(function(error) {
                                if (error.response) {
                                    if (error.response.status === 400 || error.response.status ===
                                        404) {
                                        One.helpers('jq-notify', {
                                            type: 'warning',
                                            icon: 'fa fa-info-circle me-1',
                                            message: error.response.data
                                        });
                                    }
                                    if (error.response.status === 500) {
                                        One.helpers('jq-notify', {
                                            type: 'warning',
                                            icon: 'fa fa-info-circle me-1',
                                            message: 'Maaf terjadi kesalahan!'
                                        });
                                    }
                                }

                                $('#voucher_code').prop('readonly', false);
                                $('#voucher_check_loading').hide();
                                $('#voucher_success').hide();
                            }).then(function(response) {
                                if (response.status === 200) {
                                    One.helpers('jq-notify', {
                                        type: 'success',
                                        icon: 'fa fa-info-circle me-1',
                                        message: 'Sukses Menggunakan Voucher!'
                                    });

                                    app.voucherModel = response.data;
                                    app.resetDiscount();

                                    discountPercentage = 0;
                                    discountFlat = 0;
                                    if (app.voucherModel.discount_type === 'amount') {
                                        discountFlat = app.voucherModel.discount_amount;
                                        app.voucherMsg = 'Diskon Rp' + app.voucherModel.discount_amount;

                                    } else {
                                        if (app.havePackage) {
                                            discountPercentage = app.voucherModel.discount_percentage;
                                            app.voucherMsg = 'Diskon Paket ' + app.voucherModel
                                                .discount_percentage + '%';
                                            //counting discount
                                            // console.log(app.details);
                                            // app.countingDetailDiscount();
                                        } else {
                                            app.voucherMsg =
                                                'Pilih paket terlebih dahulu lalu masukan Kode Voucher!';
                                        }
                                    }

                                    $('#voucher_code').prop('readonly', false);
                                    $('#voucher_check_loading').hide();
                                    $('#voucher_success').show();
                                    //applying discount

                                    let voucherDiscAmount = 0;
                                    if (app.voucherModel.master_type === 'discount' && app.voucherModel
                                        .discount_type === 'amount') {
                                        voucherDiscAmount = app.voucherModel.discount_amount;
                                    }

                                    for (let i = 0; i < app.details.length; i++) {
                                        if (app.details[i].type === 'package' && app.voucherModel
                                            .type === 'package') {
                                            app.details[i].coupon_id = app.voucherModel
                                                .voucher_header_id;
                                            let discount = 0;

                                            if (app.voucherModel.master_type === 'discount') {
                                                if (discountPercentage > 0) {
                                                    discount = (app.details[i].price * app.details[i]
                                                        .qty) * (discountPercentage / 100);
                                                } else {
                                                    if (discountFlat > 0) {
                                                        discount = discountFlat;
                                                    }
                                                }
                                            } else {
                                                for (let h = 0; i < app.voucherModel
                                                    .free_treatments; h++) {
                                                    if (app.details[i].package_id == app.voucherModel
                                                        .free_treatments[h].package_id) {
                                                        discount = (app.details[i].price * app.details[
                                                            i].qty);
                                                        break;
                                                    }
                                                }
                                            }

                                            app.details[i].discount = discount;
                                            app.details[i].discount_str = formatRupiah(discount + '',
                                                '');

                                            let subtotal = (app.details[i].price * app.details[i].qty) -
                                                discount;
                                            app.details[i].subtotal = subtotal;
                                            app.details[i].subtotal_str = formatRupiah(subtotal + '',
                                                '');
                                        } else if (app.details[i].type === 'item' && app.voucherModel
                                            .type === 'item') {
                                            if (voucherDiscAmount > 0) {
                                                if (voucherDiscAmount - app.details[i].subtotal >= 0) {
                                                    app.details[i].discount = app.details[i].subtotal;
                                                    let tmpSubtotal = app.details[i].subtotal;
                                                    app.details[i].discount_str = formatRupiah(app
                                                        .details[i].subtotal + '', '');
                                                    app.details[i].subtotal = 0;
                                                    app.details[i].subtotal_str = formatRupiah(0 + '',
                                                        '');

                                                    voucherDiscAmount -= tmpSubtotal;
                                                } else if (voucherDiscAmount - app.details[i].subtotal <
                                                    0) {
                                                    app.details[i].discount = voucherDiscAmount;
                                                    app.details[i].discount_str = formatRupiah(
                                                        voucherDiscAmount + '', '');

                                                    let subtotal = app.details[i].subtotal -
                                                        voucherDiscAmount;
                                                    app.details[i].subtotal = subtotal;
                                                    app.details[i].subtotal_str = formatRupiah(
                                                        subtotal + '', '');

                                                    voucherDiscAmount = 0;
                                                }
                                            }
                                        }

                                    }

                                    app.isUsingInputVoucherCode = true;
                                }
                            });
                        }
                    } else {
                        $('#voucher_check_loading').hide();
                        $('#voucher_success').hide();
                    }
                },
                resetDiscount() {
                    for (let i = 0; i < app.details.length; i++) {
                        //counting discount
                        if (app.details[i].type === 'package') {
                            app.details[i].subtotal = app.details[i].qty * app.details[i].price;
                            app.details[i].discount = 0;
                            app.details[i].discount_str = formatRupiah('0', '');
                        }
                    }
                },
                async confirmEnableFoc() {
                    app.passwordCheck.isLoading = true;
                    $('#btn_enable_foc_cancel').hide();
                    $('#btn_enable_foc_confirm').hide();

                    let data = {
                        password: app.passwordCheck.password
                    };

                    await axios({
                        method: 'post',
                        url: '{{ route('admin.cashier.password') }}',
                        data: data,
                    }).catch(function(error) {
                        if (error.response) {
                            app.passwordCheck.apiMsg = error.response.data;
                            app.passwordCheck.isLoading = false;
                            $('#btn_enable_foc_cancel').show();
                            $('#btn_enable_foc_confirm').show();
                        }
                    }).then(function(response) {
                        if (response.status === 200) {
                            One.helpers('jq-notify', {
                                type: 'success',
                                icon: 'fa fa-info-circle me-1',
                                message: 'Sukses Enable FOC!'
                            });
                            //clean data
                            //$('#foc_select').prop('disabled', false);
                            app.passwordCheck.focDisabled = 0;
                        }
                    });

                    app.passwordCheck.isLoading = false;
                    $('#btn_enable_foc_cancel').show();
                    $('#btn_enable_foc_confirm').show();
                    modalEnableFoc.hide();
                },
                async checkAppUser() {
                    if (app.userPhone.length > 6) {
                        await axios({
                            method: 'post',
                            url: '{{ route('admin.app_customer.birthday.check') }}',
                            data: {
                                phone: app.userPhone
                            },
                        }).catch(function(error) {

                        }).then(function(response) {
                            if (response.status === 200) {
                                One.helpers('jq-notify', {
                                    type: 'success',
                                    icon: 'fa fa-info-circle me-1',
                                    message: 'Sukses cek User Aplikasi!'
                                });
                                app.isAppUserFound = true;
                                app.appUserFound = 1;
                                app.appUserMembershipName = response.data.membership;
                                app.appUserPoint = response.data.point;
                                if (response.data.is_birthday) {
                                    app.appUserBirthdayFound = 1;
                                    app.isAppUserBirthday = true;
                                    app.appUserBirthdayDate = response.data.bithday_date;
                                }
                            }
                        });
                    }
                },
                cancelAppUser() {
                    app.isAppUserFound = false;
                    app.appUserFound = 0;
                    app.appUserBirthdayFound = 0;
                    app.isAppUserBirthday = false;
                    app.appUserBirthdayDate = '';
                    app.userPhone = '';
                    app.details = [];
                }
            },
            mounted: function() {
                $('.disc_input').on('focus', function() {
                    app.discountFocus = $(this).data('type');
                });
                // flatpickr('.js-flatpickr', {
                //     "locale": "id" // locale for this instance only
                // });
            },
        });
    </script>
    <script>
        let modalItem = new bootstrap.Modal(document.getElementById('modal-item-qty-old'), {
            'backdrop': 'static',
            'keyboard': false
        });
        let modalPackage = new bootstrap.Modal(document.getElementById('modal-package'), {
            'backdrop': 'static',
            'keyboard': false
        });
        let modalPayment = new bootstrap.Modal(document.getElementById('modal-payment'), {
            'backdrop': 'static',
            'keyboard': false
        });
        let modalEnableFoc = new bootstrap.Modal(document.getElementById('modal-enable-foc'), {
            'backdrop': 'static',
            'keyboard': false
        });


        function formatRupiah(angka, prefix) {
            let number_string = angka.replace(/[^,\d]/g, '').toString(),
                split = number_string.split(','),
                sisa = split[0].length % 3,
                rupiah = split[0].substr(0, sisa),
                ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
            // return rupiah + ',00';
            return rupiah + ',00';
        }

        $('#treatment_table').dataTable({
            searching: true, // Enable search (default is true)
            paging: true,    // Enable pagination
            // info: true       // Show table information
        });
    </script>
@endsection
