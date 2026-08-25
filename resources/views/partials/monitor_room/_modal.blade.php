<!-- Modal booking Multiple! -->
<div class="modal" id="modal-booking-multiple" role="dialog" aria-labelledby="modal-booking" aria-hidden="true">
    <div class="modal-dialog modal-dialog-popin modal-lg" role="document" style="max-width: 75vw;">
        <div class="modal-content">
            <div class="block block-rounded block-transparent mb-0">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Start Transaction</h3>
{{--                    <h3 class="block-title">@{{ activeRoom ? activeRoom.number : 'Select a Locker' }}</h3>--}}
                    <div class="block-options">
                        <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close" @click="clearData(activeRoom.status_id)">
                            <i class="fa fa-fw fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="block-content fs-sm space-y-3">
                    {{-- <div class="row">
                        <div class="col-sm-4">
                            <label>Room Number</label>
                        </div>
                        <div class="col-sm-8">
                            <input class="form-control" placeholder="Nomor Room" type="text" v-model="activeRoom.number" readonly>
                        </div>
                    </div> --}}
                    <div class="row">
                        <div class="col-sm-4">
                            <label>Booking Name</label>
                        </div>
                        <div class="col-sm-8">
                            <input class="form-control" placeholder="Nama Booking" type="text" v-model="bookingName" @focus="isDropdownVisible = true" @blur="hideDropdown">
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
                        <div class="col-sm-6">
                            <input class="form-control" placeholder="Nomor Telepon Member" type="text" v-model="bookingMemberPhone">
                            <span v-if="successSearchMember == false && messageSearchMember" class="form-text text-danger">@{{ messageSearchMember }}</span>
                            <span v-if="successSearchMember == true && messageSearchMember" class="form-text text-success">@{{ messageSearchMember }}</span>
                        </div>
                        <div class="col-sm-2 text-end">
                            <button type="button" class="btn btn-outline-info" @click="searchMember(bookingMemberPhone)">Search</button>
                        </div>
                    </div>
                    {{-- <div class="row">
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
                           <input type="checkbox" v-model="isFreeAddOn" class="mt-2"><label class="ms-2">Free Add On?</label>
                           <br>
                           <span v-if="messageAddOnTherapist" class="form-text text-danger mt-2">@{{ messageAddOnTherapist }}</span>
                        </div>
                    </div> --}}
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="table-responsive w-100">
                                <table class="table table-bordered table-striped table-vcenter" id="table_new_order">
                                    <thead>
                                        <tr>
                                            <th style="width: 5%;" class="text-center">
                                                <button type="button" class="btn btn-success" @click="addDetailOrder"><i class="far fa-fw fa-plus-square"></i></button>
                                            </th>
                                            <th style="width: 15%;" class="text-center">Member Phone Number</th>
                                            <th style="width: 15%;" class="text-center">Room</th>
                                            <th style="width: 15%;" class="text-center">Treatment</th>
                                            <th style="width: 15%;" class="text-center">Therapist</th>
                                            <th style="width: 10%;" class="text-center">Request Therapist?</th>
                                            <th style="width: 10%;" class="text-center">Free Add On?</th>
                                            <th style="width: 15%;" class="text-center">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, index) in orderDetails">
                                            <td class="text-center">
                                                <button type="button" class="btn btn-danger" @click="deleteDetailOrder(item, 1)"><i class="far fa-fw fa-minus-square"></i></button>
                                            </td>
                                            <td>
                                                <input class="form-control" placeholder="Nomor Member" type="text" v-model="bookingName" @focus="isDropdownVisible = true" @blur="hideDropdown">
                                                <div v-if="isDropdownVisible && existingCustomers.length > 0" class="dropdown-menu">
                                                    <div class="dropdown-section" v-for="customer in existingCustomers" :key="customer.customer_phone">
                                                        <div class="dropdown-item" @click="selectExistingCustomer(customer)">
                                                            <label>@{{ customer.customer_name }}</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <select :id = "'room' + item.id" class="form-control form-control-select2"
                                                    v-model="item.bookingRoom">
                                                </select>
                                            </td>
                                            <td>
                                                <select :id = "'treatment' + item.id" class="form-control form-control-select2"
                                                    v-model="item.bookingPackage">
                                                </select>
                                            </td>
                                            <td>
                                                <select :id = "'therapist' + item.id" class="form-control form-control-select2"
                                                    v-model="item.bookingTherapist">
                                                </select>
                                                <br>
                                                <span v-if="item.messageAddOnTherapist" class="form-text text-danger mt-2">@{{ item.messageAddOnTherapist }}</span>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" v-model="item.isRequest" class="mt-2">
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" v-model="item.isFreeAddOn" class="mt-1" :id="'free_addon_' + item.id">
                                            </td>
                                            <td>
                                                <vue-autonumeric class="form-control" :value="calculateGrandTotalItem(item)" :options="autonumericFormat" readonly>
                                                </vue-autonumeric>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-8">
                            <label class="fw-bold">Grand Total</label>
                        </div>
                        <div class="col-sm-4">
                            <vue-autonumeric class="form-control" v-model="grandTotal" :options="autonumericFormat" placeholder="0" readonly>
                            </vue-autonumeric>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-info" @click="startTreatment()">Start</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal booking! -->
<div class="modal" id="modal-booking" role="dialog" aria-labelledby="modal-booking" aria-hidden="true">
    <div class="modal-dialog modal-dialog-popin modal-lg" role="document">
        <div class="modal-content">
            <div class="block block-rounded block-transparent mb-0" v-if="activeRoom">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Start Room</h3>
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
                            <label>Room Number</label>
                        </div>
                        <div class="col-sm-8">
                            <input class="form-control" placeholder="Nomor Room" type="text" v-model="activeRoom.number" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4">
                            <label>Booking Name</label>
                        </div>
                        <div class="col-sm-8">
                            <input class="form-control" placeholder="Nama Booking" type="text" v-model="bookingName" @focus="isDropdownVisible = true" @blur="hideDropdown">
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
                        <div class="col-sm-6">
                            <input class="form-control" placeholder="Nomor Telepon Member" type="text" v-model="bookingMemberPhone">
                            <span v-if="successSearchMember == false && messageSearchMember" class="form-text text-danger">@{{ messageSearchMember }}</span>
                            <span v-if="successSearchMember == true && messageSearchMember" class="form-text text-success">@{{ messageSearchMember }}</span>
                        </div>
                        <div class="col-sm-2 text-end">
                            <button type="button" class="btn btn-outline-info" @click="searchMember(bookingMemberPhone)">Search</button>
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
{{--                            <input type="checkbox" v-model="isFreeAddOn" class="mt-2"><label class="ms-2">Free Add On?</label>--}}
{{--                            <br>--}}
{{--                            <span v-if="messageAddOnTherapist" class="form-text text-danger mt-2">@{{ messageAddOnTherapist }}</span>--}}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="table-responsive">
                                <table class="table table-striped" id="detail-table">
                                    <thead>
                                    <tr>
                                        <td style="width: 50px">
                                            <button class="btn btn-success" @click="addDetail"><i class="far fa-fw fa-plus-square"></i></button>
                                        </td>
                                        <td style="width: 300px;">Item</td>
                                        <td style="width: 100px">Qty</td>
                                        <td style="width: 100px">Stok</td>
                                        <td>Harga</td>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-if="details.length == 0">
                                        <td colspan="5" class="text-center">Tekan tombol (+) untuk menambahkan item</td>
                                    </tr>
                                    <tr v-for="(item, index) in details" :key="item.id">
                                        <td>
                                            <button class="btn btn-danger" @click="removeDetail(item)"><i class="far fa-fw fa-minus-square"></i></button>
                                        </td>
                                        <td>
                                            <select2 url="{{ route('admin.item.select') }}" placeholder="Pilih Item"
                                                     v-model="item.item_id" :value="item.item_id" class="form-select item" :data-index="index"></select2>
                                            <p class="text-danger fs-sm" v-if="item.initiated && item.is_unlimited == 0 && item.qty > item.stock">Stok tidak mencukupi!</p>
                                        </td>
                                        <td>
                                            <vue-autonumeric :options="autonumericFormat" class="form-control" v-model="item.qty"></vue-autonumeric>
                                        </td>
                                        <td>
                                            <vue-autonumeric :options="autonumericFormat" class="form-control" v-model="item.stock" readonly v-if="item.is_unlimited == 0"></vue-autonumeric>
                                            <input type="text" class="form-control" readonly value="&#8734;" v-else>
                                        </td>
                                        <td>
                                            <vue-autonumeric :options="autonumericFormat" class="form-control" v-model="item.price" readonly></vue-autonumeric>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-8">
                            <label class="fw-bold">Grand Total</label>
                        </div>
                        <div class="col-sm-4">
                            <vue-autonumeric class="form-control" v-model="grandTotal" :options="autonumericFormat" placeholder="0" readonly>
                            </vue-autonumeric>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-info" @click="startTreatment()">Start</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Booking -->
<div class="modal" id="modal-detail-booking" role="dialog" aria-labelledby="modal-detail-booking" aria-hidden="true">
    <div class="modal-dialog modal-dialog-popin modal-lg" role="document">
        <div class="modal-content">
            <div class="block block-rounded block-transparent mb-0" v-if="activeRoom">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Detail Room</h3>
                    <div class="block-options">
                        <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close" @click="clearData(activeRoom.status_id)">
                            <i class="fa fa-fw fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="block-content fs-sm space-y-3">
                    <div class="row">
                        <div class="col-sm-4">
                            <label>Room Number</label>
                        </div>
                        <div class="col-sm-8">
                            <input class="form-control" placeholder="Nomor Room" type="text" v-model="activeRoom.number" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4">
                            <label>Member/Customer Name</label>
                        </div>
                        <div class="col-sm-8">
                            <input class="form-control" type="text" v-model="detailRoom.member_name" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4">
                            <label>Therapist</label>
                        </div>
                        <div class="col-sm-8">
                            <input class="form-control" type="text" v-model="detailRoom.therapist_name" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4">
                            <label>Time</label>
                        </div>
                        <div class="col-sm-8">
                            <input class="form-control" type="text" v-model="detailRoom.time" readonly>
                        </div>
                    </div>

                    <!-- Navigation Tabs -->
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="transaction-tab" data-bs-toggle="tab" data-bs-target="#transaction"
                                    type="button" role="tab" aria-controls="transaction" aria-selected="true">
                                Transactions
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="add-on-tab" data-bs-toggle="tab" data-bs-target="#add-on"
                                    type="button" role="tab" aria-controls="add-on" aria-selected="false">
                                Add On
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="change-room-tab" data-bs-toggle="tab" data-bs-target="#change-room"
                                    type="button" role="tab" aria-controls="change-room" aria-selected="false">
                                Change Room
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="change-package-tab" data-bs-toggle="tab" data-bs-target="#change-package"
                                    type="button" role="tab" aria-controls="change-package" aria-selected="false">
                                Change Treatment
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="change-therapist-tab" data-bs-toggle="tab" data-bs-target="#change-therapist"
                                    type="button" role="tab" aria-controls="change-therapist" aria-selected="false">
                                Change Therapist
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="myTabContent">
                        <!-- Transaction Tab -->
                        <div class="tab-pane fade show active" id="transaction" role="tabpanel" aria-labelledby="transaction-tab">
                            <div class="table-responsive w-100">
                                <table class="table table-bordered table-striped table-vcenter" id="table_detail_room">
                                    <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Treatment/Item</th>
                                        <th>Description</th>
                                        <th>Qty</th>
                                        <th>Harga</th>
                                        <th>Diskon</th>
                                        <th>Subtotal</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="(item, index) in detailTransactions">
                                        <td class="text-center">@{{ index+1 }}</td>
                                        <td>@{{ item.package_name }}</td>
                                        <td>@{{ item.description }}</td>
                                        <td>@{{ item.qty.toLocaleString('id-ID') }}</td>
                                        <td>@{{ item.price.toLocaleString('id-ID') }}</td>
                                        <td>@{{ item.discount.toLocaleString('id-ID') }}</td>
                                        <td>@{{ item.subtotal.toLocaleString('id-ID') }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="fw-bold text-end">Grand Total</td>
                                        <td class="fw-bold">@{{ grandTotalTrx.toLocaleString('id-ID') }}</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-end">
                                <button type="button" class="btn btn-info" @click="paymentModal('open')">Payment</button>
                            </div>
                        </div>

                        <!-- Change Room Tab -->
                        <div class="tab-pane fade" id="change-room" role="tabpanel" aria-labelledby="change-room-tab">
                            <div class="row mb-3">
                                <div class="col-sm-4">
                                    <label>List Room Available</label>
                                </div>
                                <div class="col-sm-8">
                                    <select2 ref="roomSelect" url="{{ route('admin.room.select') }}" class="form-control" v-model="selectedRoomChange"
                                             placeholder="Pilih room yang masih available">
                                    </select2>
                                </div>
                            </div>
                            <div class="text-end">
                                <button type="button" class="btn btn-info" @click="modalConfirmationSwitchRoom.show()" :disabled="selectedRoomChange == ''">Change</button>
                            </div>
                        </div>

                        <!-- Add On Tab -->
                        <div class="tab-pane fade" id="add-on" role="tabpanel" aria-labelledby="add-on-tab">
                            <div class="row mb-3">
                                <div class="col">
                                    <div class="table-responsive">
                                        <table class="table table-striped" id="detail-table2">
                                            <thead>
                                            <tr>
                                                <td style="width: 50px">
                                                    <button class="btn btn-success" @click="addDetail"><i class="far fa-fw fa-plus-square"></i></button>
                                                </td>
                                                <td style="width: 300px;">Item</td>
                                                <td style="width: 100px">Qty</td>
                                                <td style="width: 100px">Stok</td>
                                                <td>Harga</td>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr v-if="details.length == 0">
                                                <td colspan="5" class="text-center">Tekan tombol (+) untuk menambahkan item</td>
                                            </tr>
                                            <tr v-for="(item, index) in details" :key="item.id">
                                                <td>
                                                    <button class="btn btn-danger" @click="removeDetail(item)"><i class="far fa-fw fa-minus-square"></i></button>
                                                </td>
                                                <td>
                                                    <select2 url="{{ route('admin.item.select') }}" placeholder="Pilih Item"
                                                             v-model="item.item_id" :value="item.item_id" class="form-select item" :data-index="index"></select2>
                                                    <p class="text-danger fs-sm" v-if="item.initiated && item.is_unlimited == 0 && item.qty > item.stock">Stok tidak mencukupi!</p>
                                                </td>
                                                <td>
                                                    <vue-autonumeric :options="autonumericFormat" class="form-control" v-model="item.qty"></vue-autonumeric>
                                                </td>
                                                <td>
                                                    <vue-autonumeric :options="autonumericFormat" class="form-control" v-model="item.stock" readonly v-if="item.is_unlimited == 0"></vue-autonumeric>
                                                    <input type="text" class="form-control" readonly value="&#8734;" v-else>
                                                </td>
                                                <td>
                                                    <vue-autonumeric :options="autonumericFormat" class="form-control" v-model="item.price" readonly></vue-autonumeric>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-8">
                                    <label class="fw-bold">Grand Total</label>
                                </div>
                                <div class="col-sm-4">
                                    <vue-autonumeric class="form-control" v-model="grandTotal" :options="autonumericFormat" placeholder="0" readonly>
                                    </vue-autonumeric>
                                </div>
                            </div>
                            <div class="text-end">
                                <button type="button" class="btn btn-info" @click="addAddOn" :disabled="details.length == 0">Add</button>
                            </div>
                        </div>

                        <!-- Change Treatment Tab -->
                        <div class="tab-pane fade" id="change-package" role="tabpanel" aria-labelledby="change-package-tab">
                            <div class="row mb-3">
                                <div class="col-sm-4">
                                    <label>Current Treatment</label>
                                </div>
                                <div class="col-sm-8" v-if="detailTransactions.length > 0">
                                    <input class="form-control" type="text" v-model="detailTransactions[0].package_name" readonly>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4">
                                    <label>Choose Treatment</label>
                                </div>
                                <div class="col-sm-8">
                                    <select2 ref="packageSelect" url="{{ route('admin.package.select') }}" class="form-control" v-model="selectedPackageChange"
                                             placeholder="Pilih paket yang tersedia">
                                    </select2>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4">
                                    <label class="fw-bold">Grand Total After Change</label>
                                </div>
                                <div class="col-sm-8">
                                    <vue-autonumeric class="form-control" v-model="grandTotalChangePackage" :options="autonumericFormat" placeholder="0" readonly>
                                    </vue-autonumeric>
                                </div>
                            </div>
                            <div class="text-end">
                                <button type="button" class="btn btn-info" @click="modalConfirmationSwitchPackage.show()" :disabled="selectedPackageChange == ''">Change</button>
                            </div>
                        </div>

                        <!-- Change Therapist Tab -->
                        <div class="tab-pane fade" id="change-therapist" role="tabpanel" aria-labelledby="change-therapist-tab">
{{--                            <div class="row mb-3">--}}
{{--                                <div class="col-sm-4">--}}
{{--                                    <label>Current Therapist</label>--}}
{{--                                </div>--}}
{{--                                <div class="col-sm-8" v-if="detailTransactions.length > 0">--}}
{{--                                    <input class="form-control" type="text" v-model="detailTransactions[0].package_name" readonly>--}}
{{--                                </div>--}}
{{--                            </div>--}}
                            <div class="row mb-3">
                                <div class="col-sm-4">
                                    <label>Choose Therapist</label>
                                </div>
                                <div class="col-sm-8">
                                    <select2 ref="partnerSelect" url="{{ route('admin.partner.select') }}" class="form-control" v-model="selectedPartnerChange"
                                             placeholder="Pilih terapis yang tersedia">
                                    </select2>
{{--                                    <input type="checkbox" v-model="isFreeAddOn" class="mt-2"><label class="ms-2">Free Add On?</label>--}}
                                    <br>
                                    <span v-if="messageAddOnTherapist" class="form-text text-danger mt-2">@{{ messageAddOnTherapist }}</span>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4">
                                    <label class="fw-bold">Grand Total After Change</label>
                                </div>
                                <div class="col-sm-8">
                                    <vue-autonumeric class="form-control" v-model="grandTotalChangePartner" :options="autonumericFormat" placeholder="0" readonly>
                                    </vue-autonumeric>
                                </div>
                            </div>
                            <div class="text-end">
                                <button type="button" class="btn btn-info" @click="modalConfirmationSwitchPartner.show()" :disabled="selectedPartnerChange == ''">Change</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Payment -->
<div class="modal" id="modal-payment" role="dialog" aria-labelledby="modal-payment" aria-hidden="true" tabindex="-1">
    <div class="modal-dialog modal-dialog-popin modal-xl" role="document">
        <div class="modal-content">
            <div class="block block-rounded block-transparent mb-0">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Pembayaran Transaksi</h3>
                    <div class="block-options">
                        <button type="button" class="btn-block-option" @click="paymentModal('close')">
                            <i class="fa fa-fw fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="block-content fs-sm space-y-3" v-if="activeRoom">
                    {{--                    <div v-if="transactionsPayment.">--}}
                    {{--                        <div class="row">--}}
                    {{--                            <div class="col-sm-4">--}}
                    {{--                                <label>Member/Customer Name</label>--}}
                    {{--                            </div>--}}
                    {{--                            <div class="col-sm-8">--}}
                    {{--                                <input class="form-control" type="text" v-model="detailRoom.member_name">--}}
                    {{--                            </div>--}}
                    {{--                        </div>--}}
                    {{--                        <div class="row">--}}
                    {{--                            <div class="col-sm-4">--}}
                    {{--                                <label>Member Phone Number</label>--}}
                    {{--                            </div>--}}
                    {{--                            <div class="col-sm-6">--}}
                    {{--                                <input class="form-control" placeholder="Nomor Telepon Member" type="text" v-model="detailRoom.member_phone">--}}
                    {{--                                <span v-if="successSearchMember == false && messageSearchMember" class="form-text text-danger">@{{ messageSearchMember }}</span>--}}
                    {{--                                <span v-if="successSearchMember == true && messageSearchMember" class="form-text text-success">@{{ messageSearchMember }}</span>--}}
                    {{--                            </div>--}}
                    {{--                            <div class="col-sm-2 text-end">--}}
                    {{--                                <button type="button" class="btn btn-outline-info" @click="searchMember(detailRoom.member_phone)">Search</button>--}}
                    {{--                            </div>--}}
                    {{--                        </div>--}}
                    {{--                    </div>--}}
                    <div class="row">
                        <div class="col-sm-2">
                            <label>Voucher Code</label>
                        </div>
                        <div class="col-sm-8">
                            <input class="form-control" placeholder="Masukkan kode voucher jika ada" type="text" v-model="voucherCode" :readonly="!isUseVoucherCode">
                            <span v-if="successCheckVoucher == false && messageCheckVoucher" class="form-text text-danger">@{{ messageCheckVoucher }}</span>
                            <span v-if="successCheckVoucher == true && messageCheckVoucher" class="form-text text-success">@{{ messageCheckVoucher }}</span>
                            <div class="form-check d-flex align-items-center mt-1">
                                <input class="form-check-input" type="checkbox" id="is_use_voucher_code" v-model="isUseVoucherCode">
                                <label class="form-check-label ms-1" for="is_use_voucher_code">
                                    Use Voucher?
                                </label>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <button type="button" class="btn btn-outline-info w-100" @click="checkVoucher()">Check</button>
                        </div>
                    </div>
                    <div class="table-responsive w-100">
                        <table class="table table-bordered table-striped table-vcenter" id="table_detail_room">
                            <thead>
                            <tr>
                                <th class="text-center" style="width: 5%">
                                    <a class="btn btn-success" style="cursor:pointer;"
                                       @click="addMoreRoom">
                                        <i class="fa fa-plus"></i>
                                    </a>
                                </th>
                                <th class="text-center" style="width: 5%">No</th>
                                <th class="text-center">Room</th>
                                <th class="text-center" style="width: 15%">Member Phone</th>
                                <th class="text-center" style="width: 15%">Member Name</th>
                                <th class="text-center">List Treatment/Item</th>
                                <th class="text-center" style="width: 15%">Harga</th>
                            </tr>
                            </thead>
                            <tbody>
                            <template v-for="(item, index) in transactionsPayment" :key="item.id">
                                <tr v-for="(detail, detailIndex) in item.details" :key="item.id">
                                    <td class="text-center" v-if="detailIndex === 0" :rowspan="item.details.length">
                                        <a class="btn btn-danger" style="cursor: pointer" @click="removeItem(item)" v-if="index !== 0">
                                            <i class="fa fa-minus"></i>
                                        </a>
                                    </td>
                                    <td class="text-center" v-if="detailIndex === 0" :rowspan="item.details.length">
                                        @{{ index + 1 }}
                                    </td>
                                    <td v-if="detailIndex === 0" :rowspan="item.details.length">
                                        <input type="hidden" v-model="item.no">
                                        <select2 url="{{ route('admin.room.select_active') }}" v-model="item.roomId" dropdown="#modal-payment"
                                                 name="roomIds[]" class="form-control" placeholder="Pilih room" :disabled="index == 0">
                                        </select2>
                                    </td>
                                    <td v-if="detailIndex === 0" :rowspan="item.details.length">
                                        <select2 url="{{ route('admin.user.select') }}" v-model="item.memberId" :value="item.memberId" dropdown="#modal-payment" :id="item.id"
                                                 name="memberIds[]" class="form-control" placeholder="Pilih nomor handphone" :data-index="index">
                                        </select2>
                                    </td>
                                    <td v-if="detailIndex === 0" :rowspan="item.details.length">
                                        <input type="text" v-model="item.memberName" name="memberNames[]" class="form-control"
                                               placeholder="Masukkan nama customer (jika tidak punya member)">
                                    </td>
                                    <td>@{{ detail.packageName }}</td>
                                    <td>
                                        <span v-if="detail.discount_membership > 0 && !isUseVoucherCode">
                                            <s>@{{ detail.subtotal.toLocaleString('id-ID') }}</s>
                                            @{{ (detail.subtotal - detail.discount_membership).toLocaleString('id-ID') }}
                                        </span>
                                        <span v-else>
                                            @{{ detail.subtotal.toLocaleString('id-ID') }}
                                        </span>
                                    </td>
                                </tr>
                            </template>
                            <tr>
                                <td colspan="6" class="fw-bold text-end">Subtotal</td>
                                <td class="fw-bold">@{{ subTotalPayment.toLocaleString('id-ID') }}</td>
                            </tr>
                            <tr>
                                <td colspan="6" class="fw-bold text-end">Discount (%)</td>
                                <td class="fw-bold">@{{ parseInt(discountPercent).toLocaleString('id-ID') }}</td>
                            </tr>
                            <tr>
                                <td colspan="6" class="fw-bold text-end">Discount</td>
                                <td class="fw-bold">@{{ discountValue.toLocaleString('id-ID') }}</td>
                            </tr>
                            <tr>
                                <td colspan="6" class="fw-bold text-end">Grand Total</td>
                                <td class="fw-bold">@{{ grandTotalPayment.toLocaleString('id-ID') }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <hr style="opacity: 1">
                    <div class="row">
                        <div class="col-sm-4">
                            <label class="mb-2">Amount Received</label>
                            <vue-autonumeric class="form-control" v-model="amountReceived" :options="autonumericFormat" placeholder="0" :readonly="selectedPaymentMethodText == '' || selectedPaymentMethodText !== 'CASH'">
                            </vue-autonumeric>
                        </div>
                        <div class="col-sm-4">
                            <label class="mb-2">Return</label>
                            <vue-autonumeric class="form-control" v-model="returnValue" :options="autonumericFormat" placeholder="0" readonly>
                            </vue-autonumeric>
                        </div>
                        <div class="col-sm-4">
                            <label class="mb-2">Payment Method</label>
                            <select2 ref="paymentSelect" url="{{ route('admin.pos.select') }}" class="form-control" v-model="selectedPaymentMethod"
                                     placeholder="Pilih metode pembayaran">
                            </select2>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-danger" @click="paymentModal('close')">Back</button>
                        <button type="button" class="btn btn-info ms-2" @click="payment()" :disabled="amountReceived < grandTotalPayment">Pay</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmation Switch Room -->
<div class="modal" id="modal-confirmation-switch-room" role="dialog" aria-labelledby="modal-confirmation-switch-room" aria-hidden="true">
    <div class="modal-dialog modal-dialog-popin modal-lg shadow-lg" role="document">
        <div class="modal-content shadow-lg">
            <div class="block block-rounded block-transparent mb-0">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Switch Room Confirmation</h3>
                    <div class="block-options">
                        <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fa fa-fw fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="block-content fs-sm space-y-3" v-if="activeRoom">
                    <div class="row">
                        <div class="col-sm-12">
                            <span>
                                Apakah anda yakin ingin mengubah room dari <strong>@{{ activeRoom.number }}</strong> ke <strong>@{{ selectedRoomText }}</strong>?
                            </span>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close">No</button>
                        <button type="button" class="btn btn-success ms-2" @click="switchRoom()">Yes</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmation Switch Treatment -->
<div class="modal" id="modal-confirmation-switch-package" role="dialog" aria-labelledby="modal-confirmation-switch-package" aria-hidden="true">
    <div class="modal-dialog modal-dialog-popin modal-lg shadow-lg" role="document">
        <div class="modal-content shadow-lg">
            <div class="block block-rounded block-transparent mb-0">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Switch Treatment Confirmation</h3>
                    <div class="block-options">
                        <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fa fa-fw fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="block-content fs-sm space-y-3" v-if="activeRoom && detailTransactions.length > 0">
                    <div class="row">
                        <div class="col-sm-12">
                            <span>
                                Apakah anda yakin ingin mengubah paket dari <strong>@{{ detailTransactions[0].package_name }}</strong> ke <strong>@{{ selectedPackageText }}</strong>?
                            </span>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close">No</button>
                        <button type="button" class="btn btn-success ms-2" @click="switchPackage()">Yes</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmation Switch Partner -->
<div class="modal" id="modal-confirmation-switch-partner" role="dialog" aria-labelledby="modal-confirmation-switch-partner" aria-hidden="true">
    <div class="modal-dialog modal-dialog-popin modal-lg shadow-lg" role="document">
        <div class="modal-content shadow-lg">
            <div class="block block-rounded block-transparent mb-0">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Switch Partner Confirmation</h3>
                    <div class="block-options">
                        <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fa fa-fw fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="block-content fs-sm space-y-3" v-if="activeRoom">
                    <div class="row">
                        <div class="col-sm-12">
                            <span>
                                Apakah anda yakin ingin mengubah terapis dari <strong>@{{ detailRoom.therapist_name }}</strong> ke <strong>@{{ selectedPartnerText }}</strong>?
                            </span>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close">No</button>
                        <button type="button" class="btn btn-success ms-2" @click="switchPartner()">Yes</button>
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

<!-- Modal booking! -->
{{--<div class="modal" id="modal-booking" role="dialog" aria-labelledby="modal-booking" aria-hidden="true">--}}
{{--    <div class="modal-dialog modal-dialog-popin modal-lg" role="document">--}}
{{--        <div class="modal-content">--}}
{{--            <div class="block block-rounded block-transparent mb-0" v-if="activeRoom">--}}
{{--                <div class="block-header block-header-default">--}}
{{--                    <h3 class="block-title">Start Room</h3>--}}
{{--                    --}}{{--                    <h3 class="block-title">@{{ activeRoom ? activeRoom.number : 'Select a Locker' }}</h3>--}}
{{--                    <div class="block-options">--}}
{{--                        <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close" @click="clearData(activeRoom.status_id)">--}}
{{--                            <i class="fa fa-fw fa-times"></i>--}}
{{--                        </button>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="block-content fs-sm space-y-3">--}}
{{--                    <div class="row">--}}
{{--                        <div class="col-sm-4">--}}
{{--                            <label>Room Number</label>--}}
{{--                        </div>--}}
{{--                        <div class="col-sm-8">--}}
{{--                            <input class="form-control" placeholder="Nomor Room" type="text" v-model="activeRoom.number" readonly>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="row">--}}
{{--                        <div class="col-sm-4">--}}
{{--                            <label>Booking Name</label>--}}
{{--                        </div>--}}
{{--                        <div class="col-sm-8">--}}
{{--                            <input class="form-control" placeholder="Nama Booking" type="text" v-model="bookingName" @focus="isDropdownVisible = true" @blur="hideDropdown">--}}
{{--                            <div v-if="isDropdownVisible && existingCustomers.length > 0" class="dropdown-menu">--}}
{{--                                <div class="dropdown-section" v-for="customer in existingCustomers" :key="customer.customer_phone">--}}
{{--                                    <div class="dropdown-item" @click="selectExistingCustomer(customer)">--}}
{{--                                        <label>@{{ customer.customer_name }}</label>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="row">--}}
{{--                        <div class="col-sm-4">--}}
{{--                            <label>Member Phone Number</label>--}}
{{--                        </div>--}}
{{--                        <div class="col-sm-6">--}}
{{--                            <input class="form-control" placeholder="Nomor Telepon Member" type="text" v-model="bookingMemberPhone">--}}
{{--                            <span v-if="successSearchMember == false && messageSearchMember" class="form-text text-danger">@{{ messageSearchMember }}</span>--}}
{{--                            <span v-if="successSearchMember == true && messageSearchMember" class="form-text text-success">@{{ messageSearchMember }}</span>--}}
{{--                        </div>--}}
{{--                        <div class="col-sm-2 text-end">--}}
{{--                            <button type="button" class="btn btn-outline-info" @click="searchMember(bookingMemberPhone)">Search</button>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="row">--}}
{{--                        <div class="col-sm-4">--}}
{{--                            <label>Treatment</label>--}}
{{--                        </div>--}}
{{--                        <div class="col-sm-8">--}}
{{--                            <select2 url="{{ route('admin.package.select') }}" class="form-control" v-model="bookingPackage"--}}
{{--                                     placeholder="Pilih Treatment">--}}
{{--                            </select2>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="row">--}}
{{--                        <div class="col-sm-4">--}}
{{--                            <label>Therapist</label>--}}
{{--                        </div>--}}
{{--                        <div class="col-sm-8">--}}
{{--                            <select2 url="{{ route('admin.partner.select') }}" class="form-control" v-model="bookingTherapist"--}}
{{--                                     placeholder="Pilih Therapist">--}}
{{--                            </select2>--}}
{{--                            <input type="checkbox" v-model="isFreeAddOn" class="mt-2"><label class="ms-2">Free Add On?</label>--}}
{{--                            <br>--}}
{{--                            <span v-if="messageAddOnTherapist" class="form-text text-danger mt-2">@{{ messageAddOnTherapist }}</span>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="row">--}}
{{--                        <div class="col-sm-4">--}}
{{--                            <label class="fw-bold">Grand Total</label>--}}
{{--                        </div>--}}
{{--                        <div class="col-sm-8">--}}
{{--                            <vue-autonumeric class="form-control" v-model="grandTotal" :options="autonumericFormat" placeholder="0" readonly>--}}
{{--                            </vue-autonumeric>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="text-end">--}}
{{--                        <button type="button" class="btn btn-info" @click="startTreatment()">Start</button>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--</div>--}}

