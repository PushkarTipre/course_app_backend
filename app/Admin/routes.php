<?php

use App\Admin\Controllers\CourseController;
use App\Admin\Controllers\CourseTypeController;
use App\Admin\Controllers\UserController;
use App\Admin\Controllers\LessonController;
use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('home');
    $router->resource('/users', UserController::class);
    $router->resource('/course-types', CourseTypeController::class);
    $router->resource('/courses', CourseController::class);
    $router->resource('/lessons', LessonController::class);
});
