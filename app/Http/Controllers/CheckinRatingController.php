<?php

namespace App\Http\Controllers;

use App\Models\CheckinRating;
use App\Models\Checkin;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Checkin Ratings",
 *     description="API Endpoints for managing checkin ratings"
 * )
 */
class CheckinRatingController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/checkin-ratings",
     *     summary="Get all ratings for a checkin",
     *     tags={"Checkin Ratings"},
     *     @OA\Parameter(
     *         name="checkin_id",
     *         in="query",
     *         required=true,
     *         description="ID of the checkin to get ratings for",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of ratings for the checkin",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="rating", type="number", format="float", example=4.5)
     *         )
     *     ),
     *     @OA\Response(response=404, description="Rating not found")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'checkin_id' => 'required|uuid|exists:checkins,id'
        ]);

        $rating = CheckinRating::where('checkin_id', $request->checkin_id)->first();

        if (!$rating) {
            return response()->json(['rating' => null]);
        }

        return response()->json(['rating' => $rating->rating]);
    }

    /**
     * @OA\Post(
     *     path="/api/checkin-ratings",
     *     summary="Create or update a rating for a checkin",
     *     tags={"Checkin Ratings"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"checkin_id", "rating"},
     *             @OA\Property(property="checkin_id", type="string", format="uuid"),
     *             @OA\Property(property="rating", type="number", format="float", minimum=0.5, maximum=5.0)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Rating created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(
     *                 property="rating",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="checkin_id", type="string", format="uuid"),
     *                 @OA\Property(property="rating", type="number", format="float")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized - User can only rate their own checkins"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'checkin_id' => 'required|uuid|exists:checkins,id',
            'rating' => 'required|numeric|min:0.5|max:5.0'
        ]);

        // Verify the check-in belongs to the authenticated user
        $checkin = Checkin::findOrFail($validated['checkin_id']);
        if ($checkin->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized - You can only rate your own checkins'], 403);
        }

        // Create or update the rating
        $rating = CheckinRating::updateOrCreate(
            ['checkin_id' => $validated['checkin_id']],
            ['rating' => $validated['rating']]
        );

        return response()->json([
            'message' => 'Rating saved successfully',
            'rating' => $rating
        ], 201);
    }

    /**
     * @OA\Delete(
     *     path="/api/checkin-ratings/{checkinRating}",
     *     summary="Delete a rating",
     *     tags={"Checkin Ratings"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="checkinRating",
     *         in="path",
     *         required=true,
     *         description="UUID of the rating to delete",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Rating deleted successfully"
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized - User can only delete their own ratings"),
     *     @OA\Response(response=404, description="Rating not found")
     * )
     */
    public function destroy(CheckinRating $checkinRating): JsonResponse
    {
        // Verify the check-in belongs to the authenticated user
        if ($checkinRating->checkin->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized - You can only delete your own ratings'], 403);
        }

        $checkinRating->delete();
        return response()->json(null, 204);
    }

    /**
     * Display the specified resource.
     */
    public function show(CheckinRating $checkinRating)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CheckinRating $checkinRating)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CheckinRating $checkinRating)
    {
        //
    }
}
