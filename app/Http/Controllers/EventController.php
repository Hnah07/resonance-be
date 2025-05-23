<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Source;
use App\Models\Status;
use App\Http\Resources\EventResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @OA\Schema(
 *     schema="Event",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000"),
 *     @OA\Property(property="name", type="string", example="Tomorrowland 2024"),
 *     @OA\Property(property="start_date", type="string", format="date", example="2024-07-19"),
 *     @OA\Property(property="end_date", type="string", format="date", example="2024-07-28"),
 *     @OA\Property(property="description", type="string", nullable=true, example="The world's biggest electronic dance music festival, featuring the best DJs from around the globe."),
 *     @OA\Property(property="type", type="string", enum={"concert", "festival", "tour", "clubnight", "other"}, example="festival"),
 *     @OA\Property(property="image_url", type="string", nullable=true, example="https://resonance-be.ddev.site/storage/events/tomorrowland-2024.jpg"),
 *     @OA\Property(property="source", type="string", example="manual"),
 *     @OA\Property(property="status", type="string", example="verified"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * 
 * @OA\Tag(
 *     name="Events",
 *     description="API Endpoints for managing events"
 * )
 */
class EventController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/events",
     *     summary="Get all events",
     *     tags={"Events"},
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Field to sort by",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"name", "start_date", "end_date", "type", "created_at"},
     *             default="start_date"
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
     *         name="type",
     *         in="query",
     *         description="Filter by event type",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"concert", "festival", "tour", "clubnight", "other"}
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="start_date_from",
     *         in="query",
     *         description="Filter events starting from this date",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             format="date",
     *             example="2024-01-01"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="start_date_to",
     *         in="query",
     *         description="Filter events starting until this date",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             format="date",
     *             example="2024-12-31"
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
     *         @OA\Schema(
     *             type="string",
     *             example="Tomorrowland"
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
     *         description="List of events",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000"),
     *                     @OA\Property(property="name", type="string", example="Tomorrowland 2024"),
     *                     @OA\Property(property="start_date", type="string", format="date", example="2024-07-19"),
     *                     @OA\Property(property="end_date", type="string", format="date", example="2024-07-28"),
     *                     @OA\Property(property="description", type="string", nullable=true, example="The world's biggest electronic dance music festival, featuring the best DJs from around the globe."),
     *                     @OA\Property(property="type", type="string", enum={"concert", "festival", "tour", "clubnight", "other"}, example="festival"),
     *                     @OA\Property(property="image_url", type="string", nullable=true, example="https://resonance-be.ddev.site/storage/events/tomorrowland-2024.jpg"),
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
     *                 @OA\Property(property="first", type="string", example="https://resonance-be.ddev.site/api/events?page=1"),
     *                 @OA\Property(property="last", type="string", example="https://resonance-be.ddev.site/api/events?page=5"),
     *                 @OA\Property(property="prev", type="string", nullable=true),
     *                 @OA\Property(property="next", type="string", example="https://resonance-be.ddev.site/api/events?page=2")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Event::with(['source', 'status']);

        // Apply filters
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('start_date_from')) {
            $query->where('start_date', '>=', $request->start_date_from);
        }

        if ($request->has('start_date_to')) {
            $query->where('start_date', '<=', $request->start_date_to);
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
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'start_date');
        $sortDirection = $request->get('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        // Paginate results
        $perPage = min($request->get('per_page', 15), 100);
        $events = $query->paginate($perPage);

        return EventResource::collection($events);
    }

    /**
     * @OA\Post(
     *     path="/api/events",
     *     summary="Create a new event",
     *     tags={"Events"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "start_date", "end_date", "type"},
     *             @OA\Property(property="name", type="string", example="Rock Werchter 2024"),
     *             @OA\Property(property="start_date", type="string", format="date", example="2024-06-27"),
     *             @OA\Property(property="end_date", type="string", format="date", example="2024-06-30"),
     *             @OA\Property(property="description", type="string", nullable=true, example="Belgium's biggest rock festival featuring international and local artists."),
     *             @OA\Property(property="type", type="string", enum={"concert", "festival", "tour", "clubnight", "other"}, example="festival"),
     *             @OA\Property(property="image_url", type="string", nullable=true, example="https://resonance-be.ddev.site/storage/events/rock-werchter-2024.jpg"),
     *             @OA\Property(property="source", type="string", enum={"manual", "api"}, example="manual"),
     *             @OA\Property(property="status", type="string", enum={"pending_approval", "verified", "rejected"}, example="pending_approval")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Event created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Event")
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
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'type' => 'required|in:concert,festival,tour,clubnight,other',
            'image_url' => 'nullable|url',
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

        $event = Event::create($validated);
        $event->load(['source', 'status']);
        return new EventResource($event);
    }

    /**
     * @OA\Get(
     *     path="/api/events/{id}",
     *     summary="Get a specific event",
     *     tags={"Events"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Event UUID",
     *         @OA\Schema(type="string", format="uuid"),
     *         example="123e4567-e89b-12d3-a456-426614174000"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Event details",
     *         @OA\JsonContent(ref="#/components/schemas/Event")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Event not found"
     *     )
     * )
     */
    public function show(Event $event)
    {
        $event->load(['source', 'status']);
        return new EventResource($event);
    }

    /**
     * @OA\Put(
     *     path="/api/events/{id}",
     *     summary="Update an event",
     *     tags={"Events"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Event UUID",
     *         @OA\Schema(type="string", format="uuid"),
     *         example="123e4567-e89b-12d3-a456-426614174000"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Tomorrowland 2024 - The Story of Planaxis"),
     *             @OA\Property(property="start_date", type="string", format="date", example="2024-07-19"),
     *             @OA\Property(property="end_date", type="string", format="date", example="2024-07-28"),
     *             @OA\Property(property="description", type="string", nullable=true, example="The world's biggest electronic dance music festival, featuring the best DJs from around the globe. This year's theme: The Story of Planaxis."),
     *             @OA\Property(property="type", type="string", enum={"concert", "festival", "tour", "clubnight", "other"}, example="festival"),
     *             @OA\Property(property="image_url", type="string", nullable=true, example="https://resonance-be.ddev.site/storage/events/tomorrowland-2024-planaxis.jpg"),
     *             @OA\Property(property="source", type="string", enum={"manual", "api"}, example="manual"),
     *             @OA\Property(property="status", type="string", enum={"pending_approval", "verified", "rejected"}, example="verified")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Event updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Event")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Event not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'type' => 'sometimes|required|in:concert,festival,tour,clubnight,other',
            'image_url' => 'nullable|url',
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

        $event->update($validated);
        $event->load(['source', 'status']);
        return new EventResource($event);
    }

    /**
     * @OA\Delete(
     *     path="/api/events/{id}",
     *     summary="Delete an event",
     *     tags={"Events"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Event UUID",
     *         @OA\Schema(type="string", format="uuid"),
     *         example="123e4567-e89b-12d3-a456-426614174000"
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Event deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Event not found"
     *     )
     * )
     */
    public function destroy(Event $event)
    {
        $event->delete();
        return response()->json(null, 204);
    }
}
