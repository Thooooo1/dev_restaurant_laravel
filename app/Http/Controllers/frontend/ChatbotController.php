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
        $message = trim($payload['message'] ?? $request->input('message', ''));
        if ($message === '') {
            return response()->json([
                'success' => false,
                'error' => 'Vui lòng nhập tin nhắn.'
            ], 400);
        }

        if (mb_strlen($message) > 1000) {
            return response()->json([
                'success' => false,
                'error' => 'Tin nhắn quá dài (tối đa 1.000 ký tự).'
            ], 422);
        }

        $sessionId = $payload['session_id'] ?? $request->input('session_id') ?: session()->getId();
        $user = Auth::user();

        $context = [
            'session_id' => $sessionId,
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->fullname ?? $user->username,
                'roles' => $user->roles ?? 'customer',
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
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('ChatbotController Error: ' . $e->getMessage());

            $hotline = '0338 990 538';
            try {
                if (class_exists(\App\Http\Controllers\backend\SettingController::class)) {
                    $settings = (new \App\Http\Controllers\backend\SettingController())->getSettings();
                    $hotline = $settings['hotline'] ?? $hotline;
                }
            } catch (\Throwable $ignored) {}

            return response()->json([
                'success' => false,
                'session_id' => $sessionId,
                'error' => "Hệ thống đang bận. Quý khách vui lòng thử lại sau giây lát hoặc liên hệ hotline {$hotline} để được hỗ trợ trực tiếp.",
            ], 500);
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
