<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Gallery;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Storage;
use App\Models\GalleryMedia;

class AdminGalleriesController extends Controller
{
    //

    public function index($search)
    {
        $gallery = Gallery::where('product_id', $search)->first();

        if (!$gallery) {
            return response()->json([
                'message' => 'Gallery not found'
            ], 404);
        }

        $galleryId = $gallery->getRawOriginal('id');

        $media = DB::table('gallery_media')
            ->where('gallery_id', $galleryId)
            ->orderByDesc('is_thumbnail')
            ->orderBy('media_type')
            ->orderBy('id')
            ->get()
            ->map(function ($item) {
                $item->media_url = Storage::url($item->media_path);
                return $item;
            });

        $gallery->img_url = Storage::url($gallery->img_path);
        $gallery->media = $media;

        return response()->json([
            'item' => $gallery
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|string|unique:'.Gallery::class,
            'title' => 'required|string',
            'description' => 'nullable|string',
            'material' => 'string|nullable',
            'color' => 'string|nullable',
            'shape' => 'string|nullable',
            'size' => 'string|nullable',
            'weight' => 'string|nullable',
            'category' => 'required|in:featured,fashion,gifts,home,kitchen,stationaries,supported,christmas,toys',

            'media' => 'required|array',
            'media.*' => 'required|file|mimes:jpg,jpeg,png,webp,mp4,webm,mov',

            'thumbnail_index' => 'required|integer|min:0',
        ]);

        $mediaFiles = $request->file('media');
        $thumbnailIndex = (int) $request->thumbnail_index;

        if (!isset($mediaFiles[$thumbnailIndex])) {
            return response()->json([
                'error' => 'Invalid thumbnail selected.'
            ], 400);
        }

        if (!str_starts_with($mediaFiles[$thumbnailIndex]->getMimeType(), 'image/')) {
            return response()->json([
                'error' => 'Thumbnail must be an image.'
            ], 400);
        }

        $totalSize = collect($mediaFiles)->sum(function ($file) {
            return $file->getSize();
        });

        if ($totalSize > 50 * 1024 * 1024) {
            return response()->json([
                'error' => 'The total media size must not exceed 50MB.'
            ], 400);
        }

        DB::beginTransaction();

        try {
            $storedMedia = [];

            foreach ($mediaFiles as $index => $file) {
                $path = $file->store('Gallery', 'public');

                $storedMedia[$index] = [
                    'path' => $path,
                    'type' => str_starts_with($file->getMimeType(), 'video/') ? 'video' : 'image',
                ];
            }

            $gallery = Gallery::create([
                'product_id' => $request->product_id,
                'title' => $request->title,
                'description' => $request->description,
                'material' => $request->material,
                'color' => $request->color,
                'shape' => $request->shape,
                'size' => $request->size,
                'weight' => $request->weight,
                'category' => $request->category,

                // This is now the selected thumbnail image
                'img_path' => $storedMedia[$thumbnailIndex]['path'],
            ]);

            $galleryId = $gallery->getRawOriginal('id');

            foreach ($storedMedia as $index => $media) {
                GalleryMedia::create([
                    'gallery_id' => $galleryId ,
                    'media_path' => $media['path'],
                    'media_type' => $media['type'],
                    'is_thumbnail' => $index === $thumbnailIndex,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Product Added Successfully',
            ], 200);

        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'message' => $th->getMessage()
            ], 400);
        }
    }

    public function delete($id){


        $decodedId = Hashids::decode($id)[0] ?? null;
        $gallery = Gallery::find($decodedId);

        if (!$gallery) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }

        // Delete image from storage
        if ($gallery->img_path) {
            Storage::disk('public')->delete($gallery->img_path);
        }

        // Delete database record
        $gallery->delete();

        return response()->json([
            'message' => 'Product deleted successfully'
        ]);

    }

    public function feature($id){

        $decodedId = Hashids::decode($id)[0] ?? null;
        $gallery = Gallery::find($decodedId);

        if(!$gallery){
            return response()->json([
                'message' => 'Product not found'
            ]);
        }

        $gallery->update([
            'isFeatured' => 1
        ]);

        return response()->json([
            'message' => 'Product featured successfully'
        ]);

    }

    public function archive($id){
        $decodedId = Hashids::decode($id)[0] ?? null;
        $gallery = Gallery::find($decodedId);

        if(!$gallery){
            return response()->json([
                'message' => 'Product not found'
            ]);
        }

        $gallery->update([
            'isArchive' => 1
        ]);

        return response()->json([
            'message' => 'Product archived successfully'
        ]);
    }

    public function unarchive($id){
        $decodedId = Hashids::decode($id)[0] ?? null;
        $gallery = Gallery::find($decodedId);

        if(!$gallery){
            return response()->json([
                'message' => 'Product not found'
            ]);
        }

        $gallery->update([
            'isArchive' => 0
        ]);

        return response()->json([
            'message' => 'Product archived successfully'
        ]);
    }

    public function unfeature($id){

        $decodedId = Hashids::decode($id)[0] ?? null;
        $gallery = Gallery::find($decodedId);

        if(!$gallery){
            return response()->json([
                'message' => 'Product not found'
            ]);
        }

        $gallery->update([
            'isFeatured' => 0
        ]);

        return response()->json([
            'message' => 'Product unfeature successfully'
        ]);

    }

    public function validateGallery(Request $request)
    {
        $decodedId = Hashids::decode($request->id)[0] ?? null;

        $findGallery = Gallery::where('id', $decodedId)->first();

        if (!$findGallery) {
            return response()->json([
                'error' => 'Cannot find selected gallery'
            ], 404);
        }

        $galleryId = $findGallery->getRawOriginal('id');

        $media = DB::table('gallery_media')
            ->where('gallery_id', $galleryId)
            ->orderByDesc('is_thumbnail')
            ->orderBy('media_type')
            ->orderBy('id')
            ->get()
            ->map(function ($item) {
                $item->media_url = Storage::url($item->media_path);
                return $item;
            });

        $findGallery->img_url = Storage::url($findGallery->img_path);
        $findGallery->media = $media;

        return response()->json([
            'content' => $findGallery
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $decodedId = Hashids::decode($id)[0] ?? null;

        if (!$decodedId) {
            return response()->json([
                'message' => 'Invalid gallery ID'
            ], 400);
        }

        $gallery = Gallery::findOrFail($decodedId);
        $galleryId = $gallery->getRawOriginal('id');

        $request->validate([
            'color' => 'nullable|string',
            'category' => 'nullable|in:fashion,gifts,home,kitchen,stationaries,supported,christmas,toys',
            'description' => 'nullable|string',
            'material' => 'nullable|string',
            'product_id' => 'nullable|unique:galleries,product_id,' . $galleryId,
            'shape' => 'nullable|string',
            'size' => 'nullable|string',
            'title' => 'nullable|string',
            'weight' => 'nullable|string',

            'media' => 'nullable|array',
            'media.*' => 'required|file|mimes:jpg,jpeg,png,webp,mp4,webm,mov',
            'thumbnail_index' => 'nullable|integer|min:0',

            'removed_media_ids' => 'nullable|array',
            'removed_media_ids.*' => 'nullable|integer',

            'removed_media_paths' => 'nullable|array',
            'removed_media_paths.*' => 'nullable|string',

            'thumbnail_existing_id' => 'nullable|integer',
            'thumbnail_existing_path' => 'nullable|string',
        ]);

        $mediaFiles = $request->file('media', []);

        if (count($mediaFiles) > 0) {
            $totalSize = collect($mediaFiles)->sum(function ($file) {
                return $file->getSize();
            });

            if ($totalSize > 50 * 1024 * 1024) {
                return response()->json([
                    'error' => 'The total media size must not exceed 50MB.'
                ], 400);
            }

            if ($request->filled('thumbnail_index')) {
                $thumbnailIndex = (int) $request->input('thumbnail_index');

                if (!isset($mediaFiles[$thumbnailIndex])) {
                    return response()->json([
                        'error' => 'Invalid thumbnail selected.'
                    ], 400);
                }

                if (!str_starts_with($mediaFiles[$thumbnailIndex]->getMimeType(), 'image/')) {
                    return response()->json([
                        'error' => 'Thumbnail must be an image.'
                    ], 400);
                }
            }
        }

        DB::beginTransaction();

        $newStoredPaths = [];
        $oldPathsToDelete = [];

        try {
            $fields = [
                'color',
                'category',
                'description',
                'material',
                'product_id',
                'shape',
                'size',
                'title',
                'weight',
            ];

            foreach ($fields as $field) {
                if ($request->has($field)) {
                    $gallery->{$field} = $request->input($field);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Remove selected existing media only
            |--------------------------------------------------------------------------
            */
            $removedIds = array_filter($request->input('removed_media_ids', []));
            $removedPaths = array_filter($request->input('removed_media_paths', []));

            if (!empty($removedIds) || !empty($removedPaths)) {
                $mediaToRemove = GalleryMedia::where('gallery_id', $galleryId)
                    ->where(function ($query) use ($removedIds, $removedPaths) {
                        if (!empty($removedIds)) {
                            $query->whereIn('id', $removedIds);
                        }

                        if (!empty($removedPaths)) {
                            $query->orWhereIn('media_path', $removedPaths);
                        }
                    })
                    ->get();

                foreach ($mediaToRemove as $media) {
                    if ($media->media_path) {
                        $oldPathsToDelete[] = $media->media_path;
                    }

                    $media->delete();
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Append new media
            |--------------------------------------------------------------------------
            */
            $createdMedia = [];

            foreach ($mediaFiles as $index => $file) {
                $path = $file->store('Gallery', 'public');

                $newStoredPaths[] = $path;

                $createdMedia[$index] = GalleryMedia::create([
                    'gallery_id' => $galleryId,
                    'media_path' => $path,
                    'media_type' => str_starts_with($file->getMimeType(), 'video/')
                        ? 'video'
                        : 'image',
                    'is_thumbnail' => false,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Resolve selected thumbnail
            |--------------------------------------------------------------------------
            */
            $thumbnailMedia = null;

            if ($request->filled('thumbnail_existing_id')) {
                $thumbnailMedia = GalleryMedia::where('gallery_id', $galleryId)
                    ->where('id', $request->input('thumbnail_existing_id'))
                    ->first();
            }

            if (!$thumbnailMedia && $request->filled('thumbnail_existing_path')) {
                $thumbnailMedia = GalleryMedia::where('gallery_id', $galleryId)
                    ->where('media_path', $request->input('thumbnail_existing_path'))
                    ->first();
            }

            if (!$thumbnailMedia && $request->filled('thumbnail_index')) {
                $thumbnailIndex = (int) $request->input('thumbnail_index');
                $thumbnailMedia = $createdMedia[$thumbnailIndex] ?? null;
            }

            // Keep current thumbnail if still existing
            if (!$thumbnailMedia && $gallery->img_path) {
                $thumbnailMedia = GalleryMedia::where('gallery_id', $galleryId)
                    ->where('media_path', $gallery->img_path)
                    ->where('media_type', 'image')
                    ->first();
            }

            // Fallback to current marked thumbnail
            if (!$thumbnailMedia) {
                $thumbnailMedia = GalleryMedia::where('gallery_id', $galleryId)
                    ->where('is_thumbnail', true)
                    ->where('media_type', 'image')
                    ->first();
            }

            // Final fallback to first image
            if (!$thumbnailMedia) {
                $thumbnailMedia = GalleryMedia::where('gallery_id', $galleryId)
                    ->where('media_type', 'image')
                    ->first();
            }

            if (!$thumbnailMedia) {
                DB::rollBack();

                foreach ($newStoredPaths as $path) {
                    Storage::disk('public')->delete($path);
                }

                return response()->json([
                    'error' => 'At least one image is required for the gallery thumbnail.'
                ], 400);
            }

            if ($thumbnailMedia->media_type !== 'image') {
                DB::rollBack();

                foreach ($newStoredPaths as $path) {
                    Storage::disk('public')->delete($path);
                }

                return response()->json([
                    'error' => 'Thumbnail must be an image.'
                ], 400);
            }

            GalleryMedia::where('gallery_id', $galleryId)->update([
                'is_thumbnail' => false,
            ]);

            $thumbnailMedia->is_thumbnail = true;
            $thumbnailMedia->save();

            $gallery->img_path = $thumbnailMedia->media_path;
            $gallery->save();

            DB::commit();

            foreach ($oldPathsToDelete as $path) {
                Storage::disk('public')->delete($path);
            }

            return response()->json([
                'message' => 'Gallery updated successfully',
                'gallery' => $gallery->load('media'),
            ], 200);

        } catch (\Throwable $th) {
            DB::rollBack();

            foreach ($newStoredPaths as $path) {
                Storage::disk('public')->delete($path);
            }

            return response()->json([
                'message' => $th->getMessage(),
            ], 400);
        }
    }
}
