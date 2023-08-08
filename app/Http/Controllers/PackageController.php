<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;



class PackageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $packages = Package::all();
        return response()->json([
            'message'=>'Packages retrieved successfully',
            'packages'=>$packages
        ],200);
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validator = Validator::make($request->all(),[
            'name' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'available_slots' => 'required|integer',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(),400);
        }

        $package = Package::create([
            'name'=>$request->name,
            'description'=>$request->description,
            'price'=>$request->price,
            'start_date'=>$request->start_date,
            'end_date'=>$request->end_date,
            'available_slots'=>$request->available_slots,
        ]);

        return response()->json([
            'message'=>'Package created successfully',
            'package'=>$package
        ],201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $package = Package::find($id);

        if (!$package) {
            return response()->json([
                'message' => 'Package not found',
            ], 404);
        }

        return response()->json([
            'message' => 'Package retrieved successfully',
            'package' => $package,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $package = Package::find($id);

        if (!$package) {
            return response()->json([
                'message' => 'Package not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'available_slots' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $package->name = $request->name;
        $package->description = $request->description;
        $package->price = $request->price;
        $package->start_date = $request->start_date;
        $package->end_date = $request->end_date;
        $package->available_slots = $request->available_slots;
        $package->save();

        return response()->json([
            'message' => 'Package updated successfully',
            'package' => $package,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $package = Package::find($id);

        if (!$package) {
            return response()->json([
                'message' => 'Package not found',
            ], 404);
        }

        $package->delete();

        return response()->json([
            'message' => 'Package deleted successfully',
        ], 200);
    }
}
