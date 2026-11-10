<?php

namespace App\Services\Chatbot;

use App\Models\ChatbotMessage;
use App\Models\CustomerAiProfile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ChatbotMemoryManager
{
    /**
     * Luu tin nhan vao co so du lieu phuc vu hoi thoai da luot (Multi-turn Chat)
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
        } catch (\Throwable $e) {
            Log::warning('Failed to save chatbot message memory: ' . $e->getMessage());
        }
    }

    /**
     * Tai danh sach lich su tin nhan gan nhat cua phien theo thu tu thoi gian
     */
    public function loadRecentMessages(string $sessionId, int $limit = 10): Collection
    {
        try {
            if (Schema::hasTable('chatbot_messages')) {
                return ChatbotMessage::where('session_id', $sessionId)
                    ->orderBy('id', 'desc')
                    ->limit($limit)
                    ->get()
                    ->reverse()
                    ->values();
            }
        } catch (\Throwable $e) {
            Log::warning('Chatbot memory load failed: ' . $e->getMessage());
        }

        return collect([]);
    }

    /**
     * Truy van ho so so thich / di ung (Long-term profile) cua khach hang
     */
    public function getCustomerProfile(?int $userId, ?string $phone = null): ?CustomerAiProfile
    {
        try {
            if (Schema::hasTable('customer_ai_profiles')) {
                if (!empty($userId)) {
                    return CustomerAiProfile::where('user_id', $userId)->first();
                } elseif (!empty($phone)) {
                    return CustomerAiProfile::where('phone', $phone)->first();
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Customer profile load failed: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Cap nhat ho so AI cua khach hang (chong dau doc thong tin tu khach vang lai)
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
        } catch (\Throwable $e) {
            Log::warning('Customer profile update failed: ' . $e->getMessage());
        }
    }

    /**
     * Xoa sach lich su phien lam viec
     */
    public function clearSession(string $sessionId): void
    {
        try {
            if (Schema::hasTable('chatbot_messages')) {
                ChatbotMessage::where('session_id', $sessionId)->delete();
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to clear chatbot session: ' . $e->getMessage());
        }
    }
}
