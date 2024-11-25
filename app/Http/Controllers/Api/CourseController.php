<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Redis;

// class CourseController extends Controller
// {
//     //course list
//     public function courseList()
//     {
//         $result = Course::select('name', 'thumbnail', 'lesson_num', 'price', 'id')->get();

//         return response()->json([
//             'code' => 200,
//             'msg' => 'My course list is here',
//             'data' => $result
//         ], 200);
//     }

//     public function courseDetail(Request $request)
//     {
//         $id = $request->id;
//         try {
//             $result = Course::where('id', '=', $id)->select(
//                 'id',
//                 'name',
//                 'user_token',
//                 'description',
//                 'thumbnail',
//                 'lesson_num',
//                 'video_length',
//                 'price'


//             )->firstOrFail();
//             return response()->json(
//                 [
//                     'code' => 200,
//                     'msg' => 'Course details',
//                     'data' => $result

//                 ],
//                 200
//             );
//         } catch (\Throwable $e) {
//             return response()->json([
//                 'code' => 500,
//                 'msg' => 'Course not found',
//                 'data' => $e->getMessage()
//             ], 500);
//         }
//         // $result = Course::select('name', 'thumbnail', 'lesson_num', 'price', 'id')->get();

//         // return response()->json([
//         //     'code' => 200,
//         //     'msg' => 'My course list is here',
//         //     'data' => $result
//         // ], 200);
//     }

//     public function coursesBought(Request $request)
//     {
//         $user = $request->user();
//         $result = Course::where('user_token', '=', $user->token)
//             ->select('name', 'thumbnail', 'lesson_num', 'price', 'id')->get();

//         return response()->json([
//             'code' => 200,
//             'msg' => 'The courses you bought',
//             'data' => $result
//         ], 200);
//     }

//     public function coursesSearchDefault(Request $request)
//     {
//         $user = $request->user();
//         $result = Course::where('recommended', '=', 1)
//             ->select('name', 'thumbnail', 'lesson_num', 'price', 'id')->get();

//         return response()->json([
//             'code' => 200,
//             'msg' => 'The courses recommended for you.',
//             'data' => $result
//         ], 200);
//     }


//     public function coursesSearch(Request $request)
//     {
//         $user = $request->user();
//         $search = $request->search;
//         $result = Course::where("name", "like", "%" . $search . "%")
//             ->select('name', 'thumbnail', 'lesson_num', 'price', 'id')->get();

//         return response()->json([
//             'code' => 200,
//             'msg' => 'The courses you searched',
//             'data' => $result
//         ], 200);
//     }

//     // public function authorCourseList(Request $request)
//     // {
//     //     $token = $request->token;
//     //     $result = Course::where('token', '=',  $token)
//     //         ->select('name', 'thumbnail', 'lesson_num', 'price', 'id', 'score')->get();

//     //     return response()->json([
//     //         'code' => 200,
//     //         'msg' => "Author's course list",
//     //         'data' => $result
//     //     ], 200);
//     // }

//     // public function courseAuthor(Request $request)
//     // {
//     //     $token = $request->token;
//     //     $result = User::where('user_token', '=',  $token)
//     //         ->select('token', 'name', 'avatar', 'job', 'description')->first();

//     //     return response()->json([
//     //         'code' => 200,
//     //         'msg' => "Author's course list",
//     //         'data' => $result
//     //     ], 200);
//     // }

//     public function authorCourseList(Request $request)
//     {
//         $token = $request->token;

//         $result = Course::where('user_token', '=', $token)
//             ->select('name', 'thumbnail', 'lesson_num', 'price', 'id', 'score')->get();

//         return response()->json([
//             'code' => 200,
//             'msg' => 'Courses of Author',
//             'data' => $result
//         ], 200);
//     }

//     public function courseAuthor(Request $request)
//     {
//         $token = $request->token;

//         $result = User::where('token', '=', $token)
//             ->select('token', 'name', 'avatar', 'description')->first();

//         return response()->json([
//             'code' => 200,
//             'msg' => 'Course Author',
//             'data' => $result
//         ], 200);
//     }
// }


class CourseController extends Controller
{
    //course list
    public function courseList()
    {
        $result = Course::select('name', 'thumbnail', 'lesson_num', 'price', 'id')->get();

        return response()->json([
            'code' => 200,
            'msg' => 'My course list is here',
            'data' => $result
        ], 200);
    }

    //course list
    public function courseDetail(Request $request)
    {
        $id = $request->id;

        try {

            $result =  Course::where('id', '=', $id)->select(
                'id',
                'name',
                'user_token',
                'description',
                'thumbnail',
                'lesson_num',
                'video_length',

                'price'
            )->first();

            return response()->json(
                [
                    'code' => 200,
                    'msg' => 'My course detail is here',
                    'data' => $result
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

    //course list
    public function coursesBought(Request $request)
    {
        $user = $request->user();

        $result = Course::whereIn('id', function ($query) use ($user) {
            $query->select('course_id')
                ->from('orders')
                ->where('user_token', $user->token)
                ->where('status', 1);  // Assuming status 1 means the order is completed
        })
            ->select('name', 'thumbnail', 'lesson_num', 'price', 'id')
            ->get();

        return response()->json([
            'code' => 200,
            'msg' => 'The courses you bought',
            'data' => $result
        ], 200);
    }

    public function coursesSearchDefault(Request $request)
    {
        $user = $request->user();

        $result = Course::where('recommended', '=', 1)
            ->select('name', 'thumbnail', 'lesson_num', 'price', 'id')->get();

        return response()->json([
            'code' => 200,
            'msg' => 'The courses recommened for you',
            'data' => $result
        ], 200);
    }

    public function coursesSearch(Request $request)
    {
        $user = $request->user();
        $search = $request->search;

        $result = Course::where("name", "like", "%" . $search . "%")
            ->select('name', 'thumbnail', 'lesson_num', 'price', 'id')->get();

        return response()->json([
            'code' => 200,
            'msg' => 'The courses you searched',
            'data' => $result
        ], 200);
    }

    public function authorCourseList(Request $request)
    {
        $token = $request->token;

        $result = Course::where('user_token', '=', $token)
            ->select('name', 'thumbnail', 'lesson_num', 'price', 'id', 'score')->get();

        return response()->json([
            'code' => 200,
            'msg' => 'Courses of Author',
            'data' => $result
        ], 200);
    }

    public function courseAuthor(Request $request)
    {
        $token = $request->token;

        $result = DB::table('admin_users')
            ->where('token', '=', $token)
            ->select('token', 'name', 'avatar', 'description')
            ->first();

        if ($result) {
            return response()->json([
                'code' => 200,
                'msg' => 'Course Author',
                'data' => $result
            ], 200);
        } else {
            return response()->json([
                'code' => 404,
                'msg' => 'Course Author not found',
                'data' => null
            ], 404);
        }
    }
}
