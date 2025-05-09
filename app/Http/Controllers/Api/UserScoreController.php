<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserScore;
use App\Models\UserScoreLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserScoreController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/v1/user-score/add",
     *     summary="Lưu điểm người dùng sau khi hoàn thành bài học",
     *     tags={"User Score"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"percent"},
     *             @OA\Property(property="percent", type="number", format="float", example=85.5, description="Phần trăm kết quả đúng (0–100)"),
     *             @OA\Property(property="lesson_id", type="integer", example=5, description="ID của bài học (nếu có)"),
     *             @OA\Property(property="time_spent", type="integer", example=120, description="Thời gian học bài (tính bằng giây)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lưu điểm thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Score saved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=12),
     *                 @OA\Property(property="lesson_id", type="integer", example=5),
     *                 @OA\Property(property="percent", type="number", format="float", example=85.5),
     *                 @OA\Property(property="score", type="number", format="float", example=8.6),
     *                 @OA\Property(property="submitted_at", type="string", format="date-time", example="2025-04-08T21:58:00Z"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Dữ liệu đầu vào không hợp lệ"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Chưa xác thực người dùng"
     *     )
     * )
     */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'percent' => 'required|numeric|min:0|max:100',
            'time_spent' => 'nullable|integer',
            'lesson_id' => 'nullable|integer|exists:lessons,id',
        ]);

        $score = round($validated['percent'] / 10, 1); // Quy đổi 80% → 8.0

        $userScore = UserScore::create([
            'user_id' => auth()->id(),
            'lesson_id' => $validated['lesson_id'] ?? null,
            'percent' => $validated['percent'],
            'score' => $score,
            'time_spent' =>$validated['time_spent'],
            'submitted_at' => now(),
        ]);
        return $this->responseSuccess($userScore);
    }
    /**
     * @OA\Post(
     *     path="/api/v1/topic/completed",
     *     summary="Đánh dấu topic là hoàn thành",
     *     tags={"Topic"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"topic_id", "level"},
     *             @OA\Property(property="topic_id", type="integer", example=2),
     *             @OA\Property(property="level", type="integer", example=3)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Đánh dấu hoàn thành thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Thành công")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Lỗi đầu vào hoặc logic"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Chưa xác thực"
     *     )
     * )
     */
    public function completedTopic(Request $request)
    {
        $id_user = Auth()->id();
        DB::table('user_topic_progress')->insert([
            'user_id' => $id_user,
            'topic_id' => $request->topic_id,
            'current_level' => $request->level,
            'is_completed' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $this->responseSuccess();
    }


}
