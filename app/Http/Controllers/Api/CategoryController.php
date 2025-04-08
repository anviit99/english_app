<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\Create;
use App\Http\Requests\Category\Update;
use App\Services\CategoryService;
use App\Services\UploadFileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryService $category_service,
        protected UploadFileService $uploadfile_service
    ) {}

    public function index(Request $request)
    {
        $params = $request->all();
        $categories = $this->category_service->getCategory($params);
        $response = [
            'data' => $categories->items(),
            'current_page' => $categories->currentPage(),
            'total_pages' => $categories->lastPage(),
            'per_page' => $categories->perPage(),
            'total_items' => $categories->total(),
        ];

        return $this->responseSuccess($response);
    }
    /**
     * @OA\Get(
     *     path="/api/v1/categories/get-all",
     *     summary="Danh sách danh mục (đơn giản)",
     *     tags={"Category"},
     *     @OA\Response(response="200", description="Danh sách danh mục đơn giản")
     * )
     */
    public function getAll()
    {
        $categories = $this->category_service->getAll();

        $list = $categories->map(function ($item) {
            return [
                'id' => $item->id,
                'category_name' => $item->category_name,
            ];
        });

        return $this->responseSuccess($list);
    }


    /**
     * @OA\Post(
     *     path="/api/v1/categories",
     *     summary="Tạo danh mục mới",
     *     tags={"Category"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="category_name", type="string", example="Sách tiếng Anh")
     *         )
     *     ),
     *     @OA\Response(response="200", description="Tạo danh mục thành công"),
     *     @OA\Response(response="400", description="Tạo danh mục thất bại")
     * )
     */
    public function create(Create $request)
    {
        try {
            DB::beginTransaction();
            $params = $request->only(['category_name']);
            $category = $this->category_service->createCategory($params);
            DB::commit();
            return $this->responseSuccess($category, 'Category created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseFail([], $e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/categories/{id}",
     *     summary="Cập nhật danh mục",
     *     tags={"Category"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của danh mục",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="category_name", type="string", example="Sách luyện nghe")
     *         )
     *     ),
     *     @OA\Response(response="200", description="Cập nhật thành công"),
     *     @OA\Response(response="404", description="Không tìm thấy danh mục")
     * )
     */
    public function update(Update $request, $id)
    {
        try {
            $params = $request->only(['category_name']);
            $category = $this->category_service->find($id);
            if (!$category) {
                return $this->responseFail([], "Category does not exist.");
            }

            $category->update($params);
            DB::commit();

            return $this->responseSuccess($category, 'Category updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseFail([], $e->getMessage());
        }
    }

    public function delete($id)
    {
        $category = $this->category_service->find($id);
        if ($category) {
            $this->uploadfile_service->destroyImage($category->image);
            $this->category_service->deleteCategory($id);

            return $this->responseSuccess([], "Deleted Successfully");
        }

        return $this->responseFail([], "Deleted Failed");
    }

    public function edit($id)
    {
        $category = $this->category_service->find($id);
        if ($category)
            return $this->responseSuccess($category);

        return $this->responseFail([]);
    }
}
