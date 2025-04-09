<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Stripe\Webhook;
use Stripe\Customer;
use Stripe\Price;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Exception\UnexpectedValueException;
use Stripe\Exception\SignatureVerificationException;
use App\Models\Course;
use App\Models\Order;
use Razorpay\Api\Api;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{


    public function checkout(Request $request)
    {
        try {
            $courseId = $request->id;
            $user = $request->user();
            $token = $user->token;

            $razorpay = new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET'));

            $searchCourse = Course::where('id', "=", $courseId)->first();

            if (empty($searchCourse)) {
                return response()->json(
                    [
                        'code' => 409,
                        'status' => false,
                        'msg' => 'No course found',
                        'data' => null
                    ],
                    200
                );
            }

            $orderMap = [
                "course_id" => $courseId,
                "user_token" => $token,
                "status" => 1
            ];
            $orderRes = Order::where($orderMap)->first();

            if (!empty($orderRes)) {
                return response()->json(
                    [
                        'code' => 200,
                        'status' => false,
                        'msg' => 'Course already purchased',
                        'data' => null
                    ],
                    200
                );
            }

            $your_domain = env('APP_URL');
            $success_url = $your_domain . '/success';
            $cancel_url = $your_domain . '/cancel';

            $map = [
                'user_token' => $token,
                'course_id' => $courseId,
                'total_amount' => $searchCourse->price,
                'status' => 0,
                'created_at' => Carbon::now()
            ];

            $orderNum = Order::insertGetId($map);

            $razorpayOrder = $razorpay->order->create([
                'amount' => intval($searchCourse->price * 100),
                'currency' => 'INR',
                'notes' => [
                    'order_num' => $orderNum,
                    'user_token' => $token,
                    'course_id' => $courseId,
                ],
                'receipt' => 'receipt_' . $orderNum
            ]);

            $orderData = [
                'order_id' => $razorpayOrder->id,
                'amount' => $razorpayOrder->amount,
                'currency' => $razorpayOrder->currency,
                'razorpay_key' => env('RAZORPAY_KEY'),
                'course_name' => $searchCourse->name,

                'success_url' => $success_url,
                'cancel_url' => $cancel_url
            ];


            return response()->json([
                'code' => 200,
                'status' => true,
                'data' => $orderData
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'code' => 500,
                'status' => false,
                'msg' => 'An error occurred',
                'data' => $th->getMessage()
            ], 500);
        }
    }


    public function webGoHooks()
    {
        Log::info('Start here');
        $razorpay = new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET'));
        $endPointSecret = 'Pushkaraj2410?!';
        $payload = @file_get_contents('php://input');
        $sigHeader = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'];
        $event = null;
        Log::info('Set up buffer and handshake done!');

        try {
            $razorpay->utility->verifyWebhookSignature($payload, $sigHeader, $endPointSecret);
            $event = json_decode($payload, true);
        } catch (\Razorpay\Api\Errors\SignatureVerificationError $e) {
            Log::info('SignatureVerificationError ' . $e);
            http_response_code(400);
            exit();
        } catch (\Exception $e) {
            Log::info('UnexpectedValueException ' . $e);
            http_response_code(400);
            exit();
        }

        if ($event['event'] == 'payment.captured') {
            $order = $event['payload']['payment']['entity'];
            $metadata = $order['notes'];
            $orderNum = $metadata['order_num'];
            $userToken = $metadata['user_token'];
            Log::info('Order Number ' . $orderNum);

            $map = [];
            $map['status'] = 1;
            $map['updated_at'] = Carbon::now();
            $whereMap = [];
            $whereMap['user_token'] = $userToken;
            $whereMap['id'] = $orderNum;
            Order::where($whereMap)->update($map);

            $courseId = $metadata['course_id'];
            $course = Course::find($courseId);
            if ($course) {
                $course->increment('purchase_count');
            }
        }

        http_response_code(200);
    }

    public function getLatestCourses(Request $request)
    {
        try {
            $userToken = $request->user_token;

            // Retrieve the latest 3 courses with their details
            $latestCourses = DB::table('orders')
                ->join('courses', 'orders.course_id', '=', 'courses.id') // Join with courses table
                ->where('orders.user_token', $userToken)
                ->where('orders.status', 1)
                ->orderBy('orders.created_at', 'desc')
                ->limit(3)
                ->select('orders.*', 'courses.name as course_name', 'courses.description', 'courses.thumbnail') // Select desired columns
                ->get();

            return response()->json([
                'code' => 200,
                'status' => true,
                'data' => $latestCourses
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'code' => 500,
                'status' => false,
                'msg' => 'An error occurred',
                'data' => $th->getMessage()
            ], 500);
        }
    }
}
