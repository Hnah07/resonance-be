<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use App\Http\Resources\GenreResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @OA\Schema(
 *     schema="Genre",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000"),
 *     @OA\Property(property="genre", type="string", example="Electronic Dance Music")
 * )
 * 
 * @OA\Schema(
 *     schema="GenreDetail",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/Genre"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(property="created_at", type="string", format="date-time"),
 *             @OA\Property(property="updated_at", type="string", format="date-time")
 *         )
 *     }
 * )
 * 
 * @OA\Tag(
 *     name="Genres",
 *     description="API Endpoints for managing music genres"
 * )
 */
class GenreController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/genres",
     *     summary="Get all genres",
     *     tags={"Genres"},
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Field to sort by",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"genre", "created_at"},
     *             default="genre"
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
     *         name="search",
     *         in="query",
     *         description="Search in genre name",
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
     *         description="List of genres",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(ref="#/components/schemas/Genre")
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
        $query = Genre::query();

        // Apply search filter
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('genre', 'like', "%{$search}%");
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'genre');
        $sortDirection = $request->get('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        // Paginate results
        $perPage = min($request->get('per_page', 15), 100);
        $genres = $query->paginate($perPage);

        return GenreResource::collection($genres);
    }

    /**
     * @OA\Post(
     *     path="/api/genres",
     *     summary="Create a new genre",
     *     tags={"Genres"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"genre"},
     *             @OA\Property(property="genre", type="string", example="Electronic Dance Music")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Genre created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Genre")
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
            'genre' => 'required|string|max:255|unique:genres,genre'
        ]);

        $genre = Genre::create($validated);
        return new GenreResource($genre);
    }

    /**
     * @OA\Get(
     *     path="/api/genres/{id}",
     *     summary="Get a specific genre",
     *     tags={"Genres"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Genre UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Genre details",
     *         @OA\JsonContent(ref="#/components/schemas/GenreDetail")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Genre not found"
     *     )
     * )
     */
    public function show(Genre $genre)
    {
        return new GenreResource($genre);
    }

    /**
     * @OA\Put(
     *     path="/api/genres/{id}",
     *     summary="Update a genre",
     *     tags={"Genres"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Genre UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"genre"},
     *             @OA\Property(property="genre", type="string", example="Electronic Dance Music")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Genre updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Genre")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Genre not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, Genre $genre)
    {
        $validated = $request->validate([
            'genre' => 'required|string|max:255|unique:genres,genre,' . $genre->id
        ]);

        $genre->update($validated);
        return new GenreResource($genre);
    }

    /**
     * @OA\Delete(
     *     path="/api/genres/{id}",
     *     summary="Delete a genre",
     *     tags={"Genres"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Genre UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Genre deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Genre not found"
     *     )
     * )
     */
    public function destroy(Genre $genre)
    {
        $genre->delete();
        return response()->json(null, 204);
    }
}
