<?php

namespace App\Transformer;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class MonthlySalaryPartnerTransformer extends TransformerAbstract
{
    public function transform($salary){
        $createdAt = Carbon::parse($salary->created_at)->toIso8601String();

        $action = "<a class='btn btn-xs btn-success' href='". route('admin.salary.partner.show', $salary->id). "'><i class='fa fa-list'></i></a>";

        return [
            'action' => $action,
            'partner_code' => $salary->partner_code,
            'partner_name' => $salary->partner_name,
            'partner_number' => $salary->partner_number,
            'partner_alias' => $salary->partner_alias,
            'basic_salary_amount' => $salary->basic_salary_amount,
            'commission_amount' => $salary->commission_amount,
            'penalty_amount' => $salary->penalty_amount,
            'other_amount' => $salary->other_amount,
            'total_salary_amount' => $salary->total_salary_amount,
            'created_at' => $createdAt,
        ];
    }
}
