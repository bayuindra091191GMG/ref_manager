<!-- Modal booking! -->
<div class="modal" id="modal-booking" role="dialog" aria-labelledby="modal-booking" aria-hidden="true">
    <div class="modal-dialog modal-dialog-popin modal-lg" role="document">
        <div class="modal-content">
            <div class="block block-rounded block-transparent mb-0">
                <div class="block-header block-header-default">
                    <h3 class="block-title">@{{ activeLocker ? activeLocker.number : 'Select a Locker' }}</h3>
                    <div class="block-options">
                        <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fa fa-fw fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="block-content fs-sm space-y-3">
                    <div class="row">
                        <div class="col-sm-4">
                            <label>Booking Name</label>
                        </div>
                        <div class="col-sm-8">
                            <input class="form-control" placeholder="Nama Booking" type="text" v-model="bookingName">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4">
                            <label>Member Phone Number</label>
                        </div>
                        <div class="col-sm-6">
                            <input class="form-control" placeholder="Nomor Telepon Member" type="text" v-model="bookingMemberPhone">
                            <span v-if="successSearchMember == false && messageSearchMember" class="form-text text-danger">@{{ messageSearchMember }}</span>
                            <span v-if="successSearchMember == true && messageSearchMember" class="form-text text-success">@{{ messageSearchMember }}</span>
                        </div>
                        <div class="col-sm-2">
                            <button type="button" class="btn btn-outline-info" @click="searchMember()">Search</button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4">
                            <label>Treatment</label>
                        </div>
                        <div class="col-sm-8">
                            <select2 url="{{ route('admin.package.select') }}" class="form-control" v-model="bookingPackage"
                                     placeholder="Pilih Treatment">
                            </select2>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4">
                            <label>Therapist</label>
                        </div>
                        <div class="col-sm-8">
                            <select2 url="{{ route('admin.partner.select') }}" class="form-control" v-model="bookingTherapist"
                                     placeholder="Pilih Therapist">
                            </select2>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-info" @click="bookingRoom()">Booking</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{--<!-- Pop In Block Modal -->--}}
{{--<div class="modal fade" id="modal-block-popin" tabindex="-1" role="dialog" aria-labelledby="modal-block-popin" aria-hidden="true">--}}
{{--    <div class="modal-dialog modal-dialog-popin" role="document">--}}
{{--        <div class="modal-content">--}}
{{--            <div class="block block-rounded block-transparent mb-0">--}}
{{--                <div class="block-header block-header-default">--}}
{{--                    <h3 class="block-title">Locker</h3>--}}
{{--                    <div class="block-options">--}}
{{--                        <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">--}}
{{--                            <i class="fa fa-fw fa-times"></i>--}}
{{--                        </button>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="block-content fs-sm">--}}
{{--                    <p id="menu_description"></p>--}}
{{--                    <div class="col-12 alert alert-danger alert-dismissible" role="alert" v-if="activeMenu === 'Cancel' && cancelMsg !== ''">--}}
{{--                        <p class="mb-0">--}}
{{--                            @{{ cancelMsg }}--}}
{{--                        </p>--}}
{{--                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>--}}
{{--                    </div>--}}
{{--                    <div class="row" v-if="activeMenu === 'Cancel'">--}}
{{--                        <div class="col-6">--}}
{{--                            <label>Konfirmasi Password</label>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="row mb-2" v-if="activeMenu === 'Cancel'">--}}
{{--                        <div class="col-6">--}}
{{--                            <input id="cancel-password" type="password" v-model="cancelPassword" class="form-control" placeholder="Konfirmasi Password"/>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="block-content block-content-full text-end bg-body">--}}
{{--                    <button type="button" class="btn btn-sm btn-alt-secondary me-1" data-bs-dismiss="modal">No</button>--}}
{{--                    <button type="button" class="btn btn-sm btn-primary" v-on:click="checkMenu()">Yes</button>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--</div>--}}
{{--<!-- END Pop In Block Modal -->--}}

{{--<!-- Treatment Modal -->--}}
{{--<div class="modal fade" id="modal-block-package" tabindex="-1" role="dialog" aria-labelledby="modal-block-package" aria-hidden="true">--}}
{{--    <div class="modal-dialog modal-dialog-popin" role="document">--}}
{{--        <div class="modal-content">--}}
{{--            <div class="block block-rounded block-transparent mb-0">--}}
{{--                <div class="block-header block-header-default">--}}
{{--                    <h3 class="block-title">Locker</h3>--}}
{{--                    <div class="block-options">--}}
{{--                        <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">--}}
{{--                            <i class="fa fa-fw fa-times"></i>--}}
{{--                        </button>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="block-content fs-sm">--}}

{{--                </div>--}}
{{--                <div class="block-content block-content-full text-end bg-body">--}}
{{--                    <button type="button" class="btn btn-sm btn-alt-secondary me-1" data-bs-dismiss="modal">No</button>--}}
{{--                    <button type="button" class="btn btn-sm btn-primary" v-on:click="checkMenu()">Yes</button>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--</div>--}}
{{--<!-- Large Block Modal -->--}}
{{--<div class="modal" id="modal-block-large" tabindex="-1" role="dialog" aria-labelledby="modal-block-large" aria-hidden="true">--}}
{{--    <div class="modal-dialog modal-dialog-popin modal-lg" role="document">--}}
{{--        <div class="modal-content">--}}
{{--            <div class="block block-rounded block-transparent mb-0">--}}
{{--                <div class="block-header block-header-default">--}}
{{--                    <h3 class="block-title">Pilih Treatment</h3>--}}
{{--                    <div class="block-options">--}}
{{--                        <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">--}}
{{--                            <i class="fa fa-fw fa-times"></i>--}}
{{--                        </button>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="block-content fs-sm">--}}
{{--                    <div class="row">--}}
{{--                        <div class="col-2 text-start">--}}
{{--                            <select v-on:change="getPackages" v-model="packagePagination.take" class="form-select">--}}
{{--                                <option>10</option>--}}
{{--                                <option>25</option>--}}
{{--                                <option>50</option>--}}
{{--                            </select>--}}
{{--                        </div>--}}
{{--                        <div class="col-10 text-end">--}}
{{--                            <input type="text" class="form-control" v-model="packagePagination.search" v-on:input="getPackages" placeholder="Cari Paket"/>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="table-responsive w-100 mt-1">--}}
{{--                        <table class="table table-bordered table-striped table-vcenter js-dataTable-full" id="table_data_package">--}}
{{--                            <thead>--}}
{{--                            <tr>--}}
{{--                                <th>No</th>--}}
{{--                                <th>Nama Paket</th>--}}
{{--                                <th>Durasi (menit)</th>--}}
{{--                                <th>Tipe Room</th>--}}
{{--                                <th>Harga</th>--}}
{{--                                <th>Opsi</th>--}}
{{--                            </tr>--}}
{{--                            </thead>--}}
{{--                            <tbody>--}}
{{--                            <tr v-if="packagePagination.isLoading">--}}
{{--                                <td colspan="6" class="text-center">--}}
{{--                                    <div class="spinner-border text-info mt-2 mb-2">--}}
{{--                                        <span class="visually-hidden">Loading...</span>--}}
{{--                                    </div>--}}
{{--                                </td>--}}
{{--                            </tr>--}}
{{--                            <tr v-for="(item, index) in packages">--}}
{{--                                <td>@{{ index+1 }}</td>--}}
{{--                                <td>@{{ item.name }}</td>--}}
{{--                                <td>@{{ item.duration }}</td>--}}
{{--                                <td>@{{ item.room_type }}</td>--}}
{{--                                <td>@{{ item.price_str }}</td>--}}
{{--                                <td><button type="button" class="btn btn-success" v-on:click="showModalConfirmation(item)"><i class="fa fa-plus"></i>Pilih</button></td>--}}
{{--                            </tr>--}}
{{--                            <tr>--}}
{{--                                <td colspan="4">Showing <span>@{{ packages.length }}</span> of @{{ packagePagination.count }}</td>--}}
{{--                                <td colspan="2" class="text-center">--}}
{{--                                    <button type="button" v-on:click="previousPackagePage"><i class="fa fa-chevron-left"></i></button>--}}
{{--                                    &nbsp;&nbsp;--}}
{{--                                    <button type="button" v-on:click="nextPackagePage"><i class="fa fa-chevron-right"></i></button>--}}
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
{{--<!-- END Large Block Modal -->--}}
{{--<!-- Modal Confirmation package -->--}}
{{--<div class="modal fade" id="modal_package_confirmation" tabindex="-1" role="dialog">--}}
{{--    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-slideup">--}}
{{--        <div class="modal-content">--}}
{{--            <div class="block-content">--}}
{{--                <div class="row">--}}
{{--                    <div class="col-12 text-center">--}}
{{--                        <span>Apakah Anda yakin untuk memilih paket ini untuk loker <b v-if="activeLocker !== null">@{{ activeLocker.number }}</b>?</span>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="block-content block-content-full text-center bg-body">--}}
{{--                <button type="button" id="btn_cancel_select_package" class="btn btn-warning" data-bs-dismiss="modal">No</button>--}}
{{--                <button type="button" id="btn_submit_select_package" class="btn btn-danger" v-on:click="selectPackage()">Yes</button>--}}
{{--                <button type="button" id="btn_loading_select_package" class="btn btn-primary" style="display: none;">--}}
{{--                    <i class="spinner-border spinner-border-sm text-green" role="status">--}}
{{--                        <span class="visually-hidden">Please wait...</span>--}}
{{--                    </i>--}}
{{--                </button>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--</div>--}}
{{--<!-- END Modal Confirmation package -->--}}

{{--<!-- Large Block Modal -->--}}
{{--<div class="modal" id="modal-block-room-large" tabindex="-1" role="dialog" aria-labelledby="modal-block-large" aria-hidden="true">--}}
{{--    <div class="modal-dialog modal-dialog-popin modal-lg" role="document">--}}
{{--        <div class="modal-content">--}}
{{--            <div class="block block-rounded block-transparent mb-0">--}}
{{--                <div class="block-header block-header-default">--}}
{{--                    <h3 class="block-title">Pilih Room</h3>--}}
{{--                    <div class="block-options">--}}
{{--                        <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">--}}
{{--                            <i class="fa fa-fw fa-times"></i>--}}
{{--                        </button>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="block-content fs-sm">--}}
{{--                    <div class="row">--}}
{{--                        <div class="col-2 text-start">--}}
{{--                            <select v-on:change="getRooms" v-model="roomPagination.take" class="form-select">--}}
{{--                                <option>10</option>--}}
{{--                                <option>25</option>--}}
{{--                                <option>50</option>--}}
{{--                            </select>--}}
{{--                        </div>--}}
{{--                        <div class="col-10 text-end">--}}
{{--                            <input type="text" class="form-control" v-model="roomPagination.search" v-on:input="getRooms" placeholder="Cari Room"/>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="table-responsive w-100 mt-1">--}}
{{--                        <table class="table table-bordered table-striped table-vcenter js-dataTable-full" id="table_data_room">--}}
{{--                            <thead>--}}
{{--                            <tr>--}}
{{--                                <th>No</th>--}}
{{--                                <th>Nomor Room</th>--}}
{{--                                <th>Nama Room</th>--}}
{{--                                <th>Tipe Room</th>--}}
{{--                                <th>Status Room</th>--}}
{{--                                <th>Opsi</th>--}}
{{--                            </tr>--}}
{{--                            </thead>--}}
{{--                            <tbody>--}}
{{--                            <tr v-if="roomPagination.isLoading">--}}
{{--                                <td colspan="6" class="text-center">--}}
{{--                                    <div class="spinner-border text-info mt-2 mb-2">--}}
{{--                                        <span class="visually-hidden">Loading...</span>--}}
{{--                                    </div>--}}
{{--                                </td>--}}
{{--                            </tr>--}}
{{--                            <tr v-for="(item, index) in rooms">--}}
{{--                                <td>@{{ index+1 }}</td>--}}
{{--                                <td>@{{ item.number }}</td>--}}
{{--                                <td>@{{ item.name }}</td>--}}
{{--                                <td>@{{ item.type }}</td>--}}
{{--                                <td>--}}
{{--                                    <span style="color: pink;" v-if="item.status === 'Cleaning'">@{{ item.status }}</span>--}}
{{--                                    <span style="color: lightgreen;" v-if="item.status === 'Available'">@{{ item.status }}</span>--}}
{{--                                    <span style="color: red;" v-if="item.status === 'Occupied'">@{{ item.status }}</span>--}}
{{--                                </td>--}}
{{--                                <td>--}}
{{--                                    <button type="button" class="btn btn-success" v-if="item.status === 'Available'" v-on:click="showModalConfirmationRoom(item)"><i class="fa fa-plus"></i>Pilih</button>--}}
{{--                                    <span v-else>Tidak bisa dipilih</span>--}}
{{--                                </td>--}}
{{--                            </tr>--}}
{{--                            <tr>--}}
{{--                                <td colspan="4">Showing <span>@{{ rooms.length }}</span> of @{{ roomPagination.count }}</td>--}}
{{--                                <td colspan="2" class="text-center">--}}
{{--                                    <button type="button" v-on:click="previousRoomPage"><i class="fa fa-chevron-left"></i></button>--}}
{{--                                    &nbsp;&nbsp;--}}
{{--                                    <button type="button" v-on:click="nextRoomPage"><i class="fa fa-chevron-right"></i></button>--}}
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
{{--<!-- END Large Block Modal -->--}}

{{--<!-- Large Block Modal -->--}}
{{--<div class="modal" id="modal-switch-room" tabindex="-1" role="dialog" aria-labelledby="modal-block-large" aria-hidden="true">--}}
{{--    <div class="modal-dialog modal-dialog-popin modal-lg" role="document">--}}
{{--        <div class="modal-content">--}}
{{--            <div class="block block-rounded block-transparent mb-0">--}}
{{--                <div class="block-header block-header-default">--}}
{{--                    <h3 class="block-title">Pindah Room</h3>--}}
{{--                    <div class="block-options">--}}
{{--                        <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">--}}
{{--                            <i class="fa fa-fw fa-times"></i>--}}
{{--                        </button>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="block-content fs-sm">--}}
{{--                    <div class="row">--}}
{{--                        <div class="col-2 text-start">--}}
{{--                            <select v-on:change="getRooms" v-model="roomPagination.take" class="form-select">--}}
{{--                                <option>10</option>--}}
{{--                                <option>25</option>--}}
{{--                                <option>50</option>--}}
{{--                            </select>--}}
{{--                        </div>--}}
{{--                        <div class="col-10 text-end">--}}
{{--                            <input type="text" class="form-control" v-model="roomPagination.search" v-on:input="getRooms" placeholder="Cari Room"/>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="table-responsive w-100 mt-1">--}}
{{--                        <table class="table table-bordered table-striped table-vcenter js-dataTable-full" id="table_data_room">--}}
{{--                            <thead>--}}
{{--                            <tr>--}}
{{--                                <th>No</th>--}}
{{--                                <th>Nomor Room</th>--}}
{{--                                <th>Nama Room</th>--}}
{{--                                <th>Tipe Room</th>--}}
{{--                                <th>Status Room</th>--}}
{{--                                <th>Opsi</th>--}}
{{--                            </tr>--}}
{{--                            </thead>--}}
{{--                            <tbody>--}}
{{--                            <tr v-if="roomPagination.isLoading">--}}
{{--                                <td colspan="6" class="text-center">--}}
{{--                                    <div class="spinner-border text-info mt-2 mb-2">--}}
{{--                                        <span class="visually-hidden">Loading...</span>--}}
{{--                                    </div>--}}
{{--                                </td>--}}
{{--                            </tr>--}}
{{--                            <tr v-for="(item, index) in rooms">--}}
{{--                                <td>@{{ index+1 }}</td>--}}
{{--                                <td>@{{ item.number }}</td>--}}
{{--                                <td>@{{ item.name }}</td>--}}
{{--                                <td>@{{ item.type }}</td>--}}
{{--                                <td>--}}
{{--                                    <span style="color: pink;" v-if="item.status === 'Cleaning'">@{{ item.status }}</span>--}}
{{--                                    <span style="color: lightgreen;" v-if="item.status === 'Available'">@{{ item.status }}</span>--}}
{{--                                    <span style="color: red;" v-if="item.status === 'Occupied'">@{{ item.status }}</span>--}}
{{--                                </td>--}}
{{--                                <td>--}}
{{--                                    <button type="button" class="btn btn-success" v-if="item.status === 'Available'" v-on:click="showModalConfirmationSwitchRoom(item)"><i class="fa fa-plus"></i>Pilih</button>--}}
{{--                                    <span v-else>Tidak bisa dipilih</span>--}}
{{--                                </td>--}}
{{--                            </tr>--}}
{{--                            <tr>--}}
{{--                                <td colspan="4">Showing <span>@{{ rooms.length }}</span> of @{{ roomPagination.count }}</td>--}}
{{--                                <td colspan="2" class="text-center">--}}
{{--                                    <button type="button" v-on:click="previousRoomPage"><i class="fa fa-chevron-left"></i></button>--}}
{{--                                    &nbsp;&nbsp;--}}
{{--                                    <button type="button" v-on:click="nextRoomPage"><i class="fa fa-chevron-right"></i></button>--}}
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
{{--<!-- END Large Block Modal -->--}}

{{--<!-- Modal Confirmation package -->--}}
{{--<div class="modal fade" id="modal_room_confirmation" tabindex="-1" role="dialog">--}}
{{--    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-slideup">--}}
{{--        <div class="modal-content">--}}
{{--            <div class="block-content">--}}
{{--                <div class="row">--}}
{{--                    <div class="col-12 text-center">--}}
{{--                        <span>Apakah Anda yakin untuk memilih room ini untuk loker <b v-if="activeLocker !== null">@{{ activeLocker.number }}</b>?</span>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="block-content block-content-full text-center bg-body">--}}
{{--                <button type="button" id="btn_cancel_select_room" class="btn btn-warning" data-bs-dismiss="modal">No</button>--}}
{{--                <button type="button" id="btn_submit_select_room" class="btn btn-danger" v-on:click="selectRoom()">Yes</button>--}}
{{--                <button type="button" id="btn_loading_select_room" class="btn btn-primary" style="display: none;">--}}
{{--                    <i class="spinner-border spinner-border-sm text-green" role="status">--}}
{{--                        <span class="visually-hidden">Please wait...</span>--}}
{{--                    </i>--}}
{{--                </button>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--</div>--}}
{{--<!-- END Modal Confirmation package -->--}}

{{--<!-- Modal Confirmation switch Room -->--}}
{{--<div class="modal fade" id="modal-switch-room-confirmation" tabindex="-1" role="dialog">--}}
{{--    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-slideup">--}}
{{--        <div class="modal-content">--}}
{{--            <div class="block-content">--}}
{{--                <div class="row">--}}
{{--                    <div class="col-12 text-center">--}}
{{--                        <span>Apakah Anda yakin untuk pindah ke room <b v-if="activeLocker !== null">@{{ activeLocker.number }}</b>?</span>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="block-content block-content-full text-center bg-body">--}}
{{--                <button type="button" id="btn_cancel_switch_room" class="btn btn-warning" data-bs-dismiss="modal">No</button>--}}
{{--                <button type="button" id="btn_submit_switch_room" class="btn btn-danger" v-on:click="switchRoom()">Yes</button>--}}
{{--                <button type="button" id="btn_loading_switch_room" class="btn btn-primary" style="display: none;">--}}
{{--                    <i class="spinner-border spinner-border-sm text-green" role="status">--}}
{{--                        <span class="visually-hidden">Please wait...</span>--}}
{{--                    </i>--}}
{{--                </button>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--</div>--}}
{{--<!-- END Modal Confirmation switch Room -->--}}

{{--<!-- Large Modal GRO -->--}}
{{--<div class="modal" id="modal-gro" tabindex="-1" role="dialog" aria-labelledby="modal-gro" aria-hidden="true">--}}
{{--    <div class="modal-dialog modal-dialog-popin modal-lg" role="document">--}}
{{--        <div class="modal-content">--}}
{{--            <div class="block block-rounded block-transparent mb-0">--}}
{{--                <div class="block-header block-header-default">--}}
{{--                    <h3 class="block-title">Pilih GRO</h3>--}}
{{--                    <div class="block-options">--}}
{{--                        <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">--}}
{{--                            <i class="fa fa-fw fa-times"></i>--}}
{{--                        </button>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="block-content fs-sm">--}}
{{--                    <div class="row">--}}
{{--                        <div class="col-2 text-start">--}}
{{--                            <select v-on:change="getGros" v-model="groPagination.take" class="form-select">--}}
{{--                                <option>10</option>--}}
{{--                                <option>25</option>--}}
{{--                                <option>50</option>--}}
{{--                            </select>--}}
{{--                        </div>--}}
{{--                        <div class="col-10 text-end">--}}
{{--                            <input id="gro-search" type="text" class="form-control" v-model="groPagination.search" v-on:input="getGros" placeholder="Cari GRO"/>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="table-responsive w-100 mt-1">--}}
{{--                        <table class="table table-bordered table-striped table-vcenter js-dataTable-full" id="table_data_package">--}}
{{--                            <thead>--}}
{{--                            <tr>--}}
{{--                                <th>No</th>--}}
{{--                                <th>Nama</th>--}}
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
{{--                            <tr v-for="(item, index) in gros">--}}
{{--                                <td>@{{ index+1 }}</td>--}}
{{--                                <td>@{{ item.name }}</td>--}}
{{--                                <td><button type="button" class="btn btn-success" v-on:click="showModalConfirmationGro(item)"><i class="fa fa-plus"></i>Pilih</button></td>--}}
{{--                            </tr>--}}
{{--                            <tr>--}}
{{--                                <td colspan="2">Showing <span>@{{ gros.length }}</span> of @{{ groPagination.count }}</td>--}}
{{--                                <td class="text-center">--}}
{{--                                    <button type="button" v-on:click="previousGroPage"><i class="fa fa-chevron-left"></i></button>--}}
{{--                                    &nbsp;&nbsp;--}}
{{--                                    <button type="button" v-on:click="nextGroPage"><i class="fa fa-chevron-right"></i></button>--}}
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
{{--<!-- END Modal GRO -->--}}

{{--<!-- Modal Confirmation gro -->--}}
{{--<div class="modal fade" id="modal-confirmation-gro" tabindex="-1" role="dialog">--}}
{{--    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-slideup">--}}
{{--        <div class="modal-content">--}}
{{--            <div class="block-content">--}}
{{--                <div class="row">--}}
{{--                    <div class="col-12 text-center">--}}
{{--                        <span>Apakah Anda yakin untuk memilih gro ini untuk loker <b v-if="activeLocker !== null">@{{ activeLocker.number }}</b>?</span>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="block-content block-content-full text-center bg-body">--}}
{{--                <button type="button" id="btn_cancel_select_gro" class="btn btn-warning" data-bs-dismiss="modal">No</button>--}}
{{--                <button type="button" id="btn_submit_select_gro" class="btn btn-danger" v-on:click="selectGro()">Yes</button>--}}
{{--                <button type="button" id="btn_loading_select_gro" class="btn btn-primary" style="display: none;">--}}
{{--                    <i class="spinner-border spinner-border-sm text-green" role="status">--}}
{{--                        <span class="visually-hidden">Please wait...</span>--}}
{{--                    </i>--}}
{{--                </button>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--</div>--}}
{{--<!-- END Modal Confirmation gro -->--}}

{{--<!-- Detil Modal -->--}}
{{--<div class="modal" id="modal-detil" tabindex="-1" role="dialog" aria-labelledby="modal-detil" aria-hidden="true">--}}
{{--    <div class="modal-dialog modal-dialog-popin modal-xl" role="document">--}}
{{--        <div class="modal-content">--}}
{{--            <div class="block block-rounded block-transparent mb-0">--}}
{{--                <div class="block-header block-header-default">--}}
{{--                    <h3 class="block-title">Detil</h3>--}}
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
{{--                            <span>GRO</span>--}}
{{--                        </div>--}}
{{--                        <div class="col-10">--}}
{{--                            <span v-if="activeLocker != null">@{{ activeLocker.gro_code }}</span>--}}
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
{{--                    <div class="row" v-if="isAthena">--}}
{{--                        <div class="col-12">--}}
{{--                            <h4 style="color: red">Jangan lupa menambahkan Liquor Kalo Athena dan tidak FOC ya!</h4>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="row" v-if="header !== null && header.is_happy_hour === 1">--}}
{{--                        <div class="col-6">--}}
{{--                            <h4 style="color: red">Happy Hour!</h4>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="table-responsive w-100 mt-1">--}}
{{--                        <table class="table table-bordered table-striped table-vcenter js-dataTable-full" id="table_data_transaction">--}}
{{--                            <thead>--}}
{{--                            <tr>--}}
{{--                                <th>No</th>--}}
{{--                                <th>Nama Item</th>--}}
{{--                                <th>Qty</th>--}}
{{--                                <th>Room</th>--}}
{{--                                <th>Time</th>--}}
{{--                                <th>Harga</th>--}}
{{--                                <th>Subtotal</th>--}}
{{--                                <th>Diskon</th>--}}
{{--                                <th>Status</th>--}}
{{--                            </tr>--}}
{{--                            </thead>--}}
{{--                            <tbody>--}}
{{--                            <tr v-for="(item, index) in details">--}}
{{--                                <td>@{{ index+1 }}</td>--}}
{{--                                <td>--}}
{{--                                    <span v-if="item.type === 'package'">@{{ item.package_name }}</span>--}}
{{--                                    <span v-if="item.type === 'item'">@{{ item.item_name }}</span>--}}
{{--                                    <span v-if="item.is_facility_only">Hanya Fasilitas</span>--}}
{{--                                </td>--}}
{{--                                <td>--}}
{{--                                    <input type="number" class="form-control text-end" v-model="item.qty" readonly/>--}}
{{--                                </td>--}}
{{--                                <td>--}}
{{--                                    @{{ item.room_number }}--}}
{{--                                </td>--}}
{{--                                <td>--}}
{{--                                    @{{ item.time_display }}--}}
{{--                                </td>--}}
{{--                                <td class="text-end">@{{ item.price_str }}</td>--}}
{{--                                <td><input type="text" class="form-control text-end" v-model="item.subtotal_str" readonly/></td>--}}
{{--                                <td><input type="text" class="form-control text-end" v-model="item.discount_str" readonly/></td>--}}
{{--                                <td><input type="text" class="form-control text-end" v-model="item.status" readonly/></td>--}}
{{--                                <td>--}}
{{--                                    <button type="button" class="btn btn-danger" v-on:click="showDeleteItemModal(item)"><i class="fa fa-minus"></i> Hapus</button>--}}
{{--                                </td>--}}
{{--                            </tr>--}}
{{--                            <tr>--}}
{{--                                <td colspan="6" class="text-end"><b>Total</b></td>--}}
{{--                                <td class="text-end"><b>@{{ grandTotalStr }}</b></td>--}}
{{--                            </tr>--}}
{{--                            </tbody>--}}
{{--                        </table>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--</div>--}}
{{--<!-- END Detil Modal -->--}}

{{--<!-- Modal Delete Item -->--}}
{{--<div class="modal fade" id="modal-delete-item" tabindex="-1" role="dialog" aria-labelledby="modal-block-popin" aria-hidden="true">--}}
{{--    <div class="modal-dialog modal-dialog-popin" role="document">--}}
{{--        <div class="modal-content">--}}
{{--            <div class="block block-rounded block-transparent mb-0">--}}
{{--                <div class="block-header block-header-default">--}}
{{--                    <h3 class="block-title">Hapus Item</h3>--}}
{{--                    <div class="block-options">--}}
{{--                        <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">--}}
{{--                            <i class="fa fa-fw fa-times"></i>--}}
{{--                        </button>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="block-content fs-sm">--}}
{{--                    <p id="menu_description"></p>--}}
{{--                    <div class="col-12 alert alert-danger alert-dismissible" role="alert" v-if="deleteMsg !== ''">--}}
{{--                        <p class="mb-0">--}}
{{--                            @{{ deleteMsg }}--}}
{{--                        </p>--}}
{{--                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>--}}
{{--                    </div>--}}
{{--                    <div class="row">--}}
{{--                        <div class="col-6">--}}
{{--                            <label>Konfirmasi Password</label>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="row">--}}
{{--                        <div class="col-6">--}}
{{--                            <label>Apakah yakin menghapus item ini?</label>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="row mb-2">--}}
{{--                        <div class="col-6">--}}
{{--                            <input id="password-confirmation" type="password" v-model="deletePassword" class="form-control" placeholder="Konfirmasi Password"/>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="block-content block-content-full text-end bg-body">--}}
{{--                    <button type="button" id="btn_delete_item_no" class="btn btn-sm btn-alt-secondary me-1" data-bs-dismiss="modal">No</button>--}}
{{--                    <button type="button" id="btn_delete_item_yes" class="btn btn-sm btn-primary" v-on:click="confirmDelete()">Yes</button>--}}
{{--                    <i v-if="deleteLoading" class="spinner-border spinner-border-sm text-green" role="status"></i>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--</div>--}}
{{--<!-- END Modal Delete Item -->--}}

{{--<!-- Modal Checkout -->--}}
{{--<div class="modal fade" id="modal-checkout" tabindex="-1" role="dialog" aria-labelledby="modal-checkout" aria-hidden="true">--}}
{{--    <div class="modal-dialog modal-dialog-popin" role="document">--}}
{{--        <div class="modal-content">--}}
{{--            <div class="block block-rounded block-transparent mb-0">--}}
{{--                <div class="block-header block-header-default">--}}
{{--                    <h3 class="block-title">Checkout</h3>--}}
{{--                    <div class="block-options">--}}
{{--                        <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">--}}
{{--                            <i class="fa fa-fw fa-times"></i>--}}
{{--                        </button>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="block-content fs-sm">--}}
{{--                    <div class="col-12 alert alert-danger alert-dismissible" role="alert" v-if="checkOutMsg !== ''">--}}
{{--                        <p class="mb-0">--}}
{{--                            @{{ checkOutMsg }}--}}
{{--                        </p>--}}
{{--                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>--}}
{{--                    </div>--}}
{{--                    <div class="row">--}}
{{--                        <div class="col-12">--}}
{{--                            <label>Apakah yakin checkout transaksi pada loker ini?</label>--}}
{{--                            <label>*Jika dikonfirmasi maka status Therapis akan menjadi istirahat dan kamar akan dibersihkan*</label>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="block-content block-content-full text-end bg-body">--}}
{{--                    <button type="button" id="btn_cancel_checkout" class="btn btn-sm btn-alt-secondary me-1" data-bs-dismiss="modal">No</button>--}}
{{--                    <button type="button" id="btn_checkout" class="btn btn-sm btn-primary" v-on:click="checkoutLocker()">Yes</button>--}}
{{--                    <i v-if="checkOutLoading" class="spinner-border spinner-border-sm text-green" role="status"></i>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--</div>--}}
<!-- END Modal Checkout -->

{{--<!-- Loading Div -->--}}
{{--<div class="loading" v-if="fcLoading">--}}
{{--    <div class="spinner-border text-info">--}}
{{--        <span class="visually-hidden">Loading...</span>--}}
{{--    </div>--}}
{{--</div>--}}
{{--<!-- End Loading Div -->--}}
