<?php

require_once '../models/auth.model.php'; // The model containing the Auth logic
require_once '../helpers/permission.php'; // The helper for permission checking
require_once '../models/response.model.php'; // The response model for formatting responses
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$authModel = new AuthModel();
$response = new ResponseMethods();

$authToken = $_COOKIE['auth_token'] ?? null;

if (!$authToken) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Authorization token is missing. Make sure you are logged in and the auth_token cookie is set.'
    ]);
    exit();
}

function decodeJWT($authToken) {
    try {
        // Decode the JWT using the secret key.
        $decoded = JWT::decode($authToken, new Key(SECRET_KEY, 'HS256'));
        return $decoded->data;
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid token: ' . $e->getMessage()
        ]);
        exit();
    }
}

$userData = decodeJWT($authToken); // Decode the JWT and retrieve user data
$action = $_GET['action'] ?? null;
$requestMethod = $_SERVER['REQUEST_METHOD'];

switch ($action) {
    case 'checkRole':
        if ($requestMethod === 'GET') {
            // Check if the decoded user data exists and retrieve the user's role
            if (isset($userData->role)) {  // Use object syntax here
                $role = $userData->role;  // Use object syntax here

                // Check if the role is 'admin' or 'staff'
                if ($role === 'admin' || $role === 'staff') {
                    echo json_encode([
                        'status' => 'success',
                        'role' => $role,
                        'message' => "User has the role: {$role}"
                    ]);
                } else {
                    echo json_encode([
                        'status' => 'error',
                        'message' => "User does not have the required role. Only 'admin' or 'staff' are allowed."
                    ]);
                }
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Role information not available in the token.'
                ]);
            }
        } else {
            echo json_encode($response->responsePayload(null, 'error', 'Invalid request method.', 405));
        }
        break;

        case 'decodeJWT':
          if ($requestMethod === 'GET') {
              $authToken = $_COOKIE['auth_token'] ?? null;
  
              if (!$authToken) {
                  echo json_encode([
                      'status' => 'error',
                      'message' => 'Authorization token is missing. Please make sure you are logged in and the auth_token cookie is set.'
                  ]);
                  exit();
              }
  
              // Decode the JWT token using the method
              $decodedData = decodeJWT($authToken);
  
              if ($decodedData) {
                  // If decoding is successful, return the decoded data (user data)
                  echo json_encode([
                      'status' => 'success',
                      'message' => 'Token successfully decoded.',
                      'data' => $decodedData
                  ]);
              } else {
                  echo json_encode([
                      'status' => 'error',
                      'message' => 'Failed to decode token.'
                  ]);
              }
          } else {
              echo json_encode([
                  'status' => 'error',
                  'message' => 'Invalid request method.'
              ]);
          }
          break;
  
      default:
          echo json_encode([
              'status' => 'error',
              'message' => 'Action not found.'
          ]);
          break;
  }