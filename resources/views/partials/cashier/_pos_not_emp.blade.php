<!-- POS Modal -->
<div class="modal" id="modal-pos" tabindex="-1" role="dialog" aria-labelledby="modal-pos" aria-hidden="true">
    <div class="modal-dialog modal-dialog-popin modal-xl" role="document">
        <div class="modal-content">
            <div class="block block-rounded block-transparent mb-0">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Transaksi</h3>
                    <div class="block-options">
                        <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fa fa-fw fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="block-content fs-sm">
                    <div class="row">
                        <div class="col-2">
                            <span>Nomor Locker</span>
                        </div>
                        <div class="col-10">
                            <span v-if="activeLocker !== null">@{{ activeLocker.number }}</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-2">
                            <span>Therapis</span>
                        </div>
                        <div class="col-10">
                            <span v-for="partner in partners">@{{ partner }}<span v-if="partners.length > 1">,</span></span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-2">
                            <span>Gro</span>
                        </div>
                        <div class="col-10">
                            <span v-if="gro !== null">@{{ gro.number }}</span>
                        </div>
                    </div>
                    <div class="row" v-if="memberSearch.loading">
                        <div class="col-12 mt-2 mb-2 justify-content-center">
                            <div class="spinner-border text-info">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                    <div class="row" v-else>
                        <div class="col-12 alert alert-success alert-dismissible" role="alert" v-if="!memberSearch.notFound">
                            @{{ memberSearch.name }} Ditemukan!
                        </div>
                        <div class="col-12 alert alert-danger alert-dismissible" role="alert" v-if="memberSearch.message !== ''">
                            <p class="mb-0">
                                @{{ memberSearch.message }}
                            </p>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 alert alert-danger alert-dismissible" role="alert" v-if="posMsg !== ''">
                            <p class="mb-0">
                                @{{ posMsg }}
                            </p>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
{{--                    <div class="row">--}}
{{--                        <div class="col-2">--}}
{{--                            <span>Member?</span>--}}
{{--                        </div>--}}
{{--                        <div class="col-6">--}}
{{--                            <input type="text" v-model="memberSearch.code" class="form-control" v-if="foc === 'FOC' || foc === 'FOC_PACKAGE' || foc === 'FOC_ITEM'" readonly/>--}}
{{--                            <input type="text" v-model="memberSearch.code" class="form-control" v-else/>--}}
{{--                        </div>--}}
{{--                        <div class="col-4">--}}
{{--                            <button type="button" v-on:click="checkMember" class="btn btn-info" v-if="foc === 'FOC' || foc === 'FOC_PACKAGE' || foc === 'FOC_ITEM'" disabled>Check</button>--}}
{{--                            <button type="button" v-on:click="checkMember" class="btn btn-info" v-else>Check</button>--}}
{{--                        </div>--}}
{{--                    </div>--}}
                    <div class="row mt-2 mb-2">
                        <div class="col-2">
                            <span>FOC?</span>
                        </div>
                        <div class="col-6">
                            <select class="form-select" v-model="foc" v-on:change="calculateTransaction" id="foc_select" :disabled="passwordCheck.focDisabled == 1">
                                <option value="NO" selected>Tidak FOC</option>
                                <option value="FOC">FOC</option>
                                <option value="FOC_PACKAGE">FOC Paket</option>
                                <option value="FOC_ITEM">FOC Item</option>
                            </select>
                        </div>
                        <div class="col-2">
                            <button type="button" class="btn btn-success" id="btn_enable_foc" v-on:click="showFocModal">Enable FOC</button>
                        </div>
                    </div>
                    <div class="row mt-2" v-if="foc !== 'NO'">
                        <div class="col-2">
                            <span>Nama FOC</span>
                        </div>
                        <div class="col-6">
                            <input type="text" v-model="focNumber" class="form-control" placeholder="Nama FOC"/>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-6 text-start">
                            <button type="button" class="btn btn-info" v-on:click="showMembershipModal"><i class="fa fa-plus"></i> Beli Membership</button>
                        </div>
                        <div class="col-6 text-end">
                            <button type="button" class="btn btn-success" v-on:click="refreshPayment"><i class="fa fa-sync"></i> Refresh</button>
                            &nbsp;
                            <button type="button" class="btn btn-primary" v-on:click="showModalPayment"><i class="fa fa-money-bill"></i> Payment</button>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-12 text-end">
                            <span class="fw-bold text-danger">WAJIB KLIK TOMBOL REFRESH SEBELUM LANJUT KE PEMBAYARAN!</span>
                        </div>
                    </div>
                    <div class="row mt-2" v-if="isHappyHour">
                        <div class="col-6">
                            <span style="color: indianred; font-size: 20px;">Happy Hour!</span>
                        </div>
                    </div>
                    <div class="table-responsive w-100 mt-1">
                        <table class="table table-bordered table-striped table-vcenter js-dataTable-full" id="table_data_transaction">
                            <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Item</th>
                                <th width="20%">Qty</th>
                                <th>Harga</th>
                                <th>Diskon</th>
                                <th>Subtotal</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(item, index) in details">
                                <td>@{{ index+1 }}</td>
                                <td>
                                    <span v-if="item.type === 'package'">@{{ item.package_name }}</span>
                                    <span v-if="item.type === 'item'">@{{ item.item_name }}</span>
                                    <span v-if="item.is_facility_only">Hanya Fasilitas</span>
                                    <span v-if="item.type === 'membership'">
                                        @{{ item.membership_name }}
                                        <br/>
                                        Data Member<br/>
                                        Number: @{{ memberData.number }}<br/>
                                        Nama: @{{ memberData.name }}<br/>
                                        Email: <span v-if="memberData.email !== ''">@{{ memberData.email }}</span>
                                            <span v-else>-</span>
                                        <br/>
                                        Phone: <span v-if="memberData.phone !== ''">@{{ memberData.phone }}</span>
                                            <span v-else>-</span>
                                        <br/>
                                    </span>
                                </td>
                                <td>
                                    <input type="number" class="form-control text-end" v-if="item.type === 'package' || item.type === 'membership'" v-model="item.qty" readonly/>
                                    <input type="number" class="form-control text-end" v-if="item.type === 'item' && item.is_cut == 0" v-model="item.qty" readonly/>
                                    <input type="number" class="form-control text-end" v-else-if="item.type === 'item' && item.is_cut == 1" v-model="item.qty" readonly/>
                                    <input type="number" class="form-control text-end" v-if="item.is_facility_only" value="1" readonly/>
                                </td>
                                <td class="text-end">@{{ item.price_str }}</td>
{{--                                <td class="text-end">--}}
{{--                                    <input type="number" class="form-control text-end" v-model="item.discount_percentage" v-if="item.package_name == 'Penambahan Therapis' || item.package_name == 'Penambahan Customer'" readonly/>--}}
{{--                                    <input type="number" class="form-control text-end" v-model="item.discount_percentage" v-on:input="calculateDiscount(item)" v-else-if="item.type !== 'item'"/>--}}
{{--                                    <input type="number" class="form-control text-end" v-model="item.discount_percentage" v-else readonly/>--}}
{{--                                </td>--}}
                                <td class="text-end">@{{ item.discount_str }}</td>
                                <td><input type="text" class="form-control text-end" v-model="item.subtotal_str" readonly/></td>
{{--                                <td>--}}
{{--                                    <button type="button" class="btn btn-primary" v-if="item.type === 'package'" v-on:click="showPackageModal()"><i class="fa fa-box-tissue"></i> Ubah Paket</button>--}}
{{--                                    <button type="button" class="btn btn-danger" v-if="item.type === 'item'" v-on:click="showDeleteItemModal(item)"><i class="fa fa-minus"></i> Hapus</button>--}}
{{--                                </td>--}}
                            </tr>
                            <tr>
                                <td colspan="5" class="text-end">
                                    <button id="btn_enable_discount" type="button" class="btn btn-success" v-on:click="showDiscountModal">Enable Discount</button>
                                    <b>Tambahan Diskon</b>
                                </td>
                                <td>
                                    <div class="input-group">
                                        <span class="input-group-text fw-bold">-</span>
                                        <vue-autonumeric :options="autonumericFormat" v-model="headerDiscount" class="form-control text-end fw-bold" id="discount_amount" :readonly="passwordCheck.discountDisabled == 1"></vue-autonumeric>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-end"><b>Total</b></td>
                                <td class="text-end"><b>@{{ grandTotalStr }}</b></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END POS Modal -->

<!-- Payment Modal -->
<div class="modal" id="modal-payment" tabindex="-1" role="dialog" aria-labelledby="modal-payment" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="block block-rounded block-transparent mb-0">
                <div class="block-header block-header-default" style="background: red; color: white;">
                    <h3 class="block-title">Pembayaran</h3>
                    <div class="block-options">
                        <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">
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
                            <button type="button" id="btn_finish_payment" class="btn btn-primary" v-on:click="submitTransaction"><i class="fa fa-money"></i>Finish Pembayaran</button>
                        </div>
                        <div class="col-6 text-end" v-if="grandTotal !== 0">
                            <button type="button" id="btn_add_more_payment" class="btn btn-success" v-on:click="addMorePayment"><i class="fa fa-plus"></i>Tambah Pembayaran</button>
                        </div>
                        <div id="payment_loading" class="spinner-border text-info col-12" style="display: none;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div class="table-responsive w-100 mt-1">
                        <table class="table table-bordered table-striped table-vcenter js-dataTable-full" id="table_data_ubah_paket">
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
                                <td>@{{ index+1 }}</td>
                                <td>
                                    <select class="form-select" v-model="item.payment_id">
                                        <option v-for="(payment, index) in payment_type" :value="payment.id">@{{ payment.description }}</option>
                                    </select>
                                </td>
                                <td>
                                    <vue-autonumeric :options="autonumericFormat" v-if="grandTotal == 0" readonly v-model="item.amount" class="form-control"></vue-autonumeric>
                                    <vue-autonumeric :options="autonumericFormat" v-else v-model="item.amount" class="form-control"></vue-autonumeric>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-success" v-if="index !== 0" v-on:click="deletePayment(item)"><i class="fa fa-minus"></i>Hapus</button>
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

<!-- Large Modal Membership -->
<div class="modal" id="modal-membership" tabindex="-1" role="dialog" aria-labelledby="modal-membership" aria-hidden="true">
    <div class="modal-dialog modal-dialog-popin modal-lg" role="document">
        <div class="modal-content">
            <div class="block block-rounded block-transparent mb-0">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Pilih Membership</h3>
                    <div class="block-options">
                        <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fa fa-fw fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="block-content fs-sm">
                    <div class="row">
                        <div class="col-2 text-start">
                            <select v-on:change="getMemberships" v-model="membershipPagination.take" class="form-select">
                                <option>10</option>
                                <option>25</option>
                                <option>50</option>
                            </select>
                        </div>
                        <div class="col-10 text-end">
                            <input type="text" class="form-control" v-model="membershipPagination.search" v-on:input="getMemberships" placeholder="Cari Membership"/>
                        </div>
                    </div>
                    <div class="table-responsive w-100 mt-1">
                        <table class="table table-bordered table-striped table-vcenter js-dataTable-full" id="table_data_membership">
                            <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Harga</th>
                                <th>Opsi</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-if="membershipPagination.isLoading">
                                <td colspan="3" class="text-center">
                                    <div class="spinner-border text-info mt-2 mb-2">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </td>
                            </tr>
                            <tr v-for="(item, index) in membership">
                                <td>@{{ index+1 }}</td>
                                <td>@{{ item.code }}</td>
                                <td>@{{ item.price_str }}</td>
                                <td><button type="button" class="btn btn-success" v-on:click="showMemberDetilModal(item)"><i class="fa fa-plus"></i>Pilih</button></td>
                            </tr>
                            <tr>
                                <td colspan="3">Showing <span>@{{ membership.length }}</span> of @{{ membershipPagination.count }}</td>
                                <td class="text-center">
                                    <button type="button" v-on:click="previousMembershipPage"><i class="fa fa-chevron-left"></i></button>
                                    &nbsp;&nbsp;
                                    <button type="button" v-on:click="nextMembershipPage"><i class="fa fa-chevron-right"></i></button>
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
<!-- END Modal Membership -->

<!-- Member Data Modal -->
<div class="modal" id="modal-member-data" tabindex="-1" role="dialog" aria-labelledby="modal-member-data" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="block block-rounded block-transparent mb-0">
                <div class="block-header block-header-default" style="background: red; color: white;">
                    <h3 class="block-title">Detil Member</h3>
                    <div class="block-options">
                        <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fa fa-fw fa-times" style="color: white;"></i>
                        </button>
                    </div>
                </div>
                <div class="row mt-3 ms-3">
                    <div class="col-2 justify-content-start">
                        <label class="form-label">Nomor Member</label>
                    </div>
                    <div class="col-6">
                        <input type="text" v-model="memberData.number" class="form-control" placeholder="Wajib Diisi Nomor Member"/>
                    </div>
                </div>
                <div class="row mt-3 ms-3">
                    <div class="col-2 justify-content-start">
                        <label class="form-label">Nama</label>
                    </div>
                    <div class="col-6">
                        <input type="text" v-model="memberData.name" class="form-control" placeholder="Wajib Diisi Nama"/>
                    </div>
                </div>
                <div class="row mt-2 ms-3">
                    <div class="col-2 justify-content-start">
                        <label class="form-label">Telp</label>
                    </div>
                    <div class="col-6">
                        <input type="text" v-model="memberData.phone" class="form-control" placeholder="Opsional"/>
                    </div>
                </div>
                <div class="row mt-2 ms-3">
                    <div class="col-2 justify-content-start">
                        <label class="form-label">Email</label>
                    </div>
                    <div class="col-6">
                        <input type="text" v-model="memberData.email" class="form-control" placeholder="Opsional"/>
                    </div>
                </div>
                <div class="row mt-2 ms-3">
                    <div class="col-8 justify-content-start">
                        <button type="button" class="btn btn-primary" v-on:click="chooseMembership">Konfirmasi</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END Member Data Modal -->

<!-- Modal Delete Item -->
<div class="modal fade" id="modal-delete-item" tabindex="-1" role="dialog" aria-labelledby="modal-block-popin" aria-hidden="true">
    <div class="modal-dialog modal-dialog-popin" role="document">
        <div class="modal-content">
            <div class="block block-rounded block-transparent mb-0">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Hapus Item</h3>
                    <div class="block-options">
                        <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fa fa-fw fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="block-content fs-sm">
                    <p id="menu_description"></p>
                    <div class="col-12 alert alert-danger alert-dismissible" role="alert" v-if="deleteMsg !== ''">
                        <p class="mb-0">
                            @{{ deleteMsg }}
                        </p>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <label>Konfirmasi Password</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <label>Apakah yakin menghapus item ini?</label>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6">
                            <input type="password" v-model="deletePassword" class="form-control" placeholder="Konfirmasi Password"/>
                        </div>
                    </div>
                </div>
                <div class="block-content block-content-full text-end bg-body">
                    <button type="button" id="btn_delete_item_no" class="btn btn-sm btn-alt-secondary me-1" data-bs-dismiss="modal">No</button>
                    <button type="button" id="btn_delete_item_yes" class="btn btn-sm btn-primary" v-on:click="confirmDelete()">Yes</button>
                    <i v-if="deleteLoading" class="spinner-border spinner-border-sm text-green" role="status"></i>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END Modal Delete Item -->

<!-- Modal Enable FOC -->
<div class="modal fade" id="modal-enable-foc" tabindex="-1" role="dialog" aria-labelledby="modal-block-popin" aria-hidden="true">
    <div class="modal-dialog modal-dialog-popin" role="document">
        <div class="modal-content">
            <div class="block block-rounded block-transparent mb-0">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Enable FOC</h3>
                    <div class="block-options">
                        <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fa fa-fw fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="block-content fs-sm">
                    <p id="menu_description"></p>
                    <div class="col-12 alert alert-danger alert-dismissible" role="alert" v-if="passwordCheck.apiMsg !== ''">
                        <p class="mb-0">
                            @{{ passwordCheck.apiMsg }}
                        </p>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
                            <input type="password" v-model="passwordCheck.password" class="form-control" placeholder="Konfirmasi Password"/>
                        </div>
                    </div>
                </div>
                <div class="block-content block-content-full text-end bg-body">
                    <button type="button" id="btn_enable_foc_cancel" class="btn btn-sm btn-alt-secondary me-1" data-bs-dismiss="modal">No</button>
                    <button type="button" id="btn_enable_foc_confirm" class="btn btn-sm btn-primary" v-on:click="confirmEnableFoc()">Yes</button>
                    <i v-if="passwordCheck.isLoading" class="spinner-border spinner-border-sm text-green" role="status"></i>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END Modal Enable FOC -->

<!-- Modal Enable FOC -->
<div class="modal fade" id="modal-enable-discount" tabindex="-1" role="dialog" aria-labelledby="modal-block-popin" aria-hidden="true">
    <div class="modal-dialog modal-dialog-popin" role="document">
        <div class="modal-content">
            <div class="block block-rounded block-transparent mb-0">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Enable Discount</h3>
                    <div class="block-options">
                        <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fa fa-fw fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="block-content fs-sm">
                    <p id="menu_description"></p>
                    <div class="col-12 alert alert-danger alert-dismissible" role="alert" v-if="passwordCheck.apiMsg !== ''">
                        <p class="mb-0">
                            @{{ passwordCheck.apiMsg }}
                        </p>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <label>Konfirmasi Password</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <label>Apakah yakin enable Discount?</label>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6">
                            <input type="password" v-model="passwordCheck.password" class="form-control" placeholder="Konfirmasi Password"/>
                        </div>
                    </div>
                </div>
                <div class="block-content block-content-full text-end bg-body">
                    <button type="button" id="btn_enable_discount_cancel" class="btn btn-sm btn-alt-secondary me-1" data-bs-dismiss="modal">No</button>
                    <button type="button" id="btn_enable_discount_confirm" class="btn btn-sm btn-primary" v-on:click="confirmEnableDiscount()">Yes</button>
                    <i v-if="passwordCheck.isLoading" class="spinner-border spinner-border-sm text-green" role="status"></i>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END Modal Enable FOC -->
