<!DOCTYPE html>
<html lang="en">
<head>
    <title>Laporan Gaji Karyawan</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" crossorigin="anonymous">

    <style>
        @media print {
            .print_show{
                display: block;
            }

            .print_hide {
                display: none;
            }
        }

        td{
            padding: 3px;
        }
    </style>
</head>
<body>
<div class="container" style="width: 700px !important;">
    <div id="print_button_section" class="row my-3">
        <div class="col-12">
            <button type="button" class="btn btn-primary" id="btn_print">CETAK</button>
        </div>
    </div>
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h5>Laporan Gaji Karyawan</h5>
            <span class="fw-bold">Periode {{ $periodDate }}</span><br/>
            <span class="fw-bold">Total Rp{{ number_format($grandTotalSalary, 0, ',', '.') }}</span>
        </div>
    </div>
    <div class="row mb-4" style="font-size: 11px !important;">
        <div class="col-12">
            <table class="w-100 table">
                <thead>
                <tr class="text-center">
                    <th class="text-center">No</th>
                    <th class="text-start">Nama Karyawan</th>
                    <th class="text-center">Kode Karyawan</th>
                    <th class="text-center">Gaji Dasar</th>
                    <th class="text-center">Komisi</th>
                    <th class="text-center">Pinalti</th>
                    <th class="text-center">Lain-lain</th>
                    <th class="text-center">Subtotal</th>
                </tr>
                </thead>
                <tbody>
                @php( $idx = 1 )
                @php( $grandTotalSalaryAmount = 0 )
                @foreach($salaries as $salary)
                    <tr>
                        <td class="text-center">{{ $idx }}</td>
                        <td>{{ $salary->employee_name }}</td>
                        <td>{{ $salary->employee_code }}</td>
                        <td class="text-end">{{ number_format($salary->basic_salary_amount, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($salary->commission_amount, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($salary->penalty_amount, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($salary->other_amount, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($salary->total_salary_amount, 0, ',', '.') }}</td>
                        @php( $grandTotalSalaryAmount += $salary->total_salary_amount )
                    </tr>
                    @php( $idx++ )
                @endforeach
                <tr>
                    <td class="text-end fw-bold" colspan="7">TOTAL: </td>
                    <td class="text-end fw-bold">{{ number_format($grandTotalSalary, 0, ',', '.') }}</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="{{ asset('js/lib/jquery.min.js') }}"></script>
<script>
    $(document).on('click', '#btn_print', function(){
        // $('#print_doc').printThis({
        //     header: "<h3>Surat Jalan</h3>",
        // });
        $('#print_button_section').hide();
        window.print();
    });
    // window.onafterprint = function(){
    //     $('#print_button_section').hide();
    // }
</script>

</body>
</html>
