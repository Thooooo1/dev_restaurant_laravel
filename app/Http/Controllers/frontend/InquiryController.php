<?php

namespace App\Http\Controllers\frontend;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Inquiries;

class InquiryController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'phone' => 'required|max:20',
            'message' => 'required',
        ]);

        Inquiries::create($request->all());

        return back()->with('success', 'Gửi yêu cầu thành công!');
    }
}

