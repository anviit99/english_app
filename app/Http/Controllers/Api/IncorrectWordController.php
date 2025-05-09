<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\Create;
use App\Http\Requests\Category\Update;
use App\Models\IncorrectWord;
use App\Services\LessonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
class IncorrectWordController extends Controller
{
    /**
     * @OA\Schema(
     *     schema="IncorrectWord",
     *     type="object",
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="word", type="string", example="example"),
     *     @OA\Property(property="user_id", type="integer", example=5),
     *     @OA\Property(property="mistake_count", type="integer", example=3),
     *     @OA\Property(property="last_mistake_at", type="string", format="date-time", example="2025-05-08T10:00:00Z")
     * )
     */
    public function __construct(
        protected LessonService $lessonService,
    ) {}
    /**
     * @OA\Post(
     *     path="/api/v1/incorrect-words",
     *     summary="Lưu từ đọc sai",
     *     tags={"IncorrectWords"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="word", type="string", description="Từ đọc sai"),
     *             @OA\Property(property="correct_word", type="string", description="Từ đúng")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Từ đọc sai đã được lưu",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Lỗi xác thực dữ liệu")
     * )
     */

    public function store(Request $request)
    {
        $request->validate([
            'word' => 'required|string',
            'correct_word' => 'required|string',
        ]);

        $incorrectWord = IncorrectWord::create([
            'user_id' => auth('api')->id(), // Nếu có authentication
            'word' => $request->word,
            'correct_word' => $request->correct_word,
        ]);

        return response()->json([
            'message' => 'Incorrect word recorded successfully!',
            'data' => $incorrectWord,
        ], 201);
    }
    /**
     * @OA\Get(
     *     path="/api/v1/incorrect-words",
     *     summary="Lấy danh sách các từ đọc sai",
     *     tags={"IncorrectWords"},
     *     @OA\Response(
     *         response=200,
     *         description="Danh sách từ đọc sai",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/IncorrectWord")),
     *             @OA\Property(property="current_page", type="integer", description="Trang hiện tại"),
     *             @OA\Property(property="total_pages", type="integer", description="Tổng số trang"),
     *             @OA\Property(property="per_page", type="integer", description="Số lượng items mỗi trang"),
     *             @OA\Property(property="total_items", type="integer", description="Tổng số lượng items")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Lỗi hệ thống")
     * )
     */

    public function getList(Request $request)
    {
        $incorrectWords = IncorrectWord::with('user')
        ->paginate(10);

        return response()->json([
            'data' => $incorrectWords->items(),
            'current_page' => $incorrectWords->currentPage(),
            'total_pages' => $incorrectWords->lastPage(),
            'per_page' => $incorrectWords->perPage(),
            'total_items' => $incorrectWords->total(),
        ]);
    }


    /**
     * @OA\Post(
     *     path="/api/v1/incorrect-words/correct",
     *     summary="Kiểm tra và xóa từ đọc sai khi người dùng đọc đúng",
     *     tags={"IncorrectWords"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="word", type="string", description="Từ đọc sai"),
     *             @OA\Property(property="correct_word", type="string", description="Từ đúng")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Từ đọc sai đã được sửa và xóa thành công"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy từ đọc sai khớp"
     *     ),
     *     @OA\Response(response=500, description="Lỗi hệ thống")
     * )
     */

    public function correctWord(Request $request)
    {
        $request->validate([
            'word' => 'required|string',
            'correct_word' => 'required|string',
        ]);

        // Tìm từ sai tương ứng với từ đọc sai và kiểm tra
        $incorrectWord = IncorrectWord::where('word', $request->word)
            ->where('correct_word', $request->correct_word)
            ->where('user_id', auth('api')->id()) // Nếu có authentication
            ->first();

        if ($incorrectWord) {
            // Nếu tìm thấy, xóa từ đọc sai
            $incorrectWord->delete();

            return response()->json([
                'message' => 'Incorrect word corrected and removed successfully!',
            ], 200);
        }

        return response()->json([
            'message' => 'No matching incorrect word found.',
        ], 404);
    }
}
