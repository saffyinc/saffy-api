<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

class Stories extends Model
{
    //

    protected $fillable = [
        'type',
        'title',
        'author',
        'publish_date',
        'reading_time',
        'publication_image_path',
        'publication_video_path',
        'content',
        'isArchive'
    ];

    public function getIdAttribute($value){
        return Hashids::encode($value);
    }
}
