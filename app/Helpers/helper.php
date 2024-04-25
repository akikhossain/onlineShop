<?php

use App\Models\Category;

function getCategories()
{
    return Category::orderBy('name', 'asc')
        ->with('sub_category')
        ->orderBy('id', 'DESC')
        ->where('showHome', 'Yes')
        ->where('status', 1)
        ->get();
}
