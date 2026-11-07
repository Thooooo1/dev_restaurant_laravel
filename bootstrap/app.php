<?php

use App\Http\Middleware\AdminAccess;
use App\Http\Middleware\LoginAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

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
        //
    })->create();