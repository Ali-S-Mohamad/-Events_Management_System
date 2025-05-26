<?php
namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\Models\Image;

class ImageService
{
    public function upload(UploadedFile $file, $folder, $disk = 'public'): string
    {
        $imageName = time() . '_' . $file->getClientOriginalName();
        return $file->storeAs($folder, $imageName, $disk);
    }

    public function attachImage($model, $request, $disk = 'public')
    {
        if (!$request->hasFile('image') || !$request->file('image')->isValid()) {
            return;
        }
        $file = $request->file('image');
        $folder = 'Locations images';
        $path = $this->upload($file, $folder, $disk);

        $isCover = $request->input('is_cover', false);
        
        if ($isCover) {
            $model->images()->where('is_cover', true)->update(['is_cover' => false]);
        }

        return $model->images()->create([
            'url' => $path,
            'is_cover' => $isCover,
        ]);
    }

    public function setCoverImage($model, $imageId)
    {
        $model->images()->where('is_cover', true)->update(['is_cover' => false]);
        
        $image = $model->images()->findOrFail($imageId);
        $image->update(['is_cover' => true]);
        
        return $image;
    }

    public function updateImage($model, $request, $folder, $disk = 'public')
    {
        $image = null;
        $imageId = null;
        if(isset($request->image_id)){
            $imageId = $request->input('image_id');
            $image = $model->images()->findOrFail($imageId);
        }

        $file = $request->file('image');
        if($image){
            
            Storage::disk($disk)->delete($image->url);
            $path = $this->upload($file, $folder, $disk);
            $image->update(['url' => $path]);
        } else{
            $this->attachImage( $model, $request);
        }
    }

    public function deleteImage($images, $disk = 'public')
    {
        foreach ($images as $image) {
            Storage::disk($disk)->delete($image->url);
            $image->delete();
        }
    }
}
