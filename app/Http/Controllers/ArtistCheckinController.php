<?php

namespace App\Http\Controllers;

use App\Models\ArtistCheckin;
use App\Models\Checkin;
use App\Models\Artist;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Artist Checkins",
 *     description="API Endpoints for managing artist check-ins"
 * )
 */
class ArtistCheckinController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/artist-checkins",
     *     summary="Get all artist check-ins",
     *     tags={"Artist Checkins"},
     *     @OA\Response(
     *         response=200,
     *         description="List of all artist check-ins",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(
     *                     property="artist",
     *                     type="object",
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="name", type="string")
     *                 ),
     *                 @OA\Property(
     *                     property="checkin",
     *                     type="object",
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="concert_id", type="string", format="uuid"),
     *                     @OA\Property(property="user_id", type="string", format="uuid")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(): JsonResponse
    {
        $artist_checkins = ArtistCheckin::with(['artist:id,name', 'checkin:id,concert_id,user_id'])->get();
        return response()->json($artist_checkins);
    }

    /**
     * @OA\Post(
     *     path="/api/artist-checkins",
     *     summary="Create a new artist check-in",
     *     tags={"Artist Checkins"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"checkin_id", "artist_id"},
     *             @OA\Property(property="checkin_id", type="string", format="uuid", description="ID of the check-in"),
     *             @OA\Property(property="artist_id", type="string", format="uuid", description="ID of the artist")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Artist check-in created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Artist check-in created successfully"),
     *             @OA\Property(
     *                 property="artist_checkin",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="checkin_id", type="string", format="uuid"),
     *                 @OA\Property(property="artist_id", type="string", format="uuid")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=403, description="Unauthorized - User can only add artist check-ins to their own check-ins")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'checkin_id' => 'required|uuid|exists:checkins,id',
            'artist_id' => 'required|uuid|exists:artists,id'
        ]);

        // Verify the check-in belongs to the authenticated user
        $checkin = Checkin::findOrFail($validated['checkin_id']);
        if ($checkin->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized - You can only add artist check-ins to your own check-ins'], 403);
        }

        // Verify the artist is associated with the concert
        $concert = $checkin->concert;
        if (!$concert->artists()->where('artist_id', $validated['artist_id'])->exists()) {
            return response()->json(['message' => 'This artist is not associated with this concert'], 422);
        }

        // Check if artist check-in already exists
        if (ArtistCheckin::where('checkin_id', $validated['checkin_id'])
            ->where('artist_id', $validated['artist_id'])
            ->exists()
        ) {
            return response()->json(['message' => 'Artist check-in already exists for this check-in'], 422);
        }

        $artist_checkin = ArtistCheckin::create($validated);

        return response()->json([
            'message' => 'Artist check-in created successfully',
            'artist_checkin' => $artist_checkin
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/artist-checkins/{id}",
     *     summary="Get a specific artist check-in",
     *     tags={"Artist Checkins"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Artist check-in UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Artist check-in details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="string", format="uuid"),
     *             @OA\Property(
     *                 property="artist",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="name", type="string")
     *             ),
     *             @OA\Property(
     *                 property="checkin",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="concert_id", type="string", format="uuid"),
     *                 @OA\Property(property="user_id", type="string", format="uuid")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Artist check-in not found")
     * )
     */
    public function show(ArtistCheckin $artist_checkin): JsonResponse
    {
        $artist_checkin->load(['artist:id,name', 'checkin:id,concert_id,user_id']);
        return response()->json($artist_checkin);
    }

    /**
     * @OA\Delete(
     *     path="/api/artist-checkins/{id}",
     *     summary="Delete an artist check-in",
     *     tags={"Artist Checkins"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Artist check-in UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Artist check-in deleted successfully"
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized - User can only delete their own artist check-ins"),
     *     @OA\Response(response=404, description="Artist check-in not found")
     * )
     */
    public function destroy(ArtistCheckin $artist_checkin): JsonResponse
    {
        // Verify the check-in belongs to the authenticated user
        if ($artist_checkin->checkin->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized - You can only delete your own artist check-ins'], 403);
        }

        $artist_checkin->delete();
        return response()->json(null, 204);
    }
}
