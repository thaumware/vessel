<?php

namespace App\Locations\Infrastructure\In\Http\Controllers;

use App\Locations\Application\UseCases\CreateLocation;
use App\Locations\Application\UseCases\DeleteLocation;
use App\Locations\Application\UseCases\GetLocation;
use App\Locations\Application\UseCases\ListLocations;
use App\Locations\Application\UseCases\UpdateLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Thaumware\Support\Uuid\Uuid;

class LocationsController extends Controller
{
    public function create(
        Request $request,
        CreateLocation $createLocation
    ): JsonResponse {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'address_id' => 'required|string',
            'type' => 'required|string|in:warehouse,store,office,distribution_center',
            'description' => 'nullable|string'
        ]);

        try {
            $location = $createLocation->execute(Uuid::v4(), $data);

            return response()->json([
                'data' => $location->toArray()
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function list(
        ListLocations $listLocations
    ): JsonResponse {
        $locations = $listLocations->execute();

        return response()->json([
            'data' => array_map(fn($location) => $location->toArray(), $locations)
        ]);
    }

    public function show(
        string $id,
        GetLocation $getLocation
    ): JsonResponse {
        $location = $getLocation->execute($id);

        if (!$location) {
            return response()->json([
                'error' => 'Location not found'
            ], 404);
        }

        return response()->json([
            'data' => $location->toArray()
        ]);
    }

    public function update(
        Request $request,
        string $id,
        UpdateLocation $updateLocation
    ): JsonResponse {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'address_id' => 'required|string',
            'type' => 'required|string|in:warehouse,store,office,distribution_center',
            'description' => 'nullable|string'
        ]);

        try {
            $location = $updateLocation->execute($id, $data);

            if (!$location) {
                return response()->json([
                    'error' => 'Location not found'
                ], 404);
            }

            return response()->json([
                'data' => $location->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function delete(
        string $id,
        DeleteLocation $deleteLocation
    ): JsonResponse {
        try {
            $result = $deleteLocation->execute($id);

            if (!$result) {
                return response()->json([
                    'error' => 'Location not found'
                ], 404);
            }

            return response()->json([
                'message' => 'Location deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }
}