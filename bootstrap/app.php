<?php

use App\Http\Middleware\AdminAccess;
use App\Http\Middleware\LoginAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $trustedProxies = env('TRUSTED_PROXIES');
        if ($trustedProxies === '*') {
            $middleware->trustProxies(at: '*');
        } elseif (!empty($trustedProxies)) {
            $middleware->trustProxies(at: array_map('trim', explode(',', $trustedProxies)));
        } else {
            $middleware->trustProxies(at: ['127.0.0.1', '::1']);
        }
        $middleware->validateCsrfTokens(except: [
            'api/webhook/*',
            'api/webhook',
            'webhook/*',
            'webhook',
            'api/payment/*',
            'payment/*',
            'checkout/ipn',
            'tu-van/*',
            'tu-van',
            'chatbot/*',
            'chatbot',
        ]);
        $middleware->alias([
            'login-admin' => LoginAdmin::class,
            'admin-access' => AdminAccess::class,
            'payment-demo' => \App\Http\Middleware\EnsurePaymentDemoMode::class,
        ]);
        $middleware->appendToGroup('login-admin', [
            LoginAdmin::class,
        ]);
        $middleware->appendToGroup('admin-access', [
            AdminAccess::class,
        ]);
    })
    
    ->withExceptions(function (Exceptions $exceptions) {
        // Catch 419 Page Expired (CSRF TokenMismatchException)
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() === 419) {
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Phiên làm việc đã hết hạn. Vui lòng làm mới trang.',
                        'csrf_token' => csrf_token()
                    ], 419);
                }

                if ($request->is('admin*')) {
                    return redirect()->route('admin.login')
                        ->withInput($request->except('_token', 'password'))
                        ->with('error', 'Phiên làm việc đã hết hạn do để trang quá lâu. Hệ thống đã tự động làm mới mã bảo mật, vui lòng đăng nhập lại.');
                }

                return redirect()->back()
                    ->withInput($request->except('_token', 'password'))
                    ->with('error', 'Phiên làm việc đã hết hạn. Hệ thống đã tự động làm mới, vui lòng thử lại.');
            }
        });

        // Fallback for response status code 419
        $exceptions->respond(function ($response, Throwable $e, Request $request) {
            if ($response->getStatusCode() === 419) {
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Phiên làm việc đã hết hạn. Vui lòng làm mới trang.',
                        'csrf_token' => csrf_token()
                    ], 419);
                }

                if ($request->is('admin*')) {
                    return redirect()->route('admin.login')
                        ->withInput($request->except('_token', 'password'))
                        ->with('error', 'Phiên làm việc đã hết hạn do để trang quá lâu. Hệ thống đã tự động làm mới mã bảo mật, vui lòng đăng nhập lại.');
                }

                return redirect()->back()
                    ->withInput($request->except('_token', 'password'))
                    ->with('error', 'Phiên làm việc đã hết hạn. Hệ thống đã tự động làm mới, vui lòng thử lại.');
            }

            return $response;
        });
    })->create();
