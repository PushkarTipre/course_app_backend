<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Response;


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
                'price',
                'is_free'
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


    public function coursesBought(Request $request)
    {
        $user = $request->user();

        $result = Course::whereIn('id', function ($query) use ($user) {
            $query->select('course_id')
                ->from('orders')
                ->where('user_token', $user->token)
                ->where('status', 1);
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
            ->select('name', 'thumbnail', 'lesson_num', 'price', 'id')
            ->inRandomOrder()  // This randomizes the results
            ->limit(3)        // This limits to 3 results
            ->get();

        return response()->json([
            'code' => 200,
            'msg' => 'The courses recommended for you',
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

    public function popularCourses()
    {
        $result = Course::select('name', 'thumbnail', 'lesson_num', 'price', 'id', 'purchase_count', 'enrollment_count')
            ->orderByRaw('(CASE WHEN is_free = 1 THEN enrollment_count ELSE purchase_count END) DESC')
            ->limit(10)
            ->get();

        return response()->json([
            'code' => 200,
            'msg' => 'Top 10 Popular courses based on enrollments and purchases',
            'data' => $result
        ], 200);
    }

    public function newestCourses()
    {
        $result = Course::select('name', 'thumbnail', 'lesson_num', 'price', 'id', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'code' => 200,
            'msg' => 'Top 10 Newest Courses',
            'data' => $result
        ], 200);
    }




    public function enrollInCourse(Request $request)
    {
        try {
            $user = $request->user();
            $courseId = $request->input('course_id');

            $course = Course::find($courseId);

            if (!$course) {
                return response()->json([
                    'code' => 404,
                    'msg' => 'Course not found',
                    'data' => null
                ], 404);
            }

            if ($course->is_free) {
                // Increment enrollment count
                $course->increment('enrollment_count');

                // Add a new entry to the Order table for free course
                Order::create([
                    'user_token' => $user->token,
                    'total_amount' => 0,
                    'course_id' => $courseId,
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => null,
                ]);

                return response()->json([
                    'code' => 200,
                    'msg' => 'Enrollment successful',
                    'data' => [
                        'has_access' => true,
                        'course_id' => $courseId
                    ]
                ], 200);
            }

            $hasPurchased = Order::where('user_token', $user->token)
                ->where('course_id', $courseId)
                ->where('status', 1)
                ->exists();

            if ($hasPurchased) {
                // Increment enrollment count
                $course->increment('enrollment_count');
                return response()->json([
                    'code' => 200,
                    'msg' => 'Access granted',
                    'data' => [
                        'has_access' => true,
                        'course_id' => $courseId
                    ]
                ], 200);
            } else {
                return response()->json([
                    'code' => 412,
                    'msg' => 'Please purchase this course to access the videos',
                    'data' => [
                        'has_access' => false,
                        'course_id' => $courseId
                    ]
                ], 403);
            }
        } catch (\Throwable $e) {
            return response()->json([
                'code' => 500,
                'msg' => 'Server internal error',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function courseListWithFilter(Request $request)
    {

        $query = Course::select('name', 'thumbnail', 'lesson_num', 'price', 'id', 'description');


        if ($request->has('category')) {
            $categoryId = $request->input('category');
            $query->where('type_id', $categoryId);
        }


        $result = $query->get();


        return response()->json([
            'code' => 200,
            'msg' => $request->has('category') ? 'Filtered courses' : 'All courses',
            'data' => $result
        ], 200);
    }

    public function courseCategories()
    {
        try {

            $categories = DB::table('course_types')
                ->select('id', 'title')
                ->get();

            return response()->json([
                'code' => 200,
                'msg' => 'Available course categories',
                'data' => $categories
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'code' => 500,
                'msg' => 'Server internal error',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function checkVideoAccess(Request $request)
    {
        try {
            $user = $request->user();
            $courseId = $request->input('course_id');


            $hasPurchased = Order::where('user_token', $user->token)
                ->where('course_id', $courseId)
                ->where('status', 1)
                ->exists();

            if ($hasPurchased) {
                return response()->json([
                    'code' => 200,
                    'msg' => 'Access granted',
                    'data' => [
                        'has_access' => true,
                        'course_id' => $courseId
                    ]
                ], 200);
            } else {
                return response()->json([
                    'code' => 412,
                    'msg' => 'Please purchase this course to access the videos',
                    'data' => [
                        'has_access' => false,
                        'course_id' => $courseId
                    ]
                ], 403);
            }
        } catch (\Throwable $e) {
            return response()->json([
                'code' => 500,
                'msg' => 'Server internal error',
                'data' => $e->getMessage()
            ], 500);
        }
    }
}
