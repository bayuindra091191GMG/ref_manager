<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\Branch;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Status;
use App\Transformer\RoomTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class RoomController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(){
        return view('admin.room.index');
    }

    public function getIndex(Request $request){
        $admin = Auth::guard('admin')->user();

        $branches = Room::with('branch')->with('status')
            ->where('rooms.id', '>', 0)
            ->where('branch_id', '=', $admin->branch_id);

        return DataTables::of($branches)
            ->setTransformer(new RoomTransformer)
            ->make(true);
    }

    public function create(){
        $branches = Branch::all();
        $roomTypes = RoomType::all();

        $data = [
            'branches'  => $branches,
            'roomTypes' => $roomTypes,
        ];
        return view('admin.room.create')->with($data);
    }

    public function store(Request $request){
        try{
            $validator = Validator::make($request->all(),[
                'name'      => 'required',
                'number'    => 'required',
                'type'      => 'required',
//                'branch_id' => 'required',
            ],[
                'name.required'         => 'Nama Room wajib diisi!',
                'number.required'       => 'Room Number wajib diisi!',
                'type.required'         => 'Room Type wajib dipilih!',
//                'branch_id.required'    => 'Branch wajib dipilih!',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput($request->all());
            }

            $admin = Auth::guard('admin')->user();

            if(DB::table('rooms')
                ->where('branch_id', '=', $admin->branch_id)
                ->where('number', '=', $request->input('number'))
                ->exists()){
                Session::flash('error', 'Nomor Room sudah terdaftar!');
                return back()->withInput($request->all());
            }

            $nRoom = new Room();
            $nRoom->name = $request->input('name');
            $nRoom->number = $request->input('number');
            $nRoom->description = $request->input('description');
            $nRoom->type = $request->input('type');
//            $nRoom->branch_id = $request->input('branch_id');
            $nRoom->branch_id = $admin->branch_id;
            $nRoom->status_id = 7;
            $nRoom->created_by = $admin->id;
            $nRoom->updated_by = $admin->id;
            $nRoom->save();

            Session::flash('success', 'Sukses membuat data room baru!');
            return redirect()->route('admin.room.index');
        }
        catch (\Exception $ex){
            Session::flash('error', 'Maaf kesalahan terjadi!' . $ex);
            return back()->withInput($request->all());
        }
    }

    public function edit($id){
        $branches = Branch::all();
        $roomTypes = RoomType::all();
        $statuses = Status::all();
        $room = Room::where('id', $id)->first();
        if($room == null){
            Session::flash('error', 'Room tidak ditemukan!');
            return back();
        }

        $data = [
            'room'          => $room,
            'branches'      => $branches,
            'roomTypes'     => $roomTypes,
            'statuses'      => $statuses,
        ];

        return view('admin.room.edit')->with($data);
    }

    public function update(Request $request){
        try{
            $validator = Validator::make($request->all(),[
                'id'        => 'required',
                'number'    => 'required',
                'type'      => 'required',
//                'branch_id' => 'required',
                'status_id' => 'required',
            ],[
                'name.required'         => 'Room Name wajib diisi!',
                'number.required'       => 'Room Number wajib diisi!',
                'type.required'         => 'Room Type wajib dipilih!',
//                'branch_id.required'    => 'Branch wajib dipilih!',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput($request->all());
            }

            $admin = Auth::guard('admin')->user();

            if(DB::table('rooms')
                ->where('id', '!=', $request->input('id'))
                ->where('branch_id', '=', $admin->branch_id)
                ->where('number', '=', $request->input('number'))
                ->exists()){
                Session::flash('error', 'Nomor Room sudah terdaftar!');
                return back()->withInput($request->all());
            }

            $nRoom = Room::where('id', $request->input('id'))->first();
            if($nRoom == null){
                Session::flash('error', 'Room tidak ditemukan!');
                return back()->withInput($request->all());
            }
            $nRoom->name = $request->input('name');
            $nRoom->number = $request->input('number');
            $nRoom->description = $request->input('description');
            $nRoom->type = $request->input('type');
//            $nRoom->branch_id = $request->input('branch_id');
            $nRoom->branch_id = $admin->branch_id;
            $nRoom->status_id = $request->input('status_id');
            $nRoom->updated_by = $admin->id;
            $nRoom->save();

            Session::flash('success', 'Sukses mengubah data room!');
            return redirect()->route('admin.room.index');
        }
        catch (\Exception $ex){
            Session::flash('error', 'Maaf kesalahan terjadi!');
            return back()->withInput($request->all());
        }
    }

    public function delete(Request $request){
        try{
            $room = Room::where('id', $request->input('id'))->first();
            if($room == null){
                Session::flash('error', 'Room tidak ditemukan!');
                return back();
            }
            $room->delete();

            return Response::json('Sukses', 200);
        }
        catch (\Exception $ex){
            return Response::json('error', 200);
        }
    }

    public function select(Request $request){
        $term = trim($request->search);

        $admin = Auth::guard('admin')->user();

        $rooms = DB::table('rooms')
            ->where(function($q) use ($term){
                $q->where('code', 'LIKE', '%'. $term. '%')
                    ->orWhere('name', 'LIKE', '%'. $term. '%');
            })
            ->where('status_id', '=', 5)
            ->where('branch_id', '=', $admin->branch_id)
            ->orderBy('name')
            ->get();

        $formatted_tags = [];

        foreach ($rooms as $room) {
            $formatted_tags[] = ['id' => $room->id, 'text' => $room->name];
        }

        return Response::json($formatted_tags);
    }

    public function selectActive(Request $request){
        $term = trim($request->search);

        $admin = Auth::guard('admin')->user();

        $rooms = DB::table('rooms')
            ->where(function($q) use ($term){
                $q->where('code', 'LIKE', '%'. $term. '%')
                    ->orWhere('name', 'LIKE', '%'. $term. '%');
            })
            ->where('status_id', '=', 6)
            ->where('branch_id', '=', $admin->branch_id)
            ->orderBy('name')
            ->get();

        $formatted_tags = [];

        foreach ($rooms as $room) {
            $formatted_tags[] = ['id' => $room->id, 'text' => $room->name];
        }

        return Response::json($formatted_tags);
    }

    public function selectAll(Request $request){
        $term = trim($request->search);

        $admin = Auth::guard('admin')->user();

        $rooms = DB::table('rooms')
            ->where(function($q) use ($term){
                $q->where('code', 'LIKE', '%'. $term. '%')
                    ->orWhere('name', 'LIKE', '%'. $term. '%');
            })
            ->where('branch_id', '=', $admin->branch_id)
            ->orderBy('name')
            ->get();

        $formatted_tags = [];

        foreach ($rooms as $room) {
            $formatted_tags[] = ['id' => $room->id, 'text' => $room->name];
        }

        return Response::json($formatted_tags);
    }

    public function selectOura(Request $request){
        $term = trim($request->search);

        $admin = Auth::guard('admin')->user();

        $rooms = DB::table('rooms')
            ->where(function($q) use ($term){
                $q->where('code', 'LIKE', '%'. $term. '%')
                    ->orWhere('name', 'LIKE', '%'. $term. '%');
            })
            ->where('status_id', '=', 5)
            ->where('branch_id', '=', $admin->branch_id)
            ->orderBy('name')
            ->get();

        $formatted_tags = [];

        foreach ($rooms as $room) {
            $formatted_tags[] = ['id' => $room->id. '#'. $room->name, 'text' => $room->name];
        }

        return Response::json($formatted_tags);
    }

    public function getRooms(Request $request){
        try{
            $skip = 0;
            $take = 10;
            if($request->exists('skip')){
                $skip = $request->query('skip');
            }

            if($request->exists('take')){
                $take = $request->query('take');
            }

            $arr = [];
            $user = Auth::guard('admin')->user();
            $userData = AdminUser::where('id', $user->id)->first();

            $rooms = Room::where('type', $request->query('type'))->where('branch_id', $userData->branch_id);
            if($request->exists('search') && $request->query('search') != ''){
                $rooms = $rooms->where('name', 'like', '%' . $request->query('search') . '%')
                    ->orWhere('number', 'like', '%' . $request->query('search') . '%');
            }

            $rooms = $rooms->skip($skip)->take($take)->get();
            $count = DB::table('rooms')->where('type', $request->query('type'))->where('branch_id', $userData->branch_id)->count();
            $pages = [];
            $tmpPageCount = ceil($count / 10);
            for($i=1; $i<=$tmpPageCount; $i++){
                $pages[] = $i;
            }

            $transformer = new RoomTransformer();
            foreach ($rooms as $room){
                $arr[] = $transformer->transform($room);
            }

            $data = [
                'rooms'     => $arr,
                'count'     => $count,
                'pages'     => $pages,
            ];

            return Response::json($data, 200);
        }
        catch (\Exception $ex){
            return Response::json('failed' . $ex, 500);
        }
    }

    public function getRoomsOura(Request $request){
        try{
            $skip = 0;
            $take = 100;
            if($request->exists('skip')){
                $skip = $request->query('skip');
            }

            if($request->exists('take')){
                $take = $request->query('take');
            }

            $arr = [];
            $user = Auth::guard('admin')->user();
            $userData = AdminUser::where('id', $user->id)->first();

            $rooms = Room::where('branch_id', $userData->branch_id);
            if($request->exists('search') && $request->query('search') != ''){
                $rooms = $rooms->where('name', 'like', '%' . $request->query('search') . '%')
                    ->orWhere('number', 'like', '%' . $request->query('search') . '%');
            }

            $rooms = $rooms->skip($skip)->take($take)->get();
            $count = DB::table('rooms')->where('type', $request->query('type'))->where('branch_id', $userData->branch_id)->count();
            $pages = [];
            $tmpPageCount = ceil($count / 10);
            for($i=1; $i<=$tmpPageCount; $i++){
                $pages[] = $i;
            }

            $transformer = new RoomTransformer();
            foreach ($rooms as $room){
                $arr[] = $transformer->transform($room);
            }

            $data = [
                'rooms'     => $arr,
                'count'     => $count,
                'pages'     => $pages,
            ];

            return Response::json($data, 200);
        }
        catch (\Exception $ex){
            return Response::json('failed' . $ex, 500);
        }
    }
}
