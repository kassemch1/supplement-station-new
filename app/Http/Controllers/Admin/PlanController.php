<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PlanRequest;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// The price plans shown on the home page
class PlanController extends Controller
{
    public function index()
    {
        return view('admin_views/manage_planes/edit_planes', ['planes' => Plan::all()]);
    }

    public function create()
    {
        return view('admin_views/manage_planes/add_planes');
    }

    public function store(PlanRequest $request): JsonResponse
    {
        Plan::create($request->validated());

        return response()->json(['success' => 'Plan added successfully']);
    }

    public function edit(int $id)
    {
        return view('admin_views/manage_planes/edit_planes_form', ['plane' => Plan::findOrFail($id)]);
    }

    public function update(PlanRequest $request): JsonResponse
    {
        Plan::findOrFail($request->input('planId'))->update($request->validated());

        return response()->json(['success' => 'Point updated successfully']);
    }

    public function destroy(Request $request): JsonResponse
    {
        Plan::findOrFail($request->id)->delete();

        return response()->json(['success' => 'Point deleted successfully']);
    }
}
