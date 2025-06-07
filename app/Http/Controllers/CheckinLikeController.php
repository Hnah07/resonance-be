<?php

namespace App\Http\Controllers;

use App\Models\CheckinLike;
use App\Models\Checkin;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Checkin Likes",
 *     description="API Endpoints for managing checkin likes"
 * )
 */
class CheckinLikeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/checkin-likes",
     *     summary="Get all likes for a checkin",
     *     tags={"Checkin Likes"},
     *     @OA\Parameter(
     *         name="checkin_id",
     *         in="query",
     *         description="ID of the checkin to get likes for",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of likes for the checkin",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="checkin_id", type="string", format="uuid"),
     *                 @OA\Property(property="user_id", type="string", format="uuid"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time"),
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="username", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Checkin not found")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'checkin_id' => 'required|uuid|exists:checkins,id'
        ]);

        $likes = CheckinLike::with('user:id,name,username')
            ->where('checkin_id', $request->checkin_id)
            ->get();

        return response()->json($likes);
    }

    /**
     * @OA\Post(
     *     path="/api/checkin-likes",
     *     summary="Like a checkin",
     *     tags={"Checkin Likes"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"checkin_id"},
     *             @OA\Property(property="checkin_id", type="string", format="uuid")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Like created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Checkin liked successfully"),
     *             @OA\Property(
     *                 property="like",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="checkin_id", type="string", format="uuid"),
     *                 @OA\Property(property="user_id", type="string", format="uuid"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Invalid input"),
     *     @OA\Response(response=404, description="Checkin not found"),
     *     @OA\Response(response=409, description="Already liked"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'checkin_id' => 'required|uuid|exists:checkins,id'
        ]);

        // Check if user already liked this checkin
        $existingLike = CheckinLike::where('checkin_id', $request->checkin_id)
            ->where('user_id', Auth::id())
            ->first();

        if ($existingLike) {
            return response()->json(['message' => 'You have already liked this checkin'], 409);
        }

        $like = CheckinLike::create([
            'checkin_id' => $request->checkin_id,
            'user_id' => Auth::id()
        ]);

        return response()->json([
            'message' => 'Checkin liked successfully',
            'like' => $like
        ], 201);
    }

    /**
     * @OA\Delete(
     *     path="/api/checkin-likes/{checkinLike}",
     *     summary="Unlike a checkin",
     *     tags={"Checkin Likes"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="checkinLike",
     *         in="path",
     *         required=true,
     *         description="UUID of the like to delete",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Like removed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Like removed successfully")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Like not found"),
     *     @OA\Response(response=403, description="Unauthorized to remove this like"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function destroy(CheckinLike $checkinLike): JsonResponse
    {
        // Check if the authenticated user is the owner of the like
        if ($checkinLike->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized to remove this like'], 403);
        }

        $checkinLike->delete();

        return response()->json(['message' => 'Like removed successfully']);
    }
}
