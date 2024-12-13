<?php

require_once __DIR__ . '/../config/Connection.php'; 
require_once __DIR__ . '/../interfaces/inventory.interface.php';
require_once __DIR__ . '/../../vendor/autoload.php'; // Ensure the JWT library is available
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class InventoryModel implements InventoryInterface
{
    private $db;

    public function __construct()
    {
        $connection = new Connection();
        $this->db = $connection->connect();
    }

    public function createItem($itemName, $brand, $category, $quantity)
    {
        $sql = "INSERT INTO inventory (item_name, brand, category, quantity) VALUES (:item_name, :brand, :category, :quantity)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':item_name' => $itemName,
            ':brand' => $brand,
            ':category' => $category,
            ':quantity' => $quantity,
        ]);
        return ['status' => 'success', 'message' => 'Item added successfully'];
    }

    public function readItems()
    {
        $sql = "SELECT * FROM inventory";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function updateQuantity($itemId, $quantity)
    {
        if ($quantity >= 0 && $quantity <= 20) {
            $status = 'Very Low';
        } elseif ($quantity >= 21 && $quantity <= 40) {
            $status = 'Low';
        } elseif ($quantity >= 41 && $quantity <= 60) {
            $status = 'Average';
        } elseif ($quantity >= 61 && $quantity <= 80) {
            $status = 'High';
        } else {
            $status = 'Very High';
        }

        $sql = "UPDATE inventory SET quantity = :quantity, status = :status WHERE item_id = :item_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':quantity' => $quantity,
            ':status' => $status,
            ':item_id' => $itemId,
        ]);

        return ['status' => 'success', 'message' => 'Quantity and status updated successfully'];
    }

public function deleteItem($itemId)
{
    $sql = "SELECT * FROM inventory WHERE item_id = :item_id";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':item_id' => $itemId]);
    $item = $stmt->fetch();

    if (!$item) {
        return [
            'status' => 'error',
            'message' => 'Item not found.'
        ];
    }

    $archiveSql = "INSERT INTO archive_items (item_id, item_name, brand, category, quantity, status) 
                   VALUES (:item_id, :item_name, :category, :quantity, :status)";
    $archiveStmt = $this->db->prepare($archiveSql);
    $archiveStmt->execute([
        ':item_id' => $item['item_id'],
        ':item_name' => $item['item_name'],
        ':brand' => $item['brand'],
        ':category' => $item['category'],
        ':quantity' => $item['quantity'],
        ':status' => $item['status']
    ]);

    $deleteSql = "DELETE FROM inventory WHERE item_id = :item_id";
    $deleteStmt = $this->db->prepare($deleteSql);
    $deleteStmt->execute([':item_id' => $itemId]);

    return [
        'status' => 'success',
        'message' => 'Item archived and deleted successfully.'
    ];
}

public function sortItemsByCategory($specificCategory = null)
{
    $allowedCategories = ['Writing Supplies', 'Paper Materials', 'Arts & Crafts', 'Organizational Tools', 'Miscellaneous'];

    if ($specificCategory && in_array($specificCategory, $allowedCategories)) {
        
        $sql = "SELECT * FROM inventory WHERE category = :category ORDER BY category";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':category' => $specificCategory]);
    } else {
        $sql = "SELECT * FROM inventory ORDER BY category";
        $stmt = $this->db->query($sql);
    }
    return $stmt->fetchAll();
}

    public function getItemsByStatus($status)
    {
        $sql = "SELECT * FROM inventory WHERE status = :status";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':status' => $status]);
        return $stmt->fetchAll();
    }
    public function getItemsByQuantityDesc()
    {
        $sql = "SELECT * FROM inventory ORDER BY quantity DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    public function getItemsByQuantityAsc()
    {
        $sql = "SELECT * FROM inventory ORDER BY quantity ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    public function updateQuantityValue($item_id, $new_quantity)
    {
    $pdo = (new Connection())->connect();

    $sqlFetch = "SELECT quantity FROM inventory WHERE id = :item_id";
    $stmtFetch = $pdo->prepare($sqlFetch);
    $stmtFetch->bindParam(':item_id', $item_id, PDO::PARAM_INT);
    $stmtFetch->execute();
    $currentData = $stmtFetch->fetch();

    if (!$currentData) {
        return ['status' => 'error', 'message' => 'Item not found.'];
    }

    return ['status' => 'error', 'message' => 'Failed to update quantity.'];
    }

    public function logUserAction($jwt, $functionName)
    {
        try {
            $decoded = JWT::decode($jwt, new Key(SECRET_KEY, 'HS256'));
            $userData = $decoded->data;
            $userId = $userData->user_id ?? null;
            $fullName = $userData->full_name ?? null;
    
            $sql = "INSERT INTO user_logs (user_id, full_name, function_name, timestamp) VALUES (:user_id, :full_name, :function_name, NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':user_id' => $userId,
                ':full_name' => $fullName,
                ':function_name' => $functionName
            ]);
        } catch (Exception $e) {
            error_log("Error decoding JWT: " . $e->getMessage());
            throw new Exception("Failed to log user action: Invalid JWT.");
        }
    }
    public function getAllUserLogs()
    {
        try {
            $sql = "SELECT * FROM user_logs ORDER BY timestamp DESC";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to retrieve user logs.'
            ];
        }
    }
}
