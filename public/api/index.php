<?php

/**
 * @author Ilya Dashevsky <il.dashevsky@gmail.com>
 * @license The MIT License (MIT), http://opensource.org/licenses/MIT
 * @link https://github.com/edevelops/magic-spa-backend
 */

declare(strict_types = 1);

use Psr\Container\ContainerInterface;
use DI\ContainerBuilder;
use FastRoute\RouteCollector;
use Relay\Relay;
use Laminas\Diactoros\Response;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Laminas\Diactoros\ServerRequestFactory;
use function DI\create;
use function DI\get;
use function DI\autowire;
use function FastRoute\simpleDispatcher;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;
use OpenCore\CtrlContainer\CtrlContainerBuilder;
use Middlewares\FastRoute;
use Middlewares\RequestHandler;
use OpenCore\Middlewares\CsrfProtection;
use OpenCore\Middlewares\AuthMiddleware;
use OpenCore\Middlewares\LocaleMiddleware;
use MagicSpa\Services\DbLocator;
use OpenCore\Services\Injector;
use OpenCore\Rest\RestError;

define('APP_ROOT', dirname(dirname(__DIR__)));

define('DATA_ROOT', getenv('DATA_ROOT'));

require_once APP_ROOT . '/vendor/autoload.php';

function main() {

    set_error_handler(function($severity, $message, $file, $line) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    });
        
//    $pdo=new PDO('sqlite://'.DATA_ROOT.'/db.sqlite');
//    $res=$pdo->query('SELECT * FROM "tracks"');
//    die('$res->rowCount(): '.$res->rowCount());
    
    
    //$firephp = new FirePHPHandler();
    $mainLogger = new Logger('main');

    $mainLogger->pushHandler(new StreamHandler(APP_ROOT . '/logs/main.log', Logger::DEBUG));
    //$mainLogger->pushHandler($firephp);

    set_exception_handler(function(Throwable $ex)use($mainLogger) {
        http_response_code(RestError::HTTP_INTERNAL_SERVER_ERROR);
        $mainLogger->critical($ex);
    });

    $containerBuilder = new ContainerBuilder();
    $containerBuilder->useAnnotations(false);
    $containerBuilder->addDefinitions([
        ContainerInterface::class => function()use(&$container) {
            return $container;
        },
        DbLocator::class => create()->constructor(function() {
            return ['path'=>DATA_ROOT.'/db.sqlite', 'driver'=>'pdo_sqlite'];
        }, get(Injector::class), function() {
//            $dbLogger = new Logger('sql');
//            $dbLogger->pushHandler(new StreamHandler(APP_ROOT.'/logs/sql.log', Logger::DEBUG));
//            return $dbLogger;
            return null;
        }),
        Logger::class => $mainLogger,
        'response' => function() {
            return new Response();
        },
    ]);

    /** @noinspection PhpUnhandledExceptionInspection */
    $container = $containerBuilder->build();


    $writableMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];


    $ctrlContainerBuilder = new CtrlContainerBuilder();
    $ctrlContainerBuilder->useNamespace('MagicSpa\\Controllers');
    $ctrlContainerBuilder->useServicesContainer($container);
    $ctrlContainerBuilder->useLogger($mainLogger);
    $ctrlContainerBuilder->useDbTransations($writableMethods, function($work)use($container) {
        return $container->get(DbLocator::class)->transaction($work);
    });
    $ctrlContainer = $ctrlContainerBuilder->build();


    $handlerPermissionsMap = [];
    $routesTab = [];


    $routesConfig = json_decode(file_get_contents(APP_ROOT . '/config/rest-routes.json'));
    foreach ($routesConfig as $routeConfig) {
        $ctrlName = $routeConfig->controller;
        foreach ($routeConfig->routes as $route => $methods) {
            foreach ($methods as $httpMethod => $handlerProps) {
                $handlerName = $ctrlName . '.' . $handlerProps->method;
                $handlerPermissionsMap[$handlerName] = $handlerProps->permission;
                $routesTab[$httpMethod][$route] = $handlerName;
            }
        }
    }

    $routes = simpleDispatcher(function (RouteCollector $r)use($routesTab) {
        foreach ($routesTab as $httpMethod => $routeHandlers) {
            foreach ($routeHandlers as $route => $handlerName) {
                $r->addRoute($httpMethod, '/api/' . $route, $handlerName);
            }
        }
    });

//    $middlewareQueue[] = new CsrfProtection($writableMethods, function(){
//        return 'none'; // take CSRF Token from your session manager (injected using $container)
//    }, $mainLogger);
    $middlewareQueue[] = new FastRoute($routes);
//    $middlewareQueue[] = new AuthMiddleware($handlerPermissionsMap, function($permission) {
//        return true; // check if $permission is available for current user
//    }, $mainLogger);
//    $middlewareQueue[] = new LocaleMiddleware(function() {
//        return ['langs' => ['en', 'es', 'ru', 'cn'], 'defaultLang' => 'en', 'defaultLocale' => 'en-US'];
//    });
    $middlewareQueue[] = new RequestHandler($ctrlContainer);

    /** @noinspection PhpUnhandledExceptionInspection */
    $requestHandler = new Relay($middlewareQueue);
    $response = $requestHandler->handle(ServerRequestFactory::fromGlobals());

    $emitter = new SapiEmitter();
    /** @noinspection PhpVoidFunctionResultUsedInspection */
    return $emitter->emit($response);
}

return main();
