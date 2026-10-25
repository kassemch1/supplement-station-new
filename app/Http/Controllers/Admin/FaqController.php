<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    private const RULES = [
        'question' => 'required|string|max:255',
        'answer' => 'required|string',
    ];

    public function index()
    {
        return view('admin_views/manage_faq/edit_faq', ['faqs' => Faq::all()]);
    }

    public function create()
    {
        return view('admin_views/manage_faq/add_faq');
    }

    public function store(Request $request): JsonResponse
    {
        Faq::create($request->validate(self::RULES));

        return response()->json(['success' => 'Faq added successfully']);
    }

    public function edit(int $id)
    {
        return view('admin_views/manage_faq/edit_faq_form', ['faq' => Faq::findOrFail($id)]);
    }

    public function update(Request $request): JsonResponse
    {
        Faq::findOrFail($request->input('faqId'))->update($request->validate(self::RULES));

        return response()->json(['success' => 'Faq updated successfully']);
    }

    public function destroy(Request $request): JsonResponse
    {
        Faq::findOrFail($request->id)->delete();

        return response()->json(['success' => 'Faq deleted successfully']);
    }
}
