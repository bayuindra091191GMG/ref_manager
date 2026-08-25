<!-- Large Block Modal -->
<div class="modal" id="modal-switch-room" tabindex="-1" role="dialog" aria-labelledby="modal-block-large" aria-hidden="true">
    <div class="modal-dialog modal-dialog-popin modal-lg" role="document">
        <div class="modal-content">
            <div class="block block-rounded block-transparent mb-0">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Pindah Room</h3>
                    <div class="block-options">
                        <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fa fa-fw fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="block-content fs-sm">
                    <div class="row">
                        <div class="col-2 text-start">
                            <select v-on:change="getRooms" v-model="roomPagination.take" class="form-select">
                                <option>10</option>
                                <option>25</option>
                                <option>50</option>
                            </select>
                        </div>
                        <div class="col-10 text-end">
                            <input type="text" class="form-control" v-model="roomPagination.search" v-on:input="getRooms" placeholder="Cari Room"/>
                        </div>
                    </div>
                    <div class="table-responsive w-100 mt-1">
                        <table class="table table-bordered table-striped table-vcenter js-dataTable-full" id="table_data_room">
                            <thead>
                            <tr>
                                <th>No</th>
                                <th>Nomor Room</th>
                                <th>Nama Room</th>
                                <th>Tipe Room</th>
                                <th>Status Room</th>
                                <th>Opsi</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-if="roomPagination.isLoading">
                                <td colspan="6" class="text-center">
                                    <div class="spinner-border text-info mt-2 mb-2">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </td>
                            </tr>
                            <tr v-for="(item, index) in rooms">
                                <td>@{{ index+1 }}</td>
                                <td>@{{ item.number }}</td>
                                <td>@{{ item.name }}</td>
                                <td>@{{ item.type }}</td>
                                <td>
                                    <span style="color: pink;" v-if="item.status === 'Cleaning'">@{{ item.status }}</span>
                                    <span style="color: lightgreen;" v-if="item.status === 'Available'">@{{ item.status }}</span>
                                    <span style="color: red;" v-if="item.status === 'Occupied'">@{{ item.status }}</span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-success" v-if="item.status === 'Available'" v-on:click="showModalConfirmationSwitchRoom(item)">Pilih</button>
                                    <span v-else>Tidak bisa dipilih</span>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4">Showing <span>@{{ rooms.length }}</span> of @{{ roomPagination.count }}</td>
                                <td colspan="2" class="text-center">
                                    <button type="button" v-on:click="previousRoomPage"><i class="fa fa-chevron-left"></i></button>
                                    &nbsp;&nbsp;
                                    <button type="button" v-on:click="nextRoomPage"><i class="fa fa-chevron-right"></i></button>
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
<!-- END Large Block Modal -->

<!-- Modal Confirmation switch Room -->
<div class="modal fade" id="modal-switch-room-confirmation" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-slideup">
        <div class="modal-content">
            <div class="block-content">
                <div class="row">
                    <div class="col-12 text-center">
                        <span>Apakah Anda yakin untuk pindah ke room?</span>
                    </div>
                </div>
            </div>
            <div class="block-content block-content-full text-center bg-body">
                <button type="button" id="btn_cancel_switch_room" class="btn btn-warning" data-bs-dismiss="modal">Tidak</button>
                <button type="button" id="btn_submit_switch_room" class="btn btn-success" v-on:click="switchRoom()">Ya</button>
                <button type="button" id="btn_loading_switch_room" class="btn btn-primary" style="display: none;">
                    <i class="spinner-border spinner-border-sm text-green" role="status">
                        <span class="visually-hidden">Mohon tunggu...</span>
                    </i>
                </button>
            </div>
        </div>
    </div>
</div>
<!-- END Modal Confirmation switch Room -->

<!-- Partner Modal -->
<div class="modal" id="modal-switch-partner" tabindex="-1" role="dialog" aria-labelledby="modal-block-large" aria-hidden="true">
    <div class="modal-dialog modal-dialog-popin modal-lg" role="document">
        <div class="modal-content">
            <div class="block block-rounded block-transparent mb-0">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Ubah Terapis</h3>
                    <div class="block-options">
                        <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fa fa-fw fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="block-content fs-sm">
                    <div class="row">
                        <div class="col-2 text-start">
                            <select v-on:change="getPartners" v-model="partnerPagination.take" class="form-select">
                                <option>10</option>
                                <option>25</option>
                                <option>50</option>
                            </select>
                        </div>
                        <div class="col-10 text-end">
                            <input type="text" class="form-control" v-model="partnerPagination.search" v-on:input="getPartners" placeholder="Cari Terapis"/>
                        </div>
                    </div>
                    <div class="table-responsive w-100 mt-1">
                        <table class="table table-bordered table-striped table-vcenter" id="table_data_partner">
                            <thead>
                            <tr>
                                <th>No</th>
                                <th>Nomor Terapis</th>
                                <th>Nama Terapis</th>
                                <th>Status Room</th>
                                <th>Opsi</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-if="partnerPagination.isLoading">
                                <td colspan="6" class="text-center">
                                    <div class="spinner-border text-info mt-2 mb-2">
                                        <span class="visually-hidden">Mohon tunggu...</span>
                                    </div>
                                </td>
                            </tr>
                            <tr v-for="(item, index) in partners">
                                <td>@{{ index+1 }}</td>
                                <td>@{{ item.number }}</td>
                                <td>@{{ item.alias }}</td>
                                <td>
                                    <span style="color: lightgreen;" v-if="item.status === 'Available'">@{{ item.status }}</span>
                                    <span style="color: red;" v-if="item.status === 'Occupied'">@{{ item.status }}</span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-success" v-if="item.status === 'Available'" v-on:click="showModalConfirmationSwitchPartner(item)">Pilih</button>
                                    <span v-else>Tidak bisa dipilih</span>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4">Showing <span>@{{ partners.length }}</span> of @{{ partnerPagination.count }}</td>
                                <td colspan="2" class="text-center">
                                    <button type="button" v-on:click="previousPartnerPage"><i class="fa fa-chevron-left"></i></button>
                                    &nbsp;&nbsp;
                                    <button type="button" v-on:click="nextPartnerPage"><i class="fa fa-chevron-right"></i></button>
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
<!-- END Large Block Modal -->

<!-- Modal Confirmation switch Room -->
<div class="modal fade" id="modal-switch-partner-confirmation" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-slideup">
        <div class="modal-content">
            <div class="block-content">
                <div class="row">
                    <div class="col-12 text-center">
                        <span>Apakah Anda yakin untuk ubah ke Terapis ini?</span>
                    </div>
                </div>
            </div>
            <div class="block-content block-content-full text-center bg-body">
                <button type="button" id="btn_cancel_switch_partner" class="btn btn-warning" data-bs-dismiss="modal">Tidak</button>
                <button type="button" id="btn_submit_switch_partner" class="btn btn-success" v-on:click="switchPartner()">Ya</button>
                <button type="button" id="btn_loading_switch_partner" class="btn btn-primary" style="display: none;">
                    <i class="spinner-border spinner-border-sm text-green" role="status">
                        <span class="visually-hidden">Mohon tunggu...</span>
                    </i>
                </button>
            </div>
        </div>
    </div>
</div>
<!-- END Modal Confirmation switch Room -->

