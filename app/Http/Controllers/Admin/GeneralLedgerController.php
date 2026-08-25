<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Coa;
use App\Models\CoaMonthlyAmount;
use App\Models\JournalDetail;
use App\Services\SaldoAwalGenerator;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class GeneralLedgerController extends Controller
{
    protected $totalHeader = [];

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function filter(Request $request)
    {
        $filterStartDate = Carbon::now('Asia/Jakarta')->startOfMonth()->subMonth()->format('d-m-Y');
        $filterEndDate = Carbon::now('Asia/Jakarta')->endOfMonth()->format('d-m-Y');
        if ($request->start_date != null && $request->end_date != null) {
            $startDateDecoded = rawurldecode($request->start_date);
            $endDateDecoded = rawurldecode($request->end_date);
            $start = Carbon::createFromFormat('!d-m-Y', $startDateDecoded, 'Asia/Jakarta');
            $end = Carbon::createFromFormat('!d-m-Y', $endDateDecoded, 'Asia/Jakarta');

            if ($end->greaterThanOrEqualTo($start)) {
                $filterStartDate = $startDateDecoded;
                $filterEndDate = $endDateDecoded;
            }
        }

        return view('admin.report.report_general_ledger_filter_new', compact(
            'filterStartDate',
            'filterEndDate',
            'coaId',
            'type'
        ));

    }

    public function report(Request $request)
    {
        try {
            $rules = [
                'start_date' => 'required',
                'end_date' => 'required',
            ];
            $message = [
                'start_date.required' => 'Tanggal Mulai Wajib dipilih!',
                'end_date.required' => 'Tanggal Akhir Wajib dipilih!',
            ];

            $validator = Validator::make($request->all(), $rules, $message);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator->errors())->withInput($request->all());
            }

            $admin = Auth::guard('admin')->user();
            $year = $request->input('select_year');
            $type = $request->input('type');

            if ($type == 'Profit and Loss' || $type == 'Ledger') {
                if (intval($request->input('end_date')) < intval($request->input('start_date'))) {
                    return back()->withErrors('Bulan Akhir tidak bisa lebih kecil dari Bulan Mulai')->withInput($request->all());
                }

                $inputEndDate = intval($request->input('end_date')) + 1;
                $startDate = Carbon::createFromFormat('!d-m-Y', '01-'.$request->input('start_date').'-'.$year, 'Asia/Jakarta');
                $endDate = Carbon::createFromFormat('!d-m-Y', '01-'.$inputEndDate.'-'.$year, 'Asia/Jakarta');
                $endDate->addMinutes(-1);
            } elseif ($type == 'Cashflow') {
                $startDate = Carbon::createFromFormat('!d-m-Y', '01-01-'.$year, 'Asia/Jakarta')->startOfMonth();
                $endDate = Carbon::createFromFormat('!d-m-Y', '01-12-'.$year, 'Asia/Jakarta')->endOfMonth();
            } elseif ($type == 'Balance Sheet') {
                $inputEndDate = intval($request->input('start_date')) + 1;
                $startDate = Carbon::createFromFormat('!d-m-Y', '01-'.$request->input('start_date').'-'.$year, 'Asia/Jakarta');
                $endDate = Carbon::createFromFormat('!d-m-Y', '01-'.$inputEndDate.'-'.$year, 'Asia/Jakarta');
                $endDate->addHours(2);
            } else {
                $startDate = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
                $endDate = Carbon::createFromFormat('!d-m-Y', $request->input('end_date'), 'Asia/Jakarta');
                $endDate->addDays(1)->addMinutes(-1);
            }

            // dd($startDate->toDateTimeString(). ' '. $endDate->toDateTimeString());

            $result = [];

            switch ($request->input('type')) {
                case 'Cashflow':
                    $result = $this->cashflowFunc($year);
                    break;
                case 'Trial Balance':
                    $result = $this->trialBalanceFunc();
                    break;
                case 'Profit and Loss':
                    $result = $this->profitAndLossNew($request->input('start_date'), $request->input('end_date'), $year, true);
                    break;
                case 'Profit and Loss Detail':
                    // $result = $this->profitLossHeaderVersionFunc($startDate, $endDate);
                    $result = $this->profitAndLossNew($startDate, $endDate, 2024, false);
                    break;
                case 'Balance Sheet':
                    if ($admin->branch->is_oura == 1) {
                        $result = $this->balanceSheetFuncNew($startDate, $endDate);
                    } else {
                        $result = $this->balanceSheetFunc($startDate, $endDate);
                    }

                    break;
                case 'Ledger':
                    if ($admin->branch->is_oura == 1) {
                        $result = $this->ledgerFuncNew($startDate, $endDate, $request->input('coa'));
                    } else {
                        $result = $this->ledgerFunc($startDate, $endDate, $request->input('coa'));
                    }

                    break;
                case 'Ledger Detail':
                    if ($admin->branch->is_oura == 1) {
                        $result = $this->ledgerFuncNew($startDate, $endDate, $request->input('coa'));
                    } else {
                        $result = $this->ledgerFunc($startDate, $endDate, $request->input('coa'));
                    }

                    break;
                case 'Jurnal':
                    break;
            }

            $user = Auth::guard('admin')->user();
            $branch = Branch::where('id', $user->branch_id)->first();

            $range = [];
            $extraTotal = [
                'gross' => [],
                'operating' => [],
                'nett' => [],
            ];
            if ($type == 'Profit and Loss') {
                for ($i = $request->input('start_date'); $i <= $request->input('end_date'); $i++) {
                    $range[] = $i;

                    $extraTotal['gross'][$i] = 0;
                    $extraTotal['operating'][$i] = 0;
                    $extraTotal['nett'][$i] = 0;
                }

                $income = [];
                $expense = [];

                foreach ($range as $r) {
                    $income[$r] = 0;
                    $expense[$r] = 0;
                }

                foreach ($result as $data) {
                    if ($data['type_id'] == 4) {
                        foreach ($data['header_total'] as $i => $total) {
                            $income[$i] += $total;
                        }
                    } elseif ($data['type_id'] == 5) {
                        foreach ($data['header_total'] as $i => $total) {
                            $expense[$i] += $total;
                        }
                    }
                }
                foreach ($range as $r) {
                    $gross = $income[$r];
                    $operating = $expense[$r];
                    $nett = $gross + $operating;

                    $extraTotal['gross'][$r] = $gross;
                    $extraTotal['operating'][$r] = $operating;
                    $extraTotal['nett'][$r] = $nett;
                }

                $extraTotal['income'] = $income;
                $extraTotal['expense'] = $expense;
            }

            $data = [
                'result' => $result,
                'type' => $type,
                'branch' => $branch,
                'year' => $year,
                'start_date' => $startDate->locale('id_ID')->format('Y-m-d'),
                'end_date' => $endDate->locale('id_ID')->format('Y-m-d'),
                'range' => $range,
                'extraTotal' => $extraTotal,
            ];

            switch ($request->input('type')) {
                case 'Cashflow':
                    return view('admin.general_ledger.report-cashflow')->with($data);
                    break;
                case 'Trial Balance':
                    return view('admin.general_ledger.report-trial-balance')->with($data);
                    break;
                case 'Profit and Loss':
                    return view('admin.general_ledger.report-profit-loss-v2')->with($data);
                    break;
                case 'Balance Sheet':
                    if ($admin->branch->is_oura == 1) {
                        $month = $startDate->locale('id_ID')->format('M');
                        $year = $startDate->year;

                        $configBeginningDate = DB::table('configurations')
                            ->where('branch_id', '=', $user->branch_id)
                            ->where('key', '=', 'balance_sheet_beginning_date')
                            ->first();

                        $beginningDate = Carbon::parse($configBeginningDate->value);
                        $startDate = $beginningDate->copy();
                        $beginningDate->addMonthNoOverflow();
                        if ($endDate->lessThanOrEqualTo($beginningDate)) {
                            $endDate = $beginningDate->copy();
                        }

                        $pnlStartDate = $startDate->copy()->addMonthNoOverflow();
                        $pnlEndDate = $endDate->copy()->hour(0)->subMinute();
                        // dd($pnlStartDate. ' - '. $pnlEndDate);

                        // Calculate profit and loss for current earning
                        $totalIncomeQuery = 'select (sum(jDt.credit) - sum(jDt.debit))  as amount
                            from journal_headers jHd
                                    INNER JOIN
                                        journal_details jDt ON jDt.header_id = jHd.id
                            where
                                jDt.branch_id = '.$user->branch_id."
                                AND jDt.account_type_id = 4
                                AND jHd.status_id = 13
                                AND jHd.transaction_date >= '".$pnlStartDate->format('Y-m-d H:i:s')."'
                                AND jHd.transaction_date < '".$pnlEndDate->format('Y-m-d H:i:s')."';";
                        $totalIncomeDB = DB::select($totalIncomeQuery);
                        $totalIncome = $totalIncomeDB[0]->amount;

                        // dd($totalIncome);

                        $totalCostOfSalesQuery = 'select (sum(jDt.debit) - sum(jDt.credit))  as amount
                            from journal_headers jHd
                                    INNER JOIN
                                        journal_details jDt ON jDt.header_id = jHd.id
                            where
                                jDt.branch_id = '.$user->branch_id."
                                AND jDt.account_type_id = 5
                                AND jHd.status_id = 13
                                AND jHd.transaction_date >= '".$pnlStartDate->format('Y-m-d H:i:s')."'
                                AND jHd.transaction_date < '".$pnlEndDate->format('Y-m-d H:i:s')."';";
                        $totalCostOfSalesDB = DB::select($totalCostOfSalesQuery);
                        $totalCostOfSales = $totalCostOfSalesDB[0]->amount;

                        // $jurnalExpensesQuery = "select (sum(jDt.debit) - sum(jDt.credit))  as amount
                        //     from journal_headers jHd
                        //             INNER JOIN
                        //                 journal_details jDt ON jDt.header_id = jHd.id
                        //     where
                        //         jDt.branch_id = ". $user->branch_id ."
                        //         AND jDt.account_code like '6%'
                        //         AND jHd.status_id = 13
                        //         AND jHd.transaction_date >= '". $pnlStartDate->format('Y-m-d H:i:s') ."'
                        //         AND jHd.transaction_date < '". $pnlEndDate->format('Y-m-d H:i:s') ."';";
                        // $jurnalExpensesDB = DB::select($jurnalExpensesQuery);
                        // $totalExpenses = $jurnalExpensesDB[0]->amount;

                        // $totalOtherIncomeQuery = "select (sum(jDt.credit) - sum(jDt.debit))  as amount
                        //     from journal_headers jHd
                        //             INNER JOIN
                        //                 journal_details jDt ON jDt.header_id = jHd.id
                        //     where
                        //         jDt.branch_id = ". $user->branch_id ."
                        //         AND jDt.account_code like '8%'
                        //         AND jHd.status_id = 13
                        //         AND jHd.transaction_date >= '". $pnlStartDate->format('Y-m-d H:i:s') ."'
                        //         AND jHd.transaction_date < '". $pnlEndDate->format('Y-m-d H:i:s') ."';";
                        // $totalOtherIncomeDB = DB::select($totalOtherIncomeQuery);
                        // $totalOtherIncome = $totalOtherIncomeDB[0]->amount;

                        // $totalOtherExpensesQuery = "select (sum(jDt.debit) - sum(jDt.credit))  as amount
                        //     from journal_headers jHd
                        //             INNER JOIN
                        //                 journal_details jDt ON jDt.header_id = jHd.id
                        //     where
                        //         jDt.branch_id = ". $user->branch_id ."
                        //         AND jDt.account_code like '9%'
                        //         AND jHd.status_id = 13
                        //         AND jHd.transaction_date >= '". $pnlStartDate->format('Y-m-d H:i:s') ."'
                        //         AND jHd.transaction_date < '". $pnlEndDate->format('Y-m-d H:i:s') ."';";
                        // $totalOtherExpensesDB = DB::select($totalOtherExpensesQuery);
                        // $totalOtherExpenses = $totalOtherExpensesDB[0]->amount;

                        $pnlGrandTotal = $totalIncome - $totalCostOfSales;
                        // $operatingProfit = $grossProfit - $totalExpenses;
                        // $pnlGrandTotal = $operatingProfit + $totalOtherIncome - $totalOtherExpenses;

                        $data = [
                            'result' => $result,
                            'type' => $type,
                            'branch' => $branch,
                            'year' => $year,
                            'start_date' => $startDate->locale('id_ID')->format('Y-m-d'),
                            'end_date' => $endDate->locale('id_ID')->format('Y-m-d'),
                            'range' => $range,
                            'extraTotal' => $extraTotal,
                            'month' => $month,
                            'year' => $year,
                            'pnlGrandTotal' => $pnlGrandTotal,
                        ];

                        return view('admin.general_ledger.report-balance-sheet-v3')->with($data);
                    } else {
                        return view('admin.general_ledger.report-balance-sheet')->with($data);
                    }

                    break;
                case 'Ledger':
                    return view('admin.general_ledger.report-ledger')->with($data);
                    break;
                case 'Ledger Detail':
                    return view('admin.general_ledger.report-ledger')->with($data);
                    break;
            }
        } catch (\Exception $ex) {
            Session::flash('error', $ex);

            return back();
        }
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $admin = DB::table('admin_users')->where('admin_users.id', '=', $user->id)->first();
        $branch = DB::table('branches')
            ->where('id', '=', $admin->branch_id)
            ->first();

        $branches = Branch::all();

        $defaultBranchId = $admin->branch_id ?? 1;

        $filterBranchId = $request->branch_id ?? $defaultBranchId;

        $type = $request->type ?? 'profit_loss';
        $coaId = $request->coa_id ?? 0;

        $filterStartDate = Carbon::now('Asia/Jakarta')->startOfMonth()->format('d-m-Y');
        $filterEndDate = Carbon::now('Asia/Jakarta')->endOfMonth()->format('d-m-Y');
        if ($request->start_date != null && $request->end_date != null) {
            $startDateDecoded = rawurldecode($request->start_date);
            $endDateDecoded = rawurldecode($request->end_date);
            $start = Carbon::createFromFormat('!d-m-Y', $startDateDecoded, 'Asia/Jakarta');
            $end = Carbon::createFromFormat('!d-m-Y', $endDateDecoded, 'Asia/Jakarta');

            if ($end->greaterThanOrEqualTo($start)) {
                $filterStartDate = $startDateDecoded;
                $filterEndDate = $endDateDecoded;
            }
        }

        app(SaldoAwalGenerator::class)->syncRangeForReport(
            $admin->branch_id,
            Carbon::createFromFormat('!d-m-Y', $filterStartDate, 'Asia/Jakarta'),
            Carbon::createFromFormat('!d-m-Y', $filterEndDate, 'Asia/Jakarta')
        );

        return view('admin.report.report_general_ledger', compact(
            'branches',
            'branch',
            'filterBranchId',
            'filterStartDate',
            'filterEndDate',
            'type',
            'coaId'
        ));
    }

    public function data(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
        ], [
            'category.required' => 'Pilih kategori terlebih dahulu',
            'start_date.required' => 'Pilih tanggal mulai terlebih dahulu',
            'end_date.required' => 'Pilih tanggal akhir terlebih dahulu',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors()->first(), 400);
        }

        $startDate = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
        $endDate = Carbon::createFromFormat('!d-m-Y', $request->input('end_date'), 'Asia/Jakarta');
        $endDate = $endDate->endOfDay();

        $branchId = $request->input('branch_id');

        if ($branchId == '') {
            $branchId = null;
        } elseif ($branchId == 'all') {
            $branchId = 'all';
        } else {
            $branch = Branch::find($branchId);
            $branchId = $branch->id;
        }
        $year = $endDate->copy()->format('Y');
        $type = $request->input('category');
        $coaId = $request->input('coa_id');

        $result = [];

        $range = [];
        $extraTotal = [
            'gross' => [],
            'operating' => [],
            'nett' => [],
        ];
        switch ($type) {
            case 'cashflow':
                $result = $this->cashflowFunc($year);
                break;
            case 'profit_loss':
                $result = $this->profitAndLossFunc($startDate, $endDate, $year, true);
                // $result = $this->profitAndLossNew($startDate, $endDate, $year, true);

                // $period = \Carbon\Carbon::parse($startDate)->copy();
                // $end = \Carbon\Carbon::parse($endDate)->copy();

                // while ($period->lte($end)) {
                //     $month = (int) $period->format('n'); // e.g. 1 for Jan, 12 for Dec
                //     $range[] = $month;

                //     $income[$month] = 0;
                //     $expense[$month] = 0;
                //     $extraTotal['gross'][$month] = 0;
                //     $extraTotal['operating'][$month] = 0;
                //     $extraTotal['nett'][$month] = 0;

                //     $period->addMonth(); // increment by month
                // }

                // // Summing up values by month
                // foreach ($result as $data) {
                //     if ($data['type_id'] == 4) { // income
                //         foreach ($data['header_total'] as $i => $total) {
                //             $income[$i] += $total;
                //         }
                //     // } elseif ($data['type_id'] == 5 || $data['type_id'] == 6) { // expense
                //     } else { // expense
                //         foreach ($data['header_total'] as $i => $total) {
                //             $expense[$i] += $total;
                //         }
                //     }
                // }

                // // Finalize gross, operating, nett totals
                // foreach ($range as $month) {
                //     $gross = $income[$month] ?? 0;
                //     $operating = $expense[$month] ?? 0;
                //     $nett = $gross + $operating;

                //     $extraTotal['gross'][$month] = $gross;
                //     $extraTotal['operating'][$month] = $operating;
                //     $extraTotal['nett'][$month] = $nett;
                // }

                // $extraTotal['income'] = $income;
                // $extraTotal['expense'] = $expense;

                break;
            case 'balance_sheet':
                //                    $result = $this->balanceSheetFuncNew($startDate, $endDate, $branchId);
                $result = $this->balanceSheetFunc($startDate, $endDate);
                break;
            case 'ledger':
                //                    $result = $this->ledgerFuncNew($startDate, $endDate, $request->input('coa'), $branchId);
                $result = $this->ledgerFunc($startDate, $endDate, $coaId);
                break;
            case 'trial_balance':
                $result = $this->trialBalanceFuncBU($startDate);
                break;
            case 'Jurnal':
                break;
        }

        $data = [
            'result' => $result,
            'branch' => $branch ?? null,
            'year' => $year,
            'start_date' => $startDate->locale('id_ID')->format('Y-m-d'),
            'end_date' => $endDate->locale('id_ID')->format('Y-m-d'),
            'range' => $range,
            'extraTotal' => $extraTotal,
        ];

        return Response::json($data);
    }

    public function cashflowFunc($year)
    {
        $result = [
            'transaction_cash_out' => [],
            'transaction_cash_in' => [],
        ];
        $user = Auth::guard('admin')->user();

        $cashBankIds = Coa::select('id', 'name', 'code')
            ->where('branch_id', $user->branch_id)
            ->where('cashflow', 1)
            // ->where('parent_id', $coaKas->id)
            ->get();

        $journalQuery = "SELECT c.id, c.code, c.name,
                        YEAR(jh.transaction_date) AS year,
                        MONTH(jh.transaction_date) AS month,
                        SUM(jd.debit) AS debit,
                        SUM(jd.credit) AS credit,
                        SUM(jd.original_amount) AS original_amount
                    FROM coas c
                    LEFT JOIN journal_details jd ON jd.account_id = c.id
                    LEFT JOIN journal_headers jh ON jh.id = jd.header_id
                    WHERE jh.branch_id = $user->branch_id
                    AND jh.status_id = 13
                    AND jh.transaction_date >= '".Carbon::createFromFormat('!d-m-Y', '01-01-'.$year, 'Asia/Jakarta')->startOfMonth()->toDateString()." 00:00:00'
                    AND jh.transaction_date <= '".Carbon::createFromFormat('!d-m-Y', '01-12-'.$year, 'Asia/Jakarta')->endOfMonth()->toDateString()." 23:59:59'
                    GROUP BY c.id, c.code, c.name, YEAR(jh.transaction_date), MONTH(jh.transaction_date)
                    ORDER BY c.id, year, month;";
        $dbData = DB::select($journalQuery);

        foreach ($cashBankIds as $cashBankId) {
            $nTrxCo = [
                'id' => $cashBankId->id,
                'coa_code' => $cashBankId->code,
                'coa_name' => $cashBankId->name,
                '01' => 0,
                '02' => 0,
                '03' => 0,
                '04' => 0,
                '05' => 0,
                '06' => 0,
                '07' => 0,
                '08' => 0,
                '09' => 0,
                '10' => 0,
                '11' => 0,
                '12' => 0,
            ];

            foreach ($dbData as $data) {
                if ($data->id == $cashBankId->id) {
                    switch ($data->month) {
                        case 1:
                            $nTrxCo['01'] = (float) $data->credit;
                            break;
                        case 2:
                            $nTrxCo['02'] = (float) $data->credit;
                            break;
                        case 3:
                            $nTrxCo['03'] = (float) $data->credit;
                            break;
                        case 4:
                            $nTrxCo['04'] = (float) $data->credit;
                            break;
                        case 5:
                            $nTrxCo['05'] = (float) $data->credit;
                            break;
                        case 6:
                            $nTrxCo['06'] = (float) $data->credit;
                            break;
                        case 7:
                            $nTrxCo['07'] = (float) $data->credit;
                            break;
                        case 8:
                            $nTrxCo['08'] = (float) $data->credit;
                            break;
                        case 9:
                            $nTrxCo['09'] = (float) $data->credit;
                            break;
                        case 10:
                            $nTrxCo['10'] = (float) $data->credit;
                            break;
                        case 11:
                            $nTrxCo['11'] = (float) $data->credit;
                            break;
                        case 12:
                            $nTrxCo['12'] = (float) $data->credit;
                            break;
                    }
                }
            }
            $result['transaction_cash_out'][] = $nTrxCo;

            $nTrxCi = [
                'id' => $cashBankId->id,
                'coa_code' => $cashBankId->code,
                'coa_name' => $cashBankId->name,
                '01' => 0,
                '02' => 0,
                '03' => 0,
                '04' => 0,
                '05' => 0,
                '06' => 0,
                '07' => 0,
                '08' => 0,
                '09' => 0,
                '10' => 0,
                '11' => 0,
                '12' => 0,
            ];

            foreach ($dbData as $data) {
                if ($data->id == $cashBankId->id) {
                    switch ($data->month) {
                        case 1:
                            $nTrxCi['01'] = (float) $data->debit;
                            break;
                        case 2:
                            $nTrxCi['02'] = (float) $data->debit;
                            break;
                        case 3:
                            $nTrxCi['03'] = (float) $data->debit;
                            break;
                        case 4:
                            $nTrxCi['04'] = (float) $data->debit;
                            break;
                        case 5:
                            $nTrxCi['05'] = (float) $data->debit;
                            break;
                        case 6:
                            $nTrxCi['06'] = (float) $data->debit;
                            break;
                        case 7:
                            $nTrxCi['07'] = (float) $data->debit;
                            break;
                        case 8:
                            $nTrxCi['08'] = (float) $data->debit;
                            break;
                        case 9:
                            $nTrxCi['09'] = (float) $data->debit;
                            break;
                        case 10:
                            $nTrxCi['10'] = (float) $data->debit;
                            break;
                        case 11:
                            $nTrxCi['11'] = (float) $data->debit;
                            break;
                        case 12:
                            $nTrxCi['12'] = (float) $data->debit;
                            break;
                    }
                }
            }

            $result['transaction_cash_in'][] = $nTrxCi;
        }

        return $result;
    }

    public function cashflowFuncBu($year)
    {
        $result = [
            'transaction_cash_out' => [],
            'transaction_cash_in' => [],
        ];
        $user = Auth::guard('admin')->user();

        $coaKas = Coa::where('branch_id', $user->branch_id)
            ->where(function ($q) {
                $q->where('name', 'KAS')
                    ->orWhere('name', 'Kas')
                    ->orWhere('name', 'kas');
            })
            ->first();

        $cashBankIds = Coa::select('id', 'name', 'code')
            ->where('branch_id', $user->branch_id)
            ->where('parent_id', $coaKas->id)
            ->get();

        foreach ($cashBankIds as $cashBankId) {
            // get data jurnal cash out
            $jurnalCash = DB::table('journal_details')
                ->join('journal_headers', 'journal_headers.id', '=', 'journal_details.header_id')
                ->select(
                    'journal_details.branch_id as branch_id',
                    'journal_headers.transaction_date as transaction_date',
                    'journal_details.debit as debit',
                    'journal_details.credit as credit',
                    'journal_details.original_amount as original_amount',
                )
                ->where('journal_headers.status_id', 13)
                ->where('journal_details.branch_id', $user->branch_id)
//                ->where('journal_details.transaction_type', 'CASH_OUT')
                ->where('journal_details.account_id', $cashBankId->id)->get();
            //            dd($jurnalCash, $cashBankId->id);
            // populate data by month

            $nTrx = [
                'coa_code' => $cashBankId->code,
                'coa_name' => $cashBankId->name,
                '01' => $jurnalCash
                    ->whereBetween('transaction_date',
                        [
                            Carbon::createFromFormat('!d-m-Y', '01-01-'.$year, 'Asia/Jakarta')->startOfMonth()->toDateString(),
                            Carbon::createFromFormat('!d-m-Y', '01-01-'.$year, 'Asia/Jakarta')->endOfMonth()->toDateString(),
                        ])
                    ->sum('debit'),
                '02' => $jurnalCash
                    ->whereBetween('transaction_date',
                        [
                            Carbon::createFromFormat('!d-m-Y', '01-02-'.$year, 'Asia/Jakarta')->startOfMonth()->toDateString(),
                            Carbon::createFromFormat('!d-m-Y', '01-02-'.$year, 'Asia/Jakarta')->endOfMonth()->toDateString(),
                        ])
                    ->sum('debit'),
                '03' => $jurnalCash
                    ->whereBetween('transaction_date',
                        [
                            Carbon::createFromFormat('!d-m-Y', '01-03-'.$year, 'Asia/Jakarta')->startOfMonth()->toDateString(),
                            Carbon::createFromFormat('!d-m-Y', '01-03-'.$year, 'Asia/Jakarta')->endOfMonth()->toDateString(),
                        ])
                    ->sum('debit'),
                '04' => $jurnalCash
                    ->whereBetween('transaction_date',
                        [
                            Carbon::createFromFormat('!d-m-Y', '01-04-'.$year, 'Asia/Jakarta')->startOfMonth()->toDateString(),
                            Carbon::createFromFormat('!d-m-Y', '01-04-'.$year, 'Asia/Jakarta')->endOfMonth()->toDateString(),
                        ])
                    ->sum('debit'),
                '05' => $jurnalCash
                    ->whereBetween('transaction_date',
                        [
                            Carbon::createFromFormat('!d-m-Y', '01-05-'.$year, 'Asia/Jakarta')->startOfMonth()->toDateString(),
                            Carbon::createFromFormat('!d-m-Y', '01-05-'.$year, 'Asia/Jakarta')->endOfMonth()->toDateString(),
                        ])
                    ->sum('debit'),
                '06' => $jurnalCash
                    ->whereBetween('transaction_date',
                        [
                            Carbon::createFromFormat('!d-m-Y', '01-06-'.$year, 'Asia/Jakarta')->startOfMonth()->toDateString(),
                            Carbon::createFromFormat('!d-m-Y', '01-06-'.$year, 'Asia/Jakarta')->endOfMonth()->toDateString(),
                        ])
                    ->sum('debit'),
                '07' => $jurnalCash
                    ->whereBetween('transaction_date',
                        [
                            Carbon::createFromFormat('!d-m-Y', '01-07-'.$year, 'Asia/Jakarta')->startOfMonth()->toDateString(),
                            Carbon::createFromFormat('!d-m-Y', '01-07-'.$year, 'Asia/Jakarta')->endOfMonth()->toDateString(),
                        ])
                    ->sum('credit'),
                '08' => $jurnalCash
                    ->whereBetween('transaction_date',
                        [
                            Carbon::createFromFormat('!d-m-Y', '01-08-'.$year, 'Asia/Jakarta')->startOfMonth()->toDateString(),
                            Carbon::createFromFormat('!d-m-Y', '01-08-'.$year, 'Asia/Jakarta')->endOfMonth()->toDateString(),
                        ])
                    ->sum('credit'),
                '09' => $jurnalCash
                    ->whereBetween('transaction_date',
                        [
                            Carbon::createFromFormat('!d-m-Y', '01-09-'.$year, 'Asia/Jakarta')->startOfMonth()->toDateString(),
                            Carbon::createFromFormat('!d-m-Y', '01-09-'.$year, 'Asia/Jakarta')->endOfMonth()->toDateString(),
                        ])
                    ->sum('credit'),
                '10' => $jurnalCash
                    ->whereBetween('transaction_date',
                        [
                            Carbon::createFromFormat('!d-m-Y', '01-10-'.$year, 'Asia/Jakarta')->startOfMonth()->toDateString(),
                            Carbon::createFromFormat('!d-m-Y', '01-10-'.$year, 'Asia/Jakarta')->endOfMonth()->toDateString(),
                        ])
                    ->sum('credit'),
                '11' => $jurnalCash
                    ->whereBetween('transaction_date',
                        [
                            Carbon::createFromFormat('!d-m-Y', '01-11-'.$year, 'Asia/Jakarta')->startOfMonth()->toDateString(),
                            Carbon::createFromFormat('!d-m-Y', '01-11-'.$year, 'Asia/Jakarta')->endOfMonth()->toDateString(),
                        ])
                    ->sum('credit'),
                '12' => $jurnalCash
                    ->whereBetween('transaction_date',
                        [
                            Carbon::createFromFormat('!d-m-Y', '01-12-'.$year, 'Asia/Jakarta')->startOfMonth()->toDateString(),
                            Carbon::createFromFormat('!d-m-Y', '01-12-'.$year, 'Asia/Jakarta')->endOfMonth()->toDateString(),
                        ])
                    ->sum('credit'),
            ];

            $result['transaction_cash_out'][] = $nTrx;

            // get data jurnal cash in
            //            $jurnalCash = DB::table('journal_details')
            //                ->join('journal_headers', 'journal_headers.id', '=', 'journal_details.header_id')
            //                ->select(
            //                    'journal_details.branch_id as branch_id',
            //                    'journal_headers.transaction_date as transaction_date',
            //                    'journal_headers.notes as notes',
            //                    'journal_details.debit as debit',
            //                    'journal_details.credit as credit',
            //                    'journal_details.original_amount as original_amount',
            //                    'journal_details.transaction_type as transaction_type',
            //                )
            //                ->where('journal_headers.status_id', 13)
            //                ->where('journal_details.branch_id', $user->branch_id)
            // //                ->where(function($q){
            // //                    $q->where('journal_details.transaction_type', 'CASH_IN')
            // //                        ->orWhere('journal_headers.notes', 'like', 'Begining Balance%');
            // //                })
            //                ->where('journal_details.account_id', $cashBankId->id);

            $nTrx = [
                'coa_code' => $cashBankId->code,
                'coa_name' => $cashBankId->name,
                '01' => $jurnalCash
                    ->whereBetween('transaction_date',
                        [
                            Carbon::createFromFormat('!d-m-Y', '01-01-'.$year, 'Asia/Jakarta')->startOfMonth()->toDateString(),
                            Carbon::createFromFormat('!d-m-Y', '01-01-'.$year, 'Asia/Jakarta')->endOfMonth()->toDateString(),
                        ])
                    ->sum('debit'),
                '02' => $jurnalCash
                    ->whereBetween('transaction_date',
                        [
                            Carbon::createFromFormat('!d-m-Y', '01-02-'.$year, 'Asia/Jakarta')->startOfMonth()->toDateString(),
                            Carbon::createFromFormat('!d-m-Y', '01-02-'.$year, 'Asia/Jakarta')->endOfMonth()->toDateString(),
                        ])
                    ->sum('debit'),
                '03' => $jurnalCash
                    ->whereBetween('transaction_date',
                        [
                            Carbon::createFromFormat('!d-m-Y', '01-03-'.$year, 'Asia/Jakarta')->startOfMonth()->toDateString(),
                            Carbon::createFromFormat('!d-m-Y', '01-03-'.$year, 'Asia/Jakarta')->endOfMonth()->toDateString(),
                        ])
                    ->sum('debit'),
                '04' => $jurnalCash
                    ->whereBetween('transaction_date',
                        [
                            Carbon::createFromFormat('!d-m-Y', '01-04-'.$year, 'Asia/Jakarta')->startOfMonth()->toDateString(),
                            Carbon::createFromFormat('!d-m-Y', '01-04-'.$year, 'Asia/Jakarta')->endOfMonth()->toDateString(),
                        ])
                    ->sum('debit'),
                '05' => $jurnalCash
                    ->whereBetween('transaction_date',
                        [
                            Carbon::createFromFormat('!d-m-Y', '01-05-'.$year, 'Asia/Jakarta')->startOfMonth()->toDateString(),
                            Carbon::createFromFormat('!d-m-Y', '01-05-'.$year, 'Asia/Jakarta')->endOfMonth()->toDateString(),
                        ])
                    ->sum('debit'),
                '06' => $jurnalCash
                    ->whereBetween('transaction_date',
                        [
                            Carbon::createFromFormat('!d-m-Y', '01-06-'.$year, 'Asia/Jakarta')->startOfMonth()->toDateString(),
                            Carbon::createFromFormat('!d-m-Y', '01-06-'.$year, 'Asia/Jakarta')->endOfMonth()->toDateString(),
                        ])
                    ->sum('debit'),
                '07' => $jurnalCash
                    ->whereBetween('transaction_date',
                        [
                            Carbon::createFromFormat('!d-m-Y', '01-07-'.$year, 'Asia/Jakarta')->startOfMonth()->toDateString(),
                            Carbon::createFromFormat('!d-m-Y', '01-07-'.$year, 'Asia/Jakarta')->endOfMonth()->toDateString(),
                        ])
                    ->sum('debit'),
                '08' => $jurnalCash
                    ->whereBetween('transaction_date',
                        [
                            Carbon::createFromFormat('!d-m-Y', '01-08-'.$year, 'Asia/Jakarta')->startOfMonth()->toDateString(),
                            Carbon::createFromFormat('!d-m-Y', '01-08-'.$year, 'Asia/Jakarta')->endOfMonth()->toDateString(),
                        ])
                    ->sum('debit'),
                '09' => $jurnalCash
                    ->whereBetween('transaction_date',
                        [
                            Carbon::createFromFormat('!d-m-Y', '01-09-'.$year, 'Asia/Jakarta')->startOfMonth()->toDateString(),
                            Carbon::createFromFormat('!d-m-Y', '01-09-'.$year, 'Asia/Jakarta')->endOfMonth()->toDateString(),
                        ])
                    ->sum('debit'),
                '10' => $jurnalCash
                    ->whereBetween('transaction_date',
                        [
                            Carbon::createFromFormat('!d-m-Y', '01-10-'.$year, 'Asia/Jakarta')->startOfMonth()->toDateString(),
                            Carbon::createFromFormat('!d-m-Y', '01-10-'.$year, 'Asia/Jakarta')->endOfMonth()->toDateString(),
                        ])
                    ->sum('debit'),
                '11' => $jurnalCash
                    ->whereBetween('transaction_date',
                        [
                            Carbon::createFromFormat('!d-m-Y', '01-11-'.$year, 'Asia/Jakarta')->startOfMonth()->toDateString(),
                            Carbon::createFromFormat('!d-m-Y', '01-11-'.$year, 'Asia/Jakarta')->endOfMonth()->toDateString(),
                        ])
                    ->sum('debit'),
                '12' => $jurnalCash
                    ->whereBetween('transaction_date',
                        [
                            Carbon::createFromFormat('!d-m-Y', '01-12-'.$year, 'Asia/Jakarta')->startOfMonth()->toDateString(),
                            Carbon::createFromFormat('!d-m-Y', '01-12-'.$year, 'Asia/Jakarta')->endOfMonth()->toDateString(),
                        ])
                    ->sum('debit'),
            ];

            $result['transaction_cash_in'][] = $nTrx;
        }

        return $result;
    }

    public function trialBalanceFunc()
    {
        $result = [
            'data' => [],
        ];
        $user = Auth::guard('admin')->user();

        $coas = Coa::select('id', 'name', 'code')->where('status_id', 1)
            // ->where('branch_id', $user->branch_id)
//            ->where('is_header', 2)
            ->orderBy('code', 'asc')
            ->get();

        $queryJDetails = 'select c.id, c.code, c.name, sum(jd.debit) as debit, sum(jd.credit) as credit from coas c
            left join journal_details jd on jd.account_id = c.id
            left join journal_headers jh on jh.id = jd.header_id
            where
            jh.branch_id = '.$user->branch_id."
            and jh.status_id = 13
            and jh.notes not like 'Begining Balance%'
            group by c.id, c.code, c.name
            order by c.id;";

        $qDetails = DB::select($queryJDetails);

        // $queryJDetailBeginingBalance = "select c.id, c.code, c.name, sum(jd.debit) as debit, sum(jd.credit) as credit from coas c
        //     left join journal_details jd on jd.account_id = c.id
        //     left join journal_headers jh on jh.id = jd.header_id
        //     where
        //     jh.branch_id = ". $user->branch_id ."
        //     and jh.status_id = 13
        //     and jh.notes like 'Begining Balance%'
        //     group by c.id, c.code, c.name
        //     order by c.id;";

        // $qDetailBB = DB::select($queryJDetailBeginingBalance);

        // dd($qDetails, $qDetailBB);

        foreach ($coas as $coa) {
            $nTrx = [
                'id' => $coa->id,
                'coa_code' => $coa->code,
                'coa_name' => $coa->name,
                'begining_balance' => 0,
                'debit' => 0,
                'credit' => 0,
                'ending_balance' => 0,
            ];

            foreach ($qDetails as $dt) {
                if ($dt->id == $coa->id) {
                    $nTrx['debit'] += $dt->debit == null ? 0 : (float) $dt->debit;
                    $nTrx['credit'] += $dt->credit == null ? 0 : (float) $dt->credit;
                }
            }

            // Calculate initial balance
            $beginingBalance = CoaMonthlyAmount::where('coa_id', $coa->id)
                ->where('month', $startDate->format('m'))
                ->where('year', $startDate->format('Y'))
                ->value('balance_total');

            foreach ($qDetailBB as $bb) {
                if ($bb->id == $coa->id) {
                    $nTrx['begining_balance'] = $bb->debit == 0 ? $bb->credit : $bb->debit;
                    $nTrx['ending_balance'] = $nTrx['begining_balance'] + $nTrx['debit'] - $nTrx['credit'];
                }
            }

            $result['data'][] = $nTrx;
        }

        // dd($result);
        return $result;
    }

    public function trialBalanceFuncBU($startDate)
    {

        $result = [
            'data' => [],
        ];
        $user = Auth::guard('admin')->user();

        $coas = Coa::select('id', 'name', 'code')->where('status_id', 1)
            ->where('branch_id', $user->branch_id)
            ->where('trial_balance', 1)
//            ->where('is_header', 2)
            ->get();
        foreach ($coas as $coa) {
            // get data jurnal cash out
            $jurnalDetails = DB::table('journal_details')
                ->join('journal_headers', 'journal_headers.id', '=', 'journal_details.header_id')
                ->select(
                    'journal_details.branch_id as branch_id',
                    'journal_headers.transaction_code as transaction_code',
                    'journal_headers.transaction_date as transaction_date',
                    'journal_headers.notes as notes',
                    'journal_details.transaction_type as transaction_type',
                    'journal_details.debit as debit',
                    'journal_details.credit as credit',
                    'journal_details.original_amount as original_amount',
                )
                ->where('journal_headers.status_id', 13)
                ->where('journal_details.branch_id', $user->branch_id)
                ->where('journal_details.account_id', $coa->id)
                ->where('journal_headers.notes', 'NOT like', 'Begining Balance%')
                ->get();
            //            dd($jurnalDetails);

            // $jurnalDetailBeginingBalance = DB::table('journal_details')
            //     ->join('journal_headers', 'journal_headers.id', '=', 'journal_details.header_id')
            //     ->select(
            //         'journal_details.branch_id as branch_id',
            //         'journal_headers.transaction_code as transaction_code',
            //         'journal_headers.transaction_date as transaction_date',
            //         'journal_details.transaction_type as transaction_type',
            //         'journal_headers.notes as notes',
            //         'journal_details.debit as debit',
            //         'journal_details.credit as credit',
            //         'journal_details.original_amount as original_amount',
            //     )
            //     ->where('journal_headers.status_id', 13)
            //     ->where('journal_details.branch_id', $user->branch_id)
            //     ->where('journal_headers.notes', 'like', 'Begining Balance%')
            //     ->where('journal_details.account_id', $coa->id)
            //     ->first();
            // $beginingBalance = 0;
            // if(!empty($jurnalDetailBeginingBalance)){
            //     $beginingBalance = $jurnalDetailBeginingBalance->debit == 0 ?
            //         $jurnalDetailBeginingBalance->credit : $jurnalDetailBeginingBalance->debit;
            // }
            // Calculate initial balance
            $beginingBalance = CoaMonthlyAmount::where('coa_id', $coa->id)
                ->where('month', $startDate->format('m'))
                ->where('year', $startDate->format('Y'))
                ->value('balance_total');

            //            $beginingBalance = empty($jurnalDetailBeginingBalance) ? 0 : $jurnalDetailBeginingBalance->original_amount;
            $totalDebit = $jurnalDetails->sum('debit');
            $totalCrebit = $jurnalDetails->sum('credit');
            $endingBalance = ($beginingBalance ?? 0) + $totalDebit - $totalCrebit;
            $nTrx = [
                'id' => $coa->id,
                'coa_code' => $coa->code,
                'coa_name' => $coa->name,
                'begining_balance' => $beginingBalance,
                'debit' => $totalDebit,
                'credit' => $totalCrebit,
                'ending_balance' => $endingBalance,
            ];

            $result['data'][] = $nTrx;
        }

        return $result;
    }

    public function profitLossFunc($startDate, $endDate)
    {

        $result = [
            'data' => [],
        ];
        $user = Auth::guard('admin')->user();

        $coas = Coa::select('id', 'name', 'code')
            ->where('branch_id', $user->branch_id)->where('status_id', 1)->whereIn('type_id', [4, 5])->get();
        foreach ($coas as $coa) {
            // get data jurnal cash out
            $jurnalDetails = DB::table('journal_details')
                ->join('journal_headers', 'journal_headers.id', '=', 'journal_details.header_id')
                ->select(
                    'journal_details.branch_id as branch_id',
                    'journal_headers.transaction_code as transaction_code',
                    'journal_headers.transaction_date as transaction_date',
                    'journal_headers.notes as notes',
                    'journal_details.transaction_type as transaction_type',
                    'journal_details.debit as debit',
                    'journal_details.credit as credit',
                    'journal_details.original_amount as original_amount',
                )
                ->where('journal_headers.status_id', 13)
                ->where('journal_details.branch_id', $user->branch_id)
                ->where('journal_details.account_id', $coa->id)
                ->where('journal_headers.notes', 'NOT like', 'Begining Balance%')
                ->whereBetween('journal_headers.transaction_date', [$startDate, $endDate])
                ->get();

            // $jurnalDetailBeginingBalance = DB::table('journal_details')
            //     ->join('journal_headers', 'journal_headers.id', '=', 'journal_details.header_id')
            //     ->select(
            //         'journal_details.branch_id as branch_id',
            //         'journal_headers.transaction_code as transaction_code',
            //         'journal_headers.transaction_date as transaction_date',
            //         'journal_details.transaction_type as transaction_type',
            //         'journal_details.debit as debit',
            //         'journal_details.credit as credit',
            //         'journal_details.original_amount as original_amount',
            //     )
            //     ->where('journal_headers.status_id', 13)
            //     ->where('journal_details.branch_id', $user->branch_id)
            //     ->where('journal_headers.notes', 'like', 'Begining Balance%')
            //     ->where('journal_details.account_id', $coa->id)
            //     ->first();

            // $beginingBalance = 0;
            // if(!empty($jurnalDetailBeginingBalance)){
            //     $beginingBalance = $jurnalDetailBeginingBalance->debit == 0 ?
            //         $jurnalDetailBeginingBalance->credit : $jurnalDetailBeginingBalance->debit;
            // }
            //            $beginingBalance = empty($jurnalDetailBeginingBalance) ? 0 : $jurnalDetailBeginingBalance->original_amount;

            // Calculate initial balance
            $beginingBalance = CoaMonthlyAmount::where('coa_id', $coa->id)
                ->where('month', $startDate->format('m'))
                ->where('year', $startDate->format('Y'))
                ->value('balance_total');
            $totalDebit = $jurnalDetails->sum('debit');
            $totalCrebit = $jurnalDetails->sum('credit');
            $endingBalance = $beginingBalance + $totalCrebit - $totalDebit;
            $nTrx = [
                'coa_code' => $coa->code,
                'coa_name' => $coa->name,
                'cumulative' => 0,
                'this_period' => $endingBalance,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCrebit,
                'beginning_balance' => $beginingBalance,
            ];

            $result['data'][] = $nTrx;
        }

        return $result;
    }

    public function profitLossHeaderVersionFunc($startDate, $endDate)
    {

        $result = [
            'data' => [],
        ];
        $user = Auth::guard('admin')->user();

        $coas = Coa::select('id', 'name', 'code')
            ->where('branch_id', $user->branch_id)->where('status_id', 1)->whereIn('type_id', [4, 5])->get();
        foreach ($coas as $coa) {
            // get data jurnal cash out
            $jurnalDetails = DB::table('journal_details')
                ->join('journal_headers', 'journal_headers.id', '=', 'journal_details.header_id')
                ->select(
                    'journal_details.branch_id as branch_id',
                    'journal_headers.transaction_code as transaction_code',
                    'journal_headers.transaction_date as transaction_date',
                    'journal_headers.notes as notes',
                    'journal_details.transaction_type as transaction_type',
                    'journal_details.debit as debit',
                    'journal_details.credit as credit',
                    'journal_details.original_amount as original_amount',
                )
                ->where('journal_headers.status_id', 13)
                ->where('journal_details.branch_id', $user->branch_id)
                ->where('journal_details.account_id', $coa->id)
                ->where('journal_headers.notes', 'NOT like', 'Begining Balance%')
                ->whereBetween('journal_headers.transaction_date', [$startDate, $endDate])
                ->get();

            $jurnalDetailBeginingBalance = DB::table('journal_details')
                ->join('journal_headers', 'journal_headers.id', '=', 'journal_details.header_id')
                ->select(
                    'journal_details.branch_id as branch_id',
                    'journal_headers.transaction_code as transaction_code',
                    'journal_headers.transaction_date as transaction_date',
                    'journal_details.transaction_type as transaction_type',
                    'journal_details.debit as debit',
                    'journal_details.credit as credit',
                    'journal_details.original_amount as original_amount',
                )
                ->where('journal_headers.status_id', 13)
                ->where('journal_details.branch_id', $user->branch_id)
                ->where('journal_headers.notes', 'like', 'Begining Balance%')
                ->where('journal_details.account_id', $coa->id)
                ->first();

            $beginingBalance = 0;
            if (! empty($jurnalDetailBeginingBalance)) {
                $beginingBalance = $jurnalDetailBeginingBalance->debit == 0 ?
                    $jurnalDetailBeginingBalance->credit : $jurnalDetailBeginingBalance->debit;
            }
            //            $beginingBalance = empty($jurnalDetailBeginingBalance) ? 0 : $jurnalDetailBeginingBalance->original_amount;
            $totalDebit = $jurnalDetails->sum('debit');
            $totalCrebit = $jurnalDetails->sum('credit');
            $endingBalance = $beginingBalance + $totalCrebit - $totalDebit;
            $nTrx = [
                'coa_code' => $coa->code,
                'coa_name' => $coa->name,
                'cumulative' => 0,
                'this_period' => $endingBalance,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCrebit,
                'beginning_balance' => $beginingBalance,
            ];

            $result['data'][] = $nTrx;
        }

        return $result;
    }

    public function balanceSheetFuncOld($startDate, $endDate)
    {

        $result = [
            'aktiva' => [],
            'pasiva' => [],
        ];
        $user = Auth::guard('admin')->user();

        $coas = Coa::select('id', 'name', 'code', 'level')
            ->where('branch_id', $user->branch_id)->where('status_id', 1)->whereIn('type_id', [1])->orderby('code')->get();

        $currentHeader = 0;
        $currentTotal = 0;
        $startYear = Carbon::createFromFormat('Y-m-d', '2022-01-01');
        $beginningBalanceEndDate = $startDate->copy();
        $beginningBalanceEndDate = $beginningBalanceEndDate->addMinutes(-1);
        foreach ($coas as $i => $coa) {
            // get data jurnal cash out
            $jurnalDetails = DB::table('journal_details')
                ->join('journal_headers', 'journal_headers.id', '=', 'journal_details.header_id')
                ->select(
                    'journal_details.debit as debit',
                    'journal_details.credit as credit',
                )
                ->where('journal_headers.status_id', 13)
                ->where('journal_details.branch_id', $user->branch_id)
                ->where('journal_details.account_id', $coa->id)
                ->whereBetween('journal_headers.transaction_date', [$startDate, $endDate])
                ->get();

            $jurnalDetailBeginingBalanceDebit = DB::table('journal_details')
                ->join('journal_headers', 'journal_headers.id', '=', 'journal_details.header_id')
                ->select(
                    'journal_details.debit as debit',
                    'journal_details.credit as credit',
                )
                ->where('journal_headers.status_id', 13)
                ->where('journal_details.branch_id', $user->branch_id)
                ->where('journal_details.account_id', $coa->id)
                ->whereBetween('journal_headers.transaction_date', [$startYear, $beginningBalanceEndDate])
                ->sum('debit');

            $jurnalDetailBeginingBalanceCredit = DB::table('journal_details')
                ->join('journal_headers', 'journal_headers.id', '=', 'journal_details.header_id')
                ->select(
                    'journal_details.debit as debit',
                    'journal_details.credit as credit',
                )
                ->where('journal_headers.status_id', 13)
                ->where('journal_details.branch_id', $user->branch_id)
                ->where('journal_details.account_id', $coa->id)
                ->whereBetween('journal_headers.transaction_date', [$startYear, $beginningBalanceEndDate])
                ->sum('credit');

            if ($currentHeader != $i && $coa->level == 1) {
                $result['aktiva'][$currentHeader]['total'] = $currentTotal;

                $currentHeader = $i;
                $currentTotal = 0;
            }

            $beginingBalance = $jurnalDetailBeginingBalanceDebit - $jurnalDetailBeginingBalanceCredit;

            $totalDebit = DB::table('journal_details')
                ->join('journal_headers', 'journal_headers.id', '=', 'journal_details.header_id')
                ->select(
                    'journal_details.debit as debit',
                    'journal_details.credit as credit',
                )
                ->where('journal_headers.status_id', 13)
                ->where('journal_details.branch_id', $user->branch_id)
                ->where('journal_details.account_id', $coa->id)
                ->whereBetween('journal_headers.transaction_date', [$startDate, $endDate])
                ->sum('debit');

            $totalCrebit = DB::table('journal_details')
                ->join('journal_headers', 'journal_headers.id', '=', 'journal_details.header_id')
                ->select(
                    'journal_details.debit as debit',
                    'journal_details.credit as credit',
                )
                ->where('journal_headers.status_id', 13)
                ->where('journal_details.branch_id', $user->branch_id)
                ->where('journal_details.account_id', $coa->id)
                ->whereBetween('journal_headers.transaction_date', [$startDate, $endDate])
                ->sum('credit');
            $endingBalance = $beginingBalance + $totalDebit - $totalCrebit;
            $nTrx = [
                'id' => $coa->id,
                'coa_code' => $coa->code,
                'coa_name' => $coa->name,
                'level' => $coa->level,
                'total' => $endingBalance,
            ];

            if ($coa->level > 1) {
                $currentTotal += $endingBalance;
            }

            $result['aktiva'][] = $nTrx;
        }
        $result['aktiva'][$currentHeader]['total'] = $currentTotal;

        $coas2 = Coa::select('id', 'name', 'code', 'level')
            ->where('branch_id', $user->branch_id)->where('status_id', 1)->whereIn('type_id', [2, 3])->orderby('code')->get();

        $currentHeader = 0;
        $currentTotal = 0;
        foreach ($coas2 as $i => $coa) {
            // get data jurnal cash out
            // $jurnalDetails = DB::table('journal_details')
            //     ->join('journal_headers', 'journal_headers.id', '=', 'journal_details.header_id')
            //     ->select(
            //         'journal_details.debit as debit',
            //         'journal_details.credit as credit',
            //     )
            //     ->where('journal_headers.status_id', 13)
            //     ->where('journal_details.branch_id', $user->branch_id)
            //     ->where('journal_details.account_id', $coa->id)
            //     // ->where('journal_headers.notes', 'NOT like', 'Begining Balance%')
            //     ->whereBetween('journal_headers.transaction_date', [$startYear, $endDate])
            //     ->get();

            // $jurnalDetailBeginingBalance = DB::table('journal_details')
            //     ->join('journal_headers', 'journal_headers.id', '=', 'journal_details.header_id')
            //     ->select(
            //         'journal_details.debit as debit',
            //         'journal_details.credit as credit',
            //     )
            //     ->where('journal_headers.status_id', 13)
            //     ->where('journal_details.branch_id', $user->branch_id)
            //     ->where('journal_details.account_id', $coa->id)
            //     ->whereBetween('journal_headers.transaction_date', [$startYear, $beginningBalanceEndDate])
            //     ->get();

            if ($currentHeader != $i && $coa->level == 1) {
                $result['pasiva'][$currentHeader]['total'] = $currentTotal;

                $currentHeader = $i;
                $currentTotal = 0;
            }

            // $beginingBalance = $jurnalDetailBeginingBalance->sum('credit') - $jurnalDetailBeginingBalance->sum('debit');

            $totalDebit = DB::table('journal_details')
                ->join('journal_headers', 'journal_headers.id', '=', 'journal_details.header_id')
                ->select(
                    'journal_details.debit as debit',
                    'journal_details.credit as credit',
                )
                ->where('journal_headers.status_id', 13)
                ->where('journal_details.branch_id', $user->branch_id)
                ->where('journal_details.account_id', $coa->id)
            // ->where('journal_headers.notes', 'NOT like', 'Begining Balance%')
                ->whereBetween('journal_headers.transaction_date', [$startYear, $endDate])
                ->sum('debit');

            $totalCrebit = DB::table('journal_details')
                ->join('journal_headers', 'journal_headers.id', '=', 'journal_details.header_id')
                ->select(
                    'journal_details.debit as debit',
                    'journal_details.credit as credit',
                )
                ->where('journal_headers.status_id', 13)
                ->where('journal_details.branch_id', $user->branch_id)
                ->where('journal_details.account_id', $coa->id)
            // ->where('journal_headers.notes', 'NOT like', 'Begining Balance%')
                ->whereBetween('journal_headers.transaction_date', [$startYear, $endDate])
                ->sum('credit');
            // $endingBalance = $beginingBalance + $totalCrebit - $totalDebit;
            $endingBalance = $totalCrebit - $totalDebit;
            $nTrx = [
                'id' => $coa->id,
                'coa_code' => $coa->code,
                'coa_name' => $coa->name,
                'level' => $coa->level,
                'total' => $endingBalance,
            ];

            if ($coa->level > 1) {
                $currentTotal += $endingBalance;
            }

            $result['pasiva'][] = $nTrx;
        }
        $result['pasiva'][$currentHeader]['total'] = $currentTotal;

        return $result;
    }

    public function balanceSheetFunc($startDate, $endDate)
    {

        $result = [
            'aktiva' => [],
            'pasiva' => [],
        ];
        $user = Auth::guard('admin')->user();

        $coas = Coa::select('id', 'name', 'code', 'level', 'type_id')
            ->where('branch_id', $user->branch_id)
            ->where('status_id', 1)
            ->where('balance_sheet', 1)
            // ->whereIn('type_id', [1,2,3])
            ->orderby('code')->get();

        $coaIds = [];
        foreach ($coas as $coa) {
            $coaIds[] = $coa->id;
        }

        $currentHeaderA = 0;
        $currentTotalA = 0;

        $currentHeaderP = 0;
        $currentTotalP = 0;

        $startYear = Carbon::createFromFormat('Y-m-d', '2022-01-01');
        $beginningBalanceEndDate = $startDate->copy();
        $beginningBalanceEndDate = $beginningBalanceEndDate->addMinutes(-1);

        // $base1 = DB::table('journal_details')
        //     ->join('journal_headers', 'journal_headers.id', '=', 'journal_details.header_id')
        //     ->select(
        //         'journal_details.account_id as id',
        //         'journal_details.debit as debit',
        //         'journal_details.credit as credit',
        //     )
        //     ->where('journal_headers.status_id', 13)
        //     ->where('journal_details.branch_id', $user->branch_id)
        //     ->whereIn('journal_details.account_id', $coaIds)
        //     ->whereBetween('journal_headers.transaction_date', [$startYear, $beginningBalanceEndDate])
        //     ->get();

        $base2 = DB::table('journal_details')
            ->join('journal_headers', 'journal_headers.id', '=', 'journal_details.header_id')
            ->select(
                'journal_details.account_id as id',
                'journal_details.debit as debit',
                'journal_details.credit as credit',
            )
            ->where('journal_headers.status_id', 13)
            ->where('journal_details.branch_id', $user->branch_id)
            ->whereIn('journal_details.account_id', $coaIds)
            ->whereBetween('journal_headers.transaction_date', [$startDate->startofDay(), $endDate->endofDay()])
            ->get();

        $base3 = DB::table('journal_details')
            ->join('journal_headers', 'journal_headers.id', '=', 'journal_details.header_id')
            ->select(
                'journal_details.account_id as id',
                'journal_details.debit as debit',
                'journal_details.credit as credit',
            )
            ->where('journal_headers.status_id', 13)
            ->where('journal_details.branch_id', $user->branch_id)
            ->whereIn('journal_details.account_id', $coaIds)
            // ->where('journal_headers.notes', 'NOT like', 'Begining Balance%')
            ->whereBetween('journal_headers.transaction_date', [$startDate->startofDay(), $endDate->endofDay()])
            ->get();

        $i = 0;
        foreach ($coas as $coa) {
            // get data jurnal cash out

            if ($coa->type_id == 1) {
                // $jurnalDetailBeginingBalanceDebit = 0;
                // $jurnalDetailBeginingBalanceCredit = 0;
                // foreach ($base1 as $d){
                //     if($d->id == $coa->id){
                //         $jurnalDetailBeginingBalanceDebit += $d->debit;
                //         $jurnalDetailBeginingBalanceCredit += $d->credit;
                //     }
                // }

                if ($currentHeaderA != $i && $coa->level == 1) {
                    // $result['aktiva'][$currentHeaderA]['total'] = $currentTotalA;

                    $currentHeaderA = $i;
                    $currentTotalA = 0;
                }

                // $beginingBalance = $jurnalDetailBeginingBalanceDebit - $jurnalDetailBeginingBalanceCredit;

                // Calculate initial balance
                $beginingBalance = CoaMonthlyAmount::where('coa_id', $coa->id)
                    ->where('month', $startDate->format('m'))
                    ->where('year', $startDate->format('Y'))
                    ->value('balance_total');

                $totalDebit = 0;
                $totalCredit = 0;

                foreach ($base2 as $d) {
                    if ($d->id == $coa->id) {
                        $totalDebit += $d->debit;
                        $totalCredit += $d->credit;
                    }
                }

                $endingBalance = $beginingBalance + $totalDebit - $totalCredit;
                $nTrx = [
                    'id' => $coa->id,
                    'coa_code' => $coa->code,
                    'coa_name' => $coa->name,
                    'level' => $coa->level,
                    'total' => $endingBalance,
                    'debit' => $totalDebit,
                    'credit' => $totalCredit,
                ];

                if ($coa->level > 1) {
                    $currentTotalA += $endingBalance;
                }

                $result['aktiva'][] = $nTrx;

                $i++;
            }
        }
        // $result['aktiva'][$currentHeaderA]['total'] = $currentTotalA;

        $i = 0;
        foreach ($coas as $coa) {
            if ($coa->type_id == 2 || $coa->type_id == 3) {
                if ($currentHeaderP != $i && $coa->level == 1) {
                    // $result['pasiva'][$currentHeaderP]['total'] = $currentTotalP;

                    $currentHeaderP = $i;
                    $currentTotalP = 0;
                }

                // $beginingBalance = $jurnalDetailBeginingBalance->sum('credit') - $jurnalDetailBeginingBalance->sum('debit');

                // Calculate initial balance
                $beginingBalance = CoaMonthlyAmount::where('coa_id', $coa->id)
                    ->where('month', $startDate->format('m'))
                    ->where('year', $startDate->format('Y'))
                    ->value('balance_total');

                $totalDebit = 0;
                $totalCredit = 0;

                foreach ($base3 as $d) {
                    if ($d->id == $coa->id) {
                        $totalDebit += $d->debit;
                        $totalCredit += $d->credit;
                    }
                }

                $endingBalance = $beginingBalance + $totalCredit - $totalDebit;
                // $endingBalance = $totalCredit - $totalDebit;
                $nTrx = [
                    'id' => $coa->id,
                    'coa_code' => $coa->code,
                    'coa_name' => $coa->name,
                    'level' => $coa->level,
                    'total' => $endingBalance,
                    'debit' => $totalDebit,
                    'credit' => $totalCredit,
                ];

                if ($coa->level > 1) {
                    $currentTotalP += $endingBalance;
                }

                $result['pasiva'][] = $nTrx;

                $i++;
            }
        }
        // $result['pasiva'][$currentHeaderP]['total'] = $currentTotalP;

        // dd($result);

        return $result;
    }

    public function balanceSheetFuncNew(Carbon $startDate, Carbon $endDate)
    {

        $result = [
            'aktiva' => [],
            'pasiva' => [],
        ];
        $user = Auth::guard('admin')->user();

        $coas = Coa::select('id', 'name', 'code', 'level', 'parent_code')
            ->where('branch_id', $user->branch_id)
            ->where('status_id', 1)
            ->where('type_id', 1)
            ->orderby('code')
            ->get();

        $configBeginningDate = DB::table('configurations')
            ->where('branch_id', '=', $user->branch_id)
            ->where('key', '=', 'balance_sheet_beginning_date')
            ->first();

        $beginningDate = Carbon::parse($configBeginningDate->value);
        // if($endDate->lessThanOrEqualTo($beginningDate)){
        //     $endDate = $beginningDate->copy()->addMonthNoOverflow();
        // }

        $beginningBalanceDate = $beginningDate->copy();
        foreach ($coas as $i => $coa) {
            // $tmpCoaCurrentDb = DB::table('coa_monthly_amounts')
            //     ->where('branch_id', '=', $user->branch_id)
            //     ->where('coa_id', '=', $coa->id)
            //     ->where('date', '>=', $beginningBalanceDate->toDateTimeString())
            //     ->where('date', '<=', $endDate->toDateTimeString())
            //     ->get();

            // $totalDebit = 0;
            // $totalCredit = 0;
            // if(!empty($tmpCoaCurrentDb)){
            //     $totalDebit = $tmpCoaCurrentDb->sum('debit_add');
            //     $totalCredit = $tmpCoaCurrentDb->sum('credit_add');
            // }

            $tmpCoaCurrentDb = DB::table('coa_monthly_amounts')
                ->where('branch_id', '=', $user->branch_id)
                ->where('coa_id', '=', $coa->id)
                ->where('date', '>=', $beginningBalanceDate->toDateTimeString())
                ->where('date', '<=', $endDate->toDateTimeString())
                ->orderByDesc('date')
                ->first();

            $totalDebit = 0;
            $totalCredit = 0;
            if (! empty($tmpCoaCurrentDb)) {
                $totalDebit = $tmpCoaCurrentDb->debit_total;
                $totalCredit = $tmpCoaCurrentDb->credit_total;
            }

            $endingBalance = 0;
            $viewBalance = 0;
            if ($coa->level == 1) {
                $coaChildrens = DB::table('coas')
                    ->where('branch_id', '=', $user->branch_id)
                    ->where('parent_id', '=', $coa->id)
                    ->get();

                if (! empty($coaChildrens)) {
                    foreach ($coaChildrens as $coaChildren) {
                        $tmpCoaChildrenBalance = DB::table('coa_monthly_amounts')
                            ->where('branch_id', '=', $user->branch_id)
                            ->where('coa_id', $coaChildren->id)
                            ->where('date', '>=', $beginningBalanceDate->toDateTimeString())
                            ->where('date', '<=', $endDate->toDateTimeString())
                            ->orderBy('date', 'desc')
                            ->first();

                        if (! empty($tmpCoaChildrenBalance)) {
                            $totalBalance = $tmpCoaChildrenBalance->debit_total - $tmpCoaChildrenBalance->credit_total;
                            $viewBalance += $totalBalance;
                        }
                    }
                }

                if ($totalDebit != 0 || $totalCredit != 0) {
                    $endingBalance = $totalDebit - $totalCredit;
                    $viewBalance += $endingBalance;
                }
            } else {
                $endingBalance = $totalDebit - $totalCredit;
                $viewBalance = $endingBalance;
            }

            $nTrx = [
                'id' => $coa->id,
                'coa_code' => $coa->code,
                'coa_name' => $coa->name,
                'coa_parent_code' => $coa->parent_code,
                'level' => $coa->level,
                'total' => $endingBalance,
                'balance' => $viewBalance,
            ];

            $result['aktiva'][] = $nTrx;
        }

        $coas2 = Coa::select('id', 'name', 'code', 'level', 'parent_code')
            ->where('branch_id', $user->branch_id)
            ->where('status_id', 1)
            ->whereIn('type_id', [2, 3])
            ->orderby('code')
            ->get();

        foreach ($coas2 as $i => $coa) {
            // $tmpCoaCurrentDb = DB::table('coa_monthly_amounts')
            //     ->where('branch_id', '=', $user->branch_id)
            //     ->where('coa_id', '=', $coa->id)
            //     ->where('date', '>=', $beginningBalanceDate->toDateTimeString())
            //     ->where('date', '<=', $endDate->toDateTimeString())
            //     ->get();

            $tmpCoaCurrentDb = DB::table('coa_monthly_amounts')
                ->where('branch_id', '=', $user->branch_id)
                ->where('coa_id', '=', $coa->id)
                ->where('date', '>=', $beginningBalanceDate->toDateTimeString())
                ->where('date', '<=', $endDate->toDateTimeString())
                ->orderByDesc('date')
                ->first();

            $totalDebit = 0;
            $totalCredit = 0;
            if (! empty($tmpCoaCurrentDb)) {
                $totalDebit = $tmpCoaCurrentDb->debit_total;
                $totalCredit = $tmpCoaCurrentDb->credit_total;
            }

            $endingBalance = 0;
            $viewBalance = 0;
            if ($coa->level == 1) {
                // $coaChildrens = DB::table('coas')
                //     ->where('branch_id', '=', $user->branch_id)
                //     ->where('parent_id', '=', $coa->id)
                //     ->pluck('id')
                //     ->toArray();

                // if(!empty($coaChildrens)){
                //     $tmpCoaChildrenBalance = DB::table('coa_monthly_amounts')
                //         ->selectRaw('SUM(debit_total) as debit_total, SUM(credit_total) as credit_total, (SUM(credit_total) - SUM(debit_total)) as total_balance')
                //         ->where('branch_id', '=', $user->branch_id)
                //         ->whereIn('coa_id', $coaChildrens)
                //         ->where('date', '>=', $beginningBalanceDate->toDateTimeString())
                //         ->where('date', '<=', $endDate->toDateTimeString())
                //         ->first();

                //     if(!empty($tmpCoaChildrenBalance)){
                //         $viewBalance = $tmpCoaChildrenBalance->total_balance ?? 0;
                //     }
                // }

                $coaChildrens = DB::table('coas')
                    ->where('branch_id', '=', $user->branch_id)
                    ->where('parent_id', '=', $coa->id)
                    ->get();

                if (! empty($coaChildrens)) {
                    foreach ($coaChildrens as $coaChildren) {
                        $tmpCoaChildrenBalance = DB::table('coa_monthly_amounts')
                            ->where('branch_id', '=', $user->branch_id)
                            ->where('coa_id', $coaChildren->id)
                            ->where('date', '>=', $beginningBalanceDate->toDateTimeString())
                            ->where('date', '<=', $endDate->toDateTimeString())
                            ->orderBy('date', 'desc')
                            ->first();

                        if (! empty($tmpCoaChildrenBalance)) {
                            $totalBalance = $tmpCoaChildrenBalance->credit_total - $tmpCoaChildrenBalance->debit_total;
                            $viewBalance += $totalBalance;
                        }
                    }
                }

                if ($totalDebit != 0 || $totalCredit != 0) {
                    $endingBalance = $totalCredit - $totalDebit;
                    $viewBalance += $endingBalance;
                }
            } else {
                $endingBalance = $totalCredit - $totalDebit;
                $viewBalance = $endingBalance;
            }

            $nTrx = [
                'id' => $coa->id,
                'coa_code' => $coa->code,
                'coa_name' => $coa->name,
                'coa_parent_code' => $coa->parent_code,
                'level' => $coa->level,
                'total' => $endingBalance,
                'balance' => $viewBalance,
            ];

            $result['pasiva'][] = $nTrx;
        }

        // dd($result);

        return $result;
    }

    public function ledgerFunc($startDate, $endDate, $coaId)
    {
        try {
            $user = Auth::guard('admin')->user();
            $coas = Coa::select('id', 'name', 'code')
                ->where('branch_id', $user->branch_id);
            if ($coaId == 0) {
                $coas = $coas
                    ->where('status_id', 1)
                    ->get();
            } else {
                $coas = $coas
                    ->where('id', $coaId)
                    ->get();
            }
            $nResult = [];

            $startYear = Carbon::createFromFormat('Y-m-d', '2025-01-01');
            $beginningBalanceEndDate = $startDate->copy();
            $beginningBalanceEndDate = $beginningBalanceEndDate->startofMonth();

            $sqlJd = 'select jh.id as id, jh.branch_id as branch_id, jd.account_id as acc_id, jd.account_code as account_code,
                    jh.transaction_code as transaction_code,
                    jh.transaction_date as transaction_date, jd.reference_code as reference_code,
                    jd.transaction_type as transaction_type,
                    jd.debit as debit, jd.credit as credit, jd.notes as notes from coas c
                    inner join journal_details jd on jd.account_id = c.id
                    inner join journal_headers jh on jh.id = jd.header_id
                    where
                    jh.status_id = 13
                    and jh.branch_id = '.$user->branch_id."
                    and jh.transaction_date >= '".$startDate->format('Y-m-d H:i:s')."'
                    and jh.transaction_date <= '".$endDate->format('Y-m-d H:i:s')."';";
            $journalDetails = DB::select($sqlJd);

            foreach ($coas as $coa) {
                $result = [
                    'coa_code' => $coa->code,
                    'coa_name' => $coa->name,
                    'data' => [],
                ];

                // get data jurnal cash out
                $journalDetailData = collect($journalDetails)->filter(function ($item) use ($coa) {
                    return $item->acc_id == $coa->id;
                });


                // Calculate initial balance
                $currentSaldo = CoaMonthlyAmount::where('coa_id', $coa->id)
                    ->where('month', $beginningBalanceEndDate->format('m'))
                    ->where('year', $beginningBalanceEndDate->format('Y'))
                    ->value('balance_total');

                $nTrx = [
                    'transaction_code' => '',
                    'transaction_date' => Carbon::parse($beginningBalanceEndDate)->locale('id_ID')->format('d-m-Y'),
                    'reference_code' => '',
                    'debit' => 0,
                    'credit' => 0,
                    'notes' => 'Saldo Awal',
                    'saldo' => $currentSaldo,
                ];

                $result['data'][] = $nTrx;

                foreach ($journalDetailData as $jurnalDetail) {
                    if (str_starts_with($coa->code, '1') || str_starts_with($coa->code, '5') || str_starts_with($coa->code, '6') || str_starts_with($coa->code, '9')) {
                        $value = (($jurnalDetail->debit ?? 0) - ($jurnalDetail->credit ?? 0));
                    } else {
                        $value = (($jurnalDetail->credit ?? 0) - ($jurnalDetail->debit ?? 0));
                    }
                    $currentSaldo += $value;
                    $nTrx = [
                        'transaction_code' => $jurnalDetail->transaction_code,
                        'transaction_date' => Carbon::parse($jurnalDetail->transaction_date)->locale('id_ID')->format('d-m-Y'),
                        'reference_code' => $jurnalDetail->reference_code,
                        'debit' => $jurnalDetail->debit,
                        'credit' => $jurnalDetail->credit,
                        'notes' => $jurnalDetail->notes,
                        'saldo' => $currentSaldo,
                    ];

                    $result['data'][] = $nTrx;
                }
                $nResult[] = $result;
            }

            // dd($nResult);

            return $nResult;
        } catch (\Exception $ex) {
            Log::error('Error in ledgerFunc: ' . $ex->getMessage(), [
                'exception' => $ex,
                'trace' => $ex->getTraceAsString(),
            ]);
            return [];
        }
    }

    public function ledgerFuncV1($startDate, $endDate, $coaId)
    {
        $user = Auth::guard('admin')->user();
        $coas = Coa::select('id', 'name', 'code')
            ->where('branch_id', $user->branch_id);
        if ($coaId == 0) {
            $coas = $coas
                ->where('status_id', 1)
                ->whereIn('level', [2, 3])->get();
        } else {
            $coas = $coas
                ->where('id', $coaId)
                ->get();
        }
        $nResult = [];

        $startYear = Carbon::createFromFormat('Y-m-d', '2022-01-01');
        $beginningBalanceEndDate = $startDate->copy();
        $beginningBalanceEndDate = $beginningBalanceEndDate->addMinutes(-1);
        foreach ($coas as $coa) {
            $result = [
                'coa_code' => $coa->code,
                'coa_name' => $coa->name,
                'data' => [],
            ];

            // get data jurnal cash out
            $jurnalDetails = DB::table('journal_details')
                ->join('journal_headers', 'journal_headers.id', '=', 'journal_details.header_id')
                ->select(
                    'journal_headers.id as id',
                    'journal_details.branch_id as branch_id',
                    'journal_headers.transaction_code as transaction_code',
                    'journal_headers.transaction_date as transaction_date',
                    'journal_details.reference_code as reference_code',
                    'journal_details.transaction_type as transaction_type',
                    'journal_details.debit as debit',
                    'journal_details.credit as credit',
                    'journal_details.notes as notes',
                )
                ->where('journal_headers.status_id', 13)
                ->where('journal_details.branch_id', $user->branch_id)
                ->where('journal_details.account_id', $coa->id)
                ->whereBetween('journal_headers.transaction_date', [$startDate, $endDate])
                ->orderBy('journal_headers.transaction_date')
                ->get();

            $jurnalDetailBeginingBalance = DB::table('journal_details')
                ->join('journal_headers', 'journal_headers.id', '=', 'journal_details.header_id')
                ->select(
                    'journal_headers.id as id',
                    'journal_details.branch_id as branch_id',
                    'journal_headers.transaction_code as transaction_code',
                    'journal_headers.transaction_date as transaction_date',
                    'journal_details.reference_code as reference_code',
                    'journal_details.transaction_type as transaction_type',
                    'journal_details.debit as debit',
                    'journal_details.credit as credit',
                    'journal_details.notes as notes',
                )
                ->where('journal_headers.status_id', 13)
                ->where('journal_details.branch_id', $user->branch_id)
                ->where('journal_details.account_id', $coa->id)
                ->whereBetween('journal_headers.transaction_date', [$startYear, $beginningBalanceEndDate])
                ->orderBy('journal_headers.transaction_date')
                ->get();

            $currentSaldo = 0;
            if (str_starts_with($coa->code, '1') || str_starts_with($coa->code, '5') || str_starts_with($coa->code, '6') || str_starts_with($coa->code, '9')) {
                $currentSaldo = $jurnalDetailBeginingBalance->sum('debit') - $jurnalDetailBeginingBalance->sum('credit');
            } else {
                $currentSaldo = $jurnalDetailBeginingBalance->sum('credit') - $jurnalDetailBeginingBalance->sum('debit');
            }
            $nTrx = [
                'transaction_code' => '',
                'transaction_date' => Carbon::parse($beginningBalanceEndDate)->locale('id_ID')->format('d-m-Y'),
                'reference_code' => '',
                'debit' => 0,
                'credit' => 0,
                'notes' => 'Saldo Awal',
                'saldo' => $currentSaldo,
            ];

            $result['data'][] = $nTrx;

            foreach ($jurnalDetails as $jurnalDetail) {
                if (str_starts_with($coa->code, '1') || str_starts_with($coa->code, '5') || str_starts_with($coa->code, '6') || str_starts_with($coa->code, '9')) {
                    // $value = (($detail->credit ?? 0) - ($detail->debit ?? 0));
                    $value = (($jurnalDetail->debit ?? 0) - ($jurnalDetail->credit ?? 0));
                } else {
                    // $value = abs(($detail->debit ?? 0) - ($detail->credit ?? 0));
                    $value = (($jurnalDetail->credit ?? 0) - ($jurnalDetail->debit ?? 0));
                }
                $currentSaldo += $value;
                $nTrx = [
                    'transaction_code' => $jurnalDetail->transaction_code,
                    'transaction_date' => Carbon::parse($jurnalDetail->transaction_date)->locale('id_ID')->format('d-m-Y'),
                    'reference_code' => $jurnalDetail->reference_code,
                    'debit' => $jurnalDetail->debit,
                    'credit' => $jurnalDetail->credit,
                    'notes' => $jurnalDetail->notes,
                    'saldo' => $currentSaldo,
                ];

                $result['data'][] = $nTrx;
            }
            $nResult[] = $result;
        }

        return $nResult;
    }

    public function ledgerFuncFromBalanceSheet(Carbon $date, $coaId)
    {

        try {
            $user = Auth::guard('admin')->user();
            $coas = Coa::select('id', 'name', 'code', 'type_id')
                ->where('branch_id', $user->branch_id)
                ->where('id', $coaId)
                ->get();

            $configBeginningDate = DB::table('configurations')
                ->where('branch_id', '=', $user->branch_id)
                ->where('key', '=', 'balance_sheet_beginning_date')
                ->first();

            $beginningDate = Carbon::parse($configBeginningDate->value);

            $startDateReadJournal = $beginningDate->copy()->addMonthNoOverflow();
            $endDate = $date->copy()->subMinutes(1);

            $nResult = [];
            foreach ($coas as $coa) {
                $result = [
                    'coa_code' => $coa->code,
                    'coa_name' => $coa->name,
                    'data' => [],
                ];

                $tmpCoaBeginningBalance = DB::table('coa_monthly_amounts')
                    ->where('branch_id', '=', $user->branch_id)
                    ->where('coa_id', '=', $coa->id)
                    ->where('month', '=', $beginningDate->month)
                    ->where('year', '=', $beginningDate->year)
                    ->first();

                $currentBalance = 0;
                if (! empty($tmpCoaBeginningBalance)) {
                    $currentBalance = $coa->type_id == 1 ? $tmpCoaBeginningBalance->debit_total : $tmpCoaBeginningBalance->credit_total;

                    $nTrx = [
                        'transaction_code' => '',
                        'transaction_date' => $beginningDate->locale('id_ID')->format('d-m-Y'),
                        'reference_code' => '',
                        'debit' => 0,
                        'credit' => 0,
                        'notes' => 'Saldo Awal',
                        'saldo' => $currentBalance,
                    ];

                    $result['data'][] = $nTrx;
                }

                if ($endDate->month == $beginningDate->month &&
                    $endDate->year == $beginningDate->year) {
                    $nResult[] = $result;

                    continue;
                }

                // get data jurnal cash out
                $jurnalDetails = DB::table('journal_details')
                    ->join('journal_headers', 'journal_headers.id', '=', 'journal_details.header_id')
                    ->select(
                        'journal_headers.id as id',
                        'journal_details.branch_id as branch_id',
                        'journal_headers.transaction_code as transaction_code',
                        'journal_headers.transaction_date as transaction_date',
                        'journal_details.reference_code as reference_code',
                        'journal_details.transaction_type as transaction_type',
                        'journal_details.debit as debit',
                        'journal_details.credit as credit',
                        'journal_details.notes as notes',
                    )
                    ->where('journal_headers.status_id', 13)
                    ->where('journal_details.branch_id', $user->branch_id)
                    ->where('journal_details.account_id', $coa->id)
                    ->whereBetween('journal_headers.transaction_date', [$startDateReadJournal->toDateTimeString(), $endDate->toDateTimeString()])
                    ->orderBy('journal_headers.transaction_date')
                    ->get();

                foreach ($jurnalDetails as $jurnalDetail) {
                    if (str_starts_with($coa->code, '1') || str_starts_with($coa->code, '5') || str_starts_with($coa->code, '6') || str_starts_with($coa->code, '9')) {
                        $value = (($jurnalDetail->debit ?? 0) - ($jurnalDetail->credit ?? 0));
                    } else {
                        $value = (($jurnalDetail->credit ?? 0) - ($jurnalDetail->debit ?? 0));
                    }
                    $currentBalance += $value;
                    $nTrx = [
                        'transaction_code' => $jurnalDetail->transaction_code,
                        'transaction_date' => Carbon::parse($jurnalDetail->transaction_date)->locale('id_ID')->format('d-m-Y'),
                        'reference_code' => $jurnalDetail->reference_code,
                        'debit' => $jurnalDetail->debit,
                        'credit' => $jurnalDetail->credit,
                        'notes' => $jurnalDetail->notes,
                        'saldo' => $currentBalance,
                    ];

                    $result['data'][] = $nTrx;
                }
                $nResult[] = $result;
            }

            return $nResult;
        } catch (\Exception $ex) {
            Log::channel('general_ledger')->error('GeneralLedgerController - ledgerFuncFromBalanceSheet error ex: '.$ex);
            dd($ex);
        }

    }

    public function ledgerFuncNew(Carbon $startDate, Carbon $endDate, $coaId)
    {

        try {
            $user = Auth::guard('admin')->user();
            $coas = Coa::select('id', 'name', 'code', 'type_id')
                ->where('branch_id', $user->branch_id);

            if (! empty($coaId)) {
                $coas = $coas->where('id', $coaId);
            }

            $coas = $coas->get();

            $configBeginningDate = DB::table('configurations')
                ->where('branch_id', '=', $user->branch_id)
                ->where('key', '=', 'balance_sheet_beginning_date')
                ->first();

            $beginningDate = Carbon::parse($configBeginningDate->value);

            $startDateReadJournal = $beginningDate->copy()->addMonthNoOverflow();

            $tmpEndDate = $endDate->copy()->addMonthNoOverflow();
            if ($tmpEndDate->lessThanOrEqualTo($startDateReadJournal)) {
                return [];
            }

            $nResult = [];
            foreach ($coas as $coa) {
                $result = [
                    'coa_code' => $coa->code,
                    'coa_name' => $coa->name,
                    'data' => [],
                ];

                if ($startDate->lessThan($startDateReadJournal)) {
                    $tmpCoaPreviousBalance = DB::table('coa_monthly_amounts')
                        ->where('branch_id', '=', $user->branch_id)
                        ->where('coa_id', '=', $coa->id)
                        ->where('month', '=', $beginningDate->month)
                        ->where('year', '=', $beginningDate->year)
                        ->first();

                    $startDate = $startDateReadJournal;
                } else {
                    $tmpPreviousBalanceDate = $startDate->copy()->subMonthNoOverflow();
                    $tmpCoaPreviousBalance = DB::table('coa_monthly_amounts')
                        ->where('branch_id', '=', $user->branch_id)
                        ->where('coa_id', '=', $coa->id)
                        ->where('month', '=', $tmpPreviousBalanceDate->month)
                        ->where('year', '=', $tmpPreviousBalanceDate->year)
                        ->first();
                }

                $currentBalance = 0;
                if (! empty($tmpCoaPreviousBalance)) {
                    $currentBalance = $coa->type_id == 1 ? $tmpCoaPreviousBalance->debit_total : $tmpCoaPreviousBalance->credit_total;

                    $nTrx = [
                        'transaction_code' => '',
                        'transaction_date' => $beginningDate->locale('id_ID')->format('d-m-Y'),
                        'reference_code' => '',
                        'debit' => 0,
                        'credit' => 0,
                        'notes' => 'Saldo Awal',
                        'saldo' => $currentBalance,
                    ];

                    $result['data'][] = $nTrx;
                }

                if ($endDate->lessThan($startDateReadJournal)) {
                    $nResult[] = $result;

                    continue;
                }

                // get data jurnal
                $jurnalDetails = DB::table('journal_details')
                    ->join('journal_headers', 'journal_headers.id', '=', 'journal_details.header_id')
                    ->select(
                        'journal_headers.id as id',
                        'journal_details.branch_id as branch_id',
                        'journal_headers.transaction_code as transaction_code',
                        'journal_headers.transaction_date as transaction_date',
                        'journal_details.reference_code as reference_code',
                        'journal_details.transaction_type as transaction_type',
                        'journal_details.debit as debit',
                        'journal_details.credit as credit',
                        'journal_details.notes as notes',
                    )
                    ->where('journal_headers.status_id', 13)
                    ->where('journal_details.branch_id', $user->branch_id)
                    ->where('journal_details.account_id', $coa->id)
                    ->whereBetween('journal_headers.transaction_date', [$startDate->toDateTimeString(), $endDate->toDateTimeString()])
                    ->orderBy('journal_headers.transaction_date')
                    ->get();

                foreach ($jurnalDetails as $jurnalDetail) {
                    if (str_starts_with($coa->code, '1') || str_starts_with($coa->code, '5') || str_starts_with($coa->code, '6') || str_starts_with($coa->code, '9')) {
                        $value = (($jurnalDetail->debit ?? 0) - ($jurnalDetail->credit ?? 0));
                    } else {
                        $value = (($jurnalDetail->credit ?? 0) - ($jurnalDetail->debit ?? 0));
                    }
                    $currentBalance += $value;
                    $nTrx = [
                        'transaction_code' => $jurnalDetail->transaction_code,
                        'transaction_date' => Carbon::parse($jurnalDetail->transaction_date)->locale('id_ID')->format('d-m-Y'),
                        'reference_code' => $jurnalDetail->reference_code,
                        'debit' => $jurnalDetail->debit,
                        'credit' => $jurnalDetail->credit,
                        'notes' => $jurnalDetail->notes,
                        'saldo' => $currentBalance,
                    ];

                    $result['data'][] = $nTrx;
                }
                $nResult[] = $result;
            }

            return $nResult;
        } catch (\Exception $ex) {
            Log::channel('general_ledger')->error('GeneralLedgerController - ledgerFuncNew error ex: '.$ex);
            dd($ex);
        }

    }

    public function profitAndLossFunc($startDate, $endDate)
    {
        $period = CarbonPeriod::create(
            $startDate->copy()->startOfMonth(),
            '1 month',
            $endDate->copy()->startOfMonth()
        );

        $months = [];
        foreach ($period as $date) {
            $months[] = [
                'month' => $date->format('m'),
                'year' => $date->format('Y'),
                'label' => $date->translatedFormat('F Y'), // Januari 2025
            ];
        }
        $result = [
            'months' => $months,
            'pendapatan' => ['datas' => [], 'total' => []],
            'beban' => ['datas' => [], 'total' => []],
            'biaya' => ['datas' => [], 'total' => []],
            'pendapatan_biaya_lainnya' => ['datas' => [], 'total' => []],
            'laba_kotor' => [],
            'laba_bersih_operasi' => [],
            'laba_bersih' => [],
        ];
        $user = Auth::guard('admin')->user();

        // aktiva section
        $coas = Coa::select('id', 'name', 'code', 'level', 'parent_code')
            ->where('status_id', 1)
            ->where('profit_loss', 1)
            ->orderby('code')->get();

        foreach ($coas as $coa) {

            $monthlyTotals = [];

            foreach ($months as $m) {

                $monthStart = Carbon::create($m['year'], $m['month'], 1)->startOfMonth();
                $monthEnd = Carbon::create($m['year'], $m['month'], 1)->endOfMonth();

                $jurnalDetails = DB::table('journal_details')
                    ->join('journal_headers', 'journal_headers.id', '=', 'journal_details.header_id')
                    ->where('journal_headers.status_id', 13)
                    ->where('journal_details.branch_id', $user->branch_id)
                    ->where('journal_details.account_id', $coa->id)
                    ->whereBetween('journal_headers.transaction_date', [$monthStart, $monthEnd])
                    ->select('journal_details.debit', 'journal_details.credit')
                    ->get();

                $totalDebit = $jurnalDetails->sum('debit');
                $totalCredit = $jurnalDetails->sum('credit');

                $beginingBalance = CoaMonthlyAmount::where('coa_id', $coa->id)
                    ->where('month', $m['month'])
                    ->where('year', $m['year'])
                    ->value('balance_total') ?? 0;

                $beginingBalance = 0;

                // inisialisasi total per bulan
                if (! isset($result['pendapatan']['total'][$m['label']])) {
                    $result['pendapatan']['total'][$m['label']] = 0;
                    $result['biaya']['total'][$m['label']] = 0;
                    $result['pendapatan_biaya_lainnya']['total'][$m['label']] = 0;
                }

                if (strpos($coa->code, '4') === 0) {
                    $balance = $beginingBalance + $totalCredit - $totalDebit;
                    $result['pendapatan']['total'][$m['label']] += $balance;
                }

                if (strpos($coa->code, '5') === 0 || strpos($coa->code, '6') === 0 || strpos($coa->code, '7') === 0) {
                    $balance = $beginingBalance + $totalDebit - $totalCredit;
                    $result['biaya']['total'][$m['label']] += $balance;
                }

                if (strpos($coa->code, '8') === 0) {
                    $balance = $beginingBalance + $totalDebit - $totalCredit;
                    $result['pendapatan_biaya_lainnya']['total'][$m['label']] += $balance;
                }

                $monthlyTotals[$m['label']] = $balance;
            }

            $nTrx = [
                'id' => $coa->id,
                'coa_code' => $coa->code,
                'coa_name' => $coa->name,
                'monthly' => $monthlyTotals,
            ];

            if (strpos($coa->code, '4') === 0) {
                $result['pendapatan']['datas'][] = $nTrx;
            }

            if (strpos($coa->code, '5') === 0 || strpos($coa->code, '6') === 0 || strpos($coa->code, '7') === 0) {
                $result['biaya']['datas'][] = $nTrx;
            }

            if (strpos($coa->code, '8') === 0) {
                $result['pendapatan_biaya_lainnya']['datas'][] = $nTrx;
            }
        }

        foreach ($months as $m) {
            $label = $m['label'];

            $pendapatan = $result['pendapatan']['total'][$label] ?? 0;
            $beban = $result['beban']['total'][$label] ?? 0;
            $biaya = $result['biaya']['total'][$label] ?? 0;
            $lainnya = $result['pendapatan_biaya_lainnya']['total'][$label] ?? 0;

            $result['laba_kotor'][$label] = $pendapatan - $beban;
            $result['laba_bersih_operasi'][$label] = $result['laba_kotor'][$label] - $biaya;
            $result['laba_bersih'][$label] = $result['laba_bersih_operasi'][$label] - $lainnya;
        }

        return $result;
    }

    public function profitAndLossNew($start, $end, $year, $isReal)
    {
        $user = Auth::guard('admin')->user();

        $coas = Coa::where('profit_loss', 1)
            // ->whereIn('type_id', [4,5])
            ->where('parent_id', null)
            ->where('status_id', 1)
            ->orderby('code')
            ->get();

        $coaWithParentId = Coa::where('parent_id', '!=', 0)
            ->where('profit_loss', 1)
            ->where('status_id', 1)
            ->get();

        if ($isReal) {
            $details = JournalDetail::select('journal_headers.transaction_date', 'journal_details.account_id', 'journal_details.debit', 'journal_details.credit')
                ->leftJoin('journal_headers', 'journal_details.header_id', '=', 'journal_headers.id')
                ->leftJoin('coas', 'journal_details.account_id', '=', 'coas.id')
                ->whereYear('journal_headers.transaction_date', $year)
                ->whereMonth('journal_headers.transaction_date', '>=', $start)
                ->whereMonth('journal_headers.transaction_date', '<=', $end)
                ->where('journal_headers.status_id', 13)
                ->where('journal_details.branch_id', $user->branch_id)
                ->where('coas.profit_loss', 1)
                ->orderBy('journal_details.account_type_id')
                ->get();

            $range = [];
            $period = $start->copy();
            while ($period <= $end) {
                $range[] = (int) $period->format('n');
                $period->addMonth();
            }
        } else {
            $details = JournalDetail::select('journal_headers.transaction_date', 'journal_details.account_id', 'journal_details.debit', 'journal_details.credit')
                ->leftJoin('journal_headers', 'journal_details.header_id', '=', 'journal_headers.id')
                ->leftJoin('coas', 'journal_details.account_id', '=', 'coas.id')
                ->where('journal_headers.transaction_date', '>=', $start)
                ->where('journal_headers.transaction_date', '<=', $end)
                ->where('journal_headers.status_id', 13)
                ->where('journal_details.branch_id', $user->branch_id)
                ->where('coas.profit_loss', 1)
                ->orderBy('journal_details.account_type_id')
                ->get();

            $range = [];
            $month = (int) date('n', strtotime($start));
            $range[] = intval($month);
            // dd('2');
        }
        $result = [];

        foreach ($coas as $coa) {
            $this->totalHeader = [];
            foreach ($range as $r) {
                $this->totalHeader[$r] = 0;
            }

            $newData = [
                'name' => $coa->name,
                'code' => $coa->code,
                'id' => $coa->id,
                'type_id' => $coa->type_id,
            ];

            // $check = Coa::where('parent_id', $coa->id)->where('status_id', 1)->get();
            $check = $coaWithParentId->where('parent_id', $coa->id);
            // dd($check);
            if (count($check) > 0) {
                $newData['child'] = $this->getChild($check, $coaWithParentId, $range, $details, $start->format('Y'));
            }

            // dd($coa->id, $range, $details);
            $profitLoss = $this->getProfitLoss($coa->id, $range, $details, $start->format('Y'));
            $newData['data'] = $profitLoss['data'];

            // Calculate initial balance
            // $beginingBalance = CoaMonthlyAmount::where('coa_id', $coa->id)
            //     ->where('month', $start->format('m'))
            //     ->where('year', $start->format('Y'))
            //     ->value('balance_total');
            // $newData['total'] = $beginingBalance ?? 0 + $profitLoss['total'];

            $newData['total'] = $profitLoss['total'];
            $newData['header_total'] = $this->totalHeader;
            $result[] = $newData;
        }

        // dd($coas, $result);

        if (! $isReal) {
            echo '<table>';
            foreach ($result as $data) {
                // dd($data['header_total']);
                echo '<tr>';
                echo '<td>('.$data['code'].') '.$data['name'].'</td><td>'.number_format($data['total'], 0, ',', '.').'</td>';
                echo '</tr>';

                foreach ($data['child'] as $child1) {
                    echo '<tr>';
                    echo '<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;('.$child1['code'].') '.$child1['name'].'</td><td>'.number_format($child1['total'], 0, ',', '.').'</td>';
                    echo '</tr>';

                    if (array_key_exists('child', $child1)) {
                        foreach ($child1['child'] as $child2) {
                            echo '<tr>';
                            echo '<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;('.$child2['code'].') '.$child2['name'].'</td><td>'.number_format($child2['total'], 0, ',', '.');
                            echo '</tr>';
                        }
                    }
                }

                echo '<tr>';
                echo '<td>Total '.$data['name'].'</td><td>'.number_format($data['header_total'][4], 0, ',', '.').'</td>';
                echo '</tr>';
                echo '<tr><td>&nbsp;</td></tr>';
            }
            echo '</table>';

        }

        return $result;
    }

    public function getChild($coas, $coaWithParentId, $range, $details, $year)
    {
        $child = [];
        try {
            foreach ($coas as $coa) {
                $newData = [
                    'name' => $coa->name,
                    'code' => $coa->code,
                    'id' => $coa->id,
                    'type_id' => $coa->type_id,
                ];

                // $check = Coa::where('parent_id', $coa->id)->where('status_id', 1)->get();
                $check = $coaWithParentId->where('parent_id', $coa->id);
                if (count($check) > 0) {
                    $newData['child'] = $this->getChild($check, $coaWithParentId, $range, $details, $year);
                }

                $profitLoss = $this->getProfitLoss($coa->id, $range, $details, $year);

                $newData['data'] = $profitLoss['data'];
                $newData['total'] = $profitLoss['total'];

                $child[] = $newData;
            }
        } catch (\Exception $ex) {
            Session::flash('error', 'Maaf kesalahan terjadi!'.$ex);
        }

        return $child;
    }
    // public function getProfitLoss($id, $range, $details){
    //     $data = [];
    //     $grandTotal = 0;
    //     try{
    //         foreach ($range as $currMonth){
    //             $newData = [
    //                 'total' => 0
    //             ];

    //             $data[$currMonth] = $newData;
    //         }

    //         foreach ($details as $detail){
    //             $month = (int)date('n', strtotime($detail->transaction_date));
    //             if($detail->account_id == $id){
    //                 // $value = (($detail->debit ?? 0) - ($detail->credit ?? 0));
    //                 $value = (($detail->credit ?? 0) - ($detail->debit ?? 0));
    //                 $data[$month]['total'] += $value;

    //                 $grandTotal += $value;
    //                 $this->totalHeader[$month-1] += $value;
    //             }
    //         }
    //     }
    //     catch (\Exception $ex){
    //         Session::flash('error', 'Maaf kesalahan terjadi!' . $ex);
    //     }

    //     return [
    //         'data' => $data,
    //         'total' => $grandTotal,
    //     ];
    // }

    public function getProfitLoss($id, $range, $details, $year)
    {
        $data = [];

        $grandTotal = 0;

        foreach ($range as $currMonth) {
            $beginingBalance = CoaMonthlyAmount::where('coa_id', $id)
                ->where('month', $currMonth)
                ->where('year', $year)
                ->value('balance_total');

            $newData = [
                'total' => $beginingBalance ?? 0,
            ];

            $data[$currMonth] = $newData;
        }

        $dtForeach = $details->where('account_id', $id);
        foreach ($dtForeach as $detail) {
            $month = (int) Carbon::parse($detail->transaction_date)->format('n');
            if ($detail->account_id == $id) {
                // $value = (($detail->debit ?? 0) - ($detail->credit ?? 0));
                $value = (($detail->credit ?? 0) - ($detail->debit ?? 0));
                $data[$month]['total'] += $value;

                $grandTotal += $value;
                $this->totalHeader[$month] += $value;
            }
        }

        return [
            'data' => $data,
            'total' => $grandTotal,
        ];
    }

    public function generateLedgerFromBalanceSheet(Request $request)
    {
        try {
            $date = Carbon::createFromFormat('!d-m-Y', $request->input('date'), 'Asia/Jakarta');
            $coa = $request->input('coa');

            $user = Auth::guard('admin')->user();
            $configBeginningDate = DB::table('configurations')
                ->where('branch_id', '=', $user->branch_id)
                ->where('key', '=', 'balance_sheet_beginning_date')
                ->first();

            $beginningDate = Carbon::parse($configBeginningDate->value);

            $result = $this->ledgerFuncFromBalanceSheet($date, $coa);

            $data = [
                'result' => $result,
                'type' => 'Ledger',
                'branch' => $user->branch,
                'start_date' => $beginningDate->locale('id_ID')->format('Y-m-d'),
                'end_date' => $date->copy()->addMonthNoOverflow()->locale('id_ID')->format('Y-m-d'),
            ];

            return view('admin.general_ledger.report-ledger')->with($data);
        } catch (\Exception $ex) {
            Log::channel('general_ledger')->error('GeneralLedgerController - generateLedgerFromBalanceSheet error ex: '.$ex);
            dd($ex);
        }
    }
}
