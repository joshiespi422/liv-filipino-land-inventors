<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use App\Http\Middleware\CheckUserType;
use App\Http\Middleware\CheckServiceAccess;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'role' => CheckUserType::class,
            'service_access' => CheckServiceAccess::class,

            // API Middleware
            'role.api' => \App\Http\Middleware\Api\CheckUserType::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // CONTROLLER — firstOrFail()
        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if ($request->is('api/*')) {
                $model = class_basename($e->getModel());

                $messages = [
                    'BusinessTrainingType' => 'Business training type not found.',
                    'BusinessTrainingCategory' => 'Business training category not found.',
                    'BusinessTraining' => 'Business Training module not found.',
                ];

                return response()->json([
                    'success' => false,
                    'message' => $messages[$model] ?? 'Record not found.',
                ], 404);
            }
        });

        // ModelNotFoundException
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                $previous = $e->getPrevious();

                if ($previous instanceof ModelNotFoundException) {
                    $model = class_basename($previous->getModel());

                    $messages = [
                        'BusinessTrainingType' => 'Business training type not found.',
                        'BusinessTrainingCategory' => 'Business training category not found.',
                        'BusinessTraining' => 'Business Training module not found.',
                    ];

                    return response()->json([
                        'success' => false,
                        'message' => $messages[$model] ?? 'Record not found.',
                    ], 404);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Route not found.',
                ], 404);
            }
        });

        // 401
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }
        });

        // 403
        $exceptions->render(function (AuthorizationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized.',
                ], 403);
            }
        });

        // 422
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

    })->create();
