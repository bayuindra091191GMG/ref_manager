@extends('layouts.admin')

@section('title')
    <title>Detail Terapis</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Detil Terapis {{ $partner->name }}
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.partner.index') }}">Daftar Terapis</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Detil Terapis
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <div class="content">
        <div class="row mb-3">
            <div class="col-sm-4 col-12">
                <a href="{{ route('admin.partner.index') }}" class="btn btn-dark">
                    <i class="fa fa-arrow-left"></i>
                </a>
            </div>
            <div class="col-sm-8 col-12 text-end">
                @if(!$partner->contract)
                    <a href="{{ route('admin.partner.contract.create', ['id' => $partner->id]) }}" class="btn btn-success">Buat Kontrak</a>
                @endif
                <a href="{{ route('admin.partner.edit', ['id' => $partner->id]) }}" class="btn btn-primary">Ubah</a>
            </div>
        </div>
        <div class="block block-rounded">
            <ul class="nav nav-tabs nav-tabs-alt" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="btabs-alt-static-home-tab" data-bs-toggle="tab" data-bs-target="#btabs-alt-static-home" role="tab" aria-controls="btabs-alt-static-home" aria-selected="true">Detail</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="btabs-alt-static-profile-tab" data-bs-toggle="tab" data-bs-target="#btabs-alt-static-profile" role="tab" aria-controls="btabs-alt-static-profile" aria-selected="false">Transaksi</button>
                </li>
                @if($partner->contract)
                    <li class="nav-item">
                        <button class="nav-link" id="btabs-alt-static-contract-tab" data-bs-toggle="tab" data-bs-target="#btabs-alt-static-contract" role="tab" aria-controls="btabs-alt-static-contract" aria-selected="false">Kontrak</button>
                    </li>
                @endif
            </ul>
            <div class="block-content tab-content">
                <div class="tab-pane active" id="btabs-alt-static-home" role="tabpanel" aria-labelledby="btabs-alt-static-home-tab">
                    <div class="block-content block-content-full">
                        <div class="row">
                            <div class="col-sm-6">
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
                        <div class="row">
                            <div class="col-12">
                                <div class="space-y-3">
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="code">Kode Karyawan</label>
                                        <div class="col-sm-4">
                                            <input id="code" type="text" class="form-control" style="text-transform: uppercase;"
                                                   name="code" value="{{ $partner->code}}" readonly />
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="number">No. KTP <span class="text-red">*</span></label>
                                        <div class="col-sm-4">
                                            <input id="number" type="text" class="form-control"
                                                   name="number" value="{{ $partner->ktp }}" readonly />
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="name">Nama <span class="text-red">*</span></label>
                                        <div class="col-sm-4">
                                            <input id="name" type="text" class="form-control"
                                                   name="name" value="{{ $partner->name }}" readonly />
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="alias">Alias <span class="text-red">*</span></label>
                                        <div class="col-sm-4">
                                            <input id="alias" type="text" class="form-control"
                                                   name="alias" value="{{ $partner->alias }}" readonly />
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="level">Jenis Kelamin</label>
                                        <div class="col-sm-4">
                                            <select id="gender" name="gender" class="form-select" disabled>
                                                <option value="P" @if($partner->gender == 'P') selected @endif>Perempuan</option>
                                                <option value="L" @if($partner->gender == 'L') selected @endif>Laki - laki</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="phone">Nomor Kontak</label>
                                        <div class="col-sm-4">
                                            <input id="phone" type="text" class="form-control"
                                                   name="phone" value="{{ $partner->phone ?? '' }}" readonly />
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="phone">Alamat</label>
                                        <div class="col-sm-4">
                                            <textarea name="address" id="address" rows="10" class="form-control" placeholder="Alamat Domisili" readonly>{{ $partner->address }}</textarea>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="alias">Spesialis skill
                                            <span class="text-red">*</span></label>
                                        <div class="col-sm-4">
                                            <input id="specialty" type="text" class="form-control"
                                                   name="specialty" value="{{ $partner->specialty }}" readonly/>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="level">Jenis Terapis</label>
                                        <div class="col-sm-4">
                                            <input id="level" type="text" class="form-control"
                                                   name="level" value="{{ $partner->partner_level->name }}" readonly />
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="join_date">Tanggal Bergabung</label>
                                        <div class="col-sm-4">
                                            <input id="join_date" type="text" class="form-control"
                                                   name="join_date" value="{{ \Carbon\Carbon::parse($partner->join_date)->format('d-m-Y') }}" readonly />
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="is_present">Apakah Hadir saat ini?</label>
                                        <div class="col-sm-4">
                                            <select id="is_present" name="is_present" class="form-select" disabled >
                                                <option value="0" @if($partner->is_present === 0) selected @endif>Tidak Hadir</option>
                                                <option value="1" @if($partner->is_present === 1) selected @endif>Hadir</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="status">Status</label>
                                        <div class="col-sm-4">
                                            <select id="status" name="status" class="form-select" disabled >
                                                <option value="5" @if($partner->status_id === 5) selected @endif>Available</option>
                                                <option value="6" @if($partner->status_id === 6) selected @endif>Sedang Bekerja</option>
                                                <option value="11" @if($partner->status_id === 11) selected @endif>Dibooking</option>
                                                <option value="12" @if($partner->status_id === 12) selected @endif>Istirahat</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="employement_status">Status Kepegawaian?</label>
                                        <div class="col-sm-4">
                                            <select id="employement_status" name="employement_status" class="form-select" disabled>
                                                <option value="Aktif" @if($partner->employement_status === 'Aktif') selected @endif>Aktif</option>
                                                <option value="Cuti" @if($partner->employement_status === 'Cuti') selected @endif>Cuti</option>
                                                <option value="Resign" @if($partner->employement_status === 'Resign') selected @endif>Resign</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-12 col-form-label" for="price">Uang Kehadiran</label>
                                        <div class="col-sm-4 col-12">
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input id="basic_salary_amount" type="text" class="form-control"
                                                        name="basic_salary_amount" value="{{ number_format($partner->basic_salary_amount, 0, ',', '.') }}" readonly />
                                            </div>

                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="attend_machine_id">Nomor Urut Mesin Absen</label>
                                        <div class="col-sm-4">
                                            <input id="attend_machine_id" type="text" class="form-control"
                                                name="attend_machine_id" value="{{ $partner->attend_machine_id ?? '' }}" readonly/>
                                        </div>
                                    </div>
                                    @if($partner->branch_id == 1)
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label" for="commission_percentage">Persentase Komisi Manual</label>
                                            <div class="col-sm-4">
                                                <div class="input-group">
                                                    <input id="commission_percentage" type="text" class="form-control"
                                                           name="commission_percentage" value="{{ $partner->commission_percentage }}" readonly />
                                                    <span class="input-group-text">%</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    @if(!empty($partner->photo_filename))
                                        <div class="row">
                                            <label class="col-sm-3 col-12 col-form-label" for="image">Foto Sekarang</label>
                                            <div class="col-sm-8 col-12">
                                                <img src="{{ asset('storage/images/partners/'.$partner->photo_filename) }}" style="width: 250px; height: auto;">
                                            </div>
                                        </div>
                                    @endif
                                    <hr>

                                    <h4 class="mt-4">Informasi Rekening</h4>

                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="phone">Nomor Rekening</label>
                                        <div class="col-sm-4">
                                            <input id="account_number" type="text" class="form-control" placeholder="Nomor Rekening"
                                                   name="account_number" value="{{ $partner->account_number }}" readonly />
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="phone">Nama Bank</label>
                                        <div class="col-sm-4">
                                            <input id="account_bank_name" type="text" class="form-control" placeholder="Nama Rekening"
                                                   name="account_bank_name" value="{{ $partner->account_bank_name }}" readonly />
                                        </div>
                                    </div>
                                    <hr>

                                    <h4 class="mt-4">Kontak Emergency</h4>

                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="phone">Nama Emergency</label>
                                        <div class="col-sm-4">
                                            <input id="emergency_name" type="text" class="form-control" placeholder="Nama Emergency"
                                                   name="emergency_name" value="{{ $partner->emergency_name }}" readonly />
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label" for="phone">Nomor Kontak Emergency</label>
                                        <div class="col-sm-4">
                                            <input id="emergency_phone" type="text" class="form-control" placeholder="Nomor Kontak Emergency"
                                                   name="emergency_phone" value="{{ $partner->emergency_phone }}" readonly />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="btabs-alt-static-profile" role="tabpanel" aria-labelledby="btabs-alt-static-profile-tab">
                    <h4 class="fw-normal">Data Transaksi</h4>
                    @if(count($transactions) == 0)
                        <tr>
                            <td colspan="7">Belum ada transaksi...</td>
                        </tr>
                    @endif
                    @foreach($transactions as $trx)
                        <p>{{ $trx['date'] }} - {{ count($trx['details']) }} Transaksi</p>
                        <table class="table table-responsive table-striped">
                            <tr>
                                <th>No</th>
                                <th>Code</th>
                                <th>Nama Paket</th>
                                <th>Waktu Mulai</th>
                                <th>Waktu Selesai</th>
                                <th>Customer</th>
                                <th>Amount</th>
                            </tr>
                            @php($total = 0)
                            @foreach($trx['details'] as $dt)
                                <tr>
                                    <td>{{ $dt['no'] }}</td>
                                    <td>{{ $dt['code'] }}</td>
                                    <td>{{ $dt['name'] }}</td>
                                    <td>{{ $dt['start_time'] }}</td>
                                    <td>{{ $dt['end_time'] }}</td>
                                    <td>{{ $dt['customer'] }}</td>
                                    <td>{{ number_format($dt['amount'], 0, ',', '.') }}</td>
                                </tr>
                                @php($total += $dt['amount'])
                            @endforeach
                            <tr>
                                <td colspan="6" class="text-end fw-bold">Total</td>
                                <td class="fw-bold">{{ number_format($total, 0, ',', '.') }}</td>
                            </tr>
                        </table>
                    @endforeach
                </div>
                @if($partner->contract)
                    <div class="tab-pane" id="btabs-alt-static-contract" role="tabpanel" aria-labelledby="btabs-alt-static-contract-tab">
                        <div class="d-flex flex-row align-items-center justify-content-between">
                            <h4 class="fw-normal">Kontrak</h4>
                            <div class="d-flex flex-row">
                                <button type="button" id="deleteContract" class="btn btn-danger me-2">Hapus</button>
                                <a href="{{ route('admin.partner.contract.edit', ['id' => $partner->id]) }}" class="btn btn-primary">Ubah Kontrak</a>
                            </div>
                        </div>

                        <div class="block-content block-content-full">
                            <div class="row">
                                <div class="col-sm-6">
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
                            <div class="row">
                                <div class="col-12">
                                    <div class="space-y-3">
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label">Nomor Kontrak</label>
                                            <div class="col-sm-3">
                                                <input id="code" type="text" class="form-control" style="text-transform: uppercase;"
                                                       name="code" value="{{ $partner->contract->code}}" readonly />
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label" for="gender">Jangka Waktu</label>
                                            <div class="col-sm-2">
                                                <div class="input-group">
                                                    <input id="duration" type="text" class="form-control numeric"
                                                           name="duration" value="{{ $partner->contract->duration }}" readonly/>
                                                    <span class="input-group-text">Bulan</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label" for="join_date">Tanggal Mulai <span class="text-red">*</span></label>
                                            <div class="col-sm-3">
                                                <input id="start_date" type="text" class="form-control js-flatpickr" data-date-format="d-m-Y"
                                                       name="start_date" value="{{ $partner->contract->start_date->format('d-m-Y') }}" readonly/>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label" for="join_date">Tanggal Berakhir <span class="text-red">*</span></label>
                                            <div class="col-sm-3">
                                                <input id="end_date" type="text" class="form-control js-flatpickr" data-date-format="d-m-Y"
                                                       name="end_date" value="{{ $partner->contract->end_date->format('d-m-Y') }}" readonly/>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label" for="name">Nama</label>
                                            <div class="col-sm-4">
                                                <input id="name" type="text" class="form-control"
                                                       name="name" value="{{ $partner->contract->name}}" readonly />
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label" for="marital_status">Status</label>
                                            <div class="col-sm-3">
                                                <input id="marital_status" type="text" class="form-control"
                                                       name="marital_status" value="{{ $partner->contract->marital_status}}" readonly />
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label" for="gender">Jenis Kelamin</label>
                                            <div class="col-sm-3">
                                                <input id="gender" type="text" class="form-control"
                                                       name="gender" value="{{ $partner->contract->gender}}" readonly />
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label" for="gender">Pendidikan & Jurusan</label>
                                            <div class="col-sm-4">
                                                <input id="education" type="text" class="form-control"
                                                       name="education" value="{{ $partner->contract->education}}" readonly />
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label" for="gender">Alamat</label>
                                            <div class="col-sm-4">
                                                <textarea class="form-control" id="address" name="address" readonly>{{ $partner->contract->address }}</textarea>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label" for="gender">No Telp. / HP.</label>
                                            <div class="col-sm-4">
                                                <input id="phone_number" type="text" class="form-control"
                                                       name="phone_number" value="{{ $partner->contract->phone_number}}" readonly />
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label" for="gender">Gaji</label>
                                            <div class="col-sm-4">
                                                <input id="phone_number" type="text" class="form-control"
                                                       name="phone_number" value="{{ number_format($partner->contract->salary, 0, ',', '.') }}" readonly />
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label" for="gender">Komisi</label>
                                            <div class="col-sm-4">
                                                <input id="commission" type="text" class="form-control"
                                                       name="commission" value="{{ number_format($partner->contract->commission, 1, ',', '.') }}" readonly />
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label" for="gender">KTP</label>
                                            <div class="col-sm-8 col-12">
                                                <a href="{{ \Illuminate\Support\Facades\Storage::disk('partner_ktp')->url($partner->contract->ktp_image_path) }}" target="_blank">
                                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('partner_ktp')->url($partner->contract->ktp_image_path) }}" style="max-height: 250px;">
                                                </a>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label" for="gender">PKWT</label>
                                            <div class="col-sm-4">
                                                <div class="input-group mb-3">
                                                    <input type="text" class="form-control" value="{{ $partner->contract->pkwt_image_path }}" readonly>
                                                    <a href="{{ \Illuminate\Support\Facades\Storage::disk('partner_pkwt')->url($partner->contract->pkwt_image_path) }}"
                                                       target="_blank" class="btn btn-primary"><i class="fa fa-fw fa-eye"></i></a>
                                                    <a href="{{ route('admin.partner.contract.history', ['id' => $partner->id]) }}" class="btn btn-secondary"><i class="fa fa-fw fa-list-ul"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if($partner->contract)
        <form id="delete_contract_form" method="post" action="{{ route('admin.partner.contract.delete') }}">
            @csrf
            <input type="hidden" id="id" name="id" value="{{ $partner->contract->id }}">
        </form>
    @endif
@endsection

@section('styles')
    <!-- Page JS Plugins CSS -->
@endsection

@section('scripts')
    <!-- jQuery -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script src="{{ asset('js/plugins/sweetalert2/sweetalert2.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('js/plugins/sweetalert2/sweetalert2.css') }}">

    <script>
        $('#deleteContract').on('click', function (){
            Swal.fire({
                icon: 'warning',
                title: 'Hapus Kontrak',
                text: 'Apakah Anda yakin ingin menghapus kontrak terapis ini?',
                showCancelButton: true,
                confirmButtonText: 'Ya',
                cancelButtonText: `Tidak`,
                confirmButtonColor: 'green',
                cancelButtonColor: 'orange'
            }).then((result) => {
                if(result.isConfirmed){
                    Swal.showLoading();
                    $('#delete_contract_form').submit();
                }
            });
        });

    </script>
@endsection
