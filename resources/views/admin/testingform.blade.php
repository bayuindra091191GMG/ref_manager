@extends('layouts.testingform')

@section('title')
  <title>Testing Form</title>
@endsection

@section('content')
  <!-- Hero -->
  <div >
    <div class="content space-top">
      <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
        <div class="flex-grow-1">
          <h1 class="h3 fw-bold mb-2">
            Testing Form
          </h1>
        </div>
        {{-- <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
          <ol class="breadcrumb breadcrumb-alt">
            <li class="breadcrumb-item">
              <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
            </li>
            <li class="breadcrumb-item">
              <a class="link-fx" href="{{ route('admin.sales_order.index') }}">Daftar Sales Order</a>
            </li>
            <li class="breadcrumb-item" aria-current="page">
              Testing Form
            </li>
          </ol>
        </nav> --}}
      </div>
    </div>
  </div>
  <!-- END Hero -->
  <div class="content">
    <div id="vue-section" class="content">
      <div class="row items-push">
        <div class="col-md-12 col-xl-12">
          <div class="block block-rounded h-100 mb-0">
            <div class="block-content block-content-full p-4">
              <form action="#">
                <div class="row mb-3">
                  <label class="col-sm-4 col-form-label" for="fob">Customer
                    <span class="text-red">*</span></label>
                  <div class="col-sm-8">
                    <select class="form-select" id="customer" name="customer">
                      <option value="-1" selected disabled>Pilih Customer</option>
                      @foreach ($formattedCustomer as $item)
                        <option value="{{ $item['id'] }}">{{ $item['name'] }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>

                <div class="row mb-3">
                  <label class="col-sm-4 col-form-label" for="fob">Nama Penerima
                    <span class="text-red">*</span></label>
                  <div class="col-sm-8">
                    <input placeholder="Masukkan Nama" type="text" class="form-control" id="nama_penerima"
                      name="nama_penerima">
                  </div>
                </div>

                <div class="row mb-2">
                  <label class="col-sm-4 col-form-label" for="fob">Penanggung Pembayaran
                    <span class="text-red">*</span></label>
                  <div class="col-sm-8">
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" type="radio" name="penanggung_pembayaran"
                        id="penanggung_pembayaran1" value="pemasok">
                      <label class="form-check-label" for="penanggung_pembayaran1">Penjual</label>
                    </div>
                    <div class="form-check form-check-inline ml-3">
                      <input class="form-check-input" type="radio" name="penanggung_pembayaran"
                        id="penanggung_pembayaran2" value="pembeli">
                      <label class="form-check-label" for="penanggung_pembayaran2">Penerima</label>
                    </div>
                  </div>
                </div>

                <div class="row mb-3">
                  <label class="col-sm-4 col-form-label" for="fob">Alamat Tujuan
                    <span class="text-red">*</span></label>
                  <div class="col-sm-8">
                    <input placeholder="Masukkan Alamat Tujuan" type="text" class="form-control" id="address"
                      name="address">
                  </div>
                </div>

                <div class="row mb-3">
                  <label class="col-sm-4 col-form-label" for="fob">Alamat Penerima
                    <span class="text-red">*</span></label>
                  <div class="col-sm-8">
                    <input placeholder="Masukkan Alamat Penerima" type="text" class="form-control" id="alamat_penerima"
                      name="alamat_penerima">
                  </div>
                </div>

                <div class="row mb-3">
                  <label class="col-sm-4 col-form-label" for="fob">No HP Penerima
                    <span class="text-red">*</span></label>
                  <div class="col-sm-8">
                    <input placeholder="Masukkan No HP" type="number" class="form-control" id="no_hp_penerima"
                      name="no_hp_penerima">
                  </div>
                </div>
              </form>
              <div class="row">
                <div class="col-12">
                  <form id="form_create_so" action="#">
                    @csrf
                    <div class="row">
                      <div class="col-12 text-end">
                        <button id="btn_loading" type="button" class="btn btn-success" style="display: none;">
                          <i class="spinner-border spinner-border-sm text-green" role="status">
                            <span class="visually-hidden">Mohon Tunggu...</span>
                          </i>
                        </button>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-sm-12">
                        @include('partials._success')
                        @include('partials._error')
                      </div>
                    </div>
                    @if (count($errors))
                      <div class="row">
                        <div class="col-lg-8">
                          <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul>
                              @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                              @endforeach
                            </ul>
                          </div>
                        </div>
                      </div>
                    @endif

                    <h4 class="fw-normal">Detail Order</h4>
                    <div class="row">
                      <div class="col-sm-12">
                        <div class="table-responsive w-100">
                          <table class="table table-bordered table-vcenter" id="table_data">
                            <thead>
                              <tr>
                                <th style="width: 5%" class="text-center">
                                  <a class="btn btn-success" style="cursor:pointer;" v-on:click="addMoreItem">
                                    <i class="far fa-plus-square"></i>
                                  </a>
                                </th>
                                <th style="width: 30px" class="text-center">No</th>
                                <th style="width: 150px">Nama Produk</th>
                                <th style="width: 100px">Tipe</th>
                                <th style="width: 80px">Harga</th>
                                <th style="width: 80px">Qty</th>
                                <th style="width: 130px">Sub Total</th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr v-for="(item, index) in itemList" style="vertical-align: top !important;"
                                :key="item.id">
                                <td class="text-center">
                                  <a class="btn btn-danger" style="cursor: pointer" v-on:click="removeItem(item)">
                                    <i class="far fa-minus-square"></i>
                                  </a>
                                </td>
                                <td class="text-center"><span>@{{ index + 1 }}</span>
                                </td>
                                <td>
                                  <select v-model="item.itemId" :disabled='item.is_item_filled'
                                    class="form-select form-control-select2" name="itemIds[]" id="itmIds">
                                    <option value="-1" selected disabled>Pilih Produk</option>
                                    <option v-for="(itm, index) in itemProduk" :value="index">
                                      @{{ itm }}
                                    </option>
                                  </select>
                                </td>

                                <td>
                                  <select @change='showPrice(item, index)' v-model="item.itemType"
                                    class="form-select form-control-select2" name="itemIds[]" id="itemType">
                                    <option value="-1" selected disabled>Pilih Tipe</option>
                                    <option value="1">KG</option>
                                    <option value="2">Borongan</option>
                                    <option value="3">Testing</option>
                                  </select>
                                </td>

                                <td>
                                  <input :value="item.price" readonly type="number" name="prices[]" id="prices"
                                    class="form-control">
                                </td>

                                <td>
                                  <input v-model="item.qty" @change="calculateSubTotal(item, index)" type="number"
                                    name="qtys[]" id="qty_item" class="form-control">
                                </td>

                                <td>
                                  <input :value="item.subTotal" readonly type="number" name="subtotals[]"
                                    id="subtotals" class="form-control">
                                </td>

                              </tr>
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>



                    {{-- <div class="row">
                                          <label class="col-sm-2 col-form-label" for="status">Diskon 2</label>
                                          <div class="col-sm-2">
                                              <div class="input-group">
                                                  <vue-autonumeric :options="autonumericFormat" name="disc2_percent"
                                                      class="form-control" v-model="disc2"
                                                    ></vue-autonumeric>
                                                  <span class="input-group-text" id="basic-addon2">%</span>
                                              </div>
                                          </div>
                                          <div class="col-sm-6">
                                              <vue-autonumeric :options="autonumericFormat" name="disc2_value"
                                                  class="form-control" v-model="disc2Value"
                                                 readonly></vue-autonumeric>
                                          </div>
                                      </div> --}}

                    {{-- <div class="row">
                      <label class="col-sm-2 col-form-label" for="status">PPN </label>
                      <div class="col-sm-2">
                        <div class="input-group">
                          <vue-autonumeric :options="autonumericFormat" name="ppn_percent" class="form-control"
                            v-model="ppnPercent"></vue-autonumeric>
                          <span class="input-group-text" id="basic-addon2">%</span>
                        </div>
                      </div>
                      <div class="col-sm-6">
                        <vue-autonumeric :options="autonumericFormat" name="ppn_value" class="form-control"
                          v-model="ppnValue" readonly></vue-autonumeric>
                      </div>
                    </div> --}}

                    <div class="row">
                      <label class="col-sm-2 col-form-label" for="status">Grand Total</label>
                      <div class="col-sm-8">
                        <vue-autonumeric name="grandtotal" class="form-control" :value="grandTotal"
                          readonly></vue-autonumeric>
                      </div>
                    </div>

                    <div class="row justify-content-end">
                      <div class="col-sm-8 text-end">
                        <button type="submit" class="btn btn-success mt-4">Simpan</button>&nbsp;&nbsp;
                        <a href="#" class="btn btn-danger mt-4">Batal</a>
                      </div>
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
@endsection

@section('styles')
  <!-- Page JS Plugins CSS -->
  {{--    <link rel="stylesheet" href="{{ asset('custom/summernote/summernote-bs4.css') }}"> --}}
  <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
  <link rel="stylesheet" type="text/css"
    href="{{ asset('js/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}">
  <style>
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
      color: #000;
    }
  </style>
@endsection

@section('scripts')
  <!-- jQuery -->
  <script type="text/javascript" src="{{ asset('js/lib/jquery.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>
  {{--    <script type="text/javascript" src="{{ asset('custom/summernote/summernote-bs4.min.js') }}"></script> --}}
  <script type="text/javascript" src="{{ asset('js/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}">
  </script>

  <script src="{{ asset('js/axios.js') }}"></script>
  <script src="{{ asset('js/vue.js') }}"></script>
  <script src="{{ asset('js/AutoNumeric.js') }}"></script>
  <script src="{{ asset('js/vue-autonumeric.js') }}"></script>
  <script>
    let harga_a = [
      [10000, 20000, 30000],
      [9500, 19000, 28000],
      [8000, 15000, 26000]
    ];
    let harga_b = [
      [20000, 30000, 40000],
      [19000, 28000, 37000],
      [15000, 26000, 35000]
    ];
    let vueComponent = new Vue({
      el: "#vue-section",
      data: {
        ...{
          can_submit: 1,
          qty_error: false,
          branchName: '',
          isLoading: false,
          itemList: [],
          itemProduk: [],
          type: 'regular',
          customerAddress: '',
          subTotal: 0,
          ppnType: 'none',
          ppnPercent: 11,
          ppnValue: 0,
          disc1: 0,
          disc1Value: 0,
          disc2: 0,
          disc2Value: 0,
          grandTotal: 0,


          autonumericFormat: {
            minimumValue: '0',
            maximumValue: '9999999999999',
            digitGroupSeparator: '.',
            decimalCharacter: ',',
            decimalPlaces: 0,
            modifyValueOnWheel: false,
            unformatOnSubmit: true,
            allowDecimalPadding: false,
          },
        }
      },

      methods: {

        checkInput(index) {
          this.itemList[index].is_item_filled = true;
        },
        showPrice(item, index) {
          console.log(item, index);
          itemId = this.itemList[index].itemId;
          itemType = this.itemList[index].itemType - 1;

          if (index % 2 == 0) {
            this.itemList[index].price = harga_a[itemType][itemId];
          } else {
            this.itemList[index].price = harga_b[itemType][itemId];
          }

          this.itemList[index].is_item_filled = true;
          this.calculateSubTotal(item, index);
        },

        calculateSubTotal(item, index) {
          let tmp = this.itemList[index].qty * this.itemList[index].price;
          this.itemList[index].subTotal = tmp;
          this.calculateGrandTotal(tmp);
        },

        calculateGrandTotal(value) {
          this.grandTotal = this.grandTotal + value;
        },

        addMoreItem(item) {
          let itemIdx = this.itemList.length + 1;
          let nItem = {
            id: makeid(10),
            index: itemIdx,
            no: itemIdx++,
            itemId: -1,
            qty: 0,
            stock: 0,
            unit_id: 0,
            unit_name: '',
            subTotal: 0,
            price: 0,
            is_item_filled: false,
          };
          this.itemList.push(nItem);
        },
        removeItem(item) {
          this.itemList = this.itemList.filter(function(x) {
            return x !== item;
          });
        },

        getItem(item) {
          let item_a = ['Buku', 'Kertas', 'Tinta'];
          let item_b = ['PC', 'Kamera', 'Laptop'];
          let variant_harga = ['Satuan', 'Borongan', 'Paket'];

          if (item % 2 == 0) {
            this.itemProduk = item_a;
          } else {
            this.itemProduk = item_b;
          }
          // console.log(this.itemProduk);

        }

      }
    });
  </script>

  <script>
    $('#customer').on('change', function() {
      vueComponent.getItem($(this).val());
    });
  </script>
@endsection
