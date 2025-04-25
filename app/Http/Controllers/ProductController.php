<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateProductValidator;
use App\Http\Requests\DeleteProductValidator;
use App\Http\Requests\UpdateProductValidator;
use App\ModelQuery\ProductModel;
use App\Transformers\ProductTransformer;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $model;  //

    public function __construct(ProductModel $model)
    {
        $this->model = $model;
    }

    public function getAllProducts(Request $request)
    {
        $products = $this->model->getProducts($request);
        return fractal($products, new ProductTransformer())->respond();
    }

    public function createProduct(CreateProductValidator $request)
    {
        $products = $this->model->createProduct($request);
        return fractal($products, new ProductTransformer())->respond();
    }

    public function updateProduct(UpdateProductValidator $request)
    {
        $product = $this->model->updateProduct($request);

        if (empty($product->code)) {
            return $product;
        }
        return fractal($product, new ProductTransformer())->respond();
    }

    public function deleteProduct(DeleteProductValidator $request)
    {
        $product = $this->model->deleteProduct($request);
        return response()->json(['status' => '200', 'message' => 'success'], 200);
    }
}
