<?php

require_once '../models/archive.model.php';
require_once '../helpers/permission.php';
require_once '../models/response.model.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$archiveModel = new ArchiveModel();
$response = new ResponseMethods();
$headers = apache_request_headers();
$jwt = $headers['Authorization'] ?? null;

if ($jwt) {
    $jwt = str_replace('Bearer ', '', $jwt);
}

function decodeJWT($jwt) {
    try {
        $decoded = JWT::decode($jwt, new Key(SECRET_KEY, 'HS256'));
        return $decoded->data;
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid token: ' . $e->getMessage()]);
        exit();
    }
}

$userData = decodeJWT($jwt);
$action = $_GET['action'] ?? null;
$requestMethod = $_SERVER['REQUEST_METHOD'];

switch ($action) {
    case 'retrieveArchivedItem':
        if ($requestMethod === 'POST') {
            Permission::checkRole($jwt, ['admin']);

            $data = json_decode(file_get_contents('php://input'), true);
            $item_id = $data['item_id'] ?? 0;

            $responsePayload = $archiveModel->retrieveArchivedItem($item_id);

            if ($responsePayload['status'] === 'error') {
                echo json_encode($response->responsePayload(null, 'error', $responsePayload['message'], 404));
            } else {
                echo json_encode($response->responsePayload($responsePayload, 'success', $responsePayload['message'], 200));
            }
        } else {
            echo json_encode($response->responsePayload(null, 'error', 'Invalid request method.', 405));
        }
        break;

    case 'deleteArchivedItem':
        if ($requestMethod === 'DELETE') {
            Permission::checkRole($jwt, ['admin']);

            $data = json_decode(file_get_contents('php://input'), true);
            $item_id = $data['item_id'] ?? 0;

            $responsePayload = $archiveModel->deleteArchivedItem($item_id);

            if ($responsePayload['status'] === 'error') {
                echo json_encode($response->responsePayload(null, 'error', $responsePayload['message'], 404));
            } else {
                echo json_encode($response->responsePayload($responsePayload, 'success', $responsePayload['message'], 200));
            }
        } else {
            echo json_encode($response->responsePayload(null, 'error', 'Invalid request method.', 405));
        }
        break;

        case 'getAllArchivedItems':
          if ($requestMethod === 'GET') {
              Permission::checkRole($jwt, ['admin']);
      
              $responsePayload = $archiveModel->getAllArchivedItems();
      
              if ($responsePayload['status'] === 'error') {
                  echo json_encode($response->responsePayload(null, 'error', $responsePayload['message'], 500));
              } else {
                  echo json_encode($response->responsePayload($responsePayload['data'], 'success', 'Archived items retrieved successfully.', 200));
              }
          } else {
              echo json_encode($response->responsePayload(null, 'error', 'Invalid request method.', 405));
          }
          break;      

    default:
        $response->notFound();
        break;
}
