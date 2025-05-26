<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Location;
use Illuminate\Http\Request;
use App\Services\LocationService;
use App\Http\Controllers\Controller;
use App\Http\Resources\LocationResource;
use App\Http\Resources\LocationCollection;
use App\Http\Requests\Location\StoreLocationRequest;
use App\Http\Requests\Location\UpdateLocationRequest;

class LocationController extends Controller
{

    protected LocationService $locationService;
    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    /**
     * Summary of middleware
     * @return string[]
     */
    public static function middleware(): array
    {
        return [
            'auth',
            'role:admin'
        ];
    }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // return response()->json($this->locationService->list());
        return $this->apiResponse(new LocationCollection($this->locationService->list()), 'All Locations', 200);
    }

    /**
     * Store a newly created resource in storage.
     * @param \App\Http\Requests\Location\StoreLocationRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreLocationRequest $request)
    {
        $location = $this->locationService->create($request);
        if ($location) {
            return $this->successResponse(new LocationResource($location),'Location added successfully', 200);
        } else {
            return $this->errorResponse('Not allowed..', 404);
        }
    }

    /**
     * Display the specified resource.
     * @param \App\Models\Location $location
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Location $location)
    {
        $location = $this->locationService->show($location);
        return $this->apiResponse(new LocationResource($location), 'The Location: ', 200);
    }

    /**
     * Update the specified resource in storage.
     * @param \App\Http\Requests\Location\UpdateLocationRequest $request
     * @param \App\Models\Location $location
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateLocationRequest $request, Location $location)
    {
        $location = $this->locationService->update($location, $request);
        if ($location) {
            return $this->apiResponse(new LocationResource($location), 'Location updated successfully', 200);

        }else {
            return $this->errorResponse('Not allowed..', 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param \App\Models\Location $location
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Location $location)
    {
        $this->locationService->delete($location);
        return $this->apiResponse([], 'Rating deleted successfully', 200);
    }
}
