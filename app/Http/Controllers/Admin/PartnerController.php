<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Libs\Utilities;
use App\Models\AdminUser;
use App\Models\Item;
use App\Models\Partner;
use App\Models\PartnerContract;
use App\Models\PkwtHistory;
use App\Transformer\PartnerTransformer;
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

class PartnerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request){
        try {
            $admin = Auth::guard('admin')->user();
            $filterBranchId = $admin->branch_id;

            return view('admin.partner.index',compact('filterBranchId'));
        }
        catch (\Exception $ex){
            Log::channel('master_data')->error('Admin/PartnerController - index '. $ex);
            return 'Terjadi kesalahan pada sistem!';
        }
    }

    public function getIndex(Request $request){
        $partners = DB::table('partners')
            ->join('statuses', 'partners.status_id', '=', 'statuses.id')
            ->select('partners.id as id',
                'partners.code as code',
                'partners.name as name',
                'partners.alias as alias',
                'partners.phone as phone',
                'partners.attend_machine_id as number',
                'partners.join_date as join_date',
                'partners.is_present as is_present',
                'partners.type as type',
                'partners.status_id as status_id',
                'statuses.display as status_display');

        // Filter branch id
        if($request->filled('filter_branch_id')){
            $filterBranchId = $request->input('filter_branch_id');
            if($filterBranchId > 0){
                $partners = $partners->where('partners.branch_id', '=', $filterBranchId);
            }
        }

        if($request->get('contract')){
            $partners->join('partner_contracts', 'partners.id', '=', 'partner_contracts.partner_id')
                ->whereRaw('DATE_ADD(partner_contracts.end_date, INTERVAL -1 MONTH) <= CURDATE() AND CURDATE() <= partner_contracts.end_date');
        }

        return DataTables::of($partners)
            ->setTransformer(new PartnerTransformer())
            ->make(true);
    }

    public function show($id){
        try {

            $partner = Partner::with(['status'])
                ->where('id', '=', $id)
                ->first();

            if(empty($partner)){
                Session::flash('error', 'BAD REQUEST');
                return redirect()->back();
            }

            //getting transactions per months
            $startSystem = Carbon::create(2023,2,1,0,0,0,'Asia/Jakarta');
            $now = Carbon::now('Asia/Jakarta');
            $length = $startSystem->diffInMonths($now);
            $tmpNow = $now->copy();
            $transactions = [];
            for($i=0;$i<$length;$i++){
                $tmpDate = $tmpNow->copy();
                $tmpStart = $tmpDate->copy()->startOfMonth();
                $tmpEnd = $tmpDate->copy()->endOfMonth();
                $nTrx = [
                    'date'      => $tmpStart->format('F, Y'),
                    'details'   => [],
                ];
                //get transactions
                $trxQuery = "select hd.code, p.name, (dt.price * dt.qty) as amount, dt.start_time, dt.end_time, hd.member_name
                    from transaction_detail_partners tdp
                    inner join transaction_details dt on dt.id = tdp.transaction_detail_id
                    inner join packages p on p.id = dt.package_id
                    inner join transaction_headers hd on hd.id = dt.header_id
                    where tdp.partner_id = ". $partner->id ."
                    and dt.package_id is not null
                    and hd.created_at >= '". $tmpStart->format('Y-m-d H:i:s') ."'
                    and hd.created_at <= '". $tmpEnd->format('Y-m-d H:i:s') ."';";

                $tmpTrxs = DB::select($trxQuery);

                $no = 1;
                foreach ($tmpTrxs as $trx){
                    $tmpTrxData = [
                        'no'            => $no,
                        'code'          => $trx->code,
                        'name'          => $trx->name,
                        'amount'        => $trx->amount,
                        'start_time'    => $trx->start_time,
                        'end_time'      => $trx->end_time,
                        'customer'      => $trx->member_name,
                    ];
                    $nTrx['details'][] = $tmpTrxData;
                    $no++;
                }
                $transactions[] = $nTrx;

                $tmpNow->subMonth();
            }

            return view('admin.partner.show', compact('partner', 'transactions'));
        }
        catch (\Exception $ex){
            Log::channel('master_data')->error('Admin/PartnerController - index '. $ex);
            Session::flash('error', $ex);
            return redirect()->back();
        }
    }

    public function select(Request $request){
        $term = trim($request->search);
        $withNumber = 0;

        // Check with number parameter
        if($request->with_number != null){
            $withNumber = intval($request->with_number);
        }

        $admin = Auth::guard('admin')->user();

        $partners = Partner::where(function($q) use ($term){
                $q->where('code', 'LIKE', '%'. $term. '%')
                    ->orWhere('number', 'LIKE', '%'. $term. '%')
                    ->orWhere('alias', 'LIKE', '%'. $term. '%')
                    ->orWhere('name', 'LIKE', '%'. $term. '%');
            })
            ->where('is_present', '=', 1)
            ->where('status_id', '=', 5)
            ->where('branch_id', '=', $admin->branch_id)
            ->take(10);

//        if($branchId > 0){
//            $partners = $partners->where('branch_id', $branchId);
//        }

        $partners = $partners->orderBy('name')
            ->get();

        $formatted_tags = [];

        foreach ($partners as $partner) {
            $formatted_tags[] = ['id' => $partner->id, 'text' => $partner->name  . ' - ' . ($partner->gender == 'P' ? 'Perempuan' : 'Laki-laki') . ' - ' . $partner->partner_level->name];
//            if($withNumber === 1){
//                $formatted_tags[] = ['id' => $partner->number, 'text' => $partner->name . ' - ' . $partner->partner_level->name];
//            }
//            else{
//                $formatted_tags[] = ['id' => $partner->id, 'text' => $partner->name . ' - ' . $partner->partner_level->name];
//            }
        }

        return Response::json($formatted_tags);
    }

    public function getAddOnDetail(Request $request)
    {
        try {
            $partner = Partner::where('id', $request->input('partner_id'))->first();
            $addon = Item::where('id', $partner->partner_level->item_id)->first();

//            return Response::json($addon, 200);

            $data = [
                'id' => $addon->id ?? '',
                'name' => $addon->name ?? '',
                'price' => $addon->price ?? 0,
            ];

            return response()->json([
                'detail' => $data,
                'message' => $partner->partner_level->item_id != null
                    ? 'Perhatian! Terdapat harga tambahan sebesar Rp ' . number_format($addon->price, 0, ',', '.') . ' karena terapis ini memiliki level ' . $partner->partner_level->name
                    : '',
            ]);
        } catch (\Exception $ex) {
            Log::channel('partner')->error('[Partner Get Add On Detail] - ' . $ex );
            return Response::json($ex, 500);
        }
    }

    public function selectOura(Request $request){
        $term = trim($request->search);

        $admin = Auth::guard('admin')->user();

        $partners = DB::table('partners')
            ->where(function($q) use ($term){
                $q->where('code', 'LIKE', '%'. $term. '%')
                    ->orWhere('number', 'LIKE', '%'. $term. '%')
                    ->orWhere('alias', 'LIKE', '%'. $term. '%')
                    ->orWhere('name', 'LIKE', '%'. $term. '%');
            })
            ->where('status_id', '=', 5)
            ->where('is_present', '=', 1)
            ->where('branch_id', '=', $admin->branch_id)
            ->orderBy('name')
            ->get();

        $formatted_tags = [];

        foreach ($partners as $partner) {
            $formatted_tags[] = ['id' => $partner->id. '#'. $partner->alias, 'text' => $partner->number. ' - '. $partner->name. " ($partner->alias)"];
        }

        return Response::json($formatted_tags);
    }

    public function selectForChange(Request $request){
        $term = trim($request->search);

        $user = Auth::guard('admin')->user();
        $userData = AdminUser::where('id', $user->id)->first();

        $partners = DB::table('partners')
            ->where('branch_id', $userData->branch_id)
            ->where('status_id', 5)
            ->where('is_present', 1)
            ->where(function($q) use ($term){
                $q->where('code', 'LIKE', '%'. $term. '%')
                    ->orWhere('number', 'LIKE', '%'. $term. '%')
                    ->orWhere('name', 'LIKE', '%'. $term. '%');
            });

        $partners = $partners->orderBy('number')
            ->get();

        $formatted_tags = [];

        foreach ($partners as $partner) {
            $formatted_tags[] = ['id' => $partner->id, 'text' => $partner->number. ' - '. $partner->alias];
        }

        return Response::json($formatted_tags);
    }

    /**
     * Function to show partner create form
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|string
     */
    public function create(){
        try {
            $admin = Auth::guard('admin')->user();
            $currentDate = Carbon::today('Asia/Jakarta')->format('d-m-Y');
            $partnerLevels = DB::table('partner_levels')
                ->where('branch_id', '=', $admin->branch_id)
                ->get();

            $data = [
                'currentDate' => $currentDate,
                'partnerLevels' => $partnerLevels,
                'branchId' => $admin->branch_id
            ];

            return view('admin.partner.create')->with($data);
        }
        catch (\Exception $ex){
            Log::channel('master_data')->error('Admin/PartnerController - create '. $ex);
            return 'Terjadi kesalahan pada sistem!';
        }
    }

    /**
     * Function to save newly created partner to database
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|max:200',
                'code' => 'required|max:100',
                'alias' => 'required|max:100',
                'no_ktp' => 'max:16',
            ],[
                'no_ktp.max' => 'No KTP maksimal 16 karakter!',
                'name.required' => 'Nama Terapis wajib diisi!',
                'name.max' => 'Nama Terapis maksimal 200 karakter!',
                'code.required' => 'Kode Karyawan sudah terdaftar!',
                'code.max' => 'Kode Karyawan maksimal 100 karakter!',
                'alias.required' => 'Alias wajib diisi!',
                'alias.max' => 'Alias maksimal 10 karakter!',
            ]);

            if ($validator->fails()) return redirect()
                ->back()
                ->withErrors($validator->errors())
                ->withInput($request->all());

            $admin = Auth::guard('admin')->user();

            // Validate unique code for selected branch
            $code = strtoupper(trim($request->input('code')));
            if(DB::table('partners')
                ->where('branch_id', '=', $admin->branch_id)
                ->where('code', '=', $code)
                ->exists()){
                Session::flash('error', 'Kode Karyawan sudah terpakai!');
                return redirect()->back()->withInput($request->all());
            }

            $newPartner = new Partner();
            $newPartner->branch_id = $admin->branch_id;
            $newPartner->code = $code;
            $newPartner->name = trim($request->input('name'));
            $newPartner->ktp = trim($request->input('no_ktp'));
            $newPartner->address = trim($request->input('address'));
            $newPartner->alias = strtoupper(trim($request->input('alias')));
            $newPartner->phone = $request->filled('phone') ? trim($request->input('phone')) : null;
            $newPartner->join_date = Carbon::parse($request->input('join_date'))->toDateTimeString();
            $newPartner->is_present = $request->input('is_present');
            $newPartner->gender = $request->input('gender');
            $newPartner->emergency_name = trim($request->input('emergency_name'));
            $newPartner->emergency_phone = trim($request->input('emergency_phone'));
            $newPartner->account_number = trim($request->input('account_number'));
            $newPartner->account_bank_name = trim($request->input('account_bank_name'));
            $newPartner->basic_salary_amount = trim($request->input('basic_salary_amount'));
            $newPartner->employement_status = trim($request->input('employement_status'));
            $newPartner->attend_machine_id = $request->filled('attend_machine_id') ? trim($request->input('attend_machine_id')) : null;
            $specialtyData = "";
            foreach($request->input('specialty') ?? [] as $specialty){
                $specialtyData .= $specialty. ", ";
            }
            $newPartner->specialty = $specialtyData;

            $level = intval($request->input('level'));
            $partnerLevel = DB::table('partner_levels')
                ->where('id', '=', $level)
                ->first();

            $newPartner->level_id = $level;
            $newPartner->type = $partnerLevel->name;
            $newPartner->status_id = 5;
            $newPartner->commission_percentage = Utilities::toFloat($request->input('commission_percentage'));

            $newPartner->save();

            if($request->hasFile('image')){
                $image = $request->file('image');
                $imageName = $this->saveImage($image, $newPartner->id);
                $newPartner->photo_filename = $imageName;
            }
            else{
                $newPartner->photo_filename = 'default_person_photo.png';
            }

            $newPartner->save();

            Session::flash('success', 'Berhasil tambah terapis baru!');
            return redirect()->route('admin.partner.show', ['id' => $newPartner->id]);
        }
        catch (\Exception $ex){
            Log::channel('master_data')->error('Admin/PartnerController - store '. $ex);
            Session::flash('error', 'Terjadi kesalahan pada sistem!');
            return redirect()->back()->withInput($request->all());
        }
    }

    public function edit($id){
        try {
            $partner = Partner::with(['status'])
                ->where('id', '=', $id)
                ->first();

            if(empty($partner)){
                Session::flash('error', 'BAD REQUEST');
                return redirect()->back();
            }

            $partnerLevels = DB::table('partner_levels')
                ->where('branch_id', '=', $partner->branch_id)
                ->get();

            $specialties = is_array($partner->specialty) ? $partner->specialty : explode(', ', $partner->specialty ?? '');
            $specialties = array_map('trim', $specialties);

            $data = [
                'partner' => $partner,
                'partnerLevels' => $partnerLevels,
                'specialties' => $specialties,
            ];

            return view('admin.partner.edit')->with($data);
        }
        catch (\Exception $ex){
            Log::channel('master_data')->error('Admin/PartnerController - index '. $ex);
            Session::flash('error', $ex);
            return redirect()->back();
        }
    }

    /**
     * Function to save edited partner to database
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id){
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|max:200',
                'code' => 'required|max:100',
                'alias' => 'required|max:100',
                'no_ktp' => 'max:16',
            ],[
                'no_ktp.max' => 'No KTP maksimal 16 karakter!',
                'name.required' => 'Nama Terapis wajib diisi!',
                'name.max' => 'Nama Terapis maksimal 200 karakter!',
                'code.required' => 'Kode Karyawan sudah terdaftar!',
                'code.max' => 'Kode Karyawan maksimal 100 karakter!',
                'alias.required' => 'Alias wajib diisi!',
                'alias.max' => 'Alias maksimal 10 karakter!',
            ]);

            if ($validator->fails()) return redirect()
                ->back()
                ->withErrors($validator->errors())
                ->withInput($request->all());

            $admin = Auth::guard('admin')->user();

            // Validate unique code for selected branch
            $code = strtoupper(trim($request->input('code')));
            if(DB::table('partners')
                ->where('id', '!=', $id)
                ->where('branch_id', '=', $admin->branch_id)
                ->where('code', '=', $code)
                ->exists()){
                Session::flash('error', 'Kode Karyawan sudah terpakai!');
                return redirect()->back()->withInput($request->all());
            }

            $updatedPartner = Partner::find($id);
            if(empty($updatedPartner)){
                Session::flash('error', 'BAD REQUEST!');
                return redirect()->back()->withInput($request->all());
            }

            $updatedPartner->code = $code;
            $updatedPartner->name = trim($request->input('name'));
            $updatedPartner->ktp = trim($request->input('no_ktp'));
            $updatedPartner->address = trim($request->input('address'));
            $updatedPartner->alias = strtoupper(trim($request->input('alias')));
            $updatedPartner->phone = $request->filled('phone') ? trim($request->input('phone')) : null;
            $updatedPartner->join_date = Carbon::parse($request->input('join_date'))->toDateTimeString();
            $updatedPartner->is_present = $request->input('is_present');
            $updatedPartner->gender = $request->input('gender');
            $updatedPartner->emergency_name = trim($request->input('emergency_name'));
            $updatedPartner->emergency_phone = trim($request->input('emergency_phone'));
            $updatedPartner->account_number = trim($request->input('account_number'));
            $updatedPartner->account_bank_name = trim($request->input('account_bank_name'));
            $updatedPartner->basic_salary_amount = trim($request->input('basic_salary_amount'));
            $updatedPartner->employement_status = trim($request->input('employement_status'));
            $updatedPartner->attend_machine_id = $request->filled('attend_machine_id') ? trim($request->input('attend_machine_id')) : null;
            $specialtyData = "";
            foreach($request->input('specialty') ?? [] as $specialty){
                $specialtyData .= $specialty. ", ";
            }
            $updatedPartner->specialty = $specialtyData;


            $level = intval($request->input('level'));
            $partnerLevel = DB::table('partner_levels')
                ->where('id', '=', $level)
                ->first();

            $updatedPartner->level_id = $level;
            $updatedPartner->type = $partnerLevel->name;
            $updatedPartner->status_id = $request->input('status');

            $updatedPartner->commission_percentage = Utilities::toFloat($request->input('commission_percentage'));

            if($request->hasFile('image')){
                if(!empty($updatedPartner->photo_filename)){
                    // Delete old image
                    Storage::disk('partners')->delete($updatedPartner->photo_filename);
                }

                $image = $request->file('image');
                $imageName = $this->saveImage($image, $updatedPartner->id);

                $updatedPartner->photo_filename = $imageName;
            }

            $updatedPartner->save();

            Session::flash('success', 'Berhasil ubah terapis!');
            return redirect()->route('admin.partner.show', ['id' => $id]);
        }
        catch (\Exception $ex){
            Log::channel('master_data')->error('Admin/PartnerController - store '. $ex);
            Session::flash('error', 'Terjadi kesalahan pada sistem! '. $ex->getMessage());
            return redirect()->back()->withInput($request->all());
        }
    }

    public function togglePresent(Request $request){
        try {
            if(!$request->filled('toggled_id')){
                return response()->json(collect([
                    'code' => 400,
                    'error' => 'BAD REQUEST'
                ]), 200);
            }

            $id = intval($request->input('toggled_id'));
            $partner = Partner::find($id);
            if(empty($partner)){
                return response()->json(collect([
                    'code' => 400,
                    'error' => 'BAD REQUEST'
                ]), 200);
            }

            if($partner->is_present === 1){
                $partner->is_present = 0;
            }
            else{
                $partner->is_present = 1;
            }
            $partner->save();

            return response()->json(collect([
                'code' => 200,
                'error' => 'none'
            ]), 200);
        }
        catch (\Exception $ex){
            Log::channel('setting')->error('Admin/PartnerController - togglePresent '. $ex);
            return response()->json(collect([
                'code' => 500,
                'error' => $ex->getMessage()
            ]), 500);
        }
    }

    public function toggleAllInactive(Request $request){
        try {
            $admin = Auth::guard('admin')->user();

            DB::table('partners')
                ->where('id', '>', 0)
                ->where('branch_id', '=', $admin->branch_id)
                ->update([
                    'is_present' => 0
                ]);

            Session::flash('success', 'Berhasil ubah kehadiran!');
            return redirect()->route('admin.partner.index');
        }
        catch (\Exception $ex){
            Log::channel('master_data')->error('Admin/PartnerController - toggleAllInactive '. $ex);
            Session::flash('error', 'Terjadi kesalahan pada sistem!');
            return redirect()->back()->withInput($request->all());
        }
    }

    public function getPartnerOura(Request $request){
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
            $admin = Auth::guard('admin')->user();

            $partners = Partner::where('branch_id', '=', $admin->branch_id)
                ->where('status_id', '=', 5)
                ->where('is_present', '=', 1);

            if($request->exists('search') && $request->query('search') != ''){
                $partners = $partners->where('alias', 'like', '%' . $request->query('search') . '%')
                    ->orWhere('number', 'like', '%' . $request->query('search') . '%');
            }

            $partners = $partners
                ->skip($skip)
                ->take($take)
                ->get();

            $count = DB::table('partners')
//                ->where('type', $request->query('type'))
                ->where('is_present', '=', 1)
                ->where('branch_id', $admin->branch_id)
                ->where('status_id', '=', 5)
                ->count();

            $pages = [];
            $tmpPageCount = ceil($count / 10);
            for($i = 1; $i <= $tmpPageCount; $i++){
                $pages[] = $i;
            }

            foreach ($partners as $partner){
                $arr[] = [
                    'id' => $partner->id,
                    'number' => $partner->number,
                    'alias' => $partner->alias,
                    'status' => $partner->status_id === 5 ? 'Available' : 'Occupied'
                ];
            }

            $data = [
                'partners' => $arr,
                'count' => $count,
                'pages' => $pages,
            ];

            return Response::json($data, 200);
        }
        catch (\Exception $ex){
            return Response::json('failed' . $ex, 500);
        }
    }




    // CONTRACT

    public function createContract($id){
        return view('admin.partner.contract.create', compact('id'));
    }

    public function storeContract(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'code' => 'required|max:255',
            'education' => 'required|max:200',
            'address' => 'required|max:200',
            'phone_number' => 'required|max:45',
            'ktp' => 'required',
            'pkwt' => 'required'
        ],[
            'name.required' => 'Nama Terapis wajib diisi!',
            'name.max' => 'Nama Terapis maksimal 255 karakter!',
            'code.required' => 'Nomor Kontrak wajib diisi!',
            'code.max' => 'Nomor Kontrak maksimal 255 karakter!',
            'education.required' => 'Pendidikan wajib diisi!',
            'education.max' => 'Pendidikan maksimal 200 karakter!',
            'address.required' => 'Alamat wajib diisi!',
            'address.max' => 'Alamat maksimal 200 karakter!',
            'phone_number.required' => 'No Telp wajib diisi!',
            'phone_number.max' => 'No Telp maksimal 45 karakter!',
            'ktp.required' => 'Foto KTP wajib diisi!',
            'pkwt.required' => 'Dokumen PKWT wajib diisi!',
        ]);

        if ($validator->fails()) return redirect()
            ->back()
            ->withErrors($validator->errors())
            ->withInput($request->all());

        try {
            $user = Auth::guard('admin')->user();

            $contract = PartnerContract::create([
                'branch_id' => $user->branch_id,
                'partner_id' => $request->input('partner_id'),
                'duration' => $request->input('duration'),
                'start_date' => Carbon::parse($request->input('start_date'))->toDateTimeString(),
                'end_date' => Carbon::parse($request->input('end_date'))->toDateTimeString(),
                'code' => trim($request->input('code')),
                'name' => trim($request->input('name')),
                'marital_status' => trim($request->input('marital_status')),
                'gender' => trim($request->input('gender')),
                'education' => trim($request->input('education')),
                'address' => trim($request->input('address')),
                'phone_number' => trim($request->input('phone_number')),
                'salary' => $request->input('salary') ?? 0,
                'commission' => Utilities::toFloat($request->input('commission'))/10,
            ]);

            $ktp = $request->file('ktp');
            $pkwt = $request->file('pkwt');

            $ktpName = 'ktp_' . $contract->id . '_' . date('d-m-Y_H-i') . '.' . $ktp->getClientOriginalExtension();
            $pkwtName = 'pkwt_' . $contract->id . '_' . date('d-m-Y_H-i') . '.' . $pkwt->getClientOriginalExtension();

            $ktp->storeAs('', $ktpName, 'partner_ktp');
            $pkwt->storeAs('', $pkwtName, 'partner_pkwt');

            $contract->update([
                'ktp_image_path' => $ktpName,
                'pkwt_image_path' => $pkwtName
            ]);

            Session::flash('success', 'Berhasil membuat kontrak terapis!');
            return redirect()->route('admin.partner.show', ['id' => $request->input('partner_id')]);
        } catch (\Exception $ex){
            Log::channel('master_data')->error('Admin/PartnerController - storeContract '. $ex);
            Session::flash('error', 'Terjadi kesalahan pada sistem!');
            return redirect()->back()->withInput($request->all());
        }
    }

    public function editContract($id){
        $contract = PartnerContract::where('partner_id', $id)->first();

        return view('admin.partner.contract.edit', compact('contract'));
    }

    public function updateContract(Request $request){
        $validator = Validator::make($request->all(), [
            'code' => 'required|max:255',
        ],[
            'code.required' => 'Nomor Kontrak wajib diisi!',
            'code.max' => 'Nomor Kontrak maksimal 255 karakter!',
        ]);

        if ($validator->fails()) return redirect()
            ->back()
            ->withErrors($validator->errors())
            ->withInput($request->all());

        try {
            $contract = PartnerContract::find($request->input('id'));

            $contract->update([
                'duration' => $request->input('duration'),
                'start_date' => Carbon::parse($request->input('start_date'))->toDateTimeString(),
                'end_date' => Carbon::parse($request->input('end_date'))->toDateTimeString(),
                'code' => trim($request->input('code')),
                'name' => trim($request->input('name')),
                'marital_status' => trim($request->input('marital_status')),
                'gender' => trim($request->input('gender')),
                'education' => trim($request->input('education')),
                'address' => trim($request->input('address')),
                'phone_number' => trim($request->input('phone_number')),
                'salary' => $request->input('salary') ?? 0,
                'commission' => Utilities::toFloat($request->input('commission'))/10,
            ]);

            if($request->hasFile('pkwt')){
                $pkwt = $request->file('pkwt');
                $pkwtName = 'pkwt_' . $contract->id . '_' . date('d-m-Y_H-i') . '.' . $pkwt->getClientOriginalExtension();
                $pkwt->storeAs('', $pkwtName, 'partner_pkwt');

                PkwtHistory::create([
                    'type' => 'partner',
                    'reference_id' => $contract->partner_id,
                    'filename' => $contract->pkwt_image_path
                ]);

                $contract->update([
                    'pkwt_image_path' => $pkwtName
                ]);
            }

            if($request->hasFile('ktp')){
                $ktp = $request->file('ktp');
                $ktpName = 'ktp_' . $contract->id . '_' . date('d-m-Y_H-i') . '.' . $ktp->getClientOriginalExtension();
                $ktp->storeAs('', $ktpName, 'employee_ktp');

                $contract->update([
                    'ktp_image_path' => $ktpName,
                ]);
            }

            Session::flash('success', 'Berhasil mengubah kontrak terapis!');
            return redirect()->route('admin.partner.show', ['id' => $contract->partner_id]);
        } catch (\Exception $ex){
            Log::channel('master_data')->error('Admin/PartnerController - storeContract '. $ex);
            Session::flash('error', 'Terjadi kesalahan pada sistem!');
            return redirect()->back()->withInput($request->all());
        }
    }

    public function deleteContract(Request $request){
        $contract = PartnerContract::find($request->input('id'));

        try {
            $partner_id = $contract->partner_id;
            $contract->delete();

            Session::flash('success', 'Berhasil menghapus kontrak terapis!');
            return redirect()->route('admin.partner.show', ['id' => $contract->partner_id]);
        } catch (\Exception $ex){
            Log::channel('master_data')->error('Admin/EmployeeController - storeContract '. $ex);
            Session::flash('error', 'Terjadi kesalahan pada sistem!');
            return redirect()->back()->withInput($request->all());
        }

    }

    public function pkwtHistory($id){
        $pkwts = PkwtHistory::where('type', 'partner')
            ->where('reference_id', $id)
            ->get();

        return view('admin.partner.contract.history', compact('id', 'pkwts'));
    }

    public function saveImage($image, $code)
    {
        //check folder exists
        $tmpPath = Storage::disk('partners')->path('');
        if(!Storage::exists($tmpPath)){
            //create the folder
            Storage::disk('partners')->makeDirectory('');
        }

        $mimeType = array_slice(explode('/',$image->getMimeType()), -1)[0];

        $date = Carbon::now('Asia/Jakarta');
        $imageName = $code.'_'. $date->year. $date->month. $date->day. time().'.'.$mimeType;

        $imageLoc = Storage::disk('partners')->path(''); //save to directory
        // $imagePath = array_slice(explode('public', $imageLoc), -1)[0]; //save path to database
        $imagePath = $imageLoc.$imageName; //save path to database

        //reduce size img
        if($mimeType == 'jpeg'){
            $sourceImage = imagecreatefromjpeg($image);
            $width = imagesx($sourceImage);
            $height = imagesy($sourceImage);
            if($width > 2000){
                $aspectRatio = $height / $width;
                $newWidth = 2000;
                $newHeight = $width * $aspectRatio;
            } else if($height > 2000){
                $aspectRatio = $width / $height;
                $newHeight = 2000;
                $newWidth = $height * $aspectRatio;
            } else {
                $newWidth = $width;
                $newHeight = $height;
            }
            $newImage = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagejpeg($newImage, $imagePath, 60);
        }
        else if($mimeType == 'png'){
            $sourceImage = imagecreatefrompng($image);
            $width = imagesx($sourceImage);
            $height = imagesy($sourceImage);
            if($width > 2000){
                $aspectRatio = $height / $width;
                $newWidth = 2000;
                $newHeight = $width * $aspectRatio;
            } else if($height > 2000){
                $aspectRatio = $width / $height;
                $newHeight = 2000;
                $newWidth = $height * $aspectRatio;
            } else {
                $newWidth = $width;
                $newHeight = $height;
            }
            $newImage = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            imagepng($newImage, $imagePath, 5);
        }
        else if($mimeType == 'webp'){
            $sourceImage = imagecreatefromwebp($image);
            $width = imagesx($sourceImage);
            $height = imagesy($sourceImage);
            if($width > 2000){
                $aspectRatio = $height / $width;
                $newWidth = 2000;
                $newHeight = $width * $aspectRatio;
            } else if($height > 2000){
                $aspectRatio = $width / $height;
                $newHeight = 2000;
                $newWidth = $height * $aspectRatio;
            } else {
                $newWidth = $width;
                $newHeight = $height;
            }
            $newImage = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagewebp($newImage, $imagePath, 60);
        }

        return $imageName;
    }
}
