<div class="modal" id="checkoutModal" role="dialog" aria-labelledby="checkoutModal" aria-hidden="true" style="overflow-y: hidden !important;">
    <div class="modal-dialog modal-dialog-popin modal-lg" role="document" style="max-width: 60vw;">
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
                                     placeholder="Pilih Kasir" dropdown="#checkoutModal">
                            </select2>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-4">
                            <label>Member Name</label>
                        </div>
                        <div class="col-sm-6">
                            <input class="form-control" placeholder="Nama Member" type="text" v-model="bookingName" @focus="isDropdownVisible = true" @blur="hideDropdown">
                            <div v-if="isDropdownVisible && existingCustomers.length > 0" class="dropdown-menu">
                                <div class="dropdown-section" v-for="customer in existingCustomers" :key="customer.customer_phone">
                                    <div class="dropdown-item" @click="selectExistingCustomer(customer)">
                                        <label>@{{ customer.customer_name }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4">
                            <label>Member Phone Number</label>
                        </div>
                        <div class="col-sm-4">
                            <div class="input-group">
                                <select class="form-select w-auto" style="flex: 0 0 auto !important;" v-model="phoneCode">
                                    <option v-for="item in countries" :value="item.dial_code">@{{ item.dial_code }}</option>
                                </select>
                                <input class="form-control" placeholder="Nomor Telepon Member" type="text" v-model="bookingMemberPhone">
                            </div>

                            <span v-if="successSearchMember == false && messageSearchMember" class="form-text text-danger">@{{ messageSearchMember }}</span>
                            <span v-if="successSearchMember == true && messageSearchMember" class="form-text text-success">@{{ messageSearchMember }}</span>
                        </div>
                        <div class="col-sm-2">
                            <button type="button" class="btn btn-outline-info" @click="searchMember(bookingMemberPhone)">Search</button>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-4">
                            <label>Gender</label>
                        </div>
                        <div class="col-sm-4">
                            <select class="form-select" name="gender" v-model="gender" required>
                                <option value="L">Laki - Laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-4">
                            <label>Tanggal Expired Voucher</label>
                        </div>
                        <div class="col-sm-2">
                            <input type="text" class="form-control date" v-model="expiredDate">
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
                                        <th style="width: 20%;" class="text-center">Voucher</th>
                                        <th style="width: 8%;" class="text-center">Qty</th>
                                        <th style="width: 15%;" class="text-center">Amount</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="(item, index) in details" :key="item.uid">
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
                                            <vue-autonumeric class="form-control" :value="(item.price * item.qty)" :options="autonumericFormat" readonly>
                                            </vue-autonumeric>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="fw-bold text-end">Subtotal</td>
                                        <td class="fw-bold">@{{ grandTotalPayment.toLocaleString('id-ID') }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="fw-bold text-end">PB2 (10%)</td>
                                        <td class="fw-bold">@{{ pb2.toLocaleString('id-ID') }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="fw-bold text-end">Rounding Amount</td>
                                        <td class="fw-bold">
                                            <vue-autonumeric class="form-control" v-model="customAmount" :options="autonumericFormat"></vue-autonumeric>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="fw-bold text-end">Grand Total</td>
                                        <td class="fw-bold">@{{ grandTotalPayment.toLocaleString('id-ID') }}</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <hr style="opacity: 1">
                    <div class="row">
                        <div class="col-sm-4">
                            <label class="mb-2">Jumlah Dibayar</label>
                            <vue-autonumeric class="form-control" v-model="amountReceived" :options="autonumericFormat" placeholder="0" :readonly="selectedPaymentMethod != 1">
                            </vue-autonumeric>
                        </div>
                        <div class="col-sm-4">
                            <label class="mb-2">Kembalian</label>
                            <vue-autonumeric class="form-control" v-model="returnValue" :options="autonumericFormat" placeholder="0" readonly>
                            </vue-autonumeric>
                        </div>
                        <div class="col-sm-4">
                            <label class="mb-2">Metode Pembayaran</label>
                            <select2 ref="paymentSelect" id="paymentMethod" url="{{ route('admin.pos.select') }}" class="form-control" v-model="selectedPaymentMethod"
                                     placeholder="Pilih metode pembayaran" dropdown="#checkoutModal">
                            </select2>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-info ms-2" @click="checkout()" :disabled="amountReceived < grandTotalPayment">Pay</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
