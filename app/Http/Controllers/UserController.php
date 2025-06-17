<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Users",
 *     description="API Endpoints for managing user profiles, search, and user listings"
 * )
 */
class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     summary="Get a specific user's profile",
     *     description="Retrieve detailed information about a specific user including their profile, stats, and relationships",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="User UUID",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid",
     *             example="123e4567-e89b-12d3-a456-426614174000"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User profile retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000"),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="username", type="string", example="johndoe"),
     *                 @OA\Property(property="profile_photo_url", type="string", nullable=true, example="https://example.com/photos/profile.jpg"),
     *                 @OA\Property(property="bio", type="string", nullable=true, example="Music lover and concert enthusiast"),
     *                 @OA\Property(property="city", type="string", nullable=true, example="Antwerp"),
     *                 @OA\Property(property="country", type="object", nullable=true,
     *                     @OA\Property(property="id", type="string", format="uuid", example="456e7890-e89b-12d3-a456-426614174000"),
     *                     @OA\Property(property="name", type="string", example="Belgium")
     *                 ),
     *                 @OA\Property(property="country_name", type="string", nullable=true, example="Belgium"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
     *                 @OA\Property(property="stats", type="object",
     *                     @OA\Property(property="followers_count", type="integer", example=42),
     *                     @OA\Property(property="following_count", type="integer", example=15),
     *                     @OA\Property(property="checkins_count", type="integer", example=67),
     *                     @OA\Property(property="concerts_count", type="integer", example=23),
     *                     @OA\Property(property="artists_count", type="integer", example=18)
     *                 ),
     *                 @OA\Property(property="is_following", type="boolean", example=false),
     *                 @OA\Property(property="is_current_user", type="boolean", example=false),
     *                 @OA\Property(property="email", type="string", example="john@example.com"),
     *                 @OA\Property(property="role", type="string", example="user"),
     *                 @OA\Property(property="is_active", type="boolean", example=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    public function show(string $id)
    {
        $user = User::with(['country', 'followers', 'following', 'checkins'])
            ->findOrFail($id);

        return new UserResource($user);
    }

    /**
     * @OA\Get(
     *     path="/api/users/search",
     *     summary="Search for users by username or name",
     *     description="Search for users using their username or display name. Returns up to 10 matching users.",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="query",
     *         in="query",
     *         required=false,
     *         description="Search term to match against username or name",
     *         @OA\Schema(
     *             type="string",
     *             example="john",
     *             minLength=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Search results or empty array if no query provided",
     *         @OA\JsonContent(
     *             oneOf={
     *                 @OA\Schema(
     *                     type="object",
     *                     @OA\Property(property="data", type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000"),
     *                             @OA\Property(property="name", type="string", example="John Doe"),
     *                             @OA\Property(property="username", type="string", example="johndoe"),
     *                             @OA\Property(property="profile_photo_url", type="string", nullable=true, example="https://example.com/photos/profile.jpg"),
     *                             @OA\Property(property="bio", type="string", nullable=true, example="Music lover and concert enthusiast"),
     *                             @OA\Property(property="city", type="string", nullable=true, example="Antwerp"),
     *                             @OA\Property(property="country", type="object", nullable=true,
     *                                 @OA\Property(property="id", type="string", format="uuid", example="456e7890-e89b-12d3-a456-426614174000"),
     *                                 @OA\Property(property="name", type="string", example="Belgium")
     *                             ),
     *                             @OA\Property(property="country_name", type="string", nullable=true, example="Belgium"),
     *                             @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
     *                             @OA\Property(property="stats", type="object",
     *                                 @OA\Property(property="followers_count", type="integer", example=42),
     *                                 @OA\Property(property="following_count", type="integer", example=15),
     *                                 @OA\Property(property="checkins_count", type="integer", example=67),
     *                                 @OA\Property(property="concerts_count", type="integer", example=23),
     *                                 @OA\Property(property="artists_count", type="integer", example=18)
     *                             ),
     *                             @OA\Property(property="is_following", type="boolean", example=false),
     *                             @OA\Property(property="is_current_user", type="boolean", example=false)
     *                         )
     *                     )
     *                 ),
     *                 @OA\Schema(
     *                     type="object",
     *                     @OA\Property(property="users", type="array", @OA\Items())
     *                 )
     *             }
     *         )
     *     )
     * )
     */
    public function search(Request $request)
    {
        $query = $request->input('query');

        if (empty($query)) {
            return response()->json(['users' => []]);
        }

        $users = User::where('username', 'like', "%{$query}%")
            ->orWhere('name', 'like', "%{$query}%")
            ->with(['country'])
            ->limit(10)
            ->get();

        return UserResource::collection($users);
    }

    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="Get a paginated list of users",
     *     description="Retrieve a paginated list of all users, ordered by creation date (newest first)",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Page number for pagination",
     *         @OA\Schema(
     *             type="integer",
     *             default=1,
     *             minimum=1,
     *             example=1
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Number of users per page",
     *         @OA\Schema(
     *             type="integer",
     *             default=20,
     *             minimum=1,
     *             maximum=100,
     *             example=20
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of users",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000"),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="username", type="string", example="johndoe"),
     *                     @OA\Property(property="profile_photo_url", type="string", nullable=true, example="https://example.com/photos/profile.jpg"),
     *                     @OA\Property(property="bio", type="string", nullable=true, example="Music lover and concert enthusiast"),
     *                     @OA\Property(property="city", type="string", nullable=true, example="Antwerp"),
     *                     @OA\Property(property="country", type="object", nullable=true,
     *                         @OA\Property(property="id", type="string", format="uuid", example="456e7890-e89b-12d3-a456-426614174000"),
     *                         @OA\Property(property="name", type="string", example="Belgium")
     *                     ),
     *                     @OA\Property(property="country_name", type="string", nullable=true, example="Belgium"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
     *                     @OA\Property(property="stats", type="object",
     *                         @OA\Property(property="followers_count", type="integer", example=42),
     *                         @OA\Property(property="following_count", type="integer", example=15),
     *                         @OA\Property(property="checkins_count", type="integer", example=67),
     *                         @OA\Property(property="concerts_count", type="integer", example=23),
     *                         @OA\Property(property="artists_count", type="integer", example=18)
     *                     ),
     *                     @OA\Property(property="is_following", type="boolean", example=false),
     *                     @OA\Property(property="is_current_user", type="boolean", example=false)
     *                 )
     *             ),
     *             @OA\Property(property="links", type="object",
     *                 @OA\Property(property="first", type="string", example="http://localhost/api/users?page=1"),
     *                 @OA\Property(property="last", type="string", example="http://localhost/api/users?page=5"),
     *                 @OA\Property(property="prev", type="string", nullable=true),
     *                 @OA\Property(property="next", type="string", example="http://localhost/api/users?page=2")
     *             ),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="per_page", type="integer", example=20),
     *                 @OA\Property(property="to", type="integer", example=20),
     *                 @OA\Property(property="total", type="integer", example=100)
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $users = User::with(['country'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return UserResource::collection($users);
    }
}
