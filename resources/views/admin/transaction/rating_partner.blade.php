@extends('layouts.admin')

@section('title')
    <title>Laporan Realisasi Terapis</title>
@endsection

@section('content')
    <!-- Hero -->
    <div>
        <div class="content space-top">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Laporan Realisasi Terapis
                    </h1>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3 print_hide" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Laporan Realisasi Terapis
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <div class="content">
        <div class="block block-rounded">
            <div class="block-content">
                <div class="row">
                    <div class="col-12">
                        <div class="row print_hide mb-4">
                            <div class="col-12 text-end">
                                <button type="button" id="btn_print" class="btn btn-outline-info">Print</button>
{{--                                <a id="btn_cancel" href="{{ route('admin.purchase_order.index') }}" class="btn btn-outline-danger">Back</a>--}}
                            </div>
                        </div>
                        <div class="table-responsive w-100" >
                            <table class="table table-bordered table-striped table-vcenter js-dataTable-full nowrap" id="table_data">
                                <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Status</th>
                                    <th>Qty</th>
                                    <th>Total Treatment (Rp)</th>
                                    <th>Qty Treatment Tambahan</th>
                                    <th>Total Treatment Tambahan (Rp)</th>
                                    {{-- <th>Total Commission</th> --}}
                                </tr>
                                </thead>
                                <tbody>
                                @php($no = 1)
                                @foreach($result as $partner)
                                    <tr>
                                        <td>
                                            {{ $no }}
                                        </td>
                                        <td>
                                            <a target="_blank" href="{{ route('admin.partner.show', ['id' => $partner['id']]) }}">{{ $partner['name'] }}</a>
                                        </td>
                                        <td>
                                            {{ $partner['level'] }}
                                        </td>
                                        <td>
                                            {{ $partner['qty'] }}
                                        </td>
                                        <td>
                                            {{ number_format($partner['total_treatment'], '0', ',', '.') }}
                                        </td>
                                        <td>
                                            {{ $partner['qty_treatement_addon'] }}
                                        </td>
                                        <td>
                                            {{ number_format($partner['total_treatment_addon'], '0', ',', '.') }}
                                        </td>
                                        {{-- <td>
                                            {{ number_format($partner['total_commission'], '0', ',', '.') }}
                                        </td> --}}
                                    </tr>
                                    @php($no++)
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <!-- Page JS Plugins CSS -->
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-bs5/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-buttons-bs5/buttons.bootstrap5.min.css') }}">

    <style>
        @media print {
            .print_show{
                display: block;
            }

            .print_hide {
                display: none;
            }
        }
    </style>

@endsection

@section('scripts')
    <!-- jQuery (required for DataTables plugin) -->
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>

    <!-- Page JS Plugins -->
    <script src="{{ asset('js/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-bs5/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('js/moment.min.js') }}"></script>
    <script src="{{ asset('js/plugins/bootstrap-notify/bootstrap-notify.min.js') }}"></script>

    <script>
        $(document).ready(function () {
            $(window).keydown(function (event) {
                if (event.keyCode === 13) {
                    event.preventDefault();
                    return false;
                }
            });
        });

        $(document).on('click', '#btn_print', function(){
            $('.print_show').show();
            window.print();
        });
        window.onafterprint = function(){
            $('.print_show').hide();
        }
    </script>
@endsection
