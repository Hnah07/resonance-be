<?php

namespace App\Http\Controllers;

use App\Models\CheckinPhoto;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Checkin Photos",
 *     description="API Endpoints for managing checkin photos"
 * )
 */
class CheckinPhotoController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/checkin-photos",
     *     summary="Get all checkin photos",
     *     tags={"Checkin Photos"},
     *     @OA\Response(
     *         response=200,
     *         description="List of checkin photos",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="checkin_id", type="string", format="uuid"),
     *                 @OA\Property(property="url", type="string"),
     *                 @OA\Property(property="caption", type="string", nullable=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $photos = CheckinPhoto::all();
        return response()->json($photos);
    }

    /**
     * @OA\Post(
     *     path="/api/checkin-photos",
     *     summary="Create a new checkin photo",
     *     tags={"Checkin Photos"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"checkin_id", "url"},
     *             @OA\Property(property="checkin_id", type="string", format="uuid"),
     *             @OA\Property(property="url", type="string"),
     *             @OA\Property(property="caption", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Photo created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="checkin_id", type="string", format="uuid"),
     *                 @OA\Property(property="url", type="string"),
     *                 @OA\Property(property="caption", type="string", nullable=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'checkin_id' => 'required|uuid|exists:checkins,id',
            'url' => 'required|string',
            'caption' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $photo = CheckinPhoto::create($request->all());
        return response()->json([
            'message' => 'Photo created successfully',
            'data' => $photo
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/checkin-photos/{id}",
     *     summary="Get a specific checkin photo",
     *     tags={"Checkin Photos"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="UUID of the checkin photo",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Checkin photo details",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", format="uuid"),
     *             @OA\Property(property="checkin_id", type="string", format="uuid"),
     *             @OA\Property(property="url", type="string"),
     *             @OA\Property(property="caption", type="string", nullable=true),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Photo not found"
     *     )
     * )
     */
    public function show(CheckinPhoto $checkinPhoto): JsonResponse
    {
        return response()->json($checkinPhoto);
    }

    /**
     * @OA\Put(
     *     path="/api/checkin-photos/{id}",
     *     summary="Update a checkin photo",
     *     tags={"Checkin Photos"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="UUID of the checkin photo",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="url", type="string"),
     *             @OA\Property(property="caption", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Photo updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="checkin_id", type="string", format="uuid"),
     *                 @OA\Property(property="url", type="string"),
     *                 @OA\Property(property="caption", type="string", nullable=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Photo not found"
     *     )
     * )
     */
    public function update(Request $request, CheckinPhoto $checkinPhoto): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'url' => 'sometimes|required|url',
            'caption' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $checkinPhoto->update($request->all());
        return response()->json([
            'message' => 'Photo updated successfully',
            'data' => $checkinPhoto
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/checkin-photos/{id}",
     *     summary="Delete a checkin photo",
     *     tags={"Checkin Photos"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="UUID of the checkin photo",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Photo deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Photo not found"
     *     )
     * )
     */
    public function destroy(CheckinPhoto $checkinPhoto): JsonResponse
    {
        $checkinPhoto->delete();
        return response()->json(['message' => 'Photo deleted successfully']);
    }
}
