<?php


namespace App\Transformer;

use App\Models\TransactionHeader;
use App\Models\TransactionInvoice;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class TransactionTransformer extends TransformerAbstract
{
    public function transform(TransactionInvoice $invoice){
        $date = Carbon::parse($invoice->created_at)->toIso8601String();

        $urlShowTrx = route('admin.transaction.show', ['id' => $invoice->id]);
        $action = "<a class='btn btn-xs btn-primary' href='". $urlShowTrx."'><i class='fas fa-xs fa-info'></i></a>";
        if($invoice->status_id == 9){
            $urlReprint = route('admin.monitor_room.print',['id' => $invoice->id]) . '?reprint=true';
            $action .= "&nbsp;<a class='btn btn-xs btn-success' href='". $urlReprint."' target='_blank'><i class='fas fa-xs fa-receipt'></i></a>";
        }

        return[
            'id'            => $invoice->id,
            'created_at'    => $date,
            'code'          => $invoice->code,
            'member_name'   => $invoice->member_name,
            'amount'        => $invoice->grandtotal,
            'return'        => $invoice->return_value,
            'status'        => $invoice->status->display,
            'status_id'     => $invoice->status_id,
            'action'        => $action
        ];
    }
}
