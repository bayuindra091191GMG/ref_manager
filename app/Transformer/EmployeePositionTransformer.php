<?php

namespace App\Transformer;

use App\Models\AdminUser;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class EmployeePositionTransformer extends TransformerAbstract
{
    public function transform($position){
        $createdAt = Carbon::parse($position->created_at)->toIso8601String();
        $updatedAt = $position->updated_at == null ? $createdAt : Carbon::parse($position->updated_at)->toIso8601String();
        
        $updatedBy = $position->updated_by == null ? $position->created_by : $position->updated_by;
        $updatedByName = AdminUser::find($updatedBy)->name;
        
        $action = "";
        
        if (auth()->user()->can('branch.scope_all') == 1 || auth()->user()->hasRole('superadmin') == 1 ||
        (auth()->guard('admin')->user()->hasAnyPermission('employee_position.edit')))
        {
            $action = "<a class='btn btn-xs btn-primary' href='". route('admin.employee_position.edit', $position->id). "'><i class='fa fa-pencil-alt'></i></a>";
        }
        
        return [
            'action' => $action,
            'branch_name' => $position->branch_name,
            'code' => $position->code,
            'name' => $position->name,
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
            'updated_by' => $updatedByName
        ];
    }
}
