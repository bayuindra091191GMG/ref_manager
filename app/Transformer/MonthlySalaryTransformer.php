<?php

namespace App\Transformer;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use League\Fractal\TransformerAbstract;

class MonthlySalaryTransformer extends TransformerAbstract
{
    public function transform($salary)
    {
        $createdAt = Carbon::parse($salary->created_at)->toIso8601String();
        $urlEditMeta = route('admin.salary.employee.edit', ['id' => $salary->id]);
        $urlShowMeta = route('admin.salary.employee.show', ['id' => $salary->id]);

        $action = "";
        if (Auth::guard('admin')->user()->can('branch.scope_all') == 1 || Auth::guard('admin')->user()->hasRole('superadmin') == 1
            || (Auth::guard('admin')->user()->hasPermissionTo('salary.show'))) {
            $action .= "<a class='btn btn-xs btn-info' href='" . $urlShowMeta . "'><i class='fas fa-xs fa-info'></i></a>";
        }
        if (Auth::guard('admin')->user()->can('branch.scope_all') == 1 || Auth::guard('admin')->user()->hasRole('superadmin') == 1
            || (Auth::guard('admin')->user()->hasPermissionTo('salary.edit'))) {
            $action .= "&nbsp;<a class='btn btn-xs btn-primary' href='" . $urlEditMeta . "'><i class='fas fa-xs fa-pencil-alt'></i></a>";
        }
        // if (Auth::guard('admin')->user()->is_super == 1 || Auth::guard('admin')->user()->is_developer == 1
        //     || (Auth::guard('admin')->user()->hasPermissionTo('salary.delete'))) {
        //     $action .= "&nbsp;<a class='btn btn-xs btn-danger' href='" .  . "' target='_blank'><i class='fas fa-xs fa-print'></i></a>";
        // }

        return [
            'action' => $action,
            'month_date' => $salary->month_date,
            'year_date' => $salary->year_date,
            'period' => $salary->month_date . '-' . $salary->year_date,
            'employee_id' => $salary->employee_id,
            'employee_name' => $salary->employee_name,
            'employee_code' => $salary->employee_code,
            'basic_salary' => $salary->basic_salary,
            'position_allowance' => $salary->position_allowance,
            'meal_allowance' => $salary->meal_allowance,
            'overtime_pay' => $salary->overtime_pay,
            'cubication' => $salary->cubication,
            'ritalase' => $salary->ritalase,
            'total_income' => $salary->total_income,
            'pipeline_bonus' => $salary->pipeline_bonus,
            'project_bonus' => $salary->project_bonus,
            'attendance_bonus' => $salary->attendance_bonus,
            'incentive_bonus' => $salary->incentive_bonus,
            'other_bonus' => $salary->other_bonus,
            'total_bonus' => $salary->total_bonus,
            'jamsostek' => $salary->jamsostek,
            'cooperative_fee' => $salary->cooperative_fee,
            'cooperative_loan' => $salary->cooperative_loan,
            'cooperative_loan_installment' => $salary->cooperative_loan_installment,
            'jpk' => $salary->jpk,
            'absence_deduction' => $salary->absence_deduction,
            'other_deductions' => $salary->other_deductions,
            'total_deductions' => $salary->total_deductions,
            'net_salary' => $salary->net_salary,
            'created_at' => $createdAt,
            'updated_at' => $salary->updated_at,
        ];
    }
}
