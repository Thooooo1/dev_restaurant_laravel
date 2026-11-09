<?php

namespace App\Services\Chatbot;

use App\Models\ChatbotMessage;
use App\Models\CustomerAiProfile;

class ChatbotMemoryManager
{
    /**
     * Lưu tin nhắn vào cơ sở dữ liệu để phục vụ hội thoại nhiều lượt (Multi-turn Chat)
     */
    public function saveMessage(string $sessionId, ?int $userId, string $role, string $content, ?array $toolCalls = null, ?array $toolOutput = null): void
    {
        try {
            ChatbotMessage::create([
                'session_id' => $sessionId,
                'user_id' => $userId,
                'role' => $role,
                'content' => $content,
                'tool_calls' => $toolCalls,
                'tool_output' => $toolOutput,
            ]);
        } catch (\Throwable $e) {}
    }

    /**
     * Cập nhật hồ sơ AI của khách hàng
     */
    public function updateCustomerProfile(?int $userId, ?string $phone, array $data): void
    {
        if (empty($userId) && empty($phone)) {
            return;
        }

        try {
            $query = CustomerAiProfile::query();
            if ($userId) {
                $query->where('user_id', $userId);
            } elseif ($phone) {
                $query->where('phone', $phone);
            }

            $profile = $query->first();
            if (!$profile) {
                $profile = new CustomerAiProfile();
                $profile->user_id = $userId;
                $profile->phone = $phone;
            }

            if (!empty($data['food_preferences'])) {
                $existing = (array)($profile->food_preferences ?? []);
                $merged = array_unique(array_merge($existing, (array)$data['food_preferences']));
                $profile->food_preferences = array_slice($merged, -15);
            }

            if (!empty($data['allergies'])) {
                $existingAllergies = (array)($profile->allergies ?? []);
                $profile->allergies = array_unique(array_merge($existingAllergies, (array)$data['allergies']));
            }

            if (isset($data['budget_level'])) {
                $profile->budget_level = $data['budget_level'];
            }

            $profile->last_interaction_at = now();
            $profile->save();
        } catch (\Throwable $e) {}
    }
}
