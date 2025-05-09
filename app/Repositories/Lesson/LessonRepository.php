<?php

namespace App\Repositories\Lesson;

use App\Models\Lesson;
use App\Repositories\BaseRepository;
use App\Repositories\Lesson\LessonRepositoryInterface;

class LessonRepository extends BaseRepository implements LessonRepositoryInterface
{
    public function __construct(Lesson $model)
    {
        $this->model = $model;
    }

}
