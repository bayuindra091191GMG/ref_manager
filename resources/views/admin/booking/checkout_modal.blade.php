<div class="modal" id="checkoutModal" role="dialog" aria-labelledby="checkoutModal" aria-hidden="true" style="overflow-y: hidden !important;">
    <div class="modal-dialog modal-dialog-popin modal-lg" role="document" style="max-width: 70vw;">
        <div class="modal-content">
            <div class="block block-rounded block-transparent mb-0">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Checkout</h3>
                    {{--                    <h3 class="block-title">@{{ activeRoom ? activeRoom.number : 'Select a Locker' }}</h3>--}}
                    <div class="block-options">
                        <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close" @click="clearData(activeRoom.status_id)">
                            <i class="fa fa-fw fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="block-content fs-sm space-y-3" style="max-height: calc(100vh - 120px); overflow-y: auto">
                    <div class="row">
                        <div class="col-sm-4">
                            <label>Kasir</label>
                        </div>
                        <div class="col-sm-4">
                            <select2 id="employee" url="{{ route('admin.employee.select', ['position_id' => 1]) }}" class="form-control" v-model="employee" :value="employee"
                                     placeholder="Pilih Kasir">
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
                                        <th style="width: 8%;" class="text-center">Qty</th>
                                        <th class="text-center">Therapist</th>
                                        <th class="text-center">Room</th>
                                        <th class="text-center">Durasi</th>
                                        <th style="width: 20%;" class="text-center">Amount</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="(item, index) in details">
                                        <td class="text-center">
                                            <input type="text" class="form-control" :value="index+1" readonly>
                                        </td>
                                        <td>
                                            <input type="text" :id = "'treatment' + item.id" class="form-control"
                                                   v-model="item.name" readonly>
                                        </td>
                                        <td>
                                            <vue-autonumeric class="form-control" :value="item.type == 'package' ? 1 : item.qty" :options="autonumericFormat" readonly>
                                            </vue-autonumeric>
                                        </td>
                                        <td>
                                            <template v-if="item.type == 'package' || item.need_partner">
                                                <select2 ref="partnerSelect" url="{{ route('admin.partner.select') }}" class="form-control" v-model="item.partner_id"
                                                         placeholder="Pilih terapis yang tersedia">
                                                </select2>
                                                <br>
                                                <span v-if="item.messageAddOnTherapist" class="form-text text-danger mt-2">@{{ item.messageAddOnTherapist }}</span>
                                            </template>
                                        </td>
                                        <td>
                                            <template v-if="item.type == 'package' || item.need_partner">
                                                <select2 ref="partnerSelect" url="{{ route('admin.room.select_all') }}" class="form-control" :value="item.room_id" v-model="item.room_id"
                                                         placeholder="Pilih Room"  dropdown="#checkoutModal">
                                                </select2>
                                            </template>
                                        </td>
                                        <td>
                                            <template v-if="item.type == 'package' || item.need_partner">
                                                <vue-autonumeric class="form-control" v-model="item.duration" :options="autonumericFormat"></vue-autonumeric>
                                            </template>
                                        </td>
                                        <td>
                                            <vue-autonumeric class="form-control" :value="(item.price * item.qty)" :options="autonumericFormat" readonly>
                                            </vue-autonumeric>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="fw-bold text-end">Subtotal</td>
                                        <td class="fw-bold">@{{ subTotalPayment.toLocaleString('id-ID') }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="fw-bold text-end">PB2 (10%)</td>
                                        <td class="fw-bold">@{{ parseInt(pb2).toLocaleString('id-ID') }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="fw-bold text-end">Rounding Amount</td>
                                        <td class="fw-bold">
                                            <vue-autonumeric class="form-control" v-model="customAmount" :options="autonumericFormat"></vue-autonumeric>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="fw-bold text-end">Grand Total</td>
                                        <td class="fw-bold">@{{ grandTotalPayment.toLocaleString('id-ID') }}</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-info ms-2" @click="checkout()" :disabled="!validate">Finalisasi</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
