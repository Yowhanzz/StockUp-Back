<?php
// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

// Include necessary dependencies
require_once __DIR__ . '/../src/models/archive.model.php';
require_once __DIR__ . '/../src/models/inventory.model.php';
require_once __DIR__ . '/../src/models/session.model.php';
require_once __DIR__ . '/../src/models/response.model.php';
require_once __DIR__ . '/../src/helpers/permission.php';
require_once __DIR__ . '/../src/config/Connection.php';
require_once __DIR__ . '/../src/controllers/authController.php';
require_once __DIR__ . '/../src/controllers/userController.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Helper function to decode JWT
function decodeJWT($jwt) {
    try {
        return JWT::decode($jwt, new Key(SECRET_KEY, 'HS256'))->data;
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid token: ' . $e->getMessage()]);
        exit();
    }
}

// Extract the requested action and HTTP method
$requestUri = trim($_SERVER['REQUEST_URI'], '/');
$basePath = 'STOCKUP/Backend/public';
$path = substr($requestUri, strlen($basePath));
$segments = explode('/', trim($path, '/'));
$module = ucfirst($segments[0] ?? '');
$action = $segments[1] ?? null;
$requestMethod = $_SERVER['REQUEST_METHOD'];
$jwt = $_COOKIE['auth_token'] ?? null;

// Initialize response and user data
$response = new ResponseMethods();
$userData = $jwt ? decodeJWT($jwt) : null;

// Route handling
switch ($module) {
    case 'Auth':
        $authController = new AuthController();
        $userController = new UserController();
        switch ($requestMethod . ':' . $action) {
            case 'POST:login':
                $input = json_decode(file_get_contents('php://input'), true);
                echo json_encode($response->responsePayload($authController->login($input), 'success', 'Login successful.', 200));
                break;
            case 'POST:register':
                $input = json_decode(file_get_contents('php://input'), true);
                echo json_encode($response->responsePayload($userController->register($input), 'success', 'User registered successfully.', 200));
                break;
            case 'GET:logout':
                echo json_encode($response->responsePayload($authController->logout(), 'success', 'Logged out successfully.', 200));
                break;
            default:
                http_response_code(400);
                echo json_encode($response->responsePayload(null, 'error', 'Invalid request method or action.', 400));
                break;
        }
        break;

    case 'Archive':
        $archiveModel = new ArchiveModel();
        switch ($requestMethod . ':' . $action) {
            case 'POST:retrieveArchivedItem':
                Permission::checkRole($jwt, ['admin']);
                $data = json_decode(file_get_contents('php://input'), true);
                echo json_encode($response->responsePayload($archiveModel->retrieveArchivedItem($data['item_id']), 'success', 'Item retrieved from archive.', 200));
                break;
            case 'DELETE:deleteArchivedItem':
                Permission::checkRole($jwt, ['admin']);
                $data = json_decode(file_get_contents('php://input'), true);
                echo json_encode($response->responsePayload($archiveModel->deleteArchivedItem($data['item_id']), 'success', 'Item deleted from archive.', 200));
                break;
            case 'GET:getAllArchivedItems':
                Permission::checkRole($jwt, ['admin']);
                echo json_encode($response->responsePayload($archiveModel->getAllArchivedItems(), 'success', 'Archived items retrieved successfully.', 200));
                break;
            default:
                echo json_encode($response->responsePayload(null, 'error', 'Invalid action for Archive.', 400));
                break;
        }
        break;

        case 'Inventory':
            $inventoryModel = new InventoryModel();
            switch ($requestMethod . ':' . $action) {
                case 'POST:addItem':
                    Permission::checkRole($jwt, ['admin']);
                    $data = json_decode(file_get_contents('php://input'), true);
                    $responsePayload = $inventoryModel->createItem(
                        $data['item_name'], 
                        $data['brand'], 
                        $data['category'], 
                        $data['quantity']
                    );
                    echo json_encode($response->responsePayload($responsePayload, 'success', 'Item added successfully.', 200));
                    break;
        
                case 'GET:getAllItems':
                    Permission::checkRole($jwt, ['admin', 'staff']);
                    $items = $inventoryModel->readItems();
                    echo json_encode($response->responsePayload($items, 'success', 'Items retrieved successfully.', 200));
                    break;
        
                case 'PUT:updateQuantity':
                    Permission::checkRole($jwt, ['admin', 'staff']);
                    $data = json_decode(file_get_contents('php://input'), true);
                    $responsePayload = $inventoryModel->updateQuantity($data['item_id'], $data['quantity']);
                    echo json_encode($response->responsePayload($responsePayload, 'success', 'Quantity updated successfully.', 200));
                    break;
        
                case 'DELETE:deleteItem':
                    Permission::checkRole($jwt, ['admin']);
                    $data = json_decode(file_get_contents('php://input'), true);
                    $responsePayload = $inventoryModel->deleteItem($data['item_id']);
                    echo json_encode($response->responsePayload($responsePayload, 'success', 'Item deleted successfully.', 200));
                    break;
        
                case 'GET:sortItemsByCategory':
                    Permission::checkRole($jwt, ['admin', 'staff']);
                    $category = $_GET['category'] ?? null;
                    $sortedItems = $inventoryModel->sortItemsByCategory($category);
                    echo json_encode($response->responsePayload($sortedItems, 'success', 'Items sorted by category successfully.', 200));
                    break;
        
                case 'GET:getItemsByStatus':
                    Permission::checkRole($jwt, ['admin', 'staff']);
                    $status = $_GET['status'] ?? '';
                    $items = $inventoryModel->getItemsByStatus($status);
                    echo json_encode($response->responsePayload($items, 'success', 'Items retrieved by status successfully.', 200));
                    break;
        
                case 'GET:getItemsByQuantityDesc':
                    Permission::checkRole($jwt, ['admin', 'staff']);
                    $items = $inventoryModel->getItemsByQuantityDesc();
                    echo json_encode($response->responsePayload($items, 'success', 'Items sorted by quantity (descending) successfully.', 200));
                    break;
        
                case 'GET:getItemsByQuantityAsc':
                    Permission::checkRole($jwt, ['admin', 'staff']);
                    $items = $inventoryModel->getItemsByQuantityAsc();
                    echo json_encode($response->responsePayload($items, 'success', 'Items sorted by quantity (ascending) successfully.', 200));
                    break;
        
                case 'GET:getAllUserLogs':
                    Permission::checkRole($jwt, ['admin']);
                    $logs = $inventoryModel->getAllUserLogs();
                    echo json_encode($response->responsePayload($logs, 'success', 'User logs retrieved successfully.', 200));
                    break;
        
                case 'GET:getUserLogsByFullName':
                    Permission::checkRole($jwt, ['admin']);
                    $data = json_decode(file_get_contents('php://input'), true);
                    $fullName = $data['full_name'] ?? '';
                    if (empty($fullName)) {
                        echo json_encode($response->responsePayload(null, 'error', 'Full name is required.', 400));
                        break;
                    }
                    $logs = $inventoryModel->getUserLogsByFullName($fullName);
                    echo json_encode($response->responsePayload($logs, 'success', 'User logs retrieved successfully.', 200));
                    break;
        
                case 'GET:getItemByName':
                    Permission::checkRole($jwt, ['admin', 'staff']);
                    $data = json_decode(file_get_contents('php://input'), true);
                    $itemName = $data['item_name'] ?? '';
                    if (empty($itemName)) {
                        echo json_encode($response->responsePayload(null, 'error', 'Item name is required.', 400));
                        break;
                    }
                    $item = $inventoryModel->getItemByName($itemName);
                    echo json_encode($response->responsePayload($item, 'success', 'Item retrieved successfully.', 200));
                    break;
        
                case 'GET:getItemsByNameOrder':
                    Permission::checkRole($jwt, ['admin', 'staff']);
                    $order = $_GET['order'] ?? 'asc';
                    if ($order === 'asc') {
                        $items = $inventoryModel->getItemsByNameAsc();
                    } elseif ($order === 'desc') {
                        $items = $inventoryModel->getItemsByNameDesc();
                    } else {
                        echo json_encode($response->responsePayload(null, 'error', 'Invalid order parameter.', 400));
                        break;
                    }
                    echo json_encode($response->responsePayload($items, 'success', 'Items sorted by name successfully.', 200));
                    break;
        
                default:
                    echo json_encode($response->responsePayload(null, 'error', 'Invalid action for Inventory.', 400));
                    break;
            }
            break;
        

    case 'Session':
        $sessionModel = new SessionModel();
        switch ($requestMethod . ':' . $action) {
            case 'GET:getSessions':
                Permission::checkRole($jwt, ['admin']);
        
                // Fetch query parameters (user_id or full_name)
                $user_id = $_GET['user_id'] ?? null;
                $full_name = $_GET['full_name'] ?? null;
        
                // Conditional logic based on provided parameters
                if ($user_id) {
                    $sessions = $sessionModel->getSessionsByUserId($user_id);
                } elseif ($full_name) {
                    $sessions = $sessionModel->getSessionsByFullName($full_name);
                } else {
                    $sessions = $sessionModel->getAllSessions();
                }
        
                // Return the response
                echo json_encode($response->responsePayload($sessions, 'success', 'Sessions retrieved successfully.', 200));
                break;

            default:
                echo json_encode($response->responsePayload(null, 'error', 'Invalid action for Sessions.', 400));
                break;
        }
        break;

    default:
        http_response_code(404);
        echo json_encode($response->responsePayload(null, 'error', 'Invalid module or action.', 404));
        break;
}
