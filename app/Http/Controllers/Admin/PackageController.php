<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Libs\Utilities;
use App\Models\AdminUser;
use App\Models\Branch;
use App\Models\Package;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Status;
use App\Transformer\PackageTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class PackageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index()
    {
        $user = Auth::guard('admin')->user();
        $packages = DB::table('packages')
            ->where('branch_id', '=', $user->branch_id)
            ->get();

        return view('admin.package.index', compact('packages'));
    }

    public function getIndex(Request $request)
    {
        $packages = Package::with('room_type')
            ->with('status')
            ->with('branch')
            ->with('createdBy')
            ->with('updatedBy')
            ->where('id', '>', 0)
            ->where('branch_id')
            ->orderByDesc('id');

        return DataTables::of($packages)
            ->setTransformer(new PackageTransformer)
            ->make(true);
    }

    public function saveImage($image, $code)
    {
        // check folder exists
        $tmpPath = Storage::disk('items')->path('');
        if (! Storage::exists($tmpPath)) {
            // create the folder
            Storage::disk('items')->makeDirectory('');
        }

        $mimeType = array_slice(explode('/', $image->getMimeType()), -1)[0];

        $date = Carbon::now('Asia/Jakarta');
        $imageName = $code.'_'.$date->year.$date->month.$date->day.time().'.'.$mimeType;

        $imageLoc = Storage::disk('items')->path(''); // save to directory
        // $imagePath = array_slice(explode('public', $imageLoc), -1)[0]; //save path to database
        $imagePath = $imageLoc.$imageName; // save path to database

        // reduce size img
        if ($mimeType == 'jpeg') {
            $sourceImage = imagecreatefromjpeg($image);
            $width = imagesx($sourceImage);
            $height = imagesy($sourceImage);
            $newWidth = $width * 0.2;
            $newHeight = $height * 0.2;
            $newImage = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagejpeg($newImage, $imagePath, 60);
        } elseif ($mimeType == 'png') {
            $sourceImage = imagecreatefrompng($image);
            $width = imagesx($sourceImage);
            $height = imagesy($sourceImage);
            $newWidth = $width * 0.2;
            $newHeight = $height * 0.2;
            $newImage = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            imagepng($newImage, $imagePath, 5);
        } elseif ($mimeType == 'webp') {
            $sourceImage = imagecreatefromwebp($image);
            $width = imagesx($sourceImage);
            $height = imagesy($sourceImage);
            $newWidth = $width * 0.2;
            $newHeight = $height * 0.2;
            $newImage = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagewebp($newImage, $imagePath, 60);
        }

        return $imageName;
    }

    public function create()
    {
        $user = Auth::guard('admin')->user();
        // $roomTypes = RoomType::where('branch_id', '=', $user->branch_id)->get();
        $roomTypes = RoomType::all();
        $data = [
            'roomTypes' => $roomTypes,
        ];

        return view('admin.package.create')->with($data);
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'duration' => 'required',
                'price' => 'required',
                'partner_count' => 'required',
                'room_type_id' => 'required',
                'badge_status' => 'nullable|in:recommended,best_seller',
                'image' => 'nullable|image|max:3072',
                //                'extend_duration'   => 'required',
                //                'extend_price'      => 'required',
            ], [
                'name.required' => 'Package Name wajib diisi!',
                'duration.required' => 'Package duration wajib diisi!',
                'price.required' => 'Package price wajib dipilih!',
                'partner_count.required' => 'Package jumlah therapis wajib dipilih!',
                'room_type_id.required' => 'Package tipe room wajib dipilih!',
                'badge_status.in' => 'Badge status tidak valid!',
                'image.image' => 'Gambar harus berupa gambar!',
                'image.max' => 'Ukuran gambar maksimal 3MB!',
                //                'extend_duration.required'  => 'Package durasi extend wajib dipilih!',
                //                'extend_price.required'     => 'Package biaya extend wajib dipilih!',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput($request->all());
            }

            $user = Auth::guard('admin')->user();
            $userData = AdminUser::where('id', $user->id)->first();
            $nPackage = new Package;
            $nPackage->name = $request->input('name');
            $nPackage->price = Utilities::toFloat($request->input('price'));
            $nPackage->duration = $request->input('duration');
            $nPackage->description = $request->input('description');
            $nPackage->partner_count = $request->input('partner_count');
            $nPackage->branch_id = $userData->branch_id;
            $nPackage->status_id = 1;
            $nPackage->badge_status = $request->input('badge_status');
            //            $nPackage->extend_price = Utilities::toFloat($request->input('extend_price'));
            //            $nPackage->extend_duration = $request->input('extend_duration');
            //            $nPackage->additional_partner_price = Utilities::toFloat($request->input('additional_partner_price'));
            //            $nPackage->additional_person_price = Utilities::toFloat($request->input('additional_person_price'));
            $nPackage->room_type_id = $request->input('room_type_id');
            $nPackage->created_by = $user->id;
            $nPackage->updated_by = $user->id;
            $nPackage->save();

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = $this->saveImage($image, $nPackage->code);
                $nPackage->image_path = $imageName;
                $nPackage->save();
            }

            Session::flash('success', 'Sukses membuat data paket baru!');

            return redirect()->route('admin.package.index');
        } catch (\Exception $ex) {
            Session::flash('error', 'Maaf kesalahan terjadi!'.$ex);

            return back()->withInput($request->all());
        }
    }

    public function show($id)
    {
        $package = Package::with('room_type')
            ->with('status')
            ->with('branch')
            ->with('createdBy')
            ->with('updatedBy')->where('id', $id)->first();

        if ($package == null) {
            Session::flash('error', 'Paket tidak ditemukan!');

            return back();
        }

        $transformer = new PackageTransformer;
        $data = [
            'package' => $transformer->transform($package),
        ];

        return view('admin.package.show')->with($data);
    }

    public function edit($id)
    {
        $branches = Branch::all();
        $roomTypes = RoomType::all();
        $statuses = Status::all();
        $package = Package::where('id', $id)->first();
        if ($package == null) {
            Session::flash('error', 'Paket tidak ditemukan!');

            return back();
        }

        $data = [
            'package' => $package,
            'branches' => $branches,
            'roomTypes' => $roomTypes,
            'statuses' => $statuses,
        ];

        return view('admin.package.edit')->with($data);
    }

    public function update(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'duration' => 'required',
                'price' => 'required',
                'partner_count' => 'required',
                'room_type_id' => 'required',
                'badge_status' => 'nullable|in:recommended,best_seller',
                'image' => 'nullable|image|max:3072',
            ], [
                'name.required' => 'Package Name wajib diisi!',
                'duration.required' => 'Package duration wajib diisi!',
                'price.required' => 'Package price wajib dipilih!',
                'partner_count.required' => 'Package jumlah therapis wajib dipilih!',
                'room_type_id.required' => 'Package tipe room wajib dipilih!',
                'badge_status.in' => 'Badge status tidak valid!',
                'image.image' => 'Gambar harus berupa gambar!',
                'image.max' => 'Ukuran gambar maksimal 3MB!',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput($request->all());
            }

            $user = Auth::guard('admin')->user();
            $userData = AdminUser::where('id', $user->id)->first();
            $nPackage = Package::where('id', $request->input('id'))->first();
            if ($nPackage == null) {
                Session::flash('error', 'Paket tidak ditemukan!');

                return back();
            }
            $nPackage->name = $request->input('name');
            $nPackage->price = Utilities::toFloat($request->input('price'));
            $nPackage->duration = $request->input('duration');
            $nPackage->description = $request->input('description');
            $nPackage->partner_count = $request->input('partner_count');
            $nPackage->branch_id = $userData->branch_id;
            $nPackage->status_id = 1;
            $nPackage->badge_status = $request->input('badge_status');
            //            $nPackage->extend_price = Utilities::toFloat($request->input('extend_price'));
            //            $nPackage->extend_duration = $request->input('extend_duration');
            //            $nPackage->additional_partner_price = Utilities::toFloat($request->input('additional_partner_price'));
            //            $nPackage->additional_person_price = Utilities::toFloat($request->input('additional_person_price'));
            $nPackage->room_type_id = $request->input('room_type_id');
            $nPackage->status_id = $request->input('status');
            $nPackage->updated_by = $user->id;
            $nPackage->save();

            if ($request->hasFile('image')) {
                // Delete old image
                if (! is_null($nPackage->image_path) && $nPackage->image_path !== 'storage/images/items/default.jpg') {
                    Storage::disk('package_photos')->delete($nPackage->image_path);
                }
                // Save new image
                $image = $request->file('image');
                $imageName = $this->saveImage($image, $nPackage->code);
                $nPackage->image_path = $imageName;
                $nPackage->save();
            }

            Session::flash('success', 'Sukses mengubah data paket!');

            return redirect()->route('admin.package.edit', ['id' => $nPackage->id]);
        } catch (\Exception $ex) {
            Session::flash('error', 'Maaf kesalahan terjadi!'.$ex);

            return back()->withInput($request->all());
        }
    }

    public function delete(Request $request)
    {
        try {
            $package = Package::where('id', $request->input('id'))->first();
            if ($package == null) {
                Session::flash('error', 'Paket tidak ditemukan!');

                return back();
            }
            $package->delete();

            return Response::json('Sukses', 200);
        } catch (\Exception $ex) {
            return Response::json('error', 200);
        }
    }

    public function getPackages(Request $request)
    {
        try {
            $skip = 0;
            $take = 10;
            if ($request->exists('skip')) {
                $skip = $request->query('skip');
            }

            if ($request->exists('take')) {
                $take = $request->query('take');
            }

            $arr = [];
            $user = Auth::guard('admin')->user();

            $userData = AdminUser::where('id', $user->id)->first();
            $packages = Package::where('branch_id', $userData->branch_id)
                ->where('status_id', '!=', 2);

            if ($request->exists('search') && $request->query('search') != '') {
                $packages = $packages->where('name', 'like', '%'.$request->query('search').'%');
            }

            error_log($request->query('is_birthday'));

            if (! $request->exists('is_birthday')) {
                $packages = $packages->where('is_birthday_promo', '=', 0);
            } else {
                if ($request->query('is_birthday') == 0) {
                    $packages = $packages->where('is_birthday_promo', '=', 0);
                }
            }

            $packages = $packages->skip($skip)->take($take)->get();
            $count = DB::table('packages')->where('branch_id', $userData->branch_id)->count();
            $pages = [];
            $tmpPageCount = ceil($count / 10);
            for ($i = 1; $i <= $tmpPageCount; $i++) {
                $pages[] = $i;
            }

            $transformer = new PackageTransformer;
            foreach ($packages as $package) {
                $arr[] = $transformer->transform($package);
            }

            $data = [
                'packages' => $arr,
                'count' => $count,
                'pages' => $pages,
            ];

            return Response::json($data, 200);
        } catch (\Exception $ex) {
            Log::error($ex);

            return Response::json('failed'.$ex, 500);
        }
    }

    public function select(Request $request)
    {
        $term = trim($request->search);

        $user = Auth::guard('admin')->user();
        $userData = AdminUser::find($user->id);
        $branchId = $userData->branch_id;

        $packages = DB::table('packages')
            ->join('room_types', 'packages.room_type_id', '=', 'room_types.id')
            ->select('packages.id', 'packages.code', 'packages.name', 'packages.duration', 'room_types.name as room_type')
            ->where('status_id', '!=', 2)
            ->where(function ($q) use ($term) {
                $q->where('packages.code', 'LIKE', '%'.$term.'%')
                    ->orWhere('packages.name', 'LIKE', '%'.$term.'%');
            })
            ->where('packages.branch_id', $branchId)
            ->orderBy('packages.room_type_id')
            ->orderBy('packages.code') // Then order by name
            ->get();

        $grouped_packages = [];

        foreach ($packages as $package) {
            $grouped_packages[$package->room_type]['text'] = $package->room_type;
            $grouped_packages[$package->room_type]['children'][] = [
                'id' => $package->id,
                'text' => $package->code.' - '.$package->name,
            ];
        }

        return Response::json(array_values($grouped_packages));
    }

    public function get(Request $request)
    {
        try {
            $user = Auth::guard('admin')->user();
            $userData = AdminUser::find($user->id);
            $room = Room::find($request->query('room_id'));
            $roomType = RoomType::where('name', $room->type)->where('branch_id', $userData->branch_id)->first();

            $packages = Package::where('branch_id', $userData->branch_id)
                ->where('room_type_id', $roomType->id)
                ->get();

            return Response::json($packages, 200);
        } catch (\Exception $ex) {
            return Response::json($ex, 500);
        }
    }

    public function getDetail(Request $request)
    {
        try {
            $package = Package::where('id', $request->input('package_id'))->first();
            if ($package == null) {
                return Response::json([], 200);
            }

            return Response::json($package, 200);
        } catch (\Exception $ex) {
            Log::channel('package')->error('[Package Get Detail] - '.$ex);

            return Response::json($ex, 500);
        }
    }
}
