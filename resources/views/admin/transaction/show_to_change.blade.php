@extends('layouts.admin')

@section('title')
    <title>Ubah Pembayaran Transaksi</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Ubah Pembayaran Transaksi
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.transaction.index') }}">Daftar Transaksi</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Ubah Pembayaran Transaksi
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->
    <div id="app" class="content">
        <div class="row items-push">
            <div class="col-md-12 col-xl-12">
                <div class="block block-rounded h-100 mb-0">
                    <div class="block-content block-content-full">
                        <div class="row">
                            <div class="col-12">
                                <div class="row">
                                    <div class="col-sm-12">
                                        @include('partials._success')
                                        @include('partials._error')
                                    </div>
                                </div>
                                @if(count($errors))
                                    <div class="row">
                                        <div class="col-lg-8">
                                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                <ul>
                                                    @foreach($errors->all() as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <div class="block-content">
                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label" for="code">Status</label>
                                        <div class="col-sm-6">
                                            <input type="text" id="status" class="form-control" readonly value="{{ $header['status'] }}"/>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label" for="code">Code</label>
                                        <div class="col-sm-6">
                                            <input type="text" id="code" class="form-control" readonly value="{{ $header['code'] }}"/>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label" for="locker">Nomor Locker</label>
                                        <div class="col-sm-6">
                                            <input type="text" id="locker" class="form-control" readonly value="{{ $header['locker'] }}"/>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label" for="gro">Gro</label>
                                        <div class="col-sm-6">
                                            <input type="text" id="gro" class="form-control" readonly value="{{ $header['gro'] }}"/>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label" for="room">Room</label>
                                        <div class="col-sm-6">
                                            <input type="text" id="room" class="form-control" readonly value="{{ $room }}"/>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label" for="amount">Amount</label>
                                        <div class="col-sm-6">
                                            <input type="text" id="amount" class="form-control" readonly value="{{ $header['amount_str'] }}"/>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label" for="return">Kembalian</label>
                                        <div class="col-sm-6">
                                            <input type="text" id="return" class="form-control" readonly value="{{ $header['return_str'] }}"/>
                                        </div>
                                    </div>
                                    @if($member['isMember'])
                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label" for="approved_by">Member Data</label>
                                        <div class="col-sm-4">
                                            <ul>
                                                <li>Kode: {{ $member['code'] }}</li>
                                                <li>Nama: {{ $member['name'] }}</li>
                                                <li>Phone: {{ $member['phone'] ?? '-' }}</li>
                                            </ul>
                                        </div>
                                    </div>
                                    @endif
                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label" for="return">Detil Transaksi
                                            @if($header['is_happy_hour'])
                                                <br/><label style="color: red;">Happy Hour</label>
                                            @endif
                                            @if($header['foc'] != null)
                                                @if($header['foc'] != 'no' && $header['foc'] != 'NO')
                                                    <br/><label style="color: red; font-size: 20px;">FOC</label>
                                                @endif
                                            @endif
                                        </label>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <table class="table table-bordered table-striped table-vcenter">
                                                <thead>
                                                <tr>
                                                    <td>No</td>
                                                    <td>Nama Paket/Item</td>
                                                    <td>Qty</td>
                                                    <td>Harga</td>
                                                    <td>Diskon</td>
                                                    <td>Subtotal</td>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @php($no = 1)
                                                @foreach($details as $detail)
                                                    <tr>
                                                        <td>{{ $no }}</td>
                                                        <td>
                                                            @if($detail->package_id != null)
                                                                {{ $detail->package->name }}<br/>
                                                                Room: {{ $detail->room->number }}<br/>
                                                                Therapis: &nbsp;
                                                                @foreach ($partners as $partner)
                                                                    {{ $partner['number'] }},
                                                                @endforeach
                                                                <br/>
                                                                @php($diff = 0)
                                                                @php($startDate = \Carbon\Carbon::parse($detail->start_time))
                                                                @php($endDate = \Carbon\Carbon::parse($detail->end_time))
                                                                @php($diff = $endDate->diffInMinutes($startDate))
                                                                Durasi: ({{ $diff }} menit) {{ $startDate->format('d F Y h:i:s') }} - {{ $endDate->format('d F Y h:i:s') }}
                                                            @elseif($detail->item_id != null)
                                                                {{ $detail->item->name }}
                                                            @elseif($detail->is_facility_only)
                                                                Hanya Fasilitas
                                                            @elseif($detail->membership_id != null)
                                                                {{ $detail->membership->code }}
                                                            @endif
                                                        </td>
                                                        <td>{{ $detail->qty }}</td>
                                                        <td class="text-end">{{ number_format($detail->price, 2, ',', '.') }}</td>
                                                        <td class="text-end">{{ number_format($detail->discount_amount, 2, ',', '.') }}</td>
                                                        <td class="text-end">{{ number_format($detail->subtotal - $detail->additional_person_amount - $detail->extension_amount, 2, ',', '.') }}</td>
                                                    </tr>
                                                    @if($detail->extension_times > 0)
                                                    @php($no++)
                                                    <tr>
                                                        <td>{{ $no }}</td>
                                                        <td>
                                                            Extend
                                                        </td>
                                                        <td>{{ $detail->extension_times }}</td>
                                                        <td class="text-end">{{ number_format($detail->extension_amount, 2, ',', '.') }}</td>
                                                        <td class="text-end">{{ number_format(0, 2, ',', '.') }}</td>
                                                        <td class="text-end">{{ number_format($detail->extension_amount, 2, ',', '.') }}</td>
                                                    </tr>
                                                    @endif
                                                    @if($detail->additional_person_qty > 0)
                                                    @php($no++)
                                                    <tr>
                                                        <td>{{ $no }}</td>
                                                        <td>
                                                            Tambahan Customer
                                                        </td>
                                                        <td>{{ $detail->additional_person_qty }}</td>
                                                        <td class="text-end">{{ number_format($detail->additional_person_amount, 2, ',', '.') }}</td>
                                                        <td class="text-end">{{ number_format(0, 2, ',', '.') }}</td>
                                                        <td class="text-end">{{ number_format($detail->additional_person_amount, 2, ',', '.') }}</td>
                                                    </tr>
                                                    @endif
                                                    @if($detail->additional_partner_qty > 0)
                                                    @php($no++)
                                                    <tr>
                                                        <td>{{ $no }}</td>
                                                        <td>
                                                            Tambahan Therapis
                                                        </td>
                                                        <td>{{ $detail->additional_partner_qty }}</td>
                                                        <td class="text-end">{{ number_format($detail->additional_partner_amount/$detail->additional_partner_qty, 2, ',', '.') }}</td>
                                                        <td class="text-end">{{ number_format(0, 2, ',', '.') }}</td>
                                                        <td class="text-end">{{ number_format($detail->additional_partner_amount, 2, ',', '.') }}</td>
                                                    </tr>
                                                    @endif
                                                    @php($no++)
                                                @endforeach
                                                @if($branch->is_oura === 0)
                                                        <tr>
                                                            <td colspan="5" class="text-end"><b>Subtotal</b></td>
                                                            <td class="text-end"><b>{{ $header['total_str'] }}</b></td>
                                                        </tr>
                                                    @endif

                                                    @if($branch->is_oura === 1)
                                                        @if($homeServiceFee > 0)
                                                            <tr>
                                                                <td colspan="5" class="text-end"><b>Home Service Fee</b></td>
                                                                <td class="text-end"><b>{{ number_format($homeServiceFee, 2, ',', '.') }}</b></td>
                                                            </tr>
                                                        @endif
                                                        @if($pointUsed > 0)
                                                            <tr>
                                                                <td colspan="5" class="text-end"><b>Penggunaan Poin</b></td>
                                                                <td class="text-end"><b>-{{ number_format($pointUsed, 2, ',', '.') }}</b></td>
                                                            </tr>
                                                        @endif
                                                    @endif
                                                    <tr>
                                                        <td colspan="5" class="text-end"><b>Discount</b></td>
                                                        <td class="text-end"><b>-{{ $header['discount_str'] }}</b></td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="5" class="text-end"><b>Total</b></td>
                                                        @if($header['foc'] != null && strtolower($header['foc']) != 'no')
                                                            <td class="text-end"><b>0</b></td>
                                                        @else
                                                            <td class="text-end"><b>{{ $header['amount_str'] }}</b></td>
                                                        @endif
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <form action="{{ route('admin.transaction.change_payment') }}" method="post">
                                        @csrf
                                        <div class="row mb-3">
                                            <h3 class="col-sm-3 col-form-label" for="return">Detil Pembayaran</label>
                                            <input type="hidden" name="id" value="{{ $header['id'] }}">
                                        </div>
                                        <div class="row">
                                            <div class="col-2">
                                                <label>Password</label>
                                            </div>
                                            <div class="col-6">
                                                <input type="password" name="password" class="form-control"/>
                                            </div>
                                        </div>
                                        <div class="row mt-2 mb-3">
                                            <div class="col-2">

                                            </div>
                                            <div class="col-6">
                                                <button type="submit" class="btn btn-danger" :disabled="!canSubmit">Ubah Pembayaran</button>
                                            </div>
                                        </div>
                                        <div class="row mb-3" v-if="canSubmit == false">
                                            <h4 style="color:red;">Wrong Amount!</h4>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-2 mb-2">
                                                <button type="button" class="btn btn-success" v-on:click="addMoreItem()">
                                                    Add
                                                </button>
                                            </div>
                                            <div class="col-12">
                                                <table class="table table-bordered table-striped table-vcenter">
                                                    <thead>
                                                    <tr>
                                                        <td>No</td>
                                                        <td>Old Payment Type</td>
                                                        <td>New Payment Type</td>
                                                        <td>Old Amount</td>
                                                        <td>New Amount</td>
                                                        <td>Opsi</td>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr v-for="(item, index) in payments">
                                                            <td>@{{ (index + 1) }}</td>
                                                            <td>@{{ item.payment_type_code }}</td>
                                                            <td>
                                                                <select class="form-select" name="options[]">
                                                                    <option v-for="(paymentOption, index) in paymentOptions" :value="paymentOption.id" :selected="paymentOption.id == item.payment_type_id">@{{ paymentOption.code }} - @{{ paymentOption.description }}</option>
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <vue-autonumeric :options="autonumericFormat" class="form-control" v-model="item.amount" readonly></vue-autonumeric>
                                                            </td>
                                                            <td>
                                                                <vue-autonumeric :options="autonumericFormat" name="amounts[]" class="form-control" v-model="item.new_amount" v-on:input="checkOnTotal"></vue-autonumeric>
                                                            </td>
                                                            <td v-if="index > 0">
                                                                <a class="btn btn-danger" style="cursor: pointer" v-on:click="removeItem(item)">
                                                                    <i class="far fa-minus-square"></i>
                                                                </a>
                                                            </td>
                                                            <td v-else></td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="4" class="text-end"><b>Total</b></td>
                                                            <td colspan="2" class="text-start"><b><vue-autonumeric :options="autonumericFormat" class="form-control" :value="totalAmount" readonly></vue-autonumeric></b></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
@endsection

@section('scripts')
    <!-- jQuery -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script src="{{ asset('js/vue.js') }}"></script>
    <script src="{{ asset('js/axios.js') }}"></script>
    <script src="{{ asset('js/AutoNumeric.js') }}"></script>
    <script src="{{ asset('js/vue-autonumeric.js') }}"></script>
    <script>
        let app = new Vue({
            el: '#app',
            data: {
                payments: @json($payments),
                paymentOptions: @json($paymentOptions),
                allTotal: {{ $header['amount'] }},
                canSubmit: true,
                autonumericFormat: {
                    minimumValue: '-9999999999',
                    maximumValue: '9999999999',
                    digitGroupSeparator: '.',
                    decimalCharacter: ',',
                    decimalPlaces: 2,
                    modifyValueOnWheel: false,
                    allowDecimalPadding: false,
                    unformatOnSubmit: true,
                },
            },
            computed: {
                totalAmount: function(){
                    let total = 0;
                    for(let i=0; i<this.payments.length; i++){
                        total += this.payments[i].new_amount;
                    }

                    return total;
                },
            },
            methods:{
                addMoreItem(item) {
                    let itemIdx = this.payments.length + 1;
                    let nItem = {
                        payment_type_id: 1,
                        payment_type_code: 'New Payment',
                        amount: 0,
                        new_amount: 0,
                    };
                    this.payments.push(nItem);
                },
                removeItem(item) {
                    this.payments = this.payments.filter(function (x) { return x !== item; });
                },
                checkOnTotal(){
                    if(this.totalAmount == this.allTotal){
                        this.canSubmit = true;
                    }
                    else{
                        this.canSubmit = false;
                    }
                }
            },
        });
    </script>
@endsection
