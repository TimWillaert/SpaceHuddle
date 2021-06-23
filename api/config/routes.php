<?php

// Define app routes

use App\Action\Home\HomeAction;
use App\Action\OpenApi\Version1DocAction;
use App\Action\Participant\ParticipantConnectAction;
use App\Action\PreflightAction;
use App\Action\Session\SessionDeleteAction;
use App\Action\Session\SessionUpdateAction;
use App\Action\Topic\TopicCreateAction;
use App\Action\Topic\TopicDeleteAction;
use App\Action\Topic\TopicReadAllAction;
use App\Action\Topic\TopicReadSingleAction;
use App\Action\Topic\TopicUpdateAction;
use App\Action\User\UserChangePasswordAction;
use App\Action\User\UserDeleteAction;
use App\Action\User\UserLoginAction;
use App\Action\User\UserRegisterAction;
use \App\Action\Session\SessionCreateAction;
use \App\Action\Session\SessionReadSingleAction;
use \App\Action\Session\SessionReadAllAction;
use App\Middleware\JwtAuthMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    $generateOptions = function () use ($app) {
        $routes = $app->getRouteCollector()->getRoutes();
        $optionPatterns = [];
        foreach ($routes as $route) {
            $pattern = $route->getPattern();
            if (!in_array($pattern, $optionPatterns)) {
                array_push($optionPatterns, $pattern);
            }
        }

        foreach ($optionPatterns as $pattern) {
            $app->options($pattern, PreflightAction::class);
        }
    };

    // Redirect to Swagger documentation
    $app->get("/", HomeAction::class)->setName("home");

    // Swagger API documentation
    $app->get("/docs/v1", Version1DocAction::class)->setName("docs");

    $app->group(
        "/user",
        function (RouteCollectorProxy $app) {
            $app->post("/login[/]", UserLoginAction::class);
            $app->post("/register[/]", UserRegisterAction::class);
        }
    );

    $app->group(
        "/participant",
        function (RouteCollectorProxy $app) {
            $app->post("/connect[/]", ParticipantConnectAction::class);
        }
    );

    // Password protected area
    $app->group(
        "/user",
        function (RouteCollectorProxy $app) {
            $app->put("[/]", UserChangePasswordAction::class);
            $app->delete("[/]", UserDeleteAction::class);
        }
    )->add(JwtAuthMiddleware::class);

    $app->group(
        "/sessions",
        function (RouteCollectorProxy $app) {
            $app->get("[/]", SessionReadAllAction::class);
        }
    )->add(JwtAuthMiddleware::class);

    $app->group(
        "/session",
        function (RouteCollectorProxy $app) {
            $app->group(
                "/{sessionId}/topic",
                function (RouteCollectorProxy $app) {
                    $app->post("[/]", TopicCreateAction::class);
                }
            );

            $app->get("/{sessionId}/topics[/]", TopicReadAllAction::class);

            $app->post("[/]", SessionCreateAction::class);
            $app->get("/{id}[/]", SessionReadSingleAction::class);
            $app->put("[/]", SessionUpdateAction::class);
            $app->delete("/{id}[/]", SessionDeleteAction::class);
        }
    )->add(JwtAuthMiddleware::class);

    $app->group(
        "/topic",
        function (RouteCollectorProxy $app) {
            $app->get("/{id}[/]", TopicReadSingleAction::class);
            $app->put("[/]", TopicUpdateAction::class);
            $app->delete("/{id}[/]", TopicDeleteAction::class);
        }
    )->add(JwtAuthMiddleware::class);

    $generateOptions();
};
