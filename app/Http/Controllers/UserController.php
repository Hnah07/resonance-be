<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Http\Resources\CheckinResource;
use App\Models\User;
use App\Models\Follower;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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

        // Debug authentication state
        Log::info('UserController Debug', [
            'requested_user_id' => $id,
            'auth_check' => Auth::guard('api')->check(),
            'auth_id' => Auth::guard('api')->id(),
            'user_id' => $user->id,
            'is_current_user' => Auth::guard('api')->check() && Auth::guard('api')->id() === $user->id,
            'authorization_header' => request()->header('Authorization'),
            'token_exists' => request()->bearerToken() ? 'yes' : 'no',
            'token' => request()->bearerToken(),
            'guard' => Auth::getDefaultDriver(),
        ]);

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

    /**
     * @OA\Get(
     *     path="/api/users/{id}/check-ins",
     *     summary="Get check-ins for a specific user",
     *     description="Retrieve a paginated list of check-ins for a specific user, ordered by creation date (newest first)",
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
     *         description="Number of check-ins per page",
     *         @OA\Schema(
     *             type="integer",
     *             default=10,
     *             minimum=1,
     *             maximum=50,
     *             example=10
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of user check-ins",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000"),
     *                     @OA\Property(property="user", type="object",
     *                         @OA\Property(property="id", type="string", format="uuid", example="456e7890-e89b-12d3-a456-426614174000"),
     *                         @OA\Property(property="name", type="string", example="John Doe"),
     *                         @OA\Property(property="username", type="string", example="johndoe"),
     *                         @OA\Property(property="profile_photo_url", type="string", nullable=true, example="https://example.com/photos/profile.jpg")
     *                     ),
     *                     @OA\Property(property="concert", type="object",
     *                         @OA\Property(property="id", type="string", format="uuid", example="789e0123-e89b-12d3-a456-426614174000"),
     *                         @OA\Property(property="date", type="string", format="date", example="2024-07-19"),
     *                         @OA\Property(property="event", type="object",
     *                             @OA\Property(property="id", type="string", format="uuid", example="012e3456-e89b-12d3-a456-426614174000"),
     *                             @OA\Property(property="name", type="string", example="Tomorrowland 2024"),
     *                             @OA\Property(property="type", type="string", example="festival"),
     *                             @OA\Property(property="image_url", type="string", nullable=true, example="https://example.com/events/tomorrowland.jpg")
     *                         ),
     *                         @OA\Property(property="location", type="object",
     *                             @OA\Property(property="id", type="string", format="uuid", example="345e6789-e89b-12d3-a456-426614174000"),
     *                             @OA\Property(property="name", type="string", example="Boom"),
     *                             @OA\Property(property="city", type="string", example="Boom"),
     *                             @OA\Property(property="country", type="string", example="Belgium")
     *                         ),
     *                         @OA\Property(property="artists", type="array",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="id", type="string", format="uuid", example="678e9012-e89b-12d3-a456-426614174000"),
     *                                 @OA\Property(property="name", type="string", example="Martin Garrix"),
     *                                 @OA\Property(property="image_url", type="string", nullable=true, example="https://example.com/artists/martin-garrix.jpg"),
     *                                 @OA\Property(property="genres", type="array",
     *                                     @OA\Items(
     *                                         type="object",
     *                                         @OA\Property(property="id", type="string", format="uuid", example="901e2345-e89b-12d3-a456-426614174000"),
     *                                         @OA\Property(property="name", type="string", example="Electronic")
     *                                     )
     *                                 )
     *                             )
     *                         )
     *                     ),
     *                     @OA\Property(property="photos", type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="string", format="uuid", example="234e5678-e89b-12d3-a456-426614174000"),
     *                             @OA\Property(property="url", type="string", example="https://example.com/photos/checkin.jpg"),
     *                             @OA\Property(property="caption", type="string", nullable=true, example="Amazing night!")
     *                         )
     *                     ),
     *                     @OA\Property(property="likes_count", type="integer", example=5),
     *                     @OA\Property(property="comments_count", type="integer", example=3),
     *                     @OA\Property(property="comments", type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="string", format="uuid", example="567e8901-e89b-12d3-a456-426614174000"),
     *                             @OA\Property(property="comment", type="string", example="Looks amazing!"),
     *                             @OA\Property(property="created_at", type="string", format="date-time", example="2024-07-19T20:00:00Z"),
     *                             @OA\Property(property="user", type="object",
     *                                 @OA\Property(property="id", type="string", format="uuid", example="890e1234-e89b-12d3-a456-426614174000"),
     *                                 @OA\Property(property="name", type="string", example="Jane Smith"),
     *                                 @OA\Property(property="username", type="string", example="janesmith"),
     *                                 @OA\Property(property="profile_photo_url", type="string", nullable=true, example="https://example.com/photos/jane.jpg")
     *                             )
     *                         )
     *                     ),
     *                     @OA\Property(property="is_liked", type="boolean", example=false),
     *                     @OA\Property(property="rating", type="number", format="float", nullable=true, example=4.5),
     *                     @OA\Property(property="review", type="string", nullable=true, example="Incredible experience!"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-07-19T19:30:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-07-19T19:30:00Z")
     *                 )
     *             ),
     *             @OA\Property(property="links", type="object",
     *                 @OA\Property(property="first", type="string", example="http://localhost/api/users/123e4567-e89b-12d3-a456-426614174000/check-ins?page=1"),
     *                 @OA\Property(property="last", type="string", example="http://localhost/api/users/123e4567-e89b-12d3-a456-426614174000/check-ins?page=3"),
     *                 @OA\Property(property="prev", type="string", nullable=true),
     *                 @OA\Property(property="next", type="string", example="http://localhost/api/users/123e4567-e89b-12d3-a456-426614174000/check-ins?page=2")
     *             ),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=3),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="to", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=25)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    public function checkins(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $checkins = $user->checkins()
            ->with([
                'user:id,name,username,profile_photo_path',
                'concert:id,date,event_id',
                'concert.event:id,name,type,image_url',
                'concert.location:id,name,city,country_id',
                'concert.location.country:id,name',
                'concert.artists:id,name,image_url',
                'concert.artists.genres:id,genre',
                'photos:id,checkin_id,url,caption',
                'likes:id,checkin_id,user_id',
                'comments:id,checkin_id,comment,created_at,user_id',
                'comments.user:id,name,username,profile_photo_path',
                'rating:id,checkin_id,rating',
                'review:id,checkin_id,review'
            ])
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 10));

        return CheckinResource::collection($checkins);
    }
}
