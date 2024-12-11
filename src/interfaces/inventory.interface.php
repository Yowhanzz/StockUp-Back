<?php

interface InventoryInterface {

  public function createItem($itemName, $category, $quantity);

  public function readItems();

  public function updateQuantity($itemId, $quantity);

  public function deleteItem($itemId);

  public function sortItemsByCategory();

  public function getItemsByStatus($status);

  public function getItemsByQuantityDesc();

  public function getItemsByQuantityAsc();
  
  public function updateQuantityValue($item_id, $new_quantity);

  public function logChange($item_id, $change_type, $old_value, $new_value);

  public function getLogs();

  public function logUserAction($jwt, $functionName);

}