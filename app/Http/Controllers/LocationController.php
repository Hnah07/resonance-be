<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Source;
use App\Models\Status;
use App\Models\Country;
use App\Http\Resources\LocationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @OA\Schema(
 *     schema="Location",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000"),
 *     @OA\Property(property="name", type="string", example="Sportpaleis Antwerpen"),
 *     @OA\Property(property="street", type="string", nullable=true, example="Schijnpoortweg"),
 *     @OA\Property(property="house_number", type="string", nullable=true, example="119"),
 *     @OA\Property(property="city", type="string", nullable=true, example="Antwerpen"),
 *     @OA\Property(property="postal_code", type="string", nullable=true, example="2170"),
 *     @OA\Property(property="country", type="string", example="Belgium"),
 *     @OA\Property(property="latitude", type="number", format="float", nullable=true, example=51.2306),
 *     @OA\Property(property="longitude", type="number", format="float", nullable=true, example=4.4452),
 *     @OA\Property(property="website", type="string", nullable=true),
 *     @OA\Property(property="description", type="string", nullable=true),
 *     @OA\Property(property="image_url", type="string", nullable=true),
 *     @OA\Property(property="source", type="string", example="manual"),
 *     @OA\Property(property="status", type="string", example="verified"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * 
 * @OA\Tag(
 *     name="Locations",
 *     description="API Endpoints for managing locations"
 * )
 */
class LocationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/locations",
     *     summary="Get all locations",
     *     tags={"Locations"},
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Field to sort by",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"name", "city", "country", "created_at"},
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
     *         name="country",
     *         in="query",
     *         description="Filter by country",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="city",
     *         in="query",
     *         description="Filter by city",
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
     *         name="search",
     *         in="query",
     *         description="Search in name, city, and country",
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
     *         description="List of locations",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000"),
     *                     @OA\Property(property="name", type="string", example="Sportpaleis Antwerpen"),
     *                     @OA\Property(property="street", type="string", example="Schijnpoortweg 119"),
     *                     @OA\Property(property="city", type="string", example="Antwerpen"),
     *                     @OA\Property(property="postal_code", type="string", example="2170"),
     *                     @OA\Property(property="country", type="string", example="Belgium"),
     *                     @OA\Property(property="latitude", type="number", format="float", example=51.2306),
     *                     @OA\Property(property="longitude", type="number", format="float", example=4.4452),
     *                     @OA\Property(property="description", type="string", nullable=true),
     *                     @OA\Property(property="image_url", type="string", nullable=true),
     *                     @OA\Property(property="source", type="string", example="manual"),
     *                     @OA\Property(property="status", type="string", example="verified")
     *                 )
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
        $query = Location::with(['source', 'status']);

        // Apply filters
        if ($request->has('country')) {
            $query->where('country', $request->country);
        }

        if ($request->has('city')) {
            $query->where('city', 'like', "%{$request->city}%");
        }

        if ($request->has('status')) {
            $query->whereHas('status', function ($q) use ($request) {
                $q->where('status', $request->status);
            });
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('country', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        // Paginate results
        $perPage = min($request->get('per_page', 15), 100);
        $locations = $query->paginate($perPage);

        return LocationResource::collection($locations);
    }

    /**
     * @OA\Post(
     *     path="/api/locations",
     *     summary="Create a new location",
     *     tags={"Locations"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "country"},
     *             @OA\Property(property="name", type="string", example="Sportpaleis Antwerpen"),
     *             @OA\Property(property="street", type="string", nullable=true, example="Schijnpoortweg"),
     *             @OA\Property(property="house_number", type="string", nullable=true, example="119"),
     *             @OA\Property(property="city", type="string", nullable=true, example="Antwerpen"),
     *             @OA\Property(property="postal_code", type="string", nullable=true, example="2170"),
     *             @OA\Property(property="country", type="string", example="Belgium"),
     *             @OA\Property(property="latitude", type="number", format="float", nullable=true, example=51.2306),
     *             @OA\Property(property="longitude", type="number", format="float", nullable=true, example=4.4452),
     *             @OA\Property(property="website", type="string", nullable=true),
     *             @OA\Property(property="description", type="string", nullable=true),
     *             @OA\Property(property="image_url", type="string", nullable=true),
     *             @OA\Property(property="source", type="string", enum={"manual", "api"}, example="manual"),
     *             @OA\Property(property="status", type="string", enum={"pending_approval", "verified", "rejected"}, example="pending_approval")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Location created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Location")
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
            'street' => 'nullable|string|max:255',
            'house_number' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'required|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'website' => 'nullable|url',
            'description' => 'nullable|string',
            'image_url' => 'nullable|url',
            'source' => 'nullable|in:manual,api',
            'status' => 'nullable|in:pending_approval,verified,rejected'
        ]);

        // Handle country
        $country = Country::firstOrCreate(
            ['name' => $validated['country']],
            [
                'code' => strtoupper(substr($validated['country'], 0, 2)),
                'name' => $validated['country'],
                'official_name' => $validated['country'],
                'native_name' => $validated['country'],
                'continent' => 'Europe', // Default to Europe, can be updated later
                'subregion' => 'Western Europe', // Default to Western Europe, can be updated later
                'emoji' => '🏳️', // Default emoji, can be updated later
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude']
            ]
        );
        $validated['country_id'] = $country->id;
        unset($validated['country']);

        // Convert source and status strings to IDs
        if (isset($validated['source'])) {
            $validated['source_id'] = Source::where('source', $validated['source'])->first()->id;
            unset($validated['source']);
        }

        if (isset($validated['status'])) {
            $validated['status_id'] = Status::where('status', $validated['status'])->first()->id;
            unset($validated['status']);
        }

        $location = Location::create($validated);
        $location->load(['source', 'status', 'country']);
        return new LocationResource($location);
    }

    /**
     * @OA\Get(
     *     path="/api/locations/{id}",
     *     summary="Get a specific location",
     *     tags={"Locations"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Location UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Location details",
     *         @OA\JsonContent(ref="#/components/schemas/Location")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Location not found"
     *     )
     * )
     */
    public function show(Location $location)
    {
        $location->load(['source', 'status']);
        return new LocationResource($location);
    }

    /**
     * @OA\Put(
     *     path="/api/locations/{id}",
     *     summary="Update a location",
     *     tags={"Locations"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Location UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Sportpaleis Antwerpen"),
     *             @OA\Property(property="street", type="string", nullable=true, example="Schijnpoortweg"),
     *             @OA\Property(property="house_number", type="string", nullable=true, example="119"),
     *             @OA\Property(property="city", type="string", nullable=true, example="Antwerpen"),
     *             @OA\Property(property="postal_code", type="string", nullable=true, example="2170"),
     *             @OA\Property(property="country", type="string", example="Belgium"),
     *             @OA\Property(property="latitude", type="number", format="float", nullable=true, example=51.2306),
     *             @OA\Property(property="longitude", type="number", format="float", nullable=true, example=4.4452),
     *             @OA\Property(property="website", type="string", nullable=true),
     *             @OA\Property(property="description", type="string", nullable=true),
     *             @OA\Property(property="image_url", type="string", nullable=true),
     *             @OA\Property(property="source", type="string", enum={"manual", "api"}),
     *             @OA\Property(property="status", type="string", enum={"pending_approval", "verified", "rejected"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Location updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Location")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Location not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, Location $location)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'street' => 'nullable|string|max:255',
            'house_number' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'sometimes|required|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'website' => 'nullable|url',
            'description' => 'nullable|string',
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
                    'emoji' => '🏳️', // Default emoji, can be updated later
                    'latitude' => $validated['latitude'] ?? $location->latitude,
                    'longitude' => $validated['longitude'] ?? $location->longitude
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

        $location->update($validated);
        $location->load(['source', 'status', 'country']);
        return new LocationResource($location);
    }

    /**
     * @OA\Delete(
     *     path="/api/locations/{id}",
     *     summary="Delete a location",
     *     tags={"Locations"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Location UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Location deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Location not found"
     *     )
     * )
     */
    public function destroy(Location $location)
    {
        $location->delete();
        return response()->json(null, 204);
    }
}
