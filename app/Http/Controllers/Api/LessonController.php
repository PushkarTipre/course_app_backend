<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    //
    public function lessonList(Request $request)
    {
        $id = $request->id;
        try {
            $result = Lesson::where('course_id', '=', $id)->select(
                'id',
                'name',
                'description',
                'thumbnail',
                'video'
            )->get();
            return response()->json(
                [
                    'code' => 200,
                    'msg' => 'My lesson list is here ',
                    'data' => $result

                ],
                200
            );
        } catch (\Throwable $e) {
            return response()->json([
                'code' => 500,
                'msg' => 'Lesson not found',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function lessonDetail(Request $request)
    {

        $id = $request->id;

        try {

            $result =  Lesson::where('id', '=', $id)->select(
                'name',
                'description',
                'thumbnail',
                'video'
            )->first();

            return response()->json(
                [
                    'code' => 200,
                    'msg' => 'My lesson detail is here',
                    'data' => $result->video
                ],
                200
            );
        } catch (\Throwable $e) {
            return response()->json(
                [
                    'code' => 500,
                    'msg' => 'Server internal error',
                    'data' => $e->getMessage()
                ],
                500
            );
        }
    }
}
