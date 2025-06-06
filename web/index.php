<?php
// Start a session to manage user authentication
session_start();

// Include database connection
require_once 'db/db.php';
// Include Recipe Controller
require_once 'controller/RecipeController.php';
// Include Auth Service
require_once 'services/auth.php';

//Add header
header('Content-Type: application/json');

// Parse URI path only (ignore query strings)
$uriPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Determine method and path
$method = $_SERVER['REQUEST_METHOD'];
$uri = explode('/', trim($uriPath, '/'));

// Handle login/logout routes
if ($uriPath === '/login' && $method === 'POST') {
    login();
    exit;
}

if ($uriPath === '/logout' && $method === 'GET') {
    logout();
    exit;
}

// Handle api routes
if ($method === 'GET' && $uri[0] === 'recipes' && count($uri) === 1) {
    getRecipes($pdo);

} elseif ($method === 'POST' && $uri[0] === 'recipes' && count($uri) === 1) {
    addRecipe($pdo);

} elseif ($method === 'GET' && $uri[0] === 'recipes' && isset($uri[1]) && is_numeric($uri[1])) {
    getRecipeById($pdo, intval($uri[1]));

} elseif ($method === 'PUT' && $uri[0] === 'recipes' && isset($uri[1]) && is_numeric($uri[1])) {
    updateRecipe($pdo, intval($uri[1]));

} elseif ($method === 'DELETE' && $uri[0] === 'recipes' && isset($uri[1]) && is_numeric($uri[1])) {
    deleteRecipe($pdo, intval($uri[1]));

} elseif (
    $method === 'POST' &&
    $uri[0] === 'recipes' &&
    isset($uri[1], $uri[2]) &&
    is_numeric($uri[1]) &&
    $uri[2] === 'rating'
) {
    rateRecipe($pdo, intval($uri[1]));
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed or bad request']);
}
