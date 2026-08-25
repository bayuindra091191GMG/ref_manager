<?php


namespace App\Transformer;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class BranchTransformer extends TransformerAbstract
{
    public function transform($branch){
        $date = $branch->created_at ? Carbon::parse($branch->created_at)->toIso8601String() : null;

        $branchName = e($branch->name ?? '-');
        $urlEditMeta = route('admin.branch.edit', ['id' => $branch->id]);
        $action = "<a class='btn btn-xs btn-info disabled' href='javascript:void(0)' title='Detail'><i class='fas fa-xs fa-info'></i></a>";
        $action .= "&nbsp;<a class='btn btn-xs btn-primary' href='". $urlEditMeta."' title='Edit'><i class='fas fa-xs fa-pencil-alt'></i></a>";
        $action .= "&nbsp;<a class='delete-modal btn btn-xs btn-danger' data-id='". $branch->id ."' data-name='". $branchName ."' title='Delete'><i class='fas fa-xs fa-times'></i></a>";

        $image = '-';
        if (!empty($branch->image_filename)) {
            $filename = basename($branch->image_filename);
            $imagePath = public_path('images/branches/' . $filename);

            if (is_file($imagePath)) {
                $imageUrl = asset('images/branches/' . $filename);
                $image = "<img src='". $imageUrl ."' alt='". e($filename) ."' class='img-fluid rounded' style='max-width: 96px; max-height: 64px; object-fit: contain;'>";
            } else {
                $image = e($filename);
            }
        }

        return[
            'id'            => $branch->id,
            'created_at'    => $date,
            'code'          => $branch->code ?? '-',
            'name'          => $branch->name ?? '-',
            'address'       => $branch->address ?: '-',
            'image'         => $image,
            'action'        => $action
        ];
    }
}
