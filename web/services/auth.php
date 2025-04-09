<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json');

$secret_key = "=47%oupo&h%^rre_l0g@r^0=zlf2-a*_+wkxv(0ivlrus&rzv";
$issuer = "php-auth-app";

// LOGIN Function
function login(){
    try{
        // Get global variables
        global $secret_key, $issuer;

        // get the request body
        $data = json_decode(file_get_contents("php://input"), true);

        // Check if the request body is valid JSON
        if (!isset($data['uid'], $data['pass'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing credentials"]);
            exit;
        }

        $uid = $data['uid'];
        $pass = $data['pass'];

        // Validate credentials (this is just a placeholder, not implemented logic)
        if ($uid === 'admin' && $pass === '1234') {

            // Set session variables
            $issued_at = time();
            $expire = $issued_at + (60 * 60); // 1 hour

            // Create JWT payload
            $payload = [
                'iss' => $issuer,
                'iat' => $issued_at,
                'exp' => $expire,
                'data' => ['uid' => $uid]
            ];

            // Encode the JWT and store it in the session
            $_SESSION['jwt'] = JWT::encode($payload, $secret_key, 'HS256');

            // return response
            echo json_encode([
                "message" => "Login successful",
                "expires" => $expire
            ]);
        } else {
            http_response_code(401);
            echo json_encode(["error" => "Invalid credentials"]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => "Internal server error"]);
    }
    exit;
}

// LOGOUT Function
function logout(){
    try{
        // Unset the JWT session variable 
        unset($_SESSION['jwt']);

        // Destroy the session
        session_destroy();

        // Return response
        echo json_encode(["message" => "Logged out"]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => "Internal server error"]);
    }
    exit;
}

// Middleware for verifying JWT session
function verify_jwt_session(){
    // Get global variables
    global $secret_key;

    // Check if the JWT session variable is set
    if (!isset($_SESSION['jwt'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    try {
        // Decode the JWT
        $decoded = JWT::decode($_SESSION['jwt'], new Key($secret_key, 'HS256'));

        // return the decoded payload
        return $decoded;
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid or expired token']);
        exit;
    }
}