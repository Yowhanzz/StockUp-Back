<?php

interface InventoryInterface {

  public function createItem($itemName, $brand, $category, $quantity);

  public function readItems();

  public function updateQuantity($itemId, $quantity);

  public function deleteItem($itemId);

  public function sortItemsByCategory($specificCategory = null);

  public function getItemsByStatus($status);

  public function getItemsByQuantityDesc();

  public function getItemsByQuantityAsc();
  
  public function updateQuantityValue($item_id, $new_quantity);

  public function logUserAction($jwt, $functionName);

  public function getAllUserLogs();

}