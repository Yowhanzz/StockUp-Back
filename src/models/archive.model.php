<?php

require_once __DIR__ . '/../config/Connection.php';

class ArchiveModel
{
  private $db;

  public function __construct()
  {
      $connection = new Connection();
      $this->db = $connection->connect();
  }
  public function getAllArchivedItems()
  {
      try {
          $sql = "SELECT * FROM archive_items ORDER BY archived_at DESC";
          $stmt = $this->db->prepare($sql);
          $stmt->execute();
          $archivedItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

          return [
              'status' => 'success',
              'data' => $archivedItems
          ];
      } catch (Exception $e) {
          error_log("Error fetching archived items: " . $e->getMessage());
          return [
              'status' => 'error',
              'message' => 'Failed to fetch archived items.'
          ];
      }
  }
    public function retrieveArchivedItem($itemId)
    {
        try {
            $this->db->beginTransaction();

            $sqlSelect = "SELECT * FROM archive_items WHERE item_id = :item_id";
            $stmtSelect = $this->db->prepare($sqlSelect);
            $stmtSelect->execute([':item_id' => $itemId]);
            $archivedItem = $stmtSelect->fetch(PDO::FETCH_ASSOC);

            if (!$archivedItem) {
                throw new Exception("Archived item not found.");
            }

            $sqlInsert = "
                INSERT INTO inventory (item_id, item_name, category, quantity, status) 
                VALUES (:item_id, :item_name, :category, :quantity, :status)";
            $stmtInsert = $this->db->prepare($sqlInsert);
            $stmtInsert->execute([
                ':item_id' => $archivedItem['item_id'],
                ':item_name' => $archivedItem['item_name'],
                ':category' => $archivedItem['category'],
                ':quantity' => $archivedItem['quantity'],
                ':status' => $archivedItem['status']
            ]);

            $sqlDelete = "DELETE FROM archive_items WHERE item_id = :item_id";
            $stmtDelete = $this->db->prepare($sqlDelete);
            $stmtDelete->execute([':item_id' => $itemId]);

            $this->db->commit();

            return [
                'status' => 'success',
                'message' => 'Archived item successfully retrieved to inventory.'
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error retrieving archived item: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
    public function deleteArchivedItem($itemId)
    {
        try {
            $sql = "DELETE FROM archive_items WHERE item_id = :item_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':item_id' => $itemId]);

            if ($stmt->rowCount() === 0) {
                return [
                    'status' => 'error',
                    'message' => 'Archived item not found.'
                ];
            }

            return [
                'status' => 'success',
                'message' => 'Archived item deleted successfully.'
            ];
        } catch (Exception $e) {
            error_log("Error deleting archived item: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Failed to delete archived item.'
            ];
        }
    }
}
