<?php

namespace App\Services;

use App\Models\Coa;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class SaldoAwalGenerator
{
    public function syncRangeForReport(int $branchId, Carbon $startDate, Carbon $endDate): void
    {
        $period = CarbonPeriod::create(
            $startDate->copy()->startOfMonth(),
            '1 month',
            $endDate->copy()->startOfMonth()
        );

        foreach ($period as $monthDate) {
            $this->syncMonthForReport($branchId, $monthDate->copy()->startOfMonth());
        }
    }

    public function syncMonthForReport(int $branchId, Carbon $monthDate): void
    {
        $monthDate = $monthDate->copy()->startOfMonth();

        $this->upsertMonth($branchId, $monthDate);
    }

    public function generateForwardFromLatestExisting(int $branchId, ?Carbon $endMonth = null): array
    {
        $endMonth = ($endMonth ?? Carbon::now('Asia/Jakarta'))->copy()->startOfMonth();

        $latestSaldoAwal = DB::table('coa_monthly_amounts')
            ->where('branch_id', '=', $branchId)
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->first();

        if (empty($latestSaldoAwal)) {
            return [
                'status' => 'missing_base',
                'latest_existing_month' => null,
                'generated_months' => [],
                'message' => 'Saldo awal dasar tidak ditemukan',
            ];
        }

        $latestMonth = Carbon::createFromDate($latestSaldoAwal->year, $latestSaldoAwal->month, 1, 'Asia/Jakarta')
            ->startOfMonth();
        $startMonth = $latestMonth->copy()->addMonthNoOverflow();

        if ($startMonth->greaterThan($endMonth)) {
            return [
                'status' => 'up_to_date',
                'latest_existing_month' => $latestMonth->format('Y-m'),
                'generated_months' => [],
                'message' => 'Tidak ada bulan yang perlu digenerate',
            ];
        }

        $period = CarbonPeriod::create($startMonth, '1 month', $endMonth);
        $generatedMonths = [];

        foreach ($period as $monthDate) {
            $this->upsertMonth($branchId, $monthDate->copy()->startOfMonth());
            $generatedMonths[] = $monthDate->format('Y-m');
        }

        return [
            'status' => 'generated',
            'latest_existing_month' => $latestMonth->format('Y-m'),
            'generated_months' => $generatedMonths,
            'message' => 'Saldo awal berhasil digenerate',
        ];
    }

    private function upsertMonth(int $branchId, Carbon $monthDate): void
    {
        $monthDate = $monthDate->copy()->startOfMonth();

        $coas = Coa::select('id', 'code', 'name')
            ->where('branch_id', '=', $branchId)
            ->where('status_id', '=', 1)
            ->orderBy('code')
            ->get();

        if ($coas->isEmpty()) {
            return;
        }

        $previousMonth = $monthDate->copy()->subMonthNoOverflow()->startOfMonth();
        $previousSaldoAwal = DB::table('coa_monthly_amounts')
            ->where('branch_id', '=', $branchId)
            ->where('month', '=', $previousMonth->month)
            ->where('year', '=', $previousMonth->year)
            ->get()
            ->keyBy('coa_id');

        $jurnalDetails = DB::table('journal_details')
            ->join('journal_headers', 'journal_headers.id', '=', 'journal_details.header_id')
            ->selectRaw('journal_details.account_id as coa_id, COALESCE(SUM(journal_details.debit), 0) as debit_add, COALESCE(SUM(journal_details.credit), 0) as credit_add')
            ->where('journal_headers.branch_id', '=', $branchId)
            ->where('journal_headers.status_id', '=', 13)
            ->whereBetween('journal_headers.transaction_date', [
                $previousMonth->copy()->startOfMonth()->toDateTimeString(),
                $previousMonth->copy()->endOfMonth()->toDateTimeString(),
            ])
            ->groupBy('journal_details.account_id')
            ->get()
            ->keyBy('coa_id');

        $rows = [];
        $timestamp = now();
        $rowDate = $monthDate->copy()->setTime(2, 0, 0)->toDateTimeString();
        foreach ($coas as $coa) {
            $previousBalance = $previousSaldoAwal->get($coa->id);
            $jurnalDetail = $jurnalDetails->get($coa->id);

            $debitAdd = (float) ($jurnalDetail->debit_add ?? 0);
            $creditAdd = (float) ($jurnalDetail->credit_add ?? 0);
            $previousBalanceTotal = (float) ($previousBalance->balance_total ?? 0);
            $previousMovement = $this->isDebitNormalCoa($coa->code)
                ? $debitAdd - $creditAdd
                : $creditAdd - $debitAdd;
            $balanceTotal = $previousBalanceTotal + $previousMovement;

            $rows[] = [
                'branch_id' => $branchId,
                'date' => $rowDate,
                'month' => $monthDate->month,
                'year' => $monthDate->year,
                'coa_id' => $coa->id,
                'coa_code' => $coa->code,
                'coa_name' => $coa->name,
                'debit_add' => 0,
                'credit_add' => 0,
                'debit_total' => 0,
                'credit_total' => 0,
                'aktiva' => 0,
                'pasiva' => 0,
                'balance_total' => $balanceTotal,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                DB::table('coa_monthly_amounts')
                    ->updateOrInsert(
                        [
                            'branch_id' => $row['branch_id'],
                            'month' => $row['month'],
                            'year' => $row['year'],
                            'coa_id' => $row['coa_id'],
                        ],
                        $row
                    );
            }
        });
    }

    private function calculateSaldoAwalBalance(?string $coaCode, float $debitTotal, float $creditTotal): float
    {
        if ($this->isDebitNormalCoa($coaCode)) {
            return $debitTotal - $creditTotal;
        }

        return $creditTotal - $debitTotal;
    }

    private function isDebitNormalCoa(?string $coaCode): bool
    {
        if (empty($coaCode)) {
            return true;
        }

        return str_starts_with($coaCode, '1')
            || str_starts_with($coaCode, '5')
            || str_starts_with($coaCode, '6')
            || str_starts_with($coaCode, '7')
            || str_starts_with($coaCode, '9');
    }
}
