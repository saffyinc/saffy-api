<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminStoriesController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Client\ClientStoriesController;
use App\Http\Controllers\Client\ContactUsController;
use App\Http\Controllers\Admin\AdminGalleriesController;
use App\Http\Controllers\Client\ClientGalleriesController;



Route::middleware(['auth:sanctum'])->group(function(){

    Route::get('/user', function(Request $request){
        return $request->user();
    });


    // Stories ADMIN
    Route::post('/admin/stories/add', [AdminStoriesController::class, 'addStories']);
    Route::get('/admin/stories', [AdminStoriesController::class, 'index']);
    Route::delete('/admin/stories/{id}', [AdminStoriesController::class, 'destroy']);
    Route::post('/admin/archive-stories/{id}', [AdminStoriesController::class, 'archive']);
    Route::post('/admin/unarchive-stories/{id}', [AdminStoriesController::class, 'unarchive']);
    Route::post('/admin/stories/{id}', [AdminStoriesController::class, 'update']);
    Route::put('/admin/update-user', [AuthenticatedSessionController::class, 'updatePassword']);

    // Gallery Admin
    Route::post('/admin/galleries/add', [AdminGalleriesController::class, 'store']);
    Route::get('/admin/galleries/{search}', [AdminGalleriesController::class, 'index']);
    Route::delete('/admin/galleries/delete/{id}', [AdminGalleriesController::class, 'delete']);
    Route::put('/admin/galleries/feature/{id}', [AdminGalleriesController::class, 'feature']);
    Route::put('/admin/galleries/unfeature/{id}', [AdminGalleriesController::class, 'unfeature']);
    Route::post('/admin/galleries/{id}', [AdminGalleriesController::class, 'update']);
    Route::put('/admin/archive-galleries/{id}', [AdminGalleriesController::class, 'archive']);
    Route::put('/admin/unarchive-galleries/{id}', [AdminGalleriesController::class, 'unarchive']);



});

// Client
Route::get('/client/stories', [ClientStoriesController::class, 'index']);
Route::get('/validate-story', [AdminStoriesController::class, 'validateStory']);
Route::get('/validate-gallery', [AdminGalleriesController::class, 'validateGallery']);
Route::post('/contact-us', [ContactUsController::class, 'submitContactForm']);
Route::get('/client/gallery', [ClientGalleriesController::class, 'index']);
Route::get('/client/gallery/{category}', [ClientGalleriesController::class, 'getCategory']);



Route::get('/test', function(){
    return "hello test only";
});
