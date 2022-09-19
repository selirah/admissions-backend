<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    private $_category;

    public function __construct(Category $category)
    {
        $this->_category = $category;
    }

    // @route  GET api/v1/category
    // @desc   Get categories
    // @access Private
    public function getCategories()
    {
        $categories = $this->_category->_getCategories();
        return response()->json($categories, 200);
    }
}
