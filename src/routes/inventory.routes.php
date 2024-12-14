<?php
require_once '../models/inventory.model.php';
require_once '../helpers/permission.php';
require_once '../models/response.model.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$inventoryModel = new InventoryModel();
$response = new ResponseMethods();

$jwt = $_COOKIE['auth_token'] ?? null;

if (!$jwt) {
    echo json_encode(['status' => 'error', 'message' => 'Token not found']);
    exit();
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
    case 'addItem':
        if ($requestMethod === 'POST') {
            Permission::checkRole($jwt, ['admin']);
            $data = json_decode(file_get_contents('php://input'), true);
            $item_name = $data['item_name'] ?? '';
            $brand = $data['brand'] ?? '';
            $category = $data['category'] ?? 'Miscellaneous';
            $quantity = $data['quantity'] ?? 0;

            $inventoryModel->logUserAction($jwt, 'addItem');
            $responsePayload = $inventoryModel->createItem($item_name, $brand, $category, $quantity);

            echo json_encode($response->responsePayload($responsePayload, 'success', 'Item added successfully.', 201));
        } else {
            echo json_encode($response->responsePayload(null, 'error', 'Invalid request method.', 405));
        }
        break;

    case 'getAllItems':
        if ($requestMethod === 'GET') {
            Permission::checkRole($jwt, ['admin', 'staff']);

            $inventoryModel->logUserAction($jwt, 'getAllItems');
            $items = $inventoryModel->readItems();

            echo json_encode($response->responsePayload($items, 'success', 'Items retrieved successfully.', 200));
        } else {
            echo json_encode($response->responsePayload(null, 'error', 'Invalid request method.', 405));
        }
        break;

    case 'updateQuantity':
        if ($requestMethod === 'PUT') {
            Permission::checkRole($jwt, ['admin', 'staff']);

            $data = json_decode(file_get_contents('php://input'), true);
            $item_id = $data['item_id'] ?? 0;
            $quantity = $data['quantity'] ?? 0;

            $inventoryModel->logUserAction($jwt, 'updateQuantity');
            $responsePayload = $inventoryModel->updateQuantity($item_id, $quantity);

            echo json_encode($response->responsePayload($responsePayload, 'success', 'Quantity updated successfully.', 200));
        } else {
            echo json_encode($response->responsePayload(null, 'error', 'Invalid request method.', 405));
        }
        break;

    case 'deleteItem':
        if ($requestMethod === 'DELETE') {
            Permission::checkRole($jwt, ['admin']);

            $data = json_decode(file_get_contents('php://input'), true);
            $item_id = $data['item_id'] ?? 0;

            $inventoryModel->logUserAction($jwt, 'deleteItem');
            $responsePayload = $inventoryModel->deleteItem($item_id);

            if ($responsePayload['status'] === 'error') {
                echo json_encode($response->responsePayload(null, 'error', $responsePayload['message'], 404));
            } else {
                echo json_encode($response->responsePayload($responsePayload, 'success', $responsePayload['message'], 200));
            }
        } else {
            echo json_encode($response->responsePayload(null, 'error', 'Invalid request method.', 405));
        }
        break;

    case 'sortItemsByCategory':
        if ($requestMethod === 'GET') {
            Permission::checkRole($jwt, ['admin', 'staff']);

            $specificCategory = $_GET['category'] ?? null;
            $inventoryModel->logUserAction($jwt, 'sortItemsByCategory');
            $items = $inventoryModel->sortItemsByCategory($specificCategory);

            echo json_encode($response->responsePayload($items, 'success', 'Items sorted by category successfully.', 200));
        } else {
            echo json_encode($response->responsePayload(null, 'error', 'Invalid request method.', 405));
        }
        break;

    case 'getItemsByStatus':
        if ($requestMethod === 'GET') {
            Permission::checkRole($jwt, ['admin', 'staff']);

            $status = $_GET['status'] ?? '';
            $inventoryModel->logUserAction($jwt, 'getItemsByStatus');
            $items = $inventoryModel->getItemsByStatus($status);

            echo json_encode($response->responsePayload($items, 'success', 'Items retrieved by status successfully.', 200));
        } else {
            echo json_encode($response->responsePayload(null, 'error', 'Invalid request method.', 405));
        }
        break;

    case 'getItemsByQuantityDesc':
        if ($requestMethod === 'GET') {
            Permission::checkRole($jwt, ['admin', 'staff']);

            $inventoryModel->logUserAction($jwt, 'getItemsByQuantityDesc');
            $items = $inventoryModel->getItemsByQuantityDesc();

            echo json_encode($response->responsePayload($items, 'success', 'Items sorted by quantity (descending) successfully.', 200));
        } else {
            echo json_encode($response->responsePayload(null, 'error', 'Invalid request method.', 405));
        }
        break;

    case 'getItemsByQuantityAsc':
        if ($requestMethod === 'GET') {
            Permission::checkRole($jwt, ['admin', 'staff']);

            $inventoryModel->logUserAction($jwt, 'getItemsByQuantityAsc');
            $items = $inventoryModel->getItemsByQuantityAsc();

            echo json_encode($response->responsePayload($items, 'success', 'Items sorted by quantity (ascending) successfully.', 200));
        } else {
            echo json_encode($response->responsePayload(null, 'error', 'Invalid request method.', 405));
        }
        break;

    case 'getAllUserLogs':
        if ($requestMethod === 'GET') {
            Permission::checkRole($jwt, ['admin']);

            $inventoryModel->logUserAction($jwt, 'getAllUserLogs');
            $logs = $inventoryModel->getAllUserLogs();

            echo json_encode($response->responsePayload($logs, 'success', 'User logs retrieved successfully.', 200));
        } else {
            echo json_encode($response->responsePayload(null, 'error', 'Invalid request method.', 405));
        }
        break;
    
        case 'getItemByName':
            if ($requestMethod === 'POST') { // Change to POST
                Permission::checkRole($jwt, ['admin', 'staff']);
        
                // Decode JSON payload
                $data = json_decode(file_get_contents('php://input'), true);
                $itemName = $data['item_name'] ?? '';
        
                if (empty($itemName)) {
                    echo json_encode($response->responsePayload(null, 'error', 'Item name is required.', 400));
                    break;
                }
        
                $inventoryModel->logUserAction($jwt, 'getItemByName');
                $responsePayload = $inventoryModel->getItemByName($itemName);
        
                if ($responsePayload['status'] === 'error') {
                    echo json_encode($response->responsePayload(null, 'error', $responsePayload['message'], 404));
                } else {
                    echo json_encode($response->responsePayload($responsePayload['data'], 'success', 'Item retrieved successfully.', 200));
                }
            } else {
                echo json_encode($response->responsePayload(null, 'error', 'Invalid request method.', 405));
            }
            break;        

    default:
        $response->notFound();
        break;
}
