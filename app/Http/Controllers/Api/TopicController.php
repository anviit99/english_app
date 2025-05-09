<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\Create;
use App\Http\Requests\Category\Update;
use App\Services\LessonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
class TopicController extends Controller
{

//    public function listLesson(Request $request): JsonResponse
//    {
//        $params = $request->all();
//        $lessons = $this->lessonService->getLessons($params);
//
//        return $this->responseSuccess($lessons->items());
//    }
    public function getDetail($id)
    {
        $userId = auth('api')->id();
        try {
            $topic = DB::table('topics')
                ->where('id', $id)->first();
            $lessons = DB::table('lessons')
                ->where('category_id', $topic->category_id)->where('level',$topic->level)->get();
//            dd($lessons);
            return $this->responseSuccess($lessons, 'Lấy thông tin topics thành công');
        } catch (\Exception $e) {
            return $this->responseFail([], 'Không tìm thấy danh mục');
        }
    }
}
