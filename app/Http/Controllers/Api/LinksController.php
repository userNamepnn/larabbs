<?php

namespace App\Http\Controllers\Api;

use App\Models\Link;
use App\Transformers\LinksTransformer;

class LinksController extends Controller
{
    public function index(Link $link)
    {
        $links = $link->getAllCachedLinks();
        return $this->response->collection($links, new LinksTransformer());
    }
}
