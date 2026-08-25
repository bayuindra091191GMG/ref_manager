<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Transformer\BranchTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;

class BranchController extends Controller
{
    private const EXTRA_TEXT_OPTIONS = [
        'Massage',
        'Refleksi',
        'Spa',
        'Facial',
        'Salon',
        'Pranikah',
        'Studio Simi',
        'Tambahan Treatment',
        'Food',
        'Beverage',
    ];

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(){
        return view('admin.branch.index');
    }

    public function getIndex(Request $request){
        $branches = DB::table('branches')
            ->where('branches.id', '>', 0);

        return DataTables::of($branches)
            ->setTransformer(new BranchTransformer)
            ->make(true);
    }

    public function create(){
        return view('admin.branch.create', [
            'extraTextOptions' => self::EXTRA_TEXT_OPTIONS,
        ]);
    }

    public function store(Request $request){
        try{
            $validator = Validator::make($request->all(),[
                'code'          => [
                    'required',
                    'alpha_num',
                    'max:8',
                    Rule::unique('branches', 'code'),
                ],
                'name'          => 'required|max:100',
                'description'   => 'nullable|max:200',
                'address'       => 'nullable|max:200',
                'phone'         => 'nullable|max:30',
                'latitude'      => 'nullable|numeric|between:-90,90',
                'longitude'     => 'nullable|numeric|between:-180,180',
                'extra_text'    => 'nullable|array',
                'extra_text.*'  => ['required', Rule::in(self::EXTRA_TEXT_OPTIONS)],
                'image'         => 'required|file|mimes:jpg,jpeg,png|max:5120',
            ],[
                'code.required'         => 'Kode cabang wajib diisi!',
                'code.alpha_num'        => 'Kode cabang hanya boleh berisi huruf dan angka!',
                'code.max'              => 'Kode cabang maksimal 8 karakter!',
                'code.unique'           => 'Kode cabang sudah digunakan!',
                'name.required'         => 'Nama cabang wajib diisi!',
                'name.max'              => 'Nama cabang maksimal 100 karakter!',
                'description.max'       => 'Deskripsi maksimal 200 karakter!',
                'address.max'           => 'Alamat maksimal 200 karakter!',
                'phone.max'             => 'Nomor telepon maksimal 30 karakter!',
                'latitude.numeric'      => 'Latitude harus berupa angka!',
                'latitude.between'      => 'Latitude harus berada di antara -90 sampai 90!',
                'longitude.numeric'     => 'Longitude harus berupa angka!',
                'longitude.between'     => 'Longitude harus berada di antara -180 sampai 180!',
                'extra_text.array'      => 'Extra text tidak valid!',
                'extra_text.*.in'       => 'Pilihan extra text tidak valid!',
                'image.required'        => 'Gambar cabang wajib diisi!',
                'image.mimes'           => 'Gambar cabang harus berupa PNG atau JPG!',
                'image.max'             => 'Ukuran gambar cabang maksimal 5 MB!',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput($request->all());
            }

            $user = Auth::guard('admin')->user();
            $code = strtoupper(trim($request->input('code')));
            $nBranch = new Branch();
            $nBranch->code = $code;
            $nBranch->name = trim($request->input('name'));
            $nBranch->description = $request->filled('description') ? trim($request->input('description')) : null;
            $nBranch->address = $request->filled('address') ? trim($request->input('address')) : null;
            $nBranch->phone = $request->filled('phone') ? trim($request->input('phone')) : null;
            $nBranch->latitude = $request->filled('latitude') ? (float) $request->input('latitude') : null;
            $nBranch->longitude = $request->filled('longitude') ? (float) $request->input('longitude') : null;
            $nBranch->extra_text = $this->formatExtraText($request->input('extra_text', []));
            $nBranch->image_filename = $this->saveImage($request->file('image'), $code);
            $nBranch->created_by = $user->id;
            $nBranch->save();

            Session::flash('success', 'Sukses membuat data cabang baru!');
            return redirect()->route('admin.branch.index');
        }
        catch (\Exception $ex){
            Session::flash('error', 'Maaf kesalahan terjadi!' . $ex);
            return back()->withInput($request->all());
        }
    }

    public function edit($id){
        $branch = Branch::where('id', $id)->first();
        if($branch == null){
            Session::flash('error', 'Branch tidak ditemukan!');
            return back();
        }

        $data = [
            'branch'  => $branch,
            'extraTextOptions' => self::EXTRA_TEXT_OPTIONS,
        ];

        return view('admin.branch.edit')->with($data);
    }

    public function update(Request $request){
        try{
            $validator = Validator::make($request->all(),[
                'id'            => 'required',
                'code'          => [
                    'required',
                    'alpha_num',
                    'max:8',
                    Rule::unique('branches', 'code')->ignore($request->input('id')),
                ],
                'name'          => 'required|max:100',
                'description'   => 'nullable|max:200',
                'address'       => 'nullable|max:200',
                'phone'         => 'nullable|max:30',
                'latitude'      => 'nullable|numeric|between:-90,90',
                'longitude'     => 'nullable|numeric|between:-180,180',
                'extra_text'    => 'nullable|array',
                'extra_text.*'  => ['required', Rule::in(self::EXTRA_TEXT_OPTIONS)],
                'image'         => 'required_without:current_image|file|mimes:jpg,jpeg,png|max:5120',
            ],[
                'code.required'         => 'Kode cabang wajib diisi!',
                'code.alpha_num'        => 'Kode cabang hanya boleh berisi huruf dan angka!',
                'code.max'              => 'Kode cabang maksimal 8 karakter!',
                'code.unique'           => 'Kode cabang sudah digunakan!',
                'name.required'         => 'Nama cabang wajib diisi!',
                'name.max'              => 'Nama cabang maksimal 100 karakter!',
                'description.max'       => 'Deskripsi maksimal 200 karakter!',
                'address.max'           => 'Alamat maksimal 200 karakter!',
                'phone.max'             => 'Nomor telepon maksimal 30 karakter!',
                'latitude.numeric'      => 'Latitude harus berupa angka!',
                'latitude.between'      => 'Latitude harus berada di antara -90 sampai 90!',
                'longitude.numeric'     => 'Longitude harus berupa angka!',
                'longitude.between'     => 'Longitude harus berada di antara -180 sampai 180!',
                'extra_text.array'      => 'Extra text tidak valid!',
                'extra_text.*.in'       => 'Pilihan extra text tidak valid!',
                'image.required_without'=> 'Gambar cabang wajib diisi!',
                'image.mimes'           => 'Gambar cabang harus berupa PNG atau JPG!',
                'image.max'             => 'Ukuran gambar cabang maksimal 5 MB!',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput($request->all());
            }

            $user = Auth::guard('admin')->user();
            $branch = Branch::where('id', $request->input('id'))->first();
            if($branch == null){
                Session::flash('error', 'Branch tidak ditemukan!');
                return back()->withInput($request->all());
            }
            $code = strtoupper(trim($request->input('code')));
            $branch->code = $code;
            $branch->name = trim($request->input('name'));
            $branch->description = $request->filled('description') ? trim($request->input('description')) : null;
            $branch->address = $request->filled('address') ? trim($request->input('address')) : null;
            $branch->phone = $request->filled('phone') ? trim($request->input('phone')) : null;
            $branch->latitude = $request->filled('latitude') ? (float) $request->input('latitude') : null;
            $branch->longitude = $request->filled('longitude') ? (float) $request->input('longitude') : null;
            $branch->extra_text = $this->formatExtraText($request->input('extra_text', []));

            if($request->hasFile('image')){
                $this->deleteImage($branch->image_filename);
                $branch->image_filename = $this->saveImage($request->file('image'), $code);
            }

            $branch->updated_by = $user->id;
            $branch->save();

            Session::flash('success', 'Sukses mengubah data cabang!');
            return redirect()->route('admin.branch.index');
        }
        catch (\Exception $ex){
            Session::flash('error', 'Maaf kesalahan terjadi!');
            return back()->withInput($request->all());
        }
    }

    public function delete(Request $request){
        try{
            $branch = Branch::where('id', $request->input('id'))->first();
            if($branch == null){
                Session::flash('error', 'Branch tidak ditemukan!');
                return back();
            }
            $this->deleteImage($branch->image_filename);
            $branch->delete();

            return Response::json('Sukses', 200);
        }
        catch (\Exception $ex){
            return Response::json('error', 200);
        }
    }

    public function select(Request $request)
    {
        $term = trim($request->search);

        $branches = DB::table('branches')
            ->where(function($q) use ($term){
                $q->where('code', 'LIKE', '%'. $term. '%')
                    ->orWhere('name', 'LIKE', '%'. $term. '%');
            })
            ->get();

        $formatted_tags = [];

        foreach ($branches as $branch) {
            $formatted_tags[] = ['id' => $branch->id, 'text' => $branch->name];
        }

        return Response::json($formatted_tags);
    }

    private function formatExtraText(array $extraText): ?string
    {
        $values = array_values(array_intersect(self::EXTRA_TEXT_OPTIONS, $extraText));

        return empty($values) ? null : implode('|', $values);
    }

    private function saveImage($image, string $code): string
    {
        $directory = public_path('images/branches');
        if(!File::isDirectory($directory)){
            File::makeDirectory($directory, 0755, true);
        }

        $extension = strtolower($image->getClientOriginalExtension());
        if($extension === 'jpeg'){
            $extension = 'jpg';
        }

        do {
            $filename = strtoupper($code) . '_' . Str::lower(Str::random(4)) . '.' . $extension;
            $path = $directory . DIRECTORY_SEPARATOR . $filename;
        } while (File::exists($path));

        $image->move($directory, $filename);

        return $filename;
    }

    private function deleteImage(?string $filename): void
    {
        if(empty($filename)){
            return;
        }

        $path = public_path('images/branches/' . basename($filename));
        if(File::exists($path)){
            File::delete($path);
        }
    }
}
