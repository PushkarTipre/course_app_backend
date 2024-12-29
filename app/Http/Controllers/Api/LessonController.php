<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use \Smalot\PdfParser\Parser;

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



    protected function extractQuizData($file)
    {
        // Correct the path by omitting the "public/" part
        $path = public_path('uploads/' . $file);

        Log::info('Resolved file path: ' . $path);  // Log the resolved path for debugging

        // Check if the file exists
        if (!file_exists($path)) {
            Log::error('File not found: ' . $path); // Log error if the file doesn't exist
            return [
                'success' => 'File not found',
                'content' => null
            ];
        }

        // If file exists, proceed to read it
        Log::info('File found, attempting to read...');

        // Try to read the file content
        $handle = fopen($path, 'r');
        if ($handle) {
            $parser = new Parser();
            $pdf = $parser->parseFile($path);

            $text = $pdf->getText();
            Log::info('Extracted text from PDF: ' . $text);

            // Now parse the extracted text and convert it into the desired format
            $quizData = $this->parseQuizContent($text);

            return [
                'success' => 'Quiz file processed',
                'content' => $quizData
            ];
        } else {
            Log::error('Unable to open the file: ' . $path);
            return [
                'success' => 'Failed to read file',
                'content' => null
            ];
        }
    }
    protected function parseQuizContent($text)
    {
        // Initialize the array to store the parsed questions
        $quizArray = [];

        // Split the text into lines
        $lines = explode("\n", $text);
        $currentQuestion = null;
        $currentAnswers = [];
        $correctAnswer = null;

        // Iterate through each line and process it
        foreach ($lines as $line) {
            $line = trim($line); // Trim whitespace from the line

            // Check if the line contains a question
            if (preg_match('/^Question \d+:\s*(.*)/', $line, $matches)) {
                // If there is a previous question, push it to the result
                if ($currentQuestion !== null) {
                    $quizArray[] = [
                        'question' => $currentQuestion,
                        'answers' => $currentAnswers,
                        'correct_answer' => $correctAnswer
                    ];
                }

                // Start a new question and capture only the text after the colon
                $currentQuestion = $matches[1]; // Capture the question text
                $currentAnswers = [];
                $correctAnswer = null;
            }

            // Check if the line contains an option
            elseif (preg_match('/^Option \d+:\s*(.*)/', $line, $matches)) {
                $currentAnswers[] = $matches[1]; // Capture the option text
            }

            // Check if the line contains the correct answer
            elseif (preg_match('/^Answer\s*:\s*Option\s*(\d+)/', $line, $matches)) {
                $correctAnswer = (int)$matches[1] - 1; // Convert to zero-based index
            }
        }

        // Add the last question after loop ends
        if ($currentQuestion !== null) {
            $quizArray[] = [
                'question' => $currentQuestion,
                'answers' => $currentAnswers,
                'correct_answer' => $correctAnswer
            ];
        }

        return $quizArray;
    }




    public function lessonDetail(Request $request)
    {
        $id = $request->id;

        try {
            // Fetch the lesson by ID
            $result = Lesson::where('id', '=', $id)->select(
                'name',
                'description',
                'thumbnail',
                'video'
            )->first();

            if (!$result) {
                return response()->json([
                    'code' => 404,
                    'msg' => 'Lesson not found',
                    'data' => null,
                ], 404);
            }

            $videoData = $result->video;

            // Add real quiz data for each video
            foreach ($videoData as &$video) {
                // Check if a quiz file exists
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
                    'msg' => 'My all lesson detail is here',
                    'data' => $videoData, // Return video data with real quiz_json
                ],
                200
            );
        } catch (\Throwable $e) {
            return response()->json(
                [
                    'code' => 500,
                    'msg' => 'Server internal error',
                    'data' => $e->getMessage(),
                ],
                500
            );
        }
    }
}
