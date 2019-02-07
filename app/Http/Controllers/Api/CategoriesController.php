<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use App\Transformers\CategoryTransformer;

class CategoriesController extends Controller
{
    public function index(CategoryTransformer $transformer)
    {
        return $this->response->collection(Category::all(), $transformer);
    }
}
