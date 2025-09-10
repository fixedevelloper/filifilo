<?php


namespace App\Http\Controllers\API\V2\Common;

use App\Helpers\api\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

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
    public function store(Request $request) {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'type' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            $err = null;
            foreach ($validator->errors()->all() as $error) {
                $err = $error;
            }
            return Helpers::error($err);
        }


        DB::beginTransaction();

        try {
            $product = Category::create([
                'name' => $request->name,
                'store_type' => $request->type,
                'description' => $request->description
            ]);

            DB::commit();

            return Helpers::success($product, 'Categorie créée avec succès');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création  Categorie', [
                'message' => $e->getMessage(),
                'stack' => $e->getTraceAsString(),
            ]);
            return Helpers::error('Une erreur est survenue lors de la création ddu produit');
        }
    }
    public function update(Request $request, $id) {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'type' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            $err = null;
            foreach ($validator->errors()->all() as $error) {
                $err = $error;
            }
            return Helpers::error($err);
        }


        DB::beginTransaction();

        try {
            $product=Category::query()->findOrFail($id);
            $product->update([
                'name' => $request->name,
                'type' => $request->type,
                'description' => $request->description
            ]);

            DB::commit();

            return Helpers::success($product, 'Categorie créée avec succès');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création  Categorie', [
                'message' => $e->getMessage(),
                'stack' => $e->getTraceAsString(),
            ]);
            return Helpers::error('Une erreur est survenue lors de la création ddu produit');
        }
    }
    public function destroy($id) {}
}
