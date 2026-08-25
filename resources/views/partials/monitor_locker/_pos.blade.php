<!-- POS Modal -->
{{--<div class="modal" id="modal-pos" tabindex="-1" role="dialog" aria-labelledby="modal-pos" aria-hidden="true">--}}
{{--    <div class="modal-dialog modal-dialog-popin modal-xl" role="document">--}}
{{--        <div class="modal-content">--}}
{{--            <div class="block block-rounded block-transparent mb-0">--}}
{{--                <div class="block-header block-header-default">--}}
{{--                    <h3 class="block-title">Transaksi</h3>--}}
{{--                    <div class="block-options">--}}
{{--                        <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">--}}
{{--                            <i class="fa fa-fw fa-times"></i>--}}
{{--                        </button>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="block-content fs-sm">--}}
{{--                    <div class="row">--}}
{{--                        <div class="col-2">--}}
{{--                            <span>Nomor Locker</span>--}}
{{--                        </div>--}}
{{--                        <div class="col-10">--}}
{{--                            <span v-if="activeLocker !== null">@{{ activeLocker.number }}</span>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="row">--}}
{{--                        <div class="col-2">--}}
{{--                            <span>Therapis</span>--}}
{{--                        </div>--}}
{{--                        <div class="col-10">--}}
{{--                            <span v-for="partner in partners">@{{ partner }}<span v-if="partners.length > 1">,</span></span>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="row" v-if="memberSearch.loading">--}}
{{--                        <div class="col-12 mt-2 mb-2 justify-content-center">--}}
{{--                            <div class="spinner-border text-info">--}}
{{--                                <span class="visually-hidden">Loading...</span>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="row" v-else>--}}
{{--                        <div class="col-12 alert alert-success alert-dismissible" role="alert" v-if="!memberSearch.notFound">--}}
{{--                            @{{ memberSearch.name }} Ditemukan!--}}
{{--                        </div>--}}
{{--                        <div class="col-12 alert alert-danger alert-dismissible" role="alert" v-if="memberSearch.message !== ''">--}}
{{--                            <p class="mb-0">--}}
{{--                                @{{ memberSearch.message }}--}}
{{--                            </p>--}}
{{--                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="row">--}}
{{--                        <div class="col-12 alert alert-danger alert-dismissible" role="alert" v-if="posMsg !== ''">--}}
{{--                            <p class="mb-0">--}}
{{--                                @{{ posMsg }}--}}
{{--                            </p>--}}
{{--                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="row">--}}
{{--                        <div class="col-2">--}}
{{--                            <span>Member?</span>--}}
{{--                        </div>--}}
{{--                        <div class="col-6">--}}
{{--                            <input type="text" v-model="memberSearch.name" class="form-control"/>--}}
{{--                        </div>--}}
{{--                        <div class="col-4">--}}
{{--                            <button type="button" v-on:click="checkMember" class="btn btn-info">Check</button>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="row mt-2 mb-2">--}}
{{--                        <div class="col-2">--}}
{{--                            <span>FOC?</span>--}}
{{--                        </div>--}}
{{--                        <div class="col-6">--}}
{{--                            <select class="form-select" v-model="foc" v-on:change="calculateTransaction">--}}
{{--                                <option value="NO" selected>Tidak FOC</option>--}}
{{--                                <option value="FOC">FOC</option>--}}
{{--                                <option value="FOC_PACKAGE">FOC Paket</option>--}}
{{--                                <option value="FOC_ITEM">FOC Item</option>--}}
{{--                            </select>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="row mt-2" v-if="foc !== 'NO'">--}}
{{--                        <div class="col-2">--}}
{{--                            <span>Nama FOC</span>--}}
{{--                        </div>--}}
{{--                        <div class="col-6">--}}
{{--                            <input type="text" v-model="focNumber" class="form-control" placeholder="Nama FOC"/>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="row">--}}
{{--                        <div class="col-6 text-start">--}}
{{--                            <button type="button" class="btn btn-success" v-on:click="showItemModal"><i class="fa fa-plus"></i> Tambah Item</button>--}}
{{--                        </div>--}}
{{--                        <div class="col-6 text-end">--}}
{{--                            <button type="button" class="btn btn-primary" v-on:click="showModalPayment"><i class="fa fa-money-bill"></i> Payment</button>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="row mt-2">--}}
{{--                        <div class="col-6 text-start">--}}
{{--                            <button type="button" class="btn btn-info" v-on:click="showMembershipModal"><i class="fa fa-plus"></i> Beli Membership</button>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="row mt-2" v-if="isHappyHour">--}}
{{--                        <div class="col-6">--}}
{{--                            <span style="color: indianred; font-size: 20px;">Happy Hour!</span>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="table-responsive w-100 mt-1">--}}
{{--                        <table class="table table-bordered table-striped table-vcenter js-dataTable-full" id="table_data_transaction">--}}
{{--                            <thead>--}}
{{--                            <tr>--}}
{{--                                <th>No</th>--}}
{{--                                <th>Nama Item</th>--}}
{{--                                <th width="20%">Qty</th>--}}
{{--                                <th>Harga</th>--}}
{{--                                <th>Diskon (%)</th>--}}
{{--                                <th>Diskon</th>--}}
{{--                                <th>Subtotal</th>--}}
{{--                                <th>Opsi</th>--}}
{{--                            </tr>--}}
{{--                            </thead>--}}
{{--                            <tbody>--}}
{{--                            <tr v-for="(item, index) in details">--}}
{{--                                <td>@{{ index+1 }}</td>--}}
{{--                                <td>--}}
{{--                                    <span v-if="item.type === 'package'">@{{ item.package_name }}</span>--}}
{{--                                    <span v-if="item.type === 'item'">@{{ item.item_name }}</span>--}}
{{--                                    <span v-if="item.is_facility_only">Hanya Fasilitas</span>--}}
{{--                                    <span v-if="item.type === 'membership'">--}}
{{--                                        @{{ item.membership_name }}--}}
{{--                                        <br/>--}}
{{--                                        Data Member<br/>--}}
{{--                                        Nama: @{{ memberData.name }}<br/>--}}
{{--                                        Email: <span v-if="memberData.email !== ''">@{{ memberData.email }}</span>--}}
{{--                                            <span v-else>-</span>--}}
{{--                                        <br/>--}}
{{--                                        Phone: <span v-if="memberData.phone !== ''">@{{ memberData.phone }}</span>--}}
{{--                                            <span v-else>-</span>--}}
{{--                                        <br/>--}}
{{--                                    </span>--}}
{{--                                </td>--}}
{{--                                <td>--}}
{{--                                    <input type="number" class="form-control text-end" v-if="item.type === 'package' || item.type === 'membership'" v-model="item.qty" readonly/>--}}
{{--                                    <input type="number" class="form-control text-end" v-if="item.type === 'item' && item.is_cut == 0" v-model="item.qty" v-on:input="calculateSubTotal(item)"/>--}}
{{--                                    <input type="number" class="form-control text-end" v-else-if="item.type === 'item' && item.is_cut == 1" v-model="item.qty" readonly/>--}}
{{--                                    <input type="number" class="form-control text-end" v-if="item.is_facility_only" value="1" readonly/>--}}
{{--                                </td>--}}
{{--                                <td class="text-end">@{{ item.price_str }}</td>--}}
{{--                                <td class="text-end">--}}
{{--                                    <input type="number" class="form-control text-end" v-model="item.discount_percentage" v-if="item.package_name == 'Penambahan Therapis' || item.package_name == 'Penambahan Customer'" readonly/>--}}
{{--                                    <input type="number" class="form-control text-end" v-model="item.discount_percentage" v-on:input="calculateDiscount(item)" v-else-if="item.type !== 'item'"/>--}}
{{--                                    <input type="number" class="form-control text-end" v-model="item.discount_percentage" v-else readonly/>--}}
{{--                                </td>--}}
{{--                                <td class="text-end">@{{ item.discount_str }}</td>--}}
{{--                                <td><input type="text" class="form-control text-end" v-model="item.subtotal_str" readonly/></td>--}}
{{--                                <td>--}}
{{--                                    <button type="button" class="btn btn-primary" v-if="item.type === 'package'" v-on:click="showPackageModal()"><i class="fa fa-box-tissue"></i> Ubah Paket</button>--}}
{{--                                    <button type="button" class="btn btn-danger" v-if="item.type === 'item'" v-on:click="deleteItemFromTrx(item)"><i class="fa fa-minus"></i> Hapus</button>--}}
{{--                                </td>--}}
{{--                            </tr>--}}
{{--                            <tr>--}}
{{--                                <td colspan="7" class="text-end"><b>Total</b></td>--}}
{{--                                <td class="text-end"><b>@{{ grandTotalStr }}</b></td>--}}
{{--                                <td></td>--}}
{{--                            </tr>--}}
{{--                            </tbody>--}}
{{--                        </table>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--</div>--}}
{{--<!-- END POS Modal -->--}}

{{--<!-- Item Modal -->--}}
{{--<div class="modal" id="modal-item" tabindex="-1" role="dialog" aria-labelledby="modal-item" aria-hidden="true">--}}
{{--    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">--}}
{{--        <div class="modal-content">--}}
{{--            <div class="block block-rounded block-transparent mb-0">--}}
{{--                <div class="block-header block-header-default" style="background: red; color: white;">--}}
{{--                    <h3 class="block-title">Tambah Item</h3>--}}
{{--                    <div class="block-options">--}}
{{--                        <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">--}}
{{--                            <i class="fa fa-fw fa-times" style="color: white;"></i>--}}
{{--                        </button>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="block-content fs-sm">--}}
{{--                    <div class="row">--}}
{{--                        <div class="col-12 justify-content-end">--}}
{{--                            <button type="button" class="btn btn-success" v-on:click="addItemsToTrx"><i class="fa fa-plus"></i>Pilih Item</button>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="row mt-2">--}}
{{--                        <div class="col-2 text-start">--}}
{{--                            <select v-on:change="generateItemDisplay" v-model="itemPagination.take" class="form-select">--}}
{{--                                <option>10</option>--}}
{{--                                <option>25</option>--}}
{{--                                <option>50</option>--}}
{{--                            </select>--}}
{{--                        </div>--}}
{{--                        <div class="col-10 text-end">--}}
{{--                            <input type="text" class="form-control" v-model="itemPagination.search" v-on:input="searchItem" placeholder="Cari Item"/>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="table-responsive w-100 mt-1">--}}
{{--                        <table class="table table-bordered table-striped table-vcenter js-dataTable-full" id="table_data_item">--}}
{{--                            <thead>--}}
{{--                            <tr>--}}
{{--                                <th>Check</th>--}}
{{--                                <th>Nama Item</th>--}}
{{--                                <th>Harga</th>--}}
{{--                                <th>Tipe</th>--}}
{{--                            </tr>--}}
{{--                            </thead>--}}
{{--                            <tbody>--}}
{{--                            <tr v-if="packagePagination.isLoading">--}}
{{--                                <td colspan="4" class="text-center">--}}
{{--                                    <div class="spinner-border text-info mt-2 mb-2">--}}
{{--                                        <span class="visually-hidden">Loading...</span>--}}
{{--                                    </div>--}}
{{--                                </td>--}}
{{--                            </tr>--}}
{{--                            <tr v-for="(item, index) in itemDisplays">--}}
{{--                                <td><input type="checkbox" v-model="item.selected"/></td>--}}
{{--                                <td>@{{ item.name }}</td>--}}
{{--                                <td>@{{ item.price_str }}</td>--}}
{{--                                <td>@{{ item.category }}</td>--}}
{{--                            </tr>--}}
{{--                            <tr>--}}
{{--                                <td colspan="3">Showing <span>@{{ itemDisplays.length }}</span> of @{{ items.length }}--}}
{{--                                <td class="text-center">--}}
{{--                                    <button type="button" v-on:click="previousItemPage"><i class="fa fa-chevron-left"></i></button>--}}
{{--                                    &nbsp;&nbsp;--}}
{{--                                    <button type="button" v-on:click="nextItemPage"><i class="fa fa-chevron-right"></i></button>--}}
{{--                                </td>--}}
{{--                            </tr>--}}
{{--                            </tbody>--}}
{{--                        </table>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--</div>--}}
{{--<!-- Item Modal New -->--}}
{{--<div class="modal" id="modal-item-qty" tabindex="-1" role="dialog" aria-labelledby="modal-item" aria-hidden="true">--}}
{{--    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">--}}
{{--        <div class="modal-content">--}}
{{--            <div class="block block-rounded block-transparent mb-0">--}}
{{--                <div class="block-header block-header-default" style="background: red; color: white;">--}}
{{--                    <h3 class="block-title">Tambah Item</h3>--}}
{{--                    <div class="block-options">--}}
{{--                        <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">--}}
{{--                            <i class="fa fa-fw fa-times" style="color: white;"></i>--}}
{{--                        </button>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="block-content fs-sm">--}}
{{--                    <div class="row">--}}
{{--                        <div class="col-12 justify-content-end">--}}
{{--                            <button type="button" class="btn btn-success" v-on:click="addItemsToTrxSubmit"><i class="fa fa-plus"></i>Pilih Item</button>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="row mt-2">--}}
{{--                        <div class="col-2 text-start">--}}
{{--                            <select v-on:change="getItems" v-model="itemPagination.take" class="form-select">--}}
{{--                                <option>10</option>--}}
{{--                                <option>25</option>--}}
{{--                                <option>50</option>--}}
{{--                            </select>--}}
{{--                        </div>--}}
{{--                        <div class="col-10 text-end">--}}
{{--                            <input type="text" class="form-control" v-model="itemPagination.search" v-on:input="getItems" placeholder="Cari Item"/>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="row my-2">--}}
{{--                        <div class="col-md-3" v-for="(item, index) in items" v-if="items.length > 0">--}}
{{--                            <div class="row">--}}
{{--                                <div class="col-12 text-center">--}}
{{--                                    <img :src="item.img_path" class="text-center" width="100%" v-on:click="selectItem(item)" v-if="item.selected" style="border: 5px solid deepskyblue;"/>--}}
{{--                                    <img :src="item.img_path" class="text-center" width="100%" v-on:click="selectItem(item)" v-else/>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="row my-2">--}}
{{--                                <div class="col-12 text-center">--}}
{{--                                    <span style="font-size: 10px;">@{{ item.name }} / Rp@{{ item.price_str }}</span>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="row my-2">--}}
{{--                                <div class="col-4 text-start">--}}
{{--                                    <button class="btn btn-danger" v-on:click="minusItemQty(item)"><i class="fa fa-minus"></i></button>--}}
{{--                                </div>--}}
{{--                                <div class="col-4 text-center">--}}
{{--                                    <span v-if="!item.selected" style="color: darkgrey">@{{ item.qty }}</span>--}}
{{--                                    <span v-else>@{{ item.qty }}</span>--}}
{{--                                </div>--}}
{{--                                <div class="col-4 text-end">--}}
{{--                                    <button class="btn btn-success" v-on:click="plusItemQty(item)"><i class="fa fa-plus"></i></button>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="row my-2">--}}
{{--                        <div class="col-6 text-start">--}}
{{--                            Showing <span>@{{ items.length }}</span> of @{{ itemPagination.count }}--}}
{{--                        </div>--}}
{{--                        <div class="col-6 text-end">--}}
{{--                            <button type="button" v-on:click="previousItemPage"><i class="fa fa-chevron-left"></i></button>--}}
{{--                            &nbsp;&nbsp;--}}
{{--                            <button type="button" v-on:click="nextItemPage"><i class="fa fa-chevron-right"></i></button>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--</div>--}}
{{--<div class="modal" id="modal-item-qty-old" tabindex="-1" role="dialog" aria-labelledby="modal-item-qty" aria-hidden="true">--}}
{{--    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">--}}
{{--        <div class="modal-content">--}}
{{--            <div class="block block-rounded block-transparent mb-0">--}}
{{--                <div class="block-header block-header-default" style="background: red; color: white;">--}}
{{--                    <h3 class="block-title">Tambah Item</h3>--}}
{{--                    <div class="block-options">--}}
{{--                        <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">--}}
{{--                            <i class="fa fa-fw fa-times" style="color: white;"></i>--}}
{{--                        </button>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="block-content fs-sm">--}}
{{--                    <div class="row">--}}
{{--                        <div class="col-6 text-start" v-if="!itemLoading">--}}
{{--                            <button type="button" class="btn btn-success" v-on:click="addItemsToTrxSubmit"><i class="fa fa-plus"></i>Pilih Item</button>--}}
{{--                        </div>--}}
{{--                        <div class="col-6 spinner-border text-primary" v-if="itemLoading">--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="row mt-2">--}}
{{--                        <div class="col-2 text-start">--}}
{{--                            <select v-on:change="generateItemDisplay" v-model="itemPagination.take" class="form-select">--}}
{{--                                <option>10</option>--}}
{{--                                <option>25</option>--}}
{{--                                <option>50</option>--}}
{{--                            </select>--}}
{{--                        </div>--}}
{{--                        <div class="col-10 text-end">--}}
{{--                            <input type="text" class="form-control" v-model="itemPagination.search" v-on:input="searchItem" placeholder="Cari Item"/>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="row mt-2 ms-1 me-1" v-if="addItemMsg !== ''">--}}
{{--                        <div class="col-12 alert alert-danger alert-dismissible" role="alert">--}}
{{--                            <p class="mb-0">--}}
{{--                                @{{ addItemMsg }}--}}
{{--                            </p>--}}
{{--                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="row" v-if="itemPagination.isLoading">--}}
{{--                        <div class="col-12 spinner-border text-primary">--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="table-responsive w-100 mt-1">--}}
{{--                        <table class="table table-bordered table-striped table-vcenter js-dataTable-full" id="table_data_item">--}}
{{--                            <thead>--}}
{{--                            <tr>--}}
{{--                                <th>Check</th>--}}
{{--                                <th>Nama Item</th>--}}
{{--                                <th>Harga</th>--}}
{{--                                <th>Stock</th>--}}
{{--                                <th>Qty</th>--}}
{{--                                <th>Tipe</th>--}}
{{--                            </tr>--}}
{{--                            </thead>--}}
{{--                            <tbody>--}}
{{--                            <tr v-if="packagePagination.isLoading">--}}
{{--                                <td colspan="5" class="text-center">--}}
{{--                                    <div class="spinner-border text-info mt-2 mb-2">--}}
{{--                                        <span class="visually-hidden">Loading...</span>--}}
{{--                                    </div>--}}
{{--                                </td>--}}
{{--                            </tr>--}}
{{--                            <tr v-for="(item, index) in itemDisplays">--}}
{{--                                <td>--}}
{{--                                    <input type="checkbox" v-model="item.selected" v-if="item.stock === 0 && item.is_unlimited_stock === 0" disabled/>--}}
{{--                                    <input type="checkbox" v-model="item.selected" v-else/>--}}
{{--                                </td>--}}
{{--                                <td>@{{ item.name }}</td>--}}
{{--                                <td>@{{ item.price_str }}</td>--}}
{{--                                <td>@{{ item.stock }}</td>--}}
{{--                                <td>--}}
{{--                                    <input type="number" v-model="item.qty" class="form-control" v-on:input="checkOnStock(item)" v-if="item.stock === 0 && item.is_unlimited_stock === 0" readonly/>--}}
{{--                                    <input type="number" v-model="item.qty" class="form-control" v-on:input="checkOnStock(item)" v-else/>--}}
{{--                                </td>--}}
{{--                                <td>@{{ item.category }}</td>--}}
{{--                            </tr>--}}
{{--                            <tr>--}}
{{--                                <td colspan="4">Showing <span>@{{ itemDisplays.length + itemPagination.skip }}</span> of @{{ items.length }}--}}
{{--                                <td class="text-center">--}}
{{--                                    <button type="button" v-on:click="previousItemPage"><i class="fa fa-chevron-left"></i></button>--}}
{{--                                    &nbsp;&nbsp;--}}
{{--                                    <button type="button" v-on:click="nextItemPage"><i class="fa fa-chevron-right"></i></button>--}}
{{--                                </td>--}}
{{--                            </tr>--}}
{{--                            </tbody>--}}
{{--                        </table>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--</div>--}}

{{--<!-- END Item Modal -->--}}

{{--<!-- Treatment Modal -->--}}
{{--<div class="modal" id="modal-package" tabindex="-1" role="dialog" aria-labelledby="modal-package" aria-hidden="true">--}}
{{--    <div class="modal-dialog modal-dialog-centered" role="document">--}}
{{--        <div class="modal-content">--}}
{{--            <div class="block block-rounded block-transparent mb-0">--}}
{{--                <div class="block-header block-header-default" style="background: red; color: white;">--}}
{{--                    <h3 class="block-title">Ubah Paket</h3>--}}
{{--                    <div class="block-options">--}}
{{--                        <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">--}}
{{--                            <i class="fa fa-fw fa-times" style="color: white;"></i>--}}
{{--                        </button>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="block-content fs-sm">--}}
{{--                    <div class="table-responsive w-100 mt-1">--}}
{{--                        <div class="row" v-if="changePackageMsg !== ''">--}}
{{--                            <div class="col-12 alert alert-danger alert-dismissible" role="alert">--}}
{{--                                <p class="mb-0">--}}
{{--                                    @{{ changePackageMsg }}--}}
{{--                                </p>--}}
{{--                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                        <div class="row">--}}
{{--                            <div class="col-2">--}}
{{--                                Konfirmasi Password--}}
{{--                            </div>--}}
{{--                            <div class="col-6">--}}
{{--                                <input type="password" v-model="changePackagePassword" class="form-control"/>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                        <table class="table table-bordered table-striped table-vcenter js-dataTable-full" id="table_data_ubah_paket">--}}
{{--                            <thead>--}}
{{--                            <tr>--}}
{{--                                <th>No</th>--}}
{{--                                <th>Nama Paket</th>--}}
{{--                                <th>Harga</th>--}}
{{--                                <th>Opsi</th>--}}
{{--                            </tr>--}}
{{--                            </thead>--}}
{{--                            <tbody>--}}
{{--                            <tr v-for="(item, index) in packages">--}}
{{--                                <td>@{{ index+1 }}</td>--}}
{{--                                <td>@{{ item.name }}</td>--}}
{{--                                <td>@{{ item.price_str }}</td>--}}
{{--                                <td>--}}
{{--                                    <button type="button" class="btn btn-success" v-if="item.id !== packageId" v-on:click="changePackage(item)"><i class="fa fa-box-tissue"></i>Pilih Paket</button>--}}
{{--                                    <span v-else>Tidak bisa dipilih</span>--}}
{{--                                </td>--}}
{{--                            </tr>--}}
{{--                            </tbody>--}}
{{--                        </table>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--</div>--}}
{{--<!-- END Item Modal -->--}}

{{--<!-- Payment Modal -->--}}
{{--<div class="modal" id="modal-payment" tabindex="-1" role="dialog" aria-labelledby="modal-payment" aria-hidden="true">--}}
{{--    <div class="modal-dialog modal-dialog-centered" role="document">--}}
{{--        <div class="modal-content">--}}
{{--            <div class="block block-rounded block-transparent mb-0">--}}
{{--                <div class="block-header block-header-default" style="background: red; color: white;">--}}
{{--                    <h3 class="block-title">Pembayaran</h3>--}}
{{--                    <div class="block-options">--}}
{{--                        <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">--}}
{{--                            <i class="fa fa-fw fa-times" style="color: white;"></i>--}}
{{--                        </button>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="block-content fs-sm">--}}
{{--                    <div class="row">--}}
{{--                        <div class="col-12 justify-content-end">--}}
{{--                            <span style="font-size: 20px;">Total Transaksi: @{{ grandTotalStr }}</span>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="row mt-2 mb-2">--}}
{{--                        <div class="col-12 justify-content-end">--}}
{{--                            <span style="font-size: 16px;">Total Kembalian: @{{ changeTotalStr }}</span>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="row mb-2">--}}
{{--                        <div class="col-6 justify-content-start">--}}
{{--                            <button type="button" class="btn btn-primary" v-on:click="submitTransaction"><i class="fa fa-money"></i>Finish Pembayaran</button>--}}
{{--                        </div>--}}
{{--                        <div class="col-6 text-end" v-if="grandTotal !== 0">--}}
{{--                            <button type="button" class="btn btn-success" v-on:click="addMorePayment"><i class="fa fa-plus"></i>Tambah Pembayaran</button>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="table-responsive w-100 mt-1">--}}
{{--                        <table class="table table-bordered table-striped table-vcenter js-dataTable-full" id="table_data_ubah_paket">--}}
{{--                            <thead>--}}
{{--                            <tr>--}}
{{--                                <th>No</th>--}}
{{--                                <th>Tipe Payment</th>--}}
{{--                                <th>Jumlah</th>--}}
{{--                                <th>Opsi</th>--}}
{{--                            </tr>--}}
{{--                            </thead>--}}
{{--                            <tbody>--}}
{{--                            <tr v-for="(item, index) in payments">--}}
{{--                                <td>@{{ index+1 }}</td>--}}
{{--                                <td>--}}
{{--                                    <select class="form-select" v-model="item.payment_id">--}}
{{--                                        <option v-for="(payment, index) in payment_type" :value="payment.id">@{{ payment.description }}</option>--}}
{{--                                    </select>--}}
{{--                                </td>--}}
{{--                                <td>--}}
{{--                                    <vue-autonumeric :options="autonumericFormat" v-if="grandTotal == 0" readonly v-model="item.amount" class="form-control"></vue-autonumeric>--}}
{{--                                    <vue-autonumeric :options="autonumericFormat" v-else v-model="item.amount" class="form-control"></vue-autonumeric>--}}
{{--                                </td>--}}
{{--                                <td>--}}
{{--                                    <button type="button" class="btn btn-success" v-if="index !== 0" v-on:click="deletePayment(item)"><i class="fa fa-minus"></i>Hapus</button>--}}
{{--                                </td>--}}
{{--                            </tr>--}}
{{--                            </tbody>--}}
{{--                        </table>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--</div>--}}
{{--<!-- END Payment Modal -->--}}

{{--<!-- Large Modal Membership -->--}}
{{--<div class="modal" id="modal-membership" tabindex="-1" role="dialog" aria-labelledby="modal-membership" aria-hidden="true">--}}
{{--    <div class="modal-dialog modal-dialog-popin modal-lg" role="document">--}}
{{--        <div class="modal-content">--}}
{{--            <div class="block block-rounded block-transparent mb-0">--}}
{{--                <div class="block-header block-header-default">--}}
{{--                    <h3 class="block-title">Pilih Membership</h3>--}}
{{--                    <div class="block-options">--}}
{{--                        <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">--}}
{{--                            <i class="fa fa-fw fa-times"></i>--}}
{{--                        </button>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="block-content fs-sm">--}}
{{--                    <div class="row">--}}
{{--                        <div class="col-2 text-start">--}}
{{--                            <select v-on:change="getMemberships" v-model="membershipPagination.take" class="form-select">--}}
{{--                                <option>10</option>--}}
{{--                                <option>25</option>--}}
{{--                                <option>50</option>--}}
{{--                            </select>--}}
{{--                        </div>--}}
{{--                        <div class="col-10 text-end">--}}
{{--                            <input type="text" class="form-control" v-model="membershipPagination.search" v-on:input="getMemberships" placeholder="Cari Membership"/>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="table-responsive w-100 mt-1">--}}
{{--                        <table class="table table-bordered table-striped table-vcenter js-dataTable-full" id="table_data_membership">--}}
{{--                            <thead>--}}
{{--                            <tr>--}}
{{--                                <th>No</th>--}}
{{--                                <th>Nama</th>--}}
{{--                                <th>Harga</th>--}}
{{--                                <th>Opsi</th>--}}
{{--                            </tr>--}}
{{--                            </thead>--}}
{{--                            <tbody>--}}
{{--                            <tr v-if="groPagination.isLoading">--}}
{{--                                <td colspan="3" class="text-center">--}}
{{--                                    <div class="spinner-border text-info mt-2 mb-2">--}}
{{--                                        <span class="visually-hidden">Loading...</span>--}}
{{--                                    </div>--}}
{{--                                </td>--}}
{{--                            </tr>--}}
{{--                            <tr v-for="(item, index) in membership">--}}
{{--                                <td>@{{ index+1 }}</td>--}}
{{--                                <td>@{{ item.code }}</td>--}}
{{--                                <td>@{{ item.price_str }}</td>--}}
{{--                                <td><button type="button" class="btn btn-success" v-on:click="showMemberDetilModal(item)"><i class="fa fa-plus"></i>Pilih</button></td>--}}
{{--                            </tr>--}}
{{--                            <tr>--}}
{{--                                <td colspan="3">Showing <span>@{{ membership.length }}</span> of @{{ membershipPagination.count }}</td>--}}
{{--                                <td class="text-center">--}}
{{--                                    <button type="button" v-on:click="previousMembershipPage"><i class="fa fa-chevron-left"></i></button>--}}
{{--                                    &nbsp;&nbsp;--}}
{{--                                    <button type="button" v-on:click="nextMembershipPage"><i class="fa fa-chevron-right"></i></button>--}}
{{--                                </td>--}}
{{--                            </tr>--}}
{{--                            </tbody>--}}
{{--                        </table>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--</div>--}}
{{--<!-- END Modal Membership -->--}}

{{--<!-- Member Data Modal -->--}}
{{--<div class="modal" id="modal-member-data" tabindex="-1" role="dialog" aria-labelledby="modal-member-data" aria-hidden="true">--}}
{{--    <div class="modal-dialog modal-dialog-centered" role="document">--}}
{{--        <div class="modal-content">--}}
{{--            <div class="block block-rounded block-transparent mb-0">--}}
{{--                <div class="block-header block-header-default" style="background: red; color: white;">--}}
{{--                    <h3 class="block-title">Detil Member</h3>--}}
{{--                    <div class="block-options">--}}
{{--                        <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">--}}
{{--                            <i class="fa fa-fw fa-times" style="color: white;"></i>--}}
{{--                        </button>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="row mt-3 ms-3">--}}
{{--                    <div class="col-2 justify-content-start">--}}
{{--                        <label class="form-label">Nama</label>--}}
{{--                    </div>--}}
{{--                    <div class="col-6">--}}
{{--                        <input type="text" v-model="memberData.name" class="form-control" placeholder="Wajib Diisi"/>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="row mt-2 ms-3">--}}
{{--                    <div class="col-2 justify-content-start">--}}
{{--                        <label class="form-label">Telp</label>--}}
{{--                    </div>--}}
{{--                    <div class="col-6">--}}
{{--                        <input type="text" v-model="memberData.phone" class="form-control" placeholder="Opsional"/>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="row mt-2 ms-3">--}}
{{--                    <div class="col-2 justify-content-start">--}}
{{--                        <label class="form-label">Email</label>--}}
{{--                    </div>--}}
{{--                    <div class="col-6">--}}
{{--                        <input type="text" v-model="memberData.email" class="form-control" placeholder="Opsional"/>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="row mt-2 ms-3">--}}
{{--                    <div class="col-8 justify-content-start">--}}
{{--                        <button type="button" class="btn btn-primary" v-on:click="chooseMembership">Konfirmasi</button>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--</div>--}}
<!-- END Member Data Modal -->
