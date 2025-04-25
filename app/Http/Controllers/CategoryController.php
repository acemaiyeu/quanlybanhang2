<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCategoryValidator;
use App\Http\Requests\DeleteCategoryValidator;
use App\Http\Requests\DetailCategoryValidator;
use App\Http\Requests\UpdateCategoryValidator;
use App\ModelQuery\CategoryModel;
use App\Transformers\CategoryTransformer;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected $model;  //

    public function __construct(CategoryModel $model)
    {
        $this->model = $model;
    }

    public function getAllCategories(Request $request)
    {
        $categories = $this->model->getCategories($request);
        return fractal($categories, new CategoryTransformer())->respond();
    }

    public function createCategory(CreateCategoryValidator $request)
    {
        $category = $this->model->createCategory($request);
        return fractal($category, new CategoryTransformer())->respond();
    }

    public function updateCategory(UpdateCategoryValidator $request)
    {
        $category = $this->model->updateCategory($request);

        if (empty($category->code)) {
            return $category;
        }
        return fractal($category, new CategoryTransformer())->respond();
    }

    public function deleteCategory(DeleteCategoryValidator $request)
    {
        $category = $this->model->deleteCategory($request);
        return response()->json(['status' => '200', 'message' => 'success'], 200);
    }

    public function getDetailCategory(DetailCategoryValidator $request)
    {
        $request['limit'] = 1;
        $category = $this->model->getCategories($request);
        return fractal($category, new CategoryTransformer())->respond();
    }
}
