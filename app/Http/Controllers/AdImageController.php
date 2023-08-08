<?php

namespace App\Http\Controllers;

use App\Models\AdImage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $images = AdImage::all();
        return response()->json($images,200);
    }

   
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'image'=>'required|mimes:jpeg,jpg,png,gif,webp'
        ]);

        $imageName = Str::random(32) . '.' . $request->image->extension();
        $adimage = AdImage::create(['image'=>$imageName]);
        $request->image->move(public_path('adimages'),$imageName);


        return response()->json([
            'message'=>'image has been added',
            'images'=>$adimage
        ],200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $image = AdImage::where('id',$id)->first();
        return response()->json($image,200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $request->validate([
            'image'=>'required|mimes:jpeg,jpg,png,gif,webp'
        ]);

        $adimage = AdImage::where('id',$id)->first();

        if(!$adimage){
            return response()->json([
                'message'=>'image not found'
            ],404);
        }

        $imageName = Str::random(32) . '.' . $request->image->extension();
        $request->image->move(public_path('adimages'),$imageName);
        $adimage->image= $imageName;
        $adimage->save();
    

        return response()->json([
            'message'=>'image has been updated',
            'images'=>$adimage
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $image = AdImage::where('id',$id)->first();

        if(!$image){
            return response()->json([
                'message'=>'image not found'
            ],404);
        }
        $image->delete();
        return response()->json([
            'message'=>'image has been deleted'
        ],200);
        
    }
}
