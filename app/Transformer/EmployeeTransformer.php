<?php

namespace App\Transformer;

use App\Models\AdminUser;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class EmployeeTransformer extends TransformerAbstract
{
    public function transform($employee){
        $createdAt = Carbon::parse($employee->created_at)->toIso8601String();
        $updatedAt = $employee->updated_at == null ? $createdAt : Carbon::parse($employee->updated_at)->toIso8601String();

        $updatedBy = $employee->updated_by == null ? $employee->created_by : $employee->updated_by;
        $updatedByName = AdminUser::find($updatedBy)->name;

        $action = "";

        if (auth()->user()->can('branch.scope_all') == 1 ||
        auth()->user()->hasRole('superadmin') == 1 ||
        (auth()->user()->employee != null &&
        auth()->guard('admin')->user()->hasAnyPermission('employee.list')))
        {
            $action = "<a class='btn btn-xs btn-success' href='". route('admin.employee.show', $employee->id). "'><i class='fa fa-list'></i></a>";
        }

        return [
            'action' => $action,
            'branch_name' => $employee->branch_name,
            'id' => $employee->id,
            'code' => $employee->code,
            'attend_machine_id' => $employee->attend_machine_id,
            'name' => $employee->name,
            'number' => $employee->number,
            'phone' => $employee->phone,
            'address' => $employee->address,
            'status_display' => $employee->status_display,
            'position_name' => $employee->position_name,
            'department_name' => $employee->department_name ?? '-',
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
            'updated_by' => $updatedByName
        ];
    }
}
