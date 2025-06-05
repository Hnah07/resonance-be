<?php

namespace App\Http\Controllers;

use App\Models\Concert;
use App\Models\Source;
use App\Models\Status;
use App\Http\Resources\ConcertResource;
use App\Http\Resources\ArtistResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

/**
 * @OA\Schema(
 *     schema="Concert",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000"),
 *     @OA\Property(property="event", type="object",
 *         @OA\Property(property="id", type="string", format="uuid"),
 *         @OA\Property(property="name", type="string", example="Tomorrowland 2024"),
 *         @OA\Property(property="type", type="string", enum={"concert", "festival", "tour", "clubnight", "other"})
 *     ),
 *     @OA\Property(property="location", type="object",
 *         @OA\Property(property="id", type="string", format="uuid"),
 *         @OA\Property(property="name", type="string", example="Sportpaleis Antwerpen"),
 *         @OA\Property(property="city", type="string", example="Antwerpen"),
 *         @OA\Property(property="country", type="string", example="Belgium")
 *     ),
 *     @OA\Property(property="date", type="string", format="date", example="2024-07-19"),
 *     @OA\Property(property="source", type="string", example="manual"),
 *     @OA\Property(property="status", type="string", example="verified"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * 
 * @OA\Tag(
 *     name="Concerts",
 *     description="API Endpoints for managing concerts"
 * )
 */
class ConcertController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/concerts",
     *     summary="Get all concerts",
     *     tags={"Concerts"},
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Field to sort by",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"date", "created_at"},
     *             default="date"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sort_direction",
     *         in="query",
     *         description="Sort direction",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"asc", "desc"},
     *             default="asc"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Filter concerts from this date",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             format="date",
     *             example="2024-01-01"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         description="Filter concerts until this date",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             format="date",
     *             example="2024-12-31"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="location",
     *         in="query",
     *         description="Filter by location name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="event",
     *         in="query",
     *         description="Filter by event name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"pending_approval", "verified", "rejected"}
     *         )
     *     ),
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
     *         description="List of concerts",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(ref="#/components/schemas/Concert")
     *             ),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="to", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=75)
     *             ),
     *             @OA\Property(property="links", type="object",
     *                 @OA\Property(property="first", type="string"),
     *                 @OA\Property(property="last", type="string"),
     *                 @OA\Property(property="prev", type="string", nullable=true),
     *                 @OA\Property(property="next", type="string")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Concert::with(['event', 'location', 'source', 'status', 'artists.genres']);

        // Apply filters
        if ($request->has('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        if ($request->has('location')) {
            $query->whereHas('location', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->location}%");
            });
        }

        if ($request->has('event')) {
            $query->whereHas('event', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->event}%");
            });
        }

        if ($request->has('status')) {
            $query->whereHas('status', function ($q) use ($request) {
                $q->where('status', $request->status);
            });
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'date');
        $sortDirection = $request->get('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        // Paginate results
        $perPage = min($request->get('per_page', 15), 100);
        $concerts = $query->paginate($perPage);

        return ConcertResource::collection($concerts);
    }

    /**
     * @OA\Post(
     *     path="/api/concerts",
     *     summary="Create a new concert",
     *     tags={"Concerts"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"event_id", "location_id", "date"},
     *             @OA\Property(property="event_id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000"),
     *             @OA\Property(property="location_id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000"),
     *             @OA\Property(property="date", type="string", format="date", example="2024-07-19"),
     *             @OA\Property(property="source", type="string", enum={"manual", "api"}, example="manual"),
     *             @OA\Property(property="status", type="string", enum={"pending_approval", "verified", "rejected"}, example="pending_approval")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Concert created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Concert")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_id' => 'required|uuid|exists:events,id',
            'location_id' => 'required|uuid|exists:locations,id',
            'date' => 'required|date',
            'source' => 'nullable|in:manual,api',
            'status' => 'nullable|in:pending_approval,verified,rejected'
        ]);

        // Convert source and status strings to IDs
        if (isset($validated['source'])) {
            $validated['source_id'] = Source::where('source', $validated['source'])->first()->id;
            unset($validated['source']);
        }

        if (isset($validated['status'])) {
            $validated['status_id'] = Status::where('status', $validated['status'])->first()->id;
            unset($validated['status']);
        }

        $concert = Concert::create($validated);
        $concert->load(['event', 'location', 'source', 'status']);
        return new ConcertResource($concert);
    }

    /**
     * @OA\Get(
     *     path="/api/concerts/{id}",
     *     summary="Get a specific concert",
     *     tags={"Concerts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Concert UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Concert details",
     *         @OA\JsonContent(ref="#/components/schemas/Concert")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Concert not found"
     *     )
     * )
     */
    public function show(Concert $concert)
    {
        $concert->load(['event', 'location', 'source', 'status']);
        return new ConcertResource($concert);
    }

    /**
     * @OA\Put(
     *     path="/api/concerts/{id}",
     *     summary="Update a concert",
     *     tags={"Concerts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Concert UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="event_id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000"),
     *             @OA\Property(property="location_id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000"),
     *             @OA\Property(property="date", type="string", format="date", example="2024-07-19"),
     *             @OA\Property(property="source", type="string", enum={"manual", "api"}, example="manual"),
     *             @OA\Property(property="status", type="string", enum={"pending_approval", "verified", "rejected"}, example="verified")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Concert updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Concert")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Concert not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, Concert $concert)
    {
        $validated = $request->validate([
            'event_id' => 'sometimes|required|uuid|exists:events,id',
            'location_id' => 'sometimes|required|uuid|exists:locations,id',
            'date' => 'sometimes|required|date',
            'source' => 'nullable|in:manual,api',
            'status' => 'nullable|in:pending_approval,verified,rejected'
        ]);

        // Convert source and status strings to IDs
        if (isset($validated['source'])) {
            $validated['source_id'] = Source::where('source', $validated['source'])->first()->id;
            unset($validated['source']);
        }

        if (isset($validated['status'])) {
            $validated['status_id'] = Status::where('status', $validated['status'])->first()->id;
            unset($validated['status']);
        }

        $concert->update($validated);
        $concert->load(['event', 'location', 'source', 'status']);
        return new ConcertResource($concert);
    }

    /**
     * @OA\Delete(
     *     path="/api/concerts/{id}",
     *     summary="Delete a concert",
     *     tags={"Concerts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Concert UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Concert deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Concert not found"
     *     )
     * )
     */
    public function destroy(Concert $concert)
    {
        $concert->delete();
        return response()->json(null, 204);
    }

    /**
     * @OA\Get(
     *     path="/api/concerts/{id}/artists",
     *     summary="Get all artists for a concert",
     *     tags={"Concerts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Concert UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of artists for the concert",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="description", type="string", nullable=true),
     *                     @OA\Property(property="country", type="string", nullable=true),
     *                     @OA\Property(property="formed_year", type="integer", nullable=true),
     *                     @OA\Property(property="image_url", type="string", nullable=true),
     *                     @OA\Property(property="source", type="string", nullable=true),
     *                     @OA\Property(property="status", type="string", nullable=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Concert not found"
     *     )
     * )
     */
    public function getArtists(Concert $concert)
    {
        $artists = $concert->artists()
            ->with(['country', 'source', 'status'])
            ->select('artists.*')  // Only select artist fields, not pivot fields
            ->get();
        return ArtistResource::collection($artists);
    }

    /**
     * @OA\Post(
     *     path="/api/concerts/{id}/artists",
     *     summary="Attach an artist to a concert",
     *     tags={"Concerts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Concert UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Arctic Monkeys")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Artist attached successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Artist attached successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="name", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Concert not found or artist not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or artist already attached"
     *     )
     * )
     */
    public function attachArtist(Request $request, Concert $concert)
    {
        $validated = $request->validate([
            'name' => 'required|string|exists:artists,name'
        ]);

        // Find artist by name
        $artist = \App\Models\Artist::where('name', $validated['name'])->firstOrFail();

        // Check if artist is already attached
        if ($concert->artists()->where('artist_id', $artist->id)->exists()) {
            return response()->json([
                'message' => 'Artist is already attached to this concert'
            ], 422);
        }

        // Generate a UUID for the pivot table
        $pivotId = (string) Str::uuid();

        // Attach with the generated UUID
        $concert->artists()->attach($artist->id, ['id' => $pivotId]);

        return response()->json([
            'message' => 'Artist attached successfully',
            'data' => $artist
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/concerts/{id}/artists/{artistId}",
     *     summary="Detach an artist from a concert",
     *     tags={"Concerts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Concert UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="artistId",
     *         in="path",
     *         required=true,
     *         description="Artist UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Artist detached successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Artist detached successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Concert or artist not found"
     *     )
     * )
     */
    public function detachArtist(Concert $concert, string $artistId)
    {
        // Check if artist is attached
        if (!$concert->artists()->where('artist_id', $artistId)->exists()) {
            return response()->json([
                'message' => 'Artist is not attached to this concert'
            ], 404);
        }

        $concert->artists()->detach($artistId);

        return response()->json([
            'message' => 'Artist detached successfully'
        ]);
    }
}
