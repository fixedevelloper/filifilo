<?php


namespace App\Http\Controllers\API\V2\Common;

use App\Helpers\api\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index() {
        $categories = Category::query()->orderBy('name', 'asc')->get()->map(function ($category) {
            return  [
                'id'=>$category->id,
                'name'=>$category->name,
               // 'description'=>$category->description,
            ];
        });

        return Helpers::success($categories, 'Categories récupérés avec succès');
    }
    public function store(Request $request) {}
    public function update(Request $request, $id) {}
    public function destroy($id) {}
}
