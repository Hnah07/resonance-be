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
use Illuminate\Support\Facades\DB;
use App\Models\Checkin;

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

    /**
     * @OA\Get(
     *     path="/api/users/{userId}/summary-stats",
     *     summary="Get summary statistics for a specific user",
     *     description="Retrieve summary statistics including followers, following, check-ins, concerts, and artists for a specific user",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="userId",
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
     *         description="User summary statistics retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user_id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000"),
     *                 @OA\Property(property="followers_count", type="integer", example=42),
     *                 @OA\Property(property="following_count", type="integer", example=15),
     *                 @OA\Property(property="checkins_count", type="integer", example=67),
     *                 @OA\Property(property="concerts_count", type="integer", example=23),
     *                 @OA\Property(property="artists_count", type="integer", example=18),
     *                 @OA\Property(property="total_rating", type="number", format="float", example=4.2),
     *                 @OA\Property(property="average_rating", type="number", format="float", example=4.1),
     *                 @OA\Property(property="reviews_count", type="integer", example=12),
     *                 @OA\Property(property="photos_count", type="integer", example=45),
     *                 @OA\Property(property="likes_received", type="integer", example=156),
     *                 @OA\Property(property="comments_received", type="integer", example=89)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    public function summaryStats(string $userId): JsonResponse
    {
        $user = User::findOrFail($userId);

        // Get basic counts
        $followersCount = $user->followers()->count();
        $followingCount = $user->following()->count();
        $checkinsCount = $user->checkins()->count();

        // Get unique concerts and artists from check-ins
        $concertsCount = $user->checkins()->distinct('concert_id')->count();
        $artistsCount = $user->checkins()
            ->join('artist_checkins', 'checkins.id', '=', 'artist_checkins.checkin_id')
            ->distinct('artist_checkins.artist_id')
            ->count();

        // Get rating statistics
        $ratingStats = $user->checkins()
            ->join('checkin_ratings', 'checkins.id', '=', 'checkin_ratings.checkin_id')
            ->selectRaw('COUNT(*) as reviews_count, AVG(checkin_ratings.rating) as average_rating, SUM(checkin_ratings.rating) as total_rating')
            ->first();

        // Get photos count
        $photosCount = $user->checkins()
            ->join('checkin_photos', 'checkins.id', '=', 'checkin_photos.checkin_id')
            ->count();

        // Get likes and comments received
        $likesReceived = $user->checkins()
            ->join('checkin_likes', 'checkins.id', '=', 'checkin_likes.checkin_id')
            ->count();

        $commentsReceived = $user->checkins()
            ->join('checkin_comments', 'checkins.id', '=', 'checkin_comments.checkin_id')
            ->count();

        return response()->json([
            'data' => [
                'user_id' => $user->id,
                'followers_count' => $followersCount,
                'following_count' => $followingCount,
                'checkins_count' => $checkinsCount,
                'concerts_count' => $concertsCount,
                'artists_count' => $artistsCount,
                'total_rating' => $ratingStats->total_rating ?? 0,
                'average_rating' => round($ratingStats->average_rating ?? 0, 2),
                'reviews_count' => $ratingStats->reviews_count ?? 0,
                'photos_count' => $photosCount,
                'likes_received' => $likesReceived,
                'comments_received' => $commentsReceived,
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/users/{userId}/stats",
     *     summary="Get detailed statistics for a specific user",
     *     description="Retrieve detailed statistics including monthly attendance, genre distribution, top venues, and top artists for a specific user",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="userId",
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
     *         description="User detailed statistics retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="followers_count", type="integer", example=42),
     *             @OA\Property(property="following_count", type="integer", example=15),
     *             @OA\Property(property="followers", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="username", type="string"),
     *                     @OA\Property(property="profile_photo_path", type="string", nullable=true)
     *                 )
     *             ),
     *             @OA\Property(property="following", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="username", type="string"),
     *                     @OA\Property(property="profile_photo_path", type="string", nullable=true)
     *                 )
     *             ),
     *             @OA\Property(property="monthly_attendance", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="month_number", type="integer", example=1),
     *                     @OA\Property(property="month", type="string", example="Jan"),
     *                     @OA\Property(property="count", type="integer", example=5)
     *                 )
     *             ),
     *             @OA\Property(property="genre_distribution", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="genre", type="string", example="Electronic"),
     *                     @OA\Property(property="count", type="integer", example=15)
     *                 )
     *             ),
     *             @OA\Property(property="top_venues", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="venue", type="string", example="Tomorrowland"),
     *                     @OA\Property(property="count", type="integer", example=3)
     *                 )
     *             ),
     *             @OA\Property(property="top_artists", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="artist", type="string", example="Martin Garrix"),
     *                     @OA\Property(property="image", type="string", nullable=true),
     *                     @OA\Property(property="count", type="integer", example=2)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    public function stats(string $userId): JsonResponse
    {
        $user = User::findOrFail($userId);

        // Get followers (users that follow this user)
        $followers = User::select('users.id', 'users.name', 'users.username', 'users.profile_photo_path')
            ->join('followers', 'users.id', '=', 'followers.follower_id')
            ->where('followers.followed_id', $user->id)
            ->get();

        // Get following (users that this user follows)
        $following = User::select('users.id', 'users.name', 'users.username', 'users.profile_photo_path')
            ->join('followers', 'users.id', '=', 'followers.followed_id')
            ->where('followers.follower_id', $user->id)
            ->get();

        // Monthly concert attendance based on concert dates
        $monthlyAttendance = Checkin::where('user_id', $user->id)
            ->join('concerts', 'checkins.concert_id', '=', 'concerts.id')
            ->select(
                DB::raw('MONTH(concerts.date) as month_number'),
                DB::raw('DATE_FORMAT(concerts.date, "%b") as month'),
                DB::raw('COUNT(DISTINCT checkins.id) as count')
            )
            ->groupBy('month_number', 'month')
            ->orderBy('month_number')
            ->get();

        // Genre distribution
        $genreDistribution = Checkin::where('user_id', $user->id)
            ->join('artist_checkins', 'checkins.id', '=', 'artist_checkins.checkin_id')
            ->join('artists', 'artist_checkins.artist_id', '=', 'artists.id')
            ->join('artist_genres', 'artists.id', '=', 'artist_genres.artist_id')
            ->join('genres', 'artist_genres.genre_id', '=', 'genres.id')
            ->select('genres.genre as genre', DB::raw('COUNT(DISTINCT checkins.id) as count'))
            ->groupBy('genres.id', 'genres.genre')
            ->orderByDesc('count')
            ->get();

        // Top venues
        $topVenues = Checkin::where('user_id', $user->id)
            ->join('concerts', 'checkins.concert_id', '=', 'concerts.id')
            ->join('locations', 'concerts.location_id', '=', 'locations.id')
            ->select('locations.name as venue', DB::raw('COUNT(DISTINCT checkins.id) as count'))
            ->groupBy('locations.id', 'locations.name')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Top artists
        $topArtists = Checkin::where('user_id', $user->id)
            ->join('artist_checkins', 'checkins.id', '=', 'artist_checkins.checkin_id')
            ->join('artists', 'artist_checkins.artist_id', '=', 'artists.id')
            ->select(
                'artists.name as artist',
                'artists.image_url as image',
                DB::raw('COUNT(DISTINCT checkins.id) as count')
            )
            ->groupBy('artists.id', 'artists.name', 'artists.image_url')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return response()->json([
            'followers_count' => $followers->count(),
            'following_count' => $following->count(),
            'followers' => $followers,
            'following' => $following,
            'monthly_attendance' => $monthlyAttendance,
            'genre_distribution' => $genreDistribution,
            'top_venues' => $topVenues,
            'top_artists' => $topArtists
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/users/{userId}/photos",
     *     summary="Get all photos for a specific user",
     *     description="Retrieve all check-in photos for a specific user, ordered by creation date (newest first)",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="userId",
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
     *         description="Number of photos per page",
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
     *         description="User photos retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="url", type="string"),
     *                     @OA\Property(property="caption", type="string", nullable=true),
     *                     @OA\Property(property="checkin", type="object",
     *                         @OA\Property(property="id", type="string", format="uuid"),
     *                         @OA\Property(property="concert", type="object",
     *                             @OA\Property(property="date", type="string", format="date"),
     *                             @OA\Property(property="event", type="object",
     *                                 @OA\Property(property="name", type="string"),
     *                                 @OA\Property(property="type", type="string")
     *                             ),
     *                             @OA\Property(property="location", type="object",
     *                                 @OA\Property(property="name", type="string"),
     *                                 @OA\Property(property="city", type="string")
     *                             )
     *                         ),
     *                         @OA\Property(property="created_at", type="string", format="date-time")
     *                     ),
     *                     @OA\Property(property="created_at", type="string", format="date-time")
     *                 )
     *             ),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    public function photos(Request $request, string $userId): JsonResponse
    {
        $user = User::findOrFail($userId);

        $photos = $user->checkins()
            ->join('checkin_photos', 'checkins.id', '=', 'checkin_photos.checkin_id')
            ->join('concerts', 'checkins.concert_id', '=', 'concerts.id')
            ->join('events', 'concerts.event_id', '=', 'events.id')
            ->join('locations', 'concerts.location_id', '=', 'locations.id')
            ->select(
                'checkin_photos.id',
                'checkin_photos.url',
                'checkin_photos.caption',
                'checkin_photos.created_at',
                'checkins.id as checkin_id',
                'checkins.created_at as checkin_created_at',
                'concerts.date',
                'events.name as event_name',
                'events.type as event_type',
                'locations.name as location_name',
                'locations.city'
            )
            ->orderBy('checkin_photos.created_at', 'desc')
            ->paginate($request->input('per_page', 20));

        $formattedPhotos = $photos->getCollection()->map(function ($photo) {
            return [
                'id' => $photo->id,
                'url' => $photo->url,
                'caption' => $photo->caption,
                'checkin' => [
                    'id' => $photo->checkin_id,
                    'concert' => [
                        'date' => $photo->date,
                        'event' => [
                            'name' => $photo->event_name,
                            'type' => $photo->event_type
                        ],
                        'location' => [
                            'name' => $photo->location_name,
                            'city' => $photo->city
                        ]
                    ],
                    'created_at' => $photo->checkin_created_at
                ],
                'created_at' => $photo->created_at
            ];
        });

        return response()->json([
            'data' => $formattedPhotos,
            'links' => $photos->links(),
            'meta' => [
                'current_page' => $photos->currentPage(),
                'from' => $photos->firstItem(),
                'last_page' => $photos->lastPage(),
                'per_page' => $photos->perPage(),
                'to' => $photos->lastItem(),
                'total' => $photos->total()
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/users/{userId}/friends",
     *     summary="Get friends (followers and following) for a specific user",
     *     description="Retrieve both followers and users being followed by a specific user",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="userId",
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
     *         description="User friends retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="followers", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="username", type="string"),
     *                     @OA\Property(property="profile_photo_path", type="string", nullable=true),
     *                     @OA\Property(property="bio", type="string", nullable=true),
     *                     @OA\Property(property="city", type="string", nullable=true),
     *                     @OA\Property(property="country_name", type="string", nullable=true)
     *                 )
     *             ),
     *             @OA\Property(property="following", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="username", type="string"),
     *                     @OA\Property(property="profile_photo_path", type="string", nullable=true),
     *                     @OA\Property(property="bio", type="string", nullable=true),
     *                     @OA\Property(property="city", type="string", nullable=true),
     *                     @OA\Property(property="country_name", type="string", nullable=true)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    public function friends(string $userId): JsonResponse
    {
        $user = User::findOrFail($userId);

        // Get followers (users that follow this user)
        $followers = User::select(
            'users.id',
            'users.name',
            'users.username',
            'users.profile_photo_path',
            'users.bio',
            'users.city',
            'countries.name as country_name'
        )
            ->leftJoin('countries', 'users.country_id', '=', 'countries.id')
            ->join('followers', 'users.id', '=', 'followers.follower_id')
            ->where('followers.followed_id', $user->id)
            ->get();

        // Get following (users that this user follows)
        $following = User::select(
            'users.id',
            'users.name',
            'users.username',
            'users.profile_photo_path',
            'users.bio',
            'users.city',
            'countries.name as country_name'
        )
            ->leftJoin('countries', 'users.country_id', '=', 'countries.id')
            ->join('followers', 'users.id', '=', 'followers.followed_id')
            ->where('followers.follower_id', $user->id)
            ->get();

        return response()->json([
            'followers' => $followers,
            'following' => $following
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/users/{user}/follow",
     *     summary="Follow a user",
     *     description="Follow a specific user by their ID",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="User UUID to follow",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid",
     *             example="123e4567-e89b-12d3-a456-426614174000"
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
     *     @OA\Response(response=404, description="User not found"),
     *     @OA\Response(response=409, description="Already following this user"),
     *     @OA\Response(response=422, description="Cannot follow yourself")
     * )
     */
    public function follow(string $user): JsonResponse
    {
        $userToFollow = User::findOrFail($user);

        // Prevent self-following
        if ($userToFollow->id === Auth::id()) {
            return response()->json(['message' => 'You cannot follow yourself'], 422);
        }

        // Check if already following
        $existingFollow = Follower::where('follower_id', Auth::id())
            ->where('followed_id', $userToFollow->id)
            ->first();

        if ($existingFollow) {
            return response()->json(['message' => 'You are already following this user'], 409);
        }

        $follower = Follower::create([
            'follower_id' => Auth::id(),
            'followed_id' => $userToFollow->id
        ]);

        return response()->json([
            'message' => 'Successfully followed user',
            'follower' => $follower->load('followed:id,name,username')
        ], 201);
    }

    /**
     * @OA\Delete(
     *     path="/api/users/{user}/follow",
     *     summary="Unfollow a user",
     *     description="Unfollow a specific user by their ID",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="User UUID to unfollow",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid",
     *             example="123e4567-e89b-12d3-a456-426614174000"
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Successfully unfollowed user"
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Follow relationship not found")
     * )
     */
    public function unfollow(string $user): JsonResponse
    {
        $follow = Follower::where('follower_id', Auth::id())
            ->where('followed_id', $user)
            ->first();

        if (!$follow) {
            return response()->json(['message' => 'Follow relationship not found'], 404);
        }

        $follow->delete();
        return response()->json(null, 204);
    }

    /**
     * @OA\Get(
     *     path="/api/users/{username}",
     *     summary="Get a specific user's profile by username",
     *     description="Retrieve detailed information about a specific user using their username",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="username",
     *         in="path",
     *         required=true,
     *         description="User username",
     *         @OA\Schema(
     *             type="string",
     *             example="johndoe"
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
    public function showByUsername(string $username)
    {
        $user = User::with(['country', 'followers', 'following', 'checkins'])
            ->where('username', $username)
            ->firstOrFail();

        return new UserResource($user);
    }

    /**
     * @OA\Get(
     *     path="/api/users/{username}/summary-stats",
     *     summary="Get summary statistics for a specific user by username",
     *     description="Retrieve summary statistics including followers, following, check-ins, concerts, and artists for a specific user",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="username",
     *         in="path",
     *         required=true,
     *         description="User username",
     *         @OA\Schema(
     *             type="string",
     *             example="johndoe"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User summary statistics retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user_id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000"),
     *                 @OA\Property(property="followers_count", type="integer", example=42),
     *                 @OA\Property(property="following_count", type="integer", example=15),
     *                 @OA\Property(property="checkins_count", type="integer", example=67),
     *                 @OA\Property(property="concerts_count", type="integer", example=23),
     *                 @OA\Property(property="artists_count", type="integer", example=18),
     *                 @OA\Property(property="total_rating", type="number", format="float", example=4.2),
     *                 @OA\Property(property="average_rating", type="number", format="float", example=4.1),
     *                 @OA\Property(property="reviews_count", type="integer", example=12),
     *                 @OA\Property(property="photos_count", type="integer", example=45),
     *                 @OA\Property(property="likes_received", type="integer", example=156),
     *                 @OA\Property(property="comments_received", type="integer", example=89)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    public function summaryStatsByUsername(string $username): JsonResponse
    {
        $user = User::where('username', $username)->firstOrFail();

        // Get basic counts
        $followersCount = $user->followers()->count();
        $followingCount = $user->following()->count();
        $checkinsCount = $user->checkins()->count();

        // Get unique concerts and artists from check-ins
        $concertsCount = $user->checkins()->distinct('concert_id')->count();
        $artistsCount = $user->checkins()
            ->join('artist_checkins', 'checkins.id', '=', 'artist_checkins.checkin_id')
            ->distinct('artist_checkins.artist_id')
            ->count();

        // Get rating statistics
        $ratingStats = $user->checkins()
            ->join('checkin_ratings', 'checkins.id', '=', 'checkin_ratings.checkin_id')
            ->selectRaw('COUNT(*) as reviews_count, AVG(checkin_ratings.rating) as average_rating, SUM(checkin_ratings.rating) as total_rating')
            ->first();

        // Get photos count
        $photosCount = $user->checkins()
            ->join('checkin_photos', 'checkins.id', '=', 'checkin_photos.checkin_id')
            ->count();

        // Get likes and comments received
        $likesReceived = $user->checkins()
            ->join('checkin_likes', 'checkins.id', '=', 'checkin_likes.checkin_id')
            ->count();

        $commentsReceived = $user->checkins()
            ->join('checkin_comments', 'checkins.id', '=', 'checkin_comments.checkin_id')
            ->count();

        return response()->json([
            'data' => [
                'user_id' => $user->id,
                'followers_count' => $followersCount,
                'following_count' => $followingCount,
                'checkins_count' => $checkinsCount,
                'concerts_count' => $concertsCount,
                'artists_count' => $artistsCount,
                'total_rating' => $ratingStats->total_rating ?? 0,
                'average_rating' => round($ratingStats->average_rating ?? 0, 2),
                'reviews_count' => $ratingStats->reviews_count ?? 0,
                'photos_count' => $photosCount,
                'likes_received' => $likesReceived,
                'comments_received' => $commentsReceived,
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/users/{username}/stats",
     *     summary="Get detailed statistics for a specific user by username",
     *     description="Retrieve detailed statistics including monthly attendance, genre distribution, top venues, and top artists for a specific user",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="username",
     *         in="path",
     *         required=true,
     *         description="User username",
     *         @OA\Schema(
     *             type="string",
     *             example="johndoe"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User detailed statistics retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="followers_count", type="integer", example=42),
     *             @OA\Property(property="following_count", type="integer", example=15),
     *             @OA\Property(property="followers", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="username", type="string"),
     *                     @OA\Property(property="profile_photo_path", type="string", nullable=true)
     *                 )
     *             ),
     *             @OA\Property(property="following", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="username", type="string"),
     *                     @OA\Property(property="profile_photo_path", type="string", nullable=true)
     *                 )
     *             ),
     *             @OA\Property(property="monthly_attendance", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="month_number", type="integer", example=1),
     *                     @OA\Property(property="month", type="string", example="Jan"),
     *                     @OA\Property(property="count", type="integer", example=5)
     *                 )
     *             ),
     *             @OA\Property(property="genre_distribution", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="genre", type="string", example="Electronic"),
     *                     @OA\Property(property="count", type="integer", example=15)
     *                 )
     *             ),
     *             @OA\Property(property="top_venues", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="venue", type="string", example="Tomorrowland"),
     *                     @OA\Property(property="count", type="integer", example=3)
     *                 )
     *             ),
     *             @OA\Property(property="top_artists", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="artist", type="string", example="Martin Garrix"),
     *                     @OA\Property(property="image", type="string", nullable=true),
     *                     @OA\Property(property="count", type="integer", example=2)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    public function statsByUsername(string $username): JsonResponse
    {
        $user = User::where('username', $username)->firstOrFail();

        // Get followers (users that follow this user)
        $followers = User::select('users.id', 'users.name', 'users.username', 'users.profile_photo_path')
            ->join('followers', 'users.id', '=', 'followers.follower_id')
            ->where('followers.followed_id', $user->id)
            ->get();

        // Get following (users that this user follows)
        $following = User::select('users.id', 'users.name', 'users.username', 'users.profile_photo_path')
            ->join('followers', 'users.id', '=', 'followers.followed_id')
            ->where('followers.follower_id', $user->id)
            ->get();

        // Monthly concert attendance based on concert dates
        $monthlyAttendance = Checkin::where('user_id', $user->id)
            ->join('concerts', 'checkins.concert_id', '=', 'concerts.id')
            ->select(
                DB::raw('MONTH(concerts.date) as month_number'),
                DB::raw('DATE_FORMAT(concerts.date, "%b") as month'),
                DB::raw('COUNT(DISTINCT checkins.id) as count')
            )
            ->groupBy('month_number', 'month')
            ->orderBy('month_number')
            ->get();

        // Genre distribution
        $genreDistribution = Checkin::where('user_id', $user->id)
            ->join('artist_checkins', 'checkins.id', '=', 'artist_checkins.checkin_id')
            ->join('artists', 'artist_checkins.artist_id', '=', 'artists.id')
            ->join('artist_genres', 'artists.id', '=', 'artist_genres.artist_id')
            ->join('genres', 'artist_genres.genre_id', '=', 'genres.id')
            ->select('genres.genre as genre', DB::raw('COUNT(DISTINCT checkins.id) as count'))
            ->groupBy('genres.id', 'genres.genre')
            ->orderByDesc('count')
            ->get();

        // Top venues
        $topVenues = Checkin::where('user_id', $user->id)
            ->join('concerts', 'checkins.concert_id', '=', 'concerts.id')
            ->join('locations', 'concerts.location_id', '=', 'locations.id')
            ->select('locations.name as venue', DB::raw('COUNT(DISTINCT checkins.id) as count'))
            ->groupBy('locations.id', 'locations.name')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Top artists
        $topArtists = Checkin::where('user_id', $user->id)
            ->join('artist_checkins', 'checkins.id', '=', 'artist_checkins.checkin_id')
            ->join('artists', 'artist_checkins.artist_id', '=', 'artists.id')
            ->select(
                'artists.name as artist',
                'artists.image_url as image',
                DB::raw('COUNT(DISTINCT checkins.id) as count')
            )
            ->groupBy('artists.id', 'artists.name', 'artists.image_url')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return response()->json([
            'followers_count' => $followers->count(),
            'following_count' => $following->count(),
            'followers' => $followers,
            'following' => $following,
            'monthly_attendance' => $monthlyAttendance,
            'genre_distribution' => $genreDistribution,
            'top_venues' => $topVenues,
            'top_artists' => $topArtists
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/users/{username}/photos",
     *     summary="Get all photos for a specific user by username",
     *     description="Retrieve all check-in photos for a specific user, ordered by creation date (newest first)",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="username",
     *         in="path",
     *         required=true,
     *         description="User username",
     *         @OA\Schema(
     *             type="string",
     *             example="johndoe"
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
     *         description="Number of photos per page",
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
     *         description="User photos retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="url", type="string"),
     *                     @OA\Property(property="caption", type="string", nullable=true),
     *                     @OA\Property(property="checkin", type="object",
     *                         @OA\Property(property="id", type="string", format="uuid"),
     *                         @OA\Property(property="concert", type="object",
     *                             @OA\Property(property="date", type="string", format="date"),
     *                             @OA\Property(property="event", type="object",
     *                                 @OA\Property(property="name", type="string"),
     *                                 @OA\Property(property="type", type="string")
     *                             ),
     *                             @OA\Property(property="location", type="object",
     *                                 @OA\Property(property="name", type="string"),
     *                                 @OA\Property(property="city", type="string")
     *                             )
     *                         ),
     *                         @OA\Property(property="created_at", type="string", format="date-time")
     *                     ),
     *                     @OA\Property(property="created_at", type="string", format="date-time")
     *                 )
     *             ),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    public function photosByUsername(Request $request, string $username): JsonResponse
    {
        $user = User::where('username', $username)->firstOrFail();

        $photos = $user->checkins()
            ->join('checkin_photos', 'checkins.id', '=', 'checkin_photos.checkin_id')
            ->join('concerts', 'checkins.concert_id', '=', 'concerts.id')
            ->join('events', 'concerts.event_id', '=', 'events.id')
            ->join('locations', 'concerts.location_id', '=', 'locations.id')
            ->select(
                'checkin_photos.id',
                'checkin_photos.url',
                'checkin_photos.caption',
                'checkin_photos.created_at',
                'checkins.id as checkin_id',
                'checkins.created_at as checkin_created_at',
                'concerts.date',
                'events.name as event_name',
                'events.type as event_type',
                'locations.name as location_name',
                'locations.city'
            )
            ->orderBy('checkin_photos.created_at', 'desc')
            ->paginate($request->input('per_page', 20));

        $formattedPhotos = $photos->getCollection()->map(function ($photo) {
            return [
                'id' => $photo->id,
                'url' => $photo->url,
                'caption' => $photo->caption,
                'checkin' => [
                    'id' => $photo->checkin_id,
                    'concert' => [
                        'date' => $photo->date,
                        'event' => [
                            'name' => $photo->event_name,
                            'type' => $photo->event_type
                        ],
                        'location' => [
                            'name' => $photo->location_name,
                            'city' => $photo->city
                        ]
                    ],
                    'created_at' => $photo->checkin_created_at
                ],
                'created_at' => $photo->created_at
            ];
        });

        return response()->json([
            'data' => $formattedPhotos,
            'links' => $photos->links(),
            'meta' => [
                'current_page' => $photos->currentPage(),
                'from' => $photos->firstItem(),
                'last_page' => $photos->lastPage(),
                'per_page' => $photos->perPage(),
                'to' => $photos->lastItem(),
                'total' => $photos->total()
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/users/{username}/friends",
     *     summary="Get friends (followers and following) for a specific user by username",
     *     description="Retrieve both followers and users being followed by a specific user",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="username",
     *         in="path",
     *         required=true,
     *         description="User username",
     *         @OA\Schema(
     *             type="string",
     *             example="johndoe"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User friends retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="followers", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="username", type="string"),
     *                     @OA\Property(property="profile_photo_path", type="string", nullable=true),
     *                     @OA\Property(property="bio", type="string", nullable=true),
     *                     @OA\Property(property="city", type="string", nullable=true),
     *                     @OA\Property(property="country_name", type="string", nullable=true)
     *                 )
     *             ),
     *             @OA\Property(property="following", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="username", type="string"),
     *                     @OA\Property(property="profile_photo_path", type="string", nullable=true),
     *                     @OA\Property(property="bio", type="string", nullable=true),
     *                     @OA\Property(property="city", type="string", nullable=true),
     *                     @OA\Property(property="country_name", type="string", nullable=true)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    public function friendsByUsername(string $username): JsonResponse
    {
        $user = User::where('username', $username)->firstOrFail();

        // Get followers (users that follow this user)
        $followers = User::select(
            'users.id',
            'users.name',
            'users.username',
            'users.profile_photo_path',
            'users.bio',
            'users.city',
            'countries.name as country_name'
        )
            ->leftJoin('countries', 'users.country_id', '=', 'countries.id')
            ->join('followers', 'users.id', '=', 'followers.follower_id')
            ->where('followers.followed_id', $user->id)
            ->get();

        // Get following (users that this user follows)
        $following = User::select(
            'users.id',
            'users.name',
            'users.username',
            'users.profile_photo_path',
            'users.bio',
            'users.city',
            'countries.name as country_name'
        )
            ->leftJoin('countries', 'users.country_id', '=', 'countries.id')
            ->join('followers', 'users.id', '=', 'followers.followed_id')
            ->where('followers.follower_id', $user->id)
            ->get();

        return response()->json([
            'followers' => $followers,
            'following' => $following
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/users/{username}/follow",
     *     summary="Follow a user by username",
     *     description="Follow a specific user by their username",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="username",
     *         in="path",
     *         required=true,
     *         description="User username to follow",
     *         @OA\Schema(
     *             type="string",
     *             example="johndoe"
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
     *     @OA\Response(response=404, description="User not found"),
     *     @OA\Response(response=409, description="Already following this user"),
     *     @OA\Response(response=422, description="Cannot follow yourself")
     * )
     */
    public function followByUsername(string $username): JsonResponse
    {
        $userToFollow = User::where('username', $username)->firstOrFail();

        // Prevent self-following
        if ($userToFollow->id === Auth::id()) {
            return response()->json(['message' => 'You cannot follow yourself'], 422);
        }

        // Check if already following
        $existingFollow = Follower::where('follower_id', Auth::id())
            ->where('followed_id', $userToFollow->id)
            ->first();

        if ($existingFollow) {
            return response()->json(['message' => 'You are already following this user'], 409);
        }

        $follower = Follower::create([
            'follower_id' => Auth::id(),
            'followed_id' => $userToFollow->id
        ]);

        return response()->json([
            'message' => 'Successfully followed user',
            'follower' => $follower->load('followed:id,name,username')
        ], 201);
    }

    /**
     * @OA\Delete(
     *     path="/api/users/{username}/follow",
     *     summary="Unfollow a user by username",
     *     description="Unfollow a specific user by their username",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="username",
     *         in="path",
     *         required=true,
     *         description="User username to unfollow",
     *         @OA\Schema(
     *             type="string",
     *             example="johndoe"
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Successfully unfollowed user"
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Follow relationship not found")
     * )
     */
    public function unfollowByUsername(string $username): JsonResponse
    {
        $userToUnfollow = User::where('username', $username)->firstOrFail();

        $follow = Follower::where('follower_id', Auth::id())
            ->where('followed_id', $userToUnfollow->id)
            ->first();

        if (!$follow) {
            return response()->json(['message' => 'Follow relationship not found'], 404);
        }

        $follow->delete();
        return response()->json(null, 204);
    }

    /**
     * @OA\Get(
     *     path="/api/users/{username}/check-ins",
     *     summary="Get check-ins for a specific user by username",
     *     description="Retrieve a paginated list of check-ins for a specific user, ordered by creation date (newest first)",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="username",
     *         in="path",
     *         required=true,
     *         description="User username",
     *         @OA\Schema(
     *             type="string",
     *             example="johndoe"
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
     *                 @OA\Property(property="first", type="string", example="http://localhost/api/users/johndoe/check-ins?page=1"),
     *                 @OA\Property(property="last", type="string", example="http://localhost/api/users/johndoe/check-ins?page=3"),
     *                 @OA\Property(property="prev", type="string", nullable=true),
     *                 @OA\Property(property="next", type="string", example="http://localhost/api/users/johndoe/check-ins?page=2")
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
    public function checkinsByUsername(Request $request, string $username)
    {
        $user = User::where('username', $username)->firstOrFail();

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
