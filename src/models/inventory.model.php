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

    // Create new item (Admin only)
    public function createItem($itemName, $category, $quantity)
    {
        $sql = "INSERT INTO inventory (item_name, category, quantity) VALUES (:item_name, :category, :quantity)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':item_name' => $itemName,
            ':category' => $category,
            ':quantity' => $quantity,
        ]);
        return ['status' => 'success', 'message' => 'Item added successfully'];
    }

    // Read all items
    public function readItems()
    {
        $sql = "SELECT * FROM inventory";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    // Update item quantity (Admin and Staff)
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

    // Delete item (Admin only)
// Delete item (Admin only)
public function deleteItem($itemId)
{
    // Check if the item exists in the inventory
    $sql = "SELECT * FROM inventory WHERE item_id = :item_id";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':item_id' => $itemId]);
    $item = $stmt->fetch();

    // Log the result of the fetch operation to see if the item was found
    error_log(print_r($item, true));  // Logs to the PHP error log

    // If the item doesn't exist, return an error
    if (!$item) {
        return [
            'status' => 'error',
            'message' => 'Item not found.'
        ];
    }

    // Archive the item before deletion
    $archiveSql = "INSERT INTO archive_items (item_id, item_name, category, quantity, status) 
                   VALUES (:item_id, :item_name, :category, :quantity, :status)";
    $archiveStmt = $this->db->prepare($archiveSql);
    $archiveStmt->execute([
        ':item_id' => $item['item_id'],
        ':item_name' => $item['item_name'],
        ':category' => $item['category'],
        ':quantity' => $item['quantity'],
        ':status' => $item['status']
    ]);

    // Delete the item from the inventory
    $deleteSql = "DELETE FROM inventory WHERE item_id = :item_id";
    $deleteStmt = $this->db->prepare($deleteSql);
    $deleteStmt->execute([':item_id' => $itemId]);

    return [
        'status' => 'success',
        'message' => 'Item archived and deleted successfully.'
    ];
}


    // Sort items by category
    public function sortItemsByCategory()
    {
        $sql = "SELECT * FROM inventory ORDER BY category";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    // Get items by individual stock statuses
    public function getItemsByStatus($status)
    {
        $sql = "SELECT * FROM inventory WHERE status = :status";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':status' => $status]);
        return $stmt->fetchAll();
    }

    // Sort items by quantity in descending order
    public function getItemsByQuantityDesc()
    {
        $sql = "SELECT * FROM inventory ORDER BY quantity DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    // Sort items by quantity in ascending order
    public function getItemsByQuantityAsc()
    {
        $sql = "SELECT * FROM inventory ORDER BY quantity ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function updateQuantityValue($item_id, $new_quantity)
    {
    $pdo = (new Connection())->connect();

    // Fetch the current quantity before updating
    $sqlFetch = "SELECT quantity FROM inventory WHERE id = :item_id";
    $stmtFetch = $pdo->prepare($sqlFetch);
    $stmtFetch->bindParam(':item_id', $item_id, PDO::PARAM_INT);
    $stmtFetch->execute();
    $currentData = $stmtFetch->fetch();

    if (!$currentData) {
        return ['status' => 'error', 'message' => 'Item not found.'];
    }

    $old_quantity = $currentData['quantity'];

    // Update the quantity
    $sqlUpdate = "UPDATE inventory SET quantity = :new_quantity WHERE id = :item_id";
    $stmtUpdate = $pdo->prepare($sqlUpdate);
    $stmtUpdate->bindParam(':new_quantity', $new_quantity, PDO::PARAM_INT);
    $stmtUpdate->bindParam(':item_id', $item_id, PDO::PARAM_INT);

    if ($stmtUpdate->execute()) {
        // Log the update
        $this->logChange($item_id, 'quantity_update', $old_quantity, $new_quantity);
        return ['status' => 'success', 'message' => 'Quantity updated successfully.'];
    }

    return ['status' => 'error', 'message' => 'Failed to update quantity.'];
    }

// Log function
    public function logChange($item_id, $change_type, $old_value, $new_value)
    {
    $pdo = (new Connection())->connect();

    // Decode JWT to get user info
    $headers = apache_request_headers();
    $jwt = $headers['Authorization'] ?? null;
    if ($jwt) {
        $jwt = str_replace('Bearer ', '', $jwt);
    }

    try {
        $decoded = \Firebase\JWT\JWT::decode($jwt, new \Firebase\JWT\Key(SECRET_KEY, 'HS256'));
        $updated_by = $decoded->data->username;
    } catch (\Exception $e) {
        $updated_by = 'unknown'; // Default to unknown if decoding fails
    }

    // Insert log into the database
    $sqlLog = "INSERT INTO logs (item_id, updated_by, change_type, old_value, new_value) 
               VALUES (:item_id, :updated_by, :change_type, :old_value, :new_value)";
    $stmtLog = $pdo->prepare($sqlLog);
    $stmtLog->bindParam(':item_id', $item_id, PDO::PARAM_INT);
    $stmtLog->bindParam(':updated_by', $updated_by, PDO::PARAM_STR);
    $stmtLog->bindParam(':change_type', $change_type, PDO::PARAM_STR);
    $stmtLog->bindParam(':old_value', $old_value, PDO::PARAM_STR);
    $stmtLog->bindParam(':new_value', $new_value, PDO::PARAM_STR);
    $stmtLog->execute();
    }

    public function getLogs()
    {
    $pdo = (new Connection())->connect();
    $sql = "SELECT * FROM logs ORDER BY timestamp DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
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
}
