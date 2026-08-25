<?php


namespace App\Transformer;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use League\Fractal\TransformerAbstract;

class RoomTransformer extends TransformerAbstract
{
    public function transform($room){
        $date = Carbon::parse($room->created_at)->toIso8601String();

        $urlShowMeta = route('admin.room.edit', ['id' => $room->id]);
        if(Auth::guard('admin')->user()->can('branch.scope_all') == 1 || Auth::guard('admin')->user()->hasRole('superadmin') == 1
        || (Auth::guard('admin')->user()->hasPermissionTo('room.edit'))){
            $action = "<a class='btn btn-xs btn-primary' href='". $urlShowMeta."'><i class='fas fa-xs fa-pencil-alt'></i></a>";
        }
        if(Auth::guard('admin')->user()->can('branch.scope_all') == 1 || Auth::guard('admin')->user()->hasRole('superadmin') == 1
        || (Auth::guard('admin')->user()->hasPermissionTo('room.delete'))){
            $action .= "&nbsp;<a class='delete-modal btn btn-xs btn-danger' data-id='". $room->id ."' data-name='". $room->number ."' ><i class='fas fa-xs fa-times  '></i></a>";
        }

        return[
            'created_at'    => $date,
            'id'            => $room->id,
            'name'          => $room->name,
            'type'          => $room->type,
            'number'        => $room->number,
            'branch'        => $room->branch->name,
            'status'        => $room->status->display,
            'description'   => $room->description ?? '-',
            'action'        => $action
        ];
    }
}
