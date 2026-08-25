<div class="modal" id="processModal" role="dialog" aria-labelledby="processModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-popin modal-lg" role="document" style="max-width: 60vw;">
        <div class="modal-content">
            <div class="block block-rounded block-transparent mb-0">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Proses</h3>
                    {{--                    <h3 class="block-title">@{{ activeRoom ? activeRoom.number : 'Select a Locker' }}</h3>--}}
                    <div class="block-options">
                        <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close" @click="clearData(activeRoom.status_id)">
                            <i class="fa fa-fw fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="block-content fs-sm space-y-3">
                    <div class="row">
                        <div class="col-sm-4">
                            <label>Kasir</label>
                        </div>
                        <div class="col-sm-4">
                            <select2 id="employee" url="{{ route('admin.employee.select', ['position_id' => 1]) }}" class="form-control" v-model="employee" :value="employee"
                                     placeholder="Pilih Kasir" :disabled="loading">
                            </select2>
                        </div>
                    </div>

                    <hr style="opacity: 1">

                    <div class="row">
                        <div class="col-sm-12">
                            <div class="table-responsive w-100">
                                <table class="table table-bordered table-striped table-vcenter" id="table_new_order">
                                    <thead>
                                    <tr>
                                        <th style="width: 6%;" class="text-center">
                                            No
                                        </th>
                                        <th style="width: 30%;" class="text-center">Treatment/Item</th>
                                        <th class="text-center" style="width: 14%">Therapist</th>
                                        <th style="width: 15%;" class="text-center">Ruang</th>
                                        <th style="width: 15%;" class="text-center">Tanggal</th>
                                        <th style="width: 10%;" class="text-center">Waktu</th>
                                        <th style="width: 10%;" class="text-center">Durasi</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="(item, index) in checkouts">
                                        <td class="text-center">
                                            <input type="text" class="form-control" :value="index+1" readonly>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control"
                                                   v-model="item.name" readonly>
                                        </td>
                                        <td>
                                            <select2 url="{{ route('admin.partner.select') }}" class="form-control" v-model="item.partner_id" :value="item.partner_id"
                                                     placeholder="Pilih terapis yang tersedia" :disabled="loading">
                                            </select2>
                                        </td>
                                        <td>
                                            <select2 url="{{ route('admin.room.select_all') }}" class="form-control" v-model="item.room_id" :value="item.room_id"
                                                     placeholder="Pilih Ruangan" :disabled="loading">
                                            </select2>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control date" placeholder="Pilih Tanggal"
                                                   v-model="item.date" readonly :disabled="loading">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control time" placeholder="Pilih Jam"
                                                   v-model="item.time" readonly :disabled="loading">
                                        </td>
                                        <td>
                                            <vue-autonumeric class="form-control" :name="'details['+index+'][duration]'" v-model="item.duration" :options="autonumericFormat" required></vue-autonumeric>
                                        </td>
                                    </tr>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-info ms-2" v-if="loading" disabled><i class="fa fa-fw fa-spin fa-circle-notch"></i></button>
                        <button type="button" class="btn btn-info ms-2" v-else @click="process">Proses</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
