<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libs\ApiUtilities;
use App\Models\BookingDetail;
use App\Models\PreweddingHeader;
use App\Models\UserReview;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    private array $result;

    public function __construct()
    {
        $this->result = [
            'error' => [],
            'message' => '',
            'warning' => '',
            'request_id' => ApiUtilities::generateRandomShortString(20),
            'response' => [],
        ];
    }

    public function submit(Request $request)
    {
        try {
            $reviews = $this->normalizeReviewPayloads($request);
            $data = [
                'booking_id' => $this->resolveBookingId($request, $reviews),
                'reviews' => $reviews->all(),
            ];

            $validator = Validator::make($data, [
                'booking_id' => 'required|integer',
                'reviews' => 'required|array|min:1',
                'reviews.*.rating' => 'required|integer|min:1|max:5',
                'reviews.*.description' => 'required|string',
                'reviews.*.package_id' => 'nullable|integer',
                'reviews.*.item_id' => 'nullable|integer',
            ], [
                'reviews.required' => 'Detail wajib diisi',
            ]);

            $invalidTargets = $reviews
                ->map(function (array $review, int $index): ?int {
                    $targetCount = collect(['package_id', 'item_id', 'pranikah_id'])
                        ->filter(fn ($key) => filled($review[$key] ?? null))
                        ->count();

                    return $targetCount === 1 ? null : $index;
                })
                ->filter(fn ($index) => $index !== null)
                ->values();

            if ($validator->fails() || $invalidTargets->isNotEmpty()) {
                $this->result['error'] = $validator->errors()->all();
                if ($invalidTargets->isNotEmpty()) {
                    $this->result['error'][] = 'Pilih tepat satu target review';
                }
                $this->result['message'] = 'Terjadi kesalahan validasi!';

                return Response::json($this->result, 400);
            }

            $user = $request->user();
            $savedReviewIds = [];

            foreach ($reviews as $reviewPayload) {
                $pendingReview = $this->resolveReviewSubmission($user, $reviewPayload);

                if ($pendingReview === null) {
                    $this->result['message'] = 'Terjadi Kendala untuk Review!';

                    return Response::json($this->result, 404);
                }

                $pendingReview->star = (int) $reviewPayload['rating'];
                $pendingReview->text = $reviewPayload['description'];
                $pendingReview->reviewed_at = Carbon::now('Asia/Jakarta');
                $pendingReview->status_id = 9;
                $pendingReview->save();

                $savedReviewIds[] = $pendingReview->id;
            }

            $this->result['message'] = 'Review berhasil disimpan!';
            $this->result['response'] = count($savedReviewIds) === 1
                ? ['id' => $savedReviewIds[0]]
                : ['ids' => $savedReviewIds];

            return Response::json($this->result, 200);
        } catch (\Exception $ex) {
            Log::channel('api_exception')->error('Api/ReviewController - submit ERROR EX '.$ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Internal server error';

            return Response::json($this->result, 500);
        }
    }

    private function normalizeReviewPayloads(Request $request)
    {
        $reviews = $request->input('reviews');
        $topLevelBookingId = $request->input('booking_id');

        if (is_string($reviews)) {
            $decodedReviews = json_decode($reviews, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $reviews = $decodedReviews;
            }
        }

        if (is_array($reviews) && ! array_is_list($reviews)) {
            $reviews = [$reviews];
        }

        if (! is_array($reviews)) {
            $reviews = [[
                'package_id' => $request->input('package_id'),
                'item_id' => $request->input('item_id'),
                'pranikah_id' => $request->input('pranikah_id'),
                'booking_id' => $request->input('booking_id'),
                'rating' => $request->input('rating'),
                'description' => $request->input('description'),
            ]];
        }

        return collect($reviews)
            ->map(function ($review) use ($topLevelBookingId) {
                $review = is_array($review) ? $review : [];

                if (($review['booking_id'] ?? null) === null && $topLevelBookingId !== null) {
                    $review['booking_id'] = $topLevelBookingId;
                }

                foreach (['package_id', 'item_id', 'pranikah_id', 'booking_id', 'rating'] as $key) {
                    if (($review[$key] ?? null) === '') {
                        $review[$key] = null;
                    }
                }

                return $review;
            })
            ->values();
    }

    private function resolveBookingId(Request $request, $reviews): mixed
    {
        $topLevelBookingId = $request->input('booking_id');

        if ($topLevelBookingId !== null && $topLevelBookingId !== '') {
            return $topLevelBookingId;
        }

        return $reviews
            ->pluck('booking_id')
            ->first(fn ($bookingId) => $bookingId !== null && $bookingId !== '');
    }

    private function resolveReviewSubmission($user, array $reviewPayload): ?UserReview
    {
        $targetKey = collect(['package_id', 'item_id', 'pranikah_id'])
            ->first(fn ($key) => filled($reviewPayload[$key] ?? null));

        $targetId = (int) $reviewPayload[$targetKey];
        $bookingId = (int) $reviewPayload['booking_id'];

        $pendingReview = UserReview::query()
            ->where('user_id', $user->id)
            ->where(function ($query) use ($targetKey, $targetId, $bookingId): void {
                $query->where($targetKey, $targetId)
                    ->where(function ($nested) use ($targetKey, $bookingId): void {
                        if ($targetKey === 'pranikah_id') {
                            $nested->where('pranikah_header_id', $bookingId);
                        } else {
                            $nested->where('booking_id', $bookingId);
                        }
                    });
            })
            ->where($targetKey, $targetId)
            ->where('status_id', 3)
            ->orderByDesc('id')
            ->first();

        if ($pendingReview !== null) {
            return $pendingReview;
        }

        return match ($targetKey) {
            'package_id' => $this->buildBookingReview($user, $bookingId, 'package_id', $targetId),
            'item_id' => $this->buildBookingReview($user, $bookingId, 'item_id', $targetId),
            'pranikah_id' => $this->buildPranikahReview($user, $bookingId, $targetId),
        };
    }

    public function get(Request $request)
    {
        try {
            $skip = (int) ($request->query('skip', 0));

            $reviews = UserReview::query()
                ->where('status_id', 9)
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->skip($skip)
                ->take(10)
                ->get([
                    'user_name',
                    'package_name',
                    'item_name',
                    'star',
                    'text',
                ])
                ->map(function (UserReview $review) {
                    return [
                        'customer_name' => $review->user_name,
                        'treatment_name' => $review->package_name ?: $review->item_name,
                        'rating' => (float) $review->star,
                        'description' => $review->text,
                    ];
                })
                ->values();

            $this->result['response'] = $reviews;
            $this->result['message'] = 'Sukses ambil data review!';

            return Response::json($this->result, 200);
        } catch (\Exception $ex) {
            Log::channel('api_exception')->error('Api/ReviewController - get ERROR EX '.$ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Internal server error';

            return Response::json($this->result, 500);
        }
    }

    public function getByTreatment(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:package,item',
                'id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                $this->result['error'] = $validator->errors()->all();
                $this->result['message'] = 'Terjadi kesalahan validasi!';

                return Response::json($this->result, 400);
            }

            $skip = (int) ($request->query('skip', 0));
            $column = $request->query('type') === 'package' ? 'package_id' : 'item_id';

            $reviews = UserReview::query()
                ->where('status_id', 9)
                ->where($column, (int) $request->query('id'))
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->skip($skip)
                ->take(10)
                ->get([
                    'user_name',
                    'package_name',
                    'item_name',
                    'star',
                    'text',
                ])
                ->map(function (UserReview $review) {
                    return [
                        'customer_name' => $review->user_name,
                        'treatment_name' => $review->package_name ?: $review->item_name,
                        'rating' => (float) $review->star,
                        'description' => $review->text,
                    ];
                })
                ->values();

            $this->result['response'] = $reviews;
            $this->result['message'] = 'Sukses ambil data review!';

            return Response::json($this->result, 200);
        } catch (\Exception $ex) {
            Log::channel('api_exception')->error('Api/ReviewController - getByTreatment ERROR EX '.$ex);

            $this->result['error'] = $ex->getMessage();
            $this->result['message'] = 'Internal server error';

            return Response::json($this->result, 500);
        }
    }

    private function buildBookingReview($user, int $bookingId, string $targetKey, int $targetId): ?UserReview
    {
        $reviewedDetailIds = UserReview::query()
            ->where('user_id', $user->id)
            ->whereNotNull('booking_detail_id')
            ->pluck('booking_detail_id');

        $detail = BookingDetail::query()
            ->where('header_id', $bookingId)
            ->where($targetKey, $targetId)
            ->whereNotIn('id', $reviewedDetailIds)
            ->whereHas('booking_header', function ($query) use ($user): void {
                $query->where('user_id', $user->id)
                    ->where('status_id', 9);
            })
            ->orderByDesc('id')
            ->first();

        if ($detail === null) {
            return null;
        }

        $review = new UserReview;
        $review->branch_id = $detail->booking_header->branch_id;
        $review->booking_id = $detail->booking_header->id;
        $review->booking_detail_id = $detail->id;
        $review->user_id = $user->id;
        $review->user_name = $user->name;
        $review->user_phone = $user->phone;
        $review->package_id = $detail->package_id;
        $review->package_name = $detail->package_name;
        $review->item_id = $detail->item_id;
        $review->item_name = $detail->item_name;
        $review->is_image_uploaded = false;

        return $review;
    }

    private function buildPranikahReview($user, int $bookingId, int $pranikahId): ?UserReview
    {
        $reviewedHeaderIds = UserReview::query()
            ->where('user_id', $user->id)
            ->whereNotNull('pranikah_header_id')
            ->pluck('pranikah_header_id');

        $header = PreweddingHeader::query()
            ->with('package')
            ->where('id', $bookingId)
            ->where('member_id', $user->id)
            ->where('package_id', $pranikahId)
            ->whereNotIn('id', $reviewedHeaderIds)
            ->whereHas('details', function ($query): void {
                $query->where('status_id', 9);
            })
            ->orderByDesc('id')
            ->first();

        if ($header === null) {
            return null;
        }

        $review = new UserReview;
        $review->branch_id = $header->branch_id ?? null;
        $review->user_id = $user->id;
        $review->user_name = $user->name;
        $review->user_phone = $user->phone;
        $review->pranikah_id = $header->package_id;
        $review->pranikah_header_id = $header->id;
        $review->pranikah_name = optional($header->package)->name;
        $review->is_image_uploaded = false;

        return $review;
    }
}
