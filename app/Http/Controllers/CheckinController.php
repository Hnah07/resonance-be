<?php

namespace App\Http\Controllers;

use App\Models\Checkin;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Checkins",
 *     description="API Endpoints for managing concert check-ins"
 * )
 */
class CheckinController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/checkins",
     *     summary="Get all check-ins",
     *     tags={"Checkins"},
     *     @OA\Response(
     *         response=200,
     *         description="List of all check-ins",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="concert_id", type="string", format="uuid"),
     *                 @OA\Property(property="user_id", type="string", format="uuid"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time"),
     *                 @OA\Property(
     *                     property="concert",
     *                     type="object",
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="date", type="string", format="date-time")
     *                 ),
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="name", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(): JsonResponse
    {
        $checkins = Checkin::with([
            'user:id,name,profile_photo_path',
            'concert:id,date,event_id',
            'concert.event:id,name',
            'concert.artists:id,name',
            'photos:id,checkin_id,url',
            'likes:id,checkin_id,user_id',
            'comments.user:id,name,profile_photo_path'
        ])->latest()->paginate(10);

        return response()->json($checkins);
    }

    /**
     * @OA\Post(
     *     path="/api/checkins",
     *     summary="Create a new check-in",
     *     tags={"Checkins"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"concert_id"},
     *             @OA\Property(property="concert_id", type="string", format="uuid", description="ID of the concert to check in to")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Check-in created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="string", format="uuid"),
     *             @OA\Property(property="concert_id", type="string", format="uuid"),
     *             @OA\Property(property="user_id", type="string", format="uuid"),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'concert_id' => 'required|uuid|exists:concerts,id'
        ]);

        $alreadyCheckedIn = Checkin::where('user_id', Auth::id())->where('concert_id', $validated['concert_id'])->first();
        if ($alreadyCheckedIn) {
            return response()->json(['message' => 'You have already checked in to this concert'], 422);
        }

        $checkin = Checkin::create([
            'concert_id' => $validated['concert_id'],
            'user_id' => Auth::id()
        ]);


        // Only load the concert relationship for the response
        $checkin->load('concert:id,date,event_id', 'concert.event:id,name');

        return response()->json($checkin, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/checkins/{id}",
     *     summary="Get a specific check-in",
     *     tags={"Checkins"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Check-in UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Check-in details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="string", format="uuid"),
     *             @OA\Property(property="concert_id", type="string", format="uuid"),
     *             @OA\Property(property="user_id", type="string", format="uuid"),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time"),
     *             @OA\Property(
     *                 property="concert",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="date", type="string", format="date-time")
     *             ),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="name", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Check-in not found")
     * )
     */
    public function show(Checkin $checkin): JsonResponse
    {
        $checkin->load([
            'user:id,name,profile_photo_path',
            'concert:id,date,event_id',
            'concert.event:id,name',
            'concert.artists:id,name',
            'photos:id,checkin_id,url',
            'likes:id,checkin_id,user_id',
            'comments.user:id,name,profile_photo_path'
        ]);

        return response()->json($checkin);
    }

    /**
     * @OA\Delete(
     *     path="/api/checkins/{id}",
     *     summary="Delete a check-in",
     *     tags={"Checkins"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Check-in UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Check-in deleted successfully"
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Check-in not found")
     * )
     */
    public function destroy(Checkin $checkin): JsonResponse
    {
        // Only allow users to delete their own check-ins
        if ($checkin->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $checkin->delete();
        return response()->json(null, 204);
    }
}
