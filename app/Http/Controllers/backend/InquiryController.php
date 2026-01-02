<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inquiries;

class InquiryController extends Controller
{
    public function index(Request $request)
    {
        $statusFilter = $request->query('status', 0);
        $perPage = 6;

        $inquiries = Inquiries::when($statusFilter != 0, function ($query) use ($statusFilter) {
                return $query->where('status', $statusFilter);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return view('backend.inquiry.index', compact('inquiries', 'statusFilter'));
    }

    public function show($id)
    {
        $inquiry = Inquiries::findOrFail($id);
        return view('backend.inquiry.show', compact('inquiry'));
    }

    public function updateStatus(Request $request, $id)
    {
        $inquiry = Inquiries::findOrFail($id);

        $request->validate([
            'status' => 'required|integer|min:1|max:3',
        ]);

        $inquiry->status = $request->status;
        $inquiry->save();

        return redirect()->route('admin.inquiry.index')->with('success', 'Cập nhật trạng thái thành công');
    }
}
