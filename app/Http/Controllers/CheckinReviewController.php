<?php

namespace App\Http\Controllers;

use App\Models\CheckinReview;
use App\Models\Checkin;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Checkin Reviews",
 *     description="API Endpoints for managing checkin reviews"
 * )
 */
class CheckinReviewController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/checkin-reviews",
     *     summary="Get the review for a checkin",
     *     tags={"Checkin Reviews"},
     *     @OA\Parameter(
     *         name="checkin_id",
     *         in="query",
     *         required=true,
     *         description="ID of the checkin to get the review for",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Review for the checkin",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="review", type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="checkin_id", type="string", format="uuid"),
     *                 @OA\Property(property="review", type="string"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Review not found")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'checkin_id' => 'required|uuid|exists:checkins,id',
        ]);
        $review = CheckinReview::where('checkin_id', $request->checkin_id)->first();
        return response()->json(['review' => $review]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * @OA\Post(
     *     path="/api/checkin-reviews",
     *     summary="Create a review for a checkin",
     *     tags={"Checkin Reviews"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"checkin_id", "review"},
     *             @OA\Property(property="checkin_id", type="string", format="uuid"),
     *             @OA\Property(property="review", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Review created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="review", type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="checkin_id", type="string", format="uuid"),
     *                 @OA\Property(property="review", type="string"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized - User can only review their own checkins"),
     *     @OA\Response(response=422, description="Validation error or review already exists")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'checkin_id' => 'required|uuid|exists:checkins,id',
            'review' => 'required|string',
        ]);
        $checkin = Checkin::findOrFail($validated['checkin_id']);
        if ($checkin->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized - You can only review your own checkins'], 403);
        }
        // Only one review per checkin
        $existing = CheckinReview::where('checkin_id', $validated['checkin_id'])->first();
        if ($existing) {
            return response()->json(['message' => 'Review already exists for this checkin'], 422);
        }
        $review = CheckinReview::create($validated);
        return response()->json(['message' => 'Review created successfully', 'review' => $review], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/checkin-reviews/{checkinReview}",
     *     summary="Get a specific checkin review",
     *     tags={"Checkin Reviews"},
     *     @OA\Parameter(
     *         name="checkinReview",
     *         in="path",
     *         required=true,
     *         description="UUID of the review to retrieve",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Review details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="review", type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="checkin_id", type="string", format="uuid"),
     *                 @OA\Property(property="review", type="string"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Review not found")
     * )
     */
    public function show(CheckinReview $checkinReview): JsonResponse
    {
        return response()->json(['review' => $checkinReview]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CheckinReview $checkinReview)
    {
        //
    }

    /**
     * @OA\Put(
     *     path="/api/checkin-reviews/{checkinReview}",
     *     summary="Update a checkin review",
     *     tags={"Checkin Reviews"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="checkinReview",
     *         in="path",
     *         required=true,
     *         description="UUID of the review to update",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"review"},
     *             @OA\Property(property="review", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Review updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="review", type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="checkin_id", type="string", format="uuid"),
     *                 @OA\Property(property="review", type="string"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized - User can only update their own reviews"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(Request $request, CheckinReview $checkinReview): JsonResponse
    {
        // Only the owner of the checkin can update
        if ($checkinReview->checkin->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized - You can only update your own reviews'], 403);
        }
        $validated = $request->validate([
            'review' => 'required|string',
        ]);
        $checkinReview->update($validated);
        return response()->json(['message' => 'Review updated successfully', 'review' => $checkinReview]);
    }

    /**
     * @OA\Delete(
     *     path="/api/checkin-reviews/{checkinReview}",
     *     summary="Delete a checkin review",
     *     tags={"Checkin Reviews"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="checkinReview",
     *         in="path",
     *         required=true,
     *         description="UUID of the review to delete",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Review deleted successfully"
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized - User can only delete their own reviews"),
     *     @OA\Response(response=404, description="Review not found")
     * )
     */
    public function destroy(CheckinReview $checkinReview): JsonResponse
    {
        // Only the owner of the checkin can delete
        if ($checkinReview->checkin->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized - You can only delete your own reviews'], 403);
        }
        $checkinReview->delete();
        return response()->json(null, 204);
    }
}
