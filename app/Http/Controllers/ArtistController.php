<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\Source;
use App\Models\Status;
use App\Models\Country;
use App\Http\Resources\ArtistResource;
use App\Http\Resources\ConcertResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @OA\Schema(
 *     schema="Artist",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000"),
 *     @OA\Property(property="name", type="string", example="David Guetta"),
 *     @OA\Property(property="description", type="string", nullable=true, example="French DJ and music producer known for electronic dance music."),
 *     @OA\Property(property="country", type="string", nullable=true, example="Belgium"),
 *     @OA\Property(property="formed_year", type="string", format="date", nullable=true, example="2002"),
 *     @OA\Property(property="image_url", type="string", nullable=true, example="https://resonance-be.ddev.site/storage/artists/david-guetta.jpg"),
 *     @OA\Property(property="source", type="string", example="manual"),
 *     @OA\Property(property="status", type="string", example="verified"),
 *     @OA\Property(property="genres", type="array", @OA\Items(
 *         type="object",
 *         @OA\Property(property="id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000"),
 *         @OA\Property(property="name", type="string", example="Electronic Dance Music")
 *     )),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * 
 * @OA\Tag(
 *     name="Artists",
 *     description="API Endpoints for managing artists"
 * )
 */
class ArtistController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/artists",
     *     summary="Get all artists",
     *     tags={"Artists"},
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Field to sort by",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"name", "created_at"},
     *             default="name"
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
     *         name="search",
     *         in="query",
     *         description="Search in name and description",
     *         required=false,
     *         @OA\Schema(type="string")
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
     *         description="List of artists",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(ref="#/components/schemas/Artist")
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
        $query = Artist::with(['source', 'status']);

        // Apply filters
        if ($request->has('status')) {
            $query->whereHas('status', function ($q) use ($request) {
                $q->where('status', $request->status);
            });
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        // Paginate results
        $perPage = min($request->get('per_page', 15), 100);
        $artists = $query->paginate($perPage);

        return ArtistResource::collection($artists);
    }

    /**
     * @OA\Post(
     *     path="/api/artists",
     *     summary="Create a new artist",
     *     tags={"Artists"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="David Guetta"),
     *             @OA\Property(property="description", type="string", nullable=true, example="French DJ and music producer known for electronic dance music."),
     *             @OA\Property(property="country", type="string", nullable=true, example="Belgium"),
     *             @OA\Property(property="formed_year", type="string", format="date", nullable=true, example="2002"),
     *             @OA\Property(property="image_url", type="string", nullable=true, example="https://resonance-be.ddev.site/storage/artists/david-guetta.jpg"),
     *             @OA\Property(property="source", type="string", enum={"manual", "api"}, example="manual"),
     *             @OA\Property(property="status", type="string", enum={"pending_approval", "verified", "rejected"}, example="pending_approval")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Artist created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Artist")
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'country' => 'nullable|string|max:255',
            'formed_year' => 'nullable|date_format:Y',
            'image_url' => 'nullable|url',
            'source' => 'nullable|in:manual,api',
            'status' => 'nullable|in:pending_approval,verified,rejected'
        ]);

        // Handle country if provided
        if (isset($validated['country'])) {
            $country = Country::firstOrCreate(
                ['name' => $validated['country']],
                [
                    'code' => strtoupper(substr($validated['country'], 0, 2)),
                    'name' => $validated['country'],
                    'official_name' => $validated['country'],
                    'native_name' => $validated['country'],
                    'continent' => 'Europe', // Default to Europe, can be updated later
                    'subregion' => 'Western Europe', // Default to Western Europe, can be updated later
                    'emoji' => '🏳️' // Default emoji, can be updated later
                ]
            );
            $validated['country_id'] = $country->id;
            unset($validated['country']);
        }

        // Convert source and status strings to IDs
        if (isset($validated['source'])) {
            $validated['source_id'] = Source::where('source', $validated['source'])->first()->id;
            unset($validated['source']);
        }

        if (isset($validated['status'])) {
            $validated['status_id'] = Status::where('status', $validated['status'])->first()->id;
            unset($validated['status']);
        }

        $artist = Artist::create($validated);
        $artist->load(['source', 'status', 'country']);
        return new ArtistResource($artist);
    }

    /**
     * @OA\Get(
     *     path="/api/artists/{id}",
     *     summary="Get a specific artist",
     *     tags={"Artists"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Artist UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Artist details",
     *         @OA\JsonContent(ref="#/components/schemas/Artist")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Artist not found"
     *     )
     * )
     */
    public function show(Artist $artist)
    {
        $artist->load(['source', 'status']);
        return new ArtistResource($artist);
    }

    /**
     * @OA\Put(
     *     path="/api/artists/{id}",
     *     summary="Update an artist",
     *     tags={"Artists"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Artist UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="David Guetta"),
     *             @OA\Property(property="description", type="string", nullable=true, example="French DJ and music producer known for electronic dance music."),
     *             @OA\Property(property="country", type="string", nullable=true, example="Belgium"),
     *             @OA\Property(property="formed_year", type="string", format="date", nullable=true, example="2002"),
     *             @OA\Property(property="image_url", type="string", nullable=true, example="https://resonance-be.ddev.site/storage/artists/david-guetta.jpg"),
     *             @OA\Property(property="source", type="string", enum={"manual", "api"}, example="manual"),
     *             @OA\Property(property="status", type="string", enum={"pending_approval", "verified", "rejected"}, example="verified")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Artist updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Artist")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Artist not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, Artist $artist)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'country' => 'nullable|string|max:255',
            'formed_year' => 'nullable|date_format:Y',
            'image_url' => 'nullable|url',
            'source' => 'nullable|in:manual,api',
            'status' => 'nullable|in:pending_approval,verified,rejected'
        ]);

        // Handle country if provided
        if (isset($validated['country'])) {
            $country = Country::firstOrCreate(
                ['name' => $validated['country']],
                [
                    'code' => strtoupper(substr($validated['country'], 0, 2)),
                    'name' => $validated['country'],
                    'official_name' => $validated['country'],
                    'native_name' => $validated['country'],
                    'continent' => 'Europe', // Default to Europe, can be updated later
                    'subregion' => 'Western Europe', // Default to Western Europe, can be updated later
                    'emoji' => '🏳️' // Default emoji, can be updated later
                ]
            );
            $validated['country_id'] = $country->id;
            unset($validated['country']);
        }

        // Convert source and status strings to IDs
        if (isset($validated['source'])) {
            $validated['source_id'] = Source::where('source', $validated['source'])->first()->id;
            unset($validated['source']);
        }

        if (isset($validated['status'])) {
            $validated['status_id'] = Status::where('status', $validated['status'])->first()->id;
            unset($validated['status']);
        }

        $artist->update($validated);
        $artist->load(['source', 'status', 'country']);
        return new ArtistResource($artist);
    }

    /**
     * @OA\Delete(
     *     path="/api/artists/{id}",
     *     summary="Delete an artist",
     *     tags={"Artists"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Artist UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Artist deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Artist not found"
     *     )
     * )
     */
    public function destroy(Artist $artist)
    {
        $artist->delete();
        return response()->json(null, 204);
    }

    /**
     * @OA\Get(
     *     path="/api/artists/{id}/concerts",
     *     summary="Get all concerts for an artist",
     *     tags={"Artists"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Artist UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
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
     *         description="List of concerts for the artist",
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
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Artist not found"
     *     )
     * )
     */
    public function getConcerts(Request $request, Artist $artist): AnonymousResourceCollection
    {
        $query = $artist->concerts()->with(['event', 'location', 'source', 'status']);

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
     *     path="/api/artists/{id}/concerts",
     *     summary="Attach an artist to a concert",
     *     tags={"Artists"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Artist UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"concert_id"},
     *             @OA\Property(property="concert_id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Artist attached to concert successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Artist attached to concert successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Concert")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Artist or concert not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or artist already attached"
     *     )
     * )
     */
    public function attachConcert(Request $request, Artist $artist)
    {
        $validated = $request->validate([
            'concert_id' => 'required|uuid|exists:concerts,id'
        ]);

        // Check if artist is already attached
        if ($artist->concerts()->where('concert_id', $validated['concert_id'])->exists()) {
            return response()->json([
                'message' => 'Artist is already attached to this concert'
            ], 422);
        }

        // Generate a UUID for the pivot table
        $pivotId = (string) \Illuminate\Support\Str::uuid();

        // Attach with the generated UUID
        $artist->concerts()->attach($validated['concert_id'], ['id' => $pivotId]);

        $concert = $artist->concerts()->where('concert_id', $validated['concert_id'])->first();
        $concert->load(['event', 'location', 'source', 'status']);

        return response()->json([
            'message' => 'Artist attached to concert successfully',
            'data' => new ConcertResource($concert)
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/artists/{id}/concerts/{concertId}",
     *     summary="Detach an artist from a concert",
     *     tags={"Artists"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Artist UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="concertId",
     *         in="path",
     *         required=true,
     *         description="Concert UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Artist detached from concert successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Artist detached from concert successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Artist or concert not found"
     *     )
     * )
     */
    public function detachConcert(Artist $artist, string $concertId)
    {
        // Check if artist is attached
        if (!$artist->concerts()->where('concert_id', $concertId)->exists()) {
            return response()->json([
                'message' => 'Artist is not attached to this concert'
            ], 404);
        }

        $artist->concerts()->detach($concertId);

        return response()->json([
            'message' => 'Artist detached from concert successfully'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/artists/{id}/genres",
     *     summary="Attach genres to an artist",
     *     tags={"Artists"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Artist UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"genre_ids"},
     *             @OA\Property(
     *                 property="genre_ids",
     *                 type="array",
     *                 description="Array of genre UUIDs to attach",
     *                 @OA\Items(
     *                     type="string",
     *                     format="uuid",
     *                     example="123e4567-e89b-12d3-a456-426614174000"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Genres attached successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Artist")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Artist not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function attachGenres(Request $request, Artist $artist)
    {
        $validated = $request->validate([
            'genre_ids' => 'required|array',
            'genre_ids.*' => 'required|uuid|exists:genres,id'
        ]);

        // Generate UUIDs for pivot table entries
        $pivotData = collect($validated['genre_ids'])->mapWithKeys(function ($genreId) {
            return [$genreId => ['id' => (string) \Illuminate\Support\Str::uuid()]];
        })->all();

        $artist->genres()->attach($pivotData);
        $artist->load('genres');

        return new ArtistResource($artist);
    }

    /**
     * @OA\Delete(
     *     path="/api/artists/{id}/genres/{genreId}",
     *     summary="Detach a genre from an artist",
     *     tags={"Artists"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Artist UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="genreId",
     *         in="path",
     *         required=true,
     *         description="Genre UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Genre detached successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Artist")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Artist or genre not found"
     *     )
     * )
     */
    public function detachGenre(Artist $artist, string $genreId)
    {
        if (!$artist->genres()->where('genre_id', $genreId)->exists()) {
            return response()->json([
                'message' => 'Artist does not have this genre'
            ], 404);
        }

        $artist->genres()->detach($genreId);
        $artist->load('genres');

        return new ArtistResource($artist);
    }

    /**
     * @OA\Get(
     *     path="/api/artists/{id}/genres",
     *     summary="Get all genres for an artist",
     *     tags={"Artists"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Artist UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of genres for the artist",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="name", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Artist not found"
     *     )
     * )
     */
    public function getGenres(Artist $artist)
    {
        return response()->json([
            'data' => $artist->genres->map(fn($genre) => [
                'id' => $genre->id,
                'name' => $genre->genre,
            ])
        ]);
    }
}
