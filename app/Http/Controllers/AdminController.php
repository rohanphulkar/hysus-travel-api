<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:admins',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = Admin::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        if ($request->filled('phone')) {
            $user->phone = $request->phone;
            $user->save();
        }

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $user = Admin::where('email',$request->email)->first();
        $token = auth()->guard('admin-api')->attempt($credentials);
       if ($token) {
            return response()->json([
                'message' => 'User logged in successfully',
                'user' => $user,
                'token' => $token,
            ], 200);
        } else {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'User logged out successfully',
        ], 200);
    }

    public function profile()
    {
        return response()->json([
            'message' => 'Profile retrieved successfully',
            'user' => auth()->user(),
        ], 200);
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = Admin::where('email', $request->input('email'))->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        $token = Str::random(60);
        $user->password_reset_token = $token;
        
        $user->save();

        $resetUrl = config('services.frontend.url') . '/reset-password/' . $token;

        Mail::send('emails.reset_password', ['url' => $resetUrl], function ($message) use ($user) {
            $message->to($user->email)->subject('Reset Password');
        });

        return response()->json([
            'message' => 'Password reset email sent',
        ], 200);
    }

    public function resetPassword(Request $request, $token)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = Admin::where('password_reset_token', $token)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Invalid token',
            ], 400);
        }

        $user->password = Hash::make($request->input('password'));
        $user->password_reset_token = null;
        $user->save();

        return response()->json([
            'message' => 'Password reset successfully',
        ], 200);
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = auth()->user();

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect',
            ], 400);
        }

        $user->password = Hash::make($request->input('new_password'));
        $user->save();

        return response()->json([
            'message' => 'Password changed successfully',
        ], 200);
    }

	public function getAllUser(){
        $users = User::all();
        return response()->json($users,200);
    }
    public function getAllAdmin(){
        $admins = Admin::all();
        return response()->json($admins,200);
    }

	public function destroy(string $id){
        Admin::find($id)->delete();
        return response()->json([
        'message'=>'admin deleted successfully'
    ],200);
    }

    public function getTotalBookingsForMonths($month)
    {
        $months = $month; // Default to 2 months
        $year = date('Y'); // Default to current year

        $currentDate = Carbon::now();
        $dateRange = [];
        for ($i = 0; $i < $months; $i++) {
            $dateRange[] = $currentDate->format('Y-m');
            $currentDate->subMonth();
        }

        $totalBookings = DB::table('bookings')
            ->select(DB::raw("YEAR(created_at) as year"), DB::raw("MONTH(created_at) as month"), DB::raw('count(*) as count'))
            ->whereYear('created_at', $year)
            ->whereIn(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"), $dateRange)
            ->groupBy('year', 'month')
            ->get();

        return response()->json(['total_bookings' => $totalBookings]);
    }
    public function dashboard($month){
        $totalAmounts = $this->getTotalBookingsForMonths($month);
        $totalUsers = User::count();
        $totalAdmins = Admin::count();

        $stats = ['total_amounts'=>$totalAmounts,'total_users'=>$totalUsers,'total_admins'=>$totalAdmins];

        return response()->json([
            'message'=>'Data retrieved successfully',
            'stats'=>$stats
        ],200);
    }

}
