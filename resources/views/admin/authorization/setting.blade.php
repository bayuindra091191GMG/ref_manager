@extends('layouts.admin')

@section('title')
    <title>Daftar Ijin Akses</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Daftar Ijin Akses
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Daftar Ijin Akses
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <div class="content">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                {{--                <h3 class="block-title">Users</h3>--}}
            </div>
            <div class="block-content block-content-full">
                <div class="row">
                    <div class="col-lg-8">
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
{{--                <div class="row mb-3">--}}
{{--                    @if($isSuperAdmin)--}}
{{--                        <div class="col-sm-3 col-12">--}}
{{--                            <label class="form-label fw-bold" for="branch">Pilih Cabang</label>--}}
{{--                            <select class="form-select" id="branch" name="branch" onchange="switchBranch();">--}}
{{--                                @foreach($branches as $branch)--}}
{{--                                    <option value="{{ $branch->id }}" @if($branchId === $branch->id) selected @endif>Cabang {{ $branch->name }}</option>--}}
{{--                                @endforeach--}}
{{--                            </select>--}}
{{--                        </div>--}}
{{--                    @endif--}}
{{--                </div>--}}
                <input type="hidden" id="branch" name="branch" value="{{ $branchId }}" />
                <div class="row">
                    <div class="col-12">
                        <hr/>
                        <div class="table-responsive w-100">
                            <table class="table table-bordered table-striped table-vcenter js-dataTable-full" id="table_auths">
                                <thead>
                                <tr>
                                    <th class="text-center" style="width: 5%;">No</th>
                                    <th class="text-center">Posisi</th>
                                    <th class="text-center">Ijin Akses</th>
                                    <th class="text-center">Tindakan</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($authCollects as $authCollect)
                                    <tr>
                                        <td class="text-center">{{ $authCollect['index'] }}</td>
                                        <td>{{ $authCollect['name'] }}</td>
                                        <td>
                                            @foreach($authCollect['modules'] as $authModule)
                                                <span>{{ $authModule }}</span><br/>
                                            @endforeach
                                        </td>
                                        <td class="text-center"><a href="#" class="btn btn-info edit-module" data-position-id="{{ $authCollect['positionId'] }}" data-module-ids="{{ $authCollect['moduleIds'] }}"><i class="fa fa-edit"></i></a></td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal form to edit an item -->
    <div class="modal fade" id="modal_edit" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-dialog-slideup" role="document">
            <div class="modal-content">
                <div class="block block-rounded block-transparent mb-0">
                    <form id="form_edit" method="POST" action="{{ route('admin.auth.module.update') }}">
                        @csrf
                        <input type="hidden" id="edited_branch_id" name="edited_branch_id" value="{{ $branchId }}"/>
                        <div class="block-header block-header-default">
                            <h3 class="block-title">Ubah Ijin Akses</h3>
                            <div class="block-options">
                                <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">
                                    <i class="fa fa-fw fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="block-content fs-sm">
                            <div class="row mb-3">
                                <label class="col-sm-4 col-12 col-form-label" for="edited_auth_modules">Akses Diizinkan : </label>
                                <div class="col-sm-8 col-12">
                                    <select id="edited_auth_modules" name="edited_auth_modules[]" class="form-control" multiple>
                                        @foreach($authModules as $authModule)
                                            <option value="{{ $authModule['value'] }}">{{ $authModule['text'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <input type="hidden" id="edited_position_id" name="edited_position_id"/>
                            </div>
                        </div>
                        <div class="block-content block-content-full text-center bg-body">
                            <button type="button" class="btn btn-sm btn-danger" data-bs-dismiss="modal">Batal</button>
                            <input type="submit" class="btn btn-sm btn-success" value="Ubah" />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <!-- Page JS Plugins CSS -->
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
    <style>
        .select2-container--default .select2-selection--multiple .select2-selection__choice{
            color: #000;
        }
    </style>
@endsection

@section('scripts')
    <!-- jQuery (required for DataTables plugin) -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script src="{{ asset('js/plugins/select2/js/select2.min.js') }}"></script>

    <!-- Page JS Plugins -->

    <script>
        function switchBranch(){
            let branchId = $('#branch').val();
            window.location = '{{ route('admin.auth.module.index') }}?branch_id=' + branchId;
        }

        $('#edited_auth_modules').select2({
            dropdownParent: $('#modal_edit'),
            width: '100%'
        });

        $(document).on('click', '.edit-module', function() {
            let userId = $(this).data('position-id');
            let moduleIds = $(this).data('module-ids') + '';

            let moduleIdsArr = '';
            if(moduleIds.indexOf(',') > -1){
                moduleIdsArr = moduleIds.split(',');
            }
            else{
                moduleIdsArr = moduleIds;
            }

            $('#edited_position_id').val(userId);
            $('#edited_auth_modules').val(null).trigger('change');
            $('#edited_auth_modules').val(moduleIdsArr);
            $('#edited_auth_modules').trigger('change');
            $('#modal_edit').modal('show');
        });
    </script>
@endsection
