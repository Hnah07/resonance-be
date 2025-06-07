<?php

namespace App\Http\Controllers;

use App\Models\Follower;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Followers",
 *     description="API Endpoints for managing user follows"
 * )
 */
class FollowerController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/followers",
     *     summary="Get all followers",
     *     tags={"Followers"},
     *     @OA\Response(
     *         response=200,
     *         description="List of all followers",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(
     *                     property="follower",
     *                     type="object",
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="username", type="string"),
     *                     @OA\Property(property="profile_photo_path", type="string", nullable=true)
     *                 ),
     *                 @OA\Property(
     *                     property="followed",
     *                     type="object",
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="username", type="string"),
     *                     @OA\Property(property="profile_photo_path", type="string", nullable=true)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(): JsonResponse
    {
        $followers = Follower::with('followers')->get();

        return response()->json($followers);
    }

    /**
     * @OA\Post(
     *     path="/api/followers",
     *     summary="Follow a user",
     *     tags={"Followers"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"followed_id"},
     *             @OA\Property(property="followed_id", type="string", format="uuid", description="ID of the user to follow")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successfully followed user",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Successfully followed user"),
     *             @OA\Property(
     *                 property="follower",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="username", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=409, description="Already following this user")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'followed_id' => 'required|uuid|exists:users,id'
        ]);

        // Prevent self-following
        if ($validated['followed_id'] === Auth::id()) {
            return response()->json(['message' => 'You cannot follow yourself'], 422);
        }

        // Check if already following
        $existingFollow = Follower::where('follower_id', Auth::id())
            ->where('followed_id', $validated['followed_id'])
            ->first();

        if ($existingFollow) {
            return response()->json(['message' => 'You are already following this user'], 409);
        }

        $follower = Follower::create([
            'follower_id' => Auth::id(),
            'followed_id' => $validated['followed_id']
        ]);

        return response()->json([
            'message' => 'Successfully followed user',
            'follower' => $follower->load('followed:id,name,username')
        ], 201);
    }

    /**
     * @OA\Delete(
     *     path="/api/followers/{id}",
     *     summary="Unfollow a user",
     *     tags={"Followers"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="User UUID to unfollow",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Successfully unfollowed user"
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Follow relationship not found")
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        $follow = Follower::where('follower_id', Auth::id())
            ->where('followed_id', $id)
            ->first();

        if (!$follow) {
            return response()->json(['message' => 'Follow relationship not found'], 404);
        }

        $follow->delete();
        return response()->json(null, 204);
    }

    /**
     * @OA\Get(
     *     path="/api/users/{id}/followers",
     *     summary="Get a user's followers",
     *     tags={"Followers"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="User UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of user's followers",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="username", type="string"),
     *                 @OA\Property(property="profile_photo_path", type="string", nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="User not found")
     * )
     */
    public function getFollowers(string $id): JsonResponse
    {
        $user = User::findOrFail($id);
        return response()->json($user->followers()->with('profile_photo_path')->get());
    }

    /**
     * @OA\Get(
     *     path="/api/users/{id}/following",
     *     summary="Get users that a user is following",
     *     tags={"Followers"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="User UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of users being followed",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="username", type="string"),
     *                 @OA\Property(property="profile_photo_path", type="string", nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="User not found")
     * )
     */
    public function getFollowing(string $id): JsonResponse
    {
        $user = User::findOrFail($id);
        return response()->json($user->following()->with('profile_photo_path')->get());
    }
}
