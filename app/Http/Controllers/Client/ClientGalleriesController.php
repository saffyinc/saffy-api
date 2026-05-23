<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ClientGalleriesController extends Controller
{
    public function index()
    {
        try {
            $categories = [
                [
                    'title' => 'Featured Products',
                    'query' => fn () => Gallery::where('isFeatured', 1)->where('isArchive', 0),
                    'path' => null,
                    'alwaysHideViewProducts' => true,
                ],
                [
                    'title' => 'Fashion',
                    'query' => fn () => Gallery::where('category', 'fashion')->where('isArchive', 0),
                    'path' => '/gallery/fashion',
                    'alwaysHideViewProducts' => false,
                ],
                [
                    'title' => 'Gift & Packaging',
                    'query' => fn () => Gallery::where('category', 'gifts')->where('isArchive', 0),
                    'path' => '/gallery/gifts',
                    'alwaysHideViewProducts' => false,
                ],
                [
                    'title' => 'Home & Garden',
                    'query' => fn () => Gallery::where('category', 'home')->where('isArchive', 0),
                    'path' => '/gallery/home',
                    'alwaysHideViewProducts' => false,
                ],
                [
                    'title' => 'Kitchen & Dining',
                    'query' => fn () => Gallery::where('category', 'kitchen')->where('isArchive', 0),
                    'path' => '/gallery/kitchen',
                    'alwaysHideViewProducts' => false,
                ],
                [
                    'title' => 'Stationaries & Desk Accessories',
                    'query' => fn () => Gallery::where('category', 'stationaries')->where('isArchive', 0),
                    'path' => '/gallery/stationaries',
                    'alwaysHideViewProducts' => false,
                ],
                [
                    'title' => 'Supported Communities',
                    'query' => fn () => Gallery::where('category', 'supported')->where('isArchive', 0),
                    'path' => '/gallery/supported',
                    'alwaysHideViewProducts' => false,
                ],
                [
                    'title' => 'Christmas & Holidays',
                    'query' => fn () => Gallery::where('category', 'christmas')->where('isArchive', 0),
                    'path' => '/gallery/christmas',
                    'alwaysHideViewProducts' => false,
                ],
                [
                    'title' => 'Toys & Games',
                    'query' => fn () => Gallery::where('category', 'toys')->where('isArchive', 0),
                    'path' => '/gallery/toys',
                    'alwaysHideViewProducts' => false,
                ],
            ];

            $data = collect($categories)->map(function ($category) {
                $products = $category['query']()
                    ->limit(10)
                    ->get()
                    ->map(function ($product) {
                        $galleryId = $product->getRawOriginal('id');

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

                        $product->img_url = Storage::url($product->img_path);
                        $product->media = $media;

                        return $product;
                    });

                return [
                    'title' => $category['title'],
                    'products' => $products,
                    'viewProducts' => $category['alwaysHideViewProducts']
                        ? false
                        : $products->count() >= 1,
                    ...($category['path'] ? ['path' => $category['path']] : []),
                ];
            })->values();

            return response()->json($data, 200);

        } catch (\Exception $exc) {
            return response()->json([
                'error' => $exc->getMessage()
            ], 400);
        }
    }


    public function getCategory($category)
    {

        $title = [
            'fashion' => 'Fashion',
            'gifts' => 'Gift & Packaging',
            'home' => 'Home & Garden',
            'kitchen' => 'Kitchen & Dining',
            'stationaries' => 'Stationaries & Desk Accessories',
            'supported' => 'Supported Communities',
            'christmas' => 'Christmas & Holidays',
            'toys' => 'Toys & Games',
        ];
        try {
            $gallery = Gallery::where('category', $category)
                ->where('isArchive', 0)
                ->orderBy('id', 'desc')
                ->get();

            $products = $gallery->map(function ($product) {
                $galleryId = $product->getRawOriginal('id');

                $media = DB::table('gallery_media')
                    ->where('gallery_id', $galleryId)
                    ->orderByDesc('is_thumbnail')
                    ->orderBy('id')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'media_path' => $item->media_path,
                            'media_type' => $item->media_type,
                            'is_thumbnail' => $item->is_thumbnail ?? 0,
                            'media_url' => Storage::url($item->media_path),
                        ];
                    });

                return [
                    'id' => $product->id,
                    'product_id' => $product->product_id,
                    'title' => $product->title,
                    'description' => $product->description,
                    'material' => $product->material,
                    'color' => $product->color,
                    'shape' => $product->shape,
                    'size' => $product->size,
                    'weight' => $product->weight,
                    'category' => $product->category,

                    // Same shape as your frontend sample: { title, image }
                    'image' => $product->img_path
                        ? Storage::url($product->img_path)
                        : null,

                    // Full uploaded gallery media
                    'media' => $media,
                ];
            });

            return response()->json([
                [
                    'title' => Str::headline($title[$category]),
                    'products' => $products,
                ]
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage(),
            ], 400);
        }
    }

}
