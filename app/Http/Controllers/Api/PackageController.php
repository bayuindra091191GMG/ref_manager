<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libs\ApiUtilities;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Package;
use App\Models\PreweddingPackage;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\UserReview;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;

class PackageController extends Controller
{
    private $result;

    public function __construct(){
        $this->result = [
            'error'         => [],
            'message'       => '',
            'warning'       => '',
            'request_id'    => ApiUtilities::generateRandomShortString(20),
            'response'      => []
        ];
    }

    public function get()
    {
        try {
            $userData = Auth::guard('user-api')->user();
            $rooms = Room::where('status_id', '!=', 2)
                ->get();

            $packages = Package::get();

            $roomTypes = RoomType::orderBy('sequence')->get();

            $itemCategories = ItemCategory::whereIn('id', [2,27,28])
                ->orderBy('sequence')
                ->get();

            $items = Item::get();

            $favoritePackages = Package::where('is_favorite', 1)
                ->where('status_id', '!=', 2)
                ->get();

            $packageRatings = UserReview::query()
                ->selectRaw('package_id, AVG(star) as average_rating')
                ->whereNotNull('package_id')
                ->where('status_id', 9)
                ->groupBy('package_id')
                ->pluck('average_rating', 'package_id');

            $itemRatings = UserReview::query()
                ->selectRaw('item_id, AVG(star) as average_rating')
                ->whereNotNull('item_id')
                ->where('status_id', 9)
                ->groupBy('item_id')
                ->pluck('average_rating', 'item_id');

            $packageArr = [];
            $studioArr = [];
            $salonArr = [];
            foreach ($roomTypes as $type) {
                if($type->id != 27){
                    $filteredPackages = $packages->filter(fn ($package) => $package->room_type_id == $type->id);

                    $packagesData = $filteredPackages->map(function ($package) use ($itemRatings, $packageRatings) {
                        $itemData = null;
                        if($package->item_id != null && $package->item_id != 0){
                            $item = Item::find($package->item_id);
                            $itemData = [
                                'id' => $item->id,
                                'type' => 'item',
                                'code' => $item->code,
                                'name' => $item->name,
                                'price' => 0,
                                'isFreeAddOn'     => true,
                                'picked' => false,
                                'qty' => 1,
                                'status' => $item->status_id,
                                'img' => $item->image_path != null ? asset('storage/images/items/'. $item->image_path) : '',
                                'rating' => round((float) ($itemRatings->get($item->id) ?? 0), 1),
                            ];
                        }

                        $qty = 0;
                        $picked = false;
                        $schedules = [];
                        if($package->room_type_id == 6){
                            $schedules = [
                                'time' => ["10:00", "17:00"],
                                'capacity' => 10,
                                'participants' => 0,
                            ];
                        }

                        return [
                            'id'           => $package->id,
                            'type'         => 'package',
                            'code'         => $package->code,
                            'name'         => $package->name,
                            'description'  => $package->description,
                            'room_type_id' => $package->room_type_id,
                            'duration'     => $package->duration,
                            'room_id' => null,
                            'price'        => $package->price,
                            'voucher'              => '',
                            'voucher_name'  => '',
                            'discount'             => 0,
                            'checking'          => false,
                            'isRequest'     => false,
                            'isFreeAddOn'     => false,
                            'partner_id'     => null,
                            'picked' => $picked,
                            'qty' => $qty,
                            'status' => $package->status_id,
                            'img' => $package->image_path != null ? asset('storage/images/items/'. $package->image_path) : '',
                            'rating' => round((float) ($packageRatings->get($package->id) ?? 0), 1),
                            'badge_status' => $package->badge_status ?? "",
                            'item' => $itemData,
                            'schedules' => $schedules
                        ];
                    } )->values()->all();

                    usort($packagesData, function ($a, $b) {
                        return $a['name'] >= $b['name'];
                    });

                    usort($packagesData, function ($a, $b) {
                        return $a['badge_status'] <= $b['badge_status'];
                    });

                    if($type->id == 6){
                        $studioArr = [
                            'id'      => $type->id,
                            'name'    => $type->name,
                            'display' => $type->display,
                            'items' => $packagesData,
                        ];
                    } else if($type->id == 4){
                        $salonArr = [
                            'id'      => $type->id,
                            'name'    => $type->name,
                            'display' => $type->display,
                            'packages' => $packagesData,
                        ];
                    } else {
                        $packageArr[] = [
                            'id'      => $type->id,
                            'name'    => $type->name,
                            'display' => $type->display,
                            'packages' => $packagesData,
                        ];
                    }
                }
            }

            $favoritePackageArr = [];
            $favoritePackagesData = $favoritePackages->map(function ($package) use ($itemRatings, $packageRatings) {
                $itemData = null;
                if($package->item_id != null && $package->item_id != 0){
                    $item = Item::find($package->item_id);
                    $itemData = [
                        'id' => $item->id,
                        'type' => 'item',
                        'code' => $item->code,
                        'name' => $item->name,
                        'price' => 0,
                        'isFreeAddOn'     => true,
                        'picked' => false,
                        'qty' => 1,
                        'status' => $item->status_id,
                        'img' => $item->image_path != null ? asset('storage/images/items/'. $item->image_path) : '',
                        'rating' => round((float) ($itemRatings->get($item->id) ?? 0), 1),
                    ];
                }

                $qty = 0;
                $picked = false;
                $schedules = [];
                if($package->room_type_id == 6){
                    $schedules = [
                        'time' => ["10:00", "17:00"],
                        'capacity' => 10,
                        'participants' => 0,
                    ];
                }

                return [
                    'id'           => $package->id,
                    'type'         => 'package',
                    'code'         => $package->code,
                    'name'         => $package->name,
                    'description'  => $package->description,
                    'room_type_id' => $package->room_type_id,
                    'duration'     => $package->duration,
                    'room_id' => null,
                    'price'        => $package->price,
                    'voucher'              => '',
                    'voucher_name'  => '',
                    'discount'             => 0,
                    'checking'          => false,
                    'isRequest'     => false,
                    'isFreeAddOn'     => false,
                    'partner_id'     => null,
                    'picked' => $picked,
                    'qty' => $qty,
                    'status' => $package->status_id,
                    'img' => $package->image_path != null ? asset('storage/images/items/'. $package->image_path) : '',
                    'rating' => round((float) ($packageRatings->get($package->id) ?? 0), 1),
                    'badge_status' => $package->badge_status ?? "",
                    'item' => $itemData,
                    'schedules' => $schedules
                ];
            } )->values()->all();
            $favoritePackageArr = $favoritePackagesData;


            $itemArr = [];
            foreach ($itemCategories as $category) {
                $filteredItems = $items->filter(fn ($item) => $item->category_id == $category->id);


                if (count($filteredItems) > 0) {
                    $itemsData = $filteredItems->map(function ($item) use ($category, $itemRatings) {
                        $qty = 0;
                        $picked = false;

                        return [
                            'id' => $item->id,
                            'type' => 'item',
                            'code' => $item->code,
                            'name' => $item->name,
                            'price' => $item->price,
                            'isFreeAddOn'     => false,
                            'need_partner'     => $category->id == 27,
                            'partner_id'     => null,
                            'room_id' => null,
                            'duration' => $item->duration,
                            'voucher'              => '',
                            'voucher_name'  => '',
                            'badge_status' => $item->badge_status ?? "",
                            'discount'             => 0,
                            'picked' => $picked,
                            'qty' => $qty,
                            'status' => $item->status_id,
                            'img' => $item->image_path != null ? asset('storage/images/items/'. $item->image_path) : '',
                            'rating' => round((float) ($itemRatings->get($item->id) ?? 0), 1),
                        ];
                    })->values()->all();

                    usort($itemsData, function ($a, $b) {
                        return $a['name'] >= $b['name'];
                    });

                    usort($itemsData, function ($a, $b) {
                        return $a['badge_status'] <= $b['badge_status'];
                    });

                    if($category->id == 27){
                        $packageArr[] = [
                            'id' => $category->id,
                            'name' => $category->name,
                            'packages' => $itemsData,
                        ];
                    } else {
                        $itemArr[] = [
                            'id' => $category->id,
                            'name' => $category->name,
                            'items' => $itemsData,
                        ];
                    }
                }
            }

            $result = [
                'favorite_package' => $favoritePackageArr,
                'packages' => $packageArr,
                'salon' => $salonArr,
                'studio' => $studioArr,
                'items' => $itemArr
            ];

            $this->result['response'] = $result;
            $this->result['message'] = 'Get packages success';
            return Response::json($this->result);
        } catch (\Exception $ex) {
            Log::channel('api_exception')->error('Api/BranchController - get ERROR EX '. $ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Internal server error';
            return Response::json($this->result, 500);
        }
    }

    public function getTreatmentPoint()
    {
        try {
            $itemCategories = ItemCategory::whereIn('id', [2,27,28])
                ->orderBy('sequence')
                ->get();

            $items = Item::where('is_buy_with_point', 1)->get();

            $favoritePackages = Package::where('is_buy_with_point', 1)
                ->where('status_id', '!=', 2)
                ->get();

            $packageRatings = UserReview::query()
                ->selectRaw('package_id, AVG(star) as average_rating')
                ->whereNotNull('package_id')
                ->where('status_id', 9)
                ->groupBy('package_id')
                ->pluck('average_rating', 'package_id');

            $itemRatings = UserReview::query()
                ->selectRaw('item_id, AVG(star) as average_rating')
                ->whereNotNull('item_id')
                ->where('status_id', 9)
                ->groupBy('item_id')
                ->pluck('average_rating', 'item_id');

            $favoritePackageArr = [];
            $favoritePackagesData = $favoritePackages->map(function ($package) use ($itemRatings, $packageRatings) {
                $itemData = null;
                if($package->item_id != null && $package->item_id != 0){
                    $item = Item::find($package->item_id);
                    $itemData = [
                        'id' => $item->id,
                        'type' => 'item',
                        'code' => $item->code,
                        'name' => $item->name,
                        'price' => 0,
                        'isFreeAddOn'     => true,
                        'picked' => false,
                        'qty' => 1,
                        'status' => $item->status_id,
                        'img' => $item->image_path != null ? asset('storage/images/items/'. $item->image_path) : '',
                        'rating' => round((float) ($itemRatings->get($item->id) ?? 0), 1),
                    ];
                }

                $qty = 0;
                $picked = false;
                $schedules = [];
                if($package->room_type_id == 6){
                    $schedules = [
                        'time' => ["10:00", "17:00"],
                        'capacity' => 10,
                        'participants' => 0,
                    ];
                }

                return [
                    'id'           => $package->id,
                    'type'         => 'package',
                    'code'         => $package->code,
                    'name'         => $package->name,
                    'description'  => $package->description,
                    'room_type_id' => $package->room_type_id,
                    'duration'     => $package->duration,
                    'room_id' => null,
                    'price'        => $package->price,
                    'price_point'        => $package->second_price ?? 0,
                    'voucher'              => '',
                    'voucher_name'  => '',
                    'discount'             => 0,
                    'checking'          => false,
                    'isRequest'     => false,
                    'isFreeAddOn'     => false,
                    'partner_id'     => null,
                    'picked' => $picked,
                    'qty' => $qty,
                    'status' => $package->status_id,
                    'img' => $package->image_path != null ? asset('storage/images/items/'. $package->image_path) : '',
                    'rating' => round((float) ($packageRatings->get($package->id) ?? 0), 1),
                    'badge_status' => $package->badge_status ?? "",
                    'item' => $itemData,
                    'schedules' => $schedules
                ];
            } )->values()->all();
            $favoritePackageArr = $favoritePackagesData;


            foreach ($itemCategories as $category) {
                $filteredItems = $items->filter(fn ($item) => $item->category_id == $category->id);

                if (count($filteredItems) > 0) {
                    $itemsData = $filteredItems->map(function ($item) use ($category, $itemRatings) {
                        $qty = 0;
                        $picked = false;

                        return [
                            'id' => $item->id,
                            'type' => 'item',
                            'code' => $item->code,
                            'name' => $item->name,
                            'price' => $item->price,
                            'price_point'        => $item->second_price ?? 0,
                            'isFreeAddOn'     => false,
                            'need_partner'     => $category->id == 27,
                            'partner_id'     => null,
                            'room_id' => null,
                            'duration' => $item->duration,
                            'voucher'              => '',
                            'voucher_name'  => '',
                            'badge_status' => $item->badge_status ?? "",
                            'discount'             => 0,
                            'picked' => $picked,
                            'qty' => $qty,
                            'status' => $item->status_id,
                            'img' => $item->image_path != null ? asset('storage/images/items/'. $item->image_path) : '',
                            'rating' => round((float) ($itemRatings->get($item->id) ?? 0), 1),
                        ];
                    })->values()->all();

                    usort($itemsData, function ($a, $b) {
                        return $a['name'] >= $b['name'];
                    });

                    $favoritePackageArr = array_merge($favoritePackageArr, $itemsData);
                }
            }
            usort($favoritePackageArr, function ($a, $b) {
                return $a['price_point'] >= $b['price_point'];
            });

            $result = [
                'packages' => $favoritePackageArr,
            ];

            $this->result['response'] = $result;
            $this->result['message'] = 'Get packages success';
            return Response::json($this->result);
        } catch (\Exception $ex) {
            Log::channel('api_exception')->error('Api/BranchController - get ERROR EX '. $ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Internal server error';
            return Response::json($this->result, 500);
        }
    }

    public function getPranikah()
    {
        try {
            $result = [];
            $packages = PreweddingPackage::get();
            foreach($packages as $package){
                $ids = explode('#', ltrim(rtrim($package->packages, '#'), '#'));
                $packagePrices = explode('#', ltrim(rtrim($package->package_prices, '#'), '#'));

                $details = [];
                $ct = 0;
                foreach ($ids as $id){
                    $idSplit = explode('-', $id);
                    if($idSplit[0] == 'P'){
                        $p = Package::find($idSplit[1]);
                        $name = $p->name;

                        $details[] = [
                            'item' => null,
                            'package' => $p->id,
                            'price' => $packagePrices[$ct],
                            'name' => $name
                        ];
                    }
                    else {
                        $item = Item::find($idSplit[1]);
                        $name = $item->name;

                        $details[] = [
                            'item' => $item->id,
                            'package' => null,
                            'price' => $packagePrices[$ct],
                            'name' => $name
                        ];
                    }
                    $ct++;
                }

                $result[] = [
                    'pranikah_id' => $package->id,
                    'treatments' => $details,
                ];
            }


            $this->result['response'] = $result;
            $this->result['message'] = 'Get treatment success';
            return Response::json($this->result);
        } catch (\Exception $ex) {
            Log::channel('api_exception')->error('Api/PackageController - getPranikah ERROR EX '. $ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Internal server error';
            return Response::json($this->result, 500);
        }
    }
}
