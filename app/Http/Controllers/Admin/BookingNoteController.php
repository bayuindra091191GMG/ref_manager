<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\BookingNote;
use App\Models\Partner;
use App\Transformer\BookingNoteTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class BookingNoteController extends Controller
{
    public function index(Request $request){
        $user = Auth::guard('admin')->user();
        $userData = AdminUser::where('id', $user->id)->first();

        $filterDate = Carbon::now('Asia/Jakarta');
        if($request->exists('filterDate')){
            $filterDate = Carbon::createFromFormat('!d-m-Y', $request->query('filterDate'), 'Asia/Jakarta');
        }

        $selectedPartner = '-- Filter Therapis --';
        $selectedPartnerId = -1;
        if($request->exists('partnerId')){
            $partner = Partner::where('id', $request->query('partnerId'))->first();
            if($partner != null){
                $selectedPartner = $partner->number . ' - ' . $partner->name;
                $selectedPartnerId = $partner->id;
            }
        }

        $data = [
            'selectedPartnerId' => $selectedPartnerId,
            'selectedPartner'   => $selectedPartner,
            'filterBranchId'    => $userData->branch_id,
            'filterDate'        => $filterDate->format('d-m-Y'),
        ];

        return view('admin.booking_note.index')->with($data);
    }

    public function getIndex(Request $request){
        $items = BookingNote::with(['branch', 'createdBy', 'updatedBy', 'partner'])
            ->where('id', '>', 0);

        if($request->filled('filterDate'))
        {
            $tmpStart = Carbon::createFromFormat('!d-m-Y', $request->input('filterDate'));
            $tmpEnd = Carbon::createFromFormat('!d-m-Y', $request->input('filterDate'));
            $tmpEnd->addDay();

            $items = $items->whereBetween('book_date', [$tmpStart, $tmpEnd]);
        }

        if($request->filled('partnerId') && $request->input('partnerId') != -1){
            $items = $items->where('partner_id', $request->input('partnerId'));
        }

        $items = $items->orderByDesc('id');

        return DataTables::of($items)
            ->setTransformer(new BookingNoteTransformer)
            ->make(true);
    }

    public function create(){
        $user = Auth::guard('admin')->user();
        $userData = AdminUser::where('id', $user->id)->first();

        $data = [
            'filterBranchId'    => $userData->branch_id,
        ];
        return View('admin.booking_note.create')->with($data);
    }

    public function store(Request $request){
        try{
            $validator = Validator::make($request->all(),[
                'partner_id'    => 'required',
                'booking_date' => 'required',
            ],[
                'partner_id.required'  => 'Therapis Harus dipilih!',
                'booking_date.required' => 'Tanggal booking Harus dipilih!',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput($request->all());
            }

            $user = Auth::guard('admin')->user();
            $userData = AdminUser::where('id', $user->id);
            $bookingNote = new BookingNote();
            $bookingNote->created_by = $user->id;
            $bookingNote->book_date = Carbon::createFromFormat('!d-m-Y H:i', $request->input('booking_date'), 'Asia/Jakarta');
            $bookingNote->book_by = $request->input('name');
            $bookingNote->partner_id = $request->input('partner_id');
            $bookingNote->status = 'Pending';
            $bookingNote->branch_id = $userData->branch_id;
            $bookingNote->save();

            Session::flash('success', 'Berhasil submit booking note!');
            return Redirect::route('admin.booking_notes.index');
        }
        catch (\Exception $ex){
            Session::flash('error', 'Maaf kesalahan terjadi!' . $ex);
            return back()->withInput($request->all());
        }
    }

    public function cancel(Request $request){
        try{
            $bookingNote = BookingNote::where('id', $request->input('id'))->first();
            if($bookingNote == null){
                return Response::json('Booking Note tidak ditemukan!', 404);
            }
            $bookingNote->status = 'Canceled';
            $bookingNote->save();

            return Response::json('Sukses', 200);
        }
        catch (\Exception $ex){
            return Response::json('Maaf terjadi kesalahan!', 500);
        }
    }

    public function done(Request $request){
        try{
            $bookingNote = BookingNote::where('id', $request->input('id'))->first();
            if($bookingNote == null){
                return Response::json('Booking Note tidak ditemukan!', 404);
            }
            $bookingNote->status = 'Done';
            $bookingNote->save();

            return Response::json('Sukses', 200);
        }
        catch (\Exception $ex){
            return Response::json('Maaf terjadi kesalahan!', 500);
        }
    }
}
