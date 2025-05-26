<?php
namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\Models\Image;

class ImageService
{
    /**
     * Summary of upload
     * @param \Illuminate\Http\UploadedFile $file
     * @param mixed $folder
     * @param mixed $disk
     * @return bool|string
     */
    public function upload(UploadedFile $file, $folder, $disk = 'public'): string
    {
        $imageName = time() . '_' . $file->getClientOriginalName();
        return $file->storeAs($folder, $imageName, $disk);
    }

    /**
     * Summary of attachImage
     * @param mixed $model
     * @param mixed $request
     * @param mixed $disk
     */
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

    /**
     * Summary of setCoverImage
     * @param mixed $model
     * @param mixed $imageId
     */
    public function setCoverImage($model, $imageId)
    {
        $model->images()->where('is_cover', true)->update(['is_cover' => false]);
        
        $image = $model->images()->findOrFail($imageId);
        $image->update(['is_cover' => true]);
        
        return $image;
    }

    /**
     * Summary of updateImage
     * @param mixed $model
     * @param mixed $request
     * @param mixed $folder
     * @param mixed $disk
     * @return void
     */
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

    /**
     * Summary of deleteImage
     * @param mixed $images
     * @param mixed $disk
     * @return void
     */
    public function deleteImage($images, $disk = 'public')
    {
        foreach ($images as $image) {
            Storage::disk($disk)->delete($image->url);
            $image->delete();
        }
    }
}
