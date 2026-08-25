<?php

/**
 * Created by PhpStorm.
 * User: GMG-Developer
 * Date: 13/02/2018
 * Time: 11:34
 */

namespace App\Transformer;

use App\Models\JournalHeader;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use League\Fractal\TransformerAbstract;

class JournalTransformer extends TransformerAbstract
{
    public function transform(JournalHeader $journal)
    {

        try {
            $createdDate = $journal->transaction_date != null ?
                Carbon::parse($journal->transaction_date)->format('d M Y') : '-';

            $urlPrintMeta = route('admin.journal.show', ['id' => $journal->id]).'?type=print';
            $urlShowMeta = route('admin.journal.show', ['id' => $journal->id]);
            $urlEditMeta = route('admin.journal.edit', ['id' => $journal->id]);
            $action = '';
            if (Auth::guard('admin')->user()->can('branch.scope_all') == 1 || Auth::guard('admin')->user()->hasRole('superadmin') == 1
                || (Auth::guard('admin')->user()->hasPermissionTo('journal_manual.show'))) {
                $action .= "<a class='btn btn-xs btn-dark' href='".$urlPrintMeta."' data-toggle='tooltip' data-placement='top'><i class='fas fa-xs fa-print'></i></a>";
                $action .= "&nbsp;<a class='btn btn-xs btn-info' href='".$urlShowMeta."' data-toggle='tooltip' data-placement='top'><i class='fa fa-info'></i></a>";
            }

            if ($journal->status_id != 13) {
                if (Auth::guard('admin')->user()->can('branch.scope_all') == 1 || Auth::guard('admin')->user()->hasRole('superadmin') == 1
                    || (Auth::guard('admin')->user()->hasPermissionTo('journal_manual.edit'))) {
                    $action .= "&nbsp;<a class='btn btn-xs btn-primary' href='".$urlEditMeta."' data-toggle='tooltip' data-placement='top'><i class='fa fa-pencil-alt'></i></a>";
                }
                if (Auth::guard('admin')->user()->can('branch.scope_all') == 1 || Auth::guard('admin')->user()->hasRole('superadmin') == 1
                    || (Auth::guard('admin')->user()->hasPermissionTo('journal_manual.delete'))) {
                    $action .= "&nbsp;<a class='delete-modal btn btn-xs btn-danger' data-id='".$journal->id."' data-number='".$journal->transaction_code."'><i class='fa fa-eraser'></i></a>";
                }
            }
            if (Auth::guard('admin')->user()->can('branch.scope_all') == 1 || Auth::guard('admin')->user()->hasRole('superadmin') == 1
                || (Auth::guard('admin')->user()->hasPermissionTo('journal_manual.unapprove'))) {
                $action .= "&nbsp;<a class='btn btn-xs btn-warning cancel-btn' data-id='".$journal->id."' data-number='".$journal->transaction_code."' data-toggle='tooltip' data-placement='top'><i class='fa fa-ban'></i></a>";
            }

            return [
                'id' => $journal->id,
                'transaction_code' => $journal->transaction_code,
                'transaction_type' => $journal->transaction_type,
                'notes' => $journal->notes,
                'date' => $createdDate,
                'created_at' => $createdDate,
                'status_id' => $journal->status_id,
                'action' => $action,
            ];
        } catch (\Exception $ex) {
            Log::error('Admin/Journal - transform error EX: '.$ex);

            return null;
        }
    }
}
