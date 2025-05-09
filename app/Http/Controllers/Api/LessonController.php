<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\Create;
use App\Http\Requests\Category\Update;
use App\Services\LessonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
class LessonController extends Controller
{
    public function __construct(
        protected LessonService $lessonService,
    ) {}

    /**
     * @OA\Get(
     *     path="/api/v1/lesson/list",
     *     summary="Lấy danh sách bài học",
     *     description="Trả về danh sách các bài học, có thể lọc theo category_id",
     *     tags={"Lesson"},
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="ID danh mục cần lọc",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Trang hiện tại",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Danh sách bài học",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Bài học đầu tiên"),
     *                 @OA\Property(property="content", type="string", example="Nội dung bài học..."),
     *                 @OA\Property(property="category_id", type="integer", example=2),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *             )),
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="total_pages", type="integer", example=5),
     *             @OA\Property(property="per_page", type="integer", example=10),
     *             @OA\Property(property="total_items", type="integer", example=50),
     *         )
     *     )
     * )
     */
    public function listLesson(Request $request): JsonResponse
    {
        $params = $request->all();
        $lessons = $this->lessonService->getLessons($params);

        return $this->responseSuccess($lessons->items());
    }

    /**
     * @OA\Get(
     *     path="/api/v1/lessons/{id}",
     *     summary="Lấy chi tiết bài học theo ID",
     *     description="Trả về chi tiết một bài học cụ thể",
     *     tags={"Lesson"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID bài học",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thông tin chi tiết bài học",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="Bài học đầu tiên"),
     *             @OA\Property(property="content", type="string", example="Nội dung chi tiết bài học..."),
     *             @OA\Property(property="category_id", type="integer", example=2),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy bài học"
     *     )
     * )
     */
    public function detailLesson($id): JsonResponse
    {
        $lesson = $this->lessonService->find($id);

        if (!$lesson) {
            return $this->responseFail([], 'Lesson not found');
        }

        return $this->responseSuccess($lesson);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/lesson/choice/{id}",
     *     summary="Lấy một bài học",
     *     tags={"Lesson"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bài học",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="I work as a software developer."),
     *             @OA\Property(property="description", type="string", example="Tôi làm việc như một lập trình viên phần mềm."),
     *             @OA\Property(property="tusapxep", type="string", example="lập, phần, như, tôi, một, làm, viên, phần, developer, work")
     *         )
     *     )
     * )
     */
    public function getLesson($id)
    {
        $lesson = $this->lessonService->find($id);

        if (!$lesson) {
            return response()->json(['error' => 'Lesson not found'], 404);
        }

        // Tách description thành các từ và sắp xếp ngẫu nhiên
        $words = explode(' ', $lesson->description);

        // Tạo một mảng các từ tiếng Việt ngẫu nhiên
        $randomWords = ['học', 'lập trình', 'thành công', 'phát triển', 'kỹ năng', 'nâng cao', 'mới', 'khám phá'];

        // Lấy 2-4 từ ngẫu nhiên từ mảng randomWords
        $randomWordsToAdd = array_rand(array_flip($randomWords), rand(2, 4));

        // Nếu chỉ lấy 1 từ, biến nó thành mảng để đảm bảo tính đồng nhất
        if (!is_array($randomWordsToAdd)) {
            $randomWordsToAdd = [$randomWordsToAdd];
        }

        // Thêm các từ ngẫu nhiên vào mảng words
        $words = array_merge($words, $randomWordsToAdd);

        // Shuffle các từ và gộp lại thành một chuỗi
        shuffle($words);
        $shuffledWords = implode(', ', $words);

        // Trả về bài học với các trường yêu cầu
        return response()->json([
            'id' => $lesson->id,
            'title' => $lesson->title,
            'description' => $lesson->description,
            'changed' => $shuffledWords
        ]);
    }
}
