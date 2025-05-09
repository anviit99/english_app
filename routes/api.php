<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LessonController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CategoryUnlockController;
use App\Http\Controllers\Api\UserScoreController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['middleware' => 'api', 'prefix' => '/v1/auth'], function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::group(['middleware' => 'jwt.auth', 'prefix' => 'v1'], function () {
    Route::controller(UserController::class)->group(function () {
        Route::prefix('user')->group(function () {
            Route::get('profile', 'profile');
            Route::post('update/{id}', 'update');
            Route::group(['middleware' => 'admin'], function () {
                Route::get('index', 'index');
                Route::post('create', 'create');
                Route::get('delete/{id}', 'delete');
                Route::get('edit/{id}', 'edit');
            });
            Route::post('favourite/{id}', 'favourite');
            Route::get('cart', 'cart');
            Route::get("order-history", 'orderHistory');
            Route::get("order-history-cms", 'orderHistoryCms');
            Route::post("update-order-status", 'updateOrderStatus');
        });
    });
});

Route::group(['middleware' => 'jwt.auth', 'prefix' => 'v1'], function () {
    Route::controller(LessonController::class)->group(function () {
        Route::prefix('lesson')->group(function () {
            Route::get('/list', [LessonController::class, 'listLesson']);
            Route::get('/{id}', [LessonController::class, 'detailLesson']);
            Route::get('/choice/{id}', [LessonController::class,  'getLesson']);
        });

    });
});

Route::group(['middleware' => 'jwt.auth', 'prefix' => 'v1'], function () {
    Route::controller(CategoryController::class)->group(function () {
        Route::prefix('categories')->group(function () {
            Route::get('get-all', 'getAll');
            Route::get('/{id}', 'getDetail');
            Route::group(['middleware' => 'admin'], function () {
                Route::get('index', 'index');
                Route::post('create', 'create');
                Route::post('update/{id}', 'update');
                Route::get('delete/{id}', 'delete');
                Route::get('edit/{id}', 'edit');
            });
        });
    });
});

Route::group(['middleware' => 'jwt.auth', 'prefix' => 'v1'], function () {
    Route::controller(UserScoreController::class)->group(function () {
        Route::prefix('user-score')->group(function () {
            Route::post('/add',  'store');
        });
    });
});

Route::group(['middleware' => 'jwt.auth', 'prefix' => 'v1'], function () {
    Route::controller(CategoryUnlockController::class)->group(function () {
        Route::prefix('categories')->group(function () {
            Route::post('unlock', 'unlockCategory');
            Route::get('unlocked', 'getUnlockedCategories');
            Route::get('listCategoriesUnlock', 'listCategoriesWithUnlockStatus');
        });
    });
});

Route::group(['middleware' => 'jwt.auth', 'prefix' => 'v1'], function () {
    Route::controller(\App\Http\Controllers\Api\IncorrectWordController::class)->group(function () {
        Route::post('/incorrect-words',  'store');
        Route::get('/incorrect-words',  'getList');
        Route::post('/incorrect-words/correct',  'correctWord');

    });
});


Route::group(['middleware' => 'jwt.auth', 'prefix' => 'v1'], function () {
    Route::controller(UserScoreController::class)->group(function () {
        Route::prefix('user-score')->group(function () {
            Route::post('/add',  'store');
            Route::post('/completedTopic',  'completedTopic');
        });
    });
});


Route::group(['middleware' => 'jwt.auth', 'prefix' => 'v1'], function () {
    Route::controller(\App\Http\Controllers\Api\TopicController::class)->group(function () {
        Route::prefix('topic')->group(function () {
            Route::get('/{id}',  'getDetail');
        });
    });
});
