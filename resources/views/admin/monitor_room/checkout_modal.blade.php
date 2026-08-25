<div class="modal" id="checkoutModal" role="dialog" aria-labelledby="checkoutModal" aria-hidden="true" style="overflow-y: hidden !important;">
    <div class="modal-dialog modal-dialog-popin modal-lg" role="document" style="max-width: 95vw;">
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
                            <label>Total Tamu</label>
                        </div>
                        <div class="col-sm-2">
                            Laki - Laki
                            <vue-autonumeric class="form-control" name="total_cust_male" v-model="total_cust_male" :options="autonumericFormat"></vue-autonumeric>
                            {{-- <select class="form-select" name="total_cust_male" v-model="total_cust_male" required>
                                @for($i = 0; $i <= 50; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select> --}}
                        </div>
                        <div class="col-sm-2">
                            Perempuan
                            <vue-autonumeric class="form-control" name="total_cust_female" v-model="total_cust_female" :options="autonumericFormat"></vue-autonumeric>
                            {{-- <select class="form-select" name="total_cust_female" v-model="total_cust_female" required>
                                @for($i = 0; $i <= 50; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select> --}}
                        </div>
                    </div>
{{--                    <div class="row">--}}
{{--                        <div class="col-sm-4">--}}
{{--                            <label>Room</label>--}}
{{--                        </div>--}}
{{--                        <div class="col-sm-4">--}}
{{--                            <select2 id="roomId" url="{{ route('admin.room.select_all') }}" class="form-control" v-model="roomId" :value="roomId"--}}
{{--                                     placeholder="Pilih Ruangan"  dropdown="#checkoutModal">--}}
{{--                            </select2>--}}
{{--                        </div>--}}
{{--                    </div>--}}
                    <div class="row">
                        <div class="col-sm-4">
                            <label>Tanggal</label>
                        </div>
                        <div class="col-sm-3">
                            <input id="date" type="text" class="form-control"
                                   name="date" placeholder="Tanggal"
                                   v-model="date" readonly/>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4">
                            <label>Jam Mulai</label>
                        </div>
                        <div class="col-sm-3">
                            <input id="booking_time" type="text" class="js-flatpickr form-control"
                                   name="booking_time" placeholder="Jam Mulai"
                                   v-model="time" readonly/>
                        </div>
                    </div>
{{--                    <div class="row">--}}
{{--                        <div class="col-sm-4">--}}
{{--                            <label>Durasi</label>--}}
{{--                        </div>--}}
{{--                        <div class="col-sm-2">--}}
{{--                            <div class="input-group">--}}
{{--                                <vue-autonumeric class="form-control" v-model="duration" :options="autonumericFormat"></vue-autonumeric>--}}
{{--                                <span class="input-group-text" id="basic-addon2">Menit</span>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}

                    <hr style="opacity: 1">

{{--                    <div class="row">--}}
{{--                        <div class="col-sm-2">--}}
{{--                            <label>Voucher Code</label>--}}
{{--                        </div>--}}
{{--                        <div class="col-sm-8">--}}
{{--                            <div class="row" v-for="(voucher, index) in voucherList" :key="index">--}}
{{--                                <div class="col-sm-10">--}}
{{--                                    <input class="form-control mb-2"--}}
{{--                                           placeholder="Masukkan kode voucher" type="text"--}}
{{--                                           v-model="voucher.code" :readonly="voucher.isSuccess == 1 || !isUseVoucherCode || roomId == null || roomId == ''">--}}
{{--                                    <span v-if="voucher.isSuccess"--}}
{{--                                          :class="{ 'text-success': voucher.isSuccess, 'text-danger': !voucher.isSuccess }">--}}
{{--                                    @{{ voucher.informationStatus }}--}}
{{--                                </span>--}}
{{--                                </div>--}}

{{--                                <div class="col-sm-2 ms-auto">--}}
{{--                                    <button type="button" class="btn btn-outline-info w-100"--}}
{{--                                            @click="checkVoucher(voucher.code,index)"--}}
{{--                                            :disabled="voucher.isSuccess == 1 || !isUseVoucherCode || roomId == null || roomId == ''">Check</button>--}}
{{--                                    <button type="button" class="btn btn-outline-danger w-100"--}}
{{--                                            @click="checkVoucher(voucher.code,index)" v-if="voucher.isSuccess == 1">Remove</button>--}}
{{--                                </div>--}}
{{--                            </div>--}}

{{--                            <div class="form-check d-flex align-items-center mt-1">--}}
{{--                                <input class="form-check-input" type="checkbox" id="is_use_voucher_code"--}}
{{--                                       v-model="isUseVoucherCode">--}}
{{--                                <label class="form-check-label ms-1" for="is_use_voucher_code">--}}
{{--                                    Use Voucher? <span class="text-red"> Unceklis penggunaan voucher jika ingin pakai diskon member</span>--}}
{{--                                </label>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="table-responsive w-100">
                                <table class="table table-bordered table-striped table-vcenter" id="table_new_order" style="width: 1700px;">
                                    <thead>
                                    <tr>
                                        <th style="width: 6%;" class="text-center"></th>
                                        <th style="width: 20%;" class="text-center">Treatment/Item</th>
                                        <th style="width: 8%;" class="text-center">Qty</th>
                                        <th class="text-center" style="width: 16%">Therapist</th>
                                        <th class="text-center" style="width: 16%">Room</th>
                                        <th class="text-center" style="width: 6%">Durasi</th>
                                        <th class="text-center" style="width: 20%">Voucher</th>
                                        <th style="width: 15%;" class="text-center">Amount</th>
                                        <th style="width: 15%;" class="text-center">Discount</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="(item, index) in details" :key="item.uid">
                                        <td class="text-center">
                                            <button class="btn btn-danger" @click="removeCart(item)" v-if="!item.isFreeAddOn"><i class="fa fa-fw fa-minus-square"></i></button>
                                        </td>
                                        <td>
                                            <input type="text" :id = "'treatment' + item.id" class="form-control"
                                                    v-model="item.name" readonly>
                                        </td>
                                        <td>
                                            <template v-if="item.type == 'package'">
                                                <vue-autonumeric class="form-control" :value="item.room_type_id != 6 ? 1 : item.qty" :options="autonumericFormat" readonly>
                                                </vue-autonumeric>
                                            </template>
                                            <template v-else>
                                                <vue-autonumeric class="form-control" :value="item.qty" :options="autonumericFormat" readonly>
                                                </vue-autonumeric>
                                            </template>
                                        </td>
                                        <td>
                                            <template v-if="item.type == 'package' || item.need_partner">
                                                <select2 ref="partnerSelect" url="{{ route('admin.partner.select') }}" class="form-control" v-model="item.partner_id" :value="item.partner_id"
                                                         placeholder="Pilih terapis yang tersedia"  dropdown="#checkoutModal">
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
                                            <div class="input-group" v-if="item.type == 'package'">
                                                <input type="text" class="form-control" placeholder="Kode Voucher" v-model="item.voucher" :disabled="item.checking || item.discount > 0">
                                                <button class="btn btn-primary" type="button" v-if="item.checking"><i class="fa fa-fw fa-spin fa-circle-notch"></i></button>
                                                <template v-else>
                                                    <button class="btn btn-danger" type="button" v-if="item.discount > 0" @click="removeVoucher(item)">Remove</button>
                                                    <button class="btn btn-primary" type="button" v-else @click="checkVoucher(item)">Check</button>
                                                </template>
                                            </div>
                                        </td>
                                        <td>
                                            <vue-autonumeric class="form-control" :value="(item.price * item.qty)" :options="autonumericFormat" readonly>
                                            </vue-autonumeric>
                                        </td>
                                        <td>
                                            <vue-autonumeric class="form-control" :value="item.discount" :options="autonumericFormat" readonly>
                                            </vue-autonumeric>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="8" class="fw-bold text-end">Subtotal</td>
                                        <td class="fw-bold">@{{ subTotalPayment.toLocaleString('id-ID') }}</td>
                                    </tr>
{{--                                    <tr>--}}
{{--                                        <td colspan="6" class="fw-bold text-end">Discount (%)</td>--}}
{{--                                        <td class="fw-bold">@{{ parseInt(discountPercent).toLocaleString('id-ID') }}</td>--}}
{{--                                    </tr>--}}
{{--                                    <tr>--}}
{{--                                        <td colspan="6" class="fw-bold text-end">Discount</td>--}}
{{--                                        <td class="fw-bold">@{{ discountValue.toLocaleString('id-ID') }}</td>--}}
{{--                                    </tr>--}}
                                    <tr>
                                        <td colspan="8" class="fw-bold text-end">PB2 (10%)</td>
                                        <td class="fw-bold">@{{ pb2.toLocaleString('id-ID') }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="8" class="fw-bold text-end">Rounding Amount</td>
                                        <td class="fw-bold">
                                            <vue-autonumeric class="form-control" v-model="customAmount" :options="autonumericFormat"></vue-autonumeric>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="8" class="fw-bold text-end">Grand Total</td>
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
                             <select2 ref="paymentSelect" dropdown="#checkoutModal" id="paymentMethod" url="{{ route('admin.pos.select') }}" class="form-control" v-model="selectedPaymentMethod"
                                     placeholder="Pilih metode pembayaran" dropdown="checkoutModal">
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
