<?php


namespace App\Transformer;

use App\Models\TimbanganHeader;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class ScaleHeaderBBTransformer extends TransformerAbstract
{
    public function transform(TimbanganHeader $header){
        $date = Carbon::parse($header->created_at)->toIso8601String();

        $urlShowMeta = route('admin.scale_header_bb.show', ['id' => $header->id]);
        $urlEditMeta = route('admin.scale_header_bb.edit', ['id' => $header->id]);

        $action = "<a class='btn btn-xs btn-primary' href='". $urlEditMeta."'><i class='fas fa-xs fa-pencil-alt'></i></a>";
        $action .= "&nbsp;<a class='btn btn-xs btn-success' href='". $urlShowMeta."'><i class='fas fa-xs fa-book'></i></a>";
        $action .= "&nbsp;<a class='delete-modal btn btn-xs btn-danger' data-id='". $header->id ."' data-name='". $header->code ."' ><i class='fas fa-xs fa-times  '></i></a>";

        return[
            'created_at'        => $date,
            'id'                => $header->id,
            'driver_name'       => $header->driver_name,
            'code'              => $header->code,
            'plat_no'           => $header->plat_no,
            'weight_type'       => $header->weight_type,
            'weight_1'          => $header->weight_1,
            'weight_2'          => $header->weight_2,
            'kadar_aci'         => $header->kadar_aci . '%',
            'kadar_refraksi'    => $header->kadar_refraksi . '%',
            'kadar_aci_src'     => $header->kadar_aci,
            'kadar_refraksi_src'=> $header->kadar_refraksi,
            'harga_beli'        => number_format($header->harga_beli, 2, ',', '.'),
            'bonus_spag'        => number_format($header->bonus_spag, 2, ',', '.'),
            'upah_bongkar'      => number_format($header->upah_bongkar, 2, ',', '.'),
            'harga_beli_src'    => $header->harga_beli,
            'bonus_spag_src'    => $header->bonus_spag,
            'upah_bongkar_src'  => $header->upah_bongkar,
            'berat_bruto'       => $header->berat_bruto,
            'berat_refraksi'    => $header->berat_refraksi,
            'berat_netto'       => $header->berat_netto,
            'total_price'       => $header->total_price == null ? 0 : number_format($header->total_price, 2, ',', '.'),
            'total_price_src'   => $header->total_price,
            
            'harga_beli_bb'        => number_format($header->harga_beli_bb, 2, ',', '.'),
            'harga_beli_bb_src'    => $header->harga_beli_bb,
            'total_price_bb'       => $header->total_price_bb == null ? 0 : number_format($header->total_price_bb, 2, ',', '.'),
            'total_price_bb_src'   => $header->total_price_bb,
            'po_id'                 => $header->po_id,
            'po_code'               => $header->po_code ?? "-",
            'po_code_str'           => $header->po_code." | Harga = ".number_format($header->harga_beli_bb, 0, ',', '.'),
            'total_sack'        => $header->total_sack ?? 0,
            'status_id'         => $header->status_id,
            'branch_name'       => $header->branch->name,
            'status'            => $header->status->display,
            'description'       => $header->description ?? '-',
            'action'            => $action
        ];
    }
}
