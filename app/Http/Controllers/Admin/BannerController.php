<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class BannerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request){
        try {
            $admin = Auth::guard('admin')->user();

            $filterStatusId = -1;
            if($request->status_id != null){
                $filterStatusId = intval($request->status_id);
            }

            $data = [
                'filterStatusId' => $filterStatusId,
            ];

            return view('admin.banner.index')->with($data);
        }
        catch (\Exception $ex){
            Log::channel('banner')->error('Admin/BannerController - index '. $ex);
            return 'Terjadi kesalahan pada sistem!';
        }
    }

    public function getIndex(Request $request)
    {
//        $start = Carbon::createFromFormat('!d-m-Y', $request->input('start_date'), 'Asia/Jakarta');
//        $end = Carbon::createFromFormat('!d-m-Y', $request->input('end_date'), 'Asia/Jakarta');
//        $end->addDays(1);

        $admin = Auth::guard('admin')->user();

        $banners = Banner::query();

        if($request->filled('filter_status_id')){
            $filterStatusId = $request->input('filter_status_id');
            if($filterStatusId > 0){
                $banners->where('status_id', '=', $filterStatusId);
            }
        }

        return DataTables::of($banners)
            ->editColumn('created_at', function ($row) {
                return Carbon::parse($row->created_at)->toIso8601String();
            })
            ->editColumn('status', function ($row) {
                return $row->status->display;
            })
            ->addColumn('action', function ($row) {
                $buttons = '';
                $buttons .= '<a href="' . route('admin.banner.show', ['id' => $row->id]) . '" class="btn btn-info me-1"><i class="far fa fa-eye"></i> Show</a>';
                $buttons .= '<a href="' . route('admin.banner.edit', ['id' => $row->id]) . '" class="btn btn-primary me-1"><i class="fa fa-pencil-alt"></i> Edit</a>';

                return $buttons;
            })
            ->make();
    }

    public function create()
    {
        return view('admin.banner.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'main_image' => 'required|image|max:3072',
            'name' => 'required',
        ], [
            'main_image.required' => 'Gambar harus diisi!',
            'main_image.image' => 'Gambar harus berupa gambar!',
            'main_image.max' => 'Ukuran gambar maksimal 3MB!',
            'name.required' => 'Nama harus diisi!',
        ]);

        if ($validator->fails()) {
            Session::flash('error', $validator->errors()->first());
            return back()->withInput($request->except('_token'));
        }

        try {
            DB::beginTransaction();

            $imageName = '';
            if ($request->hasFile('main_image')) {
                $image = $request->file('main_image');
                $imageName = $this->compressImage($request->input('name'), $image);
            }

            $latestBanner = Banner::latest('index')->first();
            $latestIndex = ($latestBanner ? $latestBanner->index + 1 : 0);

            Banner::create([
                'name' => $request->input('name'),
                'type' => 'voucher',
                'description' => $request->input('description'),
                'index' => $request->input('status') == 2 ? null : $latestIndex,
                'branch_id' => $request->input('branch_id') ?? null,
                'coupon_id' => $request->input('coupon_id') ?? null,
                'image_path' => $imageName,
                'status_id' => $request->input('status'),
                'created_by' => Auth::guard('admin')->user()->id,
            ]);

            DB::commit();

            Session::flash('success', 'Banner berhasil dibuat!');
            return redirect()->route('admin.banner.index');
        } catch (\Exception $exception) {
            DB::rollBack();

            Log::channel('banner')->error('Admin/BannerController - create '. $exception);
            Session::flash('error', 'Internal Server Error!');
            return back()->with($request->except('_token'));
        }
    }

    public function edit($id)
    {
        $banner = Banner::find($id);

        return view('admin.banner.edit', compact('banner'));
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'main_image' => 'image|max:3072',
            'name' => 'required',
        ], [
            'main_image.image' => 'Gambar harus berupa gambar!',
            'main_image.max' => 'Ukuran gambar maksimal 3MB!',
            'name.required' => 'Nama harus diisi!',
        ]);

        if ($validator->fails()) {
            Session::flash('error', $validator->errors()->first());
            return back()->withInput($request->except('_token'));
        }

        try {
            DB::beginTransaction();

            $banner = Banner::find($request->input('id'));

            $imageName = '';
            if ($request->hasFile('main_image')) {
                $image = $request->file('main_image');
                $imageName = $this->compressImage($request->input('name'), $image);
                $banner->update(['image_path' => $imageName]);
            }

            $latestIndex = $banner->index;
            if ($latestIndex == null) {
                $latestBanner = Banner::latest('index')->first();
                $latestIndex = ($latestBanner ? $latestBanner->index + 1 : 0);
            }

            $banner->update([
                'name' => $request->input('name'),
                'index' => $request->input('status') == 2 ? null : $latestIndex,
                'description' => $request->input('description'),
                'branch_id' => $request->input('branch_id') ?? null,
                'coupon_id' => $request->input('coupon_id') ?? null,
                'status_id' => $request->input('status'),
                'updated_by' => Auth::guard('admin')->user()->id,
            ]);

            DB::commit();

            Session::flash('success', 'Banner berhasil diupdate!');
            return redirect()->route('admin.banner.index');
        } catch (\Exception $exception) {
            DB::rollBack();

            Log::channel('banner')->error('Admin/BannerController - update '. $exception);
            Session::flash('error', 'Internal Server Error! '. $exception->getMessage());
            return back()->withInput();
        }
    }

    public function show($id)
    {
        $banner = Banner::find($id);

        return view('admin.banner.show', compact('banner'));
    }

    public function compressImage($name, $image)
    {
        $bannerName = strtolower(str_replace(' ', '_', $name)) . '_' . date("Ymd");
        $imageMimeType = strtolower($image->getClientOriginalExtension());
        $bannerImageName = $bannerName . '.' . $imageMimeType;

        // Define the full storage path
        $storagePath = Storage::disk('banners')->path($bannerImageName);

        // Save the original image
        Storage::disk('banners')->put($bannerImageName, file_get_contents($image->getRealPath()));

        // Check file size
        if ($image->getSize() > 700000) {
            $compressionQuality = 50;

            // Create an image resource
            $sourceImage = imagecreatefromstring(file_get_contents($image->getRealPath()));

            if (!$sourceImage) {
                return false; // Prevent errors if the image fails to load
            }

            // Resize
            $width = imagesx($sourceImage);
            $height = imagesy($sourceImage);
            $newWidth = $width * 0.4;
            $newHeight = $height * 0.4;
            $newImage = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            // Save the compressed image
            if ($imageMimeType == 'jpeg' || $imageMimeType == 'jpg') {
                imagejpeg($newImage, $storagePath, $compressionQuality);
            } elseif ($imageMimeType == 'png') {
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                imagepng($newImage, $storagePath, 9);
            } elseif ($imageMimeType == 'webp') {
                imagewebp($newImage, $storagePath, $compressionQuality);
            }

            // Clean up
            imagedestroy($sourceImage);
            imagedestroy($newImage);
        }

        return $bannerImageName; // Return filename only
    }
}
