<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Stories;
use Illuminate\Http\Request;

class ClientStoriesController extends Controller
{
    //

    public function index(){
        return Stories::orderBy('id', 'desc')->where('isArchive', 0)->get();
    }
}
