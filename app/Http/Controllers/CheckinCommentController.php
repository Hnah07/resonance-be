<?php

namespace App\Http\Controllers;

use App\Models\CheckinComment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="Checkin Comments",
 *     description="API Endpoints for managing check-in comments. Comments allow any authenticated user to interact with and discuss check-ins. Multiple comments can exist for each check-in."
 * )
 */
class CheckinCommentController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/checkin-comments",
     *     summary="Get all comments for a checkin",
     *     tags={"Checkin Comments"},
     *     @OA\Parameter(
     *         name="checkin_id",
     *         in="query",
     *         required=true,
     *         description="ID of the checkin to get comments for",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of comments retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="checkin_id", type="string", format="uuid"),
     *                 @OA\Property(property="user_id", type="string", format="uuid"),
     *                 @OA\Property(property="comment", type="string"),
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
     *     @OA\Response(response=400, description="Invalid checkin ID provided")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'checkin_id' => 'required|uuid|exists:checkins,id'
        ]);

        $comments = CheckinComment::with('user:id,name,username')
            ->where('checkin_id', $request->checkin_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($comments);
    }

    /**
     * @OA\Post(
     *     path="/api/checkin-comments",
     *     summary="Create a new comment on a checkin",
     *     tags={"Checkin Comments"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"checkin_id", "comment"},
     *             @OA\Property(property="checkin_id", type="string", format="uuid"),
     *             @OA\Property(property="comment", type="string", minLength=1, maxLength=1000)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Comment created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", format="uuid"),
     *             @OA\Property(property="checkin_id", type="string", format="uuid"),
     *             @OA\Property(property="user_id", type="string", format="uuid"),
     *             @OA\Property(property="comment", type="string"),
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
            'checkin_id' => 'required|uuid|exists:checkins,id',
            'comment' => 'required|string|min:1|max:1000'
        ]);

        $comment = CheckinComment::create([
            'checkin_id' => $validated['checkin_id'],
            'user_id' => Auth::id(),
            'comment' => $validated['comment']
        ]);

        return response()->json($comment, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/checkin-comments/{id}",
     *     summary="Get a specific comment",
     *     tags={"Checkin Comments"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Comment ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comment retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", format="uuid"),
     *             @OA\Property(property="checkin_id", type="string", format="uuid"),
     *             @OA\Property(property="user_id", type="string", format="uuid"),
     *             @OA\Property(property="comment", type="string"),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time"),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="username", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Comment not found")
     * )
     */
    public function show(CheckinComment $checkinComment): JsonResponse
    {
        $checkinComment->load('user:id,name,username');
        return response()->json($checkinComment);
    }

    /**
     * @OA\Put(
     *     path="/api/checkin-comments/{id}",
     *     summary="Update a comment",
     *     tags={"Checkin Comments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Comment ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"comment"},
     *             @OA\Property(property="comment", type="string", minLength=1, maxLength=1000)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comment updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", format="uuid"),
     *             @OA\Property(property="checkin_id", type="string", format="uuid"),
     *             @OA\Property(property="user_id", type="string", format="uuid"),
     *             @OA\Property(property="comment", type="string"),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized to update this comment"),
     *     @OA\Response(response=404, description="Comment not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(Request $request, CheckinComment $checkinComment): JsonResponse
    {
        if ($checkinComment->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized to update this comment'], 403);
        }

        $validated = $request->validate([
            'comment' => 'required|string|min:1|max:1000'
        ]);

        $checkinComment->update($validated);

        return response()->json($checkinComment);
    }

    /**
     * @OA\Delete(
     *     path="/api/checkin-comments/{id}",
     *     summary="Delete a comment",
     *     tags={"Checkin Comments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Comment ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Comment deleted successfully"
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized to delete this comment"),
     *     @OA\Response(response=404, description="Comment not found")
     * )
     */
    public function destroy(CheckinComment $checkinComment): JsonResponse
    {
        if ($checkinComment->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized to delete this comment'], 403);
        }

        $checkinComment->delete();

        return response()->json(null, 204);
    }
}
