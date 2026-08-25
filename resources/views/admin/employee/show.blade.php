@extends('layouts.admin')

@section('title')
    <title>Detil Karyawan</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Detil Karyawan {{ $employee->code }}
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.employee.index') }}">Daftar Karyawan</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Detil Karyawan
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <div class="content">
        <form class="space-y-3">
            @csrf
            <div class="row mb-3">
                <div class="col-sm-4 col-12">
                    <a href="{{ route('admin.employee.index') }}" class="btn btn-dark">
                        <i class="fa fa-arrow-left"></i>
                    </a>
                </div>
                <div class="col-sm-8 col-12 text-end">
                    @if(!$employee->contract)
                        <a href="{{ route('admin.employee.contract.create', ['id' => $employee->id]) }}" class="btn btn-success me-2">Buat Kontrak</a>
                    @endif

                    <a href="{{ route('admin.employee.edit', ['id' => $employee->id]) }}" class="btn btn-primary">Ubah</a>
                    &nbsp;
                    @if($employee->status_id === 1)
                        <button type="button" class="btn btn-danger" id="btn_update_status" onclick="updateStatus(2)">Non-Aktifkan</button>
                    @else
                        <button type="button" class="btn btn-success" id="btn_update_status" onclick="updateStatus(1)">Aktifkan</button>
                    @endif

                </div>
            </div>
            <div class="block block-rounded">
                <ul class="nav nav-tabs nav-tabs-alt" role="tablist">
                    <li class="nav-item">
                        <button type="button" class="nav-link active" id="btabs-alt-static-home-tab" data-bs-toggle="tab" data-bs-target="#btabs-alt-static-home" role="tab" aria-controls="btabs-alt-static-home" aria-selected="true">Detail</button>
                    </li>
                    @if($employee->contract)
                        <li class="nav-item">
                            <button type="button" class="nav-link" id="btabs-alt-static-contract-tab" data-bs-toggle="tab" data-bs-target="#btabs-alt-static-contract" role="tab" aria-controls="btabs-alt-static-contract" aria-selected="false">Kontrak</button>
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
                                            <label class="col-sm-3 col-form-label" for="image">Foto</label>
                                            <div class="col-sm-6">
                                                @if(!empty($employee->photo_path))
                                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('employee_photos')->url($employee->photo_path) }}" style="width: 150px; height: auto;" />
                                                @else
                                                    -
                                                @endif
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-3 fw-bold">Status</div>
                                            <div class="col-sm-3">
                                                @if($employee->status_id === 1)
                                                    <span class="fw-bold" style="color: green;">AKTIF</span>
                                                @else
                                                    <span class="fw-bold" style="color: red;">NON-AKTIF</span>
                                                @endif
                                            </div>
                                        </div>
                                        {{--                                <div class="row">--}}
                                        {{--                                    <label class="col-sm-3 col-form-label" for="branch">Cabang</label>--}}
                                        {{--                                    <div class="col-sm-3">--}}
                                        {{--                                        <input id="branch" type="text" class="form-control"--}}
                                        {{--                                               name="branch" value="{{ $employee->branch->name }}" readonly/>--}}
                                        {{--                                    </div>--}}
                                        {{--                                </div>--}}
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label" for="name">NIK Karyawan</label>
                                            <div class="col-sm-3">
                                                <input id="code" type="text" class="form-control"
                                                       name="code" value="{{ $employee->code }}" readonly/>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label" for="name">Nama</label>
                                            <div class="col-sm-3">
                                                <input id="name" type="text" class="form-control"
                                                       name="name" value="{{ $employee->name }}" readonly/>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label" for="phone">Nomor Telp</label>
                                            <div class="col-sm-3">
                                                <input id="phone" type="text" class="form-control"
                                                       name="phone" value="{{ $employee->phone }}" readonly/>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label" for="identity_no">Nomor KTP</label>
                                            <div class="col-sm-3">
                                                <input id="identity_no" type="text" class="form-control"
                                                       name="identity_no" value="{{ $employee->id_card_no ?? '' }}" readonly/>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label" for="department">Departemen</label>
                                            <div class="col-sm-3">
                                                <input id="department" type="text" class="form-control"
                                                       name="department" value="{{ $departmentName ?? '-' }}" readonly/>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label" for="position">Jabatan</label>
                                            <div class="col-sm-3">
                                                <input id="position" type="text" class="form-control"
                                                       name="position" value="{{ $employee->employee_position->name ?? '-' }}" readonly/>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label" for="basic_salary_amount">Gaji Pokok</label>
                                            <div class="col-sm-3">
                                                <div class="input-group">
                                                    <span class="input-group-text">Rp</span>
                                                    <input id="basic_salary_amount" type="text" class="form-control text-end"
                                                           name="basic_salary_amount" value="{{ number_format($employee->basic_salary_amount, 2, ',', '.') }}" readonly />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label" for="attend_machine_id">Nomor Urut Mesin Absen</label>
                                            <div class="col-sm-3">
                                                <input id="attend_machine_id" type="text" class="form-control"
                                                       name="attend_machine_id" value="{{ $employee->attend_machine_id }}" readonly />
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label" for="address">Alamat</label>
                                            <div class="col-sm-8">
                                                <textarea class="form-control" id="address" name="address" rows="5" style="resize: vertical;" readonly>{{ $employee->address ?? '' }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if($employee->contract)
                        <form id="delete_contract_form" method="post" action="{{ route('admin.employee.contract.delete') }}">
                            @csrf
                            <input type="hidden" id="id" name="id" value="{{ $employee->contract->id }}">
                        </form>
                        <div class="tab-pane" id="btabs-alt-static-contract" role="tabpanel" aria-labelledby="btabs-alt-static-contract-tab">
                            <div class="d-flex flex-row align-items-center justify-content-between">
                                <h4 class="fw-normal">Kontrak</h4>
                                <div class="d-flex flex-row">
                                    <button type="button" id="deleteContract" class="btn btn-danger me-2">Hapus</button>
                                    <a href="{{ route('admin.employee.contract.edit', ['id' => $employee->id]) }}" class="btn btn-primary">Ubah Kontrak</a>
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
                                                           name="code" value="{{ $employee->contract->code}}" readonly />
                                                </div>
                                            </div>
                                            <div class="row">
                                                <label class="col-sm-3 col-form-label" for="gender">Jangka Waktu</label>
                                                <div class="col-sm-2">
                                                    <div class="input-group">
                                                        <input id="duration" type="text" class="form-control numeric"
                                                               name="duration" value="{{ $employee->contract->duration }}" readonly/>
                                                        <span class="input-group-text">Bulan</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <label class="col-sm-3 col-form-label" for="join_date">Tanggal Mulai <span class="text-red">*</span></label>
                                                <div class="col-sm-3">
                                                    <input id="start_date" type="text" class="form-control js-flatpickr" data-date-format="d-m-Y"
                                                           name="start_date" value="{{ $employee->contract->start_date->format('d-m-Y') }}" readonly/>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <label class="col-sm-3 col-form-label" for="join_date">Tanggal Berakhir <span class="text-red">*</span></label>
                                                <div class="col-sm-3">
                                                    <input id="end_date" type="text" class="form-control js-flatpickr" data-date-format="d-m-Y"
                                                           name="end_date" value="{{ $employee->contract->end_date->format('d-m-Y') }}" readonly/>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <label class="col-sm-3 col-form-label" for="name">Nama</label>
                                                <div class="col-sm-4">
                                                    <input id="name" type="text" class="form-control"
                                                           name="name" value="{{ $employee->contract->name}}" readonly />
                                                </div>
                                            </div>
                                            <div class="row">
                                                <label class="col-sm-3 col-form-label" for="marital_status">Status</label>
                                                <div class="col-sm-3">
                                                    <input id="marital_status" type="text" class="form-control"
                                                           name="marital_status" value="{{ $employee->contract->marital_status}}" readonly />
                                                </div>
                                            </div>
                                            <div class="row">
                                                <label class="col-sm-3 col-form-label" for="gender">Jenis Kelamin</label>
                                                <div class="col-sm-3">
                                                    <input id="gender" type="text" class="form-control"
                                                           name="gender" value="{{ $employee->contract->gender}}" readonly />
                                                </div>
                                            </div>
                                            <div class="row">
                                                <label class="col-sm-3 col-form-label" for="gender">Pendidikan & Jurusan</label>
                                                <div class="col-sm-4">
                                                    <input id="education" type="text" class="form-control"
                                                           name="education" value="{{ $employee->contract->education}}" readonly />
                                                </div>
                                            </div>
                                            <div class="row">
                                                <label class="col-sm-3 col-form-label" for="gender">Alamat</label>
                                                <div class="col-sm-4">
                                                    <textarea class="form-control" id="address" name="address" readonly>{{ $employee->contract->address }}</textarea>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <label class="col-sm-3 col-form-label" for="gender">No Telp. / HP.</label>
                                                <div class="col-sm-4">
                                                    <input id="phone_number" type="text" class="form-control"
                                                           name="phone_number" value="{{ $employee->contract->phone_number}}" readonly />
                                                </div>
                                            </div>
                                            <div class="row">
                                                <label class="col-sm-3 col-form-label" for="gender">Gaji</label>
                                                <div class="col-sm-4">
                                                    <input id="salary" type="text" class="form-control"
                                                           name="salary" value="{{ number_format($employee->contract->salary, 0, ',', '.') }}" readonly />
                                                </div>
                                            </div>
                                            <div class="row">
                                                <label class="col-sm-3 col-form-label" for="gender">Komisi</label>
                                                <div class="col-sm-4">
                                                    <input id="commission" type="text" class="form-control"
                                                           name="commission" value="{{ number_format($employee->contract->commission, 1, ',', '.') }}" readonly />
                                                </div>
                                            </div>
                                            <div class="row">
                                                <label class="col-sm-3 col-form-label" for="gender">KTP</label>
                                                <div class="col-sm-8 col-12">
                                                    <a href="{{ \Illuminate\Support\Facades\Storage::disk('employee_ktp')->url($employee->contract->ktp_image_path) }}" target="_blank">
                                                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('employee_ktp')->url($employee->contract->ktp_image_path) }}" style="max-height: 250px;">
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <label class="col-sm-3 col-form-label" for="gender">PKWT</label>
                                                <div class="col-sm-4">
                                                    <div class="input-group mb-3">
                                                        <input type="text" class="form-control" value="{{ $employee->contract->pkwt_image_path }}" readonly>
                                                        <a href="{{ \Illuminate\Support\Facades\Storage::disk('employee_pkwt')->url($employee->contract->pkwt_image_path) }}"
                                                           target="_blank" class="btn btn-primary"><i class="fa fa-fw fa-eye"></i></a>
                                                        <a href="{{ route('admin.employee.contract.history', ['id' => $employee->id]) }}" class="btn btn-secondary"><i class="fa fa-fw fa-list-ul"></i></a>
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
        </form>
    </div>

    <form id="form_update_employee_status" method="post" action="{{ route('admin.employee.status.update') }}">
        @csrf
        <input type="hidden" id="updated_status_id" name="updated_status_id" />
        <input type="hidden" id="updated_employee_id" name="updated_employee_id" value="{{ $employee->id }}">
    </form>

    @if($employee->contract)
        <form id="delete_contract_form" method="post" action="{{ route('admin.employee.contract.delete') }}">
            @csrf
            <input type="hidden" id="id" name="id" value="{{ $employee->contract->id }}">
        </form>
    @endif
@endsection

@section('styles')
    <!-- Page JS Plugins CSS -->
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/bootstrap-fileinput/css/fileinput.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/sweetalert2/sweetalert2.css') }}">
@endsection

@section('scripts')
    <!-- jQuery -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('js/plugins/bootstrap-fileinput/js/fileinput.min.js') }}"></script>
    <script src="{{ asset('js/plugins/sweetalert2/sweetalert2.js') }}"></script>

    <script>
        $('#form_create_employee').submit(function(){
            $('#btn_submit').hide();
            $('#btn_cancel').hide();
            $('#btn_loading').show(200);
        });

        $('#image').fileinput({
            'showUpload': false,
            'overwriteInitial': false,
        });

        $('#deleteContract').on('click', function (){
            Swal.fire({
                icon: 'warning',
                title: 'Hapus Kontrak',
                text: 'Apakah Anda yakin ingin menghapus kontrak karyawan ini?',
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

        function updateStatus(flag){
            $('#updated_status_id').val(flag);
            $('#btn_update_status').hide(100);

            let iconInput = 'success';
            let titleInput = 'Aktifkan Karyawan';
            let textInput = 'Apakah Anda yakin ingin aktifkan kembali karyawan ini?'
            let confirmColor = 'green';
            let cancelColor = 'orange';

            if(flag == 2){
                iconInput = 'warning';
                titleInput = 'Non-Aktifkan Karyawan';
                textInput = 'Apakah Anda yakin ingin non-aktifkan karyawan ini?'
                confirmColor = 'red';
                cancelColor = 'orange';
            }

            Swal.fire({
                icon: iconInput,
                title: titleInput,
                text: textInput,
                showCancelButton: true,
                confirmButtonText: 'Ya',
                cancelButtonText: `Tidak`,
                confirmButtonColor: confirmColor,
                cancelButtonColor: cancelColor
            }).then((result) => {
                if(result.isConfirmed){
                    Swal.showLoading();
                    $('#form_update_employee_status').submit();
                }
                else if(result.isDismissed){
                    $('#btn_update_status').show();
                }
            });
        }
    </script>
@endsection
