<?php

namespace App\Services;

use App\Models\Lesson;
use App\Repositories\Lesson\LessonRepository;

class LessonService
{
    public function __construct(protected LessonRepository $lesson_repo) {}
    public function getLessons(array $params)
    {

        $query = Lesson::query();

        if (!empty($params['category_id'])) {
            $query->where('category_id', $params['category_id']);
        }

        return $query->paginate(10);
    }

    public function find($id)
    {
        return $this->lesson_repo->find($id);
    }

}
