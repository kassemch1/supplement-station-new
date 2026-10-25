<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Option;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// Option types (Flavor, Size...); their values are set per product (ProductOptionController)
class OptionController extends Controller
{
    private const RULES = ['option_name' => 'required|string|max:255'];

    public function index()
    {
        return view('admin_views/manage_option/edit_option', ['options' => Option::all()]);
    }

    public function create()
    {
        return view('admin_views/manage_option/add_option');
    }

    public function store(Request $request): JsonResponse
    {
        Option::create($request->validate(self::RULES));

        return response()->json(['success' => 'Option added successfully']);
    }

    public function edit(int $id)
    {
        return view('admin_views/manage_option/edit_option_form', ['option' => Option::findOrFail($id)]);
    }

    public function update(Request $request): JsonResponse
    {
        Option::findOrFail($request->input('option_id'))->update($request->validate(self::RULES));

        return response()->json(['success' => 'Option updated successfully']);
    }

    public function destroy(Request $request): JsonResponse
    {
        Option::findOrFail($request->option_id)->delete();

        return response()->json(['success' => 'Option deleted successfully']);
    }
}
