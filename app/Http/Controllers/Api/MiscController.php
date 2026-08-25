<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libs\ApiUtilities;
use App\Libs\TaxUtilities;
use App\Models\BookingHeader;
use App\Models\Configuration;
use App\Models\Item;
use App\Models\Package;
use App\Models\Partner;
use App\Models\TransactionDetail;
use App\Models\TransactionDetailPartner;
use App\Models\TransactionHeader;
use App\Models\TransactionInvoice;
use App\Models\User;
use App\Models\UserPointHistory;
use App\Models\UserVoucher;
use App\Models\UserVoucherCode;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MiscController extends Controller
{
    private const GOOGLE_MAPS_RATING_URL = 'https://maps.app.goo.gl/NVDVouCyd4H6MwTh7';

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

    public function get(Request $request){
        try{
            $totalMale = TransactionHeader::where('status_id', 9)
                ->sum('total_cust_male');

            $totalFemale = TransactionHeader::where('status_id', 9)
                ->sum('total_cust_female');

            $config = Configuration::where('key', 'google_ratings')->first();
            $rating = $this->getGoogleMapsRating($config->value ?? 0);

            $terapis = Partner::where('employement_status', 'Aktif')->count();

            $result = [
                'terapis' => $terapis,
                'ratings' => $rating,
                'orders' => ($totalMale + $totalFemale) - (($totalMale + $totalFemale)%1000),
                'ppn' => TaxUtilities::getPpnPercentage(),
            ];

            $this->result['response'] = $result;
            $this->result['message'] = 'Ambil data sukses!';
            return Response::json($this->result, 200);
        }
        catch (\Exception $ex){
            Log::channel('api_exception')->error('Api/MiscController - get ERROR EX '. $ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Internal server error';
            return Response::json($this->result, 500);
        }
    }

    private function getGoogleMapsRating($fallback)
    {
        $cachedRating = Cache::get('google_maps_rating');
        if ($cachedRating !== null) {
            return $cachedRating;
        }

        try {
            $response = Http::timeout(8)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0 Safari/537.36',
                    'Accept-Language' => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
                ])
                ->get(self::GOOGLE_MAPS_RATING_URL);

            if (! $response->successful()) {
                return $fallback;
            }

            $rating = $this->parseGoogleMapsRating($response->body());
            if ($rating === null) {
                return $fallback;
            }

            Configuration::updateOrCreate(
                ['key' => 'google_ratings'],
                ['value' => (string) $rating]
            );

            Cache::put('google_maps_rating', $rating, now()->addHours(6));

            return $rating;
        } catch (\Exception $ex) {
            Log::channel('api_exception')->warning('Api/MiscController - Google Maps rating fetch ERROR EX '.$ex);

            return $fallback;
        }
    }

    private function parseGoogleMapsRating(string $html): ?float
    {
        $patterns = [
            '/"ratingValue"\s*:\s*"?(?<rating>[0-5](?:[\.,]\d+)?)"?/i',
            '/aria-label="(?<rating>[0-5](?:[\.,]\d+)?)\s*(?:stars|bintang)/i',
            '/(?<rating>[0-5](?:[\.,]\d+)?)\s*(?:stars|bintang)/i',
            '/\\\\?"(?<rating>[0-5](?:[\.,]\d+)?)\\\\?",\s*\\\\?"(?:stars|bintang)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $matches)) {
                $rating = (float) str_replace(',', '.', $matches['rating']);
                if ($rating >= 0 && $rating <= 5) {
                    return $rating;
                }
            }
        }

        return null;
    }
}
