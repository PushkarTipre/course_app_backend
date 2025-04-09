<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class AnalyticsController extends Controller
{
    public function uploadAndProcessCsvFiles(Request $request)
    {
        try {
            // Step 1: Upload Files
            $uploadResult = $this->uploadFiles($request);

            if ($uploadResult['status'] !== 'success') {
                return response()->json([
                    'code' => $uploadResult['code'],
                    'msg' => $uploadResult['message'],
                    'data' => $uploadResult['data']
                ], $uploadResult['code']);
            }

            $filesData = $uploadResult['data'];

            // Step 2: Process Files
            $processResults = [];
            $allSuccessful = true;

            foreach ($filesData as $fileData) {
                try {
                    $processResult = $this->processCsvWithModel($fileData['full_path'], $fileData['user_id']);
                    $processResults[] = [
                        'upload' => [
                            'path' => $fileData['path'],
                            'user_id' => $fileData['user_id'],
                            'filename' => $fileData['filename']
                        ],
                        'process' => [
                            'summary' => $processResult['summary'],
                            'details' => $processResult['details'],
                            'output_file' => $processResult['output_file']
                        ],
                        'status' => 'success'
                    ];
                } catch (\Throwable $e) {
                    // Upload successful but processing failed for this file
                    Log::error('Error processing CSV file: ' . $e->getMessage());
                    $processResults[] = [
                        'upload' => [
                            'path' => $fileData['path'],
                            'user_id' => $fileData['user_id'],
                            'filename' => $fileData['filename']
                        ],
                        'process_error' => $e->getMessage(),
                        'status' => 'error'
                    ];
                    $allSuccessful = false;
                }
            }

            // Return appropriate response based on results
            $statusCode = $allSuccessful ? 200 : 206; // 206 Partial Content if any files failed
            $msg = $allSuccessful ?
                'All CSV files uploaded and processed successfully' :
                'Some CSV files were processed successfully, others failed';

            return response()->json([
                'code' => $statusCode,
                'msg' => $msg,
                'data' => [
                    'files_processed' => count($filesData),
                    'results' => $processResults
                ]
            ], $statusCode);
        } catch (\Throwable $e) {
            // Complete failure
            Log::error('Error in upload and process operation: ' . $e->getMessage());

            return response()->json([
                'code' => 500,
                'msg' => 'Failed to upload and process CSV files',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    private function uploadFiles(Request $request)
    {
        // Validate the request
        $request->validate([
            'files' => 'required',
            'files.*' => 'file|mimes:csv,txt', // Validate each file in the array
            'user_ids' => 'required|array',
            'user_ids.*' => 'string',
        ]);

        // Check if files exist in the request
        if (!$request->hasFile('files')) {
            return [
                'status' => 'error',
                'code' => 400,
                'message' => 'No files uploaded',
                'data' => null
            ];
        }

        $files = $request->file('files');
        $userIds = $request->input('user_ids');
        $filesData = [];

        // Handle both single file and array of files
        if (!is_array($files)) {
            $files = [$files]; // Convert single file to array
        }

        // Check if user_ids count matches files count
        if (count($files) !== count($userIds)) {
            return [
                'status' => 'error',
                'code' => 400,
                'message' => 'Number of files and user IDs must match',
                'data' => null
            ];
        }

        foreach ($files as $index => $file) {
            $userId = $userIds[$index];

            // Generate a unique filename
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $filename = 'analytics_' . $userId . '_' . $originalName . '_' . time() . '.csv';

            // Store the file in the storage/app/analytics directory
            $path = $file->storeAs('analytics', $filename);

            // Get the full path to the stored file
            $fullPath = Storage::path($path);

            Log::info('CSV file uploaded successfully: ' . $path . ' for user: ' . $userId);
            Log::info('Full path: ' . $fullPath);

            $filesData[] = [
                'path' => $path,
                'user_id' => $userId,
                'filename' => $filename,
                'full_path' => $fullPath
            ];
        }

        return [
            'status' => 'success',
            'code' => 200,
            'message' => count($filesData) . ' CSV file(s) uploaded successfully',
            'data' => $filesData
        ];
    }

    // For backward compatibility, keep the old method
    public function uploadAndProcessCsvFile(Request $request)
    {
        // Validate the request
        $request->validate([
            'file' => 'required|file|mimes:csv,txt', // 10MB limit
            'user_id' => 'required|string',
        ]);

        try {
            // Step 1: Upload File
            $uploadResult = $this->uploadSingleFile($request);

            if ($uploadResult['status'] !== 'success') {
                return response()->json([
                    'code' => $uploadResult['code'],
                    'msg' => $uploadResult['message'],
                    'data' => $uploadResult['data']
                ], $uploadResult['code']);
            }

            $fileData = $uploadResult['data'];

            // Step 2: Process File
            try {
                $processResult = $this->processCsvWithModel($fileData['full_path'], $fileData['user_id']);

                // Both upload and process successful
                return response()->json([
                    'code' => 200,
                    'msg' => 'CSV file uploaded and processed successfully',
                    'data' => [
                        'upload' => [
                            'path' => $fileData['path'],
                            'user_id' => $fileData['user_id'],
                            'filename' => $fileData['filename']
                        ],
                        'process' => [
                            'summary' => $processResult['summary'],
                            'details' => $processResult['details'],
                            'output_file' => $processResult['output_file']
                        ]
                    ]
                ], 200);
            } catch (\Throwable $e) {
                // Upload successful but processing failed
                Log::error('Error processing CSV file: ' . $e->getMessage());

                return response()->json([
                    'code' => 206, // Partial Content
                    'msg' => 'CSV file uploaded successfully but processing failed',
                    'data' => [
                        'upload' => [
                            'path' => $fileData['path'],
                            'user_id' => $fileData['user_id'],
                            'filename' => $fileData['filename']
                        ],
                        'process_error' => $e->getMessage()
                    ]
                ], 206);
            }
        } catch (\Throwable $e) {
            // Complete failure
            Log::error('Error in upload and process operation: ' . $e->getMessage());

            return response()->json([
                'code' => 500,
                'msg' => 'Failed to upload and process CSV file',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    private function uploadSingleFile(Request $request)
    {
        // Check if file exists in the request
        if (!$request->hasFile('file')) {
            return [
                'status' => 'error',
                'code' => 400,
                'message' => 'No file uploaded',
                'data' => null
            ];
        }

        $file = $request->file('file');
        $userId = $request->input('user_id');

        // Generate a unique filename
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $filename = 'analytics_' . $userId . '_' . $originalName . '_' . time() . '.csv';

        // Store the file in the storage/app/analytics directory
        $path = $file->storeAs('analytics', $filename);

        // Get the full path to the stored file
        $fullPath = Storage::path($path);

        Log::info('CSV file uploaded successfully: ' . $path . ' for user: ' . $userId);
        Log::info('Full path: ' . $fullPath);

        return [
            'status' => 'success',
            'code' => 200,
            'message' => 'CSV file uploaded successfully',
            'data' => [
                'path' => $path,
                'user_id' => $userId,
                'filename' => $filename,
                'full_path' => $fullPath
            ]
        ];
    }

    private function processCsvWithModel($csvPath, $userId = null)
    {
        // Define paths - use the pre-trained model directory
        $pythonScript = 'C:\xampp\htdocs\course_app\python\process_csv.py'; // Path to your Python script
        $modelDir = 'C:\xampp\htdocs\course_app\app\Models'; // Directory containing the pre-trained model JSON files
        $outputDir = storage_path('app/analytics/results');
        $pythonExecutable = 'G:\Module4\gpu-env\python.exe';

        // Create output directory if it doesn't exist
        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        // Log details before executing the process
        Log::info('Executing Python script with following parameters:');
        Log::info('Python Executable: ' . $pythonExecutable);
        Log::info('Python Script: ' . $pythonScript);
        Log::info('CSV Path: ' . $csvPath);
        Log::info('Model Directory: ' . $modelDir);
        Log::info('Output Directory: ' . $outputDir);
        Log::info('User ID (if provided): ' . ($userId ?? 'None'));

        // Prepare process arguments
        $processArgs = [
            $pythonExecutable,
            $pythonScript,
            $csvPath,
            $modelDir,
            $outputDir
        ];

        // Add user_id as an optional parameter if provided
        if ($userId !== null) {
            $processArgs[] = $userId;
        }

        // Set up the process with environment variables and working directory
        $process = new Process($processArgs);
        $process->setTimeout(300);
        $process->setWorkingDirectory('C:\xampp\htdocs\course_app'); // Set explicit working directory
        $process->setEnv([
            'PYTHONHASHSEED' => '0',           // Disable hash randomization
            'PATH' => getenv('PATH'),          // Pass system PATH
            'SYSTEMROOT' => 'C:\Windows',      // Required for Windows services
        ]);

        // Execute the process and capture output
        $process->run(function ($type, $buffer) {
            if (Process::ERR === $type) {
                Log::error('Python Error: ' . $buffer);
            } else {
                Log::info('Python Output: ' . $buffer);
            }
        });

        // Check if the process was successful
        if (!$process->isSuccessful()) {
            $errorOutput = $process->getErrorOutput();
            Log::error('Python process error: ' . $errorOutput);
            throw new \Exception('Error processing CSV file with model: ' . $errorOutput);
        }

        // Get the output from the Python script
        $output = $process->getOutput();
        Log::info('Python process complete. Output: ' . $output);

        // Extract JSON result from the output
        $resultJson = null;
        $outputFile = null;

        // Try to parse JSON from the output
        if (preg_match('/(\{.*?\})/s', $output, $matches)) {
            $resultJson = json_decode($matches[1], true);
        }

        // Find output file path in the response
        if (preg_match('/Results saved to: (.+)$/m', $output, $matches)) {
            $outputFile = $matches[1];

            // If no JSON result, try loading from the output file
            if ($resultJson === null && file_exists($outputFile)) {
                $resultJson = json_decode(file_get_contents($outputFile), true);
            }
        }

        // Load detailed results from the output file
        $detailedResults = [];
        if ($outputFile && file_exists($outputFile)) {
            $detailedResults = json_decode(file_get_contents($outputFile), true);
        }

        return [
            'summary' => $resultJson,
            'details' => $detailedResults,
            'output_file' => $outputFile
        ];
    }

    // Method to get learning pace for a specific user
    public function getUserLearningPace($userId)
    {
        try {
            // Path to the latest results file (you might want to store this in a database)
            $resultsDir = storage_path('app/analytics/results');
            $files = glob($resultsDir . '/*.json');

            // Sort files by modification time (newest first)
            usort($files, function ($a, $b) {
                return filemtime($b) - filemtime($a);
            });

            if (empty($files)) {
                return response()->json([
                    'code' => 404,
                    'msg' => 'No analysis results found',
                    'data' => null
                ], 404);
            }

            // Get the latest results file for the specific user
            $userData = null;
            $userFound = false;

            // Look through all recent result files to find the user
            foreach ($files as $file) {
                $results = json_decode(file_get_contents($file), true);

                if (isset($results['predictions'])) {
                    foreach ($results['predictions'] as $user) {
                        if ($user['user_id'] == $userId) {
                            $userFound = true;
                            $userData = $user;
                            break 2; // Break out of both loops
                        }
                    }
                }
            }

            if (!$userFound) {
                return response()->json([
                    'code' => 404,
                    'msg' => 'User not found in analysis results',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'code' => 200,
                'msg' => 'User learning pace retrieved successfully',
                'data' => $userData
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Error getting user learning pace: ' . $e->getMessage());

            return response()->json([
                'code' => 500,
                'msg' => 'Failed to retrieve user learning pace',
                'data' => $e->getMessage()
            ], 500);
        }
    }
}
