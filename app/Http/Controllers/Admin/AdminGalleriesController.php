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
            'product_id' => 'required|integer|unique:'.Gallery::class,
            'title' => 'required|string',
            'description' => 'required|string',
            'material' => 'required|string',
            'color' => 'required|string',
            'shape' => 'required|string',
            'size' => 'required|string',
            'weight' => 'required|string',
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

        $request->validate([
            'color' => 'nullable|string',
            'category' => 'nullable|in:fashion,gifts,home,kitchen,stationaries,supported,christmas,toys',
            'description' => 'nullable|string',
            'material' => 'nullable|string',
            'product_id' => 'nullable|integer|unique:galleries,product_id,' . $gallery->getRawOriginal('id'),
            'shape' => 'nullable|string',
            'size' => 'nullable|string',
            'title' => 'nullable|string',
            'weight' => 'nullable|string',

            'media' => 'nullable|array',
            'media.*' => 'required|file|mimes:jpg,jpeg,png,webp,mp4,webm,mov',
            'thumbnail_index' => 'nullable|integer|min:0',
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

            $thumbnailIndex = (int) $request->input('thumbnail_index', 0);

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

        DB::beginTransaction();

        try {
            if ($request->filled('color')) {
                $gallery->color = $request->color;
            }

            if ($request->filled('category')) {
                $gallery->category = $request->category;
            }

            if ($request->filled('description')) {
                $gallery->description = $request->description;
            }

            if ($request->filled('material')) {
                $gallery->material = $request->material;
            }

            if ($request->filled('product_id')) {
                $gallery->product_id = $request->product_id;
            }

            if ($request->filled('shape')) {
                $gallery->shape = $request->shape;
            }

            if ($request->filled('size')) {
                $gallery->size = $request->size;
            }

            if ($request->filled('title')) {
                $gallery->title = $request->title;
            }

            if ($request->filled('weight')) {
                $gallery->weight = $request->weight;
            }

            if (count($mediaFiles) > 0) {
                $galleryId = $gallery->getRawOriginal('id');

                $oldMedia = DB::table('gallery_media')
                    ->where('gallery_id', $galleryId)
                    ->get();

                foreach ($oldMedia as $oldItem) {
                    if ($oldItem->media_path) {
                        Storage::disk('public')->delete($oldItem->media_path);
                    }
                }

                DB::table('gallery_media')
                    ->where('gallery_id', $galleryId)
                    ->delete();

                $storedMedia = [];
                $thumbnailIndex = (int) $request->input('thumbnail_index', 0);

                foreach ($mediaFiles as $index => $file) {
                    $path = $file->store('Gallery', 'public');

                    $storedMedia[$index] = [
                        'path' => $path,
                        'type' => str_starts_with($file->getMimeType(), 'video/') ? 'video' : 'image',
                    ];
                }

                if ($gallery->img_path) {
                    Storage::disk('public')->delete($gallery->img_path);
                }

                $gallery->img_path = $storedMedia[$thumbnailIndex]['path'];

                foreach ($storedMedia as $index => $media) {
                    GalleryMedia::create([
                        'gallery_id' => $galleryId,
                        'media_path' => $media['path'],
                        'media_type' => $media['type'],
                        'is_thumbnail' => $index === $thumbnailIndex,
                    ]);
                }
            }

            $gallery->save();

            DB::commit();

            return response()->json([
                'message' => 'Gallery updated successfully',
                'gallery' => $gallery,
            ], 200);

        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'message' => $th->getMessage(),
            ], 400);
        }
    }
}
