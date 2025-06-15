<?php

// --- START ERROR LOGGING CONFIGURATION ---
// Enable error logging.
ini_set('log_errors', '1');
// Don't display errors directly to the user (for production security).
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
// Report all errors during development.
error_reporting(E_ALL);
// Define the log file path. It will be created if it doesn't exist.
$logFilePath = __DIR__ . '/../php_errors.log'; 
ini_set('error_log', $logFilePath);

// Optional: Test line to confirm logging is working.
// error_log("PHP error logging configured to: " . $logFilePath);
// --- END ERROR LOGGING CONFIGURATION ---


// --- START CORS HANDLING ---
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true'); 
    header('Access-Control-Max-Age: 86400');    
}

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    }
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    }
    http_response_code(204);
    exit(0);
}
// --- END CORS HANDLING ---

require_once __DIR__ . '/../vendor/autoload.php';

if (!headers_sent()) {
    header('Content-Type: application/json; charset=UTF-8');
}

// Basic routing for the GraphQL endpoint.
$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $r->post('/graphql', [App\Controller\GraphQLController::class, 'handle']);
    $r->get('/ping', function() {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=UTF-8');
        }
        return json_encode(['message' => 'pong', 'time' => date('Y-m-d H:i:s')]);
    });
});

$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
$responseContent = ''; 

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        if (!headers_sent()) { header('Content-Type: application/json; charset=UTF-8'); }
        http_response_code(404);
        $responseContent = json_encode(['error' => '404 Not Found']);
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        if (!headers_sent()) { header('Content-Type: application/json; charset=UTF-8'); }
        http_response_code(405);
        $responseContent = json_encode(['error' => '405 Method Not Allowed', 'allowed_methods' => $allowedMethods]);
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];

        try { // Catch any exceptions that might not be caught in the controller.
            if (is_callable($handler)) { 
                $responseContent = $handler($vars);
            } elseif (is_array($handler) && class_exists($handler[0]) && method_exists($handler[0], $handler[1])) {
                $controller = new $handler[0]();
                $method = $handler[1];
                $responseContent = $controller->$method($vars); 
            } else {
                if (!headers_sent()) { header('Content-Type: application/json; charset=UTF-8'); }
                http_response_code(500);
                $responseContent = json_encode(['error' => 'Invalid route handler configuration']);
                // Also log the error.
                error_log("Invalid route handler configuration for URI: {$uri} with handler: " . print_r($handler, true));
            }
        } catch (\Throwable $e) {
            // Catch any general exceptions during request processing.
            error_log("Unhandled exception in routing/controller: " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
            if (!headers_sent()) { header('Content-Type: application/json; charset=UTF-8'); }
            http_response_code(500);
            // Don't return detailed error messages to the client in production.
            $errorOutput = ['error' => 'Internal Server Error (Router/Controller Level)'];
            $responseContent = json_encode($errorOutput);
        }
        break;
    default:
        if (!headers_sent()) { header('Content-Type: application/json; charset=UTF-8'); }
        http_response_code(500);
        $responseContent = json_encode(['error' => 'Unexpected routing error']);
        error_log("Unexpected routing error for URI: {$uri}");
        break;
}

echo $responseContent;

?>