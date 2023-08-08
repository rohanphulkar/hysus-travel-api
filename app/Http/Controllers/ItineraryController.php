<?php

namespace App\Http\Controllers;

use App\Models\Itinerary;
use Illuminate\Http\Request;

class ItineraryController extends Controller
{
    public function index(){
        $itineraries = Itinerary::all();
        return response()->json([
            'message'=>'Itineraries retrieved successfully',
            'itineraries'=>$itineraries
        ],200);
    }

    public function getItineraryById(string $id){
        $itinerary = Itinerary::find($id);
        if (!$itinerary) {
            return response()->json([
                'message' => 'Itinerary not found',
            ], 404);
        }
        return response()->json([
            'message'=>'Itinerary retrieved successfully',
            'itinerary'=>$itinerary
        ],200);
    }

    public function getItineraryByUser(){
        $user = auth()->user()->id;
        $itineraries = Itinerary::where('user_id',$user);

        return response()->json([
            'message'=>'Itineraries retrieved successfully',
            'itineraries'=>$itineraries
        ],200);
    }

    public function getItineraryByBookingId(string $id){
        $itinerary = Itinerary::where('booking_id',$id)->first();

        return response()->json([
            'message'=>'Itinerary retrieved successfully',
            'itinerary'=>$itinerary
        ],200);
    }

    public function destroy(string $id){
        Itinerary::find($id)->delete();
        return response()->json([
            'message'=>'Itinerary deleted successfully',
        ],200);
    }
}
