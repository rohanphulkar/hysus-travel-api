<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Itinerary;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\StripeClient;

class BookingController extends Controller
{
    public function index(){
        $bookings = Booking::all();
        return response()->json([
            'message'=>'Bookings retrieved successfully',
            'bookings'=>$bookings
        ],200);
    }

    public function getBookingById(string $id){
        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json([
                'message' => 'Booking not found',
            ], 404);
        }

        return response()->json([
            'message'=>'Booking retrieved successfully',
            'booking'=>$booking
        ],200);
    }

    public function getBookingsOfUser(){
        $user = auth()->user()->id;
        $bookings = Booking::where('user_id',$user);
        return response()->json([
            'message'=>'Bookings retrieved successfully',
            'bookings'=>$bookings
        ],200);
    }

    public function getBookingsByPackage(string $id){
        $bookings = Booking::where('package_id',$id);
        return response()->json([
            'message'=>'Bookings retrieved successfully',
            'bookings'=>$bookings
        ],200);
    }

    public function cancelBooking(string $id){
        $booking = Booking::find($id);
        if (!$booking) {
            return response()->json([
                'message' => 'Booking not found',
            ], 404);
        }

        $booking->booking_status='cancel';
        $booking->save();

        return response()->json([
            'message'=>'Booking retrieved successfully',
            'booking'=>$booking
        ],200);
    }

    public function createBooking(Request $request){
        $validator = Validator::make($request->all(),[
            'package_id'=>'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'message'=>'Validation Error',
                'error'=>$validator->errors()
            ],400);
        }

        $package = Package::find($request->package_id);

        $booking = new Booking();
        $booking->user_id = auth()->user()->id;
        $booking->package_id = $request->package_id;
        $booking->total_amount = $package->price;
        $booking->paid_amount = 0;
        $booking->booking_status = 'pending';
        $booking->payment_status = 'pending';
        $booking->save();

        // Process payment using Stripe
        Stripe::setApiKey('sk_test_51Ncj2ASBYuAkX7FEihysDOWWpdvjvgllpuxjwj8guywdFIkiust0uYlEJUerCYdf16MaLlal9mF1975TgTWSbnow00dBMexfbB');

        try{
            $paymentIntent = PaymentIntent::create([
                'amount'=>($booking->total_amount*1/2)*100,
                'currency'=>'inr'
            ]);
			
			$booking->payment_id = $paymentIntent->id;
			$booking->save();

            $output = [
                'clientSecret' => $paymentIntent->client_secret,
            ];
            return response()->json($output, 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Payment processing failed',
            ], 500);
        }
    }
	
	public function confirmPayment(Request $request)
    {
        $paymentIntentId = $request->payment_id; // Assuming you pass the payment intent ID from the client

        Stripe::setApiKey('sk_test_51Ncj2ASBYuAkX7FEihysDOWWpdvjvgllpuxjwj8guywdFIkiust0uYlEJUerCYdf16MaLlal9mF1975TgTWSbnow00dBMexfbB');

        try {
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
			$booking = Booking::where('payment_id',$paymentIntent->id)->first();
			
            if ($paymentIntent->status === 'succeeded') {
				$itinerary = Itinerary::create([
				'package_id'=>$booking->package_id,
				'booking_id'=>$booking->id,
				'user_id'=>$booking->user_id,
				'date_of_itinerary'=>Carbon\Carbon::now()
				]);
				$booking->payment_status = 'success';
				$booking->save();
                return response()->json([
                    'message' => 'Payment was successful',
                    'payment_intent' => $paymentIntent,
                ], 200);
            } else {
				$booking->payment_status = 'failed';
				$booking->save();
                return response()->json([
                    'message' => 'Payment was not successful',
                    'payment_intent' => $paymentIntent,
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error checking payment status',
            ], 500);
        }
    }
}
