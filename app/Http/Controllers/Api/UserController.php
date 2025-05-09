<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\Update;
use App\Models\User;
use App\Models\UserScore;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function __construct(
        protected UserService $user_service,
    ) {}

    /**
     * @OA\Get(
     *     path="/api/user/profile",
     *     summary="Lấy thông tin tài khoản người dùng + thống kê điểm",
     *     tags={"User"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Thông tin tài khoản và thống kê",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Thành công!"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Nguyễn Văn A"),
     *                 @OA\Property(property="email", type="string", example="a@gmail.com"),
     *                 @OA\Property(property="total_score", type="number", example=34.5),
     *                 @OA\Property(property="total_lessons", type="integer", example=12),
     *                 @OA\Property(
     *                     property="last_lesson",
     *                     type="object",
     *                     @OA\Property(property="lesson_id", type="integer", example=5),
     *                     @OA\Property(property="score", type="number", example=9),
     *                     @OA\Property(property="percent", type="number", example=90),
     *                     @OA\Property(property="submitted_at", type="string", format="date-time", example="2025-04-08T22:10:00")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Chưa xác thực người dùng"
     *     )
     * )
     */

    public function profile()
    {
        $id_user = Auth()->id();
        $user = $this->user_service->getUserLogin($id_user);
        $totalScore = UserScore::where('user_id', $id_user)->sum('score');
        $user->total_score = round($totalScore, 2);

        // Tổng bài đã làm (distinct theo lesson_id hoặc count theo id)
        $totalLessons = UserScore::where('user_id', $id_user)
            ->whereNotNull('lesson_id')
            ->distinct('lesson_id')
            ->count('lesson_id');

        // Lần gần nhất học (tức bài có điểm gần nhất)
        $lastScore = UserScore::where('user_id', $id_user)
            ->orderByDesc('submitted_at')
            ->first();

        $user->total_score = round($totalScore, 2);
        $user->total_lessons = $totalLessons;
        $user->last_lesson = $lastScore ? [
            'lesson_id' => $lastScore->lesson_id,
            'score' => $lastScore->score,
            'percent' => $lastScore->percent,
            'submitted_at' => $lastScore->submitted_at,
        ] : null;

        $learningDays = DB::table('user_scores')
            ->where('user_id', $user->id)
            ->selectRaw('DATE(created_at) as day')
            ->groupBy('day')
            ->orderBy('day', 'desc')
            ->pluck('day')
            ->toArray();

        $streak = 0;
        $today = now()->startOfDay();

        foreach ($learningDays as $day) {
            $learningDate = \Carbon\Carbon::parse($day)->startOfDay();

            if ($today->equalTo($learningDate)) {
                $streak++;
                $today->subDay();
            } else {
                break;
            }
        }

        $user->streak = $streak ?? 0;

        return $this->responseSuccess($user, "Thành công!");
    }

    public function update(Update $request, $id)
    {
        try {
            if (!$id) {
                return $this->responseFail([], "User không tồn tại!", null, 404);
            }

            $params = $request->validated();
            $user = $this->user_service->find($id);
            $user->update($params);
            return $this->responseSuccess($user, "Thành công!");
        } catch (\Exception $e) {
            return $this->responseFail([], $e->getMessage());
        }
    }

    public function index(Request $request)
    {
        $params = $request->all();
        $users = $this->user_service->getUserAll($params);

        $response = [
            'data' => $users->items(),
            'current_page' => $users->currentPage(),
            'total_pages' => $users->lastPage(),
            'per_page' => $users->perPage(),
            'total_items' => $users->total(),
        ];

        return $this->responseSuccess($response, "Thành công!");
    }

    public function create(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_name' => 'required|string|between:2,100',
                'email' => 'required|string|email|max:100|unique:users',
                'password' => 'required|string|min:6',
                'phone' => 'required|regex:/^0[0-9]{9,10}$/',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors()->toJson(), 400);
            }

            $param_users = array_merge(
                $request->only(["email", "phone", 'user_name']),
                ['password' => bcrypt($request->password)]
            );

            $user = $this->user_service->createUser($param_users);

            return $this->responseSuccess($user, 'Tạo người dùng thành công!');
        } catch (\Exception $e) {
            return $this->responseFail([], $e->getMessage());
        }
    }

    public function delete($id)
    {
        if ($id) {
            $this->user_service->deleteUser($id);

            return $this->responseSuccess([], "Xóa thành công!");
        }

        return $this->responseFail([], "Xóa thất bại!");
    }

    public function edit($id)
    {
        $user = $this->user_service->find($id);
        if ($user)
            return $this->responseSuccess($user);

        return $this->responseFail([]);
    }

}
