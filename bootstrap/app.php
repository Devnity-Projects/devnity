<?php

use App\Http\Middleware\EnsureUserSettings;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
        
        $middleware->alias([
            'ensure.user.settings' => EnsureUserSettings::class,
            'sync.ldap.groups' => \App\Http\Middleware\SyncLdapGroups::class,
            // Spatie permission middlewares
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->respond(function (\Symfony\Component\HttpFoundation\Response $response, \Throwable $exception, \Illuminate\Http\Request $request) {
            if ($response->getStatusCode() === 403 && $request->expectsJson() === false) {
                return \Inertia\Inertia::render('Errors/403', [
                    'status' => 403,
                    'message' => $exception->getMessage() ?: 'Você não tem permissão para acessar este recurso.',
                ])->toResponse($request)->setStatusCode(403);
            }
            
            if ($response->getStatusCode() === 404 && $request->expectsJson() === false) {
                return \Inertia\Inertia::render('Errors/404', [
                    'status' => 404,
                ])->toResponse($request)->setStatusCode(404);
            }
            
            if (in_array($response->getStatusCode(), [500, 503]) && $request->expectsJson() === false && !app()->environment('local')) {
                return \Inertia\Inertia::render('Errors/500', [
                    'status' => $response->getStatusCode(),
                ])->toResponse($request)->setStatusCode($response->getStatusCode());
            }
            
            return $response;
        });
    })->create();
