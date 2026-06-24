<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: [
            '*',
        ]);
        $middleware->redirectGuestsTo(fn (\Illuminate\Http\Request $request) =>
            $request->is('admin/*') ? route('admin.login') : route('login')
        );
        // Automatically log out and block any user whose is_banned flag is set
        $middleware->appendToGroup('web', \App\Http\Middleware\CheckBanned::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
