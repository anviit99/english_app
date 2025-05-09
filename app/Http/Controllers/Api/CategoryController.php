<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\Create;
use App\Http\Requests\Category\Update;
use App\Models\Category;
use App\Models\UserCategoryProgress;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryService $category_service,
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
        $userId = auth('api')->id();

        $unlockedCategories = DB::table('category_unlocks')
            ->where('user_id', $userId)
            ->pluck('category_id')
            ->toArray();

        $categories = $this->category_service->getAll();

        $categories->transform(function ($category) use ($userId, $unlockedCategories) {
            // 3. Kiểm tra nếu category đã mở khóa
            $category->status = in_array($category->id, $unlockedCategories) ? 1 : 0;

            // Nếu category đã mở khóa, tính phần trăm bài học đã học
            if ($category->status == 1) {
                // 4. Lấy tất cả lesson_id trong category này
                $lessonIds = DB::table('lessons')
                    ->where('category_id', $category->id)
                    ->pluck('id')
                    ->toArray();

                // 5. Đếm số bài học user đã học (có trong user_scores)
                $learnedCount = DB::table('user_scores')
                    ->where('user_id', $userId)
                    ->whereIn('lesson_id', $lessonIds)
                    ->count();

                // 6. Tính phần trăm
                $totalLessons = count($lessonIds);
                $category->progress_percent = $totalLessons > 0 ? ($learnedCount / $totalLessons) * 100 : 0;
            } else {
                // Nếu chưa mở khóa, phần trăm là 0
                $category->progress_percent = 0;
            }

            return $category;
        });

        // 7. Map lại để trả về dữ liệu cần thiết
        $list = $categories->map(function ($item) {
            return [
                'id' => $item->id,  // category_id
                'category_name' => $item->category_name,
                'image' => $item->image,
                'color' => $item->color,
                'note' => $item->note,
                'status' => $item->status,
                'progress_percent' => $item->progress_percent,
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


    /**
     * @OA\Get(
     *     path="/api/v1/categories/{id}",
     *     summary="Lấy chi tiết danh mục",
     *     tags={"Category"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của danh mục",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lấy thông tin danh mục thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="category_name", type="string", example="Sách tiếng Anh"),
     *             @OA\Property(property="position", type="integer", example=3)
     *         )
     *     ),
     *     @OA\Response(response=404, description="Không tìm thấy danh mục")
     * )
     */
    public function getDetail($id)
    {
        $userId = auth('api')->id();
        try {
            $category = $this->category_service->find($id);
            foreach ($category->topics as $topic) {
                $progress = DB::table('user_topic_progress')
                    ->where('user_id', $userId)
                    ->where('topic_id', $topic->id)
                    ->first();
                $isOpen = false;
                $isCompleted = $progress->is_completed ?? false;
                if ($progress) {
                    $isOpen = true;
                } elseif ($topic->level == 1  ) {
                    $isOpen = true;
                }
                elseif ($previousCompleted) {
                    $isOpen = true;
                }
                $previousCompleted = $isCompleted;
                $topic->progress = [
                    'current_level' => $progress->current_level ?? 1,
                    'is_completed' => $progress->is_completed ?? false,
                    'is_open' => $isOpen,
                ];

            }
            return $this->responseSuccess($category, 'Lấy thông tin danh mục thành công');
        } catch (\Exception $e) {
            return $this->responseFail([], 'Không tìm thấy danh mục');
        }
    }



    public function requestSkipCategory(Request $request, $categoryId)
    {
        $user = auth()->user();

        $category = $this->category_service->find($categoryId);
        if (!$category) {
            return $this->responseFail([], "Category does not exist.");
        }

        $prevCategory = $this->category_service->find($categoryId - 1);

        if (!$prevCategory) {
            return response()->json(['message' => 'Không thể học vượt category đầu tiên.'], 400);
        }

        $progress = UserCategoryProgress::where('user_id', $user->id)
            ->where('category_id', $prevCategory->id)
            ->first();

        if (!$progress || !$progress->is_passed_exam) {
            return response()->json([
                'message' => "Bạn cần hoàn thành bài thi của category trước đó ({$prevCategory->category_name}) để học vượt."
            ], 403);
        }

        // Cho phép học vượt
        return response()->json([
            'message' => "Bạn được phép học vượt category {$category->category_name}."
        ]);
    }
}
