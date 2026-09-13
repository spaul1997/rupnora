<?php

namespace App\Http\Controllers;

use App\Support\Catalog;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        return view('pages.contact', [
            'title' => 'Contact Us',
            'faqs' => Catalog::faqs(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:150',
            'message' => 'required|string|max:2000',
        ]);

        return back()->with('success', 'Thank you for reaching out — our team will respond within 24 hours.');
    }
}
