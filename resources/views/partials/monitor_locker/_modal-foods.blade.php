<!-- Item Modal New -->
<div class="modal" id="modal-item-qty" tabindex="-1" role="dialog" aria-labelledby="modal-item" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="block block-rounded block-transparent mb-0">
                <div class="block-header block-header-default" style="background: red; color: white;">
                    <h3 class="block-title">Tambah Item</h3>
                    <div class="block-options">
                        <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fa fa-fw fa-times" style="color: white;"></i>
                        </button>
                    </div>
                </div>
                <div class="block-content fs-sm">
                    <div class="row">
                        <div class="col-2 justify-content-end">
                            <span></span>
                        </div>
                        <div class="col-10 justify-content-start">
                            <span style="color: red;">*Hanya diisi untuk loker NCI*</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-2 justify-content-end">
                            <button type="button" class="btn btn-success" v-on:click="addItemsToTrxSubmit"><i class="fa fa-plus"></i>Pilih Item</button>
                        </div>
                        <div class="col-10 justify-content-start">
                            <input id="personNumber" type="text" class="form-control" v-model="personNumber" placeholder="Nomor Therapis"/>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-2 text-start">
                            <select v-on:change="generateItemDisplay" v-model="itemPagination.take" class="form-select">
                                <option>10</option>
                                <option>25</option>
                                <option>50</option>
                            </select>
                        </div>
                        <div class="col-10 text-end">
                            <input id="food-search" type="text" class="form-control" v-model="itemPagination.search" v-on:input="searchItem" placeholder="Cari Item"/>
                        </div>
                    </div>
                    <div class="row my-2">
                        <div class="col-md-3" v-for="(item, index) in itemDisplays" v-if="items.length > 0">
                            <div class="row">
                                <div class="col-12 text-center">
                                    <img :src="item.img_path" class="text-center" width="100%" v-on:click="selectItem(item)" v-if="item.selected" style="border: 5px solid deepskyblue;"/>
                                    <img :src="item.img_path" class="text-center" width="100%" v-on:click="selectItem(item)" v-else/>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-12 text-center">
                                    <span style="font-size: 12px; font-weight: bold;">@{{ item.name }} / Rp@{{ item.price_str }}</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 text-center">
                                    <span style="font-size: 12px; font-weight: bold;">Stock: @{{ item.stock }}</span>
                                </div>
                            </div>
                            <div class="row my-2">
                                <div class="col-4 text-start">
                                    <button class="btn btn-danger" v-on:click="minusItemQty(item)"><i class="fa fa-minus"></i></button>
                                </div>
                                <div class="col-4 text-center">
                                    <span v-if="!item.selected" style="color: darkgrey">@{{ item.qty }}</span>
                                    <span v-else>@{{ item.qty }}</span>
                                </div>
                                <div class="col-4 text-end">
                                    <button class="btn btn-success" v-on:click="plusItemQty(item)"><i class="fa fa-plus"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row my-2">
                        <div class="col-6 text-start">
                            Showing <span>@{{ itemDisplays.length + itemPagination.skip }}</span> of @{{ items.length }}
                        </div>
                        <div class="col-6 text-end">
                            <button type="button" v-on:click="previousItemPage"><i class="fa fa-chevron-left"></i></button>
                            &nbsp;&nbsp;
                            <button type="button" v-on:click="nextItemPage"><i class="fa fa-chevron-right"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Detil Modal -->
<div class="modal" id="modal-detil" tabindex="-1" role="dialog" aria-labelledby="modal-detil" aria-hidden="true">
    <div class="modal-dialog modal-dialog-popin modal-xl" role="document">
        <div class="modal-content">
            <div class="block block-rounded block-transparent mb-0">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Detil</h3>
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
                            <span>Nomor Room</span>
                        </div>
                        <div class="col-10">
                            <span v-if="activeLocker !== null">@{{ activeLocker.room_number }}</span>
                        </div>
                    </div>
                    <div class="table-responsive w-100 mt-1">
                        <table class="table table-bordered table-striped table-vcenter js-dataTable-full" id="table_data_transaction">
                            <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Item</th>
                                <th width="20%">Qty</th>
                                <th>Status</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(item, index) in foods">
                                <td>@{{ index+1 }}</td>
                                <td>
                                    <span>@{{ item.name }}</span>
                                </td>
                                <td>
                                    <input type="number" class="form-control text-end" v-model="item.qty" readonly/>
                                </td>
                                <td><input type="text" class="form-control text-end" v-model="item.status" readonly/></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END Detil Modal -->
