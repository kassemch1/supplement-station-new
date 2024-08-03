<?php

namespace App\Http\Controllers\admin_controllers;


use App\Models\Option;
use Illuminate\Http\Request;

class OptionController extends Controller
{
    public function index()
    {
        $options = Option::all();
        return view('admin_views/manage_option/edit_option', [
            'options' => $options
        ]);
    }

    public function create()
    {
        return view('admin_views/manage_option/add_option');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'option_name' => 'required|string|max:255'
        ]);

        $option = new Option();
        $option->option_name = $validatedData['option_name'];
        $option->save();

        return response()->json(['success' => 'Option added successfully']);
    }

    public function edit($id)
    {
        $option = Option::find($id);
       
        return view('admin_views/manage_option/edit_option_form', [
            'option' => $option
        ]);
    }

    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'option_name' => 'required|string|max:255'
        ]);

        $option = Option::findOrFail($request->input('option_id'));
        $option->option_name = $validatedData['option_name'];
        $option->save();

        return response()->json(['success' => 'Option updated successfully']);
    }

    public function destroy(Request $request)
    {
        $option = Option::findOrFail($request->option_id);
        $option->delete();
        return response()->json(['success' => 'Option deleted successfully']);
    }

    
}
