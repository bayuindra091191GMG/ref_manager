<?php

namespace App\Transformer;

use App\Models\Package;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class PackageTransformer extends TransformerAbstract
{
    public function transform(Package $package)
    {
        $date = Carbon::parse($package->created_at)->toIso8601String();

        $urlEditPackage = route('admin.package.edit', ['id' => $package->id]);
        $urlShowPackage = route('admin.package.show', ['id' => $package->id]);
        $action = "<a class='btn btn-xs btn-primary' href='".$urlEditPackage."'><i class='fas fa-xs fa-pencil-alt'></i></a>&nbsp;";
        $action .= "<a class='btn btn-xs btn-success' href='".$urlShowPackage."'><i class='fas fa-xs fa-list'></i></a>";
        $action .= "&nbsp;<a class='delete-modal btn btn-xs btn-danger' data-id='".$package->id."' data-name='".$package->name."' ><i class='fas fa-xs fa-times  '></i></a>";

        return [
            'id' => $package->id,
            'created_at' => $date,
            'name' => $package->name,
            'branch_name' => $package->branch->name,
            'code' => $package->code,
            'duration' => $package->duration,
            'price' => $package->price,
            'price_str' => 'Rp'.number_format($package->price, '2', ',', '.'),
            'partner_count' => $package->partner_count,
            'room_type_id' => $package->room_type_id,
            'room_type_name' => $package->room_type->name,
            'room_type' => $package->room_type->display,
            'extend_duration' => $package->extend_duration,
            'extend_price' => $package->extend_price,
            'additional_person_price' => $package->additional_person_price,
            'additional_partner_price' => $package->additional_partner_price,
            'created_by' => $package->createdBy->name ?? '-',
            'updated_by' => $package->updatedBy->name ?? '-',
            'description' => $package->description ?? '-',
            'badge_status' => $package->badge_status,
            'status' => $package->status->display,
            'branch' => $package->branch->name,
            'img' => asset('storage/images/items/'.$package->image_path),
            'action' => $action,
        ];
    }
}
