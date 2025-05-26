<?php

namespace App\Services;

use App\Services\ImageService;
use App\Models\Location;

class LocationService
{
    protected ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Summary of list
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function list()
    {
        return Location::with('images','coverImage')->paginate(5);
    }


    /**
     * Summary of show
     * @param \App\Models\Location $location
     * @return Location
     */
    public function show(Location $location)
    {
        return $location->load('images','coverImage');
    }

    /**
     * Summary of create
     * @param mixed $request
     * @return Location
     */
    public function create($request): Location
    {
        $location = Location::create($request->validated());
        $this->imageService->attachImage( $location, $request);
        $location->load('images');
        return $location;
    }

    /**
     * Summary of update
     * @param \App\Models\Location $location
     * @param mixed $request
     * @return Location
     */
    public function update(Location $location, $request): Location
    {
        $location->update($request->validated());
        if ($request->hasFile('image')) {
            $this->imageService->updateImage($location, $request, 'Locations images');
        }
        $location->load('images');
        return $location;
    }

    /**
     * Summary of delete
     * @param \App\Models\Location $location
     * @return void
     */
    public function delete(Location $location): void
    {
        $this->imageService->deleteImage($location->images);
        $location->delete();
    }
}
