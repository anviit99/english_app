<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\CategoryUnlock;
use App\Models\UserScore;

class CategoryUnlockController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/v1/categories/unlock",
     *     summary="Mở khóa hạng mục nếu đủ điều kiện",
     *     tags={"Category"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="category_id", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Mở khóa thành công hoặc đã mở",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Đã mở khóa thành công!")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Chưa đủ điều kiện để mở khóa")
     * )
     */
    public function unlockCategory(Request $request)
    {
        $userId = auth()->id();
        $categoryId = $request->input('category_id');

        // Kiểm tra nếu đã unlock
        $exists = CategoryUnlock::where('user_id', $userId)
            ->where('category_id', $categoryId)
            ->exists();

        if ($exists) {
            return $this->responseSuccess([], "Bạn đã mở khóa hạng mục này rồi.");
        }

        // Ví dụ: cần hoàn thành ít nhất 3 bài trong category với điểm >= 7
        $passed = UserScore::where('user_id', $userId)
            ->where('score', '>=', 7)
            ->whereHas('lesson', function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->count();

        if ($passed >= 3) {
            CategoryUnlock::create([
                'user_id' => $userId,
                'category_id' => $categoryId,
            ]);
            return $this->responseSuccess([], "Đã mở khóa thành công!");
        }

        return $this->responseFail([], "Bạn cần hoàn thành ít nhất 3 bài với điểm >= 7 để mở khóa.");
    }

    /**
     * @OA\Get(
     *     path="/api/v1/categories/unlocked",
     *     summary="Lấy danh sách ID các category đã mở khóa của user",
     *     tags={"Category"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Danh sách các category đã mở khóa",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Danh sách category đã mở khóa"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(type="integer", example=2)
     *             )
     *         )
     *     )
     * )
     */

    public function getUnlockedCategories()
    {
        $userId = auth()->id();

        $categoryIds = CategoryUnlock::where('user_id', $userId)->pluck('category_id');

        return $this->responseSuccess($categoryIds, "Danh sách category đã mở khóa");
    }
    /**
     * @OA\Get(
     *     path="/api/v1/categories/unlock-status",
     *     summary="Danh sách tất cả category và trạng thái mở khóa của user",
     *     tags={"Category"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Danh sách category và trạng thái mở khóa",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Thành công"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Từ vựng cơ bản"),
     *                     @OA\Property(property="is_unlocked", type="boolean", example=true)
     *                 )
     *             )
     *         )
     *     )
     * )
     */

    public function listCategoriesWithUnlockStatus()
    {
        $userId = auth()->id();

        $unlockedIds = CategoryUnlock::where('user_id', $userId)->pluck('category_id')->toArray();
        if (empty($unlockedIds)) {
            $unlockedIds = [1, 2];
        }
        $categories = Category::all()->map(function ($category) use ($unlockedIds) {
            return [
                'id' => $category->id,
                'category_name' => $category->category_name,
                'is_unlocked' => in_array($category->id, $unlockedIds),
            ] ;
        }) ;

        return $this->responseSuccess($categories);
    }

}
