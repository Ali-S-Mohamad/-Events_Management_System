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
    public function list()
    {
        return Location::with('images','coverImage')->paginate(5);
    }

    public function show(Location $location)
    {
        return $location->load('images','coverImage');
    }

    public function create($request): Location
    {
        $location = Location::create($request->validated());
        $this->imageService->attachImage( $location, $request);
        $location->load('images');
        return $location;
    }

    public function update(Location $location, $request): Location
    {
        $location->update($request->validated());
        if ($request->hasFile('image')) {
            $this->imageService->updateImage($location, $request, 'Locations images');
        }
        $location->load('images');
        return $location;
    }

    public function delete(Location $location): void
    {
        $this->imageService->deleteImage($location->images);
        $location->delete();
    }
}
