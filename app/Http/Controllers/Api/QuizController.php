<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class QuizController extends Controller
{

    public function lessonDetail(Request $request)
    {
        // Validate the inputs (course_id and lesson_id)
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'lesson_id' => 'required|exists:lessons,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 422,
                'msg' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Fetch the lesson by ID and course_id
        $lesson = Lesson::where('id', '=', $request->lesson_id)
            ->where('course_id', '=', $request->course_id)
            ->first();

        if (!$lesson) {
            return response()->json([
                'code' => 404,
                'msg' => 'Lesson not found for the given course',
                'data' => null,
            ], 404);
        }

        // Get video data from the lesson
        $videoData = $lesson->video; // Assuming video is a field inside the lesson

        // Add real quiz data for each video
        foreach ($videoData as &$video) {
            // Check if a quiz file exists for this video
            if (!empty($video['quiz'])) {
                // Extract real data from the quiz file
                $video['quiz_json'] = $this->extractQuizData($video['quiz']);
            } else {
                $video['quiz_json'] = null; // If no quiz file, set as null
            }
        }

        return response()->json(
            [
                'code' => 200,
                'msg' => 'Lesson details and quiz data fetched successfully',
                'data' => $videoData, // Return video data with real quiz_json
            ],
            200
        );
    }


    public function startQuiz(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'lesson_id' => 'required|exists:lessons,id',
            'course_video_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 422,
                'msg' => 'Validation failed',
                'data' => $validator->errors()
            ], 422);
        }

        $course = Course::find($request->course_id);
        $lesson = Lesson::find($request->lesson_id);

        if (!$course || !$lesson || $course->id !== $lesson->course_id) {
            return response()->json([
                'code' => 400,
                'msg' => 'Course or Lesson mismatch',
                'data' => null
            ], 400);
        }

        $videos = is_array($lesson->video) ? $lesson->video : json_decode($lesson->video, true);
        $video = collect($videos)->firstWhere('course_video_id', $request->course_video_id);

        if (!$video) {
            return response()->json([
                'code' => 404,
                'msg' => 'Video not found',
                'data' => null
            ], 404);
        }

        $existingQuizAttempt = QuizAttempt::where('user_id', $request->user()->id)
            ->where('lesson_id', $request->lesson_id)
            ->where('course_video_id', $request->course_video_id)  // Check if it's the same video
            ->first();

        if ($existingQuizAttempt) {
            // If the user has already attempted this quiz, return the existing data
            return response()->json([
                'code' => 201,
                'msg' => 'Quiz already attempted',
                'data' => [
                    'quiz' => $existingQuizAttempt,
                    'quiz_url' => $video['quiz'] ?? null,
                    'score' => $existingQuizAttempt->score,
                    'quiz_started_at' => $existingQuizAttempt->quiz_started_at,
                    'course_video_id' => (string) $video['course_video_id'] ?? 'Unknown',  // Ensure it's a string
                ]
            ], 200);
        }

        try {
            Log::info('Creating quiz attempt with data:', [
                'user_id' => $request->user()->id,
                'user_name' => $request->user()->name,
                'unique_id' => $request->user()->unique_id,
                'quiz_unique_id' => $request->user()->unique_id . '_' . uniqid(),
            ]);

            $quizAttempt = QuizAttempt::create([
                'user_id' => $request->user()->id,
                'user_name' => $request->user()->name,
                'unique_id' =>  $request->user()->unique_id,
                'quiz_unique_id' => $request->user()->unique_id . '_' . uniqid(),
                'course_id' => $course->id,
                'lesson_id' => $lesson->id,
                'course_video_id' => $request->course_video_id,
                'score' => 0,
                'completed' => false,
                'attempted_at' => now(),
                'quiz_started_at' => now()->setTimezone('Asia/Kolkata'),
                'quiz_expiry' => now()->addMinutes(15)->setTimezone('Asia/Kolkata'),
            ]);

            return response()->json([
                'code' => 200,
                'msg' => 'Quiz started successfully',
                'data' => [
                    'quiz' => $quizAttempt,
                    'quiz_url' => $video['quiz'] ?? null,
                    'quiz_started_at' => $quizAttempt->quiz_started_at,
                    'course_video_id' => $video['course_video_id'] ?? 'Unknown',
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error starting quiz: ' . $e->getMessage());
            return response()->json([
                'code' => 500,
                'msg' => 'Error occurred while starting quiz.' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }





    public function submitQuiz(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'quiz_unique_id' => 'required|exists:quiz_attempts,quiz_unique_id',
            'score' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 422,
                'msg' => 'Validation failed',
                'data' => $validator->errors()
            ], 422);
        }

        $quizAttempt = QuizAttempt::where('quiz_unique_id', $request->quiz_unique_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$quizAttempt) {
            return response()->json([
                'code' => 404,
                'msg' => 'Quiz attempt not found',
                'data' => null
            ], 404);
        }

        $quizAttempt->update([
            'score' => $request->score,
            'completed' => true,
            'quiz_ended_at' => now()->setTimezone('Asia/Kolkata'),
            'attempted_at' => now(),
        ]);

        return response()->json([
            'code' => 200,
            'msg' => 'Quiz submitted successfully',
            'data' => [
                'score' => $quizAttempt->score,
                'completed' => $quizAttempt->completed,
                'quiz_ended_at' => $quizAttempt->quiz_ended_at,
            ]
        ], 200);
    }





    public function getQuizResult(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'quiz_unique_id' => 'required|exists:quiz_attempts,quiz_unique_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 422,
                'msg' => 'Validation failed',
                'data' => $validator->errors()
            ], 422);
        }

        $quizAttempt = QuizAttempt::where('quiz_unique_id', $request->quiz_unique_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$quizAttempt) {
            return response()->json([
                'code' => 404,
                'msg' => 'Quiz attempt not found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'code' => 200,
            'msg' => 'Quiz result retrieved successfully',
            'data' => [
                'quiz' => $quizAttempt,
                'score' => $quizAttempt->score,
                'completed' => $quizAttempt->completed,
            ]
        ], 200);
    }

    public function getAllQuizResults(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'unique_id' => 'required|exists:quiz_attempts,unique_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 422,
                'msg' => 'Validation failed',
                'data' => $validator->errors()
            ], 422);
        }


        $quizAttempts = QuizAttempt::where('unique_id', $request->unique_id)
            ->where('user_id', $user->id)
            ->get();

        if ($quizAttempts->isEmpty()) {
            return response()->json([
                'code' => 404,
                'msg' => 'No quiz attempts found for the given unique_id',
                'data' => null
            ], 404);
        }

        $results = $quizAttempts->map(function ($quizAttempt) {
            return [
                'quiz_unique_id' => $quizAttempt->quiz_unique_id,

                'score' => $quizAttempt->score,
                'completed' => $quizAttempt->completed,
                'attempted_at' => $quizAttempt->created_at,
            ];
        });

        return response()->json([
            'code' => 200,
            'msg' => 'Quiz results retrieved successfully',
            'data' => $results
        ], 200);
    }
}
