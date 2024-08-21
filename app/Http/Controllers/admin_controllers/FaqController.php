<?php

namespace App\Http\Controllers\admin_controllers;

use App\Models\faq;
use Illuminate\Http\Request;

class FaqController
{
    public function create()
    {
        return view('admin_views/manage_faq/add_faq');
    }
    public function store(Request $request)
    {
//        dd($request);
        $validatedData = $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
        ]);

        $faq =new Faq();

        $faq->question=$validatedData['question'];
        $faq->answer=$validatedData['answer'];

        $faq->save();
    }

    public function index()
    {
        $faqs=Faq::all();
        return view('admin_views/manage_faq/edit_faq',[
            'faqs'=>$faqs
        ]);
    }
    public function destroy(Request $request)
    {
        $faq=Faq::findOrFail($request->id);
        $faq->delete();
        return response()->json(['success' => 'Faq deleted successfully']);
    }
    public function edit($id)
    {
        $faq=Faq::find($id);
        return view('admin_views/manage_faq/edit_faq_form',[
            'faq'=>$faq
        ]);
    }
    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
        ]);

        $faq =Faq::findOrFail($request->input('faqId'));

        $faq->question=$validatedData['question'];
        $faq->answer=$validatedData['answer'];

        $faq->save();
        return response()->json(['success' => 'Faq updated successfully']);
    }
}
