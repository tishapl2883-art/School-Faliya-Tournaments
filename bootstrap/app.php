<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Arr;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (TokenMismatchException|HttpExceptionInterface $e, Request $request) {
            $status = $e instanceof TokenMismatchException ? 419 : $e->getStatusCode();
            if ($status !== 419 || $request->expectsJson()) {
                return null;
            }

            return back()
                ->withInput(Arr::except($request->post(), ['password', '_token']))
                ->withErrors(['session' => 'Your page expired or was open in another tab. Please re-select any photo and submit again.']);
        });
    })->create();
