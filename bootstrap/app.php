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
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->appendToGroup('login-admin',[
            LoginAdmin::class,
        ]);

    })
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->appendToGroup('admin-access',[
            AdminAccess::class,
        ]);
    })
    
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();