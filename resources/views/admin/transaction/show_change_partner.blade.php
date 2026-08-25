@extends('layouts.admin')

@section('title')
    <title>Detil Transaksi</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Detil Transaksi
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
                            Mengubah Therapis
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->
    <div id="app" class="content">
        <form action="{{ route('admin.transaction.change_partner') }}" method="POST">
            @csrf
            <div class="row items-push">
                <div class="col-md-12 col-xl-12">
                    <div class="block block-rounded h-100 mb-0">
                        <div class="block-content block-content-full">
                            <div class="row">
                                <div class="col-12">
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
                                            <div class="col-12 text-end">
                                                <a href="{{ route('admin.transaction.show', ['id' => $headerId]) }}" class="btn btn-danger">Cancel</a>
                                                <button type="submit" class="btn btn-primary">Submit</button>
                                                <input type="hidden" name="id" value="{{ $headerId }}">
                                                <input type="hidden" name="detailId" value="{{ $detailId }}">
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label" for="code">Status</label>
                                            <div class="col-sm-9">
                                                <input type="text" id="code" class="form-control" readonly value="{{ $header['status'] }}"/>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label" for="code">Code</label>
                                            <div class="col-sm-9">
                                                <input type="text" id="code" class="form-control" readonly value="{{ $header['code'] }}"/>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label" for="locker">Nomor Locker</label>
                                            <div class="col-sm-9">
                                                <input type="text" id="locker" class="form-control" readonly value="{{ $header['locker'] }}"/>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label" for="gro">Gro</label>
                                            <div class="col-sm-9">
                                                <input type="text" id="gro" class="form-control" readonly value="{{ $header['gro'] }}"/>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label" for="room">Paket</label>
                                            <div class="col-sm-9">
                                                <input type="text" id="room" class="form-control" readonly value="{{ $package->name }}"/>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label" for="return">Detil Therapis</label>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-12">
                                                <table class="table table-bordered table-striped table-vcenter">
                                                    <thead>
                                                    <tr>
                                                        <td>No</td>
                                                        <td>Therapis</td>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr v-for="(item, index) in choosenPartners">
                                                            <td>@{{ index+1 }}</td>
                                                            <td>
                                                                <select name="partnerIds[]" class="form-select" v-model="item.id">
                                                                    <option v-for="(partner, idx) in allPartners" :value="partner.id">@{{ partner.number + ' ' + partner.alias }}</option>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('js/plugins/sweetalert2/sweetalert2.css') }}">
@endsection

@section('scripts')
    <!-- jQuery -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script src="{{ asset('js/plugins/sweetalert2/sweetalert2.js') }}"></script>
    <script src="{{ asset('js/vue.js') }}"></script>
    <script>
        let app = new Vue({
            el: '#app',
            data: {
                choosenPartners: @json($choosenPartner),
                allPartners: @json($allPartners),
            },
        });
    </script>
@endsection
