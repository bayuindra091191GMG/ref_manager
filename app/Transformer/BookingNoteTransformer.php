<?php

namespace App\Transformer;

use App\Models\BookingNote;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class BookingNoteTransformer extends TransformerAbstract
{
    public function transform(BookingNote $item){
        $date = Carbon::parse($item->created_at)->toIso8601String();
        $bookDate = Carbon::parse($item->book_date)->toIso8601String();

        $action = "<a class='cancel-modal btn btn-xs btn-danger' data-id='". $item->id."'><i class='si si-close'></i></a>&nbsp;";
        $action .= "<a class='done-modal btn btn-xs btn-success' data-id='". $item->id."'><i class='fas fa-xs fa-door-open'></i></a>";

        $status = $item->status;
        if($item->status == 'Pending'){
            $status = '<span style="color: orange;">Pending</span>';
        }
        else if($item->status == 'Done'){
            $status = '<span style="color: lightgreen;">Done</span>';
        }
        else if($item->status == 'Canceled'){
            $status = '<span style="color: red;">Canceled</span>';
        }

        return[
            'created_at'    => $date,
            'therapis'      => $item->partner->number . ' - ' . $item->partner->name,
            'book_by'       => $item->book_by ?? '-',
            'book_date'     => $bookDate,
            'status'        => $status,
            'action'        => $action
        ];
    }
}
