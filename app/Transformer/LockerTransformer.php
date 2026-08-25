<?php


namespace App\Transformer;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class LockerTransformer extends TransformerAbstract
{
    public function transform($locker){
        $date = Carbon::parse($locker->created_at)->toIso8601String();

        $urlShowMeta = route('admin.locker.edit', ['id' => $locker->id]);
        $action = "<a class='btn btn-xs btn-primary' href='". $urlShowMeta."'><i class='fas fa-xs fa-pencil-alt'></i></a>";
        $action .= "&nbsp;<a class='delete-modal btn btn-xs btn-danger' data-id='". $locker->id ."' data-number='". $locker->number ."' ><i class='fas fa-xs fa-times  '></i></a>";

        return[
            'created_at'    => $date,
            'name'          => $locker->name ?? '-',
            'number'        => $locker->number,
            'branch'        => $locker->branch->name,
            'status'        => $locker->status->display,
            'description'   => $locker->description ?? '-',
            'action'        => $action
        ];
    }
}
