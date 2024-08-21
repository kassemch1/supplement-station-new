<?php

namespace App\Http\Controllers\admin_controllers;

use App\Http\Controllers\admin_controllers\Controller;
use App\Models\plane;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PlaneController extends Controller
{
    public function create()
    {
        return view('admin_views/manage_planes/add_planes');
    }

    public function store(Request $request)
    {
//        dd($request);
        $validatedData = $request->validate([
            'type' => 'required|string|max:255',
            'price' => 'required|numeric',
            'point1' => 'nullable|string|max:255',
            'point2' => 'nullable|string|max:255',
            'point3' => 'nullable|string|max:255',
            'point4' => 'nullable|string|max:255',
            'point5' => 'nullable|string|max:255',


        ]);

        $plane = new plane();

        $plane->type = $validatedData['type'];
        $plane->price = $validatedData['price'];
        $plane->point1 = $validatedData['point1'];
        $plane->point2 = $validatedData['point2'];
        $plane->point3 = $validatedData['point3'];
        $plane->point4 = $validatedData['point4'];
        $plane->point5 = $validatedData['point5'];



        $plane->save();
    }

    public function index()
    {
        $planes = plane::all();
        return view('admin_views/manage_planes/edit_planes', [
            'planes' => $planes
        ]);
    }

    public function destroy(Request $request)
    {
        $plane = plane::findOrFail($request->id);
        $plane->delete();
        return response()->json(['success' => 'Point deleted successfully']);
    }

    public function edit($id)
    {
        $plane = plane::find($id);
        return view('admin_views/manage_planes/edit_planes_form', [
            'plane' => $plane
        ]);
    }

    public function update(Request $request)
    {
//        dd($request->all());
        $validatedData = $request->validate([
            'type' => 'required|string|max:255',
            'price' => 'required|numeric',
            'point1' => 'nullable|string|max:255',
            'point2' => 'nullable|string|max:255',
            'point3' => 'nullable|string|max:255',
            'point4' => 'nullable|string|max:255',
            'point5' => 'nullable|string|max:255',

        ]);

        $plane = plane::findOrFail($request->input('planId'));

        $plane->type = $validatedData['type'];
        $plane->price = $validatedData['price'];
        $plane->point1 = $validatedData['point1'];
        $plane->point2 = $validatedData['point2'];
        $plane->point3 = $validatedData['point3'];
        $plane->point4 = $validatedData['point4'];
        $plane->point5 = $validatedData['point5'];


        $plane->save();
        return response()->json(['success' => 'Point updated successfully']);
    }
}
