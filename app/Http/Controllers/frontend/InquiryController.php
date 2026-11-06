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

        $messageContent = $request->input('message');
        if ($request->filled('title')) {
            $messageContent = "[Tiêu đề: " . $request->input('title') . "] " . $messageContent;
        }
        if ($request->filled('department')) {
            $messageContent = "[Bộ phận: " . $request->input('department') . "] " . $messageContent;
        }
        if ($request->filled('email')) {
            $messageContent .= " (Email: " . $request->input('email') . ")";
        }

        Inquiries::create([
            'name' => $request->input('name'),
            'phone' => $request->input('phone'),
            'message' => $messageContent,
            'status' => 1,
        ]);

        return back()->with('success', 'Cảm ơn quý khách! Thông tin liên hệ đã được gửi thành công. CIVILIZE sẽ phản hồi trong thời gian sớm nhất.');
    }
}

