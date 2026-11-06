<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsurePaymentDemoMode
{
    /**
     * Chỉ cho phép truy cập các endpoint giả lập khi ở chế độ demo
     */
    public function handle(Request $request, Closure $next)
    {
        if (config('payment.mode', 'demo') !== 'demo') {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Endpoint giả lập thanh toán chỉ được phép hoạt động ở chế độ PAYMENT_MODE=demo.'
                ], 404);
            }

            abort(404, 'Trang giả lập thanh toán đã bị khóa trên môi trường Production.');
        }

        return $next($request);
    }
}
