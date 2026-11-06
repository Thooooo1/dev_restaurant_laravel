<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\ChatbotService;
use App\Models\ChatbotMessage;

class ChatbotController extends Controller
{
    protected ChatbotService $chatbotService;

    public function __construct(ChatbotService $chatbotService)
    {
        $this->chatbotService = $chatbotService;
    }

    /**
     * Handle incoming chat message with Multi-turn Memory & Tool Calling
     */
    public function sendMessage(Request $request)
    {
        $payload = $request->all();
        if (empty($payload) && !empty($request->getContent())) {
            $payload = json_decode($request->getContent(), true) ?: [];
        }
        $message = $payload['message'] ?? $request->input('message');
        if (empty($message)) {
            return response()->json([
                'success' => false,
                'error' => 'Vui lòng nhập tin nhắn.'
            ], 400);
        }

        $sessionId = $payload['session_id'] ?? $request->input('session_id') ?: session()->getId();
        $user = Auth::user();

        $context = [
            'session_id' => $sessionId,
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->fullname ?? $user->username,
                'phone' => $user->phone ?? null,
                'email' => $user->email ?? null,
            ] : null,
            'table_name' => session('table_name'),
            'table_id' => session('table_id'),
        ];

        try {
            $response = $this->chatbotService->reply($message, $context);

            return response()->json([
                'success' => true,
                'session_id' => $sessionId,
                'reply' => $response['reply'] ?? '',
                'products' => $response['products'] ?? [],
                'quick_replies' => $response['quick_replies'] ?? [],
                'tool_calls' => $response['tool_calls'] ?? null,
                'tool_output' => $response['tool_output'] ?? null,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('ChatbotController Error: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return response()->json([
                'success' => true,
                'session_id' => $sessionId,
                'reply' => "Dạ em chào anh/chị ạ! Hôm nay nhà hàng CIVILIZE có các món đặc sản như Lẩu bắp bò nhúng dấm, Bò la hũ nướng tảng và Sườn nướng BBQ rất ngon miệng. Anh/chị có thể đặt bàn trước ngay trên web để nhận ưu đãi 10% hoặc gọi Hotline 0338 990 538 nhé ạ!",
                'products' => [],
                'quick_replies' => [
                    ['title' => 'Đặt bàn trước', 'payload' => 'Tôi muốn đặt bàn'],
                    ['title' => 'Xem thực đơn', 'payload' => 'Xem thực đơn', 'url' => route('site.product')],
                ],
            ]);
        }
    }

    /**
     * Get initial greeting and starter questions
     */
    public function getSuggestions(Request $request)
    {
        $sessionId = $request->input('session_id') ?: session()->getId();
        $user = Auth::user();
        $userName = $user ? ($user->fullname ?? $user->username) : null;

        $greeting = $userName 
            ? "Dạ em chào anh/chị {$userName}! Chào mừng anh/chị đã quay trở lại với CIVILIZE Restaurant.\nEm có thể hỗ trợ anh/chị chọn món ngon hoặc đặt bàn trước hôm nay ạ."
            : "Dạ em chào anh/chị! Chào mừng anh/chị đến với CIVILIZE Restaurant.\nEm có thể hỗ trợ anh/chị xem thực đơn món ngon hoặc đặt bàn trước hôm nay ạ.";

        return response()->json([
            'success' => true,
            'session_id' => $sessionId,
            'greeting' => $greeting,
            'quick_replies' => [
                ['title' => 'Gợi ý món ngon hôm nay', 'payload' => 'Gợi ý món ngon hôm nay'],
                ['title' => 'Tôi muốn đặt bàn', 'payload' => 'Tôi muốn đặt bàn'],
                ['title' => 'Kiểm tra đơn hàng', 'payload' => 'Tra cứu đơn hàng'],
                ['title' => 'Địa chỉ & Giờ mở cửa', 'payload' => 'Địa chỉ và giờ mở cửa của quán'],
                ['title' => 'Ưu đãi và Freeship', 'payload' => 'Nhà hàng có ưu đãi gì không?'],
            ]
        ]);
    }

    /**
     * Clear conversation history for the current session
     */
    public function clearSession(Request $request)
    {
        $sessionId = $request->input('session_id') ?: session()->getId();
        ChatbotMessage::where('session_id', $sessionId)->delete();
        session()->forget('chatbot_state');

        return response()->json([
            'success' => true,
            'message' => 'Đã làm mới cuộc trò chuyện.'
        ]);
    }
}
