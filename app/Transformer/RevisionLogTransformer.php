<?php


namespace App\Transformer;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class RevisionLogTransformer extends TransformerAbstract
{
    public function transform($data){
        $date = Carbon::parse($data->created_at)->toIso8601String();

        $urlShowMeta = route('admin.revision.edit', ['id' => $data->id]);
        $action = "<a class='btn btn-xs btn-primary' href='". $urlShowMeta."'><i class='fas fa-xs fa-pencil-alt'></i></a>";
        // $action .= "&nbsp;<a class='delete-modal btn btn-xs btn-danger' data-id='". $data->id ."' ><i class='fas fa-xs fa-times  '></i></a>";

        return[
            'created_at'    => $date,
            'code'          => $data->code,
            'caused_by'     => $data->caused_by ?? '-',
            'description'   => $data->description ?? '-',
            'action'        => $action
        ];
    }
}
