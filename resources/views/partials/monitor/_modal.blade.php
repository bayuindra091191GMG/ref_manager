<!-- Modal Confirmation Set Available Partner -->
<div class="modal" id="modal-set-available-partner" role="dialog" aria-labelledby="modal-set-available-partner" aria-hidden="true">
    <div class="modal-dialog modal-dialog-popin modal-lg shadow-lg" role="document">
        <div class="modal-content shadow-lg">
            <div class="block block-rounded block-transparent mb-0">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Set Partner Available</h3>
                    <div class="block-options">
                        <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close" @click="clearData()">
                            <i class="fa fa-fw fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="block-content fs-sm space-y-3" v-if="activePartner">
                    <div class="row">
                        <div class="col-sm-12">
                            <span>
                                Apakah anda yakin ingin set available terapis <strong>@{{ activePartner.name }}</strong>?
                            </span>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close" @click="clearData()">No</button>
                        <button type="button" class="btn btn-success ms-2" @click="setPartnerAvailable(activePartner.id)">Yes</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmation Set Available Room -->
<div class="modal" id="modal-set-available-room" role="dialog" aria-labelledby="modal-set-available-room" aria-hidden="true">
    <div class="modal-dialog modal-dialog-popin modal-lg shadow-lg" role="document">
        <div class="modal-content shadow-lg">
            <div class="block block-rounded block-transparent mb-0">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Set Room Available</h3>
                    <div class="block-options">
                        <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close" @click="clearData()">
                            <i class="fa fa-fw fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="block-content fs-sm space-y-3" v-if="activeRoom">
                    <div class="row">
                        <div class="col-sm-12">
                            <span>
                                Apakah anda yakin ingin set available ruangan <strong>@{{ activeRoom.number }}</strong>?
                            </span>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close" @click="clearData()">No</button>
                        <button type="button" class="btn btn-success ms-2" @click="setRoomAvailable(activeRoom.id)">Yes</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
