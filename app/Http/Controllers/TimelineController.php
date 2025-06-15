<?php

namespace App\Http\Controllers;

use App\Models\Checkin;
use App\Http\Resources\CheckinResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Annotations as OA;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(
 *     name="Timeline",
 *     description="API Endpoints for user timeline"
 * )
 */
class TimelineController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/timeline",
     *     summary="Get user's timeline",
     *     description="Returns a paginated list of check-ins from the authenticated user and users they follow",
     *     tags={"Timeline"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=15,
     *             minimum=1,
     *             maximum=100
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=1,
     *             minimum=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Timeline retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="user", type="object",
     *                         @OA\Property(property="id", type="string", format="uuid"),
     *                         @OA\Property(property="name", type="string"),
     *                         @OA\Property(property="username", type="string"),
     *                         @OA\Property(property="profile_photo_url", type="string", nullable=true)
     *                     ),
     *                     @OA\Property(property="concert", type="object",
     *                         @OA\Property(property="id", type="string", format="uuid"),
     *                         @OA\Property(property="date", type="string", format="date"),
     *                         @OA\Property(property="event", type="object",
     *                             @OA\Property(property="id", type="string", format="uuid"),
     *                             @OA\Property(property="name", type="string"),
     *                             @OA\Property(property="type", type="string"),
     *                             @OA\Property(property="image_url", type="string", nullable=true)
     *                         ),
     *                         @OA\Property(property="location", type="object",
     *                             @OA\Property(property="id", type="string", format="uuid"),
     *                             @OA\Property(property="name", type="string"),
     *                             @OA\Property(property="city", type="string"),
     *                             @OA\Property(property="country", type="string")
     *                         ),
     *                         @OA\Property(property="artists", type="array",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="id", type="string", format="uuid"),
     *                                 @OA\Property(property="name", type="string"),
     *                                 @OA\Property(property="image_url", type="string", nullable=true),
     *                                 @OA\Property(property="genres", type="array",
     *                                     @OA\Items(
     *                                         type="object",
     *                                         @OA\Property(property="id", type="string", format="uuid"),
     *                                         @OA\Property(property="name", type="string")
     *                                     )
     *                                 )
     *                             )
     *                         )
     *                     ),
     *                     @OA\Property(property="photos", type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="string", format="uuid"),
     *                             @OA\Property(property="url", type="string"),
     *                             @OA\Property(property="caption", type="string", nullable=true)
     *                         )
     *                     ),
     *                     @OA\Property(property="likes_count", type="integer"),
     *                     @OA\Property(property="comments_count", type="integer"),
     *                     @OA\Property(property="is_liked", type="boolean"),
     *                     @OA\Property(property="rating", type="number", format="float", nullable=true),
     *                     @OA\Property(property="review", type="string", nullable=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             ),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="from", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="to", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             ),
     *             @OA\Property(property="links", type="object",
     *                 @OA\Property(property="first", type="string"),
     *                 @OA\Property(property="last", type="string"),
     *                 @OA\Property(property="prev", type="string", nullable=true),
     *                 @OA\Property(property="next", type="string", nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        // Debug authentication state
        Log::info('Auth state:', [
            'isAuthenticated' => Auth::check(),
            'userId' => Auth::id(),
            'cookie' => $request->cookie(),
            'headers' => $request->headers->all()
        ]);

        $user = User::find(Auth::id());
        if (!$user) {
            Log::error('User not found for ID: ' . Auth::id());
            abort(401, 'User not found');
        }

        // Get IDs of users to include in timeline (self + following)
        $userIds = array_merge(
            [$user->id],
            $user->following()->pluck('users.id')->toArray()
        );

        // Debug: Log the user IDs we're searching for
        Log::info('Timeline user IDs:', ['userIds' => $userIds]);

        // Query check-ins with all necessary relationships
        $checkins = Checkin::with([
            'user:id,name,username,profile_photo_path',
            'concert:id,date,event_id,location_id',
            'concert.event:id,name,type,image_url',
            'concert.location:id,name,city,country_id',
            'concert.location.country:id,name',
            'artists:id,name,image_url',
            'artists.genres:id,genre',
            'photos:id,checkin_id,url,caption',
            'likes:id,checkin_id,user_id',
            'likedByUsers:id',
            'comments:id,checkin_id,user_id,comment,created_at',
            'comments.user:id,name,username,profile_photo_path',
            'rating:id,checkin_id,rating',
            'review:id,checkin_id,review'
        ])
            ->whereIn('user_id', $userIds)
            ->latest();

        // Debug: Log the SQL query and count
        Log::info('Timeline SQL:', ['sql' => $checkins->toSql(), 'bindings' => $checkins->getBindings()]);
        Log::info('Timeline count:', ['count' => $checkins->count()]);

        $checkins = $checkins->paginate(min($request->get('per_page', 15), 100));

        return CheckinResource::collection($checkins);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit()
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        //  
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy()
    {
        //
    }
}
