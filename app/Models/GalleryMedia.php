<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Gallery;

class GalleryMedia extends Model
{
    //

    protected $fillable = [
        'gallery_id',
        'media_path',
        'media_type',
        'is_thumbnail'
    ];


}
